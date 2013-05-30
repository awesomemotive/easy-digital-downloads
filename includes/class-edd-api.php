<?php
/**
 * Easy Digital Downloads API
 *
 * This class provides a front-facing JSON/XML API that makes it possible to
 * query data from the shop.
 *
 * The primary purpose of this class is for external sales / earnings tracking
 * systems, such as mobile. This class is also used in the EDD iOS App.
 *
 * @package     EDD
 * @subpackage  Classes/API
 * @copyright   Copyright (c) 2013, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.5
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * EDD_API Class
 *
 * Renders API returns as a JSON/XML array
 *
 * @since  1.5
 */
class EDD_API {
	/**
	 * Pretty Print?
	 *
	 * @var bool
	 * @access private
	 * @since 1.5
	 */
	private $pretty_print = false;

	/**
	 * Log API requests?
	 *
	 * @var bool
	 * @access private
	 * @since 1.5
	 */
	private $log_requests = true;

	/**
	 * Is this a valid request?
	 *
	 * @var bool
	 * @access private
	 * @since 1.5
	 */
	private $is_valid_request = false;

	/**
	 * User ID Performing the API Request
	 *
	 * @var int
	 * @access private
	 * @since 1.5.1
	 */
	private $user_id = 0;

	/**
	 * Response data to return
	 *
	 * @var array
	 * @access private
	 * @since 1.5.2
	 */
	private $data = array();

	/**
	 * Setup the EDD API
	 *
	 * @access public
	 * @author Daniel J Griffiths
	 * @since 1.5
	 * @return void
	 */
	public function __construct() {
		add_action( 'init',                    array( $this, 'add_endpoint'   ) );
      	add_action( 'template_redirect',       array( $this, 'process_query'  ), -1 );
		add_filter( 'query_vars',              array( $this, 'query_vars'     ) );
		add_action( 'show_user_profile',       array( $this, 'user_key_field' ) );
		add_action( 'personal_options_update', array( $this, 'update_key'     ) );

		// Determine if JSON_PRETTY_PRINT is available
		$this->pretty_print = defined( 'JSON_PRETTY_PRINT' ) ? JSON_PRETTY_PRINT : null;

		// Allow API request logging to be turned off
		$this->log_requests = apply_filters( 'edd_api_log_requests', $this->log_requests );
	}

	/**
	 * Registers a new rewrite endpoint for accessing the API
	 *
	 * @access public
	 * @author Daniel J Griffiths
	 * @param array $rewrite_rules WordPress Rewrite Rules
	 * @since 1.5
	 */
	public function add_endpoint( $rewrite_rules ) {
		add_rewrite_endpoint( 'edd-api', EP_ALL );
	}

	/**
	 * Registers query vars for API access
	 *
	 * @access public
	 * @since 1.5
	 * @author Daniel J Griffiths
	 * @param array $vars Query vars
	 * @return array $vars New query vars
	 */
	public function query_vars( $vars ) {
		$vars[] = 'token';
		$vars[] = 'key';
		$vars[] = 'query';
		$vars[] = 'type';
		$vars[] = 'product';
		$vars[] = 'number';
		$vars[] = 'date';
		$vars[] = 'startdate';
		$vars[] = 'enddate';
		$vars[] = 'customer';
		$vars[] = 'discount';
		$vars[] = 'format';

		return $vars;
	}

	/**
	 * Validate the API request
	 *
	 * Checks for the user's public key and token against the secret key
	 *
	 * @access private
	 * @global object $wp_query WordPress Query
	 * @uses EDD_API::get_user()
	 * @uses EDD_API::invalid_key()
	 * @uses EDD_API::invalid_auth()
	 * @since 1.5
	 * @return void
	 */
	private function validate_request() {
		global $wp_query;

		// Make sure we have both user and api key
		if ( empty( $wp_query->query_vars['token'] ) || empty( $wp_query->query_vars['key'] ) )
			$this->missing_auth();

		// Retrieve the user by public API key and ensure they exist
		if ( ! ( $user = $this->get_user( $wp_query->query_vars['key'] ) ) ) :
			$this->invalid_key( $wp_query->query_vars['key'] );
		else :
			$token  = urldecode( $wp_query->query_vars['token'] );
			$secret = get_user_meta( $user, 'edd_user_secret_key', true );
			$public = urldecode( $wp_query->query_vars['key'] );

			if ( hash( 'md5', $secret . $public ) === $token )
				$this->is_valid_request = true;
			else
				$this->invalid_auth();
		endif;
	}

	/**
	 * Retrieve the user ID based on the public key provided
	 *
	 * @access public
	 * @since 1.5.1
	 * @global object $wpdb Used to query the database using the WordPress
	 *   Database API
	 * @param int $key Public Key
	 * @return mixed string if user ID is found, false otherwise
	 */
	public function get_user( $key = '' ) {
		global $wpdb;

		$user = $wpdb->get_var( $wpdb->prepare( "SELECT user_id FROM $wpdb->usermeta WHERE meta_key = 'edd_user_public_key' AND meta_value = %s LIMIT 1", $key ) );

		if ( $user != NULL ) {
			$this->user_id = $user;
			return $user;
		}
		return false;
	}

	/**
	 * Displays a missing authentication error if all the parameters aren't
	 * provided
	 *
	 * @access public
	 * @author Daniel J Griffiths
	 * @uses EDD_API::output()
	 * @since 1.5
	 */
	public function missing_auth() {
		$error['error'] = __( 'You must specify both a token and API key!', 'edd' );

		$this->data = $error;
		$this->output();
	}

