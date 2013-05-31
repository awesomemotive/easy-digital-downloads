<?php
/**
 * Cron
 *
 * @package     EDD
 * @subpackage  Classes/Cron
 * @copyright   Copyright (c) 2012, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.6
*/

/**
 * EDD_Cron Class
 *
 * This class handles scheduled events
 *
 * @since 1.6
 */
class EDD_Cron {


	/**
	 * Get things going
	 *
	 * @access public
	 * @since 1.6
	 * @see EDD_Cron::weekly_events()
	 * @return void
	 */
	public function __construct() {

		add_filter( 'cron_schedules', array( $this, 'add_schedules' ) );

		add_action( 'wp', array( $this, 'schedule_Events' ) );

	}


	/**
	 * Registers new cron schedules
	 *
	 * @access public
	 * @since 1.6
	 * @return void
	 */
	public function add_schedules( $schedules = array() ) {

		// Adds once weekly to the existing schedules.
		$schedules['weekly'] = array(
			'interval' => 604800,
			'display'  => __( 'Once Weekly' )
		);

		return $schedules;

	}


	/**
	 * Schedules our events
	 *
	 * @access public
	 * @since 1.6
	 * @return void
	 */
	public function schedule_Events() {

		$this->weekly_events();
		$this->daily_events();
	}


	/**
	 * Schedule weekly events
	 *
	 * @access private
	 * @since 1.6
	 * @return void
	 */
	private function weekly_events() {
		if ( ! wp_next_scheduled( 'edd_weekly_cron' ) ) {
			wp_schedule_event( time(), 'weekly', 'edd_weekly_cron');
			do_action( 'edd_weekly_scheduled_events' );
		}
	}


	/**
	 * Schedule daily events
	 *
	 * @access private
	 * @since 1.6
	 * @return void
	 */
	private function daily_events() {
		if ( ! wp_next_scheduled( 'edd_weekly_cron' ) ) {
			wp_schedule_event( time(), 'daily', 'edd_daily_cron');
			do_action( 'edd_daily_scheduled_events' );
		}
	}

}
$edd_cron = new EDD_Cron;