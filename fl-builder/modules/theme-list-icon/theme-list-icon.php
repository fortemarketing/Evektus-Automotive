<?php

/**
 *  Theme List Icon module file
 *
 *  A list of short text items, each led by the same icon or photo, stacked or
 *  in a wrapping row.
 *
 *  A theme-owned copy of UABB List Icon from Ultimate Addons for Beaver Builder,
 *  renamed so it can sit beside the plugin's module without clashing. It depends
 *  on nothing but Beaver Builder core: the icon / photo is rendered here rather
 *  than through the plugin's Image Icon module.
 *
 *  @package Evektus
 */

defined('ABSPATH') || exit;

/**
 * Function that initializes Theme List Icon Module
 *
 * @class ThemeListIconModule
 */
class ThemeListIconModule extends FLBuilderModule
{

	/**
	 * Constructor function that constructs default values for the List Icon module.
	 *
	 * @method __construct
	 */
	public function __construct()
	{
		parent::__construct(
			array(
				'name'            => __('Theme List Icon', 'fl-builder'),
				'description'     => __('A list of items, each led by an icon or image.', 'fl-builder'),
				'category'        => __('Theme Modules', 'fl-builder'),
				'group'           => __('Theme', 'fl-builder'),
				'dir'             => EVEK_DIR . '/fl-builder/modules/theme-list-icon/',
				'url'             => EVEK_URI . '/fl-builder/modules/theme-list-icon/',
				'slug'            => 'theme-list-icon',
				'editor_export'   => false,
				'partial_refresh' => true,
			)
		);
	}

	/**
	 * Font Awesome is only needed when the list actually renders an icon.
	 * Loaded unconditionally while the builder is open so choosing one shows it
	 * without a hard reload.
	 *
	 * @method enqueue_scripts
	 */
	public function enqueue_scripts()
	{
		if (class_exists('FLBuilderModel') && FLBuilderModel::is_builder_active()) {
			$this->add_css('font-awesome-5');
			return;
		}

		if (isset($this->settings->image_type) && 'icon' === $this->settings->image_type && ! empty($this->settings->icon)) {
			$this->add_css('font-awesome-5');
		}
	}

	/**
	 * The list items worth rendering. An item whose title comes from a field
	 * connection that resolved to nothing is dropped outside the builder, as the
	 * plugin does, so an empty connection leaves no bare icon behind.
	 *
	 * @return array
	 */
	public function get_items()
	{
		$items = array();

		if (empty($this->settings->list_items) || ! is_array($this->settings->list_items)) {
			return $items;
		}

		foreach ($this->settings->list_items as $item) {
			if (! is_object($item)) {
				continue;
			}

			$title = isset($item->title) ? (string) $item->title : '';

			if ('' === trim($title) && ! empty($item->connections->title) && ! FLBuilderModel::is_builder_active()) {
				continue;
			}

			$items[] = $title;
		}

		return $items;
	}

	/**
	 * The list's direction: vertical (stacked) or horizontal (a wrapping row).
	 *
	 * @return string
	 */
	public function get_structure()
	{
		return (isset($this->settings->icon_struc_align) && 'horizontal' === $this->settings->icon_struc_align) ? 'horizontal' : 'vertical';
	}

	/**
	 * The list's alignment as a flex value: flex-start, center or flex-end.
	 *
	 * @return string
	 */
	public function get_align()
	{
		$align = isset($this->settings->align) ? $this->settings->align : 'flex-start';

		return in_array($align, array('flex-start', 'center', 'flex-end'), true) ? $align : 'flex-start';
	}

	/**
	 * Function that gets the root classname for the list.
	 *
	 * @method get_classname
	 * @return string
	 */
	public function get_classname()
	{
		$align = array(
			'flex-start' => 'left',
			'center'     => 'center',
			'flex-end'   => 'right',
		);

		return 'theme-list-icon theme-list-icon--' . $this->get_structure() . ' theme-list-icon--' . $align[$this->get_align()];
	}

