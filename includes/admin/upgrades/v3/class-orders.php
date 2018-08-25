<?php
/**
 * 3.0 Data Migration - Orders.
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
 * Orders Class.
 *
 * @since 3.0
 */
class Orders extends Base {

	/**
	 * Constructor.
	 *
	 * @param int $step Step.
	 */
	public function __construct( $step = 1 ) {
		parent::__construct( $step );

		$this->completed_message = __( 'Orders migration completed successfully.', 'easy-digital-downloads' );
		$this->upgrade           = 'migrate_orders';
	}

	/**
	 * Retrieve the data pertaining to the current step and migrate as necessary.
	 *
	 * @since 3.0
	 *
	 * @return bool True if data was migrated, false otherwise.
	 */
	public function get_data() {
		$offset = ( $this->step - 1 ) * $this->per_step;

		$results = $this->get_db()->get_results( $this->get_db()->prepare(
			"SELECT *
			 FROM {$this->get_db()->posts}
			 WHERE post_type = %s
			 ORDER BY ID ASC
			 LIMIT %d, %d",
			esc_sql( 'edd_payment' ), $offset, $this->per_step
		) );

		if ( ! empty( $results ) ) {
			foreach ( $results as $result ) {

				// Check if order has already been migrated.
				if ( $result->ID !== $this->remap_id( $result->ID, static::ORDERS ) ) {
					continue;
				}

				/** Create a new order ***************************************/

				$meta = get_post_custom( $result->ID );

				$payment_meta = maybe_unserialize( $meta['_edd_payment_meta'][0] );
				$user_info    = $payment_meta['user_info'];

				$order_number   = isset( $meta['_edd_payment_number'][0] ) ? $meta['_edd_payment_number'][0] : '';
				$user_id        = isset( $meta['_edd_payment_user_id'][0] ) && ! empty( $meta['_edd_payment_user_id'][0] ) ? $meta['_edd_payment_user_id'][0] : 0;
				$ip             = isset( $meta['_edd_payment_user_ip'][0] ) ? $meta['_edd_payment_user_ip'][0] : '';
				$mode           = isset( $meta['_edd_payment_mode'][0] ) ? $meta['_edd_payment_mode'][0] : 'live';
				$gateway        = isset( $meta['_edd_payment_gateway'][0] ) && ! empty( $meta['_edd_payment_gateway'][0] ) ? $meta['_edd_payment_gateway'][0] : 'manual';
				$customer_id    = isset( $meta['_edd_payment_customer_id'][0] ) ? $meta['_edd_payment_customer_id'][0] : 0;
				$date_completed = isset( $meta['_edd_completed_date'][0] ) ? $meta['_edd_completed_date'][0] : '0000-00-00 00:00:00';

				// Maybe convert the date completed to UTC.
				if ( '0000-00-00 00:00:00' !== $date_completed ) {
					$date_completed = EDD()->utils->date( $date_completed, edd_get_timezone_id() )->setTimezone( 'UTC' )->toDateTimeString();
				}

				// Do not use -1 as the user ID.
				$user_id = ( -1 === $user_id )
					? 0
					: $user_id;

				// Calculate totals.
				$subtotal = (float) array_reduce( wp_list_pluck( $payment_meta['cart_details'], 'subtotal' ), function( $carry, $item ) {
					return $carry += $item;
				} );

				$tax = (float) array_reduce( wp_list_pluck( $payment_meta['cart_details'], 'tax' ), function( $carry, $item ) {
					return $carry += $item;
				} );

				$discount = (float) array_reduce( wp_list_pluck( $payment_meta['cart_details'], 'discount' ), function( $carry, $item ) {
					return $carry += $item;
				} );

				$total = (float) array_reduce( wp_list_pluck( $payment_meta['cart_details'], 'price' ), function( $carry, $item ) {
					return $carry += $item;
				} );

				$type = 'refunded' === $result->post_status
					? 'refund'
					: 'sale';

				$order_data = array(
					'parent'         => $result->post_parent,
					'order_number'   => $order_number,
					'status'         => $result->post_status,
					'type'           => $type,
					'date_created'   => $result->post_date_gmt, // GMT is stored in the database as the offset is applied by the new query classes.
					'date_modified'  => $result->post_modified_gmt, // GMT is stored in the database as the offset is applied by the new query classes.
					'date_completed' => $date_completed,
					'user_id'        => $user_id,
					'customer_id'    => $customer_id,
					'email'          => $payment_meta['email'],
					'ip'             => $ip,
					'gateway'        => $gateway,
					'mode'           => $mode,
					'currency'       => $payment_meta['currency'],
					'payment_key'    => $payment_meta['key'],
					'subtotal'       => $subtotal,
					'tax'            => $tax,
					'discount'       => $discount,
					'total'          => $total,
				);

				$order_id = edd_add_order( $order_data );

				// First & last name.
				$user_info['first_name'] = isset( $user_info['first_name'] )
					? $user_info['first_name']
					: '';
				$user_info['last_name']  = isset( $user_info['last_name'] )
					? $user_info['last_name']
					: '';

				// Add order address.
				$user_info['address'] = isset( $user_info['address'] )
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
					'first_name'  => $user_info['first_name'],
					'last_name'   => $user_info['last_name'],
					'address'     => $user_info['address']['line1'],
					'address2'    => $user_info['address']['line2'],
					'city'        => $user_info['address']['city'],
					'region'      => $user_info['address']['state'],
					'country'     => $user_info['address']['country'],
					'postal_code' => $user_info['address']['zip'],
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
				$customer = edd_get_customer( $customer_id );
				if ( $customer ) {
					$customer->add_email( $payment_meta['email'] );
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

				// The cart_items array key did not exist in earlier versions of EDD.
				$cart_items = isset( $payment_meta['cart_details'] )
					? $payment_meta['cart_details']
					: array();

				if ( ! empty( $cart_items ) ) {
					foreach ( $cart_items as $key => $cart_item ) {
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
							'quantity'      => $cart_item['quantity'],
							'amount'        => (float) $cart_item['item_price'],
							'subtotal'      => $cart_item['subtotal'],
							'discount'      => $cart_item['discount'],
							'tax'           => $cart_item['tax'],
							'total'         => (float) $cart_item['price'],
							'date_created'  => $result->post_date_gmt,
							'date_modified' => $result->post_modified_gmt,
						);

						$order_item_id = edd_add_order_item( $order_item_args );

						// Store order item fees as adjustments.
						if ( isset( $cart_item['fees'] ) && ! empty( $cart_item['fees'] ) ) {
							foreach ( $cart_item['fees'] as $fee_id => $fee ) {

								$tax_rate = isset( $meta['_edd_payment_tax_rate'][0] )
									? (float) $meta['_edd_payment_tax_rate'][0]
									: 0.00;

								$tax = ( isset( $fee['no_tax'] ) && false === $fee['no_tax'] && ! empty( $tax_rate ) ) || ( $fee['amount'] < 0 && ! empty( $tax_rate ) )
									? floatval( floatval( $fee['amount'] ) - ( floatval( $fee['amount'] ) / ( 1 + $tax_rate ) ) )
									: 0.00;

								// Add the adjustment.
								$adjustment_id = edd_add_order_adjustment( array(
									'object_id'   => $order_item_id,
									'object_type' => 'order_item',
									'type'        => 'fee',
									'description' => $fee['label'],
									'subtotal'    => floatval( $fee['amount'] ),
									'tax'         => $tax,
									'total'       => floatval( $fee['amount'] ) + $tax,
								) );

								// Fee ID.
								edd_add_order_adjustment_meta( $adjustment_id, 'fee_id', $fee_id );

								// Maybe store download ID.
								if ( isset( $fee['download_id'] ) && ! empty( $fee['download_id'] ) ) {
									edd_add_order_adjustment_meta( $adjustment_id, 'download_id', $fee['download_id'] );
								}

								// Maybe store price ID.
								if ( isset( $fee['price_id'] ) && ! is_null( $fee['price_id'] ) ) {
									edd_add_order_adjustment_meta( $adjustment_id, 'price_id', $fee['price_id'] );
								}
							}
						}
					}

				// Compatibility with older versions of EDD.
				// Older versions stored a single dimensional array of download IDs.
				} elseif ( isset( $payment_meta['downloads'] ) && count( $payment_meta['downloads'] ) === count( $payment_meta['downloads'], COUNT_RECURSIVE ) ) {
					foreach ( $payment_meta['downloads'] as $cart_index => $download_id ) {
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
							'date_created'  => $result->post_date_gmt,
							'date_modified' => $result->post_modified_gmt,
						);

						edd_add_order_item( $order_item_args );
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

				if ( isset( $payment_meta['fees'] ) && ! empty( $payment_meta['fees'] ) ) {
					foreach ( $payment_meta['fees'] as $fee_id => $fee ) {

						// Reverse engineer the tax calculation.
						$tax = ( isset( $fee['no_tax'] ) && false === $fee['no_tax'] && ! empty( $tax_rate ) ) || ( $fee['amount'] < 0 && ! empty( $tax_rate ) )
							? floatval( floatval( $fee['amount'] ) - ( floatval( $fee['amount'] ) / ( 1 + $tax_rate ) ) )
							: 0.00;

						// Add the adjustment.
						$adjustment_id = edd_add_order_adjustment( array(
							'object_id'   => $order_id,
							'object_type' => 'order',
							'type'        => 'fee',
							'description' => $fee['label'],
							'subtotal'    => floatval( $fee['amount'] ),
							'tax'         => $tax,
							'total'       => floatval( $fee['amount'] ) + $tax,
						) );

						// Fee ID.
						edd_add_order_adjustment_meta( $adjustment_id, 'fee_id', $fee_id );

						// Maybe store download ID.
						if ( isset( $fee['download_id'] ) && ! empty( $fee['download_id'] ) ) {
							edd_add_order_adjustment_meta( $adjustment_id, 'download_id', $fee['download_id'] );
						}

						// Maybe store price ID.
						if ( isset( $fee['price_id'] ) && ! is_null( $fee['price_id'] ) ) {
							edd_add_order_adjustment_meta( $adjustment_id, 'price_id', $fee['price_id'] );
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
				edd_add_order_meta( $order_id, 'legacy_order_id', $result->ID );
			}

			return true;
		}

		return false;
	}

	/**
	 * Calculate the percentage completed.
	 *
	 * @since 3.0
	 *
	 * @return float Percentage.
	 */
	public function get_percentage_complete() {
		$total = $this->get_db()->get_var( $this->get_db()->prepare( "SELECT COUNT(id) AS count FROM {$this->get_db()->posts} WHERE post_type = %s", esc_sql( 'edd_payment' ) ) );

		if ( empty( $total ) ) {
			$total = 0;
		}

		$percentage = 100;

		if ( $total > 0 ) {
			$percentage = ( ( $this->per_step * $this->step ) / $total ) * 100;
		}

		if ( $percentage > 100 ) {
			$percentage = 100;
		}

		return $percentage;
	}
}