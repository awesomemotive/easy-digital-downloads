<?php
/**
 * Recount download earnings and stats AND store earnings
 *
 * This class handles batch processing of recounting earnings and stats for all downloads and store totals
 *
 * @subpackage  Admin/Tools/EDD_Tools_Recount_All_Stats
 * @copyright   Copyright (c) 2021, Sandhills Development, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       2.5
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * EDD_Tools_Recount_All_Stats Class
 *
 * @since 2.5
 */
class EDD_Tools_Recount_All_Stats extends EDD_Batch_Export {

	/**
	 * @var int[]
	 */
	private $download_ids;

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
	 * Return the calculated completion percentage
	 *
	 * @since 2.5
	 * @return int
	 */
	public function get_percentage_complete() {
		$percentage = 100;
		$total      = array_sum( (array) wp_count_posts( 'download' ) );

		if ( $total > 0 ) {
			$percentage = ( ( $this->per_step * $this->step ) / $total ) * 100;
		}

		if ( $percentage > 100 ) {
			$percentage = 100;
		}

		return $percentage;
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

		$download_ids = $this->get_download_ids();

		if ( ! empty( $download_ids ) && is_array( $download_ids ) ) {
			foreach ( $this->get_download_ids() as $download_id ) {
				edd_recalculate_download_sales_earnings( $download_id );
			}
		}

		if ( ! empty( $download_ids ) ) {
			$this->done = false;
			return true;
		} else {
			$this->delete_data( 'edd_recount_all_total' );
			$this->delete_data( 'edd_temp_recount_all_stats' );
			$this->delete_data( 'edd_temp_payment_items' );
			$this->delete_data( 'edd_temp_download_ids' );
			$this->delete_data( 'edd_temp_processed_payments' );
			$this->done    = true;
			$this->message = __( 'Earnings and sales stats successfully recounted.', 'easy-digital-downloads' );
			return false;
		}
	}

	/**
	 * Returns the download IDs to process during this step.
	 *
	 * @since 3.0
	 *
	 * @return int[]
	 */
	private function get_download_ids() {
		if ( null === $this->download_ids ) {
			$this->download_ids = get_posts( array(
				'post_status'    => 'any',
				'post_type'      => 'download',
				'posts_per_page' => $this->per_step,
				'offset'         => ( $this->step - 1 ) * $this->per_step,
				'fields'         => 'ids',
			) );
		}

		return $this->download_ids;
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
