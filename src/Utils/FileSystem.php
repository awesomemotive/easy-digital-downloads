<?php
/**
 * File system utility class.
 *
 * @package EDD\Utils
 * @since 3.2.10
 */

namespace EDD\Utils;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

/**
 * File system utility class.
 *
 * @since 3.2.10
 */
class FileSystem {

	/**
	 * Initialize the WordPress file system
	 *
	 * @since 3.0
	 *
	 * @global WP_Filesystem_Base $wp_filesystem
	 */
	public static function init_fs() {
		global $wp_filesystem;

		if ( ! empty( $wp_filesystem ) ) {
			return;
		}

		// Include the file-system.
		require_once ABSPATH . 'wp-admin/includes/file.php';

		// Initialize the file system.
		WP_Filesystem();
	}

	/**
	 * Get the WordPress file-system
	 *
	 * @since 3.0
	 *
	 * @return WP_Filesystem_Base
	 */
	public static function get_fs() {
		if ( ! empty( $GLOBALS['wp_filesystem'] ) ) {
			return $GLOBALS['wp_filesystem'];
		}

		self::init_fs();

		return $GLOBALS['wp_filesystem'];
	}

	/**
	 * Maybe move the file from the original location to the new location.
	 *
	 * @since 3.2.10
	 * @param string $file_name The file name.
	 * @param string $file      The new file location.
	 */
	public static function maybe_move_file( $file_name, $file ) {
		$uploads      = wp_upload_dir();
		$uploads_file = trailingslashit( $uploads['basedir'] ) . $file_name;
		if ( self::file_exists( $uploads_file ) ) {
			self::get_fs()->move( $uploads_file, $file );
		}
	}

	/**
	 * Check if a file exists.
	 *
	 * @since 3.3.4
	 * @param string $file The file to check.
	 * @return bool True if the file exists, false otherwise.
	 */
	public static function file_exists( $file ) {
		// Strip any protocol/file wrappers.
		$file = self::sanitize_file_path( $file );
		if ( ! self::is_direct() ) {
			return file_exists( $file );
		}

		return self::get_fs()->exists( $file );
	}

	/**
	 * Open a file.
	 *
	 * @since 3.3.4
	 * @param string $file The file to open.
	 * @param string $mode The mode to open the file in.
	 * @return resource|bool The file resource on success, false on failure.
	 */
	public static function fopen( $file, $mode ) {
		$file = self::sanitize_file_path( $file );

		return @fopen( $file, $mode );
	}

	/**
	 * Get the filesize.
	 *
	 * @since 3.3.4
	 * @param string $file The file to get the size of.
	 * @return int|bool The file size on success, false on failure.
	 */
	public static function size( $file ) {
		$file = self::sanitize_file_path( $file );

		return self::get_fs()->size( $file );
	}

	/**
	 * Get the file contents as a string.
	 *
	 * Returns the file contents as a string. If you need the file contents as an array (such as for CSV files), use `file()` instead.
	 *
	 * @since 3.3.4
	 * @param string $file The file to get the contents of.
	 * @return string|bool The file contents on success, false on failure.
	 */
	public static function get_contents( $file ) {
		$file = self::sanitize_file_path( $file );
		if ( ! self::is_direct() ) {
			return file_get_contents( $file );
		}

		return self::get_fs()->get_contents( $file );
	}

	/**
	 * Write contents to a file.
	 *
	 * @since 3.3.4
	 * @param string $file     The file to write to.
	 * @param string $contents The contents to write.
	 * @return int|bool The number of bytes written to the file on success, false on failure.
	 */
	public static function put_contents( $file, $contents ) {
		$file = self::sanitize_file_path( $file );
		if ( ! self::is_direct() ) {
			return file_put_contents( $file, $contents );
		}

		return self::get_fs()->put_contents( $file, $contents );
	}

	/**
	 * Get the file contents as an array.
	 *
	 * Returns the file contents as an array. Each line in the file is an element in the array.
	 *
	 * @since 3.3.4
	 * @param string $file The file to get the contents of.
	 * @return array|bool The file contents as an array on success, false on failure.
	 */
	public static function file( $file ) {
		$file = self::sanitize_file_path( $file );
		if ( ! self::is_direct() ) {
			return file( $file );
		}

		return self::get_fs()->get_contents_array( $file );
	}

	/**
	 * Get the modified time of a file.
	 *
	 * @since 3.3.4
	 * @param string $file The file to get the modified time of.
	 * @return int|bool The modified time on success, false on failure.
	 */
	public static function filemtime( $file ) {
		$file = self::sanitize_file_path( $file );

		return self::get_fs()->mtime( $file );
	}

	/**
	 * Create a symbolic link to a file.
	 *
	 * @since 3.3.4
	 * @param string $target The target of the link.
	 * @param string $link The link to create.
	 * @return bool True on success, false on failure.
	 */
	public static function symlink( $target, $link ) {
		$target = self::sanitize_file_path( realpath( $target ) );
		$link   = self::sanitize_file_path( $link );

		return @symlink( $target, $link );
	}

	/**
	 * Sanitize a file path.
	 *
	 * Removes potentially risky protocols from the file path.
	 *
	 * @since 3.3.4
	 * @param string $file The file path to sanitize.
	 * @return string The sanitized file path.
	 */
	public static function sanitize_file_path( $file ) {
		// If the file path doesn't have a protocol just return it.
		if ( false === strpos( $file, '://' ) && false === strpos( $file, urlencode( '://' ) ) ) {
			return $file;
		}

		$restricted_protocols = self::get_restricted_file_protocols();

		foreach ( $restricted_protocols as $protocol ) {
			// Create a case-insensitive pattern for each protocol.
			$pattern = '#^' . preg_quote( $protocol, '#' ) . '#i';
			$file    = preg_replace( $pattern, '', $file );
		}

		return $file;
	}

	/**
	 * Get the restricted file protocols.
	 *
	 * @since 3.3.4
	 * @return array The restricted file protocols.
	 */
	private static function get_restricted_file_protocols() {
		/**
		 * Filter the protocols that are restricted from file paths.
		 *
		 * @since 3.3.4
		 *
		 * @param array $protocols The protocols to restrict.
		 */
		$protocols = (array) apply_filters(
			'edd_file_system_restricted_protocols',
			array(
				'phar://',
				'php://',
				'glob://',
				'data://',
				'expect://',
				'zip://',
				'rar://',
				'zlib://',
			),
		);

		// Now we need the URL encoded protocols to ensure we catch all variations.
		return array_merge(
			$protocols,
			array_map( 'urlencode', $protocols )
		);
	}

	/**
	 * Check if the file system is direct.
	 *
	 * @since 3.3.4
	 * @return bool True if the file system is direct, false otherwise.
	 */
	private static function is_direct() {
		return self::get_fs() instanceof \WP_Filesystem_Direct;
	}
}
