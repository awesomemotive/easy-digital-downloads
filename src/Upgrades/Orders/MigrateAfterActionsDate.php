<?php
/**
 * Migrates the order actions date to a new column, from the existing order meta.
 *
 * @since 3.2.0
 *
 * @package EDD
 * @subpackage Upgrades
 * @category Orders
 */

namespace EDD\Upgrades\Orders;

use EDD\Utils\Date;
use EDD\EventManagement\SubscriberInterface;
use EDD\Upgrades\Utilities\MigrationCheck;

/**
 * Class MigrateAfterActionsDate
 *
 * @since 3.2.0
 */
class MigrateAfterActionsDate implements SubscriberInterface {

	/**
	 * The name of the upgrade.
	 *
	 * @var string
	 */
	protected $upgrade_name = 'migrate_order_actions_date';

	/**
	 * The name of the cron action.
	 *
	 * @var string
	 */
	protected $cron_action = 'edd_migrate_order_actions_date';

	/**
	 * The name of the option that stores the total count of rows to process.
	 *
	 * @var string
	 */
	protected $total_count_option = 'edd_migrate_order_actions_date_total';

	/**
	 * The remote ID of the notification that the migration is in progress.
	 *
	 * @var string
	 */
	protected $in_progress_remote_id = 'action-time-running';

	/**
	 * The remote ID of the notification that the migration is complete.
	 *
	 * @var string
	 */
	protected $complete_remote_id = 'action-time-done';

	/**
	 * Hook into actions and filters.
	 *
	 * @since  3.2
	 */
	public static function get_subscribed_events() {
		// If the upgrade has already been run, don't hook anything.
		if ( edd_has_upgrade_completed( 'migrate_order_actions_date' ) ) {
			return array();
		}

		// If the initial EDD 3.x migration hasn't completed, don't hook anything.
		if ( ! MigrationCheck::is_v30_migration_complete() ) {
			return array();
		}

		// Always hook the migration hook.
		$hooks = array(
			'edd_migrate_order_actions_date' => 'process_step',
		);

		if ( ! wp_next_scheduled( 'edd_migrate_order_actions_date' ) ) {
			$hooks['shutdown'] = array( 'maybe_schedule_background_update', 99 );
		}

		return $hooks;
	}

	/**
	 * Maybe schedules the background update.
	 *
	 * We're running this on shutdown, so we can be sure that the Orders table has been created.
	 *
	 * @since 3.2.0
	 *
	 * @return void
	 */
	public function maybe_schedule_background_update() {
		$orders_table      = new \EDD\Database\Tables\Orders();
		$table_version_key = $orders_table->db_version_key;
		$table_version     = get_option( $table_version_key, 0 );

		// If our table version is earlier than the one we need, return.
		if ( version_compare( '202307111', $table_version, '<' ) ) {
			return;
		}

		// This is the right table, and the right version, so let's schedule the background update.
		$this->register_first_background_event();
	}

	/**
	 * Registers the first background event to start the migration.
	 *
	 * @since 3.2.0
	 *
	 * @return void
	 */
	private function register_first_background_event() {
		// If we've already scheduled the cleanup, no need to schedule it again.
		if ( wp_next_scheduled( $this->cron_action ) ) {
			return;
		}

		// See if we have any order meta with the key of _edd_complete_actions_run.
		global $wpdb;
		$has_action_date_meta = $wpdb->get_var( "SELECT COUNT(meta_id) FROM {$this->get_meta_table_name()} WHERE meta_key = '_edd_complete_actions_run'" );

		if ( empty( $has_action_date_meta ) ) {
			$this->mark_complete( false );
			return;
		}

		// Only update the total count option if it doesn't exist. This prevents it from being overridden in some edge cases.
		if ( empty( get_option( $this->total_count_option ) ) ) {
			// Set the total amount in a transient so we can use it later.
			update_option( $this->total_count_option, $has_action_date_meta, false );
		}

		$this->add_or_update_initial_notification();

		// ...And schedule a single event a minute from now to start the processing of this data.
		wp_schedule_single_event( time() + MINUTE_IN_SECONDS, $this->cron_action );
	}

