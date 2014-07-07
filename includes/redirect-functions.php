<?php
/**
 * Redirect Functions
 *
 * @since 1.0
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;


/**
 * Get Redirects
 *
 * Retrieves an array of all available redirects
 *
 * @since 1.0
 * @param array $args Query arguments
 * @return mixed array if redirects exist, false otherwise
 */
function edd_csr_get_redirects( $args = array() ) {

	$defaults = array(
		'post_type'      => 'edd_redirect',
		'posts_per_page' => 30,
		'paged'          => null,
		'post_status'    => array( 'active', 'inactive' )
	);

	$args = wp_parse_args( $args, $defaults );

	$redirects = get_posts( $args );

	if ( $redirects )
		return $redirects;

	return false;
}


/**
 * Get Redirect post ID from payment ID
 *
 * @since 1.0
*/
function edd_csr_get_redirect_id( $download_id ) {

	$args = array(
		'post_type'			=> 'edd_redirect',
		'posts_per_page'	=> -1,
		'meta_key'			=> '_edd_redirect_download',
		'meta_value'		=> $download_id,
		'post_status'		=> array( 'active', 'inactive' )
	);

	$redirects = get_posts( $args );

	if ( $redirects ) {
		foreach ( $redirects as $redirect ) {
			$redirect_id = $redirect->ID;
		}
		return $redirect_id;
	}

	return false;
}


/**
 * Get page ID
 *
 * @since 1.0
 * @return int $redirect_page ID of the page to redirect to
*/

function edd_csr_get_redirect_page_id( $redirect_id ) {

	$redirect_page = get_post_meta( $redirect_id, '_edd_redirect_page', true );

	if ( $redirect_page )
		return $redirect_page;

	return false;
}


/**
 * Retrieve the redirect page
 *
 * @since 1.0
 * @param int $redirect_id
 * @return int $redirect_page Page ID
 */
function edd_csr_get_redirect_page( $redirect_id = null ) {

	$redirect_page = get_post_meta( $redirect_id, '_edd_redirect_page', true );

	return apply_filters( 'edd_csr_get_redirect_page', $redirect_page, $redirect_id );
}


/**
 * Retrieve the redirect's associated download
 *
 * @since 1.0
 * @param int $redirect_url
 * @return string $url URL
 */
function edd_csr_get_redirect_download( $redirect_download = null ) {
	$download = get_post_meta( $redirect_download, '_edd_redirect_download', true );

	return apply_filters( 'edd_csr_get_redirect_download', $download, $redirect_download );
}

/**
 * Gets all meta key values of a particular meta key
 *
 * @since 1.0
*/

