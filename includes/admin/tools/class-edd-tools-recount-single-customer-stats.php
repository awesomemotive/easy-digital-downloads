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
	 * @since 2.5
	 * @global object $wpdb Used to query the database using the WordPress
	 *   Database API
	 * @return array $data The data for the CSV file
	 */
	public function get_data() {

		$customer = new EDD_Customer( $this->customer_id );
		$payments = $this->get_stored_data( 'edd_recount_customer_payments_' . $customer->id, array() );

		$offset     = ( $this->step - 1 ) * $this->per_step;
		$step_items = array_slice( $payments, $offset, $this->per_step );

		if ( count( $step_items ) > 0 ) {
			$pending_total = (float) $this->get_stored_data( 'edd_stats_customer_pending_total' . $customer->id, 0 );
			$step_total    = 0;

			$found_payment_ids = $this->get_stored_data( 'edd_stats_found_payments_' . $customer->id, array() );

			foreach ( $step_items as $payment ) {
				$payment = get_post( $payment->ID );

				if ( is_null( $payment ) || is_wp_error( $payment ) || 'edd_payment' !== $payment->post_type ) {

					$missing_payments   = $this->get_stored_data( 'edd_stats_missing_payments' . $customer->id, array() );
					$missing_payments[] = $payment->ID;
					$this->store_data( 'edd_stats_missing_payments' . $customer->id, $missing_payments );

					continue;
				}

				$should_process_payment = 'publish' == $payment->post_status || 'revoked' == $payment->post_status ? true : false;
				$should_process_payment = apply_filters( 'edd_customer_recount_should_process_payment', $should_process_payment, $payment );

				if( true === $should_process_payment ) {

					$found_payment_ids[] = $payment->ID;

					if ( apply_filters( 'edd_customer_recount_sholud_increase_value', true, $payment ) ) {
						$payment_amount      = edd_get_payment_amount( $payment->ID );
						$step_total         += $payment_amount;
					}

				}

			}

			$updated_total = $pending_total + $step_total;
			$this->store_data( 'edd_stats_customer_pending_total' . $customer->id, $updated_total );
			$this->store_data( 'edd_stats_found_payments_' . $customer->id, $found_payment_ids );

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

		$payments = $this->get_stored_data( 'edd_recount_customer_payments_' . $this->customer_id, array() );
		$total       = count( $payments );

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
			$payment_ids      = $this->get_stored_data( 'edd_stats_found_payments_' . $customer->id, array() );
			$this->delete_data( 'edd_stats_found_payments_' . $customer->id );

			$removed_payments = array_unique( $this->get_stored_data( 'edd_stats_missing_payments' . $customer->id, array() ) );

			// Find non-existing payments (deleted) and total up the purchase count
			$purchase_count   = 0;
			foreach( $payment_ids as $key => $payment_id ) {
				if ( in_array( $payment_id, $removed_payments ) ) {
					unset( $payment_ids[ $key ] );
					continue;
				}

				$payment = get_post( $payment_id );
				if ( apply_filters( 'edd_customer_recount_sholud_increase_count', true, $payment ) ) {
					$purchase_count++;
				}
			}

			$this->delete_data( 'edd_stats_missing_payments' . $customer->id );

			$pending_total = $this->get_stored_data( 'edd_stats_customer_pending_total' . $customer->id, 0 );
			$this->delete_data( 'edd_stats_customer_pending_total' . $customer->id );
			$this->delete_data( 'edd_recount_customer_stats_' . $customer->id );
			$this->delete_data( 'edd_recount_customer_payments_' . $this->customer_id );

			$payment_ids    = implode( ',', $payment_ids );
			$customer->update( array( 'payment_ids' => $payment_ids, 'purchase_count' => $purchase_count, 'purchase_value' => $pending_total ) );

			$this->done    = true;
			$this->message = __( 'Customer stats successfully recounted.', 'easy-digital-downloads' );
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
	 * Zero out the data on step one
	 *
	 * @since 2.5
	 * @return void
	 */
	public function pre_fetch() {
		if ( $this->step === 1 ) {
			$allowed_payment_status = apply_filters( 'edd_recount_customer_payment_statuses', edd_get_payment_status_keys() );

			// Before we start, let's zero out the customer's data
			$customer = new EDD_Customer( $this->customer_id );
			$customer->update( array( 'purchase_value' => edd_format_amount( 0 ), 'purchase_count' => 0 ) );

			$attached_payment_ids = explode( ',', $customer->payment_ids );

			$attached_args = array(
				'post__in' => $attached_payment_ids,
				'number'   => -1,
				'status'   => $allowed_payment_status,
			);

			$attached_payments = edd_get_payments( $attached_args );

			$unattached_args = array(
				'post__not_in' => $attached_payment_ids,
				'number'       => -1,
				'status'       => $allowed_payment_status,
				'meta_query'   => array(
					array(
						'key'   => '_edd_payment_user_email',
						'value' => $customer->email,
					)
				),
			);

			$unattached_payments = edd_get_payments( $unattached_args );

			$payments = array_merge( $attached_payments, $unattached_payments );

			$this->store_data( 'edd_recount_customer_payments_' . $customer->id, $payments );
		}
	}

	/**
	 * Given a key, get the information from the Database Directly
	 *
	 * @since  2.5
	 * @param  string $key The option_name
	 * @return mixed       Returns the data from the database
	 */
	private function get_stored_data( $key, $default = false ) {
		global $wpdb;
		$value = $wpdb->get_var( $wpdb->prepare( "SELECT option_value FROM $wpdb->options WHERE option_name = '%s'", $key ) );

		if ( empty( $value ) ) {
			return $default;
		}

		$maybe_json = json_decode( $value );
		if ( ! is_null( $maybe_json ) && ! is_numeric( $value ) ) {
			$value = $maybe_json;
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
