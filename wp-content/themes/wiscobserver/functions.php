<?php
/**
 * Child Theme for Wisconsin Observer
 */

/**
 * Include compiled style.css
 */
function wisco_styles() {
	wp_dequeue_style( 'largo-child-styles' );
	$suffix = (LARGO_DEBUG)? '' : '.min';
	wp_enqueue_style( 'wisco', get_stylesheet_directory_uri() . '/css/child' . $suffix . '.css' );
}
add_action( 'wp_enqueue_scripts', 'wisco_styles', 20 );
