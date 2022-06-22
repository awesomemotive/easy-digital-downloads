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

	// Bail if no admin bar.
	if ( empty( $wp_admin_bar ) ) {
		return;
	}

	// Bail if user cannot manage shop settings.
	if ( ! current_user_can( 'manage_shop_settings' ) ) {
		return;
	}

	$indicator     = '';
	$notifications = EDD()->notifications->countActiveNotifications();

	if ( $notifications ) {
		$count     = $notifications < 10 ? $notifications : '!';
		$indicator = ' <div class="wp-core-ui wp-ui-notification edd-menu-notification-counter">' . $count . '</div>';
	}

	// Add the menu
	$wp_admin_bar->add_menu(
		array(
			'id'    => 'edd-store-menu',
			'title' => 'EDD' . $indicator,
			'href'  => edd_get_admin_url(
				array(
					'page' => 'edd-reports',
				)
			),
		)
	);

	$wp_admin_bar->add_menu( array(
		'parent' => 'edd-store-menu',
		'id'     => 'edd-store-reports',
		'title'  => __( 'Store Reports', 'easy-digital-downloads' ),
		'href'  => edd_get_admin_url(
			array(
				'page' => 'edd-reports',
			)
		),
	) );

	$wp_admin_bar->add_menu( array(
		'parent' => 'edd-store-menu',
		'id'     => 'edd-store-settings',
		'title'  => __( 'Store Settings', 'easy-digital-downloads' ),
		'href'  => edd_get_admin_url(
			array(
				'page' => 'edd-settings',
			)
		),
	) );

	// String.
	$text = ! edd_is_test_mode()
		? __( 'Live',      'easy-digital-downloads' )
		: __( 'Test Mode', 'easy-digital-downloads' );

	// Mode.
	$mode = ! edd_is_test_mode()
		? 'live'
		: 'test';

	$wp_admin_bar->add_menu( array(
		'parent' => 'edd-store-menu',
		'id'     => 'edd-store-status',
		'title'  => sprintf( __( 'Store Status: %s', 'easy-digital-downloads' ), '<span class="edd-mode edd-mode-' . esc_attr( $mode ) . '">' . $text . '</span>' ),
		'href'  => edd_get_admin_url(
			array(
				'page' => 'edd-settings',
				'tab'  => 'gateways',
			)
		),
	) );

	$wp_admin_bar->add_menu( array(
		'parent' => 'edd-store-menu',
		'id'     => 'edd-store-prodcuts',
		'title'  => sprintf( __( 'All %1$s', 'easy-digital-downloads' ), edd_get_label_plural() ),
		'href'  => edd_get_admin_url(),
	) );

	$wp_admin_bar->add_menu( array(
		'parent' => 'edd-store-menu',
		'id'     => 'edd-store-extensions',
		'title'  => __( 'Extensions', 'easy-digital-downloads' ),
		'href'  => edd_get_admin_url(
			array(
				'page' => 'edd-addons',
			)
		),
	) );

	$pass_manager = new \EDD\Admin\Pass_Manager();
	if ( false === $pass_manager->has_pass() ) {
		$wp_admin_bar->add_menu( array(
			'parent' => 'edd-store-menu',
			'id'     => 'edd-upgrade',
			'title'  => esc_html__( 'Upgrade to Pro', 'easy-digital-downloads' ),
			'href'   => 'https://easydigitaldownloads.com/pricing/?utm_campaign=admin&utm_medium=admin-bar&utm_source=WordPress&utm_content=Upgrade+to+Pro',
			'meta'   => array(
				'target' => '_blank',
				'rel'    => 'noopener noreferrer',
			),
		) );
	}

	// Is development environment?
	$is_dev = edd_is_dev_environment();
	if ( ! empty( $is_dev ) ) {
		$wp_admin_bar->add_menu( array(
			'id'     => 'edd-is-dev',
			'title'  => sprintf( __( 'Development Domain %s', 'easy-digital-downloads' ), '<span class="edd-mode">' . $is_dev . '</span>' ),
			'parent' => 'edd-store-menu',
			'href'  => edd_get_admin_url(
				array(
					'page' => 'edd-settings',
					'tab'  => 'gateways',
				)
			),
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
		#wp-admin-bar-edd-store-status .edd-mode {
			line-height: inherit;
		}
		#wp-admin-bar-edd-store-status .edd-mode-live {
			color: #32CD32;
		}
		#wp-admin-bar-edd-store-menu .edd-mode-test {
			color: #FF8C00;
		}
	</style>

<?php
}
add_action( 'wp_print_styles',    'edd_store_mode_admin_bar_print_link_styles' );
add_action( 'admin_print_styles', 'edd_store_mode_admin_bar_print_link_styles' );
