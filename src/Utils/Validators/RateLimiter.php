<?php
/**
 * Universal Rate Limiter
 *
 * Provides atomic rate limiting using WordPress transients so any feature
 * (cart recovery, login attempts, form submissions, etc.) can enforce
 * max attempts per time window with a configurable key and lockout.
 *
 * @package EDD\Utils
 * @copyright Copyright (c) 2026, Sandhills Development, LLC
 * @license https://opensource.org/licenses/GPL-2.0 GNU Public License
 * @since   3.6.5
 */

declare(strict_types=1);

namespace EDD\Utils\Validators;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

use WP_Error;

/**
 * Rate Limiter class.
 *
 * Uses WordPress transients for atomic operations and cache compatibility.
 * Callers pass a transient key prefix, max attempts, and window duration;
 * each identifier (e.g. IP, session id) is hashed and scoped to that key.
 */
class RateLimiter {

	/**
	 * Transient key prefix. Stored keys become `{prefix}_{md5(identifier)}`.
	 *
	 * @var string
	 */
	private string $transient_key_prefix;

	/**
	 * Maximum allowed attempts in the current window.
	 *
	 * @var int
	 */
	private int $max_attempts;

	/**
	 * Window duration in seconds (lockout / reset time).
	 *
	 * @var int
	 */
	private int $window_seconds;

	/**
	 * Constructor.
	 *
	 * @since 3.6.5
	 * @param string $transient_key_prefix Prefix for transient keys (e.g. 'edd_acr_restore_rate'). Used with md5(identifier).
	 * @param int    $max_attempts         Maximum allowed attempts per window. Default 5.
	 * @param int    $window_seconds       Window (lockout) duration in seconds. Default 1 hour.
	 */
	public function __construct(
		string $transient_key_prefix,
		int $max_attempts = 5,
		int $window_seconds = HOUR_IN_SECONDS
	) {
		$this->transient_key_prefix = $transient_key_prefix;
		$this->max_attempts         = $max_attempts > 0 ? $max_attempts : 5;
		$this->window_seconds       = $window_seconds > 0 ? $window_seconds : HOUR_IN_SECONDS;
	}

	/**
	 * Checks rate limit and increments counter. Use for action-style limits (e.g. restore link clicks).
	 *
	 * Returns true if the attempt is allowed; returns WP_Error when the rate limit is exceeded,
	 * with a message indicating how long until the lockout resets.
	 *
	 * @since 3.6.5
	 * @param string $identifier Unique identifier to limit (e.g. IP address, user id, session id).
	 * @return true|WP_Error True if allowed, WP_Error if rate limited.
	 */
	public function check( string $identifier ) {
		if ( $this->increment( $identifier ) ) {
			return true;
		}

		$remaining_time = $this->get_remaining_lockout_seconds( $identifier );
		$minutes_left   = $remaining_time > 0 ? (int) ceil( $remaining_time / 60 ) : 1;

		return new WP_Error(
			'rate_limit_exceeded',
			sprintf(
				/* translators: %d: number of minutes until lockout resets */
				__( 'Too many attempts. Please try again in %d minutes.', 'easy-digital-downloads' ),
				$minutes_left
			)
		);
	}

	/**
	 * Increments the attempt counter for the identifier. Use for quota-style limits (e.g. snapshots per session).
	 *
	 * @since 3.6.5
	 * @param string $identifier Unique identifier to limit (e.g. session id, IP).
	 * @return bool True if under the limit (counter incremented), false if rate limit exceeded.
	 */
	public function increment( string $identifier ): bool {
		$key = $this->get_transient_key( $identifier );
		$now = time();

		$current_data = get_transient( $key );

		if ( false === $current_data ) {
			$data = array(
				'count' => 1,
				'reset' => $now + $this->window_seconds,
			);
			set_transient( $key, $data, $this->window_seconds );

			return true;
		}

		if ( ! is_array( $current_data ) || ! isset( $current_data['count'] ) || ! isset( $current_data['reset'] ) ) {
			$data = array(
				'count' => 1,
				'reset' => $now + $this->window_seconds,
			);
			set_transient( $key, $data, $this->window_seconds );

			return true;
		}

		if ( $now >= (int) $current_data['reset'] ) {
			$data = array(
				'count' => 1,
				'reset' => $now + $this->window_seconds,
			);
			set_transient( $key, $data, $this->window_seconds );

			return true;
		}

		if ( (int) $current_data['count'] >= $this->max_attempts ) {
			return false;
		}

		$current_data['count'] = (int) $current_data['count'] + 1;
		$remaining_ttl         = (int) $current_data['reset'] - $now;
		if ( $remaining_ttl <= 0 ) {
			$remaining_ttl = $this->window_seconds;
		}
		set_transient( $key, $current_data, $remaining_ttl );

		return true;
	}

	/**
	 * Returns remaining seconds until the current window resets for the identifier.
	 *
	 * @since 3.6.5
	 * @param string $identifier Same identifier used for increment/check.
	 * @return int Seconds until reset, or 0 if no active window.
	 */
	public function get_remaining_lockout_seconds( string $identifier ): int {
		$key          = $this->get_transient_key( $identifier );
		$current_data = get_transient( $key );
		if ( false === $current_data || ! is_array( $current_data ) || ! isset( $current_data['reset'] ) ) {
			return 0;
		}
		$remaining = (int) $current_data['reset'] - time();

		return $remaining > 0 ? $remaining : 0;
	}

	/**
	 * Builds the transient key for an identifier.
	 *
	 * @since 3.6.5
	 * @param string $identifier Identifier to hash (e.g. IP, session id).
	 * @return string Transient key.
	 */
	private function get_transient_key( string $identifier ): string {
		return $this->transient_key_prefix . '_' . md5( $identifier );
	}
}
