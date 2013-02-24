<?php
/**
 * HTML elements
 *
 * A helper class for outputting common HTML elements, such as product drop downs
 *
 * @package     Easy Digital Downloads
 * @subpackage  HTML Elements
 * @copyright   Copyright (c) 2012, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.5
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * HTML Elements Class
 *
 * @access      public
 * @since       1.5
 */
class EDD_HTML_Elements {


	/**
	 * Renders and HTML product drop down
	 *
	 * @access      public
	 * @since       1.5
	 */
	public function product_dropdown( $name = 'edd_products' ) {

		$products = get_posts( array( 'post_type' => 'download', 'nopaging' => true ) );

		$output = '<select name="' . esc_attr( $name ) . '" id="' . esc_attr( $name ) . '">';

		if( $products ) {
			foreach( $products as $product ) {
				$output .= '<option value="' . absint( $product->ID ) . '">' . esc_html( get_the_title( $product->ID ) ) . '</option>';
			}
		} else {
			$output .= '</option value="0">' . __( 'No products found', 'edd' ) . '</option>';
		}

		$output .= '</select>';

		return $output;

	}


}