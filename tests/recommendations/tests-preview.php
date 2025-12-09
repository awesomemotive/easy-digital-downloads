<?php
/**
 * Tests for Recommendations Preview
 *
 * @group edd_recommendations
 * @group edd_pro
 */
namespace EDD\Tests\Recommendations;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;
use EDD\Pro\Recommendations\Preview as RecommendationsPreview;

class Preview extends EDD_UnitTestCase {

	/**
	 * Preview instance.
	 *
	 * @var Preview
	 */
	protected $preview;

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
			$this->markTestSkipped( 'EDD Pro is not available. Recommendations Preview tests require EDD Pro.' );
		}

		parent::setUp();

		$this->preview = new RecommendationsPreview();

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

			update_post_meta( $download_id, 'edd_price', '29.99' );

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
	 * Test Preview can be instantiated.
	 */
	public function test_preview_initialization() {
		$this->assertInstanceOf( RecommendationsPreview::class, $this->preview );
	}

	/**
	 * Test get_subscribed_events returns correct events.
	 */
	public function test_get_subscribed_events() {
		$events = RecommendationsPreview::get_subscribed_events();

		$this->assertIsArray( $events );
		$this->assertArrayHasKey( 'edd_cart_preview_data', $events );
		$this->assertArrayHasKey( 'edd_cart_preview_assets_enqueued', $events );
		$this->assertArrayHasKey( 'edd_cart_preview_after_items', $events );
		$this->assertArrayHasKey( 'edd_cart_preview_templates', $events );
	}

	/**
	 * Test add_recommendations returns unchanged data when recommendations disabled.
	 */
	public function test_add_recommendations_returns_unchanged_when_disabled() {
		edd_update_option( 'cart_recommendations', false );

		$cart_data = array(
			'items' => array(
				array(
					'id'    => $this->downloads[0],
					'name'  => 'Test Product',
					'price' => 29.99,
				),
			),
		);

		$result = $this->preview->add_recommendations( $cart_data );

		$this->assertEquals( $cart_data, $result );
		$this->assertArrayNotHasKey( 'recommendations', $result );
	}

	/**
	 * Test add_recommendations returns unchanged data when cart is empty.
	 */
	public function test_add_recommendations_returns_unchanged_when_cart_empty() {
		$cart_data = array(
			'items' => array(),
		);

		$result = $this->preview->add_recommendations( $cart_data );

		$this->assertEquals( $cart_data, $result );
		$this->assertArrayNotHasKey( 'recommendations', $result );
	}

	/**
	 * Test add_recommendations adds recommendations to cart data.
	 */
	public function test_add_recommendations_adds_recommendations_to_cart_data() {
		// Mock cached recommendations in post meta.
		update_post_meta(
			$this->downloads[0],
			'_edd_cached_recommendations',
			array(
				array(
					'product_id' => $this->downloads[1],
					'score'      => 0.95,
				),
				array(
					'product_id' => $this->downloads[2],
					'score'      => 0.85,
				),
			)
		);

		$cart_data = array(
			'items' => array(
				array(
					'id'    => $this->downloads[0],
					'name'  => 'Test Product',
					'price' => 29.99,
				),
			),
		);

		$result = $this->preview->add_recommendations( $cart_data );

		$this->assertArrayHasKey( 'recommendations', $result );
		$this->assertIsArray( $result['recommendations'] );
		$this->assertCount( 2, $result['recommendations'] );

		delete_post_meta( $this->downloads[0], '_edd_cached_recommendations' );
	}

	/**
	 * Test add_recommendations limits to 3 recommendations.
	 */
	public function test_add_recommendations_limits_to_three() {
		// Mock cached recommendations with more than 3 items.
		$cached_recommendations = array();
		for ( $i = 0; $i < 5; $i++ ) {
			$cached_recommendations[] = array(
				'product_id' => $this->downloads[ $i ],
				'score'      => 1.0 - ( $i * 0.1 ),
			);
		}

		update_post_meta( $this->downloads[0], '_edd_cached_recommendations', $cached_recommendations );

		$cart_data = array(
			'items' => array(
				array(
					'id'    => $this->downloads[0],
					'name'  => 'Test Product',
					'price' => 29.99,
				),
			),
		);

		$result = $this->preview->add_recommendations( $cart_data );

		$this->assertArrayHasKey( 'recommendations', $result );
		$this->assertCount( 3, $result['recommendations'] );

		delete_post_meta( $this->downloads[0], '_edd_cached_recommendations' );
	}

	/**
	 * Test format_recommendation returns false for invalid product.
	 */
	public function test_format_recommendation_returns_false_for_invalid_product() {
		$reflection = new \ReflectionClass( $this->preview );
		$method     = $reflection->getMethod( 'format_recommendation' );
		$method->setAccessible( true );

		$result = $method->invoke(
			$this->preview,
			array(
				'product_id' => 999999,
				'score'      => 0.9,
				'price'      => 29.99,
			)
		);

		$this->assertFalse( $result );
	}

	/**
	 * Test format_recommendation returns false for non-purchasable download.
	 */
	public function test_format_recommendation_returns_false_for_non_purchasable() {
		$draft_download = self::factory()->post->create(
			array(
				'post_type'   => 'download',
				'post_status' => 'draft',
			)
		);

		$reflection = new \ReflectionClass( $this->preview );
		$method     = $reflection->getMethod( 'format_recommendation' );
		$method->setAccessible( true );

		$result = $method->invoke(
			$this->preview,
			array(
				'product_id' => $draft_download,
				'score'      => 0.9,
				'price'      => 29.99,
			)
		);

		$this->assertFalse( $result );
	}

	/**
	 * Test format_recommendation returns correct structure.
	 */
	public function test_format_recommendation_returns_correct_structure() {
		$reflection = new \ReflectionClass( $this->preview );
		$method     = $reflection->getMethod( 'format_recommendation' );
		$method->setAccessible( true );

		$result = $method->invoke(
			$this->preview,
			array(
				'product_id' => $this->downloads[0],
				'score'      => 0.9,
				'price'      => 29.99,
			)
		);

		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'product_id', $result );
		$this->assertArrayHasKey( 'url', $result );
		$this->assertArrayHasKey( 'title', $result );
		$this->assertArrayHasKey( 'price', $result );
		$this->assertArrayHasKey( 'thumbnail', $result );

		$this->assertEquals( $this->downloads[0], $result['product_id'] );
	}

	/**
	 * Test format_recommendation includes thumbnail when available.
	 */
	public function test_format_recommendation_includes_thumbnail_when_available() {
		// Create and attach a thumbnail.
		$attachment_id = $this->factory->attachment->create_upload_object( DIR_TESTDATA . '/images/canola.jpg' );
		set_post_thumbnail( $this->downloads[0], $attachment_id );

		$reflection = new \ReflectionClass( $this->preview );
		$method     = $reflection->getMethod( 'format_recommendation' );
		$method->setAccessible( true );

		$result = $method->invoke(
			$this->preview,
			array(
				'product_id' => $this->downloads[0],
				'score'      => 0.9,
				'price'      => 29.99,
			)
		);

		$this->assertNotNull( $result['thumbnail'] );
		$this->assertIsString( $result['thumbnail'] );
	}

	/**
	 * Test format_recommendation returns null thumbnail when not available.
	 */
	public function test_format_recommendation_returns_null_thumbnail_when_not_available() {
		$reflection = new \ReflectionClass( $this->preview );
		$method     = $reflection->getMethod( 'format_recommendation' );
		$method->setAccessible( true );

		$result = $method->invoke(
			$this->preview,
			array(
				'product_id' => $this->downloads[0],
				'score'      => 0.9,
				'price'      => 29.99,
			)
		);

		$this->assertNull( $result['thumbnail'] );
	}

	/**
	 * Test enqueue_script enqueues script when recommendations enabled.
	 */
	public function test_enqueue_script_enqueues_when_enabled() {
		$this->preview->enqueue_script();

		$this->assertTrue( wp_script_is( 'edd-recommendations-preview', 'enqueued' ) );
	}

	/**
	 * Test enqueue_script doesn't enqueue when recommendations disabled.
	 */
	public function test_enqueue_script_doesnt_enqueue_when_disabled() {
		// Dequeue any previously enqueued script from other tests.
		wp_dequeue_script( 'edd-recommendations-preview' );

		edd_update_option( 'cart_recommendations', false );

		$this->preview->enqueue_script();

		$this->assertFalse( wp_script_is( 'edd-recommendations-preview', 'enqueued' ) );
	}

	/**
	 * Test render_recommendations_section outputs HTML when enabled.
	 */
	public function test_render_recommendations_section_outputs_html_when_enabled() {
		ob_start();
		$this->preview->render_recommendations_section();
		$output = ob_get_clean();

		$this->assertStringContainsString( 'cart-preview-recommendations', $output );
		$this->assertStringContainsString( 'You might also like:', $output );
	}

	/**
	 * Test render_recommendations_section outputs nothing when disabled.
	 */
	public function test_render_recommendations_section_outputs_nothing_when_disabled() {
		edd_update_option( 'cart_recommendations', false );

		ob_start();
		$this->preview->render_recommendations_section();
		$output = ob_get_clean();

		$this->assertEmpty( $output );
	}

	/**
	 * Test render_recommendation_template outputs template when enabled.
	 */
	public function test_render_recommendation_template_outputs_template_when_enabled() {
		ob_start();
		$this->preview->render_recommendation_template();
		$output = ob_get_clean();

		$this->assertStringContainsString( 'edd-cart-preview-recommendation-template', $output );
		$this->assertStringContainsString( '<template', $output );
		$this->assertStringContainsString( 'Add to Cart', $output );
	}

	/**
	 * Test render_recommendation_template outputs nothing when disabled.
	 */
	public function test_render_recommendation_template_outputs_nothing_when_disabled() {
		edd_update_option( 'cart_recommendations', false );

		ob_start();
		$this->preview->render_recommendation_template();
		$output = ob_get_clean();

		$this->assertEmpty( $output );
	}

	/**
	 * Test Preview implements SubscriberInterface.
	 */
	public function test_preview_implements_subscriber_interface() {
		$this->assertInstanceOf( \EDD\EventManagement\SubscriberInterface::class, $this->preview );
	}

	/**
	 * Test add_recommendations filters out invalid recommendations.
	 */
	public function test_add_recommendations_filters_out_invalid_recommendations() {
		// Mock cached recommendations with invalid product.
		update_post_meta(
			$this->downloads[0],
			'_edd_cached_recommendations',
			array(
				array(
					'product_id' => $this->downloads[1],
					'score'      => 0.95,
				),
				array(
					'product_id' => 999999, // Invalid
					'score'      => 0.85,
				),
			)
		);

		$cart_data = array(
			'items' => array(
				array(
					'id'    => $this->downloads[0],
					'name'  => 'Test Product',
					'price' => 29.99,
				),
			),
		);

		$result = $this->preview->add_recommendations( $cart_data );

		$this->assertArrayHasKey( 'recommendations', $result );
		$this->assertCount( 1, $result['recommendations'] ); // Only 1 valid
		$this->assertEquals( $this->downloads[1], $result['recommendations'][0]['product_id'] );

		delete_post_meta( $this->downloads[0], '_edd_cached_recommendations' );
	}

	/**
	 * Test format_recommendation handles variable pricing with default price.
	 */
	public function test_format_recommendation_handles_variable_pricing() {
		// Create a download with variable pricing.
		$download_id = self::factory()->post->create(
			array(
				'post_type'   => 'download',
				'post_title'  => 'Variable Product',
				'post_status' => 'publish',
			)
		);

		$variable_prices = array(
			array(
				'index'  => 1,
				'name'   => 'Basic',
				'amount' => 19.99,
			),
			array(
				'index'  => 2,
				'name'   => 'Pro',
				'amount' => 49.99,
			),
		);

		update_post_meta( $download_id, '_variable_pricing', 1 );
		update_post_meta( $download_id, 'edd_variable_prices', $variable_prices );
		update_post_meta( $download_id, '_edd_default_price_id', 0 ); // price_id 0 = Basic (first in array).

		$reflection = new \ReflectionClass( $this->preview );
		$method     = $reflection->getMethod( 'format_recommendation' );
		$method->setAccessible( true );

		$result = $method->invoke(
			$this->preview,
			array(
				'product_id' => $download_id,
				'score'      => 0.9,
			)
		);

		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'price', $result );
		// Should use the default price (Basic at 19.99).
		$this->assertStringContainsString( '19.99', $result['price'] );
		// Title should include variant name.
		$this->assertStringContainsString( 'Basic', $result['title'] );
	}

	/**
	 * Test format_recommendation uses non-variable pricing when not variable.
	 */
	public function test_format_recommendation_uses_non_variable_pricing() {
		$reflection = new \ReflectionClass( $this->preview );
		$method     = $reflection->getMethod( 'format_recommendation' );
		$method->setAccessible( true );

		$result = $method->invoke(
			$this->preview,
			array(
				'product_id' => $this->downloads[0],
				'score'      => 0.9,
			)
		);

		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'price', $result );
		// Should use the regular price (29.99 from setUp).
		$this->assertStringContainsString( '29.99', $result['price'] );
	}

	/**
	 * Test format_recommendation returns false for empty product_id.
	 */
	public function test_format_recommendation_returns_false_for_empty_product_id() {
		$reflection = new \ReflectionClass( $this->preview );
		$method     = $reflection->getMethod( 'format_recommendation' );
		$method->setAccessible( true );

		$result = $method->invoke(
			$this->preview,
			array(
				'product_id' => '',
				'score'      => 0.9,
			)
		);

		$this->assertFalse( $result );
	}

	/**
	 * Test format_recommendation returns false for zero product_id.
	 */
	public function test_format_recommendation_returns_false_for_zero_product_id() {
		$reflection = new \ReflectionClass( $this->preview );
		$method     = $reflection->getMethod( 'format_recommendation' );
		$method->setAccessible( true );

		$result = $method->invoke(
			$this->preview,
			array(
				'product_id' => 0,
				'score'      => 0.9,
			)
		);

		$this->assertFalse( $result );
	}

	/**
	 * Test get_thumbnail_url returns null when no thumbnail.
	 */
	public function test_get_thumbnail_url_returns_null_when_no_thumbnail() {
		$reflection = new \ReflectionClass( $this->preview );
		$method     = $reflection->getMethod( 'get_thumbnail_url' );
		$method->setAccessible( true );

		$result = $method->invoke( $this->preview, $this->downloads[0] );

		$this->assertNull( $result );
	}

	/**
	 * Test add_recommendations returns empty recommendations array when Query returns empty.
	 */
	public function test_add_recommendations_returns_empty_when_no_recommendations() {
		// Clear any cached recommendations.
		delete_post_meta( $this->downloads[0], '_edd_cached_recommendations' );

		// Mock API to return empty recommendations.
		add_filter(
			'pre_http_request',
			function( $preempt, $parsed_args, $url ) {
				if ( strpos( $url, 'recommendations/query' ) !== false ) {
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

		$cart_data = array(
			'items' => array(
				array(
					'id'    => $this->downloads[0],
					'name'  => 'Test Product',
					'price' => 29.99,
				),
			),
		);

		$result = $this->preview->add_recommendations( $cart_data );

		// Should return original cart data without recommendations key.
		$this->assertArrayNotHasKey( 'recommendations', $result );

		remove_all_filters( 'pre_http_request' );
	}
}
