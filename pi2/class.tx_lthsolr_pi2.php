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
class tx_lthsolr_pi2 extends tslib_pibase {
	public $prefixId      = 'tx_lthsolr_pi2';		// Same as class name
	public $scriptRelPath = 'pi2/class.tx_lthsolr_pi2.php';	// Path to this script relative to the extension dir.
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
            $html_template = $this->pi_getFFvalue($piFlexForm, "html_template", "sDEF", $lDef[$index]);
            $scope = $this->pi_getFFvalue($piFlexForm, "scope", "sDEF", $lDef[$index]);
            $detailPage = $this->pi_getFFvalue($piFlexForm, "detailpage", "sDEF", $lDef[$index]);
            $categories = $this->pi_getFFvalue($piFlexForm, "categories", "sDEF", $lDef[$index]);
            $customCategories = $this->pi_getFFvalue($piFlexForm, "customcategories", "sDEF", $lDef[$index]);
            $categoriesThisPage = $this->pi_getFFvalue($piFlexForm, "categoriesthispage", "sDEF", $lDef[$index]);
            $introThisPage = $this->pi_getFFvalue($piFlexForm, "introthispage", "sDEF", $lDef[$index]);
            
            $pid = $GLOBALS['TSFE']->page['pid'];
            //$solrId = t3lib_div::_GP('solrid');
            $link = $_SERVER['PHP_SELF'];
            $link_array = explode('/',$link);
            $solrId = end($link_array);
            $ip = $_SERVER['REMOTE_ADDR'];

            $syslang = $GLOBALS['TSFE']->config['config']['language'];
            if(!$syslang) {
                $syslang = 'en';
            }
            if($syslang=='se') {
                $syslang='sv';
            }
            
            /*load files needed for datatables*/
            $GLOBALS["TSFE"]->additionalHeaderData["jquery.dataTables.min.css"] = "<link rel=\"stylesheet\" type=\"text/css\" href=\"/typo3conf/ext/lth_solr/vendor/datatables/css/jquery.dataTables.min.css\" />";
            //$GLOBALS["TSFE"]->additionalHeaderData["buttons.bootstrap.min.css"] = "<link rel=\"stylesheet\" type=\"text/css\" href=\"/typo3conf/ext/lth_solr/vendor/datatables/css/buttons.bootstrap.min.css\" />";
            $GLOBALS["TSFE"]->additionalHeaderData["buttons.dataTables.min.css"] = "<link rel=\"stylesheet\" type=\"text/css\" href=\"/typo3conf/ext/lth_solr/vendor/datatables/css/buttons.dataTables.min.css\" />";
            $GLOBALS["TSFE"]->additionalFooterData["jquery.dataTables.min.js"] = "<script language=\"JavaScript\" type=\"text/javascript\" src=\"/typo3conf/ext/lth_solr/vendor/datatables/js/jquery.dataTables.js\"></script>";
            $GLOBALS["TSFE"]->additionalFooterData["dataTables.buttons.js"] = "<script language=\"JavaScript\" type=\"text/javascript\" src=\"/typo3conf/ext/lth_solr/vendor/datatables/js/dataTables.buttons.min.js\"></script>";
            $GLOBALS["TSFE"]->additionalFooterData["jszip.min.js"] = "<script language=\"JavaScript\" type=\"text/javascript\" src=\"//cdnjs.cloudflare.com/ajax/libs/jszip/2.5.0/jszip.min.js\"></script>";
            $GLOBALS["TSFE"]->additionalFooterData["pdfmake.min.js"] = "<script language=\"JavaScript\" type=\"text/javascript\" src=\"//cdn.rawgit.com/bpampuch/pdfmake/0.1.18/build/pdfmake.min.js\"></script>";
            $GLOBALS["TSFE"]->additionalFooterData["vfs_fonts.js"] = "<script language=\"JavaScript\" type=\"text/javascript\" src=\"//cdn.rawgit.com/bpampuch/pdfmake/0.1.18/build/vfs_fonts.js\"></script>";
            $GLOBALS["TSFE"]->additionalFooterData["buttons.html5.js"] = "<script language=\"JavaScript\" type=\"text/javascript\" src=\"/typo3conf/ext/lth_solr/vendor/datatables/js/buttons.html5.min.js\"></script>";
            //$GLOBALS["TSFE"]->additionalHeaderData["handlebars-v4.0.5.js"] = "<script language=\"JavaScript\" type=\"text/javascript\" src=\"/typo3conf/ext/lth_solr/vendor/handlebars/handlebars-v4.0.5.js\"></script>";

