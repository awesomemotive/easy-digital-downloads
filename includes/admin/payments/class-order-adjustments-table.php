<?php
/**
 * Order Adjustments Table Class
 *
 * @package     EDD
 * @subpackage  Admin/OrderAdjustments
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

// Load WP_List_Table if not loaded
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * EDD_Order_Adjustment_Table Class
 *
 * Renders the Order Adjustments table on the Order Adjustments page
 *
 * @since 3.0
 */
class EDD_Order_Adjustment_Table extends WP_List_Table {

	/**
	 * Number of results to show per page
	 *
	 * @var string
	 * @since 3.0
	 */
	public $per_page = 30;

	/**
	 * Order Adjustment counts, keyed by status
	 *
	 * @var array
	 * @since 3.0
	 */
	public $counts = array(
		'tax_rate' => 0,
		'discount' => 0,
		'fee'      => 0,
		'total'    => 0
	);

	/**
	 * Get things started
	 *
	 * @since 3.0
	 * @see WP_List_Table::__construct()
	 */
	public function __construct() {
		parent::__construct( array(
			'singular' => __( 'Order Adjustment',  'easy-digital-downloads' ),
			'plural'   => __( 'Order Adjustments', 'easy-digital-downloads' ),
			'ajax'     => false,
		) );

		$this->process_bulk_action();
		$this->get_counts();
	}

