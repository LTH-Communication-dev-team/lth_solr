<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
    'lth.' . $_EXTKEY
);

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass'][] = 'LTH\\lth_solr\\Hooks\\ProcessCmdmap';

// Add AJAX support
$TYPO3_CONF_VARS['FE']['eID_include']['lth_solr'] = 'EXT:lth_solr/service/ajax.php';

//Ajax in BE?
//$TYPO3_CONF_VARS['BE']['AJAX']['lth_solr::ajaxControl'] = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('lth_solr').'service/be_ajax.php:lth_solr_ajax->ajaxControl';


$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks']['tx_lthsolr_lucacheimport'] = array(
	'extension'        => $_EXTKEY,
	'title'            => 'Lucache Import',
	'description'      => '',
	'additionalFields' => '',
);

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks']['tx_lthsolr_lupimport'] = array(
	'extension'        => $_EXTKEY,
	'title'            => 'LUP Import',
	'description'      => '',
	'additionalFields' => '',
);

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks']['tx_lthsolr_typo3import'] = array(
	'extension'        => $_EXTKEY,
	'title'            => 'Typo3 Import',
	'description'      => '',
	'additionalFields' => '',
);

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks']['tx_lthsolr_falimport'] = array(
	'extension'        => $_EXTKEY,
	'title'            => 'FAL Import',
	'description'      => '',
	'additionalFields' => '',
);

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks']['tx_lthsolr_lucrisimport'] = array(
	'extension'        => $_EXTKEY,
	'title'            => 'Lucris Import',
	'description'      => '',
	'additionalFields' => '',
);

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks']['tx_lthsolr_lucris_adduuid'] = array(
	'extension'        => $_EXTKEY,
	'title'            => 'Lucris Add uuid',
	'description'      => '',
	'additionalFields' => '',
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPItoST43($_EXTKEY, 'pi1/class.tx_lthsolr_pi1.php', '_pi1', 'list_type', 1);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPItoST43($_EXTKEY, 'pi2/class.tx_lthsolr_pi2.php', '_pi2', 'list_type', 1);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPItoST43($_EXTKEY, 'pi3/class.tx_lthsolr_pi3.php', '_pi3', 'list_type', 1);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPItoST43($_EXTKEY, 'pi4/class.tx_lthsolr_pi4.php', '_pi4', 'list_type', 1);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPItoST43($_EXTKEY, 'pi5/class.tx_lthsolr_pi5.php', '_pi5', 'list_type', 1);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPItoST43($_EXTKEY, 'pi6/class.tx_lthsolr_pi6.php', '_pi6', 'list_type', 1);