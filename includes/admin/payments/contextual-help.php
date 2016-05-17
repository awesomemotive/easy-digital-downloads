<?php
/**
 * Contextual Help
 *
 * @package     EDD
 * @subpackage  Admin/Payments
 * @copyright   Copyright (c) 2015, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.4
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Payments contextual help.
 *
 * @access      private
 * @since       1.4
 * @return      void
 */
function edd_payments_contextual_help() {
	$screen = get_current_screen();

	if ( $screen->id != 'download_page_edd-payment-history' )
		return;

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
			'<p>' . __( "This screen provides access to all of your store's transactions.", 'easy-digital-downloads' ) . '</p>' .
			'<p>' . __( 'Payments can be searched by email address, user name, or filtered by status (completed, pending, etc.)', 'easy-digital-downloads' ) . '</p>' .
			'<p>' . __( 'You also have the option to bulk delete payments should you wish.', 'easy-digital-downloads' ) . '</p>'
	) );

	$screen->add_help_tab( array(
		'id'	    => 'edd-payments-search',
		'title'	    => __( 'Search Payments', 'easy-digital-downloads' ),
		'content'	=>
			'<p>' . __( 'The payment history can be searched in several different ways:', 'easy-digital-downloads' ) . '</p>' .
			'<ul>
				<li>' . __( 'You can enter the customer\'s email address', 'easy-digital-downloads' ) . '</li>
				<li>' . __( 'You can enter the customer\'s name or ID prefixed by \'user:\'', 'easy-digital-downloads' ) . '</li>
				<li>' . __( 'You can enter the 32-character purchase key', 'easy-digital-downloads' ) . '</li>
				<li>' . __( 'You can enter the purchase ID', 'easy-digital-downloads' ) . '</li>
				<li>' . __( 'You can enter a transaction ID prefixed by \'txn:\'', 'easy-digital-downloads' ) . '</li>
				<li>' . sprintf( __( 'You can enter the %s ID prefixed by \'#\'', 'easy-digital-downloads' ), edd_get_label_singular() ) . '</li>
				<li>' . __( 'You can enter the Discount Code prefixed by \'discount:\'', 'easy-digital-downloads' ) . '</li>
			</ul>'
	) );

	$screen->add_help_tab( array(
		'id'	    => 'edd-payments-details',
		'title'	    => __( 'Payment Details', 'easy-digital-downloads' ),
		'content'	=>
			'<p>' . __( 'Each payment can be further inspected by clicking the corresponding <em>View Order Details</em> link. This will provide more information including:', 'easy-digital-downloads' ) . '</p>' .

			'<ul>
				<li><strong>Purchased File</strong> - ' . __( 'The file associated with the purchase.', 'easy-digital-downloads' ) . '</li>
				<li><strong>Purchase Date</strong> - ' . __( 'The exact date and time the payment was completed.', 'easy-digital-downloads' ) . '</li>
				<li><strong>Discount Used</strong> - ' . __( 'If a coupon or discount was used during the checkout process.', 'easy-digital-downloads' ) . '</li>
				<li><strong>Name</strong> - ' . __( "The buyer's name.", 'easy-digital-downloads' ) . '</li>
				<li><strong>Email</strong> - ' . __( "The buyer's email address.", 'easy-digital-downloads' ) . '</li>
				<li><strong>Payment Notes</strong> - ' . __( 'Any customer-specific notes related to the payment.', 'easy-digital-downloads' ) . '</li>
				<li><strong>Payment Method</strong> - ' . __( 'The name of the payment gateway used to complete the payment.', 'easy-digital-downloads' ) . '</li>
				<li><strong>Purchase Key</strong> - ' . __( 'A unique key used to identify the payment.', 'easy-digital-downloads' ) . '</li>
			</ul>'
	) );

	do_action( 'edd_payments_contextual_help', $screen );
}
add_action( 'load-download_page_edd-payment-history', 'edd_payments_contextual_help' );
