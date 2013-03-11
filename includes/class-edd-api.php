<?php
/**
 * EDD API
 *
 * This class provides a front-facing JSON/XML API that makes it possible to query data from the shop.
 *
 * The primary purpose of this class is for external sales / earnings tracking systems, such as mobile
 *
 * @package  Easy Digital Downloads
 * @subpackage EDD API
 * @copyright Copyright (c) 2013, Pippin Williamson
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since  1.5
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * EDD API Class
 *
 * Renders API returns as a JSON array
 *
 * @access  private
 * @since  1.5
 */

class EDD_API {


	/**
	 * Pretty Print?
	 *
	 * @access  private
	 * @since  1.5
	 */

	private $pretty_print = false;


	/**
	 * Log API requests?
	 *
	 * @access  private
	 * @since  1.5
	 */

	private $log_requests = true;


	/**
	 * Is this a valid request?
	 *
	 * @access  private
	 * @since  1.5
	 */
	private $is_valid_request = true;


	/**
	 * Setup the API
	 *
	 * @access  private
	 * @author  Daniel J Griffiths
	 * @since  1.5
	 */

	function __construct() {

		add_action( 'init', array( $this, 'add_endpoint' ) );
      	add_action( 'template_redirect', array( $this, 'process_query' ), -1 );
		add_filter( 'query_vars', array( $this, 'query_vars' ) );
		add_action( 'show_user_profile', array( $this, 'user_key_field' ) );
		add_action( 'personal_options_update', array( $this, 'update_key' ) );

		// Determine if JSON_PRETTY_PRINT is available
		$this->pretty_print = defined( 'JSON_PRETTY_PRINT' ) ? JSON_PRETTY_PRINT : null;

		// Allow API request logging to be turned off
		$this->log_requests = apply_filters( 'edd_api_log_requests', $this->log_requests );

	}


	/**
	 * Register URL endpoint
	 *
	 * Registers a new endpoint for accessing the API
	 *
	 * @access  private
	 * @author  Daniel J Griffiths
	 * @since  1.5
	 */

	function add_endpoint( $rewrite_rules ) {
		add_rewrite_endpoint( 'edd-api', EP_ALL );
	}


	/**
	 * Register query vars
	 *
	 * Registers query vars for API access
	 *
	 * @access  private
	 * @author  Daniel J Griffiths
	 * @since  1.5
	 */

	function query_vars( $vars ) {
		$vars[] = 'user';
		$vars[] = 'key';
		$vars[] = 'query';
		$vars[] = 'type';
		$vars[] = 'product';
		$vars[] = 'number';
		$vars[] = 'date';
		$vars[] = 'startdate';
		$vars[] = 'enddate';
		$vars[] = 'customer';
		$vars[] = 'format';
		return $vars;
	}


	/**
	 * Validate the user email and API key
	 *
	 * Checks for the user email and API key and validates them
	 *
	 * @access  private
	 * @since  1.5
	 */

	private function validate_user() {

		global $wp_query;

		// Make sure we have both user and api key
		if ( empty( $wp_query->query_vars['user'] ) || empty( $wp_query->query_vars['key'] ) )
			$this->missing_auth();

		// Make sure username (email) exists
		if ( ! email_exists( $wp_query->query_vars['user'] ) )
			$this->invalid_email();

		// Check email/key combination
		if( ! $this->is_user_valid( $wp_query->query_vars['user'], $wp_query->query_vars['key'] ) )
			$this->invalid_key( $wp_query->query_vars['user'] );

	}


	/**
	 * Check if the user (email + API key) is valid
	 *
	 * @access  public
	 * @since  1.5
	 */

	public function is_user_valid( $email = '', $key = '' ) {

		$ret = false;

		$user = get_user_by( 'email', $email );

		if ( $user && $user->edd_user_api_key == $key )
			$ret = true;

		if( ! $ret )
			$this->is_valid_request = false;

		return $ret;
	}


	/**
	 * Missing authentication error
	 *
	 * @access  private
	 * @author  Daniel J Griffiths
	 * @since  1.5
	 */

	function missing_auth() {
		$error['error'] = __( 'You must specify both user and API key!', 'edd' );

		$this->is_valid_request = false;

		$this->output( $error );
	}


	/**
	 * Invalid email address error
	 *
	 * @access  private
	 * @author  Daniel J Griffiths
	 * @since  1.5
	 */

