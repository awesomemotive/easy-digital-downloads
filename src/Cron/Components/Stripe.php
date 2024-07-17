<?php
/**
 * Handles the Stripe cron events.
 *
 * @package EDD
 * @subpackage Cron/Components
 * @since 3.3.0
 */

namespace EDD\Cron\Components;

use EDD\Gateways\Stripe\Admin\LicenseManager;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Stripe Class
 *
 * @since 3.3.0
 */
class Stripe extends Component {

	/**
	 * The unique identifier for this component.
	 *
	 * @var string
	 */
	protected static $id = 'stripe';

	/**
	 * Register the subscribed events.
	 *
	 * @since 3.3.0
	 */
	public static function get_subscribed_events(): array {
		return array(
			'edds_cleanup_rate_limiting_log' => 'cleanup_rate_limiting_log',
			'edd_daily_scheduled_events'     => 'check_license',
		);
	}

	/**
	 * Clean up the rate limiting log.
	 *
	 * @since 3.3.0
	 */
	public function cleanup_rate_limiting_log() {
		edd_stripe()->rate_limiting->cleanup_log();
	}

	/**
	 * Check the Stripe license.
	 *
	 * @since 3.3.0
	 *
	 * @return void
	 */
	public function check_license() {
		$license_manager = new LicenseManager();
		$license_manager->check_license();
	}
}
