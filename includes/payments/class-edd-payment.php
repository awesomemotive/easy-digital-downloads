<?php
/**
 * Payments
 *
 * This class is for working with payments in EDD.
 *
 * @package     EDD
 * @subpackage  Classes/Payment
 * @copyright   Copyright (c) 2014, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       2.4
 */


// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) exit;


/**
 * EDD_Payment Class
 *
 * @since 2.5
 */
class EDD_Payment {

	/**
	 * The Payment we are working with
	 *
	 * @var int
	 * @access private
	 * @since 2.5
	 */

	protected $ID = 0;
	protected $number = '';
	protected $mode = 'live';
	protected $key = '';
	protected $total = 0;
	protected $subtotal = 0;
	protected $tax = 0;
	protected $fees = array();
	protected $discount = 0;
	protected $discounts = array();
	protected $date = '';
	protected $completed_date = '';
	protected $status = 'pending';
	protected $post_status = 'pending'; // Same as $status but here for backwards compat
	protected $customer_id = null;
	protected $user_id = 0;
	protected $email = '';
	protected $user_info = array();
	protected $transaction_id = '';
	protected $downloads = array();
	protected $ip = '';
	protected $gateway = '';
	protected $currency = '';
	protected $cart_details = array();
	protected $has_unlimited_downloads = false;
	protected $pending;

	/**
	 * Setup the EDD Payments class
	 *
	 * @since 2.5
	 * @param int $payment_id A given payment
	 * @return mixed void|false
	 */
	public function __construct( $payment_id = false ) {

		if( empty( $payment_id ) ) {
			return false;
		}

		$this->setup_payment( $payment_id );
	}

	public function __get( $name ) {
		return $this->$name;
	}

	public function __set( $name, $value ) {
		$ignore = array( 'downloads', 'cart_details', 'fees' );
		if ( ! in_array( $name, $ignore ) ) {
			$this->pending[ $name ] = $value;
		}

		$this->$name = $value;
	}

	/**
	 * Setup payment properties
	 *
	 * @since  2.5
	 * @param  int $payment_id The payment ID
	 * @return bool            If the setup was successful or not
	 */
	private function setup_payment( $payment_id ) {
		$this->pending = array();

		if ( empty( $payment_id ) ) {
			return false;
		}

		$payment = get_post( $payment_id );

		if( ! $payment || is_wp_error( $payment ) ) {
			return false;
		}

		if( 'edd_payment' !== $payment->post_type ) {
			return false;
		}

		// Primary Identifier
		$this->ID             = absint( $payment_id );

		// We have a payment, get the generic payment_meta item to reduce calls to it
		$payment_meta   = $this->get_meta();

		// Status and Dates
		$this->date           = $payment->post_date;
		$this->completed_date = $this->setup_completed_date();
		$this->status         = $payment->post_status;
		$this->post_status    = $this->status;
		$this->mode           = $this->setup_mode();

		// Items
		$this->fees           = $this->setup_fees( $payment_meta );
		$this->cart_details   = $this->setup_cart_details( $payment_meta );
		$this->downloads      = $this->setup_downloads( $payment_meta );

		// Currency Based
		$this->total          = $this->setup_total();
		$this->tax            = $this->setup_tax();
		$this->subtotal       = $this->setup_subtotal();
		$this->currency       = $this->setup_currency( $payment_meta );

		// Gateway based
		$this->gateway        = $this->setup_gateway();
		$this->transaction_id = $this->setup_transaction_id();

		// User based
		$this->ip             = $this->setup_ip();
		$this->customer_id    = $this->setup_customer_id();
		$this->user_id        = $this->setup_user_id();
		$this->email          = $this->setup_email();
		$this->user_info      = $this->setup_user_info( $payment_meta );

		// Other Identifiers
		$this->key            = $this->setup_payment_key();
		$this->number         = $this->setup_payment_number();

		// Additional Attributes
		$this->has_unlimited_downloads = $this->setup_has_unlimited();

		// Allow extensions to add items to this object via hook
		do_action( 'edd_setup_payment', $this, $payment_id );

		return true;
	}

