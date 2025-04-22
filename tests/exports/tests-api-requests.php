<?php

namespace EDD\Tests\Exports;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;
use EDD\Admin\Exports\Exporters\APIRequests as Exporter;

class APIRequests extends EDD_UnitTestCase {

	/**
	 * Logs fixture.
	 *
	 * @var array
	 * @static
	 */
	protected static $logs = array();

	/**
	 * Exporter instance.
	 *
	 * @var \EDD\Admin\Exports\Exporters\APIRequests
	 * @static
	 */
	private static $exporter;

	/**
	 * User ID.
	 *
	 * @var int
	 * @static
	 */
	private static $user_id;

	/**
	 * Set up fixtures once.
	 */
	public static function setUpBeforeClass(): void {
		parent::setUpBeforeClass();
		self::$user_id = parent::factory()->user->create( array( 'role' => 'administrator' ) );
		$user = new \WP_User( self::$user_id );
		$user->add_cap( 'export_shop_reports' );
		self::$logs = parent::edd()->api_request_log->create_many( 5 );
		self::$exporter = new Exporter();
	}

	public function setUp(): void {
		parent::setUp();
		wp_set_current_user( self::$user_id );
	}

	public function test_can_export_is_true() {
		$this->assertTrue( self::$exporter->can_export() );
	}

	public function test_percentage_complete_is_100() {
		$this->assertSame( 100.0, self::$exporter->get_percentage_complete() );
	}

	public function test_exporter_step_is_true() {
		$this->assertTrue( self::$exporter->process_step( 1 ) );
	}

	public function test_can_export_is_false_for_subscriber() {
		$user_id = parent::factory()->user->create( array( 'role' => 'subscriber' ) );
		wp_set_current_user( $user_id );

		$this->assertFalse( self::$exporter->can_export() );
	}
}
