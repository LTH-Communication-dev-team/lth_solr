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

$query = htmlspecialchars(t3lib_div::_GP("query"));
$action = htmlspecialchars(t3lib_div::_GP("action"));
$scope = htmlspecialchars(t3lib_div::_GP("scope"));
$facet = t3lib_div::_GP("facet");
$pid = t3lib_div::_GP('pid');
$sys_language_uid = t3lib_div::_GP('sys_language_uid');
$table_length = t3lib_div::_GP('table_length');
$pageid = t3lib_div::_GP('pageid');
$custom_categories = t3lib_div::_GP('custom_categories');
$categories = t3lib_div::_GP('categories');
$categoriesThisPage = t3lib_div::_GP('categoriesThisPage');
$introThisPage = t3lib_div::_GP('introThisPage');
$addPeople = t3lib_div::_GP('addPeople');
$sid = t3lib_div::_GP("sid");
date_default_timezone_set('Europe/Stockholm');

tslib_eidtools::connectDB();
//$GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_devlog', array('msg' => $query, 'crdate' => time()));
//$GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_devlog', array('msg' => $sys_language_uid, 'crdate' => time()));

switch($action) {
    case 'searchListShort':
        $content = searchListShort($query, $config);
        break;
    case 'facetSearch':
        $content = facetSearch($facet, $pageid, $pid, $sys_language_uid, $scope, $table_length, $categories, $custom_categories, $categoriesThisPage, $introThisPage, $addPeople, $config);
        break;
    case 'detail':
        $content = detail($scope, $config);
        break;
    case 'rest':
        $content = rest();
        break;    
    default:
        $content = basicSelect($query, $config);
        break;
}

print $content;


function searchListShort($q, $config)
{
    // create a client instance
    $client = new Solarium\Client($config);

    // get a select query instance
    $query = $client->createSelect();

    $query->setQuery('body_txt:*' . $q .'* OR display_name_t:*' . $q . '* OR phone_txt:*' . $q . '*');
    
    // this executes the query and returns the result
    $response = $client->select($query);
    
    // show documents using the resultset iterator
    
    foreach ($response as $document) {
        $id =$document->id;
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
        );
        //$data[] = array('id' => label' => $document->title_t);
    }
    //{id: "Botaurus stellaris", label: "Great Bittern", value: "Great Bittern"}
    return json_encode($data);
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


