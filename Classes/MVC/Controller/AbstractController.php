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
 * Controller
 *
 * @package Fed
 * @subpackage MVC/Controller
 */
abstract class Tx_Fed_MVC_Controller_AbstractController extends Tx_Extbase_MVC_Controller_ActionController {

	/**
	 * @var Tx_Fed_Service_Domain
	 */
	protected $infoService;

	/**
	 * @var Tx_Fed_Service_Json
	 */
	protected $jsonService;

	/**
	 * @var Tx_Fed_Utility_ExtJS
	 */
	protected $extJSService;

	/**
	 * @var Tx_Flux_Service_FluxService
	 */
	protected $flexform;

	/**
	 * @var Tx_Fed_Utility_DocumentHead
	 */
	protected $documentHead;

	/**
	 * @var Tx_Fed_Service_File
	 */
	protected $fileService;

	/**
	 * @var Tx_Fed_Service_Email
	 */
	protected $emailService;

	/**
	 * @param Tx_Fed_Service_Domain $infoService
	 */
	public function injectInfoService(Tx_Fed_Service_Domain $infoService) {
		$this->infoService = $infoService;
	}

	/**
	 * @param Tx_Fed_Service_Json $jsonService
	 */
	public function injectJSONService(Tx_Fed_Service_Json $jsonService) {
		$this->jsonService = $jsonService;
	}

	/**
	 * @param Tx_Fed_Utility_ExtJS $extJSService
	 */
	public function injectExtJSService(Tx_Fed_Utility_ExtJS $extJSService) {
		$this->extJSService = $extJSService;
	}

	/**
	 * @param Tx_Flux_Service_FluxService $flexform
	 */
	public function injectFlexFormService(Tx_Flux_Service_FluxService $flexform) {
		$this->flexform = $flexform;
	}

	/**
	 * @param Tx_Fed_Utility_DocumentHead $documentHead
	 */
	public function injectDocumentHead(Tx_Fed_Utility_DocumentHead $documentHead) {
		$this->documentHead = $documentHead;
	}

	/**
	 * @param Tx_Fed_Service_File $fileService
	 */
	public function injectFileService(Tx_Fed_Service_File $fileService) {
		$this->fileService = $fileService;
	}

	/**
	 * @param Tx_Fed_Service_Email $emailService
	 */
	public function injectEmailService(Tx_Fed_Service_Email $emailService) {
		$this->emailService = $emailService;
	}

	/**
	 * Clear the page cache for specified pages or current page
	 *
	 * @param mixed $pids
	 */
	protected function clearPageCache($pids = NULL) {
		if ($pids === NULL) {
			$pids = $GLOBALS['TSFE']->id;
		}
		if ($this->cacheService instanceof Tx_Extbase_Service_CacheService) {
			$this->cacheService->clearPageCache($pids);
		} elseif (class_exists('Tx_Extbase_Utility_Cache')) {
			call_user_func_array(array('Tx_Extbase_Utility_Cache', 'clearPageCache'), array(($pids)));
		}
	}

	/**
	 * Get the flexform definition from the current cObj instance
	 *
	 * @param boolean $fallback Set this to TRUE if you get unexpected FlexForm output - cObj ONLY stores the first detected FlexForm based on Controller name
	 * @return array
	 * @api
	 */
	public function getFlexForm($fallback = FALSE) {
		if (!$fallback) {
			$cObj = $this->configurationManager->getContentObject()->data;
			$this->flexform->setContentObjectData($cObj);
			return $this->flexform->getAll();
		}
		$data = $this->configurationManager->getContentObject()->data;
		$flexform = $data['pi_flexform'];
		$array = array();
		$dom = new DOMDocument();
		$dom->loadXML($flexform);
		foreach ($dom->getElementsByTagName('field') as $field) {
			$name = $field->getAttribute('index');
			$value = $field->getElementsByTagName('value')->item(0)->nodeValue;
			$value = trim($value);
			$array[$name] = $value;
		}
		return $array;
	}

