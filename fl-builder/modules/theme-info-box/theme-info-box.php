<?php

/**
 *  Theme Info Box module file
 *
 *  A heading and snippet of text with an optional prefix, separator, icon or
 *  photo, and a text / button / whole-box call to action.
 *
 *  A theme-owned copy of FM Info Box from Forte Marketing Modules, renamed so
 *  it can sit beside the plugin's module without clashing. It depends on
 *  nothing but Beaver Builder core: the button is the theme's own .theme-button
 *  from assets/css/theme.css rather than the plugin's shared button component.
 *
 *  @package Evektus
 */

defined('ABSPATH') || exit;

/**
 * Function that initializes Theme Info Box Module
 *
 * @class ThemeInfoBoxModule
 */
class ThemeInfoBoxModule extends FLBuilderModule
{

	/**
	 * Constructor function that constructs default values for the Info Box module.
	 *
	 * @method __construct
	 */
	public function __construct()
	{
		parent::__construct(
			array(
				'name'            => __('Theme Info Box', 'fl-builder'),
				'description'     => __('A heading and snippet of text with an optional link, icon and image.', 'fl-builder'),
				'category'        => __('Theme Modules', 'fl-builder'),
				'group'           => __('Theme', 'fl-builder'),
				'dir'             => EVEK_DIR . '/fl-builder/modules/theme-info-box/',
				'url'             => EVEK_URI . '/fl-builder/modules/theme-info-box/',
				'slug'            => 'theme-info-box',
				'partial_refresh' => true,
			)
		);
	}

	/**
	 * Font Awesome is only needed when this box actually renders a glyph: the
	 * media slot set to Icon, or an icon on the button. Loaded unconditionally
	 * while the builder is open so choosing one shows it without a hard reload.
	 *
	 * @method enqueue_scripts
	 */
	public function enqueue_scripts()
	{
		if (class_exists('FLBuilderModel') && FLBuilderModel::is_builder_active()) {
			$this->add_css('font-awesome-5');
			return;
		}

		$media_icon = (isset($this->settings->image_type) && 'icon' === $this->settings->image_type)
			&& ! empty($this->settings->icon);

		if ($media_icon || ! empty($this->settings->btn_icon)) {
			$this->add_css('font-awesome-5');
		}
	}

	/**
	 * Whether the box shows an icon or a photo at all.
	 *
	 * @return bool
	 */
	public function has_media()
	{
		return isset($this->settings->image_type) && 'none' !== $this->settings->image_type;
	}

	/**
	 * The configured media position, or '' when there is no media.
	 *
	 * @return string
	 */
	public function get_media_position()
	{
		if (! $this->has_media()) {
			return '';
		}
		return isset($this->settings->img_icon_position) ? $this->settings->img_icon_position : 'above-title';
	}

	/**
	 * Whether the media sits inline with the title.
	 *
	 * @return bool
	 */
	public function is_title_media()
	{
		return in_array($this->get_media_position(), array('left-title', 'right-title'), true);
	}

	/**
	 * Function that gets the root classname for the info box.
	 *
	 * @method get_classname
	 * @return string
	 */
	public function get_classname()
	{
		$settings  = $this->settings;
		$position  = $this->get_media_position();
		$classname = 'theme-info-box';

		$classname .= ' theme-info-box--' . ('' === $position ? 'no-media' : $position);

		// Icon vertical alignment for media beside the heading or content.
		if (isset($settings->align_items) && 'top' === $settings->align_items) {
			$classname .= ' theme-info-box--media-top';
		}

		return $classname;
	}

	/**
	 * Returns the rel attribute (including the leading space) for a link, or ''
	 * when neither noopener nor nofollow applies.
	 *
	 * @param string $target   The link target.
	 * @param string $nofollow Whether the link is nofollow ('yes').
	 * @return string
	 */
	public function get_rel($target = '', $nofollow = '')
	{
		$rel = array();

		if ('_blank' === $target) {
			$rel[] = 'noopener';
		}
		if ('yes' === $nofollow || '1' === (string) $nofollow) {
			$rel[] = 'nofollow';
		}

		return empty($rel) ? '' : ' rel="' . esc_attr(implode(' ', $rel)) . '"';
	}

