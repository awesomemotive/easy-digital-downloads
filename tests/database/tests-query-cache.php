<?php
/**
 * Query-result cache tests for EDD\Database\Query.
 *
 * Verifies that the cache key is built from the compiled SQL, so identical
 * queries share an entry and filtered queries get their own.
 *
 * @package     EDD\Tests\Database
 * @copyright   Copyright (c) 2026, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.6.8
 */

namespace EDD\Tests\Database;

use EDD\Database\Queries\Adjustment as Adjustment_Query;
use EDD\Database\Queries\Customer_Email_Address as Customer_Email_Address_Query;
use EDD\Database\Queries\Order as Order_Query;
use EDD\Tests\PHPUnit\EDD_UnitTestCase;

/**
 * @coversDefaultClass \EDD\Database\Query
 */
class QueryCache extends EDD_UnitTestCase {

	/**
	 * Order fixture.
	 *
	 * @var int
	 */
	protected static $order_id;

	/**
	 * Set up fixtures once.
	 */
	public static function wpSetUpBeforeClass() {
		self::$order_id = parent::edd()->order->create();
		edd_refund_order( self::$order_id );
	}

	/**
	 * Two separate Query instances with identical arguments must produce the
	 * same cache key. Before the sentinel fix, each instance embedded a
	 * per-instance random_bytes(18) value in the key, making them always differ.
	 *
	 * @covers ::get_cache_key
	 */
	public function test_cache_key_is_stable_across_query_instances() {
		$args = array(
			'number' => 10,
			'status' => 'complete',
		);

		$query_a = new Order_Query( $args );
		$query_b = new Order_Query( $args );

		$get_key = new \ReflectionMethod( Order_Query::class, 'get_cache_key' );
		$get_key->setAccessible( true );

		$key_a = $get_key->invoke( $query_a );
		$key_b = $get_key->invoke( $query_b );

		$this->assertSame( $key_a, $key_b );
	}

	/**
	 * A repeated identical query should hit the cache and fire no additional
	 * SQL. If the sentinel fix is absent the second call always misses the
	 * cache because it generates a different key.
	 *
	 * @covers ::get_items
	 */
	public function test_repeated_identical_query_does_not_fire_additional_sql() {
		global $wpdb;

		$args = array(
			'parent' => self::$order_id,
			'type'   => 'refund',
		);

		// Prime the cache.
		edd_get_orders( $args );

		$queries_before = $wpdb->num_queries;
		edd_get_orders( $args );
		$queries_after = $wpdb->num_queries;

		$this->assertSame( $queries_before, $queries_after );
	}

	/**
	 * The Orders list's discount filter must not be served the unfiltered list,
	 * or the cached discount list afterward.
	 *
	 * @covers ::get_cache_key
	 */
	public function test_discount_filtered_orders_do_not_share_the_unfiltered_cache_entry() {
		$discounted_order_id = parent::edd()->order->create();
		$full_price_order_id = parent::edd()->order->create();
		$discount_id         = parent::edd()->discount->create();
		parent::edd()->order_adjustment->create(
			array(
				'object_id'   => $discounted_order_id,
				'object_type' => 'order',
				'type_id'     => $discount_id,
				'type'        => 'discount',
			)
		);

		$args = array(
			'id__in' => array( $discounted_order_id, $full_price_order_id ),
			'fields' => 'ids',
		);

		$this->assertEqualSets( array( $discounted_order_id, $full_price_order_id ), edd_get_orders( $args ) );
		$this->assertEqualSets( array( $discounted_order_id ), edd_get_orders( array_merge( $args, array( 'discount_id' => $discount_id ) ) ) );
		$this->assertEqualSets( array( $discounted_order_id, $full_price_order_id ), edd_get_orders( $args ) );
	}

