<?php

/**
 * Test Logging
 */
class Test_Logging extends WP_UnitTestCase {
	protected $_object = null;

	public function setUp() {
		parent::setUp();

		$this->_object = new EDD_Logging;
		$this->_object->register_post_type();
		$this->_object->register_taxonomy();
	}

	public function test_post_type() {
		global $wp_post_types;
		$this->assertArrayHasKey( 'edd_log', $wp_post_types );
	}

	public function test_post_type_labels() {
		global $wp_post_types;
		$this->assertEquals( 'Logs', $wp_post_types['edd_log']->labels->name );
		$this->assertEquals( 'Logs', $wp_post_types['edd_log']->labels->singular_name );
		$this->assertEquals( 'Add New', $wp_post_types['edd_log']->labels->add_new );
		$this->assertEquals( 'Add New Post', $wp_post_types['edd_log']->labels->add_new_item );
		$this->assertEquals( 'Edit Post', $wp_post_types['edd_log']->labels->edit_item );
		$this->assertEquals( 'View Post', $wp_post_types['edd_log']->labels->view_item );
		$this->assertEquals( 'Search Posts', $wp_post_types['edd_log']->labels->search_items );
		$this->assertEquals( 'No posts found.', $wp_post_types['edd_log']->labels->not_found );
		$this->assertEquals( 'No posts found in Trash.', $wp_post_types['edd_log']->labels->not_found_in_trash );
		$this->assertEquals( 'Logs', $wp_post_types['edd_log']->labels->all_items );
		$this->assertEquals( 'Logs', $wp_post_types['edd_log']->labels->menu_name );
		$this->assertEquals( 'Logs', $wp_post_types['edd_log']->labels->name_admin_bar );
		$this->assertEquals( '', $wp_post_types['edd_log']->publicly_queryable );
		$this->assertEquals( 'post', $wp_post_types['edd_log']->capability_type );
		$this->assertEquals( 1, $wp_post_types['edd_log']->map_meta_cap );
		$this->assertEquals( '', $wp_post_types['edd_log']->rewrite );
		$this->assertEquals( '', $wp_post_types['edd_log']->has_archive );
		$this->assertEquals( 'Logs', $wp_post_types['edd_log']->label );
	}

	public function test_taxonomy_exist() {
		global $wp_taxonomies;
		$this->assertArrayHasKey( 'edd_log_type', $wp_taxonomies );
	}

	public function test_terms_exist() {
		$types = $this->_object->log_types();
		foreach ( $types as $type ) {
			$this->assertArrayHasKey( 'term_id', term_exists( $type, 'edd_log_type' ) );
			$this->assertArrayHasKey( 'term_taxonomy_id', term_exists( $type, 'edd_log_type' ) );
		}
	}

	public function test_log_types() {
		$types = $this->_object->log_types();
		$this->assertEquals( 'sale', $types[0] );
		$this->assertEquals( 'file_download', $types[1] );
		$this->assertEquals( 'gateway_error', $types[2] );
		$this->assertEquals( 'api_request', $types[3] );
	}

	public function test_valid_log() {
		$this->assertTrue( $this->_object->valid_type( 'file_download' ) );		
	}

	public function test_fake_log() {
		$this->assertFalse( $this->_object->valid_type( 'foo' ) );
	}

	public function test_add() {
		$this->assertNotNull( $this->_object->add() );
		$this->assertInternalType( 'integer', $this->_object->add() );
	}
}