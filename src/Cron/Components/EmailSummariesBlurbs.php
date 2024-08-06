<?php
/**
 * Email Summary Blurbs Cron Class.
 *
 * @package     EDD
 * @subpackage  Cron\Components
 * @copyright   Copyright (c) 2024, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.3.3
 */

namespace EDD\Cron\Components;

use EDD\Cron\Events\SingleEvent;
use EDD\Cron\Traits;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * EmailSummariesBlurbs Class.
 *
 * Takes care of scheduling retrieving the email summary blurbs.
 *
 * @since 3.3.3
 */
class EmailSummariesBlurbs extends Component {
	use Traits\Clear;

	/**
	 * The component ID.
	 *
	 * @var string
	 */
	protected static $id = 'email_summary_blurbs';

	/**
	 * Name of the Email Summary cron hook.
	 *
	 * @since 3.3.3
	 *
	 * @const string
	 */
	const CRON_EVENT_NAME = 'edd_email_summary_blurbs';

	/**
	 * Class constructor.
	 *
	 * @since 3.3.3
	 */
	public static function get_subscribed_events(): array {
		return array(
			'edd_daily_scheduled_events' => 'schedule_cron_events',
			self::CRON_EVENT_NAME        => 'run_cron',
		);
	}

	/**
	 * Get the current status of email summary.
	 *
	 * @since 3.3.3
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
	 * @since 3.3.3
	 * @return void
	 */
	public function schedule_cron_events() {
		// Exit if email summary is disabled or event is already scheduled.
		if ( ! $this->is_enabled() || SingleEvent::next_scheduled( self::CRON_EVENT_NAME ) ) {
			return;
		}

		$timestamp = $this->get_timestamp();
		if ( ! $timestamp ) {
			return;
		}

		SingleEvent::add(
			$timestamp,
			self::CRON_EVENT_NAME
		);
	}

	/**
	 * Initialize the cron with all the proper checks.
	 *
	 * @since 3.3.3
	 * @return void
	 */
	public function run_cron() {
		// This is not cron, abort!
		if ( ! wp_doing_cron() ) {
			return;
		}

		$blurbs = new \EDD_Email_Summary_Blurb();
		$blurbs->fetch_blurbs();

		// Schedule the next event.
		$this->schedule_cron_events();
	}

	/**
	 * Gets a timestamp to schedule retrieving the blurbs.
	 *
	 * @since 3.3.3
	 * @return int The timestamp.
	 */
	private function get_timestamp() {
		$next_event = SingleEvent::next_scheduled( EmailSummaries::CRON_EVENT_NAME );
		if ( ! $next_event ) {
			return false;
		}

		// Schedule the next event between 1 and 96 hours from now.
		$timestamp = $next_event - mt_rand( HOUR_IN_SECONDS, 96 * HOUR_IN_SECONDS );

		// If the timestamp is not in the past, return it.
		if ( $timestamp >= time() ) {
			return $timestamp;
		}

		// Try to schedule it for five minutes out as long as that's still before $next_event.
		$timestamp = time() + ( 5 * MINUTE_IN_SECONDS );
		if ( $timestamp < $next_event ) {
			return $timestamp;
		}

		return time();
	}
}
