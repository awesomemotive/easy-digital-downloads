<?php
/**
 * Tests for EDD\Elementor\Checkout\FormLayer and the checkout-block suppression
 * path in EDD\Elementor\Subscribers\Checkout.
 *
 * FormLayer is exercised as pure string output: the engage decision over a raw
 * _elementor_data-shaped array and the wrapper markup it emits. The suppression
 * tests drive the pre_render_block filter callback with an Elementor document
 * whose saved data reports the checkout container — against the real
 * \Elementor\Plugin singleton under --extra elementor, or a minimal
 * Plugin shim under the stub suite.
 *
 * @package     EDD\Tests\Elementor
 * @copyright   Copyright (c) 2025, Sandhills Development, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.7.0
 */

namespace EDD\Tests\Elementor;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;
use EDD\Elementor\Checkout\FormLayer;
use EDD\Elementor\Subscribers\Checkout;
use EDD\Tests\Elementor\Support\RealElementorFixture;

require_once __DIR__ . '/support/fakes-checkout-form-layer.php';

/**
 * Tests for the checkout form-layer string helper and block suppression.
 *
 * @covers \EDD\Elementor\Checkout\FormLayer
 * @covers \EDD\Elementor\Subscribers\Checkout::prevent_checkout_block_render
 *
 * @group elementor
 *
 * @since 3.7.0
 */
class Tests_Checkout_Form_Layer extends EDD_UnitTestCase {

	use RealElementorFixture;

	// -----------------------------------------------------------------------
	// FormLayer::should_engage() engage rule.
	//
	// Engage ONLY when the element is an edd-checkout-box elType. A box MISSING a
	// required section must still engage so the fallback renderer can fill the
	// gap — engagement and completeness are separate questions. A plain container
	// that merely holds EDD section widgets does NOT engage: only the checkout box
	// opens the purchase form. The only other guard is the edd_get_checkout_uri()
	// existence check (non-checkout context never engages).
	// -----------------------------------------------------------------------

	/**
	 * should_engage returns true when all required sections incl. payment are present.
	 *
	 * @since 3.7.0
	 *
	 * @covers \EDD\Elementor\Checkout\FormLayer::should_engage
	 */
	public function test_should_engage_returns_true_with_payment_and_required_sections() {
		$elements = array( $this->engageable_checkout_box() );

		$this->assertTrue( FormLayer::should_engage( $elements ) );
	}

	/**
	 * should_engage returns TRUE for a box missing the payment-info section.
	 *
	 * A box missing a required section must engage so the fallback renderer can
	 * supply the missing section markup inside the wrapper.
	 *
	 * @since 3.7.0
	 *
	 * @covers \EDD\Elementor\Checkout\FormLayer::should_engage
	 */
	public function test_should_engage_returns_true_for_box_missing_payment_section() {
		$elements = array( $this->checkout_box_without_payment() );

		$this->assertTrue( FormLayer::should_engage( $elements ) );
	}

	/**
	 * should_engage returns TRUE for a personal-only box (has personal-info but
	 * not payment-info) — a personal-only box must now engage.
	 *
	 * @since 3.7.0
	 *
	 * @covers \EDD\Elementor\Checkout\FormLayer::should_engage
	 */
	public function test_should_engage_returns_true_for_personal_only_box() {
		$elements = array( $this->checkout_box_without_payment() );

		$this->assertTrue( FormLayer::should_engage( $elements ) );
	}

	/**
	 * should_engage returns TRUE for a box with no required sections at all
	 * (empty edd-checkout-box), because it is still an edd-checkout-box elType.
	 *
	 * @since 3.7.0
	 *
	 * @covers \EDD\Elementor\Checkout\FormLayer::should_engage
	 */
	public function test_should_engage_returns_true_for_box_with_no_required_sections() {
		$elements = array(
			array(
				'elType'   => 'edd-checkout-box',
				'settings' => array(),
				'elements' => array(),
			),
		);

		$this->assertTrue( FormLayer::should_engage( $elements ) );
	}

	/**
	 * should_engage returns FALSE for a native container (not edd-checkout-box
	 * elType) even when it holds EDD checkout section widgets. should_engage is a
	 * box-DETECTION helper: a plain native container is not an edd-checkout-box, so
	 * it is not detected as a box. Whether the page-level purchase form opens is a
	 * separate question decided at the checkout region boundary, not here.
	 *
	 * @since 3.7.0
	 *
	 * @covers \EDD\Elementor\Checkout\FormLayer::should_engage
	 */
	public function test_should_engage_returns_false_for_native_container_with_section_widget() {
		$elements = array(
			array(
				'elType'   => 'container',
				'settings' => array(),
				'elements' => array(
					array(
						'elType'     => 'widget',
						'widgetType' => 'edd-checkout-personal-info',
						'settings'   => array(),
						'elements'   => array(),
					),
				),
			),
		);

		$this->assertFalse( FormLayer::should_engage( $elements ) );
	}

