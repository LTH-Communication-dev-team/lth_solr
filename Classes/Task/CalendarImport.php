<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Lth\Lthsolr\Task;

use TYPO3\CMS\Core\Utility\GeneralUtility;

class CalendarImport extends \TYPO3\CMS\Scheduler\Task\AbstractTask {
	
    function execute()
    {
        error_reporting(E_ALL ^ E_NOTICE);
        
	$executionSucceeded = FALSE;
        
        require(__DIR__.'/init.php');
        $maximumrecords = 20;
        $numberofloops = 40;
        
        $settings = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['lth_solr']);
        
        //$executionSucceeded = $this->moveFiles('en');
        //return $executionSucceeded;
        
        //$executionSucceeded = $this->clearIndex($settings);
        //return TRUE;
        
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
        
        $client = new \Solarium\Client($config);
        
        $buffer = $client->getPlugin('bufferedadd');
        $buffer->setBufferSize(200);

        //$allCalendars = $executionSucceeded = $this->getCalendars();
        
        //$executionSucceeded = $this->getEvents($buffer);
        
        $executionSucceeded = $this->createOrder($client);

        /*$syslang = "en";
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
        
        $executionSucceeded = $this->getEvents($client);*/
        
        return $executionSucceeded;
    }
     
    
    function getCalendars()
    {
        $offset=0;
        $allCalendars = array();
        $tmpCalendars = array();
        $run = TRUE;
        do {
            $urlToDecode = "http://lb3v2.net.lu.se/lucal/calendar?limit=100&offset=$offset";
            $timeout = 10;	
            $ctx = stream_context_create(array('http' => array('timeout' => $timeout))); 
            $tmpCalendars = file_get_contents($urlToDecode, 0, $ctx);
            if($tmpCalendars !== false) {
                if(count(json_decode($tmpCalendars, true))===0) {
                    $run = FALSE;
                } else {
                    $allCalendars = array_merge($allCalendars, json_decode($tmpCalendars, true));
                    $offset = $offset + 100;
                }
            }
        } while($run);

	return $allCalendars;
    }
    
    
    function getEvents($buffer)
    {
        $offset=0;
        $allEvents = array();
        $tmpEvents = array();
        $run = TRUE;
        do {
            $urlToDecode = "http://lb3v2.net.lu.se/lucal/event?limit=100&offset=$offset";
            $timeout = 10;	
            $ctx = stream_context_create(array('http' => array('timeout' => $timeout))); 
            $tmpEvents = file_get_contents($urlToDecode, 0, $ctx);
            if($tmpEvents !== false) {
               
                if(count(json_decode($tmpEvents, true))===0) {
                    $run = FALSE;
                } else {
                    $tmpEvents = json_decode($tmpEvents, true);

                    foreach($tmpEvents as $key => $value) {
                        $data = array(
                            'id' => $value['uuid'],
                            'boost' => '1.0',
                            'changed' => gmdate('Y-m-d\TH:i:s\Z', $value['changed']),
                            'digest' => md5($value['uuid']),
                            'title' => $value['title'],
                            'abstract' => $value['field_ns_calendar_body']['und'][0]['safe_value'],
                            'categoryId' => $value['field_ns_calendar_category']['und'][0]['tid'],
                            'categoryName' => $value['field_ns_calendar_category']['und'][0]['name'],
                            'calendar_ids' => $value['calendar_ids'],
                            'pathalias' => $value['pathalias'],
                            'startTime' => date('Y-m-d\TH:i:s\Z', $value['field_ns_calendar_date']['und'][0]['value']),
                            'endTime' => date('Y-m-d\TH:i:s\Z', $value['field_ns_calendar_date']['und'][0]['value2']),
                            'location' => $value['field_ns_calendar_location']['und'][0]['safe_value'],
                            'lead' => $this->handleChar($value['field_ns_calendar_lead']['und'][0]['safe_value']),
                            'language' => $value['language'],
                            'appKey' => 'lthsolr',
                            'docType' => 'calendar',
                            'type' => 'calendar'
                        );
                        //$this->debug($data);
                        //die();
                        $buffer->createDocument($data);
                        
                    }
                    $offset = $offset + 100;
                }
            }
        } while($run);
        
        $buffer->commit();
        
        return TRUE;
    }
    
    
    function createOrder($client)
    {
        $numberofloops = 1;
        $fieldArray = array("id");
        $update = $client->createUpdate();
        $query = $client->createSelect();
        
        $ii = 0;
        $docArray = array();
        for($i = 0; $i < $numberofloops; $i++) {
            $startrecord = $i * 1000;
            $query->setQuery('docType:calendar');
            $query->setStart($startrecord)->setRows(1000);
            $query->addSorts(array("startTime" => "asc","id" => "asc"));        
            $query->setFields($fieldArray);
            $response = $client->select($query);
            $numFound = $response->getNumFound();
            $numberofloops = ceil($numFound / 1000);
            $iii = 0;
            foreach ($response as $document) {
                $id = $document->id;
                ${"doc" . $iii} = $update->createDocument(); 
                ${"doc" . $iii}->setKey('id', $id);
                ${"doc" . $iii}->addField('dateOrder', $ii);
                ${"doc" . $iii}->setFieldModifier('dateOrder', 'set');
                $docArray[] = ${"doc" . $iii};
                $ii++;
                $iii++;
            }
            $update->addDocuments($docArray);
            $update->addCommit();
            $result = $client->update($update);
        }
        return TRUE;
    }
    
    /*
     * $startDate = date('Y-m-d', $this->startTimestamp);
		$endDate = date('Y-m-d', $this->endTimestamp);
		$startTime = date('H:i', $this->startTimestamp);
		$endTime = date('H:i', $this->endTimestamp);
     */
    
    
    function handleChar($comp)
    {
        if($comp) {
            //$special_characters = array('(',')','/','\\','&','!','.','-','+');
            $comp = str_replace(chr(11),'',$comp);
        }
        return $comp;
    }

    
    
    function lreplace($search, $replace, $subject){
   	$pos = strrpos($subject, $search);
   	if($pos !== false){
            $subject = substr_replace($subject, $replace, $pos, strlen($search));
   	}
   	return $subject;
    }
    
   
    private function debug($inputArray)
    {
        echo '<pre>';
        print_r($inputArray);
        echo '</pre>';
    }
    
    
    
    
}