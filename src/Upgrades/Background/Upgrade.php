<?php
/**
 * Base class for background upgrades.
 *
 * @package     EDD\Upgrades\Background
 * @copyright   Copyright (c) 2025, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.5.0
 */

namespace EDD\Upgrades\Background;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

use EDD\EventManagement\SubscriberInterface;
use EDD\Upgrades\Utilities\MigrationCheck;
use EDD\Utils\Date;
use EDD\Cron\Events\SingleEvent;

/**
 * Base class for upgrades.
 *
 * @since 3.5.0
 */
abstract class Upgrade implements SubscriberInterface, UpgradeInterface {

	/**
	 * The number of database rows which should prevent the upgrade running via the background process.
	 *
	 * @var int
	 */
	protected $warning_count = 25000;

	/**
	 * Hook into actions and filters.
	 *
	 * @since 3.5.0
	 */
	public static function get_subscribed_events() {
		// If the upgrade has already been run, don't hook anything.
		if ( empty( static::get_upgrade_name() ) || edd_has_upgrade_completed( static::get_upgrade_name() ) ) {
			return array();
		}

		// Always hook the migration hook.
		$hooks = array(
			static::get_cron_action() => 'process_step',
		);

		if ( ! SingleEvent::next_scheduled( static::get_cron_action() ) ) {
			$hooks['shutdown'] = array( 'maybe_schedule_background_update', 99 );
			$hooks['init']     = array( 'maybe_register_cli_command', 99 );
		}

		return $hooks;
	}

	/**
	 * Maybe schedule the background update.
	 *
	 * @since 3.5.0
	 * @return void
	 */
	public function maybe_schedule_background_update() {
		// If we've already scheduled the cleanup, no need to schedule it again.
		if ( SingleEvent::next_scheduled( static::get_cron_action() ) ) {
			return;
		}

		// If the initial EDD 3.x migration hasn't completed, don't hook anything.
		if ( ! MigrationCheck::is_v30_migration_complete() ) {
			return;
		}

		$items = $this->get_items( true );
		if ( empty( $items ) ) {
			$this->mark_complete();
			return;
		}

		// Only update the total count option if it doesn't exist. This prevents it from being overridden in some edge cases.
		$total = get_option( $this->get_total_count_option() );
		if ( empty( $total ) ) {
			// Set the total amount in a transient so we can use it later.
			update_option( $this->get_total_count_option(), $items, false );
			$total = $items;
		}

		$this->add_or_update_initial_notification();

		// If we have less than the allowed number of items, schedule the next step.
		if ( $total < $this->warning_count ) {
			SingleEvent::add(
				time() + MINUTE_IN_SECONDS,
				static::get_cron_action()
			);
		}
	}

