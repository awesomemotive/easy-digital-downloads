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
	 * @access public
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
			);

			if( ! empty( $args['s'] ) ) {
				$query_args['s'] = sanitize_text_field( $args['s'] );
			}

			if( ! empty( $args['category'] ) ) {
				if ( strpos( $args['category'], ',' ) ) {
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

		return $products;
	}

	/**
	 * Process Get Customers API Request
	 *
	 * @access public
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

		} else {
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

				$names      = explode( ' ', $customer_obj->name );
				$first_name = ! empty( $names[0] ) ? $names[0] : '';
				$last_name  = '';
				if( ! empty( $names[1] ) ) {
					unset( $names[0] );
					$last_name = implode( ' ', $names );
				}

				$customers['customers'][$customer_count]['info']['id']           = '';
				$customers['customers'][$customer_count]['info']['user_id']      = '';
				$customers['customers'][$customer_count]['info']['username']     = '';
				$customers['customers'][$customer_count]['info']['display_name'] = '';
				$customers['customers'][$customer_count]['info']['customer_id']  = $customer_obj->id;
				$customers['customers'][$customer_count]['info']['first_name']   = $first_name;
				$customers['customers'][$customer_count]['info']['last_name']    = $last_name;
				$customers['customers'][$customer_count]['info']['email']        = $customer_obj->email;
				$customers['customers'][$customer_count]['info']['date_created'] = $customer_obj->date_created;

				if ( ! empty( $customer_obj->user_id ) && $customer_obj->user_id > 0 ) {

					$user_data = get_userdata( $customer_obj->user_id );

					// Customer with registered account

					// id is going to get deprecated in the future, user user_id or customer_id instead
					$customers['customers'][$customer_count]['info']['id']           = $customer_obj->id;
					$customers['customers'][$customer_count]['info']['user_id']      = $customer_obj->user_id;
					$customers['customers'][$customer_count]['info']['username']     = $user_data->user_login;
					$customers['customers'][$customer_count]['info']['display_name'] = $user_data->display_name;

				}

				$customers['customers'][$customer_count]['stats']['total_purchases'] = $customer_obj->purchase_count;
				$customers['customers'][$customer_count]['stats']['total_spent']     = $customer_obj->purchase_value;
				$customers['customers'][$customer_count]['stats']['total_downloads'] = edd_count_file_downloads_of_user( $customer_obj->email );

				$customer_count++;

			}

		} elseif( $args['customer'] ) {

			$error['error'] = sprintf( __( 'Customer %s not found!', 'easy-digital-downloads' ), $customer );
			return $error;

		} else {

			$error['error'] = __( 'No customers found!', 'easy-digital-downloads' );
			return $error;

		}

		return $customers;
	}

}
