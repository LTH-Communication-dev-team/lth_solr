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

// require_once(PATH_tslib . 'class.tslib_pibase.php');
include __DIR__ . "/../Classes/FrontEnd/FrontEndClass.php";

/**
 * Plugin 'LTH Solr' for the 'lth_solr' extension.
 *
 * @author	Tomas Havner <tomas.havner@kansli.lth.se>
 * @package	TYPO3
 * @subpackage	tx_lthsolr
 */
class tx_lthsolr_pi11 extends \TYPO3\CMS\Frontend\Plugin\AbstractPlugin {
	public $prefixId      = 'tx_lthsolr_pi11';		// Same as class name
	public $scriptRelPath = 'pi11/class.tx_lthsolr_pi11.php';	// Path to this script relative to the extension dir.
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
            $index = 0;//$GLOBALS["TSFE"]->sys_language_uid;
            $sDef = current($piFlexForm["data"]);       
            $lDef = array_keys($sDef);
            $display = $this->pi_getFFvalue($piFlexForm, "display", "sDEF", $lDef[$index]);
            $organisation = $this->pi_getFFvalue($piFlexForm, "organisation", "sDEF", $lDef[$index]);
            $vroles = $this->pi_getFFvalue($piFlexForm, "vroles", "sDEF", $lDef[$index]);
            $facetChoice = $this->pi_getFFvalue($piFlexForm, "facetChoice", "sDEF", $lDef[$index]);
            $hideSearchField = $this->pi_getFFvalue($piFlexForm, "hideSearchField", "sDEF", $lDef[$index]);
            $publicationsLink = $this->pi_getFFvalue($piFlexForm, "publicationsLink", "sDEF", $lDef[$index]);
            $dissertationsLink = $this->pi_getFFvalue($piFlexForm, "dissertationsLink", "sDEF", $lDef[$index]);
            $projectDetailPage = $this->pi_getFFvalue($piFlexForm, "projectDetailPage", "sDEF", $lDef[$index]);
            $publicationDetailPage = $this->pi_getFFvalue($piFlexForm, "publicationDetailPage", "sDEF", $lDef[$index]);
            $staffDetailPage = $this->pi_getFFvalue($piFlexForm, "staffDetailPage", "sDEF", $lDef[$index]);
            
            if(is_array($piFlexForm['data']['sDEF']['lDEF']['extraPeople']['el'])) {
                $extraPeople = json_encode($piFlexForm['data']['sDEF']['lDEF']['extraPeople']['el']);
            }

            $syslang = $GLOBALS['TSFE']->config['config']['language'];
            if(!$syslang) {
                $syslang = 'en';
            }
            if($syslang=='se') {
                $syslang='sv';
            }

            $FrontEndClass = new FrontEndClass();
            
            $FrontEndClass->addJsCss('');
            //$scope = $_SERVER[REQUEST_URI];
            $uuid = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('uuid');
            $query = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('query');

            //$scope = trim($scope);
            //$scope = rtrim($scope, '/');
            //$scope = array_pop(explode('/',$scope));

            if($uuid) {
                $scope = $uuid;
            } else {
                $scope = $organisation;
            }
            
            $frontEndArray = array('syslang' => $syslang, 'scope' => $scope, 'vroles' => $vroles, 'facetChoice' => $facetChoice, 'query' => $query,
                'extraPeople' => $extraPeople, 'hideSearchField' => $hideSearchField, 'publicationsLink' => $publicationsLink, 
                'dissertationsLink' => $dissertationsLink, 'projectDetailPage' => $projectDetailPage, 
                'publicationDetailPage' => $publicationDetailPage, 'staffDetailPage' => $staffDetailPage);

            if($display === 'staff') {
                
                if($uuid) {
                    $uuidArray = explode('(', $uuid);
                    //array_pop($scopeArray);
                    $uuid = str_replace(')', '', array_pop($uuidArray));
                    $frontEndArray['scope'] = $uuid;
                    $content .= $FrontEndClass->showStaffNovo($frontEndArray);
                } else {
                    $frontEndArray['action'] = 'listOrganisationStaff';
                    $content .= $FrontEndClass->listOrganisation($frontEndArray);
                }
                
            } else if($display === 'organisation') {
                if($uuid) {
                    //$scope = $uuid;
                    $frontEndArray['action'] = 'listSingleOrganisationStaff';
                    $content .= $FrontEndClass->listOrganisation($frontEndArray);
                } else {
                    $frontEndArray['action'] = 'listOrganisation';
                    $content .= $FrontEndClass->listOrganisation($frontEndArray);
                }
            } else if($display === 'roles') {
                //$scope = $organisation;
                $frontEndArray['action'] = 'listOrganisationRoles';
                $content .= $FrontEndClass->listOrganisation($frontEndArray, 'listOrganisationRoles');
            } else if($display === 'publications') {
                if($uuid) {
                    $frontEndArray['scope'] = $uuid;
                    $content .= $FrontEndClass->showPublicationNovo($frontEndArray);
                } else {
                    //$scope = $organisation;
                    $frontEndArray['action'] = 'listOrganisationPublications';
                    $content .= $FrontEndClass->listOrganisation($frontEndArray);
                }
            } else if($display === 'studentpapers') {
                if($uuid) {
                    $content .= $FrontEndClass->showStudentPaperNovo($syslang, $uuid, $organisation);
                } else {
                    //$scope = $organisation;
                    $frontEndArray['action'] = 'listOrganisationStudentPapers';
                    $content .= $FrontEndClass->listOrganisation($frontEndArray);
                }
            } else if($display === 'latestdissertationsstudentpapers') {
                if($uuid) {
                    $content .= $FrontEndClass->showStudentPaperNovo($syslang, $uuid, $organisation);
                } else {
                    //$scope = $organisation;
                    $frontEndArray['action'] = 'latestDissertationsStudentPapers';
                    $content .= $FrontEndClass->listOrganisation($frontEndArray);
                }
            } else if($display === 'projects') {
                if($uuid) {
                    $content .= $FrontEndClass->showProjectNovo($syslang, $uuid, $organisation);
                } else {
                    //$scope = $organisation;
                    $frontEndArray['action'] = 'listOrganisationProjects';
                    $content .= $FrontEndClass->listOrganisation($frontEndArray);
                }
            }
            
            return $content;
	}

}


if (defined('TYPO3_MODE') && isset($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/lth_solr/pi10/class.tx_lthsolr_pi6.php'])) {
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/lth_solr/pi6/class.tx_lthsolr_pi10.php']);
}