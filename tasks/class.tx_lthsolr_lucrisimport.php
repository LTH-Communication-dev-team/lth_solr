<?php
class tx_lthsolr_lucrisimport extends tx_scheduler_Task {
	
    function execute()
    {
        error_reporting(E_ALL ^ E_NOTICE);

	$executionSucceeded = FALSE;

	$executionSucceeded = $this->indexItems();
        
        return $executionSucceeded;
    }
    

    function indexItems()
    {
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

    
	if (!$settings['solrHost'] || !$settings['solrPort'] || !$settings['solrPath'] || !$settings['solrTimeout']) {
	    return 'Please make all settings in extension manager';
	}

        // create a client instance
        $client = new Solarium\Client($config);
        $buffer = $client->getPlugin('bufferedadd');
        $buffer->setBufferSize(200);

        $current_date = gmDate("Y-m-d\TH:i:s\Z");
      
        //$this->getPublications($config, $client, $buffer, $current_date, $maximumrecords, $numberofloops);
        //$this->getOrganisations($config, $client, $buffer, $current_date, $maximumrecords, $numberofloops);
        $this->getUpmprojects($config, $client, $buffer, $current_date, $maximumrecords, $numberofloops);
        return TRUE;
    }
    
    function getPublications($config, $client, $buffer, $current_date, $maximumrecords, $numberofloops)
    {
        
        ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        //publications
        ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

        //for($i = 0; $i < $numberofloops; $i++) {
        for($i = 0; $i < 150; $i++) {
            //echo $i.':'. $numberofloops . '<br />';
            
            $startrecord = $i * $maximumrecords;
            if($startrecord > 0) $startrecord++;

            $xmlpath = "http://portal.research.lu.se/ws/rest/publication?window.size=$maximumrecords&window.offset=$startrecord&orderBy.property=id&rendering=xml_long";

            try {
                //$GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_devlog', array('msg' => '200: ' . $xmlpath, 'crdate' => time()));
                $xml = new SimpleXMLElement($xmlpath, null, true);
            } catch(Exception $e) {
                $GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_devlog', array('msg' => '500: ' . $xmlpath, 'crdate' => time()));
            }

            if($xml->children('core', true)->count == 0) {
                return "no items";
            }

            $numberofloops = ceil($xml->children('core', true)->count / 20);

            foreach($xml->xpath('//core:result//core:content') as $content) {
                $id;
                $portalUrl;
                $title;
                $abstract_en;
                $abstract_sv;
                $authorIdTemp;
                $authorNameTemp;      
                $authorId = array();
                $authorName = array();
                $organisationId = array();
                $organisationName_en = array();
                $organisationName_sv = array();
                $externalOrganisationsName = array();
                $externalOrganisationsId = array();
                $keyword_en = array();
                $keyword_sv = array();
                $userDefinedKeyword = array();
                $language_en = array();
                $language_sv = array();
                $pages;
                $numberOfPages;
                $volume;
                $journalNumber;
                $publicationStatus;
                $publicationDateYear;
                $publicationDateMonth;
                $publicationDateDay;
                $peerReview;
                $doi;
                $publicationType_en;
                $publicationType_sv;
                
                //id
                $id = (string)$content->attributes();
                
                //portalUrl
                $portalUrl = (string)$content->children('core',true)->portalUrl;
                
                //title
                $title = (string)$content->children('publication-base_uk',true)->title;
                        
                //abstract
                if($content->children('publication-base_uk',true)->abstract) {
                    foreach($content->children('publication-base_uk',true)->abstract->children('core',true)->localizedString as $abstract) {
                        if($abstract->attributes()->locale == 'en_GB') {
                            $abstract_en = (string)$abstract;
                        }
                        if($abstract->attributes()->locale == 'sv_SE') {
                            $abstract_sv = (string)$abstract;
                        }
                    }
                }
                                
                //Authors
                if($content->children('publication-base_uk',true)->persons) {
                    foreach($content->children('publication-base_uk',true)->persons->children('person-template',true)->personAssociation as $personAssociation) {
                        $authorIdTemp = $personAssociation->children('person-template',true)->person->attributes();
                        $authorIdTemp .= $personAssociation->children('person-template',true)->externalPerson->attributes();
                        if($personAssociation->children('person-template',true)->person) {
                            $authorNameTemp = $personAssociation->children('person-template',true)->person->children('person-template',true)->name->children('core',true)->firstName;
                            $authorNameTemp .= ' ' . $personAssociation->children('person-template',true)->person->children('person-template',true)->name->children('core',true)->lastName;
                        }
                        if($personAssociation->children('person-template',true)->externalPerson) {
                            $authorNameTemp = $personAssociation->children('person-template',true)->externalPerson->children('externalperson-template',true)->name->children('core',true)->firstName;
                            $authorNameTemp .= ' ' . $personAssociation->children('person-template',true)->externalPerson->children('externalperson-template',true)->name->children('core',true)->lastName;
                        }
                        if($authorIdTemp) {
                            $authorId[] = (string)$authorIdTemp;
                        }
                        if($authorNameTemp) {
                            $authorName[] = (string)$authorNameTemp;
                        }
                    }
                }
                
                //Organisations
                if($content->children('publication-base_uk',true)->organisations->children('organisation-template',true)->association) {
                    foreach($content->children('publication-base_uk',true)->organisations->children('organisation-template',true)->association as $association) {
                        $organisationId[] = $association->children('organisation-template',true)->organisation->attributes();
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
                    }
                }
                
                //External organisations
                if($content->children('publication-base_uk',true)->associatedExternalOrganisations) {
                    foreach($content->children('publication-base_uk',true)->associatedExternalOrganisations as $associatedExternalOrganisations) {
                       $externalOrganisationsId[] = $associatedExternalOrganisations->children('externalorganisation-template',true)->externalOrganisation->attributes();
                        if($associatedExternalOrganisations->children('externalorganisation-template',true)->externalOrganisation->children('externalorganisation-template',true)->name) {
                            $externalOrganisationsName[] = (string)$associatedExternalOrganisations->children('externalorganisation-template',true)->externalOrganisation->children('externalorganisation-template',true)->name;
                        }
                    }
                }
                
                //keywords (research areas
                if($content->children('core',true)->keywordGroups && $content->children('core',true)->keywordGroups->children('core',true)->keywordGroup->children('core',true)->keyword->children('core',true)->target) {
                    foreach($content->children('core',true)->keywordGroups->children('core',true)->keywordGroup->children('core',true)->keyword->children('core',true)->target->children('core',true)->term->children('core',true)->localizedString as $localizedString) {
                        if($localizedString->attributes()->locale == 'en_GB') {
                            $keyword_en[] = (string)$localizedString;
                        }
                        if($localizedString->attributes()->locale == 'sv_SE') {
                            $keyword_sv[] = (string)$localizedString;
                        }
                    }
                    
                    //userDefinedKeywords
                    foreach($content->children('core',true)->keywordGroups->children('core',true)->keywordGroup->children('core',true)->keyword->children('core',true)->target->children('core',true)->userDefinedKeyword->children('core',true)->freekeyWord as $freekeyWord) {
                        $userDefinedKeyword[] = (string)$freekeyWord;
                    }
                }
                
                //Language
                if($content->children('publication-base_uk',true)->language) {
                    foreach($content->children('publication-base_uk',true)->language->children('core',true)->term->children('core',true)->localizedString as $localizedString) {
                        if($localizedString->attributes()->locale == 'en_GB') {
                            $language_en = (string)$localizedString;
                        }
                        if($localizedString->attributes()->locale == 'sv_SE') {
                            $language_sv = (string)$localizedString;
                        }
                    }
                }
                
                //numberOfPages
                $numberOfPages = (string)$content->children('publication-base_uk',true)->numberOfPages;
                
                //Pages
                $pages = (string)$content->children('publication-base_uk',true)->pages;
                
                //Volume
                $volume = (string)$content->children('publication-base_uk',true)->volume;
                
                //journalNumber
                $journalNumber = (string)$content->children('publication-base_uk',true)->journalNumber;
                
                //publicationStatus
                $publicationStatus =(string)$content->children('publication-base_uk',true)->publicationStatus;
                
                //Publication- year, month, day
                if($content->children('publication-base_uk',true)->publicationDate) {
                    $publicationDateYear = (string)$content->children('publication-base_uk',true)->publicationDate->children('core',true)->year;
                    $publicationDateMonth =  (string)$content->children('publication-base_uk',true)->publicationDate->children('core',true)->month;
                    $publicationDateDay =  (string)$content->children('publication-base_uk',true)->publicationDate->children('core',true)->day;
                }
                
                //peerReview
                if($content->children('publication-base_uk',true)->peerReview) {
                    $peerReview = (string)$content->children('publication-base_uk',true)->peerReview->children('extensions-core',true)->peerReviewed;
                }
                
                //Doi
                if($doi = $content->children('publication-base_uk',true)->dois) {
                    $doi = (string)$content->children('publication-base_uk',true)->dois->children('core',true)->doi->children('core',true)->doi;
                }
                
                //Publication type
                if($content->children('publication-base_uk',true)->typeClassification) {
                    foreach($content->children('publication-base_uk',true)->typeClassification->children('core',true)->term->children('core',true)->localizedString as $localizedString) {
                        if($localizedString->attributes()->locale == 'en_GB') {
                            $publicationType_en = (string)$localizedString;
                        }
                        if($localizedString->attributes()->locale == 'sv_SE') {
                            $publicationType_sv = (string)$localizedString;
                        }
                    }
                }
                
                $data = array(
                    'id' => $id,
                    'doctype' => 'publication',
                    'portalUrl' => $portalUrl,
                    'title' => $title,
                    'abstract_en' => $abstract_en,
                    'abstract_sv' => $abstract_sv,
                    'authorId' => $authorId,
                    'authorName' => array_unique($authorName),
                    'organisationId' => $organisationId,
                    'organisationName_en' => array_unique($organisationName_en),
                    'organisationName_sv' => array_unique($organisationName_sv),
                    'externalOrganisationsName' => $externalOrganisationsName,
                    'externalOrganisationsId' => $externalOrganisationsId,
                    'keyword_en' => $keyword_en,
                    'keyword_sv' => $keyword_sv,
                    'userDefinedKeyword' => $userDefinedKeyword,
                    'language_en' => $language_en,
                    'language_sv' => $language_sv,
                    'numberOfPages' => $numberOfPages,
                    'pages' => $pages,
                    'volume' => $volume,
                    'journalNumber' => $journalNumber,
                    'publicationStatus' => $publicationStatus,
                    'publicationDateYear' => $publicationDateYear,
                    'publicationDateMonth' => $publicationDateMonth,
                    'publicationDateDay' => $publicationDateDay,
                    'peerReview' => $peerReview,
                    'doi' => $doi,
                    'publicationType_en' => $publicationType_en,
                    'publicationType_sv' => $publicationType_sv,
                    'boost' => '1.0',
                    'date' => $current_date,
                    'tstamp' => $current_date,
                    'digest' => md5($id),
                    
                );
                //$this->debug($data);
                $buffer->createDocument($data);

                // add the document and a commit command to the update query
                //$update->addDocument($doc);
                // this executes the query and returns the result
            }

        }
        $buffer->commit();
        return TRUE;
    }
        
        
    function getOrganisations($config, $client, $buffer, $current_date, $maximumrecords, $numberofloops)
    {
        ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        //organisations
        ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

        $numberofloops = 1;
        $maximumrecords = 20;
        $i = 0;
        for($i = 0; $i < $numberofloops; $i++) {
            //echo $i.':'. $numberofloops . '<br />';

            $startrecord = $i * $maximumrecords;
            if($startrecord > 0) $startrecord++;

            $xmlpath = "http://portal.research.lu.se/ws/rest/organisation?window.size=$maximumrecords&window.offset=$startrecord&rendering=xml_long&orderBy.property=id";

            try {
                //$GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_devlog', array('msg' => '200: ' . $xmlpath, 'crdate' => time()));
                $xml = new SimpleXMLElement($xmlpath, null, true);
            } catch(Exception $e) {
                $GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_devlog', array('msg' => '500: ' . $xmlpath, 'crdate' => time()));
            }

            if($xml->children('core', true)->count == 0) {
                return "no items";
            }

            $numberofloops = ceil($xml->children('core', true)->count / 20);

            foreach($xml->xpath('//core:result//core:content') as $content) {
                $id;
                $portalUrl;
                $name_en;
                $name_sv;
                $organisationId = array();
                $organisationName_en = array();
                $organisationName_sv = array();
                $typeClassification_en;
                $typeClassification_sv;
                $sourceId;

                //id
                $id = (string)$content->attributes();

                //portalUrl
                $portalUrl = (string)$content->children('core',true)->portalUrl;

                //name
                if($content->children('stab1',true)->name) {
                    foreach($content->children('stab1',true)->name->children('core',true)->localizedString as $name) {
                        if($name->attributes()->locale == 'en_GB') {
                            $name_en = (string)$name;
                        }
                        if($name->attributes()->locale == 'sv_SE') {
                            $name_sv = (string)$name;
                        }
                    }
                }
                
                //organisation
                if($content->children('stab1',true)->organisations) {
                    foreach($content->children('stab1',true)->organisations->children('stab1',true)->organisation as $organisation) {
                        $organisationId[] = (string)$organisation->attributes();
                        if($organisation->children('stab1',true)->name) {
                            foreach($organisation->children('stab1',true)->name->children('core',true) as $localizedString) {
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

                //typeClassification
                if($content->children('stab1',true)->typeClassification) {
                    foreach($content->children('stab1',true)->typeClassification->children('core',true)->term->children('core',true)->localizedString as $typeClassification) {
                        if($typeClassification->attributes()->locale == 'en_GB') {
                            $typeClassification_en = (string)$typeClassification;
                        }
                        if($typeClassification->attributes()->locale == 'sv_SE') {
                            $typeClassification_sv = (string)$typeClassification;
                        }
                    }
                }

                //sourceId
                $sourceId = (string)$content->children('stab1',true)->external->children('extensions-core',true)->sourceId;

                $data = array(
                    'id' => $id,
                    'doctype' => 'organisation',
                    'portalUrl' => $portalUrl,
                    'name_en' => $name_en,
                    'name_sv' => $name_sv,
                    'organisationId' => $organisationId,
                    'organisationName_en' => $organisationName_en,
                    'organisationName_sv' => $organisationName_sv,
                    'typeClassification_en' => $typeClassification_en,
                    'typeClassification_sv' => $typeClassification_sv,
                    'sourceId' => $sourceId,
                    'boost' => '1.0',
                    'date' => $current_date,
                    'tstamp' => $current_date,
                    'digest' => md5($id)
                );
                //$this->debug($data);
                $buffer->createDocument($data);
            }
        }
        $buffer->commit();
        return TRUE;
    }

    
    function getUpmprojects($config, $client, $buffer, $current_date, $maximumrecords, $numberofloops)
    {
        ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        //upmprojects
        ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

        $numberofloops = 1;
        $maximumrecords = 20;
        $i = 0;
        for($i = 0; $i < $numberofloops; $i++) {
            //echo $i.':'. $numberofloops . '<br />';

            $startrecord = $i * $maximumrecords;
            if($startrecord > 0) $startrecord++;

            $xmlpath = "http://portal.research.lu.se/ws/rest/upmprojects?window.size=$maximumrecords&window.offset=$startrecord&orderBy.property=id&rendering=xml_long";

            try {
                //$GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_devlog', array('msg' => '200: ' . $xmlpath, 'crdate' => time()));
                $xml = new SimpleXMLElement($xmlpath, null, true);
            } catch(Exception $e) {
                $GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_devlog', array('msg' => '500: ' . $xmlpath, 'crdate' => time()));
            }

            if($xml->children('core', true)->count == 0) {
                return "no items";
            }

            $numberofloops = ceil($xml->children('core', true)->count / 20);

            foreach($xml->xpath('//core:result//core:content') as $content) {
                $id;
                $portalUrl;
                $title_en;
                $title_sv;
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

                //title
                if($content->children('stab',true)->title) {
                    foreach($content->children('stab',true)->title->children('core',true)->localizedString as $title) {
                        if($title->attributes()->locale == 'en_GB') {
                            $title_en = (string)$title;
                        }
                        if($title->attributes()->locale == 'sv_SE') {
                            $title_sv = (string)$title;
                        }
                    }
                }
                
                //descriptions
                if($content->children('stab',true)->descriptions) {
                    foreach($content->children('stab',true)->descriptions as $descriptions) {
                        if($descriptions->children('extensions-core',true)->classificationDefinedField->children('extensions-core',true)->value) {
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
                $sourceId = (string)$content->children('stab1',true)->external->children('extensions-core',true)->sourceId;

                $data = array(
                    'id' => $id,
                    'doctype' => 'upmproject',
                    'portalUrl' => $portalUrl,
                    'title_en' => $title_en,
                    'title_sv' => $title_sv,
                    'organisationId' => $organisationId,
                    'organisationName_en' => $organisationName_en,
                    'organisationName_sv' => $organisationName_sv,
                    'participants' => $participants,
                    'participantId' => $participantId,
                    'descriptions_en' => $descriptions_en,
                    'descriptions_sv' => $descriptions_sv,
                    'boost' => '1.0',
                    'date' => $current_date,
                    'tstamp' => $current_date,
                    'digest' => md5($id)
                );
                //$this->debug($data);
                $buffer->createDocument($data);
            }
        }
        $buffer->commit();
        return TRUE;
    }
    
    private function debug($input)
    {
        echo '<pre>';
        print_r($input);
        echo '</pre>';
    }
}