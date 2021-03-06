<?php

ini_set('memory_limit', '-1');
error_reporting(E_ERROR);
set_time_limit(0);

use TYPO3\CMS\Core\Utility\GeneralUtility;

class tx_lthsolr_lucacheimport extends \TYPO3\CMS\Scheduler\Task\AbstractTask {
	
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

	if (!$settings['solrHost'] || !$settings['solrPort'] || !$settings['solrPath'] || !$settings['solrTimeout'] || !$settings['dbhost'] || !$settings['db'] || !$settings['grsp'] || !$settings['user'] || !$settings['pw']) {
	    return 'Please make all settings in extension manager';
	}
                
        $grsp = $settings['grsp'];

        //tslib_eidtools::connectDB();

        $dbhost = $settings['dbhost'];
        $db = $settings['db'];
        $user = $settings['user'];
        $pw = $settings['pw'];

        $con = mysqli_connect($dbhost, $user, $pw, $db) or die("57; ".mysqli_error());
       
        $employeeArray = $this->getEmployee($con);

        $employeeArray = $this->getLucrisData($employeeArray);

        $folderArray = $this->getFolderStructure($grsp);
        
        $feGroupsArray = $this->getFeGroups();

        $employeeArray = $this->getFeUsers($employeeArray);
               
        $orgArray = $this->getOrg($con);
                
        $heritageTempArray = $this->getHeritage($con);
        $heritageArray = $heritageTempArray[0];
        $heritageLegacyArray = $heritageTempArray[1];
        
        $categoriesArray = $this->getCategories();

        $this->createFolderStructure($grsp, $folderArray, $orgArray);
        
        $folderArray = $this->getFolderStructure($grsp);
        
        $this->createFeGroups($folderArray, $orgArray, $feGroupsArray);
        
        $feGroupsArray = $this->getFeGroups();
        
        $employeeArray = $this->createFeUsers($folderArray, $employeeArray, $feGroupsArray);

        $executionSucceeded = $this->updateSolr($employeeArray, $heritageArray, $heritageLegacyArray, $categoriesArray, $config);
        
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
    
    
    private function getEmployee($con)
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
            P.homepage, 
            P.lang, 
            P.degree, 
            P.degree_en,
            P.primary_lu_email,
            NOT P.has_primary_vrole AS hide_on_web,
            V.update_flag,
            V.guid,
            GROUP_CONCAT(V.room_number) AS room_number,
            GROUP_CONCAT(V.title) AS title,
            GROUP_CONCAT(V.title_en) AS title_en,
            GROUP_CONCAT(V.phone) AS phone,
            GROUP_CONCAT(V.mobile) AS mobile,
            V.orgid AS orgid,
            VORG.legacy_orgid AS orgid_legacy,
            VORG.name AS oname,
            VORG.name_en AS oname_en,
            VORG.maildelivery AS maildelivery,
            VORG.phone AS ophone,
	    VORG.street AS ostreet,
            VORG.city AS ocity,
            VORG.postal_address AS opostal_address
            FROM lucache_person AS P 
            LEFT JOIN lucache_vrole AS V ON P.id = V.id AND V.primary_role = 1 
            LEFT JOIN lucache_vorg VORG ON V.orgid = VORG.orgid
            WHERE primary_affiliation = 'employee' 
            GROUP BY P.id, V.orgid 
            ORDER BY P.id, V.orgid";
        
        $res = mysqli_query($con, $sql) or die("157; ".mysqli_error());

