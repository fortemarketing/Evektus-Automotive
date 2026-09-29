<?php
/** Pages intentionally have no theme container so Beaver Builder controls width and spacing. @package Evektus */
get_header();
?>
<main id="main" class="site-main">
	<?php while ( have_posts() ) : the_post(); ?>
		<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>><?php the_content(); ?></article>
	<?php endwhile; ?>
</main>
<?php get_footer(); ?>
