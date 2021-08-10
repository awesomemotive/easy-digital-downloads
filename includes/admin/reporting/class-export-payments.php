<?php
/**
 * Payments Export Class
 *
 * This class handles payment export
 *
 * @package     EDD
 * @subpackage  Admin/Reports
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.4.4
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * EDD_Payments_Export Class
 *
 * @since 1.4.4
 */
class EDD_Payments_Export extends EDD_Export {
	/**
	 * Our export type. Used for export-type specific filters/actions
	 * @var string
	 * @since 1.4.4
	 */
	public $export_type = 'payments';

	/**
	 * Set the export headers
	 *
	 * @since 1.6
	 * @return void
	 */
	public function headers() {
		edd_set_time_limit();

		$month = isset( $_POST['month'] ) ? absint( $_POST['month'] ) : date( 'n' );
		$year  = isset( $_POST['year']  ) ? absint( $_POST['year']  ) : date( 'Y' );

		nocache_headers();
		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename="' . apply_filters( 'edd_payments_export_filename', 'edd-export-' . $this->export_type . '-' . $month . '-' . $year ) . '.csv"' );
		header( 'Expires: 0' );
	}

	/**
	 * Set the CSV columns
	 *
	 * @since 1.4.4
	 * @return array $cols All the columns
	 */
	public function csv_cols() {
		$cols = array(
			'id'       => __( 'ID',   'easy-digital-downloads' ), // unaltered payment ID (use for querying)
			'seq_id'   => __( 'Payment Number',   'easy-digital-downloads' ), // sequential payment ID
			'email'    => __( 'Email', 'easy-digital-downloads' ),
			'first'    => __( 'First Name', 'easy-digital-downloads' ),
			'last'     => __( 'Last Name', 'easy-digital-downloads' ),
			'address1' => __( 'Address', 'easy-digital-downloads' ),
			'address2' => __( 'Address (Line 2)', 'easy-digital-downloads' ),
			'city'     => __( 'City', 'easy-digital-downloads' ),
			'state'    => __( 'State', 'easy-digital-downloads' ),
			'country'  => __( 'Country', 'easy-digital-downloads' ),
			'zip'      => __( 'Zip / Postal Code', 'easy-digital-downloads' ),
			'products' => __( 'Products', 'easy-digital-downloads' ),
			'skus'     => __( 'SKUs', 'easy-digital-downloads' ),
			'currency' => __( 'Currency', 'easy-digital-downloads' ),
			'amount'   => __( 'Amount', 'easy-digital-downloads' ),
			'tax'      => __( 'Tax', 'easy-digital-downloads' ),
			'discount' => __( 'Discount Code', 'easy-digital-downloads' ),
			'gateway'  => __( 'Payment Method', 'easy-digital-downloads' ),
			'trans_id' => __( 'Transaction ID', 'easy-digital-downloads' ),
			'key'      => __( 'Purchase Key', 'easy-digital-downloads' ),
			'date'     => __( 'Date', 'easy-digital-downloads' ),
			'user'     => __( 'User', 'easy-digital-downloads' ),
			'status'   => __( 'Status', 'easy-digital-downloads' )
		);

		if( ! edd_use_skus() ){
			unset( $cols['skus'] );
		}
		if ( ! edd_get_option( 'enable_sequential' ) ) {
			unset( $cols['seq_id'] );
		}

		return $cols;
	}

