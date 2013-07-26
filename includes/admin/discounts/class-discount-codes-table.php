<?php
/**
 * Discount Codes Table Class
 *
 * @package     EDD
 * @subpackage  Admin/Discounts
 * @copyright   Copyright (c) 2013, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.4
 */


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

// Load WP_List_Table if not loaded
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * EDD_Discount_Codes_Table Class
 *
 * Renders the Discount Codes table on the Discount Codes page
 *
 * @since 1.4
 * @author Sunny Ratilal
 */
class EDD_Discount_Codes_Table extends WP_List_Table {
	/**
	 * Number of results to show per page
	 *
	 * @var string
	 * @since 1.4
	 */
	public $per_page = 30;

	/**
	 *
	 * Total number of discounts
	 * @var string
	 * @since 1.4
	 */
	public $total_count;

	/**
	 * Active number of discounts
	 *
	 * @var string
	 * @since 1.4
	 */
	public $active_count;

	/**
	 * Inactive number of discounts
	 *
	 * @var string
	 * @since 1.4
	 */
	public $inactive_count;

	/**
	 * Get things started
	 *
	 * @access public
	 * @since 1.4
	 * @uses EDD_Discount_Codes_Table::get_discount_code_counts()
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

		$this->get_discount_code_counts();
	}

	/**
	 * Show the search field
	 *
	 * @access public
	 * @since 1.4
	 *
	 * @param string $text Label for the search box
	 * @param string $input_id ID of the search box
	 *
	 * @return svoid
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
	 * @since 1.4
	 * @return array $views All the views available
	 */
	public function get_views() {
		$base           = admin_url('edit.php?post_type=download&page=edd-discounts');

		$current        = isset( $_GET['status'] ) ? $_GET['status'] : '';
		$total_count    = '&nbsp;<span class="count">(' . $this->total_count    . ')</span>';
		$active_count   = '&nbsp;<span class="count">(' . $this->active_count . ')</span>';
		$inactive_count = '&nbsp;<span class="count">(' . $this->inactive_count  . ')</span>';

		$views = array(
			'all'		=> sprintf( '<a href="%s"%s>%s</a>', remove_query_arg( 'status', $base ), $current === 'all' || $current == '' ? ' class="current"' : '', __('All', 'edd') . $total_count ),
			'active'	=> sprintf( '<a href="%s"%s>%s</a>', add_query_arg( 'status', 'active', $base ), $current === 'active' ? ' class="current"' : '', __('Active', 'edd') . $active_count ),
			'inactive'	=> sprintf( '<a href="%s"%s>%s</a>', add_query_arg( 'status', 'inactive', $base ), $current === 'inactive' ? ' class="current"' : '', __('Inactive', 'edd') . $inactive_count ),
		);

		return $views;
	}

	/**
	 * Retrieve the table columns
	 *
	 * @access public
	 * @since 1.4
	 * @return array $columns Array of all the list table columns
	 */
	public function get_columns() {
		$columns = array(
			'cb'        => '<input type="checkbox" />',
			'ID'     	=> __( 'ID', 'edd' ),
			'name'  	=> __( 'Name', 'edd' ),
			'code'  	=> __( 'Code', 'edd' ),
			'amount'  	=> __( 'Amount', 'edd' ),
			'uses'  	=> __( 'Uses', 'edd' ),
			'max_uses' 	=> __( 'Max Uses', 'edd' ),
			'start_date'=> __( 'Start Date', 'edd' ),
			'expiration'=> __( 'Expiration', 'edd' ),
			'status'  	=> __( 'Status', 'edd' ),
		);

		return $columns;
	}

	/**
	 * Retrieve the table's sortable columns
	 *
	 * @access public
	 * @since 1.4
	 * @return array Array of all the sortable columns
	 */
	public function get_sortable_columns() {
		return array(
			'ID'     => array( 'ID', true ),
			'name'   => array( 'name', false )
		);
	}

	/**
	 * This function renders most of the columns in the list table.
	 *
	 * @access public
	 * @since 1.4
	 *
	 * @param array $item Contains all the data of the discount code
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
	 * Render the Name Column
	 *
	 * @access public
	 * @since 1.4
	 * @param array $item Contains all the data of the discount code
	 * @return string Data shown in the Name column
	 */
	function column_name( $item ) {
		$discount     = get_post( $item['ID'] );
		$base         = admin_url( 'edit.php?post_type=download&page=edd-discounts&edd-action=edit_discount&discount=' . $item['ID'] );
		$row_actions  = array();

		$row_actions['edit'] = '<a href="' . add_query_arg( array( 'edd-action' => 'edit_discount', 'discount' => $discount->ID ) ) . '">' . __( 'Edit', 'edd' ) . '</a>';

		if( strtolower( $item['status'] ) == 'active' )
			$row_actions['deactivate'] = '<a href="' . add_query_arg( array( 'edd-action' => 'deactivate_discount', 'discount' => $discount->ID ) ) . '">' . __( 'Deactive', 'edd' ) . '</a>';
		else
			$row_actions['activate'] = '<a href="' . add_query_arg( array( 'edd-action' => 'activate_discount', 'discount' => $discount->ID ) ) . '">' . __( 'Activate', 'edd' ) . '</a>';

		$row_actions['delete'] = '<a href="' . wp_nonce_url( add_query_arg( array( 'edd-action' => 'delete_discount', 'discount' => $discount->ID ) ), 'edd_discount_nonce' ) . '">' . __( 'Delete', 'edd' ) . '</a>';

		$row_actions = apply_filters( 'edd_discount_row_actions', $row_actions, $discount );

		return $item['name'] . $this->row_actions( $row_actions );
	}

