<?php
/**
 * Licensing Functions
 *
 * Functions used for saving and/or retrieving information about EDD licensed products
 * running on the site.
 *
 * @package   easy-digital-downloads
 * @copyright Copyright (c) 2021, Easy Digital Downloads
 * @license   GPL2+
 * @since     2.11.4
 */

namespace EDD\Extensions;

/**
 * Saves an option in the database with the information from the `$edd_licensed_products`
 * global. This only runs in the context of WP-Admin, because that's the only place we
 * can be certain that the information is accurate.
 *
 * @link https://github.com/awesomemotive/easy-digital-downloads/issues/8969
 *
 * @global $edd_licensed_products
 */
add_action( 'admin_init', function () {
	if ( ! is_admin() ) {
		return;
	}

	$saved_products = get_option( 'edd_licensed_extensions', array() );

	/*
	 * We only want to update this option once per day. If the timeout has expired
	 * then update it with product information.
	 *
	 * Note: We always save this option, even if `$edd_licensed_products` is empty.
	 * This is to help designate that the information has been checked and saved,
	 * even if there are no licensed products.
	 */
	if ( empty( $saved_products['timeout'] ) || $saved_products['timeout'] < time() ) {

		update_option( 'edd_licensed_extensions', json_encode( array(
			'timeout'  => strtotime( '+1 day' ),
			'products' => get_licensed_products(),
		) ), false );
	}
}, 200 );

/**
 * Returns licensed EDD extensions that are active on this site.
 * Array values are the `$item_shortname` from `\EDD_License`
 *
 * @see \EDD_License::$item_shortname
 *
 * @since 2.11.4
 * @return array
 */
function get_licensed_extension_slugs() {
	$products = get_option( 'edd_licensed_extensions' );

	/*
	 * If this isn't set for some reason, fall back to trying the global. There are
	 * probably a very limited number of cases where the option would be empty when
	 * the global is not, but worth a shot.
	 */
	if ( empty( $products ) ) {
		return get_licensed_products();
	}

	$products = json_decode( $products, true );

	return isset( $products['products'] ) && is_array( $products['products'] )
		? $products['products']
		: array();
}

/**
 * Triggers our hook for registering extensions.
 * This needs to run after all plugins have definitely been loaded.
 *
 * @since 2.11.4
 */
add_action( 'plugins_loaded', function() {
	/**
	 * Extensions should hook in here to register themselves.
	 *
	 * @since 2.11.4
	 *
	 * @param ExtensionRegistry
	 */
	do_action( 'edd_extension_license_init', EDD()->extensionRegistry );
}, PHP_INT_MAX );

/**
 * Helper function to get the actually licensed products from the global.
 * In 3.1.1.2, all products using the licensing class add their slug to the global,
 * but we are now tracking unlicensed products as well as licensed ones.
 *
 * @return void
 */
function get_licensed_products() {
	$products = array();
	global $edd_licensed_products;
	if ( empty( $edd_licensed_products ) || ! is_array( $edd_licensed_products ) ) {
		return $products;
	}
	foreach ( $edd_licensed_products as $slug => $is_licensed ) {
		if ( $is_licensed ) {
			$products[] = $slug;
		}
	}

	return $products;
}
