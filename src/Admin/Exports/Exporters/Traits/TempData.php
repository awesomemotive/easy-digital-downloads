<?php
/**
 * Exporter temporary data trait.
 *
 * Use this when the export data needs to be collected in a temporary file and combined at the end.
 *
 * @package     EDD\Admin\Exports\Exporters\Traits
 * @copyright   Copyright (c) 2025, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.3.8
 */

namespace EDD\Admin\Exports\Exporters\Traits;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

use EDD\Utils\FileSystem;

/**
 * Temporary data trait.
 *
 * @since 3.3.8
 */
trait TempData {

	/**
	 * The temporary file location.
	 *
	 * @since 3.3.8
	 * @var string
	 */
	private $temp_file;

	/**
	 * Output the CSV rows.
	 *
	 * @since 3.3.8
	 * @return bool
	 */
	protected function print_rows() {
		$rows = $this->get_rows();
		if ( ! empty( $rows ) ) {
			return $this->stash_temp_data( $rows );
		}

		$this->get_temp_file();

		$temp_data = FileSystem::get_contents( $this->temp_file );
		$data      = json_decode( $temp_data, true );
		if ( empty( $data ) ) {
			return false;
		}

		$row_data = '';
		$columns  = $this->get_columns();
		foreach ( $data as $row ) {
			$i = 1;
			foreach ( $row as $column_id => $cell ) {
				if ( ! array_key_exists( $column_id, $columns ) ) {
					continue;
				}
				$row_data .= '"' . addslashes( preg_replace( '/\"/', "'", $cell ) ) . '"';
				$row_data .= count( $columns ) === $i ? '' : ',';
				++$i;
			}
			$row_data .= "\r\n";
		}

		$this->stash_step_data( $row_data );
		if ( FileSystem::file_exists( $this->temp_file ) ) {
			FileSystem::get_fs()->delete( $this->temp_file );
		}

		return false;
	}

	/**
	 * Append data to export file.
	 *
	 * @since 3.3.8
	 * @param array $data Data to append to the export file.
	 * @return bool
	 */
	private function stash_temp_data( $data = array() ) {
		if ( ! is_array( $data ) ) {
			return false;
		}

		$this->get_temp_file();
		$current_file = FileSystem::get_contents( $this->temp_file );
		$current_data = json_decode( $current_file, true );

		if ( empty( $current_data ) ) {
			$current_data = array();
		}

		$format = array_keys( $this->get_columns() );
		$format = array_fill_keys( $format, '' );

		foreach ( $data as $key => $entry ) {
			if ( ! array_key_exists( $key, $current_data ) ) {
				$current_data[ $key ] = wp_parse_args( $entry, $format );

				continue;
			}

			foreach ( $entry as $entry_key => $entry_value ) {
				if ( ! array_key_exists( $entry_key, $current_data[ $key ] ) ) {
					$current_data[ $key ][ $entry_key ] = $entry_value;

					continue;
				}

				if ( is_array( $entry_value ) ) {
					$current_data[ $key ][ $entry_key ] = array_unique( array_merge( $current_data[ $key ][ $entry_key ], $entry_value ) );

					continue;
				}

				if ( is_numeric( $entry_value ) ) {
					$current_value                      = (float) $current_data[ $key ][ $entry_key ];
					$current_data[ $key ][ $entry_key ] = $current_value + $entry_value;
				}
			}
		}

		if ( ! empty( $current_data ) ) {
			$current_data = json_encode( $current_data );
			FileSystem::put_contents( $this->temp_file, $current_data );
		}

		return $current_data;
	}

	/**
	 * Set up the temporary file location data.
	 *
	 * @since 3.3.8
	 */
	private function get_temp_file() {
		if ( $this->temp_file && FileSystem::file_exists( $this->temp_file ) ) {
			return;
		}
		$upload_dir      = edd_get_exports_dir();
		$file_date       = gmdate( 'Y-m-d' );
		$file_hash       = substr( wp_hash( 'edd-' . $this->get_export_type() . '-export', 'nonce' ), 0, 8 );
		$temp_filename   = sprintf(
			'edd-%1$s-export-%2$s-%3$s.json',
			$this->get_export_type(),
			$file_date,
			$file_hash
		);
		$this->temp_file = trailingslashit( $upload_dir ) . $temp_filename;

		if ( ! FileSystem::file_exists( $this->temp_file ) ) {
			FileSystem::put_contents( $this->temp_file, '' );
		}
	}
}
