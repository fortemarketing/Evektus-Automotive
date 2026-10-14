<?php

/**
 * Customizer settings.
 *
 * @package Evektus
 */

defined('ABSPATH') || exit;

/** Theme mod holding the attachment ID of the logo the Dark header uses. */
const EVEK_DARK_LOGO_MOD = 'evek_dark_logo';

/** Theme mods holding the label and link of the header's button. */
const EVEK_HEADER_BUTTON_LABEL_MOD = 'evek_header_button_label';
const EVEK_HEADER_BUTTON_URL_MOD   = 'evek_header_button_url';

/** Customizer section holding the header controls. */
const EVEK_HEADER_SECTION = 'evek_header';

/** The label the header button shows until one is saved. */
function evek_default_header_button_label(): string
{
	return __('Chat with us', 'evek');
}

/**
 * The button after the main menu in header.php.
 *
 * Clearing the label removes the button. Without a link it points at #, so
 * the button is visible to style before the destination page exists.
 */
function evek_header_button(): void
{
	$label = trim((string) get_theme_mod(EVEK_HEADER_BUTTON_LABEL_MOD, evek_default_header_button_label()));

	if ('' === $label) {
		return;
	}

	$url = (string) get_theme_mod(EVEK_HEADER_BUTTON_URL_MOD, '');

	printf(
		'<a class="fm-button outline no-icon site-header__button" href="%s">%s</a>',
		esc_url('' === $url ? '#' : $url),
		esc_html($label)
	);
}

/**
 * Add a second logo slot beside the Site Identity one.
 *
 * The Site Identity logo is drawn over the hero by the Light header, so it
 * needs to read against a dark image. The Dark header needs the full colour
 * mark instead, which is a different file rather than a colour change.
 */
function evek_customize_register(WP_Customize_Manager $wp_customize): void
{
	$wp_customize->add_setting(
		EVEK_DARK_LOGO_MOD,
		array(
			'sanitize_callback' => 'absint',
			'transport'         => 'refresh',
		)
	);

	$wp_customize->add_control(
		new WP_Customize_Media_Control(
			$wp_customize,
			EVEK_DARK_LOGO_MOD,
			array(
				'label'       => __('Dark header logo', 'evek'),
				'section'     => 'title_tagline',
				'mime_type'   => 'image',
				'priority'    => 9,
			)
		)
	);
}
add_action('customize_register', 'evek_customize_register');

/**
 * Header section, for the parts of the header that are set site-wide.
 *
 * The per-entry header choice lives in the Theme Options meta box instead
 * (see inc/theme-options.php), since it varies per page.
 */
function evek_customize_register_header(WP_Customize_Manager $wp_customize): void
{
	// With selective refresh the preview redraws just the button as it is
	// edited, and the partial below gives it a pencil shortcut.
	$transport = isset($wp_customize->selective_refresh) ? 'postMessage' : 'refresh';

	$wp_customize->add_section(
		EVEK_HEADER_SECTION,
		array(
			'title'    => __('Header', 'evek'),
			'priority' => 25,
		)
	);

	$wp_customize->add_setting(
		EVEK_HEADER_BUTTON_LABEL_MOD,
		array(
			'default'           => evek_default_header_button_label(),
			'sanitize_callback' => 'sanitize_text_field',
			'transport'         => $transport,
		)
	);

	$wp_customize->add_control(
		EVEK_HEADER_BUTTON_LABEL_MOD,
		array(
			'label'       => __('Button label', 'evek'),
			'description' => __('Leave empty to hide the button.', 'evek'),
			'section'     => EVEK_HEADER_SECTION,
			'type'        => 'text',
		)
	);

	$wp_customize->add_setting(
		EVEK_HEADER_BUTTON_URL_MOD,
		array(
			'default'           => '',
			'sanitize_callback' => 'esc_url_raw',
			'transport'         => $transport,
		)
	);

	$wp_customize->add_control(
		EVEK_HEADER_BUTTON_URL_MOD,
		array(
			'label'       => __('Button link', 'evek'),
			'section'     => EVEK_HEADER_SECTION,
			'type'        => 'url',
			'input_attrs' => array('placeholder' => 'https://'),
		)
	);

	// Clearing the label removes the button, leaving nothing for the partial
	// to redraw into; core's fallback_refresh then reloads the whole preview,
	// which brings the button back once a label is entered again.
	if (isset($wp_customize->selective_refresh)) {
		$wp_customize->selective_refresh->add_partial(
			'evek_header_button',
			array(
				'selector'            => '.site-header__button',
				'container_inclusive' => true,
				'settings'            => array(EVEK_HEADER_BUTTON_LABEL_MOD, EVEK_HEADER_BUTTON_URL_MOD),
				'render_callback'     => 'evek_render_header_button_partial',
			)
		);
	}
}
add_action('customize_register', 'evek_customize_register_header');

