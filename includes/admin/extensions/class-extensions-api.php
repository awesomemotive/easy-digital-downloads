<?php

namespace EDD\Admin\Extensions;

class ExtensionsAPI {

	/**
	 * Gets the product data from the EDD Products API.
	 *
	 * @since 2.11.x
	 * @param array $body    The body for the API request.
	 * @param int   $item_id The product ID, if querying a single product.
	 * @return object
	 */
	public function get_product_data( $body = array(), $item_id = false ) {
		if ( empty( $body ) ) {
			if ( empty( $item_id ) ) {
				return false;
			}
			$body = $this->get_api_body( $item_id );
		}
		$key         = $this->array_key_first( $body );
		$option_name = "edd_extension_{$key}_{$body[ $key ]}_data";
		$option      = $this->get_stored_extension_data( $option_name );
		if ( $item_id && ! empty( $option[ $item_id ] ) ) {
			return $option[ $item_id ];
		} elseif ( ! empty( $option['timeout'] ) ) {
			unset( $option['timeout'] );

			return $option;
		}

		$request = wp_remote_get(
			$this->get_products_api_url(),
			array(
				'timeout'   => 15,
				'sslverify' => true,
				'body'      => $body,
			)
		);

		// If there was an API error, set timeout for 1 hour and the product data to false.
		if ( is_wp_error( $request ) || ( 200 !== wp_remote_retrieve_response_code( $request ) ) ) {
			$array_key = ! empty( $item_id ) ? $item_id : $body[ $key ];
			update_option(
				$option_name,
				array(
					$array_key => false,
					'timeout'  => strtotime( '+1 hour', time() ),
				),
				false
			);

			return false;
		}

		$response = json_decode( wp_remote_retrieve_body( $request ) );
		$value    = array(
			'timeout' => strtotime( '+1 week', time() ),
		);
		foreach ( $response->products as $product ) {
			$value[ $product->info->id ] = array(
				'title'       => $product->info->title,
				'image'       => $product->info->thumbnail,
				'description' => $product->info->excerpt,
			);
		}
		update_option( $option_name, $value, false );

		return $item_id ? $value[ $item_id ] : $value;
	}

	/**
	 * Gets the stored extension data from the database.
	 * If it doesn't exist, or has expired, deletes the option and returns false.
	 *
	 * @since 2.11.x
	 * @param string $option_name The option name to look for in the database.
	 * @return array|bool         Returns the option data if not expired, or false if expired or doesn't exist yet.
	 */
	private function get_stored_extension_data( $option_name ) {
		$option = get_option( $option_name );
		if ( ! empty( $option['timeout'] ) && time() <= $option['timeout'] ) {
			return $option;
		};

		delete_option( $option_name );
		return false;
	}

	/**
	 * Gets the EDD REST API URL for products.
	 *
	 * @since 2.11.x
	 * @return string
	 */
	private function get_products_api_url() {
		if ( defined( 'EDD_PRODUCTS_API_URL' ) ) {
			return EDD_PRODUCTS_API_URL;
		}

		return 'https://easydigitaldownloads.com/edd-api/v2/products/';
	}

	/**
	 * Gets the default array for the body of the API request.
	 * A class may override this by setting an array to query a tag or category.
	 *
	 * @since 2.11.x
	 * @param int $item_id The product ID.
	 * @return array
	 */
	private function get_api_body( $item_id ) {
		return array(
			'product' => $item_id,
		);
	}

	/**
	 * Gets the first key of an array.
	 * (Shims array_key_first for PHP < 7.3)
	 *
	 * @since 2.11.x
	 * @param array $array
	 * @return string|null
	 */
	private function array_key_first( array $array ) {
		if ( function_exists( 'array_key_first' ) ) {
			return array_key_first( $array );
		}
		foreach ( $array as $key => $unused ) {
			return $key;
		}

		return null;
	}
}
