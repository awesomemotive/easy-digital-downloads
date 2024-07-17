<?php
namespace EDD\Tests\Sessions;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;
use EDD\Tests\Helpers\EDD_Helper_Payment;

/**
 * Legacy `EDD_Session` class was refactored and moved to the new `EDD\Sessions\Handler` class.
 * These tests check if the legacy class is still working.
 *
 * @since 3.3.0
 */
class Deprecated extends EDD_UnitTestCase {

	private static $order;

	private static $session;

	public function test_set() {
		$this->assertEquals( 'bar', $this->get_session()->set( 'foo', 'bar' ) );
	}

	public function test_get() {
		$this->assertEquals( 'bar', $this->get_session()->get( 'foo' ) );
	}

	public function test_set_new_order_purchase_key_in_session() {
		$order            = $this->get_order();
		$purchase_session = array(
			'purchase_key' => $order->payment_key,
		);
		edd_set_purchase_session( $purchase_session );

		$session = edd_get_purchase_session();
		$this->assertEquals( $order->payment_key, $session['purchase_key'] );
	}

	public function test_should_start_session() {

		$blacklist = $this->get_session()->get_blacklist();

		foreach( $blacklist as $uri ) {
			$this->go_to( '/' . $uri );
			$this->assertFalse( $this->get_session()->should_start_session() );
		}
	}

	private function get_order() {
		if ( is_null( self::$order ) ) {
			$order_id    = EDD_Helper_Payment::create_simple_payment();
			self::$order = edd_get_order( $order_id );
		}

		return self::$order;
	}

	private function get_session() {
		if ( is_null( self::$session ) ) {
			self::$session = new \EDD_Session();
		}

		return self::$session;
	}
}
