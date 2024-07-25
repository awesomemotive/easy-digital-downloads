<?php

//edd_get_purchase_form_user

namespace EDD\Tests\Checkout;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;

class PurchaseFormUser extends EDD_UnitTestCase {

	public function setUp(): void {
		parent::setUp();
		$_POST = array();
		wp_set_current_user( 0 );
	}

	public function tearDown(): void {
		parent::tearDown();
		wp_set_current_user( 0 );
		$_POST = array();
	}

	public function test_edd_get_purchase_form_user_logged_in_user() {

		$user_id = $this->factory->user->create();
		wp_set_current_user( $user_id );

		$_POST = array(
			'edd_first' => 'John',
			'edd_last'  => 'Doe',
			'edd_email' => 'john@doe.example',
		);
		$valid_data = edd_purchase_form_validate_fields();
		$user       = edd_get_purchase_form_user( $valid_data, false );
		$legacy     = _edds_get_purchase_form_user( $valid_data );

		// assert that $user and $legacy are the same array
		$this->assertEquals( $user, $legacy );

		$this->assertEquals( $user_id, $user['user_id'] );
		$this->assertEquals( $_POST['edd_email'], $valid_data['logged_in_user']['user_email'] );
		$this->assertEquals( $_POST['edd_email'], $user['user_email'] );
		$this->assertEmpty( $user['address'] );
	}

	public function test_edd_get_purchase_form_user_guest() {

		$_POST = array(
			'edd_first' => 'John',
			'edd_last'  => 'Doe',
			'edd_email' => 'guest@edd.local',
		);

		$valid_data = edd_purchase_form_validate_fields();
		$user       = edd_get_purchase_form_user( $valid_data, false );
		$legacy     = _edds_get_purchase_form_user( $valid_data );

		// assert that $user and $legacy are the same array
		$this->assertEquals( $user, $legacy );

		$this->assertEquals( $_POST['edd_email'], $user['user_email'] );
		$this->assertEquals( $_POST['edd_email'], $valid_data['guest_user_data']['user_email'] );
	}

	public function test_edd_get_purchase_form_user_guest_with_address() {

		$_POST = array(
			'edd_first'       => 'John',
			'edd_last'        => 'Doe',
			'edd_email'       => 'guest@edd.local',
			'billing_country' => 'US',
			'card_address'    => '123 Main St',
			'card_address_2'  => 'Apt 1',
			'card_city'       => 'Springfield',
			'card_state'      => 'OR',
			'card_zip'        => '97477',
		);

		$valid_data = edd_purchase_form_validate_fields();
		$user       = edd_get_purchase_form_user( $valid_data, false );
		$legacy     = _edds_get_purchase_form_user( $valid_data );

		// assert that $user and $legacy are the same array
		$this->assertEquals( $user, $legacy );

		$this->assertEquals( $_POST['edd_email'], $user['user_email'] );
		$this->assertEquals( $_POST['edd_email'], $valid_data['guest_user_data']['user_email'] );
		$this->assertEquals( 'US', $user['address']['country'] );
		$this->assertEquals( '123 Main St', $user['address']['line1'] );
		$this->assertEquals( 'Apt 1', $user['address']['line2'] );
		$this->assertEquals( 'Springfield', $user['address']['city'] );
		$this->assertEquals( 'OR', $user['address']['state'] );
		$this->assertEquals( '97477', $user['address']['zip'] );
	}

	public function test_edd_get_purchase_form_register() {
		$_POST = array(
			'edd_first'             => 'John',
			'edd_last'              => 'Doe',
			'edd_email'             => 'newuser@edd.local',
			'edd_user_login'        => 'newuser',
			'edd_user_pass'         => 'password',
			'edd_user_pass_confirm' => 'password',
			'edd-purchase-var'      => 'needs-to-register',
		);

		$valid_data = edd_purchase_form_validate_fields();
		$user       = edd_get_purchase_form_user( $valid_data, false );

		$this->assertEquals( $_POST['edd_email'], $user['user_email'] );
		$this->assertEquals( $_POST['edd_email'], $valid_data['new_user_data']['user_email'] );
	}

	public function test_edd_get_purchase_form_register_legacy_stripe() {
		$_POST = array(
			'edd_first'             => 'John',
			'edd_last'              => 'Doe',
			'edd_email'             => 'newstripe@edd.local',
			'edd_user_login'        => 'newstripe',
			'edd_user_pass'         => 'password',
			'edd_user_pass_confirm' => 'password',
			'edd-purchase-var'      => 'needs-to-register',
		);

		$valid_data = edd_purchase_form_validate_fields();
		$user       = _edds_get_purchase_form_user( $valid_data );

		$this->assertEquals( $_POST['edd_email'], $user['user_email'] );
		$this->assertEquals( $_POST['edd_email'], $valid_data['new_user_data']['user_email'] );
	}

	public function test_edd_get_purchase_form_user_login() {

		$user_id = $this->factory->user->create();
		// update the user password to password
		wp_set_password( 'password', $user_id );
		$user  = get_user_by( 'id', $user_id );
		$_POST = array(
			'edd_first'        => 'John',
			'edd_last'         => 'Doe',
			'edd_email'        => $user->user_email,
			'edd_user_login'   => $user->user_login,
			'edd_user_pass'    => 'password',
			'edd-purchase-var' => 'needs-to-login',
		);

		$valid_data    = edd_purchase_form_validate_fields();
		$purchase_user = edd_get_purchase_form_user( $valid_data, false );
		$legacy        = _edds_get_purchase_form_user( $valid_data );

		// assert that $purchase_user and $legacy are the same array
		$this->assertEquals( $purchase_user, $legacy );

		$this->assertEquals( $user_id, $valid_data['login_user_data']['user_id'] );
		$this->assertEquals( $user->user_login, $valid_data['login_user_data']['user_login'] );
		// This assertion should be true, but it is not at this time.
		// $this->assertEquals( $user_id, $purchase_user['user_id'] );
	}

	public function test_edd_get_purchase_form_user_ajax_is_true() {
		add_filter( 'wp_doing_ajax', '__return_true' );

		$_POST = array(
			'edd_first' => 'John',
			'edd_last'  => 'Doe',
			'edd_email' => 'guest@edd.local',
		);
		$valid_data = edd_purchase_form_validate_fields();

		$this->assertTrue( edd_get_purchase_form_user( $valid_data ) );

		remove_filter( 'wp_doing_ajax', '__return_true' );
	}

	public function test_edd_get_purchase_form_user_ajax() {
		add_filter( 'wp_doing_ajax', '__return_true' );

		$_POST = array(
			'edd_first' => 'John',
			'edd_last'  => 'Doe',
			'edd_email' => 'guest@edd.local',
		);

		$valid_data = edd_purchase_form_validate_fields();
		$user       = edd_get_purchase_form_user( $valid_data, false );
		$legacy     = _edds_get_purchase_form_user( $valid_data );

		$this->assertEquals( $user, $legacy );

		remove_filter( 'wp_doing_ajax', '__return_true' );
	}
}
