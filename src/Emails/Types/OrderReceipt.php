<?php
/**
 * The OrderReceipt Email.
 *
 * @since 3.2.0
 *
 * @package EDD
 * @subpackage Emails
 */

namespace EDD\Emails\Types;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

/**
 * The OrderReceipt email class.
 *
 * This is sent to customers as their 'purchase confirmation'.
 *
 * @since 3.2.0
 */
class OrderReceipt extends Email {
	use LegacyPaymentFilters;

	/**
	 * The email ID.
	 *
	 * @var string
	 * @since 3.2.0
	 */
	protected $id = 'order_receipt';

	/**
	 * The email context.
	 *
	 * @var string
	 * @since 3.2.0
	 */
	protected $context = 'order';

	/**
	 * The email recipient type.
	 *
	 * @var string
	 * @since 3.2.0
	 */
	protected $recipient_type = 'customer';

	/**
	 * The order object.
	 *
	 * @var EDD\Orders\Order
	 * @since 3.2.0
	 */
	protected $order;

	/**
	 * The order ID.
	 *
	 * @var int
	 * @since 3.2.0
	 */
	protected $order_id;

	/**
	 * OrderReceipt constructor.
	 *
	 * @since 3.2.0
	 *
	 * @param EDD\Orders\Order $order The order object.
	 * @return void
	 */
	public function __construct( $order ) {
		$this->order = $order;

		// To support previews, we need to possibly set a `0` order ID.
		$this->order_id = false !== $order ? $order->id : 0;

		// Since we are refactoring this, we need to set the legacy filters.
		$this->set_legacy_filters();
	}

	/**
	 * Get the order of the emails.
	 *
	 * @since 3.2.0
	 *
	 * @return EDD\Orders\Order The order object.
	 */
	public function get_order() {
		return $this->order;
	}

	/**
	 * Set the raw email body content.
	 *
	 * This will add the email content to the `raw_body_content` property. It has not yet had
	 * the tag replacements executed.
	 *
	 * @since 3.2.0
	 * @return void
	 */
	protected function set_email_body_content() {
		parent::set_email_body_content();

		$this->maybe_run_legacy_filter( 'edd_purchase_receipt_' . $this->processor()->get_template() );
		$this->maybe_run_legacy_filter( 'edd_purchase_receipt' );

		$this->raw_body_content = apply_filters( 'edd_email_template_wpautop', true ) ? wpautop( $this->raw_body_content ) : $this->raw_body_content;
		$this->raw_body_content = apply_filters( 'edd_order_receipt_' . $this->processor()->get_template(), $this->raw_body_content, $this->order );
		$this->raw_body_content = apply_filters( 'edd_order_receipt', $this->raw_body_content, $this->order );
	}

	/**
	 * Set the 'from' name on the email.
	 *
	 * @since 3.2.0
	 * @return void
	 */
	protected function set_from_name() {
		parent::set_from_name();

		$this->maybe_run_legacy_filter( 'edd_purchase_from_name' );

		$this->from_name = apply_filters( 'edd_order_receipt_from_name', $this->from_name, $this->order );
	}

	/**
	 * Set the 'from' email on the email.
	 *
	 * @since 3.2.0
	 * @return void
	 */
	protected function set_from_email() {
		parent::set_from_email();

		$this->maybe_run_legacy_filter( 'edd_purchase_from_address' );

		$this->from_email = apply_filters( 'edd_order_receipt_from_email', $this->from_email, $this->order );
	}

	/**
	 * Set the 'to' email on the email.
	 *
	 * @since 3.2.0
	 * @return void
	 */
	protected function set_to_email() {
		if ( empty( $this->send_to ) && ! empty( $this->order->email ) ) {
			$this->send_to = $this->order->email;
		}
	}

	/**
	 * Set the headers email on the email.
	 *
	 * @since 3.2.0
	 * @return void
	 */
	protected function set_headers() {
		parent::set_headers();

		$this->maybe_run_legacy_filter( 'edd_receipt_headers' );

		$this->headers = apply_filters( 'edd_order_receipt_headers', $this->headers, $this->order );
	}

