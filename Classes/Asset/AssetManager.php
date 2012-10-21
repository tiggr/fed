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
 * Interface for AssetManager
 *
 * @package Fed
 * @subpackage Asset
 */
class Tx_Fed_Asset_AssetManager implements Tx_Fed_Asset_AssetManagerInterface {

	/**
	 * @var array<Tx_Fed_Asset_AssetInterface>
	 */
	protected $assets = array();

	/**
	 * @param Tx_Fed_Asset_AssetInterface $asset
	 * @return void
	 */
	public function add(Tx_Fed_Asset_AssetInterface $asset) {
		$id = $asset->getId();
		$this->assets[$id] = $asset;
	}

	/**
	 * @param mixed $assetOrId
	 * @return void
	 */
	public function remove($assetOrId) {
		if ($assetOrId instanceof Tx_Fed_Asset_AssetInterface) {
			$id = $assetOrId->getId();
		} else {
			$id = $assetOrId;
		}
		$pageRenderer = new t3lib_PageRenderer();
		unset($this->assets[$id]);
	}

	/**
	 * @return mixed
	 */
	public function buildPageHeaderCodeForAssets() {
		// TODO: Implement buildPageHeaderCodeForAssets() method.
		return NULL;
	}

	/**
	 * @return mixed
	 */
	public function buildPageFooterCodeForAssets() {
		// TODO: Implement buildPageFooterCodeForAssets() method.
		return NULL;
	}

	/**
	 * @return array<Tx_Fed_Asset_AssetInterface>
	 */
	public function getSortedAssets() {
		$keys = array_keys($this->assets);
		$assets = array_values($this->assets);
		foreach ($this->assets as $id => $asset) {
			/** @var Tx_Fed_Asset_AssetInterface $asset */
			$before = $asset->getBefore();
			$after = $asset->getAfter();

		}
		return $sorted;

	}

}
