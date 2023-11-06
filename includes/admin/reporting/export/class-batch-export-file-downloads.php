<?php
/**
 * Batch File Downloads Export Class
 *
 * This class handles file downloads export
 *
 * @package     EDD
 * @subpackage  Admin/Reporting/Export
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       2.4
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * EDD_Batch_File_Downloads_Export Class
 *
 * @since 2.4
 * @since 3.0 Refactored to use new query methods.
 */
class EDD_Batch_File_Downloads_Export extends EDD_Batch_Export {

	/**
	 * Our export type. Used for export-type specific filters/actions.
	 *
	 * @var string
	 * @since 2.4
	 */
	public $export_type = 'file_downloads';

	/**
	 * The download ID to export.
	 *
	 * @var int
	 */
	private $download_id;

	/**
	 * Set the CSV columns.
	 *
	 * @since 2.4
	 * @since 3.0 Updated to add 'User Agent' column.
	 *
	 * @return array $cols All the columns.
	 */
	public function csv_cols() {
		$cols = array(
			'date'       => __( 'Date', 'easy-digital-downloads' ),
			'user'       => __( 'Downloaded by', 'easy-digital-downloads' ),
			'ip'         => __( 'IP Address', 'easy-digital-downloads' ),
			'user_agent' => __( 'User Agent', 'easy-digital-downloads' ),
			'download'   => __( 'Product', 'easy-digital-downloads' ),
			'file'       => __( 'File', 'easy-digital-downloads' ),
		);

		return $cols;
	}

	/**
	 * Get the export data.
	 *
	 * @since 2.4
	 * @since 3.0 Refactored to use new query methods.
	 *
	 * @return array $data The data for the CSV file.
	 */
	public function get_data() {
		$data = array();

		$args = array(
			'number' => 30,
			'offset' => ( $this->step * 30 ) - 30,
		);

		if ( ! empty( $this->start ) || ! empty( $this->end ) ) {
			$args['date_created_query'] = $this->get_date_query();
		}

		if ( 0 !== $this->download_id ) {
			$args['product_id'] = $this->download_id;
		}

		$logs = edd_get_file_download_logs( $args );

		foreach ( $logs as $log ) {
			/** @var EDD\Logs\File_Download_Log $log */

			$files     = edd_get_download_files( $log->product_id );
			$file_id   = $log->file_id;
			$file_name = isset( $files[ $file_id ]['name'] ) ? $files[ $file_id ]['name'] : null;
			$customer  = edd_get_customer( $log->customer_id );

			if ( $customer ) {
				$customer = $customer->email;
				if ( ! empty( $customer->name ) ) {
					$customer = $customer->name;
					if ( preg_match( '~^[+\-=@]~m', $customer ) ) {
						$customer = "'{$customer}";
					}
				}
			} else {
				$order = edd_get_order( $log->order_id );

				if ( $order ) {
					$customer = $order->email;
				}
			}

			$data[] = array(
				'date'       => $log->date_created,
				'user'       => $customer,
				'ip'         => $log->ip,
				'user_agent' => $log->user_agent,
				'download'   => get_the_title( $log->product_id ),
				'file'       => $file_name,
			);
		}

		$data = apply_filters( 'edd_export_get_data', $data );
		$data = apply_filters( 'edd_export_get_data_' . $this->export_type, $data );

		return ! empty( $data )
			? $data
			: false;
	}

	/**
	 * Return the calculated completion percentage.
	 *
	 * @since 2.4
	 * @since 3.0 Updated to use new query methods.
	 *
	 * @return int Percentage complete.
	 */
	public function get_percentage_complete() {
		$args = array(
			'fields' => 'ids',
		);

		if ( ! empty( $this->start ) || ! empty( $this->end ) ) {
			$args['date_created_query'] = $this->get_date_query();
		}

		if ( 0 !== $this->download_id ) {
			$args['download_id'] = $this->download_id;
		}

		$total      = edd_count_file_download_logs( $args );
		$percentage = 100;

		if ( $total > 0 ) {
			$percentage = ( ( 30 * $this->step ) / $total ) * 100;
		}

		if ( $percentage > 100 ) {
			$percentage = 100;
		}

		return $percentage;
	}

	public function set_properties( $request ) {
		$this->start       = isset( $request['file-download-export-start'] ) ? sanitize_text_field( $request['file-download-export-start'] ) : '';
		$this->end         = isset( $request['file-download-export-end'] ) ? sanitize_text_field( $request['file-download-export-end'] ) : '';
		$this->download_id = isset( $request['download_id'] ) ? absint( $request['download_id'] ) : 0;
	}
}
