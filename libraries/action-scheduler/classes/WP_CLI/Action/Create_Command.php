<?php

namespace Action_Scheduler\WP_CLI\Action;

use function \WP_CLI\Utils\get_flag_value;

/**
 * WP-CLI command: action-scheduler action create
 */
class Create_Command extends \ActionScheduler_WPCLI_Command {

	const ASYNC_OPTS = array( 'async', 0 );

	/**
	 * Execute command.
	 *
	 * @return void
	 */
	public function execute() {
		$hook           = $this->args[0];
		$schedule_start = $this->args[1];
		$callback_args  = get_flag_value( $this->assoc_args, 'args', array() );
		$group          = get_flag_value( $this->assoc_args, 'group', '' );
		$interval       = absint( get_flag_value( $this->assoc_args, 'interval', 0 ) );
		$cron           = get_flag_value( $this->assoc_args, 'cron', '' );
		$unique         = get_flag_value( $this->assoc_args, 'unique', false );
		$priority       = absint( get_flag_value( $this->assoc_args, 'priority', 10 ) );

		if ( ! empty( $callback_args ) ) {
			$callback_args = json_decode( $callback_args, true );
		}

		$function_args = array(
			'start'         => $schedule_start,
			'cron'          => $cron,
			'interval'      => $interval,
			'hook'          => $hook,
			'callback_args' => $callback_args,
			'group'         => $group,
			'unique'        => $unique,
			'priority'      => $priority,
		);

		try {
			// Generate schedule start if appropriate.
			if ( ! in_array( $schedule_start, static::ASYNC_OPTS, true ) ) {
				$schedule_start         = as_get_datetime_object( $schedule_start );
				$function_args['start'] = $schedule_start->format( 'U' );
			}
		} catch ( \Exception $e ) {
			\WP_CLI::error( $e->getMessage() );
		}

		// Default to creating single action.
		$action_type = 'single';
		$function    = 'as_schedule_single_action';

		if ( ! empty( $interval ) ) { // Creating recurring action.
			$action_type = 'recurring';
			$function    = 'as_schedule_recurring_action';

			$function_args = array_filter(
				$function_args,
				static function( $key ) {
					return in_array( $key, array( 'start', 'interval', 'hook', 'callback_args', 'group', 'unique', 'priority' ), true );
				},
				ARRAY_FILTER_USE_KEY
			);
		} elseif ( ! empty( $cron ) ) { // Creating cron action.
			$action_type = 'cron';
			$function    = 'as_schedule_cron_action';

			$function_args = array_filter(
				$function_args,
				static function( $key ) {
					return in_array( $key, array( 'start', 'cron', 'hook', 'callback_args', 'group', 'unique', 'priority' ), true );
				},
				ARRAY_FILTER_USE_KEY
			);
		} elseif ( in_array( $function_args['start'], static::ASYNC_OPTS, true ) ) { // Enqueue async action.
			$action_type = 'async';
			$function    = 'as_enqueue_async_action';

			$function_args = array_filter(
				$function_args,
				static function( $key ) {
					return in_array( $key, array( 'hook', 'callback_args', 'group', 'unique', 'priority' ), true );
				},
				ARRAY_FILTER_USE_KEY
			);
		} else { // Enqueue single action.
			$function_args = array_filter(
				$function_args,
				static function( $key ) {
					return in_array( $key, array( 'start', 'hook', 'callback_args', 'group', 'unique', 'priority' ), true );
				},
				ARRAY_FILTER_USE_KEY
			);
		}

		$function_args = array_values( $function_args );

		try {
			$action_id = call_user_func_array( $function, $function_args );
		} catch ( \Exception $e ) {
			$this->print_error( $e );
		}

		if ( 0 === $action_id ) {
			$e = new \Exception( __( 'Unable to create a scheduled action.', 'action-scheduler' ) );
			$this->print_error( $e );
		}

		$this->print_success( $action_id, $action_type );
	}

	/**
	 * Print a success message with the action ID.
	 *
	 * @param int    $action_id   Created action ID.
	 * @param string $action_type Type of action.
	 *
	 * @return void
	 */
	protected function print_success( $action_id, $action_type ) {
		\WP_CLI::success(
			sprintf(
				/* translators: %1$s: type of action, %2$d: ID of the created action */
				__( '%1$s action (%2$d) scheduled.', 'action-scheduler' ),
				ucfirst( $action_type ),
				$action_id
			)
		);
	}

	/**
	 * Convert an exception into a WP CLI error.
	 *
	 * @param \Exception $e The error object.
	 * @throws \WP_CLI\ExitException When an error occurs.
	 * @return void
	 */
	protected function print_error( \Exception $e ) {
		\WP_CLI::error(
			sprintf(
				/* translators: %s refers to the exception error message. */
				__( 'There was an error creating the scheduled action: %s', 'action-scheduler' ),
				$e->getMessage()
			)
		);
	}

}
