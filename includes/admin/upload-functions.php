<?php
/**
 * Upload Functions
 *
 * @package     EDD
 * @subpackage  Admin/Upload
 * @copyright   Copyright (c) 2013, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Set Upload Directory
 *
 * Sets the upload dir to edd. This function is called from
 * edd_change_downloads_upload_dir()
 *
 * @since 1.0
 * @return array Upload directory information
*/
function edd_set_upload_dir( $upload ) {

	// Override the year / month being based on the post publication date, if year/month organization is enabled
	if ( get_option( 'uploads_use_yearmonth_folders' ) ) {
		// Generate the yearly and monthly dirs
		$time = current_time( 'mysql' );
		$y = substr( $time, 0, 4 );
		$m = substr( $time, 5, 2 );
		$upload['subdir'] = "/$y/$m";
	}

	$upload['subdir'] = '/edd' . $upload['subdir'];
	$upload['path']   = $upload['basedir'] . $upload['subdir'];
	$upload['url']	  = $upload['baseurl'] . $upload['subdir'];
	return $upload;
}


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
 * @return void
 */
function edd_create_protection_files( $force = false, $method = false ) {
	if ( false === get_transient( 'edd_check_protection_files' ) || $force ) {
		$wp_upload_dir = wp_upload_dir();
		$upload_path = $wp_upload_dir['basedir'] . '/edd';

		wp_mkdir_p( $upload_path );

		// Top level blank index.php
		if ( ! file_exists( $upload_path . '/index.php' ) ) {
			@file_put_contents( $upload_path . '/index.php', '<?php' . PHP_EOL . '// Silence is golden.' );
		}

		// Top level .htaccess file
		$rules = edd_get_htaccess_rules( $method );
		if ( file_exists( $upload_path . '/.htaccess' ) ) {
			$contents = @file_get_contents( $upload_path . '/.htaccess' );
			if ( $contents !== $rules || ! $contents ) {
				@file_put_contents( $upload_path . '/.htaccess', $rules );
			}
		}

		// Now place index.php files in all sub folders
		$folders = edd_scan_folders( $upload_path );
		foreach ( $folders as $folder ) {
			// Create index.php, if it doesn't exist
			if ( ! file_exists( $folder . 'index.php' ) ) {
				@file_put_contents( $folder . 'index.php', '<?php' . PHP_EOL . '// Silence is golden.' );
			}
		}
		// Check for the files once per day
		set_transient( 'edd_check_protection_files', true, 3600 * 24 );
	}
}
add_action( 'admin_init', 'edd_create_protection_files' );

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
 * @return string The htaccess rules
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
			$rules = "Options -Indexes\n";
			$rules .= "deny from all\n";
			$rules .= "<FilesMatch '\.(jpg|png|gif)$'>\n";
			    $rules .= "Order Allow,Deny\n";
			    $rules .= "Allow from all\n";
			$rules .= "</FilesMatch>\n";
			break;

	endswitch;
	$rules = apply_filters( 'edd_protected_directory_htaccess_rules', $rules );
	return $rules;
}