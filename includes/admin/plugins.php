<?php
/**
 * Admin Plugins
 *
 * @package     EDD
 * @subpackage  Admin/Plugins
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.8
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Plugins row action links
 *
 * @since 1.8
 * @since 3.1 Targeted just our plugin.
 *
 * @param array $links Already defined action links.
 *
 * @return array $links
 */
function edd_plugin_action_links( $links = array() ) {
	$edd_links    = array();
	$pass_manager = new \EDD\Admin\Pass_Manager();

	if ( ! $pass_manager->has_pass() ) {

		$url = edd_link_helper(
			'https://easydigitaldownloads.com/lite-upgrade/',
			array(
				'utm_medium'  => 'all-plugins',
				'utm_content' => 'upgrade-to-pro',
			)
		);

		$edd_links['edd-pro-upgrade'] = sprintf( '<a href="%s" target="_blank">' . __( 'Upgrade to Pro', 'easy-digital-downloads' ) . '</a>', $url );
	}

	$settings_url = edd_get_admin_url(
		array(
			'page' => 'edd-settings',
		)
	);

	$edd_links['settings'] = '<a href="' . esc_url( $settings_url ) . '">' . esc_html__( 'Settings', 'easy-digital-downloads' ) . '</a>';

	// Return array of links.
	return array_merge( $edd_links, $links );
}
add_filter( 'plugin_action_links_easy-digital-downloads/easy-digital-downloads.php', 'edd_plugin_action_links', 10, 2 );

/**
 * Load any CSS we need for the plugins list table.
 */
function edd_plugin_list_styles() {
	echo '<style>.edd-pro-upgrade a, .edd-pro-upgrade a:hover{color: #1da867;font-weight: 600;}</style>';
}
add_action( 'admin_print_styles-plugins.php', 'edd_plugin_list_styles' );