	/**
	 * Displays an authentication failed error if the user failed to provide valid
	 * credentials
	 *
	 * @access public
	 * @since  1.5
	 * @uses EDD_API::output()
	 * @return void
	 */
	function invalid_auth() {
		$error['error'] = __( 'Your request could not be authenticated!', 'edd' );

		$this->data = $error;
		$this->output();
	}

	/**
	 * Displays an invalid API key error if the API key provided couldn't be
	 * validated
	 *
	 * @access public
	 * @author Daniel J Griffiths
	 * @since 1.5
	 * @uses EDD_API::output()
	 * @return void
	 */
	function invalid_key() {
		$error['error'] = __( 'Invalid API key!', 'edd' );

		$this->data = $error;
		$this->output();
	}


	/**
	 * Listens for the API and then processes the API requests
	 *
	 * @access public
	 * @author Daniel J Griffiths
	 * @global $wp_query
	 * @since 1.5
	 * @return void
	 */
	public function process_query() {
		global $wp_query;

		// Check for edd-api var. Get out if not present
		if ( ! isset( $wp_query->query_vars['edd-api'] ) )
			return;

		// Check for a valid user and set errors if necessary
		$this->validate_request();

		// Only proceed if no errors have been noted
		if( ! $this->is_valid_request )
			return;

		// Determine the kind of query
		$query_mode = $this->get_query_mode();

		$data = array();

		switch( $query_mode ) :

			case 'stats' :

				$data = $this->get_stats( array(
					'type'      => isset( $wp_query->query_vars['type'] )      ? $wp_query->query_vars['type']      : null,
					'product'   => isset( $wp_query->query_vars['product'] )   ? $wp_query->query_vars['product']   : null,
					'date'      => isset( $wp_query->query_vars['date'] )      ? $wp_query->query_vars['date']      : null,
					'startdate' => isset( $wp_query->query_vars['startdate'] ) ? $wp_query->query_vars['startdate'] : null,
					'enddate'   => isset( $wp_query->query_vars['enddate'] )   ? $wp_query->query_vars['enddate']   : null
				) );

				break;

			case 'products' :

				$product = isset( $wp_query->query_vars['product'] )   ? $wp_query->query_vars['product']   : null;

				$data = $this->get_products( $product );

				break;

			case 'customers' :

				$customer = isset( $wp_query->query_vars['customer'] ) ? $wp_query->query_vars['customer']  : null;

				$data = $this->get_customers( $customer );

				break;

			case 'sales' :

				$data = $this->get_recent_sales();

				break;

			case 'discounts' :

				$discount = isset( $wp_query->query_vars['discount'] ) ? $wp_query->query_vars['discount']  : null;

				$data = $this->get_discounts( $discount );

				break;

		endswitch;

		// Allow extensions to setup their own return data
		$this->data = apply_filters( 'edd_api_output_data', $data, $query_mode, $this );

		// Log this API request, if enabled. We log it here because we have access to errors.
		$this->log_request( $this->data );

		// Send out data to the output function
		$this->output();
	}

	/**
	 * Determines the kind of query requested and also ensure it is a valid query
	 *
	 * @access private
	 * @since 1.5
	 * @global $wp_query
	 * @return string $query Query mode
	 */
	private function get_query_mode() {
		global $wp_query;

		// Whitelist our query options
		$accepted = apply_filters( 'edd_api_valid_query_modes', array(
			'stats',
			'products',
			'customers',
			'sales',
			'discounts'
		) );

		$query = isset( $wp_query->query_vars['edd-api'] ) ? $wp_query->query_vars['edd-api'] : null;

		// Make sure our query is valid
		if ( ! in_array( $query, $accepted ) ) {
			$error['error'] = __( 'Invalid query!', 'edd' );

			$this->data = $error;
			$this->output();
		}

		return $query;
	}

	/**
	 * Get page number
	 *
	 * @access private
	 * @since 1.5
	 * @global $wp_query
	 * @return int $wp_query->query_vars['page'] if page number returned (default: 1)
	 */
	private function get_paged() {
		global $wp_query;

		return isset( $wp_query->query_vars['page'] ) ? $wp_query->query_vars['page'] : 1;
	}


	/**
	 * Number of results to display per page
	 *
	 * @access private
	 * @since 1.5
	 * @global $wp_query
	 * @return int $per_page Results to display per page (default: 10)
	 */
	private function per_page() {
		global $wp_query;

		$per_page = isset( $wp_query->query_vars['number'] ) ? $wp_query->query_vars['number'] : 10;

		if( $per_page < 0 && $this->get_query_mode() == 'customers' )
			$per_page = 99999999; // Customers query doesn't support -1

		return apply_filters( 'edd_api_results_per_page', $per_page );
	}

	/**
	 * Retrieve the output format
	 *
	 * Determines whether results should be displayed in XML or JSON
	 *
	 * @access private
	 * @since 1.5
	 * @global $wp_query
	 * @return $format Output format
	 */
	private function get_output_format() {
		global $wp_query;

		$format = isset( $wp_query->query_vars['format'] ) ? $wp_query->query_vars['format'] : 'json';

		return apply_filters( 'edd_api_output_format', $format );
	}

