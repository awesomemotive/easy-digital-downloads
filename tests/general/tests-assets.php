<?php

/**
 * Assets tests.
 *
 * @package     EDD\Tests\General
 */

namespace EDD\Tests\General;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;
use EDD\Admin\Assets\Scripts;
use EDD\Admin\Assets\Styles;

class Assets extends EDD_UnitTestCase {

	public function test_edd_get_assets_url() {
		$this->assertSame( EDD_PLUGIN_URL . 'assets/build/', edd_get_assets_url() );
		$this->assertSame( EDD_PLUGIN_URL . 'assets/vendor/', edd_get_assets_url( 'vendor' ) );
	}

	public function test_edd_get_assets_url_without_path() {
		$url = edd_get_assets_url();
		$this->assertStringEndsWith( 'assets/build/', $url );
	}

	public function test_edd_get_assets_url_with_js_path() {
		$url = edd_get_assets_url( 'js/admin' );
		$this->assertStringEndsWith( 'assets/build/js/admin/', $url );
	}

	public function test_edd_get_assets_url_with_vendor_path() {
		$url = edd_get_assets_url( 'vendor/jquery' );
		$this->assertStringEndsWith( 'assets/vendor/jquery/', $url );
		$this->assertStringNotContainsString( 'build', $url );
	}

	public function test_edd_get_assets_dir() {
		$this->assertSame( EDD_PLUGIN_DIR . 'assets/build/', edd_get_assets_dir() );
		$this->assertSame( EDD_PLUGIN_DIR . 'assets/vendor/', edd_get_assets_dir( 'vendor' ) );
	}

	public function test_edd_get_assets_dir_without_path() {
		$dir = edd_get_assets_dir();
		$this->assertStringEndsWith( 'assets/build/', $dir );
		$this->assertTrue( is_dir( $dir ) );
	}

	public function test_edd_get_assets_dir_with_js_path() {
		$dir = edd_get_assets_dir( 'js/admin' );
		$this->assertStringEndsWith( 'assets/build/js/admin/', $dir );
	}

	/**
	 * @dataProvider _test_includes_assets_dp
	 */
	public function test_includes_assets( $path_to_file ) {
		$this->assertFileExists( $path_to_file );
	}

	/**
	 * Data provider for test_includes_assets().
	 */
	public function _test_includes_assets_dp() {
		return array(
			array( EDD_PLUGIN_DIR . 'assets/build/css/admin/chosen.min.css' ),
			array( EDD_PLUGIN_DIR . 'assets/build/css/admin/admin.min.css' ),
			array( EDD_PLUGIN_DIR . 'assets/images/edd-cpt-2x.png' ),
			array( EDD_PLUGIN_DIR . 'assets/images/edd-cpt.png' ),
			array( EDD_PLUGIN_DIR . 'assets/images/edd-icon-2x.png' ),
			array( EDD_PLUGIN_DIR . 'assets/images/edd-icon.png' ),
			array( EDD_PLUGIN_DIR . 'assets/images/edd-icon.svg' ),
			array( EDD_PLUGIN_DIR . 'assets/images/edd-logo.png' ),
			array( EDD_PLUGIN_DIR . 'assets/images/edd-media.png' ),
			array( EDD_PLUGIN_DIR . 'assets/images/loading.gif' ),
			array( EDD_PLUGIN_DIR . 'templates/images/loading.gif' ),
			array( EDD_PLUGIN_DIR . 'assets/images/media-button.png' ),
			array( EDD_PLUGIN_DIR . 'templates/images/tick.png' ),
			array( EDD_PLUGIN_DIR . 'assets/images/xit.gif' ),
			array( EDD_PLUGIN_DIR . 'assets/build/css/frontend/edd.min.css' ),
			array( EDD_PLUGIN_DIR . 'templates/images/xit.gif' ),
			array( EDD_PLUGIN_DIR . 'assets/build/js/admin/admin.js' ),
			array( EDD_PLUGIN_DIR . 'assets/build/js/frontend/edd-ajax.js' ),
			array( EDD_PLUGIN_DIR . 'assets/build/js/frontend/checkout.js' ),
			array( EDD_PLUGIN_DIR . 'assets/vendor/js/tom-select.complete.min.js' ),
			array( EDD_PLUGIN_DIR . 'assets/build/js/admin/chosen-compat.js' ),
			array( EDD_PLUGIN_DIR . 'assets/vendor/js/jquery.creditcardvalidator.min.js' ),
			array( EDD_PLUGIN_DIR . 'assets/vendor/js/jquery.flot.min.js' ),
			array( EDD_PLUGIN_DIR . 'assets/vendor/js/jquery.validate.min.js' ),
			array( EDD_PLUGIN_DIR . 'assets/vendor/js/tom-select.complete.min.js' ),
		);
	}
}

