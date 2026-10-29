<?php
/**
 * End-to-end create and capture for the PayPal post-capture login, with PayPal mocked.
 *
 * @package   EDD\Tests\Gateways\PayPal
 * @copyright Copyright (c) 2026, Sandhills Development, LLC
 * @license   https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since     3.7.3
 */

namespace EDD\Tests\Gateways\PayPal;

defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

use EDD\Checkout\AutoRegister;
use EDD\REST\Controllers\UnbrandedCard;
use EDD\REST\Security;
use EDD\Tests\PHPUnit\Ajax_UnitTestCase;

/**
 * End-to-end create-and-capture tests for the PayPal post-capture login.
 *
 * Drives the card and button checkout paths with PayPal mocked, then records
 * which user id each capture re-issues an auth cookie for.
 *
 * @group gateways
 * @group paypal
 */
class PurchaseUserCaptureFlowTest extends Ajax_UnitTestCase {

	/**
	 * PayPal order id the mocked PayPal API returns.
	 *
	 * @var string
	 */
	const PAYPAL_ORDER_ID = 'PAYPAL-ORDER-1';

	/**
	 * Mirrors PurchaseUser's private session key so the tests can read and reset it.
	 *
	 * @var string
	 */
	private const MARKER_KEY = 'checkout_authenticated_user';

	/**
	 * User ids passed to every observed set_auth_cookie call, in order.
	 *
	 * @var int[]
	 */
	private $cookies = array();

	/**
	 * Prior value of the logged_in_only option, restored in tear_down.
	 *
	 * @var mixed
	 */
	private $prior_logged_in_only;

	/**
	 * Wires the PayPal sandbox options and starts observing the grant hook.
	 */
	public function set_up(): void {
		parent::set_up();
		add_filter( 'edd_is_test_mode', '__return_true' );
		update_option( 'edd_paypal_sandbox_commerce_version', 'v3' );
		update_option( 'edd_paypal_sandbox_store_id', 'test-store-id' );
		update_option( 'edd_paypal_sandbox_hmac_key', str_repeat( 'a', 64 ) );
		update_option( 'edd_paypal_sandbox_merchant_id', 'MERCHANT_ID' );
		add_filter( 'pre_http_request', array( $this, 'mock_paypal' ), 10, 3 );
		add_action( 'set_auth_cookie', array( $this, 'record_cookie' ), 10, 6 );
		$this->cookies              = array();
		$this->prior_logged_in_only = edd_get_option( 'logged_in_only', false );
		wp_set_current_user( 0 );
		EDD()->session->set( self::MARKER_KEY, null );
	}

	/**
	 * Stops observing, clears checkout session state, and restores the option.
	 */
	public function tear_down(): void {
		remove_filter( 'pre_http_request', array( $this, 'mock_paypal' ), 10 );
		remove_action( 'set_auth_cookie', array( $this, 'record_cookie' ), 10 );
		remove_filter( 'edd_is_test_mode', '__return_true' );
		EDD()->session->set( self::MARKER_KEY, null );
		edd_set_purchase_session( null );
		EDD()->session->set( 'edd_resume_payment', null );
		wp_set_current_user( 0 );
		edd_clear_errors();

		// Hygiene: restore the logged_in_only option captured in set_up.
		if ( false === $this->prior_logged_in_only ) {
			edd_delete_option( 'logged_in_only' );
		} else {
			edd_update_option( 'logged_in_only', $this->prior_logged_in_only );
		}

		parent::tear_down();
	}

	/**
	 * Records the user id an auth cookie was set for.
	 *
	 * @param string $auth_cookie The cookie value.
	 * @param int    $expire      The expiration time.
	 * @param int    $expiration  The expiration time reference.
	 * @param int    $user_id     The user id the cookie was set for.
	 * @param string $scheme      The cookie scheme.
	 * @param string $token       The session token.
	 */
	public function record_cookie( $auth_cookie, $expire, $expiration, $user_id, $scheme, $token ) {
		$this->cookies[] = (int) $user_id;
	}

