<?php
/**
 * Redirects Table Class
 * Based largely on existing code from the Easy Digital Downloads plugin
 * @since 1.0
 */


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

// Load WP_List_Table if not loaded
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * EDD_CSR_Table Class
 *
 * Renders the Redirects table on the Conditional Redirects page
 *
 * @since 1.0
 */
class EDD_CSR_Table extends WP_List_Table {
	/**
	 * Number of results to show per page
	 *
	 * @var string
	 * @since 1.0
	 */
	public $per_page = 30;

	/**
	 *
	 * Total number of redirects
	 * @var string
	 * @since 1.0
	 */
	public $total_count;

	/**
	 * Active number of redirects
	 *
	 * @var string
	 * @since 1.0
	 */
	public $active_count;

	/**
	 * Inactive number of redirects
	 *
	 * @var string
	 * @since 1.0
	 */
	public $inactive_count;

	/**
	 * Get things started
	 *
	 * @access public
	 * @since 1.0
	 * @uses EDD_CSR_Table::get_redirect_counts()
	 * @see WP_List_Table::__construct()
	 * @return void
	 */
	public function __construct() {
		global $status, $page;

		parent::__construct( array(
			'singular'  => edd_get_label_singular(),    // Singular name of the listed records
			'plural'    => edd_get_label_plural(),    	// Plural name of the listed records
			'ajax'      => false             			// Does this table support ajax?
		) );

		$this->get_redirect_counts();
	}

	/**
	 * Show the search field
	 *
	 * @access public
	 * @since 1.0
	 *
	 * @param string $text Label for the search box
	 * @param string $input_id ID of the search box
	 *
	 * @return void
	 */
	public function search_box( $text, $input_id ) {
		if ( empty( $_REQUEST['s'] ) && !$this->has_items() )
			return;

		$input_id = $input_id . '-search-input';

		if ( ! empty( $_REQUEST['orderby'] ) )
			echo '<input type="hidden" name="orderby" value="' . esc_attr( $_REQUEST['orderby'] ) . '" />';
		if ( ! empty( $_REQUEST['order'] ) )
			echo '<input type="hidden" name="order" value="' . esc_attr( $_REQUEST['order'] ) . '" />';
		?>
		<p class="search-box">
			<label class="screen-reader-text" for="<?php echo $input_id ?>"><?php echo $text; ?>:</label>
			<input type="search" id="<?php echo $input_id ?>" name="s" value="<?php _admin_search_query(); ?>" />
			<?php submit_button( $text, 'button', false, false, array('ID' => 'search-submit') ); ?>
		</p>
	<?php
	}

	/**
	 * Retrieve the view types
	 *
	 * @access public
	 * @since 1.0
	 * @return array $views All the views available
	 */
	public function get_views() {
		$base           = admin_url('edit.php?post_type=download&page=edd-redirects');

		$current        = isset( $_GET['status'] ) ? $_GET['status'] : '';
		$total_count    = '&nbsp;<span class="count">(' . $this->total_count    . ')</span>';
		$active_count   = '&nbsp;<span class="count">(' . $this->active_count . ')</span>';
		$inactive_count = '&nbsp;<span class="count">(' . $this->inactive_count  . ')</span>';

		$views = array(
			'all'		=> sprintf( '<a href="%s"%s>%s</a>', remove_query_arg( 'status', $base ), $current === 'all' || $current == '' ? ' class="current"' : '', __('All', 'edd-csr') . $total_count ),
			'active'	=> sprintf( '<a href="%s"%s>%s</a>', add_query_arg( 'status', 'active', $base ), $current === 'active' ? ' class="current"' : '', __('Active', 'edd-csr') . $active_count ),
			'inactive'	=> sprintf( '<a href="%s"%s>%s</a>', add_query_arg( 'status', 'inactive', $base ), $current === 'inactive' ? ' class="current"' : '', __('Inactive', 'edd-csr') . $inactive_count ),
		);

		return $views;
	}

