<?php

namespace Action_Scheduler\WP_CLI\Action;

/**
 * WP-CLI command: action-scheduler action get
 */
class Get_Command extends \ActionScheduler_WPCLI_Command {

	/**
	 * Execute command.
	 *
	 * @return void
	 */
	public function execute() {
		$action_id = $this->args[0];
		$store     = \ActionScheduler::store();
		$logger    = \ActionScheduler::logger();
		$action    = $store->fetch_action( $action_id );

		if ( is_a( $action, ActionScheduler_NullAction::class ) ) {
			/* translators: %d is action ID. */
			\WP_CLI::error( sprintf( esc_html__( 'Unable to retrieve action %d.', 'action-scheduler' ), $action_id ) );
		}

		$only_logs   = ! empty( $this->assoc_args['field'] ) && 'log_entries' === $this->assoc_args['field'];
		$only_logs   = $only_logs || ( ! empty( $this->assoc_args['fields'] ) && 'log_entries' === $this->assoc_args['fields'] );
		$log_entries = array();

		foreach ( $logger->get_logs( $action_id ) as $log_entry ) {
			$log_entries[] = array(
				'date'    => $log_entry->get_date()->format( static::DATE_FORMAT ),
				'message' => $log_entry->get_message(),
			);
		}

		if ( $only_logs ) {
			$args = array(
				'format' => \WP_CLI\Utils\get_flag_value( $this->assoc_args, 'format', 'table' ),
			);

			$formatter = new \WP_CLI\Formatter( $args, array( 'date', 'message' ) );
			$formatter->display_items( $log_entries );

			return;
		}

		try {
			$status = $store->get_status( $action_id );
		} catch ( \Exception $e ) {
			\WP_CLI::error( $e->getMessage() );
		}

		$action_arr = array(
			'id'             => $this->args[0],
			'hook'           => $action->get_hook(),
			'status'         => $status,
			'args'           => $action->get_args(),
			'group'          => $action->get_group(),
			'recurring'      => $action->get_schedule()->is_recurring() ? 'yes' : 'no',
			'scheduled_date' => $this->get_schedule_display_string( $action->get_schedule() ),
			'log_entries'    => $log_entries,
		);

		$fields = array_keys( $action_arr );

		if ( ! empty( $this->assoc_args['fields'] ) ) {
			$fields = explode( ',', $this->assoc_args['fields'] );
		}

		$formatter = new \WP_CLI\Formatter( $this->assoc_args, $fields );
		$formatter->display_item( $action_arr );
	}

}