	/**
	 * Returns a COMPLETED PayPal capture response for the latest order.
	 *
	 * @param false|array|\WP_Error $preempt Short-circuit value for the request.
	 * @param array                 $args    The request arguments.
	 * @param string                $url     The request URL.
	 * @return array The mocked PayPal API response.
	 */
	public function mock_paypal( $preempt, $args, $url ) {
		$order = edd_get_orders( array( 'number' => 1, 'orderby' => 'id', 'order' => 'DESC' ) );
		return array(
			'response' => array( 'code' => 200 ),
			'body'     => wp_json_encode(
				array(
					'id'             => self::PAYPAL_ORDER_ID,
					'status'         => 'COMPLETED',
					'purchase_units' => array(
						array(
							'reference_id' => ! empty( $order ) ? $order[0]->payment_key : '',
							'payments'     => array( 'captures' => array( array( 'id' => 'CAPTURE-1', 'status' => 'COMPLETED' ) ) ),
						),
					),
				)
			),
		);
	}

	/**
	 * A card-fields guest buying with a registered email is not logged in.
	 */
	public function test_card_fields_guest_with_registered_email_is_not_logged_in() {
		$registered_user = $this->registered_user_without_customer();
		$order_id        = $this->card_create( array( 'edd_email' => 'registered@edd.test', 'edd_first' => 'A', 'edd_last' => 'B' ) );

		// Fixture: the connect chain connected the guest order to the registered user.
		$this->assertSame( $registered_user, (int) edd_get_order( $order_id )->user_id );
		$this->assertSame( 0, get_current_user_id() );

		$this->card_capture( $order_id );

		$this->assertSame( array(), $this->cookies, 'Guest checkout was logged in as: ' . implode( ',', $this->cookies ) );
	}

	/**
	 * A PayPal-button guest buying with a registered email is not logged in.
	 */
	public function test_paypal_button_guest_with_registered_email_is_not_logged_in() {
		$registered_user = $this->registered_user_without_customer();
		$download        = $this->create_ajax_download( 20.00 );
		edd_add_to_cart( $download );
		$purchase_data = array(
			'downloads'     => edd_get_cart_contents(),
			'price'         => 20.0,
			'subtotal'      => 20.0,
			'discount'      => 0,
			'tax'           => 0,
			'fees'          => array(),
			'user_email'    => 'registered@edd.test',
			'purchase_key'  => md5( 'capture-flow' ),
			'currency'      => 'USD',
			'gateway_nonce' => wp_create_nonce( 'edd-gateway' ),
			'user_info'     => array(
				'id'         => false,
				'email'      => 'registered@edd.test',
				'first_name' => 'A',
				'last_name'  => 'B',
				'discount'   => false,
				'address'    => false,
			),
			'cart_details'  => edd_get_cart_content_details(),
		);
		add_action(
			'wp_ajax_nopriv_edd_test_capture_flow_create',
			function () use ( $purchase_data ) {
				\EDD\Gateways\PayPal\create_order( $purchase_data );
			}
		);
		add_action(
			'wp_ajax_edd_test_capture_flow_create',
			function () use ( $purchase_data ) {
				\EDD\Gateways\PayPal\create_order( $purchase_data );
			}
		);
		$created = json_decode( $this->_handleAjax( 'edd_test_capture_flow_create' ), true );
		$this->assertTrue( $created['success'], wp_json_encode( $created ) );
		$order_id = (int) $created['data']['edd_order_id'];

		// Fixture: the connect chain connected the guest order to the registered user.
		$this->assertSame( $registered_user, (int) edd_get_order( $order_id )->user_id );

		$_POST['edd_process_paypal_nonce'] = wp_create_nonce( 'edd_process_paypal' );
		$_POST['paypal_order_id']          = self::PAYPAL_ORDER_ID;
		$this->_last_response              = '';
		$captured = json_decode( $this->_handleAjax( 'edd_capture_paypal_order' ), true );
		unset( $_POST['edd_process_paypal_nonce'], $_POST['paypal_order_id'] );
		$this->assertTrue( $captured['success'], wp_json_encode( $captured ) );
		$this->assertSame( 'complete', edd_get_order( $order_id )->status );

		$this->assertSame( array(), $this->cookies, 'Guest checkout was logged in as: ' . implode( ',', $this->cookies ) );
	}

