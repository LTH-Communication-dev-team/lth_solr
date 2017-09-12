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
class tx_lthsolr_pi2 extends \TYPO3\CMS\Frontend\Plugin\AbstractPlugin {
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
            $fe_groups = $this->pi_getFFvalue($piFlexForm, "fe_groups", "sDEF", $lDef[$index]);
            $fe_users = $this->pi_getFFvalue($piFlexForm, "fe_users", "sDEF", $lDef[$index]);
            $staffHomepagePath = $this->pi_getFFvalue($piFlexForm, "staffHomepagePath", "sDEF", $lDef[$index]);
            
            $scope = array();
            if($fe_groups) {
                $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('title','fe_groups',"uid in(" . explode('|',$fe_groups)[0].")");
                while ($row = $GLOBALS["TYPO3_DB"]->sql_fetch_assoc($res)) {
                    $scope['fe_groups'][] = explode('__', $row['title'])[0];
                }
                $GLOBALS['TYPO3_DB']->sql_free_result($res);
            } 
            if($fe_users) {
                $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('username','fe_users',"uid in(" . explode('|',$fe_users)[0].")");
                while ($row = $GLOBALS["TYPO3_DB"]->sql_fetch_assoc($res)) {
                    $scope['fe_users'][] = $row['username'];
                }
                $GLOBALS['TYPO3_DB']->sql_free_result($res);
            }
            if(count($scope > 0)) {
                $scope = urlencode(json_encode($scope));
            }
            
            $clientIp = $_SERVER['REMOTE_ADDR'];
            
            /*if($scope) {
                $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('title','fe_groups',"uid in($scope)");
                while ($row = $GLOBALS["TYPO3_DB"]->sql_fetch_assoc($res)) {
                    $title[] = explode('__', $row['title'])[0];
                }
                if($title) {
                    $scope = implode(',', $title);
                }
                $GLOBALS['TYPO3_DB']->sql_free_result($res);
            }*/
            //$detailPage = $this->pi_getFFvalue($piFlexForm, "detailpage", "sDEF", $lDef[$index]);
            $categories = $this->pi_getFFvalue($piFlexForm, "categories", "sDEF", $lDef[$index]);
            $customCategories = $this->pi_getFFvalue($piFlexForm, "customcategories", "sDEF", $lDef[$index]);
            $categoriesThisPage = $this->pi_getFFvalue($piFlexForm, "categoriesthispage", "sDEF", $lDef[$index]);
            $noItemsToShow = $this->pi_getFFvalue($piFlexForm, "noItemsToShow", "sDEF", $lDef[$index]);
            $publicationDetailPage = $this->pi_getFFvalue($piFlexForm, "publicationDetailPage", "sDEF", $lDef[$index]);
            if($publicationDetailPage) {
                $publicationDetailPage = $GLOBALS['TSFE']->cObj->typoLink_URL(
                    array(
                        'parameter' => $publicationDetailPage,
                        'forceAbsoluteUrl' => true,
                    )
                );
            }
            
            $uuid = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('uuid');
            if(strstr($uuid,")")) {
                $uuid = rtrim(array_pop(explode('(',$uuid)),")");
            }
            $pid = $GLOBALS['TSFE']->page['pid'];
            //$solrId = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('solrid');
            $link = $_SERVER['PHP_SELF'];
            $link_array = explode('/',$link);
            //$solrId = end($link_array);
            //$solrId = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP("query");
            $ip = $_SERVER['REMOTE_ADDR'];
            
            $action = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('action');
            $query = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('query');
            if($action=='detail' && $query) {
                $solrId = $query;
            }
            
            $syslang = $GLOBALS['TSFE']->config['config']['language'];
            if(!$syslang) {
                $syslang = 'en';
            }
            if($syslang=='se') {
                $syslang='sv';
            }
            