function facetSearch($facet, $pageid, $pid, $sys_language_uid, $scope, $table_length, $categories, $custom_categories, $categoriesThisPage, $introThisPage, $addPeople, $config)
{
    $content = '';
    $data = array();
    $facetResult = array();
        
    if($categories === 'standard_category') {
        $catVal = 'standard_category_sv_txt';
    } elseif($categories === 'custom_category') {
        if(!$categoriesThisPage || $categoriesThisPage == '') {
            //$GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_devlog', array('msg' => 'global', 'crdate' => time()));
            $catVal = 'lth_solr_cat_ss';
        } else {
            $catVal = 'lth_solr_cat_' . $pageid . '_ss';
        } 
        
    }

    if(!$introThisPage || $introThisPage == '') {
        $introVar = 'staff_custom_text_s';
    } else {
        $introVar = 'staff_custom_text_' . $pageid . '_s';
    }
        
    $hideVal = 'lth_solr_hide_' . $pageid . '_i';

    // create a client instance
    $client = new Solarium\Client($config);

    // get a select query instance
    $query = $client->createSelect();
    
    if($addPeople) {
        $addPeopleArray = explode("\n", $addPeople);
        $addPeople = '';
        foreach($addPeopleArray as $key => $value) {
            $addPeople .= ' OR id:' . $value;
        }
    }
    $queryToSet = '(doctype_s:"lucat" AND usergroup_txt:'.$scope.' AND hide_on_web_i:0 AND -' . $hideVal . ':[* TO *])' . $addPeople;
    $query->setQuery($queryToSet);
    
    // get the facetset component
    $facetSet = $query->getFacetSet();
    if($facet) {
        $facetArray = json_decode($facet, true);
        //$GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_devlog', array('msg' => $facet, 'crdate' => time()));

        $i=0;
        foreach($facetArray as $key => $value) {
            $facetTempArray = explode('###', $value);
            $query->addFilterQuery(array('key' => $i, 'query' => $facetTempArray[0] . ':"' . $facetTempArray[1] . '"', 'tag'=>'inner'));
            $i++;
        }
    } else if($categories === 'standard_category') {
        $facetSet->createFacetField('standard')->setField($catVal);
    } else if($custom_categories) {
        $facetSet->createFacetField('custom')->setField($catVal);
    } else {
        $facetSet->createFacetField('title')->setField('title_sort');
    }
    
    //$lth_solr_sortorder = 'lth_solr_sort_' . $pageid . '_' . $sys_language_uid . '_i';
    
    $sortArray = array(
        'lth_solr_sort_' . $pageid . '_i' => 'asc',
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
        if($categories === 'standard_category') {
            $facet_standard = $response->getFacetSet()->getFacet('standard');
            foreach ($facet_standard as $value => $count) {
                $facetResult[$catVal][] = array($value, $count);
            }
        } else if($custom_categories) {
            $facet_custom = $response->getFacetSet()->getFacet('custom');
            foreach ($facet_custom as $value => $count) {
                $facetResult[$catVal][] = array($value, $count);
            }
        } else {
            $facet_title = $response->getFacetSet()->getFacet('title');
            //$facet_ou = $response->getFacetSet()->getFacet('ou');

            foreach ($facet_title as $value => $count) {
                $facetResult['title_sort'][] = array($value, $count);
            }

            /*foreach ($facet_ou as $value => $count) {
                $facetResult['ou_autocomplete'][] = array($value, $count);
            }*/
        }
    }
    
    // show documents using the resultset iterator
    foreach ($response as $document) {
        
        $intro_t = '';
        if($document->$introVar !== '') {
            $intro_t = $document->$introVar;
        }
        
        if($document->image_s) {
            $image = '/fileadmin' . $document->image_s;
        } else {
            $image = '/typo3conf/ext/lth_solr/res/placeholder.gif';

        }
        
        $data[] = array(
            ucwords(strtolower($document->first_name_t)),
            ucwords(strtolower($document->last_name_t)),
            //ucwords(strtolower($document->first_name_t)) . ' ' . ucwords(strtolower($document->last_name_t)),
            $document->title_txt,
            $document->title_en_txt,
            $document->phone_txt,
            $document->id,
            fixString($document->email_t),            
            $document->oname_txt,
            $document->oname_en_txt,
            $document->primary_affiliation_t,
            $document->homepage_t,
            $image,
            fixString($intro_t),
            fixString($document->room_number_s),
            $document->mobile_txt
        );
    }
    $resArray = array('data' => $data, 'facet' => $facetResult, 'draw' => 1);
    return json_encode($resArray);
}


function roomWrap($input)
{
    if(input=='') {
        return '';
    } else {
        return " (Rum $input)";
    }
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


function detail($scope, $config)
{
    $content = '';
    $data = array();
    $facetResult = array();
        
    //$catVal = 'lth_solr_cat_' . $pid . '_' . $sys_language_uid . '_ss';

    // create a client instance
    $client = new Solarium\Client($config);

    // get a select query instance
    $query = $client->createSelect();

    $query->setQuery('id:'.$scope.' AND hide_on_web_i:0');
   
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
            ucwords(strtolower($document->first_name_t)),
            ucwords(strtolower($document->last_name_t)),
            //ucwords(strtolower($document->first_name_t)) . ' ' . ucwords(strtolower($document->last_name_t)),
            $document->title_txt,
            $document->title_en_txt,
            $document->phone_txt,
            $document->id,
            fixString($document->email_t),            
            $document->oname_txt,
            $document->oname_en_txt,
            $document->primary_affiliation_t,
            $document->homepage_t,
            $image,
            fixString($intro_t),
            fixString($document->room_number_s)
        );
    }
    $resArray = array('data' => $data, 'lucris' => rest());
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