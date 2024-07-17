<?php

defined( 'ABSPATH' ) || exit;

/**
 * Legacy `EDD_Auto_Register` class was refactored and moved to the new `EDD\Checkout\AutoRegister` class.
 * This alias is a safeguard to those developers who use our internal class EDD_Auto_Register,
 * which we deleted.
 *
 * @since 3.3.0
 */
class_alias( \EDD\Checkout\AutoRegister::class, 'EDD_Auto_Register' );

if ( ! function_exists( 'edd_auto_register' ) ) {
	/**
	 * Polyfill for edd_auto_register() function.
	 * Some extensions may use this function to check if auto registration is enabled.
	 *
	 * @since 3.3.0
	 * @return bool
	 */
	function edd_auto_register() {
		return \EDD\Checkout\AutoRegister::get_instance();
	}
}
