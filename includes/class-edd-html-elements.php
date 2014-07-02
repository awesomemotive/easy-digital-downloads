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
	public function product_dropdown( $args = array() ) {

		$defaults = array(
			'name'        => 'products',
			'id'          => 'products',
			'class'       => '',
			'multiple'    => false,
			'selected'    => 0,
			'chosen'      => false,
			'number'      => 30
		);

		$args = wp_parse_args( $args, $defaults );

		$products = get_posts( array(
			'post_type'      => 'download',
			'orderby'        => 'title',
			'order'          => 'ASC',
			'posts_per_page' => $args['number']
		) );

		$options = array();

		if ( $products ) {
			foreach ( $products as $product ) {
				$options[ absint( $product->ID ) ] = esc_html( $product->post_title );
			}
		} else {
			$options[0] = __( 'No products found', 'edd' );
		}

		// This ensures that any selected products are included in the drop down
		if( is_array( $args['selected'] ) ) {
			foreach( $args['selected'] as $item ) {
				if( ! in_array( $item, $options ) ) {
					$options[$item] = get_the_title( $item );
				}
			}
		} else {
			if( ! in_array( $args['selected'], $options ) ) {
				$options[$args['selected']] = get_the_title( $args['selected'] );
			}
		}

		$output = $this->select( array(
			'name'             => $args['name'],
			'selected'         => $args['selected'],
			'id'               => $args['id'],
			'class'            => $args['class'],
			'options'          => $options,
			'multiple'         => $args['multiple'],
			'chosen'           => $args['chosen'],
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
			'show_option_none' => false,
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
			'show_option_none' => false
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
		$options  = array();

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
		$selected = empty( $selected ) ? date( 'n' ) : $selected;

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
	 * @since 1.6
	 *
	 * @param array $args
	 *
	 * @return string
	 */
	public function select( $args = array() ) {
		$defaults = array(
			'options'          => array(),
			'name'             => null,
			'class'            => '',
			'id'               => '',
			'selected'         => 0,
			'chosen'           => false,
			'multiple'         => false,
			'show_option_all'  => _x( 'All', 'all dropdown items', 'edd' ),
			'show_option_none' => _x( 'None', 'no dropdown items', 'edd' )
		);

		$args = wp_parse_args( $args, $defaults );


		if( $args['multiple'] ) {
			$multiple = ' MULTIPLE';
		} else {
			$multiple = '';
		}

		if( $args['chosen'] ) {
			$args['class'] .= ' edd-select-chosen';
		}

		$output = '<select name="' . esc_attr( $args[ 'name' ] ) . '" id="' . esc_attr( sanitize_key( str_replace( '-', '_', $args[ 'id' ] ) ) ) . '" class="edd-select ' . esc_attr( $args[ 'class'] ) . '"' . $multiple . '>';

		if ( ! empty( $args[ 'options' ] ) ) {
			if ( $args[ 'show_option_all' ] ) {
				if( $args['multiple'] ) {
					$selected = selected( true, in_array( 0, $args['selected'] ), false );
				} else {
					$selected = selected( $args['selected'], 0, false );
				}
				$output .= '<option value="all"' . $selected . '>' . esc_html( $args[ 'show_option_all' ] ) . '</option>';
			}

			if ( $args[ 'show_option_none' ] ) {
				if( $args['multiple'] ) {
					$selected = selected( true, in_array( -1, $args['selected'] ), false );
				} else {
					$selected = selected( $args['selected'], -1, false );
				}
				$output .= '<option value="-1"' . $selected . '>' . esc_html( $args[ 'show_option_none' ] ) . '</option>';
			}

			foreach( $args[ 'options' ] as $key => $option ) {

				if( $args['multiple'] && is_array( $args['selected'] ) ) {
					$selected = selected( true, in_array( $key, $args['selected'] ), false );
				} else {
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
	 *
	 * @param array $args
	 *
	 * @return string
	 */
	public function checkbox( $args = array() ) {
		$defaults = array(
			'name'     => null,
			'current'  => null,
			'class'    => 'edd-checkbox'
		);

		$args = wp_parse_args( $args, $defaults );

		$output = '<input type="checkbox" name="' . esc_attr( $args[ 'name' ] ) . '" id="' . esc_attr( $args[ 'name' ] ) . '" class="' . $args[ 'class' ] . ' ' . esc_attr( $args[ 'name'] ) . '" ' . checked( 1, $args[ 'current' ], false ) . ' />';

		return $output;
	}

	/**
	 * Renders an HTML Text field
	 *
	 * @since 1.5.2
	 *
	 * @param string $name Name attribute of the text field
	 * @param string $value The value to prepopulate the field with
	 * @param string $label
	 * @param string $desc
	 * @return string Text field
	 */
	public function text( $args = array() ) {
		// Backwards compatabliity
		if ( func_num_args() > 1 ) {
			$args = func_get_args();

			$name  = $args[0];
			$value = isset( $args[1] ) ? $args[1] : '';
			$label = isset( $args[2] ) ? $args[2] : '';
			$desc  = isset( $args[3] ) ? $args[3] : '';
		}

		$defaults = array(
			'name'         => isset( $name )  ? $name  : 'text',
			'value'        => isset( $value ) ? $value : null,
			'label'        => isset( $label ) ? $label : null,
			'desc'         => isset( $desc )  ? $desc  : null,
			'placeholder'  => '',
			'class'        => 'regular-text',
			'disabled'     => false,
			'autocomplete' => ''
		);

		$args = wp_parse_args( $args, $defaults );

		$disabled = '';
		if( $args['disabled'] ) {
			$disabled = ' disabled="disabled"';
		}

		$output = '<span id="edd-' . sanitize_key( $args[ 'name' ] ) . '-wrap">';
			
			$output .= '<label class="edd-label" for="edd-' . sanitize_key( $args[ 'name' ] ) . '">' . esc_html( $args[ 'label' ] ) . '</label>';

			if ( ! empty( $args[ 'desc' ] ) ) {
				$output .= '<span class="edd-description">' . esc_html( $args[ 'desc' ] ) . '</span>';
			}

			$output .= '<input type="text" name="' . esc_attr( $args[ 'name' ] ) . '" id="' . esc_attr( $args[ 'name' ] )  . '" autocomplete="' . esc_attr( $args[ 'autocomplete' ] )  . '" value="' . esc_attr( $args[ 'value' ] ) . '" placeholder="' . esc_attr( $args[ 'placeholder' ] ) . '" class="' . $args[ 'class' ] . '"' . $disabled . '/>';

		$output .= '</span>';

		return $output;
	}

	/**
	 * Renders an HTML textarea
	 *
	 * @since 1.9
	 *
	 * @param string $name Name attribute of the textarea
	 * @param string $value The value to prepopulate the field with
	 * @param string $label
	 * @param string $desc
	 * @return string textarea
	 */
	public function textarea( $args = array() ) {
		$defaults = array(
			'name'        => 'textarea',
			'value'       => null,
			'label'       => null,
			'desc'        => null,
            'class'       => 'large-text',
			'disabled'    => false
		);

		$args = wp_parse_args( $args, $defaults );

		$disabled = '';
		if( $args['disabled'] ) {
			$disabled = ' disabled="disabled"';
		}

		$output = '<span id="edd-' . sanitize_key( $args[ 'name' ] ) . '-wrap">';

			$output .= '<label class="edd-label" for="edd-' . sanitize_key( $args[ 'name' ] ) . '">' . esc_html( $args[ 'label' ] ) . '</label>';

			$output .= '<textarea name="' . esc_attr( $args[ 'name' ] ) . '" id="' . esc_attr( $args[ 'name' ] ) . '" class="' . $args[ 'class' ] . '"' . $disabled . '>' . esc_attr( $args[ 'value' ] ) . '</textarea>';

			if ( ! empty( $args[ 'desc' ] ) ) {
				$output .= '<span class="edd-description">' . esc_html( $args[ 'desc' ] ) . '</span>';
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
	 * @return string text field with ajax search
	 */
	public function ajax_user_search( $args = array() ) {

		$defaults = array(
			'name'        => 'user_id',
			'value'       => null,
			'placeholder' => __( 'Enter username', 'edd' ),
			'label'       => null,
			'desc'        => null,
            'class'       => '',
			'disabled'    => false,
			'autocomplete'=> 'off'
		);

		$args = wp_parse_args( $args, $defaults );

		$args['class'] = 'edd-ajax-user-search ' . $args['class'];

		$output  = '<span class="edd_user_search_wrap">'; 
			$output .= $this->text( $args );
			$output .= '<span class="edd_user_search_results"></span>';
		$output .= '</span>';

		return $output;
	}
}
