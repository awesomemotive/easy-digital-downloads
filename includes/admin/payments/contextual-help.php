<?php
/**
 * Contextual Help
 *
 * @package     EDD
 * @subpackage  Admin/Payments
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.4
 */

use EDD\Admin\Pass_Manager;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Payments contextual help.
 *
 * @access      private
 * @since       1.4
 * @return      void
 */
function edd_payments_contextual_help() {
	$screen = get_current_screen();

	// Only show on main "Orders" screen.
	if ( 'download_page_edd-payment-history' !== $screen->id ) {
		return;
	}

	// Do not show on Add or View Order/Refund.
	if ( isset( $_GET['view'] ) ) {
		return;
	}

	$pass_manager = new Pass_Manager();
	if ( $pass_manager->isFree() ) {
		$docs_url = edd_link_helper(
			'https://easydigitaldownloads.com/docs/',
			array(
				'utm_medium'  => 'orders-contextual-help',
				'utm_content' => 'documentation',
			)
		);

		$upgrade_url = edd_link_helper(
			'https://easydigitaldownloads.com/lite-upgrade/',
			array(
				'utm_medium'  => 'orders-contextual-help',
				'utm_content' => 'lite-upgrade',
			)
		);

		$screen->set_help_sidebar(
			'<p><strong>' . __( 'For more information:', 'easy-digital-downloads' ) . '</strong></p>' .
			'<p>' . sprintf( __( 'Visit the <a href="%s">documentation</a> on the Easy Digital Downloads website.', 'easy-digital-downloads' ), $docs_url ) . '</p>' .
			'<p>' . sprintf(
				__( 'Need more from your Easy Digital Downloads store? <a href="%s">Upgrade Now</a>!', 'easy-digital-downloads' ),
				$upgrade_url
			) . '</p>'
		);
	}

	$screen->add_help_tab( array(
		'id'	    => 'edd-payments-overview',
		'title'	    => __( 'Overview', 'easy-digital-downloads' ),
		'content'	=>
			'<p>' . __( 'This screen provides access to all of the orders and refunds in your store.', 'easy-digital-downloads' ) . '</p>' .
			'<p>' . __( 'Orders can be searched by email address, user name, or filtered by status, mode, date range, gateway, and more!', 'easy-digital-downloads' ) . '</p>' .
			'<p>' . __( 'To maintain accurate reporting and accounting, we strongly advise against deleting any completed order data.', 'easy-digital-downloads' ) . '</p>'
	) );

	$screen->add_help_tab( array(
		'id'	    => 'edd-orders',
		'title'	    => __( '&mdash; Orders', 'easy-digital-downloads' ),
		'content'	=>
			'<p>' . __( 'Orders are placed by customers when they buy things from your store.', 'easy-digital-downloads' ) . '</p>' .
			'<p>' . __( 'Every order contains a snapshot of your store at the time the order was placed, and is made up of many different pieces of information.', 'easy-digital-downloads' ) . '</p>' .
			'<p>' . __( 'Things like products, discounts, taxes, fees, and customer email address, are all examples of information that is saved with each order.', 'easy-digital-downloads' ) . '</p>' .
			'<p>' . __( 'Both full and partial refunds are supported.', 'easy-digital-downloads' ) . '</p>'
	) );

	$screen->add_help_tab( array(
		'id'	    => 'edd-refunds',
		'title'	    => __( '&mdash; Refunds', 'easy-digital-downloads' ),
		'content'	=>
			'<p>' . __( 'Refunds are created when a customer would like money back from a completed order.', 'easy-digital-downloads' ) . '</p>' .
			'<p>' . __( 'Every refund refers back to the original order, and only contains the items and adjustments that were refunded.', 'easy-digital-downloads' ) . '</p>' .
			'<p>' . __( 'Refunds could be entire orders, or single products.', 'easy-digital-downloads' ) . '</p>' .
			'<p>' . __( 'Once an item is refunded, it cannot be undone; it can only be repurchased.', 'easy-digital-downloads' ) . '</p>'
	) );

	$screen->add_help_tab( array(
		'id'      => 'edd-payments-search',
		'title'   => __( 'Search', 'easy-digital-downloads' ),
		'content' =>
			'<p>' . __( 'The order history can be searched in several different ways.', 'easy-digital-downloads' ) . '</p>' .
			'<p>' . __( 'You can enter:', 'easy-digital-downloads' ) . '</p>' .
			'<ul>
				<li>' . __( 'The specific order ID', 'easy-digital-downloads' ) . '</li>
				<li>' . __( 'The 32-character order key', 'easy-digital-downloads' ) . '</li>
				<li>' . __( 'The customer\'s email address', 'easy-digital-downloads' ) . '</li>
				<li>' . sprintf(
					__( 'The customer\'s name or ID prefixed by %s', 'easy-digital-downloads' ),
					'<code>customer:</code>'
				) . '</li>
				<li>' . sprintf(
					__( 'A user\'s ID prefixed by %s', 'easy-digital-downloads' ),
					'<code>user:</code>'
				) . '</li>
				<li>' . sprintf(
					__( 'The %s ID prefixed by %s', 'easy-digital-downloads' ),
					edd_get_label_singular(),
					'<code>#</code>'
				) . '</li>
				<li>' . sprintf(
					__( 'The Discount Code prefixed by %s', 'easy-digital-downloads' ),
					'<code>discount:</code>'
				) . '</li>
				<li>' . sprintf(
					__( 'A transaction ID prefixed by %s', 'easy-digital-downloads' ),
					'<code>txn:</code>'
				) . '</li>
			</ul>',
	) );

	$screen->add_help_tab( array(
		'id'	    => 'edd-payments-details',
		'title'	    => __( 'Details', 'easy-digital-downloads' ),
		'content'	=>
			'<p>' . __( 'Each order can be further inspected by clicking the corresponding <em>View Order Details</em> link. This will provide more information including:', 'easy-digital-downloads' ) . '</p>' .

			'<ul>
				<li><strong>Purchased File</strong> - ' . __( 'The file associated with the purchase.', 'easy-digital-downloads' ) . '</li>
				<li><strong>Purchase Date</strong> - ' . __( 'The exact date and time the order was completed.', 'easy-digital-downloads' ) . '</li>
				<li><strong>Discount Used</strong> - ' . __( 'If a coupon or discount was used during the checkout process.', 'easy-digital-downloads' ) . '</li>
				<li><strong>Name</strong> - ' . __( "The buyer's name.", 'easy-digital-downloads' ) . '</li>
				<li><strong>Email</strong> - ' . __( "The buyer's email address.", 'easy-digital-downloads' ) . '</li>
				<li><strong>Payment Notes</strong> - ' . __( 'Any customer-specific notes related to the order.', 'easy-digital-downloads' ) . '</li>
				<li><strong>Payment Method</strong> - ' . __( 'The name of the order gateway used to complete the order.', 'easy-digital-downloads' ) . '</li>
				<li><strong>Purchase Key</strong> - ' . __( 'A unique key used to identify the order.', 'easy-digital-downloads' ) . '</li>
			</ul>'
	) );

	do_action( 'edd_payments_contextual_help', $screen );
}
add_action( 'load-download_page_edd-payment-history', 'edd_payments_contextual_help' );
