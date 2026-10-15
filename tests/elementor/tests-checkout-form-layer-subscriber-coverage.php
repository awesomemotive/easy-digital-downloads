<?php
/**
 * Coverage tests for the CheckoutFormLayer subscriber's bookkeeping helpers.
 *
 * These exercise the subscriber's pure PHP bookkeeping (event map, cart emptiness,
 * element-id resolution, section fallbacks, engagement/failsafe/reset), which have
 * no Elementor dependency, so they run against a lightweight fake element rather than
 * a real Elementor node.
 *
 * @package     EDD\Tests\Elementor
 * @copyright   Copyright (c) 2026, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.7.0
 */

namespace EDD\Tests\Elementor;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;
use EDD\Tests\Helpers\EDD_Helper_Download;
use EDD\Elementor\Checkout\FormLayer;
use EDD\Elementor\Subscribers\CheckoutFormLayer;

/**
 * Coverage tests for the form-layer subscriber's bookkeeping helpers.
 *
 * @covers \EDD\Elementor\Subscribers\CheckoutFormLayer
 *
 * @group elementor
 *
 * @since 3.7.0
 */
class CheckoutFormLayerSubscriberCoverage extends EDD_UnitTestCase {

	/**
	 * A simple download so an engaged render has cart contents.
	 *
	 * @var \WP_Post
	 */
	private static $download;

	/**
	 * Create the shared download once.
	 */
	public static function set_up_before_class() {
		parent::set_up_before_class();
		self::$download = EDD_Helper_Download::create_simple_download();
	}

	/**
	 * Remove the shared download.
	 */
	public static function tear_down_after_class() {
		EDD_Helper_Download::delete_download( self::$download->ID );
		parent::tear_down_after_class();
	}

	/**
	 * Reset the render-pass state and cart before each test.
	 */
	public function setUp(): void {
		parent::setUp();

		edd_empty_cart();
		FormLayer::reset_engaged();
		FormLayer::clear_empty_cart();
		FormLayer::clear_rendered_sections();
	}

	/**
	 * Clean up cart and form-layer engagement state after each test.
	 */
	public function tear_down() {
		edd_empty_cart();
		FormLayer::reset_engaged();
		FormLayer::clear_empty_cart();
		parent::tear_down();
	}

	/**
	 * The subscriber maps the render-pass and element render hooks.
	 *
	 * @since 3.7.0
	 */
	public function test_get_subscribed_events() {
		$events = CheckoutFormLayer::get_subscribed_events();

		$this->assertSame( 'reset_for_new_render_pass', $events['elementor/frontend/before_get_builder_content'] );
		$this->assertSame( 'before_render', $events['elementor/frontend/edd-checkout-box/before_render'] );
		$this->assertSame( 'after_render', $events['elementor/frontend/edd-checkout-box/after_render'] );
	}

	/**
	 * cart_is_empty reflects the EDD cart contents.
	 *
	 * @since 3.7.0
	 */
	public function test_cart_is_empty() {
		$subscriber = new CheckoutFormLayer();
		$method     = new \ReflectionMethod( $subscriber, 'cart_is_empty' );
		if (PHP_VERSION_ID < 80100) {
            $method->setAccessible( true );
        }

		$this->assertTrue( $method->invoke( $subscriber ), 'An empty cart must report empty.' );

		edd_add_to_cart( self::$download->ID );
		$this->assertFalse( $method->invoke( $subscriber ), 'A populated cart must report not-empty.' );
	}

	/**
	 * get_element_id prefers the raw id, falls back to get_id(), and empties cleanly.
	 *
	 * @since 3.7.0
	 */
	public function test_get_element_id_resolution() {
		$subscriber = new CheckoutFormLayer();
		$method     = new \ReflectionMethod( $subscriber, 'get_element_id' );
		if (PHP_VERSION_ID < 80100) {
            $method->setAccessible( true );
        }

		$this->assertSame( 'rawId', $method->invoke( $subscriber, $this->fake_element( 'rawId' ) ) );

		// No raw id -> falls back to get_id().
		$this->assertSame( 'fbId', $method->invoke( $subscriber, $this->fake_element( 'fbId', false ) ) );
	}

	/**
	 * get_raw_element_data returns [] for an element without get_raw_data().
	 *
	 * @since 3.7.0
	 */
	public function test_get_raw_element_data_without_method() {
		$subscriber = new CheckoutFormLayer();
		$method     = new \ReflectionMethod( $subscriber, 'get_raw_element_data' );
		if (PHP_VERSION_ID < 80100) {
            $method->setAccessible( true );
        }

		$plain = new \stdClass();

		$this->assertSame( array(), $method->invoke( $subscriber, $plain ) );
	}

