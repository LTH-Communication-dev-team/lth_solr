<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Lth\Lthsolr\Task;

use TYPO3\CMS\Core\Utility\GeneralUtility;

class ProjectImport extends \TYPO3\CMS\Scheduler\Task\AbstractTask {
	
    function execute()
    {
        error_reporting(E_ALL ^ E_NOTICE);
	$executionSucceeded = FALSE;
        
        require(__DIR__.'/init.php');
        $maximumrecords = 20;
        $numberofloops = 1;
        
        $settings = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['lth_solr']);
        
        $syslang = "sv";
        
        $config = array(
            'endpoint' => array(
                'localhost' => array(
                    'host' => $settings['solrHost'],
                    'port' => $settings['solrPort'],
                    'path' => "/solr/core_$syslang/",//$settings['solrPath'],
                    'timeout' => $settings['solrTimeout']
                )
            )
        );
        $solrLucrisApiKey = $settings['solrLucrisApiKey'];
        $solrLucrisApiVersion = $settings['solrLucrisApiVersion'];
        
        $client = new \Solarium\Client($config);
        $buffer = $client->getPlugin('bufferedadd');
        $buffer->setBufferSize(200);
        
        $mode = '';
        
        $startFromHere = 0;
        
        if($mode==='') {
            $executionSucceeded = $this->getFiles($startFromHere, $solrLucrisApiKey, $solrLucrisApiVersion);        
        }

        if($mode==='reindex') {
            //$this->deleteOldProjects($client);
        }

        $current_date = gmDate("Y-m-d\TH:i:s\Z");
        
        //Get last modified
        $query = $client->createSelect();
        $query->setQuery('docType:project');
        //$query->addSort('tstamp', $query::SORT_DESC);
        $query->setStart(0)->setRows(1);
        $response = $client->select($query);
        $idArray = array();
        foreach ($response as $document) {
            $lastModified = $document->changed;
        }

        $heritageArray = $this->getHeritage($client);

	$executionSucceeded = $this->getProjects($config, $client, $buffer, $current_date, $maximumrecords, 
                $settings, $lastModified, $heritageArray, $syslang, $solrLucrisApiKey, $solrLucrisApiVersion, $mode);
        
        $syslang = "en";
        $config = array(
            'endpoint' => array(
                'localhost' => array(
                    'host' => $settings['solrHost'],
                    'port' => $settings['solrPort'],
                    'path' => "/solr/core_$syslang/",//$settings['solrPath'],
                    'timeout' => $settings['solrTimeout']
                )
            )
        );
        $client = new \Solarium\Client($config);

        $buffer = $client->getPlugin('bufferedadd');
        $buffer->setBufferSize(200);
        
        $executionSucceeded = $this->getProjects($config, $client, $buffer, $current_date, $maximumrecords, 
                $settings, $lastModified, $heritageArray, $syslang, $solrLucrisApiKey, $solrLucrisApiVersion, $mode);
                