	/**
	 * Retrieve the table columns
	 *
	 * @access public
	 * @since 1.0
	 * @return array $columns Array of all the list table columns
	 */
	public function get_columns() {
		$columns = array(
			'cb'        => '<input type="checkbox" />',
			'download'  => sprintf( __( '%s', 'edd-csr' ), edd_get_label_singular() ),
			'redirect'  => __( 'Redirect', 'edd-csr' ),
			'status'  	=> __( 'Status', 'edd-csr' ),
		);

		return $columns;
	}

	/**
	 * Retrieve the table's sortable columns
	 *
	 * @access public
	 * @since 1.0
	 * @return array Array of all the sortable columns
	 */
	public function get_sortable_columns() {
		return array(
			'ID'     => array( 'ID', true ),
			'download'   => array( 'download', false ),
		);
	}

	/**
	 * This function renders most of the columns in the list table.
	 *
	 * @access public
	 * @since 1.0
	 *
	 * @param array $item Contains all the data of the redirect
	 * @param string $column_name The name of the column
	 *
	 * @return string Column Name
	 */
	function column_default( $item, $column_name ) {
		switch( $column_name ){
			default:
				return $item[ $column_name ];
		}
	}



	/**
	 * Render the Download Column
	 *
	 * @access public
	 * @since 1.0
	 * @param array $item Contains all the data of the redirect
	 * @return string Data shown in the Name column
	 */
	function column_download( $item ) {
		$redirect     = get_post( $item['ID'] );
		$base         = admin_url( 'edit.php?post_type=download&page=edd-redirects&edd-action=edit_redirect&redirect=' . $item['ID'] );
		$row_actions  = array();

		$row_actions['edit'] = '<a href="' . add_query_arg( array( 'edd-action' => 'edit_redirect', 'redirect' => $redirect->ID ) ) . '">' . __( 'Edit', 'edd-csr' ) . '</a>';

		if( strtolower( $item['status'] ) == 'active' )
			$row_actions['deactivate'] = '<a href="' . add_query_arg( array( 'edd-action' => 'deactivate_redirect', 'redirect' => $redirect->ID ) ) . '">' . __( 'Deactivate', 'edd-csr' ) . '</a>';
		else
			$row_actions['activate'] = '<a href="' . add_query_arg( array( 'edd-action' => 'activate_redirect', 'redirect' => $redirect->ID ) ) . '">' . __( 'Activate', 'edd-csr' ) . '</a>';

		$row_actions['delete'] = '<a href="' . wp_nonce_url( add_query_arg( array( 'edd-action' => 'delete_redirect', 'redirect' => $redirect->ID ) ), 'edd_redirect_nonce' ) . '">' . __( 'Delete', 'edd-csr' ) . '</a>';

		$row_actions = apply_filters( 'edd_csr_redirect_row_actions', $row_actions, $redirect );

		return $item['download'] . $this->row_actions( $row_actions );
	}

	/**
	 * Render the checkbox column
	 *
	 * @access public
	 * @since 1.0
	 * @param array $item Contains all the data for the checkbox column
	 * @return string Displays a checkbox
	 */
	function column_cb( $item ) {
		return sprintf(
			'<input type="checkbox" name="%1$s[]" value="%2$s" />',
			/*$1%s*/ 'redirect',
			/*$2%s*/ $item['ID']
		);
	}

	/**
	 * Retrieve the bulk actions
	 *
	 * @access public
	 * @since 1.0
	 * @return array $actions Array of the bulk actions
	 */
	public function get_bulk_actions() {
		$actions = array(
			'delete' => __( 'Delete', 'edd-csr' )
		);

		return $actions;
	}

	/**
	 * Message to be displayed when there are no items
	 *
	 * @since 1.0
	 * @access public
	 */
	function no_items() {
		_e( 'No redirects found.', 'edd-csr' );
	}


	/**
	 * Process the bulk actions
	 * @access public
	 * @since 1.0
	 * @return void
	 */


