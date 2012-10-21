<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2012 Claus Due <claus@wildside.dk>, Wildside A/S
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


/**
 * Interface for Assets
 *
 * @package Fed
 * @subpackage UserFunction
 */
class Tx_Fed_Asset_PageRendererHookProcessor {

	/**
	 * @var Tx_Extbase_Object_ObjectManagerInterface
	 */
	protected $objectManager;

	/**
	 * @var Tx_Fed_Asset_AssetManagerInterface
	 */
	protected $assetManager;

	/**
	 * @param Tx_Extbase_Object_ObjectManagerInterface $objectManager
	 * @return void
	 */
	public function injectObjectManager(Tx_Extbase_Object_ObjectManagerInterface $objectManager) {
		$this->objectManager = $objectManager;
	}

	/**
	 * @param Tx_Fed_Asset_AssetManagerInterface $assetManager
	 * @return void
	 */
	public function injectAssetManager(Tx_Fed_Asset_AssetManagerInterface $assetManager) {
		$this->assetManager = $assetManager;
	}

	/**
	 * CONSTRUCTOR
	 *
	 * @return void
	 */
	public function __construct() {
		$objectManager = t3lib_div::makeInstance('Tx_Extbase_Object_ObjectManager');
		$this->injectObjectManager($objectManager);
		$assetManager = $this->objectManager->get('Tx_Fed_Asset_AssetManagerInterface');
		$this->injectAssetManager($assetManager);
	}

	/**
	 * @param array $params
	 * @param t3lib_PageRenderer $pageRenderer
	 * @return void
	 */
	public function preProcessHook(&$params, t3lib_PageRenderer &$pageRenderer) {
		$sortedAssets = $this->assetManager->getSortedAssets();
		var_dump($sortedAssets);
		exit();
	}

}