<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Tools Actions
 *
 * @package     EDD
 * @subpackage  Admin/Tools
 * @copyright   Copyright (c) 2015, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       2.5
 */

/**
 * Register the recount batch processor
 * @since  2.5
 */
function edd_register_batch_recount_store_earnings_tool() {
	add_action( 'edd_batch_export_class_include', 'edd_include_recount_store_earnings_tool_batch_processer', 10, 1 );
}
add_action( 'edd_register_batch_exporter', 'edd_register_batch_recount_store_earnings_tool', 10 );

/**
 * Loads the tools batch processing class for recounting store earnings
 *
 * @since  2.5
 * @param  string $class The class being requested to run for the batch export
 * @return void
 */
function edd_include_recount_store_earnings_tool_batch_processer( $class ) {

	if ( 'EDD_Tools_Recount_Store_Earnings' === $class ) {
		require_once EDD_PLUGIN_DIR . 'includes/admin/tools/class-edd-tools-recount-store-earnings.php';
	}

}

/**
 * Register the recount download batch processor
 * @since  2.5
 */
function edd_register_batch_recount_download_tool() {
	add_action( 'edd_batch_export_class_include', 'edd_include_recount_download_tool_batch_processer', 10, 1 );
}
add_action( 'edd_register_batch_exporter', 'edd_register_batch_recount_download_tool', 10 );

/**
 * Loads the tools batch processing class for recounting download stats
 *
 * @since  2.5
 * @param  string $class The class being requested to run for the batch export
 * @return void
 */
function edd_include_recount_download_tool_batch_processer( $class ) {

	if ( 'EDD_Tools_Recount_Download_Stats' === $class ) {
		require_once EDD_PLUGIN_DIR . 'includes/admin/tools/class-edd-tools-recount-download-stats.php';
	}

}

/**
 * Register the recount all stats batch processor
 * @since  2.5
 */
function edd_register_batch_recount_all_tool() {
	add_action( 'edd_batch_export_class_include', 'edd_include_recount_all_tool_batch_processer', 10, 1 );
}
add_action( 'edd_register_batch_exporter', 'edd_register_batch_recount_all_tool', 10 );

/**
 * Loads the tools batch processing class for recounting all stats
 *
 * @since  2.5
 * @param  string $class The class being requested to run for the batch export
 * @return void
 */
function edd_include_recount_all_tool_batch_processer( $class ) {

	if ( 'EDD_Tools_Recount_All_Stats' === $class ) {
		require_once EDD_PLUGIN_DIR . 'includes/admin/tools/class-edd-tools-recount-all-stats.php';
	}

}

/**
 * Register the reset stats batch processor
 * @since  2.5
 */
function edd_register_batch_reset_tool() {
	add_action( 'edd_batch_export_class_include', 'edd_include_reset_tool_batch_processer', 10, 1 );
}
add_action( 'edd_register_batch_exporter', 'edd_register_batch_reset_tool', 10 );

/**
 * Loads the tools batch processing class for resetting store and product earnings
 *
 * @since  2.5
 * @param  string $class The class being requested to run for the batch export
 * @return void
 */
function edd_include_reset_tool_batch_processer( $class ) {

	if ( 'EDD_Tools_Reset_Stats' === $class ) {
		require_once EDD_PLUGIN_DIR . 'includes/admin/tools/class-edd-tools-reset-stats.php';
	}

}

/**
 * Register the reset customer stats batch processor
 * @since  2.5
 */
function edd_register_batch_customer_recount_tool() {
	add_action( 'edd_batch_export_class_include', 'edd_include_customer_recount_tool_batch_processer', 10, 1 );
}
add_action( 'edd_register_batch_exporter', 'edd_register_batch_customer_recount_tool', 10 );

/**
 * Loads the tools batch processing class for resetting all customer stats
 *
 * @since  2.5
 * @param  string $class The class being requested to run for the batch export
 * @return void
 */
function edd_include_customer_recount_tool_batch_processer( $class ) {

	if ( 'EDD_Tools_Recount_Customer_Stats' === $class ) {
		require_once EDD_PLUGIN_DIR . 'includes/admin/tools/class-edd-tools-recount-customer-stats.php';
	}

}
