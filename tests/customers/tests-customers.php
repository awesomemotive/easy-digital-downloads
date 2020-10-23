<?php
namespace EDD\Customers;

/**
 * Customers Tests.
 *
 * @group edd_customers
 */
class Tests_Customers extends \EDD_UnitTestCase {

	/**
	 * Customers fixture.
	 *
	 * @access protected
	 * @var array
	 */
	protected static $customers;

	/**
	 * Order fixture.
	 *
	 * @access protected
	 * @var int
	 */
	protected static $order;

	/**
	 * User fixture.
	 *
	 * @access protected
	 * @var int
	 */
	protected static $user;

	/**
	 * Set up fixtures once.
	 */
	public static function wpSetUpBeforeClass() {
		$customers = parent::edd()->customer->create_many( 5 );

		foreach ( $customers as $customer ) {
			self::$customers[] = edd_get_customer( $customer );
		}

		self::$user  = 1;
		self::$order = \EDD_Helper_Payment::create_simple_payment();

		edd_update_customer( self::$customers[0], array(
			'user_id' => self::$user,
		) );

		self::$customers[0]->attach_payment( self::$order );
		self::$customers[0] = edd_get_customer( $customers[0] );

		edd_update_payment_status( self::$order, 'complete' );
	}

	public function test_create_customer_from_EDD_Customer_should_be_greater_than_0() {
		$test_email = 'testaccount@domain.com';

		$customer = new \EDD_Customer( $test_email );
		$this->assertEquals( 0, $customer->id );

		$customer_id = $customer->create( array( 'email' => 'testaccount@domain.com' ) );

		$this->assertGreaterThan( 0, $customer->id );
		$this->assertSame( $customer_id, $customer->id );
		$this->assertSame( $test_email, $customer->email );
	}

	public function test_update_customer_from_EDD_Customer_should_be_true() {
		$data = array(
			'email' => 'testaccountupdated@domain.com',
			'name'  => 'Test Account',
		);

		self::$customers[1]->update( $data );

		$this->assertSame( $data['email'], self::$customers[1]->email );
		$this->assertSame( $data['name'], self::$customers[1]->name );
	}

	public function test_update_customer_from_EDD_Customer_with_no_data_should_return_false() {
		$this->assertFalse( self::$customers[0]->update() );
	}

	public function test_attach_payment_to_customer_should_return_true() {
		$order_ids = array_map( 'absint', explode( ',', self::$customers[0]->payment_ids ) );

		$this->assertTrue( in_array( self::$order, $order_ids ) );
	}

	public function test_attach_payment_with_invalid_data_should_return_false() {
		$this->assertFalse( self::$customers[0]->attach_payment() );
	}

	public function test_attach_payment_twice_should_return_true() {
		self::$customers[2]->attach_payment( self::$order );

		$expected_purchase_count = self::$customers[2]->purchase_count;
		$expected_purchase_value = self::$customers[2]->purchase_value;

		self::$customers[2]->attach_payment( self::$order );

		$this->assertSame( $expected_purchase_count, self::$customers[2]->purchase_count );
		$this->assertSame( $expected_purchase_value, self::$customers[2]->purchase_value );
	}

	public function test_remove_payment_should_return_true() {
		self::$customers[2]->attach_payment( self::$order );
		self::$customers[2]->remove_payment( self::$order );

		$order_ids = array_map( 'absint', explode( ',', self::$customers[2]->payment_ids ) );

		$this->assertFalse( in_array( self::$order, $order_ids ) );
	}

	/**
	 * @expectEDDeprecated EDD_Customer::increase_value
	 */
	public function test_increase_value_should_return_10() {
		self::$customers[3]->increase_value( 10 );

		$this->assertSame( 10.0, self::$customers[3]->purchase_value );
	}

	/**
	 * @expectEDDeprecated EDD_Customer::increase_purchase_count
	 */
	public function test_increase_purchase_count_should_return_1() {
		self::$customers[3]->increase_purchase_count();

		$this->assertSame( 1, self::$customers[3]->purchase_count );
	}

	/**
	 * @expectEDDeprecated EDD_Customer::increase_value
	 * @expectEDDeprecated EDD_Customer::decrease_value
	 */
	public function test_decrease_value_should_return_90() {
		self::$customers[4]->increase_value( 100 );
		self::$customers[4]->decrease_value( 10 );

		$this->assertSame( 90.0, self::$customers[4]->purchase_value );
	}

	/**
	 * @expectEDDeprecated EDD_Customer::increase_purchase_count
	 * @expectEDDeprecated EDD_Customer::decrease_purchase_count
	 */
	public function test_decrease_purchase_count_should_return_0() {
		self::$customers[3]->increase_purchase_count();
		self::$customers[3]->decrease_purchase_count();

		$this->assertSame( 0, self::$customers[1]->purchase_count );
	}

