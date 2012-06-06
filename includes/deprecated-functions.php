<?php
/**
 * Deprecated Functions
 *
 * All functions that have been deprecated.
 *
 * @package     Easy Digital Downloads
 * @subpackage  Deprecated Functions
 * @copyright   Copyright (c) 2012, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0 
*/




/**
 * AJAX enabled - deprecated
 *
 * Checks whether AJAX is enabled.
 *
 * @access      private
 * @since       1.0
 * @deprecated  1.0.8.3
 * @return      boolean
*/

function edd_is_ajax_enabled() {
	global $edd_options;
	if(isset($edd_options['ajax_cart'])) {
		return true;
	}
	return false;
}