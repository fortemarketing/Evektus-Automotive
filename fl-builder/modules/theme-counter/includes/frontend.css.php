<?php
/**
 *  Theme Counter front-end CSS php file
 *
 *  Outputs the per-instance styling driven by the module settings. Structural
 *  layout that does not depend on settings lives in css/frontend.css.
 *
 *  @package Evektus
 */

defined( 'ABSPATH' ) || exit;

// Layout.
$fm_layout      = $module->get_layout();
$fm_position    = $module->get_media_position();
$fm_title_media = $module->is_title_media();
$fm_align       = ( isset( $settings->align ) && in_array( $settings->align, array( 'left', 'center', 'right' ), true ) ) ? $settings->align : 'center';

// Media styles.
$fm_icon_style  = isset( $settings->icon_style ) ? $settings->icon_style : 'simple';
$fm_image_style = isset( $settings->image_style ) ? $settings->image_style : 'simple';
$fm_icon_pad    = ( isset( $settings->icon_bg_size ) && '' !== $settings->icon_bg_size ) ? (int) $settings->icon_bg_size : 30;
$fm_img_pad     = ( isset( $settings->img_bg_size ) && '' !== $settings->img_bg_size ) ? (int) $settings->img_bg_size : 0;

// Colors.
$fm_color = static function ( $key ) use ( $settings ) {
	return ( isset( $settings->$key ) && '' !== $settings->$key ) ? FLBuilderColor::hex_or_rgb( $settings->$key ) : '';
};

$fm_circle_color    = $fm_color( 'circle_color' );
$fm_circle_bg       = $fm_color( 'circle_bg_color' );
$fm_bar_color       = $fm_color( 'bar_color' );
$fm_bar_bg          = $fm_color( 'bar_bg_color' );
$fm_num_color       = $fm_color( 'num_color' );
$fm_ba_color        = $fm_color( 'ba_color' );
$fm_sep_color       = $fm_color( 'separator_color' );
$fm_icon_color      = $fm_color( 'icon_color' );
$fm_icon_hover      = $fm_color( 'icon_hover_color' );
$fm_icon_bg         = $fm_color( 'icon_bg_color' );
$fm_icon_bg_hover   = $fm_color( 'icon_bg_hover_color' );
$fm_icon_bd_hover   = $fm_color( 'icon_border_hover_color' );
$fm_img_bg          = $fm_color( 'img_bg_color' );
$fm_img_bg_hover    = $fm_color( 'img_bg_hover_color' );
$fm_img_bd_hover    = $fm_color( 'img_border_hover_color' );

// Circle.
$fm_circle = $module->get_circle_metrics();

// Separator.
$fm_sep_style  = isset( $settings->separator_style ) ? $settings->separator_style : 'solid';
$fm_sep_height = ( isset( $settings->separator_height ) && '' !== $settings->separator_height ) ? (int) $settings->separator_height : 1;
$fm_sep_width  = ( isset( $settings->separator_width ) && '' !== $settings->separator_width ) ? (int) $settings->separator_width : 100;
$fm_separator  = in_array( $fm_layout, array( 'default', 'circle' ), true ) && isset( $settings->show_separator ) && 'yes' === $settings->show_separator;

$fm_node = '.fl-node-' . $id;

// The text above and below the number, and the labels under the semicircle.
$fm_ba_selector = implode(
	', ',
	array(
		"$fm_node .theme-counter-before-text",
		"$fm_node .theme-counter-after-text",
		"$fm_node .theme-counter-counter-before-text",
		"$fm_node .theme-counter-counter-after-text",
	)
);
?>

/* Alignment. Media beside the text or the number aligns the counter to that
	 side, set in css/frontend.css; the bars always centre. */
<?php if ( 'default' === $fm_layout && ! in_array( $fm_position, array( 'left', 'right', 'left-title', 'right-title' ), true ) ) : ?>
<?php echo esc_attr( $fm_node ); ?> .theme-counter {
	text-align: <?php echo esc_attr( $fm_align ); ?>;
}
<?php endif; ?>

/* Circle and semicircle. */
<?php if ( in_array( $fm_layout, array( 'circle', 'semi-circle' ), true ) ) : ?>
<?php echo esc_attr( $fm_node ); ?> .theme-counter-circle,
<?php echo esc_attr( $fm_node ); ?> .theme-counter-semi-circle {
	max-width: <?php echo esc_attr( $fm_circle['width'] ); ?>px;
	margin-left: <?php echo 'left' === $fm_align ? '0' : 'auto'; ?>;
	margin-right: <?php echo 'right' === $fm_align ? '0' : 'auto'; ?>;
}
<?php echo esc_attr( $fm_node ); ?> .theme-counter-track,
<?php echo esc_attr( $fm_node ); ?> .theme-counter-fill {
	stroke-width: <?php echo esc_attr( $fm_circle['stroke'] ); ?>px;
}
<?php echo esc_attr( $fm_node ); ?> .theme-counter-track {
	stroke: <?php echo esc_attr( '' !== $fm_circle_bg ? $fm_circle_bg : 'transparent' ); ?>;
}
	<?php if ( '' !== $fm_circle_color ) : ?>
