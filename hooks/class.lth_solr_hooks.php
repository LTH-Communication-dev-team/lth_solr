<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2011 Federico Bernardin <federico@bernardin.it>
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 *
 *
 *   46: class tx_t3registration_hooks
 *   55:     public function addPasswordMarker(&$params, &$pObj)
 *   77:     public function fillPasswordFieldForProfile(&$params, &$pObj)
 *   89:     public function checkPasswordTwice($params, &$pObj)
 *  114:     public function addHiddenForParams(&$params, $pObj)
 *  147:     public function saveParams(&$params, $pObj)
 *  167:     public function redirectWithParams(&$params, $pObj)
 *
 * TOTAL FUNCTIONS: 6
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */

/**
 * This class manages hooks
 *
 */
class lth_solr_hooks {
    
    public function fetchLthsolrIntro(&$params, &$pObj)
    {
        $username = $GLOBALS['TSFE']->fe_user->user['username'];
        $pid = $GLOBALS['TSFE']->page['pid'];
        $sl = $GLOBALS['TSFE']->sys_language_uid;
        $existingArray = $this->getUserData('intro');
        $lth_solr_intro = $existingArray['lth_solr_intro_' . $pid . '_' . $sl];
        return $lth_solr_intro;
    }
    
    
    public function fetchLthsolrTxt(&$params, &$pObj)
    {
        $username = $GLOBALS['TSFE']->fe_user->user['username'];
        $pid = $GLOBALS['TSFE']->page['pid'];
        $sl = $GLOBALS['TSFE']->sys_language_uid;
        $existingArray = $this->getUserData('txt');
        $lth_solr_txt = $existingArray['lth_solr_txt_' . $pid . '_' . $sl];
        return $lth_solr_txt;
    }
    
