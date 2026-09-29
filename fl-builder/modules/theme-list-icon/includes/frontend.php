<?php
/**
 *  Theme List Icon front-end file
 *
 *  @package Evektus
 */

defined( 'ABSPATH' ) || exit;

$fm_items = $module->get_items();

if ( empty( $fm_items ) ) {
	echo evek_builder_placeholder( __( 'Theme List Icon', 'fl-builder' ), __( 'Add a list item to show the list.', 'fl-builder' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in evek_builder_placeholder().
	return;
}

$fm_media = $module->get_media_html();
$fm_tag   = $module->get_text_tag();

?>
<ul class="<?php echo esc_attr( $module->get_classname() ); ?>" role="list">
	<?php foreach ( $fm_items as $fm_title ) : ?>
		<li class="theme-list-icon-item">
			<?php echo $fm_media; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Built and escaped in get_media_html(). ?>
			<div class="theme-list-icon-text">
				<<?php echo esc_attr( $fm_tag ); ?> class="theme-list-icon-heading"><?php echo wp_kses_post( $fm_title ); ?></<?php echo esc_attr( $fm_tag ); ?>>
			</div>
		</li>
	<?php endforeach; ?>
</ul>
