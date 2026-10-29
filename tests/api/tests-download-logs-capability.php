<?php
/**
 * File download log API capability tests.
 */

namespace EDD\Tests\API;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;

/**
 * Tests which callers the file download log endpoint will answer.
 *
 * The endpoint reports which customer took which file from which order, when, and from which IP,
 * so it is gated on the same capability the customers endpoint uses for comparable data. These
 * cases call the mode's method directly with the API's own `user_id` and `override` set, which is
 * how the request lifecycle would have populated them.
 *
 * @group api
 */
class DownloadLogsCapability extends EDD_UnitTestCase {

	/**
	 * A user holding none of EDD's shop capabilities.
	 *
	 * @var int
	 */
	protected static $subscriber_id;

	/**
	 * A user holding the capability the endpoint requires.
	 *
	 * @var int
	 */
	protected static $shop_manager_id;

	/**
	 * The seeded log row, whose presence every refusal case depends on.
	 *
	 * @var int
	 */
	protected static $log_id;

	/**
	 * The email on the seeded log's customer, for the lookup-by-email case.
	 *
	 * @var string
	 */
	protected static $customer_email;

	public static function wpSetUpBeforeClass() {
		$roles = new \EDD_Roles();
		$roles->add_roles();
		$roles->add_caps();

		self::$subscriber_id   = self::factory()->user->create( array( 'role' => 'subscriber' ) );
		self::$shop_manager_id = self::factory()->user->create( array( 'role' => 'shop_manager' ) );

		self::$customer_email = 'download-logs@example.test';

		$customer_id = edd_add_customer(
			array(
				'name'  => 'Download Logs',
				'email' => self::$customer_email,
			)
		);

		self::$log_id = edd_add_file_download_log(
			array(
				'product_id'  => 1,
				'file_id'     => 0,
				'order_id'    => 1,
				'price_id'    => 0,
				'customer_id' => $customer_id,
				'ip'          => '203.0.113.44',
			)
		);
	}

	public function setUp(): void {
		parent::setUp();

		EDD()->api->override = false;
		EDD()->api->user_id  = 0;
	}

	public function tearDown(): void {
		EDD()->api->override = true;
		EDD()->api->user_id  = 0;

		wp_set_current_user( 0 );

		parent::tearDown();
	}

	/**
	 * The premise every case below rests on: there is a row to read, and the two actors differ in
	 * exactly the capability under test.
	 */
	public function test_the_fixture_and_the_two_actors_are_what_these_cases_assume() {
		$this->assertNotEmpty( self::$log_id, 'A log row must exist, or a refusal proves nothing.' );
		$this->assertNotEmpty(
			edd_get_file_download_logs( array( 'number' => 0 ) ),
			'The log must be readable without a capability filter, or the endpoint has nothing to withhold.'
		);

		$this->assertFalse(
			user_can( self::$subscriber_id, 'view_shop_sensitive_data' ),
			'The refused actor must lack the capability under test.'
		);
		$this->assertTrue(
			user_can( self::$shop_manager_id, 'view_shop_sensitive_data' ),
			'The permitted actor must hold it, or the control proves nothing.'
		);
	}

	/**
	 * A key belonging to a user without the capability reads no rows.
	 */
	public function test_download_logs_are_refused_without_the_capability() {
		EDD()->api->user_id = self::$subscriber_id;

		$response = EDD()->api->get_download_logs();

		$this->assertEmpty( $response, 'No log rows may be returned to a caller without the capability.' );
		$this->assertNotEmpty(
			edd_get_file_download_logs( array( 'number' => 0 ) ),
			'The refusal must withhold the rows rather than the rows having gone missing.'
		);
	}

	/**
	 * The paired positive, so the rule cannot be tightened into refusing everyone.
	 */
	public function test_download_logs_are_returned_with_the_capability() {
		EDD()->api->user_id = self::$shop_manager_id;

		$response = EDD()->api->get_download_logs();

		$this->assertNotEmpty( $response, 'A caller holding the capability must still read the log.' );
		$this->assertArrayHasKey( 'download_logs', $response );
		$this->assertNotEmpty( $response['download_logs'] );
	}

	/**
	 * Internal callers set `override`, and must keep working.
	 */
	public function test_download_logs_are_returned_for_an_override_caller() {
		EDD()->api->override = true;
		EDD()->api->user_id  = 0;

		$response = EDD()->api->get_download_logs();

		$this->assertNotEmpty( $response, 'An internal override caller must not be refused.' );
	}

	/**
	 * The customer parameter accepts an email address, so the refusal has to happen before any
	 * lookup rather than after it, or the endpoint still answers whether an address is a customer.
	 */
	public function test_the_customer_parameter_answers_nothing_without_the_capability() {
		EDD()->api->user_id = self::$subscriber_id;

		$response = EDD()->api->get_download_logs( self::$customer_email );

		$this->assertEmpty( $response, 'Naming a customer by email must not produce a different answer.' );
	}

	/**
	 * The control that catches a fix placed in validate_request() rather than on this mode: the
	 * products endpoint is deliberately public and answers with no key at all.
	 */
	public function test_the_products_endpoint_is_still_public() {
		EDD()->api->user_id = 0;

		$response = EDD()->api->get_products();

		$this->assertArrayHasKey( 'products', $response, 'The products endpoint must stay public.' );
	}
}
