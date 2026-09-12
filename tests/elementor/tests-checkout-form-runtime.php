<?php
/**
 * Runtime tests for the CheckoutFormLayer subscriber's echoed-wrapper render engine.
 *
 * Drives EDD\Elementor\Subscribers\CheckoutFormLayer through its before_render
 * and after_render hook points and asserts the emitted purchase-form markup, the
 * one-form-per-page engagement guard, and the section-hook fallbacks. Runs
 * against the real \Elementor\Plugin singleton under --extra elementor.
 *
 * @package     EDD\Tests\Elementor
 * @copyright   Copyright (c) 2025, Sandhills Development, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.7.0
 */

namespace EDD\Tests\Elementor;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;
use EDD\Tests\Helpers\EDD_Helper_Download;
use EDD\Elementor\Checkout\FormLayer;
use EDD\Elementor\Subscribers\Checkout;
use EDD\Elementor\Subscribers\CheckoutFormLayer;
use EDD\Elementor\Utils\Page;
use EDD\Tests\Elementor\Support\RealElementorFixture;

require_once __DIR__ . '/support/fakes-checkout-form-layer.php';

/**
 * A minimal fake Elementor element exposing only the members the
 * CheckoutFormLayer subscriber touches at render time.
 *
 * The subscriber calls get_raw_data() (for the detection helper) at render time;
 * it no longer coerces the element (the box is a plain container and the subscriber
 * emits the <form> itself). The set_settings()/set_render_attribute() recorders are
 * retained so tests can assert those overrides are NOT set, all without loading the
 * real Elementor base classes.
 */
class FakeFormLayerElement {

	/**
	 * The raw element-data node this element reports.
	 *
	 * @var array
	 */
	private $raw_data;

	/**
	 * Settings recorded via set_settings(), keyed by setting name.
	 *
	 * @var array
	 */
	public $settings = array();

	/**
	 * Render attributes recorded via set_render_attribute(), keyed by element.
	 *
	 * @var array
	 */
	public $render_attributes = array();

	/**
	 * Build the fake around a raw element-data node.
	 *
	 * @param array $raw_data Raw element-data (elType/widgetType/elements).
	 */
	public function __construct( array $raw_data ) {
		$this->raw_data = $raw_data;
	}

	/**
	 * Return the raw element-data node the detection helper expects.
	 *
	 * @return array
	 */
	public function get_raw_data() {
		return $this->raw_data;
	}

	/**
	 * Record a setting the subscriber forced (e.g. html_tag => form).
	 *
	 * @param string $key   The setting name.
	 * @param mixed  $value The setting value.
	 * @return void
	 */
	public function set_settings( $key, $value ) {
		$this->settings[ $key ] = $value;
	}

	/**
	 * Record a render attribute the subscriber seeded (id/method/action).
	 *
	 * @param string $element The attribute element key (e.g. _wrapper).
	 * @param mixed  $value   The attribute value (array of attr => value).
	 * @return void
	 */
	public function set_render_attribute( $element, $value ) {
		$this->render_attributes[ $element ] = $value;
	}
}

/**
 * Runtime tests for the CheckoutFormLayer subscriber's echoed-wrapper render engine.
 *
 * The subscriber does NOT use a capture-and-splice idiom (an output-capture
 * call opening a buffer, a matching buffer-flush call, and a last-occurrence
 * string-search locating the </form> close). None of those three PHP calls
 * appear anywhere in this class (structurally confirmed by
 * test_render_path_contains_no_buffer_or_splice_calls()).
 *
 * The contract this class pins: the subscriber ECHOES FormLayer::wrap_open() then
 * FormLayer::form_open() directly at before_render(), and FormLayer::form_close()
 * then FormLayer::wrap_close() directly at after_render(), for an engaged element —
 * no capture, no string splice. That no-buffer/no-splice contract is on the
 * SUBSCRIBER, and is proven by reading its own source
 * (test_render_path_contains_no_buffer_or_splice_calls()).
 *
 * For output discipline, set_up() opens a local buffer around each test and
 * tear_down() discards it, so the subscriber's unbuffered echoes are caught
 * here instead of leaking to stdout. The assertions read that buffer's live
 * contents through $this->getActualOutput() / captured_since() — capturing
 * the emitted markup for inspection, never splicing or altering it.
 *
 * Engagement is tracked per-element via an id-keyed map (not a scalar depth
 * counter and not a single global $rendered bool) plus a page-level guard: the
 * FIRST checkout box to engage wins, and every subsequent box or nested
 * checkout-flagged container is refused engagement (exactly one purchase form
 * is rendered per Elementor document render pass). The fallback gate consults
 * \EDD\Elementor\Utils\Page::has_widget($section, $box_elements) scoped to the
 * ENGAGED box's own subtree, never the page-global default.
 *
 * The section hooks (edd_checkout_form_top/_bottom) fire from the subscriber at
 * before_render()/after_render() — render_form_top() just after the form opens and
 * render_form_bottom() just before it closes — so their output lands INSIDE the
 * form the subscriber echoed, wrapping the box's own children that render between
 * the two actions. The runtime tests reproduce that order via render_engaged_box()
 * so fallback and section output is asserted to land as a descendant of the form.
 *
 * @covers \EDD\Elementor\Subscribers\CheckoutFormLayer::before_render
 * @covers \EDD\Elementor\Subscribers\CheckoutFormLayer::after_render
 *
 * @group elementor
 *
 * @since 3.7.0
 */
class Tests_Checkout_Form_Layer_Runtime extends EDD_UnitTestCase {

	use RealElementorFixture;

	/**
	 * A simple download so PaymentDetails/PersonalInfo fallback render paths
	 * that touch the cart have contents to render against.
	 *
	 * @var \WP_Post
	 */
	private static $download;

	/**
	 * The REQUEST_URI value saved before a front-end Elementor stub, restored after.
	 *
	 * @var string|null
	 */
	private $prev_request_uri = null;

	/**
	 * ob_get_level() of the per-test capture buffer opened in set_up(), so
	 * tear_down() can discard it (and any buffer a rendered block left open)
	 * without disturbing PHPUnit's own outer buffer.
	 *
	 * @var int
	 */
	private $output_buffer_level = 0;

	/**
	 * Create shared fixtures once for the class.
	 *
	 * @since 3.7.0
	 */
	public static function setUpBeforeClass(): void {
		parent::setUpBeforeClass();

		self::$download = EDD_Helper_Download::create_simple_download();
	}

	/**
	 * Tear down shared fixtures once for the class.
	 *
	 * @since 3.7.0
	 */
	public static function tearDownAfterClass(): void {
		EDD_Helper_Download::delete_download( self::$download->ID );

		parent::tearDownAfterClass();
	}

	/**
	 * Populate the cart before each test so the block renderers this class
	 * bootstraps (PersonalInfo/PaymentDetails) have a non-empty checkout to
	 * render against.
	 *
	 * @since 3.7.0
	 */
	public function setUp(): void {
		parent::setUp();

		edd_empty_cart();
		edd_add_to_cart( self::$download->ID );

		// The page-form-open guard is a static on FormLayer with a per-pass lifecycle
		// (set when the form opens, NOT cleared at box-close), so without an explicit
		// reset it would leak "already open" across tests (no process isolation) and
		// refuse every later engagement. Reset it — and the box-engagement id — here.
		FormLayer::reset_page_form_open();
		FormLayer::reset_engaged();

		// Every runtime test triggers unbuffered echoes (wrapper open/close, the
		// section hooks, and the block fallbacks). Open a local buffer to catch
		// them so none reach PHPUnit's own per-test buffer (which would otherwise
		// be printed to stdout at teardown). getActualOutput()/captured_since()
		// read THIS buffer's live contents for the markup assertions.
		ob_start();
		$this->output_buffer_level = ob_get_level();
	}

	/**
	 * Empty the cart and reset the form-layer engagement state between tests.
	 *
	 * @since 3.7.0
	 */
	public function tear_down() {
		// Discard the per-test capture buffer opened in set_up() — down to its
		// own level, in case a rendered block left one open — so PHPUnit's outer
		// buffer stays empty and prints no rendered markup to stdout.
		while ( ob_get_level() >= $this->output_buffer_level && $this->output_buffer_level > 0 ) {
			ob_get_clean();
		}

		edd_empty_cart();
		FormLayer::reset_page_form_open();
		FormLayer::reset_engaged();
		parent::tear_down();
	}

	// -----------------------------------------------------------------------
	// wrap_open/wrap_close ECHOED directly at hook points, no splice.
	// -----------------------------------------------------------------------

	/**
	 * An engaged render echoes #edd_checkout_form_wrap + wp-block-edd-checkout AND
	 * the <form id="edd_purchase_form"> directly at before_render(), and closes both
	 * at after_render(), with NO capture/splice.
	 *
	 * before_render() must echo wrap_open() then form_open() directly at the hook
	 * point (not open an internal capture buffer), so the mark-diffed output captured
	 * here contains the wrapper AND the purchase-form open tag right after
	 * before_render() runs; after_render() echoes the form close then the wrapper
	 * close.
	 *
	 * @since 3.7.0
	 */
	public function test_engaged_render_echoes_wrapper_at_hook_points_no_splice() {
		$element    = $this->engageable_element();
		$subscriber = new CheckoutFormLayer();

		$before_mark = strlen( $this->getActualOutput() );
		$subscriber->before_render( $element );
		$opened = $this->captured_since( $before_mark );

		// wrap_open() + form_open() must be ECHOED directly inside before_render(),
		// before any child markup exists — not deferred into a buffer.
		$this->assertStringContainsString(
			'id="' . FormLayer::WRAP_ID . '"',
			$opened,
			'before_render() must echo FormLayer::wrap_open() directly at the hook point, not buffer it.'
		);
		$this->assertStringContainsString( 'wp-block-edd-checkout', $opened );
		$this->assertStringContainsString(
			'id="' . FormLayer::FORM_ID . '"',
			$opened,
			'before_render() must echo FormLayer::form_open() (the purchase form) directly at the hook point — the box is no longer coerced into the form.'
		);

		// Elementor renders the box's own children between the two hooks (the box is
		// a plain container now, not the form).
		echo '<div class="fields">inner</div>';

		$after_mark = strlen( $this->getActualOutput() );
		$subscriber->after_render( $element );
		$closed = $this->captured_since( $after_mark );

		$this->assertStringContainsString(
			'</form>',
			$closed,
			'after_render() must echo FormLayer::form_close() at the hook point.'
		);
		$this->assertStringContainsString(
			'</div>',
			$closed,
			'after_render() must echo FormLayer::wrap_close() at the hook point.'
		);

		// The box is NOT coerced into a <form>: no html_tag/_wrapper overrides are set.
		$this->assertArrayNotHasKey(
			'html_tag',
			$element->settings,
			'The box must not be forced to <form> — the subscriber emits the form itself.'
		);

		$full_output = $opened . '<div class="fields">inner</div>' . $closed;

		// The form-layer emits no checkout nonce of its own.
		$this->assertStringNotContainsString( 'edd-process-checkout-nonce', $full_output );
		$this->assertStringNotContainsString( '_wpnonce', $full_output );
	}

	/**
	 * The purchase <form> emits NO `--flex-direction` style (the bridge is retired).
	 *
	 * The retired `edd_layout` -> `--flex-direction` bridge is gone: layout is chosen
	 * by the editor-JS pattern picker, which seeds per-pattern column widths onto the
	 * box's inner containers. The form the subscriber opens must therefore carry no
	 * inline `--flex-direction` custom property, even when the box stores a legacy
	 * `edd_layout` value (now ignored). The box also stays a plain (un-coerced)
	 * container.
	 *
	 * @since 3.7.0
	 */
	public function test_form_open_emits_no_flex_direction_style() {
		$element    = $this->engageable_two_column_element( 'boxRow01' );
		$subscriber = new CheckoutFormLayer();

		$output = $this->render_engaged_box( $subscriber, $element );

		$this->assertStringContainsString(
			'id="' . FormLayer::FORM_ID . '"',
			$output,
			'The purchase form must be present.'
		);
		$this->assertStringNotContainsString(
			'--flex-direction',
			$output,
			'The retired --flex-direction bridge must emit no layout var on the purchase <form>.'
		);

		// The box must NOT be coerced into a <form>.
		$this->assertArrayNotHasKey(
			'html_tag',
			$element->settings,
			'The box must stay a plain container — the subscriber emits the form itself.'
		);
	}

