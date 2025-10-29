<?php
/**
 * Log Trait
 *
 * @package     EDD\Profiler\Traits
 * @copyright   Copyright (c) 2025, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.6.0
 */

namespace EDD\Profiler\Traits;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

use EDD\Utils\FileSystem;

trait Log {

	/**
	 * Filename of the profiler log.
	 *
	 * @var string
	 */
	private static $filename = '';

	/**
	 * File path to the profiler log.
	 *
	 * @var string
	 */
	private static $file = '';

	/**
	 * Whether the profiler log file is writable or not.
	 *
	 * @var bool
	 */
	private static $is_writable = true;

	/**
	 * Max unrotated log size in bytes before rotating.
	 *
	 * @since 3.6.0
	 * @var int
	 */
	private static $max_size_bytes = 524288; // 512 KB

	/**
	 * Number of rotated log files to keep.
	 *
	 * @since 3.6.0
	 * @var int
	 */
	private static $max_files = 5;

	/**
	 * Log results to PHP error log
	 *
	 * @since 3.6.0
	 * @return void
	 */
	public function log_results() {
		if ( ! $this->is_enabled() ) {
			return;
		}

		$results = $this->get_results();

		if ( empty( $results['total_calls'] ) ) {
			return;
		}

		$name          = $this->get_name();
		$cache_enabled = $this->is_cache_enabled() ? ' [CACHE ENABLED]' : ' [CACHE DISABLED]';

		$this->log( '[' . $name . '] ======================================== ' . $cache_enabled );
		$this->log(
			sprintf(
				'[%1$s] User: %2$s',
				$name,
				get_current_user_id() ? 'Logged In' : 'Not Logged In'
			)
		);

		// Determine what's happening (action or URI).
		$action = null;
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			$action = $_POST['action'] ?? $_GET['action'] ?? 'unknown_ajax';
			$this->log(
				sprintf(
					'[%1$s] AJAX Action: %2$s',
					$name,
					$action
				)
			);
		} elseif ( defined( 'DOING_CRON' ) && DOING_CRON ) {
			$this->log( '[' . $name . '] Context: WP Cron' );
		} else {
			$this->log(
				sprintf(
					'[%1$s] URI: %2$s',
					$name,
					$_SERVER['REQUEST_URI'] ?? 'Unknown'
				)
			);
		}

		// Summary metrics.
		$this->log(
			sprintf(
				'[%1$s] Total Calls: %2$s',
				$name,
				$results['total_calls']
			)
		);
		$this->log(
			sprintf(
				'[%1$s] Calculation Time: %2$.2fms (%3$.1f%% of request)',
				$name,
				$results['calculation_time'] * 1000,
				$results['calculation_percent']
			)
		);
		$this->log(
			sprintf(
				'[%1$s] Request Time: %2$.2fms',
				$name,
				$results['request_time'] * 1000
			)
		);

		// Method breakdown.
		foreach ( $results['calls'] as $method => $data ) {
			if ( $data['count'] > 0 ) {
				$this->log(
					sprintf(
						'[%1$s]   - %2$s: %3$dx (%4$.2fms total, %5$.2fms avg)',
						$name,
						$method,
						$data['count'],
						$data['total_time'] * 1000,
						( $data['total_time'] / $data['count'] ) * 1000
					)
				);
			}
		}

