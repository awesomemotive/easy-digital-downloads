<?php

namespace EDD\Tests\Session;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;

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
		$valid_data = edd_purchase_form_validate_fields();
		$user       = edd_get_purchase_form_user( $valid_data, false );
		\EDD\Sessions\PurchaseData::set( $valid_data, $user );

		$purchase_session = edd_get_purchase_session();

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

		$valid_data = edd_purchase_form_validate_fields();
		$user       = edd_get_purchase_form_user( $valid_data, false );
		\EDD\Sessions\PurchaseData::set( $valid_data, $user );

		$purchase_session = edd_get_purchase_session();

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
}