	// -----------------------------------------------------------------------
	// Structural guard: the three buffer/splice PHP
	// built-ins (output-buffer-open, output-buffer-flush-and-close, and the
	// case-insensitive last-occurrence string search) must not appear in the
	// engaged render path.
	// -----------------------------------------------------------------------

	/**
	 * The engaged render path contains NONE of the three buffer/splice
	 * built-in calls the buffer-and-splice idiom relied on.
	 *
	 * Reads the subscriber's own source for the splice idiom. The subscriber
	 * must NOT call the buffer-open built-in in before_render() nor the
	 * last-occurrence-search plus buffer-flush built-ins in after_render(); this
	 * assertion names any such still-present splice call. The forbidden names
	 * are assembled from fragments so this test file's own bytes never contain
	 * the literal splice-call substrings (kept out of the class entirely).
	 *
	 * @since 3.7.0
	 */
	public function test_render_path_contains_no_buffer_or_splice_calls() {
		$reflection = new \ReflectionClass( CheckoutFormLayer::class );
		$source     = file_get_contents( $reflection->getFileName() );

		$forbidden_calls = array(
			'ob_' . 'start',
			'ob_get_' . 'clean',
			'strr' . 'ipos',
		);

		$found = array();
		foreach ( $forbidden_calls as $forbidden_call ) {
			if ( false !== strpos( $source, $forbidden_call ) ) {
				$found[] = $forbidden_call;
			}
		}

		$this->assertSame(
			array(),
			$found,
			'CheckoutFormLayer must not call the output-buffer-open, buffer-flush, or last-occurrence-search built-ins anywhere (it echoes directly at hook points, no buffer/splice). Still present: ' . implode( ', ', $found )
		);
	}

	// -----------------------------------------------------------------------
	// Checkout detection resolves from the wp:edd/checkout marker, independent
	// of the box render lifecycle (no scoped filter is added or removed).
	// -----------------------------------------------------------------------

	/**
	 * Checkout detection resolves from the wp:edd/checkout marker, not from any
	 * transient filter toggled by the box render lifecycle.
	 *
	 * A page carrying the marker the seeded box emits is detected as checkout
	 * before before_render() engages, while the box is engaged, and after
	 * after_render() tears the render down — the answer never depends on a scoped
	 * filter being added or removed, because there is none.
	 *
	 * @since 3.7.0
	 */
	public function test_detection_is_marker_driven_across_render_lifecycle() {
		$this->go_to_marker_page();

		$element    = $this->engageable_element();
		$subscriber = new CheckoutFormLayer();

		$this->reset_checkout_detection_cache();
		$this->assertTrue(
			edd_is_checkout(),
			'A marker page must be detected as checkout before any render engages.'
		);

		$subscriber->before_render( $element );

		$this->reset_checkout_detection_cache();
		$this->assertTrue(
			edd_is_checkout(),
			'Marker-driven detection must stay true while a box is engaged.'
		);

		$subscriber->after_render( $element );

		$this->reset_checkout_detection_cache();
		$this->assertTrue(
			edd_is_checkout(),
			'Marker-driven detection must stay true after teardown — it is not filter-scoped.'
		);
	}

	/**
	 * A child-render exception between before_render() and after_render() does
	 * not corrupt marker-driven checkout detection.
	 *
	 * There is no scoped filter to leak; detection continues to resolve from the
	 * wp:edd/checkout marker after the exception is handled and after_render()
	 * runs its teardown.
	 *
	 * @since 3.7.0
	 */
	public function test_child_render_exception_does_not_corrupt_marker_detection() {
		$this->go_to_marker_page();

		$element    = $this->engageable_element();
		$subscriber = new CheckoutFormLayer();

		$subscriber->before_render( $element );

		try {
			throw new \RuntimeException( 'Simulated child-render failure.' );
		} catch ( \RuntimeException $e ) {
			// Elementor's own render loop would let this propagate; the
			// subscriber's own after_render()/shutdown failsafe is what must
			// still tear the render down cleanly, exercised on the next line.
			$subscriber->after_render( $element );
		}

		$this->reset_checkout_detection_cache();
		$this->assertTrue(
			edd_is_checkout(),
			'Marker-driven detection must survive a child-render exception and teardown.'
		);
	}

	// -----------------------------------------------------------------------
	// Exactly one purchase form is rendered per Elementor document render pass.
	// The first box to engage wins; a second box and a nested checkout-flagged
	// container are both refused engagement (no wrapper, no forced <form>, no
	// section hooks).
	// -----------------------------------------------------------------------

	/**
	 * A second checkout box on the page does NOT open a second form: only the first
	 * box's boundary opens the wrapper + <form>, so the document holds exactly one
	 * #edd_checkout_form_wrap and one <form id="edd_purchase_form">.
	 *
	 * @since 3.7.0
	 */
	public function test_second_box_does_not_engage_one_form_per_page() {
		$subscriber = new CheckoutFormLayer();

		$box_a = $this->engageable_element( 'boxA001' );
		$a_out = $this->render_engaged_box( $subscriber, $box_a, '<div class="fields">A</div>' );

		// A second box renders after the first has fully engaged and closed.
		$box_b = $this->engageable_element( 'boxB002' );
		$mark  = strlen( $this->getActualOutput() );
		$subscriber->before_render( $box_b );
		$b_open = $this->captured_since( $mark );
		$mark   = strlen( $this->getActualOutput() );
		$subscriber->after_render( $box_b );
		$b_close = $this->captured_since( $mark );

		// Exactly one wrapper and one purchase form on the page, contributed only by Box A.
		$this->assertSame(
			1,
			substr_count( $a_out, 'id="' . FormLayer::WRAP_ID . '"' ),
			'The first box must open exactly one #edd_checkout_form_wrap.'
		);
		$this->assertSame(
			1,
			substr_count( $a_out, 'id="' . FormLayer::FORM_ID . '"' ),
			'The first box must open exactly one <form id="edd_purchase_form">.'
		);
		$this->assertSame(
			'',
			$b_open . $b_close,
			'The second box must emit nothing (no wrapper, no form): exactly one purchase form is rendered per Elementor document render pass.'
		);
		$this->assertArrayNotHasKey(
			'html_tag',
			$box_b->settings,
			'The second box must NOT be forced to <form> — it does not open the form.'
		);
		$this->assertFalse(
			FormLayer::is_engaged_element( 'boxB002' ),
			'The second box must not be recorded as the engaged box.'
		);
	}

	/**
	 * A native container NESTED inside an already-engaged box does NOT re-engage:
	 * the outer box opens the single wrapper/form, and the nested container (which
	 * merely holds a section widget) never engages the form layer, so no second
	 * wrapper/form is opened.
	 *
	 * @since 3.7.0
	 */
	public function test_nested_checkout_container_does_not_reengage() {
		$subscriber = new CheckoutFormLayer();

		// The outer box engages first.
		$outer = $this->engageable_element( 'outerBox1' );
		$mark  = strlen( $this->getActualOutput() );
		$subscriber->before_render( $outer );
		$outer_open = $this->captured_since( $mark );

		// A native container holding a section widget, nested inside the box, tries
		// to engage on its own before_render (fired during the box's print_content).
		$nested = new FakeFormLayerElement(
			array(
				'id'       => 'nestedCon1',
				'elType'   => 'container',
				'settings' => array(),
				'elements' => array(
					array(
						'elType'     => 'widget',
						'widgetType' => 'edd-checkout-payment-info',
						'settings'   => array(),
						'elements'   => array(),
					),
				),
			)
		);
		$mark = strlen( $this->getActualOutput() );
		$subscriber->before_render( $nested );
		$nested_open = $this->captured_since( $mark );
		$subscriber->after_render( $nested );

		// The outer box finishes rendering (its children rendered inside the form the
		// subscriber opened at the outer box's before_render).
		$subscriber->after_render( $outer );

		$this->assertStringContainsString(
			'id="' . FormLayer::WRAP_ID . '"',
			$outer_open,
			'The outer box must engage and open the single wrapper.'
		);
		$this->assertSame(
			'',
			$nested_open,
			'A nested checkout-flagged container must not open a second wrapper (exactly one form per Elementor document render pass).'
		);
		$this->assertFalse(
			FormLayer::is_engaged_element( 'nestedCon1' ),
			'The nested container must not be recorded as the engaged box.'
		);
		$this->assertArrayNotHasKey(
			'html_tag',
			$nested->settings,
			'The nested container must NOT be forced to <form> — it does not engage.'
		);
	}

	// -----------------------------------------------------------------------
	// A section nested inside an inner container is still detected.
	// -----------------------------------------------------------------------

	/**
	 * A payment-info widget nested inside an inner container (not a direct
	 * child of the box) still engages the box and opens its wrapper.
	 *
	 * @since 3.7.0
	 */
	public function test_nested_section_widget_still_engages_and_wraps() {
		$element    = $this->nested_engageable_element();
		$subscriber = new CheckoutFormLayer();

		$mark = strlen( $this->getActualOutput() );
		$subscriber->before_render( $element );
		$opened = $this->captured_since( $mark );

		// The box renders its children (with the nested section) between the hooks.
		echo 'nested';

		$mark = strlen( $this->getActualOutput() );
		$subscriber->after_render( $element );
		$closed = $this->captured_since( $mark );

		$this->assertStringContainsString( FormLayer::WRAP_ID, $opened . $closed );
	}

	// -----------------------------------------------------------------------
	// Present section -> OUR fallback subscriber does NOT fire.
	// -----------------------------------------------------------------------

	/**
	 * A present personal-info widget means the box-scoped
	 * Page::has_widget($section, $box_elements) gate passes (widget found) so
	 * the fallback must be suppressed — no double-render.
	 *
	 * Explicitly passes $box_elements so the gate does not depend on
	 * get_page_data() / the global \Elementor\Plugin::$instance — the missing-
	 * section fallback rendering gate is isolated from page-global state.
	 *
	 * This assertion pins the GATE contract itself (Page::has_widget accepts an
	 * explicit $elements arg); the fallback wiring that consults this gate is
	 * covered by test_missing_personal_info_fallback_fires_personal_info_render
	 * and friends below.
	 *
	 * @since 3.7.0
	 */
	public function test_present_personal_info_suppresses_fallback_gate() {
		$element      = $this->engageable_element();
		$box_elements = $element->get_raw_data()['elements'];

		$this->assertTrue(
			Page::has_widget( 'edd-checkout-personal-info', $box_elements ),
			'The box-scoped gate must find personal-info present in THIS box\'s own subtree.'
		);
	}

	/**
	 * A missing personal-info widget means the box-scoped gate reports absent,
	 * so the fallback subscriber must fire.
	 *
	 * @since 3.7.0
	 */
	public function test_missing_personal_info_fails_fallback_gate() {
		$element      = $this->element_missing_personal_info();
		$box_elements = $element->get_raw_data()['elements'];

		$this->assertFalse(
			Page::has_widget( 'edd-checkout-personal-info', $box_elements ),
			'The box-scoped gate must report personal-info absent from THIS box\'s own subtree.'
		);
	}

	// -----------------------------------------------------------------------
	// Co-hooked UserDetails stays silent; Accessibility/GeoIP DO
	// fire (positive expectation); personal-info not rendered twice.
	//
	// The Elementor engaged path fires edd_checkout_form_top with empty attrs,
	// so the co-hooked UserDetails::render() early-returns while Accessibility's
	// required-fields notice and GeoIP's form output DO run — the same positive
	// output they emit on legacy checkout. These tests assert that positive
	// Accessibility/GeoIP output is present on the Elementor path.
	// -----------------------------------------------------------------------