<?php echo esc_attr( $fm_node ); ?> .theme-counter-fill {
	stroke: <?php echo esc_attr( $fm_circle_color ); ?>;
}
	<?php endif; ?>
<?php endif; ?>

/* Bars. */
<?php if ( 'bars' === $fm_layout ) : ?>
<?php echo esc_attr( $fm_node ); ?> .theme-counter-bar-track {
	background-color: <?php echo esc_attr( '' !== $fm_bar_bg ? $fm_bar_bg : 'transparent' ); ?>;
}
	<?php if ( '' !== $fm_bar_color ) : ?>
<?php echo esc_attr( $fm_node ); ?> .theme-counter-bar {
	background-color: <?php echo esc_attr( $fm_bar_color ); ?>;
}
	<?php endif; ?>
<?php endif; ?>

/* Number. */
<?php if ( '' !== $fm_num_color ) : ?>
<?php echo esc_attr( $fm_node ); ?> .theme-counter .theme-counter-number {
	color: <?php echo esc_attr( $fm_num_color ); ?>;
}
<?php endif; ?>
<?php
// With the media inline beside the number, the margins move to the row so
// the media moves with it.
$fm_number_selector = $fm_title_media ? "$fm_node .theme-counter-title-row" : "$fm_node .theme-counter .theme-counter-number";
?>
<?php if ( isset( $settings->number_top_margin ) && '' !== $settings->number_top_margin ) : ?>
<?php echo esc_attr( $fm_number_selector ); ?> {
	margin-top: <?php echo esc_attr( (float) $settings->number_top_margin ); ?>px;
}
<?php endif; ?>
<?php if ( isset( $settings->number_bottom_margin ) && '' !== $settings->number_bottom_margin ) : ?>
<?php echo esc_attr( $fm_number_selector ); ?> {
	margin-bottom: <?php echo esc_attr( (float) $settings->number_bottom_margin ); ?>px;
}
<?php endif; ?>

/* Before and after text. */
<?php if ( '' !== $fm_ba_color ) : ?>
<?php echo esc_attr( $fm_ba_selector ); ?> {
	color: <?php echo esc_attr( $fm_ba_color ); ?>;
}
<?php endif; ?>

/* Image / icon margins, for media above, below or beside the whole text. */
<?php if ( '' !== $fm_position && ! $fm_title_media ) : ?>
	<?php if ( isset( $settings->img_icon_margin_top ) && '' !== $settings->img_icon_margin_top ) : ?>
<?php echo esc_attr( $fm_node ); ?> .theme-counter .theme-counter-media {
	margin-top: <?php echo esc_attr( (float) $settings->img_icon_margin_top ); ?>px;
}
	<?php endif; ?>
	<?php if ( isset( $settings->img_icon_margin_bottom ) && '' !== $settings->img_icon_margin_bottom ) : ?>
<?php echo esc_attr( $fm_node ); ?> .theme-counter .theme-counter-media {
	margin-bottom: <?php echo esc_attr( (float) $settings->img_icon_margin_bottom ); ?>px;
}
	<?php endif; ?>
<?php endif; ?>

/* Icon. */
<?php if ( '' !== $fm_position && 'icon' === $settings->image_type ) : ?>
	<?php if ( '' !== $fm_icon_color || ( '' !== $fm_icon_bg && 'simple' !== $fm_icon_style ) || 'custom' === $fm_icon_style ) : ?>
<?php echo esc_attr( $fm_node ); ?> .theme-counter-icon {
		<?php if ( '' !== $fm_icon_color ) : ?>
	color: <?php echo esc_attr( $fm_icon_color ); ?>;
		<?php endif; ?>
		<?php if ( '' !== $fm_icon_bg && 'simple' !== $fm_icon_style ) : ?>
	background-color: <?php echo esc_attr( $fm_icon_bg ); ?>;
		<?php endif; ?>
		<?php if ( 'custom' === $fm_icon_style ) : ?>
	padding: <?php echo esc_attr( $fm_icon_pad ); ?>px;
		<?php endif; ?>
}
	<?php endif; ?>
	<?php if ( '' !== $fm_icon_hover ) : ?>
<?php echo esc_attr( $fm_node ); ?> .theme-counter-icon:hover {
	color: <?php echo esc_attr( $fm_icon_hover ); ?>;
}
	<?php endif; ?>
	<?php if ( '' !== $fm_icon_bg_hover && 'simple' !== $fm_icon_style ) : ?>
