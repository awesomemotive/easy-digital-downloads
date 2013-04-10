<?php
/**
 * EDD AJAX Test Cases
 *
 * Taken from WordPress Unit Tests and adapted for Easy Digital
 * Downloads by Sunny Ratilal.
 *
 * Edit: Sunny Ratilal, April 2013
 */
require_once('./tests/vendor/wordpress/wp-admin/includes/screen.php');
require_once('./tests/vendor/wordpress/wp-includes/plugin.php');
require_once('./tests/vendor/wordpress-tests/lib/factory.php');

class Test_Easy_Digital_Downloads_AJAX extends WP_UnitTestCase {
	protected $_post = null;

	protected $_last_response;

	public function setUp() {
		parent::setUp();

		$wp_factory = new WP_UnitTest_Factory;

		$_actions = array(
			'edd_remove_from_cart', 'edd_add_to_cart', 'edd_apply_discount', 'checkout_login',
			'checkout_register', 'get_download_title', 'edd_local_tax_opt_in', 'edd_local_tax_opt_out',
			'edd_check_for_download_price_variations'
		);

		foreach ($_actions as $action) {
			if ( function_exists( 'wp_ajax_' . $action ) )
				add_action( 'wp_ajax_' . $action, $action, 1 );
		}

		add_filter( 'wp_die_ajax_handler', array( $this, 'getDieHandler' ), 1, 1 );

		if (!defined('DOING_AJAX'))
			define('DOING_AJAX', true);
		set_current_screen( 'ajax' );

		add_action( 'clear_auth_cookie', array( $this, 'logout' ) );

		$wp_factory->post->create_many( 5 );

		error_reporting( 0 & ~E_WARNING );
	}

	public function tearDown() {
		parent::tearDown();
		$_POST = array();
		remove_filter( 'wp_die_ajax_handler', array( $this, 'getDieHandler' ), 1, 1 );
		remove_action( 'clear_auth_cookie', array( $this, 'logout' ) );
		set_current_screen( 'front' );
	}

	public function logout() {
		unset( $GLOBALS['current_user'] );
		$cookies = array(AUTH_COOKIE, SECURE_AUTH_COOKIE, LOGGED_IN_COOKIE, USER_COOKIE, PASS_COOKIE);
		foreach ( $cookies as $c )
			unset( $_COOKIE[$c] );
	}

	public function getDieHandler() {
		return array( $this, 'dieHandler' );
	}

	public function dieHandler( $message ) {
		$this->_last_response .= ob_get_clean();
		ob_end_clean();
		if ( '' === $this->_last_response ) {
			if ( is_scalar( $message) ) {
					throw new Exception( (string) $message );
			} else {
					throw new Exception( '0' );
			}
		} else {
				throw new Exception( $message );
		}
	}

	protected function _setRole( $role ) {
		$wp_factory = new WP_UnitTest_Factory;
		$post = $_POST;
		$user_id = $wp_factory->user->create( array( 'role' => $role ) );
		wp_set_current_user( $user_id );
		$_POST = array_merge($_POST, $post);
	}

	protected function _handleAjax($action) {
		// Start output buffering
		ini_set( 'implicit_flush', false );
		ob_start();

		// Build the request
		$_POST['action'] = $action;
		$_REQUEST = $_POST;

		// Call the hooks
		do_action( 'wp_ajax_' . $_REQUEST['action'] );

		// Save the output
		$buffer = ob_get_clean();

		if ( !empty( $buffer ) )
			$this->_last_response = $buffer;
	}

	public function testRemoveItemFromCart() {
		$this->_setRole( 'administrator' );

		$_POST = array(
			'cart_item' => 0,
			'nonce' => wp_create_nonce( 'edd_ajax_nonce' ),
		);

		$this->assertEquals($this->_last_response, $this->_handleAjax('edd_remove_from_cart'));
	}
}