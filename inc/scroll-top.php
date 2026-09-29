<?php

/**
 * Back to top control.
 *
 * Hooked to wp_footer rather than rendered inside footer.php, so it still
 * appears when a Beaver Themer layout replaces the footer fallback markup.
 *
 * @package Evektus
 */

defined('ABSPATH') || exit;

/** Render the back to top button. Shown by scroll-top.js once the page scrolls. */
function evek_scroll_top(): void
{
?>
	<button type="button" class="scroll-top" hidden>
		<span class="screen-reader-text"><?php esc_html_e('Back to top', 'evek'); ?></span>
		<svg class="scroll-top__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
			<path d="M12 19V5" />
			<path d="m5 12 7-7 7 7" />
		</svg>
	</button>
<?php
}
add_action('wp_footer', 'evek_scroll_top');
