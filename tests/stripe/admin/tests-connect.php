<?php

namespace EDD\Tests\Stripe\Admin;

use \EDD\Tests\PHPUnit\EDD_UnitTestCase;
use \EDD\Gateways\Stripe\Admin\Connect as StripeConnect;

/**
 * Tests for the Stripe Connect admin class.
 *
 * @covers \EDD\Gateways\Stripe\Admin\Connect
 */
class Connect extends EDD_UnitTestCase {

	/**
	 * Admin user fixture.
	 *
	 * @var int
	 */
	protected static $admin_id;

	/**
	 * Subscriber user fixture.
	 *
	 * @var int
	 */
	protected static $subscriber_id;

	/**
	 * Shop manager user fixture: holds manage_shop_settings, not manage_options.
	 *
	 * @var int
	 */
	protected static $shop_manager_id;

	/**
	 * A second administrator, for the case where one user's state is replayed by another.
	 *
	 * @var int
	 */
	protected static $other_admin_id;

	/**
	 * Whether the broker request fired during a case.
	 *
	 * @var bool
	 */
	private $broker_called = false;

	/**
	 * Set up fixtures once.
	 */
	public static function wpSetUpBeforeClass( $factory ) {
		self::$admin_id        = $factory->user->create( array( 'role' => 'administrator' ) );
		self::$subscriber_id   = $factory->user->create( array( 'role' => 'subscriber' ) );
		self::$shop_manager_id = $factory->user->create( array( 'role' => 'shop_manager' ) );
		self::$other_admin_id  = $factory->user->create( array( 'role' => 'administrator' ) );
	}

	/**
	 * Set up before each test.
	 */
	public function setUp(): void {
		parent::setUp();

		// Admin-only files are not loaded in the test context.
		require_once EDDS_PLUGIN_DIR . '/includes/admin/settings/stripe-connect.php';

		edd_delete_option( \EDD\Gateways\Stripe\Admin\Disconnect\Deauthorize::MODES_SETTING );
		\EDD\Gateways\Stripe\Admin\Disconnect\Deauthorize::clear_error();
	}

	/**
	 * Tear down after each test.
	 */
	public function tearDown(): void {
		remove_all_filters( 'pre_http_request' );
		remove_all_filters( 'edd_is_test_mode' );

		unset( $_GET['edd_gateway_connect_completion'], $_GET['state'], $_GET['redirect_screen'] );

		wp_set_current_user( 0 );

		edd_delete_option( \EDD\Gateways\Stripe\Admin\Disconnect\Deauthorize::MODES_SETTING );
		\EDD\Gateways\Stripe\Admin\Disconnect\Deauthorize::clear_error();

		parent::tearDown();
	}

	/**
	 * @covers \EDD\Gateways\Stripe\Admin\Connect::get_connect_button()
	 */
	public function test_get_connect_button_without_capability_is_empty() {
		wp_set_current_user( self::$subscriber_id );

		$this->assertSame( '', StripeConnect::get_connect_button() );
	}

	/**
	 * @covers \EDD\Gateways\Stripe\Admin\Connect::get_connect_button()
	 */
	public function test_get_connect_button_default_markup() {
		wp_set_current_user( self::$admin_id );

		$button = StripeConnect::get_connect_button();

		$this->assertStringContainsString( 'class="edd-stripe-connect"', $button );
		$this->assertStringContainsString( '<span>Connect with Stripe</span>', $button );
		$this->assertStringContainsString( 'edd_gateway_connect_init=stripe_connect', $button );
	}

	/**
	 * @covers \EDD\Gateways\Stripe\Admin\Connect::get_connect_button()
	 */
	public function test_get_connect_button_custom_text_and_classes() {
		wp_set_current_user( self::$admin_id );

		$button = StripeConnect::get_connect_button(
			array(
				'text'    => 'Custom Text',
				'classes' => array( 'my-extra-class' ),
			)
		);

		$this->assertStringContainsString( 'class="edd-stripe-connect my-extra-class"', $button );
		$this->assertStringContainsString( '<span>Custom Text</span>', $button );
	}

	/**
	 * The button markup must remain single-line: two call sites pass it through wpautop().
	 *
	 * @covers \EDD\Gateways\Stripe\Admin\Connect::get_connect_button()
	 */
	public function test_get_connect_button_markup_is_single_line() {
		wp_set_current_user( self::$admin_id );

		$this->assertStringNotContainsString( "\n", StripeConnect::get_connect_button() );
	}

