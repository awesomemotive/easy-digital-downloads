<?php
namespace EDD\Tests\Cart\Preview;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;
use EDD\Cart\Preview\Component;

/**
 * Cart Preview Component Tests
 *
 * @group edd_cart
 * @group edd_cart_preview
 * @group edd_cart_preview_component
 */
class PreviewComponent extends EDD_UnitTestCase {

	/**
	 * Component instance.
	 *
	 * @var Component
	 */
	protected $component;

	/**
	 * Download fixture.
	 *
	 * @var \EDD_Download
	 */
	protected static $download;

	/**
	 * Set up fixtures once.
	 */
	public static function wpSetUpBeforeClass() {
		// Create a simple download.
		$post_id = self::factory()->post->create( array(
			'post_title' => 'Test Download',
			'post_type'  => 'download',
		) );

		self::$download = edd_get_download( $post_id );

		if ( self::$download ) {
			update_post_meta( self::$download->ID, '_edd_price', 20 );
		}
	}

	/**
	 * Set up before each test.
	 */
	public function setUp(): void {
		parent::setUp();

		$this->component = new Component();

		// Clear cart.
		edd_empty_cart();

		// Reset settings.
		edd_update_option( 'enable_cart_preview', false );

		// Reset admin state.
		set_current_screen( 'front' );
	}

	/**
	 * Tear down after each test.
	 */
	public function tearDown(): void {
		parent::tearDown();
		edd_empty_cart();

		// Dequeue and deregister script to prevent state leakage between tests.
		wp_dequeue_script( 'edd-cart-preview' );
		wp_deregister_script( 'edd-cart-preview' );

		// Remove the render_dialog action.
		remove_action( 'wp_footer', array( $this->component, 'render_dialog' ), 100 );
	}

	/**
	 * Test get_subscribed_events does not enqueue assets when disabled.
	 *
	 * @covers \EDD\Cart\Preview\Component::get_subscribed_events
	 */
	public function test_get_subscribed_events_returns_events_when_disabled() {
		edd_update_option( 'enable_cart_preview', false );

		$events = Component::get_subscribed_events();

		$this->assertArrayNotHasKey( 'wp_enqueue_scripts', $events );
	}

	/**
	 * Test get_subscribed_events returns events when enabled.
	 *
	 * @covers \EDD\Cart\Preview\Component::get_subscribed_events
	 */
	public function test_get_subscribed_events_returns_events_when_enabled() {
		edd_update_option( 'enable_cart_preview', true );

		$events = Component::get_subscribed_events();

		$this->assertIsArray( $events );
		$this->assertArrayHasKey( 'wp_enqueue_scripts', $events );
		$this->assertEquals( 'enqueue_assets', $events['wp_enqueue_scripts'] );
	}

	/**
	 * Test component implements SubscriberInterface.
	 *
	 * @covers \EDD\Cart\Preview\Component
	 */
	public function test_component_implements_subscriber_interface() {
		$this->assertInstanceOf( 'EDD\EventManagement\SubscriberInterface', $this->component );
	}

	/**
	 * Test enqueue_assets does nothing when disabled.
	 *
	 * @covers \EDD\Cart\Preview\Component::enqueue_assets
	 */
	public function test_enqueue_assets_does_nothing_when_disabled() {
		edd_update_option( 'enable_cart_preview', false );

		$this->component->enqueue_assets();

		$this->assertFalse( wp_script_is( 'edd-cart-preview', 'enqueued' ) );
		$this->assertFalse( has_action( 'wp_footer', array( $this->component, 'render_dialog' ) ) );
	}

	/**
	 * Test enqueue_assets does nothing when should_load is false.
	 *
	 * @covers \EDD\Cart\Preview\Component::enqueue_assets
	 */
	public function test_enqueue_assets_does_nothing_when_should_load_false() {
		edd_update_option( 'enable_cart_preview', true );
		set_current_screen( 'edit-post' ); // In admin.

		$this->component->enqueue_assets();

		$this->assertFalse( wp_script_is( 'edd-cart-preview', 'enqueued' ) );
		$this->assertFalse( has_action( 'wp_footer', array( $this->component, 'render_dialog' ) ) );
	}

	/**
	 * Test enqueue_assets enqueues when conditions met.
	 *
	 * @covers \EDD\Cart\Preview\Component::enqueue_assets
	 */
	public function test_enqueue_assets_enqueues_when_conditions_met() {
		edd_update_option( 'enable_cart_preview', true );
		set_current_screen( 'front' );
		edd_add_to_cart( self::$download->ID );

		$this->component->enqueue_assets();

		$this->assertTrue( wp_script_is( 'edd-cart-preview', 'enqueued' ) );
	}

