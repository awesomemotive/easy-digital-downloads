<?php

namespace EDD\Emails\Templates;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

/**
 * Class UserVerification
 *
 * @since 3.3.0
 * @package EDD\Emails\Templates
 */
class UserVerification extends EmailTemplate {

	/**
	 * Whether the email can be previewed.
	 *
	 * @since 3.3.0
	 * @var bool
	 */
	protected $can_preview = true;

	/**
	 * Unique identifier for this template.
	 *
	 * @since 3.3.0
	 * @var string
	 */
	protected $email_id = 'user_verification';

	/**
	 * The email recipient.
	 *
	 * @since 3.3.0
	 * @var string
	 */
	protected $recipient = 'user';

	/**
	 * The email context.
	 *
	 * @since 3.3.0
	 * @var string
	 */
	protected $context = 'user';

	/**
	 * The required tag.
	 *
	 * @since 3.3.0
	 * @var string
	 */
	protected $required_tag = 'verification_url';

	/**
	 * Name of the template.
	 *
	 * @since 3.3.0
	 * @return string
	 */
	public function get_name() {
		return __( 'User Verification', 'easy-digital-downloads' );
	}

	/**
	 * Description of the email.
	 *
	 * @since 3.3.0
	 * @return string
	 */
	public function get_description() {
		return __( 'This email is sent to a user when they need to verify their account.', 'easy-digital-downloads' );
	}

	/**
	 * Retrieves the default email properties.
	 *
	 * @since 3.3.0
	 * @return array
	 */
	public function defaults(): array {
		return array(
			'subject' => __( 'Verify your account', 'easy-digital-downloads' ),
			'heading' => __( 'Verify your account', 'easy-digital-downloads' ),
			'content' => $this->get_body_default(),
			'status'  => 1,
		);
	}

	/**
	 * Gets the required tag parameters for the email editor.
	 *
	 * @since 3.3.0
	 * @return array
	 */
	protected function get_required_tag_parameters() {
		return array(
			'label'       => __( 'Verification URL', 'easy-digital-downloads' ),
			'description' => __( 'The link for the user to verify their account.', 'easy-digital-downloads' ),
		);
	}

	/**
	 * Retrieves the preview data for this email.
	 *
	 * @since 3.3.0
	 * @return array
	 */
	protected function get_preview_data() {
		return array( get_current_user_id() );
	}

	/**
	 * The email properties that can be edited.
	 *
	 * @return array
	 */
	protected function get_editable_properties(): array {
		return array(
			'content',
			'subject',
			'heading',
		);
	}

	/**
	 * The default email body.
	 *
	 * @since 3.3.0
	 * @return string
	 */
	private function get_body_default() {
		$message = sprintf(
			/* translators: %s: The email tag that will be replaced with the customer's full name. */
			__( 'Hello %s,', 'easy-digital-downloads' ),
			'{fullname}',
		) . "\n\n";
		$message .= sprintf(
			/* translators: %s: The email tag that will be replaced with the Site Name. */
			__( 'Your account with %s needs to be verified before you can access your order history.', 'easy-digital-downloads' ),
			'{sitename}',
		) . "\n\n";
		$message .= sprintf(
			/* translators: %s: The email tag that will be replaced with the verification URL. */
			__( 'Visit this link to verify your account: %s', 'easy-digital-downloads' ),
			'{verification_url}',
		) . "\n\n";

		return $message;
	}
}