            //Load main js- and css-files
            $GLOBALS["TSFE"]->additionalFooterData["tx_lthsolr_lang"] = "<script language=\"JavaScript\" type=\"text/javascript\" src=\"/typo3conf/ext/lth_solr/res/lth_solr_lang_$syslang.js\"></script>"; 
            $GLOBALS["TSFE"]->additionalFooterData["tx_lthsolr_js"] = "<script language=\"JavaScript\" type=\"text/javascript\" src=\"/typo3conf/ext/lth_solr/res/lth_solr.js\"></script>"; 
            $GLOBALS["TSFE"]->additionalHeaderData["tx_lthsolr_css"] = "<link rel=\"stylesheet\" type=\"text/css\" href=\"/typo3conf/ext/lth_solr/res/lth_solr.css\" />";
 
                    
            $content = '';
            $facetContent = '';
            $staffList = '';
            $facets = '';
            $lu_user = '';
            
            /*if($categories === 'custom_category' && $customCategories) {
                $customCategories = 'true';
            }*/
            
            if(substr($ip, 0, 7) === '130.235' || substr($ip, 0, 7) === '127.0.0') {
                $content .= "<style>.dt-buttons { display:block;}</style>";
                $lu_user = 'ja';
            } else {
                $content .= "<style>.dt-buttons { display:none;}</style>";
            }

            $content .= '<div class="grid-31 alpha omega"><div class="grid-22 alpha">';
            
            $content .= file_get_contents("/var/www/html/typo3/typo3conf/ext/lth_solr/templates/" . $html_template);
            
            if(!$scope && $solrId) {
                $content .= '</div><input type="hidden" id="lth_solr_type" value="detail" />' .
                        '<input type="hidden" id="lth_solr_scope" value="' . $solrId . '" />';
            } else {
                $content .= '</div><div id="lth_solr_facet_container" style="margin-left:20px;" class="grid-8 omega"></div></div>' .
                    '<input type="hidden" id="lth_solr_scope" value="' . $scope . '" />' .
                    '<input type="hidden" id="lth_solr_detailpage" value="' . $detailPage . '" />' .
                    '<input type="hidden" id="sys_language_uid" value="' . $index . '" />' .
                    '<input type="hidden" id="pid" value="' . $pid . '" />' .
                    '<input type="hidden" id="lth_solr_type" value="list" />' .
                    '<input type="hidden" id="lth_solr_categories" value="' . $categories . '" />' .
                    '<input type="hidden" id="lth_solr_custom_categories" value="' . $customCategories . '" />' .
                    '<input type="hidden" id="fe_user" value="' . $GLOBALS['TSFE']->fe_user->user . '" />' .
                    '<input type="hidden" id="lu_user" value="' . $lu_user . '" />' .
                    '<input type="hidden" id="categoriesThisPage" value="' . $categoriesThisPage . '" />' .
                    '<input type="hidden" id="introThisPage" value="' . $introThisPage . '" />' .
                    '<div class="csc-default">&nbsp;</div>';
            }
            
