<?php
/**
 * 3.0 Data Migration - Data Migrator.
 *
 * @subpackage  Admin/Upgrades/v3
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */
namespace EDD\Admin\Upgrades\v3;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Data_Migrator Class.
 *
 * This class holds all the logic for migrating data to custom tables as part
 * of EDD 3.0.
 *
 * @since 3.0
 */
class Data_Migrator {

	/**
	 * Customer addresses.
	 *
	 * @since 3.0
	 *
	 * @param object $data Data to migrate.
	 */
	public static function customer_addresses( $data = null ) {

		// Bail if no data passed.
		if ( ! $data ) {
			return;
		}

		$address = maybe_unserialize( $data->meta_value );

		$user_id = absint( $data->user_id );

		$customer = edd_get_customer_by( 'user_id', $user_id );

		$address = wp_parse_args( $address, array(
			'line1'   => '',
			'line2'   => '',
			'city'    => '',
			'state'   => '',
			'zip'     => '',
			'country' => '',
		) );

		if ( $customer ) {
			edd_add_customer_address( array(
				'customer_id' => $customer->id,
				'type'        => 'primary',
				'address'     => $address['line1'],
				'address2'    => $address['line2'],
				'city'        => $address['city'],
				'region'      => $address['state'],
				'postal_code' => $address['zip'],
				'country'     => $address['country'],
			) );
		}
	}

	/**
	 * Customer email addresses.
	 *
	 * @since 3.0
	 *
	 * @param object $data Data to migrate.
	 */
	public static function customer_email_addresses( $data = null ) {

		// Bail if no data passed.
		if ( ! $data ) {
			return;
		}

		$customer_id = absint( $data->edd_customer_id );

		edd_add_customer_email_address( array(
			'customer_id' => $customer_id,
			'email'       => $data->meta_value,
		) );
	}

	/**
	 * Customer notes.
	 *
	 * @since 3.0
	 *
	 * @param object $data Data to migrate.
	 */
	public static function customer_notes( $data = null ) {

		// Bail if no data passed.
		if ( ! $data ) {
			return;
		}

		$customer_id = absint( $data->id );

		if ( property_exists( $data, 'notes' ) && ! empty( $data->notes ) ) {
			$notes = array_reverse( array_filter( explode( "\n\n", $data->notes ) ) );

			$notes = array_map( function( $val ) {
				return explode( ' - ', $val );
			}, $notes );

			if ( ! empty( $notes ) ) {
				foreach ( $notes as $note ) {
					$date = isset( $note[0] )
						? Carbon::parse( $note[0], edd_get_timezone_id() )->setTimezone( 'UTC' )->toDateTimeString()
						: '';

					$note_content = isset( $note[1] )
						? $note[1]
						: '';

					edd_add_note( array(
						'user_id'       => 0,
						'object_id'     => $customer_id,
						'object_type'   => 'customer',
						'content'       => $note_content,
						'date_created'  => $date,
						'date_modified' => $date,
					) );
				}
			}
		}
	}

	/**
	 * Discounts.
	 *
	 * @since 3.0
	 *
	 * @param object $data Data to migrate.
	 */
	public static function discounts( $data = null ) {

		// Bail if no data passed.
		if ( ! $data ) {
			return;
		}

		$data = get_post( $data->ID );

		$args            = array();
		$meta            = get_post_custom( $data->ID );
		$meta_to_migrate = array();

		foreach ( $meta as $key => $value ) {
			if ( false === strpos( $key, '_edd_discount' ) ) {

				// This is custom meta from another plugin that needs to be migrated to the new meta table.
				$meta_to_migrate[ $key ] = maybe_unserialize( $value[0] );
				continue;
			}

			$value = maybe_unserialize( $value[0] );
			$args[ str_replace( '_edd_discount_', '', $key ) ] = $value;
		}

		// If the discount name was not stored in post_meta, use value from the WP_Post object.
		if ( ! isset( $args['name'] ) ) {
			$args['name'] = $data->post_title;
		}

		$args['date_created']  = $data->post_date_gmt;
		$args['date_modified'] = $data->post_modified_gmt;

		// Use edd_store_discount() so any legacy data is handled correctly.
		$discount_id = edd_store_discount( $args );

		// Migrate any additional meta.
		if ( ! empty( $meta_to_migrate ) ) {
			foreach ( $meta_to_migrate as $key => $value ) {
				edd_add_adjustment_meta( $discount_id, $key, $value );
			}
		}

		// Store legacy discount ID.
		edd_add_adjustment_meta( $discount_id, 'legacy_discount_id', $data->ID );
	}

