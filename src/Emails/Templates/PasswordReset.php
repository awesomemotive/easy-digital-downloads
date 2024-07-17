<?php

namespace EDD\Emails\Templates;

defined( 'ABSPATH' ) || exit;

/**
 * Class PasswordReset
 * Note that this email is not registered by default. It is only registered if the login page is set.
 * The email only filters the WordPress password reset email, so only the email body is customized.
 *
 * @since 3.3.0
 * @package EDD\Emails\Templates
 */
class PasswordReset extends EmailTemplate {

	/**
	 * Unique identifier for this template.
	 *
	 * @var string
	 */
	protected $email_id = 'password_reset';

	/**
	 * The email recipient.
	 *
	 * @var string
	 */
	protected $recipient = 'user';

	/**
	 * The email context.
	 *
	 * @var string
	 */
	protected $context = 'user';

	/**
	 * The email sender.
	 *
	 * @since 3.3.0
	 * @var string
	 */
	protected $sender = 'wp';

	/**
	 * The required tag.
	 *
	 * @since 3.3.0
	 * @var string
	 */
	protected $required_tag = 'password_reset_link';

	/**
	 * Name of the template.
	 *
	 * @return string
	 */
	public function get_name() {
		return __( 'Password Reset', 'easy-digital-downloads' );
	}

	/**
	 * Description of the email.
	 *
	 * @return string
	 */
	public function get_description() {
		return __( 'This email is sent by WordPress when a user requests a password reset from the EDD Login block.', 'easy-digital-downloads' );
	}

	/**
	 * The email defaults.
	 *
	 * @since 3.3.0
	 * @return array
	 */
	public function defaults(): array {
		return array(
			'content' => $this->get_default_content(),
			'subject' => $this->get_default_subject(),
		);
	}

	/**
	 * Determines whether the email's base requirements are met.
	 * Most emails will not need this.
	 *
	 * @since 3.3.0
	 * @return bool
	 */
	public function are_base_requirements_met(): bool {
		return edd_get_login_page_uri();
	}

	/**
	 * The email editable properties.
	 *
	 * @since 3.3.0
	 * @return array
	 */
	protected function get_editable_properties(): array {
		return array( 'content' );
	}

	/**
	 * Whether the email is enabled.
	 *
	 * @since 3.3.0
	 * @return bool
	 */
	protected function is_enabled(): bool {
		return $this->are_base_requirements_met() && apply_filters( 'send_retrieve_password_email', true );
	}

	/**
	 * The email context label.
	 *
	 * @since 3.3.0
	 * @return string
	 */
	public function get_context_label(): string {
		return __( 'Lost Password', 'easy-digital-downloads' );
	}

	/**
	 * Gets the content for the status tooltip, if needed.
	 *
	 * @since 3.3.0
	 * @return array
	 */
	public function get_status_tooltip(): array {
		$content = __( 'This email cannot be disabled because it is managed and sent by WordPress.', 'easy-digital-downloads' );
		if ( ! $this->are_base_requirements_met() ) {
			$content = __( 'This email cannot be enabled because the login page has not been set.', 'easy-digital-downloads' );
		} elseif ( ! $this->is_enabled() ) {
			$content = __( 'This email cannot be enabled because it has been disabled by code.', 'easy-digital-downloads' );
		}

		return array(
			'content'  => $content,
			'dashicon' => 'dashicons-lock',
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
			'label'       => __( 'Password Reset URL', 'easy-digital-downloads' ),
			'description' => __( 'The link for the user to reset their password.', 'easy-digital-downloads' ),
		);
	}

	/**
	 * The default content.
	 *
	 * @since 3.3.0
	 * @return string
	 */
	private function get_default_content() {
		$message = __( 'Someone has requested a password reset for the following account:', 'easy-digital-downloads' ) . "\r\n\r\n";
		/* translators: %s: Email tag that will be replaced with the site name */
		$message .= sprintf( __( 'Site Name: %s', 'easy-digital-downloads' ), '{sitename}' ) . "\r\n\r\n";
		$message .= sprintf(
			/* translators: %s: Email tag that will be replaced with the username */
			_x(
				'Username: %s',
				'Context: This is an email tag (placeholder) that will be replaced at the time of sending the email',
				'easy-digital-downloads'
			),
			'{username}'
		) . "\r\n\r\n";
		$message .= __( 'If this was a mistake, ignore this email and nothing will happen.', 'easy-digital-downloads' ) . "\r\n\r\n";
		$message .= __( 'To reset your password, visit the following address:', 'easy-digital-downloads' ) . "\r\n\r\n";
		$message .= '{password_reset_link}';
		$message .= "\r\n\r\n";

		$message .= sprintf(
			/* translators: %s: IP address of password reset requester. */
			__( 'This password reset request originated from the IP address %s.', 'easy-digital-downloads' ),
			'{ip_address}'
		) . "\r\n";

		return $message;
	}

	/**
	 * The email subject.
	 *
	 * @since 3.3.0
	 * @return string
	 */
	private function get_default_subject() {
		if ( is_multisite() ) {
			$site_name = get_network()->site_name;
		} else {
			/*
			 * The blogname option is escaped with esc_html on the way into the database
			 * in sanitize_option. We want to reverse this for the plain text arena of emails.
			 */
			$site_name = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );
		}
		/* translators: %s: Site name. */
		$title = sprintf( __( '[%s] Password Reset', 'easy-digital-downloads' ), $site_name );

		// This is a WP Core filter.
		return apply_filters( 'retrieve_password_title', $title );
	}
}
