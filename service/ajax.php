<?php
// Exit, if script is called directly (must be included via eID in index_ts.php)
if (!defined ('PATH_typo3conf')) die ('Could not access this script directly!');

require(__DIR__.'/init.php');

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

$content = '';
$query = '';
$action = '';
$sid = '';

$term = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP("term");
$peopleOffset = htmlspecialchars(\TYPO3\CMS\Core\Utility\GeneralUtility::_GP("peopleOffset"));
$pageOffset = htmlspecialchars(\TYPO3\CMS\Core\Utility\GeneralUtility::_GP("pageOffset"));
$documentOffset = htmlspecialchars(\TYPO3\CMS\Core\Utility\GeneralUtility::_GP("documentOffset"));
$courseOffset = htmlspecialchars(\TYPO3\CMS\Core\Utility\GeneralUtility::_GP("courseOffset"));
$more = htmlspecialchars(\TYPO3\CMS\Core\Utility\GeneralUtility::_GP("more"));
$query = htmlspecialchars(\TYPO3\CMS\Core\Utility\GeneralUtility::_GP("query"));
$action = htmlspecialchars(\TYPO3\CMS\Core\Utility\GeneralUtility::_GP("action"));
$scope = htmlspecialchars(\TYPO3\CMS\Core\Utility\GeneralUtility::_GP("scope"));
$facet = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP("facet");
$pid = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('pid');
$syslang = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('syslang');
$table_start = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('table_start');
$table_length = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('table_length');
$pageid = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('pageid');
$custom_categories = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('custom_categories');
$categories = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('categories');
//$categoriesThisPage = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('categoriesThisPage');
//$introThisPage = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('introThisPage');
$addPeople = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('addPeople');
$detailPage = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('detailPage');
$papertype = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('papertype');
$selection = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('selection');
$sid = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP("sid");
date_default_timezone_set('Europe/Stockholm');

//tslib_eidtools::connectDB();
//$GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_devlog', array('msg' => $query, 'crdate' => time()));
//$GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_devlog', array('msg' => $sys_language_uid, 'crdate' => time()));

switch($action) {
    case 'searchListShort':
        $content = searchListShort($term, $config);
        break;
    case 'searchShort':
        $content = searchShort($term, $config);
        break;
    case 'searchLong':
        $content = searchLong($term, $table_length, $peopleOffset, $pageOffset, $documentOffset, $courseOffset, $more, $config);
        break;
    case 'searchMorePeople':
        $content = searchMore($term, 'people', $peopleOffset, $pageOffset, $documentOffset, $config);
        break;
    case 'searchMorePages':
        $content = searchMore($term, 'pages', $peopleOffset, $pageOffset, $documentOffset, $config);
        break;    
    case 'searchMoreDocuments':
        $content = searchMore($term, 'documents', $peopleOffset, $pageOffset, $documentOffset, $config);
        break;
    case 'listPublications':
        $content = listPublications($facet, $scope, $syslang, $config, $table_length, $table_start, $pageid, $query, $selection);
        break;
    case 'listStudentPapers':
        $content = listStudentpapers($facet, $scope, $syslang, $config, $table_length, $table_start, $pageid, $categories, $query, $papertype);
        break;
    case 'showPublication':
        $content = showPublication($term, $syslang, $config, $detailPage);
        break;
    case 'showStudentPaper':
        $content = showStudentPaper($scope, $syslang, $config, $detailPage);
        break;
    case 'listProjects':
        $content = listProjects($scope, $syslang, $config);
        break;
    case 'showProject':
        $content = showProject($scope, $syslang, $config);
        break;
    case 'listStaff':
        $content = listStaff($facet, $pageid, $pid, $syslang, $scope, $table_length, $table_start, $categories, 
                $custom_categories, $config, $query);
        break;
    case 'showStaff':
        $content = showStaff($scope, $config, $table_length, $syslang);
        break;
    case 'rest':
        $content = rest();
        break;    
    default:
        $content = basicSelect($query, $config);
        break;
}

print $content;


function searchShort($term, $config)
{
    $client = new Solarium\Client($config);
    
    /*$query = $client->createSuggester();
    $query->setQuery($term);
    $query->setDictionary('suggest');
    $query->setOnlyMorePopular(true);
    $query->setCount(10);
    $query->setCollate(true);
    $resultset = $client->suggester($query);
    $suggestions = array();
    foreach ($resultset as $term => $termResult) {
        foreach ($termResult as $result) {
            $suggestions[] = $result;
        }
    }
    $data = $suggestions;*/

    $query = $client->createSelect();
    
    $term = trim($term);
    if(substr($term, 0,1) == '"' && substr($term,-1) != '"') {
        $term = ltrim($term,'"');
    }

    $groupComponent = $query->getGrouping();
    if(substr($term, 0,1) == '"' && substr($term,-1) == '"') {
        $groupComponent->addQuery('doctype:lucat AND (display_name:' . str_replace(' ','\\ ',$term) . ' OR phone:' . str_replace(' ','',$term) . ' OR email:' . $term . ')');
    } else {
        $groupComponent->addQuery('doctype:lucat AND (display_name:*' . str_replace(' ','\\ ',$term) . '* OR phone:*' . str_replace(' ','',$term) . '* OR email:"' . $term . '")');
    }
    $groupComponent->addQuery('id:page* AND content:*' . str_replace(' ','\\ ',$term) . '*');
    $groupComponent->setSort('last_name_sort asc');
    $groupComponent->setLimit(5);    
    $resultset = $client->select($query);
    $groups = $resultset->getGrouping();
    foreach ($groups as $groupKey => $group) {
        foreach ($group as $document) {        
            
            $doktype = $document->doctype;
            
            if($doktype === 'lucat') {
                $id = $document->uuid;
                $value = $document->uuid;
                $label = fixArray($document->display_name);
                if($document->phone) $label .= ', ' . fixPhone(fixArray($document->phone));
                $data[] = array(
                    'id' => $id,
                    'label' => $label,
                    'value' => 'lucat_' . $value
                );
            } else {
                $id = $document->id;
                $value = $document->id;
                $label = fixArray($document->title);
                    $data[] = array(
                    'id' => $id,
                    'label' => $label,
                    'value' => $value
                );
            }
        }
    }
    return json_encode($data);
}


