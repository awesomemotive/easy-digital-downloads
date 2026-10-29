<?php
/**
 * Stripe Connect Deauthorization
 *
 * Revokes the store's Stripe Connect authorization through EDD Connect, so that credentials a
 * previous connection issued stop being accepted by Stripe.
 *
 * @package     EDD\Gateways\Stripe\Admin\Disconnect
 * @copyright   Copyright (c) 2026, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.7.3
 */

namespace EDD\Gateways\Stripe\Admin\Disconnect;

defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

use EDD\Utils\RemoteRequest;
use EDD\Utils\URL;
use WP_Error;

/**
 * Deauthorize class.
 *
 * @since 3.7.3
 */
class Deauthorize {

	/**
	 * The EDD Connect endpoint which performs the revocation.
	 *
	 * Deliberately not filterable: the request body carries the store's live Stripe secret key.
	 *
	 * @since 3.7.3
	 * @var string
	 */
	const ENDPOINT = 'https://connect.easydigitaldownloads.com/v2/stripe/deauthorize';

	/**
	 * Where a merchant removes Easy Digital Downloads from their Stripe account, which revokes it for every site.
	 *
	 * @since 3.7.3
	 * @var string
	 */
	const INSTALLED_APPS_URL = 'https://dashboard.stripe.com/settings/apps';

	/**
	 * Option holding a failed revocation until the merchant resolves it.
	 *
	 * @since 3.7.3
	 * @var string
	 */
	const ERROR_OPTION = 'edd_stripe_deauthorize_error';

	/**
	 * Setting recording the modes EDD Connect issued credentials for, as mode => account ID.
	 *
	 * @since 3.7.3
	 * @var string
	 */
	const MODES_SETTING = 'stripe_connect_modes';

	/**
	 * Transient recording that keys were removed without EDD Connect confirming the revocation.
	 *
	 * @since 3.7.3
	 * @var string
	 */
	const UNCONFIRMED_TRANSIENT = 'edd_stripe_deauthorize_unconfirmed';

	/**
	 * The time limit for a revocation request, in seconds.
	 *
	 * A store connected in both modes spends at most about 90s (30s on the request and 15s asking
	 * Stripe directly, per mode). The ceiling is well above that because a request killed partway
	 * leaves a key Stripe has revoked still stored on the site.
	 *
	 * @since 3.7.3
	 * @var int
	 */
	const TIME_LIMIT = 300;

	/**
	 * The connected Stripe account ID.
	 *
	 * @since 3.7.3
	 * @var string
	 */
	private $account_id;

	/**
	 * The modes whose credential Stripe rejected although EDD Connect did not confirm the revocation.
	 *
	 * @since 3.7.3
	 * @var string[]
	 */
	private $unconfirmed_modes = array();

	/**
	 * The modes whose keys were removed from the store.
	 *
	 * @since 3.7.3
	 * @var string[]
	 */
	private $removed_modes = array();

	/**
	 * The modes whose revocation failed, leaving their keys in place.
	 *
	 * @since 3.7.3
	 * @var string[]
	 */
	private $failed_modes = array();

	/**
	 * Constructor.
	 *
	 * @since 3.7.3
	 */
	public function __construct() {
		$this->account_id = (string) edd_stripe()->connect()->get_connect_id();
	}

	/**
	 * Whether this store holds a Connect authorization that can be revoked.
	 *
	 * Only a connection EDD Connect brokered records an account ID, and once a mode is recorded a
	 * key the merchant enters for the other mode is not sent. A store connected before modes were
	 * recorded has no record, so every key it holds is sent until it reconnects.
	 *
	 * @since 3.7.3
	 * @return bool
	 */
	public function is_revocable() {
		return ! empty( $this->account_id ) && ! empty( $this->get_credentials() );
	}

	/**
	 * Whether this site may offer to revoke the authorization.
	 *
	 * A revoke from a staging copy would turn off the live site's keys, so only the production site that holds
	 * an authorization to revoke offers one.
	 *
	 * @since 3.7.3
	 * @return bool
	 */
	public function can_offer_revoke() {
		return URL::is_production_url( home_url() ) && $this->is_revocable();
	}

