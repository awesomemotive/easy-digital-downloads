<?php

/**
 * Class ActionScheduler_NullLogEntry
 */
class ActionScheduler_NullLogEntry extends ActionScheduler_LogEntry {

	/**
	 * Construct.
	 *
	 * @param string $action_id Action ID.
	 * @param string $message   Log entry.
	 */
	public function __construct( $action_id = '', $message = '' ) {
		// nothing to see here.
	}

}
