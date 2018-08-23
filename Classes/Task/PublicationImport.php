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
        
        $dbhost = $settings['dbhost'];
        $db = $settings['db'];
        $user = $settings['user'];
        $pw = $settings['pw'];
        $solrLucrisApiKey = $settings['solrLucrisApiKey'];
        $solrLucrisApiVersion = $settings['solrLucrisApiVersion'];

        $con = mysqli_connect($dbhost, $user, $pw, $db) or die("52; ".mysqli_error());
        
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

        //$startFromHere = $numFound;
        $startFromHere = 0;
        
        $mode = 'reindex'; //'' '' 'files'
        if($mode==='' && $mode!='files') {
            //Novo
            $executionSucceeded = $this->getFilesNovo($buffer, $maximumrecords, $numberofloops, $heritageArray, $startFromHere, $lastModified, $syslang, $solrLucrisApiKey, $solrLucrisApiVersion);
        
            //$executionSucceeded = $this->getFiles($buffer, $maximumrecords, $numberofloops, $heritageArray, $startFromHere, $lastModified, $syslang, $solrLucrisApiKey);
        } else if($mode==='files') {
            //$executionSucceeded = $this->getOrgFiles($buffer, $maximumrecords, $numberofloops, $heritageArray, $startFromHere, $lastModified, $syslang);
            $executionSucceeded = $this->getOrganisations($buffer, $maximumrecords, $numberofloops, $heritageArray, $startFromHere, $lastModified, $syslang, $mode);
            return TRUE;
        }
        if($executionSucceeded || $mode==='reindex' || $mode==='restart') {
            //novo
            if($mode==='restart') {
                $mode = '';
            }
            $executionSucceeded = $this->getPublicationsNovo($buffer, $maximumrecords, $numberofloops, $heritageArray, $startFromHere, $lastModified, $syslang, $mode);
            
            //$executionSucceeded = $this->getPublications($buffer, $maximumrecords, $numberofloops, $heritageArray, $startFromHere, $lastModified, $syslang, $mode);

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
            
            //novo
            $executionSucceeded = $this->getPublicationsNovo($buffer, $maximumrecords, $numberofloops, $heritageArray, $startFromHere, $lastModified, $syslang, $mode);
            
            //$executionSucceeded = $this->getPublications($buffer, $maximumrecords, $numberofloops, $heritageArray, $startFromHere, $lastModified, $syslang, $mode);
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
    
    
    function getPublicationsNovo($buffer, $maximumrecords, $numberofloops, $heritageArray, $startFromHere, $lastModified, $syslang, $mode)
    {
        $heritageArray = $heritageArray[0];
        //$this->debug($heritageArray[0]);

        $directory = '/var/www/html/typo3/lucrisdump';

        if($mode==='reindex' && $syslang==='sv') {
            $fileArray = scandir($directory . '/indexedfilesnovo');
        } else if($mode==='reindex' && $syslang==='en') {
            $fileArray = scandir($directory . '/svindexedfilesnovo');
        } else if($mode==='' && $syslang==='en') {
            $fileArray = scandir($directory . '/svindexedfilesnovo');
        } else {
            $fileArray = scandir($directory . '/filestoindexnovo');
        }
        $fileArray = array_slice($fileArray, 2);

        foreach ($fileArray as $filekey => $filename) {
            if($mode==='reindex' && $syslang==='sv') {
                $xmlpath = $directory . '/indexedfilesnovo/' . $filename;
            } else if($mode==='reindex' && $syslang==='en') {
                $xmlpath = $directory . '/svindexedfilesnovo/' . $filename;
            } else if($syslang==='sv') {
                $xmlpath = $directory . '/filestoindexnovo/' . $filename;
            } else if($mode==='' && $syslang==='en') {
                $xmlpath = $directory . '/svindexedfilesnovo/' . $filename;
            }

            $xml = @file_get_contents($xmlpath);

            $xml = @simplexml_load_string($xml);

            if($xml) {
                $id = (string)$xml->attributes();

                $abstract = ''; //ok
                $abstract_en = '';
                $abstract_sv = '';
                $additionalLink = array(); //New
                $authorFirstName = array(); //ok
                $authorId = array(); //ok
                $authorLastName = array(); //ok
                $authorName = array(); //ok
                $authorRole = array(); //ok
                $authorOrganisationId = array(); //NEW!added
                $authorOrganisationName = array(); //NEW!added
                $authorOrganisationType = array(); //NEW!added
                $authorRole = array(); //NEW!added
                $awardedDate = ''; //NEW!;added
                $awardingInstitution = array(); //NEW!added
                $awardingInstitution_en = '';
                $awardingInstitution_sv = '';
                $bibliographicalNote = ''; //ok
                $confidential = ''; //NEW!added
                $createdDate = ''; //NEW!added
                $documentTitle = ''; //ok
                $documentType = '';
                $electronicIsbn = array(); //NEW!added
                $electronicVersionAccessType = array(); //NEW!added
                $electronicVersionAccessType_en = '';
                $electronicVersionAccessType_sv = '';
                $electronicVersionDoi = array(); //NEW!added
                $electronicVersionFileName = array(); //NEW!added
                $electronicVersionFileURL = array(); //NEW!added
                $electronicVersionLicenseType = array(); //NEW!added
                $electronicVersionLicenseType_en = '';
                $electronicVersionLicenseType_sv = '';
                $electronicVersionLink = array();
                $electronicVersionMimeType = array(); //NEW!added
                $electronicVersionSize = array(); //NEW!added
                $electronicVersionTitle = array(); //NEW!added
                $electronicVersionVersionType = array(); //NEW!added
                $electronicVersionVersionType_en = '';
                $electronicVersionVersionType_sv = '';
                $endDate = '';
                $eventCity = '';
                $eventCountry = '';
                $eventCountry_en = '';
                $eventCountry_sv = '';
                $eventLink = array();
                $eventLocation = '';
                $eventName = ''; //NEW!
                $eventName_en = '';
                $eventName_sv = '';
                $eventLink = '';
                $eventType = ''; //NEW!
                $eventType_en = '';
                $eventType_sv = '';
                $hostPublicationEditor = array(); // NEW!
                $hostPublicationTitle = '';
                $ipc = '';
                $isbn2 = array(); //NEW!added
                $journalIssn = ''; //NEW!;added
                $journalNumber = ''; //ok
                $journalTitle = ''; //ok
                $keyword = array(); //NEW!added
                $keywordType = array(); //NEW!added
                $keyword_en = '';
                $keyword_se = '';
                $keywordGroupType_en = '';
                $keywordGroupType_sv = '';
                $language = ''; //ok
                $language_en = '';
                $language_sv = '';
                $logicalName = '';
                $managingOrganisationId = ''; //NEW!added
                $managingOrganisationName = ''; //NEW!added
                $managingOrganisationalunitName_en = '';
                $managingOrganisationalunitName_sv = '';
                $managingOrganisationType = ''; //NEW!added
                $managingOrganisationalunitType_en = '';
                $managingOrganisationalunitType_sv = '';
                $modifiedDate = ''; //NEW!added
                $numberOfPages = '';
                $openAccessPermission = ''; //NEW!added
                $openAccessPermission_en = '';
                $openAccessPermission_sv = '';
                $organisationalUnitName_en = '';
                $organisationalUnitName_sv = '';
                $organisationalUnitType_en = '';
                $organisationalUnitType_sv = '';
                $organisationId = array(); //ok
                $organisationName = array(); //ok
                $organisationName_en = '';
                $organisationName_sv = '';
                $organisationSourceId = array();
                $organisationType = array(); //NEW!added
                $organisationType_en = '';
                $organisationType_sv = '';
                $pages = ''; //ok
                $patentNumber = '';
                $peerReview = '';
                $personRole_en = '';
                $personRole_sv = '';
                $placeOfPublication = ''; //ok
                $portalUrl = ''; //ok
                $publicationDateDay = ''; //ok
                $publicationDateMonth = ''; //ok
                $publicationDateYear = ''; //ok
                $publicationSerieElectronicIssn = array(); //NEW!
                $publicationSerieName = array(); //NEW!
                $publicationStatus = ''; //ok
                $publicationStatus_en = '';
                $publicationStatus_sv = '';
                $publicationType = ''; //ok
                $publicationType_en = '';
                $publicationType_sv = '';
                $publicationTypeUri = ''; //ok
                $publisher = ''; //ok
                $publisher_en = '';
                $publisher_sv = '';
                $qualification = ''; //NEW!added
                $relatedActivityId = array(); //NEW!added
                $relatedActivityName = array(); //NEW!added
                $relatedActivityName_en = '';
                $relatedActivityName_sv = '';
                $relatedActivityType = array(); //NEW!added
                $relatedActivityType_en = '';
                $relatedActivityType_sv = '';
                $relatedProjectId = array(); //NEW!added
                $relatedProjectName = array(); //NEW!added
                $relatedProjectName_en = '';
                $relatedProjectName_sv = '';
                $relatedProjectType = array(); //NEW!added
                $relatedProjectType_en = '';
                $relatedProjectType_sv = '';
                $relatedResearchOutputId = array(); //NEW!added
                $relatedResearchOutputName = array(); //NEW!added
                $relatedResearchOutputName_en = '';
                $relatedResearchOutputName_sv = '';
                $relatedResearchOutputType = array(); //NEW!added
                $relatedResearchOutputType_en = '';
                $relatedResearchOutputType_sv = '';
                $sponsorId = array(); //NEW!added
                $sponsorName = array(); //NEW!added
                $sponsorName_en = '';
                $sponsorName_sv = '';
                $startDate = '';
                $supervisorId = array(); //ok
                $supervisorName = array(); //ok
                $supervisorOrganisationId = array(); //NEW!added
                $supervisorOrganisationName = array(); //NEW!added
                $supervisorOrganisationName_en = '';
                $supervisorOrganisationName_sv = '';
                $supervisorPersonRole = array(); //NEW!
                $supervisorPersonRole_en = '';
                $supervisorPersonRole_sv = '';
                $type = 'publication';
                $visibility = ''; //NEW!added
                $visibility_en = '';
                $visibility_sv = '';
                $volume = ''; //ok
                $workflow = ''; //NEW!added
                $workflow_en = '';
                $workflow_sv = '';

               /* echo '<pre>';
    print_r((array)$xml);
    echo '</pre>';
    die();*/
                //abstract
                if($xml->abstract) {
                    $abstract_en = (string)$xml->abstract[0];
                    $abstract_sv = (string)$xml->abstract[1];
                    $abstract = $this->languageSelector($syslang, $abstract_en, $abstract_sv);
                }

                foreach((array)$xml as $key => $value) {

                    //addtionalLinks
                    if($key === 'additionalLinks') {
                        foreach($value->additionalLink as $additionalLinkItem) {
                            if($additionalLinkItem->url) {
                                $additionalLink[] = (string)$additionalLinkItem->url;
                            }
                        }
                    }

                    //awardedDate
                    if($key === 'awardedDate') {
                        $awardedDate = (string)$value;
                    }

                    //awardingInstitutions
                    if($key === 'awardingInstitutions') {
                        foreach($value->awardingInstitution as $awardingInstitutionItem) {
                            if($awardingInstitutionItem->organisationalUnit) {
                                if($awardingInstitutionItem->organisationalUnit->name) {
                                    $awardingInstitution_en = (string)$awardingInstitutionItem->organisationalUnit->name[0];
                                    $awardingInstitution_sv = (string)$awardingInstitutionItem->organisationalUnit->name[1];
                                    $awardingInstitution[] = $this->languageSelector($syslang, $awardingInstitution_en, $awardingInstitution_sv);
                                }
                            }
                        }
                    }

                    //bibliographicalNote
                    if($key === 'bibliographicalNote') {
                        $bibliographicalNote = (string)$value;
                    }

                    //confidential
                    if($key === 'confidential') {
                        $confidential = (string)$value;
                    }

                    //documentTitle
                    if($key === 'title') {
                        $documentTitle = $value;
                    }

                    //electronicIsbns
                    if($key === 'electronicIsbns') {
                        foreach($value->electronicIsbn as $electronicIsbn) {
                            $electronicIsbn[] = (string)$electronicIsbn;
                        }
                    }

                    //electronicVersions
                    if($key === 'electronicVersions') {
                        foreach($value->electronicVersion as $electronicVersion) {
                            if($electronicVersion->accessType) {
                                $electronicVersionAccessType_en = (string)$electronicVersion->accessType[0];
                                $electronicVersionAccessType_sv = (string)$electronicVersion->accessType[1];
                                $electronicVersionAccessType[] = $this->languageSelector($syslang, $electronicVersionAccessType_en, $electronicVersionAccessType_sv);
                            } else {
                                $electronicVersionAccessType[] = '';
                            }
                            if($electronicVersion->versionType) {
                                $electronicVersionVersionType_en = (string)$electronicVersion->versionType[0];
                                $electronicVersionVersionType_sv = (string)$electronicVersion->versionType[1];
                                $electronicVersionVersionType[] = $this->languageSelector($syslang, $electronicVersionVersionType_en, $electronicVersionVersionType_sv);
                            } else {
                                $electronicVersionVersionType[] = '';
                            }
                            if($electronicVersion->licenseType) {
                                $electronicVersionLicenseType_en = (string)$electronicVersion->licenseType[0];
                                $electronicVersionLicenseType_sv = (string)$electronicVersion->licenseType[1];
                                $electronicVersionLicenseType[] = $this->languageSelector($syslang, $electronicVersionLicenseType_en, $electronicVersionLicenseType_sv);
                            } else {
                                $electronicVersionLicenseType[] = '';
                            }
                            if($electronicVersion->doi) {
                                $electronicVersionDoi[] = (string)$electronicVersion->doi;
                            } else {
                                $electronicVersionDoi[] = '';
                            }
                            if($electronicVersion->title) {
                                $electronicVersionTitle[] = (string)$electronicVersion->title;
                            } else {
                                $electronicVersionTitle[] = '';
                            }
                            if($electronicVersion->file) {
                                $electronicVersionFileName[] = (string)$electronicVersion->file->fileName;
                                $electronicVersionFileURL[] = (string)$electronicVersion->file->fileURL;
                                $electronicVersionMimeType[] = (string)$electronicVersion->file->mimeType;
                                $electronicVersionSize[] = (string)$electronicVersion->file->size;
                            } else {
                                $electronicVersionFileName[] = '';
                                $electronicVersionFileURL[] = '';
                                $electronicVersionMimeType[] = '';
                                $electronicVersionSize[] = '';
                            }
                            if($electronicVersion->link) {
                                $electronicVersionLink[] = (string)$electronicVersion->link;
                            } else {
                                $electronicVersionLink[] = '';
                            }
                        }
                    }

                    //eventName
                    if($key === 'event') {
                        if($value->name) {
                            $eventName_en = (string)$value->name[0];
                            $eventName_sv = (string)$value->name[1];
                            $eventName = $this->languageSelector($syslang, $eventName_en, $eventName_sv);
                        }
                        if($value->type) {
                            $eventType_en = (string)$value->type[0];
                            $eventType_sv = (string)$value->type[1];
                            $eventType = $this->languageSelector($syslang, $eventType_en, $eventType_sv);
                        }
                        if($value->link) {
                            $eventPath = (string)$value->link->attributes()->href;
                            if($eventPath) {
                                $eventXml = @file_get_contents($eventPath);
                                $eventXml = @simplexml_load_string($eventXml);
                                if($eventXml) {
                                    $startDate = (string)$eventXml->period->startDate;
                                    $endDate = (string)$eventXml->period->endDate;
                                    if($eventXml->links) {
                                        foreach($eventXml->links as $eventLinkItem) {
                                            $eventLink[] = (string)$eventLinkItem->url;
                                        }
                                    }
                                    $eventLocation = (string)$eventXml->location;
                                    $eventCity = (string)$eventXml->city;
                                    $eventCountry_en = (string)$eventXml->country[0];
                                    $eventCountry_sv = (string)$eventXml->country[1];
                                    $eventCountry = $this->languageSelector($syslang, $eventCountry_en, $eventCountry_sv);
                                }
                            }
                        }
                    }

                    //hostPublicationsEditor
                    if($key === 'hostPublicationEditors') {
                        foreach($value->hostPublicationEditor as $hostPublicationEditorItem) {
                            if($hostPublicationEditorItem->firstName && $hostPublicationEditorItem->lastName) {
                                $hostPublicationEditor[] = (string)$hostPublicationEditorItem->firstName . ' ' . (string)$hostPublicationEditorItem->lastName;
                            }
                        }
                    }

                    //hostPublicationTitle
                    if($key === 'hostPublicationTitle') {
                        $hostPublicationTitle = (string)$value;
                    }

                    //info
                    if($key === 'info') {
                        $createdDate = (string)$value->createdDate;
                        $modifiedDate = (string)$value->modifiedDate;
                        $portalUrl = (string)$value->portalUrl;
                    }

                    //ipc
                    if($key === 'ipc') {
                        $ipc = (string)$value;
                    }

                    //isbns!!!!!!!!!!!!!!!!!!!!!!OBS ändra till isbn senare
                    if($key === 'isbns') {
                        foreach($value->isbn as $isbn) {
                            $isbn2[] = (string)$isbn;
                        }
                    }

                    //journalAssociation
                    if($key === 'journalAssociation') {
                        $journalTitle = (string)$value->title;
                        $journalIssn = (string)$value->issn;
                    }

                    //journalNumber
                    if($key === 'journalNumber') {
                        $journalNumber = (string)$value;
                    }

                    //keywordGroups
                    if($key === 'keywordGroups') {
                        foreach($value->keywordGroup as $keywordGroup) {
                            $logicalName = (string)$keywordGroup->attributes();

                            if($keywordGroup->type) {
                                $keywordGroupType_en = (string)$keywordGroup->type[0];
                                $keywordGroupType_sv = (string)$keywordGroup->type[1];
                            }
                            foreach($keywordGroup->keywords->keyword as $keywordItem) {
                                $keywordType[] = $this->languageSelector($syslang, $keywordGroupType_en, $keywordGroupType_sv);
                                $keyword[] = (string)$keywordItem;
                            }
                        }
                    }

                    //Language
                    if($key === 'language') {
                        $language_en = $value[0];
                        $language_sv = $value[1];
                        $language = $this->languageSelector($syslang, $language_en, $language_sv);
                    }

                    //managingOrganisationalunit
                    if($key === 'managingOrganisationalUnit') {
                        $managingOrganisationId = (string)$value->attributes();
                        $managingOrganisationalunitName_en = (string)$value->name[0];
                        $managingOrganisationalunitName_sv = (string)$value->name[1];
                        $managingOrganisationName = $this->languageSelector($syslang, $managingOrganisationalunitName_en, $managingOrganisationalunitName_sv);
                        $managingOrganisationalunitType_en = (string)$value->type[0];
                        $managingOrganisationalunitType_sv = (string)$value->type[1];
                        $managingOrganisationType = $this->languageSelector($syslang, $managingOrganisationalunitType_en, $managingOrganisationalunitType_sv);
                    }

                    //numberOfPages
                    if($key === 'numberOfPages') {
                        $numberOfPages = (string)$value;
                    }

                    //openAccessPermission
                    if($key === 'openAccessPermission') {
                        $openAccessPermission_en = (string)$value[0];
                        $openAccessPermission_sv = (string)$value[1];
                        $openAccessPermission = $this->languageSelector($syslang, $openAccessPermission_en, $openAccessPermission_sv);
                    }

                    //Organisationalunit
                    if($key === 'organisationalUnits') {
                        foreach($value->organisationalUnit as $organisationalUnit) {
                            $organisationId[] = (string)$organisationalUnit->attributes();
                            $organisationName_en = (string)$organisationalUnit->name[0];
                            $organisationName_sv = (string)$organisationalUnit->name[1];
                            $organisationName[] = $this->languageSelector($syslang, $organisationName_en, $organisationName_sv);
                            $organisationType_en = (string)$organisationalUnit->type[0];
                            $organisationType_sv = (string)$organisationalUnit->type[1];
                            $organisationType[] = $this->languageSelector($syslang, $organisationType_en, $organisationType_sv);
                        }
                    }

                    //peerReview
                    if($key === 'peerReview') {
                        $peerReview = $value;
                    }

                    //personsAssociations
                    if($key === 'personAssociations') {
                        foreach($value->personAssociation as $personAssociation) {
                            if($personAssociation->person) {
                                $authorId[] = (string)$personAssociation->person->attributes();
                            } else if($personAssociation->externalPerson) {
                                $authorId[] = (string)$personAssociation->externalPerson->attributes();

                            }
                            $authorName[] = (string)$personAssociation->name->firstName . ' ' . (string)$personAssociation->name->lastName;
                            $authorFirstName[] = (string)$personAssociation->name->firstName;
                            $authorLastName[] = (string)$personAssociation->name->lastName;
                            if($personAssociation->personRole) {
                                $personRole_en = (string)$personAssociation->personRole[0];
                                $personRole_sv = (string)$personAssociation->personRole[1];
                                $authorRole[] = $this->languageSelector($syslang, $personRole_en, $personRole_sv);
                            }
                            if($personAssociation->organisationalUnits) {
                                foreach($personAssociation->organisationalUnits->organisationalUnit as $organisationalUnit) {
                                    $authorOrganisationId[] = (string)$organisationalUnit->attributes();
                                    $organisationalUnitName_en = (string)$organisationalUnit->name[0];
                                    $organisationalUnitName_sv = (string)$organisationalUnit->name[1];
                                    $authorOrganisationName[] = $this->languageSelector($syslang, $organisationalUnitName_en, $organisationalUnitName_sv);
                                    $organisationalUnitType_en = (string)$organisationalUnit->type[0];
                                    $organisationalUnitType_sv = (string)$organisationalUnit->type[1];
                                    $authorOrganisationType[] = $this->languageSelector($syslang, $organisationalUnitType_en, $organisationalUnitType_sv);
                                }
                            }

                        }
                    }

                    //placeOfPublication
                    if($key === 'placeOfPublication') {
                        $placeOfPublication = (string)$value;
                    }

                    //publicationStatus, publicationDateYear, publicationDateMonth, publicationDateDay
                    if($key === 'publicationStatuses') {
                        foreach($value as $publicationStatusItem) {
                            if($publicationStatusItem->attributes()->current) {
                                $publicationStatus_en = (string)$publicationStatusItem->publicationStatus[0];
                                $publicationStatus_sv = (string)$publicationStatusItem->publicationStatus[1];
                                $publicationStatus = $this->languageSelector($syslang, $publicationStatus_en, $publicationStatus_sv);
                                $publicationDateYear = (string)$publicationStatusItem->publicationDate->year;
                                $publicationDateMonth = (string)$publicationStatusItem->publicationDate->month;
                                $publicationDateDay = (string)$publicationStatusItem->publicationDate->day;
                            }
                        }
                    }

                    //publicationType
                    if($key === 'type') {
                        $publicationType_en = $value[0];
                        $publicationType_sv = $value[1];
                        $publicationType = $this->languageSelector($syslang, $publicationType_en, $publicationType_sv);
                    }

                    //publicationSeries
                    if($key === 'publicationSeries') {
                        foreach($value->publicationSerie as $publicationSerie) {
                            $publicationSerieName[] = (string)$publicationSerie->name;
                            $publicationSerieElectronicIssn[] = (string)$publicationSerie->electronicIssn;
                        }
                    }

                    //publisher
                    if($key === 'publisher') {
                        $publisher_en = (string)$value->name;
                        $publisher_sv = (string)$value->name;
                        $publisher = $this->languageSelector($syslang, $publisher_en, $publisher_sv);
                    }

                    //qualification
                    if($key === 'qualification') {
                        $qualification = (string)$value;
                    }

                    //pages
                    if($key === 'pages') {
                        $pages = (string)$value;
                    }

                    //patentNumber
                    if($key === 'patentNumber') {
                        $patentNumber = (string)$value;
                    }

                    //relatedActivities
                    if($key === 'relatedActivities') {
                        foreach($value->relatedActivity as $relatedActivity) {
                            $relatedActivityId[] = (string)$relatedActivity->attributes();
                            if($relatedActivity->name) {
                                $relatedActivityName_en = (string)$relatedActivity->name[0];
                                $relatedActivityName_sv = (string)$relatedActivity->name[1];
                                $relatedActivityName[] = $this->languageSelector($syslang, $relatedActivityName_en, $relatedActivityName_sv);
                            }
                            if($relatedActivity->type) {
                                $relatedActivityType_en = (string)$relatedActivity->type[0];
                                $relatedActivityType_sv = (string)$relatedActivity->type[1];
                                $relatedActivityType[] = $this->languageSelector($syslang, $relatedActivityType_en, $relatedActivityType_sv);
                            }
                        }
                    }

                    //relatedProjects
                    if($key === 'relatedProjects') {
                        foreach($value->relatedProject as $relatedProject) {
                            $relatedProjectId[] = (string)$relatedProject->attributes();
                            if($relatedProject->name) {
                                $relatedProjectName_en = (string)$relatedProject->name[0];
                                $relatedProjectName_sv = (string)$relatedProject->name[1];
                                $relatedProjectName[] = $this->languageSelector($syslang, $relatedProjectName_en, $relatedProjectName_sv);
                            }
                            if($relatedProject->type) {
                                $relatedProjectType_en = (string)$relatedProject->type[0];
                                $relatedProjectType_sv = (string)$relatedProject->type[1];
                                $relatedProjectType[] = $this->languageSelector($syslang, $relatedProjectType_en, $relatedProjectType_sv);
                            }
                        }
                    }

                    //relatedResearchOutputs
                    if($key === 'relatedResearchOutputs') {
                        foreach($value->relatedResearchOutput as $relatedResearchOutput) {
                            $relatedResearchOutputId[] = (string)$relatedResearchOutput->attributes();
                            if($relatedResearchOutput->name) {
                                $relatedResearchOutputName_en = (string)$relatedResearchOutput->name[0];
                                $relatedResearchOutputName_sv = (string)$relatedResearchOutput->name[1];
                                $relatedResearchOutputName = $this->languageSelector($syslang, $relatedResearchOutputName_en, $relatedResearchOutputName_sv);
                            }
                            if($relatedResearchOutput->type) {
                                $relatedResearchOutputType_en = (string)$relatedResearchOutput->type[0];
                                $relatedResearchOutputType_sv = (string)$relatedResearchOutput->type[1];
                                $relatedResearchOutputType = $this->languageSelector($syslang, $relatedResearchOutputType_en, $relatedResearchOutputType_sv);
                            }
                        }
                    }

                    //sponsors
                    if($key === 'sponsors') {
                        foreach($value->sponsor as $sponsor) {
                            $sponsorId[] = (string)$sponsor->attributes();
                            if($sponsors->name) {
                                $sponsorName_en = (string)$sponsor->name[0];
                                $sponsorName_sv = (string)$sponsor->name[1];
                            }
                            $sponsorName[] = $this->languageSelector($syslang, $sponsorName_en, $sponsorName_sv);
                        }
                    }

                    //supervisors
                    if($key === 'supervisors') {
                        foreach($value->supervisor as $supervisor) {
                            if($supervisor->person) {
                                $supervisorId[] = (string)$supervisor->person->attributes();
                            } else if($supervisor->externalPerson) {
                                $supervisorId[] = (string)$supervisor->externalPerson->attributes();
                            }
                            if($supervisor->name) {
                                $supervisorName[] = $supervisor->name->firstName . ' ' . $supervisorItem->name->lastName;
                            }
                            if($supervisor->personRole) {
                                $supervisorPersonRole_en = (string)$supervisor->personRole[0];
                                $supervisorPersonRole_sv = (string)$supervisor->personRole[1];
                                $supervisorPersonRole[] = $this->languageSelector($syslang, $supervisorPersonRole_en, $supervisorPersonRole_sv);
                            }
                            if($supervisor->organisationalUnits) {
                                foreach($supervisor->organisationalUnits->organisationalUnit as $supervisorOrganisation) {
                                    $supervisorOrganisationId[] = (string)$supervisorOrganisation->attributes();
                                    if($supervisorOrganisation->name) {
                                        $supervisorOrganisationName_en = (string)$supervisorOrganisation->name[0];
                                        $supervisorOrganisationName_sv = (string)$supervisorOrganisation->name[1];
                                        $supervisorOrganisationName[] = $this->languageSelector($syslang, $supervisorOrganisationName_en, $supervisorOrganisationName_sv);
                                    }
                                }
                            }
                        }
                    }

                    //visibility
                    if($key === 'visibility') {
                        $visibility_en = (string)$value[0];
                        $visibility_sv = (string)$value[1];
                        $visibility = $this->languageSelector($syslang, $visibility_en, $visibility_sv);
                    }

                    //volume
                    if($key === 'volume') {
                        $volume = (string)$value;
                    }

                    //workflow
                    if($key === 'workflow') {
                        $workflow_en = (string)$value[0];
                        $workflow_sv = (string)$value[1];
                        $workflow = $this->languageSelector($syslang, $workflow_en, $workflow_sv);
                    }         

                    //CITE OCH BIBTEX
                    $citeArray = array("Standard" => "standard", "Harvard" => "harvard", "APA" => "apa", "Vancouver" => "vancouver",
                        "Author" => "author", "RIS" => "ris", "Bibtex" => "bibtex");
                    $cite = "";
                    $bibtex = "";
                    foreach($citeArray as $citebibKey => $citebib) {
                        $citebibxml = @file_get_contents($directory . '/bibtexfilesnovo/' . str_replace('.xml','', $filename) . '_' . $citebib . '.xml');
                        if($citebibxml) {
                            $citebibxml = @simplexml_load_string($citebibxml);
    /*echo '<pre>';
    echo (string)$citebibxml->rendering;
    echo '</pre>';
    die();*/
                            if($citebib==="bibtex") {
                                $bibtex = "<h3>$citebibKey</h3>" . (string)$citebibxml->rendering;
                            } else {
                                $cite .= "<h3>$citebibKey</h3>" . (string)$citebibxml->rendering;
                            }
                            /*$citebibxml = str_replace('$$$', '', $citebibxml);
                            $citebibxml = preg_replace('/<div/', '$$$<div', $citebibxml, 1);
                            $citebibxml = $this->lreplace('</div>', '</div>$$$', $citebibxml);
                            $citebibxmlArray = explode('$$$', $citebibxml);
                            if($citebib==="bibtex") {
                                $bibtex = "<h3>$citebibKey</h3>" . $citebibxmlArray[1];
                            } else {
                                $cite .= "<h3>$citebibKey</h3>" . $citebibxmlArray[1];
                            }*/
                        }
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

                $data = array(
                    'abstract' => $abstract,
                    'additionalLink' => $additionalLink,
                    'appKey' => 'lthsolr',
                    'authorFirstName' => $authorFirstName,
                    'authorId' => $authorId,
                    'authorLastName' => $authorLastName,
                    'authorName' => $authorName,
                    'authorOrganisationId' => $authorOrganisationId,
                    'authorOrganisationName' => $authorOrganisationName,
                    'authorOrganisationType' => $authorOrganisationType,
                    'authorRole' => $authorRole,
                    'awardingInstitution' => $awardingInstitution,
                    'bibliographicalNote' => $bibliographicalNote,
                    'bibtex' => $bibtex,
                    'boost' => '1.0',
                    'changed' => gmdate('Y-m-d\TH:i:s\Z', strtotime($modifiedDate)),
                    'cite' => $cite,
                    'confidential' => $confidential,
                    'createdDate' => gmdate('Y-m-d\TH:i:s\Z', strtotime($createdDate)),
                    'date' => gmdate('Y-m-d\TH:i:s\Z', strtotime($createdDate)),
                    'digest' => md5($id),
                    'docType' => 'publication',
                    'documentTitle' => $documentTitle,
                    'electronicIsbn' => $electronicIsbn,
                    'electronicVersionAccessType' => $electronicVersionAccessType,
                    'electronicVersionDoi' => $electronicVersionDoi,
                    'electronicVersionFileName' => $electronicVersionFileName,
                    'electronicVersionFileURL' => $electronicVersionFileURL,
                    'electronicVersionLicenseType' => $electronicVersionLicenseType,
                    'electronicVersionLink' => $electronicVersionLink,
                    'electronicVersionMimeType' => $electronicVersionMimeType,
                    'electronicVersionSize' => $electronicVersionSize,
                    'electronicVersionTitle' => $electronicVersionTitle,
                    'electronicVersionVersionType' => $electronicVersionVersionType,
                    'endDate' => gmdate('Y-m-d\TH:i:s\Z', strtotime($endDate)),
                    'eventCity' =>$eventCity,
                    'eventCountry' =>$eventCountry,
                    'eventName' => $eventName,
                    'eventLink' =>$eventLink,
                    'eventType' => $eventType,
                    'hostPublicationEditor' => $hostPublicationEditor,
                    'hostPublicationTitle' => $hostPublicationTitle,
                    'id' => $id,
                    'ipc' => $ipc,
                    'isbn2' => $isbn2,
                    'journalIssn' => $journalIssn,
                    'journalNumber' => $journalNumber,
                    'journalTitle' => $journalTitle,
                    'keyword' => $keyword,
                    'keywordType' => $keywordType,
                    'language' => $language,
                    'logicalName' => $logicalName,
                    'managingOrganisationId' => $managingOrganisationId,
                    'managingOrganisationName' => $managingOrganisationName,
                    'managingOrganisationType' => $managingOrganisationType,
                    'modifiedDate' => gmdate('Y-m-d\TH:i:s\Z', strtotime($modifiedDate)),
                    'numberOfPages' => $numberOfPages,
                    'openAccessPermission' => $openAccessPermission,
                    'organisationId' => $organisationId,
                    'organisationName' => $organisationName,
                    'organisationSourceId' => $organisationSourceId,
                    'organisationType' => $organisationType,
                    'pages' => $pages,
                    'patentNumber' => $patentNumber,
                    'peerReview' => $peerReview,
                    'placeOfPublication' => $placeOfPublication,
                    'portalUrl' => $portalUrl,
                    'publicationDateDay' => $publicationDateDay,
                    'publicationDateMonth' => $publicationDateMonth,
                    'publicationDateYear' => $publicationDateYear,
                    'publicationSerieElectronicIssn' => $publicationSerieElectronicIssn,
                    'publicationSerieName' => $publicationSerieName,
                    'publicationStatus' => $publicationStatus,
                    'publicationType' => $publicationType,
                    'publicationTypeUri' => $publicationTypeUri,
                    'publisher' => $publisher,
                    'qualification' => $qualification,
                    'relatedActivityId' => $relatedActivityId,
                    'relatedActivityName' => $relatedActivityName,
                    'relatedActivityType' => $relatedActivityType,
                    'relatedProjectId' => $relatedProjectId,
                    'relatedProjectName' => $relatedProjectName,
                    'relatedProjectType' => $relatedProjectType,
                    'relatedResearchOutputId' => $relatedResearchOutputId,
                    'relatedResearchOutputName' => $relatedResearchOutputName,
                    'relatedResearchOutputType' => $relatedResearchOutputType,
                    'sponsorId' => $sponsorId,
                    'sponsorName' => $sponsorName,
                    'startDate' => gmdate('Y-m-d\TH:i:s\Z', strtotime($startDate)),
                    'supervisorId' => $supervisorId,
                    'supervisorName' => $supervisorName,
                    'supervisorOrganisationId' => $supervisorOrganisationId,
                    'supervisorOrganisationName' => $supervisorOrganisationName,
                    'supervisorPersonRole' => $supervisorPersonRole,
                    'type' => $type,
                    'visibility' => $visibility,
                    'volume' => $volume,
                    'workflow' => $workflow,
                );
                
                if($awardedDate!=='') {
                    $data['awardedDate'] = gmdate('Y-m-d\TH:i:s\Z', strtotime($awardedDate));
                }

                /*echo '<pre>';
                print_r($data);
                echo '</pre>';*/

                //move files
                if($mode==='reindex' && $syslang==='sv') {
                    rename($directory . '/indexedfilesnovo/' . $filename, $directory . '/svindexedfilesnovo/' . $filename);
                } else if($mode==='' && $syslang==='sv') {
                    rename($directory . '/filestoindexnovo/' . $filename, $directory . '/svindexedfilesnovo/' . $filename);
                } else if($mode==='reindex' && $syslang==='en') {
                    rename($directory . '/svindexedfilesnovo/' . $filename, $directory . '/indexedfilesnovo/' . $filename);
                } else if($mode==='' && $syslang==='en') {
                    rename($directory . '/svindexedfilesnovo/' . $filename, $directory . '/indexedfilesnovo/' . $filename);
                } else if($mode!='files' && $syslang==='en') {
                    rename($directory . '/filestoindexnovo/' . $filename, $directory . '/indexedfilesnovo/' . $filename);
                }

                $buffer->createDocument($data);
            }
        }
        $buffer->commit();
        return TRUE;
    }
    
    
    function languageSelector($syslang, $value_en, $value_sv)
    {
        if($value_en || $value_sv) {
            if($syslang==="sv") {
                $value = $value_sv;
            } else if($syslang==="en") {
                $value = $value_en;
            }

            if(!$value && $value_en) {
                $value = $value_en;
            }
            if(!$value && $value_sv) {
                $value = $value_sv;
            }
            return trim($value);
        } else {
            return false;
        }
    }
    
    
    function getPublications($buffer, $maximumrecords, $numberofloops, $heritageArray, $startFromHere, $lastModified, $syslang, $mode)
    {
        $heritageArray = $heritageArray[0];
        //$this->debug($heritageArray[0]);
        $varArray = array('publication-base_uk','stab','stab1');
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
                $bibliographicalNote_sv = '';
                $bibliographicalNote_en = '';
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
                $supervisorName = array();
                $supervisorId = array();
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

                

                //abstract
                foreach($varArray as $varVal) {
                    //title
                    if($content->children($varVal,true)->title) {
                        $documentTitle = (string)$content->children($varVal,true)->title;
                    } /*else if($content->children('stab',true)->title) {
                        $documentTitle = (string)$content->children('stab',true)->title;
                    }*/
                    
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
                        if($content->children($varVal,true)->documents->attributes()->campus == '1') {
                            $attachmentLimitedVisibility = 'campus';
                        } else if($content->children($varVal,true)->documents->attributes()->free == '1') {
                            $attachmentLimitedVisibility = 'free';
                        } else if($content->children($varVal,true)->documents->attributes()->backend == '1') {
                            $attachmentLimitedVisibility = 'backend';
                        }
                        foreach($content->children($varVal,true)->documents->children('extensions-core',true)->document as $document) {
                            $attachmentMimeType = (string)$document->children('core',true)->mimeType;
                            $attachmentSize = (string)$document->children('core',true)->size;
                            $attachmentUrl = (string)$document->children('core',true)->url;
                            $attachmentTitle = (string)$document->children('core',true)->title;
                        }
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
                                if($personAssociation->children('person-template',true)->person->children('core',true)->portalUrl) {
                                    $authorIdTemp = (string)$personAssociation->children('person-template',true)->person->children('core',true)->portalUrl;
                                    $authorIdTemp = str_replace(').html','',array_pop(explode('(',$authorIdTemp)));
                                }
                                //$authorIdTemp = (string)$personAssociation->children('person-template',true)->person->attributes();
                                $authorNameTemp = (string)$personAssociation->children('person-template',true)->person->children('person-template',true)->name->children('core',true)->firstName . ' ' .
                                        (string)$personAssociation->children('person-template',true)->person->children('person-template',true)->name->children('core',true)->lastName;
                                $authorFirstNameTemp = (string)$personAssociation->children('person-template',true)->person->children('person-template',true)->name->children('core',true)->firstName;
                                $authorLastNameTemp = (string)$personAssociation->children('person-template',true)->person->children('person-template',true)->name->children('core',true)->lastName;
                                if($personAssociation->children('person-template',true)->person->children('person-template',true)->organisationAssociations->children('person-template',true)->organisationAssociation) { //->children('person-template',true)->organisation->children('organisation-template',true)->external) {
                                    $authorOrganisationTemp = (string)$personAssociation->children('person-template',true)->person->children('person-template',true)->organisationAssociations->children('person-template',true)->organisationAssociation->children('person-template',true)->organisation->children('organisation-template',true)->external->children('extensions-core',true)->sourceId;
                                }
                            } else if($personAssociation->children('person-template',true)->externalPerson) {
                                $authorExternalTemp = 1;
                                //$authorIdTemp = (string)$personAssociation->children('person-template',true)->externalPerson->attributes();
                                if($personAssociation->children('core',true)->portalUrl) {
                                    $authorIdTemp = (string)$personAssociation->children('core',true)->portalUrl;
                                    $authorIdTemp = str_replace(').html','',array_pop(explode('(',$authorIdTemp)));
                                }
                                $authorNameTemp = (string)$personAssociation->children('person-template',true)->externalPerson->children('externalperson-template',true)->name->children('core',true)->firstName . ' ' . (string)$personAssociation->children('person-template',true)->externalPerson->children('externalperson-template',true)->name->children('core',true)->lastName;
                                $authorFirstNameTemp = (string)$personAssociation->children('person-template',true)->externalPerson->children('externalperson-template',true)->name->children('core',true)->firstName;
                                $authorLastNameTemp = (string)$personAssociation->children('person-template',true)->externalPerson->children('externalperson-template',true)->name->children('core',true)->lastName;
                                $authorOrganisationTemp = '';
                            } else if($personAssociation->children('person-template',true)->name) {
                                if($personAssociation->children('core',true)->portalUrl) {
                                    $authorIdTemp = (string)$personAssociation->children('core',true)->portalUrl;
                                    $authorIdTemp = str_replace(').html','',array_pop(explode('(',$authorIdTemp)));
                                }
                                //$authorIdTemp = (string)$personAssociation->children('person-template',true)->person->attributes();
                                $authorNameTemp = (string)$personAssociation->children('person-template',true)->name->children('core',true)->firstName . ' ' .
                                        (string)$personAssociation->children('person-template',true)->name->children('core',true)->lastName;
                                $authorFirstNameTemp = (string)$personAssociation->children('person-template',true)->name->children('core',true)->firstName;
                                $authorLastNameTemp = (string)$personAssociation->children('person-template',true)->name->children('core',true)->lastName;
                                
                                if($personAssociation->children('person-template',true)->person) { //->children('person-template',true)->organisationAssociations->children('person-template',true)->organisationAssociation) { //->children('person-template',true)->organisation->children('organisation-template',true)->external) {
                                    $authorOrganisationTemp = (string)$personAssociation->children('person-template',true)->person->children('person-template',true)->organisationAssociations->children('person-template',true)->organisationAssociation->children('person-template',true)->organisation->children('organisation-template',true)->external->children('extensions-core',true)->sourceId;
                                }
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
                                break;
                            }
                        }
                    }
                    
                    //authorLastNameExact
                    if($authorLastName) {
                        foreach($authorLastName as $alnKey => $alnValue) {
                            if($alnValue && $alnValue != '') {
                                $authorLastNameExact = $alnValue;
                                break;
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
                    //if($content->children('stab',true)->supervisorAdvisor->children('stab', true)->classifiedInternalExternalPersonAssociation->children('stab',true)->person) {
                        foreach($content->children('stab',true)->supervisorAdvisor->children('stab', true)->classifiedInternalExternalPersonAssociation as $supervisor) {
                            if($supervisor->children('stab',true)->person) {
                                
                                $supervisorId[] = (string)$supervisor->children('stab',true)->person->attributes()->uuid;
                                $supervisorName[] = (string)$supervisor->children('stab',true)->person->children('person-template',true)->name->children('core', true)->firstName . ' ' . 
                                        (string)$supervisor->children('stab',true)->person->children('person-template',true)->name->children('core', true)->lastName;
                            }
                        }
                    //}
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
                if(!$bibliographicalNote && $bibliographicalNote_en) {
                    $bibliographicalNote = $bibliographicalNote_en;
                }
                if(!$bibliographicalNote && $bibliographicalNote_sv) {
                    $bibliographicalNote = $bibliographicalNote_sv;
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
                    'supervisorId' => $supervisorId, 
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
                } else if($mode!='files' && $syslang==='en') {
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
    
    
    function getFilesNovo($buffer, $maximumrecords, $numberofloops, $heritageArray, $startFromHere, $lastModified, $syslang, $solrLucrisApiKey, $solrLucrisApiVersion)
    {
        //TESTAREA BEGIN
        //Tidskriftsbidrag: 073e9848-cf8f-47c3-9293-b24086a6bb4a
        //$xmlpath = "https://lucris.lub.lu.se/ws/api/$solrLucrisApiVersion/research-outputs/073e9848-cf8f-47c3-9293-b24086a6bb4a?size=20&apiKey=$solrLucrisApiKey";

        //Kapitel i bok: a4080171-cfde-4fea-a32b-82df7e840cdd
        //$xmlpath = "https://lucris.lub.lu.se/ws/api/$solrLucrisApiVersion/research-outputs/a4080171-cfde-4fea-a32b-82df7e840cdd?size=20&apiKey=$solrLucrisApiKey";
        
        //Bok: 2a526fdb-2365-4c30-b514-59531c67ddc8
        //$xmlpath = "https://lucris.lub.lu.se/ws/api/$solrLucrisApiVersion/research-outputs/2a526fdb-2365-4c30-b514-59531c67ddc8?size=20&apiKey=$solrLucrisApiKey";
        
        //Bidrag till övrig tidskrift: 27f601cb-0605-47f0-b065-75f6f3127159
        //$xmlpath = "https://lucris.lub.lu.se/ws/api/$solrLucrisApiVersion/research-outputs/27f601cb-0605-47f0-b065-75f6f3127159?size=20&apiKey=$solrLucrisApiKey";
        
        //Working paper: 25e71cba-64c7-4621-b734-39f65f621797
        //$xmlpath = "https://lucris.lub.lu.se/ws/api/$solrLucrisApiVersion/research-outputs/25e71cba-64c7-4621-b734-39f65f621797?size=20&apiKey=$solrLucrisApiKey";
        
        //Konferensbidrag: 2af604a9-40be-4572-b15e-eab730b45751
        //$xmlpath = "https://lucris.lub.lu.se/ws/api/$solrLucrisApiVersion/research-outputs/2af604a9-40be-4572-b15e-eab730b45751?size=20&apiKey=$solrLucrisApiKey";
        
        //Icke textbaserad output: 8785f3a6-e0f6-467f-ae93-0bd09a28bfdf
        //$xmlpath = "https://lucris.lub.lu.se/ws/api/$solrLucrisApiVersion/research-outputs/8785f3a6-e0f6-467f-ae93-0bd09a28bfdf?size=20&apiKey=$solrLucrisApiKey";
        
        //Avhandling: a5d1d4ea-9eab-4154-a0f8-b65664e80afc
        //$xmlpath = "https://lucris.lub.lu.se/ws/api/59/research-outputs/a5d1d4ea-9eab-4154-a0f8-b65664e80afc?size=20&apiKey=$solrLucrisApiKey";
        
        //Patent: 5fd2c529-b00c-40fc-a3a6-9243a85638fd
        //$xmlpath = "https://lucris.lub.lu.se/ws/api/$solrLucrisApiVersion/research-outputs/5fd2c529-b00c-40fc-a3a6-9243a85638fd?size=20&apiKey=$solrLucrisApiKey";
        
        //Övrigt: 951ccc19-08c2-441e-bacb-09f8ce5dc11a
        //$xmlpath = "https://lucris.lub.lu.se/ws/api/$solrLucrisApiVersion/research-outputs/951ccc19-08c2-441e-bacb-09f8ce5dc11a?size=20&apiKey=$solrLucrisApiKey";
        
        //$xmlpath = "https://lucris.lub.lu.se/ws/api/$solrLucrisApiVersion/research-outputs/?size=2&offset=0&apiKey=$solrLucrisApiKey";
        
        //$xmlpath = "https://lucris.lub.lu.se/ws/api/$solrLucrisApiVersion/changes/2018-06-11/?apiKey=$solrLucrisApiKey";
        //TESTAREA ENDS

        $dateOrId = substr($lastModified,0,10); //'2018-06-11';
        $type='';
        //$executionSucceded = FALSE;
        do {
            $dateOrId = $this->getXml($solrLucrisApiVersion, $dateOrId, $solrLucrisApiKey, $type);
        } while ($dateOrId);
        
        return TRUE;                
    }
    
    
    
    function getXml($solrLucrisApiVersion, $dateOrId, $solrLucrisApiKey, $type)
    {
        $directory = '/var/www/html/typo3/lucrisdump';
        
        $xmlpath = "https://lucris.lub.lu.se/ws/api/$solrLucrisApiVersion/changes/$dateOrId/?apiKey=$solrLucrisApiKey";
        //$xmlpath = "https://lucris.lub.lu.se/ws/api/$solrLucrisApiVersion/research-outputs?order=publicationYear&size=100&offset=" . (string)$dateOrId . "&fields=id&apiKey=$solrLucrisApiKey";
        $xml = file_get_contents($xmlpath);
        $xml = @simplexml_load_string($xml);
        $nextLink = '';
        $moreChanges = '';
        $lastId = '';

        foreach($xml->children() as $contentChange) {
            if((string)$contentChange->getName() === 'navigationLink') {
                $nextLink = (string)$contentChange->attributes()->href;
            }
            if((string)$contentChange->getName() === 'moreChanges') {
                $moreChanges = (string)$contentChange;
            }
            if((string)$contentChange->getName() === 'lastId') {
                $lastId = (string)$contentChange;
            }

            if(((string)$contentChange->getName() === 'contentChange') || ($type==='again' && (string)$contentChange->getName() !== 'count' && (string)$contentChange->getName() !== 'navigationLink')) {
                //$GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_devlog', array('msg' => (string)$contentChange->getName(), 'crdate' => time()));

                if(((string)$contentChange->familySystemName === 'ResearchOutput') || ($type==='again')) {
                    
                    $id = (string)$contentChange->attributes();
                    
                    $xmlSinglePath = "https://lucris.lub.lu.se/ws/api/$solrLucrisApiVersion/research-outputs/$id?apiKey=$solrLucrisApiKey";

                    $xmlSingle = @file_get_contents($xmlSinglePath);

                    if($xmlSingle) {
                        $xmlSingle = @simplexml_load_string($xmlSingle);

                        $citeArray = array("Standard" => "standard", "Harvard" => "harvard", "APA" => "apa", 
                                        "Vancouver" => "vancouver", "Author" => "author", "RIS" => "RIS", "Bibtex" => "BIBTEX");

                        $cite = "";
                        $bibtex = "";
                        foreach($citeArray as $citebibKey => $citebib) {
                            $citebibxmlpath = "https://lucris.lub.lu.se/ws/api/$solrLucrisApiVersion/research-outputs/$id?rendering=$citebib&apiKey=$solrLucrisApiKey";
                            $citebibxml = @file_get_contents($citebibxmlpath);
                            $citebibxml = @simplexml_load_string($citebibxml);
                            //print '<p>' . $citebibxml . '</p>';
                            if($citebibxml) {
                                switch($citebib) {
                                    case 'standard':
                                        $citebibxml->asXml($directory . '/bibtexfilesnovo/' . $id . '_standard.xml');
                                        break;
                                    case 'harvard':
                                        $citebibxml->asXml($directory . '/bibtexfilesnovo/' . $id . '_harvard.xml');
                                        break;
                                    case 'apa':
                                        $citebibxml->asXml($directory . '/bibtexfilesnovo/' . $id . '_apa.xml');
                                        break;
                                    case 'vancouver':
                                        $citebibxml->asXml($directory . '/bibtexfilesnovo/' . $id . '_vancouver.xml');
                                        break;
                                    case 'author':
                                        $citebibxml->asXml($directory . '/bibtexfilesnovo/' . $id . '_author.xml');
                                        break;
                                    case 'RIS':
                                        $citebibxml->asXml($directory . '/bibtexfilesnovo/' . $id . '_ris.xml');
                                        break;
                                    case 'BIBTEX':
                                        $citebibxml->asXml($directory . '/bibtexfilesnovo/' . $id . '_bibtex.xml');
                                        break;
                                }
                            }
                        }
                        $xmlSingle->asXml($directory . '/filestoindexnovo/' . $id . '.xml');
                    }
                }
            }
        }
        //$GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_devlog', array('msg' => $lastId, 'crdate' => time()));
        if($moreChanges==='true') {
            //$this->getXml($solrLucrisApiVersion, $lastId, $solrLucrisApiKey);
            return $lastId;
        } else if($type==='again' && $nextLink) {
            return $dateOrId + 100;
        } else {
            return FALSE;
        }
        //return $moreChanges;
    }
    
    
    function getFiles($buffer, $maximumrecords, $numberofloops, $heritageArray, $startFromHere, $lastModified, $syslang, $solrLucrisApiKey)
    {          
        
        $varArray = array('publication-base_uk','stab');
        $directory = '/var/www/html/typo3/lucrisdump';

        for($i = 0; $i <= $numberofloops; $i++) {
            
            $startrecord = $startFromHere + ($i * 20);
            //$fileName = $startrecord . '.xml';
            $xmlpath = "https://lucris.lub.lu.se/ws/rest/publication?modifiedDate.fromDate=2018-04-10T14:17:22Z&window.size=20&window.offset=$startrecord&orderBy.property=created&rendering=xml_long";
            //single user
            //$xmlpath = "https://lucris.lub.lu.se/ws/rest/publication?associatedPersonUuids.uuid=2bfce8cf-370d-4ca4-a301-ee02daf88dc4&rendering=xml_long";
            ////$xmlpath = "https://lucris.lub.lu.se/ws/rest/publication?window.size=20&window.offset=$startrecord&orderBy.property=created&rendering=xml_long";
            //$xmlpath = "https://lucris.lub.lu.se/ws/rest/publication?modifiedDate.fromDate=$lastModified&window.size=$maximumrecords&window.offset=$startrecord&rendering=xml_long";
            //$xmlpath = "https://lucris.lub.lu.se/ws/rest/publication?uuids.uuid=d7118c99-02a3-45ea-a689-132e0fc2537b&rendering=xml_long";
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