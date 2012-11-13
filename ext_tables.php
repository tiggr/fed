<?php
if (!defined('TYPO3_MODE')){
	die('Access denied.');
}

if (!$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['fed']['setup']) {
	$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['fed']['setup'] = unserialize($_EXTCONF);
}

$loadBackendConfiguration = (TYPO3_MODE === 'BE' || t3lib_extMgm::isLoaded('feeditadvanced') || t3lib_extMgm::isLoaded('feedit'));

t3lib_div::loadTCA('tt_content');

if ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['fed']['setup']['enableSolrFeatures']) {
	Tx_Extbase_Utility_Extension::registerPlugin(
		$_EXTKEY,
		'Solr',
		'Solr AJAX Search'
	);
	Tx_Flux_Core::registerFluidFlexFormContentObject(
		t3lib_extMgm::extPath($_EXTKEY, 'Resources/Private/Templates/Solr/Form.html'),
		'fed_solr',
		array(),
		'Configuration',
		array(
			'templateRootPath' => 'EXT:fed/Resources/Private/Templates/',
			'layoutRootPath' => 'EXT:fed/Resources/Private/Layouts/',
			'partialRootPath' => 'EXT:fed/Resources/Private/Partials/',
		));
	$TCA['tt_content']['types']['fed_solr']['showitem'] = '
	--palette--;LLL:EXT:cms/locallang_ttc.xml:palette.general;general,
	--palette--;LLL:EXT:cms/locallang_ttc.xml:palette.header;header,
	--div--;Solr, pi_flexform;Solr settings,
	--div--;LLL:EXT:cms/locallang_ttc.xml:tabs.appearance,
	--palette--;LLL:EXT:cms/locallang_ttc.xml:palette.frames;frames,
	--div--;LLL:EXT:cms/locallang_ttc.xml:tabs.access,
	--palette--;LLL:EXT:cms/locallang_ttc.xml:palette.visibility;visibility,
	--palette--;LLL:EXT:cms/locallang_ttc.xml:palette.access;access
	';
}

if ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['fed']['setup']['enableFrontendPlugins']) {
	$pluginSignature = str_replace('_', '', $_EXTKEY) . '_template';
	$TCA['tt_content']['types']['list']['subtypes_addlist'][$pluginSignature] = 'pi_flexform';
	t3lib_extMgm::addPiFlexFormValue($pluginSignature, 'FILE:EXT:' . $_EXTKEY . '/Configuration/FlexForms/Template.xml');

	$pluginSignature = str_replace('_', '', $_EXTKEY) . '_datasource';
	$TCA['tt_content']['types']['list']['subtypes_addlist'][$pluginSignature] = 'pi_flexform';
	t3lib_extMgm::addPiFlexFormValue($pluginSignature, 'FILE:EXT:' . $_EXTKEY . '/Configuration/FlexForms/DataSource.xml');

	Tx_Extbase_Utility_Extension::registerPlugin(
		$_EXTKEY,
		'Template',
		'Fluid Template Display'
	);

	Tx_Extbase_Utility_Extension::registerPlugin(
		$_EXTKEY,
		'Datasource',
		'Data Source Display'
	);
}

