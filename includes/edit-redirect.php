<?php
/**
 * Edit Redirect Page
 *
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! isset( $_GET['redirect'] ) || ! is_numeric( $_GET['redirect'] ) ) {
	wp_die( __( 'Something went wrong.', 'edd-csr' ), __( 'Error', 'edd-csr' ) );
}

$redirect_id  = absint( $_GET['redirect'] );
$redirect     = edd_csr_get_redirect( $redirect_id );

?>
<h2><?php _e( 'Edit Redirect', 'edd-csr' ); ?> - <a href="<?php echo admin_url( 'edit.php?post_type=download&page=edd-redirects' ); ?>" class="button-secondary"><?php _e( 'Go Back', 'edd-csr' ); ?></a></h2>
<form id="edd-edit-redirect" action="" method="post">
	<?php do_action( 'edd_csr_edit_redirect_form_top', $redirect_id, $redirect ); ?>
	<table class="form-table">
		<tbody>
			<tr class="form-field">
				<th scope="row" valign="top">
					<label for="download"><?php printf( __( 'Select %s', 'edd-csr' ), edd_get_label_singular() ); ?></label>
				</th>
				<td>
					<?php 

					// current download
					$include = get_post_meta( $redirect_id, '_edd_redirect_download', true );

					// remove downloads from dropdown
					$excluded_products = edd_csr_get_meta_values();
					$excluded_products = array_filter( $excluded_products );

					$download_to_remove = array_keys( $excluded_products, $include );

					foreach( $download_to_remove as $download ) {
					    unset( $excluded_products[$download] );
					}

					$products = get_posts( array( 'post_type' => 'download', 'exclude' => $excluded_products, 'nopaging' => true, 'orderby' => 'title', 'order' => 'ASC' ) ); ?>


					<select name="download" id="download">

						<?php if ( $products ) : ?> 
						
						<option><?php printf( __( 'Select %s', 'edd-csr' ), strtolower( edd_get_label_singular() ) ); ?></option>

						<?php foreach ( $products as $product ) {
						?>
						<option value="<?php echo absint( $product->ID ); ?>" <?php echo selected( edd_csr_get_redirect_download( $redirect_id ), $product->ID, false ); ?>><?php echo esc_html( get_the_title( $product->ID ) ); ?></option>

						<?php } ?>

						<?php else : ?>

							<option><?php printf( __( 'No %s found', 'edd-csr' ), edd_get_label_plural() ); ?></option>

						<?php endif; ?>

					</select>	
					
					<p class="description"><?php printf( __( 'Select the %s that will trigger the redirect when it is succesfully purchased on it\'s own', 'edd-csr' ), strtolower( edd_get_label_singular() ) ); ?></p>
				</td>
			</tr>
			<tr class="form-field">
				<th scope="row" valign="top">
					<label for="edd-page"><?php _e( 'Redirect Page', 'edd-csr' ); ?></label>
				</th>
				<td>
				
					<?php
						$pages = get_pages();
						
						if ( $pages ) { ?>
						<select id="edd-page" name="page">
							<option><?php _e( 'Select page', 'edd-csr' ); ?></option>
						<?php
							foreach ( $pages as $page ) { ?>
								<option value="<?php echo $page->ID; ?>" <?php selected( edd_csr_get_redirect_page_id( $redirect_id ), $page->ID ); ?>><?php echo $page->post_title; ?></option>
							<?php } ?>
							
						</select>
							<?php
						}
					?>

					<p class="description"><?php printf( __( 'Select the page to redirect to when the %s above has been succesfully purchased', 'edd-csr' ), strtolower( edd_get_label_singular() ) ); ?></p>
				</td>
			</tr>
			<tr class="form-field">
				<th scope="row" valign="top">
					<label for="edd-status"><?php _e( 'Status', 'edd-csr' ); ?></label>
				</th>
				<td>
					<select name="status" id="edd-status">
						<option value="active" <?php selected( $redirect->post_status, 'active' ); ?>><?php _e( 'Active', 'edd-csr' ); ?></option>
						<option value="inactive"<?php selected( $redirect->post_status, 'inactive' ); ?>><?php _e( 'Inactive', 'edd-csr' ); ?></option>
					</select>
					<p class="description"><?php _e( 'The status of this redirect.', 'edd-csr' ); ?></p>
				</td>
			</tr>
			
		</tbody>
	</table>

	<?php do_action( 'edd_csr_edit_redirect_form_bottom', $redirect_id, $redirect ); ?>

	<p class="submit">
		<input type="hidden" name="edd-action" value="edit_redirect"/>
		<input type="hidden" name="redirect-id" value="<?php echo absint( $_GET['redirect'] ); ?>" />
		<input type="hidden" name="edd-redirect" value="<?php echo esc_url( admin_url( 'edit.php?post_type=download&page=edd-redirects' ) ); ?>" />
		<input type="hidden" name="edd-redirect-nonce" value="<?php echo wp_create_nonce( 'edd_redirect_nonce' ); ?>" />
		<input type="submit" value="<?php _e( 'Update Redirect', 'edd-csr' ); ?>" class="button-primary" />
	</p>
</form>