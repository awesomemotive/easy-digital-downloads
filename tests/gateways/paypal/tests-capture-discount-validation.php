<?php
/**
 * PayPal capture discount validation tests.
 *
 * @package     EDD\Tests\Gateways\PayPal
 * @copyright   Copyright (c) 2026, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.7.2
 */

namespace EDD\Tests\Gateways\PayPal;

defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

use EDD\REST\Controllers\Fastlane;
use EDD\REST\Controllers\UnbrandedCard;
use EDD\REST\Security;
use EDD\Tests\Discounts\DiscountFixtures;
use EDD\Tests\PHPUnit\Ajax_UnitTestCase;

/**
 * Tests the discount checks around PayPal order creation and capture.
 *
 * @group gateways
 * @group paypal
 */
class CaptureDiscountValidationTest extends Ajax_UnitTestCase {

	use DiscountFixtures;

	/**
	 * The PayPal order ID stamped on the pending order.
	 *
	 * @var string
	 */
	const PAYPAL_ORDER_ID = 'PAYPAL-ORDER-1';

	/**
	 * The session key that names the authenticated checkout user.
	 *
	 * @var string
	 */
	private const MARKER_KEY = 'checkout_authenticated_user';

	/**
	 * URLs of the requests sent to PayPal during the test.
	 *
	 * @var string[]
	 */
	private $paypal_requests = array();

	/**
	 * The posted fields the test set, with their values before it set them.
	 *
	 * @var array
	 */
	private $original_post = array();

	/**
	 * The user IDs an auth cookie was issued for during the test.
	 *
	 * @var int[]
	 */
	private $cookies = array();

	/**
	 * Connects PayPal v3 in sandbox and records outgoing requests.
	 *
	 * @return void
	 */
	public function set_up(): void {
		parent::set_up();
		$this->forget_cached_claims();

		add_filter( 'edd_is_test_mode', '__return_true' );
		update_option( 'edd_paypal_sandbox_commerce_version', 'v3' );
		update_option( 'edd_paypal_sandbox_store_id', 'test-store-id' );
		update_option( 'edd_paypal_sandbox_hmac_key', str_repeat( 'a', 64 ) );
		update_option( 'edd_paypal_sandbox_merchant_id', 'MERCHANT_ID' );

		$this->paypal_requests = array();
		add_filter( 'pre_http_request', array( $this, 'record_paypal_request' ), 10, 3 );

		$this->cookies = array();
		add_action( 'set_auth_cookie', array( $this, 'record_cookie' ), 10, 6 );
	}

	/**
	 * Restores the PayPal connection and request filters.
	 *
	 * @return void
	 */
	public function tear_down(): void {
		remove_action( 'set_auth_cookie', array( $this, 'record_cookie' ), 10 );
		remove_filter( 'pre_http_request', array( $this, 'record_paypal_request' ), 10 );
		remove_filter( 'edd_is_test_mode', '__return_true' );
		delete_option( 'edd_paypal_sandbox_commerce_version' );
		delete_option( 'edd_paypal_sandbox_store_id' );
		delete_option( 'edd_paypal_sandbox_hmac_key' );
		delete_option( 'edd_paypal_sandbox_merchant_id' );

		foreach ( $this->original_post as $key => $value ) {
			if ( is_null( $value ) ) {
				unset( $_POST[ $key ] );
			} else {
				$_POST[ $key ] = $value;
			}
		}
		$this->original_post = array();
		edd_clear_errors();

		parent::tear_down();
	}

	/**
	 * Records the user an auth cookie was issued for.
	 *
	 * @param string $auth_cookie The authentication cookie.
	 * @param int    $expire      When the cookie expires.
	 * @param int    $expiration  When the auth cookie expires.
	 * @param int    $user_id     The user the cookie was issued for.
	 * @param string $scheme      The auth scheme.
	 * @param string $token       The session token.
	 * @return void
	 */
	public function record_cookie( $auth_cookie, $expire, $expiration, $user_id, $scheme, $token ) {
		$this->cookies[] = (int) $user_id;
	}

	/**
	 * A used-up discount stops the capture before PayPal is asked to take payment.
	 */
	public function test_capture_is_not_requested_when_the_discount_is_used_up() {
		$order = $this->pending_paypal_order_with( $this->discount_at_its_cap() );

		$this->post(
			array(
				'edd_process_paypal_nonce' => wp_create_nonce( 'edd_process_paypal' ),
				'paypal_order_id'          => self::PAYPAL_ORDER_ID,
			)
		);

		$response = json_decode( $this->_handleAjax( 'edd_capture_paypal_order' ), true );

		$this->assertFalse( $response['success'] );
		$this->assertSame( array(), $this->capture_requests() );
		$this->assertSame( 'failed', edd_get_order( $order->id )->status );
		$this->assertTrue( $response['data']['refresh'] );
	}

	/**
	 * Refusing the capture leaves the discount's use count and cap in place.
	 */
	public function test_refused_capture_keeps_the_discount_used_up() {
		$discount = $this->discount_at_its_cap();
		$this->pending_paypal_order_with( $discount );

		$this->post(
			array(
				'edd_process_paypal_nonce' => wp_create_nonce( 'edd_process_paypal' ),
				'paypal_order_id'          => self::PAYPAL_ORDER_ID,
			)
		);

		$this->_handleAjax( 'edd_capture_paypal_order' );

		$discount = edd_get_discount( $discount->id );
		$this->assertSame( 1, (int) $discount->use_count );
		$this->assertTrue( $discount->is_maxed_out( false ) );
	}

	/**
	 * A repeat capture request for a completed order leaves it complete.
	 */
	public function test_repeat_capture_leaves_a_completed_order_complete() {
		$discount = $this->create_discount( array( 'max_uses' => 1 ) );
		$order    = $this->pending_paypal_order_with( $discount );
		edd_update_order_status( $order->id, 'complete' );

		$this->assertSame( 'complete', edd_get_order( $order->id )->status );
		$this->assertSame( 1, (int) edd_get_discount( $discount->id )->use_count );

		$this->post(
			array(
				'edd_process_paypal_nonce' => wp_create_nonce( 'edd_process_paypal' ),
				'paypal_order_id'          => self::PAYPAL_ORDER_ID,
			)
		);

		$this->_handleAjax( 'edd_capture_paypal_order' );

		$this->assertSame( 'complete', edd_get_order( $order->id )->status );
		$this->assertSame( 1, (int) edd_get_discount( $discount->id )->use_count );
	}

	/**
	 * An available discount lets the capture proceed.
	 */
	public function test_capture_is_requested_when_the_discount_is_available() {
		$this->pending_paypal_order_with( $this->create_discount() );

		$this->post(
			array(
				'edd_process_paypal_nonce' => wp_create_nonce( 'edd_process_paypal' ),
				'paypal_order_id'          => self::PAYPAL_ORDER_ID,
			)
		);

		$this->_handleAjax( 'edd_capture_paypal_order' );

		$this->assertCount( 1, $this->capture_requests() );
	}

	/**
	 * The card fields capture refuses an order whose discount was set inactive.
	 */
	public function test_unbranded_card_capture_is_not_requested_when_the_discount_is_inactive() {
		$discount = $this->create_discount( array( 'max_uses' => 5 ) );
		$order    = $this->pending_paypal_order_with( $discount );
		edd_update_discount( $discount->id, array( 'status' => 'inactive' ) );

		$result = ( new UnbrandedCard( new Security() ) )->capture(
			$this->rest_request(
				array(
					'paypal_order_id' => self::PAYPAL_ORDER_ID,
					'edd_order_id'    => $order->id,
				)
			)
		);

		$this->assertWPError( $result );
		$this->assertSame( 'invalid_discount', $result->get_error_code() );
		$this->assertSame( array(), $this->paypal_requests );
		$this->assertSame( 'failed', edd_get_order( $order->id )->status );
		$this->assertSame( 'inactive', edd_get_discount( $discount->id )->status );
	}

	/**
	 * Fastlane refuses a used-up cart discount before the order is built or sent to PayPal.
	 */
	public function test_fastlane_order_is_refused_when_the_cart_discount_is_used_up() {
		$this->checkout_with_cart_discount( $this->discount_at_its_cap() );

		$result = ( new Fastlane( new Security() ) )->process_payment( $this->rest_request( array( 'payment_token' => 'TOKEN' ) ) );

		$this->assertWPError( $result );
		$this->assertSame( 'invalid_discount', $result->get_error_code() );
		$this->assertSame( array(), $this->paypal_requests );
		$this->assertSame( array(), edd_get_orders( array( 'email' => 'buyer@edd.test' ) ) );
	}

