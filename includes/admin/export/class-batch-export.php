<?php
/**
 * Batch Export Class
 *
 * This is the base class for all batch export methods. Each data export type (customers, payments, etc) extend this class
 *
 * @package     EDD
 * @subpackage  Admin/Export
 * @copyright   Copyright (c) 2015, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       2.4
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * EDD_Export Class
 *
 * @since 2.4
 */
class EDD_Batch_Export extends EDD_Export {

	private $file;

	public $filename;
	public $filetype;
	public $step;

	public function __construct( $_step = 1 ) {

		$upload_dir       = wp_upload_dir();
		$this->filetype   = '.csv';
		$this->filename   = 'edd-export-' . $this->export_type . '-data' . $this->filetype;
		$this->file       = trailingslashit( $upload_dir['basedir'] ) . $this->filename;
		$this->step       = $_step;
		$this->done       = false;
	}

	public function process_step() {

		if ( ! $this->can_export() ) {
			wp_die( __( 'You do not have permission to export data.', 'edd' ), __( 'Error', 'edd' ), array( 'response' => 403 ) );
		}

		if( $this->step < 2 ) {

			// Make sure we start with a fresh file on step 1
			@unlink( $this->file );
			$this->print_csv_cols();
		}

		$rows = $this->print_csv_rows();

		if( $rows ) {

			$args = array(
				'page'       => 'edd-export',
				'edd-export' => $this->export_type,
				'step'       => $this->step += 1
			);

			$base_url = admin_url( 'index.php' );

		} else {

			$args = array(
				'post_type'  => 'download',
				'page'       => 'edd-reports',
				'tab'        => 'export',
				'edd_action' => 'payment_export',
				'ready'      => 1,
				'step'       => $this->step += 1
			);

			$base_url = admin_url( 'edit.php' );

		}

		if( isset( $_REQUEST['month'] ) ) {
			$args['month'] = sanitize_text_field( $_REQUEST['month'] );
		}

		if( isset( $_REQUEST['year'] ) ) {
			$args['year'] = sanitize_text_field( $_REQUEST['year'] );
		}

		if( isset( $_REQUEST['status'] ) ) {
			$args['status'] = sanitize_text_field( $_REQUEST['status'] );
		}

		wp_redirect( esc_url_raw( add_query_arg( $args, $base_url ) ) );
		exit;
		

	}

	/**
	 * Output the CSV columns
	 *
	 * @access public
	 * @since 1.4.4
	 * @uses EDD_Export::get_csv_cols()
	 * @return void
	 */
	public function print_csv_cols() {
		
		$col_data = '';
		$cols = $this->get_csv_cols();
		$i = 1;
		foreach( $cols as $col_id => $column ) {
			$col_data .= '"' . addslashes( $column ) . '"';
			$col_data .= $i == count( $cols ) ? '' : ',';
			$i++;
		}
		$col_data .= "\r\n";
	
		$this->stash_step_data( $col_data );

		return $col_data;

	}

	/**
	 * Retrieve the CSV rows for the current step
	 *
	 * @access public
	 * @since 2.4
	 * @return void
	 */
	public function print_csv_rows() {
		
		$row_data = '';
		$data     = $this->get_data();
		$cols     = $this->get_csv_cols();

		if( $data ) {

			// Output each row
			foreach ( $data as $row ) {
				$i = 1;
				foreach ( $row as $col_id => $column ) {
					// Make sure the column is valid
					if ( array_key_exists( $col_id, $cols ) ) {
						$row_data .= '"' . addslashes( $column ) . '"';
						$row_data .= $i == count( $cols ) ? '' : ',';
						$i++;
					}
				}
				$row_data .= "\r\n";
			}

			$this->stash_step_data( $row_data );

			return $row_data;
		}

		return false;
	}

	public function ready() {
		return ! empty( $_REQUEST['ready'] );
	}

	private function get_file() {
		$file = @file_get_contents( $this->file );
		if( ! $file ) {
			@file_put_contents( $this->file, '' );
		}
		return $file;
	}

	private function stash_step_data( $data = '' ) {

		$file = $this->get_file();
		$file .= $data;
		@file_put_contents( $this->file, $file );

	}

	/**
	 * Perform the export
	 *
	 * @access public
	 * @since 2.4
	 * @uses EDD_Export::can_export()
	 * @uses EDD_Export::headers()
	 * @uses EDD_Export::csv_cols_out()
	 * @uses EDD_Export::csv_rows_out()
	 * @return void
	 */
	public function export() {

		// Set headers
		$this->headers();

		$file = $this->get_file();

		@unlink( $this->file );

		echo $file;

		edd_die();
	}
}
