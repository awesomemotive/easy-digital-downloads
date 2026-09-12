<?php
/**
 * CheckoutTemplates Tests
 *
 * @package EDD\Tests\Checkout\Templates
 * @since   3.7.0
 */

namespace EDD\Tests\Checkout\Templates;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;
use EDD\Elementor\Subscribers\CheckoutTemplates;

/**
 * CheckoutTemplates tests.
 *
 * Unit tests for the CheckoutTemplates class that handles
 * Elementor integration for the template browser.
 *
 * @group checkout-templates
 */
class CheckoutTemplatesTest extends EDD_UnitTestCase {

	/**
	 * CheckoutTemplates instance.
	 *
	 * @var CheckoutTemplates
	 */
	protected $elementor_editor;

	/**
	 * Test checkout page ID.
	 *
	 * @var int
	 */
	protected static $checkout_page_id;

	/**
	 * Test non-checkout page ID.
	 *
	 * @var int
	 */
	protected static $other_page_id;

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

		// Create another page.
		self::$other_page_id = self::factory()->post->create(
			array(
				'post_type'   => 'page',
				'post_title'  => 'About',
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
		$this->elementor_editor = new CheckoutTemplates();
	}

	/**
	 * Tear down each test.
	 *
	 * Clean up superglobals to prevent test pollution.
	 *
	 * @return void
	 */
	public function tear_down() {
		// Clean up $_GET['post'] to prevent pollution.
		unset( $_GET['post'] );

		parent::tear_down();
	}

	/**
	 * Test CheckoutTemplates implements SubscriberInterface.
	 */
	public function test_implements_subscriber_interface() {
		$this->assertInstanceOf(
			'EDD\EventManagement\SubscriberInterface',
			$this->elementor_editor
		);
	}

	/**
	 * Test get_subscribed_events returns Elementor hooks.
	 *
	 * The ELEMENTOR_VERSION guard was removed when CheckoutTemplates moved
	 * behind the Elementor integration gate (src/Elementor/). Events are
	 * always returned because the class only loads when Elementor is active.
	 */
	public function test_get_subscribed_events_returns_hooks() {
		$events = CheckoutTemplates::get_subscribed_events();

		$this->assertIsArray( $events );
		$this->assertNotEmpty( $events );
		$this->assertArrayHasKey( 'elementor/editor/after_enqueue_scripts', $events );
	}

	/**
	 * Test CheckoutTemplates uses TemplateBrowserTrait.
	 */
	public function test_uses_template_browser_trait() {
		$traits = class_uses( CheckoutTemplates::class );

		$this->assertContains(
			'EDD\Checkout\Templates\Traits\TemplateBrowserTrait',
			$traits
		);
	}

	/**
	 * Test is_checkout_page returns false when no checkout page configured.
	 */
	public function test_is_checkout_page_false_when_not_configured() {
		// Temporarily remove checkout page setting.
		$original = edd_get_option( 'purchase_page' );
		edd_update_option( 'purchase_page', 0 );

		// Access private method.
		$reflection = new \ReflectionClass( $this->elementor_editor );
		$method     = $reflection->getMethod( 'is_checkout_page' );
		if (PHP_VERSION_ID < 80100) {
            $method->setAccessible( true );
        }

		$result = $method->invoke( $this->elementor_editor );

		// Restore.
		edd_update_option( 'purchase_page', $original );

		$this->assertFalse( $result );
	}

	/**
	 * Test is_checkout_page returns true for checkout page.
	 */
	public function test_is_checkout_page_true_for_checkout_page() {
		// Set $_GET['post'] to simulate Elementor editor.
		$_GET['post'] = self::$checkout_page_id;

		// Access private method.
		$reflection = new \ReflectionClass( $this->elementor_editor );
		$method     = $reflection->getMethod( 'is_checkout_page' );
		if (PHP_VERSION_ID < 80100) {
            $method->setAccessible( true );
        }

		$result = $method->invoke( $this->elementor_editor );

		$this->assertTrue( $result );
	}

	/**
	 * Test is_checkout_page returns false for other pages.
	 */
	public function test_is_checkout_page_false_for_other_page() {
		// Set $_GET['post'] to non-checkout page.
		$_GET['post'] = self::$other_page_id;

		// Access private method.
		$reflection = new \ReflectionClass( $this->elementor_editor );
		$method     = $reflection->getMethod( 'is_checkout_page' );
		if (PHP_VERSION_ID < 80100) {
            $method->setAccessible( true );
        }

		$result = $method->invoke( $this->elementor_editor );

		$this->assertFalse( $result );
	}

	/**
	 * Test enqueue_assets skips when not on checkout page.
	 */
	public function test_enqueue_assets_skips_non_checkout_page() {
		// Set $_GET['post'] to non-checkout page.
		$_GET['post'] = self::$other_page_id;

		// Dequeue first.
		wp_dequeue_script( 'edd-checkout-templates' );
		wp_dequeue_style( 'edd-checkout-templates' );

		$this->elementor_editor->enqueue_assets();

		$this->assertFalse( wp_script_is( 'edd-checkout-templates', 'enqueued' ) );
	}

	/**
	 * Test enqueue_assets runs on checkout page.
	 */
	public function test_enqueue_assets_runs_on_checkout_page() {
		// Set $_GET['post'] to checkout page.
		$_GET['post'] = self::$checkout_page_id;

		// Set up admin context.
		wp_set_current_user( self::$admin_user_id );

		$this->elementor_editor->enqueue_assets();

		$this->assertTrue( wp_script_is( 'edd-checkout-templates', 'enqueued' ) );
		$this->assertTrue( wp_style_is( 'edd-checkout-templates', 'enqueued' ) );
	}

	/**
	 * Test script localization contains isElementor flag.
	 */
	public function test_script_localization_contains_is_elementor_flag() {
		// Set $_GET['post'] to checkout page.
		$_GET['post'] = self::$checkout_page_id;

		// Set up admin context.
		wp_set_current_user( self::$admin_user_id );

		$this->elementor_editor->enqueue_assets();

		global $wp_scripts;
		$script = $wp_scripts->registered['edd-checkout-templates'];

		$this->assertStringContainsString( 'isElementor', $script->extra['data'] );
		$this->assertStringContainsString( '"isElementor":"1"', $script->extra['data'] );
	}

	/**
	 * Test elementor button script is not enqueued on non-checkout page.
	 */
	public function test_render_button_script_skips_non_checkout_page() {
		// Set $_GET['post'] to non-checkout page.
		$_GET['post'] = self::$other_page_id;

		// Dequeue in case a previous test enqueued it.
		wp_dequeue_script( 'edd-checkout-templates-elementor' );

		$this->elementor_editor->enqueue_assets();

		$this->assertFalse( wp_script_is( 'edd-checkout-templates-elementor', 'enqueued' ) );
	}

	/**
	 * Test elementor button script and mount-point div are present on checkout page.
	 */
	public function test_render_button_script_outputs_on_checkout_page() {
		// Set $_GET['post'] to checkout page.
		$_GET['post'] = self::$checkout_page_id;

		wp_set_current_user( self::$admin_user_id );

		$this->elementor_editor->enqueue_assets();

		// Script must be enqueued.
		$this->assertTrue( wp_script_is( 'edd-checkout-templates-elementor', 'enqueued' ) );

		// The mount-point div is rendered via the elementor/editor/footer action registered
		// during enqueue_assets(); fire it now and capture the output.
		ob_start();
		do_action( 'elementor/editor/footer' );
		$output = ob_get_clean();

		$this->assertStringContainsString( 'edd-checkout-templates-root', $output );
	}

	/**
	 * Test elementor button stylesheet is enqueued on checkout page.
	 *
	 * Inline styles were moved to the compiled asset
	 * assets/build/css/admin/checkout-templates-elementor.min.css.
	 * Verify the stylesheet handle is registered and enqueued rather than
	 * asserting on inline <style> output.
	 */
	public function test_render_button_script_contains_styles() {
		$_GET['post'] = self::$checkout_page_id;

		wp_set_current_user( self::$admin_user_id );

		$this->elementor_editor->enqueue_assets();

		$this->assertTrue( wp_style_is( 'edd-checkout-templates-elementor', 'enqueued' ) );
	}
}
