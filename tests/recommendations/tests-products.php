<?php
/**
 * Tests for Recommendations Products
 *
 * @group edd_recommendations
 * @group edd_pro
 */
namespace EDD\Tests\Recommendations;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;
use EDD\Pro\Recommendations\Products as RecommendationsProducts;

class Products extends EDD_UnitTestCase {

	/**
	 * Set up before each test.
	 */
	public function setUp(): void {
		if ( ! edd_is_pro() ) {
			$this->markTestSkipped( 'EDD Pro is not available. Recommendations Products tests require EDD Pro.' );
		}

		parent::setUp();
	}

	/**
	 * Test format returns false for invalid download ID.
	 */
	public function test_format_returns_false_for_invalid_download() {
		$result = RecommendationsProducts::format( 999999 );

		$this->assertFalse( $result );
	}

	/**
	 * Test format returns false for non-purchasable download.
	 */
	public function test_format_returns_false_for_non_purchasable_download() {
		$download_id = self::factory()->post->create(
			array(
				'post_type'   => 'download',
				'post_status' => 'draft',
			)
		);

		$result = RecommendationsProducts::format( $download_id );

		$this->assertFalse( $result );
	}

	/**
	 * Test format returns correct structure for valid download.
	 */
	public function test_format_returns_correct_structure_for_valid_download() {
		$download_id = self::factory()->post->create(
			array(
				'post_type'    => 'download',
				'post_title'   => 'Test Product',
				'post_content' => 'This is a test product description.',
				'post_status'  => 'publish',
			)
		);

		update_post_meta( $download_id, 'edd_price', '29.99' );

		$result = RecommendationsProducts::format( $download_id );

		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'id', $result );
		$this->assertArrayHasKey( 'title', $result );
		$this->assertArrayHasKey( 'description', $result );
		$this->assertArrayHasKey( 'category', $result );
		$this->assertArrayHasKey( 'price', $result );

