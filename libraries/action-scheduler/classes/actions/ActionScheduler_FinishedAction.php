<?php

/**
 * Class ActionScheduler_FinishedAction
 */
class ActionScheduler_FinishedAction extends ActionScheduler_Action {

	/**
	 * Execute action.
	 */
	public function execute() {
		// don't execute.
	}

	/**
	 * Get finished state.
	 */
	public function is_finished() {
		return true;
	}
}
