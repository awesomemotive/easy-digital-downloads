<?php

namespace EDD\Emails;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

use EDD\Database\Rows\Email as Row;

/**
 * Class Email
 *
 * @since 3.3.0
 * @package EDD\Emails
 */
class Email extends Row {

	/**
	 * The email ID.
	 *
	 * @since 3.3.0
	 * @var string
	 */
	protected $id;

	/**
	 * The email ID.
	 *
	 * @since 3.3.0
	 * @var string
	 */
	protected $email_id;

	/**
	 * The email recipient.
	 *
	 * @since 3.3.0
	 * @var string
	 */
	protected $recipient;

	/**
	 * The email context.
	 *
	 * @since 3.3.0
	 * @var string
	 */
	protected $context;

	/**
	 * The email sender.
	 *
	 * @since 3.3.0
	 * @var string
	 */
	protected $sender;

	/**
	 * The email subject.
	 *
	 * @since 3.3.0
	 * @var string
	 */
	protected $subject;

	/**
	 * The email heading.
	 *
	 * @since 3.3.0
	 * @var string
	 */
	protected $heading;

	/**
	 * The email content.
	 *
	 * @since 3.3.0
	 * @var string
	 */
	protected $content;

	/**
	 * The email status.
	 *
	 * @since 3.3.0
	 * @var bool
	 */
	protected $status;

	/**
	 * The email date created.
	 *
	 * @since 3.3.0
	 * @var string
	 */
	protected $date_created;

	/**
	 * The email date modified.
	 *
	 * @since 3.3.0
	 * @var string
	 */
	protected $date_modified;

	/**
	 * Magic setter.
	 * This exists to allow template classes to map properties to the email object.
	 *
	 * @param string $key   The property to set.
	 * @param mixed  $value The value to set.
	 * @return mixed
	 */
	public function __set( $key, $value = '' ) {
		// Return property if it exists.
		if ( property_exists( $this, $key ) ) {
			$this->{$key} = $value;
		}

		// Return null if not exists.
		return null;
	}

	/**
	 * Gets the email template.
	 *
	 * @since 3.3.0
	 * @return EDD\Emails\Templates\EmailTemplate|false
	 */
	public function get_template() {
		return edd_get_email_registry()->get_email_by_id( $this->email_id, $this );
	}

	/**
	 * Gets the email status.
	 *
	 * @since 3.3.0
	 * @return bool
	 */
	public function is_enabled() {
		$template = $this->get_template();
		if ( $template ) {
			return $template->status;
		}

		return $this->status;
	}

	/**
	 * Gets the email recipient.
	 *
	 * @since 3.3.0
	 * @param null|mixed $email_object The email type object.
	 * @return array|false
	 */
	public function get_admin_recipient_emails( $email_object = null ) {
		if ( 'admin' !== $this->recipient ) {
			return false;
		}
		$meta = edd_get_email_meta( $this->id, 'recipients', true );
		// If the email was created before the meta was correctly saved, default to admin email in settings.
		if ( empty( $meta ) && gmdate( 'H:i', strtotime( $this->date_created ) ) === gmdate( 'H:i', strtotime( $this->date_modified ) ) ) {
			$meta = 'admin';
			edd_update_email_meta( $this->id, 'recipients', $meta );
		}

		// If there isn't a custom recipient, default to the admin email.
		if ( empty( $meta ) ) {
			return get_bloginfo( 'admin_email' );
		}

		// If the meta is 'admin', get the admin emails from the EDD settings.
		if ( 'admin' === $meta ) {
			return edd_get_admin_notice_emails( $email_object );
		}

		return array_map( 'trim', explode( "\n", $meta ) );
	}
}
