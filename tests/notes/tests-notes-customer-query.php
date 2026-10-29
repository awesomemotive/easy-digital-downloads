<?php
namespace EDD\Tests\Notes;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;

/**
 * Tests for the customer_id query parameter in the Note query class.
 *
 * @group edd_notes
 * @group edd_notes_db
 * @group database
 *
 * @coversDefaultClass \EDD\Database\Queries\Note
 */
class NotesCustomerQuery extends EDD_UnitTestCase {

	/**
	 * Customer fixture.
	 *
	 * @var \EDD_Customer
	 */
	protected static $customer;

	/**
	 * Order fixture.
	 *
	 * @var \EDD\Orders\Order
	 */
	protected static $order;

	/**
	 * Second order fixture.
	 *
	 * @var \EDD\Orders\Order
	 */
	protected static $order_2;

	/**
	 * Customer note IDs.
	 *
	 * @var array
	 */
	protected static $customer_note_ids = array();

	/**
	 * Manual order note IDs.
	 *
	 * @var array
	 */
	protected static $manual_order_note_ids = array();

	/**
	 * System order note IDs.
	 *
	 * @var array
	 */
	protected static $system_order_note_ids = array();

	/**
	 * Unrelated note ID.
	 *
	 * @var int
	 */
	protected static $unrelated_note_id;

	/**
	 * Set up fixtures once.
	 */
	public static function wpSetUpBeforeClass() {
		// Create a customer.
		self::$customer = parent::edd()->customer->create_and_get(
			array(
				'email' => 'testnotes@example.com',
			)
		);

		// Create two orders for this customer.
		self::$order = parent::edd()->order->create_and_get(
			array(
				'customer_id' => self::$customer->id,
				'status'      => 'complete',
			)
		);

		self::$order_2 = parent::edd()->order->create_and_get(
			array(
				'customer_id' => self::$customer->id,
				'status'      => 'complete',
			)
		);

		// Create customer notes.
		self::$customer_note_ids[] = edd_add_note(
			array(
				'object_id'   => self::$customer->id,
				'object_type' => 'customer',
				'content'     => 'Customer note 1',
				'user_id'     => 1,
			)
		);

		self::$customer_note_ids[] = edd_add_note(
			array(
				'object_id'   => self::$customer->id,
				'object_type' => 'customer',
				'content'     => 'Customer note 2',
				'user_id'     => 1,
			)
		);

		// Create manual order notes (user_id > 0).
		self::$manual_order_note_ids[] = edd_add_note(
			array(
				'object_id'   => self::$order->id,
				'object_type' => 'order',
				'content'     => 'Manual order note on order 1',
				'user_id'     => 1,
			)
		);

		self::$manual_order_note_ids[] = edd_add_note(
			array(
				'object_id'   => self::$order_2->id,
				'object_type' => 'order',
				'content'     => 'Manual order note on order 2',
				'user_id'     => 1,
			)
		);

		// Create system order notes (user_id = 0).
		self::$system_order_note_ids[] = edd_add_note(
			array(
				'object_id'   => self::$order->id,
				'object_type' => 'order',
				'content'     => 'Status changed to complete',
				'user_id'     => 0,
			)
		);

		self::$system_order_note_ids[] = edd_add_note(
			array(
				'object_id'   => self::$order_2->id,
				'object_type' => 'order',
				'content'     => 'PayPal Transaction ID: ABC123',
				'user_id'     => 0,
			)
		);

		// Create an unrelated note on a different object.
		self::$unrelated_note_id = edd_add_note(
			array(
				'object_id'   => 99999,
				'object_type' => 'order',
				'content'     => 'Note on a different order',
				'user_id'     => 1,
			)
		);
	}

	/**
	 * @covers ::query_by_customer
	 */
	public function test_customer_id_returns_customer_notes() {
		$notes = edd_get_notes(
			array(
				'customer_id' => self::$customer->id,
				'number'      => 100,
			)
		);

		$note_ids = array_map( 'intval', wp_list_pluck( $notes, 'id' ) );

		foreach ( self::$customer_note_ids as $id ) {
			$this->assertContains( $id, $note_ids, "Customer note {$id} should be included." );
		}
	}

	/**
	 * @covers ::query_by_customer
	 */
	public function test_customer_id_returns_manual_order_notes() {
		$notes = edd_get_notes(
			array(
				'customer_id' => self::$customer->id,
				'number'      => 100,
			)
		);

		$note_ids = array_map( 'intval', wp_list_pluck( $notes, 'id' ) );

		foreach ( self::$manual_order_note_ids as $id ) {
			$this->assertContains( $id, $note_ids, "Manual order note {$id} should be included." );
		}
	}

