<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}

include_once(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($_EXTKEY).'class.tx_sampleflex_addFieldsToFlexForm.php');

$TCA['tt_content']['types']['list']['subtypes_addlist'][$_EXTKEY.'_pi2']='pi_flexform'; //New
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue($_EXTKEY.'_pi2', 'FILE:EXT:lth_solr/flexform_ds_pi2.xml'); //New

$TCA['tt_content']['types']['list']['subtypes_addlist'][$_EXTKEY.'_pi3']='pi_flexform'; //New
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue($_EXTKEY.'_pi3', 'FILE:EXT:lth_solr/flexform_ds_pi3.xml'); //New

$TCA['tt_content']['types']['list']['subtypes_addlist'][$_EXTKEY.'_pi4']='pi_flexform'; //New
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue($_EXTKEY.'_pi4', 'FILE:EXT:lth_solr/flexform_ds_pi4.xml'); //New

$TCA['tt_content']['types']['list']['subtypes_addlist'][$_EXTKEY.'_pi5']='pi_flexform'; //New
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue($_EXTKEY.'_pi5', 'FILE:EXT:lth_solr/flexform_ds_pi5.xml'); //New

$TCA['tt_content']['types']['list']['subtypes_addlist'][$_EXTKEY.'_pi6']='pi_flexform'; //New
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue($_EXTKEY.'_pi6', 'FILE:EXT:lth_solr/flexform_ds_pi6.xml'); //New

t3lib_div::loadTCA('tt_content');

$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_pi1'] = 'layout,select_key,pages';

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPlugin(array(
	'LLL:EXT:lth_solr/locallang_db.xml:tt_content.list_type_pi1',
	$_EXTKEY . '_pi1',
	\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath($_EXTKEY) . 'ext_icon.gif'
),'list_type');

$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_pi2'] = 'layout,select_key,pages';

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPlugin(array(
	'LLL:EXT:lth_solr/locallang_db.xml:tt_content.list_type_pi2',
	$_EXTKEY . '_pi2',
	\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath($_EXTKEY) . 'ext_icon.gif'
),'list_type');

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPlugin(array(
	'LLL:EXT:lth_solr/locallang_db.xml:tt_content.list_type_pi3',
	$_EXTKEY . '_pi3',
	\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath($_EXTKEY) . 'ext_icon.gif'
),'list_type');

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPlugin(array(
	'LLL:EXT:lth_solr/locallang_db.xml:tt_content.list_type_pi4',
	$_EXTKEY . '_pi4',
	\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath($_EXTKEY) . 'ext_icon.gif'
),'list_type');

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPlugin(array(
	'LLL:EXT:lth_solr/locallang_db.xml:tt_content.list_type_pi5',
	$_EXTKEY . '_pi5',
	\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath($_EXTKEY) . 'ext_icon.gif'
),'list_type');

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPlugin(array(
	'LLL:EXT:lth_solr/locallang_db.xml:tt_content.list_type_pi6',
	$_EXTKEY . '_pi6',
	\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath($_EXTKEY) . 'ext_icon.gif'
),'list_type');