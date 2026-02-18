<?php

/**
 * Abstract for WP-CLI commands.
 */
abstract class ActionScheduler_WPCLI_Command extends \WP_CLI_Command {

	const DATE_FORMAT = 'Y-m-d H:i:s O';

	/**
	 * Keyed arguments.
	 *
	 * @var string[]
	 */
	protected $args;

	/**
	 * Positional arguments.
	 *
	 * @var array<string, string>
	 */
	protected $assoc_args;

	/**
	 * Construct.
	 *
	 * @param string[]              $args       Positional arguments.
	 * @param array<string, string> $assoc_args Keyed arguments.
	 * @throws \Exception When loading a CLI command file outside of WP CLI context.
	 */
	public function __construct( array $args, array $assoc_args ) {
		if ( ! defined( 'WP_CLI' ) || ! constant( 'WP_CLI' ) ) {
			/* translators: %s php class name */
			throw new \Exception( sprintf( __( 'The %s class can only be run within WP CLI.', 'action-scheduler' ), get_class( $this ) ) );
		}

		$this->args       = $args;
		$this->assoc_args = $assoc_args;
	}

	/**
	 * Execute command.
	 */
	abstract public function execute();

	/**
	 * Get the scheduled date in a human friendly format.
	 *
	 * @see ActionScheduler_ListTable::get_schedule_display_string()
	 * @param ActionScheduler_Schedule $schedule Schedule.
	 * @return string
	 */
	protected function get_schedule_display_string( ActionScheduler_Schedule $schedule ) {

		$schedule_display_string = '';

		if ( ! $schedule->get_date() ) {
			return '0000-00-00 00:00:00';
		}

		$next_timestamp = $schedule->get_date()->getTimestamp();

		$schedule_display_string .= $schedule->get_date()->format( static::DATE_FORMAT );

		return $schedule_display_string;
	}

	/**
	 * Transforms arguments with '__' from CSV into expected arrays.
	 *
	 * @see \WP_CLI\CommandWithDBObject::process_csv_arguments_to_arrays()
	 * @link https://github.com/wp-cli/entity-command/blob/c270cc9a2367cb8f5845f26a6b5e203397c91392/src/WP_CLI/CommandWithDBObject.php#L99
	 * @return void
	 */
	protected function process_csv_arguments_to_arrays() {
		foreach ( $this->assoc_args as $k => $v ) {
			if ( false !== strpos( $k, '__' ) ) {
				$this->assoc_args[ $k ] = explode( ',', $v );
			}
		}
	}

}
