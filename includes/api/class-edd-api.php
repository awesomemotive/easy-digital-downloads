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
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.5
 * @since       3.0.4 Refactored to use the new stats API, returns same formatting as 2.x API.
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

use EDD\Stats;
use EDD\Reports;

/**
 * EDD_API Class
 *
 * Renders API returns as a JSON/XML array
 *
 * @since  1.5
 */
class EDD_API {

	/**
	 * Latest API Version
	 */
	const VERSION = 2;

	/**
	 * Pretty Print?
	 *
	 * @var bool
	 * @access private
	 * @since  1.5
	 */
	private $pretty_print = false;

	/**
	 * Log API requests?
	 *
	 * @var bool
	 * @access private
	 * @since  1.5
	 */
	public $log_requests = true;

	/**
	 * Is this a valid request?
	 *
	 * @var bool
	 * @access private
	 * @since  1.5
	 */
	private $is_valid_request = false;

	/**
	 * User ID Performing the API Request
	 *
	 * @var int
	 * @access private
	 * @since  1.5.1
	 */
	public $user_id = 0;

	/**
	 * Instance of EDD Stats class
	 *
	 * @var object
	 * @access private
	 * @since  1.7
	 */
	private $stats;

	/**
	 * Response data to return
	 *
	 * @var array
	 * @access private
	 * @since  1.5.2
	 */
	private $data = array();

	/**
	 *
	 * @var bool
	 * @access private
	 * @since  1.7
	 */
	public $override = true;

	/**
	 * Version of the API queried
	 *
	 * @var string
	 * @since 2.4
	 */
	private $queried_version;

	/**
	 * All versions of the API
	 *
	 * @var array
	 * @since 2.4
	 */
	protected $versions = array();

	/**
	 * Queried endpoint
	 *
	 * @var string
	 * @since 2.4
	 */
	private $endpoint;

	/**
	 * Endpoints routes
	 *
	 * @var object
	 * @since 2.4
	 */
	private $routes;

	/**
	 * Setup the EDD API
	 *
	 * @since  1.5
	 */
	public function __construct() {

		$this->versions = array(
			'v1' => 'EDD_API_V1',
			'v2' => 'EDD_API_V2',
		);

		foreach ( $this->get_versions() as $version => $class ) {
			require_once EDD_PLUGIN_DIR . 'includes/api/class-edd-api-' . $version . '.php';
		}

		add_action( 'init', array( $this, 'add_endpoint' ) );
		add_action( 'wp', array( $this, 'process_query' ), - 1 );
		add_filter( 'query_vars', array( $this, 'query_vars' ) );
		add_action( 'edd_process_api_key', array( $this, 'process_api_key' ) );

		// Setup a backwards compatibility check for user API Keys.
		add_filter( 'get_user_metadata', array( $this, 'api_key_backwards_copmat' ), 10, 4 );

		// Determine if JSON_PRETTY_PRINT is available.
		$this->pretty_print = defined( 'JSON_PRETTY_PRINT' ) ? JSON_PRETTY_PRINT : null;

		// Setup EDD_Stats instance.
		$this->stats = new EDD_Payment_Stats();
	}

	/**
	 * Registers a new rewrite endpoint for accessing the API
	 *
	 * @param array $rewrite_rules WordPress Rewrite Rules.
	 *
	 * @since  1.5
	 */
	public function add_endpoint( $rewrite_rules ) {
		add_rewrite_endpoint( 'edd-api', EP_ALL );
	}

	/**
	 * Registers query vars for API access
	 *
	 * @since  1.5
	 *
	 * @param array $vars Query vars.
	 *
	 * @return string[] $vars New query vars
	 */
	public function query_vars( $vars ) {

		$vars[] = 'token';
		$vars[] = 'key';
		$vars[] = 'query';
		$vars[] = 'type';
		$vars[] = 'product';
		$vars[] = 'category';
		$vars[] = 'tag';
		$vars[] = 'term_relation';
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
		$vars[] = 'info';
		$vars[] = 'include_tax';

		return $vars;
	}

	/**
	 * Retrieve the API versions
	 *
	 * @since 2.4
	 * @return array
	 */
	public function get_versions() {
		return $this->versions;
	}

	/**
	 * Retrieve the API version that was queried
	 *
	 * @since 2.4
	 * @return string
	 */
	public function get_queried_version() {
		return $this->queried_version;
	}

	/**
	 * Retrieves the default version of the API to use
	 *
	 * @access private
	 * @since  2.4
	 * @return string
	 */
	public function get_default_version() {

		$version = get_option( 'edd_default_api_version' );

		if ( defined( 'EDD_API_VERSION' ) ) {
			$version = EDD_API_VERSION;
		} elseif ( ! $version ) {
			$version = 'v1';
		}

		return $version;
	}

	/**
	 * Sets the version of the API that was queried.
	 *
	 * Falls back to the default version if no version is specified
	 *
	 * @access private
	 * @since  2.4
	 */
	private function set_queried_version() {

		global $wp_query;

		$version = $wp_query->query_vars['edd-api'];

		if ( strpos( $version, '/' ) ) {

			$version = explode( '/', $version );
			$version = strtolower( $version[0] );

			$wp_query->query_vars['edd-api'] = str_replace( $version . '/', '', $wp_query->query_vars['edd-api'] );

			if ( array_key_exists( $version, $this->versions ) ) {

				$this->queried_version = $version;

			} else {

				$this->is_valid_request = false;
				$this->invalid_version();
			}
		} else {

			$this->queried_version = $this->get_default_version();

		}
	}

	/**
	 * Validate the API request
	 *
	 * Checks for the user's public key and token against the secret key
	 *
	 * @access private
	 * @global object $wp_query WordPress Query
	 * @uses   EDD_API::get_user()
	 * @uses   EDD_API::invalid_key()
	 * @uses   EDD_API::invalid_auth()
	 * @since  1.5
	 */
	private function validate_request() {
		global $wp_query;

		$this->override = false;

		// Make sure we have both user and api key.
		if ( ! empty( $wp_query->query_vars['edd-api'] ) && ( ! $this->is_public_query() || ! empty( $wp_query->query_vars['token'] ) ) ) {

			if ( empty( $wp_query->query_vars['token'] ) || empty( $wp_query->query_vars['key'] ) ) {
				$this->missing_auth();
			}

			// Auth was provided, include the upgrade routine so we can use the fallback api checks.
			require_once EDD_PLUGIN_DIR . 'includes/admin/upgrades/upgrade-functions.php';

			// Retrieve the user by public API key and ensure they exist.
			if ( ! ( $user = $this->get_user( $wp_query->query_vars['key'] ) ) ) {
				$this->invalid_key();
			} else {

				$token  = urldecode( $wp_query->query_vars['token'] );
				$secret = $this->get_user_secret_key( $user );
				$public = urldecode( $wp_query->query_vars['key'] );

				// Verify that if user has secret key or not.
				if ( ! $secret ) {
					$this->invalid_auth();
				}

				$valid = $this->check_keys( $secret, $public, $token );
				if ( $valid ) {
					$this->is_valid_request = true;
				} else {
					$this->invalid_auth();
				}
			}
		} elseif ( ! empty( $wp_query->query_vars['edd-api'] ) && $this->is_public_query() ) {
			$this->is_valid_request = true;
			$wp_query->set( 'key', 'public' );
		}
	}

	/**
	 * Return whether this is a public query.
	 *
	 * @access private
	 * @global object $wp_query WordPress Query
	 * @since  2.6
	 * @return boolean
	 */
	private function is_public_query() {
		global $wp_query;

		$public_modes = apply_filters(
			'edd_api_public_query_modes',
			array(
				'products',
			)
		);

		return in_array( $wp_query->query_vars['edd-api'], $public_modes, true );
	}

	/**
	 * Retrieve the user ID based on the public key provided
	 *
	 * @since 1.5.1
	 * @global object $wpdb Used to query the database using the WordPress
	 *                      Database API
	 *
	 * @param string $key  Public Key.
	 *
	 * @return bool if user ID is found, false otherwise
	 */
	public function get_user( $key = '' ) {
		global $wpdb, $wp_query;

		if ( empty( $key ) ) {
			$key = isset( $wp_query->query_vars['key'] ) ?
				urldecode( $wp_query->query_vars['key'] ) :
				false;
		}

		if ( empty( $key ) ) {
			return false;
		}

		$user = get_transient( md5( 'edd_api_user_' . $key ) );

		if ( false === $user ) {
			$user = $wpdb->get_var( $wpdb->prepare( "SELECT user_id FROM $wpdb->usermeta WHERE meta_key = %s LIMIT 1", $key ) );
			set_transient( md5( 'edd_api_user_' . $key ), $user, DAY_IN_SECONDS );
		}

		if ( ! is_null( $user ) ) {
			$this->user_id = $user;

			return $user;
		}

		return false;
	}

