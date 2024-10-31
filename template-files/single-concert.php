<?php get_header(); ?>
<div id="main-content" class="content-area">
	<?php if ( have_posts()) : ?>
		<?php while ( have_posts() ) : the_post(); ?>
			<article id="post-<?php the_ID(); ?>" <?php post_class('cf'); ?>>
				<header class="entry-header">
					<?php the_title(sprintf('<h1 class="entry-title"><a href="%s">', esc_url( get_permalink() ) ), '</a></h1>'); ?>
				</header>
				<?php if (comments_open()) { ?>
					<div class="entry-content-container">
						<div class="entry-metadata">
							<div class="pull-left" style="padding-right: 15px;">
								<?php echo the_post_thumbnail('thumbnail'); ?>
							</div>
							<?php beyond_expectations_entry_meta(); ?>
						</div>
						<div class="entry-content">
							<?php the_content(); ?>
							<?php wp_link_pages(); ?>
							<?php beyond_expectations_entry_taxonomies(); ?>
						</div>
					</div>
				<?php } else { ?>
					<div class="entry-content-container">
						<div class="entry-metadata">
							<div class="pull-left" style="padding-right: 15px;">
								<?php echo the_post_thumbnail('thumbnail'); ?>
							</div>
						</div>
						<div class="entry-content">
							<?php the_content(); ?>
							<?php wp_link_pages(); ?>
							<?php beyond_expectations_entry_taxonomies(); ?>
						</div>
					</div>
				<?php } ?>
			</article>
			<?php comments_template(); ?>

		<?php endwhile; ?>
	<?php else : ?>
		<?php get_template_part('template-parts/content', 'none'); ?>
	<?php endif; ?>
</div>
<div class="entry-info">
	<div style="margin-left:3%;">
		<p><strong><?php _e( 'Information', 'beyond-expectations-child-ngo' ); ?>: </strong></p><br/>
	</div>
	<small>
	<?php $firstplaybexpsconsing = esc_html( get_post_meta( get_the_ID(), 'ngoc_first_performance', true ) );?>
	<?php if( ! empty($firstplaybexpsconsing)){?>
		<div style="margin-left:3%;padding-bottom: 5px";>
			<b><?php _e( 'Premiere', 'beyond-expectations-child-ngo' ); ?>: </b><?php echo wpautop( $firstplaybexpsconsing ); ?>
		</div>
	<?php } ?>
	<?php $lastplaybexpsconsing = esc_html( get_post_meta( get_the_ID(), 'ngoc_last_performance', true ) ); ?>
	<?php if(! empty($lastplaybexpsconsing)){?>
		<div style="margin-left:3%;">
			<b><?php _e( 'Last gig', 'beyond-expectations-child-ngo' ); ?>: </b><?php echo wpautop( $lastplaybexpsconsing ); ?>
		</div>
	<?php } ?>
	<?php $conperformbexsing = esc_html( get_post_meta( get_the_ID(), 'ngoc_performances', true ) );?>
	<?php if(! empty($conperformbexsing)){ ?>
		<div style="margin-left: 3%;">
			<br/><b><?php _e( 'Number of gigs', 'beyond-expectations-child-ngo' ); ?>: </b><?php echo wpautop( $conperformbexsing ); ?>
		</div>
	<?php } ?>
	<div style="margin-left:3%;">
		<?php the_terms($post->ID, 'concert_scenes', '<b>' . __('Venues', 'beyond-expectations-child-ngo') . ':</b><br/>', '<br/>'); ?>
	</div><br/>
	<div style="margin-left: 3%;">
		<?php the_terms($post->ID, 'concert_category', '<b>' . __('Category', 'beyond-expectations-child-ngo') . ':</b><br/>', '<br/>'); ?>
	</div>
	<br/>
	<div style="margin-left:3%;">
		<?php the_terms($post->ID, 'concert_musicians', '<b>' . __('Musicians', 'beyond-expectations-child-ngo') . ':</b> <br/>', '<br/>'); ?>
	</div>
	<br/>
	<?php $conmetainfobexsing = esc_html( get_post_meta( get_the_ID(), 'concert_meta_info', true ) ); ?>
	<?php if(! empty($conmetainfobexsing)){?>
		<div style="margin-left:3%;">
			<b><?php _e('Other coworkers', 'beyond-expectations-child-ngo')?>: </b><br/><?php echo wpautop( $conmetainfobexsing ); ?>
		</div>
	<?php } ?>
	<?php $conticketbexsing = esc_html( get_post_meta( get_the_ID(), 'ngoc_ticket_url', true ) );?>
	<?php if(! empty($conticketbexsing)){ ?>
		<br/><div style="margin-left:3%;background:lightgrey;">
			<b><?php _e('Buy tickets here', 'beyond-expectations-child-ngo')?>: </b><br/><a href="<?php echo $conticketbexsing; ?>" target="_blank"><?php echo $conticketbexsing;?></a>
		</div>
	<?php	} ?>

	</small>
<?php if( function_exists( 'adrotate_group' ) ) { echo "&nbsp;" . adrotate_group(3); } ?>
</div>

<?php get_footer(); ?>
