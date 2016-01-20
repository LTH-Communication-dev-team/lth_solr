<?php
// Exit, if script is called directly (must be included via eID in index_ts.php)
if (!defined ('PATH_typo3conf')) die ('Could not access this script directly!');

$content = '';
$query = '';
$action = '';
$sid = '';

$query = htmlspecialchars(t3lib_div::_GP("query"));
$action = htmlspecialchars(t3lib_div::_GP("action"));
$scope = htmlspecialchars(t3lib_div::_GP("scope"));
$facet = t3lib_div::_GP("facet");
$pid = t3lib_div::_GP('pid');
$sys_language_uid = t3lib_div::_GP('sys_language_uid');
$table_length = t3lib_div::_GP('table_length');
$pageid = t3lib_div::_GP('pageid');
$custom_categories = t3lib_div::_GP('custom_categories');
$sid = t3lib_div::_GP("sid");
date_default_timezone_set('Europe/Stockholm');

tslib_eidtools::connectDB();
//$GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_devlog', array('msg' => $query, 'crdate' => time()));
//$GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_devlog', array('msg' => $sys_language_uid, 'crdate' => time()));

switch($action) {
    case 'listEvents':
        listEvents();
        break;
    case 'addEvent':
        addEvent($title, $startDate, $endDate, $startTime, $endTime, $allday, $description, $place);
        break;
    case 'updateEvent':
        updateEvent($uid, $title, $startDate, $endDate, $startTime, $endTime, $allday, $description, $place);
        break;
    case 'getEvent':
        getEvent($uid);
        break;
    case 'facetSearch':
        $content = facetSearch($facet, $pageid, $pid, $sys_language_uid, $scope, $table_length, $custom_categories);
        break;
    case 'detail':
        $content = detail($scope);
        break;
    case 'rest':
        $content = rest();
        break;    
    default:
        $content = basicSelect($query);
        break;
}

print $content;

function rest()
{
    
    $requestUrl = 'https://devel.atira.dk/lund/ws/rest/person?uuids.uuid=8b564b09-9963-483b-84f9-3396ec18a67e&rendering=xml_long';
    $xml = file_get_contents($requestUrl);
    return $xml;
}


