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
    
    
    public function listStatistics($syslang, $round, $program, $insideInfobox, $backgroundcolor)
    {
        $content = '';
        if($program) {
            $class = "";
            if($insideInfobox) $class = "infobox bg-$backgroundcolor";
           $content .= "<div class=\"$class\" id=\"lthsolr_statistics_container\"><h3 class=\"h3 mt-0 mb-3\">Antagningsstatistik</h3></div>";
        } else {
            $content .= '<div class="table-responsive-sm">';
            $content .= '<table id="lthsolr_statistics_container" class="table table-striped">
                <thead class=" thead-dark">
                    <tr>
                        <th scope="col" class="">Benämning</th>
                        <th scope="col" class="">Totalt antal<br />sökande</th>
                        <th scope="col" class="">1:a hands-<br />sökande</th>
                        <th scope="col" class="">Antagna</th>
                        <th scope="col" class="" title="Gymnasiebetyg">BI</th>
                        <th scope="col" class="" title="Gymnasiebetyg med komplettering">BII</th>
                        <th scope="col" class="" title="Högskoleprov">HP</th>
                        <th scope="col" class="" title="Folkhögskola">BF</th>
                    </tr>
                </thead>
                <tbody class="">
                </tbody>
            </table>';
            $content .= '</div>';
        }

        //$content .= file_get_contents("/var/www/html/typo3/typo3conf/ext/lth_solr/templates/statistics_list.html");

        $content .= '<input type="hidden" id="lth_solr_program" value="' . $program . '" />
                <input type="hidden" id="lth_solr_round" value="' . $round . '" />
            <input type="hidden" id="lth_solr_action" value="listStatistics" />';
        
        return $content;
    }
    
    
    public function listCourses($syslang,$round)
    {
        $courseHeader = 'Fristående kurser';
        if($syslang==='en') {
            $courseHeader = 'Courses';
        }
        $content = '';
       
        //$content .= '<div class="container">';
        //$content .= '<div class="row"><div class="col"><h1 class=" my-0 pb-2 border-bottom">'.$courseHeader.'</h1></div></div>';
        $content .= '<div id="lthsolr_course_container" class=""></div>';
       // $content .= '</div>';

        $content .= file_get_contents("/var/www/html/typo3/typo3conf/ext/lth_solr/templates/course_list.html");

        $content .= '<input type="hidden" id="lth_solr_round" value="' . $round . '" />
            <input type="hidden" id="lth_solr_action" value="listCourses" />';
        
        return $content;
    }
    
    
    public function showCourse($courseCode, $round, $syslang)
    {
        $content = '';
        //$content .= '<div class="container">';
        $content .= '<div id="lthsolr_course_container" class=""></div>';
        //$content .= '</div>';

        //$content .= file_get_contents("/var/www/html/typo3/typo3conf/ext/lth_solr/templates/job_presentation.html");

        $content .= '
            <input type="hidden" id="lth_solr_scope" value="' . $courseCode . '" />
            <input type="hidden" id="lth_solr_round" value="' . $round . '" />
            <input type="hidden" id="lth_solr_action" value="showCourse" />';
        
        return $content;
    }
    
    
    public function listJobs($syslang)
    {
        $jobHeader = 'Lediga anställningar';
        if($syslang==='en') {
            $jobHeader = 'Vacant Positions';
        }
        $content = '';
       
        //$content .= '<div class="container">';
        $content .= '<div class="row"><div class="col"><h1 class=" my-0 pb-2 border-bottom">'.$jobHeader.'</h1></div></div>';
        $content .= '<div id="lthsolr_job_container" class="row"><div class="col"></div></div>';
       // $content .= '</div>';

        $content .= file_get_contents("/var/www/html/typo3/typo3conf/ext/lth_solr/templates/jobs_list.html");

        $content .= '
            <input type="hidden" id="lth_solr_action" value="listJobs" />';
        
        return $content;
    }
    
    
    public function showJob($refNr, $syslang)
    {
        $content = '';
        //$content .= '<div class="container">';
        $content .= '<div class="row"><div class="col"><h1 class="my-0 pb-2 border-bottom"></h1></div></div>';
        $content .= '<div id="lthsolr_job_container" class="row">';
        $content .= '<div class="col"><table class="table table-condensed quick-info"><tbody></tbody></table></div>';
        $content .= '</div>';
        //$content .= '</div>';

        //$content .= file_get_contents("/var/www/html/typo3/typo3conf/ext/lth_solr/templates/job_presentation.html");

        $content .= '
            <input type="hidden" id="lth_solr_scope" value="' . $refNr . '" />
            <input type="hidden" id="lth_solr_action" value="showJob" />';
        
        return $content;
    }
    
    
    public function searchResult($query, $webSearchScope, $linkStaffDetailPage)
    {
        $content .= '<div id="lthsolr_search_container">';
        
            //people
            $content .= '<div style="overflow:hidden;clear:both;display:none;" class="table-responsive">';
            $content .= '<div id="lthsolr_people_header"></div>';
            $content .= '<div id="lthsolr_staff_container" class=""></div>';
            $content .= '</div>';
            $content .= file_get_contents("/var/www/html/typo3/typo3conf/ext/lth_solr/templates/contact.html");

            //pages
            $content .= '<div style="clear:both;display:none;" class="table-responsive">';
            $content .= '<div id="lthsolr_pages_header"></div>';
            $content .= '<ul id="lthsolr_pages_container" class="table"></ul>';
            $content .= '</div>';
            $content .= file_get_contents("/var/www/html/typo3/typo3conf/ext/lth_solr/templates/pages_search.html");

            //courses
            /*$content .= '<div style="display:none;" class="table-responsive">';
            $content .= '<div id="lthsolr_courses_header"></div>';
            $content .= '<table id="lthsolr_courses_container" class="table"><tbody></tbody></table>';
            $content .= '</div>';
            $content .= file_get_contents("/var/www/html/typo3/typo3conf/ext/lth_solr/templates/courses_search.html");*/
            
            //hidden fields
            $content .= '<input type="hidden" id="lth_solr_query" value="' . $query . '" />';
            $content .= '<input type="hidden" id="lth_solr_action" value="searchLong" />';
            $content .= '<input type="hidden" id="no_cache" name="no_cache" value="1" />';
            $content .= '<input type="hidden" id="webSearchScope" value="' . $webSearchScope . '" />';
            $content .= '<input type="hidden" id="linkStaffDetailPage" value="' . $linkStaffDetailPage . '" />';  
        
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
    
    public function showProject($uuid)
    {
        $lth_solr_uuid['projects'][] = $uuid;
        if(count($lth_solr_uuid > 0)) {
            $scope = urlencode(json_encode($lth_solr_uuid));
        }
        
        //Buttons
        $content = '<div class="accordion" id="lthSolrAccordion">';

        //Project
        $content .= '<div id="lthsolr_projects_container"><div id="lthsolr_projects_header"></div></div>';
        $content .= file_get_contents("/var/www/html/typo3/typo3conf/ext/lth_solr/templates/project_presentation.html");
        
        //Publications
        $content .= '<div class="card">
            <div class="card-header" id="headingPublications"><h5 class="mb-0">
        <button class="btn btn-link" type="button" data-toggle="collapse" data-target="#collapsePublications" aria-expanded="true" aria-controls="collapsePublications">
          Publications
        </button>
      </h5>
    </div>
    <div id="collapsePublications" class="collapse" aria-labelledby="headingOne" data-parent="#lthSolrAccordion">
      <div class="card-body">';
        $content .= $this->listPublications($scope, '', '', '', '', '', '', 'showProject','fullList','','','');
        $content .= '</div></div></div>';
        
        $content .= '</div>';
        $content .= file_get_contents("/var/www/html/typo3/typo3conf/ext/lth_solr/templates/publication_list.html");
        
        //mapModal
        $content .= '<!-- projectModal -->
            <div id="projectModal" class="modal fade" role="dialog">
              <div class="modal-dialog">

                <!-- Modal content-->
                <div class="modal-content">
                  <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h1 class="modal-title">???</h1>
                  </div>
                  <div class="modal-body" style="position:relative;font-size:14px;font-weight:bold;">
                   </div>
                  <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                  </div>
                </div>
              </div>
            </div>';

        $content .= '<input type="hidden" id="lth_solr_scope" value="' . $scope . '" />
                <input type="hidden" id="lth_solr_action" value="showProject" />';
        
        return $content;
    }
    
    
    public function showStaff($uuid, $html_template, $noItemsToShow)
    {      
        $lth_solr_uuid['fe_users'][] = $uuid;
        if(count($lth_solr_uuid > 0)) {
            $scope = urlencode(json_encode($lth_solr_uuid));
        }
        
        //Staff 
        $content .= '<div id="lthsolr_show_staff_container" style="min-height:350px;">';
        //Map
        $content .= '<div id="lthsolr_map" style="cursor:pointer;">'
                . '<div style="position:relative;">'
                . '<img src="typo3conf/ext/lth_solr/res/lthmap.gif" style="width:292px;height:263px;" />'
                . '<img id="lthsolr_pin" style="width:15%;height:15%;position:absolute;top:0px;right:0px;z-index:1000;" src="typo3conf/ext/lth_solr/res/pin.png" />'
                //. '<img id="lthsolr_pinClient" style="width:15%;height:15%;position:absolute;top:0px;right:0px;z-index:1000;display:none;" src="typo3conf/ext/lth_solr/res/pin.png" />'
                . '</div>'
                . '</div>';
        $content .= '</div>';
        $content .= file_get_contents("/var/www/html/typo3/typo3conf/ext/lth_solr/templates/contact_large.html");

        //Publications
        //$content .= '<div id="lthsolr_publications_container"><div id="lthsolr_publications_header"></div></div>';
        $content .= $this->listPublications($scope, '', '', '', '', '', '', 'showStaff','fullList','','','');
        $content .= file_get_contents("/var/www/html/typo3/typo3conf/ext/lth_solr/templates/publication_list.html");

        //Projects
        //$content .= '<div id="lthsolr_projects_container"><div id="lthsolr_projects_header"></div></div>';
        //$content .= file_get_contents("/var/www/html/typo3/typo3conf/ext/lth_solr/templates/project_simple.html");        
 
        

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
            <input type="hidden" id="lth_solr_action" value="showStaff" />
            <input type="hidden" id="lth_solr_staff_pos" value="' . $showStaffPos . '" />
            <input type="hidden" id="lth_solr_no_items" value="' . $noItemsToShow . '" />';

        return $content;
    }
    
    
    public function listStaff($scope, $html_template, $noItemsToShow, $categories, $limitToStandardCategories, $heritage, $showPictures, $thisGroupOnly, $primaryRoleOnly)
    {
        $syslang = $GLOBALS['TSFE']->config['config']['language'];
        if($syslang==='en') {
            $filterText = 'Categories';
            $placeholderText = 'Free text search';
        } else {
            $filterText = 'Kategorier';
            $placeholderText = 'Fritextsökning';
        }
        $clientIp = $_SERVER['REMOTE_ADDR'];

        /*$content .= '<p class="lth_solr_filter_container">';
            $faSearchClass = '';
            if($categories !== 'no_categories' && !$limitToStandardCategories) {
                $content .= '<i class="fa fa-filter fa-lg slsGray50"></i><a class="slsPadL5 refine">Filter</a>';
                $faSearchClass = 'fa-search-pos ';
            }
            $content .= '<i class="fa fa-search ' . $faSearchClass . 'fa-lg slsGray50"></i>';
            $content .= '<input style="border:0px;box-shadow:none;" type="text" id="lthsolr_staff_filter" class="lthsolr_filter" placeholder="'.$placeholderText.'" name="lthsolr_filter" value="" />';

        $content .= '</p>';
        
        $content .= '<div id="lth_solr_facet_container"></div>';
        
        $content .= '<p style="" id="lthsolr_staff_header"></p>';
        
        
            $content .= '<div id="lthsolr_staff_container">';

        $content .= '</div>'; */
        
        $content .= '<div style="clear:both;width:100%;height:20px;margin:15px 0px 15px 0px;">'
                    . '<div style="" id="lthsolr_staff_header"></div>'
                    . '</div>';

            $content .= '<div style="width:100%;clear:both;">';
                //$content .= '<div id="lth_solr_facet_container"></div>';
                $content .= '<div id="lthsolr_staff_container"></div><div style="clear:both;width:100%;"></div>';
            $content .= '</div>';

        $content .= file_get_contents("/var/www/html/typo3/typo3conf/ext/lth_solr/templates/contact.html");

        $content .= '
            <input type="hidden" id="lth_solr_scope" value="' . $scope . '" />
            <input type="hidden" id="lth_solr_heritage" value="' . $heritage . '" />
            <input type="hidden" id="lth_solr_action" value="listStaff" />
            <input type="hidden" id="lth_solr_categories" value="' . $categories . '" />
            <input type="hidden" id="lth_solr_no_items" value="' . $noItemsToShow . '" />
            <input type="hidden" id="lth_solr_limitToStandardCategories" value="' . $limitToStandardCategories . '" />
            <input type="hidden" id="lth_solr_showPictures" value="' . $showPictures . '" />
            <input type="hidden" id="lth_solr_thisGroupOnly" value="' . $thisGroupOnly . '" />
            <input type="hidden" id="lth_solr_primaryRoleOnly" value="' . $primaryRoleOnly . '" />';
    
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

        return $content;
    }
    
    
    public function listPublications($scope, $noItemsToShow, $categories, $keyword, $pageTitle, 
            $publicationCategories, $publicationCategoriesSwitch, $display, $displayLayout, $displayFromSimpleList, $backgroundcolor, $header)
    {   
        $syslang = $GLOBALS['TSFE']->config['config']['language'];
        if($syslang==='en') {
            $filterText = 'Categories';
            $placeholderText = 'Free text search';
        } else {
            $filterText = 'Kategorier';
            $placeholderText = 'Fritextsökning';
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
        
        
        if($display === 'publications' || $display==='comingdissertations' || $display === 'showProject' || $display === 'showStaff') {

            if($displayLayout==='fullList') {
                $content .= '<div style="clear:both;width:100%;height:20px;margin:15px 0px 15px 0px;border-top:3px #000 solid;">'
                        . '<div style="float:left;" id="lthsolr_publications_header"></div>'
                        . '<div style="float:right;" id="lthsolr_publications_sort"></div>'
                        . '</div>';

                $content .= '<div id="lthsolr_publications_container"></div>';

                $content .= file_get_contents("/var/www/html/typo3/typo3conf/ext/lth_solr/templates/publication_list.html");
            } else if($displayLayout==='simpleList') {
                $backgroundcolorClass = "";
                if($backgroundcolor) $backgroundcolorClass = "bg-" . substr($backgroundcolor,0,-4);
                $content .= "<div style=\"padding:15px;height:100%;\" class=\"$backgroundcolorClass\">";
                if($header) $content .= "<h3>$header</h3>";
                $content .= "<div id=\"lthsolr_publications_container\"></div></div>";
                $content .= file_get_contents("/var/www/html/typo3/typo3conf/ext/lth_solr/templates/publication_simple_list.html");
            }
        }

        //if($scope) {
            if($display==='publications') {
                $content .= '<input type="hidden" id="lth_solr_action" value="listPublications" />';
            } else if($display==='comingdissertations') {
                $content .= '<input type="hidden" id="lth_solr_action" value="listComingDissertations" />';
            }
            $content .= '
                <input type="hidden" id="lth_solr_scope" value="' . $scope . '" />
                <input type="hidden" id="lth_solr_keyword" value="' . $keyword . '" />    
                <input type="hidden" id="lth_solr_no_items" value="' . $noItemsToShow . '" />
                <input type="hidden" id="lth_solr_pagetitle" value="' . $pageTitle . '" />
                <input type="hidden" id="lth_solr_publicationCategories" value="' . $publicationCategories . '" />
                <input type="hidden" id="lth_solr_publicationCategoriesSwitch" value="' . $publicationCategoriesSwitch . '" />
                <input type="hidden" id="lth_solr_displayFromSimpleList" value="' . $displayFromSimpleList . '" />'; 
        //}
        $content .= '<input type="hidden" id="lth_solr_display" value="' . $display . '" />';
        $content .= '<input type="hidden" id="lth_solr_displayLayout" value="' . $displayLayout . '" />';

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
        /*if($display==='list') {
            $content = '<div style="font-size:17px;">'.$content.'</div>';
        }*/
        return $content;
    }
    
    
    public function listTagCloud($scope, $noItemsToShow, $categories)
    {
        $content = '<div id="lthsolr_tagcloud_container"></div>';

        $content .= '
            <input type="hidden" id="lth_solr_scope" value="' . $scope . '" />
            <input type="hidden" id="lth_solr_no_items" value="' . $noItemsToShow . '" />
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
    
    
    public function showStudentPaper($uuid, $staffDetailPage, $projectDetailPage)
    {
        $content = '<div id="lth_solr_container" ></div>';

        $content .= file_get_contents("/var/www/html/typo3/typo3conf/ext/lth_solr/templates/studentpaper_presentation.html");

        $content = str_replace('###more###', 'Mer', $content);

        $content .= '
            <input type="hidden" id="lth_solr_uuid" value="' . $uuid . '" />
            <input type="hidden" id="lth_solr_action" value="showStudentPaper" />';

        return $content;
    }
        
        
    public function listStudentPapers($scope, $detailPage, $noItemsToShow, $categories, $papertype)
    {
        /*$content .= '<style>.glyphicon-search {font-size: 25px;}.glyphicon-filter {font-size: 15px;}</style>';
        $content .= '<div class="lth_solr_filter_container">';

        $content .= '<div style="clear:both;height:50px;">';
        if($categories != "no_categories") {
            $content .= '<div id="refine" style="float:left;width:30%;background-color:#353838;color:#ffffff;height:50px;padding:17px;font-size:16px;"><span class="glyphicon glyphicon-filter"></span><span class="refine">Filter</span></div>';
        }    
        $content .= '<div style="float:left;padding:15px 0px 0px 15px;width:10%"><span class="glyphicon glyphicon-search"></span></div>';
        $content .= '<div style="float:left;padding-top:10px;width:60%">';
        $content .= '<input style="border:0px;background-color:#fafafa;width:100%;box-shadow:none;" type="text" id="lthsolr_studentpapers_filter" class="lthsolr_filter" name="lthsolr_filter" value="" />';
        $content .= '</div>';

        $content .= '</div>';

        $content .= '</div>';    

        $content .= '<div style="clear:both;">';

        $content .= '<div id="lth_solr_facet_container"></div>';

        $content .= '<div id="lthsolr_publications_container"><div style="clear:both;height:20px;" id="lthsolr_publications_header"></div></div>';

        $content .= '</div>'; */
        
        $clientIp = $_SERVER['REMOTE_ADDR'];
        
        $content .= '<div style="clear:both;width:100%;height:30px;margin:15px 0px 15px 0px;"><div style="width:50%;float:left;" id="lthsolr_publications_header"></div><div style="float:right;" id="lthsolr_publications_sort"></div></div>';

        $content .= '<div style="width:100%;clear:both;">';
            $content .= '<div id="lthsolr_publications_container"></div>';
        $content .= '</div>';

        $content .= file_get_contents("/var/www/html/typo3/typo3conf/ext/lth_solr/templates/studentpaper_simple.html");

        $content .= '
                <input type="hidden" id="lth_solr_scope" value="' . $scope . '" />
                <input type="hidden" id="lth_solr_detailpage" value="' . $detailPage . '" />
                <input type="hidden" id="lth_solr_action" value="listStudentPapers" />
                <input type="hidden" id="lth_solr_categories" value="' . $categories . '" />
                <input type="hidden" id="lth_solr_papertype" value="' . $papertype . '" /> 
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
    
    
    public function compare($round, $scope, $wrapper)
    {
        $content = '<style>#lthsolr_compare_container .list-group-item {padding-left:5px;}'
                . '.lth_solr_next_course, .lth_solr_prev_course {cursor:pointer;}'
                . '@media (min-width: 776px){.modal-dialog {max-width: 700px;}}'
                . 'a.disabled { opacity: 0.5; pointer-events: none; cursor: default;}</style>';
        
        $content .= '<div style="margin-left:15px;"><span class="fa fa-info-circle"></span> Denna kurslistning är bara till för att ge en överblick över programmens kurser. För LTHs officiella information om kurser se: https://kurser.lth.se</div>';
        
        $content .= '<div id="lthsolr_compare_container" class=""></div>';

        //$content .= file_get_contents("/var/www/html/typo3/typo3conf/ext/lth_solr/templates/compare.html");

        $content .= '
            <input type="hidden" id="lth_solr_round" value="' . $round . '" />
            <input type="hidden" id="lth_solr_scope" value="' . $scope . '" />
            <input type="hidden" id="lth_solr_action" value="listCompare" />';
        
        if($wrapper) {
            $wrapperArray = explode('split', $wrapper);
            $content = $wrapperArray[0] . $content . $wrapperArray[1];
        }
        
        $content .= '<!-- compareModal -->
                    <div id="compareModal" class="modal" tabindex="-1" role="dialog" style="">
                        <div class="modal-dialog" role="document">
                          <div class="modal-content">
                            <div class="modal-header" style="">
                              <h2 class="modal-title" style="font-size:24px;">Modal title</h2>
                              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                              </button>
                            </div>
                            <div style="margin-left:15px;"><span class="fa fa-info-circle"></span> Denna kurslistning är bara till för att ge en överblick över programmens kurser. För LTHs officiella information om kurser se: https://kurser.lth.se</div>
                            <div style="margin:10px 10px 0px 10px;width:95%;clear:both;"><a class="lth_solr_prev_course" style="float:left;"><span class="fa fa-chevron-left"></span> Föregående kurs</a><a class="lth_solr_next_course" style="float:right;">Nästa kurs <span class="fa fa-chevron-right"></span></a></div>
                            <div class="modal-body">
                              <p>Modal body text goes here.</p>
                            </div>
                            <div style="margin:10px 10px 0px 10px;width:95%;clear:both;"><a class="lth_solr_prev_course" style="float:left;"><span class="fa fa-chevron-left"></span> Föregående kurs</a><a class="lth_solr_next_course" style="float:right;">Nästa kurs <span class="fa fa-chevron-right"></span></a></div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                            </div>
                          </div>
                        </div>
            </div>';

        return $content;
    }
    
    
    public function rssFeeder($scope, $noItemsToShow, $categories, $keyword, $pageTitle, $publicationCategories, $publicationCategoriesSwitch)
    {
        /*
         * header("Content-Type: application/rss+xml; charset=ISO-8859-1");
 
    DEFINE ('DB_USER', 'my_username');   
    DEFINE ('DB_PASSWORD', 'my_password');   
    DEFINE ('DB_HOST', 'localhost');   
    DEFINE ('DB_NAME', 'my_database'); 
 
    $rssfeed = '<?xml version="1.0" encoding="ISO-8859-1"?>';
    $rssfeed .= '<rss version="2.0">';
    $rssfeed .= '<channel>';
    $rssfeed .= '<title>My RSS feed</title>';
    $rssfeed .= '<link>http://www.mywebsite.com</link>';
    $rssfeed .= '<description>This is an example RSS feed</description>';
    $rssfeed .= '<language>en-us</language>';
    $rssfeed .= '<copyright>Copyright (C) 2009 mywebsite.com</copyright>';
 
    $connection = @mysql_connect(DB_HOST, DB_USER, DB_PASSWORD)
        or die('Could not connect to database');
    mysql_select_db(DB_NAME)
        or die ('Could not select database');
 
    $query = "SELECT * FROM mytable ORDER BY date DESC";
    $result = mysql_query($query) or die ("Could not execute query");
 
    while($row = mysql_fetch_array($result)) {
        extract($row);
 
        $rssfeed .= '<item>';
        $rssfeed .= '<title>' . $title . '</title>';
        $rssfeed .= '<description>' . $description . '</description>';
        $rssfeed .= '<link>' . $link . '</link>';
        $rssfeed .= '<pubDate>' . date("D, d M Y H:i:s O", strtotime($date)) . '</pubDate>';
        $rssfeed .= '</item>';
    }
 
    $rssfeed .= '</channel>';
    $rssfeed .= '</rss>';
 
    echo $rssfeed;
         */
    }
}