    public function beforeUpdateUser($userArray)
    {
        $uid = $GLOBALS['TSFE']->fe_user->user['uid'];
        $pid = $GLOBALS['TSFE']->page['pid'];
        $sl = $GLOBALS['TSFE']->sys_language_uid;
        $tx_t3registration_pi1Array = t3lib_div::_GP('tx_t3registration_pi1');
        $lth_solr_intro = $tx_t3registration_pi1Array['lthsolrintro'];
        $lth_solr_txt = $tx_t3registration_pi1Array['lthsolrtxt'];
        //$GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_devlog', array('msg' => $lth_solr_post, 'crdate' => time()));
        //tx_t3registration_pi1[lthsolrtxt]
        $imageArray = $tx_t3registration_pi1Array['image'];
        $existingArray = $this->getUserData('all');
        if(is_array($existingArray)) {
            $lth_solr_introArray = $existingArray[0];
            $lth_solr_txtArray = $existingArray[1];
        }
        $lth_solr_introArray['lth_solr_intro_' . $pid . '_' . $sl] = $lth_solr_intro;
        $lth_solr_txtArray['lth_solr_txt_' . $pid . '_' . $sl] = $lth_solr_txt;
        $lth_solr_intro = json_encode($lth_solr_introArray);
        $lth_solr_txt = json_encode($lth_solr_txtArray);
        $GLOBALS['TYPO3_DB']->exec_UPDATEquery('fe_users', 'uid='.intval($uid), array('lth_solr_intro' => $lth_solr_intro, 'lth_solr_txt' => $lth_solr_txt, 'tstamp' => time()));

        $this->updateSolr($pid, $sl, $imageArray, $lth_solr_intro, $lth_solr_txt);
    }
    
    
    private function getUserData($type)
    {
        $uid = $GLOBALS['TSFE']->fe_user->user['uid'];
        $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery("lth_solr_intro, lth_solr_txt", "fe_users", "uid=" . intval($uid));
        $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
        $lth_solr_intro = $row['lth_solr_intro'];
        $lth_solr_txt = $row['lth_solr_txt'];
        $GLOBALS['TYPO3_DB']->sql_free_result($res);
        if($lth_solr_intro !== '' && $type=='intro') {
            $lth_solr_return = json_decode($lth_solr_intro, true);
        }
        if($lth_solr_txt !== '' && $type=='txt') {
            $lth_solr_return = json_decode($lth_solr_txt, true);
        }
        if($type=='all') {
            if($lth_solr_intro !== '') {
                $lth_solr_return[] = json_decode($lth_solr_intro, true);
            }
            if($lth_solr_txt !== '') {
                $lth_solr_return[] = json_decode($lth_solr_txt, true);
            }
        }
        return $lth_solr_return;
    }
    
    
    private function updateSolr($pid, $sl, $imageArray, $lth_solr_intro, $lth_solr_txt)
    {
        $username = $GLOBALS['TSFE']->fe_user->user['username'];
        
        require __DIR__.'/../vendor/solarium/vendor/autoload.php';
        
        if (file_exists(__DIR__.'/../tasks/config.php')) {
            require(__DIR__.'/../tasks/config.php');
        } else {
            die(__DIR__);
            require('config.dist.php');
        }
        
        $client = new Solarium\Client($config);
                
        $query = $client->createSelect();
        
        $update = $client->createUpdate();
        
        $query->setQuery('id:'.$username);
        
        $introVar = 'lth_solr_intro_t';
        $txtVar = 'lth_solr_txt_t';
        
        $fieldArray = array('id', 'doctype_s', 'display_name_t', 'first_name_t',  'last_name_t', 'email_t', 'ou_t', 'ou_sort', 'title_t', 'title_sort', 'orgid_t', 'iamge_t',
            'primary_affiliation_t', 'homepage_t', 'lang_t', 'degree_t', 'degree_en_t', 'phone_t', 'hide_on_web_i', 'usergroup_txt', 'lth_solr_intro_t', 'lth_solr_txt_t');

        $response = $client->select($query);
        
        $doc = $update->createDocument();
        
        foreach ($response as $document) {
            
            foreach ($document as $field => $fieldValue) {
                if(in_array($field, $fieldArray) || strstr($field, 'lth_solr')!==FALSE) {
                    /*if (is_array($fieldValue)) {
                        $fieldValue = implode(', ', $fieldValue);

                    }*/
                    $doc->$field = $fieldValue;
               }
            }
            $doc->$introVar = $lth_solr_intro;
            $doc->$txtVar = $lth_solr_txt;
        }
        
        if(is_array($imageArray)) {
            foreach($imageArray as $image) {
                $doc->image_t = $image;
            }
        }
        
        $update->addDocument($doc);
        $update->addCommit();
        $result = $client->update($update);
    }
    

    /**
     * This function manages marker for double password
     *
     * @param    $params        array parameters
     * @param    $pObj        object remote caller class
     * @return    [type]        ...
     */
    public function addPasswordMarker(&$params, &$pObj) {
        if ($pObj->conf['extra.']['passwordTwice']) {
            if (!$params['preview']) {
                $field= $this->getPasswordTwiceField($pObj);
                $params['contentArray']['###' . strtoupper($field['name']) . '_FIELD###'] = $pObj->getAndReplaceSubpart($field, $params['content']);
            } else {
                $field= $this->getPasswordTwiceField($pObj);
                $params['hiddenArray'][strtoupper($field['name'])] = sprintf('<input type="hidden" name="%s" value="%s" />', $pObj->prefixId . '[' . $field['name'] . ']', $pObj->piVars[$pObj->conf['extra.']['passwordTwiceField']]);
            }
        }
    }

    protected function getPasswordTwiceField($pObj){
        $field = $pObj->getField('password');
        $field['name'] = $pObj->conf['extra.']['passwordTwiceField'];
        $field['label'] = ($pObj->conf['extra.']['passwordTwiceFieldLabel']) ? $pObj->pi_getLL($pObj->conf['extra.']['passwordTwiceFieldLabel']) : $field['label'];
        if(is_array($pObj->conf['extra.']['passwordTwice.']['field.'])) {
            $field = t3lib_div::array_merge_recursive_overrule($field,$pObj->removeDotFromArray($pObj->conf['extra.']['passwordTwice.']['field.']));
        }
        return $field;
    }

