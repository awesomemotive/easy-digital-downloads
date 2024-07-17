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
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

use EDD\Emails\Base;
use EDD\Database\Queries\LogEmail;

/**
 * Class Email
 *
 * @since 3.2.0
 */
abstract class Email {
	/**
	 * The email ID.
	 *
	 * @var string
	 * @since 3.2.0
	 */
	protected $id;

	/**
	 * The email context.
	 *
	 * @var string
	 * @since 3.2.0
	 */
	protected $context;

	/**
	 * The email recipient type.
	 *
	 * @var string
	 * @since 3.2.0
	 */
	protected $recipient_type;

	/**
	 * The email subject.
	 *
	 * @var string
	 * @since 3.2.0
	 */
	public $send_to;

	/**
	 * Whether or not this is being used in a preview context.
	 *
	 * @var bool
	 * @since 3.2.0
	 */
	public $is_preview = false;

	/**
	 * Whether or not this is being used in a test context, which actually sends.
	 *
	 * @var bool
	 * @since 3.2.0
	 */
	public $is_test = false;

	/**
	 * Whether the build method has been run, making any tag replacements.
	 *
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
	 *
	 * @var string
	 * @since 3.2.0
	 */
	protected $raw_body_content;

	/**
	 * The email subject.
	 *
	 * @var string
	 * @since 3.2.0
	 */
	protected $subject;

	/**
	 * The email attachments.
	 *
	 * @var array
	 * @since 3.2.0
	 */
	protected $attachments;

	/**
	 * The email from name.
	 *
	 * @var string
	 * @since 3.2.0
	 */
	protected $from_name;

	/**
	 * The email from email address.
	 *
	 * @var string
	 * @since 3.2.0
	 */
	protected $from_email;

	/**
	 * The email heading.
	 *
	 * @var string
	 * @since 3.2.0
	 */
	protected $heading;

	/**
	 * The email body content, with placeholders replaced.
	 *
	 * @var string
	 * @since 3.2.0
	 */
	public $message;

	/**
	 * The email headers.
	 *
	 * @var string
	 * @since 3.2.0
	 */
	protected $headers;

	/**
	 * The email object.
	 *
	 * @since 3.3.0
	 * @var \EDD\Emails\Email
	 */
	protected $email;

	/**
	 * The email template.
	 *
	 * @var \EDD\Emails\Templates\EmailTemplate
	 * @since 3.3.0
	 */
	protected $template;

	/**
	 * The email object ID.
	 *
	 * @var int|false
	 * @since 3.3.0
	 */
	protected $email_object_id;

	/**
	 * The Email constructor.
	 *
	 * @since 3.2.0
	 * @return void
	 */
	public function __construct() {}

	/**
	 * Magic method to get properties.
	 *
	 * @since 3.2.10
	 * @param string $property The property to get.
	 * @return mixed
	 */
	public function __get( $property ) {

		if ( property_exists( $this, $property ) && ! is_null( $this->$property ) ) {
			return $this->$property;
		}

		if ( is_callable( array( $this, 'get_' . $property ) ) ) {
			return call_user_func( array( $this, 'get_' . $property ) );
		}

		return null;
	}

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
	 *
	 * @since 3.2.0
	 * @return bool
	 */
	public function send() {
		// If we shouldn't send the email, just return. No `false` value here, as that could produce failure emails when it shouldn't.
		if ( ! $this->should_send() && ! $this->is_test ) {
			// Return early, so we don't spend any processing time on an email we are not going to send.
			return null;
		}

		// If we should send the email, we can go ahead and ensure it is built.
		if ( ! $this->is_built ) {
			$this->build();
		}

		// For test emails, we always send to the admin email.
		if ( $this->is_test ) {
			$this->send_to = $this->get_test_recipient();
		}

		$this->set_email_properties();

		edd_debug_log( 'Sending email: ' . $this->id );

		$sent = $this->processor()->send( $this->send_to, $this->subject, $this->message, $this->attachments );

		$this->log_email( $sent );

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

		if ( empty( $this->message ) ) {
			add_filter( 'edd_email_show_links', '__return_false' );
			$this->set_message();
			remove_filter( 'edd_email_show_links', '__return_false' );
		}

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
	 *
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
	 *
	 * @since 3.2.0
	 * @return void
	 */
	protected function set_email_body_content() {
		$this->raw_body_content = $this->get_email()->content;
	}

	/**
	 * Set the email from name.
	 *
	 * @since 3.2.0
	 * @return void
	 */
	protected function set_from_name() {
		$this->from_name = edd_get_option( 'from_name', wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ) );
	}

	/**
	 * Set the email from address.
	 *
	 * @since 3.2.0
	 * @return void
	 */
	protected function set_from_email() {
		$this->from_email = edd_get_option( 'from_email', get_option( 'admin_email' ) );
	}

	/**
	 * Set the email to address.
	 *
	 * @since 3.2.0
	 * @return void
	 */
	abstract protected function set_to_email();

	/**
	 * Set the email headers.
	 *
	 * @since 3.2.0
	 * @return void
	 */
	protected function set_headers() {
		$this->headers = $this->processor()->get_headers();
	}

