<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2011 Claus Due, Wildside A/S <claus@wildside.dk>
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
 * Scheduler task to execute CommandController commands
 *
 * @version $Id$
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser Public License, version 3 or later
 */
class Tx_Fed_Scheduler_Task extends Tx_Scheduler_Task {

	/**
	 * @var string
	 */
	protected $commandIdentifier;

	/**
	 * @var array
	 */
	protected $arguments;

	/**
	 * @var Tx_Extbase_Object_ObjectManager
	 */
	protected $objectManager;

	/**
	 * @param Tx_Extbase_Object_ObjectManager $objectManager
	 */
	public function injectObjectManager(Tx_Extbase_Object_ObjectManager $objectManager) {
		$this->objectManager = $objectManager;
	}

	/**
	 * Function execute from the Scheduler
	 *
	 * @return boolean TRUE on successful execution, FALSE on error
	 */
	public function execute() {
		list ($extensionName, $controllerName, $commandName) = explode(':', $this->commandIdentifier);
		$objectManager = t3lib_div::makeInstance('Tx_Extbase_Object_ObjectManager');
		$this->injectObjectManager($objectManager);
		$request = $this->objectManager->get('Tx_Extbase_MVC_CLI_Request');
		$dispatcher = $this->objectManager->get('Tx_Extbase_MVC_Dispatcher');
		$response = $this->objectManager->get('Tx_Extbase_MVC_CLI_Response');
		try {
			$controllerObjectNameParts = array(
				'Tx',
				Tx_Extbase_Utility_Extension::convertLowerUnderscoreToUpperCamelCase($extensionName),
				'Command',
				Tx_Extbase_Utility_Extension::convertLowerUnderscoreToUpperCamelCase($controllerName) . 'CommandController'
			);
			$controllerObjectName = implode('_', $controllerObjectNameParts);
			$request->setControllerCommandName($commandName);
			$request->setControllerObjectName($controllerObjectName);
			$request->setArguments((array) $this->arguments);
			$dispatcher->dispatch($request, $response);
			return TRUE;
		} catch (Exception $e) {
			t3lib_div::sysLog($e->getMessage(), $extensionName, $e->getCode());
			return FALSE;
		}
	}

	/**
	 * @param string $commandIdentifier
	 */
	public function setCommandIdentifier($commandIdentifier) {
		$this->commandIdentifier = $commandIdentifier;
	}

	/**
	 * @return string
	 */
	public function getCommandIdentifier() {
		return $this->commandIdentifier;
	}

	/**
	 * @param array $arguments
	 */
	public function setArguments($arguments) {
		$this->arguments = $arguments;
	}

	/**
	 * @return array
	 */
	public function getArguments() {
		return $this->arguments;
	}

}

?>