	/**
	 * Constructs an instance of $className and validates after applying values
	 * from $data. Does not generate validation messages - is purely intended
	 * to validate a form's contents through AJAX before submission is allowed.
	 * If $className is not specified a DomainObject of the type related to this
	 * controller is assumed.
	 *
	 * Circumvents request processing to output a JSON response directly.
	 *
	 * @param array $data Associative array of data to be validated
	 * @param string $action Not used
	 * @return string
	 */
	public function validateAction($data = array(), $action = NULL) {
		$errorArray = array();
		$parameters = $this->reflectionService->getMethodParameters(get_class($this), $data['action'] . 'Action');
		unset($data['action']);
		$hasErrors = FALSE;
		foreach ($parameters as $argumentName=>$objectData) {
			$className = $objectData['class'];
			if (!$className || !is_array($data[$argumentName])) {
				continue;
			}
			$propertyNames = $this->reflectionService->getClassPropertyNames($className);

			$instance = $this->objectManager->get($className);
			$validatorResolver = $this->objectManager->get('Tx_Extbase_Validation_ValidatorResolver');
			$validator = $validatorResolver->getBaseValidatorConjunction($className);

			$propertyMapper = $this->objectManager->get('Tx_Extbase_Property_Mapper');
			$propertyMapper->map($propertyNames, $data[$argumentName], $instance);

			 if (method_exists($validator, 'validate')) {
				$isValid = $validator->validate($instance);
				$errors = $isValid->getFlattenedErrors();
			} else {
				$validator->isValid($instance);
				$errors = $validator->getErrors();
			}

			$errorMessages = $this->getErrorMessages($errors);
			if (count($errorMessages) > 0) {
				$hasErrors = TRUE;
			}
			$errorArray[$argumentName] = $errorMessages;
		}
		if ($hasErrors === FALSE) {
			echo '1';
		} else {
			$this->flashMessageContainer->getAllMessagesAndFlush();
			$json = $this->jsonService->encode($errorArray);
			echo $json;
		}
		exit();
	}

	/**
	 * Get error messages
	 *
	 * @param mixed $errors
	 * @return array
	 */
	private function getErrorMessages($errors) {
		$errorArray = array();
		foreach ($errors as $name => $error) {
			if (is_array($error)) {
				$propertyErrors = $error;
			} else {
				$propertyErrors = array($error);
			}
			$errorArray[$name] = array();
			foreach ($propertyErrors as $propertyError) {
				array_push($errorArray[$name], array(
					'name' => $name,
					'message' => $propertyError->getMessage(),
					'code' => $propertyError->getCode()
				));
			}
		}
		return $errorArray;
	}

