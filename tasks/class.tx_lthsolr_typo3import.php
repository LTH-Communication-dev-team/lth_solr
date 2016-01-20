<?php

ini_set('memory_limit', '-1');
error_reporting(E_ERROR);
set_time_limit(0);

class tx_lthsolr_typo3import extends tx_scheduler_Task {
	
    function execute()
    {
	$executionSucceeded = FALSE;

	$executionSucceeded = $this->indexItems();
        
	return $executionSucceeded;
    }

    function indexItems()
    {
	require(__DIR__.'/init.php');

        $pagesArray = array();

        tslib_eidtools::connectDB();
        
        $pagesArray = $this->getPages();
        $executionSucceeded = $this->updateSolr($pagesArray, $config);
        return $executionSucceeded;
    }
    
    private function debug($inputArray)
    {
        echo '<pre>';
        print_r($inputArray);
        echo '<pre>';
    }
    
    
    private function getpages()
    {
        
        $pagesArray = array();
        $unixTimestamp = time();
        
        $sql = "SELECT uid, title FROM pages WHERE hidden = 0 AND deleted = 0 AND doktype < 200";

        $res = $GLOBALS['TYPO3_DB'] -> sql_query($sql);
        while ($row = $GLOBALS["TYPO3_DB"]->sql_fetch_assoc($res)) {
            $pagesArray[$row['uid']] = $row['title'];
        }
        $GLOBALS['TYPO3_DB']->sql_free_result($res);
        
        return $pagesArray;
    }
    
    
   
    
    private function updateSolr($pagesArray, $config)
    {
        try {
            if(count($pagesArray) > 0) {
                //create a client instance
                $client = new Solarium\Client($config);
                $buffer = $client->getPlugin('bufferedadd');
                $buffer->setBufferSize(250);

                foreach($pagesArray as $key => $value) {
                    try {
                        $url = "http://130.235.208.15/index.php?type=77&id=" . $key;
                        $content = "";
                        $doc = new DOMDocument();
                        $doc->load($url);
                        $title = $doc->getElementsByTagName('title');
                        $body = $doc->getElementsByTagName('body');
                        $data = array(
                            'id' => $key,
                            'title_t' => $title,
                            'body_txt' => $body
                        );
                        
                        try {
                            $buffer->createDocument($data);
                        } catch(Exception $e) {
                            echo 'Message: ' .$e->getMessage();
                        }
                    } catch(Exception $e) {
                        echo 'Message: ' .$e->getMessage();
                    }

                    $buffer->createDocument($data);                    
                }
                $buffer->flush();
                return TRUE;
            }
        } catch(Exception $e) {
            echo 'Message: ' .$e->getMessage();
            die();
            return false;
        }

    }
    
    
    private function getUids($inputString, $inputArray)
    {
        if($inputString) {
            $loopArray = explode(',', $inputString);
            $tmpKey = 0;
            $tmpArray = array();
            $resArray = array();
            foreach($loopArray as $key => $value) {
                $tmpKey = array_search($value, $inputArray);
                $tmpArray = $inputArray[$tmpKey];
                $resArray[] = $tmpArray['uid'];
            }
            return implode(',', $resArray);
        } else {
            return '';
        }
    }
    
    
    
    
    /*public function traverse($i_id)
    {
        //echo $i_id . '; ';
        $i_lft = $this->i_count;
        $this->i_count++;

        $a_kid = $this->get_children($i_id);

        if ($a_kid) {
            foreach($a_kid as $a_child) {
                $this->traverse($a_child);
            }
        }
        $i_rgt = $this->i_count;
        $this->i_count++;
        $this->write($i_lft, $i_rgt, $i_id);
    }
    
    
    private function get_children($i_id) 
    {
        if ( ! isset($this->a_link[$i_id])) {
            $this->a_link[$i_id] = null;
        }

        return $this->a_link[$i_id];
    }*/
}