	/**
	 * Set the email subject.
	 *
	 * @since 3.2.0
	 * @return void
	 */
	protected function set_subject() {
		$this->subject = $this->get_email()->subject;
	}

	/**
	 * Set the email heading.
	 *
	 * @since 3.2.0
	 * @return void
	 */
	protected function set_heading() {
		$this->heading = $this->get_email()->heading;
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
		$this->message = $this->maybe_apply_autop( $this->get_raw_body_content() );
	}

	/**
	 * Set the email attachments.
	 *
	 * @since 3.2.0
	 * @return void
	 */
	protected function set_attachments() {
		$this->attachments = array();
	}

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

	/**
	 * Process the tags in the content.
	 *
	 * @since 3.2.0
	 *
	 * @param string $content      The content to process.
	 * @param int    $object_id    The object ID to pass to the tags.
	 * @param object $email_object The email object to pass to the tags.
	 * @param string $context      Optional: The context to pass to the tags.
	 *
	 * @return string
	 */
	protected function process_tags( $content, $object_id, $email_object = null, $context = null ) {
		$process_object = false;
		if ( ! $context ) {
			$context        = $this->get_context();
			$process_object = true;
		}
		if ( 'order' === $context && ( $this->is_preview || $this->is_test ) ) {
			$content = edd_email_preview_template_tags( $content, false, $object_id );
		}

		// Preferred usage: sends the email object to the tags, if a custom context was not passed in.
		if ( $process_object ) {
			$content = EDD()->email_tags->do_tags( $content, $object_id, $email_object, $this );
		}

		// Original usage: sends the email context (order, subscription, user) to the tags.
		return edd_do_email_tags( $content, $object_id, $email_object, $context );
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
		$should_send = (bool) $this->get_email() && $this->get_email()->is_enabled();

		return apply_filters( 'edd_should_send_email_' . $this->id, $should_send, $this );
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

	/**
	 * Get the default email body content.
	 *
	 * @since 3.2.0
	 * @deprecated 3.3.0 Deprecated in favor of instantiating the settings classes directly.
	 * @return string
	 */
	protected function get_default_body_content() {
		return '';
	}

	/**
	 * Get the email object.
	 *
	 * @since 3.3.0
	 * @return \EDD\Emails\Email
	 */
	protected function get_email() {
		if ( ! $this->email ) {
			$this->email = $this->get_email_from_db();
		}

		return $this->email;
	}

	/**
	 * Gets the email template settings.
	 *
	 * @since 3.3.0
	 * @return \EDD\Admin\Emails\Templates\EmailTemplate
	 */
	protected function get_template() {
		if ( ! $this->template ) {
			$email          = $this->get_email();
			$this->template = $email->get_template();
		}

		return $this->template;
	}

	/**
	 * Gets the test recipient.
	 *
	 * @since 3.3.0
	 * @return string
	 */
	protected function get_test_recipient() {
		return get_option( 'admin_email' );
	}

	/**
	 * Gets the email object ID.
	 *
	 * @since 3.3.0
	 * @return int|false
	 */
	protected function get_email_object_id() {
		if ( ! empty( $this->email_object_id ) ) {
			return $this->email_object_id;
		}

		$object_id   = false;
		$reflection  = new \ReflectionClass( $this );
		$constructor = $reflection->getConstructor();
		$parameters  = $constructor->getParameters();
		if ( empty( $parameters ) ) {
			return false;
		}
		$first_parameter = reset( $parameters );
		$name            = $first_parameter->getName();
		if ( is_numeric( $this->{$name} ) ) {
			$object_id = $this->{$name};
		} elseif ( ! empty( $this->{$name} ) && ! empty( $this->{$name}->id ) ) {
			$object_id = $this->{$name}->id;
		}

		$this->email_object_id = (int) $object_id;

		return $this->email_object_id;
	}

	/**
	 * Logs the email.
	 *
	 * @since 3.3.0
	 * @param bool $sent Whether or not the email was sent.
	 * @return int|false
	 */
	private function log_email( $sent ) {
		if ( ! $sent ) {
			return false;
		}
		if ( 'admin' === $this->get_recipient_type() ) {
			return false;
		}
		if ( $this->is_test ) {
			return false;
		}
		$object_id = $this->get_email_object_id();
		if ( ! $object_id ) {
			return false;
		}
		$logs = new LogEmail();

		return $logs->add_item(
			array(
				'object_id'   => $object_id,
				'object_type' => $this->context,
				'email_id'    => $this->id,
				'subject'     => $this->subject,
				'email'       => $this->send_to,
			)
		);
	}

	/**
	 * Get the email from the database.
	 * If the email cannot be found, a new email object is returned.
	 *
	 * @since 3.3.0
	 * @return \EDD\Emails\Email
	 */
	private function get_email_from_db() {
		$email = edd_get_email( $this->id );
		if ( $email ) {
			return $email;
		}

		$email_template = edd_get_email_registry()->get_email_by_id( $this->id, $this );
		if ( $email_template ) {
			$id = $email_template->install();

			return edd_get_email_by( 'id', $id );
		}

		$email           = new \EDD\Emails\Email();
		$email->email_id = $this->id;

		return $email;
	}
}
