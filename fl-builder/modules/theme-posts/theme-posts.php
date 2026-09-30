<?php

/**
 *  Theme Posts module file
 *
 *  A post loop rendered as a grid, masonry wall, list or carousel, with an
 *  optional taxonomy filter and pagination. Every card is drawn from one
 *  editable HTML template -- the Post Layout -- filled in per post with Beaver
 *  Builder's own field connection shortcodes: [wpbb post:title],
 *  [wpbb-if post:featured_image] and the rest, the syntax documented at
 *  https://docs.wpbeaverbuilder.com/beaver-themer/field-connections/syntax
 *
 *  A theme-owned copy of FM Posts from Forte Marketing Modules, renamed so it
 *  can sit beside the plugin's module without clashing. FM Posts parses its own
 *  [fm_post] tokens; this one hands the template to Beaver Builder's field
 *  connection parser instead, exactly as the core Post Grid's custom layout
 *  does, so the template's "Insert" menu, docs and every property Beaver Themer
 *  or a plugin registers all apply. The post:* properties themselves come from
 *  Beaver Themer, so without it the shortcodes render nothing.
 *
 *  The module ships only starting CSS (css/frontend.css) and has no Style or
 *  Typography settings: a site's card design belongs in the theme stylesheet,
 *  written against the classes in the template. The carousel runs on a copy of
 *  Swiper vendored in swiper/, and masonry on the jQuery Masonry that Beaver
 *  Builder already registers.
 *
 *  @package Evektus
 */

defined('ABSPATH') || exit;

/**
 * Function that initializes Theme Posts Module
 *
 * @class ThemePostsModule
 */
class ThemePostsModule extends FLBuilderModule
{

	/**
	 * The query for the current render, cached so the filter pass and the card
	 * pass share a single query.
	 *
	 * @var WP_Query|null $the_query
	 */
	public $the_query = null;

	/**
	 * Constructor function that constructs default values for the Posts module.
	 *
	 * @method __construct
	 */
	public function __construct()
	{
		parent::__construct(
			array(
				'name'            => __('Theme Posts', 'fl-builder'),
				'description'     => __('Display posts as a grid, masonry wall, list or carousel.', 'fl-builder'),
				'category'        => __('Theme Modules', 'fl-builder'),
				'group'           => __('Theme', 'fl-builder'),
				'dir'             => EVEK_DIR . '/fl-builder/modules/theme-posts/',
				'url'             => EVEK_URI . '/fl-builder/modules/theme-posts/',
				'slug'            => 'theme-posts',
				'editor_export'   => true,
				'partial_refresh' => true,
			)
		);
	}

	/**
	 * Enqueues each library only when the settings use it.
	 *
	 * Swiper is the copy vendored in swiper/. Masonry and Font Awesome are
	 * registered by Beaver Builder core.
	 *
	 * @method enqueue_scripts
	 */
	public function enqueue_scripts()
	{
		if ($this->is_carousel()) {
			$this->add_css('evek-swiper', $this->url . 'swiper/swiper-bundle.min.css', array(), '11.2.10');
			$this->add_js('evek-swiper', $this->url . 'swiper/swiper-bundle.min.js', array(), '11.2.10', true);
		}

		// Masonry lays the cards out with jQuery Masonry. Load it in the builder
		// too so switching Layout to Masonry works without a hard reload.
		if ((class_exists('FLBuilderModel') && FLBuilderModel::is_builder_active()) || $this->is_masonry()) {
			$this->add_js('imagesloaded');
			$this->add_js('jquery-masonry');
		}

		if ($this->uses_icons()) {
			$this->add_css('font-awesome-5');
		}
	}

	/**
	 * Whether anything rendered uses a Font Awesome icon: the carousel arrows,
	 * or an icon written into the post layout template.
	 *
	 * @return bool
	 */
	public function uses_icons()
	{
		if ($this->is_carousel() && $this->has_arrows()) {
			return true;
		}

		return false !== strpos($this->get_layout_html(), 'fa-');
	}

	/**
	 * Reads a setting, falling back when it is unset or left empty.
	 *
	 * @param string $key      Setting name.
	 * @param mixed  $fallback Value to use when the setting is missing or ''.
	 * @return mixed
	 */
	public function get($key, $fallback = '')
	{
		if (! isset($this->settings->$key) || '' === $this->settings->$key) {
			return $fallback;
		}
		return $this->settings->$key;
	}

	/**
	 * The chosen layout.
	 *
	 * @return string One of grid, masonry, list, carousel.
	 */
	public function get_layout()
	{
		$layout = $this->get('layout', 'grid');
		return in_array($layout, array('grid', 'masonry', 'list', 'carousel'), true) ? $layout : 'grid';
	}

	/**
	 * Whether the posts render as a Swiper carousel.
	 *
	 * @return bool
	 */
	public function is_carousel()
	{
		return 'carousel' === $this->get_layout();
	}

	/**
	 * Whether the posts render as a masonry wall.
	 *
	 * @return bool
	 */
	public function is_masonry()
	{
		return 'masonry' === $this->get_layout();
	}

