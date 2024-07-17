<?php

namespace EDD\Emails\Templates;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

/**
 * Class OrderReceipt
 *
 * @since 3.3.0
 * @package EDD\Emails\Templates
 */
class OrderReceipt extends EmailTemplate {

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
	protected $email_id = 'order_receipt';

	/**
	 * The email recipient.
	 *
	 * @since 3.3.0
	 * @var string
	 */
	protected $recipient = 'customer';

	/**
	 * Name of the template.
	 *
	 * @since 3.3.0
	 * @return string
	 */
	public function get_name() {
		return __( 'Purchase Receipt', 'easy-digital-downloads' );
	}

	/**
	 * Description of the email.
	 *
	 * @since 3.3.0
	 * @return string
	 */
	public function get_description() {
		return __( 'Text to email customers after completing a purchase. Personalize with HTML and <code>{tag}</code> markers.', 'easy-digital-downloads' );
	}

	/**
	 * Define the default email properties.
	 *
	 * @since 3.3.0
	 * @return array
	 */
	public function defaults(): array {
		return array(
			'subject' => __( 'Purchase Receipt', 'easy-digital-downloads' ),
			'heading' => __( 'Purchase Receipt', 'easy-digital-downloads' ),
			'content' => $this->get_default_content(),
			'status'  => 1,
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
		);
	}

	/**
	 * Gets the default email body content.
	 *
	 * @since 3.3.0
	 * @return string
	 */
	private function get_default_content() {
		/* translators: %s: Email tag that will be replaced with the customer name */
		$content  = sprintf( _x( 'Dear %s,', 'Context: This is an email tag (placeholder) that will be replaced at the time of sending the email', 'easy-digital-downloads' ), '{name}' ) . "\n\n";
		$content .= __( 'Thank you for your purchase. Please click on the link(s) below to download your files.', 'easy-digital-downloads' ) . "\n\n";
		$content .= '{download_list}' . "\n\n";
		$content .= '{sitename}';

		return $content;
	}

	/* Legacy properties */
	/**
	 * Gets the option names for this email.
	 *
	 * @since 3.3.0
	 * @return array
	 */
	protected function get_options(): array {
		return array(
			'content' => 'purchase_receipt',
			'subject' => 'purchase_subject',
			'heading' => 'purchase_heading',
		);
	}
}
