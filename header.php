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

					// The call to action on the right. Change the label or link
					// with the evek_header_cta filter; an empty url hides it.
					$evek_cta = (array) apply_filters(
						'evek_header_cta',
						array(
							'label' => __('Chat with us', 'evek'),
							'url'   => home_url('/contact-us/'),
						)
					);

					if (! empty($evek_cta['url']) && ! empty($evek_cta['label'])) :
					?>
						<a class="theme-button primary site-header__cta" href="<?php echo esc_url($evek_cta['url']); ?>">
							<span><?php echo esc_html($evek_cta['label']); ?></span>
							<svg class="site-header__cta-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
								<path d="M4 5h16v11H9l-5 4z" />
								<path d="M8.5 10.5h.01M12 10.5h.01M15.5 10.5h.01" stroke-width="2.5" />
							</svg>
						</a>
					<?php endif; ?>
				</div>
			</header>
		<?php
			}
		);
		do_action('evek_after_header');
		do_action('evek_before_content');
		?>
