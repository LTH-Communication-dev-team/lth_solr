<?php

ini_set('memory_limit', '-1');
error_reporting(E_ERROR);
set_time_limit(0);

class tx_lthsolr_falimport extends tx_scheduler_Task {
	
    function execute()
    {
	$executionSucceeded = FALSE;

	$executionSucceeded = $this->indexItems();
        
	return $executionSucceeded;
    }

    function indexItems()
    {
	require(__DIR__.'/init.php');

        tslib_eidtools::connectDB();
        
        $falArray = $this->getFal();
        /*echo '<pre>';
        print_r($config);
        echo '<pre>'; */ 
        $executionSucceeded = $this->updateSolr($falArray, $config);
        
        return $executionSucceeded;
    }
    
    
    private function getFal()
    {
        
        $falArray = array();

        $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery("uid, identifier", "sys_file", "extension IN('pdf', 'doc', 'docx')");
        while ($row = $GLOBALS["TYPO3_DB"]->sql_fetch_assoc($res)) {
            $falArray[] = array(
                'uid' => $row['uid'], 
                'identifier' => $row['identifier']
            );
        }
        $GLOBALS['TYPO3_DB']->sql_free_result($res);
        return $falArray;
    }
    
    
    private function updateSolr($falArray, $config)
    {
        try {
            if(count($falArray) > 0) {
                //create a client instance
                $client = new Solarium\Client($config);
        
                foreach($falArray as $key => $value) {
                    // get an extract query instance and add settings
                    $query = $client->createExtract();
                    $query->addFieldMapping('content', 'text');
                    $query->setUprefix('attr_');
                    $query->setFile('/var/www/html/typo3/fileadmin' . $value['identifier']);
                    $query->setCommit(true);
                    $query->setOmitHeader(false);

                    // add document
                    $doc = $query->createDocument();
                    $doc->id = $value['uid'];
                    $doc->title_t = $value['identifier'];
                    $query->setDocument($doc);

                    $client->extract($query);               
                }
                return TRUE;
            }
        } catch(Exception $e) {
            echo 'Message: ' .$e->getMessage();
            die();
            return false;
        }

    }
}