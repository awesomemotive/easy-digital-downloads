<?php
/**
 * Exports Functions
 *
 * These are functions are used for exporting data from Easy Digital Downloads.
 *
 * @package     Easy Digital Downloads
 * @subpackage  Export Functions
 * @copyright   Copyright (c) 2013, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

require_once EDD_PLUGIN_DIR . 'includes/admin/reporting/class-export.php';

/**
 * Export all Payment History to CSV
 *
 * @access      private
 * @since       1.2
 * @return      void
 */
function edd_export_payment_history() {
	require_once EDD_PLUGIN_DIR . 'includes/admin/reporting/class-export-payments.php';

	$payments_export = new EDD_Payments_Export();

	$payments_export->export();
}
add_action( 'edd_payment_export', 'edd_export_payment_history' );

/**
 * Export all customers to CSV
 *
 * Using wpdb directly for performance reasons (workaround of calling all posts and fetch data respectively)
 *
 * @access      private
 * @since       1.2
 * @return      void
 */
function edd_export_all_customers() {
	require_once EDD_PLUGIN_DIR . 'includes/admin/reporting/class-export-customers.php';

	$customer_export = new EDD_Customers_Export();

	$customer_export->export();
}
add_action( 'edd_email_export', 'edd_export_all_customers' );

/**
 * Export all downloads to CSV
 *
 * @access      private
 * @since       1.2
 * @return      void
 */
function edd_export_all_downloads_history() {
	require_once EDD_PLUGIN_DIR . 'includes/admin/reporting/class-export-download-history.php';

	$file_download_export = new EDD_Download_History_Export();

	$file_download_export->export();
}
add_action( 'edd_downloads_history_export', 'edd_export_all_downloads_history' );