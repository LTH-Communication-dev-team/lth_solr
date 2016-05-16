<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class user_sampleflex_addFieldsToFlexForm {
    
    
    function getSolrData ($config) 
    {    
        $addpeopleArray = array();
        $addpeople = '';

        $pi_flexform = $config['row']['pi_flexform'];
        $pid = $config['row']['pid'];
        $sys_language_uid = $config['row']['sys_language_uid'];
        $catVar = 'lth_solr_cat_' . $pid . '_' . $sys_language_uid . '_ss';
        $hideVar = 'lth_solr_hide_' . $pid . '_' . $sys_language_uid . '_i';

        $xml = simplexml_load_string($pi_flexform);
        $test = $xml->data->sheet[0]->language;
      
        if($test) {
            echo '27b';
            foreach ($test->field as $n) {
                foreach($n->attributes() as $name => $val) {
                    if ($val == 'customcategories') {
                        $customcategories = $n->value;
                    } else if($val == 'scope') {
                        $scope = $n->value;
                    } else if($val == 'addpeople') {
                        $addpeople = $n->value;
                    } else if($val == 'categoriesthispage') {
                        $categoriesThisPage = $n->value;
                    } else if($val == 'introthispage') {
                        $introThisPage = $n->value;
                    }
                }
            }
        }
echo '44b';
	if(trim($scope) != '') {
	    $scope = str_replace(' ', '', $scope);
	    $scope = str_replace(',', "\n", $scope);
	    $scopeArray = explode("\n", $scope);
	} else if(trim($addpeople)) {
	    //
	} else {
	    return array(null);
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
echo '67';
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
        echo '79';
        if(trim($addpeople)) {
            $addpeople = str_replace(' ', '', $addpeople);
            $addpeople = str_replace(',', "\n", $addpeople);
            $addpeople = str_replace(':', '', $addpeople);
            $addpeopleArray = explode("\n",$addpeople);
            foreach($addpeopleArray as $value) {
                $queries .= " id:$value";
            }
        }
        echo '89b' . __DIR__.'/pi2/init.php';
        require(__DIR__.'/pi2/init.php');
echo '91';
        $client = new Solarium\Client($config);
        $query = $client->createSelect();
        $query->setQuery($queries);
        //$query->setFields(array('id', 'display_name_t', $catVar, $hideVar));

        $query->addSort('lth_solr_sort_' . $pid . '_i', $query::SORT_ASC);
        $query->addSort('last_name_t', $query::SORT_ASC);
        $query->addSort('first_name_t', $query::SORT_ASC);
        
        $query->setStart($offset)->setRows($limit);

        // this executes the query and returns the result
        $resultset = $client->select($query);

	return array($resultset, $customcategories, $pluginId, $categoriesThisPage, $introThisPage);
    }
    
    /*function addCustomCategories ($config)
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
		    if($doc->{'image_s'}) {
			$imageArray = explode('/',$doc->{'image_s'});
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
		    $content .= "<a href=\"#\" onclick=\"addTextarea('user_text_$i','user_edit_$i','" . $doc->id . "','$pluginId');return false;\">Edita</a>";
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
    */
    
    
    function manageStaffList($config)
    {
        //print_r($config);
        $content = '';
        $categories = '';
        
        $pid = $config['row']['pid'];
        $sys_language_uid = $config['row']['sys_language_uid'];
        
        
        //print_r($config);

	$allResponse = $this->getSolrData($config);
	$response = $allResponse[0];
        $customcategories = $allResponse[1];
        $categoriesThisPage = $allResponse[3];

        if(intval($categoriesThisPage) === 1) {
            $catVar = 'lth_solr_cat_' . $pid . '_ss';
        } else {
            $catVar = 'lth_solr_cat_ss';
        }
        $hideVar = 'lth_solr_hide_' . $pid . '_i';
        
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
                        'categoriesThisPage' : '$categoriesThisPage',
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
                    updateIndex('updateCategories', TYPO3.jQuery(this).parent().parent().attr('id').replace('edit_',''), TYPO3.jQuery(this).val(), TYPO3.jQuery(this).prop('checked'));
                });
                
                TYPO3.jQuery('.lth_solr_hideonpage').click(function() {
                    updateIndex('updateHideonpage', TYPO3.jQuery(this).parent().parent().attr('id').replace('edit_',''), TYPO3.jQuery(this).val(), TYPO3.jQuery(this).prop('checked'));
                    //console.log(TYPO3.jQuery(this).parent().parent().attr('id'));
                });
    
                TYPO3.jQuery('.selections').sortable({
                    placeholder: 'ui-state-highlight',
                    cursor: 'move',
                    update: function( event, ui ) {
                        var IDs = [];
                        TYPO3.jQuery('#lth_solr_manage_staff_list').find('.selection').each(function(){ IDs.push(TYPO3.jQuery(this).attr('id').replace('edit_','')); });
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
            #lth_solr_manage_staff_list td {
                padding:10px;
            }
            li {
                display:inline;
            }
            .lth_solr_categories {
                padding-left:10px;
            }
            </style>";
        
        $content .= '<table id="lth_solr_manage_staff_list" class="lth_solr_manage_staff_list"><tbody class="selections">';
        if($response) {
        foreach ($response as $document) {
            $content .= '<tr class="selection" id="edit_' . $document->id . '">';
            $content .= '<td style="width:300px; align:top;">'  . $document->display_name_t . ' (' . $document->id . ')</td>';
            
            $categories = '';
            //print_r( $document->$catVar);
            if($customcategories) {
                $customcategoriesArray = explode("\n", $customcategories);
                if(is_array($customcategoriesArray)) {
                    foreach($customcategoriesArray as $key => $value) {
                        $checkedCat = ' ';
                        if($document->$catVar) {
                            if(in_array($value, $document->$catVar)) {
                                $checkedCat = ' " checked="checked" ';
                            }
                        }
                        $content .= "<td><input type=\"checkbox\" name=\"lth_solr_categories\" class=\"lth_solr_categories\" value=\"$value\"$checkedCat/>$value</td>";
                    }
                }
            }
            
            $checkedHide = ' ';
            if($document->$hideVar) {
                $checkedHide = ' " checked="checked" ';
            }
            
            //$content .= "<td style=\"width: 500px; align:top;\">$categories</td>";
            
            $content .= "<td style=\"width: 150px;padding-left:90px;\">"
                    . "<input type=\"checkbox\" name=\"lth_solr_hideonpage\" class=\"lth_solr_hideonpage\" value=\"1\"$checkedHide/>Hide on this page"
                    . "</td>";
            
            $content .= "</tr>";
        }
        
        }

    
        $content .= "</tbody></table>";
        
        return $content;
    }
    
    
    function manageStaffIntroImage($config)
    {
        //print_r($config);
        $content = '';
        $categories = '';
        
        $pid = $config['row']['pid'];

        $sys_language_uid = $config['row']['sys_language_uid'];
        //$introVar = 'lth_solr_intro_' . $pid . '_' . $sys_language_uid;
        
        //print_r($config);

	$allResponse = $this->getSolrData($config);
	$response = $allResponse[0];
        $introThisPage = $allResponse[4];

        if(intval($introThisPage)===1) {
            $introVar = 'staff_custom_text_' . $pid . '_s';
        } else {
            $introVar = 'staff_custom_text_s';
        }

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
                        'introThisPage' : '$introThisPage',
                        'pid' : '$pid',
                        'sys_language_uid' : $sys_language_uid,
                        'sid' : Math.random()
                    },
                    success: function(response, opts) {
                        //console.log(response.responseText);
                    },
                    failure: function(response, opts) {
                       console.log('server-side failure with status code ' + response.status);
                    }
                });
            }
            
            
            function createEditArea(staffId, obj)
            {
                var prevArea = TYPO3.jQuery(obj).parent().parent();
                
                var imageId = TYPO3.jQuery('#'+staffId).find('#img_' + staffId).attr('data-imageId');
                var staffIntrotext = TYPO3.jQuery('#'+staffId).find('#intro_' + staffId).html();
                
                var name = TYPO3.jQuery('#name_'+staffId).html();
                var folderIcon = '<span title=\"Browse for records\" class=\"t3-icon t3-icon-actions t3-icon-actions-insert t3-icon-insert-record\">&nbsp;</span>';
                var deleteIcon = '<span class=\"t3-icon t3-icon-actions t3-icon-actions-edit t3-icon-edit-delete\">&nbsp;</span>';
                
                var editArea = '<tr id=\"'+staffId+'\">';
                editArea += '<td style=\"align:top;\">' + name + '</td>';
                editArea += '<td style=\"align:top;\"><textarea class=\"staffIntrotext\" name=\"staffIntrotext\" rows=\"4\" cols=\"50\">'+staffIntrotext+'</textarea></td>';
                editArea += '<td style=\"align:top;\"><input type=\"text\" name=\"staffTxtImage\" value=\"'+imageId+'\" class=\"staffTxtImage\" />';
                editArea += '<a class=\"staffImage\" href=\"javascript:\">' + folderIcon + '</a> <a class=\"staffDelete\" href=\"javascript:\">' + deleteIcon + '</a></td>';
                editArea += '<td><input type=\"button\" id=\"staffSave\" value=\"Save\" /><input type=\"button\" value=\"Cancel\" id=\"staffCancel\" /></td></tr>';
                TYPO3.jQuery(obj).parent().parent().replaceWith(editArea);
                
                TYPO3.jQuery('a.staffImage').click(function(){
                    setFormValueOpenBrowser('file','staffTxtImage|||jpg,gif,png|');
                });
                
                TYPO3.jQuery('#staffCancel').click(function(){
                    TYPO3.jQuery(this).parent().parent().replaceWith(prevArea);
                    
                    TYPO3.jQuery('#lth_solr_edit_member_' + staffId).click(function() {
                        createEditArea(TYPO3.jQuery(this).parent().parent().attr('id'), this);
                    });
                });
                
                TYPO3.jQuery('#staffSave').click(function(){
                    var imageId = TYPO3.jQuery(this).parent().parent().find('.staffTxtImage').val();
                    var value = [TYPO3.jQuery(this).parent().parent().find('.staffIntrotext').val(), imageId];
                    var staffId = TYPO3.jQuery(this).parent().parent().attr('id');
                    
                    Ext.Ajax.request({
                        url: 'ajax.php',
                        method: 'POST',
                        dataType: 'json',
                        params: {
                            'ajaxID' : 'lth_solr::ajaxControl',
                            'action' : 'updateIntroAndImage',
                            'items' : staffId,
                            'value' : JSON.stringify(value),
                            'pid' : '$pid',
                            'sys_language_uid' : $sys_language_uid,
                            'sid' : Math.random()
                        },
                        success: function(data) {
                            TYPO3.jQuery('#'+staffId).replaceWith(prevArea);
                            var response = JSON.parse(data.responseText);
                            TYPO3.jQuery('#intro_' + staffId).html(response.introText);
                            if(response.identifier) {
                                TYPO3.jQuery('#img_' + staffId).attr('src', '/fileadmin' + response.identifier);
                                TYPO3.jQuery('#img_' + staffId).attr('data-imageId', imageId);
                            } else {
                                TYPO3.jQuery('#img_' + staffId).attr('src', '/typo3conf/ext/lth_solr/res/placeholder.gif');
                                TYPO3.jQuery('#img_' + staffId).attr('data-imageId', '');
                            }
                            
                            TYPO3.jQuery('#lth_solr_edit_member_' + staffId).click(function() {
                                createEditArea(TYPO3.jQuery(this).parent().parent().attr('id'), this);
                            });
                        },
                        failure: function(response, opts) {
                           console.log('server-side failure with status code ' + response.status);
                        }
                    });                    
                    
                    TYPO3.jQuery('#lth_solr_edit_member_' + staffId).click(function() {
                        if(TYPO3.jQuery('#staffIntrotext').length > 0) {
                            alert('You can only edit one row at the time');
                        } else {
                            createEditArea(TYPO3.jQuery(this).parent().parent().attr('id'), this);
                        }
                    });
                });
                
                TYPO3.jQuery('a.staffDelete').click(function() {
                    var staffId = TYPO3.jQuery(this).parent().parent().attr('id');
                    TYPO3.jQuery(this).parent().parent().find('.staffTxtImage').val('');
                    TYPO3.jQuery('#img_'+staffId).attr('src', '/typo3conf/ext/lth_solr/res/placeholder.gif');
                });
            }
        
            TYPO3.jQuery(document).ready(function() {
    
                TYPO3.jQuery('#lth_solr_manage_staff_intro').sortable({
                    placeholder: 'ui-state-highlight',
                    cursor: 'move',
                    update: function( event, ui ) {
                        var IDs = [];
                        TYPO3.jQuery('#lth_solr_manage_staff_intro').find('.ui-state-default').each(function(){ IDs.push(TYPO3.jQuery(this).attr('id')); });
                        updateIndex('resort', JSON.stringify(IDs));
                    }
                }).disableSelection();
                
                TYPO3.jQuery('.lth_solr_edit_member').click(function() {
                    createEditArea(TYPO3.jQuery(this).parent().parent().attr('id'), this);
                });
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
            .lth_solr_manage_staff_intro td {
                padding:10px;
            }
            .staffTxtImage {
                width:40px;
            }
            li {
              display:inline;
            }
            </style>";
        
        $content .= '<table id="lth_solr_manage_staff_intro" class="lth_solr_manage_staff_intro">';
        //print_r($response);

        if($response) {
            foreach ($response as $document) {
                $content .= "<tr id=\"" . $document->id . "\">";
                $content .= "<td id=\"name_" . $document->id . "\" style=\"width:300px; align:top;\">$document->display_name_t ($document->id)</td>";

                $intro = $document->$introVar;
                $content .= "<td style=\"width:400px; align:top;\" id=\"intro_" . $document->id . "\">$intro</td>";
                
                //echo $imageVar . $document->$imageVar;
                if(!$document->image_s) {
                    $image = '/typo3conf/ext/lth_solr/res/placeholder.gif';
                } else {
                    $image = '/fileadmin' . $document->image_s;
                    $imageId = $document->image_id_s;
                }
                
                $content .= '<td style="width: 100px;"><img src="' . $image . '" id="img_' . $document->id . '" data-imageId="'.$imageId.'" style="width:50px;height:50px;" /></td>';
                
                $content .= "<td><input type=\"button\" name=\"Edit\" id=\"lth_solr_edit_member_" . $document->id . "\" class=\"lth_solr_edit_member\" value=\"Edit\"></td>";
            
                $content .= "</tr>";
            }
        }
    
        $content .= "</table>";
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