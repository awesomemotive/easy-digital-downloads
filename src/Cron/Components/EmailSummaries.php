<?php
/**
 * Email Summary Cron Class.
 *
 * @package     EDD
 * @subpackage  Cron\Components
 * @copyright   Copyright (c) 2022, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.1
 * @since       3.3.0 This class was converted to the new namespaced Cron Loader.
 */

namespace EDD\Cron\Components;

use EDD\Cron\Events\SingleEvent;
use EDD\Cron\Traits;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * EmailSummaries Class.
 *
 * Takes care of scheduling and sending Email Summaries.
 *
 * @since 3.1
 */
class EmailSummaries extends Component {
	use Traits\Clear;

	/**
	 * The component ID.
	 *
	 * @var string
	 */
	protected static $id = 'email_summaries';

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
	public static function get_subscribed_events(): array {
		return array(
			'edd_daily_scheduled_events' => 'schedule_cron_events',
			'updated_option'             => array( 'settings_changed', 10, 3 ),
			self::CRON_EVENT_NAME        => 'run_cron',
		);
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
		if ( ! $this->is_enabled() || SingleEvent::next_scheduled( self::CRON_EVENT_NAME ) ) {
			return;
		}

		// Get the event date based on user settings.
		$days            = EDD()->utils->date()->getDays();
		$email_frequency = edd_get_option( 'email_summary_frequency', 'weekly' );
		$week_start_day  = $days[ (int) get_option( 'start_of_week' ) ];

		if ( 'monthly' === $email_frequency ) {
			$next_time_string = 'first day of next month 8am';
		} else {
			$next_time_string = "next {$week_start_day} 8am";
		}

		$date = new \DateTime( $next_time_string, new \DateTimeZone( edd_get_timezone_id() ) );
		SingleEvent::add(
			$date->getTimestamp(),
			self::CRON_EVENT_NAME
		);
	}

	/**
	 * Clear all cron events related to email summary.
	 *
	 * @since 3.1
	 * @deprecated 3.3.0
	 *
	 * @return void
	 */
	public function clear_cron_events() {
		_edd_deprecated_function( __METHOD__, '3.3.0', 'EDD\Cron\Components\EmailSummaries::clear()' );

		self::clear( self::CRON_EVENT_NAME );
	}

	/**
	 * Detect when settings that affect the
	 * schedule of email summaries are updated.
	 *
	 * @since 3.1
	 *
	 * @param string $option_name WordPress option that was changed.
	 * @param string $old_value Old option value.
	 * @param string $new_value New option value.
	 *
	 * @return void
	 */
	public function settings_changed( $option_name, $old_value, $new_value ) {
		if ( ! in_array( $option_name, array( 'edd_settings', 'start_of_week', 'timezone_string', 'gmt_offset' ), true ) ) {
			return;
		}

		// If `edd_settings` were changed, listen
		// only to changes in specific fields.
		if ( 'edd_settings' === $option_name ) {
			$change_detected = false;
			$field_listeners = array( 'email_summary_frequency', 'disable_email_summary' );
			foreach ( $field_listeners as $field ) {
				if ( ( empty( $old_value[ $field ] ) || empty( $new_value[ $field ] ) ) || ( $old_value[ $field ] !== $new_value[ $field ] ) ) {
					$change_detected = true;
					break;
				}
			}

			if ( ! $change_detected ) {
				return;
			}

			// Reload EDD options so that we have the newest values in class methods.
			global $edd_options;
			$edd_options = get_option( 'edd_settings' );
		}

		$this->clear( self::CRON_EVENT_NAME );
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

		$email = new \EDD_Email_Summary();
		$email->send_email();

		EmailSummariesBlurbs::clear( EmailSummariesBlurbs::CRON_EVENT_NAME );
		delete_option( 'edd_email_summary_blurbs' );

		// Schedule the next event.
		$this->schedule_cron_events();
	}
}