	/**
	 * Revokes the authorization for every mode this store holds Connect credentials for.
	 *
	 * Each mode is a separate authorization at Stripe, with its own platform key and client ID, so
	 * a store connected in both has to revoke both.
	 *
	 * @since 3.7.3
	 * @return true|WP_Error True once Stripe no longer accepts the stored credentials.
	 */
	public function revoke() {
		if ( empty( $this->account_id ) ) {
			return new WP_Error(
				'edd_stripe_deauthorize_not_connected',
				__( 'This store is not connected to Stripe with Stripe Connect, so there is no authorization to revoke.', 'easy-digital-downloads' )
			);
		}

		$credentials = $this->get_credentials();
		if ( empty( $credentials ) ) {
			return new WP_Error(
				'edd_stripe_deauthorize_no_credentials',
				__( 'No Stripe Connect credentials were found to revoke.', 'easy-digital-downloads' )
			);
		}

		// Lifts PHP's execution limit and ignores a closed tab; a web server or proxy timeout is not covered.
		edd_set_time_limit( true, self::TIME_LIMIT );

		$this->unconfirmed_modes = array();
		$this->removed_modes     = array();
		$this->failed_modes      = array();

		/*
		 * The active mode goes last, so a request killed partway never leaves the mode the store is
		 * taking payments in revoked while the gateway is still on.
		 */
		$active_mode = edd_is_test_mode() ? 'test' : 'live';
		if ( isset( $credentials[ $active_mode ] ) ) {
			$active_key = $credentials[ $active_mode ];
			unset( $credentials[ $active_mode ] );
			$credentials[ $active_mode ] = $active_key;
		}

		$errors = new WP_Error();
		foreach ( $credentials as $mode => $secret_key ) {
			$revoked = $this->revoke_mode( $mode, $secret_key );

			// A credential Stripe rejects is revoked, whatever EDD Connect answered.
			if ( is_wp_error( $revoked ) && $this->is_credential_rejected( $secret_key ) ) {
				$this->unconfirmed_modes[] = $mode;
				$this->log_failure( $mode, $revoked );
				$revoked = true;
			}

			if ( true === $revoked ) {
				$this->removed_modes[] = $mode;
			}

			if ( is_wp_error( $revoked ) ) {
				$this->failed_modes[] = $mode;
				$errors->add( $revoked->get_error_code(), $revoked->get_error_message(), $revoked->get_error_data() );
				$this->log_failure( $mode, $revoked );
			}
		}

		// Nothing is deleted until every mode has answered, so a request that dies partway leaves the store connected.
		foreach ( $this->removed_modes as $mode ) {
			$this->forget_credentials( $mode );
		}

		// A mode that was removed must not stay recorded, or a key entered for it later would be sent as Connect-issued.
		if ( ! empty( $this->failed_modes ) ) {
			$remaining = array();
			foreach ( $this->failed_modes as $mode ) {
				$remaining[ $mode ] = $this->get_account_id( $mode );
			}
			edd_update_option( self::MODES_SETTING, $remaining );
		}

		return $errors->has_errors() ? $errors : true;
	}

	/**
	 * Gets the modes deleted on Stripe's word alone, with no confirmation from EDD Connect.
	 *
	 * @since 3.7.3
	 * @return string[]
	 */
	public function get_unconfirmed_modes() {
		return $this->unconfirmed_modes;
	}

	/**
	 * Gets the modes whose keys were removed from the store, confirmed or not.
	 *
	 * @since 3.7.3
	 * @return string[]
	 */
	public function get_removed_modes() {
		return $this->removed_modes;
	}

	/**
	 * Gets the modes whose revocation failed, whose keys are still in place.
	 *
	 * @since 3.7.3
	 * @return string[]
	 */
	public function get_failed_modes() {
		return $this->failed_modes;
	}

