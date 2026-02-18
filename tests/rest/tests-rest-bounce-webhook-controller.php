<?php
namespace EDD\Tests\REST;

use EDD\REST\Controllers\BounceWebhook;
use EDD\Tests\PHPUnit\EDD_UnitTestCase;

/**
 * REST Bounce Webhook Controller Tests
 *
 * @group edd_rest
 * @group edd_rest_bounce_webhook
 * @group edd_rest_bounce_webhook_controller
 */
class BounceWebhookController extends EDD_UnitTestCase {

	/**
	 * Controller instance.
	 *
	 * @var BounceWebhook
	 */
	protected $controller;

	/**
	 * Set up before each test.
	 */
	public function setUp(): void {
		parent::setUp();
		$this->controller = new BounceWebhook();
	}

	/**
	 * Test generate_webhook_secret returns a string.
	 *
	 * @covers \EDD\REST\Controllers\BounceWebhook::generate_webhook_secret
	 */
	public function test_generate_webhook_secret_returns_string() {
		$secret = BounceWebhook::generate_webhook_secret();

		$this->assertIsString( $secret );
		$this->assertNotEmpty( $secret );
	}

	/**
	 * Test generate_webhook_secret is deterministic.
	 *
	 * @covers \EDD\REST\Controllers\BounceWebhook::generate_webhook_secret
	 */
	public function test_generate_webhook_secret_is_deterministic() {
		$secret1 = BounceWebhook::generate_webhook_secret();
		$secret2 = BounceWebhook::generate_webhook_secret();

		$this->assertSame( $secret1, $secret2 );
	}

	/**
	 * Test verify_webhook_permission accepts valid secret via header.
	 *
	 * @covers \EDD\REST\Controllers\BounceWebhook::verify_webhook_permission
	 */
	public function test_verify_webhook_permission_valid_header() {
		$secret  = BounceWebhook::generate_webhook_secret();
		$request = new \WP_REST_Request( 'POST', '/edd/v3/webhooks/bounce' );
		$request->set_header( 'X-EDD-Webhook-Secret', $secret );

		$this->assertTrue( $this->controller->verify_webhook_permission( $request ) );
	}

	/**
	 * Test verify_webhook_permission accepts valid secret via query param.
	 *
	 * @covers \EDD\REST\Controllers\BounceWebhook::verify_webhook_permission
	 */
	public function test_verify_webhook_permission_valid_query_param() {
		$secret  = BounceWebhook::generate_webhook_secret();
		$request = new \WP_REST_Request( 'POST', '/edd/v3/webhooks/bounce' );
		$request->set_param( 'secret', $secret );

		$this->assertTrue( $this->controller->verify_webhook_permission( $request ) );
	}

	/**
	 * Test verify_webhook_permission rejects missing secret.
	 *
	 * @covers \EDD\REST\Controllers\BounceWebhook::verify_webhook_permission
	 */
	public function test_verify_webhook_permission_missing_secret() {
		$request = new \WP_REST_Request( 'POST', '/edd/v3/webhooks/bounce' );

		$this->assertFalse( $this->controller->verify_webhook_permission( $request ) );
	}

	/**
	 * Test verify_webhook_permission rejects wrong secret.
	 *
	 * @covers \EDD\REST\Controllers\BounceWebhook::verify_webhook_permission
	 */
	public function test_verify_webhook_permission_wrong_secret() {
		$request = new \WP_REST_Request( 'POST', '/edd/v3/webhooks/bounce' );
		$request->set_header( 'X-EDD-Webhook-Secret', 'wrong-secret-value' );

		$this->assertFalse( $this->controller->verify_webhook_permission( $request ) );
	}

	/**
	 * Test handle_bounce returns 400 for empty payload.
	 *
	 * @covers \EDD\REST\Controllers\BounceWebhook::handle_bounce
	 */
	public function test_handle_bounce_empty_payload() {
		$request = new \WP_REST_Request( 'POST', '/edd/v3/webhooks/bounce' );
		// No JSON body set — get_json_params() returns empty array.

		$response = $this->controller->handle_bounce( $request );

		$this->assertInstanceOf( 'WP_REST_Response', $response );
		$this->assertEquals( 400, $response->get_status() );

		$data = $response->get_data();
		$this->assertArrayHasKey( 'error', $data );
		$this->assertEquals( 'Invalid payload', $data['error'] );
	}

