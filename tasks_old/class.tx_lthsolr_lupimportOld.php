<?php
class tx_lthsolr_lupimport extends \TYPO3\CMS\Scheduler\Task\AbstractTask {
	var $feGroupArray = array();
	var $feUserArray = array();
	var $titleCategoriesArray = array();
	
    function execute()
    {
	$executionSucceeded = FALSE;

	//$this->configuration = Tx_Solr_Util::getSolrConfigurationFromPageId($this->site->getRootPageId());
	$this->indexItems();
	$executionSucceeded = TRUE;

	return $executionSucceeded;
    }

    function indexItems()
    {
	$scheme = 'http';
	$host = 'www2.lth.se';
	$port = '8983';
	$path = '/solr/lup/';
	
	//tslib_eidtools::connectDB();

	$solr = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('tx_solr_ConnectionManager')->getConnection($host, $port, $path, $scheme);
  
	if ( ! $solr->ping() ) {
	  echo 'Solr service not responding.';
	  exit;
	}

    //Get org tree
    $xmlpath="http://lup.lub.lu.se/luur/authority_organization?func=getOrganizations&format=xml";
    $xml = new SimpleXMLElement($xmlpath, null, true);
    
    $orgtmpArray = array();
    $orgArray = array();
    
    $orgtmpArray = json_decode(json_encode((array) $xml), 1);
    
    foreach($orgtmpArray['organizationalUnit'] as $key => $value) {
        $orgArray[$value['organizationNumber']]['parent'] = $value['parent'];
        $orgArray[$value['organizationNumber']]['name'] = $value['name'][1];
    }

    //Import from LUP
    //$xmlpath='http://lup.lub.lu.se/sru?version=1.1&operation=searchRetrieve&query=department%20exact%20011200000&sortKeys=publishingYear,,0&startRecord=1&maximumRecords=250';
    $maximumrecords = 250;
    $numberofloops = 1;
    $docs = array();
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
            $recordchangedate = strtotime($recordchangedate);
            $recordcreationdate = strtotime($recordcreationdate);
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
                if (isset($att['type']) && $att['type']=='corporate') {
                    //if ($n->role[0]->roleTerm == 'author') {
                    foreach ($n->namePart as $nPart) {
                        if ($n->role[0]->roleTerm == 'department') {
                            //$department = $nPart;
                            if(isset($n->identifier)) {
                                $departmentLucatArray[] = $n->identifier;
                            }
                            $departmentArray[] = "$nPart";
                            $facultyId = $orgArray["$n->identifier"]['parent'];
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
            /*if ( ($conftitle != '') and ($confConf != '') ) {
                $tconfTit = preg_quote($conftitle);
                $tconfConf = preg_quote($confConf);
                if ( preg_match("|$tconfTit|", $confConf) == 1 ) {
                        $confname = $confConf . ', ';
                } elseif ( preg_match("|$tconfConf|", $conftitle) == 1 ) {
                        $confname = $conftitle . ', ';
                } else {
                        $confname = $conftitle . ', ' . $confConf . ', ';
                }
            } elseif ($conftitle != '') { 
                    $confname = $conftitle . ', '; 
            } elseif ($confConf != '') { 
                    $confname = $confConf . ', ';
            }*/
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

                /*$params['body'][] = array(
                    'index' => array(
                        '_id' => "'$recid'"
                    )
                );*/
                                
               // if(count($corporateArray) > 0) $department = $corporateArray[0];
               // if(count($corporateArray) > 1) $faculty = $corporateArray[1];

                /*$params['body'][] = array(
                    'doc' => array(

                        'title' => "$title",
                        'recordchangedate' => $recordchangedate,
                        'recordcreationdate' => $recordcreationdate,
                        'genre' => "$genre",
                        'genretype' => "$genretype",
                        'nonlu' => "$nonlu",
                        'qualitycontrolled' => "$qualitycontrolled",
                        'additionalinfo' => "$additionalinfo",
                        'publicationstatus' => "$publicationstatus",
                        'year' => "$year",
                        'abstract' => "$abstract",
                        'popularscience' => "$popularscience",
                        'popularscience_exist' => "$popularscience_exist",
                        'language' => "$language",
                        'author' => $authorArray,
                        'keywords' => "$keywords",
                        'url' => "$url",
                        'fulltext' => $fulltext,
                        'confname' => "$confname",
                        'conftitle' => "$conftitle",
                        'department' => $departmentArray,
                        'subject' => "$subject",
                        'researchgroup' => $researchgroupArray,
                    )
                );
                 
                 */
                /*$doc = new SolrInputDocument();

                $doc->addField('id', $recid);
                $doc->addField('title', "$title");
                $doc->addField('abstract', "$abstract");

                $updateResponse = $client->addDocument($doc);*/
                
                $docs[] = array(
                    'id' => $recid,
                    'title' => $title,
                    'recordchangedate' => $recordchangedate,
                    'recordcreationdate' => $recordcreationdate,
                    'genre' => $genre,
                    'genretype' => $genretype,
                    'nonlu' => $nonlu,
                    'qualitycontrolled' => $qualitycontrolled,
                    'additionalinfo' => $additionalinfo,
                    'publicationstatus' => $publicationstatus,
                    'year' => $year,
                    'abstract' => $abstract,
                    'popularscience' => $popularscience,
                    'popularscience_exist' => $popularscience_exist,
                    'luplanguage' => $language,
                    'authorgiven' => $authorGivenArray,
                    'authorfamily' => $authorFamilyArray,
                    'authorlucat' => $authorLucatArray,
                    'authordisplay' => $authorDisplayArray,
                    'keywords' => $keywordsArray,
                    'url' => $url,
                    'fulltext' => $fulltext,
                    'confname' => $confname,
                    'conftitle' => $conftitle,
                    'department' => $departmentArray,
                    'departmentlucat' => $departmentLucatArray,
                    'faculty' => $facultyArray,
                    'subject' => $subject,
                    'subject_s' => $subject,
                    'researchgroup' => $researchgroupArray,
                    'isbn' => $isbn,
                    'issn' => $issn,
                    'publisher' => $publisher,
                    'conference' => $conference,
                    'volume' => $volume,
                    'issue' => $issue,
                    'journal' => $journal,
                    'editor' => $editorArray,
                );
            }
        }
        
        $documents = array();

  foreach ( $docs as $item => $fields ) {
    
    $part = new Apache_Solr_Document();
    
    foreach ( $fields as $key => $value ) {
      if ( is_array( $value ) ) {
        foreach ( $value as $data ) {
          $part->setMultiValue( $key, $data );
        }
      }
      else {
        $part->$key = $value;
      }
    }
    
    $documents[] = $part;
  }
    
  //
  //
  // Load the documents into the index
  // 
  /*print '<pre>';
  print_r($docs);
  print '</pre>';*/
  try {
    $solr->addDocuments( $documents );
    $solr->commit();
    $solr->optimize();
  }
  catch ( Exception $e ) {
    echo $e->getMessage();
  }
    }
}