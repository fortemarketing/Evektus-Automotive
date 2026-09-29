<?php
/**
 *  Theme List Icon front-end CSS php file
 *
 *  Outputs the per-instance styling driven by the module settings. Structural
 *  layout that does not depend on settings lives in css/frontend.css.
 *
 *  @package Evektus
 */

defined( 'ABSPATH' ) || exit;

// Media styles.
$fm_type        = isset( $settings->image_type ) ? $settings->image_type : 'none';
$fm_icon_style  = isset( $settings->icon_style ) ? $settings->icon_style : 'simple';
$fm_image_style = isset( $settings->image_style ) ? $settings->image_style : 'simple';

// Colors.
$fm_color = static function ( $key ) use ( $settings ) {
	return ( isset( $settings->$key ) && '' !== $settings->$key ) ? FLBuilderColor::hex_or_rgb( $settings->$key ) : '';
};

$fm_text_color    = $fm_color( 'typography_color' );
$fm_icon_color    = $fm_color( 'icon_color' );
$fm_icon_hover    = $fm_color( 'icon_hover_color' );
$fm_icon_bg       = $fm_color( 'icon_bg_color' );
$fm_icon_bg_hover = $fm_color( 'icon_bg_hover_color' );
$fm_icon_bd_hover = $fm_color( 'icon_border_hover_color' );
$fm_img_bg        = $fm_color( 'img_bg_color' );
$fm_img_bg_hover  = $fm_color( 'img_bg_hover_color' );
$fm_img_bd_hover  = $fm_color( 'img_border_hover_color' );

$fm_node = '.fl-node-' . $id;
?>

/* Text. */
<?php if ( '' !== $fm_text_color ) : ?>
<?php echo esc_attr( $fm_node ); ?> .theme-list-icon-text .theme-list-icon-heading {
	color: <?php echo esc_attr( $fm_text_color ); ?>;
}
<?php endif; ?>

/* Icon. */
<?php if ( 'icon' === $fm_type ) : ?>
	<?php if ( '' !== $fm_icon_color || ( '' !== $fm_icon_bg && 'simple' !== $fm_icon_style ) ) : ?>
<?php echo esc_attr( $fm_node ); ?> .theme-list-icon-icon {
		<?php if ( '' !== $fm_icon_color ) : ?>
	color: <?php echo esc_attr( $fm_icon_color ); ?>;
		<?php endif; ?>
		<?php if ( '' !== $fm_icon_bg && 'simple' !== $fm_icon_style ) : ?>
	background-color: <?php echo esc_attr( $fm_icon_bg ); ?>;
		<?php endif; ?>
}
	<?php endif; ?>
	<?php if ( 'custom' === $fm_icon_style && isset( $settings->icon_bg_size ) && '' !== $settings->icon_bg_size ) : ?>
<?php echo esc_attr( $fm_node ); ?> .theme-list-icon-icon {
	padding: <?php echo esc_attr( (float) $settings->icon_bg_size ); ?>px;
}
	<?php endif; ?>
	<?php if ( '' !== $fm_icon_hover ) : ?>
<?php echo esc_attr( $fm_node ); ?> .theme-list-icon-icon:hover {
	color: <?php echo esc_attr( $fm_icon_hover ); ?>;
}
	<?php endif; ?>
	<?php if ( '' !== $fm_icon_bg_hover && 'simple' !== $fm_icon_style ) : ?>
<?php echo esc_attr( $fm_node ); ?> .theme-list-icon-icon:hover {
	background-color: <?php echo esc_attr( $fm_icon_bg_hover ); ?>;
}
	<?php endif; ?>
	<?php if ( '' !== $fm_icon_bd_hover && 'custom' === $fm_icon_style ) : ?>
<?php echo esc_attr( $fm_node ); ?> .theme-list-icon-icon:hover {
	border-color: <?php echo esc_attr( $fm_icon_bd_hover ); ?>;
}
	<?php endif; ?>
