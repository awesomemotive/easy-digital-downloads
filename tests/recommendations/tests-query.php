<?php
/**
 * Tests for Recommendations Query
 *
 * @group edd_recommendations
 * @group edd_pro
 */
namespace EDD\Tests\Recommendations;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;
use EDD\Pro\Recommendations\Query as RecommendationsQuery;
use EDD\Pro\Recommendations\API;

class Query extends EDD_UnitTestCase {

	/**
	 * Query instance.
	 *
	 * @var Query
	 */
	protected $query;

	/**
	 * Test download IDs.
	 *
	 * @var array
	 */
	protected $downloads = array();

	/**
	 * Set up before each test.
	 */
	public function setUp(): void {
		if ( ! edd_is_pro() ) {
			$this->markTestSkipped( 'EDD Pro is not available. Recommendations Query tests require EDD Pro.' );
		}

		parent::setUp();

		$this->query = new RecommendationsQuery();

		// Set up necessary options.
		edd_update_option( 'cart_recommendations', true );
		update_site_option( 'edd_pro_license_key', 'test_license_key' );

		// Create test downloads.
		for ( $i = 1; $i <= 5; $i++ ) {
			$download_id = self::factory()->post->create(
				array(
					'post_type'   => 'download',
					'post_title'  => "Test Product $i",
					'post_status' => 'publish',
				)
			);
			$this->downloads[] = $download_id;
		}
	}

	/**
	 * Clean up after each test.
	 */
	public function tearDown(): void {
		edd_delete_option( 'cart_recommendations' );
		delete_site_option( 'edd_pro_license_key' );

		parent::tearDown();
	}

	/**
	 * Test Query can be instantiated.
	 */
	public function test_query_initialization() {
		$this->assertInstanceOf( RecommendationsQuery::class, $this->query );
	}

	/**
	 * Test get_cart_recommendations returns empty array when API returns error.
	 */
	public function test_get_cart_recommendations_returns_empty_on_api_error() {
		delete_site_option( 'edd_pro_license_key' );

		$result = $this->query->get_cart_recommendations( array( $this->downloads[0] ) );

		$this->assertIsArray( $result );
		$this->assertEmpty( $result );
	}

	/**
	 * Test get_cart_recommendations returns recommendations from API.
	 */
	public function test_get_cart_recommendations_returns_recommendations_from_api() {
		add_filter(
			'pre_http_request',
			function( $preempt, $parsed_args, $url ) {
				if ( strpos( $url, 'recommendations/query' ) !== false ) {
					return array(
						'response' => array(
							'code'    => 200,
							'message' => 'OK',
						),
						'body'     => json_encode(
							array(
								'recommendations' => array(
									(string) $this->downloads[0] => array(
										array(
											'product_id' => (string) $this->downloads[1],
											'score'      => 0.95,
										),
										array(
											'product_id' => (string) $this->downloads[2],
											'score'      => 0.85,
										),
									),
								),
							)
						),
					);
				}

				return $preempt;
			},
			10,
			3
		);

		$result = $this->query->get_cart_recommendations(
			array( $this->downloads[0] ),
			array( 'use_cache' => false )
		);

		$this->assertCount( 2, $result );
		$this->assertEquals( (string) $this->downloads[1], $result[0]['product_id'] );
		$this->assertEquals( 0.95, $result[0]['score'] );

		remove_all_filters( 'pre_http_request' );
	}

