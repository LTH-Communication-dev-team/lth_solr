<?php
class lth_solr_ajax {
    
    public function ajaxControl() {
        
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

        $action = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('action');
        $items = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('items');
        $value = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('value');
        $checked = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('checked');
        $pid = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('pid');
        $sys_language_uid = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('sys_language_uid');
        $categoriesThisPage = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('categoriesThisPage');
        $introThisPage = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('introThisPage');
        $sid = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('sid');

        switch($action) {
            case 'updateIntroAndImage':
		$content = $this->updateIntroAndImage($items, $pid, $value, $checked, $sys_language_uid, $introThisPage, $config);
		break;	    
            case 'resort':
		$content = $this->resort($items, $pid, $sys_language_uid, $config);
		break;
            case 'updateCategories':
		$content = $this->updateCategories($items, $pid, $value, $checked, $sys_language_uid, $categoriesThisPage, $config);
		break;
            case 'updateHideonpage':
		$content = $this->updateHideonpage($items, $pid, $value, $checked, $sys_language_uid, $config);
		break;            
	    case 'updateRedirect':
		$content = $this->updateRedirect($items, $pid, $value, $config);
		break;
            case 'hidePublication':
                $content = $this->hidePublication($items, $pid, $value, $checked, $sys_language_uid, $config);
                break;
            case 'resortPublications':
                $content = $this->resortPublications($items, $pid, $sys_language_uid, $config);
                break;
	}
        
        echo json_encode($content);
    }
    
    
    public function resort($items, $pid, $sys_language_uid, $config)
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
    
    
    public function updateCategories($items, $pid, $value, $checked, $sys_language_uid, $categoriesThisPage, $config)
    {
        // $GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_devlog', array('msg' => "$items, $pid, $value, $checked, $categoriesThisPage", 'crdate' => time()));
        
        $client = new Solarium\Client($config);
                
        $query = $client->createSelect();
        
        $update = $client->createUpdate();
        
        $query->setQuery('id:'.$items);
        
        if($categoriesThisPage) {
            $catVar = 'lth_solr_cat_' . $pid . '_ss';
        } else {
            $catVar = 'lth_solr_cat_ss';
        }

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
            } else {
                if(is_array($doc->$catVar)) {
                    $tmpCat = array_search($value, $doc->$catVar);
                    $tmpCatArray = $doc->$catVar;
                    unset($tmpCatArray[$tmpCat]);
                    $doc->$catVar = $tmpCatArray;
                } else {
                    unset($doc->$catVar);
                }
            }
        }
        
        $update->addDocument($doc);
        $update->addCommit();
        $result = $client->update($update);
        
        $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery("lth_solr_cat", "fe_users", "username='$items'  AND lth_solr_cat != ''");
        while ($row = $GLOBALS["TYPO3_DB"]->sql_fetch_assoc($res)) {
            $catArray = $row['lth_solr_cat'];
        }
        $GLOBALS['TYPO3_DB']->sql_free_result($res);

        if($catArray) {
            $catArray = json_decode($catArray, true);
        } 
        $catArray[$catVar] = $doc->$catVar;

        $updateArray = array('lth_solr_cat' => json_encode($catArray), 'tstamp' => time());

        $GLOBALS['TYPO3_DB']->exec_UPDATEquery("fe_users", "username='$items'", $updateArray);

        return $result;
    }
    
    
    public function hidePublication($items, $pid, $value, $checked, $sys_language_uid, $config)
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
    
    
    public function resortPublications($items, $pid, $sys_language_uid, $config)
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
            $data[$sortVar] = $sortVal;
            $buffer->createDocument($data);
        }
        
        $buffer->flush();
        $update = $client->createUpdate();
        $update->addCommit();
        $result = $client->update($update);
    }
    
    
    public function updateHideonpage($items, $pid, $value, $checked, $sys_language_uid, $config)
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
   
    
    public function updateIntroAndImage($username, $pid, $value, $checked, $sys_language_uid, $introThisPage, $config)
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
        
        if(intval($introThisPage)===1) {
            $introVar = 'staff_custom_text_' . $pid . '_s';
        } else {
            $introVar = 'staff_custom_text_s';
        }
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
        $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery("lth_solr_intro, image", "fe_users", "username='$username'");
        $row = $GLOBALS["TYPO3_DB"]->sql_fetch_assoc($res);
        $introArray = $row['lth_solr_intro'];
        $image = $row['image'];
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
        
        $updateImage = '';
        if($data['image_s'] == '' && $image) {
            $updateImage = $image;
        } else if($data['image_s'] != '') {
            $updateImage = $data['image_s'];
        }

        $updateArray = array('lth_solr_intro' => $introArray, 'image' => $updateImage, 'image_id' => $imageId, 'tstamp' => time());

        $GLOBALS['TYPO3_DB']->exec_UPDATEquery("fe_users", "username='$username'", $updateArray);
        /////////////////////////
        
        $returnArray = [];
        $returnArray['introText'] = $introText;
        $returnArray['identifier'] = $identifier;
        return $returnArray;
    }
    
    
    function updateRedirect($items, $pid, $value, $config)
    {
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
    }
}