function searchLong($term, $tableLength, $peopleOffset, $pageOffset, $documentOffset, $courseOffset, $more, $config)
{
    $people;
    $documents;
    $facet;
    $doktype;
    $display_name;
    $phone;
    $email;
    $title;
    $id;
    $url;
    $introText;
    
    $facetResult = array();
    $peopleData = array();
    $pageData = array();
    $documentData = array();
    $courseData = array();
    
    $client = new Solarium\Client($config);
    $query = $client->createSelect();
    $query->setStart($table_start)->setRows($table_length);
    
    $term = trim($term);
    if(substr($term, 0,1) == '"' && substr($term,-1) != '"') {
        $term = ltrim($term,'"');
    }

    $groupComponent = $query->getGrouping();
    
    if($more != 'pages' && $more != 'documents' && $more != 'courses') {  
        if(substr($term, 0,1) == '"' && substr($term,-1) == '"') {
            $groupComponent->addQuery('doctype:lucat AND (display_name:'.$term . ' OR phone:' . $term . ' OR email:' . $term . ')');
            //$GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_devlog', array('msg' => 'doctype:lucat AND (display_name:'.$term . ' OR phone:' . $term . ' OR email:' . $term . ')', 'crdate' => time()));
        } else {
            $groupComponent->addQuery('doctype:lucat AND (display_name:*' . str_replace(' ','\\ ',$term) . '* OR phone:*' . str_replace(' ','',$term) . '* OR email:"' . $term . '")');
        }
    }
    if($more != 'people' && $more != 'documents' && $more != 'courses') {
        $groupComponent->addQuery('doctype:page AND content:' . str_replace(' ','\\ ',$term));
    }
    if($more != 'pages' && $more != 'people' && $more != 'courses') {
        $groupComponent->addQuery('doctype:document AND attr_body:' . str_replace(' ','\\ ',$term));
    }
    if($more != 'pages' && $more != 'documents' && $more != 'people') {
        $groupComponent->addQuery('doctype:course AND (title_sv:' . str_replace(' ','\\ ',$term) . '* OR title_en:' . str_replace(' ','\\ ',$term) . '* OR course_code:' . strtolower(str_replace(' ','\\ ',$term.'*')).')');
    }
    
    if($pageOffset == '0' && $documentOffset == '0') $groupComponent->setSort('last_name_sort asc');
    $groupComponent->setLimit($tableLength);
    $groupComponent->setOffset(intval($peopleOffset) + intval($pageOffset) + intval($documentOffset) + intval($courseOffset));
    $resultset = $client->select($query);
    
    $groups = $resultset->getGrouping();

    foreach ($groups as $groupKey => $group) {
        $numRow[] = $group->getNumFound();
        foreach ($group as $document) {
            $id = $document->id;
            $doctype = $document->doctype;
            if($doctype === 'lucat') {
                /*$display_name = $document->display_name;
                $email = $document->email;
                $phone = fixArray($document->phone);
                $oname = fixArray($document->oname);
                $facetResult[] = fixArray($document->oname);
                $title = fixArray($document->title);
                $room_number = $document->room_number;
                if($room_number) $room_number = " (Rum " . fixArray($room_number) . ")";
                $people .= '<li>';

                $people .= "<p><b>$display_name</b>, $title, $oname$room_number<br />";
                if($email) $people .= "<a href=\"mailto:$email\">$email</a>, ";
                if($phone) $people .= "Telefon: $phone";
                $people .= "</p></li>";*/
                $peopleData[] = array(
                    ucwords(strtolower($document->first_name)),
                    ucwords(strtolower($document->last_name)),
                    $document->title,
                    $document->title_en,
                    $document->phone,
                    $document->id,
                    $document->email,
                    $document->oname,
                    $document->oname_en,
                    $document->primary_affiliation,
                    $document->homepage,
                    $document->image_id,
                    $document->lucrisphoto,
                    $document->room_number,
                    $document->mobile,
                    $document->uuid,
                    $document->orgid
                );
            } else if($doctype == 'page') {
                /*$content = $document->content;
                if (is_array($content)) {
                    $content = implode(' ', $content);
                }
                $title = $document->title;
                preg_match("/ltharticlebegin(.*)ltharticleend/s",$content, $results);
                $introText = substr($results[1], 0, 200);
                $url = $document->url;
                $documents .= '<li><h3><a href="' . $url . '">' . fixArray($title) . '</a></h3><p>' . $introText . '</p><p>' . $url . '</p></li>';
                 */
                $pageData[] = array(
                    $document->id,
                    $document->title,
                    $document->teaser,
                    $document->stream_name
                );
            } else if($doctype == 'document') {
                $documentData[] = array(
                    $document->id,
                    $document->title,
                    $document->teaser,
                    $document->stream_name
                );
            } else if($doctype == 'course') {
                $courseData[] = array(
                    $document->id,
                    $document->title_sv,
                    $document->title_en,
                    $document->course_code,
                    $document->credit,
                    $document->url
                );
            }
        }
    }

    if($more == 'people') {
        $peopleNumFound = $numRow[0];
    } else if($more == 'pages') {
        $pageNumFound = $numRow[0];
    } else if($more == 'documents') {
        $documentNumFound = $numRow[0];
    } else if($more == 'courses') {
        $courseNumFound = $numRow[0];
    } else {
        $peopleNumFound = $numRow[0];
        $pageNumFound = $numRow[1];
        $documentNumFound = $numRow[2];
        $courseNumFound = $numRow[3];
    }
    
    $facetResult = array_unique($facetResult);

    return json_encode(array('peopleData' => $peopleData, 'peopleNumFound' => $peopleNumFound, 'pageData' => $pageData, 'pageNumFound' => $pageNumFound, 'documentData' => $documentData, 'documentNumFound' => $documentNumFound, 'courseData' => $courseData, 'courseNumFound' => $courseNumFound, 'facet' => $facetResult));
}


function removeInvalidChars( $text) {
    $regex = '/( [\x00-\x7F] | [\xC0-\xDF][\x80-\xBF] | [\xE0-\xEF][\x80-\xBF]{2} | [\xF0-\xF7][\x80-\xBF]{3} ) | ./x';
    return preg_replace($regex, '$1', $text);
}