	/**
	 * Fastlane stores the cart discount on the order and counts the use.
	 */
	public function test_fastlane_order_records_the_cart_discount() {
		$discount = $this->create_discount( array( 'max_uses' => 1 ) );
		$this->checkout_with_cart_discount( $discount );

		( new Fastlane( new Security() ) )->process_payment( $this->rest_request( array( 'payment_token' => 'TOKEN' ) ) );

		$orders = edd_get_orders( array( 'email' => 'buyer@edd.test' ) );
		$this->assertCount( 1, $orders );
		$this->assertSame( 'complete', $orders[0]->status );
		$this->assertSame( array( $discount->code ), wp_list_pluck( $orders[0]->get_discounts(), 'description' ) );
		$this->assertSame( 1, (int) edd_get_discount( $discount->id )->use_count );
	}

	/**
	 * A Fastlane guest checkout clears a stale login marker, so no auth cookie is issued.
	 */
	public function test_fastlane_guest_checkout_with_registered_email_issues_no_cookie() {
		// Pre-create the WP account that owns the checkout email, with no customer tied to another user.
		$buyer_id = self::factory()->user->create( array( 'role' => 'administrator', 'user_email' => 'buyer@edd.test' ) );
		$this->assertSame( $buyer_id, get_user_by( 'email', 'buyer@edd.test' )->ID );
		$this->assertFalse( edd_get_customer_by( 'email', 'buyer@edd.test' ) );
		$this->assertFalse( edd_get_customer_by( 'user_id', $buyer_id ) );

		// A stale login marker names the registered buyer.
		EDD()->session->set( self::MARKER_KEY, $buyer_id );

		// The guest checks out with the registered user's email.
		wp_set_current_user( 0 );
		$this->checkout_with_cart_discount( $this->create_discount( array( 'max_uses' => 1 ) ) );

		( new Fastlane( new Security() ) )->process_payment( $this->rest_request( array( 'payment_token' => 'TOKEN' ) ) );

		$orders = edd_get_orders( array( 'email' => 'buyer@edd.test' ) );
		$this->assertCount( 1, $orders );
		$order = $orders[0];

		// The order resolves to the seeded buyer, so the owner-guard is in pass-through and cannot mask a regression.
		$this->assertSame( $buyer_id, (int) $order->user_id, 'Order must resolve to the seeded buyer so the owner-guard is exercised.' );

		// Fastlane cleared the stale marker as a guest, so the capture read an empty marker and issued no cookie.
		$this->assertSame( array(), $this->cookies );
	}

	/**
	 * The card fields create step refuses a used-up cart discount before the order is built.
	 */
	public function test_card_fields_order_is_refused_when_the_cart_discount_is_used_up() {
		$this->checkout_with_cart_discount( $this->discount_at_its_cap() );

		$result = ( new UnbrandedCard( new Security() ) )->create( $this->rest_request( array() ) );

		$this->assertWPError( $result );
		$this->assertSame( 'invalid_discount', $result->get_error_code() );
		$this->assertSame( array(), $this->paypal_requests );
		$this->assertSame( array(), edd_get_orders( array( 'email' => 'buyer@edd.test' ) ) );
	}

	/**
	 * The card fields create step stores the cart discount on the pending order.
	 */
	public function test_card_fields_order_records_the_cart_discount() {
		$discount = $this->create_discount();
		$this->checkout_with_cart_discount( $discount );

		( new UnbrandedCard( new Security() ) )->create( $this->rest_request( array() ) );

		$orders = edd_get_orders( array( 'email' => 'buyer@edd.test' ) );
		$this->assertCount( 1, $orders );
		$this->assertSame( array( $discount->code ), wp_list_pluck( $orders[0]->get_discounts(), 'description' ) );
	}

	/**
	 * A capture request for a canceled order is refused before PayPal is asked to take payment.
	 */
	public function test_capture_is_not_requested_for_a_cancelled_order() {
		$order = $this->pending_paypal_order_with( $this->create_discount() );
		edd_update_order_status( $order->id, 'cancelled' );
		$this->assertSame( 'cancelled', edd_get_order( $order->id )->status );

		$this->post(
			array(
				'edd_process_paypal_nonce' => wp_create_nonce( 'edd_process_paypal' ),
				'paypal_order_id'          => self::PAYPAL_ORDER_ID,
			)
		);

		$response = json_decode( $this->_handleAjax( 'edd_capture_paypal_order' ), true );

		$this->assertFalse( $response['success'] );
		$this->assertSame( array(), $this->capture_requests() );
		$this->assertSame( 'cancelled', edd_get_order( $order->id )->status );
	}

	/**
	 * A capture request for a PayPal order ID no order holds is refused before PayPal is asked to take payment.
	 */
	public function test_capture_is_not_requested_for_a_paypal_order_no_order_holds() {
		$order = $this->pending_paypal_order_with( $this->create_discount() );
		edd_update_order_meta( $order->id, 'paypal_order_id', 'PAYPAL-ORDER-2' );

		$this->post(
			array(
				'edd_process_paypal_nonce' => wp_create_nonce( 'edd_process_paypal' ),
				'paypal_order_id'          => self::PAYPAL_ORDER_ID,
			)
		);

		$response = json_decode( $this->_handleAjax( 'edd_capture_paypal_order' ), true );

		$this->assertFalse( $response['success'] );
		$this->assertSame( array(), $this->capture_requests() );
		$this->assertSame( 'pending', edd_get_order( $order->id )->status );
	}

	/**
	 * A PayPal order refused at creation shows its message inline and leaves no error to block the next attempt.
	 */
	public function test_order_refused_at_creation_leaves_no_error_for_the_next_checkout() {
		$discount      = $this->discount_used_up_while_the_order_is_built();
		$purchase_data = array(
			'price'         => 10.0,
			'user_email'    => 'buyer@edd.test',
			'purchase_key'  => md5( 'paypal-create' ),
			'currency'      => 'USD',
			'gateway_nonce' => wp_create_nonce( 'edd-gateway' ),
			'downloads'     => array(
				array(
					'id'       => 1,
					'quantity' => 1,
				),
			),
			'user_info'     => array(
				'id'         => 0,
				'email'      => 'buyer@edd.test',
				'first_name' => 'Pat',
				'last_name'  => 'Buyer',
				'discount'   => $discount->code,
				'address'    => array(),
			),
			'cart_details'  => array(
				array(
					'name'        => 'Test Download',
					'id'          => 1,
					'item_number' => array(
						'id'       => 1,
						'quantity' => 1,
					),
					'item_price'  => 20.0,
					'quantity'    => 1,
					'discount'    => 10.0,
					'subtotal'    => 20.0,
					'tax'         => 0.0,
					'fees'        => array(),
					'price'       => 10.0,
				),
			),
		);
		add_action(
			'wp_ajax_edd_test_paypal_create_order',
			function () use ( $purchase_data ) {
				\EDD\Gateways\PayPal\create_order( $purchase_data );
			}
		);

		$response = json_decode( $this->_handleAjax( 'edd_test_paypal_create_order' ), true );

		$orders = edd_get_orders( array( 'email' => 'buyer@edd.test' ) );
		$this->assertFalse( $response['success'] );
		$this->assertStringContainsString( $discount->code, $response['data'] );
		$this->assertCount( 1, $orders );
		$this->assertSame( 'failed', $orders[0]->status );
		$this->assertSame( array(), $this->paypal_requests );
		$this->assertArrayNotHasKey( 'edd-discount-error', (array) edd_get_errors() );
	}