/** Re-render the header button for the partial above. */
function evek_render_header_button_partial(): string
{
	ob_start();
	evek_header_button();

	return (string) ob_get_clean();
}

/**
 * Give the primary menu a Customizer edit shortcut.
 *
 * The pencil buttons in the preview are selective refresh partials, and core
 * only registers those for menus it rendered itself through wp_nav_menu().
 * The main menu builds its own markup (see inc/main-menu.php), so it gets no
 * shortcut unless a partial is registered for it by hand.
 *
 * Runs at 20 so WP_Customize_Nav_Menus (11) has registered the nav menu
 * settings this hangs off.
 */
function evek_customize_register_main_menu_partial(WP_Customize_Manager $wp_customize): void
{
	// Selective refresh is what draws the shortcut; nothing to hang one on
	// without it.
	if (! isset($wp_customize->selective_refresh)) {
		return;
	}

	$locations = get_nav_menu_locations();
	$menu_id   = isset($locations['primary']) ? (int) $locations['primary'] : 0;
	$settings  = array();

	// The location setting goes first so the shortcut lands on View All
	// Locations - core focuses the primary setting, and that is the first
	// one listed.
	if ($wp_customize->get_setting('nav_menu_locations[primary]')) {
		$settings[] = 'nav_menu_locations[primary]';
	}

	// Listed too, but not first: it is what makes edits to the menu itself
	// refresh the partial.
	if ($menu_id && $wp_customize->get_setting('nav_menu[' . $menu_id . ']')) {
		$settings[] = 'nav_menu[' . $menu_id . ']';
	}

	// No menu assigned yet: the header is showing the placeholder tree, and
	// there is no setting for a shortcut to point at.
	if (empty($settings)) {
		return;
	}

	$wp_customize->selective_refresh->add_partial(
		'evek_main_menu',
		array(
			'selector'			  => '.site-header .main-menu',
			'container_inclusive' => true,
			'settings'			  => $settings,
			'primary_setting'	  => $settings[0],
			'render_callback'	  => 'evek_render_main_menu_partial',
		)
	);
}
add_action('customize_register', 'evek_customize_register_main_menu_partial', 20);

/**
 * Re-render the main menu for the partial above.
 *
 * Mirrors the call in header.php; keep the two in step or a partial refresh
 * will quietly drop whichever arguments the header passes.
 */
function evek_render_main_menu_partial(): string
{
	ob_start();
	evek_main_menu(array('theme_location' => 'primary'));

	return (string) ob_get_clean();
}

/** Theme mod holding the footer's acknowledgement of country. */
const EVEK_FOOTER_ACKNOWLEDGEMENT_MOD = 'evek_footer_acknowledgement';

/** Customizer section holding the footer controls. */
const EVEK_FOOTER_SECTION = 'evek_footer';

/**
 * The acknowledgement the footer shows until one is saved: none, so the row
 * stays hidden until text is entered in the Customizer.
 */
function evek_default_footer_acknowledgement(): string
{
	return '';
}

/**
 * The footer acknowledgement as HTML, or '' when it has been cleared.
 *
 * The textarea holds plain lines, so wpautop() turns blank lines into
 * paragraphs and single line breaks into <br>.
 */
function evek_footer_acknowledgement(): string
{
	$text = (string) get_theme_mod(EVEK_FOOTER_ACKNOWLEDGEMENT_MOD, evek_default_footer_acknowledgement());
	$text = trim(wp_kses_post($text));

	return '' === $text ? '' : wpautop($text);
}