	/**
	 * A registered PayPal-button buyer gets an auth cookie re-issued after capture.
	 */
	public function test_paypal_button_registered_buyer_gets_cookie_after_capture() {
		$buyer = self::factory()->user->create( array( 'user_email' => 'button-buyer@edd.test' ) );
		// edd_process_purchase_form() registered and logged this buyer in before create_order().
		wp_set_current_user( $buyer );
		$download = $this->create_ajax_download( 20.00 );
		edd_add_to_cart( $download );
		$purchase_data = array(
			'downloads'     => edd_get_cart_contents(),
			'price'         => 20.0,
			'subtotal'      => 20.0,
			'discount'      => 0,
			'tax'           => 0,
			'fees'          => array(),
			'user_email'    => 'button-buyer@edd.test',
			'purchase_key'  => md5( 'capture-flow-b' ),
			'currency'      => 'USD',
			'gateway_nonce' => wp_create_nonce( 'edd-gateway' ),
			'user_info'     => array(
				'id'         => $buyer,
				'email'      => 'button-buyer@edd.test',
				'first_name' => 'B',
				'last_name'  => 'Uyer',
				'discount'   => false,
				'address'    => false,
			),
			'cart_details'  => edd_get_cart_content_details(),
		);
		add_action(
			'wp_ajax_edd_test_capture_flow_create_b',
			function () use ( $purchase_data ) {
				\EDD\Gateways\PayPal\create_order( $purchase_data );
			}
		);
		$created = json_decode( $this->_handleAjax( 'edd_test_capture_flow_create_b' ), true );
		$this->assertTrue( $created['success'], wp_json_encode( $created ) );
		$order_id = (int) $created['data']['edd_order_id'];
		$this->assertSame( $buyer, (int) edd_get_order( $order_id )->user_id );

		// The capture request arrives without the auth cookie (proxy-stripped).
		wp_set_current_user( 0 );
		$this->cookies                     = array();
		$_POST['edd_process_paypal_nonce'] = wp_create_nonce( 'edd_process_paypal' );
		$_POST['paypal_order_id']          = self::PAYPAL_ORDER_ID;
		$this->_last_response              = '';
		$captured = json_decode( $this->_handleAjax( 'edd_capture_paypal_order' ), true );
		$this->assertTrue( $captured['success'], wp_json_encode( $captured ) );

		$this->assertSame( array( $buyer ), $this->cookies );
	}

	/**
	 * A card-fields new-account buyer gets an auth cookie re-issued after capture.
	 */
	public function test_card_fields_new_account_buyer_gets_cookie_after_capture() {
		$order_id = $this->card_create(
			array(
				'edd_email'        => 'new-buyer@edd.test',
				'edd_first'        => 'New',
				'edd_last'         => 'Buyer',
				'edd-purchase-var' => 'needs-to-register',
				'edd_user_login'   => 'newbuyer',
				'edd_user_pass'    => 'Sup3r-secret-pass!',
			)
		);
		$new_user = get_user_by( 'login', 'newbuyer' );
		$this->assertInstanceOf( \WP_User::class, $new_user );
		$this->assertSame( $new_user->ID, (int) edd_get_order( $order_id )->user_id );

		// The capture request arrives without the auth cookie (proxy-stripped).
		wp_set_current_user( 0 );
		$this->cookies = array();

		$this->card_capture( $order_id );

		$this->assertSame( array( $new_user->ID ), $this->cookies );
	}

