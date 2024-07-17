<?php
namespace EDD\Tests\Emails;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;

class Tags extends EDD_UnitTestCase {

	/**
	 * Payment fixture.
	 *
	 * @var int
	 */
	protected static $payment_id;

	/**
	 * Download fixture.
	 *
	 * @var WP_Post
	 */
	protected static $post;

	/**
	 * Order fixture.
	 *
	 * @var \EDD\Orders\Order
	 */
	protected static $order;

	/**
	 * Set up fixtures once.
	 */
	public static function wpSetUpBeforeClass() {
		self::$post       = self::create_download();
		self::$payment_id = self::create_order();
		self::$order      = edd_get_order( self::$payment_id );

		edd_add_order_transaction(
			array(
				'object_id'      => self::$payment_id,
				'object_type'    => 'order',
				'transaction_id' => 'sample_transaction_id',
				'gateway'        => 'manual',
				'status'         => 'complete',
				'total'          => self::$order->total,
				'date_created'   => self::$order->date_created,
			)
		);
	}

	public function test_email_tags_get_tags() {
		$tags = edd_get_email_tags();

		$this->assertIsArray( $tags );
		$this->assertTrue( edd_email_tag_exists( 'download_list' ) );
		$this->assertTrue( edd_email_tag_exists( 'file_urls' ) );
		$this->assertTrue( edd_email_tag_exists( 'name' ) );
		$this->assertTrue( edd_email_tag_exists( 'fullname' ) );
		$this->assertTrue( edd_email_tag_exists( 'username' ) );
		$this->assertTrue( edd_email_tag_exists( 'user_email' ) );
		$this->assertTrue( edd_email_tag_exists( 'date' ) );
		$this->assertTrue( edd_email_tag_exists( 'subtotal' ) );
		$this->assertTrue( edd_email_tag_exists( 'tax' ) );
		$this->assertTrue( edd_email_tag_exists( 'price' ) );
		$this->assertTrue( edd_email_tag_exists( 'payment_id' ) );
		$this->assertTrue( edd_email_tag_exists( 'payment_method' ) );
		$this->assertTrue( edd_email_tag_exists( 'sitename' ) );
		$this->assertTrue( edd_email_tag_exists( 'receipt_link' ) );
		$this->assertTrue( edd_email_tag_exists( 'login_link' ) );
		$this->assertTrue( edd_email_tag_exists( 'transaction_id' ) );
		$this->assertTrue( edd_email_tag_exists( 'refund_link' ) );
		$this->assertTrue( edd_email_tag_exists( 'refund_amount' ) );
		$this->assertTrue( edd_email_tag_exists( 'refund_id' ) );
	}

	public function test_email_tags_add() {
		edd_add_email_tag( 'sample_tag', 'A sample tag for the unit test', '__return_empty_array' );

		$this->assertTrue( edd_email_tag_exists( 'sample_tag' ) );
	}

	public function test_email_tags_remove() {
		edd_remove_email_tag( 'sample_tag' );

		$this->assertFalse( edd_email_tag_exists( 'sample_tag' ) );
	}

	public function test_email_tags_download_list() {
		$order_items = edd_get_order_items( array( 'order_id' => self::$payment_id ) );
		$this->assertStringContainsString( '<strong>' . $order_items[0]->product_name . '</strong>', edd_email_tag_download_list( self::$payment_id ) );
		$this->assertStringContainsString( '<div><a href="', edd_email_tag_download_list( self::$payment_id ) );
	}

	public function test_email_tag_download_list_with_names_disabled_via_filter() {
		add_filter( 'edd_email_show_names', '__return_false' );
		$this->assertStringNotContainsString( '<strong>' . get_the_title( self::$post->ID ) . '</strong>', edd_email_tag_download_list( self::$payment_id ) );
		remove_filter( 'edd_email_show_names', '__return_false' );
	}

	public function test_email_tag_download_list_with_links_disabled_via_filer() {
		add_filter( 'edd_email_show_links', '__return_false' );
		$this->assertStringContainsString( '<div>File 2</div>', edd_email_tag_download_list( self::$payment_id ) );
		remove_filter( 'edd_email_show_links', '__return_false' );
	}

	public function test_email_tags_first_name() {
		$this->assertEquals( 'Network', edd_email_tag_first_name( self::$payment_id ) );
	}

	public function test_email_tags_fullname() {
		$this->assertEquals( 'Network Administrator', edd_email_tag_fullname( self::$payment_id ) );
	}

	public function test_email_tags_username() {
		$this->assertEquals( 'admin', edd_email_tag_username( self::$payment_id ) );
	}

	public function test_email_tags_email() {
		$this->assertEquals( 'admin@example.org', edd_email_tag_user_email( self::$payment_id ) );
	}

	public function test_email_tags_date() {
		$payment = edd_get_payment( self::$payment_id );

		$this->assertEquals( date( 'F j, Y', strtotime( $payment->date ) ), edd_email_tag_date( self::$payment_id ) );
	}

	public function test_email_tags_subtotal() {
		$this->assertEquals( '$100.00', edd_email_tag_subtotal( self::$payment_id ) );
	}

	public function test_email_tags_tax() {
		$this->assertEquals( '$0.00', edd_email_tag_tax( self::$payment_id ) );
	}

	public function test_email_tags_price() {
		$this->assertEquals( '$100.00', edd_email_tag_price( self::$payment_id ) );
	}

	public function test_email_tags_payment_id() {
		$this->assertEquals( self::$payment_id, edd_email_tag_payment_id( self::$payment_id ) );
	}

