<?php
namespace EDD\Logs;

/**
 * Logs Meta DB Tests
 *
 * @group edd_logs_db
 * @group database
 * @group edd_logs
 *
 * @coversDefaultClass \EDD_DB_Log_Meta
 */
class Logs_Meta_DB_Tests extends \EDD_UnitTestCase {

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
		$log_id = edd_add_log( array(
			'object_id'   => 1234,
			'object_type' => 'order',
			'title'       => 'Order Status Change',
			'message'     => 'Order status has changed from pending to completed.',
		) );

		self::$log = new Log( $log_id );
	}

	/**
	 * Delete all data once tests have completed.
	 */
	public static function wpTearDownAfterClass() {
		global $wpdb;

		foreach ( array(
			EDD()->logs->table_name,
			EDD()->log_meta->table_name,
			EDD()->file_download_logs->table_name,
			EDD()->api_request_logs->table_name,
		) as $table ) {
			$wpdb->query( "DELETE FROM {$table}" );
		}
	}

	/**
	 * @covers \EDD_DB_Log_Meta::add_meta()
	 * @covers \EDD\Logs\Log::add_meta()
	 */
	public function test_add_metadata_with_empty_key_value_should_return_false() {
		$this->assertFalse( self::$log->add_meta( '', '' ) );
	}

	/**
	 * @covers \EDD_DB_Log_Meta::add_meta()
	 * @covers \EDD\Logs\Log::add_meta()
	 */
	public function test_add_metadata_with_empty_value_should_be_empty() {
		$this->assertNotEmpty( self::$log->add_meta( 'test_key', '' ) );
	}

	/**
	 * @covers \EDD_DB_Log_Meta::add_meta()
	 * @covers \EDD\Logs\Log::add_meta()
	 */
	public function test_add_metadata_with_key_value_should_not_be_empty() {
		$this->assertNotEmpty( self::$log->add_meta( 'test_key', '1' ) );
	}

	/**
	 * @covers \EDD_DB_Log_Meta::update_meta()
	 * @covers \EDD\Logs\Log::update_meta()
	 */
	public function test_update_metadata_with_empty_key_value_should_be_empty() {
		$this->assertEmpty( self::$log->update_meta( '', '' ) );
	}

	/**
	 * @covers \EDD_DB_Log_Meta::update_meta()
	 * @covers \EDD\Logs\Log::update_meta()
	 */
	public function test_update_metadata_with_empty_value_should_not_be_empty() {
		$this->assertNotEmpty( self::$log->update_meta( 'test_key_2', '' ) );
	}

	/**
	 * @covers \EDD_DB_Log_Meta::update_meta()
	 * @covers \EDD\Logs\Log::update_meta()
	 */
	public function test_update_metadata_with_key_value_should_not_be_empty() {
		$this->assertNotEmpty( self::$log->update_meta( 'test_key_2', '1' ) );
	}

	/**
	 * @covers \EDD_DB_Log_Meta::get_meta()
	 * @covers \EDD\Logs\Log::get_meta()
	 */
	public function test_get_metadata_with_no_args_should_be_empty() {
		$this->assertEmpty( self::$log->get_meta() );
	}

	/**
	 * @covers \EDD_DB_Log_Meta::get_meta()
	 * @covers \EDD\Logs\Log::get_meta()
	 */
	public function test_get_metadata_with_invalid_key_should_be_empty() {
		$this->assertEmpty( self::$log->get_meta( 'key_that_does_not_exist', true ) );
	}

	/**
	 * @covers \EDD_DB_Log_Meta::get_meta()
	 * @covers \EDD\Logs\Log::get_meta()
	 */
	public function test_get_metadata_after_update_should_return_that_value() {
		self::$log->update_meta( 'test_key_2', '1' );
		$this->assertEquals( '1', self::$log->get_meta( 'test_key_2', true ) );
	}

	/**
	 * @covers \EDD_DB_Log_Meta::delete_meta()
	 * @covers \EDD\Logs\Log::delete_meta()
	 */
	public function test_delete_metadata_with_valid_key_should_return_true() {
		self::$log->update_meta( 'test_key', '1' );
		$this->assertTrue( self::$log->delete_meta( 'test_key' ) );
	}

	/**
	 * @covers \EDD_DB_Log_Meta::delete_meta()
	 * @covers \EDD\Logs\Log::delete_meta()
	 */
	public function test_delete_metadata_with_invalid_key_should_return_false() {
		$this->assertFalse( self::$log->delete_meta( 'key_that_does_not_exist' ) );
	}


}