	/**
	 * Renders the overlay anchor used by the "Complete Box" call to action.
	 *
	 * @return void
	 */
	public function render_box_link()
	{
		$settings = $this->settings;

		if (! isset($settings->cta_type) || 'box' !== $settings->cta_type || empty($settings->link)) {
			return;
		}

		$target   = isset($settings->link_target) ? $settings->link_target : '_self';
		$nofollow = isset($settings->link_nofollow) ? $settings->link_nofollow : '';
		$label    = (isset($settings->title) && '' !== $settings->title) ? wp_strip_all_tags($settings->title) : $settings->link;
?>
		<a class="theme-info-box-box-link" href="<?php echo esc_url($settings->link); ?>" target="<?php echo esc_attr($target); ?>" <?php echo $this->get_rel($target, $nofollow); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in get_rel(). 
																																	?> aria-label="<?php echo esc_attr($label); ?>"></a>
		<?php
	}

	/**
	 * Resolves the photo setting to an attachment ID and a URL fallback.
	 *
	 * The photo field normally stores a bare attachment ID, but a field
	 * connection or an older saved layout can hand back an object or array
	 * instead, so accept all three. Mirrors the plugin's FM Timeline item image.
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
	 * photo is set.
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
			return '<img class="theme-info-box-photo" src="' . esc_url($settings->photo_url) . '" alt="" loading="lazy" />';
		}

		if (empty($settings->photo)) {
			return '';
		}

		$resolved = $this->resolve_photo($settings->photo);
		$src      = ! empty($settings->photo_src) ? $settings->photo_src : $resolved['url'];

		// The Beaver Builder photo field stores the selected attachment size in
		// photo_src. Use it directly, matching the plugin's FM Info Box.
		if ('' !== $src) {
			$alt = ! empty($resolved['id']) ? get_post_meta($resolved['id'], '_wp_attachment_image_alt', true) : '';

			return '<img class="theme-info-box-photo" src="' . esc_url($src) . '" alt="' . esc_attr($alt) . '" loading="lazy" />';
		}

		if (! empty($resolved['id'])) {
			$html = wp_get_attachment_image(
				$resolved['id'],
				'full',
				false,
				array(
					'class'   => 'theme-info-box-photo',
					'loading' => 'lazy',
				)
			);

			if ('' !== $html) {
				return $html;
			}
		}

		return '';
	}

	/**
	 * Renders the icon or photo when it belongs at the given position.
	 *
	 * @param string $position One of above-title, below-title, left-title,
	 *                         right-title, left or right.
	 * @return void
	 */
	public function render_media($position)
	{
		$settings = $this->settings;

		if ($position !== $this->get_media_position()) {
			return;
		}

		if ('icon' === $settings->image_type) {

			if (empty($settings->icon)) {
				return;
			}

			$style = isset($settings->icon_style) ? $settings->icon_style : 'simple';
		?>
			<div class="theme-info-box-media">
				<span class="theme-info-box-icon theme-info-box-icon--<?php echo esc_attr($style); ?>">
					<i class="<?php echo esc_attr($settings->icon); ?>" aria-hidden="true"></i>
				</span>
			</div>
		<?php
			return;
		}

		$photo = $this->get_photo_html();

		if ('' === $photo) {
			return;
		}

		$style = isset($settings->image_style) ? $settings->image_style : 'simple';
		?>
		<div class="theme-info-box-media">
			<span class="theme-info-box-photo-wrap theme-info-box-photo-wrap--<?php echo esc_attr($style); ?>">
				<?php echo $photo; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Markup built by wp_get_attachment_image() or escaped above. 
				?>
			</span>
		</div>
	<?php
	}

