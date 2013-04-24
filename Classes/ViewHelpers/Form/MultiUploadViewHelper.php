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
 * ************************************************************* */

/**
 *
 *
 * @author Claus Due, Wildside A/S
 * @package Fed
 * @subpackage ViewHelpers/Form
 */
class Tx_Fed_ViewHelpers_Form_MultiUploadViewHelper extends Tx_Fluid_ViewHelpers_Form_UploadViewHelper {

	/**
	 * @var string
	 */
	protected $tagName = 'div';

	/**
	 * @var string
	 */
	protected $uniqueId = 'plupload';

	/**
	 * @var string
	 */
	protected $editorId;

	/**
	 * @var Tx_Fed_Service_Domain
	 */
	protected $infoService;

	/**
	 * @var Tx_Fed_Utility_DocumentHead
	 */
	protected $documentHead;

	/**
	 * @var Tx_Fed_Service_Json
	 */
	protected $jsonService;

	/**
	 * @var Tx_Fluid_Core_ViewHelper_TagBuilder
	 */
	protected $tag = NULL;

	/**
	 * @param Tx_Fed_Service_Domain $infoService
	 * @return void
	 */
	public function injectInfoService(Tx_Fed_Service_Domain $infoService) {
		$this->infoService = $infoService;
	}

	/**
	 * @param Tx_Fed_Utility_DocumentHead $documentHead
	 * @return void
	 */
	public function injectDocumentHead(Tx_Fed_Utility_DocumentHead $documentHead) {
		$this->documentHead = $documentHead;
	}

	/**
	 * @param Tx_Fed_Service_Json $jsonService
	 * @return void
	 */
	public function injectJsonService(Tx_Fed_Service_Json $jsonService) {
		$this->jsonService = $jsonService;
	}

	/**
	 * @param Tx_Fluid_Core_ViewHelper_TagBuilder $tagBuilder Tag builder
	 * @return void
	 */
	public function injectTagBuilder(Tx_Fluid_Core_ViewHelper_TagBuilder $tagBuilder) {
		$this->tag = $tagBuilder;
	}

