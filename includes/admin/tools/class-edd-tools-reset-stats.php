<?php
/**
 * Recount store earnings and stats
 *
 * This class handles batch processing of resetting store and download sales and earnings stats
 *
 * @subpackage  Admin/Tools/EDD_Tools_Reset_Stats
 * @copyright   Copyright (c) 2015, Chris Klosowski
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       2.5
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * EDD_Tools_Reset_Stats Class
 *
 * @since 2.5
 */
class EDD_Tools_Reset_Stats extends EDD_Batch_Export {

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

		$args = apply_filters( 'edd_tools_reset_stats_args', array(
			'post_type'      => 'download',
			'posts_per_page' => $this->per_step,
			'paged'          => $this->step,
			'post_status'    => 'any',
		) );

		$downloads = get_posts( $args );
		$this->downloads = array();
		if ( $downloads ) {

			foreach ( $downloads as $download ) {
				$this->downloads[] = $download->ID;
				update_post_meta( $download->ID, '_edd_download_sales'   , 0 );
				update_post_meta( $download->ID, '_edd_download_earnings', 0 );
			}

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

		$args = apply_filters( 'edd_tools_reset_stats_total_args', array(
			'post_type'      => 'download',
			'post_status'    => 'any',
			'posts_per_page' => -1,
		) );

		$downloads = get_posts( $args );
		$total     = count( $downloads );

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
			update_option( 'edd_earnings_total', 0 );
			$this->done    = true;
			$this->message = __( 'Earnings and sales stats successfully reset.', 'edd' );
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
