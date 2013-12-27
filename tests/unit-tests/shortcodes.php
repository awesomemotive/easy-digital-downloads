<?php
namespace EDD_Unit_Tests;

/**
 * @group edd_shortcode
 */
class Tests_Shortcode extends EDD_UnitTestCase {

	protected $_payment_id = null;

	protected $_post = null;

	protected $_payment_key = null;

	public function setUp() {
		parent::setUp();

		$post_id = $this->factory->post->create( array( 'post_title' => 'Test Download', 'post_type' => 'download', 'post_status' => 'publish' ) );

		$_variable_pricing = array(
			array(
				'name' => 'Simple',
				'amount' => 20
			),
			array(
				'name' => 'Advanced',
				'amount' => 100
			)
		);

		$_download_files = array(
			array(
				'name' => 'File 1',
				'file' => 'http://localhost/file1.jpg',
				'condition' => 0
			),
			array(
				'name' => 'File 2',
				'file' => 'http://localhost/file2.jpg',
				'condition' => 'all'
			)
		);

		$meta = array(
			'edd_price' => '0.00',
			'_variable_pricing' => 1,
			'_edd_price_options_mode' => 'on',
			'edd_variable_prices' => array_values( $_variable_pricing ), 
			'edd_download_files' => array_values( $_download_files ),
			'_edd_download_limit' => 20,
			'_edd_hide_purchase_link' => 1,
			'edd_product_notes' => 'Purchase Notes',
			'_edd_product_type' => 'default',
			'_edd_download_earnings' => 129.43,
			'_edd_download_sales' => 59,
			'_edd_download_limit_override_1' => 1
		);
		foreach( $meta as $key => $value ) {
			update_post_meta( $post_id, $key, $value );
		}

		$this->_post = get_post( $post_id );

		/** Generate some sales */
		$user = get_userdata(1);

		$user_info = array(
			'id' => $user->ID,
			'email' => $user->user_email,
			'first_name' => $user->first_name,
			'last_name' => $user->last_name,
			'discount' => 'none'
		);

		$download_details = array(
			array(
				'id' => $this->_post->ID,
				'options' => array(
					'price_id' => 1
				)
			)
		);

		$price = '100.00';

		$total = 0;

		$prices = get_post_meta($download_details[0]['id'], 'edd_variable_prices', true);
		$item_price = $prices[1]['amount'];

		$total += $item_price;

		$cart_details = array(
			array(
				'name' => 'Test Download',
				'id' => $this->_post->ID,
				'item_number' => array(
					'id' => $this->_post->ID,
					'options' => array(
						'price_id' => 1
					)
				),
				'price' =>  100,
				'quantity' => 1
			)
		);

		$purchase_data = array(
			'price' => number_format( (float) $total, 2 ),
			'date' => date( 'Y-m-d H:i:s', strtotime( '-1 day' ) ),
			'purchase_key' => strtolower( md5( uniqid() ) ),
			'user_email' => $user_info['email'],
			'user_info' => $user_info,
			'currency' => 'USD',
			'downloads' => $download_details,
			'cart_details' => $cart_details,
			'status' => 'complete'
		);

		$_SERVER['REMOTE_ADDR'] = '10.0.0.0';
		$_SERVER['SERVER_NAME'] = 'edd_virtual';

		$this->_payment_id = edd_insert_payment( $purchase_data );

		update_post_meta( $this->_payment_id, '_edd_payment_user_id', $user->ID );

		$this->_payment_key = $purchase_data['purchase_key'];
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
		$this->assertContains( '<table id="edd_user_history">', edd_download_history( array() ) );
	}

	public function test_purchase_history() {
		$this->assertInternalType( 'string', edd_purchase_history( array() ) );
		$this->assertContains( '<table id="edd_user_history">', edd_purchase_history( array() ) );
	}

	public function test_checkout_form_shortcode() {
		$this->assertInternalType( 'string', edd_checkout_form_shortcode() );
		$this->assertContains( '<div id="edd_checkout_wrap">', edd_checkout_form_shortcode() );
	}

	public function test_cart_shortcode() {
		$this->assertInternalType( 'string', edd_cart_shortcode() );
		$this->assertContains( '<ul class="edd-cart">', edd_cart_shortcode() );
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
		$this->assertContains( '<div class="edd_downloads_list', edd_downloads_query() );
		$this->assertContains( '<div class="edd_download_inner">', edd_downloads_query() ); // edd_download_inner will only be found if products were returned successfully
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

	public function test_receipt_shortcode() {
		$this->markTestIncomplete( 'This one needs to be fixed per #600. The purchase receipt is not retrieved for some reason.' );
		$this->assertInternalType( 'string', edd_receipt_shortcode( array( 'payment_key' => $this->_payment_key ) ) );
		$this->assertContains( '<table id="edd_purchase_receipt">', edd_receipt_shortcode( array( 'payment_key' => $this->_payment_key ) ) );
	}

	public function test_profile_shortcode() {
		$this->assertInternalType( 'string', edd_profile_editor_shortcode() );
		$this->assertContains( '<form id="edd_profile_editor_form" class="edd_form" action="', edd_profile_editor_shortcode() );
	}
}