	/**
	 * Get a user's public key.
	 *
	 * @param  int $user_id User ID.
	 *
	 * @return string
	 */
	public function get_user_public_key( $user_id = 0 ) {
		global $wpdb;

		if ( empty( $user_id ) ) {
			return '';
		}

		$cache_key       = md5( 'edd_api_user_public_key' . $user_id );
		$user_public_key = get_transient( $cache_key );

		if ( empty( $user_public_key ) ) {
			$user_public_key = $wpdb->get_var( $wpdb->prepare( "SELECT meta_key FROM $wpdb->usermeta WHERE meta_value = 'edd_user_public_key' AND user_id = %d", $user_id ) );
			set_transient( $cache_key, $user_public_key, HOUR_IN_SECONDS );
		}

		return $user_public_key;
	}

	/**
	 * Get a users's secret key.
	 *
	 * @param  int $user_id User ID.
	 *
	 * @return string
	 */
	public function get_user_secret_key( $user_id = 0 ) {
		global $wpdb;

		if ( empty( $user_id ) ) {
			return '';
		}

		$cache_key       = md5( 'edd_api_user_secret_key' . $user_id );
		$user_secret_key = get_transient( $cache_key );

		if ( empty( $user_secret_key ) ) {
			$user_secret_key = $wpdb->get_var( $wpdb->prepare( "SELECT meta_key FROM $wpdb->usermeta WHERE meta_value = 'edd_user_secret_key' AND user_id = %d", $user_id ) );
			set_transient( $cache_key, $user_secret_key, HOUR_IN_SECONDS );
		}

		return $user_secret_key;
	}

	/**
	 * Displays a missing authentication error if all the parameters aren't
	 * provided
	 *
	 * @access private
	 * @uses   EDD_API::output()
	 * @since  1.5
	 */
	private function missing_auth() {
		$error          = array();
		$error['error'] = __( 'You must specify both a token and API key!', 'easy-digital-downloads' );

		$this->data = $error;
		$this->output( 401 );
	}

	/**
	 * Displays an authentication failed error if the user failed to provide valid
	 * credentials
	 *
	 * @access private
	 * @since  1.5
	 * @uses   EDD_API::output()
	 * @return void
	 */
	private function invalid_auth() {
		$error          = array();
		$error['error'] = __( 'Your request could not be authenticated!', 'easy-digital-downloads' );

		$this->data = $error;
		$this->output( 403 );
	}

	/**
	 * Displays an invalid API key error if the API key provided couldn't be
	 * validated
	 *
	 * @access private
	 * @since  1.5
	 * @uses   EDD_API::output()
	 * @return void
	 */
	private function invalid_key() {
		$error          = array();
		$error['error'] = __( 'Invalid API key!', 'easy-digital-downloads' );

		$this->data = $error;
		$this->output( 403 );
	}

	/**
	 * Displays an invalid version error if the version number passed isn't valid
	 *
	 * @access private
	 * @since  2.4
	 * @uses   EDD_API::output()
	 * @return void
	 */
	private function invalid_version() {
		$error          = array();
		$error['error'] = __( 'Invalid API version!', 'easy-digital-downloads' );

		$this->data = $error;
		$this->output( 404 );
	}

	/**
	 * Listens for the API and then processes the API requests
	 *
	 * @global $wp_query
	 * @since 1.5
	 * @return void
	 */
	public function process_query() {

		global $wp_query;

		// Start logging how long the request takes for logging.
		$before = microtime( true );

		// Check for edd-api var. Get out if not present.
		if ( empty( $wp_query->query_vars['edd-api'] ) ) {
			return;
		}

		// Determine which version was queried.
		$this->set_queried_version();

		// Determine the kind of query.
		$this->set_query_mode();

		// Check for a valid user and set errors if necessary.
		$this->validate_request();

		// Only proceed if no errors have been noted.
		if ( ! $this->is_valid_request ) {
			return;
		}

		if ( ! defined( 'EDD_DOING_API' ) ) {
			define( 'EDD_DOING_API', true );
		}

		$data         = array();
		$version      = $this->get_queried_version();
		$this->routes = new $this->versions[ $version ]();
		$this->routes->validate_request();

		switch ( $this->endpoint ) :

			case 'stats':
				$data = $this->routes->get_stats(
					array(
						'type'        => isset( $wp_query->query_vars['type'] ) ? $wp_query->query_vars['type'] : null,
						'product'     => isset( $wp_query->query_vars['product'] ) ? $wp_query->query_vars['product'] : null,
						'date'        => isset( $wp_query->query_vars['date'] ) ? $wp_query->query_vars['date'] : null,
						'startdate'   => isset( $wp_query->query_vars['startdate'] ) ? $wp_query->query_vars['startdate'] : null,
						'enddate'     => isset( $wp_query->query_vars['enddate'] ) ? $wp_query->query_vars['enddate'] : null,
						'include_tax' => isset( $wp_query->query_vars['include_tax'] ) ? filter_var( $wp_query->query_vars['include_tax'], FILTER_VALIDATE_BOOLEAN ) : true,
					)
				);

				break;

			case 'products':
				$args = array(
					'product'       => isset( $wp_query->query_vars['product'] ) ? absint( $wp_query->query_vars['product'] ) : null,
					'category'      => isset( $wp_query->query_vars['category'] ) ? $this->sanitize_request_term( $wp_query->query_vars['category'] ) : null,
					'tag'           => isset( $wp_query->query_vars['tag'] ) ? $this->sanitize_request_term( $wp_query->query_vars['tag'] ) : null,
					'term_relation' => isset( $wp_query->query_vars['term_relation'] ) ? $this->sanitize_request_term( $wp_query->query_vars['term_relation'] ) : null,
					's'             => isset( $wp_query->query_vars['s'] ) ? sanitize_text_field( $wp_query->query_vars['s'] ) : null,
					'order'         => isset( $wp_query->query_vars['order'] ) ? $wp_query->query_vars['order'] : 'DESC',
					'orderby'       => isset( $wp_query->query_vars['orderby'] ) ? $wp_query->query_vars['orderby'] : 'date',
				);

				$data = $this->routes->get_products( $args );

				break;

			case 'customers':
				$args = array(
					'customer'  => isset( $wp_query->query_vars['customer'] ) ? $wp_query->query_vars['customer'] : null,
					'date'      => isset( $wp_query->query_vars['date'] ) ? $wp_query->query_vars['date'] : null,
					'startdate' => isset( $wp_query->query_vars['startdate'] ) ? $wp_query->query_vars['startdate'] : null,
					'enddate'   => isset( $wp_query->query_vars['enddate'] ) ? $wp_query->query_vars['enddate'] : null,
				);

				$data = $this->routes->get_customers( $args );

				break;

			case 'sales':
				$data = $this->routes->get_recent_sales();

				break;

			case 'discounts':
				$discount = isset( $wp_query->query_vars['discount'] ) ? $wp_query->query_vars['discount'] : null;

				$data = $this->routes->get_discounts( $discount );

				break;

			case 'file-download-logs':
				$customer = isset( $wp_query->query_vars['customer'] ) ? $wp_query->query_vars['customer'] : null;
				$data     = $this->get_download_logs( $customer );
				break;

			case 'info':
				$data = $this->routes->get_info();

				break;

		endswitch;

		// Allow extensions to setup their own return data.
		$this->data = apply_filters( 'edd_api_output_data', $data, $this->endpoint, $this );

		$after                       = microtime( true );
		$request_time                = ( $after - $before );
		$this->data['request_speed'] = $request_time;

		// Log this API request, if enabled. We log it here because we have access to errors.
		$this->log_request( $this->data );

		// Send out data to the output function.
		$this->output();
	}

	/**
	 * Returns the API endpoint requested
	 *
	 * @access private
	 * @since  1.5
	 * @return string $query Query mode
	 */
	public function get_query_mode() {

		return $this->endpoint;
	}

	/**
	 * Determines the kind of query requested and also ensure it is a valid query
	 *
	 * @access private
	 * @since  2.4
	 * @global $wp_query
	 */
	public function set_query_mode() {

		global $wp_query;

		// Whitelist our query options.
		$accepted = apply_filters(
			'edd_api_valid_query_modes',
			array(
				'stats',
				'products',
				'customers',
				'sales',
				'discounts',
				'file-download-logs',
				'info',
			)
		);

		$query = isset( $wp_query->query_vars['edd-api'] ) ? $wp_query->query_vars['edd-api'] : null;
		$query = str_replace( $this->queried_version . '/', '', $query );

		$error = array();

		// Make sure our query is valid.
		if ( ! in_array( $query, $accepted, true ) ) {
			$error['error'] = __( 'Invalid query!', 'easy-digital-downloads' );

			$this->data = $error;
			// 400 is Bad Request
			$this->output( 400 );
		}

		$this->endpoint = $query;
	}