    /**
     * This function manages marker for double password in update profile prefills field
     *
     * @param    $params        array parameters
     * @param    $pObj        object remote caller class
     * @return    [type]        ...
     */
    public function fillPasswordFieldForProfile(&$params, &$pObj) {
        $pObj->piVars[$pObj->conf['extra.']['passwordTwiceField']] = $params['user']['password'];
        return $pObj->piVars;
    }

    /**
     * This function evaluates double password
     *
     * @param    $params        array parameters
     * @param    $pObj        object remote caller class
     * @return    boolean        true if no errors found, otherwise return false
     */
    public function checkPasswordTwice($params, &$pObj) {
        if (!isset($pObj->conf['extra.']['passwordTwice']) || !$pObj->conf['extra.']['passwordTwice']) {
            $pObj->errorArray['error'][$pObj->conf['extra.']['passwordTwiceField']] = true;
            return true;
        } else {
            if ($pObj->piVars[$pObj->conf['extra.']['passwordTwiceField']] === $params['value']) {
                $pObj->errorArray['error'][$pObj->conf['extra.']['passwordTwiceField']] = true;
                return true;
            } else {
                $pObj->errorArray['error'][$pObj->conf['extra.']['passwordTwiceField']] = false;
                return false;
            }
        }
    }

    /**
     * This function saves password field as saltedPassword only if it is enabled
     * @param array  $params parameters to elaborate
     * @param object $pObj t3registration class
     */
    public function saltedPassword(&$params, $pObj) {

        if (!$pObj->conf['extra.']['disabledSaltedPassword'] && isset($params['user']['password']) && strlen($params['user']['password'])) {
            $password = $params['user']['password']; // plain-text password
            if (t3lib_extMgm::isLoaded('saltedpasswords')) {
                if (tx_saltedpasswords_div::isUsageEnabled('FE')) {
                    $objSalt = tx_saltedpasswords_salts_factory::getSaltingInstance(NULL);
                    if (is_object($objSalt)) {
                        $isMD5 = preg_match('/[0-9abcdef]{32,32}/', $password);
                        $isSaltedHash = t3lib_div::inList('$1$,$2$,$2a,$P$', substr($password, 0, 3));

                        if ($isMD5) {
                            $password = 'M' . $objSalt->getHashedPassword($password);
                        } elseif (!$isSaltedHash) {
                            $password = $objSalt->getHashedPassword($password);
                        }
                        $params['user']['password'] = $password;
                    }
                }
            }
        }
    }


    /**
     * This function manages the redirect parameters passed from url use extra.saveParamsFromUrl=1 to enabled features
     * use extra.saveParamsFromUrl.list to define the list of parameters to allowed to be saved (stdWrap)
     *
     * @param    $params        array parameters
     * @param    $pObj        object remote caller class
     * @return    [type]        ...
     */
    public function addHiddenForParams(&$params, $pObj) {
        //Enable function
		$found=false;
        if ($pObj->conf['extra.']['saveParamsFromUrl'] && $GLOBALS['TSFE']->loginUser == 0) {
            if (isset($pObj->piVars['paramsFromUrl'])) {
                $params['hiddenArray']['paramsFromUrl'] = '<input type="hidden" name="tx_t3registration_pi1[paramsFromUrl]" value="' . $pObj->piVars['paramsFromUrl'] . '" />';
            } else {
                $paramsWhitelist = (isset($pObj->conf['extra.']['saveParamsFromUrl.']['list']) || isset($pObj->conf['extra.']['saveParamsFromUrl.']['list.'])) ? $pObj->cObj->stdWrap($pObj->conf['extra.']['saveParamsFromUrl.']['list'], $pObj->conf['extra.']['saveParamsFromUrl.']['list.']) : '';
                $paramsList = explode('&', urldecode(t3lib_div::getIndpEnv('QUERY_STRING')));
                $paramToSave = array();
                if (is_array($paramsList) && count($paramsList)) {
                    foreach ($paramsList as $item) {
                        $tempSingleParam = explode('=', $item);
                        if (t3lib_div::inList($paramsWhitelist, $tempSingleParam[0])) {
							$found = true;
                            $paramToSave[] = htmlentities(strip_tags($item));
                        }
                    }
                }
                if (count($paramToSave) > 0 && $found) {
                    $params['hiddenArray']['paramsFromUrl'] = '<input type="hidden" name="tx_t3registration_pi1[paramsFromUrl]" value="' . implode(',', $paramToSave) . '" />';
                }
            }
        }
    }

