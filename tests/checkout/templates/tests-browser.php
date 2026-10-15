<?php
/**
 * Browser Tests
 *
 * @package EDD\Tests\Checkout\Templates
 * @since   3.7.0
 */

namespace EDD\Tests\Checkout\Templates;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;
use EDD\Admin\Checkout\Templates\Browser;
use EDD\Checkout\Templates\Config\Constants;
use EDD\Checkout\Templates\Config\EditorRegistry;

/**
 * Browser tests.
 *
 * Unit tests for the Browser class that handles
 * asset loading for the React template browser.
 *
 * @group checkout-templates
 */
class BrowserTest extends EDD_UnitTestCase {

	/**
	 * Browser instance.
	 *
	 * @var Browser
	 */
	protected $browser;

	/**
	 * Test checkout page ID.
	 *
	 * @var int
	 */
	protected static $checkout_page_id;

	/**
	 * Admin user ID for tests.
	 *
	 * @var int
	 */
	protected static $admin_user_id;

	/**
	 * Set up fixtures before class runs.
	 *
	 * @return void
	 */
	public static function setUpBeforeClass(): void {
		parent::setUpBeforeClass();

		// Create checkout page.
		self::$checkout_page_id = self::factory()->post->create(
			array(
				'post_type'   => 'page',
				'post_title'  => 'Checkout',
				'post_status' => 'publish',
			)
		);

		// Create admin user.
		self::$admin_user_id = self::factory()->user->create( array( 'role' => 'administrator' ) );

		// Set as EDD checkout page.
		edd_update_option( 'purchase_page', self::$checkout_page_id );
	}

	/**
	 * Set up each test.
	 *
	 * @return void
	 */
	public function set_up() {
		parent::set_up();
		$this->browser = new Browser();
	}

