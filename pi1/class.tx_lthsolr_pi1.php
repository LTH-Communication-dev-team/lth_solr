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
            $display = $this->pi_getFFvalue($piFlexForm, "display", "sDEF", $lDef[$index]);
            $noItemsToShow = $this->pi_getFFvalue($piFlexForm, "noItemsToShow", "sDEF", $lDef[$index]);
            
            $syslang = $GLOBALS['TSFE']->config['config']['language'];
            if(!$syslang) {
                $syslang = 'en';
            }
            if($syslang=='se') {
                $syslang='sv';
            }
            
                //Load main js- and css-files
            $GLOBALS["TSFE"]->additionalHeaderData["tx_lthsolr_js"] = "<script language=\"JavaScript\" type=\"text/javascript\" src=\"/typo3conf/ext/lth_solr/res/lth_solr.js?" . rand(1,100000000) . "\"></script>"; 
            $GLOBALS["TSFE"]->additionalHeaderData["tx_lthsolr_css"] = "<link rel=\"stylesheet\" type=\"text/css\" href=\"/typo3conf/ext/lth_solr/res/lth_solr.css?" . rand(1,100000000) . "\" />";
            $GLOBALS["TSFE"]->additionalFooterData["tx_lthsolr_lang"] = "<script language=\"JavaScript\" type=\"text/javascript\" src=\"/typo3conf/ext/lth_solr/res/lth_solr_lang_$syslang.js\"></script>"; 

            $query = htmlspecialchars(\TYPO3\CMS\Core\Utility\GeneralUtility::_GP('query'));
            $tab = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('tab');
            $actual_link = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

            $content = '';

            if(stristr($actual_link, "/demo/") || stristr($actual_link, "vkans-th0")) {
                $content .= $this->searchResult($query, $noItemsToShow);
            } else {
                $content .= $this->widget($query, $display);
            }
            //$this->debug($content);
	
            return $content;
	}
        
        
        private function searchResult($query, $noItemsToShow)
        {
            $content;
            //searchbox
            //                <input type="submit" id="edit-submit" name="op" value="' . $this->pi_getLL("search") . '" class="form-submit" />

            $content .= '<form id="lthsolr_form" action="" method="post" accept-charset="UTF-8">
            <div class="form-item form-type-textfield form-item-search" role="application">
                <input type="hidden" id="searchSiteMain" name="query" value="' . $query . '" />
                <input type="hidden" id="no_cache" name="no_cache" value="1" />
                <input type="hidden" id="lth_solr_no_items" value="' . $noItemsToShow . '" />
            </div>
            </form>';
            
            //people
            $content .= '<div id="lthsolr_people_header"></div>';
            $content .= '<table id="lthsolr_staff_container"></table>';
            $content .= file_get_contents("/var/www/html/typo3/typo3conf/ext/lth_solr/templates/contact_search.html");
            
            //pages
            $content .= '<div id="lthsolr_pages_header"></div>';
            $content .= '<div id="lthsolr_pages_container"></div>';
            $content .= file_get_contents("/var/www/html/typo3/typo3conf/ext/lth_solr/templates/pages_simple.html");
            
            //documents
            $content .= '<div id="lthsolr_documents_header"></div>';
            $content .= '<div id="lthsolr_documents_container"></div>';
            $content .= file_get_contents("/var/www/html/typo3/typo3conf/ext/lth_solr/templates/pages_simple.html");
            
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
       /* 
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
	}*/
        
        
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