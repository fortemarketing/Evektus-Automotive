<?php

/**
 * @class ThemeButtonModule
 */

defined('ABSPATH') || exit;

class ThemeButtonModule extends FLBuilderModule
{

	/**
	 * @method __construct
	 */
	public function __construct()
	{
		parent::__construct(array(
			'name'            => __('Theme Button', 'fl-builder'),
			'description'     => __('A call to action button with custom click actions.', 'fl-builder'),
			'category'        => __('Theme Modules', 'fl-builder'),
			'group'           => __('Theme', 'fl-builder'),
			'dir'             => EVEK_DIR . '/fl-builder/modules/theme-button/',
			'url'             => EVEK_URI . '/fl-builder/modules/theme-button/',
			'slug'            => 'theme-button',
			'partial_refresh' => true,
			'include_wrapper' => false,
			'element_setting' => false,
		));

		// The button's base look is the theme's own .theme-button in
		// assets/css/theme.css, so no component stylesheet is enqueued here.
	}

	/**
	 * @method enqueue_scripts
	 */
	public function enqueue_scripts()
	{
		if (! $this->settings) {
			return;
		}

		$action = isset($this->settings->click_action) ? $this->settings->click_action : 'link';

		if ('lightbox' === $action) {
			$this->add_js('jquery-magnificpopup');
			$this->add_css('font-awesome-5');
			$this->add_css('jquery-magnificpopup');
		}

		// Lightbox / Copy Text behaviour lives in js/actions.js, a copy of the
		// plugin's shared assets/button/button.js. The Button action runs the
		// editor's own JavaScript and stays inline.
		if ('lightbox' === $action || 'copy_text' === $action) {
			$this->add_js('theme-button-actions', $this->url . 'js/actions.js', array('jquery'), (string) filemtime($this->dir . 'js/actions.js'), true);
		}
	}

	/**
	 * @method get_classname
	 */
	public function get_classname()
	{
		$classname = 'theme-button-wrap';

		if (! empty($this->settings->width)) {
			$classname .= ' theme-button-width-' . $this->settings->width;
		}
		if (! empty($this->settings->align)) {
			$classname .= ' theme-button-' . $this->settings->align;
		}
		if (! empty($this->settings->icon)) {
			$classname .= ' theme-button-has-icon';
		}

		return $classname;
	}

	/**
	 * Returns the tag to use for the button based on the click action
	 * @since 2.10
	 * @return string
	 */
	public function get_tag()
	{
		if (isset($this->settings->click_action) && 'link' !== $this->settings->click_action) {
			return 'button type="button"';
		}
		return 'a';
	}

	/**
	 * Returns just the element tag name (without attributes) for use in the
	 * closing tag.
	 * @return string
	 */
	public function get_tag_name()
	{
		return strtok($this->get_tag(), ' ');
	}

	/**
	 * Returns a link attribute or data attribute based on the click action
	 * @since 2.10
	 * @return string
	 */
	public function get_link()
	{
		if ('a' === $this->get_tag()) {
			return 'href="' . esc_url(do_shortcode($this->settings->link)) . '"';
		} elseif ('video' === $this->settings->lightbox_content_type) {
			return 'data-mfp-src="' . esc_url(do_shortcode($this->settings->lightbox_video_link)) . '"';
		}
	}

	public function get_label()
	{
		$label = isset($this->settings->text) ? trim((string) $this->settings->text) : '';

		return 'aria-label="' . esc_attr($label) . '"';
	}

	/**
	 * Returns the link target based on settings
	 * @since 2.10
	 * @return string
	 */
	public function get_target()
	{
		if ('a' === $this->get_tag()) {
			return 'target="' . esc_attr($this->settings->link_target) . '"' . $this->get_rel();
		}
		return '';
	}

	/**
	 * Returns the link rel based on settings
	 * @since 1.10.9
	 */
	public function get_rel()
	{
		$rel = array();
		if (isset($this->settings->link_target) && '_blank' == $this->settings->link_target) {
			$rel[] = 'noopener';
		}
		if (isset($this->settings->link_nofollow) && 'yes' == $this->settings->link_nofollow) {
			$rel[] = 'nofollow';
		}
		$rel = implode(' ', $rel);
		if ($rel) {
			$rel = ' rel="' . $rel . '" ';
		}
		return $rel;
	}
}

/**
 * Register the module and its form settings.
 */
