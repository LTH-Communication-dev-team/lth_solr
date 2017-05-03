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
        
        $config = array(
            'endpoint' => array(
                'localhost' => array(
                    'host' => $settings['solrHost'],
                    'port' => $settings['solrPort'],
                    'path' => $settings['solrPath'],
                    'timeout' => $settings['solrTimeout']
                )
            )
        );
        
        $client = new \Solarium\Client($config);
        
        $buffer = $client->getPlugin('bufferedadd');
        $buffer->setBufferSize(200);

        $current_date = gmDate("Y-m-d\TH:i:s\Z");
        
        //Get last modified
        $query = $client->createSelect();
        $query->setQuery('doctype:upmproject');
        $query->addSort('tstamp', $query::SORT_DESC);
        $query->setStart(0)->setRows(1);
        $response = $client->select($query);
        $idArray = array();
        foreach ($response as $document) {
            $lastModified = $document->tstamp;
        }

        //$GLOBALS['TYPO3_DB']->exec_DELETEquery("tx_lthsolr_lucrisdata", "lucris_type='upmproject'");

	$executionSucceeded = $this->getUpmprojects($config, $client, $buffer, $current_date, $maximumrecords, $numberofloops, $settings, $lastModified);
        
        //$executionSucceeded = $this->deleteOldProjects($client);
        
	return $executionSucceeded;
    }
    
    
    function deleteOldProjects($client)
    {
        try {
            $query = $client->createSelect();
            $query->setQuery('doctype:upmproject');
            $query->setStart(0)->setRows(1000000);
            $response = $client->select($query);
        } catch(Exception $e) {
            die($e->getMessage());
        }
        
        foreach ($response as $document) {
            $id = $document->id;
            $xmlpath = "https://lucris.lub.lu.se/ws/rest/upmprojects?uuids.uuid=" . $id;
            $xml = file_get_contents($xmlpath);
            $xml = simplexml_load_string($xml);
            
            if($xml->children('core', true)->count == 0) {
                $update = $client->createUpdate();
                $update->addDeleteById($id);
                $update->addCommit();
                $result = $client->update($update);
                $GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_devlog', array('msg' => $id, 'crdate' => time()));
            }
        }
        
        return TRUE;
    }

    
    function getUpmprojects($config, $client, $buffer, $current_date, $maximumrecords, $numberofloops, $settings, $lastModified)
    {
        $lucrisProjectsArray = array();
        $i = 0;
        for($i = 0; $i < $numberofloops; $i++) {
            //echo $i.':'. $numberofloops . '<br />';

            $startrecord = $i * $maximumrecords;
            if($startrecord > 0) $startrecord++;

            $lucrisId = $settings['solrLucrisId'];
            $lucrisPw = $settings['solrLucrisPw'];

            $xmlpath = "https://lucris.lub.lu.se/ws/rest/upmprojects?window.size=$maximumrecords&window.offset=$startrecord&orderBy.property=id&rendering=xml_long";

            try {
                //$GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_devlog', array('msg' => '200: ' . $xmlpath, 'crdate' => time()));
                $xml = file_get_contents($xmlpath);
            } catch(Exception $e) {
                $GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_devlog', array('msg' => '500: ' . $xmlpath, 'crdate' => time()));
            }
            $xml = simplexml_load_string($xml);
            
            if($xml->children('core', true)->count == 0) {
                return "no items";
            }

            $numberofloops = ceil($xml->children('core', true)->count / 20);

            foreach($xml->xpath('//core:result//core:content') as $content) {
                $id = '';
                $portalUrl = '';
                $created = '';
                $modified = '';
                $title_en = array();
                $title_sv = array();
                $startDate = '';
                $endDate = '';
                $status = '';
                $organisationId = array();
                $organisationName_en = array();
                $organisationName_sv = array();
                $participants = array();
                $participantId = array();
                $descriptions_en = array();
                $descriptions_sv = array();

                //id
                $id = (string)$content->attributes();

                //portalUrl
                $portalUrl = (string)$content->children('core',true)->portalUrl;
                
                //created
                $created = (string)$content->children('core',true)->created;
                
                //modified
                $modified = (string)$content->children('core',true)->modified;
                
                //title
                if($content->children('stab',true)->title) {
                    foreach($content->children('stab',true)->title->children('core',true)->localizedString as $title) {
                        if($title->attributes()->locale == 'en_GB') {
                            $title_en = (string)$title;
                        } else {
                            $title_en = '';
                        }
                        if($title->attributes()->locale == 'sv_SE') {
                            $title_sv = (string)$title;
                        } else {
                            $title_sv = '';
                        }
                    }
                }
                
                //startEndDate
                if($content->children('stab',true)->startEndDate) {
                    $startDate = $content->children('stab',true)->startEndDate->children('extensions-core', true)->startDate;
                    $endDate = $content->children('stab',true)->startEndDate->children('extensions-core', true)->endDate;
                }

                //status
                if($content->children('stab',true)->status) {
                    $status = $content->children('stab',true)->status;
                }
                
                //descriptions
                if($content->children('stab',true)->descriptions) {
                    foreach($content->children('stab',true)->descriptions as $descriptions) {
                        if($descriptions->children('extensions-core',true)->classificationDefinedField) {
                            foreach($descriptions->children('extensions-core',true)->classificationDefinedField->children('extensions-core',true)->value->children('core',true)->localizedString as $localizedString) {
                                if($localizedString->attributes()->locale == 'en_GB') {
                                    $descriptions_en[] = (string)$localizedString;
                                }
                                if($localizedString->attributes()->locale == 'sv_SE') {
                                    $descriptions_sv[] = (string)$localizedString;
                                }
                            }
                        }
                    }
                }
                
                //participants
                if($content->children('stab',true)->participants) {
                    foreach($content->children('stab',true)->participants->children('stab',true)->participantAssociation as $participantAssociation) {
                        if($participantAssociation->children('person-template',true)->person) {
                            $participantId[] = (string)$participantAssociation->children('person-template',true)->person->attributes();
                            $participants[] = $participantAssociation->children('person-template',true)->person->children('person-template',true)->name->children('core',true)->firstName . ' ' . 
                                    $participantAssociation->children('person-template',true)->person->children('person-template',true)->name->children('core',true)->lastName;
                        }
                    }
                }

                //organisations
                if($content->children('stab',true)->organisations) {
                    foreach($content->children('stab',true)->organisations->children('organisation-template',true)->organisation as $organisation) {
                        $organisationId[] = (string)$organisation->attributes();
                        if($organisation->children('organisation-template',true)->name) {
                            foreach($organisation->children('organisation-template',true)->name->children('core',true) as $localizedString) {
                                if($localizedString->attributes()->locale == 'en_GB') {
                                    $organisationName_en[] = (string)$localizedString;
                                }
                                if($localizedString->attributes()->locale == 'sv_SE') {
                                    $organisationName_sv[] = (string)$localizedString;
                                }
                            }
                        }
                    }
                }

                //sourceId
                /*if($content->children('stab1',true)->external->children('extensions-core',true)->sourceId) {
                    $sourceId = (string)$content->children('stab1',true)->external->children('extensions-core',true)->sourceId;
                }*/
                $title_sort = $title_en;
                if($title_en==='') {
                    $title_sort = $title_sv;
                }
                
                $data = array(
                    'id' => $id,
                    'doctype' => 'upmproject',
                    'portalUrl' => $portalUrl,
                    'title_en' => $title_en,
                    'title_sv' => $title_sv,
                    'title_sort2' => $title_sort,
                    'projectStartDate' => $this->makeGmDate($startDate),
                    'projectEndDate' => $this->makeGmDate($endDate),
                    'projectStatus' => $status,
                    'organisationId' => $organisationId,
                    'organisationName_en' => $organisationName_en,
                    'organisationName_sv' => $organisationName_sv,
                    'participants' => $participants,
                    'participantId' => $participantId,
                    'descriptions_en' => $descriptions_en,
                    'descriptions_sv' => $descriptions_sv,
                    'boost' => '1.0',
                    'date' => gmdate('Y-m-d\TH:i:s\Z', strtotime($created)),
                    'tstamp' => gmdate('Y-m-d\TH:i:s\Z', strtotime($modified)),
                    'digest' => md5($id)
                );
                //$this->debug($data);
                $buffer->createDocument($data);
                //$GLOBALS["TYPO3_DB"]->exec_INSERTquery("tx_lthsolr_lucrisdata", array("lucris_id" => $id, "lucris_type" => "upmproject"));
            }
        }
        $buffer->commit();
        
        $update = $client->createUpdate();
        $update->addCommit();
                
        return TRUE;
    }
    
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