	/**
	 * On the Elementor engaged-render path, edd_checkout_form_top must fire
	 * WITH EMPTY attributes so the co-hooked UserDetails::render() early-
	 * returns (empty($block_attributes) bails), while Accessibility's
	 * required-fields notice fires (the SAME positive output it emits on
	 * legacy checkout) and personal-info is rendered exactly once (via our
	 * fallback, not via UserDetails).
	 *
	 * @since 3.7.0
	 */
	public function test_form_top_fires_with_empty_attrs_userdetails_silent_accessibility_fires() {
		add_filter( 'edd_show_required_fields_notice', '__return_true' );
		\EDD\Checkout\Accessibility::reset_rendered_flag();

		$user_details   = new \EDD\Blocks\Checkout\Elements\UserDetails();
		$accessibility  = new \EDD\Checkout\Accessibility();
		add_action( 'edd_checkout_form_top', array( $user_details, 'render' ), 1 );
		add_action( 'edd_checkout_form_top', array( $accessibility, 'render_required_fields_notice' ), 0 );

		$element    = $this->engageable_element();
		$subscriber = new CheckoutFormLayer();

		$output = $this->render_engaged_box( $subscriber, $element, 'children' );

		remove_action( 'edd_checkout_form_top', array( $user_details, 'render' ), 1 );
		remove_action( 'edd_checkout_form_top', array( $accessibility, 'render_required_fields_notice' ), 0 );
		remove_filter( 'edd_show_required_fields_notice', '__return_true' );

		// UserDetails::render() bails on empty($block_attributes) — its
		// .edd-blocks__user-details wrapper must be ABSENT.
		$this->assertStringNotContainsString(
			'edd-blocks__user-details',
			$output,
			'UserDetails::render() must stay silent on the Elementor path (empty-attrs early-return); the Elementor engaged render must fire edd_checkout_form_top with EMPTY attributes.'
		);

		// Accessibility's required-fields notice is the POSITIVE expectation:
		// it fires on edd_checkout_form_top on Elementor too, same as legacy
		// checkout — this is documented as INTENTIONAL.
		$this->assertStringContainsString(
			'edd-required-fields-notice',
			$output,
			'Accessibility::render_required_fields_notice must fire on the Elementor engaged path (edd_checkout_form_top), the same positive output it emits on legacy checkout — NOT "nothing rendered".'
		);
	}

	/**
	 * GeoIP's add_ip_to_form() emits its hidden IP field on the Elementor
	 * engaged path, the same output it emits on legacy checkout — a second
	 * positive expectation alongside Accessibility's notice.
	 *
	 * @since 3.7.0
	 */
	public function test_geoip_ip_field_fires_on_elementor_path() {
		EDD()->session->set( 'edd_pro_geoip', array( 'ip' => '203.0.113.5' ) );

		$geoip = new \EDD\Pro\Checkout\GeoIP();
		add_action( 'edd_checkout_form_top', array( $geoip, 'add_ip_to_form' ) );

		$element    = $this->engageable_element();
		$subscriber = new CheckoutFormLayer();

		$output = $this->render_engaged_box( $subscriber, $element, 'children' );

		remove_action( 'edd_checkout_form_top', array( $geoip, 'add_ip_to_form' ) );
		EDD()->session->set( 'edd_pro_geoip', null );

		$this->assertStringContainsString(
			'name="edd_pro_ip"',
			$output,
			'GeoIP::add_ip_to_form() must fire on edd_checkout_form_top on the Elementor engaged path, the same as legacy checkout — the Elementor path must fire edd_checkout_form_top at all.'
		);
	}

	/**
	 * Personal-info is rendered exactly once on the engaged path: UserDetails
	 * stays silent (empty attrs) and our own fallback is the sole source of the
	 * personal-info markup — never double-rendered.
	 *
	 * @since 3.7.0
	 */
	public function test_personal_info_not_rendered_twice_on_engaged_path() {
		$user_details = new \EDD\Blocks\Checkout\Elements\UserDetails();
		add_action( 'edd_checkout_form_top', array( $user_details, 'render' ), 1 );

		$element    = $this->element_missing_personal_info();
		$subscriber = new CheckoutFormLayer();

		$output = $this->render_engaged_box( $subscriber, $element, 'children' );

		remove_action( 'edd_checkout_form_top', array( $user_details, 'render' ), 1 );

		$this->assertSame(
			1,
			substr_count( $output, 'edd-blocks__checkout-user' ),
			'Personal-info must render exactly once on the engaged path: UserDetails stays silent (empty attrs) and the fallback renderer supplies the markup once (not twice).'
		);
	}

	// -----------------------------------------------------------------------
	// Box-scoped fallback rendering.
	//
	// Direct-call contract: the fallback
	// calls \EDD\Blocks\Checkout\Elements\PersonalInfo::render($attrs) and
	// Elements\PaymentDetails::render($attrs, null) DIRECTLY (buffered by the
	// fallback subscriber, exactly as PaymentInfo.php:189-191 already does),
	// with $attrs = \EDD\Blocks\Checkout\Attributes::get() (the defaults shape
	// on an Elementor page — no checkout block, no AJAX current_page).
	//
	// These tests bootstrap the full block render chain (EDD_BLOCKS_DIR/views
	// are already loaded process-wide in this suite — see
	// tests-checkout-inner-render.php's direct calls to the same statics) so a
	// missing fallback subscriber fails at the ASSERTION (absent markup / an
	// undefined-method error naming the not-yet-wired subscriber), never at a
	// fatal missing-include.
	// -----------------------------------------------------------------------

	/**
	 * Missing personal-info widget -> Elements\PersonalInfo::render fallback
	 * fires inside the engaged render, using Attributes::get() defaults.
	 *
	 * The fallback subscriber must emit the personal-info markup
	 * (edd-blocks__checkout-user / edd-email) into the captured output; the
	 * assertion names that markup, and reports a plain absence rather than a
	 * fatal if it is missing.
	 *
	 * @since 3.7.0
	 */
	public function test_missing_personal_info_fallback_fires_personal_info_render() {
		// Sanity: the block renderer this fallback must call is directly
		// callable and produces the markup we assert for below (pins the
		// direct-call contract independent of the wiring).
		$attrs = \EDD\Blocks\Checkout\Attributes::get();
		$this->assertIsArray( $attrs );

		$element    = $this->element_missing_personal_info();
		$subscriber = new CheckoutFormLayer();

		$output = $this->render_engaged_box( $subscriber, $element );

		$this->assertStringContainsString(
			'edd-blocks__checkout-user',
			$output,
			'Missing personal-info must trigger the fallback subscriber calling \EDD\Blocks\Checkout\Elements\PersonalInfo::render($attrs) on edd_checkout_form_top.'
		);
	}

	/**
	 * Missing payment-info widget -> Elements\PaymentDetails::render fallback
	 * fires inside the engaged render.
	 *
	 * @since 3.7.0
	 */
	public function test_missing_payment_info_fallback_fires_payment_details_render() {
		$element    = $this->element_missing_payment_info();
		$subscriber = new CheckoutFormLayer();

		$output = $this->render_engaged_box( $subscriber, $element );

		$this->assertStringContainsString(
			'edd-blocks__payment-details',
			$output,
			'Missing payment-info must trigger the fallback subscriber calling \EDD\Blocks\Checkout\Elements\PaymentDetails::render($attrs, null) on edd_checkout_form_bottom.'
		);
	}

	/**
	 * A missing section still yields exactly ONE complete
	 * <form id="edd_purchase_form"> — never bare divs only. Pins this contract
	 * against BOTH single-section-missing fixtures.
	 *
	 * @since 3.7.0
	 */
	public function test_missing_section_still_yields_exactly_one_complete_purchase_form() {
		foreach ( array( $this->element_missing_personal_info( 'boxC1' ), $this->element_missing_payment_info( 'boxC2' ) ) as $element ) {
			$subscriber = new CheckoutFormLayer();

			// Each fixture is an independent page render, so start a fresh render pass:
			// the page-form-open guard is a per-pass static and would otherwise stay set
			// from the previous iteration and refuse this box's form.
			$subscriber->reset_for_new_render_pass();

			$output = $this->render_engaged_box( $subscriber, $element, esc_html( $element->get_raw_data()['id'] ) );

			$this->assertSame(
				1,
				substr_count( $output, 'id="edd_purchase_form"' ),
				'Exactly one complete <form id="edd_purchase_form"> must be present even when a required section is missing — the fallback must fill the gap inside the SAME form, never replace it with bare divs.'
			);
			$this->assertStringContainsString(
				'</form>',
				$output,
				'The purchase form must be closed, not just opened, even on the fallback path.'
			);
		}
	}

	// -----------------------------------------------------------------------
	// The fallback/section output must land inside the <form>.
	// -----------------------------------------------------------------------

	/**
	 * The personal-info fallback renders as a DESCENDANT of the purchase form.
	 *
	 * The section hooks fire from the engaged box's
	 * print_content(), which Elementor echoes BETWEEN the <form> open and close
	 * tags, so the fallback markup must sit AFTER the <form> open tag and BEFORE
	 * the </form> close tag — never as a preceding sibling outside the form.
	 *
	 * @since 3.7.0
	 */
	public function test_personal_info_fallback_renders_inside_the_form() {
		$element    = $this->element_missing_personal_info( 'fbTopo1' );
		$subscriber = new CheckoutFormLayer();

		$output = $this->render_engaged_box( $subscriber, $element );

		$form_open  = strpos( $output, '<form id="edd_purchase_form"' );
		$form_close = strpos( $output, '</form>' );
		$fallback   = strpos( $output, 'edd-blocks__checkout-user' );

		$this->assertNotFalse( $form_open, 'The purchase form open tag must be present.' );
		$this->assertNotFalse( $form_close, 'The purchase form close tag must be present.' );
		$this->assertNotFalse( $fallback, 'The personal-info fallback markup must be present.' );
		$this->assertGreaterThan(
			$form_open,
			$fallback,
			'The personal-info fallback must render AFTER the <form> open tag (inside the form), not as a preceding sibling.'
		);
		$this->assertLessThan(
			$form_close,
			$fallback,
			'The personal-info fallback must render BEFORE the </form> close tag (inside the form).'
		);
	}

	// -----------------------------------------------------------------------
	// Logged-in account block emitted at the top of the form (block parity).
	// -----------------------------------------------------------------------

	/**
	 * A logged-in customer's account block renders at the TOP of the purchase form.
	 *
	 * Mirrors the block checkout, which includes logged-in.php inside the form before
	 * do_checkout_form_top(). The subscriber must emit the .edd-blocks__logged-in
	 * account block exactly once, inside the form, and BEFORE the personal-info
	 * section — never nested in the personal-info widget.
	 *
	 * @since 3.7.0
	 */
	public function test_logged_in_account_block_renders_at_form_top() {
		$user_id = self::factory()->user->create();
		wp_set_current_user( $user_id );
		$this->reset_attribute_cache();

		// A box missing the personal-info widget: the fallback emits the shared
		// user-details markup on edd_checkout_form_top (only the hidden identity
		// inputs for a logged-in customer), so the account block's position can be
		// compared against it to prove the account block renders first.
		$element    = $this->element_missing_personal_info( 'boxLoggedInTop' );
		$subscriber = new CheckoutFormLayer();

		$output = $this->render_engaged_box( $subscriber, $element );

		wp_set_current_user( 0 );
		$this->reset_attribute_cache();

		$this->assertSame(
			1,
			substr_count( $output, 'edd-blocks__logged-in' ),
			'The logged-in account block must render exactly once (no duplicate) from the subscriber.'
		);
		$this->assertStringContainsString(
			'edd-blocks__logged-in',
			$output,
			'The account block must render its logged-in account view for the logged-in customer.'
		);

		$form_open = strpos( $output, '<form id="edd_purchase_form"' );
		$account   = strpos( $output, 'edd-blocks__logged-in' );
		$personal  = strpos( $output, 'edd-blocks__user-details' );

		$this->assertNotFalse( $form_open, 'The purchase form open tag must be present.' );
		$this->assertNotFalse( $account, 'The account block must be present.' );
		$this->assertNotFalse( $personal, 'The personal-info section must be present.' );
		$this->assertGreaterThan(
			$form_open,
			$account,
			'The account block must render AFTER the <form> open tag (inside the form).'
		);
		$this->assertLessThan(
			$personal,
			$account,
			'The account block must render at the TOP of the form, before the personal-info section.'
		);
	}

	/**
	 * A guest render emits no logged-in account block at the form top.
	 *
	 * The account block is gated on the logged_in attribute, so guest checkout is
	 * unaffected.
	 *
	 * @since 3.7.0
	 */
	public function test_guest_has_no_logged_in_account_block_at_form_top() {
		wp_set_current_user( 0 );
		$this->reset_attribute_cache();

		$element    = $this->engageable_element( 'boxGuestTop' );
		$subscriber = new CheckoutFormLayer();

		$output = $this->render_engaged_box( $subscriber, $element );

		$this->assertStringNotContainsString(
			'edd-blocks__logged-in',
			$output,
			'A guest render must not emit the logged-in account block.'
		);
	}

