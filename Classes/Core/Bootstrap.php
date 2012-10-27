<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2010 Claus Due <claus@wildside.dk>, Wildside A/S
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
 * Bootstrap wrapper for special JSON-only communication between AJAX Widgets and Extbase Controllers
 *
 * @author Claus Due, Wildside A/S
 * @package Fed
 * @subpackage Core
 */
class Tx_Fed_Core_Bootstrap extends Tx_Extbase_Core_Bootstrap {

	/**
	 * @var Tx_Fed_Utility_DomainObjectInfo
	 */
	protected $infoService;

	/**
	 * @var Tx_Fed_Service_Json
	 */
	private $jsonService;

	/**
	 * Runs the request
	 *
	 * @param string $content
	 * @param array $configuration
	 * @return mixed
	 */
	public function run($content, $configuration) {
		$this->initialize($configuration);
		/** @var $infoService Tx_Fed_Service_Domain */
		$this->infoService = $this->objectManager->get('Tx_Fed_Service_Domain');
		/** @var $jsonService Tx_Fed_Service_Json */
		$this->jsonService = $this->objectManager->get('Tx_Fed_Service_Json');
		/** @var $requestHandlerResolver Tx_Extbase_MVC_RequestHandlerResolver */
		$requestHandlerResolver = $this->objectManager->get('Tx_Extbase_MVC_RequestHandlerResolver');
		/** @var $requestHandler Tx_Extbase_MVC_RequestHandlerInterface */
		$requestHandler = $requestHandlerResolver->resolveRequestHandler();
		$response = $requestHandler->handleRequest();
		if ($response === NULL) {
			return NULL;
		}
		$this->resetSingletons();

		$content = $response->getContent();
		$testJSON = $this->jsonService->decode($content);
		$object = $this->detectModelObject($content);
		if (is_array($object) && !$testJSON) {
			$data = $object;
		} elseif (is_array($testJSON)) {
			foreach ($testJSON as $k => $v) {
				$testJSON[$k] = $this->detectModelObject($v);
			}
			$data = $testJSON;
		} elseif (is_object($testJSON)) {
			foreach ($testJSON as $k => $v) {
				$testJSON->$k = $this->detectModelObject($v);
			}
			$data = $testJSON;
		} else {
			$data = $content;
		}

		if (is_array($data) || is_object($data)) {
			$output = $this->jsonService->encode($data);
		} else {
			$output = $data;
		}
		return $output;
	}

	/**
	 * Assert if $content is a reference (table:uid) to a Domain Model Object,
	 * if it is, return JSON-annotated properties
	 *
	 * @param string $content
	 * @return mixed
	 */
	private function detectModelObject($content) {
		if (is_string($content) === FALSE) {
			return $content;
		}
		list ($dataType, $uid) = explode(':', $content);
		if (class_exists($dataType) && intval($uid) > 0) {;
			$repository = $this->infoService->getRepositoryInstance($dataType);
			$object = $repository->findOneByUid($uid);
			if ($object) {
				$data = $this->infoService->getValuesByAnnotation($object, 'json', TRUE);
			} else {
				$data = NULL;
			}
		} else {
			$data = $content;
		}
		return $data;
	}

}
