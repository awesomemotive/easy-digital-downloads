<?php

function edd_allowed_mime_types( $existing_mimes ) {
 
	$existing_mimes['zip'] = 'application/zip';
 	return $existing_mimes;
 
}
add_filter('upload_mimes', 'edd_allowed_mime_types');