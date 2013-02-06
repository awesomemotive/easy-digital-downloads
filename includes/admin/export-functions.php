<?php
/**
 * Exports Functions
 *
 * These are functions are used for exporting data from Easy Digital Downloads.
 *
 * @package     Easy Digital Downloads
 * @subpackage  Export Functions
 * @copyright   Copyright (c) 2012, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;


require_once EDD_PLUGIN_DIR . 'includes/admin/reporting/class-export.php';
require_once EDD_PLUGIN_DIR . 'includes/admin/reporting/class-export-customers.php';
require_once EDD_PLUGIN_DIR . 'includes/admin/reporting/class-export-payments.php';


/**
 * Export all Payment History to CSV
 *
 * @access      private
 * @since       1.2
 * @return      void
 */

function edd_export_payment_history() {

	$payments_export = new EDD_Payments_Export();

	$payments_export->export();

}
add_action( 'edd_payment_export', 'edd_export_payment_history' );


/**
 * Export all customers to CSV
 *
 * Using wpdb directly for performance reasons (workaround of calling all posts and fetch data respectively)
 *
 * @access      private
 * @since       1.2
 * @return      void
 */

function edd_export_all_customers() {

	$customer_export = new EDD_Customers_Export();

	$customer_export->export();

}
add_action( 'edd_email_export', 'edd_export_all_customers' );


/**
 * Export all downloads to CSV
 *
 * @access      private
 * @since       1.2
 * @return      void
 */

function edd_export_all_downloads_history() {
	if ( current_user_can( 'administrator' ) ) {

		ignore_user_abort( true );

		if ( ! edd_is_func_disabled( 'set_time_limit' ) && ! ini_get( 'safe_mode' ) )
			set_time_limit( 0 );

		$report_args = array(
			'post_type'  => 'download',
			'post_status' => 'publish',
			'posts_per_page'=> -1,
			'order'   => 'post_date'
		);

		$downloads = get_posts( $report_args );

		if ( ! empty( $downloads ) ) {
			header( "Content-type: text/csv" );
			$today = date_i18n( "Y-m-d" );
			header( "Content-Disposition: attachment; filename=user_downloads_history-$today.csv" );
			header( "Pragma: no-cache" );
			header( "Expires: 0" );

			echo '"' . __( 'Date', 'edd' ) .  '",';
			echo '"' . __( 'Downloaded by', 'edd' ) .  '",';
			echo '"' . __( 'IP Address', 'edd' ) .  '",';
			echo '"' . __( 'Product', 'edd' ) .  '",';
			echo '"' . __( 'File', 'edd' ) .  '"';
			echo "\r\n";

			foreach ( $downloads as $report ) {
				$page = isset( $_GET['paged'] ) ? intval( $_GET['paged'] ) : 1;

				$download_log = new EDD_Logging();

				$file_downloads = $download_log->get_connected_logs(
					array(
						'post_parent' 	=> $report->ID,
						'posts_per_page'=> -1,
						'log_type'		=> 'file_download',
						'monthnum'		=> date( 'n' ),
						'year'			=> date( 'Y' )

					)
				);

				$files = edd_get_download_files( $report->ID );

				if ( is_array( $file_downloads ) ) {
					foreach ( $file_downloads as $log ) {
						$user_info  = get_post_meta( $log->ID, '_edd_log_user_info', true );
						$file_id  = (int) get_post_meta( $log->ID, '_edd_log_file_id', true );
						$ip   = get_post_meta( $log->ID, '_edd_log_ip', true );

						$user_id = isset( $user_info['id'] ) ? $user_info['id'] : 0;

						$user_data = get_userdata( $user_id );
						if ( $user_data ) {
							$name = $user_data->display_name;
						} else {
							$name = $user_info['email'];
						}

						$file_id = (int)$file_id !== false ? $file_id : 0;
						$file_name = isset( $files[ $file_id ]['name'] ) ? $files[ $file_id ]['name'] : null;


						echo '"' . $log->post_date . '",';
						echo '"' . $name . '",';
						echo '"' . $ip . '",';
						echo '"' . html_entity_decode( get_the_title( $report->ID ) ) . '",';
						echo '"' . $file_name . '"';
						echo "\r\n";
					} // Endforeach
				}
			}

			exit;
		}
	} else {
		wp_die( __( 'Export not allowed for non-administrators.', 'edd' ) );
	}
}
add_action( 'edd_downloads_history_export', 'edd_export_all_downloads_history' );