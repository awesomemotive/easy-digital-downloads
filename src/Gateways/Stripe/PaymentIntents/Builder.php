<?php
/**
 * Payment Intent Builder
 *
 * Handles the creation and updating of Stripe Payment Intents and Setup Intents.
 *
 * @package EDD\Gateways\Stripe\PaymentIntents
 * @since 3.6.1
 */

namespace EDD\Gateways\Stripe\PaymentIntents;

use EDD\Gateways\Stripe\PaymentIntents\AmountDetails;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

/**
 * Payment Intent Builder class.
 *
 * @since 3.6.1
 */
class Builder {
	use \EDD\Gateways\Stripe\Checkout\Traits\Recurring;

	/**
	 * The purchase data.
	 *
	 * @since 3.6.1
	 * @var array
	 */
	private $purchase_data;

	/**
	 * The payment method.
	 *
	 * @since 3.6.1
	 * @var array
	 */
	private $payment_method;

	/**
	 * The Stripe customer.
	 *
	 * @since 3.6.1
	 * @var \EDD\Vendor\Stripe\Customer
	 */
	private $customer;

	/**
	 * The calculated amount (in cents for non-zero-decimal currencies).
	 *
	 * @since 3.6.1
	 * @var int
	 */
	private $amount;

	/**
	 * The intent type (PaymentIntent or SetupIntent).
	 *
	 * @since 3.6.1
	 * @var string
	 */
	private $intent_type;

	/**
	 * The existing intent (if updating).
	 *
	 * @since 3.6.1
	 * @var \EDD\Vendor\Stripe\PaymentIntent|\EDD\Vendor\Stripe\SetupIntent|null
	 */
	private $existing_intent;

	/**
	 * Constructor.
	 *
	 * @since 3.6.1
	 * @param array                       $purchase_data  The purchase data.
	 * @param array                       $payment_method The payment method array.
	 * @param \EDD\Vendor\Stripe\Customer $customer       The Stripe customer object.
	 * @param int                         $amount         The amount in cents.
	 */
	public function __construct( $purchase_data, $payment_method, $customer, $amount ) {
		$this->purchase_data  = $purchase_data;
		$this->payment_method = $payment_method;
		$this->customer       = $customer;
		$this->amount         = $amount;
		$this->intent_type    = $this->determine_intent_type();
	}

	/**
	 * Set an existing intent for updates.
	 *
	 * @since 3.6.1
	 * @param \EDD\Vendor\Stripe\PaymentIntent|\EDD\Vendor\Stripe\SetupIntent $intent The existing intent.
	 * @return void
	 */
	public function set_existing_intent( $intent ) {
		$this->existing_intent = $intent;
	}

	/**
	 * Create a new intent.
	 *
	 * @since 3.6.1
	 * @return \EDD\Vendor\Stripe\PaymentIntent|\EDD\Vendor\Stripe\SetupIntent
	 * @throws \EDD\Vendor\Stripe\Exception\ApiErrorException If Stripe API request fails.
	 */
	public function create() {
		// Set API version if line items are present (must be done before building arguments).
		$this->maybe_update_api_version();

		$intent_args = $this->build_arguments();

		/**
		 * Allows processing before an Intent is created.
		 *
		 * @since 2.7.0
		 *
		 * @param array                       $purchase_data  Purchase data.
		 * @param array                       $intent_args    Intent arguments.
		 * @param \EDD\Vendor\Stripe\Customer $customer       Stripe Customer object.
		 */
		do_action( 'edds_payment_intent_before_create', $this->purchase_data, $intent_args, $this->customer );

		try {
			return edds_api_request( $this->intent_type, 'create', $intent_args );
		} catch ( \EDD\Vendor\Stripe\Exception\ApiErrorException $e ) {
			// Re-throw so Form.php can handle it.
			throw $e;
		}
	}

