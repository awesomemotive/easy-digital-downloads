<?php
namespace EDD_Unit_Tests;

/**
 * @group edd_ajax
 */
class Tests_AJAX extends EDD_UnitTestCase {
	protected $_post = null;

	protected $_last_response;

	public function setUp() {
		parent::setUp();

		$_actions = array(
			'edd_remove_from_cart', 'edd_add_to_cart', 'edd_apply_discount', 'checkout_login',
			'checkout_register', 'get_download_title', 'edd_local_tax_opt_in', 'edd_local_tax_opt_out',
			'edd_check_for_download_price_variations'
		);

		foreach ( $_actions as $action ) {
			if ( function_exists( 'wp_ajax_' . $action ) )
				add_action( 'wp_ajax_' . $action, $action, 1 );
		}

		if ( ! defined( 'DOING_AJAX' ) )
			define( 'DOING_AJAX', true );
		set_current_screen( 'ajax' );

		add_action( 'clear_auth_cookie', array( $this, 'logout' ) );

		$this->factory->post->create_many( 5 );

		error_reporting( 0 & ~E_WARNING );
	}

	public function tearDown() {
		parent::tearDown();
		$_POST = array();
		remove_filter( 'wp_die_ajax_handler', array( $this, 'getDieHandler' ), 1, 1 );
		remove_action( 'clear_auth_cookie', array( $this, 'logout' ) );
		set_current_screen( 'front' );
		EDD()->session->set( 'edd_cart', null );
	}

	public function logout() {
		unset( $GLOBALS['current_user'] );
		$cookies = array(AUTH_COOKIE, SECURE_AUTH_COOKIE, LOGGED_IN_COOKIE, USER_COOKIE, PASS_COOKIE);
		foreach ( $cookies as $c )
			unset( $_COOKIE[ $c ] );
	}
}
