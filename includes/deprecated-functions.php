<?php
/**
 * Deprecated Functions
 *
 * All functions that have been deprecated.
 *
 * @package     EDD
 * @subpackage  Deprecated
 * @copyright   Copyright (c) 2013, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Get Download Sales Log
 *
 * Returns an array of sales and sale info for a download.
 *
 * @since       1.0
 * @deprecated  1.3.4
 *
 * @param int $download_id ID number of the download to retrieve a log for
 * @param bool $paginate Whether to paginate the results or not
 * @param int $number Number of results to return
 * @param int $offset Number of items to skip
 *
 * @return mixed array|bool
*/
function edd_get_download_sales_log( $download_id, $paginate = false, $number = 10, $offset = 0 ) {
	$backtrace = debug_backtrace();

	_edd_deprecated_function( __FUNCTION__, '1.3.4', null, $backtrace );

	$sales_log = get_post_meta( $download_id, '_edd_sales_log', true );

	if ( $sales_log ) {
		$sales_log = array_reverse( $sales_log );
		$log = array();
		$log['number'] = count( $sales_log );
		$log['sales'] = $sales_log;

		if ( $paginate ) {
			$log['sales'] = array_slice( $sales_log, $offset, $number );
		}

		return $log;
	}

	return false;
}

/**
 * Get File Download Log
 *
 * Returns an array of file download dates and user info.
 *
 * @deprecated 1.3.4
 * @since 1.0
 *
 * @param int $download_id the ID number of the download to retrieve a log for
 * @param bool $paginate whether to paginate the results or not
 * @param int $number the number of results to return
 * @param int $offset the number of items to skip
 *
 * @return mixed array|bool
*/
function edd_get_file_download_log( $download_id, $paginate = false, $number = 10, $offset = 0 ) {
	$backtrace = debug_backtrace();

	_edd_deprecated_function( __FUNCTION__, '1.3.4', null, $backtrace );

	$download_log = get_post_meta( $download_id, '_edd_file_download_log', true );

	if ( $download_log ) {
		$download_log = array_reverse( $download_log );
		$log = array();
		$log['number'] = count( $download_log );
		$log['downloads'] = $download_log;

		if ( $paginate ) {
			$log['downloads'] = array_slice( $download_log, $offset, $number );
		}

		return $log;
	}

	return false;
}

/**
 * Get Downloads Of Purchase
 *
 * Retrieves an array of all files purchased.
 *
 * @since 1.0
 * @deprecated 1.4
 * @param int $payment_id ID number of the purchase
 * @return mixed array if purchase exists, false otherwise
 */
function edd_get_downloads_of_purchase( $payment_id, $payment_meta = null ) {
	$backtrace = debug_backtrace();

	_edd_deprecated_function( __FUNCTION__, '1.4', 'edd_get_payment_meta_downloads', $backtrace );

	if ( is_null( $payment_meta ) ) {
		$payment_meta = edd_get_payment_meta( $payment_id );
	}

	$downloads = maybe_unserialize( $payment_meta['downloads'] );

	if ( $downloads )
		return $downloads;

	return false;
}

/**
 * Get Menu Access Level
 *
 * Returns the access level required to access the downloads menu. Currently not
 * changeable, but here for a future update.
 *
 * @since 1.0
 * @deprecated 1.4.4
 * @return string
*/
function edd_get_menu_access_level() {
	$backtrace = debug_backtrace();

	_edd_deprecated_function( __FUNCTION__, '1.4.4', 'current_user_can(\'manage_shop_settings\')', $backtrace );

	return apply_filters( 'edd_menu_access_level', 'manage_options' );
}



/**
 * Check if only local taxes are enabled meaning users must opt in by using the
 * option set from the EDD Settings.
 *
 * @since 1.3.3
 * @deprecated 1.6
 * @global $edd_options
 * @return bool $local_only
 */
function edd_local_taxes_only() {

	$backtrace = debug_backtrace();

	_edd_deprecated_function( __FUNCTION__, '1.6', 'no alternatives', $backtrace );

	global $edd_options;

	$local_only = isset( $edd_options['tax_condition'] ) && $edd_options['tax_condition'] == 'local';

	return apply_filters( 'edd_local_taxes_only', $local_only );
}

/**
 * Checks if a customer has opted into local taxes
 *
 * @since 1.4.1
 * @deprecated 1.6
 * @uses EDD_Session::get()
 * @return bool
 */
function edd_local_tax_opted_in() {

	$backtrace = debug_backtrace();

	_edd_deprecated_function( __FUNCTION__, '1.6', 'no alternatives', $backtrace );

	$opted_in = EDD()->session->get( 'edd_local_tax_opt_in' );
	return ! empty( $opted_in );
}