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
 * Accesses Typoscript paths
 *
 * @author Claus Due <claus@wildside.dk>, Wildside A/S
 * @package Fed
 * @subpackage ViewHelpers
 */
class Tx_Fed_ViewHelpers_TyposcriptViewHelper extends Tx_Fed_Core_ViewHelper_AbstractViewHelper {

	/**
	 * Render
	 *
	 * @param string $path
	 * @return mixed
	 */
	public function render($path=NULL) {
		if ($path === NULL) {
			$path = $this->renderChildren();
		}
		if (!$path) {
			return NULL;
		}
		$all = $this->configurationManager->getConfiguration(Tx_Extbase_Configuration_ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT);
		$segments = explode('.', $path);
		$value = $all;
		foreach ($segments as $path) {
			if (isset($value[$path . '.'])) {
				$value = $value[$path . '.'];
			} else {
				$value = $value[$path];
			}
		}
		if (is_array($value)) {
			$value = Tx_Fed_Utility_Array::convertTypoScriptArrayToPlainArray($value);
		}
		return $value;
	}

}