	/**
	 * Sets up the dates used to retrieve earnings/sales
	 *
	 * @access public
	 * @since 1.5.1
	 * @param array $args Arguments to override defaults
	 * @return array $dates
	*/
	private function get_dates( $args = array() ) {
		$dates = array();

		$defaults = array(
			'type'      => '',
			'product'   => null,
			'date'      => null,
			'startdate' => null,
			'enddate'   => null
		);

		$args = wp_parse_args( $args, $defaults );

		date_default_timezone_set( edd_get_timezone_id() );

		if ( 'range' === $args['date'] ) {
			$startdate          = strtotime( $args['startdate'] );
			$enddate            = strtotime( $args['enddate'] );
			$dates['day_start'] = date( 'd', $startdate );
			$dates['day_end'] 	= date( 'd', $enddate );
			$dates['m_start'] 	= date( 'n', $startdate );
			$dates['m_end'] 	= date( 'n', $enddate );
			$dates['year'] 		= date( 'Y', $startdate );
			$dates['year_end'] 	= date( 'Y', $enddate );
		} else {
			// Modify dates based on predefined ranges
			switch ( $args['date'] ) :

				case 'this_month' :
					$dates['day'] 	    = null;
					$dates['m_start'] 	= date( 'n' );
					$dates['m_end']		= date( 'n' );
					$dates['year']		= date( 'Y' );
				break;

				case 'last_month' :
					$dates['day'] 	  = null;
					$dates['m_start'] = date( 'n' ) == 1 ? 12 : date( 'n' ) - 1;
					$dates['m_end']	  = $dates['m_start'];
					$dates['year']    = date( 'n' ) == 1 ? date( 'Y' ) - 1 : date( 'Y' );
				break;

				case 'today' :
					$dates['day']		= date( 'd' );
					$dates['m_start'] 	= date( 'n' );
					$dates['m_end']		= date( 'n' );
					$dates['year']		= date( 'Y' );
				break;

				case 'yesterday' :
					$month              = date( 'n' ) == 1 ? 12 : date( 'n' );
					$days_in_month      = cal_days_in_month( CAL_GREGORIAN, $month, date( 'Y' ) );
					$yesterday          = date( 'd' ) == 1 ? $days_in_month : date( 'd' ) - 1;
					$dates['day']		= $yesterday;
					$dates['m_start'] 	= $month;
					$dates['m_end'] 	= $month;
					$dates['year']		= $month == 1 && date( 'd' ) == 1 ? date( 'Y' ) - 1 : date( 'Y' );
				break;

				case 'this_quarter' :
					$month_now = date( 'n' );

					$dates['day'] 	        = null;

					if ( $month_now <= 3 ) {

						$dates['m_start'] 	= 1;
						$dates['m_end']		= 3;
						$dates['year']		= date( 'Y' );

					} else if ( $month_now <= 6 ) {

						$dates['m_start'] 	= 4;
						$dates['m_end']		= 6;
						$dates['year']		= date( 'Y' );

					} else if ( $month_now <= 9 ) {

						$dates['m_start'] 	= 7;
						$dates['m_end']		= 9;
						$dates['year']		= date( 'Y' );

					} else {

						$dates['m_start'] 	= 10;
						$dates['m_end']		= 12;
						$dates['year']		= date( 'Y' );

					}
				break;

				case 'last_quarter' :
					$month_now = date( 'n' );

					$dates['day'] 	        = null;

					if ( $month_now <= 3 ) {

						$dates['m_start'] 	= 10;
						$dates['m_end']		= 12;
						$dates['year']		= date( 'Y' ) - 1; // Previous year

					} else if ( $month_now <= 6 ) {

						$dates['m_start'] 	= 1;
						$dates['m_end']		= 3;
						$dates['year']		= date( 'Y' );

					} else if ( $month_now <= 9 ) {

						$dates['m_start'] 	= 4;
						$dates['m_end']		= 6;
						$dates['year']		= date( 'Y' );

					} else {

						$dates['m_start'] 	= 7;
						$dates['m_end']		= 9;
						$dates['year']		= date( 'Y' );

					}
				break;

				case 'this_year' :
					$dates['day'] 	    = null;
					$dates['m_start'] 	= null;
					$dates['m_end']		= null;
					$dates['year']		= date( 'Y' );
				break;

				case 'last_year' :
					$dates['day'] 	    = null;
					$dates['m_start'] 	= null;
					$dates['m_end']		= null;
					$dates['year']		= date( 'Y' ) - 1;
				break;

			endswitch;
		}

		return apply_filters( 'edd_api_stat_dates', $dates );
	}

