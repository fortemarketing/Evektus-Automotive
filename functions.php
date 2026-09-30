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
require_once __DIR__ . '/inc/animations.php';

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
 * Stylesheets that only matter while Beaver Builder is open, relative to the
 * theme root. Visitors never see what they style, so they are left out of the
 * front end.
 *
 * @return string[]
 */
function evek_builder_only_styles(): array
{
	return array('assets/css/inc/builder-placeholder.css');
}

/**
 * Every theme stylesheet in cascade order, relative to the theme root:
 * base -> palette -> theme stylesheet -> per-template -> custom.
 *
 * @return string[]
 */
function evek_stylesheets(): array
{
	$files = array('assets/css/base.css', 'assets/css/theme.css', 'style.css');

	// Per-template styles, folder by folder in the order set by
	// evek_template_asset_dirs().
	foreach (evek_template_asset_dirs() as $dir => $prefix) {
		foreach (evek_asset_files('css/' . $dir, 'css') as $path) {
			$files[] = 'assets/css/' . $dir . '/' . basename($path);
		}
	}

	// custom.css stays last so it can override anything above it.
	$files[] = 'assets/css/custom.css';

	if (evek_builder_is_active()) {
		return $files;
	}

	return array_values(array_diff($files, evek_builder_only_styles()));
}

/**
 * The handle a stylesheet is enqueued under when the styles load separately.
 *
 * @param string $file Stylesheet relative to the theme root.
 */
function evek_stylesheet_handle(string $file): string
{
	$fixed = array(
		'assets/css/base.css'   => 'evek-base',
		'assets/css/theme.css'  => 'evek-palette',
		'style.css'             => 'evek-theme',
		'assets/css/custom.css' => 'evek-custom',
	);

	if (isset($fixed[$file])) {
		return $fixed[$file];
	}

	$dir = substr(dirname($file), strlen('assets/css/'));
	$map = evek_template_asset_dirs();

	return (isset($map[$dir]) ? $map[$dir] : 'evek-') . basename($file, '.css');
}

/**
 * Points a stylesheet's relative url()s at the folder it came from, so they
 * still resolve once its rules are copied into the bundle.
 *
 * @param string $css      The stylesheet.
 * @param string $base_uri URI of the folder the stylesheet lives in.
 */
function evek_css_rebase_urls(string $css, string $base_uri): string
{
	return (string) preg_replace_callback(
		'/url\(\s*([\'"]?)(.*?)\1\s*\)/i',
		static function (array $match) use ($base_uri): string {
			if (preg_match('#^(data:|[a-z][a-z0-9+.-]*:|//|/|\#)#i', $match[2])) {
				return $match[0];
			}

			return 'url("' . $base_uri . '/' . $match[2] . '")';
		},
		$css
	);
}

/**
 * Joins the stylesheets into one file in uploads/evek/ and returns its URL,
 * or '' when it cannot be written, in which case the caller loads them
 * separately. The name is a hash of every file's path and modification time,
 * so editing, adding or removing a stylesheet produces a new bundle and the
 * browser never serves a stale one.
 *
 * Older bundles are kept for a while rather than deleted straight away, since
 * a cached page can still point at one until the page cache clears.
 *
 * @param string[] $files Stylesheets relative to the theme root.
 */
function evek_css_bundle(array $files): string
{
	$upload = wp_upload_dir(null, false);

	if (! empty($upload['error'])) {
		return '';
	}

	$stamp = '';

	foreach ($files as $file) {
		$stamp .= $file . ':' . filemtime(EVEK_DIR . '/' . $file) . ';';
	}

	$dir  = trailingslashit($upload['basedir']) . 'evek';
	$name = 'theme-' . substr(md5($stamp), 0, 12) . '.css';
	$path = $dir . '/' . $name;

	if (! is_file($path)) {
		if (! wp_mkdir_p($dir)) {
			return '';
		}

		$css = '';

		foreach ($files as $file) {
			$folder   = dirname($file);
			$base_uri = EVEK_URI . ('.' === $folder ? '' : '/' . $folder);
			$css     .= '/* ' . $file . " */\n" . evek_css_rebase_urls((string) file_get_contents(EVEK_DIR . '/' . $file), $base_uri) . "\n";
		}

		// Written to a temporary file and renamed into place, so a request
		// arriving mid-write never gets half a stylesheet.
		$temp = $path . '.' . uniqid('', true) . '.tmp';

		if (false === file_put_contents($temp, $css) || ! rename($temp, $path)) {
			if (is_file($temp)) {
				unlink($temp);
			}
			return '';
		}

		foreach (glob($dir . '/theme-*.css') ?: array() as $old) {
			if ($old !== $path && filemtime($old) < time() - 30 * DAY_IN_SECONDS) {
				unlink($old);
			}
		}
	}

	return trailingslashit($upload['baseurl']) . 'evek/' . $name;
}

/**
 * Load the theme assets with cache-busting versions.
 *
 * The stylesheets go out as one bundled file. Turn that off with the
 * evek_bundle_css filter, or by defining SCRIPT_DEBUG, to load each file
 * separately in the same order - handy when finding which file a rule is in.
 */
function evek_enqueue_assets(): void
{
	$custom_js = EVEK_DIR . '/assets/js/custom.js';
	$files     = evek_stylesheets();
	$bundle    = apply_filters('evek_bundle_css', ! (defined('SCRIPT_DEBUG') && SCRIPT_DEBUG))
		? evek_css_bundle($files)
		: '';

	if ('' !== $bundle) {
		// The version is already in the file name.
		wp_enqueue_style('evek-styles', $bundle, array(), null);
	} else {
		// Each file chained to the last so the cascade stays in order no
		// matter how many are added.
		$dependency = array();

		foreach ($files as $file) {
			$handle = evek_stylesheet_handle($file);

			wp_enqueue_style(
				$handle,
				'style.css' === $file ? get_stylesheet_uri() : EVEK_URI . '/' . $file,
				$dependency,
				(string) filemtime(EVEK_DIR . '/' . $file)
			);

			$dependency = array($handle);
		}
	}

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