	/**
	 * Resolves the photo setting to an attachment ID and a URL fallback.
	 *
	 * The photo field normally stores a bare attachment ID, but a field
	 * connection or an older saved layout can hand back an object or array
	 * instead, so accept all three. Mirrors Theme Info Box.
	 *
	 * @param mixed $photo Raw photo value from the settings.
	 * @return array Array with 'id' (int) and 'url' (string) keys.
	 */
	public function resolve_photo($photo)
	{
		$resolved = array(
			'id'  => 0,
			'url' => '',
		);

		if (is_numeric($photo)) {
			$resolved['id'] = (int) $photo;
			return $resolved;
		}

		if (is_object($photo)) {
			$photo = get_object_vars($photo);
		}

		if (is_array($photo)) {
			if (! empty($photo['id'])) {
				$resolved['id'] = (int) $photo['id'];
			}
			if (! empty($photo['src'])) {
				$resolved['url'] = $photo['src'];
			}
			if (! empty($photo['url'])) {
				$resolved['url'] = $photo['url'];
			}
		}

		return $resolved;
	}

	/**
	 * Returns the <img> markup for the photo media type, or '' when no usable
	 * photo is set. The same photo leads every item, so it is decorative and
	 * carries an empty alt.
	 *
	 * @return string
	 */
	public function get_photo_html()
	{
		$settings = $this->settings;
		$source   = isset($settings->photo_source) ? $settings->photo_source : 'library';

		if ('url' === $source) {
			if (empty($settings->photo_url)) {
				return '';
			}
			return '<img class="theme-list-icon-photo" src="' . esc_url($settings->photo_url) . '" alt="" loading="lazy" />';
		}

		if (empty($settings->photo)) {
			return '';
		}

		$resolved = $this->resolve_photo($settings->photo);
		$src      = ! empty($settings->photo_src) ? $settings->photo_src : $resolved['url'];

		// The Beaver Builder photo field stores the selected attachment size in
		// photo_src. Use it directly, matching Theme Info Box.
		if ('' !== $src) {
			return '<img class="theme-list-icon-photo" src="' . esc_url($src) . '" alt="" loading="lazy" />';
		}

		if (! empty($resolved['id'])) {
			return wp_get_attachment_image(
				$resolved['id'],
				'full',
				false,
				array(
					'class'   => 'theme-list-icon-photo',
					'alt'     => '',
					'loading' => 'lazy',
				)
			);
		}

		return '';
	}

	/**
	 * Returns the icon or photo markup that leads each item, or '' when there is
	 * none. Built once and repeated, since every item shares it.
	 *
	 * @return string
	 */
	public function get_media_html()
	{
		$settings = $this->settings;
		$type     = isset($settings->image_type) ? $settings->image_type : 'none';

		if ('icon' === $type) {

			if (empty($settings->icon)) {
				return '';
			}

			$style = isset($settings->icon_style) ? $settings->icon_style : 'simple';

			return '<span class="theme-list-icon-media"><span class="theme-list-icon-icon theme-list-icon-icon--' . esc_attr($style) . '"><i class="' . esc_attr($settings->icon) . '" aria-hidden="true"></i></span></span>';
		}

		if ('photo' === $type) {
			$photo = $this->get_photo_html();

			if ('' === $photo) {
				return '';
			}

			$style = isset($settings->image_style) ? $settings->image_style : 'simple';

			return '<span class="theme-list-icon-media"><span class="theme-list-icon-photo-wrap theme-list-icon-photo-wrap--' . esc_attr($style) . '">' . $photo . '</span></span>';
		}

		return '';
	}

