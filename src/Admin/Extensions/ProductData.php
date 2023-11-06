<?php

namespace EDD\Admin\Extensions;

class ProductData {

	/**
	 * The product name.
	 *
	 * @since 2.11.4
	 * @var string
	 */
	public $title;

	/**
	 * The product heading.
	 *
	 * @since 2.11.4
	 * @var string
	 */
	public $heading;

	/**
	 * The product slug.
	 *
	 * @since 2.11.4
	 * @var string
	 */
	public $slug = '';

	/**
	 * The URL for the product featured image.
	 *
	 * @since 2.11.4
	 * @var string
	 */
	public $image;

	/**
	 * The product description.
	 *
	 * @since 2.11.4
	 * @var string
	 */
	public $description;

	/**
	 * The extension basename.
	 *
	 * @since 2.11.4
	 * @var string
	 */
	public $basename;

	/**
	 * The settings tab where the extension settings will show.
	 *
	 * @since 2.11.4
	 * @var string
	 */
	public $tab;

	/**
	 * The settings section for the extension.
	 *
	 * @since 2.11.4
	 * @var string
	 */
	public $section;

	/**
	 * The product features.
	 *
	 * @since 2.11.4
	 * @var array
	 */
	public $features = array();

	/**
	 * The product required pass ID.
	 *
	 * @since 3.2.0
	 * @var int
	 */
	public $pass_id = '';

	/**
	 * The style for the product card.
	 *
	 * @var string
	 */
	public $style = '';

	/**
	 * The version of the product.
	 *
	 * @var string
	 */
	public $version = '';

	/**
	 * The product terms.
	 *
	 * @var array
	 */
	public $terms = array();

	/**
	 * The product categories.
	 *
	 * @var array
	 */
	public $categories = array();

	/**
	 * The product icon.
	 *
	 * @var string
	 */
	public $icon = '';

	/**
	 * Take array and return object.
	 *
	 * @since 2.11.4
	 * @param array $array
	 * @return ProductData
	 * @throws \InvalidArgumentException
	 */
	public function fromArray( $array ) {
		$expected_keys  = array( 'title', 'slug', 'description', 'basename' );
		$array_to_check = array_intersect_key( $array, array_flip( $expected_keys ) );

		if ( empty( $array_to_check ) ) {
			throw new \InvalidArgumentException(
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
	 * @since 2.11.4
	 * @param  array       $configuration The custom configuration data.
	 * @return ProductData
	 */
	public function mergeConfig( array $configuration ) {
		foreach ( $configuration as $key => $value ) {
			$this->{$key} = $value;
		}

		return $this;
	}
}