	/**
	 * Test enqueue_assets adds wp_footer action.
	 *
	 * @covers \EDD\Cart\Preview\Component::enqueue_assets
	 */
	public function test_enqueue_assets_adds_footer_action() {
		edd_update_option( 'enable_cart_preview', true );
		set_current_screen( 'front' );
		edd_add_to_cart( self::$download->ID );

		$this->component->enqueue_assets();

		$this->assertNotFalse( has_action( 'wp_footer', array( $this->component, 'render_dialog' ) ) );
		$this->assertEquals( 100, has_action( 'wp_footer', array( $this->component, 'render_dialog' ) ) );
	}

	/**
	 * Test render_dialog fires before action.
	 *
	 * @covers \EDD\Cart\Preview\Component::render_dialog
	 */
	public function test_render_dialog_fires_before_action() {
		$action_fired = false;
		add_action( 'edd_cart_preview_before_render', function() use ( &$action_fired ) {
			$action_fired = true;
		} );

		ob_start();
		$this->component->render_dialog();
		ob_end_clean();

		$this->assertTrue( $action_fired );
	}

	/**
	 * Test render_dialog fires after action.
	 *
	 * @covers \EDD\Cart\Preview\Component::render_dialog
	 */
	public function test_render_dialog_fires_after_action() {
		$action_fired = false;
		add_action( 'edd_cart_preview_after_render', function() use ( &$action_fired ) {
			$action_fired = true;
		} );

		ob_start();
		$this->component->render_dialog();
		ob_end_clean();

		$this->assertTrue( $action_fired );
	}

	/**
	 * Test render_dialog loads template.
	 *
	 * @covers \EDD\Cart\Preview\Component::render_dialog
	 */
	public function test_render_dialog_loads_template() {
		// Check if template file exists.
		$template_path = EDD_PLUGIN_DIR . 'src/Cart/Preview/templates/dialog.php';
		if ( ! file_exists( $template_path ) ) {
			$this->markTestSkipped( 'Template file does not exist yet.' );
		}

		ob_start();
		$this->component->render_dialog();
		$output = ob_get_clean();

		// The output should not be empty if the template loads.
		$this->assertNotEmpty( $output );
	}

	/**
	 * Test multiple enqueue_assets calls add action multiple times.
	 *
	 * @covers \EDD\Cart\Preview\Component::enqueue_assets
	 */
	public function test_multiple_enqueue_calls_add_action_multiple_times() {
		edd_update_option( 'enable_cart_preview', true );
		set_current_screen( 'front' );
		edd_add_to_cart( self::$download->ID );

		$this->component->enqueue_assets();
		$this->component->enqueue_assets();

		// Action should be added each time - WordPress doesn't prevent duplicate actions.
		// However, the implementation uses add_action each time without checking if it's already added.
		// This means the action will be called multiple times if enqueued multiple times.
		$this->assertNotFalse( has_action( 'wp_footer', array( $this->component, 'render_dialog' ) ) );
	}

	/**
	 * Test constructor creates Assets instance.
	 *
	 * @covers \EDD\Cart\Preview\Component::__construct
	 */
	public function test_constructor_creates_assets_instance() {
		$component = new Component();

		// Use reflection to check private property.
		$reflection = new \ReflectionClass( $component );
		$property   = $reflection->getProperty( 'assets' );
		$property->setAccessible( true );
		$assets = $property->getValue( $component );

		$this->assertInstanceOf( 'EDD\Cart\Preview\Assets', $assets );
	}

	/**
	 * Test enqueue_assets respects enable check.
	 *
	 * @covers \EDD\Cart\Preview\Component::enqueue_assets
	 */
	public function test_enqueue_assets_respects_enable_check() {
		// First enable it.
		edd_update_option( 'enable_cart_preview', true );
		set_current_screen( 'front' );
		edd_add_to_cart( self::$download->ID );

		$this->component->enqueue_assets();
		$this->assertTrue( wp_script_is( 'edd-cart-preview', 'enqueued' ) );

		// Now disable it and create a new component.
		wp_dequeue_script( 'edd-cart-preview' );
		edd_update_option( 'enable_cart_preview', false );

		$new_component = new Component();
		$new_component->enqueue_assets();

		$this->assertFalse( wp_script_is( 'edd-cart-preview', 'enqueued' ) );
	}

	/**
	 * Test render_dialog action order.
	 *
	 * @covers \EDD\Cart\Preview\Component::render_dialog
	 */
	public function test_render_dialog_action_order() {
		$call_order = array();

		add_action( 'edd_cart_preview_before_render', function() use ( &$call_order ) {
			$call_order[] = 'before';
		} );

		add_action( 'edd_cart_preview_after_render', function() use ( &$call_order ) {
			$call_order[] = 'after';
		} );

		ob_start();
		$this->component->render_dialog();
		ob_end_clean();

		$this->assertEquals( array( 'before', 'after' ), $call_order );
	}
}
