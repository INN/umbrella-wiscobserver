<?php
global $shown_ids, $post;

$sticky = get_option( 'sticky_posts' );
if (empty($sticky))
	return;

$args = array(
	'posts_per_page' => 1,
	'post__in'  => $sticky,
	'ignore_sticky_posts' => 1
);

$query = new WP_Query( $args );

if ( $query->have_posts() ) {
	while ( $query->have_posts() ) {

		// Set us up all the vars
		$query->the_post();
		$shown_ids[] = get_the_ID();
		$hero_class = largo_hero_class( $post->ID, FALSE );
		$values = get_post_custom( $post->ID );

		// Begin display

		if ( $sticky && $sticky[0] && ! is_paged() ) { ?>

			<article id="post-<?php the_ID(); ?>" <?php post_class('clearfix entry-content '); ?>>

				<?php if ( has_post_thumbnail() ) { ?>

					<header>
						<div class="hero span12 <?php echo $hero_class; ?>">
						<?php
							if( has_post_thumbnail() ){
								echo('<a href="' . get_permalink() . '" title="' . the_title_attribute( array( 'before' => __( 'Permalink to', 'largo' ) . ' ', 'echo' => false )) . '" rel="bookmark">');
								the_post_thumbnail( 'full' );
								echo('</a>');
							}
						?>
						</div>
					</header>

				<?php }

				$entry_classes = 'entry-content ';

				?>
				<div class="<?php echo $entry_classes?>">

					<?php
						largo_maybe_top_term();
					?>

					<?php
						// output the truth thingy if it's set
						$truth_thumbnail = wisco_truth_score_get_graphic_for_score( wisco_truth_score_for_post( $post ) );

						if ( ! empty ( $truth_thumbnail ) ) {
							echo '<div class="has-thumbnail '.$hero_class.'"><a href="' . get_permalink() . '">' . $truth_thumbnail  . '</a></div>';
						}

					?>

					<h2 class="entry-title"><a href="<?php the_permalink(); ?>" title="<?php the_title_attribute( array( 'before' => __( 'Permalink to', 'largo' ) . ' ' ) )?>" rel="bookmark"><?php the_title(); ?></a></h2>

					<h5 class="byline"><?php largo_byline(); ?></h5>

					<div class="entry-content">
					<?php
						largo_excerpt( $post, 2 );
						$shown_ids[] = get_the_ID();
					?>

					</div>
				</div> <!-- end sticky-solo or sticky-related -->
			</article>
		<?php } // is_paged
	} // end of while loop;
	wp_reset_postdata();
}
