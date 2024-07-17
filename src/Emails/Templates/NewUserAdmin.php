<?php

namespace EDD\Emails\Templates;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

/**
 * Class NewUserAdmin
 *
 * @since 3.3.0
 * @package EDD\Emails\Templates
 */
class NewUserAdmin extends EmailTemplate {

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
	protected $email_id = 'new_user_admin';

	/**
	 * The email recipient.
	 *
	 * @since 3.3.0
	 * @var string
	 */
	protected $recipient = 'admin';

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
		return __( 'Admin New User Notification', 'easy-digital-downloads' );
	}

	/**
	 * Description of the email.
	 *
	 * @since 3.3.0
	 * @return string
	 */
	public function get_description() {
		return __( 'This email is sent to the store admin when a new user is registered.', 'easy-digital-downloads' );
	}

	/**
	 * Define the email defaults.
	 *
	 * @since 3.3.0
	 * @return array
	 */
	public function defaults(): array {
		return array(
			/* translators: %s: Email tag that will be replaced with the Site Name */
			'subject' => sprintf( _x( '[%s] New User Registration', 'Context: This is an email tag (placeholder) that will be replaced at the time of sending the email', 'easy-digital-downloads' ), '{sitename}' ),
			'heading' => __( 'New user registration', 'easy-digital-downloads' ),
			'content' => $this->get_default_content(),
			'status'  => 1,
		);
	}

	/**
	 * The default content.
	 *
	 * @since 3.3.0
	 * @return string
	 */
	private function get_default_content() {
		$admin_message = sprintf(
			/* translators: %s: Email tag that will be replaced with the username */
			_x(
				'Username: %s',
				'Context: This is an email tag (placeholder) that will be replaced at the time of sending the email',
				'easy-digital-downloads'
			),
			'{username}'
		) . "\r\n\r\n";

		$admin_message .= sprintf(
			/* translators: %s: Email tag that will be replaced with the user email */
			_x(
				'E-mail: %s',
				'Context: This is an email tag (placeholder) that will be replaced at the time of sending the email',
				'easy-digital-downloads'
			),
			'{user_email}'
		) . "\r\n";

		return $admin_message;
	}
}
