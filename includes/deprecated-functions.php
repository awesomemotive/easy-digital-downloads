<?php
/**
 * Deprecated Functions
 *
 * All functions that have been deprecated.
 *
 * @package     Easy Digital Downloads
 * @subpackage  Deprecated Functions
 * @copyright   Copyright (c) 2012, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
*/

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Count Payments
 *
 * Returns the total number of payments recorded.
 *
 * @access      public
 * @since       1.0
 * @deprecated 	1.2
 * @return      integer
*/

function edd_count_payments( $mode, $user = null ) {

	_edd_deprecated_function( __FUNCTION__, '1.2' );

	$payments = edd_get_payments( array(
		'offset'  => 0,
		'number'  => -1,
		'mode'    => $mode,
		'orderby' => 'ID',
		'order'   => 'DESC',
		'user'    => $user
	) );
	$count = 0;
	if( $payments ) {
		$count = count( $payments );
	}
	return $count;
}



/**
 * Get Download Sales Log
 *
 * Returns an array of sales and sale info for a download.
 *
 * @param		$download_id INT the ID number of the download to retrieve a log for
 * @param		$paginate bool whether to paginate the results or not
 * @param		$number int the number of results to return
 * @param		$offset int the number of items to skip
 *
 * @access      public
 * @deprecated  1.3.4
 * @since       1.0
 * @return      array
*/

function edd_get_download_sales_log( $download_id, $paginate = false, $number = 10, $offset = 0 ) {

	_edd_deprecated_function( __FUNCTION__, '1.3.4' );

	$sales_log = get_post_meta( $download_id, '_edd_sales_log', true );

	if( $sales_log ) {
		$sales_log = array_reverse( $sales_log );
		$log = array();
		$log['number'] = count( $sales_log );
		$log['sales'] = $sales_log;
		if( $paginate ) {
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
 * @access      public
 * @deprecated  1.3.4
 * @since       1.0
 *
 * @param		$download_id INT the ID number of the download to retrieve a log for
 * @param		$paginate bool whether to paginate the results or not
 * @param		$number int the number of results to return
 * @param		$offset int the number of items to skip
 *
 * @return      array
*/

function edd_get_file_download_log( $download_id, $paginate = false, $number = 10, $offset = 0 ) {

	_edd_deprecated_function( __FUNCTION__, '1.3.4' );

	$download_log = get_post_meta( $download_id, '_edd_file_download_log', true );

	if( $download_log ) {
		$download_log = array_reverse( $download_log );
		$log = array();
		$log['number'] = count( $download_log );
		$log['downloads'] = $download_log;

		if( $paginate ) {
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
 * @access      public
 * @since       1.0
 * @deprecated  1.4
 * @param       int $payment_id - the ID number of the purchase
 * @return      mixed - array if purchase exists, false otherwise
*/

function edd_get_downloads_of_purchase($payment_id, $payment_meta = null){

	_edd_deprecated_function( __FUNCTION__, '1.4', 'edd_get_payment_meta_downloads' );

	if(is_null($payment_meta)) {
		$payment_meta = edd_get_payment_meta( $payment_id );
	}
	$downloads = maybe_unserialize($payment_meta['downloads']);
	if($downloads)
		return $downloads;
	return false;
}
