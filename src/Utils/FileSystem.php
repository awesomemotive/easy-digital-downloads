<?php

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
		if ( self::get_fs()->exists( $uploads_file ) ) {
			self::get_fs()->move( $uploads_file, $file );
		}
	}
}
