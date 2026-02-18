<?php

namespace EDD\Tests\Downloads;

use EDD\Tests\Helpers\EDD_Helper_Download;
use EDD\Tests\PHPUnit\EDD_UnitTestCase;

class Search extends EDD_UnitTestCase {

	public static function setUpBeforeClass(): void {
		parent::setUpBeforeClass();

		self::factory()->post->create_many(
			5,
			array(
				'post_type' => 'download',
			)
		);
	}

	public static function tearDownAfterClass(): void {
		EDD_Helper_Download::delete_all_downloads();

		parent::tearDownAfterClass();
	}

	public function tearDown(): void {
		parent::tearDown();
		unset( $_GET['s'] );
		unset( $_GET['variations'] );
		unset( $_GET['variations_only'] );
		unset( $_GET['exclusions'] );
	}

	public function test_search_empty_string() {
		$_GET['s'] = 'test';
		$search    = new \EDD\Downloads\Search();
		$results   = $search->search();

		$this->assertEmpty( $results );
	}

	public function test_search() {
		$_GET['s'] = 'Post title';
		$search    = new \EDD\Downloads\Search();
		$results   = $search->search();

		$this->assertCount( 5, $results );
	}

	/**
	 * Search for a specific title.
	 *
	 * @return void
	 */
	public function test_search_specific_title() {
		self::factory()->post->create(
			array(
				'post_type'  => 'download',
				'post_title' => 'Post title Specific',
			)
		);
		self::factory()->post->create(
			array(
				'post_type'  => 'download',
				'post_title' => 'Post title Again Specific',
			)
		);

		$_GET['s'] = '"Post title Specific"';
		$search    = new \EDD\Downloads\Search();
		$results   = $search->search();

		$this->assertCount( 1, $results );
	}

	/**
	 * Search for a fuzzy title.
	 *
	 * @return void
	 */
	public function test_search_fuzzy_title() {
		self::factory()->post->create(
			array(
				'post_type'  => 'download',
				'post_title' => 'Post title Fuzzy',
			)
		);
		self::factory()->post->create(
			array(
				'post_type'  => 'download',
				'post_title' => 'Post title Again Fuzzy',
			)
		);

		$_GET['s'] = 'Post title Fuzzy';
		$search    = new \EDD\Downloads\Search();
		$results   = $search->search();

		$this->assertCount( 2, $results );
	}

	/**
	 * Test that variable products are returned when searching with variations_only parameter.
	 *
	 * This test ensures that when using variations_only=true, the search correctly
	 * returns individual price variations for variable products.
	 *
	 * @return void
	 */
	public function test_search_returns_variable_products_with_variations_only() {
		// Create a variable product with price options.
		$download_id = self::factory()->post->create(
			array(
				'post_type'  => 'download',
				'post_title' => 'Variable Product Test',
			)
		);

		// Add variable pricing to the download.
		$variable_prices = array(
			array(
				'name'   => 'Basic',
				'amount' => 10,
			),
			array(
				'name'   => 'Premium',
				'amount' => 20,
			),
			array(
				'name'   => 'Ultimate',
				'amount' => 30,
			),
		);

		update_post_meta( $download_id, 'edd_price', '0.00' );
		update_post_meta( $download_id, '_variable_pricing', 1 );
		update_post_meta( $download_id, 'edd_variable_prices', $variable_prices );

		// Create another product to exclude.
		$excluded_id = self::factory()->post->create(
			array(
				'post_type'  => 'download',
				'post_title' => 'Excluded Product',
			)
		);

		// Set up search parameters as would be passed from ProductSelect.
		$_GET['s']               = 'Variable Product';
		$_GET['variations']      = true;
		$_GET['variations_only'] = true;
		$_GET['exclusions']      = $excluded_id;

		$search  = new \EDD\Downloads\Search();
		$results = $search->search();

		// Should return 3 variations (not the parent product).
		$this->assertCount( 3, $results );

		// Verify the variation IDs are formatted correctly.
		$result_ids = wp_list_pluck( $results, 'id' );
		$this->assertContains( $download_id . '_0', $result_ids );
		$this->assertContains( $download_id . '_1', $result_ids );
		$this->assertContains( $download_id . '_2', $result_ids );

		// Verify the variation names include the price option names.
		$result_names = wp_list_pluck( $results, 'name' );
		$this->assertStringContainsString( 'Variable Product Test: Basic', $result_names[0] );
		$this->assertStringContainsString( 'Variable Product Test: Premium', $result_names[1] );
		$this->assertStringContainsString( 'Variable Product Test: Ultimate', $result_names[2] );

		// Verify the excluded product is not in the results.
		$this->assertNotContains( $excluded_id, $result_ids );
	}

