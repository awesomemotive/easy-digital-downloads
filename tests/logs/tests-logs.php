<?php
namespace EDD\Logs;

/**
 * Logs DB Tests
 *
 * @group edd_logs_db
 * @group database
 * @group edd_logs
 *
 * @coversDefaultClass \EDD_Log_Query
 */
class Logs_Tests extends \EDD_UnitTestCase {

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
	 * @covers ::update_item()
	 */
	public function test_update_should_return_true() {
		$success = edd_update_log( self::$logs[0], array(
			'title' => 'Log title 45',
		) );

		$this->assertSame( 1, $success );
	}

	/**
	 * @covers ::update_item()
	 */
	public function test_log_object_after_update_should_return_true() {
		$success = edd_update_log( self::$logs[0], array(
			'title' => 'Log title 45',
		) );

		$log = new Log( self::$logs[0] );

		$this->assertEquals( 'Log title 45', $log->title );
	}

	/**
	 * @covers ::update_item()
	 */
	public function test_update_without_id_should_fail() {
		$success = edd_update_log( null, array(
			'message' => 'Payment status changed',
		) );

		$this->assertFalse( $success );
	}

	/**
	 * @covers ::delete_item()
	 */
	public function test_delete_should_return_true() {
		$success = edd_delete_log( self::$logs[0] );

		$this->assertSame( 1, $success );
	}

	/**
	 * @covers ::delete_item()
	 */
	public function test_delete_without_id_should_fail() {
		$success = edd_delete_log( '' );

		$this->assertFalse( $success );
	}

	/**
	 * @covers ::query()
	 */
	public function test_get_logs_with_number_should_return_true() {
		$logs = edd_get_logs( array(
			'number' => 10,
		) );

		$this->assertCount( 5, $logs );
	}

	/**
	 * @covers ::query()
	 */
	public function test_get_logs_with_offset_should_return_true() {
		$logs = edd_get_logs( array(
			'number' => 10,
			'offset' => 4,
		) );

		$this->assertCount( 1, $logs );
	}

	/**
	 * @covers ::query()
	 */
	public function test_get_logs_with_search_should_return_true() {
		$logs = edd_get_logs( array(
			'search' => 'Log title',
		) );

		$this->assertCount( 5, $logs );
	}

	/**
	 * @covers ::query()
	 */
	public function test_get_logs_with_orderby_title_and_order_asc_should_return_true() {
		$logs = edd_get_logs( array(
			'orderby' => 'title',
			'order'   => 'asc'
		) );

		$this->assertTrue( $logs[0]->title < $logs[1]->title );
	}

	/**
	 * @covers ::query()
	 */
	public function test_get_logs_with_orderby_title_and_order_desc_should_return_true() {
		$logs = edd_get_logs( array(
			'orderby' => 'title',
			'order'   => 'desc'
		) );

		$this->assertTrue( $logs[0]->title > $logs[1]->title );
	}

	/**
	 * @covers ::query()
	 */
	public function test_get_logs_with_orderby_content_and_order_asc_should_return_true() {
		$logs = edd_get_logs( array(
			'orderby' => 'content',
			'order'   => 'asc'
		) );

		$this->assertTrue( $logs[0]->content < $logs[1]->content );
	}

	/**
	 * @covers ::query()
	 */
	public function test_get_logs_with_orderby_content_and_order_desc_should_return_true() {
		$logs = edd_get_logs( array(
			'orderby' => 'content',
			'order'   => 'desc'
		) );

		$this->assertTrue( $logs[0]->content > $logs[1]->content );
	}

	/**
	 * @covers ::query()
	 */
	public function test_get_logs_with_order_asc_should_return_true() {
		$logs = edd_get_logs( array(
			'order' => 'asc',
		) );

		$this->assertTrue( $logs[0]->id < $logs[1]->id );
	}

	/**
	 * @covers ::query()
	 */
	public function test_get_logs_with_order_desc_should_return_true() {
		$logs = edd_get_logs( array(
			'order' => 'desc',
		) );

		$this->assertTrue( $logs[0]->id > $logs[1]->id );
	}