	/**
	 * Get page number
	 *
	 * @access private
	 * @since  1.5
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
	 * @since  1.5
	 * @global $wp_query
	 * @return int $per_page Results to display per page (default: 10)
	 */
	public function per_page() {
		global $wp_query;

		$per_page = isset( $wp_query->query_vars['number'] ) ? $wp_query->query_vars['number'] : 10;

		if ( $per_page < 0 && 'customers' === $this->get_query_mode() ) {
			$per_page = 99999999; // Customers query doesn't support -1.
		}

		return apply_filters( 'edd_api_results_per_page', $per_page );
	}

	/**
	 * Sets up the dates used to retrieve earnings/sales
	 *
	 * @since 1.5.1
	 *
	 * @param array $args Arguments to override defaults.
	 *
	 * @return array $dates
	 */
	public function get_dates( $args = array() ) {
		$dates = array();

		$defaults = array(
			'type'      => '',
			'product'   => null,
			'date'      => null,
			'startdate' => null,
			'enddate'   => null,
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
			$dates['year_end']  = date( 'Y', $enddate );
		} else {
			// Modify dates based on predefined ranges.
			switch ( $args['date'] ) :

				case 'this_month':
					$dates['day']     = 1;
					$dates['day_end'] = date( 't', $current_time );
					$dates['m_start'] = date( 'n', $current_time );
					$dates['m_end']   = date( 'n', $current_time );
					$dates['year']    = date( 'Y', $current_time );
					break;

				case 'last_month':
					$dates['day']     = 1;
					$dates['m_start'] = date( 'n', $current_time ) == 1 ? 12 : date( 'n', $current_time ) - 1;
					$dates['m_end']   = $dates['m_start'];
					$dates['year']    = date( 'n', $current_time ) == 1 ? date( 'Y', $current_time ) - 1 : date( 'Y', $current_time );
					$dates['day_end'] = date( 't', strtotime( $dates['year'] . '-' . $dates['m_start'] . '-' . $dates['day'] ) );
					break;

				case 'today':
					$dates['day']     = date( 'd', $current_time );
					$dates['day_end'] = date( 'd', $current_time );
					$dates['m_start'] = date( 'n', $current_time );
					$dates['m_end']   = date( 'n', $current_time );
					$dates['year']    = date( 'Y', $current_time );
					break;

				case 'yesterday':
					$year  = date( 'Y', $current_time );
					$month = date( 'n', $current_time );
					$day   = date( 'd', $current_time );

					if ( 1 === $month && 1 === $day ) {

						$year -= 1;
						$month = 12;
						$day   = cal_days_in_month( CAL_GREGORIAN, $month, $year );

					} elseif ( $month > 1 && 1 === $day ) {

						$month -= 1;
						$day    = cal_days_in_month( CAL_GREGORIAN, $month, $year );

					} else {

						$day -= 1;

					}

					$dates['day']     = $day;
					$dates['day_end'] = $day;
					$dates['m_start'] = $month;
					$dates['m_end']   = $month;
					$dates['year']    = $year;

					break;

				case 'this_quarter':
					$month_now = date( 'n', $current_time );

					$dates['day'] = 1;

					if ( $month_now <= 3 ) {

						$dates['m_start'] = 1;
						$dates['m_end']   = 3;
						$dates['year']    = date( 'Y', $current_time );

					} elseif ( $month_now <= 6 ) {

						$dates['m_start'] = 4;
						$dates['m_end']   = 6;
						$dates['year']    = date( 'Y', $current_time );

					} elseif ( $month_now <= 9 ) {

						$dates['m_start'] = 7;
						$dates['m_end']   = 9;
						$dates['year']    = date( 'Y', $current_time );

					} else {

						$dates['m_start'] = 10;
						$dates['m_end']   = 12;
						$dates['year']    = date( 'Y', $current_time );

					}

					$dates['day_end'] = date( 't', strtotime( $dates['year'] . '-' . $dates['m_end'] ) );

					break;

				case 'last_quarter':
					$month_now = date( 'n', $current_time );

					$dates['day'] = 1;

					if ( $month_now <= 3 ) {

						$dates['m_start'] = 10;
						$dates['m_end']   = 12;
						$dates['year']    = date( 'Y', $current_time ) - 1; // Previous year

					} elseif ( $month_now <= 6 ) {

						$dates['m_start'] = 1;
						$dates['m_end']   = 3;
						$dates['year']    = date( 'Y', $current_time );

					} elseif ( $month_now <= 9 ) {

						$dates['m_start'] = 4;
						$dates['m_end']   = 6;
						$dates['year']    = date( 'Y', $current_time );

					} else {

						$dates['m_start'] = 7;
						$dates['m_end']   = 9;
						$dates['year']    = date( 'Y', $current_time );

					}

					$dates['day_end'] = date( 't', strtotime( $dates['year'] . '-' . $dates['m_end'] ) );
					break;

				case 'this_year':
					$dates['day']     = 1;
					$dates['m_start'] = 1;
					$dates['m_end']   = 12;
					$dates['day_end'] = 31;
					$dates['year']    = date( 'Y', $current_time );
					break;

				case 'last_year':
					$dates['day']     = 1;
					$dates['m_start'] = 1;
					$dates['m_end']   = 12;
					$dates['day_end'] = 31;
					$dates['year']    = date( 'Y', $current_time ) - 1;
					break;

				case 'this_week':
				case 'last_week':
					$start_of_week = get_option( 'start_of_week' );

					if ( 'last_week' === $args['date'] ) {
						$today = date( 'd', $current_time - WEEK_IN_SECONDS );
					} else {
						$today = date( 'd', $current_time );
					}

					$day_of_the_week = date( 'w', $current_time );
					$month           = date( 'n', $current_time );
					$year            = date( 'Y', $current_time );

					// Account for a week the spans a month change (including if that week spans over a break in the year).
					if ( ( $today - $day_of_the_week ) < 1 ) {
						$start_date     = date( 'd', strtotime( $year . '-' . $month . '-' . $today . ' -' . $day_of_the_week . ' days' ) );
						$month          = $month > 1 ? $month-- : 12;
						$adjusted_month = true;
					} else {
						$start_date     = $today - $day_of_the_week;
						$adjusted_month = false;
					}

					// Account for the WordPress Start of Week setting.
					$adjusted_start_date = date( 'd', strtotime( $year . '-' . $month . '-' . $start_date . ' +' . $start_of_week . 'days' ) );

					/**
					 * Account for when the base start of the week is the end of one month, but the WordPress Start of Week setting
					 * Jumps it to the following month.
					 */
					if ( $adjusted_start_date < $start_date ) {
						if ( 12 === $month ) {
							$month = 1;
							++$year;
						} else {
							++$month;
						}
					}

					$dates['day']     = $adjusted_start_date;
					$dates['m_start'] = $month;
					$dates['year']    = $month === 12 && $adjusted_month ? $year - 1 : $year;

					$base_start_date      = $dates['year'] . '-' . $dates['m_start'] . '-' . $dates['day'];
					$base_start_timestamp = strtotime( $base_start_date . ' +6 days' );
					$dates['m_end']       = date( 'n', $base_start_timestamp );
					$dates['day_end']     = date( 'd', $base_start_timestamp );
					$dates['year_end']    = date( 'Y', $base_start_timestamp );
					break;

			endswitch;
		}

		/**
		 * Returns the filters for the dates used to retreive earnings/sales
		 *
		 * @since 1.5.1
		 *
		 * @param object $dates The dates used for retreiving earnings/sales
		 */

		return apply_filters( 'edd_api_stat_dates', $dates );
	}

