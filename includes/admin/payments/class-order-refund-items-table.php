<?php
/**
 * Order Refund Items Table Class.
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

// Load list table if not already loaded.
if ( ! class_exists( '\\EDD\\Admin\\Order_Items_Table' ) ) {
	require_once 'class-order-items-table.php';
}

/**
 * Order_Refund_Items_Table Class.
 *
 * Renders the Order Items table on the Order Items page.
 *
 * @since 3.0
 */
class Order_Refund_Items_Table extends Order_Items_Table {

	/**
	 * Constructor.
	 *
	 * @since 3.0
	 * @see WP_List_Table::__construct()
	 */
	public function __construct() {
		parent::__construct( array(
			'singular' => __( 'Refund Item',  'easy-digital-downloads' ),
			'plural'   => __( 'Refund Items', 'easy-digital-downloads' ),
			'ajax'     => false,
		) );

		$this->get_counts();
	}

	/**
	 * Gets the base URL for the order item list table.
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
			'view' => 'view-refund-details',
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
	 * Retrieves the table columns.
	 *
	 * @since 3.0
	 *
	 * @return array $columns Array of all the list table columns.
	 */
	public function get_columns() {
		$columns = array(
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

		// Return columns.
		return $columns;
	}

	/**
	 * Retrieves the sortable columns.
	 *
	 * @since 3.0
	 *
	 * @return array Array of all the sortable columns.
	 */
	public function get_sortable_columns() {
		return array(
			'name'     => array( 'product_name', false ),
			'quantity' => array( 'quantity', false ),
			'amount'   => array( 'amount', false ),
			'discount' => array( 'discount', false ),
			'tax'      => array( 'tax', false ),
			'total'    => array( 'total', false ),
		);
	}

	/**
	 * Retrieves the name of the primary column.
	 *
	 * @since  3.0
	 * @access protected
	 *
	 * @return string Name of the primary column.
	 */
	protected function get_primary_column_name() {
		return 'name';
	}

	/**
	 * Renders the Name Column.
	 *
	 * @since 3.0
	 *
	 * @param \EDD\Orders\Order_Item $refund_item Refund Item object.
	 * @return string Data shown in the Name column
	 */
	public function column_name( $refund_item ) {
		$refund_item_title = '<strong><a class="row-title" href="' . add_query_arg( array(
				'action' => 'edit',
				'post'  => $refund_item->product_id,
			), admin_url( 'post.php' )  ) . '">' . $refund_item->get_order_item_name() . '</a></strong>';

		$row_actions = array();
		$name        = $refund_item_title . $this->row_actions( $row_actions );

		/**
		 * Filters a Refund Item's title and actions.
		 *
		 * @since 3.0
		 *
		 * @param string                 $name Refund name and actions.
		 * @param \EDD\Orders\Order_Item $refund_item Refund Item.
		 */
		$name = apply_filters( 'edd_refund_item_title_and_actions', $name, $refund_item );

		return $name;
	}

	/**
	 * Outputs a message when no items are found.
	 *
	 * @since 3.0
	 */
	public function no_items() {
		esc_html_e( 'No refund items found.', 'easy-digital-downloads' );
	}

	/**
	 * Retrieves the bulk actions.
	 *
	 * @since 3.0
	 * @return array $actions Array of the bulk actions
	 */
	public function get_bulk_actions() {
		return array();
	}

	/**
	 * Processes the bulk actions.
	 *
	 * @since 3.0
	 */
	public function process_bulk_action() {
	}

	/**
	 * Outputs tablenav.
	 *
	 * @param string $which Which tablenav is being output.
	 */
	public function display_tablenav( $which ) {
	}
}
