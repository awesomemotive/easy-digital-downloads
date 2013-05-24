<?php
/**
 * Contextual Help
 *
 * @package     EDD
 * @subpackage  Admin/Reports
 * @copyright   Copyright (c) 2013, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.4
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Reports contextual help.
 *
 * @access      private
 * @since       1.4
 * @return      void
 */
function edd_reporting_contextual_help() {
	$screen = get_current_screen();

	if ( $screen->id != 'download_page_edd-reports' )
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
		'id'	    => 'edd-reports',
		'title'	    => __( 'Reports', 'edd' ),
		'content'	=> '<p>' . __( 'This screen provides you with reports for your earnings, downloads, customers and taxes.', 'edd' ) . '</p>'
	) );

	$screen->add_help_tab( array(
		'id'	    => 'edd-reports-export',
		'title'	    => __( 'Export', 'edd' ),
		'content'	=>
			'<p>' . __( 'This screen allows you to export your reports into a PDF or CSV format.', 'edd' ) . '</p>' . 
			'<p>' . __( '<strong>Sales and Earnings</strong> - This report exports all of the sales and earnings that you have made in the current year. This report includes your sales and earnings for each product as well a graphs of sales and earnings so you can compare them for each month.', 'edd' ) . '</p>' .
			'<p>' . __( '<strong>Payment History</strong> - This report exports all of payments you have received on your EDD store in a CSV format.  The report includes the contact details of the customer, the products they have purchased as well as any discount codes they have used and the final price they have paid.', 'edd' ) . '</p>' .
			'<p>' . __( "<strong>Customers</strong> - This report exports all of your customers in a CSV format. It exports the customer's name and email address and the amount of products they have purchased as well as the final price of their total purchases.", 'edd' ) . '</p>' .
			'<p>' . __( '<strong>Download History</strong> - This report exports all of the downloads you have received in the current month into a CSV. It exports the date the file was downloaded, the customer it was downloaded by, their IP address, the name of the product and the file they downloaded.', 'edd' ) . '</p>'
	) );

	if( ! empty( $_GET['tab'] ) && 'logs' == $_GET['tab'] ) {
		$screen->add_help_tab( array(
			'id'	    => 'edd-reports-log-search',
			'title'	    => __( 'Search File Downloads', 'edd' ),
			'content'	=>
				'<p>' . __( 'The file download log can be searched in several different ways:', 'edd' ) . '</p>' .
				'<ul>
					<li>' . __( 'You can enter the customer\'s email address', 'edd' ) . '</li>
					<li>' . __( 'You can enter the customer\'s IP address', 'edd' ) . '</li>
					<li>' . __( 'You can enter the download file\'s name', 'edd' ) . '</li>
				</ul>'
		) );
	}

	do_action( 'edd_reports_contextual_help', $screen );
}
add_action( 'load-download_page_edd-reports', 'edd_reporting_contextual_help' );