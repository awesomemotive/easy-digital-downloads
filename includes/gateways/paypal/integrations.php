<?php
/**
 * PayPal Commerce Integrations
 *
 * @package    easy-digital-downloads
 * @subpackage Gateways\PayPal
 * @copyright  Copyright (c) 2022, Easy Digital Downloads
 * @license    GPL2+
 * @since      3.0
 */

namespace EDD\Gateways\PayPal;

/**
 * Tells Auto Register to log the user in when the PayPal Commerce action is detected.
 * Added slightly early to not override anything more specific.
 *
 * @since 3.0
 * @param bool $should_login Whether the new user shold be automatically logged in.
 * @return bool
 */
function auto_register( $should_login ) {
	return isset( $_POST['action'] ) && 'edd_capture_paypal_order' === $_POST['action'] ? true : $should_login;
}
add_filter( 'edd_auto_register_login_user', __NAMESPACE__ . '\auto_register', 5 );
