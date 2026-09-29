<?php

/**
 *  Theme Counter module file
 *
 *  An animated number counter, shown on its own, inside a circle or semicircle
 *  that fills to the value, or beside a bar that does the same.
 *
 *  A theme-owned copy of UABB Counter from Ultimate Addons for Beaver Builder,
 *  renamed so it can sit beside the plugin's module without clashing. It depends
 *  on nothing but Beaver Builder core: the icon / photo and the separator are
 *  rendered here rather than through the plugin's Image Icon and Separator
 *  modules, and the count runs on js/frontend.js instead of jQuery Waypoints.
 *
 *  @package Evektus
 */

defined('ABSPATH') || exit;

/**
 * Function that initializes Theme Counter Module
 *
 * @class ThemeCounterModule
 */
class ThemeCounterModule extends FLBuilderModule
{

	/**
	 * Constructor function that constructs default values for the Counter module.
	 *
	 * @method __construct
	 */
	public function __construct()
	{
		parent::__construct(
			array(
				'name'            => __('Theme Counter', 'fl-builder'),
				'description'     => __('Renders an animated number counter.', 'fl-builder'),
				'category'        => __('Theme Modules', 'fl-builder'),
				'group'           => __('Theme', 'fl-builder'),
				'dir'             => EVEK_DIR . '/fl-builder/modules/theme-counter/',
				'url'             => EVEK_URI . '/fl-builder/modules/theme-counter/',
				'slug'            => 'theme-counter',
				'partial_refresh' => true,
			)
		);
	}

	/**
	 * Font Awesome is only needed when the counter actually renders an icon.
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

		if ($this->has_media() && 'icon' === $this->settings->image_type && ! empty($this->settings->icon)) {
			$this->add_css('font-awesome-5');
		}
	}

	/**
	 * The counter style: default (number only), circle, semi-circle or bars.
	 *
	 * @return string
	 */
	public function get_layout()
	{
		$layout = isset($this->settings->layout) ? $this->settings->layout : 'default';

		return in_array($layout, array('default', 'circle', 'semi-circle', 'bars'), true) ? $layout : 'default';
	}

	/**
	 * Whether the counter shows an icon or a photo at all. Only the number and
	 * circle styles offer one.
	 *
	 * @return bool
	 */
	public function has_media()
	{
		return in_array($this->get_layout(), array('default', 'circle'), true)
			&& isset($this->settings->image_type) && 'none' !== $this->settings->image_type;
	}

	/**
	 * The configured media position, or '' when there is no media. The circle
	 * style has its own position field, limited to above or below the number.
	 *
	 * @return string
	 */
	public function get_media_position()
	{
		if (! $this->has_media()) {
			return '';
		}

		if ('circle' === $this->get_layout()) {
			return isset($this->settings->circle_position) ? $this->settings->circle_position : 'above-title';
		}

		return isset($this->settings->img_icon_position) ? $this->settings->img_icon_position : 'above-title';
	}

	/**
	 * Whether the media sits inline with the number.
	 *
	 * @return bool
	 */
	public function is_title_media()
	{
		return in_array($this->get_media_position(), array('left-title', 'right-title'), true);
	}

	/**
	 * Function that gets the root classname for the counter.
	 *
	 * @method get_classname
	 * @return string
	 */
	public function get_classname()
	{
		$position  = $this->get_media_position();
		$classname = 'theme-counter theme-counter--' . $this->get_layout();

		$classname .= ' theme-counter--' . ('' === $position ? 'no-media' : $position);

		return $classname;
	}

	/**
	 * Whether the counter counts to a percentage or a custom range.
	 *
	 * @return string
	 */
	public function get_number_type()
	{
		return (isset($this->settings->number_type) && 'standard' === $this->settings->number_type) ? 'standard' : 'percent';
	}

