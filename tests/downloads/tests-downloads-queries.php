<?php

namespace EDD\Tests\Downloads;

use EDD\Downloads\Query;
use EDD\Tests\Helpers;
use EDD\Tests\PHPUnit\EDD_UnitTestCase;

class Queries extends EDD_UnitTestCase {

	protected $simple_download;

	protected $variable_download;

	protected $featured_download;

	protected $category;

	protected $tag;

	protected $author;

	public function setup(): void {
		parent::setUp();

		// Create test downloads
		$this->simple_download   = Helpers\EDD_Helper_Download::create_simple_download();
		$this->variable_download = Helpers\EDD_Helper_Download::create_variable_download();
		$this->featured_download = Helpers\EDD_Helper_Download::create_simple_download();

		// Set up featured download
		update_post_meta( $this->featured_download->ID, 'edd_feature_download', '1' );

		// Create test taxonomy terms
		$this->category = wp_insert_term( 'Test Category', 'download_category' );
		$this->tag      = wp_insert_term( 'Test Tag', 'download_tag' );

		// Create test author
		$this->author = $this->factory->user->create( array(
			'user_login' => 'testauthor',
			'user_email' => 'test@example.com',
		) );

		// Assign taxonomy terms to downloads
		wp_set_object_terms( $this->simple_download->ID, array( $this->category['term_id'] ), 'download_category' );
		wp_set_object_terms( $this->variable_download->ID, array( $this->tag['term_id'] ), 'download_tag' );

		// Set author for one download
		wp_update_post( array(
			'ID'          => $this->simple_download->ID,
			'post_author' => $this->author,
		) );
	}

	public function tearDown(): void {
		parent::tearDown();

		Helpers\EDD_Helper_Download::delete_download( $this->simple_download->ID );
		Helpers\EDD_Helper_Download::delete_download( $this->variable_download->ID );
		Helpers\EDD_Helper_Download::delete_download( $this->featured_download->ID );

		wp_delete_term( $this->category['term_id'], 'download_category' );
		wp_delete_term( $this->tag['term_id'], 'download_tag' );

		wp_delete_user( $this->author );
	}

	/**
	 * @covers \EDD\Downloads\Query::__construct
	 */
	public function test_construct_sets_attributes() {
		$attributes = array( 'number' => 10 );
		$query      = new Query( $attributes );

		$reflection = new \ReflectionClass( $query );
		$property   = $reflection->getProperty( 'attributes' );
		$property->setAccessible( true );
		$result     = $property->getValue( $query );

		// Check provided attribute
		$this->assertEquals( 10, $result['number'] );
		// Check defaults are set
		$this->assertEquals( 'DESC', $result['order'] );
		$this->assertEquals( 'post_date', $result['orderby'] );
		$this->assertTrue( $result['pagination'] );
		$this->assertEquals( array(), $result['category'] );
		$this->assertEquals( array(), $result['tag'] );
		$this->assertFalse( $result['author'] );
		$this->assertEquals( '', $result['featured'] );
		$this->assertEquals( '', $result['ids'] );
		$this->assertEquals( '', $result['exclude_category'] );
		$this->assertEquals( '', $result['exclude_tags'] );
		$this->assertEquals( 'AND', $result['relation'] );
	}

	/**
	 * @covers \EDD\Downloads\Query::get_query
	 */
	public function test_get_query_returns_array() {
		$attributes = array();
		$query      = new Query( $attributes );

		$result = $query->get_query();

		$this->assertIsArray( $result );
		$this->assertEquals( 'download', $result['post_type'] );
	}

	/**
	 * @covers \EDD\Downloads\Query::get_query
	 */
	public function test_get_query_with_order() {
		$attributes = array( 'order' => 'DESC' );
		$query      = new Query( $attributes );

		$result = $query->get_query();

		$this->assertEquals( 'DESC', $result['order'] );
	}

