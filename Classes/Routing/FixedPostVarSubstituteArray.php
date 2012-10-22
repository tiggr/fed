<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2012 Claus Due, Wildside A/S <claus@wildside.dk>
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * FixedPostVar  Substitute Array
 *
 * @package Fed
 * @subpackage Routing
 */
class Tx_Fed_Routing_FixedPostVarSubstituteArray extends Tx_Fed_Routing_AbstractSubstituteArray {

	/**
	 * @var array
	 */
	protected $extbaseUserFunctionIdentifiers = array('tx_extbase_core_bootstrap->run', 'tx_fed_core_bootstrap->run');

	/**
	 * @param array $existing
	 */
	public function __construct($existing = array()) {
		parent::__construct($existing);
		#$this->initializeObject();
	}

	/**
	 * Initialize this object
	 * @return void
	 */
	public function initializeObject() {
		$GLOBALS['SIM_ACCESS_TIME'] = time() - 86400;
		$GLOBALS['TT'] = new t3lib_timeTrack();
		$GLOBALS['TT']->start();
		$GLOBALS['TYPO3_DB'] = new t3lib_DB();
		$GLOBALS['TSFE'] = t3lib_div::makeInstance('tslib_fe',
			$GLOBALS['TYPO3_CONF_VARS'],
			t3lib_div::_GP('id'),
			t3lib_div::_GP('type'),
			t3lib_div::_GP('no_cache'),
			t3lib_div::_GP('cHash'),
			t3lib_div::_GP('jumpurl'),
			t3lib_div::_GP('MP'),
			t3lib_div::_GP('RDCT')
		);
		$GLOBALS['TSFE']->sys_page->where_hid_del = ' AND 1=1 ';
		$GLOBALS['TSFE']->sys_page->where_groupAccess = ' AND 1=1 ';
		$GLOBALS['TSFE']->connectToDB();
		$GLOBALS['TSFE']->initFEuser();
		$GLOBALS['TSFE']->checkAlternativeIdMethods();
		$GLOBALS['TSFE']->determineId();
		$GLOBALS['TSFE']->clear_preview();
		$GLOBALS['TSFE']->tmpl->forceTemplateParsing = 1;
		$GLOBALS['SIM_ACCESS_TIME'] = time() - 86400;
		$GLOBALS['TSFE']->initTemplate();
		$GLOBALS['TSFE']->getFromCache();
		$GLOBALS['TSFE']->getConfigArray();
		//return;
		$allContentOnPage = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('DISTINCT CType,list_type', 'tt_content', "pid = '" . $pageUid . "'");
		$extensionsAndPluginNames = array();
		foreach ($allContentOnPage as $contentRecord) {
			$setup = $this->getSetupForRecord($contentRecord);
			if ($this->assertIsExtbasePlugin($contentRecord, $setup) == FALSE) {
				continue;
			}
			$extensionName = $this->getArrayValueRecursive($setup, 'extensionName');
			$pluginName = $this->getArrayValueRecursive($setup, 'pluginName');
			$identity = strtolower($extensionName) . '_' . strtolower($pluginName);
			array_push($extensionsAndPluginNames, $identity);
		}
		$definitions = $this->buildFixedPostVarSetsForExtensionsAndPluginNames($extensionsAndPluginNames);
		#var_dump($definitions);
		#exit();
		unset($GLOBALS['TYPO3_DB'], $GLOBALS['TSFE'], $GLOBALS['TT']);
	}

