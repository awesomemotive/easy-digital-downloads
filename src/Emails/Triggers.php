<?php
/**
 * Class for email triggers.
 *
 * @package EDD\Emails
 * @copyright   Copyright (c) 2023, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.2.0
 */

namespace EDD\Emails;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

use EDD\EventManagement\SubscriberInterface;
use EDD\Orders\Order;

/**
 * Class Triggers
 *
 * @since 3.2.0
 */
class Triggers implements SubscriberInterface {
	use Traits\Preview;
	use Traits\Orders;

	/**
	 * Get the events that this subscriber is subscribed to.
	 *
	 * @since 3.2.0
	 * @return array The subscribed events.
	 */
	public static function get_subscribed_events() {
		return array(
			'edd_after_order_actions'        => array( 'send_order_emails', 9999, 3 ), // Run this late, so that other plugins can modify the order before the email is sent.
			'edd_email_links'                => array( 'resend_order_receipt', 10, 1 ),
			'edd_send_test_email'            => array( 'send_test_email', 10, 1 ),
			'edd_preview_email'              => array( 'preview_email', 10 ),
			'edd_refund_order'               => array( 'send_refund_receipt', 10, 2 ),
			'edd_insert_user'                => array( 'send_new_user_email', 10, 2 ),
			'edd_stripe_early_fraud_warning' => array( 'send_stripe_early_fraud_warning', 10, 1 ),
		);
	}

	/**
	 * Resend the order receipt.
	 *
	 * @since 3.2.0
	 *
	 * @param array $data The $_POST data.
	 *
	 * @return void
	 */
	public function resend_order_receipt( $data ) {
		$order_id = absint( $data['purchase_id'] );

		if ( empty( $order_id ) ) {
			return;
		}

		if ( ! current_user_can( 'edit_shop_payments' ) ) {
			wp_die( __( 'You do not have permission to edit this payment record', 'easy-digital-downloads' ), __( 'Error', 'easy-digital-downloads' ), array( 'response' => 403 ) );
		}

		$email = ! empty( $data['email'] ) ? sanitize_email( $data['email'] ) : '';
		$order = edd_get_order( $order_id );

		if ( empty( $email ) ) {
			$customer = edd_get_customer( $order->customer_id );
			$email    = $customer->email;
		}

		$order_receipt          = Registry::get( 'order_receipt', array( $order ) );
		$order_receipt->send_to = $email;
		$sent                   = $order_receipt->send();

		// Allow filtering this as extensions like Per Product Emails may disable sending the main receipt.
		$sent = apply_filters( 'edd_resend_order_receipt_was_sent', $sent, $order, $email );

		// If the email was sent, reset the file download limits.
		if ( false !== $sent ) {
			if ( is_array( $order->order_items ) ) {
				foreach ( $order->order_items as $order_item ) {
					$limit = edd_get_file_download_limit( $order_item->product_id );
					if ( ! empty( $limit ) ) {
						edd_set_file_download_limit_override( $order_item->product_id, $order_id );
					}
				}
			}
		}

		switch ( $sent ) {
			case true:
				$edd_message = 'email_sent';
				break;
			case false:
				$edd_message = 'email_send_failed';
				break;
			case null:
				$edd_message = 'email_possibly_not_sent';
				break;
		}

		edd_redirect(
			add_query_arg(
				array(
					'edd-message' => $edd_message,
					'edd-action'  => false,
					'purchase_id' => false,
					'email'       => false,
				)
			)
		);
	}

	/**
	 * Send the refund receipt.
	 *
	 * @since 3.3.0
	 * @param int $order_id  The order ID.
	 * @param int $refund_id The refund ID.
	 *
	 * @return void
	 */
	public function send_refund_receipt( $order_id, $refund_id ) {
		if ( ! $refund_id ) {
			return;
		}
		$refund = edd_get_order( $refund_id );
		if ( $refund && 'refund' === $refund->type ) {
			$refund_notice = Registry::get( 'order_refund', array( $refund, $order_id ) );
			$refund_notice->send();

			$admin_notice = Registry::get( 'admin_order_refund', array( $refund, $order_id ) );
			$admin_notice->send();
		}
	}

	/**
	 * Send the new user email.
	 *
	 * @since 3.3.0
	 * @param int   $user_id   The user ID.
	 * @param array $user_data The user data.
	 *
	 * @return void
	 */
	public function send_new_user_email( $user_id, $user_data ) {
		if ( empty( $user_id ) || empty( $user_data ) ) {
			return;
		}

		$user_email = Registry::get( 'new_user', array( $user_id, $user_data ) );
		$user_email->send();
		$admin_email = Registry::get( 'new_user_admin', array( $user_id, $user_data ) );
		$admin_email->send();
	}

	/**
	 * Register the emails.
	 *
	 * @since 3.2.0
	 * @deprecated 3.3.0
	 * @return void
	 */
	public function register_emails() {}

	/**
	 * Send the Stripe Early Fraud Warning email.
	 *
	 * @since 3.3.0
	 * @param EDD\Orders\Order $order The order object.
	 *
	 * @return void
	 */
	public function send_stripe_early_fraud_warning( $order ) {
		$early_fraud_warning = Registry::get( 'stripe_early_fraud_warning', array( $order ) );
		$early_fraud_warning->send();
	}
}