	public function create_payment( $payment_data = array() ) {
		if ( empty( $payment_data ) ) {
			return false;
		}

		// Make sure the payment is inserted with the correct timezone
		date_default_timezone_set( edd_get_timezone_id() );

		// Construct the payment title
		if ( isset( $payment_data['user_info']['first_name'] ) || isset( $payment_data['user_info']['last_name'] ) ) {
			$payment_title = $payment_data['user_info']['first_name'] . ' ' . $payment_data['user_info']['last_name'];
		} else {
			$payment_title = $payment_data['user_email'];
		}

		// Retrieve the ID of the discount used, if any
		if ( $payment_data['user_info']['discount'] != 'none' ) {
			$discount = edd_get_discount_by( 'code', $payment_data['user_info']['discount'] );
		}

		// Find the next payment number, if enabled
		if( edd_get_option( 'enable_sequential' ) ) {
			$number = edd_get_next_payment_number();
		}

		$args = apply_filters( 'edd_insert_payment_args', array(
			'post_title'    => $payment_title,
			'post_status'   => isset( $payment_data['status'] ) ? $payment_data['status'] : 'pending',
			'post_type'     => 'edd_payment',
			'post_parent'   => isset( $payment_data['parent'] ) ? $payment_data['parent'] : null,
			'post_date'     => isset( $payment_data['post_date'] ) ? $payment_data['post_date'] : null,
			'post_date_gmt' => isset( $payment_data['post_date'] ) ? get_gmt_from_date( $payment_data['post_date'] ) : null
		), $payment_data );

		// Create a blank payment
		$payment_id = wp_insert_post( $args );

		if ( ! empty( $payment_id ) ) {
			$this->ID    = $payment_id;
		}

		return $this->ID;

	}

	public function save() {
		$saved = false;

		$new_meta = array(
			'downloads'     => $this->downloads,
			'cart_details'  => $this->cart_details,
			'fees'          => $this->fees,
			'currency'      => $this->currency,
			'user_info'     => $this->user_info,
		);

		$meta        = $this->get_meta();
		$merged_meta = array_merge( $meta, $new_meta );

		// Only save the payment meta if it's changed
		if ( md5( serialize( $meta ) ) !== md5( serialize( $merged_meta) ) ) {
			$updated     = $this->update_meta( '_edd_payment_meta', $merged_meta );
			if ( false !== $updated ) {
				$saved = true;
			}
		}

		// If we have something pending, let's save it
		if ( ! empty( $this->pending ) ) {
			$total_increase = 0;
			$total_decrease = 0;

			foreach ( $this->pending as $key => $value ) {
				switch( $key ) {
					case 'downloads':
						// Update totals for pending downloads
						foreach ( $this->pending[ $key ] as $item ) {

							switch( $item['action'] ) {

								case 'add':
									$price = $item['price'];
									$taxes = $item['tax'];

									if ( 'publish' == $this->status ) {
										// Add sales logs
										$log_date =  date( 'Y-m-d G:i:s', current_time( 'timestamp', true ) );
										$price_id = isset( $item['item_number']['options']['price_id'] ) ? $item['item_number']['options']['price_id'] : 0;

										$y = 0;

										while ( $y < $item['quantity'] ) {
											echo 'logging: ' . $item['id'] . ' for payment ' . $this->ID; echo "\n";
											edd_record_sale_in_log( $item['id'], $this->ID, $price_id, $log_date );
											$y++;

										}
									}

									if ( 'pending' !== $this->status ) {
										$download = new EDD_Download( $item['id'] );
										$download->increase_sales( $item['quantity'] );
										$download->increase_earnings( $price );

										$total_increase += $price;
									}
									break;

									case 'remove':
										$log_args = array(
											'post_type'   => 'edd_log',
											'post_parent' => $item['id'],
											'numberposts' => $item['quantity'],
											'meta_query'  => array(
												array(
													'key'     => '_edd_log_payment_id',
													'value'   => $this->ID,
													'compare' => '=',
												),
												array(
													'key'     => '_edd_log_price_id',
													'value'   => $item['price_id'],
													'compare' => '='
												)
											)
										);

										$found_logs = get_posts( $log_args );
										foreach ( $found_logs as $log ) {
											wp_delete_post( $log->ID, true );
										}

										if ( 'pending' !== $this->status ) {
											$download = new EDD_Download( $item['id'] );
											$download->decrease_sales( $item['quantity'] );
											$download->decrease_earnings( $item['amount'] );

											$total_decrease += $item['amount'];
										}
										break;

							}

						}
						break;

					case 'fees':
						if ( ! empty( $this->pending[ $key ] ) ) {
							// Update totals for pending fees
							foreach ( $this->pending->fees as $fee ) {
								$total_increase += $fee['amount'];
							}
						}
						break;

					case 'gateway':
						$this->update_meta( '_edd_payment_gateway', $this->gateway );
						break;

					case 'mode':
						$this->update_meta( '_edd_payment_mode', $this->mode );
						break;

					case 'transaction_id':
						$this->update_meta( '_edd_payment_transaction_id', $this->transaction_id );
						break;

					case 'ip':
						$this->update_meta( '_edd_payment_user_ip', $this->ip );
						break;

					case 'customer_id':
						$this->update_meta( '_edd_payment_customer_id', $this->customer_id );
						break;

					case 'user_id':
						$this->update_meta( '_edd_payment_user_id', $this->user_id );
						break;

					case 'email':
						$this->update_meta( '_edd_payment_user_email', $this->email );
						break;

					case 'key':
						$this->update_meta( '_edd_payment_purchase_key', $this->key );
						break;

					case 'number':
						$this->update_meta( '_edd_payment_number', $this->number );
						break;

					case 'completed_date':
						$this->update_meta( '_edd_completed_date', $this->completed_date );
						break;

					case 'has_unlimited_downloads':
						$this->update_meta( '_edd_payment_unlimited_downloads', $this->has_unlimited_downloads );
						break;

					default:
						do_action( 'edd_payment_save', $this, $key );
						break;
				}
			}



			// Kick off some actual updates of the details
			$this->update_meta( '_edd_payment_total', $this->total );
			$this->update_meta( '_edd_payment_tax', $this->tax );

			if ( 'pending' !== $this->status ) {

				$customer = new EDD_Customer( $this->customer_id );

				$total_change = $total_increase - $total_decrease;
				if ( $total_change < 0 ) {

					$total_chnage = -( $total_change );
					// Decrease the customer's purchase stats
					$customer->decrease_value( $total_change );
					edd_decrease_total_earnings( $total_change );

				} else if (  $total_change > 0 ) {

					// Increase the customer's purchase stats
					$customer->increase_value( $total_change );
					edd_increase_total_earnings( $total_change );

				}

			}

			$this->pending = array();
			$saved         = true;
		}

		return $saved;
	}

