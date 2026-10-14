<?php
/**
 * Square checkout processing tests.
 *
 * @package     EDD\Tests\Gateways\Square\Checkout
 * @copyright   Copyright (c) 2026, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.7.1.1
 */

namespace EDD\Tests\Gateways\Square\Checkout;

defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

use EDD\Tests\PHPUnit\Ajax_UnitTestCase;
use EDD\Tests\Discounts\DiscountFixtures;
use EDD\Tests\Helpers\EDD_Helper_Download;
use EDD\Gateways\Square\Helpers\Api;
use EDD\Gateways\Square\Helpers\Customer as CustomerHelper;
use EDD\Gateways\Square\Helpers\Mode;
use EDD\Gateways\Square\Helpers\Setting;
use EDD\Vendor\Square\Apis\OrdersApi;
use EDD\Vendor\Square\Apis\PaymentsApi;
use EDD\Vendor\Square\Http\ApiResponse;
use EDD\Vendor\Square\Models\CreateOrderResponse;
use EDD\Vendor\Square\Models\CreatePaymentResponse;
use EDD\Vendor\Square\Models\Error as SquareError;
use EDD\Vendor\Square\Models\Money;
use EDD\Vendor\Square\Models\Order as SquareOrder;
use EDD\Vendor\Square\Models\Payment as SquarePayment;
use EDD\Vendor\Square\Models\RetrieveOrderResponse;
use EDD\Vendor\Square\Models\UpdateOrderResponse;
use EDD\Vendor\Square\SquareClient;

/**
 * Square checkout processing tests.
 *
 * @since 3.7.1.1
 */
class Process extends Ajax_UnitTestCase {

	use DiscountFixtures;

	/**
	 * The download being purchased.
	 *
	 * @var \WP_Post
	 */
	protected static $download;

	/**
	 * The Square client and singleton in place before the test.
	 *
	 * @var array
	 */
	private $original_api_statics = array();

	/**
	 * Creates the download being purchased.
	 *
	 * @since 3.7.1.1
	 */
	public static function wpSetUpBeforeClass() {
		self::$download = EDD_Helper_Download::create_simple_download();
	}

	/**
	 * Configures a Square location and a customer already linked to Square.
	 *
	 * @since 3.7.1.1
	 */
	public function set_up() {
		parent::set_up();
		$this->forget_cached_claims();

		foreach ( array( 'instance', 'client' ) as $property ) {
			$this->original_api_statics[ $property ] = $this->get_api_static( $property );
		}

		Setting::set( 'location_id', 'TEST_LOCATION' );

		$customer_id = self::edd()->customer->create( array( 'email' => 'square-buyer@edd.local' ) );
		edd_add_customer_meta( $customer_id, CustomerHelper::get_customer_id_meta_key(), 'SQUARE_CUSTOMER', true );
	}

	/**
	 * Restores the Square client and removes the location setting.
	 *
	 * @since 3.7.1.1
	 */
	public function tear_down() {
		foreach ( $this->original_api_statics as $property => $value ) {
			$this->set_api_static( $property, $value );
		}

		edd_delete_option( 'square_' . Mode::get() . '_location_id' );

		parent::tear_down();
	}

	/**
	 * A payment Square reports as pending leaves the order and its transaction pending.
	 *
	 * @since 3.7.1.1
	 */
	public function test_pending_square_payment_leaves_order_pending() {
		$this->stub_square_client( 'PENDING' );
		$completed_purchases = did_action( 'edd_complete_purchase' );

		$response = $this->process_checkout( $this->get_purchase_data() );
		$order    = $this->get_square_order();

		$this->assertTrue( $response['success'], $response['data']['message'] ?? '' );
		$this->assertSame( 'PENDING', $response['data']['square_payment_status'] );
		$this->assertNotNull( $order, 'The checkout did not record an order for the Square order.' );
		$this->assertSame( 'pending', $order->status );
		$this->assertSame( $completed_purchases, did_action( 'edd_complete_purchase' ) );
		$this->assertSame( 'pending', $this->get_transaction_status( $order->id ) );
	}

