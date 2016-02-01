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

//t3lib_extMgm::addStaticFile($_EXTKEY, 'static/lth_solr_settings/', 'lth_solr');

/*
 * Extend fe_user table
 */
$tempColumns = array(
    'lth_solr_intro_id' => array(
        'exclude' => 1,
        'label'   => 'Introtexts',
 			"config" => Array (
				"type" => "inline",
				"foreign_table" => "tx_lthsolr_intro",
				"foreign_field" => "parentid",
				"foreign_table_field" => "parenttable",
				"maxitems" => 10,
			)
		),
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns    ( 'fe_users', $tempColumns, 1);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes ( 'fe_users', 'lth_solr_intro_id');

/*
 * Setup for table 'tx_lthsolr_intro'
 */
$TCA["tx_lthsolr_intro"] = Array (
	"ctrl" => Array (
		'title' => 'tx_lthsolr_intro',		
		'label' => 'uid',	
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		"default_sortby" => "ORDER BY crdate",	
		"delete" => "deleted",	
		"enablecolumns" => Array (		
			"disabled" => "hidden",
		),
		"dynamicConfigFile" => t3lib_extMgm::extPath($_EXTKEY)."tca.php",
		//"iconfile" => t3lib_extMgm::extRelPath($_EXTKEY)."icon_tx_utbildningar_lthbas.gif",
	),
	"feInterface" => Array (
		"fe_admin_fieldList" => "lth_solr_intro_host, lth_solr_intro_text",
	)
);