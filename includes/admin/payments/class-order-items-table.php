<?php
/**
 * Order Items Table Class.
 *
 * @package     EDD
 * @subpackage  Admin/Orders
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */
namespace EDD\Admin;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Order_Items_Table Class.
 *
 * Renders the Order Items table on the Order Items page.
 *
 * @since 3.0
 */
class Order_Items_Table extends List_Table {

	/**
	 * Constructor.
	 *
	 * @since 3.0
	 * @see   WP_List_Table::__construct()
	 */
	public function __construct() {
		parent::__construct( array(
			'singular' => __( 'Order Item',  'easy-digital-downloads' ),
			'plural'   => __( 'Order Items', 'easy-digital-downloads' ),
			'ajax'     => false,
		) );

		$this->process_bulk_action();
		$this->get_counts();
	}

	/**
	 * Get the base URL for the order item list table.
	 *
	 * @since 3.0
	 *
	 * @return string Base URL.
	 */
	public function get_base_url() {

		// Remove some query arguments
		$base = remove_query_arg( edd_admin_removable_query_args(), edd_get_admin_base_url() );

		$id = isset( $_GET['id'] )
			? absint( $_GET['id'] )
			: 0;

		// Add base query args
		return add_query_arg( array(
			'page' => 'edd-payment-history',
			'view' => 'view-order-details',
			'id'   => $id,
		), $base );
	}

	/**
	 * Retrieve the view types.
	 *
	 * @since 3.0
	 *
	 * @return array $views All the views available.
	 */
	public function get_views() {
		return array();
	}

	/**
	 * Retrieve the table columns.
	 *
	 * @since 3.0
	 *
	 * @return array $columns Array of all the list table columns.
	 */
	public function get_columns() {
		$columns = array(
			'cb'       => '<input type="checkbox" />',
			'name'     => __( 'Product', 'easy-digital-downloads' ),
			'amount'   => __( 'Amount', 'easy-digital-downloads' ),
		);

		// Maybe add quantity column.
		if ( edd_item_quantities_enabled() ) {
			$columns['quantity'] = __( 'Quantity', 'easy-digital-downloads' );
		}

		// Add discount column after quantity.
		$columns['discount'] = __( 'Discount', 'easy-digital-downloads' );

		// Maybe add tax column.
		if ( edd_use_taxes() ) {
			$columns['tax'] = __( 'Tax', 'easy-digital-downloads' );
		}

		// Total at the end.
		$columns['total'] = __( 'Total', 'easy-digital-downloads' );

		// Remove checkbox & discount column if we're adding an order.
		if ( edd_is_add_order_page() ) {
			unset( $columns['cb'] );
			unset( $columns['discount'] );

			// Move pointer to the end of the array.
			end( $columns );

			// Add a `cb` column to display a remove icon when adding a new order.
			$columns['cb'] = '';
		}

		// Return columns.
		return $columns;
	}

	/**
	 * Retrieve the sortable columns.
	 *
	 * @since 3.0
	 *
	 * @return array Array of all the sortable columns.
	 */
	public function get_sortable_columns() {
		return edd_is_add_order_page()
			? array()
			: array(
				'name'     => array( 'product_name', false ),
				'status'   => array( 'status', false ),
				'quantity' => array( 'quantity', false ),
				'amount'   => array( 'amount', false ),
				'discount' => array( 'discount', false ),
				'tax'      => array( 'tax', false ),
				'total'    => array( 'total', false ),
			);
	}

	/**
	 * Gets the name of the primary column.
	 *
	 * @since  2.5
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
	 * @since 3.0
	 *
	 * @param \EDD\Orders\Order_Item $order_item  Order item object.
	 * @param string                 $column_name The name of the column.
	 *
	 * @return string Column name.
	 */
	public function column_default( $order_item, $column_name ) {
		switch ( $column_name ) {
			case 'amount':
			case 'discount':
			case 'tax':
			case 'subtotal':
			case 'total':
				return $this->format_currency( $order_item, $column_name );
			default:
				return property_exists( $order_item, $column_name )
					? $order_item->{$column_name}
					: '';
		}
	}

	/**
	 * This private function formats a column value for currency.
	 *
	 * @since 3.0
	 *
	 * @param \EDD\Orders\Order_Item $order_item  Data for the order_item code.
	 * @param string                 $column_name String to
	 *
	 * @return string Formatted amount.
	 */
	private function format_currency( $order_item, $column_name ) {
		static $symbol = null;

		if ( is_null( $symbol ) ) {
			$currency = edd_get_order( $order_item->order_id )->currency;
			$symbol   = edd_currency_symbol( $currency );
		}

		return $symbol . edd_format_amount( $order_item->{$column_name} );
	}