	/**
	 * Set the subject on the email.
	 *
	 * @since 3.2.0
	 * @return void
	 */
	protected function set_subject() {
		parent::set_subject();

		$this->maybe_run_legacy_filter( 'edd_purchase_subject' );

		$this->subject = apply_filters( 'edd_order_receipt_email_subject', $this->subject, $this->order );
		$this->subject = $this->process_tags( $this->subject, $this->order_id, $this->order );
	}

	/**
	 * Set the heading on the email.
	 *
	 * @since 3.2.0
	 * @return void
	 */
	protected function set_heading() {
		parent::set_heading();

		$this->maybe_run_legacy_filter( 'edd_purchase_heading' );

		$this->heading = apply_filters( 'edd_order_receipt_email_heading', wp_strip_all_tags( $this->heading ), $this->order );
		$this->heading = $this->process_tags( $this->heading, $this->order_id, $this->order );
	}

	/**
	 * Set the message on the email.
	 *
	 * @since 3.2.0
	 * @return void
	 */
	protected function set_message() {
		parent::set_message();

		$this->message = $this->process_tags( $this->message, $this->order_id, $this->order );
	}

	/**
	 * Set the attachments on the email.
	 *
	 * @since 3.2.0
	 * @return void
	 */
	protected function set_attachments() {
		$this->attachments = array();

		$this->maybe_run_legacy_filter( 'edd_receipt_attachments' );

		$this->attachments = apply_filters( 'edd_order_receipt_email_attachments', $this->attachments, $this->order );
	}

	/**
	 * Allows filtering to disable sending the default order receipt email.
	 *
	 * @since 3.2.0
	 *
	 * @return bool
	 */
	protected function should_send() {

		// Do not send receipts for orders that have been marked as 'imported'.
		if ( edd_get_order_meta( $this->order->id, '_edd_imported', true ) ) {
			return false;
		}

		if ( ! $this->get_email()->is_enabled() ) {
			return false;
		}

		// Allow developers to unhook this email via a filter.
		if ( true === apply_filters( 'edd_disable_' . $this->id, false, $this ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Set the used legacy filters.
	 *
	 * These filters all expect legacy EDD_Payment meta to be passed in, so we need to check
	 * them if they are being used. We store this locally so we can check it later.
	 *
	 * @since 3.2.0
	 *
	 * @return void
	 */
	protected function set_legacy_filters() {
		$email_template = $this->processor()->get_template();

		$this->legacy_filters = array(
			'edd_purchase_from_name'                  => array(
				'has_filter' => has_filter( 'edd_purchase_from_name' ),
				'property'   => 'from_name',
				'arguments'  => array( 'order_id', 'payment_meta' ),
			),
			'edd_purchase_from_address'               => array(
				'has_filter' => has_filter( 'edd_purchase_from_address' ),
				'property'   => 'from_email',
				'arguments'  => array( 'order_id', 'payment_meta' ),
			),
			'edd_purchase_subject'                    => array(
				'has_filter' => has_filter( 'edd_purchase_subject' ),
				'property'   => 'subject',
				'arguments'  => array( 'order_id' ),
			),
			'edd_purchase_heading'                    => array(
				'has_filter' => has_filter( 'edd_purchase_heading' ),
				'property'   => 'heading',
				'arguments'  => array( 'order_id', 'payment_meta' ),
			),
			'edd_receipt_attachments'                 => array(
				'has_filter' => has_filter( 'edd_receipt_attachments' ),
				'property'   => 'attachments',
				'arguments'  => array( 'order_id', 'payment_meta' ),
			),
			'edd_receipt_headers'                     => array(
				'has_filter' => has_filter( 'edd_receipt_headers' ),
				'property'   => 'headers',
				'arguments'  => array( 'order_id', 'payment_meta' ),
			),
			'edd_purchase_receipt_' . $email_template => array(
				'has_filter' => has_filter( 'edd_purchase_receipt_' . $email_template ),
				'property'   => 'raw_body_content',
				'arguments'  => array( 'order_id', 'payment_meta' ),
			),
			'edd_purchase_receipt'                    => array(
				'has_filter' => has_filter( 'edd_purchase_receipt' ),
				'property'   => 'raw_body_content',
				'arguments'  => array( 'order_id', 'payment_meta' ),
			),
		);
	}

	/**
	 * Gets the test recipient.
	 *
	 * @since 3.3.0
	 * @return string
	 */
	protected function get_test_recipient() {
		return edd_get_admin_notice_emails();
	}
}
