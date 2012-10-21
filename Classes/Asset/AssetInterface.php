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
 * @subpackage Asset
 */
interface Tx_Fed_Asset_AssetInterface {

	/**
	 * @param mixed $id
	 * @return void
	 */
	public function setId($id);

	/**
	 * @return mixed
	 */
	public function getId();

	/**
	 * @return string
	 */
	public function getFilePathAndFilename();

	/**
	 * @param string $filePathAndFilename
	 */
	public function setFilePathAndFilename($filePathAndFilename);

	/**
	 * @return mixed
	 */
	public function getContent();

	/**
	 * @param mixed $content
	 */
	public function setContent($content);

	/**
	 * @return string|Tx_Fed_Asset_AssetInterface
	 */
	public function getBefore();

	/**
	 * @param string|Tx_Fed_Asset_AssetInterface $before
	 * @return mixed
	 */
	public function setBefore($before);

	/**
	 * @return string|Tx_Fed_Asset_AssetInterface
	 */
	public function getAfter();

	/**
	 * @param string|Tx_Fed_Asset_AssetInterface $after
	 * @return mixed
	 */
	public function setAfter($after);

	/**
	 * @return mixed
	 */
	public function compile();

	/**
	 * @param integer $priority
	 * @return void
	 */
	public function setPriority($priority);

	/**
	 * @return integer
	 */
	public function getPriority();

}
