<?php

namespace Action_Scheduler\WP_CLI\Action;

/**
 * WP-CLI command: action-scheduler action delete
 */
class Delete_Command extends \ActionScheduler_WPCLI_Command {

	/**
	 * Array of action IDs to delete.
	 *
	 * @var int[]
	 */
	protected $action_ids = array();

	/**
	 * Number of deleted, failed, and total actions deleted.
	 *
	 * @var array<string, int>
	 */
	protected $action_counts = array(
		'deleted' => 0,
		'failed'  => 0,
		'total'   => 0,
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

		add_action( 'action_scheduler_deleted_action', array( $this, 'on_action_deleted' ) );
	}

	/**
	 * Execute.
	 *
	 * @return void
	 */
	public function execute() {
		$store = \ActionScheduler::store();

		$progress_bar = \WP_CLI\Utils\make_progress_bar(
			sprintf(
				/* translators: %d: number of actions to be deleted */
				_n( 'Deleting %d action', 'Deleting %d actions', $this->action_counts['total'], 'action-scheduler' ),
				number_format_i18n( $this->action_counts['total'] )
			),
			$this->action_counts['total']
		);

		foreach ( $this->action_ids as $action_id ) {
			try {
				$store->delete_action( $action_id );
			} catch ( \Exception $e ) {
				$this->action_counts['failed']++;
				\WP_CLI::warning( $e->getMessage() );
			}

			$progress_bar->tick();
		}

		$progress_bar->finish();

		/* translators: %1$d: number of actions deleted */
		$format = _n( 'Deleted %1$d action', 'Deleted %1$d actions', $this->action_counts['deleted'], 'action-scheduler' ) . ', ';
		/* translators: %2$d: number of actions deletions failed */
		$format .= _n( '%2$d failure.', '%2$d failures.', $this->action_counts['failed'], 'action-scheduler' );

		\WP_CLI::success(
			sprintf(
				$format,
				number_format_i18n( $this->action_counts['deleted'] ),
				number_format_i18n( $this->action_counts['failed'] )
			)
		);
	}

	/**
	 * Action: action_scheduler_deleted_action
	 *
	 * @param int $action_id Action ID.
	 * @return void
	 */
	public function on_action_deleted( $action_id ) {
		if ( 'action_scheduler_deleted_action' !== current_action() ) {
			return;
		}

		$action_id = absint( $action_id );

		if ( ! in_array( $action_id, $this->action_ids, true ) ) {
			return;
		}

		$this->action_counts['deleted']++;
		\WP_CLI::debug( sprintf( 'Action %d was deleted.', $action_id ) );
	}

}
