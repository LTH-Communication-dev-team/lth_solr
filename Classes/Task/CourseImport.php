<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Lth\Lthsolr\Task;

use TYPO3\CMS\Core\Utility\GeneralUtility;

class CourseImport extends \TYPO3\CMS\Scheduler\Task\AbstractTask {
	
    function execute()
    {
        error_reporting(E_ALL ^ E_NOTICE);
        
	$executionSucceeded = FALSE;
        
        require(__DIR__.'/init.php');
        $maximumrecords = 20;
        $numberofloops = 1;
        
        $settings = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['lth_solr']);
        
        $config = array(
            'endpoint' => array(
                'localhost' => array(
                    'host' => $settings['solrHost'],
                    'port' => $settings['solrPort'],
                    'path' => $settings['solrPath'],
                    'timeout' => $settings['solrTimeout']
                )
            )
        );
        
        $client = new \Solarium\Client($config);

	$executionSucceeded = $this->getCourses($client);
        
	return $executionSucceeded;
    }

    public function getCourses($client)
    {
        $buffer = $client->getPlugin('bufferedadd');
        $buffer->setBufferSize(250);
                
        $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery("K.KursID, K.KursSve, K.KursEng, LCASE(K.Kurskod) AS Kurskod, K.Hskpoang, KI.Webbsida", "LubasPP_dbo.Kurs K JOIN LubasPP_dbo.KursInfo KI ON K.KursID = KI.KursFK", "K.KursID", "K.KursID", "", "");
        while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
            $KursID = $row['KursID'];
            $KursSve = $row['KursSve'];
            $KursEng = $row['KursEng'];
            $kurskod = $row['Kurskod'];
            $Hskpoang = $row['Hskpoang'];
            $Webbsida = $row['Webbsida'];
            $data = array(
                'id' => 'course_' . $row['KursID'],
                'doctype' => 'course',
                'title_sv' =>  $row['KursSve'],
                'title_en' =>  $row['KursEng'],
                'course_code' => $row['Kurskod'],
                'credit' => $row['Hskpoang'],
                'url' => $row['Webbsida'],
                'boost' => '1.0'
            );
            try {
                $buffer->createDocument($data);
            } catch(Exception $e) {
                echo 'Message: ' .$e->getMessage();
            }
        }
        $GLOBALS['TYPO3_DB']->sql_free_result($res);
        $buffer->commit();
        return TRUE;
    }
}