<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2010 Claus Due <claus@wildside.dk>, Wildside A/S
 *
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
 * Exposes a model to ExtJS - generates a Model definition class file
 *
 * @package TYPO3
 * @subpackage Fluid
 * @version
 */
class Tx_Fed_ExtJS_ModelGenerator implements t3lib_Singleton {

	/**
	 * @var string
	 */
	public static $SPLIT_PATTERN_SHORTHANDSYNTAX = '/
		(
			{                                # Start of shorthand syntax
				(?:                          # Shorthand syntax is either composed of...
					[a-zA-Z0-9\->_:,.()]     # Various characters
					|"(?:\\\"|[^"])*"        # Double-quoted strings
					|\'(?:\\\\\'|[^\'])*\'   # Single-quoted strings
				)+
			}                                # End of shorthand syntax
		)/x';

	/**
	 * @var Tx_Extbase_MVC_Web_RequestBuilder
	 */
	protected $requestBuilder;

	/**
	 * @var Tx_Fed_Utility_DomainObjectInfo
	 */
	protected $infoService;

	/**
	 * ObjectManager instance
	 * @var Tx_Extbase_Object_ObjectManagerInterface
	 */
	protected $objectManager;

	/**
	 * @var Tx_Fed_Service_Json
	 */
	protected $jsonService;

	/**
	 * @var string
	 */
	protected $prefix = NULL;

	/**
	 * @var integer
	 */
	protected $typeNum;

	/**
	 * @param Tx_Extbase_MVC_Web_RequestBuilder $requestBuilder
	 * @return void
	 */
	public function injectRequestBuilder(Tx_Extbase_MVC_Web_RequestBuilder $requestBuilder) {
		$this->requestBuilder = $requestBuilder;
	}

	/**
	 * @param Tx_Fed_Service_Domain $infoService
	 */
	public function injectInfoService(Tx_Fed_Service_Domain $infoService) {
		$this->infoService = $infoService;
	}

	/**
	 * Inject a Reflection Service instance
	 * @param Tx_Extbase_Object_ObjectManagerInterface $manager
	 */
	public function injectObjectManager(Tx_Extbase_Object_ObjectManagerInterface $manager) {
		$this->objectManager = $manager;
	}

	/**
	 * @param Tx_Fed_Service_Json $jsonService
	 */
	public function injectJSONService(Tx_Fed_Service_Json $jsonService) {
		$this->jsonService = $jsonService;
	}

	/**
	 * @param string $prefix
	 */
	public function setPrefix($prefix) {
		$this->prefix = $prefix;
	}

	/**
	 * @return string
	 */
	public function getPrefix() {
		return $this->prefix;
	}

	/**
	 * @param integer $typeNum
	 */
	public function setTypeNum($typeNum) {
		$this->typeNum = $typeNum;
	}

	/**
	 * @return integer
	 */
	public function getTypeNum() {
		return $this->typeNum;
	}

	/**
	 * @param mixed $object Instance or classname of object for which to generate a model class
	 * @param array $properties Optional array of property names to expose - overrides @ExtJS annotation
	 * @param string $template Optional absolute path of Fluid template file which renders the Model class Javascript
	 * @return string
	 */
	public function generateModelClass($object, $properties = NULL, $template = NULL) {
		$className = is_object($object) ? get_class($object) : $object;
		$properties = $this->infoService->getPropertiesByAnnotation($object, 'ExtJS');
		$view = $this->objectManager->get('Tx_Fluid_View_StandAloneView');
		$prefix = $this->getPrefix();
		$shortTagSyntaxPatternBackup = Tx_Fluid_Core_Parser_TemplateParser::$SPLIT_PATTERN_SHORTHANDSYNTAX;
		Tx_Fluid_Core_Parser_TemplateParser::$SPLIT_PATTERN_SHORTHANDSYNTAX = self::$SPLIT_PATTERN_SHORTHANDSYNTAX;
		if ($template === NULL) {
			$template = $this->resolveTemplateFile($object);
		}
		$urls = array(
			'create' => $this->getStoreUri($object, 'create'),
			'read' => $this->getStoreUri($object, 'read'),
			'update' => $this->getStoreUri($object, 'update'),
			'destroy' => $this->getStoreUri($object, 'destroy'),
		);
		$view->setTemplatePathAndFilename($template);
		$view->assign('className', array_pop(explode('_', $className)));
		$view->assign('properties', $this->getPropertyDefinitions($object, $properties));
		$view->assign('typeNum', $this->typeNum);
		$view->assign('prefix', $prefix);
		$view->assign('urls', $urls);
		$content = $view->render();
		Tx_Fluid_Core_Parser_TemplateParser::$SPLIT_PATTERN_SHORTHANDSYNTAX = $shortTagSyntaxPatternBackup;
		return $content;
	}
	/**
	 * Resolve template file
	 *
	 * @param mixed $object
	 * @return string
	 */
	protected function resolveTemplateFile($object) {
		$default = t3lib_extMgm::extPath('fed', 'Resources/Private/Partials/DataSource/Model.js');
		$partialPath = $this->infoService->getPartialTemplatePath($object);
		$possibleFile = $partialPath . 'Model.js';
		$possibleRootFile = $partialPath . '../Model.js';
		if (file_exists($possibleFile)) {
			return $possibleFile;
		} elseif (is_file($possibleRootFile)) {
			return $possibleRootFile;
		} else {
			return $default;
		}
	}

	/**
	 * Get extra store URI parameters
	 *
	 * @param mixed object
	 * @return array
	 */
	protected function getExtraStoreUriParameters($object) {
		$data = array(
			$this->getRequestPrefix($object) => array(
				'controller' => $this->infoService->getControllerName($object),
				'extension' => $this->infoService->getExtensionName($object),
				'action' => 'rest'
			)
		);
		return $data;
	}

	/**
	 * Get store URI
	 *
	 * @param mixed $object
	 * @param string $actionName
	 * @return string The rendered URI
	 */
	protected function getStoreUri($object, $actionName) {
		$uriBuilder = $this->objectManager->get('Tx_Extbase_MVC_Web_Routing_UriBuilder');
		$uriBuilder->setTargetPageType($this->typeNum);
		$uriBuilder->setTargetPageUid($GLOBALS['TSFE']->id);
		$controllerArguments = array('crudAction' => $actionName);;
		$controllerName = $this->infoService->getControllerName($object);
		$extensionName = $this->infoService->getExtensionName($object);
		$pluginName = $this->infoService->getPluginName($object);
		return $uriBuilder->uriFor('rest', $controllerArguments, $controllerName, $extensionName, $pluginName);
	}

	/**
	 * Get property definitions
	 *
	 * @param mixed $object
	 * @param array $properties
	 * @return array
	 */
	protected function getPropertyDefinitions($object, $properties) {
		$types = $this->infoService->getPropertyTypes($object, $properties);
		$tags = $this->infoService->getAllTagsByAnnotation($object, 'ExtJS');
		foreach ($properties as $k => $propertyName) {
			$def = array(
				'name' => $propertyName,
				'type' => $types[$propertyName],
				'tags' => $tags,
			);
			$properties[$k] = $def;
		}
		return $properties;
	}

}