	/**
	 * The HTML tag each item's text is wrapped in.
	 *
	 * @return string
	 */
	public function get_text_tag()
	{
		$tag = isset($this->settings->typography_tag_selection) ? $this->settings->typography_tag_selection : 'h3';

		return in_array($tag, array('h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'div', 'p', 'span'), true) ? $tag : 'div';
	}
}

/**
 * Register the module and its form settings.
 */
FLBuilder::register_module(
	'ThemeListIconModule',
	array(
		'general'    => array(
			'title'    => __('General', 'fl-builder'),
			'sections' => array(
				'general' => array(
					'title'  => '',
					'fields' => array(
						'list_items' => array(
							'type'         => 'form',
							'label'        => __('List Item', 'fl-builder'),
							'form'         => 'theme-list-icon_list_item_form',
							'preview_text' => 'title',
							'multiple'     => true,
						),
					),
				),
			),
		),
		'imageicon'  => array(
			'title'    => __('Image / Icon', 'fl-builder'),
			'sections' => array(
				'type_general' => array(
					'title'  => __('Image / Icon', 'fl-builder'),
					'fields' => array(
						'image_type' => array(
							'type'    => 'select',
							'label'   => __('Image Type', 'fl-builder'),
							'default' => 'none',
							'options' => array(
								'none'  => __('None', 'fl-builder'),
								'icon'  => __('Icon', 'fl-builder'),
								'photo' => __('Photo', 'fl-builder'),
							),
							'toggle'  => array(
								'icon'  => array(
									'fields'   => array('icon_text_spacing'),
									'sections' => array('icon_basic', 'icon_style', 'icon_colors'),
								),
								'photo' => array(
									'fields'   => array('icon_text_spacing'),
									'sections' => array('img_basic', 'img_style'),
								),
							),
						),
					),
				),
				'icon_basic'   => array(
					'title'  => __('Icon Basics', 'fl-builder'),
					'fields' => array(
						'icon'      => array(
							'type'        => 'icon',
							'label'       => __('Icon', 'fl-builder'),
							'show_remove' => true,
						),
						'icon_size' => array(
							'type'       => 'unit',
							'label'      => __('Size', 'fl-builder'),
							'default'    => '30',
							'units'      => array('px'),
							'responsive' => true,
							'slider'     => array(
								'min'  => 0,
								'max'  => 200,
								'step' => 1,
							),
							'preview'    => array(
								'type'     => 'css',
								'selector' => '.theme-list-icon-icon',
								'property' => 'font-size',
							),
						),
					),
				),
				'img_basic'    => array(
					'title'  => __('Image Basics', 'fl-builder'),
					'fields' => array(
						'photo_source' => array(
							'type'    => 'select',
							'label'   => __('Photo Source', 'fl-builder'),
							'default' => 'library',
							'options' => array(
								'library' => __('Media Library', 'fl-builder'),
								'url'     => __('URL', 'fl-builder'),
							),
							'toggle'  => array(
								'library' => array(
									'fields' => array('photo'),
								),
								'url'     => array(
									'fields' => array('photo_url'),
								),
							),
						),
						'photo'        => array(
							'type'        => 'photo',
							'label'       => __('Photo', 'fl-builder'),
							'show_remove' => true,
							'connections' => array('photo'),
						),
						'photo_url'    => array(
							'type'        => 'text',
							'label'       => __('Photo URL', 'fl-builder'),
							'placeholder' => 'https://www.example.com/my-photo.jpg',
							'connections' => array('url'),
						),
						'img_size'     => array(
							'type'       => 'unit',
							'label'      => __('Size', 'fl-builder'),
							'default'    => '50',
							'units'      => array('px'),
							'responsive' => true,
							'slider'     => array(
								'min'  => 0,
								'max'  => 500,
								'step' => 5,
							),
							'preview'    => array(
								'type'     => 'css',
								'selector' => '.theme-list-icon-photo',
								'property' => 'width',
							),
						),
					),
				),
				'icon_style'   => array(
					'title'  => __('Style', 'fl-builder'),
					'fields' => array(
						'icon_style'   => array(
							'type'    => 'select',
							'label'   => __('Icon Background Style', 'fl-builder'),
							'default' => 'simple',
							'help'    => __('Circle and Square default to a white icon on the theme primary color.', 'fl-builder'),
							'options' => array(
								'simple' => __('Simple', 'fl-builder'),
								'circle' => __('Circle Background', 'fl-builder'),
								'square' => __('Square Background', 'fl-builder'),
								'custom' => __('Design your own', 'fl-builder'),
							),
							'toggle'  => array(
								'circle' => array(
									'fields' => array('icon_bg_color', 'icon_bg_hover_color'),
								),
								'square' => array(
									'fields' => array('icon_bg_color', 'icon_bg_hover_color'),
								),
								'custom' => array(
									'fields' => array('icon_bg_size', 'icon_border', 'icon_bg_color', 'icon_bg_hover_color', 'icon_border_hover_color'),
								),
							),
						),
						'icon_bg_size' => array(
							'type'        => 'unit',
							'label'       => __('Background Size', 'fl-builder'),
							'help'        => __('Spacing between the icon and the edge of its background.', 'fl-builder'),
							'placeholder' => '10',
							'units'       => array('px'),
							'slider'      => array(
								'min'  => 0,
								'max'  => 100,
								'step' => 1,
							),
						),
						'icon_border'  => array(
							'type'       => 'border',
							'label'      => __('Border', 'fl-builder'),
							'responsive' => true,
							'preview'    => array(
								'type'     => 'css',
								'selector' => '.theme-list-icon-icon',
								'property' => 'border',
							),
						),
					),
				),
				'icon_colors'  => array(
					'title'  => __('Colors', 'fl-builder'),
					'fields' => array(
						'icon_color'              => array(
							'type'        => 'color',
							'label'       => __('Icon Color', 'fl-builder'),
							'connections' => array('color'),
							'show_reset'  => true,
							'show_alpha'  => true,
							'preview'     => array(
								'type'     => 'css',
								'selector' => '.theme-list-icon-icon',
								'property' => 'color',
							),
						),
						'icon_hover_color'        => array(
							'type'        => 'color',
							'label'       => __('Icon Hover Color', 'fl-builder'),
							'connections' => array('color'),
							'show_reset'  => true,
							'show_alpha'  => true,
							'preview'     => array(
								'type' => 'none',
							),
						),
						'icon_bg_color'           => array(
							'type'        => 'color',
							'label'       => __('Background Color', 'fl-builder'),
							'connections' => array('color'),
							'show_reset'  => true,
							'show_alpha'  => true,
							'preview'     => array(
								'type'     => 'css',
								'selector' => '.theme-list-icon-icon',
								'property' => 'background-color',
							),
						),
						'icon_bg_hover_color'     => array(
							'type'        => 'color',
							'label'       => __('Background Hover Color', 'fl-builder'),
							'connections' => array('color'),
							'show_reset'  => true,
							'show_alpha'  => true,
							'preview'     => array(
								'type' => 'none',
							),
						),
						'icon_border_hover_color' => array(
							'type'        => 'color',
							'label'       => __('Border Hover Color', 'fl-builder'),
							'connections' => array('color'),
							'show_reset'  => true,
							'show_alpha'  => true,
							'preview'     => array(
								'type' => 'none',
							),
						),
					),
				),
				'img_style'    => array(
					'title'  => __('Style', 'fl-builder'),
					'fields' => array(
						'image_style'            => array(
							'type'    => 'select',
							'label'   => __('Image Style', 'fl-builder'),
							'default' => 'simple',
							'help'    => __('Circle and Square crop the image to a 1:1 ratio.', 'fl-builder'),
							'options' => array(
								'simple' => __('Simple', 'fl-builder'),
								'circle' => __('Circle', 'fl-builder'),
								'square' => __('Square', 'fl-builder'),
								'custom' => __('Design your own', 'fl-builder'),
							),
							'toggle'  => array(
								'custom' => array(
									'fields' => array('img_bg_size', 'img_border', 'img_bg_color', 'img_bg_hover_color', 'img_border_hover_color'),
								),
							),
						),
						'img_bg_size'            => array(
							'type'        => 'unit',
							'label'       => __('Background Size', 'fl-builder'),
							'help'        => __('Spacing between the image and the edge of its background.', 'fl-builder'),
							'placeholder' => '0',
							'units'       => array('px'),
							'slider'      => array(
								'min'  => 0,
								'max'  => 100,
								'step' => 1,
							),
						),
						'img_border'             => array(
							'type'       => 'border',
							'label'      => __('Border', 'fl-builder'),
							'responsive' => true,
							'preview'    => array(
								'type'     => 'css',
								'selector' => '.theme-list-icon-photo-wrap',
								'property' => 'border',
							),
						),
						'img_bg_color'           => array(
							'type'        => 'color',
							'label'       => __('Background Color', 'fl-builder'),
							'connections' => array('color'),
							'show_reset'  => true,
							'show_alpha'  => true,
							'preview'     => array(
								'type'     => 'css',
								'selector' => '.theme-list-icon-photo-wrap',
								'property' => 'background-color',
							),
						),
						'img_bg_hover_color'     => array(
							'type'        => 'color',
							'label'       => __('Background Hover Color', 'fl-builder'),
							'connections' => array('color'),
							'show_reset'  => true,
							'show_alpha'  => true,
							'preview'     => array(
								'type' => 'none',
							),
						),
						'img_border_hover_color' => array(
							'type'        => 'color',
							'label'       => __('Border Hover Color', 'fl-builder'),
							'connections' => array('color'),
							'show_reset'  => true,
							'show_alpha'  => true,
							'preview'     => array(
								'type' => 'none',
							),
						),
					),
				),
			),
		),
		'style'      => array(
			'title'    => __('Style', 'fl-builder'),
			'sections' => array(
				'structure' => array(
					'title'  => __('Structure', 'fl-builder'),
					'fields' => array(
						'icon_struc_align'  => array(
							'type'    => 'select',
							'label'   => __('List Structure', 'fl-builder'),
							'default' => 'vertical',
							'options' => array(
								'horizontal' => __('Horizontal', 'fl-builder'),
								'vertical'   => __('Vertical', 'fl-builder'),
							),
						),
						'align'             => array(
							'type'    => 'select',
							'label'   => __('Alignment', 'fl-builder'),
							'default' => 'flex-start',
							'options' => array(
								'flex-start' => __('Left', 'fl-builder'),
								'center'     => __('Center', 'fl-builder'),
								'flex-end'   => __('Right', 'fl-builder'),
							),
							'help'    => __('Right alignment also moves the image / icon to the right of the text.', 'fl-builder'),
						),
						'spacing'           => array(
							'type'        => 'unit',
							'label'       => __('Space Between Two List Elements', 'fl-builder'),
							'placeholder' => '10',
							'units'       => array('px'),
							'responsive'  => true,
							'slider'      => array(
								'min'  => 0,
								'max'  => 100,
								'step' => 1,
							),
						),
						'icon_text_spacing' => array(
							'type'        => 'unit',
							'label'       => __('Space Between Icon & Text', 'fl-builder'),
							'placeholder' => '10',
							'units'       => array('px'),
							'responsive'  => true,
							'slider'      => array(
								'min'  => 0,
								'max'  => 100,
								'step' => 1,
							),
						),
					),
				),
			),
		),
		'typography' => array(
			'title'    => __('Typography', 'fl-builder'),
			'sections' => array(
				'typography' => array(
					'title'  => __('Text', 'fl-builder'),
					'fields' => array(
						'typography_tag_selection' => array(
							'type'    => 'select',
							'label'   => __('HTML Tag', 'fl-builder'),
							'default' => 'h3',
							'options' => array(
								'h1'   => 'h1',
								'h2'   => 'h2',
								'h3'   => 'h3',
								'h4'   => 'h4',
								'h5'   => 'h5',
								'h6'   => 'h6',
								'div'  => 'div',
								'p'    => 'p',
								'span' => 'span',
							),
						),
						'font_typo'                => array(
							'type'       => 'typography',
							'label'      => __('Typography', 'fl-builder'),
							'responsive' => true,
							'preview'    => array(
								'type'     => 'css',
								'selector' => '.theme-list-icon-heading',
							),
						),
						'typography_color'         => array(
							'type'        => 'color',
							'label'       => __('Color', 'fl-builder'),
							'connections' => array('color'),
							'show_reset'  => true,
							'show_alpha'  => true,
							'preview'     => array(
								'type'     => 'css',
								'selector' => '.theme-list-icon-heading',
								'property' => 'color',
							),
						),
					),
				),
			),
		),
	)
);

/**
 * Register a settings form to use in the "form" field type above.
 */
FLBuilder::register_settings_form(
	'theme-list-icon_list_item_form',
	array(
		'title' => __('Add List Item', 'fl-builder'),
		'tabs'  => array(
			'general' => array(
				'title'    => __('General', 'fl-builder'),
				'sections' => array(
					'general' => array(
						'title'  => '',
						'fields' => array(
							'title' => array(
								'type'        => 'text',
								'label'       => __('Title', 'fl-builder'),
								'connections' => array('string', 'html'),
							),
						),
					),
				),
			),
		),
	)
);
