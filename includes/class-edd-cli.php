<?php
/**
 * Easy Digital Downloads WP-CLI
 *
 * This class provides an integration point with the WP-CLI plugin allowing
 * access to EDD from the command line.
 *
 * @package    EDD
 * @subpackage Classes/CLI
 * @copyright  Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license    http://opensource.org/license/gpl-2.0.php GNU Public License
 * @since      2.0
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

WP_CLI::add_command( 'edd', 'EDD_CLI' );

/**
 * Work with EDD through WP-CLI
 *
 * EDD_CLI Class
 *
 * Adds CLI support to EDD through WP-CLI
 *
 * @since 2.0
 */
class EDD_CLI extends WP_CLI_Command {

	private $api;


	public function __construct() {
		$this->api = new EDD_API();
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
	 * @param array $args
	 * @param array $assoc_args
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
		WP_CLI::line( sprintf( __( 'Tax rate is: %s', 'easy-digital-downloads' ), edd_get_formatted_tax_rate() ) );

		$rates = edd_get_tax_rates();
		if ( ! empty( $rates ) ) {
			foreach ( $rates as $rate ) {
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
	 * --date=[range|this_month|last_month|today|yesterday|this_quarter|last_quarter|this_year|last_year]: A specific
	 * date range to retrieve stats for
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

		$stats      = new EDD_Payment_Stats();
		$date       = isset( $assoc_args ) && array_key_exists( 'date', $assoc_args ) ? $assoc_args['date'] : false;
		$start_date = isset( $assoc_args ) && array_key_exists( 'startdate', $assoc_args ) ? $assoc_args['startdate'] : false;
		$end_date   = isset( $assoc_args ) && array_key_exists( 'enddate', $assoc_args ) ? $assoc_args['enddate'] : false;
		$download   = isset( $assoc_args ) && array_key_exists( 'product', $assoc_args ) ? $assoc_args['product'] : 0;

		if ( ! empty( $date ) ) {
			$start_date = $date;
			$end_date   = false;
		} elseif ( empty( $date ) && empty( $start_date ) ) {
			$start_date = 'this_month';
			$end_date   = false;
		}

		$earnings = $stats->get_earnings( $download, $start_date, $end_date );
		$sales    = $stats->get_sales( $download, $start_date, $end_date );

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

		if ( isset( $products['error'] ) ) {
			WP_CLI::error( $products['error'] );
		}

		if ( empty( $products ) ) {
			WP_CLI::error( __( 'No Downloads found', 'easy-digital-downloads' ) );

			return;
		}

		foreach ( $products['products'] as $product ) {
			$categories = '';
			$tags       = '';
			$pricing    = array();

			if ( is_array( $product['info']['category'] ) ) {
				$categories = array();
				foreach ( $product['info']['category'] as $category ) {
					$categories[] = $category->name;
				}

				$categories = implode( ', ', $categories );
			}

			if ( is_array( $product['info']['tags'] ) ) {
				$tags = array();
				foreach ( $product['info']['tags'] as $tag ) {
					$tags[] = $tag->name;
				}

				$tags = implode( ', ', $tags );
			}

			foreach ( $product['pricing'] as $price => $value ) {
				if ( 'amount' !== $price ) {
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

			if ( array_key_exists( 'files', $product ) ) {
				WP_CLI::line( '' );
				WP_CLI::line( __( 'Download Files:', 'easy-digital-downloads' ) );

				foreach ( $product['files'] as $file ) {
					WP_CLI::line( '  ' . sprintf( __( 'File: %s (%s)', 'easy-digital-downloads' ), $file['name'], $file['file'] ) );

					if ( isset( $file['condition'] ) && 'all' !== $file['condition'] ) {
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
	 * --create=<number>: The number of arbitrary customers to create. Leave as 1 or blank to create a customer with a
	 * speciific email
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
		$customer_id = isset( $assoc_args ) && array_key_exists( 'id', $assoc_args ) ? absint( $assoc_args['id'] ) : false;
		$email       = isset( $assoc_args ) && array_key_exists( 'email', $assoc_args ) ? $assoc_args['email'] : false;
		$name        = isset( $assoc_args ) && array_key_exists( 'name', $assoc_args ) ? $assoc_args['name'] : null;
		$user_id     = isset( $assoc_args ) && array_key_exists( 'user_id', $assoc_args ) ? $assoc_args['user_id'] : null;
		$create      = isset( $assoc_args ) && array_key_exists( 'create', $assoc_args ) ? $assoc_args['create'] : false;
		$start       = time();

		if ( $create ) {
			$number = 1;

			// Create one or more customers
			if ( ! $email ) {

				// If no email is specified, look to see if we are generating arbitrary customer accounts
				$number = is_numeric( $create ) ? absint( $create ) : 1;
			}

			for ( $i = 0; $i < $number; $i ++ ) {
				if ( ! $email ) {

					// Generate fake email
					$email = 'customer-' . uniqid() . '@test.com';
				}

				$args = array(
					'email'   => $email,
					'name'    => $name,
					'user_id' => $user_id,
				);

				$customer_id = edd_add_customer( $args );

				if ( $customer_id ) {
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

			if ( isset( $customers['error'] ) ) {
				WP_CLI::error( $customers['error'] );
			}

			if ( empty( $customers ) ) {
				WP_CLI::error( __( 'No customers found', 'easy-digital-downloads' ) );

				return;
			}

			foreach ( $customers['customers'] as $customer ) {
				WP_CLI::line( WP_CLI::colorize( '%G' . $customer['info']['email'] . '%N' ) );
				WP_CLI::line( sprintf( __( 'Customer User ID: %s', 'easy-digital-downloads' ), $customer['info']['id'] ) );
				WP_CLI::line( sprintf( __( 'Username: %s', 'easy-digital-downloads' ), $customer['info']['username'] ) );
				WP_CLI::line( sprintf( __( 'Display Name: %s', 'easy-digital-downloads' ), $customer['info']['display_name'] ) );

				if ( array_key_exists( 'first_name', $customer ) ) {
					WP_CLI::line( sprintf( __( 'First Name: %s', 'easy-digital-downloads' ), $customer['info']['first_name'] ) );
				}

				if ( array_key_exists( 'last_name', $customer ) ) {
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
	 *     --email=<customer_email>: The email address of the customer to retrieve
	 *
	 * ## EXAMPLES
	 *
	 * wp edd sales
	 * wp edd sales --email=john@test.com
	 */
	public function sales( $args, $assoc_args ) {
		$email = isset( $assoc_args ) && array_key_exists( 'email', $assoc_args ) ? $assoc_args['email'] : '';

		global $wp_query;

		$wp_query->query_vars['email'] = $email;

		$sales = $this->api->get_recent_sales();

		if ( empty( $sales ) ) {
			WP_CLI::error( __( 'No sales found', 'easy-digital-downloads' ) );

			return;
		}

		foreach ( $sales['sales'] as $sale ) {
			WP_CLI::line( WP_CLI::colorize( '%G' . $sale['ID'] . '%N' ) );
			WP_CLI::line( sprintf( __( 'Purchase Key: %s', 'easy-digital-downloads' ), $sale['key'] ) );
			WP_CLI::line( sprintf( __( 'Email: %s', 'easy-digital-downloads' ), $sale['email'] ) );
			WP_CLI::line( sprintf( __( 'Date: %s', 'easy-digital-downloads' ), $sale['date'] ) );
			WP_CLI::line( sprintf( __( 'Subtotal: %s', 'easy-digital-downloads' ), edd_format_amount( $sale['subtotal'] ) . ' ' . edd_get_currency() ) );
			WP_CLI::line( sprintf( __( 'Tax: %s', 'easy-digital-downloads' ), edd_format_amount( $sale['tax'] ) . ' ' . edd_get_currency() ) );

			if ( array_key_exists( 0, $sale['fees'] ) ) {
				WP_CLI::line( __( 'Fees:', 'easy-digital-downloads' ) );

				foreach ( $sale['fees'] as $fee ) {
					WP_CLI::line( sprintf( __( '  Fee: %s - %s', 'easy-digital-downloads' ), edd_format_amount( $fee['amount'] ), edd_get_currency() ) );
				}
			}

			WP_CLI::line( sprintf( __( 'Total: %s', 'easy-digital-downloads' ), edd_format_amount( $sale['total'] ) . ' ' . edd_get_currency() ) );
			WP_CLI::line( '' );
			WP_CLI::line( sprintf( __( 'Gateway: %s', 'easy-digital-downloads' ), $sale['gateway'] ) );

			if ( array_key_exists( 0, $sale['products'] ) ) {
				WP_CLI::line( __( 'Products:', 'easy-digital-downloads' ) );

				foreach ( $sale['products'] as $product ) {
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
		if ( ! isset( $args ) || 0 === count( $args ) ) {
			$error = __( 'No action specified, did you mean', 'easy-digital-downloads' );
		} elseif ( isset( $args ) && ! in_array( 'create', $args, true ) ) {
			$error = __( 'Invalid action specified, did you mean', 'easy-digital-downloads' );
		}

		if ( $error ) {
			$query = '';
			foreach ( $assoc_args as $key => $value ) {
				$query .= ' --' . $key . '=' . $value;
			}

			WP_CLI::error(
				sprintf( $error . ' %s?', 'wp edd payments create' . $query )
			);

			return;
		}


		// Setup some defaults
		$number   = 1;
		$status   = 'complete';
		$id       = false;
		$price_id = false;
		$tax      = 0;
		$email    = 'guest@edd.local';
		$fname    = 'Pippin';
		$lname    = 'Williamson';
		$date     = false;
		$range    = 30;

		$generate_users = false;

		if ( count( $assoc_args ) > 0 ) {
			$number   = ( array_key_exists( 'number', $assoc_args ) ) ? absint( $assoc_args['number'] ) : $number;
			$id       = ( array_key_exists( 'id', $assoc_args ) ) ? absint( $assoc_args['id'] ) : $id;
			$price_id = ( array_key_exists( 'price_id', $assoc_args ) ) ? absint( $assoc_args['price_id'] ) : $price_id;
			$tax      = ( array_key_exists( 'tax', $assoc_args ) ) ? floatval( $assoc_args['tax'] ) : $tax;
			$email    = ( array_key_exists( 'email', $assoc_args ) ) ? sanitize_email( $assoc_args['email'] ) : $email;
			$fname    = ( array_key_exists( 'fname', $assoc_args ) ) ? sanitize_text_field( $assoc_args['fname'] ) : $fname;
			$lname    = ( array_key_exists( 'lname', $assoc_args ) ) ? sanitize_text_field( $assoc_args['lname'] ) : $lname;
			$date     = ( array_key_exists( 'date', $assoc_args ) ) ? sanitize_text_field( $assoc_args['date'] ) : $date;
			$range    = ( array_key_exists( 'range', $assoc_args ) ) ? absint( $assoc_args['range'] ) : $range;

			$generate_users = ( array_key_exists( 'generate_users', $assoc_args ) ) ? (bool) absint( $assoc_args['generate_users'] ) : $generate_users;

			// Status requires a bit more validation.
			if ( array_key_exists( 'status', $assoc_args ) ) {
				$statuses = array_keys( edd_get_payment_statuses() );

				if ( in_array( $assoc_args['status'], $statuses, true ) ) {
					$status = ( 'publish' === $assoc_args['status'] )
						? 'complete'
						: $assoc_args['status'];
				} else {
					WP_CLI::warning( sprintf(
						__( "Invalid status '%s', defaulting to 'complete'", 'easy-digital-downloads' ),
						$assoc_args['status']
					) );
				}
			}
		}

		// Build the user info array.
		$user_info = array(
			'id'         => 0,
			'email'      => $email,
			'first_name' => $fname,
			'last_name'  => $lname,
			'discount'   => 'none',
		);

		$progress = \WP_CLI\Utils\make_progress_bar( 'Creating Orders', $number );

		for ( $i = 0; $i < $number; $i ++ ) {
			$products = array();
			$total    = 0;

			// No specified product
			if ( ! $id ) {
				$products = get_posts( array(
					'post_type'      => 'download',
					'orderby'        => 'rand',
					'order'          => 'ASC',
					'posts_per_page' => rand( 1, 3 ),
				) );
			} else {
				$product = get_post( $id );

				if ( 'download' !== $product->post_type ) {
					WP_CLI::error( __( 'Specified ID is not a product', 'easy-digital-downloads' ) );

					return;
				}

				$products[] = $product;
			}

			$cart_details = array();

			// Add each download to the order.
			foreach ( $products as $key => $download ) {
				if ( ! $download instanceof WP_Post ) {
					continue;
				}

				$options         = array();
				$final_downloads = array();

				// Variable price.
				if ( edd_has_variable_prices( $download->ID ) ) {
					$prices = edd_get_variable_prices( $download->ID );

					if ( false === $price_id || ! array_key_exists( $price_id, (array) $prices ) ) {
						$item_price_id = array_rand( $prices );
					} else {
						$item_price_id = $price_id;
					}

					$item_price          = $prices[ $item_price_id ]['amount'];
					$options['price_id'] = $item_price_id;

				// Flat price.
				} else {
					$item_price = edd_get_download_price( $download->ID );
				}

				$item_number = array(
					'id'       => $download->ID,
					'quantity' => 1,
					'options'  => $options,
				);

				$cart_details[ $key ] = array(
					'name'        => $download->post_title,
					'id'          => $download->ID,
					'item_number' => $item_number,
					'item_price'  => edd_sanitize_amount( $item_price ),
					'subtotal'    => edd_sanitize_amount( $item_price ),
					'price'       => edd_sanitize_amount( $item_price ),
					'quantity'    => 1,
					'discount'    => 0,
					'tax'         => edd_calculate_tax( $item_price ),
				);

				$final_downloads[ $key ] = $item_number;

				$total += $item_price;
			}

			// Generate random date.
			if ( 'random' === $date ) {
				// Randomly grab a date from the current past 30 days
				$oldest_time = strtotime( '-' . $range . ' days', current_time( 'timestamp' ) );
				$newest_time = current_time( 'timestamp' );

				$timestamp  = rand( $oldest_time, $newest_time );
				$timestring = date( "Y-m-d H:i:s", $timestamp );
			} elseif ( empty( $date ) ) {
				$timestring = false;
			} else {
				if ( is_numeric( $date ) ) {
					$timestring = date( "Y-m-d H:i:s", $date );
				} else {
					$parsed_time = strtotime( $date );
					$timestring  = date( "Y-m-d H:i:s", $parsed_time );
				}
			}

			// Maybe generate users.
			if ( $generate_users ) {
				$fname  = $this->get_fname();
				$lname  = $this->get_lname();
				$domain = $this->get_domain();
				$tld    = $this->get_tld();

				$email = $fname . '.' . $lname . '@' . $domain . '.' . $tld;

				$user_info = array(
					'id'         => 0,
					'email'      => $email,
					'first_name' => $fname,
					'last_name'  => $lname,
					'discount'   => 'none',
				);
			}

			// Build purchase data.
			$purchase_data = array(
				'price'        => edd_sanitize_amount( $total ),
				'tax'          => edd_calculate_tax( $total ),
				'purchase_key' => strtolower( md5( uniqid() ) ),
				'user_email'   => $email,
				'user_info'    => $user_info,
				'currency'     => edd_get_currency(),
				'downloads'    => $final_downloads,
				'cart_details' => $cart_details,
				'status'       => 'pending',
			);

			if ( ! empty( $timestring ) ) {
				$purchase_data['date_created'] = $timestring;
			}

			$order_id = edd_build_order( $purchase_data );

			// Ensure purchase receipts do not get sent.
			remove_action( 'edd_complete_purchase', 'edd_trigger_purchase_receipt', 999 );

			// Trigger payment status actions.
			if ( 'pending' !== $status ) {
				edd_update_order_status( $order_id, $status );
			}

			if ( ! empty( $timestring ) ) {
				$payment                 = new EDD_Payment( $order_id );
				$payment->completed_date = $timestring;
				$payment->gateway        = 'manual';
				$payment->save();
			}

			$progress->tick();
		}

		$progress->finish();

		WP_CLI::success( sprintf( __( 'Created %s orders', 'easy-digital-downloads' ), $number ) );

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

		for ( $i = 0; $i < $number; $i ++ ) {
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
				$type              = array( 'flat', 'percent' );
				$status            = array( 'active', 'inactive' );
				$product_condition = array( 'any', 'all' );

				$type_index              = array_rand( $type, 1 );
				$status_index            = array_rand( $status, 1 );
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
	 * Run the EDD 3.0 Migration via WP-CLI
	 *
	 * ## OPTIONS
	 *
	 * --force=<boolean>: If the routine should be run even if the upgrade routine has been run already
	 *
	 * @param $args
	 * @param $assoc_args
	 */
	public function v30_migration( $args, $assoc_args ) {

		// Suspend the cache addition while we're migrating.
		wp_suspend_cache_addition( true );

		$this->maybe_install_v3_tables();
		update_option( 'edd_v30_cli_migration_running', true );
		$this->migrate_tax_rates( $args, $assoc_args );
		$this->migrate_discounts( $args, $assoc_args );
		$this->migrate_payments( $args, $assoc_args );
		$this->migrate_customer_data( $args, $assoc_args );
		$this->migrate_logs( $args, $assoc_args );
		$this->migrate_order_notes( $args, $assoc_args );
		$this->migrate_customer_notes( $args, $assoc_args );
		edd_v30_is_migration_complete();
		$this->remove_legacy_data( $args, $assoc_args );
	}

	/**
	 * Installs any new 3.0 database tables that haven't yet been installed
	 *
	 * @access private
	 * @since 3.0
	 */
	private function maybe_install_v3_tables() {
		static $installed = false;

		if ( $installed ) {
			return;
		}

		foreach ( EDD()->components as $component ) {
			// Install the main component table.
			$table = $component->get_interface( 'table' );
			if ( $table instanceof EDD\Database\Table && ! $table->exists() ) {
				$table->install();
			}

			// Install the associated meta table, if there is one.
			$meta = $component->get_interface( 'meta' );
			if ( $meta instanceof EDD\Database\Table && ! $meta->exists() ) {
				$meta->install();
			}
		}

		// Only need to do this once.
		$installed = true;
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

		$this->maybe_install_v3_tables();

		require_once EDD_PLUGIN_DIR . 'includes/admin/upgrades/v3/class-data-migrator.php';

		$force = isset( $assoc_args['force'] )
			? true
			: false;

		$upgrade_completed = edd_has_upgrade_completed( 'migrate_discounts' );

		if ( ! $force && $upgrade_completed ) {
			WP_CLI::error( __( 'The discounts custom database migration has already been run. To do this anyway, use the --force argument.', 'easy-digital-downloads' ) );
		}

		$sql     = "SELECT * FROM {$wpdb->posts} WHERE post_type = 'edd_discount'";
		$results = $wpdb->get_results( $sql );
		$total   = count( $results );

		if ( ! empty( $total ) ) {

			$progress = new \cli\progress\Bar( 'Migrating Discounts', $total );

			foreach ( $results as $result ) {
				\EDD\Admin\Upgrades\v3\Data_Migrator::discounts( $result );

				$progress->tick();
			}

			$progress->finish();

			WP_CLI::line( __( 'Migration complete: Discounts', 'easy-digital-downloads' ) );
			$new_count = edd_get_discount_count();
			$old_count = $wpdb->get_col( "SELECT count(ID) FROM $wpdb->posts WHERE post_type ='edd_discount'", 0 );
			WP_CLI::line( __( 'Old Records: ', 'easy-digital-downloads' ) . $old_count[0] );
			WP_CLI::line( __( 'New Records: ', 'easy-digital-downloads' ) . $new_count );

			edd_update_db_version();
			edd_set_upgrade_complete( 'migrate_discounts' );

		} else {

			WP_CLI::line( __( 'No discount records found.', 'easy-digital-downloads' ) );
			edd_set_upgrade_complete( 'migrate_discounts' );

		}
	}

	/**
	 * Migrate logs to the custom tables.
	 *
	 * ## OPTIONS
	 *
	 * --force=<boolean>: If the routine should be run even if the upgrade routine has been run already
	 *
	 * ## EXAMPLES
	 *
	 * wp edd migrate_logs
	 * wp edd migrate_logs --force
	 */
	public function migrate_logs( $args, $assoc_args ) {
		global $wpdb;

		$this->maybe_install_v3_tables();

		require_once EDD_PLUGIN_DIR . 'includes/admin/upgrades/v3/class-data-migrator.php';

		$force = isset( $assoc_args['force'] )
			? true
			: false;

		$upgrade_completed = edd_has_upgrade_completed( 'migrate_logs' );

		if ( ! $force && $upgrade_completed ) {
			WP_CLI::error( __( 'The logs custom table migration has already been run. To do this anyway, use the --force argument.', 'easy-digital-downloads' ) );
		}

		WP_CLI::line( __( 'Preparing to migrate logs (this can take several minutes).', 'easy-digital-downloads' ) );

		$sql = "
			SELECT p.*, t.slug
			FROM {$wpdb->posts} AS p
			LEFT JOIN {$wpdb->term_relationships} AS tr ON (p.ID = tr.object_id)
			LEFT JOIN {$wpdb->term_taxonomy} AS tt ON (tr.term_taxonomy_id = tt.term_taxonomy_id)
			LEFT JOIN {$wpdb->terms} AS t ON (tt.term_id = t.term_id)
			WHERE p.post_type = 'edd_log' AND t.slug != 'sale'
		";

		$results = $wpdb->get_results( $sql );
		$total   = count( $results );

		if ( ! empty( $total ) ) {
			$progress = new \cli\progress\Bar( 'Migrating Logs', $total );

			foreach ( $results as $result ) {
				\EDD\Admin\Upgrades\v3\Data_Migrator::logs( $result );

				$progress->tick();
			}

			$progress->finish();

			WP_CLI::line( __( 'Migration complete: Logs', 'easy-digital-downloads' ) );
			$new_count = edd_count_logs() + edd_count_file_download_logs() + edd_count_api_request_logs();
			WP_CLI::line( __( 'Old Records: ', 'easy-digital-downloads' ) . $total );
			WP_CLI::line( __( 'New Records: ', 'easy-digital-downloads' ) . $new_count );

			edd_update_db_version();
			edd_set_upgrade_complete( 'migrate_logs' );
		} else {
			WP_CLI::line( __( 'No log records found.', 'easy-digital-downloads' ) );
			edd_set_upgrade_complete( 'migrate_logs' );
		}
	}

	/**
	 * Migrate order notes to the custom tables.
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
	public function migrate_order_notes( $args, $assoc_args ) {
		global $wpdb;

		$this->maybe_install_v3_tables();

		require_once EDD_PLUGIN_DIR . 'includes/admin/upgrades/v3/class-data-migrator.php';

		$force = isset( $assoc_args['force'] )
			? true
			: false;

		$upgrade_completed = edd_has_upgrade_completed( 'migrate_order_notes' );

		if ( ! $force && $upgrade_completed ) {
			WP_CLI::error( __( 'The order notes custom table migration has already been run. To do this anyway, use the --force argument.', 'easy-digital-downloads' ) );
		}

		$sql     = "SELECT * FROM {$wpdb->comments} WHERE comment_type = 'edd_payment_note' ORDER BY comment_ID ASC";
		$results = $wpdb->get_results( $sql );
		$total   = count( $results );

		if ( ! empty( $total ) ) {
			$progress = new \cli\progress\Bar( 'Migrating Notes', $total );

			foreach ( $results as $result ) {
				$result->object_id = $result->comment_post_ID;
				\EDD\Admin\Upgrades\v3\Data_Migrator::order_notes( $result );

				$progress->tick();
			}

			$progress->finish();

			WP_CLI::line( __( 'Migration complete: Order Notes', 'easy-digital-downloads' ) );
			$new_count = edd_count_notes();
			$old_count = $wpdb->get_col( "SELECT count(comment_ID) FROM {$wpdb->comments} WHERE comment_type = 'edd_payment_note'", 0 );
			WP_CLI::line( __( 'Old Records: ', 'easy-digital-downloads' ) . $old_count[0] );
			WP_CLI::line( __( 'New Records: ', 'easy-digital-downloads' ) . $new_count );

			edd_update_db_version();
			edd_set_upgrade_complete( 'migrate_order_notes' );
		} else {
			WP_CLI::line( __( 'No note records found.', 'easy-digital-downloads' ) );
			edd_set_upgrade_complete( 'migrate_order_notes' );
		}
	}

	/**
	 * Migrate customer notes to the custom tables.
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
	public function migrate_customer_notes( $args, $assoc_args ) {
		global $wpdb;

		$this->maybe_install_v3_tables();

		require_once EDD_PLUGIN_DIR . 'includes/admin/upgrades/v3/class-data-migrator.php';

		$force = isset( $assoc_args['force'] )
			? true
			: false;

		$upgrade_completed = edd_has_upgrade_completed( 'migrate_customer_notes' );

		if ( ! $force && $upgrade_completed ) {
			WP_CLI::error( __( 'The customer notes custom table migration has already been run. To do this anyway, use the --force argument.', 'easy-digital-downloads' ) );
		}

		$sql     = "SELECT * FROM {$wpdb->edd_customers}";
		$results = $wpdb->get_results( $sql );
		$total   = count( $results );

		if ( ! empty( $total ) ) {
			$progress = new \cli\progress\Bar( 'Migrating Customer Notes', $total );

			foreach ( $results as $result ) {
				\EDD\Admin\Upgrades\v3\Data_Migrator::customer_notes( $result );

				$progress->tick();
			}

			$progress->finish();

			WP_CLI::line( __( 'Migration complete: Customer Notes', 'easy-digital-downloads' ) );

			edd_update_db_version();
			edd_set_upgrade_complete( 'migrate_customer_notes' );
		} else {
			WP_CLI::line( __( 'No customer note records found.', 'easy-digital-downloads' ) );
			edd_set_upgrade_complete( 'migrate_customer_notes' );
		}
	}

	/**
	 * Migrate customer data to the custom tables.
	 *
	 * ## OPTIONS
	 *
	 * --force=<boolean>: If the routine should be run even if the upgrade routine has been run already
	 *
	 * ## EXAMPLES
	 *
	 * wp edd migrate_customer_data
	 * wp edd migrate_customer_data --force
	 */
	public function migrate_customer_data( $args, $assoc_args ) {
		global $wpdb;

		$this->maybe_install_v3_tables();

		require_once EDD_PLUGIN_DIR . 'includes/admin/upgrades/v3/class-data-migrator.php';

		$force = isset( $assoc_args['force'] )
			? true
			: false;

		$customer_addresses_complete = edd_has_upgrade_completed( 'migrate_customer_addresses' );

		if ( ! $force && $customer_addresses_complete ) {
			WP_CLI::warning( __( 'The user addresses custom table migration has already been run. To do this anyway, use the --force argument.', 'easy-digital-downloads' ) );
		} else {

			// Create the tables if they do not exist.
			$components = array(
				array( 'order', 'table' ),
				array( 'order', 'meta' ),
				array( 'customer', 'table' ),
				array( 'customer', 'meta' ),
				array( 'customer_address', 'table' ),
				array( 'customer_email_address', 'table' ),
			);

			foreach ( $components as $component ) {
				/** @var EDD\Database\Tables\Base $table */
				$table = edd_get_component_interface( $component[0], $component[1] );

				if ( $table instanceof EDD\Database\Tables\Base && ! $table->exists() ) {
					@$table->create();
				}
			}

			// Migrate user addresses first.
			$sql = "
				SELECT *
				FROM {$wpdb->usermeta}
				WHERE meta_key = '_edd_user_address'
			";
			$results = $wpdb->get_results( $sql );
			$total   = count( $results );

			if ( ! empty( $total ) ) {
				$progress = new \cli\progress\Bar( 'Migrating User Addresses', $total );

				foreach ( $results as $result ) {
					\EDD\Admin\Upgrades\v3\Data_Migrator::customer_addresses( $result, 'billing' );

					$progress->tick();
				}

				$progress->finish();
			}

			// Now update the most recent billing address entries for customers as the primary address.
			$sql = "
				UPDATE {$wpdb->edd_customer_addresses} ca
				SET ca.is_primary = 1
				WHERE ca.id IN (
					SELECT MAX(ca2.id)
					FROM ( SELECT * FROM {$wpdb->edd_customer_addresses} ) ca2
					WHERE ca2.type = 'billing'
					GROUP BY ca2.customer_id
				)
			";

			@$wpdb->query( $sql );

			edd_set_upgrade_complete( 'migrate_customer_addresses' );
		}

		$customer_email_addresses_complete = edd_has_upgrade_completed( 'migrate_customer_email_addresses' );

		if ( ! $force && $customer_email_addresses_complete ) {
			WP_CLI::warning( __( 'The user email addresses custom table migration has already been run. To do this anyway, use the --force argument.', 'easy-digital-downloads' ) );
		} else {

			WP_CLI::line( __( 'Preparing to migrate customer email addresses (this can take several minutes).', 'easy-digital-downloads' ) );

			// Migrate email addresses next.
			$sql = "
				SELECT *
				FROM {$wpdb->edd_customermeta}
				WHERE meta_key = 'additional_email'
			";
			$results = $wpdb->get_results( $sql );
			$total   = count( $results );

			if ( ! empty( $total ) ) {
				$progress = new \cli\progress\Bar( 'Migrating Email Addresses', $total );

				foreach ( $results as $result ) {
					\EDD\Admin\Upgrades\v3\Data_Migrator::customer_email_addresses( $result );

					$progress->tick();
				}

				$progress->finish();
			}
			edd_set_upgrade_complete( 'migrate_customer_email_addresses' );
		}

		WP_CLI::line( __( 'Migration complete: Email Addresses', 'easy-digital-downloads' ) );

		edd_update_db_version();
	}

	/**
	 * Migrate tax rates.
	 *
	 * ## OPTIONS
	 *
	 * --force=<boolean>: If the routine should be run even if the upgrade routine has been run already
	 *
	 * ## EXAMPLES
	 *
	 * wp edd migrate_tax_rates
	 * wp edd migrate_tax_rates --force
	 */
	public function migrate_tax_rates( $args, $assoc_args ) {
		global $wpdb;

		$this->maybe_install_v3_tables();

		require_once EDD_PLUGIN_DIR . 'includes/admin/upgrades/v3/class-data-migrator.php';

		$force = isset( $assoc_args['force'] )
			? true
			: false;

		$upgrade_completed = edd_has_upgrade_completed( 'migrate_tax_rates' );

		if ( ! $force && $upgrade_completed ) {
			WP_CLI::error( __( 'The tax rates custom table migration has already been run. To do this anyway, use the --force argument.', 'easy-digital-downloads' ) );
		}

		WP_CLI::line( __( 'Checking for default tax rate', 'easy-digital-downloads' ) );
		$default_tax_rate = edd_get_option( 'tax_rate', false );
		if ( ! empty( $default_tax_rate ) ) {
			WP_CLI::line( __( 'Migrating default tax rate', 'easy-digital-downloads' ) );
			edd_add_tax_rate(
				array(
					'scope'  => 'global',
					'amount' => floatval( $default_tax_rate ),
				)
			);
		}

		// Migrate user addresses first.
		$tax_rates = get_option( 'edd_tax_rates', array() );

		if ( ! empty( $tax_rates ) ) {
			$progress = new \cli\progress\Bar( 'Migrating Tax Rates', count( $tax_rates ) );

			foreach ( $tax_rates as $result ) {
				\EDD\Admin\Upgrades\v3\Data_Migrator::tax_rates( $result );

				$progress->tick();
			}

			$progress->finish();
		}

		WP_CLI::line( __( 'Migration complete: Tax Rates', 'easy-digital-downloads' ) );
		$new_count = edd_count_adjustments( array( 'type' => 'tax_rate' ) );
		WP_CLI::line( __( 'Old Records: ', 'easy-digital-downloads' ) . count( $tax_rates ) );
		WP_CLI::line( __( 'New Records: ', 'easy-digital-downloads' ) . $new_count );

		edd_update_db_version();
		edd_set_upgrade_complete( 'migrate_tax_rates' );
	}

	/**
	 * Migrate payments to the custom tables.
	 *
	 * ## OPTIONS
	 *
	 * --force=<boolean>: If the routine should be run even if the upgrade routine has been run already
	 *
	 * ## EXAMPLES
	 *
	 * wp edd migrate_payments
	 * wp edd migrate_payments --force
	 */
	public function migrate_payments( $args, $assoc_args ) {
		global $wpdb;

		$this->maybe_install_v3_tables();

		require_once EDD_PLUGIN_DIR . 'includes/admin/upgrades/v3/class-data-migrator.php';

		$force = isset( $assoc_args['force'] )
			? true
			: false;

		$upgrade_completed = edd_has_upgrade_completed( 'migrate_orders' );

		if ( ! $force && $upgrade_completed ) {
			WP_CLI::error( __( 'The payments custom table migration has already been run. To do this anyway, use the --force argument.', 'easy-digital-downloads' ) );
		}

		$sql = "
			SELECT *
			FROM {$wpdb->posts}
			WHERE post_type = 'edd_payment'
			ORDER BY ID ASC
		";
		$results = $wpdb->get_results( $sql );
		$total   = count( $results );

		if ( ! empty( $total ) ) {
			$progress = new \cli\progress\Bar( 'Migrating Payments', $total );
			$orders   = new \EDD\Database\Queries\Order();
			foreach ( $results as $result ) {

				// Check if order has already been migrated.
				$migrated = $orders->get_item( $result->ID );
				if ( $migrated ) {
					continue;
				}

				\EDD\Admin\Upgrades\v3\Data_Migrator::orders( $result );

				$progress->tick();
			}

			$progress->finish();

			WP_CLI::line( __( 'Migration complete: Orders', 'easy-digital-downloads' ) );
			$new_count = edd_count_orders( array( 'type' => 'sale' ) );
			$old_count = $wpdb->get_col( "SELECT count(ID) FROM {$wpdb->posts} WHERE post_type = 'edd_payment'", 0 );
			WP_CLI::line( __( 'Old Records: ', 'easy-digital-downloads' ) . $old_count[0] );
			WP_CLI::line( __( 'New Records: ', 'easy-digital-downloads' ) . $new_count );

			$refund_count = edd_count_orders( array( 'type' => 'refund' ) );
			WP_CLI::line( __( 'Refund Records Created: ', 'easy-digital-downloads' ) . $refund_count );

			edd_update_db_version();
			edd_set_upgrade_complete( 'migrate_orders' );

			$this->recalculate_download_sales_earnings();
		} else {
			WP_CLI::line( __( 'No payment records found.', 'easy-digital-downloads' ) );
			edd_set_upgrade_complete( 'migrate_orders' );
			edd_set_upgrade_complete( 'remove_legacy_payments' );
		}
	}

	/**
	 * Recalculates the sales and earnings for all downloads.
	 *
	 * @since 3.0
	 * @return void
	 *
	 * wp edd recalculate_download_sales_earnings
	 */
	public function recalculate_download_sales_earnings() {
		global $wpdb;

		$downloads = $wpdb->get_results(
			"SELECT ID
			FROM {$wpdb->posts}
			WHERE post_type = 'download'
			ORDER BY ID ASC"
		);
		$total     = count( $downloads );
		if ( ! empty( $total ) ) {
			$progress = new \cli\progress\Bar( 'Recalculating Download Sales and Earnings', $total );
			foreach ( $downloads as $download ) {
				edd_recalculate_download_sales_earnings( $download->ID );
				$progress->tick();
			}
			$progress->finish();
		}
		WP_CLI::line( __( 'Sales and Earnings successfully recalculated for all downloads.', 'easy-digital-downloads' ) );
		WP_CLI::line( __( 'Downloads Updated: ', 'easy-digital-downloads' ) . $total );
	}

	/**
	 * Removes legacy data from 2.9 and earlier that has been migrated to 3.0.
	 *
	 * ## OPTIONS
	 *
	 * --force=<boolean>: If the routine should be run even if the upgrade routine has been run already
	 *
	 * ## EXAMPLES
	 *
	 * wp edd remove_legacy_data
	 * wp edd remove_legacy_data --force
	 */
	public function remove_legacy_data( $args, $assoc_args ) {
		global $wpdb;

		WP_CLI::confirm( __( 'Do you want to remove legacy data? This will permanently remove legacy discounts, logs, and order notes.', 'easy-digital-downloads' ) );

		$force = isset( $assoc_args['force'] ) ? true : false;

		/**
		 * Discounts
		 */
		if ( ! $force && edd_has_upgrade_completed( 'remove_legacy_discounts' ) ) {
			WP_CLI::warning( __( 'Legacy discounts have already been removed. To run this anyway, use the --force argument.', 'easy-digital-downloads' ) );
		} else {
			WP_CLI::line( __( 'Removing old discount data.', 'easy-digital-downloads' ) );

			$discount_ids = $wpdb->get_results( "SELECT ID FROM $wpdb->posts WHERE post_type = 'edd_discount'" );
			$discount_ids = wp_list_pluck( $discount_ids, 'ID' );
			$discount_ids = implode( ', ', $discount_ids );

			if ( ! empty( $discount_ids ) ) {
				$delete_posts_query = "DELETE FROM $wpdb->posts WHERE ID IN ({$discount_ids})";
				$wpdb->query( $delete_posts_query );

				$delete_postmeta_query = "DELETE FROM $wpdb->postmeta WHERE post_id IN ({$discount_ids})";
				$wpdb->query( $delete_postmeta_query );
			}

			edd_set_upgrade_complete( 'remove_legacy_discounts' );
		}

		/**
		 * Logs
		 */
		if ( ! $force && edd_has_upgrade_completed( 'remove_legacy_logs' ) ) {
			WP_CLI::warning( __( 'Legacy logs have already been removed. To run this anyway, use the --force argument.', 'easy-digital-downloads' ) );
		} else {
			WP_CLI::line( __( 'Removing old logs.', 'easy-digital-downloads' ) );

			$log_ids = $wpdb->get_results( "SELECT ID FROM {$wpdb->posts} WHERE post_type = 'edd_log'" );
			$log_ids = wp_list_pluck( $log_ids, 'ID' );
			$log_ids = implode( ', ', $log_ids );

			if ( ! empty( $log_ids ) ) {
				$delete_query = "DELETE FROM {$wpdb->posts} WHERE post_type = 'edd_log'";
				$wpdb->query( $delete_query );

				$delete_postmeta_query = "DELETE FROM {$wpdb->posts} WHERE ID IN ({$log_ids})";
				$wpdb->query( $delete_postmeta_query );
			}

			edd_set_upgrade_complete( 'remove_legacy_logs' );
		}

		/**
		 * Order notes
		 */
		if ( ! $force && edd_has_upgrade_completed( 'remove_legacy_order_notes' ) ) {
			WP_CLI::warning( __( 'Legacy order notes have already been removed. To run this anyway, use the --force argument.', 'easy-digital-downloads' ) );
		} else {
			WP_CLI::line( __( 'Removing old order notes.', 'easy-digital-downloads' ) );

			$note_ids = $wpdb->get_results( "SELECT comment_ID FROM {$wpdb->comments} WHERE comment_type = 'edd_payment_note'" );
			$note_ids = wp_list_pluck( $note_ids, 'comment_ID' );
			$note_ids = implode( ', ', $note_ids );

			if ( ! empty( $note_ids ) ) {
				$delete_query = "DELETE FROM {$wpdb->comments} WHERE comment_type = 'edd_payment_note'";
				$wpdb->query( $delete_query );

				$delete_postmeta_query = "DELETE FROM {$wpdb->commentmeta} WHERE comment_id IN ({$note_ids})";
				$wpdb->query( $delete_postmeta_query );
			}

			edd_set_upgrade_complete( 'remove_legacy_order_notes' );
		}

		/**
		 * Customers
		 *
		 * @var \EDD\Database\Tables\Customers|false $customer_table
		 */
		$customer_table = edd_get_component_interface( 'customer', 'table' );
		if ( $customer_table instanceof \EDD\Database\Tables\Customers && $customer_table->column_exists( 'payment_ids' ) ) {
			WP_CLI::line( __( 'Updating customers database table.', 'easy-digital-downloads' ) );

			$wpdb->query( "ALTER TABLE {$wpdb->edd_customers} DROP `payment_ids`" );
		}

		/**
		 * Customer emails
		 */
		if ( ! $force && edd_has_upgrade_completed( 'remove_legacy_customer_emails' ) ) {
			WP_CLI::warning( __( 'Legacy customer emails have already been removed. To run this anyway, use the --force argument.', 'easy-digital-downloads' ) );
		} else {
			WP_CLI::line( __( 'Removing old customer emails.', 'easy-digital-downloads' ) );

			$wpdb->query( "DELETE FROM {$wpdb->edd_customermeta} WHERE meta_key = 'additional_email'" );

			edd_set_upgrade_complete( 'remove_legacy_customer_emails' );
		}

		/**
		 * Customer addresses
		 */
		if ( ! $force && edd_has_upgrade_completed( 'remove_legacy_customer_addresses' ) ) {
			WP_CLI::warning( __( 'Legacy customer addresses have already been removed. To run this anyway, use the --force argument.', 'easy-digital-downloads' ) );
		} else {
			WP_CLI::line( __( 'Removing old customer addresses.', 'easy-digital-downloads' ) );

			$wpdb->query( "DELETE FROM {$wpdb->usermeta} WHERE meta_key = '_edd_user_address'" );

			edd_set_upgrade_complete( 'remove_legacy_customer_addresses' );
		}

		/**
		 * Orders
		 */
		if ( ! $force && edd_has_upgrade_completed( 'remove_legacy_orders' ) ) {
			WP_CLI::warning( __( 'Legacy orders have already been removed. To run this anyway, use the --force argument.', 'easy-digital-downloads' ) );
		} else {
			WP_CLI::line( __( 'Removing old orders.', 'easy-digital-downloads' ) );

			$wpdb->query(
				"DELETE orders, order_meta FROM {$wpdb->posts} orders
				LEFT JOIN {$wpdb->postmeta} order_meta ON( orders.ID = order_meta.post_id )
				WHERE orders.post_type = 'edd_payment'"
			);

			edd_set_upgrade_complete( 'remove_legacy_orders' );
		}
	}

	/*
	 * Create sample file download log data for your EDD site
	 *
	 * ## OPTIONS
	 *
	 * --number: The number of download logs to create
	 *
	 * ## EXAMPLES
	 *
	 * wp edd download_logs create --number=10
	 */
	public function download_logs( $args, $assoc_args ) {
		global $wpdb, $edd_logs;

		$error = false;

		// At some point we'll likely add another action for payments
		if ( ! isset( $args ) || count( $args ) == 0 ) {
			$error = __( 'No action specified, did you mean', 'easy-digital-downloads' );
		} elseif ( isset( $args ) && ! in_array( 'create', $args ) ) {
			$error = __( 'Invalid action specified, did you mean', 'easy-digital-downloads' );
		}

		if ( $error ) {
			$query = '';
			foreach ( $assoc_args as $key => $value ) {
				$query .= ' --' . $key . '=' . $value;
			}

			WP_CLI::error(
				sprintf( $error . ' %s?', 'wp edd download_logs create' . $query )
			);

			return;
		}

		// Setup some defaults
		$number = 1;

		if ( count( $assoc_args ) > 0 ) {
			$number = ( array_key_exists( 'number', $assoc_args ) ) ? absint( $assoc_args['number'] ) : $number;
		}


		// First we need to find all downloads that have files associated.
		$download_ids_with_file_meta = $wpdb->get_results( "SELECT post_id, meta_value FROM {$wpdb->postmeta} WHERE meta_key = 'edd_download_files'" );
		$download_ids_with_files     = array();
		foreach ( $download_ids_with_file_meta as $meta_item ) {
			if ( empty( $meta_item->meta_value ) ) {
				continue;
			}
			$files = maybe_unserialize( $meta_item->meta_value );

			// We have an empty array;
			if ( empty( $files ) ) {
				continue;
			}

			$download_ids_with_files[ $meta_item->post_id ] = array_keys( $files );
		}

		global $wpdb;
		$product_ids = implode('","', array_keys( $download_ids_with_files ) );
		$table       = $wpdb->prefix . 'edd_order_items';
		$sql         = 'SELECT order_id, product_id, price_id, uuid FROM ' . $table . ' WHERE product_id IN ( "' . $product_ids . '")';
		$results     = $wpdb->get_results( $sql );

		// Now generate some download logs for the files.
		$progress = \WP_CLI\Utils\make_progress_bar( 'Creating File Download Logs', $number );
		$i        = 1;
		while ( $i <= $number ) {
			$found_item = array_rand( $results, 1 );
			$item       = $results[ $found_item ];

			$order_id    = (int) $item->order_id;
			$order       = edd_get_order( $order_id );
			$product_id  = (int) $item->product_id;

			if ( edd_has_variable_prices( $product_id ) ) {
				$price_id = (int) $item->price_id;
			} else {
				$price_id = false;
			}

			$customer = new EDD_Customer( $order->customer_id );

			$user_info = array(
				'email' => $order->email,
				'id'    => $order->user_id,
				'name'  => $order->name,
			);

			if ( empty( $download_ids_with_files[ $product_id ] ) ) {
				continue;
			}

			$file_id_key = array_rand( $download_ids_with_files[ $product_id ], 1 );
			$file_key    = $download_ids_with_files[ $product_id ][ $file_id_key ];
			edd_add_file_download_log( array(
				'product_id'   => absint( $product_id ),
				'file_id'      => absint( $file_key ),
				'order_id'     => absint( $order_id ),
				'price_id'     => absint( $price_id ),
				'customer_id'  => $order->customer_id,
				'ip'           => edd_get_ip(),
				'user_agent'   => 'EDD; WPCLI; download_logs;',
				'date_created' => $order->date_completed,
			) );

			$progress->tick();
			$i ++;
		}
		$progress->finish();
	}

	protected function get_fname() {
		$names = array(
			'Ilse',
			'Emelda',
			'Aurelio',
			'Chiquita',
			'Cheryl',
			'Norbert',
			'Neville',
			'Wendie',
			'Clint',
			'Synthia',
			'Tobi',
			'Nakita',
			'Marisa',
			'Maybelle',
			'Onie',
			'Donnette',
			'Henry',
			'Sheryll',
			'Leighann',
			'Wilson',
		);

		return $names[ rand( 0, ( count( $names ) - 1 ) ) ];
	}

	protected function get_lname() {
		$names = array(
			'Warner',
			'Roush',
			'Lenahan',
			'Theiss',
			'Sack',
			'Troutt',
			'Vanderburg',
			'Lisi',
			'Lemons',
			'Christon',
			'Kogut',
			'Broad',
			'Wernick',
			'Horstmann',
			'Schoenfeld',
			'Dolloff',
			'Murph',
			'Shipp',
			'Hursey',
			'Jacobi',
		);

		return $names[ rand( 0, ( count( $names ) - 1 ) ) ];
	}

	protected function get_domain() {
		$domains = array(
			'example',
			'edd',
			'rcp',
			'affwp',
		);

		return $domains[ rand( 0, ( count( $domains ) - 1 ) ) ];
	}

	protected function get_tld() {
		$tlds = array(
			'local',
			'test',
			'example',
			'localhost',
			'invalid',
		);

		return $tlds[ rand( 0, ( count( $tlds ) - 1 ) ) ];
	}
}