	/**
	 * A clauses filter keyed on a query var the class never registered, as FES's
	 * vendor dashboard does with product_id__in, still needs its own cache entry.
	 *
	 * @covers ::get_cache_key
	 */
	public function test_clauses_filter_on_unregistered_query_var_gets_its_own_cache_entry() {
		$included_order_id = parent::edd()->order->create();
		$excluded_order_id = parent::edd()->order->create();

		add_filter(
			'edd_orders_query_clauses',
			function ( $clauses, $query ) {
				if ( ! empty( $query->query_vars['only_order_id'] ) ) {
					global $wpdb;
					$clauses['where'] .= $wpdb->prepare( " AND {$query->table_alias}.id = %d", $query->query_vars['only_order_id'] );
				}

				return $clauses;
			},
			10,
			2
		);

		$args = array(
			'id__in' => array( $included_order_id, $excluded_order_id ),
			'fields' => 'ids',
		);

		$this->assertEqualSets( array( $included_order_id, $excluded_order_id ), edd_get_orders( $args ) );
		$this->assertEqualSets( array( $included_order_id ), edd_get_orders( array_merge( $args, array( 'only_order_id' => $included_order_id ) ) ) );
	}

	/**
	 * A clauses filter driven by state outside the query vars, as the Software
	 * Licensing and Simple Shipping Orders list filters read $_GET, has no
	 * query var to hash, so only a key built from the SQL can separate it.
	 *
	 * @covers ::get_cache_key
	 */
	public function test_clauses_filter_on_outside_state_gets_its_own_cache_entry() {
		$included_order_id = parent::edd()->order->create();
		$excluded_order_id = parent::edd()->order->create();
		$restrict_to_id    = 0;

		add_filter(
			'edd_orders_query_clauses',
			function ( $clauses, $query ) use ( &$restrict_to_id ) {
				if ( ! empty( $restrict_to_id ) ) {
					global $wpdb;
					$clauses['where'] .= $wpdb->prepare( " AND {$query->table_alias}.id = %d", $restrict_to_id );
				}

				return $clauses;
			},
			10,
			2
		);

		$args = array(
			'id__in' => array( $included_order_id, $excluded_order_id ),
			'fields' => 'ids',
		);

		$this->assertEqualSets( array( $included_order_id, $excluded_order_id ), edd_get_orders( $args ) );

		$restrict_to_id = $included_order_id;
		$this->assertEqualSets( array( $included_order_id ), edd_get_orders( $args ) );
	}

	/**
	 * The placeholder escape is random per request, so a key that kept it
	 * would give every LIKE search a new cache entry on each page load.
	 *
	 * @covers ::get_cache_key
	 */
	public function test_cache_key_ignores_the_placeholder_escape() {
		global $wpdb;

		$query       = new Order_Query();
		$plain_sql   = "SELECT id FROM {$wpdb->prefix}edd_orders WHERE email LIKE '%foo%'";
		$escaped_sql = $wpdb->add_placeholder_escape( $plain_sql );

		$request = new \ReflectionProperty( Order_Query::class, 'request' );
		$request->setAccessible( true );

		$get_key = new \ReflectionMethod( Order_Query::class, 'get_cache_key' );
		$get_key->setAccessible( true );

		$request->setValue( $query, $escaped_sql );
		$escaped_key = $get_key->invoke( $query );

		$request->setValue( $query, $plain_sql );
		$plain_key = $get_key->invoke( $query );

		$this->assertNotSame( $plain_sql, $escaped_sql );
		$this->assertSame( $plain_key, $escaped_key );
	}

	/**
	 * A list-cache entry stored before the `dependencies` key was added is
	 * still served for a query that declares no cache dependencies.
	 *
	 * @covers ::get_items
	 */
	public function test_cached_value_without_dependencies_is_still_served() {
		$args = array(
			'parent' => self::$order_id,
			'type'   => 'refund',
		);

		$primed_query = new Order_Query( $args );

		$get_key = new \ReflectionMethod( Order_Query::class, 'get_cache_key' );
		$get_key->setAccessible( true );

		wp_cache_set(
			$get_key->invoke( $primed_query ),
			array(
				'item_ids'    => array(),
				'found_items' => 99,
			),
			'edd-orders'
		);

		$cached_query = new Order_Query( $args );

		$this->assertSame( 99, $cached_query->found_items );
	}

