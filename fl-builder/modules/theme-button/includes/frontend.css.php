<?php

defined('ABSPATH') || exit;

$button_selector = ".fl-builder-content .fl-node-$id .theme-button:is(a, button)";

// Custom Width
FLBuilderCSS::responsive_rule(array(
	'settings'     => $settings,
	'setting_name' => 'custom_width',
	'enabled'      => 'custom' === $settings->width,
	'selector'     => $button_selector,
	'prop'         => 'width',
));

// Alignment — the wrapper is a flex row, so map the align value to
// justify-content (flex-start / center / flex-end) to position the button.
FLBuilderCSS::responsive_rule(array(
	'settings'        => $settings,
	'setting_name'    => 'align',
	// include_wrapper is false, so BB merges .fl-node-{id} onto the
	// .theme-button-wrap element itself — compound selector (no space), not a
	// descendant, or the rule never matches and alignment is ignored.
	'selector'        => ".fl-node-$id.theme-button-wrap",
	'prop'            => 'justify-content',
	'substitute_vals' => array(
		'left'   => 'flex-start',
		'center' => 'center',
		'right'  => 'flex-end',
	),
));

// Copy-text disabled state.
FLBuilderCSS::rule(array(
	'selector' => ".fl-builder-content .fl-node-$id .theme-button:disabled, .fl-builder-content .fl-node-$id .theme-button.disabled",
	'enabled'  => isset($settings->click_action) && 'copy_text' === $settings->click_action,
	'props'    => array(
		'opacity' => '0.5',
	),
));

// DuoTone icon colours.
if ($settings->duo_color1 && false !== strpos($settings->icon, 'fad fa')) {
	FLBuilderCSS::rule(array(
		'selector' => ".fl-node-$id .theme-button-icon:before",
		'props'    => array(
			'color' => FLBuilderColor::hex_or_rgb($settings->duo_color1),
		),
	));
}
if ($settings->duo_color2 && false !== strpos($settings->icon, 'fad fa')) {
	FLBuilderCSS::rule(array(
		'selector' => ".fl-node-$id .theme-button-icon:after",
		'props'    => array(
			'color'   => FLBuilderColor::hex_or_rgb($settings->duo_color2),
			'opacity' => '1',
		),
	));
}

// Lightbox content styling.
if (isset($settings->click_action) && 'lightbox' === $settings->click_action) :
	$button_node_id = "fl-node-$id";
	if (isset($settings->id) && ! empty($settings->id)) {
		$button_node_id = $settings->id;
	}

	if ('html' === $settings->lightbox_content_type) :
?>
		.<?php echo $button_node_id; ?>.theme-button-lightbox-content,
		.fl-node-<?php echo $id; ?>.theme-button-lightbox-content {
		background: #fff;
		margin: 20px auto;
		max-width: 600px;
		padding: 20px;
		position: relative;
		width: auto;
		}
		.<?php echo $button_node_id; ?>.theme-button-lightbox-content .mfp-close,
		.fl-node-<?php echo $id; ?>.theme-button-lightbox-content .mfp-close {
		top: -10px !important;
		right: -10px;
		}
	<?php
	endif;

	if ('video' === $settings->lightbox_content_type) :
	?>
		.theme-button-lightbox-wrap .mfp-content {
		background: #fff;
		}
		.theme-button-lightbox-wrap .mfp-iframe-scaler iframe {
		left: 2%;
		height: 94%;
		top: 3%;
		width: 96%;
		}
		.mfp-wrap.theme-button-lightbox-wrap .mfp-close,
		.mfp-wrap.theme-button-lightbox-wrap .mfp-close:hover {
		color: #333 !important;
		right: -4px;
		top: -10px !important;
		}
<?php
	endif;
endif;
