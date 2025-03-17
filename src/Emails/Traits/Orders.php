<?php
/**
 * Trait for order-related email triggers.
 *
 * @package     EDD\Emails\Traits
 * @copyright   Copyright (c) 2025, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.3.7
 */

namespace EDD\Emails\Traits;

use EDD\Orders\Order;
use EDD\Emails\Registry;

/**
 * Trait Orders
 *
 * @package EDD\Emails\Traits
 * @since   3.3.7
 */
trait Orders {

	/**
	 * Send the order receipt and admin order notice.
	 *
	 * @since 3.2.0
	 *
	 * @param int           $order_id The order ID.
	 * @param Order         $order    The Order object.
	 * @param \EDD_Customer $customer The customer object.
	 *
	 * @return void
	 */
	public function send_order_emails( $order_id = 0, $order = null, $customer = null ) {
		// No order ID and no Order object, we can't move forward, just return.
		if ( empty( $order_id ) && ! $order instanceof Order ) {
			return;
		}

		// Order ID present, but no Order object, let's get the Order object.
		if ( ! empty( $order_id ) && ! $order instanceof Order ) {
			$order = edd_get_order( $order_id );
		}

		// No valid Order object, we can't move forward, just return.
		if ( false === $order ) {
			return;
		}

		if ( 'refund' === $order->type || 'complete' !== $order->status ) {
			return;
		}

		$this->send_receipt( $order );
		$this->send_admin_notice( $order );
	}

	/**
	 * Send the order receipt.
	 *
	 * @param Order $order The order object.
	 * @return void
	 */
	private function send_receipt( Order $order ) {
		if ( ! metadata_exists( 'edd_order', $order->id, '_edd_should_send_order_receipt' ) ) {
			_edd_deprecated_function( 'edd_trigger_purchase_receipt', '3.2.0', 'EDD\Emails\Types\OrderReceipt' );

			return;
		}

		$should_send_order_receipt = edd_get_order_meta( $order->id, '_edd_should_send_order_receipt', true );

		edd_debug_log( 'order_receipt should send: ' . var_export( $should_send_order_receipt, true ) );

		if ( $should_send_order_receipt ) {
			// Send the email.
			$order_receipt = Registry::get( 'order_receipt', array( $order ) );
			$order_receipt->send();
		}

		// Delete the meta so we don't keep it around.
		edd_delete_order_meta( $order->id, '_edd_should_send_order_receipt' );
	}

	/**
	 * Send the admin order notice.
	 *
	 * @param Order $order The order object.
	 * @return void
	 */
	private function send_admin_notice( Order $order ) {
		if ( ! metadata_exists( 'edd_order', $order->id, '_edd_should_send_admin_order_notice' ) ) {
			_edd_deprecated_function( 'edd_admin_email_notice', '3.2.0', 'EDD\Emails\Types\AdminOrderNotice' );

			return;
		}

		// To know if people unhooked the legacy filter on edd_purchase_complete, check the order meta.
		$should_send_admin_order_notice = edd_get_order_meta( $order->id, '_edd_should_send_admin_order_notice', true );

		edd_debug_log( 'admin_order_notice should send: ' . var_export( $should_send_admin_order_notice, true ) );

		if ( $should_send_admin_order_notice ) {
			// Send the email.
			$admin_notice = Registry::get( 'admin_order_notice', array( $order ) );
			$admin_notice->send();
		}

		// Delete the meta so we don't keep it around.
		edd_delete_order_meta( $order->id, '_edd_should_send_admin_order_notice' );
	}
}
