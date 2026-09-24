<?php
/**
 * Custom CSS control + emission tests for the EDD Checkout box element.
 *
 * Runs against real Elementor (--extra elementor): the RealElementorFixture trait
 * restores the fully-initialized \Elementor\Plugin singleton in setUp so the
 * element class (which extends the native Container) instantiates and registers
 * without fatal. Covers:
 *
 *   1. The `edd_custom_css` control is registered inside the existing Checkout
 *      Layout section (`edd_checkout_layout`), using the native `code` control
 *      type — NOT on the Advanced tab and NOT in a dedicated section (removed).
 *   2. print_custom_css() emits a <style class="edd-checkout-custom-css"> block
 *      containing the configured value.
 *   3. print_custom_css() emits nothing when the setting is empty.
 *   4. print_custom_css() strips both `<style>` start tags and `</style>` end-tag
 *      sequences (including the trailing `>`), looping until stable, so a value works with or without a
 *      surrounding <style> wrapper and can never break out of the emitted
 *      style element.
 *   5. The Custom CSS is emitted on every box-render path, including the
 *      empty-cart notice branch, so it is styled regardless of cart state.
 *   6. print_custom_css() HTML-entity-decodes the value before stripping, so
 *      combinators such as `&gt;` (rewritten by wp_kses_post on save for
 *      non-privileged users) are restored, while an encoded </style> breakout
 *      sequence is still neutralized by the strip loop that follows the decode.
 *      CSS `url()` query separators (a kses-encoded `&amp;` or a bare `&reg`)
 *      survive the decode intact.
 *
 * @package     EDD\Tests\Elementor
 * @copyright   Copyright (c) 2026, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.7.0
 */

namespace EDD\Tests\Elementor;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;
use EDD\Elementor\Checkout\FormLayer;
use EDD\Elementor\Subscribers\CheckoutBox as CheckoutBoxSubscriber;
use EDD\Tests\Elementor\Support\RealElementorFixture;

/**
 * Fake elements manager that records registered element types.
 */
class FakeElementsManagerForCustomCss {

	/**
	 * Registered element type instances.
	 *
	 * @var array
	 */
	public $registered = array();

	/**
	 * Record a registered element type.
	 *
	 * @param object $element The element type instance.
	 * @return void
	 */
	public function register_element_type( $element ) {
		$this->registered[] = $element;
	}
}

/**
 * @covers \EDD\Elementor\Elements\CheckoutBox
 *
 * @group elementor
 */
class CheckoutBoxCustomCss extends EDD_UnitTestCase {

	use RealElementorFixture;

	/**
	 * Restore the real, fully-initialized Elementor singleton before each test.
	 */
	public function setUp(): void {
		parent::setUp();

		$this->edd_boot_real_elementor();
	}

	/**
	 * Clear the form-layer state and restore the real singleton after each test.
	 */
	public function tear_down() {
		if ( ! empty( $GLOBALS['edd_elementor_real_plugin'] ) ) {
			\Elementor\Plugin::$instance = $GLOBALS['edd_elementor_real_plugin'];
		}

		FormLayer::reset_engaged();
		FormLayer::clear_empty_cart();

		parent::tear_down();
	}

	/**
	 * The edd_custom_css control is registered inside the existing "Checkout
	 * Layout" section (not the Advanced tab), so it never collides with
	 * Elementor Pro's own Custom CSS control.
	 */
	public function test_custom_css_control_is_registered() {
		$element = $this->get_registered_box();

		// Initialize the real controls stack so register_controls() runs.
		$element->get_controls();

		$control = $element->get_controls( 'edd_custom_css' );

		$this->assertIsArray(
			$control,
			'The Custom CSS control must be registered.'
		);
		$this->assertSame(
			'code',
			$control['type'],
			'The Custom CSS control must use the native code control type.'
		);
		$this->assertSame(
			'edd_checkout_layout',
			$control['section'],
			'The Custom CSS control must live in the existing Checkout Layout section.'
		);

		$this->assertNull(
			$element->get_controls( 'edd_custom_css_section' ),
			'The retired dedicated Custom CSS section must no longer be registered.'
		);
	}