	/**
	 * @covers \EDD\Downloads\Query::get_query
	 */
	public function test_get_query_with_ids() {
		$ids        = $this->simple_download->ID . ',' . $this->variable_download->ID;
		$attributes = array( 'ids' => $ids );
		$query      = new Query( $attributes );

		$result = $query->get_query();

		$this->assertArrayHasKey( 'post__in', $result );
		$this->assertContains( (string) $this->simple_download->ID, $result['post__in'] );
		$this->assertContains( (string) $this->variable_download->ID, $result['post__in'] );
	}

	/**
	 * @covers \EDD\Downloads\Query::get_query
	 */
	public function test_get_query_with_featured_yes() {
		$attributes = array( 'featured' => 'yes' );
		$query      = new Query( $attributes );

		$result = $query->get_query();

		$this->assertArrayHasKey( 'meta_query', $result );
		$this->assertEquals( 'edd_feature_download', $result['meta_query'][0]['key'] );
		$this->assertEquals( '1', $result['meta_query'][0]['value'] );
	}

	/**
	 * @covers \EDD\Downloads\Query::parse_pagination
	 */
	public function test_parse_pagination_with_number() {
		$attributes = array( 'number' => 5, 'pagination' => true );
		$query      = new Query( $attributes );

		$result = $query->get_query();

		$this->assertEquals( 5, $result['posts_per_page'] );
	}

	/**
	 * @covers \EDD\Downloads\Query::parse_pagination
	 */
	public function test_parse_pagination_without_pagination_but_with_number() {
		$attributes = array( 'number' => 8, 'pagination' => false );
		$query      = new Query( $attributes );

		$result = $query->get_query();

		$this->assertEquals( 8, $result['posts_per_page'] );
	}

	/**
	 * @covers \EDD\Downloads\Query::parse_pagination
	 */
	public function test_parse_pagination_with_negative_number() {
		$attributes = array( 'number' => -3, 'pagination' => true );
		$query      = new Query( $attributes );

		$result = $query->get_query();

		$this->assertEquals( 3, $result['posts_per_page'] );
	}

	/**
	 * @covers \EDD\Downloads\Query::parse_pagination
	 */
	public function test_parse_pagination_without_pagination_and_number() {
		$attributes = array( 'pagination' => false, 'number' => 0 );
		$query      = new Query( $attributes );

		$result = $query->get_query();

		$this->assertTrue( $result['nopaging'] );
	}

	/**
	 * @covers \EDD\Downloads\Query::parse_orderby
	 */
	public function test_parse_orderby_price() {
		$attributes = array( 'orderby' => 'price' );
		$query      = new Query( $attributes );

		$result = $query->get_query();

		$this->assertEquals( 'edd_price', $result['meta_key'] );
		$this->assertEquals( 'meta_value_num', $result['orderby'] );
	}

	/**
	 * @covers \EDD\Downloads\Query::parse_orderby
	 */
	public function test_parse_orderby_sales() {
		$attributes = array( 'orderby' => 'sales' );
		$query      = new Query( $attributes );

		$result = $query->get_query();

		$this->assertEquals( '_edd_download_sales', $result['meta_key'] );
		$this->assertEquals( 'meta_value_num', $result['orderby'] );
	}

	/**
	 * @covers \EDD\Downloads\Query::parse_orderby
	 */
	public function test_parse_orderby_earnings() {
		$attributes = array( 'orderby' => 'earnings' );
		$query      = new Query( $attributes );

		$result = $query->get_query();

		$this->assertEquals( '_edd_download_earnings', $result['meta_key'] );
		$this->assertEquals( 'meta_value_num', $result['orderby'] );
	}

	/**
	 * @covers \EDD\Downloads\Query::parse_orderby
	 */
	public function test_parse_orderby_default() {
		$attributes = array( 'orderby' => 'title' );
		$query      = new Query( $attributes );

		$result = $query->get_query();

		$this->assertEquals( 'title', $result['orderby'] );
		$this->assertArrayNotHasKey( 'meta_key', $result );
	}