	/**
	 * Add a download to a given payment
	 *
	 * @since 2.5
	 * @param int  $download_id The download to add
	 * @param int  $args Other arguments to pass to the function
	 * @return void
	 */
	public function add_download( $download_id = 0, $args = array() ) {
		$download = new EDD_Download( $download_id );

		// Bail if this post isn't a download
		if( ! $download || $download->post_type !== 'download' ) {
			return false;
		}

		// Set some defaults
		$defaults = array(
			'quantity'    => 1,
			'price_id'    => false,
			'amount'      => false,
			'tax'         => 0,
			'fees'        => array(),
		);

		$args = wp_parse_args( apply_filters( 'edd_payment_add_download_args', $args, $download->ID ), $defaults );

		// Allow overriding the price
		if( $args['amount'] ) {
			$amount = $args['amount'];
		} else {
			// Deal with variable pricing
			if( edd_has_variable_prices( $download->ID ) ) {
				$prices = get_post_meta( $download->ID, 'edd_variable_prices', true );

				if( $args['price_id'] && array_key_exists( $args['price_id'], (array) $prices ) ) {
					$amount = $prices[$args['price_id']]['amount'];
				} else {
					$amount = edd_get_lowest_price_option( $download->ID );
					$args['price_id'] = edd_get_lowest_price_id( $download->ID );
				}
			} else {
				$amount = edd_get_download_price( $download->ID );
			}
		}

		// Sanitizing the price here so we don't have a dozen calls later
		$amount = edd_sanitize_amount( $amount );
		$item_price = round( $amount / $args['quantity'], edd_currency_decimal_filter() );

		// Silly item_number array
		$item_number = array(
			'id'        => $download->ID,
			'quantity'  => $args['quantity'],
			'options'   => array(
				'quantity'  => $args['quantity'],
			),
		);

		if ( false !== $args['price_id'] ) {
			$item_number['options']['price_id'] = $args['price_id'];
		}

		$this->cart_details[] = array(
			'name'        => $download->post_title,
			'id'          => $download->ID,
			'item_number' => $item_number,
			'price'       => $amount,
			'item_price'  => $item_price,
			'quantity'    => $args['quantity'],
			'tax'         => $args['tax'],
			'subtotal'    => ( $item_price * $args['quantity'] ),
		);

		// Setup the downloads meta item
		$new_download = array(
			'id'       => $download->ID,
			'quantity' => (int) $args['quantity'],
		);

		if ( ! empty( $args['price_id'] ) ) {
			$new_download['options']['price_id'] = (int) $args['price_id'];
		}

		$this->downloads[] = $new_download;

		if ( ! empty( $args['fees'] ) ) {
			foreach ( $args['fees'] as $fee ) {
				$this->add_fee( $fee['label'], $fee['amount'], $fee['type'] );
			}
		}

		$added_download = end( $this->cart_details );
		$added_download['action']  = 'add';

		$this->pending['downloads'][] = $added_download;
		reset( $this->cart_details );

		$this->increase_subtotal( $amount );
		$this->increase_tax( $args['tax'] );

		return true;

	}

