<?php
namespace EDD\Tests\Emails;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;

/**
 * @group edd_emails
 */
class Actions extends EDD_UnitTestCase {

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
		self::$order      = edd_get_order( self::$payment_id );
	}

	/**
	 * Test that each of the actions are added and each hooked in with the right priority
	 */
	public function test_email_actions() {
		global $wp_filter;

		// This is a legacy filter that we need to test, simply for extensions that unhook it.
		$this->assertarrayHasKey( 'edd_admin_email_notice', $wp_filter['edd_admin_sale_notice'][10]  );

		// Verify the order receipt email is hooked.
		$hooked_into = array_keys( $wp_filter['edd_after_order_actions'][9999] );
		$has_hook    = $this->determine_if_hook_found( 'send_order_emails', $hooked_into );
		$this->assertTrue( $has_hook, 'Did not find send_order_emails hook for edd_after_order_actions' );

		// Verify the resend order receipt email is hooked.
		$hooked_into = array_keys( $wp_filter['edd_email_links'][10] );
		$has_hook    = $this->determine_if_hook_found( 'resend_order_receipt', $hooked_into );
		$this->assertTrue( $has_hook, 'Did not find resend_order_receipt hook for edd_email_links' );

		// Verify the resend order receipt email is hooked.
		$hooked_into = array_keys( $wp_filter['edd_send_test_email'][10] );
		$has_hook    = $this->determine_if_hook_found( 'send_test_email', $hooked_into );
		$this->assertTrue( $has_hook, 'Did not find edd_send_test_email hook for send_test_email' );
	}

	public function test_admin_notice_emails() {
		$expected = array( 'admin@example.org' );

		$this->assertEquals( $expected, edd_get_admin_notice_emails() );
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
		$admin_order_notice = new \EDD\Emails\Templates\AdminOrderNotice();
		$email = $admin_order_notice->get_default( 'content' );

		$this->assertStringContainsString( 'Hello', $email );
		$this->assertStringContainsString( 'A Downloads purchase has been made', $email );
		$this->assertStringContainsString( 'Downloads sold:', $email );
		$this->assertStringContainsString( '{download_list}', $email );
		$this->assertStringContainsString( 'Amount: {price}', $email );
	}

	public function test_edd_get_default_sale_notification_email_legacy() {
		$email = edd_get_default_sale_notification_email();

		$this->assertStringContainsString( 'Hello', $email );
		$this->assertStringContainsString( 'A Downloads purchase has been made', $email );
		$this->assertStringContainsString( 'Downloads sold:', $email );
		$this->assertStringContainsString( '{download_list}', $email );
		$this->assertStringContainsString( 'Amount: {price}', $email );
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

		$this->assertStringContainsString( "From: {$from_name} <{$from_address}>", EDD()->emails->get_headers() );
	}

	public function test_get_heading() {
		EDD()->emails->__set( 'heading', 'Purchase Receipt' );

		$this->assertEquals( 'Purchase Receipt', EDD()->emails->heading );
	}

	public function test_text_to_html() {
		$message  = "Hello, this is plain text that I am going to convert to HTML\r\n";
		$message .= "Line breaks should become BR tags.\r\n";

		$expected  = wpautop( $message );

		EDD()->emails->content_type = 'text/html';
		$message = EDD()->emails->text_to_html( $message, EDD()->emails );

		$this->assertEquals( $expected, $message );
	}

	private function determine_if_hook_found( $desired_hook, $hooks ) {
		$found = false;

		foreach ( $hooks as $hook ) {
			if ( false !== strpos( $hook, $desired_hook ) ) {
				$found = true;
			}
		}

		return $found;
	}
}