	/**
	 * print_custom_css() emits a style block containing the configured value.
	 */
	public function test_print_custom_css_emits_style_block_when_value_present() {
		$element = $this->make_box_with_settings( array( 'edd_custom_css' => '.edd-checkout{color:red}' ) );

		$output = $this->capture_print_custom_css( $element );

		$this->assertStringContainsString( '<style class="edd-checkout-custom-css">', $output );
		$this->assertStringContainsString( '.edd-checkout{color:red}', $output );
	}

	/**
	 * print_custom_css() emits nothing when the setting is empty.
	 */
	public function test_print_custom_css_emits_nothing_when_value_empty() {
		$element = $this->make_box_with_settings( array( 'edd_custom_css' => '' ) );

		$output = $this->capture_print_custom_css( $element );

		$this->assertSame( '', $output );
		$this->assertStringNotContainsString( '<style class="edd-checkout-custom-css">', $output );
	}

	/**
	 * print_custom_css() strips a closing-tag breakout sequence from the value.
	 */
	public function test_print_custom_css_strips_style_breakout() {
		$element = $this->make_box_with_settings( array( 'edd_custom_css' => 'x{}</style><script>alert(1)</script>' ) );

		$output = $this->capture_print_custom_css( $element );

		$this->assertStringNotContainsString( '</style><script>', $output );

		// Only our own trailing closing tag may remain.
		$this->assertSame( 1, preg_match_all( '/<\/style>/', $output ), $output );
	}

	/**
	 * print_custom_css() strips a breakout sequence built by splicing two
	 * fragments across a single pass (the reconstruction bypass).
	 *
	 * A single preg_replace pass does not re-scan its own replacement, so a
	 * value like "</sty</stylele>" would previously splice into a fresh
	 * </style after one pass. The loop-until-stable strip catches this: the
	 * ONLY </style> left in the output is our own trailing closing tag, so no
	 * injected terminator can end the style element early. Note the leftover
	 * "<script>" text is not itself proof of exploitability — a <style>
	 * element's content is raw text to the browser's parser; it is only
	 * significant once a genuine "</style" boundary lets it escape into HTML
	 * parsing, and the loop guarantees no such boundary survives.
	 */
	public function test_print_custom_css_strips_reconstructed_style_breakout() {
		$element = $this->make_box_with_settings( array( 'edd_custom_css' => '</sty</stylele><script>alert(1)</script>' ) );

		$output = $this->capture_print_custom_css( $element );

		// The injected terminator must not survive as a real "</style><script>" breakout.
		$this->assertStringNotContainsString( '</style><script>', $output );

		// Only our own trailing closing tag may remain.
		$this->assertSame( 1, substr_count( $output, '</style>' ), $output );
	}

	/**
	 * print_custom_css() strips a surrounding <style> wrapper so authored CSS
	 * works whether it was pasted with or without one.
	 */
	public function test_print_custom_css_strips_surrounding_style_wrapper() {
		$element = $this->make_box_with_settings( array( 'edd_custom_css' => '<style>a{color:red}</style>' ) );

		$output = $this->capture_print_custom_css( $element );

		$this->assertStringContainsString( 'a{color:red}', $output );
		$this->assertStringNotContainsString( '<style>a{', $output, 'The inner <style> start tag must be stripped.' );

		// Only our own opening/closing tags may remain — the wrapper's own
		// <style>/</style> tags must be gone.
		$this->assertSame( 1, substr_count( $output, '<style' ), $output );
		$this->assertSame( 1, substr_count( $output, '</style>' ), $output );

		// No stray `>` residue from an incompletely-stripped end tag.
		$this->assertStringNotContainsString( 'red}>', $output, 'No stray > residue may follow the stripped end tag.' );
	}

