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
        $valfrihetsgradsArray = array('obligatorisk' => 0,'alternativ_obligatorisk' => 1,'valfri' => 2,'externt_valfri' => 3);
        $client = new \Solarium\Client($config);
        $update = $client->createUpdate();
        $buffer = $client->getPlugin('bufferedadd');
        $buffer->setBufferSize(250);
        //$GLOBALS['TYPO3_DB']->store_lastBuiltQuery = 1;
        $sql = "SELECT K.KursID, K.KursSve, K.KursEng, LCASE(K.Kurskod) AS Kurskod, K.Hskpoang, K.Betygskala, K.startDatum, K.slutDatum, K.kurstypTyp,
            KI.Webbsida,
            P.ProgramID, P.ProgramSve, P.ProgramEng, P.ProgramKod, 
            L.LasesFran, LCASE(L.Valfrihetsgrad) AS Valfrihetsgrad, 
            I.InriktningSve, I.InriktningID, I.Allman AS InriktningAllman,
            PO.Omgang, PO.PlanOmgangID, 
            LA.Arskurser,
            GROUP_CONCAT(DISTINCT REPLACE(LI.Forfattare,'|','') SEPARATOR '|') AS Forfattare,
            GROUP_CONCAT(DISTINCT REPLACE(LI.Forlag,'|','') SEPARATOR '|') AS Forlag,
            GROUP_CONCAT(DISTINCT REPLACE(LI.ISBN,'|','') SEPARATOR '|') AS ISBN,
            GROUP_CONCAT(DISTINCT REPLACE(LI.Titel,'|','') SEPARATOR '|') AS Titel,
            GROUP_CONCAT(DISTINCT REPLACE(LI.Undertitel,'|','') SEPARATOR '|') AS Undertitel,
            GROUP_CONCAT(DISTINCT REPLACE(LI.Utgivningsar,'|','') SEPARATOR '|') AS Utgivningsar,
            A.AvdelningSve, 
            A.AvdelningEng, A.Id AS AvdelningId, 
            KO.kursOrtSve, KO.kursOrtEng, 
            KT.kursTaktSve, KT.kursTaktEng,
            KI.ForkunKrav,
            KI.Innehall,
            KI.LarandeMal1,
            KI.LarandeMal2,
            KI.LarandeMal3,
            KI.Ovrigt,
            KI.Prestationbed,
            KI.syfte,
            KI.Urval
            FROM LubasPP_dbo.Kurs K 
            JOIN LubasPP_dbo.KursInfo KI ON K.KursID = KI.KursFK AND KI.Sprak = '$syslang'
            JOIN LubasPP_dbo.Kurs_Program KP ON K.KursID = KP.KursFK
            LEFT JOIN LubasPP_dbo.Program P ON P.ProgramID = KP.ProgramFK
            LEFT JOIN LubasPP_dbo.Laroplan L ON L.KursProgramFK = KP.KursProgramID
            LEFT JOIN LubasPP_dbo.Laroplan_Arskurser LA ON L.LaroplanID = LA.LaroplanFK
            LEFT JOIN LubasPP_dbo.Inriktning I ON I.InriktningID = L.InriktningFK
            JOIN LubasPP_dbo.PlanOmgang PO ON K.PlanOmgangFK = PO.PlanOmgangID
            LEFT JOIN LubasPP_dbo.Litteratur LI ON LI.KursFK = K.KursID
            LEFT JOIN LubasPP_dbo.Avdelning A ON K.AvdelningFK = A.Id
            LEFT JOIN LubasPP_dbo.KursOrt KO ON K.kusrOrtFK = KO.Id
            LEFT JOIN LubasPP_dbo.KursTakt KT ON K.kursTaktFK = KT.Id
            WHERE K.Nedlagd = 0 AND K.Kurskod NOT LIKE '%??%' AND KP.UtbStatusProg != 'NERLAGD' AND P.Nedlagd = 0
            GROUP BY K.KursID
            ORDER BY P.ProgramKod, PO.PlanOmgangID, LA.Arskurser, I.InriktningID, L.Valfrihetsgrad, K.KursID";
        
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
            $InriktningAllman = $row['InriktningAllman'];
            $InriktningEng = $row['InriktningEng'];
            $InriktningID = $row['InriktningID'];
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
            $kurstypTyp = $row['kurstypTyp'];
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
            $slutDatum = $row['slutDatum'];
            $Sprak = $row['Sprak'];
            $startDatum = $row['startDatum'];
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
            $abstract .= $this->langChoice(array("<h3>Syfte</h3>","<h3>Aim</h3>"),$syslang);
            $abstract .= $syfte;
            $abstract .= $this->langChoice(array("<h3>Mål</h3>", "<h3>Learning outcomes</h3>"), $syslang);
            $abstract .= $LarandeMal1;
            $abstract .= $LarandeMal2;
            $abstract .= $LarandeMal3;
            $abstract .= $this->langChoice(array("<h3>Kursinnehåll</h3>", "<h3>Contents</h3>"), $syslang);
            $abstract .= $Innehall;
            $abstract .= $this->langChoice(array("<h3>Kursens examination</h3>", "<h3>Examination details</h3>"), $syslang);
            $abstract .= "<p><b>Betygsskala:</b> $Betygskala<p>";
            $abstract .= "<p><b>Prestationsbedömning:</b> $Prestationbed<p>";
            if(is_array($Titel)) {
                $abstract .= $this->langChoice(array("<h3>Litteratur</h3>","<h3>Reading list</h3>"), $syslang);
                $abstract .= '<ul>';
                foreach($Titel as $value) {
                   $abstract .= '<li>' . $Forfattare[$i] . ': ' . $Titel[$i] . $this->addComma($Forlag[$i]) . $this->addComma($Utgivningsar[$i]) . $this->addComma($ISBN[$i]) . '</li>';
                   $i++;
                }
                $abstract .= '</ul>';
            }
            
            $courseSelectionTemp = $this->getCourseType($kurstypTyp,$Valfrihetsgrad,$this->langChoice(array($InriktningSve, $InriktningEng), $syslang),$InriktningAllman);
            $courseSelection = $courseSelectionTemp[0];
            $courseSelectionSort = $courseSelectionTemp[1];

            $data = array(
                'abstract' => $abstract,
                'department' =>  $this->langChoice(array($AvdelningSve, $AvdelningEng), $syslang),
                'departmentId' =>  $avdelningId,
                'id' => 'course_' . $ProgramKod  . '_' . $PlanOmgangID . '_' .$Arskurser . '_' . $InriktningID . '_' . $Valfrihetsgrad . '_' . $KursID,
                'courseCode' => $Kurskod,
                'courseForkunKrav' => $ForkunKrav,
                'coursePace' => $this->langChoice(array($kursTaktSve, $kursTaktEng), $syslang),
                'coursePlace' => $this->langChoice(array($kursOrtSve, $kursOrtEng), $syslang),
                'courseSelection' => $courseSelection,
                'courseSelectionSort' => $courseSelectionSort,
                'courseSlutDatum' => date('Y-m-d\TH:i:s\Z', strtotime($slutDatum)),
                'courseStartDatum' => date('Y-m-d\TH:i:s\Z', strtotime($startDatum)),
                'courseTitle' => $this->langChoice(array($KursSve, $KursEng), $syslang),
                'courseType' => $kurstypTyp,
                'courseUrval' => $Urval,
                'courseYear' =>  $Arskurser,
                'credit' => $Hskpoang,
                'homepage' => $Webbsida,
                'optional' => $Valfrihetsgrad,
                'programCode' => $ProgramKod,
                'programDirection' => $this->langChoice(array($InriktningSve, $InriktningEng), $syslang),
                'programDirectionGeneral' => $InriktningAllman,
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
            if($Valfrihetsgrad) $data['optionalSort'] = $valfrihetsgradsArray[$Valfrihetsgrad];
            //try {
            $buffer->createDocument($data);
            //} catch(Exception $e) {
            //    echo 'Message: ' .$e->getMessage();
            //}
            /*echo '<pre>';
            print_r($data);
            echo '</pre>';
            die();*/
        }
        $GLOBALS['TYPO3_DB']->sql_free_result($res);
        $buffer->commit();
        $update->addCommit();
        $client->update($update);
        return TRUE;
    }
    
    
    public function getCourseType($courseType,$optional,$programDirection,$programDirectionGeneral)
    {
        $output='';
        $outputSort = 0;
        if($optional==='obligatorisk') {
            $output = 'Obligatoriska kurser';
            $outputSort = 0;
        } else if($optional==='alternativ_obligatorisk') {
            $output = 'Alternativobligatoriska kurser';
            $outputSort = 1;
        } else if($programDirectionGeneral==0) {
            $output = 'Specialisering - ' . $programDirection;
            $outputSort = 2;
        } else if($courseType==='EXAMENSARBETE') {
            $output = 'Examensarbeten';
            $outputSort = 5;
        } else if($optional === 'externt_valfri') {
            $output = 'Externt valfria kurser';
            $outputSort = 4;
        } else if($optional === 'valfri') {
            $output = 'Valfria kurser';
            $outputSort = 3;
        }
        return array($output,$outputSort);
        //$output = Examensarbeten / Externt valfria kurser / Valfria kurser / Specialisering p - Processdesign / Obligatoriska kurser / Alternativobligatoriska kurser
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