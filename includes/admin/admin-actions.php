<?php
/**
 * Admin Actions
 *
 * @package     EDD
 * @subpackage  Admin/Actions
 * @copyright   Copyright (c) 2013, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Processes all EDD actions sent via POST and GET by looking for the 'edd-action'
 * request and running do_action() to call the function
 *
 * @since 1.0
 * @return void
 */
function edd_process_actions() {
	if ( isset( $_POST['edd-action'] ) ) {
		do_action( 'edd_' . $_POST['edd-action'], $_POST );
	}

	if ( isset( $_GET['edd-action'] ) ) {
		do_action( 'edd_' . $_GET['edd-action'], $_GET );
	}
}
add_action( 'admin_init', 'edd_process_actions' );