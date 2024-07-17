<?php
namespace EDD\Tests\Sessions;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;
use EDD\Tests\Helpers\EDD_Helper_Payment;

class Handler extends EDD_UnitTestCase {

	private static $order;

	public static function setUpBeforeClass(): void {
		parent::setUpBeforeClass();

		$order_id    = EDD_Helper_Payment::create_simple_payment();
		self::$order = edd_get_order( $order_id );
	}

	public function test_session_type_is_db() {
		$this->assertfalse( EDD()->session->use_php_sessions() );
	}

	public function test_session_component_exists() {
		$component = edd_get_component( 'session' );
		$this->assertInstanceOf( '\\EDD\\Component', $component );
	}

	public function test_set() {
		$this->assertEquals( 'bar', EDD()->session->set( 'foo', 'bar' ) );
	}

	public function test_get() {
		$this->assertEquals( 'bar', EDD()->session->get( 'foo' ) );
	}

	public function test_set_new_order_purchase_key_in_session() {
		$purchase_session =array(
			'purchase_key' => self::$order->payment_key,
		);
		edd_set_purchase_session( $purchase_session );

		$session = edd_get_purchase_session();
		$this->assertEquals( self::$order->payment_key, $session['purchase_key'] );
	}

	public function test_should_start_session() {

		$blacklist = EDD()->session->get_blacklist();

		foreach( $blacklist as $uri ) {
			$this->go_to( '/' . $uri );
			$this->assertFalse( EDD()->session->should_start_session() );
		}
	}

	public function test_use_php_sessions_is_false() {
		delete_option( 'edd_session_handling' );
		$this->assertfalse( EDD()->session->use_php_sessions() );
	}
}
