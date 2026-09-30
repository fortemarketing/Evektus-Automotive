<?php

/**
 * Evektus theme setup.
 *
 * @package Evektus
 */

defined('ABSPATH') || exit;

// This theme is standalone, so the Evektus paths resolve to the active theme
// itself rather than to a parent theme.
define('EVEK_DIR', get_stylesheet_directory());
define('EVEK_URI', get_stylesheet_directory_uri());

require_once __DIR__ . '/inc/branding.php';
require_once __DIR__ . '/inc/main-menu.php';
require_once __DIR__ . '/inc/scroll-top.php';
require_once __DIR__ . '/inc/builder-placeholder.php';

// Every shortcode lives in inc/shortcodes/, one file per shortcode. Dropping a
// file in there is enough to register it; no edit to this list is needed.
foreach (glob(__DIR__ . '/inc/shortcodes/*.php') ?: array() as $evek_shortcode_file) {
	require_once $evek_shortcode_file;
}
unset($evek_shortcode_file);

add_action(
	'after_setup_theme',
	static function (): void {
		load_theme_textdomain('evek', EVEK_DIR . '/languages');
		add_theme_support('title-tag');
		add_theme_support('post-thumbnails');
		add_image_size('card', 450, 350, true);
		add_image_size('portrait', 350, 450, true);
		add_image_size('landscape', 1024, 576);
		add_image_size('landscape-small', 480, 270);
		add_theme_support('responsive-embeds');
		add_theme_support('align-wide');
		add_theme_support('editor-styles');
		// base.css reads its fonts and colours from the variables in theme.css,
		// and every var() in it is invalid without them. When the site loads
		// web fonts, put their stylesheet URL first in this list: Beaver Builder
		// loads them on the front end, but nothing loads them into the editor.
		add_editor_style(
			array(
				'assets/css/theme.css',
				'assets/css/base.css',
				'assets/css/editor.css',
			)
		);
		add_theme_support(
			'html5',
			array('search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script')
		);
		add_theme_support(
			'custom-logo',
			array('height' => 120, 'width' => 360, 'flex-height' => true, 'flex-width' => true)
		);
		register_nav_menus(
			array(
				'primary' => __('Primary Menu', 'evek'),
				'footer'  => __('Footer Menu', 'evek'),
			)
		);

		// Native Beaver Themer header, footer and hook-based part support.
		add_theme_support('fl-theme-builder-headers');
		add_theme_support('fl-theme-builder-footers');
		add_theme_support('fl-theme-builder-parts');
	}
);

/** Register the theme's action hooks as Beaver Themer Part positions. */
function evek_theme_builder_part_hooks(array $hook_groups): array
{
	$hook_groups[] = array(
		'label' => __('Header', 'evek'),
		'hooks' => array(
			'evek_before_header' => __('Before Header', 'evek'),
			'evek_after_header'  => __('After Header', 'evek'),
		),
	);
	$hook_groups[] = array(
		'label' => __('Content', 'evek'),
		'hooks' => array(
			'evek_before_content' => __('Before Content', 'evek'),
			'evek_after_content'  => __('After Content', 'evek'),
		),
	);
	$hook_groups[] = array(
		'label' => __('Footer', 'evek'),
		'hooks' => array(
			'evek_before_footer' => __('Before Footer', 'evek'),
			'evek_after_footer'  => __('After Footer', 'evek'),
		),
	);

	return $hook_groups;
}
add_filter('fl_theme_builder_part_hooks', 'evek_theme_builder_part_hooks');

/**
 * List the asset files in a theme sub-directory, sorted so the cascade is
 * deterministic. Dropping a file into the directory is enough to load it.
 *
 * @return string[] Absolute paths.
 */
function evek_asset_files(string $relative_dir, string $extension): array
{
	$files = glob(EVEK_DIR . '/assets/' . $relative_dir . '/*.' . $extension);

	if (empty($files)) {
		return array();
	}

	sort($files);

	return $files;
}

/**
 * The per-template asset folders under assets/css/ and assets/js/, in load
 * order, each mapped to the handle prefix its files are enqueued under.
 *
 * Each folder mirrors where its PHP lives: inc/ for the components in inc/,
 * templates/ for the page templates at the theme root, and inc/shortcodes/ for
 * inc/shortcodes/. Each gets its own prefix so two files can share a name
 * without their handles colliding.
 *
 * The order is not the folder order, it is the cascade. Components load first
 * because the page templates restyle them in place - header.css sizes the logo
 * that branding.css lays out - and shortcodes last, so they can build on
 * anything a component in inc/ provides.
 *
 * @return array<string, string> Folder relative to assets/{css,js}/ => prefix.
 */
