<?php

/**
 * Class ActionScheduler_ActionFactory
 */
class ActionScheduler_ActionFactory {

	/**
	 * Return stored actions for given params.
	 *
	 * @param string                        $status The action's status in the data store.
	 * @param string                        $hook The hook to trigger when this action runs.
	 * @param array                         $args Args to pass to callbacks when the hook is triggered.
	 * @param ActionScheduler_Schedule|null $schedule The action's schedule.
	 * @param string                        $group A group to put the action in.
	 * phpcs:ignore Squiz.Commenting.FunctionComment.ExtraParamComment
	 * @param int                           $priority The action priority.
	 *
	 * @return ActionScheduler_Action An instance of the stored action.
	 */
	public function get_stored_action( $status, $hook, array $args = array(), ?ActionScheduler_Schedule $schedule = null, $group = '' ) {
		// The 6th parameter ($priority) is not formally declared in the method signature to maintain compatibility with
		// third-party subclasses created before this param was added.
		$priority = func_num_args() >= 6 ? (int) func_get_arg( 5 ) : 10;

		switch ( $status ) {
			case ActionScheduler_Store::STATUS_PENDING:
				$action_class = 'ActionScheduler_Action';
				break;
			case ActionScheduler_Store::STATUS_CANCELED:
				$action_class = 'ActionScheduler_CanceledAction';
				if ( ! is_null( $schedule ) && ! is_a( $schedule, 'ActionScheduler_CanceledSchedule' ) && ! is_a( $schedule, 'ActionScheduler_NullSchedule' ) ) {
					$schedule = new ActionScheduler_CanceledSchedule( $schedule->get_date() );
				}
				break;
			default:
				$action_class = 'ActionScheduler_FinishedAction';
				break;
		}

		$action_class = apply_filters( 'action_scheduler_stored_action_class', $action_class, $status, $hook, $args, $schedule, $group );

		$action = new $action_class( $hook, $args, $schedule, $group );
		$action->set_priority( $priority );

		/**
		 * Allow 3rd party code to change the instantiated action for a given hook, args, schedule and group.
		 *
		 * @param ActionScheduler_Action   $action The instantiated action.
		 * @param string                   $hook The instantiated action's hook.
		 * @param array                    $args The instantiated action's args.
		 * @param ActionScheduler_Schedule $schedule The instantiated action's schedule.
		 * @param string                   $group The instantiated action's group.
		 * @param int                      $priority The action priority.
		 */
		return apply_filters( 'action_scheduler_stored_action_instance', $action, $hook, $args, $schedule, $group, $priority );
	}

	/**
	 * Enqueue an action to run one time, as soon as possible (rather a specific scheduled time).
	 *
	 * This method creates a new action using the NullSchedule. In practice, this results in an action scheduled to
	 * execute "now". Therefore, it will generally run as soon as possible but is not prioritized ahead of other actions
	 * that are already past-due.
	 *
	 * @param string $hook The hook to trigger when this action runs.
	 * @param array  $args Args to pass when the hook is triggered.
	 * @param string $group A group to put the action in.
	 *
	 * @return int The ID of the stored action.
	 */
	public function async( $hook, $args = array(), $group = '' ) {
		return $this->async_unique( $hook, $args, $group, false );
	}

	/**
	 * Same as async, but also supports $unique param.
	 *
	 * @param string $hook The hook to trigger when this action runs.
	 * @param array  $args Args to pass when the hook is triggered.
	 * @param string $group A group to put the action in.
	 * @param bool   $unique Whether to ensure the action is unique.
	 *
	 * @return int The ID of the stored action.
	 */
	public function async_unique( $hook, $args = array(), $group = '', $unique = true ) {
		$schedule = new ActionScheduler_NullSchedule();
		$action   = new ActionScheduler_Action( $hook, $args, $schedule, $group );
		return $unique ? $this->store_unique_action( $action, $unique ) : $this->store( $action );
	}

	/**
	 * Create single action.
	 *
	 * @param string $hook  The hook to trigger when this action runs.
	 * @param array  $args  Args to pass when the hook is triggered.
	 * @param int    $when  Unix timestamp when the action will run.
	 * @param string $group A group to put the action in.
	 *
	 * @return int The ID of the stored action.
	 */
	public function single( $hook, $args = array(), $when = null, $group = '' ) {
		return $this->single_unique( $hook, $args, $when, $group, false );
	}

	/**
	 * Create single action only if there is no pending or running action with same name and params.
	 *
	 * @param string $hook The hook to trigger when this action runs.
	 * @param array  $args Args to pass when the hook is triggered.
	 * @param int    $when Unix timestamp when the action will run.
	 * @param string $group A group to put the action in.
	 * @param bool   $unique Whether action scheduled should be unique.
	 *
	 * @return int The ID of the stored action.
	 */
	public function single_unique( $hook, $args = array(), $when = null, $group = '', $unique = true ) {
		$date     = as_get_datetime_object( $when );
		$schedule = new ActionScheduler_SimpleSchedule( $date );
		$action   = new ActionScheduler_Action( $hook, $args, $schedule, $group );
		return $unique ? $this->store_unique_action( $action ) : $this->store( $action );
	}

