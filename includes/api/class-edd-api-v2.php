<?php
/**
 * Easy Digital Downloads API V2
 *
 * @package     EDD
 * @subpackage  Classes/API
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       2.6
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * EDD_API_V2 Class
 *
 * The base version API class
 *
 * @since  2.6
 */
class EDD_API_V2 extends EDD_API_V1 {

	/**
	 * Process Get Products API Request
	 *
	 * @since 2.6
	 * @param array $args Query arguments
	 * @return array $customers Multidimensional array of the products
	 */
	public function get_products( $args = array() ) {

		$products = array();
		$error    = array();

		if ( empty( $args['product'] ) ) {

			$products['products'] = array();

			$query_args = array(
				'post_type'        => 'download',
				'posts_per_page'   => $this->per_page(),
				'suppress_filters' => true,
				'paged'            => $this->get_paged(),
				'order'            => $args['order'],
				'orderby'          => $args['orderby'],
			);

			if( ! empty( $args['s'] ) ) {
				$query_args['s'] = sanitize_text_field( $args['s'] );
			}

			switch ( $query_args['orderby'] ) {
				case 'price':
					$query_args['meta_key'] = 'edd_price';
					$query_args['orderby']  = 'meta_value_num';
					break;

				case 'sales':
					if ( user_can( $this->user_id, 'view_shop_sensitive_data' ) || current_user_can( 'view_shop_sensitive_data' ) || $this->override ) {
						$query_args['meta_key'] = '_edd_download_sales';
						$query_args['orderby']  = 'meta_value_num';
					}
					break;

				case 'earnings':
					if ( user_can( $this->user_id, 'view_shop_sensitive_data' ) || current_user_can( 'view_shop_sensitive_data' ) || $this->override ) {
						$query_args['meta_key'] = '_edd_download_earnings';
						$query_args['orderby']  = 'meta_value_num';
					}
					break;

			}

			if( ! empty( $args['category'] ) ) {
				if ( is_string( $args[ 'categrory' ] ) ) {
					$args['category'] = explode( ',', $args['category'] );
				}

				if ( is_numeric( $args['category'] ) ) {
					$query_args['tax_query'] = array(
						array(
							'taxonomy' => 'download_category',
							'field'    => 'ID',
							'terms'    => (int) $args['category']
						),
					);
				} else if ( is_array( $args['category'] ) ) {

					foreach ( $args['category'] as $category ) {


						$field = is_numeric( $category ) ? 'ID': 'slug';

						$query_args['tax_query'][] = array(
							'taxonomy' => 'download_category',
							'field'    => $field,
							'terms'    => $category,
						);

					}

				} else {
					$query_args['download_category'] = $args['category'];
				}
			}

			if( ! empty( $args['tag'] ) ) {
				if ( strpos( $args['tag'], ',' ) ) {
					$args['tag'] = explode( ',', $args['tag'] );
				}

				if ( is_numeric( $args['tag'] ) ) {
					$query_args['tax_query'] = array(
						array(
							'taxonomy' => 'download_tag',
							'field'    => 'ID',
							'terms'    => (int) $args['tag']
						),
					);
				} else if ( is_array( $args['tag'] ) ) {

					foreach ( $args['tag'] as $tag ) {


						$field = is_numeric( $tag ) ? 'ID': 'slug';

						$query_args['tax_query'][] = array(
							'taxonomy' => 'download_tag',
							'field'    => $field,
							'terms'    => $tag,
						);

					}

				} else {
					$query_args['download_tag'] = $args['tag'];
				}
			}

			if ( ! empty( $query_args['tax_query'] ) ) {

				$relation = ! empty( $args['term_relation'] ) ? sanitize_text_field( $args['term_relation'] ) : 'OR';
				$query_args['tax_query']['relation'] = $relation;

			}

			/**
			 * Filter the query arguments for the products API
			 *
			 * @since 3.2.2
			 *
			 * @param array $query_args The query arguments.
			 * @param array $args       The original arguments passed to the API.
			 *
			 * @return array $query_args The modified query arguments.
			 */
			$query_args   = apply_filters( 'edd_api_v2_products_query_args', $query_args, $args );
			$product_list = get_posts( $query_args );

			if ( $product_list ) {
				$i = 0;
				foreach ( $product_list as $product_info ) {
					$products['products'][$i] = $this->get_product_data( $product_info );
					$i++;
				}
			}

		} else {

			if ( get_post_type( $args['product'] ) == 'download' ) {
				$product_info = get_post( $args['product'] );

				$products['products'][0] = $this->get_product_data( $product_info );

			} else {
				$error['error'] = sprintf( __( 'Product %s not found!', 'easy-digital-downloads' ), $args['product'] );
				return $error;
			}
		}

		return apply_filters( 'edd_api_products', $products );
	}

