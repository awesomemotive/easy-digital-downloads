<?php
/**
 * Admin Bar
 *
 * @package     EDD
 * @subpackage  Admin/Bar
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Maybe add the store status to the WordPress admin bar
 *
 * @since 3.0
 */
function edd_maybe_add_store_mode_admin_bar_menu( $wp_admin_bar ) {

	// Bail if no admin bar
	if ( empty( $wp_admin_bar ) ) {
		return;
	}

	// Bail if user cannot manage shop settings
	if ( ! current_user_can( 'manage_shop_settings' ) ) {
		return;
	}

	// String
	$text = ! edd_is_test_mode()
		? __( 'Live',      'easy-digital-downloads' )
		: __( 'Test Mode', 'easy-digital-downloads' );

	// Mode
	$mode = ! edd_is_test_mode()
		? 'live'
		: 'test';

	// Add the menu
    $wp_admin_bar->add_menu( array(
        'id'     => 'edd-store-menu',
        'title'  => sprintf( __( 'Store Status: %s', 'easy-digital-downloads' ), '<span class="edd-mode edd-mode-' . esc_attr( $mode ) . '">' . $text . '</span>' ),
        'parent' => false,
        'href'   => edd_get_admin_url( array(
			'page' => 'edd-settings',
			'tab'  => 'gateways'
		) )
	) );

	// Is development environment?
	$is_dev = edd_is_dev_environment();
	if ( ! empty( $is_dev ) ) {
		$wp_admin_bar->add_menu( array(
			'id'     => 'edd-is-dev',
			'title'  => sprintf( __( 'Development Domain %s', 'easy-digital-downloads' ), '<span class="edd-mode">' . $is_dev . '</span>' ),
			'parent' => 'edd-store-menu',
			'href'   => edd_get_admin_url( array(
				'page' => 'edd-settings',
				'tab'  => 'gateways'
			) )
		) );
	}
}
add_action( 'admin_bar_menu', 'edd_maybe_add_store_mode_admin_bar_menu', 9999 );

/**
 * Styling for text-mode button
 *
 * @since 3.0
 */
function edd_store_mode_admin_bar_print_link_styles() {

	// Bail if user cannot manage shop settings
	if ( ! current_user_can( 'manage_shop_settings' ) ) {
		return;
	} ?>

	<style type="text/css" id="edd-store-menu-styling">
		#wp-admin-bar-edd-store-menu .edd-mode {
			color: #fff;
			background-color: #0073aa;
			padding: 3px 7px;
			font-weight: 600;
			border-radius: 3px;
		}
		#wp-admin-bar-edd-store-menu .edd-mode-live {
			background-color: #32CD32;
		}
		#wp-admin-bar-edd-store-menu .edd-mode-test {
			background-color: #FF8C00;
		}
	</style>

<?php
}
add_action( 'wp_print_styles',    'edd_store_mode_admin_bar_print_link_styles' );
add_action( 'admin_print_styles', 'edd_store_mode_admin_bar_print_link_styles' );