	/**
	 * Whether the carousel shows its arrows.
	 *
	 * @return bool
	 */
	public function has_arrows()
	{
		return 'no' !== $this->get('arrows', 'yes');
	}

	/**
	 * The carousel arrow position.
	 *
	 * @return string
	 */
	public function get_arrow_position()
	{
		return $this->get('arrow_position', 'overlay');
	}

	/**
	 * The carousel pagination type, or 'none'.
	 *
	 * @return string
	 */
	public function get_pagination_type()
	{
		$type = $this->get('carousel_pagination', 'bullets');
		return in_array($type, array('bullets', 'fraction', 'progressbar'), true) ? $type : 'none';
	}

	/**
	 * Builds the query using Beaver Builder's core loop. Cached on the instance
	 * so the module runs one query per render.
	 *
	 * @return WP_Query
	 */
	public function get_query()
	{
		if (null === $this->the_query) {
			$this->the_query = FLBuilderLoop::query($this->settings);
		}
		return $this->the_query;
	}

	/**
	 * The post type the query is set to, used to resolve the filter taxonomy.
	 *
	 * @return string
	 */
	public function get_post_type()
	{
		$post_type = $this->get('post_type', 'post');
		return is_array($post_type) ? (string) reset($post_type) : (string) $post_type;
	}

	/**
	 * Whether the taxonomy filter bar should render. Carousels and lists have
	 * nowhere sensible to put it, so it is grid and masonry only.
	 *
	 * @return bool
	 */
	public function has_filters()
	{
		if (! in_array($this->get_layout(), array('grid', 'masonry'), true)) {
			return false;
		}
		if ('yes' !== $this->get('show_filter', 'no')) {
			return false;
		}
		return '' !== $this->get_filter_taxonomy();
	}

	/**
	 * The taxonomy the filter bar is built from. Falls back to the first
	 * taxonomy registered for the post type when none is chosen.
	 *
	 * @return string
	 */
	public function get_filter_taxonomy()
	{
		$taxonomy = $this->get('filter_taxonomy', '');

		if ('' === $taxonomy) {
			$taxonomies = get_object_taxonomies($this->get_post_type());
			$taxonomy   = empty($taxonomies) ? '' : $taxonomies[0];
		}

		return taxonomy_exists($taxonomy) ? $taxonomy : '';
	}

	/**
	 * Returns the terms of the filter taxonomy that the queried posts actually
	 * use, so the bar never offers a term that would filter to nothing.
	 *
	 * @return array Term objects keyed by term id.
	 */
	public function get_filter_terms()
	{
		$taxonomy = $this->get_filter_taxonomy();
		$query    = $this->get_query();
		$terms    = array();

		if ('' === $taxonomy || empty($query->posts)) {
			return $terms;
		}

		foreach ($query->posts as $post) {
			$post_terms = wp_get_post_terms($post->ID, $taxonomy);

			if (is_wp_error($post_terms)) {
				continue;
			}

			foreach ($post_terms as $term) {
				$terms[$term->term_id] = $term;
			}
		}

		return $terms;
	}

	/**
	 * Renders the whole module. Called from includes/frontend.php.
	 *
	 * @return void
	 */
	public function render()
	{
		$layout = $this->get_layout();
?>
		<div class="theme-posts theme-posts--<?php echo esc_attr($layout); ?>" data-layout="<?php echo esc_attr($layout); ?>">
			<?php
			$this->render_themer_notice();

			if ($this->has_filters()) {
				$this->render_filters();
			}

			if ('carousel' === $layout) {
				$this->render_carousel();
			} else {
			?>
				<div class="theme-posts__items">
					<?php
					if ($this->is_masonry()) {
						echo '<div class="theme-posts__sizer"></div>';
					}
					$this->render_posts();
					?>
				</div>
			<?php
			}

			$this->render_pagination();
			$this->render_no_results();
			?>
		</div>
	<?php
	}

	/**
	 * Warns in the builder when Beaver Themer is not active. Core Beaver Builder
	 * parses [wpbb] shortcodes but registers no post:* properties, so without
	 * Themer every card's connections come out empty.
	 *
	 * @return void
	 */
	public function render_themer_notice()
	{
		if (defined('FL_THEME_BUILDER_VERSION') || ! function_exists('evek_builder_placeholder')) {
			return;
		}

		echo evek_builder_placeholder(__('Theme Posts', 'fl-builder'), __('The Post Layout uses Beaver Themer field connections. Activate Beaver Themer to fill them in.', 'fl-builder')); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in evek_builder_placeholder().
	}


