<?php
/**
 * Cron
 *
 * @package     EDD
 * @subpackage  Classes/Cron
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.6
*/

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * EDD_Cron Class
 *
 * This class handles scheduled events
 *
 * @since 1.6
 */
class EDD_Cron {
	/**
	 * Get things going
	 *
	 * @since 1.6
	 * @see EDD_Cron::weekly_events()
	 */
	public function __construct() {
		add_filter( 'cron_schedules', array( $this, 'add_schedules'   ) );
		add_action( 'wp',             array( $this, 'schedule_events' ) );
		add_action( 'edd_daily_scheduled_events', array( $this, 'exports_cleanup' ) );
	}

	/**
	 * Registers new cron schedules
	 *
	 * @since 1.6
	 *
	 * @param array $schedules
	 * @return array
	 */
	public function add_schedules( $schedules = array() ) {
		// Adds once weekly to the existing schedules.
		$schedules['weekly'] = array(
			'interval' => 604800,
			'display'  => __( 'Once Weekly', 'easy-digital-downloads' )
		);

		return $schedules;
	}

	/**
	 * Schedules our events
	 *
	 * @since 1.6
	 * @return void
	 */
	public function schedule_events() {
		$this->weekly_events();
		$this->daily_events();
	}

	/**
	 * Schedule weekly events
	 *
	 * @access private
	 * @since 1.6
	 * @return void
	 */
	private function weekly_events() {
		if ( ! wp_next_scheduled( 'edd_weekly_scheduled_events' ) ) {
			wp_schedule_event( current_time( 'timestamp', true ), 'weekly', 'edd_weekly_scheduled_events' );
		}
	}

	/**
	 * Schedule daily events
	 *
	 * @access private
	 * @since 1.6
	 * @return void
	 */
	private function daily_events() {
		if ( ! wp_next_scheduled( 'edd_daily_scheduled_events' ) ) {
			wp_schedule_event( current_time( 'timestamp', true ), 'daily', 'edd_daily_scheduled_events' );
		}
	}

	/**
	 * Cleanup after ourselves during exports.
	 *
	 * @since 3.2.12
	 *
	 * @return void
	 */
	public function exports_cleanup() {
		if ( class_exists( 'EDD\Cron\Loader' ) ) {
			return;
		}

		$exports_dir = edd_get_exports_dir();
		$files       = scandir( $exports_dir );

		if ( ! empty( $files ) ) {
			foreach ( $files as $file ) {
				if ( '.' === $file[0] ) {
					continue;
				}

				$full_path = trailingslashit( $exports_dir ) . $file;

				if ( is_dir( $full_path ) || ( 'index.php' === basename( $full_path ) || 'index.html' === basename( $full_path ) ) ) {
					continue;
				}

				$modified_time = filemtime( $full_path );

				// If the file hasn't been modified in the last 2 hours, delete it.
				if ( time() - $modified_time > HOUR_IN_SECONDS * 2 ) {
					unlink( $full_path );
				}
			}
		}

		// Also remove any older exports in the uploads directory.
		$uploads_dir = wp_upload_dir();
		$files       = scandir( $uploads_dir['basedir'] );

		if ( ! empty( $files ) ) {
			foreach ( $files as $file ) {
				if ( '.' === $file[0] ) {
					continue;
				}

				$full_path = trailingslashit( $uploads_dir['basedir'] ) . $file;

				if ( is_dir( $full_path ) || ( 'index.php' === basename( $full_path ) || 'index.html' === basename( $full_path ) ) ) {
					continue;
				}

				// If the filename doesn't start with `edd-` don't delete it.
				if ( false === strpos( $file, 'edd-' ) ) {
					continue;
				}

				// If the file is not a .csv, don't delete it.
				if ( '.csv' !== substr( $file, -4 ) ) {
					continue;
				}

				$modified_time = filemtime( $full_path );

				// If the file hasn't been modified in the last 2 hours, delete it.
				if ( time() - $modified_time > HOUR_IN_SECONDS * 2 ) {
					unlink( $full_path );
				}
			}
		}
	}
}
$edd_cron = new EDD_Cron();
