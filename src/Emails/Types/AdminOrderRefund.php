<?php

namespace EDD\Emails\Types;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

/**
 * Class AdminOrderRefund
 *
 * @since 3.3.0
 * @package EDD\Emails\Types
 */
class AdminOrderRefund extends Email {

	/**
	 * The email ID.
	 *
	 * @var string
	 * @since 3.3.0
	 */
	protected $id = 'admin_order_refund';

	/**
	 * The email context.
	 *
	 * @var string
	 * @since 3.3.0
	 */
	protected $context = 'refund';

	/**
	 * The email recipient type.
	 *
	 * @var string
	 * @since 3.3.0
	 */
	protected $recipient_type = 'admin';

	/**
	 * The original order.
	 *
	 * @var EDD\Orders\Order
	 * @since 3.3.0
	 */
	protected $order;

	/**
	 * The original order ID.
	 *
	 * @var int
	 * @since 3.3.0
	 */
	protected $order_id;

	/**
	 * The refund.
	 *
	 * @var \EDD\Orders\Order
	 * @since 3.3.0
	 */
	protected $refund;

	/**
	 * AdminOrderRefund constructor.
	 *
	 * @since 3.3.0
	 *
	 * @param EDD\Orders\Order $refund   The refund object.
	 * @param int              $order_id The original order ID.
	 * @return void
	 */
	public function __construct( $refund, $order_id ) {
		$this->refund   = $refund;
		$this->order_id = $order_id;
		$this->order    = edd_get_order( $order_id );
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
	 * Set the email message.
	 *
	 * @since 3.3.0
	 * @return void
	 */
	protected function set_message() {
		parent::set_message();

		$this->message = $this->process_tags( $this->message, $this->order_id, $this->order );
		$this->message = $this->process_tags( $this->message, $this->refund->id, $this->refund );
	}
}
