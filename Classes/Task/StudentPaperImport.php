<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Lth\Lthsolr\Task;

use TYPO3\CMS\Core\Utility\GeneralUtility;

class StudentPaperImport extends \TYPO3\CMS\Scheduler\Task\AbstractTask {
	
    function execute()
    {
        error_reporting(E_ALL ^ E_NOTICE);
        
	$executionSucceeded = FALSE;
        
        require(__DIR__.'/init.php');
        $maximumrecords = 20;
        $numberofloops = 1;
        
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

        $con = mysqli_connect($dbhost, $user, $pw, $db) or die("42; ".mysqli_error());
        
        $client = new \Solarium\Client($config);
        
        //Get last modified
        $query = $client->createSelect();
        $query->setQuery('docType:studentPaper');
        $query->addSort('changed', $query::SORT_DESC);
        $query->setStart(0)->setRows(1);
        $response = $client->select($query);
        $idArray = array();
        foreach ($response as $document) {
            $lastModified = $document->changed;
        }
        
        $buffer = $client->getPlugin('bufferedadd');
        $buffer->setBufferSize(200);

        $current_date = gmDate("Y-m-d\TH:i:s\Z");
        
        $heritageArray = $this->getHeritage($con);
        
        $startFromHere = 0;

	//$executionSucceeded = $this->getStudentPapers($config, $client, $buffer, $current_date, $maximumrecords, $numberofloops, $startFromHere, $heritageArray, $lastModified, $syslang);
        $executionSucceeded = $this->getProgram($config, $client, $buffer, $current_date, $maximumrecords, $numberofloops, $startFromHere, $heritageArray, $lastModified, $syslang, $settings);
        
	return $executionSucceeded;
    }
    
    
    function getProgram($config, $client, $buffer, $current_date, $maximumrecords, $numberofloops, $startFromHere, $heritageArray, $lastModified, $syslang, $settings)
    {
        $update = $client->createUpdate();
        
        $dbhost = $settings['solrLadokDbhost'];
        $db = $settings['solrLadokDb'];
        $user = $settings['solrLadokId'];
        $pw = $settings['solrLadokPw'];

        $con = mysqli_connect($dbhost, $user, $pw, $db) or die("84; ".mysqli_error());
        
        $query = $client->createSelect();
        $query->setQuery('docType:studentPaper AND organisationSourceId:v1000170 and -authorId:"" and -utbildningsprogram:*');
        $query->setFields(array('id','documentTitle','authorId'));
        $query->setStart(0)->setRows(10000);
        $response = $client->select($query);

        
        foreach ($response as $document) {
            
            $id = $document->id;
            $documentTitle = $document->documentTitle;
            $authorId = $document->authorId;
            
            if($documentTitle && $authorId) {
                foreach($authorId as $key => $value) {
                    $docArray = array();
                    /*$sql = "SELECT n.konto AS konto,L.kod AS kod ";
                    $sql .= "FROM protite2 P JOIN namn N ON P.pnr=N.pnr JOIN antlin A ON A.pnr = P.pnr JOIN linje L ON L.kod = A.progr ";
                    $sql .= "WHERE P.titels = '" . addslashes($documentTitle) . "' OR p.titele = '" . addslashes($documentTitle) . "'";*/
                    $sql = "SELECT L.kod AS kod FROM namn N JOIN antlin A ON A.pnr = N.pnr JOIN linje L "
                            . "ON L.kod = A.progr WHERE n.konto='" . mysqli_real_escape_string($con,$value) . "'";
                    $res = mysqli_query($con, $sql);
                    while ($row = mysqli_fetch_array($res, MYSQLI_ASSOC)) {
                        $kod = $row['kod'];
                        ${"doc"} = $update->createDocument(); 
                        ${"doc"}->setKey('id', $id);
                        
                        ${"doc"}->addField('utbildningsprogram', $kod);
                        ${"doc"}->setFieldModifier('utbildningsprogram', 'set');
                        ${"doc"}->addField('appKey', 'lthsolr');
                        ${"doc"}->setFieldModifier('appKey', 'set');
                        $docArray[] = ${"doc"};
                    }
                    $update->addDocuments($docArray);
                    $update->addCommit();
                    $result = $client->update($update);
                }
            }
        }
        
        //die($i);
        return TRUE;
    }