	/**
	 * @covers \EDD\Gateways\Stripe\Admin\Connect::get_connect_button()
	 */
	public function test_get_connect_button_with_redirect_screen() {
		wp_set_current_user( self::$admin_id );

		$button = StripeConnect::get_connect_button(
			array(
				'redirect_screen' => 'my-screen',
			)
		);

		preg_match( '/href="([^"]+)"/', $button, $matches );
		$href = html_entity_decode( $matches[1] );

		parse_str( (string) wp_parse_url( $href, PHP_URL_QUERY ), $query_args );

		$this->assertArrayHasKey( 'customer_site_url', $query_args );
		$this->assertStringContainsString( 'redirect_screen=my-screen', urldecode( $query_args['customer_site_url'] ) );
	}

	/**
	 * @covers \EDD\Gateways\Stripe\Admin\Connect::get_redirect_screens()
	 */
	public function test_get_redirect_screens_default_contains_onboarding_wizard() {
		$screens = StripeConnect::get_redirect_screens();

		$this->assertArrayHasKey( 'onboarding-wizard', $screens );
		$this->assertTrue( $screens['onboarding-wizard']['enable_gateway'] );
		$this->assertStringContainsString( 'page=edd-onboarding-wizard', $screens['onboarding-wizard']['url'] );
		$this->assertStringContainsString( 'current_step=payment_methods', $screens['onboarding-wizard']['url'] );
	}

	/**
	 * @covers \EDD\Gateways\Stripe\Admin\Connect::get_redirect_screens()
	 */
	public function test_get_redirect_screens_is_filterable() {
		$add_screen = static function ( $screens ) {
			$screens['my-screen'] = array(
				'url'            => 'https://example.org/wp-admin/admin.php?page=my-screen',
				'enable_gateway' => false,
			);

			return $screens;
		};

		add_filter( 'edds_stripe_connect_redirect_screens', $add_screen );

		$screens = StripeConnect::get_redirect_screens();

		remove_filter( 'edds_stripe_connect_redirect_screens', $add_screen );

		$this->assertArrayHasKey( 'my-screen', $screens );
		$this->assertFalse( $screens['my-screen']['enable_gateway'] );
	}

	/**
	 * The premise these cases rest on: the roles differ in exactly the capability under test.
	 */
	public function test_shop_manager_may_manage_shop_settings_but_not_options() {
		$this->assertTrue( user_can( self::$shop_manager_id, 'manage_shop_settings' ) );
		$this->assertFalse( user_can( self::$shop_manager_id, 'manage_options' ) );
		$this->assertTrue( user_can( self::$admin_id, 'manage_options' ) );
	}

	/**
	 * A state the store never built must not be redeemed, so a request the store did not start
	 * cannot replace its payment credentials.
	 *
	 * @covers ::edds_process_gateway_connect_completion
	 */
	public function test_completion_requires_a_state_the_store_created() {
		$this->prepare_live_mode();
		wp_set_current_user( self::$admin_id );

		$this->run_completion( 'a-state-this-store-never-issued' );

		$this->assertFalse( $this->broker_called, 'The broker must not be contacted for an unrecognized state.' );
		$this->assertCredentialsUntouched();
	}

	/**
	 * The paired positive, and the case that catches a fix which breaks real connections.
	 *
	 * @covers ::edds_process_gateway_connect_completion
	 */
	public function test_completion_accepts_a_state_the_store_created() {
		$this->prepare_live_mode();
		wp_set_current_user( self::$admin_id );

		$this->run_completion( $this->mint_state() );

		$this->assertTrue( $this->broker_called, 'A genuine connection must still reach the broker.' );
		$this->assertSame( 'pk_live_REPLACED', edd_get_option( 'live_publishable_key' ) );
		$this->assertSame( 'sk_live_REPLACED', edd_get_option( 'live_secret_key' ) );
		$this->assertSame( 'acct_REPLACED', edd_get_option( 'stripe_connect_account_id' ) );
	}

	/**
	 * Which modes EDD Connect issued is what a later disconnect sends for revocation.
	 *
	 * @covers ::edds_process_gateway_connect_completion
	 */
	public function test_completion_records_the_mode_it_was_issued_for() {
		$this->prepare_live_mode();
		wp_set_current_user( self::$admin_id );

		$this->assertFalse( edd_get_option( \EDD\Gateways\Stripe\Admin\Disconnect\Deauthorize::MODES_SETTING ), 'Fixture: nothing is recorded.' );

		$this->run_completion( $this->mint_state() );

		$this->assertSame( 'sk_live_REPLACED', edd_get_option( 'live_secret_key' ), 'Fixture: the connection completed.' );
		$this->assertSame( array( 'live' => 'acct_REPLACED' ), edd_get_option( \EDD\Gateways\Stripe\Admin\Disconnect\Deauthorize::MODES_SETTING ) );

	}

