<?php
/**
 * Manage actions and callbacks related to templates.
 *
 * @package     EDD
 * @subpackage  Templates
 * @copyright   Copyright (c) 2017, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       2.8
 */

/**
 * Output a message and login form on the profile editor when the
 * current visitor is not logged in.
 *
 * @since 2.8
 */
function edd_profile_editor_logged_out() {
	echo '<p class="edd-logged-out">' . esc_html__( 'You need to log in to edit your profile.', 'easy-digital-downloads' ) . '</p>';
	echo edd_login_form(); // WPCS: XSS ok.
}
add_action( 'edd_profile_editor_logged_out', 'edd_profile_editor_logged_out' );

/**
 * Output a message on the login form when a user is already logged in.
 *
 * This remains mainly for backwards compatibility.
 *
 * @since 2.8
 */
function edd_login_form_logged_in() {
	echo '<p class="edd-logged-in">' . esc_html__( 'You are already logged in', 'easy-digital-downloads' ) . '</p>';
}
add_action( 'edd_login_form_logged_in', 'edd_login_form_logged_in' );