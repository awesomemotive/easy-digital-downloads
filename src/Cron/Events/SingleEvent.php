<?php
/**
 * Single Event class for creating single Cron Events.
 *
 * Since single events are not quite the same as a regularly scheduled event, this class
 * serves as a way to schedule a single event, without the need to register it to ensure it is
 * always scheduled.
 *
 * @since 3.3.0
 *
 * @package EDD
 * @subpackage Cron/Events
 */

namespace EDD\Cron\Events;

use EDD\Utils\Exceptions;
use EDD\Cron\Traits\NextScheduled;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Single Event Class
 *
 * @since 3.3.0
 */
class SingleEvent {
	use NextScheduled;

	/**
	 * Run Time.
	 *
	 * The UTC timestamp to run the event.
	 *
	 * @var int
	 */
	protected static $run_time = 0;

	/**
	 * Hook name.
	 *
	 * The hook that will fire when the Cron event is run.
	 *
	 * @var string
	 */
	protected static $hook;

	/**
	 * Arguments.
	 *
	 * The arguments to pass to the hook
	 *
	 * @var array
	 */
	protected static $args = array();

	/**
	 * Valid.
	 *
	 * Whether the event is valid.
	 *
	 * @var bool
	 */
	protected static $valid;

	/**
	 * Exception.
	 *
	 * The exception that was thrown.
	 *
	 * @var \Exception
	 */
	protected static $exception;

	/**
	 * Constructor.
	 *
	 * @since 3.3.0
	 *
	 * @param int    $run_time The UTC timestamp to run the event.
	 * @param string $hook The hook name.
	 * @param array  $args The arguments to pass to the hook.
	 */
	public static function add( $run_time = 0, $hook = '', $args = array() ) {
		self::$run_time = $run_time;
		self::$hook     = $hook;
		self::$args     = $args;

		if ( false === self::validate() ) {
			edd_debug_log( 'Single Event failed to validate: ' . self::$exception );
			return;
		}

		self::schedule();
	}

	/**
	 * Validate the one time event.
	 *
	 * @since 3.3.0
	 *
	 * @return bool
	 */
	private static function validate() {
		try {
			if ( ! is_string( self::$hook ) ) {
				throw new Exceptions\Invalid_Argument( __( 'The hook name must be a string.', 'easy-digital-downloads' ) );
			}

			if ( ! is_array( self::$args ) ) {
				self::$args = array( self::$args );
			}

			if ( ! is_int( self::$run_time ) ) {
				throw new Exceptions\Invalid_Argument( __( 'The run time must be an integer.', 'easy-digital-downloads' ) );
			}

			if ( empty( self::$run_time ) ) {
				throw new Exceptions\Invalid_Argument( __( 'The run time must be set.', 'easy-digital-downloads' ) );
			}

			if ( self::next_scheduled( self::$hook, self::$args ) ) {
				throw new Exceptions\Invalid_Argument( __( 'This event is already scheduled.', 'easy-digital-downloads' ) );
			}
		} catch ( \Exception $e ) {
			self::$valid     = false;
			self::$exception = $e->getMessage();
			return false;
		}

		return true;
	}

	/**
	 * Schedule the event.
	 *
	 * @since 3.3.0
	 *
	 * @return void
	 */
	private static function schedule() {
		wp_schedule_single_event( self::$run_time, self::$hook, self::$args );
	}
}