	public function remove_download( $download_id, $args ) {

		// Set some defaults
		$defaults = array(
			'quantity'    => 1,
			'amount'      => false,
			'price_id'    => false,
		);
		$args = wp_parse_args( $args, $defaults );

		$download = new EDD_Download( $download_id );

		// Bail if this post isn't a download
		if( ! $download || $download->post_type !== 'download' ) {
			return false;
		}

		$total = 0;
		$tax   = 0;

		foreach ( $this->downloads as $key => $item ) {

			if ( $download_id != $item['id'] ) {
				continue;
			}

			if ( false !== $args['price_id'] ) {
				if ( $args['price_id'] != $item['price_id'] ) {
					continue;
				}
			}

			unset( $this->downloads[ $key ] );

		}

		foreach ( $this->cart_details as $cart_key => $item ) {

			if ( $download_id != $item['id'] ) {
				continue;
			}

			if ( false !== $args['price_id'] ) {
				if ( $args['price_id'] != $item['item_number']['options']['price_id'] ) {
					continue;
				}
			}

			$total = $this->cart_details[ $cart_key ]['price'];
			$tax   = $this->cart_details[ $cart_key ]['tax'];

			unset( $this->cart_details[ $cart_key ] );

		}

		$pending_args           = $args;
		$pending_args['id']     = $download_id;
		$pending_args['price_id'] = false !== $args['price_id'] ? $args['price_id'] : false;
		$pending_args['action'] = 'remove';

		$this->pending['downloads'][] = $pending_args;

		$this->decrease_subtotal( $total );
		$this->decrease_tax( $tax );

		return true;
	}

	/**
	 * Add a fee to a given payment
	 *
	 * @since 2.5
	 * @param string $label  The description of the fee
	 * @param float  $amount The amount of the fee
	 * @param string $type   The Fee Type
	 * @return void
	 */
	public function add_fee( $label = '', $amount = 0.00, $type = '' ) {
		$this->fees[] = array(
			'label'  => $label,
			'amount' => $amount,
			'type'   => $type,
		);

		$this->pending['fees'][] = end( $this->fees );
		reset( $this->fees );

		$this->increase_subtotal( $amount );

		return true;
	}

	/**
	 * Add a discount to a given payment
	 *
	 * @since 2.5
	 * @param string $code The discount code to apply
	 * @return void
	 */
	public function add_discount( $code ) {

	}

	public function increase_subtotal( $amount = 0.00 ) {
		$amount          = (float) $amount;
		$this->subtotal += $amount;

		$this->recalculate_total();
	}

	public function decrease_subtotal( $amount = 0.00 ) {
		$amount          = (float) $amount;
		$this->subtotal -= $amount;

		if ( $this->subtotal < 0 ) {
			$this->subtotal = 0;
		}

		$this->recalculate_total();
	}

	/**
	 * Set or update the total for a payment
	 *
	 * @since 2.5
	 * @param int $amount The amount of the payment
	 * @return void
	 */
	private function recalculate_total() {
		$this->total = $this->subtotal + $this->tax;
	}

	public function increase_tax( $amount = 0.00 ) {
		$amount       = (float) $amount;
		$this->tax   += $amount;

		$this->recalculate_total();
	}

	public function decrease_tax( $amount = 0.00 ) {
		$amount     = (float) $amount;
		$this->tax -= $amount;

		if ( $this->tax < 0 ) {
			$this->tax = 0;
		}

		$this->recalculate_total();
	}

	public function update_currency( $currency ) {
		$this->currency = $currency;
	}

	private function set_gateway() {
		$this->update_meta( '_edd_payment_gateway', $this->gateway );
	}

