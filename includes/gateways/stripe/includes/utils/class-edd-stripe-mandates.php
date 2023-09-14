<?php

/**
 * Adds support for Stripe Mandates.
 *
 * Mandates are added to the Payment and Setup Intents whenever a subscription is being purchased
 * to accommodate for banking regulations to assist in renewal payments.
 *
 * @since 2.9.2.2
 */
class EDD_Stripe_Mandates {
	/**
	 * The purchase data.
	 *
	 * @since 2.9.2.2
	 * @var array
	 */
	protected $purchase_data = array();

	/**
	 * The intent type.
	 *
	 * @since 2.9.2.2
	 * @var string
	 */
	protected $intent_type = '';

	/**
	 * The amount to charge.
	 *
	 * @since 2.9.2.2
	 * @var int
	 */
	protected $amount = 0;

	/**
	 * The currency to charge.
	 *
	 * @since 2.9.2.2
	 * @var string
	 */
	protected $currency = '';

	/**
	 * The amount type.
	 *
	 * @since 2.9.2.2
	 * @var string
	 */
	protected $amount_type = 'maximum';

	/**
	 * The interval.
	 *
	 * @since 2.9.2.2
	 * @var string
	 */
	protected $interval = '';

	/**
	 * The interval count.
	 *
	 * @since 2.9.2.2
	 * @var int
	 */
	protected $interval_count = 0;

	/**
	 * The reference.
	 *
	 * @since 2.9.2.2
	 * @var string
	 */
	protected $reference = '';

	/**
	 * The mandate options.
	 *
	 * @since 2.9.2.2
	 * @var array
	 */
	public $mandate_options = array();

	/**
	 * Instaniate the class to generate mandates.
	 *
	 * @since 2.9.2.2
	 *
	 * @param array  $purchase_data The purchase data.
	 * @param string $intent_type   The intent type.
	 */
	public function __construct( $purchase_data = array(), $intent_type = 'PaymentIntent' ) {
		// Save the purchase data locally, for use later.
		$this->purchase_data = $purchase_data;
		$this->intent_type   = $intent_type;

		$this->amount    = $this->format_amount();
		$this->currency  = edd_get_currency();
		$this->reference = $purchase_data['purchase_key'];

		// Generate the interval and interval count.
		$this->get_interval_and_count( $purchase_data );

		// Now that all the data has been determined, generate the mandate options.
		$this->generate_mandate_arguments();
	}

	/**
	 * Formats the amount into a Stripe-friendly format.
	 *
	 * @since 2.9.2.2
	 *
	 * @return int The formatted amount.
	 */
	private function format_amount() {
		$amount = $this->purchase_data['price'];

		if ( edds_is_zero_decimal_currency() ) {
			return $amount;
		}

		return round( $amount * 100, 0 );
	}

	/**
	 * Gets the interval and interval count for the mandate.
	 *
	 * @since 2.9.2.2
	 */
	private function get_interval_and_count() {
		/**
		 * Setup intervals based on the Recurring Payment periods.
		 *
		 * We use a foreach here, but with Payment Elements, it's only a single subscription, we just
		 * want to properly itterate on them.
		 */
		$period = false;
		foreach ( $this->purchase_data['downloads'] as $download ) {

			// This is a non-recurring download. Move along.
			if ( ! isset( $download['options']['recurring'] ) ) {
				continue;
			}

			$period = $download['options']['recurring']['period'];
			break;
		}

		// Setup intervals for the mandate based on the Recurring Payment periods.
		switch ( $period ) {
			case 'day':
				$interval       = 'day';
				$interval_count = 1;
				break;
			case 'week':
				$interval       = 'week';
				$interval_count = 1;
				break;
			case 'month':
				$interval       = 'month';
				$interval_count = 1;
				break;
			case 'quarter':
				$interval       = 'month';
				$interval_count = 3;
				break;
			case 'semi-year':
				$interval       = 'month';
				$interval_count = 6;
				break;
			case 'year':
				$interval       = 'year';
				$interval_count = 1;
				break;
			default:
				$interval       = 'sporadic';
				$interval_count = false;
				break;
		}

		$this->interval = $interval;

		if ( false !== $interval_count ) {
			$this->interval_count = $interval_count;
		}
	}

	/**
	 * Generates the mandate options for use with an intent.
	 *
	 * @since 2.9.2.2
	 */
	private function generate_mandate_arguments() {
		$mandate_options = array(
			'reference'       => $this->reference,
			'amount'          => $this->amount,
			'start_date'      => current_time( 'timestamp' ),
			'amount_type'     => 'maximum',
			'supported_types' => array( 'india' ),
			'interval'        => $this->interval,
		);

		if ( false !== $this->interval_count ) {
			$mandate_options['interval_count'] = $this->interval_count;
		}

		// SetupIntent types require the currency to be passed with the mandate_options.
		if ( 'SetupIntent' === $this->intent_type ) {
			$mandate_options['currency'] = edd_get_currency();
		}

		/**
		 * Alllows further customization of the mandate options sent with the intent.
		 *
		 * @since 2.9.2.2
		 *
		 * @param array  $mandate_options The set of mandate options we've generated.
		 * @param array  $purchase_data   The purchase data being processed.
		 * @param string $intent_type     The intent type (either SetupIntent or PaymentIntent).
		 */
		$mandate_options = apply_filters( 'edds_mandate_options', $mandate_options, $this->purchase_data, $this->intent_type );

		$this->mandate_options = $mandate_options;
	}
}