	/**
	 * A use released while PayPal is capturing cannot leave the captured order completed without it.
	 */
	public function test_use_released_during_capture_is_not_completed_without_its_claim() {
		$discount       = $this->create_discount( array( 'max_uses' => 1 ) );
		$order          = $this->pending_paypal_order_with( $discount );
		$original_order = EDD()->session->get( 'edd_resume_payment' );
		$other_order    = null;

		// While PayPal is capturing, the buyer's session loads the failed transaction page and another checkout takes the use.
		$release_during_capture = function ( $preempt, $args, $url ) use ( $order, $discount, &$other_order ) {
			if ( false === strpos( $url, '/capture' ) || $other_order ) {
				return $preempt;
			}

			EDD()->session->set( 'edd_resume_payment', $order->id );
			add_filter( 'edd_is_failure_page', '__return_true' );
			edd_listen_for_failed_payments();
			remove_filter( 'edd_is_failure_page', '__return_true' );

			$other_order = $this->create_order_with_discount(
				$discount,
				array(
					'status'  => 'pending',
					'gateway' => 'paypal_commerce',
					'email'   => 'other-buyer@edd.test',
				)
			);
			\EDD\Discounts\Claims::claim( $other_order );

			// Runs after record_paypal_request(), which would capture the newest order, now the other one.
			return array(
				'response' => array( 'code' => 200 ),
				'body'     => wp_json_encode(
					array(
						'id'             => self::PAYPAL_ORDER_ID,
						'status'         => 'COMPLETED',
						'purchase_units' => array(
							array(
								'reference_id' => $order->payment_key,
								'payments'     => array(
									'captures' => array(
										array(
											'id'     => 'CAPTURE-1',
											'status' => 'COMPLETED',
										),
									),
								),
							),
						),
					)
				),
			);
		};
		add_filter( 'pre_http_request', $release_during_capture, 20, 3 );

		$this->post(
			array(
				'edd_process_paypal_nonce' => wp_create_nonce( 'edd_process_paypal' ),
				'paypal_order_id'          => self::PAYPAL_ORDER_ID,
			)
		);

		try {
			$this->_handleAjax( 'edd_capture_paypal_order' );
		} catch ( \WPAjaxDieContinueException $e ) {
			unset( $e );
		}

		remove_filter( 'pre_http_request', $release_during_capture, 20 );
		EDD()->session->set( 'edd_resume_payment', $original_order );

		$captured_order = edd_get_order( $order->id );
		$this->assertTrue(
			'complete' !== $captured_order->status || \EDD\Discounts\Claims::holds( $captured_order->id, $discount->id ),
			sprintf( 'Order %d completed without holding a use of a 1-use code; order %d holds it.', $captured_order->id, $other_order->id )
		);
	}

	/**
	 * A use released while the card fields capture is in flight stays with the captured order.
	 */
	public function test_card_fields_order_keeps_its_use_when_released_during_capture() {
		$discount    = $this->create_discount( array( 'max_uses' => 1 ) );
		$order       = $this->pending_paypal_order_with( $discount );
		$other_order = null;
		$this->assertTrue( \EDD\Discounts\Claims::claim( $order ) );

		$release = function ( $preempt ) use ( $order, $discount, &$other_order ) {
			if ( ! $other_order ) {
				$other_order = $this->release_from_the_buyers_session( $order, $discount );
			}

			return $preempt;
		};
		add_filter( 'pre_http_request', $release, 20 );

		( new UnbrandedCard( new Security() ) )->capture(
			$this->rest_request(
				array(
					'paypal_order_id' => self::PAYPAL_ORDER_ID,
					'edd_order_id'    => $order->id,
				)
			)
		);

		$this->assertNotNull( $other_order );
		$this->assertSame( 'complete', edd_get_order( $order->id )->status );
		$this->assertTrue( \EDD\Discounts\Claims::holds( $order->id, $discount->id ) );
		$this->assertFalse( \EDD\Discounts\Claims::holds( $other_order->id, $discount->id ) );
	}

	/**
	 * A use released while Fastlane is taking payment stays with the order being paid.
	 */
	public function test_fastlane_order_keeps_its_use_when_released_during_payment() {
		$discount    = $this->create_discount( array( 'max_uses' => 1 ) );
		$other_order = null;
		$this->checkout_with_cart_discount( $discount );

		$release = function ( $preempt ) use ( $discount, &$other_order ) {
			if ( ! $other_order ) {
				$orders      = edd_get_orders( array( 'email' => 'buyer@edd.test' ) );
				$other_order = $this->release_from_the_buyers_session( $orders[0], $discount );
			}

			return $preempt;
		};
		add_filter( 'pre_http_request', $release, 20 );

		( new Fastlane( new Security() ) )->process_payment( $this->rest_request( array( 'payment_token' => 'TOKEN' ) ) );

		$orders = edd_get_orders( array( 'email' => 'buyer@edd.test' ) );
		$this->assertNotNull( $other_order );
		$this->assertCount( 1, $orders );
		$this->assertSame( 'complete', $orders[0]->status );
		$this->assertTrue( \EDD\Discounts\Claims::holds( $orders[0]->id, $discount->id ) );
		$this->assertFalse( \EDD\Discounts\Claims::holds( $other_order->id, $discount->id ) );
	}

	/**
	 * A capture request for an order another request is already capturing is refused before PayPal is called.
	 */
	public function test_capture_is_not_requested_while_the_order_is_processing() {
		$order = $this->pending_paypal_order_with( $this->create_discount() );
		$this->assertTrue( \EDD\Discounts\OrderValidation::start_capture( $order->id ) );
		$this->assertSame( 'processing', edd_get_order( $order->id )->status );

		$this->post(
			array(
				'edd_process_paypal_nonce' => wp_create_nonce( 'edd_process_paypal' ),
				'paypal_order_id'          => self::PAYPAL_ORDER_ID,
			)
		);

		$response = json_decode( $this->_handleAjax( 'edd_capture_paypal_order' ), true );

		$this->assertFalse( $response['success'] );
		$this->assertSame( array(), $this->capture_requests() );
		$this->assertSame( 'processing', edd_get_order( $order->id )->status );
	}

	/**
	 * Discounts are redeemed while the order is processing, where the pending-order sweeps can't release its use.
	 */
	public function test_capture_redeems_discounts_only_once_the_order_is_processing() {
		$order    = $this->pending_paypal_order_with( $this->create_discount() );
		$statuses = array();
		$record   = $this->record_order_status_while_redeeming( $order->id, $statuses );

		$this->post(
			array(
				'edd_process_paypal_nonce' => wp_create_nonce( 'edd_process_paypal' ),
				'paypal_order_id'          => self::PAYPAL_ORDER_ID,
			)
		);

		$this->_handleAjax( 'edd_capture_paypal_order' );
		remove_filter( 'edd_is_discount_active', $record );

		$this->assertNotEmpty( $statuses );
		$this->assertSame( array( 'processing' ), array_values( array_unique( $statuses ) ) );
	}

	/**
	 * The card fields capture redeems discounts while the order is processing, where the pending-order sweeps can't release its use.
	 */
	public function test_card_fields_capture_redeems_discounts_only_once_the_order_is_processing() {
		$order    = $this->pending_paypal_order_with( $this->create_discount() );
		$statuses = array();
		$record   = $this->record_order_status_while_redeeming( $order->id, $statuses );

		( new UnbrandedCard( new Security() ) )->capture(
			$this->rest_request(
				array(
					'paypal_order_id' => self::PAYPAL_ORDER_ID,
					'edd_order_id'    => $order->id,
				)
			)
		);
		remove_filter( 'edd_is_discount_active', $record );

		$this->assertNotEmpty( $statuses );
		$this->assertSame( array( 'processing' ), array_values( array_unique( $statuses ) ) );
	}

	/**
	 * A card fields order refused while processing is failed and gives back the use it held.
	 */
	public function test_card_fields_capture_refused_while_processing_fails_the_order() {
		$discount = $this->create_discount( array( 'max_uses' => 5 ) );
		$order    = $this->pending_paypal_order_with( $discount );
		$this->assertTrue( \EDD\Discounts\Claims::claim( $order ) );
		edd_update_discount( $discount->id, array( 'status' => 'inactive' ) );

		$result = ( new UnbrandedCard( new Security() ) )->capture(
			$this->rest_request(
				array(
					'paypal_order_id' => self::PAYPAL_ORDER_ID,
					'edd_order_id'    => $order->id,
				)
			)
		);

		$this->assertWPError( $result );
		$this->assertSame( 'invalid_discount', $result->get_error_code() );
		$this->assertSame( 'failed', edd_get_order( $order->id )->status );
		$this->assertFalse( \EDD\Discounts\Claims::holds( $order->id, $discount->id ) );
		$this->assertSame( 0, (int) edd_get_discount( $discount->id )->use_count );
	}

