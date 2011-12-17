<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2011 Claus Due <claus@wildside.dk>, Wildside A/S
*
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 3 of the License, or
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

require_once t3lib_extMgm::extPath('cms', 'layout/class.tx_cms_layout.php');
require_once t3lib_extMgm::extPath('cms', 'layout/interfaces/interface.tx_cms_layout_tt_content_drawitemhook.php');

/**
 * Fluid Template preview renderer
 *
 * @version $Id$
 * @copyright Copyright belongs to the respective authors
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 * @package Fed
 * @subpackage Backend
 */
class Tx_Fed_Backend_Preview implements tx_cms_layout_tt_content_drawItemHook {

	/**
	 * @var Tx_Extbase_Object_ObjectManager
	 */
	protected $objectManager;

	/**
	 * @var Tx_Fluid_View_StandaloneView
	 */
	protected $view;

	/**
	 * @var Tx_Fed_Configuration_ConfigurationManager
	 */
	protected $configurationManager;

	/**
	 * @var Tx_Fed_Utility_JSON
	 */
	protected $jsonService;

	/**
	 * @var Tx_Fed_Utility_FlexForm
	 */
	protected $flexform;

	/**
	 * CONSTRUCTOR
	 */
	public function __construct() {
		$templatePathAndFilename = t3lib_extMgm::extPath('fed', 'Resources/Private/Templates/FlexibleContentElement/BackendPreview.html');
		$this->objectManager = t3lib_div::makeInstance('Tx_Extbase_Object_ObjectManager');
		$this->jsonService = $this->objectManager->get('Tx_Fed_Utility_JSON');
		$this->configurationManager = $this->objectManager->get('Tx_Fed_Configuration_ConfigurationManager');
		$this->flexform = $this->objectManager->get('Tx_Fed_Utility_FlexForm');
		$this->view = $this->objectManager->get('Tx_Fluid_View_StandaloneView');
		$this->view->setTemplatePathAndFilename($templatePathAndFilename);
	}

	/**
	 * Preprocessing
	 *
	 * @param tx_cms_layout $parentObject
	 * @param boolean $drawItem
	 * @param type $headerContent
	 * @param type $itemContent
	 * @param array $row
	 */
	public function preProcess(tx_cms_layout &$parentObject, &$drawItem, &$headerContent, &$itemContent, array &$row) {
		switch ($row['CType']) {
			case 'fed_fce': $this->preProcessFlexibleContentElement($drawItem, $itemContent, $headerContent, $row); break;
			case 'fed_template': $this->preProcessTemplateDisplay($drawItem, $itemContent, $row); break;
			default:
					// check for a defined Fluid FlexForm - attempt to render Preview section
				$flexFormConfiguration = Tx_Fed_Core::getRegisteredFlexForms('contentObject', $row['CType']);
				if (!$flexFormConfiguration && !empty($row['list_type'])) {
					$flexFormConfiguration = Tx_Fed_Core::getRegisteredFlexForms('plugin', $row['list_type']);
				}
				if ($flexFormConfiguration) {
					$this->preProcessExtbasePlugin($drawItem, $itemContent, $headerContent, $row, (array) $flexFormConfiguration);
				}
				break;
		}
	}

	/**
	 * Render preview based on a Fluid Template Display plugin
	 *
	 * @param boolean $drawItem
	 * @param string $itemContent
	 * @param array $row
	 */
	public function preProcessTemplateDisplay(&$drawItem, &$itemContent, array &$row) {
		$flexform = t3lib_div::xml2array($row['pi_flexform']);
		$templateFile = $flexform['data']['sDEF']['lDEF']['templateFile']['vDEF'];
		$templateSource = $flexform['data']['sDEF']['lDEF']['templateSource']['vDEF'];
		$fluidVars = $this->jsonService->decode($flexform['data']['sDEF']['lDEF']['fluidVars']['vDEF']);
		if (is_file($templateFile)) {
			$this->view->setTemplatePathAndFilename(PATH_site . $templateFile);
		} else if (strlen(trim($templateSource)) > 0) {
			$this->view->setTemplateSource($templateSource);
		}
		if ($fluidVars) {
			if (is_array($fluidVars)) {
				$vars = $fluidVars;
			} else {
				$vars = array();
				foreach ($fluidVars as $k=>$v) {
					$vars[$k] = $v;
				}
			}
			$this->view->assignMultiple($vars);
		}
		#$itemContent = $this->view->render();
	}

