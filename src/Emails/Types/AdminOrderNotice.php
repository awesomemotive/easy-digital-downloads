<?php
/**
 * The AdminOrderNotice Email.
 *
 * @since 3.2.0
 * @package EDD
 * @subpackage Emails
 */

namespace EDD\Emails\Types;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

/**
 * Class AdminOrderNotice
 *
 * @since 3.2.0
 * @package EDD
 * @subpackage Emails
 */
class AdminOrderNotice extends Email {
	use LegacyPaymentFilters;

	/**
	 * The email ID.
	 *
	 * @var string
	 * @since 3.2.0
	 */
	protected $id = 'admin_order_notice';

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
	protected $recipient_type = 'admin';

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
	 * AdminOrderNotice constructor.
	 *
	 * @since 3.2.0
	 *
	 * @param \EDD_Order $order The order object.
	 */
	public function __construct( $order ) {
		$this->order    = $order;
		$this->order_id = false !== $order ? $order->id : 0;

		// Setup any of the legacy filters we need to run.
		$this->set_legacy_filters();
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
		$this->raw_body_content = $this->get_email()->content;

		$this->maybe_run_legacy_filter( 'edd_sale_notification' );

		$this->raw_body_content = apply_filters( 'edd_admin_order_notification', $this->raw_body_content, $this->order );
	}

	/**
	 * Set the email from name.
	 *
	 * @since 3.2.0
	 * @return void
	 */
	protected function set_from_name() {
		parent::set_from_name();

		$this->maybe_run_legacy_filter( 'edd_purchase_from_name' );

		$this->from_name = apply_filters( 'edd_order_admin_notice_from_name', $this->from_name, $this->order );
	}

	/**
	 * Set the email from address.
	 *
	 * @since 3.2.0
	 * @return void
	 */
	protected function set_from_email() {
		parent::set_from_email();

		$this->maybe_run_legacy_filter( 'edd_admin_sale_from_address' );

		$this->from_email = apply_filters( 'edd_order_admin_notice_from_email', $this->from_email, $this->order );
	}

	/**
	 * Set the email to address.
	 *
	 * @since 3.2.0
	 * @return void
	 */
	protected function set_to_email() {
		$this->send_to = $this->get_email()->get_admin_recipient_emails( $this->order );
	}

	/**
	 * Set the email headers.
	 *
	 * @since 3.2.0
	 * @return void
	 */
	protected function set_headers() {
		parent::set_headers();

		$this->maybe_run_legacy_filter( 'edd_admin_sale_notification_headers' );

		$this->headers = apply_filters( 'edd_order_admin_notice_headers', $this->headers, $this->order );
	}

	/**
	 * Set the email subject.
	 *
	 * @since 3.2.0
	 * @return void
	 */
	protected function set_subject() {
		parent::set_subject();

		$this->maybe_run_legacy_filter( 'edd_admin_sale_notification_subject' );

		$this->subject = apply_filters( 'edd_order_admin_notice_subject', $this->subject, $this->order );
		$this->subject = $this->process_tags( $this->subject, $this->order_id, $this->order );
	}

	/**
	 * Set the email heading.
	 *
	 * @since 3.2.0
	 * @return void
	 */
	protected function set_heading() {
		parent::set_heading();

		$this->maybe_run_legacy_filter( 'edd_admin_sale_notification_heading' );

		$this->heading = apply_filters( 'edd_order_admin_notice_heading', $this->heading, $this->order );
		$this->heading = $this->process_tags( $this->heading, $this->order_id, $this->order );
	}

	/**
	 * Set the email message.
	 *
	 * @since 3.2.0
	 * @return void
	 */
	protected function set_message() {
		parent::set_message();

		// We don't want admins to get the users download links, so we'll set edd_email_show_links to false.
		add_filter( 'edd_email_show_links', '__return_false' );
		$this->message = $this->process_tags( $this->message, $this->order_id, $this->order );
		remove_filter( 'edd_email_show_links', '__return_false' );
	}

	/**
	 * Set the email attachments.
	 *
	 * @since 3.2.0
	 * @return void
	 */
	protected function set_attachments() {
		$this->attachments = array();

		$this->maybe_run_legacy_filter( 'edd_admin_sale_notification_attachments' );

		$this->attachments = apply_filters( 'edd_order_admin_notice_attachments', $this->attachments, $this->order );
	}

	/**
	 * Allows filtering to disable sending the admin sale notification.
	 *
	 * @since 3.2.0
	 *
	 * @return bool
	 */
	protected function should_send() {

		// Emails should not be sent for imported orders.
		if ( edd_get_order_meta( $this->order->id, '_edd_imported', true ) ) {
			return false;
		}

		if ( ! parent::should_send() ) {
			return false;
		}

		// Allows disabling this email by filter.
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
		$this->legacy_filters = array(
			'edd_sale_notification'                   => array(
				'has_filter' => has_filter( 'edd_sale_notification' ),
				'property'   => 'raw_body_content',
				'arguments'  => array( 'order_id', 'payment_meta' ),
			),
			'edd_purchase_from_name'                  => array(
				'has_filter' => has_filter( 'edd_purchase_from_name' ),
				'property'   => 'from_name',
				'arguments'  => array( 'order_id', 'payment_meta' ),
			),
			'edd_admin_sale_from_address'             => array(
				'has_filter' => has_filter( 'edd_admin_sale_from_address' ),
				'property'   => 'from_email',
				'arguments'  => array( 'order_id' ),
			),
			'edd_admin_sale_notification_heading'     => array(
				'has_filter' => has_filter( 'edd_admin_sale_notification_heading' ),
				'property'   => 'heading',
				'arguments'  => array( 'order_id', 'payment_meta' ),
			),
			'edd_admin_sale_notification_subject'     => array(
				'has_filter' => has_filter( 'edd_admin_sale_notification_subject' ),
				'property'   => 'subject',
				'arguments'  => array( 'order_id' ),
			),
			'edd_admin_sale_notification_attachments' => array(
				'has_filter' => has_filter( 'edd_admin_sale_notification_attachments' ),
				'property'   => 'headers',
				'arguments'  => array( 'order_id', 'payment_meta' ),
			),
			'edd_admin_sale_notification_headers'     => array(
				'has_filter' => has_filter( 'edd_admin_sale_notification_headers' ),
				'property'   => 'raw_body_content',
				'arguments'  => array( 'order_id', 'payment_meta' ),
			),
		);
	}
}
