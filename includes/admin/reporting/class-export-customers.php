<?php
/**
 * Customers Export Class
 *
 * This class handles customer export
 *
 * @package     EDD
 * @subpackage  Admin/Reports
 * @copyright   Copyright (c) 2013, Pippin Williamson
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
	 * @access public
	 * @since 1.4.4
	 * @return void
	 */
	public function headers() {
		ignore_user_abort( true );

		if ( ! edd_is_func_disabled( 'set_time_limit' ) && ! ini_get( 'safe_mode' ) )
			set_time_limit( 0 );

		$extra = '';

		if ( ! empty( $_POST['edd_export_download'] ) ) {
			$extra = sanitize_title( get_the_title( absint( $_POST['edd_export_download'] ) ) ) . '-';
		}

		nocache_headers();
		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename=edd-export-' . $extra . $this->export_type . '-' . date( 'm-d-Y' ) . '.csv' );
		header( "Expires: 0" );
	}

	/**
	 * Set the CSV columns
	 *
	 * @access public
	 * @since 1.4.4
	 * @return array $cols All the columns
	 */
	public function csv_cols() {
		if ( ! empty( $_POST['edd_export_download'] ) ) {
			$cols = array(
				'name'      => __( 'Name',   'edd' ),
				'email'     => __( 'Email', 'edd' ),
				'date'      => __( 'Date Purchased', 'edd' )
			);
		} else {

			$cols = array();

			if( 'emails' != $_POST['edd_export_option'] ) {
				$cols['name'] = __( 'Name',   'edd' );
			}

			$cols['email'] = __( 'Email',   'edd' );

			if( 'full' == $_POST['edd_export_option'] ) {
				$cols['purchases'] = __( 'Total Purchases',   'edd' );
				$cols['amount']    = __( 'Total Purchased', 'edd' ) . ' (' . html_entity_decode( edd_currency_filter( '' ) ) . ')';
			}

		}

		return $cols;
	}

	/**
	 * Get the Export Data
	 *
	 * @access public
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
				'post_parent'  => absint( $_POST['edd_export_download'] ),
				'log_type'     => 'sale',
				'nopaging'    => true
			);

			$logs = $edd_logs->get_connected_logs( $args );

			if ( $logs ) {
				foreach ( $logs as $log ) {
					$payment_id = get_post_meta( $log->ID, '_edd_log_payment_id', true );
					$email      = edd_get_payment_user_email( $payment_id );

					$wp_user = get_user_by( 'email', $email );

					$data[] = array(
						'name'      => $wp_user ? $wp_user->display_name : __( 'Guest', 'edd' ),
						'email'     => $email,
						'date'      => $log->post_date
					);
				}
			}
		} else {
			// Export all customers
			$emails = $wpdb->get_col( "SELECT DISTINCT meta_value FROM $wpdb->postmeta WHERE meta_key = '_edd_payment_user_email' " );

			$i = 0;

			foreach ( $emails as $email ) {

				if( 'emails' != $_POST['edd_export_option'] ) {
					$wp_user = get_user_by( 'email', $email );
					$data[$i]['name'] = $wp_user ? $wp_user->display_name : __( 'Guest', 'edd' );
				}

				$data[$i]['email'] = $email;

				if( 'full' == $_POST['edd_export_option'] ) {
					$stats = edd_get_purchase_stats_by_user( $email );
					$data[$i]['purchases'] = $stats['purchases'];
					$data[$i]['amount']    = edd_format_amount( $stats['total_spent'] );
				}
				$i++;
			}
		}

		$data = apply_filters( 'edd_export_get_data', $data );
		$data = apply_filters( 'edd_export_get_data_' . $this->export_type, $data );

		return $data;
	}
}