            return $content;
	}
        
        
        private function printStaffList($data, $html_template, $pageId, $sl)
        {
                // Get the template
            $cObj = t3lib_div::makeInstance('tslib_cObj');
            $templateHtml = $cObj->fileResource("typo3conf/ext/lth_solr/templates/$html_template");
            // Extract subparts from the template
            $subpart = $cObj->getSubpart($templateHtml, '###TEMPLATE###');
            $imageFolder = 'uploads/pics/';
            $markerArray = array();
        
            //$content = '<div id="lthsolr_table" class="lthsolr_table">';
//print_r($data);
            foreach ($data as $doc) {
                // Fill marker array
                //'id','last_name_t','first_name_t', 'email_t', 'ou_t', 'title_t', 'orgid_t', 'primary_affiliation_t', 'homepage_t', 
            //'lang_t' => $value['lang'], 'degree_t', 'degree_en_t', 'phone_t', 'hide_on_web_i', 'usergroups_txt'
                $markerArray['###FIRST_NAME###'] = $doc->first_name_t;
                $markerArray['###LAST_NAME###'] = $doc->last_name_t;
                $markerArray['###TITLE###'] = ucfirst($doc->title_t);
                $markerArray['###PHONE###'] = $doc->phone_t;
                $markerArray['###EMAIL###'] = $doc->email_t;
                $markerArray['###SUBJECT###'] = $doc->ou_t;

                if($doc->image_t) {
                    $markerArray['###IMAGE###'] = $imageFolder . $doc->image_t;
                } else {
                    $markerArray['###IMAGE###'] = '/typo3conf/ext/lth_solr/res/placeholder.gif';
                }

                if($doc->{'lth_solr_txt_' . $pageId . '_' . $sl . '_t'}) {
                        $markerArray['###COMMENTS###'] = $doc->{'lth_solr_txt_' . $pageId . '_' . $sl . '_t'};
                } else {
                    $markerArray['###COMMENTS###'] = '';
                }

                $markerArray['###HOMEPAGE###'] = $doc->homepage_t;
                $markerArray['###ORGID###'] = $doc->orgid_t;
                $markerArray['###PRIMARY_AFFILIATION###'] = $doc->primary_affiliation_t;
                $markerArray['###DEGREE###'] = $doc->degree_t;
                $markerArray['###DEGREE_EN###'] = $doc->degree_en_t;

                // Create the content by replacing the content markers in the template
                $content .= $cObj->substituteMarkerArray($subpart, $markerArray);
            }
            
            /*
             * foreach ($facet as $value => $count) {
    echo $value . ' [' . $count . ']<br/>';
}
             */


            //$content .= '</div>';
            
            return $content;
        }
        
        
        private function printFacet($facetType, $facetData)
        {
            $facet = '';
            $i = 0;
            $maxClass = '';
            $more = '';
            
            foreach ($facetData as $value => $count) {
                if($i > 5) {
                    $maxClass = ' class="maxlist-hidden"';
                    $more = '<p class="maxlist-more"><a>Visa alla</a></p>';
                }
                if($count > 0) {
                    $facet .= '<li' . $maxClass . '>' . $value . ' [' . $count . '] <input type="checkbox" class="lth_solr_facet" name="lth_solr_facet" value="' . $facetType . '###' . urlencode($value) . '"></li>';
                }
                $i++;
            }
            return "<ul>$facet</ul>" . $more;
        }
        
        
        private function getStaffData($scope, $pageId, $index)
        {
            $staffArray = array();

            require(__DIR__.'/init.php');

            $client = new Solarium\Client($config);

            $query = $client->createSelect();
            
            //$catVal = 'lth_solr_cat_' . $pageId . '_' . $index . '_ss';
            //$txtVal = 'lth_solr_txt_' . $pageId . '_' . $index . '_t';
            
            //die($catVal);
            $facetSet = $query->getFacetSet();
            $facetSet->createFacetField('title')->setField('title_sort');
            $facetSet->createFacetField('ou')->setField('ou_sort');

            //$query->setFields(array('id','last_name_t','first_name_t', 'email_t', 'ou_t', 'title_t', 'orgid_t', 'primary_affiliation_t', 'homepage_t', 
            //    'lang_t' => $value['lang'], 'degree_t', 'degree_en_t', 'phone_t', 'hide_on_web_i', 'image_t', 'usergroups_txt', 'title_s'));

            //$query->addSort('lth_solr_sort_' . $pageId . '_' . $index . '_i', $query::SORT_ASC);
            $query->addSort('last_name_t', $query::SORT_ASC);
            $query->addSort('first_name_t', $query::SORT_ASC);
            
            $query->setQuery('doctype_s:lucat');
            $query->setQuery('usergroup_txt:'.$scope);

            $response = $client->select($query);
            
            $facet_title = $response->getFacetSet()->getFacet('title');
            $facet_ou = $response->getFacetSet()->getFacet('ou');

            //$GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_devlog', array('msg' => print_r($result), 'crdate' => time()));
            return array($response, $facet_title, $facet_ou);
        }
}


if (defined('TYPO3_MODE') && isset($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/lth_solr/pi2/class.tx_lthsolr_pi2.php'])) {
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/lth_solr/pi2/class.tx_lthsolr_pi2.php']);
}