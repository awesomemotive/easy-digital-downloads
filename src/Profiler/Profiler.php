<?php
/**
 * Abstract Profiler Class
 *
 * @package     EDD\Profiler
 * @copyright   Copyright (c) 2025, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.6.0
 */

namespace EDD\Profiler;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Profiler Class
 *
 * @since 3.6.0
 */
abstract class Profiler {
	use Traits\Log;
	use Traits\Settings;
	use Traits\Timing;

	/**
	 * The ID of the profiler.
	 *
	 * @var string
	 */
	protected static $id;

	/**
	 * Call tracking data
	 *
	 * @var array
	 */
	protected $calls = array();

	/**
	 * Start time for the request
	 *
	 * @var float
	 */
	protected $start_time;

	/**
	 * Methods to track
	 *
	 * @var array
	 */
	protected $tracked_methods = array();

	/**
	 * Toggle cache
	 *
	 * @var bool
	 */
	protected $toggle_cache = false;

	/**
	 * Check if profiling is enabled
	 *
	 * @since 3.6.0
	 * @return bool Whether profiling is enabled.
	 */
	abstract public static function is_enabled(): bool;

	/**
	 * Get the name of the profiler
	 *
	 * @since 3.6.0
	 * @return string The name of the profiler
	 */
	abstract protected function get_name(): string;

	/**
	 * Constructor
	 *
	 * @since 3.6.0
	 * @return void
	 */
	public function __construct() {
		$this->init_tracking();
	}

	/**
	 * Get the ID of the profiler.
	 *
	 * @since 3.6.0
	 * @return string The ID of the profiler.
	 */
	public static function get_id(): string {
		return static::$id;
	}

	/**
	 * Initialize tracking hooks and actions.
	 *
	 * @since 3.6.0
	 * @return void
	 */
	protected function init_tracking() {
		if ( ! static::is_enabled() ) {
			return;
		}

		if ( ! $this->can_profile() ) {
			return;
		}

		if ( empty( $this->tracked_methods ) ) {
			return;
		}

		$this->start_time = $this->start_timer();

		// Track method calls via action hooks.
		foreach ( $this->tracked_methods as $method ) {
			$this->calls[ $method ] = array(
				'count'      => 0,
				'total_time' => 0,
				'stacks'     => array(),
			);
		}

		// Log to PHP error log on shutdown.
		add_action( 'shutdown', array( $this, 'log_results' ), 999 );
	}

	/**
	 * Track a method call
	 *
	 * @since 3.6.0
	 * @param string $method    The method name.
	 * @param array  $backtrace Optional backtrace data.
	 * @return void
	 */
	public function track_call( $method, $backtrace = null ) {
		if ( ! static::is_enabled() || ! isset( $this->calls[ $method ] ) ) {
			return;
		}

		++$this->calls[ $method ]['count'];

		// Store simplified backtrace (calling function and file).
		if ( null === $backtrace ) {
			$backtrace = debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS, 5 );
		}

		$caller = $this->get_caller_info( $backtrace );
		if ( $caller ) {
			$this->calls[ $method ]['stacks'][] = $caller;
		}
	}

	/**
	 * Get profiling results.
	 *
	 * @since 3.6.0
	 * @return array Profiling data
	 */
	public function get_results() {
		$total_time       = microtime( true ) - $this->start_time;
		$total_calls      = 0;
		$calculation_time = 0;

		foreach ( $this->calls as $method => $data ) {
			$total_calls      += $data['count'];
			$calculation_time += $data['total_time'];
		}

		return array(
			'request_time'        => $total_time,
			'calculation_time'    => $calculation_time,
			'calculation_percent' => $total_time > 0 ? ( $calculation_time / $total_time ) * 100 : 0,
			'total_calls'         => $total_calls,
			'calls'               => $this->calls,
		);
	}

	/**
	 * Check if caching is enabled.
	 *
	 * @since 3.6.0
	 * @return bool Whether caching is enabled.
	 */
	protected function is_cache_enabled(): bool {
		return true;
	}

	/**
	 * Get caller information from backtrace
	 *
	 * @since 3.6.0
	 * @param array $backtrace Debug backtrace.
	 * @return string|null Formatted caller info
	 */
	private function get_caller_info( $backtrace ) {
		// Skip the first few frames (this method, track_call, the tracked method).
		$relevant_trace = array_slice( $backtrace, 2, 3 );

		$callers = array();
		foreach ( $relevant_trace as $trace ) {
			$function = isset( $trace['function'] ) ? $trace['function'] : 'unknown';
			$class    = isset( $trace['class'] ) ? $trace['class'] . '::' : '';
			$file     = isset( $trace['file'] ) ? basename( $trace['file'] ) : '';
			$line     = isset( $trace['line'] ) ? ':' . $trace['line'] : '';

			$callers[] = $class . $function . '(' . $file . $line . ')';
		}

		return implode( ' → ', $callers );
	}

	/**
	 * Check if the current user can view the profiler.
	 *
	 * @since 3.6.0
	 * @return bool Whether the current user can view the profiler.
	 */
	protected function can_profile(): bool {
		if ( current_user_can( 'manage_shop_settings' ) ) {
			return true;
		}

		$cookie = \EDD\Utils\Cookies::get( 'edd_profiler_enabled' );

		return $cookie && hash_equals( wp_hash( get_home_url() ), $cookie );
	}
}
