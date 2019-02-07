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
include __DIR__ . "/../service/ajax.php";
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
           // print_r($conf);
            
            $type = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('type');
            if($type) {
                $pageId = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('id');
                $L = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('L');
                if($pageId) {
                    $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('pi_flexform','tt_content',"list_type='lth_solr_pi3' AND deleted=0 AND hidden=0 AND pid=".intval($pageId),'','','');
                    while ($row = $GLOBALS["TYPO3_DB"]->sql_fetch_assoc($res)) {
                        $pi_flexform = $row['pi_flexform'];
                    }
                    $GLOBALS['TYPO3_DB']->sql_free_result($res);
                    if($pi_flexform) {
                        $xml = @simplexml_load_string($pi_flexform);
                        if($xml) {
                            $test = $xml->data->sheet[0]->language;
                            if($test) {
                                foreach ($test->field as $n) {
                                    foreach($n->attributes() as $name => $val) {
                                        if ($val == 'fe_groups') {
                                            $fe_groups = (string)$n->value;
                                        }
                                        if ($val == 'fe_users') {
                                            $fe_users = (string)$n->value;
                                        }
                                        if ($val == 'publicationCategories') {
                                            $publicationCategories = (string)$n->value;
                                        }
                                        if ($val == 'publicationCategoriesSwitch') {
                                            $publicationCategoriesSwitch = (string)$n->value;
                                        }
                                    }
                                }
                                $lth_solr_uuid = array();
                                if($fe_users) {
                                    /*$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('lth_solr_uuid','fe_users',"uid in(" . $fe_users . ") AND lth_solr_uuid!=''");
                                    while ($row = $GLOBALS["TYPO3_DB"]->sql_fetch_assoc($res)) {
                                        $lth_solr_uuid['fe_users'][] = $row['lth_solr_uuid'];
                                    }
                                    $GLOBALS['TYPO3_DB']->sql_free_result($res);*/
                                    $fe_usersArray = explode(',', $fe_users);
                                    foreach ($fe_usersArray as $fkey => $fvalue) {
                                        $lth_solr_uuid['fe_users'][] = $fvalue;
                                    }
                                    $scope = urlencode(json_encode($lth_solr_uuid));
                                }
                                if($fe_groups) {
                                    $tmpArray = explode(',',$fe_groups);
                                    foreach($tmpArray as $tmpValue) {
                                        $lth_solr_uuid['fe_groups'][] = $tmpValue;
                                    }
                                    $scope = urlencode(json_encode($lth_solr_uuid));
                                }
                                if($scope) {
                                    $settings = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['lth_solr']);
                                    $syslang = "sv";
                                    $config = array(
                                        'endpoint' => array(
                                            'localhost' => array(
                                                'host' => $settings['solrHost'],
                                                'port' => $settings['solrPort'],
                                                'path' => "/solr/core_$syslang/",//$settings['solrPath'],
                                                'timeout' => $settings['solrTimeout']
                                            )
                                        )
                                    );


                                    if (!$settings['solrHost'] || !$settings['solrPort'] || !$settings['solrPath'] || !$settings['solrTimeout']) {
                                        return 'Please make all settings in extension manager';
                                    }
                                    $ajaxObj = new lthSolrAjax();
                                    $content = $ajaxObj->listPublications('', $scope, '', $config, '1000', '0', '', '', '', '', '', 'listPublication', $publicationCategories, '');
                                    if($content) {
                                        $contentArray = json_decode($content,true);
                                        foreach($contentArray['data'] as $key =>$value) {
                                            $xmlContent .= '<item>';
                                            $xmlContent .= '<title>' . $value['documentTitle'] . '</title>';
                                            $xmlContent .= '<link>????</link>';
                                            $xmlContent .= '<description>' . $value['authorName'] . '</description>';
                                            $xmlContent .= '</item>';
                                        }
                                    }
                                }
                            }
                        }
                    }
                    return '<rss version="2.0"><channel>' . $xmlContent . '</channel></rss>';
                }
            }

            $this->pi_initPIflexForm();
            $piFlexForm = $this->cObj->data["pi_flexform"];
            $index = 0;//$GLOBALS["TSFE"]->sys_language_uid;
            $sDef = current($piFlexForm["data"]);       
            $lDef = array_keys($sDef);
            $display = $this->pi_getFFvalue($piFlexForm, "display", "sDEF", $lDef[$index]);
            $displayLayout = $this->pi_getFFvalue($piFlexForm, "displayLayout", "sDEF", $lDef[$index]);
            $backgroundcolor = $this->pi_getFFvalue($piFlexForm, "backgroundcolor", "sDEF", $lDef[$index]);
            $header = $this->pi_getFFvalue($piFlexForm, "header", "sDEF", $lDef[$index]);
            
            $display = str_replace('list','publications',$display);
            if(!$displayLayout) {
                $displayLayout = 'fullList';
            }
            
            $fe_groups = $this->pi_getFFvalue($piFlexForm, "fe_groups", "sDEF", $lDef[$index]);
            $fe_users = $this->pi_getFFvalue($piFlexForm, "fe_users", "sDEF", $lDef[$index]);
            $projects = $this->pi_getFFvalue($piFlexForm, "projects", "sDEF", $lDef[$index]);
            $categories = $this->pi_getFFvalue($piFlexForm, "categories", "sDEF", $lDef[$index]);
            $publicationCategories = $this->pi_getFFvalue($piFlexForm, "publicationCategories", "sDEF", $lDef[$index]);
            $publicationCategoriesSwitch = $this->pi_getFFvalue($piFlexForm, "publicationCategoriesSwitch", "sDEF", $lDef[$index]);
            //$staffDetailPage = $this->pi_getFFvalue($piFlexForm, "staffDetailPage", "sDEF", $lDef[$index]);
            //$projectDetailPage = $this->pi_getFFvalue($piFlexForm, "projectDetailPage", "sDEF", $lDef[$index]);
            $noItemsToShow = $this->pi_getFFvalue($piFlexForm, "noItemsToShow", "sDEF", $lDef[$index]);
            $displayFromSimpleList = $this->pi_getFFvalue($piFlexForm, "displayFromSimpleList", "sDEF", $lDef[$index]);
            if($displayFromSimpleList) {
                $url = $GLOBALS['TSFE']->cObj->typoLink_URL(
                    array(
                        'parameter' => $displayFromSimpleList,
                        'forceAbsoluteUrl' => true,
                    )
                );
                $displayFromSimpleList = $url;
            }
            
            $showType = '';
            $pageTitle = '';
            $keyword;
            $uuid;
            /*if($detailPage) {
                $detailPage = $this->detailUrl($detailPage);
            }*/
            
            /*if($staffDetailPage) {
                $staffDetailPage = $this->detailUrl($staffDetailPage);
            }
            
            if($projectDetailPage) {
                $projectDetailPage = $this->detailUrl($projectDetailPage);
            }*/

            $uuid = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('uuid');
            $keyword = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('keyword');
            
            if(strstr($uuid,"(publication)")) {
                $showType = 'publication';
                $uuid = str_replace('(publication)', '', $uuid);
                $uuid = rtrim(array_pop(explode('(',$uuid)),')');
            } else if(strstr($uuid,"(department)")) {
                $showType = 'department';
                $uuid = str_replace('(department)', '', $uuid);
                $uuid = rtrim(array_pop(explode('(',$uuid)),')');
            } else if(strstr($uuid,"(author)")) {
                $showType = 'author';
                $uuid = str_replace('(author)', '', $uuid);
                $uuid = rtrim(array_pop(explode('(',$uuid)),')');
            } else if($uuid) {
                $pageTitle = array_pop(explode('/',array_shift(explode('(',$uuid))));
                $uuid = rtrim(array_pop(explode('(',$uuid)),")");
                $showType = 'publication';
            }

            $FrontEndClass = new FrontEndClass();
            $FrontEndClass->addJsCss($display);

            $lth_solr_uuid = array();

            if($fe_users) {
                $fe_usersArray = explode(',', $fe_users);
                foreach ($fe_usersArray as $fkey => $fvalue) {
                    $lth_solr_uuid['fe_users'][] = $fvalue;
                }
                $scope = urlencode(json_encode($lth_solr_uuid));
            }
            if($fe_groups) {
                $tmpArray = explode(',',$fe_groups);
                foreach($tmpArray as $tmpValue) {
                    $lth_solr_uuid['fe_groups'][] = $tmpValue;
                }
                $scope = urlencode(json_encode($lth_solr_uuid));
            }
            if($projects) {
                $tmpArray = explode(',',$projects);
                foreach($tmpArray as $tmpValue) {
                    $lth_solr_uuid['projects'][] = $tmpValue;
                }
                $scope = urlencode(json_encode($lth_solr_uuid));
            }
                
            if($showType === 'publication') {
                $lth_solr_uuid = array();
                $lth_solr_uuid['publication'][] = $uuid;
                $scope = urlencode(json_encode($lth_solr_uuid));
                $content .= $FrontEndClass->showPublication($scope, $uuid);
            } else if($showType==='department') {
                $lth_solr_uuid = array();
                $lth_solr_uuid['fe_groups'][] = $uuid;
                $scope = urlencode(json_encode($lth_solr_uuid));
                $content .= $FrontEndClass->listPublications($scope, $noItemsToShow, $categories, '', $pageTitle, $publicationCategories, 
                        $publicationCategoriesSwitch, $display, $displayLayout, '', $backgroundcolor, '');
            } else if($showType==='author') {
                //$lth_solr_uuid = array();
                $lth_solr_uuid['fe_users'][] = $uuid;
                $scope = urlencode(json_encode($lth_solr_uuid));
                $content = $FrontEndClass->showStaff($scope, $html_template, $noItemsToShow, $selection);
            } else {
                if($display === "tagcloud" && !$keyword) {
                    $content .= $FrontEndClass->listTagCloud($scope, $noItemsToShow, $categories);
                } else {
                    if($keyword) {
                        $keyword = urlencode($keyword);
                        $display = "publications";
                    }
                    $content .= $FrontEndClass->listPublications($scope, $noItemsToShow, $categories, $keyword, $pageTitle, 
                            $publicationCategories, $publicationCategoriesSwitch, $display, $displayLayout, $displayFromSimpleList, $backgroundcolor, $header);
                }
            }
            //$this->debug($content);
	
            return $content;
	}
             
        
        /* private function detailUrl($detailPage)
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
        }*/
}


if (defined('TYPO3_MODE') && isset($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/lth_solr/pi3/class.tx_lthsolr_pi3.php'])) {
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/lth_solr/pi3/class.tx_lthsolr_pi3.php']);
}