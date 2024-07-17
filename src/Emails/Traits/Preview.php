<?php

namespace EDD\Emails\Traits;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

use EDD\Emails\Registry;

/**
 * Trait Preview
 *
 * @since 3.3.0
 * @package EDD\Emails\Traits
 */
trait Preview {

	/**
	 * Determine the test email to send and call the action based on the id.
	 *
	 * @since 3.2.0
	 * @param array $data The $_POST data.
	 *
	 * @return void
	 */
	public function send_test_email( $data ) {
		// Get the email object.
		$id = ! empty( $data['email'] ) ? sanitize_text_field( $data['email'] ) : 'order_receipt';

		// Only people who can amnage the shop settings should be able to send these.
		if ( ! current_user_can( 'manage_shop_settings' ) ) {
			return;
		}

		// Verify the nonce.
		if ( ! wp_verify_nonce( $data['_wpnonce'], 'edd-test-email' ) ) {
			return;
		}

		$test = Registry::get( $id, $this->get_preview_data( $id ) );

		// Set this as a preview email.
		$test->is_test = true;

		// For tests, disable links and send.
		add_filter( 'edd_email_show_links', '__return_false' );
		$sent = $test->send();
		remove_filter( 'edd_email_show_links', '__return_false' );

		$edd_message = $sent ? 'test-email-sent' : 'test-email-failed';

		$redirect = array(
			'page'        => 'edd-emails',
			'edd-message' => $edd_message,
		);
		if ( ! empty( $data['editor'] ) ) {
			$redirect['email'] = $data['email'];
		}

		edd_redirect( edd_get_admin_url( $redirect ) );
	}

	/**
	 * Preview an email.
	 *
	 * This previously ran on `template_redirect` but now runs on it's own EDD Action, to avoid always running it.
	 *
	 * @since 3.2.0
	 * @param array $data The $_GET data.
	 * @return void
	 */
	public function preview_email( $data ) {
		if ( ! current_user_can( 'manage_shop_settings' ) ) {
			return;
		}

		// Verify the nonce.
		if ( empty( $data['_wpnonce'] ) || ! wp_verify_nonce( $data['_wpnonce'], 'edd-preview-email' ) ) {
			return;
		}

		$email_id            = ! empty( $data['email'] ) ? sanitize_text_field( $data['email'] ) : 'order_receipt';
		$preview             = Registry::get( $email_id, $this->get_preview_data( $email_id ) );
		$preview->is_preview = true;

		echo $preview->get_preview();

		exit;
	}

	/**
	 * Get the preview parameters for the email.
	 *
	 * @since 3.3.0
	 * @param string $email_id The email ID.
	 * @return array
	 */
	private function get_preview_data( $email_id ) {
		$template = $this->get_template( $email_id );

		return $template ? $template->set_preview_data() : array();
	}

	/**
	 * Gets the email template.
	 *
	 * @since 3.3.0
	 * @param string $email_id The email ID.
	 * @return \EDD\Emails\Templates\EmailTemplate
	 */
	private function get_template( $email_id ) {
		return edd_get_email_registry()->get_email_by_id( $email_id );
	}

	/**
	 * Send the test purchase confirmation email.
	 *
	 * This does the work of verifying the nonce and checking the capabilities.
	 *
	 * @since 3.2.0
	 * @deprecated 3.3.0
	 * @param array $data The $_POST data.
	 */
	public function send_test_order_receipt( $data ) {
		$this->send_test_email( $data );
	}
}
