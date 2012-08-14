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
 * Lipsum ViewHelper
 *
 * Renders Lorem Ipsum text according to either TS settings
 * or provided arguments.
 *
 * @author Claus Due, Wildside A/S
 * @package Fed
 * @subpackage ViewHelpers
 */
class Tx_Fed_ViewHelpers_LipsumViewHelper extends Tx_Fed_Core_ViewHelper_AbstractViewHelper {

	/**
	 * @var	tslib_cObj
	 */
	protected $contentObject;

	/**
	 * @var Tx_Extbase_Configuration_ConfigurationManagerInterface
	 */
	protected $configurationManager;

	/**
	 * @param Tx_Extbase_Configuration_ConfigurationManagerInterface $configurationManager
	 * @return void
	 */
	public function injectConfigurationManager(Tx_Extbase_Configuration_ConfigurationManagerInterface $configurationManager) {
		$this->configurationManager = $configurationManager;
		$this->contentObject = $this->configurationManager->getContentObject();
	}

	/**
	 * Initialize arguments
	 */
	public function initializeArguments() {
		$this->registerArgument('paragraphs', 'integer', 'Number of paragraphs to output');
		$this->registerArgument('wordsPerParagraph', 'integer', 'Number of words per paragraph');
		$this->registerArgument('skew', 'integer', 'Amount in number of words to vary the number of words per paragraph');
		$this->registerArgument('html', 'boolean', 'If TRUE, renders output as HTML paragraph tags in the same way an RTE would');
		$this->registerArgument('parseFuncTSPath', 'string', 'If you want another parseFunc for HTML processing, enter the TS path here');
	}

	/**
	 * Renders Lorem Ipsum paragraphs. If $lipsum is provided it
	 * will be used as source text. If not provided as an argument
	 * or as inline argument, $lipsum is fetched from TypoScript settings.
	 *
	 * @param string $lipsum A string of Lorem Ipsum text paragraphs divided by white spaces, a relative file path or an EXT:myext/path/to/file format.
	 * @return string
	 */
	public function render($lipsum=NULL) {
		$typoScript = $this->configurationManager->getConfiguration(Tx_Extbase_Configuration_ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS, 'Fed', 'Fce');
		$settings = $typoScript['viewhelpers']['lipsum'];
		foreach ($settings as $setting => $value) {
			if ($this->arguments[$setting]) {
				$settings[$setting] = $this->arguments['setting'];
			}
		}

		if (strlen($lipsum) === 0) {
			$lipsum = $settings['file'];
		}
		if (strlen($lipsum) < 255 && !preg_match('/[^a-z0-9_\./]/i', $lipsum)) {
				// argument is most likely a file reference.
			$sourceFile = t3lib_div::getFileAbsFileName($lipsum);
			$lipsum = file_get_contents($sourceFile);
		}
		$lipsum = preg_replace('/[\\r\\n]{1,}/i', "\n", $lipsum);
		$paragraphs = explode("\n", $lipsum);
		$paragraphs = array_slice($paragraphs, 0, intval($settings['paragraphs']));
		foreach ($paragraphs as $index => $paragraph) {
			$length = $settings['wordsPerParagraph'] + rand(0 - intval($settings['skew']), intval($settings['skew']));
			$words = explode(' ', $paragraph);
			$paragraphs[$index] = implode(' ', array_slice($words, 0, $length));
		}

		$lipsum = implode("\n", $paragraphs);
		if ((boolean) $settings['html'] === TRUE) {
			$lipsum = $this->contentObject->parseFunc($lipsum, array(), '< ' . $settings['parseFuncTSPath']);
		}
		return $lipsum;
	}

}
