<?php
/*
 * Register necessary class names with autoloader jjjj
 */
$extensionPath = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('lth_solr');
return array(
    'tx_lthsolr_lucacheimport' => $extensionPath . 'tasks/class.tx_lthsolr_lucacheimport.php',
    'tx_lthsolr_lupimport' => $extensionPath . 'tasks/class.tx_lthsolr_lupimport.php',
    'tx_lthsolr_typo3import' => $extensionPath . 'tasks/class.tx_lthsolr_typo3import.php',
    'tx_lthsolr_falimport' => $extensionPath . 'tasks/class.tx_lthsolr_falimport.php',
    'tx_lthsolr_newsimport' => $extensionPath . 'tasks/class.tx_lthsolr_newsimport.php',
    'tx_lthsolr_lucrisimport' => $extensionPath . 'tasks/class.tx_lthsolr_lucrisimport.php',
    'tx_lthsolr_lucris_adduuid' => $extensionPath . 'tasks/class.tx_lthsolr_lucris_adduuid.php',
    'lth_solr_ajax' => $extensionPath . 'service/be_ajax.php',
);