<?php
/**
 * License Upgrade Notice
 *
 * @package   easy-digital-downloads
 * @copyright Copyright (c) 2023, Easy Digital Downloads
 * @license   GPL2+
 * @since     3.1.1
 */

namespace EDD\Pro\Admin\Promos\Notices;

defined( 'ABSPATH' ) || exit;

/**
 * Class InactivePro
 *
 * @since 3.1.1
 *
 * @package EDD\Pro\Admin\Promos\Notices
 */
class InactivePro extends \EDD\Admin\Promos\Notices\License_Upgrade_Notice {

	/**
	 * Sets the notice to not be dismissible.
	 */
	const DISMISSIBLE = false;

	/**
	 * @inheritDoc
	 *
	 * @return bool
	 */
	protected function _should_display() {

		if ( $this->meets_never_display_conditions() ) {
			return false;
		}

		return true;
	}

	/**
	 * @inheritDoc
	 * @since 3.1.1
	 * @return array
	 */
	protected function get_css_classes() {
		$css_classes   = parent::get_css_classes();
		$css_classes[] = 'edd-pro-inactive';

		return $css_classes;
	}

	/**
	 * @inheritDoc
	 */
	protected function _display() {

		if ( $this->maybe_display_license_expired_message() ) {
			return;
		}

		$link_url = edd_get_admin_url(
			array(
				'page' => 'edd-settings',
			)
		);

		printf(
			wp_kses_post(
				/* Translators: %1$s opening anchor tag; %2$s closing anchor tag */
				__( 'You are using Easy Digital Downloads (Pro) without an active license key. %1$sEnter or activate your license key now.%2$s', 'easy-digital-downloads' )
			),
			'<a href="' . esc_url( $link_url ) . '">',
			'</a>'
		);
	}

	/**
	 * Gets the license expired message.
	 *
	 * @since 3.2.0
	 * @return bool
	 */
	private function maybe_display_license_expired_message() {
		$license = new \EDD\Licensing\License( 'pro' );
		if ( ! $license->is_expired() ) {
			return false;
		}

		if ( ! empty( $license->subscription_id ) ) {
			$args = array(
				'action'          => 'update',
				'utm_medium'      => 'license-notice',
				'utm_content'     => 'update-subscription',
				'subscription_id' => $license->subscription_id,
			);
			$url  = edd_link_helper(
				'https://easydigitaldownloads.com/your-account/subscriptions/',
				$args
			);

			printf(
				wp_kses_post(
					/* Translators: %1$s opening anchor tag; %2$s closing anchor tag */
					__( 'The last attempt to renew your subscription for Easy Digital Downloads (Pro) failed. %1$sUpdate your payment method.%2$s', 'easy-digital-downloads' )
				),
				'<a href="' . esc_url( $url ) . '">',
				'</a>'
			);
			return true;
		}

		$args = array(
			'utm_medium'  => 'license-notice',
			'utm_content' => 'expired',
		);
		if ( ! empty( $license->key ) ) {
			$args['license_key'] = $license->key;
		}
		$url = edd_link_helper(
			'https://easydigitaldownloads.com/checkout/',
			$args
		);

		printf(
			wp_kses_post(
				/* Translators: %1$s opening anchor tag; %2$s closing anchor tag */
				__( 'Your license for Easy Digital Downloads (Pro) has expired. %1$sRenew your license now.%2$s', 'easy-digital-downloads' )
			),
			'<a href="' . esc_url( $url ) . '">',
			'</a>'
		);
		return true;
	}
}
