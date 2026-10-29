<?php
/**
 * Customer deletion capability tests.
 *
 * @package     EDD\Tests\Customers
 * @copyright   Copyright (c) 2026, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.7.1
 */

namespace EDD\Tests\Customers;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;
use EDD\Tests\Helpers\EDD_Helper_Payment;

/**
 * Customer deletion capability tests.
 *
 * @group edd_customers
 */
class CustomerDeleteCapability extends EDD_UnitTestCase {

	/**
	 * The customer under test.
	 *
	 * @var \EDD_Customer
	 */
	protected $customer;

	/**
	 * The order attached to that customer.
	 *
	 * @var int
	 */
	protected $order_id;

	public static function wpSetUpBeforeClass() {
		// Admin includes are only loaded for admin requests, and the delete view calls into
		// customer-functions.php for its header.
		require_once EDD_PLUGIN_DIR . 'includes/admin/customers/customer-actions.php';
		require_once EDD_PLUGIN_DIR . 'includes/admin/customers/customer-functions.php';
		require_once EDD_PLUGIN_DIR . 'includes/admin/customers/customers.php';
	}

	public function setUp(): void {
		parent::setUp();

		$this->order_id = EDD_Helper_Payment::create_simple_payment();
		$order          = edd_get_order( $this->order_id );
		$this->customer = new \EDD_Customer( $order->customer_id );

		// The handler only runs for admin requests.
		set_current_screen( 'edit-shop_order' );
	}

	public function tearDown(): void {
		wp_set_current_user( 0 );
		$_POST = array();

		parent::tearDown();
	}

	/**
	 * The arguments the delete form submits.
	 *
	 * @param bool $remove_data Whether to ask for the attached orders to be destroyed.
	 * @return array
	 */
	private function delete_args( $remove_data ) {
		$args = array(
			'customer_id'                 => $this->customer->id,
			'edd-customer-delete-confirm' => '1',
			'_wpnonce'                    => wp_create_nonce( 'delete-customer' ),
		);

		if ( $remove_data ) {
			$args['edd-customer-delete-records'] = '1';
		}

		return $args;
	}

	/**
	 * A role which cannot delete an order must not destroy orders through customer deletion.
	 */
	public function test_a_role_without_delete_shop_payments_cannot_destroy_the_orders() {
		$user_id = $this->factory->user->create( array( 'role' => 'shop_accountant' ) );

		$this->assertTrue(
			user_can( $user_id, edd_get_edit_customers_role() ),
			'A shop accountant can edit customers, which is what makes this reachable.'
		);
		$this->assertFalse(
			user_can( $user_id, 'delete_shop_payments' ),
			'A shop accountant cannot delete orders, which is the rule being enforced.'
		);

		wp_set_current_user( $user_id );

		$refused = false;
		try {
			edd_customer_delete( $this->delete_args( true ) );
		} catch ( \WPDieException $e ) {
			$refused = true;
		}

		$this->assertTrue( $refused, 'The request must be refused.' );
		$this->assertInstanceOf(
			'EDD\Orders\Order',
			edd_get_order( $this->order_id ),
			'The order must still exist.'
		);
	}

	/**
	 * That role must still be able to delete the customer itself.
	 *
	 * The control that keeps the fix from taking away something legitimate: deleting a customer
	 * is permitted, and it detaches the orders rather than destroying them.
	 */
	public function test_a_role_without_delete_shop_payments_can_still_delete_the_customer() {
		$user_id = $this->factory->user->create( array( 'role' => 'shop_accountant' ) );
		wp_set_current_user( $user_id );

		$customer_id = $this->customer->id;

		try {
			edd_customer_delete( $this->delete_args( false ) );
		} catch ( \WPDieException $e ) {
			$this->fail( 'Deleting the customer without its records must be allowed.' );
		}

		$this->assertFalse( edd_get_customer( $customer_id ), 'The customer must be gone.' );

		$order = edd_get_order( $this->order_id );

		$this->assertInstanceOf( 'EDD\Orders\Order', $order, 'The order must survive.' );
		$this->assertSame( 0, (int) $order->customer_id, 'The order must be assigned to no customer.' );
	}

	/**
	 * The delete-records option must not be offered to a role that cannot use it.
	 *
	 * The server-side gate is the fix; this keeps the screen honest so the only way to reach a
	 * 403 is by crafting the request rather than by checking a box the screen offered.
	 */
	public function test_the_delete_records_option_is_not_offered_without_the_capability() {
		wp_set_current_user( $this->factory->user->create( array( 'role' => 'shop_accountant' ) ) );

		ob_start();
		edd_customers_delete_view( $this->customer );
		$refused = ob_get_clean();

		$this->assertStringNotContainsString(
			'edd-customer-delete-records',
			$refused,
			'The option must not be rendered for a role which cannot delete orders.'
		);
		$this->assertStringContainsString(
			'edd-customer-delete-confirm',
			$refused,
			'Deleting the customer itself is still offered.'
		);
		$this->assertStringContainsString(
			'Associated payments and records will be retained',
			$refused,
			'The screen must say what will happen to the orders and why the option is unavailable.'
		);

		wp_set_current_user( $this->factory->user->create( array( 'role' => 'shop_manager' ) ) );

		ob_start();
		edd_customers_delete_view( $this->customer );
		$allowed = ob_get_clean();

		$this->assertStringContainsString(
			'edd-customer-delete-records',
			$allowed,
			'The option must still be offered to a role which can delete orders.'
		);
		$this->assertStringNotContainsString(
			'Associated payments and records will be retained',
			$allowed,
			'The explanation is only for the role that cannot choose.'
		);
	}

	/**
	 * A role which can delete orders must still be able to destroy them this way.
	 *
	 * The control for the rule itself: without it, refusing everyone would pass.
	 */
	public function test_a_role_with_delete_shop_payments_can_destroy_the_orders() {
		$user_id = $this->factory->user->create( array( 'role' => 'shop_manager' ) );

		$this->assertTrue( user_can( $user_id, 'delete_shop_payments' ), 'A shop manager can delete orders.' );

		wp_set_current_user( $user_id );

		try {
			edd_customer_delete( $this->delete_args( true ) );
		} catch ( \WPDieException $e ) {
			$this->fail( 'A shop manager must be able to delete a customer and its records.' );
		}

		$this->assertFalse( edd_get_order( $this->order_id ), 'The order must be destroyed.' );
	}
}