	/**
	 * Logs.
	 *
	 * @since 3.0
	 *
	 * @param object $data Data to migrate.
	 */
	public static function logs( $data = null ) {
		global $wpdb;

		// Bail if no data passed.
		if ( ! $data ) {
			return;
		}

		if ( 'file_download' === $data->slug ) {
			$meta = $wpdb->get_results( $wpdb->prepare( "SELECT meta_key, meta_value FROM {$wpdb->postmeta} WHERE post_id = %d", absint( $data->ID ) ) );

			$post_meta = array();

			foreach ( $meta as $meta_item ) {
				$post_meta[ $meta_item->meta_key ] = maybe_unserialize( $meta_item->meta_value );
			}

			$log_data = array(
				'product_id'    => $data->post_parent,
				'file_id'       => $post_meta['_edd_log_file_id'],
				'order_id'      => isset( $post_meta['_edd_log_payment_id'] )  ? $post_meta['_edd_log_payment_id']  : 0,
				'price_id'      => isset( $post_meta['_edd_log_price_id'] )    ? $post_meta['_edd_log_price_id']    : 0,
				'customer_id'   => isset( $post_meta['_edd_log_customer_id'] ) ? $post_meta['_edd_log_customer_id'] : 0,
				'ip'            => $post_meta['_edd_log_ip'],
				'date_created'  => $data->post_date_gmt,
				'date_modified' => $data->post_modified_gmt,
			);

			edd_add_file_download_log( $log_data );
		} elseif ( 'api_request' === $data->slug ) {
			$meta = $wpdb->get_results( $wpdb->prepare( "SELECT meta_key, meta_value FROM {$wpdb->postmeta} WHERE post_id = %d", absint( $data->ID ) ) );

			$post_meta = array();

			foreach ( $meta as $meta_item ) {
				$post_meta[ $meta_item->meta_key ] = maybe_unserialize( $meta_item->meta_value );
			}

			$post_meta = wp_parse_args( $post_meta, array(
				'_edd_log_request_ip' => '',
				'_edd_log_user'       => 0,
				'_edd_log_key'        => 'public',
				'_edd_log_token'      => 'public',
				'_edd_log_version'    => '',
				'_edd_log_time'       => '',
			) );

			$log_data = array(
				'ip'            => $post_meta['_edd_log_request_ip'],
				'user_id'       => $post_meta['_edd_log_user'],
				'api_key'       => $post_meta['_edd_log_key'],
				'token'         => $post_meta['_edd_log_token'],
				'version'       => $post_meta['_edd_log_version'],
				'time'          => $post_meta['_edd_log_time'],
				'request'       => $data->post_excerpt,
				'error'         => $data->post_content,
				'date_created'  => $data->post_date_gmt,
				'date_modified' => $data->post_modified_gmt,
			);

			edd_add_api_request_log( $log_data );
		} else {
			$post = \WP_Post::get_instance( $data->ID );

			$log_data = array(
				'object_id'     => $post->post_parent,
				'object_type'   => 'download',
				'type'          => $data->slug,
				'title'         => $data->post_title,
				'message'       => $data->post_content,
				'date_created'  => $data->post_date_gmt,
				'date_modified' => $data->post_modified_gmt,
			);

			$meta            = get_post_custom( $data->ID );
			$meta_to_migrate = array();

			foreach ( $meta as $key => $value ) {
				$meta_to_migrate[ $key ] = maybe_unserialize( $value[0] );
			}

			$new_log_id = edd_add_log( $log_data );

			if ( ! empty( $meta_to_migrate ) ) {
				foreach ( $meta_to_migrate as $key => $value ) {
					edd_add_log_meta( $new_log_id, $key, $value );
				}
			}
		}
	}

