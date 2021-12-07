<?php

namespace EDD\Admin\Extensions;

class ExtensionsAPI {

	/**
	 * Gets the product data from the EDD Products API.
	 *
	 * @since 2.11.x
	 * @param array $body    The body for the API request.
	 * @param int   $item_id The product ID, if querying a single product.
	 * @return array|false
	 */
	public function get_product_data( $body = array(), $item_id = false ) {
		if ( empty( $body ) ) {
			if ( empty( $item_id ) ) {
				return false;
			}
			$body = $this->get_api_body( $item_id );
		}
		$key = $this->array_key_first( $body );
		// The option name is created from the first key/value pair of the API "body".
		$option_name = sanitize_key( "edd_extension_{$key}_{$body[ $key ]}_data" );
		$option      = get_option( $option_name );
		$is_stale    = ! empty( $option['timeout'] ) && time() > $option['timeout'];

		// If the data is "fresh" and what we want exists, return it.
		if ( ! $is_stale ) {
			if ( $item_id && ! empty( $option[ $item_id ] ) ) {
				return $option[ $item_id ];
			} elseif ( ! empty( $option['timeout'] ) ) {
				unset( $option['timeout'] );

				return $option;
			}
		}

		// The data either does not exist or is expired, so query the API.
		$url     = add_query_arg(
			array(
				'edd_action' => 'extension_data',
			),
			$this->get_products_url()
		);
		$request = wp_remote_get(
			$url,
			array(
				'timeout'   => 15,
				'sslverify' => true,
			)
		);

		// If there was an API error, set timeout for 1 hour and return stale product data if it exists.
		if ( is_wp_error( $request ) || ( 200 !== wp_remote_retrieve_response_code( $request ) ) ) {
			$data = array(
				'timeout' => strtotime( '+1 hour', time() ),
			);
			if ( $option && $is_stale ) {
				$data = array_merge( $option, array( 'timeout' => strtotime( '+1 hour', $data['timeout'] ) ) );
			}
			update_option(
				$option_name,
				$data,
				false
			);

			if ( $item_id && ! empty( $option[ $item_id ] ) ) {
				return $option[ $item_id ];
			}

			return $option;
		}

		// Fresh data has been retrieved, so remove the option and populate with fresh data.
		delete_option( $option_name );

		$response = json_decode( wp_remote_retrieve_body( $request ) );
		$value    = array(
			'timeout' => strtotime( '+1 week', time() ),
		);
		if ( $item_id && ! empty( $response->$item_id ) ) {
			$item              = $response->$item_id;
			$value[ $item_id ] = $this->get_item_data( $item );
		} elseif ( in_array( $key, array( 'category', 'tag' ), true ) ) {
			$term_id = $body[ $key ];
			foreach ( $response as $item_id => $item ) {
				if ( 'category' === $key && ( empty( $item->categories ) || ! in_array( $term_id, $item->categories, true ) ) ) {
					continue;
				} elseif ( 'tag' === $key && ( empty( $item->tags ) || ! in_array( $term_id, $item->tags, true ) ) ) {
					continue;
				}
				$value[ $item_id ] = $this->get_item_data( $item );
			}
		}

		update_option( $option_name, $value, false );

		return $item_id && ! empty( $value[ $item_id ] ) ? $value[ $item_id ] : $value;
	}

	/**
	 * Gets the product data as needed for the extension manager.
	 *
	 * @since 2.11.x
	 * @param object $item
	 * @return array
	 */
	private function get_item_data( $item ) {
		return array(
			'title'       => ! empty( $item->title ) ? $item->title : '',
			'slug'        => ! empty( $item->slug ) ? $item->slug : '',
			'image'       => ! empty( $item->image ) ? $item->image : '',
			'description' => ! empty( $item->excerpt ) ? $item->excerpt : '',
			'basename'    => ! empty( $item->custom_meta->basename ) ? $item->custom_meta->basename : '',
			'tab'         => ! empty( $item->custom_meta->settings_tab ) ? $item->custom_meta->settings_tab : '',
			'section'     => ! empty( $item->custom_meta->settings_section ) ? $item->custom_meta->settings_section : '',
		);
	}

	/**
	 * Gets the base url for the products remote request.
	 *
	 * @since 2.11.x
	 * @return string
	 */
	private function get_products_url() {
		if ( defined( 'EDD_PRODUCTS_URL' ) ) {
			return EDD_PRODUCTS_URL;
		}

		return 'https://easydigitaldownloads.com/';
	}

	/**
	 * Gets the default array for the body of the API request.
	 * A class may override this by setting an array to query a tag or category.
	 * Note that the first array key/value pair are used to create the option name.
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
