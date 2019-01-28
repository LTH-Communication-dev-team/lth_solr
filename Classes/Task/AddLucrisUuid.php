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

	$executionSucceeded = $this->getPersonUuid();
        //$executionSucceeded = $this->getImageData();
        
	return $executionSucceeded;
    }

    
    function getImageData()
    {
        $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery("id,lucris_photo","tx_lthsolr_lucrisdata","lucris_photo!='' AND lucris_photo_width=0");
        while ($row = $GLOBALS["TYPO3_DB"]->sql_fetch_assoc($res)) {
            $id = $row['id'];
            $lucris_photo = $row['lucris_photo'];
        
            if($lucris_photo) {
                list($width, $height) = @getimagesize($lucris_photo);
                if($width && $height) {
                    $GLOBALS['TYPO3_DB']->exec_UPDATEquery('tx_lthsolr_lucrisdata', 'id='.intval($id), array('lucris_photo_width' => $width, 'lucris_photo_height' => $height));
                }
            }
        }    
        return TRUE;
        //$GLOBALS['TYPO3_DB']->sql_free_result($res);
    }
    
    
    function getPersonUuid()
    {
        $current = array();
        
        $current = array();
        $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery("typo3_id,lucris_id", "tx_lthsolr_lucrisdata", "lucris_type='staff'");
        while ($row = $GLOBALS["TYPO3_DB"]->sql_fetch_assoc($res)) {
            $current[] = $row['typo3_id'];
        }
        $GLOBALS['TYPO3_DB']->sql_free_result($res);
       
        require(__DIR__.'/init.php');
        $maximumrecords = 100;
        $numberofloops = 1;
        $current_date = gmDate("Y-m-d\TH:i:s\Z");
        
        $settings = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['lth_solr']);
              
        $dbhost = $settings['dbhost'];
        $db = $settings['db'];
        $user = $settings['user'];
        $pw = $settings['pw'];
        $solrLucrisApiKey = $settings['solrLucrisApiKey'];
        $solrLucrisApiVersion = $settings['solrLucrisApiVersion'];
        
        $startfromhere = 0;

        for($i = 0; $i < $numberofloops; $i++) {

            $startrecord = $startfromhere + ($i * $maximumrecords);
            if($startrecord > 0) $startrecord++;

            $xmlpath = "https://lucris.lub.lu.se/ws/api/$solrLucrisApiVersion/persons/?apiKey=$solrLucrisApiKey&size=$maximumrecords&offset=$startrecord";

            $xml = file_get_contents($xmlpath);

            $xml = simplexml_load_string($xml);	

            $numberofloops = ceil($xml->count / $maximumrecords);

            $ii = 0;
            $docArray = array();
            
            foreach($xml->xpath('//result//person') as $content) {
                $ii++;
                $sourceId = (string)$content->externalableInfo->sourceId;
                $uuid = (string)$content->attributes();
                $photo = '';
                $profileInformation = '';
                
                //profileInformation
                $profileInformationArray = array();
                if($content->profileInformations) {
                    foreach($content->profileInformations->profileInformation as $profileInformation) {
                        $profileInformationArray[(string)$profileInformation->attributes()->type][(string)$profileInformation->attributes()->locale] = (string)$profileInformation;
                    }
                    $profileInformation = json_encode($profileInformationArray);
                }

                if($content->profilePhotos->profilePhoto) {
                    $photo = (string)$content->profilePhotos->profilePhoto->attributes()->url;
                }
                
                if($sourceId && $uuid) {
                    $sourceIdArray = explode('@', $sourceId);
                    $sourceId = $sourceIdArray[0];
                   
                    $id = (string)$sourceId;
                    if(in_array($id, $current)) {
                        $GLOBALS['TYPO3_DB']->exec_UPDATEquery('tx_lthsolr_lucrisdata', "typo3_id='$id'", array('lucris_id' => $uuid, 'lucris_photo' => $photo, 'lucris_profile_information' => $profileInformation, 'lucris_type' => 'staff'));
                    } else {
                        $GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_lthsolr_lucrisdata', array('typo3_id' => $id, 'lucris_id' => $uuid, 'lucris_photo' => $photo, 'lucris_profile_information' => $profileInformation, 'lucris_type' => 'staff'));
                    }
                }
            }
        }   
        return TRUE;
    }
}