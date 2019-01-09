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

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Plugins row action links
 *
 * @author Michael Cannon <mc@aihr.us>
 * @since 1.8
 * @param array $links already defined action links
 * @param string $file plugin file path and name being processed
 * @return array $links
 */
function edd_plugin_action_links( $links = array(), $file = '' ) {

	// Only EDD plugin row
	if ( EDD_PLUGIN_BASE === $file ) {
		$settings_url = edd_get_admin_url( array(
			'page' => 'edd-settings'
		) );

		$links['settings'] = '<a href="' . esc_url( $settings_url ) . '">' . esc_html__( 'Settings', 'easy-digital-downloads' ) . '</a>';
	}

	// Return array of links
	return $links;
}
add_filter( 'plugin_action_links', 'edd_plugin_action_links', 10, 2 );


/**
 * Plugin row meta links
 *
 * @author Michael Cannon <mc@aihr.us>
 * @since 1.8
 * @param array $links already defined meta links
 * @param string $file plugin file path and name being processed
 * @return array $input
 */
function edd_plugin_row_meta( $links = array(), $file = '' ) {

	// Only EDD plugin row
	if ( EDD_PLUGIN_BASE === $file ) {
		$extensions_url = add_query_arg( array(
			'utm_source'   => 'plugins-page',
			'utm_medium'   => 'plugin-row',
			'utm_campaign' => 'admin',
		), 'https://easydigitaldownloads.com/downloads/' );

		$links['extensions'] = '<a href="' . esc_url( $extensions_url ) . '">' . esc_html__( 'Extensions', 'easy-digital-downloads' ) . '</a>';
	}

	// Return array of links
	return $links;
}
add_filter( 'plugin_row_meta', 'edd_plugin_row_meta', 10, 2 );