	/**
	 * Update an existing intent.
	 *
	 * @since 3.6.1
	 * @param string $intent_id The intent ID to update.
	 * @return \EDD\Vendor\Stripe\PaymentIntent|\EDD\Vendor\Stripe\SetupIntent
	 * @throws \EDD\Vendor\Stripe\Exception\ApiErrorException If Stripe API request fails.
	 */
	public function update( $intent_id ) {
		// Set API version if line items are present (must be done before building arguments).
		$this->maybe_update_api_version();

		$intent_args = $this->build_arguments();

		// Existing intents need to not have the automatic_payment_methods flag set.
		if ( ! empty( $intent_args['automatic_payment_methods'] ) ) {
			unset( $intent_args['automatic_payment_methods'] );
		}

		/**
		 * Allows processing before an Intent is updated.
		 *
		 * @since 3.6.1
		 *
		 * @param string                      $intent_id      Intent ID.
		 * @param array                       $intent_args    Intent arguments.
		 * @param array                       $purchase_data  Purchase data.
		 * @param \EDD\Vendor\Stripe\Customer $customer       Stripe Customer object.
		 */
		do_action( 'edds_payment_intent_before_update', $intent_id, $intent_args, $this->purchase_data, $this->customer );

		try {
			edds_api_request( $this->intent_type, 'update', $intent_id, $intent_args );

			return edds_api_request( $this->intent_type, 'retrieve', $intent_id );
		} catch ( \EDD\Vendor\Stripe\Exception\ApiErrorException $e ) {
			// Re-throw so Form.php can handle it.
			throw $e;
		}
	}

	/**
	 * Get the intent type.
	 *
	 * @since 3.6.1
	 * @return string The intent type (PaymentIntent or SetupIntent).
	 */
	public function get_intent_type() {
		return $this->intent_type;
	}

	/**
	 * Get the arguments for fingerprinting (without mandate options).
	 *
	 * This is used to generate a fingerprint to detect if intent arguments have changed.
	 * Mandate options are excluded because they contain timestamps.
	 *
	 * @since 3.6.1
	 * @return array The intent arguments for fingerprinting.
	 */
	public function get_arguments_for_fingerprint() {
		// Get base arguments.
		$intent_args = $this->get_base_arguments();

		// Add type-specific arguments.
		if ( 'SetupIntent' === $this->intent_type ) {
			$intent_args = $this->add_setup_intent_arguments( $intent_args );
		} else {
			$intent_args = $this->add_payment_intent_arguments( $intent_args );
		}

		// Add application fee if applicable.
		$application_fee = $this->get_application_fee();
		if ( ! empty( $application_fee ) ) {
			$intent_args['application_fee_amount'] = $application_fee;
		}

		// NOTE: We explicitly do NOT add mandate options here because they contain timestamps.
		return $intent_args;
	}

	/**
	 * Build the intent arguments.
	 *
	 * @since 3.6.1
	 * @return array The intent arguments.
	 */
	private function build_arguments() {
		// Get base arguments.
		$intent_args = $this->get_base_arguments();

		// Add type-specific arguments.
		if ( 'SetupIntent' === $this->intent_type ) {
			$intent_args = $this->add_setup_intent_arguments( $intent_args );
		} else {
			$intent_args = $this->add_payment_intent_arguments( $intent_args );
		}

		$application_fee = $this->get_application_fee();
		if ( ! empty( $application_fee ) ) {
			$intent_args['application_fee_amount'] = $application_fee;
		}

		// Add mandate options if required.
		$mandate_options = $this->get_mandate_options();
		if ( ! empty( $mandate_options ) ) {
			$intent_args['payment_method_options']['card']['mandate_options'] = $mandate_options;
		}

		return $intent_args;
	}

	/**
	 * Get base intent arguments shared by both PaymentIntent and SetupIntent.
	 *
	 * @since 3.6.1
	 * @return array Base intent arguments.
	 */
	private function get_base_arguments() {
		$intent_args = array(
			'customer'                  => $this->customer->id,
			'metadata'                  => $this->get_metadata(),
			'payment_method'            => sanitize_text_field( $this->payment_method['id'] ),
			'automatic_payment_methods' => array( 'enabled' => true ),
			'description'               => edds_get_payment_description( $this->purchase_data['cart_details'] ),
		);

		// Add payment method configuration if available.
		$payment_method_configuration = $this->get_payment_method_configuration();
		if ( ! empty( $payment_method_configuration ) ) {
			$intent_args['payment_method_configuration'] = $payment_method_configuration;
		}

		return $intent_args;
	}