	/**
	 * Process Get Customers API Request
	 *
	 * @access public
	 * @since 1.5
	 * @author Daniel J Griffiths
	 * @global object $wpdb Used to query the database using the WordPress
	 *   Database API
	 * @param int $customer Customer ID
	 * @return array $customers Multidimensional array of the customers
	 */
	public function get_customers( $customer = null ) {
		if ( $customer == null ) {
			global $wpdb;

			$paged    = $this->get_paged();
			$per_page = $this->per_page();
			$offset   = $per_page * ( $paged - 1 );
			$customer_list_query = $wpdb->get_col( "SELECT DISTINCT meta_value FROM $wpdb->postmeta where meta_key = '_edd_payment_user_email' ORDER BY meta_id DESC LIMIT $per_page OFFSET $offset" );
			$customer_count = 0;

			foreach ( $customer_list_query as $customer_email ) {
				$customer_info = get_user_by( 'email', $customer_email );

				if ( $customer_info ) {
					// Customer with registered account
					$customers['customers'][$customer_count]['info']['id']           = $customer_info->ID;
					$customers['customers'][$customer_count]['info']['username']     = $customer_info->user_login;
					$customers['customers'][$customer_count]['info']['display_name'] = $customer_info->display_name;
					$customers['customers'][$customer_count]['info']['first_name']   = $customer_info->user_firstname;
					$customers['customers'][$customer_count]['info']['last_name']    = $customer_info->user_lastname;
					$customers['customers'][$customer_count]['info']['email']        = $customer_info->user_email;
				} else {
					// Guest customer
					$customers['customers'][$customer_count]['info']['id']           = -1;
					$customers['customers'][$customer_count]['info']['username']     = __( 'Guest', 'edd' );
					$customers['customers'][$customer_count]['info']['display_name'] = __( 'Guest', 'edd' );
					$customers['customers'][$customer_count]['info']['first_name']   = __( 'Guest', 'edd' );
					$customers['customers'][$customer_count]['info']['last_name']    = __( 'Guest', 'edd' );
					$customers['customers'][$customer_count]['info']['email']        = $customer_email;
				}

				$customers['customers'][$customer_count]['stats']['total_purchases'] = edd_count_purchases_of_customer( $customer_email );
				$customers['customers'][$customer_count]['stats']['total_spent']     = edd_purchase_total_of_user( $customer_email );
				$customers['customers'][$customer_count]['stats']['total_downloads'] = edd_count_file_downloads_of_user( $customer_email );

				$customer_count++;
			}
		} else {
			if ( is_numeric( $customer ) ) {
				$customer_info = get_userdata( $customer );
			} else {
				$customer_info = get_user_by( 'email', $customer );
			}

			if ( $customer_info && edd_has_purchases( $customer_info->ID ) ) {
				$customers['customers'][0]['info']['id']               = $customer_info->ID;
				$customers['customers'][0]['info']['username']         = $customer_info->user_login;
				$customers['customers'][0]['info']['display_name']     = $customer_info->display_name;
				$customers['customers'][0]['info']['first_name']       = $customer_info->user_firstname;
				$customers['customers'][0]['info']['last_name']        = $customer_info->user_lastname;
				$customers['customers'][0]['info']['email']            = $customer_info->user_email;

				$customers['customers'][0]['stats']['total_purchases'] = edd_count_purchases_of_customer( $customer );
				$customers['customers'][0]['stats']['total_spent']     = edd_purchase_total_of_user( $customer );
				$customers['customers'][0]['stats']['total_downloads'] = edd_count_file_downloads_of_user( $customer );
			} else {
				$error['error'] = sprintf( __( 'Customer %s not found!', 'edd' ), $customer );
				return $error;
			}
		}

		return $customers;
	}

	/**
	 * Process Get Products API Request
	 *
	 * @access public
	 * @author Daniel J Griffiths
	 * @since 1.5
	 * @param int $product Product (Download) ID
	 * @return array $customers Multidimensional array of the products
	 */
	public function get_products( $product = null ) {
		$products = array();

		if ( $product == null ) {
			$products['products'] = array();

			$product_list = get_posts( array( 'post_type' => 'download', 'posts_per_page' => $this->per_page(), 'paged' => $this->get_paged() ) );

			if ( $product_list ) {
				$i = 0;
				foreach ( $product_list as $product_info ) {
					$products['products'][$i]['info']['id']                           = $product_info->ID;
					$products['products'][$i]['info']['slug']                         = $product_info->post_name;
					$products['products'][$i]['info']['title']                        = $product_info->post_title;
					$products['products'][$i]['info']['create_date']                  = $product_info->post_date;
					$products['products'][$i]['info']['modified_date']                = $product_info->post_modified;
					$products['products'][$i]['info']['status']                       = $product_info->post_status;
					$products['products'][$i]['info']['link']                         = html_entity_decode( $product_info->guid );
					$products['products'][$i]['info']['content']                      = $product_info->post_content;
					$products['products'][$i]['info']['thumbnail']                    = wp_get_attachment_url( get_post_thumbnail_id( $product_info->ID ) );

					$products['products'][$i]['stats']['total']['sales']              = edd_get_download_sales_stats( $product_info->ID );
					$products['products'][$i]['stats']['total']['earnings']           = edd_get_download_earnings_stats( $product_info->ID );
					$products['products'][$i]['stats']['monthly_average']['sales']    = edd_get_average_monthly_download_sales( $product_info->ID );
					$products['products'][$i]['stats']['monthly_average']['earnings'] = edd_get_average_monthly_download_earnings( $product_info->ID );

					if ( edd_has_variable_prices( $product_info->ID ) ) {
						foreach ( edd_get_variable_prices( $product_info->ID ) as $price ) {
							$products['products'][$i]['pricing'][ sanitize_key( $price['name'] ) ] = $price['amount'];
						}
					} else {
						$products['products'][$i]['pricing']['amount'] = edd_get_download_price( $product_info->ID );
					}

					foreach ( edd_get_download_files( $product_info->ID ) as $file ) {
						$products['products'][$i]['files'][] = $file;
					}

					$products['products'][$i]['notes'] = edd_get_product_notes( $product_info->ID );
					$i++;
				}
			}
		} else {
			if ( get_post_type( $product ) == 'download' ) {
				$product_info = get_post( $product );

				$products['products'][0]['info']['id']                           = $product_info->ID;
				$products['products'][0]['info']['slug']                         = $product_info->post_name;
				$products['products'][0]['info']['title']                        = $product_info->post_title;
				$products['products'][0]['info']['create_date']                  = $product_info->post_date;
				$products['products'][0]['info']['modified_date']                = $product_info->post_modified;
				$products['products'][0]['info']['status']                       = $product_info->post_status;
				$products['products'][0]['info']['link']                         = html_entity_decode( $product_info->guid );
				$products['products'][0]['info']['content']                      = $product_info->post_content;
				$products['products'][0]['info']['thumbnail']                    = wp_get_attachment_url( get_post_thumbnail_id( $product_info->ID ) );

				$products['products'][0]['stats']['total']['sales']              = edd_get_download_sales_stats( $product_info->ID );
				$products['products'][0]['stats']['total']['earnings']           = edd_get_download_earnings_stats( $product_info->ID );
				$products['products'][0]['stats']['monthly_average']['sales']    = edd_get_average_monthly_download_sales( $product_info->ID );
				$products['products'][0]['stats']['monthly_average']['earnings'] = edd_get_average_monthly_download_earnings( $product_info->ID );

				if ( edd_has_variable_prices( $product_info->ID ) ) {
					foreach ( edd_get_variable_prices( $product_info->ID ) as $price ) {
						$products['products'][0]['pricing'][ sanitize_key( $price['name'] ) ] = $price['amount'];
					}
				} else {
					$products['products'][0]['pricing']['amount'] = edd_get_download_price( $product_info->ID );
				}

				foreach ( edd_get_download_files( $product_info->ID ) as $file ) {
					$products['products'][0]['files'][] = $file;
				}

				$products['products'][0]['notes'] = edd_get_product_notes( $product_info->ID );
			} else {
				$error['error'] = sprintf( __( 'Product %s not found!', 'edd' ), $product );
				return $error;
			}
		}

		return $products;
	}