	/**
	 * Test get_cart_recommendations uses cache when enabled.
	 */
	public function test_get_cart_recommendations_uses_cache() {
		$call_count = 0;

		add_filter(
			'pre_http_request',
			function( $preempt, $parsed_args, $url ) use ( &$call_count ) {
				if ( strpos( $url, 'recommendations/query' ) !== false ) {
					$call_count++;

					return array(
						'response' => array(
							'code'    => 200,
							'message' => 'OK',
						),
						'body'     => json_encode(
							array(
								'recommendations' => array(
									(string) $this->downloads[0] => array(
										array(
											'product_id' => (string) $this->downloads[1],
											'score'      => 0.95,
										),
									),
								),
							)
						),
					);
				}

				return $preempt;
			},
			10,
			3
		);

		// First call.
		$result1 = $this->query->get_cart_recommendations(
			array( $this->downloads[0] ),
			array( 'use_cache' => true )
		);

		// Second call should use cache.
		$result2 = $this->query->get_cart_recommendations(
			array( $this->downloads[0] ),
			array( 'use_cache' => true )
		);

		$this->assertEquals( 1, $call_count, 'API should only be called once when cache is enabled' );
		$this->assertEquals( $result1, $result2 );

		remove_all_filters( 'pre_http_request' );
	}

	/**
	 * Test get_cart_recommendations bypasses cache when disabled.
	 */
	public function test_get_cart_recommendations_bypasses_cache_when_disabled() {
		$call_count = 0;

		add_filter(
			'pre_http_request',
			function( $preempt, $parsed_args, $url ) use ( &$call_count ) {
				if ( strpos( $url, 'recommendations/query' ) !== false ) {
					$call_count++;

					return array(
						'response' => array(
							'code'    => 200,
							'message' => 'OK',
						),
						'body'     => json_encode(
							array(
								'recommendations' => array(
									(string) $this->downloads[0] => array(
										array(
											'product_id' => (string) $this->downloads[1],
											'score'      => 0.95,
										),
									),
								),
							)
						),
					);
				}

				return $preempt;
			},
			10,
			3
		);

		// Two calls with cache disabled.
		$this->query->get_cart_recommendations(
			array( $this->downloads[0] ),
			array( 'use_cache' => false )
		);
		$this->query->get_cart_recommendations(
			array( $this->downloads[0] ),
			array( 'use_cache' => false )
		);

		$this->assertEquals( 2, $call_count, 'API should be called twice when cache is disabled' );

		remove_all_filters( 'pre_http_request' );
	}

	/**
	 * Test clear_cache flushes the cache group.
	 */
	public function test_clear_cache_flushes_cache_group() {
		add_filter(
			'pre_http_request',
			function( $preempt, $parsed_args, $url ) {
				if ( strpos( $url, 'recommendations/query' ) !== false ) {
					return array(
						'response' => array(
							'code'    => 200,
							'message' => 'OK',
						),
						'body'     => json_encode(
							array(
								'recommendations' => array(
									(string) $this->downloads[0] => array(
										array(
											'product_id' => (string) $this->downloads[1],
											'score'      => 0.95,
										),
									),
								),
							)
						),
					);
				}

				return $preempt;
			},
			10,
			3
		);

		// Get recommendations to populate cache.
		$this->query->get_cart_recommendations(
			array( $this->downloads[0] ),
			array( 'use_cache' => true )
		);

		// Clear the cache.
		$this->query->clear_cache();

		// Verify cache was cleared by checking if API is called again.
		$call_count = 0;

		add_filter(
			'pre_http_request',
			function( $preempt, $parsed_args, $url ) use ( &$call_count ) {
				if ( strpos( $url, 'recommendations/query' ) !== false ) {
					$call_count++;

					return array(
						'response' => array(
							'code'    => 200,
							'message' => 'OK',
						),
						'body'     => json_encode( array( 'recommendations' => array() ) ),
					);
				}

				return $preempt;
			},
			20,
			3
		);

		$this->query->get_cart_recommendations(
			array( $this->downloads[0] ),
			array( 'use_cache' => true )
		);

		$this->assertEquals( 1, $call_count, 'API should be called again after cache clear' );

		remove_all_filters( 'pre_http_request' );
	}

