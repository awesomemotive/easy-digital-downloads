<?php

function edd_export_payment_history() {
	global $edd_options;

	$mode = edd_is_test_mode() ? 'test' : 'live';
			
	header( 'Content-Type: text/csv; charset=utf-8' );
	header( 'Content-Disposition: attachment; filename=edd-payment-history-' . date('m-d-Y') . '.csv' );
	header( "Pragma: no-cache" );
	header( "Expires: 0" );

	$payments = edd_get_payments( array(
		'offset'  => 0, 
		'number'  => -1, 
		'mode'    => $mode
	) );

	if($payments){
		$i = 0;
		echo '"' . __( 'ID', 'edd' ) .  '",';
		echo '"' . __( 'Email', 'edd' ) .  '",';
		echo '"' . __( 'First Name', 'edd' ) .  '",';
		echo '"' . __( 'Last Name', 'edd' ) .  '",';
		echo '"' . __( 'Products', 'edd' ) .  '",';
		echo '"' . __( 'Discounts,', 'edd' ) .  '",';
		echo '"' . __( 'Amount paid', 'edd' ) .  '",';
		echo '"' . __( 'Payment method', 'edd' ) .  '",';
		echo '"' . __( 'Key', 'edd' ) .  '",';
		echo '"' . __( 'Date', 'edd' ) .  '",';
		echo '"' . __( 'User', 'edd' ) .  '",';
		echo '"' . __( 'Status', 'edd' ) .  '"';
		echo "\r\n";
		foreach($payments as $payment){

			$payment_meta = edd_get_payment_meta( $payment->ID );
			$user_info = maybe_unserialize( $payment_meta['user_info'] );
			
			echo '"' . $payment->ID . '",';
			echo '"' . $payment_meta['email'] . '",';
			echo '"' . $user_info['first_name'] . '",';
			echo '"' . $user_info['last_name']. '",';

			$downloads = isset( $payment_meta['cart_details'] ) ? maybe_unserialize( $payment_meta['cart_details'])  : false;
			if( empty( $downloads ) || ! $downloads ) {
				$downloads = maybe_unserialize( $payment_meta['downloads'] );
			}

			if( $downloads ) {

				foreach( $downloads as $key => $download ) {

					// retrieve the ID of the download
					$id = isset( $payment_meta['cart_details'] ) ? $download['id'] : $download;

					// if download has variable prices, override the default price
					$price_override = isset( $payment_meta['cart_details'] ) ? $download['price'] : null;

					$user_info = unserialize( $payment_meta['user_info'] );

					// calculate the final price
					$price = edd_get_download_final_price( $id, $user_info, $price_override );

					// show name of download
					echo get_the_title($id);

					echo  ' - ';

					if( isset( $downloads[ $key ]['item_number'] ) ) {
						
						$price_options = $downloads[ $key ]['item_number']['options'];
						
						if( isset( $price_options['price_id'] ) ) {
							echo edd_get_price_option_name( $id, $price_options['price_id'] );
							echo ' - ';
						}
					}
					echo html_entity_decode( edd_currency_filter( $price ) );
					
					if( $key != ( count( $downloads ) -1 ) ) {
						echo ' / ';
					} 

				}

				echo ',';

			}

			if( isset( $user_info['discount'] ) && $user_info['discount'] != 'none' ) {
				echo '"' . $user_info['discount'] . '",';
			} else {
				echo '"' . __( 'none', 'edd' ) . '",';
			}
			echo '"' . html_entity_decode( edd_currency_filter( $payment_meta['amount'] ) ) . '",';

			$gateway = get_post_meta( $payment->ID, '_edd_payment_gateway', true );
			if( $gateway ) {
				echo '"' .  edd_get_gateway_admin_label( $gateway ) . '",';
			} else {
				echo '"' . __( 'none', 'edd' ) . '",'; 
			}
			echo '"' . $payment_meta['key'] . '",';
			echo '"' . date( get_option( 'date_format' ), strtotime( $payment->post_date ) ) . '",';
			
			$user_id = isset( $user_info['id'] ) && $user_info['id'] != -1 ? $user_info['id'] : $user_info['email'];

			echo '"';
			echo is_numeric( $user_id ) ? get_user_by( 'id', $user_id )->display_name : __('guest', 'edd');
			echo '",';
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

	if( current_user_can( 'administrator' ) ) {

		global $wpdb;
		
		$emails = $wpdb->get_col( "SELECT DISTINCT meta_value FROM $wpdb->postmeta WHERE meta_key = '_edd_payment_user_email' " );
		
		if( !empty( $emails ) ) {
			header( "Content-type: text/csv" );
			$today = date( "Y-m-d" );
			header( "Content-Disposition: attachment; filename=user_emails-$today.csv" );
			header( "Pragma: no-cache" );
			header( "Expires: 0" );
			
			echo implode( "\n", $emails );
			exit;
		}
	} else {
		wp_die(__( 'Export not allowed for non-administrators.', 'edd' ) );
	}
}
add_action( 'edd_email_export', 'edd_export_all_customers' );

/**
 * Export all downloads to CSV
 * 
 * 
 * @access      private
 * @since       1.2
 * @return      void
*/
function edd_export_all_downloads_history() {

	if( current_user_can( 'administrator' ) ) {
		
		$report_args = array(
			'post_type' 	=> 'download',
			'post_status'	=> 'publish',
			'posts_per_page'=> -1,
			'order'			=> 'post_date'
		);
    
    $downloads = get_posts( $report_args );
    
		if( $downloads ) {
			foreach( $downloads as $download ) {
				$reports_data[] = array(
					'ID' 				=> $download->ID,
					'title' 			=> get_the_title( $download->ID ),
					'sales' 			=> edd_get_download_sales_stats( $download->ID ),
					'earnings'			=> edd_get_download_earnings_stats( $download->ID ),
					'average_sales'   	=> edd_get_average_monthly_download_sales( $download->ID ),
					'average_earnings'  => edd_get_average_monthly_download_earnings( $download->ID )
				);
			}
		}
    
    
		if( !empty( $downloads ) ) {
			header( "Content-type: text/csv" );
			$today = date( "Y-m-d" );
			header( "Content-Disposition: attachment; filename=user_downloads_history-$today.csv" );
			header( "Pragma: no-cache" );
			header( "Expires: 0" );
			
  		echo '"' . __( 'ID', 'edd' ) .  '",';
  		echo '"' . __( 'Title', 'edd' ) .  '",';
  		echo '"' . __( 'Sales', 'edd' ) .  '",';
  		echo '"' . __( 'Earnings', 'edd' ) .  '",';
  		echo '"' . __( 'Average_sales', 'edd' ) .  '",';
  		echo '"' . __( 'Average_earnings,', 'edd' ) .  '"';
  		echo "\r\n";
      foreach( $reports_data as $report ) {
        echo '"' . $report['ID'] . '",';
        echo '"' . $report['title'] . '",';
        echo '"' . $report['sales'] . '",';
        echo '"' . $report['earnings'] . '",';
        echo '"' . $report['average_sales'] . '",';
        echo '"' . $report['average_earnings'] . '"';
        echo "\r\n";
      }
			
      
      exit;
		}
	} else {
		wp_die(__( 'Export not allowed for non-administrators.', 'edd' ) );
	}
}
add_action( 'edd_downloads_history_export', 'edd_export_all_downloads_history' );