	/**
	 * Get metadata for the intent.
	 *
	 * @since 3.6.1
	 * @return array Metadata array.
	 */
	private function get_metadata() {
		$metadata = array(
			'email'                => esc_html( $this->purchase_data['user_info']['email'] ),
			'edd_payment_subtotal' => esc_html( $this->purchase_data['subtotal'] ),
			'edd_payment_discount' => esc_html( $this->purchase_data['discount'] ),
			'edd_payment_tax'      => esc_html( $this->purchase_data['tax'] ),
			'edd_payment_tax_rate' => esc_html( $this->purchase_data['tax_rate'] ),
			'edd_payment_fees'     => esc_html( edd_get_cart_fee_total() ),
			'edd_payment_total'    => esc_html( $this->purchase_data['price'] ),
			'edd_payment_items'    => esc_html( $this->get_payment_items_string() ),
			'zero_decimal_amount'  => $this->amount,
		);

		/**
		 * Filters the metadata for the intent.
		 *
		 * @since 3.6.1
		 *
		 * @param array $metadata      Metadata array.
		 * @param array $purchase_data Purchase data.
		 */
		return apply_filters( 'edds_payment_intent_metadata', $metadata, $this->purchase_data );
	}

	/**
	 * Get payment items as a comma-separated string.
	 *
	 * @since 3.6.1
	 * @return string Comma-separated payment items.
	 */
	private function get_payment_items_string() {
		$payment_items = array();

		// Create a list of {$download_id}_{$price_id}.
		foreach ( $this->purchase_data['cart_details'] as $item ) {
			$item_id = $item['id'];
			if ( isset( $item['item_number']['options']['price_id'] ) ) {
				$price_id = $item['item_number']['options']['price_id'];
				$item_id .= '_' . intval( $price_id );
			}

			$payment_items[] = $item_id;
		}

		return implode( ', ', $payment_items );
	}

	/**
	 * Add SetupIntent-specific arguments.
	 *
	 * @since 3.6.1
	 * @param array $intent_args Base intent arguments.
	 * @return array Updated intent arguments.
	 */
	private function add_setup_intent_arguments( $intent_args ) {
		$intent_args['usage'] = 'off_session';

		/**
		 * Filters the arguments used to create a SetupIntent.
		 *
		 * @since 2.7.0
		 *
		 * @param array $intent_args   SetupIntent arguments.
		 * @param array $purchase_data The purchase data.
		 */
		return apply_filters( 'edds_create_setup_intent_args', $intent_args, $this->purchase_data );
	}

	/**
	 * Add PaymentIntent-specific arguments.
	 *
	 * @since 3.6.1
	 * @param array $intent_args Base intent arguments.
	 * @return array Updated intent arguments.
	 */
	private function add_payment_intent_arguments( $intent_args ) {
		$intent_args['amount']   = $this->amount;
		$intent_args['currency'] = edd_get_currency();

		$payment_method_type = $this->payment_method['type'];

		// Add amount details (line items) if enabled.
		$amount_details = $this->get_amount_details();
		if ( ! empty( $amount_details ) ) {
			$intent_args['amount_details'] = $amount_details;

			// Line items should be accompanied by payment_details for better reconciliation.
			$payment_details = array(
				'order_reference' => $this->purchase_data['purchase_key'],
			);

			// Add customer reference (email) for better reconciliation.
			if ( ! empty( $this->purchase_data['user_info']['email'] ) ) {
				$payment_details['customer_reference'] = $this->purchase_data['user_info']['email'];
			}

			$intent_args['payment_details'] = $payment_details;
		}

		// If this is a card payment method, we need to add the statement descriptor suffix.
		if ( 'card' === $payment_method_type ) {
			$statement_descriptor_suffix = \EDD\Gateways\Stripe\StatementDescriptor::sanitize_suffix( $intent_args['description'] );
			if ( ! empty( $statement_descriptor_suffix ) ) {
				$intent_args['statement_descriptor_suffix'] = $statement_descriptor_suffix;
			}
		} elseif ( 'wechat_pay' === $payment_method_type ) {
			$intent_args['payment_method_options']['wechat_pay']['client'] = 'web';
		}

		if ( $this->should_setup_future_usage( $payment_method_type ) ) {
			$intent_args['setup_future_usage'] = 'off_session';
		}

		/**
		 * Filters the arguments used to create a PaymentIntent.
		 *
		 * @since 2.7.0
		 *
		 * @param array $intent_args   PaymentIntent arguments.
		 * @param array $purchase_data The purchase data.
		 */
		$intent_args = apply_filters( 'edds_create_payment_intent_args', $intent_args, $this->purchase_data );

		/**
		 * As of Feb 1, 2024, Stripe no longer allows Statement Descriptors for PaymentIntents with cards.
		 *
		 * @since 3.2.8
		 *
		 * Because of this EDD will always default to the Stripe settings, by sending no statement descriptor.
		 * If a developer was altering it with this method, then the filters will no longer work, in order to avoid
		 * failed payments from happening.
		 *
		 * Dynamic statement descriptors can be enabled by including the Order ID in the EDD Stripe
		 */
		if ( isset( $intent_args['statement_descriptor'] ) ) {
			unset( $intent_args['statement_descriptor'] );
		}

		return $intent_args;
	}

