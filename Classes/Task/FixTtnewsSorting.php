<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Lth\Lthsolr\Task;

use TYPO3\CMS\Core\Utility\GeneralUtility;

class FixTtnewsSorting extends \TYPO3\CMS\Scheduler\Task\AbstractTask {
	
    function execute()
    {
        error_reporting(E_ALL ^ E_NOTICE);
        
	$executionSucceeded = FALSE;

	$executionSucceeded = $this->fixTtnewsSorting();
        
	return $executionSucceeded;
    }

    function fixTtnewsSorting()
    {
       
        $i=0;
        $oldPid = 0;
        $newSorting = 0;
        $sql = 'SELECT uid,pid FROM tt_news WHERE deleted=0 ORDER BY pid, DATETIME DESC';
        $res = $GLOBALS['TYPO3_DB'] -> sql_query($sql);
        while ($row = $GLOBALS["TYPO3_DB"]->sql_fetch_assoc($res)) {
            $uid = $row['uid'];
            $pid = $row['pid'];
            if($pid != $oldPid) {
                $newSorting = 0;
            } else {
                $newSorting = $newSorting + 32;
            }
            $GLOBALS['TYPO3_DB']->exec_UPDATEquery('tt_news', 'uid='.intval($uid), array('sorting' => $newSorting));
            $oldPid = $pid;
        }
        $GLOBALS['TYPO3_DB']->sql_free_result($res);
        return TRUE;
    }   
}