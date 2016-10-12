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
        $this->getPersonUuid();
        return TRUE;
    }
    
    
    function getPersonUuid()
    {
        require(__DIR__.'/init.php');
        $maximumrecords = 20;
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
        
        $con = mysqli_connect($dbhost, $user, $pw, $db) or die("48; ".mysqli_error());
    
	if (!$settings['solrHost'] || !$settings['solrPort'] || !$settings['solrPath'] || !$settings['solrTimeout']) {
	    return 'Please make all settings in extension manager';
	}

        // create a client instance
        $client = new Solarium\Client($config);
        
        $i=0;
        $startrecord = 0;
        
        // get an update query instance
        $update = $client->createUpdate();
        
        $lucrisId = $settings['solrLucrisId'];
        $lucrisPw = $settings['solrLucrisPw'];

        for($i = 0; $i < $numberofloops; $i++) {

            $startrecord = $i * $maximumrecords;
            if($startrecord > 0) $startrecord++;

            $xmlpath = "https://$lucrisId:$lucrisPw@lucris.lub.lu.se/ws/rest/person?window.size=$maximumrecords&window.offset=$startrecord&orderBy.property=id";
            //&rendering=xml_long

            try {
                //$GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_devlog', array('msg' => '200: ' . $xmlpath, 'crdate' => time()));
                $xml = new SimpleXMLElement($xmlpath, null, true);
                
            } catch(Exception $e) {
                $GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_devlog', array('msg' => '500: ' . $xmlpath, 'crdate' => time()));
            }
            if($xml->children('core', true)->count == 0) {
                return "no items";
            }

            $numberofloops = ceil($xml->children('core', true)->count / 20);

            $ii = 0;
            $docArray = array();
            //$idarray = array();
            
            foreach($xml->xpath('//core:result//core:content') as $content) {
                $ii++;
                $sourceId = $content->children('stab1',true)->external->children('extensions-core',true)->sourceId;
                $uuid = (string)$content->attributes();
                $photo = '';
                //$this->debug($content);
                //Photo
                if($content->children('stab1',true)->photos) {
                    $photo = $content->children('stab1',true)->photos->children('core',true)->file->children('core',true)->url;
                }
                


                if($sourceId && $uuid) {
                    $sourceIdArray = explode('@', $sourceId);
                    $sourceId = $sourceIdArray[0];
                   
                    $id = (string)$sourceId;
                    
                    //$idArray[$id] = $uuid;
                    if($id) {
                        $uuid = (string)$content->attributes();
                        ${"doc" . $ii} = $update->createDocument();
                        
                        ${"doc" . $ii}->setKey('id', $id);

                        ${"doc" . $ii}->addField('uuid', $uuid);
                        ${"doc" . $ii}->setFieldModifier('uuid', 'set');

                        ${"doc" . $ii}->addField('boost', '1.0');
                        ${"doc" . $ii}->setFieldModifier('boost', 'set');

                        ${"doc" . $ii}->addField('date', $current_date);
                        ${"doc" . $ii}->setFieldModifier('date', 'set');

                        ${"doc" . $ii}->addField('tstamp', $current_date);
                        ${"doc" . $ii}->setFieldModifier('tstamp', 'set');

                        ${"doc" . $ii}->addField('digest', md5((string)$sourceId));
                        ${"doc" . $ii}->setFieldModifier('digest', 'set');
                        
                        if($photo) {
                            ${"doc" . $ii}->addField('lucrisphoto', (string)$photo);
                            ${"doc" . $ii}->setFieldModifier('lucrisphoto', 'set');
                        } else {
                            ${"doc" . $ii}->removeField('lucrisphoto');
                        }

                        // add the documents and a commit command to the update query
                        $docArray[] = ${"doc" . $ii};
                        $GLOBALS['TYPO3_DB']->exec_UPDATEquery('fe_users', "username='$id'", array('lth_solr_uuid' => $uuid));
                    }
                }
            }
            $update->addDocuments($docArray);
            $update->addCommit();
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
    
    
    private function getPrimary_uid($con, $id)
    {
        $sql = "SELECT primary_uid FROM lucache_person WHERE id ='$id'";
        $res = mysqli_query($con, $sql) or die("173; ".mysqli_error());

        $row = mysqli_fetch_array($res, MYSQLI_ASSOC);
        $primary_uid = $row['primary_uid'];
        return $primary_uid;
    }
    
    private function debug($input)
    {
        echo '<pre>';
        print_r($input);
        echo '</pre>';
    }
}