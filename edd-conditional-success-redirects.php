<?php
/*
Plugin Name: Easy Digital Downloads - Conditional Success Redirects
Plugin URI: http://sumobi.com/shop/edd-conditional-success-redirects/
Description: Allows per-product confirmation pages on successful purchases
Version: 1.0.3
Author: Andrew Munro, Sumobi
Author URI: http://sumobi.com/
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'EDD_Conditional_Success_Redirects' ) ) {

	class EDD_Conditional_Success_Redirects {

		private static $instance;

		/**
		 * Main Instance
		 *
		 * Ensures that only one instance exists in memory at any one
		 * time. Also prevents needing to define globals all over the place.
		 *
		 * @since 1.0
		 *
		 */
		public static function instance() {
			if ( ! isset ( self::$instance ) ) {
				self::$instance = new self;
			}

			return self::$instance;
		}


		/**
		 * Start your engines
		 *
		 * @since 1.0
		 *
		 * @return void
		 */
		public function __construct() {
			$this->setup_globals();
			$this->includes();
			$this->setup_actions();
			$this->licensing();
		}

		/**
		 * Globals
		 *
		 * @since 1.0
		 *
		 * @return void
		 */
		private function setup_globals() {

			$this->version    = '1.0.3';

			// paths
			$this->file         = __FILE__;
			$this->basename     = apply_filters( 'edd_csr_plugin_basenname', plugin_basename( $this->file ) );
			$this->plugin_dir   = apply_filters( 'edd_csr_plugin_dir_path',  plugin_dir_path( $this->file ) );
			$this->plugin_url   = apply_filters( 'edd_csr_plugin_dir_url',   plugin_dir_url ( $this->file ) );

			// includes
			$this->includes_dir = apply_filters( 'edd_csr_includes_dir', trailingslashit( $this->plugin_dir . 'includes'  ) );
			$this->includes_url = apply_filters( 'edd_csr_includes_url', trailingslashit( $this->plugin_url . 'includes'  ) );

		}

		/**
		 * Setup the default hooks and actions
		 *
		 * @since 1.0
		 *
		 * @return void
		 */
		private function setup_actions() {

			// Internationalization
			add_action( 'init', array( $this, 'textdomain' ) );

			// Add sub-menu page
			add_action( 'admin_menu', array( $this, 'add_redirect_options'), 10 );

			// redirect
			add_action( 'edd_complete_purchase', array( $this, 'redirect' ) );

			// redirect customers if they arrive from off-site payment gateways like PayPal
			add_action( 'template_redirect', array( $this, 'template_redirect' ) );

			do_action( 'edd_csr_setup_actions' );
		}

		/**
		 * Licensing
		 *
		 * @since 1.0
		*/
		private function licensing() {
			// check if EDD_License class exists
			if ( class_exists( 'EDD_License' ) ) {
				$license = new EDD_License( __FILE__, 'Conditional Success Redirects', $this->version, 'Andrew Munro' );
			}
		}

		/**
		 * Internationalization
		 *
		 * @since 1.0
		 */
		function textdomain() {
			load_plugin_textdomain( 'edd-csr', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
		}


		/**
		 * Include required files.
		 *
		 * @since 1.0
		 *
		 * @return void
		 */
		private function includes() {
			
			require( $this->includes_dir . 'redirect-functions.php' );

			do_action( 'edd_csr_include_files' );

			if ( ! is_admin() )
				return;

			require( $this->includes_dir . 'redirect-actions.php' );
			require( $this->includes_dir . 'admin-notices.php' );
			require( $this->includes_dir . 'post-types.php' );

			do_action( 'edd_csr_include_admin_files' );
		}


		/**
		 * Add submenu page
		 *
		 * @since 1.0
		*/
		function add_redirect_options() {
			add_submenu_page( 'edit.php?post_type=download', __( 'Conditional Success Redirects', 'edd-csr' ), __( 'Conditional Success Redirects', 'edd-csr' ), 'manage_shop_settings', 'edd-redirects', array( $this, 'redirects_page') );
		}


		/**
		 * Redirects page
		 *
		 * @since 1.0
		*/
		function redirects_page() {

			if ( isset( $_GET['edd-action'] ) && $_GET['edd-action'] == 'edit_redirect' ) {
				require_once $this->includes_dir . 'edit-redirect.php';
			} 
			elseif ( isset( $_GET['edd-action'] ) && $_GET['edd-action'] == 'add_redirect' ) {
				require_once $this->includes_dir . 'add-redirect.php';
			} 
			else {
				require_once $this->includes_dir . 'class-redirects-table.php';
				$redirects_table = new EDD_CSR_Table();
				$redirects_table->prepare_items();
			?>
			<div class="wrap">
				<h2><?php _e( 'Conditional Success Redirects', 'edd-csr' ); ?><a href="<?php echo add_query_arg( array( 'edd-action' => 'add_redirect' ) ); ?>" class="add-new-h2"><?php _e( 'Add New', 'edd-csr' ); ?></a></h2>
				<?php do_action( 'edd_csr_redirects_page_top' ); ?>
				<form id="edd-redirects-filter" method="get" action="<?php echo admin_url( 'edit.php?post_type=download&page=edd-redirects' ); ?>">
					<?php $redirects_table->search_box( __( 'Search', 'edd-csr' ), 'edd-redirects' ); ?>

					<input type="hidden" name="post_type" value="download" />
					<input type="hidden" name="page" value="edd-redirects" />

					<?php $redirects_table->views() ?>
					<?php $redirects_table->display() ?>
				</form>
				<?php do_action( 'edd_csr_redirects_page_bottom' ); ?>
			</div>
		<?php
			}
		}

		/**
		 * Redirect customers to custom page if they have arrived from PayPal or other similar payment gateways that return the customer after successful purchase.
		 *
		 * @since 1.0.1
		*/
		function template_redirect() {
			global $edd_options;

			// check if we have query string and on purchase confirmation page
			if ( ! is_page( $edd_options['success_page'] ) )
				return;

			// check query string for PayPal and setup redirect.
			if ( isset( $_GET['payment-confirmation'] ) && $_GET['payment-confirmation'] ) {
				
				$purchase_session = edd_get_purchase_session();
				$cart_items = $purchase_session['downloads'];

				// get the download ID from cart items array
			 	if ( $cart_items ) {
					foreach ( $cart_items as $download ) {
						 $download_id = $download['id'];
					}
				}
				// return if no purchase session
				else {
					return;
				}

				// return if more than one item exists in cart. The default purchase confirmation will be used
				if( count( $cart_items ) > 1 )
			 	 	return;

			 	// check if the redirect is active
				if ( edd_csr_is_redirect_active( edd_csr_get_redirect_id( $download_id ) ) ) {

				 	// get redirect post ID from the download ID
					$redirect_id = edd_csr_get_redirect_id( $download_id );

					// get the page ID from the redirect ID
					$redirect = edd_csr_get_redirect_page_id( $redirect_id );

					// get the permalink from the redirect ID
					$redirect = get_permalink( $redirect );

					// redirect
					wp_redirect( $redirect, 301 ); exit;
			 		
			 	}

			}

		}

		/**
		 * Redirects customer to set page
		 *
		 * @since 1.0
		 * @param int $payment_id ID of payment
		*/
		function redirect( $payment_id ) {
			
			// get cart items from payment ID
			$cart_items = edd_get_payment_meta_cart_details( $payment_id );

		 	// get the download ID from cart items array
		 	if ( $cart_items ) {
				foreach ( $cart_items as $download ) {
					 $download_id = $download['id'];
				}
			}

		 	// return if more than one item exists in cart. The default purchase confirmation will be used
			if( count( $cart_items ) > 1 )
		 	 	return;

		 	// check if the redirect is active
			if ( edd_csr_is_redirect_active( edd_csr_get_redirect_id( $download_id ) ) ) {

			 	// get redirect post ID from the download ID
				$redirect_id = edd_csr_get_redirect_id( $download_id );

				// get the page ID from the redirect ID
				$redirect = edd_csr_get_redirect_page_id( $redirect_id );

				// get the permalink from the redirect ID
				$redirect = get_permalink( $redirect );

				$obj = new EDD_Conditional_Success_Redirects_Success_URI();
				$obj->uri = $redirect;

				add_filter( 'edd_get_success_page_uri', array( $obj, 'uri' ) );
		 		
		 	}

		}

	}
	
}


if ( ! class_exists( 'EDD_Conditional_Success_Redirects_Success_URI' ) ) {
	class EDD_Conditional_Success_Redirects_Success_URI {
	    public $uri = '';

	    function uri() {
	        return $this->uri;
	    }
	}
}

function edd_csr_redirects() {
	return EDD_Conditional_Success_Redirects::instance();
}

edd_csr_redirects();