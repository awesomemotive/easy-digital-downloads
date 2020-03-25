<?php
/**
 * Upload Functions
 *
 * @package     EDD
 * @subpackage  Admin/Upload
 * @copyright   Copyright (c) 2015, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;


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
 * @global $pagenow
 * @return void
 */
function edd_change_downloads_upload_dir() {
	global $pagenow;

	if ( ! empty( $_REQUEST['post_id'] ) && ( 'async-upload.php' == $pagenow || 'media-upload.php' == $pagenow ) ) {
		if ( 'download' == get_post_type( $_REQUEST['post_id'] ) ) {
			edd_create_protection_files( true );
			add_filter( 'upload_dir', 'edd_set_upload_dir' );
		}
	}
}
add_action( 'admin_init', 'edd_change_downloads_upload_dir', 999 );


/**
 * Creates blank index.php and .htaccess files
 *
 * This function runs approximately once per month in order to ensure all folders
 * have their necessary protection files
 *
 * @since 1.1.5
 *
 * @param bool $force
 * @param bool $method
 */

function edd_create_protection_files( $force = false, $method = false ) {
	if ( false === get_transient( 'edd_check_protection_files' ) || $force ) {

		$upload_path = edd_get_upload_dir();

		// Make sure the /edd folder is created
		wp_mkdir_p( $upload_path );

		// Top level .htaccess file
		$rules = edd_get_htaccess_rules( $method );
		if ( edd_htaccess_exists() ) {
			$contents = @file_get_contents( $upload_path . '/.htaccess' );
			if ( $contents !== $rules || ! $contents ) {
				// Update the .htaccess rules if they don't match
				@file_put_contents( $upload_path . '/.htaccess', $rules );
			}
		} elseif( wp_is_writable( $upload_path ) ) {
			// Create the file if it doesn't exist
			@file_put_contents( $upload_path . '/.htaccess', $rules );
		}

		// Top level blank index.php
		if ( ! file_exists( $upload_path . '/index.php' ) && wp_is_writable( $upload_path ) ) {
			@file_put_contents( $upload_path . '/index.php', '<?php' . PHP_EOL . '// Silence is golden.' );
		}

		// Now place index.php files in all sub folders
		$folders = edd_scan_folders( $upload_path );
		foreach ( $folders as $folder ) {
			// Create index.php, if it doesn't exist
			if ( ! file_exists( $folder . 'index.php' ) && wp_is_writable( $folder ) ) {
				@file_put_contents( $folder . 'index.php', '<?php' . PHP_EOL . '// Silence is golden.' );
			}
		}
		// Check for the files once per day
		set_transient( 'edd_check_protection_files', true, 3600 * 24 );
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
 * @return array $return List of files inside directory
 */
function edd_scan_folders( $path = '', $return = array() ) {
	$path = $path == ''? dirname( __FILE__ ) : $path;
	$lists = @scandir( $path );

	if ( ! empty( $lists ) ) {
		foreach ( $lists as $f ) {
			if ( is_dir( $path . DIRECTORY_SEPARATOR . $f ) && $f != "." && $f != ".." ) {
				if ( ! in_array( $path . DIRECTORY_SEPARATOR . $f, $return ) )
					$return[] = trailingslashit( $path . DIRECTORY_SEPARATOR . $f );

				edd_scan_folders( $path . DIRECTORY_SEPARATOR . $f, $return);
			}
		}
	}

	return $return;
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

	if( empty( $method ) )
		$method = edd_get_file_download_method();

	switch( $method ) :

		case 'redirect' :
			// Prevent directory browsing
			$rules = "Options -Indexes";
			break;

		case 'direct' :
		default :
			// Prevent directory browsing and direct access to all files, except images (they must be allowed for featured images / thumbnails)
			$allowed_filetypes = apply_filters( 'edd_protected_directory_allowed_filetypes', array( 'jpg', 'jpeg', 'png', 'gif', 'mp3', 'ogg' ) );
			$rules = "Options -Indexes\n";
			$rules .= "deny from all\n";
			$rules .= "<FilesMatch '\.(" . implode( '|', $allowed_filetypes ) . ")$'>\n";
			    $rules .= "Order Allow,Deny\n";
			    $rules .= "Allow from all\n";
			$rules .= "</FilesMatch>\n";
			break;

	endswitch;
	$rules = apply_filters( 'edd_protected_directory_htaccess_rules', $rules, $method );
	return $rules;
}
