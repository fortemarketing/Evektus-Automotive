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
		// The business details in the first column. Change any of them with the
		// evek_footer_contact filter; an empty value hides that line, and an
		// empty url hides that social icon.
		$evek_contact = (array) apply_filters(
			'evek_footer_contact',
			array(
				'address' => array('12 Address Street', 'Suburb, State'),
				'phone'   => '(07) 123 456',
				'email'   => 'info@evektus.com.au',
				'social'  => array(
					'facebook'  => 'https://www.facebook.com/',
					'instagram' => 'https://www.instagram.com/',
				),
			)
		);

		$evek_social_labels = array(
			'facebook'  => __('Facebook', 'evek'),
			'instagram' => __('Instagram', 'evek'),
		);

		$evek_social = array_filter(
			isset($evek_contact['social']) ? (array) $evek_contact['social'] : array(),
			static function ($url, $network) use ($evek_social_labels): bool {
				return ! empty($url) && isset($evek_social_labels[$network]);
			},
			ARRAY_FILTER_USE_BOTH
		);
?>
	<footer class="site-footer">
		<div class="site-container site-footer__inner">

			<div class="site-footer__top">
				<address class="site-footer__contact">
					<?php if (! empty($evek_contact['address'])) : ?>
						<p><?php echo implode('<br>', array_map('esc_html', (array) $evek_contact['address'])); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Each line escaped above. ?></p>
					<?php endif; ?>

					<?php if (! empty($evek_contact['phone']) || ! empty($evek_contact['email'])) : ?>
						<p>
							<?php if (! empty($evek_contact['phone'])) : ?>
								<a href="tel:<?php echo esc_attr(preg_replace('/[^0-9+]/', '', $evek_contact['phone'])); ?>"><?php echo esc_html($evek_contact['phone']); ?></a><br>
							<?php endif; ?>
							<?php if (! empty($evek_contact['email'])) : ?>
								<a href="mailto:<?php echo esc_attr(antispambot($evek_contact['email'])); ?>"><?php echo esc_html(antispambot($evek_contact['email'])); ?></a>
							<?php endif; ?>
						</p>
					<?php endif; ?>

					<?php if (! empty($evek_social)) : ?>
						<ul class="site-footer__social">
							<?php foreach ($evek_social as $evek_network => $evek_url) : ?>
								<li>
									<a href="<?php echo esc_url($evek_url); ?>" target="_blank" rel="noopener" aria-label="<?php echo esc_attr($evek_social_labels[$evek_network]); ?>">
										<?php if ('facebook' === $evek_network) : ?>
											<svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" focusable="false">
												<path d="M12 2a10 10 0 0 0-1.56 19.88v-6.99H7.9V12h2.54V9.8c0-2.5 1.49-3.89 3.78-3.89 1.09 0 2.24.2 2.24.2v2.46h-1.26c-1.24 0-1.63.77-1.63 1.56V12h2.78l-.44 2.89h-2.34v6.99A10 10 0 0 0 12 2z" />
											</svg>
										<?php else : ?>
											<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true" focusable="false">
												<rect x="3" y="3" width="18" height="18" rx="5" />
												<circle cx="12" cy="12" r="4" />
												<circle cx="17.5" cy="6.5" r="0.5" fill="currentColor" />
											</svg>
										<?php endif; ?>
									</a>
								</li>
							<?php endforeach; ?>
						</ul>
					<?php endif; ?>
				</address>

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
			</div>

			<div class="site-footer__meta">
				<p>
					<?php echo esc_html(get_bloginfo('name')); ?>
					&copy; <?php echo esc_html(wp_date('Y')); ?>
					<?php
					$evek_privacy = get_the_privacy_policy_link();

					if ('' !== $evek_privacy) {
						echo '<span class="site-footer__sep" aria-hidden="true">|</span> ' . $evek_privacy; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Built and escaped by core.
					}
					?>
				</p>
				<p>
					<?php
					printf(
						/* translators: %s: link to the agency that built the site. */
						esc_html__('Website by %s', 'evek'),
						'<a href="https://fortemarketing.com.au/" target="_blank" rel="noopener">Forte Marketing</a>'
					);
					?>
				</p>
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
