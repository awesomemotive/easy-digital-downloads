<?php
/**
 * Order.php
 *
 * @package   easy-digital-downloads
 * @copyright Copyright (c) 2021, Easy Digital Downloads
 * @license   GPL2+
 */

namespace EDD\Checkout;

class Order {

	/**
	 * @var array Products being purchased.
	 */
	public $products = [];

	/**
	 * @var array Fees applied.
	 */
	public $fees = [];

	/**
	 * @var float Amount before taxes and discounts.
	 */
	public $subtotal = 0.00;

	/**
	 * @var float Discount amount.
	 */
	public $discountAmount = 0.00;

	/**
	 * @var float Tax amount.
	 */
	public $taxAmount = 0.00;

	/**
	 * @var float Tax rate.
	 */
	public $taxRate = 0.00;

	/**
	 * @var float Total amount, after tax.
	 */
	public $total = 0.00;

	/**
	 * Builds a new order object from session data.
	 *
	 * @return Order
	 */
	public static function getFromSession( $userAddress = [] ) {
		$order                 = new self();
		$order->subtotal       = edd_get_cart_subtotal();
		$order->discountAmount = edd_get_cart_discounted_amount();
		$order->taxAmount      = edd_get_cart_tax();
		$order->total          = edd_get_cart_total();

		if ( ! empty( $userAddress['country'] ) && edd_use_taxes() ) {
			$order->taxRate = edd_get_cart_tax_rate(
				$userAddress['country'],
				! empty( $userAddress['state'] ) ? $userAddress['state'] : '',
				! empty( $userAddress['zip'] ) ? $userAddress['zip'] : ''
			);
		}

		return $order;
	}

}