	/**
	 * Builds and stores internally the fixed post var sets for all
	 * extensions and plugin names in $extensionsAndPluginNames
	 *
	 * @param array $extensionsAndPluginNames
	 * @return void
	 */
	protected function buildFixedPostVarSetsForExtensionsAndPluginNames($extensionsAndPluginNames) {
		$definitions = array();
		foreach ($extensionsAndPluginNames as $extensionAndPluginName) {
			list ($extensionName, $pluginName) = explode('->', $extensionAndPluginName);
			$actions = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['extbase']['extensions'][$extensionName]['plugins'][$pluginName]['controllers'];
			foreach ($actions as $controllerName => $commaSeparatedActions) {
				$actions = t3lib_div::trimExplode(',', $actions, TRUE);
				foreach ($actions as $actionName) {
					$identity = $extensionName . '_' . $pluginName . '_' . $controllerName . '_' . $actionName;
					$controllerClassName = 'Tx_' . $extensionName . '_Controller_' . $controllerName . 'Controller';
					$controllerClassReflection = new ReflectionClass($controllerClassName);
					$methodReflection = $controllerClassReflection->getMethod($actionName . 'Action');
					$arguments = $methodReflection->getParameters();
					$fixedPostVarSets = array();
					foreach ($arguments as $argumentReflection) {
						array_push($fixedPostVarSets, $this->buildFixedPostVarSetForControllerActionArgument($argumentReflection, $actionName));
					}
					$definitions[$identity] = $fixedPostVarSets;
				}
			}
		}
		return $definitions;
	}

	/**
	 * @param ReflectionClass $controllerClassReflection
	 * @param string $actionName
	 * @param string $urlPrefix
	 * @return array
	 */
	protected function buildFixedPostVarSetForControllerActionArgument(ReflectionParameter $controllerClassReflection, $actionName, $urlPrefix) {
		$definition = array(
			'GETvar' => $urlPrefix . '[' . $argumentName . ']',
		);
		if ($argument->isDefaultValueAvailable()) {
			$definition['noMatch'] = 'bypass';
		}
		return $definition;
	}

	/**
	 * @param array $contentRecord
	 * @return array|NULL
	 */
	protected function getSetupForRecord($contentRecord) {
		$typoScriptDefinition = $GLOBALS['TSFE']->tmpl->setup['tt_content.'];
		if ($contentRecord['list_type'] && isset($typoScriptDefinition['list.']['20.'][$contentRecord['list_type'] . '.'])) {
			$setup = $typoScriptDefinition['list.']['20.'][$contentRecord['list_type'] . '.'];
		} elseif ($contentRecord['CType']) {
			$setup = $typoScriptDefinition[$contentRecord['CType'] . '.'];
		} else {
			$setup = NULL;
		}
		return $setup;
	}

	/**
	 * Asserts wether or not a content record renders an Extbase plugin
	 *
	 * @param array $contentRecord
	 * @param array|NULL $setup
	 * @return boolean
	 */
	protected function assertIsExtbasePlugin($contentRecord, $setup = NULL) {
		if ($setup === NULL) {
			$setup = $this->getSetupForRecord($contentRecord);
		}
		if ($setup === NULL) {
			return FALSE;
		}
		if ($this->assertArrayContainsValueRecursive($setup, $this->extbaseUserFunctionIdentifiers)) {
			return TRUE;
		}
		return FALSE;
	}

	/**
	 * @param array $array
	 * @param mixed $value
	 * @return mixed
	 */
	protected function getArrayValueRecursive(array $array, $value) {
		foreach ($array as $key => $member) {
			if (is_array($member) && $this->getArrayValueRecursive($member, $value)) {
				return $member;
			} elseif (is_array($value) && in_array($member, $value)) {
				return $member;
			} elseif ($key == $value) {
				return $member;
			}
		}
		return NULL;
	}

	/**
	 * @param array $array
	 * @param mixed $value
	 * @return boolean
	 */
	protected function assertArrayContainsValueRecursive(array $array, $value) {
		foreach ($array as $member) {
			if (is_array($member) && $this->assertArrayContainsValueRecursive($member, $value)) {
				return $member;
			} elseif (is_array($value) && in_array($member, $value)) {
				return $member;
			} elseif ($member == $value) {
				return $member;
			}
		}
		return FALSE;
	}

}