/** Theme mods holding the footer's logo, badges, contact details and social links. */
const EVEK_FOOTER_LOGO_MOD        = 'evek_footer_logo';
const EVEK_FOOTER_BADGE_1_MOD     = 'evek_footer_badge_1';
const EVEK_FOOTER_BADGE_2_MOD     = 'evek_footer_badge_2';
const EVEK_FOOTER_PHONE_MOD       = 'evek_footer_phone';
const EVEK_FOOTER_FAX_MOD         = 'evek_footer_fax';
const EVEK_FOOTER_EMAIL_MOD       = 'evek_footer_email';
const EVEK_FOOTER_ADMIN_EMAIL_MOD = 'evek_footer_admin_email';
const EVEK_FOOTER_ADDRESS_MOD     = 'evek_footer_address';
const EVEK_FOOTER_HOURS_MOD       = 'evek_footer_hours';
const EVEK_FOOTER_YOUTUBE_MOD     = 'evek_footer_youtube';
const EVEK_FOOTER_FACEBOOK_MOD    = 'evek_footer_facebook';
const EVEK_FOOTER_INSTAGRAM_MOD   = 'evek_footer_instagram';

/** Theme mods holding the newsletter heading and the copyright line. */
const EVEK_FOOTER_NEWSLETTER_HEADING_MOD = 'evek_footer_newsletter_heading';
const EVEK_FOOTER_COPYRIGHT_MOD          = 'evek_footer_copyright';

/**
 * The Gravity Forms ID of the newsletter sign-up form. Fixed in code so it
 * cannot be lost in the Customizer. 0 until the form exists on this site, and
 * the footer leaves the newsletter out until it is set.
 */
const EVEK_FOOTER_NEWSLETTER_FORM_ID = 0;

/** The footer's text settings until others are saved. */
function evek_footer_defaults(): array
{
	return array(
		EVEK_FOOTER_PHONE_MOD              => '',
		EVEK_FOOTER_FAX_MOD                => '',
		EVEK_FOOTER_EMAIL_MOD              => '',
		EVEK_FOOTER_ADMIN_EMAIL_MOD        => '',
		EVEK_FOOTER_ADDRESS_MOD            => '',
		EVEK_FOOTER_HOURS_MOD              => '',
		EVEK_FOOTER_NEWSLETTER_HEADING_MOD => __('Sign up for updates', 'evek'),
		EVEK_FOOTER_COPYRIGHT_MOD          => __('Evektus Automotive. All Rights Reserved.', 'evek'),
	);
}

/** One footer theme mod as a trimmed string, falling back to its default. */
function evek_footer_mod(string $name): string
{
	$defaults = evek_footer_defaults();

	return trim((string) get_theme_mod($name, $defaults[$name] ?? ''));
}

/**
 * The footer logo: the Customizer's footer image, else the Site Identity logo,
 * else the site name as text until either is uploaded.
 */
function evek_footer_logo(): string
{
	$alt     = get_bloginfo('name');
	$logo_id = (int) get_theme_mod(EVEK_FOOTER_LOGO_MOD);

	if (! $logo_id || ! wp_attachment_is_image($logo_id)) {
		$logo_id = (int) get_theme_mod('custom_logo');
	}

	if ($logo_id && wp_attachment_is_image($logo_id)) {
		$image = wp_get_attachment_image($logo_id, 'full', false, array('class' => 'custom-logo', 'alt' => $alt));
	} else {
		$image = '<span class="site-branding__name">' . esc_html($alt) . '</span>';
	}

	return '<div class="site-branding">'
		. '<a class="site-branding__link" href="' . esc_url(home_url('/')) . '" rel="home">' . $image . '</a>'
		. '</div>';
}

/** The two badge images under the logo, skipping either slot left empty. */
function evek_footer_badges(): string
{
	$images = '';

	foreach (array(EVEK_FOOTER_BADGE_1_MOD, EVEK_FOOTER_BADGE_2_MOD) as $mod) {
		$id = (int) get_theme_mod($mod);

		// The alt text comes from the attachment, set in the Media Library.
		if ($id && wp_attachment_is_image($id)) {
			$images .= wp_get_attachment_image($id, 'medium', false, array('class' => 'site-footer__badge', 'loading' => 'lazy'));
		}
	}

	return '' === $images ? '' : '<div class="site-footer__badges">' . $images . '</div>';
}

/** One contact entry: the bold label, then its already escaped value. */
function evek_footer_contact_part(string $label, string $value): string
{
	return '<strong>' . esc_html($label) . ':</strong> ' . $value;
}

