<?php
 
function edd_change_downloads_upload_dir() {
    global $pagenow;

    if ( ! empty( $_POST['post_id'] ) && ( 'async-upload.php' == $pagenow || 'media-upload.php' == $pagenow ) ) {
        if ( 'download' == get_post_type( $_REQUEST['post_id'] ) ) {
            add_filter( 'upload_dir', 'edd_set_upload_dir' );
        }
    }
}
add_action('admin_init', 'edd_change_downloads_upload_dir', 999);

function edd_set_upload_dir($upload) {
	$upload['subdir']	= '/edd' . $upload['subdir'];
	$upload['path'] = $upload['basedir'] . $upload['subdir'];
	$upload['url']	= $upload['baseurl'] . $upload['subdir'];
	return $upload;
}

?>