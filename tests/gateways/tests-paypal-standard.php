<?php
/**
 * PayPal Standard Gateway Tests
 *
 * @package   easy-digital-downloads
 * @copyright Copyright (c) 2024, Sandhills Development, LLC
 * @license   GPL2+
 * @since     3.6.1
 */

namespace EDD\Tests\Gateways;

use EDD\Tests\Helpers;
use EDD\Tests\PHPUnit\EDD_UnitTestCase;

/**
 * Tests for PayPal Standard gateway functionality.
 *
 * @group gateways
 * @group paypal-standard
 */
class PayPalStandard extends EDD_UnitTestCase {

	/**
	 * Test order ID.
	 *
	 * @var int
	 */
	protected $order_id;

	/**
	 * Test order object.
	 *
	 * @var \EDD\Orders\Order
	 */
	protected $order;

	/**
	 * Set up test fixtures.
	 */
	public function setUp(): void {
		parent::setUp();

		// Create a test order.
		$this->order_id = Helpers\EDD_Helper_Payment::create_simple_payment();
		$this->order    = edd_get_order( $this->order_id );

		// Clear any existing session.
		edd_set_purchase_session( array() );
	}

	/**
	 * Clean up after tests.
	 */
	public function tearDown(): void {
		// Clear session.
		edd_set_purchase_session( array() );

		// Clear GET parameters.
		unset( $_GET['payment-id'], $_GET['payment-confirmation'] );

		parent::tearDown();
	}

	/**
	 * Test that session is restored for completed orders when session is empty.
	 *
	 * This is the main fix for issue #2052 - ensuring completed orders
	 * can retrieve receipts even when the session was lost.
	 *
	 * @covers ::edd_paypal_success_page_content()
	 */
	public function test_session_restored_for_completed_order_when_session_empty() {
		// Set order status to complete.
		edd_update_order_status( $this->order_id, 'complete' );
		$this->order = edd_get_order( $this->order_id );

		// Ensure session is empty.
		edd_set_purchase_session( array() );

		// Set GET parameters to simulate PayPal return.
		$_GET['payment-id']            = $this->order_id;
		$_GET['payment-confirmation']   = 'paypal';

		// Call the function
		$result = edd_paypal_success_page_content( 'Test content' );

		// Verify session was restored
		$session = edd_get_purchase_session();
		$this->assertNotEmpty( $session, 'Session should not be empty. Session value: ' . var_export( $session, true ) );
		$this->assertArrayHasKey( 'purchase_key', $session );
		$this->assertEquals( $this->order->payment_key, $session['purchase_key'] );

		// Verify content is returned (allows receipt shortcode to work).
		$this->assertEquals( 'Test content', $result );
	}

	/**
	 * Test that session is restored for completed orders when session doesn't match.
	 *
	 * @covers ::edd_paypal_success_page_content()
	 */
	public function test_session_restored_for_completed_order_when_session_mismatch() {
		// Set order status to complete.
		edd_update_order_status( $this->order_id, 'complete' );
		$this->order = edd_get_order( $this->order_id );

		// Set session with different purchase key.
		edd_set_purchase_session( array(
			'purchase_key' => 'wrong-key-12345',
		) );

		// Set GET parameters.
		$_GET['payment-id']            = $this->order_id;
		$_GET['payment-confirmation']   = 'paypal';

		// Call the function.
		$result = edd_paypal_success_page_content( 'Test content' );

		// Verify session was restored with correct key.
		$session = edd_get_purchase_session();
		$this->assertNotEmpty( $session );
		$this->assertEquals( $this->order->payment_key, $session['purchase_key'] );

		// Verify content is returned
		$this->assertEquals( 'Test content', $result );
	}

	/**
	 * Test that session is NOT restored when it already matches the order.
	 *
	 * @covers ::edd_paypal_success_page_content()
	 */
	public function test_session_not_restored_when_already_matches() {
		// Set order status to complete.
		edd_update_order_status( $this->order_id, 'complete' );
		$this->order = edd_get_order( $this->order_id );

		// Set session with correct purchase key.
		$original_session = array(
			'purchase_key' => $this->order->payment_key,
		);
		edd_set_purchase_session( $original_session );

		// Set GET parameters.
		$_GET['payment-id']            = $this->order_id;
		$_GET['payment-confirmation']   = 'paypal';

		// Call the function.
		$result = edd_paypal_success_page_content( 'Test content' );

		// Verify session still matches (wasn't unnecessarily overwritten).
		$session = edd_get_purchase_session();
		$this->assertEquals( $this->order->payment_key, $session['purchase_key'] );

		// Verify content is returned.
		$this->assertEquals( 'Test content', $result );
	}

