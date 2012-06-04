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

    if ( ! empty( $_POST['post_id'] ) && ( 'async-upload.php' == $pagenow || 'media-upload.php' == $pagenow ) ) {
        if ( 'download' == get_post_type( $_REQUEST['post_id'] ) ) {
            $wp_upload_dir = wp_upload_dir();
            $upload_path = $wp_upload_dir['basedir'] . '/edd' . $wp_upload_dir['subdir'];
            if (wp_mkdir_p($upload_path) && !file_exists($upload_path.'/.htaccess')) {
                if ($file_handle = @fopen( $upload_path . '/.htaccess', 'w' )) {
                    fwrite($file_handle, 'Options All -Indexes');
                    fclose($file_handle);
                }

            }
            add_filter( 'upload_dir', 'edd_set_upload_dir' );
        }
    }
}
add_action('admin_init', 'edd_change_downloads_upload_dir', 999);


/**
 * Set Upload Dir
 *
 * Sets the upload dir to /edd.
 *
 * @access      private
 * @since       1.0 
 * @return      array
*/

function edd_set_upload_dir($upload) {
	$upload['subdir']	= '/edd' . $upload['subdir'];
	$upload['path'] = $upload['basedir'] . $upload['subdir'];
	$upload['url']	= $upload['baseurl'] . $upload['subdir'];
	return $upload;
}