<?php
/**
 * Batch API Request Logs Export Class
 *
 * This class handles API request logs export
 *
 * @package     EDD
 * @subpackage  Admin/Reports
 * @copyright   Copyright (c) 2016, Sunny Ratilal
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       2.7
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * EDD_Batch_API_Requests_Export Class
 *
 * @since 2.7
 */
class EDD_Batch_API_Requests_Export extends EDD_Batch_Export {
	/**
	 * Our export type. Used for export-type specific filters/actions
	 *
	 * @var string
	 * @since 2.7
	 */
	public $export_type = 'api_requests';

	/**
	 * Set the CSV columns
	 *
	 * @access public
	 * @since 2.7
	 * @return array $cols All the columns
	 */
	public function csv_cols() {
		$cols = array(
			'ID'      => __( 'Log ID',   'easy-digital-downloads' ),
			'request' => __( 'API Request', 'easy-digital-downloads' ),
			'ip'      => __( 'IP Address', 'easy-digital-downloads' ),
			'user'    => __( 'API User', 'easy-digital-downloads' ),
			'key'     => __( 'API Key', 'easy-digital-downloads' ),
			'version' => __( 'API Version', 'easy-digital-downloads' ),
			'speed'   => __( 'Request Speed', 'easy-digital-downloads' ),
			'date'    => __( 'Date', 'easy-digital-downloads' )
		);

		return $cols;
	}

	/**
	 * Get the Export Data
	 *
	 * @access public
	 * @since 2.7
 	 * @global object $edd_logs EDD Logs Object
	 * @return array $data The data for the CSV file
	 */
	public function get_data() {
		global $edd_logs;

		$data = array();

		$args = array(
			'log_type'       => 'api_request',
			'posts_per_page' => 30,
			'paged'          => $this->step
		);

		if ( ! empty( $this->start ) || ! empty( $this->end ) ) {
			$args['date_query'] = array(
				array(
					'after'     => date( 'Y-n-d H:i:s', strtotime( $this->start ) ),
					'before'    => date( 'Y-n-d H:i:s', strtotime( $this->end ) ),
					'inclusive' => true
				)
			);
		}

		$logs = $edd_logs->get_connected_logs( $args );

		if ( $logs ) {
			foreach ( $logs as $log ) {
				$data[] = array(
					'ID'      => $log->ID,
					'request' => get_post_field( 'post_excerpt', $log->ID ),
					'ip'      => get_post_meta( $log->ID, '_edd_log_request_ip', true ),
					'user'    => get_post_meta( $log->ID, '_edd_log_user', true ),
					'key'     => get_post_meta( $log->ID, '_edd_log_key', true ),
					'version' => get_post_meta( $log->ID, '_edd_log_version', true ),
					'speed'   => get_post_meta( $log->ID, '_edd_log_time', true ),
					'date'    => $log->post_date
				);
			}

			$data = apply_filters( 'edd_export_get_data', $data );
			$data = apply_filters( 'edd_export_get_data_' . $this->export_type, $data );

			return $data;
		}

		return false;
	}

	/**
	 * Return the calculated completion percentage
	 *
	 * @since 2.7
	 * @return int
	 */
	public function get_percentage_complete() {
		global $edd_logs;

		$args = array(
			'post_type'		   => 'edd_log',
			'posts_per_page'   => -1,
			'post_status'	   => 'publish',
			'fields'           => 'ids',
			'tax_query'        => array(
				array(
					'taxonomy' 	=> 'edd_log_type',
					'field'		=> 'slug',
					'terms'		=> 'api_request'
				)
			),
			'date_query'        => array(
				array(
					'after'     => date( 'Y-n-d H:i:s', strtotime( $this->start ) ),
					'before'    => date( 'Y-n-d H:i:s', strtotime( $this->end ) ),
					'inclusive' => true
				)
			)
		);

		$logs       = new WP_Query( $args );
		$total      = (int) $logs->post_count;
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
		$this->start = isset( $request['start'] ) ? sanitize_text_field( $request['start'] ) : '';
		$this->end   = isset( $request['end'] )   ? sanitize_text_field( $request['end'] )   : '';
	}
}