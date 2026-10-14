<?php

/**
 * Site branding.
 *
 * Renders the logo set under Appearance > Customize > Site Identity, and a
 * placeholder crest and wordmark when none is set. The Dark header swaps in
 * its own logo from the second slot in that section, because a full colour
 * mark is a different file rather than a recolour of the first.
 *
 * Size is set per context in the header and footer stylesheets; the
 * placeholder SVG uses currentColor so it follows the header's foreground.
 *
 * @package Evektus
 */

defined('ABSPATH') || exit;

/**
 * The attachment ID of the logo a header renders, or 0 for the placeholder.
 *
 * @param string $variant Header style, as returned by evek_get_header_style().
 */
function evek_branding_logo_id(string $variant = 'light'): int
{
	if ('dark' === $variant) {
		$dark_logo_id = (int) get_theme_mod(EVEK_DARK_LOGO_MOD);

		if ($dark_logo_id) {
			return $dark_logo_id;
		}
	}

	return (int) get_theme_mod('custom_logo');
}

/**
 * Render the site logo, or a placeholder crest and wordmark.
 *
 * @param string $variant Header style, as returned by evek_get_header_style().
 */
function evek_site_branding(string $variant = 'light'): void
{
	$logo_id = evek_branding_logo_id($variant);
?>
	<div class="site-branding">
		<?php if ($logo_id) : ?>
			<a class="custom-logo-link" href="<?php echo esc_url(home_url('/')); ?>" rel="home">
				<?php
				// Fall back to the site name only when the attachment carries
				// no alt text of its own.
				$attr = array('class' => 'custom-logo');

				if ('' === trim((string) get_post_meta($logo_id, '_wp_attachment_image_alt', true))) {
					$attr['alt'] = get_bloginfo('name');
				}

				echo wp_get_attachment_image($logo_id, 'full', false, $attr);
				?>
			</a>
		<?php else : ?>
			<?php // Placeholder mark - swap for the Evektus logo in Site Identity.
			?>
			<a class="site-branding__link" href="<?php echo esc_url(home_url('/')); ?>" rel="home">
				<svg class="site-crest" viewBox="0 0 48 60" aria-hidden="true" focusable="false">
					<circle cx="24" cy="30" r="21.5" fill="none" stroke="currentColor" stroke-width="3" />
					<circle cx="24" cy="30" r="9" fill="currentColor" />
				</svg>
				<span class="site-wordmark">
					<span class="site-wordmark__eyebrow">Evektus</span>
					<span class="site-wordmark__name">Automotive</span>
				</span>
			</a>
		<?php endif; ?>
	</div>
<?php
}
