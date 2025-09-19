<?php
/**
 * Square application fee.
 *
 * @package     EDD\Gateways\Square\ApplicationFee
 * @copyright   Copyright (c) 2025, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.4.0
 */

namespace EDD\Gateways\Square;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

use EDD\Admin\Pass_Manager;
use EDD\Gateways\Square\Helpers\Setting;

/**
 * Class ApplicationFee
 *
 * @since 3.4.0
 */
final class ApplicationFee {

	/**
	 * The Pass Manager object.
	 *
	 * @var \EDD\Admin\Pass_Manager|null
	 */
	private $pass_manager;

	/**
	 * Gets the pass manager object.
	 *
	 * @since 3.4.0
	 * @return \EDD\Admin\Pass_Manager
	 */
	private function get_pass_manager() {
		if ( ! $this->pass_manager ) {
			$this->pass_manager = new Pass_Manager();
		}
		return $this->pass_manager;
	}

	/**
	 * Whether the application fee should be added to the payment/setup intent.
	 *
	 * @since 3.4.0
	 * @return bool
	 */
	public function has_application_fee() {
		if ( ! $this->verify_global_qualification() ) {
			return false;
		}

		// If they have a pass that is valid, we don't need to add an application fee.
		if ( $this->get_pass_manager()->isPro() ) {
			if (
				$this->get_pass_manager()->hasExtendedPass() ||
				$this->get_pass_manager()->hasProfessionalPass() ||
				$this->get_pass_manager()->hasAllAccessPass()
			) {
				return false;
			}
		}

		// If the license key expired, but they are in the grace period, we don't need to add an application fee.
		if ( $this->get_pass_manager()->get_pro_license_object()->is_expired() ) {
			if ( $this->is_license_grace_period() ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Gets the license grace period end date.
	 *
	 * @since 3.4.0
	 * @param \EDD\Licensing\License $license The license object.
	 * @return \EDD\Utils\Date|null
	 */
	public function get_license_grace_period_end_date( $license ) {
		$license_expiration = new \EDD\Utils\Date( $license->expires );
		return $license_expiration->addDays( 14 );
	}

	/**
	 * Checks if the license is in the grace period.
	 *
	 * @since 3.4.0
	 * @return bool|null
	 */
	public function is_license_grace_period() {
		$license = $this->get_pass_manager()->get_pro_license_object();
		if ( 'lifetime' === $license->expires ) {
			return null;
		}

		$grace_period_end = $this->get_license_grace_period_end_date( $license );
		$now              = new \EDD\Utils\Date( time() );
		return $license->is_expired() && $now->isBefore( $grace_period_end );
	}

	/**
	 * Gets the application fee amount.
	 * This amount is in cents and should not include a decimal.
	 * This method does not check if the application fee should be added; it only calculates it.
	 *
	 * @since 3.4.0
	 * @param float $amount The amount.
	 *
	 * @return float
	 */
	public function get_application_fee_amount( $amount ) {
		if ( ! $this->verify_global_qualification() ) {
			return 0;
		}

		return round( $amount * ( $this->get_application_fee_percentage() / 100 ), 0 );
	}

	/**
	 * Gets the application fee message for the settings screen.
	 *
	 * @since 3.4.0
	 * @return string
	 */
	public function get_fee_message() {
		if ( ! Connection::is_connected() ) {
			return $this->get_initial_connect_message();
		}

		if ( ! $this->verify_global_qualification() ) {
			return '';
		}

		if (
			$this->get_pass_manager()->has_pass() &&
			$this->get_pass_manager()->isPro() &&
			(
				$this->get_pass_manager()->hasExtendedPass() ||
				$this->get_pass_manager()->hasProfessionalPass() ||
				$this->get_pass_manager()->hasAllAccessPass()
			)
		) {
			return '';
		}

		$message = sprintf(
			/* translators: 1: opening strong tag, 2: closing strong tag, 3: the message explaining the application fee (eg "3% per-transaction fee + gateway fees"). */
			__( '%1$sPay as you go pricing:%2$s %3$s.', 'easy-digital-downloads' ),
			'<strong>',
			'</strong>',
			$this->get_base_fee_message()
		);

		if (
			$this->get_pass_manager()->isFree() ||
			$this->get_pass_manager()->hasPersonalPass()
		) {
			$message .= ' ' . sprintf(
				/* translators: Replacements are for the html wrappers for the phrase Upgrade to Pro and should not be translated. */
				__( '%1$sUpgrade to Pro%2$s to remove transaction fees.', 'easy-digital-downloads' ),
				'<span class="edd-pro-upgrade"><a href="' . edd_link_helper(
					'https://easydigitaldownloads.com/pricing/',
					array(
						'utm_medium'  => 'square-settings',
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
	 * Gets the base fee message string.
	 *
	 * @since 3.4.0
	 * @return string
	 */
	private function get_base_fee_message() {
		return sprintf(
			/* translators: the application fee percentage. */
			__( '%d%% per-transaction fee + Square fees', 'easy-digital-downloads' ),
			$this->get_application_fee_percentage()
		);
	}

	/**
	 * Gets the application fee percentage.
	 *
	 * @since 3.4.0
	 * @return int
	 */
	private function get_application_fee_percentage() {
		return 3;
	}

	/**
	 * Gets the initial Square connect message.
	 *
	 * @since 3.4.0
	 * @return string
	 */
	private function get_initial_connect_message() {
		if ( ! $this->get_pass_manager()->has_pass() || $this->get_pass_manager()->hasPersonalPass() ) {
			return sprintf(
				/* translators: the message explaining the application fee (eg "3% per-transaction fee + Square fees") */
				__( 'Connect with Square for pay as you go pricing: %s.', 'easy-digital-downloads' ),
				$this->get_base_fee_message()
			) . ' ';
		}

		return '';
	}

	/**
	 * Gets the license message.
	 *
	 * @since 3.4.0
	 * @return string
	 */
	private function get_license_message() {
		$license = $this->get_pass_manager()->get_pro_license_object();

		if ( $license->is_expired() ) {
			// For an expired license within the grace period, show the grace period message.
			$args = array(
				'utm_medium'  => 'license-notice',
				'utm_content' => 'expired',
			);
			if ( ! empty( $license->key ) ) {
				$args['license_key'] = $license->key;
			}

			$renewal_url = edd_link_helper(
				'https://easydigitaldownloads.com/checkout/',
				$args
			);

			if ( $this->is_license_grace_period() ) {
				$grace_period_end = $this->get_license_grace_period_end_date( $license );
				return ' ' . sprintf(
					/* translators: 1: opening link tag, do not translate, 2: closing link tag, do not translate;  3. the date the grace period ends */
					__( 'Your license has expired, but you are in a grace period. %1$sRenew your license key%2$s before %3$s to prevent being charged additional transaction fees.', 'easy-digital-downloads' ),
					'<a href="' . esc_url( $renewal_url ) . '" target="_blank">',
					'</a>',
					$grace_period_end->format( 'F j, Y' )
				);
			}

			try {
				$expiration_date = new \EDD\Utils\Date( $license->expires );
				return ' ' . sprintf(
					/* translators: 1: the date the license expired, 2: opening link tag, do not translate, 3: closing link tag, do not translate  */
					__( 'Your license expired on %1$s. %2$sRenew your license%3$s to prevent additional fees.', 'easy-digital-downloads' ),
					$expiration_date->format( 'F j, Y' ),
					'<a href="' . esc_url( $renewal_url ) . '" target="_blank">',
					'</a>'
				);
			} catch ( \Exception $e ) {
				// Do nothing.
			}
		}

		$args = array(
			'page' => 'edd-settings',
		);

		$licensing_url = edd_get_admin_url( $args );

		return ' ' . sprintf(
			/* translators: opening link tag, do not translate; closing link tag, do not translate */
			__( '%1$sActivate or upgrade your license%2$s to prevent additional fees.', 'easy-digital-downloads' ),
			'<a href="' . esc_url( $licensing_url ) . '">',
			'</a>'
		);
	}

	/**
	 * Verifies the global qualification for the application fee.
	 *
	 * @since 3.4.0
	 * @return bool
	 */
	private function verify_global_qualification() {
		// The global qualification is that the merchant country and currency match ours.
		$merchant_country  = Setting::get( 'country' );
		$merchant_currency = Setting::get( 'currency' );
		return 'US' === $merchant_country && 'USD' === $merchant_currency;
	}
}
