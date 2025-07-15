<?php
/**
 * Stub for the User Switching plugin functions.
 *
 * Used in the EDD\Tests\Compatibility\UserSwitching test class.
 */

if ( ! function_exists( 'switch_to_user' ) ) {
	/**
	 * Stub for the switch_to_user function.
	 *
	 * @param int  $user_id      The ID of the user to switch to.
	 * @param bool $remember     Optional. Whether to 'remember' the user. Default false.
	 * @param bool $set_old_user Optional. Whether to set the old user cookie. Default true.
	 * @return false|WP_User WP_User object on success, false on failure.
	 */
	function switch_to_user( $user_id, $remember = false, $set_old_user = true ) {
		// This is a stub, so we don't need to implement the actual functionality.
		return get_userdata( $user_id );
	}
}

if ( ! function_exists( 'switch_back_user' ) ) {
	/**
	 * Stub for the switch_back_user function.
	 *
	 * @return false|WP_User WP_User object on success, false on failure.
	 */
	function switch_back_user() {
		// This is a stub, so we don't need to implement the actual functionality.
		return true;
	}
}

if ( ! function_exists( 'switch_off_user' ) ) {
	/**
	 * Stub for the switch_off_user function.
	 *
	 * @return bool True on success, false on failure.
	 */
	function switch_off_user() {
		// This is a stub, so we don't need to implement the actual functionality.
		return true;
	}
}
