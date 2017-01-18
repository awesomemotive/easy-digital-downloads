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
}