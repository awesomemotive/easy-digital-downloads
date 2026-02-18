<?php
/**
 * Cron Migration Helper
 *
 * Helps migrate from WP-Cron to Action Scheduler and vice versa.
 *
 * @package EDD\Cron
 * @copyright   Copyright (c) 2026, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since   3.6.5
 */

namespace EDD\Cron;

use EDD\Cron\Schedulers\Handler;
use EDD\Cron\Schedulers\ActionScheduler;
use EDD\Cron\Schedulers\WPCronScheduler;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

/**
 * Cron Migrator Class
 *
 * Migrates scheduled cron events between WP-Cron and Action Scheduler.
 * Uses registered events from the Loader to ensure only EDD-managed
 * cron events are migrated.
 *
 * @since 3.6.5
 */
class Migrator {

	/**
	 * Migrate from WP-Cron to Action Scheduler.
	 *
	 * Clears all registered EDD events from WP-Cron and re-schedules
	 * them using Action Scheduler.
	 *
	 * @since 3.6.5
	 *
	 * @return bool True if migration was performed, false otherwise.
	 */
	public static function migrate_to_action_scheduler(): bool {
		if ( ! ActionScheduler::is_available() ) {
			edd_debug_log( '[EDD Cron Migration] Action Scheduler is not available. Migration aborted.' );
			return false;
		}

		$registered_events = Loader::get_registered_events();

		if ( empty( $registered_events ) ) {
			edd_debug_log( '[EDD Cron Migration] No registered events found. Migration skipped.' );
			return true;
		}

		$wp_cron  = new WPCronScheduler();
		$migrated = array();
		$failed   = array();

		// Clear events from WP-Cron.
		foreach ( $registered_events as $event ) {
			if ( ! $event instanceof Events\Event ) {
				continue;
			}

			$hook = $event->hook ?? null;
			$args = $event->args ?? array();

			if ( empty( $hook ) ) {
				continue;
			}

			// Unschedule from WP-Cron.
			if ( $wp_cron->unschedule_all( $hook, $args ) ) {
				$migrated[] = $hook;
			}
		}

		if ( empty( $migrated ) ) {
			edd_debug_log( '[EDD Cron Migration] No events were cleared from WP-Cron. Migration skipped.' );
			return false;
		}

		// Reset Handler to use Action Scheduler.
		Handler::reset();

		// Re-schedule events using Action Scheduler.
		foreach ( $registered_events as $event ) {
			if ( ! $event instanceof Events\Event ) {
				continue;
			}

			$hook = $event->hook ?? null;

			if ( empty( $hook ) || ! in_array( $hook, $migrated, true ) ) {
				continue;
			}

			try {
				$event->reset();
				$event->schedule();
			} catch ( \Exception $e ) {
				$failed[] = $hook;
				edd_debug_log(
					sprintf(
						'[EDD Cron Migration] Failed to schedule event "%s" with Action Scheduler: %s',
						$hook,
						$e->getMessage()
					)
				);
			}
		}

		// Log results.
		$success_count = count( $migrated ) - count( $failed );
		edd_debug_log(
			sprintf(
				'[EDD Cron Migration] Successfully migrated %d/%d events from WP-Cron to Action Scheduler.',
				$success_count,
				count( $migrated )
			)
		);

		if ( ! empty( $failed ) ) {
			edd_debug_log(
				sprintf(
					'[EDD Cron Migration] Failed to migrate %d events: %s',
					count( $failed ),
					implode( ', ', $failed )
				)
			);
		}

		/**
		 * Fires after EDD cron events have been migrated to Action Scheduler.
		 *
		 * @since 3.6.5
		 *
		 * @param array $migrated Array of hook names that were successfully migrated.
		 * @param array $failed Array of hook names that failed to migrate.
		 */
		do_action( 'edd_cron_migrated_to_action_scheduler', $migrated, $failed );

		return $success_count > 0;
	}

	/**
	 * Migrate from Action Scheduler to WP-Cron.
	 *
	 * Clears all registered EDD events from Action Scheduler and re-schedules
	 * them using WP-Cron.
	 *
	 * @since 3.6.5
	 *
	 * @return bool True if migration was performed, false otherwise.
	 */
	public static function migrate_to_wp_cron(): bool {
		if ( ! ActionScheduler::is_available() ) {
			edd_debug_log( '[EDD Cron Migration] Action Scheduler is not available. Migration aborted.' );
			return false;
		}

		$registered_events = Loader::get_registered_events();

		if ( empty( $registered_events ) ) {
			edd_debug_log( '[EDD Cron Migration] No registered events found. Migration skipped.' );
			return true;
		}

		$action_scheduler = new ActionScheduler();
		$migrated         = array();
		$failed           = array();

		// Clear events from Action Scheduler.
		foreach ( $registered_events as $event ) {
			if ( ! $event instanceof Events\Event ) {
				continue;
			}

			$hook = $event->hook ?? null;
			$args = $event->args ?? array();

			if ( empty( $hook ) ) {
				continue;
			}

			// Unschedule from Action Scheduler (try both 'edd' and empty group).
			$unscheduled = false;
			foreach ( array( 'edd', '' ) as $group ) {
				if ( $action_scheduler->unschedule_all( $hook, $args, $group ) ) {
					$unscheduled = true;
				}
			}

			if ( $unscheduled ) {
				$migrated[] = $hook;
			}
		}

		if ( empty( $migrated ) ) {
			edd_debug_log( '[EDD Cron Migration] No events were cleared from Action Scheduler. Migration skipped.' );
			return false;
		}

		// Reset Handler to use WP-Cron.
		Handler::reset();

		// Re-schedule events using WP-Cron.
		foreach ( $registered_events as $event ) {
			if ( ! $event instanceof Events\Event ) {
				continue;
			}

			$hook = $event->hook ?? null;

			if ( empty( $hook ) || ! in_array( $hook, $migrated, true ) ) {
				continue;
			}

			$event->reset();
			$scheduled = $event->schedule();

			if ( ! $scheduled ) {
				$failed[] = $hook;
			} else {
				$migrated[] = $hook;
			}
		}

		// Log results.
		$success_count = count( $migrated ) - count( $failed );
		edd_debug_log(
			sprintf(
				'[EDD Cron Migration] Successfully migrated %d/%d events from Action Scheduler to WP-Cron.',
				$success_count,
				count( $migrated )
			)
		);

		if ( ! empty( $failed ) ) {
			edd_debug_log(
				sprintf(
					'[EDD Cron Migration] Failed to migrate %d events: %s',
					count( $failed ),
					implode( ', ', $failed )
				)
			);
		}

		/**
		 * Fires after EDD cron events have been migrated to WP-Cron.
		 *
		 * @since 3.6.5
		 *
		 * @param array $migrated Array of hook names that were successfully migrated.
		 * @param array $failed Array of hook names that failed to migrate.
		 */
		do_action( 'edd_cron_migrated_to_wp_cron', $migrated, $failed );

		return $success_count > 0;
	}
}
