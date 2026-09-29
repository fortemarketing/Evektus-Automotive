<?php
/** @package Evektus */
get_header();
?>
<main id="main" class="site-main">
	<div class="content-container">
	<?php while ( have_posts() ) : the_post(); ?>
		<article id="post-<?php the_ID(); ?>" <?php post_class( 'entry' ); ?>><h1><?php the_title(); ?></h1><div class="entry-content"><?php the_content(); ?></div></article>
	<?php endwhile; ?>
	</div>
</main>
<?php get_footer(); ?>
