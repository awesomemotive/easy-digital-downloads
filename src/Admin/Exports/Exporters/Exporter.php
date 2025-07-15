<?php
/**
 * Abstract Exporter class.
 *
 * This is the new base class for all export methods.
 *
 * @package     EDD\Admin\Exports\Exporters
 * @copyright   Copyright (c) 2025, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.3.8
 */

namespace EDD\Admin\Exports\Exporters;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

use EDD\Utils\FileSystem;
use EDD\Utils\Transient;

/**
 * Abstract Exporter class.
 *
 * @since 3.3.8
 */
abstract class Exporter {

	/**
	 * Whether or not the file is writable.
	 *
	 * @since 3.3.8
	 * @var bool
	 */
	public $is_writable = true;

	/**
	 * Whether or not the export data is empty.
	 *
	 * @since 3.3.8
	 * @var bool
	 */
	public $is_empty = false;

	/**
	 * The result data.
	 *
	 * @since 3.3.8
	 * @var array
	 */
	public $result_data = array();

	/**
	 * The message to display when the export is complete.
	 *
	 * @since 3.3.8
	 * @var string
	 */
	public $message = '';

	/**
	 * Whether or not we're done processing.
	 *
	 * @since 3.3.8
	 * @var bool
	 */
	public $done = false;

	/**
	 * Used to determine if the export is truly an export or just a batch process.
	 *
	 * @var bool
	 */
	public $is_void = false;

	/**
	 * The file the data is stored in.
	 *
	 * @since 3.3.8
	 * @var string
	 */
	protected $file;

	/**
	 * The file type, typically .csv.
	 *
	 * @since 3.3.8
	 * @var string
	 */
	protected $filetype = 'csv';

	/**
	 * The current step being processed.
	 *
	 * @since 3.3.8
	 * @var int
	 */
	protected $step = 1;

	/**
	 * The number of items to process per step.
	 *
	 * @since 3.3.8
	 * @var int
	 */
	protected $per_step = 30;

	/**
	 * The total number of items to process.
	 *
	 * @since 3.3.8
	 * @var int
	 */
	protected $total;

	/**
	 * The start date.
	 *
	 * @since 3.3.8
	 * @var string
	 */
	protected $start = '';

	/**
	 * The end date.
	 *
	 * @since 3.3.8
	 * @var string
	 */
	protected $end = '';

	/**
	 * File system object.
	 *
	 * @since 3.3.8
	 * @var EDD\Utils\FileSystem
	 */
	protected $file_system;

	/**
	 * Constructor.
	 *
	 * @since 3.3.8
	 * @param int $step The step to start at.
	 */
	public function __construct( $step = 1 ) {
		$this->file_system = FileSystem::get_fs();
		$this->step        = $step;
	}

	/**
	 * Set the properties specific to the export.
	 *
	 * @since 3.3.8
	 * @param array $request The form data.
	 */
	abstract public function set_properties( $request ): void;

	/**
	 * Process the current step.
	 *
	 * @since 3.3.8
	 * @return bool
	 */
	public function process_step() {
		if ( ! $this->can_export() ) {
			wp_die( __( 'You do not have permission to export data.', 'easy-digital-downloads' ), __( 'Error', 'easy-digital-downloads' ), array( 'response' => 403 ) );
		}

		if ( $this->step < 2 ) {

			$this->maybe_set_up_file();
			// Make sure we start with a fresh file on step 1.
			if ( FileSystem::file_exists( $this->file ) ) {
				$this->file_system->delete( $this->file );
			}
			$this->print_columns();
		}

		return ! empty( $this->print_rows() );
	}

	/**
	 * Perform the export
	 *
	 * @since 3.3.8
	 * @return void
	 */
	public function export() {
		$this->headers();
		$file = $this->get_file();
		$this->file_system->delete( $this->file );
		echo $file;
		$transient = $this->get_transient();
		$transient->delete();
		die();
	}

