<?php

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
		define('DOMPDF_FONT_DIR', DOMPDF_DIR . '/lib/fonts/');
		define('DOMPDF_FONT_CACHE', DOMPDF_FONT_DIR);
		define('DOMPDF_TEMP_DIR', sys_get_temp_dir());
		define('DOMPDF_CHROOT', realpath(DOMPDF_DIR));
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

		define('DOMPDF_LOG_OUTPUT_FILE', DOMPDF_FONT_DIR.'log.htm');
		define('DOMPDF_FONT_HEIGHT_RATIO', 1.1);
		define('DOMPDF_ENABLE_CSS_FLOAT', $settings['DOMPDF_ENABLE_CSS_FLOAT']);
		define('DOMPDF_ENABLE_AUTOLOAD', TRUE);
		define('DOMPDF_AUTOLOAD_PREPEND', FALSE);

		define('DOMPDF_ENABLE_HTML5PARSER', $settings['DOMPDF_ENABLE_HTML5PARSER']);
		require_once(DOMPDF_LIB_DIR . '/html5lib/Parser.php');

		if (DOMPDF_ENABLE_AUTOLOAD) {
			require_once(DOMPDF_INC_DIR . '/autoload.inc.php');
			require_once(DOMPDF_LIB_DIR . '/php-font-lib/classes/font.cls.php');
		}

		//mb_internal_encoding('UTF-8');

		global $_dompdf_warnings;
		$_dompdf_warnings = array();

		global $_dompdf_show_warnings;
		$_dompdf_show_warnings = FALSE;

		global $_dompdf_debug;
		$_dompdf_debug = FALSE;

		global $_DOMPDF_DEBUG_TYPES;
		$_DOMPDF_DEBUG_TYPES = array(); //array('page-break' => 1);

		define('DEBUGPNG', FALSE);
		define('DEBUGKEEPTEMP', FALSE);
		define('DEBUGCSS', FALSE);

		define('DEBUG_LAYOUT', FALSE);
		define('DEBUG_LAYOUT_LINES', TRUE);
		define('DEBUG_LAYOUT_BLOCKS', TRUE);
		define('DEBUG_LAYOUT_INLINE', TRUE);
		define('DEBUG_LAYOUT_PADDINGBOX', TRUE);

		$dompdf = new DOMPDF();
		$dompdf->load_html($html);

		$dompdf->render();
		$dompdf->stream('output.pdf', array(
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
			$value = $value === 'FALSE' || $value === 'false' ? TRUE : $value;
			$newSettings[$setting] = $value;
		}
		$newSettings['DOMPDF_DPI'] = intval($settings['DOMPDF_DPI']);
		return $newSettings;
	}

}

?>