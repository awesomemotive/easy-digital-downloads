<?php
/**
 * Mime Types
 *
 * @package     Easy Digital Downloads
 * @subpackage  Mime Types
 * @copyright   Copyright (c) 2012, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Allowed Mime Types
 *
 * @access      public
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
	return $existing_mimes;

}
add_filter( 'upload_mimes', 'edd_allowed_mime_types' );