	/**
	 * Return the calculated completion percentage.
	 *
	 * @since 3.3.8
	 * @return float
	 */
	public function get_percentage_complete(): float {
		$total      = $this->get_or_set_total();
		$percentage = 100;

		if ( $total > 0 ) {
			$percentage = ( ( $this->per_step * $this->step ) / $total ) * 100;
		}

		if ( $percentage > 100 ) {
			$percentage = 100;
		}

		return $percentage;
	}

	/**
	 * Checks if exporting is allowed.
	 *
	 * @since 3.3.8
	 * @return bool True if exporting is allowed, false otherwise.
	 */
	public function can_export() {
		return (bool) apply_filters( 'edd_export_capability', current_user_can( 'export_shop_reports' ) );
	}

	/**
	 * Allow for prefetching of data for the remainder of the exporter.
	 * This is an empty method because it's optional.
	 *
	 * @since 3.3.8
	 * @return void
	 */
	public function pre_fetch() {}

	/**
	 * Get the columns to export.
	 *
	 * @since 3.3.8
	 * @return array
	 */
	final public function get_columns(): array {
		$columns     = $this->get_data_headers();
		$export_type = $this->get_export_type();

		// Legacy filter.
		if ( has_filter( 'edd_export_csv_cols_' . $export_type ) ) {
			$columns = apply_filters( 'edd_export_csv_cols_' . $export_type, $columns );
		}

		return apply_filters( 'edd_export_get_columns_' . $export_type, $columns );
	}

	/**
	 * Get the rows to export.
	 * The rows are defined in get_data. This method is private to allow for filtering.
	 *
	 * @since 3.3.8
	 * @return array
	 */
	final public function get_rows(): array {
		$rows = $this->get_data();
		$rows = apply_filters( 'edd_export_get_data', $rows );

		return apply_filters( 'edd_export_get_data_' . $this->get_export_type(), $rows );
	}

	/**
	 * Get the export type.
	 *
	 * @since 3.3.8
	 * @return string
	 */
	abstract protected function get_export_type(): string;

	/**
	 * Get the data to export.
	 *
	 * @since 3.3.8
	 * @return array
	 */
	abstract protected function get_data(): array;

	/**
	 * Get the columns to export.
	 *
	 * @since 3.3.8
	 * @return array
	 */
	abstract protected function get_data_headers(): array;

	/**
	 * Get the total number of items to process.
	 *
	 * @since 3.3.8
	 * @return int
	 */
	abstract protected function get_total(): int;

	/**
	 * Gets the date query.
	 *
	 * @since 3.3.8
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

	/**
	 * Output the CSV columns (headers).
	 *
	 * @since 3.3.8
	 * @return string
	 */
	private function print_columns() {
		$column_data = '';
		$columns     = $this->get_columns();
		$i           = 1;
		foreach ( $columns as $column ) {
			$column_data .= '"' . addslashes( $column ) . '"';
			$column_data .= count( $columns ) === $i ? '' : ',';
			++$i;
		}
		$column_data .= "\r\n";

		$this->stash_step_data( $column_data );

		return $column_data;
	}

	/**
	 * Output the CSV rows.
	 * Extending classes will generally not need to override this method; it's protected
	 * so that more complex exports can override it if needed.
	 *
	 * @since 3.3.8
	 * @return bool
	 */
	protected function print_rows() {
		$rows = $this->get_rows();
		if ( empty( $rows ) ) {
			return false;
		}
		$columns  = $this->get_columns();
		$row_data = '';

			// Output each row.
		foreach ( $rows as $row ) {
			$i = 1;
			foreach ( $row as $column_id => $column ) {
				// Make sure the column is valid. If not, skip it.
				if ( array_key_exists( $column_id, $columns ) ) {
					$column    = is_numeric( $column ) || ! empty( $column ) ? $column : '';
					$row_data .= '"' . addslashes( preg_replace( '/\"/', "'", $column ) ) . '"';
					$row_data .= count( $columns ) === $i ? '' : ',';
					++$i;
				}
			}
			$row_data .= "\r\n";
		}

		$this->stash_step_data( $row_data );

		return $row_data;
	}

