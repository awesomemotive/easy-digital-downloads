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
		$this->assertSame( 5, (int) wp_count_posts( 'edd_payment' )->publish );
	}

	/**
	 * @covers ::pre_get_posts
	 */
	public function test_get_posts() {
		$this->setExpectedIncorrectUsage( 'get_posts()/get_post()/WP_Query' );

		get_posts( array( 'post_type' => 'edd_payment' ) );
	}
}