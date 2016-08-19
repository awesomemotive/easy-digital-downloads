<?php


/**
 * @group edd_cpt
 */
class Tests_Post_Type_Labels extends WP_UnitTestCase {
	public function setUp() {
		parent::setUp();
	}

	public function tearDown() {
		parent::tearDown();
	}

	public function test_get_default_labels() {
		$out = edd_get_default_labels();
		$this->assertArrayHasKey( 'singular', $out );
		$this->assertArrayHasKey( 'plural', $out );

		$this->assertEquals( 'Download', $out['singular'] );
		$this->assertEquals( 'Downloads', $out['plural'] );
	}

	public function test_singular_label() {
		$this->assertEquals( 'Download', edd_get_label_singular() );
		$this->assertEquals( 'download', edd_get_label_singular( true ) );
	}

	public function test_plural_label() {
		$this->assertEquals( 'Downloads', edd_get_label_plural() );
		$this->assertEquals( 'downloads', edd_get_label_plural( true ) );
	}

	public function test_taxonomy_labels() {

		$category_labels = edd_get_taxonomy_labels();
		$this->assertInternalType( 'array', $category_labels );
		$this->assertArrayHasKey( 'name', $category_labels );
		$this->assertArrayHasKey( 'singular_name', $category_labels );
		$this->assertTrue( in_array( 'Download Category', $category_labels ) );
		$this->assertTrue( in_array( 'Download Categories', $category_labels ) );
		// Negative test for our change to exclude singular post type label in #3212
		$this->assertTrue( in_array( 'Categories', $category_labels ) );

		$this->assertInternalType( 'array', $category_labels );
		$this->assertArrayHasKey( 'name', $category_labels );
		$this->assertArrayHasKey( 'singular_name', $category_labels );
		$this->assertTrue( in_array( 'Download Category', $category_labels ) );
		$this->assertTrue( in_array( 'Download Categories', $category_labels ) );
		// Negative test for our change to exclude singular post type label in #3212
		$this->assertTrue( in_array( 'Categories', $category_labels ) );

		$tag_labels = edd_get_taxonomy_labels( 'download_tag' );
		$this->assertInternalType( 'array', $tag_labels );
		$this->assertArrayHasKey( 'name', $tag_labels );
		$this->assertArrayHasKey( 'singular_name', $tag_labels );
		$this->assertTrue( in_array( 'Download Tag', $tag_labels ) );
		$this->assertTrue( in_array( 'Download Tags', $tag_labels ) );
		// Negative test for our change to exclude singular post type label in #3212
		$this->assertTrue( in_array( 'Tags', $tag_labels ) );

	}
}
