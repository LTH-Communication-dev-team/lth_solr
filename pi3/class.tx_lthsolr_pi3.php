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
class tx_lthsolr_pi3 extends \TYPO3\CMS\Frontend\Plugin\AbstractPlugin {
	public $prefixId      = 'tx_lthsolr_pi3';		// Same as class name
	public $scriptRelPath = 'pi3/class.tx_lthsolr_pi3.php';	// Path to this script relative to the extension dir.
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
            $display = $this->pi_getFFvalue($piFlexForm, "display", "sDEF", $lDef[$index]);
            $fe_groups = $this->pi_getFFvalue($piFlexForm, "fe_groups", "sDEF", $lDef[$index]);
            $fe_users = $this->pi_getFFvalue($piFlexForm, "fe_users", "sDEF", $lDef[$index]);
            $categories = $this->pi_getFFvalue($piFlexForm, "categories", "sDEF", $lDef[$index]);
            $staffDetailPage = $this->pi_getFFvalue($piFlexForm, "staffDetailPage", "sDEF", $lDef[$index]);
            $projectDetailPage = $this->pi_getFFvalue($piFlexForm, "projectDetailPage", "sDEF", $lDef[$index]);
            $noItemsToShow = $this->pi_getFFvalue($piFlexForm, "noItemsToShow", "sDEF", $lDef[$index]);
            
            $keyword;
            $uuid;
            
            /*if($detailPage) {
                $detailPage = $this->detailUrl($detailPage);
            }*/
            
            if($staffDetailPage) {
                $staffDetailPage = $this->detailUrl($staffDetailPage);
            }
            
            if($projectDetailPage) {
                $projectDetailPage = $this->detailUrl($projectDetailPage);
            }

            $uuid = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('uuid');
            $keyword = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('keyword');

