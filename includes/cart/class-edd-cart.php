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
	public $discounts = array();

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
	 * Has discount?
	 *
	 * @var bool
	 * @since 2.7
	 */
	public $has_discounts = null;

	/**
	 * Constructor.
	 *
	 * @since 2.7
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'setup_cart' ), 1 );
	}

	/**
	 * Sets up cart components
	 *
	 * @since  2.7
	 * @access private
	 * @return void
	 */
	public function setup_cart() {
		$this->get_contents_from_session();
		$this->get_contents();
		$this->get_contents_details();
		$this->get_all_fees();
		$this->get_discounts_from_session();
		$this->get_quantity();
	}

	/**
	 * Populate the cart with the data stored in the session
	 *
	 * @since 2.7
	 * @return void
	 */
	public function get_contents_from_session() {
		$cart = EDD()->session->get( 'edd_cart' );
		$this->contents = $cart;

		do_action( 'edd_cart_contents_loaded_from_session', $this );
	}

	/**
	 * Populate the discounts with the data stored in the session.
	 *
	 * @since  2.7
	 * @return void
	 */
	public function get_discounts_from_session() {
		$discounts = EDD()->session->get( 'cart_discounts' );
		$this->discounts = $discounts;

		do_action( 'edd_cart_discounts_loaded_from_session', $this );
	}

	/**
	 * Get cart contents
	 *
	 * @since 2.7
	 * @return array List of cart contents.
	 */
	public function get_contents() {
		if ( ! did_action( 'edd_cart_contents_loaded_from_session' ) ) {
			$this->get_contents_from_session();
		}

		$cart = is_array( $this->contents ) && ! empty( $this->contents ) ? array_values( $this->contents ) : array();
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

		do_action( 'edd_cart_contents_loaded' );

		return (array) $this->contents;
	}

	/**
	 * Get cart contents details
	 *
	 * @since 2.7
	 * @return array
	 */
	public function get_contents_details() {
		global $edd_is_last_cart_item, $edd_flat_discount_total;

		if ( empty( $this->contents ) ) {
			return array();
		}

		$details = array();
		$length  = count( $this->contents ) - 1;

		foreach ( $this->contents as $key => $item ) {
			if( $key >= $length ) {
				$edd_is_last_cart_item = true;
			}

			$item['quantity'] = edd_item_quantities_enabled() ? absint( $item['quantity'] ) : 1;
			$item['quantity'] = max( 1, $item['quantity'] ); // Force quantity to 1

			$options = isset( $item['options'] ) ? $item['options'] : array();

			$price_id = isset( $options['price_id'] ) ? $options['price_id'] : null;

			$item_price = $this->get_item_price( $item['id'], $options );
			$discount   = $this->get_item_discount_amount( $item );
			$discount   = apply_filters( 'edd_get_cart_content_details_item_discount_amount', $discount, $item );
			$quantity   = $this->get_item_quantity( $item['id'], $options );
			$fees       = $this->get_fees( 'fee', $item['id'], $price_id );
			$subtotal   = floatval( $item_price ) * $quantity;

			// Subtotal for tax calculation must exclude fees that are greater than 0. See $this->get_tax_on_fees()
			$subtotal_for_tax = $subtotal;

			foreach ( $fees as $fee ) {

				$fee_amount = (float) $fee['amount'];
				$subtotal  += $fee_amount;

				if( $fee_amount > 0 ) {
					continue;
				}

				$subtotal_for_tax += $fee_amount;
			}

			$tax = $this->get_item_tax( $item['id'], $options, $subtotal_for_tax - $discount );

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
	 * Get Discounts.
	 *
	 * @since 2.7
	 * @return array $discounts The active discount codes
	 */
	public function get_discounts() {
		$this->get_discounts_from_session();
		$this->discounts = ! empty( $this->discounts ) ? explode( '|', $this->discounts ) : array();
		return $this->discounts;
	}

	/**
	 * Update Cart
	 *
	 * @since 2.7
	 * @return void
	 */
	public function update_cart() {
		EDD()->session->set( 'edd_cart', $this->contents );
	}

	/**
	 * Checks if any discounts have been applied to the cart
	 *
	 * @since 2.7
	 * @return bool
	 */
	public function has_discounts() {
		if ( null !== $this->has_discounts ) {
			return $this->has_discounts;
		}

		$has_discounts = false;

		$discounts = $this->get_discounts();
		if ( ! empty( $discounts ) ) {
			$has_discounts = true;
		}

		$this->has_discounts = apply_filters( 'edd_cart_has_discounts', $has_discounts );

		return $this->has_discounts;
	}

	/**
	 * Get quantity
	 *
	 * @since 2.7
	 * @return int
	 */
	public function get_quantity() {
		$total_quantity = 0;

		$contents = $this->get_contents();
		if ( ! empty( $contents ) ) {
			$quantities     = wp_list_pluck( $this->contents, 'quantity' );
			$total_quantity = absint( array_sum( $quantities ) );
		}

		$this->quantity = apply_filters( 'edd_get_cart_quantity', $total_quantity, $this->contents );
		return $this->quantity;
	}

	/**
	 * Checks if the cart is empty
	 *
	 * @since 2.7
	 * @return boolean
	 */
	public function is_empty() {
		return 0 === count( (array) $this->get_contents() );
	}

	/**
	 * Add to cart
	 *
	 * As of EDD 2.7, items can only be added to the cart when the object passed extends EDD_Cart_Item
	 *
	 * @since 2.7
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

		$this->contents = apply_filters( 'edd_pre_add_to_cart_contents', $this->contents );

		$quantities_enabled = edd_item_quantities_enabled() && ! edd_download_quantities_disabled( $download_id );

		if ( $download->has_variable_prices()  && ! isset( $options['price_id'] ) ) {
			// Forces to the default price ID if none is specified and download has variable prices
			$options['price_id'] = get_post_meta( $download->ID, '_edd_default_price_id', true );
		}

		if ( isset( $options['quantity'] ) ) {
			if ( is_array( $options['quantity'] ) ) {
				$quantity = array();
				foreach ( $options['quantity'] as $q ) {
					$quantity[] = $quantities_enabled ? absint( preg_replace( '/[^0-9\.]/', '', $q ) ) : 1;
				}
			} else {
				$quantity = $quantities_enabled ? absint( preg_replace( '/[^0-9\.]/', '', $options['quantity'] ) ) : 1;
			}

			unset( $options['quantity'] );
		} else {
			$quantity = 1;
		}

		// If the price IDs are a string and is a coma separated list, make it an array (allows custom add to cart URLs)
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
					'quantity'     => is_array( $quantity ) && isset( $quantity[ $key ] ) ? $quantity[ $key ] : $quantity,
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

		$this->update_cart();

		do_action( 'edd_post_add_to_cart', $download_id, $options, $items );

		// Clear all the checkout errors, if any
		edd_clear_errors();

		return count( $this->contents ) - 1;
	}

	/**
	 * Remove from cart
	 *
	 * @since 2.7
	 *
	 * @param int $key Cart key to remove. This key is the numerical index of the item contained within the cart array.
 	 * @return array Updated cart contents
	 */
	public function remove( $key ) {
		$cart = $this->get_contents();

		do_action( 'edd_pre_remove_from_cart', $key );

		if ( ! is_array( $cart ) ) {
			return true; // Empty cart
		} else {
			$item_id = isset( $cart[ $key ]['id'] ) ? $cart[ $key ]['id'] : null;
			unset( $cart[ $key ] );
		}

		$this->contents = $cart;
		$this->update_cart();

		do_action( 'edd_post_remove_from_cart', $key, $item_id );

		edd_clear_errors();

		return $this->contents;
	}

	/**
	 * Generate the URL to remove an item from the cart.
	 *
	 * @since 2.7
	 *
	 * @param int $cart_key Cart item key
 	 * @return string $remove_url URL to remove the cart item
	 */
	public function remove_item_url( $cart_key ) {
		global $wp_query;

		if ( defined( 'DOING_AJAX' ) ) {
			$current_page = edd_get_checkout_uri();
		} else {
			$current_page = edd_get_current_page_url();
		}

		$remove_url = edd_add_cache_busting( add_query_arg( array( 'cart_item' => $cart_key, 'edd_action' => 'remove' ), $current_page ) );

		return apply_filters( 'edd_remove_item_url', $remove_url );
	}

	/**
	 * Generate the URL to remove a fee from the cart.
	 *
	 * @since 2.7
	 *
	 * @param int $fee_id Fee ID.
	 * @return string $remove_url URL to remove the cart item
	 */
	public function remove_fee_url( $fee_id = '' ) {
		global $post;

		if ( defined('DOING_AJAX') ) {
			$current_page = edd_get_checkout_uri();
		} else {
			$current_page = edd_get_current_page_url();
		}

		$remove_url = add_query_arg( array( 'fee' => $fee_id, 'edd_action' => 'remove_fee', 'nocache' => 'true' ), $current_page );

		return apply_filters( 'edd_remove_fee_url', $remove_url );
	}

	/**
	 * Empty the cart
	 *
	 * @since 2.7
	 * @return void
	 */
	public function empty_cart() {
		// Remove cart contents.
		EDD()->session->set( 'edd_cart', NULL );

		// Remove all cart fees.
		EDD()->session->set( 'edd_cart_fees', NULL );

		// Remove any resuming payments.
		EDD()->session->set( 'edd_resume_payment', NULL );

		// Remove any active discounts
		$this->remove_all_discounts();
		$this->contents = array();

		do_action( 'edd_empty_cart' );
	}

	/**
	 * Remove discount from the cart
	 *
	 * @since 2.7
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
	 * @return void
	 */
	public function remove_all_discounts() {
		EDD()->session->set( 'cart_discounts', null );
		do_action( 'edd_cart_discounts_removed' );
	}

	/**
	 * Get the discounted amount on a price
	 *
	 * @since 2.7
	 *
	 * @param array       $item     Cart item.
	 * @param bool|string $discount False to use the cart discounts or a string to check with a discount code.
	 * @return float The discounted amount
	 */
	public function get_item_discount_amount( $item = array(), $discount = false ) {
		global $edd_is_last_cart_item, $edd_flat_discount_total;

		// If we're not meeting the requirements of the $item array, return or set them
		if ( empty( $item ) || empty( $item['id'] ) ) {
			return 0;
		}

		// Quantity is a requirement of the cart options array to determine the discounted price
		if ( empty( $item['quantity'] ) ) {
			return 0;
		}

		if ( ! isset( $item['options'] ) ) {
			$item['options'] = array();
		}

		$amount           = 0;
		$price            = $this->get_item_price( $item['id'], $item['options'] );
		$discounted_price = $price;

		$discounts = false === $discount ? $this->get_discounts() : array( $discount );

		// If discounts exist, only apply them to non-free cart items
		if ( ! empty( $discounts ) && 0.00 != $price ) {
			foreach ( $discounts as $discount ) {
				$code_id = edd_get_discount_id_by_code( $discount );

				// Check discount exists
				if( ! $code_id ) {
					continue;
				}

				$reqs              = edd_get_discount_product_reqs( $code_id );
				$excluded_products = edd_get_discount_excluded_products( $code_id );

				// Make sure requirements are set and that this discount shouldn't apply to the whole cart
				if ( ! empty( $reqs ) && edd_is_discount_not_global( $code_id ) ) {
					// This is a product(s) specific discount
					foreach ( $reqs as $download_id ) {
						if ( $download_id == $item['id'] && ! in_array( $item['id'], $excluded_products ) ) {
							$discounted_price -= $price - edd_get_discounted_amount( $discount, $price );
						}
					}
				} else {
					// This is a global cart discount
					if( ! in_array( $item['id'], $excluded_products ) ) {
						if( 'flat' === edd_get_discount_type( $code_id ) ) {
							/* *
							 * In order to correctly record individual item amounts, global flat rate discounts
							 * are distributed across all cart items. The discount amount is divided by the number
							 * of items in the cart and then a portion is evenly applied to each cart item
							 */
							$items_subtotal    = 0.00;
							$cart_items        = $this->get_contents();
							foreach ( $cart_items as $cart_item ) {
								if ( ! in_array( $cart_item['id'], $excluded_products ) ) {
									$item_price      = $this->get_item_price( $cart_item['id'], $cart_item['options'] );
									$items_subtotal += $item_price * $cart_item['quantity'];
								}
							}

							$item_subtotal     = ( $price * $item['quantity'] );
							$subtotal_percent  = ! empty( $items_subtotal ) ? ( $item_subtotal / $items_subtotal ) : 0;
							$code_amount       = edd_get_discount_amount( $code_id );
							$discounted_amount = $code_amount * $subtotal_percent;
							$discounted_price -= $discounted_amount;

							$edd_flat_discount_total += round( $discounted_amount, edd_currency_decimal_filter() );

							if ( $edd_is_last_cart_item && $edd_flat_discount_total < $code_amount ) {
								$adjustment = $code_amount - $edd_flat_discount_total;
								$discounted_price -= $adjustment;
							}
						} else {
							$discounted_price -= $price - edd_get_discounted_amount( $discount, $price );
						}
					}
				}

				if ( $discounted_price < 0 ) {
					$discounted_price = 0;
				}
			}

			$amount = round( ( $price - apply_filters( 'edd_get_cart_item_discounted_amount', $discounted_price, $discounts, $item, $price ) ), edd_currency_decimal_filter() );

			if ( 'flat' !== edd_get_discount_type( $code_id ) ) {
				$amount = $amount * $item['quantity'];
			}
		}

		return $amount;
	}

	/**
	 * Shows the fully formatted cart discount
	 *
	 * @since 2.7
	 *
	 * @param bool $echo Echo?
	 * @return string $amount Fully formatted cart discount
	 */
	public function display_cart_discount( $echo = false ) {
		$discounts = $this->get_discounts();

		if ( empty( $discounts ) ) {
			return false;
		}

		$discount_id  = edd_get_discount_id_by_code( $discounts[0] );
		$amount       = edd_format_discount_rate( edd_get_discount_type( $discount_id ), edd_get_discount_amount( $discount_id ) );

		if ( $echo ) {
			echo $amount;
		}

		return $amount;
	}

	/**
	 * Checks to see if an item is in the cart.
	 *
	 * @since 2.7
	 *
	 * @param int   $download_id Download ID of the item to check.
 	 * @param array $options
	 * @return bool
	 */
	public function is_item_in_cart( $download_id = 0, $options = array() ) {
		$cart = $this->get_contents();

		$ret = false;

		if ( is_array( $cart ) ) {
			foreach ( $cart as $item ) {
				if ( $item['id'] == $download_id ) {
					if ( isset( $options['price_id'] ) && isset( $item['options']['price_id'] ) ) {
						if ( $options['price_id'] == $item['options']['price_id'] ) {
							$ret = true;
							break;
						}
					} else {
						$ret = true;
						break;
					}
				}
			}
		}

		return (bool) apply_filters( 'edd_item_in_cart', $ret, $download_id, $options );
	}

	/**
	 * Get the position of an item in the cart
	 *
	 * @since 2.7
	 *
	 * @param int   $download_id Download ID of the item to check.
 	 * @param array $options
	 * @return mixed int|false
	 */
	public function get_item_position( $download_id = 0, $options = array() ) {
		$cart = $this->get_contents();

		if ( ! is_array( $cart ) ) {
			return false;
		} else {
			foreach ( $cart as $position => $item ) {
				if ( $item['id'] == $download_id ) {
					if ( isset( $options['price_id'] ) && isset( $item['options']['price_id'] ) ) {
						if ( (int) $options['price_id'] == (int) $item['options']['price_id'] ) {
							return $position;
						}
					} else {
						return $position;
					}
				}
			}
		}

		return false;
	}

	/**
	 * Get the quantity of an item in the cart.
	 *
	 * @since 2.7
	 *
	 * @param int   $download_id Download ID of the item
 	 * @param array $options
	 * @return int Numerical index of the position of the item in the cart
	 */
	public function get_item_quantity( $download_id = 0, $options = array() ) {
		$key = $this->get_item_position( $download_id, $options );

		$quantity = isset( $this->contents[ $key ]['quantity'] ) && edd_item_quantities_enabled() ? $this->contents[ $key ]['quantity'] : 1;

		if ( $quantity < 1 ) {
			$quantity = 1;
		}

		return absint( apply_filters( 'edd_get_cart_item_quantity', $quantity, $download_id, $options ) );
	}

	/**
	 * Set the quantity of an item in the cart.
	 *
	 * @since 2.7
	 *
	 * @param int   $download_id Download ID of the item
	 * @param int   $quantity    Updated quantity of the item
 	 * @param array $options
	 * @return array $contents Updated cart object.
	 */
	public function set_item_quantity( $download_id = 0, $quantity = 1, $options = array() ) {
		$key  = $this->get_item_position( $download_id, $options );

		if ( false === $key ) {
			return $this->contents;
		}

		if ( $quantity < 1 ) {
			$quantity = 1;
		}

		$this->contents[ $key ]['quantity'] = $quantity;
		$this->update_cart();

		do_action( 'edd_after_set_cart_item_quantity', $download_id, $quantity, $options, $this->contents );

		return $this->contents;
	}

	/**
	 * Cart Item Price.
	 *
	 * @since 2.7
	 *
	 * @param int   $item_id Download (cart item) ID number
 	 * @param array $options Optional parameters, used for defining variable prices
 	 * @return string Fully formatted price
	 */
	public function item_price( $item_id = 0, $options = array() ) {
		$price = $this->get_item_price( $item_id, $options );
		$label = '';

		$price_id = isset( $options['price_id'] ) ? $options['price_id'] : false;

		if ( ! edd_is_free_download( $item_id, $price_id ) && ! edd_download_is_tax_exclusive( $item_id ) ) {
			if ( edd_prices_show_tax_on_checkout() && ! edd_prices_include_tax() ) {
				$price += edd_get_cart_item_tax( $item_id, $options, $price );
			}

			if ( ! edd_prices_show_tax_on_checkout() && edd_prices_include_tax() ) {
				$price -= edd_get_cart_item_tax( $item_id, $options, $price );
			}

			if ( edd_display_tax_rate() ) {
				$label = '&nbsp;&ndash;&nbsp;';

				if ( edd_prices_show_tax_on_checkout() ) {
					$label .= sprintf( __( 'includes %s tax', 'easy-digital-downloads' ), edd_get_formatted_tax_rate() );
				} else {
					$label .= sprintf( __( 'excludes %s tax', 'easy-digital-downloads' ), edd_get_formatted_tax_rate() );
				}

				$label = apply_filters( 'edd_cart_item_tax_description', $label, $item_id, $options );
			}
		}

		$price = edd_currency_filter( edd_format_amount( $price ) );

		return apply_filters( 'edd_cart_item_price_label', $price . $label, $item_id, $options );
	}

	/**
	 * Gets the price of the cart item. Always exclusive of taxes.
 	 *
 	 * Do not use this for getting the final price (with taxes and discounts) of an item.
 	 * Use edd_get_cart_item_final_price()
	 *
	 * @since 2.7
	 *
	 * @param  int        $download_id               Download ID for the cart item
	 * @param  array      $options                   Optional parameters, used for defining variable prices
 	 * @param  bool       $remove_tax_from_inclusive Remove the tax amount from tax inclusive priced products.
 	 * @return float|bool Price for this item
	 */
	public function get_item_price( $download_id = 0, $options = array(), $remove_tax_from_inclusive = false ) {
		$price = 0;
		$variable_prices = edd_has_variable_prices( $download_id );

		if ( $variable_prices ) {
			$prices = edd_get_variable_prices( $download_id );

			if ( $prices ) {
				if ( ! empty( $options ) ) {
					$price = isset( $prices[ $options['price_id'] ] ) ? $prices[ $options['price_id'] ]['amount'] : false;
				} else {
					$price = false;
				}
			}
		}

		if ( ! $variable_prices || false === $price ) {
			// Get the standard Download price if not using variable prices
			$price = edd_get_download_price( $download_id );
		}

		if ( $remove_tax_from_inclusive && edd_prices_include_tax() ) {
			$price -= $this->get_item_tax( $download_id, $options, $price );
		}

		return apply_filters( 'edd_cart_item_price', $price, $download_id, $options );
	}

	/**
	 * Final Price of Item in Cart (incl. discounts and taxes)
	 *
	 * @since 2.7
	 *
	 * @param int $item_key Cart item key
 	 * @return float Final price for the item
	 */
	public function get_item_final_price( $item_key = 0 ) {
		$final_price = $this->details[ $item_key ]['price'];

		return apply_filters( 'edd_cart_item_final_price', $final_price, $item_key );
	}

	/**
	 * Calculate the tax for an item in the cart.
	 *
	 * @since 2.7
	 *
	 * @param array $download_id Download ID
	 * @param array $options     Cart item options
	 * @param float $subtotal    Cart item subtotal
	 * @return float Tax amount
	 */
	public function get_item_tax( $download_id = 0, $options = array(), $subtotal = '' ) {
		$tax = 0;

		if ( ! edd_download_is_tax_exclusive( $download_id ) ) {
			$country = ! empty( $_POST['billing_country'] ) ? $_POST['billing_country'] : false;
			$state   = ! empty( $_POST['card_state'] )      ? $_POST['card_state']      : false;

			$tax = edd_calculate_tax( $subtotal, $country, $state );
		}

		$tax = max( $tax, 0 );

		return apply_filters( 'edd_get_cart_item_tax', $tax, $download_id, $options, $subtotal );
	}

	/**
	 * Get Cart Fees
	 *
	 * @since 2.7
	 * @return array Cart fees
	 */
	public function get_fees( $type = 'all', $download_id = 0, $price_id = null ) {
		return EDD()->fees->get_fees( $type, $download_id, $price_id );
	}

	/**
	 * Get All Cart Fees.
	 *
	 * @since 2.7
	 * @return array
	 */
	public function get_all_fees() {
		$this->fees = EDD()->fees->get_fees( 'all' );
		return $this->fees;
	}

	/**
	 * Get Cart Items Subtotal.
	 *
	 * @since 2.7
	 *
	 * @param array $items Cart items array
 	 * @return float items subtotal
	 */
	public function get_items_subtotal( $items ) {
		$subtotal = 0.00;

		if ( is_array( $items ) && ! empty( $items ) ) {
			$prices = wp_list_pluck( $items, 'subtotal' );

			if ( is_array( $prices ) ) {
				$subtotal = array_sum( $prices );
			} else {
				$subtotal = 0.00;
			}

			if ( $subtotal < 0 ) {
				$subtotal = 0.00;
			}
		}

		$this->subtotal = apply_filters( 'edd_get_cart_items_subtotal', $subtotal );

		return $this->subtotal;
	}

	/**
	 * Get Discountable Subtotal.
	 *
	 * @since 2.7
	 * @return float Total discountable amount before taxes
	 */
	public function get_discountable_subtotal( $code_id ) {
		$cart_items = $this->get_contents_details();
		$items      = array();

		$excluded_products = edd_get_discount_excluded_products( $code_id );

		if ( $cart_items ) {
			foreach( $cart_items as $item ) {
				if ( ! in_array( $item['id'], $excluded_products ) ) {
					$items[] =  $item;
				}
			}
		}

		$subtotal = $this->get_items_subtotal( $items );

		return apply_filters( 'edd_get_cart_discountable_subtotal', $subtotal );
	}

	/**
	 * Get Discounted Amount.
	 *
	 * @since 2.7
	 *
	 * @param bool $discounts Discount codes
	 * @return float|mixed|void Total discounted amount
	 */
	public function get_discounted_amount( $discounts = false ) {
		$amount = 0.00;
		$items  = $this->get_contents_details();

		if ( $items ) {
			$discounts = wp_list_pluck( $items, 'discount' );

			if ( is_array( $discounts ) ) {
				$discounts = array_map( 'floatval', $discounts );
				$amount    = array_sum( $discounts );
			}
		}

		return apply_filters( 'edd_get_cart_discounted_amount', $amount );
	}

	/**
	 * Get Cart Subtotal.
	 *
	 * Gets the total price amount in the cart before taxes and before any discounts.
	 *
	 * @since 2.7
	 *
	 * @return float Total amount before taxes
	 */
	public function get_subtotal() {
		$items    = $this->get_contents_details();
		$subtotal = $this->get_items_subtotal( $items );

		return apply_filters( 'edd_get_cart_subtotal', $subtotal );
	}

	/**
	 * Subtotal (before taxes).
	 *
	 * @since 2.7
	 * @return float Total amount before taxes fully formatted
	 */
	public function subtotal() {
		return esc_html( edd_currency_filter( edd_format_amount( edd_get_cart_subtotal() ) ) );
	}

	/**
	 * Get Total Cart Amount.
	 *
	 * @since 2.7
	 *
	 * @param bool $discounts Array of discounts to apply (needed during AJAX calls)
	 * @return float Cart amount
	 */
	public function get_total( $discounts = false ) {
		$subtotal     = (float) $this->get_subtotal();
		$discounts    = (float) $this->get_discounted_amount();
		$fees         = (float) $this->get_total_fees();
		$cart_tax     = (float) $this->get_tax();
		$total_wo_tax = $subtotal - $discounts + $fees;
		$total        = $subtotal - $discounts + $cart_tax + $fees;

		if ( $total < 0 || ! $total_wo_tax > 0 ) {
			$total = 0.00;
		}

		$this->total = (float) apply_filters( 'edd_get_cart_total', $total );

		return $this->total;
	}

	/**
	 * Fully Formatted Total Cart Amount.
	 *
	 * @since 2.7
	 *
	 * @param bool $echo
	 * @return mixed|string|void
	 */
	public function total( $echo ) {
		$total = apply_filters( 'edd_cart_total', edd_currency_filter( edd_format_amount( $this->get_total() ) ) );

		if ( ! $echo ) {
			return $total;
		}

		echo $total;
	}

	/**
	 * Get Cart Fee Total
	 *
	 * @since 2.7
	 * @return double
	 */
	public function get_total_fees() {
		$fee_total = 0.00;

		foreach ( $this->get_fees() as $fee ) {

			// Since fees affect cart item totals, we need to not count them towards the cart total if there is an association.
			if ( ! empty( $fee['download_id'] ) ) {
				continue;
			}

			$fee_total += $fee['amount'];
		}

		return apply_filters( 'edd_get_fee_total', $fee_total, $this->fees );
	}

	/**
	 * Get the price ID for an item in the cart.
	 *
	 * @since 2.7
	 *
	 * @param array $item Item details
	 * @return string $price_id Price ID
	 */
	public function get_item_price_id( $item = array() ) {
		if ( isset( $item['item_number'] ) ) {
			$price_id = isset( $item['item_number']['options']['price_id'] ) ? $item['item_number']['options']['price_id'] : null;
		} else {
			$price_id = isset( $item['options']['price_id'] ) ? $item['options']['price_id'] : null;
		}

		return $price_id;
	}

	/**
	 * Get the price name for an item in the cart.
	 *
	 * @since 2.7
	 *
	 * @param array $item Item details
	 * @return string $name Price name
	 */
	public function get_item_price_name( $item = array() ) {
		$price_id = (int) $this->get_item_price_id( $item );
		$prices   = edd_get_variable_prices( $item['id'] );
		$name     = ! empty( $prices[ $price_id ] ) ? $prices[ $price_id ]['name'] : '';

		return apply_filters( 'edd_get_cart_item_price_name', $name, $item['id'], $price_id, $item );
	}

	/**
	 * Get the name of an item in the cart.
	 *
	 * @since 2.7
	 *
	 * @param array $item Item details
	 * @return string $name Item name
	 */
	public function get_item_name( $item = array() ) {
		$item_title = get_the_title( $item['id'] );

		if ( empty( $item_title ) ) {
			$item_title = $item['id'];
		}

		if ( edd_has_variable_prices( $item['id'] ) && false !== edd_get_cart_item_price_id( $item ) ) {
			$item_title .= ' - ' . edd_get_cart_item_price_name( $item );
		}

		return apply_filters( 'edd_get_cart_item_name', $item_title, $item['id'], $item );
	}

	/**
	 * Get all applicable tax for the items in the cart
	 *
	 * @since 2.7
	 * @return float Total tax amount
	 */
	public function get_tax() {
		$cart_tax     = 0;
		$items        = $this->get_contents_details();

		if ( $items ) {

			$taxes = wp_list_pluck( $items, 'tax' );

			if ( is_array( $taxes ) ) {
				$cart_tax = array_sum( $taxes );
			}
		}
		$cart_tax += $this->get_tax_on_fees();

		$subtotal = $this->get_subtotal();
		if ( empty( $subtotal ) ) {
			$cart_tax = 0;
		}

		$cart_tax = apply_filters( 'edd_get_cart_tax', edd_sanitize_amount( $cart_tax ) );

		return $cart_tax;
	}

	/**
	 * Gets the total tax amount for the cart contents in a fully formatted way
	 *
	 * @since 2.7
	 *
	 * @param boolean $echo Decides if the result should be returned or not
	 * @return string Total tax amount
	 */
	public function tax( $echo = false ) {
		$cart_tax = $this->get_tax();
		$cart_tax = edd_currency_filter( edd_format_amount( $cart_tax ) );

		$tax = max( $cart_tax, 0 );
		$tax = apply_filters( 'edd_cart_tax', $cart_tax );

		if ( ! $echo ) {
			return $tax;
		} else {
			echo $tax;
		}
	}

	/**
	 * Get tax applicable for fees.
	 *
	 * @since 2.7
	 * @return float Total taxable amount for fees
	 */
	public function get_tax_on_fees() {
		$tax  = 0;
		$fees = edd_get_cart_fees();

		if ( $fees ) {
			foreach ( $fees as $fee_id => $fee ) {
				if ( ! empty( $fee['no_tax'] ) || $fee['amount'] < 0 ) {
					continue;
				}

				/**
				 * Fees (at this time) must be exclusive of tax
				 */
				add_filter( 'edd_prices_include_tax', '__return_false' );
				$tax += edd_calculate_tax( $fee['amount'] );
				remove_filter( 'edd_prices_include_tax', '__return_false' );
			}
		}

		return apply_filters( 'edd_get_cart_fee_tax', $tax );
	}

	/**
	 * Is Cart Saving Enabled?
	 *
	 * @since 2.7
	 * @return bool
	 */
	public function is_saving_enabled() {
		return edd_get_option( 'enable_cart_saving', false );
	}

	/**
	 * Checks if the cart has been saved
	 *
	 * @since 2.7
	 * @return bool
	 */
	public function is_saved() {
		if ( ! $this->is_saving_enabled() ) {
			return false;
		}

		$saved_cart = get_user_meta( get_current_user_id(), 'edd_saved_cart', true );

		if ( is_user_logged_in() ) {
			if ( ! $saved_cart ) {
				return false;
			}

			if ( $saved_cart === EDD()->session->get( 'edd_cart' ) ) {
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

		// @e also have to set this instance to what the session is.
		$this->contents = $saved_cart;

		return true;
	}

	/**
	 * Retrieve a saved cart token. Used in validating saved carts
	 *
	 * @since 2.7
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
	 * @return int
	 */
	public function generate_token() {
		return apply_filters( 'edd_generate_cart_token', md5( mt_rand() . time() ) );
	}
}
