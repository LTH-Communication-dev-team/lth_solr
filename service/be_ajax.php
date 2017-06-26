<?php
class lth_solr_ajax {
    
    public function ajaxControl() {
        
        require(__DIR__.'/init.php');

        $settings = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['lth_solr']);

        if (!$settings['solrHost'] || !$settings['solrPort'] || !$settings['solrPath'] || !$settings['solrTimeout']) {
            return 'Please make all settings in extension manager';
        }

        $action = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('action');
        $items = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('items');
        $value = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('value');
        $checked = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('checked');
        $pid = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('pid');
        $syslang = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('syslang');
        $sid = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('sid');
        
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

        switch($action) {
            case 'updateIntroAndImage':
		$content = $this->updateIntroAndImage($items, $pid, $value, $checked, $syslang, $config);
		break;	    
            case 'resort':
		$content = $this->resort($items, $pid, $syslang, $config);
		break;
            case 'updateCategories':
		$content = $this->updateCategories($items, $pid, $value, $checked, $syslang, $config);
		break;
            case 'updateHideonpage':
		$content = $this->updateHideonpage($items, $pid, $value, $checked, $syslang, $config);
		break;            
	    /*case 'updateRedirect':
		$content = $this->updateRedirect($items, $pid, $value, $config);
		break;*/
            case 'hidePublication':
                $content = $this->hidePublication($items, $pid, $value, $checked, $syslang, $config);
                break;
            case 'resortPublications':
                $content = $this->resortPublications($items, $pid, $syslang, $config);
                break;
            case 'addPageShow':
                $content = $this->addPageShow($items, $pid, $config, $checked);
                break;
            case 'updateAutopage':
                $content = $this->updateAutopage($items, $pid, $value, $checked, $syslang, $config);
                break;
	}
        
