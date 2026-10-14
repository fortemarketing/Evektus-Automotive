<?php

/**
 * Per-post Theme Options.
 *
 * A "Theme Options" panel on every public post type, holding the single
 * control the theme needs: which header the entry renders with. It is a
 * standard meta box, so Screen Options (classic editor) and Preferences >
 * Advanced panels (block editor) can hide it per user.
 *
 * @package Evektus
 */

defined('ABSPATH') || exit;

/** Post meta key holding the chosen header. An empty value means "inherit". */
const EVEK_HEADER_META_KEY = '_evek_header_style';

/**
 * The available headers, keyed by the value stored in post meta.
 *
 * Every header overlays the hero; the choice is purely which colour the type
 * and rules take, so the hero behind it stays readable. Each key maps to a
 * `.site-header--{key}` modifier class in header.css, so adding a header is a
 * matter of adding an entry here and a matching block of CSS.
 *
 * @return array<string, string> Value => label.
 */
function evek_header_styles(): array
{
	return apply_filters(
		'evek_header_styles',
		array(
			'light' => __('Light', 'evek'),
			'dark'  => __('Dark', 'evek'),
		)
	);
}

/**
 * Values saved before the headers were reworked into Light and Dark, mapped
 * to their nearest equivalent so existing entries keep their intent.
 *
 * @return array<string, string> Retired value => current value.
 */
function evek_legacy_header_styles(): array
{
	return array(
		'transparent' => 'light',
		'default'     => 'dark',
	);
}

/** The header used wherever nothing has chosen one. */
function evek_default_header_style(): string
{
	$style = (string) apply_filters('evek_default_header_style', 'light');

	return isset(evek_header_styles()[$style]) ? $style : 'light';
}

/**
 * Resolve the header for a post, falling back to the site default.
 *
 * @param int|null $post_id Post to read, or null for the one being viewed.
 */
function evek_get_header_style(?int $post_id = null): string
{
	if (null === $post_id) {
		$post_id = evek_queried_post_id();
	}

	$style = $post_id ? (string) get_post_meta($post_id, EVEK_HEADER_META_KEY, true) : '';
	$style = evek_legacy_header_styles()[$style] ?? $style;

	if (! isset(evek_header_styles()[$style])) {
		$style = evek_default_header_style();
	}

	return (string) apply_filters('evek_header_style', $style, $post_id);
}

/**
 * Posts always use the Dark header.
 *
 * This runs on the filter at the end of evek_get_header_style(), so it lands
 * after the post meta has been read and overrides the per-post choice rather
 * than acting as its default. Drop the add_filter() below to hand the choice
 * back to the Theme Options panel.
 *
 * @param string $style   Header resolved from the post meta or site default.
 * @param int    $post_id Post being resolved, or 0 on a view without one.
 */
function evek_dark_header_for_posts(string $style, int $post_id): string
{
	if ($post_id && 'post' === get_post_type($post_id)) {
		return 'dark';
	}

	return $style;
}
add_filter('evek_header_style', 'evek_dark_header_for_posts', 10, 2);

/**
 * The post whose Theme Options apply to the current view: the singular entry,
 * or the page assigned to the posts page on the blog index.
 */
function evek_queried_post_id(): int
{
	if (is_singular()) {
		return (int) get_queried_object_id();
	}

	if (is_home()) {
		return (int) get_option('page_for_posts');
	}

	return 0;
}

/** The post types that get a Theme Options panel: everything public but media. */
function evek_theme_options_post_types(): array
{
	$post_types = get_post_types(array('public' => true));

	unset($post_types['attachment']);

	return (array) apply_filters('evek_theme_options_post_types', array_values($post_types));
}

/** Register the panel. Only the post type being edited is passed in. */
function evek_add_theme_options_meta_box(string $post_type): void
{
	if (! in_array($post_type, evek_theme_options_post_types(), true)) {
		return;
	}

	add_meta_box(
		'evek-theme-options',
		__('Theme Options', 'evek'),
		'evek_render_theme_options_meta_box',
		$post_type,
		'side'
	);
}
add_action('add_meta_boxes', 'evek_add_theme_options_meta_box');

/** Render the panel. */
function evek_render_theme_options_meta_box(WP_Post $post): void
{
	wp_nonce_field('evek_theme_options', 'evek_theme_options_nonce');

	$styles   = evek_header_styles();
	$selected = (string) get_post_meta($post->ID, EVEK_HEADER_META_KEY, true);
	$inherit  = $styles[evek_default_header_style()] ?? '';
?>
	<p class="post-attributes-label-wrapper">
		<label class="post-attributes-label" for="evek-header-style"><?php esc_html_e('Header', 'evek'); ?></label>
	</p>
	<select class="widefat" id="evek-header-style" name="evek_header_style">
		<option value="" <?php selected($selected, ''); ?>>
			<?php
			/* translators: %s: name of the header used when nothing is chosen. */
			printf(esc_html__('Theme Default (%s)', 'evek'), esc_html($inherit));
			?>
		</option>
		<?php foreach ($styles as $value => $label) : ?>
			<option value="<?php echo esc_attr($value); ?>" <?php selected($selected, $value); ?>>
				<?php echo esc_html($label); ?>
			</option>
		<?php endforeach; ?>
	</select>
<?php
}

/** Save the panel. An unrecognised value clears the meta back to "inherit". */
function evek_save_theme_options(int $post_id): void
{
	$nonce = isset($_POST['evek_theme_options_nonce'])
		? sanitize_key(wp_unslash($_POST['evek_theme_options_nonce']))
		: '';

	if (! $nonce || ! wp_verify_nonce($nonce, 'evek_theme_options')) {
		return;
	}

	if ((defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) || wp_is_post_revision($post_id)) {
		return;
	}

	if (! current_user_can('edit_post', $post_id)) {
		return;
	}

	$style = isset($_POST['evek_header_style'])
		? sanitize_key(wp_unslash($_POST['evek_header_style']))
		: '';

	if (isset(evek_header_styles()[$style])) {
		update_post_meta($post_id, EVEK_HEADER_META_KEY, $style);
		return;
	}

	delete_post_meta($post_id, EVEK_HEADER_META_KEY);
}
add_action('save_post', 'evek_save_theme_options');

/** Expose the resolved header to CSS that needs to react to it. */
function evek_header_body_class(array $classes): array
{
	$classes[] = 'has-' . evek_get_header_style() . '-header';

	return $classes;
}
add_filter('body_class', 'evek_header_body_class');
