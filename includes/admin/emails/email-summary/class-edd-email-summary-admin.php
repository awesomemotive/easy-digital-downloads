<?php
/**
 * Email Summary Admin Class.
 *
 * @package     EDD
 * @subpackage  Emails
 * @copyright   Copyright (c) 2022, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.1
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * EDD_Email_Summary_Admin Class.
 *
 * Takes care of Admin actions for Email Summaries.
 *
 * @since 3.1
 */
class EDD_Email_Summary_Admin {

	/**
	 * WordPress SMTP error.
	 *
	 * @since 3.1
	 *
	 * @var bool|\WP_Error
	 */
	public $mail_smtp_error = false;

	/**
	 * Class constructor.
	 *
	 * @since 3.1
	 */
	public function __construct() {
		add_action( 'wp_ajax_edd_send_test_email_summary', array( $this, 'send_test_email_summary' ) );
	}

	/**
	 * Send test Email Summary.
	 *
	 * @since 3.1
	 *
	 * @param array $data GET Request array.
	 */
	public function send_test_email_summary() {

		if ( ! current_user_can( 'manage_shop_settings' ) ) {
			echo wp_json_encode(
				array(
					'status'  => 'error',
					'message' => __( 'You do not have permission to perform this action.', 'easy-digital-downloads' ),
				)
			);
		}

		add_action( 'wp_mail_failed', array( $this, 'mail_failed' ) );

		$output       = array(
			'status'  => 'success',
			'message' => __( 'The test Email Summary was sent successfully!', 'easy-digital-downloads' ),
		);
		$email        = new EDD_Email_Summary( true );
		$email_status = $email->send_email();
		if ( ! $email_status ) {
			$output['status'] = 'error';

			// Generic error.
			$output['message'] = __( 'There was an unknown problem while sending test Email Summary!', 'easy-digital-downloads' );

			// SMTP error.
			if ( $this->mail_smtp_error ) {
				$output['message'] = $this->mail_smtp_error;
			}
		}

		echo wp_json_encode( $output );
		edd_die();
	}

	/**
	 * Get error message from failed SMTP.
	 *
	 * @since 3.1
	 *
	 * @param \WP_Error $error The WP Error thrown in WP core: `wp_mail_failed` hook.
	 */
	public function mail_failed( $error ) {
		if ( ! is_wp_error( $error ) ) {
			return;
		}

		$this->mail_smtp_error = $error->get_error_message();
	}

}
