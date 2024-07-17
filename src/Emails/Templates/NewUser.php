<?php

namespace EDD\Emails\Templates;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

/**
 * Class NewUser
 *
 * @since 3.3.0
 * @package EDD\Emails\Templates
 */
class NewUser extends EmailTemplate {

	/**
	 * Whether the email can be previewed.
	 *
	 * @since 3.3.0
	 * @var bool
	 */
	protected $can_preview = true;

	/**
	 * Whether a test email can be sent.
	 *
	 * @since 3.3.0
	 * @var bool
	 */
	protected $can_test = true;

	/**
	 * Unique identifier for this template.
	 *
	 * @since 3.3.0
	 * @var string
	 */
	protected $email_id = 'new_user';

	/**
	 * The email recipient.
	 *
	 * @since 3.3.0
	 * @var string
	 */
	protected $recipient = 'customer';

	/**
	 * The email context.
	 *
	 * @since 3.3.0
	 * @var string
	 */
	protected $context = 'user';

	/**
	 * Name of the template.
	 *
	 * @since 3.3.0
	 * @return string
	 */
	public function get_name() {
		return __( 'New User Registration', 'easy-digital-downloads' );
	}

	/**
	 * Description of the email.
	 *
	 * @since 3.3.0
	 * @return string
	 */
	public function get_description() {
		return __( 'This email is sent to a new user when their account is registered via EDD.', 'easy-digital-downloads' );
	}

	/**
	 * Define the email defaults.
	 *
	 * @since 3.3.0
	 * @return array
	 */
	public function defaults(): array {
		return array(
			/* translators: %s: Email tag that will be replaced with the Site's Name */
			'subject' => sprintf( _x( '[%s] Your username and password', 'Context: This is an email tag (placeholder) that will be replaced at the time of sending the email', 'easy-digital-downloads' ), '{sitename}' ),
			'heading' => __( 'Your account info', 'easy-digital-downloads' ),
			'content' => $this->get_default_content(),
			'status'  => 1,
		);
	}

	/**
	 * The default body.
	 *
	 * @since 3.3.0
	 * @return string
	 */
	private function get_default_content() {
		$login_url = apply_filters( 'edd_user_registration_email_login_url', wp_login_url() );

		$message = sprintf(
			/* translators: %s: Email tag that will be replaced with the username */
			_x(
				'Username: %s',
				'Context: This is an email tag (placeholder) that will be replaced at the time of sending the email',
				'easy-digital-downloads'
			),
			'{username}'
		) . "\r\n";

		$message .= __( 'Password: [entered on site]', 'easy-digital-downloads' ) . "\r\n";

		if ( EDD()->emails->html ) {
			$message .= '<a href="' . esc_url( $login_url ) . '"> ' . esc_attr__( 'Click here to log in', 'easy-digital-downloads' ) . ' &rarr;</a>';
			$message .= "\r\n";
		} else {
			/* translators: %s: login URL */
			$message .= sprintf( __( 'To log in, visit: %s', 'easy-digital-downloads' ), esc_url( $login_url ) ) . "\r\n";
		}

		return $message;
	}
}
