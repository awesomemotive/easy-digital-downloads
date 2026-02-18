<?php
/**
 * REST API controller for Bounce Webhook handling.
 *
 * Handles bounce webhook notifications from email service providers
 * (SendGrid, Mailgun, AWS SES, SendLayer, and generic formats) for EDD emails.
 *
 * @package EDD\REST\Controllers
 * @copyright Copyright (c) 2026, Sandhills Development, LLC
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @since 3.6.5
 */

namespace EDD\REST\Controllers;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Bounce Webhook controller class.
 *
 * @since 3.6.5
 */
final class BounceWebhook {

	/**
	 * Handles bounce webhook notifications.
	 *
	 * @since 3.6.5
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response Response object.
	 */
	public function handle_bounce( \WP_REST_Request $request ): \WP_REST_Response {
		$body = $request->get_json_params();

		if ( empty( $body ) ) {
			return new \WP_REST_Response(
				array( 'error' => 'Invalid payload' ),
				400
			);
		}

		// Parse the webhook payload based on provider.
		$parsed = $this->parse_bounce_webhook( $body, $request );

		if ( ! $parsed || empty( $parsed['email_id'] ) || empty( $parsed['reason'] ) ) {
			return new \WP_REST_Response(
				array( 'error' => 'Unable to parse bounce data' ),
				400
			);
		}

		// Record the bounce.
		$recorded = add_metadata( 'edd_logs_email', $parsed['email_id'], 'bounce', $parsed['reason'] );

		if ( $recorded ) {
			/**
			 * Fires after a bounce is successfully recorded on an email log entry.
			 *
			 * @since 3.6.5
			 * @param int    $email_log_id The email log ID (from edd_logs_emails).
			 * @param string $reason       The bounce reason.
			 */
			do_action( 'edd_email_bounced', $parsed['email_id'], $parsed['reason'] );

			return new \WP_REST_Response(
				array(
					'success' => true,
					'message' => 'Bounce recorded',
				),
				200
			);
		}

		return new \WP_REST_Response(
			array( 'error' => 'Failed to record bounce' ),
			500
		);
	}

	/**
	 * Verifies webhook permission via secret key.
	 *
	 * @since 3.6.5
	 * @param \WP_REST_Request $request Request object.
	 * @return bool True if authorized.
	 */
	public function verify_webhook_permission( \WP_REST_Request $request ): bool {
		// Check for secret key in header or query param.
		$provided_key = $request->get_header( 'X-EDD-Webhook-Secret' );

		if ( empty( $provided_key ) ) {
			$provided_key = $request->get_param( 'secret' );
		}

		if ( empty( $provided_key ) ) {
			edd_debug_log( '[EDD Bounce Webhook] Webhook rejected: no secret provided', true );
			return false;
		}

		// Get the expected webhook secret.
		$expected_secret = self::generate_webhook_secret();

		// Verify the key matches.
		if ( ! hash_equals( $expected_secret, $provided_key ) ) {
			edd_debug_log( '[EDD Bounce Webhook] Webhook rejected: invalid secret', true );
			return false;
		}

		return true;
	}

	/**
	 * Generates the webhook secret.
	 *
	 * Static method to allow access from settings/admin screens.
	 *
	 * @since 3.6.5
	 * @return string Webhook secret key.
	 */
	public static function generate_webhook_secret(): string {
		return hash_hmac( 'sha256', 'edd_bounce_webhook', wp_salt( 'nonce' ) );
	}

	/**
	 * Parses bounce webhook payload from various email service providers.
	 *
	 * @since 3.6.5
	 * @param array            $body    The webhook payload.
	 * @param \WP_REST_Request $request The request object.
	 * @return array|null Parsed bounce data with email_id and reason, or null on failure.
	 */
	private function parse_bounce_webhook( array $body, \WP_REST_Request $request ): ?array {
		// Attempt to identify the provider and parse accordingly.

		// WP Mail SMTP - SendGrid.
		if ( isset( $body['event'] ) && 'bounce' === $body['event'] && isset( $body['email'] ) ) {
			return $this->parse_sendgrid_bounce( $body );
		}

		// Mailgun.
		if ( isset( $body['event'] ) && 'failed' === $body['event'] && isset( $body['recipient'] ) ) {
			return $this->parse_mailgun_bounce( $body );
		}

		// AWS SES (via SNS).
		if ( isset( $body['Type'] ) && 'Notification' === $body['Type'] ) {
			return $this->parse_ses_bounce( $body );
		}

		// SendLayer.
		if ( isset( $body['EventData']['Event'] ) && 'bounced' === $body['EventData']['Event'] ) {
			return $this->parse_sendlayer_bounce( $body );
		}

		// Generic format (custom or other providers).
		if ( isset( $body['email_id'] ) && isset( $body['reason'] ) ) {
			return array(
				'email_id' => absint( $body['email_id'] ),
				'reason'   => sanitize_text_field( $body['reason'] ),
			);
		}

		edd_debug_log( '[EDD Bounce Webhook] Unknown bounce webhook format: ' . wp_json_encode( $body ), true );
		return null;
	}