	public function process_bulk_action() {
		$ids = isset( $_GET[ 'redirect' ] ) ? $_GET[ 'redirect' ] : false;

		if ( ! is_array( $ids ) )
			$ids = array( $ids );

		foreach ( $ids as $id ) {
			if ( 'delete' === $this->current_action() ) {
				edd_remove_redirect( $id );
			}
		}

	}

	/**
	 * Retrieve the redirect counts
	 *
	 * @access public
	 * @since 1.0
	 * @return void
	 */
	public function get_redirect_counts() {
		$redirect_count  = wp_count_posts( 'edd_redirect' );
		$this->active_count   = $redirect_count->active;
		$this->inactive_count = $redirect_count->inactive;
		$this->total_count    = $redirect_count->active + $redirect_count->inactive;
	}

	/**
	 * Retrieve all the data for all the redirects
	 *
	 * @access public
	 * @since 1.0
	 * @return array $redirect_data Array of all the data for the redirects
	 */
	public function redirect_data() {
		$redirect_data = array();

		$per_page = $this->per_page;

		$mode = edd_is_test_mode() ? 'test' : 'live';

		$orderby 		= isset( $_GET['orderby'] )  ? $_GET['orderby']                  : 'ID';
		$order 			= isset( $_GET['order'] )    ? $_GET['order']                    : 'DESC';
		$order_inverse 	= $order == 'DESC'           ? 'ASC'                             : 'DESC';
		$status 		= isset( $_GET['status'] )   ? $_GET['status']                   : array( 'active', 'inactive' );
		$meta_key		= isset( $_GET['meta_key'] ) ? $_GET['meta_key']                 : null;
		$search         = isset( $_GET['s'] )        ? sanitize_text_field( $_GET['s'] ) : null;
		$order_class 	= strtolower( $order_inverse );

		$redirects = edd_csr_get_redirects( array(
			'posts_per_page' => $per_page,
			'paged'          => isset( $_GET['paged'] ) ? $_GET['paged'] : 1,
			'orderby'        => $orderby,
			'order'          => $order,
			'post_status'    => $status,
			'meta_key'       => $meta_key,
			's'              => $search
		) );

		if ( $redirects ) {
			foreach ( $redirects as $redirect ) {

				$redirect_to = edd_csr_get_redirect_page( $redirect->ID ) ? edd_csr_get_redirect_page( $redirect->ID ) : '';
				$download = edd_csr_get_redirect_download( $redirect->ID ) ? get_the_title( edd_csr_get_redirect_download( $redirect->ID ) ) : '';
				
				$redirect_data[] = array(
					'ID' 			=> $redirect->ID,
					'download'		=> $download,
					'redirect'		=> get_the_title( $redirect_to ),
					'status'		=> ucwords( $redirect->post_status ),
				);
			}
		}

		return $redirect_data;
	}

	/**
	 * Setup the final data for the table
	 *
	 * @access public
	 * @since 1.0
	 * @uses EDD_CSR_Table::get_columns()
	 * @uses EDD_CSR_Table::get_sortable_columns()
	 * @uses EDD_CSR_Table::process_bulk_action()
	 * @uses EDD_CSR_Table::redirect_data()
	 * @uses WP_List_Table::get_pagenum()
	 * @uses WP_List_Table::set_pagination_args()
	 * @return void
	 */
	public function prepare_items() {
		$per_page = $this->per_page;

		$columns = $this->get_columns();

		$hidden = array();

		$sortable = $this->get_sortable_columns();

		$this->_column_headers = array( $columns, $hidden, $sortable );

		$this->process_bulk_action();

		$data = $this->redirect_data();

		$current_page = $this->get_pagenum();

		$status = isset( $_GET['status'] ) ? $_GET['status'] : 'any';

		switch( $status ) {
			case 'active':
				$total_items = $this->active_count;
				break;
			case 'inactive':
				$total_items = $this->inactive_count;
				break;
			case 'any':
				$total_items = $this->total_count;
				break;
		}

		$this->items = $data;

		$this->set_pagination_args( array(
				'total_items' => $total_items,
				'per_page'    => $per_page,
				'total_pages' => ceil( $total_items / $per_page )
			)
		);
	}
}