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

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Display upgrade notices
 *
 * @access      private
 * @since       1.3.1
 * @return      void
*/


function edd_show_upgrade_notices() {

	if( isset( $_GET['page'] ) && $_GET['page'] == 'edd-upgrades' )
		return; // don't show notices on the upgrades page

	$edd_version = get_option( 'edd_version' );

	if( ! $edd_version ) {
		// 1.3 is the first version to use this option so we must add it
		$edd_version = '1.3';
	}

	if( ! get_option( 'edd_payment_totals_upgraded' ) && ! get_option( 'edd_version' ) ) {

		if( wp_count_posts( 'edd_payment' )->publish < 1 )
			return; // no payment exist yet

		// the payment history needs updated for version 1.2
		$url = add_query_arg( 'edd-action', 'upgrade_payments' );
		$upgrade_notice = sprintf( __( 'The Payment History needs to be updated. %s', 'edd' ), '<a href="' . wp_nonce_url( $url, 'edd_upgrade_payments_nonce' ) . '">' . __( 'Click to Upgrade', 'edd' ) . '</a>' );
		add_settings_error( 'edd-notices', 'edd-payments-upgrade', $upgrade_notice, 'error' );
	}

	if( version_compare( $edd_version, '1.3.2', '<' ) && ! get_option( 'edd_logs_upgraded' ) ) {
		printf(
			'<div class="updated"><p>' . esc_html__( 'The purchase and file download history in Easy Digital Downloads needs upgraded, click %shere%s to start the upgrade.', 'edd' ) . '</p></div>',
			'<a href="' . esc_url( admin_url( 'options.php?page=edd-upgrades' ) ) . '">',
			'</a>'
		);
	}

	if( version_compare( $edd_version, '1.3.4', '<' ) ) {
		printf(
			'<div class="updated"><p>' . esc_html__( 'Easy Digital Downloads needs to upgrade the plugin pages, click %shere%s to start the upgrade.', 'edd' ) . '</p></div>',
			'<a href="' . esc_url( admin_url( 'options.php?page=edd-upgrades' ) ) . '">',
			'</a>'
		);
	}

}
add_action( 'admin_notices', 'edd_show_upgrade_notices' );


/**
 * Triggers all upgrade functions
 *
 * This function is usually triggered via ajax
 *
 * @access      private
 * @since       1.3.1
 * @return      void
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
	}

	if( version_compare( $edd_version, '1.3.4', '<' ) ) {
		edd_v134_upgrades();
	}

	update_option( 'edd_version', EDD_VERSION );

	if( DOING_AJAX )
		die( 'complete' ); // let ajax know we are done

}
add_action( 'wp_ajax_edd_trigger_upgrades', 'edd_trigger_upgrades' );


/**
 * Converts old sale and file download logs to new logging system
 *
 * @access      private
 * @since       1.3.1
 * @return      void
*/

function edd_v131_upgrades() {

	if( get_option( 'edd_logs_upgraded' ) )
		return;

	if( version_compare( get_option( 'edd_version' ), '1.3', '>=' ) )
		return;

	ignore_user_abort(true);

	if ( !edd_is_func_disabled( 'set_time_limit' ) && !ini_get('safe_mode') )
		set_time_limit(0);

	$args = array(
		'post_type' 		=> 'download',
		'posts_per_page' 	=> -1,
		'post_status' 		=> 'publish'
	);

	$query = new WP_Query( $args );
	$count = $query->post_count;
	$downloads = $query->get_posts();
	if( $downloads ) {

		$edd_log = new EDD_Logging();
		$i = 0;
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
	add_option( 'edd_logs_upgraded', '1' );

}


function edd_v134_upgrades() {

	$general_options = get_option( 'edd_settings_general' );

	if( isset( $general_options['failure_page'] ) )
		return; // settings already updated

	// Failed Purchase Page
	$failed = wp_insert_post(
		array(
			'post_title'     => __( 'Transaction Failed', 'edd' ),
			'post_content'   => __( 'Your transaction failed, please try again or contact site support.', 'edd' ),
			'post_status'    => 'publish',
			'post_author'    => 1,
			'post_type'      => 'page',
			'post_parent'    => $general_options['purchase_page'],
			'comment_status' => 'closed'
		)
	);

	$general_options['failure_page'] = $failed;

	update_option( 'edd_settings_general', $general_options );
}