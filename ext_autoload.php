<?php
/*
 * Register necessary class names with autoloader jjjj
 */
$extensionPath = t3lib_extMgm::extPath('lth_solr');
return array(
    'tx_lthsolr_lucacheimport' => $extensionPath . 'tasks/class.tx_lthsolr_lucacheimport.php',
    'tx_lthsolr_lupimport' => $extensionPath . 'tasks/class.tx_lthsolr_lupimport.php',
    'tx_lthsolr_typo3import' => $extensionPath . 'tasks/class.tx_lthsolr_typo3import.php',
    'tx_lthsolr_falimport' => $extensionPath . 'tasks/class.tx_lthsolr_falimport.php',
    'tx_lthsolr_newsimport' => $extensionPath . 'tasks/class.tx_lthsolr_newsimport.php'
);
?>