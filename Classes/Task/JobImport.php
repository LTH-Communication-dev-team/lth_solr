<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Lth\Lthsolr\Task;

use TYPO3\CMS\Core\Utility\GeneralUtility;

class JobImport extends \TYPO3\CMS\Scheduler\Task\AbstractTask {
	
    function execute()
    {
        error_reporting(E_ALL ^ E_NOTICE);
        
	$executionSucceeded = FALSE;
        
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
        
	$executionSucceeded = $this->getJobs($config, $syslang);
        
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
        
        $executionSucceeded = $this->getJobs($config, $syslang);
                
	return $executionSucceeded;
    }

    
    public function getJobs($config, $syslang)
    {
        $client = new \Solarium\Client($config);
        $update = $client->createUpdate();
        $buffer = $client->getPlugin('bufferedadd');
        $buffer->setBufferSize(250);
        
        if($syslang==='sv') {
            $jobpath = "http://feeds.mynetworkglobal.com/json/lu/position/?subcompany=7895";
        } else if($syslang==='en') {
            $jobpath = "http://feeds.mynetworkglobal.com/en/json/lu/position/?subcompany=7895";
        }
        $jobs = @file_get_contents($jobpath);
        
        if($jobs) {
            $jobArray = json_decode($jobs,true);
            
            /*echo '<pre>';
            print_r($jobArray);
            echo '</pre>';*/

            foreach($jobArray['positions'] as $key => $job) {
                $abstract = '';
                $endDate = '';
                $hidden = '';
                $hours = '';
                $id = '';
                $jobTitle = '';
                $jobType = array();
                $loginAndApplyURI = '';
                $organisationTitle = '';
                $published = '';
                $refNr = '';
                
                $admission = '';
                $pay = '';
                $nr_pos = '';
                $working_hours = '';
                $town = '';
                $county = '';
                $country = '';
                $position_contact = array();
                $jobPositionContact = array();
                $union_representative = array();
                $jobUnionRepresentative = array();
                $published = '';
                $lastUpdate = '';
                
                $abstract = $job['description'];
                $endDate = $job['ends'];
                $hidden = $job['hidden_externally'];
                $hours = $job['hours'];
                $id = $job['id'];
                $jobTitle = $job['title'];
                $jobType[] = $job['type'];
                $jobType[] = $job['jobtype']['name'];
                $jobType[] = $job['admission'];
                $jobType[] = $job['pay'];
                $jobType[] = $job['nr_pos'];
                $jobType[] = $job['working_hours'];
                $jobType[] = $job['town'];
                $jobType[] = $job['county'];
                $jobType[] = $job['country'];
                $lastUpdate = $job['lastUpdate'];
                if($lastUpdate) {
                    $lastUpdate = substr($lastUpdate,0,10);
                }
                $position_contact = $job['position_contact'];
                if($position_contact) {
                    if(is_array($position_contact)) {
                        foreach ($position_contact as $pkey => $pvalue) {
                            if(is_array($pvalue)) {
                                array_pop($pvalue);
                                $jobPositionContact[] = implode(', ', array_filter($pvalue));
                            }
                        }
                    }
                }
                $union_representative = $job['union_representative'];
                if($union_representative) {
                    if(is_array($union_representative)) {
                        foreach ($union_representative as $ukey => $uvalue) {
                            if(is_array($uvalue)) {
                                array_pop($uvalue);
                                $jobUnionRepresentative[] = implode(', ', array_filter($uvalue));
                            }
                        }
                    }
                }
                
                $loginAndApplyURI = $job['loginAndApplyURI'];
                if($loginAndApplyURI) $loginAndApplyURI = urlencode($loginAndApplyURI);
                $organisationTitle = $job['org_desc'];
                $published = $job['published'];
                $refNr = $job['ref_nr'];

                $data = array(
                    'abstract' => $abstract,
                    'appKey' => 'lth_solr',
                    'boost' => '1.0',
                    'changed' => date('Y-m-d\TH:i:s\Z', strtotime($lastUpdate)),
                    'createdDate' => date('Y-m-d\TH:i:s\Z', time()),
                    'docType' => 'job',
                    'endDate' => date('Y-m-d\TH:i:s\Z', strtotime($endDate)),
                    'hidden' => $hidden,
                    'hours' => $hours,
                    'id' => 'job_' . $id,
                    'jobPositionContact' => $jobPositionContact,
                    'jobUnionRepresentative' => $jobUnionRepresentative,
                    'jobTitle' => $jobTitle,
                    'jobType' => $jobType,
                    'loginAndApplyURI' => $loginAndApplyURI,
                    'organisationTitle' => $organisationTitle,
                    'published' => date('Y-m-d\TH:i:s\Z', strtotime($published)),
                    'refNr' => $refNr,
                    'type' => 'job'
                );
                try {
                    $buffer->createDocument($data);
                } catch(Exception $e) {
                    echo 'Message: ' .$e->getMessage();
                }
            }
        }
        $buffer->commit();
        $update->addCommit();
        $client->update($update);

        return TRUE;
    }
    
    public function addComma($input)
    {
        if($input) {
            $input .= ", " + $input;
        }
        return $input;
    }
    
    
    public function getPrograms($client, $syslang)
    {
        $buffer = $client->getPlugin('bufferedadd');
        $buffer->setBufferSize(250);
        //$GLOBALS['TYPO3_DB']->store_lastBuiltQuery = 1;
        $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery("P.ProgramID, P.ProgramEng, P.ProgramSve, P.ProgramKod, K.kursOrtEng, kursOrtSve", 
                "LubasPP_dbo.Program P JOIN LubasPP_dbo.KursOrt K ON kursOrtKod = Ort", "P.Nedlagd = 0", "", "", "");
        //$GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_devlog', array('msg' => $GLOBALS['TYPO3_DB']->debug_lastBuiltQuery, 'crdate' => time()));
        while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
            $data = array(
                'appKey' => 'lth_solr',
                'type' => 'program',
                'id' => 'program_' . $row['ProgramID'],
                'docType' => 'program',
                'title' =>  $this->langChoice(array($row['ProgramSve'], $row['ProgramEng']), $syslang),
                'courseCode' => $row['ProgramKod'],
                'courseLocation' =>  $this->langChoice(array($row['kursOrtSve'], $row['kursOrtEng']), $syslang),
                'boost' => '1.0'
            );
            try {
                $buffer->createDocument($data);
            } catch(Exception $e) {
                echo 'Message: ' .$e->getMessage();
            }
        }
        $GLOBALS['TYPO3_DB']->sql_free_result($res);
        $buffer->commit();
        return TRUE;
    }
    
    
    function clearIndex($settings)
    {
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
        $update = $client->createUpdate();
        $update->addDeleteQuery('docType:course');
        $update->addCommit();
        $result = $client->update($update);
        
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
        $update = $client->createUpdate();
        $update->addDeleteQuery('docType:course');
        $update->addCommit();
        $result = $client->update($update);
        
        return TRUE;
    }
    
    
    function langChoice($inputArray, $syslang)
    {
        if($syslang === "sv") {
            return $inputArray[0];
        } else {
            return $inputArray[1];
        }
    }
}