	return $executionSucceeded;
    }
    
    
    function deleteOldProjects($client)
    {
        try {
            $query = $client->createSelect();
            $query->setQuery('docType:project');
            $query->setStart(0)->setRows(1000000);
            $response = $client->select($query);
        } catch(Exception $e) {
            die($e->getMessage());
        }
        
        foreach ($response as $document) {
            $id = $document->id;
            
            $update = $client->createUpdate();
            $update->addDeleteById($id);
            $update->addCommit();
            $result = $client->update($update);
                //$GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_devlog', array('msg' => $id, 'crdate' => time()));
        }
        
        return TRUE;
    }

    
    function getProjects($config, $client, $buffer, $current_date, $maximumrecords, 
            $settings, $lastModified, $heritageArray, $syslang, $solrLucrisApiKey, $solrLucrisApiVersion, $mode)
    {
        $heritageArray = $heritageArray[0];
        $directory = '/var/www/html/typo3/lucrisdump';

        if($mode === '' && $syslang==='sv') {
            $fileArray = scandir($directory . '/projectstoindex');
        } else if($syslang==='en') {
            $fileArray = scandir($directory . '/svindexedprojects');
        } else if($mode === 'reindex' && $syslang==='sv') {
            $fileArray = scandir($directory . '/indexedprojects');
        }
            
        $fileArray = array_slice($fileArray, 2);
        
        foreach ($fileArray as $filekey => $filename) {
            if($mode === '' && $syslang==='sv') {
                $xmlpath = $directory . '/projectstoindex/' . $filename;
            } else if($syslang==='en') {
                $xmlpath = $directory . '/svindexedprojects/' . $filename;
            } else if($mode === 'reindex' && $syslang==='sv') {
                $xmlpath = $directory . '/indexedprojects/' . $filename;
            }

            $xml = @file_get_contents($xmlpath);
            
            $xml = @simplexml_load_string($xml);

            $projectTitle = '';
            $projectTitle_en = '';
            $projectTitle_sv = '';
            
            $projectType = '';
            $projectType_en = '';
            $projectType_sv = '';
            
            $projectStatus = '';
            $projectStatus_en = '';
            $projectStatus_sv = '';
            
            $projectDescriptionType = array();
            $descriptionType_en = '';
            $descriptionType_sv = '';
            $projectDescription = array();
            $description_en = '';
            $description_sv = '';
            
            $participantId = array();
            $participantName = array();
            $name= '';
            $name_en = '';
            $name_sv = '';
            $participantRole = array();
            $personRole_en = '';
            $personRole_sv = '';
            $participantOrganisationId = array();
            $participantOrganisationName = array();
            $organisationId = array();
            $organisationName = array();
            $organisationType = array();
            $organisationalUnitName_en = '';
            $organisationalUnitName_sv = '';
            $participantOrganisationType = array();
            $organisationalUnitType_en = '';
            $organisationalUnitType_sv = '';
            $organisationSourceId = array();
            
            $managingOrganisationId = ''; //NEW!added
            $managingOrganisationName = ''; //NEW!added
            $managingOrganisationalunitName_en = '';
            $managingOrganisationalunitName_sv = '';
            $managingOrganisationType = ''; //NEW!added
            $managingOrganisationalunitType_en = '';
            $managingOrganisationalunitType_sv = '';

            $startDate = '';
            $endDate = '';
            
            $curtailed = '';
            $visibility = '';
            $visibility_en = '';
            $visibility_sv = '';
                    
            $createdDate = '';
            $modifiedDate = '';
            $portalUrl = '';
            
            $heritage = array();
            
            $id = (string)$xml->attributes();

            //projectTitle
            if($xml->title) {
                $projectTitle_en = (string)$xml->title[0];
                $projectTitle_sv = (string)$xml->title[1];
                $projectTitle = $this->languageSelector($syslang, $projectTitle_en, $projectTitle_sv);
            }
            
            //projectType
            if($xml->type) {
                $projectType_en = (string)$xml->type[0];
                $projectType_sv = (string)$xml->type[1];
                $projectType = $this->languageSelector($syslang, $projectType_en, $projectType_sv);
            }
            
            //projectStatus
            if($xml->status) {
                $projectStatus_en = (string)$xml->status[0];
                $projectStatus_sv = (string)$xml->status[1];
                $projectStatus = $this->languageSelector($syslang, $projectStatus_en, $projectStatus_sv);
            }

            foreach((array)$xml as $key => $value) {
                //descriptions
                if($key === 'descriptions') {
                    //die($this->debug($value));
                    foreach($value->description as $description) {
                       //die($this->debug($description->attributes()));
                        if((string)$description->attributes()->locale === 'en_GB') $descriptionType_en = (string)$description->attributes()->type;
                        if((string)$description->attributes()->locale === 'sv_SE') $descriptionType_sv = (string)$description->attributes()->type;
                        
                        if((string)$description->attributes()->locale === 'en_GB') $description_en = (string)$description;
                        if((string)$description->attributes()->locale === 'sv_SE') $description_sv = (string)$description;
                        
                        $projectDescriptionType[] = $this->languageSelector($syslang, $descriptionType_en, $descriptionType_sv);
                        $projectDescription[] = $this->languageSelector($syslang, $description_en, $description_sv);
                    }
                    
                }
                
                //participants
                if($key === 'participants') {
                    foreach($value->participant as $participant) {
                        if($participant->person) {
                            $participantId[] = (string)$participant->person->attributes();
                            if($participant->person->name[0]) $name_en = (string)$participant->person->name[0];
                            if($participant->person->name[1]) $name_sv = (string)$participant->person->name[1];
                            $participantName[] = $this->languageSelector($syslang, $name_en, $name_sv);
                        } else {
                            if($participant->name->firstName) $name = (string)$participant->name->firstName;
                            if($participant->name->lastName) $name .= ' ' . (string)$participant->name->lastName;
                            if($name) $participantName[] = $name;
                        }
                        if($participant->personRole) {
                            $personRole_en = (string)$participant->personRole[0];
                            $personRole_sv = (string)$participant->personRole[1];
                            $participantRole[] = $this->languageSelector($syslang, $personRole_en, $personRole_sv);
                        }
                        if($participant->organisationalUnits) {
                            foreach($participant->organisationalUnits->organisationalUnit as $organisationalUnit) {
                                $participantOrganisationId[] = (string)$organisationalUnit->attributes();
                                if($organisationalUnit->name[0]) $organisationalUnitName_en = (string)$organisationalUnit->name[0];
                                if($organisationalUnit->name[1]) $organisationalUnitName_sv = (string)$organisationalUnit->name[1];
                                $participantOrganisationName[] = $this->languageSelector($syslang, $organisationalUnitName_en, $organisationalUnitName_sv);
                                if($organisationalUnit->type[0])  $organisationalUnitType_en = (string)$organisationalUnit->type[0];
                                if($organisationalUnit->type[1]) $organisationalUnitType_sv = (string)$organisationalUnit->type[1];
                                $participantOrganisationType[] = $this->languageSelector($syslang, $organisationalUnitType_en, $organisationalUnitType_sv);
                            }
                        }
                        
                    }
                }
                
                //Organisationalunit
                if($key === 'organisationalUnits') {

                    foreach($value->organisationalUnit as $organisationalUnit) {
                        $organisationId[] = (string)$organisationalUnit->attributes();
                        if($organisationalUnit->name[0]) $organisationName_en = (string)$organisationalUnit->name[0];
                        if($organisationalUnit->name[1]) $organisationName_sv = (string)$organisationalUnit->name[1];
                        $organisationName[] = $this->languageSelector($syslang, $organisationName_en, $organisationName_sv);
                        if($organisationalUnit->type[0]) $organisationType_en = (string)$organisationalUnit->type[0];
                        if($organisationalUnit->type[1]) $organisationType_sv = (string)$organisationalUnit->type[1];
                        $organisationType[] = $this->languageSelector($syslang, $organisationType_en, $organisationType_sv);
                    }
                }
                
                //managingOrganisationalunit
                if($key === 'managingOrganisationalUnit') {
                    $managingOrganisationId = (string)$value->attributes();
                    if($value->name[0]) $managingOrganisationalunitName_en = (string)$value->name[0];
                    if($value->name[1]) $managingOrganisationalunitName_sv = (string)$value->name[1];
                    $managingOrganisationName = $this->languageSelector($syslang, $managingOrganisationalunitName_en, $managingOrganisationalunitName_sv);
                    if($value->type[0]) $managingOrganisationalunitType_en = (string)$value->type[0];
                    if($value->type[1]) $managingOrganisationalunitType_sv = (string)$value->type[1];
                    $managingOrganisationType = $this->languageSelector($syslang, $managingOrganisationalunitType_en, $managingOrganisationalunitType_sv);
                }
                
                //period
                if($key === 'period') {
                    $startDate = (string)$value->startDate;
                    $endDate = (string)$value->endDate;
                }
                
                //curtailed
                if($key === 'curtailed') {
                    $curtailed = (string)$value;
                }
                
                //visibility
                if($key === 'visibility') {
                    if($value[0]) $visibility_en = (string)$value[0];
                    if($value[1]) $visibility_sv = (string)$value[1];
                    $visibility = $this->languageSelector($syslang, $visibility_en, $visibility_sv);
                }
                
                //info
                if($key === 'info') {
                    $createdDate = (string)$value->createdDate;
                    $modifiedDate = (string)$value->modifiedDate;
                    $portalUrl = (string)$value->portalUrl;
                }
            }
            
            if($organisationId) {
                foreach($organisationId as $key1 => $value1) {
                    $heritage[] = $value1;
                    $parent = $heritageArray[$value1];

                    if($parent) { 
                        $heritage[] = $parent;
                    }
                    $parent = $heritageArray[$parent];
                    if($parent) {
                        $heritage[] = $parent;
                    }
                    $parent = $heritageArray[$parent];
                    if($parent) {
                        $heritage[] = $parent;
                    }
                    $parent = $heritageArray[$parent];
                    if($parent) {
                        $heritage[] = $parent;
                    }
                    $parent = $heritageArray[$parent];
                    if($parent) {
                        $heritage[] = $parent;
                    }
                    $parent = $heritageArray[$parent];
                    if($parent) {
                        $heritage[] = $parent;
                    }
                    $parent = $heritageArray[$parent];
                    if($parent) {
                        $heritage[] = $parent;
                    }
                    $parent = $heritageArray[$parent];
                    if($parent) {
                        $heritage[] = $parent;
                    }
                    $parent = $heritageArray[$parent];
                    if($parent) {
                        $heritage[] = $parent;
                    }
                }

                if($heritage) {
                    array_filter($heritage);
                    $organisationSourceId = array_unique($heritage);
                }
            }
                    
            $data = array(
                'appKey' => 'lthsolr',
                'boost' => '1.0',
                'curtailed' => $curtailed,
                'date' => gmdate('Y-m-d\TH:i:s\Z', strtotime($createdDate)),
                'digest' => md5($id),
                'docType' => 'project',
                'endDate' => $this->makeGmDate($endDate),
                'id' => $id,
                'managingOrganisationId' => $managingOrganisationId,
                'managingOrganisationName' => $managingOrganisationName,
                'managingOrganisationType' => $managingOrganisationType,
                'organisationId' => $organisationId,
                'organisationName' => $organisationName,
                'organisationSourceId' => $organisationSourceId,
                'organisationType' => $organisationType,
                'participantId' => $participantId,
                'participantName' => $participantName,
                'participantOrganisationId' => $participantOrganisationId,
                'participantOrganisationName' => $participantOrganisationName,
                'participantOrganisationType' => $participantOrganisationType,
                'participantRole' => $participantRole,
                'projectDescription' => $projectDescription,
                'projectDescriptionType' => $projectDescriptionType,
                'projectStatus' => $projectStatus,
                'projectTitle' => $projectTitle,
                'projectType' => $projectType,
                'startDate' => $this->makeGmDate($startDate),
                'tstamp' => gmdate('Y-m-d\TH:i:s\Z', strtotime($modifiedDate)),
                'type' => 'project',
                'visibility' => $visibility,
            );
            //die($this->debug($data));
            //
            //move files
            if($mode === '' && $syslang==='sv') {
                rename($directory . '/projectstoindex/' . $filename, $directory . '/svindexedprojects/' . $filename);
            } else if($syslang==='en') {
                rename($directory . '/svindexedprojects/' . $filename, $directory . '/indexedprojects/' . $filename);
            } else if($mode === 'reindex' && $syslang==='sv') {
                rename($directory . '/indexedprojects/' . $filename, $directory . '/svindexedprojects/' . $filename);
            }

            $buffer->createDocument($data);
        }
        $buffer->commit();
        
        $update = $client->createUpdate();
        $update->addCommit();
                
        return TRUE;
    }
    
    
    function getFiles($startFromHere, $solrLucrisApiKey, $solrLucrisApiVersion)
    {
        $numberofloops = 1;
        
        $directory = '/var/www/html/typo3/lucrisdump';
        
        $numberOfFilesAlreadyIndexed = count( scandir($directory.'/projectstoindex') ) - 2;
        if($numberOfFilesAlreadyIndexed > 0) {
            $startFromHere = $numberOfFilesAlreadyIndexed;
        }
        
        for($i = 0; $i <= $numberofloops; $i++) {
            
            $startrecord = $startFromHere + ($i * 20);
            
            $xmlpath = "https://lucris.lub.lu.se/ws/api/$solrLucrisApiVersion/projects/?size=20&offset=$startrecord&apiKey=$solrLucrisApiKey";
            //$xmlpath = "https://lucris.lub.lu.se/ws/api/$solrLucrisApiVersion/projects/71f4cc87-44fc-44c1-b834-347ef4f3f750?apiKey=$solrLucrisApiKey";

            $xml = file_get_contents($xmlpath);
        
            $xml = @simplexml_load_string($xml);
//$id = (string)$xml->attributes();
//$xml->asXml($directory . '/projectstoindex/' . $id . '.xml');
            $numberofloops = ceil($xml->count / 20);
            
            foreach($xml->project as $project) {
                $id = (string)$project->attributes();
                $project->asXml($directory . '/projectstoindex/' . $id . '.xml');
            }
        }
        return true;                           
    }
    
    
    function languageSelector($syslang, $value_en, $value_sv)
    {
        if($value_en || $value_sv) {
            if($syslang==="sv") {
                $value = $value_sv;
            } else if($syslang==="en") {
                $value = $value_en;
            }

            if(!$value && $value_en) {
                $value = $value_en;
            }
            if(!$value && $value_sv) {
                $value = $value_sv;
            }
            return trim($value);
        } else {
            return false;
        }
    }
    
    
    private function getHeritage($client)
    {
        $heritageArray = array();
        
        /*$sql = "SELECT orgid, parent FROM lucache_vorg";
        
        $res = mysqli_query($con, $sql);
        
        while ($row = mysqli_fetch_array($res, MYSQLI_ASSOC)) {
            $heritageArray[$row['orgid']] = $row['parent'];
        }*/
        $query = $client->createSelect();
        $query->setQuery('docType:organisation');
        $query->setStart(0)->setRows(3000);
        $response = $client->select($query);
        foreach ($response as $document) {
            if($document->organisationParent) {
                foreach($document->organisationParent as $organisationParent) {
                    $heritageArray[$document->id] = $organisationParent;
                }
            }
        }
        return array($heritageArray);
    }
    
    
    /*private function getHeritage($con)
    {
        $heritageArray = array();
        
        $sql = "SELECT orgid, parent FROM lucache_vorg";
        
        $res = mysqli_query($con, $sql);
        
        while ($row = mysqli_fetch_array($res, MYSQLI_ASSOC)) {
            $heritageArray[$row['orgid']] = $row['parent'];
        }
        return array($heritageArray);
    }*/
    
    
    private function makeGmDate($input)
    {
        if($input && $input != '') {
            $input = gmDate("Y-m-d\TH:i:s\Z", strtotime($input));
            return $input;
        } else {
            return null;
        }
        
    }
    
    
    private function debug($input)
    {
        echo '<pre>';
        print_r($input);
        echo '</pre>';
    }
}