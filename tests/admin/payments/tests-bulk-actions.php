<?php
/**
 * Orders list table bulk action authorization tests.
 *
 * @package     EDD\Tests\Admin\Payments
 * @copyright   Copyright (c) 2026, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.7.1
 */

namespace EDD\Tests\Admin\Payments;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;

/**
 * Authorization tests for the Orders list table bulk actions.
 *
 * Trashing and restoring an order are the same operations the single-order handlers
 * expose, and those require `delete_shop_payments`. These tests pin the bulk handler
 * to the same gate, pin the single-order handler so the asymmetry cannot be closed
 * from the wrong end, and pin the list table so no role is offered an action its
 * handler will refuse.
 *
 * @since 3.7.1
 * @group edd_orders
 */
class BulkActions extends EDD_UnitTestCase {

	/**
	 * A Shop Accountant: holds edit_shop_payments, lacks delete_shop_payments.
	 *
	 * @since 3.7.1
	 * @var int
	 */
	protected static $accountant;

	/**
	 * A Shop Worker: holds delete_shop_payments.
	 *
	 * @since 3.7.1
	 * @var int
	 */
	protected static $worker;

	/**
	 * A Subscriber: holds no EDD capabilities at all.
	 *
	 * @since 3.7.1
	 * @var int
	 */
	protected static $subscriber;

	/**
	 * The order each test acts on.
	 *
	 * @since 3.7.1
	 * @var int
	 */
	protected $order_id;

	/**
	 * Set up the actors once for the class.
	 *
	 * @since 3.7.1
	 */
	public static function wpSetUpBeforeClass() {
		// Neither the bulk handler nor the list table is loaded outside of wp-admin.
		require_once EDD_PLUGIN_DIR . 'includes/admin/payments/actions.php';
		require_once EDD_PLUGIN_DIR . 'includes/admin/payments/class-payments-table.php';

		EDD()->roles->add_roles();
		EDD()->roles->add_caps();

		self::$accountant = self::factory()->user->create( array( 'role' => 'shop_accountant' ) );
		self::$worker     = self::factory()->user->create( array( 'role' => 'shop_worker' ) );
		self::$subscriber = self::factory()->user->create( array( 'role' => 'subscriber' ) );
	}

	/**
	 * Give each test its own completed order.
	 *
	 * @since 3.7.1
	 */
	public function setUp(): void {
		parent::setUp();

		$this->order_id = parent::edd()->order->create();
	}

	/**
	 * Reset the actor and the request between tests.
	 *
	 * @since 3.7.1
	 */
	public function tearDown(): void {
		wp_set_current_user( 0 );

		$_REQUEST = array();
		$_GET     = array();

		unset( $GLOBALS['hook_suffix'] );

		parent::tearDown();
	}

	/**
	 * A Shop Accountant cannot trash an order through the bulk action.
	 *
	 * @since 3.7.1
	 * @covers ::edd_orders_list_table_process_bulk_actions
	 */
	public function test_bulk_trash_requires_delete_capability() {
		$this->assertTrue(
			user_can( self::$accountant, 'edit_shop_payments' ),
			'Fixture: the Shop Accountant must hold edit_shop_payments, or this test proves nothing.'
		);
		$this->assertFalse( user_can( self::$accountant, 'delete_shop_payments' ) );
		$this->assertSame( 'complete', $this->get_order_status( $this->order_id ) );

		wp_set_current_user( self::$accountant );
		$this->setExpectedIncorrectUsage( 'edd_orders_list_table_process_bulk_actions' );
		$this->stage_bulk_request( 'trash' );

		edd_orders_list_table_process_bulk_actions();

		$this->assertSame( 'complete', $this->get_order_status( $this->order_id ) );
	}

