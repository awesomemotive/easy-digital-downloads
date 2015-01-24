<?php

/**
 * @group theme_compatibility
 */
class Tests_Theme_Compatibility extends WP_UnitTestCase {

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

		// Test some regular values in a post (should be unaffected)
		$this->assertContains( 'post', get_post_class() );
		$this->assertContains( 'type-post', get_post_class() );

		// Reset to origin
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
			'post_title' 	=> 'Test Download Product',
			'post_name' 	=> 'test-download-product',
			'post_type' 	=> 'download',
			'post_status' 	=> 'publish'
		) );
		$this->go_to( get_permalink( $post_id ) );

		// Test some regular values in a post (should be unaffected)
		$this->assertNotContains( 'download', get_post_class() );
		$this->assertContains( 'type-download', get_post_class() );

		// Reset to origin
		wp_delete_post( $post_id, true );

	}


}
