<?php

namespace Action_Scheduler\WP_CLI\Action;

use function \WP_CLI\Utils\get_flag_value;

/**
 * WP-CLI command: action-scheduler action cancel
 */
class Cancel_Command extends \ActionScheduler_WPCLI_Command {

	/**
	 * Execute command.
	 *
	 * @return void
	 */
	public function execute() {
		$hook          = '';
		$group         = get_flag_value( $this->assoc_args, 'group', '' );
		$callback_args = get_flag_value( $this->assoc_args, 'args', null );
		$all           = get_flag_value( $this->assoc_args, 'all', false );

		if ( ! empty( $this->args[0] ) ) {
			$hook = $this->args[0];
		}

		if ( ! empty( $callback_args ) ) {
			$callback_args = json_decode( $callback_args, true );
		}

		if ( $all ) {
			$this->cancel_all( $hook, $callback_args, $group );
			return;
		}

		$this->cancel_single( $hook, $callback_args, $group );
	}

	/**
	 * Cancel single action.
	 *
	 * @param string $hook The hook that the job will trigger.
	 * @param array  $callback_args Args that would have been passed to the job.
	 * @param string $group The group the job is assigned to.
	 * @return void
	 */
	protected function cancel_single( $hook, $callback_args, $group ) {
		if ( empty( $hook ) ) {
			\WP_CLI::error( __( 'Please specify hook of action to cancel.', 'action-scheduler' ) );
		}

		try {
			$result = as_unschedule_action( $hook, $callback_args, $group );
		} catch ( \Exception $e ) {
			$this->print_error( $e, false );
		}

		if ( null === $result ) {
			$e = new \Exception( __( 'Unable to cancel scheduled action: check the logs.', 'action-scheduler' ) );
			$this->print_error( $e, false );
		}

		$this->print_success( false );
	}

	/**
	 * Cancel all actions.
	 *
	 * @param string $hook The hook that the job will trigger.
	 * @param array  $callback_args Args that would have been passed to the job.
	 * @param string $group The group the job is assigned to.
	 * @return void
	 */
	protected function cancel_all( $hook, $callback_args, $group ) {
		if ( empty( $hook ) && empty( $group ) ) {
			\WP_CLI::error( __( 'Please specify hook and/or group of actions to cancel.', 'action-scheduler' ) );
		}

		try {
			$result = as_unschedule_all_actions( $hook, $callback_args, $group );
		} catch ( \Exception $e ) {
			$this->print_error( $e, true );
		}

		/**
		 * Because as_unschedule_all_actions() does not provide a result,
		 * neither confirm or deny actions cancelled.
		 */
		\WP_CLI::success( __( 'Request to cancel scheduled actions completed.', 'action-scheduler' ) );
	}

	/**
	 * Print a success message.
	 *
	 * @return void
	 */
	protected function print_success() {
		\WP_CLI::success( __( 'Scheduled action cancelled.', 'action-scheduler' ) );
	}

	/**
	 * Convert an exception into a WP CLI error.
	 *
	 * @param \Exception $e The error object.
	 * @param bool       $multiple Boolean if multiple actions.
	 * @throws \WP_CLI\ExitException When an error occurs.
	 * @return void
	 */
	protected function print_error( \Exception $e, $multiple ) {
		\WP_CLI::error(
			sprintf(
				/* translators: %1$s: singular or plural %2$s: refers to the exception error message. */
				__( 'There was an error cancelling the %1$s: %2$s', 'action-scheduler' ),
				$multiple ? __( 'scheduled actions', 'action-scheduler' ) : __( 'scheduled action', 'action-scheduler' ),
				$e->getMessage()
			)
		);
	}

}
