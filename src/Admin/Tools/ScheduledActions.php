<?php
/**
 * Scheduled Actions tab.
 *
 * @package     EDD\Admin\Tools
 * @copyright   Copyright (c) 2026, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.6.5
 */

namespace EDD\Admin\Tools;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

use EDD\EventManagement\SubscriberInterface;
use EDD\Cron\Schedulers\ActionScheduler;

/**
 * Scheduled Actions tab.
 *
 * @since 3.6.5
 */
class ScheduledActions implements SubscriberInterface {

	/**
	 * Get the subscribed events.
	 *
	 * @since 3.6.5
	 * @return array
	 */
	public static function get_subscribed_events(): array {
		return array(
			'edd_tools_tab_scheduled_actions' => 'render',
		);
	}

	/**
	 * Render the scheduled actions tab.
	 *
	 * @since 3.6.5
	 * @return void
	 */
	public function render(): void {
		if ( ! current_user_can( 'manage_shop_settings' ) ) {
			return;
		}

		// Check if Action Scheduler is available.
		if ( ! ActionScheduler::is_available() ) {
			$this->render_not_available();
			return;
		}

		$this->render_actions_table();
	}

	/**
	 * Render message when Action Scheduler is not available.
	 *
	 * @since 3.6.5
	 * @return void
	 */
	private function render_not_available(): void {
		?>
		<div class="edd-settings-content">
			<div class="postbox">
				<h3><span><?php esc_html_e( 'Scheduled Actions', 'easy-digital-downloads' ); ?></span></h3>
				<div class="inside">
					<p><?php esc_html_e( 'Action Scheduler is not available. Please ensure Easy Digital Downloads is properly installed.', 'easy-digital-downloads' ); ?></p>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Render the scheduled actions table.
	 *
	 * @since 3.6.5
	 * @return void
	 */
	private function render_actions_table(): void {
		// Get all scheduled actions for the EDD group (pending and in-progress).
		$actions = $this->get_edd_actions( 1000 );

		?>
		<div class="edd-settings-content">
			<div class="postbox">
				<h3><span><?php esc_html_e( 'EDD Scheduled Actions', 'easy-digital-downloads' ); ?></span></h3>
				<div class="inside">
					<p class="description">
						<?php
						echo wp_kses_post(
							sprintf(
								/* translators: %s: group identifier */
								__( 'Displaying scheduled actions registered with Easy Digital Downloads (group: %s)', 'easy-digital-downloads' ),
								'<code>' . esc_html( ActionScheduler::GROUP ) . '</code>'
							)
						);
						?>
					</p>

					<?php if ( empty( $actions ) ) : ?>
						<p><?php esc_html_e( 'No scheduled actions found.', 'easy-digital-downloads' ); ?></p>
					<?php else : ?>
						<table class="widefat striped" style="margin-top: 15px;">
							<thead>
								<tr>
									<th><?php esc_html_e( 'Hook', 'easy-digital-downloads' ); ?></th>
									<th><?php esc_html_e( 'Status', 'easy-digital-downloads' ); ?></th>
									<th><?php esc_html_e( 'Scheduled Date', 'easy-digital-downloads' ); ?></th>
									<th><?php esc_html_e( 'Recurrence', 'easy-digital-downloads' ); ?></th>
									<th><?php esc_html_e( 'Arguments', 'easy-digital-downloads' ); ?></th>
								</tr>
							</thead>
							<tbody>
								<?php foreach ( $actions as $action_id => $action ) : ?>
									<?php $this->render_action_row( $action_id, $action ); ?>
								<?php endforeach; ?>
							</tbody>
						</table>

						<p class="description" style="margin-top: 15px;">
							<?php
							echo wp_kses_post(
								sprintf(
									/* translators: %1$s: Action Scheduler admin URL, %2$s: count of actions shown */
									__( 'Showing up to %2$s pending actions. For complete action management, visit the <a href="%1$s">Action Scheduler admin page</a>.', 'easy-digital-downloads' ),
									esc_url( admin_url( 'tools.php?page=action-scheduler' ) ),
									count( $actions )
								)
							);
							?>
						</p>
					<?php endif; ?>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Render a single action row.
	 *
	 * @since 3.6.5
	 *
	 * @param int    $action_id The action ID.
	 * @param object $action    The action object.
	 * @return void
	 */
	private function render_action_row( int $action_id, $action ): void {
		if ( ! is_object( $action ) ) {
			return;
		}

		// Get action details.
		$hook     = method_exists( $action, 'get_hook' ) ? $action->get_hook() : __( 'Unknown', 'easy-digital-downloads' );
		$schedule = method_exists( $action, 'get_schedule' ) ? $action->get_schedule() : null;
		$args     = method_exists( $action, 'get_args' ) ? $action->get_args() : array();

		// Get status using Action Scheduler's store.
		$status_name = '';
		if ( class_exists( 'ActionScheduler_Store' ) ) {
			$store       = \ActionScheduler_Store::instance();
			$status_name = $store->get_status( $action_id );
		}

		$status_labels = $this->get_status_labels();
		$status        = isset( $status_labels[ $status_name ] ) ? $status_labels[ $status_name ] : ucfirst( $status_name );

		// Get recurrence using Action Scheduler's format.
		$recurrence = $this->get_recurrence( $action );

		// Get next run time using Action Scheduler's format.
		$next_run = $this->get_schedule_display( $schedule );

		// Format arguments for display.
		$args_display = '';
		if ( ! empty( $args ) ) {
			$args_display = '<ul style="margin: 0; padding-left: 20px;">';
			foreach ( $args as $key => $value ) {
				$args_display .= sprintf(
					'<li><code>%s => %s</code></li>',
					esc_html( $this->format_value( $key ) ),
					esc_html( $this->format_value( $value ) )
				);
			}
			$args_display .= '</ul>';
		}
		?>
		<tr>
			<td><strong><?php echo esc_html( $hook ); ?></strong></td>
			<td>
				<span class="edd-status-badge edd-status-<?php echo esc_attr( sanitize_html_class( $status_name ) ); ?>">
					<?php echo esc_html( $status ); ?>
				</span>
			</td>
			<td><?php echo wp_kses_post( $next_run ); ?></td>
			<td><?php echo esc_html( $recurrence ); ?></td>
			<td><?php echo ! empty( $args_display ) ? wp_kses_post( $args_display ) : '&mdash;'; ?></td>
		</tr>
		<?php
	}

	/**
	 * Get the recurrence display for an action.
	 *
	 * @since 3.6.5
	 *
	 * @param object $action The action object.
	 * @return string The recurrence display string.
	 */
	private function get_recurrence( $action ): string {
		if ( ! is_object( $action ) || ! method_exists( $action, 'get_schedule' ) ) {
			return __( 'Non-repeating', 'easy-digital-downloads' );
		}

		$schedule = $action->get_schedule();
		if ( ! $schedule || ! method_exists( $schedule, 'is_recurring' ) ) {
			return __( 'Non-repeating', 'easy-digital-downloads' );
		}

		if ( $schedule->is_recurring() && method_exists( $schedule, 'get_recurrence' ) ) {
			$recurrence = $schedule->get_recurrence();

			if ( is_numeric( $recurrence ) ) {
				/* translators: %s: time interval */
				return sprintf( __( 'Every %s', 'easy-digital-downloads' ), $this->human_interval( $recurrence ) );
			}

			return $recurrence;
		}

		return __( 'Non-repeating', 'easy-digital-downloads' );
	}

	/**
	 * Get the schedule display string for an action.
	 *
	 * @since 3.6.5
	 *
	 * @param object|null $schedule The schedule object.
	 * @return string The schedule display string.
	 */
	private function get_schedule_display( $schedule ): string {
		if ( ! $schedule || ! method_exists( $schedule, 'get_date' ) ) {
			return __( 'Not scheduled', 'easy-digital-downloads' );
		}

		$date = $schedule->get_date();
		if ( ! $date ) {
			return __( 'Not scheduled', 'easy-digital-downloads' );
		}

		$next_timestamp    = $date->getTimestamp();
		$schedule_display  = edd_date_i18n( $next_timestamp, 'datetime' );
		$schedule_display .= '<br/>';

		if ( time() > $next_timestamp ) {
			/* translators: %s: time interval */
			$schedule_display .= sprintf( __( '(%s ago)', 'easy-digital-downloads' ), $this->human_interval( time() - $next_timestamp ) );
		} else {
			/* translators: %s: time interval */
			$schedule_display .= sprintf( __( '(%s)', 'easy-digital-downloads' ), $this->human_interval( $next_timestamp - time() ) );
		}

		return $schedule_display;
	}

	/**
	 * Convert an interval of seconds into a human-friendly string.
	 *
	 * @since 3.6.5
	 *
	 * @param int $interval A interval in seconds.
	 * @return string A human friendly string representation of the interval.
	 */
	private function human_interval( int $interval ): string {
		if ( $interval <= 0 ) {
			return __( 'Now!', 'easy-digital-downloads' );
		}

		$time_periods = array(
			array(
				'seconds' => YEAR_IN_SECONDS,
				'single'  => __( 'year', 'easy-digital-downloads' ),
				'plural'  => __( 'years', 'easy-digital-downloads' ),
			),
			array(
				'seconds' => MONTH_IN_SECONDS,
				'single'  => __( 'month', 'easy-digital-downloads' ),
				'plural'  => __( 'months', 'easy-digital-downloads' ),
			),
			array(
				'seconds' => WEEK_IN_SECONDS,
				'single'  => __( 'week', 'easy-digital-downloads' ),
				'plural'  => __( 'weeks', 'easy-digital-downloads' ),
			),
			array(
				'seconds' => DAY_IN_SECONDS,
				'single'  => __( 'day', 'easy-digital-downloads' ),
				'plural'  => __( 'days', 'easy-digital-downloads' ),
			),
			array(
				'seconds' => HOUR_IN_SECONDS,
				'single'  => __( 'hour', 'easy-digital-downloads' ),
				'plural'  => __( 'hours', 'easy-digital-downloads' ),
			),
			array(
				'seconds' => MINUTE_IN_SECONDS,
				'single'  => __( 'minute', 'easy-digital-downloads' ),
				'plural'  => __( 'minutes', 'easy-digital-downloads' ),
			),
			array(
				'seconds' => 1,
				'single'  => __( 'second', 'easy-digital-downloads' ),
				'plural'  => __( 'seconds', 'easy-digital-downloads' ),
			),
		);

		$output            = '';
		$periods_included  = 0;
		$seconds_remaining = $interval;

		foreach ( $time_periods as $time_period ) {
			if ( $periods_included >= 2 || $seconds_remaining <= 0 ) {
				break;
			}

			$periods_in_interval = floor( $seconds_remaining / $time_period['seconds'] );

			if ( $periods_in_interval > 0 ) {
				if ( ! empty( $output ) ) {
					$output .= ' ';
				}
				$output            .= $periods_in_interval . ' ' . ( 1 === (int) $periods_in_interval ? $time_period['single'] : $time_period['plural'] );
				$seconds_remaining -= $periods_in_interval * $time_period['seconds'];
				++$periods_included;
			}
		}

		return $output;
	}

	/**
	 * Format a value for display.
	 *
	 * @since 3.6.5
	 *
	 * @param mixed $value The value to format.
	 * @return string The formatted value.
	 */
	private function format_value( $value ): string {
		if ( is_array( $value ) || is_object( $value ) ) {
			return wp_json_encode( $value );
		}

		if ( is_bool( $value ) ) {
			return $value ? 'true' : 'false';
		}

		if ( is_null( $value ) ) {
			return 'null';
		}

		return (string) $value;
	}

	/**
	 * Get EDD actions from Action Scheduler.
	 *
	 * @since 3.6.5
	 *
	 * @param int $per_page Number of actions to retrieve.
	 * @return array Array of action objects.
	 */
	private function get_edd_actions( int $per_page = 1000 ): array {
		if ( ! function_exists( 'as_get_scheduled_actions' ) ) {
			return array();
		}

		// Get pending and in-progress actions.
		$actions = as_get_scheduled_actions(
			array(
				'group'    => ActionScheduler::GROUP,
				'status'   => array(
					\ActionScheduler_Store::STATUS_PENDING,
					\ActionScheduler_Store::STATUS_RUNNING,
				),
				'per_page' => $per_page,
			),
			OBJECT
		);

		return $actions;
	}

	/**
	 * Get status labels from Action Scheduler.
	 *
	 * @since 3.6.5
	 *
	 * @return array Status labels.
	 */
	private function get_status_labels(): array {
		if ( class_exists( 'ActionScheduler_Store' ) ) {
			return \ActionScheduler_Store::instance()->get_status_labels();
		}

		return array(
			'pending'     => __( 'Pending', 'easy-digital-downloads' ),
			'in-progress' => __( 'In-Progress', 'easy-digital-downloads' ),
			'complete'    => __( 'Complete', 'easy-digital-downloads' ),
			'canceled'    => __( 'Canceled', 'easy-digital-downloads' ),
			'failed'      => __( 'Failed', 'easy-digital-downloads' ),
		);
	}
}