	/**
	 * Renders the title prefix and title, with the media inline beside them when
	 * the media position is left / right of the heading.
	 *
	 * @method render_title
	 * @return void
	 */
	public function render_title()
	{
		$settings   = $this->settings;
		$has_prefix = isset($settings->heading_prefix) && '' !== $settings->heading_prefix;
		$has_title  = isset($settings->title) && '' !== $settings->title;
		$inline     = $this->is_title_media();

		if (! $has_prefix && ! $has_title && ! $inline) {
			return;
		}

		if ($inline) {
			echo '<div class="theme-info-box-title-row">';
		}

		$this->render_media('left-title');

		echo '<div class="theme-info-box-title-wrap">';

		if ($has_prefix) {
			$prefix_tag = isset($settings->prefix_tag) ? $settings->prefix_tag : 'h3';
			echo '<' . esc_attr($prefix_tag) . ' class="theme-info-box-prefix">' . wp_kses_post($settings->heading_prefix) . '</' . esc_attr($prefix_tag) . '>';
		}

		if ($has_title) {
			$title_tag = isset($settings->title_tag) ? $settings->title_tag : 'h2';
			echo '<' . esc_attr($title_tag) . ' class="theme-info-box-title">' . wp_kses_post($settings->title) . '</' . esc_attr($title_tag) . '>';
		}

		echo '</div>';

		$this->render_media('right-title');

		if ($inline) {
			echo '</div>';
		}
	}

	/**
	 * Renders the description text.
	 *
	 * @method render_text
	 * @return void
	 */
	public function render_text()
	{
		global $wp_embed;

		if (! isset($this->settings->text) || '' === $this->settings->text) {
			return;
		}

		echo '<div class="theme-info-box-text">' . wpautop($wp_embed->autoembed($this->settings->text)) . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Editor field content, run through wpautop() as the core Text Editor module does.
	}

	/**
	 * Renders the separator rule between the heading and the description.
	 *
	 * @method render_separator
	 * @return void
	 */
	public function render_separator()
	{

		if (! isset($this->settings->show_separator) || 'yes' !== $this->settings->show_separator) {
			return;
		}
	?>
		<div class="theme-info-box-separator-wrap">
			<span class="theme-info-box-separator"></span>
		</div>
	<?php
	}

	/**
	 * Renders the text call to action.
	 *
	 * @method render_link
	 * @return void
	 */
	public function render_link()
	{
		$settings   = $this->settings;
		$link_label = isset($settings->cta_text) ? trim(wp_strip_all_tags((string) $settings->cta_text)) : '';

		if (! isset($settings->cta_type) || 'link' !== $settings->cta_type || '' === $link_label || empty($settings->link)) {
			return;
		}

		$target   = isset($settings->link_target) ? $settings->link_target : '_self';
		$nofollow = isset($settings->link_nofollow) ? $settings->link_nofollow : '';
	?>
		<a class="theme-info-box-cta-link" href="<?php echo esc_url($settings->link); ?>" target="<?php echo esc_attr($target); ?>" <?php echo $this->get_rel($target, $nofollow); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in get_rel(). 
																																	?> aria-label="<?php echo esc_attr($link_label); ?>"><?php echo wp_kses_post($settings->cta_text); ?></a>
	<?php
	}

	/**
	 * Renders the button call to action.
	 *
	 * Rendered here rather than delegating to another module so the info box
	 * carries no dependency on any button module.
	 *
	 * @method render_button
	 * @return void
	 */
	public function render_button()
	{
		$settings    = $this->settings;
		$button_text = isset($settings->btn_text) ? trim((string) $settings->btn_text) : '';

		if (! isset($settings->cta_type) || 'button' !== $settings->cta_type || '' === $button_text) {
			return;
		}

		$link     = isset($settings->link) ? $settings->link : '';
		$target   = isset($settings->link_target) ? $settings->link_target : '_self';
		$nofollow = isset($settings->link_nofollow) ? $settings->link_nofollow : '';
		$icon     = isset($settings->btn_icon) ? $settings->btn_icon : '';
		$icon_pos = isset($settings->btn_icon_position) ? $settings->btn_icon_position : 'after';
		$width    = isset($settings->btn_width) ? $settings->btn_width : 'auto';
		$style    = isset($settings->btn_style) ? $settings->btn_style : '';

		// One of the style variants theme.css defines; anything else falls back
		// to primary.
		if (! in_array($style, array('primary', 'secondary', 'tertiary'), true)) {
			$style = 'primary';
		}
	?>
		<div class="theme-info-box-button-wrap theme-info-box-button-width-<?php echo esc_attr($width); ?>">
			<a class="theme-button <?php echo esc_attr($style); ?> theme-info-box-button" href="<?php echo esc_url($link); ?>" target="<?php echo esc_attr($target); ?>" <?php echo $this->get_rel($target, $nofollow); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in get_rel(). 
																																	?> aria-label="<?php echo esc_attr($button_text); ?>">
				<?php if ('' !== $icon && 'before' === $icon_pos) : ?>
					<span class="theme-info-box-button-icon <?php echo esc_attr($icon); ?>" aria-hidden="true"></span>
				<?php endif; ?>
				<span class="theme-info-box-button-text"><?php echo esc_html($button_text); ?></span>
				<?php if ('' !== $icon && 'after' === $icon_pos) : ?>
					<span class="theme-info-box-button-icon <?php echo esc_attr($icon); ?>" aria-hidden="true"></span>
				<?php endif; ?>
			</a>
		</div>
<?php
	}
}

