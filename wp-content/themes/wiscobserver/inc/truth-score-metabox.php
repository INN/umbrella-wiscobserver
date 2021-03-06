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
function wisco_truth_score_get_active_options() {
	$defaults = array(
		0 => array(
			'value' => 0,
			'label' => 'Unobservable',
			'image' => get_stylesheet_directory_uri() . '/img/red/0.png',
		),
		1 => array(
			'value' => 1,
			'label' => 'False',
			'image' => get_stylesheet_directory_uri() . '/img/red/1.png',
		),
		2 => array(
			'value' => 2,
			'label' => 'Mostly False',
			'image' => get_stylesheet_directory_uri() . '/img/red/2.png',
		),
		3 => array(
			'value' => 3,
			'label' => 'Mostly True',
			'image' => get_stylesheet_directory_uri() . '/img/red/3.png',
		),
		4 => array(
			'value' => 4,
			'label' => 'Verified',
			'image' => get_stylesheet_directory_uri() . '/img/red/4.png',
		),
	);

	$options = maybe_unserialize( get_option( 'truth_score_mappings' ) );
	if ( ! is_array( $options ) ) { $options = array(); }

	$return = array_replace( $defaults, $options );

	return $return;
}

function wisco_truth_score_options_array() {
	$options =  wisco_truth_score_get_active_options();
	$return = wp_list_pluck( $options, 'label', 'value' );
	$return[ '' ] = 'Not Set'; // add the unset value
	return $return;
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
		$options = wisco_truth_score_get_active_options();

		return sprintf(
			'<img class="truth-score truth-score-%2$s" src="%1$s" alt="%2$s"/>',
			$options[$score]['image'],
			$options[$score]['label']
		);
	}

	return '';
}

/**
 * Output the appropriate "Truth Score" graphic on single posts
 */
function wisco_truth_score_single_post_action() {
	$score = wisco_truth_score_for_post();
	echo wisco_truth_score_get_graphic_for_score( $score );
}
add_action( 'largo_after_hero', 'wisco_truth_score_single_post_action');

/**
 *
 * Truth Score label and image settings page
 *
 */

/**
 * Register option page.
 *
 * @since v0.1
 */
function truth_score_plugin_menu() {
	add_options_page(
		'Truth Score', 	// $page_title title of the page.
		'Truth Score', 	                // $menu_title the text to be used for the menu.
		'manage_options', 				// $capability required capability for display.
		'truth-score', 	// $menu_slug unique slug for menu.
		'truth_score_option_page_html' 			// $function callback.
	);
}
add_action( 'admin_menu', 'truth_score_plugin_menu' );


/**
 * Output the HTML for the option page.
 *
 * @since v0.1
 */
function truth_score_option_page_html() {
	if ( !current_user_can( 'manage_options' ) ) {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}

	echo '<div class="wrap">';
		echo '<h2>Truth Score Options</h2>';
		echo '<form method="post" action="options.php">';
			settings_fields( 'truth-score' );
			do_settings_sections( 'truth-score' );
			submit_button();
		echo '</form>';
	echo '</div>'; // div.wrap
}

/**
 * Registers options for the thing
 */
function truth_score_register_options() {
	add_settings_section(
		'truth_score_mappings_options',
		'Truth Score Types',
		'truth_score_mappings_intro',
		'truth-score'
	); // id, title, callback, page
	
	add_settings_field(
		'truth_score_mappings',
		'Truth Score Labels and Images',
		'truth_score_mappings_fields',
		'truth-score',
		'truth_score_mappings_options'
	); // id, title, callback, page, section, args

	register_setting( 'truth-score', 'truth_score_mappings', 'truth_score_mappings_save' );
}
add_action( 'admin_init', 'truth_score_register_options' );

function truth_score_mappings_intro() {
	echo "Change the labels and images that will be used for the various truth score levels";
}

function truth_score_mappings_fields() {
	$scores = wisco_truth_score_get_active_options();

	foreach ( $scores as $score ) {
		?>
			<h5>
				<?php
					printf(
						__( 'Truth Score %1$s', 'truth-score' ),
						$score['value']
					);
				?>
			</h5>

			<input
				name="truth_score_mappings[]"
				type="hidden"
				value="<?php echo $score['value']; ?>"
			/>

			<label for="truth_score_mappings[]">
				<?php
					_e( 'Label', 'truth-score' );
				?>
			</label>
			<input
				name="truth_score_mappings[]"
				placeholder="<?php echo $score['label']; ?>"
				type="text"
				class="medium-text"
				value="<?php echo $score['label']; ?>"
			/>
			<br />

			<label for="truth_score_mappings[]">
				<?php
					_e( 'Image URL', 'truth-score' );
				?>
			</label>
			<input
				name="truth_score_mappings[]"
				placeholder="<?php echo $score['image']; ?>"
				type="text"
				class="medium-text"
				value="<?php echo $score['image']; ?>"
			/>
			<br/>
		<?php
	}
}

function truth_score_mappings_save( $submitted ) {
	$options = array();

	// This if check is a workaround for https://core.trac.wordpress.org/ticket/21989
	// Look at the $defaults in wisco_truth_score_get_active_options
	// If the first value of the thing is an array, then this has already been sanitized by this function.
	// The form submission is a flat array.
	if ( is_array( $submitted[0] ) ) {
		return $submitted;
	}
	$groups = array_chunk( $submitted, 3 ); // split an array of answers into an array of two-part arrays

	foreach ( $groups as $group ) {
		$options[] = array(
			'value' => $group[0],
			'label' => $group[1],
			'image' => $group[2]
		);
	}


	return $options;
}