	/**
	 * Test get_cart_recommendations excludes customer-owned products.
	 */
	public function test_get_cart_recommendations_excludes_owned_products() {
		// Create a customer and order.
		$customer = parent::edd()->customer->create_and_get();
		$user_id  = $this->factory->user->create();

		$customer->update( array( 'user_id' => $user_id ) );

		wp_set_current_user( $user_id );

		// Create an order for this customer.
		$order = parent::edd()->order->create_and_get(
			array(
				'customer_id' => $customer->id,
				'status'      => 'complete',
			)
		);

		// Add order items.
		parent::edd()->order_item->create_and_get(
			array(
				'order_id'   => $order->id,
				'product_id' => $this->downloads[1],
				'status'     => 'complete',
			)
		);

		$captured_body = null;

		add_filter(
			'pre_http_request',
			function( $preempt, $parsed_args, $url ) use ( &$captured_body ) {
				if ( strpos( $url, 'recommendations/query' ) !== false ) {
					$captured_body = $parsed_args['body'];

					return array(
						'response' => array(
							'code'    => 200,
							'message' => 'OK',
						),
						'body'     => json_encode( array( 'recommendations' => array() ) ),
					);
				}

				return $preempt;
			},
			10,
			3
		);

		$this->query->get_cart_recommendations(
			array( $this->downloads[0] ),
			array(
				'exclude_owned' => true,
				'use_cache'     => false,
			)
		);

		$body = json_decode( $captured_body, true );
		$this->assertContains( (string) $this->downloads[1], $body['exclude_ids'] );

		remove_all_filters( 'pre_http_request' );
	}

	/**
	 * Test get_cart_recommendations uses saved recommendations from post meta.
	 */
	public function test_get_cart_recommendations_uses_saved_recommendations() {
		// Save recommendations in post meta.
		$cached_recommendations = array(
			array(
				'product_id' => $this->downloads[2],
				'score'      => 0.9,
				'price'      => 19.99,
			),
			array(
				'product_id' => $this->downloads[3],
				'score'      => 0.8,
				'price'      => 29.99,
			),
		);

		update_post_meta( $this->downloads[0], '_edd_cached_recommendations', $cached_recommendations );

		// This should return cached recommendations without calling API.
		$result = $this->query->get_cart_recommendations(
			array( $this->downloads[0] ),
			array( 'use_cache' => false )
		);

		$this->assertCount( 2, $result );
		$this->assertEquals( $this->downloads[2], $result[0]['product_id'] );
	}

	/**
	 * Test filter_and_score_recommendations removes excluded products.
	 */
	public function test_filter_and_score_recommendations_removes_excluded_products() {
		$all_recommendations = array(
			array(
				'product_id' => $this->downloads[1],
				'score'      => 0.9,
				'price'      => 29.99,
			),
			array(
				'product_id' => $this->downloads[2],
				'score'      => 0.8,
				'price'      => 19.99,
			),
			array(
				'product_id' => $this->downloads[3],
				'score'      => 0.7,
				'price'      => 39.99,
			),
		);

		update_post_meta( $this->downloads[0], '_edd_cached_recommendations', $all_recommendations );

		// Exclude download[1].
		$result = $this->query->get_cart_recommendations(
			array( $this->downloads[0] ),
			array(
				'exclude_ids' => array( $this->downloads[1] ),
				'use_cache'   => false,
			)
		);

		$this->assertCount( 2, $result );

		$product_ids = wp_list_pluck( $result, 'product_id' );
		$this->assertNotContains( $this->downloads[1], $product_ids );
		$this->assertContains( $this->downloads[2], $product_ids );
		$this->assertContains( $this->downloads[3], $product_ids );
	}