/**
 * Tests for EDD\Admin\Assets\Scripts registration.
 *
 * @package     EDD\Tests\General
 */
class ScriptRegistration extends EDD_UnitTestCase {

	/**
	 * Run Scripts::register() once before each test, then reset after.
	 */
	public function setUp(): void {
		parent::setUp();
		Scripts::register();
	}

	public function tearDown(): void {
		wp_deregister_script( 'edd-tom-select' );
		wp_deregister_script( 'edd-admin-chosen-compat' );
		wp_deregister_script( 'jquery-chosen' );
		foreach ( array( 'edd-admin', 'edd-admin-chosen', 'edd-admin-menu', 'edd-admin-datepicker' ) as $style ) {
			wp_deregister_style( $style );
		}
		parent::tearDown();
	}

	// -------------------------------------------------------------------------
	// edd-tom-select
	// -------------------------------------------------------------------------

	public function test_edd_tom_select_is_registered() {
		$this->assertTrue( wp_script_is( 'edd-tom-select', 'registered' ) );
	}

	public function test_edd_tom_select_src_points_to_correct_file() {
		global $wp_scripts;
		$this->assertTrue( wp_script_is( 'edd-tom-select', 'registered' ) );
		$script = $wp_scripts->query( 'edd-tom-select', 'registered' );
		$this->assertStringEndsWith( 'tom-select.complete.min.js', $script->src );
	}

	public function test_edd_tom_select_src_is_from_vendor_directory() {
		global $wp_scripts;
		$this->assertTrue( wp_script_is( 'edd-tom-select', 'registered' ) );
		$script = $wp_scripts->query( 'edd-tom-select', 'registered' );
		$this->assertStringContainsString( 'assets/vendor/js/', $script->src );
	}

	public function test_edd_tom_select_has_no_dependencies() {
		global $wp_scripts;
		$this->assertTrue( wp_script_is( 'edd-tom-select', 'registered' ) );
		$script = $wp_scripts->query( 'edd-tom-select', 'registered' );
		$this->assertEmpty( $script->deps );
	}

	public function test_edd_tom_select_loads_in_footer() {
		global $wp_scripts;
		$this->assertSame( 1, $wp_scripts->get_data( 'edd-tom-select', 'group' ) );
	}

	// -------------------------------------------------------------------------
	// edd-admin-chosen-compat (chosen-compat.js)
	// -------------------------------------------------------------------------

	public function test_edd_admin_chosen_compat_is_registered() {
		$this->assertTrue( wp_script_is( 'edd-admin-chosen-compat', 'registered' ) );
	}

	public function test_edd_admin_chosen_compat_src_points_to_the_built_shim() {
		global $wp_scripts;
		$this->assertTrue( wp_script_is( 'edd-admin-chosen-compat', 'registered' ) );
		$script = $wp_scripts->query( 'edd-admin-chosen-compat', 'registered' );
		$this->assertStringEndsWith( 'js/admin/chosen-compat.js', $script->src );
	}

