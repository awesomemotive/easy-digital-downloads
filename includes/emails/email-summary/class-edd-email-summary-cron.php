<?php
/**
 * Email Summary Cron Class.
 *
 * @package     EDD
 * @subpackage  Emails
 * @copyright   Copyright (c) 2022, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.1
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * EDD_Email_Summary_Cron Class.
 *
 * Takes care of scheduling and sending Email Summaries.
 *
 * @since 3.1
 */
class EDD_Email_Summary_Cron {

	/**
	 * Name of the Email Summary cron hook.
	 *
	 * @since 3.1
	 *
	 * @const string
	 */
	const CRON_EVENT_NAME = 'edd_email_summary_cron';

	/**
	 * Class constructor.
	 *
	 * @since 3.1
	 */
	public function __construct() {

		// Register daily check.
		add_action( 'edd_daily_scheduled_events', array( $this, 'schedule_cron_events' ) );

		// User settings changed.
		add_action( 'updated_option', array( $this, 'settings_changed' ), 10, 1 );

		// Prepare and run cron.
		add_action( self::CRON_EVENT_NAME, array( $this, 'run_cron' ) );

	}

	/**
	 * Get the current status of email summary.
	 *
	 * @since 3.1
	 *
	 * @return bool True if email summary is enabled, false if disabled.
	 */
	public function is_enabled() {
		return (bool) ! edd_get_option( 'disable_email_summary', false );
	}

	/**
	 * Determine when the next cron event
	 * should be and schedule it.
	 *
	 * @since 3.1
	 *
	 * @return void
	 */
	public function schedule_cron_events() {
		// Exit if email summary is disabled or event is already scheduled.
		if ( ! $this->is_enabled() || wp_next_scheduled( self::CRON_EVENT_NAME ) ) {
			return;
		}

		// Get the event date based on user settings.
		$email_frequency = edd_get_option( 'email_summary_frequency', 'weekly' );
		$week_start_day  = jddayofweek( (int) get_option( 'start_of_week' ) - 1, 1);

		if ( 'monthly' === $email_frequency ) {
			$next_time_string = 'first day of next month 8am';
		} else {
			$next_time_string = "next {$week_start_day} 8am";
		}

		$date = new \DateTime( $next_time_string, wp_timezone() );
		wp_schedule_single_event( $date->getTimestamp(), self::CRON_EVENT_NAME );
	}

	/**
	 * Clear all cron events related to email summary.
	 *
	 * @since 3.1
	 *
	 * @return void
	 */
	public function clear_cron_events() {
		wp_clear_scheduled_hook( self::CRON_EVENT_NAME );
	}

	/**
	 * Detect when settings that affect the
	 * schedule of email summaries are updated.
	 *
	 * @since 3.1
	 *
	 * @param string $option_name
	 *
	 * @return void
	 */
	public function settings_changed( $option_name ) {
		if ( ! in_array( $option_name, array( 'edd_settings', 'start_of_week' ) ) ) {
			return;
		}
		// Reload EDD options so that we have the newest values.
		global $edd_options;
		$edd_options = get_option( 'edd_settings' );
		$this->clear_cron_events();
		$this->schedule_cron_events();
	}

	/**
	 * Initialize the cron with all the proper checks.
	 *
	 * @since 3.1
	 *
	 * @return void
	 */
	public function run_cron() {
		// This is not cron, abort!
		if ( ! wp_doing_cron() ) {
			return;
		}

		$email = new EDD_Email_Summary;
		$email->send_email();
	}

}
