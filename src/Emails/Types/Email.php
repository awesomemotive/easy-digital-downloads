<?php
/**
 * The Main Email class.
 *
 * Use this as a base to build out a specific email to send.
 *
 * @since 3.2.0
 * @package EDD
 * @subpackage Emails
 */

namespace EDD\Emails\Types;

use EDD\Emails\Base;

abstract class Email {
	/**
	 * The email ID.
	 * @var string
	 * @since 3.2.0
	 */
	protected $id;

	/**
	 * The email context.
	 * @var string
	 * @since 3.2.0
	 */
	protected $context;

	/**
	 * The email recipient type.
	 * @var string
	 * @since 3.2.0
	 */
	protected $recipient_type;

	/**
	 * The email subject.
	 * @var string
	 * @since 3.2.0
	 */
	public $send_to;

	/**
	 * Whether or not this is being used in a preview context.
	 * @var bool
	 * @since 3.2.0
	 */
	public $is_preview = false;

	/**
	 * Whether or not this is being used in a test context, which actually sends.
	 * @var bool
	 * @since 3.2.0
	 */
	public $is_test = false;

	/**
	 * Whether the build method has been run, making any tag replacements.
	 * @var bool
	 * @since 3.2.0
	 */
	public $is_built = false;

	/**
	 * The email processor.
	 *
	 * This handles setting all aspects of the email for sending.
	 *
	 * @var Base
	 * @since 3.2.0
	 */
	protected $processor;

	/**
	 * The raw body content, with placeholders.
	 * @var string
	 * @since 3.2.0
	 */
	protected $raw_body_content;

	/**
	 * The email subject.
	 * @var string
	 * @since 3.2.0
	 */
	protected $subject;

	/**
	 * The email attachments.
	 * @var array
	 * @since 3.2.0
	 */
	protected $attachments;

	/**
	 * The email from name.
	 * @var string
	 * @since 3.2.0
	 */
	protected $from_name;

	/**
	 * The email from email address.
	 * @var string
	 * @since 3.2.0
	 */
	protected $from_email;

	/**
	 * The email heading.
	 * @var string
	 * @since 3.2.0
	 */
	protected $heading;

	/**
	 * The email body content, with placeholders replaced.
	 * @var string
	 * @since 3.2.0
	 */
	public $message;

	/**
	 * The email headers.
	 * @var string
	 * @since 3.2.0
	 */
	protected $headers;

	/**
	 * The Email constructor.
	 *
	 * @since 3.2.0
	 * @return void
	 */
	public function __construct() {}

	/**
	 * Get the email ID.
	 *
	 * @since 3.2.0
	 *
	 * @return string
	 */
	public function get_id() {
		return $this->id;
	}

	/**
	 * Get the email context.
	 *
	 * @since 3.2.0
	 *
	 * @return string
	 */
	public function get_context() {
		return $this->context;
	}

	/**
	 * Return the raw body content.
	 *
	 * This will contain the email tags, in an non-replaced format.
	 *
	 * @since 3.2.0
	 * @return string
	 */
	public function get_raw_body_content() {
		if ( ! $this->raw_body_content ) {
			$this->set_email_body_content();
		}

		return $this->raw_body_content;
	}

	/**
	 * Get the email recipient type.
	 *
	 * @since 3.2.0
	 *
	 * @return string
	 */
	public function get_recipient_type() {
		return $this->recipient_type;
	}

	/**
	 * Send the email.
	 * @since 3.2.0
	 * @return bool
	 */
	public function send() {
		// If we shouldn't send the email, just return. No `false` value here, as that could produce failure emails when it shouldn't.
		if ( ! $this->should_send() ) {
			// Return early, so we don't spend any processing time on an email we are not going to send.
			return null;
		}

		// If we should send the email, we can go ahead and ensure it is built.
		if ( ! $this->is_built ) {
			$this->build();
		}

		$this->set_email_properties();

		edd_debug_log( 'Sending email: ' . $this->id );

		$sent = $this->processor()->send( $this->send_to, $this->subject, $this->message, $this->attachments );

		do_action( 'edd_email_sent_' . $this->id, $this, $sent );

		return $sent;
	}

