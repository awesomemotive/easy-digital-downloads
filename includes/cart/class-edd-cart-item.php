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
	 * Item price
	 *
	 * @var array|double
	 * @since 2.7
	 */
	private $price;

	/**
	 * Constructor.
	 *
	 * @param array $args Arguments to set up cart items
	 */
	public function __construct( $args = array() ) {
		$this->setup_cart( $args );
	}

	/**
	 * Setup cart
	 *
	 * @since 2.7
	 */
	private function setup_cart( $args = array() ) {
		if ( empty( $args ) ) {
			return;
		}

		$this->name = isset( $args['name'] ) ? $args['name'] : null;
		$this->price = isset( $args['price'] ) ? $args['price'] : null;
	}

	/**
	 * Name of the item
	 *
	 * @return string
	 * @since 2.7
	 */
	public function get_name() {
		return $this->name;
	}

	/**
	 * Price of the item
	 *
	 * @return array|double
	 * @since 2.7
	 */
	public function get_price() {
		return $this->price;
	}
}