function searchMore($term, $type, $peopleOffset, $pageOffset, $documentOffset, $config)
{
    $client = new Solarium\Client($config);

    $query = $client->createSelect();

    if($type==='people') {
        $query->setQuery('display_name:*' . $term . '* OR phone:*' . $term . '* OR email:' . $term);
        $query->setStart($peopleOffset)->setRows(10);
    } else {
        $query->setQuery('content:*' . $term . '*');
        $query->setStart($documentOffset)->setRows(10);
    }
    
    $sortArray = array(
        'last_name_sort' => 'asc',
        'first_name_sort' => 'asc'
    );
    
    $query->addSorts($sortArray);

    $response = $client->select($query);
    if($type==='people') {
        $peopleNumFound = $response->getNumFound();
    } else {
        $documentsNumFound = $response->getNumFound();
    }
        
    foreach ($response as $document) {
        $id = $document->id;
        $doktype = $document->doctype;
        if($doktype === 'lucat') {
            $display_name = $document->display_name;
            $email = $document->email;
            $phone = fixArray($document->phone);
            $image = $document->image;
            $oname = fixArray($document->oname);
            $title = fixArray($document->title);
            $room_number = $document->room_number;
            if($room_number) $room_number = " (Rum $room_number)";
            $people .= '<li>';
            if($image) $people .= '<img class="align_left" src="' . $image . '" style="width:100px;height:100px;" />';
            $people .= "<h3>$display_name</h3>";
            $people .= "<p>$oname$room_number, $title</p>";
            $people .= "<p>";
            if($email) $people .= "<a href=\"mailto:$email\">$email</a><br />";
            if($phone) $people .= "Telefon: $phone<br />";
            if($homepage) $people .= $homepage;
            $people .= "</p>";
            $people .= "</li>";
        } else {
            $content = $document->content;
            if (is_array($content)) {
                $content = implode(' ', $content);
            }
            $title = $document->title;
            preg_match("/ltharticlebegin(.*)ltharticleend/s",$content, $results);
            $introText = substr($results[1], 0, 200);
            $url = $document->url;
            $documents .= '<li><h3><a href="' . $url . '">' . fixArray($title) . '</a></h3><p>' . $introText . '</p><p>' . $url . '</p></li>';
        }
    }
    return json_encode(array('people' => $people, 'peopleNumFound' => $peopleNumFound, 'documents' => $documents, 'documentsNumFound' => $documentsNumFound, 'facet' => $facet));
}


function listPublications($facet, $term, $syslang, $config, $table_length, $table_start, $pageid, $filterQuery, $selection)
{
    $currentDate = gmDate("Y-m-d\TH:i:s\Z");
    
    $client = new Solarium\Client($config);

    $query = $client->createSelect();
    
    $hideVal = 'lth_solr_hide_' . $pageid . '_i';
    
    if($filterQuery) {
        $filterQuery = str_replace(" ","\ ",$filterQuery);
        $filterQuery = " AND ((title:*$filterQuery*) OR authorName:*$filterQuery*)";
    }
    
    if($selection == 'coming_dissertations') {
        $selection = ' AND publicationType_en:Doctoral Thesis*';
    }
    // AND award:['.$currentDate . ' TO *]
//$GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_devlog', array('msg' => 'doctype:publication AND -' . $hideVal . ':[* TO *] AND (organisationSourceId  :'.$term.' OR authorId:'.$term.')' . $selection . $filterQuery, 'crdate' => time()));
    $query->setQuery('doctype:publication AND -' . $hideVal . ':[* TO *] AND (organisationSourceId  :'.$term.' OR authorId:'.$term.')' . $selection . $filterQuery);
    //$query->addParam('rows', 1500);
    $query->setStart($table_start)->setRows($table_length);
    
    $publicationType = "publicationType_$syslang";
    $categoryType = "standard_category_$syslang";
    $languageType = "language_$syslang";
    
    // get the facetset component
    $facetSet = $query->getFacetSet();
    
    // create a facet field instance and set options
    $facetSet->createFacetField('standard')->setField($categoryType);
    $facetSet->createFacetField('language')->setField($languageType);
    $facetSet->createFacetField('year')->setField('publicationDateYear');

    if($facet) {
        $facetArray = json_decode($facet, true);

        $facetQuery = '';
        foreach($facetArray as $key => $value) {
            $facetTempArray = explode('###', $value);
            if($facetQuery) {
                $facetQuery .= ' AND ';
            }
            $facetQuery .= $facetTempArray[0] . ':"' . $facetTempArray[1] . '"';
        }

        $query->addFilterQuery(array('key' => 0, 'query' => $facetQuery, 'tag'=>'inner'));
    }

    $sortArray = array(
        'lth_solr_sort_' . $pageid . '_i' => 'asc',
        'publicationDateYear' => 'desc'
    );
    $query->addSorts($sortArray);

    $response = $client->select($query);
    
    $numFound = $response->getNumFound();
    
    
    
    // display facet query count
    //if(!$facet) {
        $facet_standard = $response->getFacetSet()->getFacet('standard');
        if($syslang==="en") {
            $facetHeader = "Publikation Type";
        } else {
            $facetHeader = "Publikationstyp";
        }
        foreach ($facet_standard as $value => $count) {
            //if($count > 0) {
                $facetResult[$categoryType][] = array($value, $count, $facetHeader);
            //}
        }
        
        $facet_language = $response->getFacetSet()->getFacet('language');
        if($syslang==="en") {
            $facetHeader = "Language";
        } else {
            $facetHeader = "Språk";
        }
        foreach ($facet_language as $value => $count) {
            //if($count > 0) {
                $facetResult[$languageType][] = array($value, $count, $facetHeader);
            //}
        }
        
        $facet_year = $response->getFacetSet()->getFacet('year');
        if($syslang==="en") {
            $facetHeader = "Publikation Year";
        } else {
            $facetHeader = "Publikationsår";
        }
        foreach ($facet_year as $value => $count) {
            //if($count > 0) {
                $facetResult['publicationDateYear'][] = array($value, $count, $facetHeader);
            //}
        }
    //}
        
    foreach ($response as $document) {     
        $data[] = array(
            $document->id,
            fixArray($document->title),
            ucwords(strtolower(fixArray($document->authorName))),
            fixArray($document->$publicationType),
            $document->publicationDateYear,
            $document->publicationDateMonth,
            $document->publicationDateDay,
            $document->pages,
            $document->journalTitle,
            $document->journalNumber
        );
    }
    $resArray = array('data' => $data, 'numFound' => $numFound, 'facet' => $facetResult);
    return json_encode($resArray);
}


