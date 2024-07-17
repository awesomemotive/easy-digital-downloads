<?php
/**
 * The Stripe Early Fraud Warning Email.
 *
 * @since 3.3.0
 * @package EDD
 * @subpackage Emails\Types
 */

namespace EDD\Emails\Types;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

/**
 * Class StripeEarlyFraudWarning
 *
 * @since 3.3.0
 * @package EDD
 * @subpackage Emails
 */
class StripeEarlyFraudWarning extends Email {

	/**
	 * The email ID.
	 *
	 * @var string
	 * @since 3.3.0
	 */
	protected $id = 'stripe_early_fraud_warning';

	/**
	 * The email context.
	 *
	 * @var string
	 * @since 3.3.0
	 */
	protected $context = 'order';

	/**
	 * The email recipient type.
	 *
	 * @var string
	 * @since 3.3.0
	 */
	protected $recipient_type = 'admin';

	/**
	 * The order object.
	 *
	 * @var EDD\Orders\Order
	 * @since 3.3.0
	 */
	protected $order;

	/**
	 * The order ID.
	 *
	 * @var int
	 * @since 3.3.0
	 */
	protected $order_id;

	/**
	 * AdminOrderNotice constructor.
	 *
	 * @since 3.3.0
	 *
	 * @param \EDD_Order $order The order object.
	 */
	public function __construct( $order ) {
		$this->order    = $order;
		$this->order_id = false !== $order ? $order->id : 0;
	}

	/**
	 * Set the raw email body content.
	 *
	 * This will add the email content to the `raw_body_content` property. It has not yet had
	 * the tag replacements executed.
	 *
	 * @since 3.3.0
	 * @return void
	 */
	protected function set_email_body_content() {
		$this->raw_body_content = $this->get_email()->content;
	}

	/**
	 * Set the email to address.
	 *
	 * @since 3.3.0
	 * @return void
	 */
	protected function set_to_email() {
		$this->send_to = $this->get_email()->get_admin_recipient_emails( $this->order );
	}

	/**
	 * Set the email subject.
	 *
	 * @since 3.3.0
	 * @return void
	 */
	protected function set_subject() {
		parent::set_subject();

		$this->subject = $this->process_tags( $this->subject, $this->order_id, $this->order );
	}

	/**
	 * Set the heading on the email.
	 *
	 * @since 3.3.0
	 * @return void
	 */
	protected function set_heading() {
		parent::set_heading();

		$this->heading = $this->process_tags( $this->heading, $this->order_id, $this->order );
	}

	/**
	 * Set the email message.
	 *
	 * @since 3.3.0
	 * @return void
	 */
	protected function set_message() {
		parent::set_message();

		// We don't want admins to get the users download links, so we'll set edd_email_show_links to false.
		add_filter( 'edd_email_show_links', '__return_false' );
		$this->message = $this->process_tags( $this->message, $this->order_id, $this->order );
		remove_filter( 'edd_email_show_links', '__return_false' );
	}
}