	/**
	 * Handles uploads from plupload component. Immediately outputs response -
	 * cannot persist objects!
	 *
	 * @param string $objectType
	 * @param string $propertyName
	 * @return string
	 * @api
	 */
	public function uploadAction($objectType, $propertyName) {
		try {
			if (isset($_SERVER['HTTP_CONTENT_TYPE'])) {
				$contentType = $_SERVER['HTTP_CONTENT_TYPE'];
			} elseif (isset($_SERVER['CONTENT_TYPE'])) {
				$contentType = $_SERVER['CONTENT_TYPE'];
			} else {
				$contentType = NULL;
			}
			$targetDir = PATH_site . $this->infoService->getUploadFolder($objectType, $propertyName);
			$sourceFilename = $_FILES['file']['tmp_name'];
			if (is_file($sourceFilename) === FALSE) {
				exit();
			}
			$chunk = isset($_REQUEST['chunk']) ? $_REQUEST['chunk'] : 0;
			$chunks = isset($_REQUEST['chunks']) ? $_REQUEST['chunks'] : 0;
			$filename = isset($_REQUEST['name']) ? $_REQUEST['name'] : '';

			// What follows is my (Anders Gissel) take on the subject, using some Frankenweenie code to make chunking work.

			// Use t3lib_basicFileFunctions to get a unique filename, in case we actually need it.
			/** @var $fileHandler t3lib_basicFileFunctions */
			$fileHandler = t3lib_div::makeInstance('t3lib_basicFileFunctions');
			$filename = $fileHandler->cleanFileName($filename);
			if (file_exists($targetDir . $filename) && filesize($targetDir . $filename) > 0) {
				$filename = basename($fileHandler->getUniqueName($filename, $targetDir));
			}

			// Touch the filename. This ensures that if any other user initiates an upload with the same name while
			// we're spewing chunks, we will not get a filename clash later on. Especially in the part-file. That
			// would be bad.
			touch($targetDir . '/' . $filename);

			// Get a temporary filename for our upload
			$tempFilename = $filename . '.part';
			$tempFileComplete = $targetDir . '/' . $tempFilename;

			// The following code block is lifted almost without change from the plUpload example

			// Handle non multipart uploads - older WebKit versions didn't support multipart in HTML5
			if (strpos($contentType, 'multipart') !== FALSE) {
				if (isset($_FILES['file']['tmp_name']) && is_uploaded_file($_FILES['file']['tmp_name'])) {
					// Open temp file
					$out = fopen($tempFileComplete, $chunk == 0 ? 'wb' : 'ab');
					if ($out) {
						// Read binary input stream and append it to temp file
						$in = fopen($_FILES['file']['tmp_name'], 'rb');

						if ($in) {
							while ($buff = fread($in, 4096)) {
								fwrite($out, $buff);
							}
						} else {
							die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}');
						}

						fclose($in);
						fclose($out);
						@unlink($_FILES['file']['tmp_name']);
					} else {
						die('{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream."}, "id" : "id"}');
					}
				} else {
					die('{"jsonrpc" : "2.0", "error" : {"code": 103, "message": "Failed to move uploaded file."}, "id" : "id"}');
				}
			} else {
				// Open temp file
				$out = fopen($tempFileComplete, $chunk == 0 ? "wb" : "ab");
				if ($out) {
					// Read binary input stream and append it to temp file
					$in = fopen("php://input", "rb");

					if ($in) {
						while ($buff = fread($in, 4096)) {
							fwrite($out, $buff);
						}
					} else {
						die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}');
					}

					fclose($in);
					fclose($out);
				} else {
					die('{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream."}, "id" : "id"}');
				}
			}

			// END OF PLAGIARISM

			// Check if file has been uploaded - in that case, send a response back.

