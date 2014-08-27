<?php
/**
 * Emails
 *
 * This class handles all emails sent through EDD
 *
 * @package     EDD
 * @subpackage  Classes/Emails
 * @copyright   Copyright (c) 2014, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.1.php GNU Public License
 * @since       2.1
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * EDD_Emails Class
 *
 * @since 2.1
 */
class EDD_Emails {

	/**
	 * Holds the from address
	 *
	 * @since 2.1
	 */
	private $from_address;

	/**
	 * Holds the from name
	 *
	 * @since 2.1
	 */
	private $from_name;

	/**
	 * Holds the email content type
	 *
	 * @since 2.1
	 */
	private $content_type;

	/**
	 * Holds the email headers
	 *
	 * @since 2.1
	 */
	private $headers;

	/**
	 * Whether to send email in HTML
	 *
	 * @since 2.1
	 */
	private $html = true;

	/**
	 * The email template to use
	 *
	 * @since 2.1
	 */
	private $template;

	/**
	 * Get things going
	 *
	 * @since 2.1
	 */
	public function __construct() {

		if ( 'none' === $this->get_template() ) {
			$this->html = false;
		}

		add_action( 'edd_email_send_before', array( $this, 'send_before' ) );
		add_action( 'edd_email_send_after', array( $this, 'send_after' ) );
		add_filter( 'edd_email_message', array( $this, 'text_to_html' ), 10, 2 );
	}

	/**
	 * Set a property
	 *
	 * @since 2.1
	 */
	public function __set( $key, $value ) {
		$this->$key = $value;
	}

	/**
	 * Get the email from name
	 *
	 * @since 2.1
	 */
	public function get_from_name() {
		if ( ! $this->from_name ) {
			$this->from_name = edd_get_option( 'from_name', get_bloginfo( 'name' ) );
		}

		return apply_filters( 'edd_email_from_name', wp_specialchars_decode( $this->from_name ), $this );
	}

	/**
	 * Get the email from address
	 *
	 * @since 2.1
	 */
	public function get_from_address() {
		if ( ! $this->from_address ) {
			$this->from_address = edd_get_option( 'from_email', get_option( 'admin_email' ) );
		}

		return apply_filters( 'edd_email_from_address', $this->from_address, $this );
	}

	/**
	 * Get the email content type
	 *
	 * @since 2.1
	 */
	public function get_content_type() {
		if ( ! $this->content_type && $this->html ) {
			$this->content_type = apply_filters( 'edd_email_default_content_type', 'text/html', $this );
		} else if ( ! $this->html ) {
			$this->content_type = 'text/plain';
		}

		return apply_filters( 'edd_email_content_type', $this->content_type, $this );
	}

	/**
	 * Get the email headers
	 *
	 * @since 2.1
	 */
	public function get_headers() {
		if ( ! $this->headers ) {
			$this->headers  = "From: {$this->get_from_name()} <{$this->get_from_address()}>\r\n";
			$this->headers .= "Reply-To: {$this->get_from_address()}\r\n";
			$this->headers .= "Content-Type: {$this->get_content_type()}; charset=utf-8\r\n";
		}

		return apply_filters( 'edd_email_headers', $this->headers, $this );
	}

	/**
	 * Retrieve email templates
	 *
	 * @since 2.1
	 */
	public function get_templates() {
		$templates = array(
			'default' => __( 'Default Template', 'edd' ),
			'none'    => __( 'No template, plain text only', 'edd' )
		);

		return apply_filters( 'edd_email_templates', $templates );
	}

	/**
	 * Get the enabled email template
	 *
	 * @since 2.1
	 */
	public function get_template() {
		if ( ! $this->template ) {
			$this->template = edd_get_option( 'email_template', 'default' );
		}

		return apply_filters( 'edd_email_template', $this->template );
	}