/** The contact lines, skipping any left empty. */
function evek_footer_contact(): string
{
	$items = '';

	// The visible number keeps its spacing; only a leading plus and the
	// digits survive into the tel: link. Fax shares the phone's line, as in
	// the design, and stays plain text since nobody dials a fax from a phone.
	$phone = evek_footer_mod(EVEK_FOOTER_PHONE_MOD);
	$tel   = (string) preg_replace('/(?!^\+)[^0-9]/', '', $phone);
	$fax   = evek_footer_mod(EVEK_FOOTER_FAX_MOD);
	$parts = array();

	if ('' !== $phone) {
		$parts[] = evek_footer_contact_part(
			__('Phone', 'evek'),
			'' === $tel ? esc_html($phone) : '<a href="' . esc_url('tel:' . $tel) . '">' . esc_html($phone) . '</a>'
		);
	}

	if ('' !== $fax) {
		$parts[] = evek_footer_contact_part(__('Fax', 'evek'), esc_html($fax));
	}

	if ($parts) {
		$items .= '<li>' . implode('&nbsp; ', $parts) . '</li>';
	}

	$emails = array(
		EVEK_FOOTER_EMAIL_MOD       => __('Email', 'evek'),
		EVEK_FOOTER_ADMIN_EMAIL_MOD => __('Admin', 'evek'),
	);

	foreach ($emails as $mod => $label) {
		$email = sanitize_email(evek_footer_mod($mod));

		if ('' !== $email) {
			$items .= '<li>' . evek_footer_contact_part($label, '<a href="' . esc_url('mailto:' . $email) . '">' . esc_html($email) . '</a>') . '</li>';
		}
	}

	$address = evek_footer_mod(EVEK_FOOTER_ADDRESS_MOD);

	// Google's documented Maps search URL, so the link follows whatever
	// address is entered rather than a pinned place ID.
	if ('' !== $address) {
		$maps   = 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode($address);
		$items .= '<li>' . evek_footer_contact_part(
			__('Address', 'evek'),
			'<a href="' . esc_url($maps) . '" target="_blank" rel="noopener">' . esc_html($address) . '</a>'
		) . '</li>';
	}

	$hours = evek_footer_mod(EVEK_FOOTER_HOURS_MOD);

	if ('' !== $hours) {
		$items .= '<li>' . evek_footer_contact_part(__('Opening Hours', 'evek'), esc_html($hours)) . '</li>';
	}

	return '' === $items ? '' : '<ul class="site-footer__contact">' . $items . '</ul>';
}