	/**
	 * A Shop Accountant cannot restore a trashed order through the bulk action.
	 *
	 * @since 3.7.1
	 * @covers ::edd_orders_list_table_process_bulk_actions
	 */
	public function test_bulk_restore_requires_delete_capability() {
		edd_trash_order( $this->order_id );

		$this->assertSame( 'trash', $this->get_order_status( $this->order_id ) );
		$this->assertSame(
			'complete',
			edd_get_order_meta( $this->order_id, '_pre_trash_status', true ),
			'Fixture: the order must be restorable, or a refusal is indistinguishable from a failed restore.'
		);
		$this->assertFalse( user_can( self::$accountant, 'delete_shop_payments' ) );

		wp_set_current_user( self::$accountant );
		$this->setExpectedIncorrectUsage( 'edd_orders_list_table_process_bulk_actions' );
		$this->stage_bulk_request( 'restore' );

		edd_orders_list_table_process_bulk_actions();

		$this->assertSame( 'trash', $this->get_order_status( $this->order_id ) );
	}

	/**
	 * The single-order trash handler refuses the same role.
	 *
	 * This is the other half of the asymmetry. It passes today and must keep passing:
	 * the gap has to be closed by raising the bulk gate, never by lowering this one.
	 *
	 * @since 3.7.1
	 * @covers ::edd_trigger_trash_order
	 */
	public function test_single_order_trash_still_refuses_the_same_role() {
		$this->assertTrue( user_can( self::$accountant, 'edit_shop_payments' ) );
		$this->assertFalse( user_can( self::$accountant, 'delete_shop_payments' ) );

		wp_set_current_user( self::$accountant );

		try {
			edd_trigger_trash_order(
				array(
					'_wpnonce'    => wp_create_nonce( 'edd_payment_nonce' ),
					'purchase_id' => $this->order_id,
					'order_type'  => 'sale',
				)
			);
			$this->fail( 'edd_trigger_trash_order() did not refuse a role without delete_shop_payments.' );
		} catch ( \WPDieException $e ) {
			$this->assertStringContainsString( 'do not have permission', $e->getMessage() );
		}

		$this->assertSame( 'complete', $this->get_order_status( $this->order_id ) );
	}

	/**
	 * A Shop Worker, which holds delete_shop_payments, can still bulk trash.
	 *
	 * Without this the gate could be tightened all the way to administrator and
	 * nothing would notice.
	 *
	 * @since 3.7.1
	 * @covers ::edd_orders_list_table_process_bulk_actions
	 */
	public function test_bulk_trash_is_allowed_for_a_role_holding_the_capability() {
		$this->assertTrue(
			user_can( self::$worker, 'delete_shop_payments' ),
			'Fixture: the Shop Worker must hold delete_shop_payments, or this test proves nothing.'
		);

		wp_set_current_user( self::$worker );
		$this->setExpectedIncorrectUsage( 'edd_orders_list_table_process_bulk_actions' );
		$this->stage_bulk_request( 'trash' );

		edd_orders_list_table_process_bulk_actions();

		$this->assertSame( 'trash', $this->get_order_status( $this->order_id ) );
	}

	/**
	 * A Shop Accountant cannot permanently delete an order through the bulk action.
	 *
	 * This label already mapped to delete_shop_payments; the test guards it against
	 * the allowlist that replaces the mapping.
	 *
	 * @since 3.7.1
	 * @covers ::edd_orders_list_table_process_bulk_actions
	 */
	public function test_bulk_delete_still_requires_delete_capability() {
		global $wpdb;

		edd_trash_order( $this->order_id );

		$this->assertSame( 'trash', $this->get_order_status( $this->order_id ) );
		$this->assertSame( 1, $this->count_rows( $wpdb->edd_orders, 'id' ) );
		$this->assertGreaterThan(
			0,
			$this->count_rows( $wpdb->edd_order_items, 'order_id' ),
			'Fixture: the order must have items, or their absence afterwards proves nothing.'
		);
		$this->assertGreaterThan(
			0,
			$this->count_rows( $wpdb->edd_ordermeta, 'edd_order_id' ),
			'Fixture: the order must have meta, or its absence afterwards proves nothing.'
		);
		$this->assertFalse( user_can( self::$accountant, 'delete_shop_payments' ) );

		wp_set_current_user( self::$accountant );
		$this->setExpectedIncorrectUsage( 'edd_orders_list_table_process_bulk_actions' );
		$this->stage_bulk_request( 'delete' );

		edd_orders_list_table_process_bulk_actions();

		$this->assertSame( 1, $this->count_rows( $wpdb->edd_orders, 'id' ) );
		$this->assertGreaterThan( 0, $this->count_rows( $wpdb->edd_order_items, 'order_id' ) );
		$this->assertGreaterThan( 0, $this->count_rows( $wpdb->edd_ordermeta, 'edd_order_id' ) );
	}

