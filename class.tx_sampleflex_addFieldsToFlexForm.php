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
            foreach ($test->field as $n) {
                foreach($n->attributes() as $name => $val) {
                    if ($val == 'customcategories') {
                        $customcategories = (string)$n->value;
                    } else if($val == 'scope') {
                        $scope = (string)$n->value;
                    } else if($val == 'addpeople') {
                        $addpeople = (string)$n->value;
                    } else if($val == 'categoriesthispage') {
                        $categoriesThisPage = (string)$n->value;
                    } else if($val == 'introthispage') {
                        $introThisPage = (string)$n->value;
                    }
                }
            }
        }
        /*if($scope) {
            $scopeArray1 = explode(',',$scope);
            foreach($scopeArray1 as $scope) {
                $scope = explode('|',$scope);
                $scope = explode('__',$scope[1]);
                $scopeArray[] = $scope[0];
            }
            $scopeArray = array_unique($scopeArray);
        }*/

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

        /*if($scopeArray) {
	    $i = 0;
	    foreach($scopeArray as $key => $value) {
		if($queries or i==0) {
		    //$queries = "usergroup_txt:$value";
                    $queries = "heritage:$value";
		} else {
		    $queries .= " $value";
		}
		$i++;
	    }
	}*/
//echo $queries;
        /*if(trim($addpeople)) {
            $addpeople = str_replace(' ', '', $addpeople);
            $addpeople = str_replace(',', "\n", $addpeople);
            $addpeople = str_replace(':', '', $addpeople);
            $addpeopleArray = explode("\n",$addpeople);
            foreach($addpeopleArray as $value) {
                $queries .= " id:$value";
            }
        }*/

        require(__DIR__.'/service/init.php');

        $settings = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['lth_solr']);
        
        $sconfig = array(
            'endpoint' => array(
                'localhost' => array(
                    'host' => $settings['solrHost'],
                    'port' => $settings['solrPort'],
                    'path' => $settings['solrPath'],
                    'timeout' => $settings['solrTimeout']
                )
            )
        );
    
	if (!$settings['solrHost'] || !$settings['solrPort'] || !$settings['solrPath'] || !$settings['solrTimeout']) {
	    die('Please make all settings in extension manager');
	}

        $client = new Solarium\Client($sconfig);
        $query = $client->createSelect();
        
        $showVar = 'lth_solr_show_' . $pid . '_i';
        
        $query->setQuery("$showVar:1");
        //$query->setFields(array('id', 'display_name_t', $catVar, $hideVar));

        $query->addSort('lth_solr_sort_' . $pid . '_i', $query::SORT_ASC);
        $query->addSort('last_name_sort', $query::SORT_ASC);
        $query->addSort('first_name_sort', $query::SORT_ASC);
        
        $query->setStart($offset)->setRows($limit);

        // this executes the query and returns the result
        $resultset = $client->select($query);

	return array($resultset, $customcategories, $pluginId, $categoriesThisPage, $introThisPage);
    }
    
    
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
            .selection {
                cursor: pointer;
            }
            </style>";
        
        $content .= '<table id="lth_solr_manage_staff_list" class="lth_solr_manage_staff_list"><tbody class="selections">';
        if($response) {
            foreach ($response as $document) {
                $content .= '<tr class="selection" id="edit_' . $document->id . '">';
                $content .= '<td style="width:300px; align:top;">'  . $document->display_name . ' (' . $document->id . ')</td>';

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
                //console.log(TYPO3.jQuery('#'+staffId).find('#img_' + staffId).attr('data-imageId'));
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
                        if(TYPO3.jQuery('.staffIntrotext').length > 0) {
                            alert('You can only edit one row at the time');
                        } else {
                            createEditArea(TYPO3.jQuery(this).parent().parent().attr('id'), this);
                        }
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
                            'introThisPage' : '$introThisPage',
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
                                TYPO3.jQuery('#img_' + staffId).attr('src', '/typo3conf/ext/lth_solr/res/placeholder_noframe.gif');
                                TYPO3.jQuery('#img_' + staffId).attr('data-imageId', '');
                            }
                            
                            TYPO3.jQuery('#lth_solr_edit_member_' + staffId).click(function() {
                                if(TYPO3.jQuery('.staffIntrotext').length > 0) {
                                    alert('You can only edit one row at the time');
                                } else {
                                    createEditArea(TYPO3.jQuery(this).parent().parent().attr('id'), this);
                                }
                            });
                        },
                        failure: function(response, opts) {
                           console.log('server-side failure with status code ' + response.status);
                        }
                    });                    
                    
                    TYPO3.jQuery('#lth_solr_edit_member_' + staffId).click(function() {
                        if(TYPO3.jQuery('.staffIntrotext').length > 0) {
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
                    if(TYPO3.jQuery('.staffIntrotext').length > 0) {
                        alert('You can only edit one row at the time');
                    } else {
                        createEditArea(TYPO3.jQuery(this).parent().parent().attr('id'), this);
                    }
                });

                TYPO3.jQuery('#peopleChoice').change(function() {
                    selectedId = TYPO3.jQuery(this).val();
                    TYPO3.jQuery('#peopleObject').append(TYPO3.jQuery(this).find('option:selected'));
                    updateIndex('addPageShow', selectedId, '', false);
                    var my_options = TYPO3.jQuery('#peopleObject option');
                    my_options.sort(function(a,b) {
                        if (a.text > b.text) return 1;
                        if (a.text < b.text) return -1;
                        return 0;
                    })
                    TYPO3.jQuery('#peopleObject').empty().append( my_options );

                });
                
                TYPO3.jQuery('#peopleObject').change(function() {
                    selectedId = TYPO3.jQuery(this).val();
                    console.log(selectedId);
                    TYPO3.jQuery('#peopleChoice').append(TYPO3.jQuery(this).find('option:selected'));
                    updateIndex('addPageShow', selectedId, '', true);
                    //action, items, value, checked
                    var my_options = TYPO3.jQuery('#peopleChoice option');
                    my_options.sort(function(a,b) {
                        if (a.text > b.text) return 1;
                        if (a.text < b.text) return -1;
                        return 0;
                    })
                    TYPO3.jQuery('#peopleChoice').empty().append( my_options );
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
            #peopleChoice, #peopleObject {
                min-width: 300px;
                min-height: 150px;
            }
            #peopleObject {
                margin-left:20px;
            }
            </style>";
        
        $content .= '<table id="lth_solr_manage_staff_intro" class="lth_solr_manage_staff_intro">';
        //print_r($response);

        if($response) {
            foreach ($response as $document) {
                $content .= "<tr id=\"" . $document->id . "\">";
                $content .= "<td id=\"name_" . $document->id . "\" style=\"width:300px; align:top;\">$document->display_name ($document->id)</td>";

                $intro = $document->$introVar;
                $content .= "<td style=\"width:400px; align:top;\" id=\"intro_" . $document->id . "\">$intro</td>";
                
                //echo $imageVar . $document->$imageVar;
                if(!$document->image) {
                    $image = '/typo3conf/ext/lth_solr/res/placeholder_noframe.gif';
                    $imageId = '';
                } else {
                    $image = '/fileadmin' . $document->image;
                    $imageId = $document->image_id;
                }
                
                $content .= '<td style="width: 100px;"><img src="' . $image . '" id="img_' . $document->id . '" data-imageId="'.$imageId.'" style="width:40px;height:50px;" /></td>';
                
                $content .= "<td><input type=\"button\" name=\"Edit\" id=\"lth_solr_edit_member_" . $document->id . "\" class=\"lth_solr_edit_member\" value=\"Edit\"></td>";
            
                $content .= "</tr>";
            }
        }
    
        $content .= "</table>";
        return $content;
    }
    
    
    function manageStaffRedirects($config)
    {
        $content = '';
        $categories = '';
        
        $pid = $config['row']['pid'];
        $sys_language_uid = $config['row']['sys_language_uid'];
        
        $pi_flexform = $config['row']['pi_flexform'];
        $xml = simplexml_load_string($pi_flexform);
        $test = $xml->data->sheet[0]->language;
      
        if($test) {
            foreach ($test->field as $n) {
                foreach($n->attributes() as $name => $val) {
                    if ($val == 'detailpage') {
                        $detailpage = (string)$n->value;
                    }
                }
            }
        }
        
        if($detailpage) {
            $detailpage = array_shift(explode('|', array_pop(explode('_',$detailpage))));
        }

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
            <script language=\"javascript\">
            
            function createEditRedirectArea(staffId, obj)
            {
                //console.log(TYPO3.jQuery(obj).parent().prev().html());
                //console.log(TYPO3.jQuery('#'+staffId).find('#img_' + staffId).attr('data-imageId'));
                var prevArea = TYPO3.jQuery(obj).parent().parent();
                                
                var name = TYPO3.jQuery(obj).parent().prev().prev().html();
                var redirect = TYPO3.jQuery(obj).parent().parent().find('.lth_solr_redirect').val();

                var detailpage = TYPO3.jQuery(obj).parent().parent().find('.lth_solr_detailpage').val();
                var redirectTo = '';
                var source = '';
                //console.log(redirect);
                if(!redirect && redirect=='') {
                    redirectTo = 'index.php?id='+detailpage+'&uuid='+TYPO3.jQuery(obj).parent().prev().html();
                } else {
                    redirect = JSON.parse(decodeURIComponent(redirect));
                    redirectTo = redirect[1];
                    source = redirect[0];
                    deleteButton = '<input type=\"button\" id=\"staffRedirectDelete\" value=\"Delete\" />';
                }
                
                //var deleteIcon = '<span class=\"t3-icon t3-icon-actions t3-icon-actions-edit t3-icon-edit-delete\">&nbsp;</span>';
                //console.log(source+';'+redirectTo);
                var editArea = '<tr id=\"'+staffId+'\">';
                editArea += '<td style=\"align:top;\">' + name + '</td>';
                editArea += '<td style=\"align:top;\">Source: <input type=\"text\" name=\"staffTxtSource\" value=\"'+source+'\" class=\"staffTxtSource\" /></td>';
                editArea += '<td style=\"align:top;\">Redirect to: <input type=\"text\" name=\"staffTxtRedirectto\" value=\"'+redirectTo+'\" class=\"staffTxtRedirectto\" /></td>';
                editArea += '<td><input type=\"button\" id=\"staffRedirectSave\" value=\"Save\" /><input type=\"button\" value=\"Cancel\" id=\"staffRedirectCancel\" /></td>';
                editArea += '</tr>';              
                //console.log(TYPO3.jQuery('#'+staffId).html());
                //console.log(editArea);
                TYPO3.jQuery('#'+staffId).replaceWith(editArea);
                         
                TYPO3.jQuery('#staffRedirectCancel').click(function(){
                    TYPO3.jQuery(this).parent().parent().replaceWith(prevArea);
                    TYPO3.jQuery('#lth_solr_edit_' + staffId).click(function() {
                        if(TYPO3.jQuery('.staffTxtSource').length > 0) {
                            alert('You can only edit one row at the time');
                        } else {
                            createEditRedirectArea(TYPO3.jQuery(this).parent().parent().attr('id'), this);
                        }
                    });
                });
                
                TYPO3.jQuery('#staffRedirectSave').click(function(){
                    var value = [TYPO3.jQuery(this).parent().parent().find('.staffTxtSource').val(),TYPO3.jQuery(this).parent().parent().find('.staffTxtRedirectto').val()];
                    var staffId = TYPO3.jQuery(this).parent().parent().attr('id');
                    //console.log(staffId);
                    Ext.Ajax.request({
                        url: 'ajax.php',
                        method: 'POST',
                        dataType: 'json',
                        params: {
                            'ajaxID' : 'lth_solr::ajaxControl',
                            'action' : 'updateRedirect',
                            'items' : staffId.replace('redirect_',''),
                            'value' : JSON.stringify(value),
                            'pid' : '$pid',
                            'sys_language_uid' : $sys_language_uid,
                            'sid' : Math.random()
                        },
                        success: function(data) {
                            TYPO3.jQuery(prevArea).find('.lth_solr_detailpage').val(value[0]+' '+value[1]);
                            TYPO3.jQuery(prevArea).find('td:eq(2)').html(value[0]+' '+value[1]);
                            TYPO3.jQuery('#'+staffId).replaceWith(prevArea);
                            var response = JSON.parse(data.responseText);
                            
                            TYPO3.jQuery('#lth_solr_edit_' + staffId).click(function() {
                                if(TYPO3.jQuery('.staffRedirect').length > 0) {
                                    alert('You can only edit one row at the time');
                                } else {
                                    createEditRedirectArea(TYPO3.jQuery(this).parent().parent().attr('id'), this);
                                }
                            });
                        },
                        failure: function(response, opts) {
                           console.log('server-side failure with status code ' + response.status);
                        }
                    });                    
                });
                
                TYPO3.jQuery('#staffRedirectDelete').click(function(){
                    alert('??');
                });
            }
            
        
            TYPO3.jQuery(document).ready(function() {
                TYPO3.jQuery('.lth_solr_edit_redirect').click(function() {
                    if(TYPO3.jQuery('#staffRedirectSave').length > 0) {
                        alert('You can only edit one row at the time');
                    } else {
                        createEditRedirectArea(TYPO3.jQuery(this).parent().parent().attr('id'), this);
                    }
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
            #lth_solr_manage_staff_list td {
                padding:10px;
            }
            li {
                display:inline;
            }
            .lth_solr_categories {
                padding-left:10px;
            }
            .staffTxtRedirectto {
                width:400px;
            }
            </style>";       
        
        $content .= '<table id="lth_solr_manage_redirects class="lth_solr_manage_redirects"><tbody class="selections">';
        if($response) {
            foreach ($response as $document) {
                $redirect='';
                if($document->redirect) {
                    $redirectArray = json_decode($document->redirect);
                    $redirect = $redirectArray[0] . ' ' . $redirectArray[1];
                }
                $content .= '<tr class="selection" id="redirect_' . $document->id . '">';
                $content .= '<td style="width:300px; align:top;">'  . $document->display_name . ' (' . $document->id . ')</td>';
                $content .= '<td style="display:none;">'  . $document->uuid . '</td>';
                $content .= "<td><input type=\"hidden\" class=\"lth_solr_detailpage\" value=\"$detailpage\" /><input type=\"hidden\" class=\"lth_solr_redirect\" value=\"" . rawurlencode($document->redirect) . "\" /><input type=\"button\" name=\"Edit\" id=\"lth_solr_edit_redirect_" . $document->id . "\" class=\"lth_solr_edit_redirect\" value=\"Edit\">" . $redirect . "</td>";

                $content .= "</tr>";
            }
        }
    
        $content .= "</tbody></table>";
        
        return $content;
    }
    
    function managePublications($config)
    {
        //print_r($config);
        $content = '';
        $categories = '';
        
        $pid = $config['row']['pid'];
        $sys_language_uid = $config['row']['sys_language_uid'];
        
        $hideVar = 'lth_solr_hide_' . $pid . '_i';
        
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

                    TYPO3.jQuery('.lth_solr_hidepublication').click(function() {
                        updateIndex('hidePublication', TYPO3.jQuery(this).parent().parent().attr('id').replace('publication_',''), TYPO3.jQuery(this).val(), TYPO3.jQuery(this).prop('checked'));
                        //console.log('???');
                    });

                    TYPO3.jQuery('.pubselections').sortable({
                        placeholder: 'ui-state-highlight',
                        cursor: 'move',
                        update: function( event, ui ) {
                            var IDs = [];
                            TYPO3.jQuery('#lth_solr_manage_publications').find('.pubselection').each(function(){ IDs.push(TYPO3.jQuery(this).attr('id').replace('publication_','')); });
                            updateIndex('resortPublications', JSON.stringify(IDs));
                            //console.log('!!!');
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
            .selection {
                cursor: pointer;
            }
            .lth_solr_manage_publications td {
                border-bottom:1px solid #dedede;padding:10px;
            }
            </style>";
         
        require(__DIR__.'/service/init.php');

        $settings = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['lth_solr']);
        
        $sconfig = array(
            'endpoint' => array(
                'localhost' => array(
                    'host' => $settings['solrHost'],
                    'port' => $settings['solrPort'],
                    'path' => $settings['solrPath'],
                    'timeout' => $settings['solrTimeout']
                )
            )
        );

	if (!$settings['solrHost'] || !$settings['solrPort'] || !$settings['solrPath'] || !$settings['solrTimeout']) {
	    die('Please make all settings in extension manager');
	}
        
        $pi_flexform = $config['row']['pi_flexform'];
        $xml = simplexml_load_string($pi_flexform);
        $test = $xml->data->sheet[0]->language;
      
        if($test) {
            foreach ($test->field as $n) {
                foreach($n->attributes() as $name => $val) {
                    if ($val == 'fe_groups') {
                        $fe_groups = (string)$n->value;
                    } else if($val == 'fe_users') {
                        $fe_users = (string)$n->value;
                    }
                }
            }
        }

        if($fe_groups) {
            $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('title','fe_groups',"uid in(" . explode("|",$fe_groups)[0].")");
            while ($row = $GLOBALS["TYPO3_DB"]->sql_fetch_assoc($res)) {
                $title[] = explode('__', $row['title'])[0];
            }
            if($title) {
                $scope = implode(',', $title);
            }
            $GLOBALS['TYPO3_DB']->sql_free_result($res);
        } else if($fe_users) {
            $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('lth_solr_uuid','fe_users',"uid = " . intval($fe_users));
            $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
            $scope = $row['lth_solr_uuid'];
            $GLOBALS['TYPO3_DB']->sql_free_result($res);
        } else {
            return 'Please add a group or user!';
        }

        $client = new Solarium\Client($sconfig);
        $query = $client->createSelect();
        $query->addSort('lth_solr_sort_' . $pid . '_i', $query::SORT_ASC);
        $query->addSort('publicationDateYear', $query::SORT_DESC);
        $query->setQuery('doctype:publication AND (organisationSourceId:'.$scope.' OR authorId:'.$scope.')');
        $query->setStart(0)->setRows(1000);
        $response = $client->select($query);
        
        $content .= '<table id="lth_solr_manage_publications" class="lth_solr_manage_publications"><tbody class="pubselections">';
        
        if($response) {
            foreach ($response as $document) {
                $title = '';
                if($document->title) {
                    $title = implode(',',$document->title);
                }
                $content .= '<tr class="pubselection" id="publication_' . $document->id . '">';
                $content .= '<td style="width:600px; align:top;">'  . $title . ' (' . $document->id . ')</td>';

                $checkedHide = ' ';
                if($document->$hideVar) {
                    $checkedHide = ' " checked="checked" ';
                }

                //$content .= "<td style=\"width: 500px; align:top;\">$categories</td>";

                $content .= "<td style=\"width: 150px;padding-left:90px;\">"
                        . "<input type=\"checkbox\" name=\"lth_solr_hidepublication\" class=\"lth_solr_hidepublication\" value=\"1\"$checkedHide/>Hide on this page"
                        . "</td>";

                $content .= "</tr>";
            }
        
        }

        $content .= "</tbody></table>";
        
        return $content;
    }
    
    
    function addScope($content)
    {
        $content = '';
        $objects;
        $choices;
        $lth_solr_show_bool = false;
        
        $pid = $config['row']['pid'];
        $sys_language_uid = $config['row']['sys_language_uid'];
        $showVar = 'lth_solr_show_' . $pid . '_i';
        
        $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery("uid,title","fe_groups","","","title");
        while ($row = $GLOBALS["TYPO3_DB"]->sql_fetch_assoc($res)) {
            $uid = $row['uid'];
            $title = $row['title'];
            
            if($lth_solr_show_bool) {
                $choices .= '<option value="' . $uid . '">' . $title . '</option>';
            } else {
                $objects .= '<option value="' . $uid . '">' . $title . '</option>';
            }
        }

        $GLOBALS['TYPO3_DB']->sql_free_result($res);
        
        $content .= '<table id="lth_solr_addScope" class="lth_solr_addScope"><tbody class="">';
                
        $content .= '<tr>';
        
        $content .= '<tr><td>Valda</td><td>Objekt</td></tr>';
        
        $content .= '<td><select class="selectAddScope" id="scopeChoice" name="scopeChoice" multiple="multiple">';
        $content .= $choices;
        $content .= '</select></td>';
                
        $content .= '<td><select class="selectAddScope" id="scopeObject" name="scopeObject" multiple="multiple">';
        $content .= $objects;
        $content .= '</select></td>';
        
        $content .= '</tr>';
        
        $content .= "</tbody></table>";
        
        return $content;
    }
	
    
    function addPeople($content)
    {
        $content = '';
        $objects;
        $choices;
        $lth_solr_show_bool = false;
        
        $pid = $config['row']['pid'];
        $sys_language_uid = $config['row']['sys_language_uid'];
        $showVar = 'lth_solr_show_' . $pid . '_i';
        
        $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery("lucache_id,first_name,last_name,lth_solr_show","fe_users","lucache_id!=''","","last_name, first_name");
        while ($row = $GLOBALS["TYPO3_DB"]->sql_fetch_assoc($res)) {
            $name = $row['last_name'] . ', ' . $row['first_name'];
            $id = $row['lucache_id'];
            $lth_solr_show = $row['lth_solr_show'];
            if($lth_solr_show) {
                $showArray = json_decode($lth_solr_show);
                if(in_array($showVar, $showArray)) {
                    $lth_solr_show_bool = true;
                }
            } 
            if($lth_solr_show_bool) {
                $choices .= '<option value="' . $id . '">' . $name . ' (' . $lucache_id . ')</option>';
            } else {
                $objects .= '<option value="' . $id . '">' . $name . ' (' . $lucache_id . ')</option>';
            }
        }

        $GLOBALS['TYPO3_DB']->sql_free_result($res);
        
        $content .= '<table id="lth_solr_addPeople" class="lth_solr_addPeople"><tbody class="">';
                
        $content .= '<tr>';
        
        $content .= '<tr><td>Valda</td><td>Objekt</td></tr>';
        
        $content .= '<td><select class="selectAddPeople" id="peopleChoice" name="peopleChoice" multiple="multiple">';
        $content .= $choices;
        $content .= '</select></td>';
                
        $content .= '<td><select class="selectAddPeople" id="peopleObject" name="peopleObject" multiple="multiple">';
        $content .= $objects;
        $content .= '</select></td>';
        
        $content .= '</tr>';
        
        $content .= "</tbody></table>";
        
        return $content;
    }
    
   
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