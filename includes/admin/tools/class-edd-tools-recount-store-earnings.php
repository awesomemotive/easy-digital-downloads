<?php
/**
 * Recount store earnings
 *
 * This class handles batch processing of recounting earnings
 *
 * @subpackage  Admin/Tools/EDD_Tools_Recount_Store_Earnings
 * @copyright   Copyright (c) 2015, Chris Klosowski
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       2.5
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * EDD_Tools_Recount_Store_Earnings Class
 *
 * @since 2.5
 */
class EDD_Tools_Recount_Store_Earnings extends EDD_Batch_Export {

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
	public $per_step = 100;

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

		if ( $this->step == 1 ) {
			delete_option( 'edd_temp_recount_earnings' );
		}

		$total = get_option( 'edd_temp_recount_earnings', false );

		if ( false === $total ) {
			$total = (float) 0;
			add_option( 'edd_temp_recount_earnings', $total, '', 'no' );
		}

		$args = apply_filters( 'edd_recount_earnings_args', array(
			'number' => $this->per_step,
			'page'   => $this->step,
			'status' => array( 'publish', 'revoked', 'edd_subscription' ),
			'fields' => 'ids'
		) );

		$payments = edd_get_payments( $args );

		if ( ! empty( $payments ) ) {

			foreach ( $payments as $payment ) {

				$total += edd_get_payment_amount( $payment );

			}

			if ( $total < 0 ) {
				$totals = 0;
			}

			$total = round( $total, edd_currency_decimal_filter() );

			update_option( 'edd_temp_recount_earnings', $total );

			return true;

		}

		update_option( 'edd_earnings_total', $total );
		set_transient( 'edd_earnings_total', $total, 86400 );

		return false;

	}

	/**
	 * Return the calculated completion percentage
	 *
	 * @since 2.5
	 * @return int
	 */
	public function get_percentage_complete() {

		$total = get_option( 'edd_recount_earnings_total', false );

		if ( false === $total ) {
			$args = apply_filters( 'edd_recount_earnings_total_args', array() );

			$counts = edd_count_payments( $args );
			$total  = absint( $counts->publish ) + absint( $counts->revoked );
			if ( ! empty( $counts->edd_subscription ) ) {
				$total += $counts->edd_subscription;
			}

			add_option( 'edd_recount_earnings_total', $total, '', 'no' );
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
	public function set_properties( $request ) {}

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
			delete_option( 'edd_recount_earnings_total' );
			delete_option( 'edd_temp_recount_earnings' );
			$this->done    = true;
			$this->message = __( 'Store earnings successfully recounted.', 'edd' );
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
