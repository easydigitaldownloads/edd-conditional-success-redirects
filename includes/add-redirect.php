<?php
/**
 * Add Redirect Page
 *
 * @since 1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

?>
<h2><?php _e( 'Add New Redirect', 'edd-csr' ); ?> - <a href="<?php echo admin_url( 'edit.php?post_type=download&page=edd-redirects' ); ?>" class="button-secondary"><?php _e( 'Go Back', 'edd-csr' ); ?></a></h2>
<form id="edd-add-redirect" action="" method="POST">
	<?php do_action( 'edd_csr_add_redirect_form_top' ); ?>
	<table class="form-table">
		<tbody>

			<tr class="form-field">
				<th scope="row" valign="top">
					<label for="download"><?php printf( __( 'Select %s', 'edd-csr' ), edd_get_label_singular() ); ?></label>
				</th>
				<td>
					<?php
					$excluded_products = edd_csr_get_meta_values();
					$excluded_products = array_filter( $excluded_products );
				
					$products = get_posts( array( 'post_type' => 'download', 'exclude' => $excluded_products, 'nopaging' => true, 'orderby' => 'title', 'order' => 'ASC' ) ); ?>

					<select name="download" id="download">

						<?php if ( $products ) : ?> 
						
						<option><?php printf( __( 'Select %s', 'edd-csr' ), strtolower( edd_get_label_singular() ) ); ?></option>

						<?php foreach ( $products as $product ) { 
						?>
						<option value="<?php echo absint( $product->ID ); ?>"><?php echo esc_html( get_the_title( $product->ID ) ); ?></option>

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
								<option value="<?php echo $page->ID; ?>"><?php echo $page->post_title; ?></option>
							<?php } ?>
							
						</select>
							<?php
						}
					?>

					<p class="description"><?php printf( __( 'Select the page to redirect to when the %s above has been succesfully purchased', 'edd-csr' ), strtolower( edd_get_label_singular() ) ); ?></p>
				</td>
			</tr>

			
		
		</tbody>
	</table>
	<?php do_action( 'edd_csr_add_redirect_form_bottom' ); ?>
	<p class="submit">
		<input type="hidden" name="edd-action" value="add_redirect"/>
		<input type="hidden" name="edd-redirect" value="<?php echo esc_url( admin_url( 'edit.php?post_type=download&page=edd-redirects' ) ); ?>"/>
		<input type="hidden" name="edd-redirect-nonce" value="<?php echo wp_create_nonce( 'edd_redirect_nonce' ); ?>"/>
		<input type="submit" value="<?php _e( 'Add Redirect', 'edd-csr' ); ?>" class="button-primary"/>
	</p>
</form>