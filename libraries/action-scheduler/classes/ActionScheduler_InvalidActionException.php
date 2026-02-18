<?php

/**
 * InvalidAction Exception.
 *
 * Used for identifying actions that are invalid in some way.
 *
 * @package ActionScheduler
 */
class ActionScheduler_InvalidActionException extends \InvalidArgumentException implements ActionScheduler_Exception {

	/**
	 * Create a new exception when the action's schedule cannot be fetched.
	 *
	 * @param string $action_id The action ID with bad args.
	 * @param mixed  $schedule  Passed schedule.
	 * @return static
	 */
	public static function from_schedule( $action_id, $schedule ) {
		$message = sprintf(
			/* translators: 1: action ID 2: schedule */
			__( 'Action [%1$s] has an invalid schedule: %2$s', 'action-scheduler' ),
			$action_id,
			var_export( $schedule, true ) // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_var_export
		);

		return new static( $message );
	}

	/**
	 * Create a new exception when the action's args cannot be decoded to an array.
	 *
	 * @param string $action_id The action ID with bad args.
	 * @param mixed  $args      Passed arguments.
	 * @return static
	 */
	public static function from_decoding_args( $action_id, $args = array() ) {
		$message = sprintf(
			/* translators: 1: action ID 2: arguments */
			__( 'Action [%1$s] has invalid arguments. It cannot be JSON decoded to an array. $args = %2$s', 'action-scheduler' ),
			$action_id,
			var_export( $args, true ) // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_var_export
		);

		return new static( $message );
	}
}