			if (!$chunks || $chunk == $chunks - 1) {

				$newFilename = $this->fileService->move($tempFileComplete, $targetDir . '/' . $filename);

				$response = array(
					'name' => basename($newFilename)
				);

				echo $this->jsonService->getRpcResponse($response);
			}


		} catch (Exception $e) {
			echo $this->jsonService->getRpcError($e);
		}
		exit();
	}

	/**
	 * Handles special REST CRUD requests from ExtJS4 Model Proxies type "rest"
	 *
	 * @param string $crudAction String name of CRUD action (create, read, update or destroy)
	 * @return string
	 * @api
	 */
	public function restAction($crudAction = 'read') {
		switch ($crudAction) {
			case 'update': return $this->performRestUpdate();
			case 'destroy': return $this->performRestDestroy();
			case 'create': return $this->performRestCreate();
			case 'read':
			default: return $this->performRestRead();
		}
	}

	/**
	 * PURELY INTERNAL - CAN BE OVERRIDDEN
	 * @return object
	 */
	protected function performRestCreate() {
		$data = $this->fetchRestBodyData();
		$object = $this->fetchRestObject();
		$repository = $this->infoService->getRepositoryInstance($object);
		$extensionName = $this->infoService->getExtensionName($object);
		$storagePid = $this->getConfiguredStoragePid($extensionName);
		// do NOT allow creation of UID=0
		unset($data['uid']);
		$object = $this->extJSService->mapDataFromExtJS($object, $data);
		$object->setPid($storagePid);
		$repository->add($object);
		$persistenceManager = $this->objectManager->get('Tx_Extbase_Persistence_Manager');
		$persistenceManager->persistAll();
		return $this->formatRestResponseData($object);
	}

	/**
	 * PURELY INTERNAL - CAN BE OVERRIDDEN
	 * @return mixed
	 */
	protected function performRestRead() {
		$object = $this->fetchRestObject();
		$repository = $this->infoService->getRepositoryInstance($object);
		$all = $repository->findAll()->toArray();
		$export = $this->extJSService->exportDataToExtJS($all);
		return $this->jsonService->encode($export);
	}

	/**
	 * PURELY INTERNAL - CAN BE OVERRIDDEN
	 * @return object
	 */
	protected function performRestUpdate() {
		$data = $this->fetchRestBodyData();
		$object = $this->fetchRestObject();
		$repository = $this->infoService->getRepositoryInstance($object);
		$target = $repository->findOneByUid($data['uid']);
		$object = $this->extJSService->mapDataFromExtJS($target, $data);
		$repository->update($object);
		return $this->formatRestResponseData($object);
	}

	/**
	 * PURELY INTERNAL - CAN BE OVERRIDDEN
	 * @return string
	 */
	protected function performRestDestroy() {
		$data = $this->fetchRestBodyData();
		$object = $this->fetchRestObject();
		$repository = $this->infoService->getRepositoryInstance($object);
		$target = $repository->findOneByUid($data['uid']);
		$repository->remove($target);
		return $this->formatRestResponseData();
	}

	/**
	 * Fetch an instance of an aggregate root object as specified by the request parameters
	 * @return Tx_Extbase_DomainObject_AbstractEntity
	 * @api
	 */
	public function fetchRestObject() {
		$thisClass = get_class($this);
		$controllerName = $this->request->getArgument('controller');
		$className = 'Controller_' . $controllerName . 'Controller';
		$objectClassname = str_replace($className, 'Domain_Model_', $thisClass) . $controllerName;
		$object = $this->objectManager->get($objectClassname);
		return $object;
	}

	/**
	 * Returns associative array (with subarrays if necessary) of REST body
	 *
	 * @param string $body The request body to parse, empty for auto-fetch
	 * @return array
	 */
	public function fetchRestBodyData($body = NULL) {
		if ($body === NULL) {
			$body = file_get_contents('php://input');
		}
		$arr = array();
		$data = $this->jsonService->decode($body);
		foreach ($data as $k => $v) {
			$arr[$k] = $v;
		}
		return $arr;
	}

	/**
	 * Fetch an associative array of fields posted as REST request body
	 *
	 * @param string $body The request body to parse, empty for auto-fetch
	 * @return array
	 * @api
	 */
	public function fetchRestBodyFields($body = NULL) {
		return array_keys($this->fetchRestBodyData($body));
	}

	/**
	 * Formats $data into a format agreable with ExtJS4 REST
	 *
	 * @param mixed $data Empty for NULL response
	 * @return mixed
	 */
	public function formatRestResponseData($data = NULL) {
		if ($data === NULL) {
			return '{}';
		}
		$responseData = $this->extJSService->exportDataToExtJS($data);
		$response = $this->jsonService->encode($responseData);
		return $response;
	}

	/**
	 * Get the current configured storage PID for $extensionName
	 * @param string $extensionName Optional extension name, empty for current extension name
	 * @return integer
	 */
	public function getConfiguredStoragePid() {
		$object = $this->fetchRestObject();
		if ($object) {
			$extensionName = $this->infoService->getExtensionName($object);
		} else {
			$extensionName = $this->request->getExtensionName();
		}
		$config = $this->infoService->getExtensionTyposcriptConfiguration($extensionName);
		if (is_array($config)) {
			return $config['persistence']['storagePid'];
		} else {
			return $GLOBALS['TSFE']->id;
		}
	}

}
