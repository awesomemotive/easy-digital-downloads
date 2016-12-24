<?php
/**
 * Cart Item Base Class.
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
 * EDD_Cart_Item Base Class.
 *
 * @since 2.7
 */
abstract class EDD_Cart_Item {
	/**
	 * Item name
	 *
	 * @var string
	 * @since 2.7
	 */
	private $name;

	/**
	 * The item price
	 *
	 * @since 2.7
	 * @var float
	 */
	private $price;

	/**
	 * The item prices (only if variable pricing is enabled)
	 *
	 * @since 2.7
	 * @var array
	 */
	private $prices;

	/**
	 * Constructor.
	 *
	 * @since 2.7
	 * @access protected
	 *
	 * @param array $args Arguments to set up cart items
	 * @return void
	 */
	public function __construct( $args = array() ) {
		$this->setup_cart( $args );
	}

	/**
	 * Setup cart item.
	 *
	 * @since 2.7
	 * @access private
	 * @return void
	 */
	private function setup_cart( $args = array() ) {
		if ( empty( $args ) ) {
			return;
		}

		$this->name   = isset( $args['name'] )   ? $args['name']   : null;
		$this->price  = isset( $args['price'] )  ? $args['price']  : null;
		$this->prices = isset( $args['prices'] ) ? $args['prices'] : null;
	}

	/**
	 * Name of the item.
	 *
	 * @since 2.7
	 * @access public
	 * @return string
	 */
	public function get_name() {
		return $this->name;
	}

	/**
	 * Price of the item
	 *
	 * @since 2.7
	 * @access public
	 * @return float
	 */
	public function get_price() {
		return $this->price;
	}

	/**
	 * Prices of the item (only if variable pricing is enabled)
	 *
	 * @since 2.7
	 * @access public
	 * @return array
	 */
	public function get_prices() {
		return $this->prices;
	}
}