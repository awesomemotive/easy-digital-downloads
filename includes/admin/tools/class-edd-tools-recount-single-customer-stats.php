<?php
/**
 * Recount all cutomer stats
 *
 * This class handles batch processing of recounting a single customer's stats
 *
 * @subpackage  Admin/Tools/EDD_Tools_Recount_Customer_Stats
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
class EDD_Tools_Recount_Single_Customer_Stats extends EDD_Batch_Export {

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
	public $per_step = 10;

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

		$customer    = new EDD_Customer( $this->customer_id );
		$payment_ids = explode( ',', $customer->payment_ids );

		$offset     = ( $this->step - 1 ) * $this->per_step;
		$step_items = array_slice( $payment_ids, $offset, $this->per_step );

		if ( count( $step_items ) > 0 ) {
			$pending_total = (float) get_option( 'edd_stats_customer_pending_total' . $customer->id, 0 );
			$step_total    = 0;

			foreach ( $step_items as $payment_id ) {
				$payment = get_post( $payment_id );

				if ( is_null( $payment ) || is_wp_error( $payment ) || 'edd_payment' !== $payment->post_type ) {

					$missing_payments   = get_option( 'edd_stats_missing_payments' . $customer->id, array() );
					$missing_payments[] = $payment_id;
					update_option( 'edd_stats_missing_payments' . $customer->id, $missing_payments );

					continue;
				}

				$payment_amount  = edd_get_payment_amount( $payment_id );
				$step_total     += $payment_amount;

			}

			$updated_total = $pending_total + $step_total;
			update_option( 'edd_stats_customer_pending_total' . $customer->id, $updated_total );

			return true;
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

		$customer    = new EDD_Customer( $this->customer_id );
		$payment_ids = explode( ',', $customer->payment_ids );
		$total       = count( $payment_ids );

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
		$this->customer_id = isset( $request['customer_id'] ) ? sanitize_text_field( $request['customer_id'] ) : false;
	}

	/**
	 * Process a step
	 *
	 * @since 2.5
	 * @return bool
	 */
	public function process_step() {

		if ( ! $this->can_export() ) {
			wp_die( __( 'You do not have permission to modify this data.', 'easy-digital-downloads' ), __( 'Error', 'easy-digital-downloads' ), array( 'response' => 403 ) );
		}

		$had_data = $this->get_data();

		if( $had_data ) {
			$this->done = false;
			return true;
		} else {
			$customer         = new EDD_Customer( $this->customer_id );
			$payment_ids      = explode( ',', $customer->payment_ids );
			$removed_payments = array_unique( get_option( 'edd_stats_missing_payments' . $customer->id, array() ) );

			if ( ! empty( $removed_payments ) ) {

				foreach( $payment_ids as $key => $payment_id ) {
					if ( in_array( $payment_id, $removed_payments ) ) {
						unset( $payment_ids[ $key ] );
					}
				}

				delete_option( 'edd_stats_missing_payments' . $customer->id );
			}

			$pending_total = get_option( 'edd_stats_customer_pending_total' . $customer->id, 0 );
			delete_option( 'edd_stats_customer_pending_total' . $customer->id );

			$purchase_count = count( $payment_ids );
			$payment_ids    = implode( ',', $payment_ids );
			$customer->update( array( 'payment_ids' => $payment_ids, 'purchase_count' => $purchase_count, 'purchase_value' => $pending_total ) );

			$this->done    = true;
			$this->message = __( 'Customer stats successfully recounted.', 'easy-digital-downloads' );
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

	/**
	 * Zero out the data on step one
	 *
	 * @access public
	 * @since 2.5
	 * @return void
	 */
	public function pre_fetch() {
		if ( $this->step === 1 ) {
			// Before we start, let's zero out the customer's data
			$customer = new EDD_Customer( $this->customer_id );
			$customer->update( array( 'purchase_value' => edd_format_amount( 0 ), 'purchase_count' => 0 ) );
		}
	}

}