	/**
	 * Render the checkbox column
	 *
	 * @access public
	 * @since 1.4
	 * @param array $item Contains all the data for the checkbox column
	 * @return string Displays a checkbox
	 */
	function column_cb( $item ) {
		return sprintf(
			'<input type="checkbox" name="%1$s[]" value="%2$s" />',
			/*$1%s*/ $this->_args['singular'],
			/*$2%s*/ $item['ID']
		);
	}

	/**
	 * Retrieve the bulk actions
	 *
	 * @access public
	 * @since 1.4
	 * @return array $actions Array of the bulk actions
	 */
	public function get_bulk_actions() {
		$actions = array(
			'delete' => __( 'Delete', 'edd' )
		);

		return $actions;
	}

	/**
	 * Process the bulk actions
	 *
	 * @access public
	 * @since 1.4
	 * @return void
	 */
	public function process_bulk_action() {
		$ids = isset( $_GET['download'] ) ? $_GET['download'] : false;

		if ( ! is_array( $ids ) )
			$ids = array( $ids );

		foreach ( $ids as $id ) {
			if ( 'delete' === $this->current_action() ) {
				edd_remove_discount( $id );
			}
		}

	}

	/**
	 * Retrieve the discount code counts
	 *
	 * @access public
	 * @since 1.4
	 * @return void
	 */
	public function get_discount_code_counts() {
		$discount_code_count  = wp_count_posts( 'edd_discount' );
		$this->active_count   = $discount_code_count->active;
		$this->inactive_count = $discount_code_count->inactive;
		$this->total_count    = $discount_code_count->active + $discount_code_count->inactive;
	}

	/**
	 * Retrieve all the data for all the discount codes
	 *
	 * @access public
	 * @since 1.4
	 * @return array $discount_codes_data Array of all the data for the discount codes
	 */
	public function discount_codes_data() {
		$discount_codes_data = array();

		if ( isset( $_GET['paged'] ) ) $page = $_GET['paged']; else $page = 1;

		$per_page = $this->per_page;

		$mode = edd_is_test_mode() ? 'test' : 'live';

		$orderby 		= isset( $_GET['orderby'] )  ? $_GET['orderby']                  : 'ID';
		$order 			= isset( $_GET['order'] )    ? $_GET['order']                    : 'DESC';
		$order_inverse 	= $order == 'DESC'           ? 'ASC'                             : 'DESC';
		$status 		= isset( $_GET['status'] )   ? $_GET['status']                   : array( 'active', 'inactive' );
		$meta_key		= isset( $_GET['meta_key'] ) ? $_GET['meta_key']                 : null;
		$search         = isset( $_GET['s'] )        ? sanitize_text_field( $_GET['s'] ) : null;
		$order_class 	= strtolower( $order_inverse );

		$discounts = edd_get_discounts( array(
			'posts_per_page' => $per_page,
			'page'           => isset( $_GET['paged'] ) ? $_GET['paged'] : null,
			'orderby'        => $orderby,
			'order'          => $order,
			'post_status'    => $status,
			'meta_key'       => $meta_key,
			's'              => $search
		) );

		if ( $discounts ) {
			foreach ( $discounts as $discount ) {
				if ( edd_get_discount_max_uses( $discount->ID ) ) {
					$uses =  edd_get_discount_uses( $discount->ID ) . '/' . edd_get_discount_max_uses( $discount->ID );
				} else {
					$uses = edd_get_discount_uses( $discount->ID );
				}

				if ( edd_get_discount_max_uses( $discount->ID ) ) {
					$max_uses = edd_get_discount_max_uses( $discount->ID ) ? edd_get_discount_max_uses( $discount->ID ) : __( 'unlimited', 'edd' );
				} else {
					$max_uses = __( 'Unlimited', 'edd' );
				}

				$start_date = edd_get_discount_start_date( $discount->ID );

				if ( ! empty( $start_date ) ) {
					$discount_start_date =  date_i18n( get_option( 'date_format' ), strtotime( $start_date ) );
				} else {
					$discount_start_date = __( 'No start date', 'edd' );
				}

				if ( edd_get_discount_expiration( $discount->ID ) ) {
					$expiration = edd_is_discount_expired( $discount->ID ) ? __( 'Expired', 'edd' ) : date_i18n( get_option( 'date_format' ), strtotime( edd_get_discount_expiration( $discount->ID ) ) );
				} else {
					$expiration = __( 'No expiration', 'edd' );
				}

				$discount_codes_data[] = array(
					'ID' 			=> $discount->ID,
					'name' 			=> get_the_title( $discount->ID ),
					'code' 			=> edd_get_discount_code( $discount->ID ),
					'amount' 		=> edd_format_discount_rate( edd_get_discount_type( $discount->ID ), edd_get_discount_amount( $discount->ID ) ),
					'uses' 			=> $uses,
					'max_uses' 		=> $max_uses,
					'start_date' 	=> $discount_start_date,
					'expiration'	=> $expiration,
					'status'		=> ucwords( $discount->post_status ),
				);
			}
		}

		return $discount_codes_data;
	}

	/**
	 * Setup the final data for the table
	 *
	 * @access public
	 * @since 1.4
	 * @uses EDD_Discount_Codes_Table::get_columns()
	 * @uses EDD_Discount_Codes_Table::get_sortable_columns()
	 * @uses EDD_Discount_Codes_Table::process_bulk_action()
	 * @uses EDD_Discount_Codes_Table::discount_codes_data()
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

		$data = $this->discount_codes_data();

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