	/**
	 * Records a failed revocation so admin screens report it until it is resolved.
	 *
	 * @since 3.7.3
	 *
	 * @param WP_Error $error   The failure to report.
	 * @param array    $outcome Optional. The modes `removed` from the store, those that `failed`, and
	 *                          those removed `unconfirmed`, as lists of modes.
	 * @return void
	 */
	public static function record_error( WP_Error $error, $outcome = array() ) {
		$retry = array();
		foreach ( $error->get_error_codes() as $code ) {
			foreach ( $error->get_all_error_data( $code ) as $data ) {
				if ( is_array( $data ) && ! empty( $data['retry'] ) ) {
					$retry[] = $data['retry'];
				}
			}
		}

		update_option(
			self::ERROR_OPTION,
			array(
				'code'        => $error->get_error_code(),
				'throttled'   => in_array( 'edd_stripe_deauthorize_rate_limited', $error->get_error_codes(), true ),
				'retryable'   => (bool) array_intersect( array( 'edd_stripe_deauthorize_unreachable', 'edd_stripe_deauthorize_unavailable' ), $error->get_error_codes() ),
				'message'     => implode( ' ', $error->get_error_messages() ),
				'retry'       => implode( ' ', array_unique( $retry ) ),
				'removed'     => $outcome['removed'] ?? array(),
				'failed'      => $outcome['failed'] ?? array(),
				'unconfirmed' => $outcome['unconfirmed'] ?? array(),
			),
			false
		);
	}

	/**
	 * Gets a recorded revocation failure, if there is one.
	 *
	 * @since 3.7.3
	 * @return array|false
	 */
	public static function get_error() {
		return get_option( self::ERROR_OPTION, false );
	}

	/**
	 * Clears a recorded revocation failure.
	 *
	 * @since 3.7.3
	 * @return void
	 */
	public static function clear_error() {
		delete_option( self::ERROR_OPTION );
	}

	/**
	 * Records that EDD Connect issued credentials for a mode, and for which account.
	 *
	 * @since 3.7.3
	 *
	 * @param string $mode       Either 'live' or 'test'.
	 * @param string $account_id The Stripe account the credentials were issued for.
	 * @return void
	 */
	public static function record_connected_mode( $mode, $account_id ) {
		$recorded = edd_get_option( self::MODES_SETTING, false );
		$previous = (string) edd_get_option( 'stripe_connect_account_id' );
		$modes    = array();

		/*
		 * A store connected before modes were recorded may hold Connect keys in either mode, so each is kept
		 * to be revoked against the account that was stored before this connect. It cannot tell a hand-entered
		 * key from a Connect one, so those are kept too. A first connection holds only what the merchant
		 * entered, which is not ours to revoke. Called before the account ID is saved.
		 */
		if ( empty( $recorded ) ) {
			foreach ( array( 'live', 'test' ) as $stored_mode ) {
				if ( '' !== $previous && ! empty( edd_get_option( $stored_mode . '_secret_key' ) ) ) {
					$modes[ $stored_mode ] = $previous;
				}
			}
		} else {
			$modes = (array) $recorded;
		}

		$modes[ $mode ] = $account_id;

		edd_update_option( self::MODES_SETTING, $modes );
	}

	/**
	 * Records that keys were removed without EDD Connect confirming the revocation.
	 *
	 * @since 3.7.3
	 * @return void
	 */
	public static function record_unconfirmed() {
		set_transient( self::get_transient_key( self::UNCONFIRMED_TRANSIENT ), true, 5 * MINUTE_IN_SECONDS );
	}

	/**
	 * Whether keys were removed without EDD Connect confirming the revocation.
	 *
	 * @since 3.7.3
	 * @return bool
	 */
	public static function has_unconfirmed() {
		return (bool) get_transient( self::get_transient_key( self::UNCONFIRMED_TRANSIENT ) );
	}

	/**
	 * Clears the record of an unconfirmed revocation.
	 *
	 * @since 3.7.3
	 * @return void
	 */
	public static function clear_unconfirmed() {
		delete_transient( self::get_transient_key( self::UNCONFIRMED_TRANSIENT ) );
	}

