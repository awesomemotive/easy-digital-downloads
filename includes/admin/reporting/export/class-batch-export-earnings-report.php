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

		if ( ! edd_is_func_disabled( 'set_time_limit' ) ) {
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

		$col_data = '';

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
			$start_date = date( 'Y-m-d', strtotime( $this->start ) );

			if ( $this->count() == 0 ) {
				$end_date = date( 'Y-m-d', strtotime( $this->end ) );
			} else {
				$end_date = date( 'Y-m-d', strtotime( 'first day of +1 month', strtotime( $start_date ) ) );
			}

			if ( $this->step == 1 ) {
				$row_data .= $start_date . ',';
			} elseif ( $this->step > 1 ) {
				$start_date = date( 'Y-m-d', strtotime( 'first day of +' . ( $this->step - 1 ) . ' month', strtotime( $start_date ) ) );

				if ( date( 'Y-m', strtotime( $start_date ) ) == date( 'Y-m', strtotime( $this->end ) ) ) {
					$end_date = date( 'Y-m-d', strtotime( $this->end ) );
					$row_data .= $end_date . ',';
				} else {
					$row_data .= $start_date . ',';
				}
			}

			$row_data .= isset( $data['publish']['count'] ) ? $data['publish']['count'] . ',' : 0 . ',';

			$publish_total   = isset( $data['publish']['amount']   ) ? $data['publish']['amount'] : 0;
			$refunded_total  = isset( $data['refunded']['amount']  ) ? $data['refunded']['amount'] : 0;
			$cancelled_total = isset( $data['cancelled']['amount'] ) ? $data['cancelled']['amount'] : 0;

			$row_data .= '"' . edd_format_amount( $publish_total + $refunded_total + $cancelled_total ) . '",';

			$row_data .= isset( $data['refunded']['count'] ) ? $data['refunded']['count'] . ',' : 0 . ',';
			$row_data .= isset( $data['refunded']['amount'] ) ? '"-' . edd_format_amount( $data['refunded']['amount'] ) . '"' . ',' : 0 . ',';

			$row_data .= isset( $data['revoked']['count'] ) ? $data['revoked']['count'] . ',' : 0 . ',';
			$row_data .= isset( $data['revoked']['amount'] ) ? '"' . edd_format_amount( $data['revoked']['amount'] ) . '"' . ',' : 0 . ',';

			$row_data .= isset( $data['abandoned']['count'] ) ? $data['abandoned']['count'] . ',' : 0 . ',';
			$row_data .= isset( $data['abandoned']['amount'] ) ? '"' . edd_format_amount( $data['abandoned']['amount'] ) . '"' . ',' : 0 . ',';

			$row_data .= isset( $data['failed']['count'] ) ? $data['failed']['count'] . ',' : 0 . ',';
			$row_data .= isset( $data['failed']['amount'] ) ? '"' . edd_format_amount( $data['failed']['amount'] ) . '"' . ',' : 0 . ',';

			$row_data .= isset( $data['cancelled']['count'] ) ? $data['cancelled']['count'] . ',' : 0 . ',';
			$row_data .= isset( $data['cancelled']['amount'] ) ? '"' . edd_format_amount( $data['cancelled']['amount'] ) . '"' . ',' : 0 . ',';

			$row_data .= isset( $data['publish']['amount'] ) ? '"' . edd_format_amount( $data['publish']['amount'] ) . '"' . ',' : 0;

			$row_data .= "\r\n";

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

		$start_date = date( 'Y-m-d', strtotime( $this->start ) );
		$maybe_end_date = date( 'Y-m-d', strtotime( 'first day of +1 month', strtotime( $start_date ) ) );

		if ( $this->count() == 0 ) {
			$end_date = date( 'Y-m-d', strtotime( $this->end ) );
		} else {
			$end_date = date( 'Y-m-d', strtotime( 'first day of +1 month', strtotime( $start_date ) ) );
		}

		if ( $this->step > 1 ) {
			$start_date = date( 'Y-m-d', strtotime( 'first day of +' . ( $this->step - 1 ) . ' month', strtotime( $start_date ) ) );

			if ( date( 'Y-m', strtotime( $start_date ) ) == date( 'Y-m', strtotime( $this->end ) ) ) {
				$end_date = date( 'Y-m-d', strtotime( $this->end ) );
			} else {
				$end_date = date( 'Y-m-d', strtotime( 'first day of +1 month', strtotime( $start_date ) ) );
			}
		}

		if ( strtotime( $start_date ) > strtotime( $this->end ) ) {
			return false;
		}

		$totals = $wpdb->get_results( $wpdb->prepare(
			"SELECT SUM(meta_value) AS total, DATE_FORMAT(posts.post_date, '%%m') AS m, YEAR(posts.post_date) AS y, COUNT(DISTINCT posts.ID) AS count, posts.post_status AS status
			 FROM {$wpdb->posts} AS posts
			 INNER JOIN {$wpdb->postmeta} ON posts.ID = {$wpdb->postmeta}.post_ID
			 WHERE posts.post_type IN ('edd_payment')
			 AND {$wpdb->postmeta}.meta_key = '_edd_payment_total'
			 AND posts.post_date >= %s
			 AND posts.post_date < %s
			 GROUP BY YEAR(posts.post_date), MONTH(posts.post_date), posts.post_status
			 ORDER by posts.post_date ASC", $start_date, $end_date ), ARRAY_A );

		foreach ( $totals as $total ) {
			$data[ $total['status'] ] = array(
				'count' => $total['count'],
				'amount' => $total['total']
			);
		}

		if ( empty( $data ) ) {
			$data = array(
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
		return abs( ( date( 'Y', strtotime( $this->end ) ) - date( 'Y', strtotime( $this->start ) ) ) * 12 + ( date( 'm', strtotime( $this->end ) ) - date( 'm', strtotime( $this->start ) ) ) );
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
		$this->start = ( isset( $request['start_month'] ) && isset( $request['start_year'] ) ) ? sanitize_text_field( $request['start_year'] ) . '-' . sanitize_text_field( $request['start_month'] ) . '-1' : '';
		$this->end   = ( isset( $request['end_month'] ) && isset( $request['end_year'] ) ) ? sanitize_text_field( $request['end_year'] ) . '-' . sanitize_text_field( $request['end_month'] ) . '-' . cal_days_in_month( CAL_GREGORIAN, sanitize_text_field( $request['end_month'] ), sanitize_text_field( $request['end_year'] ) ) : '';
	}
}