	/**
	 * Process Get Stats API Request
	 *
	 * @access public
	 * @author Daniel J Griffiths
	 * @since 1.5
	 * @global object $wpdb Used to query the database using the WordPress
	 *   Database API
	 * @param array $args Arguments provided by API Request
	 */
	public function get_stats( $args = array() ) {
		$defaults = array(
			'type'      => null,
			'product'   => null,
			'date'      => null,
			'startdate' => null,
			'enddate'   => null
		);

		$args = wp_parse_args( $args, $defaults );

		$dates = $this->get_dates( $args );

		if ( $args['type'] == 'sales' ) {
			if ( $args['product'] == null ) {
				if ( $args['date'] == null ) {
					$sales = $this->get_default_sales_stats();
				} elseif( $args['date'] === 'range' ) {
					// Return sales for a date range

					// Ensure the end date is later than the start date
					if( $args['enddate'] < $args['startdate'] ) {
						$error['error'] = __( 'The end date must be later than the start date!', 'edd' );
					}

					// Ensure both the start and end date are specified
					if ( empty( $args['startdate'] ) || empty( $args['enddate'] ) ) {
						$error['error'] = __( 'Invalid or no date range specified!', 'edd' );
					}

					$total = 0;

					// Loop through the years
					$year = $dates['year'];
					while( $year <= $dates['year_end' ] ) :
						// Loop through the months
						$month = $dates['m_start'];

						while( $month <= $dates['m_end'] ) :
							// Loop through the days
							$day           = $month > $dates['m_start'] ? 1 : $dates['day_start'];
							$days_in_month = cal_days_in_month( CAL_GREGORIAN, $month, $year );

							while( $day <= $days_in_month ) :
								$sale_count = edd_get_sales_by_date( $day, $month, $year );
								$sales['sales'][ date( 'Ymd', strtotime( $year . '/' . $month . '/' . $day ) ) ] = $sale_count;
								$total += $sale_count;

								$day++;
							endwhile;

							$month++;
						endwhile;

						$year++;
					endwhile;

					$sales['totals'] = $total;
				} else {
					if( $args['date'] == 'this_quarter' || $args['date'] == 'last_quarter'  ) {
   						$sales_count = 0;

						// Loop through the months
						$month = $dates['m_start'];

						while( $month <= $dates['m_end'] ) :
							$sales_count += edd_get_sales_by_date( null, $month, $dates['year'] );
							$month++;
						endwhile;

						$sales['sales'][ $args['date'] ] = $sales_count;
   					} else {
						$sales['sales'][ $args['date'] ] = edd_get_sales_by_date( $dates['day'], $dates['m_start'], $dates['year'] );
   					}
				}
			} elseif ( $args['product'] == 'all' ) {
				$products = get_posts( array( 'post_type' => 'download', 'nopaging' => true ) );
				$i = 0;
				foreach ( $products as $product_info ) {
					$sales['sales'][$i] = array( $product_info->post_name => edd_get_download_sales_stats( $product_info->ID ) );
					$i++;
				}
			} else {
				if ( get_post_type( $args['product'] ) == 'download' ) {
					$product_info = get_post( $args['product'] );
					$sales['sales'][0] = array( $product_info->post_name => edd_get_download_sales_stats( $args['product'] ) );
				} else {
					$error['error'] = sprintf( __( 'Product %s not found!', 'edd' ), $args['product'] );
				}
			}

			if ( ! empty( $error ) )
				return $error;

			return $sales;
		} elseif ( $args['type'] == 'earnings' ) {
			if ( $args['product'] == null ) {
				if ( $args['date'] == null ) {
					$earnings = $this->get_default_earnings_stats();
				} elseif ( $args['date'] === 'range' ) {
					// Return sales for a date range

					// Ensure the end date is later than the start date
					if ( $args['enddate'] < $args['startdate'] ) {
						$error['error'] = __( 'The end date must be later than the start date!', 'edd' );
					}

					// Ensure both the start and end date are specified
					if ( empty( $args['startdate'] ) || empty( $args['enddate'] ) ) {
						$error['error'] = __( 'Invalid or no date range specified!', 'edd' );
					}

					$total = (float) 0.00;

					// Loop through the years
					$year = $dates['year'];
					while ( $year <= $dates['year_end' ] ) :
						// Loop through the months
						$month = $dates['m_start'];

						while( $month <= $dates['m_end'] ) :
							// Loop through the days
							$day           = $month > $dates['m_start'] ? 1 : $dates['day_start'];
							$days_in_month = cal_days_in_month( CAL_GREGORIAN, $month, $year );

							while( $day <= $days_in_month ) :
								$sale_count = edd_get_earnings_by_date( $day, $month, $year );
								$earnings['earnings'][ date( 'Ymd', strtotime( $year . '/' . $month . '/' . $day ) ) ] = $sale_count;
								$total += $sale_count;

								$day++;
							endwhile;

							$month++;
						endwhile;

						$year++;
					endwhile;

					$earnings['totals'] = $total;
				} else {
					if ( $args['date'] == 'this_quarter' || $args['date'] == 'last_quarter'  ) {
   						$earnings_count = (float) 0.00;

						// Loop through the months
						$month = $dates['m_start'];

						while ( $month <= $dates['m_end'] ) :
							$earnings_count += edd_get_earnings_by_date( null, $month, $dates['year'] );
							$month++;
						endwhile;

						$earnings['earnings'][ $args['date'] ] = $earnings_count;
   					} else {
						$earnings['earnings'][ $args['date'] ] = edd_get_earnings_by_date( $dates['day'], $dates['m_start'], $dates['year'] );
   					}
				}
			} elseif ( $args['product'] == 'all' ) {
				$products = get_posts( array( 'post_type' => 'download', 'nopaging' => true ) );

				$i = 0;
				foreach ( $products as $product_info ) {
					$earnings['earnings'][ $i ] = array( $product_info->post_name => edd_get_download_earnings_stats( $product_info->ID ) );
					$i++;
				}
			} else {
				if ( get_post_type( $args['product'] ) == 'download' ) {
					$product_info = get_post( $args['product'] );
					$earnings['earnings'][0] = array( $product_info->post_name => edd_get_download_earnings_stats( $args['product'] ) );
				} else {
					$error['error'] = sprintf( __( 'Product %s not found!', 'edd' ), $args['product'] );
				}
			}

			if ( ! empty( $error ) )
				return $error;

			return $earnings;
		} elseif ( $args['type'] == 'customers' ) {
			global $wpdb;

			$stats = array();

			$count = $wpdb->get_col( "SELECT COUNT(DISTINCT meta_value) FROM $wpdb->postmeta WHERE meta_key = '_edd_payment_user_email'" );

			$stats['customers']['total_customers'] = $count[0];

			return $stats;
		} elseif ( empty( $args['type'] ) ) {
			$stats = array();
			$stats = array_merge( $stats, $this->get_default_sales_stats() );
			$stats = array_merge ( $stats, $this->get_default_earnings_stats() );

			return array( 'stats' => $stats );
		}
	}