/** The social icons, each shown only once its URL is set. */
function evek_footer_social(): string
{
	$networks = array(
		EVEK_FOOTER_FACEBOOK_MOD  => array(
			'label' => __('Facebook', 'evek'),
			'path'  => 'M26.6667 12.9167C26.6667 5.78667 20.6933 0 13.3333 0C5.97333 0 0 5.78667 0 12.9167C0 19.1683 4.58667 24.3737 10.6667 25.575V16.7917H8V12.9167H10.6667V9.6875C10.6667 7.19458 12.76 5.16667 15.3333 5.16667H18.6667V9.04167H16C15.2667 9.04167 14.6667 9.62292 14.6667 10.3333V12.9167H18.6667V16.7917H14.6667V25.7688C21.4 25.1229 26.6667 19.6204 26.6667 12.9167Z',
		),
		EVEK_FOOTER_INSTAGRAM_MOD => array(
			'label' => __('Instagram', 'evek'),
			'path'  => 'M14.7048 0C16.2048 0.003875 16.9661 0.011625 17.6234 0.0297083L17.8821 0.03875C18.1808 0.0490833 18.4754 0.0619999 18.8314 0.0774999C20.2501 0.142083 21.2181 0.359083 22.0674 0.678125C22.9474 1.00621 23.6888 1.45054 24.4301 2.16742C25.1084 2.8129 25.633 3.59403 25.9674 4.45625C26.2968 5.27904 26.5208 6.21679 26.5874 7.59242C26.6034 7.936 26.6168 8.22146 26.6274 8.51208L26.6354 8.76267C26.6554 9.39817 26.6634 10.1357 26.6661 11.5888L26.6674 12.5524V14.2445C26.6707 15.1866 26.6604 16.1288 26.6368 17.0707L26.6288 17.3213C26.6181 17.6119 26.6048 17.8973 26.5888 18.2409C26.5221 19.6165 26.2954 20.553 25.9674 21.3771C25.633 22.2393 25.1084 23.0204 24.4301 23.6659C23.7638 24.323 22.9575 24.8312 22.0674 25.1552C21.2181 25.4743 20.2501 25.6913 18.8314 25.7558L17.8821 25.7946L17.6234 25.8023C16.9661 25.8204 16.2048 25.8295 14.7048 25.832L13.7101 25.8333H11.9648C10.9918 25.8367 10.0188 25.8268 9.04609 25.8036L8.78743 25.7959C8.47091 25.7843 8.15446 25.7709 7.83809 25.7558C6.41943 25.6913 5.45143 25.4743 4.60076 25.1552C3.7112 24.8311 2.90535 24.3228 2.23943 23.6659C1.56069 23.0206 1.03557 22.2394 0.700759 21.3771C0.371425 20.5543 0.147425 19.6165 0.0807583 18.2409L0.0407584 17.3213L0.0340919 17.0707C0.00951325 16.1288 -0.00159906 15.1867 0.000758396 14.2445V11.5888C-0.00293213 10.6467 0.00684669 9.70455 0.0300918 8.76267L0.0394251 8.51208C0.0500918 8.22146 0.0634251 7.936 0.0794251 7.59242C0.146092 6.21679 0.370092 5.28033 0.699425 4.45625C1.03502 3.59367 1.56106 2.81252 2.24076 2.16742C2.9063 1.51067 3.71168 1.00243 4.60076 0.678125C5.45143 0.359083 6.41809 0.142083 7.83809 0.0774999C8.19276 0.0619999 8.48876 0.0490833 8.78743 0.03875L9.04609 0.0309999C10.0183 0.00805058 10.9909 -0.00185315 11.9634 0.00129157L14.7048 0ZM13.3341 6.45833C11.566 6.45833 9.87029 7.13876 8.62005 8.34994C7.3698 9.56111 6.66743 11.2038 6.66743 12.9167C6.66743 14.6295 7.3698 16.2722 8.62005 17.4834C9.87029 18.6946 11.566 19.375 13.3341 19.375C15.1022 19.375 16.7979 18.6946 18.0481 17.4834C19.2984 16.2722 20.0008 14.6295 20.0008 12.9167C20.0008 11.2038 19.2984 9.56111 18.0481 8.34994C16.7979 7.13876 15.1022 6.45833 13.3341 6.45833ZM13.3341 9.04167C13.8594 9.04158 14.3795 9.14173 14.8649 9.33639C15.3502 9.53105 15.7912 9.81641 16.1627 10.1762C16.5342 10.5359 16.8289 10.9631 17.03 11.4332C17.2311 11.9033 17.3347 12.4071 17.3348 12.916C17.3348 13.4249 17.2315 13.9288 17.0305 14.399C16.8296 14.8691 16.535 15.2964 16.1637 15.6562C15.7923 16.0161 15.3514 16.3016 14.8661 16.4965C14.3808 16.6913 13.8607 16.7916 13.3354 16.7917C12.2746 16.7917 11.2571 16.3834 10.507 15.6567C9.75685 14.93 9.33543 13.9444 9.33543 12.9167C9.33543 11.889 9.75685 10.9033 10.507 10.1766C11.2571 9.44992 12.2746 9.04167 13.3354 9.04167M20.3354 4.52083C19.8934 4.52083 19.4695 4.69094 19.1569 4.99373C18.8444 5.29653 18.6688 5.7072 18.6688 6.13542C18.6688 6.56363 18.8444 6.97431 19.1569 7.2771C19.4695 7.57989 19.8934 7.75 20.3354 7.75C20.7775 7.75 21.2014 7.57989 21.5139 7.2771C21.8265 6.97431 22.0021 6.56363 22.0021 6.13542C22.0021 5.7072 21.8265 5.29653 21.5139 4.99373C21.2014 4.69094 20.7775 4.52083 20.3354 4.52083Z',
		),
		EVEK_FOOTER_YOUTUBE_MOD   => array(
			'label' => __('YouTube', 'evek'),
			'path'  => 'M4.5 3.542H22.167A4.5 4.5 0 0 1 26.667 8.042V17.792A4.5 4.5 0 0 1 22.167 22.292H4.5A4.5 4.5 0 0 1 0 17.792V8.042A4.5 4.5 0 0 1 4.5 3.542ZM10.667 8.917V16.917L17.333 12.917Z',
		),
	);

	$items = '';

	foreach ($networks as $mod => $network) {
		$url = evek_footer_mod($mod);

		if ('' === $url) {
			continue;
		}

		// A new tab, so the site stays open behind the social page. The label
		// says so, since a screen reader user cannot see where the tab went.
		/* translators: %s: social network name - "Facebook". */
		$label = sprintf(__('%s (opens in a new tab)', 'evek'), $network['label']);

		$items .= '<li><a href="' . esc_url($url) . '" target="_blank" rel="noopener noreferrer" aria-label="' . esc_attr($label) . '">'
			. '<svg viewBox="0 0 27 26" fill="currentColor" aria-hidden="true" focusable="false"><path d="' . esc_attr($network['path']) . '" /></svg>'
			. '</a></li>';
	}

	return '' === $items ? '' : '<ul class="site-footer__social">' . $items . '</ul>';
}