	/**
	 * edd_checkout_form_top fires exactly once per render pass: once for the single
	 * engaged box, and never again for a second (refused) box.
	 *
	 * @since 3.7.0
	 */
	public function test_form_top_fires_exactly_once_per_page_render() {
		$count   = 0;
		$counter = function () use ( &$count ) {
			++$count;
		};
		add_action( 'edd_checkout_form_top', $counter, 5 );

		$subscriber = new CheckoutFormLayer();

		$box_a = $this->engageable_element( 'boxOnce1' );
		$this->render_engaged_box( $subscriber, $box_a, 'A' );

		// A second box is refused engagement, so its section hooks never fire.
		$box_b = $this->engageable_element( 'boxOnce2' );
		$subscriber->before_render( $box_b );
		$subscriber->after_render( $box_b );

		remove_action( 'edd_checkout_form_top', $counter, 5 );

		$this->assertSame(
			1,
			$count,
			'edd_checkout_form_top must fire exactly once per render pass: once for the engaged box, never for a second refused box.'
		);
	}

	// -----------------------------------------------------------------------
	// Shutdown failsafe: a render that engaged but never reached after_render
	// (a child threw) must leave no engagement state behind at shutdown.
	// -----------------------------------------------------------------------

	/**
	 * The shutdown failsafe clears every leaked engagement bit: both section
	 * fallback closures, the FormLayer engaged-element state, and the page-level
	 * form-open guard.
	 *
	 * @since 3.7.0
	 */
	public function test_shutdown_failsafe_clears_leaked_engagement_state() {
		global $wp_filter;

		$priority_ten_count = static function ( $hook ) {
			global $wp_filter;
			if ( empty( $wp_filter[ $hook ] ) || empty( $wp_filter[ $hook ]->callbacks[10] ) ) {
				return 0;
			}

			return count( $wp_filter[ $hook ]->callbacks[10] );
		};

		// before_render() now fires render_form_top() (opening the form), which removes
		// edd_show_payment_icons from edd_checkout_form_top for block-parity de-dup.
		// Neutralize that here so the pri-10 baseline reflects only the engage-registered
		// fallback and is not perturbed by the icons removal.
		remove_action( 'edd_checkout_form_top', 'edd_show_payment_icons' );

		$top_baseline    = $priority_ten_count( 'edd_checkout_form_top' );
		$bottom_baseline = $priority_ten_count( 'edd_checkout_form_bottom' );
		$shutdown_before = isset( $wp_filter['shutdown'] ) ? $wp_filter['shutdown']->callbacks : array();

		$element    = $this->engageable_element( 'leak001' );
		$subscriber = new CheckoutFormLayer();

		// Engage via before_render only — no after_render, simulating a render that
		// leaked because a child threw before after_render's finally block ran.
		$subscriber->before_render( $element );

		$this->assertTrue(
			FormLayer::is_engaged_element( 'leak001' ),
			'The box must be recorded as engaged before the failsafe runs.'
		);
		$this->assertSame(
			$top_baseline + 1,
			$priority_ten_count( 'edd_checkout_form_top' ),
			'Engaging registers one priority-10 personal-info fallback on edd_checkout_form_top.'
		);
		$this->assertSame(
			$bottom_baseline + 1,
			$priority_ten_count( 'edd_checkout_form_bottom' ),
			'Engaging registers one priority-10 payment-info fallback on edd_checkout_form_bottom.'
		);

		// Invoke ONLY the subscriber's shutdown failsafe closure (the one added to
		// the shutdown hook by engaging). Firing WP's global shutdown would also run
		// core's buffer-flush handlers and close PHPUnit's own output buffer.
		foreach ( $wp_filter['shutdown']->callbacks as $priority => $callbacks ) {
			foreach ( $callbacks as $id => $callback ) {
				if ( ! isset( $shutdown_before[ $priority ][ $id ] ) ) {
					call_user_func( $callback['function'] );
				}
			}
		}

		$this->assertFalse(
			FormLayer::is_engaged_element( 'leak001' ),
			'The shutdown failsafe must clear the FormLayer engaged-element state.'
		);
		$this->assertSame(
			$top_baseline,
			$priority_ten_count( 'edd_checkout_form_top' ),
			'The shutdown failsafe must remove the leaked personal-info fallback closure.'
		);
		$this->assertSame(
			$bottom_baseline,
			$priority_ten_count( 'edd_checkout_form_bottom' ),
			'The shutdown failsafe must remove the leaked payment-info fallback closure.'
		);

		// The page-level engaged flag was reset, so a fresh box can engage again on
		// the SAME subscriber instance.
		$next = $this->engageable_element( 'leak002' );
		$subscriber->before_render( $next );
		$this->assertTrue(
			FormLayer::is_engaged_element( 'leak002' ),
			'A new box must engage after the failsafe reset the page-level form-open guard.'
		);

		$subscriber->after_render( $next );
	}

	// -----------------------------------------------------------------------
	// A box missing BOTH required sections still yields a single complete form
	// with both fallbacks rendered inside it.
	// -----------------------------------------------------------------------

	/**
	 * An empty box (both required sections absent) renders BOTH the personal-info
	 * and payment-info fallbacks INSIDE the single <form id="edd_purchase_form">.
	 *
	 * @since 3.7.0
	 */
	public function test_empty_box_renders_both_sections_inside_single_form() {
		$element    = $this->empty_box_element( 'empty001' );
		$subscriber = new CheckoutFormLayer();

		$output = $this->render_engaged_box( $subscriber, $element );

		$this->assertSame(
			1,
			substr_count( $output, 'id="edd_purchase_form"' ),
			'An empty box must still yield exactly one purchase form.'
		);

		$form_open  = strpos( $output, '<form id="edd_purchase_form"' );
		$form_close = strpos( $output, '</form>' );
		$user       = strpos( $output, 'edd-blocks__checkout-user' );
		$payment    = strpos( $output, 'edd-blocks__payment-details' );

		$this->assertNotFalse( $user, 'The personal-info fallback markup must be present.' );
		$this->assertNotFalse( $payment, 'The payment-info fallback markup must be present.' );

		$this->assertGreaterThan( $form_open, $user, 'Personal-info must render after the <form> open tag.' );
		$this->assertLessThan( $form_close, $user, 'Personal-info must render before the </form> close tag.' );
		$this->assertGreaterThan( $form_open, $payment, 'Payment-info must render after the <form> open tag.' );
		$this->assertLessThan( $form_close, $payment, 'Payment-info must render before the </form> close tag.' );
	}

	// -----------------------------------------------------------------------
	// Payment icons de-duplication on the engaged render.
	// -----------------------------------------------------------------------

	/**
	 * edd_show_payment_icons is co-hooked on edd_payment_mode_top AND
	 * edd_checkout_form_top (template.php). render_form_top removes it from
	 * edd_checkout_form_top before firing the hook (block parity), so the icons
	 * markup does not render a second time via the form-top hook.
	 *
	 * @since 3.7.0
	 */
	public function test_payment_icons_deduplicated_on_engaged_render() {
		// Mirror template.php's registration: co-hooked on BOTH hooks.
		add_action( 'edd_payment_mode_top', 'edd_show_payment_icons' );
		add_action( 'edd_checkout_form_top', 'edd_show_payment_icons' );

		$element    = $this->engageable_element( 'icons001' );
		$subscriber = new CheckoutFormLayer();

		$output = $this->render_engaged_box( $subscriber, $element, 'children' );

		$this->assertLessThanOrEqual(
			1,
			substr_count( $output, 'edd-payment-icons' ),
			'Payment icons must not render twice on the engaged Elementor render.'
		);

		$this->assertFalse(
			has_action( 'edd_checkout_form_top', 'edd_show_payment_icons' ),
			'render_form_top must remove edd_show_payment_icons from edd_checkout_form_top (block parity de-dup).'
		);

		remove_action( 'edd_payment_mode_top', 'edd_show_payment_icons' );
	}

	// -----------------------------------------------------------------------
	// edd_before_purchase_form fires once per engaged render, not for refused boxes.
	// -----------------------------------------------------------------------

	/**
	 * edd_before_purchase_form fires exactly once for the single engaged box and
	 * NOT for a second, refused box (Stripe recurring rate-limiting hooks it).
	 *
	 * @since 3.7.0
	 */
	public function test_before_purchase_form_fires_once_per_engaged_render_not_for_refused() {
		$count   = 0;
		$counter = function () use ( &$count ) {
			++$count;
		};
		add_action( 'edd_before_purchase_form', $counter );

		$subscriber = new CheckoutFormLayer();

		$box_a = $this->engageable_element( 'before001' );
		$this->render_engaged_box( $subscriber, $box_a, 'A' );

		$this->assertSame(
			1,
			$count,
			'edd_before_purchase_form must fire exactly once for the engaged box.'
		);

		// A second box is refused engagement, so the hook must not fire again.
		$box_b = $this->engageable_element( 'before002' );
		$subscriber->before_render( $box_b );
		$subscriber->after_render( $box_b );

		remove_action( 'edd_before_purchase_form', $counter );

		$this->assertSame(
			1,
			$count,
			'edd_before_purchase_form must not fire for a second, refused box.'
		);
	}

	// -----------------------------------------------------------------------
	// Empty cart: no purchase form is opened. The first box owns the empty-cart
	// notice (mirroring the block checkout, which shows the message and no form);
	// any other box is refused. The editor/preview is exempt (tested via the
	// stubbed front-end Elementor instance below, whose editor reports not-editing).
	// -----------------------------------------------------------------------

	/**
	 * The FormLayer empty-cart marker round-trips and clears cleanly.
	 *
	 * @since 3.7.0
	 */
	public function test_form_layer_empty_cart_marker_round_trip() {
		FormLayer::clear_empty_cart();

		$this->assertFalse( FormLayer::is_empty_cart_element( 'anyBox' ), 'No owner before marking.' );

		FormLayer::mark_empty_cart( 'ownerBox' );
		$this->assertTrue( FormLayer::is_empty_cart_element( 'ownerBox' ) );
		$this->assertFalse( FormLayer::is_empty_cart_element( 'otherBox' ), 'Only the marked id owns the notice.' );
		$this->assertFalse( FormLayer::is_empty_cart_element( '' ), 'An empty id never owns the notice.' );

		FormLayer::clear_empty_cart();
		$this->assertFalse( FormLayer::is_empty_cart_element( 'ownerBox' ), 'clear_empty_cart releases the owner.' );
	}

	/**
	 * A new document render pass clears the empty-cart owner.
	 *
	 * @since 3.7.0
	 */
	public function test_reset_for_new_render_pass_clears_empty_cart_owner() {
		FormLayer::mark_empty_cart( 'passBox' );
		$this->assertTrue( FormLayer::is_empty_cart_element( 'passBox' ) );

		$subscriber = new CheckoutFormLayer();
		$subscriber->reset_for_new_render_pass();

		$this->assertFalse( FormLayer::is_empty_cart_element( 'passBox' ), 'A new render pass must clear the empty-cart owner.' );
	}

	/**
	 * A new document render pass clears the engaged-element state even when the
	 * per-box depth map is still non-empty (a render that engaged but leaked its
	 * after_render teardown), and a fresh box can engage again on the same
	 * subscriber instance.
	 *
	 * Regression guard: the earlier depth-map guard blocked ALL reset while the map
	 * was stuck, so a later pass saw a stale is_engaged_element() and could never
	 * open its own purchase form.
	 *
	 * @since 3.7.0
	 */
	public function test_reset_for_new_render_pass_clears_engaged_element_with_stuck_map() {
		$subscriber = new CheckoutFormLayer();

		// Engage via before_render only (no after_render), leaving the depth map and
		// the shared engaged-element id stuck as if a child render threw.
		$box = $this->engageable_element( 'stuckBox1' );
		$subscriber->before_render( $box );

		$this->assertTrue(
			FormLayer::is_engaged_element( 'stuckBox1' ),
			'The box must be engaged before the reset.'
		);

		// A new render pass must clear engagement despite the stuck depth map.
		$subscriber->reset_for_new_render_pass();

		$this->assertFalse(
			FormLayer::is_engaged_element( 'stuckBox1' ),
			'A new render pass must clear the engaged-element state even with a stuck depth map.'
		);

		// A fresh box must be able to engage on the next pass.
		$next = $this->engageable_element( 'stuckBox2' );
		$subscriber->before_render( $next );
		$this->assertTrue(
			FormLayer::is_engaged_element( 'stuckBox2' ),
			'A fresh box must engage after the reset cleared the stuck engagement.'
		);

		$subscriber->after_render( $next );
	}

	// -----------------------------------------------------------------------
	// Nested-document render pass: a nested Elementor document rendered mid-
	// outer-render must not wipe the outer box's open wrapper, and an empty
	// nested document (unbalanced push) must not poison a later pass's reset.
	// -----------------------------------------------------------------------

