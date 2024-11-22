<?php

namespace EDD\Tests\Session;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;
use EDD\Tests\Helpers;

class PurchaseData extends EDD_UnitTestCase {

	public function setUp(): void {
		parent::setUp();
		$_POST = array();
		wp_set_current_user( 0 );
	}

	public function tearDown(): void {
		parent::tearDown();
		wp_set_current_user( 0 );
		$_POST = array();
		edd_set_purchase_session( null );
	}

	public function test_edd_get_purchase_session_logged_in_user() {

		$user_id = $this->factory->user->create();
		wp_set_current_user( $user_id );

		$_POST = array(
			'edd_first' => 'John',
			'edd_last'  => 'Doe',
			'edd_email' => 'john@doe.example',
		);
		$purchase_session = \EDD\Sessions\PurchaseData::start( false );
		unset( $purchase_session['card_info'] );
		unset( $purchase_session['post_data'] );

		$this->assertEquals( $user_id, $purchase_session['user_info']['id'] );
		$this->assertEquals( $_POST['edd_email'], $purchase_session['user_info']['email'] );
		$this->assertEquals( $_POST['edd_email'], $purchase_session['user_email'] );

		$this->assertEquals( $purchase_session, \EDD\Sessions\PurchaseData::get() );
	}

	public function test_edd_get_purchase_session_guest() {

		$_POST = array(
			'edd_first' => 'John',
			'edd_last'  => 'Doe',
			'edd_email' => 'guest@edd.local',
		);

		$purchase_session = \EDD\Sessions\PurchaseData::start();
		unset( $purchase_session['card_info'] );
		unset( $purchase_session['post_data'] );

		$this->assertEmpty( $purchase_session['user_info']['id'] );
		$this->assertEquals( $_POST['edd_email'], $purchase_session['user_info']['email'] );
		$this->assertEquals( $_POST['edd_email'], $purchase_session['user_email'] );

		$this->assertEquals( $purchase_session, \EDD\Sessions\PurchaseData::get() );
	}

	public function test_edd_get_purchase_session_get() {

		$_POST = array(
			'edd_first' => 'John',
			'edd_last'  => 'Doe',
			'edd_email' => 'guest@edd.local',
		);

		$purchase_session = \EDD\Sessions\PurchaseData::get();

		$this->assertEmpty( $purchase_session['user_info']['id'] );
		$this->assertEquals( $_POST['edd_email'], $purchase_session['user_info']['email'] );
		$this->assertEquals( $_POST['edd_email'], $purchase_session['user_email'] );
	}

	public function test_edd_get_purchase_session_add_to_cart_is_null() {

		$_POST = array(
			'edd_first' => 'John',
			'edd_last'  => 'Doe',
			'edd_email' => 'guest2@edd.local',
		);

		$this->assertNotEmpty( \EDD\Sessions\PurchaseData::get() );

		$download = Helpers\EDD_Helper_Download::create_simple_download();
		edd_add_to_cart( $download->ID );

		$this->assertEmpty( edd_get_purchase_session() );
	}
}
