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
 * Renders a secondary Fluid template with optional arguments
 *
 * @author Claus Due <claus@wildside.dk>, Wildside A/S
 * @package Fed
 * @subpackage ViewHelpers
 */
class Tx_Fed_ViewHelpers_RenderViewHelper extends Tx_Fed_Core_ViewHelper_AbstractViewHelper {

	/**
	 * @var Tx_Fed_Service_Render
	 */
	protected $renderService;

	/**
	 * @param Tx_Fed_Service_Render $partialRender
	 */
	public function injectPartialRender(Tx_Fed_Service_Render $renderService) {
		$this->renderService = $renderService;
	}

	/**
	 * Initialize
	 */
	public function initializeArguments() {
		$this->registerArgument('template', 'string', 'Site-relative path of Fluid template file to render', TRUE);
		$this->registerArgument('arguments', 'array', 'Arguments for the partial template', FALSE, NULL);
	}

	/**
	 * @return string
	 */
	public function render() {
		return $this->renderService->renderTemplateFile($this->arguments['template'], $this->arguments['arguments']);
	}

}
