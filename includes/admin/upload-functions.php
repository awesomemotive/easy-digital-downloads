<?php
/**
 * Upload Functions
 *
 * @package     EDD
 * @subpackage  Admin/Upload
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

/**
 * Change Downloads Upload Directory
 *
 * Hooks the edd_set_upload_dir filter when appropriate. This function works by
 * hooking on the WordPress Media Uploader and moving the uploading files that
 * are used for EDD to an edd directory under wp-content/uploads/ therefore,
 * the new directory is wp-content/uploads/edd/{year}/{month}. This directory is
 * provides protection to anything uploaded to it.
 *
 * @since 1.0
 * @since 3.2.10 The function hooks into the wp_handle_upload_prefilter action.
 * @param array $file
 * @return void
 */
function edd_change_downloads_upload_dir( $file = array() ) {
	if ( empty( $_REQUEST['post_id'] ) ) {
		return $file;
	}
	if ( 'download' === get_post_type( $_REQUEST['post_id'] ) ) {
		delete_transient( 'edd_check_protection_files' );
		add_filter( 'upload_dir', 'edd_set_upload_dir' );
	}

	return $file;
}
add_filter( 'wp_handle_upload_prefilter', 'edd_change_downloads_upload_dir', 5 );

/**
 * Creates blank index.php and .htaccess files
 *
 * This function runs approximately once per day in order to ensure all folders
 * have their necessary protection files
 *
 * @since 1.1.5
 *
 * @param bool $force  Whether to force the creation of the protection files.
 * @param bool $method The method used to download files.
 */
function edd_create_protection_files( $force = false, $method = false ) {
	$file_system = EDD\Utils\FileSystem::get_fs();
	if ( false === get_transient( 'edd_check_protection_files' ) || $force ) {

		$upload_path = edd_get_upload_dir();

		// Check if the main upload path is writable.
		$upload_path_writeable = wp_is_writable( $upload_path );

		// Top level .htaccess file.
		$rules = edd_get_htaccess_rules( $method );
		if ( edd_htaccess_exists() ) {
			$contents = $file_system->get_contents( $upload_path . '/.htaccess' );
			if ( $contents !== $rules || ! $contents ) {
				// Update the .htaccess rules if they don't match.
				$file_system->put_contents( $upload_path . '/.htaccess', $rules );
			}
		} elseif ( $upload_path_writeable ) {
			// Create the file if it doesn't exist.
			$file_system->put_contents( $upload_path . '/.htaccess', $rules );
		}

		// Top level blank index.php.
		if ( $upload_path_writeable && ! file_exists( $upload_path . '/index.php' ) ) {
			$file_system->put_contents( $upload_path . '/index.php', '<?php' . PHP_EOL . '// Silence is golden.' );
		}

		if ( $upload_path_writeable && ! file_exists( $upload_path . '/index.html' ) ) {
			$file_system->put_contents( $upload_path . '/index.html', '' );
		}

		// Now place index.php files in all sub folders.
		$folders = edd_scan_folders( $upload_path );
		foreach ( $folders as $folder ) {
			// Continue if the folder is not writable.
			if ( ! wp_is_writable( $folder ) ) {
				continue;
			}

			// Create index.php, if it doesn't exist.
			if ( ! file_exists( $folder . 'index.php' ) ) {
				$file_system->put_contents( $folder . 'index.php', '<?php' . PHP_EOL . '// Silence is golden.' );
			}

			if ( ! file_exists( $folder . 'index.html' ) ) {
				$file_system->put_contents( $folder . 'index.html', '' );
			}
		}

		// Check for the files once per day.
		set_transient( 'edd_check_protection_files', true, DAY_IN_SECONDS );
	}
}
add_action( 'admin_init', 'edd_create_protection_files' );

/**
 * Checks if the .htaccess file exists in wp-content/uploads/edd
 *
 * @since 1.8
 * @return bool
 */
function edd_htaccess_exists() {
	$upload_path = edd_get_upload_dir();

	return file_exists( $upload_path . '/.htaccess' );
}

/**
 * Scans all folders inside of /uploads/edd
 *
 * @since 1.1.5
 * @since 3.2.10 Switched to using glob() for better performance and accuracy.
 *
 * @param string $path   Path to scan.
 * @param array  $return Results of previous recursion (Deprecated in 3.2.10).
 *
 * @return array $return List of files inside directory
 */
function edd_scan_folders( $path = '', $return = array() ) {
	$path = ! empty( $path ) ? $path : __DIR__;

	// Get the main directories in the root of the directory we're scanning.
	$upload_root_dirs = glob( $path . '/*', GLOB_ONLYDIR | GLOB_NOSORT | GLOB_MARK );

	// Now get all the recursive directories.
	$upload_sub_dirs = glob( $path . '/*/**', GLOB_ONLYDIR | GLOB_NOSORT | GLOB_MARK );

	// Merge the two arrays together, and avoid any possible duplicates.
	return array_unique( array_merge( $upload_root_dirs, $upload_sub_dirs ) );
}

/**
 * Retrieve the .htaccess rules to wp-content/uploads/edd/
 *
 * @since 1.6
 *
 * @param bool $method
 * @return mixed|void The htaccess rules
 */
function edd_get_htaccess_rules( $method = false ) {

	if ( empty( $method ) ) {
		$method = edd_get_file_download_method();
	}

	switch ( $method ) {

		case 'redirect':
			// Prevent directory browsing.
			$rules = 'Options -Indexes';
			break;

		case 'direct':
		default:
			// Prevent directory browsing and direct access to all files, except images (they must be allowed for featured images / thumbnails).
			$allowed_filetypes = apply_filters( 'edd_protected_directory_allowed_filetypes', array( 'jpg', 'jpeg', 'png', 'gif', 'mp3', 'ogg', 'webp' ) );
			$rules             = "Options -Indexes\n";
			$rules            .= "deny from all\n";
			$rules            .= "<FilesMatch '\.(" . implode( '|', $allowed_filetypes ) . ")$'>\n";
				$rules        .= "Order Allow,Deny\n";
				$rules        .= "Allow from all\n";
			$rules            .= "</FilesMatch>\n";
			break;
	}

	/**
	 * Filter and return the htaccess rules used to allow or deny access
	 *
	 * @since 1.6
	 *
	 * @param string $rules  The contents of .htaccess
	 * @param string $method The method (either direct|redirect)
	 */
	return apply_filters( 'edd_protected_directory_htaccess_rules', $rules, $method );
}
