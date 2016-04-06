<?php

/**
 * @group edd_emails
 */
class Tests_Emails extends WP_UnitTestCase {

	protected $_tags;

	protected $payment_id;

	public function setUp() {
		parent::setUp();
		$this->_tags = new EDD_Email_Template_Tags;

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

		$this->_post = get_post( $post_id );

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
				'id' => $this->_post->ID,
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
				'id'          => $this->_post->ID,
				'item_number' => array(
					'id'      => $this->_post->ID,
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
		$_SERVER['SERVER_NAME'] = 'edd_virtual';

		$this->_payment_id = edd_insert_payment( $purchase_data );

	}

	public function tearDown() {
		parent::tearDown();
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
		$this->assertContains( 'Hello', edd_get_default_sale_notification_email() );
		$this->assertContains( 'A Downloads purchase has been made', edd_get_default_sale_notification_email() );
		$this->assertContains( 'Downloads sold:', edd_get_default_sale_notification_email() );
		$this->assertContains( '{download_list}', edd_get_default_sale_notification_email() );
		$this->assertContains( 'Amount:  {price}', edd_get_default_sale_notification_email() );
	}

	public function test_email_tags_get_tags() {
		$this->assertInternalType( 'array', edd_get_email_tags() );
		$this->assertarrayHasKey( 'download_list', edd_get_email_tags() );
		$this->assertarrayHasKey( 'file_urls', edd_get_email_tags() );
		$this->assertarrayHasKey( 'name', edd_get_email_tags() );
		$this->assertarrayHasKey( 'fullname', edd_get_email_tags() );
		$this->assertarrayHasKey( 'username', edd_get_email_tags() );
		$this->assertarrayHasKey( 'user_email', edd_get_email_tags() );
		$this->assertarrayHasKey( 'date', edd_get_email_tags() );
		$this->assertarrayHasKey( 'subtotal', edd_get_email_tags() );
		$this->assertarrayHasKey( 'tax', edd_get_email_tags() );
		$this->assertarrayHasKey( 'price', edd_get_email_tags() );
		$this->assertarrayHasKey( 'payment_id', edd_get_email_tags() );
		$this->assertarrayHasKey( 'payment_method', edd_get_email_tags() );
		$this->assertarrayHasKey( 'sitename', edd_get_email_tags() );
		$this->assertarrayHasKey( 'receipt_link', edd_get_email_tags() );
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

		$this->assertContains( '<strong>' . get_the_title( $this->_post->ID ) . '</strong>', edd_email_tag_download_list( $this->_payment_id ) );
		add_filter( 'edd_email_show_names', '__return_false' );
		$this->assertNotContains( '<strong>' . get_the_title( $this->_post->ID ) . '</strong>', edd_email_tag_download_list( $this->_payment_id ) );
		remove_filter( 'edd_email_show_names', '__return_false' );

		$this->assertContains( '<div><a href="', edd_email_tag_download_list( $this->_payment_id ) );
		add_filter( 'edd_email_show_links', '__return_false' );
		$this->assertContains( '<div>File 2</div>', edd_email_tag_download_list( $this->_payment_id ) );
		remove_filter( 'edd_email_show_links', '__return_false' );

	}

	public function test_email_tags_first_name() {
		$this->assertEquals( 'Network', edd_email_tag_first_name( $this->_payment_id ) );
	}

	public function test_email_tags_fullname() {
		$this->assertEquals( 'Network Administrator', edd_email_tag_fullname( $this->_payment_id ) );
	}

	public function test_email_tags_username() {
		$this->assertEquals( 'admin', edd_email_tag_username( $this->_payment_id ) );
	}

	public function test_email_tags_email() {
		$this->assertEquals( 'admin@example.org', edd_email_tag_user_email( $this->_payment_id ) );
	}

	public function test_email_tags_date() {
		$this->assertEquals( date( 'F j, Y', strtotime( get_post_field( 'post_date', $this->_payment_id ) ) ), edd_email_tag_date( $this->_payment_id ) );
	}

	public function test_email_tags_subtotal() {
		$this->assertEquals( '$100.00', edd_email_tag_subtotal( $this->_payment_id ) );
	}

	public function test_email_tags_tax() {
		$this->assertEquals( '$0.00', edd_email_tag_tax( $this->_payment_id ) );
	}

	public function test_email_tags_price() {
		$this->assertEquals( '$100.00', edd_email_tag_price( $this->_payment_id ) );
	}

	public function test_email_tags_payment_id() {
		$this->assertEquals( $this->_payment_id, edd_email_tag_payment_id( $this->_payment_id ) );
	}

	public function test_email_tags_receipt_id() {
		$this->assertEquals( edd_get_payment_key( $this->_payment_id ), edd_email_tag_receipt_id( $this->_payment_id ) );
	}

	public function test_email_tags_payment_method() {
		$this->assertEquals( 'Free Purchase', edd_email_tag_payment_method( $this->_payment_id ) );
	}

	public function test_email_tags_site_name() {
		$this->assertEquals( get_bloginfo( 'name' ), edd_email_tag_sitename( $this->_payment_id ) );
	}

	public function test_email_tags_receipt_link() {
		$this->assertContains( 'View it in your browser &raquo;', edd_email_tag_receipt_link( $this->_payment_id ) );
	}

	public function test_get_from_name() {
		$this->assertEquals( get_bloginfo( 'name' ), EDD()->emails->get_from_name() );
	}

	public function test_get_from_address() {
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

		$emails = EDD()->emails;
		$emails->content_type = 'text/html';
		$message = $emails->text_to_html( $message, EDD()->emails );

		$this->assertEquals( $expected, $message );
	}

}