	/**
	 * Hook method to draw a preview from a Fluid template file
	 *
	 * @param boolean $drawItem
	 * @param string $itemContent
	 * @param string $headerContent
	 * @param array $row
	 * @param string $templatePathAndFilename
	 * @param array $variables
	 * @param array $paths
	 */
	public function drawPreview(&$drawItem, &$itemContent, &$headerContent, array &$row, $templatePathAndFilename, array $variables, array $paths=array()) {
		try {
			$this->flexform->setContentObjectData($row);
			$stored = $this->getFlexFormConfiguration($templatePathAndFilename, NULL, $paths);
			$variables = array_merge($stored, $variables, $this->flexform->getAllAndTransform($stored['fields']));
			$variables['label'] = $stored['label'];
			$variables['config'] = $stored;
			$variables['row'] = $row;
			$variables['preview'] = $this->renderFluidPreview($templatePathAndFilename, $variables);
			$this->view->assignMultiple($variables);
			$itemContent = $this->view->render();
			$headerContent = '<strong>' . $stored['label'] . '</strong> <i>' . $row['header'] . '</i> ';
			$drawItem = FALSE;
		} catch (Exception $e) {
			$itemContent = 'INVALID: ';
			$itemContent .= basename($fceTemplateFile);
			$itemContent .= '<br />' . LF;
			$itemContent .= 'Error: ' . $e->getMessage();
		}
	}

	/**
	 * Processing: Extbase Plugin preview for registered Dynamic FlexForms
	 *
	 * @param boolean $drawItem
	 * @param string $itemContent
	 * @param string $headerContent
	 * @param array $row
	 * @param array $configuration
	 */
	public function preProcessExtbasePlugin(&$drawItem, &$itemContent, &$headerContent, array &$row, array $configuration) {
		$templatePathAndFilename = $configuration['templateFilename'];
		if (file_exists($templatePathAndFilename)) {
			$this->drawPreview($drawItem, $itemContent, $headerContent, $row, $templatePathAndFilename, (array) $configuration['variables']);
		}
	}

	/**
	 * Processing: Fluid-based Flexible Content Element
	 *
	 * @param boolean $drawItem
	 * @param string $itemContent
	 * @param string $headerContent
	 * @param array $row
	 */
	public function preProcessFlexibleContentElement(&$drawItem, &$itemContent, &$headerContent, array &$row) {
		$templatePathAndFilename = $row['tx_fed_fcefile'];
		list ($extensionName, $filename) = explode(':', $templatePathAndFilename);
		$paths = array();
		if ($filename) {
			$paths = $this->configurationManager->getContentConfiguration($extensionName);
			$templatePathAndFilename = Tx_Fed_Utility_Path::translatePath($paths['templateRootPath']) . $filename;
			$paths['partialRootPath'] = Tx_Fed_Utility_Path::translatePath($paths['partialRootPath']);
			$paths['layoutRootPath'] = Tx_Fed_Utility_Path::translatePath($paths['layoutRootPath']);
		} else {
			$templatePathAndFilename = PATH_site . $templatePathAndFilename;
			$paths['layoutRootPath'] = t3lib_extMgm::extPath('fed', 'Resources/Private/Layouts/');
		}
		$this->drawPreview($drawItem, $itemContent, $headerContent, $row, $templatePathAndFilename, array(), $paths);
	}

	/**
	 * Get Dynamic FlexForm configuration from a Fluid template with rendering
	 * arguments set in $variables (Fluid template variables)
	 *
	 * @param string $templatePathAndFilename
	 * @param array $variables
	 * @param array $paths
	 * @return array
	 */
	protected function getFlexFormConfiguration($templatePathAndFilename, $variables, array $paths=array()) {
		$view = $this->objectManager->get('Tx_Fed_MVC_View_ExposedStandaloneView');
		$view->setTemplatePathAndFilename($templatePathAndFilename);
		$flexform = $this->flexform->getAll();
		$view->assignMultiple($flexform);
		$view->assignMultiple((array) $variables);
		$stored = $view->getStoredVariable('Tx_Fed_ViewHelpers_FceViewHelper', 'storage', 'Configuration');
		$stored['groups'] = array();
		foreach ($stored['fields'] as $field) {
			$groupKey = $field['group']['name'];
			$groupLabel = $field['group']['label'];
			if (is_array($stored['groups'][$groupKey]) === FALSE) {
				$stored['groups'][$groupKey] = array(
					'name' => $groupKey,
					'label' => $groupLabel,
					'fields' => array()
				);
			}
			array_push($stored['groups'][$groupKey]['fields'], $field);
		}
		return $stored;
	}

	/**
	 * Wrapper method to render a Preview section from a Fluid template file
	 *
	 * @param string $templatePathAndFilename
	 * @param array $variables
	 * @return string
	 */
	protected function renderFluidPreview($templatePathAndFilename, $variables) {
		$view = $this->objectManager->get('Tx_Fed_MVC_View_ExposedStandaloneView');
		$view->setTemplatePathAndFilename($templatePathAndFilename);
		return $view->renderStandaloneSection('Preview', (array) $variables);
	}

}


?>
