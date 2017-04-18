<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Lth\Lthsolr\Task;

use TYPO3\CMS\Core\Utility\GeneralUtility;

class ConvertForms extends \TYPO3\CMS\Scheduler\Task\AbstractTask {
	
    function execute()
    {
        error_reporting(E_ALL ^ E_NOTICE);
        
	$executionSucceeded = FALSE;

	$executionSucceeded = $this->convertForms();
        
	return $executionSucceeded;
    }

    function convertForms()
    {

        
        $i=0;
        $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery("t.uid, t.pid, t.bodytext, t.subheader, t.sorting, t.pages", "tt_content t JOIN pages p ON p.uid = t.pid", "t.CType = 'mailform' AND t.deleted = 0 AND t.hidden=0 AND p.deleted = 0 AND p.hidden=0");
        //SELECT t.uid, t.pid, t.bodytext FROM tt_content t JOIN pages p ON p.uid = t.pid WHERE t.CType = 'mailform' AND t.deleted = 0 AND t.hidden=0 AND p.deleted = 0 AND p.hidden=0
        while ($row = $GLOBALS["TYPO3_DB"]->sql_fetch_assoc($res)) {
            $uid = $row['uid'];
            $pid = $row['pid'];
            $bodytext = $row['bodytext'];
            $subheader = $row['subheader'];
            $sorting = $row['sorting'];
            $pages = $row['pages'];
            if($bodytext) {
                $i=0;
                $content = "enctype = multipart/form-data\n";
                $content .= "method = post\n";
                $content .= "prefix = tx_form\n";
                $content .= "confirmation = 0\n";
                $content .= "postProcessor {\n";
                $content .= "1 = mail\n";
                $content .= "1 {\n";
                $content .= "recipientEmail = $subheader\n";
                $content .= "senderEmail = $subheader\n";
                $content .= "subject = ###subject###\n";
                $content .= "}\n";
                if($pages) {
                    $content .= "2 = redirect\n";
                    $content .= "2 {\n";
                    $content .= "destination = $pages\n";
                    $content .= "}\n";
                }
                $content .= "}\n";
                $bodytextArray = explode("\n", $bodytext);

                if(is_array($bodytextArray)) {
                    foreach($bodytextArray as $bodytextKey => $bodytextRow) {
                        if($bodytextRow && strpos($bodytextRow, "|") !== false) {
                            
                            $bodytextRowArray = explode("|", $bodytextRow);
                            if(is_array($bodytextRowArray)) {
                                //$GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_devlog', array('msg' => $bodytextRow, 'crdate' => time()));
                                
                                $label = trim($bodytextRowArray[0]);
                                $field = trim($bodytextRowArray[1]);
                                $value = trim($bodytextRowArray[2]);
                                if(substr($field, 0, 1)==="*") {
                                    $mandatory = true;
                                    $field = substr($field, 1);
                                } else {
                                    $mandatory = false;
                                }
                                $fieldName = array_shift(explode("=", $field));
                                $fieldType = array_pop(explode("=", $field));
                                if($fieldName==="subject" && $fieldType==="hidden") {
                                    $content = str_replace("###subject###", $value, $content);
                                } else if($fieldName==="formtype_mail" && $fieldType==="submit") {
                                    $submitValue = $value;
                                } else if($fieldName==="html_enabled" && $fieldType==="hidden") {
                                    
                                } else {
                                    $i = $i+10;
                                }
                                if($fieldType) {
                                    $fieldTypeArray = explode(",", $fieldType);
                                    switch($fieldTypeArray[0]) {
                                        case "input":
                                            $content .= $this->convertInput($fieldName, $fieldType, $label, $value, $mandatory, $i);
                                            break;
                                        case "textarea":
                                            $content .= $this->convertTextarea($fieldName, $fieldType, $label, $value, $mandatory, $i);
                                            break;
                                        case "check":
                                            $content .= $this->convertCheck($fieldName, $fieldType, $label, $value, $mandatory, $i);
                                            break;
                                        case "radio":
                                            $content .= $this->convertRadio($fieldName, $fieldType, $label, $value, $mandatory, $i);
                                            break;
                                        case "select":
                                            $content .= $this->convertSelect($fieldName, $fieldType, $label, $value, $mandatory, $i);
                                            break;
                                    }
                                }
                            }
                        }
                    }
                }
                $i = $i+10;
                $content .= "$i = SUBMIT\n";
                $content .= "$i {\n";
                $content .= "type = submit\n";
                $content .= "class = btn btn-default\n";
                $content .= "name = submit\n";
                $content .= "value = $submitValue\n";
                $content .= "}\n";
                
                try {
                    $GLOBALS['TYPO3_DB']->exec_INSERTquery('tt_content', array('pid' => $pid, 'CType' => 'mailform', 'bodytext' => $content, 'sorting' => intval($sorting)+10, 'tstamp' => time(), 'crdate' => time()));

                } catch(Exception $e) {
                    echo 'Message: ' .$e->getMessage();
                }

            }
            
            /*
             * Name: | *name=input,40 | Enter your name here | 
Email: | *email=input,40 |  | 
Address: | address=textarea,40,5 |  | 
Contact me: | tv=check | 1

 | formtype_mail=submit | Send form!
 | html_enabled=hidden | 1
 | subject=hidden | This is the subject
# Example content:
             */
        }
        $GLOBALS['TYPO3_DB']->sql_free_result($res);
                                        

        return TRUE;
    }
    