	/**
	 * Test handle_bounce returns 400 for unparseable payload.
	 *
	 * @covers \EDD\REST\Controllers\BounceWebhook::handle_bounce
	 */
	public function test_handle_bounce_unparseable_payload() {
		$request = new \WP_REST_Request( 'POST', '/edd/v3/webhooks/bounce' );
		$request->set_header( 'Content-Type', 'application/json' );
		$request->set_body( wp_json_encode( array( 'unknown_field' => 'unknown_value' ) ) );

		$response = $this->controller->handle_bounce( $request );

		$this->assertInstanceOf( 'WP_REST_Response', $response );
		$this->assertEquals( 400, $response->get_status() );

		$data = $response->get_data();
		$this->assertArrayHasKey( 'error', $data );
		$this->assertEquals( 'Unable to parse bounce data', $data['error'] );
	}

	/**
	 * Test handle_bounce with valid generic payload records bounce and returns 200.
	 *
	 * @covers \EDD\REST\Controllers\BounceWebhook::handle_bounce
	 */
	public function test_handle_bounce_generic_payload() {
		// Create an email log entry.
		$email_log_id = parent::edd()->email_logs->create(
			array(
				'object_id'   => 1,
				'object_type' => 'order',
				'email_id'    => 'order_receipt',
				'subject'     => 'Your receipt',
				'email'       => 'bounce-test@example.com',
			)
		);

		$request = new \WP_REST_Request( 'POST', '/edd/v3/webhooks/bounce' );
		$request->set_header( 'Content-Type', 'application/json' );
		$request->set_body(
			wp_json_encode(
				array(
					'email_id' => $email_log_id,
					'reason'   => 'Mailbox full',
				)
			)
		);

		$response = $this->controller->handle_bounce( $request );

		$this->assertInstanceOf( 'WP_REST_Response', $response );
		$this->assertEquals( 200, $response->get_status() );

		$data = $response->get_data();
		$this->assertTrue( $data['success'] );

		// Verify the bounce metadata was recorded.
		$bounce_reason = get_metadata( 'edd_logs_email', $email_log_id, 'bounce', true );
		$this->assertEquals( 'Mailbox full', $bounce_reason );
	}

	/**
	 * Test handle_bounce with SendGrid format records bounce when email log exists.
	 *
	 * @covers \EDD\REST\Controllers\BounceWebhook::handle_bounce
	 */
	public function test_handle_bounce_sendgrid_format_with_match() {
		$email_log_id = parent::edd()->email_logs->create(
			array(
				'object_id'   => 1,
				'object_type' => 'order',
				'email_id'    => 'order_receipt',
				'subject'     => 'Your receipt',
				'email'       => 'sendgrid-bounce@example.com',
			)
		);

		$request = new \WP_REST_Request( 'POST', '/edd/v3/webhooks/bounce' );
		$request->set_header( 'Content-Type', 'application/json' );
		$request->set_body(
			wp_json_encode(
				array(
					'event'  => 'bounce',
					'email'  => 'sendgrid-bounce@example.com',
					'reason' => '550 User not found',
				)
			)
		);

		$response = $this->controller->handle_bounce( $request );

		$this->assertInstanceOf( 'WP_REST_Response', $response );
		$this->assertEquals( 200, $response->get_status() );

		$bounce_reason = get_metadata( 'edd_logs_email', $email_log_id, 'bounce', true );
		$this->assertEquals( '550 User not found', $bounce_reason );
	}

	/**
	 * Test handle_bounce with Mailgun format records bounce when email log exists.
	 *
	 * @covers \EDD\REST\Controllers\BounceWebhook::handle_bounce
	 */
	public function test_handle_bounce_mailgun_format_with_match() {
		$email_log_id = parent::edd()->email_logs->create(
			array(
				'object_id'   => 1,
				'object_type' => 'order',
				'email_id'    => 'order_receipt',
				'subject'     => 'Your receipt',
				'email'       => 'mailgun-bounce@example.com',
			)
		);

		$request = new \WP_REST_Request( 'POST', '/edd/v3/webhooks/bounce' );
		$request->set_header( 'Content-Type', 'application/json' );
		$request->set_body(
			wp_json_encode(
				array(
					'event'     => 'failed',
					'recipient' => 'mailgun-bounce@example.com',
					'error'     => '550 Mailbox not found',
				)
			)
		);

		$response = $this->controller->handle_bounce( $request );

		$this->assertInstanceOf( 'WP_REST_Response', $response );
		$this->assertEquals( 200, $response->get_status() );

		$bounce_reason = get_metadata( 'edd_logs_email', $email_log_id, 'bounce', true );
		$this->assertEquals( '550 Mailbox not found', $bounce_reason );
	}

