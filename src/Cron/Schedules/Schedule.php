<?php
/**
 * Trait for cron schedules.
 *
 * @package EDD
 * @subpackage Cron/Schedules
 *
 * @since 3.3.0
 */

namespace EDD\Cron\Schedules;
use EDD\Utils\Exception;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Schedule
 *
 * @since 3.3.0
 */
abstract class Schedule {
	/**
	 * The schedule ID.
	 *
	 * @var string
	 */
	public $id;

	/**
	 * The interval in seconds.
	 *
	 * @var int
	 */
	public $interval;

	/**
	 * The display name for the schedule.
	 *
	 * @var string
	 */
	public $display_name;

	/**
	 * Whether the schedule is valid.
	 *
	 * @var bool
	 */
	public $valid;

	/**
	 * The Schedule constructor.
	 *
	 * This sets the display name (from the abstract method), and validates the schedule.
	 *
	 * @since 3.3.0
	 */
	public function __construct() {
		$this->display_name = $this->get_display_name();
		$this->valid        = $this->validate();
	}

	/**
	 * Validates the schedule.
	 *
	 * @since 3.3.0
	 *
	 * @throws Exception If the schedule is not valid.
	 *
	 * @return bool
	 */
	private function validate() {
		try {
			if ( empty( $this->id ) || empty( $this->interval ) || empty( $this->display_name ) ) {
				throw new Exception( __( 'An ID, interval, and display name must be provided.', 'easy-digital-downloads' ) );
			}

			// The minimum interval is 5 minutes, for now.
			if ( $this->interval < 300 ) {
				throw new Exception( __( 'The interval must be at least 5 minutes.', 'easy-digital-downloads' ) );
			}
		} catch ( \Exception $e ) {
			return false;
		}

		return true;
	}

	/**
	 * Get the display name for the schedule.
	 *
	 * This must be an abstract class to be implemented by the extending class. Since display names need to be translated,
	 * they cannot be set as a class property, and therefore must be implemented via a method that returns the translatable string.
	 *
	 * @since 3.3.0
	 *
	 * @return string
	 */
	abstract protected function get_display_name(): string;
}