	/**
	 * The nonce is verified before any capability decision is made.
	 *
	 * A Subscriber holds neither capability, so if the nonce is only reached after the
	 * capability check it is never verified at all. Asserting the wp_die() from
	 * check_admin_referer() is what pins the ordering rather than the outcome.
	 *
	 * @since 3.7.1
	 * @covers ::edd_orders_list_table_process_bulk_actions
	 */
	public function test_nonce_is_verified_before_the_capability_decision() {
		$this->assertFalse( user_can( self::$subscriber, 'edit_shop_payments' ) );
		$this->assertFalse( user_can( self::$subscriber, 'delete_shop_payments' ) );

		$checks = array();
		add_action(
			'check_admin_referer',
			function ( $action, $result ) use ( &$checks ) {
				$checks[] = array(
					'action' => $action,
					'result' => $result,
				);
			},
			10,
			2
		);

		wp_set_current_user( self::$subscriber );
		$this->setExpectedIncorrectUsage( 'edd_orders_list_table_process_bulk_actions' );
		$this->stage_bulk_request( 'trash', 'not-a-valid-nonce' );

		try {
			edd_orders_list_table_process_bulk_actions();
			$this->fail( 'The invalid nonce was never verified: the handler returned instead of calling wp_die().' );
		} catch ( \WPDieException $e ) {
			$this->assertNotEmpty( $e->getMessage() );
		}

		$this->assertCount( 1, $checks );
		$this->assertSame( 'bulk-orders', $checks[0]['action'] );
		$this->assertFalse( $checks[0]['result'] );

		$this->assertSame( 'complete', $this->get_order_status( $this->order_id ) );
	}

	/**
	 * The list table offers no bulk action the handler will refuse.
	 *
	 * @since 3.7.1
	 * @covers EDD_Payment_History_Table::get_bulk_actions
	 */
	public function test_list_table_offers_no_action_its_handler_refuses() {
		$this->assertFalse( user_can( self::$accountant, 'delete_shop_payments' ) );
		$this->assertTrue( user_can( self::$worker, 'delete_shop_payments' ) );

		$GLOBALS['hook_suffix'] = 'download_page_edd-payment-history';

		wp_set_current_user( self::$accountant );
		$table = new \EDD_Payment_History_Table();

		$actions = $table->get_bulk_actions();
		$this->assertNotEmpty(
			$actions,
			'Fixture: the Shop Accountant must still be offered the status actions, or the negative is vacuous.'
		);
		$this->assertArrayNotHasKey( 'trash', $actions );

		$_REQUEST['status'] = 'trash';
		$trash_actions      = $table->get_bulk_actions();
		$this->assertArrayNotHasKey( 'restore', $trash_actions );
		$this->assertArrayNotHasKey( 'delete', $trash_actions );

		// The role the handler does accept must still be offered all three.
		wp_set_current_user( self::$worker );
		$worker_trash_actions = $table->get_bulk_actions();
		$this->assertArrayHasKey( 'restore', $worker_trash_actions );
		$this->assertArrayHasKey( 'delete', $worker_trash_actions );

		unset( $_REQUEST['status'] );
		$this->assertArrayHasKey( 'trash', $table->get_bulk_actions() );
	}

