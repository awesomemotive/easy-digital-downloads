<?php
/**
 * Easy Digital Downloads WP-CLI
 *
 * This class provides an integration point with the WP-CLI plugin allowing
 * access to EDD from the command line.
 *
 * @package		EDD
 * @subpackage	Classes/CLI
 * @copyright	Copyright (c) 2015, Pippin Williamson
 * @license		http://opensource.org/license/gpl-2.0.php GNU Public License
 * @since		2.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

WP_CLI::add_command( 'edd', 'EDD_CLI' );

/**
 * Work with EDD through WP-CLI
 *
 * EDD_CLI Class
 *
 * Adds CLI support to EDD through WP-CLI
 *
 * @since		2.0
 */
class EDD_CLI extends WP_CLI_Command {

	private $api;


	public function __construct() {
		$this->api = new EDD_API;
	}


	/**
	 * Get EDD details
	 *
	 * ## OPTIONS
	 *
	 * None. Returns basic info regarding your EDD instance.
	 *
	 * ## EXAMPLES
	 *
	 * wp edd details
	 *
	 * @access		public
	 * @param		array $args
	 * @param		array $assoc_args
	 * @return		void
	 */
	public function details( $args, $assoc_args ) {
		$symlink_file_downloads = edd_get_option( 'symlink_file_downloads', false );
		$purchase_page          = edd_get_option( 'purchase_page', '' );
		$success_page           = edd_get_option( 'success_page', '' );
		$failure_page           = edd_get_option( 'failure_page', '' );

		WP_CLI::line( sprintf( __( 'You are running EDD version: %s', 'easy-digital-downloads' ), EDD_VERSION ) );
		WP_CLI::line( "\n" . sprintf( __( 'Test mode is: %s', 'easy-digital-downloads' ), ( edd_is_test_mode() ? __( 'Enabled', 'easy-digital-downloads' ) : __( 'Disabled', 'easy-digital-downloads' ) ) ) );
		WP_CLI::line( sprintf( __( 'AJAX is: %s', 'easy-digital-downloads' ), ( edd_is_ajax_enabled() ? __( 'Enabled', 'easy-digital-downloads' ) : __( 'Disabled', 'easy-digital-downloads' ) ) ) );
		WP_CLI::line( sprintf( __( 'Guest checkouts are: %s', 'easy-digital-downloads' ), ( edd_no_guest_checkout() ? __( 'Disabled', 'easy-digital-downloads' ) : __( 'Enabled', 'easy-digital-downloads' ) ) ) );
		WP_CLI::line( sprintf( __( 'Symlinks are: %s', 'easy-digital-downloads' ), ( apply_filters( 'edd_symlink_file_downloads', isset( $symlink_file_downloads ) ) && function_exists( 'symlink' ) ? __( 'Enabled', 'easy-digital-downloads' ) : __( 'Disabled', 'easy-digital-downloads' ) ) ) );
		WP_CLI::line( "\n" . sprintf( __( 'Checkout page is: %s', 'easy-digital-downloads' ), ( ! edd_get_option( 'purchase_page', false ) ) ? __( 'Valid', 'easy-digital-downloads' ) : __( 'Invalid', 'easy-digital-downloads' ) ) );
		WP_CLI::line( sprintf( __( 'Checkout URL is: %s', 'easy-digital-downloads' ), ( ! empty( $purchase_page ) ? get_permalink( $purchase_page ) : __( 'Undefined', 'easy-digital-downloads' ) ) ) );
		WP_CLI::line( sprintf( __( 'Success URL is: %s', 'easy-digital-downloads' ), ( ! empty( $success_page ) ? get_permalink( $success_page ) : __( 'Undefined', 'easy-digital-downloads' ) ) ) );
		WP_CLI::line( sprintf( __( 'Failure URL is: %s', 'easy-digital-downloads' ), ( ! empty( $failure_page ) ? get_permalink( $failure_page ) : __( 'Undefined', 'easy-digital-downloads' ) ) ) );
		WP_CLI::line( sprintf( __( 'Downloads slug is: %s', 'easy-digital-downloads' ), ( defined( 'EDD_SLUG' ) ? '/' . EDD_SLUG : '/downloads' ) ) );
		WP_CLI::line( "\n" . sprintf( __( 'Taxes are: %s', 'easy-digital-downloads' ), ( edd_use_taxes() ? __( 'Enabled', 'easy-digital-downloads' ) : __( 'Disabled', 'easy-digital-downloads' ) ) ) );
		WP_CLI::line( sprintf( __( 'Tax rate is: %s', 'easy-digital-downloads' ), edd_get_tax_rate() * 100 . '%' ) );

		$rates = edd_get_tax_rates();
		if( ! empty( $rates ) ) {
			foreach( $rates as $rate ) {
				WP_CLI::line( sprintf( __( 'Country: %s, State: %s, Rate: %s', 'easy-digital-downloads' ), $rate['country'], $rate['state'], $rate['rate'] ) );
			}
		}
	}


