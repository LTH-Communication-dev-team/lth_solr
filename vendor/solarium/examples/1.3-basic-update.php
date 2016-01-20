<?php
//set_time_limit(0);
error_reporting(0);

require(__DIR__.'/init.php');
error_reporting(E_ALL ^ E_NOTICE);
    $maximumrecords = 250;
    $numberofloops = 1;
    $docs = array();
    

    // create a client instance
    $client = new Solarium\Client($config);

    // get an update query instance
    $update = $client->createUpdate();
    
    for($i = 0; $i < 2; $i++) {
        //$params = array();
        $startrecord = $i * $maximumrecords;
        if($startrecord > 0) $startrecord++;

        //$xmlpath="http://lup.lub.lu.se/sru?version=1.1&operation=searchRetrieve&query=publishingYear%20<%202025&startRecord=1&maximumRecords=250";
        //http://lup.lub.lu.se/luur/export?func=showRecord&recordOId=2797861
        $xmlpath="http://lup.lub.lu.se/sru?version=1.1&operation=searchRetrieve&query=publishingYear%20exact%202012&startRecord=$startrecord&maximumRecords=$maximumrecords";
        //$xmlpath="http://lup.lub.lu.se/luurSru?version=1.1&operation=searchRetrieve&query=author%20exact%20%22tts-mho%22";
        $xml = new SimpleXMLElement($xmlpath, null, true);

        if($xml->numberOfRecords == 0) {
            return "no items";
        }

        $numberofloops = ceil($xml->numberOfRecords / 250);

        $records = $xml->records[0]->record;
        foreach($records as $rec) {
            $recid = '';
            $recordchangedate = '';
            $recordcreationdate = '';
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
                    
            $data = $rec->recordData[0]->mods;
            $recid = $data->recordInfo[0]->recordIdentifier;
            $recordchangedate = $data->recordInfo[0]->recordChangeDate;
            $recordcreationdate = $data->recordInfo[0]->recordCreationDate;
            $recordchangedate = $recordchangedate;
            $recordcreationdate = $recordcreationdate;
            //Genre
            $genre = $data->genre;
            if ( ($data->genre) && ($t = $data->genre->attributes()) ) {
                $genretype = $t['type'];
            } else {
                $genretype = '';
            }
            
            //qualityControlled, additionalInfo, publicationStatus
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
            
            //Extratitle??
            foreach ($data->relatedItem as $n) {
                foreach($n->attributes() as $name => $val) {
                    if ($name == 'type' && $val == 'host') {
                            if(isset($n->titleInfo[0]->title)) $extratitle = $n->titleInfo[0]->title;
                    }
                }
            }
            
            //Year
            $year = $data->originInfo[0]->dateIssued;
            
            //Publisher
            $publisher = $data->originInfo[0]->publisher;
            
            //Abstract
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
            
            //Title
            $title = $data->titleInfo[0]->title;
            
            //Language
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
            $doc = $update->createDocument();
            $doc->id = $recid;
            $doc->title_s = $title;
            //$doc->recordchangedate_s = strtotime($recordchangedate); 
            //$doc->recordcreationdate_s = strtotime($recordcreationdate);
            $doc->genre_s = $genre;
            $doc->genretype_s = $genretype;
            $doc->nonlu_s = $nonlu;
            $doc->qualitycontrolled_s = $qualitycontrolled;
            $doc->additionalinfo_s = $additionalinfo;
            $doc->publicationstatus_s = $publicationstatus;
            $doc->year_s = $year;
            $doc->abstract_t = $abstract;
            $doc->popularscience_t = $popularscience;
            $doc->popularscience_exist_s = $popularscience_exist;
            $doc->luplanguage_s = $language;
            $doc->authorgiven_ss = $authorGivenArray;
            $doc->authorfamily_ss = $authorFamilyArray;
            $doc->authorlucat_ss = $authorLucatArray;
            $doc->authordisplay_ss = $authorDisplayArray;
            $doc->keywords_ss = $keywordsArray;
            $doc->url_s = $url;
            $doc->fulltext_t = $fulltext;
            $doc->confname_s = $confname;
            $doc->conftitle_s = $conftitle;
            $doc->department_ss = $departmentArray;
            $doc->departmentlucat_ss = $departmentLucatArray;
            $doc->faculty_ss = $facultyArray;
            $doc->subject_s = $subject;
            $doc->researchgroup_ss = $researchgroupArray;
            $doc->isbn_s = $isbn;
            $doc->issn_s = $issn;
            $doc->publisher_s = $publisher;
            $doc->conference_s = $conference;
            $doc->volume_s = $volume;
            $doc->issue_s = $issue;
            $doc->journal_s = $journal;
            $doc->editor_ss = $editorArray;

            // add the document and a commit command to the update query
            $update->addDocument($doc);
            $update->addCommit();
            // this executes the query and returns the result
            $result = $client->update($update);
            echo '<b>Update query executed</b><br/>';
            echo 'Query status: ' . $result->getStatus(). '<br/>';
            echo 'Query time: ' . $result->getQueryTime();
        }
    }