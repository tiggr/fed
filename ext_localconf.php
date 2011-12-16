<?php
if (!defined ('TYPO3_MODE')) {
	die ('Access denied.');
}


Tx_Extbase_Utility_Extension::configurePlugin(
	$_EXTKEY,
	'API',
	array(
		'Page' => 'render',
		'Hash' => 'request',
		'Tool' => 'clearCache,inspectCookie,removeCookie,setCookie,inspectSession,setSession,removeSession,validate'
	),
	array(
		'Hash' => 'request',
		'Tool' => 'clearCache,inspectCookie,removeCookie,setCookie,inspectSession,setSession,removeSession,validate'
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

Tx_Extbase_Utility_Extension::configurePlugin(
	$_EXTKEY,
	'Template',
	array(
		'Template' => 'show',
	),
	array(
	)
);


Tx_Extbase_Utility_Extension::configurePlugin(
	$_EXTKEY,
	'Datasource',
	array(
		'DataSource' => 'list,show,rest',
	),
	array(
		'DataSource' => 'rest',
	)
);

Tx_Extbase_Utility_Extension::configurePlugin(
	$_EXTKEY,
	'Hash',
	array(
		'Hash' => 'request',
	),
	array(
		'Hash' => 'request',
	)
);

Tx_Extbase_Utility_Extension::configurePlugin(
	$_EXTKEY,
	'Solr',
	array(
		'Solr' => 'search',
	),
	array(
		'Solr' => 'search',
	)
);

t3lib_extMgm::addTypoScript($_EXTKEY, 'setup', "
	[GLOBAL]
	config.tx_extbase.persistence.classes.Tx_Fed_Persistence_FileObjectStorage.mapping {
		tableName = 0
	}
	FedFrameworkBridge = PAGE
	FedFrameworkBridge {
		typeNum = 4815162342
		config {
			no_cache = 1
			disableAllHeaderCode = 1
		}
		headerData >
		4815162342 = USER_INT
		4815162342 {
			userFunc = tx_fed_core_bootstrap->run
			extensionName = Fed
			pluginName = API
		}
	}

	FedPDFBridge = PAGE
	FedPDFBridge {
		typeNum = 48151623420
		config {
			no_cache = 1
			disableAllHeaderCode = 1
		}
		headerData >
		4815162342 = USER_INT
		4815162342 {
			userFunc = tx_fed_utility_pdf->run
			extensionName = Fed
			pluginName = API
		}
	}

	FedSolrBridge = PAGE
	FedSolrBridge {
		typeNum = 1324054607
		config {
			no_cache = 1
			disableAllHeaderCode = 1
		}
		headerData >
		1324054607 = USER_INT
		1324054607 {
			userFunc = tx_fed_core_bootstrap->run
			extensionName = Fed
			pluginName = Solr
		}
	}
");

$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['fed']['setup'] = unserialize($_EXTCONF);

if ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['fed']['setup']['enableFluidPageTemplates']) {
	t3lib_extMgm::addTypoScript($_EXTKEY,'setup',
		'[GLOBAL]
		page = PAGE
		page.typeNum = 0
		page.5 = USER
		page.5.userFunc = tx_fed_core_bootstrap->run
		page.5.extensionName = Fed
		page.5.pluginName = API
		page.10 >
	', TRUE);
	$GLOBALS['TYPO3_CONF_VARS']['FE']['addRootLineFields'] .= ($GLOBALS['TYPO3_CONF_VARS']['FE']['addRootLineFields'] == '' ? '' : ',') . 'tx_fed_page_controller_action,tx_fed_page_controller_action_sub,tx_fed_page_flexform,';
}

if (TYPO3_MODE == 'BE' && $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['fed']['setup']['enableFluidContentElements']) {

	t3lib_extMgm::addPageTSConfig('
		mod.wizards.newContentElement.wizardItems.fed {
			header = Fluid Content Elements
			elements fce
			show = fce,template,datasource
			position = 0
		}
		mod.wizards.newContentElement.wizardItems.fed.elements.fce {
			icon = ../typo3conf/ext/fed/Resources/Public/Icons/Plugin.png
			title = Flexible Content Element
			description = Flexible Content Element using a Fluid template
			tt_content_defValues {
				CType = fed_fce
			}
		}
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

if (TYPO3_MODE == 'BE') {
	$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass']['fed'] = 'EXT:fed/Classes/Backend/TCEMain.php:Tx_Fed_Backend_TCEMain';
	$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processCmdmapClass']['fed'] = 'EXT:fed/Classes/Backend/TCEMain.php:Tx_Fed_Backend_TCEMain';
	$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['moveRecordClass']['fed'] = 'EXT:fed/Classes/Backend/TCEMain.php:Tx_Fed_Backend_TCEMain';
	$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_befunc.php']['getFlexFormDSClass']['fed'] = 'EXT:fed/Classes/Backend/DynamicFlexForm.php:Tx_Fed_Backend_DynamicFlexForm';
	$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['cms/layout/class.tx_cms_layout.php']['tt_content_drawItem']['fed'] = 'EXT:fed/Classes/Backend/Preview.php:Tx_Fed_Backend_Preview';
	$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['typo3/class.db_list.inc']['makeQueryArray']['fed'] = 'EXT:fed/Classes/Backend/MakeQueryArray.php:Tx_Fed_Backend_MakeQueryArray';
	$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['templavoila']['mod1']['renderPreviewContent']['fed_fce'] = 'EXT:fed/Classes/Backend/TemplaVoilaPreview.php:&Tx_Fed_Backend_TemplaVoilaPreview';

	if ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['fed']['setup']['enableIntegratedBackendLayouts']) {
		$GLOBALS['TYPO3_CONF_VARS']['BE']['XCLASS']['ext/cms/classes/class.tx_cms_backendlayout.php'] = t3lib_extMgm::extPath('fed', 'class.ux_cms_backendlayout.php');
	}

}

if ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['fed']['setup']['increaseExtbaseCacheLifetime']) {
	$GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['cache_extbase_reflection']['options']['defaultLifetime'] = 86400;
}



?>