<?php
namespace EDD_Unit_Tests;
use \EDD_Email_Template_Tags;

/**
 * @group edd_emails
 */
class Tests_Emails extends EDD_UnitTestCase {

	protected $_tags;

	public function setUp() {
		parent::setUp();
		$this->_tags = new EDD_Email_Template_Tags;
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

	public function test_template_tags() {
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
			'first_name' => 'Network',
			'last_name' => 'Administrator',
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

		$prices = get_post_meta( $download_details[0]['id'], 'edd_variable_prices', true );
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
			'key' => strtolower( md5( uniqid() ) ),
			'user_email' => $user_info['email'],
			'user_info' => $user_info,
			'currency' => 'USD',
			'downloads' => $download_details,
			'cart_details' => $cart_details,
			'status' => 'pending',
			'email' => 'admin@example.org',
			'amount' => number_format( (float) $total, 2 ),
		);

		$_SERVER['REMOTE_ADDR'] = '10.0.0.0';
		$_SERVER['SERVER_NAME'] = 'edd_virtual';

		$payment_id = edd_insert_payment( $purchase_data );

		$message = <<<DATA
Hey {fullname},

{download_list}

{file_urls}

{date}

{sitename}

{price}

{receipt_id}

{receipt_link}
DATA;

		$this->assertContains( 'Hey Network Administrator', edd_email_template_tags( $message, $purchase_data, $payment_id ) );
		$this->assertContains( '<ul><li>Test Download&nbsp;&ndash;&nbsp;Advanced<br/><ul><li>', edd_email_template_tags( $message, $purchase_data, $payment_id ) );
		$this->markTestIncomplete('This needs to be rewritten');
		//$this->assertContains( 'File 1</a></li><li>', edd_email_template_tags( $message, $purchase_data, $payment_id ) );
		$this->assertContains( 'File 2</a></li></ul> &mdash; <small>Purchase Notes</small></li></ul>', edd_email_template_tags( $message, $purchase_data, $payment_id ) );
		$this->assertContains( 'http://example.org', edd_email_template_tags( $message, $purchase_data, $payment_id ) );
		$this->assertContains( 'Test Blog', edd_email_template_tags( $message, $purchase_data, $payment_id ) );
		$this->assertContains( '&#36;100.00', edd_email_template_tags( $message, $purchase_data, $payment_id ) );
		$this->assertContains( '&edd_action=view_receipt">View it in your browser.</a>', edd_email_template_tags( $message, $purchase_data, $payment_id ) );
	}

	public function test_email_preview_template_tags() {
		$message = <<<DATA
Hey {fullname},

{download_list}

{file_urls}

{date}

{sitename}

{price}

{receipt_id}

{receipt_link}
DATA;

		$this->assertContains( '<p>Hey John Doe,</p>', edd_email_preview_template_tags( $message ) );
		$this->assertContains( '<li>Sample Product Title', edd_email_preview_template_tags( $message ) );
		$this->assertContains( '<li><a href="#">Sample Download File Name</a> - <small>Optional notes about this download.</small></li>', edd_email_preview_template_tags( $message ) );
		$this->assertContains( '<p>http://example.org/test.zip?test=key&amp;key=123</p>', edd_email_preview_template_tags( $message ) );
		$this->assertContains( '<p>Test Blog</p>', edd_email_preview_template_tags( $message ) );
		$this->assertContains( '<p>&#36;10.50</p>', edd_email_preview_template_tags( $message ) );
		$this->assertContains( '&edd_action=view_receipt">View it in your browser.</a></p>', edd_email_preview_template_tags( $message ) );
	}

	public function test_email_default_formatting() {
		$message = <<<DATA
Hey {fullname},

{download_list}

{file_urls}

{date}

{sitename}

{price}

{receipt_id}

{receipt_link}
DATA;

	$expected = <<<DATA
<p>Hey {fullname},</p>
<p>{download_list}</p>
<p>{file_urls}</p>
<p>{date}</p>
<p>{sitename}</p>
<p>{price}</p>
<p>{receipt_id}</p>
<p>{receipt_link}</p>

DATA;

		$this->assertEquals( $expected, edd_email_default_formatting($message) );
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
		$this->_tags->add( 'sample_tag', 'A sample tag for the unit test', '__return_empty_array' );
		$this->assertTrue( $this->_tags->email_tag_exists( 'sample_tag' ) );
	}

	public function test_email_tags_do_valid_tag() {
		$this->assertInternalType( 'array', $this->_tags->do_tag( 'sample_tag' ) );
	}

	public function test_email_tags_remove() {
		$this->_tags->remove( 'sample_tag' );
		$this->assertFalse( $this->_tags->email_tag_exists( 'sample_tag' ) );
	}

	public function test_email_tags_do_invalid_tag() {
		$this->assertEquals( 'sample_tag', $this->_tags->do_tag( 'sample_tag' ) );
	}

}
