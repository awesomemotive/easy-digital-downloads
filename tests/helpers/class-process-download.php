<?php

namespace EDD\Tests\Helpers;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;

abstract class Process_Download extends EDD_UnitTestCase {

	/**
	 * Customer ID
	 *
	 * @var int
	 */
	protected static $customer_id;

	/**
	 * Customer
	 *
	 * @var \EDD_Customer
	 */
	protected static $customer;

	/**
	 * Order
	 *
	 * @var \EDD\Orders\Order
	 */
	protected static $order;

	/**
	 * Variable Priced Product
	 *
	 * @var \EDD_Download
	 */
	protected static $variable_download;

	/**
	 * Bundeled Product
	 *
	 * @var \EDD_Download
	 */
	protected static $bundled_download;

	/**
	 * Sets up fixtures once
	 */
	public static function setUpBeforeClass(): void {
		parent::setUpBeforeClass();

		// Create a customer for the current user.
		self::$customer_id = parent::edd()->customer->create( array( 'user_id' => get_current_user_id() ) );
		self::$customer    = new \EDD_Customer( self::$customer_id );

		// Create a variable priced download.
		$variable_download = EDD_Helper_Download::create_variable_download();
		self::$variable_download = new \EDD_Download( $variable_download->ID );

		// Create a bundled download.
		$bundled_download = EDD_Helper_Download::create_bundled_download();
		self::$bundled_download = new \EDD_Download( $bundled_download->ID );

		self::$order = parent::edd()->order->create_and_get(
			array(
				'customer_id' => self::$customer_id,
				'user_id'     => get_current_user_id(),
				'email'       => self::$customer->email,
				'status'      => 'complete',
			)
		);

		// Add the variable priced download to the order.
		edd_add_order_item(
			array(
				'order_id'     => self::$order->id,
				'product_id'   => self::$variable_download->ID,
				'price_id'     => 0,
				'product_name' => self::$variable_download->post_title,
				'status'       => self::$order->status,
				'amount'       => 100,
				'subtotal'     => 100,
				'discount'     => 5,
				'tax'          => 25,
				'total'        => 120,
				'quantity'     => 1,
			)
		);

		// Add the bundled download to the order.
		edd_add_order_item(
			array(
				'order_id'     => self::$order->id,
				'product_id'   => self::$bundled_download->ID,
				'product_name' => self::$bundled_download->post_title,
				'status'       => self::$order->status,
				'amount'       => 100,
				'subtotal'     => 100,
				'discount'     => 5,
				'tax'          => 25,
				'total'        => 120,
				'quantity'     => 1,
			)
		);

		// Since we've added items to the order, we need to update the amounts, so we can process refunds.
		$amount   = 0;
		$subtotal = 0;
		$discount = 0;
		$tax      = 0;
		$total    = 0;

		foreach ( self::$order->items as $item ) {
			$amount   += $item->amount;
			$subtotal += $item->subtotal;
			$discount += $item->discount;
			$tax      += $item->tax;
			$total    += $item->total;
		}

		edd_update_order(
			self::$order->id,
			array(
				'amount'   => $amount,
				'subtotal' => $subtotal,
				'discount' => $discount,
				'tax'      => $tax,
				'total'    => $total,
			)
		);

		// Since we've changed the order, we need to refresh the order object.
		self::$order = edd_get_order( self::$order->id );
	}

	/**
	 * Delete the fixtures when done.
	 */
	public static function tearDownAfterClass(): void {
		parent::tearDownAfterClass();

		// Delete the customer.
		parent::edd()->customer->delete( self::$customer_id );

		// Delete the variable priced download.
		EDD_Helper_Download::delete_download( self::$variable_download->ID );

		// Delete the bundled download.
		EDD_Helper_Download::delete_download( self::$bundled_download->ID );

		// Delete the order.
		parent::edd()->order->delete( self::$order->id );
	}
}
