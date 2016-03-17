<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}

include_once(t3lib_extMgm::extPath($_EXTKEY).'class.tx_sampleflex_addFieldsToFlexForm.php');

$TCA['tt_content']['types']['list']['subtypes_addlist'][$_EXTKEY.'_pi2']='pi_flexform'; //New
t3lib_extMgm::addPiFlexFormValue($_EXTKEY.'_pi2', 'FILE:EXT:lth_solr/flexform_ds_pi2.xml'); //New


t3lib_div::loadTCA('tt_content');
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_pi1'] = 'layout,select_key,pages';

t3lib_extMgm::addPlugin(array(
	'LLL:EXT:lth_solr/locallang_db.xml:tt_content.list_type_pi1',
	$_EXTKEY . '_pi1',
	t3lib_extMgm::extRelPath($_EXTKEY) . 'ext_icon.gif'
),'list_type');

$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_pi2'] = 'layout,select_key,pages';

t3lib_extMgm::addPlugin(array(
	'LLL:EXT:lth_solr/locallang_db.xml:tt_content.list_type_pi2',
	$_EXTKEY . '_pi2',
	t3lib_extMgm::extRelPath($_EXTKEY) . 'ext_icon.gif'
),'list_type');