/**
 * Register the module and its form settings.
 */
FLBuilder::register_module(
	'ThemeInfoBoxModule',
	array(
		'general'    => array(
			'title'    => __('Content', 'fl-builder'),
			'sections' => array(
				'title'     => array(
					'title'  => __('Title', 'fl-builder'),
					'fields' => array(
						'heading_prefix' => array(
							'type'        => 'text',
							'label'       => __('Prefix', 'fl-builder'),
							'connections' => array('string', 'html'),
							'preview'     => array(
								'type'     => 'text',
								'selector' => '.theme-info-box-prefix',
							),
						),
						'title'          => array(
							'type'        => 'text',
							'label'       => __('Title', 'fl-builder'),
							'connections' => array('string', 'html'),
							'preview'     => array(
								'type'     => 'text',
								'selector' => '.theme-info-box-title',
							),
						),
					),
				),
				'text'      => array(
					'title'  => __('Description', 'fl-builder'),
					'fields' => array(
						'text' => array(
							'type'          => 'editor',
							'label'         => '',
							'media_buttons' => false,
							'rows'          => 6,
							'connections'   => array('string', 'html'),
							'preview'       => array(
								'type'     => 'text',
								'selector' => '.theme-info-box-text',
							),
						),
					),
				),
				'separator' => array(
					'title'     => __('Separator', 'fl-builder'),
					'collapsed' => true,
					'fields'    => array(
						'show_separator'           => array(
							'type'    => 'select',
							'label'   => __('Separator', 'fl-builder'),
							'default' => 'no',
							'options' => array(
								'no'  => __('Hide', 'fl-builder'),
								'yes' => __('Show', 'fl-builder'),
							),
							'toggle'  => array(
								'yes' => array(
									'fields' => array('separator_style', 'separator_color', 'separator_height', 'separator_width', 'separator_align'),
								),
							),
						),
						'separator_style'          => array(
							'type'    => 'select',
							'label'   => __('Style', 'fl-builder'),
							'default' => 'solid',
							'help'    => __('Double borders must have a thickness of at least 3px to render properly.', 'fl-builder'),
							'options' => array(
								'solid'  => __('Solid', 'fl-builder'),
								'dashed' => __('Dashed', 'fl-builder'),
								'dotted' => __('Dotted', 'fl-builder'),
								'double' => __('Double', 'fl-builder'),
							),
						),
						'separator_color'          => array(
							'type'        => 'color',
							'label'       => __('Color', 'fl-builder'),
							'default'     => 'e0e0e0',
							'connections' => array('color'),
							'show_reset'  => true,
							'show_alpha'  => true,
						),
						'separator_height'         => array(
							'type'        => 'unit',
							'label'       => __('Thickness', 'fl-builder'),
							'placeholder' => '1',
							'units'       => array('px'),
							'slider'      => array(
								'min'  => 0,
								'max'  => 20,
								'step' => 1,
							),
						),
						'separator_width'          => array(
							'type'        => 'unit',
							'label'       => __('Width', 'fl-builder'),
							'placeholder' => '100',
							'units'       => array('%'),
							'slider'      => array(
								'min'  => 0,
								'max'  => 100,
								'step' => 5,
							),
						),
						'separator_align'          => array(
							'type'       => 'align',
							'label'      => __('Alignment', 'fl-builder'),
							'default'    => 'left',
							'responsive' => true,
						),
					),
				),
			),
		),
		'imageicon'  => array(
			'title'    => __('Image / Icon', 'fl-builder'),
			'sections' => array(
				'media_type'   => array(
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
									'sections' => array('icon_basic', 'icon_style', 'icon_colors'),
									'fields'   => array('img_icon_position', 'icon_spacing'),
								),
								'photo' => array(
									'sections' => array('photo_basic', 'photo_style'),
									'fields'   => array('img_icon_position', 'icon_spacing'),
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
							'default'     => 'far fa-smile',
							'show_remove' => true,
						),
						'icon_size' => array(
							'type'        => 'unit',
							'label'       => __('Size', 'fl-builder'),
							'default'     => '30',
							'units'       => array('px'),
							'responsive'  => true,
							'slider'      => array(
								'min'  => 0,
								'max'  => 200,
								'step' => 1,
							),
							'preview'     => array(
								'type'     => 'css',
								'selector' => '.theme-info-box-icon',
								'property' => 'font-size',
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
							'options' => array(
								'simple' => __('Simple', 'fl-builder'),
								'circle' => __('Circle Background', 'fl-builder'),
								'square' => __('Square Background', 'fl-builder'),
								'custom' => __('Design your own', 'fl-builder'),
							),
							'toggle'  => array(
								'circle' => array(
									'fields' => array('icon_bg_size'),
								),
								'square' => array(
									'fields' => array('icon_bg_size'),
								),
								'custom' => array(
									'fields' => array('icon_bg_size', 'icon_border'),
								),
							),
						),
						'icon_bg_size' => array(
							'type'        => 'unit',
							'label'       => __('Background Size', 'fl-builder'),
							'help'        => __('Spacing between the icon and the edge of its background.', 'fl-builder'),
							'placeholder' => '30',
							'units'       => array('px'),
							'slider'      => array(
								'min'  => 0,
								'max'  => 200,
								'step' => 1,
							),
						),
						'icon_border'  => array(
							'type'       => 'border',
							'label'      => __('Border', 'fl-builder'),
							'responsive' => true,
							'preview'    => array(
								'type'     => 'css',
								'selector' => '.theme-info-box-icon',
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
								'selector' => '.theme-info-box-icon',
								'property' => 'color',
							),
						),
						'icon_hover_color'        => array(
							'type'        => 'color',
							'label'       => __('Icon Hover Color', 'fl-builder'),
							'connections' => array('color'),
							'show_reset'  => true,
							'show_alpha'  => true,
							'help'        => __('Applied while the whole info box is hovered.', 'fl-builder'),
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
				'photo_basic'  => array(
					'title'  => __('Image Basics', 'fl-builder'),
					'fields' => array(
						'photo_source'   => array(
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
						'photo'          => array(
							'type'        => 'photo',
							'label'       => __('Photo', 'fl-builder'),
							'show_remove' => true,
							'connections' => array('photo'),
						),
						'photo_url'      => array(
							'type'        => 'text',
							'label'       => __('Photo URL', 'fl-builder'),
							'placeholder' => 'https://www.example.com/my-photo.jpg',
							'connections' => array('url'),
						),
						'img_size'       => array(
							'type'        => 'unit',
							'label'       => __('Size', 'fl-builder'),
							'placeholder' => __('Auto', 'fl-builder'),
							'units'       => array('px'),
							'responsive'  => true,
							'slider'      => array(
								'px' => array(
									'min'  => 0,
									'max'  => 1000,
									'step' => 10,
								),
							),
							'preview'     => array(
								'type'     => 'css',
								'selector' => '.theme-info-box-photo',
								'property' => 'width',
							),
						),
					),
				),
				'photo_style'  => array(
					'title'  => __('Style', 'fl-builder'),
					'fields' => array(
						'image_style' => array(
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
									'fields' => array('img_bg_size', 'img_border'),
								),
							),
						),
						'img_bg_size' => array(
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
						'img_border'  => array(
							'type'       => 'border',
							'label'      => __('Border', 'fl-builder'),
							'responsive' => true,
							'preview'    => array(
								'type'     => 'css',
								'selector' => '.theme-info-box-photo-wrap',
								'property' => 'border',
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
						'img_icon_position' => array(
							'type'    => 'select',
							'label'   => __('Image / Icon Position', 'fl-builder'),
							'default' => 'above-title',
							'options' => array(
								'above-title' => __('Above Heading', 'fl-builder'),
								'below-title' => __('Below Heading', 'fl-builder'),
								'left-title'  => __('Left of Heading', 'fl-builder'),
								'right-title' => __('Right of Heading', 'fl-builder'),
								'left'        => __('Left of Text and Heading', 'fl-builder'),
								'right'       => __('Right of Text and Heading', 'fl-builder'),
							),
							'toggle'  => array(
								'left-title'  => array(
									'fields' => array('align_items'),
								),
								'right-title' => array(
									'fields' => array('align_items'),
								),
								'left'        => array(
									'fields' => array('align_items'),
								),
								'right'       => array(
									'fields' => array('align_items'),
								),
							),
						),
						'align'             => array(
							'type'       => 'align',
							'label'      => __('Overall Alignment', 'fl-builder'),
							'default'    => 'left',
							'responsive' => true,
							'help'       => __('The alignment applied to every element inside the info box.', 'fl-builder'),
						),
						'align_items'       => array(
							'type'    => 'select',
							'label'   => __('Image / Icon Vertical Alignment', 'fl-builder'),
							'help'    => __('How the image / icon lines up against the content or heading beside it.', 'fl-builder'),
							'default' => 'center',
							'options' => array(
								'center' => __('Center', 'fl-builder'),
								'top'    => __('Top', 'fl-builder'),
							),
						),
						'icon_spacing'      => array(
							'type'        => 'unit',
							'label'       => __('Image / Icon Spacing', 'fl-builder'),
							'help'        => __('Space between the image / icon and the content it sits beside, above or below.', 'fl-builder'),
							'placeholder' => '20',
							'units'       => array('px'),
							'slider'      => true,
						),
						'box_padding'       => array(
							'type'       => 'dimension',
							'label'      => __('Content Padding', 'fl-builder'),
							'units'      => array('px'),
							'slider'     => true,
							'responsive' => true,
							'preview'    => array(
								'type'     => 'css',
								'selector' => '.theme-info-box',
								'property' => 'padding',
							),
						),
						'min_height_switch' => array(
							'type'    => 'select',
							'label'   => __('Minimum Height', 'fl-builder'),
							'default' => 'auto',
							'options' => array(
								'auto'   => __('No', 'fl-builder'),
								'custom' => __('Yes', 'fl-builder'),
							),
							'help'    => __('Useful when several info boxes sit side by side in one row.', 'fl-builder'),
							'toggle'  => array(
								'custom' => array(
									'fields' => array('min_height', 'vertical_align'),
								),
							),
						),
						'min_height'        => array(
							'type'       => 'unit',
							'label'      => __('Height', 'fl-builder'),
							'units'      => array('px'),
							'responsive' => true,
							'slider'     => array(
								'min'  => 0,
								'max'  => 1000,
								'step' => 10,
							),
						),
						'vertical_align'    => array(
							'type'    => 'select',
							'label'   => __('Overall Vertical Alignment', 'fl-builder'),
							'default' => 'center',
							'options' => array(
								'center' => __('Center', 'fl-builder'),
								'top'    => __('Top', 'fl-builder'),
							),
						),
					),
				),
				'box_style' => array(
					'title'  => __('Box', 'fl-builder'),
					'fields' => array(
						'bg_color'           => array(
							'type'        => 'color',
							'label'       => __('Background Color', 'fl-builder'),
							'connections' => array('color'),
							'show_reset'  => true,
							'show_alpha'  => true,
							'preview'     => array(
								'type'     => 'css',
								'selector' => '.theme-info-box',
								'property' => 'background-color',
							),
						),
						'bg_hover_color'     => array(
							'type'        => 'color',
							'label'       => __('Background Hover Color', 'fl-builder'),
							'connections' => array('color'),
							'show_reset'  => true,
							'show_alpha'  => true,
							'preview'     => array(
								'type' => 'none',
							),
						),
						'box_border'         => array(
							'type'       => 'border',
							'label'      => __('Border', 'fl-builder'),
							'responsive' => true,
							'preview'    => array(
								'type'     => 'css',
								'selector' => '.theme-info-box',
								'property' => 'border',
							),
						),
						'border_hover_color' => array(
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
				'spacing'   => array(
					'title'  => __('Spacing', 'fl-builder'),
					'fields' => array(
						'prefix_spacing'      => array(
							'type'        => 'unit',
							'label'       => __('Prefix Spacing', 'fl-builder'),
							'help'        => __('Leave empty to use the theme default.', 'fl-builder'),
							'units'       => array('px'),
							'responsive'  => true,
							'slider'      => true,
							'preview'     => array(
								'type'     => 'css',
								'selector' => '.theme-info-box .theme-info-box-prefix',
								'property' => 'margin-bottom',
								'unit'     => 'px',
							),
						),
						'title_bottom_spacing' => array(
							'type'        => 'unit',
							'label'       => __('Title Spacing', 'fl-builder'),
							'help'        => __('Leave empty to use the theme default.', 'fl-builder'),
							'units'       => array('px'),
							'responsive'  => true,
							'slider'      => true,
							'preview'     => array(
								'type'     => 'css',
								'selector' => '.theme-info-box .theme-info-box-title-wrap',
								'property' => 'margin-bottom',
								'unit'     => 'px',
							),
						),
						'description_spacing'  => array(
							'type'        => 'unit',
							'label'       => __('Description Spacing', 'fl-builder'),
							'help'        => __('Leave empty to use the theme default.', 'fl-builder'),
							'units'       => array('px'),
							'responsive'  => true,
							'slider'      => true,
							'preview'     => array(
								'type'     => 'css',
								'selector' => '.theme-info-box .theme-info-box-text',
								'property' => 'margin-bottom',
								'unit'     => 'px',
							),
						),
					),
				),
			),
		),
		'cta'        => array(
			'title'    => __('Link', 'fl-builder'),
			'sections' => array(
				'cta'          => array(
					'title'  => __('Call to Action', 'fl-builder'),
					'fields' => array(
						'cta_type' => array(
							'type'    => 'select',
							'label'   => __('Type', 'fl-builder'),
							'default' => 'none',
							'options' => array(
								'none'   => __('None', 'fl-builder'),
								'link'   => __('Text', 'fl-builder'),
								'button' => __('Button', 'fl-builder'),
								'box'    => __('Complete Box', 'fl-builder'),
							),
							'toggle'  => array(
								'link'   => array(
									'fields'   => array('cta_text'),
									'sections' => array('link', 'link_style'),
								),
								'button' => array(
									'fields'   => array('btn_text'),
									'sections' => array('link', 'button_style'),
								),
								'box'    => array(
									'sections' => array('link'),
								),
							),
						),
						'cta_text' => array(
							'type'        => 'text',
							'label'       => __('Text', 'fl-builder'),
							'default'     => __('Read More', 'fl-builder'),
							'connections' => array('string', 'html'),
							'preview'     => array(
								'type'     => 'text',
								'selector' => '.theme-info-box-cta-link',
							),
						),
						'btn_text' => array(
							'type'        => 'text',
							'label'       => __('Text', 'fl-builder'),
							'default'     => __('Click Here', 'fl-builder'),
							'connections' => array('string'),
							'preview'     => array(
								'type' => 'refresh',
							),
						),
					),
				),
				'link'         => array(
					'title'  => __('Link', 'fl-builder'),
					'fields' => array(
						'link' => array(
							'type'          => 'link',
							'label'         => __('Link', 'fl-builder'),
							'placeholder'   => 'https://www.example.com',
							'show_target'   => true,
							'show_nofollow' => true,
							'help'          => __('Used by the text link, the button and the complete box, whichever call to action type is selected above.', 'fl-builder'),
							'connections'   => array('url'),
							'preview'       => array(
								'type' => 'none',
							),
						),
					),
				),
				'link_style'   => array(
					'title'  => __('Text Link', 'fl-builder'),
					'fields' => array(
						'link_color'         => array(
							'type'        => 'color',
							'label'       => __('Link Color', 'fl-builder'),
							'connections' => array('color'),
							'show_reset'  => true,
							'show_alpha'  => true,
							'preview'     => array(
								'type'     => 'css',
								'selector' => '.theme-info-box-cta-link',
								'property' => 'color',
							),
						),
						'link_hover_color'   => array(
							'type'        => 'color',
							'label'       => __('Link Hover Color', 'fl-builder'),
							'connections' => array('color'),
							'show_reset'  => true,
							'show_alpha'  => true,
							'preview'     => array(
								'type' => 'none',
							),
						),
					),
				),
				'button_style' => array(
					'title'  => __('Button', 'fl-builder'),
					'fields' => array(
						'btn_style'            => array(
							'type'    => 'select',
							'label'   => __('Style', 'fl-builder'),
							'default' => 'primary',
							'options' => array(
								'primary'   => __('Primary', 'fl-builder'),
								'secondary' => __('Secondary', 'fl-builder'),
								'tertiary'  => __('Tertiary', 'fl-builder'),
							),
							'preview' => array(
								'type' => 'refresh',
							),
						),
						'btn_icon'             => array(
							'type'        => 'icon',
							'label'       => __('Icon', 'fl-builder'),
							'show_remove' => true,
							'show'        => array(
								'fields' => array('btn_icon_position'),
							),
						),
						'btn_icon_position'    => array(
							'type'    => 'select',
							'label'   => __('Icon Position', 'fl-builder'),
							'default' => 'after',
							'options' => array(
								'before' => __('Before Text', 'fl-builder'),
								'after'  => __('After Text', 'fl-builder'),
							),
						),
						'btn_width'            => array(
							'type'    => 'select',
							'label'   => __('Width', 'fl-builder'),
							'default' => 'auto',
							'options' => array(
								'auto'   => __('Auto', 'fl-builder'),
								'full'   => __('Full Width', 'fl-builder'),
								'custom' => __('Custom', 'fl-builder'),
							),
							'toggle'  => array(
								'custom' => array(
									'fields' => array('btn_custom_width'),
								),
							),
						),
						'btn_custom_width'     => array(
							'type'       => 'unit',
							'label'      => __('Custom Width', 'fl-builder'),
							'default'    => '200',
							'units'      => array('px', '%'),
							'responsive' => true,
							'slider'     => array(
								'min'  => 0,
								'max'  => 1000,
								'step' => 10,
							),
						),
					),
				),
			),
		),
		'typography' => array(
			'title'    => __('Typography', 'fl-builder'),
			'sections' => array(
				'prefix_typography' => array(
					'title'  => __('Prefix', 'fl-builder'),
					'fields' => array(
						'prefix_tag'         => array(
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
						'prefix_color'       => array(
							'type'        => 'color',
							'label'       => __('Color', 'fl-builder'),
							'connections' => array('color'),
							'show_reset'  => true,
							'show_alpha'  => true,
							'preview'     => array(
								'type'     => 'css',
								'selector' => '.theme-info-box-prefix',
								'property' => 'color',
							),
						),
					),
				),
				'title_typography'  => array(
					'title'  => __('Title', 'fl-builder'),
					'fields' => array(
						'title_tag'         => array(
							'type'    => 'select',
							'label'   => __('HTML Tag', 'fl-builder'),
							'default' => 'h2',
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
						'title_color'       => array(
							'type'        => 'color',
							'label'       => __('Color', 'fl-builder'),
							'connections' => array('color'),
							'show_reset'  => true,
							'show_alpha'  => true,
							'preview'     => array(
								'type'     => 'css',
								'selector' => '.theme-info-box-title',
								'property' => 'color',
							),
						),
					),
				),
				'desc_typography'   => array(
					'title'     => __('Description', 'fl-builder'),
					'collapsed' => true,
					'fields'    => array(
						'desc_color'       => array(
							'type'        => 'color',
							'label'       => __('Color', 'fl-builder'),
							'connections' => array('color'),
							'show_reset'  => true,
							'show_alpha'  => true,
							'preview'     => array(
								'type'     => 'css',
								'selector' => '.theme-info-box-text',
								'property' => 'color',
							),
						),
					),
				),
			),
		),
	)
);
