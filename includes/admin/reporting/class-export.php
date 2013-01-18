<?php
/**
 * Export Class
 *
 * This is the base class for all export methods. Each data export type (customers, payments, etc) extend this class
 *
 * @package     Easy Digital Downloads
 * @subpackage  Export Class
 * @copyright   Copyright (c) 2012, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.4.2
*/

class EDD_Export {

	private $export_type;

	public function __construct() {
		$this->$export_type = 'default';
	}

	public function can_export() {
		return apply_filters( 'edd_export_capability', current_user_can( 'manage_options' ) );
	}


	public function headers() {

		ignore_user_abort( true );

		if ( ! edd_is_func_disabled( 'set_time_limit' ) && ! ini_get( 'safe_mode' ) )
			set_time_limit( 0 );

		nocache_headers();
		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename=edd-export-' . $this->export_type . '-' . date( 'm-d-Y' ) . '.csv' );
		header( "Expires: 0" );
	}

	public function csv_cols() {
		$cols = array(
			'id'   => __( 'ID',   'edd' ),
			'date' => __( 'Date', 'edd' )
		);
	}

	public function get_csv_cols() {
		$cols = $this->csv_cols();
		return apply_filters( 'edd_export_csv_cols_' . $this->export_type, $cols );
	}

	public function csv_cols_out() {
		$cols = $this->csv_cols();
		foreach( $cols as $col_id => $column ) {
			echo '"' . $column . '",';
		}
		echo "\r\n";
	}

	public function get_data() {

		$data = array(
			'id'   => '',
			'data' => date( 'F j, Y' )
		);

		$data = apply_filters( 'edd_export_get_data', $data );
		$data = apply_filters( 'edd_export_get_data_' . $this->export_type, $data );

		return $data;
	}

	public function csv_rows_out() {

		$data = $this->get_data();

		$cols = $this->get_csv_cols();

		// Output each row
		foreach(  $data as $row ) {

			foreach( $row as $col_id => $column ) {

				// Make sure the column is valid
				if( in_array( $col_id, $cols ) ) {

					echo '"' . $column . '",';

				}

			}

			echo "\r\n";

		}
	}


	public function send() {

		if( ! $this->can_export() )
			wp_die( __( 'You do not have permission to export data.', 'edd' ), __( 'Error', 'edd' ) );

		// Set headers
		$this->headers();

		// Output CSV columns (headers)
		$this->csv_cols_out();

		// Output CSV rows
		$this->csv_rows_out();



	}

}