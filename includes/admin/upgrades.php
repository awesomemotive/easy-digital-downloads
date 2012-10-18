<?php

/**
 * Upgrade Functions
 *
 * @package     Easy Digital Downloads
 * @subpackage  Download Functions
 * @copyright   Copyright (c) 2012, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       x.x.x
*/


function edd_schedule_upgrades() {

	if( ! get_option( 'edd_logs_upgraded' ) ) {

		//wp_schedule_single_event( time()+3600, 'edd_do_upgrades' );

	}
}
add_action( 'admin_init', 'edd_schedule_upgrades' );


function edd_perform_upgrades() {

	// TODO make this run via a cron job

	if( ! get_option( 'edd_logs_upgraded' ) ) {
	
		//edd_convert_purchase_logs();

	}

	if( ! get_option( 'edd_logs_upgraded' ) ) {
	
		//edd_convert_download_logs();

	}
}
add_action( 'edd_do_upgrades', 'edd_perform_upgrades' );



/**
 * Converts old sales log to new logging system
 * 
 * @access      private
 * @since       x.x.x
 * @return      void
*/

function edd_convert_purchase_logs() {

	$downloads = get_posts( array( 
		'post_type' 		=> 'download', 
		'posts_per_page' 	=> -1, 
		'post_status' 		=> 'any' 
	) );

	if( $downloads ) {

		$edd_log = new EDD_Logging();

		foreach( $downloads as $download ) {

			$logs = edd_get_download_sales_log( $download->ID, false );

			if( $logs ) {
				foreach( $logs['sales'] as $sale ) {

					$log_data = array(
						'post_parent'	=> $download->ID,
						'post_date'		=> $sale['date'],
						'log_type'		=> 'sale'

					);

					$log_meta = array(
						'payment_id'=> $sale['payment_id']
					);

					$log = $edd_log->insert_log( $log_data, $log_meta );
				
				}
			
			}

		}

	}

}


/**
 * Converts old file download logs to new logging system
 * 
 * @access      private
 * @since       x.x.x
 * @return      void
*/

function edd_convert_download_logs() {

	$downloads = get_posts( array( 
		'post_type' 		=> 'download', 
		'posts_per_page' 	=> -1, 
		'post_status' 		=> 'publish' 
	) );

	if( $downloads ) {

		$edd_log = new EDD_Logging();

		foreach( $downloads as $download ) {

			$logs = edd_get_file_download_log( $download->ID, false );

			if( $logs ) {
				foreach( $logs['downloads'] as $log ) {

					$log_data = array(
						'post_parent'	=> $download->ID,
						'post_date'		=> $log['date'],
						'log_type'		=> 'file_download'

					);

					$log_meta = array(
						'user_info'	=> $log['user_info'],
						'file_id'	=> $log['file_id'],
						'ip'		=> $log['ip']
					);

					$log = $edd_log->insert_log( $log_data, $log_meta );
				
				}
			
			}

		}

	}

}