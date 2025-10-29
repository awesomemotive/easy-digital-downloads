<?php
/**
 * Tests for User Verification Resend Functionality
 *
 * @group edd_users
 * @group edd_verification
 */

namespace EDD\Tests\Users;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;
use EDD\Users\Verification as UserVerification;

class Verification extends EDD_UnitTestCase {

	/**
	 * User Verification instance.
	 *
	 * @var UserVerification
	 */
	protected $verification;

	/**
	 * Test user ID.
	 *
	 * @var int
	 */
	protected $user_id;

	/**
	 * Test customer.
	 *
	 * @var \EDD_Customer
	 */
	protected $customer;

	/**
	 * Last AJAX response.
	 *
	 * @var string
	 */
	protected $_last_response = '';

	/**
	 * Set up each test.
	 */
	public function setUp(): void {
		parent::setUp();

		$this->verification = new UserVerification();

		// Create a test user with pending verification.
		$this->user_id = wp_insert_user(
			array(
				'user_login' => 'test_verification_user',
				'user_email' => 'test@example.com',
				'user_pass'  => wp_generate_password(),
			)
		);

		// Create associated customer.
		$this->customer = parent::edd()->customer->create_and_get(
			array(
				'email'   => 'test@example.com',
				'user_id' => $this->user_id,
			)
		);

		// Set user as pending verification.
		edd_set_user_to_pending( $this->user_id );

		// Set current user for AJAX tests.
		wp_set_current_user( $this->user_id );

		// Mock AJAX.
		if ( ! defined( 'DOING_AJAX' ) ) {
			define( 'DOING_AJAX', true );
		}

		// Add AJAX die handler to capture responses.
		add_filter( 'wp_die_ajax_handler', array( $this, 'get_die_handler' ), 1, 1 );
	}

	/**
	 * Get AJAX die handler.
	 *
	 * @return callable
	 */
	public function get_die_handler() {
		return array( $this, 'ajax_die_handler' );
	}

	/**
	 * Handle AJAX die calls.
	 *
	 * @param string $message The die message.
	 * @throws \WPAjaxDieContinueException
	 */
	public function ajax_die_handler( $message ) {
		// Capture any output that was buffered.
		if ( ob_get_length() ) {
			$this->_last_response = ob_get_clean();
		} else {
			$this->_last_response = $message;
		}
		throw new \WPAjaxDieContinueException( $message );
	}

	/**
	 * Tear down each test.
	 */
	public function tearDown(): void {
		// Remove die handler.
		remove_filter( 'wp_die_ajax_handler', array( $this, 'get_die_handler' ), 1 );

		// Clean up transients.
		delete_transient( 'edd_verification_resend_cooldown_' . $this->user_id );

		// Clean up user.
		if ( $this->user_id ) {
			wp_delete_user( $this->user_id );
		}

		// Clean up customer.
		if ( $this->customer ) {
			edd_delete_customer( $this->customer->id );
		}

		// Reset POST data.
		$_POST = array();
		$_GET = array();

		parent::tearDown();
	}

	/**
	 * Test that user is properly set as pending verification.
	 */
	public function test_user_pending_verification_status() {
		$this->assertTrue( edd_user_pending_verification( $this->user_id ) );
	}

	/**
	 * Test email masking functionality.
	 */
	public function test_mask_email() {
		$reflection = new \ReflectionClass( $this->verification );
		$method     = $reflection->getMethod( 'mask_email' );
		$method->setAccessible( true );

		// Test standard email.
		$masked = $method->invoke( $this->verification, 'chris@awesomemotive.com' );
		$this->assertEquals( 'c*****@awesomemotive.com', $masked );

		// Test short email.
		$masked = $method->invoke( $this->verification, 'a@test.com' );
		$this->assertEquals( 'a*****@test.com', $masked );

		// Test invalid email.
		$masked = $method->invoke( $this->verification, 'not-an-email' );
		$this->assertEquals( 'not-an-email', $masked );

		// Test empty email.
		$masked = $method->invoke( $this->verification, '' );
		$this->assertEquals( '', $masked );
	}

	/**
	 * Test cooldown period enforcement.
	 */
	public function test_cooldown_period() {
		$reflection = new \ReflectionClass( $this->verification );
		$method     = $reflection->getMethod( 'check_rate_limits' );
		$method->setAccessible( true );

		// First check should pass.
		$result = $method->invoke( $this->verification, $this->user_id );
		$this->assertTrue( $result );

		// Set cooldown transient.
		set_transient(
			'edd_verification_resend_cooldown_' . $this->user_id,
			time() + 120,
			120
		);

		// Second check should fail with WP_Error.
		$result = $method->invoke( $this->verification, $this->user_id );
		$this->assertInstanceOf( 'WP_Error', $result );
		$this->assertEquals( 'rate_limited', $result->get_error_code() );
	}

