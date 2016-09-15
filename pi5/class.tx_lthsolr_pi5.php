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
class tx_lthsolr_pi5 extends tslib_pibase {
	public $prefixId      = 'tx_lthsolr_pi5';		// Same as class name
	public $scriptRelPath = 'pi5/class.tx_lthsolr_pi5.php';	// Path to this script relative to the extension dir.
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
            
            $uuid = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('uuid');
            echo $uuid;
            $syslang = $GLOBALS['TSFE']->config['config']['language'];
            if(!$syslang) {
                $syslang = 'en';
            }
            if($syslang=='se') {
                $syslang='sv';
            }
            
            //load files needed for datatables
            $GLOBALS["TSFE"]->additionalHeaderData["jquery.dataTables.min.css"] = "<link rel=\"stylesheet\" type=\"text/css\" href=\"/typo3conf/ext/lth_solr/vendor/datatables/css/jquery.dataTables.min.css\" />";
            $GLOBALS["TSFE"]->additionalHeaderData["buttons.dataTables.min.css"] = "<link rel=\"stylesheet\" type=\"text/css\" href=\"/typo3conf/ext/lth_solr/vendor/datatables/css/buttons.dataTables.min.css\" />";
            $GLOBALS["TSFE"]->additionalFooterData["jquery.dataTables.min.js"] = "<script language=\"JavaScript\" type=\"text/javascript\" src=\"/typo3conf/ext/lth_solr/vendor/datatables/js/jquery.dataTables.js\"></script>";
            $GLOBALS["TSFE"]->additionalFooterData["dataTables.buttons.js"] = "<script language=\"JavaScript\" type=\"text/javascript\" src=\"/typo3conf/ext/lth_solr/vendor/datatables/js/dataTables.buttons.min.js\"></script>";
            $GLOBALS["TSFE"]->additionalFooterData["jszip.min.js"] = "<script language=\"JavaScript\" type=\"text/javascript\" src=\"//cdnjs.cloudflare.com/ajax/libs/jszip/2.5.0/jszip.min.js\"></script>";
            $GLOBALS["TSFE"]->additionalFooterData["pdfmake.min.js"] = "<script language=\"JavaScript\" type=\"text/javascript\" src=\"//cdn.rawgit.com/bpampuch/pdfmake/0.1.18/build/pdfmake.min.js\"></script>";
            $GLOBALS["TSFE"]->additionalFooterData["vfs_fonts.js"] = "<script language=\"JavaScript\" type=\"text/javascript\" src=\"//cdn.rawgit.com/bpampuch/pdfmake/0.1.18/build/vfs_fonts.js\"></script>";
            $GLOBALS["TSFE"]->additionalFooterData["buttons.html5.js"] = "<script language=\"JavaScript\" type=\"text/javascript\" src=\"/typo3conf/ext/lth_solr/vendor/datatables/js/buttons.html5.min.js\"></script>";

            
            //Load main js- and css-files
            $GLOBALS["TSFE"]->additionalFooterData["tx_lthsolr_lang"] = "<script language=\"JavaScript\" type=\"text/javascript\" src=\"/typo3conf/ext/lth_solr/res/lth_solr_lang_$syslang.js\"></script>"; 
            $GLOBALS["TSFE"]->additionalHeaderData["tx_lthsolr_js"] = "<script language=\"JavaScript\" type=\"text/javascript\" src=\"/typo3conf/ext/lth_solr/res/lth_solr.js?" . rand(1,100000000) . "\"></script>"; 
            $GLOBALS["TSFE"]->additionalHeaderData["tx_lthsolr_css"] = "<link rel=\"stylesheet\" type=\"text/css\" href=\"/typo3conf/ext/lth_solr/res/lth_solr.css?" . rand(1,100000000) . "\" />";
             
            $content = '';
                                
            $content .= $this->showStaff($uuid);
        
            //$this->debug($content);
	
            return $content;
	}
        
        
        private function showStaff($uuid)
        {
            $content = '<table id="lthsolr_person_table" border="1"  class="display" cellspacing="0" cellpadding="0" width="100%">
                <tbody id="">
                    <tr><th style="width:100px;">Name</th><td style="width:400px;">###display_name###</td><td rowspan="6" style="width:200px;">###image###</td></tr>
                    <tr><th>Title</th><td>###title###</td></tr>
                    <tr><th>Email</th><td>###email###</td></tr>
                    <tr><th>Phone</th><td>###phone###</td></tr>
                    <tr><th>oname</th><td>###oname###</td></tr>
                    <tr><th>Homepage</th><td>###homepage###</td></tr>
                </tbody>
                </table>';
            
            $content .= '<h2>Publications</h2><table id="lthsolr_publication_table" class="display" cellspacing="0" cellpadding="0" width="100%">
                <thead><tr><th>Title</th><th>Author</th><th>Type</th><th>Year</th></tr></thead>
                <tbody id="">
                </tbody></table>';
            
            $content .= '<h2>Projects</h2><table id="lthsolr_project_table" class="display" cellspacing="0" cellpadding="0" width="100%">
                <thead><tr><th>Title</th><th>Participants</th></tr></thead>
                <tbody id="">
                </tbody>
            </table>';

            $content .= '<div id="lth_solr_container" ></div>
                    <input type="hidden" id="lth_solr_uuid" value="' . $uuid . '" />
                    <input type="hidden" id="lth_solr_action" value="showStaff" />';
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


if (defined('TYPO3_MODE') && isset($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/lth_solr/pi5/class.tx_lthsolr_pi5.php'])) {
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/lth_solr/pi5/class.tx_lthsolr_pi5.php']);
}