	/**
	 * @covers \EDD\Downloads\Query::parse_orderby
	 */
	public function test_parse_orderby_with_featured_orderby() {
		$attributes = array( 'orderby' => 'title', 'featured' => 'orderby' );
		$query      = new Query( $attributes );

		$result = $query->get_query();

		$this->assertArrayHasKey( 'meta_query', $result );
		$this->assertEquals( 'OR', $result['meta_query']['relation'] );
		$this->assertStringContainsString( 'title', $result['orderby'] );
	}

	/**
	 * @covers \EDD\Downloads\Query::parse_tax_query
	 */
	public function test_parse_tax_query_with_category() {
		$attributes = array( 'category' => array( $this->category['term_id'] ) );
		$query      = new Query( $attributes );

		$result = $query->get_query();

		$this->assertArrayHasKey( 'tax_query', $result );
		$this->assertEquals( 'download_category', $result['tax_query'][0]['taxonomy'] );
		$this->assertContains( $this->category['term_id'], $result['tax_query'][0]['terms'] );
	}

	/**
	 * @covers \EDD\Downloads\Query::parse_tax_query
	 */
	public function test_parse_tax_query_with_tag() {
		$attributes = array( 'tag' => array( $this->tag['term_id'] ) );
		$query      = new Query( $attributes );

		$result = $query->get_query();

		$this->assertArrayHasKey( 'tax_query', $result );
		$this->assertEquals( 'download_tag', $result['tax_query'][0]['taxonomy'] );
		$this->assertContains( $this->tag['term_id'], $result['tax_query'][0]['terms'] );
	}

	/**
	 * @covers \EDD\Downloads\Query::parse_tax_query
	 */
	public function test_parse_tax_query_with_exclude_category() {
		$attributes = array( 'exclude_category' => $this->category['term_id'] );
		$query      = new Query( $attributes );

		$result = $query->get_query();

		$this->assertArrayHasKey( 'tax_query', $result );
		$this->assertEquals( 'download_category', $result['tax_query'][0]['taxonomy'] );
		$this->assertEquals( 'NOT IN', $result['tax_query'][0]['operator'] );
		$this->assertContains( $this->category['term_id'], $result['tax_query'][0]['terms'] );
	}

	/**
	 * @covers \EDD\Downloads\Query::parse_tax_query
	 */
	public function test_parse_tax_query_with_exclude_tags() {
		$attributes = array( 'exclude_tags' => $this->tag['term_id'] );
		$query      = new Query( $attributes );

		$result = $query->get_query();

		$this->assertArrayHasKey( 'tax_query', $result );
		$this->assertEquals( 'download_tag', $result['tax_query'][0]['taxonomy'] );
		$this->assertEquals( 'NOT IN', $result['tax_query'][0]['operator'] );
		$this->assertContains( $this->tag['term_id'], $result['tax_query'][0]['terms'] );
	}

	/**
	 * @covers \EDD\Downloads\Query::parse_tax_query
	 */
	public function test_parse_tax_query_with_relation() {
		$attributes = array(
			'category' => array( $this->category['term_id'] ),
			'tag'      => array( $this->tag['term_id'] ),
			'relation' => 'OR',
		);
		$query      = new Query( $attributes );

		$result = $query->get_query();

		$this->assertArrayHasKey( 'tax_query', $result );
		$this->assertEquals( 'OR', $result['tax_query']['relation'] );
	}

	/**
	 * @covers \EDD\Downloads\Query::get_term_ids
	 */
	public function test_get_term_ids_with_numeric_id() {
		$attributes = array( 'category' => array( $this->category['term_id'] ) );
		$query      = new Query( $attributes );

		$result = $query->get_query();

		$this->assertArrayHasKey( 'tax_query', $result );
		$this->assertContains( $this->category['term_id'], $result['tax_query'][0]['terms'] );
	}

	/**
	 * @covers \EDD\Downloads\Query::get_term_ids
	 */
	public function test_get_term_ids_with_slug() {
		$term_slug  = get_term( $this->category['term_id'], 'download_category' )->slug;
		$attributes = array( 'category' => array( $term_slug ) );
		$query      = new Query( $attributes );

		$result = $query->get_query();

		$this->assertArrayHasKey( 'tax_query', $result );
		$this->assertContains( $this->category['term_id'], $result['tax_query'][0]['terms'] );
	}

