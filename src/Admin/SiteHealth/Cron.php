<?php
/**
 * Gets the cron data for the Site Health data.
 *
 * @since 3.3.0
 * @package EDD\Admin\SiteHealth
 */

namespace EDD\Admin\SiteHealth;

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
			'label'  => __( 'Easy Digital Downloads &mdash; Cron Events', 'easy-digital-downloads' ),
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
		$data      = array();
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
			$data[ $key ] = array(
				'label' => $schedule,
				'value' => $this->get_next_scheduled( $schedule ),
			);
		}

		return $data;
	}

	/**
	 * Gets the date for the next scheduled event.
	 *
	 * @since 3.3.0
	 * @param string $event The event to check.
	 * @return string
	 */
	private function get_next_scheduled( $event ) {
		$timestamp = wp_next_scheduled( $event );
		if ( ! $timestamp ) {
			return 'Not Scheduled';
		}

		if ( defined( 'WP_DISABLE_CRON' ) && ! empty( WP_DISABLE_CRON ) ) {
			return 'Cron Disabled';
		}

		return sprintf(
			'%s (in %s)',
			edd_date_i18n( $timestamp, 'Y-m-d H:i:s' ),
			human_time_diff( $timestamp )
		);
	}
}
