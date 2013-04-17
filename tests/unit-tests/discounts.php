<?php

/**
 * Test Discounts
 */
class Test_Easy_Digital_Downloads_Discounts extends WP_UnitTestCase {
	protected $_post = null;

	public function setUp() {
		parent::setUp();

		$meta = array(
			'name' => '20 Percent Off',
			'type' => 'percent',
			'amount' => '20',
			'code' => '20OFF',
			'product_condition' => 'all',
			'max' => 10,
			'uses' => 54,
			'min_price' => 128
		);

		edd_store_discount( $meta );

		$this->_post->ID = edd_get_discount_id_by_code( '20OFF' );
	}

	public function tearDown() {
		parent::tearDown();
	}

	public function testAdditionOfDiscount() {
		$post = array(
			'name' => 'Test Discount',
			'type' => 'percent',
			'amount' => '20',
			'code' => '20OFF',
			'product_condition' => 'all',
			'start' => '12/12/2050 00:00:00',
			'expiration' => '12/31/2050 00:00:00'
		);

		$this->assertTrue(edd_store_discount($post));
	}

	public function testDiscountStatusUpdate() {
		$this->assertTrue(edd_update_discount_status($this->_post->ID));
	}

	public function testDiscountsExist() {
		edd_update_discount_status($this->_post->ID);
		$this->assertTrue(edd_has_active_discounts());
	}

	public function testDiscountExists() {
		$this->assertTrue(edd_discount_exists($this->_post->ID));
	}

	public function testDiscountRetrievedFromDB() {
		$this->assertObjectHasAttribute('ID', edd_get_discount($this->_post->ID));
		$this->assertObjectHasAttribute('post_title', edd_get_discount($this->_post->ID));
		$this->assertObjectHasAttribute('post_status', edd_get_discount($this->_post->ID));
		$this->assertObjectHasAttribute('post_type', edd_get_discount($this->_post->ID));
	}

	public function testGetDiscountCode() {
		$this->assertSame('20OFF', edd_get_discount_code($this->_post->ID));
	}

	public function testDiscountStartDate() {
		$this->assertSame('', edd_get_discount_start_date($this->_post->ID));
	}

	public function testDiscountExpirationDate() {
		$this->assertSame('', edd_get_discount_expiration($this->_post->ID));
	}

	public function testDiscountMaxUses() {
		$this->assertSame(10, edd_get_discount_max_uses($this->_post->ID));
	}

	public function testDiscountUses() {
		$this->assertSame(54, edd_get_discount_uses($this->_post->ID));
	}

	public function testDiscountMinPrice() {
		$this->assertSame(128.0, edd_get_discount_min_price($this->_post->ID));
	}

	public function testDiscountAmount() {
		$this->assertSame(20.0, edd_get_discount_amount($this->_post->ID));
	}

	public function testDiscountType() {
		$this->assertSame('percent', edd_get_discount_type($this->_post->ID));
	}

	public function testDiscountProductCondition() {
		$this->assertSame('all', edd_get_discount_product_condition($this->_post->ID));
	}

	public function testDiscountIsNotGlobal() {
		$this->assertFalse(edd_is_discount_not_global($this->_post->ID));
	}

	public function testDiscountIsSingleUse() {
		$this->assertFalse(edd_discount_is_single_use($this->_post->ID));
	}

	public function testDiscountIsStarted() {
		$this->assertTrue(edd_is_discount_started($this->_post->ID));
	}

	public function testDiscountIsExpired() {
		$this->assertFalse(edd_is_discount_expired($this->_post->ID));
	}

	public function testDiscountIsMaxedOut() {
		$this->assertTrue(edd_is_discount_maxed_out($this->_post->ID));
	}

	public function testDiscountIsMinMet() {
		$this->assertFalse( edd_discount_is_min_met( $this->_post->ID ) );
	}

	public function testDiscountIsUsed() {
		$this->assertFalse( edd_is_discount_used( $this->_post->ID ) );
	}

	public function testDiscountIsValidWhenPurchasing() {
		$this->assertFalse(edd_is_discount_valid($this->_post->ID));
	}

	public function testDiscountIDByCode() {
		$this->assertSame($this->_post->ID, edd_get_discount_id_by_code('20OFF'));
	}


	public function testGetDiscountedAmount() {
		$this->assertEquals(432.0, edd_get_discounted_amount('20OFF', '540'));
	}

	public function testDiscountIncreaseUsage() {
		$uses = edd_increase_discount_usage('20OFF');
		$this->assertSame(55, $uses);
	}

	public function testDiscountFormattedAmount() {
		$this->assertSame('20%', edd_format_discount_rate('percent', get_post_meta($this->_post->ID, '_edd_discount_amount', true)));
	}

	public function testSetCartDiscount() {
		$this->markTestSkipped();
	}

	public function testUnsetCartDiscount() {
		$this->markTestSkipped();
	}

	public function testDeletionOfDiscount() {
		edd_remove_discount( $this->_post->ID );
		$this->assertFalse( wp_cache_get( $this->_post->ID, 'posts' ) );
	}
}