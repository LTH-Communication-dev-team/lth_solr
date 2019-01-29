<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Lth\Lthsolr\Task;

use TYPO3\CMS\Core\Utility\GeneralUtility;

class FixTxnewsMedia extends \TYPO3\CMS\Scheduler\Task\AbstractTask {
	
    function execute()
    {
        error_reporting(E_ALL ^ E_NOTICE);
        
        $settings = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['lth_solr']);
        
        $nrsp = $settings['nrsp'];
        
        if(!$nrsp) {
            return FALSE;
        }
        
	$executionSucceeded = FALSE;

	$executionSucceeded = $this->fixTxnewsMedia($nrsp);
        
	return $executionSucceeded;
    }

    function fixTxnewsMedia($nrsp)
    {
       
        $i=0;
        $oldPid = 0;
        $newSorting = 0;
        //$GLOBALS['TYPO3_DB']->store_lastBuiltQuery = 1;
        $sql = "SELECT N.uid AS nuid, S.uid AS suid FROM tx_news_domain_model_news N JOIN sys_file_reference S ON N.import_id = S.uid_foreign 
            JOIN sys_file SF ON S.uid_local = SF.uid WHERE N.deleted=0 AND N.pid = " . intval($nrsp) . " ORDER BY S.uid DESC";
        $res = $GLOBALS['TYPO3_DB'] -> sql_query($sql);
        while ($row = $GLOBALS["TYPO3_DB"]->sql_fetch_assoc($res)) {
            //die($sql);
            $nuid = $row['nuid'];
            $suid = $row['suid'];

            $sql1 = "INSERT INTO sys_file_reference (pid, tstamp, crdate, cruser_id, sorting, deleted, hidden, t3ver_oid, t3ver_id, t3ver_wsid, t3ver_label,
                t3ver_state, t3ver_stage, t3ver_count, t3ver_tstamp, t3ver_move_id, t3_origuid, sys_language_uid, l10n_parent, l10n_diffsource)
                SELECT pid, tstamp, crdate, cruser_id, sorting, deleted, hidden, t3ver_oid, t3ver_id, t3ver_wsid, t3ver_label,
                t3ver_state, t3ver_stage, t3ver_count, t3ver_tstamp, t3ver_move_id, t3_origuid, sys_language_uid, l10n_parent, l10n_diffsource 
                FROM sys_file_reference
                WHERE uid = $suid";
            $res1 = $GLOBALS['TYPO3_DB'] -> sql_query($sql1);
            $suid_novo = $GLOBALS['TYPO3_DB']->sql_insert_id();
            
            $GLOBALS['TYPO3_DB']->exec_UPDATEquery('sys_file_reference', 'uid='.$suid_novo, array('tablenames' => 'tx_news_domain_model_news',
                'fieldname' => 'fal_media',
                'uid_foreign' => $nuid));
            //UPDATE tx_news_domain_model_news SET fal_media = 1 WHERE uid = 6528 (N.uid);
            $GLOBALS['TYPO3_DB']->exec_UPDATEquery('tx_news_domain_model_news', 'uid='.$nuid, array('fal_media' => 1));
            //$GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_devlog', array('msg' => $GLOBALS['TYPO3_DB']->debug_lastBuiltQuery, 'crdate' => time()));
        }
        $GLOBALS['TYPO3_DB']->sql_free_result($res);
        //die($sql);
        return TRUE;
    }   
}