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
 * Abstract SubstituteArray
 *
 * @package Fed
 * @subpackage Routing
 */
abstract class Tx_Fed_Routing_AbstractSubstituteArray implements ArrayAccess, Iterator {

	/**
	 * @var array
	 */
	protected $sets = array();

	/**
	 * @param array $existingRules
	 */
	public function __construct($existingRules = array()) {
		$this->sets = $existingRules;
	}

	/**
	 * @param mixed $offset
	 * @return mixed
	 */
	public function get($offset) {
		return $this->offsetGet($offset);
	}

	/**
	 * @param mixed $offset
	 * @return boolean
	 */
	public function offsetExists($offset) {
		return isset($this->sets[$offset]);
	}

	/**
	 * @return mixed
	 */
	public function offsetGet($offset) {
		return $this->sets[$offset];
	}

	/**
	 * @return void
	 */
	public function offsetSet($offset, $value) {
		$this->sets[$offset] = $value;
	}

	/**
	 * @return void
	 */
	public function offsetUnset($offset) {
		unset($this->sets[$offset]);
	}

	/**
	 * @return mixed Can return any type.
	 */
	public function current() {
		return current($this->sets);
	}

	/**
	 * @return void
	 */
	public function next() {
		next($this->sets);
	}

	/**
	 * @return
	 */
	public function key() {
		return key($this->sets);
	}

	/**
	 * @return boolean
	 */
	public function valid() {
		return (array_search(array_pop(array_values($this->sets)), $this->sets) !== array_pop(array_keys($this->sets)));
	}

	/**
	 * @return void
	 */
	public function rewind() {
		reset($this->sets);
	}


}