	function invalid_email() {
		$error['error'] = __( 'The email address specified is not registered!', 'edd' );

		$this->is_valid_request = false;

		$this->output( $error );
	}

	/**
	 * Invalid key error
	 *
	 * @access  private
	 * @author  Daniel J Griffiths
	 * @since  1.5
	 */

	function invalid_key( $email ) {
		$error['error'] = sprintf( __( 'Invalid API key for %s!', 'edd' ), $email );

		$this->is_valid_request = false;

		$this->output( $error );
	}


	/**
	 * Process API requests
	 *
	 * Listens for api calls
	 *
	 * @access  private
	 * @author  Daniel J Griffiths
	 * @since  1.5
	 */

	function process_query() {
		global $wp_query;

		// Check for edd-api var. Get out if not present
		if ( ! isset( $wp_query->query_vars['edd-api'] ) )
			return;

		// Check for a valid user and set errors if necessary
		$this->validate_user();

		// Only proceed if no errors have been noted
		if( ! $this->is_valid_request )
			return;

		// Determine the kind of query
		$query_mode = $this->get_query_mode();

		switch( $query_mode ) :

			case 'stats' :

				$data = $this->get_stats( array(
					'type'      => $wp_query->query_vars['type'],
					'product'   => isset( $wp_query->query_vars['product'] )   ? $wp_query->query_vars['product']   : null,
					'date'      => isset( $wp_query->query_vars['date'] )      ? $wp_query->query_vars['date']      : null,
					'startdate' => isset( $wp_query->query_vars['startdate'] ) ? $wp_query->query_vars['startdate'] : null,
					'enddate'   => isset( $wp_query->query_vars['enddate'] )   ? $wp_query->query_vars['enddate']   : null
				) );

				break;

			case 'products' :

				$product   = isset( $wp_query->query_vars['product'] )   ? $wp_query->query_vars['product']   : null;

				$data = $this->get_products( $product );

				break;

			case 'customers' :

				$customer  = isset( $wp_query->query_vars['customer'] ) ? $wp_query->query_vars['customer']  : null;

				$data = $this->get_customers( $customer );

				break;

			case 'sales' :

				$data   = $this->get_recent_sales();

				break;

		endswitch;

		// Log this API request, if enabled. We log it here because we have access to errors.
		$this->log_request( $data );

		// Send out data to the output function
		$this->output( $data );
	}


	/**
	 * Retrieve the query mode
	 *
	 * Determines the kind of query requested and also ensure it is a valid query
	 *
	 * @access  private
	 * @since  1.5
	 */

	private function get_query_mode() {

		global $wp_query;

		// Whitelist our query options
		$accepted = apply_filters( 'edd_api_valid_query_modes', array(
			'stats',
			'products',
			'customers',
			'sales'
		) );

		$query = isset( $wp_query->query_vars['edd-api'] ) ? $wp_query->query_vars['edd-api'] : null;

		// Make sure our query is valid
		if( ! in_array( $query, $accepted ) || ( $query == 'stats' && ! isset( $wp_query->query_vars['type'] )  ) ) {

			$error['error'] = __( 'Invalid query!', 'edd' );

			$this->output( $error );

		}

		return $query;
	}


	/**
	 * Get page number
	 *
	 * @access  private
	 * @since  1.5
	 */

	private function get_paged() {

		global $wp_query;

		return isset( $wp_query->query_vars['page'] ) ? $wp_query->query_vars['page'] : 1;

	}


	/**
	 * results per page
	 *
	 * @access  private
	 * @since  1.5
	 */

	private function per_page() {

		global $wp_query;

		$per_page = isset( $wp_query->query_vars['number'] ) ? $wp_query->query_vars['number'] : 10;

		return apply_filters( 'edd_api_results_per_page', $per_page );
	}


	/**
	 * Retrieve the output format
	 *
	 * Determines whether results should be displayed in XML or JSON
	 *
	 * @access  private
	 * @since  1.5
	 */

	private function get_output_format() {

		global $wp_query;

		$format = isset( $wp_query->query_vars['format'] ) ? $wp_query->query_vars['format'] : 'json';

		return apply_filters( 'edd_api_output_format', $format );

	}


	/**
	 * Get customers
	 *
	 * @access  private
	 * @author  Daniel J Griffiths
	 * @since  1.5
	 */

