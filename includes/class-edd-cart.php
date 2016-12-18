<?php
/**
 * Cart Object
 *
 * @package     EDD
 * @subpackage  Classes/Download
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
final class EDD_Cart {
	/**
	 * @var EDD_Cart
	 * @since 1.4
	 */
	private static $instance;

	/**
	 * Cart contents
	 *
	 * @var array
	 */
	private $cart;

	/**
	 * Details of the cart contents
	 *
	 * @var array
	 */
	private $details;

	/**
	 * Cart Quantity
	 *
	 * @var int
	 */
	private $quantity;

	/**
	 * Subtotal
	 *
	 * @var double
	 */
	private $subtotal;

	/**
	 * Total
	 *
	 * @var double
	 */
	private $total;

	/**
	 * Fees
	 *
	 * @var array
	 */
	private $fees;

	/**
	 * Tax
	 *
	 * @var double
	 */
	private $tax;

	/**
	 * Purchase Session
	 *
	 * @var array
	 */
	private $session;

	/**
	 * EDD_Cart Instance.
	 *
	 * Insures that only one instance of EDD_Cart exists in memory.
	 *
	 * @since 2.7
	 * @static
	 * @staticvar array $instance
	 * @return object|EDD_Cart Instance of EDD_Cart
	 */
	public static function instance() {
		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof EDD_Cart ) ) {
			self::$instance = new EDD_Cart;
			self::$instance->setup_cart();
		}

		return self::$instance;
	}

	/**
	 * Throw error on object clone.
	 *
	 * The whole idea of the singleton design pattern is that there is a single
	 * object therefore, we don't want the object to be cloned.
	 *
	 * @since 2.7
	 * @access protected
	 * @return void
	 */
	public function __clone() {
		// Cloning instances of the class is forbidden.
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'easy-digital-downloads' ), '2.7' );
	}

	/**
	 * Disable unserializing of the class.
	 *
	 * @since 2.7
	 * @access protected
	 * @return void
	 */
	public function __wakeup() {
		// Unserializing instances of the class is forbidden.
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'easy-digital-downloads' ), '2.7' );
	}

	/**
	 * Setup cart
	 *
	 * @since 2.7
	 * @access private
	 * @return void
	 */
	private function setup_cart() {

	}

	/**
	 * Get cart contents
	 *
	 * @since 2.7
	 * @access public
	 * @return void
	 */
	public function contents() {

	}

	/**
	 * Get cart content details
	 *
	 * @since 2.7
	 * @access public
	 * @return void
	 */
	public function contents_details() {

	}

	/**
	 * Get quantity
	 *
	 * @since 2.7
	 * @access public
	 * @return int
	 */
	public function quantity() {

	}

	/**
	 * Add to cart
	 *
	 * @since 2.7
	 * @access public
	 * @return array $cart Updated cart object
	 */
	public function add() {

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
}