	/**
	 * Initialize the arguments.
	 *
	 * @return void
	 * @api
	 */
	public function initializeArguments() {
		parent::initializeArguments();
		$this->registerArgument('buttons', 'string', 'CSV list of buttons to render (browse,start,stop)', FALSE, 'browse,start,stop');
		$this->registerArgument('runtimes', 'string', 'CSV list of allowed runtimes - see plupload doc', FALSE, 'html5,flash,gears,silverlight,browserplus,html4');
		$this->registerArgument('url', 'string', 'If specified, overrides built-in uploader with one you created and placed at this URL');
		$this->registerArgument('autostart', 'boolean', 'If TRUE, queue automatically starts uploading as soon as a file is added.', FALSE, FALSE);
		$this->registerArgument('maxFileSize', 'string', 'Maxium allowed file size', FALSE, '10mb');
		$this->registerArgument('chunkSize', 'string', 'Chunk size when uploading in chunks', FALSE, '1mb');
		$this->registerArgument('actionName', 'string', 'Controller action to call to finish an uploaded file. Defaults to "upload".', FALSE, 'upload');
		$this->registerArgument('pluginName', 'string', 'Force plugin name to use in generated upload URL. Defaults to current plugin name, if any.', FALSE, FALSE);
		$this->registerArgument('noHiddenValueField', 'boolean', 'If TRUE, no hidden input field is created for storing uploaded filenames. You can use this if your uploaded files end up as ObjectStorage-references (which cannot be turned into strings anyway) or similar.', FALSE, FALSE);
		$this->registerArgument('uniqueNames', 'boolean', 'If TRUE, obfuscates and randomizes file names. Default behavior is to use TYPO3 unique filename features', FALSE, FALSE);
		$this->registerArgument('resizeWidth', 'integer', 'If set, uses client side resizing of any added images width', FALSE);
		$this->registerArgument('resizeHeight', 'integer', 'If set, uses client side resizing of any added images height', FALSE);
		$this->registerArgument('resizeQuality', 'integer', 'Range 0-100, quality of resized image', FALSE, 90);
		$this->registerArgument('filters', 'array', 'Array label=>csvAllowedExtensions of file types to browse for. For example: {0: {title: "Images", extensions: "jpg,jpeg,gif,png"}, 1: {title: "Text files", extensions: "txt,pdf,doc,docx"}}', FALSE, array( array('title' => 'All files', 'extensions' => '*') ) );
		$this->registerArgument('uploadfolder', 'string', 'If specified, uses this site relative path as target upload folder. If a form object exists and this argument is not present, TCA uploadfolder is used as defined in the named field definition');
		$this->registerArgument('preinit', 'array', 'Array of preinit event listener methods - see plupload documentation for reference. The default event which sets the contents of the hidden field is always fired.', FALSE, array());
		$this->registerArgument('init', 'array', 'Array of init event listener methods - see plupload documentation for reference. The default event which sets the contents of the hidden field is always fired.', FALSE, array());
		$this->registerArgument('initFunctionName', 'string', "If you want to use your own JS-initializer entirely, insert it's name here. It MUST be a jQuery function, as it's called using jQuery(objectName). Defaults to 'fileListEditor' which is a small module delivered by this extension.", FALSE, "fileListEditor");
		$this->registerArgument('storedValue', 'mixed', "If you set this, it will be used instead of the 'actual' form element value. Supports arrays (associative; each entry MUST contain 'name'; 'uid' is an optional extra) and CSVs.", FALSE, FALSE);
		$this->registerArgument('header', 'boolean', 'If FALSE, suppresses the header which is normally added to the upload widget', FALSE, TRUE);
		$this->registerArgument('headerTitle', 'string', 'Text for header title, if different from default');
		$this->registerArgument('headerSubtitle', 'string', 'Text for header subtitle, if different from default');
		$this->registerArgument('insertJSInBody', 'boolean', 'If TRUE, the associated Javascript is added to the body output of the page instead of the headers. This can come in handy for asynchronous loading of the object, although you will then need to include all the libraries and styling manually.', FALSE, FALSE);
	}

	/**
	 * Renders a multi-upload field using plupload. Posts value as simple string.
	 *
	 * @return string
	 */
	public function render() {
		$name = $this->getName();

			// Flatten stored values into a neat CSV-string
		$value = $this->getStoredValue(TRUE);
		if (is_array($value)) {
			$fieldValue = $this->flattenFilelist($value);
		} else {
			$fieldValue = $value;
		}

		$this->uniqueId = $this->arguments['id'] ? $this->arguments['id'] : uniqid('plupload');
		$this->setErrorClassAttribute();
		$this->registerFieldNameForFormTokenGeneration($name);
		$html = array(
			'<div id="' . $this->uniqueId . '" class="fed-plupload plupload_container"></div>',
		);

			// If we aren't told not to render the hidden value field, we'll do so now.
		if ((boolean) $this->arguments['noHiddenValueField'] === FALSE) {
			$html[] = '<input id="' . $this->uniqueId . '-field" type="hidden" name="' .
				$name . '" value="' . $fieldValue . '" class="value-holder" />';
		}

			// Add JS-block to HTML output if need be.
		$html[] = $this->addScript();
		$this->tag->addAttribute('id', '');
		$this->tag->setContent(implode(LF, $html));
		return $this->tag->render();
	}

	/**
	 * @return string
	 */
	protected function getPreinitEventsJson() {
		return $this->getEventsJson($this->arguments['preinit']);
	}




	/**
	 * Flatten file list array into single CSV-line containing only the filenames.
	 * @param array $filelist The filelist array to parse.
	 * @return string
	 */
	protected function flattenFilelist($filelist) {

		$output = array();
		if (is_array($filelist)) {
			foreach ($filelist as $file) {
				$output[] = $file;
			}
		}

		return implode(',', $output);

	}


