<?php
/***************************************************************
 *  Copyright notice
 *  (c) 2010 Claus Due <claus@wildside.dk>, Wildside A/S
 *  All rights reserved
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * @author Claus Due, Wildside A/S
 * @package Fed
 * @subpackage Utility
 */
class Tx_Fed_Utility_PDF implements t3lib_Singleton {

	/**
	 * Designed for Bootstrap usage. Overrides headers and outputs PDF source
	 *
	 * @return void
	 */
	public function run() {
		$post = $_POST['tx_fed_pdf'];
		$data = $post['data'];
		$arguments = $post['arguments'];

		$filename = $arguments['filename'] ? : 'file.pdf';

		$pdf = $this->grabPDF($data, $arguments);
		header("Content-type: application/pdf");
		header("Content-Length: " . strlen($pdf));
		header("Content-disposition: attachment; filename={$filename}");
		echo $pdf;
		exit();
	}

	/**
	 * Place the source code as a temporary file, then request it through HTTP
	 * source contains the computed source as viewed by the browser; the
	 * most realistic representation possible. When used with the corresponding
	 * widget, this allows injection of a stylesheet into the computed source,
	 * allowing for style overrides when PDF-"printing".
	 * The temp file is necessary because WebKit supports JS and even AJAX -
	 * and AJAX requires a security context, meaning an HTTP request.
	 *
	 * @param string $data either URL or HTML source
	 * @param array $arguments
	 * @return string
	 */
	private function grabPDF($data, array $arguments) {

		if ($arguments['useHtml']) {
			$data = stripslashes($data);
			$tmp = tempnam(PATH_site . 'typo3temp/', 'wspdfhtml') . ".html";
			file_put_contents($tmp, $data);
			$url = 'http://' . $_SERVER['HTTP_HOST'] . str_replace(PATH_site, '/', $tmp);
		} else {
			$url = $data;
		}

		$cmd = $this->buildCommand($url, $arguments);
		$output = shell_exec($cmd);

		// Delete temp file if any
		if (isset($tmp)) {
			unlink($tmp);
		}

		return $output;
	}

	/**
	 * Generate the command to run.
	 *
	 * @param $url string: the URL to open.
	 * @param $outputFile string: path and filename for output file.
	 * @return string Command string
	 */
	public function buildCommand($url, array $arguments) {

		// Converts relative paths in arguments for wkhtmltopdf to absolute using PATH_Site
		$cliArguments = $arguments['cliArguments'] ? : '';
		$cliArguments = preg_replace(':(user-style-sheet) ([^/]):', '$1 ' . PATH_site . '$2', $cliArguments);
		$cliArguments = escapeshellarg($cliArguments);

		$cmd = 'wkhtmltopdf ' . $cliArguments;
		$cmd .= ' ' . escapeshellarg($url);
		$cmd .= ' - ';

		return $cmd;
	}

}
