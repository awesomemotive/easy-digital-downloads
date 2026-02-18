<?php

namespace Action_Scheduler\WP_CLI;

use WP_CLI;

/**
 * Action command for Action Scheduler.
 */
class Action_Command extends \WP_CLI_Command {

	/**
	 * Cancel the next occurrence or all occurrences of a scheduled action.
	 *
	 * ## OPTIONS
	 *
	 * [<hook>]
	 * : Name of the action hook.
	 *
	 * [--group=<group>]
	 * : The group the job is assigned to.
	 *
	 * [--args=<args>]
	 * : JSON object of arguments assigned to the job.
	 * ---
	 * default: []
	 * ---
	 *
	 * [--all]
	 * : Cancel all occurrences of a scheduled action.
	 *
	 * @param array $args       Positional arguments.
	 * @param array $assoc_args Keyed arguments.
	 * @return void
	 */
	public function cancel( array $args, array $assoc_args ) {
		require_once 'Action/Cancel_Command.php';
		$command = new Action\Cancel_Command( $args, $assoc_args );
		$command->execute();
	}

	/**
	 * Creates a new scheduled action.
	 *
	 * ## OPTIONS
	 *
	 * <hook>
	 * : Name of the action hook.
	 *
	 * <start>
	 * : A unix timestamp representing the date you want the action to start. Also 'async' or 'now' to enqueue an async action.
	 *
	 * [--args=<args>]
	 * : JSON object of arguments to pass to callbacks when the hook triggers.
	 * ---
	 * default: []
	 * ---
	 *
	 * [--cron=<cron>]
	 * : A cron-like schedule string (https://crontab.guru/).
	 * ---
	 * default: ''
	 * ---
	 *
	 * [--group=<group>]
	 * : The group to assign this job to.
	 * ---
	 * default: ''
	 * ---
	 *
	 * [--interval=<interval>]
	 * : Number of seconds to wait between runs.
	 * ---
	 * default: 0
	 * ---
	 *
	 * ## EXAMPLES
	 *
	 *     wp action-scheduler action create hook_async async
	 *     wp action-scheduler action create hook_single 1627147598
	 *     wp action-scheduler action create hook_recurring 1627148188 --interval=5
	 *     wp action-scheduler action create hook_cron 1627147655 --cron='5 4 * * *'
	 *
	 * @param array $args       Positional arguments.
	 * @param array $assoc_args Keyed arguments.
	 * @return void
	 */
	public function create( array $args, array $assoc_args ) {
		require_once 'Action/Create_Command.php';
		$command = new Action\Create_Command( $args, $assoc_args );
		$command->execute();
	}

	/**
	 * Delete existing scheduled action(s).
	 *
	 * ## OPTIONS
	 *
	 * <id>...
	 * : One or more IDs of actions to delete.
	 * ---
	 * default: 0
	 * ---
	 *
	 * ## EXAMPLES
	 *
	 *     # Delete the action with id 100
	 *     $ wp action-scheduler action delete 100
	 *
	 *     # Delete the actions with ids 100 and 200
	 *     $ wp action-scheduler action delete 100 200
	 *
	 *     # Delete the first five pending actions in 'action-scheduler' group
	 *     $ wp action-scheduler action delete $( wp action-scheduler action list --status=pending --group=action-scheduler --format=ids )
	 *
	 * @param array $args       Positional arguments.
	 * @param array $assoc_args Keyed arguments.
	 * @return void
	 */
	public function delete( array $args, array $assoc_args ) {
		require_once 'Action/Delete_Command.php';
		$command = new Action\Delete_Command( $args, $assoc_args );
		$command->execute();
	}

	/**
	 * Generates some scheduled actions.
	 *
	 * ## OPTIONS
	 *
	 * <hook>
	 * : Name of the action hook.
	 *
	 * <start>
	 * : The Unix timestamp representing the date you want the action to start.
	 *
	 * [--count=<count>]
	 * : Number of actions to create.
	 * ---
	 * default: 1
	 * ---
	 *
	 * [--interval=<interval>]
	 * : Number of seconds to wait between runs.
	 * ---
	 * default: 0
	 * ---
	 *
	 * [--args=<args>]
	 * : JSON object of arguments to pass to callbacks when the hook triggers.
	 * ---
	 * default: []
	 * ---
	 *
	 * [--group=<group>]
	 * : The group to assign this job to.
	 * ---
	 * default: ''
	 * ---
	 *
	 * ## EXAMPLES
	 *
	 *     wp action-scheduler action generate test_multiple 1627147598 --count=5 --interval=5
	 *
	 * @param array $args       Positional arguments.
	 * @param array $assoc_args Keyed arguments.
	 * @return void
	 */
	public function generate( array $args, array $assoc_args ) {
		require_once 'Action/Generate_Command.php';
		$command = new Action\Generate_Command( $args, $assoc_args );
		$command->execute();
	}