	/**
	 * A payment Square reports as completed completes the order and its transaction.
	 *
	 * @since 3.7.1.1
	 */
	public function test_completed_square_payment_completes_order() {
		$this->stub_square_client( 'COMPLETED' );
		$completed_purchases = did_action( 'edd_complete_purchase' );

		$response = $this->process_checkout( $this->get_purchase_data() );
		$order    = $this->get_square_order();

		$this->assertTrue( $response['success'], $response['data']['message'] ?? '' );
		$this->assertSame( 'COMPLETED', $response['data']['square_payment_status'] );
		$this->assertNotNull( $order, 'The checkout did not record an order for the Square order.' );
		$this->assertSame( 'complete', $order->status );
		$this->assertSame( $completed_purchases + 1, did_action( 'edd_complete_purchase' ) );
		$this->assertSame( 'complete', $this->get_transaction_status( $order->id ) );
	}

	/**
	 * Square is not asked to take payment when another order takes the discount's last use while this one is built.
	 *
	 * @since 3.7.2
	 */
	public function test_square_payment_is_not_requested_when_the_discount_is_used_up() {
		$payments_api = $this->stub_square_client( 'COMPLETED' );
		$payments_api->expects( $this->never() )->method( 'createPayment' );
		$discount = $this->create_discount( array( 'max_uses' => 1 ) );
		add_action(
			'edd_order_added',
			function () use ( $discount ) {
				global $wpdb;
				$wpdb->update( $wpdb->edd_adjustments, array( 'use_count' => 1 ), array( 'id' => $discount->id ) );
			}
		);

		$response = $this->process_checkout( $this->get_purchase_data_with( $discount ) );

		$orders = edd_get_orders( array( 'email' => 'square-buyer@edd.local' ) );
		$this->assertFalse( $response['success'] );
		$this->assertCount( 1, $orders );
		$this->assertSame( 'failed', $orders[0]->status );
	}

	/**
	 * A refused discount tells the form to reload, so the buyer sees the full total before paying it.
	 *
	 * @since 3.7.2
	 */
	public function test_refused_discount_asks_the_form_to_reload() {
		$this->stub_square_client( 'COMPLETED' );
		$discount = $this->create_discount( array( 'max_uses' => 1 ) );
		add_action(
			'edd_order_added',
			function () use ( $discount ) {
				global $wpdb;
				$wpdb->update( $wpdb->edd_adjustments, array( 'use_count' => 1 ), array( 'id' => $discount->id ) );
			}
		);

		$response = $this->process_checkout( $this->get_purchase_data_with( $discount ) );

		$this->assertFalse( $response['success'] );
		$this->assertTrue( $response['data']['refresh'] );
		$this->assertArrayHasKey( 'edd-discount-error', (array) edd_get_errors() );
	}

	/**
	 * A payment Square declines fails the order, so the discount use it claimed is given back.
	 *
	 * @since 3.7.2
	 */
	public function test_declined_square_payment_gives_back_the_discount_use() {
		$payments_api = $this->stub_square_client( 'FAILED', 'createPayment' );
		$payments_api->expects( $this->once() )->method( 'createPayment' );
		$discount = $this->create_discount( array( 'max_uses' => 1 ) );

		$response = $this->process_checkout( $this->get_purchase_data_with( $discount ) );

		$orders = edd_get_orders( array( 'email' => 'square-buyer@edd.local' ) );
		$this->assertFalse( $response['success'] );
		$this->assertCount( 1, $orders );
		$this->assertSame( 'failed', $orders[0]->status );
		$this->assertSame( 0, (int) edd_get_discount( $discount->id )->use_count );
	}

	/**
	 * An order Square charged stays findable by its webhook and keeps its use when a later Square call fails.
	 *
	 * @since 3.7.2
	 */
	public function test_charged_order_keeps_its_use_when_square_fails_after_payment() {
		$this->stub_square_client( 'COMPLETED', 'retrieveOrder' );
		$discount = $this->create_discount( array( 'max_uses' => 1 ) );

		$response = $this->process_checkout( $this->get_purchase_data_with( $discount ) );
		$order    = $this->get_square_order();

		$this->assertFalse( $response['success'] );
		$this->assertNotNull( $order, 'The charged order has no Square order ID for the webhook to find it by.' );
		$this->assertSame( 'processing', $order->status );
		$this->assertSame( 'pending', $this->get_transaction_status( $order->id ) );
		$this->assertSame( 1, (int) edd_get_discount( $discount->id )->use_count );
	}

