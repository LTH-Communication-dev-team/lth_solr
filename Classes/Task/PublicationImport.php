<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Lth\Lthsolr\Task;

use TYPO3\CMS\Core\Utility\GeneralUtility;

class PublicationImport extends \TYPO3\CMS\Scheduler\Task\AbstractTask {
	
    function execute()
    {
        error_reporting(E_ALL ^ E_NOTICE);
        
	$executionSucceeded = FALSE;
        
        require(__DIR__.'/init.php');
        $maximumrecords = 20;
        $numberofloops = 40;
        
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
        
        $dbhost = $settings['dbhost'];
        $db = $settings['db'];
        $user = $settings['user'];
        $pw = $settings['pw'];

        $con = mysqli_connect($dbhost, $user, $pw, $db) or die("44; ".mysqli_error());
        
        $client = new \Solarium\Client($config);
        
        //Get last modified
        $query = $client->createSelect();
        $query->setQuery('docType:publication');
        //$query->addSort('changed', $query::SORT_DESC);
        $query->setStart(0)->setRows(1);
        $response = $client->select($query);
        $idArray = array();
        $numFound = $response->getNumFound();
        foreach ($response as $document) {
            $lastModified = $document->changed;
        }

        $buffer = $client->getPlugin('bufferedadd');
        $buffer->setBufferSize(200);

        $current_date = gmDate("Y-m-d\TH:i:s\Z");
        
        $heritageArray = $this->getHeritage($con);
        
        /*$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery("COUNT(DISTINCT msg) AS nor","tx_devlog_dump","");
        $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
        $numFound = $row['nor'];
        $GLOBALS['TYPO3_DB']->sql_free_result($res);*/
        
        //$startFromHere = $numFound;
        $startFromHere = 0;
        //$executionSucceeded = $this->jsonTest();
	//$executionSucceeded = $this->getPublications($config, $client, $buffer, $current_date, $maximumrecords, $numberofloops, $settings, $heritageArray, $startFromHere, $lastModified, $syslang);
      	//$executionSucceeded = $this->updateAtoms($config, $client, $buffer, $current_date, $maximumrecords, $numberofloops, $settings, $heritageArray, $startFromHere, $lastModified, $syslang);
        //$executionSucceeded = $this->compare($config, $client, $buffer, $current_date, $maximumrecords, $numberofloops, $settings, $heritageArray, $startFromHere, $lastModified, $syslang);
$executionSucceeded = $this->getCiteBib($config, $client, $buffer, $current_date, $maximumrecords, $numberofloops, $settings, $heritageArray, $startFromHere, $lastModified, $syslang);
	return $executionSucceeded;
    }
    
    function jsonTest()
    {
        $numberofloops = 10;
        $startFromHere = 0;
        $maximumrecords = 20;
        for($i = 0; $i < 10; $i++) {
            $startrecord = $startFromHere + ($i * $maximumrecords);
            //$xmlpath = "https://lucris.lub.lu.se/ws/rest/publication?uuids.uuid=73b902e4-1c54-49f7-9a5c-68f78498b237&rendering=xml_long";
            $xmlpath = "https://lucris.lub.lu.se/ws/rest/publication?window.size=$maximumrecords&window.offset=$startrecord&orderBy.property=created&rendering=xml_long";
            //echo $xmlpath;
            $xml = @file_get_contents($xmlpath);
            //$xmlNode = simplexml_load_string($xml);
            //$numberofloops = ceil($xmlNode->children('core', true)->count / 20);
            $arrayData = '';
            //$arrayData = $this->xmlToArray($xmlNode);
            $GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_devlog_atom', array('msg' => $xmlpath));
            //echo json_encode($arrayData, JSON_PRETTY_PRINT);
        }
        return TRUE;
    }
    
function xmlToArray($xml, $options = array()) {
    $defaults = array(
        'namespaceSeparator' => ':',//you may want this to be something other than a colon
        'attributePrefix' => '@',   //to distinguish between attributes and nodes with the same name
        'alwaysArray' => array(),   //array of xml tag names which should always become arrays
        'autoArray' => true,        //only create arrays for tags which appear more than once
        'textContent' => '$',       //key used for the text content of elements
        'autoText' => true,         //skip textContent key if node has no attributes or child nodes
        'keySearch' => false,       //optional search and replace on tag and attribute names
        'keyReplace' => false       //replace values for above search values (as passed to str_replace())
    );
    $options = array_merge($defaults, $options);
    $namespaces = $xml->getDocNamespaces();
    $namespaces[''] = null; //add base (empty) namespace
 
    //get attributes from all namespaces
    $attributesArray = array();
    foreach ($namespaces as $prefix => $namespace) {
        foreach ($xml->attributes($namespace) as $attributeName => $attribute) {
            //replace characters in attribute name
            if ($options['keySearch']) $attributeName =
                    str_replace($options['keySearch'], $options['keyReplace'], $attributeName);
            $attributeKey = $options['attributePrefix']
                    . ($prefix ? $prefix . $options['namespaceSeparator'] : '')
                    . $attributeName;
            $attributesArray[$attributeKey] = (string)$attribute;
        }
    }
 
    //get child nodes from all namespaces
    $tagsArray = array();
    foreach ($namespaces as $prefix => $namespace) {
        foreach ($xml->children($namespace) as $childXml) {
            //recurse into child nodes
            $childArray = $this->xmlToArray($childXml, $options);
            list($childTagName, $childProperties) = each($childArray);
 
            //replace characters in tag name
            if ($options['keySearch']) $childTagName =
                    str_replace($options['keySearch'], $options['keyReplace'], $childTagName);
            //add namespace prefix, if any
            if ($prefix) $childTagName = $prefix . $options['namespaceSeparator'] . $childTagName;
 
            if (!isset($tagsArray[$childTagName])) {
                //only entry with this key
                //test if tags of this type should always be arrays, no matter the element count
                $tagsArray[$childTagName] =
                        in_array($childTagName, $options['alwaysArray']) || !$options['autoArray']
                        ? array($childProperties) : $childProperties;
            } elseif (
                is_array($tagsArray[$childTagName]) && array_keys($tagsArray[$childTagName])
                === range(0, count($tagsArray[$childTagName]) - 1)
            ) {
                //key already exists and is integer indexed array
                $tagsArray[$childTagName][] = $childProperties;
            } else {
                //key exists so convert to integer indexed array with previous value in position 0
                $tagsArray[$childTagName] = array($tagsArray[$childTagName], $childProperties);
            }
        }
    }
 
    //get text content of node
    $textContentArray = array();
    $plainText = trim((string)$xml);
    if ($plainText !== '') $textContentArray[$options['textContent']] = $plainText;
 
    //stick it all together
    $propertiesArray = !$options['autoText'] || $attributesArray || $tagsArray || ($plainText === '')
            ? array_merge($attributesArray, $tagsArray, $textContentArray) : $plainText;
 
    //return node as array
    return array(
        $xml->getName() => $propertiesArray
    );
}
    
