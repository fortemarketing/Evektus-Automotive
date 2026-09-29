<?php

defined('ABSPATH') || exit;

$attrs = array(
	'class' => explode(' ', $module->get_classname()),
);

$button_node_id = "fl-node-$id";
if (isset($settings->id) && ! empty($settings->id)) {
	$button_node_id = esc_attr($settings->id);
}

$tag_name    = $module->get_tag_name();
$is_lightbox = isset($settings->click_action) && 'lightbox' === $settings->click_action;
$button_text = isset($settings->text) ? trim((string) $settings->text) : '';

// get_target() already appends rel for link tags, so build the opening tag
// attributes from the module helpers and drop any empty pieces.
$element_attributes = join(' ', array_filter(array(
	$module->get_tag(),
	$module->get_link(),
	$module->get_label(),
	$module->get_target(),
)));

// Modifier classes for the button element itself. `theme-button` and its style
// variant are defined in assets/css/theme.css, shared with Theme Info Box.
$button_classes = 'theme-button ' . $module->get_button_style();

if ($is_lightbox) {
	$button_classes .= ' ' . $button_node_id . ' theme-button-lightbox';
}

// Extra attributes that depend on the click action.
$extra_attrs = '';
if (! $is_lightbox && isset($settings->link_download) && 'yes' === $settings->link_download) {
	$extra_attrs .= ' download';
}
if (isset($settings->click_action) && 'copy_text' === $settings->click_action) {
	$extra_attrs .= ' aria-live="polite"';

	if (! empty($settings->copy_text)) {
		$extra_attrs .= ' data-copy-text="' . esc_attr($settings->copy_text) . '"';
	}
	if (! empty($settings->copy_success_message)) {
		$extra_attrs .= ' data-copy-success-message="' . esc_attr($settings->copy_success_message) . '"';
	}
}

?>
<div <?php $module->render_attributes($attrs); ?>>
	<<?php echo $element_attributes; ?> class="<?php echo esc_attr($button_classes); ?>" <?php echo $extra_attrs; ?>>
		<?php if (! empty($settings->icon) && (! isset($settings->icon_position) || 'before' === $settings->icon_position)) : ?>
			<span class="theme-button-icon theme-button-icon-before <?php echo esc_attr($settings->icon); ?>" aria-hidden="true"></span>
		<?php endif; ?>
		<?php if ('' !== $button_text) : ?>
			<span class="theme-button-text"><?php echo esc_html($button_text); ?></span>
		<?php endif; ?>
		<?php if (! empty($settings->icon) && 'after' === $settings->icon_position) : ?>
			<span class="theme-button-icon theme-button-icon-after <?php echo esc_attr($settings->icon); ?>" aria-hidden="true"></span>
		<?php endif; ?>
	</<?php echo esc_attr($tag_name); ?>>
	<?php if ($is_lightbox && 'html' === $settings->lightbox_content_type && isset($settings->lightbox_content_html)) : ?>
		<div class="<?php echo esc_attr($button_node_id); ?> theme-button-lightbox-content mfp-hide">
			<?php echo $settings->lightbox_content_html; ?>
		</div>
	<?php endif; ?>
</div>