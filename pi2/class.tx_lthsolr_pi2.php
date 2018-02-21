<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2015 Tomas Havner <tomas.havner@kansli.lth.se>
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

include __DIR__ . "/../Classes/FrontEnd/FrontEndClass.php";

/**
 * Plugin 'LTH Solr' for the 'lth_solr' extension.
 *
 * @author	Tomas Havner <tomas.havner@kansli.lth.se>
 * @package	TYPO3
 * @subpackage	tx_lthsolr
 */
class tx_lthsolr_pi2 extends \TYPO3\CMS\Frontend\Plugin\AbstractPlugin {
	public $prefixId      = 'tx_lthsolr_pi2';		// Same as class name
	public $scriptRelPath = 'pi2/class.tx_lthsolr_pi2.php';	// Path to this script relative to the extension dir.
	public $extKey        = 'lth_solr';	// The extension key.
	public $pi_checkCHash = TRUE;
        
	
	/**
	 * The main method of the Plugin.
	 *
	 * @param string $content The Plugin content
	 * @param array $conf The Plugin configuration
	 * @return string The content that is displayed on the website
	 */
	public function main($content, array $conf) {
            $this->conf = $conf;
            $this->pi_setPiVarDefaults();
            $this->pi_loadLL();
            
            $this->pi_initPIflexForm();
            $piFlexForm = $this->cObj->data["pi_flexform"];
            $index = $GLOBALS["TSFE"]->sys_language_uid;
            $sDef = current($piFlexForm["data"]);       
            $lDef = array_keys($sDef);
            $html_template = $this->pi_getFFvalue($piFlexForm, "html_template", "sDEF", $lDef[$index]);
            $fe_groups = $this->pi_getFFvalue($piFlexForm, "fe_groups", "sDEF", $lDef[$index]);
            $fe_users = $this->pi_getFFvalue($piFlexForm, "fe_users", "sDEF", $lDef[$index]);
            $limitToStandardCategories = $this->pi_getFFvalue($piFlexForm, "limitToStandardCategories", "sDEF", $lDef[$index]);
            $showPictures = $this->pi_getFFvalue($piFlexForm, "showPictures", "sDEF", $lDef[$index]);

            $scope = array();
            $heritage = array();
            if($fe_groups) {
                /*
                 * SELECT f1.title, f2.title, f3.title, f4.title, f5.title, f6.title
FROM fe_groups f1 
LEFT JOIN fe_groups f2 ON f2.subgroup = f1.uid 
LEFT JOIN fe_groups f3 ON f3.subgroup = f2.uid 
LEFT JOIN fe_groups f4 ON f4.subgroup = f3.uid 
LEFT JOIN fe_groups f5 ON f5.subgroup = f4.uid 
LEFT JOIN fe_groups f6 ON f6.subgroup = f5.uid 
WHERE f1.uid IN(232,3);
                 */
                $oldTitle1 = '';
                $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('f1.title AS title1, f2.title AS title2, f3.title AS title3, f4.title AS title4, f5.title AS title5, f6.title AS title6',
                        'fe_groups f1 LEFT JOIN fe_groups f2 ON f2.subgroup = f1.uid LEFT JOIN fe_groups f3 ON f3.subgroup = f2.uid 
LEFT JOIN fe_groups f4 ON f4.subgroup = f3.uid LEFT JOIN fe_groups f5 ON f5.subgroup = f4.uid LEFT JOIN fe_groups f6 ON f6.subgroup = f5.uid ',
                        'f1.uid in(' . explode('|',$fe_groups)[0] . ')');
                while ($row = $GLOBALS["TYPO3_DB"]->sql_fetch_assoc($res)) {
                    $title1 = explode('__', $row['title1'])[0];
                    if($oldTitle1 !== $title1) $scope['fe_groups'][] = $title1;
                    $title2 = explode('__', $row['title2'])[0];
                    $title3 = explode('__', $row['title3'])[0];
                    $title4 = explode('__', $row['title4'])[0];
                    $title5 = explode('__', $row['title5'])[0];
                    $title6 = explode('__', $row['title6'])[0];
                    //if($title1) $scope['fe_groups'][$title1][] = $title1;
                    $heritage[] = $title1;
                    $heritage[] = $title2;
                    $heritage[] = $title3;
                    $heritage[] = $title4;
                    $heritage[] = $title5;
                    $heritage[] = $title6;
                    $oldTitle1 = $title1;
                }
                $GLOBALS['TYPO3_DB']->sql_free_result($res);
            } 
            if($fe_users) {
                $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('username','fe_users',"uid in(" . explode('|',$fe_users)[0].")");
                while ($row = $GLOBALS["TYPO3_DB"]->sql_fetch_assoc($res)) {
                    $scope['fe_users'][] = $row['username'];
                }
                $GLOBALS['TYPO3_DB']->sql_free_result($res);
            }
            
            $heritage = array_filter($heritage);
            $heritage = array_unique($heritage);
            
            if(count($scope > 0)) {
                $scope = urlencode(json_encode($scope));
            }
            
            if(count($heritage > 0)) {
                $heritage = urlencode(json_encode($heritage));
            }
            
            $clientIp = $_SERVER['REMOTE_ADDR'];
            
            $categories = $this->pi_getFFvalue($piFlexForm, "categories", "sDEF", $lDef[$index]);
            $customCategories = $this->pi_getFFvalue($piFlexForm, "customcategories", "sDEF", $lDef[$index]);
            $noItemsToShow = $this->pi_getFFvalue($piFlexForm, "noItemsToShow", "sDEF", $lDef[$index]);
            
            $uuid = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('uuid');
            
            
            /*if(strstr($uuid,")")) {
                $uuid = rtrim(array_pop(explode('(',$uuid)),")");
            }*/
            //$pid = $GLOBALS['TSFE']->page['pid'];
            //$ip = $_SERVER['REMOTE_ADDR'];
            if(strstr($uuid,")")) {
                $showType = 'staff';
                if(strstr($uuid,"(publication)")) {
                    $showType = 'publication';
                    $uuid = str_replace('(publication)', '', $uuid);
                }
                if(strstr($uuid,"(department)")) {
                    $showType = 'department';
                    $uuid = str_replace('(department)', '', $uuid);
                }
                if(strstr($uuid,"(author)")) {
                    $showType = 'author';
                    $uuid = str_replace('(author)', '', $uuid);
                }
                $pageTitle = array_pop(explode('/',array_shift(explode('(',$uuid))));
                $uuid = rtrim(array_pop(explode('(',$uuid)),")");
            }
            
            $action = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('action');
            $query = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('query');
        
            $FrontEndClass = new FrontEndClass();
            
            $FrontEndClass->addJsCss('');
            
            if($uuid) {
                
                if($showType==='staff' || $showType==='author') {
                    $content = $FrontEndClass->showStaff($uuid, $html_template, $noItemsToShow, $selection);
                } else if($showType==='publication') {
                    $content = $FrontEndClass->showPublication($uuid);
                } else if($showType==='department') {
                    $lth_solr_uuid = array();
                    $lth_solr_uuid['fe_groups'][] = $uuid;
                    $scope = urlencode(json_encode($lth_solr_uuid));
                    $content = $FrontEndClass->listPublications($scope, 25, '', '', $pageTitle, '', '', 'list');
                }
            //   $content = $FrontEndClass->showStaff($uuid, $html_template, $noItemsToShow);
            } else {
                $content = $FrontEndClass->listStaff($scope, $html_template, $noItemsToShow, $categories, $limitToStandardCategories, $heritage, $showPictures);
            }
            return $content;
	}
        
}


if (defined('TYPO3_MODE') && isset($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/lth_solr/pi2/class.tx_lthsolr_pi2.php'])) {
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/lth_solr/pi2/class.tx_lthsolr_pi2.php']);
}