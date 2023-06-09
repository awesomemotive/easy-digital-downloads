<?php

/**
 * Converts old sale and file download logs to new logging system
 *
 * @since 1.3.1
 * @deprecated 3.1.2
 * @uses WP_Query
 * @uses EDD_Logging
 * @return void
 */
function edd_v131_upgrades() {
	if ( get_option( 'edd_logs_upgraded' ) ) {
		return;
	}

	$edd_version = edd_get_db_version();

	if ( version_compare( $edd_version, '1.3', '>=' ) ) {
		return;
	}

	edd_set_time_limit();

	$query = new WP_Query( array(
		'post_type' 		=> 'download',
		'posts_per_page' 	=> -1,
		'post_status' 		=> 'publish'
	) );
	$downloads = $query->get_posts();

	if ( $downloads ) {
		$edd_log = new EDD_Logging();
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
 * Upgrade routine for v1.3.0
 *
 * @since 1.3.0
 * @deprecated 3.1.2
 * @return void
 */
function edd_v134_upgrades() {
	$general_options = get_option( 'edd_settings_general' );

	// Settings already updated
	if ( isset( $general_options['failure_page'] ) ) {
		return;
	}

	// Failed Purchase Page
	$failed = wp_insert_post(
		array(
			'post_title'     => __( 'Transaction Failed', 'easy-digital-downloads' ),
			'post_content'   => __( 'Your transaction failed, please try again or contact site support.', 'easy-digital-downloads' ),
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
 * @deprecated 3.1.2
 * @global $edd_options Array of all the EDD Options
 * @return void
 */
function edd_v14_upgrades() {

	/** Add [edd_receipt] to success page **/
	$success_page = get_post( edd_get_option( 'success_page' ) );

	// Check for the [edd_receipt] shortcode and add it if not present
	if ( strpos( $success_page->post_content, '[edd_receipt' ) === false ) {
		$page_content = $success_page->post_content .= "\n[edd_receipt]";
		wp_update_post( array( 'ID' => edd_get_option( 'success_page' ), 'post_content' => $page_content ) );
	}

	/** Convert Discounts to new Custom Post Type **/
	$discounts = get_option( 'edd_discounts' );

	if ( $discounts ) {
		foreach ( $discounts as $discount ) {

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
 * @deprecated 3.1.2
 * @return void
 */
function edd_v15_upgrades() {
	// Update options for missing tax settings
	$tax_options = get_option( 'edd_settings_taxes' );

	// Set include tax on checkout to off
	$tax_options['checkout_include_tax'] = 'no';

	// Check if prices are displayed with taxes
	$tax_options['prices_include_tax'] = isset( $tax_options['taxes_on_prices'] )
		? 'yes'
		: 'no';

	update_option( 'edd_settings_taxes', $tax_options );

	// Flush the rewrite rules for the new /edd-api/ end point
	flush_rewrite_rules( false );
}

/**
 * Upgrades for EDD v2.0
 *
 * @since 2.0
 * @deprecated 3.1.2
 * @return void
 */
function edd_v20_upgrades() {
	global $edd_options, $wpdb;

	edd_set_time_limit();

	// Upgrade for the anti-behavior fix - #2188
	if ( ! empty( $edd_options['disable_ajax_cart'] ) ) {
		unset( $edd_options['enable_ajax_cart'] );
	} else {
		$edd_options['enable_ajax_cart'] = '1';
	}

	// Upgrade for the anti-behavior fix - #2188
	if ( ! empty( $edd_options['disable_cart_saving'] ) ) {
		unset( $edd_options['enable_cart_saving'] );
	} else {
		$edd_options['enable_cart_saving'] = '1';
	}

	// Properly set the register / login form options based on whether they were enabled previously - #2076
	if ( ! empty( $edd_options['show_register_form'] ) ) {
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
 * @deprecated 3.1.1.2 EDD no longer implies that past orders will be updated.
 * @since 2.0
 * @return void
 */
function edd_v20_upgrade_sequential_payment_numbers() {

	if ( ! current_user_can( 'manage_shop_settings' ) ) {
		wp_die( __( 'You do not have permission to do shop upgrades', 'easy-digital-downloads' ), __( 'Error', 'easy-digital-downloads' ), array( 'response' => 403 ) );
	}

	edd_set_time_limit();

	$step   = isset( $_GET['step'] )  ? absint( $_GET['step'] )  : 1;
	$total  = isset( $_GET['total'] ) ? absint( $_GET['total'] ) : false;

	if ( empty( $total ) || $total <= 1 ) {
		$payments = edd_count_payments();
		foreach ( $payments as $status ) {
			$total += $status;
		}
	}

	$orders = edd_get_orders( array(
		'number' => 100,
		'offset' => $step == 1 ? 0 : ( $step - 1 ) * 100,
		'order'  => 'asc',
	) );

	if ( $orders ) {
		$prefix  = edd_get_option( 'sequential_prefix' );
		$postfix = edd_get_option( 'sequential_postfix' );
		$number  = ! empty( $_GET['custom'] ) ? absint( $_GET['custom'] ) : intval( edd_get_option( 'sequential_start', 1 ) );

		foreach ( $orders as $order ) {

			// Re-add the prefix and postfix
			$payment_number = $prefix . $number . $postfix;

			edd_update_order( $order->id, array(
				'order_number' => $payment_number
            ) );

			// Increment the payment number
			$number++;
		}

		// Payments found so upgrade them
		$step++;
		$redirect = add_query_arg( array(
			'page'        => 'edd-upgrades',
			'edd-upgrade' => 'upgrade_sequential_payment_numbers',
			'step'        => urlencode( $step ),
			'custom'      => urlencode( $number ),
			'total'       => urlencode( $total ),
		), admin_url( 'index.php' ) );

		edd_redirect( $redirect );

	// No more payments found, finish up
	} else {
		delete_option( 'edd_upgrade_sequential' );
		delete_option( 'edd_doing_upgrade' );

		edd_redirect( admin_url() );
	}
}

/**
 * Upgrades for EDD v2.1 and the new customers database
 *
 * @since 2.1
 * @deprecated 3.1.2 EDD no longer implies that past orders will be updated.
 * @return void
 */
function edd_v21_upgrade_customers_db() {
	global $wpdb;

	if ( ! current_user_can( 'manage_shop_settings' ) ) {
		wp_die( __( 'You do not have permission to do shop upgrades', 'easy-digital-downloads' ), __( 'Error', 'easy-digital-downloads' ), array( 'response' => 403 ) );
	}

	edd_set_time_limit();

	$number = 20;
	$step   = isset( $_GET['step'] )
		? absint( $_GET['step'] )
		: 1;
	$offset = $step == 1
		? 0
		: ( $step - 1 ) * $number;

	$emails = $wpdb->get_col( $wpdb->prepare( "SELECT DISTINCT meta_value FROM {$wpdb->postmeta} WHERE meta_key = '_edd_payment_user_email' LIMIT %d,%d;", $offset, $number ) );

	if ( $emails ) {

		foreach ( $emails as $email ) {

			if ( EDD()->customers->exists( $email ) ) {
				continue; // Allow the upgrade routine to be safely re-run in the case of failure
			}

			$payments = new EDD_Payments_Query( array(
				'user'    => $email,
				'order'   => 'ASC',
				'orderby' => 'ID',
				'number'  => 9999999,
				'page'    => $step
			) );

			$payments = $payments->get_payments();

			if ( $payments ) {

				$total_value = 0.00;
				$total_count = 0;

				foreach ( $payments as $payment ) {

					if ( 'revoked' == $payment->status || 'complete' == $payment->status ) {
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

				foreach ( $ids as $id ) {
					update_post_meta( $id, '_edd_payment_customer_id', $customer_id );
				}
			}
		}

		// Customers found so upgrade them
		$step++;
		$redirect = add_query_arg( array(
			'page'        => 'edd-upgrades',
			'edd-upgrade' => 'upgrade_customers_db',
			'step'        => urlencode( $step ),
		), admin_url( 'index.php' ) );

		edd_redirect( $redirect );

	// No more customers found, finish up
	} else {
		delete_option( 'edd_doing_upgrade' );

		edd_redirect( admin_url() );
	}
}

/**
 * Fixes the edd_log meta for 2.2.6
 *
 * @since 2.2.6
 * @deprecated 3.1.2 EDD no longer implies that past orders will be updated.
 * @return void
 */
function edd_v226_upgrade_payments_price_logs_db() {
	global $wpdb;

	if ( ! current_user_can( 'manage_shop_settings' ) ) {
		wp_die( __( 'You do not have permission to do shop upgrades', 'easy-digital-downloads' ), __( 'Error', 'easy-digital-downloads' ), array( 'response' => 403 ) );
	}

	edd_set_time_limit();

	$number = 25;
	$step   = isset( $_GET['step'] )
		? absint( $_GET['step'] )
		: 1;
	$offset = $step == 1
		? 0
		: ( $step - 1 ) * $number;

	if ( 1 === $step ) {
		// Check if we have any variable price products on the first step
		$sql = "SELECT ID FROM {$wpdb->posts} p LEFT JOIN {$wpdb->postmeta} m ON p.ID = m.post_id WHERE m.meta_key = '_variable_pricing' AND m.meta_value = 1 LIMIT 1";
		$has_variable = $wpdb->get_col( $sql );
		if ( empty( $has_variable ) ) {
			// We had no variable priced products, so go ahead and just complete
			delete_option( 'edd_doing_upgrade' );
			edd_redirect( admin_url() );
		}
	}

	$payment_ids = $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM {$wpdb->posts} WHERE post_type = 'edd_payment' ORDER BY post_date DESC LIMIT %d,%d;", $offset, $number ) );
	if ( ! empty( $payment_ids ) ) {
		foreach ( $payment_ids as $payment_id ) {
			$payment_downloads  = edd_get_payment_meta_downloads( $payment_id );
			$variable_downloads = array();

			// May not be an array due to some very old payments, move along
			if ( ! is_array( $payment_downloads ) ) {
				continue;
			}

			foreach ( $payment_downloads as $download ) {
				// Don't care if the download is a single price id
				if ( ! isset( $download['options']['price_id'] ) ) {
					continue;
				}
				$variable_downloads[] = array( 'id' => $download['id'], 'price_id' => $download['options']['price_id'] );
			}
			$variable_download_ids = array_unique( wp_list_pluck( $variable_downloads, 'id' ) );
			$unique_download_ids   = implode( ',', $variable_download_ids );

			// If there were no downloads, just fees, move along
			if ( empty( $unique_download_ids ) ) {
				continue;
			}

			// Get all Log Ids where the post parent is in the set of download IDs we found in the cart meta
			$logs = $wpdb->get_results( "SELECT m.post_id AS log_id, p.post_parent AS download_id FROM {$wpdb->postmeta} m LEFT JOIN {$wpdb->posts} p ON m.post_id = p.ID WHERE meta_key = '_edd_log_payment_id' AND meta_value = $payment_id AND p.post_parent IN ($unique_download_ids)", ARRAY_A );
			$mapped_logs = array();

			// Go through each cart item
			foreach ( $variable_downloads as $cart_item ) {
				// Itterate through the logs we found attached to this payment
				foreach ( $logs as $key => $log ) {
					// If this Log ID is associated with this download ID give it the price_id
					if ( (int) $log['download_id'] === (int) $cart_item['id'] ) {
						$mapped_logs[$log['log_id']] = $cart_item['price_id'];
						// Remove this Download/Log ID from the list, for multipurchase compatibility
						unset( $logs[$key] );
						// These aren't the logs we're looking for. Move Along, Move Along.
						break;
					}
				}
			}

			if ( ! empty( $mapped_logs ) ) {
				$update  = "UPDATE {$wpdb->postmeta} SET meta_value = ";
				$case    = "CASE post_id ";
				foreach ( $mapped_logs as $post_id => $value ) {
					$case .= "WHEN {$post_id} THEN {$value} ";
				}
				$case   .= "END ";
				$log_ids = implode( ',', array_keys( $mapped_logs ) );
				$where   = "WHERE post_id IN ({$log_ids}) AND meta_key = '_edd_log_price_id'";
				$sql     = $update . $case . $where;

				// Execute our query to update this payment
				$wpdb->query( $sql );
			}
		}

		// More Payments found so upgrade them
		$step++;
		$redirect = add_query_arg( array(
			'page'        => 'edd-upgrades',
			'edd-upgrade' => 'upgrade_payments_price_logs_db',
			'step'        => urlencode( $step ),
		), admin_url( 'index.php' ) );

		edd_redirect( $redirect );
	} else {
		delete_option( 'edd_doing_upgrade' );
		edd_redirect( admin_url() );
	}
}

/**
 * Upgrades payment taxes for 2.3
 *
 * @since 2.3
 * @deprecated 3.1.2 EDD no longer implies that past orders will be updated.
 * @return void
 */
function edd_v23_upgrade_payment_taxes() {
	global $wpdb;

	if ( ! current_user_can( 'manage_shop_settings' ) ) {
		wp_die( __( 'You do not have permission to do shop upgrades', 'easy-digital-downloads' ), __( 'Error', 'easy-digital-downloads' ), array( 'response' => 403 ) );
	}

	edd_set_time_limit();

	$number = 50;
	$step   = isset( $_GET['step'] )
		? absint( $_GET['step'] )
		: 1;
	$offset = $step == 1
		? 0
		: ( $step - 1 ) * $number;

	if ( $step < 2 ) {
		// Check if we have any payments before moving on
		$sql = "SELECT ID FROM {$wpdb->posts} WHERE post_type = 'edd_payment' LIMIT 1";
		$has_payments = $wpdb->get_col( $sql );

		if ( empty( $has_payments ) ) {
			// We had no payments, just complete
			edd_set_upgrade_complete( 'upgrade_payment_taxes' );
			delete_option( 'edd_doing_upgrade' );
			edd_redirect( admin_url() );
		}
	}

	$total = isset( $_GET['total'] ) ? absint( $_GET['total'] ) : false;
	if ( empty( $total ) || $total <= 1 ) {
		$total_sql = "SELECT COUNT(ID) as total_payments FROM {$wpdb->posts} WHERE post_type = 'edd_payment'";
		$results   = $wpdb->get_row( $total_sql, 0 );

		$total     = $results->total_payments;
	}

	$payment_ids = $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM {$wpdb->posts} WHERE post_type = 'edd_payment' ORDER BY post_date DESC LIMIT %d,%d;", $offset, $number ) );

	if ( $payment_ids ) {

		// Add the new _edd_payment_meta item
		foreach ( $payment_ids as $payment_id ) {
			$payment_tax = edd_get_payment_tax( $payment_id );
			edd_update_payment_meta( $payment_id, '_edd_payment_tax', $payment_tax );
		}

		// Payments found so upgrade them
		$step++;
		$redirect = add_query_arg( array(
			'page'        => 'edd-upgrades',
			'edd-upgrade' => 'upgrade_payment_taxes',
			'step'        => urlencode( $step ),
			'number'      => urlencode( $number ),
			'total'       => urlencode( $total ),
		), admin_url( 'index.php' ) );

		edd_redirect( $redirect );

	// No more payments found, finish up
	} else {
		edd_set_upgrade_complete( 'upgrade_payment_taxes' );
		delete_option( 'edd_doing_upgrade' );
		edd_redirect( admin_url() );
	}
}

/**
 * Run the upgrade for the customers to find all payment attachments
 *
 * @since  2.3
 * @deprecated 3.1.2 EDD no longer implies that past orders will be updated.
 * @return void
 */
function edd_v23_upgrade_customer_purchases() {
	global $wpdb;

	if ( ! current_user_can( 'manage_shop_settings' ) ) {
		wp_die( __( 'You do not have permission to do shop upgrades', 'easy-digital-downloads' ), __( 'Error', 'easy-digital-downloads' ), array( 'response' => 403 ) );
	}

	edd_set_time_limit();

	$number = 50;
	$step   = isset( $_GET['step'] )
		? absint( $_GET['step'] )
		: 1;
	$offset = $step == 1
		? 0
		: ( $step - 1 ) * $number;

	if ( $step < 2 ) {
		// Check if we have any payments before moving on
		$sql = "SELECT ID FROM {$wpdb->posts} WHERE post_type = 'edd_payment' LIMIT 1";
		$has_payments = $wpdb->get_col( $sql );

		if ( empty( $has_payments ) ) {
			// We had no payments, just complete
			edd_set_upgrade_complete( 'upgrade_customer_payments_association' );
			delete_option( 'edd_doing_upgrade' );
			edd_redirect( admin_url() );
		}
	}

	$total = isset( $_GET['total'] ) ? absint( $_GET['total'] ) : false;

	if ( empty( $total ) || $total <= 1 ) {
		$total = EDD()->customers->count();
	}

	$customers = edd_get_customers( array( 'number' => $number, 'offset' => $offset ) );

	if ( ! empty( $customers ) ) {

		foreach ( $customers as $customer ) {

			// Get payments by email and user ID
			$select = "SELECT ID FROM {$wpdb->posts} p ";
			$join   = "LEFT JOIN {$wpdb->postmeta} m ON p.ID = m.post_id ";
			$where  = "WHERE p.post_type = 'edd_payment' ";

			if ( ! empty( $customer->user_id ) && intval( $customer->user_id ) > 0 ) {
				$where .= "AND ( ( m.meta_key = '_edd_payment_user_email' AND m.meta_value = '{$customer->email}' ) OR ( m.meta_key = '_edd_payment_customer_id' AND m.meta_value = '{$customer->id}' ) OR ( m.meta_key = '_edd_payment_user_id' AND m.meta_value = '{$customer->user_id}' ) )";
			} else {
				$where .= "AND ( ( m.meta_key = '_edd_payment_user_email' AND m.meta_value = '{$customer->email}' ) OR ( m.meta_key = '_edd_payment_customer_id' AND m.meta_value = '{$customer->id}' ) ) ";
			}

			$sql            = $select . $join . $where;
			$found_payments = $wpdb->get_col( $sql );

			$unique_payment_ids  = array_unique( array_filter( $found_payments ) );

			if ( ! empty( $unique_payment_ids ) ) {

				$unique_ids_string  = implode( ',', $unique_payment_ids );
				$customer_data      = array( 'payment_ids' => $unique_ids_string );

				$purchase_value_sql = "SELECT SUM( m.meta_value ) FROM {$wpdb->postmeta} m LEFT JOIN {$wpdb->posts} p ON m.post_id = p.ID WHERE m.post_id IN ( {$unique_ids_string} ) AND p.post_status IN ( 'publish', 'revoked' ) AND m.meta_key = '_edd_payment_total'";
				$purchase_value     = $wpdb->get_col( $purchase_value_sql );

				$purchase_count_sql = "SELECT COUNT( m.post_id ) FROM {$wpdb->postmeta} m LEFT JOIN {$wpdb->posts} p ON m.post_id = p.ID WHERE m.post_id IN ( {$unique_ids_string} ) AND p.post_status IN ( 'publish', 'revoked' ) AND m.meta_key = '_edd_payment_total'";
				$purchase_count     = $wpdb->get_col( $purchase_count_sql );

				if ( ! empty( $purchase_value ) && ! empty( $purchase_count ) ) {

					$purchase_value = $purchase_value[0];
					$purchase_count = $purchase_count[0];

					$customer_data['purchase_count'] = $purchase_count;
					$customer_data['purchase_value'] = $purchase_value;
				}

			} else {
				$customer_data['purchase_count'] = 0;
				$customer_data['purchase_value'] = 0;
				$customer_data['payment_ids']    = '';
			}

			if ( ! empty( $customer_data ) ) {
				$customer = new EDD_Customer( $customer->id );
				$customer->update( $customer_data );
			}
		}

		// More Payments found so upgrade them
		$step++;
		$redirect = add_query_arg( array(
			'page'        => 'edd-upgrades',
			'edd-upgrade' => 'upgrade_customer_payments_association',
			'step'        => urlencode( $step ),
			'number'      => urlencode( $number ),
			'total'       => urlencode( $total ),
		), admin_url( 'index.php' ) );

		edd_redirect( $redirect );

	// No more customers found, finish up
	} else {
		edd_set_upgrade_complete( 'upgrade_customer_payments_association' );
		delete_option( 'edd_doing_upgrade' );

		edd_redirect( admin_url() );
	}
}

/**
 * Upgrade the User meta API Key storage to swap keys/values for performance
 *
 * @since  2.4
 * @deprecated 3.1.2
 * @return void
 */
function edd_upgrade_user_api_keys() {
	global $wpdb;

	if ( ! current_user_can( 'manage_shop_settings' ) ) {
		wp_die( __( 'You do not have permission to do shop upgrades', 'easy-digital-downloads' ), __( 'Error', 'easy-digital-downloads' ), array( 'response' => 403 ) );
	}

	edd_set_time_limit();

	$number = 10;
	$step   = isset( $_GET['step'] )
		? absint( $_GET['step'] )
		: 1;
	$offset = $step == 1
		? 0
		: ( $step - 1 ) * $number;

	if ( $step < 2 ) {
		// Check if we have any users with API Keys before moving on
		$sql     = "SELECT user_id FROM $wpdb->usermeta WHERE meta_key = 'edd_user_public_key' LIMIT 1";
		$has_key = $wpdb->get_col( $sql );

		// We had no key, just complete
		if ( empty( $has_key ) ) {
			edd_set_upgrade_complete( 'upgrade_user_api_keys' );
			delete_option( 'edd_doing_upgrade' );
			edd_redirect( admin_url() );
		}
	}

	$total = isset( $_GET['total'] )
		? absint( $_GET['total'] )
		: false;

	if ( empty( $total ) || $total <= 1 ) {
		$total = $wpdb->get_var( "SELECT count(user_id) FROM $wpdb->usermeta WHERE meta_key = 'edd_user_public_key'" );
	}

	$keys_sql   = $wpdb->prepare( "SELECT user_id, meta_key, meta_value FROM $wpdb->usermeta WHERE meta_key = 'edd_user_public_key' OR meta_key = 'edd_user_secret_key' ORDER BY user_id ASC LIMIT %d,%d;", $offset, $number );
	$found_keys = $wpdb->get_results( $keys_sql );

	if ( ! empty( $found_keys ) ) {
		foreach ( $found_keys as $key ) {
			$user_id    = $key->user_id;
			$meta_key   = $key->meta_key;
			$meta_value = $key->meta_value;

			// Generate a new entry
			update_user_meta( $user_id, $meta_value, $meta_key );

			// Delete the old one
			delete_user_meta( $user_id, $meta_key );
		}

		// More Payments found so upgrade them
		$step++;
		$redirect = add_query_arg( array(
			'page'        => 'edd-upgrades',
			'edd-upgrade' => 'upgrade_user_api_keys',
			'step'        => urlencode( $step ),
			'number'      => urlencode( $number ),
			'total'       => urlencode( $total ) ) );

		edd_redirect( $redirect );

	// No more customers found, finish up
	} else {
		edd_set_upgrade_complete( 'upgrade_user_api_keys' );
		delete_option( 'edd_doing_upgrade' );
		edd_redirect( admin_url() );
	}
}

/** 2.9.2 Upgrades ***********************************************************/

/**
 * Output the results of the file-download log data update
 *
 * @since 2.9.2
 * @deprecated 3.1.2
 */
function edd_upgrade_render_update_file_download_log_data() {
	$migration_complete = edd_has_upgrade_completed( 'update_file_download_log_data' );

	if ( $migration_complete ) : ?>
		<div id="edd-sl-migration-complete" class="notice notice-success">
			<p>
				<?php _e( '<strong>Migration complete:</strong> You have already completed the update to the file download logs.', 'easy-digital-downloads' ); ?>
			</p>
		</div>
		<?php

		delete_option( 'edd_doing_upgrade' );
		return;
	endif; ?>

	<div id="edd-migration-ready" class="notice notice-success" style="display: none;">
		<p><?php _e( '<strong>Upgrades Complete:</strong> You may now safely navigate away from this page.', 'easy-digital-downloads' ); ?></p>
	</div>

	<div id="edd-migration-nav-warn" class="notice notice-warning">
		<p><?php _e( '<strong>Important:</strong> Do not navigate away from this page until all upgrades complete.', 'easy-digital-downloads' ); ?></p>
	</div>

	<style>
		.dashicons.dashicons-yes {
			display: none;
			color: rgb(0, 128, 0);
			vertical-align: middle;
		}
	</style>

	<script>
		jQuery( function($) {
			$(document).ready(function () {
				$(document).on("DOMNodeInserted", function (e) {
					var element = e.target;

					if (element.id === 'edd-batch-success') {
						element = $(element);

						element.parent().prev().find('.edd-migration.allowed').hide();
						element.parent().prev().find('.edd-migration.unavailable').show();

						var element_wrapper   = element.parents().eq(4),
							next_step_wrapper = element_wrapper.next();

						element_wrapper.find('.dashicons.dashicons-yes').show();

						if (next_step_wrapper.find('.postbox').length) {
							next_step_wrapper.find('.edd-migration.allowed').show();
							next_step_wrapper.find('.edd-migration.unavailable').hide();

							if (auto_start_next_step) {
								next_step_wrapper.find('.edd-export-form').submit();
							}
						} else {
							$('#edd-migration-nav-warn').hide();
							$('#edd-migration-ready').slideDown();
						}
					}
				});
			});
		});
	</script>

	<div class="metabox-holder">
		<div class="postbox">
			<h2 class="hndle">
				<span><?php _e( 'Update file download logs', 'easy-digital-downloads' ); ?></span>
				<span class="dashicons dashicons-yes"></span>
			</h2>
			<div class="inside migrate-file-download-logs-control">
				<p>
					<?php _e( 'This will update the file download logs to remove some <abbr title="Personally Identifiable Information">PII</abbr> and make file download counts more accurate.', 'easy-digital-downloads' ); ?>
				</p>
				<form method="post" id="edd-fix-file-download-logs-form" class="edd-export-form edd-import-export-form">
					<span class="step-instructions-wrapper">

						<?php wp_nonce_field( 'edd_ajax_export', 'edd_ajax_export' ); ?>

						<?php if ( ! $migration_complete ) : ?>
							<span class="edd-migration allowed">
								<input type="submit" id="migrate-logs-submit" value="<?php _e( 'Update File Download Logs', 'easy-digital-downloads' ); ?>" class="button-primary"/>
							</span>
						<?php else: ?>
							<input type="submit" disabled id="migrate-logs-submit" value="<?php _e( 'Update File Download Logs', 'easy-digital-downloads' ); ?>" class="button-secondary"/>
							&mdash; <?php _e( 'File download logs have already been updated.', 'easy-digital-downloads' ); ?>
						<?php endif; ?>

						<input type="hidden" name="edd-export-class" value="EDD_File_Download_Log_Migration" />
						<span class="spinner"></span>

					</span>
				</form>
			</div><!-- .inside -->
		</div><!-- .postbox -->
	</div>

	<?php
}

/**
 * Register the batch file-download log migration
 *
 * @since 2.9.2
 * @deprecated 3.1.2
 */
function edd_register_batch_file_download_log_migration() {
	add_action( 'edd_batch_export_class_include', 'edd_include_file_download_log_migration_batch_processor', 10, 1 );
}

/**
 * Include the file-download log batch processor
 *
 * @since 2.9.2
 * @deprecated 3.1.2
 *
 * @param string $class
 */
function edd_include_file_download_log_migration_batch_processor( $class = '' ) {
	if ( 'EDD_File_Download_Log_Migration' === $class ) {
		require_once EDD_PLUGIN_DIR . 'includes/admin/upgrades/classes/class-file-download-log-migration.php';
	}
}
