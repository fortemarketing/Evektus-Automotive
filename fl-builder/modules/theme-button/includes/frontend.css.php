<?php

defined('ABSPATH') || exit;

$breakpoints     = array('', 'large', 'medium', 'responsive');
$button_selector = ".fl-builder-content .fl-node-$id .theme-button:is(a, button)";

// Hover is front-end only. Inside the builder a button is content to arrange,
// not a control to try — repainting it under the pointer on every pass makes
// dragging and selecting feel heavy — so `body.fl-builder-edit` gates the hover
// rules out. `:where()` scores zero, so the specificity worked out below is
// exactly what it was.
$live_only = ':where(body:not(.fl-builder-edit))';

// Hover only — no `:focus`. A click leaves the button focused, so pairing the
// two left the hover background and border stuck on after the pointer moved
// away. The browser's own focus ring still marks the focused button; nothing
// here resets `outline`.
$hover_selector  = "$live_only .fl-builder-content .fl-node-$id .theme-button:is(a, button):hover";

// Text colour needs a heavier prefix than everything else. Themes paint column
// content with a catch-all like Astra's
// `.fl-builder-content .fl-node-{col} *:not(span):not(input):not(textarea):not(select):not(a):not(h1)…:not(h6):not(.fl-menu-mobile-toggle)`,
// which scores three classes and eleven elements. Our own prefix is also three
// classes, so the tie breaks on element count and the column wins — repainting
// the decorative icon spans, and the button itself whenever it renders as `<button>`
// (that `:not()` list excludes `a`, not `button`).
//
// Repeating the node class buys a fourth class, and class count outranks element
// count outright, so no element list can ever catch up. Keeping it to specificity
// leaves the button overridable from Global CSS / Advanced settings, which
// `!important` would not.
$color_node           = ".fl-builder-content .fl-node-$id.fl-node-$id .theme-button:is(a, button)";
$color_selector       = "$color_node, $color_node *";
$color_hover_selector = "$live_only $color_node:hover, $live_only $color_node:hover *";

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

// Padding
FLBuilderCSS::dimension_field_rule(array(
	'settings'     => $settings,
	'setting_name' => 'padding',
	'selector'     => $button_selector,
	'unit'         => 'px',
	'props'        => array(
		'padding-top'    => 'padding_top',
		'padding-right'  => 'padding_right',
		'padding-bottom' => 'padding_bottom',
		'padding-left'   => 'padding_left',
	),
));

// Typography
FLBuilderCSS::typography_field_rule(array(
	'settings'     => $settings,
	'setting_name' => 'typography',
	'selector'     => $button_selector,
));

// Default the hover background to the normal background when one isn't set.
foreach ($breakpoints as $device) {
	$bg_color_name       = empty($device) ? 'bg_color' : "bg_color_{$device}";
	$bg_hover_color_name = empty($device) ? 'bg_hover_color' : "bg_hover_color_{$device}";

	if (! empty($settings->{$bg_color_name}) && empty($settings->{$bg_hover_color_name})) {
		$settings->{$bg_hover_color_name} = $settings->{$bg_color_name};
	}
}

// Border — explicit control only. The modern default is no border.
FLBuilderCSS::border_field_rule(array(
	'settings'     => $settings,
	'setting_name' => 'border',
	'selector'     => $button_selector,
));

foreach ($breakpoints as $device) {
	// Border hover colour.
	$setting_name = empty($device) ? 'border_hover_color' : "border_hover_color_{$device}";
	FLBuilderCSS::rule(array(
		'enabled'  => ! empty($settings->{$setting_name}),
		'media'    => $device,
		'selector' => $hover_selector,
		'props'    => array(
			'border-color' => FLBuilderColor::hex_or_rgb($settings->{$setting_name}),
		),
	));

	// Background colour.
	$setting_name = empty($device) ? 'bg_color' : "bg_color_{$device}";
	FLBuilderCSS::rule(array(
		'enabled'  => ! empty($settings->{$setting_name}),
		'media'    => $device,
		'selector' => $button_selector,
		'props'    => array(
			'background-color' => FLBuilderColor::hex_or_rgb($settings->{$setting_name}),
		),
	));

	// Background hover colour.
	$setting_name = empty($device) ? 'bg_hover_color' : "bg_hover_color_{$device}";
	FLBuilderCSS::rule(array(
		'enabled'  => ! empty($settings->{$setting_name}),
		'media'    => $device,
		'selector' => $hover_selector,
		'props'    => array(
			'background-color' => FLBuilderColor::hex_or_rgb($settings->{$setting_name}),
		),
	));

	// Text colour.
	$setting_name = empty($device) ? 'text_color' : "text_color_{$device}";
	FLBuilderCSS::rule(array(
		'enabled'  => ! empty($settings->{$setting_name}),
		'media'    => $device,
		'selector' => $color_selector,
		'props'    => array(
			'color' => FLBuilderColor::hex_or_rgb($settings->{$setting_name}),
		),
	));

	// Text hover colour.
	$setting_name = empty($device) ? 'text_hover_color' : "text_hover_color_{$device}";
	FLBuilderCSS::rule(array(
		'enabled'  => ! empty($settings->{$setting_name}),
		'media'    => $device,
		'selector' => $color_hover_selector,
		'props'    => array(
			'color' => FLBuilderColor::hex_or_rgb($settings->{$setting_name}),
		),
	));
}

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
