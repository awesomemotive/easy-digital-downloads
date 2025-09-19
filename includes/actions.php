<?php
/**
 * Front-end Actions
 *
 * @package     EDD
 * @subpackage  Functions
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.8.1
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Hooks EDD actions, when present in the $_GET superglobal. Every edd_action
 * present in $_GET is called using WordPress's do_action function. These
 * functions are called on init.
 *
 * @since 1.0
 * @return void
*/
function edd_get_actions() {
	$key = ! empty( $_GET['edd_action'] ) ? sanitize_key( $_GET['edd_action'] ) : false;

	$is_delayed_action = edd_is_delayed_action( $key );

	if ( $is_delayed_action ) {
		return;
	}

	if ( ! empty( $key ) ) {
		do_action( "edd_{$key}" , $_GET );
	}
}
add_action( 'init', 'edd_get_actions' );

/**
 * Hooks EDD actions, when present in the $_POST superglobal. Every edd_action
 * present in $_POST is called using WordPress's do_action function. These
 * functions are called on init.
 *
 * @since 1.0
 * @return void
*/
function edd_post_actions() {
	$key = ! empty( $_POST['edd_action'] ) ? sanitize_key( $_POST['edd_action'] ) : false;

	$is_delayed_action = edd_is_delayed_action( $key );

	if ( $is_delayed_action ) {
		return;
	}

	if ( ! empty( $key ) ) {
		do_action( "edd_{$key}", $_POST );
	}
}
add_action( 'init', 'edd_post_actions' );

/**
 * Call any actions that should have been delayed, in order to be sure that all necessary information
 * has been loaded by WP Core.
 *
 * Hooks EDD actions, when present in the $_GET superglobal. Every edd_action
 * present in $_POST is called using WordPress's do_action function. These
 * functions are called on template_redirect.
 *
 * @since 2.9.4
 * @return void
 */
function edd_delayed_get_actions() {
	$key = ! empty( $_GET['edd_action'] ) ? sanitize_key( $_GET['edd_action'] ) : false;
	$is_delayed_action = edd_is_delayed_action( $key );

	if ( ! $is_delayed_action ) {
		return;
	}

	if ( ! empty( $key ) ) {
		do_action( "edd_{$key}", $_GET );
	}
}
add_action( 'template_redirect', 'edd_delayed_get_actions' );

/**
 * Call any actions that should have been delayed, in order to be sure that all necessary information
 * has been loaded by WP Core.
 *
 * Hooks EDD actions, when present in the $_POST superglobal. Every edd_action
 * present in $_POST is called using WordPress's do_action function. These
 * functions are called on template_redirect.
 *
 * @since 2.9.4
 * @return void
 */
function edd_delayed_post_actions() {
	$key = ! empty( $_POST['edd_action'] ) ? sanitize_key( $_POST['edd_action'] ) : false;
	$is_delayed_action = edd_is_delayed_action( $key );

	if ( ! $is_delayed_action ) {
		return;
	}

	if ( ! empty( $key ) ) {
		do_action( "edd_{$key}", $_POST );
	}
}
add_action( 'template_redirect', 'edd_delayed_post_actions' );

/**
 * Get the list of actions that EDD has determined need to be delayed past init.
 *
 * @since 2.9.4
 *
 * @return array
 */
function edd_delayed_actions_list() {
	return (array) apply_filters( 'edd_delayed_actions', array(
		'add_to_cart'
	) );
}

/**
 * Determine if the requested action needs to be delayed or not.
 *
 * @since 2.9.4
 *
 * @param string $action
 *
 * @return bool
 */
function edd_is_delayed_action( $action = '' ) {
	return in_array( $action, edd_delayed_actions_list() );
}