	/**
	 * Renders the carousel scaffolding around the cards.
	 *
	 * Overlay arrows sit inside the swiper; Outside arrows sit beside it in a
	 * padded wrapper; the pagination and Below arrows follow it in normal flow,
	 * outside its overflow:hidden, and are handed to Swiper by reference.
	 *
	 * @return void
	 */
	public function render_carousel()
	{
		$arrows  = $this->has_arrows();
		$outside = $arrows && 'outside' === $this->get_arrow_position();
		$below   = $arrows && 'below' === $this->get_arrow_position();
		$overlay = $arrows && ! $outside && ! $below;
	?>
		<div class="theme-posts__carousel">
			<?php if ($outside) : ?><div class="theme-posts__carousel-outer"><?php endif; ?>
				<div class="swiper theme-posts__swiper">
					<div class="swiper-wrapper theme-posts__items">
						<?php $this->render_posts(); ?>
					</div>
					<?php
					if ($overlay) {
						$this->render_arrow('prev');
						$this->render_arrow('next');
					}
					?>
					<?php if ('yes' === $this->get('scrollbar', 'no')) : ?>
						<div class="swiper-scrollbar"></div>
					<?php endif; ?>
				</div>
			<?php
			if ($outside) {
				$this->render_arrow('prev');
				$this->render_arrow('next');
				echo '</div>';
			}
			?>
			<?php if ('none' !== $this->get_pagination_type()) : ?>
				<div class="swiper-pagination"></div>
			<?php endif; ?>
			<?php if ($below) : ?>
				<div class="theme-posts__arrows">
					<?php
					$this->render_arrow('prev');
					$this->render_arrow('next');
					?>
				</div>
			<?php endif; ?>
		</div>
	<?php
	}

	/**
	 * Renders one carousel arrow.
	 *
	 * @param string $dir Either 'prev' or 'next'.
	 * @return void
	 */
	public function render_arrow($dir)
	{
		$icon  = ('prev' === $dir)
			? $this->get('prev_icon', 'far fa-arrow-alt-circle-left')
			: $this->get('next_icon', 'far fa-arrow-alt-circle-right');
		$label = ('prev' === $dir) ? __('Previous', 'fl-builder') : __('Next', 'fl-builder');
	?>
		<button type="button" class="theme-posts__arrow theme-posts__arrow--<?php echo esc_attr($dir); ?>" aria-label="<?php echo esc_attr($label); ?>">
			<i class="<?php echo esc_attr($icon); ?>" aria-hidden="true"></i>
		</button>
	<?php
	}

	/**
	 * Renders the taxonomy filter bar.
	 *
	 * @return void
	 */
	public function render_filters()
	{
		$terms = $this->get_filter_terms();

		if (count($terms) < 2) {
			return;
		}
	?>
		<div class="theme-posts__filters" role="group" aria-label="<?php esc_attr_e('Filter posts', 'fl-builder'); ?>">
			<button type="button" class="theme-posts__filter is-active" data-filter="all" aria-pressed="true">
				<?php echo esc_html($this->get('filter_all_label', __('All', 'fl-builder'))); ?>
			</button>
			<?php foreach ($terms as $term) : ?>
				<button type="button" class="theme-posts__filter" data-filter="<?php echo esc_attr($term->term_id); ?>" aria-pressed="false">
					<?php echo esc_html($term->name); ?>
				</button>
			<?php endforeach; ?>
		</div>
		<?php
	}

	/**
	 * Renders every post in the query as a card.
	 *
	 * The loop is advanced with the_post(), so the global post is each card's
	 * post in turn -- the post the [wpbb post:*] shortcodes read.
	 *
	 * @return void
	 */
	public function render_posts()
	{
		$query = $this->get_query();

		if (empty($query->posts)) {
			return;
		}

		$carousel = $this->is_carousel();
		$html     = $this->get_layout_html();

		while ($query->have_posts()) {
			$query->the_post();

			$classes = array('theme-posts__item');

			if ($carousel) {
				$classes[] = 'swiper-slide';
			}

			$terms = $this->get_item_filter_terms(get_the_ID());
		?>
			<div class="<?php echo esc_attr(implode(' ', $classes)); ?>"<?php echo ('' !== $terms) ? ' data-terms="' . esc_attr($terms) . '"' : ''; ?>>
				<div class="theme-posts__card">
					<?php echo $this->render_template($html); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Builder-authored template markup, like the core Post Grid's custom layout; each [wpbb] property escapes its own value. ?>
				</div>
			</div>
		<?php
		}

		wp_reset_postdata();
	}

	/**
	 * The filter taxonomy term ids for a post, as a space separated list the
	 * front-end JS matches against.
	 *
	 * @param int $post_id ID of the post.
	 * @return string
	 */
	public function get_item_filter_terms($post_id)
	{
		if (! $this->has_filters()) {
			return '';
		}

		$terms = wp_get_post_terms($post_id, $this->get_filter_taxonomy(), array('fields' => 'ids'));

		if (is_wp_error($terms) || empty($terms)) {
			return '';
		}

		return implode(' ', array_map('intval', $terms));
	}

	/*
	 * Post layout template ----------------------------------------------------
	 */