	/**
	 * Get list of previously stored files for this uploader.
	 * @param boolean $getFromPropertyValue If FALSE $this->getValue() otherwise $this->getPropertyValue()
	 * @return array
	 */
	protected function getStoredValue($getFromPropertyValue = TRUE) {

		$return = array();

			// Get the data, either from the passed arguments or the internal functions.
		if (!$this->arguments['storedValue']) {
			$data = ($getFromPropertyValue) ? $this->getPropertyValue() : $this->getValue();
		} else {
			$data = $this->arguments['storedValue'];
		}

		if (is_string($data)) {

				// It's just a string, so let's try to explode it and return the results.
			$tempData = t3lib_div::trimExplode(',', $data, TRUE);
			foreach ($tempData as $tD) {
				$return[] = array(
					'name' => $tD,
					'uid' => FALSE
				);
			}

		} elseif (is_array($data)) {

				// This is an array. We'll assume it has the proper data format (each entry
				// MUST contain 'name'; 'uid' is optional), and just pass it along to
				// the parser directly.
			return $data;

		}

		return $return;
	}


	/**
	 * Adds necessary scripts to header. However, if includeJSInBody is set,
	 * it will return the initialization javascript as a string.
	 *
	 * @return string
	 */
	protected function addScript() {
		$scriptPath = t3lib_extMgm::siteRelPath('fed') . 'Resources/Public/Javascript/';

			// Get existing files using a handy internal function.
		$existingFiles = $this->getStoredValue();

		$propertyName = $this->arguments['property'];
		$formObject = $this->viewHelperVariableContainer->get('Tx_Fluid_ViewHelpers_FormViewHelper', 'formObject');

			// Set uploadfolder for later. We'll need it to determine file sizes of existing files.
		$uploadFolder = ($this->arguments['uploadfolder'] === FALSE) ? $this->infoService->getUploadFolder($formObject, $propertyName) : $this->arguments['uploadfolder'];

			// Add some resources.
		$pluploadPath = $scriptPath . 'com/plupload/js/';
		$this->documentHead->includeFiles(array(
			$scriptPath . 'GearsInit.js',
			$pluploadPath . 'plupload.full.js',
			$pluploadPath . 'jquery.plupload.queue/jquery.plupload.queue.js',
			$pluploadPath . 'jquery.ui.plupload/jquery.ui.plupload.js',
			$pluploadPath . 'jquery.ui.plupload/css/jquery.ui.plupload.css',
			$pluploadPath . 'jquery.plupload.queue/css/jquery.plupload.queue.css',
			$scriptPath . 'FileListEditor.js',
			t3lib_extMgm::siteRelPath('fed') . 'Resources/Public/Stylesheet/MultiUpload.css'
		));
		if (isset($this->arguments['lang'])) {
			$this->documentHead->includeFiles(array(
				$pluploadPath . 'i18n/' . $this->arguments['lang'] . '.js'
			));
		}

		$flashPath = '/' . $pluploadPath . 'plupload.flash.swf';

			// create JSON objects for each existing file
		foreach ($existingFiles as $k => $fileData) {
			$file = $fileData['name'];
			$size = (string) intval(filesize(PATH_site . $uploadFolder . '/' . $file));
			$existingFiles[$k] = array(
				'id' => 'f' . $k,
				'uid' => intval($fileData['uid']),
				'name' => $file,
				'size' => $size,
				'percent' => 100,
				'completed' => $size,
				'status' => 1,
				'existing' => TRUE
			);
		}

		$buttons = explode(',', $this->arguments['buttons']);
		$resize = array();
		if ($this->arguments['resizeWidth']) {
			$resize['width'] = $this->arguments['resizeWidth'];
		}
		if ($this->arguments['resizeHeight'] > 0) {
			$resize['height'] = $this->arguments['resizeHeight'];
		}
		if (count($resize) > 0) {
			$resize['quality'] = $this->arguments['resizeQuality'];
		}

		$options = array(
			'url' => $this->getUrl(),
			'runtimes' => $this->arguments['runtimes'],
			'autostart' => $this->arguments['autostart'],
			'filters' => $this->arguments['filters'],
			'files' => $existingFiles,
			'flash_swf_url' => $flashPath,
			'max_file_size' => $this->arguments['maxFileSize'],
			'chunk_size' => $this->arguments['chunkSize'],
			'header' => $this->arguments['header'],
			'header_title' => $this->arguments['headerTitle'],
			'header_subtitle' => $this->arguments['headerSubtitle'],
			'resize' => $resize,
			'buttons' => array(
				'browse' => in_array('browse', $buttons),
				'start' => in_array('start', $buttons),
				'stop' => in_array('stop', $buttons),
			)
		);
		$optionsJson = $this->jsonService->encode($options);
			// remove last }
		$optionsJson = substr($optionsJson, 0, -1);
		if (!empty($this->arguments['preinit'])) {
			$optionsJson .= ',"preinit":{';
			$preInitHandler = array();
			foreach ($this->arguments['preinit'] as $preInitEvent => $preInitEventHandler) {
				$preInitHandler[] = $preInitEvent . ':' . $preInitEventHandler;
			}
			$optionsJson .= implode(',', $preInitHandler) . '}';
		}
		if (!empty($this->arguments['init'])) {
			$optionsJson .= ',"init":{';
			$initHandler = array();
			foreach ($this->arguments['init'] as $initEvent => $initEventHandler) {
				$initHandler[] = $initEvent . ':' . $initEventHandler;
			}
			$optionsJson .= implode(',', $initHandler) . '}';
		}
		$optionsJson .= '}';

		$scriptBlock = '
			var ' . $this->uniqueId . ' = null;
			var ' . $this->uniqueId . 'options = ' . $optionsJson . ';
			jQuery(document).ready(function() { ' . $this->uniqueId . ' = jQuery("#' . $this->uniqueId . '").' .
				$this->arguments['initFunctionName'] . '( ' . $this->uniqueId . 'options ); });';

			// Set headers OR return the script block if told to do so.
		if ($this->arguments['insertJSInBody']) {
			return '<script type="text/javascript">' . $scriptBlock . '</script>';
		} else {
			$this->documentHead->includeHeader($scriptBlock, 'js');
		}
		return '';
	}

	/**
	 * Returns a URL appropriate for the current controller and Domain Object
	 * to use the "upload" action
	 * @return string
	 */
	public function getUrl() {
		$formObject = $this->viewHelperVariableContainer->get('Tx_Fluid_ViewHelpers_FormViewHelper', 'formObject');
		$propertyName = $this->arguments['property'];
		if ($this->arguments['url']) {
			$url = $this->arguments['url'];
		} elseif ($formObject && $propertyName) {
			$formObjectClass = get_class($formObject);
			$controllerName = $this->controllerContext->getRequest()->getControllerName();
				// Set pluginName dynamically: if found in arguments, use that name instead.
			$pluginName = (isset($this->arguments['pluginName'])) ? $this->arguments['pluginName'] : $this->controllerContext->getRequest()->getPluginName();
			$extensionName = $this->controllerContext->getRequest()->getControllerExtensionName();
			$arguments = array(
				'objectType' => $formObjectClass,
				'propertyName' => $propertyName
			);
			$url = $this->controllerContext->getUriBuilder()
				->uriFor($this->arguments['actionName'], $arguments, $controllerName, $extensionName, $pluginName);

				// If URL isn't prefixed with protocol (http or https), add a slash to the
				// beginning to make browsers what can't respects baseURL get to da choppah!
			if (!preg_match('/^http(s{0,1})\:\/\//i', $url)) {
				$url = '/' . $url;
			}
		} else {
			throw new Tx_Fluid_Exception('Multiupload ViewHelper requires either url argument or associated form object', 1312051527);
		}
		return $url;
	}

}