function showPublication($term, $syslang, $config, $detailPage)
{
    $client = new Solarium\Client($config);

    $query = $client->createSelect();

    $query->setQuery('id:'.$term);
    
    $response = $client->select($query);
    
    $content = '';
    
    $organisationNameHolder = 'organisationName_' . $syslang;
    $publicationTypeHolder = 'publicationType_' . $syslang;
    $languageHolder = 'language_' . $syslang;
    
    $detailPageArray = explode(',', $detailPage);
    $staffDetailPage = $detailPageArray[0];
    $projectDetailPage = $detailPageArray[1];
        
    foreach ($response as $document) {
        $id = $document->id;
        $title = fixArray($document->title);
        $authorNameArray = $document->authorName;
        $authorFirstNameArray = $document->authorFirstName;
        $authorLastNameArray = $document->authorLastName;
        $authorIdArray = $document->authorId;
        $i=0;
        foreach ($authorNameArray as $key => $authorName) {
            if($authorsName) $authorsName .= ', ';
            if($authorsId) $authorsId .= ', ';
            if($authorsReverseName) $authorsReverseName .= '; ';
            if($authorsReverseNameShort) $authorsReverseNameShort .= '; ';
            $authorsName .= mb_convert_case(strtolower($authorName), MB_CASE_TITLE, "UTF-8");
            $authorsReverseName .= mb_convert_case(strtolower($authorLastNameArray[$i]), MB_CASE_TITLE, "UTF-8") . ', ' . mb_convert_case(strtolower($authorFirstNameArray[$i]), MB_CASE_TITLE, "UTF-8");
            $authorsReverseNameShort .= mb_convert_case(strtolower($authorLastNameArray[$i]), MB_CASE_TITLE, "UTF-8") . ', ' . substr($authorFirstNameArray[$i], 0, 1) . '.';
            $authorsId .= $authorIdArray[$i];
            $i++;
        }

        $organisationNameArray = $document->$organisationNameHolder;
        $organisationIdArray = $document->organisationId;
        $i=0;
        foreach($organisationNameArray as $key => $organisationName) {
            if($organisations) $organisations .= ', ';
            $organisations .= '<a href="' . $organisationIdArray[$i] . '">' . $organisationName . '</a>';
            $i++;
        }

        if($document->externalOrganisationsName) {
            $externalOrganisationsNameArray = $document->externalOrganisationsName;
            $externalOrganisationsIdArray = $document->externalOrganisationsId;
            $i=0;
            foreach($externalOrganisationsNameArray as $key => $externalOrganisationsName) {
                if($externalOrganisations) $externalOrganisations .= ', ';
                $externalOrganisations .= '<a href="' . $externalOrganisationsIdArray[$i] . '">' . $externalOrganisationsName . '</a>';
                $i++;
            }
        }

        $publicationType = fixArray($document->$publicationTypeHolder);
        $publicationTypeUri = $document->publicationTypeUri;
        $language = fixArray($document->$languageHolder);
        $publicationDateYear = $document->publicationDateYear;
        $publicationDateMonth = $document->publicationDateMonth;
        $publicationDateDay = $document->publicationDateDay;
        $abstract_en = fixArray($document->abstract_en);
        $abstract_sv = fixArray($document->abstract_sv);
        if($syslang == 'sv' && $abstract_sv && $abstract_sv != '<br/>') {
            $abstract = $abstract_sv;
        } else {
            $abstract = $abstract_en;
        }
        $pages = $document->pages;
        $journalTitle = $document->journalTitle;
        $numberOfPages = $document->number_of_pages;
        $volume = $document->volume;
        $journalNumber = $document->journalNumber;
        if($syslang == 'sv') {
            $publicationStatus = $document->publicationStatus_sv;
            $keywords = $document->keywords_sv;
        } else {
            $publicationStatus = $document->publicationStatus_en;
            $keywords = $document->keywords_en;
        }
        $peerReview = $document->peerReview;
        $doi = $document->doi;
        $issn = $document->issn;
        $isbn = $document->isbn;
        $publisher = $document->publisher;
        
        $standard_category_en = $document->standard_category_en;
        
        $data = array(
            'id' => $id,
            'title' => $title,
            'abstract' => $abstract,
            'authorsName' => $authorsName,
            'authorsReverseName' => $authorsReverseName,
            'authorsReverseNameShort' => $authorsReverseNameShort,
            'authorsId' => $authorsId,
            'organisations' => $organisations,
            'externalOrganisations' => $externalOrganisations,
            'keywords' => $keywords,
            'language' => $language,
            'pages' => $pages,
            'numberOfPages' => $numberOfPages,
            'journalTitle' => $journalTitle,
            'volume' => $volume,
            'journalNumber' => $journalNumber,
            'publicationStatus' => $publicationStatus,
            'peerReview' => $peerReview,
            'publicationDateYear' => $publicationDateYear,
            'publicationDateMonth' => $publicationDateMonth,
            'publicationDateDay' => $publicationDateDay,
            'publicationType' => $publicationType,
            'publicationTypeUri' => $publicationTypeUri,
            'doi' => $doi,
            'issn' => $issn,
            'isbn' => $isbn,
            'standard_category_en' => $standard_category_en,
            'publisher' => $publisher
        );

        /*$content .= "<h3>$publicationType</h3>";

        if($abstract) {
            $content .= "<div><div class=\"textblock more-content\" style=\"height: 80px; overflow: hidden;\"></div>";

            $content .= "<a href=\"#\" onclick=\"showMore(this);return false;\" class=\"readmore\" data-height=\"144\">More</a></div>";
        }
        $content .= "<h2>Details</h2><table>";

        if($authors) $content .= "<tr><th>Authors</th><td></td></tr>";
        if($organisations) $content .= "<tr><th>Organisations</th><td></td></tr>";
        if($externalOrganisations) $content .= "<tr><th>External organisations</th><td></td></tr>";
        if($language) $content .= "<tr><th>Orginal language</th><td></td></tr>";
        if($pages) $content .= "<tr><th>Pages (from-to)</th><td></td></tr>";
        if($numberOfPages) $content .= "<tr><th>Number of pages</th><td></td></tr>";
        if($journal) $content .= "<tr><th>Journal</th><td></td></tr>";
        if($volume) $content .= "<tr><th>Volume</th><td></td></tr>";
        if($publicationStatus) $content .= "<tr><th>State</th><td></td></tr>";
        if($peerReview) $content .= "<tr><th>Peer-reviewed</th><td></td></tr>";*/
        //$content .= "<div><div></div><div>$publicationDateYear</td></tr>";
        //if($abstract) $content .= "<tr><th></th><td>$abstract_en</td></tr>";

    }
    
    $resArray = array('data' => $data, 'title' => $title);
    
    return json_encode($resArray);
}


