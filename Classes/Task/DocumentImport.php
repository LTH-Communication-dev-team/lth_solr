<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Lth\Lthsolr\Task;

use TYPO3\CMS\Core\Utility\GeneralUtility;

class DocumentImport extends \TYPO3\CMS\Scheduler\Task\AbstractTask {
	
    function execute()
    {
        error_reporting(E_ALL ^ E_NOTICE);
        
	$executionSucceeded = FALSE;
        
        require(__DIR__.'/init.php');
        $maximumrecords = 20;
        $numberofloops = 1;
        
        $syslang = "sv";
        
        $settings = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['lth_solr']);
        
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
        
        $client = new \Solarium\Client($config);

	$executionSucceeded = $this->getDocuments($client);
        
	return $executionSucceeded;
    }

    function getDocuments($client)
    {
        $uid;
        $bodytext;
        $url;
        $startPage = 0;
        
        $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery("uid,msg","tx_devlog","msg LIKE 'lth_solr_document_start_%'");
        $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
        $devUid = $row['uid'];
        $msg = $row['msg'];
        if($msg) {
            $startPage = (integer)array_pop(explode('_', $msg)) + 100;
            $GLOBALS['TYPO3_DB']->exec_UPDATEquery('tx_devlog', 'uid='.intval($devUid), array('msg' => 'lth_solr_document_start_' . (string)$startPage, 'crdate' => time()));
        } else {
            $GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_devlog', array('msg' => 'lth_solr_document_start_0', 'crdate' => time()));
        }
        
        $mimeArray = array(
            'application/pdf',
            'text/plain',
            'application/msword',
            'application/vnd.ms-powerpoint',
            'text/html',
            'application/vnd.ms-excel',
            'vnd.openxmlformats-officedocument.presentationml.presentation',
            'application/vnd.ms-office',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'text/rtf',
            'text/x-asm',
            'application/vnd.oasis.opendocument.text',
            'application/vnd.oasis.opendocument.presentation',
            'application/vnd.oasis.opendocument.spreadsheet'
        );
        
        /*$sql = "SELECT uid,identifier,NAME FROM sys_file WHERE mime_type IN('" . implode("','", $mimeArray) . "') AND FROM_UNIXTIME(tstamp) >= DATE_SUB(NOW(), INTERVAL 5 MINUTE) ORDER BY uid";
        $res = $GLOBALS['TYPO3_DB'] -> sql_query($sql);*/
        $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery("uid,identifier,name", "sys_file", "lth_solr_index = 0 AND identifier NOT LIKE '%.css' AND identifier NOT LIKE '%.js' AND identifier NOT LIKE '%/.htaccess/%' AND identifier NOT LIKE '%/template/%' AND mime_type IN('" . implode("','", $mimeArray) . "')","","size","$startPage,100");
        while ($row = $GLOBALS["TYPO3_DB"]->sql_fetch_assoc($res)) {
            $uid = $row['uid'];
            $identifier = $row['identifier'];
            $name = $row['name'];
            if($identifier && $name) {
                $this->extractDocument($client, $uid, $name, PATH_site . 'fileadmin' . $identifier);
            }
        }
        $GLOBALS['TYPO3_DB']->sql_free_result($res);
        return TRUE;
    }
       
    
    function extractDocument($client, $uid, $name, $filePath)
    {
        if(file_exists($filePath)) {
            //echo $filePath;
            // get an extract query instance and add settings
            $query = $client->createExtract();
            $query->addFieldMapping('content', 'content');
            $query->setUprefix('attr_');
            $query->setFile($filePath);
            $query->setCommit(true);
            $query->setOmitHeader(false);

            // add document
            $doc = $query->createDocument();
            $doc->id = "document$uid";
            $doc->title = $name;
            $doc->docType = 'document';
            $doc->url = $filePath;
            $doc->type = 'document';
            $doc->appKey = 'lthsolr';
            $query->setDocument($doc);

            // this executes the query and returns the result
            try {
                $result = $client->extract($query);
                $GLOBALS['TYPO3_DB']->exec_UPDATEquery('sys_file', 'uid='.intval($uid), array('lth_solr_index' => 1));
            } catch(Exception $e) {
                $GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_devlog', array('msg' => $filePath, 'crdate' => time()));
            }
        }
    }
}