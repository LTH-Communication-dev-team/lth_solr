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

$term = htmlspecialchars(t3lib_div::_GP("term"));
$peopleOffset = htmlspecialchars(t3lib_div::_GP("peopleOffset"));
$documentsOffset = htmlspecialchars(t3lib_div::_GP("documentsOffset"));
$query = htmlspecialchars(t3lib_div::_GP("query"));
$action = htmlspecialchars(t3lib_div::_GP("action"));
$scope = htmlspecialchars(t3lib_div::_GP("scope"));
$facet = t3lib_div::_GP("facet");
$pid = t3lib_div::_GP('pid');
$syslang = t3lib_div::_GP('syslang');
$table_length = t3lib_div::_GP('table_length');
$pageid = t3lib_div::_GP('pageid');
$custom_categories = t3lib_div::_GP('custom_categories');
$categories = t3lib_div::_GP('categories');
$categoriesThisPage = t3lib_div::_GP('categoriesThisPage');
$introThisPage = t3lib_div::_GP('introThisPage');
$addPeople = t3lib_div::_GP('addPeople');
$detailPage = t3lib_div::_GP('detailPage');
$sid = t3lib_div::_GP("sid");
date_default_timezone_set('Europe/Stockholm');

tslib_eidtools::connectDB();
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
        $content = searchLong($term, $config);
        break;
    case 'searchMorePeople':
        $content = searchMore($term, 'people', $peopleOffset, $documentsOffset, $config);
        break;
    case 'searchMoreDocuments':
        $content = searchMore($term, 'documents', $peopleOffset, $documentsOffset, $config);
        break;
    case 'listPublications':
        $content = listPublications($scope, $syslang, $config);
        break;
    case 'showPublication':
        $content = showPublication($scope, $syslang, $config, $detailPage);
        break;
    case 'listProjects':
        $content = listProjects($scope, $syslang, $config);
        break;
    case 'showProject':
        $content = showProject($scope, $syslang, $config);
        break;
    case 'listStaff':
        $content = listStaff($facet, $pageid, $pid, $syslang, $scope, $table_length, $categories, $custom_categories, $categoriesThisPage, $introThisPage, $addPeople, $config);
        break;
    case 'showStaff':
        $content = showStaff($scope, $config);
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

    $query = $client->createSelect();

    $groupComponent = $query->getGrouping();
    $groupComponent->addQuery('display_name:*' . $term . '* OR phone:*' . $term . '* OR email:*' . $term . '*');
    $groupComponent->addQuery('content:*' . $term . '*');
    $groupComponent->setSort('last_name_sort asc');
    $groupComponent->setLimit(5);    
    $resultset = $client->select($query);
    $groups = $resultset->getGrouping();
    foreach ($groups as $groupKey => $group) {
        foreach ($group as $document) {        
            $id = $document->id;
            $doktype = $document->doctype;
            if($doktype === 'lucat') {
                $label = fixArray($document->display_name);
            } else {
                $label = fixArray($document->title);
            }
            $value = $document->id;
            $data[] = array(
                'id' => $id,
                'label' => $label,
                'value' => $value 
            );
        }
    }
    return json_encode($data);
}