	/**
	 * @covers ::edds_process_gateway_connect_completion
	 */
	public function test_a_first_connection_leaves_a_hand_entered_key_for_the_other_mode_unrecorded() {
		$this->prepare_live_mode();
		edd_delete_option( 'stripe_connect_account_id' );
		edd_update_option( 'test_secret_key', 'sk_test_BYHAND' );
		wp_set_current_user( self::$admin_id );

		$this->assertFalse( edd_get_option( 'stripe_connect_account_id' ), 'Fixture: the store was never connected.' );

		$this->run_completion( $this->mint_state() );

		$this->assertSame( 'acct_REPLACED', edd_get_option( 'stripe_connect_account_id' ), 'Fixture: the connection completed.' );
		$this->assertSame( array( 'live' => 'acct_REPLACED' ), edd_get_option( \EDD\Gateways\Stripe\Admin\Disconnect\Deauthorize::MODES_SETTING ) );
	}

	/**
	 * @covers ::edds_process_gateway_connect_completion
	 */
	public function test_a_legacy_store_that_reconnects_one_mode_records_both_modes() {
		$this->prepare_live_mode();
		edd_update_option( 'test_secret_key', 'sk_test_LEGACY' );
		wp_set_current_user( self::$admin_id );

		$this->assertSame( 'acct_SENTINEL', edd_get_option( 'stripe_connect_account_id' ), 'Fixture: the store was already connected.' );
		$this->assertSame( 'sk_live_SENTINEL', edd_get_option( 'live_secret_key' ), 'Fixture: the live mode holds a key.' );
		$this->assertSame( 'sk_test_LEGACY', edd_get_option( 'test_secret_key' ), 'Fixture: the test mode holds a key.' );
		$this->assertFalse( edd_get_option( \EDD\Gateways\Stripe\Admin\Disconnect\Deauthorize::MODES_SETTING ), 'Fixture: nothing is recorded.' );

		$this->run_completion( $this->mint_state() );

		$this->assertSame( 'sk_live_REPLACED', edd_get_option( 'live_secret_key' ), 'Fixture: the connection completed.' );
		$this->assertSame(
			array(
				'live' => 'acct_REPLACED',
				'test' => 'acct_SENTINEL',
			),
			edd_get_option( \EDD\Gateways\Stripe\Admin\Disconnect\Deauthorize::MODES_SETTING ),
			'The inherited mode keeps the account stored before this connect.'
		);
	}

	/**
	 * A reconnect resolves the failed disconnect that was waiting on the merchant.
	 *
	 * @covers ::edds_process_gateway_connect_completion
	 */
	public function test_completion_clears_a_recorded_disconnect_failure() {
		$this->prepare_live_mode();
		wp_set_current_user( self::$admin_id );

		\EDD\Gateways\Stripe\Admin\Disconnect\Deauthorize::record_error( new \WP_Error( 'edd_stripe_deauthorize_failed', 'Stripe didn\'t confirm.' ) );
		$this->assertNotFalse( \EDD\Gateways\Stripe\Admin\Disconnect\Deauthorize::get_error(), 'Fixture: a failure is recorded.' );

		$this->run_completion( $this->mint_state() );

		$this->assertSame( 'sk_live_REPLACED', edd_get_option( 'live_secret_key' ), 'Fixture: the connection completed.' );
		$this->assertFalse( \EDD\Gateways\Stripe\Admin\Disconnect\Deauthorize::get_error() );

	}

	/**
	 * The state alone is not enough: completing still needs the capability that saves settings.
	 *
	 * @covers ::edds_process_gateway_connect_completion
	 */
	public function test_completion_requires_the_settings_capability() {
		$this->prepare_live_mode();
		wp_set_current_user( self::$subscriber_id );

		$this->assertFalse(
			user_can( self::$subscriber_id, 'manage_shop_settings' ),
			'Fixture: the user must lack the capability, or this case proves nothing.'
		);

		// Minted by this same user, so the capability is the only thing left to refuse it.
		$this->run_completion( $this->mint_state() );

		$this->assertFalse( $this->broker_called, 'A user who cannot save settings must not reach the broker.' );
		$this->assertCredentialsUntouched();
	}

