<?php
/** @package Evektus */
get_header();
?>
<main id="main" class="site-main">
	<div class="content-container">
		<?php if ( have_posts() ) : ?>
			<?php while ( have_posts() ) : the_post(); ?>
				<article id="post-<?php the_ID(); ?>" <?php post_class( 'entry' ); ?>>
					<?php if ( ! is_singular() ) : ?><h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2><?php endif; ?>
					<div class="entry-content"><?php is_singular() ? the_content() : the_excerpt(); ?></div>
				</article>
			<?php endwhile; ?>
			<?php the_posts_pagination(); ?>
		<?php else : ?>
			<h1><?php esc_html_e( 'Nothing found', 'evek' ); ?></h1>
		<?php endif; ?>
	</div>
</main>
<?php get_footer(); ?>
