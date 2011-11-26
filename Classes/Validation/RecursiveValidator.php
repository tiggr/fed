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
 * @author Claus Due, Wildside A/S
 * @version $Id$
 * @copyright Copyright belongs to the respective authors
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 * @package Fed
 * @subpackage Validation
 */
class Tx_Fed_Validation_RecursiveValidator implements t3lib_Singleton {

	/**
	 * @var Tx_Extbase_Property_MappingResults
	 */
	protected $mappingResults;

	/**
	 * @var Tx_Extbase_Reflection_Service
	 */
	protected $reflectionService;

	/**
	 * @var Tx_Extbase_Validation_ValidatorResolver
	 */
	protected $validatorResolver;

	/**
	 * @var Tx_Extbase_Property_Mapper
	 */
	protected $propertyMapper;

	/**
	 * @var Tx_Fed_Utility_DomainObjectInfo
	 */
	protected $infoService;

	/**
	 * @var Tx_Extbase_Object_ObjectManager
	 */
	protected $objectManager;

	/**
	 * @param Tx_Extbase_Reflection_Service $reflectionService
	 */
	public function injectReflectionService(Tx_Extbase_Reflection_Service $reflectionService) {
		$this->reflectionService = $reflectionService;
	}

	/**
	 * @param Tx_Extbase_Validation_ValidatorResolver $validatorResolver
	 */
	public function injectValidatorResolver(Tx_Extbase_Validation_ValidatorResolver $validatorResolver) {
		$this->validatorResolver = $validatorResolver;
	}

	/**
	 * @param Tx_Extbase_Property_Mapper $propertyMapper
	 */
	public function injectPropertyMapper(Tx_Extbase_Property_Mapper $propertyMapper) {
		$this->propertyMapper = $propertyMapper;
	}

	/**
	 * @param Tx_Fed_Utility_DomainObjectInfo $infoService
	 */
	public function injectInfoService(Tx_Fed_Utility_DomainObjectInfo $infoService) {
		$this->infoService = $infoService;
	}

	/**
	 * @param Tx_Extbase_Object_ObjectManager $objectManager
	 */
	public function injectObjectManager(Tx_Extbase_Object_ObjectManager $objectManager) {
		$this->objectManager = $objectManager;
		$this->mappingResults = $this->objectManager->get('Tx_Extbase_Property_MappingResults');
	}

	/**
	 * Validates an object recursively, adding errors along the way (indexed by
	 * their respective object paths)
	 *
	 * @param Tx_Extbase_DomainObject_AbstractDomainObject $object
	 * @param array $array
	 * @return Tx_Extbase_Property_MappingResults
	 */
	public function validate(Tx_Extbase_DomainObject_AbstractDomainObject $object, array $path=array()) {
		$className = get_class($object);
		$properties = $this->infoService->getValuesByAnnotation($object, 'var');
		$validator = $this->objectManager->get('Tx_Extbase_Validation_Validator_GenericObjectValidator');
		foreach ($properties as $propertyName=>$value) {
			if (substr($propertyName, 0, 1) == '_' || $propertyName == 'uid' || $propertyName == 'pid') {
				unset($properties[$propertyName]);
				continue;
			}
			$value = Tx_Extbase_Reflection_ObjectAccess::getProperty($object, $propertyName);
			if ($value instanceof Tx_Extbase_Persistence_ObjectStorage) {
				array_push($path, $propertyName);
				foreach ($value as $subObject) {
					array_push($path, $iteration);
					$this->validate($subObject, $path);
					array_pop($path);
				}
				array_pop($path);
			} else if ($value instanceof Tx_Extbase_DomainObject_AbstractDomainObject) {
				array_push($path, $propertyName);
				$this->validate($value, $path);
				array_pop($path);
			} else {
				$method = "get" . ucfirst($propertyName);
				$isValid = $validator->isPropertyValid($object, $propertyName);
			}
		}
		return $this->mappingResults;
	}

}

?>