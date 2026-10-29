<?php
/**
 * Tests for the Stripe checkout completion request.
 *
 * @package     EDD\Tests\Stripe
 * @copyright   Copyright (c) 2026, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.7.3
 */

namespace EDD\Tests\Stripe;

defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

use EDD\Tests\PHPUnit\Ajax_UnitTestCase;
use EDD\Vendor\Stripe\ApiRequestor;
use EDD\Vendor\Stripe\HttpClient\ClientInterface;

/**
 * Checkout Complete process() tests.
 *
 * @since 3.7.3
 *
 * @coversDefaultClass \EDD\Gateways\Stripe\Checkout\Complete
 */
class CheckoutCompleteProcess extends Ajax_UnitTestCase {

	/**
	 * Pending Stripe order the intent belongs to.
	 *
	 * @var int
	 */
	private $pending_order_id;

	/**
	 * The secret key option name for the current mode.
	 *
	 * @var string
	 */
	private $secret_key_option;

	/**
	 * The secret key in place before the test.
	 *
	 * @var mixed
	 */
	private $original_secret_key;

	/**
	 * Creates a pending Stripe order and gives the API a key to send.
	 *
	 * @since 3.7.3
	 */
	public function set_up() {
		parent::set_up();

		$this->pending_order_id = parent::edd()->order->create(
			array(
				'status'  => 'pending',
				'gateway' => 'stripe',
				'total'   => 120,
			)
		);

		$this->secret_key_option   = ( edd_is_test_mode() ? 'test' : 'live' ) . '_secret_key';
		$this->original_secret_key = edd_get_option( $this->secret_key_option, false );
		edd_update_option( $this->secret_key_option, 'sk_test_stub' );
	}

	/**
	 * Restores the Stripe HTTP client, the secret key, and the card error count.
	 *
	 * @since 3.7.3
	 */
	public function tear_down() {
		ApiRequestor::setHttpClient( null );

		if ( false === $this->original_secret_key ) {
			edd_delete_option( $this->secret_key_option );
		} else {
			edd_update_option( $this->secret_key_option, $this->original_secret_key );
		}

		edd_stripe()->rate_limiting->remove_log_entry( edd_stripe()->rate_limiting->get_card_error_id() );

		parent::tear_down();
	}

	/**
	 * A delayed method such as SEPA reports processing, and the charge.succeeded webhook completes the order later.
	 *
	 * @since 3.7.3
	 *
	 * @covers ::process
	 */
	public function test_processing_payment_intent_succeeds_and_leaves_the_order_pending() {
		$completed_orders = did_action( 'edds_order_complete' );

		$response = $this->complete_order( 'payment_intent', 'processing' );

		$this->assertTrue( $response['success'], $response['data']['message'] ?? '' );
		$this->assertSame( 'pending', $response['data']['status'] );
		$this->assertSame( 'pending', edd_get_order( $this->pending_order_id )->status );
		$this->assertSame( $completed_orders, did_action( 'edds_order_complete' ) );
	}

	/**
	 * A SetupIntent has no charge, so accepting a processing one would leave the order pending for good.
	 *
	 * @since 3.7.3
	 *
	 * @covers ::process
	 */
	public function test_processing_setup_intent_is_rejected() {
		$response = $this->complete_order( 'setup_intent', 'processing' );

		$this->assertFalse( $response['success'] );
		$this->assertSame( 'pending', edd_get_order( $this->pending_order_id )->status );
	}

	/**
	 * Dispatches the completion request for an intent Stripe reports in the given status.
	 *
	 * @since 3.7.3
	 *
	 * @param string $intent_object The Stripe object type, `payment_intent` or `setup_intent`.
	 * @param string $intent_status The intent status Stripe returns.
	 * @return array The decoded JSON response.
	 */
	private function complete_order( $intent_object, $intent_status ) {
		$this->assertTrue( has_action( 'wp_ajax_edds_create_and_complete_order' ), 'The Payment Elements completion action is not hooked.' );

		$is_setup_intent = 'setup_intent' === $intent_object;
		$intent_id       = $is_setup_intent ? 'seti_test' : 'pi_test';
		$intent          = array(
			'id'       => $intent_id,
			'object'   => $intent_object,
			'status'   => $intent_status,
			'metadata' => array(
				'edd_payment_id' => (string) $this->pending_order_id,
			),
		);
		if ( ! $is_setup_intent ) {
			$intent['amount']   = 12000;
			$intent['currency'] = 'usd';
		}
		ApiRequestor::setHttpClient( $this->get_stub_client( $intent ) );

		$_POST['intent_type']                = $is_setup_intent ? 'SetupIntent' : 'PaymentIntent';
		$_POST['intent_id']                  = $intent_id;
		$_POST['edd-process-checkout-nonce'] = wp_create_nonce( 'edd-process-checkout' );
		$this->_handleAjax( 'edds_create_and_complete_order' );

		$this->assertJson( $this->_last_response );

		return json_decode( $this->_last_response, true );
	}

	/**
	 * Builds a Stripe HTTP client that answers every request with the given object.
	 *
	 * @since 3.7.3
	 *
	 * @param array $stripe_object The object to return as the response body.
	 * @return ClientInterface
	 */
	private function get_stub_client( array $stripe_object ) {
		return new class( $stripe_object ) implements ClientInterface {

			/**
			 * The object returned as the response body.
			 *
			 * @var array
			 */
			private $stripe_object;

			/**
			 * Stores the object to return.
			 *
			 * @param array $stripe_object The object to return as the response body.
			 */
			public function __construct( array $stripe_object ) {
				$this->stripe_object = $stripe_object;
			}

			/**
			 * Returns the stored object as a successful response.
			 *
			 * @param string $method   The HTTP method.
			 * @param string $abs_url  The requested URL.
			 * @param array  $headers  The request headers.
			 * @param array  $params   The request parameters.
			 * @param bool   $has_file Whether the request carries a file.
			 * @return array
			 */
			public function request( $method, $abs_url, $headers, $params, $has_file ) {
				return array( wp_json_encode( $this->stripe_object ), 200, array() );
			}
		};
	}
}
