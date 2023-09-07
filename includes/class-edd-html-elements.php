<?php
/**
 * HTML elements
 *
 * A helper class for outputting common HTML elements, such as product drop downs
 *
 * @package     EDD
 * @subpackage  Classes/HTML
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.5
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * EDD_HTML_Elements Class
 *
 * @since 1.5
 */
class EDD_HTML_Elements {

	/**
	 * Renders an HTML Dropdown of all the Products (Downloads)
	 *
	 * @since 1.5
	 *
	 * @param array $args Arguments for the dropdown
	 *
	 * @return string $output Product dropdown
	 */
	public function product_dropdown( $args = array() ) {
		$defaults = array(
			'name'                 => 'products',
			'id'                   => 'products',
			'class'                => '',
			'multiple'             => false,
			'selected'             => 0,
			'chosen'               => false,
			'number'               => 30,
			'bundles'              => true,
			'variations'           => false,
			'show_variations_only' => false,
			'placeholder'          => sprintf( __( 'Choose a %s', 'easy-digital-downloads' ), edd_get_label_singular() ),
			'data'                 => array(
				'search-type'        => 'download',
				'search-placeholder' => sprintf( __( 'Search %s', 'easy-digital-downloads' ), edd_get_label_plural() ),
			),
			'required'             => false,
			'products'             => array(),
		);

		$args = wp_parse_args( $args, $defaults );

		$products = $args['products'];
		if ( empty( $args['products'] ) ) {
			$products = $this->get_products( $args );
		}
		$existing_ids = wp_list_pluck( $products, 'ID' );
		if ( ! empty( $args['selected'] ) ) {

			$selected_items = $args['selected'];
			if ( ! is_array( $selected_items ) ) {
				$selected_items = array( $selected_items );
			}

			foreach ( $selected_items as $selected_item ) {
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
		}

		$options = array(
			'' => '',
		);
		if ( $products ) {
			foreach ( $products as $product ) {
				// If bundles are not allowed, skip any products that are bundles.
				if ( ! $args['bundles'] && 'bundle' === edd_get_download_type( $product->ID ) ) {
					continue;
				}

				$has_variations = edd_has_variable_prices( $product->ID );

				// If a product has no variations, just add it to the list and continue.
				if ( ! $has_variations ) {
					$title                             = esc_html( $product->post_title );
					$options[ absint( $product->ID ) ] = $title;

					continue;
				}

				// The product does have variations. Add the top level product to the list
				// if not showing variations, or not showing variations only.
				if ( false === $args['variations'] || ! $args['show_variations_only'] ) {
					$title = esc_html( $product->post_title );
					if ( ! $args['show_variations_only'] ) {
						$title .= ' (' . __( 'All Price Options', 'easy-digital-downloads' ) . ')';
					}
					$options[ absint( $product->ID ) ] = $title;
				}

				// If showing variations, add them to the list.
				if ( $args['variations'] ) {
					$prices = edd_get_variable_prices( $product->ID );
					if ( ! empty( $prices ) ) {
						foreach ( $prices as $key => $value ) {
							$name = ! empty( $value['name'] ) ? $value['name'] : '';
							if ( $name ) {
								$options[ absint( $product->ID ) . '_' . $key ] = esc_html( $product->post_title . ': ' . $name );
							}
						}
					}
				}
			}
		}

		// This ensures that any selected products are included in the drop down.
		if ( is_array( $args['selected'] ) ) {
			foreach ( $args['selected'] as $item ) {
				if ( ! array_key_exists( $item, $options ) ) {

					$parsed_item = edd_parse_product_dropdown_value( $item );

					if ( ! is_null( $parsed_item['price_id'] ) ) {
						$prices = edd_get_variable_prices( (int) $parsed_item['download_id'] );
						foreach ( $prices as $key => $value ) {
							$name = ( isset( $value['name'] ) && ! empty( $value['name'] ) ) ? $value['name'] : '';

							if ( $name && (int) $parsed_item['price_id'] === (int) $key ) {
								$options[ absint( $product->ID ) . '_' . $key ] = esc_html( get_the_title( (int) $parsed_item['download_id'] ) . ': ' . $name );
							}
						}
					} else {
						$options[ $parsed_item['download_id'] ] = get_the_title( $parsed_item['download_id'] );
					}
				}
			}
		} elseif ( false !== $args['selected'] && $args['selected'] !== 0 ) {
			if ( ! array_key_exists( $args['selected'], $options ) ) {
				$parsed_item = edd_parse_product_dropdown_value( $args['selected'] );

				if ( ! is_null( $parsed_item['price_id'] ) ) {
					$prices = edd_get_variable_prices( (int) $parsed_item['download_id'] );

					foreach ( $prices as $key => $value ) {
						$name = ( isset( $value['name'] ) && ! empty( $value['name'] ) ) ? $value['name'] : '';

						if ( $name && (int) $parsed_item['price_id'] === (int) $key ) {
							$options[ absint( $product->ID ) . '_' . $key ] = esc_html( get_the_title( (int) $parsed_item['download_id'] ) . ': ' . $name );
						}
					}
				} else {
					$options[ $parsed_item['download_id'] ] = get_the_title( $parsed_item['download_id'] );
				}
			}
		}

		if ( ! $args['bundles'] ) {
			$args['class'] .= ' no-bundles';
		}

		if ( $args['variations'] ) {
			$args['class'] .= ' variations';
		}

		if ( $args['show_variations_only'] ) {
			$args['class'] .= ' variations-only';
		}

		// 'all' gets created as an option if passed via the `selected` argument.
		if ( isset( $options['all'] ) ) {
			unset( $options['all'] );
		}

		$output = $this->select( array(
			'name'             => $args['name'],
			'selected'         => $args['selected'],
			'id'               => $args['id'],
			'class'            => $args['class'],
			'options'          => $options,
			'chosen'           => $args['chosen'],
			'multiple'         => $args['multiple'],
			'placeholder'      => $args['placeholder'],
			'show_option_all'  => isset( $args['show_option_all'] ) ? $args['show_option_all'] : false,
			'show_option_none' => false,
			'data'             => $args['data'],
			'required'         => $args['required'],
		) );

		return $output;
	}

	/**
	 * Get EDD products for the product dropdown.
	 *
	 * @param array  $args     Parameters for the get_posts function.
	 * @return array WP_Post[] Array of download objects.
	 */
	public function get_products( $args = array() ) {
		$defaults = array(
			'number'  => 30,
			'bundles' => true,
		);

		$args = wp_parse_args( $args, $defaults );

		$product_args = array(
			'post_type'      => 'download',
			'orderby'        => 'title',
			'order'          => 'ASC',
			'posts_per_page' => $args['number'],
		);

		if ( ! current_user_can( 'edit_products' ) ) {
			$product_args['post_status'] = apply_filters( 'edd_product_dropdown_status_nopriv', array( 'publish' ) );
		} else {
			$product_args['post_status'] = apply_filters(
				'edd_product_dropdown_status',
				array(
					'publish',
					'draft',
					'private',
					'future',
				)
			);
		}

		if ( is_array( $product_args['post_status'] ) ) {

			// Given the array, sanitize them.
			$product_args['post_status'] = array_map( 'sanitize_text_field', $product_args['post_status'] );
		} else {

			// If we didn't get an array, fallback to 'publish'.
			$product_args['post_status'] = array( 'publish' );
		}

		// If bundles are not allowed, get a few more products to account for the ones that will be removed.
		if ( ! $args['bundles'] && 30 === $args['number'] ) {
			$product_args['posts_per_page'] = 40;
		}

		$product_args = apply_filters( 'edd_product_dropdown_args', $product_args );

		return get_posts( $product_args );
	}

	/**
	 * Renders an HTML Dropdown of all customers
	 *
	 * @since 2.2
	 *
	 * @param array $args
	 *
	 * @return string $output Customer dropdown
	 */
	public function customer_dropdown( $args = array() ) {
		$defaults = array(
			'name'          => 'customers',
			'id'            => 'customers',
			'class'         => '',
			'multiple'      => false,
			'selected'      => 0,
			'chosen'        => true,
			'placeholder'   => __( 'Choose a Customer', 'easy-digital-downloads' ),
			'number'        => 30,
			'data'          => array(
				'search-type'        => 'customer',
				'search-placeholder' => __( 'Search Customers', 'easy-digital-downloads' ),
			),
			'none_selected' => __( 'No customer attached', 'easy-digital-downloads' ),
			'required'      => false,
		);

		$args = wp_parse_args( $args, $defaults );

		$customers = edd_get_customers( array(
			'number' => $args['number'],
		) );

		$options = array();

		if ( $customers ) {
			$options[0] = $args['none_selected'];
			foreach ( $customers as $customer ) {
				$options[ absint( $customer->id ) ] = esc_html( $customer->name . ' (' . $customer->email . ')' );
			}
		} else {
			$options[0] = __( 'No customers found', 'easy-digital-downloads' );
		}

		if ( ! empty( $args['selected'] ) ) {

			// If a selected customer has been specified, we need to ensure it's in the initial list of customers displayed
			if ( ! array_key_exists( $args['selected'], $options ) ) {
				$customer = new EDD_Customer( $args['selected'] );

				if ( $customer ) {
					$options[ absint( $args['selected'] ) ] = esc_html( $customer->name . ' (' . $customer->email . ')' );
				}
			}
		}

		$output = $this->select( array(
			'name'             => $args['name'],
			'selected'         => $args['selected'],
			'id'               => $args['id'],
			'class'            => $args['class'] . ' edd-customer-select',
			'options'          => $options,
			'multiple'         => $args['multiple'],
			'placeholder'      => $args['placeholder'],
			'chosen'           => $args['chosen'],
			'show_option_all'  => false,
			'show_option_none' => false,
			'data'             => $args['data'],
			'required'         => $args['required'],
		) );

		return $output;
	}

	/**
	 * Renders an HTML Dropdown of all the Users
	 *
	 * @since 2.6.9
	 *
	 * @param array $args
	 *
	 * @return string $output User dropdown
	 */
	public function user_dropdown( $args = array() ) {
		$defaults = array(
			'name'        => 'users',
			'id'          => 'users',
			'class'       => '',
			'multiple'    => false,
			'selected'    => 0,
			'chosen'      => true,
			'placeholder' => __( 'Select a User', 'easy-digital-downloads' ),
			'number'      => 30,
			'data'        => array(
				'search-type'        => 'user',
				'search-placeholder' => __( 'Search Users', 'easy-digital-downloads' ),
			),
			'required'    => false,
		);

		$args = wp_parse_args( $args, $defaults );

		$user_args = array(
			'number' => $args['number'],
		);
		$users     = get_users( $user_args );
		$options   = array();

		if ( $users ) {
			foreach ( $users as $user ) {
				$options[ $user->ID ] = esc_html( $user->display_name );
			}
		} else {
			$options[0] = __( 'No users found', 'easy-digital-downloads' );
		}

		$selected = $args['selected'];
		if ( ! is_array( $selected ) ) {
			$selected = array( $selected );
		}
		// If a selected user has been specified, we need to ensure it's in the initial list of user displayed
		if ( ! empty( $selected ) ) {
			foreach ( $selected as $selected_user ) {
				if ( ! array_key_exists( $selected_user, $options ) ) {
					$user = get_userdata( $selected_user );

					if ( $user ) {
						$options[ absint( $user->ID ) ] = esc_html( $user->display_name );
					}
				}
			}
		}

		$output = $this->select( array(
			'name'             => $args['name'],
			'selected'         => $args['selected'],
			'id'               => $args['id'],
			'class'            => $args['class'] . ' edd-user-select',
			'options'          => $options,
			'multiple'         => $args['multiple'],
			'placeholder'      => $args['placeholder'],
			'chosen'           => $args['chosen'],
			'show_option_all'  => false,
			'show_option_none' => false,
			'data'             => $args['data'],
			'required'         => $args['required'],
		) );

		return $output;
	}

	/**
	 * Renders an HTML Dropdown of all the Discounts
	 *
	 * @since 1.5.2
	 * @since 3.0 Allow $args to be passed.
	 *
	 * @param string $name     Name attribute of the dropdown.
	 * @param int    $selected Discount to select automatically.
	 * @param string $status   Discount post_status to retrieve.
	 *
	 * @return string $output Discount dropdown
	 */
	public function discount_dropdown( $name = 'edd_discounts', $selected = 0, $status = '' ) {
		$defaults = array(
			'name'            => 'discounts',
			'id'              => 'discounts',
			'class'           => '',
			'multiple'        => false,
			'selected'        => 0,
			'chosen'          => true,
			'placeholder'     => __( 'Choose a Discount', 'easy-digital-downloads' ),
			'show_option_all' => __( 'All Discounts', 'easy-digital-downloads' ),
			'number'          => 30,
			'data'            => array(
				'search-type'        => 'discount',
				'search-placeholder' => __( 'Search Discounts', 'easy-digital-downloads' ),
			),
			'required'        => false,
		);

		$args = func_get_args();

		if ( 1 === func_num_args() && is_array( $args[0] ) ) {
			$args = wp_parse_args( $args[0], $defaults );
		} else {
			$args = wp_parse_args( array(
				'name'     => $name,
				'selected' => $selected,
				'nopaging' => true,
			), $defaults );
		}

		$discount_args = array(
			'number' => $args['number'],
		);

		if ( ! empty( $status ) ) {
			$discount_args['status'] = $status;
		}

		$discount_args['status'] = ! empty( $status ) ? $status : array( 'active', 'expired', 'inactive', 'archived' );

		$discounts = edd_get_discounts( $discount_args );
		$options   = array();

		if ( $discounts ) {
			foreach ( $discounts as $discount ) {
				$options[ absint( $discount->id ) ] = esc_html( $discount->name );
			}
		} else {
			$options[0] = __( 'No discounts found', 'easy-digital-downloads' );
		}

		$output = $this->select( array(
			'name'             => $args['name'],
			'selected'         => $args['selected'],
			'id'               => $args['id'],
			'class'            => $args['class'] . ' edd-user-select',
			'options'          => $options,
			'multiple'         => $args['multiple'],
			'placeholder'      => $args['placeholder'],
			'chosen'           => $args['chosen'],
			'show_option_all'  => $args['show_option_all'],
			'show_option_none' => false,
			'required'         => $args['required'],
		) );

		return $output;
	}

	/**
	 * Renders an HTML Dropdown of all the Categories
	 *
	 * @since 1.5.2
	 *
	 * @param string $name     Name attribute of the dropdown
	 * @param int    $selected Category to select automatically
	 *
	 * @return string $output Category dropdown
	 */
	public function category_dropdown( $name = 'edd_categories', $selected = 0 ) {
		$categories = get_terms( 'download_category', apply_filters( 'edd_category_dropdown', array() ) );
		$options    = array();

		foreach ( $categories as $category ) {
			$options[ absint( $category->term_id ) ] = esc_html( $category->name );
		}

		$category_labels = edd_get_taxonomy_labels( 'download_category' );
		$output          = $this->select( array(
			'name'             => $name,
			'selected'         => $selected,
			'options'          => $options,
			'show_option_all'  => sprintf( _x( 'All %s', 'plural: Example: "All Categories"', 'easy-digital-downloads' ), $category_labels['name'] ),
			'show_option_none' => false,
		) );

		return $output;
	}

	/**
	 * Renders an HTML Dropdown of years
	 *
	 * @since 1.5.2
	 *
	 * @param string $name         Name attribute of the dropdown.
	 * @param int    $selected     Year to select automatically.
	 * @param int    $years_before Number of years before the current year the dropdown should start with.
	 * @param int    $years_after  Number of years after the current year the dropdown should finish at.
	 * @param string $id           A unique identifier for the field.
	 * @return string $output Year dropdown
	 */
	public function year_dropdown( $name = 'year', $selected = 0, $years_before = 5, $years_after = 0, $id = 'edd_year_select' ) {
		$current    = date( 'Y' );
		$start_year = $current - absint( $years_before );
		$end_year   = $current + absint( $years_after );
		$selected   = empty( $selected ) ? date( 'Y' ) : $selected;
		$options    = array();

		while ( $start_year <= $end_year ) {
			$options[ absint( $start_year ) ] = $start_year;
			$start_year ++;
		}

		$output = $this->select(
			array(
				'name'             => $name,
				'id'               => $id . '_' . $name,
				'selected'         => $selected,
				'options'          => $options,
				'show_option_all'  => false,
				'show_option_none' => false,
			)
		);

		return $output;
	}

	/**
	 * Renders an HTML Dropdown of months
	 *
	 * @since 1.5.2
	 *
	 * @param string  $name             Name attribute of the dropdown.
	 * @param int     $selected         Month to select automatically.
	 * @param string  $id               A unique identifier for the field.
	 * @param boolean $return_long_name Whether to use the long name for the month.
	 *
	 * @return string $output Month dropdown
	 */
	public function month_dropdown( $name = 'month', $selected = 0, $id = 'edd_month_select', $return_long_name = false ) {
		$month    = 1;
		$options  = array();
		$selected = empty( $selected ) ? date( 'n' ) : $selected;

		while ( $month <= 12 ) {
			$options[ absint( $month ) ] = edd_month_num_to_name( $month, $return_long_name );
			$month ++;
		}

		$output = $this->select(
			array(
				'name'             => $name,
				'id'               => $id . '_' . $name,
				'selected'         => $selected,
				'options'          => $options,
				'show_option_all'  => false,
				'show_option_none' => false,
			)
		);

		return $output;
	}

	/**
	 * Gets the countries dropdown.
	 *
	 * @since  3.0
	 * @param  array  $args    The array of parameters passed to the method
	 * @param  string $country The selected country
	 * @return string
	 */
	public function country_select( $args = array(), $country = '' ) {
		$args = wp_parse_args(
			$args,
			array(
				'name'             => 'edd_countries',
				'class'            => 'edd_countries_filter',
				'options'          => edd_get_country_list(),
				'chosen'           => true,
				'selected'         => $country,
				'show_option_none' => false,
				'placeholder'      => __( 'Choose a Country', 'easy-digital-downloads' ),
				'show_option_all'  => __( 'All Countries', 'easy-digital-downloads' ),
				'data'             => array(
					'nonce' => wp_create_nonce( 'edd-country-field-nonce' ),
				),
				'required'         => false,
			)
		);

		if ( false === strpos( $args['class'], 'edd_countries_filter' ) ) {
			$args['class'] .= ' edd_countries_filter';
		}

		return $this->select( $args );
	}

	/**
	 * Gets the regions dropdown.
	 *
	 * @since  3.0
	 * @param  array  $args     The array of parameters passed to the method
	 * @param  string $country  The country from which to populate the regions
	 * @param  string $region   The selected region
	 * @return string
	 */
	public function region_select( $args = array(), $country = '', $region = '' ) {
		if ( ! $country ) {
			$country = edd_get_shop_country();
		}
		$args = wp_parse_args(
			$args,
			array(
				'name'             => 'edd_regions',
				'class'            => 'edd_regions_filter',
				'options'          => edd_get_shop_states( $country ),
				'chosen'           => true,
				'selected'         => $region,
				'show_option_none' => false,
				'placeholder'      => __( 'Choose a Region', 'easy-digital-downloads' ),
				'show_option_all'  => __( 'All Regions', 'easy-digital-downloads' ),
				'required'         => false,
			)
		);

		if ( false === strpos( $args['class'], 'edd_regions_filter' ) ) {
			$args['class'] .= ' edd_regions_filter';
		}

		return $this->select( $args );
	}

	/**
	 * Renders an HTML Dropdown
	 *
	 * @since 1.6
	 *
	 * @param array $args
	 *
	 * @return string
	 */
	public function select( $args = array() ) {

		$args = wp_parse_args( $args, array(
			'options'          => array(),
			'name'             => null,
			'class'            => '',
			'id'               => '',
			'selected'         => 0,
			'chosen'           => false,
			'placeholder'      => null,
			'multiple'         => false,
			'show_option_all'  => _x( 'All', 'all dropdown items', 'easy-digital-downloads' ),
			'show_option_none' => _x( 'None', 'no dropdown items', 'easy-digital-downloads' ),
			'data'             => array(),
			'readonly'         => false,
			'disabled'         => false,
			'required'         => false,
		) );

		$data_elements = '';
		foreach ( $args['data'] as $key => $value ) {
			$data_elements .= ' data-' . esc_attr( $key ) . '="' . esc_attr( $value ) . '"';
		}

		if ( $args['multiple'] ) {
			$multiple = ' MULTIPLE';
		} else {
			$multiple = '';
		}

		if ( $args['chosen'] ) {
			$args['class'] .= ' edd-select-chosen';
			if ( is_rtl() ) {
				$args['class'] .= ' chosen-rtl';
			}
		}

		if ( $args['placeholder'] ) {
			$placeholder = $args['placeholder'];
		} else {
			$placeholder = '';
		}

		if ( isset( $args['readonly'] ) && $args['readonly'] ) {
			$readonly = ' readonly="readonly"';
		} else {
			$readonly = '';
		}

		if ( isset( $args['disabled'] ) && $args['disabled'] ) {
			$disabled = ' disabled="disabled"';
		} else {
			$disabled = '';
		}

		$required = '';
		if ( ! empty( $args['required'] ) ) {
			$required = ' required';
		}

		$class  = implode( ' ', array_map( 'esc_attr', explode( ' ', $args['class'] ) ) );
		$output = '<select' . $disabled . $readonly . $required . ' name="' . esc_attr( $args['name'] ) . '" id="' . esc_attr( str_replace( '-', '_', $args['id'] ) ) . '" class="edd-select ' . $class . '"' . $multiple . ' data-placeholder="' . $placeholder . '"' . $data_elements . '>';

		if ( ! isset( $args['selected'] ) || ( is_array( $args['selected'] ) && empty( $args['selected'] ) ) || ! $args['selected'] ) {
			$selected = "";
		}

		if ( ! empty( $args['show_option_all'] ) ) {
			if ( $args['multiple'] && ! empty( $args['selected'] ) ) {
				$selected = selected( true, in_array( 0, (array) $args['selected'] ), false );
			} elseif ( isset( $args['selected'] ) && ! is_array( $args['selected'] ) ) {
				$selected = selected( $args['selected'], 0, false );
			}
			$output .= '<option value="all"' . $selected . '>' . esc_html( $args['show_option_all'] ) . '</option>';
		}

		if ( ! empty( $args['options'] ) ) {
			if ( $args['show_option_none'] ) {
				if ( $args['multiple'] ) {
					$selected = selected( true, in_array( - 1, $args['selected'] ), false );
				} elseif ( isset( $args['selected'] ) && ! is_array( $args['selected'] ) && ! empty( $args['selected'] ) ) {
					$selected = selected( $args['selected'], - 1, false );
				}
				$output .= '<option value="-1"' . $selected . '>' . esc_html( $args['show_option_none'] ) . '</option>';
			}

			foreach ( $args['options'] as $key => $option ) {
				if ( $args['multiple'] && is_array( $args['selected'] ) ) {
					$selected = selected( true, in_array( (string) $key, $args['selected'] ), false );
				} elseif ( isset( $args['selected'] ) && ! is_array( $args['selected'] ) ) {
					$selected = selected( $args['selected'], $key, false );
				}

				$output .= '<option value="' . esc_attr( $key ) . '"' . $selected . '>' . esc_html( $option ) . '</option>';
			}
		}

		$output .= '</select>';

		return $output;
	}

	/**
	 * Renders an HTML Checkbox
	 *
	 * @since 1.9
	 * @since 3.0 Added `label` argument.
	 *
	 * @param array $args
	 *
	 * @return string Checkbox HTML code
	 */
	public function checkbox( $args = array() ) {
		$defaults = array(
			'name'    => null,
			'current' => null,
			'class'   => 'edd-checkbox',
			'options' => array(
				'disabled' => false,
				'readonly' => false,
			),
			'label'   => '',
			'value'   => null,
		);

		$args = wp_parse_args( $args, $defaults );

		$classes   = explode( ' ', $args['class'] );
		$classes[] = $args['name'];
		$class     = implode( ' ', array_map( 'sanitize_html_class', array_unique( array_filter( $classes ) ) ) );

		$options = '';
		if ( ! empty( $args['options']['disabled'] ) ) {
			$options .= ' disabled="disabled"';
		} elseif ( ! empty( $args['options']['readonly'] ) ) {
			$options .= ' readonly';
		}

		$value = '';
		if ( ! empty( $args['value'] ) ) {
			$value .= ' value="' . esc_attr( $args['value'] ) . '"';
		}

		// Checked could mean 'on' or 1 or true, so sanitize it for checked()
		$to_check = ! empty( $args['current'] );
		$checked  = checked( true, $to_check, false );

		// Get the HTML to output
		$output = '<input type="checkbox" name="' . esc_attr( $args['name'] ) . '" id="' . esc_attr( $args['name'] ) . '" class="' . esc_attr( $class ) . '" ' . $value . $checked . $options . ' />';

		if ( ! empty( $args['label'] ) ) {
			$output .= '<label for="' . esc_attr( $args['name'] ) . '">' . wp_kses_post( $args['label'] ) . '</label>';
		}

		return $output;
	}

	/**
	 * Renders an HTML Text field
	 *
	 * @since 1.5.2
	 *
	 * @param array $args Arguments for the text field
	 *
	 * @return string Text field
	 */
	public function text( $args = array() ) {
		// Backwards compatibility
		if ( func_num_args() > 1 ) {
			$args = func_get_args();

			$name  = $args[0];
			$value = isset( $args[1] ) ? $args[1] : '';
			$label = isset( $args[2] ) ? $args[2] : '';
			$desc  = isset( $args[3] ) ? $args[3] : '';
		}

		$defaults = array(
			'id'           => '',
			'name'         => isset( $name ) ? $name : 'text',
			'value'        => isset( $value ) ? $value : null,
			'label'        => isset( $label ) ? $label : null,
			'desc'         => isset( $desc ) ? $desc : null,
			'placeholder'  => '',
			'class'        => 'regular-text',
			'disabled'     => false,
			'autocomplete' => '',
			'data'         => false,
			'required'     => false,
		);

		$args = wp_parse_args( $args, $defaults );

		$class    = implode( ' ', array_map( 'sanitize_html_class', explode( ' ', $args['class'] ) ) );
		$disabled = '';
		if ( $args['disabled'] ) {
			$disabled = ' disabled="disabled"';
		}

		$required = '';
		if ( ! empty( $args['required'] ) ) {
			$required = ' required';
		}

		$data = '';
		if ( ! empty( $args['data'] ) ) {
			foreach ( $args['data'] as $key => $value ) {
				$data .= 'data-' . edd_sanitize_key( $key ) . '="' . esc_attr( $value ) . '" ';
			}
		}

		$output = '<span id="edd-' . edd_sanitize_key( $args['name'] ) . '-wrap">';
		if ( ! empty( $args['label'] ) ) {
			$output .= '<label class="edd-label" for="' . edd_sanitize_key( $args['id'] ) . '">' . esc_html( $args['label'] ) . '</label>';
		}

		if ( ! empty( $args['desc'] ) ) {
			$output .= '<span class="edd-description">' . esc_html( $args['desc'] ) . '</span>';
		}

		$output .= '<input type="text" name="' . esc_attr( $args['name'] ) . '" id="' . esc_attr( $args['id'] ) . '" autocomplete="' . esc_attr( $args['autocomplete'] ) . '" value="' . esc_attr( $args['value'] ) . '" placeholder="' . esc_attr( $args['placeholder'] ) . '" class="' . $class . '" ' . $data . $disabled . $required . '/>';

		$output .= '</span>';

		return $output;
	}

	/**
	 * Renders a date picker
	 *
	 * @since 2.4
	 *
	 * @param array $args Arguments for the text field
	 *
	 * @return string Datepicker field
	 */
	public function date_field( $args = array() ) {

		if ( empty( $args['class'] ) ) {
			$args['class']          = 'edd_datepicker';
			$args['data']['format'] = edd_get_date_picker_format();

		} elseif ( ! strpos( $args['class'], 'edd_datepicker' ) ) {
			$args['class']          .= ' edd_datepicker';
			$args['data']['format'] = edd_get_date_picker_format();
		}

		return $this->text( $args );
	}

	/**
	 * Renders an HTML textarea
	 *
	 * @since 1.9
	 *
	 * @param array $args Arguments for the textarea
	 *
	 * @return string textarea
	 */
	public function textarea( $args = array() ) {
		$defaults = array(
			'name'     => 'textarea',
			'value'    => null,
			'label'    => null,
			'desc'     => null,
			'class'    => 'large-text',
			'disabled' => false,
		);

		$args = wp_parse_args( $args, $defaults );

		$class    = implode( ' ', array_map( 'sanitize_html_class', explode( ' ', $args['class'] ) ) );
		$disabled = '';
		if ( $args['disabled'] ) {
			$disabled = ' disabled="disabled"';
		}

		$output = '<span id="edd-' . edd_sanitize_key( $args['name'] ) . '-wrap">';

		if ( ! empty( $args['label'] ) ) {
			$output .= '<label class="edd-label" for="' . edd_sanitize_key( $args['name'] ) . '">' . esc_html( $args['label'] ) . '</label>';
		}

		$output .= '<textarea name="' . esc_attr( $args['name'] ) . '" id="' . edd_sanitize_key( $args['name'] ) . '" class="' . $class . '"' . $disabled . '>' . esc_attr( $args['value'] ) . '</textarea>';

		if ( ! empty( $args['desc'] ) ) {
			$output .= '<span class="edd-description">' . esc_html( $args['desc'] ) . '</span>';
		}

		$output .= '</span>';

		return $output;
	}

	/**
	 * Renders an ajax user search field
	 *
	 * @since 2.0
	 *
	 * @param array $args
	 *
	 * @return string text field with ajax search
	 */
	public function ajax_user_search( $args = array() ) {

		// Parse args
		$args = wp_parse_args( $args, array(
			'id'           => 'user_id',
			'name'         => 'user_id',
			'value'        => null,
			'placeholder'  => __( 'Enter Username', 'easy-digital-downloads' ),
			'label'        => null,
			'desc'         => null,
			'class'        => 'edd-user-dropdown',
			'disabled'     => false,
			'autocomplete' => 'off',
			'data'         => false,
		) );

		// Setup the AJAX class
		$args['class'] = 'edd-ajax-user-search ' . sanitize_html_class( $args['class'] );

		// Concatenate output
		$output  = '<span class="edd_user_search_wrap">';
		$output .= $this->text( $args );
		$output .= '<span class="edd_user_search_results hidden"><span></span></span>';
		$output .= '<span class="spinner"></span>';
		$output .= '</span>';

		return $output;
	}

	/**
	 * Show a required indicator on a field.
	 *
	 * @return string
	 */
	public function show_required() {

		$output  = '<span class="edd-required-indicator" aria-hidden="true">*</span>';
		$output .= sprintf( '<span class="screen-reader-text">%s</span>', __( 'Required', 'easy-digital-downloads' ) );

		return $output;
	}
}