	/**
	 * Add a note to a payment
	 *
	 * @since 2.5
	 * @param string $note The note to add
	 * @return void
	 */
	public function add_note( $note = false ) {
		// Bail if no note specified
		if( ! $note ) {
			return false;
		}

		edd_insert_payment_note( $this->ID, $note );
	}


	/**
	 * Set the payment status
	 *
	 * @since 2.5
	 *
	 * @param  string $status The status to set the payment to
	 * @return bool Returns if the status was successfully updated
	 */
	public function update_status( $status = false ) {

		if ( $status == 'completed' || $status == 'complete' ) {
			$status = 'publish';
		}

		$old_status = $this->status;

		if ( $old_status === $status ) {
			return false; // Don't permit status changes that aren't changes
		}

		$do_change = apply_filters( 'edd_should_update_payment_status', true, $this->ID, $status, $old_status );

		$updated = false;

		if ( $do_change ) {

			do_action( 'edd_before_payment_status_change', $this->ID, $status, $old_status );

			$update_fields = array( 'ID' => $this->ID, 'post_status' => $status, 'edit_date' => current_time( 'mysql' ) );

			$updated = wp_update_post( apply_filters( 'edd_update_payment_status_fields', $update_fields ) );

			do_action( 'edd_update_payment_status', $this->ID, $status, $old_status );

		}

		return $updated;

	}

	public function get_meta( $meta_key = '_edd_payment_meta', $single = true ) {

		$meta = get_post_meta( $this->ID, $meta_key, $single );

		if ( $meta_key === '_edd_payment_meta' ) {

			// Payment meta was simplified in EDD v1.5, so these are here for backwards compatibility
			if ( empty( $meta['key'] ) ) {
				$meta['key'] = $this->setup_payment_key();
			}

			if ( empty( $meta['email'] ) ) {
				$meta['email'] = $this->setup_email();
			}

			if ( empty( $meta['date'] ) ) {
				$meta['date'] = get_post_field( 'post_date', $this->ID );
			}
		}

		$meta = apply_filters( 'edd_get_payment_meta_' . $meta_key, $meta, $this->ID );

		return apply_filters( 'edd_get_payment_meta', $meta, $this->ID, $meta_key );
	}

	public function update_meta( $meta_key = '', $meta_value = '', $prev_value = '' ) {
		if ( empty( $meta_key ) ) {
			return false;
		}

		if ( $meta_key == 'key' || $meta_key == 'date' ) {

			$current_meta = $this->get_meta();
			$current_meta[ $meta_key ] = $meta_value;

			$meta_key     = '_edd_payment_meta';
			$meta_value   = $current_meta;

		} else if ( $meta_key == 'email' || $meta_key == '_edd_payment_user_email' ) {

			$meta_value = apply_filters( 'edd_edd_update_payment_meta_' . $meta_key, $meta_value, $this->ID );
			update_post_meta( $this->ID, '_edd_payment_user_email', $meta_value );

			$current_meta = $this->get_meta();
			$current_meta['user_info']['email']  = $meta_value;

			$meta_key     = '_edd_payment_meta';
			$meta_value   = $current_meta;

		}

		$meta_value = apply_filters( 'edd_update_payment_meta_' . $meta_key, $meta_value, $this->ID );

		return update_post_meta( $this->ID, $meta_key, $meta_value, $prev_value );
	}

	/**
	 * Setup functions only, these are not to be used by developers.
	 * These functions exist only to allow the setup routine to be backwards compatible with our old
	 * helper functions.
	 *
	 * These will run whenever setup_payment is called, which should only be called once.
	 * To update an attribute, update it directly instead of re-running the setup routine
	 */

	private function setup_completed_date() {
		$payment = get_post( $this->ID );

		if( 'pending' == $payment->post_status || 'preapproved' == $payment->post_status ) {
			return false; // This payment was never completed
		}

		$date = ( $date = $this->get_meta( '_edd_completed_date', true ) ) ? $date : $payment->modified_date;

		return apply_filters( 'edd_payment_completed_date', $date, $this->ID );
	}

	private function setup_mode() {
		return $this->get_meta( '_edd_payment_mode' );
	}

	private function setup_total() {
		$amount = $this->get_meta( '_edd_payment_total', true );

		if ( empty( $amount ) && '0.00' != $amount ) {
			$meta   = $this->get_meta( '_edd_payment_meta', true );
			$meta   = maybe_unserialize( $meta );

			if ( isset( $meta['amount'] ) ) {
				$amount = $meta['amount'];
			}
		}

		return $amount;
	}

