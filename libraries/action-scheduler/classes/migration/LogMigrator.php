<?php


namespace Action_Scheduler\Migration;

use ActionScheduler_Logger;

/**
 * Class LogMigrator
 *
 * @package Action_Scheduler\Migration
 *
 * @since 3.0.0
 *
 * @codeCoverageIgnore
 */
class LogMigrator {
	/**
	 * Source logger instance.
	 *
	 * @var ActionScheduler_Logger
	 */
	private $source;

	/**
	 * Destination logger instance.
	 *
	 * @var ActionScheduler_Logger
	 */
	private $destination;

	/**
	 * ActionMigrator constructor.
	 *
	 * @param ActionScheduler_Logger $source_logger Source logger object.
	 * @param ActionScheduler_Logger $destination_logger Destination logger object.
	 */
	public function __construct( ActionScheduler_Logger $source_logger, ActionScheduler_Logger $destination_logger ) {
		$this->source      = $source_logger;
		$this->destination = $destination_logger;
	}

	/**
	 * Migrate an action log.
	 *
	 * @param int $source_action_id Source logger object.
	 * @param int $destination_action_id Destination logger object.
	 */
	public function migrate( $source_action_id, $destination_action_id ) {
		$logs = $this->source->get_logs( $source_action_id );

		foreach ( $logs as $log ) {
			if ( absint( $log->get_action_id() ) === absint( $source_action_id ) ) {
				$this->destination->log( $destination_action_id, $log->get_message(), $log->get_date() );
			}
		}
	}
}