	/**
	 * Given a download post object, generate the data for the API output
	 *
	 * @since  2.6
	 * @param  object $product_info The Download Post Object
	 * @return array                Array of post data to return back in the API
	 */
	public function get_product_data( $product_info ) {

		// Use the parent's get_product_data to reduce code duplication
		$product = parent::get_product_data( $product_info );

		if ( edd_use_skus() ) {
			$product['info']['sku'] = edd_get_download_sku( $product['info']['id'] );
		}

		return apply_filters( 'edd_api_products_product_v2', $product );

	}

	/**
	 * Process Get Customers API Request.
	 *
	 * @since 2.6
	 *
	 * @param array $args Array of arguments for filters customers.
	 *
	 * @return array $customers Multidimensional array of the customers.
	 */
	public function get_customers( $args = array() ) {

		$paged    = $this->get_paged();
		$per_page = $this->per_page();
		$offset   = $per_page * ( $paged - 1 );

		$defaults = array(
			'customer'  => null,
			'date'      => null,
			'startdate' => null,
			'enddate'   => null,
			'number'    => $per_page,
			'offset'    => $offset,
		);

		$args      = wp_parse_args( $args, $defaults );
		$customers = array();
		$error     = array();
		$stats     = new EDD\Stats(
			array(
				'output' => 'formatted',
			)
		);

		if ( ! user_can( $this->user_id, 'view_shop_sensitive_data' ) && ! $this->override ) {
			return $customers;
		}

		$query_by_customer = false;
		if ( is_numeric( $args['customer'] ) ) {
			$field = 'id';
		} elseif ( is_email( $args['customer'] ) ) {
			$field = 'email';
		}

		if ( isset( $field ) ) {
			$args[ $field ] = $args['customer'];

			if ( ! empty( $args[ $field ] ) ) {
				$query_by_customer = true;
				unset( $args['customer'] );
			}
		}

		if ( ! empty( $args['date'] ) ) {
			if ( 'range' === $args['date'] ) {
				if ( ! empty( $args['startdate'] ) ) {
					$_GET['filter_from'] = $args['startdate'];
				}

				if ( ! empty( $args['enddate'] ) ) {
					$_GET['filter_to'] = $args['enddate'];
				}

				$_GET['range'] = 'other';
			} elseif ( ! empty( $args['date'] ) ) {
				$_GET['range'] = $args['date'];
			}

			$dates = EDD\Reports\parse_dates_for_range();

			$date_query = array(
				'column' => 'date_created',
			);

			if ( ! empty( $dates['start'] ) && ! empty( $dates['end'] ) ) {
				$date_query['compare'] = 'BETWEEN';
				$date_query['after']   = $dates['start']->format( 'Y-m-d' );
				$date_query['before']  = $dates['end']->format( 'Y-m-d' );
			} elseif ( ! empty( $dates['start'] ) ) {
				$date_query['after'] = $dates['start']->format( 'Y-m-d' );
			} elseif ( ! empty( $dates['end'] ) ) {
				$date_query['before'] = $dates['end']->format( 'Y-m-d' );
			}

			$date_query = array_filter( $date_query );
			if ( ! empty( $date_query ) ) {
				$args['date_query'] = $date_query;
			}
		}

		unset( $args['startdate'], $args['enddate'] );

		// Remove any empty values.
		$args = array_filter( $args );

		$customer_query = edd_get_customers( $args );
		$customer_count = 0;

		if ( $customer_query ) {

			foreach ( $customer_query as $customer_obj ) {
				// Setup a new EDD_Customer object so additional details are defined (like additional emails)
				$customer_obj = new EDD_Customer( $customer_obj->id );

				$names      = explode( ' ', $customer_obj->name );
				$first_name = ! empty( $names[0] ) ? $names[0] : '';
				$last_name  = '';
				if ( ! empty( $names[1] ) ) {
					unset( $names[0] );
					$last_name = implode( ' ', $names );
				}

				$customers['customers'][ $customer_count ]['info']['customer_id']       = $customer_obj->id;
				$customers['customers'][ $customer_count ]['info']['user_id']           = 0;
				$customers['customers'][ $customer_count ]['info']['username']          = '';
				$customers['customers'][ $customer_count ]['info']['display_name']      = '';
				$customers['customers'][ $customer_count ]['info']['first_name']        = $first_name;
				$customers['customers'][ $customer_count ]['info']['last_name']         = $last_name;
				$customers['customers'][ $customer_count ]['info']['email']             = $customer_obj->email;
				$customers['customers'][ $customer_count ]['info']['additional_emails'] = null;
				$customers['customers'][ $customer_count ]['info']['date_created']      = $customer_obj->date_created;

				if ( ! empty( $customer_obj->emails ) && count( $customer_obj->emails ) > 1 ) {
					$additional_emails = $customer_obj->emails;

					$primary_email_key = array_search( $customer_obj->email, $customer_obj->emails, true );
					if ( false !== $primary_email_key ) {
						unset( $additional_emails[ $primary_email_key ] );
					}

					$customers['customers'][ $customer_count ]['info']['additional_emails'] = $additional_emails;
				}

				if ( ! empty( $customer_obj->user_id ) && $customer_obj->user_id > 0 ) {

					$user_data = get_userdata( $customer_obj->user_id );

					// Customer with registered account

					// id is going to get deprecated in the future, user user_id or customer_id instead
					$customers['customers'][ $customer_count ]['info']['user_id']      = $customer_obj->user_id;
					$customers['customers'][ $customer_count ]['info']['username']     = $user_data->user_login;
					$customers['customers'][ $customer_count ]['info']['display_name'] = $user_data->display_name;

				}

				$customers['customers'][ $customer_count ]['stats']['total_purchases'] = $customer_obj->purchase_count;
				$customers['customers'][ $customer_count ]['stats']['total_spent']     = edd_format_amount( $customer_obj->purchase_value, true, '', 'typed' );
				$customers['customers'][ $customer_count ]['stats']['total_downloads'] = edd_count_file_downloads_of_customer( $customer_obj->id );

				$customer_count++;

			}
		} elseif ( true === $query_by_customer ) {

			$error['error'] = __( 'Customer not found!', 'easy-digital-downloads' );
			return $error;

		} else {

			$error['error'] = __( 'No customers found!', 'easy-digital-downloads' );
			return $error;

		}

		return apply_filters( 'edd_api_customers', $customers, $this );
	}

