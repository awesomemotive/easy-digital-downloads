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