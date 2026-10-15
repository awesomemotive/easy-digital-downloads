<?php
/**
 * Tests for EDD_Notices class.
 *
 * @package     EDD\Tests\Admin
 * @copyright   Copyright (c) 2026, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.6.9
 */

namespace EDD\Tests\Admin;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;
use EDD\Admin\Utils\Page;

/**
 * Tests for the EDD_Notices class.
 *
 * @since 3.6.9
 * @covers EDD_Notices
 */
class Notices extends EDD_UnitTestCase {

	/**
	 * Instance under test.
	 *
	 * @var \EDD_Notices
	 */
	private $notices;

	/**
	 * Set up test fixtures.
	 */
	public function setUp(): void {
		parent::setUp();
		require_once EDD_PLUGIN_DIR . 'includes/admin/class-edd-notices.php';
		$this->notices = new \EDD_Notices();
	}

	/**
	 * Tear down after each test.
	 */
	public function tearDown(): void {
		$_GET = array();
		global $pagenow, $typenow;
		$pagenow = null;
		$typenow = null;
		delete_transient( 'edd_admin_notices' );

		remove_filter( 'edd_is_admin_page', array( $this, 'filter_is_edd_admin_page' ), 10 );
		edd_delete_option( 'gateways' );
		edd_delete_option( 'stripe_elements_mode' );
		edd_delete_option( 'stripe_connect_account_id' );
		edd_delete_option( 'test_mode' );
		edd_delete_option( 'test_secret_key' );

		parent::tearDown();
	}

	/** Tests for remove_notices() ***********************************************/

	/**
	 * Confirms remove_notices() exits early on the WP dashboard and leaves
	 * non-EDD hooks untouched.
	 *
	 * @covers EDD_Notices::remove_notices
	 */
	public function test_remove_notices_returns_early_on_dashboard() {
		$this->set_admin_context();
		$GLOBALS['pagenow'] = 'index.php';

		$callback = 'some_non_edd_dashboard_callback';
		add_action( 'admin_notices', $callback );

		$this->notices->remove_notices();

		$this->assertNotFalse( has_action( 'admin_notices', $callback ) );
		remove_action( 'admin_notices', $callback );
	}

	/**
	 * Confirms remove_notices() exits early when not on an EDD admin page,
	 * leaving non-EDD hooks untouched.
	 *
	 * @covers EDD_Notices::remove_notices
	 */
	public function test_remove_notices_returns_early_when_not_edd_page() {
		// No admin context set — is_admin() returns false in the test environment.
		$callback = 'some_non_edd_callback';
		add_action( 'admin_notices', $callback );

		$this->notices->remove_notices();

		$this->assertNotFalse( has_action( 'admin_notices', $callback ) );
		remove_action( 'admin_notices', $callback );
	}

	/**
	 * Confirms remove_notices() strips non-EDD string callbacks on an EDD
	 * admin screen while preserving EDD string callbacks.
	 *
	 * @covers EDD_Notices::remove_notices
	 */
	public function test_remove_notices_removes_non_edd_string_callbacks() {
		$this->set_admin_context();
		$GLOBALS['typenow'] = 'download';

		$non_edd_callback = 'some_other_plugin_notice';
		$edd_callback     = 'edd_show_some_notice';

		add_action( 'admin_notices', $non_edd_callback );
		add_action( 'admin_notices', $edd_callback );

		$this->notices->remove_notices();

		$this->assertFalse( has_action( 'admin_notices', $non_edd_callback ) );
		$this->assertNotFalse( has_action( 'admin_notices', $edd_callback ) );

		remove_action( 'admin_notices', $edd_callback );
	}

	/**
	 * Confirms remove_notices() strips object callbacks whose class name does
	 * not contain 'edd' while preserving those that do.
	 *
	 * @covers EDD_Notices::remove_notices
	 */
	public function test_remove_notices_removes_non_edd_object_callbacks() {
		$this->set_admin_context();
		$GLOBALS['typenow'] = 'download';

		$non_edd_object = new class() {
			public function display_notice() {}
		};
		$edd_object     = new \EDD_Notices();

		add_action( 'admin_notices', array( $non_edd_object, 'display_notice' ) );
		add_action( 'admin_notices', array( $edd_object, 'display_notices' ) );

		$this->notices->remove_notices();

		$this->assertFalse( has_action( 'admin_notices', array( $non_edd_object, 'display_notice' ) ) );
		$this->assertNotFalse( has_action( 'admin_notices', array( $edd_object, 'display_notices' ) ) );

		remove_action( 'admin_notices', array( $edd_object, 'display_notices' ) );
	}

