<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
    'lth.' . $_EXTKEY
);

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass'][] = 'LTH\\Lthsolr\\Hooks\\ProcessCmdmap';

// Add AJAX support
$TYPO3_CONF_VARS['FE']['eID_include']['lth_solr'] = 'EXT:lth_solr/service/ajax.php';

//Ajax in BE?
//$TYPO3_CONF_VARS['BE']['AJAX']['lth_solr::ajaxControl'] = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('lth_solr').'service/be_ajax.php:lth_solr_ajax->ajaxControl';


/*$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks']['tx_lthsolr_lucacheimport'] = array(
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
);*/

//LuCacheImport
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks']['Lth\\Lthsolr\\Task\\LuCacheImport'] = array(
    'extension' => $_EXTKEY,
    'title' => 'Lucache import',
    'description' => '',
    'additionalFields' => ''
);

//CourseImport
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks']['Lth\\Lthsolr\\Task\\CourseImport'] = array(
    'extension' => $_EXTKEY,
    'title' => 'Course import',
    'description' => '',
    'additionalFields' => ''
);

//DocumentImport
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks']['Lth\\Lthsolr\\Task\\DocumentImport'] = array(
    'extension' => $_EXTKEY,
    'title' => 'Document import',
    'description' => '',
    'additionalFields' => ''
);

//PublicationImport
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks']['Lth\\Lthsolr\\Task\\PublicationImport'] = array(
    'extension' => $_EXTKEY,
    'title' => 'Publication import',
    'description' => '',
    'additionalFields' => ''
);

//ProjectImport
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks']['Lth\\Lthsolr\\Task\\ProjectImport'] = array(
    'extension' => $_EXTKEY,
    'title' => 'Project import',
    'description' => '',
    'additionalFields' => ''
);

//StudentPaperImport
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks']['Lth\\Lthsolr\\Task\\StudentPaperImport'] = array(
    'extension' => $_EXTKEY,
    'title' => 'Student paper import',
    'description' => '',
    'additionalFields' => ''
);

//LucrisAddUuid
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks']['Lth\\Lthsolr\\Task\\AddLucrisUuid'] = array(
    'extension' => $_EXTKEY,
    'title' => 'Add lucris uuid',
    'description' => '',
    'additionalFields' => ''
);

//ConvertForms
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks']['Lth\\Lthsolr\\Task\\ConvertForms'] = array(
    'extension' => $_EXTKEY,
    'title' => 'Convert Forms',
    'description' => '',
    'additionalFields' => ''
);

//PublicationProjectImport
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks']['Lth\\Lthsolr\\Task\\PublicationProjectImport'] = array(
    'extension' => $_EXTKEY,
    'title' => 'Import projects for publications',
    'description' => '',
    'additionalFields' => ''
);


\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPItoST43($_EXTKEY, 'pi1/class.tx_lthsolr_pi1.php', '_pi1', 'list_type', 1);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPItoST43($_EXTKEY, 'pi2/class.tx_lthsolr_pi2.php', '_pi2', 'list_type', 1);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPItoST43($_EXTKEY, 'pi3/class.tx_lthsolr_pi3.php', '_pi3', 'list_type', 1);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPItoST43($_EXTKEY, 'pi4/class.tx_lthsolr_pi4.php', '_pi4', 'list_type', 1);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPItoST43($_EXTKEY, 'pi5/class.tx_lthsolr_pi5.php', '_pi5', 'list_type', 1);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPItoST43($_EXTKEY, 'pi6/class.tx_lthsolr_pi6.php', '_pi6', 'list_type', 1);

$GLOBALS['TYPO3_CONF_VARS']['FE']['ContentObjects']['IMGTEXT'] = \Lth\Lthsolr\ContentObject\ImageTextContentObject::class;

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTypoScript('lth_solr', 'setup', '
    #############################################
    ## TypoScript added by extension "lth_solr"
    #############################################
    
    config.no_cache=1
', 43);