	/**
	 * A nested document render pass mid-outer-render must NOT leave the outer box's
	 * purchase form unclosed.
	 *
	 * The outer document begins its pass and the outer box opens
	 * #edd_checkout_form_wrap and the <form>; a NESTED document then fires its own
	 * push+pop (reset_for_new_render_pass + end_document_pass) mid-outer-render.
	 * Because a nested push must not tear down the outer box's state, the outer box's
	 * after_render must still emit </form> and the closing wrapper.
	 *
	 * @since 3.7.0
	 */
	public function test_nested_document_render_pass_leaves_outer_box_unclosed() {
		$subscriber = new CheckoutFormLayer();

		$mark = strlen( $this->getActualOutput() );

		// The outer document begins its render pass.
		$subscriber->reset_for_new_render_pass( 'outerDoc' );

		// The outer checkout box opens the wrapper + <form>.
		$outer = $this->engageable_element( 'outerBox9' );
		$subscriber->before_render( $outer );

		// A nested Elementor document renders mid-outer-render: its paired push+pop
		// fire while the outer box is still open.
		$subscriber->reset_for_new_render_pass( 'nestedDoc' );
		$subscriber->end_document_pass( 'nestedDoc' );

		// The outer box closes: its </form> + wrapper close must still be emitted.
		$subscriber->after_render( $outer );

		$output = $this->captured_since( $mark );

		$this->assertStringContainsString(
			'</form>',
			$output,
			'A nested document render mid-outer-render must not wipe the outer box: its </form> must still close.'
		);
		$this->assertSame(
			1,
			substr_count( $output, 'id="' . FormLayer::FORM_ID . '"' ),
			'Exactly one purchase form is opened, by the outer box.'
		);

		$subscriber->end_document_pass( 'outerDoc' );
	}

	/**
	 * An empty nested document (a push with no paired pop — Elementor's empty-data
	 * early return) must NOT permanently poison the render stack so a later
	 * independent top-level pass loses its reset.
	 *
	 * The outer box engages and opens the page form; an empty nested document fires
	 * the push but early-returns before its pop, leaving an unbalanced stack entry;
	 * the outer box closes and the outer pass ends. The outer pop must sweep the
	 * phantom entry so the stack returns to empty and the shared state resets,
	 * letting a subsequent independent top-level box open its own form. A naive fix
	 * that pops a single entry leaves the phantom and starves the later pass.
	 *
	 * @since 3.7.0
	 */
	public function test_empty_nested_document_pass_does_not_poison_reset() {
		$subscriber = new CheckoutFormLayer();

		// The outer document pass begins and its box engages (opens the form).
		$subscriber->reset_for_new_render_pass( 'outerDocR' );
		$outer = $this->engageable_element( 'outerBoxR' );
		$subscriber->before_render( $outer );
		$this->assertTrue( FormLayer::is_page_form_open(), 'The outer box opens the page form.' );

		// An empty nested document fires the push but early-returns before its pop.
		$subscriber->reset_for_new_render_pass( 'emptyNested' );

		// The outer box closes and the outer document pass ends.
		$subscriber->after_render( $outer );
		$subscriber->end_document_pass( 'outerDocR' );

		// Ending the outermost pass swept the unbalanced nested push, so shared state reset.
		$this->assertFalse(
			FormLayer::is_page_form_open(),
			'Ending the outermost pass must reset the page-form-open guard despite the unbalanced empty-nested push.'
		);
		$this->assertFalse(
			FormLayer::is_engaged_element( 'outerBoxR' ),
			'The engaged-element state must be cleared once the outermost pass ends.'
		);

		// A subsequent independent top-level pass must open its own form.
		$next = $this->engageable_element( 'laterBoxR' );
		$subscriber->reset_for_new_render_pass( 'laterDocR' );
		$subscriber->before_render( $next );
		$this->assertTrue(
			FormLayer::is_engaged_element( 'laterBoxR' ),
			'A later independent top-level pass must not be starved of its reset by the phantom entry.'
		);

		$subscriber->after_render( $next );
		$subscriber->end_document_pass( 'laterDocR' );
	}

	/**
	 * On an empty cart the front-end box opens no form and owns the empty-cart notice.
	 *
	 * @since 3.7.0
	 */
	public function test_empty_cart_marks_notice_owner_and_opens_no_form() {
		edd_empty_cart();
		$this->stub_frontend_elementor();

		$subscriber = new CheckoutFormLayer();
		$box        = $this->engageable_element( 'emptyBox1' );

		$mark = strlen( $this->getActualOutput() );
		$subscriber->before_render( $box );
		$emitted = $this->captured_since( $mark );
		$subscriber->after_render( $box );

		$this->assertSame( '', $emitted, 'No wrapper/form markup may be emitted on an empty cart.' );
		$this->assertArrayNotHasKey( 'html_tag', $box->settings, 'The box must NOT be forced to <form> on an empty cart.' );
		$this->assertTrue( FormLayer::is_empty_cart_element( 'emptyBox1' ), 'The first box must own the empty-cart notice.' );
		$this->assertFalse( FormLayer::is_engaged_element( 'emptyBox1' ), 'No box opens a purchase form on an empty cart.' );

		$this->reset_frontend_elementor();
	}

	/**
	 * On an empty cart only the first box owns the notice; a second box is refused.
	 *
	 * @since 3.7.0
	 */
	public function test_empty_cart_second_box_not_marked_or_engaged() {
		edd_empty_cart();
		$this->stub_frontend_elementor();

		$subscriber = new CheckoutFormLayer();

		$box_a = $this->engageable_element( 'emptyA01' );
		$subscriber->before_render( $box_a );
		$subscriber->after_render( $box_a );

		$box_b = $this->engageable_element( 'emptyB02' );
		$mark  = strlen( $this->getActualOutput() );
		$subscriber->before_render( $box_b );
		$emitted = $this->captured_since( $mark );
		$subscriber->after_render( $box_b );

		$this->assertTrue( FormLayer::is_empty_cart_element( 'emptyA01' ), 'Only the first box owns the empty-cart notice.' );
		$this->assertFalse( FormLayer::is_empty_cart_element( 'emptyB02' ), 'A second box must not own the empty-cart notice.' );
		$this->assertFalse( FormLayer::is_engaged_element( 'emptyB02' ), 'A second box must not engage on an empty cart.' );
		$this->assertSame( '', $emitted, 'A second box on an empty cart emits nothing.' );

		$this->reset_frontend_elementor();
	}

	/**
	 * A populated cart still engages a form and sets no empty-cart owner.
	 *
	 * @since 3.7.0
	 */
	public function test_populated_cart_engages_and_sets_no_empty_cart_owner() {
		// setUp() seeds one cart item, so the cart is not empty here.
		$this->stub_frontend_elementor();

		$subscriber = new CheckoutFormLayer();
		$box        = $this->engageable_element( 'liveBox1' );
		$output     = $this->render_engaged_box( $subscriber, $box, 'X' );

		$this->assertFalse( FormLayer::is_empty_cart_element( 'liveBox1' ), 'A populated cart must not set an empty-cart owner.' );
		$this->assertStringContainsString(
			'id="' . FormLayer::FORM_ID . '"',
			$output,
			'A populated cart must open the single page-level purchase form.'
		);

		$this->reset_frontend_elementor();
	}

	// -----------------------------------------------------------------------
	// render_form_bottom() captcha branch: the checkout captcha renders after
	// the bottom hook when can_do_captcha() is true, mirroring the block
	// purchase form. The classic/gateway-AJAX form never emits it, so without
	// this branch the Elementor path would omit the checkout captcha.
	// -----------------------------------------------------------------------

	/**
	 * render_form_bottom fires edd_checkout_form_bottom and, when
	 * can_do_captcha() is true, renders the checkout captcha field.
	 *
	 * @since 3.7.0
	 *
	 * @covers \EDD\Elementor\Checkout\FormLayer::render_form_bottom
	 */
	public function test_render_form_bottom_renders_captcha_when_enabled() {
		edd_update_option( 'captcha_provider', 'recaptcha' );
		edd_update_option( 'recaptcha_site_key', 'test-site-key' );
		edd_update_option( 'recaptcha_secret_key', 'test-secret-key' );
		edd_update_option( 'recaptcha_checkout', 'all' );

		$this->assertTrue(
			\EDD\Captcha\Utility::can_do_captcha(),
			'Captcha must be enabled so the render_form_bottom captcha branch is exercised.'
		);

		$fired = false;
		$spy   = function () use ( &$fired ) {
			$fired = true;
		};
		add_action( 'edd_checkout_form_bottom', $spy );

		$mark = strlen( $this->getActualOutput() );
		FormLayer::render_form_bottom();
		$output = $this->captured_since( $mark );

		remove_action( 'edd_checkout_form_bottom', $spy );
		edd_delete_option( 'captcha_provider' );
		edd_delete_option( 'recaptcha_site_key' );
		edd_delete_option( 'recaptcha_secret_key' );
		edd_delete_option( 'recaptcha_checkout' );

		$this->assertTrue( $fired, 'render_form_bottom must fire edd_checkout_form_bottom.' );
		$this->assertStringContainsString(
			'edd-blocks-recaptcha',
			$output,
			'When can_do_captcha() is true, render_form_bottom must render the checkout captcha field (block parity) that the classic/gateway-AJAX form omits.'
		);
	}

	/**
	 * render_form_bottom fires edd_checkout_form_bottom but renders no captcha
	 * field when captcha is disabled.
	 *
	 * @since 3.7.0
	 *
	 * @covers \EDD\Elementor\Checkout\FormLayer::render_form_bottom
	 */
	public function test_render_form_bottom_skips_captcha_when_disabled() {
		edd_delete_option( 'recaptcha_checkout' );
		edd_update_option( 'captcha_provider', 'none' );

		$this->assertFalse(
			\EDD\Captcha\Utility::can_do_captcha(),
			'Captcha must be disabled for the skip branch.'
		);

		$fired = false;
		$spy   = function () use ( &$fired ) {
			$fired = true;
		};
		add_action( 'edd_checkout_form_bottom', $spy );

		$mark = strlen( $this->getActualOutput() );
		FormLayer::render_form_bottom();
		$output = $this->captured_since( $mark );

		remove_action( 'edd_checkout_form_bottom', $spy );
		edd_delete_option( 'captcha_provider' );

		$this->assertTrue( $fired, 'render_form_bottom must always fire edd_checkout_form_bottom.' );
		$this->assertStringNotContainsString(
			'edd-blocks-recaptcha',
			$output,
			'With captcha disabled, render_form_bottom must not render the captcha field.'
		);
	}

	// -----------------------------------------------------------------------
	// Marker-driven detection at the ENQUEUE and RENDER paths.
	// The positive across-lifecycle case is covered by
	// test_detection_is_marker_driven_across_render_lifecycle above; these add the
	// NEGATIVE (no-marker) case and the explicit Validator::has_block() render path.
	// -----------------------------------------------------------------------

	/**
	 * A page WITHOUT the wp:edd/checkout marker is not detected as checkout, at
	 * both the render path (Validator::has_block by id) and the enqueue path
	 * (edd_is_checkout(), which the PayPal script loader reads at
	 * includes/gateways/paypal/scripts.php:63).
	 *
	 * Detection is marker-driven post-P4: has_block()/edd_is_checkout() resolve from
	 * the wp:edd/checkout marker, with no filter added or removed. A page whose
	 * content carries the marker resolves true; a loose-widget-only page (no marker)
	 * resolves false.
	 *
	 * @since 3.7.0
	 */
	public function test_detection_negative_for_page_without_marker() {
		$marker_id = self::factory()->post->create(
			array(
				'post_status'  => 'publish',
				'post_type'    => 'page',
				'post_content' => '<!-- wp:edd/checkout /-->',
			)
		);
		$no_marker_id = self::factory()->post->create(
			array(
				'post_status'  => 'publish',
				'post_type'    => 'page',
				'post_content' => '<div class="wp-block-edd-checkout-cart"></div>',
			)
		);

		// Render path: Validator::has_block() (-> has_block('edd/checkout')) keys on
		// the marker in the saved content.
		$this->assertTrue(
			\EDD\Checkout\Validator::has_block( $marker_id ),
			'A page whose saved markup carries the wp:edd/checkout marker must be detected as a checkout (render path).'
		);
		$this->assertFalse(
			\EDD\Checkout\Validator::has_block( $no_marker_id ),
			'A loose-widget-only page with no wp:edd/checkout marker must NOT be detected as a checkout (render path).'
		);

		// Enqueue path: edd_is_checkout() is the gate the PayPal loader reads. On a
		// no-marker page it must resolve false — from the marker, with no filter in play.
		$this->go_to( get_permalink( $no_marker_id ) );
		$this->reset_checkout_detection_cache();
		$this->assertFalse(
			edd_is_checkout(),
			'edd_is_checkout() (the PayPal enqueue gate) must be false on a no-marker page — detection is marker-driven, not filter-driven.'
		);
	}

