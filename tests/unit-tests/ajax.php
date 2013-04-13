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
		EDD()->session->set( 'edd_cart', null );
	}

	public function logout() {
		unset( $GLOBALS['current_user'] );
		$cookies = array(AUTH_COOKIE, SECURE_AUTH_COOKIE, LOGGED_IN_COOKIE, USER_COOKIE, PASS_COOKIE);
		foreach ( $cookies as $c )
			unset( $_COOKIE[$c] );
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

		return $buffer;
	}

	public function test_add_item_to_cart() {
		$wp_factory = new WP_UnitTest_Factory;
		$post_id = $wp_factory->post->create( array( 'post_title' => 'Test Download', 'post_type' => 'download', 'post_status' => 'publish' ) );

		$_variable_pricing = array(
			array(
				'name' => 'Simple',
				'amount' => 20
			),
			array(
				'name' => 'Advanced',
				'amount' => 100
			)
		);

		$_download_files = array(
			array(
				'name' => 'File 1',
				'file' => 'http://localhost/file1.jpg',
				'condition' => 0
			),
			array(
				'name' => 'File 2',
				'file' => 'http://localhost/file2.jpg',
				'condition' => 'all'
			)
		);

		$meta = array(
			'edd_price' => '0.00',
			'_variable_pricing' => 1,
			'_edd_price_options_mode' => 'on',
			'edd_variable_prices' => array_values( $_variable_pricing ), 
			'edd_download_files' => array_values( $_download_files ),
			'_edd_download_limit' => 20,
			'_edd_hide_purchase_link' => 1,
			'edd_product_notes' => 'Purchase Notes',
			'_edd_product_type' => 'default',
			'_edd_download_earnings' => 129.43,
			'_edd_download_sales' => 59,
			'_edd_download_limit_override_1' => 1
		);
		foreach( $meta as $key => $value ) {
			update_post_meta( $post_id, $key, $value );
		}

		$this->_setRole( 'administrator' );

		$_POST = array(
			'download_id' => $post_id,
			'variable_price' => 'yes',
			'price_mode' => 'single',
			'price_ids' => array(
				1
			),
			'nonce' => wp_create_nonce( 'edd_ajax_nonce' ),
		);

		$out = $this->_handleAjax( 'edd_add_to_cart' );

		$this->assertContains( '<li class="edd-cart-item">', $out );
		$this->assertContains( '<span class="edd-cart-item-title">Test Download <span class="edd-cart-item-separator">-</span> Advanced</span>', $out );
		$this->assertContains( '<span class="edd-cart-item-separator">-</span>&nbsp;&#36;100.00&nbsp;<span class="edd-cart-item-separator">-</span>', $out );
	}

	public function test_remove_item_from_cart() {
		$this->_setRole( 'administrator' );

		$_POST = array(
			'cart_item' => 0,
			'nonce' => wp_create_nonce( 'edd_ajax_nonce' ),
		);

		$this->assertEquals( 'removed', $this->_handleAjax( 'edd_remove_from_cart' ) );
	}

	public function test_checkout_login_fields() {
		$this->_handleAjax( 'nopriv_checkout_login' );
		
		$this->assertContains( '<fieldset id="edd_login_fields">', $this->_last_response );
		$this->assertContains( '<legend>Login to your account</legend>', $this->_last_response );
		$this->assertContains( '<p id="edd-user-login-wrap">', $this->_last_response );
		$this->assertContains( '<label class="edd-label" for="edd-username">Username</label>', $this->_last_response );
		$this->assertContains( '<input class="edd-input" type="text" name="edd_user_login" id="edd_user_login" value="" placeholder="Your username"/>', $this->_last_response );
		$this->assertContains( '<p id="edd-user-pass-wrap" class="edd_login_password">', $this->_last_response );
		$this->assertContains( '<label class="edd-label" for="edd-password">Password</label>', $this->_last_response );
		$this->assertContains( '<input class="edd-input" type="password" name="edd_user_pass" id="edd_user_pass" placeholder="Your password"/>', $this->_last_response );
		$this->assertContains( '<input type="hidden" name="edd-purchase-var" value="needs-to-login"/>', $this->_last_response );
		$this->assertContains( '</fieldset><!--end #edd_login_fields-->', $this->_last_response );
		$this->assertContains( '<p id="edd-new-account-wrap">', $this->_last_response );
		$this->assertContains( 'Need to create an account?', $this->_last_response );
		$this->assertContains( '<a href="" class="edd_checkout_register_login" data-action="checkout_register">', $this->_last_response );
		$this->assertContains( 'Register or checkout as a guest', $this->_last_response );
		$this->assertContains( '</a>', $this->_last_response );
		$this->assertContains( '</p>', $this->_last_response );
	}

	public function test_checkout_register_fields() {
		$expected = <<<DATA
	<fieldset id="edd_register_fields">
		<p id="edd-login-account-wrap">Already have an account? <a href="?login=1" class="edd_checkout_register_login" data-action="checkout_login">Login</a></p>
		<p id="edd-user-email-wrap">
			<label for="edd-email">Email</label>
			<span class="edd-description">We will send the purchase receipt to this address.</span>
			<input name="edd_email" id="edd-email" class="required edd-input" type="email" placeholder="Email" title="Email"/>
		</p>
		<p id="edd-user-first-name-wrap">
			<label class="edd-label" for="edd-first">First Name</label>
			<span class="edd-description">We will use this to personalize your account experience.</span>
			<input class="edd-input required" type="text" name="edd_first" placeholder="First Name" id="edd-first" value=""/>
		</p>
		<p id="edd-user-last-name-wrap">
			<label class="edd-label" for="edd-last">Last Name</label>
			<span class="edd-description">We will use this as well to personalize your account experience.</span>
			<input class="edd-input" type="text" name="edd_last" id="edd-last" placeholder="Last name" value=""/>
		</p>
		<fieldset id="edd_register_account_fields">
			<legend>Create an account (optional)</legend>
						<p id="edd-user-login-wrap">
				<label for="edd_user_login">Username</label>
				<span class="edd-description">The username you will use to log into your account.</span>
				<input name="edd_user_login" id="edd_user_login" class="edd-input" type="text" placeholder="Username" title="Username"/>
			</p>
			<p id="edd-user-pass-wrap">
				<label for="password">Password</label>
				<span class="edd-description">The password used to access your account.</span>
				<input name="edd_user_pass" id="edd_user_pass" class="edd-input" placeholder="Password" type="password"/>
			</p>
			<p id="edd-user-pass-confirm-wrap" class="edd_register_password">
				<label for="password_again">Password Again</label>
				<span class="edd-description">Confirm your password.</span>
				<input name="edd_user_pass_confirm" id="edd_user_pass_confirm" class="edd-input" placeholder="Confirm password" type="password"/>
			</p>
					</fieldset>
		<input type="hidden" name="edd-purchase-var" value="needs-to-register"/>

			</fieldset>
	
DATA;
		$this->_handleAjax( 'nopriv_checkout_register' );
		$this->assertEquals( $expected, $this->_last_response );
	}
}