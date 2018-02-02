<?php

/**
 * EDD Logging Class Tests
 *
 * @group edd_logs
 *
 * @coversDefaultClass EDD_Logging
 */
class Tests_Logging extends EDD_UnitTestCase {

	/**
	 * EDD_Logging fixture.
	 *
	 * @var EDD_Logging
	 * @static
	 */
	protected static $object;

	/**
	 * Log test fixture.
	 *
	 * @var int
	 * @static
	 */
	protected static $log_id;

	/**
	 * Set up fixtures once.
	 */
	public static function wpSetUpBeforeClass() {
		self::$object = new EDD_Logging();

		self::$log_id = self::$object->insert_log( array(
			'log_type'     => 'gateway_error',
			'post_parent'  => 1,
			'post_title'   => 'Test Log',
			'post_content' => 'This is a test log inserted from PHPUnit',
		) );
	}

	/**
	 * @covers ::valid_type()
	 */
	public function test_valid_log() {
		$this->assertTrue( self::$object->valid_type( 'file_download' ) );
	}

	/**
	 * @covers ::valid_type()
	 */
	public function test_fake_log() {
		$this->assertFalse( self::$object->valid_type( 'foo' ) );
	}

	/**
	 * @covers ::add()
	 */
	public function test_add() {
		$this->assertNotNull( self::$object->add() );
	}

	/**
	 * @covers ::get_logs()
	 */
	public function test_get_logs() {
		$logs = (array) self::$object->get_logs( 1, 'sale' );

		$this->assertCount( 1, $logs );
	}

	/**
	 * @covers ::get_connected_logs()
	 */
	public function test_get_connected_logs() {
		$logs = (array) self::$object->get_connected_logs( array(
			'post_parent' => 1,
			'log_type'    => 'sale'
		) );

		$this->assertCount( 1, $logs );
	}

	/**
	 * @covers ::get_log_count()
	 */
	public function test_get_log_count() {
		$this->assertSame( 1, self::$object->get_log_count( 1, 'gateway_error' ) );
	}

	/**
	 * @covers ::delete_logs()
	 */
	public function test_delete_logs() {
		self::$object->delete_logs( self::$log_id );

		$this->assertSame( 0, self::$object->get_log_count( 1, 'sale' ) );
	}
}
