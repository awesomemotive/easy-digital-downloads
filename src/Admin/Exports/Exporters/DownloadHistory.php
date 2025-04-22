<?php
/**
 * Batch File Downloads Export Class.
 *
 * This class handles exporting file download logs in batches.
 *
 * @package     EDD\Admin\Reporting\Export
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       2.4
 */

namespace EDD\Admin\Exports\Exporters;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

use EDD\Admin\Exports\Legacy\BatchExport;

/**
 * DownloadHistory Class
 *
 * @since 2.4
 * @since 3.0 Refactored to use new query methods.
 * @since 3.3.8 Updated to extend the Exporter class.
 */
class DownloadHistory extends Exporter {

	/**
	 * The number of items to process per step.
	 *
	 * @since 3.3.8
	 * @var int
	 */
	protected $per_step = 100;

	/**
	 * The download ID to export.
	 *
	 * @var int
	 */
	private $download_id = '';

	/**
	 * The customer ID to export.
	 *
	 * @since 3.3.8
	 * @var string
	 */
	private $customer_id = '';

	/**
	 * Set the CSV columns.
	 *
	 * @since 2.4
	 * @since 3.0 Updated to add 'User Agent' column.
	 *
	 * @return array
	 */
	protected function get_data_headers(): array {
		return array(
			'date'       => __( 'Date', 'easy-digital-downloads' ),
			'user'       => __( 'Downloaded by', 'easy-digital-downloads' ),
			'ip'         => __( 'IP Address', 'easy-digital-downloads' ),
			'user_agent' => __( 'User Agent', 'easy-digital-downloads' ),
			'download'   => __( 'Product', 'easy-digital-downloads' ),
			'file'       => __( 'File', 'easy-digital-downloads' ),
		);
	}

	/**
	 * Get the export data.
	 *
	 * @since 2.4
	 * @since 3.0 Refactored to use new query methods.
	 *
	 * @return array $data The data for the CSV file.
	 */
	public function get_data(): array {
		$data = array();

		$args = array_merge(
			$this->get_args(),
			array(
				'number' => $this->per_step,
				'offset' => ( $this->step * $this->per_step ) - $this->per_step,
			)
		);

		$logs = edd_get_file_download_logs( $args );
		if ( empty( $logs ) ) {
			return $data;
		}

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

		return $data;
	}

	/**
	 * Return the calculated completion percentage.
	 *
	 * @since 2.4
	 * @since 3.0 Updated to use new query methods.
	 *
	 * @return int Percentage complete.
	 */
	protected function get_total(): int {
		return edd_count_file_download_logs( $this->get_args() );
	}

	/**
	 * Set the properties specific to the file downloads export.
	 *
	 * @since 2.4
	 * @param array $request The request data.
	 */
	public function set_properties( $request ): void {
		if ( ! empty( $request['file-download-export-start'] ) ) {
			$this->start = sanitize_text_field( $request['file-download-export-start'] );
		}
		if ( ! empty( $request['file-download-export-end'] ) ) {
			$this->end = sanitize_text_field( $request['file-download-export-end'] );
		}
		if ( ! empty( $request['download_id'] ) ) {
			$this->download_id = absint( $request['download_id'] );
		}
		if ( ! empty( $request['customer_id'] ) ) {
			$this->customer_id = sanitize_text_field( $request['customer_id'] );
		}
	}

	/**
	 * Gets the export type.
	 *
	 * @since 3.3.8
	 * @return string
	 */
	protected function get_export_type(): string {
		return 'file_downloads';
	}

	/**
	 * Get the arguments for the query.
	 *
	 * @since 3.3.8
	 * @return array
	 */
	private function get_args(): array {
		$args = array();

		if ( ! empty( $this->start ) || ! empty( $this->end ) ) {
			$args['date_created_query'] = $this->get_date_query();
		}

		if ( ! empty( $this->download_id ) ) {
			$args['product_id'] = $this->download_id;
		}

		if ( ! empty( $this->customer_id ) ) {
			$args['customer_id'] = $this->customer_id;
		}

		return $args;
	}
}
