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
    public function showStaff($uuid, $html_template, $noItemsToShow, $selection, $publicationDetailPage)
    {      
        $lth_solr_uuid['fe_users'][] = $uuid;
        if(count($lth_solr_uuid > 0)) {
            $scope = urlencode(json_encode($lth_solr_uuid));
        }
                
        $content .= '<div style="max-width:500px;min-height:500px;">';
            //Staff 
            $content .= '<div id="lthsolr_staff_container"></div>';
            $content .= file_get_contents("/var/www/html/typo3/typo3conf/ext/lth_solr/templates/contact_large.html");

            //Publications
            $content .= '<div style="clear:both;" id="lthsolr_publications_container"><div id="lthsolr_publications_header"></div></div>';
            $content .= file_get_contents("/var/www/html/typo3/typo3conf/ext/lth_solr/templates/publication_simple.html");

            //Projects
            $content .= '<div id="lthsolr_projects_container"><div id="lthsolr_projects_header"></div></div>';
            $content .= file_get_contents("/var/www/html/typo3/typo3conf/ext/lth_solr/templates/project_simple.html");        
 
        $content .= '</div>';
            
                    //Map
        $content .= '<div id="lthsolr_map" style="">'
                . '<div style="position:relative;">'
                . '<img style="display;none;"style="" src="typo3conf/ext/lth_solr/res/lthmap.gif" />'
                . '<img id="lthsolr_pin" style="width:15%;height:15%;position:absolute;top:0px;right:0px;z-index:1000;display:none;" src="typo3conf/ext/lth_solr/res/pin.png" />'
                //. '<img id="lthsolr_pinPf" style="position:absolute;top:0px;right:0px;z-index:1000;display;none;" src="typo3conf/ext/lth_solr/res/pin.gif" />'
                . '</div>'
                . '</div>';
            //onclick="$(\'#myModal\').modal(\'toggle\');"
        /*Modal
            $content .= '<!-- Modal -->
                <div id="myModal" class="modal fade" role="dialog">
                  <div class="modal-dialog">

                    <!-- Modal content-->
                    <div class="modal-content">
                      <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">Modal Header</h4>
                      </div>
                      <div class="modal-body">
                        <img src="typo3conf/ext/lth_solr/res/lthmap_large.png" />
                      </div>
                      <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                      </div>
                    </div>

                  </div>
                </div>';*/

        //hidden fields
        $content .= '
            <input type="hidden" id="lth_solr_publicationdetailpage" value="' . $publicationDetailPage . '" />
            <input type="hidden" id="lth_solr_projectdetailpage" value="' . $projectDetailUrl . '" />
            <input type="hidden" id="lth_solr_scope" value="' . $scope . '" />
            <input type="hidden" id="lth_solr_detail_action" value="showStaff" />
            <input type="hidden" id="lth_solr_staff_pos" value="' . $showStaffPos . '" />
            <input type="hidden" id="lth_solr_no_items" value="' . $noItemsToShow . '" />';

        return $content;
    }
    
    
    public function listStaff($scope, $html_template, $noItemsToShow, $selection, $categories, $staffHomepagePath)
        {
            $clientIp = $_SERVER['REMOTE_ADDR'];
            
            $content .= '<style>.glyphicon-search {font-size: 25px;}.glyphicon-filter {font-size: 15px;}.glyphicon-export{cursor:pointer;}</style>';
            $content .= '<div class="lth_solr_filter_container">';
            
                //$content .= '<div style="font-weight:bold;">' . $this->pi_getLL("filter") . '</div>';
              
            $content .= '<div style="clear:both;height:50px;">';
            if($categories != "no_categories") {
                $content .= '<div id="refine" style="float:left;width:30%;background-color:#353838;color:#ffffff;height:50px;padding:17px;font-size:100%;">'
                        . '<span class="glyphicon glyphicon-filter"></span><span class="refine">Staff categories</span>'
                        . '</div>';
            }    
            $content .= '<div style="float:left;padding:15px 0px 0px 15px;width:10%"><span class="glyphicon glyphicon-search"></span></div>';
            $content .= '<div style="float:left;padding-top:10px;width:50%">';
            $content .= '<input style="border:0px;background-color:#fafafa;width:100%;box-shadow:none;" type="text" id="lthsolr_staff_filter" class="lthsolr_filter" placeholder="Free text" name="lthsolr_filter" value="" />';
            $content .= '</div>';
            
            $content .= '</div>';
                
            $content .= '</div>';    
            
            $content .= '<div style="clear:both;">';
            
            $content .= '<div id="lth_solr_facet_container"></div>';
            
            $content .= '<div id="lthsolr_staff_container"><div style="clear:both;height:40px;width:250px;" id="lthsolr_staff_header"></div></div>';
            
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
        
}
