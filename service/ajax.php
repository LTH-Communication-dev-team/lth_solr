<?php
// Exit, if script is called directly (must be included via eID in index_ts.php)
if (!defined ('PATH_typo3conf')) die ('Could not access this script directly!');

class lthSolrAjax
{
    
function myInit()
{
    /* require(__DIR__.'/init.php');*/

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
    $addPeople = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('addPeople');
    $keyword = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('keyword');
    $papertype = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('papertype');
    $limitToStandardCategories = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('limitToStandardCategories');
    $webSearchScope = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('webSearchScope');
    $sorting = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('sorting');
    $thisGroupOnly = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('thisGroupOnly');
    $primaryRoleOnly = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('primaryRoleOnly');
    $dataSettings = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('dataSettings');

    $sid = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP("sid");
    date_default_timezone_set('Europe/Stockholm');

    $settings = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['lth_solr']);
    if(!$syslang) {
        if($dataSettings['syslang']) {
            $syslang = $dataSettings['syslang'];
        } else {
            if($dataSettings['sysLang']) {
                $syslang = $dataSettings['sysLang'];
            } else {
                $syslang = "sv";
            }
        }
    }

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
        case 'listComingDissertations':
            $content = $this->listPublications($facet, $scope, $syslang, $config, $tableLength, $tableStart, $pageid, $query, $keyword, $sorting, $tableFields, $action, $publicationCategories);
            break;
        case 'listStudentPapers':
        case 'exportStudentPapers':
            $content = $this->listStudentpapers($facet, $scope, $syslang, $config, $tableLength, $tableStart, $pageid, $categories, $query, $papertype, $tableFields, $action, $publicationCategories);
            break;
        case 'showPublication':
            $content = $this->showPublication('', $scope, $syslang, $config);
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
                    $custom_categories, $config, $query, $tableFields, $action, $limitToStandardCategories, $thisGroupOnly, $primaryRoleOnly);
            break;
        case 'showStaff':
            $content = $this->showStaff($scope, $config, $syslang);
            break;
        case 'rest':
            $content = $this->rest();
            break;
        case 'listTagCloud':
            $content = $this->listTagCloud($scope, $syslang, $config, $pageid, $term, $tableLength);
            break;
        case 'listCompare':
            $dataSettings['globalRoundId'] = $settings['roundId'];
            $content = $this->listCompare($dataSettings, $config);
            break;
        case 'showCompare':
            $content = $this->showCompare($dataSettings, $config);
            break;
        case 'listJobs':
            $content = $this->listJobs($dataSettings, $config);
            break;
        case 'showJob':
            $content = $this->showJob($dataSettings, $config);
            break;
        case 'listCourses':
            $content = $this->listCourses($dataSettings, $config);
            break;
        case 'showCourse':
            $content = $this->showCourse($dataSettings, $config);
            break;
        case 'listStatistics':
            $content = $this->listStatistics($dataSettings, $config);
            break;
        case 'listOrganisation':
            $content = $this->listOrganisation($dataSettings, $config);
            break;
        case 'listOrganisationStaff':
        case 'listOrganisationStaffFirstLetter':
        case 'listSingleOrganisationStaff':
            $content = $this->listOrganisationStaff($dataSettings, $config, $action);
            break;
        case 'listOrganisationRoles':
            $content = $this->listOrganisationRoles($dataSettings, $config);
            break;
        case 'showStaffNovo':
            $content = $this->showStaffNovo($syslang, $scope, $dataSettings, $config);
            break;
        case 'listOrganisationPublications':
            $content = $this->listOrganisationPublications($dataSettings, $config, $action);
            break;
        case 'listOrganisationStudentPapers':
            $content = $this->listOrganisationStudentPapers($dataSettings, $config, $action);
            break;
        case 'showStudentPaperNovo':
            $content = $this->showStudentPaperNovo($syslang, $scope, $dataSettings, $config);
            break;
        case 'latestDissertationsStudentPapers':
            $content = $this->latestDissertationsStudentPapers($syslang, $scope, $dataSettings, $config);
            break;
        case 'listOrganisationProjects':
            $content = $this->listOrganisationProjects($dataSettings, $config, $action);
            break;
        case 'showProjectNovo':
            $content = $this->showProjectNovo($syslang, $scope, $dataSettings, $config);
            break;
        case 'showPublicationNovo':
            $content = $this->showPublicationNovo($dataSettings, $config, $action);
            break;
    }

    print $content;

}


function latestDissertationsStudentPapers($syslang, $scope, $dataSettings, $config)
{
    $scope = $dataSettings['scope'];
    $syslang = $dataSettings['syslang'];
    $tableStart = $dataSettings['tableStart'];
    
        $fieldArray = array("id","abstract","authorId","genre","documentTitle","authorName","supervisorName",
            "organisationName","organisationSourceId","publicationDateYear","language","keywordsUser","docType");
    
    $currentDate = gmDate("Y-m-d\TH:i:s\Z");
    
    $client = new Solarium\Client($config);

    $query = $client->createSelect();
    if($scope) {
        $queryToSet = 'docType:studentPaper';
        $i = 0;
        $queryToSet .= ' AND (';
        $scopeArray = explode(',', $scope);
        foreach($scopeArray as $key => $value) {
            if($i>0) $queryToSet .= ' OR ';
            $queryToSet .= 'organisationSourceId:' . array_pop(explode('__',$value));
            $i++;
        }
        $queryToSet .= ') AND publicationDateYear:[* TO ' . date('Y', strtotime('+1 years')) . ']';
    }
    $debug = $queryToSet;
    $query->setQuery($queryToSet);
    $query->setFields($fieldArray);
    $query->setStart($tableStart)->setRows(100);
    $sortArray = array(
        'publicationDateYear' => 'desc',
        'id' => 'desc',
        'documentTitle' => 'asc'
    );
    $query->addSorts($sortArray);
    $response1 = $client->select($query);
    
    $fieldArray = array("id","abstract","authorId","genre","documentTitle","authorName","supervisorName",
            "organisationName","organisationSourceId","publicationDateYear","publicationDateMonth","publicationDateDay","language","keywordsUser","docType");
    $query = $client->createSelect();
    if($scope) {
        $queryToSet = 'docType:publication';
        $i = 0;
        $queryToSet .= ' AND (';
        $scopeArray = explode(',', $scope);
        foreach($scopeArray as $key => $value) {
            if($i>0) $queryToSet .= ' OR ';
            $queryToSet .= 'organisationSourceId:' . array_shift(explode('__',$value));
            $i++;
        }
        $queryToSet .= ') AND publicationDateYear:[* TO ' . date('Y', strtotime('+1 years')) . ']';
    }
    $debug .= $queryToSet;
    $query->setQuery($queryToSet);
    $query->setFields($fieldArray);
    $query->setStart($tableStart)->setRows(100);
    $sortArray = array(
        'publicationDateYear' => 'desc',
        'publicationDateMonth' => 'desc',
        'publicationDateDay' => 'desc',
        'documentTitle' => 'asc'
    );
    $query->addSorts($sortArray);
    $response2 = $client->select($query);
     
    $numFound = $response1->getNumFound();
    
    $response = (object)array_merge((array)$response1, (array)$response2);
    //$GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_devlog', array('msg' => print_r($response,true), 'crdate' => time()));
    $i=0;
    foreach ($response1 as $document) {
        $data[$i] = array(
            "id" => $document->id,
            "abstract" => $document->abstract,
            "authorId" => $document->authorId,
            "genre" => $document->genre,
            "documentTitle" => $document->documentTitle,
            "authorName" => $document->authorName,
            "supervisorName" => $document->supervisorName,
            "organisationName" => $document->organisationName,
            "organisationSourceId" => $document->organisationSourceId,
            //"publicationDate" => $document->publicationDateYear,
            "language" => $document->language,
            "keywordsUser" => $document->keywordsUser,
            "docType" => $document->docType,
            "volume" => $document->volume
        );
        $i = $i + 2;
    }
    
    $i = 1;
    foreach ($response2 as $document) {
        $data[$i] = array(
            "id" => $document->id,
            "abstract" => $document->abstract,
            "authorId" => $document->authorId,
            "genre" => $document->genre,
            "documentTitle" => $document->documentTitle,
            "authorName" => $document->authorName,
            "supervisorName" => $document->supervisorName,
            "organisationName" => $document->organisationName,
            "organisationSourceId" => $document->organisationSourceId,
            "publicationDate" => $document->publicationDateYear . '-' . $document->publicationDateMonth . '-' . $document->publicationDateDay,
            "publicationDateMonth" => $document->publicationDateMonth,
            "publicationDateDay" => $document->publicationDateDay,
            "language" => $document->language,
            "keywordsUser" => $document->keywordsUser,
            "docType" => $document->docType,
            "volume" => $document->volume
        );
        $i = $i + 2;
    }
    if($data) ksort($data);
    $resArray = array('data' => $data, 'numFound' => $numFound, 'facet' => $facetResult, 'query' => $debug);
    return json_encode($resArray);
}


function listOrganisationStudentPapers($dataSettings, $config, $action)
{
    $facet = $dataSettings['facet'];
    $filterQuery = $dataSettings['query'];
    $scope = $dataSettings['scope'];
    $syslang = $dataSettings['syslang'];
    $tableStart = $dataSettings['tableStart'];
    
    if($action==='exportPublications') {
        $fieldArray = json_decode($tableFields, true);
    } else {
        $fieldArray = array("id","abstract","authorId","genre","documentTitle","authorName","supervisorName",
            "organisationName","organisationSourceId","publicationDateYear","language","keywordsUser","standardCategory");
    }
    
    $currentDate = gmDate("Y-m-d\TH:i:s\Z");
    
    $client = new Solarium\Client($config);

    $query = $client->createSelect();
       
    if($filterQuery) {
        $filterQuery = str_replace(" ","\ ",$filterQuery);
        $filterQuery = " AND ((documentTitle:*$filterQuery*) OR authorName:*$filterQuery*)";
    }
    
    //Organisation
    if($scope) {
        $queryToSet = 'docType:studentPaper';
        $scope = urldecode($scope);
        $i = 0;
        $queryToSet .= ' AND (';
        $scopeArray = explode(',', $scope);
        foreach($scopeArray as $key => $value) {
            if($i>0) $queryToSet .= ' OR ';
            $queryToSet .= 'organisationSourceId:' . array_pop(explode('__',$value));
            $i++;
        }

        $queryToSet .= ') AND publicationDateYear:[* TO ' . date('Y', strtotime('+1 years')) . ']';
    }
    
    $queryToSet .= $filterQuery;
    $query->setQuery($queryToSet);
    $query->setFields($fieldArray);
    $query->setStart($tableStart)->setRows(100);
    
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

        $query->addFilterQuery(array('key' => 0, 'query' => $facetQuery, 'tag'=>'outer'));
    }
    
    $sorting = "publicationDateYear";

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
            case 'publicationDateYear':
                $sortArray = array(
                    'publicationDateYear' => 'desc',
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
    
        
    foreach ($response as $document) {
        if($action==='exportPublications') {
            foreach($fieldArray as $field) {
                $data[$i][$field] = $document->$field;
            }
            $i++;
        } else {
            $data[] = array(
                "id" => $document->id,
                "abstract" => $document->abstract,
                "authorId" => $document->authorId,
                "genre" => $document->genre,
                "documentTitle" => $document->documentTitle,
                "authorName" => $document->authorName,
                "supervisorName" => $document->supervisorName,
                "organisationName" => $document->organisationName,
                "organisationSourceId" => $document->organisationSourceId,
                "publicationDateYear" => $document->publicationDateYear,
                "language" => $document->language,
                "keywordsUser" => $document->keywordsUser,
                "standardCategory" => $document->standardCategory,
                "volume" => $document->volume
            );
        }
    }
    $resArray = array('data' => $data, 'numFound' => $numFound, 'facet' => $facetResult, 'query' => $queryToSet);
    return json_encode($resArray);
}


