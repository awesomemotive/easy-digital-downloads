<?php

/**
 * Upgrade Functions
 *
 * @package     Easy Digital Downloads
 * @subpackage  Download Functions
 * @copyright   Copyright (c) 2012, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.3.1
*/



function edd_trigger_upgrades() {

	$edd_version = get_option( 'edd_version' );

	if( ! $edd_version ) {
		// 1.3 is the first version to use this option so we must add it
		$edd_version = '1.3';
		add_option( 'edd_version', $edd_version );
	}

	if( version_compare( EDD_VERSION, $edd_version, '>' ) ) {
		edd_v131_upgrades();
		update_option( 'edd_version', '1.3.1' );
	}

}
add_action( 'admin_init', 'edd_trigger_upgrades' );


/**
 * Converts old sale and file download logs to new logging system
 * 
 * @access      private
 * @since       1.3.1
 * @return      void
*/

function edd_v131_upgrades() {

	ignore_user_abort(true);
	set_time_limit(0);

	$downloads = get_posts( array( 
		'post_type' 		=> 'download', 
		'posts_per_page' 	=> -1, 
		'post_status' 		=> 'publish' 
	) );

	if( $downloads ) {

		$edd_log = new EDD_Logging();

		foreach( $downloads as $download ) {

			// convert sale logs
			$sale_logs = edd_get_download_sales_log( $download->ID, false );

			if( $sale_logs ) {
				foreach( $sale_logs['sales'] as $sale ) {


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

			// convert file download logs
			$file_logs = edd_get_file_download_log( $download->ID, false );

			if( $file_logs ) {
				foreach( $file_logs['downloads'] as $log ) {
					
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