	/**
	 * Get stats for your EDD site
	 *
	 * ## OPTIONS
	 *
	 * --product=<product_id>: The ID of a specific product to retrieve stats for, or all
	 * --date=[range|this_month|last_month|today|yesterday|this_quarter|last_quarter|this_year|last_year]: A specific date range to retrieve stats for
	 * --startdate=<date>: The start date of a date range to retrieve stats for
	 * --enddate=<date>: The end date of a date range to retrieve stats for
	 *
	 * ## EXAMPLES
	 *
	 * wp edd stats --date=this_month
	 * wp edd stats --start-date=01/02/2014 --end-date=02/23/2014
	 * wp edd stats --date=last_year
	 * wp edd stats --date=last_year --product=15
	 */
	public function stats( $args, $assoc_args ) {

		$stats      = new EDD_Payment_Stats;
		$date       = isset( $assoc_args ) && array_key_exists( 'date', $assoc_args )      ? $assoc_args['date']      : false;
		$start_date = isset( $assoc_args ) && array_key_exists( 'startdate', $assoc_args ) ? $assoc_args['startdate'] : false;
		$end_date   = isset( $assoc_args ) && array_key_exists( 'enddate', $assoc_args )   ? $assoc_args['enddate']   : false;
		$download   = isset( $assoc_args ) && array_key_exists( 'product', $assoc_args )   ? $assoc_args['product']   : 0;

		if( ! empty( $date ) ) {
			$start_date = $date;
			$end_date   = false;
		} elseif( empty( $date ) && empty( $start_date ) ) {
			$start_date = 'this_month';
			$end_date   = false;
		}

		$earnings   = $stats->get_earnings( $download, $start_date, $end_date );
		$sales      = $stats->get_sales( $download, $start_date, $end_date );

		WP_CLI::line( sprintf( __( 'Earnings: %s', 'easy-digital-downloads' ), $earnings ) );
		WP_CLI::line( sprintf( __( 'Sales: %s', 'easy-digital-downloads' ), $sales ) );

	}


	/**
	 * Get the products currently posted on your EDD site
	 *
	 * ## OPTIONS
	 *
	 * --id=<product_id>: A specific product ID to retrieve
	 *
	 *
	 * ## EXAMPLES
	 *
	 * wp edd products --id=103
	 */
	public function products( $args, $assoc_args ) {

		$product_id = isset( $assoc_args ) && array_key_exists( 'id', $assoc_args ) ? absint( $assoc_args['id'] ) : false;
		$products   = $this->api->get_products( $product_id );

		if( isset( $products['error'] ) ) {
			WP_CLI::error( $products['error'] );
		}

		if( empty( $products ) ) {
			WP_CLI::error( __( 'No Downloads found', 'easy-digital-downloads' ) );
			return;
		}

		foreach( $products['products'] as $product ) {

			$categories	= '';
			$tags       = '';
			$pricing    = array();

			if( is_array( $product['info']['category'] ) ) {

				$categories	= array();
				foreach( $product['info']['category'] as $category ) {
					$categories[] = $category->name;
				}

				$categories = implode( ', ', $categories );

			}

			if( is_array( $product['info']['tags'] ) ) {

				$tags = array();
				foreach( $product['info']['tags'] as $tag ) {

					$tags[] = $tag->name;

				}

				$tags = implode( ', ', $tags );

			}

			foreach( $product['pricing'] as $price => $value ) {

				if( 'amount' != $price ) {
					$price = $price . ' - ';
				}

				$pricing[] = $price . ': ' . edd_format_amount( $value ) . ' ' . edd_get_currency();
			}

			$pricing = implode( ', ', $pricing );

			WP_CLI::line( WP_CLI::colorize( '%G' . $product['info']['title'] . '%N' ) );
			WP_CLI::line( sprintf( __( 'ID: %d', 'easy-digital-downloads' ), $product['info']['id'] ) );
			WP_CLI::line( sprintf( __( 'Status: %s', 'easy-digital-downloads' ), $product['info']['status'] ) );
			WP_CLI::line( sprintf( __( 'Posted: %s', 'easy-digital-downloads' ), $product['info']['create_date'] ) );
			WP_CLI::line( sprintf( __( 'Categories: %s', 'easy-digital-downloads' ), $categories ) );
			WP_CLI::line( sprintf( __( 'Tags: %s', 'easy-digital-downloads' ), ( is_array( $tags ) ? '' : $tags ) ) );
			WP_CLI::line( sprintf( __( 'Pricing: %s', 'easy-digital-downloads' ), $pricing ) );
			WP_CLI::line( sprintf( __( 'Sales: %s', 'easy-digital-downloads' ), $product['stats']['total']['sales'] ) );
			WP_CLI::line( sprintf( __( 'Earnings: %s', 'easy-digital-downloads' ), edd_format_amount( $product['stats']['total']['earnings'] ) ) ) . ' ' . edd_get_currency();
			WP_CLI::line( '' );
			WP_CLI::line( sprintf( __( 'Slug: %s', 'easy-digital-downloads' ), $product['info']['slug'] ) );
			WP_CLI::line( sprintf( __( 'Permalink: %s', 'easy-digital-downloads' ), $product['info']['link'] ) );

			if( array_key_exists( 'files', $product ) ) {

				WP_CLI::line( '' );
				WP_CLI::line( __( 'Download Files:', 'easy-digital-downloads' ) );

				foreach( $product['files'] as $file ) {

					WP_CLI::line( '  ' . sprintf( __( 'File: %s (%s)', 'easy-digital-downloads' ), $file['name'], $file['file'] ) );

					if( isset( $file['condition'] ) && 'all' !== $file['condition'] ) {

						WP_CLI::line( '  ' . sprintf( __( 'Price Assignment: %s', 'easy-digital-downloads' ), $file['condition'] ) );

					}

				}

			}

			WP_CLI::line( '' );
		}

	}


