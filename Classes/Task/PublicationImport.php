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
        
        $executionSucceeded = $this->moveFiles('en');
        return $executionSucceeded;
        
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
        
        $dbhost = $settings['dbhost'];
        $db = $settings['db'];
        $user = $settings['user'];
        $pw = $settings['pw'];

        $con = mysqli_connect($dbhost, $user, $pw, $db) or die("44; ".mysqli_error());
        
        $client = new \Solarium\Client($config);
        
        //Get last modified
        $query = $client->createSelect();
        $query->setQuery('docType:publication');
        $query->addSort('changed', $query::SORT_DESC);
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
        
        $heritageArray = $this->getHeritage($client);
        /*echo '<pre>';
        print_r($heritageArray);
        echo '</pre>';
        return TRUE;*/
        $mode = '';
        //$startFromHere = $numFound;
        $startFromHere = 0;
        $mode = 'reindex'; //'' 'reindex' 'files'
        if($mode==='' && $mode!='files') {
            $executionSucceeded = $this->getFiles($buffer, $maximumrecords, $numberofloops, $heritageArray, $startFromHere, $lastModified, $syslang);
        } else if($mode==='files') {
            //$executionSucceeded = $this->getOrgFiles($buffer, $maximumrecords, $numberofloops, $heritageArray, $startFromHere, $lastModified, $syslang);
            $executionSucceeded = $this->getOrganisations($buffer, $maximumrecords, $numberofloops, $heritageArray, $startFromHere, $lastModified, $syslang, $mode);
            return TRUE;
        }
	if($executionSucceeded || $mode==='reindex') {
            $executionSucceeded = $this->getPublications($buffer, $maximumrecords, $numberofloops, $heritageArray, $startFromHere, $lastModified, $syslang, $mode);

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
            
            $executionSucceeded = $this->getPublications($buffer, $maximumrecords, $numberofloops, $heritageArray, $startFromHere, $lastModified, $syslang, $mode);
        }
        //$executionSucceeded = $this->updateAtoms($config, $client, $buffer, $current_date, $maximumrecords, $numberofloops, $settings, $heritageArray, $startFromHere, $lastModified, $syslang);
        //$executionSucceeded = $this->compare($config, $client, $buffer, $current_date, $maximumrecords, $numberofloops, $settings, $heritageArray, $startFromHere, $lastModified, $syslang);
        //$executionSucceeded = $this->getCiteBib($config, $client, $buffer, $current_date, $maximumrecords, $numberofloops, $settings, $heritageArray, $startFromHere, $lastModified, $syslang);
        return $executionSucceeded;
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
        $update->addDeleteQuery('docType:publication');
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
        $update->addDeleteQuery('docType:publication');
        $update->addCommit();
        $result = $client->update($update);
        
        return TRUE;
    }
    
    
    function moveFiles($syslang)
    {
        $directory = '/var/www/html/typo3/lucrisdump';
        if($syslang==='sv') {
            $fileArray = scandir($directory . '/indexedfiles');
        } else  {
            $fileArray = scandir($directory . '/svindexedfiles');
        }
        foreach ($fileArray as $key => $filename) {
        
             if($syslang==='sv') {
                    @rename($directory . '/indexedfiles/' . $filename, $directory . '/svindexedfiles/' . $filename);
                } else {
                    @rename($directory . '/svindexedfiles/' . $filename, $directory . '/indexedfiles/' . $filename);
                }

        }
        return TRUE;
    }
    
    
    function getPublications($buffer, $maximumrecords, $numberofloops, $heritageArray, $startFromHere, $lastModified, $syslang, $mode)
    {
        $heritageArray = $heritageArray[0];
        //$this->debug($heritageArray[0]);
        $varArray = array('publication-base_uk','stab');
        $directory = '/var/www/html/typo3/lucrisdump';
        if($mode==='reindex' && $syslang==='sv') {
            $fileArray = scandir($directory . '/indexedfiles');
        } else if($mode==='reindex' && $syslang==='en') {
            $fileArray = scandir($directory . '/svindexedfiles');
        } else {
            $fileArray = scandir($directory . '/filestoindex');
        }
        //$filename = '0.xml';
        $fileArray = array_slice($fileArray, 2);

        foreach ($fileArray as $key => $filename) {
            
        //for($i = 0; $i < $numberofloops; $i++) {
            
            //$startrecord = $startFromHere + ($i * $maximumrecords);

            //$xmlpath = "https://lucris.lub.lu.se/ws/rest/publication?modifiedDate.fromDate=$lastModified&window.size=$maximumrecords&window.offset=$startrecord&orderBy.property=created&rendering=xml_long";
            //$xmlpath = "https://lucris.lub.lu.se/ws/rest/publication?window.size=$maximumrecords&window.offset=$startrecord&orderBy.property=created&rendering=xml_long";
            //$xmlpath = "https://lucris.lub.lu.se/ws/rest/publication?modifiedDate.fromDate=$lastModified&window.size=$maximumrecords&window.offset=$startrecord&rendering=xml_long";
            //$xmlpath = "https://lucris.lub.lu.se/ws/rest/publication?uuids.uuid=73b902e4-1c54-49f7-9a5c-68f78498b237&rendering=xml_long";
            //$xmlpath = "https://lucris.lub.lu.se/ws/rest/publication?typeClassificationUris.uri=/dk/atira/pure/researchoutput/researchoutputtypes/contributiontojournal/article&window.size=20&rendering=BIBTEX";
            if($mode==='reindex' && $syslang==='sv') {
                $xmlpath = $directory . '/indexedfiles/' . $filename;
            } else if($mode==='reindex' && $syslang==='en') {
                $xmlpath = $directory . '/svindexedfiles/' . $filename;
            } else {
                $xmlpath = $directory . '/filestoindex/' . $filename;
            }

            $xml = @file_get_contents($xmlpath);
            
            
$xmlPrefix = '<?xml version="1.0" encoding="UTF-8"?>';
$xmlPrefix .= '<publication-template:GetPublicationResponse xmlns:publication-template="http://atira.dk/schemas/pure4/wsdl/template/abstractpublication/stable"';
$xmlPrefix .= ' xmlns:core="http://atira.dk/schemas/pure4/model/core/stable"';
$xmlPrefix .= ' xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"';
$xmlPrefix .= ' xmlns:publication-base_uk="http://atira.dk/schemas/pure4/model/template/abstractpublication/stable"';
$xmlPrefix .= ' xmlns:extensions-core="http://atira.dk/schemas/pure4/model/core/extensions/stable"';
$xmlPrefix .= ' xmlns:person-template="http://atira.dk/schemas/pure4/model/template/abstractperson/stable"';
$xmlPrefix .= ' xmlns:organisation-template="http://atira.dk/schemas/pure4/model/template/abstractorganisation/stable"';
$xmlPrefix .= ' xmlns:extensions-base_uk="http://atira.dk/schemas/pure4/model/base_uk/extensions/stable"';
$xmlPrefix .= ' xmlns:externalorganisation-template="http://atira.dk/schemas/pure4/model/template/externalorganisation/stable"';
$xmlPrefix .= ' xmlns:journal-template="http://atira.dk/schemas/pure4/model/template/abstractjournal/stable"';
$xmlPrefix .= ' xmlns:externalperson-template="http://atira.dk/schemas/pure4/model/template/abstractexternalperson/stable"';
$xmlPrefix .= ' xmlns:publisher-template="http://atira.dk/schemas/pure4/model/template/abstractpublisher/stable"';
$xmlPrefix .= ' xmlns:event-template="http://atira.dk/schemas/pure4/model/template/abstractevent/stable" requestId="">';
$xmlPrefix .= '<core:result>';

$xmlSuffix = '</core:result></publication-template:GetPublicationResponse>';


            $xml = @simplexml_load_string($xmlPrefix . $xml . $xmlSuffix);
            
            //$numberofloops = ceil($xml->children('core', true)->count / 20);
                //$i=0;
            foreach($xml->xpath('//core:result//core:content') as $content) {
                $abstract = '';
                $abstract_en = '';
                $abstract_sv = '';
                $articleNumber = '';
                $attachmentUrl = '';
                $attachmentLimitedVisibility = '';
                $attachmentMimeType = '';
                $attachmentSize = '';
                $attachmentTitle = '';
                $authorExternal = array();
                $authorExternalOrganisation = array();
                $authorOrganisation = array();
                $authorId = array();
                $authorName = array();
                $authorFirstName = array();
                $authorLastName = array();
                $authorFirstNameExact = '';
                $authorLastNameExact = '';
                $awardDate;
                $bibliographicalNote_sv;
                $bibliographicalNote_en;
                $bibliographicalNote = '';
                $created = '';
                $documentTitle = '';
                $doi = '';
                $edition = '';
                $electronicIsbns = '';
                $event = '';
                $event_en = '';
                $event_sv = '';
                $eventCity = '';
                $eventCountry = '';
                $event_country_sv = '';
                $event_country_en = '';
                $externalOrganisationsName = array();
                $externalOrganisationsId = array();
                $heritage = array();
                $hostPublicationTitle = '';
                $id = '';
                $issn = '';
                $journalNumber = '';
                $journalTitle = '';
                $keywords_uka_en = array();
                $keywords_uka_sv = array();
                $keywords_user_en = array();
                $keywords_user_sv = array();
                $keywordsUka = array();
                $keywordsUser = array();
                $language = '';
                $language_en = array();
                $language_sv = array();
                $modified = '';
                $numberOfPages = '';
                $organisationId = array();
                $organisationName = '';
                $organisationName_en = array();
                $organisationName_sv = array();
                $organisationSourceId = array();
                $pages = '';
                $peerReview = '';
                $placeOfPublication = '';
                $portalUrl = '';
                $printIsbns = '';
                $publicationDateYear = '';
                $publicationDateMonth = '';
                $publicationDateDay = '';
                $publicationStatus = '';
                $publicationType = '';
                $publicationType_en = '';
                $publicationType_sv = '';
                $publicationTypeUri = '';
                $publisher = '';
                $standardCategory = '';
                $supervisorName = '';
                $type = '';
                $volume = '';

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
                    $documentTitle = (string)$content->children('publication-base_uk',true)->title;
                } else if($content->children('stab',true)->title) {
                    $documentTitle = (string)$content->children('stab',true)->title;
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
                
                    //bibliographicalNote
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
                
                    //documents
                    if($content->children($varVal,true)->documents) {
                        foreach($content->children($varVal,true)->documents->children('extensions-core',true)->document as $document) {
                            $attachmentMimeType = (string)$document->children('core',true)->mimeType;
                            $attachmentSize = (string)$document->children('core',true)->size;
                            $attachmentUrl = (string)$document->children('core',true)->url;
                            $attachmentTitle = (string)$document->children('core',true)->title;
                            $attachmentLimitedVisibility = (string)$document->children('core',true)->limitedVisibility->children('core',true)->visibility;
                        }
                    }
                    
                    //limitedVisibility
                    if($content->children($varVal,true)->limitedVisibility) {
                        $attachmentLimitedVisibility = (string)$content->children($varVal,true)->limitedVisibility->children('core',true)->visibility;
                    }
                    
                    //Authors
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
                    
                    //authorFirstNameExact
                    if($authorFirstName) {
                        foreach($authorFirstName as $afnKey => $afnValue) {
                            if($afnValue && $afnValue != '') {
                                $authorFirstNameExact = $afnValue;
                            }
                        }
                    }
                    
                    //authorLastNameExact
                    if($authorLastName) {
                        foreach($authorLastName as $alnKey => $alnValue) {
                            if($alnValue && $alnValue != '') {
                                $authorLastNameExact = $alnValue;
                            }
                        }
                    }

                    //Organisations
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

                    //Language
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

                    //journal title
                    if($content->children($varVal,true)->journal) {
                        if($content->children($varVal,true)->journal->children('journal-template',true)->journal) {
                            foreach($content->children($varVal,true)->journal->children('journal-template',true)->journal->children('journal-template',true)->titles->children('journal-template',true)->title as $jtitle) {
                                $journalTitle = (string)$jtitle->children('extensions-core',true)->string;
                            }
                        }
                    }

                    //issn
                    if($content->children($varVal,true)->journal) {
                        if($content->children($varVal,true)->journal->children('journal-template',true)->issn) {
                            foreach($content->children($varVal,true)->journal->children('journal-template',true)->issn as $issn) {
                                $issn = (string)$issn->children('extensions-core',true)->string;
                            }
                        }
                    }

                    //isbn
                    if($content->children($varVal,true)->journal) {
                        if($content->children($varVal,true)->journal->children('journal-template',true)->isbn) {
                            foreach($content->children($varVal,true)->journal->children('journal-template',true)->isbn as $isbn) {
                                $isbn = (string)$isbn->children('extensions-core',true)->string;
                            }
                        }
                    }

                    //numberOfPages
                    if($content->children($varVal,true)->numberOfPages) {
                        $numberOfPages = (string)$content->children($varVal,true)->numberOfPages;
                    }
                
                    //Pages
                    if($content->children($varVal,true)->pages) {
                        $pages = (string)$content->children($varVal,true)->pages;
                    }

                    //articleNumber
                    if($content->children($varVal,true)->articleNumber) {
                        $articleNumber = (string)$content->children($varVal,true)->articleNumber;
                    }

                    //Volume
                    if($content->children($varVal,true)->volume) {
                        $volume = (string)$content->children($varVal,true)->volume;
                    }

                    //journalNumber
                    if($content->children($varVal,true)->journalNumber) {
                        $journalNumber = (string)$content->children($varVal,true)->journalNumber;
                    }
                
                    //publicationStatus
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
                
                    //hostPublicationTitle
                    if($content->children($varVal,true)->hostPublicationTitle) {
                        $hostPublicationTitle = (string)$content->children($varVal,true)->hostPublicationTitle;
                    }
                    
                    //publishers isbn, issn: associatedPublisher
                    // WITHOUT S
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
                    // WITH S
                    if($content->children($varVal,true)->associatedPublishers) {
                        if($content->children($varVal,true)->associatedPublishers->children('publisher-template',true)->placeOfPublication) {
                            $placeOfPublication = (string)$content->children($varVal,true)->associatedPublishers->children('publisher-template',true)->placeOfPublication;
                        }
                        if($content->children($varVal,true)->associatedPublishers->children('publisher-template',true)->edition) {
                            $edition = (string)$content->children($varVal,true)->associatedPublishers->children('publisher-template',true)->edition;
                        }                        
                        if($content->children($varVal,true)->associatedPublishers->children('publisher-template',true)->printIsbns) {
                            foreach($content->children($varVal,true)->associatedPublishers->children('publisher-template',true)->printIsbns as $printIsbns) {
                                $printIsbns = (string)$printIsbns->children('core',true)->value;
                            }
                        }
                        if($content->children($varVal,true)->associatedPublishers->children('publisher-template',true)->electronicIsbns) {
                            foreach($content->children($varVal,true)->associatedPublishers->children('publisher-template',true)->electronicIsbns as $electronicIsbns) {
                                $electronicIsbns = (string)$electronicIsbns->children('core',true)->value;
                            }
                        }
                        if($content->children($varVal,true)->associatedPublishers->children('publisher-template',true)->publisher) {
                            foreach($content->children($varVal,true)->associatedPublishers->children('publisher-template',true)->publisher as $publisher) {
                                $publisher = (string)$publisher->children('publisher-template',true)->name;
                            }
                        }
                    }
                    
                    //Publication- year, month, day
                    if($content->children($varVal,true)->publicationDate) {
                        $publicationDateYear = (string)$content->children($varVal,true)->publicationDate->children('core',true)->year;
                        $publicationDateMonth =  (string)$content->children($varVal,true)->publicationDate->children('core',true)->month;
                        $publicationDateDay =  (string)$content->children($varVal,true)->publicationDate->children('core',true)->day;
                    }
                
                    //peerReview
                    if($content->children($varVal,true)->peerReview) {
                        $peerReview = (string)$content->children($varVal,true)->peerReview->children('extensions-core',true)->peerReviewed;
                    }
                
                    //Doi
                    if($content->children($varVal,true)->dois) {
                        $doi = (string)$content->children($varVal,true)->dois->children('core',true)->doi->children('core',true)->doi;
                    }
                
                    //Publication type
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
                $citeArray = array("Standard" => "standard", "Harvard" => "harvard", "APA" => "apa", "Vancouver" => "vancouver",
                    "Author" => "author", "RIS" => "ris", "Bibtex" => "bibtex");
                $cite = "";
                $bibtex = "";
                foreach($citeArray as $citebibKey => $citebib) {
                    $citebibxml = @file_get_contents($directory . '/bibtexfiles/' . str_replace('.xml','', $filename) . '_' . $citebib . '.xml');
                    if($citebibxml) {
                        $citebibxml = str_replace('$$$', '', $citebibxml);
                        $citebibxml = preg_replace('/<div/', '$$$<div', $citebibxml, 1);
                        $citebibxml = $this->lreplace('</div>', '</div>$$$', $citebibxml);
                        $citebibxmlArray = explode('$$$', $citebibxml);
                        //$citebibxml = simplexml_load_string($citebibxml);
                        if($citebib==="bibtex") {
                            $bibtex = "<h3>$citebibKey</h3>" . $citebibxmlArray[1];
                        } else {
                            $cite .= "<h3>$citebibKey</h3>" . $citebibxmlArray[1];
                        }
                    }
                } 
                
                //language selector
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
                    'abstract' => $abstract,
                    'articleNumber' => $articleNumber,
                    'attachmentLimitedVisibility' => $attachmentLimitedVisibility,
                    'attachmentMimeType' => $attachmentMimeType,
                    'attachmentSize' => $attachmentSize,
                    'attachmentTitle' => $attachmentTitle,
                    'attachmentUrl' => $attachmentUrl,
                    'authorExternal' => $authorExternal,
                    'authorExternalOrganisation' => $authorExternalOrganisation,
                    'authorId' => $authorId,
                    'authorName' => array_unique($authorName),
                    'authorFirstName' => $authorFirstName,
                    'authorLastName' => $authorLastName,
                    'authorFirstNameExact' => $authorFirstNameExact,
                    'authorLastNameExact' => $authorLastNameExact,
                    'authorOrganisation' => $authorOrganisation,
                    'awardDate' => gmdate('Y-m-d\TH:i:s\Z', strtotime($awardDate)),
                    'bibliographicalNote' => $bibliographicalNote,
                    'docType' => 'publication',
                    'documentTitle' => $documentTitle,
                    'doi' => $doi,
                    'edition' => $edition,
                    'electronicIsbns' => $electronicIsbns,
                    'externalOrganisationsName' => $externalOrganisationsName,
                    'externalOrganisationsId' => $externalOrganisationsId,
                    'event' => $event,
                    'eventCity' => $eventCity,
                    'eventCountry' => $eventCountry,
                    'hostPublicationTitle' => $hostPublicationTitle,
                    'id' => $id,
                    'journalNumber' => $journalNumber,
                    'journalTitle' => $journalTitle,
                    'keywordsUka' => $keywordsUka,
                    'keywordsUser' => $keywordsUser,
                    'language' => $language,
                    'numberOfPages' => $numberOfPages,
                    'organisationId' => $organisationId,
                    'organisationName' => $organisationName,
                    'organisationSourceId' => $organisationSourceId, 
                    'pages' => $pages,
                    'peerReview' => $peerReview,
                    'placeOfPublication' => $placeOfPublication,
                    'portalUrl' => $portalUrl,
                    'printIsbns' => $printIsbns,
                    'publicationStatus' => $publicationStatus,
                    'publicationDateYear' => $publicationDateYear,
                    'publicationDateMonth' => $publicationDateMonth,
                    'publicationDateDay' => $publicationDateDay,
                    'publicationType' => $publicationType,
                    'publicationTypeUri' => $publicationTypeUri,
                    'publisher' => $publisher,
                    'supervisorName' => $supervisorName,
                    'type' => $type,
                    'volume' => $volume,
                    'standardCategory' => $publicationType,
                    'issn' => $issn,
                    'boost' => '1.0',
                    'date' => gmdate('Y-m-d\TH:i:s\Z', strtotime($created)),
                    'changed' => gmdate('Y-m-d\TH:i:s\Z', strtotime($modified)),
                    'digest' => md5($id),
                    'bibtex' => $bibtex,
                    'cite' => $cite,
                    'appKey' => 'lthsolr',
                );
                // $this->debug($data);
                //$GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_devlog', array('msg' => print_r($data,true), 'crdate' => time()));
                //$GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_devlog_atom', array('msg' => $id, 'crdate' => time()));
                //move file
                if($mode==='reindex' && $syslang==='sv') {
                    rename($directory . '/indexedfiles/' . $filename, $directory . '/svindexedfiles/' . $filename);
                } else if($mode==='reindex' && $syslang==='en') {
                    rename($directory . '/svindexedfiles/' . $filename, $directory . '/indexedfiles/' . $filename);
                } else if($mode!='files') {
                    rename($directory . '/filestoindex/' . $filename, $directory . '/indexedfiles/' . $filename);
                }
                //$i++;
                $buffer->createDocument($data);
            }
        }
        $buffer->commit();
        return TRUE;
    }
    
    
    function getOrganisations($buffer, $maximumrecords, $numberofloops, $heritageArray, $startFromHere, $lastModified, $syslang, $mode)
    {
        $directory = '/var/www/html/typo3/lucrisdump';
        $fileArray = scandir($directory . '/orgfilestoindex');
        //$filename = '0.xml';
        $fileArray = array_slice($fileArray, 2);

        foreach ($fileArray as $key => $filename) {
        //for($i = 0; $i < $numberofloops; $i++) {
            
            //$startrecord = $startFromHere + ($i * $maximumrecords);

            /*if($mode==='reindex') {
                $xmlpath = $directory . '/indexedfiles/' . $filename;
            } else {*/
                $xmlpath = $directory . '/orgfilestoindex/' . $filename;
            //}
            $xml = @file_get_contents($xmlpath);
            
            $xmlPrefix = '<?xml version="1.0" encoding="UTF-8"?>';
            $xmlPrefix .= '<GetOrganisationResponse requestId=""><result>';
            $xmlSuffix = '</result></GetOrganisationResponse>';

            $xml = @simplexml_load_string($xmlPrefix . $xml . $xmlSuffix);
            
            //$numberofloops = ceil($xml->children('core', true)->count / 20);
                //$i=0;
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
                    }
                }
                
                //organisationSourceId
                if($content->external) {
                    $organisationSourceId[] = $content->external->sourceId;
                }
                    
                //language switch
                if($syslang==="sv") {
                    $typeClassification = $typeClassification_sv;
                    $organisationTitle = $name_sv;
                } else {
                    $typeClassification = $typeClassification_en;
                    $organisationTitle = $name_en;
                }
                
                $data = array(
                    'appKey' => 'lthsolr',
                    'id' => $id,
                    'docType' => 'organisation',
                    'organisationSourceId' => $organisationSourceId, 
                    'organisationParent' => $parents,
                    'portalUrl' => $portalUrl,
                    'organisationTitle' => $organisationTitle,
                    'typeClassification' => $typeClassification,
                    'type' => 'organisation',
                    'boost' => '1.0',
                    'date' => gmdate('Y-m-d\TH:i:s\Z', strtotime($created)),
                    'changed' => gmdate('Y-m-d\TH:i:s\Z', strtotime($modified)),
                    'digest' => md5($id),
                );
                // $this->debug($data);
                //$GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_devlog', array('msg' => print_r($data,true), 'crdate' => time()));
                //$GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_devlog_atom', array('msg' => $id, 'crdate' => time()));
                //move file
                //rename($directory . '/filestoindex/' . $filename, $directory . '/indexedfiles/' . $filename);
                //$i++;
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
    
    
    private function debug($inputArray)
    {
        echo '<pre>';
        print_r($inputArray);
        echo '</pre>';
    }
    
    
    function splitFiles($config, $client, $buffer, $current_date, $maximumrecords, $numberofloops, $settings, $heritageArray, $startFromHere, $lastModified, $syslang)
    {
        $directory = '/var/www/html/typo3/lucrisdump';
        $fileArray = scandir($directory);
        //$filename = '0.xml';
        $fileArray = array_slice($fileArray, 2);
        foreach ($fileArray as $key => $filename) {
            $xml = @file_get_contents($directory . '/' . $filename);
            $xml = @simplexml_load_string($xml);
            
            foreach($xml->xpath('//core:result//core:content') as $content) {
                $id = (string)$content->attributes();
                $content->asXml($directory . '/singlefiles/' . $id . '.xml');
            }
            rename($directory . '/' . $filename, $directory . '/splitted/' . $filename);
        }
        return TRUE;
    }
    
    
    function getFiles($buffer, $maximumrecords, $numberofloops, $heritageArray, $startFromHere, $lastModified, $syslang)
    {
        //return true;
        //$files1 = scandir('/var/www/html/typo3/fileadmin/lucrisdump');
        //$startFromHere = 20 * (intval(count($files1))-2);
        
        $varArray = array('publication-base_uk','stab');
        $directory = '/var/www/html/typo3/lucrisdump';

        for($i = 0; $i <= $numberofloops; $i++) {
            
            $startrecord = $startFromHere + ($i * 20);
            //$fileName = $startrecord . '.xml';
            $xmlpath = "https://lucris.lub.lu.se/ws/rest/publication?modifiedDate.fromDate=$lastModified&window.size=20&window.offset=$startrecord&orderBy.property=created&rendering=xml_long";
            //$xmlpath = "https://lucris.lub.lu.se/ws/rest/publication?window.size=20&window.offset=$startrecord&orderBy.property=created&rendering=xml_long";
            //$xmlpath = "https://lucris.lub.lu.se/ws/rest/publication?modifiedDate.fromDate=$lastModified&window.size=$maximumrecords&window.offset=$startrecord&rendering=xml_long";
            //$xmlpath = "https://lucris.lub.lu.se/ws/rest/publication?uuids.uuid=73b902e4-1c54-49f7-9a5c-68f78498b237&rendering=xml_long";
            //$xmlpath = "https://lucris.lub.lu.se/ws/rest/publication?typeClassificationUris.uri=/dk/atira/pure/researchoutput/researchoutputtypes/contributiontojournal/article&window.size=20&rendering=BIBTEX";

            $xml = @file_get_contents($xmlpath);
//$GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_devlog_dump', array('location' => $xmlpath, 'msg' => (string)$xml, 'crdate' => time()));
            //echo $xmlpath;
            $xml = @simplexml_load_string($xml);
            $numberofloops = ceil($xml->children('core', true)->count / 20);

            foreach($xml->xpath('//core:result//core:content') as $content) {
                $id = (string)$content->attributes();
                //citebibtex
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
                                    $citebibxml->asXml($directory . '/bibtexfiles/' . $id . '_standard.xml');
                                    break;
                                case 'harvard':
                                    $citebibxml->asXml($directory . '/bibtexfiles/' . $id . '_harvard.xml');
                                    break;
                                case 'apa':
                                    $citebibxml->asXml($directory . '/bibtexfiles/' . $id . '_apa.xml');
                                    break;
                                case 'vancouver':
                                    $citebibxml->asXml($directory . '/bibtexfiles/' . $id . '_vancouver.xml');
                                    break;
                                case 'author':
                                    $citebibxml->asXml($directory . '/bibtexfiles/' . $id . '_author.xml');
                                    break;
                                case 'RIS':
                                    $citebibxml->asXml($directory . '/bibtexfiles/' . $id . '_ris.xml');
                                    break;
                                case 'BIBTEX':
                                    $citebibxml->asXml($directory . '/bibtexfiles/' . $id . '_bibtex.xml');
                                    break;
                                }
                        }
                    }
                }
                //save content as xml
                $content->asXml($directory . '/filestoindex/' . $id . '.xml');
            }
        }
        return TRUE;
    }
    
    
    function getOrgFiles($buffer, $maximumrecords, $numberofloops, $heritageArray, $startFromHere, $lastModified, $syslang)
    {        
        $directory = '/var/www/html/typo3/lucrisdump';

        for($i = 0; $i <= $numberofloops; $i++) {
            
            $startrecord = $startFromHere + ($i * 20);
            //$fileName = $startrecord . '.xml';
            $xmlpath = "https://lucris.lub.lu.se/ws/rest/organisation.current?namespaces=remove&rendering=xml_long&window.size=20&window.offset=$startrecord";
 
            $xml = @file_get_contents($xmlpath);
            //echo $xmlpath;
            $xml = @simplexml_load_string($xml);
            $numberofloops = ceil($xml->count / 20);

            foreach($xml->xpath('//result//content') as $content) {
                $id = (string)$content->attributes();                
                //save content as xml
                $content->asXml($directory . '/orgfilestoindex/' . $id . '.xml');
            }
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