	public function test_edd_admin_chosen_compat_depends_only_on_jquery_and_tom_select() {
		global $wp_scripts;
		$this->assertTrue( wp_script_is( 'edd-admin-chosen-compat', 'registered' ) );
		$script = $wp_scripts->query( 'edd-admin-chosen-compat', 'registered' );
		$this->assertSame( array( 'jquery', 'edd-tom-select' ), $script->deps );
	}

	public function test_edd_admin_chosen_compat_loads_in_footer() {
		global $wp_scripts;
		$this->assertTrue( wp_script_is( 'edd-admin-chosen-compat', 'registered' ) );
		$this->assertSame( 1, $wp_scripts->get_data( 'edd-admin-chosen-compat', 'group' ) );
	}

	// -------------------------------------------------------------------------
	// jquery-chosen is left alone off EDD screens
	// -------------------------------------------------------------------------

	public function test_jquery_chosen_is_not_registered_off_edd_screens() {
		$this->assertFalse( wp_script_is( 'jquery-chosen', 'registered' ) );
	}

	public function test_jquery_chosen_style_is_not_registered_off_edd_screens() {
		Styles::register();

		$this->assertTrue( wp_style_is( 'edd-admin-chosen', 'registered' ) );
		$this->assertFalse( wp_style_is( 'jquery-chosen', 'registered' ) );
	}

	public function test_jquery_chosen_from_another_plugin_survives_registration() {
		$other_plugin_src = 'https://example.org/wp-content/plugins/other/chosen.jquery.js';
		wp_register_script( 'jquery-chosen', $other_plugin_src, array( 'jquery' ), '1.8.7', true );

		Scripts::register();

		global $wp_scripts;
		$this->assertTrue( wp_script_is( 'jquery-chosen', 'registered' ) );
		$script = $wp_scripts->query( 'jquery-chosen', 'registered' );
		$this->assertSame( $other_plugin_src, $script->src );
	}

	// -------------------------------------------------------------------------
	// React app dependencies
	// -------------------------------------------------------------------------

	public function test_edd_admin_tax_rates_depends_on_wp_element() {
		global $wp_scripts;
		$script = $wp_scripts->query( 'edd-admin-tax-rates', 'registered' );
		$this->assertContains( 'wp-element', $script->deps );
	}

	public function test_edd_admin_tax_rates_depends_on_edd_tom_select() {
		global $wp_scripts;
		$script = $wp_scripts->query( 'edd-admin-tax-rates', 'registered' );
		$this->assertContains( 'edd-tom-select', $script->deps );
	}

	public function test_edd_admin_tax_rates_does_not_depend_on_backbone() {
		global $wp_scripts;
		$script = $wp_scripts->query( 'edd-admin-tax-rates', 'registered' );
		$this->assertNotContains( 'wp-backbone', $script->deps );
	}

	public function test_edd_admin_orders_depends_on_wp_element() {
		global $wp_scripts;
		$script = $wp_scripts->query( 'edd-admin-orders', 'registered' );
		$this->assertContains( 'wp-element', $script->deps );
	}

	public function test_edd_admin_orders_does_not_depend_on_backbone() {
		global $wp_scripts;
		$script = $wp_scripts->query( 'edd-admin-orders', 'registered' );
		$this->assertNotContains( 'wp-backbone', $script->deps );
	}

	public function test_edd_admin_orders_depends_on_notes() {
		global $wp_scripts;
		$script = $wp_scripts->query( 'edd-admin-orders', 'registered' );
		$this->assertContains( 'edd-admin-notes', $script->deps );
	}

	public function test_edd_admin_orders_depends_on_tom_select_exactly_once() {
		global $wp_scripts;
		$script = $wp_scripts->query( 'edd-admin-orders', 'registered' );

		// The shared admin dependencies already declare it for every per-page script,
		// so listing it again per page duplicates the handle.
		$this->assertCount( 1, array_keys( $script->deps, 'edd-tom-select', true ) );
	}
}