/** The footer's brand column: logo, badges, contact details and social icons. */
function evek_footer_brand(): string
{
	return '<div class="site-footer__brand">'
		. evek_footer_logo()
		. evek_footer_badges()
		. evek_footer_contact()
		. evek_footer_social()
		. '</div>';
}

/**
 * The newsletter heading and form, or '' until the form is available.
 *
 * The form only runs when Gravity Forms is active and the form exists:
 * without the plugin, do_shortcode() would print the raw [gravityform] tag
 * into the footer, and for an unknown ID Gravity Forms prints a "could not
 * locate your form" notice. The heading introduces the form, so it goes too.
 */
function evek_footer_newsletter(): string
{
	if (! EVEK_FOOTER_NEWSLETTER_FORM_ID || ! class_exists('GFAPI') || ! GFAPI::get_form(EVEK_FOOTER_NEWSLETTER_FORM_ID)) {
		return '';
	}

	$heading = evek_footer_mod(EVEK_FOOTER_NEWSLETTER_HEADING_MOD);
	$form    = do_shortcode('[gravityform id="' . (int) EVEK_FOOTER_NEWSLETTER_FORM_ID . '" title="false" description="false" ajax="true"]');

	return '<div class="site-footer__newsletter">'
		. ('' === $heading ? '' : '<h2 class="site-footer__newsletter-heading">' . esc_html($heading) . '</h2>')
		. ('' === $form ? '' : '<div class="site-footer__newsletter-form">' . $form . '</div>')
		. '</div>';
}

/** The copyright line: the symbol and current year, then the saved text. */
function evek_footer_copyright(): string
{
	$text = evek_footer_mod(EVEK_FOOTER_COPYRIGHT_MOD);

	return '' === $text ? '' : '&copy; ' . esc_html(wp_date('Y')) . ', ' . esc_html($text);
}

