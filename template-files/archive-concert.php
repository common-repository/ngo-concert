<?php
/*
Template Name: Visa konserter
*/
get_header();
$concert_post = array( 'post_type' => 'concert', );
$loop = new WP_Query( $concert_post );
?>

<div id="main-content" class="content-area">
	<?php if ($loop->have_posts()) : ?>
		<h1 class="archive-header"><?php echo _e('Concerts', 'beyond-expectations-child-ngo' ); ?>: </h1>
		<?php while ( $loop->have_posts() ) : $loop->the_post(); ?>
			<?php //get_template_part('template-parts/content', 'archive'); ?>
			<article id="post-<?php $post->ID; ?>" <?php post_class('cf'); ?>>
				<header class="entry-header">
					<?php the_title(sprintf('<h2 class="entry-title"><a href="%s">', esc_url(get_permalink())), '</a></h2>'); ?><br/>
						</header>

					<div class="entry-content-container">
						<div class="entry-metadata">
							<?php //beyond_expectations_entry_posted_on(); ?>
							<?php //echo the_post_thumbnail('thumbnail'); ?>
						</div>

						<div class="entry-content">
							<div class="fw-page-builder-content">
								<div class="fw-main-row ">
									<div class="fw-container">
										<div class="fw-row">
											<div class="pull-left" style="padding-right: 15px;">
												<?php echo the_post_thumbnail('thumbnail'); ?>
											</div>
											<?php $firstplaybexpcon = esc_html( get_post_meta( get_the_ID(), 'ngoc_first_performance', true ) );
											if( ! empty($firstplaybexpcon)){?>
												<div style="padding-bottom: 3px"><b><?php _e( 'Premiere', 'beyond-expectations-child-ngo' ); ?>: </b> <?php echo $firstplaybexpcon; ?></div>
											<?php }?>
											<?php $lastplaybexpcon = esc_html( get_post_meta( get_the_ID(), 'ngoc_last_performance', true ) );
											if(! empty($lastplaybexpcon)){?>
												<div style="padding-bottom: 3px"><b><?php _e( 'Ends', 'beyond-expectations-child-ngo' ); ?>: </b> <?php echo $lastplaybexpcon; ?></div>
											<?php }?>
											<?php $prodperformencesbexpcon = esc_html( get_post_meta( get_the_ID(), 'ngoc_performances', true ) );
											if(! empty($prodperformencesbexpcon)){?>
												<div style="padding-bottom: 3px"><b><?php _e( 'Number of gigs', 'beyond-expectations-child-ngo' ); ?>:</b> <?php echo $prodperformencesbexpcon; ?></div>
											<?php }
											the_terms( $post->ID, 'concert_scenes', '<div style="padding-bottom: 3px"><b>' . __('Venues', 'beyond-expectations-child-ngo') . ':</b> ', ', ', '</div>' );?>
											<div><?php the_content() ?></div>
										</div>
									</div>
								</div>
							</div>
							<!--<button type="buttom" class="btn btn-default">
								<a href="<?php echo get_permalink(); ?>"><?php _e('Read More', 'beyond-expectations'); ?></a>
							</button> -->
							<?php wp_link_pages(); ?>
							<?php beyond_expectations_entry_taxonomies(); ?>
						</div>
				</div> <!--end entry-content-container-->
			</article>
			<hr align="left" size="2"/>

	 <?php endwhile; ?>
		<?php beyond_expectations_paging_navigation_setup(); ?>
	 <?php else : ?>
		<?php get_template_part('template-parts/content', 'none'); ?>
	<?php endif; ?>
</div>
<div>
<?php get_sidebar(); ?>
<?php get_footer(); ?>