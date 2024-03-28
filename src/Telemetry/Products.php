<?php
/**
 * Gets the order data to send to our telemetry server.
 *
 * @package   easy-digital-downloads
 * @copyright Copyright (c) 2023, Easy Digital Downloads
 * @license   GPL2+
 * @since     3.2.0
 */

namespace EDD\Telemetry;

defined( 'ABSPATH' ) || exit;

/**
 * Class Products
 *
 * @since 3.2.0
 */
class Products {

	/**
	 * Gets the array of product data.
	 *
	 * @since 3.2.0
	 * @return array
	 */
	public function get() {

		$data = array(
			'variable_products'    => $this->get_product_count(
				array(
					'key'     => '_variable_pricing',
					'value'   => 1,
					'compare' => '=',
				)
			),
			'hidden_purchase_link' => $this->get_product_count(
				array(
					'key'     => '_edd_hide_purchase_link',
					'compare' => 'EXISTS',
				)
			),
			'download_limit'       => $this->get_product_count(
				array(
					'key'     => '_edd_download_limit',
					'value'   => 0,
					'compare' => '>',
				)
			),
			'quantities_disabled'  => $this->get_product_count(
				array(
					'key'     => '_edd_quantities_disabled',
					'compare' => 'EXISTS',
				)
			),
		);

		foreach ( edd_get_download_types() as $slug => $label ) {
			$query = array(
				'key' => '_edd_product_type',
			);

			if ( empty( $slug ) ) {
				$query['compare'] = 'NOT EXISTS';
			} else {
				$query['value']   = $slug;
				$query['compare'] = '=';
			}

			$slug                             = empty( $slug ) ? 'default' : $slug;
			$data[ 'download_type_' . $slug ] = $this->get_product_count( $query );
		}

		/**
		 * Filters the product data to send to the telemetry server.
		 * Values should be:
		 * - key: a unique string to identify the product/meta
		 * - value: the value of the product/meta. This can be a string, int, or array.
		 */
		return apply_filters( 'edd_telemetry_products', $data );
	}

	/**
	 * Gets the number of variable products.
	 *
	 * @since 3.2.0
	 * @return int
	 */
	private function get_product_count( $meta_query ) {

		$query = new \WP_Query(
			array(
				'post_type'  => 'download',
				'status'     => 'publish',
				'nopaging'   => true,
				'meta_query' => array(
					$meta_query,
				),
			)
		);

		return absint( $query->post_count );
	}
}
