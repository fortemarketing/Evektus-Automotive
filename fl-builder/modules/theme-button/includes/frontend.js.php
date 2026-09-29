<?php
/**
 *  Theme Button front-end JS.
 *
 *  The Lightbox and Copy Text actions are handled by the shared helper in
 *  assets/button/button.js, so only the call is per instance. The Button action
 *  runs whatever JavaScript the editor typed into the field, which is per
 *  instance by definition, so that one is still written out here.
 *
 *  @package Evektus
 */

defined( 'ABSPATH' ) || exit;

if ( ! isset( $settings->click_action ) || 'link' === $settings->click_action ) {
	return;
}

$fm_action = $settings->click_action;

// Custom JavaScript from the Button action. Not run in the builder, where it
// would fire against the editing UI.
if ( 'button' === $fm_action && ! isset( $_GET['fl_builder'] ) ) :
	?>
(function ($) {
	$('.fl-node-<?php echo esc_js( $id ); ?> .theme-button').on('click', function () {
		<?php echo $settings->button; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- the Button action is a code field whose purpose is to run the editor's own JavaScript. ?>
	});
})(jQuery);
	<?php
	return;
endif;

if ( ! in_array( $fm_action, array( 'lightbox', 'copy_text' ), true ) ) {
	return;
}

$fm_config = array(
	'scope'        => '.fl-node-' . $id,
	'action'       => $fm_action,
	'lightboxType' => isset( $settings->lightbox_content_type ) ? $settings->lightbox_content_type : 'html',
	'i18n'         => array(
		'copyFailed' => __( 'Failed to copy', 'fl-builder' ),
	),
);
?>
(function () {
	function fmBoot() {
		if (window.ThemeButtonActions) {
			window.ThemeButtonActions.init(<?php echo wp_json_encode( $fm_config ); ?>);
		}
	}

	if ('loading' === document.readyState) {
		document.addEventListener('DOMContentLoaded', fmBoot);
	} else {
		fmBoot();
	}
})();
