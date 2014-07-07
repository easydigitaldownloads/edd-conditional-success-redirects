<?php
/**
 * Post Type Functions
 *
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Registers and sets up the Downloads custom post type
 *
 * @since 1.0
 * @return void
 */
function edd_csr_setup_post_type() {

	// Redirect Post Type
	$redirect_labels = array(
		'name' 				=> _x( 'Redirects', 'post type general name', 'edd-csr' ),
		'singular_name' 	=> _x( 'Redirect', 'post type singular name', 'edd-csr' ),
		'add_new' 			=> __( 'Add New', 'edd-csr' ),
		'add_new_item' 		=> __( 'Add New Redirect', 'edd-csr' ),
		'edit_item' 		=> __( 'Edit Redirect', 'edd-csr' ),
		'new_item' 			=> __( 'New Redirect', 'edd-csr' ),
		'all_items' 		=> __( 'All Redirects', 'edd-csr' ),
		'view_item' 		=> __( 'View Redirect', 'edd-csr' ),
		'search_items' 		=> __( 'Search Redirects', 'edd-csr' ),
		'not_found' 		=> __( 'No Redirects found', 'edd-csr' ),
		'not_found_in_trash'=> __( 'No Redirects found in Trash', 'edd-csr' ),
		'parent_item_colon' => '',
		'menu_name' 		=> __( 'Redirects', 'edd-csr' )
	);

	$redirect_args = array(
		'labels' 			=> apply_filters( 'edd_csr_redirect_labels', $redirect_labels ),
		'public' 			=> false,
		'query_var' 		=> false,
		'rewrite' 			=> false,
		'show_ui'           => false,
		'capability_type' 	=> 'manage_shop_settings',
		'map_meta_cap'      => true,
		'supports' 			=> array( 'title' ),
		'can_export'		=> true
	);
	register_post_type( 'edd_redirect', $redirect_args );
}
add_action( 'init', 'edd_csr_setup_post_type', 1 );