	/**
	 * @covers ::query()
	 */
	public function test_get_logs_with_log_id_should_return_1() {
		$logs = edd_get_logs( array(
			'id' => 1,
		) );

		$this->assertCount( 1, $logs );
	}

	/**
	 * @covers ::query()
	 */
	public function test_get_logs_with_id__in_should_return_2() {
		$logs = edd_get_logs( array(
			'id__in' => array( 1, 2 ),
		) );

		$this->assertCount( 2, $logs );
	}

	/**
	 * @covers ::query()
	 */
	public function test_get_logs_with_id__not_in_should_return_3() {
		$logs = edd_get_logs( array(
			'id__not_in' => array( 1, 2 ),
			'number'     => 5,
		) );

		$this->assertCount( 3, $logs );
	}

	/**
	 * @covers ::query()
	 */
	public function test_get_logs_with_object_id_should_return_1() {
		$logs = edd_get_logs( array(
			'object_id' => \WP_UnitTest_Generator_Sequence::$incr
		) );

		$this->assertCount( 1, $logs );
	}

	/**
	 * @covers ::query()
	 */
	public function test_get_logs_with_object_id__in_should_return_2() {
		$logs = edd_get_logs( array(
			'object_id__in' => array( \WP_UnitTest_Generator_Sequence::$incr, \WP_UnitTest_Generator_Sequence::$incr - 1 )
		) );

		$this->assertCount( 2, $logs );
	}

	/**
	 * @covers ::query()
	 */
	public function test_get_logs_with_object_id__not_in_should_return_3() {
		$logs = edd_get_logs( array(
			'object_id__not_in' => array( \WP_UnitTest_Generator_Sequence::$incr, \WP_UnitTest_Generator_Sequence::$incr - 1 ),
			'number'            => 5,
		) );

		$this->assertCount( 3, $logs );
	}

	/**
	 * @covers ::query()
	 */
	public function test_get_logs_with_invalid_object_id_should_return_0() {
		$logs = edd_get_logs( array(
			'object_id' => 99999,
		) );

		$this->assertCount( 0, $logs );
	}

	/**
	 * @covers ::query()
	 */
	public function test_get_logs_with_object_type_should_return_5() {
		$logs = edd_get_logs( array(
			'object_type' => 'download',
		) );

		$this->assertCount( 5, $logs );
	}

	/**
	 * @covers ::query()
	 */
	public function test_get_logs_with_object_type__in_should_return_2() {
		$logs = edd_get_logs( array(
			'object_type__in' => array( 'download' )
		) );

		$this->assertCount( 5, $logs );
	}

	/**
	 * @covers ::query()
	 */
	public function test_get_logs_with_object_type__not_in_should_return_3() {
		$logs = edd_get_logs( array(
			'object_type__not_in' => array( 'download' ),
			'number'            => 5,
		) );

		$this->assertCount( 0, $logs );
	}

	/**
	 * @covers ::query()
	 */
	public function test_get_logs_by_title_should_return_1() {
		$logs = edd_get_logs( array(
			'title' => 'Log title ' . \WP_UnitTest_Generator_Sequence::$incr,
		) );

		$this->assertCount( 1, $logs );
	}

	/**
	 * @covers ::query()
	 */
	public function test_get_logs_with_invalid_title_should_return_0() {
		$logs = edd_get_logs( array(
			'title' => 'Log title 99999',
		) );

		$this->assertCount( 0, $logs );
	}

	/**
	 * @covers ::query()
	 */
	public function test_get_logs_returned_objects_should_return_true() {
		$logs = edd_get_logs( array(
			'number' => 5
		) );

		foreach ( $logs as $log ) {
			$this->assertInstanceOf( Log::class, $log );
		}
	}

	/**
	 * @covers ::count()
	 */
	public function test_count() {
		$this->assertEquals( 5, edd_count_logs() );
	}
}