    function getStudentPapers($config, $client, $buffer, $current_date, $maximumRecords, $numberOfLoops, $startFromHere, $heritageArray, $lastModified, $syslang)
    {
        $heritageArray = $heritageArray[0];
        for($i = 0; $i < 5000; $i++) {
            //echo $i.':'. $numberofloops . '<br />';
            
            $startRecord = ($i * $maximumRecords);
            if($startRecord > 0) $startRecord++;
            //$xmlpath = "https://$lucrisId:$lucrisPw@lucris.lub.lu.se/ws/rest/publication?window.size=$maximumrecords&window.offset=$startrecord&orderBy.property=id&rendering=xml_long";
            $xmlpath = "https://lup.lub.lu.se/student-papers/sru?version=1.1&operation=searchRetrieve&query=submissionStatus%20exact%20public%20AND%20id%3E$startFromHere&startRecord=$startRecord&maximumRecords=$maximumRecords&sortKeys=id";
            
            //echo $xmlpath;
            //$GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_devlog', array('msg' => $startrecord, 'crdate' => time()));
            try {
                //$GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_devlog', array('msg' => '200: ' . $xmlpath, 'crdate' => time()));
                $xml = file_get_contents($xmlpath);
                $xml = simplexml_load_string($xml);
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
                    $documentTitle;
                    $authorName = array();
                    $supervisorName = array();
                    $organisationName_en = array();
                    $organisationName_sv = array();
                    $organisationSourceId = array();
                    $abstract_en = '';
                    $abstract_sv = '';
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
                    $authorId = '';

                    $id = (string)$content->recordData->mods->recordInfo->recordIdentifier;
                    
                    $GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_devlog', array('msg' => $id, 'crdate' => time()));

                    $created = (string)$content->recordData->mods->recordInfo->recordCreationDate;

                    $modified = (string)$content->recordData->mods->recordInfo->recordChangeDate;

                    $genre = (string)$content->recordData->mods->genre;

                    $documentTitle = (string)$content->recordData->mods->titleInfo->title;

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
                            $supervisorName[] = (string)$nameTemp;
                        } else if($name->role->roleTerm == 'department') {
                            $organisationName_en[] = (string)$nameTemp;
                            $organisationName_sv[] = (string)$nameTemp;
                            $organisationSourceId[] = (string)$name->identifier;
                        }
                        if($name->affiliation) {
                            $authorId = (string)$name->affiliation;
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
                    
                    $organisationName = "";
                    $abstract = "";
                    $language_en = "";
                    
                    if($syslang==="sv") {
                        $organisationName = $organisationName_sv;
                        $abstract = $abstract_sv;
                        $language = $language_sv;
                    } else {
                        $organisationName = $organisationName_en;
                        $abstract = $abstract_en;
                        $language = $language_en;
                    }

                    $data = array(
                        'id' => $id,
                        'appKey' => 'lthsolr',
                        'authorId' => $authorId,
                        'genre' => $genre,
                        'documentTitle' => $documentTitle,
                        //'title_sort' => $title,
                        'authorName' => array_unique($authorName),
                        'supervisorName' => $supervisorName,
                        'organisationName' => $organisationName,
                        //'organisationName_sv' => $organisationName_sv,
                        'organisationSourceId' => $organisationSourceId,
                        'abstract' => $abstract,
                        //'abstract_sv' => $abstract_sv, 
                        'docType' => 'studentPaper',
                        'type' => 'studentPaper',
                        'documentUrl' => $document_url,
                        'documentType' => $document_type,
                        'documentSize' => $document_size,
                        'documentLimitedVisibility' => $document_limitedVisibility,                    
                        'publicationDateYear' => $publicationDateYear,
                        'language' => $language,
                        //'language_sv' => $language_sv,
                        'keywordsUser' => $keywords_user,
                        'standardCategory' => $genre,
                        'boost' => '1.0',
                        'date' => gmdate('Y-m-d\TH:i:s\Z', strtotime($created)),
                        'changed' => gmdate('Y-m-d\TH:i:s\Z', strtotime($modified)),
                        'digest' => md5($id)
                    );
                    //$this->debug($data);
                    $buffer->createDocument($data);
                }
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
    
    
    private function debug($inputArray)
    {
        echo '<pre>';
        print_r($inputArray);
        echo '</pre>';
    }
}