	/**
	 * should_engage returns false on an empty elements array (no box elType,
	 * no section widgets anywhere) — nothing to engage.
	 *
	 * @since 3.7.0
	 *
	 * @covers \EDD\Elementor\Checkout\FormLayer::should_engage
	 */
	public function test_should_engage_returns_false_on_empty_elements() {
		$this->assertFalse( FormLayer::should_engage( array() ) );
	}

	/**
	 * should_engage returns FALSE for a Theme-Builder header/footer template pass.
	 *
	 * A header/footer template renders an element tree that holds no
	 * edd-checkout-box (a logo, a nav menu, etc.). Functionally this is "a render
	 * with no checkout box present", so it must not engage the form layer — no
	 * purchase form opens in a header or footer. Modeled on the existing
	 * "no box present" shape (test_should_engage_returns_false_on_empty_elements /
	 * _for_native_container_with_section_widget) rather than inventing Theme-Builder
	 * scaffolding.
	 *
	 * @since 3.7.0
	 *
	 * @covers \EDD\Elementor\Checkout\FormLayer::should_engage
	 */
	public function test_should_engage_returns_false_for_header_footer_template_pass() {
		$header_footer_elements = array(
			array(
				'elType'   => 'container',
				'settings' => array(),
				'elements' => array(
					array(
						'elType'     => 'widget',
						'widgetType' => 'theme-site-logo',
						'settings'   => array(),
						'elements'   => array(),
					),
					array(
						'elType'     => 'widget',
						'widgetType' => 'nav-menu',
						'settings'   => array(),
						'elements'   => array(),
					),
				),
			),
		);

		$this->assertFalse(
			FormLayer::should_engage( $header_footer_elements ),
			'A header/footer template pass with no edd-checkout-box must not engage the form layer.'
		);
	}

	// -----------------------------------------------------------------------
	// FormLayer wrapper markup — must match the block checkout DOM contract.
	// -----------------------------------------------------------------------

	/**
	 * wrap_open emits an opening div carrying the wrapper id and the block class.
	 *
	 * The compound CSS selector requires both the WRAP_ID and the
	 * wp-block-edd-checkout class on the same opening div.
	 *
	 * @since 3.7.0
	 *
	 * @covers \EDD\Elementor\Checkout\FormLayer::wrap_open
	 */
	public function test_wrap_open_emits_wrapper_id_and_block_class() {
		$open = FormLayer::wrap_open();

		$this->assertStringContainsString( '<div', $open );
		$this->assertStringContainsString( 'id="edd_checkout_form_wrap"', $open );
		$this->assertStringContainsString( 'wp-block-edd-checkout', $open );
	}

	/**
	 * wrap_close emits the matching closing div.
	 *
	 * @since 3.7.0
	 *
	 * @covers \EDD\Elementor\Checkout\FormLayer::wrap_close
	 */
	public function test_wrap_close_emits_closing_div() {
		$this->assertSame( '</div>', FormLayer::wrap_close() );
	}

	/**
	 * The form and wrapper id constants match the block checkout contract.
	 *
	 * @since 3.7.0
	 */
	public function test_form_and_wrap_id_constants() {
		$this->assertSame( 'edd_purchase_form', FormLayer::FORM_ID );
		$this->assertSame( 'edd_checkout_form_wrap', FormLayer::WRAP_ID );
	}

	// -----------------------------------------------------------------------
	// Checkout::prevent_checkout_block_render() — suppress the edd/checkout
	// Gutenberg block when the current page's Elementor data carries a checkout
	// container. Under --extra elementor the lookup runs against the real
	// \Elementor\Plugin singleton; under the stub suite it falls back
	// to a minimal Plugin shim.
	// -----------------------------------------------------------------------

	/**
	 * The edd/checkout block is suppressed when the page has an edd-checkout-box.
	 *
	 * @since 3.7.0
	 *
	 * @covers \EDD\Elementor\Subscribers\Checkout::prevent_checkout_block_render
	 */
	public function test_checkout_block_suppressed_for_checkout_box_container() {
		$this->stub_elementor_page( array( $this->engageable_checkout_box() ) );

		$subscriber = new Checkout();
		$result     = $subscriber->prevent_checkout_block_render( null, array( 'blockName' => 'edd/checkout' ), null );

		$this->assertSame( '', $result );

		$this->reset_elementor_page();
	}

