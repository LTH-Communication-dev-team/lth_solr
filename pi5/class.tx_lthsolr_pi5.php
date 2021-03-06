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
class tx_lthsolr_pi5 extends \TYPO3\CMS\Frontend\Plugin\AbstractPlugin {
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
            
            $this->pi_initPIflexForm();
            $piFlexForm = $this->cObj->data["pi_flexform"];
            $index = $GLOBALS["TSFE"]->sys_language_uid;
            $sDef = current($piFlexForm["data"]);       
            $lDef = array_keys($sDef);
            $showStaff = $this->pi_getFFvalue($piFlexForm, "showStaff", "sDEF", $lDef[$index]);
            $showStaffPos = $this->pi_getFFvalue($piFlexForm, "showStaffPos", "sDEF", $lDef[$index]);
            $showPublications = $this->pi_getFFvalue($piFlexForm, "showPublications", "sDEF", $lDef[$index]);
            $showProjects = $this->pi_getFFvalue($piFlexForm, "showProjects", "sDEF", $lDef[$index]);

            $fe_users = $this->pi_getFFvalue($piFlexForm, "fe_users", "sDEF", $lDef[$index]);
            if($fe_users) {
                $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('lth_solr_uuid','fe_users',"uid = " . intval($fe_users));
                $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
                $uuid = $row['lth_solr_uuid'];
                $GLOBALS['TYPO3_DB']->sql_free_result($res);
            } else {
                $uuid = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('uuid');
            }
            $publicationDetailPage = $this->pi_getFFvalue($piFlexForm, "publicationDetailPage", "sDEF", $lDef[$index]);
            $publicationDetailUrl = $GLOBALS['TSFE']->cObj->typoLink_URL(
                array(
                    'parameter' => $publicationDetailPage,
                    'forceAbsoluteUrl' => true,
                )
            );
            $projectDetailPage = $this->pi_getFFvalue($piFlexForm, "projectDetailPage", "sDEF", $lDef[$index]);
            $projectDetailUrl = $GLOBALS['TSFE']->cObj->typoLink_URL(
                array(
                    'parameter' => $projectDetailPage,
                    'forceAbsoluteUrl' => true,
                )
            );
            $noItemsToShow = $this->pi_getFFvalue($piFlexForm, "noItemsToShow", "sDEF", $lDef[$index]);
            
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
             
            $content = '';
                                
            $content .= $this->showStaff($uuid, $noItemsToShow, $publicationDetailUrl, $projectDetailUrl, $syslang, array($showStaff,$showPublications,$showProjects), $showStaffPos);
        
            //$this->debug($content);
	
            return $content;
	}
        
        
        private function showStaff($uuid, $noItemsToShow, $publicationDetailUrl, $projectDetailUrl, $syslang, $show, $showStaffPos)
        {
            //Staff center
            if($show[0]) {
                $content .= '<div id="lthsolr_staff_container"></div>';
                $content .= file_get_contents("/var/www/html/typo3/typo3conf/ext/lth_solr/templates/contact_with_image_and_ingress_and_adress.html");
            }
            
            //Publications
            if($show[1]) {
                $content .= '<div id="lthsolr_publications_container"><div id="lthsolr_publications_header"></div></div>';
                $content .= file_get_contents("/var/www/html/typo3/typo3conf/ext/lth_solr/templates/publication_simple.html");
            }
            
            //Projects
            if($show[2]) {
                $content .= '<div id="lthsolr_projects_container"><div id="lthsolr_projects_header"></div></div>';
                $content .= file_get_contents("/var/www/html/typo3/typo3conf/ext/lth_solr/templates/project_simple.html");
            }
            
            //hidden fields
            $content .= '
                <input type="hidden" id="lth_solr_publicationdetailpage" value="' . $publicationDetailUrl . '" />
                <input type="hidden" id="lth_solr_projectdetailpage" value="' . $projectDetailUrl . '" />
                <input type="hidden" id="lth_solr_scope" value="' . $uuid . '" />
                <input type="hidden" id="lth_solr_syslang" value="' . $syslang . '" />
                <input type="hidden" id="lth_solr_detail_action" value="showStaff" />
                <input type="hidden" id="lth_solr_staff_pos" value="' . $showStaffPos . '" />
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


if (defined('TYPO3_MODE') && isset($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/lth_solr/pi5/class.tx_lthsolr_pi5.php'])) {
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/lth_solr/pi5/class.tx_lthsolr_pi5.php']);
}