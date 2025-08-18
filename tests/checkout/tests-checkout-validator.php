<?php
namespace EDD\Tests\Checkout;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;

/**
 * Checkout Validator tests.
 * Currently only tests the `edd_is_checkout` function.
 */
class Validator extends EDD_UnitTestCase {

	public function test_edd_is_checkout_setting() {
		$checkout_page = edd_get_option( 'purchase_page' );

		$this->go_to( get_permalink( $checkout_page ) );

		$this->assertTrue( edd_is_checkout() );
	}

	public function test_edd_is_checkout_shortcode() {
		$post_id = $this->factory->post->create( array(
			'post_title'   => 'Test Page',
			'post_type'    => 'page',
			'post_status'  => 'publish',
			'post_content' => '[download_checkout]',
		) );

		$this->go_to( get_permalink( $post_id ) );

		do_action( 'template_redirect' ); // Necessary to trigger correct actions

		$this->assertTrue( edd_is_checkout() );
	}

	public function test_edd_is_checkout_block() {
		$post_id = $this->factory->post->create( array(
			'post_title'   => 'Test Page',
			'post_type'    => 'page',
			'post_status'  => 'publish',
			'post_content' => '<!-- wp:edd/checkout /-->',
		) );
		$this->go_to( get_permalink( $post_id ) );

		do_action( 'template_redirect' ); // Necessary to trigger correct actions

		$this->assertTrue( edd_is_checkout() );
	}

	public function test_edd_is_checkout_fail() {
		$post_id = $this->factory->post->create( array(
			'post_title'   => 'Test Page 2',
			'post_type'    => 'page',
			'post_status'  => 'publish',
			'post_content' => 'Test Page',
		) );

		$this->go_to( get_permalink( $post_id ) );

		do_action( 'template_redirect' ); // Necessary to trigger correct actions

		$this->assertFalse( edd_is_checkout() );
	}

	public function test_edd_is_checkout_ajax_is_true_shortcode() {
		$post_id = $this->factory->post->create( array(
			'post_title'   => 'Test Page',
			'post_type'    => 'page',
			'post_status'  => 'publish',
			'post_content' => '[download_checkout]',
		) );
		$_POST['current_page'] = $post_id;
		add_filter( 'wp_doing_ajax', '__return_true' );

		$this->assertTrue( edd_is_checkout() );

		unset( $_POST['current_page'] );
		remove_filter( 'wp_doing_ajax', '__return_true' );
	}

	public function test_edd_is_checkout_ajax_is_true_block() {
		$post_id = $this->factory->post->create( array(
			'post_title'   => 'Test Page',
			'post_type'    => 'page',
			'post_status'  => 'publish',
			'post_content' => '<!-- wp:edd/checkout /-->',
		) );
		$_POST['current_page'] = $post_id;
		add_filter( 'wp_doing_ajax', '__return_true' );

		$this->assertTrue( edd_is_checkout() );

		unset( $_POST['current_page'] );
		remove_filter( 'wp_doing_ajax', '__return_true' );
	}

	/**
	 * Test that has_checkout returns false when post_id is 0.
	 * This tests the scenario where get_queried_object_id() would return 0.
	 */
	public function test_edd_is_checkout_with_zero_queried_object_id() {
		// Use reflection to access the private has_checkout method
		$reflection = new \ReflectionClass( 'EDD\Checkout\Validator' );
		$method = $reflection->getMethod( 'has_checkout' );
		$method->setAccessible( true );

		// Test with post_id = 0 (should return false)
		$result = $method->invokeArgs( null, array( 0 ) );
		$this->assertFalse( $result, 'has_checkout should return false when post_id is 0' );

		// Test with post_id = false (should return false)
		$result = $method->invokeArgs( null, array( false ) );
		$this->assertFalse( $result, 'has_checkout should return false when post_id is false' );

		// Test with post_id = null (should return false)
		$result = $method->invokeArgs( null, array( null ) );
		$this->assertFalse( $result, 'has_checkout should return false when post_id is null' );

		// Test with post_id = empty string (should return false)
		$result = $method->invokeArgs( null, array( '' ) );
		$this->assertFalse( $result, 'has_checkout should return false when post_id is empty string' );

		// Create a valid post to verify the method works with valid IDs
		$post_id = $this->factory->post->create( array(
			'post_title'   => 'Test Post',
			'post_type'    => 'page',
			'post_status'  => 'publish',
			'post_content' => '[download_checkout]',
		) );

		// Test with valid post_id that has checkout shortcode (should return true)
		$result = $method->invokeArgs( null, array( $post_id ) );
		$this->assertTrue( $result, 'has_checkout should return true when post has checkout shortcode' );

		// Create a post without checkout content
		$regular_post_id = $this->factory->post->create( array(
			'post_title'   => 'Regular Post',
			'post_type'    => 'page',
			'post_status'  => 'publish',
			'post_content' => 'Regular content',
		) );

		// Test with valid post_id that doesn't have checkout content (should return false)
		$result = $method->invokeArgs( null, array( $regular_post_id ) );
		$this->assertFalse( $result, 'has_checkout should return false when post has no checkout content' );
	}

	/**
	 * Test that has_checkout returns true with checkout block content.
	 */
	public function test_has_checkout_with_block_content() {
		// Use reflection to access the private has_checkout method
		$reflection = new \ReflectionClass( 'EDD\Checkout\Validator' );
		$method = $reflection->getMethod( 'has_checkout' );
		$method->setAccessible( true );

		// Create a post with checkout block
		$block_post_id = $this->factory->post->create( array(
			'post_title'   => 'Checkout Block Page',
			'post_type'    => 'page',
			'post_status'  => 'publish',
			'post_content' => '<!-- wp:edd/checkout /-->',
		) );

		// Test with valid post_id that has checkout block (should return true)
		$result = $method->invokeArgs( null, array( $block_post_id ) );
		$this->assertTrue( $result, 'has_checkout should return true when post has checkout block' );
	}
}
