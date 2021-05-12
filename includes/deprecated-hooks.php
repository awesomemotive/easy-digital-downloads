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

/**
 * Fires after the order receipt files, if needed.
 *
 * @deprecated 3.0
 * @todo       Formally deprecate in EDD 3.1
 * @param int   $filekey          Index of array of files returned by edd_get_download_files() that this download link is for.
 * @param array $file             The array of file information.
 * @param int   $item->product_id The product ID.
 * @param int   $order->id        The order ID.
 */
add_action( 'edd_order_receipt_files', function( $filekey, $file, $product_id, $order_id ) {
	if ( ! has_action( 'edd_receipt_files' ) ) {
		return;
	}
	$meta = edd_get_payment_meta( $order_id );
	do_action( 'edd_receipt_files', $filekey, $file, $product_id, $order_id, $meta );
}, 10, 4 );


/**
 * Fires after the order receipt bundled items, if needed.
 *
 * @deprecated 3.0
 * @todo       Formally deprecate in EDD 3.1
 * @param int   $filekey          Index of array of files returned by edd_get_download_files() that this download link is for.
 * @param array $file             The array of file information.
 * @param int   $item->product_id The product ID.
 * @param array $bundle_item      The array of information about the bundled item.
 * @param int   $order->id        The order ID.
 */
add_action( 'edd_order_receipt_bundle_files', function( $filekey, $file, $product_id, $bundle_item, $order_id ) {
	if ( ! has_action( 'edd_receipt_bundle_files' ) ) {
		return;
	}
	$meta = edd_get_payment_meta( $order_id );
	do_action( 'edd_receipt_bundle_files', $filekey, $file, $product_id, $bundle_item, $order_id, $meta );
}, 10, 5 );

/**
 * Fires at the end of the product cell.
 *
 * @deprecated 3.0
 * @todo       Formally deprecate in EDD 3.1
 * @param \EDD\Orders\Order_Item $item The current order item.
 * @param \EDD\Orders\Order $order     The current order object.
 */
add_action( 'edd_order_receipt_after_files', function( $item, $order ) {
	if ( ! has_action( 'edd_purchase_receipt_after_files' ) ) {
		return;
	}
	$meta = edd_get_payment_meta( $order->id );
	do_action( 'edd_purchase_receipt_after_files', $item->product_id, $order->id, $meta, $item->price_id );
}, 10, 2 );

/**
 * Fires before the order receipt table, if needed.
 *
 * @deprecated 3.0
 * @todo       Formally deprecate in EDD 3.1
 * @param \EDD\Orders\Order $order            The current order object.
 * @param array             $edd_receipt_args The shortcode parameters for the receipt.
 */
add_action( 'edd_order_receipt_before_table', function( $order, $edd_receipt_args ) {
	if ( ! has_action( 'edd_payment_receipt_before_table' ) ) {
		return;
	}
	$payment = edd_get_payment( $order->id );
	do_action( 'edd_payment_receipt_before_table', $payment, $edd_receipt_args );
}, 10, 2 );

/**
 * Fires at the beginning of the order receipt `thead`, if needed.
 *
 * @deprecated 3.0
 * @todo       Formally deprecate in EDD 3.1
 * @param \EDD\Orders\Order $order            The current order object.
 * @param array             $edd_receipt_args The shortcode parameters for the receipt.
 */
add_action( 'edd_order_receipt_before', function( $order, $edd_receipt_args ) {
	if ( ! has_action( 'edd_payment_receipt_before' ) ) {
		return;
	}
	$payment = edd_get_payment( $order->id );
	do_action( 'edd_payment_receipt_before', $payment, $edd_receipt_args );
}, 10, 2 );

/**
 * Fires at the end of the order receipt `tbody`, if needed.
 *
 * @deprecated 3.0
 * @todo       Formally deprecate in EDD 3.1
 * @param \EDD\Orders\Order $order            The current order object.
 * @param array             $edd_receipt_args The shortcode parameters for the receipt.
 */
add_action( 'edd_order_receipt_after', function( $order, $edd_receipt_args ) {
	if ( ! has_action( 'edd_payment_receipt_after' ) ) {
		return;
	}
	$payment = edd_get_payment( $order->id );
	do_action( 'edd_payment_receipt_after', $payment, $edd_receipt_args );
}, 10, 2 );

