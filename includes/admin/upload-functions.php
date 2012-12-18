<?php
/**
 * Upload Functions
 *
 * @package     Easy Digital Downloads
 * @subpackage  Upload Functions
 * @copyright   Copyright (c) 2012, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
*/

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Change Downloads Upload Dir
 *
 * Hooks the edd_set_upload_dir filter when appropiate.
 *
 * @access      private
 * @since       1.0
 * @return      void
*/

function edd_change_downloads_upload_dir() {
    global $pagenow;

    if( !empty( $_REQUEST['post_id'] ) && ( 'async-upload.php' == $pagenow || 'media-upload.php' == $pagenow ) ) {
        if( 'download' == get_post_type( $_REQUEST['post_id'] ) ) {

            $wp_upload_dir = wp_upload_dir();
            $upload_path = $wp_upload_dir['basedir'] . '/edd' . $wp_upload_dir['subdir'];

            // We don't want users snooping in the EDD root, so let's add htacess there, first
            // Creating the directory if it doesn't already exist.
            $rules = 'Options -Indexes';
            if( !@file_get_contents( $wp_upload_dir['basedir'] . '/edd/.htaccess' ) ) {
            	wp_mkdir_p( $wp_upload_dir['basedir'] . '/edd' );
            } // end if
            @file_put_contents( $wp_upload_dir['basedir'] . '/edd/.htaccess', $rules );

            // now add blank index.php files to the {year}/{month} directory
            if ( wp_mkdir_p( $upload_path ) ) {

                if( !file_exists( $upload_path . '/index.php' ) ) {
                    @file_put_contents( $upload_path . '/index.php', '<?php' . PHP_EOL . '// Silence is golden.' );
                }

            }
            add_filter( 'upload_dir', 'edd_set_upload_dir' );
        }
    }
}
add_action( 'admin_init', 'edd_change_downloads_upload_dir', 999 );


/**
 * Set Upload Dir
 *
 * Sets the upload dir to /edd.
 *
 * @access      private
 * @since       1.0
 * @return      array
*/

function edd_set_upload_dir( $upload ) {
	$upload['subdir']	= '/edd' . $upload['subdir'];
	$upload['path'] = $upload['basedir'] . $upload['subdir'];
	$upload['url']	= $upload['baseurl'] . $upload['subdir'];
	return $upload;
}


/**
 * Creates blank index.php and .htaccess files
 *
 * This function runs approximately once per month in order
 * to ensure all folders have their necessary protection files
 *
 * @access      private
 * @since       1.1.5
 * @return      void
*/

function edd_create_protection_files() {
    if( false === get_transient( 'edd_check_protection_files' ) ) {
        $wp_upload_dir = wp_upload_dir();
        $upload_path = $wp_upload_dir['basedir'] . '/edd';

        wp_mkdir_p( $upload_path );

        // top level blank index.php
        if( !file_exists( $upload_path . '/index.php' ) ) {
            @file_put_contents( $upload_path . '/index.php', '<?php' . PHP_EOL . '// Silence is golden.' );
        }

        // top level .htaccess file
        $rules = 'Options -Indexes';
        if ( file_exists( $upload_path . '/.htaccess' ) ) {
            $contents = @file_get_contents( $upload_path . '/.htaccess' );
            if( false === strpos( $contents, 'Options -Indexes' ) || ! $contents ) {
                @file_put_contents( $upload_path . '/.htaccess', $rules );
            }
        }

        // now place index.php files in all sub folders
        $folders = edd_scan_folders( $upload_path );
        foreach( $folders as $folder ) {
            // create index.php, if it doesn't exist
            if( !file_exists( $folder . 'index.php' ) ) {
                @file_put_contents( $folder . 'index.php', '<?php' . PHP_EOL . '// Silence is golden.' );
            }
        }
        // only have this run the first time. This is just to create .htaccess files in existing folders
        set_transient( 'edd_check_protection_files', true, 2678400 );
    }
}
add_action( 'admin_init', 'edd_create_protection_files' );


/**
 * Scans all folders inside of /uploads/edd
 *
 *
 * @access      private
 * @since       1.1.5
 * @return      array
*/

function edd_scan_folders( $path = '', $return = array() ) {
    $path = $path == ''? dirname( __FILE__ ) : $path;
    $lists = @scandir( $path );

    if( !empty( $lists ) ) {
        foreach( $lists as $f ) {

            if( is_dir( $path . DIRECTORY_SEPARATOR . $f ) && $f != "." && $f != ".." ) {
                if( !in_array( $path . DIRECTORY_SEPARATOR . $f, $return ) )
                    $return[] = trailingslashit( $path . DIRECTORY_SEPARATOR . $f );

                edd_scan_folders( $path . DIRECTORY_SEPARATOR . $f, $return);
            }

        }
    }
    return $return;
}
