<?php

namespace EDD\Emails\Templates;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

/**
 * Class AdminOrderNotice
 *
 * @since 3.3.0
 * @package EDD\Emails\Templates
 */
class AdminOrderNotice extends EmailTemplate {

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
	protected $email_id = 'admin_order_notice';

	/**
	 * The email recipient.
	 *
	 * @since 3.3.0
	 * @var string
	 */
	protected $recipient = 'admin';

	/**
	 * The email meta.
	 *
	 * @since 3.3.0
	 * @var array
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
		return __( 'Admin Sale Notification', 'easy-digital-downloads' );
	}

	/**
	 * Description of the email.
	 *
	 * @since 3.3.0
	 * @return string
	 */
	public function get_description() {
		return __(
			'Text to email as a notification for every completed purchase. Personalize with HTML and <code>{tag}</code> markers.',
			'easy-digital-downloads'
		);
	}

	/**
	 * Define the default email properties.
	 *
	 * @since 3.3.0
	 * @return array
	 */
	public function defaults(): array {
		return array(
			/* translators: %s: The email tag that will be replaced with the payment ID. */
			'subject'    => sprintf( __( 'New download purchase - Order #%s', 'easy-digital-downloads' ), '{payment_id}' ),
			'heading'    => __( 'New Sale!', 'easy-digital-downloads' ),
			'content'    => $this->get_default_content(),
			'status'     => 1,
			'recipients' => 'admin',
		);
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
			'heading',
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
		$content  = __( 'Hello', 'easy-digital-downloads' );
		$content .= "\n\n";

		/* translators: %s: The plural label for the Download post type. */
		$content .= sprintf( __( 'A %s purchase has been made', 'easy-digital-downloads' ), edd_get_label_plural() );
		$content .= ".\n\n";

		/* translators: %s: The plural label for the Download post type. */
		$content .= sprintf( __( '%s sold:', 'easy-digital-downloads' ), edd_get_label_plural() ) . "\n\n";

		$content .= '{download_list}' . "\n\n";

		/* translators: %s: The email tag that will be replaced by the customer's full name */
		$content .= sprintf( __( 'Purchased by: %s', 'easy-digital-downloads' ), '{fullname}' ) . "\n";

		/* translators: %s: The email tag that will be replaced by the order total. */
		$content .= sprintf( _x( 'Amount: %s', 'Context: This is a tag (placholder) for email content that will be replaced when sending.', 'easy-digital-downloads' ), '{price}' ) . "\n";

		/* translators: %s: The email tag that will be replaced by the payment method. */
		$content .= sprintf( __( 'Payment Method: %s', 'easy-digital-downloads' ), '{payment_method}' ) . "\n\n";

		$content .= __( 'Thank you', 'easy-digital-downloads' );

		return $content;
	}

	/* Legacy */
	/**
	 * Gets the option names for this email.
	 *
	 * @since 3.3.0
	 * @return array
	 */
	protected function get_options(): array {
		return array(
			'content'  => 'sale_notification',
			'subject'  => 'sale_notification_subject',
			'heading'  => 'sale_notification_heading',
			'disabled' => 'disable_admin_notices',
		);
	}
}
