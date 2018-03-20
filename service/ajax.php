<?php
// Exit, if script is called directly (must be included via eID in index_ts.php)
if (!defined ('PATH_typo3conf')) die ('Could not access this script directly!');

class lthSolrAjax
{
    
function myInit()
{
require(__DIR__.'/init.php');

$term = '';
$content = '';
$query = '';
$action = '';
$sid = '';

$term = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP("term");
$peopleOffset = htmlspecialchars(\TYPO3\CMS\Core\Utility\GeneralUtility::_GP("peopleOffset"));
$pageOffset = htmlspecialchars(\TYPO3\CMS\Core\Utility\GeneralUtility::_GP("pageOffset"));
$courseOffset = htmlspecialchars(\TYPO3\CMS\Core\Utility\GeneralUtility::_GP("courseOffset"));
$more = htmlspecialchars(\TYPO3\CMS\Core\Utility\GeneralUtility::_GP("more"));
$query = htmlspecialchars(\TYPO3\CMS\Core\Utility\GeneralUtility::_GP("query"));
if($query) $query = trim($query);
$action = htmlspecialchars(\TYPO3\CMS\Core\Utility\GeneralUtility::_GP("action"));
$scope = htmlspecialchars(\TYPO3\CMS\Core\Utility\GeneralUtility::_GP("scope"));
$facet = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP("facet");
$pid = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('pid');
$syslang = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('syslang');
$tableStart = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('tableStart');
$tableLength = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('tableLength');
$tableFields = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('tableFields');
$pageid = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('pageid');
$custom_categories = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('custom_categories');
$categories = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('categories');
$publicationCategories = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('publicationCategories');
//$introThisPage = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('introThisPage');
$addPeople = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('addPeople');
$keyword = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('keyword');
$papertype = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('papertype');
$limitToStandardCategories = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('limitToStandardCategories');
$webSearchScope = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('webSearchScope');
$sorting = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('sorting');
        
$sid = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP("sid");
date_default_timezone_set('Europe/Stockholm');

$settings = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['lth_solr']);
if(!$syslang) $syslang = "sv";
        
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


if (!$settings['solrHost'] || !$settings['solrPort'] || !$settings['solrPath'] || !$settings['solrTimeout']) {
    return 'Please make all settings in extension manager';
}

//tslib_eidtools::connectDB();
//  $GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_devlog', array('msg' => $term, 'crdate' => time()));
//$GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_devlog', array('msg' => $sys_language_uid, 'crdate' => time()));

switch($action) {
    case 'searchListShort':
        $content = $this->searchListShort($term, $config);
        break;
    case 'searchShort':
        $content = $this->searchShort($query, $config);
        break;
    case 'searchLong':
        $content = $this->searchLong($term, $query, $tableLength, $peopleOffset, $pageOffset, $courseOffset, $webSearchScope, $more, $config);
        break;
    case 'searchMorePeople':
        $content = $this->searchMore($term, 'people', $peopleOffset, $pageOffset, $documentOffset, $config);
        break;
    case 'searchMorePages':
        $content = $this->searchMore($term, 'pages', $peopleOffset, $pageOffset, $documentOffset, $config);
        break;    
    case 'searchMoreDocuments':
        $content = $this->searchMore($term, 'documents', $peopleOffset, $pageOffset, $documentOffset, $config);
        break;
    case 'listPublications':
    case 'exportPublications':
        $content = $this->listPublications($facet, $scope, $syslang, $config, $tableLength, $tableStart, $pageid, $query, $keyword, $sorting, $tableFields, $action, $publicationCategories);
        break;
    case 'listStudentPapers':
    case 'exportStudentPapers':
        $content = $this->listStudentpapers($facet, $scope, $syslang, $config, $tableLength, $tableStart, $pageid, $categories, $query, $papertype, $tableFields, $action, $publicationCategories);
        break;
    case 'showPublication':
        $content = $this->showPublication('', $term, $syslang, $config);
        break;
    case 'showStudentPaper':
        $content = $this->showStudentPaper($term, $syslang, $config);
        break;
    case 'listProjects':
        $content = $this->listProjects($scope, $syslang, $config, $tableLength, $tableStart, $query);
        break;
    case 'showProject':
        $content = $this->showProject($scope, $syslang, $config);
        break;
    case 'listStaff':
    case 'exportStaff':
        $content = $this->listStaff($facet, $pageid, $pid, $syslang, $scope, $tableLength, $tableStart, $categories, 
                $custom_categories, $config, $query, $tableFields, $action, $limitToStandardCategories);
        break;
    case 'showStaff':
        $content = $this->showStaff($scope, $config, $syslang);
        break;
    case 'rest':
        $content = $this->rest();
        break;
    case 'listTagCloud':
        $content = $this->listTagCloud($scope, $syslang, $config, $pageid, $term);
        break;
    /*default:
        $content = $this->basicSelect($query, $config);
        break;*/
}

print $content;

}