	/**
	 * Tear down after each test.
	 *
	 * @return void
	 */
	public function tear_down() {
		unset( $_GET['page'], $_GET['tab'], $_GET['section'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$GLOBALS['pagenow'] = null;
		$GLOBALS['typenow'] = null;
		parent::tear_down();
	}

	/**
	 * Test Browser implements SubscriberInterface.
	 */
	public function test_implements_subscriber_interface() {
		$this->assertInstanceOf(
			'EDD\EventManagement\SubscriberInterface',
			$this->browser
		);
	}

	/**
	 * Test get_subscribed_events returns array.
	 */
	public function test_get_subscribed_events_returns_array() {
		$events = Browser::get_subscribed_events();

		$this->assertIsArray( $events );
	}

	/**
	 * Test get_subscribed_events contains admin_enqueue_scripts.
	 */
	public function test_get_subscribed_events_contains_enqueue_hook() {
		$events = Browser::get_subscribed_events();

		$this->assertArrayHasKey( 'admin_enqueue_scripts', $events );
		$this->assertSame( 'maybe_enqueue_assets', $events['admin_enqueue_scripts'] );
	}

	/**
	 * Test maybe_enqueue_assets does not enqueue on non-settings page.
	 *
	 * @covers EDD\Admin\Checkout\Templates\Browser::maybe_enqueue_assets
	 */
	public function test_maybe_enqueue_assets_skips_non_settings_page() {
		set_current_screen( 'edit-download' );

		// Dequeue first to ensure clean state.
		wp_dequeue_script( 'edd-checkout-templates' );
		wp_dequeue_style( 'edd-checkout-templates' );

		$this->browser->maybe_enqueue_assets( 'edit.php' );

		$this->assertFalse( wp_script_is( 'edd-checkout-templates', 'enqueued' ) );
		$this->assertFalse( wp_style_is( 'edd-checkout-templates', 'enqueued' ) );
	}

	/**
	 * Test maybe_enqueue_assets does not enqueue on emails tab.
	 *
	 * @covers EDD\Admin\Checkout\Templates\Browser::maybe_enqueue_assets
	 */
	public function test_maybe_enqueue_assets_not_on_emails_tab() {
		wp_set_current_user( self::$admin_user_id );
		set_current_screen( 'edit-download' );
		$GLOBALS['pagenow'] = 'edit.php';
		$GLOBALS['typenow'] = 'download';
		$_GET['page']       = 'edd-settings'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$_GET['tab']        = 'emails'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		// Dequeue first to ensure clean state.
		wp_dequeue_script( 'edd-checkout-templates' );
		wp_dequeue_style( 'edd-checkout-templates' );

		$this->browser->maybe_enqueue_assets( 'download_page_edd-settings' );

		$this->assertFalse( wp_script_is( 'edd-checkout-templates', 'enqueued' ) );
		$this->assertFalse( wp_style_is( 'edd-checkout-templates', 'enqueued' ) );
	}

	/**
	 * Test maybe_enqueue_assets does not enqueue outside the Pages section.
	 *
	 * @covers EDD\Admin\Checkout\Templates\Browser::maybe_enqueue_assets
	 */
	public function test_maybe_enqueue_assets_not_on_other_section() {
		wp_set_current_user( self::$admin_user_id );
		set_current_screen( 'edit-download' );
		$GLOBALS['pagenow'] = 'edit.php';
		$GLOBALS['typenow'] = 'download';
		$_GET['page']       = 'edd-settings'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$_GET['tab']        = 'general'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$_GET['section']    = 'main'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		// Dequeue first to ensure clean state.
		wp_dequeue_script( 'edd-checkout-templates' );
		wp_dequeue_style( 'edd-checkout-templates' );

		$this->browser->maybe_enqueue_assets( 'download_page_edd-settings' );

		$this->assertFalse( wp_script_is( 'edd-checkout-templates', 'enqueued' ) );
		$this->assertFalse( wp_style_is( 'edd-checkout-templates', 'enqueued' ) );
	}

	/**
	 * Test maybe_enqueue_assets enqueues on general tab.
	 *
	 * @covers EDD\Admin\Checkout\Templates\Browser::maybe_enqueue_assets
	 */
	public function test_maybe_enqueue_assets_on_general_tab() {
		wp_set_current_user( self::$admin_user_id );
		set_current_screen( 'edit-download' );
		$GLOBALS['pagenow'] = 'edit.php';
		$GLOBALS['typenow'] = 'download';
		$_GET['page']       = 'edd-settings'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$_GET['section']    = 'pages'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$_GET['tab']        = 'general'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		$this->browser->maybe_enqueue_assets( 'download_page_edd-settings' );

		$this->assertTrue( wp_script_is( 'edd-checkout-templates', 'enqueued' ) );
	}

	/**
	 * Test maybe_enqueue_assets enqueues when no tab is set (defaults to general).
	 *
	 * @covers EDD\Admin\Checkout\Templates\Browser::maybe_enqueue_assets
	 */
	public function test_maybe_enqueue_assets_on_default_tab() {
		wp_set_current_user( self::$admin_user_id );
		set_current_screen( 'edit-download' );
		$GLOBALS['pagenow'] = 'edit.php';
		$GLOBALS['typenow'] = 'download';
		$_GET['page']       = 'edd-settings'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$_GET['section']    = 'pages'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		unset( $_GET['tab'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		$this->browser->maybe_enqueue_assets( 'download_page_edd-settings' );

		$this->assertTrue( wp_script_is( 'edd-checkout-templates', 'enqueued' ) );
	}

	/**
	 * Test maybe_enqueue_assets enqueues on settings page.
	 */
	public function test_maybe_enqueue_assets_enqueues_on_settings_page() {
		// Simulate admin context.
		wp_set_current_user( self::$admin_user_id );
		set_current_screen( 'edit-download' );
		$GLOBALS['pagenow'] = 'edit.php';
		$GLOBALS['typenow'] = 'download';
		$_GET['page']       = 'edd-settings'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$_GET['section']    = 'pages'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		$this->browser->maybe_enqueue_assets( 'download_page_edd-settings' );

		$this->assertTrue( wp_script_is( 'edd-checkout-templates', 'enqueued' ) );
		$this->assertTrue( wp_style_is( 'edd-checkout-templates', 'enqueued' ) );
	}

	/**
	 * Test script has correct dependencies.
	 */
	public function test_script_has_dependencies() {
		wp_set_current_user( self::$admin_user_id );
		set_current_screen( 'edit-download' );
		$GLOBALS['pagenow'] = 'edit.php';
		$GLOBALS['typenow'] = 'download';
		$_GET['page']       = 'edd-settings'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$_GET['section']    = 'pages'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		$this->browser->maybe_enqueue_assets( 'download_page_edd-settings' );

		global $wp_scripts;
		$script = $wp_scripts->registered['edd-checkout-templates'];

		$this->assertContains( 'wp-element', $script->deps );
		$this->assertContains( 'wp-components', $script->deps );
		$this->assertContains( 'wp-i18n', $script->deps );
	}

	/**
	 * Test script localization data exists.
	 */
	public function test_script_localization_data_exists() {
		wp_set_current_user( self::$admin_user_id );
		set_current_screen( 'edit-download' );
		$GLOBALS['pagenow'] = 'edit.php';
		$GLOBALS['typenow'] = 'download';
		$_GET['page']       = 'edd-settings'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$_GET['section']    = 'pages'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		$this->browser->maybe_enqueue_assets( 'download_page_edd-settings' );

		global $wp_scripts;
		$script = $wp_scripts->registered['edd-checkout-templates'];

		// Check that localization data is set.
		$this->assertNotEmpty( $script->extra['data'] );
		$this->assertStringContainsString( 'eddCheckoutTemplates', $script->extra['data'] );
	}

	/**
	 * Test script localization contains restUrl.
	 */
	public function test_script_localization_contains_rest_url() {
		wp_set_current_user( self::$admin_user_id );
		set_current_screen( 'edit-download' );
		$GLOBALS['pagenow'] = 'edit.php';
		$GLOBALS['typenow'] = 'download';
		$_GET['page']       = 'edd-settings'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$_GET['section']    = 'pages'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		$this->browser->maybe_enqueue_assets( 'download_page_edd-settings' );

		global $wp_scripts;
		$script = $wp_scripts->registered['edd-checkout-templates'];

		$this->assertStringContainsString( 'restUrl', $script->extra['data'] );
		$this->assertStringContainsString( 'checkout-templates', $script->extra['data'] );
	}

	/**
	 * Test script localization contains nonce.
	 */
	public function test_script_localization_contains_nonce() {
		wp_set_current_user( self::$admin_user_id );
		set_current_screen( 'edit-download' );
		$GLOBALS['pagenow'] = 'edit.php';
		$GLOBALS['typenow'] = 'download';
		$_GET['page']       = 'edd-settings'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$_GET['section']    = 'pages'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		$this->browser->maybe_enqueue_assets( 'download_page_edd-settings' );

		global $wp_scripts;
		$script = $wp_scripts->registered['edd-checkout-templates'];

		$this->assertStringContainsString( 'nonce', $script->extra['data'] );
	}

	/**
	 * Test script localization contains checkoutPageId.
	 */
	public function test_script_localization_contains_checkout_page_id() {
		wp_set_current_user( self::$admin_user_id );
		set_current_screen( 'edit-download' );
		$GLOBALS['pagenow'] = 'edit.php';
		$GLOBALS['typenow'] = 'download';
		$_GET['page']       = 'edd-settings'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$_GET['section']    = 'pages'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		$this->browser->maybe_enqueue_assets( 'download_page_edd-settings' );

		global $wp_scripts;
		$script = $wp_scripts->registered['edd-checkout-templates'];

		$this->assertStringContainsString( 'checkoutPageId', $script->extra['data'] );
	}

	/**
	 * Test script localization contains editors.
	 */
	public function test_script_localization_contains_editors() {
		wp_set_current_user( self::$admin_user_id );
		set_current_screen( 'edit-download' );
		$GLOBALS['pagenow'] = 'edit.php';
		$GLOBALS['typenow'] = 'download';
		$_GET['page']       = 'edd-settings'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$_GET['section']    = 'pages'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		$this->browser->maybe_enqueue_assets( 'download_page_edd-settings' );

		global $wp_scripts;
		$script = $wp_scripts->registered['edd-checkout-templates'];

		$this->assertStringContainsString( 'editors', $script->extra['data'] );
	}

	/**
	 * Test style has wp-components dependency.
	 */
	public function test_style_has_components_dependency() {
		wp_set_current_user( self::$admin_user_id );
		set_current_screen( 'edit-download' );
		$GLOBALS['pagenow'] = 'edit.php';
		$GLOBALS['typenow'] = 'download';
		$_GET['page']       = 'edd-settings'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$_GET['section']    = 'pages'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		$this->browser->maybe_enqueue_assets( 'download_page_edd-settings' );

		global $wp_styles;
		$style = $wp_styles->registered['edd-checkout-templates'];

		$this->assertContains( 'wp-components', $style->deps );
	}

	/**
	 * Test Browser uses TemplateBrowserTrait.
	 */
	public function test_uses_template_browser_trait() {
		$traits = class_uses( Browser::class );

		$this->assertContains(
			'EDD\Checkout\Templates\Traits\TemplateBrowserTrait',
			$traits
		);
	}

	/**
	 * Elementor is advertised as unavailable when ELEMENTOR_VERSION is not defined.
	 *
	 * The editor map now always carries every editor so the card can explain an
	 * unavailable one. With Elementor absent the entry is present but reports
	 * available => false with the 'missing' reason, and carries no version key.
	 *
	 * @since 3.7.0
	 */
	public function test_elementor_reported_unavailable_when_constant_undefined() {
		if ( defined( 'ELEMENTOR_VERSION' ) ) {
			$this->markTestSkipped( 'ELEMENTOR_VERSION already defined (leaked from another test file, or --extra elementor).' );
		}

		// Three one-way definers exist (tests-elementor-importer.php, tests-restore-endpoint.php,
		// tests-import-endpoint.php), each guarded by `if ( ! defined(...) )`, so this test's pass
		// depends on directory-scan order: it must run before any of them defines the constant.
		$this->assertFalse( defined( 'ELEMENTOR_VERSION' ), 'Precondition: ELEMENTOR_VERSION must not be defined when this test runs.' );

		$reflection = new \ReflectionClass( $this->browser );
		$method     = $reflection->getMethod( 'get_available_editors' );
		if (PHP_VERSION_ID < 80100) {
            $method->setAccessible( true );
        }

		$editors = $method->invoke( $this->browser );

		$this->assertArrayHasKey( Constants::EDITOR_ELEMENTOR, $editors );
		$this->assertFalse( $editors[ Constants::EDITOR_ELEMENTOR ]['available'] );
		$this->assertSame( 'missing', $editors[ Constants::EDITOR_ELEMENTOR ]['reason'] );
		$this->assertArrayNotHasKey( 'version', $editors[ Constants::EDITOR_ELEMENTOR ] );

		// With Elementor absent the container overlay must not run: reaching it
		// would fatal on the unloaded \Elementor\Plugin, so the reason can never
		// be 'container' here. This documents that intent; the 'missing' assertion
		// above already regresses if the class_exists guard is removed.
		$this->assertNotSame( 'container', $editors[ Constants::EDITOR_ELEMENTOR ]['reason'] );
	}

	/**
	 * Block editor entry is advertised as unavailable.
	 *
	 * Block imports are deferred (BlockImporter::can_import() returns false), so
	 * get_available_editors() must report the block editor with available => false
	 * to keep the UI from offering an import that would fail.
	 *
	 * @since 3.7.0
	 */
	public function test_block_editor_advertised_unavailable() {
		$reflection = new \ReflectionClass( $this->browser );
		$method     = $reflection->getMethod( 'get_available_editors' );
		if (PHP_VERSION_ID < 80100) {
            $method->setAccessible( true );
        }

		$editors = $method->invoke( $this->browser );

		$this->assertArrayHasKey( Constants::EDITOR_BLOCKS, $editors );
		$this->assertFalse( $editors[ Constants::EDITOR_BLOCKS ]['available'] );
	}

	/**
	 * Elementor editor available flag is tested via tests-elementor-importer.php.
	 *
	 * Removed from this file because defining ELEMENTOR_VERSION here would
	 * pollute the "without Elementor" tests in tests-elementor-importer.php
	 * (which runs after this file alphabetically). The "available at 3.5"
	 * case is covered by test_can_import_returns_true_with_elementor in
	 * tests-elementor-importer.php.
	 */

	/**
	 * Licenses URL has no tab param and matches the REST route's licenses_url.
	 *
	 * The add-on Licenses tab registers no settings of its own, so it is the
	 * wrong target for entering a Pro pass. The URL must point at the plain
	 * settings screen, with no tab, the same way the REST route builds it.
	 *
	 * @since 3.7.0
	 */
	public function test_licenses_url_has_no_tab_and_matches_rest_route() {
		$reflection = new \ReflectionClass( $this->browser );
		$method     = $reflection->getMethod( 'get_base_script_data' );
		if (PHP_VERSION_ID < 80100) {
            $method->setAccessible( true );
        }

		$data = $method->invoke( $this->browser );

		$this->assertArrayHasKey( 'licensesUrl', $data );

		$url = $data['licensesUrl'];

		$this->assertStringNotContainsString( 'tab=licenses', $url );

		$query = array();
		wp_parse_str( (string) wp_parse_url( $url, PHP_URL_QUERY ), $query );
		$this->assertArrayNotHasKey( 'tab', $query );

		$this->assertSame( edd_get_admin_url( array( 'page' => 'edd-settings' ) ), $url );
	}

	/**
	 * The version branch cites the minimum floor when Elementor is not loaded.
	 *
	 * With ELEMENTOR_VERSION undefined the importer is unavailable for the
	 * 'version' reason, so the REST message must cite the minimum version. This
	 * host runs before the default-suite files that define ELEMENTOR_VERSION, so
	 * the version branch is reachable here.
	 *
	 * @since 3.7.0
	 */
	public function test_resolve_editor_importer_version_branch_cites_floor() {
		if ( defined( 'ELEMENTOR_VERSION' ) ) {
			$this->markTestSkipped( 'ELEMENTOR_VERSION already defined — version branch unreachable in this process.' );
		}

		$controller = new \EDD\Pro\REST\Controllers\CheckoutTemplates();
		$result     = $controller->resolve_editor_importer( Constants::EDITOR_ELEMENTOR );

		$this->assertWPError( $result );
		$this->assertSame( 'importer_unavailable', $result->get_error_code() );

		$message = $result->get_error_message();
		$this->assertStringContainsString( EditorRegistry::MIN_ELEMENTOR_VERSION, $message );
	}

	/**
	 * The Elementor version floor has a single source.
	 *
	 * The Pro importer's minimum-version constant resolves to the same value as
	 * the registry constant, so bumping the registry moves both.
	 *
	 * @since 3.7.0
	 */
	public function test_elementor_floor_has_single_source() {
		$importer_floor = ( new \ReflectionClass( \EDD\Pro\Checkout\Templates\Importer\ElementorImporter::class ) )
			->getConstant( 'MIN_ELEMENTOR_VERSION' );

		$this->assertSame( EditorRegistry::MIN_ELEMENTOR_VERSION, $importer_floor );
	}

	/**
	 * The Block Editor availability is the literal false.
	 *
	 * @since 3.7.0
	 */
	public function test_block_editor_availability_is_literal_false() {
		$this->assertFalse( EditorRegistry::is_available( Constants::EDITOR_BLOCKS ) );
	}
}
