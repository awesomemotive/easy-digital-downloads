<?php

/**
 * @group edd_emails
 */
class Tests_Emails extends EDD_UnitTestCase {

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
	 * Set up fixtures once.
	 */
	public static function wpSetUpBeforeClass() {
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

		self::$post = get_post( $post_id );

		/** Generate some sales */
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
			'status'       => 'pending',
			'gateway'      => 'manual',
			'email'        => 'admin@example.org',
			'amount'       => number_format( (float) $total, 2 ),
		);

		$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
		$_SERVER['SERVER_NAME'] = 'edd-virtual.local';

		self::$payment_id = edd_insert_payment( $purchase_data );
	}

	/**
     * Test that each of the actions are added and each hooked in with the right priority
     */
	public function test_email_actions() {
		global $wp_filter;

		$this->assertarrayHasKey( 'edd_admin_email_notice',       $wp_filter['edd_admin_sale_notice'][10]  );
		$this->assertarrayHasKey( 'edd_trigger_purchase_receipt', $wp_filter['edd_complete_purchase'][999] );
		$this->assertarrayHasKey( 'edd_resend_purchase_receipt',  $wp_filter['edd_email_links'][10]        );
		$this->assertarrayHasKey( 'edd_send_test_email',          $wp_filter['edd_send_test_email'][10]    );
	}

	public function test_admin_notice_emails() {
		$expected = array( 'admin@example.org' );

		$this->assertEquals( $expected, edd_get_admin_notice_emails() );
	}

	public function test_admin_notice_disabled() {
		$this->assertFalse( edd_admin_notices_disabled() );
	}

	public function test_email_templates() {
		$expected = array(
			'default' => 'Default Template',
			'none' => 'No template, plain text only'
		);

		$this->assertEquals( $expected, edd_get_email_templates() );
	}

	public function test_get_template() {
		$this->assertEquals( 'default', EDD()->emails->get_template() );
	}

	public function test_edd_get_default_sale_notification_email() {
		$email = edd_get_default_sale_notification_email();

		$this->assertContains( 'Hello', $email );
		$this->assertContains( 'A Downloads purchase has been made', $email );
		$this->assertContains( 'Downloads sold:', $email );
		$this->assertContains( '{download_list}', $email );
		$this->assertContains( 'Amount:  {price}', $email );
	}

	public function test_email_tags_get_tags() {
		$tags = edd_get_email_tags();

		$this->assertInternalType( 'array', $tags );
		$this->assertarrayHasKey( 'download_list', $tags );
		$this->assertarrayHasKey( 'file_urls', $tags );
		$this->assertarrayHasKey( 'name', $tags );
		$this->assertarrayHasKey( 'fullname', $tags );
		$this->assertarrayHasKey( 'username', $tags );
		$this->assertarrayHasKey( 'user_email', $tags );
		$this->assertarrayHasKey( 'date', $tags );
		$this->assertarrayHasKey( 'subtotal', $tags );
		$this->assertarrayHasKey( 'tax', $tags );
		$this->assertarrayHasKey( 'price', $tags );
		$this->assertarrayHasKey( 'payment_id', $tags );
		$this->assertarrayHasKey( 'payment_method', $tags );
		$this->assertarrayHasKey( 'sitename', $tags );
		$this->assertarrayHasKey( 'receipt_link', $tags );
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
		$this->assertContains( '<strong>' . $order_items[0]->get_order_item_name() . '</strong>', edd_email_tag_download_list( self::$payment_id ) );
		$this->assertContains( '<div><a href="', edd_email_tag_download_list( self::$payment_id ) );
	}

	public function test_email_tag_download_list_with_names_disabled_via_filter() {
		add_filter( 'edd_email_show_names', '__return_false' );
		$this->assertNotContains( '<strong>' . get_the_title( self::$post->ID ) . '</strong>', edd_email_tag_download_list( self::$payment_id ) );
		remove_filter( 'edd_email_show_names', '__return_false' );
	}

	public function test_email_tag_download_list_with_links_disabled_via_filer() {
		add_filter( 'edd_email_show_links', '__return_false' );
		$this->assertContains( '<div>File 2</div>', edd_email_tag_download_list( self::$payment_id ) );
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

	public function test_email_tags_payment_method() {
		$this->assertEquals( 'Store Gateway', edd_email_tag_payment_method( self::$payment_id ) );
	}

	public function test_email_tags_site_name() {
		$this->assertEquals( get_bloginfo( 'name' ), edd_email_tag_sitename( self::$payment_id ) );
	}

	public function test_email_tags_receipt_link() {
		$this->assertContains( 'View it in your browser &raquo;', edd_email_tag_receipt_link( self::$payment_id ) );
	}

	public function test_get_from_name() {
		$this->assertEquals( get_bloginfo( 'name' ), EDD()->emails->get_from_name() );
	}

	public function test_get_from_address() {
		$this->assertEquals( get_bloginfo( 'admin_email' ), EDD()->emails->get_from_address() );
	}

	public function test_fallback_for_invalid_from_address() {
		edd_update_option( 'from_email', 'not-an-email' );

		$this->assertEquals( get_bloginfo( 'admin_email' ), EDD()->emails->get_from_address() );
	}

	public function test_get_content_type() {
		$this->assertEquals( 'text/html', EDD()->emails->get_content_type() );

		EDD()->emails->content_type = 'text/plain';

		$this->assertEquals( 'text/plain', EDD()->emails->get_content_type() );

	}

	public function test_get_headers() {
		$from_name = EDD()->emails->get_from_name();
		$from_address = EDD()->emails->get_from_address();

		$this->assertContains( "From: {$from_name} <{$from_address}>", EDD()->emails->get_headers() );
	}

	public function test_get_heading() {
		EDD()->emails->__set( 'heading', 'Purchase Receipt' );

		$this->assertEquals( 'Purchase Receipt', EDD()->emails->get_heading() );
	}

	public function test_text_to_html() {
		$message  = "Hello, this is plain text that I am going to convert to HTML\r\n";
		$message .= "Line breaks should become BR tags.\r\n";

		$expected  = wpautop( $message );

		EDD()->emails->content_type = 'text/html';
		$message = EDD()->emails->text_to_html( $message, EDD()->emails );

		$this->assertEquals( $expected, $message );
	}
}