	/**
	 * Test that pending orders still show processing indicator.
	 *
	 * This verifies existing behavior is maintained.
	 *
	 * @covers ::edd_paypal_success_page_content()
	 */
	public function test_pending_order_shows_processing_indicator() {
		// Ensure order is pending.
		edd_update_order_status( $this->order_id, 'pending' );
		$this->order = edd_get_order( $this->order_id );

		// Set session.
		edd_set_purchase_session( array(
			'purchase_key' => $this->order->payment_key,
		) );

		// Set GET parameters.
		$_GET['payment-id']            = $this->order_id;
		$_GET['payment-confirmation']   = 'paypal';

		// Call the function.
		$result = edd_paypal_success_page_content( 'Test content' );

		// Verify processing template is returned (not the original content).
		$this->assertNotEquals( 'Test content', $result );
		$this->assertNotEmpty( $result );
	}

	/**
	 * Test that session is restored for pending orders when session is empty.
	 *
	 * This verifies session restoration works for pending orders too.
	 *
	 * @covers ::edd_paypal_success_page_content()
	 */
	public function test_session_restored_for_pending_order_when_session_empty() {
		// Ensure order is pending.
		edd_update_order_status( $this->order_id, 'pending' );
		$this->order = edd_get_order( $this->order_id );

		// Ensure session is empty.
		edd_set_purchase_session( array() );

		// Set GET parameters.
		$_GET['payment-id']            = $this->order_id;
		$_GET['payment-confirmation']   = 'paypal';

		// Call the function.
		$result = edd_paypal_success_page_content( 'Test content' );

		// Verify session was restored.
		$session = edd_get_purchase_session();
		$this->assertNotEmpty( $session );
		$this->assertEquals( $this->order->payment_key, $session['purchase_key'] );

		// Verify processing template is returned.
		$this->assertNotEquals( 'Test content', $result );
	}

	/**
	 * Test that function returns content when order doesn't exist.
	 *
	 * @covers ::edd_paypal_success_page_content()
	 */
	public function test_returns_content_when_order_not_found() {
		// Set GET parameters with non-existent order ID.
		$_GET['payment-id']            = 99999;
		$_GET['payment-confirmation']   = 'paypal';

		// Call the function.
		$result = edd_paypal_success_page_content( 'Test content' );

		// Verify original content is returned.
		$this->assertEquals( 'Test content', $result );

		// Verify session was not modified.
		$session = edd_get_purchase_session();
		$this->assertEmpty( $session );
	}

	/**
	 * Test that function returns content when payment-confirmation is not 'paypal'.
	 *
	 * @covers ::edd_paypal_success_page_content()
	 */
	public function test_returns_content_when_payment_confirmation_not_paypal() {
		// Set order status to complete.
		edd_update_order_status( $this->order_id, 'complete' );
		$this->order = edd_get_order( $this->order_id );

		// Set GET parameters with wrong confirmation.
		$_GET['payment-id']            = $this->order_id;
		$_GET['payment-confirmation']   = 'stripe';

		// Call the function.
		$result = edd_paypal_success_page_content( 'Test content' );

		// Verify original content is returned.
		$this->assertEquals( 'Test content', $result );

		// Verify session was not modified.
		$session = edd_get_purchase_session();
		$this->assertEmpty( $session );
	}

	/**
	 * Test that function uses session purchase_key when payment-id is not provided.
	 *
	 * @covers ::edd_paypal_success_page_content()
	 */
	public function test_uses_session_purchase_key_when_payment_id_missing() {
		// Set order status to complete.
		edd_update_order_status( $this->order_id, 'complete' );
		$this->order = edd_get_order( $this->order_id );

		// Set session with purchase key (but no payment-id in GET).
		edd_set_purchase_session( array(
			'purchase_key' => $this->order->payment_key,
		) );

		// Set only payment-confirmation (no payment-id).
		$_GET['payment-confirmation'] = 'paypal';

		// Call the function.
		$result = edd_paypal_success_page_content( 'Test content' );

		// Verify session was maintained.
		$session = edd_get_purchase_session();
		$this->assertEquals( $this->order->payment_key, $session['purchase_key'] );

		// Verify content is returned.
		$this->assertEquals( 'Test content', $result );
	}

	/**
	 * Test that function returns content when both order_id and session are missing.
	 *
	 * @covers ::edd_paypal_success_page_content()
	 */
	public function test_returns_content_when_no_order_id_and_no_session() {
		// Ensure session is empty.
		edd_set_purchase_session( array() );

		// Set only payment-confirmation (no payment-id, no session).
		$_GET['payment-confirmation'] = 'paypal';

		// Call the function.
		$result = edd_paypal_success_page_content( 'Test content' );

		// Verify original content is returned.
		$this->assertEquals( 'Test content', $result );
	}
}

