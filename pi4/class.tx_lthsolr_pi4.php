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

/**
 * Plugin 'LTH Solr' for the 'lth_solr' extension.
 *
 * @author	Tomas Havner <tomas.havner@kansli.lth.se>
 * @package	TYPO3
 * @subpackage	tx_lthsolr
 */

include __DIR__ . "/../Classes/FrontEnd/FrontEndClass.php";

class tx_lthsolr_pi4 extends \TYPO3\CMS\Frontend\Plugin\AbstractPlugin {
	public $prefixId      = 'tx_lthsolr_pi4';		// Same as class name
	public $scriptRelPath = 'pi4/class.tx_lthsolr_pi4.php';	// Path to this script relative to the extension dir.
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
            $fe_groups = $this->pi_getFFvalue($piFlexForm, "fe_groups", "sDEF", $lDef[$index]);
            $noItemsToShow = $this->pi_getFFvalue($piFlexForm, "noItemsToShow", "sDEF", $lDef[$index]);
            
            if($fe_groups) {
                $tmpArray = explode(',',$fe_groups);
                foreach($tmpArray as $tmpValue) {
                    $lth_solr_uuid['fe_groups'][] = $tmpValue;
                }
                $scope = urlencode(json_encode($lth_solr_uuid));
            }
            
            $uuid = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('uuid');
            if(strstr($uuid,"(participant)")) {
                $showType = 'participant';
                $uuid = str_replace(')','',explode('(',$uuid)[1]);
            }
            //die($uuid);
            if(strstr($uuid,"(publication)")) {
                $showType = 'publication';
                $uuid = str_replace(')','',explode('(',$uuid)[1]);
            }
            
            if(strstr($uuid,")")) {
                $showType = 'project';
                $uuid = rtrim(array_pop(explode('(',$uuid)),")");
            }
            
            $syslang = $GLOBALS['TSFE']->config['config']['language'];
            if(!$syslang) {
                $syslang = 'en';
            }
            if($syslang=='se') {
                $syslang='sv';
            }
                       
            $FrontEndClass = new FrontEndClass();
            $FrontEndClass->addJsCss('projects');     
            //$query = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('query');
            
            if(!$scope && !$uuid) {
                return 'Please add organisation uuid';
            }
            $content = '';
                                
            if($showType === 'project') {
                $content = $FrontEndClass->showProject($uuid);
            } else if($showType === 'participant') {
                $lth_solr_uuid = array();
                $lth_solr_uuid['fe_users'][] = $uuid;
                $scope = urlencode(json_encode($lth_solr_uuid));
                $content = $FrontEndClass->showStaff($scope, $html_template, $noItemsToShow, $selection);
            } else if($showType === 'publication') {
                $lth_solr_uuid = array();
                $lth_solr_uuid['publication'][] = $uuid;
                $content = $FrontEndClass->showPublication($scope, $uuid);
            } else {
                $content = $this->listProjects($scope, $detailUrl, $syslang, $noItemsToShow);
            }
        
            //$this->debug($content);
	
            return $content;
	}
              
        
        private function listProjects($scope, $detailUrl, $syslang, $noItemsToShow)
        {
            
            $content .= '<div id="lthsolr_projects_container" ><div style="clear:both;height:20px;" id="lthsolr_projects_header"></div></div>';
            
            $content .= file_get_contents("/var/www/html/typo3/typo3conf/ext/lth_solr/templates/project_simple.html");
            
            $content .= '<input type="hidden" id="lth_solr_scope" value="' . $scope . '" />
                    <input type="hidden" id="lth_solr_action" value="listProjects" />
                    <input type="hidden" id="lth_solr_syslang" value="' . $syslang . '" />
                    <input type="hidden" id="lth_solr_no_items" value="' . $noItemsToShow . '" />';
            return $content;
        }
        
        
        private function detailUrl($detailPage)
        {
            $detailUrl = $GLOBALS['TSFE']->cObj->typoLink_URL(
                array(
                    'parameter' => $detailPage,
                    'forceAbsoluteUrl' => true,
                )
            );
            return $detailUrl;
        }
        
               
        /*private function searchBox($query)
        {
            $content = '<form action="" method="post" accept-charset="UTF-8">
            <div class="form-item form-type-textfield form-item-search" role="application">
                <input type="text" id="searchSiteMain" name="search" value="' . $query . '" />
                <input type="submit" id="edit-submit" name="op" value="Sök" class="form-submit" />
                <input type="hidden" id="query" name="query" value="' . $query . '" />
            </div>
            </form>';
            
            return $content;
        }
        
        private function getXMLDoc($uri)
	{
            $xmlDoc = new DOMDocument('1.0', 'UTF-8');
            $success = $xmlDoc->loadXML(\TYPO3\CMS\Core\Utility\GeneralUtility::getURL($uri));
		if(!$success)
		{
			\TYPO3\CMS\Core\Utility\GeneralUtility::devlog(microtime(). 'XML is not loaded. Is it valid xml? Check url.', 'pure', 2,  array('url' => $uri));
			return null;
		}

		return $xmlDoc;
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
        }*/
}


if (defined('TYPO3_MODE') && isset($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/lth_solr/pi4/class.tx_lthsolr_pi4.php'])) {
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/lth_solr/pi4/class.tx_lthsolr_pi4.php']);
}