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
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Export all Payment History to CSV
 *
 * @access      private
 * @since       1.2
 * @return      void
 */

function edd_export_payment_history() {
	global $edd_options;

	$mode = edd_is_test_mode() ? 'test' : 'live';

	header( 'Content-Type: text/csv; charset=utf-8' );
	header( 'Content-Disposition: attachment; filename=edd-payment-history-' . date( 'm-d-Y' ) . '.csv' );
	header( "Pragma: no-cache" );
	header( "Expires: 0" );

	$payments = edd_get_payments( array(
			'offset'  => 0,
			'number'  => -1,
			'mode'    => $mode
		) );

	if ( $payments ) {
		$i = 0;
		echo '"' . __( 'ID', 'edd' ) 			.  '",';
		echo '"' . __( 'Email', 'edd' ) 		.  '",';
		echo '"' . __( 'First Name', 'edd' ) 	.  '",';
		echo '"' . __( 'Last Name', 'edd' ) 	.  '",';
		echo '"' . __( 'Products', 'edd' ) 		.  '",';
		echo '"' . __( 'Discounts,', 'edd' ) 	.  '",';
		echo '"' . __( 'Amount paid', 'edd' ) 	.  '",';
		if ( edd_use_taxes() ) {
			echo '"' . __( 'Amount taxed', 'edd' ) . '",';
		}
		echo '"' . __( 'Payment method', 'edd' ).  '",';
		echo '"' . __( 'Key', 'edd' ) 			.  '",';
		echo '"' . __( 'Date', 'edd' ) 			.  '",';
		echo '"' . __( 'User', 'edd' ) 			.  '",';
		echo '"' . __( 'Status', 'edd' ) 		.  '"';
		echo "\r\n";
		foreach ( $payments as $payment ) {

			$payment_meta 	= edd_get_payment_meta( $payment->ID );
			$user_info 		= edd_get_payment_meta_user_info( $payment->ID );

			echo '"' . $payment->ID 			. '",';
			echo '"' . $payment_meta['email'] 	. '",';
			echo '"' . $user_info['first_name'] . '",';
			echo '"' . $user_info['last_name']	. '",';

			$downloads = edd_get_payment_meta_cart_details( $payment->ID );

			if ( empty( $downloads ) || ! $downloads ) {
				$downloads = maybe_unserialize( $payment_meta['downloads'] );
			}

			if ( $downloads ) {

				foreach ( $downloads as $key => $download ) {

					// Download ID
					$id = isset( $payment_meta['cart_details'] ) ? $download['id'] : $download;

					// If the download has variable prices, override the default price
					$price_override = isset( $payment_meta['cart_details'] ) ? $download['price'] : null;

					$user_info = unserialize( $payment_meta['user_info'] );

					$price = edd_get_download_final_price( $id, $user_info, $price_override );

					// Display the Downoad Name
					echo '"' . get_the_title( $id );

					echo  ' - ';

					if ( isset( $downloads[ $key ]['item_number'] ) ) {
						$price_options = $downloads[ $key ]['item_number']['options'];

						if ( isset( $price_options['price_id'] ) ) {
							echo edd_get_price_option_name( $id, $price_options['price_id'] );
							echo ' - ';
						}
					}
					echo html_entity_decode( edd_currency_filter( $price ) );

					if ( $key != ( count( $downloads ) -1 ) ) {
						echo ' / ';
					}

				}

				echo '",';

			}

			if ( isset( $user_info['discount'] ) && $user_info['discount'] != 'none' ) {
				echo '"' . $user_info['discount'] . '",';
			} else {
				echo '"' . __( 'none', 'edd' ) . '",';
			}

			echo '"' . html_entity_decode( edd_currency_filter( edd_format_amount( $payment_meta['amount'] ) ) ) . '",';

			if ( edd_use_taxes() ) {
				echo '"' . html_entity_decode( edd_payment_tax( $payment->ID, $payment_meta ) ) . '",';
			}

			$gateway = get_post_meta( $payment->ID, '_edd_payment_gateway', true );
			if ( $gateway ) {
				echo '"' .  edd_get_gateway_admin_label( $gateway ) . '",';
			} else {
				echo '"' . __( 'none', 'edd' ) . '",';
			}

			echo '"' . $payment_meta['key'] . '",';

			echo '"' . date_i18n( get_option( 'date_format' ), strtotime( $payment->post_date ) ) . '",';

			$user_id = isset( $user_info['id'] ) && $user_info['id'] != -1 ? $user_info['id'] : $user_info['email'];
			echo '"' . is_numeric( $user_id ) ? get_user_by( 'id', $user_id )->display_name : __( 'guest', 'edd' ) . '",';
			echo '"' . edd_get_payment_status( $payment, true ) . '"';
			echo "\r\n";

			$i++;
		}
	} else {
		echo __( 'No payments recorded yet', 'edd' );
	}
	die();
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

	if ( current_user_can( 'administrator' ) ) {

		global $wpdb;

		$emails = $wpdb->get_col( "SELECT DISTINCT meta_value FROM $wpdb->postmeta WHERE meta_key = '_edd_payment_user_email' " );

		if ( !empty( $emails ) ) {
			header( "Content-type: text/csv" );
			$today = date( "Y-m-d" );
			header( "Content-Disposition: attachment; filename=customers-$today.csv" );
			header( "Pragma: no-cache" );
			header( "Expires: 0" );

			echo '"' . __( 'Email', 'edd' ) 			.  '",';
			echo '"' . __( 'Name', 'edd' ) 				.  '",';
			echo '"' . __( 'Total Purchases', 'edd' ) 	.  '",';
			echo '"' . __( 'Total Purchased', 'edd' ) 	.  '"';
			echo "\r\n";
			foreach( $emails as $email ) {

				$wp_user = get_user_by( 'email', $email );

				echo $email . ',';
				echo $wp_user ? $wp_user->display_name : __( 'Guest', 'edd' );
				echo ',';
				echo edd_count_purchases_of_customer( $email ) . ',';
				echo html_entity_decode( edd_currency_filter( edd_format_amount( edd_purchase_total_of_user( $email ) ) ) );
				echo "\n";

			}
			exit;
		}
	} else {
		wp_die( __( 'Export not allowed for non-administrators.', 'edd' ) );
	}
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

					} // endforeach
				}
			}


			exit;
		}
	} else {
		wp_die( __( 'Export not allowed for non-administrators.', 'edd' ) );
	}
}
add_action( 'edd_downloads_history_export', 'edd_export_all_downloads_history' );
