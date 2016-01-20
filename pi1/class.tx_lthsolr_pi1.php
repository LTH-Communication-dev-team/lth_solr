<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2015 Tomas Havner <tomas.havner@kansli.lth.se>
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

// require_once(PATH_tslib . 'class.tslib_pibase.php');

/**
 * Plugin 'LTH Solr' for the 'lth_solr' extension.
 *
 * @author	Tomas Havner <tomas.havner@kansli.lth.se>
 * @package	TYPO3
 * @subpackage	tx_lthsolr
 */
class tx_lthsolr_pi1 extends tslib_pibase {
	public $prefixId      = 'tx_lthsolr_pi1';		// Same as class name
	public $scriptRelPath = 'pi1/class.tx_lthsolr_pi1.php';	// Path to this script relative to the extension dir.
	public $extKey        = 'lth_solr';	// The extension key.
	public $pi_checkCHash = TRUE;
	
	/**
	 * The main method of the Plugin.
	 *
	 * @param string $content The Plugin content
	 * @param array $conf The Plugin configuration
	 * @return string The content that is displayed on the website
	 */
	public function main($content, array $conf) {
            $this->conf = $conf;
            $this->pi_setPiVarDefaults();
            $this->pi_loadLL();
            
                //Load main js- and css-files
            $GLOBALS["TSFE"]->additionalHeaderData["tx_lthsolr_js"] = "<script language=\"JavaScript\" type=\"text/javascript\" src=\"/typo3conf/ext/lth_solr/res/lth_solr.js\"></script>"; 
            $GLOBALS["TSFE"]->additionalHeaderData["tx_lthsolr_css"] = "<link rel=\"stylesheet\" type=\"text/css\" href=\"/typo3conf/ext/lth_solr/res/lth_solr.css\" />";
             
            $id = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('id');
		
            $content = '';
            
            //$content = $this->restTest();
            
            $content .= file_get_contents("/var/www/html/typo3/typo3conf/ext/lth_solr/templates/rest.html");
            $content .= '<input type="hidden" id="lth_solr_type" value="rest" />';
        
            //$this->debug($content);
	
            return $content;
	}
        
        
        private function restTest()
        {
           /* $client = new SoapClient("https://devel.atira.dk/lund/ws/Pure4WebService/pure4.wsdl");
            $params = array('uuids.uuid' => '"8b564b09-9963-483b-84f9-3396ec18a67e"',
                        'rendering' => 'xml_short',
                        'window.size' => '1',
                        'state' => 'active');
                $response = $client->GetPerson($params);
                
                
            
            $this->debug($pure_array['result']['content']);
           
$this->debug(json_decode($json_encode($response), TRUE));
            
$pure_json = json_encode($response);
            $pure_array = json_decode($pure_json, TRUE);
            return $pure_array;*/
            //$requestUrl = url_encode('https://devel.atira.dk/lund/ws/rest/person?uuids.uuid=8b564b09-9963-483b-84f9-3396ec18a67e');
            //$xmlDoc = $this->getXMLDoc($requestUrl);
            //var_dump($xmlDoc);
           //echo $xmlDoc;
            
        }
        
        
        private function getXMLDoc($uri)
	{
            $xmlDoc = new DOMDocument('1.0', 'UTF-8');
            $success = $xmlDoc->loadXML(t3lib_div::getURL($uri));
		if(!$success)
		{
			t3lib_div::devlog(microtime(). 'XML is not loaded. Is it valid xml? Check url.', 'pure', 2,  array('url' => $uri));
			return null;
		}

		return $xmlDoc;
	}
        
        
        private function debug($input)
        {
            echo '<pre>';
            print_r($input);
            echo '</pre>';
        }
        
        private function searchId($id)
        {
            $content = '';
            
            require(__DIR__.'/init.php');

            // create a client instance
            $client = new Solarium\Client($config);

            // get a select query instance
            $query = $client->createSelect();
            
            // set a query (all prices starting from 12)
            $query->setQuery('email_t:"'.$id.'"');


            // this executes the query and returns the result
            $resultset = $client->execute($query);


            // show documents using the resultset iterator
            foreach ($resultset as $document) {

                $content .=  '<hr/><table>';

                // the documents are also iterable, to get all fields
                foreach ($document as $field => $value) {
                    // this converts multivalue fields to a comma-separated string
                    if (is_array($value)) {
                        $value = implode(', ', $value);
                    }

                    $content .=  '<tr><th>' . $field . '</th><td>' . $value . '</td></tr>';
                }

                $content .=  '</table>';
            }
            return $content;
        }
        
        
        private function searchForm()
        {
            $content = '';
            
            require(__DIR__.'/init.php');

            // create a client instance
            $client = new Solarium\Client($config);

            // get a select query instance
            $query = $client->createQuery($client::QUERY_SELECT);

            // this executes the query and returns the result
            $resultset = $client->execute($query);

            // display the total number of documents found by solr
            $content .=  'NumFound: '.$resultset->getNumFound();

            // show documents using the resultset iterator
            foreach ($resultset as $document) {

                $content .=  '<hr/><table>';

                // the documents are also iterable, to get all fields
                foreach ($document as $field => $value) {
                    // this converts multivalue fields to a comma-separated string
                    if (is_array($value)) {
                        $value = implode(', ', $value);
                    }

                    $content .=  '<tr><th>' . $field . '</th><td>' . $value . '</td></tr>';
                }

                $content .=  '</table>';
            }
            return $content;
        }
}



if (defined('TYPO3_MODE') && isset($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/lth_solr/pi1/class.tx_lthsolr_pi1.php'])) {
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/lth_solr/pi1/class.tx_lthsolr_pi1.php']);
}

?>