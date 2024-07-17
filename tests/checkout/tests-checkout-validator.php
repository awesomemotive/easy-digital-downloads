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
}
