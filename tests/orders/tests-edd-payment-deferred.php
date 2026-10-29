<?php
/**
 * EDD_Payment deferred property loading tests.
 *
 * @group edd_orders
 * @group edd_payment
 */

namespace EDD\Tests\Orders;

use EDD\Tests\Helpers;
use EDD\Tests\PHPUnit\EDD_UnitTestCase;

/**
 * @coversDefaultClass \EDD_Payment
 */
class EDD_Payment_Deferred extends EDD_UnitTestCase {

	/**
	 * Order IDs created by a test, torn down after it.
	 *
	 * @var array
	 */
	private $payment_ids = array();

	/**
	 * Discount IDs created by a test, torn down after it.
	 *
	 * @var array
	 */
	private $discount_ids = array();

	public function set_up(): void {
		parent::set_up();
	}

	public function tear_down(): void {
		remove_all_filters( 'edd/payments/defer_properties' );

		foreach ( $this->discount_ids as $discount_id ) {
			Helpers\EDD_Helper_Discount::delete_discount( $discount_id );
		}

		$this->discount_ids = array();

		foreach ( $this->payment_ids as $payment_id ) {
			Helpers\EDD_Helper_Payment::delete_payment( $payment_id );
			edd_destroy_order( $payment_id );
		}

		$this->payment_ids = array();

		parent::tear_down();
	}

	public function test_deferred_payment_matches_eager_payment() {
		$payment_id = $this->create_payment();

		$this->assertSame(
			$this->get_properties( $payment_id, false ),
			$this->get_properties( $payment_id, true )
		);
	}

	public function test_deferred_payment_with_fee_matches_eager_payment() {
		$payment_id = $this->create_payment( 'fee' );

		$this->assertSame(
			$this->get_properties( $payment_id, false ),
			$this->get_properties( $payment_id, true )
		);
	}

	public function test_deferred_payment_with_tax_matches_eager_payment() {
		$payment_id = $this->create_payment( 'tax' );

		$this->assertSame(
			$this->get_properties( $payment_id, false ),
			$this->get_properties( $payment_id, true )
		);
	}

	/**
	 * A billing address and `_option_` order item meta are both read while building
	 * user_info and cart_details, so they have to survive the deferral too.
	 */
	public function test_deferred_payment_with_address_and_item_options_matches_eager_payment() {
		$payment_id = $this->create_payment();

		edd_add_order_address(
			array(
				'order_id'    => $payment_id,
				'type'        => 'billing',
				'first_name'  => 'Ford',
				'last_name'   => 'Prefect',
				'address'     => '155 Country Lane',
				'city'        => 'Cottington',
				'region'      => 'NC',
				'postal_code' => '27513',
				'country'     => 'US',
			)
		);

		$items = edd_get_order_items( array( 'order_id' => $payment_id ) );

		edd_add_order_item_meta( reset( $items )->id, '_option_size', 'large' );

		$this->assertSame(
			$this->get_properties( $payment_id, false ),
			$this->get_properties( $payment_id, true )
		);
	}

	public function test_construction_costs_fewer_queries_than_eager_construction() {
		$payment_id = $this->create_payment( 'fee' );

		$deferred = $this->count_construction_queries( $payment_id, true );
		$eager    = $this->count_construction_queries( $payment_id, false );

		$this->assertLessThan( $eager, $deferred );
	}

	/**
	 * Guards the point of the change rather than a query count: the order items are
	 * fetched because something asked for the cart, not because an object was built.
	 */
	public function test_reading_cart_details_is_what_queries_the_order_items() {
		global $wpdb;

		$payment_id = $this->create_payment();

		wp_cache_flush();

		$payment = edd_get_payment( $payment_id );
		$before  = $wpdb->num_queries;

		$payment->cart_details;

		$this->assertGreaterThan( $before, $wpdb->num_queries );
	}

	public function test_deferred_payment_with_discount_matches_eager_payment() {
		$payment_id = $this->create_payment();

		$this->add_discount( $payment_id );

		$this->assertSame(
			$this->get_properties( $payment_id, false ),
			$this->get_properties( $payment_id, true )
		);
	}

