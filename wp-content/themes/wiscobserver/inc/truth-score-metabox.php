<?php
/**
 * Functions related to the "Truth Score" metabox
 *
 * The score from this metabox is output in the place of the thumbnail image in partials/content.php
 */

/**
 * Getting this standardized across places
 *
 * Why a function to return this array? It turns out that static methods of classes can't access $this
 */
function wisco_truth_score_options_array() {
	return array(
		'' => 'Not Set',
		'0' => 'Unobservable',
		'1' => 'False',
		'2' => 'Mostly False',
		'3' => 'Mostly True',
		'4' => 'Verified'
	);
}

/**
 * Create the "Truth Score" metabox
 */
function wisco_truth_score_metabox_display() {
	global $post;
	$truthiness = wisco_truth_score_for_post( $post );
	wp_nonce_field( 'truth_score', 'truth_score_nonce' );

	$options = wisco_truth_score_options_array();
	?>
		<div id="wisco_truth_score_metabox">
			<label for="truth_score"></label>
			<select name="truth_score" id="truth_score">
				<?php
					foreach ( $options as $value => $label ) {
						printf(
							'<option value="%1$s" %2$s>%3$s</option>',
							esc_attr($value),
							selected( $value, $truthiness, False ),
							esc_attr($label)
						);
					}
				?>
			</select>
		</div>
	<?php
}

/**
 * add the "Truth Score" metabox to the list of metaboxes
 */
function wisco_truth_score_add_meta_box() {
	add_meta_box (
		'wisco_truth_score_metabox',
		__( 'Truth Score', 'wisco' ),
		'wisco_truth_score_metabox_display',
		'post',
		'side',
		'default'
	);
}
add_action( 'admin_init', 'wisco_truth_score_add_meta_box' );

/**
 * Save the "Truth Score" metabox
 */
function wisco_truth_score_save_fields( $post_id ){

	// bail if our nonce isn't set, or if we cannot verify it.
	if ( ! isset( $_POST['truth_score_nonce'] ) || ! wp_verify_nonce( $_POST['truth_score_nonce'] , 'truth_score') ) {
		var_log("bailing");
		return;
	}

	global $post;

	if ( isset( $_POST['truth_score'] ) ) {
		update_post_meta( $post->ID, 'truth_score', esc_attr($_POST['truth_score']) );
	}
}
add_action( 'save_post', 'wisco_truth_score_save_fields' );

/**
 * Get truth score for post
 *
 * @param WP_Post|str|int|null $post Optional; the ID or WP_Post of the post that should be checked for truth score
 * @return string The truth score for a post, or '' if it has none. Truth scores are usually string numbers 0-4
 */
function wisco_truth_score_for_post( $post = null ) {
	$post = get_post( $post );

	$post_custom = get_post_meta( $post->ID, 'truth_score', true );
	$truthiness = ( isset( $post_custom ) ) ? $post_custom : '';

	return $truthiness;
}

/**
 * Given a truth score, get the appropriate image 
 *
 * If you are copying this file to another site, make sure that the filepath in the img src attribute is correct
 * @param str $score '' or 0-4
 * @return HTML for the appropriate image
 *
 * @todo: alt text
 */
function wisco_truth_score_get_graphic_for_score( $score ) {
	if ( is_numeric( $score ) ) {
		$texts = wisco_truth_score_options_array();

		return sprintf(
			'<img class="truth-score truth-score-%2$s" src="%1$s/img/red/%2$s.png" alt="%3$s"/>',
			get_stylesheet_directory_uri(),
			$score,
			$texts[$score]
		);
	}
}

/**
 * Output the appropriate "Truth Score" graphic on single posts
 */
function wisco_truth_score_single_post_action() {
	$score = wisco_truth_score_for_post();
	echo wisco_truth_score_get_graphic_for_score( $score );
}
add_action( 'largo_after_hero', 'wisco_truth_score_single_post_action');