	/**
	 * The default post layout HTML: Beaver Themer's own default Post Grid
	 * layout, with this module's classes so css/frontend.css styles it.
	 *
	 * @return string
	 */
	public static function get_default_layout_html()
	{
		return '[wpbb-if post:featured_image]
<div class="theme-posts__thumb">
	[wpbb post:featured_image size="medium_large" display="tag" linked="yes"]
</div>
[/wpbb-if]
<div class="theme-posts__content">
	<h3 class="theme-posts__title">[wpbb post:link text="title"]</h3>
	<div class="theme-posts__meta">[wpbb post:author_name link="yes"] | [wpbb post:date format="F j, Y"]</div>
	<div class="theme-posts__text">[wpbb post:excerpt length="25" more="&hellip;"]</div>
	<div class="theme-posts__cta">[wpbb post:link text="custom" custom_text="Read More"]</div>
</div>';
	}

	/**
	 * The post layout template HTML.
	 *
	 * @return string
	 */
	public function get_layout_html()
	{
		$layout = $this->get('post_layout', null);

		if (is_array($layout)) {
			$layout = (object) $layout;
		}

		if (is_object($layout) && isset($layout->html)) {
			return (string) $layout->html;
		}

		// Beaver Builder merges the nested form's defaults in on load, so this
		// only runs for settings saved before the form existed.
		return self::get_default_layout_html();
	}

	/**
	 * Fills the post layout template for the current post in the loop.
	 *
	 * The same two passes the core Post Grid's custom layout makes: the field
	 * connection parser first, which also reaches [wpbb] shortcodes inside HTML
	 * attributes (href="[wpbb post:url]") that do_shortcode() skips, then
	 * do_shortcode() for [wpbb-if] and any other shortcode in the template.
	 *
	 * @param string $html The post layout template.
	 * @return string
	 */
	public function render_template($html)
	{
		if (class_exists('FLThemeBuilderFieldConnections')) {
			$html = FLThemeBuilderFieldConnections::parse_shortcodes($html);
		}

		return do_shortcode($html);
	}

	/*
	 * Pagination --------------------------------------------------------------
	 */

	/**
	 * Renders the paged navigation below the posts.
	 *
	 * Carousels page themselves, so pagination is skipped for them.
	 *
	 * @return void
	 */
	public function render_pagination()
	{
		$type = $this->get('pagination_type', 'none');

		if ('none' === $type || $this->is_carousel()) {
			return;
		}

		$query = $this->get_query();

		if (empty($query->posts) || (int) $query->max_num_pages < 2) {
			return;
		}

		if ('load_more' === $type) {
			$this->render_load_more($query);
			return;
		}

		$permalink_structure = get_option('permalink_structure');
		$base                = untrailingslashit(wp_specialchars_decode(get_pagenum_link()));
		$current             = max(1, (int) FLBuilderLoop::get_paged());
		?>
		<nav class="theme-posts__pagination" aria-label="<?php esc_attr_e('Posts', 'fl-builder'); ?>">
			<?php
			echo wp_kses_post(
				paginate_links(
					array(
						'base'    => FLBuilderLoop::build_base_url($permalink_structure, $base) . '%_%',
						'format'  => FLBuilderLoop::paged_format($permalink_structure, $base),
						'current' => $current,
						'total'   => (int) $query->max_num_pages,
						'type'    => 'list',
					)
				)
			);
			?>
		</nav>
	<?php
	}

	/**
	 * Renders the Load More button.
	 *
	 * The button carries the next page's URL; the front-end JS fetches that
	 * page, lifts this node's cards out of it and appends them. Rendering the
	 * URL server side keeps the button working as a plain link when JS is off.
	 * It is the theme's own .theme-button, in the style chosen in the settings.
	 *
	 * @param WP_Query $query The query being paged.
	 * @return void
	 */
	public function render_load_more($query)
	{
		$current = max(1, (int) FLBuilderLoop::get_paged());

		if ($current >= (int) $query->max_num_pages) {
			return;
		}

		$permalink_structure = get_option('permalink_structure');
		$base                = untrailingslashit(wp_specialchars_decode(get_pagenum_link()));
		$base                = FLBuilderLoop::build_base_url($permalink_structure, $base);
		$format              = FLBuilderLoop::paged_format($permalink_structure, $base);
		$next                = str_replace('%#%', $current + 1, $base . $format);
		$style               = $this->get('load_more_style', 'primary');

		if (! in_array($style, array('primary', 'secondary', 'tertiary'), true)) {
			$style = 'primary';
		}
	?>
		<div class="theme-posts__pagination theme-posts__pagination--load-more">
			<a class="theme-button <?php echo esc_attr($style); ?> theme-posts__load-more" href="<?php echo esc_url($next); ?>" data-next="<?php echo esc_url($next); ?>" data-total="<?php echo esc_attr((int) $query->max_num_pages); ?>">
				<span><?php echo wp_kses_post($this->get('load_more_text', __('Load More', 'fl-builder'))); ?></span>
			</a>
		</div>
	<?php
	}