	/**
	 * A lookup by a cache-key column's old value, on the instance that primed it
	 * and then changed it, must miss rather than return the updated row.
	 *
	 * @covers ::get_item_by
	 * @covers ::update_item
	 */
	public function test_same_instance_lookup_by_old_value_after_update_returns_false() {
		$discount_id = edd_add_discount(
			array(
				'name'   => 'Same Instance Rename',
				'code'   => 'SAMEINSTANCEOLD',
				'status' => 'active',
				'type'   => 'percent',
				'amount' => '10',
			)
		);

		$query = new Adjustment_Query();

		// Prime the lookup on this instance.
		$this->assertSame( (int) $discount_id, (int) $query->get_item_by( 'code', 'SAMEINSTANCEOLD' )->id );

		$query->update_item( $discount_id, array( 'code' => 'SAMEINSTANCENEW' ) );

		$this->assertFalse( $query->get_item_by( 'code', 'SAMEINSTANCEOLD' ) );
	}

	/**
	 * Updating a column that is not a cache key must keep a primed lookup cached,
	 * and the cached lookup must return the updated row.
	 *
	 * @covers ::get_item_by
	 * @covers ::update_item
	 */
	public function test_non_cache_key_update_keeps_lookup_warm_and_fresh() {
		global $wpdb;

		$discount_id = edd_add_discount(
			array(
				'name'   => 'Original Name',
				'code'   => 'WARMLOOKUP',
				'status' => 'active',
				'type'   => 'percent',
				'amount' => '10',
			)
		);

		$query = new Adjustment_Query();

		// Prime the lookup.
		$query->get_item_by( 'code', 'WARMLOOKUP' );

		$query->update_item( $discount_id, array( 'name' => 'Updated Name' ) );

		$queries_before = $wpdb->num_queries;
		$discount       = $query->get_item_by( 'code', 'WARMLOOKUP' );
		$queries_after  = $wpdb->num_queries;

		$this->assertSame( 'Updated Name', $discount->name );
		$this->assertSame( $queries_before, $queries_after );
	}

	/**
	 * For a column whose values are not unique, deleting the row a primed lookup
	 * returned must make the lookup return a row that still exists.
	 *
	 * @covers ::get_item_by
	 * @covers ::delete_item
	 */
	public function test_lookup_after_delete_returns_surviving_row() {
		$customer_id = parent::edd()->customer->create();
		edd_add_customer_email_address(
			array(
				'customer_id' => $customer_id,
				'email'       => 'first-secondary@example.org',
			)
		);
		edd_add_customer_email_address(
			array(
				'customer_id' => $customer_id,
				'email'       => 'second-secondary@example.org',
			)
		);

		$query = new Customer_Email_Address_Query();

		// Prime the lookup, then delete the row it returned.
		$deleted_address = $query->get_item_by( 'customer_id', $customer_id );
		$query->delete_item( $deleted_address->id );

		$surviving_ids = array_map(
			'intval',
			edd_get_customer_email_addresses(
				array(
					'customer_id' => $customer_id,
					'fields'      => 'ids',
				)
			)
		);

		$address = $query->get_item_by( 'customer_id', $customer_id );

		$this->assertNotFalse( $address );
		$this->assertNotSame( (int) $deleted_address->id, (int) $address->id );
		$this->assertContains( (int) $address->id, $surviving_ids );
	}

	/**
	 * The pointer key is stable per value and the entry carries its generation, so
	 * repeated transitions overwrite it in place instead of orphaning keys.
	 *
	 * @covers ::get_item_by
	 * @covers ::update_item
	 */
	public function test_secondary_pointer_is_stable_key_with_generation() {
		$discount_id = edd_add_discount(
			array(
				'name'   => 'Pointer Shape',
				'code'   => 'POINTERSHAPE',
				'status' => 'active',
				'type'   => 'percent',
				'amount' => '10',
			)
		);

		$query    = new Adjustment_Query();
		$property = new \ReflectionProperty( $query, 'cache_group' );
		$property->setAccessible( true );
		$group = $property->getValue( $query ) . '-by-code';

		$query->get_item_by( 'code', 'POINTERSHAPE' );

		$pointer = wp_cache_get( md5( 'POINTERSHAPE' ), $group );
		$this->assertIsArray( $pointer );
		$this->assertSame( (int) $discount_id, (int) $pointer['item_id'] );
		$this->assertSame( wp_cache_get( 'last_changed', $group ), $pointer['last_changed'] );

		// Transition away and back: the same key is rewritten with the new generation.
		$query->update_item( $discount_id, array( 'code' => 'POINTERSHAPE2' ) );
		$query->update_item( $discount_id, array( 'code' => 'POINTERSHAPE' ) );
		$this->assertSame( (int) $discount_id, (int) $query->get_item_by( 'code', 'POINTERSHAPE' )->id );

		$pointer = wp_cache_get( md5( 'POINTERSHAPE' ), $group );
		$this->assertSame( wp_cache_get( 'last_changed', $group ), $pointer['last_changed'] );
	}

