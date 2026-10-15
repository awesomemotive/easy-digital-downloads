<?php
/**
 * Query-result cache tests for EDD\Database\Query.
 *
 * Verifies that the sentinel-stripping fix in get_cache_key() allows
 * BerlinDB's query-result cache to work correctly across Query instances.
 *
 * @package     EDD\Tests\Database
 * @copyright   Copyright (c) 2026, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.6.8
 */

namespace EDD\Tests\Database;

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
		if (PHP_VERSION_ID < 80100) {
            $get_key->setAccessible( true );
        }

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
}