	/**
	 * array_convert() loads discounts before user_info, which hides whether user_info
	 * can load its discount on its own.
	 */
	public function test_user_info_reports_the_discount_when_read_first() {
		$payment_id = $this->create_payment();
		$code       = $this->add_discount( $payment_id );

		wp_cache_flush();

		$this->assertSame( $code, edd_get_payment( $payment_id )->user_info['discount'] );
	}

	/**
	 * save() writes the order discount as the sum of cart_details, so an unloaded cart
	 * zeroes it on any save.
	 */
	public function test_saving_an_unrelated_change_keeps_the_order_discount() {
		$payment_id = $this->create_payment();
		$items      = edd_get_order_items( array( 'order_id' => $payment_id ) );

		edd_update_order_item( reset( $items )->id, array( 'discount' => 5 ) );
		edd_update_order( $payment_id, array( 'discount' => 5 ) );

		wp_cache_flush();

		$payment                 = edd_get_payment( $payment_id );
		$payment->transaction_id = 'changed-after-discount';
		$payment->save();

		wp_cache_flush();

		$this->assertSame( 5.0, (float) edd_get_order( $payment_id )->discount );
	}

	public function test_array_convert_leaves_nothing_deferred() {
		$payment = edd_get_payment( $this->create_payment( 'fee' ) );

		$properties = $payment->array_convert();

		$this->assertEmpty( $properties['deferred'] );
	}

	/**
	 * `empty()` and `isset()` route through __isset(), which PHP calls before __get(),
	 * so a deferred property has to load there or every emptiness guard reports empty.
	 */
	public function test_empty_check_loads_a_deferred_property() {
		$payment = edd_get_payment( $this->create_payment( 'fee' ) );

		$this->assertFalse( empty( $payment->fees ) );
		$this->assertFalse( empty( $payment->cart_details ) );
		$this->assertFalse( empty( $payment->payment_meta ) );
	}

	public function test_nested_offset_check_loads_a_deferred_property() {
		$payment = edd_get_payment( $this->create_payment() );

		$this->assertTrue( isset( $payment->cart_details[0]['item_price'] ) );
	}

	/**
	 * These four emptiness guards each sit in front of live extension behavior where a
	 * false negative loses work silently rather than erroring: All Access passes not
	 * expiring, Stripe refunds not issued, and VAT exports coming out blank.
	 */
	public function test_emptiness_guards_relied_on_by_extensions_still_report_populated() {
		$payment = edd_get_payment( $this->create_payment() );

		$this->assertFalse( empty( $payment->downloads ) );
		$this->assertFalse( empty( $payment->transaction_id ) );
		$this->assertFalse( empty( $payment->cart_details ) );
	}

	public function test_address_offset_check_reports_populated() {
		$payment_id = $this->create_payment();

		edd_add_order_address(
			array(
				'order_id' => $payment_id,
				'type'     => 'billing',
				'country'  => 'US',
				'region'   => 'NC',
			)
		);

		$payment = edd_get_payment( $payment_id );

		$this->assertTrue( isset( $payment->address['country'] ) );
	}

	/**
	 * A write has to cancel the pending load, or the read after it returns the stored
	 * value instead of what the caller just set.
	 */
	public function test_written_property_is_not_overwritten_by_a_later_read() {
		$payment = edd_get_payment( $this->create_payment() );

		$payment->transaction_id = 'written-not-loaded';

		$this->assertSame( 'written-not-loaded', $payment->transaction_id );
	}

