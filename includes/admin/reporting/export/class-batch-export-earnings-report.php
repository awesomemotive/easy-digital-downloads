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
	 * @var string
	 */
	public $export_type = 'earnings_report';

	/**
	 * Set the export headers.
	 *
	 * @since 2.7
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
	 * Get the column headers for the Earnings Report
	 *
	 * @since 2.8.18
	 * @return array
	 */
	public function get_csv_cols() {

		// Always start with the date column.
		$pre_status_columns = array(
			__( 'Monthly Sales Activity', 'easy-digital-downloads' ),
			__( 'Gross Activity', 'easy-digital-downloads' ),
		);

		$status_cols = $this->get_status_cols();

		// Append the arrays together so it starts with the date, then include the status list.
		$cols = array_merge( $pre_status_columns, $status_cols );

		// Include the 'net' after all other columns.
		$cols[] = __( 'Net Activity', 'easy-digital-downloads' );

		return $cols;

	}

	/**
	 * Specifically retrieve the headers for supported order statuses.
	 *
	 * @since 2.8.18
	 * @return array
	 */
	public function get_status_cols() {
		$status_cols        = edd_get_payment_statuses();
		$supported_statuses = $this->get_supported_statuses();

		foreach ( $status_cols as $id => $label ) {
			if ( ! in_array( $id, $supported_statuses ) ) {
				unset( $status_cols[ $id ] );
			}
		}

		return array_values( $status_cols );
	}

	/**
	 * Get a list of the statuses supported in this report.
	 *
	 * @since 2.8.18
	 * @return array The status keys supported (not Labels)
	 */
	public function get_supported_statuses() {
		$statuses = edd_get_payment_statuses();

		// Unset a few statuses we don't need in the report:
		unset( $statuses['pending'], $statuses['processing'], $statuses['preapproval'] );
		$supported_statuses = array_keys( $statuses );

		return apply_filters( 'edd_export_earnings_supported_statuses', $supported_statuses );
	}

	/**
	 * Output the CSV columns.
	 *
	 * We make use of this function to set up the header of the earnings report.
	 *
	 * @since 2.7
	 *
	 * @return array $cols CSV header.
	 */
	public function print_csv_cols() {
		$cols     = $this->get_csv_cols();
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

		$statuses    = $this->get_supported_statuses();
		$number_cols = count( $statuses ) + 2;

		$col_data .= ',';
		for ( $i = 1; $i <= $number_cols; $i++ ) {
			$col_data .= __( 'Order Count', 'easy-digital-downloads' ) . ',';
			$col_data .= __( 'Gross Amount', 'easy-digital-downloads' );

			if ( $number_cols !== $i ) {
				$col_data .= ',';
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

			$supported_statuses = $this->get_supported_statuses();

			$gross_count  = 0;
			$gross_amount = 0;
			foreach ( $supported_statuses as $status ) {
				$gross_count  += absint( $data[ $status ]['count'] );
				$gross_amount += $data[ $status ]['amount'];
			}

			$row_data .= $gross_count . ',';
			$row_data .= '"' . edd_format_amount( $gross_amount ) . '",';

			foreach ( $data as $status => $status_data ) {
				$row_data .= isset( $data[ $status ]['count'] ) ? $data[ $status ]['count'] . ',' : 0 . ',';

				$column_amount = isset( $data[ $status ]['amount'] ) ? edd_format_amount( $data[ $status ]['amount'] ) : 0;
				if ( ! empty( $column_amount ) && 'refunded' == $status ) {
					$column_amount = '-' . $column_amount;
				}

				$row_data .= isset( $data[ $status ]['amount'] ) ? '"' . $column_amount . '"' . ',' : 0 . ',';
			}

			// Allows extensions with other 'completed' statuses to alter net earnings, like recurring.
			$completed_statuses = apply_filters( 'edd_export_earnings_completed_statuses', array( 'publish', 'revoked' ) );

			$net_count  = 0;
			$net_amount = 0;
			foreach ( $completed_statuses as $status ) {
				$net_count  += absint( $data[ $status ]['count'] );
				$net_amount += floatval( $data[ $status ]['amount'] );
			}
			$row_data .= $net_count . ',';
			$row_data .= '"' . edd_format_amount( $net_amount ) . '"';

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
	 *
	 * @return array $data The data for the CSV file
	 */
	public function get_data() {
		global $wpdb;

		$data = array();

		$start_date = date( 'Y-m-d 00:00:00', strtotime( $this->start ) );

		if ( $this->count() == 0 ) {
			$end_date = date( 'Y-m-d 23:59:59', strtotime( $this->end ) );
		} else {
			$end_date = date( 'Y-m-d 23:59:59', strtotime( 'first day of +1 month', strtotime( $start_date ) ) );
		}

		if ( $this->step > 1 ) {
			$start_date = date( 'Y-m-d 00:00:00', strtotime( 'first day of +' . ( $this->step - 1 ) . ' month', strtotime( $start_date ) ) );

			if ( date( 'Y-m', strtotime( $start_date ) ) == date( 'Y-m', strtotime( $this->end ) ) ) {
				$end_date = date( 'Y-m-d 23:59:59', strtotime( $this->end ) );
			} else {
				$end_date = date( 'Y-m-d 23:59:59', strtotime( 'first day of +1 month', strtotime( $start_date ) ) );
			}
		}

		if ( strtotime( $start_date ) > strtotime( $this->end ) ) {
			return false;
		}

		$statuses = $this->get_supported_statuses();
		$totals   = $wpdb->get_results( $wpdb->prepare(
			"SELECT SUM(meta_value) AS total, COUNT(DISTINCT posts.ID) AS count, posts.post_status AS status
			 FROM {$wpdb->posts} AS posts
			 INNER JOIN {$wpdb->postmeta} ON posts.ID = {$wpdb->postmeta}.post_ID
			 WHERE posts.post_type IN ('edd_payment')
			 AND {$wpdb->postmeta}.meta_key = '_edd_payment_total'
			 AND posts.post_date >= %s
			 AND posts.post_date < %s
			 GROUP BY YEAR(posts.post_date), MONTH(posts.post_date), posts.post_status
			 ORDER by posts.post_date ASC", $start_date, $end_date ), ARRAY_A );

		$total_data = array();
		foreach ( $totals as $row ) {
			$total_data[ $row['status'] ] = array(
				'count'  => $row['count'],
				'amount' => $row['total']
			);
		}

		foreach ( $statuses as $status ) {

			if ( ! isset( $total_data[ $status ] ) ) {
				$data[ $status ] = array(
					'count'  => 0,
					'amount' => 0,
				);
			} else {
				$data[ $status ] = array(
					'count'  => $total_data[ $status ]['count'],
					'amount' => $total_data[ $status ]['amount'],
				);
			}

		}

		$data = apply_filters( 'edd_export_get_data', $data );
		$data = apply_filters( 'edd_export_get_data_' . $this->export_type, $data, $start_date, $end_date );

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
	 *
	 * @param array $request The Form Data passed into the batch processing
	 * @return void
	 */
	public function set_properties( $request ) {
		$this->start = ( isset( $request['start_month'] ) && isset( $request['start_year'] ) ) ? sanitize_text_field( $request['start_year'] ) . '-' . sanitize_text_field( $request['start_month'] ) . '-1' : '';
		$this->end   = ( isset( $request['end_month'] ) && isset( $request['end_year'] ) ) ? sanitize_text_field( $request['end_year'] ) . '-' . sanitize_text_field( $request['end_month'] ) . '-' . cal_days_in_month( CAL_GREGORIAN, sanitize_text_field( $request['end_month'] ), sanitize_text_field( $request['end_year'] ) ) : '';
	}
}
