<?php get_header() ?>

<div class ="container">
	<div class ="row">
		<section id="site-content" class ="content-area" role ="region" aria-labelledby="page-title-<?php echo $post->ID ?>">
			<?php
			if(have_posts()){
				if(is_tax()){
					global $wp_query;
					$term = $wp_query->get_queried_object();
					$title = $term->name;  // Title, IE name of musician
					$desc = $term->description;	// Description of musician
				}
			}
			if(isset($post)):
				$term_slug = get_query_var( 'term' );
				$taxonomyName = get_query_var( 'taxonomy' );
				$current_term = get_term_by( 'slug', $term_slug, $taxonomyName );
				$term_id = $current_term->term_id;

				if(function_exists('get_wp_term_image')){
					$meta_image = get_wp_term_image($term_id);
				} ?>

				<article>
					<header>
						<h1 id="page-title-<?php echo $term_id; ?>"><?php echo $title; ?></h1>
						<?php // Show Edit in Backend button for logged in Editors and Admins
						if(is_user_logged_in() && current_user_can('edit_posts')):
							$url = get_bloginfo('url').'/wp-admin/term.php?taxonomy=concert_musicians&tag_ID='.$term_id; ?>
						<?php endif; ?>
					</header>
					<div class="entry-content">
						<div class="row">
							<!-- Show taxonomy image -->
							<?php if($meta_image) { ?>
								<div class="pull-left" style="margin-right:2%;">
									<img src="<?php echo $meta_image; ?>" alt="<?php echo $title; ?>" style="width:150px;height:150px;">
								</div>
							<?php } ?>
							<div style="margin-right:2%;height:100px;">
								<strong><?php _e( 'Plays in', 'beyond-expectations-child-ngo' ); ?>:</strong><br />
								<!-- Loop through all productions that taxonomy is member of.. -->
								<?php while ( have_posts() ) : the_post(); ?>
									<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a><br />
								<?php endwhile; ?>
							</div>
							<div class="pull-left" style="margin-right:2%;">
								<?php echo wpautop($desc); ?>
							</div>
						</div>
					</div>
					<div class="entry-content" margin-top: 10px;">
						<div class="row">
<!--          <div class="pull-left" style="margin-left:2%">
								<time datetime="<?php echo get_the_date('Y-m-d\TH:i:s'); ?>"><?php echo get_the_date('j F, Y H:i') ?></time><br/>
								<?php echo get_the_author_posts_link(); ?>
						</div> -->
						</div>
					</div>
	<!-- No Tags for actors, but we keep for now...
		<?php if(wp_get_post_tags($post->ID)): ?>
			<div class="article-box__tags">
				<a name="taggar" class="anchor"></a>
				<?php get_template_part('parts/meta/tags'); ?>
			</div>
	-->
				<?php endif ?>

	<!--	FOOTER, not needed, but we keep.
				<footer class="article-box__footer">
					<div class="article-box__footer__col article-box__footer__col--meta">
						<time datetime="<?php echo get_the_date('Y-m-d\TH:i:s'); ?>"><?php //echo get_the_date('j F, Y H:i') ?></time>
					</div>
					<div class="article-box__footer__col article-box__footer__col--meta">
						<?php //_e('Författare', 'wally') ?>:
						<?php echo get_the_author_posts_link(); ?>
					</div>
					</footer>-->
				</article>
				<?php //do_action("wally_after-post-loop-item");
			endif;
				 ///endwhile;
				// endif;
					//do_action("wally_after_post_loop");
					comments_template();
				?>
		</section>
	</div>
</div>
<?php get_sidebar() ?>
<?php get_footer() ?>