	/**
	 * @covers ::query_by_customer
	 */
	public function test_customer_id_excludes_system_order_notes() {
		$notes = edd_get_notes(
			array(
				'customer_id' => self::$customer->id,
				'number'      => 100,
			)
		);

		$note_ids = array_map( 'intval', wp_list_pluck( $notes, 'id' ) );

		foreach ( self::$system_order_note_ids as $id ) {
			$this->assertNotContains( $id, $note_ids, "System order note {$id} should be excluded." );
		}
	}

	/**
	 * @covers ::query_by_customer
	 */
	public function test_customer_id_excludes_unrelated_notes() {
		$notes = edd_get_notes(
			array(
				'customer_id' => self::$customer->id,
				'number'      => 100,
			)
		);

		$note_ids = array_map( 'intval', wp_list_pluck( $notes, 'id' ) );

		$this->assertNotContains( self::$unrelated_note_id, $note_ids, 'Unrelated note should be excluded.' );
	}

	/**
	 * @covers ::query_by_customer
	 */
	public function test_customer_id_returns_correct_total_count() {
		$notes = edd_get_notes(
			array(
				'customer_id' => self::$customer->id,
				'number'      => 100,
			)
		);

		// 2 customer notes + 2 manual order notes = 4.
		$this->assertCount( 4, $notes );
	}

	/**
	 * @covers ::query_by_customer
	 */
	public function test_customer_id_count_query() {
		$count = edd_count_notes(
			array(
				'customer_id' => self::$customer->id,
			)
		);

		// 2 customer notes + 2 manual order notes = 4.
		$this->assertSame( 4, $count );
	}

	/**
	 * @covers ::query_by_customer
	 */
	public function test_customer_id_with_pagination() {
		$page_1 = edd_get_notes(
			array(
				'customer_id' => self::$customer->id,
				'number'      => 2,
				'offset'      => 0,
				'order'       => 'desc',
			)
		);

		$page_2 = edd_get_notes(
			array(
				'customer_id' => self::$customer->id,
				'number'      => 2,
				'offset'      => 2,
				'order'       => 'desc',
			)
		);

		$this->assertCount( 2, $page_1 );
		$this->assertCount( 2, $page_2 );

		// Pages should not overlap.
		$page_1_ids = wp_list_pluck( $page_1, 'id' );
		$page_2_ids = wp_list_pluck( $page_2, 'id' );
		$this->assertEmpty( array_intersect( $page_1_ids, $page_2_ids ), 'Pages should not contain overlapping notes.' );
	}

	/**
	 * @covers ::query_by_customer
	 */
	public function test_customer_id_with_no_orders_returns_only_customer_notes() {
		// Create a customer with no orders.
		$customer = parent::edd()->customer->create_and_get(
			array(
				'email' => 'noorders@example.com',
			)
		);

		$note_id = edd_add_note(
			array(
				'object_id'   => $customer->id,
				'object_type' => 'customer',
				'content'     => 'Only customer note',
				'user_id'     => 1,
			)
		);

		$notes = edd_get_notes(
			array(
				'customer_id' => $customer->id,
				'number'      => 100,
			)
		);

		$this->assertCount( 1, $notes );
		$this->assertEquals( $note_id, $notes[0]->id );
	}

	/**
	 * @covers ::query_by_customer
	 */
	public function test_customer_id_with_no_notes_returns_empty() {
		$customer = parent::edd()->customer->create_and_get(
			array(
				'email' => 'nonotes@example.com',
			)
		);

		$notes = edd_get_notes(
			array(
				'customer_id' => $customer->id,
				'number'      => 100,
			)
		);

		$this->assertCount( 0, $notes );
	}

	/**
	 * @covers ::query_by_customer
	 */
	public function test_customer_id_notes_have_correct_object_types() {
		$notes = edd_get_notes(
			array(
				'customer_id' => self::$customer->id,
				'number'      => 100,
			)
		);

		$object_types = array_unique( wp_list_pluck( $notes, 'object_type' ) );
		sort( $object_types );

		$this->assertSame( array( 'customer', 'order' ), $object_types );
	}