	/**
	 * Maybe register the CLI command.
	 *
	 * @since 3.5.0
	 * @return void
	 */
	public function maybe_register_cli_command() {
		if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
			return;
		}
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		\WP_CLI::add_command( 'edd upgrade', array( $this, 'do_cli' ) );
	}

	/**
	 * Runs the upgrade via WP-CLI.
	 *
	 * @since 3.5.0
	 *
	 * @param array $args       The arguments passed to the command.
	 * @param array $assoc_args The associative arguments passed to the command.
	 *
	 * @return void
	 */
	public function do_cli( $args, $assoc_args ) {
		if ( ! isset( $args[0] ) || static::get_upgrade_name() !== $args[0] ) {
			\WP_CLI::error( __( 'Invalid upgrade name.', 'easy-digital-downloads' ) );
			return;
		}
		// If the upgrade has already been run, show a message and return.
		if ( edd_has_upgrade_completed( static::get_upgrade_name() ) ) {
			/* translators: %s is the upgrade name */
			\WP_CLI::error( sprintf( __( 'The %s upgrade has already been run.', 'easy-digital-downloads' ), static::get_upgrade_name() ) );
		}

		$total    = $this->get_items( true );
		$progress = new \cli\progress\Bar( 'Processing...', $total );

		while ( $this->get_items( true ) > 0 ) {
			$this->process_step();
			$progress->tick();
		}

		$progress->finish();
		/* translators: %s is the upgrade name */
		\WP_CLI::success( sprintf( __( 'The %s upgrade has been completed.', 'easy-digital-downloads' ), static::get_upgrade_name() ) );
	}

	/**
	 * Gets the name of the cron action.
	 *
	 * @since 3.5.0
	 * @return string
	 */
	protected static function get_cron_action(): string {
		return 'edd_' . static::get_upgrade_name();
	}

	/**
	 * Gets the percentage complete.
	 *
	 * @since 3.5.0
	 *
	 * @return int
	 */
	protected function get_percentage_complete() {
		static $percent_complete;

		if ( ! is_null( $percent_complete ) ) {
			return $percent_complete;
		}

		// Get the total amount of rows remaining.
		$total_rows_remaining = $this->get_items( true );

		// Get the total we started with.
		$total_rows_start = get_option( $this->get_total_count_option() );

		// Format a % complete without decimals.
		$percent_complete = number_format( ( ( $total_rows_start - $total_rows_remaining ) / $total_rows_start ) * 100, 0 );

		// Just in case we end up over 100%, somehow...make it 100%.
		if ( $percent_complete > 100 ) {
			$percent_complete = 100;
		}

		return $percent_complete;
	}

	/**
	 * Marks the upgrade process as complete.
	 *
	 * @since 3.5.0
	 * @return void
	 */
	protected function mark_complete() {
		// Set the upgrade as complete.
		edd_set_upgrade_complete( static::get_upgrade_name() );

		// Delete the total count option. It may not exist, but we should delete it anyway.
		$had_total = get_option( $this->get_total_count_option() );
		delete_option( $this->get_total_count_option() );

		// If there was no total count, we will not show a notification.
		if ( empty( $had_total ) ) {
			return;
		}

		$initial_notification = $this->get_initial_notification();
		if ( ! empty( $initial_notification ) ) {
			EDD()->notifications->update( $initial_notification->id, array( 'dismissed' => 1 ) );
		}

		EDD()->notifications->maybe_add_local_notification(
			wp_parse_args(
				$this->get_complete_notification(),
				array(
					'remote_id'  => $this->get_notification_id(),
					'buttons'    => '',
					'conditions' => '',
					'type'       => 'success',
					'title'      => '',
					'content'    => '',
				)
			)
		);
	}

	/**
	 * Adds or updates the initial notification about the migration.
	 *
	 * @since 3.5.0
	 *
	 * @return void
	 */
	protected function add_or_update_initial_notification() {
		$initial_notification = $this->get_initial_notification();
		$percent_complete     = $this->get_percentage_complete();
		$total_count          = get_option( $this->get_total_count_option(), 0 );
		if ( $total_count < $this->warning_count ) {
			$notification_params = $this->get_in_progress_notification();
		} else {
			$notification_params = $this->get_cli_notification();
		}
		$notification = wp_parse_args(
			$notification_params,
			array(
				'remote_id'  => $this->get_notification_id( true ),
				'buttons'    => '',
				'conditions' => '',
				'type'       => 'info',
				'title'      => '',
				'content'    => '',
			)
		);

		// translators: %s is the % complete.
		$notification['title'] = sprintf( $notification['title'], $percent_complete );

		if ( ! empty( $initial_notification ) ) {
			$date = new Date();
			$date->setTimestamp( time() )->setTimezone( new \DateTimeZone( 'UTC' ) );

			// Update the notification.
			EDD()->notifications->update(
				$initial_notification->id,
				array(
					'title'        => $notification['title'],
					'date_created' => $date->format( 'mysql' ),
				)
			);
		} else {
			// Add the notification.
			EDD()->notifications->maybe_add_local_notification( $notification );
		}
	}

	/**
	 * Determines if the step can be processed.
	 *
	 * @since 3.5.0
	 *
	 * @return bool
	 */
	protected function can_process_step() {
		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			return true;
		}

		return edd_doing_cron();
	}

	/**
	 * Gets the initial notification about the migration.
	 *
	 * @since 3.5.0
	 *
	 * @return object
	 */
	private function get_initial_notification() {
		return EDD()->notifications->get_item_by( 'remote_id', $this->get_notification_id( true ) );
	}

	/**
	 * Gets the notification ID for the migration.
	 *
	 * @since 3.5.0
	 *
	 * @param bool $in_progress Whether to get the in progress or complete notification ID.
	 * @return string
	 */
	private function get_notification_id( $in_progress = false ): string {
		$upgrade_name = substr( md5( static::get_upgrade_name() ), 0, 10 );

		return $in_progress ? $upgrade_name . '-working' : $upgrade_name . '-complete';
	}

	/**
	 * Gets the total count option name.
	 *
	 * @since 3.5.0
	 * @return string
	 */
	private function get_total_count_option(): string {
		return 'edd_' . static::get_upgrade_name() . '_total';
	}
}
