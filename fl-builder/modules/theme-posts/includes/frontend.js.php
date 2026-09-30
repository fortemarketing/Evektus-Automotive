<?php
/**
 *  Theme Posts front-end JS php file
 *
 *  Hands this instance's settings to ThemePosts (js/frontend.js).
 *
 *  @package Evektus
 */

defined( 'ABSPATH' ) || exit;

/**
 * Beaver Builder's global responsive breakpoints, used for Swiper's own.
 *
 * BB treats each breakpoint as a max-width -- "medium" means up to and
 * including medium_breakpoint -- while Swiper's breakpoint keys are min-widths.
 * Hence the +1 on each below: the next device band starts one pixel past where
 * BB's ends.
 */
$fm_global_settings = FLBuilderModel::get_global_settings();
$fm_mobile_bp       = isset( $fm_global_settings->responsive_breakpoint ) ? (int) $fm_global_settings->responsive_breakpoint : 768;
$fm_medium_bp       = isset( $fm_global_settings->medium_breakpoint ) ? (int) $fm_global_settings->medium_breakpoint : 992;
$fm_large_bp        = isset( $fm_global_settings->large_breakpoint ) ? (int) $fm_global_settings->large_breakpoint : 1200;
$fm_medium_bp       = max( $fm_medium_bp, $fm_mobile_bp );
$fm_large_bp        = max( $fm_large_bp, $fm_medium_bp );

/**
 * A responsive setting's value on each device, each inheriting from the next
 * size up when left empty -- the way Beaver Builder resolves them.
 *
 * @param object $settings Module settings.
 * @param string $name     Setting name.
 * @param int    $fallback Value when even the desktop setting is empty.
 * @return array
 */
$fm_responsive = function ( $settings, $name, $fallback ) {
	$value = $fallback;
	$out   = array();

	foreach ( array(
		'default'    => '',
		'large'      => '_large',
		'medium'     => '_medium',
		'responsive' => '_responsive',
	) as $device => $suffix ) {
		$key = $name . $suffix;

		if ( isset( $settings->$key ) && is_numeric( $settings->$key ) ) {
			$value = (int) $settings->$key;
		}

		$out[ $device ] = $value;
	}

	return $out;
};

$fm_per_view = $fm_responsive( $settings, 'slides_per_view', 1 );
$fm_space    = $fm_responsive( $settings, 'space_between', 20 );

/**
 * A yes / no select as a boolean, with the default the form gives it.
 *
 * @param string $name     Setting name.
 * @param string $fallback 'yes' or 'no' when the setting is unset.
 * @return bool
 */
$fm_yes = function ( $name, $fallback ) use ( $module ) {
	return 'yes' === $module->get( $name, $fallback );
};

$fm_config = array(
	'id'       => $id,
	'layout'   => $module->get_layout(),
	'loadMore' => 'load_more' === $module->get( 'pagination_type', 'none' ),
	'carousel' => array(
		'speed'                => is_numeric( $module->get( 'speed', 400 ) ) ? (int) $module->get( 'speed', 400 ) : 400,
		'loop'                 => $fm_yes( 'loop', 'yes' ),
		'centeredSlides'       => $fm_yes( 'centered_slides', 'no' ),
		'grabCursor'           => $fm_yes( 'grab_cursor', 'yes' ),
		'freeMode'             => $fm_yes( 'free_mode', 'no' ),
		'keyboard'             => $fm_yes( 'keyboard', 'no' ),
		'mousewheel'           => $fm_yes( 'mousewheel', 'no' ),
		'autoplay'             => $fm_yes( 'autoplay', 'yes' ),
		'autoplayDelay'        => is_numeric( $module->get( 'autoplay_delay', 3000 ) ) ? (int) $module->get( 'autoplay_delay', 3000 ) : 3000,
		'disableOnInteraction' => $fm_yes( 'disable_on_interaction', 'no' ),
		'pauseOnHover'         => $fm_yes( 'pause_on_mouse_enter', 'yes' ),
		'arrows'               => $module->has_arrows(),
		'pagination'           => $module->get_pagination_type(),
		'dynamicBullets'       => $fm_yes( 'dynamic_bullets', 'no' ),
		'scrollbar'            => $fm_yes( 'scrollbar', 'no' ),
		// Mobile-first: the phone values are the base, each wider band overrides.
		'base'                 => array(
			'slidesPerView' => max( 1, $fm_per_view['responsive'] ),
			'spaceBetween'  => $fm_space['responsive'],
		),
		'breakpoints'          => array(
			$fm_mobile_bp + 1 => array(
				'slidesPerView' => max( 1, $fm_per_view['medium'] ),
				'spaceBetween'  => $fm_space['medium'],
			),
			$fm_medium_bp + 1 => array(
				'slidesPerView' => max( 1, $fm_per_view['large'] ),
				'spaceBetween'  => $fm_space['large'],
			),
			$fm_large_bp + 1  => array(
				'slidesPerView' => max( 1, $fm_per_view['default'] ),
				'spaceBetween'  => $fm_space['default'],
			),
		),
	),
);
?>
(function () {
	function fmBoot() {
		new ThemePosts(<?php echo wp_json_encode( $fm_config ); ?>);
	}

	if ('loading' === document.readyState) {
		document.addEventListener('DOMContentLoaded', fmBoot);
	} else {
		fmBoot();
	}
})();
