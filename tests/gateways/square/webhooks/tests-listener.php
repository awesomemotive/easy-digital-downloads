<?php
/**
 * Square webhook listener tests.
 *
 * @package     EDD\Tests\Gateways\Square\Webhooks
 * @copyright   Copyright (c) 2026, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.7.3
 */

namespace EDD\Tests\Gateways\Square\Webhooks;

defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

use EDD\Gateways\Square\Helpers\Mode;
use EDD\Gateways\Square\Helpers\Setting;
use EDD\Gateways\Square\Webhooks\Listener;
use EDD\Tests\PHPUnit\EDD_UnitTestCase;

/**
 * Square webhook listener tests.
 *
 * @since 3.7.3
 */
class ListenerValidation extends EDD_UnitTestCase {

	const SUBSCRIPTION_ID = 'wbhk_test_subscription';

	const SIGNATURE_KEY = 'test_signature_key';

	/**
	 * The Square mode cached before the test.
	 *
	 * @var mixed
	 */
	private $original_mode;

	/**
	 * The $_SERVER values the test overwrote, with null for a key that was absent.
	 *
	 * @var array
	 */
	private $original_server = array();

	/**
	 * Whether test mode was enabled before the test.
	 *
	 * @var mixed
	 */
	private $original_test_mode;

	/**
	 * The webhook options the test may have written, keyed by option name, with null for one that was absent.
	 *
	 * @var array
	 */
	private $original_webhook_options = array();

	/**
	 * Starts every test in live mode with a clean Square mode cache.
	 *
	 * @since 3.7.3
	 */
	public function set_up() {
		parent::set_up();

		$this->original_mode      = $this->get_cached_mode();
		$this->original_test_mode = edd_get_option( 'test_mode', null );

		foreach ( array( 'live', 'sandbox' ) as $mode ) {
			foreach ( array( 'webhook_subscription_id', 'webhook_signature_key' ) as $key ) {
				$this->original_webhook_options[ "square_{$mode}_{$key}" ] = edd_get_option( "square_{$mode}_{$key}", null );
			}
		}

		$this->set_test_mode( false );
	}

	/**
	 * Restores the Square mode cache, test mode, webhook options and $_SERVER.
	 *
	 * @since 3.7.3
	 */
	public function tear_down() {
		foreach ( $this->original_server as $key => $value ) {
			if ( null === $value ) {
				unset( $_SERVER[ $key ] );
			} else {
				$_SERVER[ $key ] = $value;
			}
		}

		if ( null === $this->original_test_mode ) {
			edd_delete_option( 'test_mode' );
		} else {
			edd_update_option( 'test_mode', $this->original_test_mode );
		}

		foreach ( $this->original_webhook_options as $name => $value ) {
			if ( null === $value ) {
				edd_delete_option( $name );
			} else {
				edd_update_option( $name, $value );
			}
		}

		$this->set_cached_mode( $this->original_mode );

		parent::tear_down();
	}

	/**
	 * A webhook Square marks Production passes validation in live mode.
	 *
	 * @since 3.7.3
	 */
	public function test_production_webhook_is_valid_in_live_mode() {
		$this->save_webhook_settings();
		$request = $this->get_signed_request( 'Production' );

		$this->assertSame( 'live', Mode::get() );
		$this->assertSame( self::SUBSCRIPTION_ID, Setting::get( 'webhook_subscription_id' ) );
		$this->assertSame( self::SIGNATURE_KEY, Setting::get( 'webhook_signature_key' ) );
		$this->assertTrue( ( new Listener() )->validate_request( $request ) );
	}

	/**
	 * A webhook Square marks Sandbox is refused in live mode with a 403.
	 *
	 * @since 3.7.3
	 */
	public function test_sandbox_webhook_is_rejected_in_live_mode() {
		$this->save_webhook_settings();
		$request = $this->get_signed_request( 'Sandbox' );

		$this->assertSame( 'live', Mode::get() );
		$this->assertSame( self::SUBSCRIPTION_ID, Setting::get( 'webhook_subscription_id' ) );
		$result = ( new Listener() )->validate_request( $request );

		$this->assertWPError( $result );
		$this->assertSame( 'invalid_environment', $result->get_error_code() );
		$this->assertSame( 403, $result->get_error_data()['status'] );
	}