	/**
	 * Renders the no-results message and optional search form.
	 *
	 * @return void
	 */
	public function render_no_results()
	{
		$query = $this->get_query();

		if (! empty($query->posts)) {
			return;
		}
	?>
		<div class="theme-posts__empty">
			<p><?php echo wp_kses_post($this->get('no_results_message', __("Sorry, we couldn't find any posts.", 'fl-builder'))); ?></p>
			<?php
			if ('1' === (string) $this->get('show_search', '0')) {
				get_search_form();
			}
			?>
		</div>
<?php
	}
}

/**
 * Fills the taxonomy select when the settings form renders.
 *
 * The options cannot be built at registration: modules load on `init` at the
 * default priority, which is also when most plugins and themes register their
 * custom post types and taxonomies, so whichever ran first would decide
 * whether a taxonomy appeared in the list. Deferring to render time means the
 * whole of `init` has finished by the time the options are read.
 *
 * The field opts in with an `evek_taxonomies` key rather than being matched on
 * its name, so this never reaches a field of the same name in another module.
 * Beaver Builder ignores keys it does not recognise.
 *
 * @param array  $field    The field config about to render.
 * @param string $name     The field name.
 * @param object $settings The settings being rendered.
 * @return array
 */
function evek_posts_taxonomy_field_options($field, $name, $settings)
{
	if (! isset($field['evek_taxonomies'])) {
		return $field;
	}

	$options = ('auto' === $field['evek_taxonomies']) ? array('' => __('Automatic', 'fl-builder')) : array();

	foreach (get_taxonomies(array('public' => true), 'objects') as $taxonomy) {
		$options[$taxonomy->name] = $taxonomy->label;
	}

	$field['options'] = $options;

	return $field;
}
add_filter('fl_builder_render_settings_field', 'evek_posts_taxonomy_field_options', 10, 3);

/**
 * The Post Layout form, opened from the post_layout field. The HTML half of
 * Beaver Themer's custom post layout, with the same field connection "Insert"
 * menu. There is no CSS tab: styles live in the theme.
 */
FLBuilder::register_settings_form(
	'theme_posts_post_layout',
	array(
		'title' => __('Post Layout', 'fl-builder'),
		'tabs'  => array(
			'html' => array(
				'title'    => __('HTML', 'fl-builder'),
				'sections' => array(
					'html' => array(
						'title'  => '',
						'fields' => array(
							'html' => array(
								'type'        => 'code',
								'editor'      => 'html',
								'label'       => '',
								'rows'        => '18',
								'default'     => ThemePostsModule::get_default_layout_html(),
								'help'        => __('Filled in for each post with Beaver Builder field connections, e.g. [wpbb post:title]. Use the + button to insert one; see docs.wpbeaverbuilder.com/beaver-themer/field-connections/syntax for the syntax.', 'fl-builder'),
								'preview'     => array(
									'type' => 'none',
								),
								'connections' => array('html', 'string', 'url', 'color'),
							),
						),
					),
				),
			),
		),
	)
);

/**
 * Register the module and its form settings.
 */
