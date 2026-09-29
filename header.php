<?php

/**
 * Site header fallback, used wherever no Beaver Themer header is assigned.
 *
 * @package Evektus
 */

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
		do_action('evek_before_header');
		evek_render_location(
			'header',
			static function (): void {
		?>
			<header class="site-header">
				<div class="site-container site-header__inner">
					<?php evek_site_branding(); ?>

					<?php
					// Keep these arguments in step with evek_render_main_menu_partial()
					// in inc/main-menu.php, or a Customizer refresh will drop them.
					evek_main_menu(array('theme_location' => 'primary'));
					?>
				</div>
			</header>
		<?php
			}
		);
		do_action('evek_after_header');
		do_action('evek_before_content');
		?>
