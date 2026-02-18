<?php

namespace Action_Scheduler\WP_CLI\Action;

/**
 * WP-CLI command: action-scheduler action run
 */
class Run_Command extends \ActionScheduler_WPCLI_Command {

	/**
	 * Array of action IDs to execute.
	 *
	 * @var int[]
	 */
	protected $action_ids = array();

	/**
	 * Number of executed, failed, ignored, invalid, and total actions.
	 *
	 * @var array<string, int>
	 */
	protected $action_counts = array(
		'executed' => 0,
		'failed'   => 0,
		'ignored'  => 0,
		'invalid'  => 0,
		'total'    => 0,
	);

	/**
	 * Construct.
	 *
	 * @param string[]              $args       Positional arguments.
	 * @param array<string, string> $assoc_args Keyed arguments.
	 */
	public function __construct( array $args, array $assoc_args ) {
		parent::__construct( $args, $assoc_args );

		$this->action_ids             = array_map( 'absint', $args );
		$this->action_counts['total'] = count( $this->action_ids );

		add_action( 'action_scheduler_execution_ignored', array( $this, 'on_action_ignored' ) );
		add_action( 'action_scheduler_after_execute', array( $this, 'on_action_executed' ) );
		add_action( 'action_scheduler_failed_execution', array( $this, 'on_action_failed' ), 10, 2 );
		add_action( 'action_scheduler_failed_validation', array( $this, 'on_action_invalid' ), 10, 2 );
	}

	/**
	 * Execute.
	 *
	 * @return void
	 */
	public function execute() {
		$runner = \ActionScheduler::runner();

		$progress_bar = \WP_CLI\Utils\make_progress_bar(
			sprintf(
				/* translators: %d: number of actions */
				_n( 'Executing %d action', 'Executing %d actions', $this->action_counts['total'], 'action-scheduler' ),
				number_format_i18n( $this->action_counts['total'] )
			),
			$this->action_counts['total']
		);

		foreach ( $this->action_ids as $action_id ) {
			$runner->process_action( $action_id, 'Action Scheduler CLI' );
			$progress_bar->tick();
		}

		$progress_bar->finish();

		foreach ( array(
			'ignored',
			'invalid',
			'failed',
		) as $type ) {
			$count = $this->action_counts[ $type ];

			if ( empty( $count ) ) {
				continue;
			}

			/*
			 * translators:
			 * %1$d: count of actions evaluated.
			 * %2$s: type of action evaluated.
			 */
			$format = _n( '%1$d action %2$s.', '%1$d actions %2$s.', $count, 'action-scheduler' );

			\WP_CLI::warning(
				sprintf(
					$format,
					number_format_i18n( $count ),
					$type
				)
			);
		}

		\WP_CLI::success(
			sprintf(
				/* translators: %d: number of executed actions */
				_n( 'Executed %d action.', 'Executed %d actions.', $this->action_counts['executed'], 'action-scheduler' ),
				number_format_i18n( $this->action_counts['executed'] )
			)
		);
	}

	/**
	 * Action: action_scheduler_execution_ignored
	 *
	 * @param int $action_id Action ID.
	 * @return void
	 */
	public function on_action_ignored( $action_id ) {
		if ( 'action_scheduler_execution_ignored' !== current_action() ) {
			return;
		}

		$action_id = absint( $action_id );

		if ( ! in_array( $action_id, $this->action_ids, true ) ) {
			return;
		}

		$this->action_counts['ignored']++;
		\WP_CLI::debug( sprintf( 'Action %d was ignored.', $action_id ) );
	}

	/**
	 * Action: action_scheduler_after_execute
	 *
	 * @param int $action_id Action ID.
	 * @return void
	 */
	public function on_action_executed( $action_id ) {
		if ( 'action_scheduler_after_execute' !== current_action() ) {
			return;
		}

		$action_id = absint( $action_id );

		if ( ! in_array( $action_id, $this->action_ids, true ) ) {
			return;
		}

		$this->action_counts['executed']++;
		\WP_CLI::debug( sprintf( 'Action %d was executed.', $action_id ) );
	}

	/**
	 * Action: action_scheduler_failed_execution
	 *
	 * @param int        $action_id Action ID.
	 * @param \Exception $e         Exception.
	 * @return void
	 */
	public function on_action_failed( $action_id, \Exception $e ) {
		if ( 'action_scheduler_failed_execution' !== current_action() ) {
			return;
		}

		$action_id = absint( $action_id );

		if ( ! in_array( $action_id, $this->action_ids, true ) ) {
			return;
		}

		$this->action_counts['failed']++;
		\WP_CLI::debug( sprintf( 'Action %d failed execution: %s', $action_id, $e->getMessage() ) );
	}

	/**
	 * Action: action_scheduler_failed_validation
	 *
	 * @param int        $action_id Action ID.
	 * @param \Exception $e         Exception.
	 * @return void
	 */
	public function on_action_invalid( $action_id, \Exception $e ) {
		if ( 'action_scheduler_failed_validation' !== current_action() ) {
			return;
		}

		$action_id = absint( $action_id );

		if ( ! in_array( $action_id, $this->action_ids, true ) ) {
			return;
		}

		$this->action_counts['invalid']++;
		\WP_CLI::debug( sprintf( 'Action %d failed validation: %s', $action_id, $e->getMessage() ) );
	}

}