	/**
	 * The paired positive for the capability: a shop manager administers Stripe settings, so the
	 * one who is invited to connect is the one who can finish.
	 *
	 * @covers ::edds_process_gateway_connect_completion
	 */
	public function test_a_shop_manager_may_complete_the_connection() {
		$this->prepare_live_mode();
		wp_set_current_user( self::$shop_manager_id );

		$this->assertNotEmpty(
			StripeConnect::get_connect_button(),
			'Fixture: a shop manager must be offered the button, or the surfaces do not disagree.'
		);

		$this->run_completion( $this->mint_state() );

		$this->assertTrue(
			$this->broker_called,
			'A shop manager is offered the connect button, so the same user must be able to complete.'
		);
	}

	/**
	 * The onboarding wizard builds its own connect URL, so the state it emits has to be one the
	 * completion handler accepts. Nothing else in the suite drives that path.
	 *
	 * @covers \EDD\Admin\Onboarding\Wizard::update_stripe_connect_url()
	 */
	public function test_the_wizard_connect_url_carries_a_state_the_handler_accepts() {
		wp_set_current_user( self::$admin_id );

		$wizard = new \EDD\Admin\Onboarding\Wizard();
		$url    = $wizard->update_stripe_connect_url();

		$state = null;
		parse_str( (string) wp_parse_url( $url, PHP_URL_QUERY ), $query );
		$state = isset( $query['state'] ) ? $query['state'] : null;

		$this->assertNotEmpty( $state, 'Fixture: the wizard URL must carry a state at all.' );
		$this->assertTrue(
			StripeConnect::is_state_valid( $state ),
			'The wizard must emit a state the completion handler accepts.'
		);
	}

	/**
	 * A state signed for some other purpose is not a connect state.
	 *
	 * The signing key is shared across every Tokenizer caller, so the signature alone does not
	 * say what the value was issued for.
	 *
	 * @covers \EDD\Gateways\Stripe\Admin\Connect::is_state_valid()
	 */
	public function test_a_state_signed_for_another_purpose_is_refused() {
		wp_set_current_user( self::$admin_id );

		$payload = implode( '.', array( bin2hex( random_bytes( 16 ) ), time(), get_current_user_id() ) );
		$foreign = $payload . '.' . \EDD\Utils\Tokenizer::tokenize( $payload );

		$this->assertTrue(
			\EDD\Utils\Tokenizer::is_token_valid( substr( $foreign, strrpos( $foreign, '.' ) + 1 ), $payload ),
			'Fixture: the token must genuinely be one this store signed over the bare payload.'
		);

		$this->assertFalse(
			StripeConnect::is_state_valid( $foreign ),
			'A signature over the unscoped payload must not pass as a connect state.'
		);
	}

	/**
	 * The state's segments are joined on a dot and split back apart on return, so no segment may
	 * be able to contain one. The random segment is the only one that is not an integer.
	 *
	 * @covers \EDD\Gateways\Stripe\Admin\Connect::create_state()
	 */
	public function test_a_filtered_random_password_cannot_break_the_state() {
		wp_set_current_user( self::$admin_id );

		add_filter( 'random_password', array( $this, 'password_with_a_dot' ) );

		$this->assertStringContainsString(
			'.',
			wp_generate_password( 32, false ),
			'Fixture: the filter must actually put a dot into wp_generate_password().'
		);

		$state = StripeConnect::create_state();

		remove_filter( 'random_password', array( $this, 'password_with_a_dot' ) );

		$this->assertTrue(
			StripeConnect::is_state_valid( $state ),
			'A state must survive a site that filters wp_generate_password().'
		);
	}

	/**
	 * Returns a password containing the delimiter.
	 *
	 * @return string
	 */
	public function password_with_a_dot() {
		return 'aaaa.bbbb.cccc';
	}

	/**
	 * A state belongs to the user who started the flow.
	 *
	 * @covers ::edds_process_gateway_connect_completion
	 */
	public function test_completion_rejects_a_state_created_for_another_user() {
		$this->prepare_live_mode();

		wp_set_current_user( self::$admin_id );
		$state = $this->mint_state();

		wp_set_current_user( self::$other_admin_id );
		$this->run_completion( $state );

		$this->assertFalse( $this->broker_called, "Another user's state must not be redeemable." );
		$this->assertCredentialsUntouched();
	}