	/**
	 * Parse email template tags
	 *
	 * @since 2.1
	 */
	public function parse_tags( $content ) {

		// The email tags are parsed during setup for purchase receipts and sale notifications
		// Onoce tags are not restricted to payments, we'll expand this. See https://github.com/easydigitaldownloads/Easy-Digital-Downloads/issues/2151

		return $content;
	}

	/**
	 * Build the final email
	 *
	 * @since 2.1
	 */
	public function build_email( $message, $heading ) {

		if ( false === $this->html ) {
			return apply_filters( 'edd_email_message', wp_strip_all_tags( $message ), $this );
		}

		ob_start();

		edd_get_template_part( 'emails/header', $this->get_template(), true );

		do_action( 'edd_email_header', $this );

		if ( has_action( 'edd_email_template_' . $this->get_template() ) ) {
			do_action( 'edd_email_template_' . $this->get_template() );
		} else {
			edd_get_template_part( 'emails/body', $this->get_template(), true );
		}

		do_action( 'edd_email_body', $this );

		edd_get_template_part( 'emails/footer', $this->get_template(), true );

		do_action( 'edd_email_footer', $this );

		$body    = ob_get_clean();
		$message = str_replace( '{email}', $message, $body );
		$message = str_replace( '{heading}', $heading, $message );

		return apply_filters( 'edd_email_message', $message, $this );
	}

	/**
	 * Send the email
	 * @param  string  $to               The To address to send to.
	 * @param  string  $subject          The subject line of the email to send.
	 * @param  string  $message          The body of the email to send.
	 * @param  string  $heading          A heading to use at the top of the email body. If you pass an
	 *                                   empty string (the default), then the subject will be used. To
	 *                                   have no heading at all, pass NULL.
	 * @param  string|array $attachments Attachments to the email in a format supported by wp_mail()
	 * @since 2.1
	 */
	public function send( $to, $subject, $message, $heading = '', $attachments = '' ) {

		if ( ! did_action( 'init' ) && ! did_action( 'admin_init' ) ) {
			_doing_it_wrong( __FUNCTION__, __( 'You cannot send email with EDD_Emails until init/admin_init has been reached', 'edd' ), null );
			return false;
		}

		if ( !is_null( $heading ) && empty( $heading ) ) {
			$heading = $subject;
		}

		do_action( 'edd_email_send_before', $this );

		$subject = $this->parse_tags( $subject );
		$message = $this->parse_tags( $message );

		$message = $this->build_email( $message, $heading );
		if ( empty( $attachments ) ) {
			$attachments = apply_filters( 'edd_email_default_attachments', '' );
		}
		$attachments = apply_filters( 'edd_email_attachments', $attachments, $this );

		$sent = wp_mail( $to, $subject, $message, $this->get_headers(), $attachments );

		do_action( 'edd_email_send_after', $this );

		return $sent;

	}

	/**
	 * Add filters / actions before the email is sent
	 *
	 * @since 2.1
	 */
	public function send_before() {
		add_filter( 'wp_mail_from', array( $this, 'get_from_address' ) );
		add_filter( 'wp_mail_from_name', array( $this, 'get_from_name' ) );
		add_filter( 'wp_mail_content_type', array( $this, 'get_content_type' ) );
	}

	/**
	 * Remove filters / actions after the email is sent
	 *
	 * @since 2.1
	 */
	public function send_after() {
		remove_filter( 'wp_mail_from', array( $this, 'get_from_address' ) );
		remove_filter( 'wp_mail_from_name', array( $this, 'get_from_name' ) );
		remove_filter( 'wp_mail_content_type', array( $this, 'get_content_type' ) );
	}

	/**
	 * Converts text to formatted HTML. This is primarily for turning line breaks into <p> and <br/> tags.
	 *
	 * @since 2.1
	 */
	public function text_to_html( $message, $class_object ) {

		if ( 'html' == $this->content_type ) {
			$message = wpautop( $message );
		}

		return $message;
	}

}