function exportPublications()
{
    
}


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
    
    if($term) {
        $term = str_replace(':','', $term);
        $term = str_replace(';','', $term);
    }

    $groupComponent = $query->getGrouping();
    if(substr($term, 0,1) == '"' && substr($term,-1) == '"') {
        $queryToSet = 'docType:staff AND primaryAffiliation:employee AND (name:' . str_replace(' ','\\ ',$term) . ' OR phone:' . str_replace(' ','',$term) . ' OR email:' . str_replace('.','\.',$term) . ')';
        $groupComponent->addQuery($queryToSet);
    } else {
        $queryToSet = 'docType:staff AND primaryAffiliation:employee AND (name:*' . str_replace(' ','\\ ',$term) . '* OR phone:*' . str_replace(' ','',$term) . '* OR email:*' . $term . '*)';
        $groupComponent->addQuery($queryToSet);
    }
    //$groupComponent->addQuery('type:pages AND content:*' . str_replace(' ','\\ ',$term) . '*');
    //$groupComponent->addQuery('docType:document AND content:*' . str_replace(' ','\\ ',$term) . '*');
    $groupComponent->addQuery('docType:course AND (title:*' . str_replace(' ','\\ ',$term) . '* OR courseCode:*' . str_replace(' ','',$term) . '*)');
    $groupComponent->addQuery('docType:program AND title:*' . str_replace(' ','\\ ',$term) . '*');
    $groupComponent->setSort('lastNameExact asc');
    $groupComponent->setLimit(5);    
    $resultset = $client->select($query);
    $groups = $resultset->getGrouping();
    //LTH: http://connector.search.lu.se/solr/sr/www.lth.se/sid-07856cbc0c3c046c4f20/havn/customsites/1/undefined?1505980697453-sid-07856cbc0c3c046c4f20--1261231745
    //LU:  http://connector.search.lu.se/solr/sr/www.lth.se/sid-07856cbc0c3c046c4f20/havn/all/1/undefined?1505980697453-sid-07856cbc0c3c046c4f20--1261231745
    //$luRes = @file_get_contents("http://connector.search.lu.se/solr/sr/www.lth.se/sid-07856cbc0c3c046c4f20/$term/all/1/undefined?1505829015363");
    //$lthRes = @file_get_contents("http://connector.search.lu.se/solr/sr/www.lth.se/sid-07856cbc0c3c046c4f20/$term/customsites/1/undefined?1505829015363");
    $luRes = @file_get_contents("http://connector.search.lu.se/solr/sr/www.lth.se/sid-07856cbc0c3c046c4f20/$term/all/1/undefined?1505829015363");
    $lthRes = @file_get_contents("http://connector.search.lu.se/solr/sr/www.lth.se/sid-07856cbc0c3c046c4f20/$term/customsites/1/undefined?1505829015363");
    
    /*$luResArray = explode('<div class="hit-wrapper">', $luRes);
    $lthResArray = explode('<div class="hit-wrapper">', $lthRes);
    $luRes = $luResArray[2];
    $lthRes = $lthResArray[1];
    $luResArray = explode('<div class="pager-wrapper item-list">', $luRes);
    $lthResArray = explode('<div class="pager-wrapper item-list">', $lthRes);
    $luRes = array_shift($luResArray);
    $lthRes = array_shift($lthResArray);
    //$luRes = implode('<div class="hit-wrapper">', $luResArray);
     * 
     */
    if($luRes) {
        $luRes = json_decode($luRes, true);
    }
    if($lthRes) {
        $lthRes = json_decode($lthRes, true);
    }
    
    foreach ($groups as $groupKey => $group) {
        foreach ($group as $document) {        
            
            $docType = $document->docType;
            
            if($docType === 'staff') {
                $email   = $document->email;
                $value = $document->id;
                $label = $this->fixArray($document->name);
                if($document->phone) {
                    if($document->phone[0] !=='NULL') {
                        $label .= ', ' . $this->fixPhone($document->phone[0]);
                    }
                }
                //if($email) $label .= ', ' . $this->fixArray($email);
                $data[] = array(
                    'id' => $email,
                    'label' => $label,
                    'type' => 'staff',
                    'value' => $label
                );
            } /*else if($docType === 'course') {
                $id = $document->id;
                $value = $document->homepage;
                $label = $document->courseCode . ', ' . $this->fixArray($document->title);
                $data[] = array(
                    'id' => $id,
                    'label' => $label,
                    'type' => 'course',
                    'value' => $value
                );
            } else if($docType === 'program') {
                $id = $document->id;
                $value = $document->id;
                $label = $this->fixArray($document->title);
                $data[] = array(
                    'id' => $id,
                    'label' => $label,
                    'type' => 'program',
                    'value' => $value
                );
            } else if($docType === 'document') {
                $id = $document->id;
                $value = $document->id;
                $label = $this->fixArray($document->title);
                $data[] = array(
                    'id' => $id,
                    'label' => $label,
                    'type' => 'document',
                    'value' => $value
                );
            } else {
                $id = $document->id;
                $value = $document->id;
                $label = $this->fixArray($document->title);
                $data[] = array(
                    'id' => $id,
                    'label' => $label,
                    'value' => $value
                );
            }*/
        }
    }
    $data[] = array(
                    'id' => 'lu',
                    'label' => 'lu',
                    'value' => $luRes,
                    'type' => 'web page'
                );
    $data[] = array(
                    'id' => 'lth',
                    'label' => 'lth',
                    'value' => $lthRes,
                    'type' => 'web page'
                );
    return json_encode($data);
}


function file_get_contents_utf8($fn) {
     $content = file_get_contents($fn);
      return mb_convert_encoding($content, 'UTF-8',
          mb_detect_encoding($content, 'UTF-8, ISO-8859-1', true));
}


