<?php
/**
 * Deprecated Hooks
 *
 * All hooks that have been deprecated.
 *
 * @package     EDD
 * @subpackage  Deprecated
 * @copyright   Copyright (c) 2021, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */

/**
 * Legacy pre-refund hook which fired after a payment status changed, but before store stats were updated.
 *
 * @deprecated 3.0
 * @todo       Formally deprecate in EDD 3.1
 * @link       https://github.com/easydigitaldownloads/easy-digital-downloads/issues/8574
 *
 * @param int $order_id The original order id.
 */
add_action( 'edd_refund_order', function( $order_id ) {
	if ( has_action( 'edd_pre_refund_payment' ) ) {
		do_action( 'edd_pre_refund_payment', edd_get_payment( $order_id ) );
	}
} );

/**
 * Legacy post-refund hook which fired after a payment status changed and store stats were updated.
 *
 * @deprecated 3.0
 * @todo       Formally deprecate in EDD 3.1
 * @link       https://github.com/easydigitaldownloads/easy-digital-downloads/issues/8574
 *
 * @param int $order_id The original order id.
 */
add_action( 'edd_refund_order', function( $order_id ) {
	if ( has_action( 'edd_post_refund_payment' ) ) {
		do_action( 'edd_post_refund_payment', edd_get_payment( $order_id ) );
	}
} );