	private function setup_tax() {
		$tax = $this->get_meta( '_edd_payment_tax', true );

		// We don't have tax as it's own meta and no meta was passed
		if ( '' === $tax ) {

			$tax = isset( $payment_meta['tax'] ) ? $payment_meta['tax'] : 0;

		}

		return apply_filters( 'edd_get_payment_tax', $tax, $this->ID );
	}

	private function setup_subtotal() {
		$subtotal     = 0;
		$cart_details = $this->cart_details;

		if ( is_array( $cart_details ) ) {

			foreach ( $cart_details as $item ) {

				if ( isset( $item['subtotal'] ) ) {

					$subtotal += $item['subtotal'];

				}

			}

		} else {

			$subtotal  = $this->total;
			$tax       = edd_use_taxes() ? $this->tax : 0;
			$subtotal -= $tax;

		}

		return apply_filters( 'edd_get_payment_subtotal', $subtotal, $this->ID );
	}

	private function setup_currency( $payment_meta ) {
		$currency = isset( $payment_meta['currency'] ) ? $payment_meta['currency'] : edd_get_currency();
		return apply_filters( 'edd_payment_currency_code', $currency, $this->ID );
	}

	private function setup_fees( $payment_meta ) {
		$payment_fees = isset( $payment_meta['fees'] ) ? $payment_meta['fees'] : array();
		return $payment_fees;
	}

	private function setup_gateway() {
		$gateway = $this->get_meta( '_edd_payment_gateway', true );
		return apply_filters( 'edd_payment_gateway', $gateway );
	}

	private function setup_transaction_id() {
		$transaction_id = false;
		$transaction_id = $this->get_meta( '_edd_payment_transaction_id', true );

		if ( empty( $transaction_id ) ) {

			$gateway        = $this->gateway;
			$transaction_id = apply_filters( 'edd_get_payment_transaction_id-' . $gateway, $this->ID );

		}

		return apply_filters( 'edd_get_payment_transaction_id', $transaction_id, $this->ID );
	}

	private function setup_ip() {
		$ip = $this->get_meta( '_edd_payment_user_ip', true );
		return apply_filters( 'edd_payment_user_ip', $ip );
	}

	private function setup_customer_id() {
		$customer_id = $this->get_meta( '_edd_payment_customer_id', true );
		return apply_filters( 'edd_payment_customer_id', $customer_id );
	}

	private function setup_user_id() {
		$user_id = $this->get_meta( '_edd_payment_user_id', true );
		return apply_filters( 'edd_payment_user_id', $user_id );
	}

	private function setup_email() {
		$email = $this->get_meta( '_edd_payment_user_email', true );
		return apply_filters( 'edd_payment_user_email', $email );
	}

	private function setup_user_info( $payment_meta ) {
		$user_info    = isset( $payment_meta['user_info'] ) ? $payment_meta['user_info'] : false;
		return apply_filters( 'edd_payment_meta_user_info', $user_info );
	}

	private function setup_payment_key() {
		$key = $this->get_meta( '_edd_payment_purchase_key', true );
		return apply_filters( 'edd_payment_key', $key, $this->ID );
	}

	private function setup_payment_number() {
		$number = $this->ID;

		if ( edd_get_option( 'enable_sequential' ) ) {

			$number = $this->get_meta( '_edd_payment_number', true );

			if ( ! $number ) {

				$number = $this->ID;

			}

		}

		return apply_filters( 'edd_payment_number', $number, $this->ID );
	}

	private function setup_cart_details( $payment_meta ) {
		$cart_details = isset( $payment_meta['cart_details'] ) ? maybe_unserialize( $payment_meta['cart_details'] ) : array();
		return apply_filters( 'edd_payment_cart_details', $cart_details, $this->ID );
	}

	private function setup_downloads( $payment_meta ) {
		$downloads = isset( $payment_meta['downloads'] ) ? maybe_unserialize( $payment_meta['downloads'] ) : array();
		return apply_filters( 'edd_payment_meta_downloads', $downloads, $this->ID );
	}

	private function setup_has_unlimited() {
		$unlimited = (bool) $this->get_meta( '_edd_payment_unlimited_downloads', true );
		return apply_filters( 'edd_payment_unlimited_downloads', $unlimited );
	}

	public function array_convert() {
		return get_object_vars( $this );
	}

}