function listStudentPapers($facet, $term, $syslang, $config, $table_length, $table_start, $pageid, $categories, $filterQuery, $papertype)
{
    $client = new Solarium\Client($config);

    $query = $client->createSelect();
        
    if($filterQuery) {
        $filterQuery = ' AND (title_sort:*' . $filterQuery . '*)';
    }
    
    if($papertype) {
        $paperTypeArray = explode(',', $papertype);
        foreach($paperTypeArray as $key => $value) {
            if($papertype) $papertype .= ' OR ';
            $papertype .= 'genre:studentPublications' . $value;
        }
        $papertype = ' AND (' . $papertype . ')';
    }

    $query->setQuery('doctype:studentPaper AND (organisationSourceId  :'.$term.')' . $papertype . $filterQuery);
    //$query->addParam('rows', 1500);
    $query->setStart($table_start)->setRows($table_length);
    
    // get the facetset component
    $facetSet = $query->getFacetSet();
    if($facet) {
        $facetArray = json_decode($facet, true);

        $facetQuery = '';
        foreach($facetArray as $key => $value) {
            $facetTempArray = explode('###', $value);
            if($facetQuery) {
                $facetQuery .= ' OR ';
            }
            $facetQuery .= $facetTempArray[0] . ':' . $facetTempArray[1] . '';
        }

        $query->addFilterQuery(array('key' => 0, 'query' => $facetQuery, 'tag'=>'inner'));
    } else if($categories) {
        $facetSet->createFacetField('standard')->setField('standard_category_' . $syslang);
    }

    $sortArray = array(
        'publicationDateYear' => 'desc'
    );
    $query->addSorts($sortArray);

    $response = $client->select($query);
    
    $numFound = $response->getNumFound();
    
    $categoryType = "standard_category_$syslang";
    $publicationType = "publicationType_$syslang";
    
    // display facet query count
    if(!$facet && $categories) {
        $facet_standard = $response->getFacetSet()->getFacet('standard');
        foreach ($facet_standard as $value => $count) {
            $facetResult[$categoryType][] = array($value, $count);
        }
    }
        
    foreach ($response as $document) {     
        $data[] = array(
            $document->id,
            fixArray($document->title),
            ucwords(strtolower(fixArray($document->authorName))),
            fixArray($document->$publicationType),
            $document->publicationDateYear
        );
    }
    $resArray = array('data' => $data, 'numFound' => $numFound, 'facet' => $facetResult);
    return json_encode($resArray);
}


function showStudentPaper($term, $syslang, $config, $detailPage)
{
    $client = new Solarium\Client($config);

    $query = $client->createSelect();

    $query->setQuery('id:'.$term);
    
    $response = $client->select($query);
    
    $content = '';
    
    $organisationNameHolder = 'organisationName_' . $syslang;
    $publicationTypeHolder = 'publicationType_' . $syslang;
    $languageHolder = 'language_' . $syslang;
    
    $detailPageArray = explode(',', $detailPage);
    $staffDetailPage = $detailPageArray[0];
    $projectDetailPage = $detailPageArray[1];
        
    foreach ($response as $document) {
        $id = $document->id;
        $title = fixArray($document->title);
        $authorNameArray = $document->authorName;
        $authorIdArray = $document->authorId;
        $i=0;
        foreach ($authorNameArray as $key => $authorName) {
            if($authors) $authors .= ', ';
            $authors .= '<a href="' . $staffDetailPage . '?no_cache=1&uuid=' . $authorIdArray[$i] . '">' . mb_convert_case(strtolower($authorName), MB_CASE_TITLE, "UTF-8") . '</a>';
            $i++;
        }

        $organisationNameArray = $document->$organisationNameHolder;
        $organisationIdArray = $document->organisationId;
        $i=0;
        foreach($organisationNameArray as $key => $organisationName) {
            if($organisations) $organisations .= ', ';
            $organisations .= '<a href="' . $organisationIdArray[$i] . '">' . $organisationName . '</a>';
            $i++;
        }

        if($document->externalOrganisationsName) {
            $externalOrganisationsNameArray = $document->externalOrganisationsName;
            $externalOrganisationsIdArray = $document->externalOrganisationsId;
            $i=0;
            foreach($externalOrganisationsNameArray as $key => $externalOrganisationsName) {
                if($externalOrganisations) $externalOrganisations .= ', ';
                $externalOrganisations .= '<a href="' . $externalOrganisationsIdArray[$i] . '">' . $externalOrganisationsName . '</a>';
                $i++;
            }
        }

        $publicationType = fixArray($document->$publicationTypeHolder);
        $language = fixArray($document->$languageHolder);
        $publicationDateYear = $document->publicationDateYear;
        $abstract_en = fixArray($document->abstract_en);
        $abstract_sv = fixArray($document->abstract_sv);
        if($syslang == 'sv' && $abstract_sv && $abstract_sv != '<br/>') {
            $abstract = $abstract_sv;
        } else {
            $abstract = $abstract_en;
        }
        $pages = $document->pages;
        $journalTitle = $document->journalTitle;
        $numberOfPages = $document->number_of_pages;
        $volume = $document->volume;
        $journalNumber = $document->journalNumber;
        if($syslang == 'sv') {
            $publicationStatus = $document->publicationStatus_sv;
            $keywords = $document->keywords_sv;
        } else {
            $publicationStatus = $document->publicationStatus_en;
            $keywords = $document->keywords_en;
        }
        $peerReview = $document->peerReview;
        
        $data = array(
            $abstract,
            $authors,
            $organisations,
            $externalOrganisations,
            $keywords,
            $language,
            $pages,
            $numberOfPages,
            $journalTitle,
            $volume,
            $journalNumber,
            $publicationStatus,
            $peerReview,
        );

        /*$content .= "<h3>$publicationType</h3>";

        if($abstract) {
            $content .= "<div><div class=\"textblock more-content\" style=\"height: 80px; overflow: hidden;\"></div>";

            $content .= "<a href=\"#\" onclick=\"showMore(this);return false;\" class=\"readmore\" data-height=\"144\">More</a></div>";
        }
        $content .= "<h2>Details</h2><table>";

        if($authors) $content .= "<tr><th>Authors</th><td></td></tr>";
        if($organisations) $content .= "<tr><th>Organisations</th><td></td></tr>";
        if($externalOrganisations) $content .= "<tr><th>External organisations</th><td></td></tr>";
        if($language) $content .= "<tr><th>Orginal language</th><td></td></tr>";
        if($pages) $content .= "<tr><th>Pages (from-to)</th><td></td></tr>";
        if($numberOfPages) $content .= "<tr><th>Number of pages</th><td></td></tr>";
        if($journal) $content .= "<tr><th>Journal</th><td></td></tr>";
        if($volume) $content .= "<tr><th>Volume</th><td></td></tr>";
        if($publicationStatus) $content .= "<tr><th>State</th><td></td></tr>";
        if($peerReview) $content .= "<tr><th>Peer-reviewed</th><td></td></tr>";*/
        //$content .= "<div><div></div><div>$publicationDateYear</td></tr>";
        //if($abstract) $content .= "<tr><th></th><td>$abstract_en</td></tr>";

    }
    
    $resArray = array('data' => $data, 'title' => $title);
    
    return json_encode($resArray);
}


