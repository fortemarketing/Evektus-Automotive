<?php

/**
 * Site header fallback, used wherever no Beaver Themer header is assigned.
 *
 * The markup is shared by every header; which one renders is a `--{style}`
 * modifier class, chosen per entry under Theme Options > Header and resolved
 * by evek_get_header_style() in inc/theme-options.php.
 *
 * @package Evektus
 */

// Guarded so the site degrades to the site default header rather than
// fatalling if this file is ever newer than the functions.php that defines
// the resolver.
$evek_header_style = function_exists('evek_get_header_style') ? evek_get_header_style() : 'light';
?>
<!doctype html>
<html <?php language_attributes(); ?>>

<head>
	<meta charset="<?php bloginfo('charset'); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
	<?php wp_body_open(); ?>
	<a class="screen-reader-text" href="#main"><?php esc_html_e('Skip to content', 'evek'); ?></a>
	<div class="site-shell">
		<?php
		do_action('forte_before_header');
		forte_render_location(
			'header',
			static function () use ($evek_header_style): void {
		?>
			<header class="site-header site-header--<?php echo esc_attr($evek_header_style); ?>">

				<div class="site-container site-header__inner">
					<?php evek_site_branding($evek_header_style); ?>

					<div class="site-header__nav">
						<?php evek_main_menu(array('theme_location' => 'primary')); ?>

						<?php evek_header_button(); ?>
					</div>
				</div>

			</header>
		<?php
			}
		);
		do_action('forte_after_header');
		do_action('forte_before_content');
		?>