	/**
	 * A charge with no answer from Square may have gone through, so its order stays processing where the webhook can find it.
	 *
	 * @since 3.7.2
	 */
	public function test_payment_with_an_unknown_outcome_keeps_the_order_processing() {
		$this->stub_square_client( 'COMPLETED', 'createPayment', true );
		$discount = $this->create_discount( array( 'max_uses' => 1 ) );

		$response = $this->process_checkout( $this->get_purchase_data_with( $discount ) );
		$order    = $this->get_square_order();

		$this->assertFalse( $response['success'] );
		$this->assertNotNull( $order, 'The order has no Square order ID for the webhook to find it by.' );
		$this->assertSame( 'processing', $order->status );
		$this->assertNull( $this->get_transaction_status( $order->id ) );
		$this->assertSame( 1, (int) edd_get_discount( $discount->id )->use_count );
	}

	/**
	 * A buyer whose order Square's webhook completed first still gets a successful checkout.
	 *
	 * @since 3.7.2
	 */
	public function test_order_completed_by_webhook_first_still_succeeds() {
		$this->stub_square_client( 'COMPLETED' );
		$discount = $this->create_discount( array( 'max_uses' => 1 ) );
		add_action(
			'edd_order_transaction_added',
			function ( $transaction_id, $data ) {
				edd_update_order_status( $data['object_id'], 'complete' );
			},
			10,
			2
		);

		$response = $this->process_checkout( $this->get_purchase_data_with( $discount ) );

		$this->assertTrue( $response['success'], $response['data']['message'] ?? '' );
		$this->assertSame( 'complete', $this->get_square_order()->status );
		$this->assertSame( 1, (int) edd_get_discount( $discount->id )->use_count );
	}

	/**
	 * An order is held out of pending while Square charges it, so a second submit can't resume it and charge it again.
	 *
	 * @since 3.7.2
	 */
	public function test_order_is_held_in_processing_while_square_charges_it() {
		$payments_api = $this->stub_square_client( 'COMPLETED' );
		$statuses     = array();
		// The stub's own matcher supplies the payment; this one only reads the order mid-charge.
		$payments_api->expects( $this->once() )->method( 'createPayment' )->willReturnCallback(
			function () use ( &$statuses ) {
				$orders     = edd_get_orders( array( 'email' => 'square-buyer@edd.local' ) );
				$statuses[] = $orders[0]->status;
			}
		);

		$response = $this->process_checkout( $this->get_purchase_data() );

		$this->assertTrue( $response['success'], $response['data']['message'] ?? '' );
		$this->assertSame( array( 'processing' ), $statuses );
		$this->assertSame( 'complete', $this->get_square_order()->status );
	}

	/**
	 * Purchase data that skipped Square's checkout validation is refused before Square is charged.
	 *
	 * @since 3.7.1.1
	 *
	 * @dataProvider data_gateway_nonce_not_from_checkout
	 *
	 * @param string|null $gateway_nonce The gateway nonce, or null to leave it out.
	 */
	public function test_process_requires_gateway_nonce( $gateway_nonce ) {
		$payments_api = $this->stub_square_client( 'COMPLETED' );
		$payments_api->expects( $this->never() )->method( 'createPayment' );

		$purchase_data = $this->get_purchase_data();
		if ( is_null( $gateway_nonce ) ) {
			unset( $purchase_data['gateway_nonce'] );
		} else {
			$purchase_data['gateway_nonce'] = $gateway_nonce;
		}

		$response = $this->process_checkout( $purchase_data );

		$this->assertFalse( $response['success'] );
		$this->assertNull( $this->get_square_order(), 'An order was recorded for purchase data that skipped checkout validation.' );
	}

	/**
	 * Gateway nonces that did not come from checkout validation.
	 *
	 * @since 3.7.1.1
	 *
	 * @return array
	 */
	public function data_gateway_nonce_not_from_checkout() {
		return array(
			'missing'                  => array( null ),
			'created for other action' => array( wp_create_nonce( 'edd-other-action' ) ),
		);
	}

