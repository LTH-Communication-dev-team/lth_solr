<?php
class tx_lthsolr_lucris_adduuid extends tx_scheduler_Task {

	//http://portal.research.lu.se/ws/rest/organisation?typeClassificationUris.uri=/dk/atira/pure/organisation/organisationtypes/organisation/researchteam
    function execute()
    {
        error_reporting(E_ALL ^ E_NOTICE);

	$executionSucceeded = FALSE;

	$executionSucceeded = $this->indexItems();
        return $executionSucceeded;
    }

    function indexItems()
    {
        require(__DIR__.'/init.php');
        $maximumrecords = 150;
        $numberofloops = 1;
        $current_date = gmDate("Y-m-d\TH:i:s\Z");
        
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
        
        $dbhost = $settings['dbhost'];
        $db = $settings['db'];
        $user = $settings['user'];
        $pw = $settings['pw'];
        
        $con = mysqli_connect($dbhost, $user, $pw, $db) or die("40; ".mysqli_error());
    
	if (!$settings['solrHost'] || !$settings['solrPort'] || !$settings['solrPath'] || !$settings['solrTimeout']) {
	    return 'Please make all settings in extension manager';
	}

        // create a client instance
        $client = new Solarium\Client($config);
        
        $i=0;
        $startrecord = 0;
        
        // get an update query instance
        $update = $client->createUpdate();

        for($i = 0; $i < $numberofloops; $i++) {
            
            $startrecord = $i * $maximumrecords;
            if($startrecord > 0) $startrecord++;

            $xmlpath = "http://portal.research.lu.se/ws/rest/person?window.size=$maximumrecords&window.offset=$startrecord&orderBy.property=id";

            try {
                //$GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_devlog', array('msg' => '200: ' . $xmlpath, 'crdate' => time()));
                $xml = new SimpleXMLElement($xmlpath, null, true);
                
            } catch(Exception $e) {
                $GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_devlog', array('msg' => '500: ' . $xmlpath, 'crdate' => time()));
            }
            if($xml->children('core', true)->count == 0) {
                return "no items";
            }

            $numberofloops = ceil($xml->children('core', true)->count / 150);

            $ii = 0;
            $docArray = array();
            //$idarray = array();
            
            foreach($xml->xpath('//core:content') as $content) {
                $ii++;
                $sourceId = $content->children('stab1',true)->external->children('extensions-core',true)->sourceId;
                $uuid = (string)$content->attributes();
                
                if($sourceId && $uuid) {
                    $sourceIdArray = explode('@', $sourceId);
                    $sourceId = $sourceIdArray[0];
                   
                    $id = (string)$sourceId;
                    
                    if(strstr($id,'-')) {
                        //${"doc" . $ii}->setKey('primary_uid', (string)$sourceId);
                        //echo '1' . (string)$sourceId;
                        $id = $this->getId($con, $id);
                    }
                    
                    //$idArray[$id] = $uuid;
                    if($id) {
                        ${"doc" . $ii} = $update->createDocument();
                        
                        ${"doc" . $ii}->setKey('id', $id);

                        ${"doc" . $ii}->addField('uuid', (string)$content->attributes());
                        ${"doc" . $ii}->setFieldModifier('uuid', 'set');

                        ${"doc" . $ii}->addField('boost', '1.0');
                        ${"doc" . $ii}->setFieldModifier('boost', 'set');

                        ${"doc" . $ii}->addField('date', $current_date);
                        ${"doc" . $ii}->setFieldModifier('date', 'set');

                        ${"doc" . $ii}->addField('tstamp', $current_date);
                        ${"doc" . $ii}->setFieldModifier('tstamp', 'set');

                        ${"doc" . $ii}->addField('digest', md5((string)$sourceId));
                        ${"doc" . $ii}->setFieldModifier('digest', 'set');

                        // add the documents and a commit command to the update query
                        $docArray[] = ${"doc" . $ii};
                    }
                }
            }
            $update->addDocuments($docArray);
            /*$update->addDocuments(array($doc1,$doc2,$doc3,$doc4,$doc5,$doc6,$doc7,$doc8,$doc9,$doc10,
                                        $doc11,$doc12,$doc13,$doc14,$doc15,$doc16,$doc17,$doc18,$doc19,$doc20,
                                        $doc21,$doc22,$doc23,$doc24,$doc25,$doc26,$doc27,$doc28,$doc29,$doc30,
                                        $doc31,$doc32,$doc33,$doc34,$doc35,$doc36,$doc37,$doc38,$doc39,$doc40,
                                        $doc41,$doc41,$doc43,$doc44,$doc45,$doc46,$doc47,$doc48,$doc49,$doc50));*/
            $update->addCommit();

            // this executes the query and returns the result
            $result = $client->update($update);

        }
        
        mysqli_close($con);
        
        return TRUE;
    }
    
    
    private function getId($con, $primary_uid)
    {
        $sql = "SELECT id FROM lucache_person WHERE primary_uid ='$primary_uid'";
        $res = mysqli_query($con, $sql) or die("140; ".mysqli_error());

        $row = mysqli_fetch_array($res, MYSQLI_ASSOC);
        $id = $row['id'];
        return $id;
    }
    
    private function debug($input)
    {
        echo '<pre>';
        print_r($input);
        echo '</pre>';
    }
}