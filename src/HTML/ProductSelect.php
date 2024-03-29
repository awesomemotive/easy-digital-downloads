<?php
/**
 * ProductSelect HTML Element
 *
 * @package EDD
 * @subpackage HTML
 * @since 3.2.8
 */

namespace EDD\HTML;

defined( 'ABSPATH' ) || exit;

/**
 * Class ProductSelect
 *
 * @since 3.2.8
 * @package EDD\HTML
 */
class ProductSelect extends Select {

	/**
	 * Gets the HTML for the element.
	 *
	 * @since 3.2.8
	 * @return string Element HTML.
	 */
	public function get() {

		$options = $this->get_options();

		// Update options to remove any excluded products.
		if ( ! empty( $this->args['excluded_products'] ) && is_array( $this->args['excluded_products'] ) ) {
			$excluded_products = array();
			foreach ( $this->args['excluded_products'] as $exclusion ) {
				if ( array_key_exists( $exclusion, $options ) ) {
					unset( $options[ $exclusion ] );
				}
				$excluded_products[] = absint( $exclusion );
			}
			$this->args['data']['excluded-products'] = implode( ',', array_filter( $excluded_products ) );
		}

		$this->args['class']            = $this->get_classes();
		$this->args['options']          = $options;
		$this->args['show_option_none'] = false;

		return parent::get();
	}

	/**
	 * Gets the default arguments for the element.
	 *
	 * @since 3.2.8
	 * @return array
	 */
	protected function defaults() {
		return array(
			'name'                 => 'products',
			'id'                   => 'products',
			'class'                => array(),
			'multiple'             => false,
			'selected'             => 0,
			'chosen'               => false,
			'number'               => 30,
			'bundles'              => true,
			'variations'           => false,
			'show_variations_only' => false,
			'placeholder'          => sprintf(
				/* translators: %s: Download singular label */
				__( 'Choose a %s', 'easy-digital-downloads' ),
				edd_get_label_singular()
			),
			'data'                 => array(
				'search-type'        => 'download',
				'search-placeholder' => sprintf(
					/* translators: %s: Download plural label */
					__( 'Search %s', 'easy-digital-downloads' ),
					edd_get_label_plural()
				),
			),
			'required'             => false,
			'products'             => array(),
			'show_option_all'      => false,
		);
	}

	/**
	 * Gets the options for the select element.
	 *
	 * @since 3.2.8
	 * @return array
	 */
	private function get_options() {
		$products = $this->get_products();
		$options  = array( '' => '' );
		if ( $products ) {
			foreach ( $products as $product ) {
				// If bundles are not allowed, skip any products that are bundles.
				if ( ! $this->args['bundles'] && 'bundle' === edd_get_download_type( $product->ID ) ) {
					continue;
				}

				// If a product has no variations, just add it to the list and continue.
				if ( ! edd_has_variable_prices( $product->ID ) ) {
					$options[ absint( $product->ID ) ] = esc_html( $product->post_title );

					continue;
				}

				// The product does have variations. Add the top level product to the list
				// if not showing variations, or not showing variations only.
				if ( false === $this->args['variations'] || ! $this->args['show_variations_only'] ) {
					$options[ absint( $product->ID ) ] = $this->get_product_title( $product );
				}

				$variations = $this->get_variations( $product );
				if ( ! empty( $variations ) ) {
					$options = $options + $variations;
				}
			}
		}

		if ( empty( $this->args['selected'] ) ) {
			return $options;
		}

		// The selected item(s) always need to be in the list, so we make sure to add them if missing.
		$selected = (array) $this->args['selected'];
		foreach ( $selected as $item ) {
			if ( array_key_exists( $item, $options ) ) {
				continue;
			}

			$missing_item = $this->get_missing_selected_product( $item );
			if ( ! empty( $missing_item ) ) {
				$options = $options + $missing_item;
			}
		}

		return $options;
	}