	/**
	 * Fires the Square gateway hook and returns the decoded JSON response.
	 *
	 * @since 3.7.1.1
	 *
	 * @param array $purchase_data The purchase data handed to the gateway.
	 * @return array
	 */
	private function process_checkout( $purchase_data ) {
		$this->assertTrue( has_action( 'edd_gateway_square' ), 'The Square gateway is not hooked.' );

		$this->_last_response = '';
		$level                = ob_get_level();
		ob_start();
		try {
			do_action( 'edd_gateway_square', $purchase_data );
		} catch ( \WPAjaxDieContinueException $e ) {
			// Every response ends the request through wp_die().
		}

		if ( ob_get_level() > $level ) {
			$this->_last_response .= ob_get_clean();
		}

		$this->assertJson( $this->_last_response );

		return json_decode( $this->_last_response, true );
	}

	/**
	 * Replaces the Square client with one whose payment comes back in the given status.
	 *
	 * @since 3.7.1.1
	 *
	 * @since 3.7.2 Can fail one Square call.
	 *
	 * @param string $payment_status The Square payment status.
	 * @param string $failing_call   The Square API method that returns an error, if any.
	 * @param bool   $times_out      Whether the failing call throws, as a timeout does, instead of answering with an error.
	 * @return \PHPUnit\Framework\MockObject\MockObject The payments API mock.
	 */
	private function stub_square_client( $payment_status, $failing_call = '', $times_out = false ) {
		$total = new Money();
		$total->setAmount( 2000 );
		$total->setCurrency( 'USD' );

		$square_order = new SquareOrder( 'TEST_LOCATION' );
		$square_order->setId( 'SQUARE_ORDER' );
		$square_order->setCustomerId( 'SQUARE_CUSTOMER' );
		$square_order->setVersion( 1 );
		$square_order->setTotalMoney( $total );

		$payment = new SquarePayment();
		$payment->setId( 'SQUARE_PAYMENT' );
		$payment->setStatus( $payment_status );
		$payment->setAmountMoney( $total );
		$payment->setOrderId( 'SQUARE_ORDER' );

		$create_order_result = new CreateOrderResponse();
		$create_order_result->setOrder( $square_order );

		$retrieve_order_result = new RetrieveOrderResponse();
		$retrieve_order_result->setOrder( $square_order );

		$create_payment_result = new CreatePaymentResponse();
		$create_payment_result->setPayment( $payment );

		$orders_api = $this->createMock( OrdersApi::class );
		$orders_api->method( 'createOrder' )->willReturn( $this->get_successful_response( $create_order_result ) );
		$orders_api->method( 'retrieveOrder' )->willReturn(
			'retrieveOrder' === $failing_call ? $this->get_error_response() : $this->get_successful_response( $retrieve_order_result )
		);

		$update_order_result = new UpdateOrderResponse();
		$update_order_result->setOrder( $square_order );
		$orders_api->method( 'updateOrder' )->willReturn( $this->get_successful_response( $update_order_result ) );

		$payments_api = $this->createMock( PaymentsApi::class );
		if ( 'createPayment' === $failing_call && $times_out ) {
			$payments_api->method( 'createPayment' )->willThrowException( new \RuntimeException( 'Operation timed out' ) );
		} else {
			$payments_api->method( 'createPayment' )->willReturn(
				'createPayment' === $failing_call ? $this->get_error_response() : $this->get_successful_response( $create_payment_result )
			);
		}

		$client = $this->createMock( SquareClient::class );
		$client->method( 'getOrdersApi' )->willReturn( $orders_api );
		$client->method( 'getPaymentsApi' )->willReturn( $payments_api );

		$api_reflection = new \ReflectionClass( Api::class );
		$this->set_api_static( 'instance', $api_reflection->newInstanceWithoutConstructor() );
		$this->set_api_static( 'client', $client );

		return $payments_api;
	}

	/**
	 * Builds a successful Square API response wrapping the given result.
	 *
	 * @since 3.7.1.1
	 *
	 * @param object $result The response result.
	 * @return ApiResponse
	 */
	private function get_successful_response( $result ) {
		$response = $this->createMock( ApiResponse::class );
		$response->method( 'isSuccess' )->willReturn( true );
		$response->method( 'getResult' )->willReturn( $result );

		return $response;
	}

