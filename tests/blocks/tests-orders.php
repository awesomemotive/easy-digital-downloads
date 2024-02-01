<?php
namespace EDD\Tests\Blocks;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;
use EDD\Blocks\Orders as Orders_Block;
use EDD\Tests\Helpers;

/**
 * API List Table tests.
 *
 * @group blocks
 */
class Orders extends EDD_UnitTestCase {

	protected static $customer_id;

	protected static $customer;

	public static function wpSetupBeforeClass() {
		require_once EDD_PLUGIN_DIR . 'includes/blocks/includes/orders/orders.php';

		// Create a customer for the current user.
		self::$customer_id = parent::edd()->customer->create( array( 'user_id' => get_current_user_id() ) );
		self::$customer    = new \EDD_Customer( self::$customer_id );
	}

	public function setUp(): void {
		Helpers\EDD_Helper_Download::add_download_files();
	}

	public function tearDown(): void {
		Helpers\EDD_Helper_Download::remove_download_files();
	}

	public function test_get_purchased_products_default_args_no_orders() {
		$products = Orders_Block\get_purchased_products(
			array(
				'search'     => false,
				'variations' => true,
				'nofiles'    => __( 'No downloadable files found.', 'easy-digital-downloads' ),
				'hide_empty' => true,
			)
		);

		$this->assertFalse( $products );
	}

	public function test_get_purchased_products_has_orders() {
		$order = parent::edd()->order->create_and_get(
			array(
				'customer_id' => self::$customer_id,
				'user_id'     => get_current_user_id(),
				'email'       => self::$customer->email,
				'status'      => 'complete',
			)
		);

		$products = Orders_Block\get_purchased_products(
			array(
				'search'     => false,
				'variations' => true,
				'nofiles'    => __( 'No downloadable files found.', 'easy-digital-downloads' ),
				'hide_empty' => true,
			)
		);

		$this->assertSame( 1, count( $products ) );

		// Clean up.
		parent::edd()->order->delete( $order->ID );
	}

	public function test_get_purchased_products_has_orders_all_not_deliverable() {
		$order = parent::edd()->order->create_and_get(
			array(
				'customer_id' => self::$customer_id,
				'user_id'     => get_current_user_id(),
				'email'       => self::$customer->email,
				'status'      => 'refunded',
			)
		);


		$products = Orders_Block\get_purchased_products(
			array(
				'search'     => false,
				'variations' => true,
				'nofiles'    => __( 'No downloadable files found.', 'easy-digital-downloads' ),
				'hide_empty' => true,
			)
		);

		$this->assertFalse( $products );

		// Clean up.
		parent::edd()->order->delete( $order->ID );
	}

	public function test_get_purchased_products_has_orders_single_not_deliverable() {
		$order = parent::edd()->order->create_and_get(
			array(
				'customer_id' => self::$customer_id,
				'user_id'     => get_current_user_id(),
				'email'       => self::$customer->email,
				'status'      => 'completed',
			)
		);

		edd_update_order_item(
			$order->items[0]->id,
			array(
				'status' => 'refunded',
			)
		);

		$products = Orders_Block\get_purchased_products(
			array(
				'search'     => false,
				'variations' => true,
				'nofiles'    => __( 'No downloadable files found.', 'easy-digital-downloads' ),
				'hide_empty' => true,
			)
		);

		$this->assertFalse( $products );

		// Clean up.
		parent::edd()->order->delete( $order->ID );
	}
}
