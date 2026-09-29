<?php
/**
 *  Theme Counter front-end file
 *
 *  @package Evektus
 */

defined( 'ABSPATH' ) || exit;

$fm_layout = $module->get_layout();

?>
<div class="<?php echo esc_attr( $module->get_classname() ); ?>">
	<?php if ( 'circle' === $fm_layout ) : ?>
		<div class="theme-counter-circle">
			<?php $module->render_circle_bar(); ?>
			<div class="theme-counter-text">
				<?php
				$module->render_before_number_text();
				$module->render_media( 'above-title' );
				$module->render_number();
				$module->render_separator();
				$module->render_media( 'below-title' );
				$module->render_after_number_text();
				?>
			</div>
		</div>
	<?php elseif ( 'semi-circle' === $fm_layout ) : ?>
		<div class="theme-counter-semi-circle">
			<div class="theme-counter-gauge">
				<?php $module->render_semi_circle_bar(); ?>
				<div class="theme-counter-text">
					<?php $module->render_number(); ?>
				</div>
			</div>
			<?php $module->render_counter_texts(); ?>
		</div>
		<?php
	elseif ( 'bars' === $fm_layout ) :
		$fm_position = isset( $settings->number_position ) ? $settings->number_position : 'default';
		?>
		<div class="theme-counter-text theme-counter-position-<?php echo esc_attr( $fm_position ); ?>">
			<?php
			$module->render_before_number_text();

			// Number above the bar.
			if ( 'above' === $fm_position ) {
				$module->render_number();
			}
			?>
			<div class="theme-counter-bar-track">
				<div class="theme-counter-bar">
					<?php
					// Number inside the bar.
					if ( 'default' === $fm_position ) {
						$module->render_number();
					}
					?>
				</div>
			</div>
			<?php
			// Number below the bar.
			if ( 'below' === $fm_position ) {
				$module->render_number();
			}

			$module->render_after_number_text();
			?>
		</div>
	<?php else : ?>
		<div class="theme-counter-inner">
			<?php
			// Image / icon to the left of the whole text block.
			$module->render_media( 'left' );
			?>
			<div class="theme-counter-text">
				<?php
				$module->render_before_number_text();

				// Image / icon above the number.
				$module->render_media( 'above-title' );

				// The number (with the image / icon inline when set to left /
				// right of the number).
				$module->render_title();

				$module->render_separator();

				// Image / icon below the number and separator.
				$module->render_media( 'below-title' );

				$module->render_after_number_text();
				?>
			</div>
			<?php
			// Image / icon to the right of the whole text block.
			$module->render_media( 'right' );
			?>
		</div>
	<?php endif; ?>
</div>
