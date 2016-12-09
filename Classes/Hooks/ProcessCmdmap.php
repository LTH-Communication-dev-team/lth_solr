<?php
namespace LTH\lth_solr\Hooks;

class ProcessCmdmap {
   /**
    *
    * @param string $table the table of the record
    * @param integer $id the ID of the record
    * @param array $record The accordant database record
    * @param boolean $recordWasDeleted can be set so that other hooks or
    * @param DataHandler $tcemainObj reference to the main tcemain object
    * @return   void
    */
    
    function processDatamap_afterDatabaseOperations($command, $table, $id, $value, $dataHandler)
    {
        //$GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_devlog', array('msg' => print_r($dataHandler,true), 'crdate' => time()));
        
        require __DIR__.'/../../vendor/solarium/vendor/autoload.php';
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
        
        if (!$settings['solrHost'] || !$settings['solrPort'] || !$settings['solrPath'] || !$settings['solrTimeout']) {
	    die();
	}
        
        $title;
        $primary_uid;
        $client = new \Solarium\Client($config);
        
        $update = $client->createUpdate();
        $docArray = array();
        if($table==='tt_content') {
            $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery("pid,list_type,pi_flexform","tt_content","uid=".intval($id));
            $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
            $pid = $row['pid'];
            $list_type = $row['list_type'];
            $pi_flexform = $row['pi_flexform'];
            
            if($list_type=='lth_solr_pi2') {
                $xml = simplexml_load_string($pi_flexform);
                $test = $xml->data->sheet[0]->language;
                if($test) {
                    foreach ($test->field as $n) {
                        foreach($n->attributes() as $name => $val) {
                            if ($val == 'scope') {
                                $fe_groups = (string)$n->value;
                            }
                        }
                    }
                }
                if($fe_groups) {
                    $docArray = array();
                    $showVar = 'lth_solr_show_' . $pid . '_i';
                    //$sql = "SELECT FU.lucache_id FROM fe_groups FG JOIN fe_users FU ON FIND_IN_SET(FG.uid, FU.usergroup) WHERE FU.lucache_id != '' AND FG.uid IN($fe_groups)";
                    $sql = "SELECT * FROM fe_users WHERE lucache_id != '' AND FIND_IN_SET((SELECT SUBSTRING_INDEX(SUBSTRING_INDEX(title, '__', 1), '__', -1) FROM fe_groups WHERE uid = $fe_groups),lth_solr_heritage)";
                    $res = $GLOBALS['TYPO3_DB'] -> sql_query($sql);
                    while ($row = $GLOBALS["TYPO3_DB"]->sql_fetch_assoc($res)) {
                        $lucache_id = $row['lucache_id'];
                        if($lucache_id) {
                            $doc = $update->createDocument();
                            $doc->setKey('id', $lucache_id);
                            ${"doc"}->addField($showVar, 1);
                            ${"doc"}->setFieldModifier($showVar, 'set');

                            $docArray[] = $doc;
                            $update->addDocuments($docArray);
                            
                        }
                    }
                    $update->addCommit();
                    $result = $client->update($update);
                }
                $GLOBALS['TYPO3_DB']->sql_free_result($res);
            }
            
        } /*else if($table==='fe_users') {
            
            $usergroup[] = explode('__', $row['usergroup'])[0];
            $username = $row['username'];
            
            //print_r($usergroup);
            if($username) {
                $query = $client->createSelect();
                $query->setQuery('primary_uid:'.$username);
                $response = $client->select($query);
                foreach ($response as $document) {
                    
                    $sId = $document['id'];
                    //$sUsergroup = $document['usergroup'];
                    //echo $sId . $sUsergroup;
                }
                if($sId) {
                    //$usergroup = array_unique(array_merge($usergroup, $sUsergroup));
                    $doc = $update->createDocument();

                    $doc->setKey('id', $sId);

                    $doc->addField('orgid', $usergroup);
                    $doc->setFieldModifier('orgid', 'set');

                    // add the documents and a commit command to the update query
                    $docArray[] = $doc;
                    $update->addDocuments($docArray);
                    $update->addCommit();
                    $result = $client->update($update);
                }
            }
            $GLOBALS['TYPO3_DB']->sql_free_result($res);
            
        }*/
    }
}