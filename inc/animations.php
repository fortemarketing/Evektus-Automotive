<?php

/**
 * Scroll animations for Beaver Builder rows, columns and modules.
 *
 * A node animates in as it scrolls into view when it carries a data-reveal
 * attribute, added here server side so the hidden starting state is in the
 * HTML from the first paint and nothing flashes before the script runs. A node
 * opts in by its node ID in evek_animation_nodes(), with one of these effects:
 *
 *   up       fades in while rising a short way
 *   fade     fades in on the spot
 *   zoom     fades in while its background settles from a slight zoom, for
 *            rows and columns with a background photo
 *   stagger  its cards or list items rise in one after another
 *
 * Nodes that come into view together are staggered in page order. The motion
 * itself lives in assets/css/inc/animations.css and the observer in
 * assets/js/inc/animations.js.
 *
 * @package Evektus
 */

defined('ABSPATH') || exit;

/** The effects a node can use. */
function evek_animation_effects(): array
{
	return array('up', 'fade', 'zoom', 'stagger');
}

/**
 * The nodes that animate, by Beaver Builder node ID. Node IDs belong to one
 * layout, so this is site content: add to it here, or with the
 * evek_animation_nodes filter.
 *
 * @return array<string, string> Node ID => effect.
 */
function evek_animation_nodes(): array
{
	$nodes = array(
		// Home - hero: title, then the three counters.
		'd3ubf0e64khy' => 'up',
		'wuxtq7bdlpc6' => 'up',
		'mlpt09u51s2k' => 'up',
		'yru6hpelwa9v' => 'up',

		// Home - full width photo.
		'6wma1bk5zjyh' => 'zoom',

		// Home - overview: photo column and text.
		'f5qt14ldb7xi' => 'zoom',
		'6sgek4dcfpvq' => 'up',

		// Home - our services: heading, button, cards.
		'8e4t7mwdzqpl' => 'up',
		'l2aijed4rfq0' => 'up',
		'64y09z5sfqai' => 'stagger',

		// Home - about us: colour block and text.
		'gwhyopvi9c8b' => 'zoom',
		'coi4j5bkry8z' => 'up',

		// Home - other services: text and the two lists.
		'w5s8gyma0kne' => 'up',
		'syo4b895e0nz' => 'stagger',
		'1zwug57n6kap' => 'stagger',

		// Home - selling points: heading and the three boxes.
		'egurny4iq8fm' => 'up',
		'kp7fdx1se8tr' => 'up',
		'e380gfpt1kcm' => 'up',
		'isjqf2kauh5t' => 'up',

		// Home - testimonials: heading, button, carousel.
		'fwmev3pr9z54' => 'up',
		'd7l5tsjhwmr0' => 'up',
		'e310pxqiouk9' => 'up',

		// Home - closing call to action: logo and heading.
		'0ahnx9r6g8do' => 'up',
		'tkucjf3wpz98' => 'up',
	);

	return (array) apply_filters('evek_animation_nodes', $nodes);
}

/**
 * The effect a node uses from the node map, or ''.
 *
 * @param string $node_id Beaver Builder node ID.
 */
function evek_animation_effect(string $node_id): string
{
	$nodes = evek_animation_nodes();

	if (isset($nodes[$node_id]) && in_array($nodes[$node_id], evek_animation_effects(), true)) {
		return $nodes[$node_id];
	}

	return '';
}

/**
 * Adds data-reveal to an animated node. Beaver Builder prints these attribute
 * values as they are, so the effect is escaped here.
 *
 * @param array  $attrs The node's HTML attributes.
 * @param object $node  The row, column or module.
 */
function evek_animation_node_attributes(array $attrs, $node): array
{
	if (empty($node->node)) {
		return $attrs;
	}

	$effect = evek_animation_effect((string) $node->node);

	if ('' !== $effect) {
		$attrs['data-reveal'] = esc_attr($effect);
	}

	return $attrs;
}
add_filter('fl_builder_row_attributes', 'evek_animation_node_attributes', 10, 2);
add_filter('fl_builder_column_attributes', 'evek_animation_node_attributes', 10, 2);
add_filter('fl_builder_module_attributes', 'evek_animation_node_attributes', 10, 2);

/**
 * Switches the animations on for this page view, before anything paints.
 *
 * animations.css only hides a node while <html> carries evek-motion, so the
 * page stays fully visible when the script cannot run, the visitor prefers
 * reduced motion, or the builder is open. If animations.js has not taken over
 * within a few seconds - blocked, or failed to load - the class comes off
 * again and everything shows.
 */
function evek_animation_head_script(): void
{
	if (class_exists('FLBuilderModel') && FLBuilderModel::is_builder_active()) {
		return;
	}
?>
	<script>
		(function(d, w) {
			if (!('IntersectionObserver' in w) || w.matchMedia('(prefers-reduced-motion: reduce)').matches) {
				return;
			}
			d.documentElement.classList.add('evek-motion');
			w.setTimeout(function() {
				if (!w.evekAnimationsReady) {
					d.documentElement.classList.remove('evek-motion');
				}
			}, 3000);
		})(document, window);
	</script>
<?php
}
add_action('wp_head', 'evek_animation_head_script', 1);
