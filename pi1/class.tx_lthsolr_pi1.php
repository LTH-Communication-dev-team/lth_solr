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
class tx_lthsolr_pi1 extends \TYPO3\CMS\Frontend\Plugin\AbstractPlugin {
	public $prefixId      = 'tx_lthsolr_pi1';		// Same as class name
	public $scriptRelPath = 'pi1/class.tx_lthsolr_pi1.php';	// Path to this script relative to the extension dir.
	public $extKey        = 'lth_solr';	// The extension key.
	//public $pi_checkCHash = TRUE;
        
	
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
            $this->pi_USER_INT_obj=1;

            $this->pi_initPIflexForm();
            $piFlexForm = $this->cObj->data["pi_flexform"];
            $index = $GLOBALS["TSFE"]->sys_language_uid;
            $sDef = current($piFlexForm["data"]);       
            $lDef = array_keys($sDef);
            $webSearchScope = $this->pi_getFFvalue($piFlexForm, "webSearchScope", "sDEF", $lDef[$index]);
            $linkStaffDetailPage = $this->pi_getFFvalue($piFlexForm, "linkStaffDetailPage", "sDEF", $lDef[$index]);
            $noItemsToShow = $this->pi_getFFvalue($piFlexForm, "noItemsToShow", "sDEF", $lDef[$index]);
            
            $FrontEndClass = new FrontEndClass();
            
            $FrontEndClass->addJsCss('');
            
            $query = htmlspecialchars(\TYPO3\CMS\Core\Utility\GeneralUtility::_GP('query')) . htmlspecialchars(\TYPO3\CMS\Core\Utility\GeneralUtility::_GP('term'));;
            $actual_link = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
            $pageTitle = '';
           
            $uuid = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('uuid');
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

            if($uuid) {
                if($showType==='staff' || $showType==='author') {
                    $content = $FrontEndClass->showStaff($uuid, $html_template, $noItemsToShow, $selection);
                } else if($showType==='publication') {
                    $content = $FrontEndClass->showPublication($uuid);
                } else if($showType==='department') {
                    $lth_solr_uuid = array();
                    $lth_solr_uuid['fe_groups'][] = $uuid;
                    $scope = urlencode(json_encode($lth_solr_uuid));
                    $content = $FrontEndClass->listPublications($scope, 25, '', '', $pageTitle);
                }
            } else if(stristr($actual_link, "/demo/") || stristr($actual_link, "vkans-th0")) {
                $content = $FrontEndClass->searchResult($query, $webSearchScope, $linkStaffDetailPage);
            } else {
                $content = $FrontEndClass->widget($query, $display);
            }
            //$this->debug($content);
	
            return $content;
	}
}


if (defined('TYPO3_MODE') && isset($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/lth_solr/pi1/class.tx_lthsolr_pi1.php'])) {
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/lth_solr/pi1/class.tx_lthsolr_pi1.php']);
}