<?php
namespace EDD\Tests\Logs\General;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;

/**
 * Logs Meta DB Tests
 *
 * @group edd_logs_db
 * @group database
 * @group edd_logs
 *
 * @coversDefaultClass \EDD\Database\Queries\Logs
 */
class Log_Meta_Tests extends EDD_UnitTestCase {

	/**
	 * Discount object test fixture.
	 *
	 * @access protected
	 * @var    Log
	 */
	protected static $log;

	/**
	 * Set up fixtures.
	 */
	public static function wpSetUpBeforeClass() {
		self::$log = parent::edd()->log->create_and_get();
	}

	public function tearDown(): void {
		parent::tearDown();

		edd_get_component_interface( 'log', 'meta' )->truncate();
	}

	/**
	 * @covers \EDD\Database\Queries\Logs::add_meta()
	 * @covers \EDD\Logs\Log::add_meta()
	 */
	public function test_add_metadata_with_empty_key_value_should_return_false() {
		$this->assertFalse( edd_add_log_meta( self::$log->id, '', '' ) );
	}

	/**
	 * @covers \EDD\Database\Queries\Logs::add_meta()
	 * @covers \EDD\Logs\Log::add_meta()
	 */
	public function test_add_metadata_with_empty_value_should_be_empty() {
		$this->assertNotEmpty( edd_add_log_meta( self::$log->id, 'test_key', '' ) );
	}

	/**
	 * @covers \EDD\Database\Queries\Logs::add_meta()
	 * @covers \EDD\Logs\Log::add_meta()
	 */
	public function test_add_metadata_with_key_value_should_not_be_empty() {
		$this->assertNotEmpty( edd_add_log_meta( self::$log->id, 'test_key', '1' ) );
	}

	/**
	 * @covers \EDD\Database\Queries\Logs::update_meta()
	 * @covers \EDD\Logs\Log::update_meta()
	 */
	public function test_update_metadata_with_empty_key_value_should_be_empty() {
		$this->assertEmpty( edd_update_note_meta( self::$log->id, '', '' ) );
	}

	/**
	 * @covers \EDD\Database\Queries\Logs::update_meta()
	 * @covers \EDD\Logs\Log::update_meta()
	 */
	public function test_update_metadata_with_empty_value_should_not_be_empty() {
		$this->assertNotEmpty( edd_update_log_meta( self::$log->id, 'test_key_2', '' ) );
	}

	/**
	 * @covers \EDD\Database\Queries\Logs::update_meta()
	 * @covers \EDD\Logs\Log::update_meta()
	 */
	public function test_update_metadata_with_key_value_should_not_be_empty() {
		$this->assertNotEmpty( edd_update_log_meta( self::$log->id, 'test_key_2', '1' ) );
	}

	/**
	 * @covers \EDD\Database\Queries\Logs::get_meta()
	 * @covers \EDD\Logs\Log::get_meta()
	 */
	public function test_get_metadata_with_no_args_should_be_empty() {
		$this->assertEmpty( edd_get_note_meta( self::$log->id ) );
	}

	/**
	 * @covers \EDD\Database\Queries\Logs::get_meta()
	 * @covers \EDD\Logs\Log::get_meta()
	 */
	public function test_get_metadata_with_invalid_key_should_be_empty() {
		$this->assertEmpty( edd_get_log_meta( self::$log->id, 'key_that_does_not_exist', true ) );
	}

	/**
	 * @covers \EDD\Database\Queries\Logs::get_meta()
	 * @covers \EDD\Logs\Log::get_meta()
	 */
	public function test_get_metadata_after_update_should_return_that_value() {
		edd_update_log_meta( self::$log->id, 'test_key_2', '1' );
		$this->assertEquals( '1', edd_get_log_meta( self::$log->id, 'test_key_2', true ) );
	}

	/**
	 * @covers \EDD\Database\Queries\Logs::delete_meta()
	 * @covers \EDD\Logs\Log::delete_meta()
	 */
	public function test_delete_metadata_with_valid_key_should_return_true() {
		edd_update_log_meta( self::$log->id, 'test_key', '1' );
		$this->assertTrue( edd_delete_log_meta( self::$log->id, 'test_key' ) );
	}

	/**
	 * @covers \EDD\Database\Queries\Logs::delete_meta()
	 * @covers \EDD\Logs\Log::delete_meta()
	 */
	public function test_delete_metadata_with_invalid_key_should_return_false() {
		$this->assertFalse( edd_delete_log_meta( self::$log->id, 'key_that_does_not_exist' ) );
	}
}