	/**
	 * Render the Name Column
	 *
	 * @since 3.0
	 *
	 * @param \EDD\Orders\Order_Item $order_item Order Item object.
	 *
	 * @return string Data shown in the Name column
	 */
	public function column_name( $order_item ) {
		$base        = $this->get_base_url();
		$status      = strtolower( $order_item->status );
		$row_actions = array();

		// Edit
		$row_actions['edit'] = '<a href="' . add_query_arg( array(
				'edd-action' => 'edit_order_item',
				'order_item' => $order_item->id,
			), $base ) . '">' . __( 'Edit', 'easy-digital-downloads' ) . '</a>';

		// No state
		$state = '';

		// Active, so add "deactivate" action
		if ( empty( $status ) ) {
			$row_actions['complete'] = '<a href="' . esc_url( wp_nonce_url( add_query_arg( array(
					'edd-action' => 'handle_order_item_change',
					'status'     => 'inherit',
					'order_item' => $order_item->id,
				), $base ), 'edd_order_item_nonce' ) ) . '">' . __( 'Complete', 'easy-digital-downloads' ) . '</a>';

		} elseif ( in_array( $status, array( 'inherit', 'publish' ), true ) ) {

			if ( edd_get_download_files( $order_item->id, $order_item->price_id ) ) {
				$row_actions['copy'] = '<span class="edd-copy-download-link-wrapper"><a href="" class="edd-copy-download-link" data-download-id="' . esc_attr( $order_item->id ) . '" data-price-id="' . esc_attr( $order_item->id ) . '">' . __( 'Link', 'easy-digital-downloads' ) . '</a>';
			}

			$row_actions['refund'] = '<a href="' . esc_url( wp_nonce_url( add_query_arg( array(
					'edd-action' => 'handle_order_item_change',
					'status'     => 'refunded',
					'order_item' => $order_item->id,
				), $base ), 'edd_order_item_nonce' ) ) . '">' . __( 'Refund', 'easy-digital-downloads' ) . '</a>';

			// Inactive, so add "activate" action
		} elseif ( 'refunded' === $status ) {
			$state                   = __( 'Refunded', 'easy-digital-downloads' );
			$row_actions['activate'] = '<a href="' . esc_url( wp_nonce_url( add_query_arg( array(
					'edd-action' => 'handle_order_item_change',
					'status'     => 'inherit',
					'order_item' => $order_item->id,
				), $base ), 'edd_order_item_nonce' ) ) . '">' . __( 'Reverse', 'easy-digital-downloads' ) . '</a>';
		}

		// Delete
		$row_actions['delete'] = '<a href="' . esc_url( wp_nonce_url( add_query_arg( array(
				'edd-action' => 'delete_order_item',
				'order_item' => $order_item->id,
			), $base ), 'edd_order_item_nonce' ) ) . '">' . __( 'Delete', 'easy-digital-downloads' ) . '</a>';

		// Filter all order_item row actions
		$row_actions = apply_filters( 'edd_order_item_row_actions', $row_actions, $order_item );

		// Format order item state
		if ( ! empty( $state ) ) {
			$state = ' &mdash; <span class="order-item-state">' . $state . '</span>';
		}

		// Wrap order_item title in strong anchor
		$order_item_title = '<strong><a class="row-title" href="' . add_query_arg( array(
				'edd-action' => 'edit_order_item',
				'order_item' => $order_item->id,
			), $base ) . '">' . $order_item->get_order_item_name() . '</a>' . $state . '</strong>';

		// Return order_item title & row actions
		return $order_item_title . $this->row_actions( $row_actions );
	}

	/**
	 * Render the checkbox column
	 *
	 * @since 3.0
	 *
	 * @param \EDD\Orders\Order_Item $order_item Order Item object.
	 *
	 * @return string Displays a checkbox
	 */
	public function column_cb( $order_item ) {
		return sprintf(
			'<input type="checkbox" name="%1$s[]" value="%2$s" />',
			/*$1%s*/
			'order_item',
			/*$2%s*/
			$order_item->id
		);
	}

	/**
	 * Render the status column
	 *
	 * @since 3.0
	 *
	 * @param \EDD\Orders\Order_Item $order_item Order Item object.
	 *
	 * @return string Displays the order_item status
	 */
	public function column_status( $order_item ) {
		return ! empty( $order_item->status )
			? ucwords( $order_item->status )
			: '&mdash;';
	}

