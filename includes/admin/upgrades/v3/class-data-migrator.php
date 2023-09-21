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
	 * @param object $data       Data to migrate.
	 * @param string $type       The type of address this is.
	 */
	public static function customer_addresses( $data = null, $type = 'billing' ) {

		// Bail if no data passed.
		if ( ! $data ) {
			return;
		}

		$address = maybe_unserialize( $data->meta_value );

		$user_id = absint( $data->user_id );

		$customer = edd_get_customer_by( 'user_id', $user_id );

		$address = wp_parse_args(
			$address,
			array(
				'line1'   => '',
				'line2'   => '',
				'city'    => '',
				'state'   => '',
				'zip'     => '',
				'country' => '',
			)
		);

		$address_to_check = array_filter( $address );

		// Do not migrate empty addresses.
		if ( empty( $address_to_check ) ) {
			return;
		}

		if ( $customer ) {
			edd_maybe_add_customer_address(
				$customer->id,
				array(
					'is_primary'   => true,
					'name'         => $customer->name,
					'address'      => $address['line1'],
					'address2'     => $address['line2'],
					'city'         => $address['city'],
					'region'       => $address['state'],
					'postal_code'  => $address['zip'],
					'country'      => $address['country'],
					'date_created' => $customer->date_created,
				)
			);
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
		if ( ! isset( $data->edd_customer_id ) || ! isset( $data->meta_value ) ) {
			return;
		}

		$customer = edd_get_customer( absint( $data->edd_customer_id ) );
		if ( ! $customer ) {
			return;
		}

		edd_add_customer_email_address(
			array(
				'customer_id'  => $customer->id,
				'email'        => $data->meta_value,
				'date_created' => $customer->date_created,
			)
		);
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
					try {
						$date = isset( $note[0] )
							? EDD()->utils->date( $note[0], edd_get_timezone_id() )->setTimezone( 'UTC' )->toDateTimeString()
							: '';
					} catch ( \Exception $e ) {
						// An empty date will be changed to current time in BerlinDB.
						$date = '';
					}

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
		$core_meta       = array(
			'code',
			'name',
			'status',
			'uses',
			'max_uses',
			'amount',
			'start',
			'expiration',
			'type',
			'min_price',
			'product_reqs',
			'product_condition',
			'excluded_products',
			'is_not_global',
			'is_single_use',
		);

		foreach ( $meta as $key => $value ) {
			$value = maybe_unserialize( $value[0] );
			if ( false === strpos( $key, '_edd_discount' ) ) {

				// This is custom meta from another plugin that needs to be migrated to the new meta table.
				$meta_to_migrate[ $key ] = $value;
				continue;
			}
			$meta_key = str_replace( '_edd_discount_', '', $key );
			if ( ! in_array( $meta_key, $core_meta, true ) ) {
				$meta_to_migrate[ $meta_key ] = $value;
				continue;
			}

			$args[ $meta_key ] = $value;
		}

		// If the discount name was not stored in post_meta, use value from the WP_Post object.
		if ( ! isset( $args['name'] ) ) {
			$args['name'] = $data->post_title;
		}

		$args['id']            = $data->ID;
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

		$meta_to_migrate = array();
		if ( 'file_download' === $data->slug ) {
			$meta = $wpdb->get_results( $wpdb->prepare( "SELECT meta_key, meta_value FROM {$wpdb->postmeta} WHERE post_id = %d", absint( $data->ID ) ) );

			$post_meta = array();

			foreach ( $meta as $meta_item ) {
				$post_meta[ $meta_item->meta_key ] = maybe_unserialize( $meta_item->meta_value );
			}

			$log_data = array(
				'product_id'    => $data->post_parent,
				/*
				 * Custom Deliverables was overriding the file ID to be a string instead of an integer. The preg_replace
				 * allows us to try to salvage the file ID from that string.
				 */
				'file_id'       => isset( $post_meta['_edd_log_file_id'] ) ? preg_replace( '/[^0-9]/', '', $post_meta['_edd_log_file_id'] ) : 0,
				'order_id'      => isset( $post_meta['_edd_log_payment_id'] )  ? $post_meta['_edd_log_payment_id']  : 0,
				'price_id'      => isset( $post_meta['_edd_log_price_id'] )    ? $post_meta['_edd_log_price_id']    : 0,
				'customer_id'   => isset( $post_meta['_edd_log_customer_id'] ) ? $post_meta['_edd_log_customer_id'] : 0,
				'ip'            => isset( $post_meta['_edd_log_ip'] ) ? $post_meta['_edd_log_ip'] : '',
				'date_created'  => $data->post_date_gmt,
				'date_modified' => $data->post_modified_gmt,
			);

			$meta_to_remove = array(
				'_edd_log_file_id',
				'_edd_log_payment_id',
				'_edd_log_price_id',
				'_edd_log_customer_id',
				'_edd_log_ip',
				'_edd_log_user_id',
			);
			// If the log doesn't have a customer ID, but does have a user ID, keep the user ID as metadata.
			if ( empty( $log_data['customer_id'] ) && ! empty( $post_meta['_edd_log_user_id'] ) && ! in_array( $post_meta['_edd_log_user_id'], array( 0, -1 ) ) ) {
				$meta_to_remove = array_diff( $meta_to_remove, array( '_edd_log_user_id' ) );
			}
			$meta_to_migrate   = $post_meta;
			$new_log_id        = edd_add_file_download_log( $log_data );
			$add_meta_function = 'edd_add_file_download_log_meta';

			/**
			 * Triggers after a file download log has been migrated.
			 *
			 * @since 3.0
			 *
			 * @param int    $new_log_id ID of the newly created log.
			 * @param object $data       Data from the posts table. (Essentially a `WP_Post`, without being that object.)
			 * @param array  $post_meta  All meta associated with this log.
			 */
			do_action( 'edd_30_migrate_file_download_log', $new_log_id, $data, $post_meta );
		} elseif ( 'api_request' === $data->slug ) {
			$meta = $wpdb->get_results( $wpdb->prepare( "SELECT meta_key, meta_value FROM {$wpdb->postmeta} WHERE post_id = %d", absint( $data->ID ) ) );

			$post_meta = array();

			foreach ( $meta as $meta_item ) {
				$post_meta[ $meta_item->meta_key ] = maybe_unserialize( $meta_item->meta_value );
			}

			$post_meta = wp_parse_args(
				$post_meta,
				array(
					'_edd_log_request_ip' => '',
					'_edd_log_user'       => 0,
					'_edd_log_key'        => 'public',
					'_edd_log_token'      => 'public',
					'_edd_log_version'    => '',
					'_edd_log_time'       => '',
				)
			);

			if ( empty( $post_meta['_edd_log_token'] ) ) {
				$post_meta['_edd_log_token'] = 'public' === $post_meta['_edd_log_key'] ? 'public' : '';
			}

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

			$meta_to_remove    = array(
				'_edd_log_request_ip',
				'_edd_log_user',
				'_edd_log_key',
				'_edd_log_token',
				'_edd_log_version',
				'_edd_log_time',
			);
			$meta_to_migrate   = $post_meta;
			$new_log_id        = edd_add_api_request_log( $log_data );
			$add_meta_function = 'edd_add_api_request_log_meta';
		} else {
			$post_meta = get_post_custom( $data->ID );
			foreach ( $post_meta as $key => $value ) {
				$meta_to_migrate[ $key ] = maybe_unserialize( $value[0] );
			}

			$log_data = array(
				'object_id'     => $data->post_parent,
				'object_type'   => 'download',
				'user_id'       => ! empty( $meta_to_migrate['_edd_log_user'] ) ? $meta_to_migrate['_edd_log_user'] : $data->post_author,
				'type'          => $data->slug,
				'title'         => $data->post_title,
				'content'       => $data->post_content,
				'date_created'  => $data->post_date_gmt,
				'date_modified' => $data->post_modified_gmt,
			);

			$meta_to_remove = array(
				'_edit_lock',
				'_edd_log_user',
			);

			$new_log_id        = edd_add_log( $log_data );
			$add_meta_function = 'edd_add_log_meta';
		}

		if ( ! is_callable( $add_meta_function ) || empty( $meta_to_migrate ) ) {
			return;
		}

		foreach ( $meta_to_migrate as $key => $value ) {
			if ( ! in_array( $key, $meta_to_remove, true ) ) {
				// Strip off `_edd_log_` prefix.
				$key = str_replace( '_edd_log_', '', $key );

				$add_meta_function( $new_log_id, $key, $value );
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

		$date_actions_run_note = __( 'After payment actions processed.', 'easy-digital-downloads' );
		if ( $date_actions_run_note === $data->comment_content ) {
			$order = edd_get_order( $data->object_id );
			if ( $order->date_actions_run ) {
				return;
			}
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
			return false;
		}

		/** Create a new order ***************************************/
		global $wpdb;

		// Get's all the post meta for this payment.
		$meta = get_post_custom( $data->ID );

		$payment_meta = maybe_unserialize( $meta['_edd_payment_meta'][0] );
		$user_info    = isset( $payment_meta['user_info'] ) ? maybe_unserialize( $payment_meta['user_info'] ) : array();

		// It is possible that for some reason the entire unserialized array is invalid, so before trying to use it, let's just verify we got an array back.
		if ( ! is_array( $payment_meta ) ) {
			// Dump this data to a file to ensure we keep it for later use.
			edd_debug_log( '==== Failed Migrating Legacy Payment ID: ' . $data->ID . ' ====', true );
			edd_debug_log( 'Reason: Payment Meta Unserialization failed.', true );
			edd_debug_log( '- Post Data', true );
			foreach ( get_object_vars( $data ) as $key => $value ) {
				edd_debug_log( '-- ' . $key . ': ' . $value, true );
			}

			edd_debug_log( '- Post Meta', true );
			foreach ( $meta as $key => $value_array ) {
				edd_debug_log( '-- Meta Key: ' . $key, true );
				foreach ( $value_array as $value ) {
					edd_debug_log( '--- ' . $value, true );
				}
			}

			return false;
		}

		// Some old EDD data has the user info serialized, but starting with something other than a: so it can't be unserialized
		$user_info = self::fix_possible_serialization( $user_info );
		$user_info = maybe_unserialize( $user_info );

		if ( ! is_array( $user_info ) ) {
			$user_info = array();
		}

		/**
		 * Last chance to filter payment meta before we use it!
		 * Note: If modifying `cart_details`, then it's recommended that you first run
		 * `EDD\Admin\Upgrades\v3\Data_Migrator::fix_possible_serialization()`
		 * before making adjustments.
		 *
		 * @since 3.0
		 *
		 * @param array $payment_meta Payment meta.
		 * @param int   $payment_id   ID of the payment.
		 * @param array $meta         All post meta.
		 */
		$payment_meta = apply_filters( 'edd_30_migration_payment_meta', $payment_meta, $data->ID, $meta );

		$order_number     = isset( $meta['_edd_payment_number'][0] ) ? $meta['_edd_payment_number'][0] : '';
		$user_id          = isset( $meta['_edd_payment_user_id'][0] ) && ! empty( $meta['_edd_payment_user_id'][0] ) ? $meta['_edd_payment_user_id'][0] : 0;
		$ip               = isset( $meta['_edd_payment_user_ip'][0] ) ? $meta['_edd_payment_user_ip'][0] : '';
		$mode             = isset( $meta['_edd_payment_mode'][0] ) ? $meta['_edd_payment_mode'][0] : 'live';
		$gateway          = isset( $meta['_edd_payment_gateway'][0] ) && ! empty( $meta['_edd_payment_gateway'][0] ) ? $meta['_edd_payment_gateway'][0] : 'manual';
		$customer_id      = isset( $meta['_edd_payment_customer_id'][0] ) ? $meta['_edd_payment_customer_id'][0] : 0;
		$date_completed   = isset( $meta['_edd_completed_date'][0] ) ? $meta['_edd_completed_date'][0] : null;
		$purchase_key     = isset( $meta['_edd_payment_purchase_key'][0]) ? $meta['_edd_payment_purchase_key'][0] : false;
		$purchase_email   = isset( $meta['_edd_payment_user_email'][0] ) ? $meta['_edd_payment_user_email'][0] : $payment_meta['email'];
		$date_actions_run = isset( $meta['_edd_complete_actions_run'][0] ) ? $meta['_edd_complete_actions_run'][0] : null;

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
		$cart_details = isset( $payment_meta['cart_details'] ) ? maybe_unserialize( $payment_meta['cart_details'] ) : array();

		// Some old EDD data has the cart details serialized, but starting with something other than a: so it can't be unserialized
		$cart_details = self::fix_possible_serialization( $cart_details );

		// Some old cart data does not contain subtotal or discount information. Normalize it.
		$cart_details = self::normalize_cart_details( $cart_details );

		// Account for possible double serialization of the cart_details
		$cart_downloads = isset( $payment_meta['downloads'] ) ? maybe_unserialize( $payment_meta['downloads'] ) : array();

		// Some old EDD data has the downloads serialized, but starting with something other than a: so it can't be unserialized
		$cart_downloads = self::fix_possible_serialization( $cart_downloads );

		// If the order status is 'publish' convert it to the new 'complete' status.
		$order_status = 'publish' === $data->post_status ? 'complete' : $data->post_status;

		// If there are no items, and it's abandoned, just return, since this isn't a valid order.
		if ( 'abandoned' === $order_status && empty( $cart_downloads ) && empty( $cart_details ) ) {
			edd_debug_log( 'Skipping order ' . $data->ID . ' due to abandoned status and no products.', true );
			return false;
		}

		$order_subtotal = 0;
		$order_tax      = 0;
		$order_discount = 0;
		$order_total    = 0;

		// Track the total value of added fees in case the Order was initially migrated
		// without _edd_payment_total or _edd_payment_tax and manual calculation was needed.
		$order_fees_tax       = 0;
		$order_fees_total     = 0;
		$order_items_fees_tax = 0;

		// Retrieve the tax amount from metadata if available.
		$meta_tax = isset( $meta['_edd_payment_tax'] )
			? $meta['_edd_payment_tax']
			: false;

		if ( false !== $meta_tax ) {
			$meta_tax  = maybe_unserialize( $meta_tax );
			$order_tax = (float) $meta_tax[0];
		}

		$meta_total = false;
		// Retrieve the total amount from metadata if available.
		if ( isset( $meta['_edd_payment_total'] ) ) {
			$meta_total  = maybe_unserialize( $meta['_edd_payment_total'] );
			$order_total = (float) $meta_total[0];
		} elseif ( isset( $payment_meta['amount'] ) ) {
			$meta_total  = maybe_unserialize( $payment_meta['amount'] );
			$order_total = (float) $meta_total;
		}

		// In some cases (very few) there is no cart details...so we have to just avoid this part.
		if ( ! empty( $cart_details ) && is_array( $cart_details ) ) {

			// Loop through the items in the purchase to build the totals.
			foreach ( $cart_details as $cart_item ) {
				$order_subtotal += $cart_item['subtotal'];

				// Add the cart line item tax amount if a total is not available on the order.
				if ( false === $meta_tax ) {
					$order_tax += $cart_item['tax'];
				}

				$order_discount += $cart_item['discount'];

				// Add the cart line item price amount (includes tax, order item fee, _but not order item fee tax_)
				// if a total is not available on the order.
				if ( false === $meta_total ) {
					$order_total += $cart_item['price'];
				}
			}

		}

		// Account for a situation where the post_date_gmt is set to 0000-00-00 00:00:00
		$date_created_gmt = $data->post_date_gmt;
		if ( '0000-00-00 00:00:00' === $date_created_gmt ) {

			$date_created_gmt  = new \DateTime( $data->post_date );
			$modified_time     = new \DateTime( $data->post_modified );
			$modified_time_gmt = new \DateTime( $data->post_modified_gmt );

			if ( $modified_time != $modified_time_gmt ) {
				$diff = $modified_time_gmt->diff( $modified_time );

				$time_diff = 'PT';

				// Add hours to the offset string.
				if ( ! empty( $diff->h ) ) {
					$time_diff .= $diff->h . 'H';
				}

				// Add minutes to the offset string.
				if ( ! empty( $diff->i ) ) {
					$time_diff .= $diff->i . 'M';
				}

				// Account for -/+ GMT offsets.
				try {
					if ( 1 === $diff->invert ) {
						$date_created_gmt->add( new \DateInterval( $time_diff ) );
					} else {
						$date_created_gmt->sub( new \DateInterval( $time_diff ) );
					}
				} catch ( \Exception $e ) {

				}
			}

			$date_created_gmt = $date_created_gmt->format('Y-m-d H:i:s');
		}

		// Maybe convert the date completed to UTC or backfill the date_completed.
		$non_completed_statuses = apply_filters( 'edd_30_noncomplete_statuses', edd_get_incomplete_order_statuses() );
		if ( ! in_array( $order_status, $non_completed_statuses, true ) ) {

			if ( ! empty( $date_completed ) ) {  // Update the data_completed to the UTC.
				try {
					$date_completed = EDD()->utils->date( $date_completed, edd_get_timezone_id() )->setTimezone( 'UTC' )->toDateTimeString();
				} catch ( \Exception $e ) {
					$date_completed = $date_created_gmt;
				}
			} elseif ( is_null( $date_completed ) ) { // Backfill a missing date_completed (for things like recurring payments).
				$date_completed = $date_created_gmt;
			}

		}

		if ( 'manual_purchases' === $gateway && isset( $meta['_edd_payment_total'][0] ) ) {
			$gateway     = 'manual';
			$order_total = $meta['_edd_payment_total'][0];
		}

		/*
		 * Build up the order address data. Actual insertion happens later, but we need this now to figure out the tax rate.
		 */

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
			'name'         => trim( $user_info['first_name'] . ' ' . $user_info['last_name'] ),
			'address'      => isset( $user_info['address']['line1'] )   ? $user_info['address']['line1']   : '',
			'address2'     => isset( $user_info['address']['line2'] )   ? $user_info['address']['line2']   : '',
			'city'         => isset( $user_info['address']['city'] )    ? $user_info['address']['city']    : '',
			'region'       => isset( $user_info['address']['state'] )   ? $user_info['address']['state']   : '',
			'country'      => isset( $user_info['address']['country'] ) && array_key_exists( strtoupper( $user_info['address']['country'] ), edd_get_country_list() )
				? $user_info['address']['country']
				: '',
			'postal_code'  => isset( $user_info['address']['zip'] )     ? $user_info['address']['zip']     : '',
			'date_created' => $date_created_gmt,
		);

		$tax_rate_id = null;
		$tax_rate = isset( $meta['_edd_payment_tax_rate'][0] )
			? (float) $meta['_edd_payment_tax_rate'][0]
			: 0.00;

		/*
		 * Previously tax rates were stored as a decimal (e.g. `0.2`) but they're now stored as a percentage
		 * (e.g. `20`). So we need to convert.
		 */
		if ( $tax_rate < 1 ) {
			$tax_rate = $tax_rate * 100;
		}

		$set_tax_rate_meta = false;

		if ( ! empty( $tax_rate ) ) {
			// Fetch the actual tax rate object for the order region & country.
			$tax_rate_object = edd_get_tax_rate_by_location( array(
				'country' => $order_address_data['country'],
				'region'  => $order_address_data['region'],
			) );

			if ( ! empty( $tax_rate_object->id ) && $tax_rate_object->amount == $tax_rate ) {
				$tax_rate_id = $tax_rate_object->id;
			}
		}

		/*
		 * If we cannot find a matching Adjustment object, we should save this in order meta so it isn't lost.
		 */
		if ( ! empty( $tax_rate ) && empty( $tax_rate_id ) ) {
			$set_tax_rate_meta = true;
		}

		// Build the order data before inserting.
		$order_data = array(
			'id'             => $data->ID,
			'parent'         => $data->post_parent,
			'order_number'   => $order_number,
			'status'         => $order_status,
			'type'           => 'sale',
			'date_created'   => $date_created_gmt, // GMT is stored in the database as the offset is applied by the new query classes.
			'date_modified'  => $data->post_modified_gmt, // GMT is stored in the database as the offset is applied by the new query classes.
			'date_completed' => $date_completed,
			'user_id'        => $user_id,
			'customer_id'    => $customer_id,
			'email'          => $purchase_email,
			'ip'             => $ip,
			'gateway'        => $gateway,
			'mode'           => $mode,
			'currency'       => ! empty( $payment_meta['currency'] ) ? $payment_meta['currency'] : edd_get_currency(),
			'payment_key'    => $purchase_key,
			'tax_rate_id'    => $tax_rate_id,
			'subtotal'       => $order_subtotal,
			'tax'            => $order_tax,
			'discount'       => $order_discount,
			'total'          => $order_total,
		);

		// Orders placed prior to 2.8 won't have this meta, so only include it if the value isn't empty.
		if ( ! empty( $date_actions_run ) ) {
			$actions_date = new \EDD\Utils\Date();

			// We store the dates in the DB as UTC.
			$actions_date->setTimestamp( $date_actions_run )->setTimezone( new \DateTimeZone( 'UTC' ) );

			// Format the date in MySQL DATETIME format.
			$order_data['date_actions_run'] = $actions_date->format( 'mysql' );
		}

		/**
		 * Filters the data used to create the order.
		 *
		 * @since 3.0
		 *
		 * @param array $order_data   Order creation arguments.
		 * @param array $payment_meta Payment meta.
		 * @param array $cart_details Cart details.
		 * @param array $meta         All payment meta.
		 */
		$order_data = apply_filters( 'edd_30_migration_order_creation_data', $order_data, $payment_meta, $cart_details, $meta );

		update_option( '_edd_v30_doing_order_migration', true, false );

		// Remove all order status transition actions.
		remove_all_actions( 'edd_transition_order_status' );
		remove_all_actions( 'edd_transition_order_item_status' );

		$order_id = edd_add_order( $order_data );

		// Save an un-matched tax rate in order meta.
		if ( $set_tax_rate_meta ) {
			edd_add_order_meta( $order_id, 'tax_rate', $tax_rate );
		}

		// Do not pass the original order ID into other arrays
		unset( $order_data['id'] );

		// Reset the $refund_id variable so that we don't end up accidentally creating refunds.
		$refund_id = 0;

		// If the order status is 'refunded', we need to generate a new order with the type of 'refund'.
		if ( 'refunded' === $order_status ) {

			// Since the refund is a near copy of the original order, copy over the arguments.
			$refund_data = $order_data;

			$refund_data['parent']       = $order_id;
			$refund_data['order_number'] = $order_id . apply_filters( 'edd_order_refund_suffix', '-R-' ) . '1';
			$refund_data['type']         = 'refund';
			$refund_data['status']       = 'complete';

			// Negate the amounts
			$refund_data['subtotal'] = edd_negate_amount( $order_subtotal );
			$refund_data['tax']      = edd_negate_amount( $order_tax );
			$refund_data['discount'] = edd_negate_amount( $order_discount );
			$refund_data['total']    = edd_negate_amount( $order_total );


			// These are the best guess at the date it was refunded since we didn't store that prior.
			$refund_data['date_created']  = $data->post_modified_gmt;
			$refund_data['date_modified'] = $data->post_modified_gmt;

			$refund_id = edd_add_order( $refund_data );

		}

		// Remove empty data.
		$order_address_data = array_filter( $order_address_data );
		if ( ! empty( $order_address_data ) ) {
			// Add to edd_order_addresses table.
			$order_address_data['order_id'] = $order_id;
			edd_add_order_address( $order_address_data );
		}

		// Maybe add the address to the edd_customer_addresses.
		$customer_address_data = $order_address_data;

		// We don't need to pass this data to edd_maybe_add_customer_address().
		unset( $customer_address_data['order_id'] );
		unset( $customer_address_data['first_name'] );
		unset( $customer_address_data['last_name'] );

		// If possible, set the order date as the address creation date.
		$customer_address_data['date_created'] = $date_created_gmt;

		// Maybe add address to customer record.
		edd_maybe_add_customer_address( $customer_id, $customer_address_data );

		// Maybe add email address to customer record
		if ( ! empty( $customer ) && $customer instanceof \EDD_Customer ) {
			$type = ( $customer->email === $purchase_email ) ? 'primary' : 'secondary';
			edd_add_customer_email_address(
				array(
					'customer_id'  => $customer_id,
					'date_created' => $date_created_gmt,
					'email'        => $purchase_email,
					'type'         => $type,
				)
			);
		}

		/** Migrate meta *********************************************/

		// Unlimited downloads meta is not an order property, so we set it on the order meta for the new order ID.
		if ( isset( $meta['_edd_payment_unlimited_downloads'] ) && ! empty( $meta['_edd_payment_unlimited_downloads'][0] ) ) {
			edd_add_order_meta( $order_id, 'unlimited_downloads', $meta['_edd_payment_unlimited_downloads'][0] );
		}

		// Transaction IDs are no longer meta, and have their own table and data set, so we need to add the transactions.
		$transaction_id = ! empty( $meta['_edd_payment_transaction_id'][0] ) ? $meta['_edd_payment_transaction_id'][0] : false;
		// If we have no transaction ID & the gateway was PayPal, let's check in old payment notes.
		if ( empty( $transaction_id ) && false !== strpos( $gateway, 'paypal' ) ) {
			$transaction_id = self::find_transaction_id_from_notes( $order_id );
		}
		if ( ! empty( $transaction_id ) ) {
			edd_add_order_transaction( array(
				'object_id'      => $order_id,
				'object_type'    => 'order',
				'transaction_id' => $transaction_id,
				'gateway'        => $gateway,
				'status'         => 'complete',
				'total'          => $order_total,
				'date_created'   => $date_completed,
				'date_modified'  => $date_completed,
			) );
		}

		/**
		 * By default, this is what is stored in payment meta. These array keys are part of the core payment meta in 2.x
		 * but are not needed as part of the order meta and will not be migrated.
		 * Extensions can add their keys to this filter if they use the payment meta array to store data and have
		 * established a migration process to keep the data intact with the new order tables.
		 *
		 * @since 3.0
		 * @param array The array of payment meta keys.
		 */
		$core_meta_keys = apply_filters( 'edd_30_payment_meta_keys_not_migrated', array(
			'fees',
			'key',
			'email',
			'date',
			'downloads',
			'cart_details',
			'currency',
			'discount',
			'subtotal',
			'tax',
			'amount',
			'user_id',
		) );

		// Remove core keys from `user_info`.
		$remaining_user_info = false;
		if ( ! empty( $user_info ) ) {
			/**
			 * Array keys which are part of the core `user_info` in payment meta which are not needed as part of the order meta.
			 * Extensions can add their keys to this filter if they use the `user_info` array to store data and have
			 * established a migration process to keep the data intact with the new order tables.
			 *
			 * @since 3.0
			 * @param array The array of user info keys.
			 */
			$core_user_info      = apply_filters( 'edd_30_core_user_info', array( 'id', 'email', 'first_name', 'last_name', 'discount', 'address', 'user_id' ) );
			$remaining_user_info = array_diff_key( $user_info, array_flip( $core_user_info ) );
		}

		// If an extension has added data to `user_info`, migrate it.
		if ( $remaining_user_info ) {
			$payment_meta['user_info'] = $remaining_user_info;
		} else {
			$core_meta_keys[] = 'user_info';
		}

		// Remove all the core payment meta from the array, and...
		if ( is_array( $payment_meta ) ) {
			$remaining_payment_meta = array_diff_key( $payment_meta, array_flip( $core_meta_keys ) );

			// ..If we have extra payment meta, it needs to be migrated across.
			if ( 0 < count( $remaining_payment_meta ) ) {
				edd_add_order_meta( $order_id, 'payment_meta', $remaining_payment_meta );
			}
		}

		/** Create order items ***************************************/

		// Now we iterate through all the cart items and make rows in the order items table.
		if ( ! empty( $cart_details ) ) {
			foreach ( $cart_details as $key => $cart_item ) {
				// Reset any conditional IDs to be safe.
				$refund_order_item_id = 0;

				// Get product name.
				$product_name = isset( $cart_item['name'] )
					? $cart_item['name']
					: '';

				// Get price ID.
				$price_id = self::get_valid_price_id_for_cart_item( $cart_item );

				if ( ! empty( $product_name ) ) {
					$option_name = edd_get_price_option_name( $cart_item['id'], $price_id );
					if ( ! empty( $option_name ) ) {
						$product_name .= ' — ' . $option_name;
					}
				}

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
					'subtotal'      => (float) $cart_item['subtotal'],
					'discount'      => (float) $cart_item['discount'],
					'tax'           => $cart_item['tax'],
					'total'         => (float) $cart_item['price'],
					'date_created'  => $date_created_gmt,
					'date_modified' => $data->post_modified_gmt,
				);

				/**
				 * Filters the arguments used to create the order item.
				 *
				 * @since 1.0
				 *
				 * @param array $order_item_args Order item arguments.
				 * @param array $cart_item       Original cart item.
				 * @param array $payment_meta    Payment meta.
				 * @param array $meta            All meta.
				 */
				$order_item_args = apply_filters( 'edd_30_migration_order_item_creation_data', $order_item_args, $cart_item, $payment_meta, $meta );

				$order_item_id = edd_add_order_item( $order_item_args );

				if ( ! empty( $cart_item['item_number']['options'] ) ) {
					// Collect any item_number options and store them.

					// Remove our price_id and quantity, as they are columns on the order item now.
					unset( $cart_item['item_number']['options']['price_id'] );
					unset( $cart_item['item_number']['options']['quantity'] );

					foreach ( $cart_item['item_number']['options'] as $option_key => $value ) {
						$option_key = '_option_' . sanitize_key( $option_key );

						edd_add_order_item_meta( $order_item_id, $option_key, $value );
					}
				}

				// If the order status is refunded, we also need to add all the refunded order items on the refund order as well.
				if ( ! empty( $refund_id ) ) {

					// Since the refund is a near copy of the original order, copy over the arguments.
					$refund_item_args = $order_item_args;

					$refund_item_args['parent']   = $order_item_id;
					$refund_item_args['order_id'] = $refund_id;
					$refund_item_args['status']   = 'complete';

					// Subtotal is actually set to subtotal - discount.
					$refund_item_args['subtotal'] = $refund_item_args['subtotal'] - $refund_item_args['discount'];

					// Negate the amounts
					$refund_item_args['quantity'] = edd_negate_int( $cart_item['quantity'] );
					foreach( array( 'amount', 'subtotal', 'tax', 'total' ) as $field_to_negate ) {
						$refund_item_args[ $field_to_negate ] = edd_negate_amount( $refund_item_args[ $field_to_negate ] );
					}

					// These are our best estimates since we did not store the refund date previously.
					$refund_item_args['date_crated']   = $data->post_modified_gmt;
					$refund_item_args['date_modified'] = $data->post_modified_gmt;

					$refund_order_item_id = edd_add_order_item( $refund_item_args );

					if ( ! empty( $cart_item['item_number']['options'] ) ) {
						// Collect any item_number options and store them.

						// Remove our price_id and quantity, as they are columns on the order item now.
						unset( $cart_item['item_number']['options']['price_id'] );
						unset( $cart_item['item_number']['options']['quantity'] );

						foreach ( $cart_item['item_number']['options'] as $option_key => $value ) {
							$option_key = '_option_' . sanitize_key( $option_key );

							edd_add_order_item_meta( $refund_order_item_id, $option_key, $value );
						}
					}

				}

				// Store order item fees as adjustments.
				if ( isset( $cart_item['fees'] ) && ! empty( $cart_item['fees'] ) ) {
					foreach ( $cart_item['fees'] as $fee_id => $fee ) {
						// Reset any conditional IDs to be safe.
						$refund_adjustment_id = 0;

						$tax   = EDD()->fees->get_calculated_tax( $fee, $tax_rate );
						$total = floatval( $fee['amount'] ) + $tax;

						// Track order item fees tax to adjust order if needed.
						$order_items_fees_tax += $tax;

						// Add the adjustment.
						$adjustment_args = array(
							'object_id'   => $order_item_id,
							'object_type' => 'order_item',
							'type_key'    => $fee_id,
							'type'        => 'fee',
							'description' => $fee['label'],
							'subtotal'    => floatval( $fee['amount'] ),
							'tax'         => $tax,
							'total'       => floatval( $fee['amount'] ) + $tax,
						);

						/**
						 * Filters the arguments used to create an order item adjustment.
						 *
						 * @since 3.0
						 *
						 * @param array $adjustment_args Adjustment arguments for a fee.
						 * @param array $fee             Original fee data.
						 * @param array $cart_item       Cart item this fee is part of.
						 * @param array $payment_meta    Payment meta.
						 * @param array $meta            All meta.
						 */
						$adjustment_args = apply_filters( 'edd_30_migration_order_item_adjustment_creation_data', $adjustment_args, $fee, $cart_item, $payment_meta, $meta );

						$adjustment_id = edd_add_order_adjustment( $adjustment_args );
						edd_add_extra_fee_order_adjustment_meta( $adjustment_id, $fee );

						// If we refunded the main order, the fees also need to be added to the refund order type we created.
						if ( ! empty( $refund_id ) ) {
							$refund_adjustment_args              = $adjustment_args;
							$refund_adjustment_args['parent']    = $adjustment_id;
							$refund_adjustment_args['object_id'] = $refund_order_item_id;
							$refund_adjustment_args['subtotal']  = edd_negate_amount( floatval( $fee['amount'] ) );
							$refund_adjustment_args['tax']       = edd_negate_amount( $tax );
							$refund_adjustment_args['total']     = edd_negate_amount( floatval( $fee['amount'] ) + $tax );

							$refund_adjustment_id = edd_add_order_adjustment( $refund_adjustment_args );
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
					'price_id'      => null,
					'cart_index'    => $cart_index,
					'type'          => 'download',
					'quantity'      => 1,
					'amount'        => (float) $payment_meta['amount'],
					'subtotal'      => (float) $payment_meta['amount'],
					'discount'      => 0.00,
					'tax'           => 0.00,
					'total'         => (float) $payment_meta['amount'],
					'date_created'  => $date_created_gmt,
					'date_modified' => $data->post_modified_gmt,
				);

				$order_item_id = edd_add_order_item( $order_item_args );

				// If the order was refunded, we also need to add these items to the refund order.
				if ( ! empty( $refund_id ) ) {

					// Since the refund is a near copy of the original order, copy over the arguments.
					$refund_item_args = $order_item_args;

					$refund_item_args['parent']   = $order_item_id;
					$refund_item_args['order_id'] = $refund_id;
					$refund_item_args['quantity'] = edd_negate_int( 1 );
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

		if ( isset( $payment_meta['fees'] ) && ! empty( $payment_meta['fees'] ) ) {
			foreach ( $payment_meta['fees'] as $fee_id => $fee ) {
				// Reset any conditional IDs to be safe.
				$refund_adjustment_id = 0;

				if ( ! empty( $fee['download_id'] ) ) {
					continue;
				}

				$tax   = EDD()->fees->get_calculated_tax( $fee, $tax_rate );
				$total = floatval( $fee['amount'] ) + $tax;

				$order_fees_tax   += $tax;
				$order_fees_total += $total;

				// Add the adjustment.
				$adjustment_args = array(
					'object_id'     => $order_id,
					'object_type'   => 'order',
					'type_key'      => $fee_id,
					'type'          => 'fee',
					'description'   => $fee['label'],
					'subtotal'      => floatval( $fee['amount'] ),
					'tax'           => $tax,
					'total'         => $total,
					'date_created'  => $date_created_gmt,
					'date_modified' => $data->post_modified_gmt,
				);

				/**
				 * Filters the order adjustment arguments.
				 *
				 * @since 3.0
				 *
				 * @param array $adjustment_args Arguments used to create the order adjustment.
				 * @param array $fee             Fee data.
				 * @param array $payment_meta    Payment meta.
				 * @param array $meta            All meta.
				 */
				$adjustment_args = apply_filters( 'edd_30_migration_order_adjustment_creation_data', $adjustment_args, $fee, $payment_meta, $meta );

				$adjustment_id = edd_add_order_adjustment( $adjustment_args );
				edd_add_extra_fee_order_adjustment_meta( $adjustment_id, $fee );

				if ( ! empty( $refund_id ) ) {

					// Since the refund is a near copy of the original order, copy over the arguments.
					$refund_adjustment_args = $adjustment_args;

					$refund_adjustment_args['parent']    = $adjustment_id;
					$refund_adjustment_args['object_id'] = $refund_id;

					// Negate the amounts.
					$refund_adjustment_args['subtotal'] = edd_negate_amount( floatval( $fee['amount'] ) );
					$refund_adjustment_args['tax']      = edd_negate_amount( $tax );
					$refund_adjustment_args['total']    = edd_negate_amount( floatval( $fee['amount'] ) + $tax );

					$refund_adjustment_id = edd_add_order_adjustment( $refund_adjustment_args );
				}
			}
		}

		// Add fee taxes (order and order item) if the order tax amount was previously manually calculated.
		if ( false === $meta_tax ) {
			edd_update_order( $order_id, array(
				'tax' => $order_tax + $order_fees_tax + $order_items_fees_tax,
			) );
		}

		// Add fee totals (order and order item) if the order tax amount was previously manually calculated.
		// Order item fees were previously included in the total calculation. We must manually include
		// order item fee tax amounts, and order fees total (subtotal + tax).
		if ( false === $meta_total ) {
			edd_update_order( $order_id, array(
				'total' => $order_total + $order_fees_total + $order_items_fees_tax,
			) );
		}

		// Insert discounts.
		$discounts = ! empty( $user_info['discount'] )
			? $user_info['discount']
			: array();

		if ( ! is_array( $discounts ) ) {
			$discounts = explode( ',', $discounts );
		}

		$first_discount        = reset( $discounts );
		$discount_arg_defaults = array(
			'object_id'     => $order_id,
			'object_type'   => 'order',
			'type'          => 'discount',
			'subtotal'      => $order_discount,
			'total'         => $order_discount,
			'date_created'  => $date_created_gmt,
			'date_modified' => $data->post_modified_gmt,
		);
		if ( ! empty( $discounts ) && ( 'none' !== $first_discount ) ) {
			if ( 1 === count( $discounts ) ) {
				$discount_code = reset( $discounts );

				/** @var \EDD_Discount $discount_object */
				$discount_object = edd_get_discount_by( 'code', $discount_code );

				if ( $discount_object instanceof \EDD_Discount ) {
					$discount_args = wp_parse_args(
						array(
							'type_id'     => $discount_object->id,
							'description' => $discount_object->code,
						),
						$discount_arg_defaults
					);
				} else {
					$discount_args = wp_parse_args(
						array(
							'description' => $discount_code,
						),
						$discount_arg_defaults
					);
				}

				/**
				 * Filters the arguments used to create a discount adjustment.
				 *
				 * @since 3.0
				 *
				 * @param array         $discount_args   Order adjustment arguments.
				 * @param \EDD_Discount $discount_object Discount object.
				 * @param float         $order_subtotal  Order subtotal.
				 * @param array         $user_info       User info array.
				 * @param array         $payment_meta    Payment meta.
				 * @param array         $meta            All post meta.
				 */
				$discount_args = apply_filters( 'edd_30_migration_order_discount_creation_data', $discount_args, $discount_object, $order_subtotal, $user_info, $payment_meta, $meta );

				$new_discount_id = edd_add_order_adjustment( $discount_args );
				if ( $order_discount <= 0 ) {
					edd_add_order_adjustment_meta(
						$new_discount_id,
						'migrated_order_discount_unknown',
						(int) $order_id,
						true
					);
				}
			} else {
				foreach ( $discounts as $discount_code ) {

					/** @var \EDD_Discount $discount_object */
					$discount_object = edd_get_discount_by( 'code', $discount_code );

					if ( $discount_object instanceof \EDD_Discount ) {
						$calculated_discount = $order_subtotal - $discount_object->get_discounted_amount( $order_subtotal );
						$discount_args       = wp_parse_args(
							array(
								'type_id'     => $discount_object->id,
								'description' => $discount_object->code,
								'subtotal'    => $calculated_discount,
								'total'       => $calculated_discount,
							),
							$discount_arg_defaults
						);
					} else {
						$discount_args = wp_parse_args(
							array(
								'description' => $discount_code,
								'subtotal'    => 0.00,
								'total'       => 0.00,
							),
							$discount_arg_defaults
						);
					}

					/**
					 * Filters the arguments used to create a discount adjustment.
					 *
					 * @since 3.0
					 *
					 * @param array         $discount_args   Order adjustment arguments.
					 * @param \EDD_Discount $discount_object Discount object.
					 * @param float         $order_subtotal  Order subtotal.
					 * @param array         $user_info       User info array.
					 * @param array         $payment_meta    Payment meta.
					 * @param array         $meta            All post meta.
					 */
					$discount_args = apply_filters( 'edd_30_migration_order_discount_creation_data', $discount_args, $discount_object, $order_subtotal, $user_info, $payment_meta, $meta );

					$new_discount_id = edd_add_order_adjustment( $discount_args );
					if ( $calculated_discount <= 0 ) {
						edd_add_order_adjustment_meta(
							$new_discount_id,
							'migrated_order_discount_unknown',
							(int) $order_id,
							true
						);
					}
				}
			}
		} elseif ( ! empty( $discounts ) && 'none' === $first_discount && $order_discount > 0 ) {
			// The order was saved with a discount amount, but no discount code.
			$discount_args = wp_parse_args(
				array(
					'description' => __( 'Legacy Discount', 'easy-digital-downloads' ),
				),
				$discount_arg_defaults
			);

			// There is no filter applied here because there is no discount object.
			edd_add_order_adjustment( $discount_args );
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
			'_edd_payment_transaction_id',
			'_edd_complete_actions_run',
		);

		// Determine what main payment meta keys were from core and what were custom...
		$remaining_meta = array_diff_key( $meta, array_flip( $core_meta_keys ) );

		// ...and whatever is not from core, needs to be added as new order meta.
		foreach ( $remaining_meta as $meta_key => $meta_value ) {
			$meta_value = maybe_unserialize( $meta_value[0] );

			edd_add_order_meta( $order_id, $meta_key, $meta_value );
		}

		/**
		 * Now that we're done, let's run a hook here so we can allow extensions to make any necessary changes.
		 *
		 * @since 3.0
		 * @param int   $order_id     The order ID.
		 * @param array $payment_meta The `_edd_payment_meta` value for the original payment.
		 * @param array $meta         All post meta associated with the payment.
		 */
		do_action( 'edd_30_migrate_order', $order_id, $payment_meta, $meta );

		delete_option( '_edd_v30_doing_order_migration' );

		return $order_id;
	}

	/**
	 * Retrieves a valid price ID for a given cart item.
	 * If the product does not have variable prices, then `null` is always returned.
	 * If the supplied price ID does not match a price ID that actually exists, then the default
	 * variable price is returned instead of the supplied one.
	 *
	 * @since 3.0
	 *
	 * @param array $cart_item Array of cart item details.
	 *
	 * @return int|null
	 */
	protected static function get_valid_price_id_for_cart_item( $cart_item ) {
		// If the product doesn't have variable prices, just return `null`.
		if ( ! edd_has_variable_prices( $cart_item['id'] ) ) {
			return null;
		}

		$variable_prices = edd_get_variable_prices( $cart_item['id'] );
		if ( ! is_array( $variable_prices ) || empty( $variable_prices ) ) {
			return null;
		}

		// Return the price ID that's set to the cart item right now, if not numeric return NULL.
		return isset( $cart_item['item_number']['options']['price_id'] ) && is_numeric( $cart_item['item_number']['options']['price_id'] )
			? absint( $cart_item['item_number']['options']['price_id'] )
			: null;
	}

	/**
	 * Attempts to locate a PayPal transaction ID from legacy payment notes.
	 *
	 * @since 3.0
	 *
	 * @param int $payment_id
	 *
	 * @return string|false Transaction ID on success, false if not found.
	 */
	private static function find_transaction_id_from_notes( $payment_id ) {
		global $wpdb;

		$payment_notes = $wpdb->get_col( $wpdb->prepare(
			"SELECT comment_content FROM {$wpdb->comments} WHERE comment_post_ID = %d",
			$payment_id
		) );

		if ( empty( $payment_notes ) || ! is_array( $payment_notes ) ) {
			return false;
		}

		foreach ( $payment_notes as $note ) {
			if ( preg_match( '/^PayPal Transaction ID: ([^\s]+)/', $note, $match ) ) {
				return $match[1];
			}
		}

		return false;
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

		$scope = ! empty( $data['global'] )
			? 'country'
			: 'region';

		// If the scope is 'country', look for other active rates that are country wide and set them as 'inactive'.
		if ( 'country' === $scope ) {
			$tax_rates = edd_get_adjustments(
				array(
					'type'   => 'tax_rate',
					'status' => 'active',
					'scope'  => 'country',
					'name'   => $data['country'],
				)
			);

			if ( ! empty( $tax_rates ) ) {
				foreach ( $tax_rates as $tax_rate ) {
					edd_update_adjustment(
						$tax_rate->id,
						array( 'status' => 'inactive', )
					);
				}
			}
		}

		$adjustment_data = array(
			'name'   => $data['country'],
			'scope'  => $scope,
			'amount' => floatval( $data['rate'] ),
		);

		if ( ! empty( $data['state'] ) ) {
			$adjustment_data['description'] = sanitize_text_field( $data['state'] );
		}

		edd_add_tax_rate( $adjustment_data );
	}

	/**
	 * Normalizes and backfills legacy payment cart data.
	 *
	 * @since 3.0.0
	 *
	 * @param array|string $cart_details Cart details. No action is performed if a string
	 *                                   (array cannot be unserialized) is provided.
	 * @return array|string
	 */
	private static function normalize_cart_details( $cart_details ) {
		if ( ! is_array( $cart_details ) ) {
			return $cart_details;
		}

		foreach ( $cart_details as &$cart_item ) {

			// Get price.
			$cart_item['price'] = isset( $cart_item['price'] )
				? (float) $cart_item['price']
				: 0.00;

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
		}

		return $cart_details;
	}

	/**
	 * Given that some data quite possible has bad serialization, we need to possibly fix the bad serialization.
	 *
	 * @since 3.0.0
	 *
	 * @param $data
	 *
	 * @return mixed
	 */
	public static function fix_possible_serialization( $data ) {
		if ( ! is_array( $data ) && is_string( $data ) ) {
			$data = substr_replace( $data, 'a', 0, 1 );
		}

		return $data;
	}
}
