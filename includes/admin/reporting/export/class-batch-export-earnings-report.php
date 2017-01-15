<?php
/**
 * Batch Earnings Report Export Class.
 *
 * This class handles earnings report export.
 *
 * @package     EDD
 * @subpackage  Admin/Reports
 * @copyright   Copyright (c) 2017, Sunny Ratilal
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
class EDD_Batch_Earnings_Report_Export extends EDD_Batch_Export {
	/**
	 * Our export type. Used for export-type specific filters/actions.
	 *
	 * @since 2.7
	 * @access public
	 * @var string
	 */
	public $export_type = 'earnings_report';

	/**
	 * Set the export headers.
	 *
	 * @since 2.7
	 * @access public
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
		header( 'Content-Disposition: attachment; filename=' . apply_filters( 'edd_earnings_report_export_filename', 'edd-export-' . $this->export_type . '-' . date( 'm' ) . '-' . date( 'Y' ) ) . '.csv' );
		header( "Expires: 0" );
	}

	/**
	 * Output the CSV columns.
	 *
	 * We make use of this function to set up the header of the earnings report.
	 *
	 * @access public
	 * @since 2.7
	 *
	 * @return array $cols CSV header.
	 */
	public function print_csv_cols() {
		$cols = array(
			__( 'Monthly Sales Activity', 'easy-digital-downloads' ),
			__( 'Sales', 'easy-digital-downloads' ),
			__( 'Refunds', 'easy-digital-downloads' ),
			__( 'Revoked', 'easy-digital-downloads' ),
			__( 'Abandoned', 'easy-digital-downloads' ),
			__( 'Failed', 'easy-digital-downloads' ),
			__( 'Cancelled', 'easy-digital-downloads' ),
			__( 'Net Activity', 'easy-digital-downloads' )
		);

		for ( $i = 0; $i < count( $cols ); $i++ ) {
			$col_data .= $cols[ $i ];

			// We don't need an extra space after the first column
			if ( $i == 0 ) {
				$col_data .= ',';
				continue;
			}

			if ( $i == ( count( $cols ) - 1 ) ) {
				$col_data .= "\r\n";
			} else {
				$col_data .= ",,";
			}
		}

		// Subtract 2 for `Net Activity` and `Monthly Sales Activity` column
		$statuses = count( $cols ) - 2;

		$col_data .= ',';
		for ( $i = 0; $i < $statuses; $i++ ) {
			$col_data .= __( 'Count', 'easy-digital-downloads' ) . ',' . __( 'Gross Amount', 'easy-digital-downloads' );

			if ( $i == ( $statuses - 1 ) ) {
				$col_data .= "\r\n";
			} else {
				$col_data .= ",";
			}
		}

		$col_data .= "\r\n";

		$this->stash_step_data( $col_data );

		return $col_data;
	}

	/**
	 * Print the CSV rows for the current step.
	 *
	 * @since 2.7
	 * @access public
	 *
	 * @return mixed string|false
	 */
	public function print_csv_rows() {
		$row_data = '';

		$data = $this->get_data();

		if ( $data ) {
			foreach ( $data as $item ) {
				$row_data .= ','; // Leave first column empty

				$row_data .= isset( $item['publish']['count'] ) ? $item['publish']['count'] : 0 . ',';

				$total = 0;
				foreach ( $item as $status => $value ) {
					$total += $value['amount'];
				}
				$row_data .= $total . ',';

				$row_data .= isset( $item['refunded']['count'] ) ? $item['refunded']['count'] : 0 . ',';
				$row_data .= isset( $item['refunded']['amount'] ) ? '-' . $item['refunded']['amount'] : 0 . ',';

				$row_data .= isset( $item['revoked']['count'] ) ? $item['revoked']['count'] : 0 . ',';
				$row_data .= isset( $item['revoked']['amount'] ) ? '-' . $item['revoked']['amount'] : 0 . ',';

				$row_data .= isset( $item['abandoned']['count'] ) ? $item['abandoned']['count'] : 0 . ',';
				$row_data .= isset( $item['abandoned']['amount'] ) ? '-' . $item['abandoned']['amount'] : 0 . ',';

				$row_data .= isset( $item['failed']['count'] ) ? $item['failed']['count'] : 0 . ',';
				$row_data .= isset( $item['failed']['amount'] ) ? '-' . $item['failed']['amount'] : 0 . ',';

				$row_data .= isset( $item['cancelled']['count'] ) ? $item['cancelled']['count'] : 0 . ',';
				$row_data .= isset( $item['cancelled']['amount'] ) ? '-' . $item['cancelled']['amount'] : 0 . ',';

				$row_data .= isset( $item['publish']['amount'] ) ? $item['publish']['amount'] : 0;

				$row_data .= "\r\n";
			}

			$this->stash_step_data( $row_data );

			return $row_data;
		}

		return false;
	}

	/**
	 * Get the Export Data.
	 *
	 * @since 2.7
	 * @access public
	 *
	 * @return array $data The data for the CSV file
	 */
	public function get_data() {
		global $wpdb;

		$data = array();

		$start_year  = isset( $_POST['start_year'] )   ? absint( $_POST['start_year'] )   : date( 'Y' );
		$end_year    = isset( $_POST['end_year'] )     ? absint( $_POST['end_year'] )     : date( 'Y' );
		$start_month = isset( $_POST['start_month'] )  ? absint( $_POST['start_month'] )  : date( 'm' );
		$end_month   = isset( $_POST['end_month'] )    ? absint( $_POST['end_month'] )    : date( 'm' );

		$start_date = date( 'Y-m-d', strtotime( $start_year . '-' . $start_month . '-01' ) );
		$end_date = date( 'Y-m-d', strtotime( $end_year . '-' . $end_month . '-01' ) );

		$totals = $wpdb->get_results( $wpdb->prepare(
			"SELECT SUM(meta_value) AS total, DATE_FORMAT(posts.post_date, '%%m') AS m, YEAR(posts.post_date) AS y, COUNT(DISTINCT posts.ID) AS count, posts.post_status AS status
			 FROM {$wpdb->posts} AS posts
			 INNER JOIN {$wpdb->postmeta} ON posts.ID = {$wpdb->postmeta}.post_ID
			 WHERE posts.post_type IN ('edd_payment')
			 AND {$wpdb->postmeta}.meta_key = '_edd_payment_total'
			 AND posts.post_date >= %s
			 AND posts.post_date < %s
			 GROUP BY YEAR(posts.post_date), MONTH(posts.post_date), posts.post_status
			 ORDER by posts.post_date ASC", $start_date, date( 'Y-m-d', strtotime( '+1 month', strtotime( $end_date ) ) ) ), ARRAY_A );

		foreach ( $totals as $total ) {
			$key = (int) $total['y'] . $total['m'];

			$data[ $key ][ $total['status'] ] = array(
				'count' => $total['count'],
				'amount' => $total['total']
			);
		}

		while ( strtotime( $start_date ) <= strtotime( $end_date ) ) {
			$year = date( 'Y', strtotime( $start_date ) );
			$month = date( 'm', strtotime( $start_date ) );

			$key = $year . $month;

			if ( ! isset( $data[ $key ] ) ) {
				$data[ $key ] = array(
					'publish' => array(
						'count' => 0,
						'amount' => 0
					),
					'refunded' => array(
						'count' => 0,
						'amount' => 0
					),
					'cancelled' => array(
						'count' => 0,
						'amount' => 0
					),
					'revoked' => array(
						'count' => 0,
						'amount' => 0
					),
				);
			}

			$start_date = date( 'Y-m-d', strtotime( '+1 month', strtotime( $start_date ) ) );
		}

		ksort( $data );

		$data = apply_filters( 'edd_export_get_data', $data );
		$data = apply_filters( 'edd_export_get_data_' . $this->export_type, $data );

		return $data;
	}

	/**
	 * Count the number of months we are dealing with.
	 *
	 * @since 2.7
	 * @access private
	 *
	 * @return void
	 */
	private function count() {
		$start_month = date( 'm', strtotime( $this->start ) );
		$start_year = date( 'Y', strtotime( $this->start ) );
		$end_month = date( 'm', strtotime( $this->end ) );
		$end_year = date( 'Y', strtotime( $this->end ) );

		if ( $start_year == $end_year ) {
			$number_of_months = ( $end_month - $start_month ) + 1;
		} else {
			$number_of_months = ( ( ( $end_year - $start_year ) * 12 ) - $start_month ) + $end_month + 1;
		}

		return $number_of_months;
	}

	/**
	 * Return the calculated completion percentage
	 *
	 * @since 2.7
	 * @access public
	 *
	 * @return int Percentage of batch processing complete.
	 */
	public function get_percentage_complete() {
		$percentage = 100;

		$total = $this->count();

		if ( $total > 0 ) {
			$percentage =  ( $this->step / $total ) * 100;
		}

		if ( $percentage > 100 ) {
			$percentage = 100;
		}

		return $percentage;
	}

	/**
	 * Set the properties specific to the earnings report.
	 *
	 * @since 2.7
	 * @access public
	 *
	 * @param array $request The Form Data passed into the batch processing
	 * @return void
	 */
	public function set_properties( $request ) {
		$this->start = isset( $request['start'] ) ? sanitize_text_field( $request['start'] ) : '';
		$this->end   = isset( $request['end']   ) ? sanitize_text_field( $request['end']   ) : '';
	}
}