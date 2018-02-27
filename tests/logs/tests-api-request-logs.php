<?php
namespace EDD\Logs;

/**
 * API Request Log DB Tests
 *
 * @group edd_logs_db
 * @group database
 * @group edd_logs
 *
 * @coversDefaultClass \EDD_DB_Logs_File_Downloads
 */
class API_Request_Logs_DB_Tests extends \EDD_UnitTestCase {

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
		self::$logs = parent::edd()->api_request_log->create_many( 5 );
	}

	/**
	 * @covers ::$cache_group
	 */
	public function test_cache_group_should_be_logs() {
		$this->assertSame( 'logs_api_requests', EDD()->api_request_logs->cache_group );
	}

	/**
	 * @covers ::$primary_key
	 */
	public function test_primary_key_should_be_id() {
		$this->assertSame( 'id', EDD()->api_request_logs->primary_key );
	}

	/**
	 * @covers ::get_columns()
	 */
	public function test_get_columns_should_return_all_columns() {
		$expected = array(
			'id'           => '%d',
			'user_id'      => '%d',
			'api_key'      => '%s',
			'token'        => '%s',
			'version'      => '%s',
			'request'      => '%s',
			'error'        => '%s',
			'ip'           => '%s',
			'time'         => '%f',
			'date_created' => '%s',
		);

		$this->assertEqualSets( $expected, EDD()->api_request_logs->get_columns() );
	}

	/**
	 * @covers \EDD_DB_Logs::get_column_defaults()
	 */
	public function test_get_column_defaults_should_return_defaults() {
		$expected = array(
			'id'           => 0,
			'user_id'      => 0,
			'api_key'      => 'public',
			'token'        => '',
			'version'      => '',
			'request'      => '',
			'error'        => '',
			'ip'           => '',
			'time'         => '',
			'date_created' => date( 'Y-m-d H:i:s' ),
		);

		$this->assertEqualSets( $expected, EDD()->api_request_logs->get_column_defaults() );
	}

	/**
	 * @covers ::update()
	 */
	public function test_update_should_return_true() {
		$success = EDD()->api_request_logs->update( self::$logs[0], array(
			'ip' => '10.0.0.1',
		) );

		$this->assertTrue( $success );
	}

	/**
	 * @covers ::update()
	 */
	public function test_log_object_after_update_should_return_true() {
		$success = EDD()->api_request_logs->update( self::$logs[0], array(
			'ip' => '10.0.0.1',
		) );

		$log = new API_Request_Log( self::$logs[0] );

		$this->assertEquals( '10.0.0.1', $log->ip );
	}

	/**
	 * @covers \EDD_DB_Logs::update()
	 */
	public function test_update_without_ip_should_fail() {
		$success = EDD()->api_request_logs->update( null, array(
			'ip' => '10.0.0.1',
		) );

		$this->assertFalse( $success );
	}

	/**
	 * @covers ::delete()
	 */
	public function test_delete_should_return_true() {
		$success = EDD()->api_request_logs->delete( self::$logs[0] );

		$this->assertTrue( $success );
	}

	/**
	 * @covers ::delete()
	 */
	public function test_delete_without_id_should_fail() {
		$success = EDD()->api_request_logs->delete( '' );

		$this->assertFalse( $success );
	}

	/**
	 * @covers ::get_logs()
	 */
	public function test_get_logs() {
		$logs = EDD()->api_request_logs->get_logs();

		$this->assertCount( 5, $logs );
	}

	/**
	 * @covers ::get_logs()
	 */
	public function test_get_logs_with_number_should_return_true() {
		$logs = EDD()->api_request_logs->get_logs( array(
			'number' => 10,
		) );

		$this->assertCount( 5, $logs );
	}

	/**
	 * @covers ::get_logs()
	 */
	public function test_get_logs_with_offset_should_return_true() {
		$logs = EDD()->api_request_logs->get_logs( array(
			'number' => 10,
			'offset' => 4,
		) );

		$this->assertCount( 1, $logs );
	}

	/**
	 * @covers ::get_logs()
	 */
	public function test_get_logs_with_search_should_return_true() {
		$logs = EDD()->api_request_logs->get_logs( array(
			'search' => 'edd-api=sales',
		) );

		$this->assertCount( 5, $logs );
	}

	/**
	 * @covers ::get_logs()
	 */
	public function test_get_logs_with_search_with_orderby_should_return_true() {
		$logs = EDD()->api_request_logs->get_logs( array(
			'search'  => 'edd-api=sales',
			'orderby' => 'api_key',
		) );

		$this->assertTrue( $logs[0]->api_key > $logs[1]->api_key );
	}

	/**
	 * @covers ::get_logs()
	 */
	public function test_get_logs_with_search_with_orderby_and_order_desc_should_return_true() {
		$logs = EDD()->api_request_logs->get_logs( array(
			'search'  => 'edd-api=sales',
			'orderby' => 'api_key',
			'order'   => 'desc'
		) );

		$this->assertTrue( $logs[0]->api_key > $logs[1]->api_key );
	}

	/**
	 * @covers ::get_logs()
	 */
	public function test_get_logs_with_search_with_orderby_and_order_asc_should_return_true() {
		$logs = EDD()->api_request_logs->get_logs( array(
			'search'  => 'edd-api=sales',
			'orderby' => 'api_key',
			'order'   => 'asc'
		) );

		$this->assertTrue( $logs[0]->api_key < $logs[1]->api_key );
	}

	/**
	 * @covers ::get_logs()
	 */
	public function test_get_logs_with_invalid_search_should_return_true() {
		$logs = EDD()->api_request_logs->get_logs( array(
			'search' => 'edd-api=info',
		) );

		$this->assertCount( 0, $logs );
	}

	/**
	 * @covers ::get_logs()
	 */
	public function test_get_logs_with_orderby_api_key_and_order_asc_should_return_true() {
		$logs = EDD()->api_request_logs->get_logs( array(
			'orderby' => 'api_key',
			'order'   => 'asc'
		) );

		$this->assertTrue( $logs[0]->api_key < $logs[1]->api_key );
	}

	/**
	 * @covers ::get_logs()
	 */
	public function test_get_logs_with_orderby_api_key_and_order_desc_should_return_true() {
		$logs = EDD()->api_request_logs->get_logs( array(
			'orderby' => 'api_key',
			'order'   => 'desc'
		) );

		$this->assertTrue( $logs[0]->api_key > $logs[1]->api_key );
	}

	/**
	 * @covers ::get_logs()
	 */
	public function test_get_logs_with_orderby_token_and_order_asc_should_return_true() {
		$logs = EDD()->api_request_logs->get_logs( array(
			'orderby' => 'token',
			'order'   => 'asc'
		) );

		$this->assertTrue( $logs[0]->token < $logs[1]->token );
	}

	/**
	 * @covers ::get_logs()
	 */
	public function test_get_logs_with_orderby_token_and_order_desc_should_return_true() {
		$logs = EDD()->api_request_logs->get_logs( array(
			'orderby' => 'token',
			'order'   => 'desc'
		) );

		$this->assertTrue( $logs[0]->token > $logs[1]->token );
	}

	/**
	 * @covers ::get_logs()
	 */
	public function test_get_logs_with_orderby_request_and_order_asc_should_return_true() {
		$logs = EDD()->api_request_logs->get_logs( array(
			'orderby' => 'request',
			'order'   => 'asc'
		) );

		$this->assertTrue( $logs[0]->request < $logs[1]->request );
	}

	/**
	 * @covers ::get_logs()
	 */
	public function test_get_logs_with_orderby_request_and_order_desc_should_return_true() {
		$logs = EDD()->api_request_logs->get_logs( array(
			'orderby' => 'request',
			'order'   => 'desc'
		) );

		$this->assertTrue( $logs[0]->request > $logs[1]->request );
	}

	/**
	 * @covers ::get_logs()
	 */
	public function test_get_logs_with_orderby_time_and_order_asc_should_return_true() {
		$logs = EDD()->api_request_logs->get_logs( array(
			'orderby' => 'time',
			'order'   => 'asc'
		) );

		$this->assertTrue( $logs[0]->time < $logs[1]->time );
	}

	/**
	 * @covers ::get_logs()
	 */
	public function test_get_logs_with_orderby_time_and_order_desc_should_return_true() {
		$logs = EDD()->api_request_logs->get_logs( array(
			'orderby' => 'time',
			'order'   => 'desc'
		) );

		$this->assertTrue( $logs[0]->time > $logs[1]->time );
	}

	/**
	 * @covers ::get_logs()
	 */
	public function test_get_logs_with_order_asc_should_return_true() {
		$logs = EDD()->api_request_logs->get_logs( array(
			'order' => 'asc',
		) );

		$this->assertTrue( $logs[0]->id < $logs[1]->id );
	}

	/**
	 * @covers ::get_logs()
	 */
	public function test_get_logs_with_order_desc_should_return_true() {
		$logs = EDD()->api_request_logs->get_logs( array(
			'order' => 'desc',
		) );

		$this->assertTrue( $logs[0]->id > $logs[1]->id );
	}

	/**
	 * @covers ::get_logs()
	 */
	public function test_get_logs_by_user_id_should_return_true() {
		$logs = EDD()->api_request_logs->get_logs( array(
			'user_id' => \WP_UnitTest_Generator_Sequence::$incr
		) );

		$this->assertCount( 1, $logs );
	}

	/**
	 * @covers ::get_logs()
	 */
	public function test_get_logs_by_invalid_user_id_should_return_true() {
		$logs = EDD()->api_request_logs->get_logs( array(
			'user_id' => 99999,
		) );

		$this->assertCount( 0, $logs );
	}

	/**
	 * @covers ::get_logs()
	 */
	public function test_get_logs_with_invalid_version_should_return_true() {
		$logs = EDD()->api_request_logs->get_logs( array(
			'version' => 'v99999',
		) );

		$this->assertCount( 0, $logs );
	}

	/**
	 * @covers ::get_logs()
	 */
	public function test_get_logs_with_invalid_api_key_should_return_true() {
		$logs = EDD()->api_request_logs->get_logs( array(
			'api_key' => 'b8062c469b938052f3bf4656999ee995b8062c469b938052f3bf4656999ee995',
		) );

		$this->assertCount( 0, $logs );
	}

	/**
	 * @covers ::get_logs()
	 */
	public function test_get_logs_with_invalid_token_should_return_true() {
		$logs = EDD()->api_request_logs->get_logs( array(
			'token' => 'b8062c469b938052f3bf4656999ee995b8062c469b938052f3bf4656999ee995',
		) );

		$this->assertCount( 0, $logs );
	}

	/**
	 * @covers ::get_logs()
	 */
	public function test_get_logs_with_invalid_request_should_return_true() {
		$logs = EDD()->api_request_logs->get_logs( array(
			'request' => 'foo',
		) );

		$this->assertCount( 0, $logs );
	}

	/**
	 * @covers ::get_logs()
	 */
	public function test_get_logs_with_invalid_ip_should_return_true() {
		$logs = EDD()->api_request_logs->get_logs( array(
			'ip' => '999.999.999.999',
		) );

		$this->assertCount( 0, $logs );
	}

	/**
	 * @covers ::count()
	 */
	public function test_count() {
		$this->assertEquals( 5, EDD()->api_request_logs->count() );
	}

}