	/**
	 * A Theme-Builder header/footer template pass (no checkout box in its tree)
	 * opens no purchase form and is not detected as a checkout.
	 *
	 * A header/footer template holds no edd-checkout-box, so should_engage is false
	 * and the subscriber emits no wrapper/form when it renders such an element; and
	 * because no box means no wp:edd/checkout marker is ever written, has_block()
	 * stays false for such a page. Scoped to the existing "no box present" fixture
	 * shape (no new Theme-Builder scaffolding invented).
	 *
	 * @since 3.7.0
	 */
	public function test_header_footer_template_pass_opens_no_form_and_is_not_checkout() {
		// A header/footer container render: not an edd-checkout-box, so the subscriber
		// engages nothing and emits no wrapper/form.
		$header_element = new FakeFormLayerElement(
			array(
				'id'       => 'hdrFtr001',
				'elType'   => 'container',
				'settings' => array(),
				'elements' => array(
					array(
						'elType'     => 'widget',
						'widgetType' => 'theme-site-logo',
						'settings'   => array(),
						'elements'   => array(),
					),
				),
			)
		);
		$subscriber = new CheckoutFormLayer();

		$mark = strlen( $this->getActualOutput() );
		$subscriber->before_render( $header_element );
		$emitted = $this->captured_since( $mark );
		$subscriber->after_render( $header_element );

		$this->assertSame(
			'',
			$emitted,
			'A header/footer template pass (no checkout box) must open no purchase form.'
		);
		$this->assertFalse(
			FormLayer::is_engaged_element( 'hdrFtr001' ),
			'A header/footer container must not be recorded as the engaged box.'
		);

		// No box in the tree => no marker written => not a checkout page.
		$header_page_id = self::factory()->post->create(
			array(
				'post_status'  => 'publish',
				'post_type'    => 'page',
				'post_content' => '<header class="site-header">Header</header>',
			)
		);
		$this->assertFalse(
			\EDD\Checkout\Validator::has_block( $header_page_id ),
			'A header/footer template with no checkout box writes no marker, so has_block() stays false.'
		);
	}

	// -----------------------------------------------------------------------
	// CONTENT case: arbitrary content / other forms alongside the box on the same
	// page. The box's form-boundary must wrap ONLY the box, never its siblings.
	// -----------------------------------------------------------------------

	/**
	 * The box form-boundary does not leak around sibling content or unrelated forms.
	 *
	 * On a page that also holds arbitrary content and other (non-checkout) forms
	 * before and after the box, the subscriber opens exactly ONE #edd_purchase_form
	 * and its wrapper wraps only the box's own children — the sibling content and the
	 * unrelated forms are neither swallowed by the box wrapper nor nested inside the
	 * purchase form.
	 *
	 * @since 3.7.0
	 */
	public function test_box_form_boundary_does_not_leak_around_sibling_content() {
		$subscriber = new CheckoutFormLayer();
		$box        = $this->engageable_element( 'contentBox1' );

		$before_sibling = '<div class="page-intro">Welcome</div>'
			. '<form id="unrelated_newsletter" action="/subscribe"><input name="email"></form>';
		$after_sibling  = '<form id="unrelated_search" action="/search"><input name="q"></form>'
			. '<div class="page-outro">Thanks</div>';

		$mark = strlen( $this->getActualOutput() );
		echo $before_sibling; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- test-controlled static markup.
		$box_output = $this->render_engaged_box( $subscriber, $box, '<div class="fields">box children</div>' );
		echo $after_sibling; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- test-controlled static markup.
		$full = $this->captured_since( $mark );

		// Exactly one purchase form on the whole page — contributed only by the box.
		$this->assertSame(
			1,
			substr_count( $full, 'id="' . FormLayer::FORM_ID . '"' ),
			'The box must open exactly one purchase form on a page that also holds unrelated content/forms.'
		);

		// The box's OWN emitted markup wraps only its children — never the siblings.
		$this->assertStringNotContainsString(
			'unrelated_newsletter',
			$box_output,
			'The box form-boundary must not wrap the unrelated form that precedes the box.'
		);
		$this->assertStringNotContainsString(
			'unrelated_search',
			$box_output,
			'The box form-boundary must not wrap the unrelated form that follows the box.'
		);
		$this->assertStringNotContainsString( 'page-intro', $box_output, 'Sibling content before the box must stay outside the box wrapper.' );
		$this->assertStringNotContainsString( 'page-outro', $box_output, 'Sibling content after the box must stay outside the box wrapper.' );

		// The unrelated forms survive untouched in the page output.
		$this->assertStringContainsString( 'id="unrelated_newsletter"', $full );
		$this->assertStringContainsString( 'id="unrelated_search"', $full );
	}

	// -----------------------------------------------------------------------
	// Address-with-personal-info + #edd_cc_address count (the CORE oracle).
	//
	// The composable Elementor checkout renders the billing address via the
	// personal-info section (UserDetails), NOT via the edd/checkout-* Gutenberg
	// markers, which stay in post_content but are suppressed by
	// Checkout::prevent_checkout_block_render on a page that carries a checkout box.
	// G1's failure mode is a throwaway do_blocks pass firing the inner-marker render
	// (edd_checkout_form_top -> UserDetails -> Address::render, which fires
	// edd_cc_billing_top) BEFORE the visible Elementor pass; because both
	// edd_checkout_form_top and edd_cc_billing_top are fire-once-per-request
	// (Utility::do_checkout_form_top and Address::get_fields_to_render each guard on
	// did_action), a throwaway that consumes the billing hook first would leave the
	// VISIBLE address either lost or doubled. A plain single-render count NEVER
	// exercises that ordering, so the oracle below drives the throwaway
	// leg first and proves the suppression preserves exactly one visible address.
	// -----------------------------------------------------------------------

	/**
	 * Marker suppression keeps the throwaway do_blocks pass from consuming the
	 * billing hook, so the VISIBLE Elementor pass renders exactly one #edd_cc_address.
	 *
	 * This is the CORE address-count oracle. It does NOT rely on a single render:
	 *
	 * 1. SUPPRESSION INVARIANT (immune to the fire-once render globals): with a
	 *    checkout box on the page, render_block() short-circuits each of edd/checkout
	 *    and its four inner markers to '' BEFORE the block's render callback runs, so
	 *    the throwaway pass cannot fire edd_checkout_form_top / edd_cc_billing_top.
	 * 2. ORDERING ANALOG: the throwaway do_blocks() leg runs FIRST and is asserted to
	 *    emit no billing address AND to leave did_action( 'edd_cc_billing_top' )
	 *    unchanged (the billing hook stays available); THEN the visible engaged render
	 *    yields the address exactly once when the hook was still clean (never doubled,
	 *    never lost).
	 * 3. ANTI-FALSE-PASS CONTROL: with NO box on the page the SAME
	 *    throwaway leg is not suppressed — its inner-marker callback runs and fires
	 *    edd_checkout_form_top — proving the single visible address is caused BY the
	 *    suppression, not by the throwaway being an inert no-op.
	 *
	 * The full live throwaway-before-visible ORDERING across a real the_content
	 * double-render is additionally covered by the P8 live e2e harness; here the
	 * ordering is reproduced headlessly against the shipped suppression mechanism.
	 *
	 * @since 3.7.0
	 *
	 * @covers \EDD\Elementor\Subscribers\Checkout::prevent_checkout_block_render
	 * @covers \EDD\Elementor\Subscribers\CheckoutFormLayer::before_render
	 */
	public function test_marker_suppression_yields_single_visible_billing_address() {
		// Force address fields so the billing fieldset (#edd_cc_address) renders on
		// the visible pass; without configured fields Address::render emits nothing.
		$prev_fields = edd_get_option( 'checkout_address_fields' );
		edd_update_option(
			'checkout_address_fields',
			array(
				'country' => 1,
				'address' => 1,
				'city'    => 1,
				'state'   => 1,
				'zip'     => 1,
			)
		);

		$checkout_blocks = array(
			'edd/checkout',
			'edd/checkout-personal-info',
			'edd/checkout-payment-info',
			'edd/checkout-cart',
			'edd/checkout-discount-form',
		);

		$suppressor = new Checkout();
		add_filter( 'pre_render_block', array( $suppressor, 'prevent_checkout_block_render' ), 10, 3 );

		// (3) ANTI-FALSE-PASS CONTROL FIRST — before anything fires the fire-once
		// edd_checkout_form_top: with NO box the throwaway inner-marker render is NOT
		// suppressed and DOES fire edd_checkout_form_top. Run first because
		// Utility::do_checkout_form_top() self-guards on did_action, so a later run
		// (after the visible pass fired the hook) could not fire it again.
		$this->stub_checkout_box_page( false );

		$registered_here = false;
		if ( ! \WP_Block_Type_Registry::get_instance()->is_registered( 'edd/checkout-personal-info' ) ) {
			register_block_type(
				'edd/checkout-personal-info',
				array( 'render_callback' => array( new \EDD\Blocks\Checkout\PersonalInfo(), 'render' ) )
			);
			$registered_here = true;
		}

		$form_top_hits = 0;
		$form_top_spy  = function () use ( &$form_top_hits ) {
			++$form_top_hits;
		};
		add_action( 'edd_checkout_form_top', $form_top_spy, 99 );
		do_blocks( '<!-- wp:edd/checkout-personal-info /-->' );
		remove_action( 'edd_checkout_form_top', $form_top_spy, 99 );

		if ( $registered_here ) {
			unregister_block_type( 'edd/checkout-personal-info' );
		}
		$this->reset_checkout_box_page();

		$this->assertGreaterThan(
			0,
			$form_top_hits,
			'Without suppression the throwaway inner-marker render must fire edd_checkout_form_top — proving the suppressed pass is genuinely short-circuited, not an inert no-op.'
		);

		// (1) SUPPRESSION INVARIANT: with a checkout box present, each marker render
		// short-circuits to '' before its callback can run.
		$this->stub_checkout_box_page( true );
		foreach ( $checkout_blocks as $name ) {
			$parsed = array(
				'blockName'    => $name,
				'attrs'        => array(),
				'innerBlocks'  => array(),
				'innerHTML'    => '',
				'innerContent' => array(),
			);
			$this->assertSame(
				'',
				render_block( $parsed ),
				"The {$name} marker render must be suppressed (short-circuited to '') on a page carrying an Elementor checkout box, so a throwaway do_blocks pass cannot fire the top-of-form/billing hooks."
			);
		}

		// (2) ORDERING ANALOG: throwaway do_blocks leg FIRST.
		$billing_before_throwaway = did_action( 'edd_cc_billing_top' );
		$throwaway                = do_blocks(
			'<!-- wp:edd/checkout-personal-info /-->'
			. '<!-- wp:edd/checkout-payment-info /-->'
			. '<!-- wp:edd/checkout-cart /-->'
			. '<!-- wp:edd/checkout-discount-form /-->'
		);

		$this->assertSame(
			0,
			substr_count( $throwaway, 'id="edd_cc_address"' ),
			'The suppressed throwaway do_blocks pass must emit no billing address.'
		);
		$this->assertSame(
			$billing_before_throwaway,
			did_action( 'edd_cc_billing_top' ),
			'The suppressed throwaway pass must NOT fire edd_cc_billing_top: the billing hook must stay available so the visible pass can render the address.'
		);

		// Visible engaged pass: render_form_top fires edd_checkout_form_top unguarded,
		// and the personal-info fallback (missing-personal-info box) renders the
		// billing address exactly once — provided the throwaway did not consume the
		// billing hook, which (2) above just proved.
		$billing_clean = ( 0 === did_action( 'edd_cc_billing_top' ) );
		$element       = $this->element_missing_personal_info( 'boxAddrOracle' );
		$layer         = new CheckoutFormLayer();
		$visible       = $this->render_engaged_box( $layer, $element );

		$expected_address = $billing_clean ? 1 : 0;
		$this->assertSame(
			$expected_address,
			substr_count( $visible, 'id="edd_cc_address"' ),
			'The visible Elementor pass must render the billing address exactly once when the billing hook was preserved by suppression (never lost, never doubled).'
		);
		$this->assertLessThanOrEqual(
			1,
			substr_count( $visible, 'id="edd_cc_address"' ),
			'The billing address must never render more than once on the visible pass.'
		);

		// Cleanup.
		remove_filter( 'pre_render_block', array( $suppressor, 'prevent_checkout_block_render' ), 10 );
		$this->reset_checkout_box_page();
		if ( false === $prev_fields ) {
			edd_delete_option( 'checkout_address_fields' );
		} else {
			edd_update_option( 'checkout_address_fields', $prev_fields );
		}
	}

