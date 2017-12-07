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
class tx_lthsolr_pi6 extends \TYPO3\CMS\Frontend\Plugin\AbstractPlugin {
	public $prefixId      = 'tx_lthsolr_pi6';		// Same as class name
	public $scriptRelPath = 'pi5/class.tx_lthsolr_pi6.php';	// Path to this script relative to the extension dir.
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

            $papertype = $this->pi_getFFvalue($piFlexForm, "papertype", "sDEF", $lDef[$index]);
            $scope = $this->pi_getFFvalue($piFlexForm, "scope", "sDEF", $lDef[$index]);
            if($scope) {
                $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('title','fe_groups',"uid in($scope)");
                while ($row = $GLOBALS["TYPO3_DB"]->sql_fetch_assoc($res)) {
                    $title[] = explode('__', $row['title'])[0];
                }
                if($title) {
                    $scope = implode(',', $title);
                }
                $GLOBALS['TYPO3_DB']->sql_free_result($res);
            }
            $detailPage = $this->pi_getFFvalue($piFlexForm, "detailPage", "sDEF", $lDef[$index]);
            $detailUrl = $GLOBALS['TSFE']->cObj->typoLink_URL(
                array(
                    'parameter' => $detailPage,
                    'forceAbsoluteUrl' => true,
                )
            );
            $categories = $this->pi_getFFvalue($piFlexForm, "categories", "sDEF", $lDef[$index]);
            $noItemsToShow = $this->pi_getFFvalue($piFlexForm, "noItemsToShow", "sDEF", $lDef[$index]);
            
            $uuid = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('uuid');
            if(strstr($uuid,")")) {
                $uuid = rtrim(array_pop(explode('(',$uuid)),")");
                $scope = $uuid;
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
            
            if(!$scope && !$uuid) {
                return 'Please add organisation uuid';
            }
            $content = '';

            if($uuid && substr($uuid, 0, 1) !== "v") {
                $content .= $FrontEndClass->showStudentPaper($uuid, $staffDetailPage, $projectDetailPage);
            } else {
                $content .= $FrontEndClass->listStudentPapers($scope, $detailPage, $noItemsToShow, $categories, $papertype);
            }
            //$this->debug($content);
            return $content;
	}
        
        
        private function debug($input)
        {
            echo '<pre>';
            print_r($input);
            echo '</pre>';
        }
        
        private function searchId($id)
        {
            $content = '';
            
            require(__DIR__.'/init.php');

            // create a client instance
            $client = new Solarium\Client($config);

            // get a select query instance
            $query = $client->createSelect();
            
            // set a query (all prices starting from 12)
            $query->setQuery('email_t:"'.$id.'"');


            // this executes the query and returns the result
            $resultset = $client->execute($query);


            // show documents using the resultset iterator
            foreach ($resultset as $document) {

                $content .=  '<hr/><table>';

                // the documents are also iterable, to get all fields
                foreach ($document as $field => $value) {
                    // this converts multivalue fields to a comma-separated string
                    if (is_array($value)) {
                        $value = implode(', ', $value);
                    }

                    $content .=  '<tr><th>' . $field . '</th><td>' . $value . '</td></tr>';
                }

                $content .=  '</table>';
            }
            return $content;
        }
        
        
        private function searchForm()
        {
            $content = '';
            
            require(__DIR__.'/init.php');

            // create a client instance
            $client = new Solarium\Client($config);

            // get a select query instance
            $query = $client->createQuery($client::QUERY_SELECT);

            // this executes the query and returns the result
            $resultset = $client->execute($query);

            // display the total number of documents found by solr
            $content .=  'NumFound: '.$resultset->getNumFound();

            // show documents using the resultset iterator
            foreach ($resultset as $document) {

                $content .=  '<hr/><table>';

                // the documents are also iterable, to get all fields
                foreach ($document as $field => $value) {
                    // this converts multivalue fields to a comma-separated string
                    if (is_array($value)) {
                        $value = implode(', ', $value);
                    }

                    $content .=  '<tr><th>' . $field . '</th><td>' . $value . '</td></tr>';
                }

                $content .=  '</table>';
            }
            return $content;
        }
}


if (defined('TYPO3_MODE') && isset($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/lth_solr/pi6/class.tx_lthsolr_pi6.php'])) {
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/lth_solr/pi6/class.tx_lthsolr_pi6.php']);
}