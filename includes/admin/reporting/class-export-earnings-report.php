<?php
/**
 * Earnings Report Export Class
 *
 * This class handles earnings report export.
 *
 * @package     EDD
 * @subpackage  Admin/Reports
 * @copyright   Copyright (c) 2016, Sunny Ratilal
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       2.7
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * EDD_Earnings_Report_Export Class
 *
 * @since 2.7
 */
class EDD_Earnings_Report_Export extends EDD_Export {
	/**
	 * Our export type. Used for export-type specific filters/actions.
	 *
	 * @var string
	 * @since 2.7
	 */
	public $export_type = 'earnings_report';

	/**
	 * Set the export headers.
	 *
	 * @access public
	 * @since 2.7
	 *
	 * @return void
	 */
	public function headers() {
		ignore_user_abort( true );

		if ( ! edd_is_func_disabled( 'set_time_limit' ) && ! ini_get( 'safe_mode' ) ) {
			set_time_limit( 0 );
		}

		nocache_headers();
		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename=' . apply_filters( 'edd_earnings_export_filename', 'edd-export-' . $this->export_type . '-' . date( 'n' ) . '-' . date( 'Y' ) ) . '.csv' );
		header( "Expires: 0" );
	}

	/**
	 * Report heading.
	 *
	 * @since 2.7
	 * @access public
	 *
	 * @return void
	 */
	private function report_headers() {
		$start_year  = isset( $_POST['start_year'] )   ? absint( $_POST['start_year'] )   : date( 'Y' );
		$end_year    = isset( $_POST['end_year'] )     ? absint( $_POST['end_year'] )     : date( 'Y' );
		$start_month = isset( $_POST['start_month'] )  ? absint( $_POST['start_month'] )  : date( 'm' );
		$end_month   = isset( $_POST['end_month'] )    ? absint( $_POST['end_month'] )    : date( 'm' );

		$start_date = date( 'Y-m-d', strtotime( $start_year . '-' . $start_month . '-01' ) );
		$end_date = date( 'Y-m-d', strtotime( $end_year . '-' . $end_month . '-01' ) );

		// Create two initial blank columns.
		echo ", ,";

		echo __( 'Month', 'easy-digital-downloads' ) . ',';

		while ( strtotime( $start_date ) <= strtotime( $end_date ) ) {
			echo date( 'Y-m-d', $start_date );

			if ( $start_date == $end_date ) {
				echo '\r\n';
			} else {
				echo ',';
			}

			$start_date = strtotime( '+1 month', $start_date );
		}

		/**
	 * Perform the export.
	 *
	 * @since 2.7
	 * @access public
	 *
	 * @uses EDD_Export::can_export()
	 * @uses EDD_Export::headers()
	 * @uses EDD_Export::csv_cols_out()
	 * @uses EDD_Export::csv_rows_out()
	 *
	 * @return void
	 */
	public function export() {
		if ( ! $this->can_export() )
			wp_die( __( 'You do not have permission to export data.', 'easy-digital-downloads' ), __( 'Error', 'easy-digital-downloads' ), array( 'response' => 403 ) );

		// Set headers
		$this->headers();

		$this->report_headers();

		edd_die();
	}
}