	/**
	 * Create the first instance of an action recurring on a given interval.
	 *
	 * @param string $hook The hook to trigger when this action runs.
	 * @param array  $args Args to pass when the hook is triggered.
	 * @param int    $first Unix timestamp for the first run.
	 * @param int    $interval Seconds between runs.
	 * @param string $group A group to put the action in.
	 *
	 * @return int The ID of the stored action.
	 */
	public function recurring( $hook, $args = array(), $first = null, $interval = null, $group = '' ) {
		return $this->recurring_unique( $hook, $args, $first, $interval, $group, false );
	}

	/**
	 * Create the first instance of an action recurring on a given interval only if there is no pending or running action with same name and params.
	 *
	 * @param string $hook The hook to trigger when this action runs.
	 * @param array  $args Args to pass when the hook is triggered.
	 * @param int    $first Unix timestamp for the first run.
	 * @param int    $interval Seconds between runs.
	 * @param string $group A group to put the action in.
	 * @param bool   $unique Whether action scheduled should be unique.
	 *
	 * @return int The ID of the stored action.
	 */
	public function recurring_unique( $hook, $args = array(), $first = null, $interval = null, $group = '', $unique = true ) {
		if ( empty( $interval ) ) {
			return $this->single_unique( $hook, $args, $first, $group, $unique );
		}
		$date     = as_get_datetime_object( $first );
		$schedule = new ActionScheduler_IntervalSchedule( $date, $interval );
		$action   = new ActionScheduler_Action( $hook, $args, $schedule, $group );
		return $unique ? $this->store_unique_action( $action ) : $this->store( $action );
	}

	/**
	 * Create the first instance of an action recurring on a Cron schedule.
	 *
	 * @param string $hook The hook to trigger when this action runs.
	 * @param array  $args Args to pass when the hook is triggered.
	 * @param int    $base_timestamp The first instance of the action will be scheduled
	 *        to run at a time calculated after this timestamp matching the cron
	 *        expression. This can be used to delay the first instance of the action.
	 * @param int    $schedule A cron definition string.
	 * @param string $group A group to put the action in.
	 *
	 * @return int The ID of the stored action.
	 */
	public function cron( $hook, $args = array(), $base_timestamp = null, $schedule = null, $group = '' ) {
		return $this->cron_unique( $hook, $args, $base_timestamp, $schedule, $group, false );
	}


	/**
	 * Create the first instance of an action recurring on a Cron schedule only if there is no pending or running action with same name and params.
	 *
	 * @param string $hook The hook to trigger when this action runs.
	 * @param array  $args Args to pass when the hook is triggered.
	 * @param int    $base_timestamp The first instance of the action will be scheduled
	 *        to run at a time calculated after this timestamp matching the cron
	 *        expression. This can be used to delay the first instance of the action.
	 * @param int    $schedule A cron definition string.
	 * @param string $group A group to put the action in.
	 * @param bool   $unique Whether action scheduled should be unique.
	 *
	 * @return int The ID of the stored action.
	 **/
	public function cron_unique( $hook, $args = array(), $base_timestamp = null, $schedule = null, $group = '', $unique = true ) {
		if ( empty( $schedule ) ) {
			return $this->single_unique( $hook, $args, $base_timestamp, $group, $unique );
		}
		$date     = as_get_datetime_object( $base_timestamp );
		$cron     = CronExpression::factory( $schedule );
		$schedule = new ActionScheduler_CronSchedule( $date, $cron );
		$action   = new ActionScheduler_Action( $hook, $args, $schedule, $group );
		return $unique ? $this->store_unique_action( $action ) : $this->store( $action );
	}

	/**
	 * Create a successive instance of a recurring or cron action.
	 *
	 * Importantly, the action will be rescheduled to run based on the current date/time.
	 * That means when the action is scheduled to run in the past, the next scheduled date
	 * will be pushed forward. For example, if a recurring action set to run every hour
	 * was scheduled to run 5 seconds ago, it will be next scheduled for 1 hour in the
	 * future, which is 1 hour and 5 seconds from when it was last scheduled to run.
	 *
	 * Alternatively, if the action is scheduled to run in the future, and is run early,
	 * likely via manual intervention, then its schedule will change based on the time now.
	 * For example, if a recurring action set to run every day, and is run 12 hours early,
	 * it will run again in 24 hours, not 36 hours.
	 *
	 * This slippage is less of an issue with Cron actions, as the specific run time can
	 * be set for them to run, e.g. 1am each day. In those cases, and entire period would
	 * need to be missed before there was any change is scheduled, e.g. in the case of an
	 * action scheduled for 1am each day, the action would need to run an entire day late.
	 *
	 * @param ActionScheduler_Action $action The existing action.
	 *
	 * @return string The ID of the stored action
	 * @throws InvalidArgumentException If $action is not a recurring action.
	 */
	public function repeat( $action ) {
		$schedule = $action->get_schedule();
		$next     = $schedule->get_next( as_get_datetime_object() );

		if ( is_null( $next ) || ! $schedule->is_recurring() ) {
			throw new InvalidArgumentException( __( 'Invalid action - must be a recurring action.', 'action-scheduler' ) );
		}

		$schedule_class = get_class( $schedule );
		$new_schedule   = new $schedule( $next, $schedule->get_recurrence(), $schedule->get_first_date() );
		$new_action     = new ActionScheduler_Action( $action->get_hook(), $action->get_args(), $new_schedule, $action->get_group() );
		$new_action->set_priority( $action->get_priority() );
		return $this->store( $new_action );
	}

