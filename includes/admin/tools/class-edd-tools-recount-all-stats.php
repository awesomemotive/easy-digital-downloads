<?php
/**
 * Recount download earnings and stats AND store earnings
 *
 * This class handles batch processing of recounting earnings and stats for all downloads and store totals
 *
 * @subpackage  Admin/Tools/EDD_Tools_Recount_All_Stats
 * @copyright   Copyright (c) 2015, Chris Klosowski
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       2.5
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * EDD_Tools_Recount_All_Stats Class
 *
 * @since 2.5
 */
class EDD_Tools_Recount_All_Stats extends EDD_Batch_Export {

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

		$totals             = get_option( 'edd_temp_recount_all_stats' , false );
		$payment_items      = get_option( 'edd_temp_payment_items'     , false );
		$processed_payments = get_option( 'edd_temp_processed_payments', false );

		if ( false === $totals ) {
			$totals = array();
			add_option( 'edd_temp_recount_all_stats', $totals, '', 'no' );
		}

		if ( false === $payment_items ) {
			$payment_items = array();
			add_option( 'edd_temp_payment_items', $payment_items, '', 'no' );
		}

		if ( false === $processed_payments ) {
			$processed_payments = array();
			add_option( 'edd_temp_processed_payments', $processed_payments, '', 'no' );
		}

		$all_downloads = get_option( 'edd_temp_download_ids' );

		$args = apply_filters( 'edd_recount_download_stats_args', array(
			'post_parent'    => $all_downloads,
			'post_type'      => 'edd_log',
			'posts_per_page' => $this->per_step,
			'post_status'    => 'publish',
			'paged'          => $this->step,
			'log_type'       => 'sale',
			'fields'         => 'ids',
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

				// Prevent payments that have all ready been retrieved from a previous sales log from counting again.
				if ( in_array( $payment->ID, $processed_payments ) ) {
					continue;
				}

				if ( in_array( $payment->post_status, array( 'revoked', 'published', 'edd_subscription' ) ) ) {
					$processed_payments[] = $payment->ID;
					continue;
				}

				$items = $payment_items[ $payment->ID ];

				foreach ( $items as $item ) {
					$download_id = $item['id'];

					if ( ! in_array( $download_id, $all_downloads ) ) {
						continue;
					}

					if ( ! array_key_exists( $download_id, $totals ) ) {
						$totals[ $download_id ] = array(
							'sales'    => (int) 0,
							'earnings' => (float) 0,
						);
					}

					$totals[ $download_id ]['sales']++;
					$totals[ $download_id ]['earnings'] += $item['price'];

					if ( ! array_key_exists( 'edd_earnings_total', $totals ) ) {
						$totals['edd_earnings_total'] = $item['price'];
					} else {
						$totals['edd_earnings_total'] += $item['price'];
					}

				}

				$processed_payments[] = $payment->ID;
			}

			update_option( 'edd_temp_processed_payments', $processed_payments );
			update_option( 'edd_temp_recount_all_stats', $totals );

			return true;
		}

		foreach ( $totals as $key => $stats ) {
			if ( is_numeric( $key ) ) {
				update_post_meta( $key, '_edd_download_sales'   , $stats['sales'] );
				update_post_meta( $key, '_edd_download_earnings', $stats['earnings'] );
			} else {
				update_option( $key, $stats );
				set_transient( $key, $stats, 86400 );
			}
		}

		return false;

	}

	/**
	 * Return the calculated completion percentage
	 *
	 * @since 2.5
	 * @return int
	 */
	public function get_percentage_complete() {

		$total = get_option( 'edd_recount_all_total', false );

		if ( false === $total ) {
			$this->pre_fetch();
			$total = get_option( 'edd_recount_all_total', 0 );
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
			delete_option( 'edd_recount_all_total' );
			delete_option( 'edd_temp_recount_all_stats' );
			delete_option( 'edd_temp_payment_items' );
			delete_option( 'edd_temp_download_ids' );
			delete_option( 'edd_temp_processed_payments' );
			$this->done    = true;
			$this->message = __( 'Earnings and sales stats successfully recounted.', 'edd' );
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

	public function pre_fetch() {
		global $edd_logs, $wpdb;

		if ( $this->step == 1 ) {
			delete_option( 'edd_temp_recount_all_total' );
			delete_option( 'edd_temp_recount_all_stats' );
			delete_option( 'edd_temp_payment_items' );
			delete_option( 'edd_temp_processed_payments' );
		}

		$total   = get_option( 'edd_temp_recount_all_total', false );

		if ( false === $total ) {
			$total         = 0;
			$payment_items = get_option( 'edd_temp_payment_items', false );

			if ( false === $payment_items ) {
				$payment_items = array();
				add_option( 'edd_temp_payment_items', $payment_items, '', 'no' );
			}

			$all_downloads = get_option( 'edd_temp_download_ids', false );

			if ( false === $all_downloads ) {
				$args = array(
					'post_status'    => 'any',
					'post_type'      => 'download',
					'posts_per_page' => -1,
					'fields'         => 'ids',
				);

				$all_downloads = get_posts( $args );
				add_option( 'edd_temp_download_ids', $all_downloads, '', 'no' );
			}

			$args  = apply_filters( 'edd_recount_download_stats_total_args', array(
				'post_parent'    => $all_downloads,
				'post_type'      => 'edd_log',
				'post_status'    => 'publish',
				'log_type'       => 'sale',
				'fields'         => 'ids',
				'nopaging'       => true,
			) );

			$all_logs = $edd_logs->get_connected_logs( $args, 'sale' );

			if ( $all_logs ) {
				$log_ids     = implode( ',', $all_logs );
				$payment_ids = $wpdb->get_col( "SELECT meta_value FROM $wpdb->postmeta WHERE meta_key='_edd_log_payment_id' AND post_id IN ($log_ids)" );
				unset( $log_ids );

				$payment_ids = implode( ',', $payment_ids );
				$payments = $wpdb->get_results( "SELECT ID, post_status FROM $wpdb->posts WHERE ID IN (" . $payment_ids . ")" );
				unset( $payment_ids );

				foreach ( $payments as $payment ) {
					if ( in_array( $payment->post_status, array( 'revoked', 'published', 'edd_subscription' ) ) ) {
						continue;
					}

					if ( ! array_key_exists( $payment->ID, $payment_items ) ) {

						$items = edd_get_payment_meta_cart_details( $payment->ID );
						$payment_items[ $payment->ID ] = $items;

					}

				}

				$total = count( $all_logs );
			}

			update_option( 'edd_temp_payment_items', $payment_items );
			update_option( 'edd_recount_all_total' , $total );
		}

	}

}
