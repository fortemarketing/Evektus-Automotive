<?php

/**
 * Markup shared by the accordion shortcodes in inc/shortcodes/.
 *
 * @package Evektus
 */

defined('ABSPATH') || exit;

/** The icon font class every accordion toggle uses. */
const EVEK_ACCORDION_ICON = 'evek-arrow-down';

/**
 * The toggle icon: the arrow from the site's Beaver Builder icon set, the same
 * one the fm-accordion module is given.
 *
 * Beaver Builder only loads a custom icon set's stylesheet for icons its own
 * modules use, so this asks it to load the set for this icon too. A late
 * enqueue like this prints in the footer, which is still before the font is
 * needed.
 */
function evek_accordion_icon(): string
{
	if (class_exists('FLBuilderIcons')) {
		FLBuilderIcons::enqueue_styles_for_icon(EVEK_ACCORDION_ICON);
	}

	return '<span class="evek-accordion-icon-wrap">'
		. '<i class="fm-accordion-button-icon ' . esc_attr(EVEK_ACCORDION_ICON) . '" aria-hidden="true"></i>'
		. '</span>';
}