	/**
	 * Gets the products for the select element.
	 *
	 * @since 3.2.8
	 * @return array
	 */
	private function get_products() {
		$products = $this->args['products'];
		if ( empty( $this->args['products'] ) ) {
			$products = EDD()->html->get_products( $this->args );
		}

		if ( empty( $this->args['selected'] ) ) {
			return $products;
		}

		$selected_items = (array) $this->args['selected'];
		$existing_ids   = wp_list_pluck( $products, 'ID' );
		foreach ( $selected_items as $selected_item ) {
			if ( 'download' !== get_post_type( $selected_item ) ) {
				continue;
			}
			if ( ! in_array( $selected_item, $existing_ids, true ) ) {

				// If the selected item has a variation, we just need the product ID.
				$has_variation = strpos( $selected_item, '_' );
				if ( false !== $has_variation ) {
					$selected_item = substr( $selected_item, 0, $has_variation );
				}

				$post = get_post( $selected_item );
				if ( ! is_null( $post ) ) {
					$products[] = $post;
				}
			}
		}

		return $products;
	}

	/**
	 * Gets the classes for the select element.
	 *
	 * @since 3.2.8
	 * @return array
	 */
	private function get_classes() {
		$classes = $this->args['class'];
		if ( ! is_array( $classes ) ) {
			$classes = explode( ' ', $classes );
		}
		if ( ! $this->args['bundles'] ) {
			$classes[] = 'no-bundles';
		}

		if ( $this->args['variations'] ) {
			$classes[] = 'variations';
		}

		if ( $this->args['show_variations_only'] ) {
			$classes[] = 'variations-only';
		}

		if ( ! empty( $this->args['exclude_current'] ) ) {
			$classes[] = 'exclude-current';
		}

		return $classes;
	}

	/**
	 * Gets the missing selected product.
	 *
	 * @since 3.2.8
	 * @param string $item    Item to check.
	 * @return array
	 */
	private function get_missing_selected_product( $item ) {
		$options     = array();
		$parsed_item = edd_parse_product_dropdown_value( $item );
		$download_id = (int) $parsed_item['download_id'];

		if ( 'download' !== get_post_type( $download_id ) ) {
			return $options;
		}

		if ( ! is_null( $parsed_item['price_id'] ) ) {
			$prices = edd_get_variable_prices( $download_id );
			foreach ( $prices as $key => $value ) {
				$name = ! empty( $value['name'] ) ? $value['name'] : '';

				if ( $name && (int) $parsed_item['price_id'] === (int) $key ) {
					$option_key             = absint( $download_id ) . '_' . $key;
					$options[ $option_key ] = esc_html( get_the_title( $download_id ) . ': ' . $name );
				}
			}
		} else {
			$options[ $download_id ] = get_the_title( $download_id );
		}

		return $options;
	}

	/**
	 * Gets the product title for the select element.
	 *
	 * @since 3.2.8
	 * @param \WP_Post $product Product post object.
	 * @return string
	 */
	private function get_product_title( $product ) {
		$title = esc_html( $product->post_title );
		if ( ! $this->args['show_variations_only'] ) {
			$title .= ' (' . __( 'All Price Options', 'easy-digital-downloads' ) . ')';
		}

		return $title;
	}

	/**
	 * Gets the product variations for the select element.
	 *
	 * @since 3.2.8
	 * @param \WP_Post $product Product post object.
	 * @return array
	 */
	private function get_variations( $product ) {
		if ( empty( $this->args['variations'] ) ) {
			return array();
		}
		$prices = edd_get_variable_prices( $product->ID );
		if ( empty( $prices ) ) {
			return array();
		}

		$options = array();
		foreach ( $prices as $key => $value ) {
			if ( ! empty( $value['name'] ) ) {
				$options[ absint( $product->ID ) . '_' . $key ] = esc_html( $product->post_title . ': ' . $value['name'] );
			}
		}

		return $options;
	}
}
