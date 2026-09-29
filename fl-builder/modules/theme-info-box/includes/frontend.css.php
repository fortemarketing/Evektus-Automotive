<?php
/**
 *  Theme Info Box front-end CSS php file
 *
 *  Outputs the per-instance styling driven by the module settings. Structural
 *  layout that does not depend on settings lives in css/frontend.css.
 *
 *  @package Evektus
 */

defined( 'ABSPATH' ) || exit;

// Layout.
$fm_has_media   = isset( $settings->image_type ) && 'none' !== $settings->image_type;
$fm_position    = $fm_has_media && isset( $settings->img_icon_position ) ? $settings->img_icon_position : '';
$fm_side_media  = in_array( $fm_position, array( 'left', 'right' ), true );
$fm_title_media = in_array( $fm_position, array( 'left-title', 'right-title' ), true );
$fm_spacing     = ( isset( $settings->icon_spacing ) && '' !== $settings->icon_spacing ) ? (int) $settings->icon_spacing : 20;

// Media styles.
$fm_icon_style  = isset( $settings->icon_style ) ? $settings->icon_style : 'simple';
$fm_image_style = isset( $settings->image_style ) ? $settings->image_style : 'simple';
$fm_icon_pad    = ( isset( $settings->icon_bg_size ) && '' !== $settings->icon_bg_size ) ? (int) $settings->icon_bg_size : 30;
$fm_img_pad     = ( isset( $settings->img_bg_size ) && '' !== $settings->img_bg_size ) ? (int) $settings->img_bg_size : 0;

// Colors.
$fm_bg              = ( isset( $settings->bg_color ) && '' !== $settings->bg_color ) ? FLBuilderColor::hex_or_rgb( $settings->bg_color ) : '';
$fm_bg_hover        = ( isset( $settings->bg_hover_color ) && '' !== $settings->bg_hover_color ) ? FLBuilderColor::hex_or_rgb( $settings->bg_hover_color ) : '';
$fm_border_hover    = ( isset( $settings->border_hover_color ) && '' !== $settings->border_hover_color ) ? FLBuilderColor::hex_or_rgb( $settings->border_hover_color ) : '';
$fm_icon_color      = ( isset( $settings->icon_color ) && '' !== $settings->icon_color ) ? FLBuilderColor::hex_or_rgb( $settings->icon_color ) : '';
$fm_icon_hover      = ( isset( $settings->icon_hover_color ) && '' !== $settings->icon_hover_color ) ? FLBuilderColor::hex_or_rgb( $settings->icon_hover_color ) : '';
$fm_icon_bg         = ( isset( $settings->icon_bg_color ) && '' !== $settings->icon_bg_color ) ? FLBuilderColor::hex_or_rgb( $settings->icon_bg_color ) : '';
$fm_icon_bg_hover   = ( isset( $settings->icon_bg_hover_color ) && '' !== $settings->icon_bg_hover_color ) ? FLBuilderColor::hex_or_rgb( $settings->icon_bg_hover_color ) : '';
$fm_icon_bd_hover   = ( isset( $settings->icon_border_hover_color ) && '' !== $settings->icon_border_hover_color ) ? FLBuilderColor::hex_or_rgb( $settings->icon_border_hover_color ) : '';
$fm_prefix_color    = ( isset( $settings->prefix_color ) && '' !== $settings->prefix_color ) ? FLBuilderColor::hex_or_rgb( $settings->prefix_color ) : '';
$fm_title_color     = ( isset( $settings->title_color ) && '' !== $settings->title_color ) ? FLBuilderColor::hex_or_rgb( $settings->title_color ) : '';
$fm_desc_color      = ( isset( $settings->desc_color ) && '' !== $settings->desc_color ) ? FLBuilderColor::hex_or_rgb( $settings->desc_color ) : '';
$fm_link_color      = ( isset( $settings->link_color ) && '' !== $settings->link_color ) ? FLBuilderColor::hex_or_rgb( $settings->link_color ) : '';
$fm_link_hover      = ( isset( $settings->link_hover_color ) && '' !== $settings->link_hover_color ) ? FLBuilderColor::hex_or_rgb( $settings->link_hover_color ) : '';
$fm_sep_color       = ( isset( $settings->separator_color ) && '' !== $settings->separator_color ) ? FLBuilderColor::hex_or_rgb( $settings->separator_color ) : '';

