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
		WP_CLI::line( sprintf( __( 'Taxes are handled: %s', 'edd' ), ( edd_taxes_after_discounts() ? __( 'After discounts', 'edd' ) : __( 'Before discounts', 'edd' ) ) ) );
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
	 * --type=[sales|earnings|customers]: The type of stats to retrieve
	 * --product=[all|<product_id>]: The ID of a specific product to retrieve stats for, or all
	 * --date=[range|this_month|last_month|today|yesterday|this_quarter|last_quarter|this_year|last_year]: A specific date range to retrieve stats for
	 * --startdate=<date>: The start date of a date range to retrieve stats for
	 * --enddate=<date>: The end date of a date range to retrieve stats for
	 *
	 * --verbose: Print detailed stats
	 * --raw: Dump the relevant stats array
	 *
	 * ## EXAMPLES
	 *
	 * wp edd stats --type=sales --date=this_month
	 * wp edd stats --type=earnings --date=last_year
	 * wp edd stats --type=customers
	 */
	public function stats( $args, $assoc_args ) {
		WP_CLI::error( __( 'Pending EDD_API 2.0', 'edd' ) );
	}


	/**
	 * Get the products currently posted on your EDD site
	 *
	 * ## OPTIONS
	 *
	 * --id=<product_id>: A specific product ID to retrieve
	 *
	 * --verbose: Print detailed product info
	 * --raw: Dump the relevant product array
	 *
	 * ## EXAMPLES
	 *
	 * wp edd products --id=103
	 * wp edd products --id=103 --verbose
	 * wp edd products --id=103 --raw
	 */
	public function products( $args, $assoc_args ) {

		$product_id = isset( $assoc_args ) && array_key_exists( 'id', $assoc_args ) ? absint( $assoc_args['id'] ) : false;
		$products   = $this->api->get_products( $product_id );

		if( isset( $products['error'] ) ) {
			WP_CLI::error( $products['error'] );
		}

		if( empty( $products ) ) {
			WP_CLI::error( __( 'No Downloads found', 'edd' ) );
		}

		foreach( $products['products'] as $product ) {
		
			$categories	= array();
			$tags		= array();
			$pricing	= array();

			if( is_array( $product['info']['category'] ) ) {

				foreach( $product['info']['category'] as $category ) {
					$categories[] = $category->name;
				}

				$categories = implode( ', ', $categories );

			}

			if( is_array( $product['info']['tags'] ) ) {
			
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
	 * verbose: Print detailed customer info
	 * raw: Dump the relevant customer array
	 *
	 * ## EXAMPLES
	 *
	 * wp edd customers --id=103
	 * wp edd customers --id=103 --verbose
	 * wp edd customers --id=103 --raw
	 */
	public function customers( $args, $assoc_args ) {
		$verbose	= ( array_key_exists( 'verbose', $assoc_args ) ? true : false );
		$raw		= ( array_key_exists( 'raw', $assoc_args ) ? true : false );
		$curr_pos	= edd_get_option( 'currency_position', 'before' );

		if( isset( $assoc_args ) && array_key_exists( 'id', $assoc_args ) && !empty( $assoc_args['id'] ) ) {
			$customers = $this->api->get_customers( $assoc_args['id'] );

			if( isset( $customers['error'] ) ) {
				WP_CLI::error( $customers['error'] );
			} else {
				$customer = $customers['customers'][0];

				if( $raw ) {
					print_r( $customer );
				} else {
					WP_CLI::line( WP_CLI::colorize( '%G' . $customer['info']['email'] . '%N' ) );
					WP_CLI::line( sprintf( __( 'Customer ID: %s', 'edd' ), $customer['info']['id'] ) );
					WP_CLI::line( sprintf( __( 'Username: %s', 'edd' ), $customer['info']['username'] ) );
					WP_CLI::line( sprintf( __( 'Display Name: %s', 'edd' ), $customer['info']['display_name'] ) );

					if( array_key_exists( 'first_name', $customer ) ) {
						WP_CLI::line( sprintf( __( 'First Name: %s', 'edd' ), $customer['info']['first_name'] ) );
					}

					if( array_key_exists( 'last_name', $customer ) ) {
						WP_CLI::line( sprintf( __( 'Last Name: %s', 'edd' ), $customer['info']['last_name'] ) );
					}

					WP_CLI::line( sprintf( __( 'Email: %s', 'edd' ), $customer['info']['email'] ) );

					if( $verbose ) {
						WP_CLI::line( '' );
						WP_CLI::line( sprintf( __( 'Purchases: %s', 'edd' ), $customer['stats']['total_purchases'] ) );
						WP_CLI::line( sprintf( __( 'Total Spent: %s', 'edd' ), ( $curr_pos == 'before' ? edd_get_currency() . ' ' : '' ) . edd_format_amount( $customer['stats']['total_spent'] ) . ( $curr_pos == 'after' ? ' ' . edd_get_currency() : '' ) ) );
						WP_CLI::line( sprintf( __( 'Total Downloads: %s', 'edd' ), $customer['stats']['total_downloads'] ) );
					}
				}
			}
		} else {
			$customers = $this->api->get_customers();

			if( $raw ) {
				print_r( $customers );
			} else {
				foreach( $customers['customers'] as $customer ) {
					WP_CLI::line( WP_CLI::colorize( '%G' . $customer['info']['email'] . '%N' ) );
					WP_CLI::line( sprintf( __( 'Customer ID: %s', 'edd' ), $customer['info']['id'] ) );
					WP_CLI::line( sprintf( __( 'Username: %s', 'edd' ), $customer['info']['username'] ) );
					WP_CLI::line( sprintf( __( 'Display Name: %s', 'edd' ), $customer['info']['display_name'] ) );

					if( array_key_exists( 'first_name', $customer ) ) {
						WP_CLI::line( sprintf( __( 'First Name: %s', 'edd' ), $customer['info']['first_name'] ) );
					}

					if( array_key_exists( 'last_name', $customer ) ) {
						WP_CLI::line( sprintf( __( 'Last Name: %s', 'edd' ), $customer['info']['last_name'] ) );
					}

					WP_CLI::line( sprintf( __( 'Email: %s', 'edd' ), $customer['info']['email'] ) );

					if( $verbose ) {
						WP_CLI::line( '' );
						WP_CLI::line( sprintf( __( 'Purchases: %s', 'edd' ), $customer['stats']['total_purchases'] ) );
						WP_CLI::line( sprintf( __( 'Total Spent: %s', 'edd' ), ( $curr_pos == 'before' ? edd_get_currency() . ' ' : '' ) . edd_format_amount( $customer['stats']['total_spent'] ) . ( $curr_pos == 'after' ? ' ' . edd_get_currency() : '' ) ) );
						WP_CLI::line( sprintf( __( 'Total Downloads: %s', 'edd' ), $customer['stats']['total_downloads'] ) );
					}

					WP_CLI::line( '' );
				}
			}
		}
	}


	/**
	 * Get the recent sales for your EDD site
	 *
	 * ## OPTIONS
	 *
	 * --verbose: Print detailed customer info
	 * --raw: Dump the relevant customer array
	 *
	 * ## EXAMPLES
	 *
	 * wp edd sales
	 * wp edd sales --verbose
	 * wp edd sales --raw
	 */
	public function sales( $args, $assoc_args ) {
		$verbose	= ( array_key_exists( 'verbose', $assoc_args ) ? true : false );
		$raw		= ( array_key_exists( 'raw', $assoc_args ) ? true : false );
		$curr_pos	= edd_get_option( 'currency_position', 'before' );

		$sales = $this->api->get_recent_sales();

		if( $raw ) {
			print_r( $sales );
		} else {
			foreach( $sales['sales'] as $sale ) {
				WP_CLI::line( WP_CLI::colorize( '%G' . $sale['ID'] . '%N' ) );
				WP_CLI::line( sprintf( __( 'Purchase Key: %s', 'edd' ), $sale['key'] ) );
				WP_CLI::line( sprintf( __( 'Email: %s', 'edd' ), $sale['email'] ) );
				WP_CLI::line( sprintf( __( 'Date: %s', 'edd' ), $sale['date'] ) );
				WP_CLI::line( sprintf( __( 'Subtotal: %s', 'edd' ), ( $curr_pos == 'before' ? edd_get_currency() . ' ' : '' ) . edd_format_amount( $sale['subtotal'] ) . ( $curr_pos == 'after' ? ' ' . edd_get_currency() : '' ) ) );
				WP_CLI::line( sprintf( __( 'Tax: %s', 'edd' ), ( $curr_pos == 'before' ? edd_get_currency() . ' ' : '' ) . edd_format_amount( $sale['tax'] ) . ( $curr_pos == 'after' ? ' ' . edd_get_currency() : '' ) ) );

				if( array_key_exists( 0, $sale['fees'] ) ) {
					WP_CLI::line( __( 'Fees:', 'edd' ) );

					foreach( $sale['fees'] as $fee ) {
						WP_CLI::line( sprintf( __( '  Fee: %s - %s', 'edd' ), $fee['label'], ( $curr_pos == 'before' ? edd_get_currency() . ' ' : '' ) . edd_format_amount( $fee['amount'] ) . ( $curr_pos == 'after' ? ' ' . edd_get_currency() : '' ) ) );
					}
				}

				WP_CLI::line( sprintf( __( 'Total: %s', 'edd' ), ( $curr_pos == 'before' ? edd_get_currency() . ' ' : '' ) . edd_format_amount( $sale['total'] ) . ( $curr_pos == 'after' ? ' ' . edd_get_currency() : '' ) ) );

				if( $verbose ) {
					WP_CLI::line( '' );
					WP_CLI::line( sprintf( __( 'Gateway: %s', 'edd' ), $sale['gateway'] ) );

					if( array_key_exists( 0, $sale['products'] ) ) {
						WP_CLI::line( __( 'Products:', 'edd' ) );

						foreach( $sale['products'] as $product ) {
							WP_CLI::line( sprintf( __( '  Product: %s - %s %s', 'edd' ), $product['name'], ( $curr_pos == 'before' ? edd_get_currency() . ' ' : '' ) . edd_format_amount( $product['price'] ) . ( $curr_pos == 'after' ? ' ' . edd_get_currency() : '' ), !empty( $product['price_name'] ) ? '(' . $product['price_name'] . ')' : '' ) );
						}
					}
				}

				WP_CLI::line( '' );
			}
		}
	}


	/**
	 * Get discount details for on your EDD site
	 *
	 * ## OPTIONS
	 *
	 * --id=<product_id>: A specific discount ID to retrieve
	 *
	 * --verbose: Print detailed discount info
	 * --raw: Dump the relevant discount array
	 *
	 * ## EXAMPLES
	 *
	 * wp edd discounts --id=103
	 * wp edd discounts --id=103 --verbose
	 * wp edd discounts --id=103 --raw
	 */
	public function discounts( $args, $assoc_args ) {
		$verbose	= ( array_key_exists( 'verbose', $assoc_args ) ? true : false );
		$raw		= ( array_key_exists( 'raw', $assoc_args ) ? true : false );
		$curr_pos	= edd_get_option( 'currency_position', 'before' );

		if( isset( $assoc_args ) && array_key_exists( 'id', $assoc_args ) && !empty( $assoc_args['id'] ) ) {
			$discounts = $this->api->get_discounts( $assoc_args['id'] );

			if( isset( $discounts['error'] ) ) {
				WP_CLI::error( $discounts['error'] );
			} else {
				$discounts = $discounts['discounts'][0];

				if( $raw ) {
					print_r( $discounts );
				} else {
					WP_CLI::line( WP_CLI::colorize( '%G' . $discounts['ID'] . '%N' ) );
					WP_CLI::line( sprintf( __( 'Name: %s', 'edd' ), $discounts['name'] ) );
					WP_CLI::line( sprintf( __( 'Code: %s', 'edd' ), $discounts['code'] ) );

					if( $discounts['type'] == 'percent' ) {
						$amount = $discounts['amount'] . '%';
					} else {
						$amount = ( $curr_pos == 'before' ? edd_get_currency() . ' ' : '' ) . edd_format_amount( $discounts['amount'] ) . ( $curr_pos == 'after' ? ' ' . edd_get_currency() : '' );
					}

					WP_CLI::line( sprintf( __( 'Amount: %s', 'edd' ), $amount ) );
					WP_CLI::line( sprintf( __( 'Uses: %s', 'edd' ), $discounts['uses'] ) );
					WP_CLI::line( sprintf( __( 'Max Uses: %s', 'edd' ), ( $discounts['max_uses'] == '0' ? __( 'Unlimited', 'edd' ) : $discounts['max_uses'] ) ) );
					WP_CLI::line( sprintf( __( 'Start Date: %s', 'edd' ), ( empty( $discounts['start_date'] ) ? __( 'No Start Date', 'edd' ) : $discounts['start_date'] ) ) );
					WP_CLI::line( sprintf( __( 'Expiration Date: %s', 'edd' ), ( empty( $discounts['exp_date'] ) ? __( 'No Expiration', 'edd' ) : $discounts['exp_date'] ) ) );
					WP_CLI::line( sprintf( __( 'Status: %s', 'edd' ), ucwords( $discounts['status'] ) ) );

					if( $verbose ) {
						WP_CLI::line( '' );
						if( array_key_exists( 0, $discounts['product_requirements'] ) ) {
							WP_CLI::line( __( 'Product Requirements:', 'edd' ) );

							foreach( $discounts['product_requirements'] as $req => $req_id ) {
								WP_CLI::line( sprintf( __( '  Product: %s', 'edd' ), $req_id ) );
							}
						}

						WP_CLI::line( '' );

						WP_CLI::line( sprintf( __( 'Global Discount: %s', 'edd' ), ( empty( $discounts['global_discount'] ) ? 'False' : 'True' ) ) );
						WP_CLI::line( sprintf( __( 'Single Use: %s', 'edd' ), ( empty( $discounts['single_use'] ) ? 'False' : 'True' ) ) );

					}
				}
			}
		} else {
			$discounts = $this->api->get_discounts();

			if( $raw ) {
				print_r( $discounts );
			} else {
				foreach( $discounts['discounts'] as $discount ) {
					WP_CLI::line( WP_CLI::colorize( '%G' . $discount['ID'] . '%N' ) );
					WP_CLI::line( sprintf( __( 'Name: %s', 'edd' ), $discount['name'] ) );
					WP_CLI::line( sprintf( __( 'Code: %s', 'edd' ), $discount['code'] ) );

					if( $discount['type'] == 'percent' ) {
						$amount = $discount['amount'] . '%';
					} else {
						$amount = ( $curr_pos == 'before' ? edd_get_currency() . ' ' : '' ) . edd_format_amount( $discount['amount'] ) . ( $curr_pos == 'after' ? ' ' . edd_get_currency() : '' );
					}

					WP_CLI::line( sprintf( __( 'Amount: %s', 'edd' ), $amount ) );
					WP_CLI::line( sprintf( __( 'Uses: %s', 'edd' ), $discount['uses'] ) );
					WP_CLI::line( sprintf( __( 'Max Uses: %s', 'edd' ), ( $discount['max_uses'] == '0' ? __( 'Unlimited', 'edd' ) : $discount['max_uses'] ) ) );
					WP_CLI::line( sprintf( __( 'Start Date: %s', 'edd' ), ( empty( $discount['start_date'] ) ? __( 'No Start Date', 'edd' ) : $discount['start_date'] ) ) );
					WP_CLI::line( sprintf( __( 'Expiration Date: %s', 'edd' ), ( empty( $discount['exp_date'] ) ? __( 'No Expiration', 'edd' ) : $discount['exp_date'] ) ) );
					WP_CLI::line( sprintf( __( 'Status: %s', 'edd' ), ucwords( $discount['status'] ) ) );

					if( $verbose ) {
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
					}

					WP_CLI::line( '' );
				}
			}
		}
	}
}
