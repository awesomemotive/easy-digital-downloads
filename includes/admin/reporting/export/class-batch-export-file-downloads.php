<?php
/**
 * Batch File Downloads Export Class
 *
 * This class handles file downloads export
 *
 * @package     EDD
 * @subpackage  Admin/Reports
 * @copyright   Copyright (c) 2015, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       2.4
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * EDD_Batch_File_Downloads_Export Class
 *
 * @since 2.4
 */
class EDD_Batch_File_Downloads_Export extends EDD_Batch_Export {

	/**
	 * Our export type. Used for export-type specific filters/actions
	 *
	 * @var string
	 * @since 2.4
	 */
	public $export_type = 'file_downloads';

	/**
	 * Set the CSV columns
	 *
	 * @since 2.4
	 * @return array $cols All the columns
	 */
	public function csv_cols() {

		$cols = array(
			'date'     => __( 'Date',   'easy-digital-downloads' ),
			'user'     => __( 'Downloaded by', 'easy-digital-downloads' ),
			'ip'       => __( 'IP Address', 'easy-digital-downloads' ),
			'download' => __( 'Product', 'easy-digital-downloads' ),
			'file'     => __( 'File', 'easy-digital-downloads' )
		);

		return $cols;
	}

	/**
	 * Get the Export Data
	 *
	 * @since 2.4
 	 * @global object $edd_logs EDD Logs Object
	 * @return array $data The data for the CSV file
	 */
	public function get_data() {

		global $edd_logs;

		$data = array();

		$args = array(
			'log_type'       => 'file_download',
			'posts_per_page' => 30,
			'paged'          => $this->step
		);

		if( ! empty( $this->start ) || ! empty( $this->end ) ) {

			$args['date_query'] = array(
				array(
					'after'     => date( 'Y-n-d H:i:s', strtotime( $this->start ) ),
					'before'    => date( 'Y-n-d H:i:s', strtotime( $this->end ) ),
					'inclusive' => true
				)
			);

		}

		if ( 0 !== $this->download_id ) {
			$args['post_parent'] = $this->download_id;
		}

		$logs = $edd_logs->get_connected_logs( $args );

		if ( $logs ) {
			foreach ( $logs as $log ) {
				$customer_id = get_post_meta( $log->ID, '_edd_log_customer_id', true );
				$customer    = false;
				if ( ! empty( $customer_id ) ) {
					$customer = new EDD_Customer( $customer_id );
				}

				$files       = edd_get_download_files( $log->post_parent );
				$file_id     = (int) get_post_meta( $log->ID, '_edd_log_file_id', true );
				$file_name   = isset( $files[ $file_id ]['name'] ) ? $files[ $file_id ]['name'] : null;

				if ( ! empty( $customer ) ) {
					$user = ! empty( $customer->name ) ? $customer->name : $customer->email;
				} else {
					$payment_id = get_post_meta( $log->ID, '_edd_log_payment_id', true );
					$user       = edd_get_payment_user_email( $payment_id );
				}

				$data[]    = array(
					'date'     => $log->post_date,
					'user'     => $user,
					'ip'       => get_post_meta( $log->ID, '_edd_log_ip', true ),
					'download' => get_the_title( $log->post_parent ),
					'file'     => $file_name
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
	 * @since 2.4
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
					'terms'		=> 'file_download'
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

		if ( 0 !== $this->download_id ) {
			$args['post_parent'] = $this->download_id;
		}

		$logs       = new WP_Query( $args );
		$total      = (int) $logs->post_count;
		$percentage = 100;

		if( $total > 0 ) {
			$percentage = ( ( 30 * $this->step ) / $total ) * 100;
		}

		if( $percentage > 100 ) {
			$percentage = 100;
		}

		return $percentage;
	}

	public function set_properties( $request ) {
		$this->start       = isset( $request['start'] )         ? sanitize_text_field( $request['start'] ) : '';
		$this->end         = isset( $request['end']  )          ? sanitize_text_field( $request['end']  )  : '';
		$this->download_id = isset( $request['download_id'] )   ? absint( $request['download_id'] )        : 0;
	}
}
