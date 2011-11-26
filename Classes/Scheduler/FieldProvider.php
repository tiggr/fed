<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of FieldProvider
 *
 * @author claus
 */
class Tx_Fed_Scheduler_FieldProvider implements Tx_Scheduler_AdditionalFieldProvider {

	/**
	 * @var Tx_Extbase_MVC_CLI_CommandManager
	 */
	protected $commandManager;

	/**
	 * @var Tx_Extbase_Object_ObjectManager
	 */
	protected $objectManager;

	/**
	 * @var Tx_Fed_Scheduler_Task
	 */
	protected $task;

	/**
	 * @param Tx_Extbase_MVC_CLI_CommandManager $commandManager
	 */
	public function injectCommandManager(Tx_Extbase_MVC_CLI_CommandManager $commandManager) {
		$this->commandManager = $commandManager;
	}

	/**
	 * @param Tx_Extbase_Object_ObjectManager $objectManager
	 */
	public function injectObjectManager(Tx_Extbase_Object_ObjectManager $objectManager) {
		$this->objectManager = $objectManager;
	}

	/**
	 * CONSTRUCTOR
	 */
	public function __construct() {
		$objectManager = t3lib_div::makeInstance('Tx_Extbase_Object_ObjectManager');
		$this->injectObjectManager($objectManager);
		$commandManager = $this->objectManager->get('Tx_Extbase_MVC_CLI_CommandManager');
		$this->injectCommandManager($commandManager);
	}

	/**
	 * Render additional information fields within the scheduler backend.
	 *
	 * @param array $taskInfo Array information of task to return
	 * @param task $task Task object
	 * @param Tx_Scheduler_Module $schedulerModule Reference to the calling object (BE module of the Scheduler)
	 * @return array Additional fields
	 * @see interfaces/tx_scheduler_AdditionalFieldProvider#getAdditionalFields($taskInfo, $task, $schedulerModule)
	 */
	public function getAdditionalFields(array &$taskInfo, $task, Tx_Scheduler_Module $schedulerModule) {
		$this->task = $task;
		$fields = array();
		$fields['action'] = $this->getCommandControllerActionField();
		if ($this->task && $this->task->getCommandIdentifier()) {
			$command = $this->commandManager->getCommandByIdentifier($this->task->getCommandIdentifier());
			$fields['description'] = $this->getCommandControllerActionDescriptionField();
			$argumentFields = $this->getCommandControllerActionArgumentFields($command->getArgumentDefinitions());
			$fields = array_merge($fields, $argumentFields);
		}
		return $fields;
	}

	/**
	 * Validates additional selected fields
	 *
	 * @param array $submittedData
	 * @param Tx_Scheduler_Task $schedulerModule
	 * @return boolean
	 */
	public function validateAdditionalFields(array &$submittedData, Tx_Scheduler_Module $schedulerModule) {
		return TRUE;
	}

	/**
	 * Saves additional field values
	 *
	 * @param array $submittedData
	 * @param Tx_Scheduler_Task $task
	 * @return boolean
	 */
	public function saveAdditionalFields(array $submittedData, Tx_Scheduler_Task $task) {
		$task->setCommandIdentifier($submittedData['task_extbase']['action']);
		$task->setArguments($submittedData['task_extbase']['arguments']);
		return TRUE;
	}

	/**
	 * Get description of selected command
	 *
	 * @return string
	 */
	protected function getCommandControllerActionDescriptionField() {
		$command = $this->commandManager->getCommandByIdentifier($this->task->getCommandIdentifier());
		return array(
			'code' => $command->getDescription(),
			'label' => 'Description'
		);
	}

	/**
	 * Gets a select field containing all possible CommandController actions
	 *
	 * @return array
	 */
	protected function getCommandControllerActionField() {
		$commands = $this->commandManager->getAvailableCommands();
		$options = array();
		foreach ($commands as $command) {
			if ($command instanceof Tx_Extbase_MVC_CLI_Command) {
				$classNameParts = explode('_', $command->getControllerClassName());
				$identifier = $command->getCommandIdentifier();
				$options[$identifier] = $classNameParts[1] . ' ' . str_replace('CommandController', '', $classNameParts[3]) . ': ' . $command->getControllerCommandName();
			}
		}
		$name = "action";
		$currentlySelectedCommand = $this->task ? $this->task->getCommandIdentifier() : NULL;
		return array(
			'code' => $this->renderSelectField($name, $options, $currentlySelectedCommand) . '<br />(note: save and reopen to define command arguments)',
			'label' => $this->getActionLabel()
		);
	}

	/**
	 * Gets a set of fields covering arguments which must be sent to $currentControllerAction
	 *
	 * @param array $argumentDefinitions
	 * @return array
	 */
	protected function getCommandControllerActionArgumentFields(array $argumentDefinitions) {
		$fields = array();
		$argumentValues = $this->task->getArguments();
		foreach ($argumentDefinitions as $index=>$argument) {
			$name = $argument->getName();
			$description = $argument->getDescription();
			$value = $argumentValues[$name];
			$fields[$name] = array(
				'code' => '<input type="text" name="tx_scheduler[task_fed][arguments][' . $name . ']" value="' . $value . '" />',
				'label' => $this->getArgumentLabel($name)
			);
		}
		return $fields;
	}

	/**
	 * Gets an array of language labels related to the extension providing the
	 * currently selected command.
	 *
	 * @param string $extensionName
	 * @param string $extensionName
	 * @return array
	 */
	protected function getLanguageLabel($key, $extensionName=NULL) {
		if (!$extensionName) {
			list ($extensionName, $commandControllerName, $commandName) = explode(':', $this->task->getCommandIdentifier());
		}
		return Tx_Extbase_Utility_Localization::translate($key, $extensionName);
	}

	/**
	 * Get a human-readable label for a command argument
	 *
	 * @param string $argumentName
	 */
	protected function getArgumentLabel($argumentName) {
		list ($extensionName, $commandControllerName, $commandName) = explode(':', $this->task->getCommandIdentifier());
		$path = array('command', $commandControllerName, $commandName, 'arguments', $argumentName);
		$index = implode('.', $path);
		$label = $this->getLanguageLabel($index);
		if ($label) {
			return $label;
		} else {
			return 'Argument: ' . $argumentName;
		}
	}

	/**
	 * Get a human-readable label for the action field
	 *
	 * @param string $name
	 * @return string
	 */
	protected function getActionLabel() {
		$index = 'task.action';
		$label = $this->getLanguageLabel($index, 'fed');
		if (!$label) {
			return 'CommandController Command';
		}
	}

	/**
	 * Render a select field with name $name and options $options
	 *
	 * @param string $name
	 * @param array $options
	 * return string
	 */
	protected function renderSelectField($name, $options, $selectedOptionValue) {
		$html = array(
			'<select name="tx_scheduler[task_extbase][' . $name . ']">'
		);
		foreach ($options as $optionValue=>$optionLabel) {
			$selected = $optionValue == $selectedOptionValue ? ' selected="selected"' : '';
			array_push($html, '<option value="' . $optionValue . '"' . $selected . '>' . $optionLabel . '</option>');
		}
		array_push($html, '</select>');
		return implode(LF, $html);
	}


}

?>
