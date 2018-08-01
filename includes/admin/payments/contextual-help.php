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

	if ( 'download_page_edd-payment-history' !== $screen->id ) {
		return;
	}

	$screen->set_help_sidebar(
		'<p><strong>' . sprintf( __( 'For more information:', 'easy-digital-downloads' ) . '</strong></p>' .
		'<p>' . sprintf( __( 'Visit the <a href="%s">documentation</a> on the Easy Digital Downloads website.', 'easy-digital-downloads' ), esc_url( 'http://docs.easydigitaldownloads.com/' ) ) ) . '</p>' .
		'<p>' . sprintf(
					__( '<a href="%s">Post an issue</a> on <a href="%s">GitHub</a>. View <a href="%s">extensions</a> or <a href="%s">themes</a>.', 'easy-digital-downloads' ),
					esc_url( 'https://github.com/easydigitaldownloads/Easy-Digital-Downloads/issues' ),
					esc_url( 'https://github.com/easydigitaldownloads/Easy-Digital-Downloads' ),
					esc_url( 'https://easydigitaldownloads.com/downloads/?utm_source=plugin-payments-page&utm_medium=contextual-help-sidebar&utm_term=extensions&utm_campaign=ContextualHelp' ),
					esc_url( 'https://easydigitaldownloads.com/themes/?utm_source=plugin-payments-page&utm_medium=contextual-help-sidebar&utm_term=themes&utm_campaign=ContextualHelp' )
				) . '</p>'
	);

	$screen->add_help_tab( array(
		'id'	    => 'edd-payments-overview',
		'title'	    => __( 'Overview', 'easy-digital-downloads' ),
		'content'	=>
			'<p>' . __( 'This screen provides access to all of the orders, refunds, and invoices in your store.', 'easy-digital-downloads' ) . '</p>' .
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
			'<p>' . __( 'Orders can be refunded entirely, or individual items can be refunded by editing an existing order.', 'easy-digital-downloads' ) . '</p>'
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
		'id'	    => 'edd-invoice',
		'title'	    => __( '&mdash; Invoices', 'easy-digital-downloads' ),
		'content'	=>
			'<p>' . __( 'Invoices are created by store admins as a way to request that a customer pay you for something.', 'easy-digital-downloads' ) . '</p>' .
			'<p>' . __( 'Every invoice contains a snapshot of your store at the time the order was placed, and is made up of many different pieces of information.', 'easy-digital-downloads' ) . '</p>' .
			'<p>' . __( 'Things like products, discounts, taxes, fees, and customer email address, are all examples of information that is saved with each invoice.', 'easy-digital-downloads' ) . '</p>' .
			'<p>' . __( 'Invoices can be refunded entirely, or individual items can be refunded by editing an existing invoice.', 'easy-digital-downloads' ) . '</p>'
	) );

	$screen->add_help_tab( array(
		'id'	    => 'edd-payments-search',
		'title'	    => __( 'Search', 'easy-digital-downloads' ),
		'content'	=>
			'<p>' . __( 'The order history can be searched in several different ways.', 'easy-digital-downloads' ) . '</p>' .
			'<p>' . __( 'You can enter:', 'easy-digital-downloads' ) . '</p>' .
			'<ul>
				<li>' . __( 'The purchase ID', 'easy-digital-downloads' ) . '</li>
				<li>' . __( 'The 32-character purchase key', 'easy-digital-downloads' ) . '</li>
				<li>' . __( 'The customer\'s email address', 'easy-digital-downloads' ) . '</li>
				<li>' . __( 'The customer\'s name or ID prefixed by <code>user:</code>', 'easy-digital-downloads' ) . '</li>
				<li>' . sprintf( __( 'The %s ID prefixed by <code>#</code>', 'easy-digital-downloads' ), edd_get_label_singular() ) . '</li>
				<li>' . __( 'The Discount Code prefixed by <code>discount:</code>', 'easy-digital-downloads' ) . '</li>
				<li>' . __( 'A transaction ID prefixed by <code>txn:</code>', 'easy-digital-downloads' ) . '</li>
			</ul>'
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
