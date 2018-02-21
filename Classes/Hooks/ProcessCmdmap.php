<?php
namespace LTH\Lthsolr\Hooks;

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
        
        $docArray = array();
       /* if($table==='tt_content') {
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
                            if ($val == 'fe_groups') {
                                $fe_groups = (string)$n->value;
                            }
                            if ($val == 'staffHomepagePath') {
                                $staffHomepagePath = (string)$n->value;
                            }
                        }
                    }

                    if($fe_groups && $staffHomepagePath) {
                        $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('title','fe_groups',"uid in($fe_groups)");
                        while ($row = $GLOBALS["TYPO3_DB"]->sql_fetch_assoc($res)) {
                            $title[] = explode('__', $row['title'])[0];
                        }
                        if($title) {
                            $scope = implode(',', $title);
                        }
                        $GLOBALS['TYPO3_DB']->sql_free_result($res);

                        $scopeArray = explode(",", $scope);
                        $scope = '';
                        foreach($scopeArray as $key => $value) {
                            if($scope) {
                                $scope .= ' OR ';
                            } else {
                                $scope .= ' AND (orgid:';
                            }
                            $scope .= '"' . $value . '" OR heritage:"' . $value . '"';
                        }
                        $scope .= ")";
                        //$scope .= " OR $showVal:1)";
                        
                        $backendUtility = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Backend\\Utility\\BackendUtility');
                        $rootLine = $backendUtility->BEgetRootline($pid);
                        $domainName = $backendUtility->firstDomainRecord($rootLine);
                        $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery("uid","sys_domain","domainName='$domainName'");
                        $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
                        $domainId = $row["uid"];
                        $redirectArray = array();
                        $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery("uid, url","tx_myredirects_domain_model_redirect","domain=$domainId");
                        while ($row = $GLOBALS["TYPO3_DB"]->sql_fetch_assoc($res)) {
                            $redirectArray[$row["uid"]] = $row["url"];
                        }
                        $GLOBALS['TYPO3_DB']->sql_free_result($res);
                                 
                        $TSObj = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\TypoScript\\TemplateService');
                        $TSObj->tt_track = 0;
                        $TSObj->init();
                        $TSObj->runThroughTemplates($rootLine);
                        $TSObj->generateConfig();
                        $TS = $TSObj->setup;
                        $syslang = $TS['config.']['language'];
                        $baseURL = $TS['config.']['baseURL'];
                                                
                        require(__DIR__.'/init.php');

                        $settings = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['lth_solr']);

                        $sconfig = array(
                            'endpoint' => array(
                                'localhost' => array(
                                    'host' => $settings['solrHost'],
                                    'port' => $settings['solrPort'],
                                    'path' => "/solr/core_$syslang/",//$settings['solrPath'],
                                    'timeout' => $settings['solrTimeout']
                                )
                            )
                        );
                        if (!$settings['solrHost'] || !$settings['solrPort'] || !$settings['solrTimeout']) {
                            die('Please make all settings in extension manager');
                        }
                        $client = new \Solarium\Client($sconfig);
                        $query = $client->createSelect();

                        $queryToSet = '(docType:"staff"'.$scope.")";
                        //$GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_devlog', array('msg' => $queryToSet, 'crdate' => time()));
                        $query->setStart(0)->setRows(3000);
                        $query->setQuery($queryToSet);
                        $response = $client->select($query);
                        if($response) {
                            $nameArray = array();
                            foreach ($response as $document) {
                                $uuid = $document->uuid;
                                if(!$uuid) {
                                    $uuid = $document->guid;
                                }
                                $name = str_replace(' ', '-', $document->name);
                                $name = preg_replace("/&([a-z])[a-z]+;/i", "$1", htmlentities($name));
                                if(in_array($name, $nameArray)) {
                                    for($i=0; $i<100; $i++) {
                                        if(!in_array($name . '-' . $i, $nameArray)) {
                                            $name = $name . '-' . $i;
                                            break;
                                        }
                                    }
                                }
                                $nameArray[$uuid] = strtolower($name);
                            }
                        }
                        //$GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_devlog', array('msg' => print_r($nameArray,true), 'crdate' => time()));
                        //$GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_devlog', array('msg' => $domainId, 'crdate' => time()));
                        if(is_array($nameArray) && $domainId) {
                            foreach ($nameArray as $key => $value) {
                                $url_hash = sha1($value);
                                $rKey = array_search($value, $redirectArray);
                                if($rKey) {
                                    $GLOBALS['TYPO3_DB']->exec_UPDATEquery('tx_myredirects_domain_model_redirect', 'uid='.intval($rKey), array('url_hash' => $url_hash, 'url' => $value, 'destination' => str_replace("//", "/", "/" . rtrim(ltrim($staffHomepagePath,"/"),"/") . "/?uuid=lthsolr[$key]"), 'domain' => $domainId, 'tstamp' => time()));
                                } else {
                                    $GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_myredirects_domain_model_redirect', array('url_hash' => $url_hash, 'url' => $value, 'destination' => str_replace("//", "/", "/" . rtrim(ltrim($staffHomepagePath,"/"),"/") . "/?uuid=lthsolr[$key]"), 'domain' => $domainId, 'crdate' => time(), 'tstamp' => time()));
                                }
                            }
                        }
                    }
                }
            }
        }
        */
        
        /*$title;
        $primary_uid;
        $client = new \Solarium\Client($config);
        
        $update = $client->createUpdate();

                if($fe_groups) {
                    $docArray = array();
                    $showVar = 'lth_solr_show_' . $pid . '_i';
                    //$sql = "SELECT FU.lucache_id FROM fe_groups FG JOIN fe_users FU ON FIND_IN_SET(FG.uid, FU.usergroup) WHERE FU.lucache_id != '' AND FG.uid IN($fe_groups)";
                    $sql = "SELECT * FROM fe_users WHERE lucache_id != '' AND FIND_IN_SET((SELECT SUBSTRING_INDEX(SUBSTRING_INDEX(title, '__', 1), '__', -1) FROM fe_groups WHERE uid = $fe_groups),lth_solr_heritage)";
                    $res = $GLOBALS['TYPO3_DB'] -> sql_query($sql);
                    while ($row = $GLOBALS["TYPO3_DB"]->sql_fetch_assoc($res)) {
                        $uid = $row['uid'];
                        $lucache_id = $row['lucache_id'];
                        $lth_solr_show = $row['lth_solr_show'];
                        if($lucache_id) {
                            $doc = $update->createDocument();
                            $doc->setKey('id', $lucache_id);
                            ${"doc"}->addField($showVar, 1);
                            ${"doc"}->setFieldModifier($showVar, 'set');

                            $docArray[] = $doc;
                            $update->addDocuments($docArray);
                            
                            $showArray = array();
                            if($lth_solr_show) {
                                $showArray = json_decode($lth_solr_show, true);
                            }
                            $showArray[] = $showVar;
                            
                            $showArray = array_unique($showArray);

                            $updateArray = array('lth_solr_show' => json_encode($showArray), 'tstamp' => time());

                            $GLOBALS['TYPO3_DB']->exec_UPDATEquery("fe_users", "uid='$uid'", $updateArray);
                            
                        }
                    }
                    $update->addCommit();
                    $result = $client->update($update);
                }
                $GLOBALS['TYPO3_DB']->sql_free_result($res);
            }
            
        }*/
    }
}