function searchLong($term, $inputQuery, $tableLength, $peopleOffset, $pageOffset, $courseOffset, $webSearchScope, $more, $config)
{
    $fieldArray = array("docType","firstName","lastName","title","phone","email","organisationId","organisationName","primary_affiliation","homepage","image",
        "imageId","lucrisPhoto","roomNumber","mobile","guid","uuid","orgid","id","courseCode","credit");
    $people;
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
    $courseData = array();
    
    $client = new Solarium\Client($config);
    $query = $client->createSelect();
    $query->setStart($tableStart)->setRows($tableLength);
    $query->setFields($fieldArray);
    
    if($term) {
        //$GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_devlog', array('msg' => $term, 'crdate' => time()));
        $term = array_pop(explode(',', htmlspecialchars_decode($term)));
    } else {
        $term = $inputQuery;
    }
    $term = trim($term);

    if(substr($term, 0,1) == '"' && substr($term,-1) != '"') {
        $term = ltrim($term,'"');
    }
    
    if($more != 'local' && $more != 'global') {
        $groupComponent = $query->getGrouping();
    }
    
    if($term) {
        $term = str_replace(':','', $term);
        $term = str_replace(';','', $term);
    }
    
    if($more != 'local' && $more != 'global' && $more != 'courses') {  
        if(substr($term, 0,1) == '"' && substr($term,-1) == '"') {
            $queryToSet = 'docType:staff AND primaryAffiliation:employee AND (name:*'.$term . '* OR phone:*' . $term . '* OR email:*' . $term . '*)';
            $groupComponent->addQuery($queryToSet);
            //$GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_devlog', array('msg' => 'doctype:lucat AND (display_name:'.$term . ' OR phone:' . $term . ' OR email:' . $term . ')', 'crdate' => time()));
        } else {
            $queryToSet = 'docType:staff AND primaryAffiliation:employee AND (name:*' . str_replace(' ','\\ ',$term) . '* OR phone:*' . str_replace(' ','',$term) . '* OR email:"' . $term . '")';
            $groupComponent->addQuery($queryToSet);
            //$GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_devlog', array('msg' => 'docType:staff AND primaryAffiliation:employee AND (name:*' . str_replace(' ','\\ ',$term) . '* OR phone:*' . str_replace(' ','',$term) . '* OR email:"' . $term . '")', 'crdate' => time()));
        }
    }
    /*if($more != 'people' && $more != 'documents' && $more != 'courses') {
        $term = str_replace(' ','\\ ',$term);
        $groupComponent->addQuery('type:pages AND ((title:' . $term . ' OR title:"' . $term . '"^10' . ') OR content:' . $term . ')');      
    }*/
    /*if($more != 'pages' && $more != 'people' && $more != 'courses') {
        $groupComponent->addQuery('docType:document AND attr_body:' . str_replace(' ','\\ ',$term));
    }*/
    if($more != 'local' && $more != 'global' && $more != 'people') {
        $groupComponent->addQuery('docType:course AND (title:' . str_replace(' ','\\ ',$term) . '* OR courseCode:' . strtolower(str_replace(' ','\\ ',$term.'*')).')');
    }
    
    if($more != 'local' && $more != 'global') {
        $groupComponent->setSort('lastNameExact asc');

        $groupComponent->setLimit($tableLength);
        $groupComponent->setOffset(intval($peopleOffset) + intval($pageOffset) + intval($courseOffset));
        $resultset = $client->select($query);
    }
    
    
    if($pageOffset==0) {
        $pageOffset = 1;
    } else {
        $pageOffset = 1 + $pageOffset/20;
    }
    
    if(!$webSearchScope) {
        $webSearchScope='global';
    }
    
    if(($webSearchScope==='global' || $more==='global') && $more != 'people' && $more != 'courses') {
        $connectString = "http://connector.search.lu.se/solr/sr/www.lth.se/sid-07856cbc0c3c046c4f20/$term/all/$pageOffset?1505829015363";
        $pageRes = file_get_contents($connectString);
        //$GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_devlog', array('msg' => "http://connector.search.lu.se/solr/sr/www.lth.se/sid-07856cbc0c3c046c4f20/$term/all/$pageOffset?1505829015363", 'crdate' => time()));
        /*preg_match_all('/<span class="numhits">(.*?)<\/span>/s', $pageRes, $matches);
        $pageNumFound = trim($matches[1][1]);
        $pageResArray = explode('<div class="hit-wrapper">', $pageRes);
        $pageRes = $pageResArray[2];*/
        //$GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_devlog', array('msg' => $pageResArray[2], 'crdate' => time()));
    } else if(($webSearchScope==='local' || $more==='local') && $more != 'people' && $more != 'courses') {
        $connectString = "http://connector.search.lu.se/solr/sr/www.lth.se/sid-07856cbc0c3c046c4f20/$term/customsites/$pageOffset?1505829015363";
        $pageRes = file_get_contents($connectString);
        /*preg_match_all('/<span class="numhits">(.*?)<\/span>/s', $pageRes, $matches);
        $pageNumFound = trim($matches[1][0]);
        $pageResArray = explode('<div class="hit-wrapper">', $pageRes);
        $pageRes = $pageResArray[1];*/
        //$GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_devlog', array('msg' => $pageResArray[1], 'crdate' => time()));
    }
    if($pageRes) {
        $pageRes = json_decode($pageRes, true);
    }
    //$pageResArray = explode('<div class="pager-wrapper item-list">', $pageRes);
    //$pageRes = array_shift($pageResArray);
    //$GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_devlog', array('msg' => $connectString, 'crdate' => time()));
    
    if($more != 'local' && $more != 'global') {
    $groups = $resultset->getGrouping();

    foreach ($groups as $groupKey => $group) {
        $numRow[] = $group->getNumFound();
        foreach ($group as $document) {
            
            $id = $document->id;
            $docType = $document->docType;
            $type = $document->type;
            if($docType === 'staff') {
                
            if($document->image) {
                $image = '/fileadmin' . $document->image;
            } else if($document->lucrisPhoto) {
                $image = $document->lucrisPhoto;
            } else {
                $image = '';
            }
        
                $peopleData[] = array(
                    "firstName" => ucwords(strtolower($document->firstName)),
                    "lastName" => ucwords(strtolower($document->lastName)),
                    "title" => $document->title,
                    "phone" => $document->phone,
                    "email" => $document->email,
                    "organisationId" => $document->organisationId,
                    "organisationName" => $document->organisationName,
                    "primary_affiliation" => $document->primary_affiliation,
                    "homepage" => $document->homepage,
                    "imageId" => $document->imageId,
                    "image" => $image,
                    "lucrisPhoto" => $document->lucrisPhoto,
                    "roomNumber" => $document->roomNumber,
                    "mobile" => $document->mobile,
                    "guid" => $document->guid,
                    "uuid" => $document->uuid,
                    "orgid" => $document->orgid
                );
            } /*else if($type == 'pages') {
                $pageData[] = array(
                    $document->id,
                    $document->title,
                    $document->teaser,
                    $document->stream_name
                );
            } else if($docType == 'document') {
                $documentData[] = array(
                    $document->id,
                    $document->title,
                    $document->teaser,
                    $document->stream_name
                );
            } */ else if($docType == 'course') {
                $courseData[] = array(
                    "id" => $document->id,
                    "title" => $document->title,
                    "courseCode" => $document->courseCode,
                    "credit" => $document->credit,
                    "homepage" => $document->homepage
                );
            }
        }
    }

    if($more == 'people') {
        $peopleNumFound = $numRow[0];
    } /*else if($more == 'pages') {
        $pageNumFound = $numRow[0];
    } else if($more == 'documents') {
        $documentNumFound = $numRow[0];
    } */else if($more == 'courses') {
        $courseNumFound = $numRow[0];
    } else {
        $peopleNumFound = $numRow[0];
        //$pageNumFound = $numRow[1];
        //$documentNumFound = $numRow[2];
        $courseNumFound = $numRow[1];
    }
    }
    $facetResult = array_unique($facetResult);

    return json_encode(array('peopleData' => $peopleData, 'peopleNumFound' => $peopleNumFound, 'pageData' => $pageRes, 
        'pageNumFound' => $pageNumFound, 'courseData' => $courseData, 
        'courseNumFound' => $courseNumFound, 'facet' => $facetResult, 'debug' => $queryToSet));
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
            $phone = $this->fixArray($document->phone);
            $image = $document->image;
            $oname = $this->fixArray($document->oname);
            $title = $this->fixArray($document->title);
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
            $documents .= '<li><h3><a href="' . $url . '">' . $this->fixArray($title) . '</a></h3><p>' . $introText . '</p><p>' . $url . '</p></li>';
        }
    }
    return json_encode(array('people' => $people, 'peopleNumFound' => $peopleNumFound, 'documents' => $documents, 'documentsNumFound' => $documentsNumFound, 'facet' => $facet));
}