	/**
	 * Parses SendGrid bounce webhook.
	 *
	 * @since 3.6.5
	 * @param array $body The webhook payload.
	 * @return array|null Parsed bounce data.
	 */
	private function parse_sendgrid_bounce( array $body ): ?array {
		$email  = $body['email'] ?? '';
		$reason = $body['reason'] ?? 'Unknown bounce reason';

		// Find the email_id by recipient email address.
		$email_id = $this->find_email_by_recipient( $email );

		if ( ! $email_id ) {
			return null;
		}

		return array(
			'email_id' => $email_id,
			'reason'   => $reason,
		);
	}

	/**
	 * Parses Mailgun bounce webhook.
	 *
	 * @since 3.6.5
	 * @param array $body The webhook payload.
	 * @return array|null Parsed bounce data.
	 */
	private function parse_mailgun_bounce( array $body ): ?array {
		$email  = $body['recipient'] ?? '';
		$reason = $body['error'] ?? 'Unknown bounce reason';

		// Find the email_id by recipient email address.
		$email_id = $this->find_email_by_recipient( $email );

		if ( ! $email_id ) {
			return null;
		}

		return array(
			'email_id' => $email_id,
			'reason'   => $reason,
		);
	}

	/**
	 * Parses AWS SES bounce webhook.
	 *
	 * @since 3.6.5
	 * @param array $body The webhook payload.
	 * @return array|null Parsed bounce data.
	 */
	private function parse_ses_bounce( array $body ): ?array {
		if ( ! isset( $body['Message'] ) ) {
			return null;
		}

		$message = json_decode( $body['Message'], true );

		if ( ! $message || ! isset( $message['bounce'] ) ) {
			return null;
		}

		$bounce     = $message['bounce'];
		$recipients = $bounce['bouncedRecipients'] ?? array();

		if ( empty( $recipients ) ) {
			return null;
		}

		$email  = $recipients[0]['emailAddress'] ?? '';
		$reason = $bounce['bounceType'] ?? 'Unknown bounce reason';

		// Find the email_id by recipient email address.
		$email_id = $this->find_email_by_recipient( $email );

		if ( ! $email_id ) {
			return null;
		}

		return array(
			'email_id' => $email_id,
			'reason'   => $reason,
		);
	}

	/**
	 * Parses SendLayer bounce webhook.
	 *
	 * @since 3.6.5
	 * @param array $body The webhook payload.
	 * @return array|null Parsed bounce data.
	 */
	private function parse_sendlayer_bounce( array $body ): ?array {
		$event_data = $body['EventData'];
		$email      = $event_data['BouncedEmailAddress']['EmailAddress'] ?? '';
		$reason     = $event_data['Reason'] ?? 'Unknown bounce reason';

		// Find the email_id by recipient email address.
		$email_id = $this->find_email_by_recipient( $email );

		if ( ! $email_id ) {
			return null;
		}

		return array(
			'email_id' => $email_id,
			'reason'   => $reason,
		);
	}

	/**
	 * Finds an email log ID by recipient email address.
	 *
	 * @since 3.6.5
	 * @param string $recipient_email The recipient email address.
	 * @return int|null The email log ID if found, null otherwise.
	 */
	private function find_email_by_recipient( string $recipient_email ): ?int {
		if ( empty( $recipient_email ) ) {
			return null;
		}

		// Find the most recent email log sent to this address.
		$query  = new \EDD\Database\Queries\LogEmail();
		$emails = $query->query(
			array(
				'email'   => sanitize_email( $recipient_email ),
				'orderby' => 'date_created',
				'order'   => 'DESC',
				'number'  => 1,
			)
		);

		if ( empty( $emails ) ) {
			return null;
		}

		return (int) $emails[0]->id;
	}
}
