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
        //$GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_devlog', array('msg' => $table, 'crdate' => time()));

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
        
        if($table==='fe_users') {
            $sql = 'SELECT FG.title AS usergroup, FU.username AS username FROM fe_groups FG JOIN fe_users FU ON FIND_IN_SET(FG.uid, FU.usergroup) WHERE FU.uid = ' . intval($id);
            $res = $GLOBALS['TYPO3_DB'] -> sql_query($sql);
            while ($row = $GLOBALS["TYPO3_DB"]->sql_fetch_assoc($res)) {
                $usergroup[] = explode('__', $row['usergroup'])[0];
                $username = $row['username'];
            }
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
            $GLOBALS['TYPO3_DB']->sql_free_result($res);
            
        }
    }
}