            /*load files needed for datatables
            $GLOBALS["TSFE"]->additionalHeaderData["jquery.dataTables.min.css"] = "<link rel=\"stylesheet\" type=\"text/css\" href=\"/typo3conf/ext/lth_solr/vendor/datatables/css/jquery.dataTables.min.css\" />";
            $GLOBALS["TSFE"]->additionalHeaderData["responsive.dataTables.min.css"] = "<link rel=\"stylesheet\" type=\"text/css\" href=\"/typo3conf/ext/lth_solr/vendor/datatables/css/responsive.dataTables.min.css\" />";
            $GLOBALS["TSFE"]->additionalHeaderData["buttons.dataTables.min.css"] = "<link rel=\"stylesheet\" type=\"text/css\" href=\"/typo3conf/ext/lth_solr/vendor/datatables/css/buttons.dataTables.min.css\" />";
            $GLOBALS["TSFE"]->additionalFooterData["jquery.dataTables.min.js"] = "<script language=\"JavaScript\" type=\"text/javascript\" src=\"/typo3conf/ext/lth_solr/vendor/datatables/js/jquery.dataTables.js\"></script>";
            $GLOBALS["TSFE"]->additionalFooterData["dataTables.buttons.js"] = "<script language=\"JavaScript\" type=\"text/javascript\" src=\"/typo3conf/ext/lth_solr/vendor/datatables/js/dataTables.buttons.js\"></script>";
            $GLOBALS["TSFE"]->additionalFooterData["jszip.min.js"] = "<script language=\"JavaScript\" type=\"text/javascript\" src=\"//cdnjs.cloudflare.com/ajax/libs/jszip/2.5.0/jszip.min.js\"></script>";
            $GLOBALS["TSFE"]->additionalFooterData["pdfmake.min.js"] = "<script language=\"JavaScript\" type=\"text/javascript\" src=\"//cdn.rawgit.com/bpampuch/pdfmake/0.1.18/build/pdfmake.min.js\"></script>";
            $GLOBALS["TSFE"]->additionalFooterData["vfs_fonts.js"] = "<script language=\"JavaScript\" type=\"text/javascript\" src=\"//cdn.rawgit.com/bpampuch/pdfmake/0.1.18/build/vfs_fonts.js\"></script>";
            $GLOBALS["TSFE"]->additionalFooterData["buttons.html5.js"] = "<script language=\"JavaScript\" type=\"text/javascript\" src=\"/typo3conf/ext/lth_solr/vendor/datatables/js/buttons.html5.min.js\"></script>";
            $GLOBALS["TSFE"]->additionalFooterData["dataTables.responsive.min.js"] = "<script language=\"JavaScript\" type=\"text/javascript\" src=\"/typo3conf/ext/lth_solr/vendor/datatables/js/dataTables.responsive.min.js\"></script>";
*/          
            //Load main js- and css-files
            $GLOBALS["TSFE"]->additionalFooterData["tx_lthsolr_lang"] = "<script language=\"JavaScript\" type=\"text/javascript\" src=\"/typo3conf/ext/lth_solr/res/lth_solr_lang_$syslang.js\"></script>"; 
            $GLOBALS["TSFE"]->additionalFooterData["tx_lthsolr_js"] = "<script language=\"JavaScript\" type=\"text/javascript\" src=\"/typo3conf/ext/lth_solr/res/lth_solr.js\"></script>"; 
            $GLOBALS["TSFE"]->additionalHeaderData["tx_lthsolr_css"] = "<link rel=\"stylesheet\" type=\"text/css\" href=\"/typo3conf/ext/lth_solr/res/lth_solr.css\" />";
            $GLOBALS["TSFE"]->additionalFooterData["tx_lthsolr_download"] = "<script language=\"JavaScript\" type=\"text/javascript\" src=\"/typo3conf/ext/lth_solr/vendor/download/download.js\"></script>"; 

                    
            /*$content = '';
            $facetContent = '';
            $staffList = '';
            $facets = '';
            $lu_user = '';
            
            if($hideFilter && $categories == 'no_categories') {
            } else {
                $content .= '<div class="lth_solr_filter_container">';
                        }
            
            $content .= '<div class="form-group">';
            if(!$hideFilter) {
                $content .= '<div class="input-group">';
                $content .= '<div class="input-group-addon"><span class="glyphicon glyphicon-filter" aria-hidden="true"></span></div><input type="text" id="lthsolr_staff_filter" class="form-control" name="lthsolr_filter" title="' . $this->pi_getLL("filter_search_results") . '" value="" />';
                $content .= '</div>';
            }
            $content .= '</div>';

            $content .= '<div class="lth_solr_facet_container"></div>';
            
            if(substr($clientIp,0,7) === '130.235' || $clientIp === '127.0.0.1') {
                $content .= '<div id="lth_solr_tools" class="form-group">';
                $content .= '<a href="javascript:"><span class="glyphicon glyphicon-cog" aria-hidden="true"></span>' . $this->pi_getLL("export_data") . '</a>';
                $content .= '</div>';
            }
            
            if($hideFilter && $categories == 'no_categories') {
            } else {
                $content .= '</div>';
            }
            
            if(substr($clientIp,0,7) === '130.235' || $clientIp === '127.0.0.1') {
                $content .= '<div id="lth_solr_hidden_tools" class="form-group">';
                $content .= '<span style="margin-left:15px;" class="glyphicon glyphicon-export" aria-hidden="true"></span><a href="javascript:" class="exportStaffCsv">' . $this->pi_getLL("export_csv") . '</a>'
                        . '<span style="margin-left:15px;" class="glyphicon glyphicon-export" aria-hidden="true"></span><a href="javascript:" class="exportStaffTxt">' . $this->pi_getLL("export_txt") . '</a>';
                $content .= '</div>';
            }            
            
            $content .= '<div id="lthsolr_staff_header"></div>';
            
            $content .= '<div id="lthsolr_staff_container"></div>';
                        
            $content .= file_get_contents("/var/www/html/typo3/typo3conf/ext/lth_solr/templates/" . $html_template);
            
            if($solrId) {
                $content .= '<input type="hidden" id="lth_solr_action" value="showStaff" />' .
                    '<input type="hidden" id="lth_solr_scope" value="' . $scope . '" />';
            } else {
                $content .= '
                    <input type="hidden" id="lth_solr_scope" value="' . $scope . '" />
                    <input type="hidden" id="lth_solr_syslang" value="' . $syslang . '" />
                    <input type="hidden" id="pid" value="' . $pid . '" />
                    <input type="hidden" id="lth_solr_action" value="listStaff" />
                    <input type="hidden" id="lth_solr_categories" value="' . $categories . '" />
                    <input type="hidden" id="lth_solr_custom_categories" value="' . $customCategories . '" />
                    <input type="hidden" id="fe_user" value="' . $GLOBALS['TSFE']->fe_user->user . '" />
                    <input type="hidden" id="lu_user" value="' . $lu_user . '" />
                    <input type="hidden" id="categoriesThisPage" value="' . $categoriesThisPage . '" />
                    <input type="hidden" id="introThisPage" value="' . $introThisPage . '" />
                    <input type="hidden" id="lth_solr_no_items" value="' . $noItemsToShow . '" />
                    <div style="clear:both"></div>';
            }*/
            if($uuid) {
                $content = $this->showStaff($uuid, $html_template, $noItemsToShow, $selection, $publicationDetailPage);
            } else {
                $content = $this->listStaff($scope, $html_template, $noItemsToShow, $selection, $categories, $staffHomepagePath);
            }
            return $content;
	}
        
        
        private function listStaff($scope, $html_template, $noItemsToShow, $selection, $categories, $staffHomepagePath)
        {
            $clientIp = $_SERVER['REMOTE_ADDR'];
            
            $content .= '<style>.glyphicon-search {font-size: 25px;}.glyphicon-filter {font-size: 15px;}.glyphicon-export{cursor:pointer;}</style>';
            $content .= '<div class="lth_solr_filter_container">';
            
                //$content .= '<div style="font-weight:bold;">' . $this->pi_getLL("filter") . '</div>';
              
            $content .= '<div style="clear:both;height:50px;">';
            if($categories != "no_categories") {
                $content .= '<div id="refine" style="float:left;width:30%;background-color:#353838;color:#ffffff;height:50px;padding:17px;font-size:16px;"><span class="glyphicon glyphicon-filter"></span><span class="refine">Filter</span></div>';
            }    
            $content .= '<div style="float:left;padding:15px 0px 0px 15px;width:10%"><span class="glyphicon glyphicon-search"></span></div>';
            $content .= '<div style="float:left;padding-top:10px;width:60%">';
            $content .= '<input style="border:0px;background-color:#fafafa;width:100%;box-shadow:none;" type="text" id="lthsolr_staff_filter" class="lthsolr_filter" placeholdera="' . $this->pi_getLL("freetext") . '" name="lthsolr_filter" value="" />';
            $content .= '</div>';
            
            $content .= '</div>';
                
            $content .= '</div>';    
            
            $content .= '<div style="clear:both;">';
            
            $content .= '<div id="lth_solr_facet_container"></div>';
            
            $content .= '<div id="lthsolr_staff_container"><div style="clear:both;height:20px;" id="lthsolr_staff_header"></div></div>';
            
            $content .= '</div>'; 
            
            $content .= file_get_contents("/var/www/html/typo3/typo3conf/ext/lth_solr/templates/$html_template");
            
            $content .= '
                <input type="hidden" id="lth_solr_scope" value="' . $scope . '" />
                <input type="hidden" id="lth_solr_publicationdetailpage" value="' . $detailPage . '" />
                <input type="hidden" id="lth_solr_action" value="listStaff" />
                <input type="hidden" id="lth_solr_selection" value="' . $selection . '" />
                <input type="hidden" id="lth_solr_categories" value="' . $categories . '" />
                <input type="hidden" id="lth_solr_staffhomepagepath" value="' . $staffHomepagePath . '" />    
                <input type="hidden" id="lth_solr_no_items" value="' . $noItemsToShow . '" />';
            
                if(substr($clientIp,0,7) === '130.235' || $clientIp === '127.0.0.1') {
                    $content .= '<input type="hidden" id="lth_solr_lu" value="yes" />';
                }  
            
            return $content;
        }
        
        
        private function showStaff($uuid, $html_template, $noItemsToShow, $selection, $publicationDetailPage)
        {
            //$uuid =  substr(array_pop(explode('[', $uuid)),0,-1);
            //Staff center
            //if($show[0]) {
                $content .= '<div id="lthsolr_staff_container"></div>';
                $content .= file_get_contents("/var/www/html/typo3/typo3conf/ext/lth_solr/templates/contact_with_image_and_ingress_and_adress.html");
            //}
            
            //Publications
            //if($show[1]) {
                $content .= '<div id="lthsolr_publications_container"><div id="lthsolr_publications_header"></div></div>';
                $content .= file_get_contents("/var/www/html/typo3/typo3conf/ext/lth_solr/templates/publication_simple.html");
            //}
            
            //Projects
            //if($show[2]) {
                $content .= '<div id="lthsolr_projects_container"><div id="lthsolr_projects_header"></div></div>';
                $content .= file_get_contents("/var/www/html/typo3/typo3conf/ext/lth_solr/templates/project_simple.html");
            //}
            
            //hidden fields
            $content .= '
                <input type="hidden" id="lth_solr_publicationdetailpage" value="' . $publicationDetailPage . '" />
                <input type="hidden" id="lth_solr_projectdetailpage" value="' . $projectDetailUrl . '" />
                <input type="hidden" id="lth_solr_scope" value="' . $uuid . '" />
                <input type="hidden" id="lth_solr_detail_action" value="showStaff" />
                <input type="hidden" id="lth_solr_staff_pos" value="' . $showStaffPos . '" />
                <input type="hidden" id="lth_solr_no_items" value="' . $noItemsToShow . '" />';
            
            return $content;
        }
        
        
        
      /*  private function printStaffList($data, $html_template, $pageId, $sl)
        {
                // Get the template
            $cObj = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('tslib_cObj');
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
        }*/
        
}


if (defined('TYPO3_MODE') && isset($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/lth_solr/pi2/class.tx_lthsolr_pi2.php'])) {
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/lth_solr/pi2/class.tx_lthsolr_pi2.php']);
}