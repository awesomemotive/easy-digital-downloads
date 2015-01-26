<?php
/**
 * Upgrade Functions
 *
 * @package     EDD
 * @subpackage  Admin/Upgrades
 * @copyright   Copyright (c) 2014, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.3.1
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Display Upgrade Notices
 *
 * @since 1.3.1
 * @return void
*/
function edd_show_upgrade_notices() {
	if ( isset( $_GET['page'] ) && $_GET['page'] == 'edd-upgrades' )
		return; // Don't show notices on the upgrades page

	$edd_version = get_option( 'edd_version' );

	if ( ! $edd_version ) {
		// 1.3 is the first version to use this option so we must add it
		$edd_version = '1.3';
	}

	$edd_version = preg_replace( '/[^0-9.].*/', '', $edd_version );

	if ( ! get_option( 'edd_payment_totals_upgraded' ) && ! get_option( 'edd_version' ) ) {
		if ( wp_count_posts( 'edd_payment' )->publish < 1 )
			return; // No payment exist yet

		// The payment history needs updated for version 1.2
		$url = add_query_arg( 'edd-action', 'upgrade_payments' );
		$upgrade_notice = sprintf( __( 'The Payment History needs to be updated. %s', 'edd' ), '<a href="' . wp_nonce_url( $url, 'edd_upgrade_payments_nonce' ) . '">' . __( 'Click to Upgrade', 'edd' ) . '</a>' );
		add_settings_error( 'edd-notices', 'edd-payments-upgrade', $upgrade_notice, 'error' );
	}

	if ( version_compare( $edd_version, '1.3.2', '<' ) && ! get_option( 'edd_logs_upgraded' ) ) {
		printf(
			'<div class="updated"><p>' . esc_html__( 'The Purchase and File Download History in Easy Digital Downloads needs to be upgraded, click %shere%s to start the upgrade.', 'edd' ) . '</p></div>',
			'<a href="' . esc_url( admin_url( 'options.php?page=edd-upgrades' ) ) . '">',
			'</a>'
		);
	}

	if ( version_compare( $edd_version, '1.3.4', '<' ) || version_compare( $edd_version, '1.4', '<' ) ) {
		printf(
			'<div class="updated"><p>' . esc_html__( 'Easy Digital Downloads needs to upgrade the plugin pages, click %shere%s to start the upgrade.', 'edd' ) . '</p></div>',
			'<a href="' . esc_url( admin_url( 'options.php?page=edd-upgrades' ) ) . '">',
			'</a>'
		);
	}

	if ( version_compare( $edd_version, '1.5', '<' ) ) {
		printf(
			'<div class="updated"><p>' . esc_html__( 'Easy Digital Downloads needs to upgrade the database, click %shere%s to start the upgrade.', 'edd' ) . '</p></div>',
			'<a href="' . esc_url( admin_url( 'options.php?page=edd-upgrades' ) ) . '">',
			'</a>'
		);
	}

	if ( version_compare( $edd_version, '2.0', '<' ) ) {
		printf(
			'<div class="updated"><p>' . esc_html__( 'Easy Digital Downloads needs to upgrade the database, click %shere%s to start the upgrade.', 'edd' ) . '</p></div>',
			'<a href="' . esc_url( admin_url( 'options.php?page=edd-upgrades' ) ) . '">',
			'</a>'
		);
	}

	if ( EDD()->session->get( 'upgrade_sequential' ) && edd_get_payments() ) {
		printf(
			'<div class="updated"><p>' . __( 'Easy Digital Downloads needs to upgrade past order numbers to make them sequential, click <a href="%s">here</a> to start the upgrade.', 'edd' ) . '</p></div>',
			admin_url( 'index.php?page=edd-upgrades&edd-upgrade=upgrade_sequential_payment_numbers' )
		);
	}

	if ( version_compare( $edd_version, '2.1', '<' ) ) {
		printf(
			'<div class="updated"><p>' . esc_html__( 'Easy Digital Downloads needs to upgrade the customer database, click %shere%s to start the upgrade.', 'edd' ) . '</p></div>',
			'<a href="' . esc_url( admin_url( 'index.php?page=edd-upgrades&edd-upgrade=upgrade_customers_db' ) ) . '">',
			'</a>'
		);
	}

}
add_action( 'admin_notices', 'edd_show_upgrade_notices' );

