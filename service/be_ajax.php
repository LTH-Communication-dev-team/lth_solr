<?php
class lth_solr_ajax {
    
    public function ajaxControl() {
        $action = t3lib_div::_GP('action');
        $items = t3lib_div::_GP('items');
        $value = t3lib_div::_GP('value');
        $checked = t3lib_div::_GP('checked');
        $pid = t3lib_div::_GP('pid');
        $sys_language_uid = t3lib_div::_GP('sys_language_uid');
        $categoriesThisPage = t3lib_div::_GP('categoriesThisPage');
        $introThisPage = t3lib_div::_GP('introThisPage');
        $sid = t3lib_div::_GP('sid');

        switch($action) {
            case 'updateIntroAndImage':
		$content = $this->updateIntroAndImage($items, $pid, $value, $checked, $sys_language_uid, $introThisPage);
		break;	    
            case 'resort':
		$content = $this->resort($items, $pid, $sys_language_uid);
		break;
            case 'updateCategories':
		$content = $this->updateCategories($items, $pid, $value, $checked, $sys_language_uid, $categoriesThisPage);
		break;
            case 'updateHideonpage':
		$content = $this->updateHideonpage($items, $pid, $value, $checked, $sys_language_uid);
		break;            
	    /*case 'updateImage':
		$content = $this->updateImage($catvalue, $username, $checked, $sys_language_uid, $pluginid, $i);
		break;
	    case 'updateText':
		$content = $this->updateText($catvalue, $username, $checked, $sys_language_uid, $pluginid, $i);
		break;
             */
	}
        
        echo json_encode($content);
    }
    
    public function resort($items, $pid, $sys_language_uid)
    {
        $sortVal = 10;
        
        $staffArray = array();
        $staffArray = json_decode($items, true);

        require(__DIR__.'/init.php');
        
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
            
            $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery("lth_solr_sort", "fe_users", "username='$value'  AND lth_solr_sort != ''");
            while ($row = $GLOBALS["TYPO3_DB"]->sql_fetch_assoc($res)) {
                $sortArray = $row['lth_solr_sort'];
            }
            $GLOBALS['TYPO3_DB']->sql_free_result($res);

            if($sortArray) {
                $sortArray = json_decode($sortArray, true);
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
    
    
    public function updateCategories($items, $pid, $value, $checked, $sys_language_uid, $categoriesThisPage)
    {
        // $GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_devlog', array('msg' => "$items, $pid, $value, $checked, $categoriesThisPage", 'crdate' => time()));
        require(__DIR__.'/init.php');
        
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
    
    
   public function updateHideonpage($items, $pid, $value, $checked, $sys_language_uid)
    {
        //$GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_devlog', array('msg' => "$items, $pid, $value, $checked", 'crdate' => time()));
        require(__DIR__.'/init.php');
        
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
   
    
    public function updateIntroAndImage($username, $pid, $value, $checked, $sys_language_uid, $introThisPage)
    {
        $valueArray = json_decode($value, true);
        $introText = $valueArray[0];
        $imageId = $valueArray[1];

        $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('identifier, mime_type', 'sys_file', 'uid='.intval($imageId), '', '', '') or die('285; '.mysql_error());
	$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
	$identifier = $row['identifier'];
	$mime_type = $row['mime_type'];
	$GLOBALS['TYPO3_DB']->sql_free_result($res);

        require(__DIR__.'/init.php');
        
        $client = new Solarium\Client($config);
                
        $query = $client->createSelect();
        
        $buffer = $client->getPlugin('bufferedadd');
        $buffer->setBufferSize(50);
        
        if($introThisPage) {
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
                $data['image_s'] = $identifier;
                $data['image_id_s'] = $imageId;
            } else {
                $data['image_s'] = '';
                $data['image_id_s'] = '';                
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

    
    /*function updateText($strSave, $username, $checked, $sys_language, $pluginId, $i)
    {
	$confArr = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['institutioner']);
    
	if (!$confArr['solrServer']) {
	    return 'Ange Solr-server';
	}

	if (!$confArr['solrPort']) {
	    return 'Ange Solr-port';
	}

	if (!$confArr['solrPath']) {
	    return 'Ange Solr-path';
	}

	$scheme = 'http';
	
	$solr = t3lib_div::makeInstance('tx_solr_ConnectionManager')->getConnection($confArr['solrServer'], $confArr['solrPort'], $confArr['solrPath'], $scheme);

        $query = "id:$username";
        $results = false;
        $limit = 1;
 
        if (get_magic_quotes_gpc() == 1) {
            $query = stripslashes($query);
        }
        
        try {
            $response = $solr->search($query, 0, $limit);
        }
        catch(Exception $e) {
            return '180:' . $e->getMessage();
            exit();
        }
        
        if(isset($response->response->docs[0])) {
 
            //$docs = array();
            foreach($response->response->docs as $document) {
                $doc = array();
                foreach($document as $field => $value) {
                    $doc[$field] = $value;
                }

                $doc['staff_custom_text_'.$pluginId . '_s'] = $strSave;
                
                unset($doc['_version_']);
                unset($doc['alphaNameSort']);
            }

	    $part = new Apache_Solr_Document();

	    foreach ( $doc as $key => $value ) {
		if ( is_array( $value ) ) {
		    foreach ( $value as $data ) {
			$part->setMultiValue( $key, $data );
		    }
		}
		else {
		    $part->$key = $value;
		}
	    }

            try {
                $solr->addDocument($part);
                $solr->commit();
                $solr->optimize();
                $response = 'updateText done!';
            }
            catch ( Exception $e ) {
                $response = $e->getMessage();
            }
        } else {
            $response = "Kein Eintrag gefunden";
        }
	return $response;
    }*/
}