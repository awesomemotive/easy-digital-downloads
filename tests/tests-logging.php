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
		$logs = (array) self::$object->get_logs( 1, 'gateway_error' );

		$this->assertCount( 1, $logs );
	}

	/**
	 * @covers ::get_connected_logs()
	 */
	public function test_get_connected_logs() {
		$logs = (array) self::$object->get_connected_logs( array(
			'post_parent' => 1,
			'log_type'    => 'gateway_error'
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

		$this->assertSame( 1, self::$object->get_log_count( 1, 'gateway_error' ) );

		self::$object->delete_logs( 1 );

		$this->assertSame( 0, self::$object->get_log_count( 1, 'gateway_error' ) );
	}

	/**
	 * @covers edd_record_gateway_error()
	 */
	public function test_edd_record_gateway_error() {

		$log_id     = edd_record_gateway_error( 'Test gateway error', 'Test gateway error content' );
		$actual_log = edd_get_log( $log_id );

		$expected = array(
			'object_id'   => '0',
			'object_type' => 'gateway_error',
			'user_id'     => '0',
			'type'        => 'gateway_error',
			'title'       => 'Test gateway error',
			'content'     => 'Test gateway error content',
		);

		$this->assertSame( $expected['object_id'], $actual_log->object_id );
		$this->assertSame( $expected['object_type'], $actual_log->object_type );
		$this->assertSame( $expected['user_id'], $actual_log->user_id );
		$this->assertSame( $expected['type'], $actual_log->type );
		$this->assertSame( $expected['title'], $actual_log->title );
		$this->assertSame( $expected['content'], $actual_log->content );
	}

	/**
	 * @covers edd_record_gateway_error()
	 */
	public function test_edd_add_log_with_null_type_and_no_id() {

		$log_id = edd_add_log(
			array(
				'object_type' => null,
				'object_id'   => 0,
				'title'       => 'Test log with null object type and no ID',
			)
		);

		$actual_log = edd_get_log( $log_id );

		$expected = array(
			'object_id'   => '0',
			'object_type' => null,
			'user_id'     => '0',
			'type'        => '',
			'title'       => 'Test log with null object type and no ID',
			'content'     => '',
		);

		$this->assertSame( $expected['object_id'], $actual_log->object_id );
		$this->assertSame( $expected['object_type'], $actual_log->object_type );
		$this->assertSame( $expected['user_id'], $actual_log->user_id );
		$this->assertSame( $expected['type'], $actual_log->type );
		$this->assertSame( $expected['title'], $actual_log->title );
		$this->assertSame( $expected['content'], $actual_log->content );
	}

	/**
	 * @covers edd_add_log()
	 */
	public function test_edd_add_log_with_null_type_and_an_id() {

		$log_id = edd_add_log(
			array(
				'object_type' => null,
				'object_id'   => 1,
				'title'       => 'Test log with null object type and an ID (should fail)',
			)
		);

		$actual_log = edd_get_log( $log_id );

		$expected = false;

		$this->assertSame( $expected, $actual_log );
	}

	/**
	 * @covers edd_add_log()
	 */
	public function test_edd_add_log_with_empty_type_and_no_id() {

		$log_id = edd_add_log(
			array(
				'object_type' => 0,
				'object_id'   => 0,
				'title'       => 'Test log with empty object type and no ID (should fail)',
			)
		);

		$actual_log = edd_get_log( $log_id );

		$expected = false;

		$this->assertSame( $expected, $actual_log );
	}

	/**
	 * @covers edd_add_log()
	 */
	public function test_edd_add_log_with_empty_type_and_an_id() {

		$log_id = edd_add_log(
			array(
				'object_type' => 0,
				'object_id'   => 1,
				'title'       => 'Test log with empty object type and an ID (should fail)',
			)
		);

		$actual_log = edd_get_log( $log_id );

		$expected = false;

		$this->assertSame( $expected, $actual_log );
	}

}
