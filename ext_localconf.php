<?php
if (!defined('TYPO3_MODE')) {
	die('Access denied.');
}
if (!$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['fed']['setup']) {
	$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['fed']['setup'] = unserialize($_EXTCONF);
}
$loadBackendConfiguration = (TYPO3_MODE === 'BE' || t3lib_extMgm::isLoaded('feeditadvanced') || t3lib_extMgm::isLoaded('feedit'));

Tx_Extbase_Utility_Extension::configurePlugin(
	$_EXTKEY,
	'API',
	array(
		'Page' => 'render',
		'Hash' => 'request',
	),
	array(
		'Hash' => 'request',
	)
);

Tx_Extbase_Utility_Extension::configurePlugin(
	$_EXTKEY,
	'Fce',
	array(
		'FlexibleContentElement' => 'render',
	),
	array(
	),
	Tx_Extbase_Utility_Extension::PLUGIN_TYPE_CONTENT_ELEMENT
);

if ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['fed']['setup']['enableFrontendPlugins']) {

	Tx_Extbase_Utility_Extension::configurePlugin(
		$_EXTKEY,
		'Template',
		array(
			'Template' => 'show',
		),
		array(
		),
		Tx_Extbase_Utility_Extension::PLUGIN_TYPE_CONTENT_ELEMENT
	);

	Tx_Extbase_Utility_Extension::configurePlugin(
		$_EXTKEY,
		'Datasource',
		array(
			'DataSource' => 'list,show,rest',
		),
		array(
			'DataSource' => 'rest',
		),
		Tx_Extbase_Utility_Extension::PLUGIN_TYPE_CONTENT_ELEMENT
	);
}

if ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['fed']['setup']['enableAutoRouting']) {
	$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/realurl/class.tx_realurl_autoconfgen.php']['extensionConfiguration']['fed'] = 'Tx_Fed_Routing_AutoConfigurationGenerator->buildAutomaticRules';
}

if ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['fed']['setup']['enableFluidPageTemplates']) {
	if (!$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['fed']['setup']['disableAutomaticTypoScriptInclusion']) {
		t3lib_extMgm::addTypoScriptSetup('<INCLUDE_TYPOSCRIPT: source="FILE:EXT:fed/Configuration/TypoScript/Page/setup.txt">');
		if ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['fed']['setup']['enableFallbackFluidPageTemplate']) {
			t3lib_extMgm::addTypoScriptSetup('<INCLUDE_TYPOSCRIPT: source="FILE:EXT:fed/Configuration/TypoScript/Page/fallbacktemplate.txt">');
		}
	}
	$GLOBALS['TYPO3_CONF_VARS']['FE']['addRootLineFields'] .= ($GLOBALS['TYPO3_CONF_VARS']['FE']['addRootLineFields'] == '' ? '' : ',') . 'tx_fed_page_controller_action,tx_fed_page_controller_action_sub,tx_fed_page_flexform,';
}

if ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['fed']['setup']['enableSolrFeatures']) {
	Tx_Extbase_Utility_Extension::configurePlugin(
		$_EXTKEY,
		'Solr',
		array(
			'Solr' => 'form,search',
		),
		array(
			'Solr' => 'search',
		),
		Tx_Extbase_Utility_Extension::PLUGIN_TYPE_CONTENT_ELEMENT
	);
	if (!$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['fed']['setup']['disableAutomaticTypoScriptInclusion']) {
		t3lib_extMgm::addTypoScriptSetup('<INCLUDE_TYPOSCRIPT: source="FILE:EXT:fed/Configuration/TypoScript/Solr/setup.txt">');
	}
}

$fedWizardElements = array();
if ($loadBackendConfiguration) {
	if ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['fed']['setup']['enableSolrFeatures']
	|| $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['fed']['setup']['enableFrontendPlugins']
	) {
		array_push($fedWizardElements, 'template');
		array_push($fedWizardElements, 'datasource');
		array_push($fedWizardElements, 'solr');
	}

	Tx_Fed_Core::loadRegisteredFluidContentElementTypoScript();

	if ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['fed']['setup']['enableFrontendPlugins']) {

		t3lib_extMgm::addPageTSConfig('
			mod.wizards.newContentElement.wizardItems.fed.elements.template {
				icon = ../typo3conf/ext/fed/Resources/Public/Icons/Plugin.png
				title = Template Display
				description = Flexible Content Element using a Fluid template
				tt_content_defValues {
					CType = list
					list_type = fed_template
				}
			}
			mod.wizards.newContentElement.wizardItems.fed.elements.datasource {
				icon = ../typo3conf/ext/fed/Resources/Public/Icons/Plugin.png
				title = DataSource Display
				description = DataSource Display through Fluid Template
				tt_content_defValues {
					CType = list
					list_type = fed_datasource
				}
			}
		');
	}

	if ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['fed']['setup']['enableSolrFeatures']) {

		t3lib_extMgm::addPageTSConfig('
			mod.wizards.newContentElement.wizardItems.fed.elements.solr {
				icon = ../typo3conf/ext/fed/Resources/Public/Icons/Plugin.png
				title = Solr AJAX Search Form and Results
				description = Inserts a Solr search form configured by TypoScript settings for the "solr" extension.
				tt_content_defValues {
					CType = fed_solr
				}
			}
		');
	}

	$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass']['fed'] = 'EXT:fed/Classes/Backend/TCEMain.php:Tx_Fed_Backend_TCEMain';
	$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processCmdmapClass']['fed'] = 'EXT:fed/Classes/Backend/TCEMain.php:Tx_Fed_Backend_TCEMain';
	$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['moveRecordClass']['fed'] = 'EXT:fed/Classes/Backend/TCEMain.php:Tx_Fed_Backend_TCEMain';

	if ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['fed']['setup']['enableIntegratedBackendLayouts']) {
		$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['cms/classes/class.tx_cms_backendlayout.php']['tx_cms_BackendLayout']['fed'] = 'EXT:fed/Classes/Backend/BackendLayout.php:Tx_Fed_Backend_BackendLayout';
	}
}

if ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['fed']['setup']['increaseExtbaseCacheLifetime']) {
	$GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['cache_extbase_reflection']['options']['defaultLifetime'] = 86400;
}

$TYPO3_CONF_VARS['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['clearCachePostProc'][] = 'EXT:fed/Classes/Backend/TCEMain.php:&Tx_Fed_Backend_TCEMain->clearCacheCommand';

unset($loadBackendConfiguration);