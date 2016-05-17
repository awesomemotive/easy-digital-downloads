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

	public function test_use_cart_cookie() {
		$this->assertTrue( EDD()->session->use_cart_cookie() );
		define( 'EDD_USE_CART_COOKIE', false );
		$this->assertFalse( EDD()->session->use_cart_cookie());
	}

	public function test_should_start_session() {

		$blacklist = EDD()->session->get_blacklist();

		foreach( $blacklist as $uri ) {

			$this->go_to( '/' . $uri );
			$this->assertFalse( EDD()->session->should_start_session() );

		}

	}
}