<?php
/**
 * Cart Object
 *
 * @package     EDD
 * @subpackage  Classes/Cart
 * @copyright   Copyright (c) 2016, Sunny Ratilal
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       2.7
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * EDD_Cart Class
 *
 * @since 2.7
 */
class EDD_Cart {
	/**
	 * Cart contents
	 *
	 * @var array
	 * @since 2.7
	 */
	public $contents = array();

	/**
	 * Details of the cart contents
	 *
	 * @var array
	 * @since 2.7
	 */
	public $details = array();

	/**
	 * Cart Quantity
	 *
	 * @var int
	 * @since 2.7
	 */
	public $quantity = 0;

	/**
	 * Subtotal
	 *
	 * @var float
	 * @since 2.7
	 */
	public $subtotal = 0.00;

	/**
	 * Total
	 *
	 * @var float
	 * @since 2.7
	 */
	public $total = 0.00;

	/**
	 * Fees
	 *
	 * @var array
	 * @since 2.7
	 */
	public $fees = array();

	/**
	 * Tax
	 *
	 * @var float
	 * @since 2.7
	 */
	public $tax = 0.00;

	/**
	 * Purchase Session
	 *
	 * @var array
	 * @since 2.7
	 */
	public $session;

	/**
	 * Discount codes
	 *
	 * @var array
	 * @since 2.7
	 */
	public $discounts;

	/**
	 * Cart saving
	 *
	 * @var bool
	 * @since 2.7
	 */
	public $saving;

	/**
	 * Saved cart
	 *
	 * @var array
	 * @since 2.7
	 */
	public $saved;

	/**
	 * Constructor.
	 *
	 * @since 2.7
	 * @access public
	 */
	public function __construct() {
		$this->setup_cart();
	}

	/**
	 * Sets up cart components
	 *
	 * @since  2.7
	 * @access private
	 * @return void
	 */
	private function setup_cart() {
		$this->get_contents_from_session();
		$this->get_contents();
		$this->get_contents_details();
		// $this->quantity = $this->get_quantity();
		$this->get_all_fees();
		// $this->discounts = EDD()->session->get( 'cart_discounts' );
		// $this->saving = edd_get_option( 'enable_cart_saving', false );
		// $this->saved = get_user_meta( get_current_user_id(), 'edd_saved_cart', true );
	}

	/**
	 * Populate the cart with the data stored in the session
	 *
	 * @since 2.7
	 * @access public
	 * @return void
	 */
	public function get_contents_from_session() {
		$cart = EDD()->session->get( 'edd_cart' );
		$this->contents = $cart;

		do_action( 'edd_cart_contents_loaded_from_session', $this );
	}

	/**
	 * Get cart contents
	 *
	 * @since 2.7
	 * @access public
	 * @return void
	 */
	public function get_contents() {
		$this->get_contents_from_session();

		$cart = ! empty( $this->contents ) ? array_values( $this->contents ) : array();
		$cart_count = count( $cart );

		foreach ( $cart as $key => $item ) {
			$download = new EDD_Download( $item['id'] );

			// If the item is not a download or it's status has changed since it was added to the cart.
			if ( empty( $download->ID ) || ! $download->can_purchase() ) {
				unset( $cart[ $key ] );
			}
		}

		// We've removed items, reset the cart session
		if ( count( $cart ) < $cart_count ) {
			$this->contents = $cart;
			$this->update_cart();
		}

		$this->contents = apply_filters( 'edd_cart_contents', $cart );

		return $this->contents;
	}