	/**
	 * Get the Export Data
	 *
	 * @since 1.4.4
	 * @global object $wpdb Used to query the database using the WordPress
	 *   Database API
	 * @return array $data The data for the CSV file
	 */
	public function get_data() {
		global $wpdb;

		$data = array();

		$payments = edd_get_payments( array(
			'offset' => 0,
			'number' => 9999999,
			'mode'   => edd_is_test_mode() ? 'test' : 'live',
			'status' => isset( $_POST['edd_export_payment_status'] ) ? $_POST['edd_export_payment_status'] : 'any',
			'month'  => isset( $_POST['month'] ) ? absint( $_POST['month'] ) : date( 'n' ),
			'year'   => isset( $_POST['year'] ) ? absint( $_POST['year'] ) : date( 'Y' )
		) );

		foreach ( $payments as $payment ) {
			$payment_meta   = edd_get_payment_meta( $payment->ID );
			$user_info      = edd_get_payment_meta_user_info( $payment->ID );
			$downloads      = edd_get_payment_meta_cart_details( $payment->ID );
			$total          = edd_get_payment_amount( $payment->ID );
			$user_id        = isset( $user_info['id'] ) && $user_info['id'] != -1 ? $user_info['id'] : $user_info['email'];
			$products       = '';
			$skus           = '';

			if ( $downloads ) {
				foreach ( $downloads as $key => $download ) {
					// Download ID
					$id = isset( $payment_meta['cart_details'] ) ? $download['id'] : $download;

					// If the download has variable prices, override the default price
					$price_override = isset( $payment_meta['cart_details'] ) ? $download['price'] : null;

					$price = edd_get_download_final_price( $id, $user_info, $price_override );

					// Display the Downoad Name
					$products .= get_the_title( $id ) . ' - ';

					if ( edd_use_skus() ) {
						$sku = edd_get_download_sku( $id );

						if ( ! empty( $sku ) )
							$skus .= $sku;
					}

					if ( isset( $downloads[ $key ]['item_number'] ) && isset( $downloads[ $key ]['item_number']['options'] ) ) {
						$price_options = $downloads[ $key ]['item_number']['options'];

						if ( isset( $price_options['price_id'] ) ) {
							$products .= edd_get_price_option_name( $id, $price_options['price_id'], $payment->ID ) . ' - ';
						}
					}
					$products .= html_entity_decode( edd_currency_filter( $price ) );

					if ( $key != ( count( $downloads ) -1 ) ) {
						$products .= ' / ';

						if( edd_use_skus() )
							$skus .= ' / ';
					}
				}
			}

			if ( is_numeric( $user_id ) ) {
				$user = get_userdata( $user_id );
			} else {
				$user = false;
			}

			$currency_code = edd_get_payment_currency_code( $payment->ID );

			$data[] = array(
				'id'       => $payment->ID,
				'seq_id'   => edd_get_payment_number( $payment->ID ),
				'email'    => $payment_meta['email'],
				'first'    => $user_info['first_name'],
				'last'     => $user_info['last_name'],
				'address1' => isset( $user_info['address']['line1'] ) ? $user_info['address']['line1'] : '',
				'address2' => isset( $user_info['address']['line2'] ) ? $user_info['address']['line2'] : '',
				'city'     => isset( $user_info['address']['city'] ) ? $user_info['address']['city'] : '',
				'state'    => isset( $user_info['address']['state'] ) ? $user_info['address']['state'] : '',
				'country'  => isset( $user_info['address']['country'] ) ? $user_info['address']['country'] : '',
				'zip'      => isset( $user_info['address']['zip'] ) ? $user_info['address']['zip'] : '',
				'products' => $products,
				'skus'     => $skus,
				'currency' => $currency_code,
				'amount'   => html_entity_decode( edd_format_amount( $total, $currency_code ) ),
				'tax'      => html_entity_decode( edd_format_amount( edd_get_payment_tax( $payment->ID, $payment_meta ), $currency_code ) ),
				'discount' => isset( $user_info['discount'] ) && $user_info['discount'] != 'none' ? $user_info['discount'] : __( 'none', 'easy-digital-downloads' ),
				'gateway'  => edd_get_gateway_admin_label( edd_get_payment_meta( $payment->ID, '_edd_payment_gateway', true ) ),
				'trans_id' => edd_get_payment_transaction_id( $payment->ID ),
				'key'      => $payment_meta['key'],
				'date'     => $payment->post_date,
				'user'     => $user ? $user->display_name : __( 'guest', 'easy-digital-downloads' ),
				'status'   => edd_get_payment_status( $payment, true )
			);

		}

		$data = apply_filters( 'edd_export_get_data', $data );
		$data = apply_filters( 'edd_export_get_data_' . $this->export_type, $data );

		return $data;
	}
}
