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
 * @version $Id$
 * @copyright Copyright belongs to the respective authors
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 * @package Fed
 * @subpackage ViewHelpers\Data
 */
class Tx_Fed_ViewHelpers_Data_FuncViewHelper extends Tx_Fed_Core_ViewHelper_AbstractViewHelper {

	public function initializeArguments() {
		$this->registerArgument('func', 'string', 'Function name to be called; can be an absolute reference (leave out $instance) or a method name for $instance');
		$this->registerArgument('instance', 'string', 'If specified, runs $func on $instance', FALSE, NULL);
		$this->registerArgument('arguments', 'array', 'Array of arguments, in order, to pass to the function', FALSE, array());
	}

	/**
	 * Render method
	 *
	 * @return string
	 */
	public function render() {
		$func = $this->arguments['func'];
		$instance = $this->arguments['instance'];
		$arguments = $this->arguments['arguments'];
		$content = $this->renderChildren();
		if (count($arguments) == 0 && trim($content) != '') {
			// innerHTML is assumed to be the only parameter to pass to $func
			array_push($arguments, $content);
		}

		if ($instance) {
			$method = array($instance, $func);
		} else {
			$method = $func;
		}
		if (count($arguments) === 1) {
			$output = $method(array_pop($arguments));
		} else {
			$output = call_user_func_array($method, $arguments);
		}
		return $output;
	}

}

?>