	public function test_email_tags_receipt_id() {
		$this->assertEquals( edd_get_payment_key( self::$payment_id ), edd_email_tag_receipt_id( self::$payment_id ) );
	}

	public function test_email_tags_receipt_id_with_order() {
		$this->assertEquals( edd_get_payment_key( self::$payment_id ), edd_email_tag_receipt_id( false, self::$order ) );
	}

	public function test_email_tags_payment_method() {
		$this->assertEquals( 'Store Gateway', edd_email_tag_payment_method( self::$payment_id ) );
	}

	public function test_email_tags_payment_method_with_order() {
		$this->assertEquals( 'Store Gateway', edd_email_tag_payment_method( self::$payment_id, self::$order ) );
	}

	public function test_email_tags_site_name() {
		$this->assertEquals( get_bloginfo( 'name' ), edd_email_tag_sitename( self::$payment_id ) );
	}

	public function test_email_tags_receipt_link() {
		$this->assertStringContainsString( 'View it in your browser &raquo;', edd_email_tag_receipt_link( self::$payment_id ) );
	}

	public function test_email_tags_transaction_id() {
		$render = new \EDD\Emails\Tags\Render();
		$this->assertEquals( 'sample_transaction_id', $render->transaction_id( self::$payment_id ) );
	}

	public function test_email_tags_transaction_id_not_in_array_for_customer() {
		$this->assertFalse( edd_email_tag_exists( 'transaction_id', 'order', 'customer' ) );
	}

	public function test_email_tags_refund_link_order_context_is_not_parsed() {
		$render = new \EDD\Emails\Tags\Render();

		$this->assertEquals( '{refund_link}', $render->refund_link( self::$payment_id ) );
	}

	public function test_email_tags_refund_link_refund_context_is_link() {
		$new_order = self::create_order();
		$refund_id = edd_refund_order( $new_order );
		$render    = new \EDD\Emails\Tags\Render();
		$link      = edd_get_admin_url(
			array(
				'page' => 'edd-payment-history',
				'view' => 'view-refund-details',
				'id'   => $refund_id,
			)
		);
		$refund   = edd_get_order( $refund_id );
		$expected = edd_currency_filter( edd_format_amount( $refund->total * -1 ), $refund->currency );

		$this->assertEquals( $link, $render->refund_link( $refund_id, edd_get_order( $refund_id ), 'refund' ) );
		$this->assertEquals( $expected, $render->refund_amount( $refund_id, $refund, 'refund' ) );
		$this->assertEquals( $refund->order_number, $render->refund_id( $refund_id, $refund, 'refund' ) );
		$this->assertEquals( '{refund_id}', $render->refund_id( $new_order, null, 'refund' ) );
	}

	private static function create_download() {
		$post_id = self::factory()->post->create( array( 'post_title' => 'Test Download', 'post_type' => 'download', 'post_status' => 'publish' ) );

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
				'name'      => 'File 1',
				'file'      => 'http://localhost/file1.jpg',
				'condition' => 0
			),
			array(
				'name'      => 'File 2',
				'file'      => 'http://localhost/file2.jpg',
				'condition' => 'all'
			)
		);

		$meta = array(
			'edd_price'                      => '0.00',
			'_variable_pricing'              => 1,
			'_edd_price_options_mode'        => 'on',
			'edd_variable_prices'            => array_values( $_variable_pricing ),
			'edd_download_files'             => array_values( $_download_files ),
			'_edd_download_limit'            => 20,
			'_edd_hide_purchase_link'        => 1,
			'edd_product_notes'              => 'Purchase Notes',
			'_edd_product_type'              => 'default',
			'_edd_download_earnings'         => 129.43,
			'_edd_download_sales'            => 59,
			'_edd_download_limit_override_1' => 1
		);
		foreach( $meta as $key => $value ) {
			update_post_meta( $post_id, $key, $value );
		}

		return get_post( $post_id );
	}

	private static function create_order() {
		$user = get_userdata(1);

		$user_info = array(
			'id'         => $user->ID,
			'email'      => $user->user_email,
			'first_name' => 'Network',
			'last_name'  => 'Administrator',
			'discount'   => 'none'
		);

		$download_details = array(
			array(
				'id' => self::$post->ID,
				'options' => array(
					'price_id' => 1
				)
			)
		);

		$price = '100.00';

		$total = 0;

		$prices = get_post_meta( $download_details[0]['id'], 'edd_variable_prices', true );
		$item_price = $prices[1]['amount'];

		$total += $item_price;

		$cart_details = array(
			array(
				'name'        => 'Test Download',
				'id'          => self::$post->ID,
				'item_number' => array(
					'id'      => self::$post->ID,
					'options' => array(
						'price_id' => 1
					)
				),
				'discount'   => 0,
				'subtotal'   => 100,
				'price'      => 100,
				'item_price' => 100,
				'tax'        => 0,
				'quantity'   => 1
			)
		);

		$purchase_data = array(
			'price'        => number_format( (float) $total, 2 ),
			'date'         => date( 'Y-m-d H:i:s', strtotime( '-1 day' ) ),
			'purchase_key' => strtolower( md5( uniqid() ) ),
			'key'          => strtolower( md5( uniqid() ) ),
			'user_email'   => $user_info['email'],
			'user_info'    => $user_info,
			'currency'     => 'USD',
			'downloads'    => $download_details,
			'cart_details' => $cart_details,
			'status'       => 'complete',
			'gateway'      => 'manual',
			'email'        => 'admin@example.org',
			'amount'       => number_format( (float) $total, 2 ),
		);

		$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
		$_SERVER['SERVER_NAME'] = 'edd-virtual.local';

		return edd_insert_payment( $purchase_data );
	}
}
