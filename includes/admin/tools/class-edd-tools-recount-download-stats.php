<?php
/**
 * Recount download earnings and stats
 *
 * This class handles batch processing of recounting earnings and stats
 *
 * @subpackage  Admin/Tools/EDD_Tools_Recount_Stats
 * @copyright   Copyright (c) 2021, Sandhills Development, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       2.5
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

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
	 * @var string
	 */
	public $message = '';

	/**
	 * ID of the download we're recounting stats for
	 * @var int|false
	 */
	protected $download_id = false;

	/**
	 * Get the Export Data
	 *
	 * @since 2.5
	 * @global object $wpdb Used to query the database using the WordPress
	 *   Database API
	 * @return bool
	 */
	public function get_data() {

		$accepted_statuses = apply_filters( 'edd_recount_accepted_statuses', edd_get_gross_order_statuses() );

		// These arguments are no longer used, but keeping the filter here to apply the deprecation notice.
		$deprecated_args = edd_apply_filters_deprecated( 'edd_recount_download_stats_args', array(
			array(
				'post_parent'    => $this->download_id,
				'post_type'      => 'edd_log',
				'posts_per_page' => $this->per_step,
				'post_status'    => 'publish',
				'paged'          => $this->step,
				'log_type'       => 'sale',
				'fields'         => 'ids',
			)
		), '3.0' );

		if ( ! empty( $this->download_id ) && is_numeric( $this->download_id ) ) {
			edd_recalculate_download_sales_earnings( $this->download_id );

			return false;
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
		return 100;
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
			wp_die( __( 'You do not have permission to perform this action.', 'easy-digital-downloads' ), __( 'Error', 'easy-digital-downloads' ), array( 'response' => 403 ) );
		}

		$more_to_do = $this->get_data();

		if( $more_to_do ) {
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
		edd_set_time_limit();
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
	 * Delete an option
	 *
	 * @since  2.5
	 * @param  string $key The option_name to delete
	 * @return void
	 */
	protected function delete_data( $key ) {
		global $wpdb;
		$wpdb->delete( $wpdb->options, array( 'option_name' => $key ) );
	}

}