function listPublications($facet, $scope, $syslang, $config, $tableLength, $tableStart, $pageid, $filterQuery, $keyword, $sorting, $tableFields, $action, $publicationCategories)
{
    if($action==='exportPublications') {
        $fieldArray = json_decode($tableFields, true);
    } else {
        $fieldArray = array("articleNumber","authorName","documentTitle","attachmentLimitedVisibility","attachmentMimeType","attachmentSize",
                "attachmentUrl","hostPublicationTitle","id","journalTitle","journalNumber","numberOfPages","pages","publicationType",
                "publicationDateYear","publicationDateMonth","publicationDateDay","placeOfPublication","publisher","volume");
    }
    
    $currentDate = gmDate("Y-m-d\TH:i:s\Z");
    
    $client = new Solarium\Client($config);

    $query = $client->createSelect();
    
    $hideVal = 'lth_solr_hide_' . $pageid . '_i';
    
    if($filterQuery) {
        $filterQuery = str_replace(" ","\ ",$filterQuery);
        $filterQuery = " AND ((documentTitle:*$filterQuery*) OR authorName:*$filterQuery*)";
    }
    
    $publicationSelection = '';
    if($publicationCategories) {
        if($publicationCategories === 'free' || $publicationCategories === 'campus') {
            $publicationSelection = 'attachmentLimitedVisibility:' . $publicationCategories;
        } else {
            $publicationCategories = explode(',', $publicationCategories);
            foreach($publicationCategories as $pcKey => $pcValue) {
                if($publicationSelection) {
                    $publicationSelection .= " OR ";
                }
                $publicationSelection .= 'standardCategory:"' . urldecode($pcValue) .'"';
            }
        }
        $publicationSelection = " AND ($publicationSelection)";
    }
    
    if($keyword) {
        $keyword = ' AND (keywordsUser:' . str_replace(' ', '\\ ', urldecode($keyword)) . ' OR keywordsUka:' . str_replace(' ', '\\ ', urldecode($keyword)) . ')';
    }
    
    if($scope) {
        //$debugQuery = urldecode($scope);
        $scope = json_decode(urldecode($scope),true);
        //var_dump($scope);
        foreach($scope as $key => $value) {
            if($term) {
                $term .= " OR ";
            }
            if($key === "fe_groups") {
                $term .= "organisationSourceId:" . implode(' OR organisationSourceId:', $value);
            } else {
                $term .= "authorId:" . implode(' OR authorId:', $value);
            }
        }
    }

    $queryToSet = 'docType:publication AND -' . $hideVal . ':1 AND publicationDateYear:[* TO ' . date('Y', strtotime('+1 years')) . '] AND (' . $term . ')' . $keyword . $publicationSelection . $filterQuery;
    $query->setQuery($queryToSet);
    $query->setFields($fieldArray);
    $query->setStart($tableStart)->setRows($tableLength);
    
    // get the facetset component
    $facetSet = $query->getFacetSet();
    
    // create a facet field instance and set options
    $facetSet->createFacetField('standard')->setField('standardCategory');
    $facetSet->createFacetField('language')->setField('language');
    $facetSet->createFacetField('year')->setField('publicationDateYear');
    $facetSet->createFacetField('visibility')->setField('attachmentLimitedVisibility');

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

    if($sorting) {
        switch($sorting) {
            case 'publicationType':
                $sortArray = array(
                    'publicationType' => 'asc',
                    'publicationDateYear' => 'desc',
                    'publicationDateMonth' => 'desc',
                    'publicationDateDay' => 'desc',
                    'documentTitle' => 'asc'
                );
                break;
            case 'publicationYear':
                $sortArray = array(
                    'publicationDateYear' => 'desc',
                    'publicationDateMonth' => 'desc',
                    'publicationDateDay' => 'desc',
                    'documentTitle' => 'asc'
                );
                break;
            case 'documentTitle':
                $sortArray = array(
                    'documentTitle' => 'asc',
                    'publicationDateYear' => 'desc',
                    'publicationDateMonth' => 'desc',
                    'publicationDateDay' => 'desc',
                );
                break;
            case 'authorName':
                $sortArray = array(
                    'authorLastNameExact' => 'asc',
                    'authorFirstNameExact' => 'asc',
                    'publicationDateYear' => 'desc',
                    'publicationDateMonth' => 'desc',
                    'publicationDateDay' => 'desc',
                    'documentTitle' => 'asc',
                );
                break;
        }
    } else {
        $sortArray = array(
            'lth_solr_sort_' . $pageid . '_i' => 'asc',
            'publicationDateYear' => 'desc',
            'publicationDateMonth' => 'desc',
            'publicationDateDay' => 'desc',
            'documentTitle' => 'asc',
            'lastNameExact' => 'asc',
            'firstNameExact' => 'asc'
        );
    }

    $query->addSorts($sortArray);

    $response = $client->select($query);
    
    $numFound = $response->getNumFound();
    
    // display facet query count
    $facetStandard = $response->getFacetSet()->getFacet('standard');
    if($syslang==="en") {
        $facetHeader = "Publication Type";
    } else {
        $facetHeader = "Publikationstyp";
    }
    foreach ($facetStandard as $value => $count) {
        if($count > 0) $facetResult["standardCategory"][] = array($value, $count, $facetHeader);
    }

    $facetLanguage = $response->getFacetSet()->getFacet('language');
    if($syslang==="en") {
        $facetHeader = "Language";
    } else {
        $facetHeader = "Språk";
    }
    foreach ($facetLanguage as $value => $count) {
        if($count > 0) $facetResult["language"][] = array($value, $count, $facetHeader);
    }

    $facetYear = $response->getFacetSet()->getFacet('year');
    if($syslang==="en") {
        $facetHeader = "Publication Year";
    } else {
        $facetHeader = "Publikationsår";
    }
    foreach ($facetYear as $value => $count) {
        if($count > 0) $facetResult['publicationDateYear'][] = array($value, $count, $facetHeader);
    }
    if($facetResult['publicationDateYear']) usort($facetResult['publicationDateYear'],array($this,'compareOrder'));
    
    $facetVisibility = $response->getFacetSet()->getFacet('visibility');
    if($syslang==="en") {
        $facetHeader = "Full text";
    } else {
        $facetHeader = "Fulltext";
    }
    foreach ($facetVisibility as $value => $count) {
        if($count > 0) $facetResult['attachmentLimitedVisibility'][] = array($value, $count, $facetHeader);
    }
        
    foreach ($response as $document) {
        if($action==='exportPublications') {
            foreach($fieldArray as $field) {
                $data[$i][$field] = $document->$field;
            }
            $i++;
        } else {
            $data[] = array(
                "articleNumber" => $document->$articleNumber,
                "authorName" => ucwords(strtolower($this->fixArray($document->authorName))),
                "documentTitle" => $document->documentTitle,
                "attachmentLimitedVisibility" => $document->attachmentLimitedVisibility,
                "attachmentMimeType" => $document->attachmentMimeType,
                "attachmentSize" => $document->attachmentSize,
                "attachmentUrl" => $document->attachmentUrl,
                "hostPublicationTitle" => $document->hostPublicationTitle,
                "id" => $document->id,
                "journalTitle" => $document->journalTitle,
                "journalNumber" => $document->journalNumber,
                "numberOfPages" => $document->numberOfPages,
                "pages" => $document->pages,
                "publicationType" => $this->fixArray($document->publicationType),
                "publicationDateYear" => $document->publicationDateYear,
                "publicationDateMonth" => $document->publicationDateMonth,
                "publicationDateDay" => $document->publicationDateDay,
                "placeOfPublication" => $document->placeOfPublication,
                "publisher" => $document->publisher,
                "volume" => $document->volume,
            );
        }
    }
    $resArray = array('data' => $data, 'numFound' => $numFound, 'facet' => $facetResult, 'query' => $queryToSet);
    return json_encode($resArray);
}