	/**
	 * Maybe add application fee to intent arguments.
	 *
	 * @since 3.6.1
	 * @return int Application fee amount.
	 */
	private function get_application_fee() {
		if ( edd_stripe()->application_fee->has_application_fee() ) {
			return edd_stripe()->application_fee->get_application_fee_amount( $this->amount );
		}

		return 0;
	}

	/**
	 * Maybe add mandate options to intent arguments.
	 *
	 * @since 3.6.1
	 * @return array|false Mandate options.
	 */
	private function get_mandate_options() {
		if ( ! $this->is_mandate_required() ) {
			return false;
		}

		require_once EDDS_PLUGIN_DIR . 'includes/utils/class-edd-stripe-mandates.php';
		$mandates = new \EDD_Stripe_Mandates( $this->purchase_data, $this->intent_type );

		return $mandates->mandate_options;
	}

	/**
	 * Determine the intent type based on amount and settings.
	 *
	 * @since 3.6.1
	 * @return string Intent type (PaymentIntent or SetupIntent).
	 */
	private function determine_intent_type() {
		return ( 0 === $this->amount || edds_is_preapprove_enabled() ) ? 'SetupIntent' : 'PaymentIntent';
	}

	/**
	 * Check if future usage should be set up.
	 *
	 * @since 3.6.1
	 * @param string $payment_method_type The payment method type.
	 * @return bool True if future usage should be set up.
	 */
	private function should_setup_future_usage( $payment_method_type ) {
		if ( 'link' === $payment_method_type ) {
			return true;
		}

		return $this->cart_contains_subscription();
	}

	/**
	 * Get payment method configuration ID.
	 *
	 * @since 3.6.1
	 * @return string Payment method configuration ID.
	 */
	private function get_payment_method_configuration() {
		$type = '';

		if ( $this->cart_contains_subscription() ) {
			$type = 'subscriptions';

			if ( $this->cart_has_free_trial() ) {
				$type = 'trials';
			}
		}

		return \EDD\Gateways\Stripe\PaymentMethods::get_configuration_id( $type );
	}

	/**
	 * Check if a mandate is required.
	 *
	 * @since 3.6.1
	 * @return bool True if mandate is required.
	 */
	private function is_mandate_required() {
		/**
		 * Filters whether a mandate is required for the Stripe checkout form.
		 *
		 * @since 3.6.1
		 * @param bool  $mandate_required Whether a mandate is required.
		 * @param array $payment_method   The payment method.
		 */
		return apply_filters( 'edds_mandate_required', 'card' === $this->payment_method['type'], $this->payment_method );
	}

	/**
	 * Get amount details (line items) for the payment intent.
	 *
	 * @since 3.6.1
	 * @return array Amount details or empty array if line items are disabled.
	 */
	private function get_amount_details() {
		$amount_details = new AmountDetails( $this->purchase_data, $this->payment_method['type'] );

		return $amount_details->build();
	}

	/**
	 * Maybe set the Stripe API version for line items.
	 *
	 * Line items are currently in public preview and require the preview API version header.
	 * This must be called before the API request is made.
	 *
	 * The preview version is only used for creating PaymentIntents with line items;
	 * there are breaking changes for subscriptions in this version.
	 *
	 * @since 3.6.1
	 * @link https://docs.stripe.com/payments/payment-line-items
	 * @return void
	 */
	private function maybe_update_api_version() {
		// Only set API version for PaymentIntents (SetupIntents use their own version).
		if ( 'PaymentIntent' !== $this->intent_type ) {
			return;
		}

		// Check if line items will be included.
		$amount_details = new AmountDetails( $this->purchase_data, $this->payment_method['type'] );
		if ( ! $amount_details->is_enabled() ) {
			return;
		}

		/**
		 * Line items are in public preview and require the preview API version header.
		 *
		 * @link https://docs.stripe.com/payments/payment-line-items
		 */
		add_action(
			'edds_pre_stripe_api_request',
			function () {
				\EDD\Vendor\Stripe\Stripe::setApiVersion( '2025-04-30.preview' );
			},
			100
		);
	}
}