/**
 * Triggers all upgrade functions
 *
 * This function is usually triggered via AJAX
 *
 * @since 1.3.1
 * @return void
*/
function edd_trigger_upgrades() {

	if( ! current_user_can( 'manage_shop_settings' ) ) {
		wp_die( __( 'You do not have permission to do shop upgrades', 'edd' ), __( 'Error', 'edd' ), array( 'response' => 403 ) );
	}

	$edd_version = get_option( 'edd_version' );

	if ( ! $edd_version ) {
		// 1.3 is the first version to use this option so we must add it
		$edd_version = '1.3';
		add_option( 'edd_version', $edd_version );
	}

	if ( version_compare( EDD_VERSION, $edd_version, '>' ) ) {
		edd_v131_upgrades();
	}

	if ( version_compare( $edd_version, '1.3.4', '<' ) ) {
		edd_v134_upgrades();
	}

	if ( version_compare( $edd_version, '1.4', '<' ) ) {
		edd_v14_upgrades();
	}

	if ( version_compare( $edd_version, '1.5', '<' ) ) {
		edd_v15_upgrades();
	}

	if ( version_compare( $edd_version, '2.0', '<' ) ) {
		edd_v20_upgrades();
	}

	update_option( 'edd_version', EDD_VERSION );

	if ( DOING_AJAX )
		die( 'complete' ); // Let AJAX know that the upgrade is complete
}
add_action( 'wp_ajax_edd_trigger_upgrades', 'edd_trigger_upgrades' );

/**
 * Converts old sale and file download logs to new logging system
 *
 * @since 1.3.1
 * @uses WP_Query
 * @uses EDD_Logging
 * @return void
 */