if ($loadBackendConfiguration) {
	$versionNumbers = explode('.', TYPO3_version);
	if ($versionNumbers[0] >= 4 && $versionNumbers[1] >= 6) {
		$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks']['Tx_Fed_Scheduler_Task'] = array(
			'extension'        => $_EXTKEY,
			'title'            => 'LLL:EXT:' . $_EXTKEY . '/Resources/Private/Language/locallang.xml:task.name',
			'description'      => 'LLL:EXT:' . $_EXTKEY . '/Resources/Private/Language/locallang.xml:task.description',
			'additionalFields' => 'Tx_Fed_Scheduler_FieldProvider'
		);
	}

	$TCA['tt_content']['types']['list']['subtypes_addlist']['fed_sandbox'] = 'pi_flexform';

	$GLOBALS['TCA']['tt_content']['ctrl']['typeicon_classes']['fed_fce'] = 'apps-pagetree-root';
	$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['extbase']['extensions']['fed']['plugins']['fed_fce']['pluginType'] = 'CType';
	Tx_Extbase_Utility_Extension::registerPlugin(
		$_EXTKEY,
		'Fce',
		'Fluid Content',
		t3lib_extMgm::extRelPath('fed') . 'ext_icon.gif'
	);
	t3lib_extMgm::addPlugin(array('Fluid Content', 'fed_fce', t3lib_extMgm::extRelPath('fed') . 'ext_icon.gif'), 'CType');
	Tx_Flux_Core::registerConfigurationProvider('Tx_Fed_Provider_Configuration_ContentObjectConfigurationProvider');

	$TCA['tt_content']['types']['list']['subtypes_addlist']['fed_fce'] = 'pi_flexform';
	$TCA['tt_content']['types']['fed_fce']['showitem'] = '
	--palette--;LLL:EXT:cms/locallang_ttc.xml:palette.general;general,
	--palette--;LLL:EXT:cms/locallang_ttc.xml:palette.header;header,
	--div--;Content settings, tx_fed_fcefile;Element type, pi_flexform;Configuration,
	--div--;LLL:EXT:cms/locallang_ttc.xml:tabs.appearance,
	--palette--;LLL:EXT:cms/locallang_ttc.xml:palette.frames;frames,
	--div--;LLL:EXT:cms/locallang_ttc.xml:tabs.access,
	--palette--;LLL:EXT:cms/locallang_ttc.xml:palette.visibility;visibility,
	--palette--;LLL:EXT:cms/locallang_ttc.xml:palette.access;access
	 ';

	if ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['fed']['setup']['enableFluidPageTemplates']) {
		t3lib_div::loadTCA('pages');
		t3lib_extMgm::addTCAcolumns('pages', array(
			'tx_fed_page_controller_action' => array (
				'exclude' => 1,
				'label' => 'LLL:EXT:fed/Resources/Private/Language/locallang_db.xml:pages.tx_fed_page_controller_action',
				'config' => array (
					'type' => 'user',
					'userFunc' => 'Tx_Fed_Backend_PageLayoutSelector->renderField'
				)
			),
			'tx_fed_page_controller_action_sub' => array (
				'exclude' => 1,
				'label' => 'LLL:EXT:fed/Resources/Private/Language/locallang_db.xml:pages.tx_fed_page_controller_action_sub',
				'config' => array (
					'type' => 'user',
					'userFunc' => 'Tx_Fed_Backend_PageLayoutSelector->renderField'
				)
			),
			'tx_fed_page_flexform' => Array (
				'exclude' => 1,
				'label' => 'LLL:EXT:fed/Resources/Private/Language/locallang_db.xml:pages.tx_fed_page_flexform',
				'config' => array (
					'type' => 'flex',
				)
			),
		), 1);
		t3lib_extMgm::addToAllTCAtypes(
			'pages',
			'tx_fed_page_controller_action,tx_fed_page_controller_action_sub,tx_fed_page_flexform',
			'0,1,4',
			'before:layout'
		);
		Tx_Flux_Core::registerConfigurationProvider('Tx_Fed_Provider_Configuration_PageConfigurationProvider');
	}

	t3lib_extMgm::addStaticFile($_EXTKEY, 'Configuration/TypoScript', 'FED Fluid Extbase Development Framework');
	t3lib_extMgm::addStaticFile($_EXTKEY, 'Configuration/TypoScript/PdfBridge', 'FED PDF Bridge - Used by PDF ViewHelper');
	t3lib_extMgm::addStaticFile($_EXTKEY, 'Configuration/TypoScript/FrameworkBridge', 'FED Framework Bridge - Used by some ViewHelpers (AJAX)');

	$TCA['tt_content']['types']['fed_template']['showitem'] = '
	--palette--;LLL:EXT:cms/locallang_ttc.xml:palette.general;general,
	--palette--;LLL:EXT:cms/locallang_ttc.xml:palette.header;header,
	--div--;Fluid Template, pi_flexform;Fluid Template settings,
	--div--;LLL:EXT:cms/locallang_ttc.xml:tabs.appearance,
	--palette--;LLL:EXT:cms/locallang_ttc.xml:palette.frames;frames,
	--div--;LLL:EXT:cms/locallang_ttc.xml:tabs.access,
	--palette--;LLL:EXT:cms/locallang_ttc.xml:palette.visibility;visibility,
	--palette--;LLL:EXT:cms/locallang_ttc.xml:palette.access;access
	';
	$TCA['tt_content']['types']['fed_datasource']['showitem'] = '
	--palette--;LLL:EXT:cms/locallang_ttc.xml:palette.general;general,
	--palette--;LLL:EXT:cms/locallang_ttc.xml:palette.header;header,
	--div--;DataSource Display, pi_flexform;DataSource Display settings,
	--div--;LLL:EXT:cms/locallang_ttc.xml:tabs.appearance,
	--palette--;LLL:EXT:cms/locallang_ttc.xml:palette.frames;frames,
	--div--;LLL:EXT:cms/locallang_ttc.xml:tabs.access,
	--palette--;LLL:EXT:cms/locallang_ttc.xml:palette.visibility;visibility,
	--palette--;LLL:EXT:cms/locallang_ttc.xml:palette.access;access
	';

	t3lib_extMgm::addLLrefForTCAdescr('tx_fed_domain_model_datasource', 'EXT:fed/Resources/Private/Language/locallang_csh_tx_fed_domain_model_datasource.xml');
	t3lib_extMgm::allowTableOnStandardPages('tx_fed_domain_model_datasource');
	$TCA['tx_fed_domain_model_datasource'] = array(
		'ctrl' => array(
			'title' => 'LLL:EXT:fed/Resources/Private/Language/locallang_db.xml:tx_fed_domain_model_datasource',
			'label' => 'name',
			'tstamp' => 'tstamp',
			'crdate' => 'crdate',
			'dividers2tabs' => TRUE,
			'versioningWS' => 2,
			'versioning_followPages' => TRUE,
			'origUid' => 't3_origuid',
			'languageField' => 'sys_language_uid',
			'transOrigPointerField' => 'l10n_parent',
			'transOrigDiffSourceField' => 'l10n_diffsource',
			'delete' => 'deleted',
			'enablecolumns' => array(
				'disabled' => 'hidden',
				'starttime' => 'starttime',
				'endtime' => 'endtime',
			),
			'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY) . 'Configuration/TCA/DataSource.php',
			'iconfile' => t3lib_extMgm::extRelPath($_EXTKEY) . 'Resources/Public/Icons/tx_fed_domain_model_datasource.gif'
		),
	);

	t3lib_extMgm::addTCAcolumns('tt_content', array(
		'tx_fed_fcefile' => array (
			'exclude' => 1,
			'label' => 'LLL:EXT:fed/Resources/Private/Language/locallang_db.xml:tt_content.tx_fed_fcefile',
			'config' => array (
				'type' => 'user',
				'userFunc' => 'Tx_Fed_Backend_FCESelector->renderField',
			)
		),
	), 1);

	require_once t3lib_extMgm::extPath($_EXTKEY, 'Configuration/Wizard/FlexFormCodeEditor.php');

}

if ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['fed']['setup']['increaseExtbaseCacheLifetime']) {
	if ($GLOBALS['typo3CacheManager']) {
		try {
			$GLOBALS['typo3CacheManager']->getCache('extbase_object')->getBackend()->setDefaultLifetime(86400);
		} catch (Exception $e) {
				// adjusting the caching lifetime this way only works on 4.6+ currently
		}
	}
}

unset($loadBackendConfiguration);