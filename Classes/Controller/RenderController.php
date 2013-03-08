<?php
/*                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU General Public License as published by the Free   *
 * Software Foundation, either version 3 of the License, or (at your      *
 * option) any later version.                                             *
 *                                                                        *
 * This script is distributed in the hope that it will be useful, but     *
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHAN-    *
 * TABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General      *
 * Public License for more details.                                       *
 *                                                                        *
 * You should have received a copy of the GNU General Public License      *
 * along with the script.                                                 *
 * If not, see http://www.gnu.org/licenses/gpl.html                       *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

/**
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */

class RenderController {

	/**
	 * Renders a pdf of the current html
	 *
	 * @param	object		$pObj: The parent object
	 * @return	void
	 */
	public function lastFeHook(&$pObj) {
		$settings = $GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_dompdfbase.']['settings.'];

		if (array_key_exists('pdf', $_GET) && $_GET['pdf'] === '1') {
			if ($settings['allowPdf']) {
				$this->generatePdf($pObj['pObj']->content, $settings);
				die();
			} else {
				$pObj['pObj']->content = 'The Typoscript Setting for this page must have "plugin.tx_dompdfbase.settings.allowPdf = 1" in order to generate a pdf';
			}
		}

		if (array_key_exists('print', $_GET) && $_GET['print'] === '1') {
			if ($settings['allowPdf']) {
				$pObj['pObj']->content = $this->mediaPrintToScreen($pObj['pObj']->content);
			} else {
				$pObj['pObj']->content = 'The Typoscript Setting for this page must have "plugin.tx_dompdfbase.settings.allowPrint = 1" in order to display a print version';
			}
		}
	}

	/**
	 * @param $html
	 */
	public function mediaPrintToScreen($html) {
		return str_ireplace(
			array(' media="screen"', " media='screen'", ' media="print"', " media='print'"),
			array(' media="none"', ' media="none"', ' media="screen"', ' media="screen"'),
			$html
		);
	}

	/**
	 * @param $html
	 * @param array $settings
	 */
	public function generatePdf($html, $settings = array()) {
		$settings = $this->cleanSettings($settings);

		define('DOMPDF_DIR', str_replace(DIRECTORY_SEPARATOR, '/', realpath(t3lib_extMgm::extPath('dompdf_base') . 'Resources/Private/Php/DomPdf/')));
		define('DOMPDF_INC_DIR', DOMPDF_DIR . '/include');
		define('DOMPDF_LIB_DIR', DOMPDF_DIR . '/lib');
		require_once(DOMPDF_INC_DIR . '/functions.inc.php');
		define('DOMPDF_FONT_DIR', $settings['DOMPDF_FONT_DIR']);

		mkdir(PATH_site . 'typo3temp/dompdf_base/');
		define('DOMPDF_FONT_CACHE', PATH_site . 'typo3temp/dompdf_base/');
		define('DOMPDF_TEMP_DIR', sys_get_temp_dir());
		define('DOMPDF_CHROOT', PATH_site);
		define('DOMPDF_UNICODE_ENABLED', $settings['DOMPDF_UNICODE_ENABLED']);
		define('DOMPDF_ENABLE_FONTSUBSETTING', $settings['DOMPDF_ENABLE_FONTSUBSETTING']);
		define('DOMPDF_PDF_BACKEND', $settings['DOMPDF_PDF_BACKEND']);
		define('DOMPDF_DEFAULT_MEDIA_TYPE', $settings['DOMPDF_DEFAULT_MEDIA_TYPE']);
		define('DOMPDF_DEFAULT_PAPER_SIZE', $settings['DOMPDF_DEFAULT_PAPER_SIZE']);
		define('DOMPDF_DEFAULT_FONT', $settings['DOMPDF_DEFAULT_FONT']);
		define('DOMPDF_DPI', $settings['DOMPDF_DPI']);
		define('DOMPDF_ENABLE_PHP', $settings['DOMPDF_ENABLE_PHP']);
		define('DOMPDF_ENABLE_JAVASCRIPT', $settings['DOMPDF_ENABLE_JAVASCRIPT']);
		define('DOMPDF_ENABLE_REMOTE', $settings['DOMPDF_ENABLE_REMOTE']);

		define('DOMPDF_LOG_OUTPUT_FILE', DOMPDF_FONT_CACHE . 'log.html');
		define('DOMPDF_FONT_HEIGHT_RATIO', $settings['DOMPDF_FONT_HEIGHT_RATIO']);
		define('DOMPDF_ENABLE_CSS_FLOAT', $settings['DOMPDF_ENABLE_CSS_FLOAT']);
		define('DOMPDF_ENABLE_AUTOLOAD', TRUE);
		define('DOMPDF_AUTOLOAD_PREPEND', FALSE);

		define('DOMPDF_ENABLE_HTML5PARSER', $settings['DOMPDF_ENABLE_HTML5PARSER']);
		require_once(DOMPDF_LIB_DIR . '/html5lib/Parser.php');

		if (DOMPDF_ENABLE_AUTOLOAD) {
			require_once(DOMPDF_INC_DIR . '/autoload.inc.php');
			require_once(DOMPDF_LIB_DIR . '/php-font-lib/classes/font.cls.php');
		}

		global $_dompdf_warnings;
		$_dompdf_warnings = array();

		global $_dompdf_show_warnings;
		$_dompdf_show_warnings = FALSE;

		global $_dompdf_debug;
		$_dompdf_debug = FALSE;

		global $_DOMPDF_DEBUG_TYPES;
		$_DOMPDF_DEBUG_TYPES = array(); //array('page-break' => 1);

		define('DEBUGPNG', $settings['DEBUGPNG']);
		define('DEBUGKEEPTEMP', $settings['DEBUGKEEPTEMP']);
		define('DEBUGCSS', $settings['DEBUGCSS']);

		define('DEBUG_LAYOUT', $settings['DEBUG_LAYOUT']);
		define('DEBUG_LAYOUT_LINES', $settings['DEBUG_LAYOUT_LINES']);
		define('DEBUG_LAYOUT_BLOCKS', $settings['DEBUG_LAYOUT_BLOCKS']);
		define('DEBUG_LAYOUT_INLINE', $settings['DEBUG_LAYOUT_INLINE']);
		define('DEBUG_LAYOUT_PADDINGBOX', $settings['DEBUG_LAYOUT_PADDINGBOX']);

		$dompdf = new DOMPDF();
		$dompdf->load_html($html);

		$dompdf->render();
		$dompdf->stream($GLOBALS['TSFE']->page['title'], array(
			'Attachment' => intval($settings['forceDownloadOfPdf'])
		));
	}

	/**
	 * @param $settings
	 */
	public function cleanSettings($settings) {
		$newSettings = array();
		foreach($settings as $setting => $value) {
			$value = $value === 'TRUE' || $value === 'true' ? TRUE : $value;
			$value = $value === 'FALSE' || $value === 'false' ? FALSE : $value;
			$newSettings[$setting] = $value;
		}
		$newSettings['DOMPDF_DPI'] = intval($settings['DOMPDF_DPI']);
		$newSettings['DOMPDF_FONT_HEIGHT_RATIO'] = floatval($settings['DOMPDF_FONT_HEIGHT_RATIO']);
		$newSettings['DOMPDF_FONT_DIR'] = \TYPO3\CMS\Core\Utility\GeneralUtility::getFileAbsFileName($settings['DOMPDF_FONT_DIR']);

		return $newSettings;
	}

}

?>