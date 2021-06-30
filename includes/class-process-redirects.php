<?php

class EDD_Conditional_Success_Redirects_Process_Redirects {

	public function __construct() {
		// process standard on-site payment
		add_action( 'edd_complete_purchase', array( $this, 'process_standard_payment' ) );

		// process paypal standard payment
		add_filter( 'edd_payment_confirm_paypal', array( $this, 'process_paypal_standard' ) );

		// processes all other off-site payments
		add_action( 'template_redirect', array( $this, 'process_offsite_payment' ) );
	}


		/**
		 * Process PayPal Standard purchase
		 * uses edd_payment_confirm_paypal filter
		 *
		 * @since  1.0.4
		 * @return string
		 */
		public function process_paypal_standard( $content ) {

			// return content if there's no redirect
			if ( ! $this->get_redirect() ) {
				return $content;
			}

			// return if no payment-id query string or purchase session
			if ( ! isset( $_GET['payment-id'] ) && ! edd_get_purchase_session() ) {
				return $content;
			}

			// get payment ID from the query string
			$payment_id = isset( $_GET['payment-id'] ) ? absint( $_GET['payment-id'] ) : false;

			// no query string, get the payment ID from the purchase session
			if ( ! $payment_id ) {
				$session    = edd_get_purchase_session();
				$payment_id = edd_get_purchase_id_by_key( $session['purchase_key'] );
			}

			$payment = edd_get_payment( $payment_id );

			// if payment is pending or private (buy now button behavior), load the payment processing template
			if ( $payment && ( 'pending' == edd_get_payment_status( $payment ) || 'private' == $payment->status ) ) {

				// Payment is still pending or private so show processing indicator to fix the Race Condition
				ob_start();

				// load the payment processing template if the payment is still pending
				$this->payment_processing();

				$content = ob_get_clean();

			} elseif ( $payment && edd_is_payment_complete( $payment->ID) ) {

				$redirect_url = $this->get_redirect();

				if( $redirect_url ) {
				 	// payment is complete, it can redirect straight away
				 	wp_redirect( $redirect_url, 301 );
				 	exit;
				}
			}

			return $content;
		}


		/**
		 * Payment processing template
		 * The idea here is to give the website enough time to receive instructions from PayPal as per https://github.com/easydigitaldownloads/Easy-Digital-Downloads/issues/1839
		 * You should always add the neccessary checks on the redirected page if you are going to show the customer sensitive information
		 *
		 * Similar to EDD's /templates/payment-processing.php file
		 *
		 * @since  1.0.4
		 * @return string
		 */
		public function payment_processing() {
			$redirect = $this->get_redirect();

			?>
			<div id="edd-payment-processing">
				<p><?php printf( __( 'Your purchase is processing. This page will reload automatically in 8 seconds. If it does not, click <a href="%s">here</a>.', 'edd' ), $redirect ); ?>
				<span class="edd-cart-ajax"><i class="edd-icon-spinner edd-icon-spin"></i></span>
				<script type="text/javascript">setTimeout(function(){ window.location = '<?php echo $redirect; ?>'; }, 8000);</script>
			</div>

			<?php
		}


		/**
		 * Process all other off-site payments
		 *
		 * @since 1.0.1
		 * @todo  Move PayPal express to edd_payment_confirm_paypalexpress filter
		*/
		public function process_offsite_payment() {

			// check if we have query string and on purchase confirmation page
			if ( ! is_page( edd_get_option( 'success_page' ) ) ) {
				return;
			}

			$custom_redirect_url = $this->get_redirect();
			$edd_success_page_url = get_permalink( edd_get_option( 'success_page' ) );

			if ( trailingslashit( $custom_redirect_url ) == trailingslashit( $edd_success_page_url ) ) {
				return;
			}

			$redirect = true;

			// Detect if we are on the confirmation page for PayPal Express - Do not redirect if so
			if ( ! empty( $_GET['token'] ) && ! empty( $_GET['payment-confirmation'] ) && 'paypalexpress' == $_GET['payment-confirmation'] ) {
				$redirect = false;
			}

			// Detect if we are on the confirmation page for PayPal Express for Recurring Payments - Do not redirect if so
			if ( ! empty( $_GET['edd-confirm'] ) ) {
				$redirect = false;
			}

			if( $redirect ) {
				
				$redirect_url = $this->get_redirect();

				if( $redirect_url ) {
	
					wp_redirect( $redirect_url, 301 );				
					exit;
	
				}
			}

			// Do nothing if we got here

		}


		/**
		 * Redirects customer to set page
		 *
		 * @since 1.0
		 * @param int $payment_id ID of payment
		*/
		public function process_standard_payment( $payment_id ) {

			// get cart items from payment ID
			$cart_items = edd_get_payment_meta_cart_details( $payment_id );

		 	// get the download ID from cart items array
		 	if ( $cart_items ) {
				foreach ( $cart_items as $download ) {
					 $download_id = $download['id'];
				}
			}

		 	// return if more than one item exists in cart. The default purchase confirmation will be used
			if ( count( $cart_items ) > 1 )
		 	 	return;

		 	// redirect by default to the normal EDD success page
		 	$redirect = apply_filters( 'edd_csr_redirect', get_permalink( edd_get_option( 'success_page' ) ), $download_id );

		 	// check if the redirect is active
			if ( edd_csr_is_redirect_active( edd_csr_get_redirect_id( $download_id ) ) ) {

			 	// get redirect post ID from the download ID
				$redirect_id = edd_csr_get_redirect_id( $download_id );

				// get the page ID from the redirect ID
				$redirect = edd_csr_get_redirect_page_id( $redirect_id );

				$redirect = get_permalink( $redirect );

		 	}

		 	// redirect
		 	$obj      = new EDD_Conditional_Success_Redirects_Success_URI();
		 	$obj->uri = $redirect;

		 	add_filter( 'edd_get_success_page_uri', array( $obj, 'uri' ) );
			add_filter( 'edd_success_page_url', array( $obj, 'uri' ) );

		}

		/**
		 * Gets the redirect
		 *
		 * @since 1.0.4
		 * @return string $redirect
		*/
		public function get_redirect() {

			// get the purchase session
			$purchase_session = edd_get_purchase_session();

			if ( ! $purchase_session ) {
				return false;
			}

			$cart_items = $purchase_session['downloads'];

			// get the download ID from cart items array
		 	if ( $cart_items ) {
				foreach ( $cart_items as $download ) {
					 $download_id = $download['id'];
				}
			}

			// return if more than one item exists in cart. The default purchase confirmation will be used
			if ( count( $cart_items ) > 1 ) {
		 	 	return false;
			}

		 	// redirect by default to the normal EDD success page
		 	$redirect = apply_filters( 'edd_csr_redirect', false, $download_id );

		 	// check if the redirect is active
			if ( edd_csr_is_redirect_active( edd_csr_get_redirect_id( $download_id ) ) ) {

			 	// get redirect post ID from the download ID
				$redirect_id = edd_csr_get_redirect_id( $download_id );

				// get the page ID from the redirect ID
				$redirect = edd_csr_get_redirect_page_id( $redirect_id );

				// get the permalink from the redirect ID
				$redirect = get_permalink( $redirect );

		 	}

		 	// return the redirect
		 	return $redirect;

		}


}
$edd_csr_process_redirects = new EDD_Conditional_Success_Redirects_Process_Redirects;