	/**
	 * A failed order whose retried capture PayPal rejects goes back to failed and gives its use back.
	 */
	public function test_rejected_capture_of_a_failed_order_leaves_it_failed() {
		$discount = $this->create_discount( array( 'max_uses' => 1 ) );
		$order    = $this->pending_paypal_order_with( $discount );
		$this->assertTrue( \EDD\Discounts\Claims::claim( $order ) );
		edd_update_order_status( $order->id, 'failed' );
		$this->assertSame( 0, (int) edd_get_discount( $discount->id )->use_count );
		add_filter( 'pre_http_request', array( $this, 'reject_capture' ), 20, 3 );

		$this->post(
			array(
				'edd_process_paypal_nonce' => wp_create_nonce( 'edd_process_paypal' ),
				'paypal_order_id'          => self::PAYPAL_ORDER_ID,
			)
		);

		$response = json_decode( $this->capture_in_the_buyers_session( $order ), true );

		$this->assertFalse( $response['success'] );
		$this->assertCount( 1, $this->capture_requests() );
		$this->assertSame( 'failed', edd_get_order( $order->id )->status );
		$this->assertSame( 0, (int) edd_get_discount( $discount->id )->use_count );
	}

	/**
	 * A capture PayPal reports with a status the gateway does not map leaves the order pending.
	 */
	public function test_capture_with_an_unrecognized_status_leaves_the_order_pending() {
		$order = $this->pending_paypal_order_with( $this->create_discount() );
		add_filter( 'pre_http_request', array( $this, 'answer_capture_with_unrecognized_status' ), 20, 3 );

		$this->post(
			array(
				'edd_process_paypal_nonce' => wp_create_nonce( 'edd_process_paypal' ),
				'paypal_order_id'          => self::PAYPAL_ORDER_ID,
			)
		);

		$this->_handleAjax( 'edd_capture_paypal_order' );

		$this->assertCount( 1, $this->capture_requests() );
		$this->assertSame( 'pending', edd_get_order( $order->id )->status );
	}

	/**
	 * A declined capture that PayPal retries with another payment method claims the use again and captures.
	 */
	public function test_declined_capture_can_be_retried_with_another_payment_method() {
		$discount = $this->create_discount( array( 'max_uses' => 1 ) );
		$order    = $this->pending_paypal_order_with( $discount );
		$this->assertTrue( \EDD\Discounts\Claims::claim( $order ) );
		edd_update_order_status( $order->id, 'failed' );
		$this->assertSame( 0, (int) edd_get_discount( $discount->id )->use_count );

		$this->post(
			array(
				'edd_process_paypal_nonce' => wp_create_nonce( 'edd_process_paypal' ),
				'paypal_order_id'          => self::PAYPAL_ORDER_ID,
			)
		);

		$this->capture_in_the_buyers_session( $order );

		$this->assertCount( 1, $this->capture_requests() );
		$this->assertSame( 'complete', edd_get_order( $order->id )->status );
		$this->assertSame( 1, (int) edd_get_discount( $discount->id )->use_count );
	}

	/**
	 * The retry's capture is the order's transaction after a decline, since webhooks and refunds use it.
	 */
	public function test_retry_after_a_declined_capture_is_the_orders_transaction() {
		$discount = $this->create_discount( array( 'max_uses' => 1 ) );
		$order    = $this->pending_paypal_order_with( $discount );
		add_filter( 'pre_http_request', array( $this, 'answer_capture_as_declined' ), 20 );

		$this->post(
			array(
				'edd_process_paypal_nonce' => wp_create_nonce( 'edd_process_paypal' ),
				'paypal_order_id'          => self::PAYPAL_ORDER_ID,
			)
		);
		$this->capture_in_the_buyers_session( $order );
		$this->assertSame( 'failed', edd_get_order( $order->id )->status );

		remove_filter( 'pre_http_request', array( $this, 'answer_capture_as_declined' ), 20 );
		$this->capture_in_the_buyers_session( $order );

		$this->assertSame( 'CAPTURE-1', edd_get_order( $order->id )->get_transaction_id() );
		$this->assertSame( 'complete', edd_get_order( $order->id )->status );
	}

	/**
	 * A declined capture is noted on the order without becoming its transaction.
	 */
	public function test_declined_capture_is_noted_without_a_transaction() {
		$discount = $this->create_discount( array( 'max_uses' => 1 ) );
		$order    = $this->pending_paypal_order_with( $discount );
		add_filter( 'pre_http_request', array( $this, 'answer_capture_as_declined' ), 20 );

		$this->post(
			array(
				'edd_process_paypal_nonce' => wp_create_nonce( 'edd_process_paypal' ),
				'paypal_order_id'          => self::PAYPAL_ORDER_ID,
			)
		);
		$this->capture_in_the_buyers_session( $order );

		$this->assertEmpty( edd_get_order( $order->id )->get_transaction_id() );
		$this->assertTrue( $this->order_has_note_containing( $order->id, 'CAPTURE-DECLINED' ) );
	}

	/**
	 * A declined card fields capture is noted on the order without becoming its transaction.
	 */
	public function test_declined_card_fields_capture_is_noted_without_a_transaction() {
		$discount = $this->create_discount( array( 'max_uses' => 1 ) );
		$order    = $this->pending_paypal_order_with( $discount );
		add_filter( 'pre_http_request', array( $this, 'answer_capture_as_declined' ), 20 );

		( new UnbrandedCard( new Security() ) )->capture(
			$this->rest_request(
				array(
					'paypal_order_id' => self::PAYPAL_ORDER_ID,
					'edd_order_id'    => $order->id,
				)
			)
		);

		$this->assertEmpty( edd_get_order( $order->id )->get_transaction_id() );
		$this->assertTrue( $this->order_has_note_containing( $order->id, 'CAPTURE-DECLINED' ) );
	}

	/**
	 * A declined Fastlane payment is noted on the order without becoming its transaction.
	 */
	public function test_declined_fastlane_payment_is_noted_without_a_transaction() {
		$discount = $this->create_discount( array( 'max_uses' => 1 ) );
		$this->checkout_with_cart_discount( $discount );
		add_filter( 'pre_http_request', array( $this, 'answer_capture_as_declined' ), 20 );

		( new Fastlane( new Security() ) )->process_payment( $this->rest_request( array( 'payment_token' => 'TOKEN' ) ) );

		$orders = edd_get_orders( array( 'email' => 'buyer@edd.test' ) );
		$this->assertCount( 1, $orders );
		$this->assertEmpty( $orders[0]->get_transaction_id() );
		$this->assertTrue( $this->order_has_note_containing( $orders[0]->id, 'CAPTURE-DECLINED' ) );
	}

	/**
	 * A capture request for a failed order from outside the buyer's checkout is refused and leaves the order failed.
	 */
	public function test_capture_of_a_failed_order_requires_the_buyers_session() {
		$discount = $this->create_discount( array( 'max_uses' => 1 ) );
		$order    = $this->pending_paypal_order_with( $discount );
		edd_update_order_status( $order->id, 'failed' );
		$this->assertSame( 'failed', edd_get_order( $order->id )->status );
		$this->assertNotSame( $order->id, absint( EDD()->session->get( 'edd_resume_payment' ) ) );
		$notes = edd_count_notes( array( 'object_id' => $order->id, 'object_type' => 'order' ) );

		$this->post(
			array(
				'edd_process_paypal_nonce' => wp_create_nonce( 'edd_process_paypal' ),
				'paypal_order_id'          => self::PAYPAL_ORDER_ID,
			)
		);

		$response = json_decode( $this->_handleAjax( 'edd_capture_paypal_order' ), true );

		$this->assertFalse( $response['success'] );
		$this->assertSame( array(), $this->capture_requests() );
		$this->assertSame( 'failed', edd_get_order( $order->id )->status );
		$this->assertSame( 0, (int) edd_get_discount( $discount->id )->use_count );
		$this->assertSame( $notes, edd_count_notes( array( 'object_id' => $order->id, 'object_type' => 'order' ) ) );
	}

