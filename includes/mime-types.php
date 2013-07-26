<?php
/**
 * Mime Types
 *
 * @package     EDD
 * @subpackage  Functions
 * @copyright   Copyright (c) 2013, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Allowed Mime Types
 *
 * @since 1.0
 * @param array $$existing_mimes A list of all the existing MIME types
 * @return array $$existing_mimes A list of all the new MIME types appended
 */
function edd_allowed_mime_types( $existing_mimes ) {
	$existing_mimes['zip']  = 'application/zip';
	$existing_mimes['epub'] = 'application/epub+zip';
	$existing_mimes['mobi'] = 'application/x-mobipocket-ebook';
	$existing_mimes['m4r']  = 'audio/aac';
	$existing_mimes['aif']  = 'audio/x-aiff';
	$existing_mimes['aiff'] = 'audio/aiff';
	$existing_mimes['psd']  = 'image/photoshop';
	$existing_mimes['exe']  = 'application/octet-stream';
	$existing_mimes['apk']  = 'application/vnd.android.package-archive';
	$existing_mimes['msi']  = 'application/x-ole-storage';

	return $existing_mimes;
}
add_filter( 'upload_mimes', 'edd_allowed_mime_types' );