	/**
	 * Test that variable products include both parent and variations when variations=true but variations_only=false.
	 *
	 * @return void
	 */
	public function test_search_returns_parent_and_variations() {
		// Create a variable product with price options.
		$download_id = self::factory()->post->create(
			array(
				'post_type'  => 'download',
				'post_title' => 'Variable Product Parent Test',
			)
		);

		// Add variable pricing to the download.
		$variable_prices = array(
			array(
				'name'   => 'Standard',
				'amount' => 15,
			),
			array(
				'name'   => 'Pro',
				'amount' => 25,
			),
		);

		update_post_meta( $download_id, 'edd_price', '0.00' );
		update_post_meta( $download_id, '_variable_pricing', 1 );
		update_post_meta( $download_id, 'edd_variable_prices', $variable_prices );

		// Set up search parameters to include both parent and variations.
		$_GET['s']               = 'Variable Product Parent';
		$_GET['variations']      = true;
		$_GET['variations_only'] = false;

		$search  = new \EDD\Downloads\Search();
		$results = $search->search();

		// Should return 3 items: parent + 2 variations.
		$this->assertCount( 3, $results );

		// Verify the parent product is included with "(All Price Options)" text.
		$result_names = wp_list_pluck( $results, 'name' );
		$this->assertStringContainsString( '(All Price Options)', $result_names[0] );

		// Verify the variations are also included.
		$result_ids = wp_list_pluck( $results, 'id' );
		$this->assertContains( $download_id . '_0', $result_ids );
		$this->assertContains( $download_id . '_1', $result_ids );
	}

	/**
	 * Test that variable products are returned when variations=false.
	 *
	 * This test ensures the fix for the bug where variable products were not
	 * being returned when variations parameter was false.
	 *
	 * @return void
	 */
	public function test_search_returns_variable_products_when_variations_false() {
		// Create a variable product with price options.
		$download_id = self::factory()->post->create(
			array(
				'post_type'  => 'download',
				'post_title' => 'Variable Product Variations False',
			)
		);

		// Add variable pricing to the download.
		$variable_prices = array(
			array(
				'name'   => 'Basic',
				'amount' => 10,
			),
			array(
				'name'   => 'Pro',
				'amount' => 20,
			),
		);

		update_post_meta( $download_id, 'edd_price', '0.00' );
		update_post_meta( $download_id, '_variable_pricing', 1 );
		update_post_meta( $download_id, 'edd_variable_prices', $variable_prices );

		// Set up search parameters with variations=false.
		// This was the bug scenario where variable products were not being returned.
		$_GET['s']          = 'Variable Product';
		$_GET['variations'] = false;

		$search  = new \EDD\Downloads\Search();
		$results = $search->search();

		// Should return 1 item (the parent product only).
		$this->assertCount( 1, $results );

		// Verify the product ID matches.
		$this->assertEquals( $download_id, $results[0]['id'] );

		// Verify the product name matches.
		$this->assertStringContainsString( 'Variable Product Variations False', $results[0]['name'] );

		// Verify it includes the "(All Price Options)" suffix when variations is false.
		$this->assertStringContainsString( '(All Price Options)', $results[0]['name'] );
	}