		$this->assertEquals( (string) $download_id, $result['id'] );
		$this->assertEquals( 'Test Product', $result['title'] );
		$this->assertEquals( 29.99, $result['price'] );
	}

	/**
	 * Test format accepts EDD_Download object.
	 */
	public function test_format_accepts_edd_download_object() {
		$download_id = self::factory()->post->create(
			array(
				'post_type'   => 'download',
				'post_status' => 'publish',
			)
		);

		$download = edd_get_download( $download_id );
		$result   = RecommendationsProducts::format( $download );

		$this->assertIsArray( $result );
		$this->assertEquals( (string) $download_id, $result['id'] );
	}

	/**
	 * Test format includes primary category.
	 */
	public function test_format_includes_primary_category() {
		$category_id = $this->factory->term->create(
			array(
				'taxonomy' => 'download_category',
				'name'     => 'Test Category',
			)
		);

		$download_id = self::factory()->post->create(
			array(
				'post_type'   => 'download',
				'post_status' => 'publish',
			)
		);

		wp_set_object_terms( $download_id, $category_id, 'download_category' );

		$result = RecommendationsProducts::format( $download_id );

		$this->assertEquals( 'Test Category', $result['category'] );
	}

	/**
	 * Test format returns empty category when none assigned.
	 */
	public function test_format_returns_empty_category_when_none_assigned() {
		$download_id = self::factory()->post->create(
			array(
				'post_type'   => 'download',
				'post_status' => 'publish',
			)
		);

		$result = RecommendationsProducts::format( $download_id );

		$this->assertEquals( '', $result['category'] );
	}

	/**
	 * Test format includes custom fields when filtered.
	 */
	public function test_format_includes_custom_fields_when_filtered() {
		$download_id = self::factory()->post->create(
			array(
				'post_type'   => 'download',
				'post_status' => 'publish',
			)
		);

		add_filter(
			'edd_recommendations_custom_fields',
			function( $fields, $id ) use ( $download_id ) {
				if ( $id === $download_id ) {
					$fields['difficulty'] = 'intermediate';
					$fields['file_type']  = 'pdf';
				}

				return $fields;
			},
			10,
			2
		);

		$result = RecommendationsProducts::format( $download_id );

		$this->assertArrayHasKey( 'custom_fields', $result );
		$this->assertEquals( 'intermediate', $result['custom_fields']['difficulty'] );
		$this->assertEquals( 'pdf', $result['custom_fields']['file_type'] );

		remove_all_filters( 'edd_recommendations_custom_fields' );
	}

	/**
	 * Test format doesn't include custom_fields key when empty.
	 */
	public function test_format_excludes_custom_fields_when_empty() {
		$download_id = self::factory()->post->create(
			array(
				'post_type'   => 'download',
				'post_status' => 'publish',
			)
		);

		$result = RecommendationsProducts::format( $download_id );

		$this->assertArrayNotHasKey( 'custom_fields', $result );
	}

	/**
	 * Test format_downloads filters out invalid downloads.
	 */
	public function test_format_downloads_filters_out_invalid_downloads() {
		$valid_download = self::factory()->post->create(
			array(
				'post_type'   => 'download',
				'post_status' => 'publish',
			)
		);
		$invalid_download = self::factory()->post->create(
			array(
				'post_type'   => 'download',
				'post_status' => 'draft',
			)
		);

		$result = RecommendationsProducts::format_downloads( array( $valid_download, $invalid_download, 999999 ) );

		$this->assertCount( 1, $result );
		$this->assertEquals( (string) $valid_download, $result[0]['id'] );
	}

	/**
	 * Test get_all_download_ids returns published downloads.
	 */
	public function test_get_all_download_ids_returns_published_downloads() {
		$download1 = self::factory()->post->create(
			array(
				'post_type'   => 'download',
				'post_status' => 'publish',
			)
		);
		$download2 = self::factory()->post->create(
			array(
				'post_type'   => 'download',
				'post_status' => 'publish',
			)
		);
		$draft = self::factory()->post->create(
			array(
				'post_type'   => 'download',
				'post_status' => 'draft',
			)
		);

		$result = RecommendationsProducts::get_all_download_ids();

		$this->assertIsArray( $result );
		$this->assertContains( $download1, $result );
		$this->assertContains( $download2, $result );
		$this->assertNotContains( $draft, $result );
	}

	/**
	 * Test get_all_download_ids can be filtered.
	 */
	public function test_get_all_download_ids_can_be_filtered() {
		$download1 = self::factory()->post->create(
			array(
				'post_type'   => 'download',
				'post_status' => 'publish',
			)
		);
		$download2 = self::factory()->post->create(
			array(
				'post_type'   => 'download',
				'post_status' => 'publish',
			)
		);

		add_filter(
			'edd_recommendations_sync_query_args',
			function( $args ) use ( $download1 ) {
				$args['post__in'] = array( $download1 );

				return $args;
			}
		);

		$result = RecommendationsProducts::get_all_download_ids();

		$this->assertCount( 1, $result );
		$this->assertEquals( $download1, $result[0] );

		remove_all_filters( 'edd_recommendations_sync_query_args' );
	}

	/**
	 * Test clean_description strips HTML tags.
	 */
	public function test_clean_description_strips_html_tags() {
		$download_id = self::factory()->post->create(
			array(
				'post_type'    => 'download',
				'post_content' => '<p>This is <strong>bold</strong> text.</p>',
				'post_status'  => 'publish',
			)
		);

		$result = RecommendationsProducts::format( $download_id );

		$this->assertStringNotContainsString( '<p>', $result['description'] );
		$this->assertStringNotContainsString( '<strong>', $result['description'] );
		$this->assertStringContainsString( 'This is bold text.', $result['description'] );
	}

	/**
	 * Test clean_description strips shortcodes.
	 */
	public function test_clean_description_strips_shortcodes() {
		// Register a test shortcode to ensure stripping works.
		add_shortcode( 'testshortcode', '__return_empty_string' );

		$download_id = self::factory()->post->create(
			array(
				'post_type'    => 'download',
				'post_content' => 'This is text with [testshortcode] included.',
				'post_status'  => 'publish',
			)
		);

		$result = RecommendationsProducts::format( $download_id );

		$this->assertStringNotContainsString( '[testshortcode]', $result['description'] );

		remove_shortcode( 'testshortcode' );
	}

	/**
	 * Test clean_description truncates to 500 characters.
	 */
	public function test_clean_description_truncates_to_500_chars() {
		$long_content = str_repeat( 'This is a long description. ', 50 ); // Over 500 chars

		$download_id = self::factory()->post->create(
			array(
				'post_type'    => 'download',
				'post_content' => $long_content,
				'post_status'  => 'publish',
			)
		);

		$result = RecommendationsProducts::format( $download_id );

		$this->assertLessThanOrEqual( 500, mb_strlen( $result['description'] ) );
	}

	/**
	 * Test get_display_price returns single price.
	 */
	public function test_get_display_price_returns_single_price() {
		$download_id = self::factory()->post->create(
			array(
				'post_type'   => 'download',
				'post_status' => 'publish',
			)
		);

		update_post_meta( $download_id, 'edd_price', '49.99' );

		$result = RecommendationsProducts::format( $download_id );

		$this->assertEquals( 49.99, $result['price'] );
	}

	/**
	 * Test get_display_price returns lowest variable price.
	 */
	public function test_get_display_price_returns_lowest_variable_price() {
		$download_id = self::factory()->post->create(
			array(
				'post_type'   => 'download',
				'post_status' => 'publish',
			)
		);

		$variable_prices = array(
			array(
				'index'  => 1,
				'name'   => 'Basic',
				'amount' => 29.99,
			),
			array(
				'index'  => 2,
				'name'   => 'Pro',
				'amount' => 49.99,
			),
			array(
				'index'  => 3,
				'name'   => 'Standard',
				'amount' => 19.99,
			),
		);

		update_post_meta( $download_id, '_variable_pricing', 1 );
		update_post_meta( $download_id, 'edd_variable_prices', $variable_prices );

		$result = RecommendationsProducts::format( $download_id );

		$this->assertEquals( 19.99, $result['price'] );
	}

	/**
	 * Test get_display_price returns 0.00 for variable pricing with no prices.
	 */
	public function test_get_display_price_returns_zero_for_variable_pricing_with_no_prices() {
		$download_id = self::factory()->post->create(
			array(
				'post_type'   => 'download',
				'post_status' => 'publish',
			)
		);

		update_post_meta( $download_id, '_variable_pricing', 1 );
		update_post_meta( $download_id, 'edd_variable_prices', array() );

		$result = RecommendationsProducts::format( $download_id );

		$this->assertEquals( 0.00, $result['price'] );
	}

	/**
	 * Test format returns float for price.
	 */
	public function test_format_returns_float_for_price() {
		$download_id = self::factory()->post->create(
			array(
				'post_type'   => 'download',
				'post_status' => 'publish',
			)
		);

		update_post_meta( $download_id, 'edd_price', '29.99' );

		$result = RecommendationsProducts::format( $download_id );

		$this->assertIsFloat( $result['price'] );
	}
}