/**
 * Tests for the generic Chosen handles, which EDD aliases only on its own screens.
 *
 * @package     EDD\Tests\General
 */
class ChosenCompatRegistration extends EDD_UnitTestCase {

	/**
	 * Load the admin assets for an EDD screen before each test, then reset after.
	 */
	public function setUp(): void {
		parent::setUp();
		add_filter( 'edd_is_admin_page', '__return_true' );
		Scripts::register();
		Styles::register();
		Scripts::enqueue( 'settings.php' );
		Styles::enqueue( 'settings.php' );
	}

	public function tearDown(): void {
		remove_filter( 'edd_is_admin_page', '__return_true' );

		global $wp_scripts, $wp_styles;

		foreach ( array( 'edd-tom-select', 'edd-admin-chosen-compat', 'edd-admin-scripts', 'edd-admin-tax-rates', 'jquery-chosen' ) as $script ) {
			wp_dequeue_script( $script );
			wp_deregister_script( $script );
		}

		// Core handles that Scripts::enqueue() queued are dequeued only, never deregistered.
		foreach ( array( 'jquery-form', 'jquery-ui-datepicker', 'jquery-ui-dialog', 'jquery-ui-tooltip', 'media-upload', 'wp-ajax-response', 'wp-color-picker' ) as $script ) {
			wp_dequeue_script( $script );
		}

		foreach ( array( 'edd-admin', 'edd-admin-chosen', 'edd-admin-menu', 'edd-admin-datepicker', 'jquery-chosen' ) as $style ) {
			wp_dequeue_style( $style );
			wp_deregister_style( $style );
		}

		foreach ( array( 'wp-jquery-ui-dialog', 'wp-color-picker' ) as $style ) {
			wp_dequeue_style( $style );
		}

		// do_items() leaves done/to_do/groups populated, which would strand a later test's queue.
		$wp_scripts->done = array();
		$wp_scripts->to_do = array();
		$wp_scripts->groups = array();
		$wp_styles->done = array();
		$wp_styles->to_do = array();
		$wp_styles->groups = array();

		parent::tearDown();
	}

	// -------------------------------------------------------------------------
	// Fixture
	// -------------------------------------------------------------------------

	public function test_edd_admin_chosen_compat_is_registered_on_an_edd_screen() {
		$this->assertTrue( wp_script_is( 'edd-admin-chosen-compat', 'registered' ) );
	}

	public function test_edd_admin_chosen_style_is_registered_on_an_edd_screen() {
		$this->assertTrue( wp_style_is( 'edd-admin-chosen', 'registered' ) );
	}

	// -------------------------------------------------------------------------
	// The shim is what EDD enqueues
	// -------------------------------------------------------------------------

	public function test_edd_admin_chosen_compat_is_enqueued_on_an_edd_screen() {
		$this->assertTrue( wp_script_is( 'edd-admin-chosen-compat', 'enqueued' ) );
	}

	/**
	 * The tax rates screen must reach for neither Chosen handle.
	 *
	 * It builds its own Tom Select field, so a dependency on either the shim or the generic
	 * alias would load Chosen's jQuery surface for a screen that has no jQuery left to serve.
	 */
	public function test_admin_tax_rates_depends_on_neither_chosen_handle() {
		global $wp_scripts;
		$this->assertTrue( wp_script_is( 'edd-admin-tax-rates', 'registered' ) );
		$script = $wp_scripts->query( 'edd-admin-tax-rates', 'registered' );
		$this->assertNotContains( 'edd-admin-chosen-compat', $script->deps );
		$this->assertNotContains( 'jquery-chosen', $script->deps );
	}

	// -------------------------------------------------------------------------
	// jquery-chosen is a deps-only alias
	// -------------------------------------------------------------------------

