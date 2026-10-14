<?php

/**
 * Evektus theme setup.
 *
 * @package Evektus
 */

defined('ABSPATH') || exit;

require_once __DIR__ . '/inc/customizer.php';
require_once __DIR__ . '/inc/branding.php';
require_once __DIR__ . '/inc/main-menu.php';
require_once __DIR__ . '/inc/theme-options.php';
require_once __DIR__ . '/inc/scroll-top.php';
require_once __DIR__ . '/inc/builder-placeholder.php';
require_once __DIR__ . '/inc/accordion.php';
require_once __DIR__ . '/inc/uploads.php';

// Every shortcode lives in inc/shortcodes/, one file per shortcode.
foreach (glob(__DIR__ . '/inc/shortcodes/*.php') ?: array() as $evek_shortcode_file) {
	require_once $evek_shortcode_file;
}
unset($evek_shortcode_file);

// This theme is standalone, so the Forte paths resolve to the active
// theme itself rather than to a parent theme.
define('FORTE_DIR', get_stylesheet_directory());
define('FORTE_URI', get_stylesheet_directory_uri());

add_action(
	'after_setup_theme',
	static function (): void {
		load_theme_textdomain('evek', FORTE_DIR . '/languages');
		add_theme_support('title-tag');
		add_theme_support('post-thumbnails');
		add_post_type_support('page', 'excerpt');
		add_image_size('card', 450, 350, true);
		add_image_size('portrait', 350, 450, true);
		add_image_size('landscape', 1024, 576);
		add_image_size('landscape-small', 480, 270);
		add_theme_support('responsive-embeds');
		add_theme_support('align-wide');
		add_theme_support('editor-styles');
		// Order matters: base.css reads its fonts and colours from the
		// variables in theme.css, and every var() in it is invalid without
		// them - which is what left the editor in Times New Roman.
		add_editor_style(
			array(
				// Beaver Builder loads Inter on the front end; nothing loads it
				// into the classic editor. Median is not a Google font: its
				// @font-face is in theme.json, and in editor.css for TinyMCE.
				'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap',
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
				'footer-1' => __('Footer Menu 1', 'evek'),
				'footer-2' => __('Footer Menu 2', 'evek'),
				'footer-3' => __('Footer Menu 3', 'evek'),
				'legal'   => __('Legal Menu', 'evek'),
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
			'forte_before_header' => __('Before Header', 'evek'),
			'forte_after_header'  => __('After Header', 'evek'),
		),
	);
	$hook_groups[] = array(
		'label' => __('Content', 'evek'),
		'hooks' => array(
			'forte_before_content' => __('Before Content', 'evek'),
			'forte_after_content'  => __('After Content', 'evek'),
		),
	);
	$hook_groups[] = array(
		'label' => __('Footer', 'evek'),
		'hooks' => array(
			'forte_before_footer' => __('Before Footer', 'evek'),
			'forte_after_footer'  => __('After Footer', 'evek'),
		),
	);

	return $hook_groups;
}
add_filter('fl_theme_builder_part_hooks', 'evek_theme_builder_part_hooks');

/**
 * Load the theme's own Beaver Builder modules, one folder each in
 * fl-builder/modules/.
 *
 * Beaver Builder only loads a module from the theme when its folder is in this
 * list; dropping one in without it does nothing.
 */
function evek_builder_module_paths($paths)
{
	return array_merge((array) $paths, glob(FORTE_DIR . '/fl-builder/modules/*', GLOB_ONLYDIR) ?: array());
}
add_filter('fl_builder_load_modules_paths', 'evek_builder_module_paths');

/**
 * List the asset files in a theme sub-directory, sorted so the cascade is
 * deterministic. Dropping a file into the directory is enough to load it.
 *
 * @return string[] Absolute paths.
 */
function evek_asset_files(string $relative_dir, string $extension): array
{
	$files = glob(FORTE_DIR . '/assets/' . $relative_dir . '/*.' . $extension);

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
 * because the page templates restyle them in place - header.css and
 * footer.css override branding.css at equal specificity - and shortcodes
 * last, after the accordion chrome in inc/ that two of them build on.
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
 * Every front-end stylesheet in cascade order:
 * base -> reusable classes -> palette -> theme stylesheet -> per-template -> custom.
 *
 * The bundle concatenates these and the fallback enqueues them, so both load
 * in exactly the same order.
 *
 * @return string[] Absolute paths.
 */
function evek_css_files(): array
{
	$files = array(
		FORTE_DIR . '/assets/css/base.css',
		FORTE_DIR . '/assets/css/classes.css',
		FORTE_DIR . '/assets/css/theme.css',
		FORTE_DIR . '/style.css',
	);

	foreach (array_keys(evek_template_asset_dirs()) as $dir) {
		$files = array_merge($files, evek_asset_files('css/' . $dir, 'css'));
	}

	// custom.css stays last so it can override anything above it.
	$files[] = FORTE_DIR . '/assets/css/custom.css';

	return $files;
}

/**
 * Where CSS bundles are written: a folder of their own under uploads/, since
 * the theme folder may not be writable and is replaced on every deploy.
 *
 * @return array{path: string, url: string}
 */
function evek_css_bundle_dir(): array
{
	$uploads = wp_upload_dir(null, false);

	return array(
		'path' => $uploads['basedir'] . '/evek-cache',
		'url'  => set_url_scheme($uploads['baseurl'] . '/evek-cache'),
	);
}

/**
 * The bundle's filename, hashed from every source file's path and mtime.
 *
 * Editing, adding or removing any stylesheet changes the name, so a new bundle
 * is built on the next request with no cache to clear and no stale copy left
 * in a browser.
 */
function evek_css_bundle_name(array $files): string
{
	$stamp = '';

	foreach ($files as $file) {
		$stamp .= $file . '|' . filemtime($file) . "\n";
	}

	return 'theme-' . substr(md5($stamp), 0, 12) . '.css';
}

/**
 * Concatenate the theme's stylesheets into one file and return its path,
 * building it first if the current bundle does not exist yet.
 *
 * Returns null when the file cannot be written, so the caller falls back to
 * enqueuing each stylesheet on its own - the page is never left unstyled.
 */
function evek_css_bundle_path(): ?string
{
	$files = evek_css_files();
	$dir   = evek_css_bundle_dir();
	$name  = evek_css_bundle_name($files);
	$path  = $dir['path'] . '/' . $name;

	if (! file_exists($path)) {
		if (! wp_mkdir_p($dir['path'])) {
			return null;
		}

		$css = '';

		foreach ($files as $file) {
			$contents = file_get_contents($file);

			if (false === $contents) {
				return null;
			}

			$css .= $contents . "\n";
		}

		// Comments and whitespace only. gzip takes care of the rest, and
		// anything cleverer risks rewriting strings inside content: values.
		$css = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $css);
		$css = trim(preg_replace('/\s+/', ' ', $css));

		// Write then rename, so a concurrent request never serves a
		// half-written file. If the rename loses a race to another request
		// that has already written the same bundle, that copy is identical.
		$tmp = $path . '.' . uniqid('', true) . '.tmp';

		if (false === file_put_contents($tmp, $css) || ! rename($tmp, $path)) {
			if (file_exists($tmp)) {
				unlink($tmp);
			}

			if (! file_exists($path)) {
				return null;
			}
		}
	}

	return $path;
}

/**
 * Enqueue each stylesheet on its own, chained so the cascade holds. Used when
 * bundling is switched off or the bundle cannot be written.
 */
function evek_enqueue_css_files(): void
{
	$dependency = array();

	foreach (evek_css_files() as $path) {
		$relative = substr($path, strlen(FORTE_DIR) + 1);
		$handle   = 'evek-' . str_replace('/', '-', preg_replace('#^assets/css/|\.css$#', '', $relative));

		wp_enqueue_style(
			$handle,
			FORTE_URI . '/' . $relative,
			$dependency,
			(string) filemtime($path)
		);

		$dependency = array($handle);
	}
}

/**
 * Load the theme assets. Styles arrive as one bundled file - the approach
 * Astra's addon takes in Astra_Minify - so a page makes one render-blocking
 * request instead of one per stylesheet.
 *
 * Bundling is off under SCRIPT_DEBUG so each rule can be traced to its file in
 * dev tools. Filter evek_bundle_css to force it either way.
 *
 * The bundle is printed inline in a <style> block rather than linked, which
 * takes the last render-blocking request of the theme's own off the critical
 * path. At ~13 KiB brotli'd into the page, that costs less than the round trip
 * it saves on a first visit. Filter evek_inline_css to false to link it instead,
 * so it is cached across pages - worth doing if the bundle grows much larger.
 */
function evek_enqueue_assets(): void
{
	$path = apply_filters('evek_bundle_css', ! (defined('SCRIPT_DEBUG') && SCRIPT_DEBUG))
		? evek_css_bundle_path()
		: null;
	$css  = null !== $path && apply_filters('evek_inline_css', true)
		? file_get_contents($path)
		: false;

	if (false !== $css) {
		// A handle with no src prints only its inline style, in the same spot
		// in <head> the linked bundle would have taken.
		wp_register_style('evek-theme', false, array(), null);
		wp_enqueue_style('evek-theme');
		wp_add_inline_style('evek-theme', $css);
	} elseif (null !== $path) {
		// The filename already changes with the contents, so no ?ver= is needed.
		wp_enqueue_style('evek-theme', evek_css_bundle_dir()['url'] . '/' . basename($path), array(), null);
	} else {
		evek_enqueue_css_files();
	}

	// Per-template scripts, from the same folders as the styles. Each is
	// self-contained, so they carry no dependencies on one another.
	foreach (evek_template_asset_dirs() as $dir => $prefix) {
		foreach (evek_asset_files('js/' . $dir, 'js') as $path) {
			wp_enqueue_script(
				$prefix . basename($path, '.js'),
				FORTE_URI . '/assets/js/' . $dir . '/' . basename($path),
				array(),
				(string) filemtime($path),
				true
			);
		}
	}

	wp_enqueue_script(
		'evek-custom',
		FORTE_URI . '/assets/js/custom.js',
		array(),
		(string) filemtime(FORTE_DIR . '/assets/js/custom.js'),
		true
	);
}
add_action('wp_enqueue_scripts', 'evek_enqueue_assets', 20);

/**
 * List Median in Beaver Builder's font picker.
 *
 * Median is not a Google font, so Beaver Builder neither lists it nor knows
 * how to load it. The theme loads it instead - theme.json declares the font
 * face, and WordPress prints it on every front-end page, the builder included
 * - so it is added here as a system font: selectable in module settings, with
 * nothing for Beaver Builder to enqueue.
 */
function evek_builder_system_fonts($fonts)
{
	$fonts = (array) $fonts;

	$fonts['Median'] = array(
		'fallback' => '"Arial Black", "Helvetica Neue", sans-serif',
		'weights'  => array('400'),
	);

	return $fonts;
}
add_filter('fl_builder_font_families_system', 'evek_builder_system_fonts');

/**
 * Delete superseded CSS bundles once a day.
 *
 * A superseded bundle is kept for a day rather than deleted as soon as it is
 * replaced: pages held in a page cache or CDN still link to it, and would load
 * unstyled until purged. Abandoned .tmp files from failed writes go too.
 */
function evek_prune_css_bundles(): void
{
	$dir     = evek_css_bundle_dir();
	$current = evek_css_bundle_name(evek_css_files());
	$cutoff  = time() - DAY_IN_SECONDS;
	$stale   = array_merge(
		glob($dir['path'] . '/theme-*.css') ?: array(),
		glob($dir['path'] . '/theme-*.tmp') ?: array()
	);

	foreach ($stale as $file) {
		if (basename($file) !== $current && filemtime($file) < $cutoff) {
			unlink($file);
		}
	}
}
add_action('evek_prune_css_bundles', 'evek_prune_css_bundles');

add_action(
	'init',
	static function (): void {
		if (! wp_next_scheduled('evek_prune_css_bundles')) {
			wp_schedule_event(time() + HOUR_IN_SECONDS, 'daily', 'evek_prune_css_bundles');
		}
	}
);

add_action(
	'switch_theme',
	static function (): void {
		wp_clear_scheduled_hook('evek_prune_css_bundles');
	}
);

/** Render a Themer layout when assigned, otherwise run the fallback callback. */
function forte_render_location(string $location, callable $fallback): void
{
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

/**
 * Render a placeholder menu so the header design is visible before any menu
 * exists. Assigning a real menu to the location replaces this automatically.
 */
function evek_render_placeholder_menu(string $class, array $items): void
{
	echo '<ul class="' . esc_attr($class) . ' menu--placeholder">';

	foreach ($items as $item) {
		printf('<li class="menu-item"><a href="#">%s</a></li>', esc_html($item));
	}

	echo '</ul>';
}

/** Placeholder heading and links for each footer column, keyed by location. */
function evek_footer_menu_placeholders(): array
{
	return array(
		'footer-1' => array('Repairs', array('Brakes and Clutch Repairs', 'Steering & Suspension', 'Air Conditioning', 'Auto Electrical', 'Fleet Maintenance')),
		'footer-2' => array('Servicing', array('4×4 Servicing and Repairs', 'EV Servicing and Repairs', 'Caravans and Motorhomes', 'Tailored Work Vehicle Fitouts', 'Logbook Servicing')),
		'footer-3' => array('Mechanical', array('Mechanical Repairs', 'RWC and COI', 'Diagnostics', 'Batteries')),
	);
}

/**
 * The heading over a footer column: the name of the menu assigned to its
 * location, so it is renamed under Appearance > Menus like any menu. Until a
 * menu is assigned, the column's placeholder heading.
 */
function evek_footer_menu_heading(string $location): string
{
	$locations = get_nav_menu_locations();
	$menu      = isset($locations[$location]) ? wp_get_nav_menu_object($locations[$location]) : false;

	if ($menu) {
		return $menu->name;
	}

	$placeholders = evek_footer_menu_placeholders();

	return $placeholders[$location][0] ?? '';
}

/** Placeholder links for a footer column, shown until a menu is assigned to it. */
function evek_footer_menu_fallback(array $args): void
{
	$placeholders = evek_footer_menu_placeholders();
	$location     = $args['theme_location'] ?? '';

	if (isset($placeholders[$location])) {
		evek_render_placeholder_menu('sub-menu', $placeholders[$location][1]);
	}
}

/** Placeholder items for the Legal location. */
function evek_legal_menu_fallback(): void
{
	evek_render_placeholder_menu(
		'legal-menu',
		array('Privacy Policy')
	);
}
