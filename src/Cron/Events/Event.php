<?php
/**
 * Abstract class for creating Cron Events.
 *
 * @package EDD
 * @subpackage Cron/Events
 *
 * @since 3.3.0
 */

namespace EDD\Cron\Events;

use EDD\Utils\Exceptions;
use EDD\Cron\Traits\NextScheduled;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Event Class
 *
 * @since 3.3.0
 */
abstract class Event {
	use NextScheduled;

	/**
	 * Hook name.
	 *
	 * The hook that will fire when the Cron event is run.
	 *
	 * @var string
	 */
	protected $hook;

	/**
	 * First Run Time.
	 *
	 * The UTC timestamp to run the event for the first time.
	 *
	 * @var int
	 */
	protected $first_run = 0;

	/**
	 * Schedule.
	 *
	 * The registered WP Cron schedule to use.
	 *
	 * @var string
	 */
	protected $schedule;

	/**
	 * Arguments.
	 *
	 * The arguments to pass to the hook
	 * when the event is run.
	 *
	 * @var array
	 */
	protected $args = array();

	/**
	 * Whether the event is valid.
	 *
	 * @var bool
	 */
	public $valid;

	/**
	 * Event constructor.
	 *
	 * @since 3.3.0
	 */
	public function __construct() {
		$this->valid = $this->validate();
	}

	/**
	 * Validate the event.
	 *
	 * Validates the event before registering it, to ensure all the required properties are set, and
	 * to allow for custom first_run and arguments to be defined.
	 *
	 * @since 3.3.0
	 *
	 * @throws Exceptions\Attribute_Not_Found If the hook and schedule are not set.
	 * @throws Exceptions\Invalid_Argument If the event is already scheduled.
	 *
	 * @return bool
	 */
	private function validate() {
		try {
			if ( empty( $this->hook ) || empty( $this->schedule ) ) {
				throw new Exceptions\Attribute_Not_Found( __( 'A hook and schedule are required to schedule an event.', 'easy-digital-downloads' ) );
			}

			if ( empty( $this->first_run ) ) {
				$this->first_run = $this->calculate_first_run();
			}

			if ( empty( $this->args ) ) {
				$this->args = $this->build_args();
			}

			if ( $this->next_scheduled( $this->hook, $this->args ) ) {
				/* translators: %s: hook name that would be run for this WP Cron event. */
				throw new Exceptions\Invalid_Argument( sprintf( __( 'The event %s is already scheduled.', 'easy-digital-downloads' ), $this->hook ) );
			}
		} catch ( \Exception $e ) {
			return false;
		}

		return true;
	}

	/**
	 * Calculate the first run time.
	 *
	 * By default the Event class takes a unix timestamp for the first run time.
	 * If you need to do date calculations to get a specific time for the first run, set the class value as 0 and override this method.
	 *
	 * @since 3.3.0
	 *
	 * @return int
	 */
	private function calculate_first_run() {
		$now = time();

		// If the first run is in the past, set it to now.
		if ( $this->first_run < $now ) {
			$this->first_run = $now;
		}

		return $this->first_run;
	}

	/**
	 * Build the arguments to pass to the hook when the event is run.
	 *
	 * By default, the Event class does not pass any arguments to the hook.
	 * If you need to pass arguments into your cron hook, override this method.
	 *
	 * @since 3.3.0
	 *
	 * @return array
	 */
	private function build_args() {
		return array();
	}

	/**
	 * Schedule an event.
	 *
	 * @since 3.3.0
	 *
	 * @return void
	 */
	public function schedule() {
		if ( ! $this->valid ) {
			return;
		}

		wp_schedule_event( $this->first_run, $this->schedule, $this->hook, $this->args );
	}
}
