<?php


/**
 * @group edd_session
 */
class Tests_Session extends WP_UnitTestCase {
	public function setUp() {
		parent::setUp();
		new \EDD_Session;
	}

	public function tearDown() {
		parent::tearDown();
	}

	public function test_set() {
		$this->assertEquals( 'bar', EDD()->session->set( 'foo', 'bar' ) );
	}

	public function test_get() {
		$this->assertEquals( 'bar', EDD()->session->get( 'foo' ) );
	}
}