	/**
	 * Order notes.
	 *
	 * @since 3.0
	 *
	 * @param object $data Data to migrate.
	 */
	public static function order_notes( $data = null ) {

		// Bail if no data passed.
		if ( ! $data ) {
			return;
		}

		$note_data = array(
			'object_id'     => $data->object_id,
			'object_type'   => 'order',
			'date_created'  => $data->comment_date_gmt,
			'date_modified' => $data->comment_date_gmt,
			'content'       => $data->comment_content,
			'user_id'       => $data->user_id,
		);

		$id = edd_add_note( $note_data );

		$meta = get_comment_meta( $data->comment_ID );
		if ( ! empty( $meta ) ) {
			foreach ( $meta as $key => $value ) {
				edd_add_note_meta( $id, $key, $value );
			}
		}
	}

	public static function orders( $data = null ) {

		// Bail if no data passed.
		if ( ! $data ) {
			return;
		}

		/** Create a new order ***************************************/

		$meta = get_post_custom( $data->ID );

		$payment_meta = maybe_unserialize( $meta['_edd_payment_meta'][0] );
		$user_info    = maybe_unserialize( $payment_meta['user_info'] );

		// Some old EDD data has the user info serialized, but starting with something other than a: so it can't be unserialized
		if ( ! is_array( $user_info ) && is_string( $user_info ) ) {
			$user_info = substr_replace( $user_info, 'a', 0, 1 );
		}

		$order_number   = isset( $meta['_edd_payment_number'][0] ) ? $meta['_edd_payment_number'][0] : '';
		$user_id        = isset( $meta['_edd_payment_user_id'][0] ) && ! empty( $meta['_edd_payment_user_id'][0] ) ? $meta['_edd_payment_user_id'][0] : 0;
		$ip             = isset( $meta['_edd_payment_user_ip'][0] ) ? $meta['_edd_payment_user_ip'][0] : '';
		$mode           = isset( $meta['_edd_payment_mode'][0] ) ? $meta['_edd_payment_mode'][0] : 'live';
		$gateway        = isset( $meta['_edd_payment_gateway'][0] ) && ! empty( $meta['_edd_payment_gateway'][0] ) ? $meta['_edd_payment_gateway'][0] : 'manual';
		$customer_id    = isset( $meta['_edd_payment_customer_id'][0] ) ? $meta['_edd_payment_customer_id'][0] : 0;
		$date_completed = isset( $meta['_edd_completed_date'][0] ) ? $meta['_edd_completed_date'][0] : '0000-00-00 00:00:00';
		$purchase_key   = isset( $meta['_edd_payment_purchase_key'][0]) ? $meta['_edd_payment_purchase_key'][0] : false;
		$purchase_email = isset( $meta['_edd_payment_user_email'][0] ) ? $meta['_edd_payment_user_email'][0] : $payment_meta['email'];

		// Get the customer object
		if ( ! empty( $customer_id ) ) {
			$customer = edd_get_customer( $customer_id );
		} else if ( ! empty( $purchase_email ) ) {
			$customer = edd_get_customer_by( 'email', $purchase_email );
			if ( $customer ) {
				$customer_id = $customer->id;
			}
		}

		if ( false === $purchase_key ) {
			$purchase_key = isset( $payment_meta['key'] ) ? $payment_meta['key'] : '';
		}

		// Do not use -1 as the user ID.
		$user_id = ( -1 === $user_id )
			? 0
			: $user_id;

		// Account for possible double serialization of the cart_details
		$cart_details = maybe_unserialize( $payment_meta['cart_details'] );
		// Some old EDD data has the cart details serialized, but starting with something other than a: so it can't be unserialized
		if ( ! is_array( $cart_details ) && is_string( $cart_details ) ) {
			$cart_details = substr_replace( $cart_details, 'a', 0, 1 );
		}

		// Account for possible double serialization of the cart_details
		$cart_downloads = maybe_unserialize( $payment_meta['downloads'] );
		// Some old EDD data has the downloads serialized, but starting with something other than a: so it can't be unserialized
		if ( ! is_array( $cart_downloads ) && is_string( $cart_downloads ) ) {
			$cart_downloads = substr_replace( $cart_downloads, 'a', 0, 1 );
		}

		$order_status = 'publish' === $data->post_status ? 'complete' : $data->post_status;

		// If there are no items, and it's abandoned, just return, since this isn't a valid order.
		if ( 'abandoned' === $order_status && empty( $cart_downloads ) && empty( $cart_details ) ) {
			edd_debug_log( 'Skipping order ' . $data->ID . ' due to abandoned status and no products.', true );
			return;
		}

		$subtotal = 0;
		$tax      = 0;
		$discount = 0;
		$total    = 0;

		// In some cases (very few) there is no cart details...so we have to just avoid this part.
		if ( ! empty( $cart_details ) && is_array( $cart_details ) ) {
			foreach ( $cart_details as $cart_item ) {
				$subtotal += (float) isset( $cart_item['subtotal'] ) ? $cart_item['subtotal'] : 0;
				$tax      += (float) isset( $cart_item['tax'] )      ? $cart_item['tax']      : 0;
				$discount += (float) isset( $cart_item['discount'] ) ? $cart_item['discount'] : 0;
				$total    += (float) isset( $cart_item['price'] )    ? $cart_item['price']    : 0;
			}
		}

		// Maybe convert the date completed to UTC.
		$non_completed_statuses = apply_filters( 'edd_30_noncomplete_statuses', array ( 'pending', 'cancelled', 'abandoned', 'processing' ) );
		if ( ! in_array( $order_status, $non_completed_statuses ) && '0000-00-00 00:00:00' !== $date_completed ) {
			$date_completed = EDD()->utils->date( $date_completed, edd_get_timezone_id() )->setTimezone( 'UTC' )->toDateTimeString();
		}

		$order_data = array(
			'parent'         => $data->post_parent,
			'order_number'   => $order_number,
			'status'         => $order_status,
			'type'           => 'sale',
			'date_created'   => $data->post_date_gmt, // GMT is stored in the database as the offset is applied by the new query classes.
			'date_modified'  => $data->post_modified_gmt, // GMT is stored in the database as the offset is applied by the new query classes.
			'date_completed' => $date_completed,
			'user_id'        => $user_id,
			'customer_id'    => $customer_id,
			'email'          => $purchase_email,
			'ip'             => $ip,
			'gateway'        => $gateway,
			'mode'           => $mode,
			'currency'       => $payment_meta['currency'],
			'payment_key'    => $purchase_key,
			'subtotal'       => $subtotal,
			'tax'            => $tax,
			'discount'       => $discount,
			'total'          => $total,
		);

		$order_id = edd_add_order( $order_data );

		// Reset the $refund_id variable so that we don't end up accidentally creating refunds.
		$refund_id = 0;

		if ( 'refunded' === $order_status ) {

			// Since the refund is a near copy of the original order, copy over the arguments.
			$refund_data = $order_data;

			$refund_data['parent']       = $order_id;
			$refund_data['order_number'] = $order_id . apply_filters( 'edd_order_refund_suffix', '-R-' ) . '1';
			$refund_data['type']         = 'refund';
			$refund_data['status']       = 'complete';

			// Negate the amounts
			$refund_data['subtotal'] = edd_negate_amount( $subtotal );
			$refund_data['tax']      = edd_negate_amount( $tax );
			$refund_data['discount'] = edd_negate_amount( $discount );
			$refund_data['total']    = edd_negate_amount( $total );


			// These are the best guess at the date it was refunded since we didn't store that prior.
			$refund_data['date_created']  = $data->post_modified_gmt;
			$refund_data['date_modified'] = $data->post_modified_gmt;

			$refund_id = edd_add_order( $refund_data );

		}

		// First & last name.
		$user_info['first_name'] = ! empty( $user_info['first_name'] )
			? $user_info['first_name']
			: '';
		$user_info['last_name']  = ! empty( $user_info['last_name'] )
			? $user_info['last_name']
			: '';

		// Add order address.
		$user_info['address'] = ! empty( $user_info['address'] )
			? $user_info['address']
			: array();

		$user_info['address'] = wp_parse_args( $user_info['address'], array(
			'line1'   => '',
			'line2'   => '',
			'city'    => '',
			'zip'     => '',
			'country' => '',
			'state'   => '',
		) );

		$order_address_data = array(
			'order_id'    => $order_id,
			'first_name'  => isset( $user_info['first_name'] )         ? $user_info['first_name']         : '',
			'last_name'   => isset( $user_info['last_name'] )          ? $user_info['last_name']          : '',
			'address'     => isset( $user_info['address']['line1'] )   ? $user_info['address']['line1']   : '',
			'address2'    => isset( $user_info['address']['line2'] )   ? $user_info['address']['line2']   : '',
			'city'        => isset( $user_info['address']['city'] )    ? $user_info['address']['city']    : '',
			'region'      => isset( $user_info['address']['state'] )   ? $user_info['address']['state']   : '',
			'country'     => isset( $user_info['address']['country'] ) ? $user_info['address']['country'] : '',
			'postal_code' => isset( $user_info['address']['zip'] )     ? $user_info['address']['zip']     : '',
		);

		// Remove empty data.
		$order_address_data = array_filter( $order_address_data );

		// Add to edd_order_addresses table.
		edd_add_order_address( $order_address_data );

		// Maybe add the address to the edd_customer_addresses.
		$customer_address_data = $order_address_data;

		// We don't need to pass this data to edd_maybe_add_customer_address().
		unset( $customer_address_data['order_id'] );
		unset( $customer_address_data['first_name'] );
		unset( $customer_address_data['last_name'] );

		// Maybe add address to customer record.
		edd_maybe_add_customer_address( $customer_id, $customer_address_data );

		// Maybe add email address to customer record
		if ( ! empty( $customer ) && $customer instanceof EDD_Customer ) {
			$primary = ( $customer->email === $purchase_email );
			$customer->add_email( $purchase_email, $primary );
		}

		/** Migrate meta *********************************************/

		if ( isset( $meta['_edd_payment_unlimited_downloads'] ) && ! empty( $meta['_edd_payment_unlimited_downloads'][0] ) ) {
			edd_add_order_meta( $order_id, 'unlimited_downloads', $meta['_edd_payment_unlimited_downloads'][0] );
		}

		if ( isset( $meta['_edd_payment_transaction_id'] ) && ! empty( $meta['_edd_payment_transaction_id'][0] ) ) {
			edd_add_order_transaction( array(
				'object_id'      => $order_id,
				'object_type'    => 'order',
				'transaction_id' => $meta['_edd_payment_transaction_id'][0],
				'gateway'        => $gateway,
				'status'         => 'complete',
				'total'          => $total,
				'date_created'   => $date_completed,
				'date_modified'  => $date_completed,
			) );
		}

		// By default, this is what is stored in payment meta.
		$core_meta_keys = array(
			'fees',
			'key',
			'email',
			'date',
			'user_info',
			'downloads',
			'cart_details',
			'currency',
			'discount',
			'subtotal',
			'tax',
		);

		$remaining_payment_meta = array_diff_key( $meta['_edd_payment_meta'], array_flip( $core_meta_keys ) );

		// If we have extra payment meta, it needs to be migrated across.
		if ( 0 < count( $remaining_payment_meta ) ) {
			edd_add_order_meta( $order_id, 'payment_meta', $remaining_payment_meta );
		}

		/** Create order items ***************************************/

		if ( ! empty( $cart_details ) ) {
			foreach ( $cart_details as $key => $cart_item ) {
				// Reset any conditional IDs to be safe.
				$refund_order_item_id = 0;

				// Get product name.
				$product_name = isset( $cart_item['name'] )
					? $cart_item['name']
					: '';

				// Get price ID.
				$price_id = isset( $cart_item['item_number']['options']['price_id'] )
					? absint( $cart_item['item_number']['options']['price_id'] )
					: 0;

				// Get item price.
				$cart_item['item_price'] = isset( $cart_item['item_price'] )
					? (float) $cart_item['item_price']
					: (float) $cart_item['price'];

				// Get quantity.
				$cart_item['quantity'] = isset( $cart_item['quantity'] )
					? $cart_item['quantity']
					: 1;

				// Get subtotal.
				$cart_item['subtotal'] = isset( $cart_item['subtotal'] )
					? (float) $cart_item['subtotal']
					: (float) $cart_item['quantity'] * $cart_item['item_price'];

				// Get discount.
				$cart_item['discount'] = isset( $cart_item['discount'] )
					? (float) $cart_item['discount']
					: 0.00;

				// Get tax.
				$cart_item['tax'] = isset( $cart_item['tax'] )
					? (float) $cart_item['tax']
					: 0.00;

				$order_item_args = array(
					'order_id'      => $order_id,
					'product_id'    => $cart_item['id'],
					'product_name'  => $product_name,
					'price_id'      => $price_id,
					'cart_index'    => $key,
					'type'          => 'download',
					'status'        => $order_status,
					'quantity'      => $cart_item['quantity'],
					'amount'        => (float) $cart_item['item_price'],
					'subtotal'      => $cart_item['subtotal'],
					'discount'      => $cart_item['discount'],
					'tax'           => $cart_item['tax'],
					'total'         => (float) $cart_item['price'],
					'date_created'  => $data->post_date_gmt,
					'date_modified' => $data->post_modified_gmt,
				);

				$order_item_id = edd_add_order_item( $order_item_args );

				if ( ! empty( $refund_id ) ) {

					// Since the refund is a near copy of the original order, copy over the arguments.
					$refund_item_args = $order_item_args;

					$refund_item_args['order_id'] = $refund_id;
					$refund_item_args['status']   = 'refunded';

					// Negate the amounts
					$refund_item_args['quantity'] = edd_negate_amount( $cart_item['quantity'] );
					$refund_item_args['amount']   = edd_negate_amount( (float) $cart_item['item_price'] );
					$refund_item_args['subtotal'] = edd_negate_amount( $cart_item['subtotal'] );
					$refund_item_args['discount'] = edd_negate_amount( $cart_item['discount'] );
					$refund_item_args['tax']      = edd_negate_amount( $cart_item['tax'] );
					$refund_item_args['total']    = edd_negate_amount( (float) $cart_item['price'] );

					// These are our best estimates since we did not store the refund date previously.
					$refund_item_args['date_crated']   = $data->post_modified_gmt;
					$refund_item_args['date_modified'] = $data->post_modified_gmt;

					$refund_order_item_id = edd_add_order_item( $refund_item_args );

				}

				// Store order item fees as adjustments.
				if ( isset( $cart_item['fees'] ) && ! empty( $cart_item['fees'] ) ) {
					foreach ( $cart_item['fees'] as $fee_id => $fee ) {
						// Reset any conditional IDs to be safe.
						$refund_adjustment_id = 0;

						$tax_rate = isset( $meta['_edd_payment_tax_rate'][0] )
							? (float) $meta['_edd_payment_tax_rate'][0]
							: 0.00;

						$tax = ( isset( $fee['no_tax'] ) && false === $fee['no_tax'] && ! empty( $tax_rate ) ) || ( $fee['amount'] < 0 && ! empty( $tax_rate ) )
							? floatval( floatval( $fee['amount'] ) - ( floatval( $fee['amount'] ) / ( 1 + $tax_rate ) ) )
							: 0.00;

						// Add the adjustment.
						$adjustment_args = array(
							'object_id'   => $order_item_id,
							'object_type' => 'order_item',
							'type'        => 'fee',
							'description' => $fee['label'],
							'subtotal'    => floatval( $fee['amount'] ),
							'tax'         => $tax,
							'total'       => floatval( $fee['amount'] ) + $tax,
						);

						$adjustment_id = edd_add_order_adjustment( $adjustment_args );

						// Fee ID.
						edd_add_order_adjustment_meta( $adjustment_id, 'fee_id', $fee_id );

						if ( ! empty( $refund_id ) ) {
							$refund_adjustment_args = $adjustment_args;
							$refund_adjustment_args['object_id'] = $refund_order_item_id;
							$refund_adjustment_args['subtotal']  = edd_negate_amount( floatval( $fee['amount'] ) );
							$refund_adjustment_args['tax']       = edd_negate_amount( $tax );
							$refund_adjustment_args['total']     = edd_negate_amount( floatval( $fee['amount'] ) + $tax );


							$refund_adjustment_id = edd_add_order_adjustment( $refund_adjustment_args );
							edd_add_order_adjustment_meta( $refund_adjustment_id, 'fee_id', $fee_id );
						}

						// Maybe store download ID.
						if ( isset( $fee['download_id'] ) && ! empty( $fee['download_id'] ) ) {
							edd_add_order_adjustment_meta( $adjustment_id, 'download_id', $fee['download_id'] );

							if ( ! empty( $refund_adjustment_id ) ) {
								edd_add_order_adjustment_meta( $refund_adjustment_id, 'download_id', $fee['download_id'] );
							}
						}

						// Maybe store price ID.
						if ( isset( $fee['price_id'] ) && ! is_null( $fee['price_id'] ) ) {
							edd_add_order_adjustment_meta( $adjustment_id, 'price_id', $fee['price_id'] );

							if ( ! empty( $refund_adjustment_id ) ) {
								edd_add_order_adjustment_meta( $refund_adjustment_id, 'price_id', $fee['price_id'] );
							}
						}
					}
				}
			}

			// Compatibility with older versions of EDD.
			// Older versions stored a single dimensional array of download IDs.
		} elseif ( is_array( $cart_downloads ) && count( $cart_downloads ) === count( $cart_downloads, COUNT_RECURSIVE ) ) {
			foreach ( $cart_downloads as $cart_index => $download_id ) {
				$download = edd_get_download( $download_id );

				$order_item_args = array(
					'order_id'      => $order_id,
					'product_id'    => $download_id,
					'product_name'  => $download->post_name,
					'price_id'      => 0,
					'cart_index'    => $cart_index,
					'type'          => 'download',
					'quantity'      => 1,
					'amount'        => (float) $payment_meta['amount'],
					'subtotal'      => (float) $payment_meta['amount'],
					'discount'      => 0.00,
					'tax'           => 0.00,
					'total'         => (float) $payment_meta['amount'],
					'date_created'  => $data->post_date_gmt,
					'date_modified' => $data->post_modified_gmt,
				);

				edd_add_order_item( $order_item_args );

				if ( ! empty( $refund_id ) ) {

					// Since the refund is a near copy of the original order, copy over the arguments.
					$refund_item_args = $order_item_args;

					$refund_item_args['order_id'] = $refund_id;
					$refund_item_args['quantity'] = edd_negate_amount( 1 );
					$refund_item_args['amount']   = edd_negate_amount( (float) $payment_meta['amount'] );
					$refund_item_args['subtotal'] = edd_negate_amount( (float) $payment_meta['amount'] );
					$refund_item_args['total']    = edd_negate_amount( (float) $payment_meta['amount'] );

					// These are the best guess at the time, since we didn't store this data previously.
					$refund_item_args['date_created']  = $data->post_modified_gmt;
					$refund_item_args['date_modified'] = $data->post_modified_gmt;

					edd_add_order_item( $order_item_args );
				}
			}
		}

		/** Create order adjustments *********************************/

		$tax_rate = isset( $meta['_edd_payment_tax_rate'][0] )
			? (float) $meta['_edd_payment_tax_rate'][0]
			: 0.00;

		// Tax rate is no longer stored in meta.
		edd_add_order_adjustment( array(
			'object_id'   => $order_id,
			'object_type' => 'order',
			'type_id'     => 0,
			'type'        => 'tax_rate',
			'total'       => $tax_rate,
		) );

		if ( ! empty( $refund_id ) ) {
			edd_add_order_adjustment( array(
				'object_id'   => $refund_id,
				'object_type' => 'order',
				'type_id'     => 0,
				'type'        => 'tax_rate',
				'total'       => $tax_rate,
			) );
		}

		if ( isset( $payment_meta['fees'] ) && ! empty( $payment_meta['fees'] ) ) {
			foreach ( $payment_meta['fees'] as $fee_id => $fee ) {
				// Reset any conditional IDs to be safe.
				$refund_adjustment_id = 0;

				// Reverse engineer the tax calculation.
				$tax = ( isset( $fee['no_tax'] ) && false === $fee['no_tax'] && ! empty( $tax_rate ) ) || ( $fee['amount'] < 0 && ! empty( $tax_rate ) )
					? floatval( floatval( $fee['amount'] ) - ( floatval( $fee['amount'] ) / ( 1 + $tax_rate ) ) )
					: 0.00;

				// Add the adjustment.
				$adjustment_args = array(
					'object_id'   => $order_id,
					'object_type' => 'order',
					'type'        => 'fee',
					'description' => $fee['label'],
					'subtotal'    => floatval( $fee['amount'] ),
					'tax'         => $tax,
					'total'       => floatval( $fee['amount'] ) + $tax,
				);

				$adjustment_id = edd_add_order_adjustment( $adjustment_args );

				// Fee ID.
				edd_add_order_adjustment_meta( $adjustment_id, 'fee_id', $fee_id );

				if ( ! empty( $refund_id ) ) {

					// Since the refund is a near copy of the original order, copy over the arguments.
					$refund_adjustment_args = $adjustment_args;

					$refund_adjustment_args['object_id'] = $refund_id;

					// Negate the amounts.
					$refund_adjustment_args['subtotal'] = edd_negate_amount( floatval( $fee['amount'] ) );
					$refund_adjustment_args['tax']      = edd_negate_amount( $tax );
					$refund_adjustment_args['total']    = edd_negate_amount( floatval( $fee['amount'] ) + $tax );

					$refund_adjustment_id = edd_add_order_adjustment( $refund_adjustment_args );
				}

				// Maybe store download ID.
				if ( isset( $fee['download_id'] ) && ! empty( $fee['download_id'] ) ) {
					edd_add_order_adjustment_meta( $adjustment_id, 'download_id', $fee['download_id'] );

					if ( ! empty( $refund_adjustment_id ) ) {
						edd_add_order_adjustment_meta( $refund_adjustment_id, 'download_id', $fee['download_id'] );
					}
				}

				// Maybe store price ID.
				if ( isset( $fee['price_id'] ) && ! is_null( $fee['price_id'] ) ) {
					edd_add_order_adjustment_meta( $adjustment_id, 'price_id', $fee['price_id'] );

					if ( ! empty( $refund_adjustment_id ) ) {
						edd_add_order_adjustment_meta( $refund_adjustment_id, 'price_id', $fee['price_id'] );
					}
				}
			}
		}

		// Insert discounts.
		$discounts = ! empty( $user_info['discount'] )
			? $user_info['discount']
			: array();

		if ( ! is_array( $discounts ) ) {
			$discounts = explode( ',', $discounts );
		}

		if ( ! empty( $discounts ) && ( 'none' !== $discounts[0] ) ) {
			foreach ( $discounts as $discount ) {

				/** @var \EDD_Discount $discount */
				$discount = edd_get_discount_by( 'code', $discount );

				if ( false === $discount ) {
					continue;
				}

				edd_add_order_adjustment( array(
					'object_id'   => $order_id,
					'object_type' => 'order',
					'type_id'     => $discount->id,
					'type'        => 'discount',
					'description' => $discount,
					'subtotal'    => $subtotal - $discount->get_discounted_amount( $subtotal ),
					'total'       => $subtotal - $discount->get_discounted_amount( $subtotal ),
				) );

				if ( ! empty( $refund_id ) ) {
					edd_add_order_adjustment( array(
						'object_id'   => $refund_id,
						'object_type' => 'order',
						'type_id'     => $discount->id,
						'type'        => 'discount',
						'description' => $discount,
						'subtotal'    => edd_negate_amount($subtotal - $discount->get_discounted_amount( $subtotal ) ),
						'total'       => edd_negate_amount( $subtotal - $discount->get_discounted_amount( $subtotal ) ),
					) );
				}
			}
		}

		/** Create order meta ****************************************/

		$core_meta_keys = array(
			'_edd_payment_user_email',
			'_edd_payment_customer_id',
			'_edd_payment_user_id',
			'_edd_payment_user_ip',
			'_edd_payment_purchase_key',
			'_edd_payment_total',
			'_edd_payment_mode',
			'_edd_payment_gateway',
			'_edd_payment_meta',
			'_edd_payment_tax',
			'_edd_payment_tax_rate',
			'_edd_completed_date',
			'_edd_payment_unlimited_downloads',
			'_edd_payment_number',
		);

		$remaining_meta = array_diff_key( $meta, array_flip( $core_meta_keys ) );

		// Migrate additional payment meta.
		foreach ( $remaining_meta as $meta_key => $meta_value ) {
			$meta_value = $meta_value[0];

			edd_add_order_meta( $order_id, $meta_key, $meta_value );
		}

		// Store the legacy ID in order meta.
		edd_add_order_meta( $order_id, 'legacy_order_id', $data->ID );
	}

	/**
	 * Tax rates.
	 *
	 * @since 3.0
	 *
	 * @param object $data Data to migrate.
	 */
	public static function tax_rates( $data = null ) {

		// Bail if no data passed.
		if ( ! $data ) {
			return;
		}

		$scope = isset( $data['global'] )
			? 'country'
			: 'region';

		$region = isset( $data['state'] )
			? sanitize_text_field( $data['state'] )
			: '';

		$adjustment_data = array(
			'name'        => $data['country'],
			'status'      => 'active',
			'type'        => 'tax_rate',
			'scope'       => $scope,
			'amount_type' => 'percent',
			'amount'      => floatval( $data['rate'] ),
			'description' => $region,
		);

		edd_add_adjustment( $adjustment_data );
	}
}