        while ($row = mysqli_fetch_array($res, MYSQLI_ASSOC)) {
            $primary_uid = $row['primary_uid'];
            $employeeArray[$primary_uid]['id'] = $row['id'];
            $employeeArray[$primary_uid]['primary_uid'] = $primary_uid;
            $employeeArray[$primary_uid]['first_name'] = $this->toUC($row['first_name']);
            $employeeArray[$primary_uid]['last_name'] = $this->toUC($row['last_name']);
            $employeeArray[$primary_uid]['email'] = $row['primary_lu_email'];
            $employeeArray[$primary_uid]['primary_affiliation'] = $row['primary_affiliation'];
            $employeeArray[$primary_uid]['homepage'] = $row['homepage'];
            $employeeArray[$primary_uid]['lang'] = $row['lang'];
            $employeeArray[$primary_uid]['degree'] = utf8_encode($row['degree']);
            $employeeArray[$primary_uid]['degree_en'] = utf8_encode($row['degree_en']);
            $employeeArray[$primary_uid]['hide_on_web'] = $row['hide_on_web'];
            $employeeArray[$primary_uid]['update_flag'] = $row['update_flag'];
            $employeeArray[$primary_uid]['guid'] = $row['guid'];
            $employeeArray[$primary_uid]['ophone'] = $row['ophone'];
	    $employeeArray[$primary_uid]['ostreet'] = utf8_encode($row['ostreet']);
            $employeeArray[$primary_uid]['ocity'] = utf8_encode($row['ocity']);
            $employeeArray[$primary_uid]['opostal_address'] = utf8_encode($row['opostal_address']);

            //arrays:
            $employeeArray[$primary_uid]['room_number'][] = $row['room_number'];
            $employeeArray[$primary_uid]['title'][] = utf8_encode($row['title']);
            $employeeArray[$primary_uid]['title_en'][] = utf8_encode($row['title_en']);
            $employeeArray[$primary_uid]['phone'][] = $row['phone'];
            $employeeArray[$primary_uid]['mobile'][] = $row['mobile'];
            $employeeArray[$primary_uid]['orgid'][] = $row['orgid'];
            $employeeArray[$primary_uid]['orgid_legacy'][] = $row['orgid_legacy'];
            $employeeArray[$primary_uid]['oname'][] = utf8_encode($row['oname']);
            $employeeArray[$primary_uid]['oname_en'][] = utf8_encode($row['oname_en']);
            $employeeArray[$primary_uid]['maildelivery'][] = $row['maildelivery'];
            
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

            if($lucris_profile_information) {
                $profileInformationArray = json_decode($lucris_profile_information, true);
                $profileInformation_sv = $profileInformationArray['sv'];
                $profileInformation_en = $profileInformationArray['en'];
            } else {
                $profileInformation_sv = '';
                $profileInformation_en = '';
            }
            if(array_key_exists($typo3_id, $employeeArray)) {
                $employeeArray[$typo3_id]['uuid'] = $lucris_id;
                $employeeArray[$typo3_id]['lucrisphoto'] = $lucrisphoto;
                $employeeArray[$typo3_id]['profileInformation_sv'] = $profileInformation_sv;
                $employeeArray[$typo3_id]['profileInformation_en'] = $profileInformation_en;
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
        $heritageLegacyArray = array();
        
        $sql = "SELECT orgid, parent, legacy_orgid, legacy_parent FROM lucache_vorg";
        
        $res = mysqli_query($con, $sql);
        
        while ($row = mysqli_fetch_array($res, MYSQLI_ASSOC)) {
            $heritageArray[$row['orgid']] = $row['parent'];
            $heritageLegacyArray[$row['legacy_orgid']] = $row['legacy_parent'];
        }
        //$this->debug($heritageLegacyArray);
        return array($heritageArray, $heritageLegacyArray);
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
    
    
    private function getFeGroups()
    {
        $feGroupsArray = array();
        $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid, pid, title, subgroup', 'fe_groups', 'deleted = 0');
        while ($row = $GLOBALS["TYPO3_DB"]->sql_fetch_assoc($res)) {
            $titleArray = explode('__', $row['title']);
            if(is_array($titleArray)) {
                $feGroupsArray[$titleArray[0]] = array('title' => $row['title'], 'uid' => $row['uid'], 'pid' => $row['pid'], 'subgroup' => $row['subgroup']);
            }
        }
        return $feGroupsArray;
    }
    
    
    private function getFeUsers($employeeArray)
    {
        $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('DISTINCT username, usergroup, image, image_id, lth_solr_cat, '
                . 'lth_solr_sort, lth_solr_intro, lth_solr_autohomepage, lth_solr_show', 'fe_users', 'deleted = 0');
        while ($row = $GLOBALS["TYPO3_DB"]->sql_fetch_assoc($res)) {
            $username = $row['username'];
            $lth_solr_cat = $row['lth_solr_cat'];
            $lth_solr_intro = $row['lth_solr_intro'];
            $lth_solr_sort = $row['lth_solr_sort'];
            $lth_solr_autohomepage = $row['lth_solr_autohomepage'];
            $lth_solr_show = $row['lth_solr_show'];
            
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
                            //$GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_devlog', array('msg' => $key.';'.$value, 'crdate' => time()));
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
                
                if($lth_solr_autohomepage && $lth_solr_autohomepage !== '') {
                    $lth_solr_autohomepage = json_decode($lth_solr_autohomepage, true);
                    if($lth_solr_autohomepage) {
                        foreach($lth_solr_autohomepage as $key => $value) {
                            $employeeArray[$username]['lth_solr_autohomepage'][$key] = $value;
                        }
                    }
                }
                
                $employeeArray[$username]['lth_solr_show'] = $lth_solr_show;
                $employeeArray[$username]['usergroup'] = $row['usergroup'];
                $employeeArray[$username]['image'] = $row['image'];
                $employeeArray[$username]['image_id'] = $row['image_id'];
                $employeeArray[$username]['exist'] = TRUE;
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
    
    
    private function createFeGroups($folderArray, $orgArray, $feGroupsArray)
    {
        $tmpTitle = '';
        $tmpKeyArray = array();
        $pidArray = array();

        foreach($orgArray as $key => $value) {
            $tmpTitle = $value['orgid'] . '__' . $value['name'];
            //print_r($feGroupsArray);
            if(!array_key_exists($value['orgid'], $feGroupsArray)) {
                $folder = $folderArray[$value['orgid']];
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
                    'subgroup' => $feGroupsArray[$value['parent']]['uid'],
                    'tstamp' => time())
                );
            }
        }
    }
    
    
    private function createFeUsers($folderArray, $employeeArray, $feGroupsArray)
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
            //echo $value['usergroup'];
            //echo $usergroupArray['pid'];
            //echo $usergroupArray['usergroup'];
            if($usergroupArray[0]) {
                $ugFe = $value['usergroup'];
                if($ugFe) {
                    $ugFeArray = explode(',', $ugFe);
                    foreach($ugFeArray as $keyF =>$valueF) {
                        if($valueF != $usergroupArray[0]) {
                            $employeeArray[$key]['extra_orgid'][] = explode('__', $this->getGroupName($valueF))[0];
                        }
                    }
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
                    if(!$value['roomnumber']) $value['roomnumber'] = '';
                    $updateArray = array(
                        'pid' => $usergroupArray[1],
                        'usergroup' => $usergroupArray[0],
                        'first_name' => $value['first_name'],
                        'last_name' => $value['last_name'],
                        'title' => $title,
                        'name' => $value['last_name'] . ', ' . $value['first_name'],
                        'email' => $value['email'],
                        'www' => (string)$value['homepage'],
                        'telephone' => $phone,
                        'roomnumber' => $room_number,
                        'hide_on_web' => $value['hide_on_web'],
                        'lth_solr_uuid' => (string)$value['uuid'],
                        'lucache_id' => $lucache_id,
                        'tstamp' => time()
                    );
                    
                    //$this->debug($updateArray);
                    //echo '443';
                    $GLOBALS['TYPO3_DB']->exec_UPDATEquery('fe_users', "username = '" . $value['primary_uid'] . "'", $updateArray);
                    //echo $GLOBALS['TYPO3_DB']->debug_lastBuiltQuery;
                    /*$employeeArray[$key]['image'] = $feUsersArray[$key]['image'];
                    $employeeArray[$key]['lth_solr_intro'] = $feUsersArray[$key]['lth_solr_intro'];
                    $employeeArray[$key]['lth_solr_txt'] = $feUsersArray[$key]['lth_solr_txt'];*/
                } else if($usergroupArray[1]) {
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
                        'hide_on_web' => $value['hide_on_web'],
                        'lth_solr_uuid' => (string)$value['uuid'],
                        'lucache_id' => $lucache_id,
                        'crdate' => time(), 
                        'tstamp' => time()
                    );
                    //$this->debug($insertArray);
                    //$GLOBALS['TYPO3_DB']->store_lastBuiltQuery = 1;
                    $GLOBALS['TYPO3_DB']->exec_INSERTquery('fe_users', $insertArray);
                    //echo $GLOBALS['TYPO3_DB']->debug_lastBuiltQuery;
                    //echo '471';
                }
            } 
        }
        return $employeeArray;
    }
    
    
    private function updateSolr($employeeArray, $heritageArray, $heritageLegacyArray, $categoriesArray, $config)
    {
        //$this->debug($employeeArray);
        //echo count($employeeArray);
        try {
            if(count($employeeArray) > 0) {
                $current_date = gmDate("Y-m-d\TH:i:s\Z");
                
                //create a client instance
                $client = new Solarium\Client($config);
                
                $buffer = $client->getPlugin('bufferedadd');
                $buffer->setBufferSize(250);
                
                foreach($employeeArray as $key => $value) {
                    if($value['id']) {
                        $heritage = array();
                        $heritage2 = array();
                        $legacy = array();

                        //$orgidArray = explode('###', $value['orgid']);
                        $orgidArray = $value['orgid'];
                        foreach($orgidArray as $key1 => $value1) {
                            $heritage[] = $value1;
                            $heritage2[$value1] = $value1;
                            $parent = $heritageArray[$value1];
                            if($parent) { 
                                $heritage[] = $parent;
                                $heritage2[$value1] .= ',' . $parent;
                            }
                            $parent = $heritageArray[$parent];
                            if($parent) {
                                $heritage[] = $parent;
                                $heritage2[$value1] .= ',' . $parent;
                            }
                            $parent = $heritageArray[$parent];
                            if($parent) {
                                $heritage[] = $parent;
                                $heritage2[$value1] .= ',' . $parent;
                            }
                            $parent = $heritageArray[$parent];
                            if($parent) {
                                $heritage[] = $parent;
                                $heritage2[$value1] .= ',' . $parent;
                            }
                            $parent = $heritageArray[$parent];
                            if($parent) {
                                $heritage[] = $parent;
                                $heritage2[$value1] .= ',' . $parent;
                            }
                            $parent = $heritageArray[$parent];
                            if($parent) {
                                $heritage[] = $parent;
                                $heritage2[$value1] .= ',' . $parent;
                            }
                            $parent = $heritageArray[$parent];
                            if($parent) {
                                $heritage[] = $parent;
                                $heritage2[$value1] .= ',' . $parent;
                            }
                            $parent = $heritageArray[$parent];
                            if($parent) {
                                $heritage[] = $parent;
                                $heritage2[$value1] .= ',' . $parent;
                            }
                            $parent = $heritageArray[$parent];
                            if($parent) {
                                $heritage[] = $parent;
                                $heritage2[$value1] .= ',' . $parent;
                            }
                        }

                        //$orgidLegacyArray = explode('###', $value['orgid_legacy']);
                        $orgidLegacyArray = $value['orgid_legacy'];
                        foreach($orgidLegacyArray as $key1 => $value1) {
                            $legacy[] = $value1;
                            $parent = $heritageLegacyArray[$value1];
                            if($parent) $legacy[] = $parent;
                            $parent = $heritageLegacyArray[$parent];
                            if($parent) $legacy[] = $parent;
                            $parent = $heritageLegacyArray[$parent];
                            if($parent) $legacy[] = $parent;
                            $parent = $heritageLegacyArray[$parent];
                            if($parent) $legacy[] = $parent;
                            $parent = $heritageLegacyArray[$parent];
                            if($parent) $legacy[] = $parent;
                            $parent = $heritageLegacyArray[$parent];
                            if($parent) $legacy[] = $parent;
                            $parent = $heritageLegacyArray[$parent];
                            if($parent) $legacy[] = $parent;
                            $parent = $heritageLegacyArray[$parent];
                            if($parent) $legacy[] = $parent;
                            $parent = $heritageLegacyArray[$parent];
                            if($parent) $legacy[] = $parent;
                        }

                        array_filter($heritage);
                        array_filter($legacy);

                        $heritage = array_unique($heritage);
                        $legacy = array_unique($legacy);
                        
                        if($value['extra_orgid']) {
                            $value['orgid'] = array_unique(array_merge($value['orgid'], $value['extra_orgid']));
                        }

                        $display_name_t = $value['first_name'] . ' ' . $value['last_name'];
                        $homepage = $value['homepage'];
                        /*if(!$homepage || $homepage === '') {
                            $homepage = str_replace(' ', '_', $display_name_t);
                        }*/

                        $standard_category_sv = array();
                        $standard_category_en = array();
                        //$titleArray = explode('###', $value['title']);
                        $titleArray = $value['title'];
                        //$title_enArray = explode('###', $value['title_en']);
                        $title_enArray = $value['title_en'];
                        foreach($titleArray as $tkey => $tvalue) {
                            $standard_category_sv[] = $categoriesArray[$tvalue][0];
                            $standard_category_en[] = $categoriesArray[$tvalue][1];
                        }

                        //echo $value['id'].',';
                        $data = array(
                            /*'id' => $key,
                            'doctype_s' => 'lucat',
                            'display_name_t' => $display_name_t,
                            'first_name_t' => $value['first_name'],
                            'last_name_t' => $value['last_name'],
                            'first_name_s' => $value['first_name'],
                            'last_name_s' => $value['last_name'],
                            'email_t' => $value['email'],
                            'primary_affiliation_t' => $value['primary_affiliation'],
                            'homepage_t' => strtolower($homepage),
                            'lang_t' => $value['lang'],
                            'degree_t' => $value['degree'],
                            'degree_en_t' => $value['degree_en'],                        
                            'hide_on_web_i' => intval($value['hide_on_web']),
                            'update_flag_i' => intval($value['update_flag']),
                            'title_sort' => explode('###', $value['title']),
                            'ou_sort' => explode('###', $value['oname']),
                            'guid_s' => $value['guid'],
                            'standard_category_sv_txt' => $standard_category_sv,
                            'standard_category_en_txt' => $standard_category_en,
                            //arrays:
                            'title_txt' => $titleArray,
                            'title_en_txt' => $title_enArray,
                            'phone_txt' => explode('###', $value['phone']),
                            'mobile_txt' => explode('###', $value['mobile']),
                            'room_number_s' => $value['room_number'],
                            'orgid_txt' => explode('###', $value['orgid']),
                            'oname_txt' => explode('###', $value['oname']),
                            'oname_en_txt' => explode('###', $value['oname_en']),
                            'maildelivery_txt' => explode('###', $value['maildelivery']),
                            //extra:
                            'image_s' => $value['image'],
                            'image_id_s' => $value['image_id'],
                            //'lth_solr_intro_txt' => $value['lth_solr_intro'],
                            //'lth_solr_txt_t' => $value['lth_solr_txt'],
                            'usergroup_txt' => $heritage,
                            'lth_solr_sort_ss' => $value['lth_solr_sort'],*/
                            //New
                            'id' => $value['id'],
                            'primary_uid' => $value['primary_uid'],
                            'doctype' => 'lucat',
                            'display_name' => $display_name_t,
                            'first_name' => $value['first_name'],
                            'last_name' => $value['last_name'],
                            'first_name_sort' => $value['first_name'],
                            'last_name_sort' => $value['last_name'],
                            'email' => $value['email'],
                            'primary_affiliation' => $value['primary_affiliation'],
                            'homepage' => strtolower($homepage),
                            'lang' => $value['lang'],
                            'degree' => $value['degree'],
                            'degree_en' => $value['degree_en'],                        
                            'hide_on_web' => intval($value['hide_on_web']),
                            'update_flag' => intval($value['update_flag']),
                            'title_sort' => $titleArray,
                            //'ou_sort' => $this->fixArray(explode('###', $value['oname'])),
                            'guid' => $value['guid'],
                            'standard_category_sv' => $standard_category_sv,
                            'standard_category_en' => $standard_category_en,
                            //arrays:
                            'title' => $titleArray,
                            'title_en' => $title_enArray,
                            'phone' => $value['phone'],
                            'mobile' => $value['mobile'],
                            'room_number' => $value['room_number'],
                            'orgid' => $value['orgid'],
                            'oname' => $value['oname'],
                            'oname_en' => $value['oname_en'],
                            'oname_sort' => $value['oname'],
                            'oname_sort_en' => $value['oname_en'],
                            'maildelivery' => $value['maildelivery'],
                            'ophone' => $value['ophone'],
                            'ostreet' => $value['ostreet'],
                            'ocity' => $value['ocity'],
                            'opostal_address' => $value['opostal_address'],
                            //extra:
                            'image' => $value['image'],
                            'image_id' => $value['image_id'],
                            //'ltholr_intro_txt' => $value['lth_solr_intro'],
                            //'lth_solr_txt_t' => $value['lth_solr_txt'],
                            //'usergroup' => $value['orgid'],
                            'heritage' => $heritage,
                            'uuid' => $value['uuid'],
                            'lucrisphoto' => $value['lucrisphoto'],
                            'profileInformation_en' => $value['profileInformation_en'],
                            'profileInformation_sv' => $value['profileInformation_sv'],
                            'boost' => '1.0',
                            'date' => $current_date,
                            'tstamp' => $current_date,
                            'digest' => md5($key)
                        );

                        $GLOBALS['TYPO3_DB']->exec_UPDATEquery('fe_users', "username='".$key."'", array('lth_solr_heritage' => implode(',', $heritage), 'lth_solr_legacy_heritage' => implode(',', $legacy)));

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
                        
                        if(is_array($value['lth_solr_autohomepage'])) {
                            foreach($value['lth_solr_autohomepage'] as $key4 => $value4) {
                                $data[$key4] = $value4;
                            }
                        }
                        
                        if($value['lth_solr_show']) {
                            $lth_solr_showArray = json_decode($value['lth_solr_show'],true);
                            foreach($lth_solr_showArray as $showKey => $showValue) {
                                $data[$showValue] = 1;
                            }
                        }

                        try {
                            $buffer->createDocument($data);

                        } catch(Exception $e) {
                            echo 'Message: ' .$e->getMessage();
                        }
                    }
                } 
                // this executes the query and returns the result
                $buffer->commit();
                return TRUE;
            } else {
                echo 'no!!';
            }
        } catch(Exception $e) {
            echo 'Message: ' .$e->getMessage();
            return false;
        }
    }
    
    
    private function fixArray($inputArray)
    {
        if($inputArray) {
            if(is_array($inputArray)) {
                $inputArray = array_unique($inputArray);
            }
        }
        return $inputArray;
    }
    
    
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
            $in = mb_convert_case(utf8_encode($in), MB_CASE_TITLE, "UTF-8");
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