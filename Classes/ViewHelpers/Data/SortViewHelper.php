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
 *
 * @author Claus Due, Wildside A/S
 * @package Fed
 * @subpackage ViewHelpers\Data
 */
class Tx_Fed_ViewHelpers_Data_SortViewHelper extends Tx_Fluid_Core_ViewHelper_AbstractTagBasedViewHelper {

	/**
	 * Initialize arguments
	 */
	public function initializeArguments() {
		$this->registerArgument('as', 'string', 'Which variable to update in the TemplateVariableContainer. If left out, returns sorted data instead of updating the varialbe (i.e. reference or copy)');
		$this->registerArgument('sortBy', 'string', 'Which property/field to sort by - leave out for numeric sorting based on indexes(keys)');
		$this->registerArgument('order', 'string', 'ASC or DESC', FALSE, 'ASC');
		$this->registerArgument('array', 'array', 'DEPRECATED: Optional; use to sort an array');
		$this->registerArgument('objectStorage', 'Tx_Extbase_Persistence_ObjectStorage|Tx_Extbase_Persistence_LazyObjectStorage', 'DEPRECATED: Optional; use to sort an ObjectStorage');
		$this->registerArgument('queryResult', 'Tx_Extbase_Persistence_QueryResult', 'DEPRECATED: Optional; use to sort a QueryResult');
	}

	/**
	 * "Render" method - sorts a target list-type target. Either $array or $objectStorage must be specified. If both are,
	 * ObjectStorage takes precedence.
	 *
	 * @param array|object An array, Iterator, ObjectStorage, LazyObjectStorage or QueryResult to sort
	 * @return mixed
	 */
	public function render($subject=NULL) {
		if ($subject === NULL) {
			$priorities = array('array', 'objectStorage', 'queryResult');
			foreach ($priorities as $argumentName) {
				if ($this->arguments[$argumentName]) {
					$subject = $this->arguments[$argumentName];
					break;
				}
			}
		}
		$sorted = NULL;
		if (is_array($subject) === TRUE) {
			$sorted = $this->sortArray($subject);
		} else {
			if ($subject instanceof Tx_Extbase_Persistence_ObjectStorage || $subject instanceof Tx_Extbase_Persistence_LazyObjectStorage) {
				$sorted = $this->sortObjectStorage($subject);
			} elseif ($subject instanceof Iterator) {
				/** @var Iterator $subject */
				$array = array();
				foreach ($subject as $index => $item) {
					$array[$index] = $item;
				}
				$sorted = $this->sortArray($array);
			} elseif ($subject instanceof Tx_Extbase_Persistence_QueryResultInterface) {
				/** @var Tx_Extbase_Persistence_QueryResultInterface $subject */
				$sorted = $this->sortArray($subject->toArray());
			}
		}
		if ($sorted === NULL) {
			throw new Exception('Nothing to sort, SortViewHelper has no purpose in life, performing LATE term self-abortion');
		}
		if ($this->arguments['as']) {
			if ($this->templateVariableContainer->exists($this->arguments['as'])) {
				$this->templateVariableContainer->remove($this->arguments['as']);
			}
			$this->templateVariableContainer->add($this->arguments['as'], $sorted);
			return $this->renderChildren();
		} else {
			return $sorted;
		}
	}

	/**
	 * Sort an array
	 *
	 * @param array $array
	 * @return array
	 */
	protected function sortArray($array) {
		$sorted = array();
		foreach ($array as $index => $object) {
			if ($this->arguments['sortBy']) {
				$index = $this->getSortValue($object);
			}
			while (isset($sorted[$index])) {
				$index .= '1';
			}
			$sorted[$index] = $object;
		}
		if ($this->arguments['order'] === 'ASC') {
			ksort($sorted);
		} else {
			krsort($sorted);
		}
		return $sorted;
	}

	/**
	 * Sort a Tx_Extbase_Persistence_ObjectStorage instance
	 *
	 * @param Tx_Extbase_Persistence_ObjectStorage $storage
	 * @return Tx_Extbase_Persistence_ObjectStorage
	 */
	protected function sortObjectStorage($storage) {
		/** @var Tx_Extbase_Object_ObjectManager $objectManager */
		$objectManager = t3lib_div::makeInstance('Tx_Extbase_Object_ObjectManager');
		/** @var Tx_Extbase_Persistence_ObjectStorage $temp */
		$temp = $objectManager->get('Tx_Extbase_Persistence_ObjectStorage');
		foreach ($storage as $item) {
			$temp->attach($item);
		}
		$sorted = array();
		foreach ($storage as $index => $item) {
			if ($this->arguments['sortBy']) {
				$index = $this->getSortValue($item);
			}
			while (isset($sorted[$index])) {
				$index .= '1';
			}
			$sorted[$index] = $item;
		}
		if ($this->arguments['order'] === 'ASC') {
			ksort($sorted);
		} else {
			krsort($sorted);
		}
		$storage = $objectManager->get('Tx_Extbase_Persistence_ObjectStorage');
		foreach ($sorted as $item) {
			$storage->attach($item);
		}
		return $storage;
	}

	/**
	 * Gets the value to use as sorting value from $object
	 *
	 * @param mixed $object
	 * @return mixed
	 */
	protected function getSortValue($object) {
		$field = $this->arguments['sortBy'];
		$value = Tx_Extbase_Reflection_ObjectAccess::getProperty($object, $field);
		if ($value instanceof DateTime) {
			$value = $value->format('U');
		} elseif ($value instanceof Tx_Extbase_Persistence_ObjectStorage) {
			$value = $value->count();
		} elseif (is_array($value)) {
			$value = count($value);
		}
		return $value;
	}
}
