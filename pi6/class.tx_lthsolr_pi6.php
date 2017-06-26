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
            
            //Load main js- and css-files
            $GLOBALS["TSFE"]->additionalFooterData["tx_lthsolr_lang"] = "<script language=\"JavaScript\" type=\"text/javascript\" src=\"/typo3conf/ext/lth_solr/res/lth_solr_lang_$syslang.js\"></script>"; 
            $GLOBALS["TSFE"]->additionalHeaderData["tx_lthsolr_js"] = "<script language=\"JavaScript\" type=\"text/javascript\" src=\"/typo3conf/ext/lth_solr/res/lth_solr.js?" . rand(1,100000000) . "\"></script>"; 
            $GLOBALS["TSFE"]->additionalHeaderData["tx_lthsolr_css"] = "<link rel=\"stylesheet\" type=\"text/css\" href=\"/typo3conf/ext/lth_solr/res/lth_solr.css?" . rand(1,100000000) . "\" />";
             
            if(!$scope && !$uuid) {
                return 'Please add organisation uuid';
            }
            $content = '';

            if($uuid && substr($uuid, 0, 1) !== "v") {
                $content .= $this->showStudentPaper($uuid, $staffDetailPage, $projectDetailPage);
            } else {
                $content .= $this->listStudentPapers($scope, $detailPage, $noItemsToShow, $categories, $papertype);
            }
            //$this->debug($content);
            return $content;
	}
        
        
        private function showStudentPaper($uuid, $staffDetailPage, $projectDetailPage)
        {
            $content = '<div id="lth_solr_container" ></div>';
            
            $content .= file_get_contents("/var/www/html/typo3/typo3conf/ext/lth_solr/templates/studentpaper_presentation.html");
            
            $content = str_replace('###more###', 'Mer', $content);
            
            $content .= '
                <input type="hidden" id="lth_solr_uuid" value="' . $uuid . '" />
                <input type="hidden" id="lth_solr_action" value="showStudentPaper" />';
            
            return $content;
        }
        
        
        private function listStudentPapers($scope, $detailPage, $noItemsToShow, $categories, $papertype)
        {
            /*$content .= '<div style="clear:both;margin-top:20px;margin-bottom:20px;">Filter: <input type="text" id="lthsolr_publications_filter" class="lthsolr_filter" name="lthsolr_filter" value="" /></div>';
            
            $content .= '<div class="lth_solr_facet_container"></div>';
            
            $content .= '<div id="lthsolr_publications_header"></div>';
            
            $content .= '<div id="lthsolr_publications_container"></div>';*/
            
            $content .= '<style>.glyphicon-search {font-size: 25px;}.glyphicon-filter {font-size: 15px;}</style>';
            $content .= '<div class="lth_solr_filter_container">';
            
                //$content .= '<div style="font-weight:bold;">' . $this->pi_getLL("filter") . '</div>';
              
            $content .= '<div style="clear:both;height:50px;">';
            if($categories != "no_categories") {
                $content .= '<div id="refine" style="float:left;width:30%;background-color:#353838;color:#ffffff;height:50px;padding:17px;font-size:16px;"><span class="glyphicon glyphicon-filter"></span><span class="refine">Filter</span></div>';
            }    
            $content .= '<div style="float:left;padding:15px 0px 0px 15px;width:10%"><span class="glyphicon glyphicon-search"></span></div>';
            $content .= '<div style="float:left;padding-top:10px;width:60%">';
            $content .= '<input style="border:0px;background-color:#fafafa;width:100%;box-shadow:none;" type="text" id="lthsolr_studentpapers_filter" class="lthsolr_filter" placeholdera="' . $this->pi_getLL("freetext") . '" name="lthsolr_filter" value="" />';
            $content .= '</div>';
            
            $content .= '</div>';
                
            $content .= '</div>';    
            
            $content .= '<div style="clear:both;">';
            
            $content .= '<div id="lth_solr_facet_container"></div>';
            
            $content .= '<div id="lthsolr_publications_container"><div style="clear:both;height:20px;" id="lthsolr_publications_header"></div></div>';
            
            $content .= '</div>'; 
            
            $content .= file_get_contents("/var/www/html/typo3/typo3conf/ext/lth_solr/templates/studentpaper_simple.html");
            
            $content .= '
                    <input type="hidden" id="lth_solr_scope" value="' . $scope . '" />
                    <input type="hidden" id="lth_solr_detailpage" value="' . $detailPage . '" />
                    <input type="hidden" id="lth_solr_action" value="listStudentPapers" />
                    <input type="hidden" id="lth_solr_categories" value="' . $categories . '" />
                    <input type="hidden" id="lth_solr_papertype" value="' . $papertype . '" /> 
                    <input type="hidden" id="lth_solr_no_items" value="' . $noItemsToShow . '" />';
            return $content;
        }
        
               
        private function searchBox($query)
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