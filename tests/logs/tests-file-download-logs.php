<?php
namespace EDD\Logs;

/**
 * File Download Logs DB Tests
 *
 * @group edd_logs_db
 * @group database
 * @group edd_logs
 *
 * @coversDefaultClass \EDD_DB_Logs_File_Downloads
 */
class File_Downloads_Logs_Tests extends \EDD_UnitTestCase {

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
		self::$logs = parent::edd()->file_download_log->create_many( 5 );
	}

	/**
	 * @covers ::update()
	 */
	public function test_update_should_return_true() {
		$success = edd_update_file_download_log( self::$logs[0], array(
			'ip' => '10.0.0.1',
		) );

		$this->assertSame( 1, $success );
	}

	/**
	 * @covers ::update()
	 */
	public function test_log_object_after_update_should_return_true() {
		$success = edd_update_file_download_log( self::$logs[0], array(
			'ip' => '10.0.0.1',
		) );

		$log = new File_Download_Log( self::$logs[0] );

		$this->assertEquals( '10.0.0.1', $log->ip );
	}

	/**
	 * @covers \EDD_DB_Logs::update()
	 */
	public function test_update_without_id_should_fail() {
		$success = edd_update_file_download_log( null, array(
			'ip' => '10.0.0.1',
		) );

		$this->assertFalse( $success );
	}

	/**
	 * @covers ::delete()
	 */
	public function test_delete_should_return_true() {
		$success = edd_delete_file_download_log( self::$logs[0] );

		$this->assertSame( 1, $success );
	}

	/**
	 * @covers ::delete()
	 */
	public function test_delete_without_id_should_fail() {
		$success = edd_delete_file_download_log( '' );

		$this->assertFalse( $success );
	}

	/**
	 * @covers ::get_logs()
	 */
	public function test_get_logs_with_number_should_return_true() {
		$logs = edd_get_file_download_logs( array(
			'number' => 10,
		) );

		$this->assertCount( 5, $logs );
	}

	/**
	 * @covers ::get_logs()
	 */
	public function test_get_logs_with_offset_should_return_true() {
		$logs = edd_get_file_download_logs( array(
			'number' => 10,
			'offset' => 4,
		) );

		$this->assertCount( 1, $logs );
	}

	/**
	 * @covers ::get_logs()
	 */
	public function test_get_logs_with_search_should_return_true() {
		$logs = edd_get_file_download_logs( array(
			'search'         => '@edd.test',
			'search_columns' => array( 'email' ),
		) );

		$this->assertCount( 5, $logs );
	}

	/**
	 * @covers ::get_logs()
	 */
	public function test_get_logs_with_orderby_download_id_and_order_asc_should_return_true() {
		$logs = edd_get_file_download_logs( array(
			'orderby' => 'download_id',
			'order'   => 'asc'
		) );

		$this->assertTrue( $logs[0]->download_id < $logs[1]->download_id );
	}

	/**
	 * @covers ::get_logs()
	 */
	public function test_get_logs_with_orderby_download_id_and_order_desc_should_return_true() {
		$logs = edd_get_file_download_logs( array(
			'orderby' => 'download_id',
			'order'   => 'desc'
		) );

		$this->assertTrue( $logs[0]->download_id > $logs[1]->download_id );
	}

	/**
	 * @covers ::get_logs()
	 */
	public function test_get_logs_with_orderby_email_and_order_asc_should_return_true() {
		$logs = edd_get_file_download_logs( array(
			'orderby' => 'email',
			'order'   => 'asc'
		) );

		$this->assertTrue( $logs[0]->email < $logs[1]->email );
	}

	/**
	 * @covers ::get_logs()
	 */
	public function test_get_logs_with_orderby_email_and_order_desc_should_return_true() {
		$logs = edd_get_file_download_logs( array(
			'orderby' => 'email',
			'order'   => 'desc'
		) );

		$this->assertTrue( $logs[0]->email > $logs[1]->email );
	}

	/**
	 * @covers ::get_logs()
	 */
	public function test_get_logs_with_order_asc_should_return_true() {
		$logs = edd_get_file_download_logs( array(
			'order' => 'asc',
		) );

		$this->assertTrue( $logs[0]->id < $logs[1]->id );
	}

	/**
	 * @covers ::get_logs()
	 */
	public function test_get_logs_with_order_desc_should_return_true() {
		$logs = edd_get_file_download_logs( array(
			'order' => 'desc',
		) );

		$this->assertTrue( $logs[0]->id > $logs[1]->id );
	}

	/**
	 * @covers ::get_logs()
	 */
	public function test_get_logs_by_download_id_should_return_true() {
		$logs = edd_get_file_download_logs( array(
			'download_id' => \WP_UnitTest_Generator_Sequence::$incr
		) );

		$this->assertCount( 1, $logs );
	}

	/**
	 * @covers ::get_logs()
	 */
	public function test_get_logs_by_invalid_object_id_should_return_true() {
		$logs = edd_get_file_download_logs( array(
			'download_id' => 99999,
		) );

		$this->assertCount( 0, $logs );
	}

	/**
	 * @covers ::get_logs()
	 */
	public function test_get_logs_with_invalid_payment_id_should_return_true() {
		$logs = edd_get_file_download_logs( array(
			'payment_id' => 99999,
		) );

		$this->assertCount( 0, $logs );
	}

	/**
	 * @covers ::get_logs()
	 */
	public function test_get_logs_with_invalid_file_id_should_return_true() {
		$logs = edd_get_file_download_logs( array(
			'file_id' => 99999,
		) );

		$this->assertCount( 0, $logs );
	}

	/**
	 * @covers ::get_logs()
	 */
	public function test_get_logs_with_invalid_price_id_should_return_true() {
		$logs = edd_get_file_download_logs( array(
			'price_id' => 99999,
		) );

		$this->assertCount( 0, $logs );
	}

	/**
	 * @covers ::count()
	 */
	public function test_count() {
		$this->assertEquals( 5, edd_count_file_download_logs() );
	}

}