<?php

namespace EDD\Admin\Extensions;

use EDD\Admin\Pass_Manager;

class ExtensionsAPI {

	/**
	 * Gets the product data from the EDD Products API.
	 *
	 * @since 2.11.4
	 * @param array $body    The body for the API request.
	 * @param int   $item_id The product ID, if querying a single product.
	 * @return false|array|ProductData
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
		$option      = get_site_option( $option_name );
		$is_stale    = $this->option_has_expired( $option );

		// The ProductData class.
		$product_data = new ProductData();

		// If the data is "fresh" and what we want exists, return it.
		if ( $option && ! $is_stale ) {
			if ( $item_id && ! empty( $option[ $item_id ] ) ) {
				return $product_data->fromArray( $option[ $item_id ] );
			} elseif ( ! empty( $option['timeout'] ) ) {
				unset( $option['timeout'] );

				return $option;
			}
		}

		// Get all of the product data.
		$all_product_data = $this->get_all_product_data();

		// If no product data was retrieved, let the option sit for an hour.
		if ( empty( $all_product_data ) ) {
			$data = array(
				'timeout' => strtotime( '+1 hour', time() ),
			);
			if ( $option && $is_stale ) {
				$data = array_merge( $option, $data );
			}
			update_site_option( $option_name, $data );

			if ( $item_id && ! empty( $option[ $item_id ] ) ) {
				return $product_data->fromArray( $option[ $item_id ] );
			}
			unset( $option['timeout'] );

			return $option;
		}

		$value = array(
			'timeout' => strtotime( '+1 week', time() ),
		);
		if ( $item_id && ! empty( $all_product_data->$item_id ) ) {
			$item              = $all_product_data->$item_id;
			$value[ $item_id ] = $this->get_item_data( $item );
		} elseif ( in_array( $key, array( 'category', 'tag' ), true ) ) {
			$term_id = $body[ $key ];
			if ( 1592 === $body[ $key ] ) {
				$value = $value + $this->get_pass_extensions_data( $all_product_data );
			} else {
				foreach ( $all_product_data as $item_id => $item ) {
					if ( 'category' === $key && ( empty( $item->categories ) || ! in_array( $term_id, $item->categories, true ) ) ) {
						continue;
					} elseif ( 'tag' === $key && ( empty( $item->tags ) || ! in_array( $term_id, $item->tags, true ) ) ) {
						continue;
					}
					$value[ $item_id ] = $this->get_item_data( $item );
				}
			}
		}

		if ( is_multisite() && get_option( $option_name ) ) {
			delete_option( $option_name );
		}
		update_site_option( $option_name, $value );
		unset( $value['timeout'] );

		return $item_id && ! empty( $value[ $item_id ] ) ? $product_data->fromArray( $value[ $item_id ] ) : $value;
	}

	/**
	 * Gets the extensions data for displaying on the extensions page.
	 * The extensions are grouped by pass.
	 *
	 * @since 3.1.1
	 * @param object $all_product_data
	 * @return array
	 */
	private function get_pass_extensions_data( $all_product_data ) {
		$recommended     = array();
		$personal_pass   = array();
		$extended_pass   = array();
		$pro_pass        = array();
		$all_access_pass = array();
		$pass_manager    = new Pass_Manager();
		foreach ( $all_product_data as $item_id => $item ) {
			if ( ! empty( $item->categories ) ) {
				if ( ! in_array( 1592, $item->categories, true ) ) {
					continue;
				}

				$item->pass_id = $this->get_pass_id( $item->categories, $pass_manager );
				if ( ! empty( $item->tags ) && in_array( 2333, $item->tags, true ) ) {
					$recommended[ $item_id ] = $this->get_item_data( $item );
				} elseif ( $pass_manager::PERSONAL_PASS_ID === $item->pass_id ) {
					$personal_pass[ $item_id ] = $this->get_item_data( $item );
				} elseif ( $pass_manager::EXTENDED_PASS_ID === $item->pass_id ) {
					$extended_pass[ $item_id ] = $this->get_item_data( $item );
				} elseif ( $pass_manager::PROFESSIONAL_PASS_ID === $item->pass_id ) {
					$pro_pass[ $item_id ] = $this->get_item_data( $item );
				} else {
					$all_access_pass[ $item_id ] = $this->get_item_data( $item );
				}
			}
		}

		return $recommended + $personal_pass + $extended_pass + $pro_pass + $all_access_pass;
	}

