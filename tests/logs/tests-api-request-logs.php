<?php
namespace EDD\Logs;

/**
 * API Request Log DB Tests
 *
 * @group edd_logs_db
 * @group database
 * @group edd_logs
 *
 * @coversDefaultClass \EDD_Log_API_Request_Query
 */
class Api_Request_Logs_Tests extends \EDD_UnitTestCase {

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
	 * @covers ::query()
	 */
	public function test_get_logs_with_user_id_should_return_1() {
		$logs = edd_get_api_request_logs( array(
			'user_id' => \WP_UnitTest_Generator_Sequence::$incr
		) );

		$this->assertCount( 1, $logs );
	}

	/**
	 * @covers ::query()
	 */
	public function test_get_logs_with_user_id__in_should_return_2() {
		$logs = edd_get_api_request_logs( array(
			'user_id__in' => array( \WP_UnitTest_Generator_Sequence::$incr, \WP_UnitTest_Generator_Sequence::$incr - 1 )
		) );

		$this->assertCount( 2, $logs );
	}

	/**
	 * @covers ::query()
	 */
	public function test_get_logs_with_user_id__not_in_should_return_3() {
		$logs = edd_get_api_request_logs( array(
			'user_id__not_in' => array( \WP_UnitTest_Generator_Sequence::$incr, \WP_UnitTest_Generator_Sequence::$incr - 1 )
		) );

		$this->assertCount( 3, $logs );
	}

	/**
	 * @covers ::query()
	 */
	public function test_get_logs_with_api_key_should_return_1() {
		$logs = edd_get_api_request_logs( array(
			'api_key' => 'b8062c469b938352k3bg4656999ed' . \WP_UnitTest_Generator_Sequence::$incr
		) );

		$this->assertCount( 1, $logs );
	}

	/**
	 * @covers ::query()
	 */
	public function test_get_logs_with_api_key__in_should_return_2() {
		$logs = edd_get_api_request_logs( array(
			'api_key__in' => array(
				'b8062c469b938352k3bg4656999ed' . \WP_UnitTest_Generator_Sequence::$incr,
				'b8062c469b938352k3bg4656999ed' . (\WP_UnitTest_Generator_Sequence::$incr - 1)
			)
		) );

		$this->assertCount( 2, $logs );
	}

	/**
	 * @covers ::query()
	 */
	public function test_get_logs_with_api_key__not_in_should_return_2() {
		$logs = edd_get_api_request_logs( array(
			'api_key__not_in' => array(
				'b8062c469b938352k3bg4656999ed' . \WP_UnitTest_Generator_Sequence::$incr,
				'b8062c469b938352k3bg4656999ed' . (\WP_UnitTest_Generator_Sequence::$incr - 1)
			)
		) );

		$this->assertCount( 3, $logs );
	}

	/**
	 * @covers ::query()
	 */
	public function test_get_logs_with_token_should_return_1() {
		$logs = edd_get_api_request_logs( array(
			'token' => 'b8062c469b938352k3bg4656999ed' . \WP_UnitTest_Generator_Sequence::$incr
		) );

		$this->assertCount( 1, $logs );
	}

	/**
	 * @covers ::query()
	 */
	public function test_get_logs_with_token__in_should_return_2() {
		$logs = edd_get_api_request_logs( array(
			'token__in' => array(
				'b8062c469b938352k3bg4656999ed' . \WP_UnitTest_Generator_Sequence::$incr,
				'b8062c469b938352k3bg4656999ed' . (\WP_UnitTest_Generator_Sequence::$incr - 1)
			)
		) );

		$this->assertCount( 2, $logs );
	}

	/**
	 * @covers ::query()
	 */
	public function test_get_logs_with_token__not_in_should_return_2() {
		$logs = edd_get_api_request_logs( array(
			'token__not_in' => array(
				'b8062c469b938352k3bg4656999ed' . \WP_UnitTest_Generator_Sequence::$incr,
				'b8062c469b938352k3bg4656999ed' . (\WP_UnitTest_Generator_Sequence::$incr - 1)
			)
		) );

		$this->assertCount( 3, $logs );
	}

	/**
	 * @covers ::query()
	 */
	public function test_get_logs_with_ip_should_return_5() {
		$logs = edd_get_api_request_logs( array(
			'ip' => '10.1.1.1'
		) );

		$this->assertCount( 5, $logs );
	}

	/**
	 * @covers ::query()
	 */
	public function test_get_logs_with_ip__in_should_return_5() {
		$logs = edd_get_api_request_logs( array(
			'ip__in' => array( '10.1.1.1' )
		) );

		$this->assertCount( 5, $logs );
	}

	/**
	 * @covers ::query()
	 */
	public function test_get_logs_with_ip__not_in_should_return_0() {
		$logs = edd_get_api_request_logs( array(
			'ip__not_in' => array( '10.1.1.1' )
		) );

		$this->assertCount( 0, $logs );
	}

