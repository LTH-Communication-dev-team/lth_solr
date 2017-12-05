<?php
/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */



/**
 * Contains IMGTEXT content object.
 */
class FrontEndClass
{
    /**
     * @var ResourceFactory
     */
    //protected $fileFactory = null;

    /**
     
     */
    
    public function addJsCss($display)
    {
        //Load main js- and css-files
        $syslang = $GLOBALS['TSFE']->config['config']['language'];
        if(!$syslang) {
            $syslang = 'en';
        }
        if($syslang=='se') {
            $syslang='sv';
        }
            
        $GLOBALS["TSFE"]->additionalHeaderData["font-awesome.min"] = "<link rel=\"stylesheet\" type=\"text/css\" href=\"/typo3conf/ext/lth_solr/res/font-awesome.min.css?" . rand(1,100000000) . "\" />";
        $GLOBALS["TSFE"]->additionalFooterData["tx_lthsolr_js"] = "<script language=\"JavaScript\" type=\"text/javascript\" src=\"/typo3conf/ext/lth_solr/res/lth_solr.js?" . rand(1,100000000) . "\"></script>"; 
        $GLOBALS["TSFE"]->additionalHeaderData["tx_lthsolr_css"] = "<link rel=\"stylesheet\" type=\"text/css\" href=\"/typo3conf/ext/lth_solr/res/lth_solr.css?" . rand(1,100000000) . "\" />";
        $GLOBALS["TSFE"]->additionalFooterData["tx_lthsolr_download"] = "<script language=\"JavaScript\" type=\"text/javascript\" src=\"/typo3conf/ext/lth_solr/vendor/download/download.js\"></script>"; 
        $GLOBALS["TSFE"]->additionalFooterData["tx_lthsolr_lang"] = "<script language=\"JavaScript\" type=\"text/javascript\" src=\"/typo3conf/ext/lth_solr/res/lth_solr_lang_$syslang.js?" . rand(1,100000000) . "\"></script>";
        
        if($display === "tagcloud") {
            $GLOBALS["TSFE"]->additionalFooterData["jqcloud.js"] = "<script language=\"JavaScript\" type=\"text/javascript\" src=\"/typo3conf/ext/lth_solr/vendor/jqcloud/jqcloud.js?" . rand(1,100000000) . "\"></script>"; 
            $GLOBALS["TSFE"]->additionalHeaderData["jqcloud.css"] = "<link rel=\"stylesheet\" type=\"text/css\" href=\"/typo3conf/ext/lth_solr/vendor/jqcloud/jqcloud.css?" . rand(1,100000000) . "\" />";              
        }
    }
    
    
    public function searchResult($query, $webSearchScope, $linkStaffDetailPage)
    {
        $content .= '<div class="lth_solr_filter_container" style="max-width:850px;">';
        $content .= '<div style="clear:both;height:50px;">';
            //if($categories != "no_categories") {
                $content .= '<div id="refine" style="float:left;width:200px;background-color:#353838;color:#ffffff;height:50px;padding:17px;font-size:14px;">'
                       // . '<span class="glyphicon glyphicon-filter"></span><span class="refine">'.$filterText.'</span>'
                        . '</div>';
            //}    
        $content .= '<div style="float:left;padding:15px 0px 0px 15px;width:40px;"><span class="glyphicon glyphicon-search"></span></div>';
        $content .= '<div style="float:left;height:50px;width:50%">';
            $content .= '<input style="border:0px;background-color:#fafafa;height:50px;width:100%;box-shadow:none;" type="text" id="searchSiteMain" class="lthsolr_filter" placeholder="" name="query" value="'.$query.'" />';
        $content .= '</div>';

        $content .= '</div>';
        $content .= '<form id="lthsolr_form" action="" method="post" accept-charset="UTF-8">';
            /*<div class="input-group" style="width:90%;">
            <input type="text" class="form-control" id="" name="" value="' . $query . '" />
            <div class="input-group-btn">  
            <button class="btn btn-lg btn-primary" style="height:38px;" type="submit"> Search </button>
            </div>
            </div>*/
        $content .= '<input type="hidden" id="no_cache" name="no_cache" value="1" />
            <input type="hidden" id="webSearchScope" value="' . $webSearchScope . '" />
            <input type="hidden" id="linkStaffDetailPage" value="' . $linkStaffDetailPage . '" />    
        </form>';
        //$content .= "</header>";
$content .= '</div>';

        $content .= '<div id="lthsolr_search_container">';
        //people
        $content .= '<div style="overflow:hidden;clear:both;display:none;" class="table-responsive"><div id="lthsolr_people_header"></div>';
        $content .= '<table id="lthsolr_staff_container" class="table"><tbody></tbody></table></div>';
        $content .= file_get_contents("/var/www/html/typo3/typo3conf/ext/lth_solr/templates/contact_search.html");

        //pages
        $content .= '<div style="clear:both;display:none;" class="table-responsive"><div id="lthsolr_pages_header"></div>';
        $content .= '<ul id="lthsolr_pages_container" class="table"></ul></div>';
        $content .= file_get_contents("/var/www/html/typo3/typo3conf/ext/lth_solr/templates/pages_search.html");

        //courses
        $content .= '<div style="display:none;" class="table-responsive"><div id="lthsolr_courses_header"></div>';
        $content .= '<table id="lthsolr_courses_container" class="table"><tbody></tbody></table></div>';
        $content .= file_get_contents("/var/www/html/typo3/typo3conf/ext/lth_solr/templates/courses_search.html");
        
        $content .= '</div>';
        return $content;
    }
        
        
    public function widget($query, $display)
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
    
    
    public function showStaff($uuid, $html_template, $noItemsToShow)
    {      
        $lth_solr_uuid['fe_users'][] = $uuid;
        if(count($lth_solr_uuid > 0)) {
            $scope = urlencode(json_encode($lth_solr_uuid));
        }
        
        //Staff 
        $content .= '<div id="lthsolr_show_staff_container" style="min-height:280px;"></div>';
        $content .= file_get_contents("/var/www/html/typo3/typo3conf/ext/lth_solr/templates/contact_large.html");

        //Publications
        //$content .= '<div id="lthsolr_publications_container"><div id="lthsolr_publications_header"></div></div>';
        $content .= $this->listPublications('', '', '', '', '', '', '');
        $content .= file_get_contents("/var/www/html/typo3/typo3conf/ext/lth_solr/templates/publication_simple.html");

        //Projects
        //$content .= '<div id="lthsolr_projects_container"><div id="lthsolr_projects_header"></div></div>';
        //$content .= file_get_contents("/var/www/html/typo3/typo3conf/ext/lth_solr/templates/project_simple.html");        
 
        //Map
        $content .= '<div id="lthsolr_map" style="cursor:pointer;">'
                . '<div style="position:relative;">'
                . '<img src="typo3conf/ext/lth_solr/res/lthmap.gif" />'
                . '<img id="lthsolr_pin" style="width:15%;height:15%;position:absolute;top:0px;right:0px;z-index:1000;" src="typo3conf/ext/lth_solr/res/pin.png" />'
                //. '<img id="lthsolr_pinClient" style="width:15%;height:15%;position:absolute;top:0px;right:0px;z-index:1000;display:none;" src="typo3conf/ext/lth_solr/res/pin.png" />'
                . '</div>'
                . '</div>';

        //mapModal
        $content .= '<!-- mapModal -->
            <div id="mapModal" class="modal fade" role="dialog">
              <div class="modal-dialog">

                <!-- Modal content-->
                <div class="modal-content">
                  <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h1 class="modal-title">LTH map</h1>
                  </div>
                  <div class="modal-body" style="position:relative;font-size:14px;font-weight:bold;">
                    <img src="typo3conf/ext/lth_solr/res/lthmap_large.png" />
                    <img id="lthsolr_modal_pin" style="width:15%;height:15%;position:absolute;top:0px;right:0px;z-index:1100;" src="typo3conf/ext/lth_solr/res/pin.png" />
                  </div>
                  <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                  </div>
                </div>

              </div>
            </div>';

        //hidden fields
        $content .= '
            <input type="hidden" id="lth_solr_scope" value="' . $scope . '" />
            <input type="hidden" id="lth_solr_detail_action" value="showStaff" />
            <input type="hidden" id="lth_solr_staff_pos" value="' . $showStaffPos . '" />
            <input type="hidden" id="lth_solr_no_items" value="' . $noItemsToShow . '" />';

        return '<div style="max-width:800px;position:relative;font-size:17px;line-height:27px;">' . $content . '</div>';
    }
    
    
    public function listStaff($scope, $html_template, $noItemsToShow, $categories)
    {
        $syslang = $GLOBALS['TSFE']->config['config']['language'];
        if($syslang==='en') {
            $filterText = 'Categories';
        } else {
            $filterText = 'Kategorier';
        }
        $clientIp = $_SERVER['REMOTE_ADDR'];

        $content .= '<p class="lth_solr_filter_container">';
            $content .= '<i class="fa fa-filter fa-lg slsGray50"></i><a class="slsPadL5 refine">Filter</a>';
            $content .= '<i class="fa fa-search fa-lg slsGray50"></i>';
            $content .= '<input style="border:0px;box-shadow:none;" type="text" id="lthsolr_staff_filter" class="lthsolr_filter" placeholder="" name="lthsolr_filter" value="" />';
            
            /*$content .= '<div style="clear:both;height:50px;">';
                if($categories != "no_categories") {
                    $content .= '<div id="refine" style="float:left;width:200px;background-color:#353838;color:#ffffff;height:50px;padding:17px;font-size:14px;">'
                            . '<span class="glyphicon glyphicon-filter"></span><span class="refine">'.$filterText.'</span>'
                            . '</div>';
                }    
                $content .= '<div style="float:left;padding:15px 0px 0px 15px;width:40px;"><span class="glyphicon glyphicon-search"></span></div>';
                $content .= '<div style="float:left;height:50px;width:50%">';
                    $content .= '<input style="" type="text" id="lthsolr_staff_filter" class="lthsolr_filter" placeholder="" name="lthsolr_filter" value="" />';
                $content .= '</div>';

            $content .= '</div>';*/

        $content .= '</p>';
        
        $content .= '<p style="" id="lthsolr_staff_header"></p>';

        $content .= '<div style="width:100%;clear:both;">';

            $content .= '<div id="lth_solr_facet_container"></div>';

            $content .= '<div id="lthsolr_staff_container">';
                
            $content .= '</div>';

        $content .= '</div>'; 

        $content .= file_get_contents("/var/www/html/typo3/typo3conf/ext/lth_solr/templates/contact.html");

        $content .= '
            <input type="hidden" id="lth_solr_scope" value="' . $scope . '" />
            <input type="hidden" id="lth_solr_action" value="listStaff" />
            <input type="hidden" id="lth_solr_categories" value="' . $categories . '" />
            <input type="hidden" id="lth_solr_no_items" value="' . $noItemsToShow . '" />';

            if(substr($clientIp,0,7) === '130.235' || $clientIp === '127.0.0.1') {
                $content .= '<input type="hidden" id="lth_solr_lu" value="yes" />';
                
                //exportModal
                $content .= '<!-- exportModal -->
                    <div id="exportModal" class="modal fade" role="dialog">
                      <div class="modal-dialog">

                        <!-- Modal content-->
                        <div class="modal-content">
                          <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                            <h1 class="modal-title">Export staff</h1>
                          </div>
                          <div class="modal-body" style="position:relative;">
                            </div>
                          <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                          </div>
                        </div>

                      </div>
                    </div>';
            }  

        return '<div style="font-size:17px;">'.$content.'</div>';
    }
    
    
    public function listPublications($scope, $noItemsToShow, $categories, $keyword, $pageTitle, $publicationCategories, $publicationCategoriesSwitch)
    {   
        if($syslang==='en') {
            $filterText = 'Categories';
        } else {
            $filterText = 'Kategorier';
        }
        
        $clientIp = $_SERVER['REMOTE_ADDR'];
        
        /*$content .= '<div class="lth_solr_filter_container">';

            $content .= '<div style="clear:both;height:50px;">';
                if($categories != "no_categories") {
                    $content .= '<div id="refine" style="float:left;width:200px;background-color:#353838;color:#ffffff;height:50px;padding:17px;font-size:14px;"><span class="glyphicon glyphicon-filter"></span><span class="refine">' . $filterText . '</span></div>';
                }
            $content .= '<div style="float:left;padding:15px 0px 0px 15px;width:40px;"><span class="glyphicon glyphicon-search"></span></div>';
            $content .= '<div style="float:left;height:50px;">';
                $content .= '<input style="border:0px;background-color:#fafafa;width:100%;height:50px;box-shadow:none;" type="text" id="lthsolr_publications_filter" class="lthsolr_filter" placeholder="" name="lthsolr_filter" value="" />';
            $content .= '</div>';

        $content .= '</div>';

        $content .= '</div>';*/
        $content .= '<p class="lth_solr_filter_container">';
            $content .= '<i class="fa fa-filter fa-lg slsGray50"></i><a class="slsPadL5 refine">Filter</a>';
            $content .= '<i class="fa fa-search fa-lg slsGray50"></i>';
            $content .= '<input style="border:0px;box-shadow:none;" type="text" id="lthsolr_publications_filter" class="lthsolr_filter" placeholder="" name="lthsolr_filter" value="" />';
        $content .= '</p>';
        $content .= '<div style="clear:both;width:100%;height:30px;margin:15px 0px 15px 0px;"><div style="width:50%;float:left;" id="lthsolr_publications_header"></div><div style="float:right;" id="lthsolr_publications_sort"></div></div>';

        $content .= '<div style="width:100%;clear:both;">';
            $content .= '<div id="lth_solr_facet_container"></div>';
            $content .= '<div id="lthsolr_publications_container"></div>';
        $content .= '</div>';
        

        $content .= file_get_contents("/var/www/html/typo3/typo3conf/ext/lth_solr/templates/publication_simple.html");

        if($scope) {
            $content .= '
                <input type="hidden" id="lth_solr_scope" value="' . $scope . '" />
                <input type="hidden" id="lth_solr_action" value="listPublications" />
                <input type="hidden" id="lth_solr_keyword" value="' . $keyword . '" />    
                <input type="hidden" id="lth_solr_no_items" value="' . $noItemsToShow . '" />
                <input type="hidden" id="lth_solr_pagetitle" value="' . $pageTitle . '" />
                <input type="hidden" id="lth_solr_publicationCategories" value="' . $publicationCategories . '" />
                <input type="hidden" id="lth_solr_publicationCategoriesSwitch" value="' . $publicationCategoriesSwitch . '" />'; 
            }
            
            if(substr($clientIp,0,7) === '130.235' || $clientIp === '127.0.0.1') {
                $content .= '<input type="hidden" id="lth_solr_lu" value="yes" />';
                //exportModal
                $content .= '<!-- exportModal -->
                    <div id="exportModal" class="modal fade" role="dialog">
                      <div class="modal-dialog">

                        <!-- Modal content-->
                        <div class="modal-content">
                          <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                            <h1 class="modal-title">Export publications</h1>
                          </div>
                          <div class="modal-body" style="position:relative;">
                            </div>
                          <div style="clear:both;" class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                          </div>
                        </div>

                      </div>
                    </div>';
            } 
        
        return '<div style="font-size:17px;">'.$content.'</div>';
    }
    
    
    public function listTagCloud($scope, $noItemsToShow, $categories)
    {
        $content = '<div id="lthsolr_tagcloud_container"></div>';

        $content .= '
            <input type="hidden" id="lth_solr_scope" value="' . $scope . '" />
            <input type="hidden" id="lth_solr_action" value="listTagCloud" />';
        return $content;
    }


    public function showPublication($uuid)
    {
        $content = '<div id="lth_solr_container" ></div>';

        $content .= file_get_contents("/var/www/html/typo3/typo3conf/ext/lth_solr/templates/publication_presentation.html");

        $content = str_replace('###more###', "more", $content);

        $content .= '
            <input type="hidden" id="lth_solr_uuid" value="' . $uuid . '" />
            <input type="hidden" id="lth_solr_action" value="showPublication" />';

        return $content;
    }
        
}