function listProjects($term, $syslang, $config)
{
    $client = new Solarium\Client($config);

    $query = $client->createSelect();

    $query->setQuery('doctype:upmproject AND organisationId:'.$term);
    //$query->addParam('rows', 1500);
    $query->setStart($table_start)->setRows($table_length);
    
    $response = $client->select($query);
    
    $numFound = $response->getNumFound();
        
    foreach ($response as $document) {     
        $data[] = array(
            $document->id,
            fixArray($document->title_en),
            ucwords(strtolower(fixArray($document->participants))),
            substr($document->projectStartDate,0,10).'',
            substr($document->projectEndDate,0,10).'',
            ucwords(strtolower(str_replace('_',' ',$document->projectStatus)))
        );
    }
    $resArray = array('data' => $data, 'numFound'=> $numFound);
    return json_encode($resArray);
}


function showProject($term, $syslang, $config)
{
    $client = new Solarium\Client($config);

    $query = $client->createSelect();

    $query->setQuery('id:'.$term);
    
    $response = $client->select($query);
    
    $content = '';
        
    foreach ($response as $document) {     
        /*            'id' => $id,
        'portalUrl' => $portalUrl,
        'title' => $title,
        'abstract_en' => $abstract_en,
        'abstract_sv' => $abstract_sv,
        'authorId' => $authorId,
        'authorName' => array_unique($authorName),
        'organisationId' => $organisationId,
        'organisationName_en' => array_unique($organisationName_en),
        'organisationName_sv' => array_unique($organisationName_sv),
        'externalOrganisations' => $externalOrganisations,
        'keyword_en' => $keyword_en,
        'keyword_sv' => $keyword_sv,
        'userDefinedKeyword' => $userDefinedKeyword,
        'language_en' => $language_en,
        'language_sv' => $language_sv,
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
            */
            $id = $document->id;
            $title = fixArray($document->title_en);
            //$participants = ucwords(strtolower(fixArray($document->participants)));
            $participantsArray = $document->participants;
            $participantIdArray = $document->participantId;
            $i=0;
            foreach($participantsArray as $key => $participant) {
                if($participants) $participants .= ', ';
                $participants .= '<a href="testarea/solr/staff/detail/?uuid=' . $participantIdArray[$i] . '">' . $participant . '</a>';
                $i++;
            }
            
            $descriptions_en = fixArray($document->descriptions_en);
            $descriptions_sv = fixArray($document->descriptions_sv);
            if($syslang == 'sv' && $descriptions_sv) {
                $description = $descriptions_sv;
            } else {
                $description = $descriptions_en;
            }
            
            $content .= "<table>";
            $content .= "<tr><th>Description</th><td>$description</td></tr>";
            $content .= "<tr><th>Participants</th><td>$participants</td></tr>";
            $content .= "</table>";
    }
    $resArray = array('data' => $content, 'title' => $title);
    return json_encode($resArray);
}


function searchListShort($term, $config)
{
    $client = new Solarium\Client($config);

    $query = $client->createSelect();

    //$query->setQuery('body_txt:*' . $term .'* OR display_name_t:*' . $term . '* OR phone_txt:*' . $term . '*');
    $query->setQuery('content:*' . $term .'* OR title:*' . $term . '* OR display_name:*' . $term . '* OR phone:*' . $term . '* OR email:*' . $term . '*');
    
    $response = $client->select($query);
    
    $numfound = $response->getNumFound();
        
    foreach ($response as $document) {
        /*$id =$document->id;
        $label = $document->title_t;
        $value = $document->path_s;
        
        if($document->display_name_t) {
            $label = $document->display_name_t;
            $value = 'kontakt/' . $id;
        }        
        
        if($document->homepage_t) {
            $value = $document->homepage_t;
        }
                
        $data[] = array(
            'id' => $id,
            'label' => $label,
            'value' => $value 
        );*/

        // the documents are also iterable, to get all fields
        $id = $document->id;
        $label = $document->display_name . ' ' . fixArray($document->phone);// . ' ' . $document->mobile;// . ' ' . $document->email;
        if($document->homepage) {
            $value = urlencode($document->homepage);
        } else {
            $value = $document->id;
        }
                
        $data[] = array(
            'id' => $id,
            'label' => $label,
            'value' => $value 
        );

    }
    //{id: "Botaurus stellaris", label: "Great Bittern", value: "Great Bittern"}
    return json_encode($data);
}