	/**
	 * @covers ::query()
	 */
	public function test_get_logs_with_time_should_return_1() {
		$logs = edd_get_api_request_logs( array(
			'time' => '0.0' . \WP_UnitTest_Generator_Sequence::$incr
		) );

		$this->assertCount( 1, $logs );
	}

	/**
	 * @covers ::query()
	 */
	public function test_get_logs_with_time__in_should_return_2() {
		$logs = edd_get_api_request_logs( array(
			'time__in' => array(
				'0.0' . \WP_UnitTest_Generator_Sequence::$incr,
				'0.0' . (\WP_UnitTest_Generator_Sequence::$incr - 1)
			)
		) );

		$this->assertCount( 2, $logs );
	}

	/**
	 * @covers ::query()
	 */
	public function test_get_logs_with_time__not_in_should_return_3() {
		$logs = edd_get_api_request_logs( array(
			'time__not_in' => array(
				'0.0' . \WP_UnitTest_Generator_Sequence::$incr,
				'0.0' . (\WP_UnitTest_Generator_Sequence::$incr - 1)
			)
		) );

		$this->assertCount( 3, $logs );
	}

	/**
	 * @covers ::update_item()
	 */
	public function test_update_should_return_true() {
		$success = edd_update_api_request_log( self::$logs[0], array(
			'ip' => '10.0.0.1',
		) );

		$this->assertSame( 1, $success );
	}

	/**
	 * @covers ::update_item()
	 */
	public function test_log_object_after_update_should_return_true() {
		$success = edd_update_api_request_log( self::$logs[0], array(
			'ip' => '10.0.0.1',
		) );

		$log = new Api_Request_Log( self::$logs[0] );

		$this->assertEquals( '10.0.0.1', $log->ip );
	}

	/**
	 * @covers ::update_item()
	 */
	public function test_update_without_ip_should_fail() {
		$success = edd_update_api_request_log( null, array(
			'ip' => '10.0.0.1',
		) );

		$this->assertFalse( $success );
	}

	/**
	 * @covers ::delete_item()
	 */
	public function test_delete_should_return_true() {
		$success = edd_delete_api_request_log( self::$logs[0] );

		$this->assertSame( 1, $success );
	}

	/**
	 * @covers ::delete_item()
	 */
	public function test_delete_without_id_should_fail() {
		$success = edd_delete_api_request_log( '' );

		$this->assertFalse( $success );
	}

	/**
	 * @covers ::query()
	 */
	public function test_get_logs_with_small_number_should_return_true() {
		$logs = edd_get_api_request_logs( array(
			'number' => 2,
		) );

		$this->assertCount( 2, $logs );
	}

	/**
	 * @covers ::query()
	 */
	public function test_get_logs_with_number_should_return_true() {
		$logs = edd_get_api_request_logs( array(
			'number' => 10,
		) );

		$this->assertCount( 5, $logs );
	}

	/**
	 * @covers ::query()
	 */
	public function test_get_logs_with_offset_should_return_true() {
		$logs = edd_get_api_request_logs( array(
			'number' => 10,
			'offset' => 4,
		) );

		$this->assertCount( 1, $logs );
	}

	/**
	 * @covers ::query()
	 */
	public function test_get_logs_with_search_should_return_true() {
		$logs = edd_get_api_request_logs( array(
			'search' => 'edd-api=sales',
		) );

		$this->assertCount( 5, $logs );
	}

	/**
	 * @covers ::query()
	 */
	public function test_get_logs_with_search_with_orderby_should_return_true() {
		$logs = edd_get_api_request_logs( array(
			'search'  => 'edd-api=sales',
			'orderby' => 'api_key',
		) );

		$this->assertTrue( $logs[0]->api_key > $logs[1]->api_key );
	}

	/**
	 * @covers ::query()
	 */
	public function test_get_logs_with_search_with_orderby_and_order_desc_should_return_true() {
		$logs = edd_get_api_request_logs( array(
			'search'  => 'edd-api=sales',
			'orderby' => 'api_key',
			'order'   => 'desc'
		) );

		$this->assertTrue( $logs[0]->api_key > $logs[1]->api_key );
	}

	/**
	 * @covers ::query()
	 */
	public function test_get_logs_with_search_with_orderby_and_order_asc_should_return_true() {
		$logs = edd_get_api_request_logs( array(
			'search'  => 'edd-api=sales',
			'orderby' => 'api_key',
			'order'   => 'asc'
		) );

		$this->assertTrue( $logs[0]->api_key < $logs[1]->api_key );
	}

	/**
	 * @covers ::query()
	 */
	public function test_get_logs_with_invalid_search_should_return_true() {
		$logs = edd_get_api_request_logs( array(
			'search' => 'edd-api=info',
		) );

		$this->assertCount( 0, $logs );
	}

	/**
	 * @covers ::query()
	 */
	public function test_get_logs_with_orderby_api_key_and_order_asc_should_return_true() {
		$logs = edd_get_api_request_logs( array(
			'orderby' => 'api_key',
			'order'   => 'asc'
		) );

		$this->assertTrue( $logs[0]->api_key < $logs[1]->api_key );
	}

