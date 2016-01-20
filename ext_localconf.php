<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}

// Add AJAX support
$TYPO3_CONF_VARS['FE']['eID_include']['lth_solr'] = 'EXT:lth_solr/service/ajax.php';

//Ajax in BE?
$TYPO3_CONF_VARS['BE']['AJAX']['lth_solr::ajaxControl'] = t3lib_extMgm::extPath('lth_solr').'service/be_ajax.php:lth_solr_ajax->ajaxControl';


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

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks']['tx_lthsolr_newsimport'] = array(
	'extension'        => $_EXTKEY,
	'title'            => 'News Import',
	'description'      => '',
	'additionalFields' => '',
);
t3lib_extMgm::addPItoST43($_EXTKEY, 'pi1/class.tx_lthsolr_pi1.php', '_pi1', 'list_type', 1);

t3lib_extMgm::addPItoST43($_EXTKEY, 'pi2/class.tx_lthsolr_pi2.php', '_pi2', 'list_type', 1);

$TYPO3_CONF_VARS['EXTCONF']['t3registration']['beforeUpdateUser'][] = 'EXT:lth_solr/hooks/class.lth_solr_hooks.php:lth_solr_hooks->beforeUpdateUser';