// Separator.
$fm_sep_style  = isset( $settings->separator_style ) ? $settings->separator_style : 'solid';
$fm_sep_height = ( isset( $settings->separator_height ) && '' !== $settings->separator_height ) ? (int) $settings->separator_height : 1;
$fm_sep_width  = ( isset( $settings->separator_width ) && '' !== $settings->separator_width ) ? (int) $settings->separator_width : 100;

$fm_node = '.fl-node-' . $id;

// Both calls to action are anchors, and a row or column carrying a link colour
// emits `.fl-builder-content .fl-node-{col} a` -- two classes and an element,
// which outranks the two classes `$fm_node .theme-info-box-button` would have on
// its own. Rules that colour a CTA are written against this scoped prefix
// instead, putting them a class ahead of the column.
$fm_cta_node = '.fl-builder-content ' . $fm_node;
?>

/* Box. */
<?php if ( '' !== $fm_bg ) : ?>
<?php echo esc_attr( $fm_node ); ?> .theme-info-box {
	background-color: <?php echo esc_attr( $fm_bg ); ?>;
}
<?php endif; ?>
<?php if ( '' !== $fm_bg_hover ) : ?>
<?php echo esc_attr( $fm_node ); ?> .theme-info-box:hover {
	background-color: <?php echo esc_attr( $fm_bg_hover ); ?>;
}
<?php endif; ?>
<?php if ( '' !== $fm_border_hover ) : ?>
<?php echo esc_attr( $fm_node ); ?> .theme-info-box:hover {
	border-color: <?php echo esc_attr( $fm_border_hover ); ?>;
}
<?php endif; ?>
<?php if ( isset( $settings->min_height_switch ) && 'custom' === $settings->min_height_switch ) : ?>
<?php echo esc_attr( $fm_node ); ?> .theme-info-box {
	display: grid;
	align-content: <?php echo ( isset( $settings->vertical_align ) && 'top' === $settings->vertical_align ) ? 'start' : 'center'; ?>;
}
<?php endif; ?>

/* Image / icon placement. When the media sits beside the content or beside the
	 heading it is a child of a grid row, so the gap is that row's own `gap`. */
<?php if ( $fm_side_media || $fm_title_media ) : ?>
<?php echo esc_attr( $fm_node ); ?> <?php echo $fm_side_media ? '.theme-info-box-inner' : '.theme-info-box-title-row'; ?> {
	gap: <?php echo esc_attr( $fm_spacing ); ?>px;
	align-items: <?php echo ( isset( $settings->align_items ) && 'top' === $settings->align_items ) ? 'start' : 'center'; ?>;
}
<?php endif; ?>
<?php if ( 'above-title' === $fm_position ) : ?>
<?php echo esc_attr( $fm_node ); ?> .theme-info-box-content > .theme-info-box-media {
	margin-bottom: <?php echo esc_attr( $fm_spacing ); ?>px;
}
<?php elseif ( 'below-title' === $fm_position ) : ?>
<?php echo esc_attr( $fm_node ); ?> .theme-info-box-content > .theme-info-box-media {
	margin-top: <?php echo esc_attr( $fm_spacing ); ?>px;
}
<?php endif; ?>

/* Icon. */
<?php if ( $fm_has_media && 'icon' === $settings->image_type ) : ?>
	<?php if ( '' !== $fm_icon_color || '' !== $fm_icon_bg || 'simple' !== $fm_icon_style ) : ?>
<?php echo esc_attr( $fm_node ); ?> .theme-info-box-icon {
	<?php if ( '' !== $fm_icon_color ) : ?>
	color: <?php echo esc_attr( $fm_icon_color ); ?>;
	<?php endif; ?>
	<?php if ( '' !== $fm_icon_bg ) : ?>
	background-color: <?php echo esc_attr( $fm_icon_bg ); ?>;
	<?php endif; ?>
	<?php if ( 'simple' !== $fm_icon_style ) : ?>
	padding: <?php echo esc_attr( $fm_icon_pad ); ?>px;
	<?php endif; ?>
}
	<?php endif; ?>
	<?php if ( '' !== $fm_icon_hover ) : ?>