function fixPhone($inputString)
{
    if($inputString) {
        $inputString = str_replace('+4646222', '+46 46 222 ', $inputString);
        $inputString = substr_replace($inputString, ' ' . substr($inputString, -2), -2);
    }
    return $inputString;
}


function fixArray($inputArray)
{
    if($doctype==='lucat') {
        return false;
    }
    if($inputArray) {
        if(is_array($inputArray)) {
            $inputArray = array_unique($inputArray);
            $inputArray = array_filter($inputArray);
            $inputArray = implode(', ', $inputArray);
        }
    }
    return $inputArray;
}


function rest()
{
    $requestUrl = 'http://portal.research.lu.se/ws/rest/person?email=maria.persson@nek.lu.se&rendering=xml_long';
    $desciption;
    $xmlDoc = new DomDocument;
    $xml = DOMDocument::load($requestUrl);

    if ($xml) {
        $xp = new DOMXPath($xml);
                    $xp->registerNamespace('core', 'http://atira.dk/schemas/pure4/model/core/stable');
                    $xp->registerNamespace('stab1',"http://atira.dk/schemas/pure4/model/template/abstractperson/stable");
        $items = $xp->query('//core:result/core:content/stab1:profileInformation/extensions-core:customField');
        if ($items->length) {
            foreach($items as $item) {
                $desciption .= $xp->evaluate('string(extensions-core:value)', $item);
            }
            //$item = $items->item(0);

            //tx_pure_cache::insertCachedData($name, $key, $this->cacheTime);
            return $desciption;
        }
    }
}


function listStaff($facet, $pageid, $pid, $sys_language_uid, $scope, $table_length, $table_start, $categories, $custom_categories, $config, $filterQuery)
{
    $content = '';
    $data = array();
    $facetResult = array();
        
    if($categories === 'standard_category') {
        //$catVal = 'standard_category_sv_txt';
        $catVal = 'standard_category_sv';
    } elseif($categories === 'custom_category') {
        //if(!$categoriesThisPage || $categoriesThisPage == '') {
            //$GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_devlog', array('msg' => 'global', 'crdate' => time()));
        //    $catVal = 'lth_solr_cat_ss';
        //} else {
            $catVal = 'lth_solr_cat_' . $pageid . '_ss';
        //} 
    }

    //if(!$introThisPage || $introThisPage == '') {
    //    $introVar = 'staff_custom_text_s';
    //} else {
        $introVar = 'staff_custom_text_' . $pageid . '_s';
    //}
        
    $showVal = 'lth_solr_show_' . $pageid . '_i';
    
    $hideVal = 'lth_solr_hide_' . $pageid . '_i';
    
    $autoVal = 'lth_solr_autohomepage_' . $pageid . '_s';

    $client = new Solarium\Client($config);

    $query = $client->createSelect();
    
    if($scope) {
        $scopeArray = explode(",", $scope);
        $scope = '';
        foreach($scopeArray as $key => $value) {
            if($scope) {
                $scope .= ' OR ';
            } else {
                $scope .= ' AND (orgid:';
            }
            $scope .= '"' . $value . '" OR heritage:"' . $value . '"';
        }
        $scope .= " OR $showVal:1)";
    } else {
        $scope = " OR $showVal:1";
    }
    
    if($filterQuery) {
        $filterQuery = ' AND (display_name:*' . $filterQuery . '* OR phone:*' . $filterQuery . '*)';
    }

    $queryToSet = '(doctype:"lucat"'.$scope. ' AND hide_on_web:0 AND disable_i:0 AND -' . $hideVal . ':[* TO *])' . $filterQuery;
    //$queryToSet = '(doctype:"lucat" AND '. $showVal .':1 AND hide_on_web:0 AND -' . $hideVal . ':[* TO *])' . $filterQuery;
    //$GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_devlog', array('msg' => $queryToSet, 'crdate' => time()));
    $query->setQuery($queryToSet);
    
    $query->setStart($table_start)->setRows($table_length);
    
    // get the facetset component
    $facetSet = $query->getFacetSet();
    if($facet) {
        $facetArray = json_decode($facet, true);
        //$GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_devlog', array('msg' => $facet, 'crdate' => time()));

        $facetQuery = '';
        foreach($facetArray as $key => $value) {
            $facetTempArray = explode('###', $value);
            if($facetQuery) {
                $facetQuery .= ' OR ';
            }
            $facetQuery .= $facetTempArray[0] . ':' . $facetTempArray[1] . '';
        }
        //$GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_devlog', array('msg' => $facetQuery, 'crdate' => time()));
        $query->addFilterQuery(array('key' => 0, 'query' => $facetQuery, 'tag'=>'inner'));
    } else if($categories === 'standard_category') {
        $facetSet->createFacetField('standard')->setField($catVal);
    } else if($categories === 'custom_category') {
        $facetSet->createFacetField('custom')->setField($catVal);
    } /*else {
        $facetSet->createFacetField('title')->setField('title_sort');
    }*/
        
    $sortArray = array(
        'lth_solr_sort_' . $pageid . '_i' => 'asc',
        'last_name_sort' => 'asc',
        'first_name_sort' => 'asc'
    );
    
    //$query->addSort('last_name_s', $query::SORT_ASC);
    //$query->addSort('first_name_s', $query::SORT_ASC);
    $query->addSorts($sortArray);
    
    //$query->addParam('rows', 15000);
    
    // this executes the query and returns the result
    $response = $client->select($query);

    // display the total number of documents found by solr
    $numFound = $response->getNumFound();

    // display facet query count
    if(!$facet) {
        if($categories === 'standard_category') {
            $facet_standard = $response->getFacetSet()->getFacet('standard');
            foreach ($facet_standard as $value => $count) {
                if($count > 0) $facetResult[$catVal][] = array($value, $count);
            }
        } else if($categories === 'custom_category') {
            $facet_custom = $response->getFacetSet()->getFacet('custom');
            foreach ($facet_custom as $value => $count) {
                if($count > 0) $facetResult[$catVal][] = array($value, $count);
            }
        } /*else {
            $facet_title = $response->getFacetSet()->getFacet('title');
            //$facet_ou = $response->getFacetSet()->getFacet('ou');

            foreach ($facet_title as $value => $count) {
                $facetResult['title_sort'][] = array($value, $count);
            }
        }*/
    }
    
    // show documents using the resultset iterator
    foreach ($response as $document) {
        $image = '';
        $intro_t = '';
        if($document->$introVar) {
            $intro_t = '<p class="lthsolr_intro">' . $document->$introVar . '</p>';
        }

        if($document->image) {
            $image = '/fileadmin' . $document->image;
        } else if($document->lucrisphoto) {
            $image = $document->lucrisphoto;
        } else {
            $image = '';
        }
        
        $data[] = array(           
            mb_convert_case(strtolower($document->first_name), MB_CASE_TITLE, "UTF-8"),
            mb_convert_case(strtolower($document->last_name), MB_CASE_TITLE, "UTF-8"),
            $document->title,
            $document->title_en,
            $document->phone,
            $document->id,
            $document->email,
            $document->oname,
            $document->oname_en,
            $document->primary_affiliation,
            $document->homepage,
            $image,
            $intro_t,
            fixRoomNumber($document->room_number),
            $document->mobile,
            $document->$autoVal,
            $document->orgid
        );
    }
    $resArray = array('data' => $data, 'numFound' => $numFound,'facet' => $facetResult, 'draw' => 1);
    return json_encode($resArray);
}


