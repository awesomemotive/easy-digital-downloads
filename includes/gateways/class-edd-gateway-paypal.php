<?php
/**
 * PayPal Payment Gateway.
 *
 * @package     EDD
 * @subpackage  Gateways
 * @copyright   Copyright (c) 2017, Sunny Ratilal
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       2.7
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class EDD_Gateway_PayPal extends EDD_Gateway {
	/**
	 * PayPal API Endpoint.
	 *
	 * @access private
	 * @since  2.7
	 * @var    string
	 */
	private $api_endpoint;

	/**
	 * Checkout URL.
	 *
	 * @access private
	 * @since  2.7
	 * @var    string
	 */
	private $checkout_url;

	/**
	 * PayPal API Username.
	 *
	 * @access protected
	 * @since  2.7
	 * @var    string
	 */
	protected $username;

	/**
	 * PayPal API Password.
	 *
	 * @access protected
	 * @since  2.7
	 * @var    string
	 */
	protected $password;

	/**
	 * PayPal API Signature.
	 *
	 * @access protected
	 * @since  2.7
	 * @var    string
	 */
	protected $signature;

	/**
	 * Constructor.
	 *
	 * @access public
	 * @since  2.7
	 *
	 * @return void
	 */
	public function __construct() {
		$this->ID = 'paypal';

		parent::__construct();
	}

	/**
	 * Initialise the gateway.
	 *
	 * @access public
	 * @since  2.7
	 *
	 * @return void
	 */
	public function init() {
		if ( $this->test_mode ) {
			$this->api_endpoint = 'https://api-3t.sandbox.paypal.com/nvp';
			$this->username     = edd_get_option( 'paypal_test_api_username' );
			$this->password     = edd_get_option( 'paypal_test_api_password' );
			$this->signature    = edd_get_option( 'paypal_test_api_signature' );
		} else {
			$this->api_endpoint = 'https://api-3t.paypal.com/nvp';
			$this->username     = edd_get_option( 'paypal_live_api_username' );
			$this->password     = edd_get_option( 'paypal_live_api_password' );
			$this->signature    = edd_get_option( 'paypal_live_api_signature' );
		}
	}

	/**
	 * Process the purchase.
	 *
	 * @access public
	 * @since  2.7
	 *
	 * @param array $purchase_data Purchase data from session.
	 * @return void
	 */
	public function process_purchase( $purchase_data = array() ) {
		if ( ! wp_verify_nonce( $purchase_data['gateway_nonce'], 'edd-gateway' ) ) {
			wp_die( __( 'Nonce verification has failed', 'easy-digital-downloads' ), __( 'Error', 'easy-digital-downloads' ), array( 'response' => 403 ) );
		}

		// Collect payment data
		$payment_data = array(
			'price'        => $purchase_data['price'],
			'date'         => $purchase_data['date'],
			'user_email'   => $purchase_data['user_email'],
			'purchase_key' => $purchase_data['purchase_key'],
			'currency'     => edd_get_currency(),
			'downloads'    => $purchase_data['downloads'],
			'user_info'    => $purchase_data['user_info'],
			'cart_details' => $purchase_data['cart_details'],
			'gateway'      => 'paypal',
			'status'       => ! empty( $purchase_data['buy_now'] ) ? 'private' : 'pending'
		);

		// Record the pending payment
		$payment = edd_insert_payment( $payment_data );

		// Check payment
		if ( ! $payment ) {
			edd_record_gateway_error( __( 'Payment Error', 'easy-digital-downloads' ), sprintf( __( 'Payment creation failed before sending buyer to PayPal. Payment data: %s', 'easy-digital-downloads' ), json_encode( $payment_data ) ), $payment );
			edd_send_back_to_checkout( '?payment-mode=' . $purchase_data['post_data']['edd-gateway'] );
		} else {
			$listener_url = add_query_arg( 'edd-listener', 'IPN', home_url( 'index.php' ) );
			$return_url = add_query_arg( array(
					'payment-confirmation' => 'paypal',
					'payment-id' => $payment
				), get_permalink( edd_get_option( 'success_page', false ) ) );
			$paypal_redirect = trailingslashit( edd_get_paypal_redirect() ) . '?';

			$paypal_args = array(
				'business'      => edd_get_option( 'paypal_email', false ),
				'email'         => $purchase_data['user_email'],
				'first_name'    => $purchase_data['user_info']['first_name'],
				'last_name'     => $purchase_data['user_info']['last_name'],
				'invoice'       => $purchase_data['purchase_key'],
				'no_shipping'   => '1',
				'shipping'      => '0',
				'no_note'       => '1',
				'currency_code' => edd_get_currency(),
				'charset'       => get_bloginfo( 'charset' ),
				'custom'        => $payment,
				'rm'            => '2',
				'return'        => $return_url,
				'cancel_return' => edd_get_failed_transaction_uri( '?payment-id=' . $payment ),
				'notify_url'    => $listener_url,
				'page_style'    => edd_get_paypal_page_style(),
				'cbt'           => get_bloginfo( 'name' ),
				'bn'            => 'EasyDigitalDownloads_SP'
			);

			if ( ! empty( $purchase_data['user_info']['address'] ) ) {
				$paypal_args['address1'] = $purchase_data['user_info']['address']['line1'];
				$paypal_args['address2'] = $purchase_data['user_info']['address']['line2'];
				$paypal_args['city']     = $purchase_data['user_info']['address']['city'];
				$paypal_args['country']  = $purchase_data['user_info']['address']['country'];
			}

			$paypal_extra_args = array(
				'cmd'    => '_cart',
				'upload' => '1'
			);

			$paypal_args = array_merge( $paypal_extra_args, $paypal_args );

			// Add cart items
			$i = 1;
			if ( is_array( $purchase_data['cart_details'] ) && ! empty( $purchase_data['cart_details'] ) ) {
				foreach ( $purchase_data['cart_details'] as $item ) {
					$item_amount = round( ( $item['subtotal'] / $item['quantity'] ) - ( $item['discount'] / $item['quantity'] ), 2 );

					if ( $item_amount <= 0 ) {
						$item_amount = 0;
					}

					$paypal_args['item_name_' . $i ] = stripslashes_deep( html_entity_decode( edd_get_cart_item_name( $item ), ENT_COMPAT, 'UTF-8' ) );
					$paypal_args['quantity_' . $i ]  = $item['quantity'];
					$paypal_args['amount_' . $i ]    = $item_amount;

					if ( edd_use_skus() ) {
						$paypal_args['item_number_' . $i ] = edd_get_download_sku( $item['id'] );
					}

					$i++;
				}
			}

			// Calculate discount
			$discounted_amount = 0.00;
			if ( ! empty( $purchase_data['fees'] ) ) {
				$i = empty( $i ) ? 1 : $i;
				foreach ( $purchase_data['fees'] as $fee ) {
					if ( floatval( $fee['amount'] ) > '0' ) {
						// this is a positive fee
						$paypal_args['item_name_' . $i ] = stripslashes_deep( html_entity_decode( wp_strip_all_tags( $fee['label'] ), ENT_COMPAT, 'UTF-8' ) );
						$paypal_args['quantity_'  . $i ] = '1';
						$paypal_args['amount_'    . $i ] = edd_sanitize_amount( $fee['amount'] );
						$i++;
					} else if ( empty( $fee['download_id'] ) ) {
						// This is a negative fee (discount) not assigned to a specific Download
						$discounted_amount += abs( $fee['amount'] );
					}
				}
			}

			if ( $discounted_amount > '0' ) {
				$paypal_args['discount_amount_cart'] = edd_sanitize_amount( $discounted_amount );
			}

			// Add taxes to the cart
			if ( edd_use_taxes() ) {
				$paypal_args['tax_cart'] = edd_sanitize_amount( $purchase_data['tax'] );
			}

			$paypal_args = apply_filters( 'edd_paypal_redirect_args', $paypal_args, $purchase_data );

			$paypal_redirect .= http_build_query( $paypal_args );
			$paypal_redirect = str_replace( '&amp;', '&', $paypal_redirect ); // Fix for some sites that encode the entities

			edd_empty_cart();
			wp_redirect( $paypal_redirect );
			exit;
		}
	}

	/**
	 * Process PayPal IPN.
	 *
	 * @access public
	 * @since  2.7
	 *
	 * @return void
	 */
	public function process_webhooks() {
		if ( isset( $_SERVER['REQUEST_METHOD'] ) && $_SERVER['REQUEST_METHOD'] != 'POST' ) {
			return;
		}

		$post_data = '';

		// Fallback just in case post_max_size is lower than needed
		if ( ini_get( 'allow_url_fopen' ) ) {
			$post_data = file_get_contents( 'php://input' );
		} else {
			// If allow_url_fopen is not enabled, then make sure that post_max_size is large enough
			ini_set( 'post_max_size', '12M' );
		}
		// Start the encoded data collection with notification command
		$encoded_data = 'cmd=_notify-validate';

		$arg_separator = edd_get_php_arg_separator_output();

		if ( $post_data || strlen( $post_data ) > 0 ) {
			$encoded_data .= $arg_separator . $post_data;
		} else {
			if ( empty( $_POST ) ) {
				return;
			} else {
				foreach ( $_POST as $key => $value ) {
					$encoded_data .= $arg_separator . "$key=" . urlencode( $value );
				}
			}
		}

		parse_str( $encoded_data, $encoded_data_array );

		foreach ( $encoded_data_array as $key => $value ) {
			if ( false !== strpos( $key, 'amp;' ) ) {
				$new_key = str_replace( '&amp;', '&', $key );
				$new_key = str_replace( 'amp;', '&', $new_key );

				unset( $encoded_data_array[ $key ] );
				$encoded_data_array[ $new_key ] = $value;
			}
		}

		$paypal_redirect = edd_get_paypal_redirect( true );

		if ( ! edd_get_option( 'disable_paypal_verification' ) ) {
			// Validate the IPN
			$remote_post_vars = array(
				'method'      => 'POST',
				'timeout'     => 45,
				'redirection' => 5,
				'httpversion' => '1.1',
				'blocking'    => true,
				'headers'     => array(
					'host'         => 'www.paypal.com',
					'connection'   => 'close',
					'content-type' => 'application/x-www-form-urlencoded',
					'post'         => '/cgi-bin/webscr HTTP/1.1',

				),
				'sslverify'   => false,
				'body'        => $encoded_data_array
			);

			$api_response = wp_remote_post( edd_get_paypal_redirect( true ), $remote_post_vars );

			if ( is_wp_error( $api_response ) ) {
				edd_record_gateway_error( __( 'IPN Error', 'easy-digital-downloads' ), sprintf( __( 'Invalid IPN verification response. IPN data: %s', 'easy-digital-downloads' ), json_encode( $api_response ) ) );
				return;
			}

			if ( wp_remote_retrieve_body( $api_response ) !== 'VERIFIED' && edd_get_option( 'disable_paypal_verification', false ) ) {
				edd_record_gateway_error( __( 'IPN Error', 'easy-digital-downloads' ), sprintf( __( 'Invalid IPN verification response. IPN data: %s', 'easy-digital-downloads' ), json_encode( $api_response ) ) );
				return;
			}
		}

		// Check if $post_data_array has been populated
		if ( ! is_array( $encoded_data_array ) && ! empty( $encoded_data_array ) ) {
			return;
		}

		$defaults = array(
			'txn_type'       => '',
			'payment_status' => ''
		);

		$encoded_data_array = wp_parse_args( $encoded_data_array, $defaults );

		$payment_id = 0;

		if ( ! empty( $encoded_data_array[ 'parent_txn_id' ] ) ) {
			$payment_id = edd_get_purchase_id_by_transaction_id( $encoded_data_array[ 'parent_txn_id' ] );
		} elseif ( ! empty( $encoded_data_array[ 'txn_id' ] ) ) {
			$payment_id = edd_get_purchase_id_by_transaction_id( $encoded_data_array[ 'txn_id' ] );
		}

		if ( empty( $payment_id ) ) {
			$payment_id = ! empty( $encoded_data_array[ 'custom' ] ) ? absint( $encoded_data_array[ 'custom' ] ) : 0;
		}

		if ( has_action( 'edd_paypal_' . $encoded_data_array['txn_type'] ) ) {
			// Allow PayPal IPN types to be processed separately
			do_action( 'edd_paypal_' . $encoded_data_array['txn_type'], $encoded_data_array, $payment_id );
		} else {
			// Fallback to web accept just in case the txn_type isn't present
			do_action( 'edd_paypal_web_accept', $encoded_data_array, $payment_id );
		}

		exit;
	}
}