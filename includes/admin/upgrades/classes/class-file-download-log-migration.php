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
 * @since       2.9.2
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

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
	 * @return array $data The data for the CSV file
	 */
	public function get_data() {
		$step_items = $this->get_log_ids_for_current_step();

		if ( ! is_array( $step_items ) ) {
			return false;
		}

		if ( empty( $step_items ) ) {
			return false;
		}

		foreach ( $step_items as $log_id ) {
			$log_id           = (int) $log_id;
			$sanitized_log_id = absint( $log_id );

			if ( $sanitized_log_id !== $log_id ) {
				edd_debug_log( "Log ID mismatch, skipping log ID {$log_id}" );
				continue;
			}

			$has_customer_id = (int) get_post_meta( $log_id, '_edd_log_customer_id', true );
			if ( ! empty( $has_customer_id ) ) {
				continue;
			}

			$payment_id = (int) get_post_meta( $log_id, '_edd_log_payment_id', true );
			if ( ! empty( $payment_id ) ) {
				$customer_id = edd_get_payment_customer_id( $payment_id );

				if ( $customer_id < 0 ) {
					$customer_id = 0;
				}

				update_post_meta( $log_id, '_edd_log_customer_id', $customer_id );
				delete_post_meta( $log_id, '_edd_log_user_info' );
			}
		}

		return true;
	}

	/**
	 * Return the calculated completion percentage
	 *
	 * @since 2.9.2
	 * @return int
	 */
	public function get_percentage_complete() {
		$total = (int) get_option( 'edd_fdlm_total_logs', 0 );

		$percentage = 100;

		if( $total > 0 ) {
			$percentage = ( ( $this->step * $this->per_step ) / $total ) * 100;
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
			$this->done = true;
			delete_option( 'edd_fdlm_total_logs' );
			delete_option( 'edd_fdlm_term_tax_id' );
			$this->message = __( 'File download logs updated successfully.', 'easy-digital-downloads' );
			edd_set_upgrade_complete( 'update_file_download_log_data' );
			return false;
		}
	}

	public function headers() {
		edd_set_time_limit();
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

	/**
	 * Fetch total number of log IDs needing migration
	 *
	 * @since 2.9.5
	 *
	 * @global object $wpdb
	 */
	public function pre_fetch() {
		global $wpdb;

		// Default count (assume no entries)
		$log_id_count = 0;

		// Query for a term ID (make sure log items exist)
		$term_id = $wpdb->get_var( "SELECT term_id FROM {$wpdb->terms} WHERE name = 'file_download' LIMIT 1" );

		// Log items exist...
		if ( ! empty( $term_id ) ) {

			// Query for possible entries...
			$term_tax_id  = $wpdb->get_var( $wpdb->prepare( "SELECT term_taxonomy_id FROM {$wpdb->term_taxonomy} WHERE term_id = %d AND taxonomy = 'edd_log_type' LIMIT 1", $term_id ) );

			// Entries exist...
			if ( ! empty( $term_tax_id ) ) {

				// Cache the term taxonomy ID
				update_option( 'edd_fdlm_term_tax_id', $term_tax_id );

				// Count the number of entries!
				$log_id_count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->term_relationships} WHERE term_taxonomy_id = %d", $term_tax_id ) );
			}
		}

		// Temporarily save the number of rows
		update_option( 'edd_fdlm_total_logs', (int) $log_id_count );
	}

	/**
	 * Get the log IDs (50 based on this->per_step) for the current step
	 *
	 * @since 2.9.5
	 *
	 * @global object $wpdb
	 * @return array
	 */
	private function get_log_ids_for_current_step() {
		global $wpdb;

		// Default values
		$log_ids = array();
		$offset  = ( $this->step * $this->per_step ) - $this->per_step;

		// Count the number of entries!
		$term_tax_id = (int) get_option( 'edd_fdlm_term_tax_id', 0 );

		// Only query if term taxonomy ID was prefetched
		if ( ! empty( $term_tax_id ) ) {
			$log_ids = $wpdb->get_col( $wpdb->prepare( "SELECT object_id FROM {$wpdb->term_relationships} WHERE term_taxonomy_id = %d LIMIT %d, %d", $term_tax_id, $offset, $this->per_step ) );
		}

		// Always return an array
		return ! is_wp_error( $log_ids )
			? (array) $log_ids
			: array();
	}
}