		$this->log( '[' . $name . '] ========================================' );
	}

	/**
	 * Get the profiler log file path.
	 *
	 * @since 3.6.0
	 * @return string The profiler log file path.
	 */
	public static function get_file_path() {
		self::setup_log_file();

		return self::$file;
	}

	/**
	 * Get the contents of the profiler log file.
	 *
	 * @since 3.6.0
	 * @return string The contents of the profiler log file.
	 */
	public static function get_file_contents() {
		$file = '';
		self::setup_log_file();

		if ( FileSystem::get_fs()->exists( self::$file ) ) {
			if ( ! FileSystem::get_fs()->is_writable( self::$file ) ) {
				self::$is_writable = false;
			}

			$file = FileSystem::get_fs()->get_contents( self::$file );
		} else {
			FileSystem::get_fs()->put_contents( self::$file, '' );
			FileSystem::get_fs()->chmod( self::$file, 0664 );
		}

		return $file;
	}

	/**
	 * Clear the profiler log file.
	 *
	 * @since 3.6.0
	 * @return void
	 */
	public static function clear_log_file() {
		self::setup_log_file();

		$fs   = FileSystem::get_fs();
		$base = self::$file;

		// Delete base and all rotated variants (e.g., -1 .. -N).
		$targets   = array( $base );
		$max_files = (int) self::$max_files;
		for ( $i = 1; $i <= $max_files; $i++ ) {
			$targets[] = $base . '-' . $i;
		}
		foreach ( $targets as $target ) {
			if ( $fs->exists( $target ) ) {
				$fs->delete( $target );
			}
		}

		self::$file        = '';
		self::$is_writable = true;
	}

	/**
	 * Sets up the profiler log file if it is writable
	 *
	 * @since 3.6.0
	 * @return void
	 */
	private static function setup_log_file() {
		if ( ! empty( self::$file ) ) {
			return;
		}

		$upload_dir     = edd_get_upload_dir();
		self::$filename = wp_hash( home_url( '/' ) ) . '-edd-profiler-' . self::get_id() . '.log';
		self::$file     = trailingslashit( $upload_dir ) . self::$filename;

		if ( ! FileSystem::get_fs()->exists( self::$file ) ) {
			FileSystem::maybe_move_file( self::$filename, self::$file );
		}

		if ( ! FileSystem::get_fs()->is_writable( $upload_dir ) ) {
			self::$is_writable = false;
		}
	}

	/**
	 * Log message to the profiler log file.
	 *
	 * @since 3.6.0
	 * @param string $message Message to insert in the log.
	 * @return void
	 */
	private function log( $message = '' ) {
		if ( ! self::$is_writable || empty( $message ) ) {
			return;
		}

		self::setup_log_file();

		// Rotate if file too large before appending.
		self::maybe_rotate_log();

		$message = gmdate( 'Y-n-d H:i:s' ) . ' - ' . $message . "\r\n";
		file_put_contents( self::$file, $message, FILE_APPEND );
	}

	/**
	 * Rotate the profiler log file when it exceeds the size threshold.
	 * Keeps up to self::$max_files rotated logs, using suffixes -1, -2, ...
	 *
	 * @since 3.6.0
	 * @return void
	 */
	private static function maybe_rotate_log() {
		$file = self::$file;
		$fs   = FileSystem::get_fs();
		if ( empty( $file ) || ! $fs->exists( $file ) ) {
			return;
		}

		$size = filesize( $file );
		if ( false === $size || $size < self::get_max_size_bytes() ) {
			return;
		}

		// Delete the oldest if it exists.
		$oldest = $file . '-' . self::$max_files;
		if ( $fs->exists( $oldest ) ) {
			$fs->delete( $oldest );
		}

		// Shift rotated files upwards.
		for ( $i = self::$max_files - 1; $i >= 1; $i-- ) {
			$from = $file . '-' . $i;
			$to   = $file . '-' . ( $i + 1 );
			if ( $fs->exists( $from ) ) {
				rename( $from, $to );
			}
		}

		// Rotate current file to -1 and create a new file.
		rename( $file, $file . '-1' );
		$fs->put_contents( $file, '' );
		$fs->chmod( $file, 0664 );
	}

	/**
	 * Get the tail of the log file, optionally limited by bytes.
	 *
	 * Returns an array with keys:
	 * - contents (string): tail content
	 * - truncated (bool): whether the content was truncated
	 *
	 * @since 3.6.0
	 * @param int|null $bytes Number of bytes from end of file; when null, uses filter.
	 * @return array
	 */
	public static function get_file_tail( $bytes = null ) {
		self::setup_log_file();
		$fs = FileSystem::get_fs();
		if ( null === $bytes ) {
			$bytes = (int) apply_filters( 'edd_profiler_heartbeat_tail_bytes', 131072 ); // 128 KB
		}

		if ( ! $fs->exists( self::$file ) ) {
			return array(
				'contents'  => '',
				'truncated' => false,
			);
		}

		$size = filesize( self::$file );
		if ( false === $size || $size <= 0 ) {
			return array(
				'contents'  => '',
				'truncated' => false,
			);
		}

		$truncated = false;
		$start     = 0;
		$length    = (int) $size;

		if ( $size > $bytes ) {
			$start     = $size - $bytes;
			$length    = $bytes;
			$truncated = true;
		}

		$fp = @fopen( self::$file, 'rb' );
		if ( false === $fp ) {
			return array(
				'contents'  => $fs->get_contents( self::$file ),
				'truncated' => $truncated,
			);
		}
		if ( $start > 0 ) {
			fseek( $fp, $start );
		}
		$contents = fread( $fp, $length );
		fclose( $fp );

		return array(
			'contents'  => (string) $contents,
			'truncated' => (bool) $truncated,
		);
	}

	/**
	 * Get the effective max log size threshold (bytes).
	 *
	 * @since 3.6.0
	 * @return int
	 */
	private static function get_max_size_bytes() {
		return (int) apply_filters( 'edd_profiler_max_log_size_bytes', self::$max_size_bytes );
	}
}