	/**
	 * Test max emails limit using LogEmail query.
	 */
	public function test_max_emails_limit() {
		$reflection = new \ReflectionClass( $this->verification );
		$method     = $reflection->getMethod( 'get_verification_email_count' );
		$method->setAccessible( true );

		// Create 5 email log entries.
		$log_email = new \EDD\Database\Queries\LogEmail();
		for ( $i = 0; $i < 5; $i++ ) {
			$log_email->add_item(
				array(
					'email_id'    => 'user_verification',
					'object_id'   => $this->user_id,
					'object_type' => 'user',
					'subject'     => 'Verify your account',
					'email'       => 'test@example.com',
				)
			);
		}

		// Count should be 5.
		$count = $method->invoke( $this->verification, $this->user_id );
		$this->assertEquals( 5, $count );

		// Check rate limits should fail.
		$rate_limit_method = $reflection->getMethod( 'check_rate_limits' );
		$rate_limit_method->setAccessible( true );
		$result = $rate_limit_method->invoke( $this->verification, $this->user_id );

		$this->assertInstanceOf( 'WP_Error', $result );
		$this->assertEquals( 'max_emails_reached', $result->get_error_code() );
	}

	/**
	 * Test email count only includes verification emails.
	 */
	public function test_email_count_filters_by_type() {
		$reflection = new \ReflectionClass( $this->verification );
		$method     = $reflection->getMethod( 'get_verification_email_count' );
		$method->setAccessible( true );

		// Add verification email.
		$log_email = new \EDD\Database\Queries\LogEmail();
		$log_email->add_item(
			array(
				'email_id'    => 'user_verification',
				'object_id'   => $this->user_id,
				'object_type' => 'user',
				'subject'     => 'Verify your account',
				'email'       => 'test@example.com',
			)
		);

		// Add non-verification email (should not be counted).
		$log_email->add_item(
			array(
				'email_id'    => 'order_receipt',
				'object_id'   => $this->user_id,
				'object_type' => 'order',
				'subject'     => 'Your order receipt',
				'email'       => 'test@example.com',
			)
		);

		// Count should be 1 (only verification emails).
		$count = $method->invoke( $this->verification, $this->user_id );
		$this->assertEquals( 1, $count );
	}

	/**
	 * Test AJAX resend with invalid nonce.
	 */
	public function test_ajax_resend_invalid_nonce() {
		$_POST = array(
			'nonce' => 'invalid_nonce',
		);

		$this->_last_response = '';

		// Start output buffering before the AJAX call.
		ob_start();

		try {
			$this->verification->ajax_resend_verification_email();
		} catch ( \WPAjaxDieContinueException $e ) {
			// Expected - wp_send_json_error() uses wp_die().
		}

		// Clean any remaining output.
		if ( ob_get_length() ) {
			ob_end_clean();
		}

		$this->assertNotEmpty( $this->_last_response, 'AJAX response was not captured' );
		$response = json_decode( $this->_last_response, true );
		$this->assertIsArray( $response, 'Response is not valid JSON: ' . $this->_last_response );
		$this->assertArrayHasKey( 'success', $response );
		$this->assertFalse( $response['success'] );
		$this->assertStringContainsString( 'Security', $response['data']['message'] );
	}

	/**
	 * Test AJAX resend when user is not logged in.
	 */
	public function test_ajax_resend_not_logged_in() {
		wp_set_current_user( 0 );

		$_POST = array(
			'nonce' => wp_create_nonce( 'edd-verification-resend' ),
		);

		$this->_last_response = '';

		// Start output buffering before the AJAX call.
		ob_start();

		try {
			$this->verification->ajax_resend_verification_email();
		} catch ( \WPAjaxDieContinueException $e ) {
			// Expected - wp_send_json_error() uses wp_die().
		}

		// Clean any remaining output.
		if ( ob_get_length() ) {
			ob_end_clean();
		}

		$this->assertNotEmpty( $this->_last_response, 'AJAX response was not captured' );
		$response = json_decode( $this->_last_response, true );
		$this->assertIsArray( $response, 'Response is not valid JSON: ' . $this->_last_response );
		$this->assertFalse( $response['success'] );
		$this->assertStringContainsString( 'logged in', $response['data']['message'] );
	}

	/**
	 * Test AJAX resend when user is already verified.
	 */
	public function test_ajax_resend_already_verified() {
		// Remove pending verification status.
		delete_user_meta( $this->user_id, '_edd_pending_verification' );

		$_POST = array(
			'nonce' => wp_create_nonce( 'edd-verification-resend' ),
		);

		$this->_last_response = '';

		// Start output buffering before the AJAX call.
		ob_start();

		try {
			$this->verification->ajax_resend_verification_email();
		} catch ( \WPAjaxDieContinueException $e ) {
			// Expected - wp_send_json_error() uses wp_die().
		}

		// Clean any remaining output.
		if ( ob_get_length() ) {
			ob_end_clean();
		}

		$this->assertNotEmpty( $this->_last_response, 'AJAX response was not captured' );
		$response = json_decode( $this->_last_response, true );
		$this->assertIsArray( $response, 'Response is not valid JSON: ' . $this->_last_response );
		$this->assertFalse( $response['success'] );
		$this->assertStringContainsString( 'already verified', $response['data']['message'] );
	}

