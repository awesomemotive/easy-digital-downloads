<?php

/**
 * Class ActionScheduler_RecurringActionScheduler
 *
 * This class ensures that the `action_scheduler_ensure_recurring_actions` hook is triggered on a daily interval. This
 * simplifies the process for other plugins to register their recurring actions without requiring each plugin to query
 * or schedule actions independently on every request.
 */
class ActionScheduler_RecurringActionScheduler {

	/**
	 * @var string The hook of the scheduled recurring action that is run to trigger the
	 *      `action_scheduler_ensure_recurring_actions` hook that plugins should use.  We can't directly have the
	 *      scheduled action hook be the hook plugins should use because the actions will show as failed if no plugin
	 *      was actively hooked into it.
	 */
	private const RUN_SCHEDULED_RECURRING_ACTIONS_HOOK = 'action_scheduler_run_recurring_actions_schedule_hook';

	/**
	 * Initialize the instance.  Should only be run on a single instance per request.
	 *
	 * @return void
	 */
	public function init(): void {
		add_action( self::RUN_SCHEDULED_RECURRING_ACTIONS_HOOK, array( $this, 'run_recurring_scheduler_hook' ) );
		if ( is_admin() && ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) ) {
			add_action( 'action_scheduler_init', array( $this, 'schedule_recurring_scheduler_hook' ) );
		}
	}

	/**
	 * Schedule the recurring `action_scheduler_ensure_recurring_actions` action if not already scheduled.
	 *
	 * @return void
	 */
	public function schedule_recurring_scheduler_hook(): void {
		if ( false === get_transient( 'as_is_ensure_recurring_actions_scheduled' ) ) {
			if ( ! as_has_scheduled_action( self::RUN_SCHEDULED_RECURRING_ACTIONS_HOOK ) ) {
				as_schedule_recurring_action(
					time(),
					DAY_IN_SECONDS,
					self::RUN_SCHEDULED_RECURRING_ACTIONS_HOOK,
					[],
					'ActionScheduler',
					true,
					20
				);
			}
			set_transient( 'as_is_ensure_recurring_actions_scheduled', true, HOUR_IN_SECONDS );
		}
	}

	/**
	 * Trigger the hook to allow other plugins to schedule their recurring actions.
	 *
	 * @return void
	 */
	public function run_recurring_scheduler_hook(): void {
		/**
		 * Fires to allow extensions to verify and ensure their recurring actions are scheduled.
		 *
		 * This action is scheduled to trigger once every 24 hrs for the purpose of having 3rd party plugins verify that
		 * any previously scheduled recurring actions are still scheduled. Because recurring actions could stop getting
		 * rescheduled by default due to excessive failures, database issues, or other interruptions, extensions can use
		 * this hook to check for the existence of their recurring actions and reschedule them if necessary.
		 *
		 * Example usage:
		 *
		 * add_action('action_scheduler_ensure_recurring_actions', function() {
		 *     // Check if the recurring action is scheduled, and reschedule if missing.
		 *     if ( ! as_has_scheduled_action('my_recurring_action') ) {
		 *         as_schedule_recurring_action( time(), HOUR_IN_SECONDS, 'my_recurring_action' );
		 *     }
		 * });
		 *
		 * @since 3.9.3
		 */
		do_action( 'action_scheduler_ensure_recurring_actions' );
	}
}