<?php echo esc_attr( $fm_node ); ?> .theme-info-box:hover .theme-info-box-icon {
	color: <?php echo esc_attr( $fm_icon_hover ); ?>;
}
	<?php endif; ?>
	<?php if ( '' !== $fm_icon_bg_hover ) : ?>
<?php echo esc_attr( $fm_node ); ?> .theme-info-box:hover .theme-info-box-icon {
	background-color: <?php echo esc_attr( $fm_icon_bg_hover ); ?>;
}
	<?php endif; ?>
	<?php if ( '' !== $fm_icon_bd_hover ) : ?>
<?php echo esc_attr( $fm_node ); ?> .theme-info-box:hover .theme-info-box-icon {
	border-color: <?php echo esc_attr( $fm_icon_bd_hover ); ?>;
}
	<?php endif; ?>
<?php endif; ?>

/* Photo. */
<?php if ( $fm_has_media && 'photo' === $settings->image_type ) : ?>
	<?php if ( 'custom' === $fm_image_style && $fm_img_pad > 0 ) : ?>
<?php echo esc_attr( $fm_node ); ?> .theme-info-box-photo-wrap {
	padding: <?php echo esc_attr( $fm_img_pad ); ?>px;
}
	<?php endif; ?>
<?php endif; ?>

/* Title prefix and title. */
<?php if ( '' !== $fm_prefix_color ) : ?>
<?php echo esc_attr( $fm_node ); ?> .theme-info-box-prefix {
	color: <?php echo esc_attr( $fm_prefix_color ); ?>;
}
<?php endif; ?>
<?php if ( '' !== $fm_title_color ) : ?>
<?php echo esc_attr( $fm_node ); ?> .theme-info-box-title {
	color: <?php echo esc_attr( $fm_title_color ); ?>;
}
<?php endif; ?>

/* Description. */
<?php if ( '' !== $fm_desc_color ) : ?>
<?php echo esc_attr( $fm_node ); ?> .theme-info-box-text {
	color: <?php echo esc_attr( $fm_desc_color ); ?>;
}
<?php endif; ?>

/* Separator. */
<?php if ( isset( $settings->show_separator ) && 'yes' === $settings->show_separator ) : ?>
<?php echo esc_attr( $fm_node ); ?> .theme-info-box-separator {
	width: <?php echo esc_attr( $fm_sep_width ); ?>%;
	border-top-style: <?php echo esc_attr( $fm_sep_style ); ?>;
	border-top-width: <?php echo esc_attr( $fm_sep_height ); ?>px;
	<?php if ( '' !== $fm_sep_color ) : ?>
	border-top-color: <?php echo esc_attr( $fm_sep_color ); ?>;
	<?php endif; ?>
}
<?php endif; ?>

/* Text link call to action. */
<?php if ( isset( $settings->cta_type ) && 'link' === $settings->cta_type ) : ?>
	<?php if ( '' !== $fm_link_color ) : ?>
<?php echo esc_attr( $fm_cta_node ); ?> .theme-info-box-cta-link {
	color: <?php echo esc_attr( $fm_link_color ); ?>;
}
	<?php endif; ?>
	<?php if ( '' !== $fm_link_hover ) : ?>
<?php echo esc_attr( $fm_cta_node ); ?> .theme-info-box-cta-link:hover,
<?php echo esc_attr( $fm_cta_node ); ?> .theme-info-box:hover .theme-info-box-cta-link {
	color: <?php echo esc_attr( $fm_link_hover ); ?>;
}
	<?php endif; ?>
<?php endif; ?>