function listOrganisationPublications($dataSettings, $config, $action)
{
    /*
     * $(".dropdown-menu li a").click(function(){
  var selText = $(this).text();
  $(this).parents('.btn-group').find('.dropdown-toggle').html(selText+' <span class="caret"></span>');
});
     */
    $facet = $dataSettings['facet'];
    $filterQuery = $dataSettings['query'];
    $scope = $dataSettings['scope'];
    $syslang = $dataSettings['syslang'];
    $tableLength = $dataSettings['tableLength'];
    $tableStart = $dataSettings['tableStart'];
    
    if($action==='exportPublications') {
        $fieldArray = json_decode($tableFields, true);
    } else {
        $fieldArray = array("articleNumber","authorName","bibliographicalNote","documentTitle",
            "electronicIsbn","electronicVersionAccessType","electronicVersionDoi","electronicVersionFileName","electronicVersionFileURL",
            "electronicVersionLicenseType","electronicVersionLink","electronicVersionMimeType","electronicVersionSize","electronicVersionTitle",
            "electronicVersionVersionType","hostPublicationTitle","id","journalTitle","journalNumber","numberOfPages","openAccessPermission","pages",
            "placeOfPublication","publicationDateYear","publicationDateMonth","publicationDateDay","portalUrl","publicationStatus","publicationType",
            "publisher","volume");
    }
    
    $currentDate = gmDate("Y-m-d\TH:i:s\Z");
    
    $client = new Solarium\Client($config);

    $query = $client->createSelect();
       
    if($filterQuery) {
        $filterQuery = str_replace(" ","\ ",$filterQuery);
        $filterQuery = " AND ((documentTitle:*$filterQuery*) OR authorName:*$filterQuery*)";
    }
    
    //Organisation
    if($scope) {
        $queryToSet = 'docType:publication';
        $scope = urldecode($scope);
        $i = 0;
        $queryToSet .= ' AND (';
        $scopeArray = explode(',', $scope);
        foreach($scopeArray as $key => $value) {
            if($i>0) $queryToSet .= ' OR ';
            $queryToSet .= 'organisationSourceId:' . array_shift(explode('__',$value)) . ' OR organisationTitleExact:' . str_replace(' ', '\ ', $value) ;
            $i++;
        }

        $queryToSet .= ')';
    }
    
    $queryToSet .= ' AND (workflow:Granskad OR workflow:Validated) AND publicationDateYear:[* TO ' . date('Y', strtotime('+1 years')) . ']' . $filterQuery;

    $query->setQuery($queryToSet);
    $query->setFields($fieldArray);
    if(!$tableStart) $tableStart = 0;
    if(!$tableLength) $tableLength = 50;
    $query->setStart($tableStart)->setRows($tableLength);
    
    // get the facetset component
    $facetSet = $query->getFacetSet();
    
    // create a facet field instance and set options
    $facetSet->createFacetField('type')->setField('publicationTypeExact');
    $facetSet->createFacetField('language')->setField('languageExact');
    $facetSet->createFacetField('year')->setField('publicationDateYear');
    $facetSet->createFacetField('electronicVersionAccessType')->setField('electronicVersionAccessType');

    if($facet) {
        $facetArray = json_decode($facet, true);
        $oldFacetKey = '';
        $facetQuery = '(';
        foreach($facetArray as $key => $value) {
            $facetTempArray = explode('###', $value);
            
            if($facetQuery !== '(') {
                if($facetTempArray[0] === $oldFacetKey) {
                    $facetQuery .= ' OR ';
                } else {
                    $facetQuery .= ') AND (';
                }
            }
            $facetQuery .= $facetTempArray[0] . ':"' . $facetTempArray[1] . '"';
            $oldFacetKey = $facetTempArray[0];
        }
        $facetQuery .= ')';
        $query->addFilterQuery(array('key' => 0, 'query' => $facetQuery, 'tag'=>'outer'));
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
    $facetType = $response->getFacetSet()->getFacet('type');
    if($syslang==="en") {
        $facetHeader = "Publication Type";
    } else {
        $facetHeader = "Publikationstyp";
    }
    foreach ($facetType as $value => $count) {
        if($count > 0) $facetResult["publicationTypeExact"][] = array($value, $count, $facetHeader);
    }

    $facetLanguage = $response->getFacetSet()->getFacet('language');
    if($syslang==="en") {
        $facetHeader = "Language";
    } else {
        $facetHeader = "Språk";
    }
    foreach ($facetLanguage as $value => $count) {
        if($count > 0) $facetResult["languageExact"][] = array($value, $count, $facetHeader);
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
    
    $facetElectronicVersionAccessType = $response->getFacetSet()->getFacet('electronicVersionAccessType');
    if($syslang==="en") {
        $facetHeader = "Full text";
    } else {
        $facetHeader = "Fulltext";
    }
    foreach ($facetElectronicVersionAccessType as $value => $count) {
        if($count > 0) $facetResult['electronicVersionAccessType'][] = array($value, $count, $facetHeader);
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
                "authorName" => $document->authorName,
                "bibliographicalNote" => "",//$document->bibliographicalNote,
                "documentTitle" => $document->documentTitle,
                "electronicIsbn" => $document->electronicIsbn,
                "electronicVersionAccessType" => $document->electronicVersionAccessType,
                "electronicVersionDoi" => $document->electronicVersionDoi,
                "electronicVersionFileName" => $document->electronicVersionFileName,
                "electronicVersionFileURL" => $document->electronicVersionFileURL,
                "electronicVersionLicenseType" => $document->electronicVersionLicenseType,
                "electronicVersionLink" => $document->electronicVersionLink,
                "electronicVersionMimeType" => $document->electronicVersionMimeType,
                "electronicVersionSize" => $document->electronicVersionSize,
                "electronicVersionTitle" => $document->electronicVersionTitle,
                "electronicVersionVersionType" => $document->electronicVersionVersionType,
                "hostPublicationTitle" => $document->hostPublicationTitle,
                "id" => $document->id,
                "journalTitle" => $document->journalTitle,
                "journalNumber" => $document->journalNumber,
                "numberOfPages" => $document->numberOfPages,
                "openAccessPermission" => $document->openAccessPermission,
                "pages" => $document->pages,
                "portalUrl" => $document->portalUrl,
                "publicationStatus" => $document->publicationStatus,
                "publicationType" => $document->publicationType,
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


function listOrganisation($dataSettings, $config)
{
    $filterQuery = $dataSettings['query'];
    $scope = $dataSettings['scope'];
    $syslang = $dataSettings['syslang'];

    $fieldArray = array("id", "homepage", "mailDelivery", "organisationCity", "organisationParent", 
        "organisationPhone", "organisationPostalAddress", "organisationSourceId", "organisationStreet", "organisationTitle");
    
    $client = new Solarium\Client($config);

    $query = $client->createSelect();
    
    if($filterQuery) {
        $filterQuery = str_replace(' ','\\ ',$filterQuery);
        $filterQuery = ' AND (mailDelivery:*' . $filterQuery . '* OR organisationCity:*' . $filterQuery . '* OR organisationPhone:*' . $filterQuery . '* OR organisationTitleExact:*' . $filterQuery . '* OR organisationStreet:*' . $filterQuery . '*)';
    }
      /*
       * "mailDelivery":["25"],
        "organisationCity":["Lund"],
        "organisationParent":["fcf07d05-9faa-4629-a7ad-efcdbdf13327"],
        "organisationPhone":["+46462227300"],
        "organisationPostalAddress":["Box 43$221 00$Lund"],
        "organisationSourceId":["v1000643"],
        "organisationStreet":["Sölvegatan 27"],
        "organisationTitle":"Astronomi",
       */
    if($scope) {
        $scope = urldecode($scope);
        $i = 0;
        $term .= ' AND (';
        $scopeArray = explode(',', $scope);
        foreach($scopeArray as $key => $value) {
            if($i>0) $term .= ' OR ';
            $term .= 'organisationParent:' . array_shift(explode('__',$value));
            $i++;
        }
        $term .= ')';
    }
    
    $queryToSet = 'docType:organisation' . $term . $filterQuery;

    $query->setQuery($queryToSet);
        
    $query->setFields($fieldArray);
        
    $query->setStart(0)->setRows(1000);
    
    $sortArray = array(
        'organisationTitle' => 'asc'
    );
    
    $query->addSorts($sortArray);

    $response = $client->select($query);
    
    $numFound = $response->getNumFound();
    
    foreach ($response as $document) {
        $data[] = array(
            "id" => $document->id,
            "homepage" => $document->homepage, 
            "mailDelivery" => $document->mailDelivery, 
            "organisationCity" => $document->organisationCity, 
            "organisationParent" => $document->organisationParent, 
            "organisationPhone" => $document->organisationPhone, 
            "organisationPostalAddress" => $document->organisationPostalAddress, 
            "organisationSourceId" => $document->organisationSourceId, 
            "organisationStreet" => $document->organisationStreet,
            "organisationTitle" => $document->organisationTitle,
        );
    }
    
    $resArray = array('data' => $data, 'numFound' => $numFound, 'query' => $queryToSet);
    
    return json_encode($resArray);
}


function listOrganisationStaff($dataSettings, $config, $action)
{
    $extraPeople = $dataSettings['extraPeople'];
    $facet = $dataSettings['facet'];
    $facetChoice = $dataSettings['facetChoice'];
    $filterQuery = $dataSettings['query'];
    $tableStart = $dataSettings['tableStart'];
    $scope = $dataSettings['scope'];
    $syslang = $dataSettings['syslang'];
    $vroles = $dataSettings['vroles'];
    $term = '';
    $data = array();
    
    $client = new Solarium\Client($config);
    
    $query = $client->createSelect();
    
    //Organisation
    if($scope) {
        $fieldArray = array("mailDelivery", "organisationSourceId","organisationTitle");
        $queryToSet = 'docType:organisation';
        $scope = urldecode($scope);
        $i = 0;
        $queryToSet .= ' AND (';
        $scopeArray = explode(',', $scope);
        foreach($scopeArray as $key => $value) {
            if($i>0) $queryToSet .= ' OR ';
            $queryToSet .= 'organisationSourceId:' . array_pop(explode('__',$value)) . ' OR organisationTitleExact:' . str_replace(' ', '\ ', $value) ;
            $i++;
        }
        if(count($scopeArray)===1) $organisationToShow = $scope;
        $queryToSet .= ')';
        $query->setQuery($queryToSet);
        $query->setFields($fieldArray);
        $response = $client->select($query);
        $scopeArray = array();
        foreach ($response as $document) {
            $mailDelivery = $document->mailDelivery[0];
            $scopeArray[] = $document->organisationSourceId[0];
            $organisationTitle = $document->organisationTitle;
        }
    }

    //Staff
    $fieldArray = array("email","firstName","guid","heritage","heritageName","heritage2","homepage","id","image","intro","lastName","lucrisPhoto","mobile",
        "organisationId","organisationHideOnWeb","organisationLeaveOfAbsence","organisationName","organisationPrimaryRole",
        "phone","portalUrl","primaryAffiliation","primaryVroleOu","primaryVroleTitle","primaryVroleOrgid","primaryVrolePhone","roomNumber","title", "uuid");
    
    
    if($scopeArray) {
        //$scope = explode(',', urldecode($scope));
        foreach($scopeArray as $key => $value) {
            if($term && $value) {
                $term .= ' OR ';
                
            }
            if($value) $term .= 'heritage2:*' . $value . '*';
        }
        
        //$term .= ' OR heritageName2:*' . str_replace('$',',',str_replace(' ', '\ ', strtolower($scope))) . '*';
        $term = ' AND (' . $term . ')';
    }
    
    $singleScope = '';
    if($action==='listSingleOrganisationStaff') $singleScope = $value;
    
    $photo = array();
    if($extraPeople) {
        $i = 0;
        $ii = 0;
        $email='';
        $term .= ' AND (';
        $extraPeople = json_decode(urldecode($extraPeople), true);

        foreach($extraPeople as $key => $value) {
            foreach($value['container']['el'] as $key1 => $value1) {
                if($key1==='email') {
                    if($i>0) {
                        $term .= ' OR ';
                    }
                    $email = $value1['vDEF'];
                    $term .= 'email:' . $email;
                    $i++;
                    //$extraPeopleLuSortArray[$value1['vDEF']] = $i;
                    
                    $data[$email]['email'] = $email;
                }
                if($key1==='name') {
                    $data[$email]['name'] = $value1['vDEF'];
                }
                if($key1==='title') {
                    $data[$email]['primaryVroleTitle'] = str_replace("\n","<br />", $value1['vDEF']);
                }
                if($key1==='organisation') {
                    $data[$email]['primaryVroleOu'] = $value1['vDEF'];
                }
                if($key1==='hideOrganisation') {
                    $data[$email]['hideOrganisation'] = $value1['vDEF'];
                }
                if($key1==='phone') {
                    $data[$email]['phone'] = $value1['vDEF'];
                }
                if($key1==='homepage') {
                    $data[$email]['homepage'] = $value1['vDEF'];
                }
                if($key1==='photo') {
                    $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('identifier','sys_file','uid='.intval($value1['vDEF']),'','','');
                    $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
                    if($row) $data[$email]['image'] = '/fileadmin' . $row['identifier'];
                }
                $data[$email]['order'] = $ii;
                if($filterQuery && !stristr($data[$email]['name'], $filterQuery)) {
                    unset($data[$email]);
                }
            }
            $ii++;
        }
        $term .= ')';
    }
    
    if($filterQuery) {
        $filterQuery = str_replace(" ","\ ",$filterQuery);
        $filterQuery = " AND (lastNameExact:*$filterQuery* OR firstNameExact:*$filterQuery* OR email:*$filterQuery*)";
    }
    
    $queryToSet = 'docType:staff AND (primaryAffiliation:employee OR primaryAffiliation:member)' . $term . $filterQuery;
    //$GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_devlog', array('msg' => $queryToSet, 'crdate' => time()));

    $query->setQuery($queryToSet);
    $query->setFields($fieldArray);
    $query->setStart($tableStart)->setRows(100);
         
    if($facetChoice) {
        $facetSet = $query->getFacetSet();
        $exclude = array('dt');
        $facetSet->createFacetField('firstLetter')->setField('firstLetterExact')->setExcludes($exclude);
        $facetSet->createFacetField('standard')->setField('standardCategory')->setExcludes($exclude);
        $facetSet->setLimit(1000);
        $facetSet->setSort(SORT_INDEX);
        
        if($facet) {
            $facetArray = json_decode($facet, true);
            //$GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_devlog', array('msg' => print_r($facetArray,true), 'crdate' => time()));
            foreach($facetArray as $key => $value) {
                $facetTempArray = explode('###', $value);
                if($facetQuery) {
                    $facetQuery .= ' AND ';
                }
                $facetQuery .= $facetTempArray[0] . ':"' . $facetTempArray[1] . '"'; 
            }
            if($facetTempArray[0] === 'firstLetterExact') {
                $tag = 'dt';
            } else {
                $tag = 'outer';
            }
            $query->addFilterQuery(array('key' => 0, 'query' => $facetQuery, 'tag' => $tag));
        }
    }
    
    if($scope) {
        $sortArray = array(
            'lastNameSort' => 'asc',
            'firstNameSort' => 'asc'
        );
        $query->addSorts($sortArray); 
    }

    $response = $client->select($query);

    $numFound = $response->getNumFound();
    
    if($facetChoice) {
        $facets = $response->getFacetSet()->getFacet('firstLetter');
        foreach ($facets as $value => $count) {
            if($count > 0) $facetResult['firstLetterExact'][] = array($value, $count);
        }
        $facets = $response->getFacetSet()->getFacet('standard');
        foreach ($facets as $value => $count) {
            if($count > 0) $facetResult['standardCategory'][] = array($value, $count);
        }
    }

    foreach ($response as $document) {
        $image = '';

        if($document->image) {
            $image = '/' . ltrim($document->image,'/');
        } else if($document->lucrisPhoto) {
            $image = $document->lucrisPhoto;
        }

        $email = $document->email[0];
        $data[$email]["email"] = $document->email;
        $data[$email]["firstName"] = mb_convert_case(strtolower($document->firstName), MB_CASE_TITLE, "UTF-8");
        $data[$email]["guid"] = $document->guid;
        $data[$email]["heritage2"] = $document->heritage2;
        $data[$email]["heritageName"] = $document->heritageName;
        $data[$email]["homepage"] = $document->homepage;
        $data[$email]["id"] = $document->guid;
        $data[$email]["image"] = $image;
        $data[$email]["imgtest"] = $document->image;
        $data[$email]["intro"] = $intro;
        $data[$email]["lastName"] = mb_convert_case(strtolower($document->lastName), MB_CASE_TITLE, "UTF-8");
        $data[$email]["mobile"] = $document->mobile;
        $data[$email]["name"] = mb_convert_case(strtolower($document->firstName), MB_CASE_TITLE, "UTF-8") . ' ' . mb_convert_case(strtolower($document->lastName), MB_CASE_TITLE, "UTF-8");
        $data[$email]["organisationId"] = $document->organisationId;
        $data[$email]["organisationName"] = $document->organisationName;
        $data[$email]["organisationHideOnWeb"] = $document->organisationHideOnWeb;
        $data[$email]["organisationLeaveOfAbsence"] = $document->organisationLeaveOfAbsence;
        $data[$email]["organisationPrimaryRole"] = $document->organisationPrimaryRole;
        $data[$email]["phone"] = $this->isInArray($email, $data, "phone", $document->phone);
        $data[$email]["portalUrl"] = $document->portalUrl;
        $data[$email]["primaryAffiliation"] = $document->primaryAffiliation;
        $data[$email]["primaryVroleOu"] = $this->isInArray($email, $data, "primaryVroleOu", $document->primaryVroleOu);
        $data[$email]["primaryVroleTitle"] = $this->isInArray($email, $data, "primaryVroleTitle", $document->primaryVroleTitle);
        $data[$email]["primaryVroleOrgid"] = $document->primaryVroleOrgid;
        $data[$email]["primaryVrolePhone"] = $document->primaryVrolePhone;
        $data[$email]["roomNumber"] = $this->fixRoomNumber($document->roomNumber);
        $data[$email]["title"] = $document->title;
        $data[$email]["uuid"] = $document->uuid;
    }

    if($extraPeople) {
        usort($data, function($a, $b) {
            return $a['order'] <=> $b['order'];
        });
    } /*else {
        usort($data, function($a, $b) {
            return $a['lastName'] <=> $b['lastName'];
        });
    }*/
    
    $resArray = array('data' => $data, 'facet' => $facetResult, 'singleScope' => $singleScope, 'mailDelivery' => $mailDelivery, 'organisationTitle' => $organisationTitle, 'numFound' => $numFound, 'query' => $queryToSet);
    
    return json_encode($resArray);
}


function isInArray($email, $myArray, $key, $value)
{
       // $GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_devlog', array('msg' => $email . ';' . $myArray[$email][$key] . ';' . $key . ';' . $value, 'crdate' => time()));
    if($myArray[$email][$key]) {
        return $myArray[$email][$key];
    } else {
        return $value;
    }
}


function listOrganisationRoles($dataSettings, $config)
{
    $filterQuery = $dataSettings['query'];
    $facet = $dataSettings['facet'];
    $syslang = $dataSettings['syslang'];
    $scope = $dataSettings['scope'];
    $vroles = $dataSettings['vroles'];
    
    $client = new Solarium\Client($config);
    
    $query = $client->createSelect();
    
    $fieldArray = array("firstName","lastName","title","phone","id","email","organisationName",
        "primaryAffiliation","homepage","image","lucrisPhoto","intro","roomNumber","mobile",
        "organisationId","organisationHideOnWeb","organisationLeaveOfAbsence","guid","uuid","heritage","heritageName",
        "primaryVroleOu","primaryVroleTitle","primaryVroleOrgid","primaryVrolePhone");
    
    if($filterQuery) {
        $filterQuery = str_replace(' ','\\ ',$filterQuery);
        $filterQuery = ' AND (name:*' . $filterQuery . '* OR phone:*' . $filterQuery . '* OR title:*' . $filterQuery . '* OR organisationName:*' . $filterQuery . '*)';
    }
    
    if($scope) {
        $scope = urldecode($scope);
        $i = 0;
        $term .= ' AND (';
        $scopeArray = explode(',', $scope);
        foreach($scopeArray as $key => $value) {
            if($i>0) $term .= ' OR ';
            $term .= 'heritage2:*' . array_pop(explode('__', $value)) . '*';
            $i++;
        }
        $term .= ')';
    }

    if($vroles) {
        $i = 0;
        $term .= ' AND (';
        $vrolesArray = explode(',', str_replace('$',',',$vroles));
        foreach($vrolesArray as $key => $value) {
            if($i>0) $term .= ' OR ';
            $term .= 'title:' . str_replace(' ', '\ ', strtolower($value));
            $i++;
        }
        $term .= ')';
    }
    //$GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_devlog', array('msg' => print_r($vrolesArray,true), 'crdate' => time()));
    $queryToSet = 'docType:staff AND (primaryAffiliation:employee OR primaryAffiliation:member)' . $term . $filterQuery;

    $query->setQuery($queryToSet);
    $query->setFields($fieldArray);
    $query->setStart(0)->setRows(1000);
    
    $facetSet = $query->getFacetSet();
    $facetSet->createFacetField('standard')->setField('standardCategory');
    
    if($facet) {
        $query->addFilterQuery(array('key' => 0, 'query' => 'standardCategory:'.$facet, 'tag'=>'inner'));
    }
    
    $sortArray = array(
        'lastNameExact' => 'asc',
        'firstNameExact' => 'asc'
    );
    
    $query->addSorts($sortArray);

    $response = $client->select($query);

    $numFound = $response->getNumFound();
    
    $facetStandard = $response->getFacetSet()->getFacet('standard');
    foreach ($facetStandard as $value => $count) {
        if($count > 0) $facetResult['standardCategory'][] = array($value, $count);
    }
    $ii=0;
    foreach ($response as $document) {
        $image = '';

        if($document->image) {
            $image = '/fileadmin' . $document->image;
        } else if($document->lucrisPhoto) {
            $image = $document->lucrisPhoto;
        }
        
        $title = $document->title;
        /*
         * title =
         * $vrolesArray = array('0' => bitr', 1 => pref
         */
        $mainKey=0;
        
        foreach($vrolesArray as $key => $value) {
            $tmpKey = array_search($value, $title);
            if($tmpKey) $mainKey = $tmpKey;
        }
        
        $data[$document->organisationName[$mainKey].array_search($document->title[$mainKey],$vrolesArray)] = array(           
            "firstName" => mb_convert_case(strtolower($document->firstName), MB_CASE_TITLE, "UTF-8"),
            "lastName" => mb_convert_case(strtolower($document->lastName), MB_CASE_TITLE, "UTF-8"),
            "title" => $this->getFromMainKey($document->title, $mainKey),
            "phone" => $this->getFromMainKey($document->phone, $mainKey),
            "id" => $document->guid,
            "email" => $this->getFromMainKey($document->email, $mainKey),
            "organisationName" => $this->getFromMainKey($document->organisationName, $mainKey),
            "organisationHideOnWeb" => $document->organisationHideOnWeb,
            "organisationLeaveOfAbsence" => $document->organisationLeaveOfAbsence,
            "primaryAffiliation" => $document->primaryAffiliation,
            "primaryVroleOu" => $document->primaryVroleOu,
            "primaryVroleTitle" => $document->primaryVroleTitle,
            "primaryVroleOrgid" => $document->primaryVroleOrgid,
            "primaryVrolePhone" => $document->primaryVrolePhone,
            "homepage" => $document->homepage,
            "image" => $image,
            "intro" => $intro,
            "roomNumber" => $this->fixRoomNumber($document->roomNumber),
            "mobile" => $this->getFromMainKey($document->mobile, $mainKey),
            "organisationId" => $document->organisationId,
            "guid" => $document->guid,
            "uuid" => $document->uuid,
            "imgtest" => $document->image,
            "heritage" => $document->heritage,
            "heritageName" => $document->heritageName
        );
        $ii++;
    }
    if($data) uksort($data, "strnatcasecmp");
    
    $resArray = array('data' => $data, 'numFound' => $numFound, 'query' => $queryToSet);
    
    return json_encode($resArray);
}


function getFromMainKey($value, $key)
{
    if($value[$key]) {
        return $value[$key];
    } else {
        return $value[0];
    }
}


function showProjectNovo($syslang, $scope, $dataSettings, $config)
{
    $fieldArray = array('id','curtailed','endDate','managingOrganisationId','managingOrganisationName','managingOrganisationType','organisationId',
            'organisationName','organisationType','participantId','participantName','participantOrganisationId','participantOrganisationName',
            'participantOrganisationType','participantRole','portalUrl','projectDescription','projectDescriptionType','projectStatus','projectTitle','projectType',
            'startDate','visibility');
    
    if($dataSettings['organisation']) $organisation = array_pop(explode('__', strtolower($dataSettings['organisation'])));
    $scope = $dataSettings['scope'];
    $sysLang = $dataSettings['sysLang'];
    $content = '';
    $staffData = array();
    $publicationsData = array();
    //$projectData = array();
       
    $client = new Solarium\Client($config);
    $query = $client->createSelect();
    
    //Staff
    $queryToSet1 = 'docType:project AND portalUrl:"' . $scope . '"';
    $query->setQuery($queryToSet1);
    $query->setFields($fieldArray);
    $projectResponse = $client->select($query);
    
    foreach ($projectResponse as $document) {    
        $id = $document->id;
        $docType = $document->docType;
        
        $mainKey=0;
        $i=0;
                
        $projectData[] = array(
            'id' => $document->id,
            'curtailed' => $document->curtailed,
            'endDate' => (string)$document->endDate,
            'managingOrganisationId' => $document->managingOrganisationId,
            'managingOrganisationName' => $document->managingOrganisationName,
            'managingOrganisationType' => $document->managingOrganisationType,
            'organisationId' => $this->fixArray($document->organisationId),
            'organisationName' => $this->fixArray($document->organisationName),
            'organisationType' => $this->fixArray($document->organisationType),
            'participantId' => $this->fixArray($document->participantId),
            'participantName' => $document->participantName,
            'participantOrganisationId' => $this->fixArray($document->participantOrganisationId),
            'participantOrganisationName' => $this->fixArray($document->participantOrganisationName),
            'participantOrganisationType' => $this->fixArray($document->participantOrganisationType),
            'participantRole' => $this->fixArray($document->participantRole),
            'projectDescription' => $document->projectDescription,
            'projectDescriptionType' => $document->projectDescriptionType,
            'projectStatus' => $document->projectStatus,
            'projectTitle' => $document->projectTitle,
            'projectType' => $document->projectType,
            'startDate' => (string)$document->startDate,
            'visibility' => $document->visibility,
        );
    }
    
    //Publications
    $fieldArray = array("articleNumber","authorName","bibliographicalNote","documentTitle",
            "electronicIsbn","electronicVersionAccessType","electronicVersionDoi","electronicVersionFileName","electronicVersionFileURL",
            "electronicVersionLicenseType","electronicVersionLink","electronicVersionMimeType","electronicVersionSize","electronicVersionTitle",
            "electronicVersionVersionType","hostPublicationTitle","id","journalTitle","journalNumber","numberOfPages","openAccessPermission",
            "pages","publicationType",
            "publicationDateYear","publicationDateMonth","publicationDateDay","placeOfPublication","publisher","volume");
    
    $queryToSet2 = 'docType:publication AND relatedProjectId:' . $id;
    $query->setStart(0)->setRows(1000);
    $query->setQuery($queryToSet2);
    $query->setFields($fieldArray);
    $sortArray = array(
        'publicationDateYear' => 'desc',
        'publicationDateMonth' => 'desc',
        'publicationDateDay' => 'desc',
        'documentTitle' => 'asc',
        'lastNameExact' => 'asc',
        'firstNameExact' => 'asc'
    );
    $query->addSorts($sortArray);
    $publicationsResponse = $client->select($query);

    foreach ($publicationsResponse as $document) {
        $publicationsData[] = array(
            "articleNumber" => $document->$articleNumber,
            "authorName" => ucwords(strtolower($this->fixArray($document->authorName))),
            "bibliographicalNote" => "",//$document->bibliographicalNote,
            "documentTitle" => $document->documentTitle,
            "electronicIsbn" => $document->electronicIsbn,
            "electronicVersionAccessType" => $document->electronicVersionAccessType,
            "electronicVersionDoi" => $document->electronicVersionDoi,
            "electronicVersionFileName" => $document->electronicVersionFileName,
            "electronicVersionFileURL" => $document->electronicVersionFileURL,
            "electronicVersionLicenseType" => $document->electronicVersionLicenseType,
            "electronicVersionLink" => $document->electronicVersionLink,
            "electronicVersionMimeType" => $document->electronicVersionMimeType,
            "electronicVersionSize" => $document->electronicVersionSize,
            "electronicVersionTitle" => $document->electronicVersionTitle,
            "electronicVersionVersionType" => $document->electronicVersionVersionType,
            "hostPublicationTitle" => $document->hostPublicationTitle,
            "id" => $document->id,
            "journalTitle" => $document->journalTitle,
            "journalNumber" => $document->journalNumber,
            "numberOfPages" => $document->numberOfPages,
            "openAccessPermission" => $document->openAccessPermission,
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
       
    $resArray = array('projectData' => $projectData, 'publicationsData' => $publicationsData, 'query' => $queryToSet1 . ';' . $queryToSet2);
    
    return json_encode($resArray);
}


function showStaffNovo($syslang, $scope, $dataSettings, $config)
{
    $fieldArray = array("coordinates","docType","email","firstName","guid","heritageName2","heritage2","homepage","id",
        "image","intro","lastName","lucrisPhoto","mailDelivery","mobile","organisationCity","organisationDescription",
        "organisationHideOnWeb","organisationId","organisationLeaveOfAbsence","organisationName","organisationPhone","organisationStreet",
        "organisationPostalAddress","phone","primaryAffiliation","profileInformationNovo","roomNumber","title","uuid");
    
    if($dataSettings['organisation']) $organisation = array_pop(explode('__', strtolower($dataSettings['organisation'])));
    $scope = $dataSettings['scope'];
    $sysLang = $dataSettings['sysLang'];
    $content = '';
    $staffData = array();
    $publicationsData = array();
    //$projectData = array();
       
    $client = new Solarium\Client($config);
    $query = $client->createSelect();
    
    //Staff
    $queryToSet1 = 'docType:staff AND (guid:' . $scope . ' OR uuid:' . $scope . ')';
    //$queryToSet1 = 'docType:staff AND portalUrl:' . $scope;
    $query->setQuery($queryToSet1);
    $query->setFields($fieldArray);
    $staffResponse = $client->select($query);
    
    //Publications
    $fieldArray = array("articleNumber","authorName","bibliographicalNote","documentTitle",
            "electronicIsbn","electronicVersionAccessType","electronicVersionDoi","electronicVersionFileName","electronicVersionFileURL",
            "electronicVersionLicenseType","electronicVersionLink","electronicVersionMimeType","electronicVersionSize","electronicVersionTitle",
            "electronicVersionVersionType","hostPublicationTitle","id","journalTitle","journalNumber","numberOfPages","openAccessPermission",
            "pages","portalUrl","publicationType","publicationDateYear","publicationDateMonth","publicationDateDay","placeOfPublication","publisher","volume");
    
    $queryToSet2 = 'docType:publication AND authorId:' . $scope;
    $queryToSet2 .= ' AND (workflow:Granskad OR workflow:Validated)';
    $query->setStart(0)->setRows(1000);
    $query->setQuery($queryToSet2);
    $query->setFields($fieldArray);
    $sortArray = array(
        'publicationDateYear' => 'desc',
        'publicationDateMonth' => 'desc',
        'publicationDateDay' => 'desc',
        'documentTitle' => 'asc',
        'lastNameExact' => 'asc',
        'firstNameExact' => 'asc'
    );
    $query->addSorts($sortArray);
    $publicationsResponse = $client->select($query);

    foreach ($staffResponse as $document) {    
        $id = $document->id;
        $docType = $document->docType;

        if($document->image) {
            $image = $document->image;
            if(!stristr($image, 'fileadmin')) $image = '/fileadmin' . $image;
            if(substr($image,0,1) !== '/') $image = '/' . $image;
        } else if($document->lucrisPhoto) {
            $image = $document->lucrisPhoto;
        }
        
        $mainKey=0;
        $i=0;
        $mailDelivery = array();
        $mobile = array();
        $organisationHideOnWeb = array();
        $organisationId = array();
        $organisationLeaveOfAbsence = array();
        $organisationName = array();
        $phone = array();
        $roomNumber = array();
        $title = array();
                
        if($document->organisationId) {
            //$heritage2Array = json_decode($document->heritage2, true);
            //$GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_devlog', array('msg' => print_r($document->organisationId,true), 'crdate' => time()));
            foreach($document->organisationId as $key => $value) {
                //if(array_search($organisation, $value)!==false) {
                    $mailDelivery[] = $document->mailDelivery[$i];
                    $mobile[] = $document->mobile[$i];
                    $organisationDescription[] = $document->organisationDescription[$i];
                    $organisationHideOnWeb[] = $document->organisationHideOnWeb[$i];
                    $organisationId[] = $document->organisationId[$i];
                    $organisationLeaveOfAbsence[] = $document->organisationLeaveOfAbsence[$i];
                    $organisationName[] = $document->organisationName[$i];
                    $phone[] = $document->phone[$i];
                    $roomNumber[] = $document->roomNumber[$i];
                    $title[] = $document->title[$i];
                //}
                $i++;
            }
        } 
        
        /*if(!$organisationId) {
            $mailDelivery = $document->mailDelivery;
            $mobile = $document->mobile;
            $organisationId = $document->organisationId;
            $organisationLeaveOfAbsence = $document->organisationLeaveOfAbsence;
            $organisationName = $document->organisationName;
            $phone = $document->phone;
            $roomNumber = $document->roomNumber;
            $title = $document->title;
        }*/

        $staffData[] = array(
            "coordinates" => $this->fixArray($document->coordinates),
            "email" => $document->email[0],
            "firstName" => $document->firstName,
            "guid" => $document->guid,
            "homepage" => $document->homepage,
            "image" => $image,
            "intro" => $intro,
            "lastName" => $document->lastName,
            "mailDelivery" => $mailDelivery,
            "mailDeliverySame" => $this->checkSame($mailDelivery),
            "mobile" => $mobile,
            "mobileSame" => $this->checkSame($mobile),
            "organisationDescription" => $document->organisationDescription,
            "organisationName" => $organisationName,
            "organisationId" => $organisationId,
            "organisationLeaveOfAbsence" => $organisationLeaveOfAbsence,
            "organisationHideOnWeb" => $organisationHideOnWeb,
            "organisationPhone" => $document->organisationPhone,
            "organisationStreet" => $document->organisationStreet,
            "organisationStreetSame" => $this->checkSame($document->organisationStreet),
            "organisationCity" => $document->organisationCity,
            "organisationPostalAddress" => $document->organisationPostalAddress,
            "organisationPostalAddressSame" => $this->checkSame($document->organisationPostalAddress),
            "primaryAffiliation" => $document->primaryAffiliation,
            "title" => $title,
            "phone" => $phone,
            "phoneSame" => $this->checkSame($phone),
            "profileInformation" => $document->profileInformationNovo,
            "roomNumber" => $roomNumber,
            "roomNumberSame" => $this->checkSame($roomNumber),
            "uuid" => $document->uuid,
        );
    }
    
    foreach ($publicationsResponse as $document) {
        $publicationsData[] = array(
            "articleNumber" => $document->$articleNumber,
            "authorName" => ucwords(strtolower($this->fixArray($document->authorName))),
            "bibliographicalNote" => "",//$document->bibliographicalNote,
            "documentTitle" => $document->documentTitle,
            "electronicIsbn" => $document->electronicIsbn,
            "electronicVersionAccessType" => $document->electronicVersionAccessType,
            "electronicVersionDoi" => $document->electronicVersionDoi,
            "electronicVersionFileName" => $document->electronicVersionFileName,
            "electronicVersionFileURL" => $document->electronicVersionFileURL,
            "electronicVersionLicenseType" => $document->electronicVersionLicenseType,
            "electronicVersionLink" => $document->electronicVersionLink,
            "electronicVersionMimeType" => $document->electronicVersionMimeType,
            "electronicVersionSize" => $document->electronicVersionSize,
            "electronicVersionTitle" => $document->electronicVersionTitle,
            "electronicVersionVersionType" => $document->electronicVersionVersionType,
            "hostPublicationTitle" => $document->hostPublicationTitle,
            "id" => $document->id,
            "journalTitle" => $document->journalTitle,
            "journalNumber" => $document->journalNumber,
            "numberOfPages" => $document->numberOfPages,
            "openAccessPermission" => $document->openAccessPermission,
            "pages" => $document->pages,
            "portalUrl" => $document->portalUrl,
            "publicationType" => $this->fixArray($document->publicationType),
            "publicationDateYear" => $document->publicationDateYear,
            "publicationDateMonth" => $document->publicationDateMonth,
            "publicationDateDay" => $document->publicationDateDay,
            "placeOfPublication" => $document->placeOfPublication,
            "publisher" => $document->publisher,
            "volume" => $document->volume,
        );
    }
       
    $resArray = array('staffData' => $staffData, 'publicationsData' => $publicationsData, 'publicationDetailUrl' => $publicationDetailUrl, 'query' => $queryToSet1 . ';' . $queryToSet2);
    
    return json_encode($resArray);
}


function checkSame($allvalues)
{
    if ($allvalues && count(array_unique($allvalues)) === 1) {
        return true;
    }
    return false;
}


function listCourses($dataSettings, $config)
{
    $round = $dataSettings['round'];
    
    $syslang = $dataSettings['syslang'];
    
    $currentDate = gmDate("Y-m-d\TH:i:s\Z");
    
    $fieldArray = array("id", "courseCode", "coursePlace", "courseTitle", "credit");
    
    $client = new Solarium\Client($config);

    $query = $client->createSelect();
      
    $queryToSet = "docType:course AND roundId:$round";

    $query->setQuery($queryToSet);
        
    $query->setFields($fieldArray);
        
    $query->setStart(0)->setRows(1000);
    
    $sortArray = array(
        'courseTitle' => 'asc'
    );
    
    $query->addSorts($sortArray);

    $response = $client->select($query);
    
    $numFound = $response->getNumFound();
    
    foreach ($response as $document) {
        $data[] = array(
            "courseCode" => $document->courseCode,
            "coursePlace" => $document->coursePlace,
            "courseTitle" => $document->courseTitle,
            "credit" => $document->credit,
            "id" => $document->id
        );
    }
    
    $resArray = array('data' => $data, 'numFound' => $numFound, 'query' => $queryToSet);
    
    return json_encode($resArray);
}


function showCourse($dataSettings, $config)
{
    $scope = $dataSettings['scope'];
    $roundId = $dataSettings['roundId'];
    
    $syslang = $dataSettings['syslang'];
    
    $fieldArray = array("department","courseCode","courseTitle","credit","homepage","ratingScale","courseForkunKrav","courseSlutDatum","courseSlutDatum","coursePace");
    
    $client = new Solarium\Client($config);

    $query = $client->createSelect();
    
    $queryToSet = "docType:course AND courseCode:$scope AND roundId:$roundId";

    $query->setQuery($queryToSet);
        
    $query->setFields($fieldArray);
    
    $response = $client->select($query);
        
    foreach ($response as $document) {
        $data = array(
            "courseCode" => $document->courseCode,
            "courseForkunKrav" => $document->courseForkunKrav,
            "coursePace" => $document->coursePace,
            "courseSlutDatum" => $document->courseSlutDatum,
            "courseSlutDatum" => $document->courseSlutDatum,
            "courseTitle" => $document->courseTitle,
            "credit" => $document->credit,
            "department" => $document->department,
            "homepage" => $document->homepage,
            "ratingScale" => $document->ratingScale
        );
    }
    
    $resArray = array('data' => $data, 'query' => $queryToSet);
    
    return json_encode($resArray);
}


function listStatistics($dataSettings, $config)
{
    $program = addslashes($dataSettings['program']);
    $round = addslashes($dataSettings['round']);
    $syslang = addslashes($dataSettings['syslang']);
    $currentDate = gmDate("Y-m-d\TH:i:s\Z");
    $fieldArray = array("id","statTermin","statType","statTitle","statCode","statVal1","statVal2","statApplicants");
    
    $client = new Solarium\Client($config);

    $query = $client->createSelect();
      
    $queryToSet = "docType:stat AND statType:Program AND statFaculty:t AND statTermin:$round";
    if($program) {
        $queryToSet .= " AND statProgramCode:$program";
    }
    $query->setQuery($queryToSet);
        
    $query->setFields($fieldArray);
        
    $query->setStart(0)->setRows(1000);
    
    $sortArray = array(
        'endDate' => 'desc',
        'jobTitle' => 'asc'
    );
    
    $query->addSorts($sortArray);

    $response = $client->select($query);
    
    $numFound = $response->getNumFound();
    
    foreach ($response as $document) {
        $data[] = array(
            "id" => $document->id,
            "statTermin" => $document->statTermin,
            "statType" => $document->statType,
            "statTitle" => $document->statTitle,
            "statCode" => $document->statCode,
            "statVal1" => $document->statVal1,
            "statVal2" => $document->statVal2,
            "statApplicants" => $document->statApplicants
        );
    }
    
    $resArray = array('data' => $data, 'numFound' => $numFound, 'query' => $queryToSet);
    
    return json_encode($resArray);
}


function listJobs($dataSettings, $config)
{
    $syslang = $dataSettings['syslang'];
    
    $currentDate = gmDate("Y-m-d\TH:i:s\Z");
    
    $fieldArray = array("id", "endDate", "jobType", "refNr", "jobTitle");
    
    $client = new Solarium\Client($config);

    $query = $client->createSelect();
      
    $queryToSet = "docType:job AND endDate:[$currentDate TO *] AND hidden:0";

    $query->setQuery($queryToSet);
        
    $query->setFields($fieldArray);
        
    $query->setStart(0)->setRows(1000);
    
    $sortArray = array(
        'endDate' => 'desc',
        'jobTitle' => 'asc'
    );
    
    $query->addSorts($sortArray);

    $response = $client->select($query);
    
    $numFound = $response->getNumFound();
    
    foreach ($response as $document) {
        $data[] = array(
            "endDate" => $document->endDate,
            "id" => $document->id,
            "jobTitle" => $document->jobTitle,
            "jobType" => $document->jobType,
            "refNr" => $document->refNr
        );
    }
    
    $resArray = array('data' => $data, 'numFound' => $numFound, 'query' => $queryToSet);
    
    return json_encode($resArray);
}


function showJob($dataSettings, $config)
{
    $scope = $dataSettings['scope'];
    
    if($scope) $scope = str_replace('-','\/',strtoupper($scope));
    
    $syslang = $dataSettings['syslang'];
    
    $fieldArray = array("abstract","endDate","jobPositionContact","jobUnionRepresentative","jobTitle","jobType","loginAndApplyURI","published");
    
    $client = new Solarium\Client($config);

    $query = $client->createSelect();
    
    $queryToSet = "docType:job AND refNr:$scope";

    $query->setQuery($queryToSet);
        
    $query->setFields($fieldArray);
    
    $response = $client->select($query);
        
    foreach ($response as $document) {
        $data = array(
            "abstract" => $document->abstract,
            "endDate" => $document->endDate,
            "id" => $document->id,
            "jobPositionContact" => $document->jobPositionContact,
            "jobUnionRepresentative" => $document->jobUnionRepresentative,
            "jobTitle" => $document->jobTitle,
            "jobType" => $document->jobType,
            "loginAndApplyURI" => $document->loginAndApplyURI,
            "published" => $document->published
        );
    }
    
    $resArray = array('data' => $data, 'query' => $queryToSet);
    
    return json_encode($resArray);
}


function listCompare($dataSettings, $config)
{
    $scope = $dataSettings['scope'];
    $syslang = $dataSettings['syslang'];
    $roundId = $dataSettings['roundId'];
    if(!$roundId) $roundId = $dataSettings['globalRoundId'];
    $term = '';
    $prevId = '';
    $i=0;
    
    $fieldArray = array("id","courseCode","courseSelection","courseSelectionSort","courseType","courseTitle","courseYear","credit","homepage",
        "optional","programCode","programDirection","programDirectionGeneral","programTitle","ratingScale");
    
    $client = new Solarium\Client($config);

    $query = $client->createSelect();
    
    if($scope) {
        $scope = explode(',',urldecode($scope));
        foreach($scope as $key => $value) {
            if($term) {
                $term .= ' OR ';
            }
            $term .= "programCode:$value";
        }
        $term = " AND ($term) ";
    }
    
    $queryToSet = "docType:course AND courseYear:* AND roundId:$roundId  AND -courseTitle:Kandidatarbete*$term";

    $query->setQuery($queryToSet);
        
    $query->setFields($fieldArray);
        
    $query->setStart(0)->setRows(100000);
    
    $sortArray = array(
        'programTitle' => 'asc',
        'courseYear' => 'asc',
        'courseSelectionSort' => 'asc',
        'courseTitle' => 'asc'
    );
    
    $query->addSorts($sortArray);

    $response = $client->select($query);
    
    $numFound = $response->getNumFound();
    
    /*
     * "courseCode":"vtvm01",
        "courseTitle":"Examensarbete i trafikteknik",
        "courseType":"EXAMENSARBETE",
        "optional":"valfri",
        "programDirection":"Allmän inriktning V",
        "programDirectionGeneral":1},
     */
    
    foreach ($response as $document) {
        $data[$document->programTitle][$document->courseYear][$document->courseSelectionSort.$document->courseSelection][$document->courseCode] = array(
            "courseCode" => $document->courseCode,
            "courseType" => $document->courseType,
            "courseTitle" => $document->courseTitle,
            "credit" => $document->credit,
            "id" => $document->id,
            "prevId" => $prevId,
            "optional" => $document->optional,
            "programDirectionGeneral" => $document->programDirectionGeneral,
            "ratingScale" => $document->ratingScale
        );
        if($prevKey) {
            $pArray = explode('|', $prevKey);
            $data[$pArray[0]][$pArray[1]][$pArray[2]][$pArray[3]]["nextId"] = $document->id;
        }
        $prevKey = implode('|', array($document->programTitle,$document->courseYear,$document->courseSelectionSort.$document->courseSelection,$document->courseCode));
        $prevId = $document->id;
        $i++;
    }

    $resArray = array('data' => $data, 'numFound' => $numFound, 'query' => $queryToSet);
    
    return json_encode($resArray);
}


function getCourseType($courseType,$optional,$programDirection,$programDirectionGeneral)
{
    if($optional==='obligatorisk') {
        $output = 'Obligatoriska kurser';
    } else if($optional==='alternativ_obligatorisk') {
        $output = 'Alternativobligatoriska kurser';
    } else if($programDirectionGeneral==0) {
        $output = 'Specialisering - ' . $programDirection;
    } else if($courseType==='EXAMENSARBETE') {
        $output = 'Examensarbeten';
    } else if($optional === 'externt_valfri') {
        $output = 'Externt valfria kurser';
    } else if($optional === 'valfri') {
        $output = 'Valfria kurser';
    }
    return $output;
    //$output = Examensarbeten / Externt valfria kurser / Valfria kurser / Specialisering p - Processdesign / Obligatoriska kurser / Alternativobligatoriska kurser
}


function showCompare($dataSettings, $config)
{
    $scope = $dataSettings['scope'];
    $syslang = $dataSettings['syslang'];
    
    $fieldArray = array("abstract","courseCode","courseTitle","courseYear","credit","homepage","id",
        "optional","programCode","programDirection","programTitle","ratingScale");
    
    $client = new Solarium\Client($config);

    $query = $client->createSelect();
    
    $queryToSet = "docType:course AND id:$scope";

    $query->setQuery($queryToSet);
        
    $query->setFields($fieldArray);
    
    $response = $client->select($query);
        
    foreach ($response as $document) {
        $data[] = array(
            "abstract" => $document->abstract,
            "courseCode" => $document->courseCode,
            "courseTitle" => $document->courseTitle,
            "courseYear" => $document->courseYear,
            "credit" => $document->credit,
            "id" => $document->id,
            "programTitle" => $document->programTitle,
            "ratingScale" => $document->ratingScale
        );
    }
    
    $resArray = array('data' => $data, 'query' => $queryToSet);
    
    return json_encode($resArray);
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
        $term = array_pop(explode(',', htmlspecialchars_decode(urldecode($term))));
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
        $fieldArray = array("articleNumber","authorName","bibliographicalNote","documentTitle",
            "electronicIsbn","electronicVersionAccessType","electronicVersionDoi","electronicVersionFileName","electronicVersionFileURL",
            "electronicVersionLicenseType","electronicVersionLink","electronicVersionMimeType","electronicVersionSize","electronicVersionTitle",
            "electronicVersionVersionType","hostPublicationTitle","id","journalTitle","journalNumber","numberOfPages","openAccessPermission","pages","publicationType",
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
        $keyword = ' AND keyword:' . str_replace(' ', '\\ ', urldecode($keyword));
    }
    
    if($scope) {
        //$debugQuery = urldecode($scope);
        $scope = json_decode(urldecode($scope),true);
        //var_dump($scope);

        foreach($scope as $key => $value) {
            /*if($term) {
                $term .= " OR ";
            }*/
            if($key === "fe_groups") {
                $term .= " AND (organisationSourceId:" . $this->getLucrisId($value[0], $config) . ')';
            } else if($key === "fe_users") {
                $term .= " AND (authorId:" . implode(' OR authorId:', $value) . ')';
            } else if($key === "projects") {
                $term .= " AND (relatedProjectId:" . implode(' OR relatedProjectId:', $value) . ')';
            }
        }
        //$term = " AND ($term) ";
    }
//query: "docType:publication AND (workflow:Granskad OR workflow:Validated) AND -lth_solr_hide_p73484_i:1 AND publicationDateYear:[* TO 2020] AND (authorId:abb52c25-085c-4656-b97b-cadd7f262168) "

    $listComingDissertations = '';
    if($action==='listComingDissertations') {
        $listComingDissertations = ' AND awardedDate:[' . $currentDate . ' TO *]';
    }
    $queryToSet = 'docType:publication AND (workflow:Granskad OR workflow:Validated)' . $listComingDissertations . ' AND -' . $hideVal . ':1 AND publicationDateYear:[* TO ' . date('Y', strtotime('+1 years')) . ']' . $term . $keyword . $publicationSelection . $filterQuery;

    $query->setQuery($queryToSet);
    $query->setFields($fieldArray);
    if(!$tableStart) $tableStart = 0;
    if(!$tableLength) $tableLength = 10;
    $query->setStart($tableStart)->setRows($tableLength);
    
    // get the facetset component
    $facetSet = $query->getFacetSet();
    
    // create a facet field instance and set options
    $facetSet->createFacetField('standard')->setField('standardCategory');
    $facetSet->createFacetField('language')->setField('language');
    $facetSet->createFacetField('year')->setField('publicationDateYear');
    $facetSet->createFacetField('electronicVersionAccessType')->setField('electronicVersionAccessType');

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
    
    $facetElectronicVersionAccessType = $response->getFacetSet()->getFacet('electronicVersionAccessType');
    if($syslang==="en") {
        $facetHeader = "Full text";
    } else {
        $facetHeader = "Fulltext";
    }
    foreach ($facetElectronicVersionAccessType as $value => $count) {
        if($count > 0) $facetResult['electronicVersionAccessType'][] = array($value, $count, $facetHeader);
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
                "bibliographicalNote" => "",//$document->bibliographicalNote,
                "documentTitle" => $document->documentTitle,
                "electronicIsbn" => $document->electronicIsbn,
                "electronicVersionAccessType" => $document->electronicVersionAccessType,
                "electronicVersionDoi" => $document->electronicVersionDoi,
                "electronicVersionFileName" => $document->electronicVersionFileName,
                "electronicVersionFileURL" => $document->electronicVersionFileURL,
                "electronicVersionLicenseType" => $document->electronicVersionLicenseType,
                "electronicVersionLink" => $document->electronicVersionLink,
                "electronicVersionMimeType" => $document->electronicVersionMimeType,
                "electronicVersionSize" => $document->electronicVersionSize,
                "electronicVersionTitle" => $document->electronicVersionTitle,
                "electronicVersionVersionType" => $document->electronicVersionVersionType,
                "hostPublicationTitle" => $document->hostPublicationTitle,
                "id" => $document->id,
                "journalTitle" => $document->journalTitle,
                "journalNumber" => $document->journalNumber,
                "numberOfPages" => $document->numberOfPages,
                "openAccessPermission" => $document->openAccessPermission,
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


function getLucrisId($orgId, $config)
{
    if(strlen($orgId) < 20) {
        $client = new Solarium\Client($config);
        $query = $client->createSelect();
        $queryToSet = "docType:organisation AND organisationSourceId:" . $orgId;
        $query->setQuery($queryToSet);   
        $query->setFields(array("id"));
        $response = $client->select($query);    
        foreach ($response as $document) {
            $orgId = $document->id;
        }
    }
    return $orgId;
}


function showPublication($response, $scope, $syslang, $config)
{
    $fieldArray = array("abstract","additionalLink","authorExternal","authorId","authorName","authorOrganisationId","authorReverseName","authorReverseNameShort",
        "bibtex","cite","documentTitle","doi","edition","electronicIsbn","electronicVersionAccessType","electronicVersionDoi","electronicVersionFileName","electronicVersionFileURL",
        "electronicVersionLicenseType","electronicVersionLink","electronicVersionMimeType","electronicVersionSize","electronicVersionTitle",
        "electronicVersionVersionType","electronicIsbns","endDate","externalOrganisations","eventCity","eventCountry","eventName","eventLink","eventType",
        "id","hostPublicationTitle","issn","journalTitle","journalNumber","keywords_uka","keywords_user","language","numberOfPages","openAccessPermission","organisationName",
        "organisationId","organisationSourceId","pages","peerReview","placeOfPublication","printIsbns","publicationDateYear","publicationDateMonth",
        "publicationDateDay","publicationType","publicationTypeUri","publisher","publicationStatus","standard_category_en","startDate","supervisorId","supervisorName",
        "supervisorOrganisationId","supervisorOrganisationName","supervisorPersonRole","title","volume");
    
    if(!$response) {
        $client = new Solarium\Client($config);

        $query = $client->createSelect();
        
        if($scope) {
            $term='';
            $scope = json_decode(urldecode($scope),true);
            $uuid = $scope['publication'][0];
            $queryToSet = 'docType:publication AND id:'.$uuid;
            //$GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_devlog', array('msg' => print_r($scope, true), 'crdate' => time()));
            foreach($scope as $key => $value) {
                if($key==='fe_groups') {
                    foreach($value as $key1 => $value1) {
                        if($term) {
                            $term .= " OR ";
                        }
                        $term .= 'organisationSourceId:' . $value1;
                    }
                }
                if($key==='fe_users') {
                    foreach($value as $key1 => $value1) {
                        if($term) {
                            $term .= " OR ";
                        }
                        $term .= 'authorId:' . $value1;
                    }
                }
            }
            if($term) $queryToSet .= ' AND (' . $term . ')';
            //$organisation = $this->getLucrisId($scope['fe_groups'][0],$config);
        }
        
        $queryToSet .= ' AND (workflow:Granskad OR workflow:Validated)';

        $query->setQuery($queryToSet);
        
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
        $abstract = $this->fixArray($document->abstract);
        $additionalLink = $document->additionalLink;
        $authorNameArray = $document->authorName;
        $authorFirstNameArray = $document->authorFirstName;
        $authorLastNameArray = $document->authorLastName;
        $authorExternalArray = $document->authorExternal;
        $authorOrganisationId = $document->authorOrganisationId;
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
            $i++;
        }
        if($document->organisationName) {
            $organisationName = $document->organisationName;
            $organisationId = $document->organisationId;
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
        $bibtex = $document->bibtex;
        $cite = $document->cite;
        $doi = $document->doi;
        $electronicIsbn = $document->electronicIsbn;
        $electronicVersionAccessType = $document->electronicVersionAccessType;
        $electronicVersionDoi = $document->electronicVersionDoi;
        $electronicVersionFileName = $document->electronicVersionFileName;
        $electronicVersionFileURL = $document->electronicVersionFileURL;
        $electronicVersionLicenseType = $document->electronicVersionLicenseType;
        $electronicVersionLink = $document->electronicVersionLink;
        $electronicVersionMimeType = $document->electronicVersionMimeType;
        $electronicVersionSize = $document->electronicVersionSize;
        $electronicVersionTitle = $document->electronicVersionTitle;
        $electronicVersionVersionType = $document->electronicVersionVersionType;
        $edition = $document->edition;
        $endDate = $document->endDate;
        $eventName = $document->eventName;
        $eventLink = $document->eventLink;
        $eventType = $document->eventType;
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
        $openAccessPermission = $document->openAccessPermission;
        $pages = $document->pages;
        $peerReview = $document->peerReview;
        $printIsbns = $document->printIsbns;
        $publicationDateYear = $document->publicationDateYear;
        $publicationDateMonth = $document->publicationDateMonth;
        $publicationDateDay = $document->publicationDateDay;
        $publicationStatus = $document->publicationStatus;
        $placeOfPublication = $document->placeOfPublication;
        $publisher = $document->publisher;
        $publicationType = $document->publicationType;
        $publicationTypeUri = $document->publicationTypeUri;
        $startDate = $document->startDate;
        $supervisors = $document->supervisorName;
        //Novo begin
        $supervisorId = $document->supervisorId;
        $supervisorName = $document->supervisorName;
        $supervisorOrganisationId = $document->supervisorOrganisationId;
        $supervisorOrganisationName = $document->supervisorOrganisationName;
        $supervisorPersonRole = $document->supervisorPersonRole;
        //Novo ends
        $type = $document->type;
        $volume = $document->volume;
        
        $data = array(
            'abstract' => $abstract,
            'additionalLink' => $additionalLink,
            'authorExternal' => $authorExternal,
            'authorId' => $document->authorId,
            'authorName' => $document->authorName,
            'authorOrganisationId' => $authorOrganisationId,
            'authorReverseName' => rawurlencode($authorReverseName),
            'authorReverseNameShort' => rawurlencode(str_replace("$", ", ", $this->str_lreplace("$", " and ", $authorReverseNameShort))),
            'bibtex' => $bibtex,
            'cite' => $cite,
            'doi' => $doi,
            'edition' => $edition,
            'electronicIsbns' => $electronicIsbns,
            'electronicIsbn' => $electronicIsbn,
            'electronicVersionAccessType' => $electronicVersionAccessType,
            'electronicVersionDoi' => $electronicVersionDoi,
            'electronicVersionFileName' => $electronicVersionFileName,
            'electronicVersionFileURL' => $electronicVersionFileURL,
            'electronicVersionLicenseType' => $electronicVersionLicenseType,
            'electronicVersionLink' => $electronicVersionLink,
            'electronicVersionMimeType' => $electronicVersionMimeType,
            'electronicVersionSize' => $electronicVersionSize,
            'electronicVersionTitle' => $electronicVersionTitle,
            'electronicVersionVersionType' => $electronicVersionVersionType,
            'endDate' => $endDate,
            'eventName' => $eventName,
            'eventLink' => $eventLink,
            'eventType' => $eventType,
            'eventCity' => $eventCity,
            'eventCountry' => $eventCountry,
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
            'openAccessPermission' => $openAccessPermission,
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
            'startDate' => $startDate,
            'supervisors' => $supervisors,
            'supervisorId' => $document->supervisorId,
            'supervisorName' => $document->supervisorName,
            'supervisorOrganisationId' => $document->supervisorOrganisationId,
            'supervisorOrganisationName' => $document->supervisorOrganisationName,
            'supervisorPersonRole' => $document->supervisorPersonRole,
            'title' => $title,
            'volume' => $volume,
        );

    }
    
    $resArray = array('data' => $data, 'title' => $title, 'query' => $queryToSet);
    
    return json_encode($resArray);
}


function showPublicationNovo($dataSettings, $config, $action)
{
    $fieldArray = array("abstract","additionalLink","authorExternal","authorId","authorName","authorOrganisationId","authorReverseName","authorReverseNameShort",
        "bibliographicalNote","bibtex","cite","documentTitle","doi","edition","electronicIsbn","electronicVersionAccessType","electronicVersionDoi",
        "electronicVersionFileName","electronicVersionFileURL",
        "electronicVersionLicenseType","electronicVersionLink","electronicVersionMimeType","electronicVersionSize","electronicVersionTitle",
        "electronicVersionVersionType","electronicIsbns","endDate","externalOrganisations","eventCity","eventCountry","eventName","eventLink","eventType",
        "id","hostPublicationTitle","issn","isbn2","journalTitle","journalNumber","keyword","keywordType","language","numberOfPages","openAccessPermission","organisationName",
        "organisationId","organisationSourceId","pages","peerReview","placeOfPublication","printIsbns","publicationDateYear","publicationDateMonth",
        "publicationDateDay","publicationType","publicationTypeUri","publisher","publicationStatus","standard_category_en","startDate","supervisorId","supervisorName",
        "supervisorOrganisationId","supervisorOrganisationName","supervisorPersonRole","title","volume");
    
    $scope = $dataSettings['scope'];
    
    $client = new Solarium\Client($config);

    $query = $client->createSelect();

    if($scope) {
        $queryToSet = 'docType:publication AND portalUrl:*' . $scope . '*';
        //(id:7658dc68-1b43-4510-83ea-cd5f28b70b64)
    }

    //$queryToSet .= ' AND (workflow:Granskad OR workflow:Validated)';

    $query->setQuery($queryToSet);

    $query->setFields($fieldArray);

    $response = $client->select($query);

    $content = '';
        
    $organisationNameHolder = 'organisationName_' . $syslang;
    $publicationTypeHolder = 'publicationType_' . $syslang;
    $languageHolder = 'language_' . $syslang;
    
    /*$detailPageArray = explode(',', $detailPage);
    $staffDetailPage = $detailPageArray[0];
    $projectDetailPage = $detailPageArray[1];*/
        
    foreach ($response as $document) {
        $id = $document->id;
        $title = $this->fixArray($document->documentTitle);
        $abstract = $this->fixArray($document->abstract);
        $additionalLink = $document->additionalLink;
        $authorNameArray = $document->authorName;
        $authorFirstNameArray = $document->authorFirstName;
        $authorLastNameArray = $document->authorLastName;
        $authorExternalArray = $document->authorExternal;
        $authorOrganisationId = $document->authorOrganisationId;
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
            $i++;
        }
        if($document->organisationName) {
            $organisationName = $document->organisationName;
            $organisationId = $document->organisationId;
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
        $bibliographicalNote = $document->bibliographicalNote;
        $bibtex = $document->bibtex;
        $cite = $document->cite;
        $doi = $document->doi;
        $electronicIsbn = $document->electronicIsbn;
        $electronicVersionAccessType = $document->electronicVersionAccessType;
        $electronicVersionDoi = $document->electronicVersionDoi;
        $electronicVersionFileName = $document->electronicVersionFileName;
        $electronicVersionFileURL = $document->electronicVersionFileURL;
        $electronicVersionLicenseType = $document->electronicVersionLicenseType;
        $electronicVersionLink = $document->electronicVersionLink;
        $electronicVersionMimeType = $document->electronicVersionMimeType;
        $electronicVersionSize = $document->electronicVersionSize;
        $electronicVersionTitle = $document->electronicVersionTitle;
        $electronicVersionVersionType = $document->electronicVersionVersionType;
        $edition = $document->edition;
        $endDate = $document->endDate;
        $eventName = $document->eventName;
        $eventLink = $document->eventLink;
        $eventType = $document->eventType;
        $eventCity = $document->eventCity;
        $eventCountry = $document->eventCountry;
        $hostPublicationTitle = $document->hostPublicationTitle;
        $isbn2 = $document->issn;
        $issn = $document->issn;
        $journalNumber = $document->journalNumber;
        $journalTitle = $document->journalTitle;
        $keyword = $document->keyword;
        $keywordType = $document->keywordType;
        $language = $this->fixArray($document->language);
        $numberOfPages = $document->numberOfPages;
        $openAccessPermission = $document->openAccessPermission;
        $pages = $document->pages;
        $peerReview = $document->peerReview;
        $printIsbns = $document->printIsbns;
        $publicationDateYear = $document->publicationDateYear;
        $publicationDateMonth = $document->publicationDateMonth;
        $publicationDateDay = $document->publicationDateDay;
        $publicationStatus = $document->publicationStatus;
        $placeOfPublication = $document->placeOfPublication;
        $publisher = $document->publisher;
        $publicationType = $document->publicationType;
        $publicationTypeUri = $document->publicationTypeUri;
        $startDate = $document->startDate;
        $supervisors = $document->supervisorName;
        //Novo begin
        $supervisorId = $document->supervisorId;
        $supervisorName = $document->supervisorName;
        $supervisorOrganisationId = $document->supervisorOrganisationId;
        $supervisorOrganisationName = $document->supervisorOrganisationName;
        $supervisorPersonRole = $document->supervisorPersonRole;
        //Novo ends
        $type = $document->type;
        $volume = $document->volume;
        
        $data = array(
            'abstract' => $abstract,
            'additionalLink' => $additionalLink,
            'authorExternal' => $authorExternal,
            'authorId' => $document->authorId,
            'authorName' => $document->authorName,
            'authorOrganisationId' => $authorOrganisationId,
            'authorReverseName' => rawurlencode($authorReverseName),
            'authorReverseNameShort' => rawurlencode(str_replace("$", ", ", $this->str_lreplace("$", " and ", $authorReverseNameShort))),
            'bibliographicalNote' => $bibliographicalNote,
            'bibtex' => $bibtex,
            'cite' => $cite,
            'doi' => $doi,
            'edition' => $edition,
            'electronicIsbns' => $electronicIsbns,
            'electronicIsbn' => $electronicIsbn,
            'electronicVersionAccessType' => $electronicVersionAccessType,
            'electronicVersionDoi' => $electronicVersionDoi,
            'electronicVersionFileName' => $electronicVersionFileName,
            'electronicVersionFileURL' => $electronicVersionFileURL,
            'electronicVersionLicenseType' => $electronicVersionLicenseType,
            'electronicVersionLink' => $electronicVersionLink,
            'electronicVersionMimeType' => $electronicVersionMimeType,
            'electronicVersionSize' => $electronicVersionSize,
            'electronicVersionTitle' => $electronicVersionTitle,
            'electronicVersionVersionType' => $electronicVersionVersionType,
            'endDate' => $endDate,
            'eventName' => $eventName,
            'eventLink' => $eventLink,
            'eventType' => $eventType,
            'eventCity' => $eventCity,
            'eventCountry' => $eventCountry,
            'externalOrganisations' => $externalOrganisations,
            'id' => $id,
            'hostPublicationTitle' => $hostPublicationTitle,
            'isbn' => $isbn2,
            'issn' => $issn,
            'journalTitle' => $journalTitle,
            'journalNumber' => $journalNumber,
            'keyword' => $keyword,
            'keywordType' => $keywordType,
            'language' => $language,
            'numberOfPages' => $numberOfPages,
            'openAccessPermission' => $openAccessPermission,
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
            'startDate' => $startDate,
            'supervisors' => $supervisors,
            'supervisorId' => $document->supervisorId,
            'supervisorName' => $document->supervisorName,
            'supervisorOrganisationId' => $document->supervisorOrganisationId,
            'supervisorOrganisationName' => $document->supervisorOrganisationName,
            'supervisorPersonRole' => $document->supervisorPersonRole,
            'title' => $title,
            'volume' => $volume,
        );

    }
    
    $resArray = array('data' => $data, 'title' => $title, 'query' => $queryToSet);
    
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


function listTagCloud($scope, $syslang, $config, $pageid, $path, $tableLength)
{
    $fieldArray = array("keyword");
            
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

    $queryToSet = 'docType:publication AND (workflow:Granskad OR workflow:Validated) AND -' . $hideVal . ':1 AND publicationDateYear:[* TO ' . date("Y") . '] AND ('.$term.')';
    $query->setQuery($queryToSet);
    //$GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_devlog', array('msg' => 'docType:publication AND -' . $hideVal . ':1 AND publicationDateYear:[* TO ' . date("Y") . '] AND ('.$term.')', 'crdate' => time()));
    //$query->addParam('rows', 1500);
    $query->setFields($fieldArray);
    $query->setStart(0)->setRows(10000);
    $sortArray = array(
        'documentTitle' => 'asc'
    );
    $query->addSorts($sortArray);

    $response = $client->select($query);
    
    $numFound = $response->getNumFound();
    $tagArray = array();
    $i=1;
    $randomClasses = array("", "vertical", "", "");
    
    $keywordsArray = array();
    $tmpArray = array();
    foreach ($response as $document) {
        if(is_array($document->keyword)) {
            
            $tmpArray = array_unique($document->keyword);
            foreach($tmpArray as $key => $value) {
                $keywordsArray[] = $value;
            }
        }
    }

    $data = array();
    if($tableLength) $tableLength = intval($tableLength);
    if($keywordsArray) {
        asort($keywordsArray);
        //$GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_devlog', array('msg' => print_r($keywordsArray, true), 'crdate' => time()));
        foreach($keywordsArray as $key => $value) {
            if($oldValue !== $value) {
                if($i > $tableLength) {
                    $data[] = array(
                        'text' => $oldValue,
                        'link' => urldecode($path) . '?keyword=' . $oldValue,
                        'html' => array('class' => $randomClasses[array_rand($randomClasses, 1)]),
                        'weight' => ($i)+1
                    );
                }
                $i = 0;
                
            }
            $oldValue = $value;
            $i++;
        }
    }
    $resArray = array('data' => $data, 'numFound' => $numFound, 'queryToSet' => $queryToSet);
    return json_encode($resArray);
}


function showStudentPaperNovo($syslang, $scope, $dataSettings, $config)
{
    $client = new Solarium\Client($config);

    $query = $client->createSelect();
    
    $scope = $dataSettings['scope'];
    
    if($scope) {
        /*$scope = str_replace('--', '$$$', $scope);
        $scope = str_replace('-', ' ', $scope);
        $scope = str_replace('$$$', '- ', $scope);*/
        $queryToSet = 'docType:studentPaper AND documentTitleExact:"' . urldecode($scope) . '"';
    }

    $query->setQuery($queryToSet);
    
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

    }
    
    $resArray = array('data' => $data, 'debug' => $queryToSet);
    
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


function listOrganisationProjects($dataSettings, $config, $action)
{
    $filterQuery = $dataSettings['query'];
    $scope = $dataSettings['scope'];
    $syslang = $dataSettings['syslang'];
    
    $fieldArray = array('id','curtailed','endDate','managingOrganisationId','managingOrganisationName','managingOrganisationType','organisationId',
            'organisationName','organisationType','participantId','participantName','participantOrganisationId','participantOrganisationName',
            'participantOrganisationType','participantRole','portalUrl','projectDescription','projectDescriptionType','projectStatus','projectTitle','projectType',
            'startDate','visibility');
    
    $client = new Solarium\Client($config);
    $query = $client->createSelect();
    
    if($filterQuery) {
        $filterQuery = str_replace(" ","\ ",$filterQuery);
        $filterQuery = " AND ((projectTitle:*$filterQuery*) OR participantName:*$filterQuery*)";
    }
    
    if($scope) {
        $queryToSet = 'docType:project';
        $scope = urldecode($scope);
        $i = 0;
        $queryToSet .= ' AND (';
        $scopeArray = explode(',', $scope);
        foreach($scopeArray as $key => $value) {
            if($i>0) $queryToSet .= ' OR ';
            $queryToSet .= 'organisationSourceId:' . array_shift(explode('__',$value)) . ' OR organisationTitleExact:' . str_replace(' ', '\ ', $value) ;
            $i++;
        }
        $queryToSet .= ')';
    }

    $query->setQuery($queryToSet);
    $query->setFields($fieldArray);
    $query->setStart($tableStart)->setRows(100);
    
    $sortArray = array(
        'projectTitle' => 'asc'
    );
    $query->addSorts($sortArray);
    
    // get the facetset component
    $facetSet = $query->getFacetSet();
    
    // create a facet field instance and set options
    $facetSet->createFacetField('status')->setField('projectStatus');
    $facetSet->createFacetField('type')->setField('projectType');

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
    
    $response = $client->select($query);
    
    $numFound = $response->getNumFound();
    
    // display facet query count
    $facetStatus = $response->getFacetSet()->getFacet('status');
    if($syslang==="en") {
        $facetHeader = "Project Status";
    } else {
        $facetHeader = "Projektstatus";
    }
    foreach ($facetStatus as $value => $count) {
        if($count > 0) $facetResult["status"][] = array($value, $count, $facetHeader);
    }
    
    $facetType = $response->getFacetSet()->getFacet('type');
    if($syslang==="en") {
        $facetHeader = "Project Type";
    } else {
        $facetHeader = "Projekttyp";
    }
    foreach ($facetType as $value => $count) {
        if($count > 0) $facetResult["type"][] = array($value, $count, $facetHeader);
    }
        
    foreach ($response as $document) {     
        $data[] = array(
            'id' => $document->id,
            'curtailed' => $document->curtailed,
            'endDate' => (string)$document->endDate,
            'managingOrganisationId' => $document->managingOrganisationId,
            'managingOrganisationName' => $document->managingOrganisationName,
            'managingOrganisationType' => $document->managingOrganisationType,
            'organisationId' => $this->fixArray($document->organisationId),
            'organisationName' => $this->fixArray($document->organisationName),
            'organisationType' => $this->fixArray($document->organisationType),
            'participantId' => $this->fixArray($document->participantId),
            'participantName' => $this->fixArray($document->participantName),
            'participantOrganisationId' => $this->fixArray($document->participantOrganisationId),
            'participantOrganisationName' => $this->fixArray($document->participantOrganisationName),
            'participantOrganisationType' => $this->fixArray($document->participantOrganisationType),
            'participantRole' => $this->fixArray($document->participantRole),
            'portalUrl' => $document->portalUrl,
            'projectDescription' => $this->fixArray($document->projectDescription),
            'projectDescriptionType' => $this->fixArray($document->projectDescriptionType),
            'projectStatus' => $document->projectStatus,
            'projectTitle' => $document->projectTitle,
            'projectType' => $document->projectType,
            'startDate' => (string)$document->startDate,
            'visibility' => $document->visibility,
        );
    }
    $resArray = array('data' => $data, 'facet' => $facetResult, 'numFound'=> $numFound, 'debug' => $queryToSet . $debug);
    return json_encode($resArray);
}


function listProjects($scope, $syslang, $config, $tableLength, $tableStart, $filterQuery)
{
    $client = new Solarium\Client($config);

    $query = $client->createSelect();
    
    if($filterQuery) {
        $filterQuery = str_replace(" ","\ ",$filterQuery);
        $filterQuery = " AND ((projectTitle:*$filterQuery*) OR participantName:*$filterQuery*)";
    }
    
    if($scope) {
        
        $scope = json_decode(urldecode($scope),true);
        
        foreach($scope as $key => $value) {
            foreach($value as $skey => $svalue) {
                if($term) {
                    $term .= " OR ";
                }
                $term .= "organisationSourceId:$svalue";
            }
        }
    }

    $queryToSet = 'docType:project AND (' . $term . ')' . $filterQuery;
    $query->setQuery($queryToSet);
    //$query->addParam('rows', 1500);
    $query->setStart($tableStart)->setRows($tableLength);
    
    $sortArray = array(
        'projectTitle' => 'asc'
    );
    $query->addSorts($sortArray);
    
    // get the facetset component
    $facetSet = $query->getFacetSet();
    
    // create a facet field instance and set options
    $facetSet->createFacetField('status')->setField('projectStatus');
    $facetSet->createFacetField('type')->setField('projectType');

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
    
    $response = $client->select($query);
    
    $numFound = $response->getNumFound();
    
    // display facet query count
    $facetStatus = $response->getFacetSet()->getFacet('status');
    if($syslang==="en") {
        $facetHeader = "Project Status";
    } else {
        $facetHeader = "Projektstatus";
    }
    foreach ($facetStatus as $value => $count) {
        if($count > 0) $facetResult["status"][] = array($value, $count, $facetHeader);
    }
    
    $facetType = $response->getFacetSet()->getFacet('type');
    if($syslang==="en") {
        $facetHeader = "Project Type";
    } else {
        $facetHeader = "Projekttyp";
    }
    foreach ($facetType as $value => $count) {
        if($count > 0) $facetResult["type"][] = array($value, $count, $facetHeader);
    }
        
    foreach ($response as $document) {     
        $data[] = array(
            'id' => $document->id,
            
            'curtailed' => $document->curtailed,
            'endDate' => (string)$document->endDate,
            'managingOrganisationId' => $document->managingOrganisationId,
            'managingOrganisationName' => $document->managingOrganisationName,
            'managingOrganisationType' => $document->managingOrganisationType,
            'organisationId' => $this->fixArray($document->organisationId),
            'organisationName' => $this->fixArray($document->organisationName),
            'organisationType' => $this->fixArray($document->organisationType),
            'participantId' => $this->fixArray($document->participantId),
            'participantName' => $this->fixArray($document->participantName),
            'participantOrganisationId' => $this->fixArray($document->participantOrganisationId),
            'participantOrganisationName' => $this->fixArray($document->participantOrganisationName),
            'participantOrganisationType' => $this->fixArray($document->participantOrganisationType),
            'participantRole' => $this->fixArray($document->participantRole),
            'projectDescription' => $this->fixArray($document->projectDescription),
            'projectDescriptionType' => $this->fixArray($document->projectDescriptionType),
            'projectStatus' => $document->projectStatus,
            'projectTitle' => $document->projectTitle,
            'projectType' => $document->projectType,
            'startDate' => (string)$document->startDate,
            'visibility' => $document->visibility,
        );
    }
    $resArray = array('data' => $data, 'facet' => $facetResult, 'numFound'=> $numFound, 'debug' => $queryToSet . $debug);
    return json_encode($resArray);
}


function showProject($scope, $syslang, $config)
{
    $client = new Solarium\Client($config);
    $query = $client->createSelect();
    if($scope) {
        $scope = json_decode(urldecode($scope),true);
        $scope = $scope['projects'][0];
    }
    
    //Project
    $query->setQuery('id:'.$scope);
    $response = $client->select($query);       
    foreach ($response as $document) {     
        $projectData = array(
            'id' => $document->id,
            'curtailed' => $document->curtailed,
            'endDate' => (string)$document->endDate,
            'managingOrganisationId' => $document->managingOrganisationId,
            'managingOrganisationName' => $document->managingOrganisationName,
            'managingOrganisationType' => $document->managingOrganisationType,
            'organisationId' => $this->fixArray($document->organisationId),
            'organisationName' => $this->fixArray($document->organisationName),
            'organisationType' => $this->fixArray($document->organisationType),
            'participantId' => $this->fixArray($document->participantId),
            'participantName' => $this->fixArray($document->participantName),
            'participantOrganisationId' => $this->fixArray($document->participantOrganisationId),
            'participantOrganisationName' => $this->fixArray($document->participantOrganisationName),
            'participantOrganisationType' => $this->fixArray($document->participantOrganisationType),
            'participantRole' => $this->fixArray($document->participantRole),
            'projectDescription' => $document->projectDescription,
            'projectDescriptionType' => $document->projectDescriptionType,
            'projectStatus' => $document->projectStatus,
            'projectTitle' => $document->projectTitle,
            'projectType' => $document->projectType,
            'startDate' => (string)$document->startDate,
            'visibility' => $document->visibility,
        );
    }
    
    //Publications
    $publicationData = $this->listOrganisationPublications($dataSettings, $config, $action);
    
    $resArray = array('projectData' => $projectData, 'publicationData' => $publicationData);
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
        $custom_categories, $config, $filterQuery, $tableFields, $action, $limitToStandardCategories, $thisGroupOnly, $primaryRoleOnly)
{
    if($action==='exportStaff') {
        $fieldArray = json_decode($tableFields, true);
    } else {
        $fieldArray = array("firstName","lastName","title","phone","id","email","organisationName",
            "primaryAffiliation","homepage","image","lucrisPhoto","intro","roomNumber","mobile",
            "organisationId","organisationHideOnWeb","organisationLeaveOfAbsence","guid","uuid","heritage",
            "primaryVroleOu","primaryVroleTitle","primaryVroleOrgid","primaryVrolePhone");
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
                if($thisGroupOnly) {
                    $term .= "organisationId:" . implode(' OR organisationId:', $value);
                } else {
                    $term .= "heritage:" . implode(' OR heritage:', $value);
                    $feGroupsArray = $value;
                }
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
                "organisationHideOnWeb" => $document->organisationHideOnWeb,
                "organisationLeaveOfAbsence" => $document->organisationLeaveOfAbsence,
                "primaryAffiliation" => $document->primaryAffiliation,
                "primaryVroleOu" => $document->primaryVroleOu,
                "primaryVroleTitle" => $document->primaryVroleTitle,
                "primaryVroleOrgid" => $document->primaryVroleOrgid,
                "primaryVrolePhone" => $document->primaryVrolePhone,
                "homepage" => $document->homepage,
                "image" => $image,
                "intro" => $intro,
                "roomNumber" => $this->fixRoomNumber($document->roomNumber),
                "mobile" => $document->mobile,
                "organisationId" => $document->organisationId,
                "guid" => $document->guid,
                "uuid" => $document->uuid,
                "imgtest" => $document->image
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
    $fieldArray = array("docType","firstName","lastName","title","phone","id","email","mailDelivery","organisationName","primaryAffiliation","homepage","image","intro","roomNumber",
        "mobile","uuid","guid","organisationId","organisationPhone","organisationStreet","organisationCity","organisationPostalAddress",
        "profileInformation","coordinates","lucrisPhoto");
    
    $content = '';
    $personData = array();
    $publicationData = array();
    $projectData = array();
    if($scope) {
        $scope = json_decode(urldecode($scope),true);
        //$GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_devlog', array('msg' => $scope['fe_groups'][0], 'crdate' => time()));
        $uuid = $scope['fe_users'][0];
        $organisation = $this->getLucrisId($scope['fe_groups'][0],$config);
    }
    
    $client = new Solarium\Client($config);
    $query = $client->createSelect();
    $queryToSet = 'docType:organisation AND id:' . $organisation;
    $query->setQuery($queryToSet);
    $query->setFields(array("organisationSourceId"));
    $response = $client->select($query);
    if($response) {
        foreach ($response as $document) {
            $organisationSourceId = $document->organisationSourceId;
        }
    }
    
    $queryToSet = 'docType:staff AND heritage:' . $organisationSourceId[0] . ' AND (guid:' . $uuid . ' OR uuid:' . $uuid . ')';
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
                "email" => $document->email,
                "firstName" => $document->firstName,
                "lastName" => $document->lastName,
                "mailDelivery" => $document->mailDelivery,
                "title" => $document->title,
                "phone" => $document->phone,
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