	/**
	 * Test handle_bounce with SES format records bounce when email log exists.
	 *
	 * @covers \EDD\REST\Controllers\BounceWebhook::handle_bounce
	 */
	public function test_handle_bounce_ses_format_with_match() {
		$email_log_id = parent::edd()->email_logs->create(
			array(
				'object_id'   => 1,
				'object_type' => 'order',
				'email_id'    => 'order_receipt',
				'subject'     => 'Your receipt',
				'email'       => 'ses-bounce@example.com',
			)
		);

		$request = new \WP_REST_Request( 'POST', '/edd/v3/webhooks/bounce' );
		$request->set_header( 'Content-Type', 'application/json' );
		$request->set_body(
			wp_json_encode(
				array(
					'Type'    => 'Notification',
					'Message' => wp_json_encode(
						array(
							'bounce' => array(
								'bounceType'        => 'Permanent',
								'bouncedRecipients' => array(
									array(
										'emailAddress' => 'ses-bounce@example.com',
									),
								),
							),
						)
					),
				)
			)
		);

		$response = $this->controller->handle_bounce( $request );

		$this->assertInstanceOf( 'WP_REST_Response', $response );
		$this->assertEquals( 200, $response->get_status() );

		$bounce_reason = get_metadata( 'edd_logs_email', $email_log_id, 'bounce', true );
		$this->assertEquals( 'Permanent', $bounce_reason );
	}

	/**
	 * Test handle_bounce with SendGrid format (no matching email returns 400).
	 *
	 * @covers \EDD\REST\Controllers\BounceWebhook::handle_bounce
	 */
	public function test_handle_bounce_sendgrid_format_no_match() {
		$request = new \WP_REST_Request( 'POST', '/edd/v3/webhooks/bounce' );
		$request->set_header( 'Content-Type', 'application/json' );
		$request->set_body(
			wp_json_encode(
				array(
					'event'  => 'bounce',
					'email'  => 'nonexistent@example.com',
					'reason' => '550 User not found',
				)
			)
		);

		$response = $this->controller->handle_bounce( $request );

		$this->assertInstanceOf( 'WP_REST_Response', $response );
		// No matching email in DB, so parse returns null -> 400.
		$this->assertEquals( 400, $response->get_status() );
	}

	/**
	 * Test handle_bounce with Mailgun format (no matching email returns 400).
	 *
	 * @covers \EDD\REST\Controllers\BounceWebhook::handle_bounce
	 */
	public function test_handle_bounce_mailgun_format_no_match() {
		$request = new \WP_REST_Request( 'POST', '/edd/v3/webhooks/bounce' );
		$request->set_header( 'Content-Type', 'application/json' );
		$request->set_body(
			wp_json_encode(
				array(
					'event'     => 'failed',
					'recipient' => 'nonexistent@example.com',
					'error'     => '550 Mailbox not found',
				)
			)
		);

		$response = $this->controller->handle_bounce( $request );

		$this->assertInstanceOf( 'WP_REST_Response', $response );
		$this->assertEquals( 400, $response->get_status() );
	}

	/**
	 * Test handle_bounce with SES format (no matching email returns 400).
	 *
	 * @covers \EDD\REST\Controllers\BounceWebhook::handle_bounce
	 */
	public function test_handle_bounce_ses_format_no_match() {
		$request = new \WP_REST_Request( 'POST', '/edd/v3/webhooks/bounce' );
		$request->set_header( 'Content-Type', 'application/json' );
		$request->set_body(
			wp_json_encode(
				array(
					'Type'    => 'Notification',
					'Message' => wp_json_encode(
						array(
							'bounce' => array(
								'bounceType'       => 'Permanent',
								'bouncedRecipients' => array(
									array(
										'emailAddress' => 'nonexistent@example.com',
									),
								),
							),
						)
					),
				)
			)
		);

		$response = $this->controller->handle_bounce( $request );

		$this->assertInstanceOf( 'WP_REST_Response', $response );
		$this->assertEquals( 400, $response->get_status() );
	}

