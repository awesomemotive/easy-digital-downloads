<?php
namespace EDD\Logs;

/**
 * Logs DB Tests
 *
 * @group edd_logs_db
 * @group database
 * @group edd_logs
 *
 * @coversDefaultClass \EDD_DB_Logs
 */
class Logs_DB_Tests extends \EDD_UnitTestCase {

	/**
	 * Logs fixture.
	 *
	 * @var array
	 * @static
	 */
	protected static $logs = array();

	/**
	 * Set up fixtures once.
	 */
	public static function wpSetUpBeforeClass() {

	}

	/**
	 * @covers \EDD_DB_Logs::get_columns()
	 */
	public function test_get_columns_should_return_all_columns() {
		$expected = array(
			'id'           => '%d',
			'object_id'    => '%d',
			'object_type'  => '%s',
			'type'         => '%s',
			'title'        => '%s',
			'message'      => '%s',
			'date_created' => '%s',
		);

		$this->assertEqualSets( $expected, EDD()->logs->get_columns() );
	}

	/**
	 * @covers \EDD_DB_Logs::get_column_defaults()
	 */
	public function test_get_column_defaults_should_return_defaults() {
		$expected = array(
			'id'           => 0,
			'object_id'    => 0,
			'object_type'  => '',
			'type'         => '',
			'title'        => '',
			'message'      => '',
			'date_created' => date( 'Y-m-d H:i:s' ),
		);

		$this->assertSame( $expected, EDD()->logs->get_column_defaults() );
	}

	/**
	 * @covers \EDD_DB_Logs::update()
	 */
	public function test_update_without_id_should_fail() {
		$success = EDD()->logs->update( null, array(
			'message' => 'Payment status changed',
		) );

		$this->assertFalse( $success );
	}

}