	/**
	 * Returns the final email content that would be sent, after templates have been applied.
	 *
	 * @since 3.2.0
	 */
	public function get_preview() {
		if ( ! $this->is_preview ) {
			return '';
		}

		if ( ! $this->is_built ) {
			$this->build();
		}

		$this->set_email_properties();

		return $this->processor()->build_email( $this->message );
	}

	/**
	 * Get the processing class EDD\Emails\Base.
	 *
	 * @since 3.2.0
	 * @return Base
	 */
	protected function processor() {
		if ( is_null( $this->processor ) ) {
			$this->processor = new Base();
		}

		return $this->processor;
	}

	/**
	 * Build the email.
	 * @since 3.2.0
	 * @return void
	 */
	protected function build() {
		$this->set_from_name();

		$this->set_from_email();

		$this->set_to_email();

		$this->set_headers();

		$this->set_subject();

		$this->set_heading();

		$this->set_message();

		$this->set_attachments();

		$this->is_built = true;
	}

	/**
	 * Set the email body content, without tags replaced.
	 * @since 3.2.0
	 * @return void
	 */
	abstract protected function set_email_body_content();

	/**
	 * Set the email from name.
	 * @since 3.2.0
	 * @return void
	 */
	abstract protected function set_from_name();

	/**
	 * Set the email from address.
	 * @since 3.2.0
	 * @return void
	 */
	abstract protected function set_from_email();

	/**
	 * Set the email to address.
	 * @since 3.2.0
	 * @return void
	 */
	abstract protected function set_to_email();

	/**
	 * Set the email headers.
	 * @since 3.2.0
	 * @return void
	 */
	abstract protected function set_headers();

	/**
	 * Set the email subject.
	 * @since 3.2.0
	 * @return void
	 */
	abstract protected function set_subject();

	/**
	 * Set the email heading.
	 * @since 3.2.0
	 * @return void
	 */
	abstract protected function set_heading();

	/**
	 * Set the email message.
	 * @since 3.2.0
	 * @return void
	 */
	abstract protected function set_message();

	/**
	 * Set the email attachments.
	 * @since 3.2.0
	 * @return void
	 */
	abstract protected function set_attachments();

	/**
	 * Get the default email body content.
	 *
	 * @since 3.2.0
	 * @return string
	 */
	abstract public function get_default_body_content();

	/**
	 * Possibly runs the content provided through wpautop.
	 *
	 * @since 3.2.0
	 *
	 * @param string $content The content to run through wpautop.
	 *
	 * @return string
	 */
	protected function maybe_apply_autop( $content ) {
		if ( apply_filters( 'edd_email_template_wpautop', true ) ) {
			return wpautop( $content );
		}

		return $content;
	}

	protected function process_tags( $content, $arguments ) {
		if ( $this->is_preview || $this->is_test ) {
			return edd_email_preview_template_tags( $content );
		}

		return edd_do_email_tags( $content, $arguments );
	}

	/**
	 * Should this email send.
	 *
	 * This is a placeholder method that should be overridden in the child class if there is logic that needs to be run
	 * on if this email should be sent or not.
	 *
	 * @since 3.2.0
	 *
	 * @return bool
	 */
	protected function should_send() {
		return apply_filters( 'edd_should_send_email_' . $this->id, true, $this );
	}

	/**
	 * Add properties to the base email processor.
	 *
	 * @since 3.2.0
	 * @return void
	 */
	private function set_email_properties() {
		$this->processor()->__set( 'from_name', $this->from_name );
		$this->processor()->__set( 'from_address', $this->from_email );
		$this->processor()->__set( 'heading', $this->heading );
		$this->processor()->__set( 'headers', $this->headers );
	}
}