	/**
	 * A capture request for a trashed order is refused before PayPal is asked to take payment.
	 */
	public function test_capture_is_not_requested_for_a_trashed_order() {
		$discount = $this->create_discount( array( 'max_uses' => 1 ) );
		$order    = $this->pending_paypal_order_with( $discount );
		edd_trash_order( $order->id );
		$this->assertSame( 'trash', edd_get_order( $order->id )->status );
		$this->assertSame( 0, (int) edd_get_discount( $discount->id )->use_count );

		$this->post(
			array(
				'edd_process_paypal_nonce' => wp_create_nonce( 'edd_process_paypal' ),
				'paypal_order_id'          => self::PAYPAL_ORDER_ID,
			)
		);

		$response = json_decode( $this->capture_in_the_buyers_session( $order ), true );

		$this->assertFalse( $response['success'] );
		$this->assertSame( array(), $this->capture_requests() );
		$this->assertSame( 'trash', edd_get_order( $order->id )->status );
		$this->assertSame( 0, (int) edd_get_discount( $discount->id )->use_count );
	}

	/**
	 * Fastlane fails the order before PayPal is asked to take payment when the discount's last use is gone.
	 */
	public function test_fastlane_order_is_failed_when_the_discount_use_cannot_be_claimed() {
		$this->checkout_with_cart_discount( $this->discount_used_up_while_the_order_is_built() );

		$result = ( new Fastlane( new Security() ) )->process_payment( $this->rest_request( array( 'payment_token' => 'TOKEN' ) ) );

		$orders = edd_get_orders( array( 'email' => 'buyer@edd.test' ) );
		$this->assertWPError( $result );
		$this->assertSame( 'invalid_discount', $result->get_error_code() );
		$this->assertSame( array(), $this->paypal_requests );
		$this->assertCount( 1, $orders );
		$this->assertSame( 'failed', $orders[0]->status );
	}

	/**
	 * The card fields create step fails the order before PayPal is contacted when the discount's last use is gone.
	 */
	public function test_card_fields_order_is_failed_when_the_discount_use_cannot_be_claimed() {
		$this->checkout_with_cart_discount( $this->discount_used_up_while_the_order_is_built() );

		$result = ( new UnbrandedCard( new Security() ) )->create( $this->rest_request( array() ) );

		$orders = edd_get_orders( array( 'email' => 'buyer@edd.test' ) );
		$this->assertWPError( $result );
		$this->assertSame( 'invalid_discount', $result->get_error_code() );
		$this->assertSame( array(), $this->paypal_requests );
		$this->assertCount( 1, $orders );
		$this->assertSame( 'failed', $orders[0]->status );
		$this->assertSame( array(), edd_get_cart_discounts() );
		$this->assertTrue( $result->get_error_data()['refresh'] );
	}

	/**
	 * A capture that ends in an unexpected error puts the order back to pending.
	 */
	public function test_capture_that_errors_unexpectedly_puts_the_order_back_to_pending() {
		$order = $this->pending_paypal_order_with( $this->create_discount() );
		add_filter( 'pre_http_request', array( $this, 'error_on_paypal_request' ), 20 );

		$this->post(
			array(
				'edd_process_paypal_nonce' => wp_create_nonce( 'edd_process_paypal' ),
				'paypal_order_id'          => self::PAYPAL_ORDER_ID,
			)
		);
		$error = $this->handle_ajax_with_error( 'edd_capture_paypal_order' );

		$this->assertInstanceOf( \RuntimeException::class, $error );
		$this->assertCount( 1, $this->capture_requests() );
		$this->assertSame( 'pending', edd_get_order( $order->id )->status );
	}

	/**
	 * A card fields capture that ends in an unexpected error puts the order back to pending.
	 */
	public function test_card_fields_capture_that_errors_unexpectedly_puts_the_order_back_to_pending() {
		$order = $this->pending_paypal_order_with( $this->create_discount() );
		add_filter( 'pre_http_request', array( $this, 'error_on_paypal_request' ), 20 );
		$error = null;

		try {
			( new UnbrandedCard( new Security() ) )->capture(
				$this->rest_request(
					array(
						'paypal_order_id' => self::PAYPAL_ORDER_ID,
						'edd_order_id'    => $order->id,
					)
				)
			);
		} catch ( \RuntimeException $e ) {
			$error = $e;
		}

		$this->assertInstanceOf( \RuntimeException::class, $error );
		$this->assertNotEmpty( $this->paypal_requests );
		$this->assertSame( 'pending', edd_get_order( $order->id )->status );
	}

	/**
	 * A Fastlane payment that ends in an unexpected error puts the order back to pending.
	 */
	public function test_fastlane_payment_that_errors_unexpectedly_puts_the_order_back_to_pending() {
		$this->checkout_with_cart_discount( $this->create_discount() );
		add_filter( 'pre_http_request', array( $this, 'error_on_paypal_request' ), 20 );
		$error = null;

		try {
			( new Fastlane( new Security() ) )->process_payment( $this->rest_request( array( 'payment_token' => 'TOKEN' ) ) );
		} catch ( \RuntimeException $e ) {
			$error = $e;
		}

		$orders = edd_get_orders( array( 'email' => 'buyer@edd.test' ) );
		$this->assertInstanceOf( \RuntimeException::class, $error );
		$this->assertNotEmpty( $this->paypal_requests );
		$this->assertCount( 1, $orders );
		$this->assertSame( 'pending', $orders[0]->status );
	}

	/**
	 * An error after PayPal captures leaves the paid order processing with its use held, out of reach of the pending-order sweep.
	 */
	public function test_capture_that_errors_after_paypal_captures_leaves_the_order_processing() {
		$discount = $this->create_discount( array( 'max_uses' => 1 ) );
		$order    = $this->pending_paypal_order_with( $discount );
		add_action( 'edd_order_transaction_added', array( $this, 'error_on_recording_the_transaction' ) );

		$this->post(
			array(
				'edd_process_paypal_nonce' => wp_create_nonce( 'edd_process_paypal' ),
				'paypal_order_id'          => self::PAYPAL_ORDER_ID,
			)
		);
		$error = $this->handle_ajax_with_error( 'edd_capture_paypal_order' );

		$this->assertInstanceOf( \RuntimeException::class, $error );
		$this->assertCount( 1, $this->capture_requests() );
		$this->assertSame( 'processing', edd_get_order( $order->id )->status );
		$this->assertTrue( \EDD\Discounts\Claims::holds( $order->id, $discount->id ) );
	}

	/**
	 * An error after PayPal captures a retried declined order leaves it processing rather than failing a paid order.
	 */
	public function test_retried_capture_that_errors_after_paypal_captures_leaves_the_order_processing() {
		$discount = $this->create_discount( array( 'max_uses' => 1 ) );
		$order    = $this->pending_paypal_order_with( $discount );
		$this->assertTrue( \EDD\Discounts\Claims::claim( $order ) );
		edd_update_order_status( $order->id, 'failed' );
		add_action( 'edd_order_transaction_added', array( $this, 'error_on_recording_the_transaction' ) );

		$this->post(
			array(
				'edd_process_paypal_nonce' => wp_create_nonce( 'edd_process_paypal' ),
				'paypal_order_id'          => self::PAYPAL_ORDER_ID,
			)
		);
		$original_order = EDD()->session->get( 'edd_resume_payment' );
		EDD()->session->set( 'edd_resume_payment', $order->id );
		$error = $this->handle_ajax_with_error( 'edd_capture_paypal_order' );
		EDD()->session->set( 'edd_resume_payment', $original_order );

		$this->assertInstanceOf( \RuntimeException::class, $error );
		$this->assertCount( 1, $this->capture_requests() );
		$this->assertSame( 'processing', edd_get_order( $order->id )->status );
		$this->assertTrue( \EDD\Discounts\Claims::holds( $order->id, $discount->id ) );
	}

	/**
	 * An error after PayPal captures a card fields order leaves it processing with its use held.
	 */
	public function test_card_fields_capture_that_errors_after_paypal_captures_leaves_the_order_processing() {
		$discount = $this->create_discount( array( 'max_uses' => 1 ) );
		$order    = $this->pending_paypal_order_with( $discount );
		add_action( 'edd_order_transaction_added', array( $this, 'error_on_recording_the_transaction' ) );
		$error = null;

		try {
			( new UnbrandedCard( new Security() ) )->capture(
				$this->rest_request(
					array(
						'paypal_order_id' => self::PAYPAL_ORDER_ID,
						'edd_order_id'    => $order->id,
					)
				)
			);
		} catch ( \RuntimeException $e ) {
			$error = $e;
		}

		$this->assertInstanceOf( \RuntimeException::class, $error );
		$this->assertCount( 1, $this->capture_requests() );
		$this->assertSame( 'processing', edd_get_order( $order->id )->status );
		$this->assertTrue( \EDD\Discounts\Claims::holds( $order->id, $discount->id ) );
	}