	/**
	 * A card-fields Auto Register buyer gets an auth cookie re-issued after capture.
	 */
	public function test_card_fields_auto_register_buyer_gets_cookie_after_capture() {
		edd_update_option( 'logged_in_only', 'auto' );
		$this->wire_auto_register();

		// Auto Register signs the new account in only for a visitor with a checkout this session.
		edd_set_purchase_session( array( 'purchase_key' => md5( 'auto-register-capture' ) ) );

		// Fixtures the Auto Register handler branches on.
		$this->assertTrue( AutoRegister::is_enabled(), 'Fixture: Auto Register must be enabled.' );
		$this->assertSame( 0, get_current_user_id(), 'Fixture: the buyer must start as a guest.' );
		$this->assertFalse( get_user_by( 'email', 'auto-buyer@edd.test' ), 'Fixture: no account may exist for the email yet.' );

		$order_id = $this->card_create( array( 'edd_email' => 'auto-buyer@edd.test', 'edd_first' => 'Auto', 'edd_last' => 'Buyer' ) );

		$new_user = get_user_by( 'email', 'auto-buyer@edd.test' );
		$this->assertInstanceOf( \WP_User::class, $new_user, 'Auto Register must create the account.' );
		$this->assertSame( $new_user->ID, (int) edd_get_order( $order_id )->user_id, 'Auto Register must assign the account to the order.' );

		// The capture request arrives without the auth cookie (proxy-stripped).
		wp_set_current_user( 0 );
		$this->cookies = array();

		$this->card_capture( $order_id );

		$this->assertSame( array( $new_user->ID ), $this->cookies );
	}

	/**
	 * A card-fields returning buyer who logs in gets a cookie re-issued after capture.
	 */
	public function test_card_fields_login_buyer_gets_cookie_after_capture() {
		$user_id  = self::factory()->user->create( array( 'user_login' => 'loginbuyer', 'user_pass' => 'Sup3r-secret-pass!', 'user_email' => 'login-buyer@edd.test' ) );
		$order_id = $this->card_create(
			array(
				'edd_email'        => 'login-buyer@edd.test',
				'edd_first'        => 'Log',
				'edd_last'         => 'In',
				'edd-purchase-var' => 'needs-to-login',
				'edd_user_login'   => 'loginbuyer',
				'edd_user_pass'    => 'Sup3r-secret-pass!',
			)
		);
		$this->assertSame( $user_id, (int) edd_get_order( $order_id )->user_id );

		wp_set_current_user( 0 );
		$this->cookies = array();

		$this->card_capture( $order_id );

		$this->assertSame( array( $user_id ), $this->cookies );
	}

	/**
	 * An earlier login does not carry into a later guest capture.
	 */
	public function test_prior_login_does_not_carry_into_a_later_guest_capture() {
		$other_customer  = self::factory()->user->create( array( 'user_login' => 'other_customer', 'user_pass' => 'Sup3r-secret-pass!', 'user_email' => 'other-customer@edd.test' ) );
		$registered_user = $this->registered_user_without_customer();

		// Another customer logs in through card fields; that order is never captured.
		$this->card_create(
			array(
				'edd_email'        => 'other-customer@edd.test',
				'edd_first'        => 'Other',
				'edd_last'         => 'Customer',
				'edd-purchase-var' => 'needs-to-login',
				'edd_user_login'   => 'other_customer',
				'edd_user_pass'    => 'Sup3r-secret-pass!',
			)
		);
		$this->assertSame( $other_customer, (int) EDD()->session->get( self::MARKER_KEY ) );

		// Auth cookie dropped; then a guest checkout with the registered user's email.
		wp_set_current_user( 0 );
		edd_empty_cart();
		EDD()->session->set( 'edd_resume_payment', null );
		// Clear the request fields the earlier login left behind, so the second checkout runs as a clean guest.
		$leftover_keys = array( 'edd_email', 'edd_first', 'edd_last', 'edd-purchase-var', 'edd_user_login', 'edd_user_pass' );
		foreach ( $leftover_keys as $leftover_key ) {
			unset( $_POST[ $leftover_key ], $_REQUEST[ $leftover_key ] );
		}
		$order_id = $this->card_create( array( 'edd_email' => 'registered@edd.test', 'edd_first' => 'A', 'edd_last' => 'B' ) );
		$this->assertSame( $registered_user, (int) edd_get_order( $order_id )->user_id, 'Guest checkout must connect the order to the registered user by email.' );
		$this->assertSame( 0, (int) EDD()->session->get( self::MARKER_KEY ), 'Guest checkout must clear the earlier login marker.' );

		$this->cookies = array();
		$this->card_capture( $order_id );

		$this->assertSame( array(), $this->cookies );
	}