        echo json_encode($content);
    }
    
    
    public function resort($items, $pid, $syslang, $config)
    {
        $sortVal = 10;
        $lth_solr_sort = '';
        
        $staffArray = array();
        $staffArray = json_decode($items, true);
        
        $client = new Solarium\Client($config);
                
        $query = $client->createSelect();
        
        $buffer = $client->getPlugin('bufferedadd');
        $buffer->setBufferSize(50);
        
        $sortVar = 'lth_solr_sort_' . $pid . '_i';

        foreach($staffArray as $key => $value) {
            $data = array();
            
            $sortVal = $sortVal + 1;
            $query->setQuery('id:'.$value);

            //$fieldArray = array('id', 'display_name_t', 'first_name_t', 'last_name_t', 'email_t', 'ou_t', 'title_t', 'orgid_t',
            //    'primary_affiliation_t', 'homepage_t', 'lang_t', 'degree_t', 'degree_en_t', 'phone_t', 'hide_on_web_t', 
             //   'usergroup_txt', 'doctype_s');
            

            $response = $client->select($query);
            //$GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_devlog', array('msg' => print_r($result), 'crdate' => time()));
            foreach ($response as $document) {
                foreach ($document as $field => $fieldValue) {
                    if($field != 'score') {
                       /* if (is_array($fieldValue)) {
                            $fieldValue = implode(', ', $fieldValue);

                        }*/
                        //echo $fieldValue;
                        $data[$field] = $fieldValue;
                   }
                }
            }
            $data["appKey"] = "lthsolr";
            $data[$sortVar] = $sortVal;
            $buffer->createDocument($data);
            
            $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery("lth_solr_sort", "fe_users", "username='$value'");
            while ($row = $GLOBALS["TYPO3_DB"]->sql_fetch_assoc($res)) {
                $lth_solr_sort = $row['lth_solr_sort'];
            }
            $GLOBALS['TYPO3_DB']->sql_free_result($res);

            if($lth_solr_sort) {
                $sortArray = json_decode($lth_solr_sort, true);
            } 
            $sortArray[$sortVar] = $sortVal;

            $updateArray = array('lth_solr_sort' => json_encode($sortArray), 'tstamp' => time());

            $GLOBALS['TYPO3_DB']->exec_UPDATEquery("fe_users", "username='$value'", $updateArray);
            /*print '<pre>';
            print_r($data);
            print '</pre>';*/
        }
        
        $buffer->flush();
        $update = $client->createUpdate();
        $update->addCommit();
        $result = $client->update($update);
    }
    
    
    public function updateCategories($items, $pid, $value, $checked, $syslang, $config)
    {
        // $GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_devlog', array('msg' => "$items, $pid, $value, $checked, $categoriesThisPage", 'crdate' => time()));
        
        $client = new Solarium\Client($config);
                
        $query = $client->createSelect();
        
        $update = $client->createUpdate();
        
        $query->setQuery('id:'.$items);
        
        //if($categoriesThisPage) {
            $catVar = 'lth_solr_cat_' . $pid . '_ss';
        //} else {
        //    $catVar = 'lth_solr_cat_ss';
        //}

        $response = $client->select($query);
        
        $doc = $update->createDocument();
        $doc->appKey = 'lthsolr';
        
        foreach ($response as $document) {
            
            foreach ($document as $field => $fieldValue) {
                if($field != 'score') {
                    /*if (is_array($fieldValue)) {
                        $fieldValue = implode(', ', $fieldValue);

                    }*/
                    $doc->$field = $fieldValue;
                }
            }
            
            $primary_uid = $document->primary_uid;
            $catArray = array();
            $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery("lth_solr_cat", "fe_users", "username='$primary_uid'  AND lth_solr_cat != ''");
            while ($row = $GLOBALS["TYPO3_DB"]->sql_fetch_assoc($res)) {
                $catArray = $row['lth_solr_cat'];
            }
            $GLOBALS['TYPO3_DB']->sql_free_result($res);
            if($catArray) {
                $catArray = json_decode($catArray, true);
            } 
            
            if($checked === 'true') {
                if(is_array($doc->$catVar)) {
                    $tmpCatArray = array();
                    $tmpCatArray = $doc->$catVar;
                    $tmpCatArray[] = $value;
                    $doc->$catVar = $tmpCatArray;
                } else if(is_string($doc->$catVar)) {
                    $tmpCatArray = array($doc->$catVar);
                    $tmpCatArray[] = $value;
                    $doc->$catVar = $tmpCatArray;
                } else {
                    $doc->$catVar = $value;
                }
                if(is_array($catArray)) {
                    $catArray[$catVar][] = $value;
                }
            } else {
                if(is_array($doc->$catVar)) {
                    $tmpCat = array_search($value, $doc->$catVar);
                    $tmpCatArray = $doc->$catVar;
                    unset($tmpCatArray[$tmpCat]);
                    $doc->$catVar = $tmpCatArray;
                } else {
                    unset($doc->$catVar);
                }
                if(is_array($catArray[$catVar])) {
                    $tmpCat = array_search($value, $catArray[$catVar]);
                    unset($catArray[$catVar][$tmpCat]);
                } 
            }
        }
        
        $update->addDocument($doc);
        $update->addCommit();
        $result = $client->update($update);
        
        

        
       // $catArray[$catVar] = $doc->$catVar;
//$GLOBALS['TYPO3_DB']->store_lastBuiltQuery = 1;
        $updateArray = array('lth_solr_cat' => json_encode($catArray), 'tstamp' => time());

        $GLOBALS['TYPO3_DB']->exec_UPDATEquery("fe_users", "username='$primary_uid'", $updateArray);
//$GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_devlog', array('msg' => $GLOBALS['TYPO3_DB']->debug_lastBuiltQuery, 'crdate' => time()));
        return $result;
    }
    
    
    public function hidePublication($items, $pid, $value, $checked, $syslang, $config)
    {
        //$GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_devlog', array('msg' => $items. $pid. $value. $checked, 'crdate' => time()));
        $client = new Solarium\Client($config);
        
        $hideVar = 'lth_solr_hide_' . $pid . '_i';

        $update = $client->createUpdate();
        
        ${"doc"} = $update->createDocument();
                        
        ${"doc"}->setKey('id', $items);

        if($checked === 'true') {
            ${"doc"}->addField($hideVar, 1);
            ${"doc"}->setFieldModifier($hideVar, 'set');
        } else {
            ${"doc"}->addField($hideVar, 0);
            ${"doc"}->setFieldModifier($hideVar, 'set');
        }
        $docArray[] = ${"doc"};

        $update->addDocuments($docArray);
        $update->addCommit();
        $result = $client->update($update);
    }
    
    
    public function resortPublications($items, $pid, $syslang, $config)
    {
        //$GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_devlog', array('msg' => $items. $pid, 'crdate' => time()));
        $sortVal = 10;
        $lth_solr_sort = '';
        
        $publicationsArray = array();
        $publicationsArray = json_decode($items, true);
        
        $client = new Solarium\Client($config);
                
        $query = $client->createSelect();
        
        $buffer = $client->getPlugin('bufferedadd');
        $buffer->setBufferSize(50);
        
        $sortVar = 'lth_solr_sort_' . $pid . '_i';

        foreach($publicationsArray as $key => $value) {
            $data = array();
            
            $sortVal = $sortVal + 1;
            $query->setQuery('id:'.$value);

            $response = $client->select($query);
            foreach ($response as $document) {
                foreach ($document as $field => $fieldValue) {
                    if($field != 'score') {
                        $data[$field] = $fieldValue;
                   }
                }
            }
            $data['appKey'] = 'lthsolr';
            $data[$sortVar] = $sortVal;
            $buffer->createDocument($data);
        }
        
        $buffer->flush();
        $update = $client->createUpdate();
        $update->addCommit();
        $result = $client->update($update);
    }
    
    
    public function updateHideonpage($items, $pid, $value, $checked, $syslang, $config)
    {
        //$GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_devlog', array('msg' => "$items, $pid, $value, $checked", 'crdate' => time()));
        
        $client = new Solarium\Client($config);
                
        $query = $client->createSelect();
        
        $update = $client->createUpdate();
        
        $query->setQuery('id:'.$items);
        
        $hideVar = 'lth_solr_hide_' . $pid . '_i';

        $response = $client->select($query);
        
        $doc = $update->createDocument();
        
        foreach ($response as $document) {
            
            foreach ($document as $field => $fieldValue) {
                if($field != 'score') {
                    /*if (is_array($fieldValue)) {
                        $fieldValue = implode(', ', $fieldValue);

                    }*/
                    $doc->$field = $fieldValue;
               }
               
            }
            $doc->appKey = 'lthsolr';
            if($checked === 'true') {
                /*if(is_array($doc->$hideVar)) {
                    $tmpHideArray = array();
                    $tmpHideArray = $doc->$hideVar;
                    $tmpHideArray[] = $value;
                    $doc->$HideVar = $tmpHideArray;
                } else if(is_string($doc->$hideVar)) {
                    $tmpHideArray = array($doc->$hideVar);
                    $tmpHideArray[] = $value;
                    $doc->$hideVar = $tmpHideArray;
                } else {*/
                    $doc->$hideVar = 1;
                //}
            } else {
                unset($doc->$hideVar);
               /* if(is_array($doc->$hideVar)) {
                    $tmpHide = array_search($value, $doc->$hideVar);
                    $tmpHideArray = $doc->$hideVar;
                    unset($tmpHideArray[$tmpHide]);
                    $doc->$hideVar = $tmpHideArray;
                } else {
                    unset($doc->$hideVar);
                }*/
            }            
        }
        
        $update->addDocument($doc);
        $update->addCommit();
        $result = $client->update($update);
        
        $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery("lth_solr_hide", "fe_users", "username='$items'  AND lth_solr_hide != ''");
        while ($row = $GLOBALS["TYPO3_DB"]->sql_fetch_assoc($res)) {
            $hideArray = $row['lth_solr_hide'];
        }
        $GLOBALS['TYPO3_DB']->sql_free_result($res);

        if($hideArray) {
            $hideArray = json_decode($hideArray, true);
        } 
        $hideArray[$hideVar] = $doc->$hideVar;

        $updateArray = array('lth_solr_hide' => json_encode($hideArray), 'tstamp' => time());

        $GLOBALS['TYPO3_DB']->exec_UPDATEquery("fe_users", "username='$items'", $updateArray);
        
        return $result;
    }
   
    
    public function updateIntroAndImage($username, $pid, $value, $checked, $syslang, $config)
    {
        $valueArray = json_decode($value, true);
        $introText = $valueArray[0];
        $imageId = $valueArray[1];

        $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('identifier, mime_type', 'sys_file', 'uid='.intval($imageId), '', '', '') or die('285; '.mysql_error());
	$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
	$identifier = $row['identifier'];
	$mime_type = $row['mime_type'];
	$GLOBALS['TYPO3_DB']->sql_free_result($res);
        
        $client = new Solarium\Client($config);
                
        $query = $client->createSelect();
        
        $buffer = $client->getPlugin('bufferedadd');
        $buffer->setBufferSize(50);
        
        //if(intval($introThisPage)===1) {
            $introVar = 'staff_custom_text_' . $pid . '_s';
        //} else {
         //   $introVar = 'staff_custom_text_s';
        //}
        $imageVar = 'image_s';

        $data = array();

        $query->setQuery('id:'.$username);      

        $response = $client->select($query);
        //$GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_devlog', array('msg' => print_r($result), 'crdate' => time()));
        foreach ($response as $document) {
            foreach ($document as $field => $fieldValue) {
                if($field != 'score') {
                    $data[$field] = $fieldValue;
                }
                
            }
            if($identifier) {
                $data['image'] = $identifier;
                $data['image_id'] = $imageId;
            } else {
                $data['image'] = '';
                $data['image_id'] = '';                
            }
            if($introText) {
                $data[$introVar] = $introText;
            }
        }

        $buffer->createDocument($data);

        //$GLOBALS['TYPO3_DB']->exec_UPDATEquery("fe_users", "username='$value'", $updateArray);
        /*print '<pre>';
        print_r($data);
        print '</pre>';*/
        
        $buffer->flush();
        $update = $client->createUpdate();
        $update->addCommit();
        $client->update($update);
        
        /////////////////////////
        $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery("lth_solr_intro", "fe_users", "lucache_id='$username'");
        $row = $GLOBALS["TYPO3_DB"]->sql_fetch_assoc($res);
        $introArray = $row['lth_solr_intro'];
        //$image = $row['image'];
        $GLOBALS['TYPO3_DB']->sql_free_result($res);

        if($introArray) {
            $introArray = json_decode($introArray, true);
        }
        
        if($introText) {
            $introArray[$introVar] = $introText;
        }
        if($introArray) {
            $introArray = json_encode($introArray, true);
        } else {
            $introArray = '';
        }
        
       /* $updateImage = '';
        if($data['image_s'] == '' && $image) {
            $updateImage = $image;
        } else if($data['image_s'] != '') {
            $updateImage = $data['image_s'];
        }*/

        $updateArray = array('lth_solr_intro' => $introArray, 'image' => $identifier, 'image_id' => $imageId, 'tstamp' => time());

        $GLOBALS['TYPO3_DB']->exec_UPDATEquery("fe_users", "lucache_id='$username'", $updateArray);
        /////////////////////////
        
        $returnArray = [];
        $returnArray['introText'] = $introText;
        $returnArray['identifier'] = $identifier;
        return $returnArray;
    }
    
    
    function updateAutopage($items, $pid, $value, $checked, $syslang, $config)
    {
        $username = $items;
        $autoArray = array();
        $name = '';
        $autoVar = 'lth_solr_autohomepage_' . $pid . '_s';
        
        $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery("uid, lth_solr_autohomepage", "fe_users", "lucache_id='$username'");
        $row = $GLOBALS["TYPO3_DB"]->sql_fetch_assoc($res);
        $feUid = $row['uid'];
        $autoArray = $row['lth_solr_autohomepage'];
        $GLOBALS['TYPO3_DB']->sql_free_result($res);

        if($autoArray) {
            $autoArray = json_decode($autoArray, true);
        } 
        
        $name = $value;
        $rootLine = \TYPO3\CMS\Backend\Utility\BackendUtility::BEgetRootLine($pid);
        if(is_array($rootLine)) {
            foreach($rootLine as $key => $value) {
                $uidArray[] = $value['uid'];
            }
            if(is_array($uidArray)) {
                $uidString = implode(',', $uidArray);
                $GLOBALS['TYPO3_DB']->store_lastBuiltQuery = 1;
                $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery("p.uid AS pid","pages p JOIN sys_template s ON s.pid=p.uid AND s.root = 1 AND s.hidden=0 AND 
s.deleted=0","p.uid IN($uidString)","","p.uid DESC","0,1");
                $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
                $pid = $row['pid'];
                if($pid) {
                    $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery("uid","pages","title='staff_container' AND pid=$pid AND hidden=0 AND deleted=0");
                    $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
                    $scUid = $row['uid'];
                    if(!$scUid) {
                        $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery("MAX(sorting) AS sorting","pages","pid = $pid AND hidden=0 AND deleted=0");
                        $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
                        $sorting = $row['sorting'];
                        if($sorting) {
                            $sorting = intval($sorting) + 100;
                        }
                        $GLOBALS['TYPO3_DB']->exec_INSERTquery('pages', array('pid' => $pid, 'perms_userid' => 1, 'perms_groupid' => 1,
                            'perms_user' => 31, 'perms_group' => 0, 'perms_everybody' => 0, 'title' => "staff_container", 'doktype' => 254,
                            'sorting' => $sorting, 'tx_realurl_exclude' => 1));
                        $scUid = $GLOBALS['TYPO3_DB']->sql_insert_id();
                    }
                    $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery("p.uid AS pUid, t.uid AS tUid, p.deleted","pages p LEFT JOIN tt_content t ON p.uid=t.pid",
                            "p.pid=$scUid AND p.title='" . $this->fixAAO($name) . "'");
                    $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
                    $pUid = $row['pUid'];
                    $tUid = $row['tUid'];
                    $deleted = $row['deleted'];
                    $pi_flexform = '<?xml version="1.0" encoding="utf-8" standalone="yes" ?>
                    <T3FlexForms>
                        <data>
                            <sheet index="sDEF">
                                <language index="lDEF">
                                    <field index="noItemsToShow">
                                        <value index="vDEF">10</value>
                                    </field>
                                    <field index="publicationDetailPage">
                                        <value index="vDEF">41778</value>
                                    </field>
                                    <field index="projectDetailPage">
                                        <value index="vDEF">41780</value>
                                    </field>
                                    <field index="showStaff">
                                        <value index="vDEF">1</value>
                                    </field>
                                    <field index="showPublications">
                                        <value index="vDEF">1</value>
                                    </field>
                                    <field index="showProjects">
                                        <value index="vDEF">1</value>
                                    </field>
                                    <field index="fe_users">
                                        <value index="vDEF">'.$feUid.'</value>
                                    </field>
                                    <field index="showStaffPos">
                                        <value index="vDEF">right</value>
                                    </field>
                                </language>
                            </sheet>
                        </data>
                    </T3FlexForms>';
                    if(!$pUid) {
                        $GLOBALS['TYPO3_DB']->exec_INSERTquery('pages', array('pid' => $scUid, 'perms_userid' => 1, 'perms_groupid' => 1,
                            'perms_user' => 31, 'perms_group' => 0, 'perms_everybody' => 0, 'title' => $this->fixAAO($name), 'doktype' => 1,
                            'nav_hide' => 1));
                        $pUid = $GLOBALS['TYPO3_DB']->sql_insert_id();
                        //tt_content
                        $GLOBALS['TYPO3_DB']->exec_INSERTquery('tt_content', array('pid' => $pUid, 'CType' => 'list',
                            'list_type' => 'lth_solr_pi5', 'pi_flexform' => $pi_flexform));
                    } else if($pUid && $deleted && ($checked || $checked == 'true')) {
                        $GLOBALS['TYPO3_DB']->exec_UPDATEquery('pages', 'uid='.intval($pUid), array('deleted' => 0, 'title' => $this->fixAAO($name)));
                        $GLOBALS['TYPO3_DB']->exec_UPDATEquery('tt_content', 'uid='.intval($tUid), array('pid' => $pUid, 'CType' => 'list',
                            'list_type' => 'lth_solr_pi5', 'pi_flexform' => $pi_flexform, 'deleted' => 0));
                    } else if(!$checked || $checked == 'false') {
                        $GLOBALS['TYPO3_DB']->exec_UPDATEquery('pages', 'uid='.intval($pUid), array('deleted' => 1));
                    }
                }
            }
        }
        
        $rVal = $GLOBALS['TYPO3_DB']->debug_lastBuiltQuery;
            
        if(!$checked || $checked == 'false') {
            $name = '';
        }
        $autoArray[$autoVar] = $this->fixAAO($name);
        
        $updateArray = array('lth_solr_autohomepage' => json_encode($autoArray, true));

        $GLOBALS['TYPO3_DB']->exec_UPDATEquery("fe_users", "lucache_id='$username'", $updateArray);
        
        $client = new Solarium\Client($config);
        $update = $client->createUpdate();
        ${"doc"} = $update->createDocument();
        ${"doc"}->setKey('id', $items);
        ${"doc"}->addField($autoVar, $this->fixAAO($name));
        ${"doc"}->setFieldModifier($autoVar, 'set');
        $docArray[] = ${"doc"};
        $update->addDocuments($docArray);
        $update->addCommit();
        $result = $client->update($update);
        /*$client = new Solarium\Client($config);
        
        
        
        $value = json_decode($value);
        $url = $value[0];
        $destination = $value[1];
        
        if($url && $destination) {
            $url = rtrim(ltrim($url,'/'),'/') . '/';
            $updateInsertArray = array('url_hash' => hexdec(substr(md5($url), 0, 7)), 'url' => $url, 'destination' => $destination, 'last_referer' => '', 'has_moved' => 1, 'tstamp' => time());
            $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery("url", "tx_realurl_redirects", "url='" . addslashes($url) . "'");
            if($GLOBALS['TYPO3_DB']->sql_num_rows($res) > 0) {
                $GLOBALS['TYPO3_DB']->exec_UPDATEquery("tx_realurl_redirects", "url='" . addslashes($url) . "'", $updateInsertArray);
            } else {
                $GLOBALS['TYPO3_DB']->exec_INSERTquery("tx_realurl_redirects", $updateInsertArray); 
            }
            
            
        }
         */
       // return TRUE;
        return $rVal;
    }
    
    
    function fixAAO($name)
    {
        if($name) {
            $name = strtolower($name);
            $name = str_replace('å', 'a', $name);
            $name = str_replace('ä', 'a', $name);
            $name = str_replace('ö', 'a', $name);
            $name = str_replace(' ', '-', $name);
        }
        return $name;
    }
    
    
    /*function updateRedirect($items, $pid, $value, $config)
    {
        $client = new Solarium\Client($config);
        
        $update = $client->createUpdate();
        
        ${"doc"} = $update->createDocument();
                        
        ${"doc"}->setKey('id', $items);
        
        //////////////
        $value = json_decode($value);
        $url = $value[0];
        $destination = $value[1];
        
        if($url && $destination) {
            $url = rtrim(ltrim($url,'/'),'/') . '/';
            $updateInsertArray = array('url_hash' => hexdec(substr(md5($url), 0, 7)), 'url' => $url, 'destination' => $destination, 'last_referer' => '', 'has_moved' => 1, 'tstamp' => time());
            $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery("url", "tx_realurl_redirects", "url='" . addslashes($url) . "'");
            if($GLOBALS['TYPO3_DB']->sql_num_rows($res) > 0) {
                $GLOBALS['TYPO3_DB']->exec_UPDATEquery("tx_realurl_redirects", "url='" . addslashes($url) . "'", $updateInsertArray);
            } else {
                $GLOBALS['TYPO3_DB']->exec_INSERTquery("tx_realurl_redirects", $updateInsertArray); 
            }
            
            ${"doc"}->addField('redirect', json_encode(array($url,$destination)));
            ${"doc"}->setFieldModifier('redirect', 'set');
            $docArray[] = ${"doc"};
            $update->addDocuments($docArray);
            $update->addCommit();
            $result = $client->update($update);
        }
    }
    */
    
    function addPageShow($items, $pid, $config, $checked)
    {
        $client = new Solarium\Client($config);
        
        $showVar = 'lth_solr_show_' . $pid . '_i';

        $update = $client->createUpdate();
        
        ${"doc"} = $update->createDocument();
                        
        ${"doc"}->setKey('id', $items);

        if($checked === 'true') {
            ${"doc"}->addField($showVar, 1);
            ${"doc"}->setFieldModifier($showVar, 'set');
        } else {
            ${"doc"}->addField($showVar, 0);
            ${"doc"}->setFieldModifier($showVar, 'set');
        }
        $docArray[] = ${"doc"};

        $update->addDocuments($docArray);
        $update->addCommit();
        $result = $client->update($update);
        
        $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery("lth_solr_show","fe_users","lucache_id='$items'");
        $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
        $lth_solr_show = $row['lth_solr_show'];
        if($lth_solr_show) {
            $showArray = json_decode($lth_solr_show);
            if(($key = array_search($showVar, $showArray)) !== false) {
                unset($showArray[$key]);
            } else {
                $showArray[] = $showVar;
            }
        } else {
            $showArray = array();
            $showArray[] = $showVar;
        }
        
        $updateArray = array('lth_solr_show' => json_encode($showArray), 'tstamp' => time());

        $GLOBALS['TYPO3_DB']->exec_UPDATEquery("fe_users", "lucache_id='$items'", $updateArray);
    }
}