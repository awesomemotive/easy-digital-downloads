<?php
/**
 * Order Functions
 *
 * @package     EDD
 * @subpackage  Orders
 * @copyright   Copyright (c) 2018, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0.0
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Add an order.
 *
 * @since 3.0
 *
 * @param array $data
 * @return int|false ID of newly created order, false on error.
 */
function edd_add_order( $data = array() ) {
	$orders = new EDD\Database\Queries\Order();

	return $orders->add_item( $data );
}

/**
 * Delete an order.
 *
 * @since 3.0
 *
 * @param int $order_id Order ID.
 * @return int
 */
function edd_delete_order( $order_id = 0 ) {
	$orders = new EDD\Database\Queries\Order();

	return $orders->delete_item( $order_id );
}

/**
 * Update an order.
 *
 * @since 3.0
 *
 * @param int   $order_id Order ID.
 * @param array $data   Updated order data.
 * @return bool Whether or not the order was updated.
 */
function edd_update_order( $order_id = 0, $data = array() ) {
	$orders = new EDD\Database\Queries\Order();

	return $orders->update_item( $order_id, $data );
}

/**
 * Get an order by ID.
 *
 * @since 3.0
 *
 * @param int $order_id Order ID.
 * @return EDD\Orders\Order Order object.
 */
function edd_get_order( $order_id = 0 ) {
	return edd_get_order_by( 'id', $order_id );
}

/**
 * Get an order by a specific field value.
 *
 * @since 3.0
 *
 * @param string $field Database table field.
 * @param string $value Value of the row.
 * @return object
 */
function edd_get_order_by( $field = '', $value = '' ) {

	// Instantiate a query object
	$orders = new EDD\Database\Queries\Order();

	// Get an item
	return $orders->get_item_by( $field, $value );
}

/**
 * Query for orders.
 *
 * @since 3.0
 *
 * @param array $args
 * @return array
 */
function edd_get_orders( $args = array() ) {

	// Parse args
	$r = wp_parse_args( $args, array(
		'number' => 30
	) );

	// Instantiate a query object
	$orders = new EDD\Database\Queries\Order();

	// Return items
	return $orders->query( $r );
}

/**
 * Count orders.
 *
 * @since 3.0
 *
 * @param array $args
 * @return int
 */
function edd_count_orders( $args = array() ) {

	// Parse args
	$r = wp_parse_args( $args, array(
		'count' => true
	) );

	// Query for count(s)
	$orders = new EDD\Database\Queries\Order( $r );

	// Return count(s)
	return absint( $orders->found_items );
}

/**
 * Query for and return array of order counts, keyed by status.
 *
 * @since 3.0
 *
 * @return array
 */
function edd_get_order_counts() {

	// Default statuses
	$defaults = array_fill_keys( array_keys( edd_get_payment_statuses() ), 0 );

	// Query for count
	$counts = edd_get_orders( array(
		'count'   => true,
		'groupby' => 'status'
	) );

	// Default array
	$o = array();

	// Loop through counts and shape return value
	if ( ! empty( $counts ) ) {

		// Loop through statuses
		foreach ( $counts as $item ) {
			if ( empty( $item['status'] ) ) {
				continue;
			}

			$o[ $item['status'] ] = absint( $item['count'] );
		}
	}

	// Return counts
	return array_merge( $defaults, $o );
}

/**
 * Check if an order can be recovered.
 *
 * @since 3.0
 *
 * @param int $order_id Order ID.
 * @return bool Whether or not the order can be recovered.
 */
function edd_is_order_recoverable( $order_id = 0 ) {
	$order = edd_get_order( $order_id );

	if ( ! $order ) {
		return false;
	}

	$recoverable_statuses = apply_filters( 'edd_recoverable_payment_statuses', array( 'pending', 'abandoned', 'failed' ) );

	$transaction_id = $order->get_transaction_id();

	if ( in_array( $order->get_status(), $recoverable_statuses ) && empty( $transaction_id ) ) {
		return true;
	}

	return false;
}

/**
 * Generate the correct parameters required to insert a new order into the database
 * based on the order details passed by the gateway.
 *
 * @since 3.0
 *
 * @param array $order_data Order data.
 *
 * @return int|bool Integer of order ID if successful, false otherwise.
 */
