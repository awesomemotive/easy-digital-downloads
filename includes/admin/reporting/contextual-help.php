<?php
/**
 * Contextual Help
 *
 * @package     EDD
 * @subpackage  Admin/Reports
 * @copyright   Copyright (c) 2015, Pippin Williamson
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
		'<p><strong>' . sprintf( __( 'For more information:', 'easy-digital-downloads' ) . '</strong></p>' .
		'<p>' . sprintf( __( 'Visit the <a href="%s">documentation</a> on the Easy Digital Downloads website.', 'easy-digital-downloads' ), esc_url( 'http://docs.easydigitaldownloads.com/' ) ) ) . '</p>' .
		'<p>' . sprintf(
					__( '<a href="%s">Post an issue</a> on <a href="%s">GitHub</a>. View <a href="%s">extensions</a> or <a href="%s">themes</a>.', 'easy-digital-downloads' ),
					esc_url( 'https://github.com/easydigitaldownloads/Easy-Digital-Downloads/issues' ),
					esc_url( 'https://github.com/easydigitaldownloads/Easy-Digital-Downloads' ),
					esc_url( 'https://easydigitaldownloads.com/downloads/?utm_source=plugin-reports-page&utm_medium=contextual-help-sidebar&utm_term=extensions&utm_campaign=ContextualHelp' ),
					esc_url( 'https://easydigitaldownloads.com/themes/?utm_source=plugin-reports-page&utm_medium=contextual-help-sidebar&utm_term=themes&utm_campaign=ContextualHelp' )
				) . '</p>'
	);

	$screen->add_help_tab( array(
		'id'	    => 'edd-reports',
		'title'	    => __( 'Reports', 'easy-digital-downloads' ),
		'content'	=> '<p>' . __( 'This screen provides you with reports for your earnings, downloads, customers and taxes.', 'easy-digital-downloads' ) . '</p>'
	) );

	$screen->add_help_tab( array(
		'id'	    => 'edd-reports-export',
		'title'	    => __( 'Export', 'easy-digital-downloads' ),
		'content'	=>
			'<p>' . __( 'This screen allows you to export your reports into a CSV format.', 'easy-digital-downloads' ) . '</p>' .
			'<p>' . __( '<strong>Sales and Earnings</strong> - This report exports all of the sales and earnings that you have made in the current year. It includes your sales and earnings for each product as well a graphs of sales and earnings so you can compare them for each month.', 'easy-digital-downloads' ) . '</p>' .
			'<p>' . __( '<strong>Payment History</strong> - This report exports all of the payments you have received on your EDD store in a CSV format.  It includes the contact details of the customer, the products they have purchased as well as any discount codes they have used and the final price they have paid.', 'easy-digital-downloads' ) . '</p>' .
			'<p>' . __( "<strong>Customers</strong> - This report exports all of your customers in a CSV format. It exports the customer's name and email address and the amount of products they have purchased as well as the final price of their total purchases.", 'easy-digital-downloads' ) . '</p>' .
			'<p>' . __( '<strong>Download History</strong> - This report exports all of the downloads you have received in the current month into a CSV. It exports the date the file was downloaded, the customer it was downloaded by, their IP address, the name of the product and the file they downloaded.', 'easy-digital-downloads' ) . '</p>'
	) );

	if( ! empty( $_GET['tab'] ) && 'logs' == $_GET['tab'] ) {
		$screen->add_help_tab( array(
			'id'	    => 'edd-reports-log-search',
			'title'	    => __( 'Search File Downloads', 'easy-digital-downloads' ),
			'content'	=>
				'<p>' . __( 'The file download log can be searched in several different ways:', 'easy-digital-downloads' ) . '</p>' .
				'<ul>
					<li>' . __( 'You can enter the customer\'s email address', 'easy-digital-downloads' ) . '</li>
					<li>' . __( 'You can enter the customer\'s IP address', 'easy-digital-downloads' ) . '</li>
					<li>' . __( 'You can enter the download file\'s name', 'easy-digital-downloads' ) . '</li>
				</ul>'
		) );
	}

	do_action( 'edd_reports_contextual_help', $screen );
}
add_action( 'load-download_page_edd-reports', 'edd_reporting_contextual_help' );
