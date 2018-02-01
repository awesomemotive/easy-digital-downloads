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
	 * Set up fixtures once.
	 */
	public static function wpSetUpBeforeClass() {
		self::$object = new EDD_Logging();
	}

	public function test_valid_log() {
		$this->assertTrue( self::$object->valid_type( 'file_download' ) );
	}

	public function test_fake_log() {
		$this->assertFalse( self::$object->valid_type( 'foo' ) );
	}

	public function test_add() {
		$this->assertNotNull( self::$object->add() );
		$this->assertInternalType( 'integer', self::$object->add() );
	}

	public function test_insert_log() {
		$this->assertNotNull( self::$object->insert_log( array( 'log_type' => 'sale' ) ) );
		$this->assertInternalType( 'integer', self::$object->insert_log( array( 'log_type' => 'sale' ) ) );
	}

	public function test_get_logs() {
		$log_id = self::$object->insert_log( array( 'log_type' => 'sale', 'post_parent' => 1, 'post_title' => 'Test Log', 'post_content' => 'This is a test log inserted from PHPUnit' ) );
		$out = self::$object->get_logs( 1, 'sale' );

		$this->assertObjectHasAttribute( 'ID', $out[0] );
		$this->assertObjectHasAttribute( 'post_author', $out[0] );
		$this->assertObjectHasAttribute( 'post_date', $out[0] );
		$this->assertObjectHasAttribute( 'post_date_gmt', $out[0] );
		$this->assertObjectHasAttribute( 'post_content', $out[0] );
		$this->assertObjectHasAttribute( 'post_title', $out[0] );
		$this->assertObjectHasAttribute( 'post_excerpt', $out[0] );
		$this->assertObjectHasAttribute( 'post_status', $out[0] );
		$this->assertObjectHasAttribute( 'comment_status', $out[0] );
		$this->assertObjectHasAttribute( 'ping_status', $out[0] );
		$this->assertObjectHasAttribute( 'post_password', $out[0] );
		$this->assertObjectHasAttribute( 'post_name', $out[0] );
		$this->assertObjectHasAttribute( 'to_ping', $out[0] );
		$this->assertObjectHasAttribute( 'pinged', $out[0] );
		$this->assertObjectHasAttribute( 'post_modified', $out[0] );
		$this->assertObjectHasAttribute( 'post_modified_gmt', $out[0] );
		$this->assertObjectHasAttribute( 'post_content_filtered', $out[0] );
		$this->assertObjectHasAttribute( 'post_parent', $out[0] );
		$this->assertObjectHasAttribute( 'guid', $out[0] );
		$this->assertObjectHasAttribute( 'menu_order', $out[0] );
		$this->assertObjectHasAttribute( 'post_type', $out[0] );
		$this->assertObjectHasAttribute( 'post_mime_type', $out[0] );
		$this->assertObjectHasAttribute( 'comment_count', $out[0] );
		$this->assertObjectHasAttribute( 'filter', $out[0] );

		$this->assertEquals( 'This is a test log inserted from PHPUnit', $out[0]->post_content );
		$this->assertEquals( 'Test Log', $out[0]->post_title );
		$this->assertEquals( 'edd_log', $out[0]->post_type );
	}

	public function test_get_connected_logs() {
		$log_id = self::$object->insert_log( array( 'log_type' => 'sale', 'post_parent' => 1, 'post_title' => 'Test Log', 'post_content' => 'This is a test log inserted from PHPUnit' ) );
		$out = self::$object->get_connected_logs( array( 'post_parent' => 1, 'log_type' => 'sale' ) );

		$this->assertObjectHasAttribute( 'ID', $out[0] );
		$this->assertObjectHasAttribute( 'post_author', $out[0] );
		$this->assertObjectHasAttribute( 'post_date', $out[0] );
		$this->assertObjectHasAttribute( 'post_date_gmt', $out[0] );
		$this->assertObjectHasAttribute( 'post_content', $out[0] );
		$this->assertObjectHasAttribute( 'post_title', $out[0] );
		$this->assertObjectHasAttribute( 'post_excerpt', $out[0] );
		$this->assertObjectHasAttribute( 'post_status', $out[0] );
		$this->assertObjectHasAttribute( 'comment_status', $out[0] );
		$this->assertObjectHasAttribute( 'ping_status', $out[0] );
		$this->assertObjectHasAttribute( 'post_password', $out[0] );
		$this->assertObjectHasAttribute( 'post_name', $out[0] );
		$this->assertObjectHasAttribute( 'to_ping', $out[0] );
		$this->assertObjectHasAttribute( 'pinged', $out[0] );
		$this->assertObjectHasAttribute( 'post_modified', $out[0] );
		$this->assertObjectHasAttribute( 'post_modified_gmt', $out[0] );
		$this->assertObjectHasAttribute( 'post_content_filtered', $out[0] );
		$this->assertObjectHasAttribute( 'post_parent', $out[0] );
		$this->assertObjectHasAttribute( 'guid', $out[0] );
		$this->assertObjectHasAttribute( 'menu_order', $out[0] );
		$this->assertObjectHasAttribute( 'post_type', $out[0] );
		$this->assertObjectHasAttribute( 'post_mime_type', $out[0] );
		$this->assertObjectHasAttribute( 'comment_count', $out[0] );
		$this->assertObjectHasAttribute( 'filter', $out[0] );

		$this->assertEquals( 'This is a test log inserted from PHPUnit', $out[0]->post_content );
		$this->assertEquals( 'Test Log', $out[0]->post_title );
		$this->assertEquals( 'edd_log', $out[0]->post_type );
	}

	public function test_get_log_count() {
		self::$object->insert_log( array( 'log_type' => 'sale', 'post_parent' => 1, 'post_title' => 'Test Log', 'post_content' => 'This is a test log inserted from PHPUnit' ) );
		self::$object->insert_log( array( 'log_type' => 'sale', 'post_parent' => 1, 'post_title' => 'Test Log', 'post_content' => 'This is a test log inserted from PHPUnit' ) );
		self::$object->insert_log( array( 'log_type' => 'sale', 'post_parent' => 1, 'post_title' => 'Test Log', 'post_content' => 'This is a test log inserted from PHPUnit' ) );
		self::$object->insert_log( array( 'log_type' => 'sale', 'post_parent' => 1, 'post_title' => 'Test Log', 'post_content' => 'This is a test log inserted from PHPUnit' ) );
		self::$object->insert_log( array( 'log_type' => 'sale', 'post_parent' => 1, 'post_title' => 'Test Log', 'post_content' => 'This is a test log inserted from PHPUnit' ) );

		$this->assertInternalType( 'integer', self::$object->get_log_count( 1, 'sale' ) );
		$this->assertEquals( 5, self::$object->get_log_count( 1, 'sale' ) );
	}

	public function test_delete_logs() {
		self::$object->insert_log( array( 'log_type' => 'sale', 'post_parent' => 1, 'post_title' => 'Test Log', 'post_content' => 'This is a test log inserted from PHPUnit' ) );
		self::$object->insert_log( array( 'log_type' => 'sale', 'post_parent' => 1, 'post_title' => 'Test Log', 'post_content' => 'This is a test log inserted from PHPUnit' ) );
		self::$object->insert_log( array( 'log_type' => 'sale', 'post_parent' => 1, 'post_title' => 'Test Log', 'post_content' => 'This is a test log inserted from PHPUnit' ) );
		self::$object->insert_log( array( 'log_type' => 'sale', 'post_parent' => 1, 'post_title' => 'Test Log', 'post_content' => 'This is a test log inserted from PHPUnit' ) );
		self::$object->insert_log( array( 'log_type' => 'sale', 'post_parent' => 1, 'post_title' => 'Test Log', 'post_content' => 'This is a test log inserted from PHPUnit' ) );

		$this->assertNull( self::$object->delete_logs( 1 ) );
	}
}
