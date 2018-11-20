<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Lth\Lthsolr\Task;

use TYPO3\CMS\Core\Utility\GeneralUtility;

class StatImport extends \TYPO3\CMS\Scheduler\Task\AbstractTask {
	
    function execute()
    {
        error_reporting(E_ALL ^ E_NOTICE);
        
	$executionSucceeded = FALSE;
        
        require(__DIR__.'/init.php');
                
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
        
        $executionSucceeded = $this->getLubas($settings, $syslang);
        
	$executionSucceeded = $this->getStat($config, $syslang);
        
        /*$syslang = "en";
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
        
        $executionSucceeded = $this->getStat($config, $syslang);*/
                
	return $executionSucceeded;
    }
    
    
    public function getLubas($settings, $syslang)
    {
        $lubasHostName = $settings['lubasHostName'];
        $lubasDatabase = $settings['lubasDatabase'];
        $lubasUserName = $settings['lubasUserName'];
        $lubasPw = $settings['lubasPw'];
        
        /*$query = "SELECT CO.offeringcode_intern, CO.course_offering_id, C.course_swe, C.department_id, C.credit, C.coursecode_intern, AR.admissionround_swe, C.course_id";
            $query .= " FROM course_offering CO INNER JOIN course C ON C.coursecode_intern = CO.coursecode_intern INNER JOIN admission_round AR ON AR.admissionround_id = CO.admissionround_id";
            $query .= " LEFT JOIN faculty_department FD ON C.department_id = FD.department_id";
            $query .= " LEFT JOIN faculty F ON F.faculty_id_intern = FD.faculty_id_intern";
            $query .= " WHERE F.faculty_id = 't'";
            $query .= " ORDER BY C.course_swe";*/
        $query = "SELECT program_swe FROM program";
            try {
                $pdo = new PDO("odbc:uwdbcluster05", "$sql_username", "$sql_password");
                $res = $pdo->query($query);
            } catch(Exception $e) {
                echo 'Message: ' .$e->getMessage();
            }
            while($lt = $res->fetch( PDO::FETCH_ASSOC )){ 
                /*$id = $lt["course_offering_id"];
                $course_swe = $lt["course_swe"];
                $credit = $lt["credit"];
                $coursecode_intern = $lt["coursecode_intern"];
                $omgang = urlencode($lt["admissionround_swe"]);
                $course_id = $lt["course_id"];
                $offeringcode_intern = $lt["offeringcode_intern"];*/
                echo $lt['program_swe'];
                //$content .= "<p class=\"newIconList3pil\"><a href=\"index.php?id=$single_page&courseid=$offeringcode_intern&application_open=$application_open&no_cache=1\">$course_swe ($course_id), $credit HP</a></p>";
            }
            //echo "<div style=\"margin-left:10px; clear:both;\">$content</div>";
            $pdo=null;
            die();
    }

    
    public function getStat($config, $syslang)
    {
        $client = new \Solarium\Client($config);
        $update = $client->createUpdate();
        $buffer = $client->getPlugin('bufferedadd');
        $buffer->setBufferSize(250);
        
        //tillfalleArray
        $tillfalleArray = array(array("Sokande","Total"), array("Urval1","Antagningspoang"), array("Urval2","Antagningspoang"));
        
        //Läs in antagningsomgångar
        $roundPath = "http://statistik.uhr.se/rest/stats/antagningsomgangar";
        $rounds = file_get_contents($roundPath);
        
        $dataArray = array();

        if($rounds) {
            $roundsArray = json_decode($rounds,true);
            foreach($roundsArray as $rkey => $rvalue) {
                foreach($tillfalleArray as $tkey => $tvalue) {
                    $statPath = "http://statistik.uhr.se/rest/stats/tableData?request=";
                    $statPath .= str_replace('%2B','+', urlencode(json_encode(array("tillfalle" => $tvalue[0],
                        "vy" => $tvalue[1],
                        "antagningsomgang" => $rvalue,
                        "larosateId" => "LU+",
                        "utbildningstyp" => "",
                        "fritextFilter" => "",
                        "urvalsGrupp" => "",
                        "firstResult" => 0,
                        "maxResults" => 25000,
                        "sorteringsKolumn" => 1,
                        "sorteringsOrdningDesc" => false,
                        "requestNumber" => 1,
                        "paginate" => true)))
                    );
/*
 * https://statistik.uhr.se/rest/stats/tableData?request={"tillfalle":"Sokande","vy":"Total","antagningsomgang":"HT2018","larosateId":"LU+","utbildningstyp":"","fritextFilter":"","urvalsGrupp":"","firstResult":0,"maxResults":25,"sorteringsKolumn":1,"sorteringsOrdningDesc":false,"requestNumber":1,"paginate":true}
 */
                    $stats = file_get_contents($statPath);

                    if($stats) {
                        $statArray = json_decode($stats,true);

                        foreach($statArray['aaData'] as $key => $vArray) {
                            if(is_array($vArray)) {
                                if($tvalue[0]==='Sokande') {
                                    /*
                                     *0 ["HT2018", 
                                     *1 "Kurs", 
                                     *2 "Astronomi: Introduktion till astrofysiken", 
                                     *3 "LU-10001", 
                                     *4 "Lunds universitet", 
                                     *5 39, 
                                     *6 1],
                                     */
                                    $dataArray[$vArray[3]][$vArray[0]]['statApplicants'] = implode(',',array($vArray[5],$vArray[6]));
                                } else if($tvalue[0]==='Urval1') {
                                    $dataArray[$vArray[3]][$vArray[0]]['statType']  = $vArray[1];
                                    $dataArray[$vArray[3]][$vArray[0]]['statTitle']  = $vArray[2];
                                    $dataArray[$vArray[3]][$vArray[0]]['statCode']  = $vArray[3];
                                    $dataArray[$vArray[3]][$vArray[0]]['statTermin']  = $vArray[0];
                                    $dataArray[$vArray[3]][$vArray[0]]['statUniv']  = $vArray[4];
                                    $dataArray[$vArray[3]][$vArray[0]]['statVal1'][] = implode(',',array($vArray[5],$vArray[6],$vArray[7],$vArray[8]));
                                } else if($tvalue[0]==='Urval2') {
                                    $dataArray[$vArray[3]][$vArray[0]]['statType']  = $vArray[1];
                                    $dataArray[$vArray[3]][$vArray[0]]['statTitle']  = $vArray[2];
                                    $dataArray[$vArray[3]][$vArray[0]]['statCode']  = $vArray[3];
                                    $dataArray[$vArray[3]][$vArray[0]]['statTermin']  = $vArray[0];
                                    $dataArray[$vArray[3]][$vArray[0]]['statUniv']  = $vArray[4];
                                    $dataArray[$vArray[3]][$vArray[0]]['statVal2'][] = implode(',',array($vArray[5],$vArray[6],$vArray[7],$vArray[8]));
                                }
                            }
                        }
                        
                    }
                    
                }
                
            }
            /*echo '<pre>';
                        print_r($dataArray);
                        echo '</pre>';
                        die();*/
            foreach($dataArray as $dkey => $dArray) {
                foreach($dArray as $dakey => $davalue) {
                    $data = array(
                        'appKey' => 'lth_solr',
                        'boost' => '1.0',
                        'createdDate' => date('Y-m-d\TH:i:s\Z', time()),
                        'docType' => 'stat',
                        'id' => 'stat_' . $davalue['statCode'] . '_' . $davalue['statTermin'],
                        'statApplicants' => $davalue['statApplicants'],
                        'statCode' => $davalue['statCode'],
                        'statTermin' => $davalue['statTermin'],
                        'statTitle' => $davalue['statTitle'],
                        'statType' => $davalue['statType'],
                        'statUniv' => $davalue['statUniv'],
                        'statVal1' => $davalue['statVal1'],
                        'statVal2' => $davalue['statVal2'],
                        'id' => 'stat_' . $davalue['statCode'] . '_' . $davalue['statTermin'],
                        'type' => 'stat'
                    );
                    try {
                        $buffer->createDocument($data);
                    } catch(Exception $e) {
                        echo 'Message: ' .$e->getMessage();
                    }
                }
            }
        }
        
        $buffer->commit();
        $update->addCommit();
        $client->update($update);

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
        $update->addDeleteQuery('docType:stat');
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
        $update->addDeleteQuery('docType:stat');
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

/*works: http://statistik.uhr.se/rest/stats/tableData?request=%7B%22tillfalle%22%3A%22Urval2%22%2C%22vy%22%3A%22Antagningspoang%22%2C%22antagningsomgang%22%3A%22HT2018%22%2C%22larosateId%22%3A%22LU+%22%2C%22utbildningstyp%22%3A%22%22%2C%22fritextFilter%22%3A%22%22%2C%22urvalsGrupp%22%3A%22%22%2C%22firstResult%22%3A0%2C%22maxResults%22%3A25%2C%22sorteringsKolumn%22%3A1%2C%22sorteringsOrdningDesc%22%3Afalse%2C%22requestNumber%22%3A1%2C%22paginate%22%3Atrue%7D
        my:    http://statistik.uhr.se/rest/stats/tableData?request=%7B%22tillfalle%22%3A%22Urval2%22%2C%22vy%22%3A%22Antagningspoang%22%2C%22antagningsomgang%22%3A%22HT2018%22%2C%22larosateId%22%3A%22LU+%22%2C%22utbildningstyp%22%3A%22%22%2C%22fritextFilter%22%3A%22%22%2C%22urvalsGrupp%22%3A%22%22%2C%22firstResult%22%3A0%2C%22maxResults%22%3A25%2C%22sorteringsKolumn%22%3A1%2C%22sorteringsOrdningDesc%22%3Afalse%2C%22requestNumber%22%3A1%2C%22paginate%22%3Atrue%7D
        $statPath = "http://statistik.uhr.se/rest/stats/tableData?request=";
        https://statistik.uhr.se/rest/stats/tableData?request=
        %7B%22tillfalle%22%3A%22Urval2%22%2C%22vy%22%3A%22Antagningspoang%22%2C%22antagningsomgang%22%3A%22HT2018%22%2C%22larosateId%22%3A%22LU+%22%2C
        %22utbildningstyp%22%3A%22%22%2C%22fritextFilter%22%3A%22%22%2C%22urvalsGrupp%22%3A%22%22%2C%22firstResult%22%3A0%2C%22maxResults%22%3A25%2C%22
        sorteringsKolumn%22%3A1%2C%22sorteringsOrdningDesc%22%3Afalse%2C%22requestNumber%22%3A1%2C%22paginate%22%3Atrue%7D
https://statistik.uhr.se/rest/stats/tableData?request={"tillfalle":"Sokande","vy":"Total","antagningsomgang":"HT2018","larosateId":"LU+","utbildningstyp":"","fritextFilter":"","urvalsGrupp":"","firstResult":0,"maxResults":25,"sorteringsKolumn":1,"sorteringsOrdningDesc":false,"requestNumber":1,"paginate":true}
 [3913] => Array
                (
                    [0] => HT2018
                    [1] => Program
                    [2] => Högskoleingenjörsutbildning i datateknik
                    [3] => LU-80008
                    [4] => Lunds universitet
                    [5] => BF
                    [6] => -
                    [7] => 0
                    [8] => 0
                )
 *        */