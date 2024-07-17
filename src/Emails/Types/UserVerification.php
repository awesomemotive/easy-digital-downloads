<?php

namespace EDD\Emails\Types;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

/**
 * Class UserVerification
 *
 * @since 3.3.0
 * @package EDD
 * @subpackage Emails
 */
class UserVerification extends Email {

	/**
	 * The email ID.
	 *
	 * @var string
	 * @since 3.3.0
	 */
	protected $id = 'user_verification';

	/**
	 * The email context.
	 *
	 * @var string
	 * @since 3.3.0
	 */
	protected $context = 'user';

	/**
	 * The email recipient type.
	 *
	 * @var string
	 * @since 3.3.0
	 */
	protected $recipient_type = 'customer';

	/**
	 * The user ID.
	 *
	 * @var int
	 * @since 3.3.0
	 */
	protected $user_id;

	/**
	 * The user data.
	 *
	 * @var WP_User
	 * @since 3.3.0
	 */
	protected $user_data;

	/**
	 * The class constructor.
	 *
	 * @since 3.3.0
	 * @param int $user_id The user ID.
	 */
	public function __construct( $user_id ) {
		$this->user_id   = $user_id;
		$this->user_data = get_userdata( $user_id );
	}

	/**
	 * Set the email message.
	 * In this class, this function only gets the message and maybe applies wpautop.
	 * Tags are processed in the child classes.
	 *
	 * @since 3.2.0
	 * @return void
	 */
	protected function set_message() {
		parent::set_message();
		if ( false === strpos( $this->message, '{verification_url}' ) ) {
			$this->message = $this->get_fallback_content();
		} else {
			$this->message = $this->parse_tag( $this->message );
		}

		$this->message = $this->process_tags( $this->message, $this->user_id, $this->user_data );
		$this->message = apply_filters( 'edd_user_verification_email_message', $this->message, $this->user_id );
	}

	/**
	 * Set the email subject.
	 *
	 * @since 3.2.0
	 * @return void
	 */
	protected function set_subject() {
		parent::set_subject();
		$this->subject = apply_filters( 'edd_user_verification_email_subject', $this->subject, $this->user_id );
	}

	/**
	 * Set the email heading.
	 *
	 * @since 3.2.0
	 * @return void
	 */
	protected function set_heading() {
		parent::set_heading();
		$this->heading = apply_filters( 'edd_user_verification_email_heading', $this->heading, $this->user_id );
	}

	/**
	 * Set the email to address.
	 *
	 * @since 3.2.0
	 * @return void
	 */
	protected function set_to_email() {
		$this->send_to = $this->user_data->user_email;
	}

	/**
	 * Gets the original email content, from before this was registered
	 * as an email template.
	 *
	 * @since 3.3.0
	 * @return string
	 */
	private function get_fallback_content() {
		if ( empty( $this->from_name ) ) {
			$this->set_from_name();
		}
		$template = $this->get_template();

		return $this->parse_tag( $template->get_default( 'content' ) );
	}

	/**
	 * Parses the {verification_url} tag.
	 *
	 * @since 3.3.0
	 * @param string $content The content to parse.
	 * @return string
	 */
	private function parse_tag( $content ) {
		return str_replace( '{verification_url}', esc_url_raw( edd_get_user_verification_url( $this->user_id ) ), $content );
	}
}
