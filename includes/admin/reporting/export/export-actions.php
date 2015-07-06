<?php

if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Add a hook allowing extensions to register a hook on the batch export process
 *
 * @since  2.4.2
 * @return void
 */
function edd_register_batch_exporters() {
	if ( is_admin() ) {
		do_action( 'edd_register_batch_exporter' );
	}
}
add_action( 'plugins_loaded', 'edd_register_batch_exporters' );

/**
 * Register the payments batch exporter
 * @since  2.4.2
 */
function edd_register_payments_batch_export() {
	add_action( 'edd_batch_export_class_include', 'edd_include_payments_batch_processer', 10, 1 );
}
add_action( 'edd_register_batch_exporter', 'edd_register_payments_batch_export', 10 );

/**
 * Loads the payments batch process if needed
 *
 * @since  2.4.2
 * @param  string $class The class being requested to run for the batch export
 * @return void
 */
function edd_include_payments_batch_processer( $class ) {

	if ( 'EDD_Batch_Payments_Export' === $class ) {
		require_once EDD_PLUGIN_DIR . 'includes/admin/reporting/export/class-batch-export-payments.php';
	}

}

/**
 * Register the customers batch exporter
 * @since  2.4.2
 */
function edd_register_customers_batch_export() {
	add_action( 'edd_batch_export_class_include', 'edd_include_customers_batch_processer', 10, 1 );
}
add_action( 'edd_register_batch_exporter', 'edd_register_customers_batch_export', 10 );

/**
 * Loads the customers batch process if needed
 *
 * @since  2.4.2
 * @param  string $class The class being requested to run for the batch export
 * @return void
 */
function edd_include_customers_batch_processer( $class ) {

	if ( 'EDD_Batch_Customers_Export' === $class ) {
		require_once EDD_PLUGIN_DIR . 'includes/admin/reporting/export/class-batch-export-customers.php';
	}

}

/**
 * Register the file downloads batch exporter
 * @since  2.4.2
 */
function edd_register_file_downloads_batch_export() {
	add_action( 'edd_batch_export_class_include', 'edd_include_file_downloads_batch_processer', 10, 1 );
}
add_action( 'edd_register_batch_exporter', 'edd_register_file_downloads_batch_export', 10 );

/**
 * Loads the file downloads batch process if needed
 *
 * @since  2.4.2
 * @param  string $class The class being requested to run for the batch export
 * @return void
 */
function edd_include_file_downloads_batch_processer( $class ) {

	if ( 'EDD_Batch_File_Downloads_Export' === $class ) {
		require_once EDD_PLUGIN_DIR . 'includes/admin/reporting/export/class-batch-export-file-downloads.php';
	}

}