	public function test_add_customer_note_should_return_1() {
		self::$customers[0]->add_note( 'Test Note' );
		$this->assertSame( 1, self::$customers[0]->get_notes_count() );
	}

	public function test_paged_notes_should_return_1() {
		self::$customers[0]->add_note( 'Test Note 1' );
		self::$customers[0]->add_note( 'Test Note 2' );

		$this->assertCount( 1, self::$customers[0]->get_notes( 1, 2 ) );
	}

	public function test_get_payment_ids_of_customer_should_return_1() {
		self::$customers[0]->attach_payment( self::$order );

		$this->assertCount( 1, self::$customers[0]->get_payment_ids() );
	}

	public function test_get_payment_ids_of_customer_with_no_payments_should_return_0() {
		$this->assertCount( 0, parent::edd()->customer->create_and_get()->get_payment_ids() );
	}

	public function test_add_email_should_return_true() {
		$this->assertTrue( self::$customers[1]->add_email( 'added-email@edd.test' ) );

		/** @var $customer \EDD_Customer */
		$customer = edd_get_customer( self::$customers[1]->id );

		$this->assertTrue( in_array( 'added-email@edd.test', $customer->emails ) );
	}

	public function test_add_email_with_primary_parameter_should_return_true() {
		$this->assertTrue( self::$customers[2]->add_email( 'added-email2@edd.test', true ) );
		$this->assertSame( 'added-email2@edd.test', self::$customers[2]->email );
	}

	public function test_remove_email_should_return_false() {
		self::$customers[1]->add_email( 'added-email@edd.test' );

		$this->assertTrue( self::$customers[1]->remove_email( 'added-email@edd.test' ) );
		$this->assertFalse( in_array( 'added-email@edd.test', self::$customers[1]->emails, true ) );
	}

	public function test_validate_username_should_return_true() {
		$this->assertTrue( edd_validate_username( 'easydigitaldownloads' ) );
	}

	public function test_validate_username_with_invalid_characters_should_return_false() {
		$this->assertFalse( edd_validate_username( 'edd12345$%&+-!@£%^&()(*&^%$£@!' ) );
	}

	public function test_get_users_purchases_should_return_1() {
		$this->assertCount( 1, edd_get_users_purchases( self::$user ) );
	}

	public function test_get_users_purchases_with_invalid_user_id_should_return_false() {
		$this->assertFalse( edd_get_users_purchases( 0 ) );
	}

	public function test_user_has_purchases_should_return_true() {
		self::$customers[0]->attach_payment( self::$order );

		$this->assertTrue( edd_has_purchases( self::$user ) );
	}

	public function test_count_purchases_of_user_should_return_1() {
		$this->assertEquals( 1, edd_count_purchases_of_customer( self::$user ) );
	}

	public function test_count_purchases_of_user_with_no_args_should_return_0() {
		$this->assertEquals( 0, edd_count_purchases_of_customer() );
	}

	public function test_users_purchased_product_pending_should_be_false() {
		$this->assertFalse( edd_get_users_purchased_products( self::$user, 'pending' ) );
	}

	public function test_user_has_purchased_with_invalid_user_and_download_id_should_return_false() {
		$this->assertFalse( edd_has_user_purchased( 0, 888 ) );
	}

	public function test_user_has_purchased_with_valid_user_and_download_id_should_return_true() {
		$this->assertTrue( edd_has_user_purchased( self::$user, edd_get_payment( self::$order )->downloads[0]['id'] ) );
	}

	public function test_user_has_purchased_with_valid_user_and_invalid_download_id_should_return_false() {
		$this->assertFalse( edd_has_user_purchased( self::$user, 99999 ) );
	}

	public function test_edd_add_past_purchases_to_new_user() {
		$order_id = \EDD_Helper_Payment::create_simple_guest_payment();

		$userdata = array(
			'user_login' => 'guest',
			'user_email' => 'guest@example.org',
			'user_pass'  => 'guest_pass'
		);
		$user_id = wp_insert_user( $userdata );

		$orders = edd_get_payments( array( 's' => $userdata['user_email'], 'output' => 'payments' ) );
		$order = $orders[0];

		$this->assertSame( $order->ID, $order_id );
	}

	public function test_user_verification_base_url() {
		$original_purchase_history_page = edd_get_option( 'purchase_history_page', 0 );
		$purchase_history_page = get_permalink( $original_purchase_history_page );
		$this->assertEquals( $purchase_history_page, edd_get_user_verification_page() );

		edd_update_option( 'purchase_history_page', 0 );
		$home_url = home_url();
		$this->assertEquals( $home_url, edd_get_user_verification_page() );

		edd_update_option( 'purchase_history_page', $original_purchase_history_page );
	}

	public function test_set_user_to_verified_with_no_user_id_should_return_false() {
		$this->assertFalse( edd_set_user_to_verified() );
	}

