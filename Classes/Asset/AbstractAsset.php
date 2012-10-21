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
abstract class Tx_Fed_Asset_AbstractAsset {

	/**
	 * @var string
	 */
	protected $id;

	/**
	 * @var string|Tx_Fed_Asset_AssetInterface
	 */
	protected $before;

	/**
	 * @var string|Tx_Fed_Asset_AssetInterface
	 */
	protected $after;

	/**
	 * @var string
	 */
	protected $filePathAndFilename;

	/**
	 * @var mixed
	 */
	protected $content;

	/**
	 * @var integer
	 */
	protected $priority;

	/**
	 * @param string|Tx_Fed_Asset_AssetInterface $after
	 * @return void
	 */
	public function setAfter($after) {
		$this->after = $after;
	}

	/**
	 * @return string|Tx_Fed_Asset_AssetInterface
	 */
	public function getAfter() {
		return $this->after;
	}

	/**
	 * @param string|Tx_Fed_Asset_AssetInterface $before
	 * @return void
	 */
	public function setBefore($before) {
		$this->before = $before;
	}

	/**
	 * @return string|Tx_Fed_Asset_AssetInterface
	 */
	public function getBefore() {
		return $this->before;
	}

	/**
	 * @param mixed $content
	 * @return void
	 */
	public function setContent($content) {
		$this->content = $content;
	}

	/**
	 * @return mixed
	 */
	public function getContent() {
		return $this->content;
	}

	/**
	 * @param string $filePathAndFilename
	 * @return void
	 */
	public function setFilePathAndFilename($filePathAndFilename) {
		$this->filePathAndFilename = $filePathAndFilename;
	}

	/**
	 * @return string
	 */
	public function getFilePathAndFilename() {
		return $this->filePathAndFilename;
	}

	/**
	 * @param string $id
	 * @return void
	 */
	public function setId($id) {
		$this->id = $id;
	}

	/**
	 * @return string
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * @return mixed
	 */
	public function compile() {
		if ($this->content) {
			return $this->content;
		}
		return $this->filePathAndFilename;
	}

	/**
	 * @param integer $priority
	 * @return void
	 */
	public function setPriority($priority) {
		$this->priority = $priority;
	}

	/**
	 * @return integer
	 */
	public function getPriority() {
		return $this->priority;
	}

}