	/**
	 * Gets the translated label for a Stripe mode, for use in merchant-facing text.
	 *
	 * @since 3.7.3
	 *
	 * @param string $mode Either 'live' or 'test'.
	 * @return string
	 */
	public static function get_mode_label( $mode ) {
		return 'live' === $mode
			? _x( 'live', 'Stripe Connect mode', 'easy-digital-downloads' )
			: _x( 'test', 'Stripe Connect mode', 'easy-digital-downloads' );
	}

	/**
	 * Gets a transient key for the current user.
	 *
	 * The record is per user so that another administrator loading an admin page cannot consume the
	 * message before the person who clicked Disconnect has seen it.
	 *
	 * @since 3.7.3
	 *
	 * @param string $base The transient name the key is built from.
	 * @return string
	 */
	private static function get_transient_key( $base ) {
		return $base . '_' . get_current_user_id();
	}

	/**
	 * Writes a failed revocation to the debug log.
	 *
	 * Support needs to see this on a store where the merchant has moved on.
	 *
	 * @since 3.7.3
	 *
	 * @param string   $mode  Either 'live' or 'test'.
	 * @param WP_Error $error The failure.
	 * @return void
	 */
	private function log_failure( $mode, WP_Error $error ) {
		edd_debug_log(
			sprintf(
				'Stripe Connect: revoking the %1$s authorization for %2$s failed. %3$s: %4$s',
				$mode,
				$this->get_account_id( $mode ),
				$error->get_error_code(),
				$error->get_error_message()
			),
			true
		);
	}

	/**
	 * Deletes the stored secret and publishable keys for a mode.
	 *
	 * @since 3.7.3
	 *
	 * @param string $mode Either 'live' or 'test'.
	 * @return void
	 */
	private function forget_credentials( $mode ) {
		edd_delete_option( $mode . '_secret_key' );
		edd_delete_option( $mode . '_publishable_key' );
	}

	/**
	 * Whether Stripe has stopped accepting a credential.
	 *
	 * Asked directly rather than through EDD Connect, so that it stays answerable when EDD Connect
	 * is the thing that could not be reached. A credential Stripe rejects is dead whatever killed
	 * it, which is the only property the disconnect depends on.
	 *
	 * @since 3.7.3
	 *
	 * @param string $secret_key The credential to test.
	 * @return bool False whenever Stripe's answer is anything other than a rejection.
	 */
	private function is_credential_rejected( $secret_key ) {
		$request = new RemoteRequest(
			'https://api.stripe.com/v1/account',
			array(
				'timeout'     => 15,
				'redirection' => 0,
				'headers'     => array( 'Authorization' => 'Bearer ' . $secret_key ),
			)
		);

		if ( is_wp_error( $request->response ) ) {
			return false;
		}

		return 401 === (int) $request->code;
	}

	/**
	 * Revokes the authorization for a single mode.
	 *
	 * @since 3.7.3
	 *
	 * @param string $mode       Either 'live' or 'test'.
	 * @param string $secret_key The credential this store holds for that mode.
	 * @return true|WP_Error
	 */
	private function revoke_mode( $mode, $secret_key ) {
		$request = new RemoteRequest(
			self::ENDPOINT,
			array(
				'method'      => 'POST',
				'redirection' => 0,

				/*
				 * Longer than RemoteRequest's default on purpose. EDD Connect verifies the credential
				 * with Stripe and then calls Stripe again to revoke it, so the default cuts the
				 * response off mid-revocation and the revocation is reported as a failure.
				 */
				'timeout'     => 30,
				'headers'     => array( 'Content-Type' => 'application/json' ),
				'body'        => wp_json_encode(
					array(
						'stripe_user_id' => $this->get_account_id( $mode ),
						'secret_key'     => $secret_key,
						'live_mode'      => 'live' === $mode ? 1 : 0,
						'site_url'       => home_url(),
					)
				),
			)
		);

		if ( is_wp_error( $request->response ) ) {
			return new WP_Error(
				'edd_stripe_deauthorize_unreachable',
				sprintf(
					/* translators: 1: The Stripe mode, either live or test. 2: The error message. */
					__( 'We couldn\'t reach EDD to turn off the %1$s keys: %2$s', 'easy-digital-downloads' ),
					self::get_mode_label( $mode ),
					$request->response->get_error_message()
				)
			);
		}

		$body = json_decode( (string) $request->body, true );

		if ( 200 === (int) $request->code && true === ( $body['success'] ?? null ) ) {
			return true;
		}

		if ( 429 === (int) $request->code ) {
			return $this->handle_throttled( $mode, isset( $body['error']['retry_after'] ) ? (int) $body['error']['retry_after'] : 0 );
		}

		return new WP_Error(
			(int) $request->code >= 500 ? 'edd_stripe_deauthorize_unavailable' : 'edd_stripe_deauthorize_failed',
			sprintf(
				/* translators: 1: The Stripe mode, either live or test. 2: The error message. */
				__( 'The %1$s keys couldn\'t be turned off: %2$s', 'easy-digital-downloads' ),
				self::get_mode_label( $mode ),
				! empty( $body['error']['message'] ) && is_string( $body['error']['message'] ) ? $body['error']['message'] : __( 'Stripe didn\'t confirm.', 'easy-digital-downloads' )
			)
		);
	}