	/**
	 * An error after PayPal takes a Fastlane payment leaves the order processing with its use held.
	 */
	public function test_fastlane_payment_that_errors_after_paypal_captures_leaves_the_order_processing() {
		$discount = $this->create_discount( array( 'max_uses' => 1 ) );
		$this->checkout_with_cart_discount( $discount );
		add_action( 'edd_order_transaction_added', array( $this, 'error_on_recording_the_transaction' ) );
		$error = null;

		try {
			( new Fastlane( new Security() ) )->process_payment( $this->rest_request( array( 'payment_token' => 'TOKEN' ) ) );
		} catch ( \RuntimeException $e ) {
			$error = $e;
		}

		$orders = edd_get_orders( array( 'email' => 'buyer@edd.test' ) );
		$this->assertInstanceOf( \RuntimeException::class, $error );
		$this->assertNotEmpty( $this->paypal_requests );
		$this->assertCount( 1, $orders );
		$this->assertSame( 'processing', $orders[0]->status );
		$this->assertTrue( \EDD\Discounts\Claims::holds( $orders[0]->id, $discount->id ) );
	}

	/**
	 * A declined capture whose recording errors still fails the order, so its use is given back.
	 */
	public function test_declined_capture_that_errors_while_recording_gives_back_its_use() {
		$discount = $this->create_discount( array( 'max_uses' => 1 ) );
		$order    = $this->pending_paypal_order_with( $discount );
		add_filter( 'pre_http_request', array( $this, 'answer_capture_as_declined' ), 20 );
		add_action( 'edd_note_added', array( $this, 'error_on_adding_the_declined_note' ), 10, 2 );

		$this->post(
			array(
				'edd_process_paypal_nonce' => wp_create_nonce( 'edd_process_paypal' ),
				'paypal_order_id'          => self::PAYPAL_ORDER_ID,
			)
		);
		$error = $this->handle_ajax_with_error( 'edd_capture_paypal_order' );

		$this->assertInstanceOf( \RuntimeException::class, $error );
		$this->assertSame( 'failed', edd_get_order( $order->id )->status );
		$this->assertSame( 0, (int) edd_get_discount( $discount->id )->use_count );
	}

	/**
	 * A declined Fastlane payment whose order meta errors still fails the order, so its use is given back.
	 */
	public function test_declined_fastlane_payment_that_errors_on_its_meta_gives_back_its_use() {
		$discount = $this->create_discount( array( 'max_uses' => 1 ) );
		$this->checkout_with_cart_discount( $discount );
		add_filter( 'pre_http_request', array( $this, 'answer_capture_as_declined' ), 20 );
		add_action( 'added_edd_order_meta', array( $this, 'error_on_storing_the_payment_source' ), 10, 3 );
		add_action( 'updated_edd_order_meta', array( $this, 'error_on_storing_the_payment_source' ), 10, 3 );
		$error = null;

		try {
			( new Fastlane( new Security() ) )->process_payment( $this->rest_request( array( 'payment_token' => 'TOKEN' ) ) );
		} catch ( \RuntimeException $e ) {
			$error = $e;
		}

		$orders = edd_get_orders( array( 'email' => 'buyer@edd.test' ) );
		$this->assertInstanceOf( \RuntimeException::class, $error );
		$this->assertCount( 1, $orders );
		$this->assertSame( 'failed', $orders[0]->status );
		$this->assertSame( 0, (int) edd_get_discount( $discount->id )->use_count );
	}

	/**
	 * An error on a note after PayPal captures leaves the payment recorded, so the webhook can still match it.
	 */
	public function test_capture_that_errors_on_a_note_keeps_the_payment_recorded() {
		$discount = $this->create_discount( array( 'max_uses' => 1 ) );
		$order    = $this->pending_paypal_order_with( $discount );
		add_filter( 'pre_http_request', array( $this, 'answer_capture_as_held_for_review' ), 20 );
		add_action( 'edd_note_added', array( $this, 'error_on_adding_the_review_note' ), 10, 2 );

		$this->post(
			array(
				'edd_process_paypal_nonce' => wp_create_nonce( 'edd_process_paypal' ),
				'paypal_order_id'          => self::PAYPAL_ORDER_ID,
			)
		);
		$error = $this->handle_ajax_with_error( 'edd_capture_paypal_order' );

		$this->assertInstanceOf( \RuntimeException::class, $error );
		$this->assertSame( 'CAPTURE-1', edd_get_order( $order->id )->get_transaction_id() );
		$this->assertSame( 'processing', edd_get_order( $order->id )->status );
		$this->assertTrue( \EDD\Discounts\Claims::holds( $order->id, $discount->id ) );
	}

	/**
	 * A capture whose purchase unit names no order is recorded on the order being captured.
	 */
	public function test_capture_with_an_unmatched_reference_is_recorded_on_the_captured_order() {
		$discount = $this->create_discount( array( 'max_uses' => 1 ) );
		$order    = $this->pending_paypal_order_with( $discount );
		add_filter( 'pre_http_request', array( $this, 'answer_capture_with_unmatched_reference' ), 20 );

		$this->post(
			array(
				'edd_process_paypal_nonce' => wp_create_nonce( 'edd_process_paypal' ),
				'paypal_order_id'          => self::PAYPAL_ORDER_ID,
			)
		);
		$this->_handleAjax( 'edd_capture_paypal_order' );

		$this->assertSame( 'CAPTURE-1', edd_get_order( $order->id )->get_transaction_id() );
		$this->assertSame( 'complete', edd_get_order( $order->id )->status );
		$this->assertSame( 1, (int) edd_get_discount( $discount->id )->use_count );
	}

	/**
	 * A card fields capture PayPal reports with an unmapped status leaves the paid order pending with its use held.
	 */
	public function test_card_fields_capture_with_an_unrecognized_status_keeps_its_use() {
		$discount = $this->create_discount( array( 'max_uses' => 1 ) );
		$order    = $this->pending_paypal_order_with( $discount );
		add_filter( 'pre_http_request', array( $this, 'answer_capture_with_unrecognized_status' ), 20 );

		( new UnbrandedCard( new Security() ) )->capture(
			$this->rest_request(
				array(
					'paypal_order_id' => self::PAYPAL_ORDER_ID,
					'edd_order_id'    => $order->id,
				)
			)
		);

		$this->assertSame( 'pending', edd_get_order( $order->id )->status );
		$this->assertTrue( \EDD\Discounts\Claims::holds( $order->id, $discount->id ) );
	}

	/**
	 * A Fastlane payment PayPal reports with an unmapped status leaves the paid order pending with its use held.
	 */
	public function test_fastlane_payment_with_an_unrecognized_status_keeps_its_use() {
		$discount = $this->create_discount( array( 'max_uses' => 1 ) );
		$this->checkout_with_cart_discount( $discount );
		add_filter( 'pre_http_request', array( $this, 'answer_capture_with_unrecognized_status' ), 20 );

		( new Fastlane( new Security() ) )->process_payment( $this->rest_request( array( 'payment_token' => 'TOKEN' ) ) );

		$orders = edd_get_orders( array( 'email' => 'buyer@edd.test' ) );
		$this->assertCount( 1, $orders );
		$this->assertSame( 'pending', $orders[0]->status );
		$this->assertTrue( \EDD\Discounts\Claims::holds( $orders[0]->id, $discount->id ) );
	}

	/**
	 * A card fields response with no capture took no payment, so the order fails and gives its use back.
	 */
	public function test_card_fields_capture_without_a_capture_fails_the_order() {
		$discount = $this->create_discount( array( 'max_uses' => 1 ) );
		$order    = $this->pending_paypal_order_with( $discount );
		add_filter( 'pre_http_request', array( $this, 'answer_capture_without_a_capture' ), 20 );

		$response = ( new UnbrandedCard( new Security() ) )->capture(
			$this->rest_request(
				array(
					'paypal_order_id' => self::PAYPAL_ORDER_ID,
					'edd_order_id'    => $order->id,
				)
			)
		);

		$this->assertWPError( $response );
		$this->assertSame( 'payment_unknown_status', $response->get_error_code() );
		$this->assertSame( 'failed', edd_get_order( $order->id )->status );
		$this->assertSame( 0, (int) edd_get_discount( $discount->id )->use_count );
	}

