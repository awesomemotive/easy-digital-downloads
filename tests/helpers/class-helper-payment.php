<?php

/**
 * Class EDD_Helper_Payment.
 *
 * Helper class to create and delete a payment easily.
 */
class EDD_Helper_Payment extends WP_UnitTestCase {

	/**
	 * Delete a payment.
	 *
	 * @since 2.3
	 *
	 * @param int $payment_id ID of the payment to delete.
	 */
	public static function delete_payment( $payment_id ) {

		// Delete the payment
		edd_delete_purchase( $payment_id );

	}

	/**
	 * Create a simple payment.
	 *
	 * @since 2.3
	 */
	public static function create_simple_payment() {

		global $edd_options;

		// Enable a few options
		$edd_options['enable_sequential'] = '1';
		$edd_options['sequential_prefix'] = 'EDD-';
		update_option( 'edd_settings', $edd_options );

		$simple_download   = EDD_Helper_Download::create_simple_download();
		$variable_download = EDD_Helper_Download::create_variable_download();

		/** Generate some sales */
		$user      = get_userdata(1);
		$user_info = array(
			'id'            => $user->ID,
			'email'         => $user->user_email,
			'first_name'    => $user->first_name,
			'last_name'     => $user->last_name,
			'discount'      => 'none'
		);

		$download_details = array(
			array(
				'id' => $simple_download->ID,
				'options' => array(
					'price_id' => 0
				)
			),
			array(
				'id' => $variable_download->ID,
				'options' => array(
					'price_id' => 1
				)
			),
		);

		$total                  = 0;
		$simple_price           = get_post_meta( $simple_download->ID, 'edd_price', true );
		$variable_prices        = get_post_meta( $variable_download->ID, 'edd_variable_prices', true );
		$variable_item_price    = $variable_prices[1]['amount']; // == $100

		$total += $variable_item_price + $simple_price;

		$cart_details = array(
			array(
				'name'          => 'Test Download',
				'id'            => $simple_download->ID,
				'item_number'   => array(
					'id'        => $simple_download->ID,
					'options'   => array(
						'price_id' => 1
					)
				),
				'price'         => $simple_price,
				'item_price'    => $simple_price,
				'tax'           => 0,
				'quantity'      => 1
			),
			array(
				'name'          => 'Variable Test Download',
				'id'            => $variable_download->ID,
				'item_number'   => array(
					'id'        => $variable_download->ID,
					'options'   => array(
						'price_id' => 1
					)
				),
				'price'         => $variable_item_price,
				'item_price'    => $variable_item_price,
				'tax'           => 0,
				'quantity'      => 1
			),
		);

		$purchase_data = array(
			'price'         => number_format( (float) $total, 2 ),
			'date'          => date( 'Y-m-d H:i:s', strtotime( '-1 day' ) ),
			'purchase_key'  => strtolower( md5( uniqid() ) ),
			'user_email'    => $user_info['email'],
			'user_info'     => $user_info,
			'currency'      => 'USD',
			'downloads'     => $download_details,
			'cart_details'  => $cart_details,
			'status'        => 'pending'
		);

		$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
		$_SERVER['SERVER_NAME'] = 'edd_virtual';

		$payment_id = edd_insert_payment( $purchase_data );
		$key 		= $purchase_data['purchase_key'];

		$transaction_id = 'FIR3SID3';
		edd_set_payment_transaction_id( $payment_id, $transaction_id );
		edd_insert_payment_note( $payment_id, sprintf( __( 'PayPal Transaction ID: %s', 'edd' ), $transaction_id ) );

		return $payment_id;

	}

}