	/**
	 * Retrieves Recent Sales
	 *
	 * @access public
	 * @since  1.5
	 * @return array
	 */
	public function get_recent_sales() {
		$sales = array();

		$query = edd_get_payments( array( 'number' => $this->per_page(), 'page' => $this->get_paged(), 'status' => 'publish' ) );

		if ( $query ) {
			$i = 0;
			foreach ( $query as $payment ) {
				$payment_meta          = edd_get_payment_meta( $payment->ID );
				$user_info             = edd_get_payment_meta_user_info( $payment->ID );
				$cart_items            = edd_get_payment_meta_cart_details( $payment->ID );

				$sales['sales'][ $i ]['ID']       = $payment->ID;
				$sales['sales'][ $i ]['key']      = edd_get_payment_key( $payment->ID );
				$sales['sales'][ $i ]['subtotal'] = edd_get_payment_subtotal( $payment->ID );
				$sales['sales'][ $i ]['tax']      = edd_get_payment_tax( $payment->ID );
				$sales['sales'][ $i ]['fees']     = edd_get_payment_fees( $payment->ID );
				$sales['sales'][ $i ]['total']    = edd_get_payment_amount( $payment->ID );
				$sales['sales'][ $i ]['gateway']  = edd_get_payment_gateway( $payment->ID );
				$sales['sales'][ $i ]['email']    = edd_get_payment_user_email( $payment->ID );
				$sales['sales'][ $i ]['date']     = $payment->post_date;
				$sales['sales'][ $i ]['products'] = array();

				$c = 0;

				foreach ( $cart_items as $key => $item ) {
					$price_override = isset( $payment_meta['cart_details'] ) ? $item['price'] : null;
					$price          = edd_get_download_final_price( $item['id'], $user_info, $price_override );

					if ( isset( $cart_items[ $key ]['item_number'] ) ) {
						$price_name     = '';
						$price_options  = $cart_items[ $key ]['item_number']['options'];
						if ( isset( $price_options['price_id'] ) ) {
							$price_name = edd_get_price_option_name( $item['id'], $price_options['price_id'], $payment->ID );
						}
					}

					$sales['sales'][ $i ]['products'][ $c ]['name']       = get_the_title( $item['id'] );
					$sales['sales'][ $i ]['products'][ $c ]['price']      = $price;
					$sales['sales'][ $i ]['products'][ $c ]['price_name'] = $price_name;
					$c++;
				}

				$i++;
			}
		}
		return $sales;
	}