	/**
	 * A Fastlane response with no capture took no payment, so the order fails and gives its use back.
	 */
	public function test_fastlane_payment_without_a_capture_fails_the_order() {
		$discount = $this->create_discount( array( 'max_uses' => 1 ) );
		$this->checkout_with_cart_discount( $discount );
		add_filter( 'pre_http_request', array( $this, 'answer_capture_without_a_capture' ), 20 );

		$response = ( new Fastlane( new Security() ) )->process_payment( $this->rest_request( array( 'payment_token' => 'TOKEN' ) ) );

		$orders = edd_get_orders( array( 'email' => 'buyer@edd.test' ) );
		$this->assertWPError( $response );
		$this->assertSame( 'payment_unknown_status', $response->get_error_code() );
		$this->assertCount( 1, $orders );
		$this->assertSame( 'failed', $orders[0]->status );
		$this->assertSame( 0, (int) edd_get_discount( $discount->id )->use_count );
	}

	/**
	 * An error while building the Fastlane order arguments leaves the order pending.
	 */
	public function test_fastlane_order_arguments_error_leaves_the_order_pending() {
		$this->checkout_with_cart_discount( $this->create_discount() );
		$error_on_arguments = function () {
			throw new \RuntimeException( 'Order arguments could not be built.' );
		};
		add_filter( 'edd_paypal_fastlane_order_arguments', $error_on_arguments );
		$error = null;

		try {
			( new Fastlane( new Security() ) )->process_payment( $this->rest_request( array( 'payment_token' => 'TOKEN' ) ) );
		} catch ( \RuntimeException $e ) {
			$error = $e;
		}
		remove_filter( 'edd_paypal_fastlane_order_arguments', $error_on_arguments );

		$orders = edd_get_orders( array( 'email' => 'buyer@edd.test' ) );
		$this->assertInstanceOf( \RuntimeException::class, $error );
		$this->assertCount( 1, $orders );
		$this->assertSame( 'pending', $orders[0]->status );
	}

	/**
	 * A blank PayPal order ID is refused before the session's order is captured.
	 */
	public function test_capture_is_not_requested_for_a_blank_paypal_order_id() {
		$order = $this->create_order_with_discount(
			$this->create_discount(),
			array(
				'status'  => 'pending',
				'gateway' => 'paypal_commerce',
				'email'   => 'buyer@edd.test',
			)
		);
		$this->assertSame( '', edd_get_order_meta( $order->id, 'paypal_order_id', true ) );
		$original_order = EDD()->session->get( 'edd_resume_payment' );
		EDD()->session->set( 'edd_resume_payment', $order->id );

		$this->post(
			array(
				'edd_process_paypal_nonce' => wp_create_nonce( 'edd_process_paypal' ),
				'paypal_order_id'          => ' ',
			)
		);
		$response = json_decode( $this->_handleAjax( 'edd_capture_paypal_order' ), true );
		EDD()->session->set( 'edd_resume_payment', $original_order );

		$this->assertFalse( $response['success'] );
		$this->assertSame( array(), $this->capture_requests() );
		$this->assertSame( 'pending', edd_get_order( $order->id )->status );
	}

	/**
	 * Records each PayPal request and answers it with a completed capture.
	 *
	 * @param false|array $preempt Whether to preempt the request.
	 * @param array       $args    Request arguments.
	 * @param string      $url     Request URL.
	 * @return array
	 */
	public function record_paypal_request( $preempt, $args, $url ) {
		$this->paypal_requests[] = $url;

		$order = edd_get_orders(
			array(
				'number'  => 1,
				'orderby' => 'id',
				'order'   => 'DESC',
			)
		);

		return array(
			'response' => array( 'code' => 200 ),
			'body'     => wp_json_encode(
				array(
					'id'             => self::PAYPAL_ORDER_ID,
					'status'         => 'COMPLETED',
					'purchase_units' => array(
						array(
							'reference_id' => ! empty( $order ) ? $order[0]->payment_key : '',
							'payments'     => array(
								'captures' => array(
									array(
										'id'     => 'CAPTURE-1',
										'status' => 'COMPLETED',
									),
								),
							),
						),
					),
				)
			),
		);
	}

	/**
	 * Answers a capture request with an error that is not a decline.
	 *
	 * @param false|array $preempt Whether to preempt the request.
	 * @param array       $args    Request arguments.
	 * @param string      $url     Request URL.
	 * @return false|array
	 */
	public function reject_capture( $preempt, $args, $url ) {
		if ( false === strpos( $url, '/capture' ) ) {
			return $preempt;
		}

		return array(
			'response' => array(
				'code'    => 422,
				'message' => 'Unprocessable Entity',
			),
			'body'     => wp_json_encode(
				array(
					'name'    => 'UNPROCESSABLE_ENTITY',
					'details' => array( array( 'issue' => 'ORDER_NOT_APPROVED' ) ),
				)
			),
		);
	}

	/**
	 * Rewrites the recorded PayPal answer so its capture carries a status the gateway does not map.
	 *
	 * @param false|array $preempt The answer from record_paypal_request().
	 * @return false|array
	 */
	public function answer_capture_with_unrecognized_status( $preempt ) {
		return $this->rewrite_purchase_unit( $preempt, array( 'payments' => array( 'captures' => array( array( 'status' => 'UNRECOGNIZED' ) ) ) ) );
	}

	/**
	 * Rewrites the recorded PayPal answer so it carries no capture.
	 *
	 * @param false|array $preempt The answer from record_paypal_request().
	 * @return false|array
	 */
	public function answer_capture_without_a_capture( $preempt ) {
		if ( ! is_array( $preempt ) ) {
			return $preempt;
		}

		// The recursive replace in rewrite_purchase_unit() can't empty a list.
		$body = json_decode( $preempt['body'], true );
		$body['purchase_units'][0]['payments']['captures'] = array();
		$preempt['body']                                   = wp_json_encode( $body );

		return $preempt;
	}

	/**
	 * Rewrites the recorded PayPal answer so its capture is declined.
	 *
	 * @param false|array $preempt The answer from record_paypal_request().
	 * @return false|array
	 */
	public function answer_capture_as_declined( $preempt ) {
		$declined = array(
			'id'     => 'CAPTURE-DECLINED',
			'status' => 'DECLINED',
		);

		return $this->rewrite_purchase_unit( $preempt, array( 'payments' => array( 'captures' => array( $declined ) ) ) );
	}

	/**
	 * Rewrites the recorded PayPal answer so its capture is held for review.
	 *
	 * @param false|array $preempt The answer from record_paypal_request().
	 * @return false|array
	 */
	public function answer_capture_as_held_for_review( $preempt ) {
		$held = array(
			'status'         => 'PENDING',
			'status_details' => array( 'reason' => 'PENDING_REVIEW' ),
		);

		return $this->rewrite_purchase_unit( $preempt, array( 'payments' => array( 'captures' => array( $held ) ) ) );
	}

	/**
	 * Rewrites the recorded PayPal answer so its purchase unit names no order.
	 *
	 * @param false|array $preempt The answer from record_paypal_request().
	 * @return false|array
	 */
	public function answer_capture_with_unmatched_reference( $preempt ) {
		return $this->rewrite_purchase_unit( $preempt, array( 'reference_id' => 'NO-SUCH-ORDER' ) );
	}

	/**
	 * Fails every PayPal request with an error that is not a gateway error.
	 *
	 * @throws \RuntimeException Always.
	 * @return void
	 */
	public function error_on_paypal_request() {
		throw new \RuntimeException( 'Unexpected error while contacting PayPal.' );
	}

	/**
	 * Fails the request while the captured payment's transaction is being recorded.
	 *
	 * @throws \RuntimeException Always.
	 * @return void
	 */
	public function error_on_recording_the_transaction() {
		throw new \RuntimeException( 'Unexpected error while recording the transaction.' );
	}

	/**
	 * Fails the request while the PayPal payment source is stored on the order.
	 *
	 * @param int    $meta_id  The meta ID.
	 * @param int    $order_id The order ID.
	 * @param string $meta_key The meta key.
	 * @throws \RuntimeException On the payment source.
	 * @return void
	 */
	public function error_on_storing_the_payment_source( $meta_id, $order_id, $meta_key ) {
		if ( '_edd_paypal_payment_source' === $meta_key ) {
			throw new \RuntimeException( 'Unexpected error while storing the payment source.' );
		}
	}