	/**
	 * Test filter_and_score_recommendations boosts score for duplicate recommendations.
	 */
	public function test_filter_and_score_recommendations_boosts_duplicate_scores() {
		// Product 2 is recommended by both downloads.
		$recommendations_1 = array(
			array(
				'product_id' => $this->downloads[2],
				'score'      => 0.8,
				'price'      => 29.99,
			),
		);

		$recommendations_2 = array(
			array(
				'product_id' => $this->downloads[2],
				'score'      => 0.7,
				'price'      => 29.99,
			),
			array(
				'product_id' => $this->downloads[3],
				'score'      => 0.9,
				'price'      => 19.99,
			),
		);

		update_post_meta( $this->downloads[0], '_edd_cached_recommendations', $recommendations_1 );
		update_post_meta( $this->downloads[1], '_edd_cached_recommendations', $recommendations_2 );

		$result = $this->query->get_cart_recommendations(
			array( $this->downloads[0], $this->downloads[1] ),
			array( 'use_cache' => false )
		);

		// Product 3 should come first (0.9 score, no boost needed).
		$this->assertEquals( $this->downloads[3], $result[0]['product_id'] );

		// Product 2 should be second and have a boosted score higher than the original max 0.8 (now 0.88 with 10% boost).
		$this->assertEquals( $this->downloads[2], $result[1]['product_id'] );
		$this->assertGreaterThan( 0.8, $result[1]['score'] );
	}

	/**
	 * Test filter_and_score_recommendations limits results.
	 */
	public function test_filter_and_score_recommendations_limits_results() {
		$all_recommendations = array();

		for ( $i = 1; $i <= 10; $i++ ) {
			$all_recommendations[] = array(
				'product_id' => $this->downloads[0] + $i,
				'score'      => 1.0 - ( $i * 0.05 ),
				'price'      => 29.99,
			);
		}

		update_post_meta( $this->downloads[0], '_edd_cached_recommendations', $all_recommendations );

		$result = $this->query->get_cart_recommendations(
			array( $this->downloads[0] ),
			array(
				'limit'     => 3,
				'use_cache' => false,
			)
		);

		$this->assertCount( 3, $result );
	}

	/**
	 * Test filter_and_score_recommendations sorts by score descending.
	 */
	public function test_filter_and_score_recommendations_sorts_by_score() {
		$all_recommendations = array(
			array(
				'product_id' => $this->downloads[1],
				'score'      => 0.5,
				'price'      => 29.99,
			),
			array(
				'product_id' => $this->downloads[2],
				'score'      => 0.9,
				'price'      => 19.99,
			),
			array(
				'product_id' => $this->downloads[3],
				'score'      => 0.7,
				'price'      => 39.99,
			),
		);

		update_post_meta( $this->downloads[0], '_edd_cached_recommendations', $all_recommendations );

		$result = $this->query->get_cart_recommendations(
			array( $this->downloads[0] ),
			array( 'use_cache' => false )
		);

		// Should be sorted by score descending.
		$this->assertEquals( $this->downloads[2], $result[0]['product_id'] ); // 0.9
		$this->assertEquals( $this->downloads[3], $result[1]['product_id'] ); // 0.7
		$this->assertEquals( $this->downloads[1], $result[2]['product_id'] ); // 0.5
	}

	/**
	 * Test get_cache_key generates consistent keys.
	 */
	public function test_get_cache_key_generates_consistent_keys() {
		$reflection = new \ReflectionClass( $this->query );
		$method     = $reflection->getMethod( 'get_cache_key' );
		$method->setAccessible( true );

		$args1 = array(
			'product_ids' => array( 1, 2, 3 ),
			'type'        => 'similar',
			'limit'       => 5,
		);

		$args2 = array(
			'product_ids' => array( 1, 2, 3 ),
			'type'        => 'similar',
			'limit'       => 5,
		);

		$key1 = $method->invoke( $this->query, $args1 );
		$key2 = $method->invoke( $this->query, $args2 );

		$this->assertEquals( $key1, $key2 );
	}

	/**
	 * Test get_cache_key generates different keys for different args.
	 */
	public function test_get_cache_key_generates_different_keys_for_different_args() {
		$reflection = new \ReflectionClass( $this->query );
		$method     = $reflection->getMethod( 'get_cache_key' );
		$method->setAccessible( true );

		$args1 = array(
			'product_ids' => array( 1, 2, 3 ),
			'type'        => 'similar',
			'limit'       => 5,
		);

		$args2 = array(
			'product_ids' => array( 1, 2, 3 ),
			'type'        => 'complementary',
			'limit'       => 5,
		);

		$key1 = $method->invoke( $this->query, $args1 );
		$key2 = $method->invoke( $this->query, $args2 );

		$this->assertNotEquals( $key1, $key2 );
	}

