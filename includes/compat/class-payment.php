<?php
/**
 * Backwards Compatibility Handler for Payments.
 *
 * @package     EDD
 * @subpackage  Compat
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */
namespace EDD\Compat;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Payment Class.
 *
 * EDD 3.0 moves away from storing payment data in wp_posts. This class handles all the backwards compatibility for the
 * transition to custom tables.
 *
 * @since 3.0
 */
class Payment extends Base {

	/**
	 * Holds the component for which we are handling back-compat. There is a chance that two methods have the same name
	 * and need to be dispatched to completely other methods. When a new instance of Back_Compat is created, a component
	 * can be passed to the constructor which will allow __call() to dispatch to the correct methods.
	 *
	 * @since 3.0
	 * @access protected
	 * @var string
	 */
	protected $component = 'payment';

	/**
	 * Backwards compatibility hooks for payments.
	 *
	 * @since 3.0
	 * @access protected
	 */
	protected function hooks() {

		/* Actions ************************************************************/

		add_action( 'pre_get_posts', array( $this, 'pre_get_posts' ), 99, 1 );

		/* Filters ************************************************************/

		add_filter( 'query',                array( $this, 'wp_count_posts'       ), 10, 1 );
		add_filter( 'get_post_metadata',    array( $this, 'get_post_metadata'    ), 99, 4 );
		add_filter( 'update_post_metadata', array( $this, 'update_post_metadata' ), 99, 5 );
		add_filter( 'add_post_metadata',    array( $this, 'update_post_metadata' ), 99, 5 );
	}

	/**
	 * Backwards compatibility layer for wp_count_posts().
	 *
	 * This is here for backwards compatibility purposes with the migration to custom tables in EDD 3.0.
	 *
	 * @since 3.0
	 *
	 * @param string $query SQL request.
	 *
	 * @return string $request Rewritten SQL query.
	 */
	public function wp_count_posts( $query ) {
		global $wpdb;

		$expected = "SELECT post_status, COUNT( * ) AS num_posts FROM {$wpdb->posts} WHERE post_type = 'edd_payment' GROUP BY post_status";

		if ( $expected === $query ) {
			$query = "SELECT status AS post_status, COUNT( * ) AS num_posts FROM {$wpdb->edd_orders} GROUP BY post_status";
		}

		return $query;
	}

	/**
	 * Add a message for anyone to trying to get payments via get_post/get_posts/WP_Query.
	 * Force filters to run for all queries that have `edd_discount` as the post type.
	 *
	 * This is here for backwards compatibility purposes with the migration to custom tables in EDD 3.0.
	 *
	 * @since 3.0
	 *
	 * @param \WP_Query $query
	 */
	public function pre_get_posts( $query ) {
		global $wpdb;

		if ( 'pre_get_posts' !== current_filter() ) {
			$message = __( 'This function is not meant to be called directly. It is only here for backwards compatibility purposes.', 'easy-digital-downloads' );
			_doing_it_wrong( __FUNCTION__, $message, 'EDD 3.0' );
		}

		// Bail if not a payment
		if ( 'edd_payment' !== $query->get( 'post_type' ) ) {
			return;
		}

		// Force filters to run
		$query->set( 'suppress_filters', false );

		// Setup doing-it-wrong message
		$message = sprintf(
			__( 'As of Easy Digital Downloads 3.0, orders no longer exist in the %1$s table. They have been migrated to %2$s. Orders should be accessed using %3$s or %4$s. See %5$s for more information.', 'easy-digital-downloads' ),
			'<code>' . $wpdb->posts . '</code>',
			'<code>' . edd_get_component_interface( 'order', 'table' )->table_name . '</code>',
			'<code>edd_get_orders()</code>',
			'<code>edd_get_order()</code>',
			'https://easydigitaldownloads.com/development/'
		);

		_doing_it_wrong( 'get_posts()/get_post()/WP_Query', $message, 'EDD 3.0' );
	}

