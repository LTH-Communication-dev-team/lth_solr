<?php
class tx_lthsolr_lupimport extends \TYPO3\CMS\Scheduler\Task\AbstractTask {

	
    function execute()
    {
        error_reporting(E_ALL ^ E_NOTICE);

	$executionSucceeded = FALSE;

	//$this->configuration = Tx_Solr_Util::getSolrConfigurationFromPageId($this->site->getRootPageId());
	$executionSucceeded = $this->indexItems();
        return $executionSucceeded;
    }

    function indexItems()
    {
        //tslib_eidtools::connectDB();
        require(__DIR__.'/init.php');
        $maximumrecords = 250;
        $numberofloops = 1;
        //$docs = array();
        
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
        $buffer->setBufferSize(250);

      
        for($i = 0; $i < $numberofloops; $i++) {
            //echo $i.':'. $numberofloops . '<br />';
            
            //$params = array();
            $startrecord = $i * $maximumrecords;
            if($startrecord > 0) $startrecord++;

            $xmlpath = 'http://lup.lub.lu.se/sru?version=1.1&operation=searchRetrieve&query=publishingYear<2025&startRecord='.$startrecord.'&maximumRecords='.$maximumrecords;
            //http://lup.lub.lu.se/luur/export?func=showRecord&recordOId=2797861
            //$xmlpath="http://lup.lub.lu.se/sru?version=1.1&operation=searchRetrieve&startRecord=$startrecord&maximumRecords=$maximumrecords";
            //echo $xmlpath;
            //$xmlpath="http://lup.lub.lu.se/luurSru?version=1.1&operation=searchRetrieve&query=author%20exact%20%22tts-mho%22";
            try {
                //$GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_devlog', array('msg' => '200: ' . $xmlpath, 'crdate' => time()));
                $xml = new SimpleXMLElement($xmlpath, null, true);
                
            } catch(Exception $e) {
                $GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_devlog', array('msg' => '500: ' . $xmlpath, 'crdate' => time()));
            }
           
//echo $xml->numberOfRecords;
            if($xml->numberOfRecords == 0) {
                return "no items";
            }

            $numberofloops = ceil($xml->numberOfRecords / 250);

            $records = $xml->records[0]->record;
             
            foreach($records as $rec) {
                $recid = '';
                $recordchangedate = '';
                $recordcreationdate = '';
                $recorddateapproved = '';
                $genre = '';
                $genretype = '';
                $nonlu = '';
                $qualitycontrolled = '';
                $additionalinfo = '';
                $publicationstatus = '';
                $extratitle = '';
                $popularscience = '';
                $popularscience_exist = '';
                $year = '';
                $abstract = '';
                $title = '';
                $language = '';

                $authors = '';
                $url = '';
                $confname = '';
                $conftitle = '';
                $faculty = '';
                $department = '';
                $lucatid = '';
                $subject = '';
                $researchgroup = '';
                $fulltext = '';
                $given = '';
                $family = '';
                $isbn = '';
                $issn = '';
                $publisher = '';
                $conference = '';
                $volume = '';
                $issue = '';
                $journal = '';

                //Data***************************************************************
                $data = $rec->recordData[0]->mods;
                //Recorddata*********************************************************
                $recid = $data->recordInfo[0]->recordIdentifier;
                $recordchangedate = $data->recordInfo[0]->recordChangeDate;
                $recordcreationdate = $data->recordInfo[0]->recordCreationDate;
                $recorddateapproved = $data->recordInfo[0]->recordDateApproved;

                //Genre**************************************************************
                $genre = $data->genre;
                if ( ($data->genre) && ($t = $data->genre->attributes()) ) {
                    $genretype = $t['type'];
                } else {
                    $genretype = '';
                }

                //qualityControlled, additionalInfo, publicationStatus´***************
                foreach ($data->note as $n) {
                    foreach($n->attributes() as $name => $val) {
                        if ($name == 'type' && $val == 'nonLu') {
                            $nonlu=$n;
                        } elseif ($name == 'type' && $val == 'qualityControlled') {
                            $qualitycontrolled=$n;
                        } elseif ($name == 'type' && $val == 'additionalInfo') {
                            $additionalinfo=$n;
                        } elseif ($name == 'type' && $val == 'publicationStatus') {
                            $publicationstatus=$n;
                        }
                    }
                }

                //Extratitle??*********************************************************
                foreach ($data->relatedItem as $n) {
                    foreach($n->attributes() as $name => $val) {
                        if ($name == 'type' && $val == 'host') {
                                if(isset($n->titleInfo[0]->title)) $extratitle = $n->titleInfo[0]->title;
                        }
                    }
                }

                //Year*****************************************************************
                $year = $data->originInfo[0]->dateIssued;

                //Publisher************************************************************
                $publisher = $data->originInfo[0]->publisher;

                //Abstract*************************************************************
                foreach ($data->abstract as $n) {
                    $att=$n->attributes();
                    if (isset($att['type']) && $att['type']=='popular') {
                        $popularscience = "$n";
                        $popularscience_exist = "Show with popular science";
                    } else {
                        $abstract = "$n";
                        $popularscience_exist = "Show without popular science";
                    }

                }

                //Title******************************************************************
                $title = $data->titleInfo[0]->title;

                //Language***************************************************************
                $language = $data->language[0]->languageTerm;

                //Authors
                $keywordsArray = array();
                foreach ($data->subject as $n) {
                    $att=$n->attributes();
                    if (isset($att['authority'])) { 
                        //continue; 
                        $subject = $n->topic;
                    } else {
                        //$tmp=array();
                        foreach ($n->topic as $v) { 
                            //$tmp[]=$v; 
                            $keywordsArray[] = $v;
                            //echo $v;
                        }
                        //$keywords = implode(', ', $tmp);
                    }
                }

                $dateO=preg_split("/T|\+/",$data->dateOther);
                $date=$dateO[0];

                //$authorArray = array();
                $authorGivenArray = array();
                $authorFamilyArray = array();
                $authorTermsOfAddressArray = array();
                $authorLucatArray = array();
                $authorDisplayArray = array();
                $departmentArray = array();
                $departmentLucatArray = array();
                $facultyArray = array();
                $researchgroupArray = array();
                $id=0;
                foreach ($data->name as $n) {
                    $authorDisplay = '';
                    $att=$n->attributes();
                    if (isset($att['type']) && $att['type']=='personal') {
                        if ($n->role[0]->roleTerm == 'author') {
                            foreach ($n->namePart as $nPart) {
                                $natt = $nPart->attributes();
                                if ( $natt['type'] == 'given' ) {
                                        //if(isset($au[$id]['first'])) $au[$id]['first'] .= $nPart . ' ';
                                        //$authors .= $nPart . ' ';
                                    //$authorArray[$id]['given'] = "$nPart";
                                    $authorGivenArray[$id] = "$nPart";
                                    $authorDisplay = "$nPart";
                                } elseif ( $natt['type'] == 'family' ) {
                                        //$au[$id]['last'] = $nPart;
                                        //$authors .= $nPart . ' ';
                                    //$authorArray[$id]['family'] = "$nPart";
                                    $authorFamilyArray[$id] = "$nPart";
                                    $authorDisplay .= ' ' . "$nPart";
                                } elseif ( $natt['type'] == 'termsOfAddress' ) {
                                        //$au[$id]['last'] = $nPart;
                                        //$authors .= $nPart . ' ';
                                    //$authorArray[]['termsOfAddress'] = $nPart;
                                    $authorDisplay = "$nPart" . $authorDisplay;
                                }
                            }
                            //$authors .= '; ';
                            //$au[$id]['affil'] = $n->affiliation;
                            $authorDisplayArray[$id] = $authorDisplay;
                            $authorLucatArray[$id] = $n->affiliation;

                            $id++;

                        }
                    }
                    //Department

                    $did=0;
                    $rid=0;
                    $facultyId = '';
                    //$facultyKey = '';
                    $orgArray = array();
                    if (isset($att['type']) && $att['type']=='corporate') {
                        //if ($n->role[0]->roleTerm == 'author') {
                        foreach ($n->namePart as $nPart) {
                            if ($n->role[0]->roleTerm == 'department') {
                                //$department = $nPart;
                                if(isset($n->identifier)) {
                                    $departmentLucatArray[] = $n->identifier;
                                }
                                $departmentArray[] = "$nPart";
                                //$facultyKey = 
                                $facultyId = $orgArray[strval($n->identifier)]['parent'];
                                if(isset($orgArray[$facultyId]['name'])) $facultyArray[] = $orgArray[$facultyId]['name'];
                                $did++;
                            }
                            if ($n->role[0]->roleTerm == 'research group') {
                                //$researchgroup .= $nPart;
                                $researchgroupArray[] = "$nPart";
                            }
                        }
                        //}
                    }
                }

                //Editors
                $editorArray = array();
                $tmpEditor = '';
                $id=0;
                foreach ($data->name as $n) {
                    $att=$n->attributes();
                    if (isset($att['type']) && $att['type']=='personal') {
                        if ($n->role[0]->roleTerm == 'editor') {
                            foreach ($n->namePart as $nPart) {
                                $natt = $nPart->attributes();
                                if ( $natt['type'] == 'given' ) {
                                        //if(isset($ed[$id]['first'])) $ed[$id]['first'] .= $nPart . ' ';
                                        //$authors .= $nPart . ' ';
                                    $tmpEditor = $nPart;
                                } elseif ( $natt['type'] == 'family' ) {
                                        //$ed[$id]['last'] = $nPart;
                                        //$authors .= $nPart . ' ';
                                    $tmpEditor = $nPart;
                                }
                            }
                            $editorArray[] = $tmpEditor;
                            //$authors .= '; ';
                            //$ed[$id]['affil'] = $n->affiliation;
                            //$id++;
                        }
                    }
                }

                //Url
                foreach ($data->relatedItem as $n) {
                    if ($n->location) {
                            $url=$n->location[0]->url;
                            break;
                    }
                }

                //Fulltext
                if($url) {
                    $fulltext = 'Only publications with fulltext';
                }

                //##############confName######################
                foreach ($data->relatedItem as $rel) {
                    $att=$rel->attributes();
                    if (isset($att['type']) && $att['type']=='host') {
                        if(isset($rel->titleInfo[0]->title)) $journal = $rel->titleInfo[0]->title;
                    }
                }
                $confConf = '';
                foreach ($data->name as $n) {
                    $att=$n->attributes();
                    if (isset($att['type']) && $att['type']=='conference') {
                        $conference = $n->namePart;
                    }
                }


                if ( ($genre == 'conference paper') or ($genre == 'conference abstract') ) {
                    if ( ($data->originInfo) && $data->originInfo->place && $data->originInfo->place->placeTerm ) {
                        foreach ($data->originInfo->place->placeTerm as $n) {
                            $att=$n->attributes();
                            if (isset($att['type']) && $att['type']=='text') {
                                    $conference .= ', ' . $n[0];
                            }
                        }
                    }
                }
                foreach ($data->relatedItem as $n) {
                    $att=$n->attributes();
                    if (isset($att['type']) && $att['type']=='host') {
                        if ( ($n->part) && ($n->part->detail) ) {
                            foreach ($n->part->detail as $d) {
                                $att=$d->attributes();
                                if (isset($att['type']) && $att['type']=='volume') {
                                    $volume = $d->number[0];
                                } elseif (isset($att['type']) && $att['type']=='issue') {
                                    $issue = $d->number[0];
                                }
                            }
                        }
                        if ( ($n->part) && ($n->part->extent) ) {
                            foreach ($n->part->extent as $d) {
                                $att = $d->attributes();
                                if ( isset($att['unit']) && ($att['unit'] == 'pages') && $d->start ) {
                                    $pages = ' pp. ' . $d->start[0] . '-' . $d->end[0];
                                }
                            }
                        }
                    }
                }

                //Isbn, Issn
                if ( ($genre != 'article') and ($genre != 'conference paper') and ($genre != 'conference abstract') ) {
                    foreach ($data->relatedItem as $n) {
                        $att=$n->attributes();
                        if (isset($att['type']) && $att['type']=='host') {
                            $d = $n->identifier[0];
                            if ( $d ) {
                                $datt=$d->attributes();
                                if (isset($datt['type']) && $datt['type']=='isbn') {
                                    $isbn = $d;
                                }
                                if (isset($datt['type']) && $datt['type']=='issn') {
                                    $issn = $d;
                                }
                            }
                        }
                    }
                }               

                // create a new document for the data
                // please note that any type of validation is missing in this example to keep it simple!
                //$doc = $update->createDocument();
                //echo gmdate($dateFormat, strtotime($recordchangedate));
                
                $data = array(
                    'id' => $recid,
                    'title_t' => $title,
                    'genre_t' => $genre,
                    'genretype_t' => $genretype,
                    'nonlu_t' => $nonlu,
                    'qualitycontrolled_t' => $qualitycontrolled,
                    'additionalinfo_t' => $additionalinfo,
                    'publicationstatus_t' => $publicationstatus,
                    'year_t' => $year,
                    'abstract_t' => $abstract,
                    'popularscience_t' => $popularscience,
                    'popularscience_exist_t' => $popularscience_exist,
                    'luplanguage_t' => $language,
                    'authorgiven_txt' => $authorGivenArray,
                    'authorfamily_txt' => $authorFamilyArray,
                    'authorlucat_txt' => $authorLucatArray,
                    'authordisplay_txt' => $authorDisplayArray,
                    'keywords_txt' => $keywordsArray,
                    'url_t' => $url,
                    'fulltext_t' => $fulltext,
                    'confname_t' => $confname,
                    'conftitle_t' => $conftitle,
                    'department_txt' => $departmentArray,
                    'departmentlucat_txt' => $departmentLucatArray,
                    'faculty_txt' => $facultyArray,
                    'subject_t' => $subject,
                    'researchgroup_txt' => $researchgroupArray,
                    'isbn_t' => $isbn,
                    'issn_t' => $issn,
                    'publisher_t' => $publisher,
                    'conference_t' => $conference,
                    'volume_t' => $volume,
                    'issue_t' => $issue,
                    'journal_t' => $journal,
                    'editor_txt' => $editorArray
                );

                
                if(gmdate($dateFormat, strtotime($recordchangedate))!=='') {
                    $data['recordchangedate_dt'] = gmdate($dateFormat, strtotime($recordchangedate));
                }
                if(gmdate($dateFormat, strtotime($recordcreationdate))!=='') {
                    $data['recordcreationdate_dt'] = gmdate($dateFormat, strtotime($recordcreationdate));
                }
                if(gmdate($dateFormat, strtotime($recorddateapproved))!=='') {
                    $data['recorddateapproved_dt'] = gmdate($dateFormat, strtotime($recorddateapproved));
                }
                
                $buffer->createDocument($data);

                // add the document and a commit command to the update query
                //$update->addDocument($doc);
                // this executes the query and returns the result
            }
        }
        // get an update query instance
        //$update = $client->createUpdate();
        $buffer->flush();
        return TRUE;
    }
}