	/**
	 * Creates a registered user that owns the email and has no customer record.
	 *
	 * @return int The new user id.
	 */
	private function registered_user_without_customer() {
		$registered_user = self::factory()->user->create( array( 'role' => 'administrator', 'user_email' => 'registered@edd.test' ) );
		// Fixture: the account exists, owns the email, and has no customer record.
		$this->assertSame( $registered_user, get_user_by( 'email', 'registered@edd.test' )->ID );
		$this->assertFalse( edd_get_customer_by( 'email', 'registered@edd.test' ) );
		$this->assertFalse( edd_get_customer_by( 'user_id', $registered_user ) );
		return $registered_user;
	}

	/**
	 * Builds a JSON REST request for the unbranded card controller.
	 *
	 * @param array $body The request body.
	 * @return \WP_REST_Request The prepared request.
	 */
	private function rest( $body ) {
		$request = new \WP_REST_Request( 'POST' );
		$request->set_header( 'Content-Type', 'application/json' );
		$request->set_body( wp_json_encode( $body ) );
		return $request;
	}

	/**
	 * Runs a card-fields create request and returns the new order id.
	 *
	 * @param array $form The checkout form data.
	 * @return int The created order id.
	 */
	private function card_create( $form ) {
		edd_add_to_cart( $this->create_ajax_download( 20.00 ) );
		$response = ( new UnbrandedCard( new Security() ) )->create( $this->rest( array( 'form_data' => $form ) ) );
		$this->assertNotWPError( $response, is_wp_error( $response ) ? $response->get_error_message() : '' );
		return (int) $response->get_data()['edd_order_id'];
	}

	/**
	 * Runs a card-fields capture request for the given order.
	 *
	 * @param int $order_id The order id to capture.
	 */
	private function card_capture( $order_id ) {
		$response = ( new UnbrandedCard( new Security() ) )->capture(
			$this->rest( array( 'paypal_order_id' => self::PAYPAL_ORDER_ID, 'edd_order_id' => $order_id ) )
		);
		$this->assertNotWPError( $response, is_wp_error( $response ) ? $response->get_error_message() : '' );
		$this->assertSame( 'complete', edd_get_order( $order_id )->status );
	}

	/**
	 * Subscribes the Auto Register handler to order building.
	 */
	private function wire_auto_register() {
		$events = AutoRegister::get_subscribed_events();
		$this->assertArrayHasKey( 'edd_built_order', $events, 'Fixture: Auto Register must subscribe to order building.' );

		$subscription = (array) $events['edd_built_order'];
		$method       = $subscription[0];
		$priority     = $subscription[1] ?? 10;
		$accepted     = $subscription[2] ?? 1;

		$subscriber = new AutoRegister();
		$this->assertTrue( method_exists( $subscriber, $method ), 'Fixture: the Auto Register handler must exist.' );

		add_action( 'edd_built_order', array( $subscriber, $method ), $priority, $accepted );
	}
}