	/**
	 * Fails the request while the note for a capture held for review is added.
	 *
	 * @param int   $note_id The note ID.
	 * @param array $note    The note data.
	 * @throws \RuntimeException On the review note.
	 * @return void
	 */
	public function error_on_adding_the_review_note( $note_id, $note ) {
		if ( false !== strpos( $note['content'] ?? '', 'PENDING_REVIEW' ) ) {
			throw new \RuntimeException( 'Unexpected error while adding the review note.' );
		}
	}

	/**
	 * Fails the request while the note for a declined capture is added.
	 *
	 * @param int   $note_id The note ID.
	 * @param array $note    The note data.
	 * @throws \RuntimeException On the declined capture note.
	 * @return void
	 */
	public function error_on_adding_the_declined_note( $note_id, $note ) {
		if ( false !== strpos( $note['content'] ?? '', 'CAPTURE-DECLINED' ) ) {
			throw new \RuntimeException( 'Unexpected error while adding the declined capture note.' );
		}
	}

	/**
	 * Merges changes into the first purchase unit of a recorded PayPal answer.
	 *
	 * @param false|array $preempt The answer from record_paypal_request().
	 * @param array       $changes The purchase unit fields to change.
	 * @return false|array
	 */
	private function rewrite_purchase_unit( $preempt, $changes ) {
		if ( ! is_array( $preempt ) ) {
			return $preempt;
		}

		$body                      = json_decode( $preempt['body'], true );
		$body['purchase_units'][0] = array_replace_recursive( $body['purchase_units'][0], $changes );
		$preempt['body']           = wp_json_encode( $body );

		return $preempt;
	}

	/**
	 * Sets posted fields, remembering what they held before.
	 *
	 * @param array $fields The fields to post.
	 * @return void
	 */
	private function post( $fields ) {
		foreach ( $fields as $key => $value ) {
			if ( ! array_key_exists( $key, $this->original_post ) ) {
				$this->original_post[ $key ] = $_POST[ $key ] ?? null;
			}
			$_POST[ $key ] = $value;
		}
	}

	/**
	 * Runs an AJAX action that ends in an uncaught error, closing the output buffer it leaves open.
	 *
	 * @param string $action The AJAX action.
	 * @return \RuntimeException|null The error, or null when none was thrown.
	 */
	private function handle_ajax_with_error( $action ) {
		$level = ob_get_level();

		try {
			$this->_handleAjax( $action );
		} catch ( \RuntimeException $e ) {
			while ( ob_get_level() > $level ) {
				ob_end_clean();
			}

			return $e;
		}

		return null;
	}

	/**
	 * Runs the capture request from the checkout session that built the order.
	 *
	 * @param \EDD\Orders\Order $order The order the session is paying for.
	 * @return string The AJAX response.
	 */
	private function capture_in_the_buyers_session( $order ) {
		$original_order = EDD()->session->get( 'edd_resume_payment' );
		EDD()->session->set( 'edd_resume_payment', $order->id );

		try {
			return $this->_handleAjax( 'edd_capture_paypal_order' );
		} finally {
			EDD()->session->set( 'edd_resume_payment', $original_order );
		}
	}

	/**
	 * Returns the recorded requests that asked PayPal to capture.
	 *
	 * @return string[]
	 */
	private function capture_requests() {
		return array_values(
			array_filter(
				$this->paypal_requests,
				function ( $url ) {
					return false !== strpos( $url, '/capture' );
				}
			)
		);
	}

	/**
	 * Records the order's status each time a discount's availability is checked; the caller removes the filter.
	 *
	 * @param int   $order_id The order ID.
	 * @param array $statuses Receives the recorded statuses.
	 * @return callable The `edd_is_discount_active` filter added.
	 */
	private function record_order_status_while_redeeming( $order_id, &$statuses ) {
		$record = function ( $is_active ) use ( $order_id, &$statuses ) {
			$statuses[] = edd_get_order( $order_id )->status;

			return $is_active;
		};
		add_filter( 'edd_is_discount_active', $record );

		return $record;
	}

	/**
	 * Whether any note on the order contains the text.
	 *
	 * @param int    $order_id The order ID.
	 * @param string $text     The text to look for.
	 * @return bool
	 */
	private function order_has_note_containing( $order_id, $text ) {
		$notes = edd_get_notes(
			array(
				'object_id'   => $order_id,
				'object_type' => 'order',
				// Berlin's no-limit value.
				'number'      => 0,
			)
		);

		foreach ( $notes as $note ) {
			if ( false !== strpos( $note->content, $text ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Creates an active discount whose single use is already claimed.
	 *
	 * @return \EDD_Discount
	 */
	private function discount_at_its_cap() {
		$discount = $this->create_discount(
			array(
				'max_uses'  => 1,
				'use_count' => 1,
			)
		);

		$this->assertSame( 'active', $discount->status );
		$this->assertTrue( $discount->is_maxed_out( false ) );

		return $discount;
	}

	/**
	 * Creates a discount whose last use is taken by another order while this order is being built.
	 *
	 * @return \EDD_Discount
	 */
	private function discount_used_up_while_the_order_is_built() {
		$discount = $this->create_discount( array( 'max_uses' => 1 ) );

		add_action(
			'edd_order_added',
			function () use ( $discount ) {
				global $wpdb;
				$wpdb->update( $wpdb->edd_adjustments, array( 'use_count' => 1 ), array( 'id' => $discount->id ) );
			}
		);

		return $discount;
	}

	/**
	 * Fills the cart, applies a discount to it, and posts the fields the checkout form sends.
	 *
	 * @param \EDD_Discount $discount The discount.
	 * @return void
	 */
	private function checkout_with_cart_discount( $discount ) {
		edd_add_to_cart( $this->create_ajax_download( 20.00 ) );
		edd_set_cart_discount( $discount->code );

		$this->post(
			array(
				'edd_email' => 'buyer@edd.test',
				'edd_first' => 'Pat',
				'edd_last'  => 'Buyer',
			)
		);

		$this->assertSame( array( $discount->code ), edd_get_cart_discounts() );
	}

	/**
	 * Builds a JSON REST request.
	 *
	 * @param array $body The request body.
	 * @return \WP_REST_Request
	 */
	private function rest_request( $body ) {
		$request = new \WP_REST_Request( 'POST' );
		$request->set_header( 'Content-Type', 'application/json' );
		$request->set_body( wp_json_encode( $body ) );

		return $request;
	}

	/**
	 * Fails an order from the buyer's own session, as the failed transaction page does, and gives its freed use to another order.
	 *
	 * @param \EDD\Orders\Order $order    The order the buyer's session is paying for.
	 * @param \EDD_Discount      $discount The discount the order holds.
	 * @return \EDD\Orders\Order The other order.
	 */
	private function release_from_the_buyers_session( $order, $discount ) {
		$original_order = EDD()->session->get( 'edd_resume_payment' );
		EDD()->session->set( 'edd_resume_payment', $order->id );
		add_filter( 'edd_is_failure_page', '__return_true' );
		edd_listen_for_failed_payments();
		remove_filter( 'edd_is_failure_page', '__return_true' );
		EDD()->session->set( 'edd_resume_payment', $original_order );

		$other_order = $this->create_order_with_discount(
			$discount,
			array(
				'status'  => 'pending',
				'gateway' => 'paypal_commerce',
				'email'   => 'other-buyer@edd.test',
			)
		);
		\EDD\Discounts\Claims::claim( $other_order );

		return $other_order;
	}

	/**
	 * Creates a pending PayPal order carrying a discount, as the PayPal create step stores it.
	 *
	 * @param \EDD_Discount $discount The discount.
	 * @return \EDD\Orders\Order
	 */
	private function pending_paypal_order_with( $discount ) {
		$order = $this->create_order_with_discount(
			$discount,
			array(
				'status'      => 'pending',
				'gateway'     => 'paypal_commerce',
				'mode'        => 'test',
				'email'       => 'buyer@edd.test',
				'payment_key' => md5( 'paypal-capture' ),
			)
		);
		edd_update_order_meta( $order->id, 'paypal_order_id', self::PAYPAL_ORDER_ID );

		return $order;
	}
}