	/**
	 * Get the customers currently on your EDD site. Can also be used to create customers records
	 *
	 * ## OPTIONS
	 *
	 * --id=<customer_id>: A specific customer ID to retrieve
	 * --email=<customer_email>: The email address of the customer to retrieve
	 * --create=<number>: The number of arbitrary customers to create. Leave as 1 or blank to create a customer with a speciific email
	 *
	 * ## EXAMPLES
	 *
	 * wp edd customers --id=103
	 * wp edd customers --email=john@test.com
	 * wp edd customers --create=1 --email=john@test.com
	 * wp edd customers --create=1 --email=john@test.com --name="John Doe"
	 * wp edd customers --create=1 --email=john@test.com --name="John Doe" user_id=1
	 * wp edd customers --create=1000
	 */
	public function customers( $args, $assoc_args ) {

		$customer_id = isset( $assoc_args ) && array_key_exists( 'id', $assoc_args )      ? absint( $assoc_args['id'] ) : false;
		$email       = isset( $assoc_args ) && array_key_exists( 'email', $assoc_args )   ? $assoc_args['email']        : false;
		$name        = isset( $assoc_args ) && array_key_exists( 'name', $assoc_args )    ? $assoc_args['name']         : null;
		$user_id     = isset( $assoc_args ) && array_key_exists( 'user_id', $assoc_args ) ? $assoc_args['user_id']      : null;
		$create      = isset( $assoc_args ) && array_key_exists( 'create', $assoc_args )  ? $assoc_args['create']       : false;
		$start       = time();

		if( $create ) {

			$number = 1;

			// Create one or more customers
			if( ! $email ) {

				// If no email is specified, look to see if we are generating arbitrary customer accounts
				$number = is_numeric( $create ) ? absint( $create ) : 1;

			}

			for( $i = 0; $i < $number; $i++ ) {

				if( ! $email ) {

					// Generate fake email
					$email = 'customer-' . uniqid() . '@test.com';

				}

				$args = array(
					'email'   => $email,
					'name'    => $name,
					'user_id' => $user_id
				);

				$customer_id = edd_add_customer( $args );

				if( $customer_id ) {
					WP_CLI::line( sprintf( __( 'Customer %d created successfully', 'easy-digital-downloads' ), $customer_id ) );
				} else {
					WP_CLI::error( __( 'Failed to create customer', 'easy-digital-downloads' ) );
				}

				// Reset email to false so it is generated on the next loop (if creating customers)
				$email = false;

			}

			WP_CLI::line( WP_CLI::colorize( '%G' . sprintf( __( '%d customers created in %d seconds', 'easy-digital-downloads' ), $create, time() - $start ) . '%N' ) );

		} else {

			// Search for customers

			$search = false;

			// Checking if search is being done by id, email or user_id fields.
			if ( $customer_id || $email || ( 'null' !== $user_id ) ) {
				$search           = array();
				$customer_details = array();

				if ( $customer_id ) {
					$customer_details['id'] = $customer_id;
				} elseif ( $email ) {
					$customer_details['email'] = $email;
				} elseif ( null !== $user_id ) {
					$customer_details['user_id'] = $user_id;
				}

				$search['customer'] = $customer_details;
			}

			$customers = $this->api->get_customers( $search );

			if( isset( $customers['error'] ) ) {
				WP_CLI::error( $customers['error'] );
			}

			if( empty( $customers ) ) {
				WP_CLI::error( __( 'No customers found', 'easy-digital-downloads' ) );
				return;
			}

			foreach( $customers['customers'] as $customer ) {
				WP_CLI::line( WP_CLI::colorize( '%G' . $customer['info']['email'] . '%N' ) );
				WP_CLI::line( sprintf( __( 'Customer User ID: %s', 'easy-digital-downloads' ), $customer['info']['id'] ) );
				WP_CLI::line( sprintf( __( 'Username: %s', 'easy-digital-downloads' ), $customer['info']['username'] ) );
				WP_CLI::line( sprintf( __( 'Display Name: %s', 'easy-digital-downloads' ), $customer['info']['display_name'] ) );

				if( array_key_exists( 'first_name', $customer ) ) {
					WP_CLI::line( sprintf( __( 'First Name: %s', 'easy-digital-downloads' ), $customer['info']['first_name'] ) );
				}

				if( array_key_exists( 'last_name', $customer ) ) {
					WP_CLI::line( sprintf( __( 'Last Name: %s', 'easy-digital-downloads' ), $customer['info']['last_name'] ) );
				}

				WP_CLI::line( sprintf( __( 'Email: %s', 'easy-digital-downloads' ), $customer['info']['email'] ) );

				WP_CLI::line( '' );
				WP_CLI::line( sprintf( __( 'Purchases: %s', 'easy-digital-downloads' ), $customer['stats']['total_purchases'] ) );
				WP_CLI::line( sprintf( __( 'Total Spent: %s', 'easy-digital-downloads' ), edd_format_amount( $customer['stats']['total_spent'] ) . ' ' . edd_get_currency() ) );
				WP_CLI::line( sprintf( __( 'Total Downloads: %s', 'easy-digital-downloads' ), $customer['stats']['total_downloads'] ) );

				WP_CLI::line( '' );
			}

		}

	}


