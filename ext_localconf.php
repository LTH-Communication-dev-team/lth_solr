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

//PublicationCleanUp
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks']['Lth\\Lthsolr\\Task\\PublicationCleanUp'] = array(
    'extension' => $_EXTKEY,
    'title' => 'Clean up publications',
    'description' => '',
    'additionalFields' => ''
);

//calenderImport
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks']['Lth\\Lthsolr\\Task\\CalendarImport'] = array(
    'extension' => $_EXTKEY,
    'title' => 'Import Calendar events',
    'description' => '',
    'additionalFields' => ''
);

//FixTtnewsSorting
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks']['Lth\\Lthsolr\\Task\\FixTtnewsSorting'] = array(
    'extension' => $_EXTKEY,
    'title' => 'Fix tt_news Sorting',
    'description' => '',
    'additionalFields' => ''
);

//FixTxnewsMedia
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks']['Lth\\Lthsolr\\Task\\FixTxnewsMedia'] = array(
    'extension' => $_EXTKEY,
    'title' => 'Fix tx_news media',
    'description' => '',
    'additionalFields' => ''
);

//JobImport
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks']['Lth\\Lthsolr\\Task\\JobImport'] = array(
    'extension' => $_EXTKEY,
    'title' => 'Job Import',
    'description' => 'Job Import',
    'additionalFields' => ''
);

//StatImport
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks']['Lth\\Lthsolr\\Task\\StatImport'] = array(
    'extension' => $_EXTKEY,
    'title' => 'Stat Import',
    'description' => 'Stat Import',
    'additionalFields' => ''
);

//OrganisationImport
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks']['Lth\\Lthsolr\\Task\\OrganisationImport'] = array(
    'extension' => $_EXTKEY,
    'title' => 'Organisation Import',
    'description' => 'Organisation Import',
    'additionalFields' => ''
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPItoST43($_EXTKEY, 'pi1/class.tx_lthsolr_pi1.php', '_pi1', 'list_type', 0);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPItoST43($_EXTKEY, 'pi2/class.tx_lthsolr_pi2.php', '_pi2', 'list_type', 0);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPItoST43($_EXTKEY, 'pi3/class.tx_lthsolr_pi3.php', '_pi3', 'list_type', 0);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPItoST43($_EXTKEY, 'pi4/class.tx_lthsolr_pi4.php', '_pi4', 'list_type', 0);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPItoST43($_EXTKEY, 'pi5/class.tx_lthsolr_pi5.php', '_pi5', 'list_type', 0);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPItoST43($_EXTKEY, 'pi6/class.tx_lthsolr_pi6.php', '_pi6', 'list_type', 0);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPItoST43($_EXTKEY, 'pi7/class.tx_lthsolr_pi7.php', '_pi7', 'list_type', 0);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPItoST43($_EXTKEY, 'pi8/class.tx_lthsolr_pi8.php', '_pi8', 'list_type', 0);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPItoST43($_EXTKEY, 'pi9/class.tx_lthsolr_pi9.php', '_pi9', 'list_type', 0);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPItoST43($_EXTKEY, 'pi10/class.tx_lthsolr_pi10.php', '_pi10', 'list_type', 0);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPItoST43($_EXTKEY, 'pi11/class.tx_lthsolr_pi11.php', '_pi11', 'list_type', 0);

$GLOBALS['TYPO3_CONF_VARS']['FE']['ContentObjects']['IMGTEXT'] = \Lth\Lthsolr\ContentObject\ImageTextContentObject::class;

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTypoScript('lth_solr', 'setup', '
    #############################################
    ## TypoScript added by extension "lth_solr"
    #############################################
    
    plugin.tx_lthsolr_pi1 = USER_INT
    plugin.tx_lthsolr_pi2 = USER_INT
    plugin.tx_lthsolr_pi3 = USER_INT
    plugin.tx_lthsolr_pi4 = USER_INT
    plugin.tx_lthsolr_pi5 = USER_INT
    plugin.tx_lthsolr_pi6 = USER_INT
    plugin.tx_lthsolr_pi7 = USER_INT
    plugin.tx_lthsolr_pi8 = USER_INT
    plugin.tx_lthsolr_pi9 = USER_INT
    plugin.tx_lthsolr_pi10 = USER_INT
    plugin.tx_lthsolr_pi11 = USER_INT
    
    rss = PAGE
    rss {
      typeNum = 100

      10 < plugin.tx_lthsolr_pi3     

      config {
        disableAllHeaderCode = 1
        additionalHeaders = Content-type:application/xml
        xhtml_cleaning = 0
        admPanel = 0
        debug = 0
        no_cache = 1
      }
    }
', 43);