	/**
	 * The box-scoped section fallbacks register on the form hooks and unregister.
	 *
	 * @since 3.7.0
	 */
	public function test_register_and_unregister_section_fallbacks() {
		$subscriber = new CheckoutFormLayer();

		$register = new \ReflectionMethod( $subscriber, 'register_section_fallbacks' );
		if (PHP_VERSION_ID < 80100) {
            $register->setAccessible( true );
        }
		$register->invoke( $subscriber, 'fbBox', array() );

		$this->assertNotFalse( has_action( 'edd_checkout_form_top' ), 'A personal-info fallback must hook the form top.' );
		$this->assertNotFalse( has_action( 'edd_checkout_form_bottom' ), 'A payment-info fallback must hook the form bottom.' );

		$unregister = new \ReflectionMethod( $subscriber, 'unregister_section_fallbacks' );
		if (PHP_VERSION_ID < 80100) {
            $unregister->setAccessible( true );
        }
		$unregister->invoke( $subscriber, 'fbBox' );

		// Idempotent: a second unregister for an unknown id is a no-op.
		$unregister->invoke( $subscriber, 'unknownBox' );

		$this->assertTrue( true );
	}

	/**
	 * An empty id is not engaged by before_render.
	 *
	 * @since 3.7.0
	 */
	public function test_before_render_ignores_element_without_id() {
		$subscriber = new CheckoutFormLayer();

		$element = new class() {
			public function get_raw_data() {
				return array(
					'id'       => '',
					'elType'   => 'edd-checkout-box',
					'elements' => array(),
				);
			}
		};

		$subscriber->before_render( $element );

		// No engagement recorded for an unresolvable id.
		$this->assertFalse( FormLayer::is_box_engaged() );
	}

	/**
	 * Engaging then running the shutdown failsafe clears the leaked state.
	 *
	 * before_render on a populated cart engages the box and registers the one-time
	 * shutdown failsafe; firing the shutdown action runs the failsafe closure, which
	 * clears the engaged element and resets the flag.
	 *
	 * @since 3.7.0
	 */
	public function test_shutdown_failsafe_clears_leaked_engagement() {
		global $wp_filter;

		edd_add_to_cart( self::$download->ID );

		$shutdown_before = isset( $wp_filter['shutdown'] ) ? $wp_filter['shutdown']->callbacks : array();

		$subscriber = new CheckoutFormLayer();
		$element    = $this->fake_element( 'leakBox' );

		ob_start();
		$subscriber->before_render( $element );
		ob_get_clean();

		$this->assertTrue( FormLayer::is_engaged_element( 'leakBox' ), 'The box must engage on a populated cart.' );

		// Invoke ONLY the subscriber's own shutdown failsafe closure(s) — firing WP's
		// global shutdown would also run core's buffer-flush handlers and close
		// PHPUnit's own output buffer.
		foreach ( $wp_filter['shutdown']->callbacks as $priority => $callbacks ) {
			foreach ( $callbacks as $id => $callback ) {
				if ( ! isset( $shutdown_before[ $priority ][ $id ] ) ) {
					call_user_func( $callback['function'] );
				}
			}
		}

		$this->assertFalse( FormLayer::is_engaged_element( 'leakBox' ), 'The shutdown failsafe must clear the leaked engagement.' );
	}

	/**
	 * reset_for_new_render_pass tears down a stuck engagement (leaked teardown).
	 *
	 * @since 3.7.0
	 */
	public function test_reset_for_new_render_pass_clears_stuck_map() {
		edd_add_to_cart( self::$download->ID );

		$subscriber = new CheckoutFormLayer();
		$element    = $this->fake_element( 'stuckPass' );

		ob_start();
		$subscriber->before_render( $element );
		ob_get_clean();

		$this->assertTrue( FormLayer::is_engaged_element( 'stuckPass' ) );

		$subscriber->reset_for_new_render_pass();

		$this->assertFalse( FormLayer::is_engaged_element( 'stuckPass' ), 'A new render pass must clear the stuck engagement.' );
	}

	/**
	 * A fake element exposing get_raw_data()/get_id() like the runtime element.
	 *
	 * @param string $id       Element id.
	 * @param bool    $with_raw Whether get_raw_data() carries an id.
	 * @return object
	 */
	private function fake_element( string $id, bool $with_raw = true ) {
		return new class( $id, $with_raw ) {
			public $id;
			public $with_raw;
			public $settings = array();

			public function __construct( $id, $with_raw ) {
				$this->id       = $id;
				$this->with_raw = $with_raw;
			}

			public function get_raw_data() {
				return array(
					'id'       => $this->with_raw ? $this->id : '',
					'elType'   => 'edd-checkout-box',
					'settings' => array(),
					'elements' => array(),
				);
			}

			public function get_id() {
				return $this->id;
			}

			public function set_settings( $key, $value ) {
				$this->settings[ $key ] = $value;
			}

			public function set_render_attribute( $element, $value ) {}
		};
	}
}
