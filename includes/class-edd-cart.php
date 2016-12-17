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
}