<?php

namespace EDD\Admin\Extensions;

class ProductData {

	/**
	 * The product name.
	 *
	 * @since 2.11.x
	 * @var string
	 */
	public $title;

	/**
	 * The product slug.
	 *
	 * @since 2.11.x
	 * @var string
	 */
	public $slug;

	/**
	 * The URL for the product featured image.
	 *
	 * @since 2.11.x
	 * @var string
	 */
	public $image;

	/**
	 * The product description.
	 *
	 * @since 2.11.x
	 * @var string
	 */
	public $description;

	/**
	 * The extension basename.
	 *
	 * @since 2.11.x
	 * @var string
	 */
	public $basename;

	/**
	 * The settings tab where the extension settings will show.
	 *
	 * @since 2.11.x
	 * @var string
	 */
	public $tab;

	/**
	 * The settings section for the extension.
	 *
	 * @since 2.11.x
	 * @var string
	 */
	public $section;

	/**
	 * The product features.
	 *
	 * @since 2.11.x
	 * @var array
	 */
	public $features = array();

	/**
	 * Take array and return object.
	 *
	 * @since 2.11.x
	 * @param array $array
	 * @return ProductData
	 */
	public function fromArray( $array ) {
		$expected_keys  = array( 'title', 'slug', 'description', 'basename' );
		$array_to_check = array_intersect_key( $array, array_flip( $expected_keys ) );

		if ( empty( $array_to_check ) ) {
			throw new \Exception(
				'Invalid ProductData object, must have the exact following keys: ' . implode( ', ', $expected_keys )
			);
		}

		$product_data = new self();
		foreach ( $array as $key => $value ) {
			$product_data->$key = $value;
		}

		return $product_data;
	}

	/**
	 * Merge an array of data into an object.
	 *
	 * @since 2.11.x
	 * @param  ProductData $product       The original product data object.
	 * @param  array       $configuration The custom configuration data.
	 * @return ProductData
	 */
	public function mergeConfig( ProductData $product, array $configuration ) {
		foreach ( $configuration as $key => $value ) {
			$product->{$key} = $value;
		}

		return $product;
	}
}
