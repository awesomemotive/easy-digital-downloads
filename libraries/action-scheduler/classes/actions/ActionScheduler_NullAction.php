<?php

/**
 * Class ActionScheduler_NullAction
 */
class ActionScheduler_NullAction extends ActionScheduler_Action {

	/**
	 * Construct.
	 *
	 * @param string                        $hook Action hook.
	 * @param mixed[]                       $args Action arguments.
	 * @param null|ActionScheduler_Schedule $schedule Action schedule.
	 */
	public function __construct( $hook = '', array $args = array(), ?ActionScheduler_Schedule $schedule = null ) {
		$this->set_schedule( new ActionScheduler_NullSchedule() );
	}

	/**
	 * Execute action.
	 */
	public function execute() {
		// don't execute.
	}
}
