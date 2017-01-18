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

/**
 * EDD_Gateway_PayPal Class
 *
 * @since   2.7
 * @version 1.0
 */
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
	 * @param array $purchase_data {
	 *    Purchase Data.
	 *
	 *    @type array  downloads    Array of download IDs.
	 *    @type float  price        Total price of cart contents.
	 *    @type string purchase_key Randomly generated purchase key.
	 *    @type string user_email   User's email address.
	 *    @type string date         Date.
	 *    @type string currency     Store currency.
	 *    @type int    user_info    User info.
	 *    @type string gateway      Gateway selected at checkout.
	 *    @type array  cart_details Array of cart details.
	 *    @type string status       Payment status.
	 *    @type bool   buy_now      Buy Now enabled.
	 * }
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

	/**
	 * Checkout form. The form returns false as it is disabled for this gateway.
	 *
	 * @access public
	 * @since  2.7
	 *
	 * @return false
	 */
	public function cc_form() {
		return false;
	}

	/**
	 * Gateway Settings.
	 *
	 * @access public
	 * @since  2.7
	 *
	 * @param array $gateway_settings Gateway settings.
	 * @return array $gateway_settings Updated gateway settings.
	 */
	public function register_settings() {
		$paypal_settings = array (
			'paypal_settings' => array(
				'id'   => 'paypal_settings',
				'name' => '<strong>' . __( 'PayPal Standard Settings', 'easy-digital-downloads' ) . '</strong>',
				'type' => 'header',
			),
			'paypal_email' => array(
				'id'   => 'paypal_email',
				'name' => __( 'PayPal Email', 'easy-digital-downloads' ),
				'desc' => __( 'Enter your PayPal account\'s email', 'easy-digital-downloads' ),
				'type' => 'text',
				'size' => 'regular',
			),
			'paypal_page_style' => array(
				'id'   => 'paypal_page_style',
				'name' => __( 'PayPal Page Style', 'easy-digital-downloads' ),
				'desc' => __( 'Enter the name of the page style to use, or leave blank for default', 'easy-digital-downloads' ),
				'type' => 'text',
				'size' => 'regular',
			),
		);

		$disable_ipn_desc = sprintf(
			__( 'If payments are not getting marked as complete, then check this box. This forces the site to use a slightly less secure method of verifying purchases. See our <a href="%s" target="_blank">FAQ</a> for further information.', 'easy-digital-downloads' ),
			'http://docs.easydigitaldownloads.com/article/190-payments-not-marked-as-complete'
		);

		$paypal_settings['disable_paypal_verification'] = array(
			'id'   => 'disable_paypal_verification',
			'name' => __( 'Disable PayPal IPN Verification', 'easy-digital-downloads' ),
			'desc' => $disable_ipn_desc,
			'type' => 'checkbox',
		);

		$api_key_settings = array(
			'paypal_api_keys_desc' => array(
				'id'   => 'paypal_api_keys_desc',
				'name' => __( 'API Credentials', 'easy-digital-downloads' ),
				'type' => 'descriptive_text',
				'desc' => sprintf(
					__( 'API credentials are necessary to process PayPal refunds from inside WordPress. These can be obtained from <a href="%s" target="_blank">your PayPal account</a>.', 'easy-digital-downloads' ),
					'https://developer.paypal.com/docs/classic/api/apiCredentials/#creating-an-api-signature'
				)
			),
			'paypal_live_api_username' => array(
				'id'   => 'paypal_live_api_username',
				'name' => __( 'Live API Username', 'easy-digital-downloads' ),
				'desc' => __( 'Your PayPal live API username. ', 'easy-digital-downloads' ),
				'type' => 'text',
				'size' => 'regular'
			),
			'paypal_live_api_password' => array(
				'id'   => 'paypal_live_api_password',
				'name' => __( 'Live API Password', 'easy-digital-downloads' ),
				'desc' => __( 'Your PayPal live API password.', 'easy-digital-downloads' ),
				'type' => 'text',
				'size' => 'regular'
			),
			'paypal_live_api_signature' => array(
				'id'   => 'paypal_live_api_signature',
				'name' => __( 'Live API Signature', 'easy-digital-downloads' ),
				'desc' => __( 'Your PayPal live API signature.', 'easy-digital-downloads' ),
				'type' => 'text',
				'size' => 'regular'
			),
			'paypal_test_api_username' => array(
				'id'   => 'paypal_test_api_username',
				'name' => __( 'Test API Username', 'easy-digital-downloads' ),
				'desc' => __( 'Your PayPal test API username.', 'easy-digital-downloads' ),
				'type' => 'text',
				'size' => 'regular'
			),
			'paypal_test_api_password' => array(
				'id'   => 'paypal_test_api_password',
				'name' => __( 'Test API Password', 'easy-digital-downloads' ),
				'desc' => __( 'Your PayPal test API password.', 'easy-digital-downloads' ),
				'type' => 'text',
				'size' => 'regular'
			),
			'paypal_test_api_signature' => array(
				'id'   => 'paypal_test_api_signature',
				'name' => __( 'Test API Signature', 'easy-digital-downloads' ),
				'desc' => __( 'Your PayPal test API signature.', 'easy-digital-downloads' ),
				'type' => 'text',
				'size' => 'regular'
			)
		);

		$paypal_settings = array_merge( $paypal_settings, $api_key_settings );

		$paypal_settings = apply_filters( 'edd_paypal_settings', $paypal_settings );
		$gateway_settings['paypal'] = $paypal_settings;

		return $gateway_settings;
	}

	/**
	 * Process Web Accept and Cart.
	 *
	 * @access public
	 * @since  2.7
	 *
	 * @param array $data       IPN Data.
	 * @param int   $payment_id Payment ID.
	 * @return void
	 */
	public function process_web_accept_and_cart( $data, $payment_id ) {
		if ( $data['txn_type'] != 'web_accept' && $data['txn_type'] != 'cart' && $data['payment_status'] != 'Refunded' ) {
			return;
		}

		if ( empty( $payment_id ) ) {
			return;
		}

		$payment = new EDD_Payment( $payment_id );

		// Collect payment details
		$purchase_key   = isset( $data['invoice'] ) ? $data['invoice'] : $data['item_number'];
		$paypal_amount  = $data['mc_gross'];
		$payment_status = strtolower( $data['payment_status'] );
		$currency_code  = strtolower( $data['mc_currency'] );
		$business_email = isset( $data['business'] ) && is_email( $data['business'] ) ? trim( $data['business'] ) : trim( $data['receiver_email'] );


		if ( $payment->gateway != 'paypal' ) {
			return; // this isn't a PayPal standard IPN
		}

		// Verify payment recipient
		if ( strcasecmp( $business_email, trim( edd_get_option( 'paypal_email', false ) ) ) != 0 ) {
			edd_record_gateway_error( __( 'IPN Error', 'easy-digital-downloads' ), sprintf( __( 'Invalid business email in IPN response. IPN data: %s', 'easy-digital-downloads' ), json_encode( $data ) ), $payment_id );
			edd_update_payment_status( $payment_id, 'failed' );
			edd_insert_payment_note( $payment_id, __( 'Payment failed due to invalid PayPal business email.', 'easy-digital-downloads' ) );
			return;
		}

		// Verify payment currency
		if ( $currency_code != strtolower( $payment->currency ) ) {
			edd_record_gateway_error( __( 'IPN Error', 'easy-digital-downloads' ), sprintf( __( 'Invalid currency in IPN response. IPN data: %s', 'easy-digital-downloads' ), json_encode( $data ) ), $payment_id );
			edd_update_payment_status( $payment_id, 'failed' );
			edd_insert_payment_note( $payment_id, __( 'Payment failed due to invalid currency in PayPal IPN.', 'easy-digital-downloads' ) );
			return;
		}

		if ( empty( $payment->email ) ) {
			// This runs when a Buy Now purchase was made. It bypasses checkout so no personal info is collected until PayPal

			// Setup and store the customers's details
			$address = array();
			$address['line1']    = ! empty( $data['address_street']       ) ? sanitize_text_field( $data['address_street'] )       : false;
			$address['city']     = ! empty( $data['address_city']         ) ? sanitize_text_field( $data['address_city'] )         : false;
			$address['state']    = ! empty( $data['address_state']        ) ? sanitize_text_field( $data['address_state'] )        : false;
			$address['country']  = ! empty( $data['address_country_code'] ) ? sanitize_text_field( $data['address_country_code'] ) : false;
			$address['zip']      = ! empty( $data['address_zip']          ) ? sanitize_text_field( $data['address_zip'] )          : false;

			$payment->email      = sanitize_text_field( $data['payer_email'] );
			$payment->first_name = sanitize_text_field( $data['first_name'] );
			$payment->last_name  = sanitize_text_field( $data['last_name'] );
			$payment->address    = $address;

			if ( empty( $payment->customer_id ) ) {
				$customer = new EDD_Customer( $payment->email );
				if ( ! $customer || $customer->id < 1 ) {
					$customer->create( array(
						'email'   => $payment->email,
						'name'    => $payment->first_name . ' ' . $payment->last_name,
						'user_id' => $payment->user_id
					) );
				}

				$payment->customer_id = $customer->id;
			}

			$payment->save();
		}

		if ( $payment_status == 'refunded' || $payment_status == 'reversed' ) {
			// Process a refund
			edd_process_paypal_refund( $data, $payment_id );
		} else {
			if ( get_post_status( $payment_id ) == 'publish' ) {
				return; // Only complete payments once
			}

			// Retrieve the total purchase amount (before PayPal)
			$payment_amount = edd_get_payment_amount( $payment_id );

			if ( number_format( (float) $paypal_amount, 2 ) < number_format( (float) $payment_amount, 2 ) ) {
				// The prices don't match
				edd_record_gateway_error( __( 'IPN Error', 'easy-digital-downloads' ), sprintf( __( 'Invalid payment amount in IPN response. IPN data: %s', 'easy-digital-downloads' ), json_encode( $data ) ), $payment_id );
				edd_update_payment_status( $payment_id, 'failed' );
				edd_insert_payment_note( $payment_id, __( 'Payment failed due to invalid amount in PayPal IPN.', 'easy-digital-downloads' ) );
				return;
			}
			if ( $purchase_key != edd_get_payment_key( $payment_id ) ) {
				// Purchase keys don't match
				edd_record_gateway_error( __( 'IPN Error', 'easy-digital-downloads' ), sprintf( __( 'Invalid purchase key in IPN response. IPN data: %s', 'easy-digital-downloads' ), json_encode( $data ) ), $payment_id );
				edd_update_payment_status( $payment_id, 'failed' );
				edd_insert_payment_note( $payment_id, __( 'Payment failed due to invalid purchase key in PayPal IPN.', 'easy-digital-downloads' ) );
				return;
			}

			if ( 'completed' == $payment_status || edd_is_test_mode() ) {
				edd_insert_payment_note( $payment_id, sprintf( __( 'PayPal Transaction ID: %s', 'easy-digital-downloads' ) , $data['txn_id'] ) );
				edd_set_payment_transaction_id( $payment_id, $data['txn_id'] );
				edd_update_payment_status( $payment_id, 'publish' );
			} else if ( 'pending' == $payment_status && isset( $data['pending_reason'] ) ) {
				// Look for possible pending reasons, such as an echeck
				$note = '';

				switch ( strtolower( $data['pending_reason'] ) ) {
					case 'echeck' :
						$note = __( 'Payment made via eCheck and will clear automatically in 5-8 days', 'easy-digital-downloads' );
						break;
					case 'address' :
						$note = __( 'Payment requires a confirmed customer address and must be accepted manually through PayPal', 'easy-digital-downloads' );
						break;
					case 'intl' :
						$note = __( 'Payment must be accepted manually through PayPal due to international account regulations', 'easy-digital-downloads' );
						break;
					case 'multi-currency' :
						$note = __( 'Payment received in non-shop currency and must be accepted manually through PayPal', 'easy-digital-downloads' );
						break;
					case 'paymentreview' :
					case 'regulatory_review' :
						$note = __( 'Payment is being reviewed by PayPal staff as high-risk or in possible violation of government regulations', 'easy-digital-downloads' );
						break;
					case 'unilateral' :
						$note = __( 'Payment was sent to non-confirmed or non-registered email address.', 'easy-digital-downloads' );
						break;
					case 'upgrade' :
						$note = __( 'PayPal account must be upgraded before this payment can be accepted', 'easy-digital-downloads' );
						break;
					case 'verify' :
						$note = __( 'PayPal account is not verified. Verify account in order to accept this payment', 'easy-digital-downloads' );
						break;
					case 'other' :
						$note = __( 'Payment is pending for unknown reasons. Contact PayPal support for assistance', 'easy-digital-downloads' );
						break;
				}

				if ( ! empty( $note ) ) {
					edd_insert_payment_note( $payment_id, $note );
				}
			}
		}
	}

	/**
	 * Process Refund.
	 *
	 * @access public
	 * @since  2.7
	 *
	 * @param array $data       IPN Data.
	 * @param int   $payment_id Payment ID.
	 * @return void
	 */
	public function process_refund( $data, $payment_id ) {
		if ( empty( $payment_id ) ) {
			return;
		}

		if ( 'refunded' == get_post_status( $payment_id ) ) {
			return; // Only refund payments once
		}

		$payment_amount = edd_get_payment_amount( $payment_id );
		$refund_amount  = $data['mc_gross'] * -1;

		if ( number_format( (float) $refund_amount, 2 ) < number_format( (float) $payment_amount, 2 ) ) {
			edd_insert_payment_note( $payment_id, sprintf( __( 'Partial PayPal refund processed: %s', 'easy-digital-downloads' ), $data['parent_txn_id'] ) );
			return; // This is a partial refund
		}

		edd_insert_payment_note( $payment_id, sprintf( __( 'PayPal Payment #%s Refunded for reason: %s', 'easy-digital-downloads' ), $data['parent_txn_id'], $data['reason_code'] ) );
		edd_insert_payment_note( $payment_id, sprintf( __( 'PayPal Refund Transaction ID: %s', 'easy-digital-downloads' ), $data['txn_id'] ) );
		edd_update_payment_status( $payment_id, 'refunded' );
	}

	/**
	 * Get PayPal Redirect.
	 *
	 * @access public
	 * @since  2.7
	 *
	 * @param bool $ssl_check Is SSL? (Default: false)
	 * @return string PayPal Redirect URI.
	 */
	public function get_redirect( $ssl_check = false ) {
		$protocol = 'http://';

		if ( is_ssl() || ! $ssl_check ) {
			$protocol = 'https://';
		}

		// Check the current payment mode
		if ( $this->test_mode ) {
			// Test mode
			$paypal_uri = $protocol . 'www.sandbox.paypal.com/cgi-bin/webscr';
		} else {
			// Live mode
			$paypal_uri = $protocol . 'www.paypal.com/cgi-bin/webscr';
		}

		return apply_filters( 'edd_paypal_uri', $paypal_uri );
	}
}