	/**
	 * Test AJAX verification status check.
	 */
	public function test_ajax_check_verification_status_verified() {
		// Remove pending verification status.
		delete_user_meta( $this->user_id, '_edd_pending_verification' );

		$_POST = array(
			'nonce' => wp_create_nonce( 'edd-verification-resend' ),
		);

		$this->_last_response = '';

		// Start output buffering before the AJAX call.
		ob_start();

		try {
			$this->verification->ajax_check_verification_status();
		} catch ( \WPAjaxDieContinueException $e ) {
			// Expected - wp_send_json_success() uses wp_die().
		}

		// Clean any remaining output.
		if ( ob_get_length() ) {
			ob_end_clean();
		}

		$this->assertNotEmpty( $this->_last_response, 'AJAX response was not captured' );
		$response = json_decode( $this->_last_response, true );
		$this->assertIsArray( $response, 'Response is not valid JSON: ' . $this->_last_response );
		$this->assertTrue( $response['success'] );
		$this->assertTrue( $response['data']['is_verified'] );
	}

	/**
	 * Test AJAX verification status check for pending user.
	 */
	public function test_ajax_check_verification_status_pending() {
		$_POST = array(
			'nonce' => wp_create_nonce( 'edd-verification-resend' ),
		);

		$this->_last_response = '';

		// Start output buffering before the AJAX call.
		ob_start();

		try {
			$this->verification->ajax_check_verification_status();
		} catch ( \WPAjaxDieContinueException $e ) {
			// Expected - wp_send_json_success() uses wp_die().
		}

		// Clean any remaining output.
		if ( ob_get_length() ) {
			ob_end_clean();
		}

		$this->assertNotEmpty( $this->_last_response, 'AJAX response was not captured' );
		$response = json_decode( $this->_last_response, true );
		$this->assertIsArray( $response, 'Response is not valid JSON: ' . $this->_last_response );
		$this->assertTrue( $response['success'] );
		$this->assertEmpty( $response['data']['is_verified'] );
	}

	/**
	 * Test admin resend verifies nonce.
	 */
	public function test_admin_resend_requires_valid_nonce() {
		// Set admin user.
		$admin_id = wp_insert_user(
			array(
				'user_login' => 'admin_test',
				'user_email' => 'admin@example.com',
				'user_pass'  => wp_generate_password(),
				'role'       => 'administrator',
			)
		);
		wp_set_current_user( $admin_id );

		$_GET = array(
			'id'       => $this->customer->id,
			'_wpnonce' => 'invalid_nonce',
		);

		// wp_die will be called by the nonce check in wp_verify_nonce.
		$this->expectException( \WPAjaxDieContinueException::class );
		$this->verification->process_admin_verification_resend();

		// Cleanup admin user.
		wp_delete_user( $admin_id );
	}

	/**
	 * Test subscribed events are registered.
	 */
	public function test_subscribed_events() {
		$events = UserVerification::get_subscribed_events();

		$this->assertArrayHasKey( 'wp_ajax_edd_resend_verification_email', $events );
		$this->assertArrayHasKey( 'wp_ajax_edd_check_verification_status', $events );
		$this->assertArrayHasKey( 'edd_resend_verification_email_admin', $events );
		$this->assertArrayHasKey( 'edd_modal_rendered', $events );
	}

	/**
	 * Test scripts enqueue only for pending verification users.
	 */
	public function test_scripts_enqueue_conditions() {
		// Scripts should enqueue for pending user.
		$this->verification->enqueue_scripts( 'verification' );
		$this->assertTrue( wp_script_is( 'edd-user-verification', 'enqueued' ) );

		// Clean up.
		wp_dequeue_script( 'edd-user-verification' );
		wp_dequeue_script( 'edd-modal' );

		// Remove verification status.
		delete_user_meta( $this->user_id, '_edd_pending_verification' );

		// Scripts should NOT enqueue for verified user.
		$this->verification->enqueue_scripts( 'verification' );
		$this->assertFalse( wp_script_is( 'edd-user-verification', 'enqueued' ) );
	}

	/**
	 * Test localized script data.
	 */
	public function test_localized_script_data() {
		$this->verification->enqueue_scripts( 'verification' );

		$data = wp_scripts()->get_data( 'edd-user-verification', 'data' );
		$this->assertStringContainsString( 'eddVerification', $data );
		$this->assertStringContainsString( 'ajax_url', $data );
		$this->assertStringContainsString( 'nonce', $data );
		$this->assertStringContainsString( 'masked_email', $data );
		$this->assertStringContainsString( 't*****@example.com', $data );
	}

	/**
	 * Test render method outputs button and modal.
	 */
	public function test_render_method() {
		ob_start();
		UserVerification::render();
		$output = ob_get_clean();

		$this->assertStringContainsString( 'id="edd-verification-resend"', $output );
		$this->assertStringContainsString( 'Resend Verification Email', $output );
		$this->assertStringContainsString( 'button', $output );
	}

	/**
	 * Test that constants are properly defined.
	 */
	public function test_constants_defined() {
		$this->assertEquals( 5, UserVerification::MAX_EMAILS );
		$this->assertEquals( 120, UserVerification::COOLDOWN_SECONDS );
	}
}