function edd_v131_upgrades() {
	if ( get_option( 'edd_logs_upgraded' ) )
		return;

	if ( version_compare( get_option( 'edd_version' ), '1.3', '>=' ) )
		return;

	ignore_user_abort( true );

	if ( ! edd_is_func_disabled( 'set_time_limit' ) && ! ini_get( 'safe_mode' ) )
		set_time_limit( 0 );

	$args = array(
		'post_type' 		=> 'download',
		'posts_per_page' 	=> -1,
		'post_status' 		=> 'publish'
	);

	$query = new WP_Query( $args );
	$count = $query->post_count;
	$downloads = $query->get_posts();

	if ( $downloads ) {
		$edd_log = new EDD_Logging();
		$i = 0;
		foreach ( $downloads as $download ) {
			// Convert sale logs
			$sale_logs = edd_get_download_sales_log( $download->ID, false );

			if ( $sale_logs ) {
				foreach ( $sale_logs['sales'] as $sale ) {
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

			// Convert file download logs
			$file_logs = edd_get_file_download_log( $download->ID, false );

			if ( $file_logs ) {
				foreach ( $file_logs['downloads'] as $log ) {
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

/**
 * Upgrade routine for v1.3.4
 *
 * @since 1.3.4
 * @return void
 */
function edd_v134_upgrades() {
	$general_options = get_option( 'edd_settings_general' );

	if ( isset( $general_options['failure_page'] ) )
		return; // Settings already updated

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

/**
 * Upgrade routine for v1.4
 *
 * @since 1.4
 * @global $edd_options Array of all the EDD Options
 * @return void
 */
function edd_v14_upgrades() {
	global $edd_options;

	/** Add [edd_receipt] to success page **/
	$success_page = get_post( $edd_options['success_page'] );

	// Check for the [edd_receipt] short code and add it if not present
	if( strpos( $success_page->post_content, '[edd_receipt' ) === false ) {
		$page_content = $success_page->post_content .= "\n[edd_receipt]";
		wp_update_post( array( 'ID' => $edd_options['success_page'], 'post_content' => $page_content ) );
	}

	/** Convert Discounts to new Custom Post Type **/
	$discounts = get_option( 'edd_discounts' );

	if ( $discounts ) {
		foreach ( $discounts as $discount_key => $discount ) {
			$status = isset( $discount['status'] ) ? $discount['status'] : 'inactive';

			$discount_id = wp_insert_post( array(
				'post_type'   => 'edd_discount',
				'post_title'  => isset( $discount['name'] ) ? $discount['name'] : '',
				'post_status' => 'active'
			) );

			$meta = array(
				'code'        => isset( $discount['code'] ) ? $discount['code'] : '',
				'uses'        => isset( $discount['uses'] ) ? $discount['uses'] : '',
				'max_uses'    => isset( $discount['max'] ) ? $discount['max'] : '',
				'amount'      => isset( $discount['amount'] ) ? $discount['amount'] : '',
				'start'       => isset( $discount['start'] ) ? $discount['start'] : '',
				'expiration'  => isset( $discount['expiration'] ) ? $discount['expiration'] : '',
				'type'        => isset( $discount['type'] ) ? $discount['type'] : '',
				'min_price'   => isset( $discount['min_price'] ) ? $discount['min_price'] : ''
			);

			foreach ( $meta as $meta_key => $value ) {
				update_post_meta( $discount_id, '_edd_discount_' . $meta_key, $value );
			}
		}

		// Remove old discounts from database
		delete_option( 'edd_discounts' );
	}
}


/**
 * Upgrade routine for v1.5
 *
 * @since 1.5
 * @return void
 */
function edd_v15_upgrades() {
	// Update options for missing tax settings
	$tax_options = get_option( 'edd_settings_taxes' );

	// Set include tax on checkout to off
	$tax_options['checkout_include_tax'] = 'no';

	// Check if prices are displayed with taxes
	if( isset( $tax_options['taxes_on_prices'] ) ) {
		$tax_options['prices_include_tax'] = 'yes';
	} else {
		$tax_options['prices_include_tax'] = 'no';
	}

	update_option( 'edd_settings_taxes', $tax_options );

	// Flush the rewrite rules for the new /edd-api/ end point
	flush_rewrite_rules();
}

/**
 * Upgrades for EDD v2.0
 *
 * @since 2.0
 * @return void
 */
function edd_v20_upgrades() {

	global $edd_options, $wpdb;

	ignore_user_abort( true );

	if ( ! edd_is_func_disabled( 'set_time_limit' ) && ! ini_get( 'safe_mode' ) ) {
		set_time_limit( 0 );
	}

	// Upgrade for the anti-behavior fix - #2188
	if( ! empty( $edd_options['disable_ajax_cart'] ) ) {
		unset( $edd_options['enable_ajax_cart'] );
	} else {
		$edd_options['enable_ajax_cart'] = '1';
	}

	// Upgrade for the anti-behavior fix - #2188
	if( ! empty( $edd_options['disable_cart_saving'] ) ) {
		unset( $edd_options['enable_cart_saving'] );
	} else {
		$edd_options['enable_cart_saving'] = '1';
	}

	// Properly set the register / login form options based on whether they were enabled previously - #2076
	if( ! empty( $edd_options['show_register_form'] ) ) {
		$edd_options['show_register_form'] = 'both';
	} else {
		$edd_options['show_register_form'] = 'none';
	}

	// Remove all old, improperly expired sessions. See https://github.com/easydigitaldownloads/Easy-Digital-Downloads/issues/2031
	$wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE '_wp_session_expires_%' AND option_value+0 < 2789308218" );

	update_option( 'edd_settings', $edd_options );

}

/**
 * Upgrades for EDD v2.0 and sequential payment numbers
 *
 * @since 2.0
 * @return void
 */
function edd_v20_upgrade_sequential_payment_numbers() {

	if( ! current_user_can( 'manage_shop_settings' ) ) {
		wp_die( __( 'You do not have permission to do shop upgrades', 'edd' ), __( 'Error', 'edd' ), array( 'response' => 403 ) );
	}

	ignore_user_abort( true );

	if ( ! edd_is_func_disabled( 'set_time_limit' ) && ! ini_get( 'safe_mode' ) ) {
		set_time_limit( 0 );
	}

	$step   = isset( $_GET['step'] )  ? absint( $_GET['step'] )  : 1;
	$total  = isset( $_GET['total'] ) ? absint( $_GET['total'] ) : false;

	if( empty( $total ) || $total <= 1 ) {
		$payments = edd_count_payments();
		foreach( $payments as $status ) {
			$total += $status;
		}
	}

	$args   = array(
		'number' => 100,
		'page'   => $step,
		'status' => 'any',
		'order'  => 'ASC'
	);

	$payments = new EDD_Payments_Query( $args );
	$payments = $payments->get_payments();

	if( $payments ) {

		$prefix  = edd_get_option( 'sequential_prefix' );
		$postfix = edd_get_option( 'sequential_postfix' );
		$number  = ! empty( $_GET['custom'] ) ? absint( $_GET['custom'] ) : intval( edd_get_option( 'sequential_start', 1 ) );

		foreach( $payments as $payment ) {

			// Re-add the prefix and postfix
			$payment_number = $prefix . $number . $postfix;

			edd_update_payment_meta( $payment->ID, '_edd_payment_number', $payment_number );

			// Increment the payment number
			$number++;

		}

		// Payments found so upgrade them
		$step++;
		$redirect = add_query_arg( array(
			'page'        => 'edd-upgrades',
			'edd-upgrade' => 'upgrade_sequential_payment_numbers',
			'step'        => $step,
			'custom'      => $number,
			'total'       => $total
		), admin_url( 'index.php' ) );
		wp_redirect( $redirect ); exit;

	} else {


		// No more payments found, finish up
		EDD()->session->set( 'upgrade_sequential', null );
		wp_redirect( admin_url() ); exit;
	}

}
add_action( 'edd_upgrade_sequential_payment_numbers', 'edd_v20_upgrade_sequential_payment_numbers' );

/**
 * Upgrades for EDD v2.1 and the new customers database
 *
 * @since 2.1
 * @return void
 */
function edd_v21_upgrade_customers_db() {

	global $wpdb;

	if( ! current_user_can( 'manage_shop_settings' ) ) {
		wp_die( __( 'You do not have permission to do shop upgrades', 'edd' ), __( 'Error', 'edd' ), array( 'response' => 403 ) );
	}

	ignore_user_abort( true );

	if ( ! edd_is_func_disabled( 'set_time_limit' ) && ! ini_get( 'safe_mode' ) ) {
		@set_time_limit(0);
	}

	if( ! get_option( 'edd_upgrade_customers_db_version' ) ) {
		// Create the customers database on the first run
		@EDD()->customers->create_table();
	}

	$step   = isset( $_GET['step'] ) ? absint( $_GET['step'] ) : 1;
	$number = 20;
	$offset = $step == 1 ? 0 : ( $step - 1 ) * $number;

	$emails = $wpdb->get_col( $wpdb->prepare( "SELECT DISTINCT meta_value FROM $wpdb->postmeta WHERE meta_key = '_edd_payment_user_email' LIMIT %d,%d;", $offset, $number ) );

	if( $emails ) {

		foreach( $emails as $email ) {

			if( EDD()->customers->exists( $email ) ) {
				continue; // Allow the upgrade routine to be safely re-run in the case of failure
			}

			$args = array(
				'user'    => $email,
				'order'   => 'ASC',
				'orderby' => 'ID',
				'number'  => -1,
				'page'    => $step
			);

			$payments = new EDD_Payments_Query( $args );
			$payments = $payments->get_payments();

			if( $payments ) {

				$total_value = 0.00;
				$total_count = 0;

				foreach( $payments as $payment ) {

					$status = get_post_status( $payment->ID );
					if( 'revoked' == $status || 'publish' == $status ) {

						$total_value += $payment->total;
						$total_count += 1;

					}

				}

				$ids  = wp_list_pluck( $payments, 'ID' );

				$user = get_user_by( 'email', $email );

				$args = array(
					'email'          => $email,
					'user_id'        => $user ? $user->ID : 0,
					'name'           => $user ? $user->display_name : '',
					'purchase_count' => $total_count,
					'purchase_value' => round( $total_value, 2 ),
					'payment_ids'    => implode( ',', array_map( 'absint', $ids ) ),
					'date_created'   => $payments[0]->date
				);

				$customer_id = EDD()->customers->add( $args );

				foreach( $ids as $id ) {
					update_post_meta( $id, '_edd_payment_customer_id', $customer_id );
				}

			}

		}

		// Customers found so upgrade them
		$step++;
		$redirect = add_query_arg( array(
			'page'        => 'edd-upgrades',
			'edd-upgrade' => 'upgrade_customers_db',
			'step'        => $step
		), admin_url( 'index.php' ) );
		wp_redirect( $redirect ); exit;

	} else {

		// No more customers found, finish up

		update_option( 'edd_version', preg_replace( '/[^0-9.].*/', '', EDD_VERSION ) );

		wp_redirect( admin_url() ); exit;
	}

}
add_action( 'edd_upgrade_customers_db', 'edd_v21_upgrade_customers_db' );