	/**
	 * Function that renders the number. It starts at zero; js/frontend.js counts
	 * it up to the configured value once the counter scrolls into view.
	 *
	 * @method render_number
	 * @return void
	 */
	public function render_number()
	{
		$settings = $this->settings;
		$percent  = 'percent' === $this->get_number_type();
		$prefix   = $percent ? '' : (isset($settings->number_prefix) ? $settings->number_prefix : '');
		$suffix   = $percent ? '%' : (isset($settings->number_suffix) ? $settings->number_suffix : '');
		$tag      = isset($settings->num_tag_selection) ? $settings->num_tag_selection : 'h2';

		if (! in_array($tag, array('h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'div', 'p', 'span'), true)) {
			$tag = 'div';
		}

		echo '<' . esc_attr($tag) . ' class="theme-counter-number">' . wp_kses_post($prefix) . '<span class="theme-counter-number-int">0</span>' . wp_kses_post($suffix) . '</' . esc_attr($tag) . '>';
	}

	/**
	 * Function that renders the text above the number.
	 *
	 * @method render_before_number_text
	 * @return void
	 */
	public function render_before_number_text()
	{
		if (empty($this->settings->before_number_text)) {
			return;
		}

		echo '<span class="theme-counter-before-text">' . wp_kses_post($this->settings->before_number_text) . '</span>';
	}

	/**
	 * Function that renders the text below the number.
	 *
	 * @method render_after_number_text
	 * @return void
	 */
	public function render_after_number_text()
	{
		if (empty($this->settings->after_number_text)) {
			return;
		}

		echo '<span class="theme-counter-after-text">' . wp_kses_post($this->settings->after_number_text) . '</span>';
	}

	/**
	 * Function that renders the labels under either end of the semicircle.
	 *
	 * @method render_counter_texts
	 * @return void
	 */
	public function render_counter_texts()
	{
		$before = isset($this->settings->before_counter_text) ? $this->settings->before_counter_text : '';
		$after  = isset($this->settings->after_counter_text) ? $this->settings->after_counter_text : '';

		if ('' === $before && '' === $after) {
			return;
		}

		echo '<div class="theme-counter-gauge-labels">';
		echo '<span class="theme-counter-counter-before-text">' . wp_kses_post($before) . '</span>';
		echo '<span class="theme-counter-counter-after-text">' . wp_kses_post($after) . '</span>';
		echo '</div>';
	}

	/**
	 * The circle's size and stroke width, in SVG user units.
	 *
	 * @return array Array with 'width' and 'stroke' keys.
	 */
	public function get_circle_metrics()
	{
		$width  = ! empty($this->settings->circle_width) && is_numeric($this->settings->circle_width) ? (float) $this->settings->circle_width : 300;
		$stroke = ! empty($this->settings->circle_dash_width) && is_numeric($this->settings->circle_dash_width) ? (float) $this->settings->circle_dash_width : 10;

		return array(
			'width'  => $width,
			'stroke' => min($stroke, $width / 2),
		);
	}

	/**
	 * Function that renders the circle bar. Both rings use pathLength="100", so
	 * the fill's dash offset runs from 100 (empty) to 0 (full) whatever the size.
	 *
	 * @method render_circle_bar
	 * @return void
	 */
	public function render_circle_bar()
	{
		$metrics = $this->get_circle_metrics();
		$pos     = $metrics['width'] / 2;
		$radius  = $pos - ($metrics['stroke'] / 2);
	?>
		<svg class="theme-counter-svg" viewBox="0 0 <?php echo esc_attr($metrics['width'] . ' ' . $metrics['width']); ?>" preserveAspectRatio="xMidYMid meet" aria-hidden="true" focusable="false">
			<circle class="theme-counter-track" r="<?php echo esc_attr($radius); ?>" cx="<?php echo esc_attr($pos); ?>" cy="<?php echo esc_attr($pos); ?>" fill="none" pathLength="100"></circle>
			<circle class="theme-counter-fill" r="<?php echo esc_attr($radius); ?>" cx="<?php echo esc_attr($pos); ?>" cy="<?php echo esc_attr($pos); ?>" fill="none" pathLength="100" stroke-dasharray="100" stroke-dashoffset="100" transform="rotate(-90 <?php echo esc_attr($pos . ' ' . $pos); ?>)"></circle>
		</svg>
	<?php
	}

	/**
	 * Function that renders the semicircle bar: an arc over the top from left to
	 * right, filled from the left.
	 *
	 * @method render_semi_circle_bar
	 * @return void
	 */
	public function render_semi_circle_bar()
	{
		$metrics = $this->get_circle_metrics();
		$pos     = $metrics['width'] / 2;
		$radius  = $pos - ($metrics['stroke'] / 2);
		$arc     = sprintf('M %1$s %2$s A %3$s %3$s 0 0 1 %4$s %2$s', $pos - $radius, $pos, $radius, $pos + $radius);
	?>
		<svg class="theme-counter-svg" viewBox="0 0 <?php echo esc_attr($metrics['width'] . ' ' . $pos); ?>" preserveAspectRatio="xMidYMax meet" aria-hidden="true" focusable="false">
			<path class="theme-counter-track" d="<?php echo esc_attr($arc); ?>" fill="none" pathLength="100"></path>
			<path class="theme-counter-fill" d="<?php echo esc_attr($arc); ?>" fill="none" pathLength="100" stroke-dasharray="100" stroke-dashoffset="100"></path>
		</svg>
	<?php
	}

	/**
	 * Renders the separator rule below the number.
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
		<div class="theme-counter-separator-wrap">
			<span class="theme-counter-separator"></span>
		</div>
		<?php
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
			return '<img class="theme-counter-photo" src="' . esc_url($settings->photo_url) . '" alt="" loading="lazy" />';
		}

		if (empty($settings->photo)) {
			return '';
		}

		$resolved = $this->resolve_photo($settings->photo);
		$src      = ! empty($settings->photo_src) ? $settings->photo_src : $resolved['url'];

		// The Beaver Builder photo field stores the selected attachment size in
		// photo_src. Use it directly, matching Theme Info Box.
		if ('' !== $src) {
			$alt = ! empty($resolved['id']) ? get_post_meta($resolved['id'], '_wp_attachment_image_alt', true) : '';

			return '<img class="theme-counter-photo" src="' . esc_url($src) . '" alt="' . esc_attr($alt) . '" loading="lazy" />';
		}

		if (! empty($resolved['id'])) {
			$html = wp_get_attachment_image(
				$resolved['id'],
				'full',
				false,
				array(
					'class'   => 'theme-counter-photo',
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
			<div class="theme-counter-media">
				<span class="theme-counter-icon theme-counter-icon--<?php echo esc_attr($style); ?>">
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
		<div class="theme-counter-media">
			<span class="theme-counter-photo-wrap theme-counter-photo-wrap--<?php echo esc_attr($style); ?>">
				<?php echo $photo; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Markup built by wp_get_attachment_image() or escaped above.
				?>
			</span>
		</div>
<?php
	}

	/**
	 * Renders the number, with the media inline beside it when the media
	 * position is left / right of the heading.
	 *
	 * @method render_title
	 * @return void
	 */
	public function render_title()
	{
		$inline = $this->is_title_media();

		if ($inline) {
			echo '<div class="theme-counter-title-row">';
		}

		$this->render_media('left-title');
		$this->render_number();
		$this->render_media('right-title');

		if ($inline) {
			echo '</div>';
		}
	}

	/**
	 * The settings js/frontend.js needs to run the count, with the same
	 * fallbacks the plugin applies: an empty or invalid number counts to 100,
	 * the total defaults to the number, and speed and delay default to one
	 * second each.
	 *
	 * @return array
	 */
	public function get_js_config()
	{
		$settings = $this->settings;
		$number   = isset($settings->number) ? trim((string) $settings->number) : '';
		$number   = ('' !== $number && is_numeric($number)) ? $number : '100';
		$max      = isset($settings->max_number) ? trim((string) $settings->max_number) : '';
		$max      = ('' !== $max && is_numeric($max) && 0 != $max) ? (float) $max : (float) $number; // phpcs:ignore Universal.Operators.StrictComparisons.LooseNotEqual -- '0', '0.0' and 0 are all zero.
		$speed    = (isset($settings->animation_speed) && is_numeric($settings->animation_speed)) ? (float) $settings->animation_speed : 1;
		$delay    = (isset($settings->delay) && is_numeric($settings->delay)) ? (float) $settings->delay : 1;
		$decimals = strpos($number, '.') !== false ? strlen(substr(strrchr($number, '.'), 1)) : 0;

		return array(
			'id'           => $this->node,
			'layout'       => $this->get_layout(),
			'type'         => $this->get_number_type(),
			'number'       => (float) $number,
			'decimals'     => $decimals,
			'max'          => $max,
			'numberFormat' => isset($settings->number_format) ? $settings->number_format : 'comma',
			'locale'       => str_replace('_', '-', get_locale()),
			'speed'        => max(0, $speed) * 1000,
			'delay'        => max(0, $delay) * 1000,
		);
	}
}

/**
 * Register the module and its form settings.
 */
FLBuilder::register_module(
	'ThemeCounterModule',
	array(
		'general'    => array(
			'title'    => __('General', 'fl-builder'),
			'sections' => array(
				'general' => array(
					'title'  => '',
					'fields' => array(
						'layout'              => array(
							'type'    => 'select',
							'label'   => __('Counter Style', 'fl-builder'),
							'default' => 'default',
							'options' => array(
								'default'     => __('Only Numbers', 'fl-builder'),
								'circle'      => __('Circle Counter', 'fl-builder'),
								'semi-circle' => __('Semicircle Counter', 'fl-builder'),
								'bars'        => __('Bars Counter', 'fl-builder'),
							),
							'toggle'  => array(
								'default'     => array(
									'sections' => array('separator', 'structure', 'img_icon_margins'),
									'tabs'     => array('imageicon'),
									'fields'   => array('img_icon_position', 'before_number_text', 'after_number_text'),
								),
								'circle'      => array(
									'sections' => array('circle_bar_style', 'separator', 'structure', 'img_icon_margins'),
									'tabs'     => array('imageicon'),
									'fields'   => array('circle_position', 'max_number', 'before_number_text', 'after_number_text'),
								),
								'semi-circle' => array(
									'sections' => array('circle_bar_style', 'structure'),
									'fields'   => array('max_number', 'before_counter_text', 'after_counter_text'),
								),
								'bars'        => array(
									'sections' => array('bar_style'),
									'fields'   => array('max_number', 'number_position', 'before_number_text', 'after_number_text'),
								),
							),
						),
						'number_type'         => array(
							'type'    => 'select',
							'label'   => __('Number Range', 'fl-builder'),
							'default' => 'percent',
							'options' => array(
								'percent'  => __('In Percentage ( Out of 100% )', 'fl-builder'),
								'standard' => __('Custom Range ( Define your own range )', 'fl-builder'),
							),
							'toggle'  => array(
								'standard' => array(
									'fields' => array('number_prefix', 'number_suffix', 'number_format'),
								),
							),
						),
						'number'              => array(
							'type'        => 'unit',
							'label'       => __('Counter Number', 'fl-builder'),
							'placeholder' => '100',
							'help'        => __('Enter counter value', 'fl-builder'),
							'connections' => array('html'),
						),
						'max_number'          => array(
							'type'        => 'unit',
							'label'       => __('Out Of', 'fl-builder'),
							'help'        => __('The total number of units for this counter when Number Range is set to Custom Range. For example, if the Number is set to 250 and the Total is set to 500, the counter will fill to 50%.', 'fl-builder'),
							'connections' => array('html'),
						),
						'number_format'       => array(
							'type'    => 'select',
							'label'   => __('Counter Number Format', 'fl-builder'),
							'default' => 'comma',
							'options' => array(
								'comma'  => __('Comma Delimiter', 'fl-builder'),
								'locale' => __('WordPress Locale based Delimiter', 'fl-builder'),
								'none'   => __('Number without Delimiter', 'fl-builder'),
							),
							'help'    => __('Control the delimiters of entered number.', 'fl-builder'),
						),
						'number_position'     => array(
							'type'    => 'select',
							'label'   => __('Number Position', 'fl-builder'),
							'help'    => __('Where to display the number in relation to the bar.', 'fl-builder'),
							'default' => 'default',
							'options' => array(
								'none'    => __('None', 'fl-builder'),
								'default' => __('Inside Bar', 'fl-builder'),
								'above'   => __('Above Bar', 'fl-builder'),
								'below'   => __('Below Bar', 'fl-builder'),
							),
						),
						'before_number_text'  => array(
							'type'        => 'text',
							'label'       => __('Text Above Number', 'fl-builder'),
							'help'        => __('Text to appear above the number. Leave it empty for none.', 'fl-builder'),
							'connections' => array('html', 'string'),
							'preview'     => array(
								'type'     => 'text',
								'selector' => '.theme-counter-before-text',
							),
						),
						'before_counter_text' => array(
							'type'    => 'text',
							'label'   => __('Text Before Counter', 'fl-builder'),
							'help'    => __('Text to appear under the start of the semicircle. Leave it empty for none.', 'fl-builder'),
							'preview' => array(
								'type'     => 'text',
								'selector' => '.theme-counter-counter-before-text',
							),
						),
						'after_number_text'   => array(
							'type'        => 'text',
							'label'       => __('Text Below Number', 'fl-builder'),
							'help'        => __('Text to appear below the number. Leave it empty for none.', 'fl-builder'),
							'connections' => array('html', 'string'),
							'preview'     => array(
								'type'     => 'text',
								'selector' => '.theme-counter-after-text',
							),
						),
						'after_counter_text'  => array(
							'type'    => 'text',
							'label'   => __('Text After Counter', 'fl-builder'),
							'help'    => __('Text to appear under the end of the semicircle. Leave it empty for none.', 'fl-builder'),
							'preview' => array(
								'type'     => 'text',
								'selector' => '.theme-counter-counter-after-text',
							),
						),
						'number_prefix'       => array(
							'type'  => 'text',
							'label' => __('Number Prefix', 'fl-builder'),
							'help'  => __('For example, if your number is US$ 10, your prefix would be "US$ ".', 'fl-builder'),
						),
						'number_suffix'       => array(
							'type'  => 'text',
							'label' => __('Number Suffix', 'fl-builder'),
							'help'  => __('For example, if your number is 10+, your suffix would be "+".', 'fl-builder'),
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
						'image_type'        => array(
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
								),
								'photo' => array(
									'sections' => array('img_basic', 'img_style'),
								),
							),
						),
						'img_icon_position' => array(
							'type'    => 'select',
							'label'   => __('Position', 'fl-builder'),
							'default' => 'above-title',
							'options' => array(
								'above-title' => __('Above Number', 'fl-builder'),
								'below-title' => __('Below Number', 'fl-builder'),
								'left-title'  => __('Left of Number', 'fl-builder'),
								'right-title' => __('Right of Number', 'fl-builder'),
								'left'        => __('Left of Text and Number', 'fl-builder'),
								'right'       => __('Right of Text and Number', 'fl-builder'),
							),
						),
						'circle_position'   => array(
							'type'    => 'select',
							'label'   => __('Position', 'fl-builder'),
							'default' => 'above-title',
							'options' => array(
								'above-title' => __('Above Number', 'fl-builder'),
								'below-title' => __('Below Number', 'fl-builder'),
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
								'selector' => '.theme-counter-icon',
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
							'default'    => '150',
							'units'      => array('px'),
							'responsive' => true,
							'slider'     => array(
								'min'  => 0,
								'max'  => 1000,
								'step' => 10,
							),
							'preview'    => array(
								'type'     => 'css',
								'selector' => '.theme-counter-photo',
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
								'selector' => '.theme-counter-icon',
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
								'selector' => '.theme-counter-icon',
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
								'selector' => '.theme-counter-icon',
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
								'selector' => '.theme-counter-photo-wrap',
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
								'selector' => '.theme-counter-photo-wrap',
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
				'circle_bar_style' => array(
					'title'  => __('Circle Bar Styles', 'fl-builder'),
					'fields' => array(
						'circle_width'      => array(
							'type'        => 'unit',
							'label'       => __('Circle Size', 'fl-builder'),
							'placeholder' => '300',
							'units'       => array('px'),
							'slider'      => array(
								'min'  => 50,
								'max'  => 1000,
								'step' => 10,
							),
						),
						'circle_dash_width' => array(
							'type'        => 'unit',
							'label'       => __('Circle Stroke Size', 'fl-builder'),
							'placeholder' => '10',
							'units'       => array('px'),
							'slider'      => array(
								'min'  => 1,
								'max'  => 100,
								'step' => 1,
							),
						),
						'circle_color'      => array(
							'type'        => 'color',
							'label'       => __('Circle Foreground Color', 'fl-builder'),
							'help'        => __('Leave empty to use the theme primary color.', 'fl-builder'),
							'connections' => array('color'),
							'show_reset'  => true,
							'show_alpha'  => true,
							'preview'     => array(
								'type'     => 'css',
								'selector' => '.theme-counter-fill',
								'property' => 'stroke',
							),
						),
						'circle_bg_color'   => array(
							'type'        => 'color',
							'label'       => __('Circle Background Color', 'fl-builder'),
							'default'     => 'fafafa',
							'connections' => array('color'),
							'show_reset'  => true,
							'show_alpha'  => true,
							'preview'     => array(
								'type'     => 'css',
								'selector' => '.theme-counter-track',
								'property' => 'stroke',
							),
						),
					),
				),
				'bar_style'        => array(
					'title'  => __('Bar Styles', 'fl-builder'),
					'fields' => array(
						'bar_color'    => array(
							'type'        => 'color',
							'label'       => __('Bar Foreground Color', 'fl-builder'),
							'help'        => __('Leave empty to use the theme primary color.', 'fl-builder'),
							'connections' => array('color'),
							'show_reset'  => true,
							'show_alpha'  => true,
							'preview'     => array(
								'type'     => 'css',
								'selector' => '.theme-counter-bar',
								'property' => 'background-color',
							),
						),
						'bar_bg_color' => array(
							'type'        => 'color',
							'label'       => __('Bar Background Color', 'fl-builder'),
							'default'     => 'fafafa',
							'connections' => array('color'),
							'show_reset'  => true,
							'show_alpha'  => true,
							'preview'     => array(
								'type'     => 'css',
								'selector' => '.theme-counter-bar-track',
								'property' => 'background-color',
							),
						),
					),
				),
				'structure'        => array(
					'title'  => __('Structure', 'fl-builder'),
					'fields' => array(
						'align' => array(
							'type'    => 'align',
							'label'   => __('Overall Alignment', 'fl-builder'),
							'default' => 'center',
							'help'    => __('Ignored when the image / icon sits left or right of the number, which aligns the counter to that side.', 'fl-builder'),
						),
					),
				),
				'margin_style'     => array(
					'title'  => __('Number Margins', 'fl-builder'),
					'fields' => array(
						'number_top_margin'    => array(
							'type'   => 'unit',
							'label'  => __('Number Top Margin', 'fl-builder'),
							'units'  => array('px'),
							'slider' => true,
						),
						'number_bottom_margin' => array(
							'type'   => 'unit',
							'label'  => __('Number Bottom Margin', 'fl-builder'),
							'units'  => array('px'),
							'slider' => true,
						),
					),
				),
				'separator'        => array(
					'title'     => __('Separator ( Below Number )', 'fl-builder'),
					'collapsed' => true,
					'fields'    => array(
						'show_separator'          => array(
							'type'    => 'select',
							'label'   => __('Show separator', 'fl-builder'),
							'default' => 'no',
							'options' => array(
								'yes' => __('Yes', 'fl-builder'),
								'no'  => __('No', 'fl-builder'),
							),
							'toggle'  => array(
								'yes' => array(
									'fields' => array('separator_style', 'separator_color', 'separator_height', 'separator_width', 'separator_alignment', 'separator_top_margin', 'separator_bottom_margin'),
								),
							),
						),
						'separator_style'         => array(
							'type'    => 'select',
							'label'   => __('Style', 'fl-builder'),
							'default' => 'solid',
							'options' => array(
								'solid'  => __('Solid', 'fl-builder'),
								'dashed' => __('Dashed', 'fl-builder'),
								'dotted' => __('Dotted', 'fl-builder'),
								'double' => __('Double', 'fl-builder'),
							),
							'help'    => __('The type of border to use. Double borders must have a height of at least 3px to render properly.', 'fl-builder'),
						),
						'separator_color'         => array(
							'type'        => 'color',
							'label'       => __('Separator Color', 'fl-builder'),
							'connections' => array('color'),
							'show_reset'  => true,
							'show_alpha'  => true,
							'preview'     => array(
								'type'     => 'css',
								'selector' => '.theme-counter-separator',
								'property' => 'border-top-color',
							),
						),
						'separator_height'        => array(
							'type'        => 'unit',
							'label'       => __('Thickness', 'fl-builder'),
							'placeholder' => '1',
							'units'       => array('px'),
							'help'        => __('Adjust thickness of border.', 'fl-builder'),
							'slider'      => array(
								'min'  => 0,
								'max'  => 20,
								'step' => 1,
							),
						),
						'separator_width'         => array(
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
						'separator_alignment'     => array(
							'type'       => 'align',
							'label'      => __('Alignment', 'fl-builder'),
							'responsive' => true,
							'help'       => __('Leave unset to follow the overall alignment.', 'fl-builder'),
						),
						'separator_top_margin'    => array(
							'type'   => 'unit',
							'label'  => __('Separator Top Margin', 'fl-builder'),
							'units'  => array('px'),
							'slider' => true,
						),
						'separator_bottom_margin' => array(
							'type'   => 'unit',
							'label'  => __('Separator Bottom Margin', 'fl-builder'),
							'units'  => array('px'),
							'slider' => true,
						),
					),
				),
				'img_icon_margins' => array(
					'title'     => __('Image / Icon Margins', 'fl-builder'),
					'collapsed' => true,
					'fields'    => array(
						'img_icon_margin_top'    => array(
							'type'   => 'unit',
							'label'  => __('Top', 'fl-builder'),
							'units'  => array('px'),
							'slider' => true,
						),
						'img_icon_margin_bottom' => array(
							'type'   => 'unit',
							'label'  => __('Bottom', 'fl-builder'),
							'units'  => array('px'),
							'slider' => true,
						),
					),
				),
				'animation'        => array(
					'title'  => __('Counter Animation', 'fl-builder'),
					'fields' => array(
						'animation_speed' => array(
							'type'        => 'unit',
							'label'       => __('Animation Speed', 'fl-builder'),
							'placeholder' => '1',
							'description' => __('second(s)', 'fl-builder'),
							'help'        => __('Number of seconds to complete the animation.', 'fl-builder'),
						),
						'delay'           => array(
							'type'        => 'unit',
							'label'       => __('Animation Delay', 'fl-builder'),
							'placeholder' => '1',
							'description' => __('second(s)', 'fl-builder'),
							'help'        => __('Number of seconds to wait after the counter scrolls into view.', 'fl-builder'),
						),
					),
				),
			),
		),
		'typography' => array(
			'title'    => __('Typography', 'fl-builder'),
			'sections' => array(
				'number_typography'  => array(
					'title'  => __('Number Text', 'fl-builder'),
					'fields' => array(
						'num_tag_selection' => array(
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
						'num_typo'          => array(
							'type'       => 'typography',
							'label'      => __('Typography', 'fl-builder'),
							'responsive' => true,
							'preview'    => array(
								'type'     => 'css',
								'selector' => '.theme-counter-number',
							),
						),
						'num_color'         => array(
							'type'        => 'color',
							'label'       => __('Color', 'fl-builder'),
							'connections' => array('color'),
							'show_reset'  => true,
							'show_alpha'  => true,
							'preview'     => array(
								'type'     => 'css',
								'selector' => '.theme-counter-number',
								'property' => 'color',
							),
						),
					),
				),
				'ba_text_typography' => array(
					'title'  => __('Before - After Text', 'fl-builder'),
					'fields' => array(
						'ba_typo'  => array(
							'type'       => 'typography',
							'label'      => __('Typography', 'fl-builder'),
							'responsive' => true,
							'preview'    => array(
								'type'     => 'css',
								'selector' => '.theme-counter-before-text, .theme-counter-after-text, .theme-counter-counter-before-text, .theme-counter-counter-after-text',
							),
						),
						'ba_color' => array(
							'type'        => 'color',
							'label'       => __('Color', 'fl-builder'),
							'connections' => array('color'),
							'show_reset'  => true,
							'show_alpha'  => true,
							'preview'     => array(
								'type'     => 'css',
								'selector' => '.theme-counter-before-text, .theme-counter-after-text, .theme-counter-counter-before-text, .theme-counter-counter-after-text',
								'property' => 'color',
							),
						),
					),
				),
			),
		),
	)
);