	/**
	 * Get the recent sales for your EDD site
	 *
	 * ## OPTIONS
	 *
	 * 	 --email=<customer_email>: The email address of the customer to retrieve
	 *
	 * ## EXAMPLES
	 *
	 * wp edd sales
	 * wp edd sales --email=john@test.com
	 */
	public function sales( $args, $assoc_args ) {

		$email = isset( $assoc_args ) && array_key_exists( 'email', $assoc_args )  ? $assoc_args['email'] : '';

		global $wp_query;

		$wp_query->query_vars['email'] = $email;

		$sales = $this->api->get_recent_sales();

		if( empty( $sales ) ) {
			WP_CLI::error( __( 'No sales found', 'easy-digital-downloads' ) );
			return;
		}

		foreach( $sales['sales'] as $sale ) {
			WP_CLI::line( WP_CLI::colorize( '%G' . $sale['ID'] . '%N' ) );
			WP_CLI::line( sprintf( __( 'Purchase Key: %s', 'easy-digital-downloads' ), $sale['key'] ) );
			WP_CLI::line( sprintf( __( 'Email: %s', 'easy-digital-downloads' ), $sale['email'] ) );
			WP_CLI::line( sprintf( __( 'Date: %s', 'easy-digital-downloads' ), $sale['date'] ) );
			WP_CLI::line( sprintf( __( 'Subtotal: %s', 'easy-digital-downloads' ), edd_format_amount( $sale['subtotal'] ) . ' ' . edd_get_currency() ) );
			WP_CLI::line( sprintf( __( 'Tax: %s', 'easy-digital-downloads' ), edd_format_amount( $sale['tax'] ) . ' ' . edd_get_currency() ) );

			if( array_key_exists( 0, $sale['fees'] ) ) {
				WP_CLI::line( __( 'Fees:', 'easy-digital-downloads' ) );

				foreach( $sale['fees'] as $fee ) {
					WP_CLI::line( sprintf( __( '  Fee: %s - %s', 'easy-digital-downloads' ), edd_format_amount( $fee['amount'] ), edd_get_currency() ) );
				}
			}

			WP_CLI::line( sprintf( __( 'Total: %s', 'easy-digital-downloads' ), edd_format_amount( $sale['total'] ) . ' ' . edd_get_currency() ) );
			WP_CLI::line( '' );
			WP_CLI::line( sprintf( __( 'Gateway: %s', 'easy-digital-downloads' ), $sale['gateway'] ) );

			if( array_key_exists( 0, $sale['products'] ) ) {
				WP_CLI::line( __( 'Products:', 'easy-digital-downloads' ) );

				foreach( $sale['products'] as $product ) {
					$price_name = ! empty( $product['price_name'] ) ? ' (' . $product['price_name'] . ')' : '';
					WP_CLI::line( sprintf( __( '  Product: %s - %s', 'easy-digital-downloads' ), $product['name'], edd_format_amount( $product['price'] ) . ' ' . edd_get_currency() . $price_name ) );
				}
			}

			WP_CLI::line( '' );
		}
	}


	/**
	 * Get discount details for on your EDD site
	 *
	 * ## OPTIONS
	 *
	 * --id=<discount_id>: A specific discount ID to retrieve
	 *
	 * ## EXAMPLES
	 *
	 * wp edd discounts --id=103
	 */
	public function discounts( $args, $assoc_args ) {

		$discount_id = isset( $assoc_args ) && array_key_exists( 'id', $assoc_args ) ? absint( $assoc_args['id'] ) : false;

		$discounts = $this->api->get_discounts( $discount_id );

		if ( isset( $discounts['error'] ) ) {
			WP_CLI::error( $discounts['error'] );
		}

		if ( empty( $discounts ) ) {
			WP_CLI::error( __( 'No discounts found', 'easy-digital-downloads' ) );

			return;
		}

		foreach ( $discounts['discounts'] as $discount ) {
			WP_CLI::line( WP_CLI::colorize( '%G' . $discount['ID'] . '%N' ) );
			WP_CLI::line( sprintf( __( 'Name: %s', 'easy-digital-downloads' ), $discount['name'] ) );
			WP_CLI::line( sprintf( __( 'Code: %s', 'easy-digital-downloads' ), $discount['code'] ) );

			if ( $discount['type'] == 'percent' ) {
				$amount = $discount['amount'] . '%';
			} else {
				$amount = edd_format_amount( $discount['amount'] ) . ' ' . edd_get_currency();
			}

			WP_CLI::line( sprintf( __( 'Amount: %s', 'easy-digital-downloads' ), $amount ) );
			WP_CLI::line( sprintf( __( 'Uses: %s', 'easy-digital-downloads' ), $discount['uses'] ) );
			WP_CLI::line( sprintf( __( 'Max Uses: %s', 'easy-digital-downloads' ), ( $discount['max_uses'] == '0' ? __( 'Unlimited', 'easy-digital-downloads' ) : $discount['max_uses'] ) ) );
			WP_CLI::line( sprintf( __( 'Start Date: %s', 'easy-digital-downloads' ), ( empty( $discount['start_date'] ) ? __( 'No Start Date', 'easy-digital-downloads' ) : $discount['start_date'] ) ) );
			WP_CLI::line( sprintf( __( 'Expiration Date: %s', 'easy-digital-downloads' ), ( empty( $discount['exp_date'] ) ? __( 'No Expiration', 'easy-digital-downloads' ) : $discount['exp_date'] ) ) );
			WP_CLI::line( sprintf( __( 'Status: %s', 'easy-digital-downloads' ), ucwords( $discount['status'] ) ) );

			WP_CLI::line( '' );

			if ( array_key_exists( 0, $discount['product_requirements'] ) ) {
				WP_CLI::line( __( 'Product Requirements:', 'easy-digital-downloads' ) );

				foreach ( $discount['product_requirements'] as $req => $req_id ) {
					WP_CLI::line( sprintf( __( '  Product: %s', 'easy-digital-downloads' ), $req_id ) );
				}
			}

			WP_CLI::line( '' );

			WP_CLI::line( sprintf( __( 'Global Discount: %s', 'easy-digital-downloads' ), ( empty( $discount['global_discount'] ) ? 'False' : 'True' ) ) );
			WP_CLI::line( sprintf( __( 'Single Use: %s', 'easy-digital-downloads' ), ( empty( $discount['single_use'] ) ? 'False' : 'True' ) ) );

			WP_CLI::line( '' );
		}
	}