/**
 * Fires after the order receipt table, if needed.
 *
 * @deprecated 3.0
 * @todo       Formally deprecate in EDD 3.1
 * @param \EDD\Orders\Order $order            The current order object.
 * @param array             $edd_receipt_args The shortcode parameters for the receipt.
 */
add_action( 'edd_order_receipt_after_table', function( $order, $edd_receipt_args ) {
	if ( ! has_action( 'edd_payment_receipt_after_table' ) ) {
		return;
	}
	$payment = edd_get_payment( $order->id );
	do_action( 'edd_payment_receipt_after_table', $payment, $edd_receipt_args );
}, 10, 2 );

/**
 * Fires the edd_before_purchase_history hook in the purchase history, if needed.
 *
 * @deprecated 3.0
 * @todo       Formally deprecate in EDD 3.1
 * @param \EDD\Orders\Order[] $orders The array of the current user's orders.
 */
add_action( 'edd_before_order_history', function( $orders ) {
	if ( ! has_action( 'edd_before_purchase_history' ) ) {
		return;
	}

	$payments = array();

	if ( ! empty( $orders ) ) {
		$order_ids = wp_list_pluck( $orders, 'id' );
		$payments  = edd_get_payments(
			array(
				'id__in'  => $order_ids,
				'orderby' => 'date',
			)
		);
	}

	do_action( 'edd_before_purchase_history', $payments );
} );

/**
 * Fires at the beginning of the purchase history row, if needed.
 *
 * @deprecated 3.0
 * @todo       Formally deprecate in EDD 3.1
 * @param \EDD\Orders\Order $order The current order object.
 */
add_action( 'edd_order_history_row_start', function( \EDD\Orders\Order $order ) {
	if ( ! has_action( 'edd_purchase_history_row_start' ) ) {
		return;
	}

	$payment = edd_get_payment( $order->id );
	if ( ! $payment ) {
		return;
	}

	do_action( 'edd_purchase_history_row_start', $payment->ID, $payment->payment_meta );
} );

/**
 * Fires at the end of the purchase history row, if needed.
 *
 * @deprecated 3.0
 * @todo       Formally deprecate in EDD 3.1
 * @param \EDD\Orders\Order $order The current order object.
 */
add_action( 'edd_order_history_row_end', function( \EDD\Orders\Order $order ) {
	if ( ! has_action( 'edd_purchase_history_row_end' ) ) {
		return;
	}

	$payment = edd_get_payment( $order->id );
	if ( ! $payment ) {
		return;
	}

	do_action( 'edd_purchase_history_row_end', $payment->ID, $payment->payment_meta );
} );

/**
 * Fires the edd_after_purchase_history hook in the purchase history, if needed.
 *
 * @deprecated 3.0
 * @todo       Formally deprecate in EDD 3.1
 * @param \EDD\Orders\Order[] $orders The array of the current user's orders.
 */
add_action( 'edd_after_order_history', function( $orders ) {
	if ( ! has_action( 'edd_after_purchase_history' ) ) {
		return;
	}

	$payments = array();

	if ( ! empty( $orders ) ) {
		$order_ids = wp_list_pluck( $orders, 'id' );
		$payments  = edd_get_payments(
			array(
				'id__in'  => $order_ids,
				'orderby' => 'date',
			)
		);
	}

	do_action( 'edd_after_purchase_history', $payments );
} );

/**
 * Fires after the individual download file in the downloads history, if needed.
 *
 * @deprecated 3.0
 * @todo       Formally deprecate in 3.1
 * @param int                    $filekey Download file ID.
 * @param array                  $file    Array of file information.
 * @param \EDD\Orders\Order_Item $item    The order item object.
 * @param \EDD\Orders\Order      $order   The order object.
 */
add_action( 'edd_download_history_download_file', function( $filekey, $file, $item, $order ) {
	if ( ! has_action( 'edd_download_history_files' ) ) {
		return;
	}
	$purchase_data = edd_get_payment_meta( $order->id );
	do_action( 'edd_download_history_files', $filekey, $file, $item->product_id, $order->id, $purchase_data );
}, 10, 4 );
