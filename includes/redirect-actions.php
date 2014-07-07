<?php
/**
 * Redirect Actions
 *
 * @since 1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Sets up and stores a new redirect 
 *
 * @since 1.0
 * @param array $data Redirect data
 * @uses edd_store_redirect()
 * @return void
 */
function edd_csr_add_redirect( $data ) {
	if ( isset( $data['edd-redirect-nonce'] ) && wp_verify_nonce( $data['edd-redirect-nonce'], 'edd_redirect_nonce' ) ) {
		// Setup the redirect details
		$posted = array();

		foreach ( $data as $key => $value ) {
			if ( $key != 'edd-redirect-nonce' && $key != 'edd-action' && $key != 'edd-redirect' ) {
				if ( is_string( $value ) || is_int( $value ) )
					$posted[ $key ] = strip_tags( addslashes( $value ) );
				elseif ( is_array( $value ) )
					$posted[ $key ] = array_map( 'absint', $value );
			}
		}

		// Set the redirect default status to active
		$posted['status'] = 'active';

		if ( edd_csr_store_redirect( $posted ) ) {
			wp_redirect( add_query_arg( 'edd-message', 'redirect_added', $data['edd-redirect'] ) ); edd_die();
		} else {
			wp_redirect( add_query_arg( 'edd-message', 'redirect_add_failed', $data['edd-redirect'] ) ); edd_die();
		}		
	}
}
add_action( 'edd_add_redirect', 'edd_csr_add_redirect' );


/**
 * Saves an edited redirect
 *
 * @since 1.0
 * @param array $data Redirect data
 * @return void
 */
function edd_csr_edit_redirect( $data ) {
	if ( isset( $data['edd-redirect-nonce'] ) && wp_verify_nonce( $data['edd-redirect-nonce'], 'edd_redirect_nonce' ) ) {
		// Setup the redirect details
		$redirect = array();

		foreach ( $data as $key => $value ) {
			if ( $key != 'edd-redirect-nonce' && $key != 'edd-action' && $key != 'redirect-id' && $key != 'edd-redirect' ) {
				if ( is_string( $value ) || is_int( $value ) )
					$redirect[ $key ] = strip_tags( addslashes( $value ) );
				elseif ( is_array( $value ) )
					$redirect[ $key ] = array_map( 'absint', $value );
			}
		}

		if ( edd_csr_store_redirect( $redirect, $data['redirect-id'] ) ) {
			wp_redirect( add_query_arg( 'edd-message', 'redirect_updated', $data['edd-redirect'] ) ); edd_die();
		} else {
			wp_redirect( add_query_arg( 'edd-message', 'redirect_update_failed', $data['edd-redirect'] ) ); edd_die();
		}
	}
}
add_action( 'edd_edit_redirect', 'edd_csr_edit_redirect' );


/**
 * Listens for when a redirect delete button is clicked and deletes the
 * redirect 
 *
 * @since 1.0
 * @param array $data Redirect data
 * @uses edd_remove_redirect()
 * @return void
 */
function edd_csr_delete_redirect( $data ) {

	if ( ! isset( $data['_wpnonce'] ) || ! wp_verify_nonce( $data['_wpnonce'], 'edd_redirect_nonce' ) )
		wp_die( __( 'Trying to cheat or something?', 'edd-csr' ), __( 'Error', 'edd-csr' ) );

	$redirect_id = $data['redirect'];

	edd_csr_remove_redirect( $redirect_id );
}
add_action( 'edd_delete_redirect', 'edd_csr_delete_redirect' );


/**
 * Activates Redirect Code
 *
 * Sets a redirect's status to active
 *
 * @since 1.0
 * @param array $data Redirect data
 * @uses edd_csr_update_redirect_status()
 * @return void
 */
function edd_csr_activate_redirect( $data ) {
	$id = $data['redirect'];
	edd_csr_update_redirect_status( $id, 'active' );
}
add_action( 'edd_activate_redirect', 'edd_csr_activate_redirect' );


/**
 * Deactivate Redirect
 *
 * Sets a redirect's status to deactive
 *
 * @since 1.0
 * @param array $data Redirect data
 * @uses edd_csr_update_redirect_status()
 * @return void
*/
function edd_csr_deactivate_redirect( $data) {
	$id = $data['redirect'];
	edd_csr_update_redirect_status( $id, 'inactive' );
}
add_action( 'edd_deactivate_redirect', 'edd_csr_deactivate_redirect' );