<?php

/**
 * Main menu.
 *
 * Renders a WordPress nav menu as a mega menu on desktop and a drill-down
 * off-canvas menu on mobile.
 *
 * Expected menu structure:
 * - Level 0: top level items (e.g. "About")
 * - Level 1: mega menu columns
 * - Level 2: group headings, or plain links when they have no children
 * - Level 3: links inside a group
 *
 * A group is a heading plus its links, and a column can stack several of them.
 * Whether a level 2 item is a heading is decided by its children alone: with
 * children it is a heading, without children it is a plain link. Runs of
 * consecutive links share one list.
 *
 * @package Evektus
 */

defined('ABSPATH') || exit;

/**
 * Build a normalised menu tree for a theme location.
 *
 * @return array<int, array{title: string, url: string, current: bool, children: array}>
 */
function evek_main_menu_tree(string $location): array
{
	$locations = get_nav_menu_locations();
	$menu_id   = isset($locations[$location]) ? (int) $locations[$location] : 0;
	$items     = $menu_id ? wp_get_nav_menu_items($menu_id) : array();

	if (empty($items)) {
		return array();
	}

	// Adds current-menu-item and friends to $item->classes.
	_wp_menu_item_classes_by_context($items);

	$by_parent = array();

	foreach ($items as $item) {
		$by_parent[(int) $item->menu_item_parent][] = $item;
	}

	return evek_main_menu_branch($by_parent, 0);
}

/** Recursively normalise one branch of the menu tree. */
function evek_main_menu_branch(array $by_parent, int $parent_id): array
{
	if (empty($by_parent[$parent_id])) {
		return array();
	}

	$branch = array();

	foreach ($by_parent[$parent_id] as $item) {
		$branch[] = array(
			'title'    => (string) $item->title,
			'url'      => (string) $item->url,
			'current'  => is_array($item->classes) && in_array('current-menu-item', $item->classes, true),
			'children' => evek_main_menu_branch($by_parent, (int) $item->ID),
		);
	}

	return $branch;
}

/**
 * Placeholder tree shown until a menu is assigned to the location, so the mega
 * menu is visible and testable straight away. A real menu replaces it.
 */
function evek_main_menu_placeholder_tree(): array
{
	$node = static function (string $title, array $children = array()): array {
		return array('title' => $title, 'url' => '#', 'current' => false, 'children' => $children);
	};

	$link = static function (string $title) use ($node): array {
		return $node($title);
	};

	// A level 2 item with children is a heading; without children it is a link.
	$group = static function (string $title, array $links) use ($node, $link): array {
		return $node($title, array_map($link, $links));
	};

	$column = static function (array $groups) use ($node): array {
		return $node('-', $groups);
	};

	return array(
		$node('About', array(
			$column(array(
				$group('Company', array('Our Story', 'Our Team')),
				$link('Careers'),
				$link('Testimonials'),
			)),
			$column(array(
				$group('Services', array('Service One', 'Service Two', 'Service Three')),
				$link('Pricing'),
			)),
			$column(array(
				$group('Resources', array('Blog', 'Case Studies', 'Downloads', 'FAQs')),
			)),
		)),
		$node('Services'),
		$node('News'),
		$node('Contact'),
	);
}

/**
 * Render the main menu.
 *
 * mega_selector sets the panel's width and horizontal position from a target
 * node (empty = full width). mega_selector_vertical sets the panel's top edge
 * (empty = pinned below the header).
 */
function evek_main_menu(array $args = array()): void
{
	$args = array_merge(
		array(
			'theme_location'         => 'primary',
			'mega_selector'          => '',
			'mega_selector_vertical' => '',
		),
		$args
	);

	$tree = evek_main_menu_tree($args['theme_location']);

	if (empty($tree)) {
		$tree = evek_main_menu_placeholder_tree();
	}

	$attributes = ' class="main-menu" aria-label="' . esc_attr__('Main menu', 'evek') . '"';

	if (!empty($args['mega_selector'])) {
		$attributes .= ' data-mega-menu="' . esc_attr($args['mega_selector']) . '"';
	}

	if (!empty($args['mega_selector_vertical'])) {
		$attributes .= ' data-mega-menu-vertical="' . esc_attr($args['mega_selector_vertical']) . '"';
	}

	echo '<nav' . $attributes . '>';

	// Mobile hamburger toggle.
	echo '<button class="main-menu__toggle" aria-label="' . esc_attr__('Open menu', 'evek') . '" aria-expanded="false">';
	echo '<span></span><span></span><span></span>';
	echo '</button>';

	// Backdrop behind the mobile off-canvas panel.
	echo '<div class="main-menu__backdrop" aria-hidden="true"></div>';

	echo '<div class="main-menu__panel">';

	echo '<div class="main-menu__panel-head">';
	echo '<button class="main-menu__close" aria-label="' . esc_attr__('Close menu', 'evek') . '">';
	echo '<span></span><span></span>';
	echo '</button>';
	echo '</div>';

	echo '<div class="main-menu__body">';
	echo '<ul class="main-menu__list">';

	foreach ($tree as $item) {
		evek_main_menu_top_item($item);
	}

	echo '</ul>';
	echo '</div>';
	echo '</div>';
	echo '</nav>';
}

