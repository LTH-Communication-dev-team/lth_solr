<?php
// ******************************************************************
// This is the standard 
// ******************************************************************
$TCA['tx_lthsolr_intro'] = Array (
	'ctrl' => $TCA['tx_lthsolr_intro']['ctrl'],
	'interface' => Array (
		'showRecordFieldList' => 'lth_solr_intro_host, lth_solr_intro_text'
	),
	'columns' => Array (
 		'lth_solr_intro_host' => Array (
 			'label' => 'lth_solr_intro_host',
			'l10n_mode' => $l10n_mode,
 			'config' => Array (
 				'type' => 'input',
 				'size' => '40',
 				'max' => '256'
 			)
 		),
		'lth_solr_intro_text' => Array (
 			'label' => 'lth_solr_intro_text',
			'l10n_mode' => $l10n_mode,
 			'config' => Array (
 				'type' => 'input',
 				'size' => '40',
 				'max' => '256'
 			)
 		),
"hidden" => Array (		
			"exclude" => 1,
			"label" => "deleted",
			"config" => Array (
				"type" => "check",
				"default" => "0"
			)
		),
            "deleted" => Array (		
			"exclude" => 1,
			"label" => "hidden",
			"config" => Array (
				"type" => "check",
				"default" => "0"
			)
		),

		/**
		 * The following fields have to be configured here to get them processed by the listview in the tt_news BE module
		 * they should never appear in the 'showitem' list as editable fields, though.
		 */
		'uid' => Array (
			'label' => 'uid',
			'config' => Array (
				'type' => 'none'
			)
		),
		'pid' => Array (
			'label' => 'pid',
			'config' => Array (
				'type' => 'none'
			)
		),
		'tstamp' => Array (
			'label' => 'tstamp',
			'config' => Array (
				'type' => 'input',
				'eval' => 'datetime',
			)
		),

	),
);