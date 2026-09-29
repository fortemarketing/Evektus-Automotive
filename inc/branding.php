<?php

/**
 * Site branding.
 *
 * Renders the logo set under Appearance > Customize > Site Identity, or the
 * site title as text when none is set. Size is set per context in the header
 * and footer stylesheets.
 *
 * @package Evektus
 */

defined('ABSPATH') || exit;

/** Render the site logo, or the site title when no logo is set. */
function evek_site_branding(): void
{
	$logo_id = (int) get_theme_mod('custom_logo');

	// A logo whose attachment has since been deleted falls through to the
	// title rather than leaving an empty link behind.
	$has_logo = $logo_id && wp_attachment_is_image($logo_id);
?>
	<div class="site-branding">
		<?php if ($has_logo) : ?>
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
			<a class="site-title" href="<?php echo esc_url(home_url('/')); ?>" rel="home"><?php echo esc_html(get_bloginfo('name')); ?></a>
		<?php endif; ?>
	</div>
<?php
}