	/**
	 * Processes a single step of the migration.
	 *
	 * If there are more items to process, it will schedule another single event to run in a minute.
	 * If there are no more items to process, it will mark the migration as complete.
	 *
	 * @since 3.2.0
	 *
	 * @return void
	 */
	public function process_step() {
		// Since this hooks on an action, don't let it run if we're not in a cron.
		if ( ! edd_doing_cron() ) {
			return;
		}

		global $wpdb;
		$meta_rows = $wpdb->get_results( "SELECT meta_id, edd_order_id, meta_value FROM {$this->get_meta_table_name()} WHERE meta_key = '_edd_complete_actions_run' ORDER BY edd_order_id DESC LIMIT 500" );

		edd_debug_log( 'Found records to migrate (max 500): ' . count( $meta_rows ) );

		// If we don't have anymore items to process, mark it as complete.
		if ( empty( $meta_rows ) ) {
			$this->mark_complete();
			return;
		}

		// Iterate through the meta rows and update the order.
		$migrated_meta_ids = array();
		$migrated_note_ids = array();

		// We don't need to schedule a recalculation here.
		add_filter( 'edd_recalculate_bypass_cron', '__return_true' );

		foreach ( $meta_rows as $row ) {
			// Convert the timestamp to a DateTime object.

			$date = new Date();
			$date->setTimestamp( $row->meta_value )->setTimezone( new \DateTimeZone( 'UTC' ) );

			// Update the order.
			$updated = edd_update_order( $row->edd_order_id, array( 'date_actions_run' => $date->format( 'mysql' ) ) );

			if ( $updated ) {
				// Store the meta ids we can remove.
				$migrated_meta_ids[] = $row->meta_id;

				// Store the note ids we can remove.
				$note_content = __( 'After payment actions processed.', 'easy-digital-downloads' );
				$note_id      = $wpdb->get_var( "SELECT id FROM {$this->get_notes_table_name()} WHERE object_id = {$row->edd_order_id} AND object_type = 'order' AND content = '{$note_content}'" );
				if ( $note_id ) {
					$migrated_note_ids[] = $note_id;
				}
			}
		}

		// Remove our filter for bypassing the cron.
		remove_filter( 'edd_recalculate_bypass_cron', '__return_true' );

		$migrated_meta_ids = array_filter( $migrated_meta_ids );
		// If we have any migrated meta IDs, delete them.
		if ( ! empty( $migrated_meta_ids ) ) {
			// Delete the meta rows we just migrated.
			$wpdb->query( "DELETE FROM {$this->get_meta_table_name()} WHERE meta_id IN (" . implode( ',', $migrated_meta_ids ) . ')' );
		}

		$migrated_note_ids = array_filter( $migrated_note_ids );
		// If we found notes to delete, delete them.
		if ( ! empty( $migrated_note_ids ) ) {
			$wpdb->query( "DELETE FROM {$this->get_notes_table_name()} WHERE id IN (" . implode( ',', $migrated_note_ids ) . ')' );
		}

		$this->add_or_update_initial_notification();

		$percent_complete = $this->get_percentage_complete();

		edd_debug_log( 'Processed step of order actions date migration. Percentage Complete: ' . $percent_complete . '%' );

		// ...And schedule another single event so we can process the next batch.
		wp_schedule_single_event( time() + MINUTE_IN_SECONDS, 'edd_migrate_order_actions_date' );
	}

	/**
	 * Marks the migration as complete.
	 *
	 * @since 3.2.0
	 *
	 * @param bool $add_notification If we should add a notification that the migration is complete.
	 *
	 * @return void
	 */
	private function mark_complete( $add_notification = true ) {
		// Set the upgrade as complete.
		edd_set_upgrade_complete( 'migrate_order_actions_date' );

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
				'title'      => __( 'Order Table Optimization Complete!', 'easy-digital-downloads' ),
				'content'    => __( 'Easy Digital Downloads has finished updating your orders database! Thank you for your patience.', 'easy-digital-downloads' ),
			)
		);
	}

	/**
	 * Gets the initial notification about the migration.
	 *
	 * @since 3.2.0
	 *
	 * @return object
	 */
	private function get_initial_notification() {
		return EDD()->notifications->get_item_by( 'remote_id', $this->in_progress_remote_id );
	}

	/**
	 * Adds or updates the initial notification about the migration.
	 *
	 * @since 3.2.0
	 *
	 * @return void
	 */
	private function add_or_update_initial_notification() {
		$initial_notification = $this->get_initial_notification();
		$percent_complete     = $this->get_percentage_complete();

		// translators: %s is the % complete.
		$notification_title = sprintf( __( 'Optimizing Orders Table ( %d%% )', 'easy-digital-downloads' ), $percent_complete );

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
					'content'    => __( 'Easy Digital Downloads is updating the Orders and Order Meta table in the background. This process may take a while to complete depending on the number of orders you have. We\'ll let you know when the process is complete.', 'easy-digital-downloads' ),
				)
			);
		}
	}

	/**
	 * Gets the name of the order meta table.
	 *
	 * @since 3.2.0
	 *
	 * @return string
	 */
	private function get_meta_table_name() {
		$order_meta_table = new \EDD\Database\Tables\Order_Meta();
		return $order_meta_table->table_name;
	}

	/**
	 * Gets the name of the notes table.
	 *
	 * @since 3.2.0
	 *
	 * @return string
	 */
	private function get_notes_table_name() {
		$notes_table = new \EDD\Database\Tables\Notes();
		return $notes_table->table_name;
	}

	/**
	 * Gets the percentage complete.
	 *
	 * @since 3.2.0
	 *
	 * @return int
	 */
	private function get_percentage_complete() {
		static $percent_complete;

		if ( ! is_null( $percent_complete ) ) {
			return $percent_complete;
		}

		global $wpdb;

		// Get the total amount of rows remaining.
		$total_rows_remaining = $wpdb->get_var( "SELECT COUNT(*) FROM {$this->get_meta_table_name()} WHERE meta_key = '_edd_complete_actions_run'" );

		// Get the total we started with.
		$total_rows_start = get_option( $this->total_count_option );

		// Format a % complete without decimals.
		$percent_complete = number_format( ( ( $total_rows_start - $total_rows_remaining ) / $total_rows_start ) * 100, 0 );

		// Just in case we end up over 100%, somehow...make it 100%;
		if ( $percent_complete > 100 ) {
			$percent_complete = 100;
		}

		return $percent_complete;
	}
}
