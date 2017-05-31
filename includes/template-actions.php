<?php
/**
 * Manage actions and callbacks related to templates.
 *
 * @since 2.9.0
 *
 * @package EDD
 * @category Template
 * @author Easy Digital Downloads
 */

/**
 * Output a message and login form on the profile editor when the
 * current visitor is not logged in.
 *
 * @since 2.9.0
 */
function edd_profile_editor_logged_out() {
	echo '<p>' . esc_html_e( 'You need to login to edit your profile.', 'easy-digital-downloads' ) . '</p>';
	echo edd_login_form(); // WPCS: XSS ok.
}
add_action( 'edd_profile_editor_logged_out', 'edd_profile_editor_logged_out' );

/**
 * Output a message on the login form when a user is already logged in.
 *
 * This remains mainly for backwards compatibility.
 *
 * @since 2.9.0
 */
function edd_login_form_logged_in() {
	echo '<p class="edd-logged-in">' . esc_html_e( 'You are already logged in', 'easy-digital-downloads' ) . '</p>';
}
add_action( 'edd_login_form_logged_in', 'edd_login_form_logged_in' );