	/**
	 * Test handle_bounce with SendLayer format records bounce when email log exists.
	 *
	 * @covers \EDD\REST\Controllers\BounceWebhook::handle_bounce
	 */
	public function test_handle_bounce_sendlayer_format_with_match() {
		$email_log_id = parent::edd()->email_logs->create(
			array(
				'object_id'   => 1,
				'object_type' => 'order',
				'email_id'    => 'order_receipt',
				'subject'     => 'Your receipt',
				'email'       => 'sendlayer-bounce@example.com',
			)
		);

		$request = new \WP_REST_Request( 'POST', '/edd/v3/webhooks/bounce' );
		$request->set_header( 'Content-Type', 'application/json' );
		$request->set_body(
			wp_json_encode(
				array(
					'Signature' => array(
						'Timestamp' => 1770917391,
						'Token'     => 'abc123',
						'Signature' => 'def456',
					),
					'EventData' => array(
						'Event'                => 'bounced',
						'EventType'            => 'failed',
						'Domain'               => 'example.com',
						'MessageID'            => 'test-message-id',
						'BouncedEmailAddress'  => array(
							'EmailAddress'   => 'sendlayer-bounce@example.com',
							'Status'         => 'smtp;550 5.1.0 Recipient not found',
							'DiagnosticCode' => '5.1.0 (unknown address-related status)',
						),
						'Reason'               => 'smtp;550 5.1.0 Recipient not found',
						'Code'                 => '5.1.0 (unknown address-related status)',
					),
				)
			)
		);

		$response = $this->controller->handle_bounce( $request );

		$this->assertInstanceOf( 'WP_REST_Response', $response );
		$this->assertEquals( 200, $response->get_status() );

		$bounce_reason = get_metadata( 'edd_logs_email', $email_log_id, 'bounce', true );
		$this->assertEquals( 'smtp;550 5.1.0 Recipient not found', $bounce_reason );
	}

	/**
	 * Test handle_bounce with SendLayer format (no matching email returns 400).
	 *
	 * @covers \EDD\REST\Controllers\BounceWebhook::handle_bounce
	 */
	public function test_handle_bounce_sendlayer_format_no_match() {
		$request = new \WP_REST_Request( 'POST', '/edd/v3/webhooks/bounce' );
		$request->set_header( 'Content-Type', 'application/json' );
		$request->set_body(
			wp_json_encode(
				array(
					'Signature' => array(
						'Timestamp' => 1770917391,
						'Token'     => 'abc123',
						'Signature' => 'def456',
					),
					'EventData' => array(
						'Event'                => 'bounced',
						'EventType'            => 'failed',
						'Domain'               => 'example.com',
						'MessageID'            => 'test-message-id',
						'BouncedEmailAddress'  => array(
							'EmailAddress'   => 'nonexistent@example.com',
							'Status'         => 'smtp;550 5.1.0 Recipient not found',
							'DiagnosticCode' => '5.1.0',
						),
						'Reason'               => 'smtp;550 5.1.0 Recipient not found',
						'Code'                 => '5.1.0',
					),
				)
			)
		);

		$response = $this->controller->handle_bounce( $request );

		$this->assertInstanceOf( 'WP_REST_Response', $response );
		$this->assertEquals( 400, $response->get_status() );
	}

	/**
	 * Test handle_bounce with SES format missing Message field returns 400.
	 *
	 * @covers \EDD\REST\Controllers\BounceWebhook::handle_bounce
	 */
	public function test_handle_bounce_ses_format_missing_message() {
		$request = new \WP_REST_Request( 'POST', '/edd/v3/webhooks/bounce' );
		$request->set_header( 'Content-Type', 'application/json' );
		$request->set_body(
			wp_json_encode(
				array(
					'Type' => 'Notification',
				)
			)
		);

		$response = $this->controller->handle_bounce( $request );

		$this->assertInstanceOf( 'WP_REST_Response', $response );
		$this->assertEquals( 400, $response->get_status() );
	}

	/**
	 * Test handle_bounce with SES format non-bounce message returns 400.
	 *
	 * @covers \EDD\REST\Controllers\BounceWebhook::handle_bounce
	 */
	public function test_handle_bounce_ses_format_non_bounce_message() {
		$request = new \WP_REST_Request( 'POST', '/edd/v3/webhooks/bounce' );
		$request->set_header( 'Content-Type', 'application/json' );
		$request->set_body(
			wp_json_encode(
				array(
					'Type'    => 'Notification',
					'Message' => wp_json_encode(
						array(
							'delivery' => array( 'recipients' => array( 'test@example.com' ) ),
						)
					),
				)
			)
		);

		$response = $this->controller->handle_bounce( $request );

		$this->assertInstanceOf( 'WP_REST_Response', $response );
		$this->assertEquals( 400, $response->get_status() );
	}

	/**
	 * Test handle_bounce with generic format and email_id of 0 returns 400.
	 *
	 * @covers \EDD\REST\Controllers\BounceWebhook::handle_bounce
	 */
	public function test_handle_bounce_generic_zero_email_id() {
		$request = new \WP_REST_Request( 'POST', '/edd/v3/webhooks/bounce' );
		$request->set_header( 'Content-Type', 'application/json' );
		$request->set_body(
			wp_json_encode(
				array(
					'email_id' => 0,
					'reason'   => 'Bounced',
				)
			)
		);

		$response = $this->controller->handle_bounce( $request );

		$this->assertInstanceOf( 'WP_REST_Response', $response );
		// absint(0) = 0, which is empty, so this should return 400.
		$this->assertEquals( 400, $response->get_status() );
	}
}