	/**
	 * Test get_cart_recommendations excludes products in cart when requested.
	 */
	public function test_get_cart_recommendations_excludes_products_in_cart() {
		// Add a product to the cart.
		edd_add_to_cart( $this->downloads[1] );

		$captured_body = null;

		add_filter(
			'pre_http_request',
			function( $preempt, $parsed_args, $url ) use ( &$captured_body ) {
				if ( strpos( $url, 'recommendations/query' ) !== false ) {
					$captured_body = $parsed_args['body'];

					return array(
						'response' => array(
							'code'    => 200,
							'message' => 'OK',
						),
						'body'     => json_encode( array( 'recommendations' => array() ) ),
					);
				}

				return $preempt;
			},
			10,
			3
		);

		$this->query->get_cart_recommendations(
			array( $this->downloads[0] ),
			array(
				'exclude_in_cart' => true,
				'use_cache'       => false,
			)
		);

		$body = json_decode( $captured_body, true );
		$this->assertContains( (string) $this->downloads[1], $body['exclude_ids'] );

		// Clean up cart.
		edd_empty_cart();

		remove_all_filters( 'pre_http_request' );
	}

	/**
	 * Test get_hidden_downloads returns hidden downloads.
	 */
	public function test_get_hidden_downloads_returns_hidden_downloads() {
		// Create a hidden download.
		$hidden_download = self::factory()->post->create(
			array(
				'post_type'   => 'download',
				'post_status' => 'publish',
			)
		);
		update_post_meta( $hidden_download, '_edd_hide_download', true );

		// Clear transient to force fresh query.
		delete_transient( 'edd_hd_ids' );

		$reflection = new \ReflectionClass( $this->query );
		$method     = $reflection->getMethod( 'get_hidden_downloads' );
		$method->setAccessible( true );

		$result = $method->invoke( $this->query );

		$this->assertIsArray( $result );
		$this->assertContains( $hidden_download, $result );
	}

	/**
	 * Test get_hidden_downloads uses cached transient.
	 */
	public function test_get_hidden_downloads_uses_cached_transient() {
		// Set a fake transient value.
		$fake_hidden_ids = array( 999, 888, 777 );
		set_transient( 'edd_hd_ids', $fake_hidden_ids );

		$reflection = new \ReflectionClass( $this->query );
		$method     = $reflection->getMethod( 'get_hidden_downloads' );
		$method->setAccessible( true );

		$result = $method->invoke( $this->query );

		$this->assertEquals( $fake_hidden_ids, $result );

		// Clean up.
		delete_transient( 'edd_hd_ids' );
	}

	/**
	 * Test get_cart_recommendations excludes hidden downloads.
	 */
	public function test_get_cart_recommendations_excludes_hidden_downloads() {
		// Create a hidden download.
		$hidden_download = self::factory()->post->create(
			array(
				'post_type'   => 'download',
				'post_status' => 'publish',
			)
		);
		update_post_meta( $hidden_download, '_edd_hide_download', true );

		// Clear transient.
		delete_transient( 'edd_hd_ids' );

		$captured_body = null;

		add_filter(
			'pre_http_request',
			function( $preempt, $parsed_args, $url ) use ( &$captured_body ) {
				if ( strpos( $url, 'recommendations/query' ) !== false ) {
					$captured_body = $parsed_args['body'];

					return array(
						'response' => array(
							'code'    => 200,
							'message' => 'OK',
						),
						'body'     => json_encode( array( 'recommendations' => array() ) ),
					);
				}

				return $preempt;
			},
			10,
			3
		);

		$this->query->get_cart_recommendations(
			array( $this->downloads[0] ),
			array( 'use_cache' => false )
		);

		$body = json_decode( $captured_body, true );
		$this->assertContains( (string) $hidden_download, $body['exclude_ids'] );

		remove_all_filters( 'pre_http_request' );
	}