	/**
	 * Create sample purchase data for your EDD site
	 *
	 * ## OPTIONS
	 *
	 * --number: The number of purchases to create
	 * --status=<status>: The status to create purchases as
	 * --id=<product_id>: A specific product to create purchase data for
	 * --price_id=<price_id>: A price ID of the specified product
	 *
	 * ## EXAMPLES
	 *
	 * wp edd payments create --number=10 --status=complete
	 * wp edd payments create --number=10 --id=103
	 */
	public function payments( $args, $assoc_args ) {

		$error = false;

		// At some point we'll likely add another action for payments
		if( ! isset( $args ) ||  count( $args ) == 0 ) {
			$error = __( 'No action specified, did you mean', 'easy-digital-downloads' );
		} elseif( isset( $args ) && ! in_array( 'create', $args ) ) {
			$error = __( 'Invalid action specified, did you mean', 'easy-digital-downloads' );
		}

		if( $error ) {
			foreach( $assoc_args as $key => $value ) {
				$query .= ' --' . $key . '=' . $value;
			}

			WP_CLI::error(
				sprintf( $error . ' %s?', 'wp edd payments create' . $query )
			);

			return;
		}


		// Setup some defaults
		$number     = 1;
		$status     = 'complete';
		$id         = false;
		$price_id   = false;
		$tax        = 0;
		$email      = 'guest@edd.local';
		$fname      = 'Pippin';
		$lname      = 'Williamson';
		$date       = false;
		$range      = 30;

		$generate_users = false;

		if( count( $assoc_args ) > 0 ) {
			$number     = ( array_key_exists( 'number', $assoc_args ) )   ? absint( $assoc_args['number'] )             : $number;
			$id         = ( array_key_exists( 'id', $assoc_args ) )       ? absint( $assoc_args['id'] )                 : $id;
			$price_id   = ( array_key_exists( 'price_id', $assoc_args ) ) ? absint( $assoc_args['id'] )                 : $price_id;
			$tax        = ( array_key_exists( 'tax', $assoc_args ) )      ? floatval( $assoc_args['tax'] )              : $tax;
			$email      = ( array_key_exists( 'email', $assoc_args ) )    ? sanitize_email( $assoc_args['email'] )      : $email;
			$fname      = ( array_key_exists( 'fname', $assoc_args ) )    ? sanitize_text_field( $assoc_args['fname'] ) : $fname;
			$lname      = ( array_key_exists( 'lname', $assoc_args ) )    ? sanitize_text_field( $assoc_args['lname'] ) : $lname;
			$date       = ( array_key_exists( 'date', $assoc_args ) )     ? sanitize_text_field( $assoc_args['date'] )  : $date;
			$range      = ( array_key_exists( 'range', $assoc_args ) )    ? absint( $assoc_args['range'] )              : $range;

			$generate_users = ( array_key_exists( 'generate_users', $assoc_args ) ) ? (bool) absint( $assoc_args['generate_users'] ) : $generate_users;

			// Status requires a bit more validation
			if( array_key_exists( 'status', $assoc_args ) ) {
				$stati = array(
					'publish',
					'complete',
					'pending',
					'refunded',
					'revoked',
					'failed',
					'abandoned',
					'preapproval',
					'cancelled'
				);

				if( in_array( $assoc_args['status'], $stati ) ) {
					$status = ( $assoc_args['status'] == 'complete' ) ? 'publish' : $assoc_args['status'];
				} else {
					WP_CLI::warning( sprintf(
						__( "Invalid status '%s', defaulting to 'complete'", 'easy-digital-downloads' ),
						$assoc_args['status']
					) );
				}
			}
		}

		// Build the user info array
		$user_info = array(
			'id'            => 0,
			'email'         => $email,
			'first_name'    => $fname,
			'last_name'     => $lname,
			'discount'      => 'none'
		);

		$progress = \WP_CLI\Utils\make_progress_bar( 'Creating Payments', $number );

		for( $i = 0; $i < $number; $i++ ) {

			$products = array();
			$total    = 0;

			// No specified product
			if( ! $id ) {

				$products = get_posts( array(
					'post_type'     => 'download',
					'orderby'       => 'rand',
					'order'         => 'ASC',
					'posts_per_page'=> rand( 1, 3 ),
				) );

			} else {

				$product = get_post( $id );

				if( $product->post_type != 'download' ) {
					WP_CLI::error( __( 'Specified ID is not a product', 'easy-digital-downloads' ) );
					return;
				}

				$products[] = $product;

			}

			$cart_details = array();

			// Create the purchases
			foreach( $products as $key => $download ) {

				if( ! is_a( $download, 'WP_Post' ) ) {
					continue;
				}

				$options = array();
				$final_downloads = array();

				// Deal with variable pricing
				if( edd_has_variable_prices( $download->ID ) ) {

					$prices = edd_get_variable_prices( $download->ID );

					if( false === $price_id || ! array_key_exists( $price_id, (array) $prices ) ) {
						$item_price_id = array_rand( $prices );
					} else {
						$item_price_id = $price_id;
					}

					$item_price = $prices[ $item_price_id ]['amount'];
					$options['price_id'] = $item_price_id;

				} else {

					$item_price = edd_get_download_price( $download->ID );

				}

				$item_number = array(
					'id'       => $download->ID,
					'quantity' => 1,
					'options'  => $options
				);

				$cart_details[$key] = array(
					'name'        => $download->post_title,
					'id'          => $download->ID,
					'item_number' => $item_number,
					'item_price'  => edd_sanitize_amount( $item_price ),
					'subtotal'    => edd_sanitize_amount( $item_price ),
					'price'	      => edd_sanitize_amount( $item_price ),
					'quantity'    => 1,
					'discount'    => 0,
					'tax'         => $tax
				);

				$final_downloads[$key] = $item_number;

				$total += $item_price;

			}

			if ( 'random' === $date ) {
				// Randomly grab a date from the current past 30 days
				$oldest_time = strtotime( '-' . $range . ' days', current_time( 'timestamp') );
				$newest_time = current_time( 'timestamp' );

				$timestamp   = rand( $oldest_time, $newest_time );
				$timestring  = date( "Y-m-d H:i:s", $timestamp );
			} elseif ( empty( $date ) ) {
				$timestring = false;
			} else {
				if ( is_numeric( $date ) ) {
					$timestring = date( "Y-m-d H:i:s", $date );
				} else {
					$parsed_time = strtotime( $date );
					$timestring = date( "Y-m-d H:i:s", $parsed_time );
				}
			}

			if ( $generate_users ) {
				$fname  = $this->get_fname();
				$lname  = $this->get_lname();
				$domain = $this->get_domain();
				$tld    = $this->get_tld();

				$email  = $fname . '.' . $lname . '@' . $domain . '.' . $tld;

				$user_info = array(
					'id'            => 0,
					'email'         => $email,
					'first_name'    => $fname,
					'last_name'     => $lname,
					'discount'      => 'none'
				);
			}

			$purchase_data = array(
				'price'	        => edd_sanitize_amount( $total ),
				'tax'           => 0,
				'purchase_key'  => strtolower( md5( uniqid() ) ),
				'user_email'    => $email,
				'user_info'     => $user_info,
				'currency'      => edd_get_currency(),
				'downloads'     => $final_downloads,
				'cart_details'  => $cart_details,
				'status'        => 'pending',
			);

			if ( ! empty( $timestring ) ) {
				$purchase_data['post_date'] = $timestring;
			}

			$payment_id = edd_insert_payment( $purchase_data );

			remove_action( 'edd_complete_purchase', 'edd_trigger_purchase_receipt', 999 );

			if( $status != 'pending' ) {
				edd_update_payment_status( $payment_id, $status );
			}

			if ( ! empty( $timestring ) ) {
				$payment = new EDD_Payment( $payment_id );
				$payment->completed_date = $timestring;
				$payment->save();
			}

			$progress->tick();

		}

		$progress->finish();

		WP_CLI::success( sprintf( __( 'Created %s payments', 'easy-digital-downloads' ), $number ) );
		return;
	}