	/** Tests for add_notice() ***************************************************/

	/**
	 * @covers EDD_Notices::add_notice
	 */
	public function test_add_notice_with_string_message() {
		$this->notices->add_notice(
			array(
				'id'      => 'test-notice',
				'message' => 'Hello world',
			)
		);

		$notices = $this->get_notices_property();
		$this->assertArrayHasKey( 'test-notice', $notices );
		$this->assertStringContainsString( 'Hello world', $notices['test-notice'] );
	}

	/**
	 * @covers EDD_Notices::add_notice
	 */
	public function test_add_notice_with_array_message() {
		$this->notices->add_notice(
			array(
				'id'      => 'test-notice',
				'message' => array( 'First line', 'Second line' ),
			)
		);

		$notices = $this->get_notices_property();
		$this->assertStringContainsString( 'First line', $notices['test-notice'] );
		$this->assertStringContainsString( 'Second line', $notices['test-notice'] );
	}

	/**
	 * @covers EDD_Notices::add_notice
	 */
	public function test_add_notice_with_wp_error_single_message() {
		$error = new \WP_Error( 'test-error', 'Something went wrong' );
		$this->notices->add_notice(
			array(
				'id'      => 'test-notice',
				'message' => $error,
			)
		);

		$notices = $this->get_notices_property();
		$this->assertArrayHasKey( 'test-notice', $notices );
		$this->assertStringContainsString( 'Something went wrong', $notices['test-notice'] );
	}

	/**
	 * @covers EDD_Notices::add_notice
	 */
	public function test_add_notice_with_empty_wp_error_returns_false() {
		$result = $this->notices->add_notice(
			array(
				'id'      => 'test-notice',
				'message' => new \WP_Error(),
			)
		);

		$this->assertFalse( $result );
		$this->assertEmpty( $this->get_notices_property() );
	}

	/**
	 * @covers EDD_Notices::add_notice
	 */
	public function test_add_notice_with_invalid_message_type_returns_false() {
		$result = $this->notices->add_notice(
			array(
				'id'      => 'test-notice',
				'message' => 42,
			)
		);

		$this->assertFalse( $result );
	}

	/**
	 * @covers EDD_Notices::add_notice
	 */
	public function test_add_notice_prevents_duplicate_id() {
		$this->notices->add_notice(
			array(
				'id'      => 'test-notice',
				'message' => 'First',
			)
		);
		$this->notices->add_notice(
			array(
				'id'      => 'test-notice',
				'message' => 'Second',
			)
		);

		$notices = $this->get_notices_property();
		$this->assertCount( 1, $notices );
		$this->assertStringContainsString( 'First', $notices['test-notice'] );
	}

	/**
	 * @covers EDD_Notices::add_notice
	 */
	public function test_add_notice_dismissible_includes_class() {
		$this->notices->add_notice(
			array(
				'id'             => 'test-notice',
				'message'        => 'Test',
				'is_dismissible' => true,
			)
		);

		$notices = $this->get_notices_property();
		$this->assertStringContainsString( 'is-dismissible', $notices['test-notice'] );
	}

	/**
	 * @covers EDD_Notices::add_notice
	 */
	public function test_add_notice_non_dismissible_omits_class() {
		$this->notices->add_notice(
			array(
				'id'             => 'test-notice',
				'message'        => 'Test',
				'is_dismissible' => false,
			)
		);

		$notices = $this->get_notices_property();
		$this->assertStringNotContainsString( 'is-dismissible', $notices['test-notice'] );
	}

	/**
	 * @covers EDD_Notices::add_notice
	 */
	public function test_add_notice_custom_class_included_in_output() {
		$this->notices->add_notice(
			array(
				'id'      => 'test-notice',
				'message' => 'Test',
				'class'   => 'notice-warning',
			)
		);

		$notices = $this->get_notices_property();
		$this->assertStringContainsString( 'notice-warning', $notices['test-notice'] );
	}

	/** Tests for add_transient_notice() *****************************************/

