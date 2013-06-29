<?php
/**
 * HTML elements
 *
 * A helper class for outputting common HTML elements, such as product drop downs
 *
 * @package     EDD
 * @subpackage  Classes/HTML
 * @copyright   Copyright (c) 2012, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.5
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * EDD_HTML_Elements Class
 *
 * @since 1.5
 */
class EDD_HTML_Elements {
	/**
	 * Renders an HTML Dropdown of all the Products (Downloads)
	 *
	 * @access public
	 * @since 1.5
	 * @param string $name Name attribute of the dropdown
	 * @param int $selected Download to select automatically
	 * @return string $output Product dropdown
	 */
	public function product_dropdown( $name = 'edd_products', $selected = 0 ) {
		$products = get_posts( array(
			'post_type' => 'download',
			'nopaging'  => true,
			'orderby'   => 'title',
			'order'     => 'ASC'
		) );

		if ( $products ) {
			foreach ( $products as $product ) {
				$options[ absint( $product->ID ) ] = esc_html( get_the_title( $product->ID ) );
			}
		} else {
			$options[0] = __( 'No products found', 'edd' );
		}

		$output = $this->select( array(
			'name'             => $name,
			'selected'         => $selected,
			'options'          => $options,
			'show_option_all'  => false,
			'show_option_none' => false
		) );

		return $output;
	}

	/**
	 * Renders an HTML Dropdown of all the Discounts
	 *
	 * @access public
	 * @since 1.5.2
	 * @param string $name Name attribute of the dropdown
	 * @param int    $selected Discount to select automatically
	 * @param string $status Discount post_status to retrieve
	 * @return string $output Discount dropdown
	 */
	public function discount_dropdown( $name = 'edd_discounts', $selected = 0, $status = '' ) {
		$args = array( 'nopaging' => true );

		if ( ! empty( $status ) )
			$args[ 'post_status' ] = $status;

		$discounts = edd_get_discounts( $args );
		$options   = array();

		if ( $discounts ) {
			foreach ( $discounts as $discount ) {
				$options[ absint( $discount->ID ) ] = esc_html( get_the_title( $discount->ID ) );
			}
		} else {
			$options[0] = __( 'No discounts found', 'edd' );
		}

		$output = $this->select( array(
			'name'             => $name,
			'selected'         => $selected,
			'options'          => $options,
			'show_option_all'  => false,
			'show_option_none' => false
		) );

		return $output;
	}

	/**
	 * Renders an HTML Dropdown of all the Categories
	 *
	 * @access public
	 * @since 1.5.2
	 * @param string $name Name attribute of the dropdown
	 * @param int    $selected Category to select automatically
	 * @return string $output Category dropdown
	 */
	public function category_dropdown( $name = 'edd_categories', $selected = 0 ) {
		$categories = get_terms( 'download_category', apply_filters( 'edd_category_dropdown', array() ) );
		$options    = array();

		foreach ( $categories as $category ) {
			$options[ absint( $category->term_id ) ] = esc_html( $category->name );
		}

		$output = $this->select( array(
			'name'             => $name,
			'selected'         => $selected,
			'options'          => $options,
			'show_option_all'  => __( 'All Categories', 'edd' ),
			'show_option_none' => __( 'No categories found', 'edd' )
		) );

		return $output;
	}

	/**
	 * Renders an HTML Dropdown of years
	 *
	 * @access public
	 * @since 1.5.2
	 * @param string $name Name attribute of the dropdown
	 * @param int    $selected Year to select automatically
	 * @return string $output Year dropdown
	 */
	public function year_dropdown( $name = 'year', $selected = 0 ) {
		$current  = date( 'Y' );
		$year     = $current - 5;
		$selected = empty( $selected ) ? date( 'Y' ) : $selected;

		while ( $year <= $current ) {
			$options[ absint( $year ) ] = $year;
			$year++;
		}

		$output = $this->select( array(
			'name'             => $name,
			'selected'         => $selected,
			'options'          => $options,
			'show_option_all'  => false,
			'show_option_none' => false
		) );

		return $output;
	}

	/**
	 * Renders an HTML Dropdown of months
	 *
	 * @access public
	 * @since 1.5.2
	 * @param string $name Name attribute of the dropdown
	 * @param int    $selected Month to select automatically
	 * @return string $output Month dropdown
	 */
	public function month_dropdown( $name = 'month', $selected = 0 ) {
		$month   = 1;
		$options = array();

		while ( $month <= 12 ) {
			$options[ absint( $month ) ] = edd_month_num_to_name( $month );
			$month++;
		}

		$output = $this->select( array(
			'name'             => $name,
			'selected'         => $selected,
			'options'          => $options,
			'show_option_all'  => false,
			'show_option_none' => false
		) );

		return $output;
	}

	/**
	 * Renders an HTML Dropdown
	 *
	 * @access public
	 * @since 1.6
	 * @param string $options Options of the dropdown
	 * @param string $name Name attribute of the dropdown
	 * @param int    $selected Option key to select by default
	 * @return string $output The dropdown
	 */

	public function select( $args = array()) {
		$defaults = array(
			'options'          => array(),
			'name'             => null,
			'selected'         => 0,
			'show_option_all'  => _x( 'All', 'all dropdown items', 'edd' ),
			'show_option_none' => _x( 'None', 'no dropdown items', 'edd' )
		);

		$args = wp_parse_args( $args, $defaults );

		$output = '<select name="' . esc_attr( $args[ 'name' ] ) . '" id="' . esc_attr( $args[ 'name' ] ) . '" class="edd-select ' . esc_attr( $args[ 'name'] ) . '">';

		if ( ! empty( $args[ 'options' ] ) ) {
			if ( $args[ 'show_option_all' ] )
				$output .= '<option value="0"' . selected( $args['selected'], 0, false ) . '>' . esc_html( $args[ 'show_option_all' ] ) . '</option>';

			if ( $args[ 'show_option_none' ] )
				$output .= '<option value="-1"' . selected( $args['selected'], -1, false ) . '>' . esc_html( $args[ 'show_option_none' ] ) . '</option>';

			foreach( $args[ 'options' ] as $key => $option ) {
				$output .= '<option value="' . esc_attr( $key ) . '"' . selected( $args['selected'], $key, false ) . '>' . esc_html( $option ) . '</option>';
			}
		}

		$output .= '</select>';

		return $output;
	}

	/**
	 * Renders an HTML Text field
	 *
	 * @access public
	 * @since 1.5.2
	 * @param string $name Name attribute of the text field
	 * @param string $value The value to prepopulate the field with
	 * @return string $output Text field
	 */
	public function text( $name = 'text', $value = '', $label = '', $desc = '' ) {
		$output = '<p id="edd-' . sanitize_key( $name ) . '-wrap">';
			$output .= '<label class="edd-label" for="edd-' . sanitize_key( $name ) . '">' . esc_html( $label ) . '</label>';
			if ( ! empty( $desc ) )
				$output .= '<span class="edd-description">' . esc_html( $desc ) . '</span>';
			$output = '<input type="text" name="' . esc_attr( $name ) . '" id="' . esc_attr( $name )  . '" value="' . esc_attr( $value ) . '"/>';
		$output .= '</p>';

		return $output;
	}
}
