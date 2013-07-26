<?php
/**
 * Plugin Compatibility
 *
 * Functions for compatibility with other plugins.
 *
 * @package     EDD
 * @subpackage  Functions/Compatibility
 * @copyright   Copyright (c) 2013, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Disables admin sorting of Post Types Order
 *
 * When sorting downloads by price, earnings, sales, date, or name,
 * we need to remove the posts_orderby that Post Types Order imposes
 *
 * @since 1.2.2
 * @return void
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
 * @since 1.3.3.1
 * @return bool
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
 * @since 1.4.1
 * @return bool $caching True if caching plugin is enabled, false otherwise
 */
function edd_is_caching_plugin_active() {
	$caching = ( function_exists( 'wpsupercache_site_admin' ) || defined( 'W3TC' ) );
	return apply_filters( 'edd_is_caching_plugin_active', $caching );
}

/**
 * Adds a ?nocache option for the checkout page
 *
 * This ensures the checkout page remains uncached when plugins like WP Super Cache are activated
 *
 * @since 1.4.1
 * @param array $settings Misc Settings
 * @return array $settings Updated Misc Settings
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

/**
 * Show the correct language on the [downloads] short code if qTranslate is active
  *
 * @since 1.7
 * @param string $content Download content
 * @return string $content Download content
 */
function edd_qtranslate_content( $content ) {
	if( defined( 'QT_LANGUAGE' ) )
		$content = qtrans_useCurrentLanguageIfNotFoundShowAvailable( $content );
	return $content;
}
add_filter( 'edd_downloads_content', 'edd_qtranslate_content' );
add_filter( 'edd_downloads_excerpt', 'edd_qtranslate_content' );