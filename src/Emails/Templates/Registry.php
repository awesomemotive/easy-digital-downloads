<?php

namespace EDD\Emails\Templates;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

use EDD\Utils\Exception;
use EDD\Emails\Email;

/**
 * Class Registry
 *
 * @since 3.3.0
 * @package EDD\Emails\Templates
 */
class Registry {

	/**
	 * The registered emails.
	 *
	 * @since 3.3.0
	 * @var array
	 */
	private $emails = array();

	/**
	 * Gets the instance of the class.
	 *
	 * @since 3.3.0
	 * @return Registry
	 */
	public static function get_instance() {
		static $instance = null;

		if ( is_null( $instance ) ) {
			$instance = new Registry();
		}

		return $instance;
	}

	/**
	 * Retrieves a list of available emails.
	 *
	 * @since 3.3.0
	 * @return array
	 */
	public function get_emails() {
		if ( empty( $this->emails ) ) {
			$emails = array(
				'order_receipt'              => OrderReceipt::class,
				'admin_order_notice'         => AdminOrderNotice::class,
				'order_refund'               => OrderRefund::class,
				'admin_order_refund'         => AdminOrderRefund::class,
				'new_user'                   => NewUser::class,
				'new_user_admin'             => NewUserAdmin::class,
				'user_verification'          => UserVerification::class,
				'password_reset'             => PasswordReset::class,
				'stripe_early_fraud_warning' => StripeEarlyFraudWarning::class,
			);

			$this->emails = apply_filters( 'edd_email_registered_templates', $emails );
		}

		return $this->emails;
	}

	/**
	 * Retrieves a list of available recipients.
	 *
	 * @since 3.3.0
	 * @return array
	 */
	public function get_recipients() {
		$recipients = apply_filters( 'edd_email_recipients', array() );

		$recipients['customer'] = __( 'Customer', 'easy-digital-downloads' );
		$recipients['admin']    = __( 'Admin', 'easy-digital-downloads' );
		$recipients['user']     = __( 'User', 'easy-digital-downloads' );
		asort( $recipients );

		return $recipients;
	}

	/**
	 * Retrieves a list of available senders.
	 *
	 * @since 3.3.0
	 * @return array
	 */
	public function get_senders() {
		$senders = apply_filters( 'edd_email_senders', array() );

		$senders['edd'] = __( 'EDD Core', 'easy-digital-downloads' );
		$senders['wp']  = __( 'WordPress', 'easy-digital-downloads' );
		asort( $senders );

		return $senders;
	}

	/**
	 * Retrieves a list of available contexts.
	 *
	 * @since 3.3.0
	 * @return array
	 */
	public function get_contexts() {
		$contexts = apply_filters( 'edd_email_contexts', array() );
		$contexts = wp_parse_args(
			$contexts,
			array(
				'order'  => __( 'Order', 'easy-digital-downloads' ),
				'refund' => __( 'Refund', 'easy-digital-downloads' ),
				'user'   => __( 'Account', 'easy-digital-downloads' ),
			)
		);

		return $contexts;
	}

	/**
	 * Retrieves a list of available actions.
	 *
	 * @since 3.3.0
	 * @return array
	 */
	public function get_add_new_actions() {
		$actions = array(
			'license_new' => array(
				'promo' => 4916,
				'label' => __( 'Add License Renewal Notice', 'easy-digital-downloads' ),
			),
			'sub_new'     => array(
				'promo' => 28530,
				'label' => __( 'Add Subscription Reminder', 'easy-digital-downloads' ),
			),
			'edd_ppe_new' => array(
				'promo' => 90781,
				'label' => __( 'Add Per Product Email', 'easy-digital-downloads' ),
			),
		);

		return apply_filters( 'edd_email_add_new_actions', $actions );
	}

	/**
	 * Retrieves an `EmailTemplate` instance by its ID (slug).
	 *
	 * @since 3.3.0
	 *
	 * @param string $id The email ID.
	 * @param Email  $email Optional. The email object, if already instantiated.
	 *
	 * @return EmailTemplate
	 * @throws Exception If the email class cannot be instantiated.
	 */
	public function get_email_by_id( $id, $email = null ) {

		$emails = $this->get_emails();

		// Check the database first.
		if ( ! $email instanceof Email ) {
			$email = edd_get_email( $id );
		}
		if ( $email && array_key_exists( $email->email_id, $emails ) ) {
			return $this->make_email_class( $emails[ $email->email_id ], array( $email->email_id, $email ) );
		}

		// Otherwise, loop through the array of emails and try to find it.
		foreach ( $emails as $key => $email ) {
			try {
				$email = $this->make_email_class( $email, array( $key ) );
				if ( $email->email_id === $id ) {
					return $email;
				}
			} catch ( Exception $e ) {
				// Do nothing.
			}
		}

		return null;
	}

	/**
	 * Retrieve an `EmailTemplate` instance by its class name.
	 *
	 * @param string $class_name Name of the class.
	 * @param array  $arguments  Optional arguments for the class.
	 * @return EmailTemplate
	 * @throws Exception If the class is not registered.
	 */
	public function get_email( $class_name, $arguments = array() ) {
		if ( ! in_array( $class_name, $this->get_emails(), true ) ) {
			throw new Exception( sprintf( 'Email template %s not found.', $class_name ) );
		}

		return $this->make_email_class( $class_name, $arguments );
	}

	/**
	 * Converts the supplied `Email` class name into an instance of that class
	 * (with some validation).
	 *
	 * @since 3.3.0
	 * @param string $class_name The class name.
	 * @param array  $arguments  Optional. The array of arguments.
	 * @return \EDD\Emails\EmailTemplate
	 * @throws Exception If the class does not exist or does not extend the `EmailTemplate` class.
	 */
	public function make_email_class( $class_name, $arguments = array() ) {
		$class_name = sanitize_text_field( $class_name );
		if ( ! class_exists( $class_name ) ) {
			throw new Exception( __( 'Invalid email template.', 'easy-digital-downloads' ) );
		}

		if ( ! is_subclass_of( $class_name, EmailTemplate::class ) ) {
			/* translators: %1$s is the class name, %2$s is the parent class name. */
			throw new Exception( sprintf( __( 'The %1$s class must extend the %2$s class.', 'easy-digital-downloads' ), $class_name, EmailTemplate::class ) );
		}

		return new $class_name( ...$arguments );
	}
}