	/**
	 * Get cart contents details
	 *
	 * @since 2.7
	 * @access public
	 * @return void
	 */
	public function get_contents_details() {
		global $edd_is_last_cart_item, $edd_flat_discount_total;

		$cart_items = $this->get_contents();

		if ( empty( $cart_items ) ) {
			return false;
		}

		$details = array();
		$length  = count( $cart_items ) - 1;

		foreach ( $cart_items as $key => $item ) {
			if( $key >= $length ) {
				$edd_is_last_cart_item = true;
			}

			$item['quantity'] = edd_item_quantities_enabled() ? absint( $item['quantity'] ) : 1;

			$price_id = isset( $item['options']['price_id'] ) ? $item['options']['price_id'] : null;

			$item_price = edd_get_cart_item_price( $item['id'], $item['options'] );
			$discount   = edd_get_cart_item_discount_amount( $item );
			$discount   = apply_filters( 'edd_get_cart_content_details_item_discount_amount', $discount, $item );
			$quantity   = edd_get_cart_item_quantity( $item['id'], $item['options'] );
			$fees       = edd_get_cart_fees( 'fee', $item['id'], $price_id );
			$subtotal   = $item_price * $quantity;
			$tax        = edd_get_cart_item_tax( $item['id'], $item['options'], $subtotal - $discount );

			foreach ( $fees as $fee ) {
				if ( $fee['amount'] < 0 ) {
					$subtotal += $fee['amount'];
				}
			}

			if ( edd_prices_include_tax() ) {
				$subtotal -= round( $tax, edd_currency_decimal_filter() );
			}

			$total = $subtotal - $discount + $tax;

			if ( $total < 0 ) {
				$total = 0;
			}

			$details[ $key ]  = array(
				'name'        => get_the_title( $item['id'] ),
				'id'          => $item['id'],
				'item_number' => $item,
				'item_price'  => round( $item_price, edd_currency_decimal_filter() ),
				'quantity'    => $quantity,
				'discount'    => round( $discount, edd_currency_decimal_filter() ),
				'subtotal'    => round( $subtotal, edd_currency_decimal_filter() ),
				'tax'         => round( $tax, edd_currency_decimal_filter() ),
				'fees'        => $fees,
				'price'       => round( $total, edd_currency_decimal_filter() )
			);

			if ( $edd_is_last_cart_item ) {
				$edd_is_last_cart_item   = false;
				$edd_flat_discount_total = 0.00;
			}
		}

		$this->details = $details;
		return $this->details;
	}

	/**
	 * Update Cart
	 *
	 * @since 2.7
	 * @access public
	 * @return void
	 */
	public function update_cart() {
		EDD()->session->set( 'edd_cart', $this->contents );
	}

	/**
	 * Get Discounts
	 *
	 * @since 2.7
	 * @access public
	 * @return mixed array|false
	 */
	public function get_discounts() {
		return ! empty( $this->discounts ) ? explode( '|', $this->discounts ) : false;
	}

	/**
	 * Checks if any discounts have been applied to the cart
	 *
	 * @since 2.7
	 * @access public
	 * @return bool
	 */
	public function has_discounts() {
		$has_discounts = false;

		if ( $this->get_discounts() ) {
			$has_discounts = true;
		}

		return apply_filters( 'edd_cart_has_discounts', $has_discounts );
	}

	/**
	 * Get quantity
	 *
	 * @since 2.7
	 * @access public
	 * @return int
	 */
	public function get_quantity() {
		$total_quantity = 0;
		$cart = $this->contents;

		if ( ! empty( $cart ) ) {
			$quantities     = wp_list_pluck( $cart, 'quantity' );
			$total_quantity = absint( array_sum( $quantities ) );
		}

		$this->quantity = apply_filters( 'edd_get_cart_quantity', $total_quantity, $cart );
		return $this->quantity;
	}

	/**
	 * Checks if the cart is empty
	 *
	 * @since 2.7
	 * @access public
	 * @return boolean
	 */
	public function is_empty() {
		return 0 === sizeof( $this->contents );
	}

