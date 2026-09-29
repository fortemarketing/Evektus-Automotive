<?php
/**
 *  Theme Counter front-end JS.
 *
 *  The count itself lives in js/frontend.js, so only the call is per instance.
 *
 *  @package Evektus
 */

defined( 'ABSPATH' ) || exit;

?>
(function () {
	function fmBoot() {
		if (window.ThemeCounter) {
			new window.ThemeCounter(<?php echo wp_json_encode( $module->get_js_config() ); ?>);
		}
	}

	if ('loading' === document.readyState) {
		document.addEventListener('DOMContentLoaded', fmBoot);
	} else {
		fmBoot();
	}
})();
