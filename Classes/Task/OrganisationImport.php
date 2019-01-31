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

        $dbhost = $settings['dbhost'];
        $db = $settings['db'];
        $user = $settings['user'];
        $pw = $settings['pw'];

        $con = mysqli_connect($dbhost, $user, $pw, $db) or die("60; ".mysqli_error());
       
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
        
        $sql = "SELECT orgid, homepage, phone, street, city, postal_address, maildelivery FROM lucache_vorg";
        
        $res = mysqli_query($con, $sql) or die("99; ".mysqli_error());

        while ($row = mysqli_fetch_array($res, MYSQLI_ASSOC)) {
            $orgid = $row['orgid'];
            $organisationArray[$orgid]['homepage'] = $row['homepage'];
            $organisationArray[$orgid]['phone'] = $row['phone'];
            $organisationArray[$orgid]['street'] = $row['street'];
            $organisationArray[$orgid]['city'] = $row['city'];
            $organisationArray[$orgid]['postal_address'] = $row['postal_address'];
            $organisationArray[$orgid]['maildelivery'] = $row['maildelivery'];
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

            foreach($xml->xpath('//result//content') as $content) {
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
                $portalUrl = (string)$content->portalUrl;
                
                //name
                if($content->name) {
                    foreach($content->name->localizedString as $localizedString) {
                        if($localizedString->attributes()->locale == 'en_GB') {
                            $name_en = (string)$localizedString;
                        }
                        if($localizedString->attributes()->locale == 'sv_SE') {
                            $name_sv = (string)$localizedString;
                        }
                    }
                }
                
                //typeClassification
                if($content->typeClassification) {
                    foreach($content->typeClassification->term->localizedString as $localizedString) {
                        if($localizedString->attributes()->locale == 'en_GB') {
                            $typeClassification_en = (string)$localizedString;
                        }
                        if($localizedString->attributes()->locale == 'sv_SE') {
                            $typeClassification_sv = (string)$localizedString;
                        }
                    }
                }
                
                //parents
                if($content->organisations) {
                    foreach($content->organisations->organisation as $organisation) {
                        $parents[] = (string)$organisation->attributes();
                        if($organisation->name->localizedString->attributes()->locale == 'en_GB') {
                            $parentName_en[] = (string)$organisation->name->localizedString;
                        }
                        if($organisation->name->localizedString->attributes()->locale == 'sv_SE') {
                            $parentName_sv[] = (string)$organisation->name->localizedString;
                        }
                    }
                }
                
                //organisationSourceId
                if($content->external) {
                    $organisationSourceId[] = $content->external->sourceId;
                    $orgid = (string)$content->external->sourceId;
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
                $mailDelivery = (string)$organisationArray[$orgid]['maildelivery'];
                $organisationCity = $organisationArray[$orgid]['city'];
                $organisationPhone = $organisationArray[$orgid]['phone'];
                $organisationPostalAddress = $organisationArray[$orgid]['postal_address'];
                $organisationStreet = $organisationArray[$orgid]['street'];
                $homepage = $organisationArray[$orgid]['homepage'];
                
                $data = array(
                    'appKey' => 'lthsolr',
                    'id' => $id,
                    'docType' => 'organisation',
                    'homepage' => $homepage,
                    'mailDelivery' => $mailDelivery,
                    'organisationCity' => $organisationCity,
                    'organisationParent' => $parents,
                    'organisationParentName' => $parentName,
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
}
