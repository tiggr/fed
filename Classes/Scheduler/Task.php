<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Task
 *
 * @author claus
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
				'Controller',
				'Command',
				Tx_Extbase_Utility_Extension::convertLowerUnderscoreToUpperCamelCase($controllerName) . 'CommandController'
			);
			$controllerObjectName = implode('_', $controllerObjectNameParts);
			$request->setControllerCommandName($commandName);
			$request->setControllerObjectName($controllerObjectName);
			$request->setArguments($this->arguments);
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