	public function test_jquery_chosen_script_is_registered() {
		$this->assertTrue( wp_script_is( 'jquery-chosen', 'registered' ) );
	}

	public function test_jquery_chosen_script_alias_has_no_source_of_its_own() {
		global $wp_scripts;
		$this->assertTrue( wp_script_is( 'jquery-chosen', 'registered' ) );
		$script = $wp_scripts->query( 'jquery-chosen', 'registered' );
		$this->assertFalse( $script->src );
	}

	public function test_jquery_chosen_script_alias_pulls_in_the_shim_alone() {
		global $wp_scripts;
		$this->assertTrue( wp_script_is( 'jquery-chosen', 'registered' ) );
		$script = $wp_scripts->query( 'jquery-chosen', 'registered' );
		$this->assertSame( array( 'edd-admin-chosen-compat' ), $script->deps );
	}

	public function test_jquery_chosen_style_alias_has_no_stylesheet_of_its_own() {
		global $wp_styles;
		$this->assertTrue( wp_style_is( 'jquery-chosen', 'registered' ) );
		$style = $wp_styles->query( 'jquery-chosen', 'registered' );
		$this->assertFalse( $style->src );
	}

	public function test_jquery_chosen_style_alias_pulls_in_edd_admin_chosen_alone() {
		global $wp_styles;
		$this->assertTrue( wp_style_is( 'jquery-chosen', 'registered' ) );
		$style = $wp_styles->query( 'jquery-chosen', 'registered' );
		$this->assertSame( array( 'edd-admin-chosen' ), $style->deps );
	}

	public function test_jquery_chosen_style_is_not_enqueued_by_edd() {
		$this->assertTrue( wp_style_is( 'edd-admin-chosen', 'enqueued' ) );
		$this->assertFalse( wp_style_is( 'jquery-chosen', 'enqueued' ) );
	}

	// -------------------------------------------------------------------------
	// Enqueuing the alias loads the real asset
	// -------------------------------------------------------------------------

	public function test_enqueuing_the_jquery_chosen_script_alias_prints_the_shim() {
		global $wp_scripts;
		$this->assertTrue( wp_script_is( 'jquery-chosen', 'registered' ) );

		// EDD already queued the shim, so isolate the alias as its only route into done.
		$this->reset_script_queue();
		wp_enqueue_script( 'jquery-chosen' );
		$this->assertNotContains( 'edd-admin-chosen-compat', $wp_scripts->done );

		ob_start();
		$wp_scripts->do_items( false, 1 );
		ob_end_clean();

		$this->assertContains( 'edd-admin-chosen-compat', $wp_scripts->done );
	}

	public function test_enqueuing_the_jquery_chosen_style_alias_prints_edd_admin_chosen() {
		global $wp_styles;
		$this->assertTrue( wp_style_is( 'jquery-chosen', 'registered' ) );

		// EDD already queued the stylesheet, so isolate the alias as its only route into done.
		$this->reset_style_queue();
		wp_enqueue_style( 'jquery-chosen' );
		$this->assertNotContains( 'edd-admin-chosen', $wp_styles->done );

		ob_start();
		$wp_styles->do_items();
		ob_end_clean();

		$this->assertContains( 'edd-admin-chosen', $wp_styles->done );
	}

	/**
	 * Empty the script queue so a handle can only reach done through what this test enqueues.
	 */
	private function reset_script_queue() {
		global $wp_scripts;
		$wp_scripts->queue = array();
		$wp_scripts->to_do = array();
		$wp_scripts->done = array();
		$wp_scripts->groups = array();
	}

	/**
	 * Empty the style queue so a handle can only reach done through what this test enqueues.
	 */
	private function reset_style_queue() {
		global $wp_styles;
		$wp_styles->queue = array();
		$wp_styles->to_do = array();
		$wp_styles->done = array();
		$wp_styles->groups = array();
	}
}
