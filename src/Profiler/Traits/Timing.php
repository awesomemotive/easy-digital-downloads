<?php
/**
 * Timing Trait
 *
 * @package     EDD\Profiler\Traits
 * @copyright   Copyright (c) 2025, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.6.0
 */

namespace EDD\Profiler\Traits;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

/**
 * Timing Trait
 *
 * @since 3.6.0
 */
trait Timing {

	/**
	 * Track execution time for a method
	 *
	 * @since 3.6.0
	 * @param string $method The method name.
	 * @param float  $time   Execution time in seconds.
	 * @return void
	 */
	public function track_time( $method, $time ) {
		if ( ! $this->is_enabled() || ! isset( $this->calls[ $method ] ) ) {
			return;
		}

		$this->calls[ $method ]['total_time'] += $time;
	}

	/**
	 * Start timing a method.
	 *
	 * @since 3.6.0
	 * @return float The current microtime for tracking elapsed time.
	 */
	public function start_timer() {
		return microtime( true );
	}

	/**
	 * End timing a method and record it
	 *
	 * @since 3.6.0
	 * @param string $method     Method name.
	 * @param float  $start_time Start time from start_timer().
	 * @return void
	 */
	public function end_timer( $method, $start_time ) {
		if ( ! $this->is_enabled() ) {
			return;
		}

		$elapsed = microtime( true ) - $start_time;
		$this->track_time( $method, $elapsed );
	}
}