	/**
	 * A state can be shaped correctly and still not have been built here. This is the case a
	 * forged value falls into, and the one the signature exists for.
	 *
	 * @covers ::edds_process_gateway_connect_completion
	 */
	public function test_completion_rejects_a_correctly_shaped_state_with_a_bad_token() {
		$this->prepare_live_mode();
		wp_set_current_user( self::$admin_id );

		$payload = implode(
			'.',
			array(
				wp_generate_password( 32, false ),
				time(),
				self::$admin_id,
			)
		);

		// Everything the real value has, except a token this store could have produced.
		$forged = $payload . '.' . str_repeat( 'a', 64 );

		$this->assertCount( 4, explode( '.', $forged ), 'The fixture must have the shape of a real state.' );
		$this->assertFalse(
			\EDD\Utils\Tokenizer::is_token_valid( str_repeat( 'a', 64 ), $payload ),
			'The fixture token must not validate, or the shape is what refuses it.'
		);

		$this->run_completion( $forged );

		$this->assertFalse( $this->broker_called, 'A state this store did not sign must not be redeemable.' );
		$this->assertCredentialsUntouched();
	}

	/**
	 * A state stops being redeemable once its window has passed.
	 *
	 * @covers ::edds_process_gateway_connect_completion
	 */
	public function test_completion_rejects_an_expired_state() {
		$this->prepare_live_mode();
		wp_set_current_user( self::$admin_id );

		// Built the way the store builds one, but issued outside the window.
		$payload = implode(
			'.',
			array(
				wp_generate_password( 32, false ),
				time() - ( StripeConnect::STATE_LIFETIME + 60 ),
				self::$admin_id,
			)
		);
		$expired = $payload . '.' . \EDD\Utils\Tokenizer::tokenize( $payload );

		$this->assertTrue(
			\EDD\Utils\Tokenizer::is_token_valid( substr( $expired, strrpos( $expired, '.' ) + 1 ), $payload ),
			'The fixture must carry a genuine token, or age is not what refuses it.'
		);

		$this->run_completion( $expired );

		$this->assertFalse( $this->broker_called, 'An expired state must not be redeemable.' );
		$this->assertCredentialsUntouched();
	}

	/**
	 * The branch that switches the gateway on sits behind the same check.
	 *
	 * @covers ::edds_process_gateway_connect_completion
	 */
	public function test_gateway_enable_branch_is_behind_the_state_check() {
		$this->prepare_live_mode();
		wp_set_current_user( self::$admin_id );

		$before = edd_get_option( 'gateways', array() );
		$this->assertArrayNotHasKey( 'stripe', (array) $before, 'Stripe must start disabled.' );

		$_GET['redirect_screen'] = 'onboarding-wizard';
		$this->run_completion( 'a-state-this-store-never-issued' );

		$this->assertFalse( $this->broker_called );
		$this->assertArrayNotHasKey( 'stripe', (array) edd_get_option( 'gateways', array() ) );
	}

	/**
	 * Puts the store in live mode, seeds recognizable credentials, and stands in for the broker.
	 */
	private function prepare_live_mode() {
		add_filter( 'edd_is_test_mode', '__return_false' );

		edd_update_option( 'live_publishable_key', 'pk_live_SENTINEL' );
		edd_update_option( 'live_secret_key', 'sk_live_SENTINEL' );
		edd_update_option( 'stripe_connect_account_id', 'acct_SENTINEL' );

		$this->broker_called = false;

		add_filter(
			'pre_http_request',
			function () {
				$this->broker_called = true;

				return array(
					'response' => array( 'code' => 200 ),
					'body'     => wp_json_encode(
						array(
							'data' => array(
								'publishable_key' => 'pk_live_REPLACED',
								'secret_key'      => 'sk_live_REPLACED',
								'stripe_user_id'  => 'acct_REPLACED',
							),
						)
					),
				);
			}
		);
	}

	/**
	 * Builds a state the way the connect link does, for the current user.
	 *
	 * @return string
	 */
	private function mint_state() {
		$url = edds_stripe_connect_url();
		$this->assertNotEmpty( $url, 'The connect URL fixture must exist.' );

		$query = array();
		parse_str( (string) wp_parse_url( $url, PHP_URL_QUERY ), $query );

		$this->assertNotEmpty( $query['state'], 'The connect URL must carry a state.' );

		return $query['state'];
	}

	/**
	 * Runs the completion handler with the given state.
	 *
	 * @param string $state The state to present.
	 */
	private function run_completion( $state ) {
		$_GET['edd_gateway_connect_completion'] = 'stripe_connect';
		$_GET['state']                          = $state;

		edds_process_gateway_connect_completion();
	}

	/**
	 * Asserts the seeded credentials are still in place.
	 */
	private function assertCredentialsUntouched() {
		$this->assertSame( 'pk_live_SENTINEL', edd_get_option( 'live_publishable_key' ) );
		$this->assertSame( 'sk_live_SENTINEL', edd_get_option( 'live_secret_key' ) );
		$this->assertSame( 'acct_SENTINEL', edd_get_option( 'stripe_connect_account_id' ) );
	}
}
