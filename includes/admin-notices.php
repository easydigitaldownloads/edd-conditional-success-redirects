<?php
/**
 * Admin Notices
 *
 * @since       1.0
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Admin Messages
 *
 * @since 1.0
 * @return void
 */
function edd_csr_admin_messages() {

	if ( isset( $_GET['edd-message'] ) && 'redirect_added' == $_GET['edd-message'] && current_user_can( 'manage_shop_settings' ) ) {
		 add_settings_error( 'edd-csr-notices', 'edd-redirect-added', __( 'Redirect added.', 'edd-csr' ), 'updated' );
	}

	if ( isset( $_GET['edd-message'] ) && 'redirect_add_failed' == $_GET['edd-message'] && current_user_can( 'manage_shop_settings' ) ) {
		add_settings_error( 'edd-csr-notices', 'edd-redirect-add-fail', __( 'There was a problem adding your redirect, please try again.', 'edd-csr' ), 'error' );
	}

	if ( isset( $_GET['edd-message'] ) && 'redirect_updated' == $_GET['edd-message'] && current_user_can( 'manage_shop_settings' ) ) {
		 add_settings_error( 'edd-csr-notices', 'edd-redirect-updated', __( 'Redirect updated.', 'edd-csr' ), 'updated' );
	}

	if ( isset( $_GET['edd-message'] ) && 'redirect_update_failed' == $_GET['edd-message'] && current_user_can( 'manage_shop_settings' ) ) {
		add_settings_error( 'edd-csr-notices', 'edd-redirect-updated-fail', __( 'There was a problem updating your redirect, please try again.', 'edd-csr' ), 'error' );
	}

	if ( isset( $_GET['edd-action'] ) && 'deactivate_redirect' == $_GET['edd-action'] && current_user_can( 'manage_shop_settings' ) ) {
		add_settings_error( 'edd-csr-notices', 'edd-redirect-deactivated', __( 'Redirect Deactivated', 'edd-csr' ), 'updated' );
	}

	if ( isset( $_GET['edd-action'] ) && 'activate_redirect' == $_GET['edd-action'] && current_user_can( 'manage_shop_settings' ) ) {
		add_settings_error( 'edd-csr-notices', 'edd-redirect-activated', __( 'Redirect Activated', 'edd-csr' ), 'updated' );
	}

	if ( isset( $_GET['edd-action'] ) && 'delete_redirect' == $_GET['edd-action'] && current_user_can( 'manage_shop_settings' ) ) {
		add_settings_error( 'edd-csr-notices', 'edd-redirect-deleted', __( 'Redirect Deleted', 'edd-csr' ), 'updated' );
	}



	settings_errors( 'edd-csr-notices' );
}
add_action( 'admin_notices', 'edd_csr_admin_messages' );