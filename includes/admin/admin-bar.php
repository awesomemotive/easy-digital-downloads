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

	if ( ! empty( $count ) ) {
		$wp_admin_bar->add_menu( array(
			'parent' => 'edd-store-menu',
			'id'     => 'edd-store-notifications',
			'title'  => __( 'Notifications', 'easy-digital-downloads' ) . ' <div class="wp-core-ui wp-ui-notification edd-menu-notification-indicator"></div>',
			'href'  => edd_get_admin_url(
				array(
					'page'          => 'edd-reports',
					'notifications' => 'true',
				)
			),
		) );
	}

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

	$wp_admin_bar->add_menu( array(
		'parent' => 'edd-store-menu',
		'id'     => 'edd-store-prodcuts',
		/* translators: plural downlaods label */
		'title'  => sprintf( __( 'All %1$s', 'easy-digital-downloads' ), edd_get_label_plural() ),
		'href'   => edd_get_admin_url(),
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
		/* translators: %s: store status ("Test Mode" or "Live Mode") */
		'title'  => sprintf( __( 'Store Status: %s', 'easy-digital-downloads' ), '<span class="edd-mode edd-mode-' . esc_attr( $mode ) . '">' . $text . '</span>' ),
		'href'   => edd_get_admin_url(
			array(
				'page' => 'edd-settings',
				'tab'  => 'gateways',
			)
		),
	) );


	$pass_manager = new \EDD\Admin\Pass_Manager();
	if ( ! $pass_manager->has_pass() ) {
		$url = edd_link_helper(
			'https://easydigitaldownloads.com/lite-upgrade/',
			array(
				'utm_medium'  => 'admin-bar',
				'utm_content' => 'upgrade-to-pro',
			)
		);
		$wp_admin_bar->add_menu( array(
			'parent' => 'edd-store-menu',
			'id'     => 'edd-upgrade',
			'title'  => esc_html__( 'Upgrade to Pro', 'easy-digital-downloads' ),
			'href'   => $url,
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
			/* translators: %s: Whether this is a development domain (1 for true, 0 for false) */
			'title'  => sprintf( __( 'Development Domain %s', 'easy-digital-downloads' ), '<span class="edd-mode">' . $is_dev . '</span>' ),
			'parent' => 'edd-store-menu',
			'href'   => edd_get_admin_url(
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

		#wpadminbar .edd-menu-notification-counter {
			display: inline-block !important;
			min-width: 18px !important;
			height: 18px !important;
			border-radius: 9px !important;
			margin: 7px 0 0 2px !important;
			vertical-align: top !important;
			font-size: 11px !important;
			line-height: 1.6 !important;
			text-align: center !important;
		}

		#wpadminbar .edd-menu-notification-indicator {
			float: right !important;
			margin: 10px 0 0 !important;
			width: 8px !important;
			height: 8px !important;
			border-radius: 4px !important;
		}

		#wpadminbar .edd-menu-notification-indicator:after {
			display: block !important;
			content: "";
			position: absolute !important;
			width: inherit !important;
			height: inherit !important;
			border-radius: inherit !important;
			background-color: inherit !important;
			animation: edd-menu-notification-indicator-pulse 1.5s infinite !important;
		}

		@keyframes edd-menu-notification-indicator-pulse {
			0% {
				transform: scale(1);
				opacity: 1;
			}
			100% {
				transform: scale(3);
				opacity: 0;
			}
		}

		#wpadminbar #wp-admin-bar-edd-upgrade a {
			background-color: #00a32a;
			color: #fff;
			margin-top: 5px;
		}

		#wpadminbar #wp-admin-bar-edd-upgrade a:hover {
			background-color: #008a20;
		}

		#wpadminbar .edd-menu-form-last {
			border-bottom: 1px solid #3c4146 !important;
			margin-bottom: 6px !important;
			padding-bottom: 6px !important;
		}

		<?php if ( ! is_admin() ) : ?>
		#wpadminbar .wp-ui-notification.edd-menu-notification-counter,
		#wpadminbar .wp-ui-notification.edd-menu-notification-indicator {
			color: #fff;
			background-color: #d63638;
		}
		<?php endif; ?>

	</style>

<?php
}
add_action( 'wp_print_styles', 'edd_store_mode_admin_bar_print_link_styles' );
add_action( 'admin_print_styles', 'edd_store_mode_admin_bar_print_link_styles' );
