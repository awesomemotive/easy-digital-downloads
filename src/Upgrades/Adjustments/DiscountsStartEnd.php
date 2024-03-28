<?php
/**
 * Fixes the start and end dates for discounts that were migrated to EDD 3.0 with a 0000-00-00 00:00:00 date.
 *
 * @since 3.2.10
 *
 * @package EDD
 * @subpackage Upgrades
 * @category Adjustments
 */
namespace EDD\Upgrades\Adjustments;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

use EDD\EventManagement\SubscriberInterface;
use EDD\Upgrades\Utilities\MigrationCheck;
use EDD\Utils\Date;

/**
 * Class DiscountsStartEnd
 *
 * @since 3.2.10
 * @package EDD\Upgrades\Adjustments
 */
class DiscountsStartEnd implements SubscriberInterface {

	/**
	 * The name of the upgrade.
	 *
	 * @var string
	 */
	protected static $upgrade_name = 'discounts_start_end';

	/**
	 * The name of the cron action.
	 *
	 * @var string
	 */
	protected static $cron_action = 'edd_fix_discount_start_end_date';

	/**
	 * The name of the option that stores the total count of rows to process.
	 *
	 * @var string
	 */
	protected $total_count_option = 'edd_fix_discount_start_end_date_total';

	/**
	 * The remote ID of the notification that the migration is in progress.
	 *
	 * @var string
	 */
	protected $in_progress_remote_id = 'discount-dates-run';

	/**
	 * The remote ID of the notification that the migration is complete.
	 *
	 * @var string
	 */
	protected $complete_remote_id = 'discount-date-done';

	/**
	 * The zeroed (invalid) date value.
	 *
	 * @var string
	 */
	private $zeroed_value = '0000-00-00 00:00:00';

	/**
	 * Hook into actions and filters.
	 *
	 * @since  3.2.10
	 */
	public static function get_subscribed_events() {
		// If the upgrade has already been run, don't hook anything.
		if ( edd_has_upgrade_completed( self::$upgrade_name ) ) {
			return array();
		}

		// If the initial EDD 3.x migration hasn't completed, don't hook anything.
		if ( ! MigrationCheck::is_v30_migration_complete() ) {
			return array();
		}

		// Always hook the migration hook.
		$hooks = array(
			self::$cron_action => 'process_step',
		);

		if ( ! wp_next_scheduled( self::$cron_action ) ) {
			$hooks['shutdown'] = array( 'maybe_schedule_background_update', 99 );
		}

		return $hooks;
	}

	/**
	 * Maybe schedule the background update.
	 *
	 * @since 3.2.10
	 * @return void
	 */
	public function maybe_schedule_background_update() {
		// If we've already scheduled the cleanup, no need to schedule it again.
		if ( wp_next_scheduled( self::$cron_action ) ) {
			return;
		}

		// See if we have any adjustments with 0000-00-00 00:00:00 dates for the start and end.
		$affected_discount_count = $this->get_discounts( true );

		if ( empty( $affected_discount_count ) ) {
			$this->mark_complete( false );
			return;
		}

		// Only update the total count option if it doesn't exist. This prevents it from being overridden in some edge cases.
		if ( empty( get_option( $this->total_count_option ) ) ) {
			// Set the total amount in a transient so we can use it later.
			update_option( $this->total_count_option, $affected_discount_count, false );
		}

		$this->add_or_update_initial_notification();

		// ...And schedule a single event a minute from now to start the processing of this data.
		wp_schedule_single_event( time() + MINUTE_IN_SECONDS, self::$cron_action );
	}

	/**
	 * Process the upgrade step.
	 *
	 * @since 3.2.10
	 * @return void
	 */
	public function process_step() {
		// Only let this run in the background.
		if ( ! edd_doing_cron() ) {
			return;
		}

		$discounts = $this->get_discounts();
		if ( empty( $discounts ) ) {
			$this->mark_complete( true );
			return;
		}

		foreach ( $discounts as $discount ) {
			$data = array();
			if ( $this->zeroed_value === $discount->start_date ) {
				$data['start_date'] = null;
			}
			if ( $this->zeroed_value === $discount->end_date ) {
				$data['end_date'] = null;
			}
			if ( ! empty( $data ) ) {
				edd_update_adjustment( $discount->id, $data );
			}
		}

		$this->add_or_update_initial_notification();

		wp_schedule_single_event( time() + MINUTE_IN_SECONDS, self::$cron_action );
	}