	/**
	 * Process Get Customers API Request
	 *
	 * @since  1.5
	 *
	 * @param int $customer Customer ID.
	 *
	 * @return array $customers Multidimensional array of the customers
	 */
	public function get_customers( $customer = null ) {

		$customer  = is_array( $customer ) ? $customer['customer'] : $customer;
		$customers = array();
		$error     = array();

		if ( ! user_can( $this->user_id, 'view_shop_sensitive_data' ) && ! $this->override ) {
			return $customers;
		}

		$paged    = $this->get_paged();
		$per_page = $this->per_page();
		$offset   = $per_page * ( $paged - 1 );

		if ( is_numeric( $customer ) ) {
			$field = 'id';
		} elseif ( is_array( $customer ) ) {
			// Checking if search is being done by id, email, user_id fields.
			if ( array_key_exists( 'id', $customer ) ) {
				$field = 'id';
			} elseif ( array_key_exists( 'email', $customer ) ) {
				$field = 'email';
			} elseif ( array_key_exists( 'user_id', $customer ) ) {
				$field = 'user_id';
			}

			$customer = $customer[ $field ];
		} else {
			$field = 'email';
		}

		$args = array(
			'number' => $per_page,
			'offset' => $offset,
		);

		if ( ! is_null( $customer ) ) {
			$args[ $field ] = $customer;
		}

		$customer_query = edd_get_customers( $args );
		$customer_count = 0;

		if ( $customer_query ) {

			foreach ( $customer_query as $customer_obj ) {

				$names      = explode( ' ', $customer_obj->name );
				$first_name = ! empty( $names[0] ) ? $names[0] : '';
				$last_name  = '';
				if ( ! empty( $names[1] ) ) {
					unset( $names[0] );
					$last_name = implode( ' ', $names );
				}

				$customers['customers'][ $customer_count ]['info']['id']           = '';
				$customers['customers'][ $customer_count ]['info']['user_id']      = '';
				$customers['customers'][ $customer_count ]['info']['username']     = '';
				$customers['customers'][ $customer_count ]['info']['display_name'] = '';
				$customers['customers'][ $customer_count ]['info']['customer_id']  = $customer_obj->id;
				$customers['customers'][ $customer_count ]['info']['first_name']   = $first_name;
				$customers['customers'][ $customer_count ]['info']['last_name']    = $last_name;
				$customers['customers'][ $customer_count ]['info']['email']        = $customer_obj->email;

				if ( ! empty( $customer_obj->user_id ) && $customer_obj->user_id > 0 ) {

					$user_data = get_userdata( $customer_obj->user_id );

					// Customer with registered account.

					// id is going to get deprecated in the future, user user_id or customer_id instead.
					$customers['customers'][ $customer_count ]['info']['id']           = $customer_obj->user_id;
					$customers['customers'][ $customer_count ]['info']['user_id']      = $customer_obj->user_id;
					$customers['customers'][ $customer_count ]['info']['username']     = $user_data->user_login;
					$customers['customers'][ $customer_count ]['info']['display_name'] = $user_data->display_name;

				}

				$customers['customers'][ $customer_count ]['stats']['total_purchases'] = $customer_obj->purchase_count;
				$customers['customers'][ $customer_count ]['stats']['total_spent']     = $customer_obj->purchase_value;
				$customers['customers'][ $customer_count ]['stats']['total_downloads'] = edd_count_file_downloads_of_customer( $customer_obj->id );

				++$customer_count;

			}
		} elseif ( $customer ) {

			/* translators: %s: customer ID. */
			$error['error'] = sprintf( __( 'Customer %s not found!', 'easy-digital-downloads' ), $customer );

			return $error;

		} else {

			$error['error'] = __( 'No customers found!', 'easy-digital-downloads' );

			return $error;

		}

		return apply_filters( 'edd_api_customers', $customers, $this );
	}

	/**
	 * Process Get Products API Request
	 *
	 * @since  1.5
	 *
	 * @param array $args Arguments provided by API Request.
	 *
	 * @return array $customers Multidimensional array of the products
	 */
	public function get_products( $args = array() ) {
		$products = array();
		$error    = array();

		if ( empty( $args['product'] ) ) {
			$products['products'] = array();

			$parameters = array(
				'post_type'        => 'download',
				'posts_per_page'   => $this->per_page(),
				'suppress_filters' => true,
				'paged'            => $this->get_paged(),
			);

			if ( isset( $args['s'] ) && ! empty( $args['s'] ) ) {
				$parameters['s'] = $args['s'];
			}

			$product_list = get_posts( $parameters );

			if ( $product_list ) {
				$i = 0;
				foreach ( $product_list as $product_info ) {
					$products['products'][ $i ] = $this->get_product_data( $product_info );
					++$i;
				}
			}
		} elseif ( 'download' === get_post_type( $args['product'] ) ) {
				$product_info = get_post( $args['product'] );

				$products['products'][0] = $this->get_product_data( $product_info );
		} else {
			/* translators: %s: product ID. */
			$error['error'] = sprintf( __( 'Product %s not found!', 'easy-digital-downloads' ), $args['product'] );

			return $error;
		}

		return apply_filters( 'edd_api_products', $products, $this );
	}