    function compare($config, $client, $buffer, $current_date, $maximumrecords, $numberofloops, $settings, $heritageArray, $startFromHere, $lastModified, $syslang)
    {
        $idArray = array();
        $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery("msg","tx_devlog_ids","","","","");
        while ($row = $GLOBALS["TYPO3_DB"]->sql_fetch_assoc($res)) {
            $idArray[] = $row["msg"];
        }
        //print_r($idArray);
        $query = $client->createSelect();
        $query->setQuery('docType:publication');
        //$query->addSort('changed', $query::SORT_DESC);
        $query->setStart(0)->setRows(10000);
        //Nästa körning är $query->setStart(10001)->setRows(10000);
        $response = $client->select($query);
        
        $numFound = $response->getNumFound();
        foreach ($response as $document) {
            $id = $document->id;
           // echo $id;
            if(in_array($id, $idArray)) {
                $GLOBALS['TYPO3_DB']->exec_INSERTquery("tx_devlog_ids", "msg='$id'", array("msg" => $id."_ok"));
            }
        }
        return true;
    }
    
    function updateAtoms($config, $client, $buffer, $current_date, $maximumrecords, $numberofloops, $settings, $heritageArray, $startFromHere, $lastModified, $syslang)
    {
        $heritageArray = $heritageArray[0];
        //$this->debug($heritageArray[0]);
        $varArray = array('publication-base_uk','stab');
        
        $update = $client->createUpdate();

        //for($i = 0; $i < $numberofloops; $i++) {
        for($i = 0; $i < 100; $i++) {
            //echo $i.':'. $numberofloops . '<br />';
            
            $startrecord = 1950 + intval($startFromHere) + ($i * $maximumrecords);
            if($startrecord > 0) $startrecord++;
            //$xmlpath = "https://lucris.lub.lu.se/ws/rest/publication?window.size=$maximumrecords&window.offset=$startrecord&orderBy.property=id&rendering=xml_long";
            $xmlpath = "https://lucris.lub.lu.se/ws/rest/publication?window.size=$maximumrecords&window.offset=$startrecord&orderBy.property[0]=publicationYearMonthDay&orderBy.property[0].descending=false";
            //$xmlpath = "https://lucris.lub.lu.se/ws/rest/publication?modifiedDate.fromDate=$lastModified&window.size=$maximumrecords&window.offset=$startrecord&rendering=xml_long";
            //$xmlpath = "https://lucris.lub.lu.se/ws/rest/publication?uuids.uuid=c9c61408-4194-4b81-adc6-a15f4529b0bf&rendering=xml_long";
            //$xmlpath = "https://lucris.lub.lu.se/ws/rest/publication?typeClassificationUris.uri=/dk/atira/pure/researchoutput/researchoutputtypes/contributiontojournal/article&window.size=20&rendering=BIBTEX";
            
            $xml = @file_get_contents($xmlpath);
            if(!$xml) {
                
                return TRUE;
            }
            try {
                $xml = simplexml_load_string($xml);
            } catch(Exception $e) {
                $GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_devlog_error', array('msg' => $xmlpath, 'crdate' => time()));
            }
                  
            
            if($xml->children('core', true)->count == 0) {
                return "no items";
            }

            //$numberofloops = ceil($xml->children('core', true)->count / 200);

            foreach($xml->xpath('//core:result//core:content') as $content) {
                $authorExternal = array();
                $authorExternalOrganisation = array();
                $authorOrganisation = array();
                $authorId = array();
                $authorName = array();
                $authorFirstName = array();
                $authorLastName = array();
                
                $id = (string)$content->attributes();

                $GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_devlog_atom', array('msg' => $id, 'crdate' => time()));
                
                //Authors
                foreach($varArray as $varVal) {
                    if($content->children($varVal,true)->persons) {
                        foreach($content->children($varVal,true)->persons->children('person-template',true)->personAssociation as $personAssociation) {
                            $authorExternalTemp = 0;
                            $authorIdTemp = "";
                            $authorNameTemp = "";
                            $authorFirstNameTemp = "";
                            $authorLastNameTemp = "";
                            $authorExternalOrganisationTemp = "";
                            $authorOrganisationTemp = "";
                            if($personAssociation->children('person-template',true)->person) {
                                $authorIdTemp = (string)$personAssociation->children('person-template',true)->person->attributes();
                                $authorNameTemp = (string)$personAssociation->children('person-template',true)->person->children('person-template',true)->name->children('core',true)->firstName . ' ' . (string)$personAssociation->children('person-template',true)->person->children('person-template',true)->name->children('core',true)->lastName;
                                $authorFirstNameTemp = (string)$personAssociation->children('person-template',true)->person->children('person-template',true)->name->children('core',true)->firstName;
                                $authorLastNameTemp = (string)$personAssociation->children('person-template',true)->person->children('person-template',true)->name->children('core',true)->lastName;
                                if($personAssociation->children('person-template',true)->person->children('person-template',true)->organisationAssociations->children('person-template',true)->organisationAssociation) {
                                    //->children('organisation-template',true)->external->children('person-template',true)->organisation
                                    $authorOrganisationTemp = (string)$personAssociation->children('person-template',true)->person->children('person-template',true)->organisationAssociations->children('person-template',true)->organisationAssociation->children('person-template',true)->organisation->children('organisation-template',true)->external->children('extensions-core',true)->sourceId;
                                }
                            } else if($personAssociation->children('person-template',true)->externalPerson) {
                                $authorExternalTemp = 1;
                                $authorIdTemp = (string)$personAssociation->children('person-template',true)->externalPerson->attributes();
                                $authorNameTemp = (string)$personAssociation->children('person-template',true)->externalPerson->children('externalperson-template',true)->name->children('core',true)->firstName . ' ' . (string)$personAssociation->children('person-template',true)->externalPerson->children('externalperson-template',true)->name->children('core',true)->lastName;
                                $authorFirstNameTemp = (string)$personAssociation->children('person-template',true)->externalPerson->children('externalperson-template',true)->name->children('core',true)->firstName;
                                $authorLastNameTemp = (string)$personAssociation->children('person-template',true)->externalPerson->children('externalperson-template',true)->name->children('core',true)->lastName;
                                $authorOrganisationTemp = '';
                            }
                            if($personAssociation->children('person-template',true)->externalOrganisation) {
                                $authorExternalOrganisationTemp = (string)$personAssociation->children('person-template',true)->externalOrganisation;
                            }
                            $authorExternal[] = $authorExternalTemp;
                            $authorId[] = $authorIdTemp;
                            $authorName[] = $authorNameTemp;
                            $authorFirstName[] = $authorFirstNameTemp;
                            $authorLastName[] = $authorLastNameTemp;
                            $authorExternalOrganisation[] = $authorExternalOrganisationTemp;
                            if($authorOrganisationTemp) {
                                $heritage = array();
                                $heritage[] = $authorOrganisationTemp;
                                $parent = $heritageArray[$authorOrganisationTemp];
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
                                if($heritage) {
                                    array_filter($heritage);
                                    $authorOrganisationTemp = implode(',',array_unique($heritage));
                                }
                            }
                            $authorOrganisation[] = $authorOrganisationTemp;
                        }
                    }
                }
                
                ${"doc"} = $update->createDocument();
                ${"doc"}->setKey('id', $id);
                ${"doc"}->addField('authorOrganisation', $authorOrganisation);
                ${"doc"}->setFieldModifier('authorOrganisation', 'set');
                ${"doc"}->addField('appKey', 'lthsolr');
                ${"doc"}->setFieldModifier('appKey', 'set');
                ${"doc"}->addField('type', 'publication');
                ${"doc"}->setFieldModifier('type', 'set');
                $docArray[] = ${"doc"};
                
            }
            $update->addDocuments($docArray);
            $update->addCommit();
            $result = $client->update($update);
            $docArray = array();
        }
        return TRUE;
    }
    

