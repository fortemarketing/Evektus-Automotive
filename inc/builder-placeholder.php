<?php

/**
 * Beaver Builder placeholders.
 *
 * A shortcode should render nothing when the data behind it is absent. On the
 * front end that is what should happen. Inside the builder it leaves a module
 * with no height, which cannot be selected, moved or deleted, so a row can end
 * up holding something invisible.
 *
 * evek_builder_placeholder() renders a labelled box in its place, the way the
 * FacetWP modules do, and only while the builder is active. Nothing here ever
 * reaches a visitor. Return it from a shortcode's empty case:
 *
 *   if (empty($items)) {
 *       return evek_builder_placeholder('Team Grid', 'No team members published yet.');
 *   }
 *
 * @package Evektus
 */

defined('ABSPATH') || exit;

/** Whether a page is being edited in Beaver Builder right now. */
function evek_builder_is_active(): bool
{
	return class_exists('FLBuilderModel') && FLBuilderModel::is_builder_active();
}

/**
 * A placeholder box naming what would render here, or '' outside the builder.
 *
 * @param string $label The shortcode's name, as an editor would know it.
 * @param string $note  Why there is nothing to show, in one short sentence.
 */
function evek_builder_placeholder(string $label, string $note = ''): string
{
	if (!evek_builder_is_active()) {
		return '';
	}

	$output = '<div class="evek-builder-placeholder">'
		. '<span class="evek-builder-placeholder__label">' . esc_html($label) . '</span>';

	if ('' !== $note) {
		$output .= '<span class="evek-builder-placeholder__note">' . esc_html($note) . '</span>';
	}

	return $output . '</div>';
}