function fixRoomNumber($input)
{
    if(is_array($input)) {
        $input = array_unique($input);
    }
    return $input;
}


function fixString($input)
{
    if(!input || $input == '') {
        return '';
    } else {
        if(is_array($input)) {
            return implode(', ', $input);
        } else {
            return $input;
        }
    }
}


function showStaff($scope, $config, $table_length, $syslang)
{
    $content = '';
    $personData = array();
    $publicationData = array();
    $projectData = array();
    
    if(!$table_length) $table_length = 10;
    
    $publicationType = "publicationType_$syslang";

    $client = new Solarium\Client($config);
    $query = $client->createSelect();
    //$query->setStart($table_start)->setRows($table_length);
    $groupComponent = $query->getGrouping();

    $groupComponent->addQuery('uuid:' . $scope);
    $groupComponent->addQuery('authorId:' . $scope);
    $groupComponent->addQuery('participantId:' . $scope);
    $groupComponent->setSort('publicationDateYear desc');
    $groupComponent->setLimit($table_length);

    $resultset = $client->select($query);
    //var_dump($resultset);
    $groups = $resultset->getGrouping();
    foreach ($groups as $groupKey => $group) {
        //var_dump($group);
        $numRow[] = $group->getNumFound();

        foreach ($group as $document) {        
            $id = $document->id;
            $doktype = $document->doctype;
            
            $intro = '';
            if($document->$introVar) {
                $intro = '<p class="lthsolr_intro">' . $document->staff_custom_text_s . '</p>';
            }

            if($doktype === 'lucat') {
                if($document->image) {
                    $image = '/fileadmin' . $document->image;
                } else if($document->lucrisphoto) {
                    $image = $document->lucrisphoto;
                } else {
                    $image = '/typo3conf/ext/lth_solr/res/placeholder_noframe.gif';
                }
                
                $personData[] = array(
                    ucwords(strtolower($document->first_name)),
                    ucwords(strtolower($document->last_name)),
                    $document->title,
                    $document->title_en,
                    $document->phone,
                    $document->id,
                    $document->email,
                    $document->oname,
                    $document->oname_en,
                    $document->primary_affiliation,
                    $document->homepage,
                    $image,
                    $intro,
                    $document->room_number,
                    $document->mobile,
                    $document->uuid,
                    $document->orgid,
                    $document->ophone,
                    $document->ostreet,
                    $document->ocity,
                    $document->opostal_address,
                    $document->profileInformation_sv
                );
            } else if($doktype === 'publication') {
                $publicationData[] = array(
                    $document->id,
                    fixArray($document->title),
                    ucwords(strtolower(fixArray($document->authorName))),
                    fixArray($document->$publicationType),
                    $document->publicationDateYear,
                    $document->publicationDateMonth,
                    $document->publicationDateDay,
                    $document->pages,
                    $document->journalTitle,
                    $document->journalNumber
                );
                
            } else if($doktype === 'upmproject') {
                $projectData[] = array(
                    $document->id,
                    fixArray($document->title_en),
                    ucwords(strtolower(fixArray($document->participants))),
                    substr($document->projectStartDate,0,10).'',
                    substr($document->projectEndDate,0,10).'',
                    ucwords(strtolower(str_replace('_',' ',$document->projectStatus)))
                );
                
            }
        }
    }
    
    $resArray = array('personData' => $personData, 'publicationData' => $publicationData, 'publicationNumFound' => $numRow[1], 'projectData' => $projectData, 'projectNumFound' => $numRow[2]);
    
    return json_encode($resArray);
}

function getLucris()
{
    $client = new SoapClient("http://portal.research.lu.se/ws/pure4webservice/pure4.wsdl");
    //http://pure.leuphana.de/ws/Pure4WebService/pure4.wsdl
    $temp = $client->GetPersonRequest(       
            //array("typeClassificationUris" => array("uri" => "/dk/atira/pure/activity/activitytypes/appearance/%")
        //)     
    );
    return $temp;
        
}


function basicSelect($q, $config)
{
    
    $resArray = array();

        // create a client instance
    $client = new Solarium\Client($config);

    // get a select query instance
    $query = $client->createSelect();

    // set a query (all prices starting from 12)
    $query->setQuery('last_name_t:'.$q.'*');

    // set start and rows param (comparable to SQL limit) using fluent interface
    //$query->setStart(2)->setRows(20);

    // set fields to fetch (this overrides the default setting 'all fields')
    $query->setFields(array('display_name_t', 'email_t'));

    // sort the results by price ascending
    $query->addSort('last_name_t', $query::SORT_ASC);

    // this executes the query and returns the result
    $resultset = $client->select($query);
    $i = 0;
    // show documents using the resultset iterator
    foreach ($resultset as $document) {
        $content = '';
        // the documents are also iterable, to get all fields
        /*foreach ($document as $field => $value) {
            // this converts multivalue fields to a comma-separated string
            if (is_array($value)) {
                $value = implode(', ', $value);
            }

            $content .= $value . ' ';
        }*/
        $friends[$i] = $document->display_name_t.', ' . $document->email_t;
        $i++;
    }
    return $_GET["callback"] . "(" . json_encode($friends) . ")";
}