	/**
	 * Add to cart
	 *
	 * As of EDD 2.7, items can only be added to the cart when the object passed extends EDD_Cart_Item
	 *
	 * @since 2.7
	 * @access public
	 * @return array $cart Updated cart object
	 */
	public function add( $download_id, $options = array() ) {
		$download = new EDD_Download( $download_id );

		if ( empty( $download->ID ) ) {
			return; // Not a download product
		}

		if ( ! $download->can_purchase() ) {
			return; // Do not allow draft/pending to be purchased if can't edit. Fixes #1056
		}

		do_action( 'edd_pre_add_to_cart', $download_id, $options );

		$this->contents = apply_filters( 'edd_pre_add_to_cart_contents', $this->get_contents() );

		if ( edd_has_variable_prices( $download_id )  && ! isset( $options['price_id'] ) ) {
			// Forces to the first price ID if none is specified and download has variable prices
			$options['price_id'] = '0';
		}

		if ( isset( $options['quantity'] ) ) {
			if ( is_array( $options['quantity'] ) ) {
				$quantity = array();
				foreach ( $options['quantity'] as $q ) {
					$quantity[] = absint( preg_replace( '/[^0-9\.]/', '', $q ) );
				}
			} else {
				$quantity = absint( preg_replace( '/[^0-9\.]/', '', $options['quantity'] ) );
			}

			unset( $options['quantity'] );
		} else {
			$quantity = 1;
		}

		// If the price IDs are a string and is a coma separted list, make it an array (allows custom add to cart URLs)
		if ( isset( $options['price_id'] ) && ! is_array( $options['price_id'] ) && false !== strpos( $options['price_id'], ',' ) ) {
			$options['price_id'] = explode( ',', $options['price_id'] );
		}

		$items = array();

		if ( isset( $options['price_id'] ) && is_array( $options['price_id'] ) ) {
			// Process multiple price options at once
			foreach ( $options['price_id'] as $key => $price ) {
				$items[] = array(
					'id'           => $download_id,
					'options'      => array(
						'price_id' => preg_replace( '/[^0-9\.-]/', '', $price )
					),
					'quantity'     => $quantity[ $key ],
				);
			}
		} else {
			// Sanitize price IDs
			foreach( $options as $key => $option ) {
				if ( 'price_id' == $key ) {
					$options[ $key ] = preg_replace( '/[^0-9\.-]/', '', $option );
				}
			}

			// Add a single item
			$items[] = array(
				'id'       => $download_id,
				'options'  => $options,
				'quantity' => $quantity
			);
		}

		foreach ( $items as &$item ) {
			$item = apply_filters( 'edd_add_to_cart_item', $item );
			$to_add = $item;

			if ( ! is_array( $to_add ) ) {
				return;
			}

			if ( ! isset( $to_add['id'] ) || empty( $to_add['id'] ) ) {
				return;
			}

			if ( edd_item_in_cart( $to_add['id'], $to_add['options'] ) && edd_item_quantities_enabled() ) {
				$key = edd_get_item_position_in_cart( $to_add['id'], $to_add['options'] );

				if ( is_array( $quantity ) ) {
					$this->contents[ $key ]['quantity'] += $quantity[ $key ];
				} else {
					$this->contents[ $key ]['quantity'] += $quantity;
				}
			} else {
				$this->contents[] = $to_add;
			}
		}

		unset( $item );

		EDD()->session->set( 'edd_cart', $this->contents );

		do_action( 'edd_post_add_to_cart', $download_id, $options, $items );

		// Clear all the checkout errors, if any
		edd_clear_errors();

		return count( $this->contents ) - 1;
	}

	/**
	 * Remove from cart
	 *
	 * @since 2.7
	 * @access public
	 * @return array $cart Updated cart object
	 */
	public function remove() {

	}

	/**
	 * Empty the cart
	 *
	 * @since 2.7
	 * @access public
	 * @return void
	 */
	public function empty() {
		// Remove cart contents
		EDD()->session->set( 'edd_cart', null );

		// Remove all cart fees
		EDD()->session->set( 'edd_cart_fees', null );

		// Remove any active discounts
		$this->remove_all_discounts();

		do_action( 'edd_empty_cart' );
	}

	/**
	 * Remove discount from the cart
	 *
	 * @since 2.7
	 * @access public
	 * @return array Discount codes
	 */
	public function remove_discount( $code = '' ) {
		if ( empty( $code ) ) {
			return;
		}

		if ( $this->discounts ) {
			$key = array_search( $code, $this->discounts );

			if ( false !== $key ) {
				unset( $this->discounts[ $key ] );
			}

			$this->discounts = implode( '|', array_values( $this->discounts ) );

			// update the active discounts
			EDD()->session->set( 'cart_discounts', $this->discounts );
		}

		do_action( 'edd_cart_discount_removed', $code, $this->discounts );
		do_action( 'edd_cart_discounts_updated', $this->discounts );

		return $this->discounts;
	}

	/**
	 * Remove all discount codes
	 *
	 * @since 2.7
	 * @access public
	 * @return void
	 */
	public function remove_all_discounts() {
		EDD()->session->set( 'cart_discounts', null );
		do_action( 'edd_cart_discounts_removed' );
	}

	/**
	 * Cart Discount HTML Output
	 *
	 * @since 2.7
	 * @access public
	 * @return mixed string|void
	 */
	public function discount_output( $discounts = false, $echo = false ) {
		if ( ! $discounts ) {
			$discounts = $this->discounts;
		}

		if ( ! $discounts ) {
			return;
		}

		$html = '';

		foreach ( $discounts as $discount ) {
			$discount_id = edd_get_discount_id_by_code( $discount );
			$rate        = edd_format_discount_rate( edd_get_discount_type( $discount_id ), edd_get_discount_amount( $discount_id ) );

			$remove_url  = add_query_arg(
				array(
					'edd_action'    => 'remove_cart_discount',
					'discount_id'   => $discount_id,
					'discount_code' => $discount
				),
				edd_get_checkout_uri()
			);

			$discount_html = '';
			$discount_html .= "<span class=\"edd_discount\">\n";
				$discount_html .= "<span class=\"edd_discount_rate\">$discount&nbsp;&ndash;&nbsp;$rate</span>\n";
				$discount_html .= "<a href=\"$remove_url\" data-code=\"$discount\" class=\"edd_discount_remove\"></a>\n";
			$discount_html .= "</span>\n";

			$html .= apply_filters( 'edd_get_cart_discount_html', $discount_html, $discount, $rate, $remove_url );
		}

		$ouput = apply_filters( 'edd_get_cart_discounts_html', $html, $discounts, $rate, $remove_url );

		if ( ! $echo ) {
			return $output;
		} else {
			echo $output;
		}
	}