	/**
	 * A webhook Square marks Sandbox passes validation in test mode.
	 *
	 * @since 3.7.3
	 */
	public function test_sandbox_webhook_is_valid_in_test_mode() {
		$this->set_test_mode( true );
		$this->save_webhook_settings();
		$request = $this->get_signed_request( 'Sandbox' );

		$this->assertSame( 'sandbox', Mode::get() );
		$this->assertSame( self::SUBSCRIPTION_ID, Setting::get( 'webhook_subscription_id' ) );
		$this->assertSame( self::SIGNATURE_KEY, Setting::get( 'webhook_signature_key' ) );
		$this->assertTrue( ( new Listener() )->validate_request( $request ) );
	}

	/**
	 * A webhook Square marks Production is refused in test mode with a 403.
	 *
	 * @since 3.7.3
	 */
	public function test_production_webhook_is_rejected_in_test_mode() {
		$this->set_test_mode( true );
		$this->save_webhook_settings();
		$request = $this->get_signed_request( 'Production' );

		$this->assertSame( 'sandbox', Mode::get() );
		$result = ( new Listener() )->validate_request( $request );

		$this->assertWPError( $result );
		$this->assertSame( 'invalid_environment', $result->get_error_code() );
		$this->assertSame( 403, $result->get_error_data()['status'] );
	}

	/**
	 * An environment Square does not use is refused with a 403.
	 *
	 * @since 3.7.3
	 */
	public function test_unknown_environment_is_rejected() {
		$this->save_webhook_settings();
		$request = $this->get_signed_request( 'Staging' );

		$result = ( new Listener() )->validate_request( $request );

		$this->assertWPError( $result );
		$this->assertSame( 'invalid_environment', $result->get_error_code() );
		$this->assertSame( 403, $result->get_error_data()['status'] );
	}

	/**
	 * A webhook with no environment header is refused with a 400.
	 *
	 * @since 3.7.3
	 */
	public function test_missing_environment_is_rejected_as_bad_request() {
		$this->save_webhook_settings();
		$request = $this->get_signed_request( '' );

		$result = ( new Listener() )->validate_request( $request );

		$this->assertWPError( $result );
		$this->assertSame( 'empty_environment', $result->get_error_code() );
		$this->assertSame( 400, $result->get_error_data()['status'] );
	}

	/**
	 * A webhook with an empty body is refused with a 400.
	 *
	 * @since 3.7.3
	 */
	public function test_empty_body_is_rejected_as_bad_request() {
		$this->save_webhook_settings();
		$request = $this->get_signed_request( 'Production', '' );

		$result = ( new Listener() )->validate_request( $request );

		$this->assertWPError( $result );
		$this->assertSame( 'empty_body', $result->get_error_code() );
		$this->assertSame( 400, $result->get_error_data()['status'] );
	}

	/**
	 * A webhook with no subscription ID is refused with a 400.
	 *
	 * @since 3.7.3
	 */
	public function test_missing_subscription_id_is_rejected_as_bad_request() {
		$this->save_webhook_settings();
		$request = $this->get_signed_request( 'Production', '{"type":"refund.updated"}', '' );

		$result = ( new Listener() )->validate_request( $request );

		$this->assertWPError( $result );
		$this->assertSame( 'empty_subscription_id', $result->get_error_code() );
		$this->assertSame( 400, $result->get_error_data()['status'] );
	}

	/**
	 * A webhook for a subscription ID other than the saved one is refused with a 403.
	 *
	 * @since 3.7.3
	 */
	public function test_other_subscription_id_is_rejected() {
		$this->save_webhook_settings();
		$request = $this->get_signed_request( 'Production', '{"type":"refund.updated"}', 'wbhk_other_subscription' );

		$this->assertSame( self::SUBSCRIPTION_ID, Setting::get( 'webhook_subscription_id' ) );
		$result = ( new Listener() )->validate_request( $request );

		$this->assertWPError( $result );
		$this->assertSame( 'invalid_subscription_id', $result->get_error_code() );
		$this->assertSame( 403, $result->get_error_data()['status'] );
	}

	/**
	 * A webhook whose signature does not match is refused with a 403.
	 *
	 * @since 3.7.3
	 */
	public function test_bad_signature_is_rejected() {
		$this->save_webhook_settings();
		$request = $this->get_signed_request( 'Production', '{"type":"refund.updated"}', self::SUBSCRIPTION_ID, 'not-the-signature' );

		$this->assertSame( self::SIGNATURE_KEY, Setting::get( 'webhook_signature_key' ) );
		$result = ( new Listener() )->validate_request( $request );

		$this->assertWPError( $result );
		$this->assertSame( 'invalid_signature', $result->get_error_code() );
		$this->assertSame( 403, $result->get_error_data()['status'] );
	}

