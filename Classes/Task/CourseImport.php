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
        
        //$executionSucceeded = $this->clearIndex($settings);
        
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
        
	$executionSucceeded = $this->getCourses($config, $syslang);
        
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
        
        $executionSucceeded = $this->getCourses($config, $syslang);
        
        //$executionSucceeded = $this->getPrograms($client, $syslang);
        
	return $executionSucceeded;
    }

    
    public function getCourses($config, $syslang)
    {
        $client = new \Solarium\Client($config);
        $update = $client->createUpdate();
        $buffer = $client->getPlugin('bufferedadd');
        $buffer->setBufferSize(250);
        //$GLOBALS['TYPO3_DB']->store_lastBuiltQuery = 1;
        $sql = "SELECT K.KursID, K.KursSve, K.KursEng, LCASE(K.Kurskod) AS Kurskod, K.Hskpoang, K.Betygskala, KI.Webbsida, 
            GROUP_CONCAT(REPLACE(KI.ForkunKrav,'|','') SEPARATOR '|') AS ForkunKrav,
            GROUP_CONCAT(REPLACE(KI.Innehall,'|','') SEPARATOR '|') AS Innehall,
            GROUP_CONCAT(REPLACE(KI.LarandeMal1,'|','') SEPARATOR '|') AS LarandeMal1,
            GROUP_CONCAT(REPLACE(KI.LarandeMal2,'|','') SEPARATOR '|') AS LarandeMal2,
            GROUP_CONCAT(REPLACE(KI.LarandeMal3,'|','') SEPARATOR '|') AS LarandeMal3,
            GROUP_CONCAT(REPLACE(KI.Ovrigt,'|','') SEPARATOR '|') AS Ovrigt,
            GROUP_CONCAT(REPLACE(KI.Prestationbed,'|','') SEPARATOR '|') AS Prestationbed,
            GROUP_CONCAT(REPLACE(KI.syfte,'|','') SEPARATOR '|') AS syfte,
            GROUP_CONCAT(REPLACE(KI.Urval,'|','') SEPARATOR '|') AS Urval,
            P.ProgramID, P.ProgramSve, P.ProgramEng, P.ProgramKod, L.LasesFran, LCASE(L.Valfrihetsgrad) AS Valfrihetsgrad, I.InriktningSve, PO.Omgang, PO.PlanOmgangID,
            LA.Arskurser, LI.FriText_en, LI.FriText_sv, A.AvdelningSve, A.AvdelningEng, A.Id AS AvdelningId, KO.kursOrtSve, KO.kursOrtEng, KT.kursTaktSve, KT.kursTaktEng,
            GROUP_CONCAT(REPLACE(LI.Forfattare,'|','') SEPARATOR '|') AS Forfattare,
            GROUP_CONCAT(REPLACE(LI.Forlag,'|','') SEPARATOR '|') AS Forlag,
            GROUP_CONCAT(REPLACE(LI.ISBN,'|','') SEPARATOR '|') AS ISBN,
            GROUP_CONCAT(REPLACE(LI.Titel,'|','') SEPARATOR '|') AS Titel,
            GROUP_CONCAT(REPLACE(LI.Undertitel,'|','') SEPARATOR '|') AS Undertitel,
            GROUP_CONCAT(REPLACE(LI.Utgivningsar,'|','') SEPARATOR '|') AS Utgivningsar
            FROM LubasPP_dbo.Kurs K 
            JOIN LubasPP_dbo.KursInfo KI ON K.KursID = KI.KursFK AND K.Nedlagd = 0
            JOIN LubasPP_dbo.Kurs_Program KP ON K.KursID = KP.KursFK
            LEFT JOIN LubasPP_dbo.Program P ON P.ProgramID = KP.ProgramFK AND P.Nedlagd = 0
            LEFT JOIN LubasPP_dbo.Laroplan L ON L.KursProgramFK = KP.KursProgramID
            LEFT JOIN LubasPP_dbo.Laroplan_Arskurser LA ON L.LaroplanID = LA.LaroplanFK
            LEFT JOIN LubasPP_dbo.Inriktning I ON I.InriktningID = L.InriktningFK
            LEFT JOIN LubasPP_dbo.PlanOmgang PO ON K.PlanOmgangFK = PO.PlanOmgangID
            LEFT JOIN LubasPP_dbo.Litteratur LI ON LI.KursFK = K.KursID
            LEFT JOIN LubasPP_dbo.Avdelning A ON K.AvdelningFK = A.Id
            LEFT JOIN LubasPP_dbo.KursOrt KO ON K.kusrOrtFK = KO.Id
            LEFT JOIN LubasPP_dbo.KursTakt KT ON K.kursTaktFK = KT.Id
            WHERE K.Kurskod NOT LIKE '%??%'
            GROUP BY P.ProgramKod, K.KursID, PO.PlanOmgangID
            HAVING PO.PlanOmgangID=29 AND P.`ProgramKod`='F'
            ORDER BY P.ProgramKod, PO.PlanOmgangID, K.KursId";
        $res = $GLOBALS['TYPO3_DB'] -> sql_query($sql);
        //$ProgramKod . '_' . $PlanOmgangID . '_' . $KursID
        while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
            $AvdelningSve = $row['AvdelningSve'];
            $AvdelningEng = $row['AvdelningEng'];
            $AvdelningId = $row['AvdelningId'];
            $Arskurser = $row['Arskurser'];
            $Betygskala = $row['Betygskala'];
            $Forfattare = explode('|', $row['Forfattare']);
            $ForkunKrav = $row['ForkunKrav'];
            $Forlag = explode('|', $row['Forlag']);
            $Hskpoang = $row['Hskpoang'];
            $Innehall = $this->langChoice(explode('|', $row['Innehall']),$syslang);
            $InriktningEng = $row['InriktningEng'];
            $InriktningSve = $row['InriktningSve'];
            $ISBN = explode('|', $row['ISBN']);
            $KursID = $row['KursID'];
            $KursEng = $row['KursEng'];
            $KursID = $row['KursID'];
            $Kurskod = $row['Kurskod'];
            $kursOrtSve = $row['kursOrtSve'];
            $kursOrtEng = $row['kursOrtEng'];
            $KursSve = $row['KursSve'];
            $kursTaktSve = $row['kursTaktSve'];
            $kursTaktEng = $row['kursTaktEng'];
            $LarandeMal1 = $this->langChoice(explode('|', $row['LarandeMal1']),$syslang);
            $LarandeMal2 = $this->langChoice(explode('|', $row['LarandeMal2']),$syslang);
            $LarandeMal3 = $this->langChoice(explode('|', $row['LarandeMal3']),$syslang);
            $LasesFran = $row['LasesFran'];
            $Omgang = $row['Omgang'];
            $Ovrigt = $row['Ovrigt'];
            $PlanOmgangID = $row['PlanOmgangID'];
            $Prestationbed = $this->langChoice(explode('|', $row['Prestationbed']),$syslang);
            $ProgramID = $row['ProgramID'];
            $ProgramKod = $row['ProgramKod'];
            $ProgramEng = $row['ProgramEng'];
            $ProgramSve = $row['ProgramSve'];
            $Sprak = $row['Sprak'];
            $syfte = $this->langChoice(explode('|', $row['syfte']),$syslang);
            $Titel = explode('|', $row['Titel']);
            $Undertitel = explode('|', $row['Undertitel']);
            $Urval = $row['Urval'];
            $Utgivningsar = explode('|', $row['Utgivningsar']);
            $Valfrihetsgrad = $row['Valfrihetsgrad'];
            $Webbsida = $row['Webbsida'];
            //Build abstract
            $abstract = '';
            $i=0;
            $abstract .= $syfte;
            $abstract .= $LarandeMal1;
            $abstract .= $LarandeMal2;
            $abstract .= $LarandeMal3;
            $abstract .= $Innehall;
            $abstract .= $Betygskala;
            $abstract .= $Prestationbed;
            if(is_array($Titel)) {
                $abstract .= '<ul>';
                foreach($Titel as $value) {
                   $abstract .= '<li>' . $Forfattare[$i] . ': ' . $Titel[$i] . $this->addComma($Forlag[$i]) . $this->addComma($Utgivningsar[$i]) . $this->addComma($ISBN[$i]) . '</li>';
                   $i++;
                }
                $abstract .= '</ul>';
            }

            $data = array(
                'abstract' => $abstract,
                'department' =>  $this->langChoice(array($AvdelningSve, $AvdelningEng), $syslang),
                'departmentId' =>  $avdelningId,
                'id' => 'course_' . $ProgramKod . '_' . $PlanOmgangID . '_' . $KursID,
                'courseCode' => $Kurskod,
                'coursePace' => $this->langChoice(array($kursTaktSve, $kursTaktEng), $syslang),
                'coursePlace' => $this->langChoice(array($kursOrtSve, $kursOrtEng), $syslang),
                'courseTitle' => $this->langChoice(array($KursSve, $KursEng), $syslang),
                'courseYear' =>  $Arskurser,
                'credit' => $Hskpoang,
                'homepage' => $Webbsida,
                'optional' => $Valfrihetsgrad,
                'programCode' => $ProgramKod,
                'programDirection' => $this->langChoice(array($InriktningSve, $InriktningEng), $syslang),
                'programTitle' => $this->langChoice(array($ProgramSve, $ProgramEng), $syslang),
                'ratingScale' => $Betygskala,
                'round' => $Omgang,
                'roundId' => $PlanOmgangID,
                'roundAndRoundId' => $PlanOmgangID . '____' . $Omgang,
                'docType' => 'course',
                'appKey' => 'lth_solr',
                'boost' => '1.0',
                'type' => 'course'
            );
            //try {
                $buffer->createDocument($data);
                $buffer->commit();
        $update->addCommit();
        $client->update($update);
            //} catch(Exception $e) {
            //    echo 'Message: ' .$e->getMessage();
            //}
            echo '<pre>';
            print_r($data);
            echo '</pre>';
            die();
        }
        $GLOBALS['TYPO3_DB']->sql_free_result($res);
        //$buffer->commit();
        //$update->addCommit();
        //$client->update($update);
        return TRUE;
    }
    
    public function addComma($input)
    {
        if($input) {
            $input .= ", " + $input;
        }
        return $input;
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
                'title' =>  $this->langChoice(array($row['ProgramSve'], $row['ProgramEng']), $syslang),
                'courseCode' => $row['ProgramKod'],
                'courseLocation' =>  $this->langChoice(array($row['kursOrtSve'], $row['kursOrtEng']), $syslang),
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
    
    
    function langChoice($inputArray, $syslang)
    {
        if($syslang === "sv") {
            return $inputArray[0];
        } else {
            return $inputArray[1];
        }
    }
}