	/**
	 * The edd/checkout block is still suppressed for the legacy edd-checkout widget.
	 *
	 * Guards the early-return refactor: a page built with the legacy widget
	 * (rather than the container) must continue to suppress the block.
	 *
	 * @since 3.7.0
	 *
	 * @covers \EDD\Elementor\Subscribers\Checkout::prevent_checkout_block_render
	 */
	public function test_checkout_block_suppressed_for_legacy_checkout_widget() {
		$legacy = array(
			array(
				'elType'     => 'widget',
				'widgetType' => 'edd-checkout',
				'settings'   => array(),
				'elements'   => array(),
			),
		);
		$this->stub_elementor_page( $legacy );

		$subscriber = new Checkout();
		$result     = $subscriber->prevent_checkout_block_render( null, array( 'blockName' => 'edd/checkout' ), null );

		$this->assertSame( '', $result );

		$this->reset_elementor_page();
	}

	/**
	 * A non-checkout block passes through unchanged.
	 *
	 * @since 3.7.0
	 *
	 * @covers \EDD\Elementor\Subscribers\Checkout::prevent_checkout_block_render
	 */
	public function test_other_block_passes_through_unchanged() {
		$subscriber = new Checkout();
		$pre_render = 'untouched';

		$result = $subscriber->prevent_checkout_block_render( $pre_render, array( 'blockName' => 'core/paragraph' ), null );

		$this->assertSame( $pre_render, $result );
	}

	// -----------------------------------------------------------------------
	// CheckoutBox::is_dynamic_content() — the box opts out of Elementor's
	// element cache so its request-dependent output is never statically cached.
	// The element class extends the native Container, so the box is constructed
	// against real Elementor via the registration subscriber (RealElementorFixture).
	// -----------------------------------------------------------------------

	/**
	 * The checkout box reports is_dynamic_content() true so Elementor never
	 * element-caches its output.
	 *
	 * The native Container returns false here, which would let the element cache
	 * store the box's rendered HTML and replay it for the cache TTL without
	 * re-running print_content(); the box's cart/nonce/gateway-dependent output
	 * would go stale. Returning true opts the box out of that cache.
	 *
	 * @since 3.7.0
	 *
	 * @covers \EDD\Elementor\Elements\CheckoutBox::is_dynamic_content
	 */
	public function test_checkout_box_is_dynamic_content_opts_out_of_element_cache() {
		$this->edd_boot_real_elementor();

		$manager    = new FakeElementsManager();
		$subscriber = new \EDD\Elementor\Subscribers\CheckoutBox();
		$subscriber->register_element( $manager );

		$box = $manager->registered[0];

		$is_dynamic_content = new \ReflectionMethod( $box, 'is_dynamic_content' );
		if (PHP_VERSION_ID < 80100) {
            $is_dynamic_content->setAccessible( true );
        }

		$this->assertTrue(
			$is_dynamic_content->invoke( $box ),
			'The checkout box must be dynamic content so Elementor never element-caches its request-dependent output.'
		);
	}

	// -----------------------------------------------------------------------
	// Fixture builders — raw element-data arrays that mirror the saved
	// _elementor_data shape the form-layer and subscriber receive at runtime.
	// -----------------------------------------------------------------------

	/**
	 * Build an edd-checkout-box container holding both required section widgets
	 * plus the payment-info section (a fully-engageable checkout).
	 *
	 * @since 3.7.0
	 *
	 * @return array A raw edd-checkout-box container node.
	 */
	private function engageable_checkout_box(): array {
		return array(
			'elType'   => 'edd-checkout-box',
			'settings' => array(),
			'elements' => array(
				array(
					'elType'     => 'widget',
					'widgetType' => 'edd-checkout-personal-info',
					'settings'   => array(),
					'elements'   => array(),
				),
				array(
					'elType'     => 'widget',
					'widgetType' => 'edd-checkout-payment-info',
					'settings'   => array(),
					'elements'   => array(),
				),
			),
		);
	}

	/**
	 * Build an edd-checkout-box container missing the payment-info section.
	 *
	 * @since 3.7.0
	 *
	 * @return array A raw edd-checkout-box container node without payment-info.
	 */
	private function checkout_box_without_payment(): array {
		return array(
			'elType'   => 'edd-checkout-box',
			'settings' => array(),
			'elements' => array(
				array(
					'elType'     => 'widget',
					'widgetType' => 'edd-checkout-personal-info',
					'settings'   => array(),
					'elements'   => array(),
				),
			),
		);
	}