	/**
	 * Retrieves Recent Sales
	 *
	 * @since  2.6
	 * @return array
	 */
	public function get_recent_sales() {
		global $wp_query;

		$sales = array();

		if ( ! user_can( $this->user_id, 'view_shop_reports' ) && ! $this->override ) {
			return $sales;
		}

		if ( isset( $wp_query->query_vars['id'] ) ) {
			$query   = array();
			$query[] = edd_get_order( $wp_query->query_vars['id'] );
		} elseif ( isset( $wp_query->query_vars['purchasekey'] ) ) {
			$query   = array();
			$query[] = edd_get_order_by( 'payment_key', $wp_query->query_vars['purchasekey'] );
		} elseif ( isset( $wp_query->query_vars['email'] ) ) {
			$query = edd_get_orders(
				array(
					'type'       => 'sale',
					'email'      => $wp_query->query_vars['email'],
					'number'     => $this->per_page(),
					'offset'     => ( $this->get_paged() - 1 ) * $this->per_page(),
					'status__in' => edd_get_net_order_statuses(),
				)
			);
		} else {
			$query = edd_get_orders(
				array(
					'type'       => 'sale',
					'number'     => $this->per_page(),
					'offset'     => ( $this->get_paged() - 1 ) * $this->per_page(),
					'status__in' => edd_get_net_order_statuses(),
				)
			);
		}

		if ( $query ) {
			$i = 0;
			foreach ( $query as $order ) {
				/** @var EDD\Orders\Order $order  An Order object. */

				$localized_time = edd_get_edd_timezone_equivalent_date_from_utc( EDD()->utils->date( $order->date_created ) );

				$sales['sales'][ $i ]['ID']             = $order->get_number();
				$sales['sales'][ $i ]['mode']           = $order->mode;
				$sales['sales'][ $i ]['status']         = $order->status;
				$sales['sales'][ $i ]['transaction_id'] = $order->get_transaction_id();
				$sales['sales'][ $i ]['key']            = $order->payment_key;
				$sales['sales'][ $i ]['subtotal']       = $order->subtotal;
				$sales['sales'][ $i ]['tax']            = $order->tax;
				$sales['sales'][ $i ]['total']          = $order->total;
				$sales['sales'][ $i ]['gateway']        = $order->gateway;
				$sales['sales'][ $i ]['customer_id']    = $order->customer_id;
				$sales['sales'][ $i ]['user_id']        = $order->user_id;
				$sales['sales'][ $i ]['email']          = $order->email;
				$sales['sales'][ $i ]['date']           = $localized_time->copy()->format( 'Y-m-d H:i:s' );
				$sales['sales'][ $i ]['date_utc']       = $order->date_created;

				$fees      = array();
				$discounts = array();

				foreach ( $order->adjustments as $adjustment ) {
					switch ( $adjustment->type ) {
						case 'fee':
							$fees[] = array(
								'amount'      => $adjustment->total,
								'label'       => $adjustment->description,
								'no_tax'      => empty( $adjustment->tax ),
								'type'        => $adjustment->type,
								'price_id'    => null,
								'download_id' => null,
								'id'          => $adjustment->type_key,
							);
							break;

						case 'discount':
							$discounts[ $adjustment->description ] = $adjustment->total;
							break;
					}
				}

				$c = 0;
				$cart_items = array();

				foreach ( $order->items as $item ) {
					$cart_items[ $c ]['object_id'] = $item->id;
					$cart_items[ $c ]['id']        = $item->product_id;
					$cart_items[ $c ]['quantity']  = $item->quantity;
					$cart_items[ $c ]['name']      = $item->product_name;
					$cart_items[ $c ]['price']     = $item->total;

					// Keeping this here for backwards compatibility.
					$cart_items[ $c ]['price_name'] = null === $item->price_id
						? ''
						: edd_get_price_name( $item->product_id, array( 'price_id' => $item->price_id ) );

					// Check for any item level fees to include in the fees array.
					foreach ( $item->adjustments as $adjustment ) {
						if ( 'fee' === $adjustment->type ) {
							$fees[] = array(
								'amount'      => $adjustment->total,
								'label'       => $adjustment->description,
								'no_tax'      => empty( $adjustment->tax ),
								'type'        => $adjustment->type,
								'price_id'    => $item->price_id,
								'download_id' => $item->product_id,
								'id'          => $adjustment->type_key,
							);
						}
					}

					$c++;
				}

				$sales['sales'][ $i ]['products']  = $cart_items;
				$sales['sales'][ $i ]['fees']      = ! empty( $fees ) ? $fees : null;
				$sales['sales'][ $i ]['discounts'] = ! empty( $discounts ) ? $discounts : null;

				$i++;
			}
		}
		return apply_filters( 'edd_api_sales', $sales, $this );
	}

}
