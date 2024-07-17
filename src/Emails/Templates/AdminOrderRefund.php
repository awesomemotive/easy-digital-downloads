<?php

namespace EDD\Emails\Templates;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

/**
 * Class AdminOrderRefund
 *
 * @since 3.3.0
 * @package EDD\Emails\Templates
 */
class AdminOrderRefund extends EmailTemplate {

	/**
	 * Whether the email can be previewed.
	 *
	 * @since 3.3.0
	 * @var bool
	 */
	protected $can_preview = true;

	/**
	 * Whether a test email can be sent.
	 *
	 * @since 3.3.0
	 * @var bool
	 */
	protected $can_test = true;

	/**
	 * Unique identifier for this template.
	 *
	 * @since 3.3.0
	 * @var string
	 */
	protected $email_id = 'admin_order_refund';

	/**
	 * The email recipient.
	 *
	 * @since 3.3.0
	 * @var string
	 */
	protected $recipient = 'admin';

	/**
	 * The email context.
	 *
	 * @since 3.3.0
	 * @var string
	 */
	protected $context = 'refund';

	/**
	 * The email meta.
	 *
	 * @var array
	 * @since 3.3.0
	 */
	protected $meta = array(
		'recipients' => '',
	);

	/**
	 * Name of the template.
	 *
	 * @since 3.3.0
	 * @return string
	 */
	public function get_name() {
		return __( 'Admin Refund Notification', 'easy-digital-downloads' );
	}

	/**
	 * Description of the email.
	 *
	 * @since 3.3.0
	 * @return string
	 */
	public function get_description() {
		return __( 'Text to email to admin(s) after issuing a refund.', 'easy-digital-downloads' );
	}

	/**
	 * Define the default email properties.
	 *
	 * @since 3.3.0
	 * @return array
	 */
	public function defaults(): array {
		return array(
			'subject' => __( 'An order has been refunded', 'easy-digital-downloads' ),
			'content' => $this->get_default_content(),
			'status'  => 0,
		);
	}

	/**
	 * Gets the email preview data.
	 *
	 * @since 3.3.0
	 * @return array
	 */
	protected function get_preview_data() {
		$refund_id = Previews\Data::get_refund_id();
		$refund    = edd_get_order( $refund_id );

		return $refund ?
			array(
				$refund,
				$refund->parent,
			) :
			array();
	}

	/**
	 * The email properties that can be edited.
	 *
	 * @return array
	 */
	protected function get_editable_properties(): array {
		return array(
			'content',
			'subject',
			'status',
			'recipient',
		);
	}

	/**
	 * Gets the default email content.
	 *
	 * @since 3.3.0
	 * @return string
	 */
	private function get_default_content() {
		/* translators: %s: The email tag that will be replaced with the order ID. */
		return sprintf( __( 'Order %s has been refunded.', 'easy-digital-downloads' ), '{payment_id}' );
	}
}