            if(strstr($uuid,")")) {
                $uuid = rtrim(array_pop(explode('(',$uuid)),")");
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
            $GLOBALS["TSFE"]->additionalFooterData["tx_lthsolr_js"] = "<script language=\"JavaScript\" type=\"text/javascript\" src=\"/typo3conf/ext/lth_solr/res/lth_solr.js?" . rand(1,100000000) . "\"></script>"; 
            $GLOBALS["TSFE"]->additionalHeaderData["tx_lthsolr_css"] = "<link rel=\"stylesheet\" type=\"text/css\" href=\"/typo3conf/ext/lth_solr/res/lth_solr.css?" . rand(1,100000000) . "\" />";
            //$GLOBALS["TSFE"]->additionalFooterData["tx_lthsolr_download"] = "<script language=\"JavaScript\" type=\"text/javascript\" src=\"/typo3conf/ext/lth_solr/vendor/download/download.js\"></script>"; 
            if($display === "tagcloud") {
                $GLOBALS["TSFE"]->additionalFooterData["jqcloud.js"] = "<script language=\"JavaScript\" type=\"text/javascript\" src=\"/typo3conf/ext/lth_solr/vendor/jqcloud/jqcloud.js?" . rand(1,100000000) . "\"></script>"; 
                $GLOBALS["TSFE"]->additionalHeaderData["jqcloud.css"] = "<link rel=\"stylesheet\" type=\"text/css\" href=\"/typo3conf/ext/lth_solr/vendor/jqcloud/jqcloud.css?" . rand(1,100000000) . "\" />";              
            }

            $content = '';
            if($uuid && substr($uuid, 0, 1) !== "v" && substr($uuid, 0, 2) !== "--" && !$keyword) {
                $content .= $this->showPublication($uuid, $staffDetailPage, $projectDetailPage);
            } else if(substr($uuid, 0, 1) === "v") {
                $lth_solr_uuid = array();
                $lth_solr_uuid['fe_groups'][] = $uuid;
                $scope = urlencode(json_encode($lth_solr_uuid));
                $content .= $this->listPublications($scope, $detailPage, $noItemsToShow, $selection, $categories);
            } else if(substr($uuid, 0, 2) === "--") {
                $lth_solr_uuid = array();
                $lth_solr_uuid['fe_users'][] = str_replace('--','', $uuid);
                $scope = urlencode(json_encode($lth_solr_uuid));
                $content .= $this->listPublications($scope, $detailPage, $noItemsToShow, $selection, $categories);
            } else {
                $lth_solr_uuid = array();
                if($fe_groups) {
                    $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('title','fe_groups',"uid in(" . explode('|',$fe_groups)[0].")");
                    while ($row = $GLOBALS["TYPO3_DB"]->sql_fetch_assoc($res)) {
                        $lth_solr_uuid['fe_groups'][] = explode('__', $row['title'])[0];
                    }
                    $GLOBALS['TYPO3_DB']->sql_free_result($res);
                } 
                if($fe_users) {
                    $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('lth_solr_uuid','fe_users',"uid in(" . explode('|',$fe_users)[0].") AND lth_solr_uuid!=''");
                    while ($row = $GLOBALS["TYPO3_DB"]->sql_fetch_assoc($res)) {
                        $lth_solr_uuid['fe_users'][] = $row['lth_solr_uuid'];
                    }
                    $GLOBALS['TYPO3_DB']->sql_free_result($res);
                }
                if(count($lth_solr_uuid > 0)) {
                    $scope = urlencode(json_encode($lth_solr_uuid));
                }
                if($display === "tagcloud" && !$keyword) {
                    $content .= $this->listTagCloud($scope, $detailPage, $noItemsToShow, $selection, $categories);
                } else {
                    if($keyword) {
                        $keyword = urlencode($keyword);
                    }
                    $content .= $this->listPublications($scope, $detailPage, $noItemsToShow, $selection, $categories, $keyword);
                }
            }
        
            //$this->debug($content);
	
            return $content;
	}
        
        
        private function listTagCloud($scope, $detailPage, $noItemsToShow, $selection, $categories)
        {
            $content = '<div id="lthsolr_tagcloud_container"></div>';
                                    
            $content .= '
                <input type="hidden" id="lth_solr_scope" value="' . $scope . '" />
                <input type="hidden" id="lth_solr_action" value="listTagCloud" />';
            return $content;
        }
        
        
        private function showPublication($uuid, $staffDetailPage, $projectDetailPage)
        {
            $content = '<div id="lth_solr_container" ></div>';
            
            $content .= file_get_contents("/var/www/html/typo3/typo3conf/ext/lth_solr/templates/publication_presentation.html");
            
            $content = str_replace('###more###', $this->pi_getLL("more"), $content);
            
            $content .= '
                <input type="hidden" id="lth_solr_staffdetailpage" value="' . $staffDetailPage . '" />
                <input type="hidden" id="lth_solr_projectdetailpage" value="' . $projectDetailPage . '" />
                <input type="hidden" id="lth_solr_uuid" value="' . $uuid . '" />
                <input type="hidden" id="lth_solr_action" value="showPublication" />';
            
            return $content;
        }
        
        
        private function listPublications($scope, $detailPage, $noItemsToShow, $selection, $categories, $keyword)
        {   
            $content .= '<style>.glyphicon-search {font-size: 25px;}.glyphicon-filter, .glyphicon-export {font-size: 15px;}</style>';
            $content .= '<div class="lth_solr_filter_container">';
              
            $content .= '<div style="clear:both;height:50px;">';
            if($categories != "no_categories") {
                $content .= '<div id="refine" style="float:left;width:30%;background-color:#353838;color:#ffffff;height:50px;padding:17px;font-size:16px;"><span class="glyphicon glyphicon-filter"></span><span class="refine">Filter</span></div>';
            }
            $content .= '<div style="float:left;padding:15px 0px 0px 15px;width:10%"><span class="glyphicon glyphicon-search"></span></div>';
            $content .= '<div style="float:left;padding-top:10px;width:50%">';
            $content .= '<input style="border:0px;background-color:#fafafa;width:100%;box-shadow:none;" type="text" id="lthsolr_publications_filter" class="lthsolr_filter" placeholdera="' . $this->pi_getLL("freetext") . '" name="lthsolr_filter" value="" />';
            $content .= '</div>';

            $content .= '</div>';
                
            $content .= '</div>';    
            
            $content .= '<div style="clear:both;">';
            
            $content .= '<div id="lth_solr_facet_container"></div>';
            
            $content .= '<div id="lthsolr_publications_container"><div style="clear:both;height:20px;" id="lthsolr_publications_header"></div></div>';
            
            $content .= '</div>'; 
            
            $content .= file_get_contents("/var/www/html/typo3/typo3conf/ext/lth_solr/templates/publication_simple.html");
            
            $content .= '
                <input type="hidden" id="lth_solr_scope" value="' . $scope . '" />
                <input type="hidden" id="lth_solr_publicationdetailpage" value="' . $detailPage . '" />
                <input type="hidden" id="lth_solr_action" value="listPublications" />
                <input type="hidden" id="lth_solr_keyword" value="' . $keyword . '" />    
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
        
        
        private function debug($input)
        {
            echo '<pre>';
            print_r($input);
            echo '</pre>';
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


if (defined('TYPO3_MODE') && isset($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/lth_solr/pi3/class.tx_lthsolr_pi3.php'])) {
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/lth_solr/pi3/class.tx_lthsolr_pi3.php']);
}