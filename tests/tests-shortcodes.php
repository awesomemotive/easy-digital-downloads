<?php
namespace EDD\Tests;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;
use EDD\Tests\Helpers;

/**
 * @group edd_shortcode
 */
class Tests_Shortcode extends EDD_UnitTestCase {

	protected static $payment_key;

	protected static $user_id;

	/**
	 * Set up fixtures once.
	 * @expectedDeprecated edd_trigger_purchase_receipt
	 * @expectedDeprecated edd_admin_email_notice
	 */
	public static function wpSetUpBeforeClass() {
		self::$user_id = self::factory()->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( self::$user_id );

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

		$post = get_post( $post_id );

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
				'id' => $post->ID,
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
				'id' => $post->ID,
				'item_number' => array(
					'id' => $post->ID,
					'options' => array(
						'price_id' => 1
					)
				),
				'price' =>  100,
				'item_price' => 100,
				'tax' => 0,
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
		$_SERVER['SERVER_NAME'] = 'edd-virtual.local';

		remove_action( 'edd_complete_purchase', 'edd_trigger_purchase_receipt', 999, 3 );

		$payment_id = edd_insert_payment( $purchase_data );

		add_action( 'edd_complete_purchase', 'edd_trigger_purchase_receipt', 999, 3 );

		edd_update_order( $payment_id, array(
			'user_id' => $user->ID
		) );

		self::$payment_key = $purchase_data['purchase_key'];

		// Remove the account pending filter to only show once in a thread
		remove_filter( 'edd_allow_template_part_account_pending', 'edd_load_verification_template_once', 10, 1 );
	}

	public function setup(): void {
		parent::setUp();

		wp_set_current_user( self::$user_id );

		// Remove the account pending filter to only show once in a thread
		remove_filter( 'edd_allow_template_part_account_pending', 'edd_load_verification_template_once', 10, 1 );
	}

	public static function tearDownAfterClass(): void {
		parent::tearDownAfterClass();
		unset( $_SERVER['REMOTE_ADDR'] );
		unset( $_SERVER['SERVER_NAME'] );
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
		$actual = edd_download_history();

		$this->assertIsString( $actual );
		$this->assertStringContainsString( '<p class="edd-no-downloads">', $actual );

		edd_set_user_to_pending( self::$user_id );

		$this->assertStringContainsString( '<p class="edd-account-pending">', edd_download_history() );
	}

	public function test_purchase_history() {
		$actual = edd_purchase_history();

		$this->assertIsString( $actual );
		$this->assertStringContainsString( '<p class="edd-no-purchases">', $actual );

		edd_set_user_to_pending( self::$user_id );

		$this->assertStringContainsString( '<p class="edd-account-pending">', edd_purchase_history() );
	}

	public function test_checkout_form_shortcode() {
		$actual = edd_checkout_form_shortcode( array() );

		$this->assertIsString( $actual );
		$this->assertStringContainsString( '<div id="edd_checkout_wrap">', $actual );
	}

	public function test_cart_shortcode() {
		$actual = edd_cart_shortcode( array() );

		$this->assertIsString( $actual );
		$this->assertStringContainsString( '<ul class="edd-cart">', $actual );
	}

	public function test_login_form() {
		$purchase_history_page = edd_get_option( 'purchase_history_page' );

		$actual = edd_login_form_shortcode( array() );

		$this->assertIsString( $actual );
		$this->assertStringContainsString( '<p class="edd-logged-in">You are already logged in</p>', $actual );

		// Log out the user so we can see the login form
		wp_set_current_user( 0 );

		$args = array(
			'redirect' => get_option( 'site_url' ),
		);

		$login_form = edd_login_form_shortcode( $args );
		$this->assertIsString( $login_form );
		$this->assertStringContainsString( '"' . get_option( 'site_url' ) . '"', $login_form );

		edd_update_option( 'login_redirect_page', $purchase_history_page );

		$login_form = edd_login_form_shortcode( array() );
		$this->assertIsString( $login_form );
		$this->assertStringContainsString( '"' . get_permalink( $purchase_history_page ) . '"', $login_form );
	}

	public function test_purchase_collection_shortcode() {
		$this->go_to( '/' );

		$actual = edd_purchase_collection_shortcode( array() );

		$this->assertIsString( $actual );
		$this->assertEquals( '<a href="/?edd_action=purchase_collection&#038;taxonomy&#038;terms" class="button blue edd-submit">Purchase All Items</a>', $actual );
	}

	public function test_download_price_shortcode() {
		$post_id = self::factory()->post->create( array( 'post_type' => 'download' ) );

		$meta = array(
			'edd_price' => '54.43',
		);

		foreach ( $meta as $key => $value ) {
			update_post_meta( $post_id, $key, $value );
		}

		$actual = edd_download_price_shortcode( array( 'id' => $post_id ) );

		$this->assertIsString( $actual );
		$this->assertEquals( '<span class="edd_price" id="edd_price_'. $post_id .'">&#36;54.43</span>', $actual );
	}

	public function __test_receipt_shortcode() {
		/**
		 * @internal This test fails on Travis but passes when running locally.
		 */

//		$actual = edd_receipt_shortcode( array( 'payment_key' => self::$payment_key ) );
//
//		$this->assertIsString( $actual );
//		$this->assertStringContainsString( '<table id="edd_purchase_receipt" class="edd-table">', $actual  );
	}

	public function test_profile_shortcode() {
		$actual = edd_profile_editor_shortcode( array() );

		$this->assertIsString( $actual );
		$this->assertStringContainsString( '<form id="edd_profile_editor_form" class="edd_form" action="', $actual );

		edd_set_user_to_pending( self::$user_id );

		$this->assertStringContainsString( '<p class="edd-account-pending">', edd_profile_editor_shortcode( array() ) );
	}

	public function test_profile_pending_single_load() {
		add_filter( 'edd_allow_template_part_account_pending', 'edd_load_verification_template_once', 10, 1 );
		edd_set_user_to_pending( self::$user_id );

		$actual = edd_profile_editor_shortcode( array() );

		$this->assertStringContainsString( '<p class="edd-account-pending">', $actual );

		remove_filter( 'edd_allow_template_part_account_pending', 'edd_load_verification_template_once', 10, 1 );
	}

	public function test_downloads_shortcode_pagination() {
		$output = edd_downloads_query( array() );
		$this->assertStringNotContainsString( 'id="edd_download_pagination"', $output );

		// Create a second post so we can see pagination
		self::factory()->post->create( array( 'post_title' => 'Test Download #2', 'post_type' => 'download', 'post_status' => 'publish' ) );

		$output2 = edd_downloads_query( array( 'number' => 1 ) );
		$this->assertStringContainsString( 'id="edd_download_pagination"', $output2 );

		edd_set_user_to_pending( self::$user_id );

		$this->assertStringContainsString( '<p class="edd-account-pending">', edd_download_history( array() ) );
	}

	public function test_downloads_shortcode_nopaging() {
		// Create a posts so we can see pagination
		self::factory()->post->create( array( 'post_title' => 'Test Download #2', 'post_type' => 'download', 'post_status' => 'publish' ) );
		self::factory()->post->create( array( 'post_title' => 'Test Download #3', 'post_type' => 'download', 'post_status' => 'publish' ) );
		self::factory()->post->create( array( 'post_title' => 'Test Download #4', 'post_type' => 'download', 'post_status' => 'publish' ) );

		$output2 = edd_downloads_query( array( 'number' => 1, 'pagination' => 'false' ) );
		$this->assertStringNotContainsString( 'id="edd_download_pagination"', $output2 );
	}
}
