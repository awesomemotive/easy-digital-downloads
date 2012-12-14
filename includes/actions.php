<?php
/**
 * Front-end Actions
 *
 * @package     Easy Digital Downloads
 * @subpackage  Front-end Actions
 * @copyright   Copyright (c) 2012, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.8.1
*/

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Get Actions
 *
 * Hooks EDD actions, when present in $_GET.
 *
 * @access      private
 * @since       1.0
 * @return      void
*/

function edd_cart_get_actions() {
	if( isset( $_GET['edd_action'] ) ) {
		do_action( 'edd_' . $_GET['edd_action'], $_GET );
	}
}
add_action('init', 'edd_cart_get_actions');


/**
 * Post Actions
 *
 * Hooks EDD actions, when present in $_POST.
 *
 * @access      private
 * @since       1.0
 * @return      void
*/

function edd_cart_post_actions() {
	if( isset( $_POST['edd_action'] ) ) {
		do_action( 'edd_' . $_POST['edd_action'], $_POST );
	}
}
add_action('init', 'edd_cart_post_actions');