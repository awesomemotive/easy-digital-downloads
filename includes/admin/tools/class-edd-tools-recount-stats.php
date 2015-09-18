<?php
/**
 * Recount store earnings and stats
 *
 * This class handles batch processing of recounting earnings and stats
 *
 * @subpackage  Admin/Tools
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
class EDD_Tools_Recount_Stats extends EDD_Batch_Export {

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
	 * Sets the defeault status to incomplete
	 * @since 2.5
	 * @var boolean
	 */
	public $is_complete = false;

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
		$total = (float) 0;

		$args = apply_filters( 'edd_get_total_earnings_args', array(
			'number'   => 30,
			'page'     => $this->step,
			'status' => array( 'publish', 'revoked' ),
			'fields' => 'ids'
		) );

		$payments = edd_get_payments( $args );
		if ( $payments ) {

			foreach ( $payments as $payment ) {
				$total += edd_get_payment_amount( $payment );
			}

			if ( $total < 0 ) {
				$total = 0;
			}

			$total = round( $total, 2 );

			// Store the total for the first time
			update_option( 'edd_earnings_total', $total );

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

		$status = 'publish';
		$args   = array(
			'start-date' => date( 'Y-n-d H:i:s', strtotime( $this->start ) ),
			'end-date'   => date( 'Y-n-d H:i:s', strtotime( $this->end ) ),
		);

		$total = edd_count_payments( $args )->$status;

		$percentage = 100;

		if( $total > 0 ) {
			$percentage = ( ( 30 * $this->step ) / $total ) * 100;
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
		$this->start  = isset( $request['download_id'] )  ? sanitize_text_field( $request['download_id'] )  : '';
	}

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

		$rows = $this->get_data();

		if( $rows ) {
			return true;
		} else {
			return false;
		}
	}

	public function headers() {
		ignore_user_abort( true );

		if ( ! edd_is_func_disabled( 'set_time_limit' ) && ! ini_get( 'safe_mode' ) ) {
			set_time_limit( 0 );
		}

		nocache_headers();
		header( "Location: " . admin_url( 'edit.php?post_type=download&page=edd-tools&tab=general' ) );
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