	/**
	 * A scalar or stale pointer is ignored and overwritten in place.
	 *
	 * @covers ::get_item_by
	 */
	public function test_legacy_and_stale_pointers_are_ignored() {
		$discount_id = edd_add_discount(
			array(
				'name'   => 'Legacy Pointer',
				'code'   => 'LEGACYPOINTER',
				'status' => 'active',
				'type'   => 'percent',
				'amount' => '10',
			)
		);
		$other_id    = edd_add_discount(
			array(
				'name'   => 'Other Pointer',
				'code'   => 'OTHERPOINTER',
				'status' => 'active',
				'type'   => 'percent',
				'amount' => '10',
			)
		);

		$query    = new Adjustment_Query();
		$property = new \ReflectionProperty( $query, 'cache_group' );
		$property->setAccessible( true );
		$group = $property->getValue( $query ) . '-by-code';

		$query->get_item_by( 'code', 'LEGACYPOINTER' );
		$key = md5( 'LEGACYPOINTER' );

		// A scalar entry with no generation.
		wp_cache_set( $key, (int) $other_id, $group );
		$this->assertSame( (int) $discount_id, (int) $query->get_item_by( 'code', 'LEGACYPOINTER' )->id );
		$this->assertIsArray( wp_cache_get( $key, $group ) );

		// A pointer stamped with an older generation.
		wp_cache_set(
			$key,
			array(
				'item_id'      => (int) $other_id,
				'last_changed' => 'stale',
			),
			$group
		);
		$this->assertSame( (int) $discount_id, (int) $query->get_item_by( 'code', 'LEGACYPOINTER' )->id );
		$this->assertSame(
			wp_cache_get( 'last_changed', $group ),
			wp_cache_get( $key, $group )['last_changed']
		);
	}

	/**
	 * The generation is read before the database, so a write during the lookup
	 * leaves the stored pointer stamped with the older generation.
	 *
	 * @covers ::get_item_by
	 */
	public function test_secondary_generation_is_captured_before_the_read() {
		global $wpdb;

		edd_add_discount(
			array(
				'name'   => 'Captured Generation',
				'code'   => 'CAPTUREDGEN',
				'status' => 'active',
				'type'   => 'percent',
				'amount' => '10',
			)
		);

		$query    = new Adjustment_Query();
		$property = new \ReflectionProperty( $query, 'cache_group' );
		$property->setAccessible( true );
		$group = $property->getValue( $query ) . '-by-code';
		$key   = md5( 'CAPTUREDGEN' );

		// Start cold, with a known generation.
		wp_cache_delete( $key, $group );
		wp_cache_set( 'last_changed', 'before-read', $group );

		$fired  = false;
		$filter = function ( $sql ) use ( &$fired, $group ) {
			if ( ! $fired && 0 === stripos( ltrim( $sql ), 'SELECT' ) && false !== strpos( $sql, 'edd_adjustments' ) ) {
				$fired = true;
				wp_cache_set( 'last_changed', 'during-read', $group );
			}

			return $sql;
		};

		add_filter( 'query', $filter );
		try {
			$query->get_item_by( 'code', 'CAPTUREDGEN' );
		} finally {
			remove_filter( 'query', $filter );
		}

		$this->assertTrue( $fired );
		$this->assertSame( 'before-read', wp_cache_get( $key, $group )['last_changed'] );
		$this->assertSame( 'during-read', wp_cache_get( 'last_changed', $group ) );

		// The entry no longer matches the group's generation, so the next lookup reads the database.
		$queries = $wpdb->num_queries;
		$query->get_item_by( 'code', 'CAPTUREDGEN' );
		$this->assertGreaterThan( $queries, $wpdb->num_queries );
	}
}