	/**
	 * Test get_cart_contents returns cart contents.
	 */
	public function test_get_cart_contents_returns_cart_contents() {
		// Add a product to the cart.
		edd_add_to_cart( $this->downloads[0] );

		$reflection = new \ReflectionClass( $this->query );
		$method     = $reflection->getMethod( 'get_cart_contents' );
		$method->setAccessible( true );

		$result = $method->invoke( $this->query );

		$this->assertIsArray( $result );
		$this->assertNotEmpty( $result );
		$this->assertEquals( $this->downloads[0], $result[0]['id'] );

		// Clean up cart.
		edd_empty_cart();
	}

	/**
	 * Test get_cart_contents returns empty array when cart is empty.
	 */
	public function test_get_cart_contents_returns_empty_array_when_cart_empty() {
		// Ensure cart is empty.
		edd_empty_cart();

		// Create a new Query instance to avoid cached cart contents.
		$query = new RecommendationsQuery();

		$reflection = new \ReflectionClass( $query );
		$method     = $reflection->getMethod( 'get_cart_contents' );
		$method->setAccessible( true );

		$result = $method->invoke( $query );

		// edd_get_cart_contents returns false when empty, but we handle that.
		$this->assertEmpty( $result );
	}

	/**
	 * Test get_cart_recommendations uses default product IDs from cart when none provided.
	 */
	public function test_get_cart_recommendations_uses_cart_product_ids_when_none_provided() {
		// Add a product to the cart.
		edd_add_to_cart( $this->downloads[0] );

		// Mock cached recommendations.
		update_post_meta(
			$this->downloads[0],
			'_edd_cached_recommendations',
			array(
				array(
					'product_id' => $this->downloads[1],
					'score'      => 0.9,
				),
			)
		);

		// Create new query to avoid stale cart contents.
		$query = new RecommendationsQuery();

		$result = $query->get_cart_recommendations(
			array(), // Empty array should pull from cart.
			array( 'use_cache' => false )
		);

		// Should get recommendations based on cart contents.
		$this->assertNotEmpty( $result );

		// Clean up.
		edd_empty_cart();
		delete_post_meta( $this->downloads[0], '_edd_cached_recommendations' );
	}

	/**
	 * Test filter_and_score_recommendations skips recommendations with empty product_id.
	 */
	public function test_filter_and_score_recommendations_skips_empty_product_id() {
		$all_recommendations = array(
			array(
				'product_id' => $this->downloads[1],
				'score'      => 0.9,
			),
			array(
				'product_id' => '', // Empty
				'score'      => 0.8,
			),
			array(
				// Missing product_id.
				'score' => 0.7,
			),
		);

		update_post_meta( $this->downloads[0], '_edd_cached_recommendations', $all_recommendations );

		$result = $this->query->get_cart_recommendations(
			array( $this->downloads[0] ),
			array( 'use_cache' => false )
		);

		// Should only have 1 valid recommendation.
		$this->assertCount( 1, $result );
		$this->assertEquals( $this->downloads[1], $result[0]['product_id'] );

		delete_post_meta( $this->downloads[0], '_edd_cached_recommendations' );
	}

	/**
	 * Test filter_and_score_recommendations handles string product IDs.
	 */
	public function test_filter_and_score_recommendations_handles_string_product_ids() {
		$all_recommendations = array(
			array(
				'product_id' => (string) $this->downloads[1], // String ID.
				'score'      => 0.9,
			),
		);

		update_post_meta( $this->downloads[0], '_edd_cached_recommendations', $all_recommendations );

		$result = $this->query->get_cart_recommendations(
			array( $this->downloads[0] ),
			array( 'use_cache' => false )
		);

		$this->assertCount( 1, $result );
		// Product ID should be converted to integer.
		$this->assertIsInt( $result[0]['product_id'] );
		$this->assertEquals( $this->downloads[1], $result[0]['product_id'] );

		delete_post_meta( $this->downloads[0], '_edd_cached_recommendations' );
	}
}
