<?php
/**
 * Batch API Request Logs Export Class
 *
 * This class handles API request logs export
 *
 * @package     EDD\Admin\Exports\Exporters
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       2.7
 */

namespace EDD\Admin\Exports\Exporters;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

/**
 * APIRequests Class.
 *
 * @since 2.7
 * @since 3.3.8 Extends Exporter.
 */
class APIRequests extends Exporter {

	/**
	 * Get the export type.
	 *
	 * @since 3.3.8
	 * @return string
	 */
	protected function get_export_type(): string {
		return 'api_requests';
	}

	/**
	 * Set the CSV columns.
	 *
	 * @since 2.7
	 * @return array The columns for the CSV file.
	 */
	protected function get_data_headers(): array {
		return array(
			'ID'      => __( 'Log ID', 'easy-digital-downloads' ),
			'request' => __( 'API Request', 'easy-digital-downloads' ),
			'ip'      => __( 'IP Address', 'easy-digital-downloads' ),
			'user'    => __( 'API User', 'easy-digital-downloads' ),
			'key'     => __( 'API Key', 'easy-digital-downloads' ),
			'version' => __( 'API Version', 'easy-digital-downloads' ),
			'speed'   => __( 'Request Speed', 'easy-digital-downloads' ),
			'date'    => __( 'Date', 'easy-digital-downloads' ),
		);
	}

	/**
	 * Get the export data.
	 *
	 * @since 2.7
	 * @since 3.0 Updated to use new query methods.
	 *
	 * @return array $data The data for the CSV file.
	 */
	protected function get_data(): array {
		$data = array();
		$args = array_merge(
			array(
				'number' => $this->per_step,
				'offset' => ( $this->step * $this->per_step ) - $this->per_step,
			),
			$this->get_base_args()
		);

		$logs = edd_get_api_request_logs( $args );

		foreach ( $logs as $log ) {
			/** @var EDD\Logs\Api_Request_Log $log */

			$data[] = array(
				'ID'      => $log->id,
				'request' => $log->request,
				'ip'      => $log->ip,
				'user'    => $log->user_id,
				'key'     => $log->api_key,
				'version' => $log->version,
				'speed'   => $log->time,
				'date'    => $log->date_created,
			);
		}

		return $data;
	}

	/**
	 * Get the date query for the API request logs.
	 */
	public function set_properties( $request ): void {
		if ( isset( $request['api-requests-export-start'] ) ) {
			$this->start = sanitize_text_field( $request['api-requests-export-start'] );
		}
		if ( isset( $request['api-requests-export-end'] ) ) {
			$this->end = sanitize_text_field( $request['api-requests-export-end'] );
		}
	}

	/**
	 * Gets the total number of items to export.
	 *
	 * @since 3.3.8
	 * @return int
	 */
	protected function get_total(): int {
		return edd_count_api_request_logs( $this->get_base_args() );
	}

	/**
	 * Get the base args for the API request logs.
	 *
	 * @since 3.3.8
	 * @return array
	 */
	private function get_base_args(): array {
		$args = array();
		if ( ! empty( $this->start ) || ! empty( $this->end ) ) {
			$args['date_query'] = $this->get_date_query();
		}

		return $args;
	}
}