	/**
	 * Answers a throttled revocation.
	 *
	 * EDD Connect allows five attempts an hour per account and mode, so a throttle means the merchant
	 * has been retrying and the wait is theirs to make. It is always reported with the wait.
	 *
	 * @since 3.7.3
	 *
	 * @param string $mode        Either 'live' or 'test'.
	 * @param int    $retry_after Seconds until the next attempt is allowed.
	 * @return WP_Error
	 */
	private function handle_throttled( $mode, $retry_after ) {
		$message = sprintf(
			/* translators: %s: The Stripe mode, either live or test. */
			__( 'Too many attempts to turn off the %s keys.', 'easy-digital-downloads' ),
			self::get_mode_label( $mode )
		);

		// Naming a wait EDD Connect did not give would be inventing one.
		if ( $retry_after < MINUTE_IN_SECONDS ) {
			$retry = __( 'Please try again shortly.', 'easy-digital-downloads' );
		} else {
			$minutes = (int) ceil( $retry_after / MINUTE_IN_SECONDS );
			$retry   = sprintf(
				/* translators: %d: The number of minutes to wait. */
				_n( 'Please try again in %d minute.', 'Please try again in %d minutes.', $minutes, 'easy-digital-downloads' ),
				$minutes
			);
		}

		// The wait is data, not message: it is only worth printing where the merchant has a revoke to retry.
		return new WP_Error( 'edd_stripe_deauthorize_rate_limited', $message, array( 'retry' => $retry ) );
	}

	/**
	 * Gets the Stripe account a mode's credentials were issued for.
	 *
	 * A store with no per-mode record has the single stored account ID.
	 *
	 * @since 3.7.3
	 *
	 * @param string $mode Either 'live' or 'test'.
	 * @return string
	 */
	private function get_account_id( $mode ) {
		$recorded = edd_get_option( self::MODES_SETTING, array() );

		return ! empty( $recorded[ $mode ] ) ? (string) $recorded[ $mode ] : $this->account_id;
	}

	/**
	 * Gets the stored Connect credentials, keyed by mode.
	 *
	 * Once modes are recorded only those are returned, so a key the merchant entered by hand for
	 * the other mode is not sent. A store connected before the modes were recorded has no record,
	 * and every stored key is returned for it.
	 *
	 * @since 3.7.3
	 * @return array
	 */
	private function get_credentials() {
		$credentials = array();
		$recorded    = edd_get_option( self::MODES_SETTING, array() );

		foreach ( array( 'live', 'test' ) as $mode ) {
			if ( ! empty( $recorded ) && ! isset( $recorded[ $mode ] ) ) {
				continue;
			}

			$secret_key = edd_get_option( $mode . '_secret_key' );

			if ( ! empty( $secret_key ) ) {
				$credentials[ $mode ] = trim( $secret_key );
			}
		}

		return $credentials;
	}
}
