<?php
/**
 * Handles Export related cron events.
 *
 * @since 3.3.0
 *
 * @package EDD
 * @subpackage Cron\Components
 */

namespace EDD\Cron\Components;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Exports Class
 *
 * @since 3.3.0
 */
class Exports extends Component {

	/**
	 * The unique identifier for this component.
	 *
	 * @var string
	 */
	protected static $id = 'exports';

	/**
	 * Register the subscribed events.
	 *
	 * @since 3.3.0
	 */
	public static function get_subscribed_events(): array {
		return array(
			'edd_daily_scheduled_events' => 'clean_exports',
		);
	}

	/**
	 * Clean up the exports directory.
	 *
	 * @since 3.3.0
	 *
	 * @return void
	 */
	public function clean_exports() {
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

		// Now ensure that there are no files in the main uploads directory.
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

				// If the file does not have `edd-` in the name, skip it.
				if ( false === strpos( $file, 'edd-' ) ) {
					continue;
				}

				// If the file is not a CSV, skip it.
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
