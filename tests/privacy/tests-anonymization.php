<?php
namespace EDD\Tests\Privacy;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;
use \EDD\Database\Queries\LogEmail;

class Anonymization extends EDD_UnitTestCase {

	/**
	 * @var \EDD\Tests\Factory\Customer
	 */
	protected static $customer;

	/**
	 * @var \EDD\Tests\Factory\Order[]
	 */
	protected static $orders;

	/**
	 * @var \EDD\Tests\Factory\Customer_Email_Address[]
	 */
	protected static $emails;

	/**
	 * @var \EDD\Tests\Factory\Customer_Address[]
	 */
	protected static $addresses;

	public function setUp(): void {
		self::$customer  = parent::edd()->customer->create_and_get();
		self::$orders    = parent::edd()->order->create_many( 3, array( 'customer_id' => self::$customer->id ) );
		self::$emails    = parent::edd()->customer_email_address->create_many( 3, array( 'customer_id' => self::$customer->id, 'type' => 'secondary' ) );
		self::$addresses = parent::edd()->customer_address->create_many( 3, array( 'customer_id' => self::$customer->id ) );
	}

	public function tearDown(): void {
		parent::edd()->customer->delete( self::$customer->id );
		parent::edd()->order->delete_many( self::$orders );
		parent::edd()->customer_email_address->delete_many( self::$emails );
		parent::edd()->customer_address->delete_many( self::$addresses );
	}

	public function test_customer_anonymization_success() {
		$anonymized = _edd_anonymize_customer( self::$customer->id );

		$this->assertTrue( $anonymized['success'] );
	}

	public function test_customer_anonymization_email_not_empty() {
		$anonymized          = _edd_anonymize_customer( self::$customer->id );
		$anonymized_customer = edd_get_customer( self::$customer->id );

		$this->assertNotEmpty( $anonymized_customer->email );
	}

	public function test_customer_anonymization_email_not_equals_original() {
		$anonymized          = _edd_anonymize_customer( self::$customer->id );
		$anonymized_customer = edd_get_customer( self::$customer->id );
		$this->assertNotEquals( self::$customer->email, $anonymized_customer->email );
	}

	public function test_customer_anonymization_order_count_is_3() {
		$anonymized = _edd_anonymize_customer( self::$customer->id );

		$this->assertEquals( 3, edd_count_orders( array( 'customer_id' => self::$customer->id ) ) );
	}

	public function test_customer_anonymization_email_addresses_count_0() {
		$anonymized = _edd_anonymize_customer( self::$customer->id );

		$this->assertEquals( 0, edd_count_customer_email_addresses( array( 'customer_id' => self::$customer->id ) ) );
	}

	public function test_customer_anonymization_addresses_count_0() {
		$anonymized = _edd_anonymize_customer( self::$customer->id );

		$this->assertEquals( 0, edd_count_customer_addresses( array( 'customer_id' => self::$customer->id ) ) );
	}

	public function test_customer_anonymization_status_is_invalid() {
		$anonymized          = _edd_anonymize_customer( self::$customer->id );
		$anonymized_customer = edd_get_customer( self::$customer->id );

		$this->assertEquals( 'disabled', $anonymized_customer->status );
	}

	public function test_customer_anonymization_deletes_email_logs() {
		$query            = new LogEmail();
		$random_email_key = array_rand( self::$emails );
		$random_email     = edd_get_customer_email_address( self::$emails[ $random_email_key ] );
		parent::edd()->email_logs->create_many( 3, array( 'email' => $random_email->email ) );

		// Ensure that the email logs exist.
		$this->assertEquals( 3, $query->query( array( 'email' => $random_email->email, 'count' => true ) ) );
		$anonymized = _edd_anonymize_customer( self::$customer->id );

		// Ensure that the email logs have been deleted.
		$this->assertEquals( 0, $query->query( array( 'email' => $random_email->email, 'count' => true ) ) );
	}
}
