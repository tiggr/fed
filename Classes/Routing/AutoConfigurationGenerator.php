<?php
class Tx_Fed_Routing_AutoConfigurationGenerator {

	/**
	 * @param array $config
	 * @param string $extKey
	 */
	public function buildAutomaticRules(&$config, &$extKey) {
		$extensionsAndPluginNames = array();
		foreach ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['extbase']['extensions'] as $extensionName => $extensionConfiguration) {
			foreach ($extensionConfiguration['plugins'] as $pluginName => $pluginConfiguration) {
				array_push($extensionsAndPluginNames, $extensionName . '->' . $pluginName);
				unset($pluginConfiguration);
			}
			unset($extensionConfiguration);
		}
		$definitions = $this->buildFixedPostVarSetsForExtensionsAndPluginNames($extensionsAndPluginNames);
		#header("Content-type: text/plain");
		#var_dump($definitions);
		#var_dump($extensionsAndPluginNames);
		#syslog(LOG_ERR, var_export($extensionsAndPluginNames, TRUE));
		#syslog(LOG_ERR, var_export($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['extbase']['extensions'], TRUE));
		#syslog(LOG_ERR, 'test');
		#syslog(LOG_ERR, var_export($definitions, TRUE));
		#exit();
		$config['fixedPostVars']['_DEFAULT'] = $definitions;
		#var_dump($config);
		#exit();
		return $config;
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
			foreach ($actions as $controllerName => $controllerConfiguration) {
				foreach ($controllerConfiguration['actions'] as $actionName) {
					$identity = $extensionName . '_' . $pluginName . '_' . $controllerName . '_' . $actionName;
					$controllerClassName = 'Tx_' . $extensionName . '_Controller_' . $controllerName . 'Controller';
					$controllerClassReflection = new ReflectionClass($controllerClassName);
					if (method_exists($controllerClassName, $actionName . 'Action') === FALSE) {
						continue;
					}
					$methodReflection = $controllerClassReflection->getMethod($actionName . 'Action');
					$arguments = $methodReflection->getParameters();
					$urlPrefix = 'tx_' . strtolower(str_replace('_', '', $extensionName) . '_' . str_replace('_', '', $pluginName));
					$fixedPostVarSets = array();
					foreach ($arguments as $argumentReflection) {
						array_push($fixedPostVarSets, $this->buildFixedPostVarSetForControllerActionArgument($argumentReflection, $actionName, $urlPrefix));
					}
					#$pageUids = $this->getAllPAgeUidsWherePluginOccursFirstInColPosZero()
					$definitions[$identity] = $fixedPostVarSets;
				}
			}
		}
		return $definitions;
	}

	/**
	 * @param ReflectionParameter $argument
	 * @param string $actionName
	 * @param string $urlPrefix
	 * @return array
	 */
	protected function buildFixedPostVarSetForControllerActionArgument(ReflectionParameter $argument, $actionName, $urlPrefix) {
		$argumentName = $argument->getName();
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