function evek_template_asset_dirs(): array
{
	return array(
		'inc'            => 'evek-inc-',
		'templates'      => 'evek-template-',
		'inc/shortcodes' => 'evek-shortcode-',
	);
}

/**
 * Load the theme assets in cascade order with cache-busting versions:
 * base -> palette -> theme stylesheet -> per-template -> custom.
 */
function evek_enqueue_assets(): void
{
	$base_css   = EVEK_DIR . '/assets/css/base.css';
	$theme_css  = EVEK_DIR . '/assets/css/theme.css';
	$stylesheet = EVEK_DIR . '/style.css';
	$custom_css = EVEK_DIR . '/assets/css/custom.css';
	$custom_js  = EVEK_DIR . '/assets/js/custom.js';

	wp_enqueue_style(
		'evek-base',
		EVEK_URI . '/assets/css/base.css',
		array(),
		(string) filemtime($base_css)
	);

	// The brand palette and type scale. Everything downstream references these
	// custom properties rather than restating the values.
	wp_enqueue_style(
		'evek-palette',
		EVEK_URI . '/assets/css/theme.css',
		array('evek-base'),
		(string) filemtime($theme_css)
	);

	wp_enqueue_style(
		'evek-theme',
		get_stylesheet_uri(),
		array('evek-palette'),
		(string) filemtime($stylesheet)
	);

	// Per-template styles, folder by folder in the order set by
	// evek_template_asset_dirs(), each chained to the last so their order
	// stays predictable no matter how many files are added.
	$dependency = 'evek-theme';

	foreach (evek_template_asset_dirs() as $dir => $prefix) {
		foreach (evek_asset_files('css/' . $dir, 'css') as $path) {
			$handle = $prefix . basename($path, '.css');

			wp_enqueue_style(
				$handle,
				EVEK_URI . '/assets/css/' . $dir . '/' . basename($path),
				array($dependency),
				(string) filemtime($path)
			);

			$dependency = $handle;
		}
	}

	// custom.css stays last so it can override anything above it.
	wp_enqueue_style(
		'evek-custom',
		EVEK_URI . '/assets/css/custom.css',
		array($dependency),
		(string) filemtime($custom_css)
	);

	// Per-template scripts, from the same folders as the styles. Each is
	// self-contained, so they carry no dependencies on one another.
	foreach (evek_template_asset_dirs() as $dir => $prefix) {
		foreach (evek_asset_files('js/' . $dir, 'js') as $path) {
			wp_enqueue_script(
				$prefix . basename($path, '.js'),
				EVEK_URI . '/assets/js/' . $dir . '/' . basename($path),
				array(),
				(string) filemtime($path),
				true
			);
		}
	}

	wp_enqueue_script(
		'evek-custom',
		EVEK_URI . '/assets/js/custom.js',
		array(),
		(string) filemtime($custom_js),
		true
	);
}
add_action('wp_enqueue_scripts', 'evek_enqueue_assets', 20);

/**
 * The post types that render without the site header and footer.
 *
 * A saved Beaver Builder template is a fragment meant to be dropped into a
 * page, but the builder routes it through page.php, which would wrap it in the
 * chrome of a full page.
 */
function evek_chromeless_post_types(): array
{
	return (array) apply_filters('evek_chromeless_post_types', array('fl-builder-template'));
}

/**
 * Whether the site header and footer are suppressed on this view.
 *
 * Only the two evek_render_location() calls are skipped, so the document, its
 * assets and the builder's own editing UI are untouched.
 */
function evek_hides_site_chrome(): bool
{
	return (bool) apply_filters('evek_hides_site_chrome', is_singular(evek_chromeless_post_types()));
}

/** Render a Themer layout when assigned, otherwise run the fallback callback. */
function evek_render_location(string $location, callable $fallback): void
{
	if (evek_hides_site_chrome()) {
		return;
	}

	$data_method   = 'get_current_page_' . $location . '_ids';
	$render_method = 'render_' . $location;

	if (
		class_exists('FLThemeBuilderLayoutData') && class_exists('FLThemeBuilderLayoutRenderer')
		&& is_callable(array('FLThemeBuilderLayoutData', $data_method))
		&& ! empty(FLThemeBuilderLayoutData::$data_method())
	) {
		FLThemeBuilderLayoutRenderer::$render_method();
		return;
	}

	$fallback();
}
