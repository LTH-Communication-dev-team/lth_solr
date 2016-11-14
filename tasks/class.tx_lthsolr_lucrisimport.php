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

        $dbhost = $settings['dbhost'];
        $db = $settings['db'];
        $user = $settings['user'];
        $pw = $settings['pw'];

        $con = mysqli_connect($dbhost, $user, $pw, $db) or die("40; ".mysqli_error());
    
	if (!$settings['solrHost'] || !$settings['solrPort'] || !$settings['solrPath'] || !$settings['solrTimeout'] || !$settings['solrLucrisId'] || !$settings['solrLucrisPw']) {
	    return 'Please make all settings in extension manager';
	}

        // create a client instance
        $client = new Solarium\Client($config);
        $query = $client->createSelect();
        $buffer = $client->getPlugin('bufferedadd');
        $buffer->setBufferSize(200);

        $current_date = gmDate("Y-m-d\TH:i:s\Z");
        
        $heritageArray = $this->getHeritage($con);
        
        /*Get last modified
        $query->setQuery('doctype:publication');
        $query->addSort('tstamp', $query::SORT_DESC);
        $query->setStart(0)->setRows(200000);
        $response = $client->select($query);
        $idArray = array();
        foreach ($response as $document) {
            $idArray[] = $document->id;
        }*/
        $startFromHere = 7420;
        
        //gc_disable();
        //echo $lastModified;
        //$this->getPublications($config, $client, $buffer, $current_date, $maximumrecords, $numberofloops, $settings, $heritageArray, $startFromHere);
        //$this->getOrganisations($config, $client, $buffer, $current_date, $maximumrecords, $numberofloops, $settings, $lastModified);
        //$this->getUpmprojects($config, $client, $buffer, $current_date, $maximumrecords, $numberofloops, $settings, $lastModified);
        //$this->getStudentPapers($config, $client, $buffer, 100, 1, $startFromHere, $heritageArray);
        //$this->standardCat($config, $client);
        //$this->getType($config, $client, $settings, $startFromHere);
        //$this->getXml($config, $client, $buffer, $current_date, $maximumrecords, $numberofloops, $settings, $heritageArray, $startFromHere);

        $this->getPages($settings['solrHost'] . ':' . $settings['solrPort'] . $settings['solrPath']);
        //$this->getDocuments($client);
        return TRUE;
    }
    
    
    function getPages($solrPath)
    {
        $uid;
        $bodytext;
        $url;
        $startPage = 0;
        
        $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery("uid,msg","tx_devlog","msg LIKE 'lth_solr_page_start_%'");
        $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
        $devUid = $row['uid'];
        $msg = $row['msg'];
        if($msg) {
            $startPage = (integer)array_pop(explode('_', $msg)) + 1000;
            $GLOBALS['TYPO3_DB']->exec_UPDATEquery('tx_devlog', 'uid='.intval($devUid), array('msg' => 'lth_solr_page_start_' . (string)$startPage, 'crdate' => time()));
        } else {
            $GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_devlog', array('msg' => 'lth_solr_page_start_0', 'crdate' => time()));
        }

        $this->initTSFE();
        // return TRUE;
        $cObj = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Frontend\\ContentObject\\ContentObjectRenderer');

        $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery("DISTINCT p.uid,t.bodytext","pages p LEFT JOIN tt_content t ON (p.uid = t.pid AND (CType = 'text' OR CType = 'textpic'))","p.deleted=0 AND p.hidden=0 AND p.doktype = 1 AND (p.fe_group = 0 OR p.fe_group = '')","p.uid","","$startPage,1000");
        while ($row = $GLOBALS["TYPO3_DB"]->sql_fetch_assoc($res)) {
            $uid = $row['uid'];
            $bodytext = $row['bodytext'];
            if($bodytext) $bodytext = urlencode($this->strtrim(strip_tags($bodytext), 200));
            $url = $cObj->typolink_URL(array('parameter' => $uid, 'forceAbsoluteUrl' => 1));
            //echo $uid . ';' . $url . '<br />';
            if($url) $this->extractPage($url, $bodytext, "page$uid", $solrPath);
        }
        $GLOBALS['TYPO3_DB']->sql_free_result($res);
        
        return TRUE;
    }
    
    
    function getDocuments($client)
    {
        $mimeArray = array(
            'application/pdf',
            'text/plain',
            'application/msword',
            'application/vnd.ms-powerpoint',
            'text/html',
            'application/vnd.ms-excel',
            'vnd.openxmlformats-officedocument.presentationml.presentation',
            'application/vnd.ms-office',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'text/rtf',
            'text/x-asm',
            'application/vnd.oasis.opendocument.text',
            'application/vnd.oasis.opendocument.presentation',
            'application/vnd.oasis.opendocument.spreadsheet'
        );
        
        $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery("uid,identifier,name","sys_file","mime_type IN('" . implode("','", $mimeArray) . "')","","","");
        while ($row = $GLOBALS["TYPO3_DB"]->sql_fetch_assoc($res)) {
            $uid = $row['uid'];
            $identifier = $row['identifier'];
            $name = $row['name'];
            if($identifier && $name) {
                $this->extractDocument($client, $uid, $name, PATH_site . 'fileadmin' . $identifier);
            }
        }
        $GLOBALS['TYPO3_DB']->sql_free_result($res);
        return TRUE;
    }
    
    
    function extractPage($pageUrl, $bodytext, $id, $solrPath)
    {
        try {
            $pageUrl = "http://" . $solrPath . "update/extract?literal.id=$id&literal.teaser=$bodytext&literal.doctype=page&fmap.content=content&commit=true&stream.url=$pageUrl";
            //echo $pageUrl;
            $curl = curl_init($pageUrl);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
            //curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "BANURL");
            $res = curl_exec($curl);
        } catch(Exception $e) {
            //echo 'Message: ' .$e->getMessage();
        }

    }
    
    
    function extractDocument($client, $uid, $name, $filePath)
    {
        if(file_exists($filePath)) {
            //echo $filePath;
            // get an extract query instance and add settings
            $query = $client->createExtract();
            $query->addFieldMapping('content', 'body');
            $query->setUprefix('attr_');
            $query->setFile($filePath);
            $query->setCommit(true);
            $query->setOmitHeader(false);

            // add document
            $doc = $query->createDocument();
            $doc->id = "document$uid";
            $doc->title = $name;
            $doc->doctype = 'document';
            $query->setDocument($doc);

            // this executes the query and returns the result
            try {
                $result = $client->extract($query);
            } catch(Exception $e) {
                $GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_devlog', array('msg' => $filePath, 'crdate' => time()));
            }
            
        }
    }
    
    
    function initTSFE($id = 4, $typeNum = 0)
    {
        if (!is_object($GLOBALS['TT'])) {
            $GLOBALS['TT'] = new \TYPO3\CMS\Core\TimeTracker\NullTimeTracker;
            $GLOBALS['TT']->start();
        }
        
        $GLOBALS['TSFE'] = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Frontend\\Controller\\TypoScriptFrontendController',  $GLOBALS['TYPO3_CONF_VARS'], $id, $typeNum);
        
        $GLOBALS['TSFE']->connectToDB();
        $GLOBALS['TSFE']->initFEuser();
        $GLOBALS['TSFE']->determineId();
        //return TRUE;
        $GLOBALS['TSFE']->initTemplate();
        $GLOBALS['TSFE']->getConfigArray();

        /*if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('realurl')) {
            $rootline = \TYPO3\CMS\Backend\Utility\BackendUtility::BEgetRootLine($id);
            $host = \TYPO3\CMS\Backend\Utility\BackendUtility::firstDomainRecord($rootline);
            $_SERVER['HTTP_HOST'] = $host;
        }*/
    }
    
    
    function strtrim($str, $maxlen=100, $elli=NULL, $maxoverflow=15) {
        global $CONF;

        if (strlen($str) > $maxlen) {

            if ($CONF["BODY_TRIM_METHOD_STRLEN"]) {
                return substr($str, 0, $maxlen);
            }

            $output = NULL;
            $body = explode(" ", $str);
            $body_count = count($body);

            $i=0;

            do {
                $output .= $body[$i]." ";
                $thisLen = strlen($output);
                $cycle = ($thisLen < $maxlen && $i < $body_count-1 && ($thisLen+strlen($body[$i+1])) < $maxlen+$maxoverflow?true:false);
                $i++;
            } while ($cycle);
            return $output.$elli;
        }
        else return $str;
    }
    
    
    function standardCat($config, $client)
    {
        $numberofloops = 1;
        
        // create a client instance
        $client = new Solarium\Client($config);
        $query = $client->createSelect();
        $update = $client->createUpdate();
        
        for($i = 0; $i < $numberofloops; $i++) {
            $startrecord = $i * 1000;
            $query->setQuery('doctype:publication AND -title_sort:[* TO *]');
            $query->setStart($startrecord)->setRows(1000);
            $response = $client->select($query);
            $numFound = $response->getNumFound();
            
            $numberofloops = ceil($numFound / 1000);
            
            //if($startrecord > 0) $startrecord++;
            
            
            $ii = 0;
            $docArray = array();
            foreach ($response as $document) {
                $ii++;
                $id = $document->id;
                if($document->title) {
                    if(is_array($document->title)) {
                        $title = $document->title[0];
                    }
                }
                //echo $title;
                $publicationType_en = $document->publicationType_en;
                $publicationType_sv = $document->publicationType_sv;
                //echo $publicationType_en[0] . $publicationType_sv[0];
                ${"doc" . $ii} = $update->createDocument();
                ${"doc" . $ii}->setKey('id', $id);
                ${"doc" . $ii}->addField('standard_category_en', str_replace(' ', '_', $publicationType_en[0]));
                ${"doc" . $ii}->setFieldModifier('standard_category_en', 'set');
                ${"doc" . $ii}->addField('standard_category_sv', str_replace(' ', '_', $publicationType_sv[0]));
                ${"doc" . $ii}->setFieldModifier('standard_category_sv', 'set');
                //${"doc" . $ii}->addField('id_sort', $id);
                //${"doc" . $ii}->setFieldModifier('id_sort', 'set');
                ${"doc" . $ii}->addField('title_sort', $title);
                ${"doc" . $ii}->setFieldModifier('title_sort', 'set');
                $docArray[] = ${"doc" . $ii};
            }
            $update->addDocuments($docArray);
            $update->addCommit();
            $result = $client->update($update);
        }
        
        return TRUE;
    }
    
    
    function getStudentPapers($config, $client, $buffer, $maximumRecords, $numberOfLoops, $startFromHere, $heritageArray)
    {
        $heritageArray = $heritageArray[0];
        for($i = 0; $i < 500; $i++) {
            //echo $i.':'. $numberofloops . '<br />';
            
            $startRecord = ($i * $maximumRecords);
            if($startRecord > 0) $startRecord++;
            //$xmlpath = "https://$lucrisId:$lucrisPw@lucris.lub.lu.se/ws/rest/publication?window.size=$maximumrecords&window.offset=$startrecord&orderBy.property=id&rendering=xml_long";
            $xmlpath = "https://lup.lub.lu.se/student-papers/sru?version=1.1&operation=searchRetrieve&query=submissionStatus%20exact%20public%20AND%20id%3E$startFromHere&startRecord=$startRecord&maximumRecords=$maximumRecords&sortKeys=id";
            
            //echo $xmlpath;
            $GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_devlog', array('msg' => $startrecord, 'crdate' => time()));
            try {
                //$GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_devlog', array('msg' => '200: ' . $xmlpath, 'crdate' => time()));
                $xml = new SimpleXMLElement($xmlpath, null, true);
            } catch(Exception $e) {
                $GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_devlog', array('msg' => '500: ' . $xmlpath, 'crdate' => time()));
            }
            //$this->debug($xml);

            if($xml->records->record) {
                foreach($xml->records->record as $content) {
                    $id;
                    $created;

                    $modified;
                    $genre;
                    $title;
                    $authorName = array();
                    $supervisorName = array();
                    $organisationName_en = array();
                    $organisationName_sv = array();
                    $organisationSourceId = array();
                    $abstract_en;
                    $abstract_sv;
                    $document_url;
                    $document_type;
                    $document_size;
                    $document_limitedVisibility;
                    $publicationDateYear;
                    $language_en;
                    $created;
                    $modified;
                    $language_sv;
                    $keywords_user = array();
                    $heritage = array();

                    $id = (string)$content->recordData->mods->recordInfo->recordIdentifier;

                    $created = (string)$content->recordData->mods->recordInfo->recordCreationDate;

                    $modified = (string)$content->recordData->mods->recordInfo->recordChangeDate;

                    $genre = (string)$content->recordData->mods->genre;

                    $title = (string)$content->recordData->mods->titleInfo->title;

                    //name
                    foreach($content->recordData->mods->name as $name) {
                        $nameTemp = '';
                        foreach($name->namePart as $namePart) {
                            if($nameTemp) $nameTemp .= ' ';
                            $nameTemp .= $namePart;
                        }
                        if($name->role->roleTerm == 'author') {
                            $authorName[] = (string)$nameTemp;
                        } else if($name->role->roleTerm == 'supervisor') {
                            $supervisorName[] = (string)$nameTemp;  //NY FÄLTTYP!!!!!!!!!!!!!!!!!
                        } else if($name->role->roleTerm == 'department') {
                            $organisationName_en[] = (string)$nameTemp;
                            $organisationName_sv[] = (string)$nameTemp;
                            $organisationSourceId[] = (string)$name->identifier;
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

                    //abstract
                    foreach($content->recordData->mods->abstract as $abstract) {
                        if($abstract['lang'] == 'eng') {
                            $abstract_en = (string)$abstract;
                        } else if($abstract['lang'] == 'swe') {
                            $abstract_sv = (string)$abstract;
                        }
                    }

                    //document_url
                    $document_url = (string)$content->recordData->mods->relatedItem->location->url;

                    $document_type = (string)$content->recordData->mods->relatedItem->physicalDescription->internetMediaType; //NY FÄLTTYP!!!!!!!!!!!!!!!!!

                    if($content->recordData->mods->relatedItem->note['type'] == 'fileSize') {
                        $document_size = (string)$content->recordData->mods->relatedItem->note; //NY FÄLTTYP!!!!!!!!!!!!!!!!!
                    }

                    //document_limitedVisibility
                    if($content->recordData->mods->relatedItem->accessCondition['type'] == 'restrictionOnAccess') {
                        $document_limitedVisibility = (string)$content->recordData->mods->relatedItem->accessCondition;
                    }

                    //publicationDateYear
                    $publicationDateYear = (string)$content->recordData->mods->originInfo->dateIssued;

                    //language
                    $language_en = (string)$content->recordData->mods->language->languageTerm;
                    $language_sv = (string)$content->recordData->mods->language->languageTerm;

                    //keywords
                    foreach($content->recordData->mods->subject->topic as $topic) {
                        $keywords_user[] = (string)$topic;
                    }

                    $data = array(
                        'id' => $id,
                        'genre' => $genre,
                        'title' => $title,
                        'title_sort' => $title,
                        'authorName' => array_unique($authorName),
                        'supervisorName' => $supervisorName,
                        'organisationName_en' => $organisationName_en,
                        'organisationName_sv' => $organisationName_sv,
                        'organisationSourceId' => $organisationSourceId,
                        'abstract_en' => $abstract_en,
                        'abstract_sv' => $abstract_sv, 
                        'doctype' => 'studentPaper',
                        'document_url' => $document_url,
                        'document_type' => $document_type,
                        'document_size' => $document_size,
                        'document_limitedVisibility' => $document_limitedVisibility,                    
                        'publicationDateYear' => $publicationDateYear,
                        'language_en' => $language_en,
                        'language_sv' => $language_sv,
                        'keywords_user' => $keywords_user,                    
                        'boost' => '1.0',
                        'date' => gmdate('Y-m-d\TH:i:s\Z', strtotime($created)),
                        'tstamp' => gmdate('Y-m-d\TH:i:s\Z', strtotime($modified)),
                        'digest' => md5($id)
                    );
                    //$this->debug($data);
                    $buffer->createDocument($data);
                }
            }
        }
        $buffer->commit();
        $GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_devlog', array('msg' => $id, 'crdate' => time()));
        return TRUE;
    }
    
    
    function getXml($config, $client, $buffer, $current_date, $maximumrecords, $numberofloops, $settings, $heritageArray, $startFromHere)
    {
        $xmlString;
        $startrecord;
        $lucrisId;
        $lucrisPw;       
        $xmlpath;      
                
        for($i = 0; $i < 1000; $i++) {
            //echo $i.':'. $numberofloops . '<br />';
            
           $startrecord = $startFromHere + ($i * $maximumrecords);
            if($startrecord > 0) $startrecord++;
            
            $lucrisId = $settings['solrLucrisId'];
            $lucrisPw = $settings['solrLucrisPw'];

            $xmlpath = "https://$lucrisId:$lucrisPw@lucris.lub.lu.se/ws/rest/publication?window.size=10&window.offset=$startrecord&orderBy.property=id&rendering=xml_long";
            //$xmlpath = "https://$lucrisId:$lucrisPw@lucris.lub.lu.se/ws/rest/publication?uuids.uuid=defd3bac-a445-4938-b263-a44b59077039&rendering=xml_long";
            
             /*try {
                //$GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_devlog', array('msg' => '200: ' . $xmlpath, 'crdate' => time()));
                $xml = new SimpleXMLElement($xmlpath, null, true);
            } catch(Exception $e) {
                $GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_devlog', array('msg' => '500: ' . $xmlpath, 'crdate' => time()));
            }

            if($xml->children('core', true)->count == 0) {
                return "no items";
            }
            
            $xmlString = $xml->asXML();
            
            */
            $xmlDoc = new DOMDocument();
            $xmlDoc->load($xmlpath);

            $xmlString = $xmlDoc->saveXML();
            $GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_vjchat_entry', array('entry' => "$xmlString", 'pid' => 100, 'style' => 1));
            
        }
        return TRUE;
    }
    
    function getPublications($config, $client, $buffer, $current_date, $maximumrecords, $numberofloops, $settings, $heritageArray, $startFromHere)
    {
        $heritageArray = $heritageArray[0];
        //$this->debug($heritageArray[0]);
        ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        //publications
        ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

        //for($i = 0; $i < $numberofloops; $i++) {
        for($i = 0; $i < $numberofloops; $i++) {
            //echo $i.':'. $numberofloops . '<br />';
            
            $startrecord = $startFromHere + ($i * $maximumrecords);
            if($startrecord > 0) $startrecord++;
            
            $lucrisId = $settings['solrLucrisId'];
            $lucrisPw = $settings['solrLucrisPw'];

            $xmlpath = "https://$lucrisId:$lucrisPw@lucris.lub.lu.se/ws/rest/publication?window.size=$maximumrecords&window.offset=$startrecord&orderBy.property=id&rendering=xml_long";
            //$xmlpath = "https://$lucrisId:$lucrisPw@lucris.lub.lu.se/ws/rest/publication?uuids.uuid=defd3bac-a445-4938-b263-a44b59077039&rendering=xml_long";
            
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
                $type;
                $portalUrl;
                $created;
                $modified;
                $document_title;
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
                $standard_category_sv;
                $standard_category_en;
                $organisationSourceId = array();
                $hertitage = array();
                $keywords_uka_en = array();
                $keywords_uka_sv = array();
                $keywords_user = array();
                $document_url = array();
                $document_title = array();
                $document_limitedVisibility = array();
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
                
                //id
                $id = (string)$content->attributes();

                //portalUrl
                $portalUrl = (string)$content->children('core',true)->portalUrl;
                
                //awardDate
                $awardDate = (string)$content->children('stab',true)->awardDate;
                
                //bibliographicalNote
                if($content->children('publication-base_uk', true)->bibliographicalNote) {
                    foreach($content->children('publication-base_uk', true)->bibliographicalNote->children('cre', true)->localizedString as $localizedString) {
                        if($localizedString->attributes()->locale == 'en_GB') {
                            $bibliographicalNote_en = (string)$localizedString;
                        }
                        if($localizedString->attributes()->locale == 'sv_SE') {
                            $bibliographicalNote_sv[] = (string)$localizedString;
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

                //keywords
                if($content->children('core',true)->keywordGroups) {
                    foreach($content->children('core',true)->keywordGroups->children('core',true)->keywordGroup as $keywordGroup) {
                        if($keywordGroup->children('core',true)->configuration->children('core')->logicalName == 'uka_full') {
                            foreach($keywordGroup->children('core',true)->keyword->children('core',true)->target->children('core')->term->children('core',true)->localizedString as $localizedString) {
                                if($localizedString->attributes()->locale == 'en_GB') {
                                    $keywords_uka_en[] = (string)$localizedString;
                                }
                                if($localizedString->attributes()->locale == 'sv_SE') {
                                    $keywords_uka_sv[] = (string)$localizedString;
                                }
                            }
                        } else if($keywordGroup->children('core',true)->configuration->children('core')->logicalName == 'keywordContainers') {
                            foreach($keywordGroup->children('core',true)->keyword->children('core',true)->userDefinedKeyword As $userDefinedKeyword) {
                                $keywords_user[] = (string)$userDefinedKeyword->children('core',true)->freeKeyword;
                            }
                        }
                    }
                }

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

                //documents
                if($content->children('publication-base_uk',true)->documents) {
                    foreach($content->children('publication-base_uk',true)->documents->children('extension-core',true)->document as $document) {
                        $document_url[] = (string)$document->children('core',true)->url;
                        $document_title[] = (string)$document->children('core',true)->title;
                        $document_limitedVisibility[] = (string)$document->children('core',true)->limitedVisibility;
                    }
                }

                //Authors
                if($content->children('publication-base_uk',true)->persons) {
                    foreach($content->children('publication-base_uk',true)->persons->children('person-template',true)->personAssociation as $personAssociation) {
                        if($personAssociation->children('person-template',true)->person) {
                            $authorIdTemp = (string)$personAssociation->children('person-template',true)->person->attributes();
                            $authorNameTemp = (string)$personAssociation->children('person-template',true)->person->children('person-template',true)->name->children('core',true)->firstName;
                            $authorNameTemp .= ' ' . (string)$personAssociation->children('person-template',true)->person->children('person-template',true)->name->children('core',true)->lastName;
                        }
                        if($personAssociation->children('person-template',true)->externalPerson) {
                            $authorIdTemp = (string)$personAssociation->children('person-template',true)->externalPerson->attributes();
                            $authorNameTemp = (string)$personAssociation->children('person-template',true)->externalPerson->children('externalperson-template',true)->name->children('core',true)->firstName;
                            $authorNameTemp .= ' ' . (string)$personAssociation->children('person-template',true)->externalPerson->children('externalperson-template',true)->name->children('core',true)->lastName;
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
                if($content->children('publication-base_uk',true)->organisations && 
                        $content->children('publication-base_uk',true)->organisations->children('organisation-template',true)->association) {
                    foreach($content->children('publication-base_uk',true)->organisations->children('organisation-template',true)->association as $association) {
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
                if($content->children('stab',true)->associatedExternalOrganisations && $content->children('stab',true)->associatedExternalOrganisations->children('externalorganisation-template',true)->externalOrganisation) {
                    foreach($content->children('stab',true)->associatedExternalOrganisations as $associatedExternalOrganisations) {
                       $externalOrganisationsId[] = (string)$associatedExternalOrganisations->children('externalorganisation-template',true)->externalOrganisation->attributes();
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
                }

                //userDefinedKeywords
                if($content->children('core',true)->keywordGroups && $content->children('core',true)->keywordGroups->children('core',true)->keywordGroup->children('core',true)->keyword && $content->children('core',true)->keywordGroups->children('core',true)->keywordGroup->children('core',true)->keyword->children('core',true)->userDefinedKeyword) {
                    foreach($content->children('core',true)->keywordGroups->children('core',true)->keywordGroup->children('core',true)->keyword->children('core',true)->userDefinedKeyword->children('core',true)->freeKeyword as $freeKeyword) {
                        $userDefinedKeyword[] = (string)$freeKeyword;
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
                $publicationStatus = (string)$content->children('publication-base_uk',true)->publicationStatus;
                
                //hostPublicationTitle
                $hostPublicationTitle = (string)$content->children('publication-base_uk',true)->hostPublicationTitle;
                    
                //publishers
                if($content->children('publication-base_uk',true)->associatedPublishers) {
                    foreach($content->children('publication-base_uk',true)->associatedPublishers->children('publisher-template',true)->publisher as $publisher) {
                        $publisher = (string)$publisher->children('publisher-template',true)->name;
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
                    'type' => $type,
                    'abstract_en' => $abstract_en,
                    'abstract_sv' => $abstract_sv,
                    'authorId' => $authorId,
                    'authorName' => array_unique($authorName),                   
                    'doctype' => 'publication',
                    'document_url' => $document_url,
                    'document_title' => $document_title,
                    'document_limitedVisibility' => $document_limitedVisibility,                    
                    'externalOrganisationsName' => $externalOrganisationsName,
                    'externalOrganisationsId' => $externalOrganisationsId,
                    'event_en' => $event_en,
                    'event_sv' => $event_sv,
                    'event_city' => $event_city,
                    'event_country_en' => $event_country_en,
                    'event_country_sv' => $event_country_sv,
                    'hostPublicationTitle' => $hostPublicationTitle,
                    'journalNumber' => $journalNumber,
                    'keywords_uka_en' => $keywords_uka_en,
                    'keywords_uka_sv' => $keywords_uka_sv,
                    'keywords_user' => $keywords_user,
                    'language_en' => $language_en,
                    'language_sv' => $language_sv,
                    'number_of_pages' => $numberOfPages,
                    'organisationId' => $organisationId,
                    'organisationName_en' => $organisationName_en,
                    'organisationName_sv' => $organisationName_sv,
                    'organisationSourceId' => $organisationSourceId, 
                    'pages' => $pages,
                    'peerReview' => $peerReview,
                    'portalUrl' => $portalUrl,
                    'publicationStatus' => $publicationStatus,
                    'publicationDateYear' => $publicationDateYear,
                    'publicationDateMonth' => $publicationDateMonth,
                    'publicationDateDay' => $publicationDateDay,
                    'publicationType_en' => $publicationType_en,
                    'publicationType_sv' => $publicationType_sv,
                    'publisher' => $publisher,
                    'title' => $title,
                    'volume' => $volume,
                    'standard_category_en' => $publicationType_en,
                    'standard_category_sv' => $publicationType_sv,
                    'awardDate' => gmdate('Y-m-d\TH:i:s\Z', strtotime($awardDate)),
                    'bibliographicalNote_sv' => $bibliographicalNote_sv,
                    'bibliographicalNote_en' => $bibliographicalNote_en,
                    'doi' => $doi,
                    'boost' => '1.0',
                    'date' => gmdate('Y-m-d\TH:i:s\Z', strtotime($created)),
                    'tstamp' => gmdate('Y-m-d\TH:i:s\Z', strtotime($modified)),
                    'digest' => md5($id),
                    //'keyword_en' => $keyword_en,
                    //'keyword_sv' => $keyword_sv,
                    //'userDefinedKeyword' => $userDefinedKeyword,
                );
                //$this->debug($data);
                $buffer->createDocument($data);
            }

        }
        $buffer->commit();
        return TRUE;
    }
        
    
    function getType($config, $client, $settings, $startFromHere)
    {
       
        $ii = 0;
        $docArray = array();
        
        $update = $client->createUpdate();
            
        for($i = 0; $i < 500; $i++) {
            //echo $i.':'. $numberofloops . '<br />';
            
            $startrecord = $startFromHere + ($i * 100);
            if($startrecord > 0) $startrecord++;
            
            $lucrisId = $settings['solrLucrisId'];
            $lucrisPw = $settings['solrLucrisPw'];

            $xmlpath = "https://$lucrisId:$lucrisPw@lucris.lub.lu.se/ws/rest/publication?window.size=100&window.offset=$startrecord&orderBy.property=id";
            //die($xmlpath);
            try {
                //$GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_devlog', array('msg' => '200: ' . $xmlpath, 'crdate' => time()));
                $xml = new SimpleXMLElement($xmlpath, null, true);
            } catch(Exception $e) {
                $GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_devlog', array('msg' => '500: ' . $xmlpath, 'crdate' => time()));
            }

            if($xml->children('core', true)->count == 0) {
                return "no items";
            }

            foreach($xml->xpath('//core:result//core:content') as $content) {
                $ii++;
                //id
                $id = (string)$content->attributes();
                
                //type
                $type = (string)$content->children('core',true)->type;
                if($type) {
                    $type = (string)array_pop(explode('.', $type));
                }
                
                if($id && $type) {
                   
                    ${"doc" . $ii} = $update->createDocument();

                    ${"doc" . $ii}->setKey('id', $id);

                    ${"doc" . $ii}->addField('type', $type);
                    ${"doc" . $ii}->setFieldModifier('type', 'set');

                    // add the documents and a commit command to the update query
                    $docArray[] = ${"doc" . $ii};
                }
            }
            $update->addDocuments($docArray);
            $update->addCommit();
            $result = $client->update($update);

        }

        return TRUE;
    }
     
    
    public function toString($input)
    {
        try {
            return (string) $input;
        } catch (Exception $exception) {
            return '';
        }
    }
        
    
    function getOrganisations($config, $client, $buffer, $current_date, $maximumrecords, $numberofloops, $settings, $lastModified)
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

            $lucrisId = $settings['solrLucrisId'];
            $lucrisPw = $settings['solrLucrisPw'];

            $xmlpath = "https://$lucrisId:$lucrisPw@lucris.lub.lu.se/ws/rest/organisation?window.size=$maximumrecords&window.offset=$startrecord&rendering=xml_long&orderBy.property=id";

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
                $created;
                $modified;
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
                
                //created
                $created = (string)$content->children('core',true)->created;
                
                //modified
                $modified = (string)$content->children('core',true)->modified;

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
                    'date' => gmdate('Y-m-d\TH:i:s\Z', strtotime($created)),
                    'tstamp' => gmdate('Y-m-d\TH:i:s\Z', strtotime($modified)),
                    'digest' => md5($id)
                );
                //$this->debug($data);
                $buffer->createDocument($data);
            }
        }
        $buffer->commit();
        return TRUE;
    }

    
    function getUpmprojects($config, $client, $buffer, $current_date, $maximumrecords, $numberofloops, $settings, $lastModified)
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

            $lucrisId = $settings['solrLucrisId'];
            $lucrisPw = $settings['solrLucrisPw'];

            $xmlpath = "https://$lucrisId:$lucrisPw@lucris.lub.lu.se/ws/rest/upmprojects?window.size=$maximumrecords&window.offset=$startrecord&orderBy.property=id&rendering=xml_long";

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
                $created;
                $modified;
                $title_en;
                $title_sv;
                $startDate;
                $endDate;
                $status;
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
                
                //created
                $created = (string)$content->children('core',true)->created;
                
                //modified
                $modified = (string)$content->children('core',true)->modified;
                
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
                
                //startEndDate
                if($content->children('stab',true)->startEndDate) {
                    $startDate = $content->children('stab',true)->startEndDate->children('extensions-core', true)->startDate;
                    $endDate = $content->children('stab',true)->startEndDate->children('extensions-core', true)->endDate;
                }

                //status
                if($content->children('stab',true)->status) {
                    $status = $content->children('stab',true)->status;
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
                    'projectStartDate' => $this->makeGmDate($startDate),
                    'projectEndDate' => $this->makeGmDate($endDate),
                    'projectStatus' => $status,
                    'organisationId' => $organisationId,
                    'organisationName_en' => $organisationName_en,
                    'organisationName_sv' => $organisationName_sv,
                    'participants' => $participants,
                    'participantId' => $participantId,
                    'descriptions_en' => $descriptions_en,
                    'descriptions_sv' => $descriptions_sv,
                    'boost' => '1.0',
                    'date' => gmdate('Y-m-d\TH:i:s\Z', strtotime($created)),
                    'tstamp' => gmdate('Y-m-d\TH:i:s\Z', strtotime($modified)),
                    'digest' => md5($id)
                );
                //$this->debug($data);
                $buffer->createDocument($data);
            }
        }
        $buffer->commit();
        return TRUE;
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
    
    
    private function getcategories()
    {
     /*
        $categoriyArray = array(
            'Bidrag till tidskrift' = array(
                'Artikel i vetenskaplig tidskrift',
                'Letter',
                'Debatt/notis/ledare',
                'Recension av bok/film/utställning etc.',
                'Special- / temanummer av tidskrift (redaktör)',
                'Publicerat konferensabstrakt'),
            'Kapitel i bok/rapport/konferenshandling' = array(
                'Kapitel i bok',
                'Bidrag till encyklopedi/referensverk',
                'Kapitel i rapport',
                'Förord till konferenspublikation',
                'Konferenspaper i proceeding',
                'För-/efterord'),
            'Bok/rapport' = array(
                'Bok',
                'Antologi (redaktör)',
                'Textkritisk utgåva',
                'Rapport',
                'Konferenspublikation (redaktör)'),
            '!!Contribution to specialist publication or newspaper' = array(
                'Artikel',
                'Dagstidnings- /Nyhetsartikel',
                'Recension av bok/film/utställning etc.',
                'Arbetsdokument',
                'Working paper'),
            'Bidrag till konferens' = array(
                'Konferenspaper, ej i proceeding',
                'Poster',
                'Konferensabstrakt',
                'Annan'),
            'Icke-textmässig form' = array(
                'Konstnärligt arbete',
                'Kurerad/ producerad utställning/ event',
                'Webbpublikation/bloggpost/site'),
            'Avhandling' = array(
                'Doktorsavhandling (monografi)',
                'Doktorsavhandling (sammanläggning)',
                'Licentiatavhandling',
                'Masteruppsats',
                'Doctoral Thesis (artistic)'),
            'Patent' = array(
                'Patent'),
            'Annat bidrag' = array(
                'Annan')
        )
         */
    }
    
    
    private function makeGmDate($input)
    {
        if($input && $input != '') {
            $input = gmDate("Y-m-d\TH:i:s\Z", strtotime($input));
            return $input;
        } else {
            return null;
        }
        
    }
    
    private function debug($input)
    {
        echo '<pre>';
        print_r($input);
        echo '</pre>';
    }
}