	/**
	 * Verify that refund orders are excluded from the customer notes query.
	 *
	 * @covers ::query_by_customer
	 */
	public function test_customer_id_excludes_refund_order_notes() {
		// Create a refund order for the customer.
		$refund = parent::edd()->order->create_and_get(
			array(
				'customer_id' => self::$customer->id,
				'status'      => 'complete',
				'type'        => 'refund',
			)
		);

		$refund_note_id = edd_add_note(
			array(
				'object_id'   => $refund->id,
				'object_type' => 'order',
				'content'     => 'Refund note',
				'user_id'     => 1,
			)
		);

		$notes    = edd_get_notes(
			array(
				'customer_id' => self::$customer->id,
				'number'      => 100,
			)
		);
		$note_ids = array_map( 'intval', wp_list_pluck( $notes, 'id' ) );

		$this->assertNotContains( $refund_note_id, $note_ids, 'Refund order notes should be excluded.' );
	}

	/**
	 * @covers ::set_query_var_defaults
	 */
	public function test_customer_notes_are_not_served_from_another_customers_cache() {
		// All writes come first, so the notes last_changed value is stable across both reads.
		$other_customer = parent::edd()->customer->create_and_get(
			array(
				'email' => 'othercustomer@example.com',
			)
		);
		$other_note_id  = edd_add_note(
			array(
				'object_id'   => $other_customer->id,
				'object_type' => 'customer',
				'content'     => 'Other customer note',
				'user_id'     => 1,
			)
		);

		$customer_note_ids       = array_map( 'intval', wp_list_pluck( self::$customer->get_notes( 20, 1 ), 'id' ) );
		$other_customer_note_ids = array_map( 'intval', wp_list_pluck( $other_customer->get_notes( 20, 1 ), 'id' ) );

		$expected_customer_note_ids = array_merge( self::$customer_note_ids, self::$manual_order_note_ids );
		sort( $customer_note_ids );
		sort( $expected_customer_note_ids );

		$this->assertSame( array( (int) $other_note_id ), $other_customer_note_ids );
		$this->assertSame( array_map( 'intval', $expected_customer_note_ids ), $customer_note_ids );
	}

	/**
	 * @covers ::set_query_var_defaults
	 */
	public function test_customer_notes_count_is_not_served_from_another_customers_cache() {
		// All writes come first, so the notes last_changed value is stable across both reads.
		$other_customer = parent::edd()->customer->create_and_get(
			array(
				'email' => 'othercustomer@example.com',
			)
		);
		edd_add_note(
			array(
				'object_id'   => $other_customer->id,
				'object_type' => 'customer',
				'content'     => 'Other customer note',
				'user_id'     => 1,
			)
		);

		$customer_count       = self::$customer->get_notes_count();
		$other_customer_count = $other_customer->get_notes_count();

		$this->assertSame( 1, $other_customer_count );
		$this->assertSame( 4, $customer_count );
	}

	/**
	 * @covers ::set_query_var_defaults
	 */
	public function test_repeated_customer_notes_query_is_served_from_cache() {
		global $wpdb;

		$args = array(
			'customer_id' => self::$customer->id,
			'number'      => 20,
		);

		edd_get_notes( $args );

		$queries_before = $wpdb->num_queries;
		edd_get_notes( $args );
		$queries_after = $wpdb->num_queries;

		$this->assertSame( $queries_before, $queries_after );
	}

	/**
	 * @covers ::get_cache_dependency_queries
	 */
	public function test_moving_order_to_another_customer_moves_its_notes() {
		$previous_customer = parent::edd()->customer->create_and_get( array( 'email' => 'previouscustomer@example.com' ) );
		$new_customer      = parent::edd()->customer->create_and_get( array( 'email' => 'newcustomer@example.com' ) );
		$moved_order       = $this->create_order_with_manual_note( $previous_customer->id );
		$moved_note_id     = $moved_order['note_id'];

		$this->assertContains( $moved_note_id, $this->get_customer_note_ids( $previous_customer ) );
		$this->assertNotContains( $moved_note_id, $this->get_customer_note_ids( $new_customer ) );

		$notes_last_changed = wp_cache_get( 'last_changed', 'edd-notes' );
		edd_update_order( $moved_order['order_id'], array( 'customer_id' => $new_customer->id ) );

		$this->assertSame( $notes_last_changed, wp_cache_get( 'last_changed', 'edd-notes' ) );
		$this->assertNotContains( $moved_note_id, $this->get_customer_note_ids( $previous_customer ) );
		$this->assertContains( $moved_note_id, $this->get_customer_note_ids( $new_customer ) );
	}

