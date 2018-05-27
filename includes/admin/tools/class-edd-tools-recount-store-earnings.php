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
	 * @since 2.5
	 * @global object $wpdb Used to query the database using the WordPress
	 *   Database API
	 * @return array $data The data for the CSV file
	 */
	public function get_data() {

		if ( $this->step == 1 ) {
			$this->delete_data( 'edd_temp_recount_earnings' );
		}

		$total = get_option( 'edd_temp_recount_earnings', false );

		if ( false === $total ) {
			$total = (float) 0;
			$this->store_data( 'edd_temp_recount_earnings', $total );
		}

		$accepted_statuses  = apply_filters( 'edd_recount_accepted_statuses', array( 'publish', 'revoked' ) );

		$args = apply_filters( 'edd_recount_earnings_args', array(
			'number' => $this->per_step,
			'page'   => $this->step,
			'status' => $accepted_statuses,
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

			$this->store_data( 'edd_temp_recount_earnings', $total );

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

		$total = $this->get_stored_data( 'edd_recount_earnings_total' );

		if ( false === $total ) {
			$args = apply_filters( 'edd_recount_earnings_total_args', array() );

			$counts = edd_count_payments( $args );
			$total  = absint( $counts->publish ) + absint( $counts->revoked );
			$total  = apply_filters( 'edd_recount_store_earnings_total', $total );

			$this->store_data( 'edd_recount_earnings_total', $total );
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
			wp_die( __( 'You do not have permission to export data.', 'easy-digital-downloads' ), __( 'Error', 'easy-digital-downloads' ), array( 'response' => 403 ) );
		}

		$had_data = $this->get_data();

		if( $had_data ) {
			$this->done = false;
			return true;
		} else {
			delete_transient( 'edd_stats_earnings' );
			delete_transient( 'edd_stats_sales' );
			delete_transient( 'edd_estimated_monthly_stats' . true );
			delete_transient( 'edd_estimated_monthly_stats' . false );

			$this->delete_data( 'edd_recount_earnings_total' );
			$this->delete_data( 'edd_temp_recount_earnings' );
			$this->done    = true;
			$this->message = __( 'Store earnings successfully recounted.', 'easy-digital-downloads' );
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