    /**
     * This function manages the redirect parameters passed from url use extra.saveParamsFromUrl=1 to enabled features
     * this part of hook save params in cache_md5params before saving user
     *
     * @param    $params        array parameters
     * @param    $pObj        object remote caller class
     * @return    [type]        ...
     */
    public function saveParams(&$params, $pObj) {
        if ($pObj->conf['extra.']['saveParamsFromUrl'] && $GLOBALS['TSFE']->loginUser == 0 && (isset($params['piVars']['paramsFromUrl']) || $pObj->conf['extra.']['saveParamsFromUrlWithoutParams'])) {
            $values = array(
                'md5hash' => substr(md5($params['user']['uid'] . $GLOBALS['TYPO3_CONF_VARS']['SYS']['encryptionKey']), 0, 20),
                'tstamp'  => time(),
                'type'    => 'fe',
                'params'  => serialize($params['piVars']['paramsFromUrl'])
            );
            $GLOBALS['TYPO3_DB']->exec_INSERTquery('cache_md5params', $values);
        }
    }

    /**
     * This function manages the redirect parameters passed from url use extra.saveParamsFromUrl=1 to enabled features
     * this part of hook fetch data from cache to redirect user
     *
     * @param    $params        array parameters
     * @param    $pObj        object remote caller class
     * @return    [type]        ...
     */
    public function redirectWithParams(&$params, $pObj) {
        if ($pObj->conf['extra.']['saveParamsFromUrl'] && $GLOBALS['TSFE']->loginUser == 0) {
            if ($params['lastEvent'] == 'userAuth') {
                $resource = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'cache_md5params', 'md5hash=' . $GLOBALS['TYPO3_DB']->fullQuoteStr(substr(md5($params['user']['uid'] . $GLOBALS['TYPO3_CONF_VARS']['SYS']['encryptionKey']), 0, 20), 'cache_md5params'));
                if ($GLOBALS['TYPO3_DB']->sql_num_rows($resource) == 1) {
                    $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($resource);
                    $paramsWhitelist = (isset($pObj->conf['extra.']['saveParamsFromUrl.']['list']) || isset($pObj->conf['extra.']['saveParamsFromUrl.']['list.'])) ? $pObj->cObj->stdWrap($pObj->conf['extra.']['saveParamsFromUrl.']['list'], $pObj->conf['extra.']['saveParamsFromUrl.']['list.']) : '';
                    $paramsList = explode(',', unserialize($row['params']));
                    foreach ($paramsList as $item) {
                        $tempSingleParam = explode('=', $item);
                        if (t3lib_div::inList($paramsWhitelist, $tempSingleParam[0])) {
                            $urlParameters[$tempSingleParam[0]] = $tempSingleParam[1];
                        }
                    }
                    if (isset($pObj->conf['extra.']['saveParamsFromUrl.']['pageParameter'])) {
                        $redirectId = $urlParameters[$pObj->conf['extra.']['saveParamsFromUrl.']['pageParameter']];
                        unset($urlParameters[$pObj->conf['extra.']['saveParamsFromUrl.']['pageParameter']]);
                    } else {
                        $redirectId = (isset($pObj->conf['extra.']['saveParamsFromUrl.']['redirectPage'])) ? $pObj->conf['extra.']['saveParamsFromUrl.']['redirectPage'] : $GLOBALS['TSFE']->id;
                    }
                    $GLOBALS['TYPO3_DB']->exec_DELETEquery('cache_md5params', 'md5hash=' . $GLOBALS['TYPO3_DB']->fullQuoteStr(substr(md5($params['user']['uid'] . $GLOBALS['TYPO3_CONF_VARS']['SYS']['encryptionKey']), 0, 20), 'cache_md5params'));
                    header('Location: ' . t3lib_div::locationHeaderUrl($pObj->pi_getPageLink($redirectId, '', $urlParameters)));
                    exit();
                }
            }
        }
    }
}


?>