/** Render one top level item and, when it has children, its mega panel. */
function evek_main_menu_top_item(array $item): void
{
	$columns  = $item['children'];
	$has_mega = !empty($columns);

	$item_classes = array('main-menu__item');

	if ($has_mega) {
		$item_classes[] = 'main-menu__item--has-mega';
	}

	if ($item['current']) {
		$item_classes[] = 'is-current';
	}

	echo '<li class="' . esc_attr(implode(' ', $item_classes)) . '">';

	printf(
		'<a class="main-menu__top-link" href="%s"%s>%s%s</a>',
		esc_url($item['url']),
		$item['current'] ? ' aria-current="page"' : '',
		esc_html($item['title']),
		$has_mega ? evek_main_menu_caret() : ''
	);

	if ($has_mega) {
		// Mobile drill-down trigger (hidden on desktop).
		evek_main_menu_drill_toggle($item['title']);

		echo '<div class="main-menu__mega">';

		evek_main_menu_back_button($item['title']);

		echo '<div class="main-menu__columns">';

		foreach ($columns as $column) {
			evek_main_menu_column($column);
		}

		echo '</div>';
		echo '</div>';
	}

	echo '</li>';
}

/**
 * Render one column of a mega panel.
 *
 * Each level 2 child with children of its own becomes a group (heading plus
 * links). Children without children of their own are plain links, and runs of
 * them share a single list.
 */
function evek_main_menu_column(array $column): void
{
	echo '<div class="main-menu__col">';

	if (empty($column['children'])) {
		// Nothing beneath it: the column item renders as a plain link.
		evek_main_menu_loose_links(array($column));
	} else {
		$loose = array();

		foreach ($column['children'] as $child) {
			if (empty($child['children'])) {
				$loose[] = $child;
				continue;
			}

			evek_main_menu_loose_links($loose);
			$loose = array();
			evek_main_menu_group($child);
		}

		evek_main_menu_loose_links($loose);
	}

	echo '</div>';
}

/**
 * Render one group: a heading plus its links. The group is the unit that
 * drills open on mobile.
 */
function evek_main_menu_group(array $group): void
{
	// The heading is a link when the item has a real URL.
	$has_url = !empty($group['url']) && $group['url'] !== '#';

	echo '<div class="main-menu__group main-menu__group--has-children">';

	// Heading row: on mobile this is a drillable row.
	echo '<div class="main-menu__col-head">';

	if ($has_url) {
		// A heading is a page in its own right, so it marks itself current the
		// same way the links beneath it do.
		printf(
			'<a class="main-menu__mega-heading" href="%s"%s>%s</a>',
			esc_url($group['url']),
			$group['current'] ? ' aria-current="page"' : '',
			esc_html($group['title'])
		);
	} else {
		echo '<span class="main-menu__mega-heading">' . esc_html($group['title']) . '</span>';
	}

	evek_main_menu_drill_toggle($group['title']);
	echo '</div>';

	// Second-level drill panel on mobile, plain list wrapper on desktop.
	echo '<div class="main-menu__sub">';
	evek_main_menu_back_button($group['title']);
	evek_main_menu_link_list($group['children']);
	echo '</div>';

	echo '</div>';
}

/** Render a run of links that sit in a column without a heading above them. */
function evek_main_menu_loose_links(array $items): void
{
	if (empty($items)) {
		return;
	}

	echo '<div class="main-menu__group main-menu__group--has-children main-menu__group--no-heading">';
	echo '<div class="main-menu__sub">';
	evek_main_menu_link_list($items);
	echo '</div>';
	echo '</div>';
}