	public function test_set_user_to_pending_with_no_user_id_should_return_false() {
		$this->assertFalse( edd_set_user_to_pending() );
	}

	public function test_set_active_user_to_verified_should_return_false() {
		$this->assertFalse( edd_set_user_to_verified( 1 ) );
	}

	public function test_active_user_is_pending_should_return_false() {
		$this->assertFalse( edd_user_pending_verification( 1 ) );
	}

	public function test_set_user_to_pending_should_return_true() {
		$this->assertTrue( edd_set_user_to_pending( 1 ) );
		$this->assertEquals( '1', get_user_meta( 1, '_edd_pending_verification', true ) );
		$this->assertTrue( edd_user_pending_verification( 1 ) );
	}

	public function test_set_user_to_verified_should_return_true() {
		edd_set_user_to_pending( 1 );

		$this->assertTrue( edd_set_user_to_verified( 1 ) );
		$this->assertEmpty( get_user_meta( 1, '_edd_pending_verification', true ) );
		$this->assertFalse( edd_user_pending_verification( 1 ) );
	}

	public function test_get_user_verification_url_with_no_id_should_return_false() {
		$this->assertFalse( edd_get_user_verification_url() );
	}

	public function test_get_user_verification_url_should_return_true() {
		$url = edd_get_user_verification_url( 1 );

		$this->assertContains( 'edd_action=verify_user', $url );
		$this->assertContains( 'user_id=1', $url );
		$this->assertContains( 'ttl', $url );
		$this->assertContains( 'token', $url );
	}

	public function test_get_user_verification_request_url_should_return_true() {
		$url = edd_get_user_verification_request_url( 1 );

		$this->assertContains( 'edd_action=send_verification_email', $url );
	}

	public function test_validate_user_verification_token_with_valid_url_should_true() {
		$url = edd_get_user_verification_url( 1 );

		$this->assertTrue( edd_validate_user_verification_token( $url ) );
	}

	public function test_validate_user_verification_token_with_invalid_url_should_false() {
		$url = edd_get_user_verification_url( 1 );

		$this->assertFalse( edd_validate_user_verification_token( substr( $url, -1 ) ) );
		$this->assertFalse( edd_validate_user_verification_token( remove_query_arg( 'token', $url ) ) );
	}

	public function test_get_purchase_total_of_user_should_return_120() {
		self::$customers[0]->attach_payment( self::$order );

		$purchase_total = edd_purchase_total_of_user( self::$user );

		$this->assertSame( '120.00', $purchase_total );
	}

	public function test_get_payment_ids_with_invalid_customer_should_be_empty() {
		$customer_id  = edd_add_customer( array(
			'email' => 'test_user@example.com'
		) );
		$customer = new \EDD_Customer( $customer_id );

		$this->assertEmpty( $customer->get_payment_ids() );
	}

	public function test_get_payments_with_invalid_customer_should_be_empty() {
		$customer = new \EDD_Customer( 'test_user@example.com' );

		$this->assertEmpty( $customer->get_payments() );
	}

	public function test_get_users_purchased_products_should_return_2() {
		$this->assertCount( 2, (array) edd_get_users_purchased_products( self::$user ) );
	}

	public function test_get_purchase_stats_by_user_should_return_true() {
		$stats = edd_get_purchase_stats_by_user( self::$user );

		$this->assertSame( 1, $stats['purchases'] );
		$this->assertSame( '120.00', $stats['total_spent'] );
	}

	public function test_get_customer_payment_ids_should_return_1() {
		$this->assertCount( 1, self::$customers[0]->get_payment_ids() );
	}

	public function test_get_customer_payments_should_return_1() {
		$this->assertCount( 1, self::$customers[0]->get_payments() );
	}

	public function test_get_customer_pending_payments_should_be_empty() {
		$this->assertEmpty( self::$customers[0]->get_payments( 'pending' ) );
	}

	public function test_customer_and_user_order_lookup_success() {
		self::$customers[0]->attach_payment( self::$order );

		$customers_orders = edd_get_orders( array( 'customer_id' => self::$customers[0]->id, 'number' => 9999 ) );
		$users_orders     = edd_get_orders( array( 'user_id' => self::$customers[0]->user_id, 'number' => 9999 ) );

		$this->assertEquals( $customers_orders, $users_orders );
	}

	public function test_customer_and_user_order_lookup_success_after_user_id_change() {
		self::$customers[0]->attach_payment( self::$order );

		self::$customers[0]->update( array( 'user_id' => 2 ) );
		$this->assertSame( '2', self::$customers[0]->user_id );

		$customers_orders = edd_get_orders( array( 'customer_id' => self::$customers[0]->id, 'number' => 9999 ) );
		$users_orders     = edd_get_orders( array( 'user_id' => self::$customers[0]->user_id, 'number' => 9999 ) );

		$this->assertEquals( $customers_orders, $users_orders );
	}
}