	/**
	 * A refused webhook answers through the REST server with a 4xx status, not a 500.
	 *
	 * @since 3.7.3
	 */
	public function test_rejected_webhook_returns_client_error_status() {
		$this->save_webhook_settings();
		$request = $this->get_signed_request( 'Sandbox' );

		$response = $this->dispatch_through_rest_server( $request );

		$this->assertSame( 403, $response->get_status() );
	}

	/**
	 * Dispatches the request on a fresh REST server that has the Square route registered.
	 *
	 * @since 3.7.3
	 *
	 * @param \WP_REST_Request $request The request.
	 * @return \WP_REST_Response
	 */
	private function dispatch_through_rest_server( \WP_REST_Request $request ) {
		global $wp_rest_server;

		$original_server = $wp_rest_server;
		$wp_rest_server  = new \WP_REST_Server();
		do_action( 'rest_api_init', $wp_rest_server );

		$response = rest_do_request( $request );

		$wp_rest_server = $original_server;

		return $response;
	}

	/**
	 * Saves the subscription ID and signature key for the current mode.
	 *
	 * @since 3.7.3
	 */
	private function save_webhook_settings() {
		Setting::set( 'webhook_subscription_id', self::SUBSCRIPTION_ID );
		Setting::set( 'webhook_signature_key', self::SIGNATURE_KEY );
	}

	/**
	 * Puts a webhook's headers in $_SERVER, where the getallheaders() polyfill reads them, and returns its request.
	 *
	 * @since 3.7.3
	 *
	 * @param string $environment     The Square-Environment header value.
	 * @param string $body            The request body.
	 * @param string $subscription_id The Square-Subscription-Id header value.
	 * @param string $signature       The signature header value; the valid one when empty.
	 * @return \WP_REST_Request
	 */
	private function get_signed_request( $environment, $body = '{"type":"refund.updated"}', $subscription_id = self::SUBSCRIPTION_ID, $signature = '' ) {
		if ( '' === $signature ) {
			$notification_url = rest_url( Listener::REST_NAMESPACE . '/' . Listener::REST_ROUTE );
			$signature        = base64_encode( hash_hmac( 'sha256', $notification_url . $body, self::SIGNATURE_KEY, true ) ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
		}

		$headers = array(
			'HTTP_SQUARE_ENVIRONMENT'            => $environment,
			'HTTP_SQUARE_SUBSCRIPTION_ID'        => $subscription_id,
			'HTTP_X_SQUARE_HMACSHA256_SIGNATURE' => $signature,
		);
		foreach ( $headers as $key => $value ) {
			if ( ! array_key_exists( $key, $this->original_server ) ) {
				$this->original_server[ $key ] = $_SERVER[ $key ] ?? null;
			}
			$_SERVER[ $key ] = $value;
		}

		$request = new \WP_REST_Request( 'POST', '/' . Listener::REST_NAMESPACE . '/' . Listener::REST_ROUTE );
		$request->set_body( $body );

		return $request;
	}

	/**
	 * Turns test mode on or off and resets the cached Square mode to match.
	 *
	 * @since 3.7.3
	 *
	 * @param bool $enabled Whether test mode is on.
	 */
	private function set_test_mode( $enabled ) {
		edd_update_option( 'test_mode', $enabled ? 1 : 0 );
		$this->set_cached_mode( null );
	}

	/**
	 * Gets the cached Square mode.
	 *
	 * @since 3.7.3
	 *
	 * @return mixed
	 */
	private function get_cached_mode() {
		$reflection = new \ReflectionProperty( Mode::class, 'mode' );
		$reflection->setAccessible( true );

		return $reflection->getValue();
	}

	/**
	 * Sets the cached Square mode.
	 *
	 * @since 3.7.3
	 *
	 * @param mixed $mode The mode, or null to make Mode::get() read the setting again.
	 */
	private function set_cached_mode( $mode ) {
		$reflection = new \ReflectionProperty( Mode::class, 'mode' );
		$reflection->setAccessible( true );
		$reflection->setValue( null, $mode );
	}
}
