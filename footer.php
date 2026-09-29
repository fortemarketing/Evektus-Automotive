<?php

/**
 * Site footer fallback, used wherever no Beaver Themer footer is assigned.
 *
 * @package Evektus
 */

do_action('evek_after_content');
do_action('evek_before_footer');
evek_render_location(
	'footer',
	static function (): void {
?>
	<footer class="site-footer">
		<div class="site-container site-footer__inner">

			<?php if (has_nav_menu('footer')) : ?>
				<nav class="site-footer__nav" aria-label="<?php esc_attr_e('Footer Menu', 'evek'); ?>">
					<?php
					wp_nav_menu(
						array(
							'theme_location' => 'footer',
							'container'      => false,
							'menu_class'     => 'footer-menu',
							'depth'          => 1,
						)
					);
					?>
				</nav>
			<?php endif; ?>

			<div class="site-footer__meta">
				<p>&copy; <?php echo esc_html(wp_date('Y')); ?> <?php echo esc_html(get_bloginfo('name')); ?></p>
			</div>

		</div>
	</footer>
<?php
	}
);
do_action('evek_after_footer');
?>
</div>
<?php wp_footer(); ?>
</body>

</html>
