<?php

/**
 * Commands for Action Scheduler.
 */
class ActionScheduler_WPCLI_Clean_Command extends WP_CLI_Command {
	/**
	 * Run the Action Scheduler Queue Cleaner
	 *
	 * ## OPTIONS
	 *
	 * [--batch-size=<size>]
	 * : The maximum number of actions to delete per batch. Defaults to 20.
	 *
	 * [--batches=<size>]
	 * : Limit execution to a number of batches. Defaults to 0, meaning batches will continue all eligible actions are deleted.
	 *
	 * [--status=<status>]
	 * : Only clean actions with the specified status. Defaults to Canceled, Completed. Define multiple statuses as a comma separated string (without spaces), e.g. `--status=complete,failed,canceled`
	 *
	 * [--before=<datestring>]
	 * : Only delete actions with scheduled date older than this. Defaults to 31 days. e.g `--before='7 days ago'`, `--before='02-Feb-2020 20:20:20'`
	 *
	 * [--pause=<seconds>]
	 * : The number of seconds to pause between batches. Default no pause.
	 *
	 * @param array $args Positional arguments.
	 * @param array $assoc_args Keyed arguments.
	 * @throws \WP_CLI\ExitException When an error occurs.
	 *
	 * @subcommand clean
	 */
	public function clean( $args, $assoc_args ) {
		// Handle passed arguments.
		$batch   = absint( \WP_CLI\Utils\get_flag_value( $assoc_args, 'batch-size', 20 ) );
		$batches = absint( \WP_CLI\Utils\get_flag_value( $assoc_args, 'batches', 0 ) );
		$status  = explode( ',', WP_CLI\Utils\get_flag_value( $assoc_args, 'status', '' ) );
		$status  = array_filter( array_map( 'trim', $status ) );
		$before  = \WP_CLI\Utils\get_flag_value( $assoc_args, 'before', '' );
		$sleep   = \WP_CLI\Utils\get_flag_value( $assoc_args, 'pause', 0 );

		$batches_completed = 0;
		$actions_deleted   = 0;
		$unlimited         = 0 === $batches;
		try {
			$lifespan = as_get_datetime_object( $before );
		} catch ( Exception $e ) {
			$lifespan = null;
		}

		try {
			// Custom queue cleaner instance.
			$cleaner = new ActionScheduler_QueueCleaner( null, $batch );

			// Clean actions for as long as possible.
			while ( $unlimited || $batches_completed < $batches ) {
				if ( $sleep && $batches_completed > 0 ) {
					sleep( $sleep );
				}

				$deleted = count( $cleaner->clean_actions( $status, $lifespan, null, 'CLI' ) );
				if ( $deleted <= 0 ) {
					break;
				}
				$actions_deleted += $deleted;
				$batches_completed++;
				$this->print_success( $deleted );
			}
		} catch ( Exception $e ) {
			$this->print_error( $e );
		}

		$this->print_total_batches( $batches_completed );
		if ( $batches_completed > 1 ) {
			$this->print_success( $actions_deleted );
		}
	}

	/**
	 * Print WP CLI message about how many batches of actions were processed.
	 *
	 * @param int $batches_processed Number of batches processed.
	 */
	protected function print_total_batches( int $batches_processed ) {
		WP_CLI::log(
			sprintf(
				/* translators: %d refers to the total number of batches processed */
				_n( '%d batch processed.', '%d batches processed.', $batches_processed, 'action-scheduler' ),
				$batches_processed
			)
		);
	}

	/**
	 * Convert an exception into a WP CLI error.
	 *
	 * @param Exception $e The error object.
	 */
	protected function print_error( Exception $e ) {
		WP_CLI::error(
			sprintf(
				/* translators: %s refers to the exception error message */
				__( 'There was an error deleting an action: %s', 'action-scheduler' ),
				$e->getMessage()
			)
		);
	}

	/**
	 * Print a success message with the number of completed actions.
	 *
	 * @param int $actions_deleted Number of deleted actions.
	 */
	protected function print_success( int $actions_deleted ) {
		WP_CLI::success(
			sprintf(
				/* translators: %d refers to the total number of actions deleted */
				_n( '%d action deleted.', '%d actions deleted.', $actions_deleted, 'action-scheduler' ),
				$actions_deleted
			)
		);
	}
}