	/**
	 * Creates a scheduled action.
	 *
	 * This general purpose method can be used in place of specific methods such as async(),
	 * async_unique(), single() or single_unique(), etc.
	 *
	 * @internal Not intended for public use, should not be overridden by subclasses.
	 *
	 * @param array $options {
	 *     Describes the action we wish to schedule.
	 *
	 *     @type string     $type      Must be one of 'async', 'cron', 'recurring', or 'single'.
	 *     @type string     $hook      The hook to be executed.
	 *     @type array      $arguments Arguments to be passed to the callback.
	 *     @type string     $group     The action group.
	 *     @type bool       $unique    If the action should be unique.
	 *     @type int        $when      Timestamp. Indicates when the action, or first instance of the action in the case
	 *                                 of recurring or cron actions, becomes due.
	 *     @type int|string $pattern   Recurrence pattern. This is either an interval in seconds for recurring actions
	 *                                 or a cron expression for cron actions.
	 *     @type int        $priority  Lower values means higher priority. Should be in the range 0-255.
	 * }
	 *
	 * @return int The action ID. Zero if there was an error scheduling the action.
	 */
	public function create( array $options = array() ) {
		$defaults = array(
			'type'      => 'single',
			'hook'      => '',
			'arguments' => array(),
			'group'     => '',
			'unique'    => false,
			'when'      => time(),
			'pattern'   => null,
			'priority'  => 10,
		);

		$options = array_merge( $defaults, $options );

		// Cron/recurring actions without a pattern are treated as single actions (this gives calling code the ability
		// to use functions like as_schedule_recurring_action() to schedule recurring as well as single actions).
		if ( ( 'cron' === $options['type'] || 'recurring' === $options['type'] ) && empty( $options['pattern'] ) ) {
			$options['type'] = 'single';
		}

		switch ( $options['type'] ) {
			case 'async':
				$schedule = new ActionScheduler_NullSchedule();
				break;

			case 'cron':
				$date     = as_get_datetime_object( $options['when'] );
				$cron     = CronExpression::factory( $options['pattern'] );
				$schedule = new ActionScheduler_CronSchedule( $date, $cron );
				break;

			case 'recurring':
				$date     = as_get_datetime_object( $options['when'] );
				$schedule = new ActionScheduler_IntervalSchedule( $date, $options['pattern'] );
				break;

			case 'single':
				$date     = as_get_datetime_object( $options['when'] );
				$schedule = new ActionScheduler_SimpleSchedule( $date );
				break;

			default:
				// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
				error_log( "Unknown action type '{$options['type']}' specified when trying to create an action for '{$options['hook']}'." );
				return 0;
		}

		$action = new ActionScheduler_Action( $options['hook'], $options['arguments'], $schedule, $options['group'] );
		$action->set_priority( $options['priority'] );

		$action_id = 0;
		try {
			$action_id = $options['unique'] ? $this->store_unique_action( $action ) : $this->store( $action );
		} catch ( Exception $e ) {
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			error_log(
				sprintf(
					/* translators: %1$s is the name of the hook to be enqueued, %2$s is the exception message. */
					__( 'Caught exception while enqueuing action "%1$s": %2$s', 'action-scheduler' ),
					$options['hook'],
					$e->getMessage()
				)
			);
		}
		return $action_id;
	}

	/**
	 * Save action to database.
	 *
	 * @param ActionScheduler_Action $action Action object to save.
	 *
	 * @return int The ID of the stored action
	 */
	protected function store( ActionScheduler_Action $action ) {
		$store = ActionScheduler_Store::instance();
		return $store->save_action( $action );
	}

	/**
	 * Store action if it's unique.
	 *
	 * @param ActionScheduler_Action $action Action object to store.
	 *
	 * @return int ID of the created action. Will be 0 if action was not created.
	 */
	protected function store_unique_action( ActionScheduler_Action $action ) {
		$store = ActionScheduler_Store::instance();
		if ( method_exists( $store, 'save_unique_action' ) ) {
			return $store->save_unique_action( $action );
		} else {
			/**
			 * Fallback to non-unique action if the store doesn't support unique actions.
			 * We try to save the action as unique, accepting that there might be a race condition.
			 * This is likely still better than giving up on unique actions entirely.
			 */
			$existing_action_id = (int) $store->find_action(
				$action->get_hook(),
				array(
					'args'   => $action->get_args(),
					'status' => ActionScheduler_Store::STATUS_PENDING,
					'group'  => $action->get_group(),
				)
			);
			if ( $existing_action_id > 0 ) {
				return 0;
			}
			return $store->save_action( $action );
		}
	}
}
