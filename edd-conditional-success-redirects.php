<?php
/*
Plugin Name: Easy Digital Downloads - Conditional Success Redirects
Plugin URI: http://sumobi.com/shop/edd-conditional-success-redirects/
Description: Allows per-product confirmation pages on successful purchases
Version: 1.1.1
Author: Andrew Munro, Sumobi
Author URI: http://sumobi.com/
Text Domain: edd-csr
Domain Path: languages
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
			if ( ! isset( self::$instance ) && ! ( self::$instance instanceof EDD_Conditional_Success_Redirects ) ) {
				self::$instance = new EDD_Conditional_Success_Redirects;
				self::$instance->setup_globals();
				self::$instance->includes();
				self::$instance->setup_actions();
				self::$instance->licensing();
				self::$instance->load_textdomain();
			}

			return self::$instance;
		}

		/**
		 * Globals
		 *
		 * @since 1.0
		 *
		 * @return void
		 */
		private function setup_globals() {

			$this->version    = '1.1.1';

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
			// Add sub-menu page
			add_action( 'admin_menu', array( $this, 'add_redirect_options'), 10 );

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
		 * Loads the plugin language files
		 *
		 * @access public
		 * @since 1.1
		 * @return void
		 */
		public function load_textdomain() {

			// Set filter for plugin's languages directory
			$lang_dir = dirname( plugin_basename( __FILE__ ) ) . '/languages/';
			$lang_dir = apply_filters( 'edd_csr_languages_directory', $lang_dir );

			// Traditional WordPress plugin locale filter
			$locale        = apply_filters( 'plugin_locale',  get_locale(), 'edd-csr' );
			$mofile        = sprintf( '%1$s-%2$s.mo', 'edd-csr', $locale );

			// Setup paths to current locale file
			$mofile_local  = $lang_dir . $mofile;
			$mofile_global = WP_LANG_DIR . '/edd-conditional-success-redirects/' . $mofile;

			if ( file_exists( $mofile_global ) ) {
				// Look in global /wp-content/languages/edd-conditional-success-redirects/ folder
				load_textdomain( 'edd-csr', $mofile_global );
			} elseif ( file_exists( $mofile_local ) ) {
				// Look in local /wp-content/plugins/edd-conditional-success-redirects/languages/ folder
				load_textdomain( 'edd-csr', $mofile_local );
			} else {
				// Load the default language files
				load_plugin_textdomain( 'edd-csr', false, $lang_dir );
			}
		}

		/**
		 * Include required files.
		 *
		 * @since 1.0
		 *
		 * @return void
		 */
		private function includes() {

			require( $this->includes_dir . 'class-process-redirects.php' );
			require( $this->includes_dir . 'redirect-functions.php' );

			do_action( 'edd_csr_include_files' );

			if ( ! is_admin() ) {
				return;
			}

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
		public function add_redirect_options() {
			add_submenu_page( 'edit.php?post_type=download', __( 'Conditional Success Redirects', 'edd-csr' ), __( 'Conditional Success Redirects', 'edd-csr' ), 'manage_shop_settings', 'edd-redirects', array( $this, 'redirects_page') );
		}


		/**
		 * Redirects page
		 *
		 * @since 1.0
		*/
		public function redirects_page() {

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
				<h2><?php _e( 'Conditional Success Redirects', 'edd-csr' ); ?><a href="<?php echo esc_url( add_query_arg( array( 'edd-action' => 'add_redirect' ) ) ); ?>" class="add-new-h2"><?php _e( 'Add New', 'edd-csr' ); ?></a></h2>
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

	}


	/**
	 * The main function responsible for returning the one true EDD_Conditional_Success_Redirects
	 * instance to functions everywhere
	 *
	 * @since       1.1
	 * @return      \EDD_Conditional_Success_Redirects The one true EDD_Conditional_Success_Redirects
	 *
	 * @todo        Inclusion of the activation code below isn't mandatory, but
	 *              can prevent any number of errors, including fatal errors, in
	 *              situations where your extension is activated but EDD is not
	 *              present.
	 */
	function edd_csr_redirects() {
	    if ( ! class_exists( 'Easy_Digital_Downloads' ) ) {
	        if ( ! class_exists( 'EDD_Extension_Activation' ) ) {
	            require_once 'includes/class.extension-activation.php';
	        }

	        $activation = new EDD_Extension_Activation( plugin_dir_path( __FILE__ ), basename( __FILE__ ) );
	        $activation = $activation->run();
	        return EDD_Conditional_Success_Redirects::instance();
	    } else {
	        return EDD_Conditional_Success_Redirects::instance();
	    }
	}

	add_action( 'plugins_loaded', 'edd_csr_redirects' );

}

if ( ! class_exists( 'EDD_Conditional_Success_Redirects_Success_URI' ) ) {
	class EDD_Conditional_Success_Redirects_Success_URI {
	    public $uri = '';

	    function uri() {
	        return $this->uri;
	    }
	}
}