<?php
// Responsive and compound field rules.
if ( class_exists( 'FLBuilderCSS' ) ) {

	// Spacing is hidden for now (see the commented-out Style > Spacing section
	// in theme-info-box.php), so none of these rules run and custom.css sets
	// every gap. That also keeps values saved on older boxes from applying.
	/*
	// Individual bottom margins replace container gaps so each text element's
	// spacing can be controlled independently. Title spacing sits on the
	// title wrap, the element assets/css/custom.css spaces the heading with.
	$fm_spacing_rules = array(
		'prefix_spacing'       => '.theme-info-box-prefix',
		'title_bottom_spacing' => '.theme-info-box-title-wrap',
		'description_spacing'  => '.theme-info-box-text',
	);

	// An empty field outputs nothing, leaving the spacing to custom.css. A
	// value is scoped one class deeper than custom.css's rules so it wins
	// whichever stylesheet loads last.
	foreach ( $fm_spacing_rules as $fm_setting => $fm_selector ) {
		FLBuilderCSS::responsive_rule(
			array(
				'settings'     => $settings,
				'setting_name' => $fm_setting,
				'selector'     => "$fm_node .theme-info-box $fm_selector",
				'prop'         => 'margin-bottom',
				'unit'         => 'px',
				'ignore'       => array( '' ),
			)
		);
	}
	*/

	// Overall alignment drives the text and inline media.
	FLBuilderCSS::responsive_rule(
		array(
			'settings'     => $settings,
			'setting_name' => 'align',
			'selector'     => "$fm_node .theme-info-box",
			'prop'         => 'text-align',
			'ignore'       => array( '' ),
		)
	);

	// Content padding.
	FLBuilderCSS::dimension_field_rule(
		array(
			'settings'     => $settings,
			'setting_name' => 'box_padding',
			'selector'     => "$fm_node .theme-info-box",
			'unit'         => 'px',
			'props'        => array(
				'padding-top'    => 'box_padding_top',
				'padding-right'  => 'box_padding_right',
				'padding-bottom' => 'box_padding_bottom',
				'padding-left'   => 'box_padding_left',
			),
		)
	);

	// Minimum height.
	FLBuilderCSS::responsive_rule(
		array(
			'settings'     => $settings,
			'setting_name' => 'min_height',
			'enabled'      => isset( $settings->min_height_switch ) && 'custom' === $settings->min_height_switch,
			'selector'     => "$fm_node .theme-info-box",
			'prop'         => 'min-height',
			'unit'         => 'px',
			'ignore'       => array( '' ),
		)
	);

	// Icon size.
	FLBuilderCSS::responsive_rule(
		array(
			'settings'     => $settings,
			'setting_name' => 'icon_size',
			'selector'     => "$fm_node .theme-info-box-icon",
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
			'selector'     => "$fm_node .theme-info-box-photo",
			'prop'         => 'width',
			'unit'         => 'px',
			'ignore'       => array( '' ),
		)
	);

	// Separator alignment — overrides the overall alignment for the rule only.
	FLBuilderCSS::responsive_rule(
		array(
			'settings'     => $settings,
			'setting_name' => 'separator_align',
			'enabled'      => isset( $settings->show_separator ) && 'yes' === $settings->show_separator,
			'selector'     => "$fm_node .theme-info-box-separator-wrap",
			'prop'         => 'text-align',
			'ignore'       => array( '' ),
		)
	);

	// Button width and padding.
	FLBuilderCSS::responsive_rule(
		array(
			'settings'     => $settings,
			'setting_name' => 'btn_custom_width',
			'enabled'      => isset( $settings->btn_width ) && 'custom' === $settings->btn_width,
			'selector'     => "$fm_node .theme-info-box-button",
			'prop'         => 'width',
			'ignore'       => array( '' ),
		)
	);

	// Borders.
	FLBuilderCSS::border_field_rule(
		array(
			'settings'     => $settings,
			'setting_name' => 'box_border',
			'selector'     => "$fm_node .theme-info-box",
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
				'selector'     => "$fm_node .theme-info-box-icon",
			)
		);
	}

	if ( 'custom' === $fm_image_style ) {
		FLBuilderCSS::border_field_rule(
			array(
				'settings'     => $settings,
				'setting_name' => 'img_border',
				'selector'     => "$fm_node .theme-info-box-photo-wrap",
			)
		);
	}
}
