<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Lth\Lthsolr\Task;

ini_set('memory_limit', '-1');
error_reporting(E_ERROR);
set_time_limit(0);

use TYPO3\CMS\Core\Utility\GeneralUtility;

class LuCacheImport extends \TYPO3\CMS\Scheduler\Task\AbstractTask {
	
    function execute()
    {
	$executionSucceeded = FALSE;

	$executionSucceeded = $this->indexItems();
        
	return $executionSucceeded;
    }

    function indexItems()
    {

        require(__DIR__.'/init.php');

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

        if (!$settings['solrHost'] || !$settings['solrPort'] || !$settings['solrPath'] || !$settings['solrTimeout'] || !$settings['dbhost'] || !$settings['db'] || !$settings['grsp'] || !$settings['studentGrsp'] || !$settings['user'] || !$settings['pw']) {
	    return 'Please make all settings in extension manager';
	}
                
        $grsp = $settings['grsp'];
        $studentGrsp = $settings['studentGrsp'];
        $hideonwebGrsp = $settings['hideonwebGrsp'];
        $studentMainGroup = $settings['solrStudentMainGroup'];

        $dbhost = $settings['dbhost'];
        $db = $settings['db'];
        $user = $settings['user'];
        $pw = $settings['pw'];
        $solrImageImportFolder = $settings['solrImageImportFolder'];

        $con = mysqli_connect($dbhost, $user, $pw, $db) or die("60; ".mysqli_error());

        $imageArray = $this->getImageArray($solrImageImportFolder);

        $employeeArray = $this->getEmployee($con, $imageArray);

        $employeeArray = $this->getLucrisData($employeeArray);

        $folderArray = $this->getFolderStructure($grsp);
        
        $feGroupsArray = $this->getFeGroups($grsp);
 
        $employeeArray = $this->getFeUsers($employeeArray, $grsp);

        $orgArray = $this->getOrg($con);
                
        $heritageArray = $this->getHeritage($con);
        
        $heritage2Array = $this->getHeritage2($config);

        $categoriesArray = $this->getCategories();

        $this->createFolderStructure($grsp, $folderArray, $orgArray);
        
        $folderArray = $this->getFolderStructure($grsp);
        
        $this->createFeGroups($folderArray, $orgArray, $feGroupsArray, $heritageArray);
        
        $feGroupsArray = $this->getFeGroups($grsp);
        
        $this->createFeUsers($folderArray, $employeeArray, $feGroupsArray, $studentGrsp, $hideonwebGrsp, $studentMainGroup);

        $executionSucceeded = $this->updateSolr($employeeArray, $heritageArray, $heritage2Array, $categoriesArray, $config, $syslang, $orgArray);
        
        $syslang = "en";
        
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
        $executionSucceeded = $this->updateSolr($employeeArray, $heritageArray, $heritage2Array, $categoriesArray, $config, $syslang, $orgArray);
        //$executionSucceeded = TRUE;
        
        //mysqli_free_result($res);
        return $executionSucceeded;
    }
    
    
    private function debug($inputArray)
    {
        echo '<pre>';
        print_r($inputArray);
        echo '</pre>';
    }
    
    
    private function getImageArray($solrImageImportFolder)
    {
        $email = '';
        $fileArray = scandir($solrImageImportFolder);
        foreach ($fileArray as $filekey => $filename) {
            $tmpArray = explode('.', $filename);
            array_pop($tmpArray);
            $email = implode('.', $tmpArray);
            $imageArray[$email] = $filename;
        }
        return array_slice($imageArray, 2);
    }
    
    
    private function getEmployee($con, $imageArray)
    {
        /*
         * uid                 VARCHAR(30),    -- uid
        display_name        VARCHAR(100),   -- displayName
        first_name          VARCHAR(50),    -- givenName
        last_name           VARCHAR(50),    -- sn
        email               VARCHAR(100),   -- mail
        ou                  VARCHAR(200),   -- ou
        title               VARCHAR(128),   -- title
        orgid               VARCHAR(16),    -- departmentNumber
        primary_affiliation VARCHAR(20),    -- eduPersonPrimaryAffiliation
        pnr                 VARCHAR(12),    -- norEduPersonNIN (10 chars for now)
        homepage            VARCHAR(100),   -- labeledURI
        lang                CHAR(2),        -- preferredLanguage
        degree              VARCHAR(100),   -- luEduPersonAcademicDegree
        degree_en           VARCHAR(100),   -- luEduPersonAcademicDegree;lang-en
        phone               VARCHAR(32),    -- telephoneNumber
        hide_on_web         BOOLEAN DEFAULT FALSE, -- luEduPersonPrivacy: webb=1
         */
        
        $employeeArray = array();
        $secondArray = array();
        
        $sql = "SELECT 
            P.id,
            P.primary_uid, 
            LCASE(P.first_name) AS first_name, 
            LCASE(P.last_name) AS last_name, 
            P.primary_affiliation,
            P.primary_vrole_ou,
            P.primary_vrole_title,
            P.primary_vrole_orgid,
            P.primary_vrole_phone,
            P.homepage, 
            P.lang, 
            P.degree, 
            P.degree_en,
            P.primary_lu_email,
            GROUP_CONCAT(V.guid SEPARATOR '|') AS guid,
            GROUP_CONCAT(V.hide_on_web SEPARATOR '|') AS hide_on_web,
            GROUP_CONCAT(V.leave_of_absence SEPARATOR '|') AS leave_of_absence,
            GROUP_CONCAT(V.orgid SEPARATOR '|') AS orgid,
            GROUP_CONCAT(COALESCE(V.room_number,'NULL') SEPARATOR '|') AS room_number,
            GROUP_CONCAT(V.title SEPARATOR '|') AS title,
            GROUP_CONCAT(V.title_en SEPARATOR '|') AS title_en,
            GROUP_CONCAT(COALESCE(V.phone,'NULL') SEPARATOR '|') AS phone,
            GROUP_CONCAT(COALESCE(V.mobile,'NULL') SEPARATOR '|') AS mobile,
            GROUP_CONCAT(VORG.legacy_orgid SEPARATOR '|') AS orgid_legacy,
            GROUP_CONCAT(VORG.name SEPARATOR '|') AS oname,
            GROUP_CONCAT(VORG.name_en SEPARATOR '|') AS oname_en,
            GROUP_CONCAT(VORG.maildelivery SEPARATOR '|') AS maildelivery,
            GROUP_CONCAT(VORG.phone SEPARATOR '|') AS ophone,
	    GROUP_CONCAT(VORG.street SEPARATOR '|') AS ostreet,
            GROUP_CONCAT(VORG.city SEPARATOR '|') AS ocity,
            GROUP_CONCAT(VORG.postal_address SEPARATOR '|') AS opostal_address
            FROM lucache_person AS P 
            LEFT JOIN lucache_vrole AS V ON (P.id = V.id AND V.hide_on_web = 0) 
            LEFT JOIN lucache_vorg VORG ON V.orgid = VORG.orgid 
            GROUP BY P.id
            ORDER BY P.id, V.orgid";
        
        $res = mysqli_query($con, $sql) or die("183; ".mysqli_error());

        while ($row = mysqli_fetch_array($res, MYSQLI_ASSOC)) {
            $primary_uid = $row['primary_uid'];
            $email = $row['primary_lu_email'];
            $employeeArray[$primary_uid]['id'] = $row['id'];
            $employeeArray[$primary_uid]['primary_uid'] = $primary_uid;
            $employeeArray[$primary_uid]['first_name'] = $this->toUC($row['first_name']);
            $employeeArray[$primary_uid]['last_name'] = $this->toUC($row['last_name']);
            $employeeArray[$primary_uid]['email'] = $email;
            $employeeArray[$primary_uid]['primary_affiliation'] = $row['primary_affiliation'];
            $employeeArray[$primary_uid]['primary_vrole_ou'] = $row['primary_vrole_ou'];
            $employeeArray[$primary_uid]['primary_vrole_title'] = $row['primary_vrole_title'];
            $employeeArray[$primary_uid]['primary_vrole_orgid'] = $row['primary_vrole_orgid'];
            $employeeArray[$primary_uid]['primary_vrole_phone'] = $row['primary_vrole_phone'];
            $employeeArray[$primary_uid]['homepage'] = $row['homepage'];
            $employeeArray[$primary_uid]['lang'] = $row['lang'];
            $employeeArray[$primary_uid]['degree'] = $row['degree'];
            $employeeArray[$primary_uid]['degree_en'] = $row['degree_en'];
            //arrays:
            $employeeArray[$primary_uid]['guid'] = explode('|', $row['guid']);
            $employeeArray[$primary_uid]['hide_on_web'] = explode('|', $row['hide_on_web']);
            $employeeArray[$primary_uid]['leave_of_absence'] = explode('|', $row['leave_of_absence']);
            $employeeArray[$primary_uid]['ophone'] = explode('|', $row['ophone']);
	    $employeeArray[$primary_uid]['ostreet'] = explode('|', $row['ostreet']);
            $employeeArray[$primary_uid]['ocity'] = explode('|', $row['ocity']);
            $employeeArray[$primary_uid]['opostal_address'] = explode('|', $row['opostal_address']);
            $employeeArray[$primary_uid]['room_number'] = explode('|', $row['room_number']);
            $employeeArray[$primary_uid]['title'] = explode('|', $row['title']);
            $employeeArray[$primary_uid]['title_en'] = explode('|', $row['title_en']);
            $employeeArray[$primary_uid]['phone'] = explode('|', $row['phone']);
            $employeeArray[$primary_uid]['mobile'] = explode('|', $row['mobile']);
            $employeeArray[$primary_uid]['orgid'] = explode('|', $row['orgid']);
            $employeeArray[$primary_uid]['orgid_legacy'] = explode('|', $row['orgid_legacy']);
            $employeeArray[$primary_uid]['oname'] = explode('|', $row['oname']);
            $employeeArray[$primary_uid]['oname_en'] = explode('|', $row['oname_en']);
            $employeeArray[$primary_uid]['maildelivery'] = explode('|', $row['maildelivery']);
            if($email && $imageArray[$email]) {
                $employeeArray[$primary_uid]['image'] = 'fileadmin/images/uploads/' . $imageArray[$email];
            }
        }

        return $employeeArray;
    }
    
    
    private function getLucrisData($employeeArray)
    {
        $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery("DISTINCT typo3_id,lucris_id,lucris_photo,lucris_profile_information","tx_lthsolr_lucrisdata","typo3_id!=''");
        while ($row = $GLOBALS["TYPO3_DB"]->sql_fetch_assoc($res)) {
            $typo3_id = $row['typo3_id'];
            $lucris_id = $row['lucris_id'];
            $lucrisphoto = $row['lucris_photo'];
            $lucris_profile_information = $row['lucris_profile_information'];

            $profileInformation_en = '';
            $profileInformation_sv = '';
            $resArray = array();
            if($lucris_profile_information) {
                $profileInformationArray = json_decode($lucris_profile_information, true);
                foreach($profileInformationArray as $key => $value) {
                    if($key==='en') {
                        $resArray['en'] = $value;
                    } else if($key==='sv') {
                        $resArray['sv'] = $value;
                    } else if($value['en_GB']) {
                        $resArray['en'][$key] = $value['en_GB'];
                    } else if($value['sv_SE']) {
                        $resArray['sv'][$key] = $value['sv_SE'];
                    }
                }
                if($resArray['en']) $profileInformation_en = json_encode ($resArray['en']);
                if($resArray['sv']) $profileInformation_sv = json_encode ($resArray['sv']);
            } 
            
            if(array_key_exists($typo3_id, $employeeArray)) {
                $employeeArray[$typo3_id]['uuid'] = $lucris_id;
                $employeeArray[$typo3_id]['lucrisphoto'] = $lucrisphoto;
                $employeeArray[$typo3_id]['profileInformation_en'] = $profileInformation_en;
                $employeeArray[$typo3_id]['profileInformation_sv'] = $profileInformation_sv;
            }
        }
        $GLOBALS['TYPO3_DB']->sql_free_result($res);
        return $employeeArray;
    }
     
    
    private function getOrg($con)
    {
        $orgArray = array();
        /*
         *  orgid               VARCHAR(16),    -- departmentNumber
            parent              VARCHAR(16),    -- 0 if top level
            name                VARCHAR(128),   -- ou
            name_en             VARCHAR(128),   -- ou;lang-en
            orgtype             VARCHAR(20),    -- luEduOrgType
            homepage            VARCHAR(128),   -- eduOrgUnitHomePageURI
            homepage_en         VARCHAR(128),   -- eduOrgUnitHomePageURI;lang-en
         */
        $sql = "SELECT O1.parent, O1.orgid,
            O1.name, O1.name_en, O1.orgtype, O1.homepage, O1.homepage_en, GROUP_CONCAT(O2.orgid) AS subgroup 
            FROM lucache_vorg AS O1 LEFT JOIN lucache_vorg AS O2 ON O1.orgid = O2.parent 
            WHERE O1.orgid IS NOT NULL 
            GROUP BY O1.orgid";
        $res = mysqli_query($con, $sql);
        while ($row = mysqli_fetch_array($res, MYSQLI_ASSOC)) {
            $orgArray[$row['orgid']] = array(
                'orgid' => $row['orgid'],
                'parent' => $row['parent'],
                'name' => utf8_encode($row['name']),
                'name_en' => utf8_encode($row['name_en']),
                'orgtype' => utf8_encode($row['orgtype']),
                'homepage' => $row['homepage'],
                'homepage_en' => $row['homepage_en'],
                'subgroup' => $row['subgroup'] . ''
            );
        }
        return $orgArray;
    }
    
    
    private function getHeritage($con)
    {
        $heritageArray = array();
        
        $sql = "SELECT orgid, parent FROM lucache_vorg";
        
        $res = mysqli_query($con, $sql);
        
        while ($row = mysqli_fetch_array($res, MYSQLI_ASSOC)) {
            $heritageArray[$row['orgid']] = $row['parent'];
        }

        return $heritageArray;
    }
    
    
    private function getHeritage2($config)
    {
        $heritageArray = array();
        $organisationArray = array();
        
        $client = new \Solarium\Client($config);
        $query = $client->createSelect();
        $query->setQuery('docType:organisation');
        $query->setFields(array("id", "organisationSourceId", "organisationParent"));
        $query->setStart(0)->setRows(10000);
        $response = $client->select($query);
        foreach ($response as $document) {
            if($document->organisationSourceId && substr($document->organisationSourceId[0],0,1)==='v') {
                $organisationArray[$document->id] = array('parent' => $document->organisationParent, 'organisationSourceId' => $document->organisationSourceId[0]);
            }
        }

        if($organisationArray) {
            foreach ($organisationArray as $key => $value) {
                if($value['organisationSourceId'] && $value['parent']) {
                    foreach($value['parent'] as $key2 => $value2) {
                        if($organisationArray[$value2]['organisationSourceId']) $heritageArray[$value['organisationSourceId']][] = $organisationArray[$value2]['organisationSourceId'];
                    }
                }
            }
        }
        
        return $heritageArray;
    }
    
    
    private function getCategories()
    {
        $categoriesArray = array();
        $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery("title_sv, name_sv, name_en", "tx_lthsolr_titles t JOIN tx_lthsolr_categories c ON t.category = c.id","");
        while ($row = $GLOBALS["TYPO3_DB"]->sql_fetch_assoc($res)) {
            $categoriesArray[strtolower($row['title_sv'])] = array(str_replace(' ', '_', $row['name_sv']), str_replace(' ', '_', $row['name_en']));
        }
        $GLOBALS['TYPO3_DB']->sql_free_result($res);
        return $categoriesArray;
    }
    
    
    private function getFolderStructure($grsp)
    {
        $folderArray = array();
        $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid, title', 'pages', 'pid = ' . intval($grsp) . ' AND deleted = 0');
        while ($row = $GLOBALS["TYPO3_DB"]->sql_fetch_assoc($res)) {
            $titleArray = explode('__', $row['title']);
            $folderArray[$titleArray[0]] = array('uid' => $row['uid'], 'title' => $row['title']);
        }
        return $folderArray;
    }
    
    
    private function getFeGroups($grsp)
    {
        $feGroupsArray = array();
        $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('F.uid,F.pid,F.title,F.subgroup',
                'fe_groups F JOIN pages P ON P.uid=F.pid',
                'F.deleted=0 AND P.Pid='.intval($grsp));
        while ($row = $GLOBALS["TYPO3_DB"]->sql_fetch_assoc($res)) {
            $titleArray = explode('__', $row['title']);
            if(is_array($titleArray)) {
                $feGroupsArray[$titleArray[0]] = array('title' => $row['title'], 'uid' => $row['uid'], 'pid' => $row['pid'], 'subgroup' => $row['subgroup']);
            }
        }
        return $feGroupsArray;
    }
    
    
    private function getFeUsers($employeeArray, $grsp)
    {
        $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('DISTINCT F.username,F.usergroup,F.image,F.image_id,F.lth_solr_cat,F.lucache_id,F.lth_solr_sort,F.lth_solr_intro,F.lth_solr_show',
                'fe_users F JOIN pages P ON P.uid=F.pid',
                'F.lth_solr_index=1 AND F.deleted=0 AND F.disable=0 AND P.pid='.intval($grsp));
        while ($row = $GLOBALS["TYPO3_DB"]->sql_fetch_assoc($res)) {
            $username = $row['username'];
            $lth_solr_cat = $row['lth_solr_cat'];
            $lth_solr_intro = $row['lth_solr_intro'];
            $lth_solr_sort = $row['lth_solr_sort'];
            $lth_solr_show = $row['lth_solr_show'];
            $lucache_id = $row['lucache_id'];
            
            if(array_key_exists($username, $employeeArray)) {
                        
                if($lth_solr_cat && $lth_solr_cat !== '') {
                    $lth_solr_cat = json_decode($lth_solr_cat, true);
                    if($lth_solr_cat) {
                        foreach($lth_solr_cat as $key => $value) {
                            $employeeArray[$username]['lth_solr_cat'][$key] = $value;
                        }
                    }
                }

                if($lth_solr_intro && $lth_solr_intro !== '') {
                    $lth_solr_intro = json_decode($lth_solr_intro, true);
                    if($lth_solr_intro) {
                        foreach($lth_solr_intro as $key => $value) {
                            $employeeArray[$username]['lth_solr_intro'][$key] = $value;
                        }
                    }
                }

                if($lth_solr_sort && $lth_solr_sort !== '') {
                    $lth_solr_sort = json_decode($lth_solr_sort, true);
                    if($lth_solr_sort) {
                        foreach($lth_solr_sort as $key => $value) {
                            $employeeArray[$username]['lth_solr_sort'][$key] = $value;
                        }
                    }
                }
        
                $employeeArray[$username]['lth_solr_show'] = $lth_solr_show;
                $employeeArray[$username]['usergroup'] = $row['usergroup'];
                if($row['image']) $employeeArray[$username]['image'] = $row['image'];
                if($row['image_id']) $employeeArray[$username]['image_id'] = $row['image_id'];
                $employeeArray[$username]['exist'] = TRUE;
            } else if($username) {
                $employeeArray[$username]['id'] = $lucache_id;
                $employeeArray[$username]['exist'] = 'disable';
            }
        }
        return $employeeArray;
    }
    
    
    private function createFolderStructure($grsp, $folderArray, $orgArray)
    {
        if(!$grsp) {
            return false;
        }
        //print_r($folderArray);
        foreach($orgArray as $key => $value) {
            $tmpTitle = $value['orgid'] . '__' . $value['name'];
            if(!array_key_exists($value['orgid'], $folderArray)) {
                $GLOBALS['TYPO3_DB']->exec_INSERTquery('pages', array('title' => $tmpTitle, 'pid' => $grsp, 'doktype' => 254, 'crdate' => time(), 'tstamp' => time()));
            }
        }
    }
    
    
    private function createFeGroups($folderArray, $orgArray, $feGroupsArray, $heritageArray)
    {
        $tmpTitle = '';
        $tmpKeyArray = array();
        $pidArray = array();

        foreach($orgArray as $key => $value) {
            $tmpTitle = $value['orgid'] . '__' . $value['name'];
            $subGroup = $heritageArray[$value['orgid']];
            if($subGroup) {
                $subGroup = $feGroupsArray[$subGroup]['uid'];
            }
            //print_r($feGroupsArray);
            $folder = $folderArray[$value['orgid']];
            if(!array_key_exists($value['orgid'], $feGroupsArray)) {
                if($folder['uid']){
                    $GLOBALS['TYPO3_DB']->exec_INSERTquery('fe_groups', array(
                        'title' => $tmpTitle,
                        'pid' => $folder['uid'],
                        //'subgroup' => $value['subgroup'],
                        'crdate' => time(), 
                        'tstamp' => time())
                    );
                }
            } else if($folder['uid']) {
                $GLOBALS['TYPO3_DB']->exec_UPDATEquery('fe_groups', "title='$tmpTitle'", array(
                    'subgroup' => $subGroup,
                    'tstamp' => time())
                );
            }
        }
    }
    
    
    private function createFeUsers($folderArray, $employeeArray, $feGroupsArray, $studentGrsp, $hideonwebGrsp, $studentMainGroup)
    {
        //$GLOBALS['TYPO3_DB']->store_lastBuiltQuery = 1;
        $title;
        $lucache_id;
                    

        foreach($employeeArray as $key => $value) {
            $title = '';
            $phone = '';
            $room_number = '';
            
            if(is_array($value['title'])) {
                $title = implode(',', array_unique($value['title']));
            }
            if(is_array($value['phone'])) {
                $phone = implode(',', array_unique($value['phone']));
            }
            if(is_array($value['room_number'])) {
                $room_number = implode(',', array_unique($value['room_number']));
            } 
            $usergroupArray = $this->getUids($value['orgid'], $feGroupsArray);

            if($usergroupArray[0] || $value['primary_affiliation'] === 'student') {
                $ugFe = $value['usergroup'];
                if($value['primary_affiliation'] === 'student') {
                    $usergroupArray[0] = $studentMainGroup;
                    $usergroupArray[1] = $studentGrsp;
                }
                if($ugFe && $usergroupArray[0]) {
                    $ugFeArray = explode(',', $ugFe);
                    array_push($ugFeArray, $usergroupArray[0]);
                    $ugFeArray = array_unique($ugFeArray);
                    $usergroupArray[0] = implode(',', $ugFeArray);
                }
                
                if($value['id'] && $value['id'] != '') {
                    $lucache_id = $value['id'];
                } else {
                    $lucache_id = $key;
                }

                if($value['exist']===TRUE && $usergroupArray[1]) {
                    if(!$value['roomnumber']) {
                        $value['roomnumber'] = '';
                    }
                    $updateArray = array(
                        'pid' => $usergroupArray[1],
                        'usergroup' => $usergroupArray[0],
                        'disable' => 0,
                        'first_name' => $value['first_name'],
                        'last_name' => $value['last_name'],
                        'title' => $title,
                        'name' => $value['last_name'] . ', ' . $value['first_name'],
                        'email' => $value['email'],
                        'www' => (string)$value['homepage'],
                        'telephone' => $phone,
                        'roomnumber' => $room_number,
                        'lth_solr_uuid' => (string)$value['uuid'],
                        'lucache_id' => $lucache_id,
                        'lth_solr_index' => 1,
                        'tstamp' => time()
                    );
                    $GLOBALS['TYPO3_DB']->exec_UPDATEquery('fe_users', "username = '" . $value['primary_uid'] . "'", $updateArray);
                } else if($usergroupArray[1]  || $value['primary_affiliation'] === 'student') {
                    if($value['primary_affiliation'] === 'student') {
                        $usergroupArray[1] = $studentGrsp;
                    }
                    $insertArray = array(
                        'username' => $value['primary_uid'],
                        'password' => $this->setRandomPassword(),
                        'name' => $value['last_name'] . ', ' . $value['first_name'],
                        'first_name' => $value['first_name'],
                        'last_name' => $value['last_name'],
                        'title' => $title.'',
                        'email' => $value['email'].'',
                        'www' => (string)$value['homepage'].'',
                        'telephone' => $phone.'',
                        'roomnumber' => $room_number.'',
                        'pid' => $usergroupArray[1],
                        'usergroup' => $usergroupArray[0],
                        'lth_solr_uuid' => (string)$value['uuid'],
                        'lucache_id' => $lucache_id,
                        'lth_solr_index' => 1,
                        'crdate' => time(), 
                        'tstamp' => time()
                    );
                    $GLOBALS['TYPO3_DB']->exec_INSERTquery('fe_users', $insertArray);
                }
            } else if($value['exist'] === 'disable') {
                 $updateArray = array(
                    'disable' => 1,
                    'tstamp' => time()
                );
                $GLOBALS['TYPO3_DB']->exec_UPDATEquery('fe_users', "username = '" . $key . "'", $updateArray);
            }
        }
    }
    
    
    private function updateSolr($employeeArray, $heritageArray, $heritage2Array, $categoriesArray, $config, $syslang, $orgArray)
    {
        //$GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_devlog', array('msg' => print_r($employeeArray['ju1665ca'],true), 'crdate' => time()));
        $coordinatesArray = $this->getCoordinates();
        try {
            if(count($employeeArray) > 0) {
                
                $current_date = gmDate("Y-m-d\TH:i:s\Z");
                
                //create a client instance
                $client = new \Solarium\Client($config);
                $update = $client->createUpdate();
                $buffer = $client->getPlugin('bufferedadd');
                $buffer->setBufferSize(250);
                $docArray = array();
                
                if($syslang==='sv') {
                    $nameTmp = 'name';
                } else {
                    $nameTmp = 'name_en';
                }
                
                foreach($employeeArray as $key => $value) {
                    if($value['exist']==='disable' && $value['id']) {
                        // add the delete id and a commit command to the update query
                        //$GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_devlog', array('msg' => $value['id'], 'crdate' => time()));
                        $update->addDeleteById($value['id']);
                    } else if($value['id'] && ($value['primary_affiliation']==='employee' || $value['primary_affiliation']==='member')) {
                        $heritage = array();
                        $heritageName = array();
                        $heritage2 = array();
                        $heritageName2 = array();

                        //$orgidArray = explode('###', $value['orgid']);
                        $orgidArray = $value['orgid'];

                        foreach($orgidArray as $key1 => $value1) {

                            if(key_exists($value1,$coordinatesArray)) {
                                $value['coordinates'][] = $coordinatesArray[$value1];
                            } else {
                                $value['coordinates'][] = "";
                            }
                            $heritage[] = $value1;
                            
                            $heritageName[] = strtolower(utf8_decode($orgArray[$value1][$nameTmp]));
                            $parent = $heritageArray[$value1];
                            $parent2 = $heritage2Array[$value1];
                            if($parent) {
                                $heritage[] = $parent;
                                $heritageName[] = strtolower(utf8_decode($orgArray[$parent][$nameTmp]));
                                if($parent2) {
                                    foreach($parent2 as $key2 => $value2) {
                                        $heritage2[$value1][] = $value2;
                                        $heritageName2[strtolower($orgArray[$value1][$nameTmp])][] = strtolower($orgArray[$value2][$nameTmp]);
                                    }
                                }
                            }
                            $parent2 = $heritage2Array[$parent];
                            $parent = $heritageArray[$parent];
                            if($parent) {
                                $heritage[] = $parent;
                                $heritageName[] = strtolower(utf8_decode($orgArray[$parent][$nameTmp]));
                                if($parent2) {
                                    foreach($parent2 as $key2 => $value2) {
                                        $heritage2[$value1][] = $value2;
                                        $heritageName2[strtolower($orgArray[$value1][$nameTmp])][] = strtolower($orgArray[$value2][$nameTmp]);
                                    }
                                }
                            }
                            $parent2 = $heritage2Array[$parent];
                            $parent = $heritageArray[$parent];
                            if($parent) {
                                $heritage[] = $parent;
                                $heritageName[] = strtolower(utf8_decode($orgArray[$parent][$nameTmp]));
                                if($parent2) {
                                    foreach($parent2 as $key2 => $value2) {
                                        $heritage2[$value1][] = $value2;
                                        $heritageName2[strtolower($orgArray[$value1][$nameTmp])][] = strtolower($orgArray[$value2][$nameTmp]);
                                    }
                                }
                            }
                            $parent2 = $heritage2Array[$parent];
                            $parent = $heritageArray[$parent];
                            if($parent) {
                                $heritage[] = $parent;
                                $heritageName[] = strtolower(utf8_decode($orgArray[$parent][$nameTmp]));
                                if($parent2) {
                                    foreach($parent2 as $key2 => $value2) {
                                        $heritage2[$value1][] = $value2;
                                        $heritageName2[strtolower($orgArray[$value1][$nameTmp])][] = strtolower($orgArray[$value2][$nameTmp]);
                                    }
                                }
                            }
                            $parent2 = $heritage2Array[$parent];
                            $parent = $heritageArray[$parent];
                            if($parent) {
                                $heritage[] = $parent;
                                $heritageName[] = strtolower(utf8_decode($orgArray[$parent][$nameTmp]));
                                if($parent2) {
                                    foreach($parent2 as $key2 => $value2) {
                                        $heritage2[$value1][] = $value2;
                                        $heritageName2[strtolower($orgArray[$value1][$nameTmp])][] = strtolower($orgArray[$value2][$nameTmp]);
                                    }
                                }
                            }
                            $parent2 = $heritage2Array[$parent];
                            $parent = $heritageArray[$parent];
                            if($parent) {
                                $heritage[] = $parent;
                                $heritageName[] = strtolower(utf8_decode($orgArray[$parent][$nameTmp]));
                                if($parent2) {
                                    foreach($parent2 as $key2 => $value2) {
                                        $heritage2[$value1][] = $value2;
                                        $heritageName2[strtolower($orgArray[$value1][$nameTmp])][] = strtolower($orgArray[$value2][$nameTmp]);
                                    }
                                }
                            }
                            $parent2 = $heritage2Array[$parent];
                            $parent = $heritageArray[$parent];
                            if($parent) {
                                $heritage[] = $parent;
                                $heritageName[] = strtolower(utf8_decode($orgArray[$parent][$nameTmp]));
                                if($parent2) {
                                    foreach($parent2 as $key2 => $value2) {
                                        $heritage2[$value1][] = $value2;
                                        $heritageName2[strtolower($orgArray[$value1][$nameTmp])][] = strtolower($orgArray[$value2][$nameTmp]);
                                    }
                                }
                            }
                            $parent2 = $heritage2Array[$parent];
                            $parent = $heritageArray[$parent];
                            if($parent) {
                                $heritage[] = $parent;
                                $heritageName[] = strtolower(utf8_decode($orgArray[$parent][$nameTmp]));
                                if($parent2) {
                                    foreach($parent2 as $key2 => $value2) {
                                        $heritage2[$value1][] = $value2;
                                        $heritageName2[strtolower($orgArray[$value1][$nameTmp])][] = strtolower($orgArray[$value2][$nameTmp]);
                                    }
                                }
                            }
                            $parent2 = $heritage2Array[$parent];
                            $parent = $heritageArray[$parent];
                            if($parent) {
                                $heritage[] = $parent;
                                $heritageName[] = strtolower(utf8_decode($orgArray[$parent][$nameTmp]));
                                if($parent2) {
                                    foreach($parent2 as $key2 => $value2) {
                                        $heritage2[$value1][] = $value2;
                                        $heritageName2[strtolower($orgArray[$value1][$nameTmp])][] = strtolower($orgArray[$value2][$nameTmp]);
                                    }
                                }
                            }
                        }

                        array_filter($heritage);
                        array_filter($heritageName);

                        $standard_category_sv = array();
                        $standard_category_en = array();
                        $standardCategory = array();
                        $title = '';
                        $organisationName = '';
                        //$titleArray = explode('###', $value['title']);
                        $titleArray = $value['title'];
                        //$title_enArray = explode('###', $value['title_en']);
                        $title_enArray = $value['title_en'];
                        foreach($titleArray as $tkey => $tvalue) {
                            if($categoriesArray[$tvalue][0]) $standard_category_sv[] = $categoriesArray[$tvalue][0];
                            if($categoriesArray[$tvalue][1]) $standard_category_en[] = $categoriesArray[$tvalue][1];
                        }
                        if(!$standard_category_sv) $standard_category_sv[] = 'Övriga';
                        if(!$standard_category_en) $standard_category_en[] = 'Other';
                        
                        if($syslang==="sv") {
                            $standardCategory = $standard_category_sv;
                            $title = $titleArray;
                            $organisationName = $value['oname'];
                        } else {
                            $standardCategory = $standard_category_en;
                            $title = $title_enArray;
                            $organisationName = $value['oname_en'];
                        }

                        $data = array(
                            'appKey' => 'lthsolr',
                            'id' => $value['id'],
                            'primaryUid' => $key,
                            'docType' => 'staff',
                            'type' => 'staff',
                            'name' => $value['first_name'] . ' ' . $value['last_name'],
                            'firstLetter' => mb_substr($value['last_name'],0,1),
                            'firstName' => $value['first_name'],
                            'lastName' => $value['last_name'],
                            'email' => $value['email'],
                            'primaryAffiliation' => $value['primary_affiliation'],
                            'primaryVroleOu' => $value['primary_vrole_ou'],
                            'primaryVroleTitle' => $value['primary_vrole_title'],
                            'primaryVroleOrgid' => $value['primary_vrole_orgid'],
                            'primaryVrolePhone' => $value['primary_vrole_phone'],
                            'homepage' => $value['homepage'],
                            'language' => $value['lang'],
                            'degree' => $value['degree'],
                            'standardCategory' => $standardCategory,
                            //arrays:
                            'guid' => $value['guid'],
                            'mailDelivery' => $value['maildelivery'],
                            'mobile' => $value['mobile'],
                            'organisationId' => $value['orgid'],
                            'organisationHideOnWeb' => $value['hide_on_web'],
                            'organisationLeaveOfAbsence' => $value['leave_of_absence'],
                            'organisationName' => $organisationName,
                            'organisationPhone' => $value['ophone'],
                            'organisationStreet' => $value['ostreet'],
                            'organisationCity' => $value['ocity'],
                            'organisationPostalAddress' => $value['opostal_address'],
                            'phone' => $value['phone'],
                            'roomNumber' => $value['room_number'],
                            'title' => $title,
                            //extra:
                            'image' => $value['image'],
                            'imageId' => $value['image_id'],
                            'heritage' => $heritage,
                            'heritageName' => $heritageName,
                            'uuid' => $value['uuid'],
                            'lucrisPhoto' => $value['lucrisphoto'],
                            'profileInformationNovo' => $this->languageSelector($syslang, $value['profileInformation_en'], $value['profileInformation_sv']),
                            'coordinates' => $value['coordinates'],
                            'boost' => '1.0',
                            'date' => $current_date,
                            'changed' => $current_date,
                            'digest' => md5($key)
                        );

                        $GLOBALS['TYPO3_DB']->exec_UPDATEquery('fe_users', "username='".$key."'", array('lth_solr_heritage' => implode(',', $heritage)));

                        if($heritage2) {
                            $data['heritage2'] = json_encode($heritage2);
                        }
                        
                        if($heritageName2) {
                            $data['heritageName2'] = json_encode($heritageName2);
                        }
                        
                        if(is_array($value['lth_solr_cat'])) {
                            foreach($value['lth_solr_cat'] as $key1 => $value1) {
                                $data[$key1] = $value1;
                            }
                        }

                        if(is_array($value['lth_solr_intro'])) {
                            foreach($value['lth_solr_intro'] as $key2 => $value2) {
                                $data[$key2] = $value2;
                            }
                        }

                        if(is_array($value['lth_solr_sort'])) {
                            foreach($value['lth_solr_sort'] as $key3 => $value3) {
                                $data[$key3] = $value3;
                            }
                        }
                        
                        if($value['lth_solr_show']) {
                            $lth_solr_showArray = json_decode($value['lth_solr_show'],true);
                            foreach($lth_solr_showArray as $showKey => $showValue) {
                                $data[$showValue] = 1;
                            }
                        }
                        
                        $data['disable_intS'] = 0;
                        $buffer->createDocument($data);
                    }
                } 
                // this executes the query and returns the result
                $buffer->commit();

                $update->addCommit();
                $client->update($update);
                return TRUE;
            } else {
                echo 'no!!';
            }
        } catch(Exception $e) {
            echo 'Message: ' .$e->getMessage();
            return false;
        }
    }
    
    
    function languageSelector($syslang, $value_en, $value_sv)
    {
        if($value_en || $value_sv) {
            if($syslang==="sv") {
                $value = $value_sv;
            } else if($syslang==="en") {
                $value = $value_en;
            }

            if(!$value && $value_en) {
                $value = $value_en;
            }
            if(!$value && $value_sv) {
                $value = $value_sv;
            }
            return trim($value);
        } else {
            return false;
        }
    }
    
    
    /*private function fixArray($inputArray)
    {
        if($inputArray) {
            if(is_array($inputArray)) {
                $inputArray = array_unique($inputArray);
            }
        }
        return $inputArray;
    }*/
    
    
    private function getUids($inputArray, $feGroupsArray)
    {
        //print_r($feGroupsArray);
        //echo $inputString;
        if($inputArray) {
            //$loopArray = explode('###', $inputString);
            $resArray = array();
            foreach($inputArray as $key => $value) {
                //echo $value;$feGroupsArray['usergroup']
                $group = $feGroupsArray[$value];
                
                $resArray['usergroup'][] = $group['uid'];
                $resArray['pid'][] = $group['pid'];
            }
            $usergroup =implode(',', $resArray['usergroup']);
            if(!$usergroup) {
                $usergroup = '';
            }
            $pid = array_shift($resArray['pid']);
            return array($usergroup, $pid);
        } else {
            return '';
        }
    }
    
    
    private function getCoordinates()
    {
        /*
         * 
         v1000621",
          "v1000623
         */
        $coordinates = array(
            "v1000201" => "55.711106,13.210369",
            "v1001206" => "55.711106,13.210369",
            "v1001155" => "55.713790,13.212870",
            "v1001112" => "55.714700,13.212767",
            "v1001106" => "55.711106,13.210369",
            "v1001105" => "55.711106,13.210369",
            "v1001103" => "55.711106,13.210369",
            "v1001102" => "55.711106,13.210369",
            "v1001101" => "55.711106,13.210369",
            "v1001100" => "55.711106,13.210369",
            "v1000270" => "55.711568,13.209549",
            "v1001207" => "55.711106,13.210369",
            "v1000213" => "55.713790,13.212870",
            "v1000262" => "56.077386,13.228442",
            "v1000202" => "55.711106,13.210369",
            "v1000277" => "55.711106,13.210369",
            "v1000245" => "55.715765,13.210117",
            "v1000295" => "55.714700,13.212767",
            "v1000298" => "55.714700,13.212767",
            "v1000306" => "55.714700,13.212767",
            "v1000254" => "55.712544,13.211670",
            "v1000948" => "55.712544,13.211670",
            "v1000183" => "55.712544,13.211670",
            "v1001138" => "55.712544,13.211670",
            "v1000233" => "55.712544,13.211670",
            "v1000219" => "55.712544,13.211670",
            "v1000228" => "55.712544,13.211670",
            "v1000226" => "55.712544,13.211670",
            "v1000220" => "55.712544,13.211670",
            "v1000223" => "55.712544,13.211670",
            "v1000221" => "55.712544,13.211670",
            "v1000222" => "55.712544,13.211670",
            "v1000224" => "55.712544,13.211670",
            "v1000257" => "55.712544,13.211670",
            "v1000256" => "55.712544,13.211670",
            "v1000225" => "55.712544,13.211670",
            "v1000170" => "55.712472,13.209144",
            "v1000268" => "55.712472,13.209144",
            "v1000265" => "55.712472,13.209144",
            "v1000267" => "55.712472,13.209144",
            "v1000263" => "55.712472,13.209144",
            "v1000272" => "55.712472,13.209144",
            "v1000273" => "55.712472,13.209144",
            "v1000953" => "55.711568,13.209549",
            "v1000920" => "55.710211,13.219709",
            "v1000212" => "55.710211,13.219709",
            "v1000243" => "55.715765,13.210117",
            "v1000264" => "55.713220,13.210425",
            "v1000231" => "55.709569,13.209803",
            "v1000209" => "55.709569,13.209803",
            "v1000211" => "55.709569,13.209803",
            "v1000260" => "55.709569,13.209803",
            "v1000203" => "55.709569,13.209803",
            "v1000249" => "55.709569,13.209803",
            "v1000251" => "55.709569,13.209803",
            "v1000252" => "55.709569,13.209803",
            "v1000259" => "55.709569,13.209803",
            "v1000206" => "55.709569,13.209803",
            "v1000208" => "55.709569,13.209803",
            "v1000205" => "55.709569,13.209803",
            "v1000210" => "55.709569,13.209803",
            "v1000207" => "55.709569,13.209803",
            "v1000248" => "55.709569,13.209803",
            "v1000253" => "55.709569,13.209803",
            "v1000242" => "55.711106,13.210369",
            "v1000234" => "55.711106,13.210369",
            "v1000939" => "55.711106,13.210369",
            "v1000255" => "55.711106,13.210369",
            "v1000190" => "55.710321,13.200762",
            "v1000621" => "55.710321,13.200762",//fysik
            "v1000623" => "55.710321,13.200762",//
            "v1000174" => "55.710321,13.200762",
            "v1000274" => "55.712472,13.209144",
            "v1000217" => "55.713790,13.212870",
            "v1000215" => "55.713790,13.212870",
            "v1000216" => "55.713790,13.212870",
            "v1000241" => "55.714700,13.212767",
            "v1000235" => "55.714700,13.212767",
            "v1000236" => "55.714700,13.212767",
            "v1000238" => "55.714700,13.212767",
            "v1000240" => "55.714700,13.212767",
            "v1000944" => "55.714700,13.212767",
            "v1000237" => "55.714700,13.212767",
            "v1000239" => "55.714700,13.212767",
            "v1000921" => "55.715765,13.210117",
            "v1000278" => "56.038641,12.698769",
            "v1000682" => "56.038641,12.698769",
            "v1000261" => "56.038641,12.698769",
            "v1000286" => "55.711106,13.210369",
            "v1000291" => "55.711106,13.210369",
            "v1000287" => "55.711106,13.210369",
            "v1000303" => "55.711106,13.210369",
            "v1000296" => "55.711106,13.210369",
            "v1000283" => "55.711106,13.210369",
            "v1000311" => "55.711106,13.210369",
            "v1000304" => "55.711106,13.210369",
            "v1000290" => "55.711106,13.210369",
            "v1000668" => "55.710466,13.205075",//matte
            "v1000665" => "55.710466,13.205075"//
        );
        return $coordinates;
    }
    
    
    private function getGroupName($uid)
    {
        $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('title','fe_groups','uid=' . intval($uid));
        $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
        $GLOBALS['TYPO3_DB']->sql_free_result($res);
        return $row['title'];
    }
    
    
    private function toUC($in)
    {
        if($in) {
            $in = str_replace('-', ' - ', $in);
            //$in = ucwords(utf8_encode($in));
            $in = mb_convert_case($in, MB_CASE_TITLE, "UTF-8");
            $in = str_replace(' - ', '-', $in);
        }
        return $in;
    }
    
    /**
        * Defines a random password.
        *
        * @return string
        */
       static public function setRandomPassword() {
               /** @var \TYPO3\CMS\Saltedpasswords\Salt\SaltInterface $instance */
               $instance = NULL;
               if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('saltedpasswords')) {
                       $instance = \TYPO3\CMS\Saltedpasswords\Salt\SaltFactory::getSaltingInstance(NULL, TYPO3_MODE);
               }
               $password = GeneralUtility::generateRandomBytes(16);
               $password = $instance ? $instance->getHashedPassword($password) : md5($password);
               return $password;
       }
    
    
    /*public function traverse($i_id)
    {
        //echo $i_id . '; ';
        $i_lft = $this->i_count;
        $this->i_count++;

        $a_kid = $this->get_children($i_id);

        if ($a_kid) {
            foreach($a_kid as $a_child) {
                $this->traverse($a_child);
            }
        }
        $i_rgt = $this->i_count;
        $this->i_count++;
        $this->write($i_lft, $i_rgt, $i_id);
    }
    
    
    private function get_children($i_id) 
    {
        if ( ! isset($this->a_link[$i_id])) {
            $this->a_link[$i_id] = null;
        }

        return $this->a_link[$i_id];
    }*/
}