	/**
	 * Set the active Elementor kit's global container width for a test, deterministically.
	 *
	 * kits_manager->get_current_settings( 'container_width' ), which resolves the
	 * active kit through the documents-manager cache. get_active_kit() returns that
	 * SAME cached document object, so setting the value on it in-memory is visible to
	 * the read path immediately — no DB round-trip or cache purge needed. Boots real
	 * Elementor first (skips the test under the plain stub suite).
	 *
	 * @since 3.7.0
	 *
	 * @param array $width The container_width value to set (e.g. ['unit'=>'px','size'=>812]).
	 * @return callable A restore closure that puts the original width back.
	 */
	private function set_kit_container_width( array $width ): callable {
		$plugin = $this->edd_boot_real_elementor();
		$kit    = $plugin->kits_manager->get_active_kit();

		$original = $kit->get_settings( 'container_width' );
		$kit->set_settings( 'container_width', $width );

		return static function () use ( $kit, $original ) {
			$kit->set_settings( 'container_width', $original );
		};
	}

	/**
	 * Build a \Elementor\Plugin double for the page-data lookup.
	 *
	 * This is a throwaway, uninitialized REAL Plugin — the private constructor is
	 * bypassed and only its documents member is wired, which is all the suppression
	 * path (Plugin::$instance->documents->get()) reads. The test is skipped when real
	 * Elementor is absent (the plain stub suite).
	 *
	 * @since 3.7.0
	 *
	 * @return object A real, uninitialized \Elementor\Plugin instance.
	 */
	private function make_elementor_plugin_double() {
		$this->edd_require_real_elementor();

		return ( new \ReflectionClass( \Elementor\Plugin::class ) )->newInstanceWithoutConstructor();
	}

	/**
	 * Install a Plugin double whose document for the given post returns the
	 * supplied element data, and point the page lookup at it.
	 *
	 * @since 3.7.0
	 *
	 * @param array $elements Raw element-data arrays to expose as saved page data.
	 * @return int The post id the lookup will resolve.
	 */
	private function stub_elementor_page( array $elements ): int {
		$post_id = self::factory()->post->create();

		$plugin            = $this->make_elementor_plugin_double();
		$plugin->documents = new FakeElementorDocuments();

		$plugin->documents->documents[ $post_id ] = new FakeElementorDocument( $elements );

		\Elementor\Plugin::$instance = $plugin;

		// A page built with Elementor always carries its tree in _elementor_data, and the widget
		// lookup tests that raw string before it decodes anything.
		update_post_meta( $post_id, '_elementor_data', wp_slash( wp_json_encode( $elements ) ) );

		// Page::get_id() resolves the current page from the elementor-preview request param.
		$_REQUEST['elementor-preview'] = (string) $post_id;

		return $post_id;
	}

	/**
	 * Restore request state after a stubbed-page test.
	 *
	 * Under --extra elementor the captured real singleton is restored (undoing the
	 * throwaway) so later tests that instantiate a real element see a healthy
	 * Plugin; under the stub suite the pointer is nulled as before.
	 *
	 * @since 3.7.0
	 */
	private function reset_elementor_page() {
		unset( $_REQUEST['elementor-preview'] );

		if ( ! empty( $GLOBALS['edd_elementor_real_plugin'] ) ) {
			$this->edd_boot_real_elementor();

			return;
		}

		if ( class_exists( '\Elementor\Plugin', false ) ) {
			\Elementor\Plugin::$instance = null;
		}
	}
	/**
	 * form_open() carries no inline style now that the account line renders inside the box.
	 *
	 * @covers \EDD\Elementor\Checkout\FormLayer::form_open
	 */
	public function test_form_open_carries_no_inline_style() {
		$form = FormLayer::form_open( 'https://example.test/checkout' );

		$this->assertStringContainsString( 'id="edd_purchase_form"', $form );
		$this->assertStringNotContainsString( 'style=', $form, 'The form needs no width hint: the account line is inside the box.' );
	}

	/**
	 * The top and bottom checkout content renders from the box hooks, not around the box.
	 *
	 * @covers \EDD\Elementor\Subscribers\CheckoutFormLayer::get_subscribed_events
	 */
	public function test_box_hooks_render_the_checkout_top_and_bottom() {
		$events = \EDD\Elementor\Subscribers\CheckoutFormLayer::get_subscribed_events();

		$this->assertSame( 'render_box_top', $events['edd_elementor_checkout_box_top'] );
		$this->assertSame( 'render_box_bottom', $events['edd_elementor_checkout_box_bottom'] );
	}

}