	/**
	 * @covers EDD_Notices::add_transient_notice
	 */
	public function test_add_transient_notice_creates_transient() {
		\EDD_Notices::add_transient_notice( 'test-id', 'Test message' );

		$stored = get_transient( 'edd_admin_notices' );
		$this->assertIsArray( $stored );
		$this->assertCount( 1, $stored );
		$this->assertSame( 'test-id', $stored[0]['id'] );
		$this->assertSame( 'Test message', $stored[0]['message'] );
	}

	/**
	 * @covers EDD_Notices::add_transient_notice
	 */
	public function test_add_transient_notice_appends_to_existing() {
		\EDD_Notices::add_transient_notice( 'notice-1', 'First' );
		\EDD_Notices::add_transient_notice( 'notice-2', 'Second' );

		$stored = get_transient( 'edd_admin_notices' );
		$this->assertCount( 2, $stored );
	}

	/**
	 * @covers EDD_Notices::add_transient_notice
	 */
	public function test_add_transient_notice_default_type_is_success() {
		\EDD_Notices::add_transient_notice( 'test-id', 'Test message' );

		$stored = get_transient( 'edd_admin_notices' );
		$this->assertSame( 'success', $stored[0]['class'] );
	}

	/** Tests for add_stripe_notice() ********************************************/

	/**
	 * @covers EDD_Notices::add_stripe_notice
	 */
	public function test_stripe_notice_points_connected_card_elements_stores_to_elements_mode() {
		$this->set_stripe_card_elements_context();
		edd_update_option( 'stripe_connect_account_id', 'acct_test' );

		$this->add_stripe_notice();

		$notices = $this->get_notices_property();
		$this->assertArrayHasKey( 'edd-stripe-card-elements', $notices );
		$this->assertStringContainsString( 'change Elements Mode to Payment Elements', $notices['edd-stripe-card-elements'] );
		$this->assertStringNotContainsString( 'Connect with Stripe', $notices['edd-stripe-card-elements'] );
	}

	/**
	 * @covers EDD_Notices::add_stripe_notice
	 */
	public function test_stripe_notice_points_manual_api_key_stores_to_stripe_connect() {
		$this->set_stripe_card_elements_context();
		edd_update_option( 'test_mode', true );
		edd_update_option( 'test_secret_key', 'sk_test_manual' );

		$this->add_stripe_notice();

		$notices = $this->get_notices_property();
		$this->assertArrayHasKey( 'edd-stripe-card-elements', $notices );
		$this->assertStringContainsString( 'Connect with Stripe', $notices['edd-stripe-card-elements'] );
		$this->assertStringNotContainsString( 'Elements Mode', $notices['edd-stripe-card-elements'] );
	}

	/** Helpers ******************************************************************/

	/**
	 * Invokes the private add_stripe_notice() method.
	 */
	private function add_stripe_notice(): void {
		$method = new \ReflectionMethod( \EDD_Notices::class, 'add_stripe_notice' );
		$method->setAccessible( true );
		$method->invoke( $this->notices );
	}

	/**
	 * Puts the store on Card Elements with Stripe active, on an EDD admin page other than the dashboard.
	 */
	private function set_stripe_card_elements_context(): void {
		add_filter( 'edd_is_admin_page', array( $this, 'filter_is_edd_admin_page' ), 10, 4 );
		edd_update_option( 'gateways', array( 'stripe' => 1 ) );
		edd_update_option( 'stripe_elements_mode', 'card-elements' );
	}

	/**
	 * Reports every EDD admin page as current except the WordPress dashboard.
	 *
	 * @param bool   $found       Whether the current page is an EDD admin page.
	 * @param string $page        The page slug.
	 * @param string $view        The view slug.
	 * @param string $passed_page The page slug being checked.
	 * @return bool
	 */
	public function filter_is_edd_admin_page( $found, $page, $view, $passed_page ): bool {
		return 'index.php' !== $passed_page;
	}

	/**
	 * Retrieves the private $notices array via reflection.
	 *
	 * @return array
	 */
	private function get_notices_property(): array {
		$reflection = new \ReflectionProperty( \EDD_Notices::class, 'notices' );
		if (PHP_VERSION_ID < 80100) {
            $reflection->setAccessible( true );
        }
		return $reflection->getValue( $this->notices );
	}

	/**
	 * Sets an admin screen context so is_admin() returns true.
	 *
	 * @param string $screen Screen ID to pass to set_current_screen().
	 */
	private function set_admin_context( string $screen = 'edit-download' ): void {
		set_current_screen( $screen );
	}
}
