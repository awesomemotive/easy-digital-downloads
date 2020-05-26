<?php
/**
 * Easy Digital Downloads API V1
 *
 * @package     EDD
 * @subpackage  Classes/API
 * @copyright   Copyright (c) 2015, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       2.6
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

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
	 * Process Get Customers API Request
	 *
	 * @since 2.6
	 * @global object $wpdb Used to query the database using the WordPress Database API
	 * @param array $args Array of arguments for filters customers
	 * @return array $customers Multidimensional array of the customers
	 */
	public function get_customers( $args = array() ) {
		global $wpdb;

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

		if( ! user_can( $this->user_id, 'view_shop_sensitive_data' ) && ! $this->override ) {
			return $customers;
		}

		if( is_numeric( $args['customer'] ) ) {
			$field = 'id';
		} else {
			$field = 'email';
		}

		$args[ $field ] = $args['customer'];

		$dates = $this->get_dates( $args );

		if( $args['date'] === 'range' ) {

			// Ensure the end date is later than the start date
			if( ( ! empty( $args['enddate'] ) && ! empty( $args['enddate'] ) ) && $args['enddate'] < $args['startdate'] ) {
				$error['error'] = __( 'The end date must be later than the start date!', 'easy-digital-downloads' );
			}

			$date_range = array();
			if ( ! empty( $args['startdate'] ) ) {
				$date_range['start'] = $dates['year']     . sprintf('%02d', $dates['m_start'] ) . $dates['day_start'];
			}

			if ( ! empty( $args['enddate'] ) ) {
				$date_range['end'] = $dates['year_end'] . sprintf('%02d', $dates['m_end'] )   . $dates['day_end'];
			}

			$args['date'] = $date_range;

		} elseif( ! empty( $args['date'] ) ) {

			if( $args['date'] == 'this_quarter' || $args['date'] == 'last_quarter'  ) {

				$args['date'] = array(
					'start' => $dates['year'] . sprintf('%02d', $dates['m_start'] ) . '01',
					'end'   => $dates['year'] . sprintf('%02d', $dates['m_end'] )   . cal_days_in_month( CAL_GREGORIAN, $dates['m_end'], $dates['year'] ),
				);

			} else if ( $args['date'] == 'this_month' || $args['date'] == 'last_month' ) {
				$args['date'] = array(
					'start' => $dates['year'] . sprintf( '%02d', $dates['m_start'] ) . '01',
					'end'   => $dates['year'] . sprintf( '%02d', $dates['m_end'] ). cal_days_in_month( CAL_GREGORIAN, $dates['m_end'], $dates['year'] ),
				);
			} else if ( $args['date'] == 'this_year' || $args['date'] == 'last_year' ) {
				$args['date'] = array(
					'start' => $dates['year'] . '0101',
					'end'   => $dates['year'] . '1231',
				);
			} else {
				$args['date'] = $dates['year'] . sprintf('%02d', $dates['m_start'] ) . $dates['day'];
			}
		}

		unset( $args['startdate'], $args['enddate'] );

		$customer_query = EDD()->customers->get_customers( $args );
		$customer_count = 0;

		if( $customer_query ) {

			foreach ( $customer_query as $customer_obj ) {
				// Setup a new EDD_Customer object so additional details are defined (like additional emails)
				$customer_obj = new EDD_Customer( $customer_obj->id );

				$names      = explode( ' ', $customer_obj->name );
				$first_name = ! empty( $names[0] ) ? $names[0] : '';
				$last_name  = '';
				if( ! empty( $names[1] ) ) {
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

					$primary_email_key = array_search( $customer_obj->email, $customer_obj->emails );
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
				$customers['customers'][ $customer_count ]['stats']['total_spent']     = $customer_obj->purchase_value;
				$customers['customers'][ $customer_count ]['stats']['total_downloads'] = edd_count_file_downloads_of_customer( $customer_obj->id );

				$customer_count++;

			}

		} elseif( $args['customer'] ) {

			$error['error'] = sprintf( __( 'Customer %s not found!', 'easy-digital-downloads' ), $customer );
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

		if( ! user_can( $this->user_id, 'view_shop_reports' ) && ! $this->override ) {
			return $sales;
		}

		if( isset( $wp_query->query_vars['id'] ) ) {
			$query   = array();
			$query[] = new EDD_Payment( $wp_query->query_vars['id'] );
		} elseif( isset( $wp_query->query_vars['purchasekey'] ) ) {
			$query   = array();
			$query[] = edd_get_payment_by( 'key', $wp_query->query_vars['purchasekey'] );
		} elseif( isset( $wp_query->query_vars['email'] ) ) {
			$query = edd_get_payments( array( 'fields' => 'ids', 'meta_key' => '_edd_payment_user_email', 'meta_value' => $wp_query->query_vars['email'], 'number' => $this->per_page(), 'page' => $this->get_paged(), 'status' => 'publish' ) );
		} else {
			$query = edd_get_payments( array( 'fields' => 'ids', 'number' => $this->per_page(), 'page' => $this->get_paged(), 'status' => 'publish' ) );
		}

		if ( $query ) {
			$i = 0;
			foreach ( $query as $payment ) {
				if ( is_numeric( $payment ) ) {
					$payment = new EDD_Payment( $payment );
				}

				$payment_meta = $payment->get_meta();
				$user_info    = $payment->user_info;

				$sales['sales'][ $i ]['ID']             = $payment->number;
				$sales['sales'][ $i ]['mode']           = $payment->mode;
				$sales['sales'][ $i ]['status']         = $payment->status;
				$sales['sales'][ $i ]['transaction_id'] = ( ! empty( $payment->transaction_id ) ) ? $payment->transaction_id : null;
				$sales['sales'][ $i ]['key']            = $payment->key;
				$sales['sales'][ $i ]['subtotal']       = $payment->subtotal;
				$sales['sales'][ $i ]['tax']            = $payment->tax;
				$sales['sales'][ $i ]['fees']           = ( ! empty( $payment->fees ) ? $payment->fees : null );
				$sales['sales'][ $i ]['total']          = $payment->total;
				$sales['sales'][ $i ]['gateway']        = $payment->gateway;
				$sales['sales'][ $i ]['customer_id']    = $payment->customer_id;
				$sales['sales'][ $i ]['user_id']        = $payment->user_id;
				$sales['sales'][ $i ]['email']          = $payment->email;
				$sales['sales'][ $i ]['date']           = $payment->date;

				$c = 0;

				$discounts       = ! empty( $payment->discounts ) ? explode( ',', $payment->discounts ) : array();
				$discounts       = array_map( 'trim', $discounts );
				$discount_values = array();

				foreach ( $discounts as $discount ) {
					if ( 'none' === $discount ) { continue; }

					$discount_values[ $discount ] = 0;
				}

				$cart_items = array();

				foreach ( $payment->cart_details as $key => $item ) {

					$item_id    = isset( $item['id']    )      ? $item['id']         : $item;
					$price      = isset( $item['price'] )      ? $item['price']      : false; // The final price for the item
					$item_price = isset( $item['item_price'] ) ? $item['item_price'] : false; // The price before discounts

					$price_id   = isset( $item['item_number']['options']['price_id'] ) ? $item['item_number']['options']['price_id'] : null;
					$quantity   = isset( $item['quantity'] ) && $item['quantity'] > 0  ? $item['quantity']                           : 1;

					if( ! $price ) {
						// This function is only used on payments with near 1.0 cart data structure
						$price = edd_get_download_final_price( $item_id, $user_info, null );
					}

					$price_name = '';
					if ( isset( $item['item_number'] ) && isset( $item['item_number']['options'] ) ) {
						$price_options  = $item['item_number']['options'];
						if ( isset( $price_options['price_id'] ) ) {
							$price_name = edd_get_price_option_name( $item_id, $price_options['price_id'], $payment->ID );
						}
					}

					$cart_items[ $c ]['id']         = $item_id;
					$cart_items[ $c ]['quantity']   = $quantity;
					$cart_items[ $c ]['name']       = get_the_title( $item_id );
					$cart_items[ $c ]['price']      = $price;
					$cart_items[ $c ]['price_name'] = $price_name;

					// Determine the discount amount for the item, if there is one
					foreach ( $discount_values as $discount => $amount ) {

						$item_discount = edd_get_cart_item_discount_amount( $item, $discount );
						$discount_values[ $discount ] += $item_discount;

					}

					$c++;
				}

				$sales['sales'][ $i ]['discounts'] = ( ! empty( $discount_values ) ? $discount_values : null );;
				$sales['sales'][ $i ]['products']  = $cart_items;

				$i++;
			}
		}
		return apply_filters( 'edd_api_sales', $sales, $this );
	}

}