	/**
	 * Marker suppression never FORCES a billing address: with no address fields
	 * required, the engaged Elementor render emits zero #edd_cc_address — the same
	 * count the block checkout would produce (block-parity check).
	 *
	 * The optional-branch companion to the core oracle: suppressing the markers must
	 * not synthesize a billing fieldset where none is configured.
	 *
	 * @since 3.7.0
	 *
	 * @covers \EDD\Elementor\Subscribers\CheckoutFormLayer::before_render
	 */
	public function test_marker_suppression_never_forces_address_when_not_required() {
		// No checkout_address_fields configured and taxes off -> Address::get_fields()
		// is empty -> Address::render() emits nothing.
		$prev_fields = edd_get_option( 'checkout_address_fields' );
		edd_delete_option( 'checkout_address_fields' );

		$element = $this->element_missing_personal_info( 'boxNoAddr' );
		$layer   = new CheckoutFormLayer();
		$visible = $this->render_engaged_box( $layer, $element );

		// The personal-info section still renders (the fallback fired)...
		$this->assertStringContainsString(
			'edd-blocks__checkout-user',
			$visible,
			'The personal-info section must still render on the engaged path even when no billing address is required.'
		);
		// ...but no billing fieldset is synthesized.
		$this->assertSame(
			0,
			substr_count( $visible, 'id="edd_cc_address"' ),
			'Marker suppression must not force a billing address when none is required — the count matches the block checkout (zero).'
		);

		if ( false === $prev_fields ) {
			edd_delete_option( 'checkout_address_fields' );
		} else {
			edd_update_option( 'checkout_address_fields', $prev_fields );
		}
	}

	/**
	 * A logged-in customer with INCOMPLETE profile data still gets the account block
	 * at the top of the form AND the personal-info section still renders (row 9).
	 *
	 * The account block is gated only on the logged_in attribute, never on data
	 * completeness, so a logged-in customer with no name/address on file still sees
	 * the account block; the personal-info fallback still supplies the user-details
	 * fieldset inside the same form.
	 *
	 * @since 3.7.0
	 *
	 * @covers \EDD\Elementor\Subscribers\CheckoutFormLayer::before_render
	 */
	public function test_logged_in_incomplete_data_still_emits_account_block_and_personal_info() {
		// A logged-in user with no first/last name and no stored address (incomplete data).
		$user_id = self::factory()->user->create( array( 'user_email' => 'incomplete-customer@example.test' ) );
		wp_set_current_user( $user_id );
		$this->reset_attribute_cache();

		// A box missing the personal-info widget so the fallback supplies the
		// user-details fieldset alongside the subscriber's account block.
		$element = $this->element_missing_personal_info( 'boxIncomplete' );
		$layer   = new CheckoutFormLayer();
		$output  = $this->render_engaged_box( $layer, $element );

		wp_set_current_user( 0 );
		$this->reset_attribute_cache();

		$this->assertStringContainsString(
			'edd-blocks__logged-in',
			$output,
			'A logged-in customer with incomplete data must still get the account block (gated on logged_in, not on data completeness).'
		);
		$this->assertStringContainsString(
			'edd-blocks__user-details',
			$output,
			'The personal-info section (user-details) must still render for a logged-in customer with incomplete data.'
		);
		// For a logged-in customer UserDetails emits the identity capture as hidden
		// inputs (#edd-email) inside the user-details wrapper — the logged-in analog of
		// the guest .edd-blocks__checkout-user fieldset — so the personal-info section
		// still renders even with incomplete profile data.
		$this->assertStringContainsString(
			'id="edd-email"',
			$output,
			'The personal-info identity field (#edd-email) must still render for a logged-in customer with incomplete data.'
		);
	}

	/**
	 * The renewal-form reposition gate is closed when EDD_SL_VERSION is undefined,
	 * even though edd_sl_renewal_form() exists.
	 *
	 * An installed-but-inactive Software Licensing (the coverage/matrix CI image)
	 * defines the function without the constant, so the gate must answer from the
	 * constant's presence rather than dereferencing it — otherwise every checkout
	 * render fatals with an undefined-constant Error on PHP 8.
	 *
	 * @since 3.7.0
	 *
	 * @covers \EDD\Integrations\SoftwareLicensing::should_move_renewal_form
	 */
	public function test_sl_reposition_gate_requires_the_sl_version_constant() {
		$this->define_sl_renewal_form_stub();

		if ( defined( 'EDD_SL_VERSION' ) ) {
			$this->assertSame(
				version_compare( EDD_SL_VERSION, '3.9.6', '<=' ),
				\EDD\Integrations\SoftwareLicensing::should_move_renewal_form(),
				'With EDD_SL_VERSION defined the gate must follow the version comparison.'
			);

			return;
		}

		$this->assertFalse(
			\EDD\Integrations\SoftwareLicensing::should_move_renewal_form(),
			'should_move_renewal_form() must return false (not fatal) when edd_sl_renewal_form() exists but EDD_SL_VERSION does not.'
		);
	}

	/**
	 * On an older SL site the renewal form is moved OFF edd_before_purchase_form and
	 * edd_after_checkout_cart and RE-ADDED on edd_after_purchase_form, and after_render
	 * fires edd_after_purchase_form.
	 *
	 * The reposition is now shared with the block checkout via
	 * SoftwareLicensing::remove_renewal_form(), which only intervenes for SL <= 3.9.6
	 * (3.9.7 positions the form itself). This test therefore only runs where that gate
	 * is open; the closed-gate contract is asserted in the companion test below.
	 *
	 * @since 3.7.0
	 *
	 * @covers \EDD\Elementor\Subscribers\CheckoutFormLayer::before_render
	 * @covers \EDD\Elementor\Subscribers\CheckoutFormLayer::after_render
	 * @covers \EDD\Integrations\SoftwareLicensing::remove_renewal_form
	 */
	public function test_sl_renewal_form_repositioned_below_the_purchase_form() {
		$this->define_sl_renewal_form_stub();

		if ( ! \EDD\Integrations\SoftwareLicensing::should_move_renewal_form() ) {
			$this->markTestSkipped( 'Requires an EDD Software Licensing version (<= 3.9.6) that needs the checkout to reposition the renewal form.' );
		}

		$state = $this->render_engaged_box_with_sl_hooks( 'boxSlRenewal' );

		// before_render() moved the renewal form off the pre-form and after-cart hooks...
		$this->assertFalse(
			has_action( 'edd_before_purchase_form', 'edd_sl_renewal_form' ),
			'before_render() must remove edd_sl_renewal_form from edd_before_purchase_form so the renewal form does not nest inside the purchase form.'
		);
		$this->assertFalse(
			has_action( 'edd_after_checkout_cart', 'edd_sl_renewal_form' ),
			'before_render() must remove edd_sl_renewal_form from edd_after_checkout_cart so the renewal form is not also emitted inside the cart.'
		);
		// ...and re-added it on the post-form hook (no edd/license-renewal block owns it in the suite).
		$this->assertNotFalse(
			$state['readded_after_form'],
			'before_render() must re-add edd_sl_renewal_form on edd_after_purchase_form so the renewal form renders below the checkout, outside the <form>.'
		);
		$this->assertGreaterThanOrEqual(
			1,
			$state['after_form_fired'],
			'after_render() must fire edd_after_purchase_form, where the SL renewal form is re-homed.'
		);
	}

	/**
	 * On an SL version that positions the renewal form itself, the shared reposition
	 * helper leaves SL's own hook registrations alone.
	 *
	 * SL 3.9.7+ suppresses the in-cart position from can_do_form() and unhooks itself
	 * after rendering at edd_before_purchase_form — which the form layer fires inside
	 * #edd_checkout_form_wrap but BEFORE the <form> opens, so nothing nests. The
	 * Elementor path previously repositioned unconditionally; sharing the block
	 * checkout's gate means it must now be a no-op here. The helper is called directly
	 * rather than through a render pass so an active SL unhooking ITSELF mid-render
	 * cannot be mistaken for the helper having moved the form.
	 *
	 * @since 3.7.0
	 *
	 * @covers \EDD\Integrations\SoftwareLicensing::remove_renewal_form
	 */
	public function test_sl_renewal_form_left_in_place_when_sl_positions_it_itself() {
		$this->define_sl_renewal_form_stub();

		if ( \EDD\Integrations\SoftwareLicensing::should_move_renewal_form() ) {
			$this->markTestSkipped( 'Requires an EDD Software Licensing version (> 3.9.6, or absent) that positions the renewal form itself.' );
		}

		add_action( 'edd_before_purchase_form', 'edd_sl_renewal_form', -1 );
		add_action( 'edd_after_checkout_cart', 'edd_sl_renewal_form' );

		\EDD\Integrations\SoftwareLicensing::remove_renewal_form();

		$after_cart = has_action( 'edd_after_checkout_cart', 'edd_sl_renewal_form' );
		$after_form = has_action( 'edd_after_purchase_form', 'edd_sl_renewal_form' );

		remove_action( 'edd_after_checkout_cart', 'edd_sl_renewal_form' );

		$this->assertNotFalse(
			$after_cart,
			'remove_renewal_form() must leave edd_sl_renewal_form on edd_after_checkout_cart when SL positions the form itself.'
		);
		$this->assertFalse(
			$after_form,
			'remove_renewal_form() must NOT re-home edd_sl_renewal_form onto edd_after_purchase_form when SL positions the form itself.'
		);
	}

	/**
	 * Define the global edd_sl_renewal_form() stub the reposition branch is guarded on.
	 *
	 * The Software Licensing plugin is absent from most suite runs. The stub has an
	 * empty body so it emits nothing, keeping a later engaged render that re-adds it
	 * output-neutral.
	 *
	 * @since 3.7.0
	 *
	 * @return void
	 */
	private function define_sl_renewal_form_stub() {
		if ( function_exists( 'edd_sl_renewal_form' ) ) {
			return;
		}

		eval( 'namespace { function edd_sl_renewal_form() {} }' ); // phpcs:ignore Squiz.PHP.Eval.Discouraged -- Defines a global edd_sl_renewal_form() stub so the SL-reposition branch (guarded by function_exists) can run without the Software Licensing plugin.
	}

	/**
	 * Render an engaged box with SL's own renewal-form registrations in place and
	 * report the resulting hook state.
	 *
	 * Mirrors SL's registrations (pre-form at priority -1 plus the legacy after-cart
	 * position), spies on edd_after_purchase_form, captures the hook state BEFORE
	 * tearing the test's own hooks down, and restores every hook it added.
	 *
	 * @since 3.7.0
	 *
	 * @param string $element_id The fake element id to engage.
	 * @return array{readded_after_form:int|false, after_form_fired:int}
	 */
	private function render_engaged_box_with_sl_hooks( $element_id ) {
		add_action( 'edd_before_purchase_form', 'edd_sl_renewal_form', -1 );
		add_action( 'edd_after_checkout_cart', 'edd_sl_renewal_form' );

		$after_form_fired = 0;
		$after_form_spy   = function () use ( &$after_form_fired ) {
			++$after_form_fired;
		};
		add_action( 'edd_after_purchase_form', $after_form_spy, 99 );

		$layer = new CheckoutFormLayer();
		$this->render_engaged_box( $layer, $this->engageable_element( $element_id ), 'children' );

		$state = array(
			'readded_after_form' => has_action( 'edd_after_purchase_form', 'edd_sl_renewal_form' ),
			'after_form_fired'   => $after_form_fired,
		);

		remove_action( 'edd_after_purchase_form', $after_form_spy, 99 );
		remove_action( 'edd_after_purchase_form', 'edd_sl_renewal_form' );
		remove_action( 'edd_before_purchase_form', 'edd_sl_renewal_form', -1 );
		remove_action( 'edd_after_checkout_cart', 'edd_sl_renewal_form' );

		return $state;
	}

	/**
	 * Build a fully-engageable fake element (personal-info + payment-info).
	 *
	 * @since 3.7.0
	 *
	 * @param string $id Element id (unique per fake so the engaged-depth map
	 *                   can key on it).
	 * @return FakeFormLayerElement
	 */
	private function engageable_element( string $id = 'box0001' ): FakeFormLayerElement {
		return new FakeFormLayerElement(
			array(
				'id'       => $id,
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
			)
		);
	}