	/**
	 * @covers \EDD\Downloads\Query::get_term_ids
	 */
	public function test_get_term_ids_with_string_list() {
		$term_ids   = $this->category['term_id'] . ',' . $this->tag['term_id'];
		$attributes = array( 'category' => $term_ids );
		$query      = new Query( $attributes );

		$result = $query->get_query();

		$this->assertArrayHasKey( 'tax_query', $result );
		$this->assertContains( $this->category['term_id'], $result['tax_query'][0]['terms'] );
	}

	/**
	 * @covers \EDD\Downloads\Query::parse_author
	 */
	public function test_parse_author_with_numeric_id() {
		$attributes = array( 'author' => $this->author );
		$query      = new Query( $attributes );

		$result = $query->get_query();

		$this->assertArrayHasKey( 'author', $result );
		$this->assertEquals( $this->author, $result['author'] );
	}

	/**
	 * @covers \EDD\Downloads\Query::parse_author
	 */
	public function test_parse_author_with_username() {
		$attributes = array( 'author' => 'testauthor' );
		$query      = new Query( $attributes );

		$result = $query->get_query();

		$this->assertArrayHasKey( 'author', $result );
		$this->assertEquals( $this->author, $result['author'] );
	}

	/**
	 * @covers \EDD\Downloads\Query::parse_author
	 */
	public function test_parse_author_with_multiple_authors() {
		$author2    = $this->factory->user->create( array( 'user_login' => 'testauthor2' ) );
		$attributes = array( 'author' => $this->author . ',' . $author2 );
		$query      = new Query( $attributes );

		$result = $query->get_query();

		$this->assertArrayHasKey( 'author', $result );
		$this->assertStringContainsString( (string) $this->author, $result['author'] );
		$this->assertStringContainsString( (string) $author2, $result['author'] );

		wp_delete_user( $author2 );
	}

	/**
	 * @covers \EDD\Downloads\Query::get_paged
	 */
	public function test_get_paged_returns_one_by_default() {
		$attributes = array();
		$query      = new Query( $attributes );

		$result = $query->get_query();

		$this->assertEquals( 1, $result['paged'] );
	}

	/**
	 * @covers \EDD\Downloads\Query::get_query
	 */
	public function test_get_query_applies_filter() {
		$filter_applied = false;
		$filter_function = function( $query, $attributes ) use ( &$filter_applied ) {
			$filter_applied = true;
			return $query;
		};

		add_filter( 'edd_downloads_query', $filter_function, 10, 2 );

		$attributes = array();
		$query      = new Query( $attributes );
		$query->get_query();

		remove_filter( 'edd_downloads_query', $filter_function );

		$this->assertTrue( $filter_applied );
	}

	/**
	 * @covers \EDD\Downloads\Query::get_query
	 */
	public function test_get_query_complex_scenario() {
		$attributes = array(
			'number'     => 10,
			'orderby'    => 'sales',
			'order'      => 'DESC',
			'category'   => array( $this->category['term_id'] ),
			'tag'        => array( $this->tag['term_id'] ),
			'author'     => $this->author,
			'featured'   => 'yes',
			'pagination' => true,
		);
		$query      = new Query( $attributes );

		$result = $query->get_query();

		// Test multiple aspects are properly set
		$this->assertEquals( 'download', $result['post_type'] );
		$this->assertEquals( 'DESC', $result['order'] );
		$this->assertEquals( 10, $result['posts_per_page'] );
		$this->assertEquals( '_edd_download_sales', $result['meta_key'] );
		$this->assertEquals( 'meta_value_num', $result['orderby'] );
		$this->assertArrayHasKey( 'tax_query', $result );
		$this->assertArrayHasKey( 'meta_query', $result );
		$this->assertEquals( $this->author, $result['author'] );
		$this->assertEquals( 1, $result['paged'] );
	}
}