	/**
	 * Is item in the cart?
	 *
	 * @since 2.7
	 * @access public
	 * @return bool
	 */
	public function is_item_in_cart() {

	}

	/**
	 * Get the item position from the cart
	 *
	 * @since 2.7
	 * @access public
	 * @return mixed int|false
	 */
	public function item_position() {

	}

	/**
	 * Get Cart Item Quantity
	 *
	 * @since 2.7
	 * @access public
	 * @return int
	 */
	public function item_quantity() {

	}

	/**
	 * Set Cart Item Quantity
	 *
	 * @since 2.7
	 * @access public
	 * @return array $cart Updated cart object
	 */
	public function set_item_quantity() {

	}

	/**
	 * Price of Item in Cart
	 *
	 * @since 2.7
	 * @access public
	 * @return double
	 */
	public function item_price() {

	}

	/**
	 * Final Price of Item in Cart (incl. discounts and taxes)
	 *
	 * @since 2.7
	 * @access public
	 * @return double
	 */
	public function final_item_price() {

	}

	/**
	 * Tax for Item in Cart
	 *
	 * @since 2.7
	 * @access public
	 * @return double
	 */

	/**
	 * Get Cart Fees
	 *
	 * @since 2.7
	 * @access public
	 * @return array Cart fees
	 */
	public function get_fees( $type = 'all', $download_id = 0, $price_id = null ) {
		return EDD()->fees->get_fees( $type, $download_id, $price_id );
	}

	/**
	 * Get All Cart Fees.
	 *
	 * @since 2.7
	 * @access public
	 * @return array
	 */
	public function get_all_fees() {
		$this->fees = EDD()->fees->get_fees( 'all' );
		return $this->fees;
	}

	/**
	 * Get Cart Fee Total
	 *
	 * @since 2.7
	 * @access public
	 * @return double
	 */
	public function get_total_fees() {
		$fee_total = 0.00;
		foreach ( $this->fees as $fee ) {
			if ( ! empty( $fee['download_id'] ) && $fee['amount'] <= 0 ) {
				continue;
			}

			$fee_total += $fee['amount'];
		}

		return apply_filters( 'edd_get_fee_total', $fee_total, $this->fees );
	}

	/**
	 * Is Cart Saving Enabled?
	 *
	 * @since 2.7
	 * @access public
	 * @return bool
	 */
	public function is_saving_enabled() {
		return $this->saving;
	}

	/**
	 * Checks if the cart has been saved
	 *
	 * @since 2.7
	 * @access public
	 * @return bool
	 */
	public function is_saved() {
		if ( ! $this->saving ) {
			return false;
		}

		if ( is_user_logged_in() ) {
			if ( ! $this->saved ) {
				return false;
			}

			if ( $this->saved === EDD()->session->get( 'edd_cart' ) ) {
				return false;
			}

			return true;
		} else {
			if ( ! isset( $_COOKIE['edd_saved_cart'] ) ) {
				return false;
			}

			if ( json_decode( stripslashes( $_COOKIE['edd_saved_cart'] ), true ) === EDD()->session->get( 'edd_cart' ) ) {
				return false;
			}

			return true;
		}
	}

