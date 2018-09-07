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
        
        $executionSucceeded = $this->clearIndex($settings);
        
        $syslang = "sv";
        
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

	$executionSucceeded = $this->getCourses($client, $syslang);
        
        $syslang = "en";
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
        
        $executionSucceeded = $this->getCourses($client, $syslang);
        
        //$executionSucceeded = $this->getPrograms($client, $syslang);
        
	return $executionSucceeded;
    }

    
    public function getCourses($client, $syslang)
    {
        $buffer = $client->getPlugin('bufferedadd');
        $buffer->setBufferSize(250);
        //$GLOBALS['TYPO3_DB']->store_lastBuiltQuery = 1;
        $sql = "SELECT K.KursID, K.KursSve, K.KursEng, LCASE(K.Kurskod) AS Kurskod, K.Hskpoang, K.Betygskala, KI.Webbsida,
            P.ProgramID, P.ProgramEng, P.ProgramSve, P.ProgramKod, L.LasesFran, LCASE(L.Valfrihetsgrad) AS Valfrihetsgrad, I.InriktningSve, I.InriktningEng, 
            PO.Omgang, LA.Arskurser
            FROM LubasPP_dbo.Kurs K 
            JOIN LubasPP_dbo.KursInfo KI ON K.KursID = KI.KursFK
            JOIN LubasPP_dbo.Kurs_Program KP ON K.KursID = KP.KursFK
            JOIN LubasPP_dbo.Program P ON P.ProgramID = KP.ProgramFK
            JOIN LubasPP_dbo.Laroplan L ON L.KursProgramFK = KP.KursProgramID
            JOIN LubasPP_dbo.Laroplan_Arskurser LA ON L.LaroplanID = LA.LaroplanFK
            JOIN LubasPP_dbo.Inriktning I ON I.InriktningID = L.InriktningFK
            JOIN LubasPP_dbo.PlanOmgang PO ON K.PlanOmgangFK = PO.PlanOmgangID
            WHERE PO.PlanOmgangID = 29 AND K.Nedlagd = 0 AND P.Nedlagd = 0 AND K.Kurskod NOT LIKE '%??%'
            GROUP BY K.KursID, PO.PlanOmgangID
            ORDER BY P.ProgramId, LA.Arskurser, K.KursSve";
        $res = $GLOBALS['TYPO3_DB'] -> sql_query($sql);
        
        //$GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_devlog', array('msg' => $GLOBALS['TYPO3_DB']->debug_lastBuiltQuery, 'crdate' => time()));
        while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
            $Arskurser = $row['Arskurser'];
            $Betygskala = $row['Betygskala'];
            $Hskpoang = $row['Hskpoang'];
            $InriktningEng = $row['InriktningEng'];
            $InriktningSve = $row['InriktningSve'];
            $KursID = $row['KursID'];
            $KursEng = $row['KursEng'];
            $KursID = $row['KursID'];
            $kurskod = $row['Kurskod'];
            $KursSve = $row['KursSve'];
            $LasesFran = $row['LasesFran'];
            $Omgang = $row['Omgang'];
            $ProgramID = $row['ProgramID'];
            $ProgramKod = $row['ProgramKod'];
            $ProgramEng = $row['ProgramEng'];
            $ProgramSve = $row['ProgramSve'];
            $Valfrihetsgrad = $row['Valfrihetsgrad'];
            $Webbsida = $row['Webbsida'];
            
            $data = array(                
                'id' => 'course_' . $KursID,
                'courseCode' => $Kurskod,
                'courseTitle' =>  $this->titleChoice(array($KursSve, $KursEng), $syslang),
                'courseYear' =>  $Arskurser,
                'credit' => $Hskpoang,
                'homepage' => $Webbsida,
                'optional' => $Valfrihetsgrad,
                'programCode' => $ProgramKod,
                'programDirection' => $this->titleChoice(array($InriktningSve, $InriktningEng), $syslang),
                'programTitle' => $this->titleChoice(array($ProgramSve, $ProgramEng), $syslang),
                'ratingScale' => $Betygskala,
                'round' => $Omgang,
                'docType' => 'course',
                'appKey' => 'lth_solr',
                'boost' => '1.0',
                'type' => 'course'
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
    
    
    public function getPrograms($client, $syslang)
    {
        $buffer = $client->getPlugin('bufferedadd');
        $buffer->setBufferSize(250);
        //$GLOBALS['TYPO3_DB']->store_lastBuiltQuery = 1;
        $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery("P.ProgramID, P.ProgramEng, P.ProgramSve, P.ProgramKod, K.kursOrtEng, kursOrtSve", 
                "LubasPP_dbo.Program P JOIN LubasPP_dbo.KursOrt K ON kursOrtKod = Ort", "P.Nedlagd = 0", "", "", "");
        //$GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_devlog', array('msg' => $GLOBALS['TYPO3_DB']->debug_lastBuiltQuery, 'crdate' => time()));
        while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
            $data = array(
                'appKey' => 'lth_solr',
                'type' => 'program',
                'id' => 'program_' . $row['ProgramID'],
                'docType' => 'program',
                'title' =>  $this->titleChoice(array($row['ProgramSve'], $row['ProgramEng']), $syslang),
                'courseCode' => $row['ProgramKod'],
                'courseLocation' =>  $this->titleChoice(array($row['kursOrtSve'], $row['kursOrtEng']), $syslang),
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
    
    
    function clearIndex($settings)
    {
        $syslang = "sv";
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
        $update = $client->createUpdate();
        $update->addDeleteQuery('docType:course');
        $update->addCommit();
        $result = $client->update($update);
        
        $syslang = "en";
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
        $update = $client->createUpdate();
        $update->addDeleteQuery('docType:course');
        $update->addCommit();
        $result = $client->update($update);
        
        return TRUE;
    }
    
    
    function titleChoice($titleArray, $syslang)
    {
        if($syslang === "sv") {
            return $titleArray[0];
        } else {
            return $titleArray[1];
        }
    }
}