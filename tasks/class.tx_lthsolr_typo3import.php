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
        
        //$pagesArray[];
        $unixTimestamp = time();
        
        $sql = "SELECT uid, pid, title, subtitle, nav_title FROM pages WHERE hidden = 0 AND deleted = 0 AND doktype < 200";

        $res = $GLOBALS['TYPO3_DB'] -> sql_query($sql);
        while ($row = $GLOBALS["TYPO3_DB"]->sql_fetch_assoc($res)) {
            $pagesArray[] = array('pid' => $row['pid'], 'uid' => $row['uid'], 'title' => $row['title'], 'subtitle' => $row['subtitle'], 'nav_title' => $row['nav_title']);
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
                    $uid = $value['uid'];
                    $pid = $value['pid'];
                    $title = $value['title'];
                    $subtitle = $value['subtitle'];
                    $nav_title = $value['nav_title'];
                    if($nav_title) {
                        $title = $nav_title;
                    } elseif($subtitle) {
                        $title = $subtitle;
                    }
                    
                    try {
                        
                        
                        
                        $rootLine = \TYPO3\CMS\Backend\Utility\BackendUtility::BEgetRootLine($pid);
                        if($rootLine) $domain = \TYPO3\CMS\Backend\Utility\BackendUtility::firstDomainRecord($rootLine);
                        if($domain) {
                            $url = 'http://' . rtrim($domain, '/') . '/index.php?id=' . $uid . '&type=77';
                            if($url) {
                                // Create DOM from URL or file
                                $body = file_get_contents($url);

                                $body = strip_tags($body);
                            }
                            $pagePath = \TYPO3\CMS\Backend\Utility\BackendUtility::getRecordPath($uid,'','');
                        }
                        if($pagePath) $fullPath = $this->getFullPath($pagePath, $domain);
                        /*$title = $doc->getElementsByTagName('title');
                        $body = $doc->getElementsByTagName('body');*/
                        $data = array(
                            'id' => 'page_' . $uid,
                            'type_s' => 'page',
                            'title_t' => $title,
                            'body_txt' => $body,
                            'path_s' => $fullPath
                        );
                        //$this->debug($data);
                        //echo $url;
                        $buffer->createDocument($data);
                    } catch(Exception $e) {
                        echo 'Message: ' .$e->getMessage();
                    }

                    //$buffer->createDocument($data);                    
                }
                $buffer->commit();
                return TRUE;
            }
        } catch(Exception $e) {
            echo 'Message: ' .$e->getMessage();
            //die();
            return false;
        }

    }
    
    
    function getFullPath($pagePath, $domain)
    {
        $pagePathArray = explode('/', $pagePath);
        //print_r($pagePathArray);
        array_shift($pagePathArray);
        array_shift($pagePathArray);
        //array_shift($pagePathArray);
        //if($cacheCmd) array_shift($pagePathArray);
        $pagePath = strtolower(implode('/', $pagePathArray));
        $pagePath = str_replace('å','aa',$pagePath);
        $pagePath = str_replace('ä','ae',$pagePath);
        $pagePath = str_replace('ö','oe',$pagePath);
        $pagePath = str_replace(' & ','-',$pagePath);
        $pagePath = str_replace(' - ','-',$pagePath);
        $pagePath = str_replace(' -','-',$pagePath);
        $pagePath = str_replace('- ','-',$pagePath);
        $pagePath = str_replace(' ','-',$pagePath);
        //$pagePath = str_replace('/','',$pagePath);
        //$GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_devlog', array('msg' => $pagePath, 'crdate' => time()));
        //echo $pagePath . "--";
        $fullPath = 'http://' . rtrim($domain,'/') . '/' . trim($pagePath, '/') . '/';
        //echo $fullPath . "--";
        return $fullPath;
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