	/**
	 * Process Get Discounts API Request
	 *
	 * @access public
	 * @since 1.6
	 * @global object $wpdb Used to query the database using the WordPress
	 *   Database API
	 * @param int $discount Discount ID
	 * @return array $discounts Multidimensional array of the discounts
	 */
	public function get_discounts( $discount = null ) {

		if ( empty( $discount ) ) {

			global $wpdb;

			$paged     = $this->get_paged();
			$per_page  = $this->per_page();
			$discounts = edd_get_discounts( array( 'posts_per_page' => $per_page, 'paged' => $paged ) );
			$count     = 0;

			foreach ( $discounts as $discount ) {

				$discount_list['discounts'][$count]['ID']                    = $discount->ID;
				$discount_list['discounts'][$count]['name']                  = $discount->post_title;
				$discount_list['discounts'][$count]['code']                  = edd_get_discount_code( $discount->ID );
				$discount_list['discounts'][$count]['amount']                = edd_get_discount_amount( $discount->ID );
				$discount_list['discounts'][$count]['min_price']             = edd_get_discount_min_price( $discount->ID );
				$discount_list['discounts'][$count]['type']                  = edd_get_discount_type( $discount->ID );
				$discount_list['discounts'][$count]['uses']                  = edd_get_discount_uses( $discount->ID );
				$discount_list['discounts'][$count]['max_uses']              = edd_get_discount_max_uses( $discount->ID );
				$discount_list['discounts'][$count]['start_date']            = edd_get_discount_start_date( $discount->ID );
				$discount_list['discounts'][$count]['exp_date']              = edd_get_discount_expiration( $discount->ID );
				$discount_list['discounts'][$count]['status']                = $discount->post_status;
				$discount_list['discounts'][$count]['product_requirements']  = edd_get_discount_product_reqs( $discount->ID );
				$discount_list['discounts'][$count]['requirement_condition'] = edd_get_discount_product_condition( $discount->ID );
				$discount_list['discounts'][$count]['global_discount']       = edd_is_discount_not_global( $discount->ID );
				$discount_list['discounts'][$count]['single_use']            = edd_discount_is_single_use( $discount->ID );

				$count++;
			}

		} else {

			if ( is_numeric( $discount ) && get_post( $discount ) ) {

				$discount_list['discounts'][0]['ID']                         = $discount;
				$discount_list['discounts'][0]['name']                       = get_post_field( 'post_title', $discount );
				$discount_list['discounts'][0]['code']                       = edd_get_discount_code( $discount );
				$discount_list['discounts'][0]['amount']                     = edd_get_discount_amount( $discount );
				$discount_list['discounts'][0]['min_price']                  = edd_get_discount_min_price( $discount );
				$discount_list['discounts'][0]['type']                       = edd_get_discount_type( $discount );
				$discount_list['discounts'][0]['uses']                       = edd_get_discount_uses( $discount );
				$discount_list['discounts'][0]['max_uses']                   = edd_get_discount_max_uses( $discount );
				$discount_list['discounts'][0]['start_date']                 = edd_get_discount_start_date( $discount );
				$discount_list['discounts'][0]['exp_date']                   = edd_get_discount_expiration( $discount );
				$discount_list['discounts'][0]['status']                     = get_post_field( 'post_status', $discount );
				$discount_list['discounts'][0]['product_requirements']       = edd_get_discount_product_reqs( $discount );
				$discount_list['discounts'][0]['requirement_condition']      = edd_get_discount_product_condition( $discount );
				$discount_list['discounts'][0]['global_discount']            = edd_is_discount_not_global( $discount );
				$discount_list['discounts'][0]['single_use']                 = edd_discount_is_single_use( $discount );

			} else {

				$error['error'] = sprintf( __( 'Discount %s not found!', 'edd' ), $discount );
				return $error;

			}

		}

		return $discount_list;
	}


	/**
	 * Log each API request, if enabled
	 *
	 * @access private
	 * @since  1.5
	 * @global $edd_logs
	 * @global $wp_query
	 * @param array $data
	 * @return void
	 */
	private function log_request( $data = array() ) {
		if ( ! $this->log_requests )
			return;

		global $edd_logs, $wp_query;

		$query = array(
			'key'       => $wp_query->query_vars['key'],
			'query'     => isset( $wp_query->query_vars['query'] )     ? $wp_query->query_vars['query']     : null,
			'type'      => isset( $wp_query->query_vars['type'] )      ? $wp_query->query_vars['type']      : null,
			'product'   => isset( $wp_query->query_vars['product'] )   ? $wp_query->query_vars['product']   : null,
			'customer'  => isset( $wp_query->query_vars['customer'] )  ? $wp_query->query_vars['customer']  : null,
			'date'      => isset( $wp_query->query_vars['date'] )      ? $wp_query->query_vars['date']      : null,
			'startdate' => isset( $wp_query->query_vars['startdate'] ) ? $wp_query->query_vars['startdate'] : null,
			'enddate'   => isset( $wp_query->query_vars['enddate'] )   ? $wp_query->query_vars['enddate']   : null,
		);

		$log_data = array(
			'log_type'     => 'api_request',
			'post_excerpt' => http_build_query( $query ),
			'post_content' => ! empty( $data['error'] ) ? $data['error'] : '',
		);

		$log_meta = array(
			'request_ip' => edd_get_ip(),
			'user'       => $this->user_id,
			'key'        => $wp_query->query_vars['key']
		);

		$edd_logs->insert_log( $log_data, $log_meta );
	}


	/**
	 * Retrieve the output data
	 *
	 * @access public
	 * @since 1.5.2
	 * @return array
	 */
	public function get_output() {
		return $this->data;
	}


