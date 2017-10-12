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
            $displayOrder = $this->pi_getFFvalue($piFlexForm, "displayOrder", "sDEF", $lDef[$index]);
            $noItemsToShow = $this->pi_getFFvalue($piFlexForm, "noItemsToShow", "sDEF", $lDef[$index]);
            
            $FrontEndClass = new FrontEndClass();
            
            $syslang = $GLOBALS['TSFE']->config['config']['language'];
            if(!$syslang) {
                $syslang = 'en';
                
            }
            if($syslang=='se') {
                $syslang='sv';
                
            }
            if($syslang==='sv') {
                $pageHeader = 'Sök';
            } else {
                $pageHeader = 'Search';
            }
            
                //Load main js- and css-files
            $GLOBALS["TSFE"]->additionalFooterData["tx_lthsolr_js"] = "<script language=\"JavaScript\" type=\"text/javascript\" src=\"/typo3conf/ext/lth_solr/res/lth_solr.js?" . rand(1,100000000) . "\"></script>"; 
            $GLOBALS["TSFE"]->additionalHeaderData["tx_lthsolr_css"] = "<link rel=\"stylesheet\" type=\"text/css\" href=\"/typo3conf/ext/lth_solr/res/lth_solr.css?" . rand(1,100000000) . "\" />";
            $GLOBALS["TSFE"]->additionalFooterData["tx_lthsolr_lang"] = "<script language=\"JavaScript\" type=\"text/javascript\" src=\"/typo3conf/ext/lth_solr/res/lth_solr_lang_$syslang.js?" . rand(1,100000000) . "\"></script>"; 

            $query = htmlspecialchars(\TYPO3\CMS\Core\Utility\GeneralUtility::_GP('query')) . htmlspecialchars(\TYPO3\CMS\Core\Utility\GeneralUtility::_GP('term'));;
            $tab = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('tab');
            $actual_link = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

            $content = '';
            
            $uuid = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('uuid');
            if(strstr($uuid,")")) {
                $uuid = rtrim(array_pop(explode('(',$uuid)),")");
            }

            if($uuid) {
                $content = $FrontEndClass->showStaff($uuid, $html_template, $noItemsToShow, $selection, $publicationDetailPage);
            } else if(stristr($actual_link, "/demo/") || stristr($actual_link, "vkans-th0")) {
                $content .= $this->searchResult($query, $displayOrder, $pageHeader);
            } else {
                $content .= $this->widget($query, $display);
            }
            //$this->debug($content);
	
            return '<div style="position:relative;">' . $content . '</div>';
	}
        
        
        private function searchResult($query, $displayOrder, $pageHeader)
        {
            $content;
            //$content .= "<header id=\"page_title\"><h1>$pageHeader</h1>";

            $content .= '<form id="lthsolr_form" action="" method="post" accept-charset="UTF-8">
                <div class="input-group" style="width:76%;">
                <input type="text" class="form-control" id="searchSiteMain" name="query" value="' . $query . '" />
                <div class="input-group-btn">  
                <button class="btn btn-lg btn-primary" style="height:38px;" type="submit"> Search </button>
                </div>
                </div>
                <input type="hidden" id="no_cache" name="no_cache" value="1" />
                <input type="hidden" id="lthsolr_display_order" value="' . $displayOrder . '" />            
            </form>';
            //$content .= "</header>";
            
            $content .= '<div id="lthsolr_search_container">';
            //people
            $content .= '<div style="clear:both;display:none;" class="table-responsive lthsolr_table_wrapper"><div id="lthsolr_people_header"></div>';
            $content .= '<table id="lthsolr_staff_container" class="table"><tbody></tbody></table></div>';
            $content .= file_get_contents("/var/www/html/typo3/typo3conf/ext/lth_solr/templates/contact_search.html");
            
            //pages
            $content .= '<div style="clear:both;display:none;" class="table-responsive lthsolr_table_wrapper"><div id="lthsolr_pages_header"></div>';
            $content .= '<ul id="lthsolr_pages_container" class="table"></ul></div>';
            $content .= file_get_contents("/var/www/html/typo3/typo3conf/ext/lth_solr/templates/pages_search.html");
            
            //documents
            /*$content .= '<div class="table-responsive lthsolr_table_wrapper"><div id="lthsolr_documents_header"></div>';
            $content .= '<table id="lthsolr_documents_container" class="table"></table></div>';
            $content .= file_get_contents("/var/www/html/typo3/typo3conf/ext/lth_solr/templates/documents_search.html");*/
            
            //courses
            $content .= '<div style="display:none;" class="table-responsive lthsolr_table_wrapper"><div id="lthsolr_courses_header"></div>';
            $content .= '<table id="lthsolr_courses_container" class="table"></table></div>';
            $content .= file_get_contents("/var/www/html/typo3/typo3conf/ext/lth_solr/templates/courses_search.html");
            $content .= '</div>';
            return $content;
        }
        
        
        private function widget($query, $display)
        {
            $content = '';
            if($display == 'lu') {
                $content = '<style>#solrtab-customsites { display:none !important;}</style>';
            } else if($display == 'lth') {
                $content = '<style>#solrtab-all { display:none !important;}</style>';           
            }
            
            $content .= 
                   // . '<input type="button" onclick="widget(\'tomas\');" name="send" value="Search" />'
                    '<div id="solrsearchresult"></div>'
                    . '';
            
            return $content;
        }
        
               
        private function searchBox($query)
        {
            $content = '<form action="" method="post" accept-charset="UTF-8">
            <div class="form-item form-type-textfield form-item-search" role="application">
                <input type="text" id="searchSiteMain" name="query" value="' . $query . '" />
                <input type="submit" id="edit-submit" name="op" value="Sök" class="form-submit" />
                <input type="hidden" id="no_cache" name="no_cache" value="1" />
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
        
        /*private function searchId($id)
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


if (defined('TYPO3_MODE') && isset($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/lth_solr/pi1/class.tx_lthsolr_pi1.php'])) {
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/lth_solr/pi1/class.tx_lthsolr_pi1.php']);
}