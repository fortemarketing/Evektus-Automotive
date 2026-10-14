<?php

/** @package Evektus */
do_action('forte_after_content');
do_action('forte_before_footer');
forte_render_location(
	'footer',
	static function (): void {
?>
	<footer class="site-footer">

		<div class="site-footer__main">
			<div class="site-container site-footer__main-inner">

				<?php // Edited in Appearance > Customize > Footer. Escaped as it is built.
				echo evek_footer_brand();
				?>

				<div class="site-footer__aside">
					<?php // Edited in Appearance > Customize > Footer. Escaped as it is built.
					echo evek_footer_newsletter();
					?>

					<nav class="site-footer__nav" aria-label="<?php esc_attr_e('Footer Menu', 'evek'); ?>">
						<div class="footer-menu">
							<?php
							// One column per location, headed by its menu's name
							// (see evek_footer_menu_heading()).
							foreach (array('footer-1', 'footer-2', 'footer-3') as $footer_location) :
								$footer_heading = evek_footer_menu_heading($footer_location);
							?>
								<div class="footer-menu__column">
									<?php if ('' !== $footer_heading) : ?>
										<h3 class="footer-menu__heading"><?php echo esc_html($footer_heading); ?></h3>
									<?php endif; ?>

									<?php
									wp_nav_menu(
										array(
											'theme_location' => $footer_location,
											'container'		 => false,
											'menu_class'	 => 'sub-menu',
											'depth'			 => 1,
											'fallback_cb'	 => 'evek_footer_menu_fallback',
										)
									);
									?>
								</div>
							<?php endforeach; ?>
						</div>
					</nav>
				</div>

			</div>
		</div>

		<div class="site-container site-footer__acknowledgement">
			<?php // Edited in Appearance > Customize > Footer. The div is printed even
			// when empty, so the Customizer preview has somewhere to put new text;
			// footer.css hides the row while it is empty, so nothing may sit
			// between the tags - not even whitespace - or :empty stops matching.
			?>
			<div class="site-footer__acknowledgement-text"><?php echo evek_footer_acknowledgement(); // Sanitised with wp_kses_post(). ?></div>
		</div>

		<div class="site-container site-footer__bottom">
			<?php // Edited in Appearance > Customize > Footer. Printed even when empty,
			// like the acknowledgement, so the preview can fill it in.
			?>
			<p class="site-footer__copyright"><?php echo evek_footer_copyright(); // Escaped as it is built. 
												?></p>

			<div class="site-footer__legal">
				<nav aria-label="<?php esc_attr_e('Legal Menu', 'evek'); ?>">
					<?php
					wp_nav_menu(
						array(
							'theme_location' => 'legal',
							'container'		 => false,
							'menu_class'	 => 'legal-menu',
							'depth'			 => 1,
							'fallback_cb'	 => 'evek_legal_menu_fallback',
						)
					);
					?>
				</nav>

				<p>Website by <a class="site-footer__credit" href="https://fortemarketing.com.au" target="_blank" rel="noopener noreferrer">Forte Marketing</a></p>
			</div>
		</div>

	</footer>
<?php
	}
);
do_action('forte_after_footer');
?>
</div>
<?php wp_footer(); ?>
</body>

</html>