<?php
/**
 * Functions related to the "Truth Score" metabox
 *
 * The score from this metabox is output in the place of the thumbnail image in partials/content.php
 */

/**
 * Create the "Truth Score" metabox
 */
function wisco_truth_score_metabox_display() {
	global $post;
	$post_custom = get_post_meta( $post->ID, 'truth_score', true );
	$truthiness = ( isset( $post_custom ) ) ? $post_custom : '';
	var_log($truthiness);
	wp_nonce_field( 'truth_score', 'truth_score_nonce' );

	$options = array(
		'' => 'Not Set',
		'0' => 'Unobservable',
		'1' => 'False',
		'2' => 'Mostly False',
		'3' => 'Mostly True',
		'4' => 'Verified'
	);
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
 * Output the appropriate "Truth Score" graphic on single posts
 */
function wisco_truth_score_single_post_action() {
	global $post;
	$score = get_post_meta( $post->ID, 'truth_score', true );
	echo '<h1>truthy: '. esc_attr($score) . '</h1>';
}
add_action( 'largo_after_hero', 'wisco_truth_score_single_post_action');