function edd_build_order( $order_data = array() ) {
	if (  empty( $order_data ) ) {
		return false;
	}

	/** Setup order information ***************************************************/

	$gateway = ! empty( $order_data['gateway'] ) ? $order_data['gateway'] : '';
	$gateway = empty( $gateway ) && isset( $_POST['edd-gateway'] ) ? $_POST['edd-gateway'] : $gateway;

	// Build order information based on data passed from the gateway.
	$order_args = array(
		'parent'      => ! empty( $order_data['parent'] ) ? absint( $order_data['parent'] ) : '',
		'status'      => ! empty( $order_data['status'] ) ? $order_data['status'] : 'pending',
		'user_id'     => $order_data['user_info']['id'],
		'email'       => $order_data['user_info']['email'],
		'ip'          => edd_get_ip(),
		'gateway'     => $gateway,
		'mode'        => edd_is_test_mode() ? 'test' : 'live',
		'currency'    => ! empty( $order_data['currency'] ) ? $order_data['currency'] : edd_get_currency(),
		'payment_key' => $order_data['purchase_key'],
	);

	/** Setup customer ************************************************************/

	$customer = new stdClass;

	if ( did_action( 'edd_pre_process_purchase' ) && is_user_logged_in() ) {
		$customer = new EDD_Customer( get_current_user_id(), true );

		// Customer is logged in but used a different email to purchase so we need to assign that email address to their customer record.
		if ( ! empty( $customer->id ) && $order_args['email'] != $customer->email ) {
			$customer->add_email( $order_args['email'] );
		}
	}

	if ( empty( $customer->id ) ) {
		$customer = new EDD_Customer( $this->email );

		if ( empty( $order_data['user_info']['first_name'] ) && empty( $order_data['user_info']['last_name'] ) ) {
			$name = $order_args['email'];
		} else {
			$name = $order_data['user_info']['first_name'] . ' ' . $order_data['user_info']['last_name'];
		}

		$customer->create( array(
			'name'    => $name,
			'email'   => $order_args['email'],
			'user_id' => $order_args['user_id'],
		) );
	}

	$order_args['customer_id'] = $customer->id;

	/** Insert order **************************************************************/

	// Add order into the edd_orders table.
	$order_id = edd_add_order( $order_args );

	// Attach order to the customer record.
	$customer->attach_payment( $order_id, false );

	/** Insert order meta *********************************************************/
	edd_add_order_meta( $order_id, 'user_info', array(
		'first_name' => $order_data['user_info']['first_name'],
		'last_name'  => $order_data['user_info']['last_name'],
		'address'    => $order_data['user_info']['address'],
	) );

	/** Insert order items ********************************************************/


	/** Insert order adjustments **************************************************/


	/** Calculate totals **********************************************************/
	$subtotal = 0.00;
	$tax = 0.00;
	$discount = 0.00;

	do_action( 'edd_insert_payment', $order_id, $order_data );

	if ( ! $order_id ) {
		return false;
	} else {
		return $order_id;
	}
}

/** Order Items ***************************************************************/

/**
 * Add an order item.
 *
 * @since 3.0
 *
 * @param array $data
 * @return int|false ID of newly created order, false on error.
 */
function edd_add_order_item( $data = array() ) {

	// An order ID and product ID must be supplied for every order that is
	// inserted into the database.
	if ( empty( $data['order_id'] ) || empty( $data['product_id'] ) ) {
		return false;
	}

	// Instantiate a query object
	$orders = new EDD\Database\Queries\Order_Item();

	return $orders->add_item( $data );
}

/**
 * Delete an order item.
 *
 * @since 3.0
 *
 * @param int $order_item_id Order item ID.
 * @return int
 */
function edd_delete_order_item( $order_item_id = 0 ) {
	$orders = new EDD\Database\Queries\Order_Item();

	return $orders->delete_item( $order_item_id );
}

/**
 * Update an order item.
 *
 * @since 3.0
 *
 * @param int   $order_id API request order ID.
 * @param array $data   Updated file download order data.
 * @return bool Whether or not the file download order was updated.
 */
function edd_update_order_item( $order_id = 0, $data = array() ) {
	$orders = new EDD\Database\Queries\Order_Item();

	return $orders->update_item( $order_id, $data );
}

