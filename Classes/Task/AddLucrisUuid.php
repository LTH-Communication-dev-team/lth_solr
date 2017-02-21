<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Lth\Lthsolr\Task;

use TYPO3\CMS\Core\Utility\GeneralUtility;

class AddLucrisUuid extends \TYPO3\CMS\Scheduler\Task\AbstractTask {
	
    function execute()
    {
        error_reporting(E_ALL ^ E_NOTICE);
        
	$executionSucceeded = FALSE;

	$executionSucceeded = $this->getPersonUuid($client);
        
	return $executionSucceeded;
    }

    function getPersonUuid()
    {
        require(__DIR__.'/init.php');
        $maximumrecords = 20;
        $numberofloops = 1;
        $current_date = gmDate("Y-m-d\TH:i:s\Z");
        
        $settings = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['lth_solr']);
              
        $dbhost = $settings['dbhost'];
        $db = $settings['db'];
        $user = $settings['user'];
        $pw = $settings['pw'];
        
        $con = mysqli_connect($dbhost, $user, $pw, $db) or die("48; ".mysqli_error());
     
        $i=0;
        $startrecord = 0;
             
        $lucrisId = $settings['solrLucrisId'];
        $lucrisPw = $settings['solrLucrisPw'];
        
        //$sql = 'TRUNCATE TABLE tx_lthsolr_lucrisdata';
        //$res = $GLOBALS['TYPO3_DB'] -> sql_query($sql);
        
        $startfromhere = 0;

        for($i = 0; $i < $numberofloops; $i++) {

            $startrecord = $startfromhere + ($i * $maximumrecords);
            if($startrecord > 0) $startrecord++;

            $xmlpath = "https://$lucrisId:$lucrisPw@lucris.lub.lu.se/ws/rest/person?window.size=$maximumrecords&window.offset=$startrecord&orderBy.property=id&rendering=xml_long";
            //$xmlpath = "https://$lucrisId:$lucrisPw@lucris.lub.lu.se/ws/rest/person?uuids.uuid=a432d89f-3d61-427c-ac2d-604f8ba57441&rendering=xml_long";

            try {
                $xml = file_get_contents($xmlpath);
            } catch(Exception $e) {
                echo 'Message: ' .$e->getMessage();
            }
            
            $xml = simplexml_load_string($xml);	


            $numberofloops = ceil($xml->children('core', true)->count / 20);

            $ii = 0;
            $docArray = array();
            //$idarray = array();
            
            foreach($xml->xpath('//core:result//core:content') as $content) {
                $ii++;
                $sourceId = (string)$content->children('stab1',true)->external->children('extensions-core',true)->sourceId;
                $uuid = (string)$content->attributes();
                $photo = '';
                $profileInformation = '';

                if($content->children('stab1',true)->photos) {
                    $photo = (string)$content->children('stab1',true)->photos->children('core',true)->file->children('core',true)->url;
                }
                

                //profileInformation
                $profileInformationArray = array();
                if($content->children('stab1',true)->profileInformation) {
                    foreach($content->children('stab1', true)->profileInformation->children('extensions-core', true)->customField->children('extensions-core',true)->value->children('core',true)->localizedString as $localizedString) {
                        //echo '126';
                        if($localizedString->attributes()->locale == 'en_GB') {
                            $profileInformationArray['en'] = (string)$localizedString;
                        }
                        if($localizedString->attributes()->locale == 'sv_SE') {
                            $profileInformationArray['sv'] = (string)$localizedString;
                        }
                    }
                    if(count($profileInformationArray) > 0) {
                        $profileInformation = json_encode($profileInformationArray);
                    }
                }

                if($sourceId && $uuid) {
                    $sourceIdArray = explode('@', $sourceId);
                    $sourceId = $sourceIdArray[0];
                   
                    $id = (string)$sourceId;
                    $GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_lthsolr_lucrisdata', array('typo3_id' => $id, 'lucris_id' => $uuid, 'lucris_photo' => $photo, 'lucris_profile_information' => $profileInformation));
                }
            }

        }
        
        
        return TRUE;
    }
    
}