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

$fm_columns = $fm_responsive( $settings, 'post_columns', 3 );
$fm_scroll  = $fm_responsive( $settings, 'slides_to_scroll', 1 );
$fm_gap     = $fm_responsive( $settings, 'column_gap', 30 );

$fm_config = array(
	'id'         => $id,
	'layout'     => $module->get_layout(),
	'loadMore'   => 'load_more' === $module->get( 'pagination_type', 'none' ),
	'carousel'   => array(
		'speed'          => is_numeric( $module->get( 'transition_speed', 500 ) ) ? (int) $module->get( 'transition_speed', 500 ) : 500,
		'loop'           => 'yes' === $module->get( 'infinite', 'yes' ),
		'autoplay'       => 'yes' === $module->get( 'autoplay', 'no' ),
		'autoplayDelay'  => is_numeric( $module->get( 'autoplay_speed', 5000 ) ) ? (int) $module->get( 'autoplay_speed', 5000 ) : 5000,
		'pauseOnHover'   => 'yes' === $module->get( 'pause_on_hover', 'yes' ),
		'arrows'         => $module->has_arrows(),
		'pagination'     => $module->get_pagination_type(),
		'dynamicBullets' => 'yes' === $module->get( 'dynamic_bullets', 'no' ),
		// Mobile-first: the phone values are the base, each wider band overrides.
		'base'           => array(
			'slidesPerView'  => max( 1, $fm_columns['responsive'] ),
			'slidesPerGroup' => max( 1, $fm_scroll['responsive'] ),
			'spaceBetween'   => $fm_gap['responsive'],
		),
		'breakpoints'    => array(
			$fm_mobile_bp + 1 => array(
				'slidesPerView'  => max( 1, $fm_columns['medium'] ),
				'slidesPerGroup' => max( 1, $fm_scroll['medium'] ),
				'spaceBetween'   => $fm_gap['medium'],
			),
			$fm_medium_bp + 1 => array(
				'slidesPerView'  => max( 1, $fm_columns['large'] ),
				'slidesPerGroup' => max( 1, $fm_scroll['large'] ),
				'spaceBetween'   => $fm_gap['large'],
			),
			$fm_large_bp + 1  => array(
				'slidesPerView'  => max( 1, $fm_columns['default'] ),
				'slidesPerGroup' => max( 1, $fm_scroll['default'] ),
				'spaceBetween'   => $fm_gap['default'],
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
