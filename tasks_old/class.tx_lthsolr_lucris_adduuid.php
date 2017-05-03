<?php
class tx_lthsolr_lucris_adduuid extends \TYPO3\CMS\Scheduler\Task\AbstractTask {

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
        
        //$sql = 'TRUNCATE TABLE tx_lthsolr_lucrisdata';
        //$res = $GLOBALS['TYPO3_DB'] -> sql_query($sql);
        
        $startfromhere = 0;

        for($i = 0; $i < $numberofloops; $i++) {

            $startrecord = $startfromhere + ($i * $maximumrecords);
            if($startrecord > 0) $startrecord++;

            $xmlpath = "https://$lucrisId:$lucrisPw@lucris.lub.lu.se/ws/rest/person?window.size=$maximumrecords&window.offset=$startrecord&orderBy.property=id&rendering=xml_long";
            //$xmlpath = "https://$lucrisId:$lucrisPw@lucris.lub.lu.se/ws/rest/person?uuids.uuid=a432d89f-3d61-427c-ac2d-604f8ba57441&rendering=xml_long";
            //die($xmlpath);
            //try {
               // if (file_exists($xmlpath)) {	
                    $xml = file_get_contents($xmlpath);
                    //$xml = utf8_encode($xml);
                   //$xml = htmlentities($xml);
                    //$xml = preg_replace('/[\x00-\x08\x0b-\x0c\x0e-\x1f]/', '', $xml);
                    $xml = simplexml_load_string($xml);	
                   // $this->debug($xml);
                  //return TRUE;
               // }
                //$GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_devlog', array('msg' => '200: ' . $xmlpath, 'crdate' => time()));
                //$xml = new SimpleXMLElement($xmlpath, null, true);

            //} catch(Exception $e) {
               // $GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_devlog', array('msg' => '500: ' . $xmlpath, 'crdate' => time()));
                 //echo $xmlpath;
                // print_r($xml);
            //}
            /*if($xml->children('core', true)->count == 0) {
                return "no items";
            }*/

            $numberofloops = ceil($xml->children('core', true)->count / 20);

            $ii = 0;
            $docArray = array();
            //$idarray = array();
            
            foreach($xml->xpath('//core:result//core:content') as $content) {
                $ii++;
                $sourceId = (string)$content->children('stab1',true)->external->children('extensions-core',true)->sourceId;
                $uuid = (string)$content->attributes();
                $photo = '';
                $profileInformation = '';
                //$this->debug($content);
                //Photo
                if($content->children('stab1',true)->photos) {
                    $photo = (string)$content->children('stab1',true)->photos->children('core',true)->file->children('core',true)->url;
                }
                

                //profileInformation
                $profileInformationArray = array();
                if($content->children('stab1',true)->profileInformation) {
                    foreach($content->children('stab1', true)->profileInformation->children('extensions-core', true)->customField->children('extensions-core',true)->value->children('core',true)->localizedString as $localizedString) {
                        //echo '126';
                        if($localizedString->attributes()->locale == 'en_GB') {
                            $profileInformationArray['en'] = (string)$localizedString;
                        }
                        if($localizedString->attributes()->locale == 'sv_SE') {
                            $profileInformationArray['sv'] = (string)$localizedString;
                        }
                    }
                    if(count($profileInformationArray) > 0) {
                        $profileInformation = json_encode($profileInformationArray);
                    }
                }
                //echo $uuid . $sourceId . $lucris_photo . $profileInformation;
                /*
                 * stab1:profileInformation xmlns:stab1="http://atira.dk/schemas/pure4/model/template/abstractperson/stable">
<extensions-core:customField>
<extensions-core:value>
<core:localizedString locale="sv_SE
                 */

                if($sourceId && $uuid) {
                    $sourceIdArray = explode('@', $sourceId);
                    $sourceId = $sourceIdArray[0];
                   
                    $id = (string)$sourceId;
                    $GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_lthsolr_lucrisdata', array('typo3_id' => $id, 'lucris_id' => $uuid, 'lucris_photo' => $photo, 'lucris_profile_information' => $profileInformation));
                    //$idArray[$id] = $uuid;
                   /* if($id) {
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
                            ${"doc" . $ii}->addField('lucrisphoto', '');
                            ${"doc" . $ii}->setFieldModifier('lucrisphoto', 'set');
                        }

                        // add the documents and a commit command to the update query
                        $docArray[] = ${"doc" . $ii};
                        $GLOBALS['TYPO3_DB']->exec_UPDATEquery('fe_users', "username='$id'", array('lth_solr_uuid' => $uuid));
                    }*/
                }
            }
            /*$update->addDocuments($docArray);
            $update->addCommit();
            $result = $client->update($update);*/

        }
        
        //mysqli_close($con);
        
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