	/**
	 * @covers ::query()
	 */
	public function test_get_logs_with_orderby_api_key_and_order_desc_should_return_true() {
		$logs = edd_get_api_request_logs( array(
			'orderby' => 'api_key',
			'order'   => 'desc'
		) );

		$this->assertTrue( $logs[0]->api_key > $logs[1]->api_key );
	}

	/**
	 * @covers ::query()
	 */
	public function test_get_logs_with_orderby_token_and_order_asc_should_return_true() {
		$logs = edd_get_api_request_logs( array(
			'orderby' => 'token',
			'order'   => 'asc'
		) );

		$this->assertTrue( $logs[0]->token < $logs[1]->token );
	}

	/**
	 * @covers ::query()
	 */
	public function test_get_logs_with_orderby_token_and_order_desc_should_return_true() {
		$logs = edd_get_api_request_logs( array(
			'orderby' => 'token',
			'order'   => 'desc'
		) );

		$this->assertTrue( $logs[0]->token > $logs[1]->token );
	}

	/**
	 * @covers ::query()
	 */
	public function test_get_logs_with_orderby_request_and_order_asc_should_return_true() {
		$logs = edd_get_api_request_logs( array(
			'orderby' => 'request',
			'order'   => 'asc'
		) );

		$this->assertTrue( $logs[0]->request < $logs[1]->request );
	}

	/**
	 * @covers ::query()
	 */
	public function test_get_logs_with_orderby_request_and_order_desc_should_return_true() {
		$logs = edd_get_api_request_logs( array(
			'orderby' => 'request',
			'order'   => 'desc'
		) );

		$this->assertTrue( $logs[0]->request > $logs[1]->request );
	}

	/**
	 * @covers ::query()
	 */
	public function test_get_logs_with_orderby_time_and_order_asc_should_return_true() {
		$logs = edd_get_api_request_logs( array(
			'orderby' => 'time',
			'order'   => 'asc'
		) );

		$this->assertTrue( $logs[0]->time < $logs[1]->time );
	}

	/**
	 * @covers ::query()
	 */
	public function test_get_logs_with_orderby_time_and_order_desc_should_return_true() {
		$logs = edd_get_api_request_logs( array(
			'orderby' => 'time',
			'order'   => 'desc'
		) );

		$this->assertTrue( $logs[0]->time > $logs[1]->time );
	}

	/**
	 * @covers ::query()
	 */
	public function test_get_logs_with_order_asc_should_return_true() {
		$logs = edd_get_api_request_logs( array(
			'order' => 'asc',
		) );

		$this->assertTrue( $logs[0]->id < $logs[1]->id );
	}

	/**
	 * @covers ::query()
	 */
	public function test_get_logs_with_order_desc_should_return_true() {
		$logs = edd_get_api_request_logs( array(
			'order' => 'desc',
		) );

		$this->assertTrue( $logs[0]->id > $logs[1]->id );
	}

	/**
	 * @covers ::query()
	 */
	public function test_get_logs_by_user_id_should_return_true() {
		$logs = edd_get_api_request_logs( array(
			'user_id' => \WP_UnitTest_Generator_Sequence::$incr
		) );

		$this->assertCount( 1, $logs );
	}

	/**
	 * @covers ::query()
	 */
	public function test_get_logs_by_invalid_user_id_should_return_true() {
		$logs = edd_get_api_request_logs( array(
			'user_id' => 99999,
		) );

		$this->assertCount( 0, $logs );
	}

	/**
	 * @covers ::query()
	 */
	public function test_get_logs_with_invalid_version_should_return_true() {
		$logs = edd_get_api_request_logs( array(
			'version' => 'v99999',
		) );

		$this->assertCount( 0, $logs );
	}

	/**
	 * @covers ::query()
	 */
	public function test_get_logs_with_invalid_api_key_should_return_true() {
		$logs = edd_get_api_request_logs( array(
			'api_key' => 'b8062c469b938052f3bf4656999ee995b8062c469b938052f3bf4656999ee995',
		) );

		$this->assertCount( 0, $logs );
	}

	/**
	 * @covers ::query()
	 */
	public function test_get_logs_with_invalid_token_should_return_true() {
		$logs = edd_get_api_request_logs( array(
			'token' => 'b8062c469b938052f3bf4656999ee995b8062c469b938052f3bf4656999ee995',
		) );

		$this->assertCount( 0, $logs );
	}

	/**
	 * @covers ::query()
	 */
	public function test_get_logs_with_invalid_request_should_return_true() {
		$logs = edd_get_api_request_logs( array(
			'request' => 'foo',
		) );

		$this->assertCount( 0, $logs );
	}

	/**
	 * @covers ::query()
	 */
	public function test_get_logs_with_invalid_ip_should_return_true() {
		$logs = edd_get_api_request_logs( array(
			'ip' => '999.999.999.999',
		) );

		$this->assertCount( 0, $logs );
	}

	/**
	 * @covers ::count()
	 */
	public function test_count() {
		$this->assertEquals( 5, edd_count_api_request_logs() );
	}

}