	/**
	 * @covers ::get_cache_dependency_queries
	 */
	public function test_moving_order_to_another_customer_moves_its_notes_count() {
		$previous_customer = parent::edd()->customer->create_and_get( array( 'email' => 'previouscustomer@example.com' ) );
		$new_customer      = parent::edd()->customer->create_and_get( array( 'email' => 'newcustomer@example.com' ) );
		$moved_order       = $this->create_order_with_manual_note( $previous_customer->id );

		$previous_customer_count = $previous_customer->get_notes_count();
		$new_customer_count      = $new_customer->get_notes_count();

		edd_update_order( $moved_order['order_id'], array( 'customer_id' => $new_customer->id ) );

		$this->assertSame( $previous_customer_count - 1, $previous_customer->get_notes_count() );
		$this->assertSame( $new_customer_count + 1, $new_customer->get_notes_count() );
	}

	/**
	 * @covers \EDD\Database\Query::get_items
	 */
	public function test_corrected_customer_notes_are_cached_again_after_order_move() {
		global $wpdb;

		$previous_customer = parent::edd()->customer->create_and_get( array( 'email' => 'previouscustomer@example.com' ) );
		$new_customer      = parent::edd()->customer->create_and_get( array( 'email' => 'newcustomer@example.com' ) );
		$moved_order       = $this->create_order_with_manual_note( $previous_customer->id );
		$moved_note_id     = $moved_order['note_id'];
		$args              = array(
			'customer_id' => $previous_customer->id,
			'number'      => 20,
		);

		$this->assertContains( $moved_note_id, array_map( 'intval', wp_list_pluck( edd_get_notes( $args ), 'id' ) ) );

		edd_update_order( $moved_order['order_id'], array( 'customer_id' => $new_customer->id ) );

		$corrected_note_ids = array_map( 'intval', wp_list_pluck( edd_get_notes( $args ), 'id' ) );

		$queries_before  = $wpdb->num_queries;
		$cached_note_ids = array_map( 'intval', wp_list_pluck( edd_get_notes( $args ), 'id' ) );
		$queries_after   = $wpdb->num_queries;

		$this->assertNotContains( $moved_note_id, $corrected_note_ids );
		$this->assertSame( $corrected_note_ids, $cached_note_ids );
		$this->assertSame( $queries_before, $queries_after );
	}

	/**
	 * @covers ::get_cache_dependency_queries
	 */
	public function test_deleting_order_removes_its_notes_from_customer_notes() {
		$customer      = parent::edd()->customer->create_and_get( array( 'email' => 'deletedordercustomer@example.com' ) );
		$deleted_order = $this->create_order_with_manual_note( $customer->id );
		$note_id       = $deleted_order['note_id'];

		$this->assertContains( $note_id, $this->get_customer_note_ids( $customer ) );

		edd_delete_order( $deleted_order['order_id'] );

		$this->assertNotFalse( edd_get_note( $note_id ), 'The note row should be left in place.' );
		$this->assertNotContains( $note_id, $this->get_customer_note_ids( $customer ) );
	}

	/**
	 * @covers ::get_cache_dependency_queries
	 */
	public function test_order_write_keeps_non_customer_notes_queries_cached() {
		global $wpdb;

		$previous_customer = parent::edd()->customer->create_and_get( array( 'email' => 'previouscustomer@example.com' ) );
		$new_customer      = parent::edd()->customer->create_and_get( array( 'email' => 'newcustomer@example.com' ) );
		$moved_order       = $this->create_order_with_manual_note( $previous_customer->id );
		$args              = array(
			'object_id'   => $moved_order['order_id'],
			'object_type' => 'order',
		);

		edd_get_notes( $args );

		edd_update_order( $moved_order['order_id'], array( 'customer_id' => $new_customer->id ) );

		$queries_before = $wpdb->num_queries;
		edd_get_notes( $args );
		$queries_after = $wpdb->num_queries;

		$this->assertSame( $queries_before, $queries_after );
	}

	/**
	 * Creates an order for a customer with one manual note on it.
	 *
	 * @param int $customer_id Customer ID.
	 * @return array The order ID and note ID.
	 */
	private function create_order_with_manual_note( $customer_id ) {
		$order_id = parent::edd()->order->create(
			array(
				'customer_id' => $customer_id,
				'status'      => 'complete',
			)
		);
		$note_id  = edd_add_note(
			array(
				'object_id'   => $order_id,
				'object_type' => 'order',
				'content'     => 'Manual order note',
				'user_id'     => 1,
			)
		);

		return array(
			'order_id' => (int) $order_id,
			'note_id'  => (int) $note_id,
		);
	}

	/**
	 * Gets the IDs of a customer's notes.
	 *
	 * @param \EDD_Customer $customer Customer.
	 * @return int[]
	 */
	private function get_customer_note_ids( $customer ) {
		return array_map( 'intval', wp_list_pluck( $customer->get_notes( 20, 1 ), 'id' ) );
	}
}