	/**
	 * Message to be displayed when there are no items
	 *
	 * @since 3.0
	 */
	public function no_items() {
		edd_is_add_order_page()
			? esc_html_e( 'Add an order item.', 'easy-digital-downloads' )
			: esc_html_e( 'No order items found.', 'easy-digital-downloads' );
	}

	/**
	 * Retrieve the bulk actions
	 *
	 * @since 3.0
	 * @return array $actions Array of the bulk actions
	 */
	public function get_bulk_actions() {
		return array(
			'refund' => __( 'Refund', 'easy-digital-downloads' ),
			'delete' => __( 'Delete', 'easy-digital-downloads' ),
		);
	}

	/**
	 * Process the bulk actions
	 *
	 * @since 3.0
	 */
	public function process_bulk_action() {
		if ( empty( $_REQUEST['_wpnonce'] ) ) {
			return;
		}

		if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'bulk-order_items' ) ) {
			return;
		}

		$ids = isset( $_GET['order_item'] )
			? $_GET['order_item']
			: false;

		if ( ! is_array( $ids ) ) {
			$ids = array( $ids );
		}

		foreach ( $ids as $id ) {
			switch ( $this->current_action() ) {
				case 'delete':
					edd_delete_order_item( $id );
					break;
				case 'refund':
					edd_update_order_item( $id, array(
						'status' => 'refunded',
					) );
					break;
				case 'complete':
					edd_update_order_item( $id, array(
						'status' => 'publish',
					) );
					break;
			}
		}
	}

	/**
	 * Retrieve the order_item code counts
	 *
	 * @since 3.0
	 */
	public function get_counts() {

		// Maybe retrieve counts.
		if ( ! edd_is_add_order_page() ) {

			// Check for an order ID
			$order_id = ! empty( $_GET['id'] )
				? absint( $_GET['id'] ) // WPCS: CSRF ok.
				: 0;

			// Get counts
			$this->counts = edd_get_order_item_counts( array(
				'order_id' => $order_id
			) );
		}
	}

	/**
	 * Retrieve all the data for all the order_item codes
	 *
	 * @since 3.0
	 * @return array $order_items_data Array of all the data for the order_item codes
	 */
	public function order_items_data() {

		// Return if we are adding a new order.
		if ( edd_is_add_order_page() ) {
			return array();
		}

		// Query args.
		$status  = $this->get_status();
		$orderby = isset( $_GET['orderby'] ) ? sanitize_key( $_GET['orderby'] ) : 'id';
		$order   = isset( $_GET['order'] ) ? sanitize_key( $_GET['order'] ) : 'DESC';
		$search  = isset( $_GET['s'] ) ? sanitize_text_field( $_GET['s'] ) : null;
		$paged   = isset( $_GET['paged'] ) ? absint( $_GET['paged'] ) : 1;
		$id      = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0;

		// Get order items.
		return edd_get_order_items( array(
			'order_id' => $id,
			'number'   => $this->per_page,
			'paged'    => $paged,
			'orderby'  => $orderby,
			'order'    => $order,
			'status'   => $status,
			'search'   => $search,
		) );
	}

	/**
	 * Setup the final data for the table
	 *
	 * @since 3.0
	 */
	public function prepare_items() {
		$this->_column_headers = array(
			$this->get_columns(),
			array(),
			$this->get_sortable_columns(),
		);

		$this->items = $this->order_items_data();

		$status = $this->get_status( 'total' );

		// Maybe setup pagination.
		if ( ! edd_is_add_order_page() ) {
			$this->set_pagination_args( array(
				'total_items' => $this->counts[ $status ],
				'per_page'    => $this->per_page,
				'total_pages' => ceil( $this->counts[ $status ] / $this->per_page ),
			) );
		}
	}

	/**
	 * Generates content for a single row of the table
	 *
	 * @since 3.0
	 *
	 * @param \EDD\Orders\Order_Item $item Order item object.
	 */
	public function single_row( $item ) {

		// Status.
		$classes = array_map( 'sanitize_html_class', array(
			'order-' . $item->order_id,
			$item->status,
		) );

		// Turn into a string.
		$class = implode( ' ', $classes );
		?>

		<tr id="order-item-<?php echo esc_attr( $item->id ); ?>" class="<?php echo esc_html( $class ); ?>">
			<?php $this->single_row_columns( $item ); ?>
		</tr>

		<?php
	}
}