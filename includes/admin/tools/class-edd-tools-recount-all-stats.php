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

if ( ! class_exists( 'EDD_Tools_Recount_Download_Stats' ) ) {
	require_once EDD_PLUGIN_DIR . 'includes/admin/tools/class-edd-tools-recount-download-stats.php';
}

/**
 * EDD_Tools_Recount_All_Stats Class
 *
 * @since 2.5
 */
class EDD_Tools_Recount_All_Stats extends EDD_Tools_Recount_Download_Stats {

	/**
	 * @var int[]
	 */
	private $download_ids;

	/**
	 * Return the calculated completion percentage
	 *
	 * @since 2.5
	 * @return int
	 */
	public function get_percentage_complete() {
		$percentage = 100;

		$query = new WP_Query( array(
			'post_status' => 'any',
			'post_type'   => 'download',
			'fields'      => 'ids'
		) );
		$total = $query->post_count;

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
				$this->download_id = $download_id;
				$this->get_data();
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

}
