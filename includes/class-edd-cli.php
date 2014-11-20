<?php
/**
 * Easy Digital Downloads WP-CLI
 *
 * This class provides an integration point with the WP-CLI plugin allowing
 * access to EDD from the command line.
 *
 * @package		EDD
 * @subpackage	Classes/CLI
 * @copyright	Copyright (c) 2014, Pippin Williamson
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
	 * @global		array $edd_options
	 * @return		void
	 */
	public function details( $args, $assoc_args ) {
		global $edd_options;

		WP_CLI::line( sprintf( __( 'You are running EDD version: %s', 'edd' ), EDD_VERSION ) );
		WP_CLI::line( "\n" . sprintf( __( 'Test mode is: %s', 'edd' ), ( edd_is_test_mode() ? __( 'Enabled', 'edd' ) : __( 'Disabled', 'edd' ) ) ) );
		WP_CLI::line( sprintf( __( 'Ajax is: %s', 'edd' ), ( edd_is_ajax_enabled() ? __( 'Enabled', 'edd' ) : __( 'Disabled', 'edd' ) ) ) );
		WP_CLI::line( sprintf( __( 'Guest checkouts are: %s', 'edd' ), ( edd_no_guest_checkout() ? __( 'Disabled', 'edd' ) : __( 'Enabled', 'edd' ) ) ) );
		WP_CLI::line( sprintf( __( 'Symlinks are: %s', 'edd' ), ( apply_filters( 'edd_symlink_file_downloads', isset( $edd_options['symlink_file_downloads'] ) ) && function_exists( 'symlink' ) ? __( 'Enabled', 'edd' ) : __( 'Disabled', 'edd' ) ) ) );
		WP_CLI::line( "\n" . sprintf( __( 'Checkout page is: %s', 'edd' ), ( ! empty( $edd_options['purchase_page'] ) ? __( 'Valid', 'edd' ) : __( 'Invalid', 'edd' ) ) ) );
		WP_CLI::line( sprintf( __( 'Checkout URL is: %s', 'edd' ), ( ! empty( $edd_options['purchase_page'] ) ? get_permalink( $edd_options['purchase_page'] ) : __( 'Undefined', 'edd' ) ) ) );
		WP_CLI::line( sprintf( __( 'Success URL is: %s', 'edd' ), ( ! empty( $edd_options['success_page'] ) ? get_permalink( $edd_options['success_page'] ) : __( 'Undefined', 'edd' ) ) ) );
		WP_CLI::line( sprintf( __( 'Failure URL is: %s', 'edd' ), ( ! empty( $edd_options['failure_page'] ) ? get_permalink( $edd_options['failure_page'] ) : __( 'Undefined', 'edd' ) ) ) );
		WP_CLI::line( sprintf( __( 'Downloads slug is: %s', 'edd' ), ( defined( 'EDD_SLUG' ) ? '/' . EDD_SLUG : '/downloads' ) ) );
		WP_CLI::line( "\n" . sprintf( __( 'Taxes are: %s', 'edd' ), ( edd_use_taxes() ? __( 'Enabled', 'edd' ) : __( 'Disabled', 'edd' ) ) ) );
		WP_CLI::line( sprintf( __( 'Tax rate is: %s', 'edd' ), edd_get_tax_rate() * 100 . '%' ) );

		$rates = edd_get_tax_rates();
		if( ! empty( $rates ) ) {
			foreach( $rates as $rate ) {
				WP_CLI::line( sprintf( __( 'Country: %s, State: %s, Rate: %s', 'edd' ), $rate['country'], $rate['state'], $rate['rate'] ) );
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
		} elseif( empty( $date ) && empty( $startdate ) ) {
			$start_date = 'this_month';
			$end_date   = false;
		}

		$earnings   = $stats->get_earnings( $download, $start_date, $end_date );
		$sales      = $stats->get_sales( $download, $start_date, $end_date );

		WP_CLI::line( sprintf( __( 'Earnings: %s', 'edd' ), $earnings ) );
		WP_CLI::line( sprintf( __( 'Sales: %s', 'edd' ), $sales ) );

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
			WP_CLI::error( __( 'No Downloads found', 'edd' ) );
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
			WP_CLI::line( sprintf( __( 'ID: %d', 'edd' ), $product['info']['id'] ) );
			WP_CLI::line( sprintf( __( 'Status: %s', 'edd' ), $product['info']['status'] ) );
			WP_CLI::line( sprintf( __( 'Posted: %s', 'edd' ), $product['info']['create_date'] ) );
			WP_CLI::line( sprintf( __( 'Categories: %s', 'edd' ), $categories ) );
			WP_CLI::line( sprintf( __( 'Tags: %s', 'edd' ), ( is_array( $tags ) ? '' : $tags ) ) );
			WP_CLI::line( sprintf( __( 'Pricing: %s', 'edd' ), $pricing ) );
			WP_CLI::line( sprintf( __( 'Sales: %s', 'edd' ), $product['stats']['total']['sales'] ) );
			WP_CLI::line( sprintf( __( 'Earnings: %s', 'edd' ), edd_format_amount( $product['stats']['total']['earnings'] ) ) ) . ' ' . edd_get_currency();
			WP_CLI::line( '' );
			WP_CLI::line( sprintf( __( 'Slug: %s', 'edd' ), $product['info']['slug'] ) );
			WP_CLI::line( sprintf( __( 'Permalink: %s', 'edd' ), $product['info']['link'] ) );

			if( array_key_exists( 'files', $product ) ) {

				WP_CLI::line( '' );
				WP_CLI::line( __( 'Download Files:', 'edd' ) );

				foreach( $product['files'] as $file ) {

					WP_CLI::line( '  ' . sprintf( __( 'File: %s (%s)', 'edd' ), $file['name'], $file['file'] ) );

					if( isset( $file['condition'] ) && 'all' !== $file['condition'] ) {

						WP_CLI::line( '  ' . sprintf( __( 'Price Assignment: %s', 'edd' ), $file['condition'] ) );

					}

				}

			}

			WP_CLI::line( '' );
		}

	}


	/**
	 * Get the customers currently on your EDD site
	 *
	 * ## OPTIONS
	 *
	 * --id=<customer_id>: A specific customer ID to retrieve
	 *
	 * ## EXAMPLES
	 *
	 * wp edd customers --id=103
	 */
	public function customers( $args, $assoc_args ) {

		$customer_id = isset( $assoc_args ) && array_key_exists( 'id', $assoc_args ) ? absint( $assoc_args['id'] ) : false;
		$customers   = $this->api->get_customers( $customer_id );

		if( isset( $customers['error'] ) ) {
			WP_CLI::error( $customers['error'] );
		}

		if( empty( $customers ) ) {
			WP_CLI::error( __( 'No customers found', 'edd' ) );
			return;
		}

		foreach( $customers['customers'] as $customer ) {
			WP_CLI::line( WP_CLI::colorize( '%G' . $customer['info']['email'] . '%N' ) );
			WP_CLI::line( sprintf( __( 'Customer User ID: %s', 'edd' ), $customer['info']['id'] ) );
			WP_CLI::line( sprintf( __( 'Username: %s', 'edd' ), $customer['info']['username'] ) );
			WP_CLI::line( sprintf( __( 'Display Name: %s', 'edd' ), $customer['info']['display_name'] ) );

			if( array_key_exists( 'first_name', $customer ) ) {
				WP_CLI::line( sprintf( __( 'First Name: %s', 'edd' ), $customer['info']['first_name'] ) );
			}

			if( array_key_exists( 'last_name', $customer ) ) {
				WP_CLI::line( sprintf( __( 'Last Name: %s', 'edd' ), $customer['info']['last_name'] ) );
			}

			WP_CLI::line( sprintf( __( 'Email: %s', 'edd' ), $customer['info']['email'] ) );

			WP_CLI::line( '' );
			WP_CLI::line( sprintf( __( 'Purchases: %s', 'edd' ), $customer['stats']['total_purchases'] ) );
			WP_CLI::line( sprintf( __( 'Total Spent: %s', 'edd' ), edd_format_amount( $customer['stats']['total_spent'] ) . ' ' . edd_get_currency() ) );
			WP_CLI::line( sprintf( __( 'Total Downloads: %s', 'edd' ), $customer['stats']['total_downloads'] ) );

			WP_CLI::line( '' );
		}

	}


	/**
	 * Get the recent sales for your EDD site
	 *
	 * ## OPTIONS
	 *
	 *
	 * ## EXAMPLES
	 *
	 * wp edd sales
	 */
	public function sales( $args, $assoc_args ) {

		$sales = $this->api->get_recent_sales();

		if( empty( $sales ) ) {
			WP_CLI::error( __( 'No sales found', 'edd' ) );
			return;
		}

		foreach( $sales['sales'] as $sale ) {
			WP_CLI::line( WP_CLI::colorize( '%G' . $sale['ID'] . '%N' ) );
			WP_CLI::line( sprintf( __( 'Purchase Key: %s', 'edd' ), $sale['key'] ) );
			WP_CLI::line( sprintf( __( 'Email: %s', 'edd' ), $sale['email'] ) );
			WP_CLI::line( sprintf( __( 'Date: %s', 'edd' ), $sale['date'] ) );
			WP_CLI::line( sprintf( __( 'Subtotal: %s', 'edd' ), edd_format_amount( $sale['subtotal'] ) . ' ' . edd_get_currency() ) );
			WP_CLI::line( sprintf( __( 'Tax: %s', 'edd' ), edd_format_amount( $sale['tax'] ) . ' ' . edd_get_currency() ) );

			if( array_key_exists( 0, $sale['fees'] ) ) {
				WP_CLI::line( __( 'Fees:', 'edd' ) );

				foreach( $sale['fees'] as $fee ) {
					WP_CLI::line( sprintf( __( '  Fee: %s - %s', 'edd' ), edd_format_amount( $fee['amount'] ) . ' ' . edd_get_currency() ) );
				}
			}

			WP_CLI::line( sprintf( __( 'Total: %s', 'edd' ), edd_format_amount( $sale['total'] ) . ' ' . edd_get_currency() ) );
			WP_CLI::line( '' );
			WP_CLI::line( sprintf( __( 'Gateway: %s', 'edd' ), $sale['gateway'] ) );

			if( array_key_exists( 0, $sale['products'] ) ) {
				WP_CLI::line( __( 'Products:', 'edd' ) );

				foreach( $sale['products'] as $product ) {
					$price_name = ! empty( $product['price_name'] ) ? ' (' . $product['price_name'] . ')' : '';
					WP_CLI::line( sprintf( __( '  Product: %s - %s', 'edd' ), $product['name'], edd_format_amount( $product['price'] ) . ' ' . edd_get_currency() . $price_name ) );
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

		if( isset( $discounts['error'] ) ) {
			WP_CLI::error( $discounts['error'] );
		}

		if( empty( $discounts ) ) {
			WP_CLI::error( __( 'No discounts found', 'edd' ) );
			return;
		}

		foreach( $discounts['discounts'] as $discount ) {
			WP_CLI::line( WP_CLI::colorize( '%G' . $discount['ID'] . '%N' ) );
			WP_CLI::line( sprintf( __( 'Name: %s', 'edd' ), $discount['name'] ) );
			WP_CLI::line( sprintf( __( 'Code: %s', 'edd' ), $discount['code'] ) );

			if( $discount['type'] == 'percent' ) {
				$amount = $discount['amount'] . '%';
			} else {
				$amount = edd_format_amount( $discount['amount'] ) . ' ' . edd_get_currency();
			}

			WP_CLI::line( sprintf( __( 'Amount: %s', 'edd' ), $amount ) );
			WP_CLI::line( sprintf( __( 'Uses: %s', 'edd' ), $discount['uses'] ) );
			WP_CLI::line( sprintf( __( 'Max Uses: %s', 'edd' ), ( $discount['max_uses'] == '0' ? __( 'Unlimited', 'edd' ) : $discount['max_uses'] ) ) );
			WP_CLI::line( sprintf( __( 'Start Date: %s', 'edd' ), ( empty( $discount['start_date'] ) ? __( 'No Start Date', 'edd' ) : $discount['start_date'] ) ) );
			WP_CLI::line( sprintf( __( 'Expiration Date: %s', 'edd' ), ( empty( $discount['exp_date'] ) ? __( 'No Expiration', 'edd' ) : $discount['exp_date'] ) ) );
			WP_CLI::line( sprintf( __( 'Status: %s', 'edd' ), ucwords( $discount['status'] ) ) );

			WP_CLI::line( '' );

			if( array_key_exists( 0, $discount['product_requirements'] ) ) {
				WP_CLI::line( __( 'Product Requirements:', 'edd' ) );

				foreach( $discount['product_requirements'] as $req => $req_id ) {
					WP_CLI::line( sprintf( __( '  Product: %s', 'edd' ), $req_id ) );
				}
			}

			WP_CLI::line( '' );

			WP_CLI::line( sprintf( __( 'Global Discount: %s', 'edd' ), ( empty( $discount['global_discount'] ) ? 'False' : 'True' ) ) );
			WP_CLI::line( sprintf( __( 'Single Use: %s', 'edd' ), ( empty( $discount['single_use'] ) ? 'False' : 'True' ) ) );

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
	 * wp edd payments create --number=10 --status=completed
	 * wp edd payments create --number=10 --id=103
	 */
	public function payments( $args, $assoc_args ) {

		$error = false;

		// At some point we'll likely add another action for payments
		if( ! isset( $args ) ||  count( $args ) == 0 ) {
			$error = __( 'No action specified, did you mean', 'edd' );
		} elseif( isset( $args ) && ! in_array( 'create', $args ) ) {
			$error = __( 'Invalid action specified, did you mean', 'edd' );
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

		if( count( $assoc_args ) > 0 ) {
			$number     = ( array_key_exists( 'number', $assoc_args ) )   ? absint( $assoc_args['number'] ) : $number;
			$id         = ( array_key_exists( 'id', $assoc_args ) )       ? absint( $assoc_args['id'] )     : $id;
			$price_id   = ( array_key_exists( 'price_id', $assoc_args ) ) ? absint( $assoc_args['id'] )     : false;

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
						__( "Invalid status '%s', defaulting to 'complete'", 'edd' ),
						$assoc_args['status']
					) );
				}
			}
		}

		// Build the user info array
		$user_info = array(
			'id'            => 0,
			'email'         => 'guest@local.dev',
			'first_name'    => 'Pippin',
			'last_name'     => 'Williamson',
			'discount'      => 'none'
		);

		for( $i = 0; $i < $number; $i++ ) {

			$products = array();
			$total    = 0;

			// No specified product
			if( ! $id ) {

				$products = get_posts( array(
					'post_type'     => 'download',
					'orderby'       => 'rand',
					'order'         => 'ASC',
					'posts_per_page'=> 1
				) );

			} else {

				$product = get_post( $id );

				if( $product->post_type != 'download' ) {
					WP_CLI::error( __( 'Specified ID is not a product', 'edd' ) );
					return;
				}

				$products[] = $product;

			}

			// Create the purchases
			foreach( $products as $key => $download ) {

				if( ! is_a( $download, 'WP_Post' ) ) {
					continue;
				}

				$options = array();

				// Deal with variable pricing
				if( edd_has_variable_prices( $download->ID ) ) {

					$prices = edd_get_variable_prices( $download->ID );

					if( false === $price_id || ! array_key_exists( $price_id, (array) $prices ) ) {
						$price_id = rand( 0, count( $prices ) - 1 );
					}

					$item_price = $prices[ $price_id ]['amount'];
					$options['price_id'] = $price_id;

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
					'tax'         => 0
				);

				$total += $item_price;

			}

			$purchase_data = array(
				'price'	        => edd_sanitize_amount( $total ),
				'tax'           => 0,
				'purchase_key'  => strtolower( md5( uniqid() ) ),
				'user_email'    => 'guest@local.dev',
				'user_info'     => $user_info,
				'currency'      => edd_get_currency(),
				'downloads'     => (array) $download,
				'cart_details'  => $cart_details,
				'status'        => 'pending'
			);

			$payment_id = edd_insert_payment( $purchase_data );

			remove_action( 'edd_complete_purchase', 'edd_trigger_purchase_receipt', 999 );

			if( $status != 'pending' ) {
				edd_update_payment_status( $payment_id, $status );
			}
		}

		WP_CLI::success( sprintf( __( 'Created %s payments', 'edd' ), $number ) );
		return;
	}
}