	/**
	 * Given a download post object, generate the data for the API output
	 *
	 * @since  2.3.9
	 *
	 * @param  object $product_info The Download Post Object.
	 *
	 * @return array                Array of post data to return back in the API
	 */
	public function get_product_data( $product_info ) {

		$product = array();

		$product['info']['id']            = $product_info->ID;
		$product['info']['slug']          = $product_info->post_name;
		$product['info']['title']         = $product_info->post_title;
		$product['info']['create_date']   = $product_info->post_date;
		$product['info']['modified_date'] = $product_info->post_modified;
		$product['info']['status']        = $product_info->post_status;
		$product['info']['link']          = html_entity_decode( $product_info->guid );
		$product['info']['permalink']     = html_entity_decode( get_permalink( $product_info->ID ) );
		$product['info']['content']       = $product_info->post_content;
		$product['info']['excerpt']       = $product_info->post_excerpt;
		$product['info']['thumbnail']     = wp_get_attachment_url( get_post_thumbnail_id( $product_info->ID ) );
		$product['info']['category']      = get_the_terms( $product_info, 'download_category' );
		$product['info']['tags']          = get_the_terms( $product_info, 'download_tag' );

		if ( user_can( $this->user_id, 'view_shop_reports' ) || $this->override ) {
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

		if ( user_can( $this->user_id, 'view_shop_sensitive_data' ) || $this->override ) {
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
	 * @since  1.5
	 *
	 * @global object $wpdb Used to query the database using the WordPress
	 *
	 * @param array $args Arguments provided by API Request.
	 *
	 * @return array
	 */
	public function get_stats( $args = array() ) {

		// Parse args.
		$args = wp_parse_args(
			$args,
			array(
				'type'        => null,
				'product'     => null,
				'date'        => null,
				'startdate'   => null,
				'enddate'     => null,
				'include_tax' => true,
			)
		);

		$dates = $this->get_dates( $args );

		$stats    = array();
		$earnings = array(
			'earnings' => array(),
		);
		$sales    = array(
			'sales' => array(),
		);
		$error    = array();

		if ( ! user_can( $this->user_id, 'view_shop_reports' ) && ! $this->override ) {
			return $stats;
		}

		if ( 'sales' === $args['type'] ) {
			if ( null === $args['product'] ) {
				if ( null === $args['date'] ) {
					$sales = $this->get_default_sales_stats();
				} elseif ( 'range' === $args['date'] ) {
					// Ensure the end date is later than the start date.
					if ( $args['enddate'] < $args['startdate'] ) {
						$error['error'] = __( 'The end date must be later than the start date!', 'easy-digital-downloads' );
					}

					// Ensure both the start and end date are specified.
					if ( empty( $args['startdate'] ) || empty( $args['enddate'] ) ) {
						$error['error'] = __( 'Invalid or no date range specified!', 'easy-digital-downloads' );
					}

					$start_date = EDD()->utils->date( $dates['year'] . '-' . $dates['m_start'] . '-' . $dates['day_start'], edd_get_timezone_id(), false )->startOfDay();
					$end_date   = EDD()->utils->date( $dates['year_end'] . '-' . $dates['m_end'] . '-' . $dates['day_end'], edd_get_timezone_id(), false )->endOfDay();

					// Force the data for the reports API.
					$_GET['filter_from'] = $start_date->format( 'Y-m-d' );
					$_GET['filter_to']   = $end_date->format( 'Y-m-d' );
					$_GET['range']       = 'other';

					$stats = new Stats(
						array(
							'revenue_type' => 'net',
						)
					);

					// Get UTC dates for selected date range.
					$utc_dates = Reports\parse_dates_for_range();

					// Get total sales.
					$sales['totals'] = $stats->get_order_count(
						array(
							'start' => $utc_dates['start']->format( 'Y-m-d H:i:s' ),
							'end'   => $utc_dates['end']->format( 'Y-m-d H:i:s' ),
						)
					);

					// Get sales for each day in the requested date range.
					$current_date = $start_date->copy();
					while ( $current_date->lte( $end_date ) ) {
						// Force the data for the reports API.
						$_GET['filter_from'] = $current_date->format( 'Y-m-d' );
						$_GET['filter_to']   = $current_date->format( 'Y-m-d' );
						$_GET['range']       = 'other';

						$date_key  = str_replace( '-', '', $current_date->format( 'Y-m-d' ) );
						$utc_dates = Reports\parse_dates_for_range();

						if ( ! isset( $sales['sales'][ $date_key ] ) ) {
							$sales['sales'][ $date_key ] = $stats->get_order_count(
								array(
									'start' => $utc_dates['start']->format( 'Y-m-d H:i:s' ),
									'end'   => $utc_dates['end']->format( 'Y-m-d H:i:s' ),
								)
							);
						}

						$current_date->addDay();
					}

					ksort( $sales['sales'] );
				} else {
					$stats = new Stats(
						array(
							'range'        => $args['date'],
							'revenue_type' => 'net',
						)
					);

					$sales['sales'][ $args['date'] ] = $stats->get_order_count();
				}
			} elseif ( 'all' === $args['product'] ) {
				$products = get_posts(
					array(
						'post_type' => 'download',
						'nopaging'  => true,
					)
				);
				$i        = 0;

				$stats = new Stats();
				foreach ( $products as $product_info ) {
					$product_order_count = $stats->get_order_item_count(
						array(
							'product_id' => $product_info->ID,
						)
					);

					$sales['sales'][ $i ] = array(
						$product_info->post_name => $product_order_count,
					);
					++$i;
				}
			} elseif ( 'download' === get_post_type( $args['product'] ) ) {
					$stats            = new Stats();
					$product_info     = get_post( $args['product'] );
					$order_item_count = $stats->get_order_item_count(
						array(
							'product_id' => $args['product'],
						)
					);

					$sales['sales'][0] = array(
						$product_info->post_name => $order_item_count,
					);
			} else {
				$error['error'] = sprintf( __( 'Product %s not found!', 'easy-digital-downloads' ), $args['product'] );
			}

			if ( ! empty( $error ) ) {
				return $error;
			}

			return apply_filters( 'edd_api_stats_sales', $sales, $this );
		} elseif ( 'earnings' === $args['type'] ) {
			if ( null === $args['product'] ) {
				if ( null === $args['date'] ) {
					$earnings = $this->get_default_earnings_stats( $args );
				} elseif ( 'range' === $args['date'] ) {
					// Ensure the end date is later than the start date.
					if ( $args['enddate'] < $args['startdate'] ) {
						$error['error'] = __( 'The end date must be later than the start date!', 'easy-digital-downloads' );
					}

					// Ensure both the start and end date are specified.
					if ( empty( $args['startdate'] ) || empty( $args['enddate'] ) ) {
						$error['error'] = __( 'Invalid or no date range specified!', 'easy-digital-downloads' );
					}

					$start_date = EDD()->utils->date( $dates['year'] . '-' . $dates['m_start'] . '-' . $dates['day_start'], edd_get_timezone_id(), false )->startOfDay();
					$end_date   = EDD()->utils->date( $dates['year_end'] . '-' . $dates['m_end'] . '-' . $dates['day_end'], edd_get_timezone_id(), false )->endOfDay();

					// Force the data for the reports API.
					$_GET['filter_from'] = $start_date->format( 'Y-m-d' );
					$_GET['filter_to']   = $end_date->format( 'Y-m-d' );
					$_GET['range']       = 'other';

					$stats = new Stats(
						array(
							'revenue_type'  => 'net',
							'exclude_taxes' => ! $args['include_tax'],
							'output'        => 'typed',
						)
					);

					// Get UTC dates for selected date range.
					$utc_dates = Reports\parse_dates_for_range();

					// Get total earnings.
					$earnings['totals'] = $stats->get_order_earnings(
						array(
							'start' => $utc_dates['start']->format( 'Y-m-d H:i:s' ),
							'end'   => $utc_dates['end']->format( 'Y-m-d H:i:s' ),
						)
					);

					// Get earnings for each day in the requested date range.
					$current_date = $start_date->copy();
					while ( $current_date->lte( $end_date ) ) {
						// Force the data for the reports API.
						$_GET['filter_from'] = $current_date->format( 'Y-m-d' );
						$_GET['filter_to']   = $current_date->format( 'Y-m-d' );
						$_GET['range']       = 'other';

						$date_key  = str_replace( '-', '', $current_date->format( 'Y-m-d' ) );
						$utc_dates = Reports\parse_dates_for_range();

						if ( ! isset( $sales['earnings'][ $date_key ] ) ) {
							$earnings['earnings'][ $date_key ] = $stats->get_order_earnings(
								array(
									'start' => $utc_dates['start']->format( 'Y-m-d H:i:s' ),
									'end'   => $utc_dates['end']->format( 'Y-m-d H:i:s' ),
								)
							);
						}

						$current_date->addDay();
					}

					ksort( $earnings['earnings'] );
				} else {
					$stats                                 = new Stats(
						array(
							'range'        => $args['date'],
							'revenue_type' => 'net',
							'exclude_tax'  => ! $args['include_tax'],
							'output'       => 'typed',
						)
					);
					$earnings['earnings'][ $args['date'] ] = $stats->get_order_earnings();
				}
			} elseif ( 'all' === $args['product'] ) {
				$products = get_posts(
					array(
						'post_type' => 'download',
						'nopaging'  => true,
					)
				);
				$i        = 0;

				$stats = new Stats();
				foreach ( $products as $product_info ) {
					$product_earnings = $stats->get_order_item_earnings(
						array(
							'product_id' => $product_info->ID,
							'output'     => 'typed',
						)
					);

					$earnings['earnings'][ $i ] = array(
						$product_info->post_name => $product_earnings,
					);
					++$i;
				}
			} elseif ( 'download' === get_post_type( $args['product'] ) ) {
					$stats               = new Stats();
					$product_info        = get_post( $args['product'] );
					$order_item_earnings = $stats->get_order_item_earnings(
						array(
							'product_id' => $args['product'],
							'output'     => 'typed',
						)
					);

					$earnings['earnings'][0] = array(
						$product_info->post_name => $order_item_earnings,
					);
			} else {
				$error['error'] = sprintf( __( 'Product %s not found!', 'easy-digital-downloads' ), $args['product'] );
			}

			if ( ! empty( $error ) ) {
				return $error;
			}

			return apply_filters( 'edd_api_stats_earnings', $earnings, $this );
		} elseif ( 'customers' === $args['type'] ) {
			$stats['customers']['total_customers'] = edd_count_customers();

			return apply_filters( 'edd_api_stats_customers', $stats, $this );
		} elseif ( empty( $args['type'] ) ) {
			$stats = array_merge( $stats, $this->get_default_sales_stats() );
			$stats = array_merge( $stats, $this->get_default_earnings_stats( $args ) );

			return apply_filters(
				'edd_api_stats',
				array(
					'stats' => $stats,
					$this,
				)
			);
		}
	}

	/**
	 * Retrieves Recent Sales
	 *
	 * @since  1.5
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
			$query[] = new EDD_Payment( $wp_query->query_vars['id'] );
		} elseif ( isset( $wp_query->query_vars['purchasekey'] ) ) {
			$query   = array();
			$query[] = edd_get_payment_by( 'key', $wp_query->query_vars['purchasekey'] );
		} elseif ( isset( $wp_query->query_vars['email'] ) ) {
			$query = edd_get_payments(
				array(
					'fields'     => 'ids',
					'meta_key'   => '_edd_payment_user_email',
					'meta_value' => $wp_query->query_vars['email'],
					'number'     => $this->per_page(),
					'page'       => $this->get_paged(),
					'status'     => 'complete',
				)
			);
		} else {
			$query = edd_get_payments(
				array(
					'fields' => 'ids',
					'number' => $this->per_page(),
					'page'   => $this->get_paged(),
					'status' => 'complete',
				)
			);
		}

		if ( $query ) {
			$i = 0;
			foreach ( $query as $payment ) {
				if ( is_numeric( $payment ) ) {
					$payment = new EDD_Payment( $payment );
				}

				if ( empty( $payment ) ) {
					continue;
				}

				$user_info = $payment->user_info;

				$sales['sales'][ $i ]['ID']             = $payment->number;
				$sales['sales'][ $i ]['transaction_id'] = $payment->transaction_id;
				$sales['sales'][ $i ]['key']            = $payment->key;
				$sales['sales'][ $i ]['discount']       = ! empty( $payment->discounts ) ? explode( ',', $payment->discounts ) : array();
				$sales['sales'][ $i ]['subtotal']       = $payment->subtotal;
				$sales['sales'][ $i ]['tax']            = $payment->tax;
				$sales['sales'][ $i ]['fees']           = $payment->fees;
				$sales['sales'][ $i ]['total']          = $payment->total;
				$sales['sales'][ $i ]['gateway']        = $payment->gateway;
				$sales['sales'][ $i ]['email']          = $payment->email;
				$sales['sales'][ $i ]['user_id']        = $payment->user_id;
				$sales['sales'][ $i ]['customer_id']    = $payment->customer_id;
				$sales['sales'][ $i ]['date']           = $payment->date;
				$sales['sales'][ $i ]['products']       = array();

				$c = 0;

				if ( ! empty( $payment->cart_details ) && is_array( $payment->cart_details ) ) {
					foreach ( $payment->cart_details as $key => $item ) {

						$item_id  = isset( $item['id'] ) ? $item['id'] : $item;
						$price    = isset( $item['price'] ) ? $item['price'] : false;
						$price_id = isset( $item['item_number']['options']['price_id'] ) ? $item['item_number']['options']['price_id'] : null;
						$quantity = isset( $item['quantity'] ) && $item['quantity'] > 0 ? $item['quantity'] : 1;

						if ( ! $price ) {
							// This function is only used on payments with near 1.0 cart data structure
							$price = edd_get_download_final_price( $item_id, $user_info, null );
						}

						$price_name = '';
						if ( isset( $item['item_number'] ) && isset( $item['item_number']['options'] ) ) {
							$price_options = $item['item_number']['options'];
							if ( isset( $price_options['price_id'] ) ) {
								$price_name = edd_get_price_option_name( $item_id, $price_options['price_id'], $payment->ID );
							}
						}

						$sales['sales'][ $i ]['products'][ $c ]['id']         = $item_id;
						$sales['sales'][ $i ]['products'][ $c ]['quantity']   = $quantity;
						$sales['sales'][ $i ]['products'][ $c ]['name']       = get_the_title( $item_id );
						$sales['sales'][ $i ]['products'][ $c ]['price']      = $price;
						$sales['sales'][ $i ]['products'][ $c ]['price_name'] = $price_name;
						++$c;
					}
				}

				++$i;
			}
		}

		return apply_filters( 'edd_api_sales', $sales, $this );
	}

	/**
	 * Process Get Discounts API Request
	 *
	 * @since 1.6
	 * @global object $wpdb     Used to query the database using the WordPress
	 *                          Database API
	 *
	 * @param int $discount Discount ID.
	 *
	 * @return array $discounts Multidimensional array of the discounts
	 */
	public function get_discounts( $discount = null ) {

		$discount_list = array();
		$error         = array();

		if ( ! user_can( $this->user_id, 'manage_shop_discounts' ) && ! $this->override ) {
			return $discount_list;
		}

		if ( empty( $discount ) ) {
			$count     = 0;
			$paged     = $this->get_paged();
			$per_page  = $this->per_page();
			$discounts = edd_get_discounts(
				array(
					'number' => $per_page,
					'offset' => ( absint( $paged ) - 1 ) * $per_page,
				)
			);

			if ( empty( $discounts ) ) {
				$error['error'] = __( 'No discounts found!', 'easy-digital-downloads' );

				return $error;
			}

			foreach ( $discounts as $discount ) {
				$discount_list['discounts'][ $count ]['ID']                    = $discount->id;
				$discount_list['discounts'][ $count ]['name']                  = $discount->name;
				$discount_list['discounts'][ $count ]['code']                  = $discount->code;
				$discount_list['discounts'][ $count ]['amount']                = $discount->amount;
				$discount_list['discounts'][ $count ]['min_price']             = $discount->min_charge_amount;
				$discount_list['discounts'][ $count ]['type']                  = $discount->amount_type;
				$discount_list['discounts'][ $count ]['uses']                  = $discount->use_count;
				$discount_list['discounts'][ $count ]['max_uses']              = $discount->max_uses;
				$discount_list['discounts'][ $count ]['start_date']            = $discount->start_date;
				$discount_list['discounts'][ $count ]['exp_date']              = $discount->end_date;
				$discount_list['discounts'][ $count ]['status']                = $discount->status;
				$discount_list['discounts'][ $count ]['product_requirements']  = $discount->product_reqs;
				$discount_list['discounts'][ $count ]['requirement_condition'] = $discount->product_condition;
				$discount_list['discounts'][ $count ]['global_discount']       = 'global' === $discount->scope;
				$discount_list['discounts'][ $count ]['excluded_products']     = $discount->excluded_products;
				$discount_list['discounts'][ $count ]['single_use']            = $discount->once_per_customer;

				++$count;
			}
		} else {
			$discount_object = edd_get_discount( $discount );
			if ( is_numeric( $discount ) && $discount_object ) {
				$discount_list['discounts'][0]['ID']                    = $discount_object->id;
				$discount_list['discounts'][0]['name']                  = $discount_object->name;
				$discount_list['discounts'][0]['code']                  = $discount_object->code;
				$discount_list['discounts'][0]['amount']                = $discount_object->amount;
				$discount_list['discounts'][0]['min_price']             = $discount_object->min_charge_amount;
				$discount_list['discounts'][0]['type']                  = $discount_object->amount_type;
				$discount_list['discounts'][0]['uses']                  = $discount_object->use_count;
				$discount_list['discounts'][0]['max_uses']              = $discount_object->max_uses;
				$discount_list['discounts'][0]['start_date']            = $discount_object->start_date;
				$discount_list['discounts'][0]['exp_date']              = $discount_object->end_date;
				$discount_list['discounts'][0]['status']                = $discount_object->status;
				$discount_list['discounts'][0]['product_requirements']  = $discount_object->product_reqs;
				$discount_list['discounts'][0]['requirement_condition'] = $discount_object->product_condition;
				$discount_list['discounts'][0]['global_discount']       = 'global' === $discount_object->scope;
				$discount_list['discounts'][0]['excluded_products']     = $discount_object->excluded_products;
				$discount_list['discounts'][0]['single_use']            = $discount_object->once_per_customer;
			} else {
				/* translators: %s: discount ID. */
				$error['error'] = sprintf( __( 'Discount %s not found!', 'easy-digital-downloads' ), $discount );

				return $error;
			}
		}

		return apply_filters( 'edd_api_discounts', $discount_list, $this );
	}

	/**
	 * Process Get Downloads API Request to retrieve download logs
	 *
	 * @since  2.5
	 * @since 3.2.1 Updated to use edd_get_file_download_logs().
	 *
	 * @param  int $customer_id The customer ID or email you wish to retrieve download logs for.
	 * @return array            Multidimensional array of the download logs
	 */
	public function get_download_logs( $customer_id = 0 ) {

		$downloads      = array();
		$paged          = $this->get_paged();
		$per_page       = $this->per_page();
		$offset         = $per_page * ( $paged - 1 );
		$valid_customer = true;

		$query = array(
			'number' => $per_page,
			'offset' => $offset,
		);
		if ( ! empty( $customer_id ) ) {
			if ( is_email( $customer_id ) ) {
				$customer = edd_get_customer_by( 'email', $customer_id );
			} else {
				$customer = edd_get_customer( $customer_id );
			}
			if ( $customer ) {
				$query['customer_id'] = $customer->id;
			} else {
				$valid_customer = false;
			}
		}

		$logs = $valid_customer ? edd_get_file_download_logs( $query ) : false;

		if ( empty( $logs ) ) {
			$error['error'] = __( 'No download logs found!', 'easy-digital-downloads' );

			return $error;
		}

		foreach ( $logs as $log ) {

			$customer  = edd_get_customer( $log->customer_id );
			$file_id   = $log->file_id;
			$file_name = edd_get_file_download_log_meta( $log->id, 'file_name', true );
			if ( empty( $file_name ) ) {
				$files = get_post_meta( $log->product_id, 'edd_download_files', true );
				if ( is_array( $files ) && isset( $files[ $file_id ] ) ) {
					$file_name = ! empty( $files[ $file_id ]['name'] )
						? $files[ $file_id ]['name']
						: edd_get_file_name( $files[ $file_id ] );
				}
			}

			$item = (array) apply_filters(
				'edd_api_download_log_item',
				array(
					'ID'           => (int) $log->id,
					'user_id'      => (int) $customer->user_id,
					'product_id'   => (int) $log->product_id,
					'product_name' => edd_get_download_name( $log->product_id, $log->product_id ),
					'customer_id'  => (int) $log->customer_id,
					'payment_id'   => (int) $log->order_id,
					'file'         => $file_name,
					'ip'           => $log->ip,
					'date'         => $log->date_created,
				),
				$log,
				edd_get_file_download_log_meta( $log->id )
			);

			$downloads['download_logs'][] = $item;
		}

		return apply_filters( 'edd_api_download_logs', $downloads, $this );
	}

	/**
	 * Process Get Info API Request
	 *
	 * @return array
	 */
	public function get_info() {
		$data = array();

		// plugin.php required to use is_plugin_active().
		require_once ABSPATH . 'wp-admin/includes/plugin.php';

		// Integrations.
		if ( is_plugin_active( 'edd-commissions/edd-commissions.php' ) ) {
			$data['info']['integrations']['commissions'] = true;
		}

		if ( class_exists( 'EDD_Software_Licensing' ) ) {
			$data['info']['integrations']['software_licensing'] = true;
		}

		if ( class_exists( 'EDD_Front_End_Submissions' ) ) {
			$data['info']['integrations']['fes'] = true;
		}

		if ( class_exists( 'EDD_Reviews' ) ) {
			$data['info']['integrations']['reviews'] = true;
		}

		if ( class_exists( 'EDD_Recurring' ) ) {
			$data['info']['integrations']['recurring'] = true;
		}

		// Permissions.
		if ( user_can( $this->user_id, 'view_shop_reports' ) ) {
			$data['info']['permissions']['view_shop_reports'] = true;
		}

		if ( user_can( $this->user_id, 'view_shop_sensitive_data' ) ) {
			$data['info']['permissions']['view_shop_sensitive_data'] = true;
		}

		if ( user_can( $this->user_id, 'manage_shop_discounts' ) ) {
			$data['info']['permissions']['manage_shop_discounts'] = true;
		}

		// Site Information.
		if ( user_can( $this->user_id, 'view_shop_sensitive_data' ) ) {
			$data['info']['site']['wp_version']  = get_bloginfo( 'version' );
			$data['info']['site']['edd_version'] = EDD_VERSION;
		}

		$data['info']['site']['currency']            = edd_get_currency();
		$data['info']['site']['currency_position']   = edd_get_option( 'currency_position', 'before' );
		$data['info']['site']['decimal_separator']   = edd_get_option( 'decimal_separator', '.' );
		$data['info']['site']['thousands_separator'] = edd_get_option( 'thousands_separator', ',' );

		return apply_filters( 'edd_api_info', $data, $this );
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
	 * Log each API request, if enabled
	 *
	 * @access private
	 * @since  1.5
	 * @global      $edd_logs
	 * @global      $wp_query
	 *
	 * @param array $data Data to log.
	 *
	 * @return void
	 */
	private function log_request( $data = array() ) {
		if ( ! $this->log_requests() ) {
			return;
		}

		global $wp_query;

		$query = array(
			'edd-api'     => $wp_query->query_vars['edd-api'],
			'key'         => isset( $wp_query->query_vars['key'] ) ? $wp_query->query_vars['key'] : null,
			'token'       => isset( $wp_query->query_vars['token'] ) ? $wp_query->query_vars['token'] : null,
			'query'       => isset( $wp_query->query_vars['query'] ) ? $wp_query->query_vars['query'] : null,
			'type'        => isset( $wp_query->query_vars['type'] ) ? $wp_query->query_vars['type'] : null,
			'product'     => isset( $wp_query->query_vars['product'] ) ? $wp_query->query_vars['product'] : null,
			'customer'    => isset( $wp_query->query_vars['customer'] ) ? $wp_query->query_vars['customer'] : null,
			'date'        => isset( $wp_query->query_vars['date'] ) ? $wp_query->query_vars['date'] : null,
			'startdate'   => isset( $wp_query->query_vars['startdate'] ) ? $wp_query->query_vars['startdate'] : null,
			'enddate'     => isset( $wp_query->query_vars['enddate'] ) ? $wp_query->query_vars['enddate'] : null,
			'id'          => isset( $wp_query->query_vars['id'] ) ? $wp_query->query_vars['id'] : null,
			'purchasekey' => isset( $wp_query->query_vars['purchasekey'] ) ? $wp_query->query_vars['purchasekey'] : null,
			'email'       => isset( $wp_query->query_vars['email'] ) ? $wp_query->query_vars['email'] : null,
		);

		$data = array(
			'user_id' => $this->user_id,
			'api_key' => isset( $wp_query->query_vars['key'] ) ? $wp_query->query_vars['key'] : 'public',
			'token'   => isset( $wp_query->query_vars['token'] ) ? $wp_query->query_vars['token'] : 'public',
			'version' => $this->get_queried_version(),
			'request' => http_build_query( $query ),
			'error'   => ! empty( $data['error'] ) ? $data['error'] : '',
			'ip'      => edd_get_ip(),
			'time'    => $data['request_speed'],
		);

		edd_add_api_request_log( $data );
	}


	/**
	 * Retrieve the output data
	 *
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
	 * @since  1.5
	 * @global    $wp_query
	 *
	 * @param int $status_code HTTP status code.
	 */
	public function output( $status_code = 200 ) {
		$format = $this->get_output_format();

		status_header( $status_code );

		do_action( 'edd_api_output_before', $this->data, $this, $format );

		switch ( $format ) :

			case 'xml':
				require_once EDD_PLUGIN_DIR . 'includes/libraries/class-ArrayToXML.php';
				$arraytoxml = new ArrayToXML();
				$xml        = $arraytoxml->buildXML( $this->data, 'edd' );

				echo $xml;

				break;

			case 'json':
				header( 'Content-Type: application/json' );
				if ( ! empty( $this->pretty_print ) ) {
					echo json_encode( $this->data, $this->pretty_print );
				} else {
					echo json_encode( $this->data );
				}

				break;

			default:
				// Allow other formats to be added via extensions.
				do_action( 'edd_api_output_' . $format, $this->data, $this );

				break;

		endswitch;

		do_action( 'edd_api_output_after', $this->data, $this, $format );

		if ( defined( 'EDD_DOING_TESTS' ) && EDD_DOING_TESTS ) {
			edd_die();
		} else {
			die();
		}
	}

	/**
	 * Modify User Profile
	 *
	 * Modifies the output of profile.php to add key generation/revocation
	 *
	 * @since  1.5
	 * @deprecated 3.2.7 Use the edd_show_user_api_key_field function instead.
	 *
	 * @param object $user Current user info.
	 *
	 * @return void
	 */
	public function user_key_field( $user ) {
		_edd_deprecated_function( __FUNCTION__, '3.2.7', 'edd_show_user_api_key_field' );

		edd_show_user_api_key_field( $user );
	}

	/**
	 * Process an API key generation/revocation
	 *
	 * @since 2.0.0
	 *
	 * @param array $args The arguments passed to the API Key processing function.
	 *
	 * @return void
	 */
	public function process_api_key( $args ) {
		if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'edd-api-nonce' ) ) {
			wp_die( __( 'Nonce verification failed.', 'easy-digital-downloads' ), __( 'Error', 'easy-digital-downloads' ), array( 'response' => 403 ) );
		}

		if ( empty( $args['user_id'] ) ) {
			wp_die( __( 'User ID Required', 'easy-digital-downloads' ), __( 'Error', 'easy-digital-downloads' ), array( 'response' => 401 ) );
		}

		if ( is_numeric( $args['user_id'] ) ) {
			$user_id = isset( $args['user_id'] ) ? absint( $args['user_id'] ) : get_current_user_id();
		} else {
			$userdata = get_user_by( 'login', $args['user_id'] );
			$user_id  = $userdata->ID;
		}
		$process = isset( $args['edd_api_process'] ) ? strtolower( $args['edd_api_process'] ) : false;

		if (
			get_current_user_id() === $user_id &&
			! edd_get_option( 'allow_user_api_keys' ) &&
			! current_user_can( 'manage_shop_settings' )
		) {
			wp_die(
				sprintf(
					/* translators: %s: action being performed. */
					__( 'You do not have permission to %s API keys for this user', 'easy-digital-downloads' ),
					$process
				),
				__( 'Error', 'easy-digital-downloads' ),
				array( 'response' => 403 )
			);
		} elseif ( ! current_user_can( 'manage_shop_settings' ) ) {
			wp_die(
				sprintf(
					/* translators: %s: action being performed. */
					__( 'You do not have permission to %s API keys for this user', 'easy-digital-downloads' ),
					$process
				),
				__( 'Error', 'easy-digital-downloads' ),
				array( 'response' => 403 )
			);
		}

		switch ( $process ) {
			case 'generate':
				if ( $this->generate_api_key( $user_id ) ) {
					edd_redirect(
						edd_get_admin_url(
							array(
								'page'        => 'edd-tools',
								'tab'         => 'api_keys',
								'edd-message' => 'api-key-generated',
							)
						)
					);
				} else {
					edd_redirect(
						edd_get_admin_url(
							array(
								'page'        => 'edd-tools',
								'tab'         => 'api_keys',
								'edd-message' => 'api-key-failed',
							)
						)
					);
				}
				break;
			case 'regenerate':
				$this->generate_api_key( $user_id, true );
				edd_redirect(
					edd_get_admin_url(
						array(
							'page'        => 'edd-tools',
							'tab'         => 'api_keys',
							'edd-message' => 'api-key-regenerated',
						)
					)
				);
				break;
			case 'revoke':
				$this->revoke_api_key( $user_id );
				edd_redirect(
					edd_get_admin_url(
						array(
							'page'        => 'edd-tools',
							'tab'         => 'api_keys',
							'edd-message' => 'api-key-revoked',
						)
					)
				);
				break;
			default:
				break;
		}
	}

	/**
	 * Generate new API keys for a user
	 *
	 * @since 2.0.0
	 *
	 * @param int     $user_id    User ID the key is being generated for.
	 * @param boolean $regenerate Regenerate the key for the user.
	 *
	 * @return boolean True if (re)generated successfully, false otherwise.
	 */
	public function generate_api_key( $user_id = 0, $regenerate = false ) {
		if ( empty( $user_id ) ) {
			return false;
		}

		$user = get_userdata( $user_id );

		if ( ! $user ) {
			return false;
		}

		$public_key = $this->get_user_public_key( $user_id );

		if ( empty( $public_key ) || true === $regenerate ) {
			$new_public_key = $this->generate_public_key( $user->user_email );
			$new_secret_key = $this->generate_private_key( $user->ID );
		} else {
			return false;
		}

		if ( true === $regenerate ) {
			$this->revoke_api_key( $user->ID );
		}

		update_user_meta( $user_id, $new_public_key, 'edd_user_public_key' );
		update_user_meta( $user_id, $new_secret_key, 'edd_user_secret_key' );

		delete_transient( 'edd_total_api_keys' );

		return true;
	}

	/**
	 * Revoke a users API keys
	 *
	 * @since 2.0.0
	 *
	 * @param int $user_id User ID of user to revoke key for.
	 *
	 * @return string
	 */
	public function revoke_api_key( $user_id = 0 ) {
		if ( empty( $user_id ) ) {
			return false;
		}

		$user = get_userdata( $user_id );

		if ( ! $user ) {
			return false;
		}

		$public_key = $this->get_user_public_key( $user_id );
		$secret_key = $this->get_user_secret_key( $user_id );
		if ( ! empty( $public_key ) ) {
			delete_transient( md5( 'edd_api_user_' . $public_key ) );
			delete_transient( md5( 'edd_api_user_public_key' . $user_id ) );
			delete_transient( md5( 'edd_api_user_secret_key' . $user_id ) );
			delete_user_meta( $user_id, $public_key );
			delete_user_meta( $user_id, $secret_key );
		} else {
			return false;
		}

		delete_transient( 'edd_total_api_keys' );

		return true;
	}

	public function get_version() {
		return self::VERSION;
	}


	/**
	 * Generate and Save API key
	 *
	 * Generates the key requested by user_key_field and stores it in the database
	 *
	 * @since  1.5
	 *
	 * @param int $user_id The user ID.
	 *
	 * @return void
	 */
	public function update_key( $user_id ) {
		edd_update_user_api_key( $user_id );
	}

	/**
	 * Generate the public key for a user
	 *
	 * @access private
	 * @since  1.9.9
	 *
	 * @param string $user_email The user's email address.
	 *
	 * @return string
	 */
	public function generate_public_key( $user_email = '' ) {
		$auth_key = defined( 'AUTH_KEY' ) ? AUTH_KEY : '';
		$public   = hash( 'md5', $user_email . $auth_key . date( 'U' ) );

		return $public;
	}

	/**
	 * Generate the secret key for a user
	 *
	 * @access private
	 * @since  1.9.9
	 *
	 * @param int $user_id The user's ID.
	 *
	 * @return string
	 */
	public function generate_private_key( $user_id = 0 ) {
		$auth_key = defined( 'AUTH_KEY' ) ? AUTH_KEY : '';
		$secret   = hash( 'md5', $user_id . $auth_key . date( 'U' ) );

		return $secret;
	}

	/**
	 * Retrieve the user's token
	 *
	 * @access private
	 * @since  1.9.9
	 *
	 * @param int $user_id The user's ID.
	 *
	 * @return string
	 */
	public function get_token( $user_id = 0 ) {
		return hash( 'md5', $this->get_user_secret_key( $user_id ) . $this->get_user_public_key( $user_id ) );
	}

	/**
	 * Retrieve the last time this user's key was used.
	 *
	 * @since 3.2.7
	 *
	 * @param int $user_id The User ID.
	 *
	 * @return string|bool The last time the key was used (UTC), or false if it has never been used.
	 */
	public function get_last_used( $user_id ) {
		$logs = edd_get_api_request_logs(
			array(
				'user_id' => $user_id,
				'number'  => 1,
				'orderby' => 'date_created',
				'order'   => 'DESC',
			)
		);

		return ! empty( $logs ) ?
			$logs[0]->date_created : // This is in UTC time zone, directly from the database.
			false;
	}

	/**
	 * Generate the default sales stats returned by the 'stats' endpoint
	 *
	 * @access private
	 * @since  1.5.3
	 * @return array default sales statistics
	 */
	private function get_default_sales_stats() {

		$stats                   = new Stats(
			array(
				'range'        => 'today',
				'revenue_type' => 'net',
			)
		);
		$sales['sales']['today'] = $stats->get_order_count();

		$stats                           = new Stats(
			array(
				'range'        => 'this_month',
				'revenue_type' => 'net',
			)
		);
		$sales['sales']['current_month'] = $stats->get_order_count();

		$stats                        = new Stats(
			array(
				'range'        => 'last_month',
				'revenue_type' => 'net',
			)
		);
		$sales['sales']['last_month'] = $stats->get_order_count();

		$sales['sales']['totals'] = edd_get_total_sales();

		return $sales;
	}

	/**
	 * Generate the default earnings stats returned by the 'stats' endpoint
	 *
	 * @access private
	 * @since  1.5.3
	 * @return array default earnings statistics
	 */
	private function get_default_earnings_stats( $args ) {

		$stats                         = new Stats(
			array(
				'range'         => 'today',
				'exclude_taxes' => ! $args['include_tax'],
				'revenue_type'  => 'net',
				'output'        => 'typed',
			)
		);
		$earnings['earnings']['today'] = $stats->get_order_earnings();

		$stats                                 = new Stats(
			array(
				'range'         => 'this_month',
				'exclude_taxes' => ! $args['include_tax'],
				'revenue_type'  => 'net',
				'output'        => 'typed',
			)
		);
		$earnings['earnings']['current_month'] = $stats->get_order_earnings();

		$stats                              = new Stats(
			array(
				'range'         => 'last_month',
				'exclude_taxes' => ! $args['include_tax'],
				'revenue_type'  => 'net',
				'output'        => 'typed',
			)
		);
		$earnings['earnings']['last_month'] = $stats->get_order_earnings();

		$earnings['earnings']['totals'] = edd_get_total_earnings( $args['include_tax'] );

		return $earnings;
	}

	/**
	 * A Backwards Compatibility call for the change of meta_key/value for users API Keys
	 *
	 * @since  2.4
	 *
	 * @param  string $check     Wether to check the cache or not.
	 * @param  int    $object_id The User ID being passed.
	 * @param  string $meta_key  The user meta key.
	 * @param  bool   $single    If it should return a single value or array.
	 *
	 * @return string            The API key/secret for the user supplied
	 */
	public function api_key_backwards_copmat( $check, $object_id, $meta_key, $single ) {

		if ( 'edd_user_public_key' !== $meta_key && 'edd_user_secret_key' !== $meta_key ) {
			return $check;
		}

		$return = $check;

		switch ( $meta_key ) {
			case 'edd_user_public_key':
				$return = EDD()->api->get_user_public_key( $object_id );
				break;
			case 'edd_user_secret_key':
				$return = EDD()->api->get_user_secret_key( $object_id );
				break;
		}

		if ( ! $single ) {
			$return = array( $return );
		}

		return $return;
	}

	/**
	 * Sanitizes category and tag terms
	 *
	 * @access private
	 * @since  2.6
	 *
	 * @param mixed $term Request variable.
	 *
	 * @return mixed Sanitized term/s
	 */
	public function sanitize_request_term( $term ) {
		if ( is_array( $term ) ) {
			$term = array_map( 'sanitize_text_field', $term );
		} elseif ( is_int( $term ) ) {
			$term = absint( $term );
		} else {
			$term = sanitize_text_field( $term );
		}

		return $term;
	}

	/**
	 * Disable request logging
	 *
	 * @since  2.7
	 */
	public function log_requests() {
		switch ( $this->is_public_query() ) {
			case true:
				$log_requests = (bool) edd_get_option( 'enable_public_request_logs', false );
				break;
			default:
				$log_requests = true;
				break;
		}

		return apply_filters( 'edd_api_log_requests', $log_requests );
	}

	/**
	 * Check API keys vs token
	 *
	 * @since  2.8.2
	 *
	 * @param string $secret Secret key.
	 * @param string $public Public key.
	 * @param string $token  Token used in API request.
	 *
	 * @return bool
	 */
	public function check_keys( $secret, $public, $token ) {
		return hash_equals( md5( $secret . $public ), $token );
	}

	/**
	 * Flush previous API output. This is for unit tests only as we use a static fixture.
	 *
	 * @since 3.0
	 */
	public function flush_api_output() {
		if ( defined( 'EDD_UNIT_TESTS' ) ) {
			$this->data = array();
		}
	}
}