    function convertInput($fieldName, $fieldType, $label, $value, $mandatory, $i)
    {
        /*
         * 10 = TEXTLINE
10 {
	type = text
	name = name
	required = required
	label {
		value = Name
	}
}
         20 = TEXTLINE
10 {
type = text
name = email
required = required
label { 
value = Email:
 }
         */
        $content = "$i = TEXTLINE\n";
        $content .= "$i {\n";
        $content .= "type = text\n";
        $content .= "name = $fieldName\n";
        if($mandatory===true) {
            $content .= "required = required\n";
        }
        $fieldType = array_pop(explode("=", $fieldType));
        if($label) {
            $content .= "label {\nvalue = $label\n}\n";
        }
        $content .= "}\n";
        return $content;
    }
    
    function convertTextarea($fieldName, $fieldType, $label, $value, $mandatory, $i)
    {
        /*
         * 30 = TEXTAREA
30 {
	cols = 40
	rows = 5
	name = address
	label {
		value = Address
	}
}
         */
        $content = "$i = TEXTAREA\n";
        $content .= "$i{\n";
        $fieldType = array_pop(explode("=", $fieldType));
        $fieldTypeArray= explode(",", $fieldType);
        if($fieldTypeArray[1]) {
            $content .= "cols = $fieldTypeArray[1]\n";
        }
        if($fieldTypeArray[2]) {
            $content .= "rows = $fieldTypeArray[2]\n";
        }
        $content .= "class = form-control\n";
        $content .= "name = $fieldName\n";
        if($mandatory===true) {
            $content .= "required = required\n";
        }
        if($label) {
            $content .= "label {\nvalue = $label\n}\n";
        }
        $content .= "}\n";
        
        return $content;
    }
    
    function convertCheck($fieldName, $fieldType, $label, $value, $mandatory, $i)
    {
        /*
         * 40 = CHECKBOX
40 {
	type = checkbox
	checked = checked
	name = tv
	label {
		value = Contact me
	}
}
         */
        $content = "$i = CHECKBOX\n";
        $content .= "$i{\n";
        $content .= "type = checkbox\n";
        if($value) {
            $content .= "checked = checked\n";
        }
        $content .= "name = $fieldName\n";
        
        if($mandatory===true) {
            $content .= "required = required\n";
        }
        if($label) {
            $content .= "label {\nvalue = $label\n}\n";
        }
        $content .= "}\n";
        return $content;
    }
    
    function convertRadio($fieldName, $fieldType, $label, $value, $mandatory, $i)
    {
        /*
         * Avslutning av: | *Avslutning_av=radio | Nyhetsbrevet, LTH-nytt i pappersformat
         */
        
        if($value) {
            $valueArray = explode(",", $value);
            $GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_devlog', array('msg' => count($valueArray), 'crdate' => time()));
            //if(count($valueArray) > 1) {
                $ii=0;
                $content = "$i = RADIOGROUP\n";
                $content .= "$i{\n";
                if($mandatory===true) {
                    $content .= "required = required\n";
                }
                if($label) {
                    $content .= "legend {\nvalue = $label\n}\n";
                }
                $content .= "name = $fieldName\n";
                foreach($valueArray as $vKey => $vValue) {
                    $ii=$ii+10;
                    $content .= "$ii = RADIO\n";
                    $content .= "$ii{\n";
                    $content .= "type = RADIO\n";
                    $content .= "value = $vValue\n";
                    $content .= "label {\nvalue = $vValue\n}\n";
                    $content .= "}\n";
                }
                $content .= "}\n";
            /*} else if(count($valueArray) === 1) {
                $content = "$i = RADIO\n";
                $content .= "$i{\n";
                $content .= "type = radio\n";
                if($mandatory===true) {
                    $content .= "required = required\n";
                }
                $content .= "name = $fieldName\n";
                if($mandatory===true) {
                    $content .= "required = required\n";
                }
                if($label) {
                    $content .= "label {\nvalue = $label\n}\n";
                }
                $content .= "}\n";
            }*/
        }
        return $content;
    }
    
    function convertSelect($fieldName, $fieldType, $label, $value, $mandatory, $i)
    {
        /*
         * 60 = SELECT
60 {
	multiple = multiple
	name = dropdown
	required = required
	size = 3
	label {
		value = Dropdown
	}
	10 = OPTION
	10 {
		text = Option 1
		selected = selected
		value = Value 1
	}
	20 = OPTION
	20 {
		text = Option 2
		value = Value 2
	}
	30 = OPTION
	30 {
		text = Option 3
		value = Value 3
	}
}
         * 
         * eeee | www=select,23,m | www, www, www

         */
        $content = "$i = SELECT\n";
        $content .= "$i{\n";
        $fieldType = array_pop(explode("=", $fieldType));
        $fieldTypeArray= explode(",", $fieldType);
        if($fieldTypeArray[2]==="m") {
            $content .= "multiple = multiple\n";
        }
        $content .= "name = $fieldName\n";
        if($mandatory===true) {
            $content .= "required = required\n";
        }
        if($fieldTypeArray[1]) {
            $content .= "size = $fieldTypeArray[1]\n";
        }
        if($label) {
            $content .= "label {\nvalue = $label\n}\n";
        }
        $valueArray = explode(",", $value);
        $ii=0;
        foreach($valueArray as $vKey => $vValue) {
            $ii=$ii+10;
            $content .= "$ii = OPTION\n";
            $content .= "$ii {\n";
            $content .= "text = $vValue\n";
            $content .= "value = $vValue\n";
            $content .= "}\n";
        }
        $content .= "}\n";
        return $content;
    }
}