	/**
	 * print_custom_css() strips a wrapper AND a reconstruction-style breakout
	 * combined in the same value (regression for both defects together).
	 */
	public function test_print_custom_css_strips_wrapper_and_reconstructed_breakout_combined() {
		$element = $this->make_box_with_settings( array( 'edd_custom_css' => '<style></sty</stylele>a{}</style>' ) );

		$output = $this->capture_print_custom_css( $element );

		$this->assertStringContainsString( 'a{}', $output );
		$this->assertSame( 1, substr_count( $output, '<style' ), $output );
		$this->assertSame( 1, substr_count( $output, '</style>' ), $output );

		// No stray `>` residue from an incompletely-stripped end tag.
		$this->assertStringNotContainsString( 'a{}>', $output, 'No stray > residue may follow the stripped end tag.' );
	}

	/**
	 * print_custom_css() decodes HTML entities so a combinator rewritten by
	 * Elementor's wp_kses_post save-time filter (e.g. `>` becoming `&gt;` for
	 * any user without unfiltered_html) is restored in the emitted CSS.
	 */
	public function test_print_custom_css_decodes_entities_restoring_combinator() {
		$authored = '.foo > .bar{color:red}';
		$stored   = wp_kses_post( $authored );

		// Guard the premise: kses is what encodes the combinator at save time.
		$this->assertStringContainsString( '&gt;', $stored, 'wp_kses_post() must be what encodes the combinator.' );

		$element = $this->make_box_with_settings( array( 'edd_custom_css' => $stored ) );

		$output = $this->capture_print_custom_css( $element );

		$this->assertStringContainsString( $authored, $output );
		$this->assertStringNotContainsString( '&gt;', $output );
	}

	/**
	 * print_custom_css() still neutralizes a breakout sequence that was itself
	 * HTML-entity-encoded, confirming the decode-then-strip order is safe: the
	 * strip loop runs on the decoded value, so a would-be </style> can never
	 * survive to break out of the emitted style element.
	 */
	public function test_print_custom_css_strips_entity_encoded_style_breakout() {
		$element = $this->make_box_with_settings( array( 'edd_custom_css' => '&lt;/style&gt;&lt;script&gt;alert(1)&lt;/script&gt;' ) );

		$output = $this->capture_print_custom_css( $element );

		$this->assertStringNotContainsString( '</style><script>', $output );

		// Only our own trailing closing tag may remain.
		$this->assertSame( 1, substr_count( $output, '</style>' ), $output );

		$this->assertStringNotContainsString( '&lt;', $output, 'The value must be entity-decoded before stripping, so no encoded &lt; remains.' );
	}

	/**
	 * A CSS url() query string survives the entity decode: a kses-encoded
	 * `&amp;` is restored to a single `&`, and a bare semicolon-less `&reg`
	 * is left literal (html_entity_decode does not decode entities without a
	 * trailing semicolon), so query separators are never corrupted.
	 */
	public function test_print_custom_css_preserves_url_query_ampersand() {
		$element = $this->make_box_with_settings(
			array( 'edd_custom_css' => '.a{background:url(https://e.test/i.php?x=1&amp;reg=2)}.b{background:url(https://e.test/j.php?y=1&reg=2)}' )
		);

		$output = $this->capture_print_custom_css( $element );

		// kses-encoded & restored to a single &; bare &reg left literal; no entity corruption.
		$this->assertStringContainsString( '?x=1&reg=2', $output );

		// Forward-looking guard: a semicolon-less "&reg" is unchanged today because
		// html_entity_decode() only decodes entities that have a trailing semicolon.
		// This assertion guards against a future decoder change that expands
		// semicolon-less legacy entities and would otherwise silently corrupt this
		// query separator.
		$this->assertStringContainsString( '?y=1&reg=2', $output );
		$this->assertStringNotContainsString( "\xC2\xAE", $output, 'A semicolon-less &reg must not decode to the registered-trademark glyph.' );
	}

