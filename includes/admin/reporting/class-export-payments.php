<?php
/**
 * Payments Export Class
 *
 * This class handles payment export
 *
 * @package     Easy Digital Downloads
 * @subpackage  Export Class
 * @copyright   Copyright (c) 2013, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.4.4
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class EDD_Payments_Export extends EDD_Export {
	/**
	 * Our export type. Used for export-type specific filters / actions
	 *
	 * @access      public
	 * @var         string
	 * @since       1.4.4
	 */
	public $export_type = 'payments';

	/**
	 * Set the CSV columns
	 *
	 * @access      public
	 * @since       1.4.4
	 * @return      array
	 */
	public function csv_cols() {
		$cols = array(
			'id'       => __( 'ID',   'edd' ),
			'email'    => __( 'Email', 'edd' ),
			'first'    => __( 'First Name', 'edd' ),
			'last'     => __( 'Last Name', 'edd' ),
			'products' => __( 'Products', 'edd' ),
			'amount'   => __( 'Amount', 'edd' ),
			'tax'      => __( 'Tax', 'edd' ),
			'gateway'  => __( 'Payment Method', 'edd' ),
			'key'      => __( 'Purchase Key', 'edd' ),
			'date'     => __( 'Date', 'edd' ),
			'user'     => __( 'User', 'edd' ),
			'status'   => __( 'Status', 'edd' )
		);
		return $cols;
	}

	/**
	 * Get the data being exported
	 *
	 * @access      public
	 * @since       1.4.4
	 * @return      array
	 */
	public function get_data() {
		global $wpdb;

		$data = array();

		$payments = edd_get_payments( array(
			'offset'  => 0,
			'number'  => -1,
			'mode'    => edd_is_test_mode() ? 'test' : 'live',
			'status'  => isset( $_POST['edd_export_payment_status'] ) ? $_POST['edd_export_payment_status'] : 'any'
		) );

		foreach ( $payments as $payment ) {
			$payment_meta 	= edd_get_payment_meta( $payment->ID );
			$user_info 		= edd_get_payment_meta_user_info( $payment->ID );
			$downloads      = edd_get_payment_meta_cart_details( $payment->ID );
			$total          = isset( $payment_meta['amount'] ) ? $payment_meta['amount'] : 0.00;
			$user_id        = isset( $user_info['id'] ) && $user_info['id'] != -1 ? $user_info['id'] : $user_info['email'];
			$products       = '';

			if ( $downloads ) {
				foreach ( $downloads as $key => $download ) {
					// Download ID
					$id = isset( $payment_meta['cart_details'] ) ? $download['id'] : $download;

					// If the download has variable prices, override the default price
					$price_override = isset( $payment_meta['cart_details'] ) ? $download['price'] : null;

					$price = edd_get_download_final_price( $id, $user_info, $price_override );

					// Display the Downoad Name
					$products .= get_the_title( $id ) . ' - ';

					if ( isset( $downloads[ $key ]['item_number'] ) ) {
						$price_options = $downloads[ $key ]['item_number']['options'];

						if ( isset( $price_options['price_id'] ) ) {
							$products .= edd_get_price_option_name( $id, $price_options['price_id'] ) . ' - ';
						}
					}
					$products .= html_entity_decode( edd_currency_filter( $price ) );

					if ( $key != ( count( $downloads ) -1 ) ) {
						$products .= ' / ';
					}
				}
			}

			if ( is_numeric( $user_id ) ) {
				$user = get_userdata( $user_id );
			} else {
				$user = false;
			}

			$data[] = array(
				'id'       => $payment->ID,
				'email'    => $payment_meta['email'],
				'first'    => $user_info['first_name'],
				'last'     => $user_info['last_name'],
				'products' => $products,
				'amount'   => html_entity_decode( edd_currency_filter( edd_format_amount( $total ) ) ),
				'tax'      => html_entity_decode( edd_payment_tax( $payment->ID, $payment_meta ) ),
				'discount' => isset( $user_info['discount'] ) && $user_info['discount'] != 'none' ? $user_info['discount'] : __( 'none', 'edd' ),
				'gateway'  => edd_get_gateway_admin_label( get_post_meta( $payment->ID, '_edd_payment_gateway', true ) ),
				'key'      => $payment_meta['key'],
				'date'     => $payment->post_date,
				'user'     => $user ? $user->display_name : __( 'guest', 'edd' ),
				'status'   => edd_get_payment_status( $payment, true )
			);

		}

		$data = apply_filters( 'edd_export_get_data', $data );
		$data = apply_filters( 'edd_export_get_data_' . $this->export_type, $data );

		return $data;
	}
}