/** Render the <ul> of links shared by every group. */
function evek_main_menu_link_list(array $items): void
{
	echo '<ul class="main-menu__sub-list">';

	foreach ($items as $item) {
		echo '<li class="main-menu__sub-item">';
		printf(
			'<a class="main-menu__sub-link" href="%s"%s>%s</a>',
			esc_url($item['url']),
			$item['current'] ? ' aria-current="page"' : '',
			esc_html($item['title'])
		);
		echo '</li>';
	}

	echo '</ul>';
}

/** Drill-down trigger used on mobile for both top level items and columns. */
function evek_main_menu_drill_toggle(string $title): void
{
	printf(
		'<button class="main-menu__drill-toggle" aria-expanded="false" aria-label="%s"><span class="main-menu__chevron" aria-hidden="true"></span></button>',
		/* translators: %s: menu item title. */
		esc_attr(sprintf(__('Open %s submenu', 'evek'), $title))
	);
}

/** Back button that closes one mobile drill level. Hidden on desktop. */
function evek_main_menu_back_button(string $title): void
{
	printf(
		'<button class="main-menu__back"><span class="main-menu__chevron main-menu__chevron--back" aria-hidden="true"></span>%s</button>',
		esc_html($title)
	);
}

/** Dropdown caret shown on top level items that open a mega panel. */
function evek_main_menu_caret(): string
{
	return '<svg class="main-menu__caret" width="11" height="6" viewBox="0 0 11 6" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M9.925 -1.24408e-05L10.8083 0.884154L5.99417 5.69999C5.91703 5.77762 5.8253 5.83922 5.72425 5.88126C5.62321 5.9233 5.51485 5.94495 5.40542 5.94495C5.29598 5.94495 5.18762 5.9233 5.08658 5.88126C4.98554 5.83922 4.89381 5.77762 4.81667 5.69999L0 0.884154L0.883333 0.000820199L5.40417 4.52082L9.925 -1.24408e-05Z" /></svg>';
}

/**
 * Give the main menu a Customizer edit shortcut.
 *
 * The pencil buttons in the preview are selective refresh partials, and core
 * only registers those for menus it rendered itself through wp_nav_menu().
 * The main menu builds its own markup, so it gets no shortcut unless a partial
 * is registered for it by hand.
 *
 * Runs at 20 so WP_Customize_Nav_Menus (11) has registered the nav menu
 * settings this hangs off.
 */
function evek_customize_register_main_menu_partial(WP_Customize_Manager $wp_customize): void
{
	// Selective refresh is what draws the shortcut; nothing to hang one on
	// without it.
	if (! isset($wp_customize->selective_refresh)) {
		return;
	}

	$locations = get_nav_menu_locations();
	$menu_id   = isset($locations['primary']) ? (int) $locations['primary'] : 0;
	$settings  = array();

	// The location setting goes first so the shortcut lands on View All
	// Locations - core focuses the primary setting, and that is the first one
	// listed.
	if ($wp_customize->get_setting('nav_menu_locations[primary]')) {
		$settings[] = 'nav_menu_locations[primary]';
	}

	// Listed too, but not first: it is what makes edits to the menu itself
	// refresh the partial.
	if ($menu_id && $wp_customize->get_setting('nav_menu[' . $menu_id . ']')) {
		$settings[] = 'nav_menu[' . $menu_id . ']';
	}

	// No menu assigned yet: the header is showing the placeholder tree, and
	// there is no setting for a shortcut to point at.
	if (empty($settings)) {
		return;
	}

	$wp_customize->selective_refresh->add_partial(
		'evek_main_menu',
		array(
			'selector'            => '.site-header .main-menu',
			'container_inclusive' => true,
			'settings'            => $settings,
			'primary_setting'     => $settings[0],
			'render_callback'     => 'evek_render_main_menu_partial',
		)
	);
}
add_action('customize_register', 'evek_customize_register_main_menu_partial', 20);

/**
 * Re-render the main menu for the partial above.
 *
 * Mirrors the call in header.php; keep the two in step or a partial refresh
 * will quietly drop whichever arguments the header passes.
 */
function evek_render_main_menu_partial(): string
{
	ob_start();
	evek_main_menu(array('theme_location' => 'primary'));

	return (string) ob_get_clean();
}
