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
	 * @access public
	 * @since 2.5
	 * @global object $wpdb Used to query the database using the WordPress
	 *   Database API
	 * @return array $data The data for the CSV file
	 */
	public function get_data() {
		global $edd_logs, $wpdb;

		if ( $this->step == 1 ) {
			delete_option( 'edd_temp_recount_download_stats' );
		}

		$totals = get_option( 'edd_temp_recount_download_stats', false );

		if ( false === $totals ) {
			$totals = array(
				'earnings' => (float) 0,
				'sales'    => 0,
			);
			add_option( 'edd_temp_recount_download_stats', $totals, '', 'no' );
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
				if ( in_array( $payment->post_status, array( 'revoked', 'published', 'edd_subscription' ) ) ) {
					continue;
				}

				$items = edd_get_payment_meta_cart_details( $payment->ID );

				foreach ( $items as $item ) {
					if ( $item['id'] != $this->download_id ) {
						continue;
					}
					$this->_log_ids_debug[] = $payment->ID;

					$totals['sales']++;
					$totals['earnings'] += $item['price'];
				}
			}

			update_option( 'edd_temp_recount_download_stats', $totals );

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
			delete_option( 'edd_recount_total_' . $this->download_id );
		}

		$total   = get_option( 'edd_recount_total_' . $this->download_id, false );

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
					if ( in_array( $payment->post_status, array( 'revoked', 'published', 'edd_subscription' ) ) ) {
						continue;
					}

					$total++;
				}
			}

			add_option( 'edd_recount_total_' . $this->download_id, $total, '', 'no' );
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
			wp_die( __( 'You do not have permission to export data.', 'edd' ), __( 'Error', 'edd' ), array( 'response' => 403 ) );
		}

		$had_data = $this->get_data();

		if( $had_data ) {
			$this->done = false;
			return true;
		} else {
			delete_option( 'edd_recount_total_' . $this->download_id );
			delete_option( 'edd_temp_recount_download_stats' );
			$this->done    = true;
			$this->message = sprintf( __( 'Earnings and sales stats successfully recounted for %s.', 'edd' ), get_the_title( $this->download_id ) );
			return false;
		}
	}

	public function headers() {
		ignore_user_abort( true );

		if ( ! edd_is_func_disabled( 'set_time_limit' ) && ! ini_get( 'safe_mode' ) ) {
			set_time_limit( 0 );
		}
	}

	/**
	 * Perform the export
	 *
	 * @access public
	 * @since 2.5
	 * @return void
	 */
	public function export() {

		// Set headers
		$this->headers();

		edd_die();
	}

}
