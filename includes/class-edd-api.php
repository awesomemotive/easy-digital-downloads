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
 * @copyright   Copyright (c) 2015, Pippin Williamson
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
	 * API Version
	 */
	const VERSION = '1.3';

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
	public $log_requests = true;

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
	 * Instance of EDD Stats class
	 *
	 * @var object
	 * @access private
	 * @since 1.7
	 */
	private $stats;

	/**
	 * Response data to return
	 *
	 * @var array
	 * @access private
	 * @since 1.5.2
	 */
	private $data = array();

	/**
	 *
	 * @var bool
	 * @access private
	 * @since 1.7
	 */
	private $override = true;

	/**
	 * Setup the EDD API
	 *
	 * @author Daniel J Griffiths
	 * @since 1.5
	 */
	public function __construct() {
		add_action( 'init',                     array( $this, 'add_endpoint'     ) );
		add_action( 'template_redirect',        array( $this, 'process_query'    ), -1 );
		add_filter( 'query_vars',               array( $this, 'query_vars'       ) );
		add_action( 'show_user_profile',        array( $this, 'user_key_field'   ) );
		add_action( 'edit_user_profile',        array( $this, 'user_key_field'   ) );
		add_action( 'personal_options_update',  array( $this, 'update_key'       ) );
		add_action( 'edit_user_profile_update', array( $this, 'update_key'       ) );
		add_action( 'edd_process_api_key',      array( $this, 'process_api_key'  ) );

		// Determine if JSON_PRETTY_PRINT is available
		$this->pretty_print = defined( 'JSON_PRETTY_PRINT' ) ? JSON_PRETTY_PRINT : null;

		// Allow API request logging to be turned off
		$this->log_requests = apply_filters( 'edd_api_log_requests', $this->log_requests );

		// Setup EDD_Stats instance
		$this->stats = new EDD_Payment_Stats;

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
	 * @return string[] $vars New query vars
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
		$vars[] = 'id';
		$vars[] = 'purchasekey';
		$vars[] = 'email';

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

		$this->override = false;

        // Make sure we have both user and api key
		if ( ! empty( $wp_query->query_vars['edd-api'] ) && ( $wp_query->query_vars['edd-api'] != 'products' || ! empty( $wp_query->query_vars['token'] ) ) ) {
			if ( empty( $wp_query->query_vars['token'] ) || empty( $wp_query->query_vars['key'] ) )
				$this->missing_auth();

			// Retrieve the user by public API key and ensure they exist
			if ( ! ( $user = $this->get_user( $wp_query->query_vars['key'] ) ) ) :
				$this->invalid_key();
			else :
				$token  = urldecode( $wp_query->query_vars['token'] );
				$secret = get_user_meta( $user, 'edd_user_secret_key', true );
				$public = urldecode( $wp_query->query_vars['key'] );

				if ( hash_equals( md5( $secret . $public ), $token ) )
					$this->is_valid_request = true;
				else
					$this->invalid_auth();
			endif;
		} elseif ( !empty( $wp_query->query_vars['edd-api'] ) && $wp_query->query_vars['edd-api'] == 'products' ) {
			$this->is_valid_request = true;
			$wp_query->set( 'key', 'public' );
		}
	}

	/**
	 * Retrieve the user ID based on the public key provided
	 *
	 * @access public
	 * @since 1.5.1
	 * @global object $wpdb Used to query the database using the WordPress
	 * Database API
	 *
	 * @param string $key Public Key
	 *
	 * @return bool if user ID is found, false otherwise
	 */
	public function get_user( $key = '' ) {
		global $wpdb, $wp_query;

		if( empty( $key ) )
			$key = urldecode( $wp_query->query_vars['key'] );

		if ( empty( $key ) ) {
			return false;
		}

		$user = get_transient( md5( 'edd_api_user_' . $key ) );

		if ( false === $user ) {
			$user = $wpdb->get_var( $wpdb->prepare( "SELECT user_id FROM $wpdb->usermeta WHERE meta_key = 'edd_user_public_key' AND meta_value = %s LIMIT 1", $key ) );
			set_transient( md5( 'edd_api_user_' . $key ) , $user, DAY_IN_SECONDS );
		}

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
	 * @access private
	 * @author Daniel J Griffiths
	 * @uses EDD_API::output()
	 * @since 1.5
	 */
	private function missing_auth() {
		$error = array();
		$error['error'] = __( 'You must specify both a token and API key!', 'edd' );

		$this->data = $error;
		$this->output( 401 );
	}

	/**
	 * Displays an authentication failed error if the user failed to provide valid
	 * credentials
	 *
	 * @access private
	 * @since  1.5
	 * @uses EDD_API::output()
	 * @return void
	 */
	private function invalid_auth() {
		$error = array();
		$error['error'] = __( 'Your request could not be authenticated!', 'edd' );

		$this->data = $error;
		$this->output( 401 );
	}

	/**
	 * Displays an invalid API key error if the API key provided couldn't be
	 * validated
	 *
	 * @access private
	 * @author Daniel J Griffiths
	 * @since 1.5
	 * @uses EDD_API::output()
	 * @return void
	 */
	private function invalid_key() {
		$error = array();
		$error['error'] = __( 'Invalid API key!', 'edd' );

		$this->data = $error;
		$this->output( 401 );
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

		if( ! defined( 'EDD_DOING_API' ) ) {
			define( 'EDD_DOING_API', true );
		}

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
	public function get_query_mode() {
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
		$error = array();
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
	public function get_paged() {
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
	public function per_page() {
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
	 * @since 1.5
	 *
	 * @return mixed|void
	 */
	public function get_output_format() {
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
	public function get_dates( $args = array() ) {
		$dates = array();

		$defaults = array(
			'type'      => '',
			'product'   => null,
			'date'      => null,
			'startdate' => null,
			'enddate'   => null
		);

		$args = wp_parse_args( $args, $defaults );

		$current_time = current_time( 'timestamp' );

		if ( 'range' === $args['date'] ) {
			$startdate          = strtotime( $args['startdate'] );
			$enddate            = strtotime( $args['enddate'] );
			$dates['day_start'] = date( 'd', $startdate );
			$dates['day_end']   = date( 'd', $enddate );
			$dates['m_start']   = date( 'n', $startdate );
			$dates['m_end']     = date( 'n', $enddate );
			$dates['year']      = date( 'Y', $startdate );
			$dates['year_end'] 	= date( 'Y', $enddate );
		} else {
			// Modify dates based on predefined ranges
			switch ( $args['date'] ) :

				case 'this_month' :
					$dates['day']       = null;
					$dates['m_start']   = date( 'n', $current_time );
					$dates['m_end']     = date( 'n', $current_time );
					$dates['year']      = date( 'Y', $current_time );
				break;

				case 'last_month' :
					$dates['day']     = null;
					$dates['m_start'] = date( 'n', $current_time ) == 1 ? 12 : date( 'n', $current_time ) - 1;
					$dates['m_end']   = $dates['m_start'];
					$dates['year']    = date( 'n', $current_time ) == 1 ? date( 'Y', $current_time ) - 1 : date( 'Y', $current_time );
				break;

				case 'today' :
					$dates['day']       = date( 'd', $current_time );
					$dates['m_start']   = date( 'n', $current_time );
					$dates['m_end']     = date( 'n', $current_time );
					$dates['year']      = date( 'Y', $current_time );
				break;

				case 'yesterday' :

					$year               = date( 'Y', $current_time );
					$month              = date( 'n', $current_time );
					$day                = date( 'd', $current_time );

					if ( $month == 1 && $day == 1 ) {

						$year -= 1;
						$month = 12;
						$day   = cal_days_in_month( CAL_GREGORIAN, $month, $year );

					} elseif ( $month > 1 && $day == 1 ) {

						$month -= 1;
						$day   = cal_days_in_month( CAL_GREGORIAN, $month, $year );

					} else {

						$day -= 1;

					}

					$dates['day']       = $day;
					$dates['m_start']   = $month;
					$dates['m_end']     = $month;
					$dates['year']      = $year;

				break;

				case 'this_quarter' :
					$month_now = date( 'n', $current_time );

					$dates['day']           = null;

					if ( $month_now <= 3 ) {

						$dates['m_start']   = 1;
						$dates['m_end']     = 3;
						$dates['year']      = date( 'Y', $current_time );

					} else if ( $month_now <= 6 ) {

						$dates['m_start']   = 4;
						$dates['m_end']     = 6;
						$dates['year']      = date( 'Y', $current_time );

					} else if ( $month_now <= 9 ) {

						$dates['m_start']   = 7;
						$dates['m_end']     = 9;
						$dates['year']      = date( 'Y', $current_time );

					} else {

						$dates['m_start']   = 10;
						$dates['m_end']     = 12;
						$dates['year']      = date( 'Y', $current_time );

					}
				break;

				case 'last_quarter' :
					$month_now = date( 'n', $current_time );

					$dates['day']           = null;

					if ( $month_now <= 3 ) {

						$dates['m_start']   = 10;
						$dates['m_end']     = 12;
						$dates['year']      = date( 'Y', $current_time ) - 1; // Previous year

					} else if ( $month_now <= 6 ) {

						$dates['m_start']   = 1;
						$dates['m_end']     = 3;
						$dates['year']      = date( 'Y', $current_time );

					} else if ( $month_now <= 9 ) {

						$dates['m_start']   = 4;
						$dates['m_end']     = 6;
						$dates['year']      = date( 'Y', $current_time );

					} else {

						$dates['m_start']   = 7;
						$dates['m_end']     = 9;
						$dates['year']      = date( 'Y', $current_time );

					}
				break;

				case 'this_year' :
					$dates['day']       = null;
					$dates['m_start']   = null;
					$dates['m_end']     = null;
					$dates['year']      = date( 'Y', $current_time );
				break;

				case 'last_year' :
					$dates['day']       = null;
					$dates['m_start']   = null;
					$dates['m_end']     = null;
					$dates['year']      = date( 'Y', $current_time ) - 1;
				break;

			endswitch;
		}

		/**
		 * Returns the filters for the dates used to retreive earnings/sales
		 *
		 * @since 1.5.1
		 * @param object $dates The dates used for retreiving earnings/sales
		 */

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

		$customers = array();
		$error = array();
		if( ! user_can( $this->user_id, 'view_shop_sensitive_data' ) && ! $this->override ) {
			return $customers;
		}

		global $wpdb;

		$paged    = $this->get_paged();
		$per_page = $this->per_page();
		$offset   = $per_page * ( $paged - 1 );

		if( is_numeric( $customer ) ) {
			$field = 'id';
		} else {
			$field = 'email';
		}

		$customer_query = EDD()->customers->get_customers( array( 'number' => $per_page, 'offset' => $offset, $field => $customer ) );
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

				if ( ! empty( $customer_obj->user_id ) ) {

					$user_data = get_userdata( $customer_obj->user_id );

					// Customer with registered account

					// id is going to get deprecated in the future, user user_id or customer_id instead
					$customers['customers'][$customer_count]['info']['id']           = $customer_obj->user_id;
					$customers['customers'][$customer_count]['info']['user_id']      = $customer_obj->user_id;
					$customers['customers'][$customer_count]['info']['username']     = $user_data->user_login;
					$customers['customers'][$customer_count]['info']['display_name'] = $user_data->display_name;

				}

				$customers['customers'][$customer_count]['stats']['total_purchases'] = $customer_obj->purchase_count;
				$customers['customers'][$customer_count]['stats']['total_spent']     = $customer_obj->purchase_value;
				$customers['customers'][$customer_count]['stats']['total_downloads'] = edd_count_file_downloads_of_user( $customer_obj->email );

				$customer_count++;

			}

		} elseif( $customer ) {

			$error['error'] = sprintf( __( 'Customer %s not found!', 'edd' ), $customer );
			return $error;

		} else {

			$error['error'] = __( 'No customers found!', 'edd' );
			return $error;

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
		$error = array();

		if ( $product == null ) {
			$products['products'] = array();

			$product_list = get_posts( array(
				'post_type'        => 'download',
				'posts_per_page'   => $this->per_page(),
				'suppress_filters' => true,
				'paged'            => $this->get_paged()
			) );

			if ( $product_list ) {
				$i = 0;
				foreach ( $product_list as $product_info ) {
					$products['products'][$i] = $this->get_product_data( $product_info );
					$i++;
				}
			}
		} else {
			if ( get_post_type( $product ) == 'download' ) {
				$product_info = get_post( $product );

				$products['products'][0] = $this->get_product_data( $product_info );

			} else {
				$error['error'] = sprintf( __( 'Product %s not found!', 'edd' ), $product );
				return $error;
			}
		}

		return $products;
	}

	/**
	 * Given a download post object, generate the data for the API output
	 *
	 * @since  2.3.9
	 * @param  object $product_info The Download Post Object
	 * @return array                Array of post data to return back in the API
	 */
	private function get_product_data( $product_info ) {

		$product = array();

		$product['info']['id']                           = $product_info->ID;
		$product['info']['slug']                         = $product_info->post_name;
		$product['info']['title']                        = $product_info->post_title;
		$product['info']['create_date']                  = $product_info->post_date;
		$product['info']['modified_date']                = $product_info->post_modified;
		$product['info']['status']                       = $product_info->post_status;
		$product['info']['link']                         = html_entity_decode( $product_info->guid );
		$product['info']['content']                      = $product_info->post_content;
		$product['info']['thumbnail']                    = wp_get_attachment_url( get_post_thumbnail_id( $product_info->ID ) );
		$product['info']['category']                     = get_the_terms( $product_info, 'download_category' );
		$product['info']['tags']                         = get_the_terms( $product_info, 'download_tag' );

		if( user_can( $this->user_id, 'view_shop_reports' ) || $this->override ) {
			$product['stats']['total']['sales']              = edd_get_download_sales_stats( $product_info->ID );
			$product['stats']['total']['earnings']           = edd_get_download_earnings_stats( $product_info->ID );
			$product['stats']['monthly_average']['sales']    = edd_get_average_monthly_download_sales( $product_info->ID );
			$product['stats']['monthly_average']['earnings'] = edd_get_average_monthly_download_earnings( $product_info->ID );
		}

		if ( edd_has_variable_prices( $product_info->ID ) ) {
			foreach ( edd_get_variable_prices( $product_info->ID ) as $price ) {
				$product['pricing'][ sanitize_key( $price['name'] ) ] = $price['amount'];
			}
		} else {
			$product['pricing']['amount'] = edd_get_download_price( $product_info->ID );
		}

		if( user_can( $this->user_id, 'view_shop_sensitive_data' ) || $this->override ) {
			foreach ( edd_get_download_files( $product_info->ID ) as $file ) {
				$product['files'][] = $file;
			}
			$product['notes'] = edd_get_product_notes( $product_info->ID );
		}

		return apply_filters( 'edd_api_products_product', $product );

	}

	/**
	 * Process Get Stats API Request
	 *
	 * @author Daniel J Griffiths
	 * @since 1.5
	 *
	 * @global object $wpdb Used to query the database using the WordPress
	 *
	 * @param array $args Arguments provided by API Request
	 *
	 * @return array
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

		$stats    = array();
		$earnings = array(
			'earnings' => array()
		);
		$sales    = array(
			'sales' => array()
		);
		$error    = array();

		if( ! user_can( $this->user_id, 'view_shop_reports' ) && ! $this->override ) {
			return $stats;
		}

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
					$y = $dates['year'];
					while( $y <= $dates['year_end'] ) :

						if( $dates['year'] == $dates['year_end'] ) {
							$month_start = $dates['m_start'];
							$month_end   = $dates['m_end'];
						} elseif( $y == $dates['year'] && $dates['year_end'] > $dates['year'] ) {
							$month_start = $dates['m_start'];
							$month_end   = 12;
						} elseif( $y == $dates['year_end'] ) {
							$month_start = 1;
							$month_end   = $dates['m_end'];
						} else {
							$month_start = 1;
							$month_end   = 12;
						}

						$i = $month_start;
						while ( $i <= $month_end ) :

							if( $i == $dates['m_start'] ) {
								$d = $dates['day_start'];
							} else {
								$d = 1;
							}

							if( $i == $dates['m_end'] ) {
								$num_of_days = $dates['day_end'];
							} else {
								$num_of_days 	= cal_days_in_month( CAL_GREGORIAN, $i, $y );
							}

							while ( $d <= $num_of_days ) :
								$sale_count = edd_get_sales_by_date( $d, $i, $y );
								$date_key   = date( 'Ymd', strtotime( $y . '/' . $i . '/' . $d ) );
								if ( ! isset( $sales['sales'][ $date_key ] ) ) {
									$sales['sales'][ $date_key ] = 0;
								}
								$sales['sales'][ $date_key ] += $sale_count;
								$total += $sale_count;
								$d++;
							endwhile;
							$i++;
						endwhile;

						$y++;
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
					$y = $dates['year'];
					if ( ! isset( $earnings['earnings'] ) ) {
						$earnings['earnings'] = array();
					}
					while( $y <= $dates['year_end'] ) :

						if( $dates['year'] == $dates['year_end'] ) {
							$month_start = $dates['m_start'];
							$month_end   = $dates['m_end'];
						} elseif( $y == $dates['year'] && $dates['year_end'] > $dates['year'] ) {
							$month_start = $dates['m_start'];
							$month_end   = 12;
						} elseif( $y == $dates['year_end'] ) {
							$month_start = 1;
							$month_end   = $dates['m_end'];
						} else {
							$month_start = 1;
							$month_end   = 12;
						}

						$i = $month_start;
						while ( $i <= $month_end ) :

							if( $i == $dates['m_start'] )
								$d = $dates['day_start'];
							else
								$d = 1;

							if( $i == $dates['m_end'] ) {
								$num_of_days = $dates['day_end'];
							} else {
								$num_of_days = cal_days_in_month( CAL_GREGORIAN, $i, $y );
							}

							while ( $d <= $num_of_days ) :
								$earnings_stat = edd_get_earnings_by_date( $d, $i, $y );
								$date_key = date( 'Ymd', strtotime( $y . '/' . $i . '/' . $d ) );
								if ( ! isset( $earnings['earnings'][ $date_key ] ) ) {
									$earnings['earnings'][ $date_key ] = 0;
								}
								$earnings['earnings'][ $date_key ] += $earnings_stat;
								$total += $earnings_stat;
								$d++;
							endwhile;

							$i++;
						endwhile;

						$y++;
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
		global $wp_query;

		$sales = array();

		if( ! user_can( $this->user_id, 'view_shop_reports' ) && ! $this->override ) {
			return $sales;
		}

		if( isset( $wp_query->query_vars['id'] ) ) {
			$query   = array();
			$query[] = edd_get_payment_by( 'id', $wp_query->query_vars['id'] );
		} elseif( isset( $wp_query->query_vars['purchasekey'] ) ) {
			$query   = array();
			$query[] = edd_get_payment_by( 'key', $wp_query->query_vars['purchasekey'] );
		} elseif( isset( $wp_query->query_vars['email'] ) ) {
			$query = edd_get_payments( array( 'meta_key' => '_edd_payment_user_email', 'meta_value' => $wp_query->query_vars['email'], 'number' => $this->per_page(), 'page' => $this->get_paged(), 'status' => 'publish' ) );
		} else {
			$query = edd_get_payments( array( 'number' => $this->per_page(), 'page' => $this->get_paged(), 'status' => 'publish' ) );
		}

		if ( $query ) {
			$i = 0;
			foreach ( $query as $payment ) {
				$payment_meta = edd_get_payment_meta( $payment->ID );
				$user_info    = edd_get_payment_meta_user_info( $payment->ID );
				$cart_items   = edd_get_payment_meta_cart_details( $payment->ID );

				$sales['sales'][ $i ]['ID']             = edd_get_payment_number( $payment->ID );
				$sales['sales'][ $i ]['transaction_id'] = edd_get_payment_transaction_id( $payment->ID );
				$sales['sales'][ $i ]['key']            = edd_get_payment_key( $payment->ID );
				$sales['sales'][ $i ]['discount']       = isset( $user_info['discount'] ) && $user_info['discount'] != 'none' ? explode( ',', $user_info['discount'] ) : array();
				$sales['sales'][ $i ]['subtotal']       = edd_get_payment_subtotal( $payment->ID );
				$sales['sales'][ $i ]['tax']            = edd_get_payment_tax( $payment->ID );
				$sales['sales'][ $i ]['fees']           = edd_get_payment_fees( $payment->ID );
				$sales['sales'][ $i ]['total']          = edd_get_payment_amount( $payment->ID );
				$sales['sales'][ $i ]['gateway']        = edd_get_payment_gateway( $payment->ID );
				$sales['sales'][ $i ]['email']          = edd_get_payment_user_email( $payment->ID );
				$sales['sales'][ $i ]['date']           = $payment->post_date;
				$sales['sales'][ $i ]['products']       = array();

				$c = 0;

				foreach ( $cart_items as $key => $item ) {

					$item_id  = isset( $item['id']    ) ? $item['id']    : $item;
					$price    = isset( $item['price'] ) ? $item['price'] : false;
					$price_id = isset( $item['item_number']['options']['price_id'] ) ? $item['item_number']['options']['price_id'] : null;
					$quantity = isset( $item['quantity'] ) && $item['quantity'] > 0 ? $item['quantity'] : 1;

					if( ! $price ) {
						// This function is only used on payments with near 1.0 cart data structure
						$price = edd_get_download_final_price( $item_id, $user_info, null );
					}

					$price_name = '';
					if ( isset( $item['item_number'] ) && isset( $item['item_number']['options'] ) ) {
						$price_options  = $item['item_number']['options'];
						if ( isset( $price_options['price_id'] ) ) {
							$price_name = edd_get_price_option_name( $item['id'], $price_options['price_id'], $payment->ID );
						}
					}

					$sales['sales'][ $i ]['products'][ $c ]['quantity']   = $quantity;
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

		$discount_list = array();

		if( ! user_can( $this->user_id, 'manage_shop_discounts' ) && ! $this->override ) {
			return $discount_list;
		}
		$error = array();

		if ( empty( $discount ) ) {

			global $wpdb;

			$paged     = $this->get_paged();
			$per_page  = $this->per_page();
			$discounts = edd_get_discounts( array( 'posts_per_page' => $per_page, 'paged' => $paged ) );
			$count     = 0;

			if ( empty( $discounts ) ) {
				$error['error'] = __( 'No discounts found!', 'edd' );
				return $error;
			}

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
			'edd-api'     => $wp_query->query_vars['edd-api'],
			'key'         => $wp_query->query_vars['key'],
			'token'       => $wp_query->query_vars['token'],
			'query'       => isset( $wp_query->query_vars['query'] )       ? $wp_query->query_vars['query']       : null,
			'type'        => isset( $wp_query->query_vars['type'] )        ? $wp_query->query_vars['type']        : null,
			'product'     => isset( $wp_query->query_vars['product'] )     ? $wp_query->query_vars['product']     : null,
			'customer'    => isset( $wp_query->query_vars['customer'] )    ? $wp_query->query_vars['customer']    : null,
			'date'        => isset( $wp_query->query_vars['date'] )        ? $wp_query->query_vars['date']        : null,
			'startdate'   => isset( $wp_query->query_vars['startdate'] )   ? $wp_query->query_vars['startdate']   : null,
			'enddate'     => isset( $wp_query->query_vars['enddate'] )     ? $wp_query->query_vars['enddate']     : null,
			'id'          => isset( $wp_query->query_vars['id'] )          ? $wp_query->query_vars['id']          : null,
			'purchasekey' => isset( $wp_query->query_vars['purchasekey'] ) ? $wp_query->query_vars['purchasekey'] : null,
			'email'       => isset( $wp_query->query_vars['email'] )       ? $wp_query->query_vars['email']       : null,
		);

		$log_data = array(
			'log_type'     => 'api_request',
			'post_excerpt' => http_build_query( $query ),
			'post_content' => ! empty( $data['error'] ) ? $data['error'] : '',
		);

		$log_meta = array(
			'request_ip' => edd_get_ip(),
			'user'       => $this->user_id,
			'key'        => $wp_query->query_vars['key'],
			'token'      => $wp_query->query_vars['token']
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
	 * @author Daniel J Griffiths
	 * @since 1.5
	 * @global $wp_query
	 *
	 * @param int $status_code
	 */
	public function output( $status_code = 200 ) {
		global $wp_query;

		$format = $this->get_output_format();

		status_header( $status_code );

		do_action( 'edd_api_output_before', $this->data, $this, $format );

		switch ( $format ) :

			case 'xml' :

				require_once EDD_PLUGIN_DIR . 'includes/libraries/array2xml.php';
				$xml = Array2XML::createXML( 'edd', $this->data );
				echo $xml->saveXML();

				break;

			case 'json' :

				header( 'Content-Type: application/json' );
				if ( ! empty( $this->pretty_print ) )
					echo json_encode( $this->data, $this->pretty_print );
				else
					echo json_encode( $this->data );

				break;


			default :

				// Allow other formats to be added via extensions
				do_action( 'edd_api_output_' . $format, $this->data, $this );

				break;

		endswitch;

		do_action( 'edd_api_output_after', $this->data, $this, $format );

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
	 * @param object $user Current user info
	 * @return void
	 */
	function user_key_field( $user ) {
		if ( ( edd_get_option( 'api_allow_user_keys', false ) || current_user_can( 'manage_shop_settings' ) ) && current_user_can( 'edit_user', $user->ID ) ) {
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
								<strong><?php _e( 'Token:', 'edd' ); ?>&nbsp;</strong><span id="token"><?php echo $this->get_token( $user->ID ); ?></span><br/>
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
	 * Process an API key generation/revocation
	 *
	 * @access public
	 * @since 2.0.0
	 * @param array $args
	 * @return void
	 */
	public function process_api_key( $args ) {

		if( ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'edd-api-nonce' ) ) {

			wp_die( __( 'Nonce verification failed', 'edd' ), __( 'Error', 'edd' ), array( 'response' => 403 ) );

		}

		if( is_numeric( $args['user_id'] ) ) {
			$user_id    = isset( $args['user_id'] ) ? absint( $args['user_id'] ) : get_current_user_id();
		} else {
			$userdata   = get_user_by( 'login', $args['user_id'] );
			$user_id    = $userdata->ID;
		}
		$process    = isset( $args['edd_api_process'] ) ? strtolower( $args['edd_api_process'] ) : false;

		if( $user_id == get_current_user_id() && ! edd_get_option( 'allow_user_api_keys' ) && ! current_user_can( 'manage_shop_settings' ) ) {
			wp_die( sprintf( __( 'You do not have permission to %s API keys for this user', 'edd' ), $process ), __( 'Error', 'edd' ), array( 'response' => 403 ) );
		} elseif( ! current_user_can( 'manage_shop_settings' ) ) {
			wp_die( sprintf( __( 'You do not have permission to %s API keys for this user', 'edd' ), $process ), __( 'Error', 'edd' ), array( 'response' => 403 ) );
		}

		switch( $process ) {
			case 'generate':
				if( $this->generate_api_key( $user_id ) ) {
					delete_transient( 'edd-total-api-keys' );
					wp_redirect( add_query_arg( 'edd-message', 'api-key-generated', 'edit.php?post_type=download&page=edd-tools&tab=api_keys' ) ); exit();
				} else {
					wp_redirect( add_query_arg( 'edd-message', 'api-key-failed', 'edit.php?post_type=download&page=edd-tools&tab=api_keys' ) ); exit();
				}
				break;
			case 'regenerate':
				$this->generate_api_key( $user_id, true );
				delete_transient( 'edd-total-api-keys' );
				wp_redirect( add_query_arg( 'edd-message', 'api-key-regenerated', 'edit.php?post_type=download&page=edd-tools&tab=api_keys' ) ); exit();
				break;
			case 'revoke':
				$this->revoke_api_key( $user_id );
				delete_transient( 'edd-total-api-keys' );
				wp_redirect( add_query_arg( 'edd-message', 'api-key-revoked', 'edit.php?post_type=download&page=edd-tools&tab=api_keys' ) ); exit();
				break;
			default;
				break;
		}
	}

	/**
	 * Generate new API keys for a user
	 *
	 * @access public
	 * @since 2.0.0
	 * @param int $user_id User ID the key is being generated for
	 * @param boolean $regenerate Regenerate the key for the user
	 * @return boolean True if (re)generated succesfully, false otherwise.
	 */
	public function generate_api_key( $user_id = 0, $regenerate = false ) {

		if( empty( $user_id ) ) {
			return false;
		}

		$user = get_userdata( $user_id );

		if( ! $user ) {
			return false;
		}

		if ( empty( $user->edd_user_public_key ) ) {
			update_user_meta( $user_id, 'edd_user_public_key', $this->generate_public_key( $user->user_email ) );
			update_user_meta( $user_id, 'edd_user_secret_key', $this->generate_private_key( $user->ID ) );
		} elseif( $regenerate == true ) {
			$this->revoke_api_key( $user->ID );
			update_user_meta( $user_id, 'edd_user_public_key', $this->generate_public_key( $user->user_email ) );
			update_user_meta( $user_id, 'edd_user_secret_key', $this->generate_private_key( $user->ID ) );
		} else {
			return false;
		}

		return true;
	}

	/**
	 * Revoke a users API keys
	 *
	 * @access public
	 * @since 2.0.0
	 * @param int $user_id User ID of user to revoke key for
	 * @return string
	 */
	public function revoke_api_key( $user_id = 0 ) {

		if( empty( $user_id ) ) {
			return false;
		}

		$user = get_userdata( $user_id );

		if( ! $user ) {
			return false;
		}

		if ( ! empty( $user->edd_user_public_key ) ) {
			delete_transient( md5( 'edd_api_user_' . $user->edd_user_public_key ) );
			delete_user_meta( $user_id, 'edd_user_public_key' );
			delete_user_meta( $user_id, 'edd_user_secret_key' );
		} else {
			return false;
		}

		return true;
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
				update_user_meta( $user_id, 'edd_user_public_key', $this->generate_public_key( $user->user_email ) );
				update_user_meta( $user_id, 'edd_user_secret_key', $this->generate_private_key( $user->ID ) );
			} else {
				$this->revoke_api_key( $user_id );
			}
		}
	}

	/**
	 * Generate the public key for a user
	 *
	 * @access private
	 * @since 1.9.9
	 * @param string $user_email
	 * @return string
	 */
	private function generate_public_key( $user_email = '' ) {
		$auth_key = defined( 'AUTH_KEY' ) ? AUTH_KEY : '';
		$public   = hash( 'md5', $user_email . $auth_key . date( 'U' ) );
		return $public;
	}

	/**
	 * Generate the secret key for a user
	 *
	 * @access private
	 * @since 1.9.9
	 * @param int $user_id
	 * @return string
	 */
	private function generate_private_key( $user_id = 0 ) {
		$auth_key = defined( 'AUTH_KEY' ) ? AUTH_KEY : '';
		$secret   = hash( 'md5', $user_id . $auth_key . date( 'U' ) );
		return $secret;
	}

	/**
	 * Retrieve the user's token
	 *
	 * @access private
	 * @since 1.9.9
	 * @param int $user_id
	 * @return string
	 */
	private function get_token( $user_id = 0 ) {
		$user = get_userdata( $user_id );
		return hash( 'md5', $user->edd_user_secret_key . $user->edd_user_public_key );
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
		$sales = array();
		$sales['sales']['today']         = $this->stats->get_sales( 0, 'today' );
		$sales['sales']['current_month'] = $this->stats->get_sales( 0, 'this_month' );
		$sales['sales']['last_month']    = $this->stats->get_sales( 0, 'last_month' );
		$sales['sales']['totals']        = edd_get_total_sales();

		return $sales;
	}

	/**
	 * Generate the default earnings stats returned by the 'stats' endpoint
	 *
	 * @access private
	 * @since 1.5.3
	 * @return array default earnings statistics
	 */
	private function get_default_earnings_stats() {

		// Default earnings return
		$earnings = array();
		$earnings['earnings']['today']         = $this->stats->get_earnings( 0, 'today' );
		$earnings['earnings']['current_month'] = $this->stats->get_earnings( 0, 'this_month' );
		$earnings['earnings']['last_month']    = $this->stats->get_earnings( 0, 'last_month' );
		$earnings['earnings']['totals']        = edd_get_total_earnings();

		return $earnings;
	}
}
