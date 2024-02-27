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

namespace EDD\HTML;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Elements Class
 *
 * @since 1.5
 */
class Elements {

	/**
	 * Renders an HTML Dropdown of all the Products (Downloads)
	 *
	 * @since 1.5
	 * @since 3.2.8 Updated to use the ProductSelect class.
	 * @param array $args Arguments for the dropdown.
	 *
	 * @return string $output Product dropdown
	 */
	public function product_dropdown( $args = array() ) {
		$select = new ProductSelect( $args );

		return $select->get();
	}

	/**
	 * Get EDD products for the product dropdown.
	 *
	 * @param array $args     Parameters for the get_posts function.
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
	 * @param array $args Arguments for the dropdown.
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

		$customers = edd_get_customers(
			array(
				'number' => $args['number'],
			)
		);

		$options = array();

		if ( $customers ) {
			$options[0] = $args['none_selected'];
			foreach ( $customers as $customer ) {
				$options[ absint( $customer->id ) ] = esc_html( $customer->name . ' (' . $customer->email . ')' );
			}
		} else {
			$options[0] = __( 'No customers found', 'easy-digital-downloads' );
		}

		// If a selected customer has been specified, we need to ensure it's in the initial list of customers displayed.
		if ( ! empty( $args['selected'] ) && ! array_key_exists( $args['selected'], $options ) ) {
			$customer = edd_get_customer( $args['selected'] );

			if ( $customer ) {
				$options[ absint( $args['selected'] ) ] = esc_html( $customer->name . ' (' . $customer->email . ')' );
			}
		}

		return $this->select(
			array(
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
			)
		);
	}

	/**
	 * Renders an HTML Dropdown of all the Users
	 *
	 * @since 2.6.9
	 *
	 * @param array $args Arguments for the dropdown.
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
		// If a selected user has been specified, we need to ensure it's in the initial list of user displayed.
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

		return $this->select(
			array(
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
			)
		);
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
			$args = wp_parse_args(
				array(
					'name'     => $name,
					'selected' => $selected,
					'nopaging' => true,
				),
				$defaults
			);
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

		return $this->select(
			array(
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
			)
		);
	}

	/**
	 * Renders an HTML Dropdown of all the Categories
	 *
	 * @since 1.5.2
	 *
	 * @param string $name     Name attribute of the dropdown.
	 * @param int    $selected Category to select automatically.
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

		return $this->select(
			array(
				'name'             => $name,
				'selected'         => $selected,
				'options'          => $options,
				'show_option_all'  => sprintf(
					/* translators: %s: Download Category taxonomy name */
					_x( 'All %s', 'plural: Example: "All Categories"', 'easy-digital-downloads' ),
					$category_labels['name']
				),
				'show_option_none' => false,
			)
		);
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
			++$start_year;
		}

		return $this->select(
			array(
				'name'             => $name,
				'id'               => $id . '_' . $name,
				'selected'         => $selected,
				'options'          => $options,
				'show_option_all'  => false,
				'show_option_none' => false,
			)
		);
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
			++$month;
		}

		return $this->select(
			array(
				'name'             => $name,
				'id'               => $id . '_' . $name,
				'selected'         => $selected,
				'options'          => $options,
				'show_option_all'  => false,
				'show_option_none' => false,
			)
		);
	}

	/**
	 * Gets the countries dropdown.
	 *
	 * @since  3.0
	 * @param  array  $args    The array of parameters passed to the method.
	 * @param  string $country The selected country.
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
	 * @param  array  $args     The array of parameters passed to the method.
	 * @param  string $country  The country from which to populate the regions.
	 * @param  string $region   The selected region.
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
	 * @since 3.2.8 Updated to use the Select class.
	 * @param array $args The arguments for the dropdown.
	 * @return string
	 */
	public function select( $args = array() ) {
		$select = new Select( $args );

		return $select->get();
	}

	/**
	 * Renders an HTML Checkbox
	 *
	 * @since 1.9
	 * @since 3.0 Added `label` argument.
	 * @since 3.2.8 Updated to use the Checkbox class.
	 * @param array $args Arguments for the checkbox.
	 * @return string Checkbox HTML code
	 */
	public function checkbox( $args = array() ) {
		$checkbox = new Checkbox( $args );

		return $checkbox->get();
	}

	/**
	 * Renders an HTML Text field
	 *
	 * @since 1.5.2
	 * @since 3.2.8 Updated to use the Text class.
	 * @param array $args Arguments for the text field.
	 * @return string Text field
	 */
	public function text( $args = array() ) {
		if ( func_num_args() > 1 ) {
			$legacy_args = func_get_args();
			$args        = array(
				'name'  => $legacy_args[0],
				'value' => isset( $legacy_args[1] ) ? $legacy_args[1] : '',
				'label' => isset( $legacy_args[2] ) ? $legacy_args[2] : '',
				'desc'  => isset( $legacy_args[3] ) ? $legacy_args[3] : '',
			);
		}

		$text = new Text( $args );

		return $text->get();
	}

	/**
	 * Renders a date picker
	 *
	 * @since 2.4
	 *
	 * @param array $args Arguments for the text field.
	 *
	 * @return string Datepicker field
	 */
	public function date_field( $args = array() ) {

		if ( empty( $args['class'] ) ) {
			$args['class']          = 'edd_datepicker';
			$args['data']['format'] = edd_get_date_picker_format();

		} elseif ( ! strpos( $args['class'], 'edd_datepicker' ) ) {
			$args['class']         .= ' edd_datepicker';
			$args['data']['format'] = edd_get_date_picker_format();
		}

		return $this->text( $args );
	}

	/**
	 * Renders an HTML textarea
	 *
	 * @since 1.9
	 * @since 3.2.8 Updated to use the Textarea class.
	 * @param array $args Arguments for the textarea.
	 * @return string textarea
	 */
	public function textarea( $args = array() ) {
		$textarea = new Textarea( $args );

		return $textarea->get();
	}

	/**
	 * Renders an ajax user search field
	 *
	 * @since 2.0
	 *
	 * @param array $args Arguments for the field.
	 *
	 * @return string text field with ajax search
	 */
	public function ajax_user_search( $args = array() ) {

		$args = wp_parse_args(
			$args,
			array(
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
			)
		);

		// Setup the AJAX class.
		$args['class'] = 'edd-ajax-user-search ' . sanitize_html_class( $args['class'] );

		// Concatenate output.
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