	/**
	 * Create discount codes for your EDD site
	 *
	 * ## OPTIONS
	 *
	 * --legacy: Create legacy discount codes using pre-3.0 schema
	 * --number: The number of discounts to create
	 *
	 * ## EXAMPLES
	 *
	 * wp edd create_discounts --number=100
	 * wp edd create_discounts --number=50 --legacy
	 */
	public function create_discounts( $args, $assoc_args ) {
		$number = array_key_exists( 'number', $assoc_args ) ? absint( $assoc_args['number'] ) : 1;
		$legacy = array_key_exists( 'legacy', $assoc_args ) ? true : false;

		$progress = \WP_CLI\Utils\make_progress_bar( 'Creating Discount Codes', $number );

		for ( $i = 0; $i < $number; $i++ ) {
			if ( $legacy ) {
				$discount_id = wp_insert_post( array(
					'post_type'   => 'edd_discount',
					'post_title'  => 'Auto-Generated Legacy Discount #' . $i,
					'post_status' => 'active',
				) );

				$download_ids = get_posts( array(
					'post_type'      => 'download',
					'posts_per_page' => 2,
					'fields'         => 'ids',
					'orderby'        => 'rand',
				) );

				$meta = array(
					'code'              => 'LEGACY' . $i,
					'status'            => 'active',
					'uses'              => 10,
					'max_uses'          => 20,
					'name'              => 'Auto-Generated Legacy Discount #' . $i,
					'amount'            => 20,
					'start'             => '01/01/2000 00:00:00',
					'expiration'        => '12/31/2050 23:59:59',
					'type'              => 'percent',
					'min_price'         => '10.50',
					'product_reqs'      => array( $download_ids[0] ),
					'product_condition' => 'all',
					'excluded_products' => array( $download_ids[1] ),
					'is_not_global'     => true,
					'is_single_use'     => true,
				);

				remove_action( 'pre_get_posts', '_edd_discount_get_post_doing_it_wrong', 99, 1 );
				remove_filter( 'add_post_metadata', '_edd_discount_update_meta_backcompat', 99 );

				foreach ( $meta as $key => $value ) {
					add_post_meta( $discount_id, '_edd_discount_' . $key, $value );
				}

				add_filter( 'add_post_metadata', '_edd_discount_update_meta_backcompat', 99, 5 );
				add_action( 'pre_get_posts', '_edd_discount_get_post_doing_it_wrong', 99, 1 );
			} else {
				$type = array( 'flat', 'percent' );
				$status = array( 'active', 'inactive' );
				$product_condition = array( 'any', 'all' );

				$type_index = array_rand( $type, 1 );
				$status_index = array_rand( $status, 1 );
				$product_condition_index = array_rand( $product_condition, 1 );

				$post = array(
					'code'              => md5( time() ),
					'uses'              => mt_rand( 0, 100 ),
					'max'               => mt_rand( 0, 100 ),
					'name'              => 'Auto-Generated Discount #' . $i,
					'type'              => $type[ $type_index ],
					'amount'            => mt_rand( 10, 95 ),
					'start'             => '12/12/2010 00:00:00',
					'expiration'        => '12/31/2050 23:59:59',
					'min_price'         => mt_rand( 30, 255 ),
					'status'            => $status[ $status_index ],
					'product_condition' => $product_condition[ $product_condition_index ],
				);

				edd_store_discount( $post );

				$progress->tick();
			}
		}

		$progress->finish();

		WP_CLI::success( sprintf( __( 'Created %s discounts', 'easy-digital-downloads' ), $number ) );
		return;
	}