	/**
	 * Test sort_by_relevance: Exact match prioritization.
	 *
	 * Verifies that when searching for a product, exact title matches appear
	 * before products that only partially match.
	 *
	 * @return void
	 * @since 3.6.5
	 */
	public function test_search_sort_by_relevance_exact_match() {
		// Create products with varying match types.
		$exact_match_id = self::factory()->post->create(
			array(
				'post_type'  => 'download',
				'post_title' => 'Product Alpha',
			)
		);

		$partial_match_1_id = self::factory()->post->create(
			array(
				'post_type'  => 'download',
				'post_title' => 'Product Alpha Pro',
			)
		);

		$partial_match_2_id = self::factory()->post->create(
			array(
				'post_type'  => 'download',
				'post_title' => 'Advanced Product Alpha',
			)
		);

		$_GET['s'] = 'Product Alpha';
		$search    = new \EDD\Downloads\Search();
		$results   = $search->search();

		// Should return all 3 products.
		$this->assertCount( 3, $results );

		// Exact match should appear first.
		$this->assertEquals( $exact_match_id, $results[0]['id'] );
		$this->assertStringContainsString( 'Product Alpha', $results[0]['name'] );
	}

	/**
	 * Test sort_by_relevance: "Starts with" prioritization.
	 *
	 * Verifies that products starting with the search term appear before
	 * products that only have a partial match.
	 *
	 * @return void
	 * @since 3.6.5
	 */
	public function test_search_sort_by_relevance_starts_with() {
		// Create products with different match types.
		$starts_with_1_id = self::factory()->post->create(
			array(
				'post_type'  => 'download',
				'post_title' => 'Test Suite Pro',
			)
		);

		$starts_with_2_id = self::factory()->post->create(
			array(
				'post_type'  => 'download',
				'post_title' => 'Test Framework',
			)
		);

		$partial_match_id = self::factory()->post->create(
			array(
				'post_type'  => 'download',
				'post_title' => 'Advanced Test Tools',
			)
		);

		$_GET['s'] = 'Test';
		$search    = new \EDD\Downloads\Search();
		$results   = $search->search();

		// Should return all 3 products.
		$this->assertCount( 3, $results );

		// First two should be "starts with" matches.
		$first_id  = $results[0]['id'];
		$second_id = $results[1]['id'];

		$this->assertTrue(
			( $first_id === $starts_with_1_id || $first_id === $starts_with_2_id ),
			'First result should be a "starts with" match'
		);

		$this->assertTrue(
			( $second_id === $starts_with_1_id || $second_id === $starts_with_2_id ),
			'Second result should be a "starts with" match'
		);

		// Third should be the partial match.
		$this->assertEquals( $partial_match_id, $results[2]['id'] );
	}

	/**
	 * Test sort_by_relevance: Alphabetical ordering (tiebreaker).
	 *
	 * Verifies that when multiple products have the same match type (e.g., both
	 * start with the search term), they are sorted alphabetically.
	 *
	 * @return void
	 * @since 3.6.5
	 */
	public function test_search_sort_by_relevance_alphabetical_tiebreaker() {
		// Create products with same match type but different names.
		$product_b_id = self::factory()->post->create(
			array(
				'post_type'  => 'download',
				'post_title' => 'Pro Bundle',
			)
		);

		$product_a_id = self::factory()->post->create(
			array(
				'post_type'  => 'download',
				'post_title' => 'Pro Advanced',
			)
		);

		$product_c_id = self::factory()->post->create(
			array(
				'post_type'  => 'download',
				'post_title' => 'Pro Complete',
			)
		);

		$_GET['s'] = 'Pro';
		$search    = new \EDD\Downloads\Search();
		$results   = $search->search();

		// Should return all 3 products.
		$this->assertCount( 3, $results );

		// All start with "Pro", so they should be sorted alphabetically.
		// Expected order: Pro Advanced, Pro Bundle, Pro Complete
		$this->assertEquals( $product_a_id, $results[0]['id'] );
		$this->assertEquals( $product_b_id, $results[1]['id'] );
		$this->assertEquals( $product_c_id, $results[2]['id'] );
	}

