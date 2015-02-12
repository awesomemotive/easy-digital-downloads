<?php
/**
 * Admin Plugins
 *
 * @package     EDD
 * @subpackage  Admin/Plugins
 * @copyright   Copyright (c) 2015, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.8
 */


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Plugins row action links
 *
 * @author Michael Cannon <mc@aihr.us>
 * @since 1.8
 * @param array $links already defined action links
 * @param string $file plugin file path and name being processed
 * @return array $links
 */
function edd_plugin_action_links( $links, $file ) {
	$settings_link = '<a href="' . admin_url( 'edit.php?post_type=download&page=edd-settings' ) . '">' . esc_html__( 'General Settings', 'edd' ) . '</a>';
	if ( $file == 'easy-digital-downloads/easy-digital-downloads.php' )
		array_unshift( $links, $settings_link );

	return $links;
}
add_filter( 'plugin_action_links', 'edd_plugin_action_links', 10, 2 );


/**
 * Plugin row meta links
 *
 * @author Michael Cannon <mc@aihr.us>
 * @since 1.8
 * @param array $input already defined meta links
 * @param string $file plugin file path and name being processed
 * @return array $input
 */
function edd_plugin_row_meta( $input, $file ) {
	if ( $file != 'easy-digital-downloads/easy-digital-downloads.php' )
		return $input;

	$links = array(
		'<a href="' . admin_url( 'index.php?page=edd-getting-started' ) . '">' . esc_html__( 'Getting Started', 'edd' ) . '</a>',
		'<a href="https://easydigitaldownloads.com/extensions/">' . esc_html__( 'Add Ons', 'edd' ) . '</a>',
	);

	$input = array_merge( $input, $links );

	return $input;
}
add_filter( 'plugin_row_meta', 'edd_plugin_row_meta', 10, 2 );