	/**
	 * Show the search field
	 *
	 * @access public
	 * @since 3.0
	 *
	 * @param string $text Label for the search box
	 * @param string $input_id ID of the search box
	 */
	public function search_box( $text, $input_id ) {

		// Bail if no customers and no search
		if ( empty( $_REQUEST['s'] ) && ! $this->has_adjustments() ) {
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
            <label class="screen-reader-text" for="<?php echo esc_attr( $input_id ) ?>"><?php echo esc_html( $text ); ?>:</label>
            <input type="search" id="<?php echo esc_attr( $input_id ); ?>" name="s" value="<?php _admin_search_query(); ?>"/>
			<?php submit_button( esc_html( $text ), 'button', false, false, array( 'ID' => 'search-submit' ) ); ?>
        </p>

		<?php
	}

	/**
	 * Get the base URL for the order_adjustment list table
	 *
	 * @since 3.0
	 *
	 * @return string
	 */
	public function get_base_url() {

		// Remove some query arguments
		$base = remove_query_arg( edd_admin_removable_query_args(), admin_url( 'edit.php' ) );

		$id = isset( $_GET['id'] )
			? absint( $_GET['id'] )
			: 0;

		// Add base query args
		return add_query_arg( array(
			'post_type' => 'download',
			'page'      => 'edd-payment-history',
			'view'      => 'view-order-details',
			'id'        => $id
		), $base );
	}

	/**
	 * Retrieve the view types
	 *
	 * @access public
	 * @since 3.0
	 *
	 * @return array $views All the views available
	 */
	public function get_views() {
		return array();
	}

	/**
	 * Retrieve the table columns
	 *
	 * @access public
	 * @since 3.0
	 *
	 * @return array $columns Array of all the list table columns
	 */
	public function get_columns() {
		return array(
			'cb'     => '<input type="checkbox" />',
			'name'   => __( 'Name',        'easy-digital-downloads' ),
			'type'   => __( 'Type',        'easy-digital-downloads' ),
			'desc'   => __( 'Description', 'easy-digital-downloads' ),
			'amount' => __( 'Amount',      'easy-digital-downloads' )
		);
	}

	/**
	 * Retrieve the sortable columns
	 *
	 * @access public
	 * @since 3.0
	 *
	 * @return array Array of all the sortable columns
	 */
	public function get_sortable_columns() {
		return array(
			'name'     => array( 'name',        false ),
			'type'     => array( 'type',        false ),
			'desc'     => array( 'descirption', false ),
			'amount'   => array( 'amount',      false )
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
	 * @since 3.0
	 *
	 * @param EDD\Orders\Order_Adjustment $order_adjustment Order_Adjustment object.
	 * @param string $column_name The name of the column
	 *
	 * @return string Column Name
	 */
	public function column_default( $order_adjustment, $column_name ) {
		return property_exists( $order_adjustment, $column_name )
			? $order_adjustment->{$column_name}
			: '';
	}

	/**
	 * This function renders the amount column.
	 *
	 * @access public
	 * @since 3.0
	 *
	 * @param EDD\Orders\Order_Adjustment $order_adjustment Data for the order_adjustment code.
	 * @return string Formatted amount.
	 */
	public function column_amount( $order_adjustment ) {
		$currency = edd_get_order( $order_adjustment->object_id )->currency;

		return edd_currency_symbol( $currency ) . edd_format_amount( $order_adjustment->amount );
	}

	/**
	 * Render the Name Column
	 *
	 * @access public
	 * @since 3.0
	 *
	 * @param EDD\Orders\Order_Adjustment $order_adjustment Order_Adjustment object.
	 * @return string Data shown in the Name column
	 */
	public function column_name( $order_adjustment ) {
		$base         = $this->get_base_url();
		$row_actions  = array();

		// Edit
		$row_actions['edit'] = '<a href="' . add_query_arg( array(
			'edd-action'       => 'edit_order_adjustment',
			'order_adjustment' => $order_adjustment->id,
		), $base ) . '">' . __( 'Edit', 'easy-digital-downloads' ) . '</a>';

		// Delete
		$row_actions['delete'] = '<a href="' . esc_url( wp_nonce_url( add_query_arg( array(
			'edd-action'       => 'delete_order_adjustment',
			'order_adjustment' => $order_adjustment->id,
		), $base ), 'edd_order_adjustment_nonce' ) ) . '">' . __( 'Delete', 'easy-digital-downloads' ) . '</a>';

		// Filter all order_adjustment row actions
		$row_actions = apply_filters( 'edd_order_adjustment_row_actions', $row_actions, $order_adjustment );

		// Update name based on type
		if ( 'discount' === $order_adjustment->type ) {
			$name = edd_get_discount_field( $order_adjustment->type_id, 'name' );

		} elseif ( 'tax_rate' === $order_adjustment->type ) {
			$name = __( 'Tax', 'easy-digital-downloads' );

		} elseif ( 'fee' === $order_adjustment->type ) {
			$name = __( 'Fee', 'easy-digital-downloads' );

		} else {
			$name = '&mdash;';
		}

		// Wrap order_adjustment title in strong anchor
		$order_adjustment_title = '<strong><a class="row-title" href="' . add_query_arg( array(
			'edd-action'       => 'edit_order_adjustment',
			'order_adjustment' => $order_adjustment->id,
		), $base ) . '">' . esc_html( $name ) . '</a></strong>';

		// Return order_adjustment title & row actions
		return $order_adjustment_title . $this->row_actions( $row_actions );
	}

	/**
	 * Render the checkbox column
	 *
	 * @access public
	 * @since 3.0
	 *
	 * @param EDD\Orders\Order_Adjustment $order_adjustment Order_Adjustment object.
	 *
	 * @return string Displays a checkbox
	 */
	public function column_cb( $order_adjustment ) {
		return sprintf(
			'<input type="checkbox" name="%1$s[]" value="%2$s" />',
			/*$1%s*/ 'order_adjustment',
			/*$2%s*/ $order_adjustment->id
		);
	}

	/**
	 * Render the checkbox column
	 *
	 * @access public
	 * @since 3.0
	 *
	 * @param EDD\Orders\Order_Adjustment $order_adjustment Order_Adjustment object.
	 *
	 * @return string Displays the order type
	 */
	public function column_type( $order_adjustment ) {
		return ! empty( $order_adjustment->type )
			? ucwords( str_replace( array( '_', '-' ), ' ', $order_adjustment->type ) )
			: '&mdash;';
	}


	/**
	 * Render the checkbox column
	 *
	 * @access public
	 * @since 3.0
	 *
	 * @param EDD\Orders\Order_Adjustment $order_adjustment Order_Adjustment object.
	 *
	 * @return string Displays the order type
	 */
	public function column_desc( $order_adjustment ) {
		$value = $order_adjustment->description;

		// Update desc based on type
		if ( 'discount' === $order_adjustment->type ) {
			$desc = '<code>' . sanitize_key( $value ) . '</code>';

		} elseif ( 'tax_rate' === $order_adjustment->type ) {
			$desc = $value;

		} elseif ( 'fee' === $order_adjustment->type ) {
			$desc = edd_get_order_adjustment_meta( $order_adjustment->id, 'fee_id', true );
		}

		return ! empty( $desc )
			? $desc
			: '&mdash;';
	}

	/**
	 * Message to be displayed when there are no adjustments
	 *
	 * @since 3.0
	 * @access public
	 */
	public function no_items() {
		_e( 'No order adjustments found.', 'easy-digital-downloads' );
	}

	/**
	 * Retrieve the bulk actions
	 *
	 * @access public
	 * @since 3.0
	 * @return array $actions Array of the bulk actions
	 */
	public function get_bulk_actions() {
		return array(
			'delete' => __( 'Delete', 'easy-digital-downloads' )
		);
	}

	/**
	 * Process the bulk actions
	 *
	 * @access public
	 * @since 3.0
	 */
	public function process_bulk_action() {
		if ( empty( $_REQUEST['_wpnonce'] ) ) {
			return;
		}

		if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'bulk-order_adjustments' ) ) {
			return;
		}

		$ids = isset( $_GET['order_adjustment'] )
			? $_GET['order_adjustment']
			: false;

		if ( ! is_array( $ids ) ) {
			$ids = array( $ids );
		}

		foreach ( $ids as $id ) {
			switch ( $this->current_action() ) {
				case 'delete' :
					edd_delete_order_adjustment( $id );
					break;
			}
		}
	}

	/**
	 * Retrieve the order_adjustment code counts
	 *
	 * @access public
	 * @since 3.0
	 */
	public function get_counts() {
		$this->counts = edd_get_order_adjustment_counts( $_GET['id'] );
	}

	/**
	 * Retrieve all the data for all the order_adjustment codes
	 *
	 * @access public
	 * @since 3.0
	 * @return array $order_adjustments_data Array of all the data for the order_adjustment codes
	 */
	public function order_adjustments_data() {

		// Query args
		$orderby = isset( $_GET['orderby'] ) ? sanitize_key( $_GET['orderby']  ) : 'id';
		$order   = isset( $_GET['order']   ) ? sanitize_key( $_GET['order']    ) : 'DESC';
		$type    = isset( $_GET['type']    ) ? sanitize_key( $_GET['type']     ) : '';
		$search  = isset( $_GET['s']       ) ? sanitize_text_field( $_GET['s'] ) : null;
		$paged   = isset( $_GET['paged']   ) ? absint( $_GET['paged']          ) : 1;
		$id      = isset( $_GET['id']      ) ? absint( $_GET['id']             ) : 0;

		// Get order_adjustments
		return edd_get_order_adjustments( array(
			'object_id'   => $id,
			'object_type' => 'order',
			'number'      => $this->per_page,
			'paged'       => $paged,
			'orderby'     => $orderby,
			'order'       => $order,
			'type'        => $type,
			'search'      => $search
		) );
	}

	/**
	 * Setup the final data for the table
	 *
	 * @access public
	 * @since 3.0
	 * @uses EDD_Order_Adjustment_Table::get_columns()
	 * @uses EDD_Order_Adjustment_Table::get_sortable_columns()
	 * @uses EDD_Order_Adjustment_Table::order_adjustments_data()
	 * @uses WP_List_Table::get_pagenum()
	 * @uses WP_List_Table::set_pagination_args()
	 */
	public function prepare_items() {
		$this->_column_headers = array(
			$this->get_columns(),
			array(),
			$this->get_sortable_columns()
		);

		$this->items = $this->order_adjustments_data();

		$type = isset( $_GET['type'] )
			? sanitize_key( $_GET['type'] )
			: 'total';

		// Setup pagination
		$this->set_pagination_args( array(
			'total_items' => $this->counts[ $type ],
			'per_page'    => $this->per_page,
			'total_pages' => ceil( $this->counts[ $type ] / $this->per_page )
		) );
	}

	/**
	 * Generates content for a single row of the table
	 *
	 * @since 3.0
	 * @access public
	 *
	 * @param object $item The current item
	 */
	public function single_row( $item ) {

		// Status
		$classes = array_map( 'sanitize_html_class', array(
			'order-'. $item->order_id,
			$item->type
		) );

		// Turn into a string
		$class = implode( ' ', $classes ); ?>

		<tr id="order-item-<?php echo esc_attr( $item->id ); ?>" class="<?php echo esc_html( $class ); ?>">
			<?php $this->single_row_columns( $item ); ?>
		</tr>

		<?php
	}
}