	/**
	 * save() writes the order discount as the sum of cart_details, so a load stuck after
	 * one failure would zero it on the next save.
	 */
	public function test_failed_load_is_retried_on_the_next_read() {
		$payment_id = $this->create_payment();
		$items      = edd_get_order_items( array( 'order_id' => $payment_id ) );

		edd_update_order_item( reset( $items )->id, array( 'discount' => 5 ) );
		edd_update_order( $payment_id, array( 'discount' => 5 ) );

		wp_cache_flush();

		$payment = edd_get_payment( $payment_id );

		$throw_once = function () use ( &$throw_once ) {
			remove_filter( 'get_edd_order_item_metadata', $throw_once );

			throw new \RuntimeException( 'Order item meta unavailable.' );
		};
		add_filter( 'get_edd_order_item_metadata', $throw_once );

		try {
			$payment->cart_details;
		} catch ( \RuntimeException $exception ) {
			// The read after this one is under test.
		}

		$this->assertCount( count( $items ), $payment->cart_details );

		$payment->transaction_id = 'changed-after-failed-load';
		$payment->save();

		wp_cache_flush();

		$this->assertSame( 5.0, (float) edd_get_order( $payment_id )->discount );
	}

	/**
	 * setup_user_id() backfills the order's user from its customer, so it has to see the
	 * stored customer rather than one the caller has not saved yet.
	 *
	 * @dataProvider data_defer_modes
	 *
	 * @param bool $defer Whether properties should load on first read.
	 */
	public function test_changing_the_customer_does_not_relink_the_user_before_save( $defer ) {
		$payment_id     = $this->create_payment();
		$stored_user_id = (int) edd_get_order( $payment_id )->user_id;
		$other_customer = parent::edd()->customer->create( array( 'user_id' => self::factory()->user->create() ) );

		$this->defer_properties( $defer );

		wp_cache_flush();

		$payment              = edd_get_payment( $payment_id );
		$payment->customer_id = $other_customer;

		$this->assertSame( $stored_user_id, (int) $payment->user_id );

		wp_cache_flush();

		$this->assertSame( $stored_user_id, (int) edd_get_order( $payment_id )->user_id );
	}

	/**
	 * Old code built payment_meta in the constructor, so it held the stored values until
	 * save() ran.
	 *
	 * @dataProvider data_defer_modes
	 *
	 * @param bool $defer Whether properties should load on first read.
	 */
	public function test_payment_meta_reports_stored_email_after_an_unsaved_write( $defer ) {
		$payment_id   = $this->create_payment();
		$stored_email = edd_get_order( $payment_id )->email;

		$this->defer_properties( $defer );

		wp_cache_flush();

		$payment        = edd_get_payment( $payment_id );
		$payment->email = 'unsaved@example.com';

		$this->assertSame( $stored_email, $payment->payment_meta['email'] );
	}

	public function test_unsaved_payment_has_declared_defaults() {
		$payment = new \EDD_Payment();

		$this->assertSame( array(), $payment->cart_details );
		$this->assertSame( array(), $payment->payment_meta );
		$this->assertSame( 'none', $payment->discounts );
	}

	/**
	 * recalculate_total() reads fees_total directly, so a fresh object has to load it
	 * there or the fee falls out of the total.
	 */
	public function test_increase_tax_keeps_fees_in_the_total() {
		$payment  = edd_get_payment( $this->create_payment( 'fee' ) );
		$expected = (float) $payment->total + 1;

		$payment->increase_tax( 1 );

		$this->assertEqualsWithDelta( $expected, (float) $payment->total, 0.001 );
	}

	public function test_writing_the_deferred_map_does_not_empty_the_cart() {
		$payment_id = $this->create_payment();
		$payment    = edd_get_payment( $payment_id );

		$payment->deferred = array();

		$this->assertCount( count( edd_get_order_items( array( 'order_id' => $payment_id ) ) ), $payment->cart_details );
	}

	public function test_writing_the_deferred_map_is_not_queued_for_save() {
		$saved_keys = array();

		add_action(
			'edd_payment_save',
			function ( $payment, $key ) use ( &$saved_keys ) {
				$saved_keys[] = $key;
			},
			10,
			2
		);

		$payment           = edd_get_payment( $this->create_payment() );
		$payment->deferred = array();
		$payment->save();

		$this->assertNotContains( 'deferred', $saved_keys );
	}

