<?php
namespace EDD_Unit_Tests;

/**
 * @group edd_shortcode
 */
class Tests_Shortcode extends EDD_UnitTestCase {
	public function setUp() {
		parent::setUp();
	}

	public function tearDown() {
		parent::tearDown();
	}

	public function test_shortcodes_are_registered() {
		global $shortcode_tags;

		$this->assertArrayHasKey( 'purchase_link', $shortcode_tags );
		$this->assertArrayHasKey( 'download_history', $shortcode_tags );
		$this->assertArrayHasKey( 'purchase_history', $shortcode_tags );
		$this->assertArrayHasKey( 'download_checkout', $shortcode_tags );
		$this->assertArrayHasKey( 'download_cart', $shortcode_tags );
		$this->assertArrayHasKey( 'edd_login', $shortcode_tags );
		$this->assertArrayHasKey( 'download_discounts', $shortcode_tags );
		$this->assertArrayHasKey( 'purchase_collection', $shortcode_tags );
		$this->assertArrayHasKey( 'downloads', $shortcode_tags );
		$this->assertArrayHasKey( 'edd_price', $shortcode_tags );
		$this->assertArrayHasKey( 'edd_receipt', $shortcode_tags );
		$this->assertArrayHasKey( 'edd_profile_editor', $shortcode_tags );
	}

	public function test_download_history() {
		$this->assertInternalType( 'string', edd_download_history( array() ) );
		$this->assertContains( '<p class="edd-no-downloads">You have not purchased any downloads</p>', edd_download_history( array() ) );
	}

	public function test_purchase_history() {
		$this->assertInternalType( 'string', edd_purchase_history( array() ) );
		$this->assertContains( '<p class="edd-no-purchases">You have not made any purchases</p>', edd_purchase_history( array() ) );
	}

	public function test_checkout_form_shortcode() {
		$this->assertInternalType( 'string', edd_checkout_form_shortcode() );
		$this->markTestIncomplete( 'This test needs to be rewritten per #600.');
	}

	public function test_cart_shortcode() {
		$this->markTestIncomplete( 'This test needs to be rewritten per #600.');
	}

	public function test_login_form() {
		$this->assertInternalType( 'string', edd_login_form_shortcode() );
		$this->assertEquals( '<p class="edd-logged-in">You are already logged in</p>', edd_login_form_shortcode() );
	}

	public function test_discounts_shortcode() {
		$post_id = $this->factory->post->create( array( 'post_type' => 'edd_discount', 'post_status' => 'active' ) );

		$meta = array(
			'type' => 'percent',
			'amount' => '20',
			'code' => '20OFF',
			'product_condition' => 'all',
			'start' => '12/12/2000 00:00:00',
			'expiration' => '12/31/2050 23:59:59',
			'max_uses' => 10,
			'uses' => 54,
			'min_price' => 128,
			'is_not_global' => true,
			'is_single_use' => true
		);

		foreach( $meta as $key => $value ) {
			update_post_meta( $post_id, '_edd_discount_' . $key, $value );
		}

		$this->assertInternalType( 'string', edd_discounts_shortcode( array() ) );
		$this->assertEquals( '<ul id="edd_discounts_list"><li class="edd_discount"><span class="edd_discount_name">20OFF</span><span class="edd_discount_separator"> - </span><span class="edd_discount_amount">20%</span></li></ul>', edd_discounts_shortcode( array() ) );
	}

	public function test_purchase_collection_shortcode() {
		$this->assertInternalType( 'string', edd_purchase_collection_shortcode() );
		$this->assertEquals( '<a href="?edd_action=purchase_collection&taxonomy&terms" class="button blue edd-submit">Purchase All Items</a>', edd_purchase_collection_shortcode() );
	}

	public function test_downloads_query() {
		$post_id = $this->factory->post->create( array( 'post_type' => 'download', 'post_status' => 'publish' ) );
		$this->assertInternalType( 'string', edd_downloads_query() );
		$this->markTestIncomplete( 'This test needs to be rewritten per #600.');
	}

	public function test_download_price_shortcode() {
		$post_id = $this->factory->post->create( array( 'post_type' => 'download' ) );

		$meta = array(
			'edd_price' => '54.43',
		);

		foreach ( $meta as $key => $value ) {
			update_post_meta( $post_id, $key, $value );
		}

		$this->assertInternalType( 'string', edd_download_price_shortcode( array( 'id' => $post_id ) ) );
		$this->assertEquals( '<span class="edd_price" id="edd_price_'. $post_id .'">&#36;54.43</span>', edd_download_price_shortcode( array( 'id' => $post_id ) ) );
	}

	/**
	 * This test is failing for some reason. Needs further work.
	 */
	public function test_receipt_shortcode() {
		$this->markTestIncomplete( 'This test needs to be rewritten per #600.');
	}
}