	/**
	 * Build an engageable fake element missing the personal-info section.
	 *
	 * Under the new engage rule this box still engages (it is
	 * an edd-checkout-box elType containing a present section widget), but the
	 * box-scoped detection reports personal-info missing so the fallback
	 * renderer must fire.
	 *
	 * @since 3.7.0
	 *
	 * @param string $id Element id.
	 * @return FakeFormLayerElement
	 */
	private function element_missing_personal_info( string $id = 'box0002' ): FakeFormLayerElement {
		return new FakeFormLayerElement(
			array(
				'id'       => $id,
				'elType'   => 'edd-checkout-box',
				'settings' => array(),
				'elements' => array(
					array(
						'elType'     => 'widget',
						'widgetType' => 'edd-checkout-payment-info',
						'settings'   => array(),
						'elements'   => array(),
					),
				),
			)
		);
	}

	/**
	 * Build an engageable fake element missing the payment-info section.
	 *
	 * Engages (edd-checkout-box elType, present personal-info
	 * section), but is missing payment-info so the fallback renderer must fire.
	 *
	 * @since 3.7.0
	 *
	 * @param string $id Element id.
	 * @return FakeFormLayerElement
	 */
	private function element_missing_payment_info( string $id = 'box0003' ): FakeFormLayerElement {
		return new FakeFormLayerElement(
			array(
				'id'       => $id,
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
			)
		);
	}

	/**
	 * Build a fake element that engages purely on its elType: an
	 * edd-checkout-box with no children at all. An empty edd-checkout-box still
	 * engages — the elType alone is sufficient.
	 *
	 * @since 3.7.0
	 *
	 * @param string $id Element id.
	 * @return FakeFormLayerElement
	 */
	private function empty_box_element( string $id = 'box0004' ): FakeFormLayerElement {
		return new FakeFormLayerElement(
			array(
				'id'       => $id,
				'elType'   => 'edd-checkout-box',
				'settings' => array(),
				'elements' => array(),
			)
		);
	}

	/**
	 * Build a nested tree: an edd-checkout-box wrapped inside a plain native
	 * container, with the payment-info widget nested a further level inside a
	 * sub-container of the box (not a direct child).
	 *
	 * A section nested inside an inner container (not a direct
	 * child of the box) must still be detected by the recursive walk.
	 *
	 * @since 3.7.0
	 *
	 * @param string $id Element id for the outer edd-checkout-box.
	 * @return FakeFormLayerElement
	 */
	private function nested_engageable_element( string $id = 'box0005' ): FakeFormLayerElement {
		return new FakeFormLayerElement(
			array(
				'id'       => $id,
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
						'elType'   => 'container',
						'settings' => array(),
						'elements' => array(
							array(
								'elType'     => 'widget',
								'widgetType' => 'edd-checkout-payment-info',
								'settings'   => array(),
								'elements'   => array(),
							),
						),
					),
				),
			)
		);
	}

	/**
	 * Read the output the per-test capture buffer (opened in set_up()) has
	 * accumulated since a prior length mark.
	 *
	 * getActualOutput() reads the current contents of the innermost active
	 * output buffer — here, the local buffer set_up() opened around each test.
	 * Marking its length before/after a call and taking the substr difference
	 * lets the echoed-wrapper approach's plain, unbuffered echoes be asserted on
	 * without splicing or altering the emitted markup.
	 *
	 * @since 3.7.0
	 *
	 * @param int $mark The getActualOutput() length recorded before the call.
	 * @return string The output emitted since the mark.
	 */
	private function captured_since( int $mark ): string {
		return substr( $this->getActualOutput(), $mark );
	}

	/**
	 * Render an engaged box in the real Elementor print_element() order and return
	 * the output captured for the whole render.
	 *
	 * Mirrors Element_Base::print_element() under the page-level form model: the
	 * subscriber's before_render() action echoes the wrapper open, the <form> open,
	 * registers the section fallbacks, and fires edd_checkout_form_top INSIDE the form
	 * (render_form_top); the box then prints its own children (stood in for by
	 * $children, the print_content() output Elementor echoes between the actions); and
	 * the subscriber's after_render() action fires edd_checkout_form_bottom
	 * (render_form_bottom, + captcha) INSIDE the form, then echoes the </form> close
	 * and the wrapper close. Because the section hooks fire around the children within
	 * the form tags, a test using this helper fails if that output lands outside the
	 * form.
	 *
	 * @since 3.7.0
	 *
	 * @param CheckoutFormLayer    $subscriber The form-layer subscriber under test.
	 * @param FakeFormLayerElement $element    The engaged element fake.
	 * @param string               $children   Markup standing in for the box's own child widgets.
	 * @return string The output emitted for the whole engaged render.
	 */
	private function render_engaged_box( CheckoutFormLayer $subscriber, FakeFormLayerElement $element, string $children = '' ): string {
		$mark = strlen( $this->getActualOutput() );

		// before_render() opens the wrapper + <form>.
		$subscriber->before_render( $element );

		// print_content() fires the box hooks around its children, so the top/bottom content renders
		// inside the box. Called directly, as before_render() is: no event manager in tests.
		$subscriber->render_box_top();

		echo $children; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- test-controlled static markup.

		$subscriber->render_box_bottom();

		// after_render() closes the form + wrapper.
		$subscriber->after_render( $element );

		return $this->captured_since( $mark );
	}

	/**
	 * Build an engageable two-section checkout box.
	 *
	 * Mirrors engageable_element() with the two required sections. It carries a
	 * legacy `edd_layout` setting to prove the retired bridge ignores it — the
	 * subscriber emits no `--flex-direction` regardless of the stored value.
	 *
	 * @since 3.7.0
	 *
	 * @param string $id Element id.
	 * @return FakeFormLayerElement
	 */
	private function engageable_two_column_element( string $id = 'boxRow01' ): FakeFormLayerElement {
		return new FakeFormLayerElement(
			array(
				'id'       => $id,
				'elType'   => 'edd-checkout-box',
				'settings' => array( 'edd_layout' => 'row' ),
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
			)
		);
	}

	/**
	 * Reset the Validator's cached checkout-detection state so each assertion
	 * re-resolves the current page from the wp:edd/checkout marker rather than a
	 * value cached by an earlier call.
	 *
	 * @since 3.7.0
	 *
	 * @return void
	 */
	private function reset_checkout_detection_cache(): void {
		foreach ( array( 'is_checkout', 'is_wp_query_set' ) as $property ) {
			$reflected = new \ReflectionProperty( \EDD\Checkout\Validator::class, $property );
			if (PHP_VERSION_ID < 80100) {
                $reflected->setAccessible( true );
            }
			$reflected->setValue( null, null );
		}
	}

	/**
	 * Create and query a page whose content carries the wp:edd/checkout marker,
	 * matching what the seeded box emits into the rendered markup.
	 *
	 * @since 3.7.0
	 *
	 * @return int The created page id.
	 */
	private function go_to_marker_page(): int {
		$post_id = self::factory()->post->create(
			array(
				'post_status'  => 'publish',
				'post_type'    => 'page',
				'post_content' => '<!-- wp:edd/checkout /-->',
			)
		);
		$this->go_to( get_permalink( $post_id ) );

		return $post_id;
	}

	/**
	 * Clear the memoised checkout attributes so a login-state change is reflected.
	 *
	 * Attributes::get() caches its result for the request; the account-block gate
	 * reads logged_in from it, so the cache must be cleared after changing the
	 * current user.
	 *
	 * @since 3.7.0
	 *
	 * @return void
	 */
	private function reset_attribute_cache(): void {
		$property = new \ReflectionProperty( \EDD\Blocks\Checkout\Attributes::class, 'cached_attributes' );
		if (PHP_VERSION_ID < 80100) {
            $property->setAccessible( true );
        }
		$property->setValue( null, null );
	}

	/**
	 * Point Page::is_edit_mode() at a front-end (not-editing) Elementor instance.
	 *
	 * Assigns \Elementor\Plugin::$instance an object that declares its own editor
	 * (reporting not-editing), so the detection is deterministic and warning-free
	 * regardless of test order. The request URI is normalized to a plain
	 * front-end path.
	 *
	 * @since 3.7.0
	 */
	private function stub_frontend_elementor(): void {
		$this->edd_require_real_elementor();

		$this->prev_request_uri = $_SERVER['REQUEST_URI'] ?? null;
		$_SERVER['REQUEST_URI'] = '/checkout/';

		\Elementor\Plugin::$instance = new class() {
			public $editor;
			public $documents;
		};
		\Elementor\Plugin::$instance->editor = new class() {
			public function is_edit_mode() {
				return false;
			}
		};
	}

	/**
	 * Restore request state and clear the front-end Elementor stub.
	 *
	 * @since 3.7.0
	 */
	private function reset_frontend_elementor(): void {
		if ( null === $this->prev_request_uri ) {
			$_SERVER['REQUEST_URI'] = '';
		} else {
			$_SERVER['REQUEST_URI'] = $this->prev_request_uri;
		}

		if ( class_exists( '\Elementor\Plugin', false ) ) {
			\Elementor\Plugin::$instance = null;
		}

		FormLayer::clear_empty_cart();
	}

	/**
	 * Create and query a page whose content carries NO wp:edd/checkout marker.
	 *
	 * A loose section widget saved outside a box writes no marker (the marker-write
	 * gate), so a loose-widget-only page carries only bare markup — no block
	 * comment. Same shape a Theme-Builder header/footer template pass produces (no
	 * checkout box anywhere in the tree).
	 *
	 * @since 3.7.0
	 *
	 * @return int The created page id.
	 */
	private function go_to_no_marker_page(): int {
		$post_id = self::factory()->post->create(
			array(
				'post_status'  => 'publish',
				'post_type'    => 'page',
				'post_content' => '<div class="wp-block-edd-checkout-cart"></div>',
			)
		);
		$this->go_to( get_permalink( $post_id ) );

		return $post_id;
	}

	/**
	 * Build a \Elementor\Plugin double for the page-data lookup.
	 *
	 * A throwaway, uninitialized REAL Plugin (private ctor bypassed, only documents
	 * wired). Mirrors Tests_Checkout_Form_Layer::make_elementor_plugin_double(). The
	 * test is skipped when real Elementor is absent (the plain stub suite).
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
	 * Install an Elementor page whose current document reports the given
	 * checkout-box tree, so Page::has_widget() resolves the box.
	 *
	 * Reuses the file-scope FakeElementorDocument/FakeElementorDocuments fakes the
	 * suppression tests already use. The cart is populated by set_up(), so the
	 * engaged render never reaches the Page::is_edit_mode() branch (its
	 * cart_is_empty() short-circuit is false), keeping this front-end and free of the
	 * Elementor editor path.
	 *
	 * @since 3.7.0
	 *
	 * @param bool $with_box Whether the page carries an edd-checkout-box (true) or a
	 *                       plain container with no checkout element (false).
	 * @return int The stubbed post id the page lookup resolves.
	 */
	private function stub_checkout_box_page( bool $with_box = true ): int {
		$post_id = self::factory()->post->create();

		$elements = $with_box
			? array(
				array(
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
				),
			)
			: array(
				array(
					'elType'   => 'container',
					'settings' => array(),
					'elements' => array(),
				),
			);

		// The page lookup reads nothing but Plugin::$instance->documents->get().
		$plugin                                   = $this->make_elementor_plugin_double();
		$plugin->documents                        = new FakeElementorDocuments();
		$plugin->documents->documents[ $post_id ] = new FakeElementorDocument( $elements );
		\Elementor\Plugin::$instance              = $plugin;

		$_REQUEST['elementor-preview'] = (string) $post_id;

		return $post_id;
	}

	/**
	 * Tear down the Elementor page installed by stub_checkout_box_page().
	 *
	 * Under --extra elementor the captured real singleton is restored (undoing the
	 * throwaway); under the stub suite the pointer is nulled as before.
	 *
	 * @since 3.7.0
	 *
	 * @return void
	 */
	private function reset_checkout_box_page(): void {
		unset( $_REQUEST['elementor-preview'] );

		if ( ! empty( $GLOBALS['edd_elementor_real_plugin'] ) ) {
			$this->edd_boot_real_elementor();

			return;
		}

		if ( class_exists( '\Elementor\Plugin', false ) ) {
			\Elementor\Plugin::$instance = null;
		}
	}
}
