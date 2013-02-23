<?php
/**
 * Mime Types
 *
 * @package     Easy Digital Downloads
 * @subpackage  Mime Types
 * @copyright   Copyright (c) 2013, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Allowed Mime Types
 *
 * @access      public
 * @param       array $$existing_mimes A list of all the existing MIME types
 * @since       1.0
 * @return      array
 */
function edd_allowed_mime_types( $existing_mimes ) {
	$existing_mimes['zip']  = 'application/zip';
	$existing_mimes['epub'] = 'application/epub+zip';
	$existing_mimes['mobi'] = 'application/x-mobipocket-ebook';
	$existing_mimes['m4r']  = 'audio/aac';
	$existing_mimes['psd']  = 'image/photoshop';
	$existing_mimes['exe']  = 'application/octet-stream';
	$existing_mimes['apk']  = 'application/vnd.android.package-archive';
	$existing_mimes['msi']  = 'application/x-ole-storage';

	return $existing_mimes;
}
add_filter( 'upload_mimes', 'edd_allowed_mime_types' );