	/**
	 * The empty-cart notice owner still emits the Custom CSS style block, even
	 * though it is not the engaged/form-rendering box.
	 */
	public function test_print_content_empty_cart_owner_still_emits_custom_css() {
		$element = $this->make_box_with_id_and_settings(
			'customCssEmptyCart',
			array( 'edd_custom_css' => '.edd-checkout{color:red}' )
		);

		FormLayer::reset_engaged();
		FormLayer::mark_empty_cart( 'customCssEmptyCart' );

		$output = $this->capture_print_content( $element );

		$this->assertStringContainsString( '<style class="edd-checkout-custom-css">', $output );
		$this->assertStringContainsString( '.edd-checkout{color:red}', $output );
	}

	/**
	 * Register the checkout box element against a fake elements manager and return it.
	 *
	 * @return \EDD\Elementor\Elements\CheckoutBox
	 */
	private function get_registered_box() {
		$manager    = new FakeElementsManagerForCustomCss();
		$subscriber = new CheckoutBoxSubscriber();
		$subscriber->register_element( $manager );

		return $manager->registered[0];
	}

	/**
	 * Construct a checkout box element with initialized data so the given
	 * settings (including edd_custom_css) are readable via get_settings_for_display().
	 *
	 * The subscriber's no-arg `new CheckoutBox()` (used for registration tests)
	 * leaves the element's internal data uninitialized, which is fine for reading
	 * controls but breaks settings access. Constructing with an explicit `$data`
	 * array (mirroring how Elementor itself instantiates elements from stored
	 * document data) initializes settings so print_custom_css() can read them.
	 *
	 * @param array $settings Settings to seed on the element.
	 * @return \EDD\Elementor\Elements\CheckoutBox
	 */
	private function make_box_with_settings( array $settings ) {
		return new \EDD\Elementor\Elements\CheckoutBox(
			array(
				'id'       => 'edd-test-box',
				'settings' => $settings,
			)
		);
	}

	/**
	 * Construct a full checkout-box element instance carrying a given id and settings.
	 *
	 * The print_content branches key on get_id(), and parent::print_content()
	 * iterates get_children() (which reads the element's `elements` data), so a
	 * full data instance (matching CheckoutBoxElementCoverage::make_box_with_id())
	 * is required to drive print_content() rather than print_custom_css() directly.
	 * The element class must already be loaded (registration requires the native
	 * Container file), which get_registered_box() guarantees as a side effect.
	 *
	 * @param string $id       The id get_id() should report.
	 * @param array  $settings Settings to seed on the element.
	 * @return \EDD\Elementor\Elements\CheckoutBox
	 */
	private function make_box_with_id_and_settings( string $id, array $settings ) {
		$this->get_registered_box();

		return new \EDD\Elementor\Elements\CheckoutBox(
			array(
				'id'       => $id,
				'elType'   => 'edd-checkout-box',
				'settings' => $settings,
				'elements' => array(),
			)
		);
	}

	/**
	 * Invoke the protected print_content() method and capture its output.
	 *
	 * @param object $element The checkout box element instance.
	 * @return string The captured output.
	 */
	private function capture_print_content( $element ): string {
		$reflection = new \ReflectionClass( $element );
		$method     = $reflection->getMethod( 'print_content' );
		if (PHP_VERSION_ID < 80100) {
            $method->setAccessible( true );
        }

		ob_start();
		$method->invoke( $element );
		return ob_get_clean();
	}

	/**
	 * Invoke the protected print_custom_css() method and capture its output.
	 *
	 * @param object $element The checkout box element instance.
	 * @return string The captured output.
	 */
	private function capture_print_custom_css( $element ): string {
		$reflection = new \ReflectionClass( $element );
		$method     = $reflection->getMethod( 'print_custom_css' );
		if (PHP_VERSION_ID < 80100) {
            $method->setAccessible( true );
        }

		ob_start();
		$method->invoke( $element );
		return ob_get_clean();
	}
}
