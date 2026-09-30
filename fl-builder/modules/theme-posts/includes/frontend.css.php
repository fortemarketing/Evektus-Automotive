<?php
/**
 *  Theme Posts front-end CSS php file
 *
 *  Outputs the per-instance layout values driven by the module settings --
 *  columns, gaps and the carousel controls -- through Beaver Builder's own
 *  FLBuilderCSS helpers so responsive values follow BB's breakpoints and
 *  inheritance. There is no card styling here: that belongs in the theme, over
 *  the starting styles in css/frontend.css.
 *
 *  @package Evektus
 */

defined( 'ABSPATH' ) || exit;

$fm_node     = ".fl-node-$id";
$fm_layout   = isset( $settings->layout ) ? $settings->layout : 'grid';
$fm_carousel = 'carousel' === $fm_layout;

// Layout ------------------------------------------------------------------

/*
 * The column count feeds repeat() and a calc(), so it goes out as a bare
 * integer rather than through responsive_rule(), which would append any saved
 * _unit. A carousel gets its column count from Swiper instead.
 */
if ( ! $fm_carousel ) {
	foreach ( array(
		'default'    => '',
		'large'      => '_large',
		'medium'     => '_medium',
		'responsive' => '_responsive',
	) as $fm_media => $fm_suffix ) {
		$fm_key = 'post_columns' . $fm_suffix;

		if ( isset( $settings->$fm_key ) && is_numeric( $settings->$fm_key ) && (int) $settings->$fm_key > 0 ) {
			FLBuilderCSS::rule(
				array(
					'media'    => $fm_media,
					'selector' => $fm_node . ' .theme-posts__items',
					'props'    => array(
						'--theme-posts-columns' => (string) (int) $settings->$fm_key,
					),
				)
			);
		}
	}

	foreach ( array(
		'column_gap' => '--theme-posts-col-gap',
		'row_gap'    => '--theme-posts-row-gap',
	) as $fm_setting => $fm_prop ) {
		FLBuilderCSS::responsive_rule(
			array(
				'settings'     => $settings,
				'setting_name' => $fm_setting,
				'selector'     => $fm_node . ' .theme-posts__items',
				'prop'         => $fm_prop,
				'unit'         => 'px',
			)
		);
	}
}

// Carousel navigation -----------------------------------------------------

if ( $fm_carousel ) {

	$arrow_size   = isset( $settings->arrow_size ) && '' !== $settings->arrow_size ? (int) $settings->arrow_size : 40;
	$arrow_gutter = isset( $settings->arrow_gutter ) && '' !== $settings->arrow_gutter ? (int) $settings->arrow_gutter : 50;
	$arrow_gap    = isset( $settings->arrow_gap ) && '' !== $settings->arrow_gap ? (int) $settings->arrow_gap : 10;

	FLBuilderCSS::rule(
		array(
			'selector' => $fm_node . ' .theme-posts',
			'props'    => array(
				'--theme-posts-arrow-size'   => $arrow_size . 'px',
				'--theme-posts-arrow-gutter' => $arrow_gutter . 'px',
				'--theme-posts-arrow-gap'    => $arrow_gap . 'px',
				'--theme-posts-arrow-color'  => isset( $settings->arrow_color ) && '' !== $settings->arrow_color
					? FLBuilderColor::hex_or_rgb( $settings->arrow_color )
					: '',
			),
		)
	);

	FLBuilderCSS::responsive_rule(
		array(
			'settings'     => $settings,
			'setting_name' => 'arrow_spacing',
			'selector'     => $fm_node . ' .theme-posts__arrows',
			'prop'         => 'padding-top',
			'unit'         => 'px',
		)
	);

	FLBuilderCSS::responsive_rule(
		array(
			'settings'     => $settings,
			'setting_name' => 'carousel_pagination_spacing',
			'selector'     => $fm_node . ' .theme-posts__carousel > .swiper-pagination',
			'prop'         => 'margin-top',
			'unit'         => 'px',
		)
	);

	if ( isset( $settings->carousel_pagination_color ) && '' !== $settings->carousel_pagination_color ) {
		$fm_pag_color = FLBuilderColor::hex_or_rgb( $settings->carousel_pagination_color );

		// Every bullet takes the colour, as in FM Carousel; Swiper's own opacity
		// is what dims the inactive ones.
		FLBuilderCSS::rule(
			array(
				'selector' => $fm_node . ' .swiper-pagination-bullet, ' . $fm_node . ' .swiper-pagination-progressbar-fill',
				'props'    => array(
					'background-color' => $fm_pag_color,
				),
			)
		);

		FLBuilderCSS::rule(
			array(
				'selector' => $fm_node . ' .swiper-pagination-fraction',
				'props'    => array(
					'color' => $fm_pag_color,
				),
			)
		);
	}
}
