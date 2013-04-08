<?php
/**
 * Test Discounts
 */
class Test_Easy_Digital_Downloads_Discounts extends WP_UnitTestCase {
	public function setUp() {
		parent::setUp();
	}

	public function tearDown() {
		parent::tearDown();
	}

	public function testAdditionOfDiscount() {
		$meta = array(
			'name' => 'Test Discount',
			'type' => 'percentage',
			'amount' => '20',
			'code' => '20OFF',
			'product_condition' => 'all'
		);

		$post = array(
			'post_type' => 'edd_discount',
			'post_title' => $meta['name'],
			'post_status' => 'active'
		);

		$id = wp_insert_post($post);

		$this->assertTrue(is_numeric($id));
		$this->assertTrue($id > 0);

		foreach($meta as $key => $value) {
			update_post_meta($id, '_edd_discount_' . $key, $value);
		}

		$out = get_post($id);
		$this->assertEquals($post['post_title'], $out->post_title);
		$this->assertEquals($post['post_status'], $out->post_status);

		$out = get_post_meta($id);
		$this->assertEquals($meta['name'], $out['_edd_discount_name'][0]);
		$this->assertEquals($meta['type'], $out['_edd_discount_type'][0]);
		$this->assertEquals($meta['amount'], $out['_edd_discount_amount'][0]);
		$this->assertEquals($meta['code'], $out['_edd_discount_code'][0]);
		$this->assertEquals($meta['product_condition'], $out['_edd_discount_product_condition'][0]);

		return $id;
	}

	/**
     * @depends testAdditionOfDiscount
     */
	public function testDeletionofDiscount( $discount_id ) {
		wp_delete_post( $discount_id, true );
		$this->assertFalse( wp_cache_get( $discount_id, 'posts' ) );
	}
}