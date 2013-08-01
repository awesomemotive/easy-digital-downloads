<?php
/**
 * Exports Functions
 *
 * These are functions are used for exporting data from Easy Digital Downloads.
 *
 * @package     EDD
 * @subpackage  Admin/Export
 * @copyright   Copyright (c) 2013, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

require_once EDD_PLUGIN_DIR . 'includes/admin/reporting/class-export.php';

/**
 * Exports all the payments stored in Payment History to a CSV file using the
 * EDD_Export class.
 *
 * @since 1.4.4
 * @return void
 */
function edd_export_payment_history() {
	require_once EDD_PLUGIN_DIR . 'includes/admin/reporting/class-export-payments.php';

	$payments_export = new EDD_Payments_Export();

	$payments_export->export();
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