	/**
	 * Output Query in either JSON/XML. The query data is outputted as JSON
	 * by default
	 *
	 * @access public
	 * @author Daniel J Griffiths
	 * @since 1.5
	 * @global $wp_query
	 * @param array $data
	 * @return void
	 */
	public function output() {
		global $wp_query;

		$data = $this->data;

		$format = $this->get_output_format();

		do_action( 'edd_api_output_before', $data, $this, $format );

		switch( $format ) :

			case 'xml' :

				require_once EDD_PLUGIN_DIR . 'includes/libraries/array2xml.php';
				$xml = Array2XML::createXML( 'edd', $data );
				echo $xml->saveXML();

				break;

			case 'json' :

				header( 'Content-Type: application/json' );
				if ( ! empty( $this->pretty_print ) )
					echo json_encode( $data, $this->pretty_print );
				else
					echo json_encode( $data );

				break;


			default :

				// Allow other formats to be added via extensions
				do_action( 'edd_api_output_' . $format, $data, $this );

				break;

		endswitch;

		do_action( 'edd_api_output_after', $data, $this, $format );

		edd_die();
	}

	/**
	 * Modify User Profile
	 *
	 * Modifies the output of profile.php to add key generation/revocation
	 *
	 * @access public
	 * @author Daniel J Griffiths
	 * @since 1.5
	 * @global $edd_options Array of all the EDD Options
	 * @param object $user Current user info
	 * @return void
	 */
	function user_key_field( $user ) {
		global $edd_options;

		if ( ( isset( $edd_options['api_allow_user_keys'] ) || current_user_can( 'manage_shop_settings' ) ) && current_user_can( 'view_shop_reports' ) ) {
			$user = get_userdata( $user->ID );
			?>
			<table class="form-table">
				<tbody>
					<tr>
						<th>
							<label for="edd_set_api_key"><?php _e( 'Easy Digital Downloads API Keys', 'edd' ); ?></label>
						</th>
						<td>
							<?php if ( empty( $user->edd_user_public_key ) ) { ?>
							<input name="edd_set_api_key" type="checkbox" id="edd_set_api_key" value="0" />
							<span class="description"><?php _e( 'Generate API Key', 'edd' ); ?></span>
							<?php } else { ?>
								<strong><?php _e( 'Public key:', 'edd' ); ?>&nbsp;</strong><span id="publickey"><?php echo $user->edd_user_public_key; ?></span><br/>
								<strong><?php _e( 'Secret key:', 'edd' ); ?>&nbsp;</strong><span id="privatekey"><?php echo $user->edd_user_secret_key; ?></span><br/>
								<strong><?php _e( 'Token:', 'edd' ); ?>&nbsp;</strong><span id="token"><?php echo hash( 'md5', $user->edd_user_secret_key . $user->edd_user_public_key ); ?></span><br/>
								<input name="edd_set_api_key" type="checkbox" id="edd_set_api_key" value="0" />
								<span class="description"><?php _e( 'Revoke API Keys', 'edd' ); ?></span>
							<?php } ?>
						</td>
					</tr>
				</tbody>
			</table>
		<?php }
	}

	/**
	 * Generate and Save API key
	 *
	 * Generates the key requested by user_key_field and stores it in the database
	 *
	 * @access public
	 * @author Daniel J Griffiths
	 * @since 1.5
	 * @param int $user_id
	 * @return void
	 */
	public function update_key( $user_id ) {
		if ( current_user_can( 'edit_user', $user_id ) && isset( $_POST['edd_set_api_key'] ) ) {
			$user = get_userdata( $user_id );

			if ( empty( $user->edd_user_public_key ) ) {
				$public = hash( 'md5', $user->user_email . date( 'U' ) );
				update_user_meta( $user_id, 'edd_user_public_key', $public );
			} else {
				delete_user_meta( $user_id, 'edd_user_public_key' );
			}

			if ( empty( $user->edd_user_secret_key ) ) {
				$secret = hash( 'md5', $user->ID . date( 'U' ) );
				update_user_meta( $user_id, 'edd_user_secret_key', $secret );
			} else {
				delete_user_meta( $user_id, 'edd_user_secret_key' );
			}
		}
	}

	/**
	 * Generate the default sales stats returned by the 'stats' endpoint
	 *
	 * @access private
	 * @since 1.5.3
	 * @return array default sales statistics
	 */
	private function get_default_sales_stats() {
		// Default sales return
		$previous_month = date( 'n' ) == 1 ? 12 : date( 'n' ) - 1;
		$previous_year  = date( 'n' ) == 1 ? date( 'Y' ) - 1 : date( 'Y' );

		$sales['sales']['current_month'] = edd_get_sales_by_date( null, date( 'n' ), date( 'Y' ) );
		$sales['sales']['last_month']    = edd_get_sales_by_date( null, $previous_month, $previous_year );
		$sales['sales']['totals']        = edd_get_total_sales();

		return $sales;
	}

	/**
	 * Generate the default earnings stats returned by the 'stats' endpoint
	 *
	 * @access private
	 * @since 1.5.3
	 * @return array default eranings statistics
	 */
	private function get_default_earnings_stats() {
		// Default earnings return
		$previous_month = date( 'n' ) == 1 ? 12 : date( 'n' ) - 1;
		$previous_year  = date( 'n' ) == 1 ? date( 'Y' ) - 1 : date( 'Y' );

		$earnings['earnings']['current_month'] = edd_get_earnings_by_date( null, date( 'n' ), date( 'Y' ) );
		$earnings['earnings']['last_month']    = edd_get_earnings_by_date( null, $previous_month, $previous_year );
		$earnings['earnings']['totals']        = edd_get_total_earnings();

		return $earnings;
	}
}
