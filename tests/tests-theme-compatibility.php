<?php
namespace EDD\Tests;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;

/**
 * @group theme_compatibility
 */
class Tests_Theme_Compatibility extends EDD_UnitTestCase {

	/**
	 * Test that the filter exists of the function.
	 *
	 * @since 2.2.4
	 */
	public function test_edd_responsive_download_post_class_filter() {

		$this->assertNotFalse( has_filter( 'post_class', 'edd_responsive_download_post_class' ) );

	}

	/**
	 * Test the function
	 *
	 * @since 2.2.4
	 */
	public function test_edd_responsive_download_post_class_post() {

		// Prepare test
		$post_id = $this->factory->post->create( array(
			'post_title' 	=> 'Hello World',
			'post_name' 	=> 'hello-world',
			'post_type' 	=> 'post',
			'post_status' 	=> 'publish'
		) );
		$this->go_to( get_permalink( $post_id ) );

		$post_classes = get_post_class();

		// Test some regular values in a post (should be unaffected)
		$this->assertTrue( in_array( 'post-' . $post_id, $post_classes ) );
		$this->assertTrue( in_array( 'type-post', $post_classes ) );

		// Reset to origin
		$this->go_to( '' );
		wp_delete_post( $post_id, true );

	}

	/**
	 * Test the function
	 *
	 * @since 2.2.4
	 */
	public function test_edd_responsive_download_post_class_download() {

		// Prepare test
		$post_id = $this->factory->post->create( array(
			'post_title'  => 'Test Download Product',
			'post_name'   => 'test-download-product',
			'post_type'   => 'download',
			'post_status' => 'publish',
		) );

		$this->go_to( get_permalink( $post_id ) );

		$post_classes = get_post_class();

		// Test some regular values in a download (should be unaffected)
		$this->assertTrue( in_array( 'type-download', $post_classes ) );

		// Reset to origin
		$this->go_to( '' );
		wp_delete_post( $post_id, true );
	}
}