/**
 * Gets all meta key values of a particular meta key
 *
 * @since 1.0
*/
function edd_csr_get_meta_values() {
    global $wpdb;

    $meta_values = $wpdb->get_col( $wpdb->prepare( "
        SELECT pm.meta_value FROM {$wpdb->postmeta} pm
        LEFT JOIN {$wpdb->posts} p ON p.ID = pm.post_id
        WHERE pm.meta_key = '_edd_redirect_download' 
        AND ( p.post_status = 'active' OR p.post_status = 'inactive')
        AND p.post_type = '%s'
    ", 'edd_redirect' ) );

    return $meta_values;
}


/**
 * Stores a redirect. If the redirect already exists, it updates it, otherwise
 * it creates a new one.
 *
 * @since 1.0
 * @param string $details
 * @param int $redirect_id
 * @return bool Whether or not redirect was created
 */
function edd_csr_store_redirect( $details, $redirect_id = null ) {

	$meta = array(
		'download'	=> isset( $details['download'] ) && is_numeric( $details['download'] )	? $details['download'] : '',
		'page'		=> isset( $details['page'] ) && is_numeric( $details['page'] ) ? $details['page'] : '',
	);

	// check if redirect exists
	if ( edd_csr_redirect_exists( $redirect_id ) && ! empty( $redirect_id ) ) {

		// Update an existing redirect

		$details = apply_filters( 'edd_csr_update_redirect', $details, $redirect_id );

		do_action( 'edd_csr_pre_update_redirect', $details, $redirect_id );

		wp_update_post( array(
			'ID'          => $redirect_id,
			'post_title'  => get_the_title( $details['download'] ),
			'post_status' => $details['status']
		) );

		foreach( $meta as $key => $value ) {
			update_post_meta( $redirect_id, '_edd_redirect_' . $key, $value );
		}

		do_action( 'edd_csr_post_update_redirect', $details, $redirect_id );

		// Redirect updated
		return true;

	} else {

		// Add the redirect
		$details = apply_filters( 'edd_csr_insert_redirect', $details );

		do_action( 'edd_csr_pre_insert_redirect', $details );

		// check that the post title (download name) does not already exist
		$redirect_id = wp_insert_post( array(
			'post_type'   => 'edd_redirect',
			'post_title'  => isset( $details['download'] ) ? get_the_title( $details['download'] ) : '',
			'post_status' => 'active'
		) );

		foreach( $meta as $key => $value ) {
			update_post_meta( $redirect_id, '_edd_redirect_' . $key, $value );
		}

		do_action( 'edd_csr_post_insert_redirect', $details, $redirect_id );

		// Redirect created
		return true;

	}
}


/**
 * Deletes a redirect.
 *
 * @since 1.0
 * @param int $redirect_id Redirect ID (default: 0)
 * @return void
 */
function edd_csr_remove_redirect( $redirect_id = 0 ) {
	do_action( 'edd_csr_pre_delete_redirect', $redirect_id );

	wp_delete_post( $redirect_id, true );

	do_action( 'edd_csr_post_delete_redirect', $redirect_id );
}


/**
 * Updates a redirect's status from one status to another.
 *
 * @since 1.0
 * @param int $redirect_id Redirect ID (default: 0)
 * @param string $new_status New status (default: active)
 * @return bool
 */
function edd_csr_update_redirect_status( $redirect_id = 0, $new_status = 'active' ) {
	$redirect = edd_csr_get_redirect( $redirect_id );

	if ( $redirect ) {
		do_action( 'edd_csr_pre_update_redirect_status', $redirect_id, $new_status, $redirect->post_status );

		wp_update_post( array( 'ID' => $redirect_id, 'post_status' => $new_status ) );

		do_action( 'edd_csr_post_update_redirect_status', $redirect_id, $new_status, $redirect->post_status );

		return true;
	}

	return false;
}


/**
 * Checks to see if a redirect already exists.
 *
 * @since 1.0
 * @param int $redirect_id Redirect ID
 * @return bool
 */
function edd_csr_redirect_exists( $redirect_id ) {

	if ( edd_csr_get_redirect( $redirect_id ) )
		return true;

	return false;
}

/**
 * Get Redirect
 *
 * Retrieves a complete redirect by redirect ID.
 *
 * @since 1.0
 * @param string $redirect_id Redirect ID
 * @return array
 */
function edd_csr_get_redirect( $redirect_id ) {

	$redirect = get_post( $redirect_id );

	if ( get_post_type( $redirect_id ) != 'edd_redirect' )
		return false;

	return $redirect;
}


/**
 * Checks whether a redirect is active.
 *
 * @since 1.0
 * @param int $redirect_id
 * @return bool
 */
function edd_csr_is_redirect_active( $redirect_id = null ) {
	$redirect = edd_csr_get_redirect( $redirect_id );
	$return   = false;

	if ( $redirect ) {
		if ( $redirect->post_status == 'active' ) {
			$return = true;
		}
	}

	return apply_filters( 'edd_csr_is_redirect_active', $return, $redirect_id );
}


/**
 * Has Active Redirects
 *
 * Checks if there is any active redirects, returns a boolean.
 *
 * @since 1.0
 * @return bool
 */
function edd_csr_has_active_redirects() {
	$has_active = false;

	$redirects  = edd_csr_get_redirects();

	if ( $redirects) {
		foreach ( $redirects as $redirect ) {
			if ( $redirect->post_status == 'active' ) {
				$has_active = true;
				break;
			}
		}
	}

	return $has_active;
}