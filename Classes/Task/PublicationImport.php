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
        
        $startFromHere = $numFound;
        
	$executionSucceeded = $this->getPublications($config, $client, $buffer, $current_date, $maximumrecords, $numberofloops, $settings, $heritageArray, $startFromHere, $lastModified, $syslang);
        
	return $executionSucceeded;
    }
    

    function getPublications($config, $client, $buffer, $current_date, $maximumrecords, $numberofloops, $settings, $heritageArray, $startFromHere, $lastModified, $syslang)
    {
        $heritageArray = $heritageArray[0];
        //$this->debug($heritageArray[0]);
        $varArray = array('publication-base_uk','stab');

        //for($i = 0; $i < $numberofloops; $i++) {
        for($i = 0; $i < $numberofloops; $i++) {
            //echo $i.':'. $numberofloops . '<br />';
            
            $startrecord = $startFromHere + ($i * $maximumrecords);
            if($startrecord > 0) $startrecord++;
            
            $lucrisId = $settings['solrLucrisId'];
            $lucrisPw = $settings['solrLucrisPw'];

            $xmlpath = "https://lucris.lub.lu.se/ws/rest/publication?window.size=$maximumrecords&window.offset=$startrecord&orderBy.property=id&rendering=xml_long";
            //$xmlpath = "https://lucris.lub.lu.se/ws/rest/publication?modifiedDate.fromDate=$lastModified&window.size=$maximumrecords&window.offset=$startrecord&rendering=xml_long";
            //$xmlpath = "https://lucris.lub.lu.se/ws/rest/publication?uuids.uuid=c9c61408-4194-4b81-adc6-a15f4529b0bf&rendering=xml_long";
            //$xmlpath = "https://lucris.lub.lu.se/ws/rest/publication?typeClassificationUris.uri=/dk/atira/pure/researchoutput/researchoutputtypes/contributiontojournal/article&window.size=20&rendering=BIBTEX";
            
            $xml = file_get_contents($xmlpath);         
            $xml = simplexml_load_string($xml);        
            
            if($xml->children('core', true)->count == 0) {
                return "no items";
            }

            //$numberofloops = ceil($xml->children('core', true)->count / 20);

            foreach($xml->xpath('//core:result//core:content') as $content) {
                $id;
                $type;
                $portalUrl;
                $created;
                $modified;
                //$title = array();
                $abstract_en = '';
                $abstract_sv = '';
                $authorExternal = array();
                $authorExternalOrganisation = array();
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
                $pages;
                $numberOfPages;
                $volume;
                $journalNumber;
                $journalTitle;
                $publicationStatus_en;
                $publicationStatus_sv;
                $publicationDateYear;
                $publicationDateMonth;
                $publicationDateDay;
                $peerReview;
                $doi;
                $publicationType_en = '';
                $publicationType_sv = '';
                $publicationTypeUri = '';
                $standard_category_sv;
                $standard_category_en;
                $organisationSourceId = array();
                $hertitage = array();
                $keywords_uka_en = array();
                $keywords_uka_sv = array();
                $keywords_user_en = array();
                $keywords_user_sv = array();
                $document_url = "";
                $document_title = "";
                $document_limitedVisibility = "";
                $hostPublicationTitle;
                $publisher;
                $event_en;
                $event_sv;
                $event_city;
                $event_country_en;
                $event_country_sv;
                $heritage = array();
                $awardDate;
                $bibliographicalNote_sv;
                $bibliographicalNote_en;
                $issn = '';
                $isbn = '';
                $abstract = '';
                $bibliographicalNote = '';
                $event = '';
                $eventCountry = '';
                $keywordsUka = array();
                $keywordsUser = array();
                $language = '';
                $organisationName = '';
                $publicationStatus = '';
                $publicationType = '';
                
                //id
                $id = (string)$content->attributes();

                $GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_devlog', array('msg' => $id, 'crdate' => time()));
                //portalUrl
                $portalUrl = (string)$content->children('core',true)->portalUrl;
                
                //awardDate
                $awardDate = (string)$content->children('stab',true)->awardDate;
                
                //bibliographicalNote
                foreach($varArray as $varVal) {
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
                }
                
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
                }

                //documents
                foreach($varArray as $varVal) {
                    if($content->children($varVal,true)->documents) {
                        foreach($content->children($varVal,true)->documents->children('extension-core',true)->document as $document) {
                            $document_url = (string)$document->children('core',true)->url;
                            //$document_title[] = (string)$document->children('core',true)->title;
                            $document_limitedVisibility = (string)$document->children('core',true)->limitedVisibility;
                        }
                    }
                }

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
                            if($personAssociation->children('person-template',true)->person) {
                                $authorIdTemp = (string)$personAssociation->children('person-template',true)->person->attributes();
                                $authorNameTemp = (string)$personAssociation->children('person-template',true)->person->children('person-template',true)->name->children('core',true)->firstName . ' ' . (string)$personAssociation->children('person-template',true)->person->children('person-template',true)->name->children('core',true)->lastName;
                                $authorFirstNameTemp = (string)$personAssociation->children('person-template',true)->person->children('person-template',true)->name->children('core',true)->firstName;
                                $authorLastNameTemp = (string)$personAssociation->children('person-template',true)->person->children('person-template',true)->name->children('core',true)->lastName;
                            } else if($personAssociation->children('person-template',true)->externalPerson) {
                                $authorExternalTemp = 1;
                                $authorIdTemp = (string)$personAssociation->children('person-template',true)->externalPerson->attributes();
                                $authorNameTemp = (string)$personAssociation->children('person-template',true)->externalPerson->children('externalperson-template',true)->name->children('core',true)->firstName . ' ' . (string)$personAssociation->children('person-template',true)->externalPerson->children('externalperson-template',true)->name->children('core',true)->lastName;
                                $authorFirstNameTemp = (string)$personAssociation->children('person-template',true)->externalPerson->children('externalperson-template',true)->name->children('core',true)->firstName;
                                $authorLastNameTemp = (string)$personAssociation->children('person-template',true)->externalPerson->children('externalperson-template',true)->name->children('core',true)->lastName;
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
                        }
                    }
                }

                //Organisations
                foreach($varArray as $varVal) {
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
                }

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

                //Language
                foreach($varArray as $varVal) {
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
                }
                
                //journal title
                foreach($varArray as $varVal) {
                    if($content->children($varVal,true)->journal) {
                        if($content->children($varVal,true)->journal->children('journal-template',true)->journal) {
                            foreach($content->children($varVal,true)->journal->children('journal-template',true)->journal->children('journal-template',true)->titles->children('journal-template',true)->title as $jtitle) {
                                $journalTitle = (string)$jtitle->children('extensions-core',true)->string;
                            }
                        }
                    }
                }
                
                //issn
                foreach($varArray as $varVal) {
                    if($content->children($varVal,true)->journal) {
                        if($content->children($varVal,true)->journal->children('journal-template',true)->issn) {
                            foreach($content->children($varVal,true)->journal->children('journal-template',true)->issn as $issn) {
                                $issn = (string)$issn->children('extensions-core',true)->string;
                            }
                        }
                    }
                }
                
                //isbn
                foreach($varArray as $varVal) {
                    if($content->children($varVal,true)->journal) {
                        if($content->children($varVal,true)->journal->children('journal-template',true)->isbn) {
                            foreach($content->children($varVal,true)->journal->children('journal-template',true)->isbn as $isbn) {
                                $isbn = (string)$isbn->children('extensions-core',true)->string;
                            }
                        }
                    }
                }

                //numberOfPages
                foreach($varArray as $varVal) {
                    if($content->children($varVal,true)->numberOfPages) {
                        $numberOfPages = (string)$content->children($varVal,true)->numberOfPages;
                    }
                }
                
                //Pages
                foreach($varArray as $varVal) {
                    if($content->children($varVal,true)->pages) {
                        $pages = (string)$content->children($varVal,true)->pages;
                    }
                }
                
                //Volume
                foreach($varArray as $varVal) {
                    if($content->children($varVal,true)->volume) {
                        $volume = (string)$content->children($varVal,true)->volume;
                    }
                }
                
                //journalNumber
                foreach($varArray as $varVal) {
                    if($content->children($varVal,true)->journalNumber) {
                        $journalNumber = (string)$content->children($varVal,true)->journalNumber;
                    }
                }
                
                //publicationStatus
                foreach($varArray as $varVal) {
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
                }
                
                //hostPublicationTitle
                foreach($varArray as $varVal) {
                    if($content->children($varVal,true)->hostPublicationTitle) {
                        $hostPublicationTitle = (string)$content->children($varVal,true)->hostPublicationTitle;
                    }
                }
                    
                //publishers
                foreach($varArray as $varVal) {
                    if($content->children($varVal,true)->associatedPublishers) {
                        foreach($content->children($varVal,true)->associatedPublishers->children('publisher-template',true)->publisher as $publisher) {
                            $publisher = (string)$publisher->children('publisher-template',true)->name;
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
                    $event_city = $content->children('stab',true)->event->children('event-template',true)->city;
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
                
                //Publication- year, month, day
                foreach($varArray as $varVal) {
                    if($content->children($varVal,true)->publicationDate) {
                        $publicationDateYear = (string)$content->children($varVal,true)->publicationDate->children('core',true)->year;
                        $publicationDateMonth =  (string)$content->children($varVal,true)->publicationDate->children('core',true)->month;
                        $publicationDateDay =  (string)$content->children($varVal,true)->publicationDate->children('core',true)->day;
                    }
                }
                
                //peerReview
                foreach($varArray as $varVal) {
                    if($content->children($varVal,true)->peerReview) {
                        $peerReview = (string)$content->children($varVal,true)->peerReview->children('extensions-core',true)->peerReviewed;
                    }
                }
                
                //Doi
                foreach($varArray as $varVal) {
                    if($content->children($varVal,true)->dois) {
                        $doi = (string)$content->children($varVal,true)->dois->children('core',true)->doi->children('core',true)->doi;
                    }
                }
                
                //Publication type
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
                
                //CITE OCH BIBTEX
                $citeArray = array("Standard" => "standard", "Harvard" => "harvard", "APA" => "apa", "Vancouver" => "vancouver", "Author" => "author", "RIS" => "RIS", "Bibtex" => "BIBTEX");
                $cite = "";
                $bibtex = "";
                foreach($citeArray as $citebibKey => $citebib) {
                    $citebibxmlpath = "https://lucris.lub.lu.se/ws/rest/publication?uuids.uuid=$id&typeClassificationUris.uri=/dk/atira/pure/researchoutput/researchoutputtypes/contributiontojournal/article&rendering=$citebib";
                    $citebibxml = file_get_contents($citebibxmlpath);
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
                
                //$title_unique = preg_replace("/[^a-z0-9\s]/i", "", $title);
                //$title_unique = preg_replace("/\s\s+/", " ", $title_unique); 
                
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
                    'eventCity' => $event_city,
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
                    'isbn' => $isbn,
                    'boost' => '1.0',
                    'date' => gmdate('Y-m-d\TH:i:s\Z', strtotime($created)),
                    'changed' => gmdate('Y-m-d\TH:i:s\Z', strtotime($modified)),
                    'digest' => md5($id),
                    'bibtex' => $bibtex,
                    'cite' => $cite,
                    'appKey' => 'lthsolr'
                );
                // $this->debug($data);
                //$GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_devlog', array('msg' => print_r($data,true), 'crdate' => time()));
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
}