	/**
	 * The list table offers no row action the handler will refuse.
	 *
	 * @since 3.7.1
	 * @covers EDD_Payment_History_Table::column_number
	 */
	public function test_list_table_offers_no_row_action_its_handler_refuses() {
		$GLOBALS['hook_suffix'] = 'download_page_edd-payment-history';

		$order = edd_get_order( $this->order_id );
		$this->assertNotEmpty( $order );
		$this->assertTrue( edd_is_order_trashable( $order->id ), 'Fixture: the order has to be trashable.' );

		$table = new \EDD_Payment_History_Table();

		wp_set_current_user( self::$worker );
		$this->assertStringContainsString(
			'edd-action=trash_order',
			$table->column_number( $order ),
			'Fixture: the role the handler accepts must be offered the link, or the negative is vacuous.'
		);

		wp_set_current_user( self::$accountant );
		$this->assertStringNotContainsString( 'edd-action=trash_order', $table->column_number( $order ) );

		// The same on a trashed order: restore and delete belong to the handler's role.
		edd_trash_order( $order->id );
		$trashed = edd_get_order( $order->id );
		$this->assertTrue( edd_is_order_restorable( $trashed->id ), 'Fixture: the order has to be restorable.' );

		wp_set_current_user( self::$worker );
		$offered = $table->column_number( $trashed );
		$this->assertStringContainsString( 'edd-action=restore_order', $offered );
		$this->assertStringContainsString( 'edd-action=delete_order', $offered );

		wp_set_current_user( self::$accountant );
		$refused = $table->column_number( $trashed );
		$this->assertStringNotContainsString( 'edd-action=restore_order', $refused );
		$this->assertStringNotContainsString( 'edd-action=delete_order', $refused );
		$this->assertStringNotContainsString( 'view-order-details', $refused, 'A trashed order still has no edit link.' );
	}

	/**
	 * The order details screen offers no trash link its handler will refuse.
	 *
	 * @since 3.7.1
	 * @covers ::edd_order_details_attributes
	 */
	public function test_order_details_offers_no_trash_link_its_handler_refuses() {
		require_once EDD_PLUGIN_DIR . 'includes/admin/payments/orders.php';

		$order = edd_get_order( $this->order_id );
		$this->assertNotEmpty( $order );

		wp_set_current_user( self::$worker );
		$this->assertStringContainsString(
			'edd-action=trash_order',
			$this->render_order_attributes( $order ),
			'Fixture: the role the handler accepts must be offered the link, or the negative is vacuous.'
		);

		wp_set_current_user( self::$accountant );
		$this->assertStringNotContainsString( 'edd-action=trash_order', $this->render_order_attributes( $order ) );
	}

	/**
	 * Capture the order attributes box for an order.
	 *
	 * @since 3.7.1
	 *
	 * @param \EDD\Orders\Order $order The order.
	 * @return string
	 */
	private function render_order_attributes( $order ) {
		ob_start();
		edd_order_details_attributes( $order );

		return ob_get_clean();
	}

	/**
	 * Stage the request the bulk handler reads.
	 *
	 * @since 3.7.1
	 *
	 * @param string $action The bulk action.
	 * @param string $nonce  Optional. The nonce to send. Defaults to a valid one.
	 */
	private function stage_bulk_request( $action, $nonce = '' ) {
		if ( empty( $nonce ) ) {
			$nonce = wp_create_nonce( 'bulk-orders' );
		}

		$_REQUEST['action']   = $action;
		$_REQUEST['_wpnonce'] = $nonce;
		$_GET['order']        = array( $this->order_id );
		$_GET['_wpnonce']     = $nonce;
	}

	/**
	 * Read an order's status straight out of the orders table.
	 *
	 * @since 3.7.1
	 *
	 * @param int $order_id The order ID.
	 * @return string|null
	 */
	private function get_order_status( $order_id ) {
		global $wpdb;

		return $wpdb->get_var( $wpdb->prepare( "SELECT status FROM {$wpdb->edd_orders} WHERE id = %d", $order_id ) );
	}

	/**
	 * Count the rows belonging to this test's order in one of the order tables.
	 *
	 * @since 3.7.1
	 *
	 * @param string $table  The full table name.
	 * @param string $column The column holding the order ID.
	 * @return int
	 */
	private function count_rows( $table, $column ) {
		global $wpdb;

		return (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$table} WHERE {$column} = %d", $this->order_id ) );
	}
}
