<?php

/**
 * Checkout Functions
 *
 * @package     Easy Digital Downloads
 * @subpackage  Checkout Functions
 * @copyright   Copyright (c) 2012, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
*/

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;


/**
 * Determines if a user can checkout or not
 *
 * @access      private
 * @since       1.3.3
 * @return      bool
*/

function edd_can_checkout() {

	global $edd_options;

	$can_checkout = true; // always true for now

	return (bool) apply_filters( 'edd_can_checkout', $can_checkout );

}