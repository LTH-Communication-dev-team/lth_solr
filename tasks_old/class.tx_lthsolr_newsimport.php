<?php

ini_set('memory_limit', '-1');
error_reporting(E_ERROR);
set_time_limit(0);

class tx_lthsolr_newsimport extends \TYPO3\CMS\Scheduler\Task\AbstractTask {
	
    function execute()
    {
	$executionSucceeded = FALSE;

	$executionSucceeded = $this->indexItems();
        
	return $executionSucceeded;
    }

    function indexItems()
    {
	require(__DIR__.'/init.php');
        
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
	    return 'Please make all settings in extension manager';
	}

        $newsArray = array();

        //tslib_eidtools::connectDB();
        
        $newsArray = $this->getNews();
              
        /*echo '<pre>';
        print_r($newsArray);
        echo '<pre>';*/
        
        $executionSucceeded = $this->updateSolr($newsArray, $config);
        return $executionSucceeded;
    }
    
    
    private function getNews()
    {
        
        $newsArray = array();
       
        $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid, pid, title, short, bodytext, author', 'tt_news', 'deleted = 0 AND hidden = 0');
        while ($row = $GLOBALS["TYPO3_DB"]->sql_fetch_assoc($res)) {
            $newsArray[] = array('uid' => $row['uid'], 'pid' => $row['pid'], 'title' => $row['title'], 'short' => $row['short'], 'bodytext' => $row['bodytext']);
        }
        $GLOBALS['TYPO3_DB']->sql_free_result($res);
        
        return $newsArray;
    }
    
    
   
    
    private function updateSolr($newsArray, $config)
    {
        try {
            if(count($newsArray) > 0) {
                //create a client instance
                $client = new Solarium\Client($config);
                
                $buffer = $client->getPlugin('bufferedadd');
                $buffer->setBufferSize(250);
        
                foreach($newsArray as $key => $value) {
                    $data = array(
                        'id' => $value['uid'],
                        'pid_i' => $value['pid'],
                        'title_t' => $value['title'],
                        'short_t' => $value['short'],
                        'bodytext_txt' => $value['bodytext']
                    );

                    $buffer->createDocument($data);                    
                }
                // this executes the query and returns the result
                $buffer->flush();
                return TRUE;
            }
        } catch(Exception $e) {
            echo 'Message: ' .$e->getMessage();
            die();
            return false;
        }
    }
}