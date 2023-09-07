<?php

namespace EDD\Gateways\Stripe;

defined( 'ABSPATH' ) || exit;

/**
 * Class to handle the Stripe license data.
 *
 * @since 3.2.0
 */
final class License {

	/**
	 * The license object.
	 *
	 * @var \EDD\Licensing\License|false
	 */
	public $license_data;

	/**
	 * Whether the user has a pass that includes Stripe Pro.
	 *
	 * @var bool
	 */
	public $is_pass_license = false;

	/**
	 * The class constructor.
	 */
	public function __construct() {
		$this->license_data = $this->get_license_data();
	}

	/**
	 * Whether the license is active and valid.
	 *
	 * @since 3.2.0
	 * @return bool
	 */
	public function is_license_valid() {
		return ! empty( $this->license_data->success ) && 'valid' === $this->license_data->license;
	}

	/**
	 * Gets the license expiration.
	 *
	 * @since 3.2.0
	 * @return string|int|bool
	 */
	public function get_expiration() {
		return ! empty( $this->license_data->expires ) ? $this->license_data->expires : false;
	}

	/**
	 * Whether the license is expired.
	 *
	 * @since 3.2.0
	 * @return bool
	 */
	public function is_expired() {
		return $this->license_data ? $this->license_data->is_expired() : false;
	}

	/**
	 * Whether the license is in a grace period.
	 *
	 * @since 3.2.0
	 * @return bool
	 */
	public function is_in_grace_period() {
		$expiration = $this->get_expiration();
		if ( empty( $expiration ) || 'lifetime' === $expiration ) {
			return false;
		}
		if ( $this->is_expired() ) {
			$now        = current_time( 'timestamp' ); // phpcs:ignore WordPress.DateTime.CurrentTimeTimestamp.Requested
			$expiration = strtotime( $expiration );
			// Fourteen day grace period.
			return ( $now - $expiration < ( DAY_IN_SECONDS * 14 ) );
		}

		return false;
	}

	/**
	 * Whether the license is in a new install grace period.
	 *
	 * @since 3.2.0
	 * @return bool
	 */
	public function is_in_new_install_grace_period() {
		if ( $this->is_license_valid() ) {
			return false;
		}
		if ( ! empty( $this->license_data->key ) ) {
			return false;
		}

		$end_time = $this->get_new_install_grace_period_end_time();
		if ( $end_time && time() < $end_time ) {
			return true;
		}

		return false;
	}

	/**
	 * Whether the license is expiring in the next two weeks.
	 *
	 * @since 3.2.0
	 * @return bool
	 */
	public function is_expiring_soon() {
		if ( $this->is_expired() ) {
			return false;
		}
		$expiration = $this->get_expiration();
		if ( ! $expiration || 'lifetime' === $expiration ) {
			return false;
		}
		if ( strtotime( $expiration ) - time() <= ( DAY_IN_SECONDS * 14 ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Gets the localized date for the end of the grace period.
	 *
	 * @since 3.2.0
	 * @return string
	 */
	public function get_grace_period_end_date() {
		if ( ! $this->is_in_grace_period() ) {
			return '';
		}

		return $this->get_localized_date( strtotime( $this->get_expiration() ) + ( DAY_IN_SECONDS * 14 ) );
	}

	/**
	 * Gets the localized date for the expiration date.
	 *
	 * @since 3.2.0
	 * @return string
	 */
	public function get_expiration_date() {
		$expiration = $this->get_expiration();
		if ( empty( $expiration ) || 'lifetime' === $expiration ) {
			return '';
		}

		return $this->get_localized_date( strtotime( $expiration ) );
	}

	/**
	 * Gets the localized date for the end of the new install grace period.
	 *
	 * @since 3.2.0
	 * @return string
	 */
	public function get_new_install_grace_period_end_date() {
		$end_time = $this->get_new_install_grace_period_end_time();

		return $end_time ? $this->get_localized_date( $end_time ) : '';
	}

	/**
	 * Gets the renewal URL.
	 *
	 * @since 3.2.0
	 * @param string $license_status The license status.
	 * @return string
	 */
	public function get_renewal_url( $license_status ) {
		$args = array(
			'utm_medium'  => 'license-notice',
			'utm_content' => $license_status,
		);
		if ( ! empty( $this->license_data->key ) ) {
			$args['license_key'] = $this->license_data->key;
		}

		return edd_link_helper(
			'https://easydigitaldownloads.com/checkout/',
			$args
		);
	}

	/**
	 * Gets the URL for the licenses tab.
	 *
	 * @since 3.2.0
	 * @return string
	 */
	public function get_licensing_url() {
		$args = array(
			'page' => 'edd-settings',
		);
		if ( ( function_exists( 'edd_is_admin_page' ) && edd_is_admin_page( 'settings', 'general' ) ) || ( ! edd_is_pro() && edds_is_pro() ) ) {
			$args['tab'] = 'licenses';
		}

		return edd_get_admin_url( $args );
	}

	/**
	 * Gets the license data.
	 *
	 * @since 3.2.0
	 * @return \EDD\Licensing\License|false
	 */
	private function get_license_data() {

		// Check for a pass license first.
		$pass_license = $this->get_pass_license();
		if ( $pass_license ) {
			$this->is_pass_license = true;

			return $pass_license;
		}

		// Check for a Stripe Pro license key.
		$license = new \EDD\Licensing\License( 'Stripe Pro Payment Gateway' );
		if ( ! empty( $license->key ) ) {
			return $license;
		}

		return false;
	}

	/**
	 * Gets the pass license.
	 *
	 * @since 3.2.0
	 * @return \EDD\Licensing\License|false
	 */
	private function get_pass_license() {
		$pro_license = new \EDD\Licensing\License( 'pro' );
		if ( empty( $pro_license->key ) ) {
			return false;
		}
		$pass_manager = new \EDD\Admin\Pass_Manager();
		if ( $pass_manager::pass_compare( $pass_manager->highest_pass_id, $pass_manager::EXTENDED_PASS_ID, '>=' ) ) {
			return $pro_license;
		}
		if ( $pass_manager->hasExtendedPass() || $pass_manager->hasAllAccessPass() ) {
			return $pro_license;
		}

		return false;
	}

	/**
	 * Gets the end time for the new install grace period.
	 *
	 * @since 3.2.0
	 * @return int|bool
	 */
	private function get_new_install_grace_period_end_time() {
		$installed = get_transient( 'edd_stripe_new_install' );

		return $installed ? $installed + ( HOUR_IN_SECONDS * 72 ) : false;
	}

	/**
	 * Gets the localized date for the given timestamp.
	 *
	 * @since 3.2.0
	 * @param int $date The timestamp to localize.
	 * @return string
	 */
	private function get_localized_date( $date ) {
		$format = get_option( 'date_format', 'Y-m-d' );
		if ( ! is_numeric( $date ) ) {
			$date = strtotime( $date );
		}

		return date( // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
			$format,
			$date
		);
	}
}
