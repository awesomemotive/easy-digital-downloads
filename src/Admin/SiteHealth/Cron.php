<?php
/**
 * Gets the cron data for the Site Health data.
 *
 * @since 3.3.0
 * @package EDD\Admin\SiteHealth
 */

namespace EDD\Admin\SiteHealth;

use EDD\Cron\Schedulers\Handler;

/**
 * Loads cron data into Site Health.
 *
 * @since 3.3.0
 */
class Cron {

	/**
	 * Gets the cron section data for Site Health.
	 *
	 * @since 3.3.0
	 * @return array
	 */
	public function get() {
		return array(
			'label'  => __( 'Easy Digital Downloads &mdash; Scheduled Events', 'easy-digital-downloads' ),
			'fields' => $this->get_data(),
		);
	}

	/**
	 * Gets the array of cron data.
	 *
	 * @since 3.3.0
	 * @return array
	 */
	private function get_data() {
		$data = array();

		// Add system information.
		$system_info = $this->get_system_info();
		foreach ( $system_info as $key => $info ) {
			$data[ 'system_' . $key ] = $info;
		}

		// Add scheduled events.
		$schedules = array(
			'daily'  => 'edd_daily_scheduled_events',
			'weekly' => 'edd_weekly_scheduled_events',
			'email'  => 'edd_email_summary_cron',
		);
		if ( edd_get_component( 'session' ) ) {
			$schedules['sessions'] = 'edd_cleanup_sessions';
		}
		if ( edd_is_gateway_active( 'stripe' ) ) {
			$schedules['stripe'] = 'edds_cleanup_rate_limiting_log';
		}
		foreach ( $schedules as $key => $schedule ) {
			$data[ 'event_' . $key ] = array(
				'label' => $schedule,
				'value' => $this->get_next_scheduled( $schedule ),
			);
		}

		return $data;
	}

	/**
	 * Gets the cron system information.
	 *
	 * @since 3.6.5
	 * @return array
	 */
	private function get_system_info() {
		$data = array(
			'active_scheduler' => array(
				'label' => __( 'Active Scheduler', 'easy-digital-downloads' ),
				'value' => $this->format_scheduler_name( Handler::get_active_scheduler_name() ),
			),
		);

		$data['registered_events'] = array(
			'label' => __( 'Registered Events', 'easy-digital-downloads' ),
			'value' => count( \EDD\Cron\Loader::get_registered_events() ),
		);

		$data['registered_components'] = array(
			'label' => __( 'Registered Components', 'easy-digital-downloads' ),
			'value' => count( \EDD\Cron\Loader::get_registered_components() ),
		);

		// Add WP-Cron status.
		if ( defined( 'WP_DISABLE_CRON' ) && WP_DISABLE_CRON ) {
			$data['wp_cron_status'] = array(
				'label' => __( 'WP-Cron Status', 'easy-digital-downloads' ),
				'value' => __( 'Disabled (WP_DISABLE_CRON is set)', 'easy-digital-downloads' ),
			);
		}

		return $data;
	}

	/**
	 * Format the scheduler name for display.
	 *
	 * @since 3.6.5
	 * @param string $scheduler The scheduler name.
	 * @return string
	 */
	private function format_scheduler_name( $scheduler ) {
		$names = array(
			'action-scheduler' => __( 'Action Scheduler', 'easy-digital-downloads' ),
			'wp-cron'          => __( 'WP-Cron', 'easy-digital-downloads' ),
		);

		return isset( $names[ $scheduler ] ) ? $names[ $scheduler ] : $scheduler;
	}

	/**
	 * Gets the date for the next scheduled event.
	 *
	 * @since 3.3.0
	 * @param string $event The event to check.
	 * @return string
	 */
	private function get_next_scheduled( $event ) {
		// Use the active scheduler to check for scheduled events.
		$scheduler = Handler::get_scheduler();
		$timestamp = $scheduler->next_scheduled( $event );

		if ( ! $timestamp ) {
			return __( 'Not Scheduled', 'easy-digital-downloads' );
		}

		if ( defined( 'WP_DISABLE_CRON' ) && WP_DISABLE_CRON ) {
			return __( 'Cron Disabled', 'easy-digital-downloads' );
		}

		return sprintf(
			'%s (in %s)',
			edd_date_i18n( $timestamp, 'Y-m-d H:i:s' ),
			human_time_diff( $timestamp )
		);
	}
}
