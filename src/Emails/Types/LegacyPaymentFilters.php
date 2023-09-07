<?php
/**
 * Traits for email types that require possibly running legacy filters for Payments.
 *
 * Use this trait on your class that extends EDD\Emails\Types\Email, when you are working towards deprecating
 * filers or want to conditionally load filters that use legacy EDD_Payment objects.
 *
 * @since 3.2.0
 * @package EDD
 * @subpackage Emails
 */

namespace EDD\Emails\Types;

trait LegacyPaymentFilters {

	/**
	 * The legacy filters that are being used.
	 * @var array
	 * @since 3.2.0
	 */
	private $legacy_filters;

	/**
	 * The payment object.
	 * @var \EDD_Payment
	 * @since 3.2.0
	 */
	private $payment_object;

	/**
	 * The payment meta.
	 * @var array
	 * @since 3.2.0
	 */
	private $payment_meta;

	/**
	 * The legacy filters that are being used.
	 *
	 * Example:
	 * array(
	 *     'filter_hook' => array(
	 *         'has_filter' => has_filter( 'filter_hook' ),
	 *         'property'   => 'property_filter_changes',
	 *         'arguments'  => array( 'property_one', 'property_two' ),
	 *     ),
	 * );
	 *
	 * @see EDD\Emails\Types\OrderReceipt::set_legacy_filters() for an example.
	 *
	 * @since 3.2.0
	 */
	abstract protected function set_legacy_filters();

	/**
	 * Maybe run a legacy filter.
	 *
	 * If the legacy filter is run, the property it modifies will be updated.
	 *
	 * @since 3.2.0
	 *
	 * @param string $hook The hook to check.
	 *
	 * @return void
	 */
	private function maybe_run_legacy_filter( $hook = '' ) {
		if ( ! $this->is_legacy_filter_used( $hook ) ) {
			return;
		}

		$filter_properties = $this->legacy_filters[ $hook ];

		$property  = $filter_properties['property'];
		$value     = $this->$property;
		$arguments = array();
		foreach ( $filter_properties['arguments'] as $argument ) {
			// Ensure we set the payment data, only if we need it.
			if ( 'payment_meta' === $argument ) {
				$this->set_payment_data();
			}

			$arguments[] = $this->$argument;
		}

		$this->$property = apply_filters_ref_array( $hook, array_merge( array( $value ), $arguments ) );
	}

	/**
	 * Check if a legacy filter is being used.
	 *
	 * @since 3.2.0
	 * @param string $filter The filter to check.
	 *
	 * @return bool
	 */
	private function is_legacy_filter_used( $filter = '' ) {
		if ( empty( $filter ) ) {
			return false;
		}

		return array_key_exists( $filter, $this->legacy_filters ) && $this->legacy_filters[ $filter ]['has_filter'];
	}

	/**
	 * Sets the payment object on the class.
	 *
	 * @since 3.2.0
	 *
	 * @return void
	 */
	private function set_payment_data() {
		if ( $this->payment_object instanceof \EDD_Payment && ! empty( $this->payment_meta ) ) {
			return;
		}

		$this->payment_object = new \EDD_Payment( $this->order_id );
		$this->payment_meta   = $this->payment_object->get_meta( '_edd_payment_meta', true );
	}
}
