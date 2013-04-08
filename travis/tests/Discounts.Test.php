<?php
require_once('./travis/vendor/wordpress-tests/lib/factory.php');

/**
 * Test Discounts
 */
class Test_Easy_Digital_Downloads_Discounts extends WP_UnitTestCase {
	protected $_post = null;

	public function setUp() {
		parent::setUp();
		$wp_factory = new WP_UnitTest_Factory;
		$post_id = $wp_factory->post->create( array( 'post_type' => 'edd_discount', 'post_status' => 'draft' ) );
		$this->_post = get_post( $post_id );
	}

	public function tearDown() {
		parent::tearDown();
	}

	public function testAdditionOfDiscount() {
		$post = array(
			'name' => 'Test Discount',
			'type' => 'percentage',
			'amount' => '20',
			'code' => '20OFF',
			'product_condition' => 'all'
		);

		$this->assertTrue(edd_store_discount($post));
	}

	public function testDeletionOfDiscount() {
		edd_remove_discount( $this->_post->ID );
		$this->assertFalse( wp_cache_get( $this->_post->ID, 'posts' ) );
	}
}