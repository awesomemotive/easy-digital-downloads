<?php
/**
 * Stripe Disconnect Notices
 *
 * The admin notices reporting how a Stripe disconnect ended.
 *
 * @package     EDD\Gateways\Stripe\Admin\Disconnect
 * @copyright   Copyright (c) 2026, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.7.3
 */

namespace EDD\Gateways\Stripe\Admin\Disconnect;

defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

use EDD\EventManagement\SubscriberInterface;

/**
 * Notices class.
 *
 * @since 3.7.3
 */
class Notices implements SubscriberInterface {

	/**
	 * Gets the subscribed events.
	 *
	 * Runs after `EDD_Notices::add_notices()` (priority 20) so the notice registry is ready.
	 *
	 * @since 3.7.3
	 * @return array
	 */
	public static function get_subscribed_events() {
		return array(
			'admin_init' => array( 'add_notices', 21 ),
		);
	}

	/**
	 * Adds the disconnect notices.
	 *
	 * @since 3.7.3
	 * @return void
	 */
	public function add_notices() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$this->add_failed_notice();
		$this->add_unconfirmed_notice();
	}

	/**
	 * Checks whether the current request is one where a disconnect notice may appear.
	 *
	 * @since 3.7.3
	 * @return bool
	 */
	private function can_show_notice() {
		return edd_is_admin_page() && ! edd_is_admin_page( 'index.php' );
	}

	/**
	 * Reports a revocation that failed for at least one mode.
	 *
	 * @since 3.7.3
	 * @return void
	 */
	private function add_failed_notice() {
		if ( ! $this->can_show_notice() ) {
			return;
		}

		/*
		 * The failure path leaves the keys in place, so a store that needs this notice still has its
		 * account ID. Reading the loaded settings first keeps an option lookup off every page load.
		 */
		if ( ! edd_get_option( 'stripe_connect_account_id' ) ) {
			return;
		}

		$error = Deauthorize::get_error();
		if ( empty( $error['message'] ) ) {
			return;
		}

		$apps_url = Deauthorize::INSTALLED_APPS_URL;

		$removed = $error['removed'] ?? array();
		$failed  = $error['failed'] ?? array();

		// A non-production site has no revoke button to try again with.
		$can_retry = ( new Deauthorize() )->can_offer_revoke();

		// A throttled mode's own wait is printed with its message, so a vaguer one beside it would contradict it.
		$show_retry = $can_retry && ! empty( $error['retryable'] ) && empty( $error['throttled'] );

		if ( ! empty( $removed ) && ! empty( $failed ) ) {
			// One mode was turned off before another failed, so its keys are gone while the other's remain.
			$message = array(
				sprintf(
					/* translators: 1: The Stripe mode that was disconnected, either live or test. 2: The Stripe mode whose access could not be revoked, either live or test. */
					__( 'Your %1$s Stripe keys were turned off and removed from this site, so reconnect to get new ones. We couldn\'t revoke access for your %2$s keys right now, so they are still in place.', 'easy-digital-downloads' ) . ( $show_retry ? ' ' . __( 'Please try again in a few minutes.', 'easy-digital-downloads' ) : '' ),
					esc_html( Deauthorize::get_mode_label( reset( $removed ) ) ),
					esc_html( Deauthorize::get_mode_label( reset( $failed ) ) )
				),
			);

			if ( ! empty( $error['unconfirmed'] ) ) {
				$message[] = sprintf(
					/* translators: 1: The Stripe mode whose old keys could not be confirmed as turned off, either live or test. 2: opening anchor tag to the Stripe dashboard, do not translate; 3: closing anchor tag, do not translate */
					__( 'We couldn\'t confirm the old %1$s keys were turned off. You can check in Settings > Installed Apps in your %2$sStripe dashboard%3$s. Removing Easy Digital Downloads there turns Stripe off for every site using this account.', 'easy-digital-downloads' ),
					esc_html( Deauthorize::get_mode_label( reset( $error['unconfirmed'] ) ) ),
					'<a href="' . esc_url( $apps_url ) . '" target="_blank" rel="noopener noreferrer">',
					'</a>'
				);
			}
		} else {
			$message = array(
				__( 'We couldn\'t revoke access to your Stripe account right now, so your current keys are still in place.', 'easy-digital-downloads' ) . ( $show_retry ? ' ' . __( 'Please try again in a few minutes.', 'easy-digital-downloads' ) : '' ),
			);
		}

		$message[] = esc_html( $error['message'] ) . ( $can_retry && ! empty( $error['retry'] ) ? ' ' . esc_html( $error['retry'] ) : '' );

		$message[] = sprintf(
			/* translators: 1: opening anchor tag to remove the keys from this site only, do not translate; 2: closing anchor tag, do not translate; 3: opening anchor tag to the Stripe dashboard, do not translate; 4: closing anchor tag, do not translate */
			__( 'Or %1$sremove the keys from this site only%2$s. They\'ll keep working in Stripe, and removing Easy Digital Downloads under Settings > Installed Apps in your %3$sStripe dashboard%4$s turns Stripe off for every site using this account.', 'easy-digital-downloads' ),
			'<a href="' . esc_url( edds_stripe_connect_disconnect_url() ) . '">',
			'</a>',
			'<a href="' . esc_url( $apps_url ) . '" target="_blank" rel="noopener noreferrer">',
			'</a>'
		);

		EDD()->notices->add_notice(
			array(
				'id'             => 'edd-stripe-deauthorize-failed',
				'class'          => 'error',
				'message'        => $message,
				'is_dismissible' => false,
			)
		);
	}

	/**
	 * Reports a Stripe disconnect that removed the keys without Stripe confirming the revocation.
	 *
	 * Reached when EDD Connect did not confirm but Stripe rejected the stored key, so the keys are
	 * gone and the merchant still has to check that the authorization is too.
	 *
	 * @since 3.7.3
	 * @return void
	 */
	private function add_unconfirmed_notice() {
		if ( ! $this->can_show_notice() || ! Deauthorize::has_unconfirmed() ) {
			return;
		}

		Deauthorize::clear_unconfirmed();

		EDD()->notices->add_notice(
			array(
				'id'             => 'edd-stripe-deauthorize-unconfirmed',
				'class'          => 'warning',
				'message'        => sprintf(
					/* translators: 1: opening anchor tag to the Stripe dashboard, do not translate; 2: closing anchor tag, do not translate */
					__( 'Stripe is disconnected and its keys were removed from this site. We couldn\'t confirm the old keys were turned off. You can check in Settings > Installed Apps in your %1$sStripe dashboard%2$s. Removing Easy Digital Downloads there turns Stripe off for every site using this account.', 'easy-digital-downloads' ),
					'<a href="' . esc_url( Deauthorize::INSTALLED_APPS_URL ) . '" target="_blank" rel="noopener noreferrer">',
					'</a>'
				),
				'is_dismissible' => false,
			)
		);
	}
}