	/**
	 * Backwards compatibility filters for get_post_meta() calls on payments.
	 *
	 * @since 3.0
	 *
	 * @param  mixed  $value       The value get_post_meta would return if we don't filter.
	 * @param  int    $object_id   The object ID post meta was requested for.
	 * @param  string $meta_key    The meta key requested.
	 * @param  bool   $single      If a single value or an array of the value is requested.
	 *
	 * @return mixed The value to return.
	 */
	public function get_post_metadata( $value, $object_id, $meta_key, $single ) {

		if ( 'get_post_metadata' !== current_filter() ) {
			$message = __( 'This function is not meant to be called directly. It is only here for backwards compatibility purposes.', 'easy-digital-downloads' );
			_doing_it_wrong( __FUNCTION__, esc_html( $message ), 'EDD 3.0' );
		}

		// Bail early of not a back-compat key
		if ( ! in_array( $meta_key, $this->get_meta_key_whitelist(), true ) ) {
			return $value;
		}

		// Bail if order does not exist
		$order = $this->_shim_edd_get_order( $object_id );
		if ( empty( $order ) ) {
			return $value;
		}

		switch ( $meta_key ) {
			case '_edd_payment_purchase_key':
				$value = $order->payment_key;
				break;
			case '_edd_payment_transaction_id':
				$value = $order->get_transaction_id();
				break;
			case '_edd_payment_user_email':
				$value = $order->email;
				break;
			case '_edd_payment_meta':
				$p = edd_get_payment( $object_id );
				$value = array( $p->get_meta( '_edd_payment_meta' ) );
				break;
			case '_edd_completed_date':
				$value = $order->date_completed;
				break;
			case '_edd_payment_gateway':
				$value = $order->gateway;
				break;
			case '_edd_payment_user_id':
				$value = $order->user_id;
				break;
			case '_edd_payment_user_ip':
				$value = $order->ip;
				break;
			case '_edd_payment_mode':
				$value = $order->mode;
				break;
			case '_edd_payment_tax_rate':
				$value = $order->get_tax_rate();
				/*
				 * Tax rates are now stored as percentages (e.g. `20.00`) but previously they were stored as
				 * decimals (e.g. `0.2`) so we convert it back to a decimal.
				 */
				if ( is_numeric( $value ) ) {
					$value = $value / 100;
				}
				break;
			case '_edd_payment_customer_id':
				$value = $order->customer_id;
				break;
			case '_edd_payment_total':
				$value = $order->total;
				break;
			case '_edd_payment_tax':
				$value = $order->tax;
				break;
			case '_edd_payment_number':
				$value = $order->get_number();
				break;
			default :
				$value = edd_get_order_meta( $order->id, $meta_key, true );
				break;
		}

		if ( $this->show_notices ) {
			_doing_it_wrong( 'get_post_meta()', 'All payment postmeta has been <strong>deprecated</strong> since Easy Digital Downloads 3.0! Use <code>edd_get_order()</code> instead.', 'EDD 3.0' );

			if ( $this->show_backtrace ) {
				$backtrace = debug_backtrace();
				trigger_error( print_r( $backtrace, 1 ) );
			}
		}

		return $value;
	}

	/**
	 * Backwards compatibility filters for add/update_post_meta() calls on payments.
	 *
	 * @since 3.0
	 *
	 * @param mixed  $check      Comes in 'null' but if returned not null, WordPress Core will not interact with the postmeta table.
	 * @param int    $object_id  The object ID post meta was requested for.
	 * @param string $meta_key   The meta key requested.
	 * @param mixed  $meta_value The value get_post_meta would return if we don't filter.
	 * @param mixed  $prev_value The previous value of the meta
	 *
	 * @return mixed Returns 'null' if no action should be taken and WordPress core can continue, or non-null to avoid postmeta.
	 */
	public function update_post_metadata( $check, $object_id, $meta_key, $meta_value, $prev_value ) {

		// Bail early of not a back-compat key
		if ( ! in_array( $meta_key, $this->get_meta_key_whitelist(), true ) ) {
			return $check;
		}

		// Bail if payment does not exist
		$payment = edd_get_payment( $object_id );
		if ( empty( $payment ) ) {
			return $check;
		}

		$check = $payment->update_meta( $meta_key, $meta_value );

		if ( $this->show_notices ) {
			_doing_it_wrong( 'add_post_meta()/update_post_meta()', 'All payment postmeta has been <strong>deprecated</strong> since Easy Digital Downloads 3.0! Use <code>edd_add_order_meta()/edd_update_order_meta()()</code> instead.', 'EDD 3.0' );

			if ( $this->show_backtrace ) {
				$backtrace = debug_backtrace();
				trigger_error( print_r( $backtrace, 1 ) );
			}
		}

		return $check;
	}

	/**
	 * Retrieves a list of whitelisted meta keys that we want to catch in get/update post meta calls.
	 *
	 * @since 3.0
	 * @return array
	 */
	private function get_meta_key_whitelist() {
		$meta_keys = array(
			'_edd_payment_purchase_key',
			'_edd_payment_transaction_id',
			'_edd_payment_meta',
			'_edd_completed_date',
			'_edd_payment_gateway',
			'_edd_payment_user_id',
			'_edd_payment_user_email',
			'_edd_payment_user_ip',
			'_edd_payment_mode',
			'_edd_payment_tax_rate',
			'_edd_payment_customer_id',
			'_edd_payment_total',
			'_edd_payment_tax',
			'_edd_payment_number',
			'_edd_sl_upgraded_payment_id', // EDD SL
			'_edd_sl_is_renewal', // EDD SL
			'_edds_stripe_customer_id', // EDD Stripe
		);

		/**
		 * Allows the whitelisted post meta keys to be filtered. Extensions should add their meta key(s) to this
		 * list if they want add/update/get post meta calls to be routed to order meta.
		 *
		 * @param array $meta_keys
		 *
		 * @since 3.0
		 */
		$meta_keys = apply_filters( 'edd_30_post_meta_key_whitelist', $meta_keys );

		return (array) $meta_keys;
	}

	/**
	 * Gets the order from the database.
	 * This is a duplicate of edd_get_order, but is defined separately here
	 * for pending migration purposes.
	 *
	 * @todo deprecate in 3.1
	 *
	 * @param int $order_id
	 * @return false|EDD\Orders\Order
	 */
	private function _shim_edd_get_order( $order_id ) {
		$orders = new \EDD\Database\Queries\Order();

		// Return order
		return $orders->get_item( $order_id );
	}
}
