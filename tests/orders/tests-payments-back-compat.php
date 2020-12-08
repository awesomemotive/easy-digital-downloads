<?php
namespace EDD\Orders;

/**
 * Payments backwards compatibility tests.
 *
 * @group edd_back_compat
 *
 * @coversDefaultClass \EDD\Compat\Payment
 */
class Payment_Back_Compat_Tests extends \EDD_UnitTestCase {

	/**
	 * Orders fixture.
	 *
	 * @var array
	 * @static
	 */
	protected static $orders = array();

	/**
	 * Set up fixtures once.
	 */
	public static function wpSetUpBeforeClass() {
		self::$orders = parent::edd()->order->create_many( 5 );
	}

	/**
	 * @covers ::wp_count_posts
	 */
	public function test_wp_count_posts() {
		$this->assertSame( 5, (int) wp_count_posts( 'edd_payment' )->complete );
	}

	/**
	 * @covers ::pre_get_posts
	 */
	public function test_get_posts() {
		$this->setExpectedIncorrectUsage( 'get_posts()/get_post()/WP_Query' );

		get_posts( array( 'post_type' => 'edd_payment' ) );
	}

	/**
	 * @covers ::get_post_metadata
	 */
	public function test_get_post_metadata__edd_payment_purchase_key() {
		$this->setExpectedIncorrectUsage( 'get_post_meta()' );

		$expected = edd_get_order( self::$orders[0] )->payment_key;
		$actual   = get_post_meta( self::$orders[0], '_edd_payment_purchase_key', true );

		$this->assertSame( $expected, $actual );
	}

	/**
	 * @covers ::get_post_metadata
	 */
	public function test_get_post_metadata__edd_payment_transaction_id() {
		$this->setExpectedIncorrectUsage( 'get_post_meta()' );

		$expected = edd_get_order( self::$orders[0] )->get_transaction_id();
		$actual   = get_post_meta( self::$orders[0], '_edd_payment_transaction_id', true );

		$this->assertSame( $expected, $actual );
	}

	/**
	 * @covers ::get_post_metadata
	 */
	public function test_get_post_metadata__edd_payment_user_email() {
		$this->setExpectedIncorrectUsage( 'get_post_meta()' );

		$expected = edd_get_order( self::$orders[0] )->email;
		$actual   = get_post_meta( self::$orders[0], '_edd_payment_user_email', true );

		$this->assertSame( $expected, $actual );
	}

	/**
	 * @covers ::get_post_metadata
	 */
	public function test_get_post_metadata__edd_payment_meta() {
		$this->setExpectedIncorrectUsage( 'get_post_meta()' );

		$expected = edd_get_payment( self::$orders[0] )->get_meta( '_edd_payment_meta' );
		$actual   = get_post_meta( self::$orders[0], '_edd_payment_meta', true );

		$this->assertSame( $expected, $actual );
	}

	/**
	 * @covers ::get_post_metadata
	 */
	public function test_get_post_metadata__edd_completed_date() {
		$this->setExpectedIncorrectUsage( 'get_post_meta()' );

		$expected = edd_get_order( self::$orders[0] )->date_completed;
		$actual   = get_post_meta( self::$orders[0], '_edd_completed_date', true );

		$this->assertSame( $expected, $actual );
	}

	/**
	 * @covers ::get_post_metadata
	 */
	public function test_get_post_metadata__edd_payment_gateway() {
		$this->setExpectedIncorrectUsage( 'get_post_meta()' );

		$expected = edd_get_order( self::$orders[0] )->gateway;
		$actual   = get_post_meta( self::$orders[0], '_edd_payment_gateway', true );

		$this->assertSame( $expected, $actual );
	}

	/**
	 * @covers ::get_post_metadata
	 */
	public function test_get_post_metadata__edd_payment_user_id() {
		$this->setExpectedIncorrectUsage( 'get_post_meta()' );

		$expected = edd_get_order( self::$orders[0] )->user_id;
		$actual   = get_post_meta( self::$orders[0], '_edd_payment_user_id', true );

		$this->assertSame( $expected, $actual );
	}

	/**
	 * @covers ::get_post_metadata
	 */
	public function test_get_post_metadata__edd_payment_user_ip() {
		$this->setExpectedIncorrectUsage( 'get_post_meta()' );

		$expected = edd_get_order( self::$orders[0] )->ip;
		$actual   = get_post_meta( self::$orders[0], '_edd_payment_user_ip', true );

		$this->assertSame( $expected, $actual );
	}

	/**
	 * @covers ::get_post_metadata
	 */
	public function test_get_post_metadata__edd_payment_mode() {
		$this->setExpectedIncorrectUsage( 'get_post_meta()' );

		$expected = edd_get_order( self::$orders[0] )->mode;
		$actual   = get_post_meta( self::$orders[0], '_edd_payment_mode', true );

		$this->assertSame( $expected, $actual );
	}

	/**
	 * @covers ::get_post_metadata
	 */
	public function test_get_post_metadata__edd_payment_tax_rate() {
		$this->setExpectedIncorrectUsage( 'get_post_meta()' );

		$expected = edd_get_order( self::$orders[0] )->get_tax_rate();
		$actual   = get_post_meta( self::$orders[0], '_edd_payment_tax_rate', true );

		$this->assertSame( $expected, $actual );
	}

	public function test_tax_rate_converted_to_decimal_when_querying_post_meta() {
		// Create an adjustment.
		$adjustment_id = edd_add_adjustment( array(
			'name'        => 'GB',
			'status'      => 'active',
			'type'        => 'tax_rate',
			'scope'       => 'country',
			'amount_type' => 'percent',
			'amount'      => 20
		) );

		// Create an order.
		$order = parent::edd()->order->create_and_get( array(
			'tax_rate_id' => $adjustment_id,
			'tax'         => 2
		) );

		$this->setExpectedIncorrectUsage( 'get_post_meta()' );

		$this->assertEquals( 0.2, get_post_meta( $order->id, '_edd_payment_tax_rate', true ) );
	}

	/**
	 * @covers ::get_post_metadata
	 */
	public function test_get_post_metadata__edd_payment_customer_id() {
		$this->setExpectedIncorrectUsage( 'get_post_meta()' );

		$expected = edd_get_order( self::$orders[0] )->customer_id;
		$actual   = get_post_meta( self::$orders[0], '_edd_payment_customer_id', true );

		$this->assertSame( $expected, $actual );
	}

	/**
	 * @covers ::get_post_metadata
	 */
	public function test_get_post_metadata__edd_payment_total() {
		$this->setExpectedIncorrectUsage( 'get_post_meta()' );

		$expected = edd_get_order( self::$orders[0] )->total;
		$actual   = get_post_meta( self::$orders[0], '_edd_payment_total', true );

		$this->assertSame( $expected, $actual );
	}

	/**
	 * @covers ::get_post_metadata
	 */
	public function test_get_post_metadata__edd_payment_tax() {
		$this->setExpectedIncorrectUsage( 'get_post_meta()' );

		$expected = edd_get_order( self::$orders[0] )->tax;
		$actual   = get_post_meta( self::$orders[0], '_edd_payment_tax', true );

		$this->assertSame( $expected, $actual );
	}

	/**
	 * @covers ::get_post_metadata
	 */
	public function test_get_post_metadata__edd_payment_number() {
		$this->setExpectedIncorrectUsage( 'get_post_meta()' );

		$expected = edd_get_order( self::$orders[0] )->get_number();
		$actual   = get_post_meta( self::$orders[0], '_edd_payment_number', true );

		$this->assertSame( $expected, $actual );
	}
}
