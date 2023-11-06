<?php
/**
 * Batch Export Class
 *
 * This is the base class for all batch export methods. Each data export type (customers, payments, etc) extend this class
 *
 * @package     EDD
 * @subpackage  Admin/Export
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       2.4
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * EDD_Export Class
 *
 * @since 2.4
 */
class EDD_Batch_Export extends EDD_Export {

	/**
	 * Whether or not we're done processing.
	 *
	 * @var bool
	 */
	public $done;

	/**
	 * The file the data is stored in
	 *
	 * @since 2.4
	 * @var string
	 */
	protected $file;

	/**
	 * The name of the file the data is stored in
	 *
	 * @since 2.4
	 * @var string
	 */
	public $filename;

	/**
	 * The file type, typically .csv
	 *
	 * @since 2.4
	 * @var string
	 */
	public $filetype;

	/**
	 * The current step being processed
	 *
	 * @since 2.4
	 * @var int
	 */
	public $step;

	/**
	 * Start date, Y-m-d H:i:s
	 *
	 * @since 2.4
	 * @var string
	 */
	public $start;

	/**
	 * End date, Y-m-d H:i:s
	 *
	 * @since 2.4
	 * @var string
	 */
	public $end;

	/**
	 * Status to export
	 *
	 * @since 2.4
	 * @var string
	 */
	public $status;

	/**
	 * Download to export data for
	 *
	 * @since 2.4
	 * @var int
	 */
	public $download = null;

	/**
	 * Download Price ID to export data for
	 *
	 * @since 2.4
	 * @var int
	 */
	public $price_id = null;

	/**
	 * Is the export file writable
	 *
	 * @since 2.4.4
	 * @var bool
	 */
	public $is_writable = true;

	/**
	 *  Is the export file empty
	 *
	 * @since 2.4.4
	 * @var bool
	 */
	public $is_empty = false;

	/**
	 * The data to return to the script.
	 *
	 * @since 3.0
	 * @var array
	 */
	public $result_data = array();

	/**
	 * Get things started
	 *
	 * @since 2.4
	 *
	 * @param int $_step The step to process.
	 */
	public function __construct( $_step = 1 ) {

		$upload_dir     = wp_upload_dir();
		$this->filetype = '.csv';
		$this->filename = 'edd-' . $this->export_type . $this->filetype;
		$this->file     = trailingslashit( $upload_dir['basedir'] ) . $this->filename;

		if ( ! is_writeable( $upload_dir['basedir'] ) ) {
			$this->is_writable = false;
		}

		$this->step = $_step;
		$this->done = false;
	}

	/**
	 * Process a step
	 *
	 * @since 2.4
	 * @return bool
	 */
	public function process_step() {

		if ( ! $this->can_export() ) {
			wp_die( __( 'You do not have permission to export data.', 'easy-digital-downloads' ), __( 'Error', 'easy-digital-downloads' ), array( 'response' => 403 ) );
		}

		if ( $this->step < 2 ) {

			// Make sure we start with a fresh file on step 1.
			if ( file_exists( $this->file ) ) {
				unlink( $this->file );
			}
			$this->print_csv_cols();
		}

		$rows = $this->print_csv_rows();

		if ( $rows ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Output the CSV columns
	 *
	 * @since 2.4
	 * @uses EDD_Export::get_csv_cols()
	 * @return string
	 */
	public function print_csv_cols() {

		$col_data = '';
		$cols     = $this->get_csv_cols();
		$i        = 1;
		foreach ( $cols as $col_id => $column ) {
			$col_data .= '"' . addslashes( $column ) . '"';
			$col_data .= count( $cols ) === $i ? '' : ',';
			++$i;
		}
		$col_data .= "\r\n";

		$this->stash_step_data( $col_data );

		return $col_data;
	}

	/**
	 * Print the CSV rows for the current step
	 *
	 * @since 2.4
	 * @return string|false
	 */
	public function print_csv_rows() {

		$row_data = '';
		$data     = $this->get_data();
		$cols     = $this->get_csv_cols();

		if ( $data ) {

			// Output each row.
			foreach ( $data as $row ) {
				$i = 1;
				foreach ( $row as $col_id => $column ) {
					// Make sure the column is valid. If not, skip it.
					if ( array_key_exists( $col_id, $cols ) ) {
						$column    = is_numeric( $column ) || ! empty( $column ) ? $column : '';
						$row_data .= '"' . addslashes( preg_replace( '/\"/', "'", $column ) ) . '"';
						$row_data .= count( $cols ) === $i ? '' : ',';
						++$i;
					}
				}
				$row_data .= "\r\n";
			}

			$this->stash_step_data( $row_data );

			return $row_data;
		}

		return false;
	}

	/**
	 * Return the calculated completion percentage
	 *
	 * @since 2.4
	 * @return int
	 */
	public function get_percentage_complete() {
		return 100;
	}

	/**
	 * Retrieve the file data is written to
	 *
	 * @since 2.4
	 * @return string
	 */
	protected function get_file() {

		$file = '';

		if ( @file_exists( $this->file ) ) {

			if ( ! is_writeable( $this->file ) ) {
				$this->is_writable = false;
			}

			$file = @file_get_contents( $this->file );

		} else {

			@file_put_contents( $this->file, '' ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
			@chmod( $this->file, 0664 ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged

		}

		return $file;
	}

	/**
	 * Append data to export file
	 *
	 * @since 2.4
	 * @param string $data The data to add to the file.
	 * @return void
	 */
	protected function stash_step_data( $data = '' ) {

		$file  = $this->get_file();
		$file .= $data;
		@file_put_contents( $this->file, $file ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged

		// If we have no rows after this step, mark it as an empty export.
		$file_rows    = file( $this->file, FILE_SKIP_EMPTY_LINES );
		$default_cols = $this->get_csv_cols();
		$default_cols = empty( $default_cols ) ? 0 : 1;

		$this->is_empty = count( $file_rows ) === $default_cols ? true : false;
	}

	/**
	 * Perform the export
	 *
	 * @since 2.4
	 * @return void
	 */
	public function export() {

		// Set headers.
		$this->headers();

		$file = $this->get_file();

		@unlink( $this->file );

		echo $file;

		die();
	}

	/**
	 * Set the properties specific to the export
	 *
	 * @since 2.4.2
	 * @param array $request The Form Data passed into the batch processing.
	 */
	public function set_properties( $request ) {}

	/**
	 * Allow for prefetching of data for the remainder of the exporter
	 *
	 * @since  2.5
	 * @return void
	 */
	public function pre_fetch() {}

	/**
	 * Gets the date query.
	 *
	 * @since 3.0
	 * @return array
	 */
	protected function get_date_query() {
		$date_query = array(
			'after'     => '',
			'before'    => '',
			'inclusive' => true,
		);

		if ( $this->start ) {
			$start_date_string   = EDD()->utils->get_date_string( $this->start );
			$date_query['after'] = edd_get_utc_date_string( $start_date_string );
		}

		if ( $this->end ) {
			$end_date_string      = EDD()->utils->get_date_string( $this->end, 23, 59, 59 );
			$date_query['before'] = edd_get_utc_date_string( $end_date_string );
		}

		return array( $date_query );
	}
}