	/**
	 * The address only reaches the order when save() sees payment_meta change, so the
	 * stored payment_meta has to be read before the write replaces user_info.
	 *
	 * @dataProvider data_defer_modes
	 *
	 * @param bool $defer Whether properties should load on first read.
	 */
	public function test_saving_a_direct_user_info_write_updates_the_order_address( $defer ) {
		$payment_id = $this->create_payment();

		$this->defer_properties( $defer );

		// Read from a separate object, so the one under test stays unloaded.
		$user_info            = edd_get_payment( $payment_id )->user_info;
		$user_info['address'] = array(
			'line1'   => '155 Country Lane',
			'line2'   => '',
			'city'    => 'Cottington',
			'state'   => 'NC',
			'zip'     => '27513',
			'country' => 'US',
		);

		wp_cache_flush();

		$payment            = edd_get_payment( $payment_id );
		$payment->user_info = $user_info;
		$payment->save();

		wp_cache_flush();

		$this->assertSame( 'Cottington', edd_get_order( $payment_id )->get_address()->city );
	}

	/**
	 * Both loading modes, keyed for readable failure output.
	 *
	 * @return array
	 */
	public function data_defer_modes() {
		return array(
			'deferred' => array( true ),
			'eager'    => array( false ),
		);
	}

	/**
	 * Create a payment and register it for teardown.
	 *
	 * @param string $flavor One of 'simple', 'fee', or 'tax'.
	 * @return int
	 */
	private function create_payment( $flavor = 'simple' ) {
		switch ( $flavor ) {
			case 'fee':
				$payment_id = Helpers\EDD_Helper_Payment::create_simple_payment_with_fee();
				break;

			case 'tax':
				$payment_id = Helpers\EDD_Helper_Payment::create_simple_payment_with_tax();
				break;

			default:
				$payment_id = Helpers\EDD_Helper_Payment::create_simple_payment();
				break;
		}

		$this->payment_ids[] = $payment_id;

		return $payment_id;
	}

	/**
	 * Apply a discount to an order and register it for teardown.
	 *
	 * @param int $payment_id The order ID.
	 * @return string The discount code.
	 */
	private function add_discount( $payment_id ) {
		$discount_id = Helpers\EDD_Helper_Discount::create_simple_percent_discount();
		$discount    = edd_get_discount( $discount_id );

		$this->discount_ids[] = $discount_id;

		edd_add_order_adjustment(
			array(
				'object_id'   => $payment_id,
				'object_type' => 'order',
				'type'        => 'discount',
				'type_id'     => $discount_id,
				'description' => $discount->code,
				'subtotal'    => 2,
				'total'       => 2,
			)
		);

		return $discount->code;
	}

	/**
	 * Count the queries one construction costs.
	 *
	 * @param int  $payment_id The order ID.
	 * @param bool $defer      Whether properties should load on first read.
	 * @return int
	 */
	private function count_construction_queries( $payment_id, $defer ) {
		global $wpdb;

		$this->defer_properties( $defer );

		wp_cache_flush();

		$before = $wpdb->num_queries;

		edd_get_payment( $payment_id );

		return $wpdb->num_queries - $before;
	}

	/**
	 * Build a payment and return its properties for comparison.
	 *
	 * The order object and the loading bookkeeping are dropped: they are two separate
	 * instances holding the same data, and comparing them adds nothing the properties
	 * built from them do not already prove.
	 *
	 * @param int  $payment_id The order ID.
	 * @param bool $defer      Whether properties should load on first read.
	 * @return array
	 */
	private function get_properties( $payment_id, $defer ) {
		$this->defer_properties( $defer );

		// Berlin caches rows, so clear it to give both passes the same starting point.
		wp_cache_flush();

		$properties = edd_get_payment( $payment_id )->array_convert();

		unset( $properties['order'], $properties['deferred'], $properties['loading'] );

		return $properties;
	}

	/**
	 * Choose whether payments built after this call load properties on first read.
	 *
	 * @param bool $defer Whether properties should load on first read.
	 * @return void
	 */
	private function defer_properties( $defer ) {
		remove_all_filters( 'edd/payments/defer_properties' );

		add_filter(
			'edd/payments/defer_properties',
			function () use ( $defer ) {
				return $defer;
			}
		);
	}
}
