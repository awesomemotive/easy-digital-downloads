<?php
/**
 * REST API block functions.
 *
 * @package   edd-blocks
 * @copyright 2022 Easy Digital Downloads
 * @license   GPL2+
 * @since 2.0
 */

namespace EDD\Blocks\Downloads\Rest;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

add_action( 'rest_api_init', __NAMESPACE__ . '\register_rest_fields' );
/**
 * Register rest fields.
 *
 * @since 2.0
 * @return void
 */
function register_rest_fields() {

	$taxonomies = array( 'download_category', 'download_tag' );
	foreach ( $taxonomies as $taxonomy ) {
		register_rest_field(
			$taxonomy,
			'meta',
			array(
				'get_callback' => __NAMESPACE__ . '\get_term_meta',
			)
		);
	}
}

/**
 * Get term meta.
 *
 * @since 2.0
 * @param array  $object
 * @param string $field_name
 * @param mixed  $request
 *
 * @return array
 */
function get_term_meta( $object, $field_name, $request ) {

	if ( empty( $object['id'] ) ) {
		return false;
	}

	// Get the image ID.
	$image_id = get_term_meta( $object['id'], 'download_term_image', true );

	// Build meta array.
	return array( 'image' => wp_get_attachment_image( $image_id ) );
}

add_filter( 'edd_api_products_product', __NAMESPACE__ . '\update_products_api' );
/**
 * Add data to the products API output.
 *
 * @since 2.0
 * @param array $product
 * @return array
 */
function update_products_api( $product ) {

	// Get the product ID.
	$product_id = $product['info']['id'];

	// Download Image.
	$product['info']['image'] = wp_get_attachment_image( get_post_meta( $product_id, '_thumbnail_id', true ) );

	// Purchase link.
	$product['info']['purchase_link'] = edd_get_purchase_link( array( 'download_id' => $product_id ) );

	// Price.
	$product['info']['price'] = edd_price( $product_id, false );

	return $product;
}
