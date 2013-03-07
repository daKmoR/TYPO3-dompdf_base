<?php

class RenderController {

	/**
	 * Renders a pdf of the current html
	 *
	 * @param	object		$pObj: The parent object
	 * @return	void
	 */
	public function lastFeHook(&$pObj) {
		if (!(array_key_exists('pdf', $_GET) && $_GET['pdf'] === '1')) return;

		$html = $pObj['pObj']->content;

		define('DOMPDF_DIR', str_replace(DIRECTORY_SEPARATOR, '/', realpath(t3lib_extMgm::extPath('dompdf_base') . 'Resources/Private/Php/DomPdf/')));
		define('DOMPDF_INC_DIR', DOMPDF_DIR . '/include');
		define('DOMPDF_LIB_DIR', DOMPDF_DIR . '/lib');
		require_once(DOMPDF_INC_DIR . '/functions.inc.php');
		define('DOMPDF_FONT_DIR', DOMPDF_DIR . '/lib/fonts/');
		define('DOMPDF_FONT_CACHE', DOMPDF_FONT_DIR);
		define('DOMPDF_TEMP_DIR', sys_get_temp_dir());
		define('DOMPDF_CHROOT', realpath(DOMPDF_DIR));
		define('DOMPDF_UNICODE_ENABLED', true);
		define('DOMPDF_ENABLE_FONTSUBSETTING', false);
		define('DOMPDF_PDF_BACKEND', 'CPDF');
		define('DOMPDF_DEFAULT_MEDIA_TYPE', 'print');
		define('DOMPDF_DEFAULT_PAPER_SIZE', 'letter');
		define('DOMPDF_DEFAULT_FONT', 'serif');
		define('DOMPDF_DPI', 96);
		define('DOMPDF_ENABLE_PHP', false);
		define('DOMPDF_ENABLE_JAVASCRIPT', false);
		define('DOMPDF_ENABLE_REMOTE', true);

		define('DOMPDF_LOG_OUTPUT_FILE', DOMPDF_FONT_DIR.'log.htm');
		define('DOMPDF_FONT_HEIGHT_RATIO', 1.1);
		define('DOMPDF_ENABLE_CSS_FLOAT', true);
		define('DOMPDF_ENABLE_AUTOLOAD', true);
		define('DOMPDF_AUTOLOAD_PREPEND', false);

		define('DOMPDF_ENABLE_HTML5PARSER', true);
		require_once(DOMPDF_LIB_DIR . '/html5lib/Parser.php');

		if (DOMPDF_ENABLE_AUTOLOAD) {
			require_once(DOMPDF_INC_DIR . '/autoload.inc.php');
			require_once(DOMPDF_LIB_DIR . '/php-font-lib/classes/font.cls.php');
		}

		//mb_internal_encoding('UTF-8');

		global $_dompdf_warnings;
		$_dompdf_warnings = array();

		global $_dompdf_show_warnings;
		$_dompdf_show_warnings = false;

		global $_dompdf_debug;
		$_dompdf_debug = false;

		global $_DOMPDF_DEBUG_TYPES;
		$_DOMPDF_DEBUG_TYPES = array(); //array('page-break' => 1);

		define('DEBUGPNG', false);
		define('DEBUGKEEPTEMP', false);
		define('DEBUGCSS', false);

		define('DEBUG_LAYOUT', false);
		define('DEBUG_LAYOUT_LINES', true);
		define('DEBUG_LAYOUT_BLOCKS', true);
		define('DEBUG_LAYOUT_INLINE', true);
		define('DEBUG_LAYOUT_PADDINGBOX', true);

		$dompdf = new DOMPDF();
		$dompdf->load_html($html);

		$dompdf->render();
		$dompdf->stream('output.pdf', array(
			'Attachment' => 0
		));
	}

}


?>