<?php
/**
 *  Theme Info Box front-end file
 *
 *  @package Evektus
 */

defined( 'ABSPATH' ) || exit;

?>
<div class="<?php echo esc_attr( $module->get_classname() ); ?>">
	<?php $module->render_box_link(); ?>
	<div class="theme-info-box-inner">
		<?php
		// Image / icon to the left of the whole content block.
		$module->render_media( 'left' );
		?>
		<div class="theme-info-box-content">
			<?php
			// Image / icon above the heading.
			$module->render_media( 'above-title' );

			// Title prefix and title (with the image / icon inline when set to
			// left / right of the heading).
			$module->render_title();

			// Image / icon below the heading.
			$module->render_media( 'below-title' );

			// Separator rule.
			$module->render_separator();

			if ( ! empty( $settings->text ) || ( isset( $settings->cta_type ) && in_array( $settings->cta_type, array( 'link', 'button' ), true ) ) ) {
				?>
				<div class="theme-info-box-text-wrap">
					<?php
					// Description.
					$module->render_text();

					// Text link call to action.
					$module->render_link();

					// Button call to action.
					$module->render_button();
					?>
				</div>
				<?php
			}
			?>
		</div>
		<?php
		// Image / icon to the right of the whole content block.
		$module->render_media( 'right' );
		?>
	</div>
</div>
