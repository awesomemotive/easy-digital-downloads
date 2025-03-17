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
		$content = $this->apply_legacy_filters( $this->get_email()->content, 'message' );

		/**
		 * Filter the email body content.
		 *
		 * @since 3.3.7
		 * @param string   $content The email body content.
		 * @param \WP_User $user    The user object.
		 */
		$content = apply_filters( 'edd_new_user_email_message', $content, get_userdata( $this->user_id ) );

		$this->raw_body_content = $content;
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

		$this->subject = $this->apply_legacy_filters( $this->subject, 'subject' );
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
		$this->heading = $this->apply_legacy_filters( $this->heading, 'heading' );

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

		$this->message = $this->process_tags( $this->message, $this->user_id );
	}

	/**
	 * Apply legacy filters.
	 *
	 * @param string $content The content to filter.
	 * @param string $key     The key to get the filter name.
	 * @return string
	 */
	private function apply_legacy_filters( $content, $key ) {
		$legacy_filters = array(
			'message' => 'edd_user_registration_email_message',
			'subject' => 'edd_user_registration_email_subject',
			'heading' => 'edd_user_registration_email_heading',
		);

		if ( ! array_key_exists( $key, $legacy_filters ) ) {
			return $content;
		}

		if ( ! has_filter( $legacy_filters[ $key ] ) ) {
			return $content;
		}

		EDD()->notifications->maybe_add_local_notification(
			array(
				'remote_id'  => 'new_user_mail_filter',
				'buttons'    => '',
				'conditions' => '',
				'type'       => 'warning',
				'title'      => __( 'Please Check Your Custom Code', 'easy-digital-downloads' ),
				'content'    => __( 'EDD has detected that you are filtering the user registration email. To improve security and performance, in an upcoming release of EDD, the password will no longer be included in the email filter. Please verify and update any customizations you may have in place.', 'easy-digital-downloads' ),
			)
		);

		return apply_filters( $legacy_filters[ $key ], $content, $this->user_data );
	}
}
