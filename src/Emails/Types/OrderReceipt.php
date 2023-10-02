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
		$option_value           = edd_get_option( 'purchase_receipt', false );
		$this->raw_body_content = $option_value ? stripslashes( $option_value ) : $this->get_default_body_content();

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
		$this->from_name = edd_get_option( 'from_name', wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ) );

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
		$this->from_email = edd_get_option( 'from_email', get_bloginfo( 'admin_email' ) );

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
		$this->headers = $this->processor()->get_headers();

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
		$this->subject = edd_get_option( 'purchase_subject', __( 'Purchase Receipt', 'easy-digital-downloads' ) );

		$this->maybe_run_legacy_filter( 'edd_purchase_subject' );

		$this->subject = apply_filters( 'edd_order_receipt_email_subject', $this->subject, $this->order );
		$this->subject = wp_strip_all_tags( $this->process_tags( $this->subject, $this->order_id ) );
	}

	/**
	 * Set the heading on the email.
	 *
	 * @since 3.2.0
	 * @return void
	 */
	protected function set_heading() {
		$this->heading = edd_get_option( 'purchase_heading', __( 'Purchase Receipt', 'easy-digital-downloads' ) );

		$this->maybe_run_legacy_filter( 'edd_purchase_heading' );

		$this->heading = apply_filters( 'edd_order_receipt_email_heading', wp_strip_all_tags( $this->heading ), $this->order );
		$this->heading = $this->process_tags( $this->heading, $this->order_id );
	}

	/**
	 * Set the message on the email.
	 *
	 * @since 3.2.0
	 * @return void
	 */
	protected function set_message() {
		$message = $this->get_raw_body_content();

		$this->message = $this->process_tags( $this->maybe_apply_autop( $message ), $this->order_id );
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
	 * Get the default email body content.
	 *
	 * @since 3.2.0
	 * @return string
	 */
	public function get_default_body_content() {
		$default_email_body  = __( 'Dear', 'easy-digital-downloads' ) . " {name},\n\n";
		$default_email_body .= __( 'Thank you for your purchase. Please click on the link(s) below to download your files.', 'easy-digital-downloads' ) . "\n\n";
		$default_email_body .= '{download_list}' . "\n\n";
		$default_email_body .= '{sitename}';

		return $default_email_body;
	}

	/**
	 * Allows filtering to disable sending the default order receipt email.
	 *
	 * @since 3.2.0
	 *
	 * @return bool
	 */
	protected function should_send() {
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
}
