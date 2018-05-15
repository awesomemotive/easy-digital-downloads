<?php
/**
 * Migrate File Download Logs
 *
 * Removes some PII from the log meta, and adds the customer ID of the payment to allow more accurate
 * file download counts for a customer.
 *
 * @subpackage  Admin/Classes/EDD_SL_License_Log_Migration
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       2.6.2
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * EDD_File_Download_Log_Migration Class
 *
 * @since 2.9.2
 */
class EDD_File_Download_Log_Migration extends EDD_Batch_Export {

	/**
	 * Our export type. Used for export-type specific filters/actions
	 * @var string
	 * @since 2.9.2
	 */
	public $export_type = '';

	/**
	 * Allows for a non-download batch processing to be run.
	 * @since  2.9.2
	 * @var boolean
	 */
	public $is_void = true;

	/**
	 * Sets the number of items to pull on each step
	 * @since  2.9.2
	 * @var integer
	 */
	public $per_step = 50;

	/**
	 * Get the Export Data
	 *
	 * @access public
	 * @since 2.9.2
	 * @global object $wpdb Used to query the database using the WordPress
	 *   Database API
	 * @return array $data The data for the CSV file
	 */
	public function get_data() {

		global $wpdb;

		$items = $this->get_stored_data( 'edd_file_download_log_ids' );

		if ( ! is_array( $items ) ) {
			return false;
		}

		$offset     = ( $this->step - 1 ) * $this->per_step;
		$step_items = array_slice( $items, $offset, $this->per_step, true );

		if ( $step_items ) {

			foreach ( $step_items as $log_id ) {

				$log_id           = (int) $log_id['object_id'];
				$sanitized_log_id = absint( $log_id );

				if ( $sanitized_log_id !== $log_id ) {
					edd_debug_log( "Log ID mismatch, skipping log ID {$log_id}" );
					continue;
				}

				$has_customer_id = get_post_meta( $log_id, '_edd_log_customer_id', true );
				if ( ! empty( $has_customer_id ) ) {
					continue;
				}

				$payment_id = $wpdb->get_var( "SELECT meta_value FROM {$wpdb->postmeta} WHERE post_id = {$sanitized_log_id} AND meta_key = '_edd_log_payment_id'" );
				if ( ! empty( $payment_id ) ) {
					$payment     = edd_get_payment( $payment_id );
					$customer_id = $payment->customer_id;

					if ( $customer_id < 0 ) {
						$customer_id = 0;
					}

					update_post_meta( $log_id, '_edd_log_customer_id', $customer_id );
					delete_post_meta( $log_id, '_edd_log_user_info' );

				}

			}

			return true;

		}

		return false;

	}

	/**
	 * Return the calculated completion percentage
	 *
	 * @since 2.9.2
	 * @return int
	 */
	public function get_percentage_complete() {

		$items = $this->get_stored_data( 'edd_file_download_log_ids', false );
		$total = count( $items );

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
	 * @since 2.9.2
	 * @param array $request The Form Data passed into the batch processing
	 */
	public function set_properties( $request ) {}

	/**
	 * Process a step
	 *
	 * @since 2.9.2
	 * @return bool
	 */
	public function process_step() {

		if ( ! $this->can_export() ) {
			wp_die(
				__( 'You do not have permission to run this upgrade.', 'easy-digital-downloads' ),
				__( 'Error', 'easy-digital-downloads' ),
				array( 'response' => 403 ) );
		}

		$had_data = $this->get_data();

		if( $had_data ) {
			$this->done = false;
			return true;
		} else {
			$this->delete_data( 'edd_file_download_log_ids' );

			$this->done    = true;
			$this->message = __( 'File download logs updated successfully.', 'easy-digital-downloads' );
			edd_set_upgrade_complete( 'update_file_download_log_data' );
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
	 * @access public
	 * @since 2.9.2
	 * @return void
	 */
	public function export() {

		// Set headers
		$this->headers();

		edd_die();
	}

	public function pre_fetch() {
		global $wpdb;

		// Get all the file download logs
		if ( $this->step == 1 ) {
			$this->delete_data( 'edd_file_download_log_ids' );

			$term_id     = $wpdb->get_var( "SELECT term_id FROM {$wpdb->terms} WHERE name = 'file_download'" );
			$term_tax_id = $wpdb->get_var( "SELECT term_taxonomy_id FROM {$wpdb->term_taxonomy} WHERE term_id = {$term_id} AND taxonomy = 'edd_log_type'" );
			$log_ids     = $wpdb->get_results( "SELECT object_id FROM {$wpdb->term_relationships} WHERE term_taxonomy_id = {$term_tax_id}" );

			$this->store_data( 'edd_file_download_log_ids', $log_ids );

		}

	}

	/**
	 * Given a key, get the information from the Database Directly
	 *
	 * @since  2.9.2
	 * @param  string $key The option_name
	 * @return mixed       Returns the data from the database
	 */
	private function get_stored_data( $key ) {
		global $wpdb;
		$value = $wpdb->get_var(
			$wpdb->prepare( "SELECT option_value FROM $wpdb->options WHERE option_name = '%s'", $key )
		);

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
	 * @since  2.9.2
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
	 * @since  2.9.2
	 * @param  string $key The option_name to delete
	 * @return void
	 */
	private function delete_data( $key ) {
		global $wpdb;
		$wpdb->delete( $wpdb->options, array( 'option_name' => $key ) );
	}

}