	/**
	 * Get details about a scheduled action.
	 *
	 * ## OPTIONS
	 *
	 * <id>
	 * : The ID of the action to get.
	 * ---
	 * default: 0
	 * ---
	 *
	 * [--field=<field>]
	 * : Instead of returning the whole action, returns the value of a single field.
	 *
	 * [--fields=<fields>]
	 * : Limit the output to specific fields (comma-separated). Defaults to all fields.
	 *
	 * [--format=<format>]
	 * : Render output in a particular format.
	 * ---
	 * default: table
	 * options:
	 *   - table
	 *   - csv
	 *   - json
	 *   - yaml
	 * ---
	 *
	 * @param array $args       Positional arguments.
	 * @param array $assoc_args Keyed arguments.
	 * @return void
	 */
	public function get( array $args, array $assoc_args ) {
		require_once 'Action/Get_Command.php';
		$command = new Action\Get_Command( $args, $assoc_args );
		$command->execute();
	}

	/**
	 * Get a list of scheduled actions.
	 *
	 * Display actions based on all arguments supported by
	 * [as_get_scheduled_actions()](https://actionscheduler.org/api/#function-reference--as_get_scheduled_actions).
	 *
	 * ## OPTIONS
	 *
	 * [--<field>=<value>]
	 * : One or more arguments to pass to as_get_scheduled_actions().
	 *
	 * [--field=<field>]
	 * : Prints the value of a single property for each action.
	 *
	 * [--fields=<fields>]
	 * : Limit the output to specific object properties.
	 *
	 * [--format=<format>]
	 * : Render output in a particular format.
	 * ---
	 * default: table
	 * options:
	 *   - table
	 *   - csv
	 *   - ids
	 *   - json
	 *   - count
	 *   - yaml
	 * ---
	 *
	 * ## AVAILABLE FIELDS
	 *
	 * These fields will be displayed by default for each action:
	 *
	 * * id
	 * * hook
	 * * status
	 * * group
	 * * recurring
	 * * scheduled_date
	 *
	 * These fields are optionally available:
	 *
	 * * args
	 * * log_entries
	 *
	 * @param array $args       Positional arguments.
	 * @param array $assoc_args Keyed arguments.
	 * @return void
	 *
	 * @subcommand list
	 */
	public function subcommand_list( array $args, array $assoc_args ) {
		require_once 'Action/List_Command.php';
		$command = new Action\List_Command( $args, $assoc_args );
		$command->execute();
	}

	/**
	 * Get logs for a scheduled action.
	 *
	 * ## OPTIONS
	 *
	 * <id>
	 * : The ID of the action to get.
	 * ---
	 * default: 0
	 * ---
	 *
	 * @param array $args Positional arguments.
	 * @return void
	 */
	public function logs( array $args ) {
		$command = sprintf( 'action-scheduler action get %d --field=log_entries', $args[0] );
		WP_CLI::runcommand( $command );
	}

	/**
	 * Get the ID or timestamp of the next scheduled action.
	 *
	 * ## OPTIONS
	 *
	 * <hook>
	 * : The hook of the next scheduled action.
	 *
	 * [--args=<args>]
	 * : JSON object of arguments to search for next scheduled action.
	 * ---
	 * default: []
	 * ---
	 *
	 * [--group=<group>]
	 * : The group to which the next scheduled action is assigned.
	 * ---
	 * default: ''
	 * ---
	 *
	 * [--raw]
	 * : Display the raw output of as_next_scheduled_action() (timestamp or boolean).
	 *
	 * @param array $args       Positional arguments.
	 * @param array $assoc_args Keyed arguments.
	 * @return void
	 */
	public function next( array $args, array $assoc_args ) {
		require_once 'Action/Next_Command.php';
		$command = new Action\Next_Command( $args, $assoc_args );
		$command->execute();
	}

	/**
	 * Run existing scheduled action(s).
	 *
	 * ## OPTIONS
	 *
	 * <id>...
	 * : One or more IDs of actions to run.
	 * ---
	 * default: 0
	 * ---
	 *
	 * ## EXAMPLES
	 *
	 *     # Run the action with id 100
	 *     $ wp action-scheduler action run 100
	 *
	 *     # Run the actions with ids 100 and 200
	 *     $ wp action-scheduler action run 100 200
	 *
	 *     # Run the first five pending actions in 'action-scheduler' group
	 *     $ wp action-scheduler action run $( wp action-scheduler action list --status=pending --group=action-scheduler --format=ids )
	 *
	 * @param array $args       Positional arguments.
	 * @param array $assoc_args Keyed arguments.
	 * @return void
	 */
	public function run( array $args, array $assoc_args ) {
		require_once 'Action/Run_Command.php';
		$command = new Action\Run_Command( $args, $assoc_args );
		$command->execute();
	}

}
