<?php
/**
 * Contextual Help
 *
 * @package     EDD
 * @subpackage  Admin/Payments
 * @copyright   Copyright (c) 2013, Pippin Williamson
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
		'<p><strong>' . sprintf( __( 'For more information:', 'edd' ) . '</strong></p>' .
		'<p>' . sprintf( __( 'Visit the <a href="%s">documentation</a> on the Easy Digital Downloads website.', 'edd' ), esc_url( 'https://easydigitaldownloads.com/documentation/' ) ) ) . '</p>' .
		'<p>' . sprintf(
					__( '<a href="%s">Post an issue</a> on <a href="%s">GitHub</a>. View <a href="%s">extensions</a> or <a href="%s">themes</a>.', 'edd' ),
					esc_url( 'https://github.com/easydigitaldownloads/Easy-Digital-Downloads/issues' ),
					esc_url( 'https://github.com/easydigitaldownloads/Easy-Digital-Downloads' ),
					esc_url( 'https://easydigitaldownloads.com/extensions/' ),
					esc_url( 'https://easydigitaldownloads.com/themes/' )
				) . '</p>'
	);

	$screen->add_help_tab( array(
		'id'	    => 'edd-payments-overview',
		'title'	    => __( 'Overview', 'edd' ),
		'content'	=>
			'<p>' . __( "This screen provides access to all of your store's transactions.", 'edd' ) . '</p>' . 
			'<p>' . __( 'Payments can be searched by email address, user name, or filtered by status (completed, pending, etc.)', 'edd' ) . '</p>' .
			'<p>' . __( 'You also have the option to bulk delete payment should you wish.', 'edd' ) . '</p>'
	) );

	$screen->add_help_tab( array(
		'id'	    => 'edd-payments-search',
		'title'	    => __( 'Search Payments', 'edd' ),
		'content'	=>
			'<p>' . __( 'The payment history can be searched in several different ways:', 'edd' ) . '</p>' .
			'<ul>
				<li>' . __( 'You can enter the customer\'s email address', 'edd' ) . '</li>
				<li>' . __( 'You can enter the customer\'s name', 'edd' ) . '</li>
				<li>' . __( 'You can enter the 32-character purchase key', 'edd' ) . '</li>
			</ul>'
	) );

	$screen->add_help_tab( array(
		'id'	    => 'edd-payments-details',
		'title'	    => __( 'Order Details', 'edd' ),
		'content'	=>
			'<p>' . __( 'Each payment can be further inspected by clicking the corresponding <em>View Order Details</em> link. This will provide more information including:', 'edd' ) . '</p>' . 

			'<ul>
				<li><strong>Purchased File</strong> - ' . __( 'The file associated with the purchase.', 'edd' ) . '</li>
				<li><strong>Purchase Date</strong> - ' . __( 'The exact date and time the payment was completed.', 'edd' ) . '</li>
				<li><strong>Discount Used</strong> - ' . __( 'If a coupon or discount was used during the checkout process.', 'edd' ) . '</li>
				<li><strong>Name</strong> - ' . __( "The buyer's name.", 'edd' ) . '</li>
				<li><strong>Email</strong> - ' . __( "The buyer's email address.", 'edd' ) . '</li>
				<li><strong>Payment Notes</strong> - ' . __( 'Any customer-specific notes related to the payment.', 'edd' ) . '</li>
				<li><strong>Payment Method</strong> - ' . __( 'The name of the payment gateway used to complete the payment.', 'edd' ) . '</li>
				<li><strong>Purchase Key</strong> - ' . __( 'A unique key used to identify the payment.', 'edd' ) . '</li>
			</ul>'
	) );

	do_action( 'edd_payments_contextual_help', $screen );
}
add_action( 'load-download_page_edd-payment-history', 'edd_payments_contextual_help' );