<?php

namespace EDD\Emails\Types;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

/**
 * Class NewUser
 *
 * @since 3.3.0
 * @package EDD
 * @subpackage Emails
 */
class NewUser extends Email {

	/**
	 * The email ID.
	 *
	 * @var string
	 * @since 3.3.0
	 */
	protected $id = 'new_user';

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
	 * @var array
	 * @since 3.3.0
	 */
	protected $user_data;

	/**
	 * The class constructor.
	 *
	 * @since 3.3.0
	 * @param int   $user_id   The user ID.
	 * @param array $user_data The user data.
	 */
	public function __construct( $user_id, $user_data ) {
		$this->user_id   = $user_id;
		$this->user_data = $user_data;
	}

	/**
	 * Set the email body content, without tags replaced.
	 *
	 * @since 3.3.0
	 * @return void
	 */
	protected function set_email_body_content() {
		$this->raw_body_content = apply_filters( 'edd_user_registration_admin_email_message', $this->get_email()->content, $this->user_data );
	}

	/**
	 * Set the email to address.
	 *
	 * @since 3.3.0
	 * @return void
	 */
	protected function set_to_email() {
		if ( empty( $this->send_to ) ) {
			$this->send_to = $this->user_data['user_email'];
		}
	}

	/**
	 * Set the email subject.
	 *
	 * @since 3.3.0
	 * @return void
	 */
	protected function set_subject() {
		parent::set_subject();

		$this->subject = apply_filters( 'edd_user_registration_email_subject', $this->subject, $this->user_data );
		$this->subject = $this->process_tags( $this->subject, $this->user_id );
	}

	/**
	 * Set the email heading.
	 *
	 * @since 3.3.0
	 * @return void
	 */
	protected function set_heading() {
		parent::set_heading();
		$this->heading = apply_filters( 'edd_user_registration_email_heading', $this->heading, $this->user_data );

		$this->heading = $this->process_tags( $this->heading, $this->user_id );
	}

	/**
	 * Set the email message.
	 *
	 * @since 3.3.0
	 * @return void
	 */
	protected function set_message() {
		parent::set_message();

		$this->message = apply_filters( 'edd_user_registration_email_message', $this->message, $this->user_data );
		$this->message = $this->process_tags( $this->message, $this->user_id );
	}
}