function facetSearch($facet, $pageid, $pid, $sys_language_uid, $scope, $table_length, $custom_categories)
{
    $content = '';
    $data = array();
    $facetResult = array();
    
    require(__DIR__.'/init.php');
    
    $catVal = 'lth_solr_cat_' . $pageid . '_' . $sys_language_uid . '_ss';
    $hideVal = 'lth_solr_hide_' . $pageid . '_' . $sys_language_uid . '_i';

    // create a client instance
    $client = new Solarium\Client($config);

    // get a select query instance
    $query = $client->createSelect();
            
    $query->setQuery('doctype_s:"lucat" AND usergroup_txt:'.$scope.' AND hide_on_web_i:0 AND -' . $hideVal . ':[* TO *]');
    
    // get the facetset component
    $facetSet = $query->getFacetSet();
    if($facet) {
        $facetArray = json_decode($facet, true);
        $i=0;
        foreach($facetArray as $key => $value) {
            $facetTempArray = explode('###', $value);
            $query->addFilterQuery(array('key' => $i, 'query' => $facetTempArray[0] . ':"' . $facetTempArray[1] . '"', 'tag'=>'inner'));
            $i++;
        }
    } else if($custom_categories) {
        $facetSet->createFacetField('custom')->setField($catVal);
    } else {
        $facetSet->createFacetField('title')->setField('title_autocomplete');
        $facetSet->createFacetField('ou')->setField('ou_autocomplete');
    }
    
    //$lth_solr_sortorder = 'lth_solr_sort_' . $pageid . '_' . $sys_language_uid . '_i';
    
    $sortArray = array(
            'lth_solr_sort_' . $pageid . '_' . $sys_language_uid . '_i' => 'asc',
            'last_name_s' => 'asc',
            'first_name_s' => 'asc'
        );
    
    //$query->addSort('last_name_s', $query::SORT_ASC);
    //$query->addSort('first_name_s', $query::SORT_ASC);
    
    $query->addSorts($sortArray);
    
    $query->addParam('rows', 15000);
    
    // this executes the query and returns the result
    $response = $client->select($query);

    // display the total number of documents found by solr
    //$numFound = $resultset->getNumFound();

    // display facet query count
    if(!$facet) {
        if($custom_categories) {
            $facet_custom = $response->getFacetSet()->getFacet('custom');
            foreach ($facet_custom as $value => $count) {
                $facetResult[$catVal][] = array($value, $count);
            }
        } else {
            $facet_title = $response->getFacetSet()->getFacet('title');
            $facet_ou = $response->getFacetSet()->getFacet('ou');

            foreach ($facet_title as $value => $count) {
                $facetResult['title_autocomplete'][] = array($value, $count);
            }

            foreach ($facet_ou as $value => $count) {
                $facetResult['ou_autocomplete'][] = array($value, $count);
            }
        }
    }
    
    // show documents using the resultset iterator
    foreach ($response as $document) {
        if($document->image_t != NULL) {
            $image_t = 'uploads/pics/' . $document->image_t;
        } else {
            $image_t = 'typo3conf/ext/lth_solr/res/placeholder.gif';
        }
        $lth_solr_intro = $document->lth_solr_intro_t;
        $lth_solr_txt = $document->lth_solr_txt_t;
        if($lth_solr_intro !== '') {
            $lth_solr_introArray = json_decode($lth_solr_intro, true);
            $lth_solr_intro = $lth_solr_introArray['lth_solr_intro_' . $pid . '_' . $sys_language_uid];
        }
        if($lth_solr_txt !== '') {
            $lth_solr_txtArray = json_decode($lth_solr_txt, true);
            $lth_solr_txt = $lth_solr_txtArray['lth_solr_txt_' . $pid . '_' . $sys_language_uid];
        }
        $data[] = array(
            ucwords(strtolower($document->first_name_t)) . ' ' . ucwords(strtolower($document->last_name_t)),
            ucwords(strtolower($document->title_autocomplete)),
            $document->phone_txt,
            $document->id,
            $document->email_t,
            $document->ou_t,
            $document->orgid_t,
            $document->primary_affiliation_t,
            $document->homepage_t,
            $image_t,
            $lth_solr_intro,
            $lth_solr_txt
        );
    }
    $resArray = array('data' => $data, 'facet' => $facetResult, 'draw' => 1);
    return json_encode($resArray);
}


function detail($scope)
{
    $content = '';
    $data = array();
    $facetResult = array();
    
    require(__DIR__.'/init.php');
    
    //$catVal = 'lth_solr_cat_' . $pid . '_' . $sys_language_uid . '_ss';

    // create a client instance
    $client = new Solarium\Client($config);

    // get a select query instance
    $query = $client->createSelect();

    $query->setQuery('homepage_t:'.$scope.' AND hide_on_web_i:0');
   
    // this executes the query and returns the result
    $response = $client->select($query);

    // show documents using the resultset iterator
    foreach ($response as $document) {
        if($document->image_t != NULL) {
            $image_t = 'uploads/pics/' . $document->image_t;
        } else {
            $image_t = 'typo3conf/ext/lth_solr/res/placeholder.gif';
        }
        $lth_solr_intro = $document->lth_solr_intro_t;
        $lth_solr_txt = $document->lth_solr_txt_t;
        if($lth_solr_intro !== '') {
            $lth_solr_introArray = json_decode($lth_solr_intro, true);
            $lth_solr_intro = $lth_solr_introArray['lth_solr_intro_' . $pid . '_' . $sys_language_uid];
        }
        if($lth_solr_txt !== '') {
            $lth_solr_txtArray = json_decode($lth_solr_txt, true);
            $lth_solr_txt = $lth_solr_txtArray['lth_solr_txt_' . $pid . '_' . $sys_language_uid];
        }
        $data = array(
            ucwords(strtolower($document->first_name_t)) . ' ' . ucwords(strtolower($document->last_name_t)),
            ucwords(strtolower($document->title_autocomplete)),
            $document->phone_txt,
            $document->id,
            $document->email_t,
            $document->ou_t,
            $document->orgid_t,
            $document->primary_affiliation_t,
            $document->homepage_t,
            $image_t,
            $document->room_number_txt,
            $document->maildelivery_txt,
            $lth_solr_intro,
            $lth_solr_txt
        );
    }
    $resArray = array('data' => $data);
    return json_encode($resArray);
}


function basicSelect($q)
{
    require(__DIR__.'/init.php');
    
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