	/**
	 * Save Cart
	 *
	 * @since 2.7
	 * @access public
	 * @return bool
	 */
	public function save() {
		if ( ! $this->is_saving_enabled() ) {
			return false;
		}

		$user_id  = get_current_user_id();
		$cart     = EDD()->session->get( 'edd_cart' );
		$token    = edd_generate_cart_token();
		$messages = EDD()->session->get( 'edd_cart_messages' );

		if ( is_user_logged_in() ) {
			update_user_meta( $user_id, 'edd_saved_cart', $cart,  false );
			update_user_meta( $user_id, 'edd_cart_token', $token, false );
		} else {
			$cart = json_encode( $cart );
			setcookie( 'edd_saved_cart', $cart,  time() + 3600 * 24 * 7, COOKIEPATH, COOKIE_DOMAIN );
			setcookie( 'edd_cart_token', $token, time() + 3600 * 24 * 7, COOKIEPATH, COOKIE_DOMAIN );
		}

		$messages = EDD()->session->get( 'edd_cart_messages' );

		if ( ! $messages ) {
			$messages = array();
		}

		$messages['edd_cart_save_successful'] = sprintf(
			'<strong>%1$s</strong>: %2$s',
			__( 'Success', 'easy-digital-downloads' ),
			__( 'Cart saved successfully. You can restore your cart using this URL:', 'easy-digital-downloads' ) . ' ' . '<a href="' .  edd_get_checkout_uri() . '?edd_action=restore_cart&edd_cart_token=' . $token . '">' .  edd_get_checkout_uri() . '?edd_action=restore_cart&edd_cart_token=' . $token . '</a>'
		);

		EDD()->session->set( 'edd_cart_messages', $messages );

		if ( $cart ) {
			return true;
		}

		return false;
	}

	/**
	 * Restore Cart
	 *
	 * @since 2.7
	 * @access public
	 * @return bool
	 */
	public function restore() {
		if ( ! $this->is_saving_enabled() ) {
			return false;
		}

		$user_id    = get_current_user_id();
		$saved_cart = get_user_meta( $user_id, 'edd_saved_cart', true );
		$token      = $this->get_token();

		if ( is_user_logged_in() && $saved_cart ) {
			$messages = EDD()->session->get( 'edd_cart_messages' );

			if ( ! $messages ) {
				$messages = array();
			}

			if ( isset( $_GET['edd_cart_token'] ) && ! hash_equals( $_GET['edd_cart_token'], $token ) ) {
				$messages['edd_cart_restoration_failed'] = sprintf( '<strong>%1$s</strong>: %2$s', __( 'Error', 'easy-digital-downloads' ), __( 'Cart restoration failed. Invalid token.', 'easy-digital-downloads' ) );
				EDD()->session->set( 'edd_cart_messages', $messages );
			}

			delete_user_meta( $user_id, 'edd_saved_cart' );
			delete_user_meta( $user_id, 'edd_cart_token' );

			if ( isset( $_GET['edd_cart_token'] ) && $_GET['edd_cart_token'] != $token ) {
				return new WP_Error( 'invalid_cart_token', __( 'The cart cannot be restored. Invalid token.', 'easy-digital-downloads' ) );
			}
		} elseif ( ! is_user_logged_in() && isset( $_COOKIE['edd_saved_cart'] ) && $token ) {
			$saved_cart = $_COOKIE['edd_saved_cart'];

			if ( ! hash_equals( $_GET['edd_cart_token'], $token ) ) {
				$messages['edd_cart_restoration_failed'] = sprintf( '<strong>%1$s</strong>: %2$s', __( 'Error', 'easy-digital-downloads' ), __( 'Cart restoration failed. Invalid token.', 'easy-digital-downloads' ) );
				EDD()->session->set( 'edd_cart_messages', $messages );

				return new WP_Error( 'invalid_cart_token', __( 'The cart cannot be restored. Invalid token.', 'easy-digital-downloads' ) );
			}

			$saved_cart = json_decode( stripslashes( $saved_cart ), true );

			setcookie( 'edd_saved_cart', '', time()-3600, COOKIEPATH, COOKIE_DOMAIN );
			setcookie( 'edd_cart_token', '', time()-3600, COOKIEPATH, COOKIE_DOMAIN );
		}

		$messages['edd_cart_restoration_successful'] = sprintf( '<strong>%1$s</strong>: %2$s', __( 'Success', 'easy-digital-downloads' ), __( 'Cart restored successfully.', 'easy-digital-downloads' ) );
		EDD()->session->set( 'edd_cart', $saved_cart );
		EDD()->session->set( 'edd_cart_messages', $messages );

		return true;
	}

	/**
	 * Retrieve a saved cart token. Used in validating saved carts
	 *
	 * @since 2.7
	 * @access public
	 * @return int
	 */
	public function get_token() {
		$user_id = get_current_user_id();

		if ( is_user_logged_in() ) {
			$token = get_user_meta( $user_id, 'edd_cart_token', true );
		} else {
			$token = isset( $_COOKIE['edd_cart_token'] ) ? $_COOKIE['edd_cart_token'] : false;
		}

		return apply_filters( 'edd_get_cart_token', $token, $user_id );
	}

	/**
	 * Generate URL token to restore the cart via a URL
	 *
	 * @since 2.7
	 * @access public
	 * @return int
	 */
	public function generate_token() {
		return apply_filters( 'edd_generate_cart_token', md5( mt_rand() . time() ) );
	}
}