	/**
	 * Migrate Discounts to the custom tables
	 *
	 * ## OPTIONS
	 *
	 * --force=<boolean>: If the routine should be run even if the upgrade routine has been run already
	 *
	 * ## EXAMPLES
	 *
	 * wp edd migrate_discounts
	 * wp edd migrate_discounts --force
	 */
	public function migrate_discounts( $args, $assoc_args ) {
		global $wpdb;
		$force = isset( $assoc_args['force'] ) ? true : false;

		$upgrade_completed = edd_has_upgrade_completed( 'migrate_discounts' );

		if ( ! $force && $upgrade_completed ) {
			WP_CLI::error( __( 'The discounts custom database migration has already been run. To do this anyway, use the --force argument.', 'eddc' ) );
		}

		$discounts_db = edd_get_component_interface( 'discount', 'table' );
		if ( ! $discounts_db->exists() ) {
			@$discounts_db->create();
		}

		$discount_meta = edd_get_component_interface( 'discount', 'meta' );
		if ( ! $discount_meta->exists() ) {
			@$discount_meta->create();
		}

		$sql = "SELECT * FROM $wpdb->posts WHERE post_type = 'edd_discount'";
		$results = $wpdb->get_results( $sql );
		$total = count( $results );

		if ( ! empty( $total ) ) {

			$progress = new \cli\progress\Bar( 'Migrating Discounts', $total );

			foreach ( $results as $old_discount ) {
				$discount = new EDD_Discount;
				$discount->migrate( $old_discount->ID );
				$progress->tick();
			}

			$progress->finish();

			WP_CLI::line( __( 'Migration complete.', 'easy-digital-downloads' ) );
			$new_count = count( edd_get_discounts( array( 'number' => -1 ) ) );
			$old_count = $wpdb->get_col( "SELECT count(ID) FROM $wpdb->posts WHERE post_type ='edd_discount'", 0 );
			WP_CLI::line( __( 'Old Records: ', 'easy-digital-downloads' ) . $old_count[0] );
			WP_CLI::line( __( 'New Records: ', 'easy-digital-downloads' ) . $new_count );

			update_option( 'edd_version', preg_replace( '/[^0-9.].*/', '', EDD_VERSION ) );
			edd_set_upgrade_complete( 'migrate_discounts' );

			WP_CLI::confirm( __( 'Remove legacy discount records?', 'easy-digital-downloads' ), $remove_args = array() );
			WP_CLI::line( __( 'Removing old discount data.', 'easy-digital-downloads' ) );

			$discount_ids = $wpdb->get_results( "SELECT ID FROM $wpdb->posts WHERE post_type = 'edd_discount'" );
			$discount_ids = wp_list_pluck( $discount_ids, 'ID' );
			$discount_ids = implode( ', ', $discount_ids );

			$delete_posts_query = "DELETE FROM $wpdb->posts WHERE ID IN ({$discount_ids})";
			$wpdb->query( $delete_posts_query );

			$delete_postmeta_query = "DELETE FROM $wpdb->postmeta WHERE post_id IN ({$discount_ids})";
			$wpdb->query( $delete_postmeta_query );

			edd_set_upgrade_complete( 'remove_legacy_discounts' );

		} else {

			WP_CLI::line( __( 'No discount records found.', 'easy-digital-downloads' ) );
			edd_set_upgrade_complete( 'migrate_discounts' );
			edd_set_upgrade_complete( 'remove_legacy_discounts' );

		}
	}

