<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Lth\Lthsolr\Task;

ini_set('memory_limit', '-1');
error_reporting(E_ERROR);
set_time_limit(0);

use TYPO3\CMS\Core\Utility\GeneralUtility;

class OrganisationImport extends \TYPO3\CMS\Scheduler\Task\AbstractTask {
	
    function execute()
    {
	$executionSucceeded = FALSE;

	$executionSucceeded = $this->indexItems();
        
	return $executionSucceeded;
    }

    function indexItems()
    {

        require(__DIR__.'/init.php');

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

        if (!$settings['solrHost'] || !$settings['solrPort'] || !$settings['solrPath'] || !$settings['solrTimeout'] || !$settings['dbhost'] || !$settings['db'] || !$settings['grsp'] || !$settings['studentGrsp'] || !$settings['user'] || !$settings['pw']) {
	    return 'Please make all settings in extension manager';
	}
                
        $grsp = $settings['grsp'];
        $studentGrsp = $settings['studentGrsp'];
        $hideonwebGrsp = $settings['hideonwebGrsp'];
        $studentMainGroup = $settings['solrStudentMainGroup'];
        
        $solrLucrisApiKey = $settings['solrLucrisApiKey'];
        $solrLucrisApiVersion = $settings['solrLucrisApiVersion'];

        $dbhost = $settings['dbhost'];
        $db = $settings['db'];
        $user = $settings['user'];
        $pw = $settings['pw'];

        $con = mysqli_connect($dbhost, $user, $pw, $db) or die("60; ".mysqli_error());
        
        //$executionSucceeded = $this->getOrgFiles($solrLucrisApiVersion, $solrLucrisApiKey);
       
        $organisationArray = $this->getLucacheOrganisation($con);

        $executionSucceeded = $this->getOrganisations($organisationArray, $config, $syslang);
        
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
        $executionSucceeded = $this->getOrganisations($organisationArray, $config, $syslang);
        
        return $executionSucceeded;
    }
    
    
    private function debug($inputArray)
    {
        echo '<pre>';
        print_r($inputArray);
        echo '</pre>';
    }
    
    
    private function getLucacheOrganisation($con)
    {
        $organisationArray = array();
        
        $sql = "SELECT city, homepage, maildelivery, orgid, parent, phone, postal_address, street FROM lucache_vorg";
        
        $res = mysqli_query($con, $sql) or die("99; ".mysqli_error());

        while ($row = mysqli_fetch_array($res, MYSQLI_ASSOC)) {
            $orgid = $row['orgid'];
            $organisationArray[$orgid]['city'] = $row['city'];
            $organisationArray[$orgid]['homepage'] = $row['homepage'];
            $organisationArray[$orgid]['maildelivery'] = $row['maildelivery'];
            $organisationArray[$orgid]['parent'] = $row['parent'];
            $organisationArray[$orgid]['phone'] = $row['phone'];
            $organisationArray[$orgid]['postal_address'] = $row['postal_address'];
            $organisationArray[$orgid]['street'] = $row['street'];
        }

        return $organisationArray;
    }
    
    
    function getOrganisations($organisationArray, $config, $syslang)
    {
        //$this->debug($organisationArray);
        //die();
        //create a client instance
        $client = new \Solarium\Client($config);
        $update = $client->createUpdate();
        $buffer = $client->getPlugin('bufferedadd');
        $buffer->setBufferSize(250);
        $docArray = array();
                
        $directory = '/var/www/html/typo3/lucrisdump';
        $fileArray = scandir($directory . '/orgfilestoindex');
        //$filename = '0.xml';
        $fileArray = array_slice($fileArray, 2);

        foreach ($fileArray as $key => $filename) {

            $xmlpath = $directory . '/orgfilestoindex/' . $filename;
            $xml = @file_get_contents($xmlpath);
            
            $xmlPrefix = '<?xml version="1.0" encoding="UTF-8"?>';
            $xmlPrefix .= '<GetOrganisationResponse requestId=""><result>';
            $xmlSuffix = '</result></GetOrganisationResponse>';

            $xml = @simplexml_load_string($xmlPrefix . $xml . $xmlSuffix);

            foreach($xml->xpath('//result//organisationalUnit') as $content) {
                $organisationId = array();
                $name_en = '';
                $name_sv = '';
                $organisationTitle = '';
                $typeClassification_sv = '';
                $typeClassification_en = '';
                $typeClassification = '';
                $id = '';
                $portalUrl = '';
                $parents = array();
                $organisationSourceId = array();
                
                $mailDelivery = '';
                $organisationCity = '';
                $organisationParentSourceId = '';
                $organisationPhone = '';
                $organisationPostalAddress = '';
                $organisationStreet = '';
                $homepage = '';
                $parentName = array();
                $parentName_sv = array();
                $parentName_en = array();
                
                //id
                $id = (string)$content->attributes();
                
                //portalUrl
                $portalUrl = (string)$content->info->portalUrl;
                
                //name
                if($content->name) {
                    foreach($content->name as $name) {
                        if($name->attributes()->locale == 'en_GB') {
                            $name_en = (string)$name;
                        }
                        if($name->attributes()->locale == 'sv_SE') {
                            $name_sv = (string)$name;
                        }
                    }
                }
                
                //typeClassification
                if($content->type) {
                    foreach($content->type as $type) {
                        if($type->attributes()->locale == 'en_GB') {
                            $typeClassification_en = (string)$type;
                        }
                        if($type->attributes()->locale == 'sv_SE') {
                            $typeClassification_sv = (string)$type;
                        }
                    }
                }
                
                //parents
                if($content->parents) {
                    
                    foreach($content->parents->parent as $parent) {
                        
                        $parents[] = (string)$parent->attributes();

                        $parentName_en[] = (string)$parent->name[0];

                        $parentName_sv[] = (string)$parent->name[1];
                    }
                }
                
                //organisationSourceId
                if($content->externalableInfo) {
                    $organisationSourceId[] = $content->externalableInfo->sourceId;
                    $orgid = (string)$content->externalableInfo->sourceId;
                }
                    
                //language switch
                if($syslang==="sv") {
                    $typeClassification = $typeClassification_sv;
                    $organisationTitle = $name_sv;
                    $parentName = $parentName_sv;
                } else {
                    $typeClassification = $typeClassification_en;
                    $organisationTitle = $name_en;
                    $parentName = $parentName_en;
                }
                
                //Extradata from lucache
                $homepage = $organisationArray[$orgid]['homepage'];
                $mailDelivery = (string)$organisationArray[$orgid]['maildelivery'];
                $organisationCity = $organisationArray[$orgid]['city'];
                $organisationParentSourceId = $organisationArray[$orgid]['parent'];
                $organisationPhone = $organisationArray[$orgid]['phone'];
                $organisationPostalAddress = $organisationArray[$orgid]['postal_address'];
                $organisationStreet = $organisationArray[$orgid]['street'];
                
                
                $data = array(
                    'appKey' => 'lthsolr',
                    'id' => $id,
                    'docType' => 'organisation',
                    'homepage' => $homepage,
                    'mailDelivery' => $mailDelivery,
                    'organisationCity' => $organisationCity,
                    'organisationParent' => $parents,
                    'organisationParentName' => $parentName,
                    'organisationParentSourceId' => $organisationParentSourceId,
                    'organisationPhone' => $organisationPhone,
                    'organisationPostalAddress' => $organisationPostalAddress,
                    'organisationSourceId' => $organisationSourceId,
                    'organisationStreet' => $organisationStreet,
                    'organisationTitle' => $organisationTitle,
                    'portalUrl' => $portalUrl,
                    'typeClassification' => $typeClassification,
                    'type' => 'organisation',
                    'boost' => '1.0',
                    'date' => gmdate('Y-m-d\TH:i:s\Z', strtotime($created)),
                    'changed' => gmdate('Y-m-d\TH:i:s\Z', strtotime($modified)),
                    'digest' => md5($id),
                );

                $buffer->createDocument($data);
            }
        }
        $buffer->commit();

        $update->addCommit();
        $client->update($update);

        return TRUE;
    }
    
    function getOrgFiles($solrLucrisApiVersion, $solrLucrisApiKey)
    {        
        $numberofloops = 1;
        $startFromHere=0;
        $directory = '/var/www/html/typo3/lucrisdump';

        for($i = 0; $i <= $numberofloops; $i++) {
            
            $startrecord = $startFromHere + ($i * 20);
            //$fileName = $startrecord . '.xml';
            //$xmlpath = "https://lucris.lub.lu.se/ws/rest/organisation.current?namespaces=remove&rendering=xml_long&window.size=20&window.offset=$startrecord";
            $xmlpath = "https://lucris.lub.lu.se/ws/api/$solrLucrisApiVersion/organisational-units/?apiKey=$solrLucrisApiKey&size=20&offset=$startrecord";

            $xml = @file_get_contents($xmlpath);
            //echo $xmlpath;
            $xml = @simplexml_load_string($xml);
            $numberofloops = ceil($xml->count / 20);

            foreach($xml->xpath('//result//organisationalUnit') as $content) {
                $id = (string)$content->attributes();                
                //save content as xml
                $content->asXml($directory . '/orgfilestoindex/' . $id . '.xml');
            }
        }
        return TRUE;
    }
}
