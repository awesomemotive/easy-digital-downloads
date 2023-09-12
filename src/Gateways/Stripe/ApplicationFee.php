<?php

namespace EDD\Gateways\Stripe;

/**
 * Class ApplicationFee
 *
 * @package EDD\Stripe
 * @since 3.2.0
 */
final class ApplicationFee {

	/**
	 * The license object, if found.
	 *
	 * @var \EDD\Gateways\Stripe\License|null
	 */
	private $license;

	/**
	 * Whether the application fee should be added to the payment/setup intent.
	 *
	 * @since 3.2.0
	 * @return bool
	 */
	public function has_application_fee() {
		// Not connected (always false).
		if ( empty( edd_stripe()->connect()->get_connect_id() ) ) {
			return false;
		}

		// Not in country that supports the fees (always false).
		if ( true !== edds_stripe_connect_account_country_supports_application_fees() ) {
			return false;
		}

		// No license (always true).
		if ( ! $this->get_license() ) {
			return true;
		}

		// The license is valid (always false).
		if ( $this->license->is_license_valid() ) {
			return false;
		}

		// It's a new pro install, so false for now.
		if ( $this->license->is_in_new_install_grace_period() ) {
			return false;
		}

		// License is expired, but in the grace period, so false for now.
		if ( $this->license->is_in_grace_period() ) {
			return false;
		}

		return true;
	}

	/**
	 * Gets the application fee amount.
	 * This amount is in cents and should not include a decimal.
	 * This method does not check if the application fee should be added; it only calculates it.
	 *
	 * @since 3.2.0
	 * @param float $amount The amount.
	 *
	 * @return float
	 */
	public function get_application_fee_amount( $amount ) {
		return round( $amount * ( $this->get_application_fee_percentage() / 100 ), 0 );
	}

	/**
	 * Gets the application fee message for the settings screen.
	 *
	 * @since 3.2.0
	 * @return string
	 */
	public function get_fee_message() {
		if ( $this->get_license() && $this->license->is_license_valid() ) {
			return '';
		}

		// Message that shows before connecting to Stripe.
		if ( ! edd_doing_ajax() ) {
			return $this->get_initial_connect_message() . ' ';
		}

		$message = sprintf(
			/* translators: 1. opening strong tag; 2. closing strong tag; 3. the message explaining the application fee (eg "3% per-transaction fee + Stripe fees"). */
			__( '%1$sPay as you go pricing:%2$s %3$s.', 'easy-digital-downloads' ),
			'<strong>',
			'</strong>',
			$this->get_base_fee_message()
		);
		if ( empty( $this->license->license_data->key ) && ! edds_is_pro() && ! edd_is_pro() ) {
			$message .= ' ' . sprintf(
				/* Translators: Replacements are for the html wrappers for the phrse Upgrade to Pro and should not be translated. */
				__( '%1$sUpgrade to Pro%2$s to remove transaction fees.', 'easy-digital-downloads' ),
				'<span class="edd-pro-upgrade"><a href="' . edd_link_helper(
					'https://easydigitaldownloads.com/pricing/',
					array(
						'utm_medium'  => 'stripe-settings',
						'utm_content' => 'upgrade-to-pro',
					)
				) . '" target="_blank">',
				'</a></span>'
			);
		} else {
			$message .= $this->get_license_message();
		}

		return $message;
	}

	/**
	 * Resets the license object.
	 *
	 * @since 3.2.0
	 */
	public function reset_license() {
		$this->license = null;
	}

	/**
	 * Gets the base fee message string.
	 *
	 * @since 3.2.0
	 * @return string
	 */
	private function get_base_fee_message() {
		return sprintf(
			/* translators: the application fee percentage. */
			__( '%d%% per-transaction fee + Stripe fees', 'easy-digital-downloads' ),
			$this->get_application_fee_percentage()
		);
	}

	/**
	 * Gets the application fee percentage.
	 *
	 * @since 3.2.0
	 * @return int
	 */
	private function get_application_fee_percentage() {
		return 3;
	}

	/**
	 * Gets the license object.
	 *
	 * @since 3.2.0
	 * @return \EDD\Gateways\Stripe\License
	 */
	private function get_license() {
		if ( is_null( $this->license ) ) {
			$this->license = new License();
		}

		return $this->license;
	}

	/**
	 * Gets the initial Stripe connect message.
	 *
	 * @since 3.2.0
	 * @return string
	 */
	private function get_initial_connect_message() {
		if ( ! $this->get_license() || empty( $this->license->license_data->key ) ) {
			return sprintf(
				/* translators: the message explaining the application fee (eg "3% per-transaction fee + Stripe fees") */
				__( 'Connect with Stripe for pay as you go pricing: %s.', 'easy-digital-downloads' ),
				$this->get_base_fee_message()
			);
		}

		// Stripe Pro is active, but the license is not valid.
		return sprintf(
			/* translators: the message explaining the application fee (eg "3% per-transaction fee + Stripe fees") */
			__( 'Connect with Stripe for pay as you go pricing: %s. Activate your license to remove the per-transaction fee.', 'easy-digital-downloads' ),
			$this->get_base_fee_message()
		);
	}

	/**
	 * Gets the license message.
	 *
	 * @since 3.2.0
	 * @return string
	 */
	private function get_license_message() {

		$license = $this->get_license();

		// For a new install, show the new install grace period message.
		$new_install_grace = $license->get_new_install_grace_period_end_date();
		if ( $new_install_grace ) {
			return ' ' . sprintf(
				/* translators: the date the grace period ends */
				__( 'You are in a grace period for your new license. Activate your license by %s to remove additional fees.', 'easy-digital-downloads' ),
				$new_install_grace
			);
		}

		if ( $license->is_expired() ) {
			// For an expired license within the grace period, show the grace period message.
			$grace_period_end_date = $license->get_grace_period_end_date();
			if ( $grace_period_end_date ) {
				return ' ' . sprintf(
					/* translators: 1. opening link tag, do not translate; 2. closing link tag, do not translate;  3. the date the grace period ends */
					__( 'Your license has expired, but you are in a grace period. %1$sRenew your license key%2$s before %3$s to prevent being charged additional transaction fees.', 'easy-digital-downloads' ),
					'<a href="' . esc_url( $license->get_renewal_url( 'expired' ) ) . '" target="_blank">',
					'</a>',
					$grace_period_end_date
				);
			}

			return ' ' . sprintf(
				/* translators: 1. the date the license expired; 2. opening link tag, do not translate; 3. closing link tag, do not translate  */
				__( 'Your license expired on %1$s. %2$sRenew your license%3$s to remove additional fees.', 'easy-digital-downloads' ),
				$license->get_expiration_date(),
				'<a href="' . esc_url( $license->get_renewal_url( 'expired' ) ) . '" target="_blank">',
				'</a>'
			);
		}

		return ' ' . sprintf(
			/* translators: opening link tag, do not translate; closing link tag, do not translate */
			__( '%1$sActivate or upgrade your license%2$s to remove additional fees.', 'easy-digital-downloads' ),
			'<a href="' . esc_url( $license->get_licensing_url() ) . '">',
			'</a>'
		);
	}
}