	/**
	 * Test sort_by_relevance: Case-insensitive matching.
	 *
	 * Verifies that search is case-insensitive - searching for "product beta"
	 * (lowercase) returns the same results as "Product Beta" (mixed case).
	 *
	 * @return void
	 * @since 3.6.5
	 */
	public function test_search_sort_by_relevance_case_insensitive() {
		// Create products.
		$exact_id = self::factory()->post->create(
			array(
				'post_type'  => 'download',
				'post_title' => 'Product Beta',
			)
		);

		$partial_id = self::factory()->post->create(
			array(
				'post_type'  => 'download',
				'post_title' => 'Advanced Product Beta',
			)
		);

		// Search with lowercase.
		$_GET['s'] = 'product beta';
		$search    = new \EDD\Downloads\Search();
		$results   = $search->search();

		// Should still find exact match first.
		$this->assertCount( 2, $results );
		$this->assertEquals( $exact_id, $results[0]['id'] );
	}

	/**
	 * Test sort_by_relevance: Multibyte character support (French).
	 *
	 * Verifies that multibyte characters (French accents) are handled correctly
	 * in the relevance sorting algorithm.
	 *
	 * @return void
	 * @since 3.6.5
	 */
	public function test_search_sort_by_relevance_multibyte_french() {
		// Create products with French accented characters.
		$exact_id = self::factory()->post->create(
			array(
				'post_type'  => 'download',
				'post_title' => 'Café',
			)
		);

		$starts_with_id = self::factory()->post->create(
			array(
				'post_type'  => 'download',
				'post_title' => 'Café Suite',
			)
		);

		$partial_id = self::factory()->post->create(
			array(
				'post_type'  => 'download',
				'post_title' => 'Advanced Café',
			)
		);

		$_GET['s'] = 'Café';
		$search    = new \EDD\Downloads\Search();
		$results   = $search->search();

		// Exact match should appear first.
		$this->assertCount( 3, $results );
		$this->assertEquals( $exact_id, $results[0]['id'] );
	}

	/**
	 * Test sort_by_relevance: Multibyte character support (Spanish).
	 *
	 * Verifies that multibyte characters (Spanish Ñ) are handled correctly
	 * in the relevance sorting algorithm.
	 *
	 * @return void
	 * @since 3.6.5
	 */
	public function test_search_sort_by_relevance_multibyte_spanish() {
		// Create products with Spanish multibyte character (Ñ).
		$starts_with_1_id = self::factory()->post->create(
			array(
				'post_type'  => 'download',
				'post_title' => 'Ñoño Pro',
			)
		);

		$starts_with_2_id = self::factory()->post->create(
			array(
				'post_type'  => 'download',
				'post_title' => 'Ñoño Software',
			)
		);

		$partial_id = self::factory()->post->create(
			array(
				'post_type'  => 'download',
				'post_title' => 'Advanced Ñoño',
			)
		);

		$_GET['s'] = 'Ñoño';
		$search    = new \EDD\Downloads\Search();
		$results   = $search->search();

		// Both "Ñoño Pro" and "Ñoño Software" start with "Ñoño".
		// They should appear before the partial match.
		$this->assertCount( 3, $results );

		$first_id  = $results[0]['id'];
		$second_id = $results[1]['id'];

		$this->assertTrue(
			( $first_id === $starts_with_1_id || $first_id === $starts_with_2_id ),
			'First result should start with "Ñoño"'
		);

		$this->assertTrue(
			( $second_id === $starts_with_1_id || $second_id === $starts_with_2_id ),
			'Second result should start with "Ñoño"'
		);

		// Third should be the partial match.
		$this->assertEquals( $partial_id, $results[2]['id'] );
	}

	/**
	 * Test sort_by_relevance: Mixed ASCII and multibyte characters.
	 *
	 * Verifies that the algorithm correctly handles a mix of ASCII and
	 * multibyte products in the same search.
	 *
	 * @return void
	 * @since 3.6.5
	 */
	public function test_search_sort_by_relevance_mixed_characters() {
		// Create mixed ASCII and multibyte products.
		$ascii_id = self::factory()->post->create(
			array(
				'post_type'  => 'download',
				'post_title' => 'Pro Plugin',
			)
		);

		$multibyte_id = self::factory()->post->create(
			array(
				'post_type'  => 'download',
				'post_title' => 'Café Pro',
			)
		);

		$partial_ascii = self::factory()->post->create(
			array(
				'post_type'  => 'download',
				'post_title' => 'Advanced Pro',
			)
		);

		$partial_multibyte = self::factory()->post->create(
			array(
				'post_type'  => 'download',
				'post_title' => 'Café Tools',
			)
		);

		$_GET['s'] = 'Pro';
		$search    = new \EDD\Downloads\Search();
		$results   = $search->search();

		// Should find products containing "Pro".
		// "Pro Plugin" and "Café Pro" both start with or contain "Pro".
		$result_ids = wp_list_pluck( $results, 'id' );
		$this->assertContains( $ascii_id, $result_ids );
		$this->assertContains( $multibyte_id, $result_ids );
	}