/**
 * Get an order item by ID.
 *
 * @since 3.0
 *
 * @param int $order_id Order ID.
 * @return object
 */
function edd_get_order_item( $order_id = 0 ) {
	return edd_get_order_item_by( 'id', $order_id );
}

/**
 * Get an order item by field and value.
 *
 * @since 3.0
 *
 * @param string $field Database table field.
 * @param string $value Value of the row.
 * @return object
 */
function edd_get_order_item_by( $field = '', $value = '' ) {

	// Instantiate a query object
	$orders = new EDD\Database\Queries\Order_Item();

	// Return item
	return $orders->get_item_by( $field, $value );
}

/**
 * Query for order items.
 *
 * @since 3.0
 *
 * @param array $args
 * @return array
 */
function edd_get_order_items( $args = array() ) {

	// Parse args
	$r = wp_parse_args( $args, array(
		'number' => 30
	) );

	// Instantiate a query object
	$orders = new EDD\Database\Queries\Order_Item();

	// Return items
	return $orders->query( $r );
}

/**
 * Count order items.
 *
 * @since 3.0
 *
 * @param array $args
 * @return int
 */
function edd_count_order_items( $args = array() ) {

	// Parse args
	$r = wp_parse_args( $args, array(
		'count' => true
	) );

	// Query for count(s)
	$orders = new EDD\Database\Queries\Order_Item( $r );

	// Return count(s)
	return absint( $orders->found_items );
}

/** Order Adjustments *********************************************************/

/**
 * Add an order adjustment.
 *
 * @since 3.0
 *
 * @param array $data
 * @return int|false ID of newly created order adjustment, false on error.
 */
function edd_add_order_adjustment( $data ) {

	// An object ID and object ID must be supplied for every order that is
	// inserted into the database.
	if ( empty( $data['object_id'] ) || empty( $data['object_type'] ) ) {
		return false;
	}

	// Instantiate a query object
	$orders = new EDD\Database\Queries\Order_Adjustment();

	return $orders->add_item( $data );
}

/**
 * Delete an API request order.
 *
 * @since 3.0
 *
 * @param int $order_id API request order ID.
 * @return int
 */
function edd_delete_order_adjustment( $order_id = 0 ) {
	$orders = new EDD\Database\Queries\Order_Adjustment();

	return $orders->delete_item( $order_id );
}

/**
 * Update an API request order.
 *
 * @since 3.0
 *
 * @param int   $order_id API request order ID.
 * @param array $data   Updated API request order data.
 * @return bool Whether or not the API request order was updated.
 */
function edd_update_order_adjustment( $order_id = 0, $data = array() ) {
	$orders = new EDD\Database\Queries\Order_Adjustment();

	return $orders->update_item( $order_id, $data );
}

/**
 * Get an API request order by ID.
 *
 * @since 3.0
 *
 * @param int $order_id API request order ID.
 * @return object
 */
function edd_get_order_adjustment( $order_id = 0 ) {
	return edd_get_order_adjustment_by( 'id', $order_id );
}

/**
 * Get an API request order by a specific field value.
 *
 * @since 3.0
 *
 * @param string $field Database table field.
 * @param string $value Value of the row.
 * @return object
 */
function edd_get_order_adjustment_by( $field = '', $value = '' ) {
	$orders = new EDD\Database\Queries\Order_Adjustment();

	// Return note
	return $orders->get_item_by( $field, $value );
}

/**
 * Query for API request orders.
 *
 * @since 3.0
 *
 * @param array $args
 * @return array
 */
function edd_get_order_adjustments( $args = array() ) {

	// Parse args
	$r = wp_parse_args( $args, array(
		'number' => 30
	) );

	// Instantiate a query object
	$orders = new EDD\Database\Queries\Order_Adjustment();

	// Return orders
	return $orders->query( $r );
}

/**
 * Count API request orders.
 *
 * @since 3.0
 *
 * @param array $args
 * @return int
 */
function edd_count_order_adjustments( $args = array() ) {

	// Parse args
	$r = wp_parse_args( $args, array(
		'count' => true
	) );

	// Query for count(s)
	$orders = new EDD\Database\Queries\Order_Adjustment( $r );

	// Return count(s)
	return absint( $orders->found_items );
}