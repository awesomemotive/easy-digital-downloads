<?php
/**
 * Recount download earnings and stats
 *
 * This class handles batch processing of recounting earnings and stats
 *
 * @subpackage  Admin/Tools/EDD_Tools_Recount_Stats
 * @copyright   Copyright (c) 2015, Chris Klosowski
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       2.5
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * EDD_Tools_Recount_Stats Class
 *
 * @since 2.5
 */
class EDD_Tools_Recount_Download_Stats extends EDD_Batch_Export {

	/**
	 * Our export type. Used for export-type specific filters/actions
	 * @var string
	 * @since 2.5
	 */
	public $export_type = '';

	/**
	 * Allows for a non-download batch processing to be run.
	 * @since  2.5
	 * @var boolean
	 */
	public $is_void = true;

	/**
	 * Sets the number of items to pull on each step
	 * @since  2.5
	 * @var integer
	 */
	public $per_step = 30;

	/**
	 * Get the Export Data
	 *
	 * @since 2.5
	 * @global object $wpdb Used to query the database using the WordPress
	 *   Database API
	 * @return array $data The data for the CSV file
	 */
	public function get_data() {
		global $edd_logs, $wpdb;

		$accepted_statuses  = apply_filters( 'edd_recount_accepted_statuses', array( 'publish', 'revoked' ) );

		if ( $this->step == 1 ) {
			$this->delete_data( 'edd_temp_recount_download_stats' );
		}

		$totals = $this->get_stored_data( 'edd_temp_recount_download_stats' );

		if ( false === $totals ) {
			$totals = array(
				'earnings' => (float) 0,
				'sales'    => 0,
			);
			$this->store_data( 'edd_temp_recount_download_stats', $totals );
		}

		$args = apply_filters( 'edd_recount_download_stats_args', array(
			'post_parent'    => $this->download_id,
			'post_type'      => 'edd_log',
			'posts_per_page' => $this->per_step,
			'post_status'    => 'publish',
			'paged'          => $this->step,
			'log_type'       => 'sale',
			'fields'         => 'ids',
		) );

		$log_ids = $edd_logs->get_connected_logs( $args, 'sale' );
		$this->_log_ids_debug = array();
		if ( $log_ids ) {
			$log_ids     = implode( ',', $log_ids );
			$payment_ids = $wpdb->get_col( "SELECT meta_value FROM $wpdb->postmeta WHERE meta_key='_edd_log_payment_id' AND post_id IN ($log_ids)" );
			unset( $log_ids );

			$payment_ids = implode( ',', $payment_ids );
			$payments = $wpdb->get_results( "SELECT ID, post_status FROM $wpdb->posts WHERE ID IN (" . $payment_ids . ")" );
			unset( $payment_ids );

			foreach ( $payments as $payment ) {

				$payment = new EDD_Payment( $payment->ID );

				if ( ! in_array( $payment->post_status, $accepted_statuses ) ) {
					continue;
				}

				$items = edd_get_payment_meta_cart_details( $payment->ID );

				foreach ( $items as $item ) {

					if ( $item['id'] != $this->download_id ) {
						continue;
					}

					$this->_log_ids_debug[] = $payment->ID;

					/*
					 * Prior to version 2.9.9, there was a bug with item-specific earnings.
					 *
					 * To get around it, we will use alternate calculation logic if the payment was created before version 2.9.9 was released.
					 *
					 * This is not a perfect fix but is as close as we can get to accurate reporting.
					 *
					 * See https://github.com/easydigitaldownloads/easy-digital-downloads/issues/7507
					 */
					if ( $payment->date > '2018-12-03' ) {

						$amount = $item['price'];

					} else {

						$amount = $item['price'];

						if ( ! empty( $item['fees'] ) ) {
							foreach( $item['fees'] as $fee ) {
								// Only let negative fees affect earnings
								if ( $fee['amount'] > 0 ) {
									continue;
								}

								$amount += $fee['amount'];
							}
						}

					}

					$totals['sales']++;
					$totals['earnings'] += $amount;

				}

			}

			$this->store_data( 'edd_temp_recount_download_stats', $totals );

			return true;
		}


		update_post_meta( $this->download_id, '_edd_download_sales'   , $totals['sales'] );
		update_post_meta( $this->download_id, '_edd_download_earnings', $totals['earnings'] );

		return false;

	}