function showPublication($response, $term, $syslang, $config)
{
    $fieldArray = array('abstract','attachmentLimitedVisibility','attachmentMimeType','attachmentSize','attachmentTitle','attachmentUrl','authorExternal','authorId',
        'authorName','authorOrganisation','authorReverseName','authorReverseNameShort','bibtex','cite','documentTitle','doi','edition','electronicIsbns','externalOrganisations',
        'id','hostPublicationTitle','issn','journalTitle','journalNumber','keywords_uka','keywords_user','language','numberOfPages','organisationName',
        'organisationId','organisationSourceId','pages','peerReview','placeOfPublication','printIsbns','publicationDateYear','publicationDateMonth',
        'publicationDateDay','publicationType','publicationTypeUri','publisher','publicationStatus','standard_category_en','supervisors','title','volume');
    
    if(!$response) {
        $client = new Solarium\Client($config);

        $query = $client->createSelect();

        $query->setQuery('id:'.$term);
        
        $query->setFields($fieldArray);

        $response = $client->select($query);

        $content = '';
    }
    $organisationNameHolder = 'organisationName_' . $syslang;
    $publicationTypeHolder = 'publicationType_' . $syslang;
    $languageHolder = 'language_' . $syslang;
    
    /*$detailPageArray = explode(',', $detailPage);
    $staffDetailPage = $detailPageArray[0];
    $projectDetailPage = $detailPageArray[1];*/
        
    foreach ($response as $document) {
        $id = $document->id;
        $title = $this->fixArray($document->documentTitle);
        $authorNameArray = $document->authorName;
        $authorFirstNameArray = $document->authorFirstName;
        $authorLastNameArray = $document->authorLastName;
        $authorExternalArray = $document->authorExternal;
        $authorOrganisationArray = $document->authorOrganisation;
        $authorIdArray = $document->authorId;
        $i=0;
        foreach ($authorNameArray as $key => $name) {
            if($authorName) $authorName .= ',';
            if($authorId) $authorId .= ',';
            if($authorExternal || $authorExternal=='0') $authorExternal .= ',';
            if($authorOrganisation) $authorOrganisation .= ';';
            if($authorReverseName) $authorReverseName .= '; ';
            if($authorReverseNameShort) $authorReverseNameShort .= '$';
            $authorName .= mb_convert_case(strtolower($name), MB_CASE_TITLE, "UTF-8");
            $authorReverseName .= mb_convert_case(strtolower($authorLastNameArray[$i]), MB_CASE_TITLE, "UTF-8") . ', ' . mb_convert_case(strtolower($authorFirstNameArray[$i]), MB_CASE_TITLE, "UTF-8");
            $authorReverseNameShort .= mb_convert_case(strtolower($authorLastNameArray[$i]), MB_CASE_TITLE, "UTF-8") . ', ' . substr($authorFirstNameArray[$i], 0, 1) . '.';
            $authorId .= $authorIdArray[$i];
            $authorExternal .= $authorExternalArray[$i];
            $authorExternal = (string)$authorExternal;
            $authorOrganisation = $authorOrganisationArray[$i];
            $i++;
        }
        if($document->organisationName) {
            $organisationName = $document->organisationName[0];
            $organisationId = $document->organisationId[0];
            /*$i=0;
            foreach($organisationNameArray as $key => $organisationName) {
                if($organisations) $organisations .= ', ';
                $organisations .= '<a href="' . $organisationIdArray[$i] . '">' . $organisationName . '</a>';
                $i++;
            }*/
        }
        if($document->organisationSourceId) {
            $organisationSourceId = $document->organisationSourceId[0];
        }
        if($document->externalOrganisationsName) {
            $externalOrganisationsNameArray = $document->externalOrganisationsName;
            $externalOrganisationsIdArray = $document->externalOrganisationsId;
            $i=0;
            foreach($externalOrganisationsNameArray as $key => $externalOrganisationsName) {
                if($externalOrganisations) $externalOrganisations .= ', ';
                //$externalOrganisations .= '<a href="' . $externalOrganisationsIdArray[$i] . '">' . $externalOrganisationsName . '</a>';
                $externalOrganisations .= $externalOrganisationsName;
                $i++;
            }
        }
        
        $abstract = $this->fixArray($document->abstract);
        $attachmentLimitedVisibility = $document->attachmentLimitedVisibility;
        $attachmentMimeType = $document->attachmentMimeType;
        $attachmentSize = $document->attachmentSize;
        $attachmentTitle = $document->attachmentTitle;
        $attachmentUrl = $document->attachmentUrl;
        $bibtex = $document->bibtex;
        $cite = $document->cite;
        $doi = $document->doi;
        $electronicIsbns = $document->electronicIsbns;
        $edition = $document->edition;
        $event = $document->event;
        $eventCity = $document->eventCity;
        $eventCountry = $document->eventCountry;
        $hostPublicationTitle = $document->hostPublicationTitle;
        $issn = $document->issn;
        $journalNumber = $document->journalNumber;
        $journalTitle = $document->journalTitle;
        $keywordsUka = $document->keywordsUka;
        $keywordsUser = $document->keywordsUser;
        $language = $this->fixArray($document->language);
        $numberOfPages = $document->numberOfPages;
        $pages = $document->pages;
        $peerReview = $document->peerReview;
        $printIsbns = $document->printIsbns;
        $publicationDateYear = $document->publicationDateYear;
        $publicationDateMonth = $document->publicationDateMonth;
        $publicationDateDay = $document->publicationDateDay;
        $publicationStatus = $document->publicationStatus;
        $placeOfPublication = $document->placeOfPublication;
        $publisher = $document->publisher;
        $publicationType = $this->fixArray($document->publicationType);
        $publicationTypeUri = $document->publicationTypeUri;
        $supervisors = $document->supervisorName;
        $type = $document->type;
        $volume = $document->volume;
        
        $data = array(
            'abstract' => $abstract,
            'attachmentLimitedVisibility' => $attachmentLimitedVisibility,
            'attachmentMimeType' => $attachmentMimeType,
            'attachmentSize' => $attachmentSize,
            'attachmentTitle' => $attachmentTitle,
            'attachmentUrl' => $attachmentUrl,
            'authorExternal' => $authorExternal,
            'authorId' => $document->authorId,
            'authorName' => $document->authorName,
            'authorOrganisation' => $authorOrganisation,
            'authorReverseName' => rawurlencode($authorReverseName),
            'authorReverseNameShort' => rawurlencode(str_replace("$", ", ", $this->str_lreplace("$", " and ", $authorReverseNameShort))),
            'bibtex' => $bibtex,
            'cite' => $cite,
            'doi' => $doi,
            'edition' => $edition,
            'electronicIsbns' => $electronicIsbns,
            'externalOrganisations' => $externalOrganisations,
            'id' => $id,
            'hostPublicationTitle' => $hostPublicationTitle,
            'issn' => $issn,
            'journalTitle' => $journalTitle,
            'journalNumber' => $journalNumber,
            'keywords_uka' => $keywordsUka,
            'keywords_user' => $keywordsUser,
            'language' => $language,
            'numberOfPages' => $numberOfPages,
            'organisationName' => $organisationName,
            'organisationId' => $organisationId,
            'organisationSourceId' => $organisationSourceId,
            'pages' => $pages,
            'peerReview' => $peerReview,
            'placeOfPublication' => $placeOfPublication,
            'printIsbns' => $printIsbns,
            'publicationDateYear' => $publicationDateYear,
            'publicationDateMonth' => $publicationDateMonth,
            'publicationDateDay' => $publicationDateDay,
            'publicationType' => $publicationType,
            'publicationTypeUri' => $publicationTypeUri,
            'publisher' => $publisher,
            'publicationStatus' => $publicationStatus,
            'standard_category_en' => $standardCategory,
            'supervisors' => $supervisors,
            'title' => $title,
            'volume' => $volume,
        );

        //$GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_devlog', array('msg' => print_r($data,true), 'crdate' => time()));
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


function str_lreplace($search, $replace, $subject)
{
    $pos = strrpos($subject, $search);

    if($pos !== false)
    {
        $subject = substr_replace($subject, $replace, $pos, strlen($search));
    }

    return $subject;
}


function listStudentPapers($facet, $term, $syslang, $config, $tableLength, $tableStart, $pageid, $categories, $filterQuery, $papertype, $tableFields, $action, $publicationCategories)
{
    $client = new Solarium\Client($config);

    $query = $client->createSelect();
        
    if($filterQuery) {
        //$filterQuery = ' AND (documentTitle:*' . $filterQuery . '*)';
        $filterQuery = str_replace(" ","\ ",$filterQuery);
        $filterQuery = " AND ((documentTitle:*$filterQuery*) OR authorName:*$filterQuery*)";
    }
    
    if($papertype) {
        $paperTypeArray = explode(',', $papertype);
        foreach($paperTypeArray as $key => $value) {
            if($papertype) $papertype .= ' OR ';
            $papertype .= 'genre:studentPublications' . $value;
        }
        $papertype = ' AND (' . $papertype . ')';
    }

    $queryToSet = 'docType:studentPaper AND (organisationSourceId  :'.$term.')' . $papertype . $filterQuery;
    $query->setQuery($queryToSet);
    //$query->addParam('rows', 1500);
    $query->setStart($tableStart)->setRows($tableLength);
    
    // get the facetset component
    $facetSet = $query->getFacetSet();
    // create a facet field instance and set options
    $facetSet->createFacetField('standard')->setField('standardCategory');
    $facetSet->createFacetField('language')->setField('language');
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
        'publicationDateYear' => 'desc',
        'documentTitle' => 'asc'
    );
    $query->addSorts($sortArray);

    $response = $client->select($query);
    
    $numFound = $response->getNumFound();
    
    $facetStandard = $response->getFacetSet()->getFacet('standard');
    if($syslang==="en") {
        $facetHeader = "Publication Type";
    } else {
        $facetHeader = "Publikationstyp";
    }
    foreach ($facetStandard as $value => $count) {
        //if($count > 0) {
            $facetResult["standardCategory"][] = array($value, $count, $facetHeader);
        //}
    }

    $facetLanguage = $response->getFacetSet()->getFacet('language');
    if($syslang==="en") {
        $facetHeader = "Language";
    } else {
        $facetHeader = "Språk";
    }
    foreach ($facetLanguage as $value => $count) {
        //if($count > 0) {
            $facetResult["language"][] = array($value, $count, $facetHeader);
        //}
    }

    $facetYear = $response->getFacetSet()->getFacet('year');
    if($syslang==="en") {
        $facetHeader = "Publication Year";
    } else {
        $facetHeader = "Publikationsår";
    }
    foreach ($facetYear as $value => $count) {
        //if($count > 0) {
            $facetResult['publicationDateYear'][] = array($value, $count, $facetHeader);
        //}
    }
        
    foreach ($response as $document) {     
        $data[] = array(
            $document->id,
            $this->fixArray($document->documentTitle),
            ucwords(strtolower($this->fixArray($document->authorName))),
            $document->publicationDateYear,
            $document->organisationName
        );
    }
    $resArray = array('data' => $data, 'numFound' => $numFound, 'facet' => $facetResult, 'debug' => $queryToSet);
    return json_encode($resArray);
}


function listTagCloud($scope, $syslang, $config, $pageid, $path)
{
    $client = new Solarium\Client($config);

    $query = $client->createSelect();
    
    $hideVal = 'lth_solr_hide_' . $pageid . '_i';
    
    if($scope) {
        $scope = json_decode(urldecode($scope),true);
        
        foreach($scope as $key => $value) {
            if($term) {
                $term .= " OR ";
            }
            if($key === "fe_groups") {
                $term .= "organisationSourceId:$value[0]";
            } else {
                $term .= "authorId:$value[0]";
            }
        }
    }

    $query->setQuery('docType:publication AND -' . $hideVal . ':1 AND publicationDateYear:[* TO ' . date("Y") . '] AND ('.$term.')');
    //$GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_devlog', array('msg' => 'docType:publication AND -' . $hideVal . ':1 AND publicationDateYear:[* TO ' . date("Y") . '] AND ('.$term.')', 'crdate' => time()));
    //$query->addParam('rows', 1500);
    $query->setStart(0)->setRows(10000);
    $sortArray = array(
        'documentTitle' => 'asc'
    );
    $query->addSorts($sortArray);

    $response = $client->select($query);
    
    $numFound = $response->getNumFound();
    $tagArray = array();
    $i=1;
    
    foreach ($response as $document) {
        if(is_array($document->keywordsUser)) {
            foreach($document->keywordsUser as $key => $value) {
                $keywordsArray[] = $value;
            }
        }
        if(is_array($document->keywordsUka)) {
            foreach($document->keywordsUka as $key => $value) {
                $keywordsArray[] = $value;
            }
        }
    }
    asort($keywordsArray);
    foreach($keywordsArray as $key => $value) {
        if($oldValue != $value && $i > 0) {
            $data[] = array(
                /*$document->id,
                ,
                ucwords(strtolower($this->fixArray($document->authorName))),
                $this->fixArray($document->publicationType),
                $document->publicationDateYear,
                $document->publicationDateMonth,
                $document->publicationDateDay,
                $document->pages,
                $document->journalTitle,
                $document->journalNumber*/
                'text' => $value,
                'link' => urldecode($path) . '?keyword=' . $value,
                'weight' => (13*$i)
            );
            $i=0;
        }
        $oldValue = $value;
        $i++;
    }
    $resArray = array('data' => $data, 'numFound' => $numFound);
    return json_encode($resArray);
}


function showStudentPaper($term, $syslang, $config)
{
    $client = new Solarium\Client($config);

    $query = $client->createSelect();

    $query->setQuery('id:'.$term);
    
    $response = $client->select($query);
    $numFound = $response->getNumFound();
    $content = '';
        
    foreach ($response as $document) {
        $id = $document->id;
        $abstract = $document->abstract;
        $documentTitle = $document->documentTitle;
        $authorNameArray = $document->authorName;
        //$authorIdArray = $document->authorId;
        $i=0;
        if(is_array($authorNameArray)) {
            foreach ($authorNameArray as $key => $authorName) {
                if($authors) $authors .= ', ';
                $authors .=  mb_convert_case(strtolower($authorName), MB_CASE_TITLE, "UTF-8");
                //$authors .= '<a href="' . $staffDetailPage . '?no_cache=1&uuid=' . $authorIdArray[$i] . '">' . mb_convert_case(strtolower($authorName), MB_CASE_TITLE, "UTF-8") . '</a>';
                $i++;
            }
        }

        $organisations = $document->organisationName[0];

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

        $publicationType = $document->genre;
        $language = $this->fixArray($document->language);
        $publicationDateYear = $document->publicationDateYear;
        $keywords = $this->fixArray($document->keywordsUser);
        $documentUrl = $document->documentUrl;
        $supervisorName = $document->supervisorName;
        $organisationSourceId = $document->organisationSourceId[0];
        $bibtex = "@misc{" . $id . ",<br />";
        if($abstract) $bibtex .= "abstract = {" . $abstract . "},<br />";
        $bibtex .= "author = {" . $authors . "},<br />";
        $bibtex .= "keyword = {" . $keywords . "},<br />";
        $bibtex .= "language = {" . $language . "},<br />";
        $bibtex .= "note = {Student Paper},<br />";
        $bibtex .= "title = {" . $documentTitle . "},<br />";
        $bibtex .= "year = {" . $publicationDateYear . "},<br />";
        $bibtex .= "}";
                
        $data = array(
            "abstract" => $abstract,
            "documentTitle" => $documentTitle,
            "authors" => $authors,
            "organisations" => $organisations,
            "externalOrganisations" => $externalOrganisations,
            "publicationType" => $publicationType,
            "language" => $language,
            "publicationDateYear" => $publicationDateYear,
            "keywords" => $keywords,
            "documentUrl" => $documentUrl,
            "supervisorName" => $supervisorName,
            "organisationSourceId" => $organisationSourceId,
            "bibtex" => $bibtex
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
    
    $resArray = array('data' => $data);
    
    return json_encode($resArray);
}


function listProjects($scope, $syslang, $config, $tableLength, $tableStart, $filterQuery)
{
    $client = new Solarium\Client($config);

    $query = $client->createSelect();
    
    if($filterQuery) {
        $filterQuery = str_replace(" ","\ ",$filterQuery);
        $filterQuery = " AND ((projectTitle:*$filterQuery*) OR paticipant:*$filterQuery*)";
    }
    
    if($scope) {
        $scope = json_decode(urldecode($scope),true);
        
        foreach($scope as $key => $value) {
            if($term) {
                $term .= " OR ";
            }
            if($key === "fe_groups") {
                $term .= "organisationSourceId:$value[0]";
            } else {
                $term .= "authorId:$value[0]";
            }
        }
    }

    $query->setQuery('docType:upmproject AND (' . $term . ')' . $filterQuery);
    //$query->addParam('rows', 1500);
    $query->setStart($tableStart)->setRows($tableLength);
    
    $response = $client->select($query);
    
    $numFound = $response->getNumFound();
        
    foreach ($response as $document) {     
        $data[] = array(
            'id' => $document->id,
            'title' => $document->projectTitle,
            'participants' => ucwords(strtolower($this->fixArray($document->participants))),
            'projectStartDate' => substr($document->projectStartDate,0,10).'',
            'projectEndDate' => substr($document->projectEndDate,0,10).'',
            'projectStatus' => ucwords(strtolower(str_replace('_',' ',$document->projectStatus)))
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
        $data = array(
            'id' => $document->id,
            'title' => $document->projectTitle,
            'participants' => ucwords(strtolower($this->fixArray($document->participants))),
            'projectStartDate' => substr($document->projectStartDate,0,10).'',
            'projectEndDate' => substr($document->projectEndDate,0,10).'',
            'projectStatus' => ucwords(strtolower(str_replace('_',' ',$document->projectStatus))),
            'description' => $document->abstract,
        );
    }
    $resArray = array('data' => $data);
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
        $label = $document->display_name . ' ' . $this->fixArray($document->phone);// . ' ' . $document->mobile;// . ' ' . $document->email;
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


function listStaff($facet, $pageid, $pid, $syslang, $scope, $tableLength, $tableStart, $categories, 
        $custom_categories, $config, $filterQuery, $tableFields, $action, $limitToStandardCategories)
{
    if($action==='exportStaff') {
        $fieldArray = json_decode($tableFields, true);
    } else {
        $fieldArray = array("firstName","lastName","title","phone","id","email","organisationName","primaryAffiliation","homepage",
                "image","lucrisPhoto","intro","roomNumber","mobile","organisationId","guid","uuid","heritage");
    }
    
    $facetResult = array();
    
    $currentDate = gmDate("Y-m-d\TH:i:s\Z");
    
    $client = new Solarium\Client($config);
    
    $query = $client->createSelect();
    
    $hideVal = 'lth_solr_hide_' . $pageid . '_intS';
    
    if($filterQuery) {
        $filterQuery = str_replace(' ','\\ ',$filterQuery);
        $filterQuery = ' AND (name:*' . $filterQuery . '* OR phone:*' . $filterQuery . '* OR title:*' . $filterQuery . '* OR organisationName:*' . $filterQuery . '*)';
    }
    
    if($scope) {
        $feGroupsArray = array();
        $scope = json_decode(urldecode($scope),true);
        foreach($scope as $key => $value) {
            if($term) {
                $term .= " OR ";
            }
            if($key === "fe_groups") {
                $term .= "heritage:" . implode(' OR heritage:', $value);
                $feGroupsArray = $value;
            } else {
                $term .= "primaryUid:" . implode(' OR primaryUid:', $value);
            }
        }
    }

    $queryToSet = 'docType:staff AND (primaryAffiliation:employee OR primaryAffiliation:member) AND (' . $term . ')'. ' AND disable_intS:0 AND -' . $hideVal . ':[* TO *]' . $filterQuery;
    //docType:staff AND primaryAffiliation:employee AND (name:*'.$term . '* OR phone:*' . $term . '* OR email:*' . $term . '*)'
    $query->setQuery($queryToSet);
    $query->setFields($fieldArray);
    $query->setStart($tableStart)->setRows($tableLength);
    
    // get the facetset component
    $facetSet = $query->getFacetSet();
        
    // create a facet field instance and set options
    if($categories === 'standard_category') {
        $catVal = 'standardCategory';
    } elseif($categories === 'custom_category') {
        $catVal = 'lth_solr_cat_' . $pageid . '_stringM';
    }
    if($categories === 'standard_category') {
        $facetSet->createFacetField('standard')->setField($catVal);
    } else if($categories === 'custom_category') {
        $facetSet->createFacetField('custom')->setField($catVal);
    }
    
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
        //$GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_devlog', array('msg' => $facetQuery, 'crdate' => time()));
    }
    
    if($limitToStandardCategories) {
        $limitToStandardCategoriesArray = explode(',',$limitToStandardCategories);
        foreach($limitToStandardCategoriesArray as $key => $value) {
            if($facetQuery) {
                $facetQuery .= ' OR ';
            }
            $facetQuery .= 'standardCategory:' . $value;
        }
        $query->addFilterQuery(array('key' => 0, 'query' => $facetQuery, 'tag'=>'inner'));
    }
        
    $sortArray = array(
        'lth_solr_sort_' . $pageid . '_i' => 'asc',
        'lastNameExact' => 'asc',
        'firstNameExact' => 'asc'
    );
    $query->addSorts($sortArray);

    $response = $client->select($query);

    $numFound = $response->getNumFound();

    // display facet query count
    $facetHeader = "";
    if($syslang==="en") {
        $facetHeader = "Staff category";
    } else {
        $facetHeader = "Personalkategori";
    }

    if($categories === 'standard_category' && !$limitToStandardCategories) {
        $facetStandard = $response->getFacetSet()->getFacet('standard');
        foreach ($facetStandard as $value => $count) {
            if($count > 0) $facetResult[$catVal][] = array($value, $count, $facetHeader);
        }
    } else if($categories === 'custom_category' && !$limitToStandardCategories) {
        $facetCustom = $response->getFacetSet()->getFacet('custom');
        foreach ($facetCustom as $value => $count) {
            if($count > 0) $facetResult[$catVal][] = array($value, $count, $facetHeader);
        }
    } 
    $introVar = 'staff_custom_text_' . $pageid . '_s';
    
    $i=0;
    $heritageArray = array();
    
    foreach ($response as $document) {
        $image = '';
        $intro = '';
        if($document->$introVar) {
            $intro = '<p class="lthsolr_intro">' . $document->$introVar . '</p>';
        }

        if($document->image) {
            $image = '/fileadmin' . $document->image;
        } else if($document->lucrisPhoto) {
            $image = $document->lucrisPhoto;
        } else {
            $image = '';
        }
        
        if($action==='exportStaff') {
            foreach($fieldArray as $field) {
                $data[$i][$field] = $document->$field;
            }
            $i++;
        } else {
            $data[] = array(           
                "firstName" => mb_convert_case(strtolower($document->firstName), MB_CASE_TITLE, "UTF-8"),
                "lastName" => mb_convert_case(strtolower($document->lastName), MB_CASE_TITLE, "UTF-8"),
                "title" => $document->title,
                "phone" => $document->phone,
                "id" => $document->guid,
                "email" => $document->email,
                "organisationName" => $document->organisationName,
                "primaryAffiliation" => $document->primaryAffiliation,
                "homepage" => $document->homepage,
                "image" => $image,
                "intro" => $intro,
                "roomNumber" => $this->fixRoomNumber($document->roomNumber),
                "mobile" => $document->mobile,
                "organisationId" => $document->organisationId,
                "guid" => $document->guid,
                "uuid" => $document->uuid
            );
            $heritageArray[] = $document->heritage;
        }
    }
    foreach($feGroupsArray as $fKey => $fValue) {
        if(count($heritageArray) > 0 && count($feGroupsArray) > 0) {
            $includeThese = array();
            $includeFlag = FALSE;
            foreach($heritageArray as $hKey1 => $hValue1) {
                foreach($hValue1 as $hKey => $hValue) {
                    //$GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_devlog', array('msg' => $hValue.'::'.$fValue, 'crdate' => time()));
                    if($hValue==='new') {
                        $includeFlag = TRUE;
                    } else if($hValue !== $fValue && $includeFlag) {
                        $includeThese[] = $hValue;
                    } else if($hValue === $fValue && $includeFlag) {
                        $includeThese[] = $hValue;
                        $includeFlag = FALSE;
                    }
                }
            }
            $includeThese = array_unique($includeThese);
        }
    }
    $resArray = array('data' => $data, 'numFound' => $numFound,'facet' => $facetResult, 'includeThese' => $includeThese, 'debug' => $queryToSet.$facetQuery);
    return json_encode($resArray);
}


function fixRoomNumber($input)
{
    if(is_array($input)) {
        $input = array_unique($input);
    }
    return $input;
}


function showStaff($scope, $config, $syslang)
{
    $fieldArray = array("docType","firstName","lastName","title","phone","id","email","organisationName","primaryAffiliation","homepage","image","intro","roomNumber",
        "mobile","uuid","guid","organisationId","organisationPhone","organisationStreet","organisationCity","organisationPostalAddress",
        "profileInformation","coordinates","lucrisPhoto");
    
    $content = '';
    $personData = array();
    $publicationData = array();
    $projectData = array();
        
    if($scope) {
        $scope = json_decode(urldecode($scope),true);
        $scope = $scope['fe_users'][0];
    }
    
    $client = new Solarium\Client($config);
    $query = $client->createSelect();
    $queryToSet = 'docType:staff AND (guid:' . $scope . ' OR uuid:' . $scope . ')';
    $query->setQuery($queryToSet);
    $query->setFields($fieldArray);
    $response = $client->select($query);

    foreach ($response as $document) {    
        $id = $document->id;
        $docType = $document->docType;

        $intro = '';
        if($document->$introVar) {
            $intro = '<p class="lthsolr_intro">' . $document->staff_custom_text_s . '</p>';
        }

        if($docType === 'staff') {
            if($document->image) {
                $image = '/fileadmin' . $document->image;
            } else if($document->lucrisPhoto) {
                $image = $document->lucrisPhoto;
            }

            $data[] = array(
                "firstName" => ucwords(strtolower($document->firstName)),
                "lastName" => ucwords(strtolower($document->lastName)),
                "title" => $document->title,
                "phone" => $document->phone,
                "email" => $document->email,
                "organisationName" => $document->organisationName,
                "primaryAffiliation" => $document->primaryAffiliation,
                "homepage" => $document->homepage,
                "image" => $image,
                "intro" => $intro,
                "roomNumber" => $document->roomNumber,
                "mobile" => $document->mobile,
                "uuid" => $document->uuid,
                "guid" => $document->guid,
                "organisationId" => $document->organisationId,
                "organisationPhone" => $document->organisationPhone,
                "organisationStreet" => $document->organisationStreet,
                "organisationCity" => $document->organisationCity,
                "organisationPostalAddress" => $document->organisationPostalAddress,
                "profileInformation" => $document->profileInformation,
                "coordinates" => $this->fixArray($document->coordinates)
            );
        }
    }
    //}
    
    $resArray = array('data' => $data, 'debug' => $queryToSet);
    
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


function compareOrder($a, $b)
{
  return $b[0] - $a[0];
}

}
$myObject = new lthSolrAjax;
$myObject->myInit();