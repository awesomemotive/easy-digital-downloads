<?php
namespace EDD\Tests;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;

/**
 * @group edd_cpt
 */
class Tests_Post_Types extends EDD_UnitTestCase {

	/**
	 * @covers ::edd_setup_edd_post_types
	 */
	public function test_downloads_post_type() {
		global $wp_post_types;
		$this->assertArrayHasKey( 'download', $wp_post_types );
	}

	public function test_downloads_post_type_labels() {
		global $wp_post_types;
		$this->assertEquals( 'Downloads', $wp_post_types['download']->labels->name );
		$this->assertEquals( 'Download', $wp_post_types['download']->labels->singular_name );
		$this->assertEquals( 'Add New', $wp_post_types['download']->labels->add_new );
		$this->assertEquals( 'Add New Download', $wp_post_types['download']->labels->add_new_item );
		$this->assertEquals( 'Edit Download', $wp_post_types['download']->labels->edit_item );
		$this->assertEquals( 'View Download', $wp_post_types['download']->labels->view_item );
		$this->assertEquals( 'Search Downloads', $wp_post_types['download']->labels->search_items );
		$this->assertEquals( 'No Downloads found', $wp_post_types['download']->labels->not_found );
		$this->assertEquals( 'No Downloads found in Trash', $wp_post_types['download']->labels->not_found_in_trash );
		$this->assertEquals( 'Downloads', $wp_post_types['download']->labels->all_items );
		$this->assertEquals( 'Downloads', $wp_post_types['download']->labels->menu_name );
		$this->assertEquals( 'Download', $wp_post_types['download']->labels->name_admin_bar );
		$this->assertEquals( 1, $wp_post_types['download']->publicly_queryable );
		$this->assertEquals( 'product', $wp_post_types['download']->capability_type );
		$this->assertEquals( 1, $wp_post_types['download']->map_meta_cap );
		$this->assertEquals( 'downloads', $wp_post_types['download']->rewrite['slug'] );
		$this->assertEquals( 1, $wp_post_types['download']->has_archive );
		$this->assertEquals( 'download', $wp_post_types['download']->query_var );
		$this->assertEquals( 'Downloads', $wp_post_types['download']->label );
	}
}