	/**
	 * Append data to export file.
	 *
	 * @since 3.3.8
	 * @param string $data The data to add to the file.
	 * @return void
	 */
	final protected function stash_step_data( $data ) {

		$file  = $this->get_file();
		$file .= $data;
		$this->file_system->put_contents( $this->file, $file );

		// If we have no rows after this step, mark it as an empty export.
		$file_rows       = FileSystem::file( $this->file, FILE_SKIP_EMPTY_LINES );
		$default_columns = $this->get_columns();
		$default_columns = empty( $default_columns ) ? 0 : 1;

		$this->is_empty = count( $file_rows ) === $default_columns;
	}

	/**
	 * Get the number of items to process per step.
	 *
	 * @since 3.3.8
	 * @return int
	 */
	protected function get_per_step(): int {
		$export_type = $this->get_export_type();

		/**
		 * Filters the number of items to process per step for a specific export type.
		 *
		 * @since 3.3.8
		 * @param int $per_step The number of items to process per step.
		 * @return int
		 */
		$per_step = apply_filters( 'edd_export_per_step_' . $export_type, $this->per_step );

		/**
		 * Filters the number of items to process per step for any export type.
		 *
		 * @since 3.3.8
		 * @param int    $per_step    The number of items to process per step.
		 * @param string $export_type The export type.
		 * @return int
		 */
		return apply_filters( 'edd_export_per_step', $per_step, $export_type );
	}

	/**
	 * Gets the final filename.
	 *
	 * @since 3.5.0
	 * @return string
	 */
	protected function get_final_filename() {
		$date = gmdate( 'm-d-Y' );

		return "edd-export-{$this->get_export_type()}-{$date}.{$this->filetype}";
	}

	/**
	 * Set the export headers.
	 *
	 * @since 3.3.8
	 */
	private function headers() {
		edd_set_time_limit();

		nocache_headers();
		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename="' . $this->get_final_filename() . '"' );
		header( 'Expires: 0' );

		/**
		 * We need to append a BOM to the export so that Microsoft Excel knows
		 * that the file is in Unicode.
		 *
		 * @see https://github.com/easydigitaldownloads/easy-digital-downloads/issues/4859
		 */
		echo "\xEF\xBB\xBF";
	}

	/**
	 * Retrieve the file data is written to.
	 *
	 * @since 3.3.8
	 * @return string
	 */
	protected function get_file() {

		$this->maybe_set_up_file();

		if ( FileSystem::file_exists( $this->file ) ) {
			if ( ! $this->file_system->is_writable( $this->file ) ) {
				$this->is_writable = false;
			}

			return FileSystem::get_contents( $this->file );
		}

		$this->file_system->put_contents( $this->file, '' );
		$this->file_system->chmod( $this->file, 0664 );

		return '';
	}

	/**
	 * Set up the export file.
	 *
	 * @since 3.3.8
	 * @return void
	 */
	protected function maybe_set_up_file() {
		if ( $this->file ) {
			return;
		}
		$exports_dir = edd_get_exports_dir();
		$file_date   = gmdate( 'Y-m-d' );
		$export_type = $this->get_export_type();
		$file_hash   = substr( wp_hash( "edd-{$export_type}-export", 'nonce' ), 0, 8 );
		$filename    = sprintf(
			'edd-%1$s-export-%2$s-%3$s.%4$s',
			$export_type,
			$file_date,
			$file_hash,
			$this->filetype
		);
		$this->file  = trailingslashit( $exports_dir ) . $filename;

		if ( ! $this->file_system->is_writable( $exports_dir ) ) {
			$this->is_writable = false;
		}
	}

	/**
	 * Get the transient.
	 *
	 * @since 3.3.8
	 * @return Transient
	 */
	protected function get_transient() {
		return new Transient( 'edd_export_' . $this->get_export_type() . '_total' );
	}

	/**
	 * Get or set the total number of items to process.
	 *
	 * @since 3.3.8
	 * @return int
	 */
	private function get_or_set_total(): int {
		$transient = $this->get_transient();
		$total     = $transient->get();
		if ( $total ) {
			return $total;
		}

		$total = $this->get_total();
		if ( ! $total ) {
			return 0;
		}

		$transient->set( $total );

		return $total;
	}
}