    function getPublications($config, $client, $buffer, $current_date, $maximumrecords, $numberofloops, $settings, $heritageArray, $startFromHere, $lastModified, $syslang)
    {
        $heritageArray = $heritageArray[0];
        //$this->debug($heritageArray[0]);
        $varArray = array('publication-base_uk','stab');

        for($i = 0; $i < $numberofloops; $i++) {
            
            $startrecord = $startFromHere + ($i * $maximumrecords);

            //$xmlpath = "https://lucris.lub.lu.se/ws/rest/publication?modifiedDate.fromDate=$lastModified&window.size=$maximumrecords&window.offset=$startrecord&orderBy.property=created&rendering=xml_long";
            $xmlpath = "https://lucris.lub.lu.se/ws/rest/publication?window.size=$maximumrecords&window.offset=$startrecord&orderBy.property=created&rendering=xml_long";
            //$xmlpath = "https://lucris.lub.lu.se/ws/rest/publication?modifiedDate.fromDate=$lastModified&window.size=$maximumrecords&window.offset=$startrecord&rendering=xml_long";
            //$xmlpath = "https://lucris.lub.lu.se/ws/rest/publication?uuids.uuid=73b902e4-1c54-49f7-9a5c-68f78498b237&rendering=xml_long";
            //$xmlpath = "https://lucris.lub.lu.se/ws/rest/publication?typeClassificationUris.uri=/dk/atira/pure/researchoutput/researchoutputtypes/contributiontojournal/article&window.size=20&rendering=BIBTEX";
    
            $xml = @file_get_contents($xmlpath);
          

            $xml = @simplexml_load_string($xml);
            //$GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_devlog', array('msg' => var_dump($xml), 'crdate' => time()));
            if(!$xml || $xml==="") {
                return TRUE;
            }            
            if($xml->children('core', true)->count == 0) {
                return TRUE;
            }

            $numberofloops = ceil($xml->children('core', true)->count / 20);

            foreach($xml->xpath('//core:result//core:content') as $content) {
                $id = '';
                $type = '';
                $portalUrl = '';
                $created = '';
                $modified = '';
                //$title = array();
                $abstract_en = '';
                $abstract_sv = '';
                $authorExternal = array();
                $authorExternalOrganisation = array();
                $authorOrganisation = array();
                $authorId = array();
                $authorName = array();
                $authorFirstName = array();
                $authorLastName = array();
                $organisationId = array();
                $organisationName_en = array();
                $organisationName_sv = array();
                $externalOrganisationsName = array();
                $externalOrganisationsId = array();
                $language_en = array();
                $language_sv = array();
                $pages = '';
                $numberOfPages = '';
                $volume = '';
                $journalNumber = '';
                $journalTitle = '';
                $publicationDateYear = '';
                $publicationDateMonth = '';
                $publicationDateDay = '';
                $peerReview = '';
                $doi = '';
                $publicationType_en = '';
                $publicationType_sv = '';
                $publicationTypeUri = '';
                $standardCategory = '';
                $organisationSourceId = array();
                $hertitage = array();
                $keywords_uka_en = array();
                $keywords_uka_sv = array();
                $keywords_user_en = array();
                $keywords_user_sv = array();
                $document_url = '';
                $document_title = '';
                $document_limitedVisibility = '';
                $hostPublicationTitle = '';
                $publisher = '';
                $heritage = array();
                $awardDate;
                $bibliographicalNote_sv;
                $bibliographicalNote_en;
                $issn = '';
                $printIsbns = '';
                $electronicIsbns = '';
                $abstract = '';
                $bibliographicalNote = '';
                $event = '';
                $eventCity = '';
                $eventCountry = '';
                $event_country_sv = '';
                $event_country_en = '';
                $keywordsUka = array();
                $keywordsUser = array();
                $language = '';
                $organisationName = '';
                $publicationStatus = '';
                $publicationType = '';
                $placeOfPublication = '';
                $edition = '';
                $supervisorName = '';
                
                //id
                $id = (string)$content->attributes();

                //portalUrl
                $portalUrl = (string)$content->children('core',true)->portalUrl;
                
                //awardDate
                $awardDate = (string)$content->children('stab',true)->awardDate;
               
                //type
                $type = (string)$content->children('core',true)->type;
                if($type) {
                    $type = (string)array_pop(explode('.', $type));
                }
                
                //created
                $created = (string)$content->children('core',true)->created;
                
                //modified
                $modified = (string)$content->children('core',true)->modified;

                //title
                if($content->children('publication-base_uk',true)->title) {
                    $document_title = (string)$content->children('publication-base_uk',true)->title;
                } else if($content->children('stab',true)->title) {
                    $document_title = (string)$content->children('stab',true)->title;
                }

                //abstract
                foreach($varArray as $varVal) {
                    if($content->children($varVal,true)->abstract) {
                        foreach($content->children($varVal,true)->abstract->children('core',true)->localizedString as $abstract) {
                            if($abstract->attributes()->locale == 'en_GB') {
                                $abstract_en = (string)$abstract;
                            }
                            if($abstract->attributes()->locale == 'sv_SE') {
                                $abstract_sv = (string)$abstract;
                            }
                        }
                    }
                //}
                
                //bibliographicalNote
                //foreach($varArray as $varVal) {
                    if($content->children($varVal, true)->bibliographicalNote) {
                        foreach($content->children($varVal, true)->bibliographicalNote->children('core', true)->localizedString as $localizedString) {
                            if($localizedString->attributes()->locale == 'en_GB') {
                                $bibliographicalNote_en = (string)$localizedString;
                            }
                            if($localizedString->attributes()->locale == 'sv_SE') {
                                $bibliographicalNote_sv = (string)$localizedString;
                            }
                        }
                    }
                //}
                
                //documents
                //foreach($varArray as $varVal) {
                    if($content->children($varVal,true)->documents) {
                        foreach($content->children($varVal,true)->documents->children('extension-core',true)->document as $document) {
                            $document_url = (string)$document->children('core',true)->url;
                            //$document_title[] = (string)$document->children('core',true)->title;
                            $document_limitedVisibility = (string)$document->children('core',true)->limitedVisibility;
                        }
                    }
                //}

                //Authors
                //foreach($varArray as $varVal) {
                    if($content->children($varVal,true)->persons) {
                        foreach($content->children($varVal,true)->persons->children('person-template',true)->personAssociation as $personAssociation) {
                            $authorExternalTemp = 0;
                            $authorIdTemp = "";
                            $authorNameTemp = "";
                            $authorFirstNameTemp = "";
                            $authorLastNameTemp = "";
                            $authorExternalOrganisationTemp = "";
                            $authorOrganisationTemp = "";
                            if($personAssociation->children('person-template',true)->person) {
                                $authorIdTemp = (string)$personAssociation->children('person-template',true)->person->attributes();
                                $authorNameTemp = (string)$personAssociation->children('person-template',true)->person->children('person-template',true)->name->children('core',true)->firstName . ' ' . (string)$personAssociation->children('person-template',true)->person->children('person-template',true)->name->children('core',true)->lastName;
                                $authorFirstNameTemp = (string)$personAssociation->children('person-template',true)->person->children('person-template',true)->name->children('core',true)->firstName;
                                $authorLastNameTemp = (string)$personAssociation->children('person-template',true)->person->children('person-template',true)->name->children('core',true)->lastName;
                                if($personAssociation->children('person-template',true)->person->children('person-template',true)->organisationAssociations->children('person-template',true)->organisationAssociation) { //->children('person-template',true)->organisation->children('organisation-template',true)->external) {
                                    $authorOrganisationTemp = (string)$personAssociation->children('person-template',true)->person->children('person-template',true)->organisationAssociations->children('person-template',true)->organisationAssociation->children('person-template',true)->organisation->children('organisation-template',true)->external->children('extensions-core',true)->sourceId;
                                }
                            } else if($personAssociation->children('person-template',true)->externalPerson) {
                                $authorExternalTemp = 1;
                                $authorIdTemp = (string)$personAssociation->children('person-template',true)->externalPerson->attributes();
                                $authorNameTemp = (string)$personAssociation->children('person-template',true)->externalPerson->children('externalperson-template',true)->name->children('core',true)->firstName . ' ' . (string)$personAssociation->children('person-template',true)->externalPerson->children('externalperson-template',true)->name->children('core',true)->lastName;
                                $authorFirstNameTemp = (string)$personAssociation->children('person-template',true)->externalPerson->children('externalperson-template',true)->name->children('core',true)->firstName;
                                $authorLastNameTemp = (string)$personAssociation->children('person-template',true)->externalPerson->children('externalperson-template',true)->name->children('core',true)->lastName;
                                $authorOrganisationTemp = '';
                            }
                            if($personAssociation->children('person-template',true)->externalOrganisation) {
                                $authorExternalOrganisationTemp = (string)$personAssociation->children('person-template',true)->externalOrganisation;
                            }
                            $authorExternal[] = $authorExternalTemp;
                            $authorId[] = $authorIdTemp;
                            $authorName[] = $authorNameTemp;
                            $authorFirstName[] = $authorFirstNameTemp;
                            $authorLastName[] = $authorLastNameTemp;
                            $authorExternalOrganisation[] = $authorExternalOrganisationTemp;
                            if($authorOrganisationTemp) {
                                $heritage = array();
                                $heritage[] = $authorOrganisationTemp;
                                $parent = $heritageArray[$authorOrganisationTemp];
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
                                if($heritage) {
                                    array_filter($heritage);
                                    $authorOrganisationTemp = implode(',',array_unique($heritage));
                                }
                            }
                            $authorOrganisation[] = $authorOrganisationTemp;
                        }
                    }
                //}

                //Organisations
                //foreach($varArray as $varVal) {
                    if($content->children($varVal,true)->organisations && $content->children($varVal,true)->organisations->children('organisation-template',true)->association) {
                        foreach($content->children($varVal,true)->organisations->children('organisation-template',true)->association as $association) {
                            $organisationId[] = (string)$association->children('organisation-template',true)->organisation->attributes();
                            foreach($association->children('organisation-template',true)->organisation->children('organisation-template',true)->name->children('core',true)->localizedString as $localizedString) {
                                //
                                //core:localizedString
                                //echo $localizedString->asXML();
                                if($localizedString->attributes()->locale == 'en_GB') {
                                    $organisationName_en[] = (string)$localizedString;
                                }
                                if($localizedString->attributes()->locale == 'sv_SE') {
                                    $organisationName_sv[] = (string)$localizedString;
                                }
                            }
                            $organisationSourceId[] = (string)$association->children('organisation-template',true)->organisation->children('organisation-template',true)->external->children('extensions-core',true)->sourceId;
                        }
                    }
                //}
                
                //Language
                //foreach($varArray as $varVal) {
                    if($content->children($varVal,true)->language) {
                        foreach($content->children($varVal,true)->language->children('core',true)->term->children('core',true)->localizedString as $localizedString) {
                            if($localizedString->attributes()->locale == 'en_GB') {
                                $language_en = (string)$localizedString;
                            }
                            if($localizedString->attributes()->locale == 'sv_SE') {
                                $language_sv = (string)$localizedString;
                            }
                        }
                    }
                //}
                
                //journal title
                //foreach($varArray as $varVal) {
                    if($content->children($varVal,true)->journal) {
                        if($content->children($varVal,true)->journal->children('journal-template',true)->journal) {
                            foreach($content->children($varVal,true)->journal->children('journal-template',true)->journal->children('journal-template',true)->titles->children('journal-template',true)->title as $jtitle) {
                                $journalTitle = (string)$jtitle->children('extensions-core',true)->string;
                            }
                        }
                    }
                //}
                
                //issn
                //foreach($varArray as $varVal) {
                    if($content->children($varVal,true)->journal) {
                        if($content->children($varVal,true)->journal->children('journal-template',true)->issn) {
                            foreach($content->children($varVal,true)->journal->children('journal-template',true)->issn as $issn) {
                                $issn = (string)$issn->children('extensions-core',true)->string;
                            }
                        }
                    }
                //}
                
                //isbn
                //foreach($varArray as $varVal) {
                    if($content->children($varVal,true)->journal) {
                        if($content->children($varVal,true)->journal->children('journal-template',true)->isbn) {
                            foreach($content->children($varVal,true)->journal->children('journal-template',true)->isbn as $isbn) {
                                $isbn = (string)$isbn->children('extensions-core',true)->string;
                            }
                        }
                    }
                //}

                //numberOfPages
                //foreach($varArray as $varVal) {
                    if($content->children($varVal,true)->numberOfPages) {
                        $numberOfPages = (string)$content->children($varVal,true)->numberOfPages;
                    }
                //}
                
                //Pages
                //foreach($varArray as $varVal) {
                    if($content->children($varVal,true)->pages) {
                        $pages = (string)$content->children($varVal,true)->pages;
                    }
                //}
                
                //Volume
                //foreach($varArray as $varVal) {
                    if($content->children($varVal,true)->volume) {
                        $volume = (string)$content->children($varVal,true)->volume;
                    }
                //}
                
                //journalNumber
                //foreach($varArray as $varVal) {
                    if($content->children($varVal,true)->journalNumber) {
                        $journalNumber = (string)$content->children($varVal,true)->journalNumber;
                    }
                //}
                
                //publicationStatus
                //foreach($varArray as $varVal) {
                    if($content->children($varVal,true)->publicationStatus) {
                        foreach($content->children($varVal,true)->publicationStatus->children('core',true)->term->children('core',true)->localizedString as $localizedString) {
                            if($localizedString->attributes()->locale == 'en_GB') {
                                $publicationStatus_en = (string)$localizedString;
                            }
                            if($localizedString->attributes()->locale == 'sv_SE') {
                                $publicationStatus_sv = (string)$localizedString;
                            }
                        }
                        $publicationStatus = (string)$content->children($varVal,true)->publicationStatus;
                    }
                //}
                
                //hostPublicationTitle
                //foreach($varArray as $varVal) {
                    if($content->children($varVal,true)->hostPublicationTitle) {
                        $hostPublicationTitle = (string)$content->children($varVal,true)->hostPublicationTitle;
                    }
                //}
                    
                //publishers isbn, issn
                //foreach($varArray as $varVal) {
                    if($content->children($varVal,true)->associatedPublisher) {
                        if($content->children($varVal,true)->associatedPublisher->children('publisher-template',true)->placeOfPublication) {
                            $placeOfPublication = (string)$content->children($varVal,true)->associatedPublisher->children('publisher-template',true)->placeOfPublication;
                        }
                        if($content->children($varVal,true)->associatedPublisher->children('publisher-template',true)->edition) {
                            $edition = (string)$content->children($varVal,true)->associatedPublisher->children('publisher-template',true)->edition;
                        }                        
                        if($content->children($varVal,true)->associatedPublisher->children('publisher-template',true)->printIsbns) {
                            foreach($content->children($varVal,true)->associatedPublisher->children('publisher-template',true)->printIsbns as $printIsbns) {
                                $printIsbns = (string)$printIsbns->children('core',true)->value;
                            }
                        }
                        if($content->children($varVal,true)->associatedPublisher->children('publisher-template',true)->electronicIsbns) {
                            foreach($content->children($varVal,true)->associatedPublisher->children('publisher-template',true)->electronicIsbns as $electronicIsbns) {
                                $electronicIsbns = (string)$electronicIsbns->children('core',true)->value;
                            }
                        }
                        if($content->children($varVal,true)->associatedPublisher->children('publisher-template',true)->publisher) {
                            foreach($content->children($varVal,true)->associatedPublisher->children('publisher-template',true)->publisher as $publisher) {
                                $publisher = (string)$publisher->children('publisher-template',true)->name;
                            }
                        }
                    }
                    
                //}
                
                //Publication- year, month, day
                //foreach($varArray as $varVal) {
                    if($content->children($varVal,true)->publicationDate) {
                        $publicationDateYear = (string)$content->children($varVal,true)->publicationDate->children('core',true)->year;
                        $publicationDateMonth =  (string)$content->children($varVal,true)->publicationDate->children('core',true)->month;
                        $publicationDateDay =  (string)$content->children($varVal,true)->publicationDate->children('core',true)->day;
                    }
                //}
                
                //peerReview
                //foreach($varArray as $varVal) {
                    if($content->children($varVal,true)->peerReview) {
                        $peerReview = (string)$content->children($varVal,true)->peerReview->children('extensions-core',true)->peerReviewed;
                    }
                //}
                
                //Doi
                //foreach($varArray as $varVal) {
                    if($content->children($varVal,true)->dois) {
                        $doi = (string)$content->children($varVal,true)->dois->children('core',true)->doi->children('core',true)->doi;
                    }
                //}
                
                //Publication type
                //foreach($varArray as $varVal) {
                    if($content->children($varVal,true)->typeClassification) {
                        foreach($content->children($varVal,true)->typeClassification->children('core',true)->term->children('core',true)->localizedString as $localizedString) {
                            if($localizedString->attributes()->locale == 'en_GB') {
                                $publicationType_en = (string)$localizedString;
                            }
                            if($localizedString->attributes()->locale == 'sv_SE') {
                                $publicationType_sv = (string)$localizedString;
                            }
                        }
                        $publicationTypeUri = (string)$content->children($varVal,true)->typeClassification->children('core',true)->uri;
                    }
                    
                }

                $heritage = array();
                foreach($organisationSourceId as $key1 => $value1) {
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

                //External organisations
                if($content->children('publication-base_uk',true)->associatedExternalOrganisations && $content->children('publication-base_uk',true)->associatedExternalOrganisations->children('externalorganisation-template',true)->externalOrganisation) {
                    foreach($content->children('publication-base_uk',true)->associatedExternalOrganisations as $associatedExternalOrganisations) {
                       $externalOrganisationsId[] = (string)$associatedExternalOrganisations->children('externalorganisation-template',true)->externalOrganisation->attributes();
                        if($associatedExternalOrganisations->children('externalorganisation-template',true)->externalOrganisation->children('externalorganisation-template',true)->name) {
                            $externalOrganisationsName[] = (string)$associatedExternalOrganisations->children('externalorganisation-template',true)->externalOrganisation->children('externalorganisation-template',true)->name;
                        }
                    }
                }

                //Keywords
                if($content->children('core',true)->keywordGroups && $content->children('core',true)->keywordGroups->children('core',true)->keywordGroup) {
                    foreach($content->children('core',true)->keywordGroups->children('core',true)->keywordGroup as $keywordGroup) {
                        if($keywordGroup->children('core',true)->keyword && $keywordGroup->children('core',true)->keyword->children('core',true)->target) {
                            if((string)$keywordGroup->children('core',true)->configuration->children('core',true)->logicalName == 'uka_full') {
                                foreach($keywordGroup->children('core',true)->keyword->children('core',true)->target->children('core',true)->term->children('core',true)->localizedString as $localizedString) {
                                    if($localizedString->attributes()->locale == 'en_GB') {
                                        $keywords_uka_en[] = (string)$localizedString;
                                    }
                                    if($localizedString->attributes()->locale == 'sv_SE') {
                                        $keywords_uka_sv[] = (string)$localizedString;
                                    }
                                }
                            }
                        } else if($keywordGroup->children('core',true)->keyword && $keywordGroup->children('core',true)->keyword->children('core',true)->userDefinedKeyword) {
                            foreach($keywordGroup->children('core',true)->keyword->children('core',true)->userDefinedKeyword as $userDefinedKeyword) {
                                if($userDefinedKeyword->attributes()->locale == 'en_GB') {
                                    foreach($userDefinedKeyword->children('core',true)->freeKeyword as $freeKeyword) {
                                        $keywords_user_en[] = (string)$freeKeyword;
                                    }
                                }
                                if($userDefinedKeyword->attributes()->locale == 'sv_SE') {
                                    foreach($userDefinedKeyword->children('core',true)->freeKeyword as $freeKeyword) {
                                        $keywords_user_sv[] = (string)$freeKeyword;
                                    }
                                }
                            }
                        }
                    }
                }
                
                //supervisorAdvisor
                if($content->children('stab',true)->supervisorAdvisor) {
                    if($content->children('stab',true)->supervisorAdvisor->children('stab', true)->classifiedInternalExternalPersonAssociation->children('stab',true)->person) {
                        foreach($content->children('stab',true)->supervisorAdvisor->children('stab', true)->classifiedInternalExternalPersonAssociation->children('stab',true)->person->children('person-template',true)->name as $supervisor) {
                            $supervisorName = (string)$supervisor->children('core', true)->firstName . (string)$supervisor->children('core', true)->lastName;
                        }
                    }
                }

                //event
                if($content->children('stab',true)->event) {
                    foreach($content->children('stab',true)->event->children('event-template',true)->title->children('core',true)->localizedString as $localizedString) {
                        if($localizedString->attributes()->locale == 'en_GB') {
                            $event_en = (string)$localizedString;
                        }
                        if($localizedString->attributes()->locale == 'sv_SE') {
                            $event_sv = (string)$localizedString;
                        }
                    }
                    $eventCity = $content->children('stab',true)->event->children('event-template',true)->city;
                    if($content->children('stab',true)->event->children('event-template',true)->country) {
                        foreach($content->children('stab',true)->event->children('event-template',true)->country->children('core',true)->localizedString as $localizedString) {
                            if($localizedString->attributes()->locale == 'en_GB') {
                                $event_country_en = (string)$localizedString;
                            }
                            if($localizedString->attributes()->locale == 'sv_SE') {
                                $event_country_sv = (string)$localizedString;
                            }
                        }
                    }
                }
                
                //CITE OCH BIBTEX
                $citeArray = array("Standard" => "standard", "Harvard" => "harvard", "APA" => "apa", "Vancouver" => "vancouver", "Author" => "author", "RIS" => "RIS", "Bibtex" => "BIBTEX");
                $cite = "";
                $bibtex = "";
                foreach($citeArray as $citebibKey => $citebib) {
                    $citebibxmlpath = "https://lucris.lub.lu.se/ws/rest/publication?uuids.uuid=$id&typeClassificationUris.uri=$publicationTypeUri&rendering=$citebib";
                    for( $ii=0; $ii<9; $ii++ ) { 
                        $citebibxml = @file_get_contents($citebibxmlpath);
                        if( $citebibxml !== FALSE ) { 
                            break;
                        }
                    }
                    $citebibxml = str_replace('$$$', '', $citebibxml);
                    $citebibxml = preg_replace('/<div/', '$$$<div', $citebibxml, 1);
                    $citebibxml = $this->lreplace('</div>', '</div>$$$', $citebibxml);
                    $citebibxmlArray = explode('$$$', $citebibxml);
                    //$citebibxml = simplexml_load_string($citebibxml);
                    if($citebib==="BIBTEX") {
                        $bibtex = "<h3>$citebibKey</h3>" . $citebibxmlArray[1];
                    } else {
                        $cite .= "<h3>$citebibKey</h3>" . $citebibxmlArray[1];
                    }
                } 
                
                if($syslang==="sv") {
                    $abstract = $abstract_sv;
                    $bibliographicalNote = $bibliographicalNote_sv;
                    $event = $event_sv;
                    $eventCountry = $event_country_sv;
                    $keywordsUka = $keywords_uka_sv;
                    $keywordsUser = $keywords_user_sv;
                    $language = $language_sv;
                    $organisationName = $organisationName_sv;
                    $publicationStatus = $publicationStatus_sv;
                    $publicationType = $publicationType_sv;
                } else {
                    $abstract = $abstract_en;
                    $bibliographicalNote = $bibliographicalNote_en;
                    $event = $event_en;
                    $eventCountry = $event_country_en;
                    $keywordsUka = $keywords_uka_en;
                    $keywordsUser = $keywords_user_en;
                    $language = $language_en;
                    $organisationName = $organisationName_en;
                    $publicationStatus = $publicationStatus_en;
                    $publicationType = $publicationType_en;
                }
                
                if(!$abstract && $abstract_en) {
                    $abstract = $abstract_en;
                }
                if(!$abstract && $abstract_sv) {
                    $abstract = $abstract_sv;
                }
                if(!$keywordsUser && $keywords_user_en) {
                    $keywordsUser = $keywords_user_en;
                }
                if(!$keywordsUser && $keywords_user_sv) {
                    $keywordsUser = $keywords_user_sv;
                }

                $data = array(
                    'id' => $id,
                    'abstract' => $abstract,
                    'authorExternal' => $authorExternal,
                    'authorExternalOrganisation' => $authorExternalOrganisation,
                    'authorId' => $authorId,
                    'authorName' => array_unique($authorName),
                    //'authorName_sort' => array_unique($authorName),
                    'authorFirstName' => $authorFirstName,
                    'authorLastName' => $authorLastName,
                    'authorOrganisation' => $authorOrganisation,
                    'awardDate' => gmdate('Y-m-d\TH:i:s\Z', strtotime($awardDate)),
                    'bibliographicalNote' => $bibliographicalNote,
                    //'bibliographicalNote_en' => $bibliographicalNote_en,
                    'docType' => 'publication',
                    'documentUrl' => $document_url,
                    'documentTitle' => $document_title,
                    'documentLimitedVisibility' => $document_limitedVisibility,     
                    'doi' => $doi,
                    'externalOrganisationsName' => $externalOrganisationsName,
                    'externalOrganisationsId' => $externalOrganisationsId,
                    'event' => $event,
                    //'event_sv' => $event_sv,
                    'eventCity' => $eventCity,
                    'eventCountry' => $event_country,
                    //'event_country_sv' => $event_country_sv,
                    'hostPublicationTitle' => $hostPublicationTitle,
                    'journalNumber' => $journalNumber,
                    'journalTitle' => $journalTitle,
                    'keywordsUka' => $keywordsUka,
                    //'keywords_uka_sv' => $keywords_uka_sv,
                    'keywordsUser' => $keywordsUser,
                    //'keywords_user_sv' => $keywords_user_sv,
                    'language' => $language,
                    //'language_sv' => $language_sv,
                    'numberOfPages' => $numberOfPages,
                    'organisationId' => $organisationId,
                    'organisationName' => $organisationName,
                    //'organisationName_sv' => $organisationName_sv,
                    'organisationSourceId' => $organisationSourceId, 
                    'pages' => $pages,
                    'peerReview' => $peerReview,
                    'portalUrl' => $portalUrl,
                    'publicationStatus' => $publicationStatus,
                    //'publicationStatus_sv' => $publicationStatus_sv,
                    'publicationDateYear' => $publicationDateYear,
                    'publicationDateMonth' => $publicationDateMonth,
                    'publicationDateDay' => $publicationDateDay,
                    'publicationType' => $publicationType,
                    //'publicationType_sv' => $publicationType_sv,
                    'publicationTypeUri' => $publicationTypeUri,
                    'publisher' => $publisher,
                    //'title' => $title,
                    //'title_sort' => $title,
                    'type' => $type,
                    'volume' => $volume,
                    'standardCategory' => $publicationType,
                    //'standard_category_sv' => $publicationType_sv,
                    'issn' => $issn,
                    'printIsbns' => $printIsbns,
                    'electronicIsbns' => $electronicIsbns,
                    'boost' => '1.0',
                    'date' => gmdate('Y-m-d\TH:i:s\Z', strtotime($created)),
                    'changed' => gmdate('Y-m-d\TH:i:s\Z', strtotime($modified)),
                    'digest' => md5($id),
                    'bibtex' => $bibtex,
                    'cite' => $cite,
                    'appKey' => 'lthsolr',
                    'placeOfPublication' => $placeOfPublication,
                    'edition' => $edition,
                    'supervisorName' => $supervisorName
                );
                // $this->debug($data);
                //$GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_devlog', array('msg' => print_r($data,true), 'crdate' => time()));
                $GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_devlog_atom', array('msg' => $id, 'crdate' => time()));
                $buffer->createDocument($data);
            }
        }
        $buffer->commit();
        return TRUE;
    }
    
    
    function lreplace($search, $replace, $subject){
   	$pos = strrpos($subject, $search);
   	if($pos !== false){
            $subject = substr_replace($subject, $replace, $pos, strlen($search));
   	}
   	return $subject;
    }
    
    
    private function getHeritage($con)
    {
        $heritageArray = array();
        
        $sql = "SELECT orgid, parent FROM lucache_vorg";
        
        $res = mysqli_query($con, $sql);
        
        while ($row = mysqli_fetch_array($res, MYSQLI_ASSOC)) {
            $heritageArray[$row['orgid']] = $row['parent'];
        }
        return array($heritageArray);
    }
    
    
    private function debug($inputArray)
    {
        echo '<pre>';
        print_r($inputArray);
        echo '</pre>';
    }
    
    
    function getFiles($config, $client, $buffer, $current_date, $maximumrecords, $numberofloops, $settings, $heritageArray, $startFromHere, $lastModified, $syslang)
    {
        $files1 = scandir('/var/www/html/typo3/fileadmin/lucrisdump');
        $startFromHere = 20 * (intval(count($files1))-2);
        
        //$varArray = array('publication-base_uk','stab');

        for($i = 0; $i <= 200; $i++) {
            
            $startrecord = $startFromHere + ($i * 20);
            $fileName = $startrecord . '.xml';
            //$xmlpath = "https://lucris.lub.lu.se/ws/rest/publication?modifiedDate.fromDate=$lastModified&window.size=$maximumrecords&window.offset=$startrecord&orderBy.property=created&rendering=xml_long";
            $xmlpath = "https://lucris.lub.lu.se/ws/rest/publication?window.size=20&window.offset=$startrecord&orderBy.property=created&rendering=xml_long";
            //$xmlpath = "https://lucris.lub.lu.se/ws/rest/publication?modifiedDate.fromDate=$lastModified&window.size=$maximumrecords&window.offset=$startrecord&rendering=xml_long";
            //$xmlpath = "https://lucris.lub.lu.se/ws/rest/publication?uuids.uuid=73b902e4-1c54-49f7-9a5c-68f78498b237&rendering=xml_long";
            //$xmlpath = "https://lucris.lub.lu.se/ws/rest/publication?typeClassificationUris.uri=/dk/atira/pure/researchoutput/researchoutputtypes/contributiontojournal/article&window.size=20&rendering=BIBTEX";

            $xml = @file_get_contents($xmlpath);
//$GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_devlog_dump', array('location' => $xmlpath, 'msg' => (string)$xml, 'crdate' => time()));
            //echo $xmlpath;
            $xml = @simplexml_load_string($xml);
            
            $xml->asXml('/var/www/html/typo3/fileadmin/lucrisdump/' . $fileName);

            /*foreach($xml->xpath('//core:result//core:content') as $content) {
   
                //id
                $id = (string)$content->attributes();
                $publicationTypeUri = '';
                foreach($varArray as $varVal) {
                    if($content->children($varVal,true)->typeClassification) {
                        foreach($content->children($varVal,true)->typeClassification->children('core',true)->term->children('core',true)->localizedString as $localizedString) {
                            if($localizedString->attributes()->locale == 'en_GB') {
                                $publicationType_en = (string)$localizedString;
                            }
                            if($localizedString->attributes()->locale == 'sv_SE') {
                                $publicationType_sv = (string)$localizedString;
                            }
                        }
                        $publicationTypeUri = (string)$content->children($varVal,true)->typeClassification->children('core',true)->uri;
                    }
                }

                if($publicationTypeUri) {
                    //CITE OCH BIBTEX
                    $citeArray = array("Standard" => "standard", "Harvard" => "harvard", "APA" => "apa", 
                        "Vancouver" => "vancouver", "Author" => "author", "RIS" => "RIS", "Bibtex" => "BIBTEX");
                    $cite = "";
                    $bibtex = "";
                    foreach($citeArray as $citebibKey => $citebib) {
                        $citebibxmlpath = "https://lucris.lub.lu.se/ws/rest/publication?uuids.uuid=$id&typeClassificationUris.uri=$publicationTypeUri&rendering=$citebib";
                        for( $ii=0; $ii<9; $ii++ ) { 
                            $citebibxml = @file_get_contents($citebibxmlpath);
                            if( $citebibxml !== FALSE ) { 
                                break;
                            }
                        }
                        if($citebibxml) {
                            $GLOBALS['TYPO3_DB']->exec_INSERTquery('uid_citebib', array('location' => $id, 'msg' => $citebibxml, 'type' => $citebib));
                        }
                    }
                }
            }*/
        }
        return TRUE;
    }
    
    
    function getCiteBib($config, $client, $buffer, $current_date, $maximumrecords, $numberofloops, $settings, $heritageArray, $startFromHere, $lastModified, $syslang)
    {
        $directory = '/var/www/html/typo3/fileadmin/lucrisdump';
        $varArray = array('publication-base_uk','stab');
        
        $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('filename','tx_devlog_dump',"done=0",'filename','filename','0,20');
        while ($row = $GLOBALS["TYPO3_DB"]->sql_fetch_assoc($res)) {
            $filename = $row['filename'];
            $xml = @file_get_contents($directory . '/' . $filename);
            $xml = @simplexml_load_string($xml);
            
            foreach($xml->xpath('//core:result//core:content') as $content) {
   
                $id = (string)$content->attributes();
                //echo $id;
                //$GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_devlog_dump', array('uuid' => $id, 'crdate' => time()));
                $publicationTypeUri = '';
                foreach($varArray as $varVal) {
                    if($content->children($varVal,true)->typeClassification) {
                        foreach($content->children($varVal,true)->typeClassification->children('core',true)->term->children('core',true)->localizedString as $localizedString) {
                            if($localizedString->attributes()->locale == 'en_GB') {
                                $publicationType_en = (string)$localizedString;
                            }
                            if($localizedString->attributes()->locale == 'sv_SE') {
                                $publicationType_sv = (string)$localizedString;
                            }
                        }
                        $publicationTypeUri = (string)$content->children($varVal,true)->typeClassification->children('core',true)->uri;
                    }
                }

                if($publicationTypeUri) {
                    $GLOBALS['TYPO3_DB']->exec_UPDATEquery('tx_devlog_dump', "uuid='$id'", array('uri' => $publicationTypeUri));
                    $citeArray = array("Standard" => "standard", "Harvard" => "harvard", "APA" => "apa", 
                        "Vancouver" => "vancouver", "Author" => "author", "RIS" => "RIS", "Bibtex" => "BIBTEX");
                    $cite = "";
                    $bibtex = "";
                    foreach($citeArray as $citebibKey => $citebib) {
                        $citebibxmlpath = "https://lucris.lub.lu.se/ws/rest/publication?uuids.uuid=$id&typeClassificationUris.uri=$publicationTypeUri&rendering=$citebib";
                        //print '<p>' . $citebibxmlpath . '</p>';
                        $citebibxml = @file_get_contents($citebibxmlpath);
                        $citebibxml = @simplexml_load_string($citebibxml);
                        //print '<p>' . $citebibxml . '</p>';
                        if($citebibxml) {
                            switch($citebib) {
                                case 'standard':
                                    $citebibxml->asXml('/var/www/html/typo3/fileadmin/lucrisdump/' . $id . '_standard.xml');
                                    break;
                                case 'harvard':
                                    $citebibxml->asXml('/var/www/html/typo3/fileadmin/lucrisdump/' . $id . '_harvard.xml');
                                    break;
                                case 'apa':
                                    $citebibxml->asXml('/var/www/html/typo3/fileadmin/lucrisdump/' . $id . '_apa.xml');
                                    break;
                                case 'vancouver':
                                    $citebibxml->asXml('/var/www/html/typo3/fileadmin/lucrisdump/' . $id . '_vancouver.xml');
                                    break;
                                case 'author':
                                    $citebibxml->asXml('/var/www/html/typo3/fileadmin/lucrisdump/' . $id . '_author.xml');
                                    break;
                                case 'RIS':
                                    $citebibxml->asXml('/var/www/html/typo3/fileadmin/lucrisdump/' . $id . '_ris.xml');
                                    break;
                                case 'BIBTEX':
                                    $citebibxml->asXml('/var/www/html/typo3/fileadmin/lucrisdump/' . $id . '_bibtex.xml');
                                    break;
                                }
                        }
                    }
                }
            }
            $GLOBALS['TYPO3_DB']->exec_UPDATEquery('tx_devlog_dump', "filename='$filename'", array('done' => 1));
        }

        return TRUE;
    }
}