<?php endif; ?>

/* Photo. */
<?php if ( 'photo' === $fm_type && 'custom' === $fm_image_style ) : ?>
<?php echo esc_attr( $fm_node ); ?> .theme-list-icon-photo-wrap {
	<?php if ( isset( $settings->img_bg_size ) && '' !== $settings->img_bg_size ) : ?>
	padding: <?php echo esc_attr( (float) $settings->img_bg_size ); ?>px;
	<?php endif; ?>
	<?php if ( '' !== $fm_img_bg ) : ?>
	background-color: <?php echo esc_attr( $fm_img_bg ); ?>;
	<?php endif; ?>
}
	<?php if ( '' !== $fm_img_bg_hover ) : ?>
<?php echo esc_attr( $fm_node ); ?> .theme-list-icon-photo-wrap:hover {
	background-color: <?php echo esc_attr( $fm_img_bg_hover ); ?>;
}
	<?php endif; ?>
	<?php if ( '' !== $fm_img_bd_hover ) : ?>
<?php echo esc_attr( $fm_node ); ?> .theme-list-icon-photo-wrap:hover {
	border-color: <?php echo esc_attr( $fm_img_bd_hover ); ?>;
}
	<?php endif; ?>
<?php endif; ?>

<?php
// Responsive and compound field rules.
if ( class_exists( 'FLBuilderCSS' ) ) {

	// Space between items: the gap along the list's own direction. A horizontal
	// list keeps its fixed row gap for when it wraps.
	FLBuilderCSS::responsive_rule(
		array(
			'settings'     => $settings,
			'setting_name' => 'spacing',
			'selector'     => "$fm_node .theme-list-icon",
			'prop'         => 'horizontal' === $module->get_structure() ? 'column-gap' : 'row-gap',
			'unit'         => 'px',
			'ignore'       => array( '' ),
		)
	);

	// Space between the image / icon and the text.
	FLBuilderCSS::responsive_rule(
		array(
			'settings'     => $settings,
			'setting_name' => 'icon_text_spacing',
			'enabled'      => 'none' !== $fm_type,
			'selector'     => "$fm_node .theme-list-icon-item",
			'prop'         => 'gap',
			'unit'         => 'px',
			'ignore'       => array( '' ),
		)
	);

	// Typography. Scoped under the text wrap so it outranks the theme's own
	// heading rules for whichever tag the items use.
	FLBuilderCSS::typography_field_rule(
		array(
			'settings'     => $settings,
			'setting_name' => 'font_typo',
			'selector'     => "$fm_node .theme-list-icon-text .theme-list-icon-heading",
		)
	);

	// Icon size.
	FLBuilderCSS::responsive_rule(
		array(
			'settings'     => $settings,
			'setting_name' => 'icon_size',
			'enabled'      => 'icon' === $fm_type,
			'selector'     => "$fm_node .theme-list-icon-icon",
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
			'enabled'      => 'photo' === $fm_type,
			'selector'     => "$fm_node .theme-list-icon-photo",
			'prop'         => 'width',
			'unit'         => 'px',
			'ignore'       => array( '' ),
		)
	);

	// The icon and image borders are only offered by the "Design your own"
	// styles, so they are skipped for the preset styles even if an earlier
	// custom border is still stored on the module.
	if ( 'icon' === $fm_type && 'custom' === $fm_icon_style ) {
		FLBuilderCSS::border_field_rule(
			array(
				'settings'     => $settings,
				'setting_name' => 'icon_border',
				'selector'     => "$fm_node .theme-list-icon-icon",
			)
		);
	}

	if ( 'photo' === $fm_type && 'custom' === $fm_image_style ) {
		FLBuilderCSS::border_field_rule(
			array(
				'settings'     => $settings,
				'setting_name' => 'img_border',
				'selector'     => "$fm_node .theme-list-icon-photo-wrap",
			)
		);
	}
}
