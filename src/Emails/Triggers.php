<?php
/**
 * Class for email triggers.
 *
 * @since 3.2.0
 * @package EDD
 * @subpackage Emails
 */

namespace EDD\Emails;

use EDD\EventManagement\SubscriberInterface;
use EDD\Emails\Registry;
use EDD\Orders\Order;

class Triggers implements SubscriberInterface {

	/**
	 * Get the events that this subscriber is subscribed to.
	 *
	 * @since 3.2.0
	 * @return array The subscribed events.
	 */
	public static function get_subscribed_events() {
		return array(
			'init'                              => array( 'register_emails', 3 ),
			'edd_after_order_actions'           => array( 'send_order_emails', 9999, 3 ), // Run this late, so that other plugins can modify the order before the email is sent.
			'edd_email_links'                   => array( 'resend_order_receipt', 10, 1 ),
			'edd_send_test_email'               => array( 'send_test_email', 10, 1 ),
			'edd_preview_email'                 => array( 'preview_email', 10 ),
			'edd_send_test_email_order_receipt' => array( 'send_test_order_receipt', 10, 1 ),
		);
	}

	public function register_emails() {
		Registry::register( 'order_receipt', 'EDD\Emails\Types\OrderReceipt' );
		Registry::register( 'admin_order_notice', 'EDD\Emails\Types\AdminOrderNotice' );
	}

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

		/**
		 * Currently we only send for orders, not refunds, but that is a requested feature:
		 *
		 * @see https://github.com/awesomemotive/easy-digital-downloads-pro/issues/546
		 */
		if ( 'refund' === $order->type ) {
			return;
		}

		// To know if people unhooked the legacy filter on edd_purchase_complete, check the order meta.
		$should_send_order_receipt = edd_get_order_meta( $order_id, '_edd_should_send_order_receipt', true );

		edd_debug_log( 'order_receipt should send: ' . var_export( $should_send_order_receipt, true ) );

		// Delete the meta so we don't keep it around.
		edd_delete_order_meta( $order_id, '_edd_should_send_order_receipt' );

		if ( $should_send_order_receipt ) {
			// Send the email.
			$order_receipt = Registry::get( 'order_receipt', array( $order ) );
			$order_receipt->send();
		} else {
			_edd_deprecated_function( 'edd_trigger_purchase_receipt', '3.2.0', 'EDD\Emails\Types\OrderReceipt' );
		}

		// To know if people unhooked the legacy filter on edd_purchase_complete, check the order meta.
		$should_send_admin_order_notice = edd_get_order_meta( $order_id, '_edd_should_send_admin_order_notice', true );

		edd_debug_log( 'admin_order_notice should send: ' . var_export( $should_send_admin_order_notice, true ) );

		// Delete the meta so we don't keep it around.
		edd_delete_order_meta( $order_id, '_edd_should_send_admin_order_notice' );

		if ( $should_send_admin_order_notice ) {
			// Send the email.
			$admin_notice = Registry::get( 'admin_order_notice', array( $order ) );
			$admin_notice->send();
		} else {
			_edd_deprecated_function( 'edd_admin_email_notice', '3.2.0', 'EDD\Emails\Types\AdminOrderNotice' );
		}
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
			$customer = new \EDD_Customer( $order->customer_id );
			$email    = $customer->email;
		}

		// To know if people unhooked the legacy filter on edd_purchase_complete, check the order meta.
		$should_send_order_receipt = edd_get_order_meta( $order_id, '_edd_should_send_order_receipt', true );

		edd_debug_log( 'order_receipt should send: ' . var_export( $should_send_order_receipt, true ) );

		// Delete the meta so we don't keep it around.
		edd_delete_order_meta( $order_id, '_edd_should_send_order_receipt' );

		$sent = false;

		if ( $should_send_order_receipt ) {
			$order_receipt          = Registry::get( 'order_receipt', array( $order ) );
			$order_receipt->send_to = $email;
			$sent                   = $order_receipt->send();
		}

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
	 * Determine the test email to send and call the action based on the id.
	 *
	 * @since 3.2.0
	 * @param array $data The $_POST data.
	 *
	 * @return void
	 */
	public function send_test_email( $data ) {
		// Get the email object.
		$id = ! empty( $data['email'] ) ? sanitize_text_field( $data['email'] ) : 'order_receipt';
		do_action( 'edd_send_test_email_' . $id, $data );
	}

	/**
	 * Send the test purchase confirmation email.
	 *
	 * This does the work of verifying the nonce and checking the capabilities.
	 *
	 * @since 3.2.0
	 * @param array $data The $_POST data.
	 */
	public function send_test_order_receipt( $data ) {
		// Only people who can amnage the shop settings should be able to send these.
		if ( ! current_user_can( 'manage_shop_settings' ) ) {
			return;
		}

		// Verify the nonce.
		if ( ! wp_verify_nonce( $data['_wpnonce'], 'edd-test-email' ) ) {
			return;
		}

		$order_receipt = Registry::get( $data['email'], array( false ) );

		// Set this as a preview email.
		$order_receipt->is_test = true;

		// For the test email we'll send it to all the admin notice emails.
		$order_receipt->send_to = edd_get_admin_notice_emails();

		// Send the receipt.
		$sent = $order_receipt->send();

		$edd_message = $sent ? 'test-purchase-email-sent' : 'test-purchase-email-failed';
		// Redirect the user back to the email settings.
		$url = edd_get_admin_url(
			array(
				'page'        => 'edd-settings',
				'tab'         => 'emails',
				'section'     => 'purchase_receipts',
				'edd-message' => $edd_message,
			)
		);

		edd_redirect( $url );
	}

	/**
	 * Preview the purchase confirmation emails.
	 *
	 * This previously ran on `template_redirect` but now runs on it's own EDD Action, to avoid always running it.
	 *
	 * @since 3.2.0
	 * @return void
	 */
	public function preview_email() {
		if( ! current_user_can( 'manage_shop_settings' ) ) {
			return;
		}

		$order_receipt_preview = Registry::get( 'order_receipt', array( false ) );
		$order_receipt_preview->is_preview = true;

		echo $order_receipt_preview->get_preview();

		exit;
	}
}