FLBuilder::register_module(
	'ThemePostsModule',
	array(
		'general'    => array(
			'title'    => __('General', 'fl-builder'),
			'sections' => array(
				'layout_section'      => array(
					'title'  => __('Layout', 'fl-builder'),
					'fields' => array(
						'layout'       => array(
							'type'    => 'select',
							'label'   => __('Layout', 'fl-builder'),
							'default' => 'grid',
							'options' => array(
								'grid'     => __('Grid', 'fl-builder'),
								'masonry'  => __('Masonry', 'fl-builder'),
								'list'     => __('List', 'fl-builder'),
								'carousel' => __('Carousel', 'fl-builder'),
							),
							'help'    => __('Grid keeps every card in a row the same height. Masonry lets each card keep its natural height. List stacks one card per row.', 'fl-builder'),
							'toggle'  => array(
								'grid'     => array(
									'fields'   => array('post_columns', 'column_gap', 'row_gap'),
									'sections' => array('filters_section'),
								),
								'masonry'  => array(
									'fields'   => array('post_columns', 'column_gap', 'row_gap'),
									'sections' => array('filters_section'),
								),
								'list'     => array(
									'fields' => array('row_gap'),
								),
								'carousel' => array(
									'tabs' => array('carousel', 'navigation'),
								),
							),
						),
						'post_columns' => array(
							'type'       => 'unit',
							'label'      => __('Columns', 'fl-builder'),
							'default'    => '3',
							'responsive' => array(
								'default' => array(
									'default'    => '3',
									'medium'     => '2',
									'responsive' => '1',
								),
							),
							'slider'     => array(
								'min'  => 1,
								'max'  => 8,
								'step' => 1,
							),
						),
						'column_gap'   => array(
							'type'       => 'unit',
							'label'      => __('Column Gap', 'fl-builder'),
							'default'    => '30',
							'units'      => array('px'),
							'responsive' => true,
							'slider'     => array(
								'min'  => 0,
								'max'  => 120,
								'step' => 1,
							),
						),
						'row_gap'      => array(
							'type'       => 'unit',
							'label'      => __('Row Gap', 'fl-builder'),
							'default'    => '30',
							'units'      => array('px'),
							'responsive' => true,
							'slider'     => array(
								'min'  => 0,
								'max'  => 120,
								'step' => 1,
							),
						),
					),
				),
				'post_layout_section' => array(
					'title'  => __('Post Layout', 'fl-builder'),
					'fields' => array(
						'post_layout' => array(
							'type'         => 'form',
							'label'        => __('Post Layout', 'fl-builder'),
							'form'         => 'theme_posts_post_layout',
							'preview_text' => null,
							'multiple'     => false,
							'help'         => __('The HTML every card is built from, using Beaver Builder field connections such as [wpbb post:title]. Style it in the theme.', 'fl-builder'),
						),
					),
				),
				'filters_section'     => array(
					'title'  => __('Taxonomy Filter', 'fl-builder'),
					'fields' => array(
						'show_filter'      => array(
							'type'    => 'select',
							'label'   => __('Filter Bar', 'fl-builder'),
							'default' => 'no',
							'options' => array(
								'yes' => __('Show', 'fl-builder'),
								'no'  => __('Hide', 'fl-builder'),
							),
							'help'    => __('Filters the posts already on the page. It is hidden when the query returns fewer than two terms.', 'fl-builder'),
							'toggle'  => array(
								'yes' => array(
									'fields' => array('filter_taxonomy', 'filter_all_label'),
								),
							),
						),
						'filter_taxonomy'  => array(
							'type'            => 'select',
							'label'           => __('Taxonomy', 'fl-builder'),
							'default'         => '',
							'options'         => array(),
							'evek_taxonomies' => 'auto',
							'help'            => __('Automatic uses the first taxonomy registered for the queried post type.', 'fl-builder'),
						),
						'filter_all_label' => array(
							'type'    => 'text',
							'label'   => __('"All" Label', 'fl-builder'),
							'default' => __('All', 'fl-builder'),
						),
					),
				),
				'pagination_section'  => array(
					'title'  => __('Pagination', 'fl-builder'),
					'fields' => array(
						'pagination_type'    => array(
							'type'    => 'select',
							'label'   => __('Pagination', 'fl-builder'),
							'default' => 'none',
							'options' => array(
								'none'      => __('None', 'fl-builder'),
								'numbers'   => __('Numbers', 'fl-builder'),
								'load_more' => __('Load More Button', 'fl-builder'),
							),
							'help'    => __('Set the posts per page under the Query tab. Carousels page themselves and ignore this.', 'fl-builder'),
							'toggle'  => array(
								'load_more' => array(
									'fields' => array('load_more_text', 'load_more_style'),
								),
							),
						),
						'load_more_text'     => array(
							'type'    => 'text',
							'label'   => __('Button Text', 'fl-builder'),
							'default' => __('Load More', 'fl-builder'),
						),
						'load_more_style'    => array(
							'type'    => 'select',
							'label'   => __('Button Style', 'fl-builder'),
							'default' => 'primary',
							'options' => array(
								'primary'   => __('Primary', 'fl-builder'),
								'secondary' => __('Secondary', 'fl-builder'),
								'tertiary'  => __('Tertiary', 'fl-builder'),
							),
						),
						'no_results_message' => array(
							'type'    => 'text',
							'label'   => __('No Results Message', 'fl-builder'),
							'default' => __("Sorry, we couldn't find any posts.", 'fl-builder'),
						),
						'show_search'        => array(
							'type'    => 'select',
							'label'   => __('Show Search', 'fl-builder'),
							'default' => '0',
							'options' => array(
								'1' => __('Show', 'fl-builder'),
								'0' => __('Hide', 'fl-builder'),
							),
							'help'    => __('Show a search form when no posts are found.', 'fl-builder'),
						),
					),
				),
			),
		),
		'query'      => array(
			'title' => __('Query', 'fl-builder'),
			'file'  => FL_BUILDER_DIR . 'includes/loop-settings.php',
		),
		'carousel'   => array(
			'title'    => __('Carousel', 'fl-builder'),
			'sections' => array(
				'slides_section'   => array(
					'title'  => __('Slides', 'fl-builder'),
					'fields' => array(
						'slides_per_view' => array(
							'type'        => 'unit',
							'label'       => __('Slides Per View', 'fl-builder'),
							'description' => __('slides', 'fl-builder'),
							'responsive'  => array(
								'default' => array(
									'default'    => '3',
									'large'      => '',
									'medium'     => '2',
									'responsive' => '1',
								),
							),
						),
						'space_between'   => array(
							'type'        => 'unit',
							'label'       => __('Space Between', 'fl-builder'),
							'description' => 'px',
							'responsive'  => array(
								'default' => array(
									'default'    => '20',
									'large'      => '',
									'medium'     => '16',
									'responsive' => '12',
								),
							),
						),
					),
				),
				'behavior_section' => array(
					'title'  => __('Behavior', 'fl-builder'),
					'fields' => array(
						'loop' => array(
							'type'    => 'select',
							'label'   => __('Loop', 'fl-builder'),
							'default' => 'yes',
							'options' => array(
								'yes' => __('Yes', 'fl-builder'),
								'no'  => __('No', 'fl-builder'),
							),
						),
						'centered_slides' => array(
							'type'    => 'select',
							'label'   => __('Center Active Slide', 'fl-builder'),
							'default' => 'no',
							'options' => array(
								'yes' => __('Yes', 'fl-builder'),
								'no'  => __('No', 'fl-builder'),
							),
						),
						'speed' => array(
							'type'        => 'unit',
							'label'       => __('Transition Speed', 'fl-builder'),
							'default'     => '400',
							'placeholder' => '400',
							'description' => 'ms',
						),
						'grab_cursor' => array(
							'type'    => 'select',
							'label'   => __('Grab Cursor', 'fl-builder'),
							'default' => 'yes',
							'options' => array(
								'yes' => __('Yes', 'fl-builder'),
								'no'  => __('No', 'fl-builder'),
							),
						),
						'free_mode' => array(
							'type'    => 'select',
							'label'   => __('Free Mode', 'fl-builder'),
							'default' => 'no',
							'options' => array(
								'yes' => __('Yes', 'fl-builder'),
								'no'  => __('No', 'fl-builder'),
							),
							'help'    => __('Slides move freely without snapping.', 'fl-builder'),
						),
						'keyboard' => array(
							'type'    => 'select',
							'label'   => __('Keyboard Navigation', 'fl-builder'),
							'default' => 'no',
							'options' => array(
								'yes' => __('Yes', 'fl-builder'),
								'no'  => __('No', 'fl-builder'),
							),
						),
						'mousewheel' => array(
							'type'    => 'select',
							'label'   => __('Mousewheel Control', 'fl-builder'),
							'default' => 'no',
							'options' => array(
								'yes' => __('Yes', 'fl-builder'),
								'no'  => __('No', 'fl-builder'),
							),
						),
					),
				),
				'autoplay_section' => array(
					'title'  => __('Autoplay', 'fl-builder'),
					'fields' => array(
						'autoplay' => array(
							'type'    => 'select',
							'label'   => __('Enable Autoplay', 'fl-builder'),
							'default' => 'yes',
							'options' => array(
								'yes' => __('Yes', 'fl-builder'),
								'no'  => __('No', 'fl-builder'),
							),
							'toggle'  => array(
								'yes' => array(
									'fields' => array('autoplay_delay', 'disable_on_interaction', 'pause_on_mouse_enter'),
								),
							),
						),
						'autoplay_delay' => array(
							'type'        => 'unit',
							'label'       => __('Delay', 'fl-builder'),
							'default'     => '3000',
							'placeholder' => '3000',
							'description' => 'ms',
						),
						'disable_on_interaction' => array(
							'type'    => 'select',
							'label'   => __('Disable After Interaction', 'fl-builder'),
							'default' => 'no',
							'options' => array(
								'yes' => __('Yes', 'fl-builder'),
								'no'  => __('No', 'fl-builder'),
							),
						),
						'pause_on_mouse_enter' => array(
							'type'    => 'select',
							'label'   => __('Pause on Hover', 'fl-builder'),
							'default' => 'yes',
							'options' => array(
								'yes' => __('Yes', 'fl-builder'),
								'no'  => __('No', 'fl-builder'),
							),
						),
					),
				),
			),
		),
		'navigation' => array(
			'title'    => __('Navigation', 'fl-builder'),
			'sections' => array(
				'arrows_section'              => array(
					'title'  => __('Arrows', 'fl-builder'),
					'fields' => array(
						'arrows'         => array(
							'type'    => 'select',
							'label'   => __('Show Arrows', 'fl-builder'),
							'default' => 'yes',
							'options' => array(
								'yes' => __('Yes', 'fl-builder'),
								'no'  => __('No', 'fl-builder'),
							),
							'toggle'  => array(
								'yes' => array(
									'fields' => array('prev_icon', 'next_icon', 'arrow_size', 'arrow_color', 'arrow_position'),
								),
							),
						),
						'arrow_color'    => array(
							'type'        => 'color',
							'connections' => array('color'),
							'label'       => __('Arrow Color', 'fl-builder'),
							'default'     => '',
							'show_reset'  => true,
							'show_alpha'  => true,
							'preview'     => array(
								'type' => 'refresh',
							),
						),
						'arrow_size'     => array(
							'type'    => 'unit',
							'label'       => __('Arrow Size', 'fl-builder'),
							'placeholder' => '40',
							'units'   => array('px'),
							'slider'  => array(
								'min'  => 8,
								'max'  => 100,
								'step' => 1,
							),
						),
						'prev_icon'      => array(
							'type'    => 'icon',
							'label'   => __('Previous Arrow Icon', 'fl-builder'),
							'default' => 'far fa-arrow-alt-circle-left',
						),
						'next_icon'      => array(
							'type'    => 'icon',
							'label'   => __('Next Arrow Icon', 'fl-builder'),
							'default' => 'far fa-arrow-alt-circle-right',
						),
						'arrow_position' => array(
							'type'    => 'select',
							'label'   => __('Arrow Position', 'fl-builder'),
							'default' => 'overlay',
							'options' => array(
								'overlay' => __('Overlay', 'fl-builder'),
								'outside' => __('Outside', 'fl-builder'),
								'below'   => __('Below', 'fl-builder'),
							),
							'help'    => __('Overlay centers arrows over the sides. Outside reserves gutters beside the slides. Below places arrows in a block below the carousel.', 'fl-builder'),
							'toggle'  => array(
								'outside' => array(
									'fields' => array('arrow_gutter'),
								),
								'below'   => array(
									'fields' => array('arrow_gap', 'arrow_spacing'),
								),
							),
						),
						'arrow_gutter'   => array(
							'type'    => 'unit',
							'label'       => __('Gutter Width', 'fl-builder'),
							'default'     => '50',
							'placeholder' => '50',
							'description' => 'px',
						),
						'arrow_gap'      => array(
							'type'    => 'unit',
							'label'   => __('Gap Between Arrows', 'fl-builder'),
							'default' => '10',
							'units'   => array('px'),
							'slider'  => array(
								'min'  => 0,
								'max'  => 60,
								'step' => 1,
							),
						),
						'arrow_spacing'  => array(
							'type'       => 'unit',
							'label'      => __('Top Spacing', 'fl-builder'),
							'default'    => '20',
							'units'      => array('px'),
							'responsive' => true,
							'slider'     => array(
								'min'  => 0,
								'max'  => 120,
								'step' => 1,
							),
							'help'       => __('Space above the arrows in the Below layout. With pagination on, the dots sit between the slides and the arrows, so this is the gap below the dots.', 'fl-builder'),
							'preview'    => array(
								'type'     => 'css',
								'selector' => '.theme-posts__arrows',
								'property' => 'padding-top',
								'unit'     => 'px',
							),
						),
					),
				),
				'carousel_pagination_section' => array(
					'title'  => __('Pagination', 'fl-builder'),
					'fields' => array(
						'carousel_pagination'         => array(
							'type'    => 'select',
							'label'   => __('Show Pagination', 'fl-builder'),
							'default' => 'bullets',
							'options' => array(
								'bullets'     => __('Bullets', 'fl-builder'),
								'fraction'    => __('Fraction', 'fl-builder'),
								'progressbar' => __('Progress Bar', 'fl-builder'),
								'none'        => __('None', 'fl-builder'),
							),
							'toggle'  => array(
								'bullets'     => array(
									'fields' => array('carousel_pagination_color', 'dynamic_bullets', 'carousel_pagination_spacing'),
								),
								'fraction'    => array(
									'fields' => array('carousel_pagination_color', 'carousel_pagination_spacing'),
								),
								'progressbar' => array(
									'fields' => array('carousel_pagination_color', 'carousel_pagination_spacing'),
								),
							),
						),
						'carousel_pagination_color'   => array(
							'type'        => 'color',
							'connections' => array('color'),
							'label'       => __('Pagination Color', 'fl-builder'),
							'default'     => '',
							'show_reset'  => true,
							'show_alpha'  => true,
							'preview'     => array(
								'type' => 'refresh',
							),
						),
						'carousel_pagination_spacing' => array(
							'type'       => 'unit',
							'label'      => __('Top Spacing', 'fl-builder'),
							'default'    => '20',
							'units'      => array('px'),
							'responsive' => true,
							'help'       => __('Space between the slides and the pagination.', 'fl-builder'),
							'preview'    => array(
								'type'     => 'css',
								'selector' => '.theme-posts__carousel > .swiper-pagination',
								'property' => 'margin-top',
								'unit'     => 'px',
							),
							'slider'     => array(
								'min'  => 0,
								'max'  => 120,
								'step' => 1,
							),
						),
						'dynamic_bullets'             => array(
							'type'    => 'select',
							'label'   => __('Dynamic Bullets', 'fl-builder'),
							'default' => 'no',
							'options' => array(
								'yes' => __('Yes', 'fl-builder'),
								'no'  => __('No', 'fl-builder'),
							),
						),
					),
				),
				'scrollbar_section'           => array(
					'title'  => __('Scrollbar', 'fl-builder'),
					'fields' => array(
						'scrollbar' => array(
							'type'    => 'select',
							'label'   => __('Show Scrollbar', 'fl-builder'),
							'default' => 'no',
							'options' => array(
								'yes' => __('Yes', 'fl-builder'),
								'no'  => __('No', 'fl-builder'),
							),
						),
					),
				),
			),
		),
	)
);

/*
 * Loads the AJAX suggest-field filter used by the core loop settings on the
 * Query tab (Post Type / Order / taxonomy & author suggest fields).
 */
require_once FL_BUILDER_DIR . 'includes/loop-settings-filter.php';
