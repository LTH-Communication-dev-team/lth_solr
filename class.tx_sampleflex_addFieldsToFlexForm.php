<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class user_sampleflex_addFieldsToFlexForm {
    
    
    function getSolrData ($config) 
    {
	$confArr = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['lth_solr']);
    
	if (!$confArr['solrServer']) {
	    return 'Ange Solr-server';
	}

	if (!$confArr['solrPort']) {
	    return 'Ange Solr-port';
	}

	if (!$confArr['solrPath']) {
	    return 'Ange Solr-path';
	}
    
        $addpeopleArray = array();
        $addpeople = '';

        $pi_flexform = $config['row']['pi_flexform'];
        $pid = $config['row']['pid'];
        $sys_language_uid = $config['row']['sys_language_uid'];
        $catVar = 'lth_solr_cat_' . $pid . '_' . $sys_language_uid . '_ss';
        $hideVar = 'lth_solr_hide_' . $pid . '_' . $sys_language_uid . '_i';

        $xml = simplexml_load_string($pi_flexform);
        $test = $xml->data->sheet[0]->language;
        foreach ($test->field as $n) {
            foreach($n->attributes() as $name => $val) {
		if ($val == 'customcategories') {
                    $customcategories = $n->value;
                } else if($val == 'scope') {
                    $scope = $n->value;
                } else if($val == 'addpeople') {
                    $addpeople = $n->value;
                } else if($val == 'removepeople') {
                    $removepeople = $n->value;
                }
            }
        }

	if(trim($scope) != '') {
	    $scope = str_replace(' ', '', $scope);
	    $scope = str_replace(',', "\n", $scope);
	    $scopeArray = explode("\n", $scope);
	} else if(trim($addpeople)) {
	    //
	} else {
	    return 'You have to save a selection of departments/people!';
	}
	
        /*if($customcategories) {
            $customcategoriesArray = explode("\n", $customcategories);
        } else {
            return 'You have to save custom categories!';
        }*/
        
        $queryFilterString = '';
        $offset=null;
        $limit=null;
        $okString = '';
        if($offset=='null' || $offset=='') $offset=0;
        if($limit=='null' || $limit=='') $limit=700;

	if($scopeArray) {
	    $i = 0;
	    foreach($scopeArray as $key => $value) {
		if($queries or i==0) {
		    $queries = "usergroup_txt:$value";
		} else {
		    $queries .= " $value";
		}
		$i++;
	    }
	}
        
        if(trim($addpeople)) {
            $addpeople = str_replace(' ', '', $addpeople);
            $addpeople = str_replace(',', "\n", $addpeople);
            $addpeople = str_replace(':', '', $addpeople);
            $addpeopleArray = explode("\n",$addpeople);
            foreach($addpeopleArray as $value) {
                $queries .= " id:$value";
            }
        }
	
	if(trim($removepeople)) {
	    $removepeople = str_replace(' ', '', $removepeople);
	    $removepeople = str_replace(',', "\n", $removepeople);
	    $removepeople = str_replace(':', '', $removepeople);
	    $removepeopleArray = explode("\n",$removepeople);
	    foreach($removepeopleArray as $value) {
		$queries .= " !id:$value";
	    }
	}
        
        require(__DIR__.'/pi2/init.php');

        $client = new Solarium\Client($config);
        
        $query = $client->createSelect();
        
        $query->setQuery($queries);
        
        $query->setFields(array('id', 'display_name_t', $catVar, $hideVar));

        $query->addSort('lth_solr_sort_' . $pid . '_' . $sys_language_uid . '_i', $query::SORT_ASC);
        $query->addSort('last_name_t', $query::SORT_ASC);
        $query->addSort('first_name_t', $query::SORT_ASC);
        
        $query->setStart($offset)->setRows($limit);

        // this executes the query and returns the result
        $resultset = $client->select($query);
        
        //print_r($resultset);
        /* display the total number of documents found by solr
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
        return $content;*/

	return array($resultset, $customcategories, $pluginId);
    }
    
    function addCustomCategories ($config)
    {
        return '';
	//print_r($config);
        $pid = $config['row']['pid'];
	
	$allResponse = $this->getSolrData($config);
	$response = $allResponse[0];
	$customcategoriesArray = $allResponse[1];
	$pluginId = $allResponse[2];
        
        if(!is_array($allResponse[0])) {
            return $allResponse[0];
        }
	
	$content = "<script language=\"javascript\">
	    
	var myVar=setInterval(function () {myTimer()}, 1000);
	function myTimer() {
	    Ext.each(Ext.query('input.not_saved'), function (el) {
	    //console.log(el.getValue());
		if(el.getValue()!='' && Ext.get('temp_'+el.id).getValue() != el.getValue()) {
		    strSave = el.getValue();
		    userName = Ext.get('user_'+el.id).getValue();
		    Ext.get(el).removeClass('not_saved');
		    updateIndex('updateImage',strSave,'',userName);
		    Ext.get('temp_'+el.id).set({'value': strSave});

		    Ext.get(el).addClass('saved');
		}
	    }, this);
	}
	
	function addTextarea(tdId,editId,username,pluginId)
	{
	    var strTextarea = '<textarea id=\"'+tdId+'_textarea\">'+Ext.get('temp_'+tdId).getValue()+ '</textarea>';
	    strEdit = '<a href=\"#\" onclick=\"removeTextarea(\''+tdId+'\',\''+editId+'\',\''+username+'\','+pluginId+');return false;\">Cancel</a> ';
	    strEdit += '<a href=\"#\" onclick=\"saveTextarea(\''+tdId+'\',\''+editId+'\',\''+username+'\','+pluginId+');return false;\">Save</a>';
	    document.getElementById(tdId).innerHTML = strTextarea;
	    document.getElementById(editId).innerHTML = strEdit;
	}
	
	function removeTextarea(tdId,editId,username,pluginId)
	{
	    var strText = Ext.get('temp_'+tdId).getValue();
	    var strEdit = '<a href=\"#\" onclick=\"addTextarea(\''+tdId+'\',\''+editId+'\',\''+username+'\','+pluginId+');return false;\">Edit</a>';
	    document.getElementById(tdId).innerHTML = strText;
	    document.getElementById(editId).innerHTML = strEdit;
	}
	
	function saveTextarea(tdId,editId,username,pluginId)
	{
	    strSave = Ext.get(tdId+'_textarea').getValue();
	    updateIndex('updateText',strSave,'',username);
	    Ext.get('temp_'+tdId).set({'value': strSave});
	    removeTextarea(tdId,editId,username,pluginId);
	}
        </script>";
	
	$content .= "<table style=\"padding:10px;width:700px;\" cellspacing=\"5\"><tbody>";

	$tmpCatArray = array();
	
        $numberOfHits = $response->response->numFound;
        $content .= "<tr><td colspan=\"" . (count($customcategoriesArray) + 1) . "\">$numberOfHits</td></tr>";
        if ( $response->getHttpStatus() == 200 ) { 
            if ( $response->response->numFound > 0 ) {
                $i=0;
                
                //print_r($response);
                
                foreach ( $response->response->docs as $doc) {
                    $content .= "<tr><td>$doc->name ($doc->id)</td>";
		    $GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_devlog', array('msg' => $doc->{"staff_custom_category_facet_".$sys_language."_$pluginId"."_ss"}, 'crdate' => time()));
		    //Remove old categories
		    if(is_array($doc->{"staff_custom_category_facet_".$sys_language."_$pluginId"."_ss"})) {
			foreach($doc->{"staff_custom_category_facet_".$sys_language."_$pluginId"."_ss"} as $key9 => $value9) {
			    $value9Array = explode('_', $value9);
			    if(!in_array($value9Array[1], $customcategoriesArray)) {
				$this->deleteCategory($doc->id, "staff_custom_category_facet_".$sys_language."_$pluginId"."_ss", $value9);
			    }
			}
		    } else {
			$value9Array = explode('_', $doc->{"staff_custom_category_facet_".$sys_language."_$pluginId"."_ss"});
			if(!in_array($value9Array[1], $customcategoriesArray)) {
				$this->deleteCategory($doc->id, "staff_custom_category_facet_".$sys_language."_$pluginId"."_ss", $doc->{"staff_custom_category_facet_".$sys_language."_$pluginId"."_ss"});
			    }
		    }
		    
                    $ii=0;
                    foreach($customcategoriesArray as $key => $value) {
                        $okString = '';
			if(is_array($doc->{"staff_custom_category_facet_".$sys_language."_$pluginId"."_ss"})) {
			    if(in_array($ii.'_'.$value, str_replace('+',' ',$doc->{"staff_custom_category_facet_".$sys_language."_$pluginId"."_ss"}))) {
				$okString = ' checked="checked"';
			    }
			} else {
			    if($ii.'_'.$value == str_replace('+',' ',$doc->{"staff_custom_category_facet_".$sys_language."_$pluginId"."_ss"})) {
				$okString = ' checked="checked"';
			    }
			}
                        $content .= "<td><input type=\"checkbox\" value=\"$ii"."_".$value."\" onclick=\"updateIndex('updateIndex',this.value,this.checked,'$doc->id','$sys_language');\"$okString />$value</td>";
                        $ii++;
                    }
                    if(!$okString) $okString = ' checked="checked"';
                    $content .= "</tr>";
                    $i++;
                }
            }
        }
        else {
            $content = '<tr><td>' . $response->getHttpStatusMessage() . '</td></tr>';
        }
    
        $content .= "</tbody></table>";
        return $content;
    }
    
    function addCustomImages($config)
    {
        return '';
	$tt_contentUid = $config['row']['uid'];
	$content = "<table style=\"padding:10px;width:700px;\" cellspacing=\"5\"><tbody>";
	
	$allResponse = $this->getSolrData($config);
	$response = $allResponse[0];
	$pluginId = $allResponse[2];
        
        if(!is_array($allResponse[0])) {
            return $allResponse[0];
        }

        $numberOfHits = $response->response->numFound;
        $content .= "<tr><td colspan=\"" . (count($customcategoriesArray) + 1) . "\">$numberOfHits</td></tr>";
        if ( $response->getHttpStatus() == 200 ) { 
            if ( $response->response->numFound > 0 ) {
                $i=0;
                
                //print_r($response);
                
                foreach ( $response->response->docs as $doc) {
		    $image = '';
		    if($doc->{'staff_custom_image_'.$pluginId.'_s'}) {
			$imageArray = explode('/',$doc->{'staff_custom_image_'.$pluginId.'_s'});
			$image = end($imageArray);
		    }
		    
                    $content .= "<tr><td>$doc->name  ($doc->id)</td>";
		    $content .= "<td>";
		    $content .= "<input id=\"user_image_$i\" type=\"hidden\" value=\"" . $doc->id . "\">";
		    $content .= "<input id=\"temp_image_$i\" type=\"hidden\" value=\"$image\"/>";
		    $content .= "<input id=\"image_$i\" class=\"not_saved\" type=\"hidden\" name=\"data[tt_content][$tt_contentUid][pi_flexform][data][addCustomImages][lDEF][image_$i][vDEF]\" value=\"$image\" />";
                    $content .= "<select class=\"addCustomImages\" name=\"data[tt_content][$tt_contentUid][pi_flexform][data][addCustomImages][lDEF][image_$i][vDEF]_list\"><option value=\"$image\">$image</option></select>";
                    //$content .= "<a href=\"#\" onclick=\"updateIndex('updateImage','','','$doc->id','');\">$image";
		    $content .= "<a href=\"#\" onclick=\"setFormValueOpenBrowser('db','data[tt_content][$tt_contentUid][pi_flexform][data][addCustomImages][lDEF][image_$i][vDEF]|||tx_dam|'); return false;\"><span title=\"Browse for records\" class=\"t3-icon t3-icon-actions t3-icon-actions-insert t3-icon-insert-record\">&nbsp;</span></a>";
		    $content .= "</td>";
                    $content .= "</tr>";
                    $i++;
                }
            }
        }
        else {
            $content = '<tr><td>' . $response->getHttpStatusMessage() . '</td></tr>';
        }
    
        $content .= "</tbody></table>";
        return $content;
    }
    
    function addCustomTexts($config)
    {
        return '';
	$tt_contentUid = $config['row']['uid'];
	$content = "<table style=\"padding:10px;width:900px;\" cellspacing=\"5\"><tbody>";
	
	$allResponse = $this->getSolrData($config);
	$response = $allResponse[0];
	$pluginId = $allResponse[2];
        
        if(!is_array($allResponse[0])) {
            return $allResponse[0];
        }

        $numberOfHits = $response->response->numFound;
        $content .= "<tr><td colspan=\"3\">$numberOfHits</td></tr>";
        if ( $response->getHttpStatus() == 200 ) { 
            if ( $response->response->numFound > 0 ) {
                $i=0;
                
                //print_r($response);
                
                foreach ( $response->response->docs as $doc) {
                    $content .= "<tr><td style=\"width:200px;\" >$doc->name  ($doc->id)<input id=\"temp_user_text_$i\" type=\"hidden\" value=\"" . $doc->{'staff_custom_text_'.$pluginId.'_s'} . "\"/></td>";
		    $content .= "<td style=\"width:500px;\" id=\"user_text_$i\">";
		    $content .= $doc->{'staff_custom_text_'.$pluginId.'_s'} . "</td>";
		    $content .= "<td style=\"width:200px;\" id=\"user_edit_$i\">";
		    $content .= "<a href=\"#\" onclick=\"addTextarea('user_text_$i','user_edit_$i','" . $doc->id . "','$pluginId');return false;\">Edit</a>";
		    $content .= "</td>";
                    $content .= "</tr>";
                    $i++;
                }
            }
        }
        else {
            $content = '<tr><td>' . $response->getHttpStatusMessage() . '</td></tr>';
        }
    
        $content .= "</tbody></table>";
        return $content;
	
	
    }
    
    function manageCategories($config)
    {
        return '';
	$sys_language_uid = $config['row']['sys_language_uid'];
        if($sys_language_uid==0) {
            $sys_language = 'sv';
        } else {
            $sys_language = 'en';
        }
	
	$oldCat = '';
	$cat = '';
	$allResponse = $this->getSolrData($config);
	$response = $allResponse[0];
	$pluginId = $allResponse[2];
        
        if(!is_array($allResponse[0])) {
            return $allResponse[0];
        }

        $numberOfHits = $response->response->numFound;
	$content = "<table style=\"padding:10px;\" cellspacing=\"5\"><tbody>";

        $content .= "<tr><td colspan=\"" . (count($customcategoriesArray) + 1) . "\">$numberOfHits</td></tr>";
        if ( $response->getHttpStatus() == 200 ) { 
            if ( $response->response->numFound > 0 ) {
                $i=0;
                
                //print_r($response);
                
                foreach ( $response->response->docs as $doc) {
		    $cat = $doc->{'staff_custom_category_facet_'.$sys_language};
		    
		    if(is_array($cat)) {
			foreach($cat as $key => $value) {
			    if($value and $value!=$oldCat) {
				$content .= "<tr>";
				$content .= "<td>";
				$content .= $value;
				$content .= "</td>";
				$content .= "<td>";
				$content .= "<a href=\"#\" onclick=\"updateIndex('deleteIndex','$value','staff_custom_category_facet_$sys_language" . "_".$pluginId."_ss','','');\">Delete</a>";
				$content .= "</td>";
				$content .= "</tr>";
			    }
			    $oldCat = $value;
			}
		    } else {
			if($cat and $cat!=$oldCat) {
			    $content .= "<tr>";
			    $content .= "<td>";
			    $content .= $cat;
			    $content .= "</td>";
			    $content .= "<td>";
			    $content .= "<a href=\"#\" onclick=\"updateIndex('deleteIndex','$cat','staff_custom_category_facet_'.$sys_language,'','');\">Delete</a>";
			    $content .= "</td>";
			    $content .= "</tr>";
			}
			$oldCat = $cat;
		    }
                }
            }
        }
        else {
            $content = '<tr><td>' . $response->getHttpStatusMessage() . '</td></tr>';
        }
    
        $content .= "</tbody></table>";
        return $content;
    }
    
    function manageStaffList($config)
    {
        //print_r($config);
        $content = '';
        $categories = '';
        
        $pid = $config['row']['pid'];
        $sys_language_uid = $config['row']['sys_language_uid'];
        $catVar = 'lth_solr_cat_' . $pid . '_' . $sys_language_uid . '_ss';
        $hideVar = 'lth_solr_hide_' . $pid . '_' . $sys_language_uid . '_i';
        
        //print_r($config);

	$allResponse = $this->getSolrData($config);
	$response = $allResponse[0];
        $customcategories = $allResponse[1];
        
        
        // show documents using the resultset iterator
        $content .= "<style>.ui-state-highlight{width:100%; height: 50px; background-color:yellow;}</style>
            <link rel=\"stylesheet\" href=\"https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/themes/smoothness/jquery-ui.css\">
            <script src=\"/typo3conf/ext/lth_solr/res/jquery.sortable.js\"></script>
            <script language=\"javascript\">
            
            function updateIndex(action, items, value, checked) {
                //console.log(items);
                Ext.Ajax.request({
                    url: 'ajax.php',
                    method: 'POST',
                    params: {
                        'ajaxID' : 'lth_solr::ajaxControl',
                        'action' : action,
                        'items' : items,
                        'value' : value,
                        'checked' : checked,
                        'pid' : '$pid',
                        'sys_language_uid' : $sys_language_uid,
                        'sid' : Math.random()
                    },
                    success: function(response, opts) {
                        //var obj = Ext.decode(response.responseText);
                        //console.dir(obj);
                        //console.log(response.responseText);
                        if(action=='updateText') {
                            //alert(response.responseText);
                        }
                        //console.log(response.responseText);
                    },
                    failure: function(response, opts) {
                       console.log('server-side failure with status code ' + response.status);
                    }
                });
            }
        
            TYPO3.jQuery(document).ready(function() {
                TYPO3.jQuery('.lth_solr_categories').click(function() {
                    updateIndex('updateCategories', TYPO3.jQuery(this).parent().parent().attr('id'), TYPO3.jQuery(this).val(), TYPO3.jQuery(this).prop('checked'));
                });
                
                TYPO3.jQuery('.lth_solr_hideonpage').click(function() {
                    updateIndex('updateHideonpage', TYPO3.jQuery(this).parent().parent().attr('id'), TYPO3.jQuery(this).val(), TYPO3.jQuery(this).prop('checked'));
                    //console.log(TYPO3.jQuery(this).parent().parent().attr('id'));
                });
    
                TYPO3.jQuery('#lth_solr_manage_staff').sortable({
                    placeholder: 'ui-state-highlight',
                    cursor: 'move',
                    update: function( event, ui ) {
                        var IDs = [];
                        TYPO3.jQuery('#lth_solr_manage_staff').find('.ui-state-default').each(function(){ IDs.push(TYPO3.jQuery(this).attr('id')); });
                        updateIndex('resort', JSON.stringify(IDs));
                    }
                }).disableSelection();
            });
            </script>
            <style>
            .ui-state-default {
                border: 1px black dotted;
                height: 50px;
                width: 100%;
                display: block;
                clear:both;
            }
            
            li {
              display:inline;
            }
            </style>";
        
        $content .= '<div id="lth_solr_manage_staff" class="lth_solr_manage_staff">';
        if($response) {
        foreach ($response as $document) {
            $content .= "<div class=\"ui-state-default\"  id=\"" . $document->id . "\">";
            $content .= "<div style=\"width:400px; float:left;\">";
            
            $content .= "<ul style=\"\">";
            
            $content .= '<li style="width: 100px;">' . $document->id . ': </li>';
            $content .= '<li style="width: 100px;">' . $document->display_name_t . '</li>';
            
            $content .= "</ul>";
            
            $content .= "</div>";
            
            $categories = '';
            //print_r( $document->$catVar);
            if($customcategories) {
                $customcategoriesArray = explode("\n", $customcategories);
                if(is_array($customcategoriesArray)) {
                    foreach($customcategoriesArray as $key => $value) {
                        $checkedCat = ' ';
                        if(in_array($value, $document->$catVar)) {
                            $checkedCat = ' " checked="checked" ';
                        }
                        $categories .= "<input type=\"checkbox\" name=\"lth_solr_categories\" class=\"lth_solr_categories\" value=\"$value\"$checkedCat/>$value";
                    }
                }
            }
            
            $checkedHide = ' ';
            if($document->$hideVar) {
                $checkedHide = ' " checked="checked" ';
            }
            
            $content .= "<div style=\"width: 300px; float:left; padding:15px;\">$categories</div>";
            
            $content .= "<div style=\"width: 300px; float:left; padding:15px; border-left: 1px black solid;\">"
                    . "<input type=\"checkbox\" name=\"lth_solr_hideonpage\" class=\"lth_solr_hideonpage\" value=\"1\"$checkedHide/>Hide on this page"
                    . "</div>";
            
            $content .= "</div>";
        }
        
        }

    
        $content .= "</div>";
        return $content;
    }
    
    
    function manageStaffIntroImage($config)
    {
        //print_r($config);
        $content = '';
        $categories = '';
        
        $pid = $config['row']['pid'];
        $sys_language_uid = $config['row']['sys_language_uid'];
        $introVar = 'lth_solr_intro_' . $pid . '_' . $sys_language_uid;
        $imageVar = 'lth_solr_hide_' . $pid . '_' . $sys_language_uid . '_i';
        
        //print_r($config);

	$allResponse = $this->getSolrData($config);
	$response = $allResponse[0];
        
        
        // show documents using the resultset iterator
        $content .= "<style>.ui-state-highlight{width:100%; height: 50px; background-color:yellow;}</style>
            <link rel=\"stylesheet\" href=\"https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/themes/smoothness/jquery-ui.css\">
            <script language=\"javascript\">
            
            function updateIndex(action, items, value, checked) {
                //console.log(items);
                Ext.Ajax.request({
                    url: 'ajax.php',
                    method: 'POST',
                    params: {
                        'ajaxID' : 'lth_solr::ajaxControl',
                        'action' : action,
                        'items' : items,
                        'value' : value,
                        'checked' : checked,
                        'pid' : '$pid',
                        'sys_language_uid' : $sys_language_uid,
                        'sid' : Math.random()
                    },
                    success: function(response, opts) {
                        //var obj = Ext.decode(response.responseText);
                        //console.dir(obj);
                        //console.log(response.responseText);
                        if(action=='updateText') {
                            //alert(response.responseText);
                        }
                        //console.log(response.responseText);
                    },
                    failure: function(response, opts) {
                       console.log('server-side failure with status code ' + response.status);
                    }
                });
            }
        
            TYPO3.jQuery(document).ready(function() {
                TYPO3.jQuery('.lth_solr_categories').click(function() {
                    updateIndex('updateCategories', TYPO3.jQuery(this).parent().parent().attr('id'), TYPO3.jQuery(this).val(), TYPO3.jQuery(this).prop('checked'));
                });
                
                TYPO3.jQuery('.lth_solr_hideonpage').click(function() {
                    updateIndex('updateHideonpage', TYPO3.jQuery(this).parent().parent().attr('id'), TYPO3.jQuery(this).val(), TYPO3.jQuery(this).prop('checked'));
                    //console.log(TYPO3.jQuery(this).parent().parent().attr('id'));
                });
    
                TYPO3.jQuery('#lth_solr_manage_staff').sortable({
                    placeholder: 'ui-state-highlight',
                    cursor: 'move',
                    update: function( event, ui ) {
                        var IDs = [];
                        TYPO3.jQuery('#lth_solr_manage_staff').find('.ui-state-default').each(function(){ IDs.push(TYPO3.jQuery(this).attr('id')); });
                        updateIndex('resort', JSON.stringify(IDs));
                    }
                }).disableSelection();
            });
            </script>
            <style>
            .ui-state-default {
                border: 1px black dotted;
                height: 50px;
                width: 100%;
                display: block;
                clear:both;
            }
            
            li {
              display:inline;
            }
            </style>";
        
        $content .= '<div id="lth_solr_manage_staff" class="lth_solr_manage_staff">';
        if($response) {
        foreach ($response as $document) {
            $content .= "<div class=\"ui-state-default\"  id=\"" . $document->id . "\">";
            $content .= "<div style=\"width:400px; float:left;\">";
            
            $content .= "<ul style=\"\">";
            
            $content .= '<li style="width: 100px;">' . $document->id . ': </li>';
            $content .= '<li style="width: 100px;">' . $document->display_name_t . '</li>';
            
            $content .= "</ul>";
            
            $content .= "</div>";
            
            if($document->lth_solr_intro_t) {
                $introArray = json_decode($document->lth_solr_intro_t, TRUE);
                $intro = $introArray[$introVar];
            }
                        
            $content .= "<div style=\"width: 300px; float:left; padding:15px;\">$intro<input type=\"button\" name=\"Edit\" value=\"Edit\"></div>";
            
            /*$content .= "<div style=\"width: 300px; float:left; padding:15px; border-left: 1px black solid;\">"
                    . "<input type=\"checkbox\" name=\"lth_solr_hideonpage\" class=\"lth_solr_hideonpage\" value=\"1\"$checkedHide/>Hide on this page"
                    . "</div>";*/
            
   /*         <form>
    <input type="hidden" name="hiddenField" />
</form>

<p>Please edit me...</p>

<script type="text/javascript">
var replaceWith = $('<input name="temp" type="text" />'),
    connectWith = $('input[name="hiddenField"]');

$('p').inlineEdit(replaceWith, connectWith);
</script>*/
            
            $content .= "</div>";
        }
        
        }

    
        $content .= "</div>";
        return $content;
    }
    
   /* function deleteCategory($username, $cat, $val)
    {
	$confArr = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['institutioner']);
    
	if (!$confArr['solrServer']) {
	    return 'Ange Solr-server';
	}

	if (!$confArr['solrPort']) {
	    return 'Ange Solr-port';
	}

	if (!$confArr['solrPath']) {
	    return 'Ange Solr-path';
	}

	$scheme = 'http';
	
	$solr = t3lib_div::makeInstance('tx_solr_ConnectionManager')->getConnection($confArr['solrServer'], $confArr['solrPort'], $confArr['solrPath'], $scheme);

        $query = "id:$username";
        $results = false;
        $limit = 1;

        if (get_magic_quotes_gpc() == 1) {
            $query = stripslashes($query);
        }
        
        try {
            $response = $solr->search($query, 0, $limit);
        }
        catch(Exception $e) {
            return '437:' . $e->getMessage();
            exit();
        }
        
        if(isset($response->response->docs[0])) {
 
            //$docs = array();
            foreach($response->response->docs as $document) {
                $doc = array();
                foreach($document as $field => $value) {
                    $doc[$field] = $value;
                }
		if(is_array($doc[$cat])) {
		    $key = array_search($val,$doc[$cat]);
		} else {
		    $key = 0;
		}
		unset($doc[$cat[$key]]);

                unset($doc['_version_']);
                unset($doc['alphaNameSort']);
            }

	    $part = new Apache_Solr_Document();

	    foreach ( $doc as $key => $value ) {
		if ( is_array( $value ) ) {
		    foreach ( $value as $data ) {
			$part->setMultiValue( $key, $data );
		    }
		}
		else {
		    $part->$key = $value;
		}
	    }

            try {
                $solr->addDocument($part);
                $solr->commit();
                $solr->optimize();
                $response = 'delete category done!';

            }
            catch ( Exception $e ) {
                $response = $e->getMessage();
            }
        } else {
            $response = "Kein Eintrag gefunden";
        }

	return $response;
    
    }*/
	    
    function objectToArray($d) {
	if (is_object($d)) {
	    // Gets the properties of the given object
	    // with get_object_vars function
	    $d = get_object_vars($d);
	}

	if (is_array($d)) {
	    /*
	    * Return array converted to object
	    * Using __FUNCTION__ (Magic constant)
	    * for recursive call
	    */
	    return array_map(array($this, 'objectToArray'), $d);
	//$this->d = get_object_vars($d);
	}
	else {
	    // Return array
	    return $d;
	}
    }
}