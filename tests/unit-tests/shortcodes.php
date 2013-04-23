<?php
namespace EDD_Unit_Tests;

/**
 * @group edd_shortcode
 */
class Tests_Shortcode extends \WP_UnitTestCase {
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
		$this->assertEquals( '<span class="edd_empty_cart">Your cart is empty.</span>', edd_checkout_form_shortcode() );
	}

	public function test_cart_shortcode() {
		$this->assertInternalType( 'string', edd_cart_shortcode( array() ) );
		$this->assertContains( '<ul class="edd-cart">', edd_cart_shortcode( array() ) );
		$this->assertContains( '<!--dynamic-cached-content-->', edd_cart_shortcode() );
		$this->assertContains( '<li class="cart_item empty"><span class="edd_empty_cart">Your cart is empty.</span></li>', edd_cart_shortcode( array() ) );
		$this->assertContains( '<li class="cart_item edd_checkout" style="display:none;"><a href="">Checkout</a></li>', edd_cart_shortcode( array() ) );
		$this->assertContains( '<!--/dynamic-cached-content-->', edd_cart_shortcode( array() ) );
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
			'product_condition' => 'any',
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
		$this->assertEquals( '<a href="?edd_action=purchase_collection&taxonomy&terms">Purchase All Items</a>', edd_purchase_collection_shortcode() );
	}

	public function test_downloads_query() {
		$post_id = $this->factory->post->create( array( 'post_type' => 'download', 'post_status' => 'publish' ) );
		$this->assertInternalType( 'string', edd_downloads_query() );
		$this->assertContains( '<div class="edd_downloads_list">', edd_downloads_query() );
		$this->assertContains( '<div itemscope itemtype="http://schema.org/Product" class="edd_download" id="edd_download_'. $post_id .'" style="width: 33%; float: left;">', edd_downloads_query() );
		$this->assertContains( '<div class="edd_download_inner">', edd_downloads_query() );
		$this->assertContains( '<h3 itemprop="name" class="edd_download_title">', edd_downloads_query() );
		$this->assertContains( '<a itemprop="url"', edd_downloads_query() );
		$this->assertContains( '</h3>', edd_downloads_query() );
		$this->assertContains( '<div itemprop="description" class="edd_download_excerpt">', edd_downloads_query() );
		$this->assertContains( '<p>Post excerpt 1</p>', edd_downloads_query() );
		$this->assertContains( '<div class="edd_download_buy_button">', edd_downloads_query() );
		$this->assertContains( '<form id="edd_purchase_'. $post_id .'" class="edd_download_purchase_form" method="post">', edd_downloads_query() );
		$this->assertContains( '<div class="edd_purchase_submit_wrapper">', edd_downloads_query() );
		$this->assertContains( '<input type="submit" class="edd-add-to-cart button blue edd-submit" name="edd_purchase_download" value="&#036;0.00&nbsp;&ndash;&nbsp;Purchase" data-action="edd_add_to_cart" data-download-id="'. $post_id .'" data-variable-price=no data-price-mode=single />', edd_downloads_query() );
		$this->assertContains( '<a href="" class="edd_go_to_checkout button blue edd-submit" style="display:none;">Checkout</a>', edd_downloads_query() );
		$this->assertContains( '<span class="edd-cart-ajax-alert">', edd_downloads_query() );
		$this->assertContains( '<img alt="Loading"', edd_downloads_query() );
		$this->assertContains( '<span class="edd-cart-added-alert" style="display: none;">&mdash;', edd_downloads_query() );
		$this->assertContains( 'Item successfully added to your <a href="" title="Go to Checkout">cart</a>.', edd_downloads_query() );
		$this->assertContains( '</span>', edd_downloads_query() );
		$this->assertContains( '</div><!--end .edd_purchase_submit_wrapper-->', edd_downloads_query() );
		$this->assertContains( '<input type="hidden" name="download_id" value="'. $post_id .'">', edd_downloads_query() );
		$this->assertContains( '<input type="hidden" name="edd_action" value="add_to_cart">', edd_downloads_query() );
		$this->assertContains( '</form><!--end #edd_purchase_'. $post_id .'-->', edd_downloads_query() );
		$this->assertContains( '<div style="clear:both;"></div>', edd_downloads_query() );
		$this->assertContains( '<div id="edd_download_pagination" class="navigation">', edd_downloads_query() );
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
		$this->assertInternalType( 'string', edd_receipt_shortcode() );
		$this->assertEquals( 'Sorry, trouble retrieving payment receipt.', edd_receipt_shortcode() );

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
		$user = get_userdata( 1 );

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
			'status' => 'pending'
		);

		$_SERVER['REMOTE_ADDR'] = '10.0.0.0';
		$_SERVER['SERVER_NAME'] = 'edd_virtual';

		$payment_id = edd_insert_payment( $purchase_data );
		$meta = edd_get_payment_meta( $payment_id );

		$_GET = array( 'purchase_key' => $purchase_data['purchase_key'] );
		wp_set_current_user( 2 );
		
		$this->assertContains( '<table id="edd_purchase_receipt">', edd_receipt_shortcode() );
		$this->assertContains( '<th><strong>Payment:</strong></th>', edd_receipt_shortcode() );
		$this->assertContains( '<th>#'. $payment_id .'</th>', edd_receipt_shortcode() );
		$this->assertContains( '<td><strong>Date:</strong></td>', edd_receipt_shortcode() );
		$this->assertContains( '<td>'. date_i18n( get_option( 'date_format' ), strtotime( $meta['date'] ) ) .'</td>', edd_receipt_shortcode() );
		$this->assertContains( '<td><strong>Subtotal</strong></td>', edd_receipt_shortcode() );
		$this->assertContains( '&#36;100.00', edd_receipt_shortcode() );
		$this->assertContains( '<td><strong>Total Price:</strong></td>', edd_receipt_shortcode() );
		$this->assertContains( '<td><strong>Payment Method:</strong></td>', edd_receipt_shortcode() );
		$this->assertContains( '<td><strong>Payment Key:</strong></td>', edd_receipt_shortcode() );
		$this->assertContains( '<td>'. get_post_meta( $payment_id, '_edd_payment_purchase_key', true ) .'</td>', edd_receipt_shortcode() );
		$this->assertContains( '<h3>Products</h3>', edd_receipt_shortcode() );
		$this->assertContains( '<table id="edd_purchase_receipt_products">', edd_receipt_shortcode() );
		$this->assertContains( '<th>Name</th>', edd_receipt_shortcode() );
		$this->assertContains( '<th>Price</th>', edd_receipt_shortcode() );
		$this->assertContains( '<div class="edd_purchase_receipt_product_name">', edd_receipt_shortcode() );
		$this->assertContains( 'Test Download', edd_receipt_shortcode() );
		$this->assertContains( '<span class="edd_purchase_receipt_price_name">&nbsp;&ndash;&nbsp;Advanced</span>', edd_receipt_shortcode() );
		$this->assertContains( '<div class="edd_purchase_receipt_product_notes">Purchase Notes</div>', edd_receipt_shortcode() );
		$this->assertContains( '<td><strong>Total Price:</strong></td>', edd_receipt_shortcode() );
		$this->assertContains( '<tfoot>', edd_receipt_shortcode() );
		wp_set_current_user( 1 );
	}
}