	/**
	 * Marks the fix process as complete.
	 *
	 * @since 3.2.10
	 *
	 * @param bool $add_notification If we should add a notification that the fix process is complete.
	 *
	 * @return void
	 */
	private function mark_complete( $add_notification = true ) {
		// Set the upgrade as complete.
		edd_set_upgrade_complete( self::$upgrade_name );

		// Delete the total count option. It may not exist, but we should delete it anyway.
		delete_option( $this->total_count_option );

		if ( false === $add_notification ) {
			return;
		}

		$initial_notification = $this->get_initial_notification();
		if ( ! empty( $initial_notification ) ) {
			EDD()->notifications->update( $initial_notification->id, array( 'dismissed' => 1 ) );
		}

		EDD()->notifications->maybe_add_local_notification(
			array(
				'remote_id'  => $this->complete_remote_id,
				'buttons'    => '',
				'conditions' => '',
				'type'       => 'success',
				'title'      => __( 'Discount Updates Complete!', 'easy-digital-downloads' ),
				'content'    => __( 'Easy Digital Downloads has finished updating your discount codes! Thank you for your patience.', 'easy-digital-downloads' ),
			)
		);
	}

	/**
	 * Gets the initial notification about the migration.
	 *
	 * @since 3.2.10
	 *
	 * @return object
	 */
	private function get_initial_notification() {
		return EDD()->notifications->get_item_by( 'remote_id', $this->in_progress_remote_id );
	}

	/**
	 * Adds or updates the initial notification about the migration.
	 *
	 * @since 3.2.10
	 *
	 * @return void
	 */
	private function add_or_update_initial_notification() {
		$initial_notification = $this->get_initial_notification();
		$percent_complete     = $this->get_percentage_complete();

		// translators: %s is the % complete.
		$notification_title = sprintf( __( 'Updating Discounts ( %d%% )', 'easy-digital-downloads' ), $percent_complete );

		if ( ! empty( $initial_notification ) ) {
			$date = new Date();
			$date->setTimestamp( time() )->setTimezone( new \DateTimeZone( 'UTC' ) );

			// Update the notification.
			EDD()->notifications->update(
				$initial_notification->id,
				array(
					'title'        => $notification_title,
					'date_created' => $date->format( 'mysql' ),
				)
			);
		} else {
			// Add the notification.
			EDD()->notifications->maybe_add_local_notification(
				array(
					'remote_id'  => $this->in_progress_remote_id,
					'buttons'    => '',
					'conditions' => '',
					'type'       => 'info',
					'title'      => $notification_title,
					'content'    => __( 'Easy Digital Downloads is performing maintenance in the background on discount codes that may contain invalid start and end dates. This process may take a while to complete depending on the number of discounts you have. We\'ll let you know when the process is complete.', 'easy-digital-downloads' ),
				)
			);
		}
	}

	/**
	 * Gets the percentage complete.
	 *
	 * @since 3.2.10
	 *
	 * @return int
	 */
	private function get_percentage_complete() {
		static $percent_complete;

		if ( ! is_null( $percent_complete ) ) {
			return $percent_complete;
		}

		// Get the total amount of rows remaining.
		$total_rows_remaining = $this->get_discounts( true );

		// Get the total we started with.
		$total_rows_start = get_option( $this->total_count_option );

		// Format a % complete without decimals.
		$percent_complete = number_format( ( ( $total_rows_start - $total_rows_remaining ) / $total_rows_start ) * 100, 0 );

		// Just in case we end up over 100%, somehow...make it 100%.
		if ( $percent_complete > 100 ) {
			$percent_complete = 100;
		}

		return $percent_complete;
	}

	/**
	 * Get discounts with empty start or end dates.
	 *
	 * @since 3.2.10
	 *
	 * @param bool $count Whether to return the count of discounts.
	 *
	 * @return array|bool
	 */
	private function get_discounts( $count = false ) {
		$get_adjustments_function = $count ? 'edd_count_adjustments' : 'edd_get_adjustments';

		$discounts = $get_adjustments_function(
			array(
				'type'       => 'discount',
				'date_query' => array(
					'relation' => 'OR',
					array(
						'column'  => 'start_date',
						'value'   => $this->zeroed_value,
						'compare' => '=',
					),
					array(
						'column'  => 'end_date',
						'value'   => $this->zeroed_value,
						'compare' => '=',
					),
				),
			)
		);

		if ( ! empty( $discounts ) ) {
			return $discounts;
		}

		return false;
	}
}
