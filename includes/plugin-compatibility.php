<?php
/**
 * Plugin Compatibility
 *
 * Functions for compatibility with other plugins.
 *
 * @package     Easy Digital Downloads
 * @subpackage  Gateway Functions
 * @copyright   Copyright (c) 2013, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Remove Restrict Meta Box
 *
 * Removes the "Restrict This Content" meta box from Restrict Content Pro.
 *
 * @access      private
 * @since       1.0
 * @return      array
 */
function edd_remove_restrict_meta_box( $post_types ) {
	$post_types[] = 'download';

	return $post_types;
}
add_filter( 'rcp_metabox_excluded_post_types', 'edd_remove_restrict_meta_box', 999 );

/**
 * Disables admin sorting of Post Types Order
 *
 * When sorting downloads by price, earnings, sales, date, or name,
 * we need to remove the posts_orderby that Post Types Order imposes
 *
 * @access      private
 * @since       1.2.2
 * @return      void
 */
function edd_remove_post_types_order() {
	remove_filter( 'posts_orderby', 'CPTOrderPosts' );
}
add_action( 'load-edit.php', 'edd_remove_post_types_order' );

/**
 * Disables opengraph tags on the checkout page
 *
 * There is a bizarre conflict that makes the checkout errors not get displayed
 * when the Jetpack opengraph tags are displayed
 *
 * @access      private
 * @since       1.3.3.1
 * @return      bool
 */
function edd_disable_jetpack_og_on_checkout() {
	if ( edd_is_checkout() ) {
		remove_action( 'wp_head', 'jetpack_og_tags' );
	}
}
add_action( 'template_redirect', 'edd_disable_jetpack_og_on_checkout' );

/**
 * Checks if a caching plugin is active
 *
 * @access      private
 * @since       1.4.1
 * @return      bool
 */
function edd_is_caching_plugin_active() {
	$caching = ( function_exists( 'wpsupercache_site_admin' ) || defined( 'W3TC_IN_MINIFY' ) );
	return apply_filters( 'edd_is_caching_plugin_active', $caching );
}

/**
 * Adds a ?nocache option for the checkout page
 *
 * This ensures the checkout page remains uncached when plugins like WP Super Cache are activated
 *
 * @access      private
 * @since       1.4.1
 * @return      array
 */
function edd_append_no_cache_param( $settings ) {
	if ( ! edd_is_caching_plugin_active() )
		return $settings;

	$settings[] = array(
		'id' => 'no_cache_checkout',
		'name' => __('No Caching on Checkout?', 'edd'),
		'desc' => __('Check this box in order to append a ?nocache parameter to the checkout URL to prevent caching plugins from caching the page.', 'edd'),
		'type' => 'checkbox'
	);

	return $settings;
}
add_filter( 'edd_settings_misc', 'edd_append_no_cache_param', -1 );