	/**
	 * Test sort_by_relevance: Empty search string.
	 *
	 * Verifies that empty search strings are handled gracefully and return
	 * no results (as expected for search functionality).
	 *
	 * @return void
	 * @since 3.6.5
	 */
	public function test_search_sort_by_relevance_empty_search() {
		// Create a product.
		self::factory()->post->create(
			array(
				'post_type'  => 'download',
				'post_title' => 'Test Product',
			)
		);

		$_GET['s'] = '';
		$search    = new \EDD\Downloads\Search();
		$results   = $search->search();

		// Empty search should return empty results.
		$this->assertEmpty( $results );
	}

	/**
	 * Test sort_by_relevance: No matches.
	 *
	 * Verifies that searches with no matching products return empty results.
	 *
	 * @return void
	 * @since 3.6.5
	 */
	public function test_search_sort_by_relevance_no_matches() {
		// Create a product.
		self::factory()->post->create(
			array(
				'post_type'  => 'download',
				'post_title' => 'Test Product',
			)
		);

		// Search for something that doesn't exist.
		$_GET['s'] = 'NonexistentProduct';
		$search    = new \EDD\Downloads\Search();
		$results   = $search->search();

		// No matches should return empty results.
		$this->assertEmpty( $results );
	}

	/**
	 * Test sort_by_relevance: Complex real-world scenario.
	 *
	 * Tests a realistic scenario with multiple product types and names
	 * to ensure the three-tier sorting works correctly across complex scenarios.
	 *
	 * @return void
	 * @since 3.6.5
	 */
	public function test_search_sort_by_relevance_complex_scenario() {
		// Create a realistic set of products.
		$exact_id = self::factory()->post->create(
			array(
				'post_type'  => 'download',
				'post_title' => 'Analytics',
			)
		);

		$starts_with_1_id = self::factory()->post->create(
			array(
				'post_type'  => 'download',
				'post_title' => 'Analytics Pro',
			)
		);

		$starts_with_2_id = self::factory()->post->create(
			array(
				'post_type'  => 'download',
				'post_title' => 'Analytics Advanced',
			)
		);

		$partial_1_id = self::factory()->post->create(
			array(
				'post_type'  => 'download',
				'post_title' => 'Premium Analytics Suite',
			)
		);

		$partial_2_id = self::factory()->post->create(
			array(
				'post_type'  => 'download',
				'post_title' => 'Data Analytics Tools',
			)
		);

		$_GET['s'] = 'Analytics';
		$search    = new \EDD\Downloads\Search();
		$results   = $search->search();

		// Should return all 5 products.
		$this->assertCount( 5, $results );

		// First should be exact match.
		$this->assertEquals( $exact_id, $results[0]['id'] );

		// Next two should be "starts with" matches (alphabetically ordered).
		$second_id = $results[1]['id'];
		$third_id  = $results[2]['id'];

		$this->assertTrue(
			( $second_id === $starts_with_1_id || $second_id === $starts_with_2_id ),
			'Second result should be a "starts with" match'
		);

		$this->assertTrue(
			( $third_id === $starts_with_1_id || $third_id === $starts_with_2_id ),
			'Third result should be a "starts with" match'
		);

		// Last two should be partial matches.
		$fourth_id  = $results[3]['id'];
		$fifth_id   = $results[4]['id'];

		$this->assertTrue(
			( $fourth_id === $partial_1_id || $fourth_id === $partial_2_id ),
			'Fourth result should be a partial match'
		);

		$this->assertTrue(
			( $fifth_id === $partial_1_id || $fifth_id === $partial_2_id ),
			'Fifth result should be a partial match'
		);
	}
}
