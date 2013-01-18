<?php
/**
 * Customers Export Class
 *
 * This class handles customer export
 *
 * @package     Easy Digital Downloads
 * @subpackage  Export Class
 * @copyright   Copyright (c) 2012, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.4.2
*/

class EDD_Customers_Export extends EDD_Export {

	public $export_type = 'customers';

	public function csv_cols() {
		$cols = array(
			'name'      => __( 'Name',   'edd' ),
			'email'     => __( 'Email', 'edd' ),
			'purchases' => __( 'Total Purchases', 'edd' ),
			'amount'    => __( 'Total Purchased', 'edd' )
		);
		return $cols;
	}

	public function get_data() {

		global $wpdb;

		$data = array();

		$emails = $wpdb->get_col( "SELECT DISTINCT meta_value FROM $wpdb->postmeta WHERE meta_key = '_edd_payment_user_email' " );

		foreach ( $emails as $email ) {

			$wp_user = get_user_by( 'email', $email );

			$data[] = array(
				'name'      => $wp_user ? $wp_user->display_name : __( 'Guest', 'edd' ),
				'email'     => $email,
				'purchases' => edd_count_purchases_of_customer( $email ),
				'amount'    => html_entity_decode( edd_currency_filter( edd_format_amount( edd_purchase_total_of_user( $email ) ) ) )
			);

		}

		$data = apply_filters( 'edd_export_get_data', $data );
		$data = apply_filters( 'edd_export_get_data_' . $this->export_type, $data );

		return $data;
	}

}