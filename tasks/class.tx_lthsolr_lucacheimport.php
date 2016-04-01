<?php

ini_set('memory_limit', '-1');
error_reporting(E_ERROR);
set_time_limit(0);

use TYPO3\CMS\Core\Utility\GeneralUtility;

class tx_lthsolr_lucacheimport extends tx_scheduler_Task {
	
    function execute()
    {
	$executionSucceeded = FALSE;

	$executionSucceeded = $this->indexItems();
        
	return $executionSucceeded;
    }

    function indexItems()
    {
	require(__DIR__.'/init.php');
        
        if (file_exists(__DIR__.'/config.php')) {
            require(__DIR__.'/config.php');
        } else {
            die(__DIR__);
        }

        $grsp = $config['grsp'];

        tslib_eidtools::connectDB();

        $dbhost = "db.ddg.lth.se";
        $db = "users";
        $user = "lucache";
        $pw = "5ipsD3R2XA8wWEhm";

        $con = mysqli_connect($dbhost, $user, $pw, $db) or die("39; ".mysqli_error());
       
        $employeeArray = $this->getEmployee($con);

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
        echo '<pre>';
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
        
        $sql = "SELECT 
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
            V.room_number,
            GROUP_CONCAT(V.title SEPARATOR '###') AS title,
            GROUP_CONCAT(V.title_en SEPARATOR '###') AS title_en,
            GROUP_CONCAT(V.phone SEPARATOR '###') AS phone, 
            GROUP_CONCAT(V.mobile SEPARATOR '###') AS mobile,
            GROUP_CONCAT(V.orgid SEPARATOR '###') AS orgid,
            GROUP_CONCAT(VORG.legacy_orgid SEPARATOR '###') AS orgid_legacy,
            GROUP_CONCAT(O.name SEPARATOR '###') AS oname,
            GROUP_CONCAT(O.name_en SEPARATOR '###') AS oname_en,
            GROUP_CONCAT(O.maildelivery SEPARATOR '###') AS maildelivery
            FROM lucache_person AS P 
            LEFT JOIN lucache_vrole AS V ON P.id = V.id
            LEFT JOIN lucache_vorg AS O ON V.orgid = O.orgid
            LEFT JOIN lucache_vorg VORG ON V.orgid = VORG.orgid
            GROUP BY V.uid";
        
        $res = mysqli_query($con, $sql) or die("136; ".mysqli_error());

        while ($row = mysqli_fetch_array($res, MYSQLI_ASSOC)) {
            $employeeArray[$row['primary_uid']] = array(
                'uid' => $row['primary_uid'], 
                'first_name' => $this->toUC($row['first_name']),
                'last_name' => $this->toUC($row['last_name']), 
                'email' => $row['primary_lu_email'],
                'primary_affiliation' => $row['primary_affiliation'],
                'homepage' => $row['homepage'], 
                'lang' => $row['lang'], 
                'degree' => utf8_encode($row['degree']), 
                'degree_en' => utf8_encode($row['degree_en']),
                'hide_on_web' => $row['hide_on_web'],
                'update_flag' => $row['update_flag'],
                'guid' => $row['guid'],
                'room_number' => $row['room_number'],
                //arrays:
                'title' => utf8_encode($row['title']),
                'title_en' => utf8_encode($row['title_en']),
                'phone' => $row['phone'],
                'mobile' => $row['mobile'],
                'orgid' => $row['orgid'],
                'orgid_legacy' => $row['orgid_legacy'],
                'oname' => utf8_encode($row['oname']),
                'oname_en' => utf8_encode($row['oname_en']),
                'maildelivery' => $row['maildelivery']
            );
        }
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
        //$this->debug($heritageArray);
        return array($heritageArray, $heritageLegacyArray);
    }
    
    
    private function getCategories()
    {
        $categoriesArray = array();
        $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery("title_sv, name_sv, name_en", "tx_lthsolr_titles t JOIN tx_lthsolr_categories c ON t.category = c.id");
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
            $folderArray[$row['title']] = array('uid' => $row['uid'], 'title' => $row['title']);
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
        //$feUsersArray = array();
        $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('username, usergroup, image, image_id, lth_solr_cat, lth_solr_sort, lth_solr_intro', 'fe_users', 'deleted = 0');
        while ($row = $GLOBALS["TYPO3_DB"]->sql_fetch_assoc($res)) {
            $username = $row['username'];
            $lth_solr_cat = $row['lth_solr_cat'];
            $lth_solr_intro = $row['lth_solr_intro'];
            $$lth_solr_sort = $row['$lth_solr_sort'];
            
            if($lth_solr_cat && $lth_solr_cat !== '') {
                $lth_solr_cat = json_decode($lth_solr_cat, true);
                foreach($lth_solr_cat as $key => $value) {
                    $employeeArray[$username]['lth_solr_cat'][$key] = $value;
                }
            }
            
            if($lth_solr_intro && $lth_solr_intro !== '') {
                $lth_solr_intro = json_decode($lth_solr_intro, true);
                foreach($lth_solr_intro as $key => $value) {
                    $employeeArray[$username]['lth_solr_intro'][$key] = $value;
                }
            }
            
            if($lth_solr_sort && $lth_solr_sort !== '') {
                $lth_solr_sort = json_decode($lth_solr_sort, true);
                foreach($lth_solr_sort as $key => $value) {
                    $employeeArray[$username]['lth_solr_sort'][$key] = $value;
                }
            } 
            
            $employeeArray[$username]['usergroup'] = $row['usergroup']; 
            $employeeArray[$username]['image'] = $row['image'];
            $employeeArray[$username]['image_id'] = $row['image_id'];
            //$employeeArray[$username]['lth_solr_cat'] = $row['lth_solr_cat']; 
            $employeeArray[$username]['exist'] = TRUE;
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
            if(!array_key_exists($tmpTitle, $folderArray)) {
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
                
                $GLOBALS['TYPO3_DB']->exec_INSERTquery('fe_groups', array(
                    'title' => $tmpTitle,
                    'pid' => $folder['uid'],
                    //'subgroup' => $value['subgroup'],
                    'crdate' => time(), 
                    'tstamp' => time())
                );
            } else {
                $GLOBALS['TYPO3_DB']->exec_UPDATEquery('pages', 'title=' . $tmpTitle, array(
                    'subgroup' => $feGroupsArray[$value['parent']]['uid'],
                    'tstamp' => time())
                );
            }
        }
    }
    
    
    private function createFeUsers($folderArray, $employeeArray, $feGroupsArray)
    {
        foreach($employeeArray as $key => $value) {
            //echo $value['usergroup'];
            $usergroupArray = $this->getUids($value['orgid'], $feGroupsArray);
            //echo $value['usergroup'];
            //echo $usergroupArray['pid'];
            //echo $usergroupArray['usergroup'];
            if($usergroupArray[0]) {
                if($value['exist']===TRUE) {
                    //echo $value['uid'];
                    $updateArray = array(
                        'pid' => $usergroupArray[1],
                        'usergroup' => $usergroupArray[0],
                        'first_name' => $value['first_name'],
                        'last_name' => $value['last_name'],
                        'title' => $value['title'],
                        'name' => $value['last_name'] . ', ' . $value['first_name'],
                        'email' => $value['email'],
                        'www' => (string)$value['homepage'],
                        'hide_on_web' => $value['hide_on_web'],
                        'tstamp' => time()
                    );
                    $GLOBALS['TYPO3_DB']->exec_UPDATEquery('fe_users', "username = '".$value['uid'] . "'", $updateArray);
                    /*$employeeArray[$key]['image'] = $feUsersArray[$key]['image'];
                    $employeeArray[$key]['lth_solr_intro'] = $feUsersArray[$key]['lth_solr_intro'];
                    $employeeArray[$key]['lth_solr_txt'] = $feUsersArray[$key]['lth_solr_txt'];*/
                } else {
                    //echo $value['exist'];
                    $insertArray = array(
                        'username' => $value['uid'],
                        'password' => $this->setRandomPassword(),
                        'name' => $value['last_name'] . ', ' . $value['first_name'],
                        'title' => $value['title'],
                        'email' => $value['email'],
                        'www' => (string)$value['homepage'],
                        'pid' => $usergroupArray[1],
                        'usergroup' => $usergroupArray[0],
                        'hide_on_web' => $value['hide_on_web'],
                        'crdate' => time(), 
                        'tstamp' => time()
                    );
                    $GLOBALS['TYPO3_DB']->exec_INSERTquery('fe_users', $insertArray);                    
                }
            }
        }
        return $employeeArray;
    }
    
    
    private function updateSolr($employeeArray, $heritageArray, $heritageLegacyArray, $categoriesArray, $config)
    {
        //$this->debug($employeeArray);
        try {
            if(count($employeeArray) > 0) {
                //create a client instance
                $client = new Solarium\Client($config);
                
                $buffer = $client->getPlugin('bufferedadd');
                $buffer->setBufferSize(250);
                
                foreach($employeeArray as $key => $value) {
                    $heritage = array();
                    $legacy = array();
                    
                    $orgidArray = explode('###', $value['orgid']);
                    foreach($orgidArray as $key1 => $value1) {
                        $heritage[] = $value1;
                        $parent = $heritageArray[$value1];
                        if($parent) $heritage[] = $parent;
                        $parent = $heritageArray[$parent];
                        if($parent) $heritage[] = $parent;
                        $parent = $heritageArray[$parent];
                        if($parent) $heritage[] = $parent;
                        $parent = $heritageArray[$parent];
                        if($parent) $heritage[] = $parent;
                        $parent = $heritageArray[$parent];
                        if($parent) $heritage[] = $parent;
                        $parent = $heritageArray[$parent];
                        if($parent) $heritage[] = $parent;
                        $parent = $heritageArray[$parent];
                        if($parent) $heritage[] = $parent;
                        $parent = $heritageArray[$parent];
                        if($parent) $heritage[] = $parent;
                        $parent = $heritageArray[$parent];
                        if($parent) $heritage[] = $parent;
                    }
                    
                    $orgidLegacyArray = explode('###', $value['orgid_legacy']);
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

                    $display_name_t = $value['first_name'] . ' ' . $value['last_name'];
                    $homepage = $value['homepage'];
                    /*if(!$homepage || $homepage === '') {
                        $homepage = str_replace(' ', '_', $display_name_t);
                    }*/
                    
                    $standard_category_sv = array();
                    $standard_category_en = array();
                    $titleArray = explode('###', $value['title']);
                    $title_enArray = explode('###', $value['title_en']);
                    foreach($titleArray as $tkey => $tvalue) {
                        $standard_category_sv[] = $categoriesArray[$tvalue][0];
                        $standard_category_en[] = $categoriesArray[$tvalue][1];
                    }
                    
                    $data = array(
                        'id' => $key,
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
                        //'lth_solr_intro_t' => $value['lth_solr_intro'],
                        //'lth_solr_txt_t' => $value['lth_solr_txt'],
                        'usergroup_txt' => $heritage,
                        'lth_solr_sort_ss' => $value['lth_solr_sort'],
                    );
                    
                    $GLOBALS['TYPO3_DB']->exec_UPDATEquery('fe_users', "username='".$key."'", array('lth_solr_heritage' => implode(',', $heritage), 'lth_solr_legacy_heritage' => implode(',', $legacy)));

                    if(is_array($value['lth_solr_cat'])) {
                        foreach($value['lth_solr_cat'] as $key => $value) {
                            $data[$key] = $value;
                        }
                    }
                    
                    if(is_array($value['lth_solr_intro'])) {
                        foreach($value['lth_solr_intro'] as $key => $value) {
                            $data[$key] = $value;
                        }
                    }
                    
                    if(is_array($value['lth_solr_sort'])) {
                        foreach($value['lth_solr_sort'] as $key => $value) {
                            $data[$key] = $value;
                        }
                    }

                    try {
                        $buffer->createDocument($data);                    

                    } catch(Exception $e) {
                        echo 'Message: ' .$e->getMessage();
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
    
    
    private function getUids($inputString, $feGroupsArray)
    {
        //print_r($feGroupsArray);
        if($inputString) {
            $loopArray = explode(',', $inputString);
            $resArray = array();
            foreach($loopArray as $key => $value) {
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
    
    
    private function toUC($in)
    {
        if($in) {
            $in = str_replace('-', ' - ', $in);
            $in = ucwords($in);
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