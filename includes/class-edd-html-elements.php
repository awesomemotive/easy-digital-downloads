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
		$products = get_posts( array( 'post_type' => 'download', 'nopaging' => true, 'orderby' => 'title', 'order' => 'ASC' ) );

		$output = '<select name="' . esc_attr( $name ) . '" id="' . esc_attr( $name ) . '">';

		if ( $products ) {
			foreach ( $products as $product ) {
				$output .= '<option value="' . absint( $product->ID ) . '"' . selected( $selected, $product->ID, false ) . '>' . esc_html( get_the_title( $product->ID ) ) . '</option>';
			}
		} else {
			$output .= '<option value="0">' . __( 'No products found', 'edd' ) . '</option>';
		}

		$output .= '</select>';

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
			$args['post_status'] = $status;

		$discounts = edd_get_discounts( $args );

		$output = '<select name="' . esc_attr( $name ) . '" id="' . esc_attr( $name ) . '">';

		if ( $discounts ) {
			foreach ( $discounts as $discount ) {
				$output .= '<option value="' . absint( $discount->ID ) . '"' . selected( $selected, $discount->ID, false ) . '>' . esc_html( get_the_title( $discount->ID ) ) . '</option>';
			}
		} else {
			$output .= '<option value="0">' . __( 'No discounts found', 'edd' ) . '</option>';
		}

		$output .= '</select>';

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
		$categories = get_terms( 'download_category' );

		$output = '<select name="' . esc_attr( $name ) . '" id="' . esc_attr( $name ) . '">';

		$output .= '<option value="0">' . __( 'All Categories', 'edd' ) . '</option>';
		if ( $categories ) {
			foreach ( $categories as $category ) {
				$output .= '<option value="' . absint( $category->term_id ) . '"' . selected( $selected, $category->term_id, false ) . '>' . esc_html( $category->name ) . '</option>';
			}
		} else {
			$output .= '<option value="0">' . __( 'No categories found', 'edd' ) . '</option>';
		}

		$output .= '</select>';

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

		$output = '<select name="' . esc_attr( $name ) . '" id="' . esc_attr( $name ) . '">';

		while ( $year <= $current ) {
			$output .= '<option value="' . absint( $year ) . '"' . selected( $selected, $year, false ) . '>' . $year . '</option>';
			$year++;
		}

		$output .= '</select>';

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
		$output  = '<select name="' . esc_attr( $name ) . '" id="' . esc_attr( $name ) . '">';

		while ( $month <= 12 ) {
			$output .= '<option value="' . absint( $month ) . '"' . selected( $selected, $month, false ) . '>' . edd_month_num_to_name( $month ) . '</option>';
			$month++;
		}

		$output .= '</select>';

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
	public function select( $options = array(), $name = 'year', $selected = 0 ) {

		$output = '<select name="' . esc_attr( $name ) . '" id="' . esc_attr( $name ) . '" class="edd-select ' . esc_attr( $name ) . '">';

		foreach( $options as $key => $option ) {
			$output .= '<option value="' . esc_attr( $key ) . '"' . selected( $selected, $key, false ) . '>' . esc_html( $option ) . '</option>';
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