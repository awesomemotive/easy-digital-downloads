<?php
/**
 * The Stripe Early Fraud Warning Email Template.
 *
 * @since 3.3.0
 *
 * @package EDD
 * @subpackage Emails\Templates
 */

namespace EDD\Emails\Templates;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

/**
 * Class StripeEarlyFraudWarning
 *
 * @since 3.3.0
 */
class StripeEarlyFraudWarning extends EmailTemplate {

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
	protected $email_id = 'stripe_early_fraud_warning';

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
	protected $context = 'order';

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
		return __( 'Stripe Early Fraud Warning', 'easy-digital-downloads' );
	}

	/**
	 * Description of the email.
	 *
	 * @since 3.3.0
	 * @return string
	 */
	public function get_description() {
		return __(
			"Be alerted when an early fraud warning is detected by Stripe's machine learning. Avoid disputes before they even happen by reviewing flagged orders to verify them.",
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
			/* translators: %s: Email tag that will be replaced with the order ID. */
			'subject' => sprintf( __( 'Stripe Early Fraud Warning - Order #%s', 'easy-digital-downloads' ), '{payment_id}' ),
			'heading' => __( 'Possible Fraudulent Order', 'easy-digital-downloads' ),
			'content' => $this->get_default_content(),
			'status'  => edd_is_gateway_active( 'stripe' ) ? 1 : 0,
		);
	}

	/**
	 * Gets the content for the status tooltip, if needed.
	 *
	 * @since 3.3.0
	 * @return array
	 */
	public function get_status_tooltip(): array {
		if ( $this->can_edit( 'status' ) ) {
			return array();
		}

		return array(
			'content'  => __( 'This email is only available if the Stripe gateway is enabled and using the Payment Elements mode.', 'easy-digital-downloads' ),
			'dashicon' => 'dashicons-lock',
		);
	}

	/**
	 * This email cannot be activated if the Stripe gateway is not active.
	 *
	 * @since 3.3.0
	 * @return bool
	 */
	public function are_base_requirements_met(): bool {
		return edd_is_gateway_active( 'stripe' ) && 'payment-elements' === edds_get_elements_mode();
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
		/* translators: %s: The plural label for the Download post type. */
		$content  = __( 'Hello', 'easy-digital-downloads' );
		$content .= "\n\n";
		$content .= __( 'Stripe has detected a potential fraudulent order.', 'easy-digital-downloads' );
		$content .= "\n\n";
		/* translators: %s: The plural label for the Download post type. */
		$content .= sprintf( __( '%s sold:', 'easy-digital-downloads' ), edd_get_label_plural() ) . "\n\n";
		$content .= '{download_list}' . "\n\n";
		/* translators: %s: The email tag that will be replaced by the customer's full name */
		$content .= sprintf( __( 'Purchased by: %s', 'easy-digital-downloads' ), '{fullname}' ) . "\n";
		/* translators: %s: The email tag that will be replaced by the order total. */
		$content .= sprintf( _x( 'Amount: %s', 'Context: This is a tag (placholder) for email content that will be replaced when sending.', 'easy-digital-downloads' ), '{price}' ) . "\n";
		/* translators: 1: The opening anchor tag, 2: The closing anchor tag */
		$content .= sprintf( __( '%1$sOrder Details%2$s', 'easy-digital-downloads' ), '<a href="{order_details_link}">', '</a>' ) . "\n\n";
		$content .= __( 'Note: Once you have reviewed the order, ensure you take the appropriate action within your Stripe dashboard to help improve future fraud detection.', 'easy-digital-downloads' );

		return $content;
	}
}
