<?php
/**
 * Discount Claims Cleanup
 *
 * @package     EDD\Cron\Events
 * @copyright   Copyright (c) 2026, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.7.2
 */

namespace EDD\Cron\Events;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

/**
 * Schedules the hourly release of discount uses held by stale pending orders.
 *
 * @since 3.7.2
 */
class DiscountClaimsCleanup extends Event {

	/**
	 * Hook name.
	 *
	 * @since 3.7.2
	 * @var string
	 */
	protected $hook = 'edd/discounts/release_expired_claims';

	/**
	 * First Run Time.
	 *
	 * @since 3.7.2
	 * @var int
	 */
	protected $first_run = 0;

	/**
	 * Schedule.
	 *
	 * @since 3.7.2
	 * @var string
	 */
	protected $schedule = 'hourly';
}
