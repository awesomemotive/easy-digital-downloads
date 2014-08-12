<?php
/**
 * Emails
 *
 * This class handles all emails sent through EDD
 *
 * @package     EDD
 * @subpackage  Classes/Emails
 * @copyright   Copyright (c) 2014, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       2.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * EDD_Emails Class
 *
 * @since 2.0
 */
class EDD_Emails {

	/**
	 * Holds the from address
	 *
	 * @since 2.0
	 */
	private $from_address;

	/**
	 * Holds the from name
	 *
	 * @since 2.0
	 */
	private $from_name;

	/**
	 * Holds the email content type
	 *
	 * @since 2.0
	 */
	private $content_type;

	/**
	 * Holds the email headers
	 *
	 * @since 2.0
	 */
	private $headers;

	/**
	 * Whether to send email in HTML
	 *
	 * @since 2.0
	 */
	private $html = true;

	/**
	 * The email template to use
	 *
	 * @since 2.0
	 */
	private $template;

	/**
	 * Get things going
	 *
	 * @since 2.0
	 */
	public function __construct() {
		add_action( 'edd_email_send_before', array( $this, 'send_before' ) );
		add_action( 'edd_email_send_after', array( $this, 'send_after' ) );
	}

	/**
	 * Set a property
	 *
	 * @since 2.0
	 */
	public function __set( $key, $value ) {
		$this->$key = $value;
	}

	/**
	 * Get the email from name
	 *
	 * @since 2.0
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
	 * @since 2.0
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
	 * @since 2.0
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
	 * @since 2.0
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
	 * Get the email attachments
	 *
	 * @since 2.0
	 */
	public function get_attachments() {
		if ( ! $this->headers ) {
			$this->attachments = apply_filters( 'edd_email_default_attachments', '' );
		}

		return apply_filters( 'edd_email_attachments', $this->attachments, $this );
	}

	/**
	 * Retrieve email templates
	 *
	 * @since 2.0
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
	 * @since 2.0
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
	 * @since 2.0
	 */
	public function parse_tags( $content ) {
		return $content; // Do something with email tags once complete.
	}

	/**
	 * Build the final email
	 *
	 * @since 2.0
	 */
	public function build_email( $message ) {

		if ( false === $this->html )
			return $message;

		ob_start();

		do_action( 'edd_email_header', $this );

		edd_get_template_part( 'emails/header', null, true );
		do_action( 'edd_email_header', $this );

		edd_get_template_part( sprintf( 'emails/%s', $this->get_template() ), null, true );
		do_action( 'edd_email_body', $this );

		edd_get_template_part( 'emails/footer', null, true );
		do_action( 'edd_email_footer', $this );

		$body    = ob_get_clean();
		$message = str_replace( '{email}', $message, $body );

		return $message;
	}

	/**
	 * Send the email
	 *
	 * @since 2.0
	 */
	public function send( $to, $subject, $message ) {
		do_action( 'edd_email_send_before', $this );

		$subject = $this->parse_tags( $subject );
		$message = $this->parse_tags( $message );

		$message = $this->build_email( $message );

		$sent = wp_mail( $to, $subject, $message, $this->get_headers(), $this->get_attachments() );

		do_action( 'edd_email_send_after', $this );

		return $sent;

	}

	/**
	 * Add filters / actions before the email is sent
	 *
	 * @since 2.0
	 */
	public function send_before() {
		add_filter( 'wp_mail_from', array( $this, 'get_from_address' ) );
		add_filter( 'wp_mail_from_name', array( $this, 'get_from_name' ) );
		add_filter( 'wp_mail_content_type', array( $this, 'get_content_type' ) );
	}

	/**
	 * Remove filters / actions after the email is sent
	 *
	 * @since 2.0
	 */
	public function send_after() {
		remove_filter( 'wp_mail_from', array( $this, 'get_from_address' ) );
		remove_filter( 'wp_mail_from_name', array( $this, 'get_from_name' ) );
		remove_filter( 'wp_mail_content_type', array( $this, 'get_content_type' ) );
	}

}