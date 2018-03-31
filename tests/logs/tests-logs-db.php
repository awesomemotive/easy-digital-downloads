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
		self::$logs = parent::edd()->log->create_many( 5 );
	}

	/**
	 * @covers ::update()
	 */
	public function test_update_should_return_true() {
		$success = edd_update_log( self::$logs[0], array(
			'title' => 'Log title 45',
		) );

		$this->assertTrue( $success );
	}

	/**
	 * @covers ::update()
	 */
	public function test_log_object_after_update_should_return_true() {
		$success = edd_update_log( self::$logs[0], array(
			'title' => 'Log title 45',
		) );

		$log = new Log( self::$logs[0] );

		$this->assertEquals( 'Log title 45', $log->title );
	}

	/**
	 * @covers \EDD_DB_Logs::update()
	 */
	public function test_update_without_id_should_fail() {
		$success = edd_update_log( null, array(
			'message' => 'Payment status changed',
		) );

		$this->assertFalse( $success );
	}

	/**
	 * @covers ::delete()
	 */
	public function test_delete_should_return_true() {
		$success = edd_delete_log( self::$logs[0] );

		$this->assertSame( 1, $success );
	}

	/**
	 * @covers ::delete()
	 */
	public function test_delete_without_id_should_fail() {
		$success = edd_delete_log( '' );

		$this->assertFalse( $success );
	}

	/**
	 * @covers ::get_logs()
	 */
	public function test_get_logs_with_number_should_return_true() {
		$logs = edd_get_logs( array(
			'number' => 10,
		) );

		$this->assertCount( 5, $logs );
	}

	/**
	 * @covers ::get_logs()
	 */
	public function test_get_logs_with_offset_should_return_true() {
		$logs = edd_get_logs( array(
			'number' => 10,
			'offset' => 4,
		) );

		$this->assertCount( 1, $logs );
	}

	/**
	 * @covers ::get_logs()
	 */
	public function test_get_logs_with_search_should_return_true() {
		$logs = edd_get_logs( array(
			'search' => 'Log title',
		) );

		$this->assertCount( 5, $logs );
	}

	/**
	 * @covers ::get_logs()
	 */
	public function test_get_logs_with_orderby_title_and_order_asc_should_return_true() {
		$logs = edd_get_logs( array(
			'orderby' => 'title',
			'order'   => 'asc'
		) );

		$this->assertTrue( $logs[0]->title < $logs[1]->title );
	}

	/**
	 * @covers ::get_logs()
	 */
	public function test_get_logs_with_orderby_title_and_order_desc_should_return_true() {
		$logs = edd_get_logs( array(
			'orderby' => 'title',
			'order'   => 'desc'
		) );

		$this->assertTrue( $logs[0]->title > $logs[1]->title );
	}

	/**
	 * @covers ::get_logs()
	 */
	public function test_get_logs_with_orderby_message_and_order_asc_should_return_true() {
		$logs = edd_get_logs( array(
			'orderby' => 'message',
			'order'   => 'asc'
		) );

		$this->assertTrue( $logs[0]->message < $logs[1]->message );
	}

	/**
	 * @covers ::get_logs()
	 */
	public function test_get_logs_with_orderby_message_and_order_desc_should_return_true() {
		$logs = edd_get_logs( array(
			'orderby' => 'message',
			'order'   => 'desc'
		) );

		$this->assertTrue( $logs[0]->message > $logs[1]->message );
	}

	/**
	 * @covers ::get_logs()
	 */
	public function test_get_logs_with_order_asc_should_return_true() {
		$logs = edd_get_logs( array(
			'order' => 'asc',
		) );

		$this->assertTrue( $logs[0]->id < $logs[1]->id );
	}

	/**
	 * @covers ::get_logs()
	 */
	public function test_get_logs_with_order_desc_should_return_true() {
		$logs = edd_get_logs( array(
			'order' => 'desc',
		) );

		$this->assertTrue( $logs[0]->id > $logs[1]->id );
	}

	/**
	 * @covers ::get_logs()
	 */
	public function test_get_logs_by_object_id_should_return_true() {
		$logs = edd_get_logs( array(
			'object_id' => \WP_UnitTest_Generator_Sequence::$incr
		) );

		$this->assertCount( 1, $logs );
	}

	/**
	 * @covers ::get_logs()
	 */
	public function test_get_logs_by_invalid_object_id_should_return_true() {
		$logs = edd_get_logs( array(
			'object_id' => 99999,
		) );

		$this->assertCount( 0, $logs );
	}

	/**
	 * @covers ::get_logs()
	 */
	public function test_get_logs_by_object_type_should_return_true() {
		$logs = edd_get_logs( array(
			'object_type' => 'download',
		) );

		$this->assertCount( 5, $logs );
	}

	/**
	 * @covers ::get_logs()
	 */
	public function test_get_logs_by_title_should_return_true() {
		$logs = edd_get_logs( array(
			'title' => 'Log title ' . \WP_UnitTest_Generator_Sequence::$incr,
		) );

		$this->assertCount( 1, $logs );
	}

	/**
	 * @covers ::get_logs()
	 */
	public function test_get_logs_with_invalid_title_should_return_true() {
		$logs = edd_get_logs( array(
			'title' => 'Log title 99999',
		) );

		$this->assertCount( 0, $logs );
	}

	/**
	 * @covers ::count()
	 */
	public function test_count() {
		$this->assertEquals( 5, edd_count_logs() );
	}

	/**
	 * @covers ::counts_by_type()
	 */
	public function test_counts_by_type() {
		$counts = EDD()->logs->counts_by_type();
		$this->assertEquals( 5, $counts->sale );
	}
}