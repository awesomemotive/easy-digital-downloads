<?php
/**
 * Customers Export Class
 *
 * This class handles customer export
 *
 * @package     EDD
 * @subpackage  Admin/Reports
 * @copyright   Copyright (c) 2013, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.4.4
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * EDD_Download_History_Export Class
 *
 * @since 1.4.4
 */
class EDD_Download_History_Export extends EDD_Export {
	/**
	 * Our export type. Used for export-type specific filters/actions
	 *
	 * @var string
	 * @since 1.4.4
	 */
	public $export_type = 'download_history';

	/**
	 * Set the CSV columns
	 *
	 * @access public
	 * @since 1.4.4
	 * @return array $cols All the columns
	 */
	public function csv_cols() {
		$cols = array(
			'date'     => __( 'Date',   'edd' ),
			'user'     => __( 'Downloaded by', 'edd' ),
			'ip'       => __( 'IP Address', 'edd' ),
			'download' => __( 'Product', 'edd' ),
			'file'     => __( 'File', 'edd' )
		);
		return $cols;
	}

	/**
	 * Get the Export Data
	 *
	 * @access public
	 * @since 1.4.4
 	 * @global object $edd_logs EDD Logs Object
	 * @return array $data The data for the CSV file
	 */
	public function get_data() {
		global $edd_logs;

		$data = array();

		$logs = $edd_logs->get_connected_logs( array(
			'nopaging' => true,
			'log_type' => 'file_download',
			'month'    => date( 'n' ),
			'year'     => date( 'Y' )
		) );

		if ( $logs ) {
			foreach ( $logs as $log ) {
				$user_info = get_post_meta( $log->ID, '_edd_log_user_info', true );
				$files     = edd_get_download_files( $log->post_parent );
				$file_id   = (int) get_post_meta( $log->ID, '_edd_log_file_id', true );
				$file_name = isset( $files[ $file_id ]['name'] ) ? $files[ $file_id ]['name'] : null;
				$user      = get_userdata( $user_info['id'] );
				$user      = $user ? $user->user_login : $user_info['email'];

				$data[]    = array(
					'date'     => $log->post_date,
					'user'     => $user,
					'ip'       => get_post_meta( $log->ID, '_edd_log_ip', true ),
					'download' => get_the_title( $log->post_parent ),
					'file'     => $file_name
				);
			}
		}

		$data = apply_filters( 'edd_export_get_data', $data );
		$data = apply_filters( 'edd_export_get_data_' . $this->export_type, $data );

		return $data;
	}
}