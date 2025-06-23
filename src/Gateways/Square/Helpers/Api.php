<?php
/**
 * API helper for the Square gateway.
 *
 * @package     EDD\Gateways\Square\Helpers
 * @copyright   Copyright (c) 2025, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.4.0
 */

namespace EDD\Gateways\Square\Helpers;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

use EDD\Vendor\Square\SquareClient;
use EDD\Vendor\Square\SquareClientBuilder;
use EDD\Vendor\Square\Authentication\BearerAuthCredentialsBuilder;
use EDD\Vendor\Square\Environment;
use EDD\Gateways\Square\Helpers\Mode;
use EDD\Gateways\Square\Helpers\Setting;
use EDD\Gateways\Square\Connection;

/**
 * API helper for the Square gateway.
 *
 * A single class to handle all API interactions. Generates a single client instance that
 * can be used many times during the same request.
 *
 * @since 3.4.0
 */
class Api {

	/**
	 * The client instance.
	 *
	 * @var SquareClient
	 */
	private static $client;

	/**
	 * The elevated client instance.
	 *
	 * @var SquareClient
	 */
	private static $elevated_client;

	/**
	 * The instance of the class.
	 *
	 * Singleton pattern.
	 *
	 * @var \EDD\Gateways\Square\Helpers\Api
	 */
	private static $instance;

	/**
	 * Get the instance of the class.
	 *
	 * @return \EDD\Gateways\Square\Helpers\Api
	 */
	public static function get_instance() {
		if ( ! self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Constructor.
	 *
	 * @since 3.4.0
	 */
	private function __construct() {
		self::set_client();
	}

	/**
	 * Get the Square client.
	 *
	 * @since 3.4.0
	 *
	 * @return void
	 */
	private static function set_client() {
		if ( self::token_expired() ) {
			Connection::refresh_access_token();
		}

		$environment = self::get_environment();

		self::$client = SquareClientBuilder::init()
			->bearerAuthCredentials( BearerAuthCredentialsBuilder::init( self::get_access_token() ) )
			->squareVersion( '2025-01-23' )
			->environment( $environment )
			->build();
	}

	/**
	 * Set the elevated client.
	 *
	 * @since 3.4.0
	 * @param string $token The token.
	 *
	 * @return void
	 */
	private static function set_elevated_client( string $token ) {
		$environment = self::get_environment();

		self::$elevated_client = SquareClientBuilder::init()
			->bearerAuthCredentials( BearerAuthCredentialsBuilder::init( $token ) )
			->squareVersion( '2025-01-23' )
			->environment( $environment )
			->build();
	}

	/**
	 * Get the Square client.
	 *
	 * Example:
	 * Api::client()->catalogApi()->listCatalog();
	 *
	 * @since 3.4.0
	 * @param bool        $elevated Whether to use the elevated client.
	 * @param string|null $token The token.
	 *
	 * @return SquareClient The Square client.
	 */
	public static function client( $elevated = false, $token = null ): SquareClient {
		// Always ensure that the instance is set.
		self::get_instance();

		if ( $elevated ) {
			if ( ! self::$elevated_client ) {
				self::set_elevated_client( $token );
			}

			return self::$elevated_client;
		}

		return self::$client;
	}

	/**
	 * Get the environment.
	 *
	 * @since 3.4.0
	 * @return string The environment.
	 */
	private static function get_environment() {
		if ( Mode::get() === 'sandbox' ) {
			return Environment::SANDBOX;
		}

		return Environment::PRODUCTION;
	}

	/**
	 * Get the access token.
	 *
	 * @since 3.4.0
	 * @return string The access token.
	 */
	public static function get_access_token() {
		$option_name = 'square_' . Mode::get() . '_access_token';
		return edd_get_option( $option_name );
	}

	/**
	 * Get the idempotency key.
	 *
	 * @since 3.4.0
	 * @param string $prefix The prefix for the idempotency key.
	 * @return string The idempotency key.
	 */
	public static function get_idempotency_key( $prefix = '' ) {
		// Max length is 45 characters and uniqid returns 23 characters with more entropy enabled.
		$prefix = substr( $prefix, 0, 45 - 23 );

		return uniqid( $prefix, true );
	}

	/**
	 * Checks if the token is expired.
	 *
	 * @since 3.4.0
	 * @return bool
	 */
	private static function token_expired() {
		$expires_at = Setting::get( 'expires_at' );
		if ( ! $expires_at ) {
			return true;
		}

		$expires_at = new \EDD\Utils\Date( $expires_at );
		$now        = new \EDD\Utils\Date( time() );
		return $expires_at->isBefore( $now );
	}
}