	/**
	 * Builds a Square API response carrying an error.
	 *
	 * @since 3.7.2
	 *
	 * @return ApiResponse
	 */
	private function get_error_response() {
		$response = $this->createMock( ApiResponse::class );
		$response->method( 'isSuccess' )->willReturn( false );
		$response->method( 'getErrors' )->willReturn( array( new SquareError( 'PAYMENT_METHOD_ERROR', 'CARD_DECLINED' ) ) );

		return $response;
	}

	/**
	 * Gets the EDD order carrying the stubbed Square order ID.
	 *
	 * @since 3.7.1.1
	 *
	 * @return \EDD\Orders\Order|null
	 */
	private function get_square_order() {
		$orders = edd_get_orders(
			array(
				'meta_query' => array(
					array(
						'key'   => 'square_order_id',
						'value' => 'SQUARE_ORDER',
					),
				),
			)
		);

		return $orders ? reset( $orders ) : null;
	}

	/**
	 * Gets the status of the order's Square transaction.
	 *
	 * @since 3.7.1.1
	 *
	 * @param int $order_id The order ID.
	 * @return string|null
	 */
	private function get_transaction_status( $order_id ) {
		$transactions = edd_get_order_transactions(
			array(
				'object_id'      => $order_id,
				'transaction_id' => 'SQUARE_PAYMENT',
			)
		);

		return $transactions ? reset( $transactions )->status : null;
	}

	/**
	 * Gets purchase data for a single download bought through Square.
	 *
	 * @since 3.7.1.1
	 *
	 * @return array
	 */
	private function get_purchase_data() {
		return array(
			'gateway_nonce' => wp_create_nonce( 'edd-gateway' ),
			'fees'          => array(),
			'subtotal'      => 20,
			'discount'      => 0,
			'tax'           => 0,
			'tax_rate'      => 0,
			'price'         => 20,
			'date'          => false,
			'user_email'    => 'square-buyer@edd.local',
			'purchase_key'  => md5( uniqid( 'square', true ) ),
			'currency'      => 'USD',
			'gateway'       => 'square',
			'source_id'     => 'TEST_SOURCE',
			'downloads'     => array(
				array(
					'id'       => self::$download->ID,
					'options'  => array(),
					'quantity' => 1,
				),
			),
			'user_info'     => array(
				'id'         => 0,
				'email'      => 'square-buyer@edd.local',
				'first_name' => 'Square',
				'last_name'  => 'Buyer',
				'discount'   => 'none',
				'address'    => array(),
			),
			'cart_details'  => array(
				array(
					'name'        => edd_get_download_name( self::$download->ID ),
					'id'          => self::$download->ID,
					'item_number' => array(
						'id'       => self::$download->ID,
						'options'  => array(),
						'quantity' => 1,
					),
					'item_price'  => 20,
					'quantity'    => 1,
					'discount'    => 0,
					'subtotal'    => 20,
					'tax'         => 0,
					'fees'        => array(),
					'price'       => 20,
				),
			),
		);
	}

	/**
	 * Gets purchase data for the download bought through Square with a discount applied.
	 *
	 * @since 3.7.2
	 *
	 * @param \EDD_Discount $discount The discount.
	 * @return array
	 */
	private function get_purchase_data_with( $discount ) {
		$purchase_data                                = $this->get_purchase_data();
		$purchase_data['discount']                    = 18;
		$purchase_data['price']                       = 2;
		$purchase_data['user_info']['discount']       = $discount->code;
		$purchase_data['cart_details'][0]['discount'] = 18;
		$purchase_data['cart_details'][0]['price']    = 2;

		return $purchase_data;
	}

	/**
	 * Gets a private static property of the Square API helper.
	 *
	 * @since 3.7.1.1
	 *
	 * @param string $property The property name.
	 * @return mixed
	 */
	private function get_api_static( $property ) {
		$reflection = new \ReflectionProperty( Api::class, $property );
		$reflection->setAccessible( true );

		return $reflection->getValue();
	}

	/**
	 * Sets a private static property of the Square API helper.
	 *
	 * @since 3.7.1.1
	 *
	 * @param string $property The property name.
	 * @param mixed  $value    The value.
	 */
	private function set_api_static( $property, $value ) {
		$reflection = new \ReflectionProperty( Api::class, $property );
		$reflection->setAccessible( true );
		$reflection->setValue( null, $value );
	}
}