FLBuilder::register_module('ThemeButtonModule', array(
	'general' => array(
		'title'    => __('General', 'fl-builder'),
		'sections' => array(
			'general'  => array(
				'title'  => '',
				'fields' => array(
					'text'                 => array(
						'type'        => 'text',
						'label'       => __('Text', 'fl-builder'),
						'default'     => __('Click Here', 'fl-builder'),
						'preview'     => array(
							'type' => 'refresh',
						),
						'connections' => array('string'),
					),
					'icon'                 => array(
						'type'        => 'icon',
						'label'       => __('Icon', 'fl-builder'),
						'show_remove' => true,
						'show'        => array(
							'fields' => array('icon_position', 'icon_animation'),
						),
						'preview'     => array(
							'type' => 'none',
						),
					),
					'icon_position'        => array(
						'type'    => 'select',
						'label'   => __('Icon Position', 'fl-builder'),
						'default' => 'before',
						'options' => array(
							'before' => __('Before Text', 'fl-builder'),
							'after'  => __('After Text', 'fl-builder'),
						),
						'preview' => array(
							'type' => 'none',
						),
					),
					'icon_animation'       => array(
						'type'    => 'select',
						'label'   => __('Icon Visibility', 'fl-builder'),
						'default' => 'disable',
						'options' => array(
							'disable' => __('Always Visible', 'fl-builder'),
							'enable'  => __('Fade In On Hover', 'fl-builder'),
						),
						'preview' => array(
							'type' => 'none',
						),
					),
					'click_action'         => array(
						'type'    => 'select',
						'label'   => __('Click Action', 'fl-builder'),
						'default' => 'link',
						'options' => array(
							'link'      => __('Link', 'fl-builder'),
							'button'    => __('Button', 'fl-builder'),
							'lightbox'  => __('Lightbox', 'fl-builder'),
							'copy_text' => __('Copy Text', 'fl-builder'),
						),
						'toggle'  => array(
							'link'      => array(
								'fields' => array('link'),
							),
							'button'    => array(
								'fields' => array('button'),
							),
							'lightbox'  => array(
								'sections' => array('lightbox'),
							),
							'copy_text' => array(
								'fields' => array('copy_text', 'copy_success_message'),
							),
						),
						'preview' => array(
							'type' => 'none',
						),
					),
					'link'                 => array(
						'type'          => 'link',
						'label'         => __('Link', 'fl-builder'),
						'placeholder'   => 'https://www.example.com',
						'show_target'   => true,
						'show_nofollow' => true,
						'show_download' => true,
						'preview'       => array(
							'type' => 'none',
						),
						'connections'   => array('url'),
					),
					'button'               => array(
						'type'    => 'code',
						'editor'  => 'javascript',
						'label'   => __('Button Code', 'fl-builder'),
						'rows'    => '18',
						'help'    => __('Implement custom button functionality using JavaScript. Your logic will be available to the button\'s click event.', 'fl-builder'),
						'preview' => array(
							'type' => 'none',
						),
					),
					'copy_text'            => array(
						'type'    => 'text',
						'label'   => __('Text to Copy', 'fl-builder'),
						'default' => '',
						'preview' => array(
							'type' => 'none',
						),
					),

					'copy_success_message' => array(
						'type'    => 'text',
						'label'   => __('Copy Success Message', 'fl-builder'),
						'default' => __('Copied!', 'fl-builder'),
						'preview' => array(
							'type' => 'none',
						),
					),
				),
			),
			'lightbox' => array(
				'title'  => __('Lightbox Content', 'fl-builder'),
				'fields' => array(
					'lightbox_content_type' => array(
						'type'    => 'select',
						'label'   => __('Content Type', 'fl-builder'),
						'default' => 'html',
						'options' => array(
							'html'  => __('HTML', 'fl-builder'),
							'video' => __('Video', 'fl-builder'),
						),
						'preview' => array(
							'type' => 'none',
						),
						'toggle'  => array(
							'html'  => array(
								'fields' => array('lightbox_content_html'),
							),
							'video' => array(
								'fields' => array('lightbox_video_link'),
							),
						),
					),
					'lightbox_content_html' => array(
						'type'        => 'code',
						'editor'      => 'html',
						'label'       => '',
						'rows'        => '19',
						'preview'     => array(
							'type' => 'none',
						),
						'connections' => array('string'),
					),
					'lightbox_video_link'   => array(
						'type'        => 'text',
						'label'       => __('Video Link', 'fl-builder'),
						'placeholder' => 'https://vimeo.com/122546221',
						'preview'     => array(
							'type' => 'none',
						),
						'connections' => array('custom_field'),
					),
				),
			),
		),
	),
	'style'   => array(
		'title'    => __('Style', 'fl-builder'),
		'sections' => array(
			'style'  => array(
				'title'  => '',
				'fields' => array(
					'width'        => array(
						'type'    => 'select',
						'label'   => __('Width', 'fl-builder'),
						'default' => 'auto',
						'options' => array(
							'auto'   => _x('Auto', 'Width.', 'fl-builder'),
							'full'   => __('Full Width', 'fl-builder'),
							'custom' => __('Custom', 'fl-builder'),
						),
						'toggle'  => array(
							'auto'   => array(
								'fields' => array('align'),
							),
							'full'   => array(),
							'custom' => array(
								'fields' => array('align', 'custom_width'),
							),
						),
					),
					'custom_width' => array(
						'type'       => 'unit',
						'label'      => __('Custom Width', 'fl-builder'),
						'default'    => '200',
						'responsive' => true,
						'slider'     => array(
							'px' => array(
								'min'  => 0,
								'max'  => 1000,
								'step' => 10,
							),
						),
						'units'      => array(
							'px',
							'vw',
							'%',
						),
						'preview'    => array(
							'type'     => 'css',
							'selector' => '.theme-button:is(a, button)',
							'property' => 'width',
						),
					),
					'align'        => array(
						'type'       => 'align',
						'label'      => __('Align', 'fl-builder'),
						'default'    => 'left',
						'responsive' => true,
						'preview'    => array(
							'type'     => 'css',
							'selector' => '{node}.theme-button-wrap, .theme-button-wrap',
							'property' => 'justify-content',
						),
					),
					'padding'      => array(
						'type'       => 'dimension',
						'label'      => __('Padding', 'fl-builder'),
						'responsive' => true,
						'slider'     => true,
						'units'      => array('px'),
						'preview'    => array(
							'type'     => 'css',
							'selector' => '.theme-button:is(a, button)',
							'property' => 'padding',
						),
					),
				),
			),
			'text'   => array(
				'title'  => __('Text', 'fl-builder'),
				'fields' => array(
					'text_color'       => array(
						'type'        => 'color',
						'connections' => array('color'),
						'label'       => __('Text Color', 'fl-builder'),
						'default'     => '',
						'show_reset'  => true,
						'show_alpha'  => true,
						'responsive'  => true,
						'preview'     => array(
							'type'      => 'css',
							'selector'  => '.theme-button:is(a, button), .theme-button:is(a, button) *',
							'property'  => 'color',
							'important' => true,
						),
					),
					'text_hover_color' => array(
						'type'        => 'color',
						'connections' => array('color'),
						'label'       => __('Text Hover Color', 'fl-builder'),
						'default'     => '',
						'show_reset'  => true,
						'show_alpha'  => true,
						'responsive'  => true,
						'preview'     => array(
							'type'      => 'css',
							'selector'  => '.theme-button:is(a, button):hover, .theme-button:is(a, button):hover *',
							'property'  => 'color',
							'important' => true,
						),
					),
					'typography'       => array(
						'type'       => 'typography',
						'label'      => __('Typography', 'fl-builder'),
						'responsive' => true,
						'preview'    => array(
							'type'     => 'css',
							'selector' => '.theme-button:is(a, button)',
						),
					),
				),
			),
			'icons'  => array(
				'title'  => __('Icon', 'fl-builder'),
				'fields' => array(
					'duo_color1' => array(
						'label'       => __('DuoTone Icon Primary Color', 'fl-builder'),
						'type'        => 'color',
						'connections' => array('color'),
						'default'     => '',
						'show_reset'  => true,
						'show_alpha'  => true,
						'preview'     => array(
							'type'      => 'css',
							'selector'  => '.theme-button-icon.fad:before',
							'property'  => 'color',
							'important' => true,
						),
					),
					'duo_color2' => array(
						'label'       => __('DuoTone Icon Secondary Color', 'fl-builder'),
						'type'        => 'color',
						'connections' => array('color'),
						'default'     => '',
						'show_reset'  => true,
						'show_alpha'  => true,
						'preview'     => array(
							'type'      => 'css',
							'selector'  => '.theme-button-icon.fad:after',
							'property'  => 'color',
							'important' => true,
						),
					),
				),
			),
			'colors' => array(
				'title'  => __('Background', 'fl-builder'),
				'fields' => array(
					'bg_color'          => array(
						'type'        => 'color',
						'connections' => array('color'),
						'label'       => __('Background Color', 'fl-builder'),
						'default'     => '',
						'show_reset'  => true,
						'show_alpha'  => true,
						'responsive'  => true,
						'preview'     => array(
							'type' => 'refresh',
						),
					),
					'bg_hover_color'    => array(
						'type'        => 'color',
						'connections' => array('color'),
						'label'       => __('Background Hover Color', 'fl-builder'),
						'default'     => '',
						'show_reset'  => true,
						'show_alpha'  => true,
						'responsive'  => true,
						'preview'     => array(
							'type' => 'none',
						),
					),
				),
			),
			'border' => array(
				'title'  => __('Border', 'fl-builder'),
				'fields' => array(
					'border'             => array(
						'type'       => 'border',
						'label'      => __('Border', 'fl-builder'),
						'responsive' => true,
						'preview'    => array(
							'type'      => 'css',
							'selector'  => '.theme-button:is(a, button)',
							'important' => true,
						),
					),
					'border_hover_color' => array(
						'type'        => 'color',
						'connections' => array('color'),
						'label'       => __('Border Hover Color', 'fl-builder'),
						'default'     => '',
						'show_reset'  => true,
						'show_alpha'  => true,
						'responsive'  => true,
						'preview'     => array(
							'type' => 'none',
						),
					),
				),
			),
		),
	),
));