	/**
	 * Return the calculated completion percentage
	 *
	 * @since 2.5
	 * @return int
	 */
	public function get_percentage_complete() {
		global $edd_logs, $wpdb;

		if ( $this->step == 1 ) {
			$this->delete_data( 'edd_recount_total_' . $this->download_id );
		}

		$accepted_statuses  = apply_filters( 'edd_recount_accepted_statuses', array( 'publish', 'revoked' ) );
		$total   = $this->get_stored_data( 'edd_recount_total_' . $this->download_id );

		if ( false === $total ) {
			$total = 0;
			$args  = apply_filters( 'edd_recount_download_stats_total_args', array(
				'post_parent'    => $this->download_id,
				'post_type'      => 'edd_log',
				'post_status'    => 'publish',
				'log_type'       => 'sale',
				'fields'         => 'ids',
				'nopaging'       => true,
			) );

			$log_ids = $edd_logs->get_connected_logs( $args, 'sale' );

			if ( $log_ids ) {
				$log_ids     = implode( ',', $log_ids );
				$payment_ids = $wpdb->get_col( "SELECT meta_value FROM $wpdb->postmeta WHERE meta_key='_edd_log_payment_id' AND post_id IN ($log_ids)" );
				unset( $log_ids );

				$payment_ids = implode( ',', $payment_ids );
				$payments = $wpdb->get_results( "SELECT ID, post_status FROM $wpdb->posts WHERE ID IN (" . $payment_ids . ")" );
				unset( $payment_ids );

				foreach ( $payments as $payment ) {
					if ( in_array( $payment->post_status, $accepted_statuses ) ) {
						continue;
					}

					$total++;
				}
			}

			$this->store_data( 'edd_recount_total_' . $this->download_id, $total );
		}

		$percentage = 100;

		if( $total > 0 ) {
			$percentage = ( ( $this->per_step * $this->step ) / $total ) * 100;
		}

		if( $percentage > 100 ) {
			$percentage = 100;
		}

		return $percentage;
	}

	/**
	 * Set the properties specific to the payments export
	 *
	 * @since 2.5
	 * @param array $request The Form Data passed into the batch processing
	 */
	public function set_properties( $request ) {
		$this->download_id = isset( $request['download_id'] ) ? sanitize_text_field( $request['download_id'] ) : false;
	}

	/**
	 * Process a step
	 *
	 * @since 2.5
	 * @return bool
	 */
	public function process_step() {

		if ( ! $this->can_export() ) {
			wp_die( __( 'You do not have permission to export data.', 'easy-digital-downloads' ), __( 'Error', 'easy-digital-downloads' ), array( 'response' => 403 ) );
		}

		$had_data = $this->get_data();

		if( $had_data ) {
			$this->done = false;
			return true;
		} else {
			$this->delete_data( 'edd_recount_total_' . $this->download_id );
			$this->delete_data( 'edd_temp_recount_download_stats' );
			$this->done    = true;
			$this->message = sprintf( __( 'Earnings and sales stats successfully recounted for %s.', 'easy-digital-downloads' ), get_the_title( $this->download_id ) );
			return false;
		}
	}

	public function headers() {
		ignore_user_abort( true );

		if ( ! edd_is_func_disabled( 'set_time_limit' ) ) {
			set_time_limit( 0 );
		}
	}

	/**
	 * Perform the export
	 *
	 * @since 2.5
	 * @return void
	 */
	public function export() {

		// Set headers
		$this->headers();

		edd_die();
	}

	/**
	 * Given a key, get the information from the Database Directly
	 *
	 * @since  2.5
	 * @param  string $key The option_name
	 * @return mixed       Returns the data from the database
	 */
	private function get_stored_data( $key ) {
		global $wpdb;
		$value = $wpdb->get_var( $wpdb->prepare( "SELECT option_value FROM $wpdb->options WHERE option_name = '%s'", $key ) );

		if ( empty( $value ) ) {
			return false;
		}

		$maybe_json = json_decode( $value );
		if ( ! is_null( $maybe_json ) ) {
			$value = json_decode( $value, true );
		}

		return $value;
	}

	/**
	 * Give a key, store the value
	 *
	 * @since  2.5
	 * @param  string $key   The option_name
	 * @param  mixed  $value  The value to store
	 * @return void
	 */
	private function store_data( $key, $value ) {
		global $wpdb;

		$value = is_array( $value ) ? wp_json_encode( $value ) : esc_attr( $value );

		$data = array(
			'option_name'  => $key,
			'option_value' => $value,
			'autoload'     => 'no',
		);

		$formats = array(
			'%s', '%s', '%s',
		);

		$wpdb->replace( $wpdb->options, $data, $formats );
	}

	/**
	 * Delete an option
	 *
	 * @since  2.5
	 * @param  string $key The option_name to delete
	 * @return void
	 */
	private function delete_data( $key ) {
		global $wpdb;
		$wpdb->delete( $wpdb->options, array( 'option_name' => $key ) );
	}

}
