<?php
/**
 * Accounting Functions
 *
 * @package		EDD
 * @subpackage	Functions
 * @copyright	Copyright (c) 2013, Pippin Williamson
 * @license		http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since		1.6
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Retrieves a download SKU by ID.
 *
 * @since 1.6
 * @param int $download Download ID
 * @return string|int $sku Download SKU
 */
function edd_get_download_sku( $download = 0 ) {
	$sku = get_post_meta( $download, 'edd_sku', true );
	if ( $sku ) {
		return $sku;
	} else {
		return '-';
	}
}