	/**
	 * Gets the pass ID required to be able to install the extension.
	 *
	 * @since 3.2.2
	 * @param array $categories          The extension categories.
	 * @param Pass_Manager $pass_manager The pass manager.
	 * @return string
	 */
	private function get_pass_id( $categories, $pass_manager ) {
		if ( in_array( $pass_manager->categories[ $pass_manager::PERSONAL_PASS_ID ], $categories, true ) ) {
			return $pass_manager::PERSONAL_PASS_ID;
		}
		if ( in_array( $pass_manager->categories[ $pass_manager::EXTENDED_PASS_ID ], $categories, true ) ) {
			return $pass_manager::EXTENDED_PASS_ID;
		}
		if ( in_array( $pass_manager->categories[ $pass_manager::PROFESSIONAL_PASS_ID ], $categories, true ) ) {
			return $pass_manager::PROFESSIONAL_PASS_ID;
		}

		return $pass_manager::ALL_ACCESS_PASS_ID;
	}

	/**
	 * Gets all of the product data, either from an option or an API request.
	 * If the option exists and has data, it will be an object.
	 *
	 * @since 2.11.4
	 * @return object|false
	 */
	private function get_all_product_data() {
		// Possibly all product data is in an option. If it is, return it.
		$all_product_data = get_site_option( 'edd_all_extension_data' );
		if ( $all_product_data && ! $this->option_has_expired( $all_product_data ) ) {
			return ! empty( $all_product_data['products'] ) ? $all_product_data['products'] : false;
		}

		// Otherwise, query the API.
		$url     = add_query_arg(
			array(
				'edd_action' => 'extension_data',
			),
			$this->get_products_url()
		);
		$request = wp_remote_get(
			esc_url_raw( $url ),
			array(
				'timeout'   => 15,
				'sslverify' => true,
			)
		);

		// If there was an API error, set option and return false.
		if ( is_wp_error( $request ) || ( 200 !== wp_remote_retrieve_response_code( $request ) ) ) {
			update_site_option(
				'edd_all_extension_data',
				array(
					'timeout' => strtotime( '+1 hour', time() ),
				)
			);

			return false;
		}

		// Fresh data has been retrieved, so update the option with a four hour timeout.
		$all_product_data = json_decode( wp_remote_retrieve_body( $request ) );
		$data             = array(
			'timeout'  => strtotime( '+4 hours', time() ),
			'products' => $all_product_data,
		);

		if ( is_multisite() && get_option( 'edd_all_extension_data' ) ) {
			delete_option( 'edd_all_extension_data' );
		}
		update_site_option( 'edd_all_extension_data', $data );

		return $all_product_data;
	}

	/**
	 * Gets the product data as needed for the extension manager.
	 *
	 * @since 2.11.4
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
			'icon'        => $this->get_icon( $item ),
			'categories'  => ! empty( $item->categories ) ? $item->categories : array(),
			'terms'       => ! empty( $item->terms ) ? $item->terms : array(),
			'version'     => ! empty( $item->version ) ? $item->version : false,
			'pass_id'     => ! empty( $item->pass_id ) ? $item->pass_id : '',
		);
	}

	/**
	 * Gets the product icon.
	 *
	 * @since 3.1.1
	 * @param object $item
	 * @return string
	 */
	private function get_icon( $item ) {
		$icon = ! empty( $item->custom_meta->icon ) ? $item->custom_meta->icon : '';

		if ( $icon ) {
			return $icon;
		}

		$icon_size = '2x';

		return ! empty( $item->icons->{$icon_size} ) ? $item->icons->{$icon_size} : $icon;
	}
	/**
	 * Gets the base url for the products remote request.
	 *
	 * @since 2.11.4
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
	 * @since 2.11.4
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
	 * @since 2.11.4
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

	/**
	 * Checks whether a given option has "expired".
	 *
	 * @since 2.11.4
	 * @param array|false $option
	 * @return bool
	 */
	private function option_has_expired( $option ) {
		return empty( $option['timeout'] ) || time() > $option['timeout'];
	}
}
