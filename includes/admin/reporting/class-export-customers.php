<?php
/**
 * Customers Export Class
 *
 * This class handles customer export
 *
 * @package     EDD
 * @subpackage  Admin/Reports
 * @copyright   Copyright (c) 2015, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.4.4
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * EDD_Customers_Export Class
 *
 * @since 1.4.4
 */
class EDD_Customers_Export extends EDD_Export {
	/**
	 * Our export type. Used for export-type specific filters/actions
	 *
	 * @var string
	 * @since 1.4.4
	 */
	public $export_type = 'customers';

	/**
	 * Set the export headers
	 *
	 * @since 1.4.4
	 * @return void
	 */
	public function headers() {
		ignore_user_abort( true );

		if ( ! edd_is_func_disabled( 'set_time_limit' ) )
			set_time_limit( 0 );

		$extra = '';

		if ( ! empty( $_POST['edd_export_download'] ) ) {
			$extra = sanitize_title( get_the_title( absint( $_POST['edd_export_download'] ) ) ) . '-';
		}

		nocache_headers();
		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename=' . apply_filters( 'edd_customers_export_filename', 'edd-export-' . $extra . $this->export_type . '-' . date( 'm-d-Y' ) ) . '.csv' );
		header( "Expires: 0" );
	}

	/**
	 * Set the CSV columns
	 *
	 * @since 1.4.4
	 * @return array $cols All the columns
	 */
	public function csv_cols() {
		if ( ! empty( $_POST['edd_export_download'] ) ) {
			$cols = array(
				'first_name' => __( 'First Name',   'easy-digital-downloads' ),
				'last_name'  => __( 'Last Name',   'easy-digital-downloads' ),
				'email'      => __( 'Email', 'easy-digital-downloads' ),
				'date'       => __( 'Date Purchased', 'easy-digital-downloads' )
			);
		} else {

			$cols = array();

			if( 'emails' != $_POST['edd_export_option'] ) {
				$cols['name'] = __( 'Name',   'easy-digital-downloads' );
			}

			$cols['email'] = __( 'Email',   'easy-digital-downloads' );

			if( 'full' == $_POST['edd_export_option'] ) {
				$cols['purchases'] = __( 'Total Purchases',   'easy-digital-downloads' );
				$cols['amount']    = __( 'Total Purchased', 'easy-digital-downloads' ) . ' (' . html_entity_decode( edd_currency_filter( '' ) ) . ')';
			}

		}

		return $cols;
	}

	/**
	 * Get the Export Data
	 *
	 * @since 1.4.4
	 * @global object $wpdb Used to query the database using the WordPress
	 *   Database API
	 * @global object $edd_logs EDD Logs Object
	 * @return array $data The data for the CSV file
	 */
	public function get_data() {
		global $wpdb;

		$data = array();

		if ( ! empty( $_POST['edd_export_download'] ) ) {

			// Export customers of a specific product
			global $edd_logs;

			$args = array(
				'post_parent' => absint( $_POST['edd_export_download'] ),
				'log_type'    => 'sale',
				'nopaging'    => true
			);

			if( isset( $_POST['edd_price_option'] ) ) {
				$args['meta_query'] = array(
					array(
						'key'   => '_edd_log_price_id',
						'value' => (int) $_POST['edd_price_option']
					)
				);
			}

			$logs = $edd_logs->get_connected_logs( $args );

			if ( $logs ) {
				foreach ( $logs as $log ) {
					$payment_id = get_post_meta( $log->ID, '_edd_log_payment_id', true );
					$user_info  = edd_get_payment_meta_user_info( $payment_id );
					$data[] = array(
						'first_name' => $user_info['first_name'],
						'last_name'  => $user_info['last_name'],
						'email'      => $user_info['email'],
						'date'       => $log->post_date
					);
				}
			}

		} else {

			// Export all customers
			$customers = EDD()->customers->get_customers( array( 'number' => -1 ) );

			$i = 0;

			foreach ( $customers as $customer ) {

				if( 'emails' != $_POST['edd_export_option'] ) {
					$data[$i]['name'] = $customer->name;
				}

				$data[$i]['email'] = $customer->email;

				if( 'full' == $_POST['edd_export_option'] ) {

					$data[$i]['purchases'] = $customer->purchase_count;
					$data[$i]['amount']    = edd_format_amount( $customer->purchase_value );

				}
				$i++;
			}
		}

		$data = apply_filters( 'edd_export_get_data', $data );
		$data = apply_filters( 'edd_export_get_data_' . $this->export_type, $data );

		return $data;
	}
}