	/**
	 * Migrate notes to the custom tables.
	 *
	 * ## OPTIONS
	 *
	 * --force=<boolean>: If the routine should be run even if the upgrade routine has been run already
	 *
	 * ## EXAMPLES
	 *
	 * wp edd migrate_notes
	 * wp edd migrate_notes --force
	 */
	public function migrate_notes( $args, $assoc_args ) {
		global $wpdb;
		$force = isset( $assoc_args['force'] ) ? true : false;

		$upgrade_completed = edd_has_upgrade_completed( 'migrate_notes' );

		if ( ! $force && $upgrade_completed ) {
			WP_CLI::error( __( 'The notes custom table migration has already been run. To do this anyway, use the --force argument.', 'easy-digital-downloads' ) );
		}

		if ( ! EDD()->notes->table_exists( EDD()->notes->table_name ) ) {
			@EDD()->notes->create_table();
		}

		if ( ! EDD()->note_meta->table_exists( EDD()->note_meta->table_name ) ) {
			@EDD()->note_meta->create_table();
		}

		$sql = "SELECT * FROM $wpdb->comments WHERE comment_type = 'edd_payment_note'";
		$results = $wpdb->get_results( $sql );
		$total = count( $results );

		if ( ! empty( $total ) ) {
			$progress = new \cli\progress\Bar( 'Migrating Notes', $total );

			foreach ( $results as $old_note ) {
				$note_data = array(
					'object_id'    => $old_note->comment_post_ID,
					'object_type'  => 'payment',
					'date_created' => $old_note->comment_date,
					'content'      => $old_note->comment_content,
					'user_id'      => $old_note->user_id,
				);

				$id = EDD()->notes->insert( $note_data );
				$note = new EDD\Notes\Note( $id );

				$meta = get_comment_meta( $old_note->comment_ID );
				if ( ! empty( $meta ) ) {
					foreach ( $meta as $key => $value ) {
						$note->add_meta( $key, $value );
					}
				}

				edd_debug_log( $old_note->comment_ID . ' successfully migrated to ' . $id );
				$progress->tick();
			}

			$progress->finish();

			WP_CLI::line( __( 'Migration complete.', 'easy-digital-downloads' ) );
			$new_count = EDD()->notes->count();
			$old_count = $wpdb->get_col( "SELECT count(comment_ID) FROM $wpdb->comments WHERE comment_type = 'edd_payment_note'", 0 );
			WP_CLI::line( __( 'Old Records: ', 'easy-digital-downloads' ) . $old_count[0] );
			WP_CLI::line( __( 'New Records: ', 'easy-digital-downloads' ) . $new_count );

			update_option( 'edd_version', preg_replace( '/[^0-9.].*/', '', EDD_VERSION ) );
			edd_set_upgrade_complete( 'migrate_notes' );

			WP_CLI::confirm( __( 'Remove legacy notes?', 'easy-digital-downloads' ), $remove_args = array() );
			WP_CLI::line( __( 'Removing old notes.', 'easy-digital-downloads' ) );

			$note_ids = $wpdb->get_results( "SELECT comment_ID FROM $wpdb->comments WHERE comment_type = 'edd_payment_note'" );
			$note_ids = wp_list_pluck( $note_ids, 'comment_ID' );
			$note_ids = implode( ', ', $note_ids );

			$delete_query = "DELETE FROM $wpdb->comments WHERE comment_ID IN ({$note_ids})";
			$wpdb->query( $delete_query );

			$delete_postmeta_query = "DELETE FROM $wpdb->commentmeta WHERE comment_id IN ({$note_ids})";
			$wpdb->query( $delete_postmeta_query );

			edd_set_upgrade_complete( 'remove_legacy_notes' );
		} else {
			WP_CLI::line( __( 'No note records found.', 'easy-digital-downloads' ) );
			edd_set_upgrade_complete( 'migrate_notes' );
			edd_set_upgrade_complete( 'remove_legacy_notes' );
		}
	}

	protected function get_fname() {
		$names = array(
			'Ilse','Emelda','Aurelio','Chiquita','Cheryl','Norbert','Neville','Wendie','Clint','Synthia','Tobi','Nakita',
			'Marisa','Maybelle','Onie','Donnette','Henry','Sheryll','Leighann','Wilson',
		);

		return $names[ rand( 0, ( count( $names ) - 1 ) ) ];
	}

	protected function get_lname() {
		$names = array(
			'Warner','Roush','Lenahan','Theiss','Sack','Troutt','Vanderburg','Lisi','Lemons','Christon','Kogut',
			'Broad','Wernick','Horstmann','Schoenfeld','Dolloff','Murph','Shipp','Hursey','Jacobi',
		);

		return $names[ rand( 0, ( count( $names ) - 1 ) ) ];
	}

	protected function get_domain() {
		$domains = array(
			'example', 'edd', 'rcp', 'affwp',
		);

		return $domains[ rand( 0, ( count( $domains ) - 1 ) ) ];
	}

	protected function get_tld() {
		$tlds = array(
			'local', 'test', 'example', 'localhost', 'invalid',
		);

		return $tlds[ rand( 0, ( count( $tlds ) - 1 ) ) ];
	}
}