	function get_customers( $customer = null ) {
		if ( $customer == null ) {

			global $wpdb;

			$paged    = $this->get_paged();
			$per_page = $this->per_page();
			$offset   = $per_page * ( $paged - 1 );
			$customer_list_query = $wpdb->get_col( "SELECT DISTINCT meta_value FROM $wpdb->postmeta where meta_key = '_edd_payment_user_email' ORDER BY meta_id DESC LIMIT $per_page OFFSET $offset" );
			$customer_count = 0;

			foreach ( $customer_list_query as $customer_email ) {

				$customer_info = get_user_by( 'email', $customer_email );

				if( $customer_info ) {

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
	 * Get products
	 *
	 * @access  private
	 * @author  Daniel J Griffiths
	 * @since  1.5
	 */

	function get_products( $product = null ) {

		$products = array();

		if ( $product == null ) {

			$products['products'] = array();

			$product_list = get_posts( array( 'post_type' => 'download', 'posts_per_page' => $this->per_page(), 'paged' => $this->get_paged() ) );

			if( $product_list ) {
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
		//echo '<pre>'; print_r( $products ); echo '</pre>'; exit;
		return $products;

	}


	/**
	 * Get stats
	 *
	 * @access  private
	 * @author  Daniel J Griffiths
	 * @since  1.5
	 */

	function get_stats( $args = array() ) {

		$defaults = array(
			'type'      => '',
			'product'   => null,
			'date'      => null,
			'startdate' => null,
			'enddate'   => null
		);

		$args = wp_parse_args( $args, $defaults );

		$previous_month = date( 'n' ) == 1 ? 12 : date( 'n' ) - 1;
		$previous_year = $previous_month == 12 ? date( 'Y' ) - 1 : date( 'Y' );

		if ( date( 'j' ) == 1 ) {

			if ( date( 'n' ) == 3 ) {

				$yesterday = 28;

			} elseif ( date( 'n' ) == 5 || date( 'n' ) == 6 || date( 'n' ) == 10 || date( 'n' ) == 12 ) {

				$yesterday = 30;

			} else {

				$yesterday = 31;

			}

		} else {

			$yesterday = date( 'j' ) - 1;

		}

		if ( $args['type'] == 'sales' ) {

			if ( $args['product'] == null ) {

				if ( $args['date'] == null ) {

					$sales['sales']['current_month'] = edd_get_sales_by_date( null, date( 'n' ), date( 'Y' ) );
					$sales['sales']['last_month'] = edd_get_sales_by_date( null, $previous_month, $previous_year );
					$sales['sales']['totals'] = edd_get_total_sales();

				} elseif ( $args['date'] == 'today' ) {

					$sales['sales']['today'] = edd_get_sales_by_date( date( 'j' ), date( 'n' ), date( 'Y' ) );

				} elseif ( $args['date'] == 'yesterday' ) {

					$sales['sales']['yesterday'] = edd_get_sales_by_date( $yesterday, date( 'n' ), date( 'Y' ) );

				} elseif ( $args['date'] == 'range' ) {

					if ( isset( $args['startdate'] ) && isset( $args['enddate'] ) ) {

						if( $args['enddate'] < $args['startdate'] ) {

							$error['error'] = __( 'The end date must be later than the date date!', 'edd' );

						} else {

							global $wp_query;

							$args['startdate'] = DateTime::createFromFormat( 'Ymd', $args['startdate'] )->format( 'Y-m-d' );
							$args['enddate'] = DateTime::createFromFormat( 'Ymd', $args['enddate'] + 1 )->format( 'Y-m-d' );
							$daterange = new DatePeriod(
								new DateTime( $args['startdate'] ),
								new DateInterval( 'P1D' ),
								new DateTime( $args['enddate'] )
							);

							$sales['totals'] = 0;

							foreach ( $daterange as $day ) {

								$tag                  = ( $this->get_output_format() == 'xml' ? $day->format( 'MdY' ) : $day->format( 'Ymd' ) );
								$sale_count           = edd_get_sales_by_date( $day->format( 'j' ), $day->format( 'n' ), $day->format( 'Y' ) );
								$sales['sales'][$tag] = $sale_count;
								$sales['totals']      += $sale_count;

							}

						}

					} else {

						$error['error'] = __( 'Invalid or no date range specified!', 'edd' );

					}
				} else {

					$error['error'] = __( 'Invalid option for argument \'date\'!', 'edd' );

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

			if( ! empty( $error ) )
				return $error;

			return $sales;

		} elseif ( $args['type'] == 'earnings' ) {

			if ( $args['product'] == null ) {

				if ( $args['date'] == null ) {

					$earnings['earnings']['current_month'] = edd_get_earnings_by_date( null, date( 'n' ), date( 'Y' ) );
					$earnings['earnings']['last_month'] = edd_get_earnings_by_date( null, $previous_month, $previous_year );
					$earnings['earnings']['totals'] = edd_get_total_earnings();

				} elseif ( $args['date'] == 'today' ) {

					$earnings['earnings']['today'] = edd_get_earnings_by_date( date( 'j' ), date( 'n' ), date( 'Y' ) );

				} elseif ( $args['date'] == 'yesterday' ) {

					$earnings['earnings']['yesterday'] = edd_get_earnings_by_date( $yesterday, date( 'n' ), date( 'Y' ) );

				} elseif ( $args['date'] == 'range' ) {

					if ( isset( $args['startdate'] ) && isset( $args['enddate'] ) ) {

						if( $args['enddate'] < $args['startdate'] ) {

							$error['error'] = __( 'The end date must be later than the date date!', 'edd' );

						} else {

							global $wp_query;

							$args['startdate'] = DateTime::createFromFormat( 'Ymd', $args['startdate'] )->format( 'Y-m-d' );
							$args['enddate'] = DateTime::createFromFormat( 'Ymd', $args['enddate'] + 1 )->format( 'Y-m-d' );

							$daterange = new DatePeriod(
								new DateTime( $args['startdate'] ),
								new DateInterval( 'P1D' ),
								new DateTime( $args['enddate'] )
							);

							$earnings['totals'] = '0.00';

							foreach ( $daterange as $day ) {

								$tag                        = $this->get_output_format() == 'xml' ? $day->format( 'MdY' ) : $day->format( 'Ymd' );
								$earnings_count             = edd_get_earnings_by_date( $day->format( 'j' ), $day->format( 'n' ), $day->format( 'Y' ) );
								$earnings['earnings'][$tag] = $earnings_count;
								$earnings['totals']         += $earnings_count;
							}

						}

					} else {

						$error['error'] = __( 'Invalid or no date range specified!', 'edd' );


					}

				} else {

					$error['error'] = __( 'Invalid option for argument \'date\'!', 'edd' );

				}

			} elseif ( $args['product'] == 'all' ) {

				$products = get_posts( array( 'post_type' => 'download', 'nopaging' => true ) );

				$i = 0;

				foreach ( $products as $product_info ) {

					$earnings['earnings'][$i] = array( $product_info->post_name => edd_get_download_earnings_stats( $product_info->ID ) );
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

			if( ! empty( $error ) )
				return $error;

			return $earnings;

		} elseif ( $args['type'] == 'customers' ) {

			global $wpdb;

			$stats = array();

			$count = $wpdb->get_col( "SELECT COUNT(DISTINCT meta_value) FROM $wpdb->postmeta WHERE meta_key = '_edd_payment_user_email'" );
			$stats['customers']['total_customers'] = $count[0];

			return $stats;

		}

	}


	/**
	 * Retrieves recent sales
	 *
	 * @access public
	 * @since  1.5
	 * @return array
	 */

	function get_recent_sales() {

		$sales = array();

		$query = edd_get_payments( array( 'number' => $this->per_page(), 'page' => $this->get_paged() ) );

		if( $query ) {
			$i = 0;
			foreach( $query as $payment ) {

				$payment_meta          = edd_get_payment_meta( $payment->ID );
				$user_info             = edd_get_payment_meta_user_info( $payment->ID );
				$cart_items            = edd_get_payment_meta_cart_details( $payment->ID );

				$sales['sales'][$i]['ID']       = $payment->ID;
				$sales['sales'][$i]['key']      = edd_get_payment_key( $payment->ID );
				$sales['sales'][$i]['subtotal'] = edd_get_payment_subtotal( $payment->ID );
				$sales['sales'][$i]['tax']      = edd_get_payment_tax( $payment->ID );
				$sales['sales'][$i]['fees']     = edd_get_payment_fees( $payment->ID );
				$sales['sales'][$i]['total']    = edd_get_payment_amount( $payment->ID );
				$sales['sales'][$i]['gateway']  = edd_get_payment_gateway( $payment->ID );
				$sales['sales'][$i]['email']    = edd_get_payment_user_email( $payment->ID );
				$sales['sales'][$i]['date']     = $payment->post_date;
				$sales['sales'][$i]['products'] = array();

				$c = 0;
				foreach( $cart_items as $key => $item ) {

					$price_override = isset( $payment_meta['cart_details'] ) ? $item['price'] : null;
					$price          = edd_get_download_final_price( $item['id'], $user_info, $price_override );

					if ( isset( $cart_items[ $key ]['item_number'])) {
						$price_name     = '';
						$price_options  = $cart_items[ $key ]['item_number']['options'];
						if ( isset( $price_options['price_id'] ) ) {
							$price_name = edd_get_price_option_name( $item['id'], $price_options['price_id'], $payment->ID );
						}
					}

					$sales['sales'][$i]['products'][$c]['name']       = get_the_title( $item['id'] );
					$sales['sales'][$i]['products'][$c]['price']      = $price;
					$sales['sales'][$i]['products'][$c]['price_name'] = $price_name;
					$c++;

				}

				$i++;
			}

		}

		return $sales;

	}


	/**
	 * Log each API request, if enabled
	 *
	 * @access private
	 * @since  1.5
	 * @return void
	 */

	private function log_request( $data = array() ) {

		if( ! $this->log_requests )
			return;

		global $edd_logs, $wp_query;

		$query = array(
			'user'      => $wp_query->query_vars['user'],
			'api_key'   => $wp_query->query_vars['key'],
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
			'post_title'   => $wp_query->query_vars['user'] . ' ' . edd_get_ip(), // Makes logs easier to search by user / IP
			'post_excerpt' => http_build_query( $query ),
			'post_content' => ! empty( $data['error'] ) ? $data['error'] : '',
		);

		$log_meta = array(
			'request_ip' => edd_get_ip(),
			'user'       => $wp_query->query_vars['user'],
			'api_key'    => $wp_query->query_vars['key']
		);

		$edd_logs->insert_log( $log_data, $log_meta );

	}


	/**
	 * Output query return
	 *
	 * @access  private
	 * @author  Daniel J Griffiths
	 * @since  1.5
	 */

	function output( $data ) {
		global $wp_query;

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
				if( ! empty( $this->pretty_print ) )
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

		exit;
	}


	/**
	 * Modify user profile
	 *
	 * Modifies the output of profile.php to add key generation/revocation
	 *
	 * @access  private
	 * @author  Daniel J Griffiths
	 * @since  1.5
	 */

	function user_key_field( $user ) {
		if ( ( isset( $edd_options['api_allow_user_keys'] ) || current_user_can( 'manage_shop_settings' ) ) && current_user_can( 'view_shop_reports' ) ) {
			$user = get_userdata( $user->ID );
			?>
			<table class="form-table">
				<tbody>
					<tr>
						<th>
							<label for="edd_set_api_key"><?php _e( 'Easy Digital Downloads API Key', 'edd' ); ?></label>
						</th>
						<td>
							<?php if ( empty( $user->edd_user_api_key ) ) { ?>
							<input name="edd_set_api_key" type="checkbox" id="edd_set_api_key" value="0" />
							<span class="description"><?php _e( 'Generate API Key', 'edd' ); ?></span>
							<?php } else { ?>
								<span id="key"><?php echo $user->edd_user_api_key; ?></span><br/>
								<input name="edd_set_api_key" type="checkbox" id="edd_set_api_key" value="0" />
								<span class="description"><?php _e( 'Revoke API Key', 'edd' ); ?></span>
							<?php } ?>
						</td>
					</tr>
				</tbody>
			</table>
		<?php }
	}


	/**
	 * Generate and save API key
	 *
	 * Generates the key requested by user_key_field and stores it to the database
	 *
	 * @access  private
	 * @author  Daniel J Griffiths
	 * @since  1.5
	 */

	function update_key( $user_id ) {

		if ( current_user_can( 'edit_user', $user_id ) && isset( $_POST['edd_set_api_key'] ) ) {

			$user = get_userdata( $user_id );

			if ( empty( $user->edd_user_api_key ) ) {

				$hash = hash( 'md5', $user->user_email . date( 'U' ) );
				update_user_meta( $user_id, 'edd_user_api_key', $hash );

			} else {

				delete_user_meta( $user_id, 'edd_user_api_key' );

			}

		}

	}

}