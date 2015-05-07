<?php
/**
 * Exports Functions
 *
 * These are functions are used for exporting data from Easy Digital Downloads.
 *
 * @package     EDD
 * @subpackage  Admin/Export
 * @copyright   Copyright (c) 2015, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

require_once EDD_PLUGIN_DIR . 'includes/admin/reporting/class-export.php';

function edd_do_ajax_export() {

	if( ! current_user_can( 'export_shop_reports' ) ) {
		die( '-1' );
	}

	require_once EDD_PLUGIN_DIR . 'includes/admin/reporting/class-batch-export.php';
	require_once EDD_PLUGIN_DIR . 'includes/admin/reporting/class-batch-export-payments.php';

	parse_str( $_POST['form'], $form );

	$_REQUEST = $form = (array) $form;
	$step     = absint( $_POST['step'] );
	$type     = $form['edd-action'];
	$class    = $form['edd-export-class'];
	$export   = new $class( $step );

	$export->month  = isset( $_REQUEST['month'] )   ? absint( $_REQUEST['month'] )                : date( 'n' );
	$export->year   = isset( $_REQUEST['year']  )   ? absint( $_REQUEST['year']  )                : date( 'Y' );
	$export->status = isset( $_REQUEST['status']  ) ? sanitize_text_field( $_REQUEST['status']  ) : 'complete';

	$ret = $export->process_step( $step );

	if( $ret ) {
		$step += 1;
		echo json_encode( array( 'step' => $step ) ); exit;
	} else {
		$download_url = add_query_arg( array( 'step' => $step, 'edd_action' => $type ), admin_url() );
		echo json_encode( array( 'step' => 'done', 'url' => $download_url ) ); exit;
	}
}
add_action( 'wp_ajax_edd_do_ajax_export', 'edd_do_ajax_export' );

/**
 * Exports earnings for a specified time period
 * EDD_Earnings_Export class.
 *
 * @since 2.0
 * @return void
 */
function edd_export_earnings() {
	require_once EDD_PLUGIN_DIR . 'includes/admin/reporting/class-export-earnings.php';

	$earnings_export = new EDD_Earnings_Export();

	$earnings_export->export();
}
add_action( 'edd_earnings_export', 'edd_export_earnings' );

/**
 * Exports all the payments stored in Payment History to a CSV file using the
 * EDD_Export class.
 *
 * @since 1.4.4
 * @return void
 */
function edd_export_payment_history() {

	require_once EDD_PLUGIN_DIR . 'includes/admin/export/class-export-payments.php';

	$step   = isset( $_REQUEST['step'] ) ? absint( $_REQUEST['step'] ) : 1;
	$export = new EDD_Payments_Export( $step );

	$export->month  = isset( $_REQUEST['month'] )   ? absint( $_REQUEST['month'] )                : date( 'n' );
	$export->year   = isset( $_REQUEST['year']  )   ? absint( $_REQUEST['year']  )                : date( 'Y' );
	$export->status = isset( $_REQUEST['status']  ) ? sanitize_text_field( $_REQUEST['status']  ) : 'complete';

	if( $export->ready() ) {
		$export->export();
	} else {	
		$export->process_step( $step );
	}

}
add_action( 'edd_payment_export', 'edd_export_payment_history' );

/**
 * Export all the customers to a CSV file.
 *
 * Note: The WordPress Database API is being used directly for performance
 * reasons (workaround of calling all posts and fetch data respectively)
 *
 * @since 1.4.4
 * @return void
 */
function edd_export_all_customers() {
	require_once EDD_PLUGIN_DIR . 'includes/admin/reporting/class-export-customers.php';

	$customer_export = new EDD_Customers_Export();

	$customer_export->export();
}
add_action( 'edd_email_export', 'edd_export_all_customers' );

/**
 * Exports all the downloads to a CSV file using the EDD_Export class.
 *
 * @since 1.4.4
 * @return void
 */
function edd_export_all_downloads_history() {
	require_once EDD_PLUGIN_DIR . 'includes/admin/reporting/class-export-download-history.php';

	$file_download_export = new EDD_Download_History_Export();

	$file_download_export->export();
}
add_action( 'edd_downloads_history_export', 'edd_export_all_downloads_history' );