/** Footer section: everything in the footer except its menus. */
function evek_customize_register_footer(WP_Customize_Manager $wp_customize): void
{
	// With selective refresh the preview redraws just the part being edited.
	$transport = isset($wp_customize->selective_refresh) ? 'postMessage' : 'refresh';
	$defaults  = evek_footer_defaults();

	$wp_customize->add_section(
		EVEK_FOOTER_SECTION,
		array(
			'title'    => __('Footer', 'evek'),
			'priority' => 130,
		)
	);

	$images = array(
		EVEK_FOOTER_LOGO_MOD    => __('Footer logo', 'evek'),
		EVEK_FOOTER_BADGE_1_MOD => __('Badge 1', 'evek'),
		EVEK_FOOTER_BADGE_2_MOD => __('Badge 2', 'evek'),
	);

	// Controls are listed in the order their content sits in the footer.
	$priority = 1;

	foreach ($images as $mod => $label) {
		$wp_customize->add_setting(
			$mod,
			array(
				'sanitize_callback' => 'absint',
				'transport'         => $transport,
			)
		);

		$wp_customize->add_control(
			new WP_Customize_Media_Control(
				$wp_customize,
				$mod,
				array(
					'label'     => $label,
					'section'   => EVEK_FOOTER_SECTION,
					'mime_type' => 'image',
					'priority'  => $priority++,
				)
			)
		);
	}

	$fields = array(
		EVEK_FOOTER_PHONE_MOD       => array(__('Phone', 'evek'), 'text', 'sanitize_text_field'),
		EVEK_FOOTER_FAX_MOD         => array(__('Fax', 'evek'), 'text', 'sanitize_text_field'),
		EVEK_FOOTER_EMAIL_MOD       => array(__('Email', 'evek'), 'email', 'sanitize_email'),
		EVEK_FOOTER_ADMIN_EMAIL_MOD => array(__('Admin email', 'evek'), 'email', 'sanitize_email'),
		EVEK_FOOTER_ADDRESS_MOD     => array(__('Address', 'evek'), 'text', 'sanitize_text_field'),
		EVEK_FOOTER_HOURS_MOD       => array(__('Opening hours', 'evek'), 'text', 'sanitize_text_field'),
		EVEK_FOOTER_FACEBOOK_MOD    => array(__('Facebook link', 'evek'), 'url', 'esc_url_raw'),
		EVEK_FOOTER_INSTAGRAM_MOD   => array(__('Instagram link', 'evek'), 'url', 'esc_url_raw'),
		EVEK_FOOTER_YOUTUBE_MOD     => array(__('YouTube link', 'evek'), 'url', 'esc_url_raw'),
	);

	// The newsletter takes a full refresh rather than a partial: a form plugin
	// enqueues and initialises its scripts on page load, which a partial
	// swapping in new markup would skip.
	$newsletter_fields = array(
		EVEK_FOOTER_NEWSLETTER_HEADING_MOD => array(__('Newsletter heading', 'evek'), 'text', 'sanitize_text_field'),
	);

	$groups = array(
		array($fields, $transport),
		array($newsletter_fields, 'refresh'),
	);

	foreach ($groups as $group) {
		list($group_fields, $group_transport) = $group;

		foreach ($group_fields as $mod => $field) {
			list($label, $type, $sanitize) = $field;

			$wp_customize->add_setting(
				$mod,
				array(
					'default'           => $defaults[$mod] ?? '',
					'sanitize_callback' => $sanitize,
					'transport'         => $group_transport,
				)
			);

			$wp_customize->add_control(
				$mod,
				array(
					'label'       => $label,
					'section'     => EVEK_FOOTER_SECTION,
					'type'        => $type,
					'priority'    => $priority++,
					'input_attrs' => 'url' === $type ? array('placeholder' => 'https://') : array(),
				)
			);
		}
	}

	$wp_customize->add_setting(
		EVEK_FOOTER_ACKNOWLEDGEMENT_MOD,
		array(
			'default'           => evek_default_footer_acknowledgement(),
			'sanitize_callback' => 'wp_kses_post',
			'transport'         => $transport,
		)
	);

	$wp_customize->add_control(
		EVEK_FOOTER_ACKNOWLEDGEMENT_MOD,
		array(
			'label'       => __('Acknowledgement of country', 'evek'),
			'section'     => EVEK_FOOTER_SECTION,
			'type'        => 'textarea',
			'priority'    => $priority++,
			'input_attrs' => array('rows' => 12),
		)
	);

	$wp_customize->add_setting(
		EVEK_FOOTER_COPYRIGHT_MOD,
		array(
			'default'           => $defaults[EVEK_FOOTER_COPYRIGHT_MOD],
			'sanitize_callback' => 'sanitize_text_field',
			'transport'         => $transport,
		)
	);

	$wp_customize->add_control(
		EVEK_FOOTER_COPYRIGHT_MOD,
		array(
			'label'       => __('Copyright', 'evek'),
			'section'     => EVEK_FOOTER_SECTION,
			'type'        => 'textarea',
			'priority'    => $priority++,
			'input_attrs' => array('rows' => 3),
		)
	);

	// Redraws just the part being edited as it is typed, and gives it a
	// pencil shortcut in the preview.
	if (isset($wp_customize->selective_refresh)) {
		$wp_customize->selective_refresh->add_partial(
			EVEK_FOOTER_ACKNOWLEDGEMENT_MOD,
			array(
				'selector'            => '.site-footer__acknowledgement-text',
				'container_inclusive' => false,
				'render_callback'     => 'evek_footer_acknowledgement',
			)
		);

		$wp_customize->selective_refresh->add_partial(
			EVEK_FOOTER_COPYRIGHT_MOD,
			array(
				'selector'            => '.site-footer__copyright',
				'container_inclusive' => false,
				'render_callback'     => 'evek_footer_copyright',
			)
		);

		$wp_customize->selective_refresh->add_partial(
			'evek_footer_brand',
			array(
				'selector'            => '.site-footer__brand',
				'container_inclusive' => true,
				'settings'            => array_merge(array_keys($images), array_keys($fields)),
				'render_callback'     => 'evek_footer_brand',
			)
		);
	}
}
add_action('customize_register', 'evek_customize_register_footer');