<?php echo esc_attr( $fm_node ); ?> .theme-counter-icon:hover {
	background-color: <?php echo esc_attr( $fm_icon_bg_hover ); ?>;
}
	<?php endif; ?>
	<?php if ( '' !== $fm_icon_bd_hover && 'custom' === $fm_icon_style ) : ?>
<?php echo esc_attr( $fm_node ); ?> .theme-counter-icon:hover {
	border-color: <?php echo esc_attr( $fm_icon_bd_hover ); ?>;
}
	<?php endif; ?>
<?php endif; ?>

/* Photo. */
<?php if ( '' !== $fm_position && 'photo' === $settings->image_type && 'custom' === $fm_image_style ) : ?>
<?php echo esc_attr( $fm_node ); ?> .theme-counter-photo-wrap {
	<?php if ( $fm_img_pad > 0 ) : ?>
	padding: <?php echo esc_attr( $fm_img_pad ); ?>px;
	<?php endif; ?>
	<?php if ( '' !== $fm_img_bg ) : ?>
	background-color: <?php echo esc_attr( $fm_img_bg ); ?>;
	<?php endif; ?>
}
	<?php if ( '' !== $fm_img_bg_hover ) : ?>
<?php echo esc_attr( $fm_node ); ?> .theme-counter-photo-wrap:hover {
	background-color: <?php echo esc_attr( $fm_img_bg_hover ); ?>;
}
	<?php endif; ?>
	<?php if ( '' !== $fm_img_bd_hover ) : ?>
<?php echo esc_attr( $fm_node ); ?> .theme-counter-photo-wrap:hover {
	border-color: <?php echo esc_attr( $fm_img_bd_hover ); ?>;
}
	<?php endif; ?>
<?php endif; ?>

/* Separator. */
<?php if ( $fm_separator ) : ?>
<?php echo esc_attr( $fm_node ); ?> .theme-counter-separator {
	width: <?php echo esc_attr( $fm_sep_width ); ?>%;
	border-top-style: <?php echo esc_attr( $fm_sep_style ); ?>;
	border-top-width: <?php echo esc_attr( $fm_sep_height ); ?>px;
	<?php if ( '' !== $fm_sep_color ) : ?>
	border-top-color: <?php echo esc_attr( $fm_sep_color ); ?>;
	<?php endif; ?>
}
	<?php if ( isset( $settings->separator_top_margin ) && '' !== $settings->separator_top_margin ) : ?>
<?php echo esc_attr( $fm_node ); ?> .theme-counter-separator-wrap {
	margin-top: <?php echo esc_attr( (float) $settings->separator_top_margin ); ?>px;
}
	<?php endif; ?>
	<?php if ( isset( $settings->separator_bottom_margin ) && '' !== $settings->separator_bottom_margin ) : ?>
<?php echo esc_attr( $fm_node ); ?> .theme-counter-separator-wrap {
	margin-bottom: <?php echo esc_attr( (float) $settings->separator_bottom_margin ); ?>px;
}
	<?php endif; ?>
<?php endif; ?>

<?php
// Responsive and compound field rules.
if ( class_exists( 'FLBuilderCSS' ) ) {

	// Separator alignment — overrides the overall alignment for the rule only.
	FLBuilderCSS::responsive_rule(
		array(
			'settings'     => $settings,
			'setting_name' => 'separator_alignment',
			'enabled'      => $fm_separator,
			'selector'     => "$fm_node .theme-counter-separator-wrap",
			'prop'         => 'text-align',
			'ignore'       => array( '' ),
		)
	);

	// Icon size.
	FLBuilderCSS::responsive_rule(
		array(
			'settings'     => $settings,
			'setting_name' => 'icon_size',
			'selector'     => "$fm_node .theme-counter-icon",
			'prop'         => 'font-size',
			'unit'         => 'px',
			'ignore'       => array( '' ),
		)
	);

	// Photo size.
	FLBuilderCSS::responsive_rule(
		array(
			'settings'     => $settings,
			'setting_name' => 'img_size',
			'selector'     => "$fm_node .theme-counter-photo",
			'prop'         => 'width',
			'unit'         => 'px',
			'ignore'       => array( '' ),
		)
	);

	// The icon and image borders are only offered by the "Design your own"
	// styles, so they are skipped for the preset styles even if an earlier
	// custom border is still stored on the module.
	if ( 'custom' === $fm_icon_style ) {
		FLBuilderCSS::border_field_rule(
			array(
				'settings'     => $settings,
				'setting_name' => 'icon_border',
				'selector'     => "$fm_node .theme-counter-icon",
			)
		);
	}

	if ( 'custom' === $fm_image_style ) {
		FLBuilderCSS::border_field_rule(
			array(
				'settings'     => $settings,
				'setting_name' => 'img_border',
				'selector'     => "$fm_node .theme-counter-photo-wrap",
			)
		);
	}
}