function searchLong($term, $config)
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
    
    $client = new Solarium\Client($config);
    $query = $client->createSelect();

    $groupComponent = $query->getGrouping();
    $groupComponent->addQuery('display_name:*' . $term . '* OR phone:*' . $term . '* OR email:' . $term);
    $groupComponent->addQuery('content:*' . $term . '*');
    $groupComponent->setSort('last_name_sort asc');
    $groupComponent->setLimit(5);
    $resultset = $client->select($query);
    
    $groups = $resultset->getGrouping();
    
    foreach ($groups as $groupKey => $group) {
        $numfound = $group->getNumFound();
        foreach ($group as $document) {
            $id = $document->id;
            $doktype = $document->doctype;
            if($doktype === 'lucat') {
                $peopleNumFound = $numfound;
                $display_name = $document->display_name;
                $email = $document->email;
                $phone = fixArray($document->phone);
                //$image = $document->image;
                $oname = fixArray($document->oname);
                $title = fixArray($document->title);
                $room_number = $document->room_number;
                if($room_number) $room_number = " (Rum $room_number)";
                $people .= '<li>';
                /*if($image) $people .= '<img class="align_left" src="' . $image . '" style="width:100px;height:100px;" />';
                $people .= "<h3>$display_name</h3>";
                $people .= "<p>$oname$room_number, $title</p>";
                $people .= "<p>";
                if($email) $people .= "<a href=\"mailto:$email\">$email</a><br />";
                if($phone) $people .= "Telefon: $phone<br />";
                if($homepage) $people .= $homepage;
                $people .= "</p>";*/
                $people .= "<p><b>$display_name</b>, $title, $oname$room_number<br />";
                if($email) $people .= "<a href=\"mailto:$email\">$email</a>, ";
                if($phone) $people .= "Telefon: $phone";
                $people .= "</p></li>";
            } else {
                $documentsNumFound = $numfound;
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
    }
    if($people && $peopleNumFound > 5) {
        $people .= "<li id=\"morePeople\"><a href=\"#\" onclick=\"searchResult('$term', 'searchMorePeople', 5, 5); return false;\">Visa fler</a></li>";
    }
    if($documents && $documentsNumFound > 5) {
        $documents .= "<li id=\"moreDocuments\"><a href=\"#\" onclick=\"searchResult('$term', 'searchMoreDocuments', 5, 5); return false;\">Visa fler</a></li>";
    }
    return json_encode(array('people' => $people, 'peopleNumFound' => $peopleNumFound, 'documents' => $documents, 'documentsNumFound' => $documentsNumFound, 'facet' => $facet));
}


function searchMore($term, $type, $peopleOffset, $documentsOffset, $config)
{
    $client = new Solarium\Client($config);

    $query = $client->createSelect();

    if($type==='people') {
        $query->setQuery('display_name:*' . $term . '* OR phone:*' . $term . '* OR email:' . $term);
        $query->setStart($peopleOffset)->setRows(10);
    } else {
        $query->setQuery('content:*' . $term . '*');
        $query->setStart($documentsOffset)->setRows(10);
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


function listPublications($term, $syslang, $config)
{
    $client = new Solarium\Client($config);

    $query = $client->createSelect();

    $query->setQuery('doctype:publication AND organisationId:'.$term);
    $query->addParam('rows', 1500);
    
    $response = $client->select($query);
    
    $numfound = $response->getNumFound();
        
    foreach ($response as $document) {     
        $data[] = array(
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
            'DT_RowId' => $document->id,
            'title' => fixArray($document->title),
            'authorName' => ucwords(strtolower(fixArray($document->authorName))),
            'publicationType_en' => fixArray($document->publicationType_en),
            'publicationDateYear' => $document->publicationDateYear,
            'abstract_en' => $document->abstract_en
        );
    }
    $resArray = array('data' => $data, 'draw'=> 1, 'recordsTotal'=> $numfound, 'recordsFiltered'=> $numfound, 'facet' => $facetResult, 'draw' => 1);
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
            $title = fixArray($document->title);
            $authorNameArray = $document->authorName;
            $authorIdArray = $document->authorId;
            $i=0;
            foreach ($authorNameArray as $key => $authorName) {
                if($authors) $authors .= ', ';
                $authors .= '<a href="' . $detailPage . '?no_cache=1&uuid=' . $authorIdArray[$i] . '">' . mb_convert_case(strtolower($authorName), MB_CASE_TITLE, "UTF-8") . '</a>';
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
            
            $externalOrganisationsNameArray = $document->externalOrganisationsName;
            $externalOrganisationsIdArray = $document->externalOrganisationsId;
            $i=0;
            foreach($externalOrganisationsNameArray as $key => $externalOrganisationsName) {
                if($externalOrganisations) $externalOrganisations .= ', ';
                $externalOrganisations .= '<a href="' . $externalOrganisationsIdArray[$i] . '">' . $externalOrganisationsName . '</a>';
                $i++;
            }
            
            $publicationType = fixArray($document->$publicationTypeHolder);
            $language = fixArray($document->$languageHolder);
            $publicationDateYear = $document->publicationDateYear;
            $abstract_en = fixArray($document->abstract_en);
            $abstract_sv = fixArray($document->abstract_sv);
            if($syslang == 'sv' && $abstract_sv) {
                $abstract = $abstract_sv;
            } else {
                $abstract = $abstract_en;
            }
            $pages = $document->pages;
            $numberOfPages = $document->numberOfPages;
            $volume = $document->volume;
            $journalNumber = $document->journalNumber;
            $publicationStatus = $document->publicationStatus;
            $peerReview = $document->peerReview;
            
            $content .= "<h3>$publicationType</h3>";
            
            if($abstract) {
                $content .= "<div><div class=\"textblock more-content\" style=\"height: 80px; overflow: hidden;\">$abstract</div>";

                $content .= "<a href=\"#\" onclick=\"showMore(this);return false;\" class=\"readmore\" data-height=\"144\">More</a></div>";
            }
            $content .= "<h2>Details</h2><table>";
            
            if($authors) $content .= "<tr><th>Authors</th><td>$authors</td></tr>";
            if($organisations) $content .= "<tr><th>Organisations</th><td>$organisations</td></tr>";
            if($externalOrganisations) $content .= "<tr><th>External organisations</th><td>$externalOrganisations</td></tr>";
            if($language) $content .= "<tr><th>Orginal language</th><td>$language</td></tr>";
            if($pages) $content .= "<tr><th>Pages (from-to)</th><td>$pages</td></tr>";
            if($numberOfPages) $content .= "<tr><th>Number of pages</th><td>$numberOfPages</td></tr>";
            if($journal) $content .= "<tr><th>Journal</th><td>$volume</td></tr>";
            if($volume) $content .= "<tr><th>Volume</th><td>$journalNumber</td></tr>";
            if($publicationStatus) $content .= "<tr><th>State</th><td>$publicationStatus</td></tr>";
            if($peerReview) $content .= "<tr><th>Peer-reviewed</th><td>$peerReview</td></tr>";
            //$content .= "<div><div></div><div>$publicationDateYear</td></tr>";
            //if($abstract) $content .= "<tr><th></th><td>$abstract_en</td></tr>";
            
            $content .= "</table>";
    }
    $resArray = array('data' => $content, 'title' => $title);
    return json_encode($resArray);
}


function listProjects($term, $syslang, $config)
{
    $client = new Solarium\Client($config);

    $query = $client->createSelect();

    $query->setQuery('doctype:upmproject AND organisationId:'.$term);
    $query->addParam('rows', 1500);
    
    $response = $client->select($query);
    
    $numfound = $response->getNumFound();
        
    foreach ($response as $document) {     
        $data[] = array(
/*
 * 'id' => $id,
                    'doctype' => 'upmproject',
                    'portalUrl' => $portalUrl,
                    'title_en' => $title_en,
                    'title_sv' => $title_sv,
                    'organisations_uuid' => $organisations_uuid,
                    'organisations_name_en' => $organisations_name_en,
                    'organisations_name_sv' => $organisations_name_sv,
                    'boost' => '1.0',
                    'date' => $current_date,
                    'tstamp' => $current_date,
                    'digest' => md5($id)
 */
            'DT_RowId' => $document->id,
            'title' => fixArray($document->title_en),
            'participants' => ucwords(strtolower(fixArray($document->participants))),
            'projectStartDate' => substr($document->projectStartDate,0,10).'',
            'projectEndDate' => substr($document->projectEndDate,0,10).'',
            'projectStatus' => ucwords($document->projectStatus)
        );
    }
    $resArray = array('data' => $data, 'draw'=> 1, 'recordsTotal'=> $numfound, 'recordsFiltered'=> $numfound, 'facet' => $facetResult, 'draw' => 1);
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


function fixArray($inputArray)
{
    if($doctype==='lucat') {
        return false;
    }
    if($inputArray) {
        if(is_array($inputArray)) {
            $inputArray = array_unique($inputArray);
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


function listStaff($facet, $pageid, $pid, $sys_language_uid, $scope, $table_length, $categories, $custom_categories, $categoriesThisPage, $introThisPage, $addPeople, $config)
{
    $content = '';
    $data = array();
    $facetResult = array();
        
    if($categories === 'standard_category') {
        //$catVal = 'standard_category_sv_txt';
        $catVal = 'standard_category_sv';
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
    
    if($scope) {
        $scopeArray = explode("\n", $scope);
        $scope = '';
        foreach($scopeArray as $key => $value) {
            if($scope) {
                $scope .= ' OR ';
            } else {
                $scope .= ' AND usergroup:';
            }
            $scope .= '"' . $value . '"';
        }
    }
    
    if($addPeople) {
        $addPeopleArray = explode("\n", $addPeople);
        $addPeople = '';
        foreach($addPeopleArray as $key => $value) {
            if($addPeople) {
                $addPeople .= ' OR ';
            } else if($scope) {
                $addPeople .= ' OR (id:';
            } else {
                $addPeople .= ' AND (id:';
            }
            $addPeople .= $value;
        }
        $addPeople .= ')';
    }
    //$queryToSet = '(doctype_s:"lucat" AND usergroup_txt:'.$scope.' AND hide_on_web_i:0 AND -' . $hideVal . ':[* TO *])' . $addPeople;
    $queryToSet = '(doctype:"lucat"'.$scope.' AND hide_on_web:0 AND -' . $hideVal . ':[* TO *])' . $addPeople;
    //$GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_devlog', array('msg' => $queryToSet, 'crdate' => time()));
    $query->setQuery($queryToSet);
    
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
            } else {
                $facetQuery .= $facetTempArray[0] . ':';
            }
            $facetQuery .= '"' . $facetTempArray[1] . '"';
        }
        //$GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_devlog', array('msg' => $facetQuery, 'crdate' => time()));
        $query->addFilterQuery(array('key' => 0, 'query' => $facetQuery, 'tag'=>'inner'));
    } else if($categories === 'standard_category') {
        $facetSet->createFacetField('standard')->setField($catVal);
    } else if($custom_categories) {
        $facetSet->createFacetField('custom')->setField($catVal);
    } else {
        $facetSet->createFacetField('title')->setField('title_sort');
    }
        
    $sortArray = array(
        'lth_solr_sort_' . $pageid . '_i' => 'asc',
        'last_name_sort' => 'asc',
        'first_name_sort' => 'asc'
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
        if($document->$introVar) {
            $intro_t = $document->$introVar;
        }
        
        if($document->image) {
            $image = '/fileadmin' . $document->image;
        } else {
            $image = '/typo3conf/ext/lth_solr/res/placeholder_noframe.gif';

        }
        
        $data[] = array(
            /*ucwords(strtolower($document->first_name_t)),
            ucwords(strtolower($document->last_name_t)),
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
            $document->mobile_txt*/
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
            array_unique($document->room_number),
            $document->mobile,
            $document->uuid,
            $document->orgid,
            $document->heritage2
        );
    }
    $resArray = array('data' => $data, 'iTotalRecords' => count($data),
  'iTotalDisplayRecords' => count($data),'facet' => $facetResult, 'draw' => 1);
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


function showStaff($scope, $config)
{
    $content = '';
    $personData = array();
    $publicationData = array();
    $projectData = array();

    $client = new Solarium\Client($config);
    $query = $client->createSelect();
    
    $groupComponent = $query->getGrouping();
    $groupComponent->addQuery('uuid:' . $scope);
    $groupComponent->addQuery('authorId:' . $scope);
    $groupComponent->addQuery('participantId:' . $scope);
    //$groupComponent->setSort('last_name_sort asc');
    $groupComponent->setLimit(1500);    
    $resultset = $client->select($query);
    $groups = $resultset->getGrouping();
    foreach ($groups as $groupKey => $group) {
        foreach ($group as $document) {        
            $id = $document->id;
            $doktype = $document->doctype;
            if($doktype === 'lucat') {
                if($document->image) {
                    $image = '/fileadmin' . $document->image;
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
                    $intro_t,
                    array_unique($document->room_number),
                    $document->mobile,
                    $document->uuid,
                    $document->orgid,
                    $document->heritage2
                );        
            } else if($doktype === 'publication') {
                $publicationData[] = array(
                    'DT_RowId' => $document->id,
                    'title' => fixArray($document->title),
                    'authorName' => ucwords(strtolower(fixArray($document->authorName))),
                    'publicationType_en' => fixArray($document->publicationType_en),
                    'publicationDateYear' => $document->publicationDateYear,
                    'abstract_en' => $document->abstract_en
                );
            } else if($doktype === 'upmproject') {
                $projectData[] = array(
                    'DT_RowId' => $document->id,
                    'title' => fixArray($document->title_en),
                    'participants' => ucwords(strtolower(fixArray($document->participants)))
                );
            }
        }
    }
    
    $resArray = array('personData' => $personData, 'publicationData' => $publicationData, 'projectData' => $projectData);
    
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