<?php
/**
 * Discount Codes Table Class
 *
 * @package     EDD
 * @subpackage  Admin/Discounts
 * @copyright   Copyright (c) 2018, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.4
 * @since       3.0 - Updated to work with the discount code migration to custom tables.
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
 * @since 3.0 - Updated to work with the discount code migration to custom tables.
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
	 * Number of expired discounts.
	 *
	 * @var int
	 * @since 3.0
	 */
	public $expired_count;

	/**
	 * Get things started
	 *
	 * @since 1.4
	 * @uses EDD_Discount_Codes_Table::get_discount_code_counts()
	 * @see WP_List_Table::__construct()
	 */
	public function __construct() {
		parent::__construct( array(
			'singular' => 'discount',
			'plural'   => 'discounts',
			'ajax'     => false,
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
		if ( empty( $_REQUEST['s'] ) && ! $this->has_items() ) {
			return;
		}

		$input_id = $input_id . '-search-input';

		if ( ! empty( $_REQUEST['orderby'] ) ) {
			echo '<input type="hidden" name="orderby" value="' . esc_attr( $_REQUEST['orderby'] ) . '" />';
		}

		if ( ! empty( $_REQUEST['order'] ) ) {
			echo '<input type="hidden" name="order" value="' . esc_attr( $_REQUEST['order'] ) . '" />';
		}
		?>
		<p class="search-box">
            <label class="screen-reader-text" for="<?php echo $input_id ?>"><?php echo $text; ?>:</label>
            <input type="search" id="<?php echo $input_id ?>" name="s" value="<?php _admin_search_query(); ?>"/>
			<?php submit_button( $text, 'button', false, false, array( 'ID' => 'search-submit' ) ); ?>
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
		$base = admin_url( 'edit.php?post_type=download&page=edd-discounts' );

		$current        = isset( $_GET['status'] ) ? sanitize_key( $_GET['status'] ) : '';
		$total_count    = '&nbsp;<span class="count">(' . $this->total_count . ')</span>';
		$active_count   = '&nbsp;<span class="count">(' . $this->active_count . ')</span>';
		$inactive_count = '&nbsp;<span class="count">(' . $this->inactive_count . ')</span>';
		$expired_count =  '&nbsp;<span class="count">(' . $this->expired_count . ')</span>';

		$views = array(
			'all'      => sprintf( '<a href="%s"%s>%s</a>', remove_query_arg( 'status', $base ), $current === 'all' || $current == '' ? ' class="current"' : '', __( 'All', 'easy-digital-downloads' ) . $total_count ),
			'active'   => sprintf( '<a href="%s"%s>%s</a>', add_query_arg( 'status', 'active', $base ), $current === 'active' ? ' class="current"' : '', __( 'Active', 'easy-digital-downloads' ) . $active_count ),
			'inactive' => sprintf( '<a href="%s"%s>%s</a>', add_query_arg( 'status', 'inactive', $base ), $current === 'inactive' ? ' class="current"' : '', __( 'Inactive', 'easy-digital-downloads' ) . $inactive_count ),
			'expired'  => sprintf( '<a href="%s"%s>%s</a>', add_query_arg( 'status', 'expired', $base ), $current === 'expired' ? ' class="current"' : '', __( 'Expired', 'easy-digital-downloads' ) . $expired_count ),
		);

		return $views;
	}

	/**
	 * Retrieve the table columns
	 *
	 * @access public
	 * @since 1.4
     *
	 * @return array $columns Array of all the list table columns
	 */
	public function get_columns() {
		$columns = array(
			'cb'         => '<input type="checkbox" />',
			'name'       => __( 'Name', 'easy-digital-downloads' ),
			'code'       => __( 'Code', 'easy-digital-downloads' ),
			'amount'     => __( 'Amount', 'easy-digital-downloads' ),
			'use_count'  => __( 'Uses', 'easy-digital-downloads' ),
			'start_date' => __( 'Start Date', 'easy-digital-downloads' ),
			'end_date'   => __( 'End Date', 'easy-digital-downloads' ),
			'status'     => __( 'Status', 'easy-digital-downloads' ),
		);

		return $columns;
	}

	/**
	 * Retrieve the table's sortable columns
	 *
	 * @access public
	 * @since 1.4
     *
	 * @return array Array of all the sortable columns
	 */
	public function get_sortable_columns() {
		return array(
			'name'       => array( 'name', false ),
			'code'       => array( 'code', false ),
			'use_count'  => array( 'use_count', false ),
			'start_date' => array( 'start_date', false ),
			'end_date'   => array( 'end_date', false ),
		);
	}

	/**
	 * Gets the name of the primary column.
	 *
	 * @since 2.5
	 * @access protected
	 *
	 * @return string Name of the primary column.
	 */
	protected function get_primary_column_name() {
		return 'name';
	}

	/**
	 * This function renders most of the columns in the list table.
	 *
	 * @access public
	 * @since 1.4
	 *
	 * @param EDD_Discount $item Discount object.
	 * @param string $column_name The name of the column
	 *
	 * @return string Column Name
	 */
	function column_default( $item, $column_name ) {
		return property_exists( $item, $column_name ) ? $item->$column_name : '';
	}

	/**
	 * This function renders the amount column.
	 *
	 * @access public
	 * @since 3.0
	 *
	 * @param EDD_Discount $item Data for the discount code.
	 * @return string Formatted amount.
	 */
	public function column_amount( $item ) {
		return edd_format_discount_rate( $item->type, $item->amount );
    }

	/**
	 * This function renders the start column.
	 *
	 * @access public
	 * @since 3.0
	 *
	 * @param EDD_Discount $item Discount object.
	 * @return string Start  date
	 */
	function column_start_date( $item ) {
		$start_date = $item->get_start_date();

		if ( $start_date ) {
			$display = date_i18n( get_option( 'date_format' ), strtotime( $start_date ) );
		} else {
			$display = __( 'No start date', 'easy-digital-downloads' );
		}

		return $display;
	}

	/**
	 * This function renders the expiration column.
	 *
	 * @access public
	 * @since 3.0
	 *
	 * @param EDD_Discount $item Discount object.
	 * @return string Expiration  date
	 */
	function column_end_date( $item ) {
		$expiration = $item->get_expiration();

		if ( $expiration ) {
			$display = date( 'F j, Y', strtotime( $expiration ) );
		} else {
			$display = __( 'No expiration', 'easy-digital-downloads' );
		}

		return $display;
	}

	/**
	 * Render the Name Column
	 *
	 * @access public
	 * @since 1.4
     *
	 * @param EDD_Discount $item Discount object.
	 * @return string Data shown in the Name column
	 */
	function column_name( $item ) {
		$row_actions  = array();

		$row_actions['edit'] = '<a href="' . add_query_arg( array( 'edd-action' => 'edit_discount', 'discount' => $item->ID ) ) . '">' . __( 'Edit', 'easy-digital-downloads' ) . '</a>';

		if ( strtolower( $item->status ) == 'active' ) {
			$row_actions['deactivate'] = '<a href="' . esc_url( wp_nonce_url( add_query_arg( array( 'edd-action' => 'deactivate_discount', 'discount' => $item->ID ) ), 'edd_discount_nonce' ) ) . '">' . __( 'Deactivate', 'easy-digital-downloads' ) . '</a>';
		} elseif ( strtolower( $item->status ) == 'inactive' ) {
			$row_actions['activate'] = '<a href="' . esc_url( wp_nonce_url( add_query_arg( array( 'edd-action' => 'activate_discount', 'discount' => $item->ID ) ), 'edd_discount_nonce' ) ) . '">' . __( 'Activate', 'easy-digital-downloads' ) . '</a>';
		}

		$row_actions['delete'] = '<a href="' . esc_url( wp_nonce_url( add_query_arg( array( 'edd-action' => 'delete_discount', 'discount' => $item->ID ) ), 'edd_discount_nonce' ) ) . '">' . __( 'Delete', 'easy-digital-downloads' ) . '</a>';

		$row_actions = apply_filters( 'edd_discount_row_actions', $row_actions, $item );

		return stripslashes( $item->name ) . $this->row_actions( $row_actions );
	}

	/**
	 * Render the checkbox column
	 *
	 * @access public
	 * @since 1.4
     *
	 * @param EDD_Discount $item Discount object.
	 * @return string Displays a checkbox
	 */
	function column_cb( $item ) {
		return sprintf(
			'<input type="checkbox" name="%1$s[]" value="%2$s" />',
			/*$1%s*/ 'discount',
			/*$2%s*/ $item->ID
		);
	}

	/**
	 * Render the status column
	 *
	 * @access public
	 * @since 1.9.9
     *
	 * @param EDD_Discount $item Discount object.
	 * @return string Displays the discount status
	 */
	function column_status( $item ) {
		switch ( $item->status ) {
			case 'expired':
				$status = __( 'Expired', 'easy-digital-downloads' );
				break;
			case 'inactive':
				$status = __( 'Inactive', 'easy-digital-downloads' );
				break;
			case 'active':
			default:
				$status = __( 'Active', 'easy-digital-downloads' );
				break;
		}

		return $status;
	}

	/**
	 * Message to be displayed when there are no items
	 *
	 * @since 1.7.2
	 * @access public
	 */
	function no_items() {
		_e( 'No discounts found.', 'easy-digital-downloads' );
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
			'activate'   => __( 'Activate', 'easy-digital-downloads' ),
			'deactivate' => __( 'Deactivate', 'easy-digital-downloads' ),
			'delete'     => __( 'Delete', 'easy-digital-downloads' ),
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
		if ( empty( $_REQUEST['_wpnonce'] ) ) {
			return;
		}

		if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'bulk-discounts' ) ) {
			return;
		}

		$ids = isset( $_GET['discount'] ) ? $_GET['discount'] : false;

		if ( ! is_array( $ids ) ) {
			$ids = array( $ids );
		}

		foreach ( $ids as $id ) {
			if ( 'delete' === $this->current_action() ) {
				edd_remove_discount( $id );
			}
			if ( 'activate' === $this->current_action() ) {
				edd_update_discount_status( $id, 'active' );
			}
			if ( 'deactivate' === $this->current_action() ) {
				edd_update_discount_status( $id, 'inactive' );
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
		$discount_code_count  = EDD()->discounts->count_by_status();
		$this->active_count   = $discount_code_count->active;
		$this->inactive_count = $discount_code_count->inactive;
		$this->expired_count  = $discount_code_count->expired;
		$this->total_count    = $discount_code_count->active + $discount_code_count->inactive + $discount_code_count->expired;
	}

	/**
	 * Retrieve all the data for all the discount codes
	 *
	 * @access public
	 * @since 1.4
	 * @return array $discount_codes_data Array of all the data for the discount codes
	 */
	public function discount_codes_data() {
		$orderby = isset( $_GET['orderby'] ) ? sanitize_key( $_GET['orderby'] ) : 'ID';
		$order   = isset( $_GET['order'] ) ? sanitize_key( $_GET['order'] ) : 'DESC';
		$status  = isset( $_GET['status'] ) ? sanitize_key( $_GET['status'] ) : array( 'active', 'inactive', 'expired' );
		$search  = isset( $_GET['s'] ) ? sanitize_text_field( $_GET['s'] ) : null;

		$args = array(
			'posts_per_page' => $this->per_page,
			'paged'          => isset( $_GET['paged'] ) ? absint( $_GET['paged'] ) : 1,
			'orderby'        => $orderby,
			'order'          => $order,
			'status'         => $status,
			'search'         => $search,
		);

		return edd_get_discounts( $args );
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

		$status = isset( $_GET['status'] ) ? sanitize_key( $_GET['status'] ) : 'any';

		switch ( $status ) {
			case 'active':
				$total_items = $this->active_count;
				break;
			case 'inactive':
				$total_items = $this->inactive_count;
				break;
			case 'any':
			default:
				$total_items = $this->total_count;
				break;
		}

		$this->items = $data;

		$this->set_pagination_args( array(
			'total_items' => $total_items,
			'per_page'    => $per_page,
			'total_pages' => ceil( $total_items / $per_page ),
		) );
	}
}
