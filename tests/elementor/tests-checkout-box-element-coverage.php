<?php
/**
 * Coverage tests for the EDD checkout-box element's own overrides, against REAL Elementor.
 *
 * Runs under --extra elementor: the RealElementorFixture restores the initialized
 * \Elementor\Plugin singleton so the element (which extends the native Container)
 * instantiates through the real registration path. Its identity, default data,
 * initial config, dynamic-content opt-out, real registered controls, and the three
 * print_content branches are exercised against the real Container base class.
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
 * Coverage tests for the checkout-box element's own overrides.
 *
 * @covers \EDD\Elementor\Elements\CheckoutBox
 *
 * @group elementor
 *
 * @since 3.7.0
 */
class CheckoutBoxElementCoverage extends EDD_UnitTestCase {

	use RealElementorFixture;

	/**
	 * Restore the real Elementor singleton and place a front-end request.
	 */
	public function setUp(): void {
		parent::setUp();

		$this->edd_boot_real_elementor();

		$_SERVER['REQUEST_URI'] = '/checkout/';
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
	 * The identity accessors report the box's fixed panel identity.
	 *
	 * @since 3.7.0
	 */
	public function test_identity_accessors() {
		$box = $this->make_box();

		$this->assertSame( 'edd-checkout-box', $box::get_type() );
		$this->assertSame( 'edd-checkout-box', $box->get_name() );
		$this->assertSame( 'EDD Checkout', $box->get_title() );
		$this->assertSame( 'eicon-cart-medium', $box->get_icon() );
		$this->assertContains( 'edd-checkout', $box->get_keywords() );
		$this->assertContains( 'checkout', $box->get_keywords() );
	}

	/**
	 * get_default_data backstops the css_classes setting to edd-checkout.
	 *
	 * @since 3.7.0
	 */
	public function test_get_default_data_seeds_css_classes() {
		$box = $this->make_box();

		$method = new \ReflectionMethod( $box, 'get_default_data' );
		if (PHP_VERSION_ID < 80100) {
            $method->setAccessible( true );
        }
		$data = $method->invoke( $box );

		$this->assertIsArray( $data );
		$this->assertArrayHasKey( 'settings', $data );
		$this->assertSame( 'edd-checkout', $data['settings']['css_classes'] );
	}

	/**
	 * get_initial_config forces the panel-visibility and EDD-category flags.
	 *
	 * @since 3.7.0
	 */
	public function test_get_initial_config_forces_panel_flags() {
		$box = $this->make_box();

		$method = new \ReflectionMethod( $box, 'get_initial_config' );
		if (PHP_VERSION_ID < 80100) {
            $method->setAccessible( true );
        }
		$config = $method->invoke( $box );

		$this->assertTrue( $config['show_in_panel'] );
		$this->assertSame( array( 'edd' ), $config['categories'] );
		$this->assertTrue( $config['include_in_widgets_config'] );
	}

	/**
	 * register_controls re-defaults css_classes and registers the panel layout picker.
	 *
	 * The retired `edd_layout` control is gone: the layout is chosen by the
	 * panel-hosted five-pattern picker, registered as the custom `edd-layout-picker`
	 * control inside its OWN dedicated "Checkout Layout" section, separate from the
	 * native Container controls, which all stay visible at their defaults. The
	 * controls are read off the REAL controls stack (Elementor strips the
	 * editor-presentation args label/tab/label_on/off from the persisted control in
	 * this non-editor context, so the retrievable contract is the control type,
	 * section membership, and seed defaults).
	 *
	 * @since 3.7.0
	 */
	public function test_register_controls_updates_css_classes_and_registers_panel_layout_picker() {
		$box = $this->make_box();

		// Initialize the real controls stack — this runs the element's
		// register_controls() through the native Container's control pipeline.
		$box->get_controls();

		$this->assertNull( $box->get_controls( 'edd_layout' ), 'The retired edd_layout control must not be registered.' );

		$picker = $box->get_controls( 'edd_layout_picker' );
		$this->assertIsArray( $picker, 'The panel-hosted layout picker control must be registered.' );
		$this->assertSame( 'edd-layout-picker', $picker['type'] );
		$this->assertSame( 'edd_checkout_layout', $picker['section'] );

		$section = $box->get_controls( 'edd_checkout_layout' );
		$this->assertIsArray( $section, 'The picker must live in its own dedicated "Checkout Layout" section.' );
		$this->assertSame( 'section', $section['type'] );

		$css_classes = $box->get_controls( 'css_classes' );
		$this->assertIsArray( $css_classes );
		$this->assertSame( 'edd-checkout', $css_classes['default'] );

		$this->assertNotNull(
			$box->get_controls( 'flex_direction' ),
			'The native flex_direction control must stay registered (it stays visible and its CSS drives the row patterns).'
		);

		// Each non-required section is toggled by a native Elementor `switcher` (the
		// slider), copying the monolith checkout widget's Show/Hide control shape.
		$cart     = $box->get_controls( 'edd_section_cart' );
		$discount = $box->get_controls( 'edd_section_discount_form' );

		$this->assertSame( 'switcher', $cart['type'] );
		$this->assertSame( 'switcher', $discount['type'] );
		$this->assertSame( 'yes', $cart['default'] );
		$this->assertSame( '', $discount['default'] );
	}

	/**
	 * is_dynamic_content opts the box out of Elementor's element cache.
	 *
	 * @since 3.7.0
	 */
	public function test_is_dynamic_content_true() {
		$box = $this->make_box();

		$method = new \ReflectionMethod( $box, 'is_dynamic_content' );
		if (PHP_VERSION_ID < 80100) {
            $method->setAccessible( true );
        }

		$this->assertTrue( $method->invoke( $box ) );
	}

	/**
	 * The engaged box's print_content fires its own slot hooks around its children.
	 *
	 * print_content fires the box top/bottom hooks and the CheckoutFormLayer subscriber renders the
	 * account line and form-level content in response, so that content lands in the same container as
	 * the sections. Asserts each box hook fires once, carrying the box's element id.
	 *
	 * @since 3.7.0
	 */
	public function test_print_content_engaged_fires_the_box_slot_hooks_around_its_children() {
		$box = $this->make_box_with_id( 'covBoxEngaged' );

		FormLayer::reset_engaged();
		FormLayer::clear_empty_cart();
		FormLayer::mark_engaged( 'covBoxEngaged' );

		$top     = 0;
		$bottom  = 0;
		$top_ids = array();
		add_action(
			'edd_elementor_checkout_box_top',
			function ( $element_id ) use ( &$top, &$top_ids ) {
				++$top;
				$top_ids[] = $element_id;
			}
		);
		add_action( 'edd_elementor_checkout_box_bottom', function () use ( &$bottom ) { ++$bottom; } );

		$method = new \ReflectionMethod( $box, 'print_content' );
		if (PHP_VERSION_ID < 80100) {
            $method->setAccessible( true );
        }

		ob_start();
		$method->invoke( $box );
		ob_get_clean();

		$this->assertSame( 1, $top, 'The engaged box print_content must fire edd_elementor_checkout_box_top exactly once.' );
		$this->assertSame( 1, $bottom, 'The engaged box print_content must fire edd_elementor_checkout_box_bottom exactly once.' );
		$this->assertSame( array( 'covBoxEngaged' ), $top_ids, 'The box slot hook must pass the engaged box element id.' );

		FormLayer::reset_engaged();
	}

	/**
	 * Whatever a slot hook prints is wrapped so it cannot take width from the columns.
	 *
	 * Slot output is a sibling of the box's columns, so in a row layout it competes with them for
	 * width. The wrapper makes it span the row instead, and is emitted only when the hook printed
	 * something.
	 *
	 * @since 3.7.0
	 */
	public function test_print_content_engaged_wraps_slot_output_and_skips_empty_slots() {
		$box = $this->make_box_with_id( 'covBoxSlots' );

		FormLayer::reset_engaged();
		FormLayer::clear_empty_cart();
		FormLayer::mark_engaged( 'covBoxSlots' );

		add_action( 'edd_elementor_checkout_box_top', function () { echo '<p class="cov-slot-top">top</p>'; } );

		$method = new \ReflectionMethod( $box, 'print_content' );
		if (PHP_VERSION_ID < 80100) {
            $method->setAccessible( true );
        }

		ob_start();
		$method->invoke( $box );
		$output = ob_get_clean();

		$this->assertStringContainsString(
			'edd-checkout-box__slot edd-checkout-box__slot--top',
			$output,
			'Slot output must be wrapped so it spans the box row instead of becoming a column.'
		);
		$this->assertStringContainsString( 'cov-slot-top', $output, 'The wrapper must contain what the hook printed.' );
		$this->assertStringNotContainsString(
			'edd-checkout-box__slot--bottom',
			$output,
			'A slot that printed nothing must emit no wrapper, so it adds no element and no gap.'
		);

		FormLayer::reset_engaged();
	}

	/**
	 * The empty-cart notice owner emits the empty-cart action instead of a form.
	 *
	 * @since 3.7.0
	 */
	public function test_print_content_empty_cart_owner_fires_cart_empty() {
		$box = $this->make_box_with_id( 'covBoxEmpty' );

		FormLayer::reset_engaged();
		FormLayer::mark_empty_cart( 'covBoxEmpty' );

		$fired = 0;
		add_action( 'edd_cart_empty', function () use ( &$fired ) { ++$fired; } );

		$method = new \ReflectionMethod( $box, 'print_content' );
		if (PHP_VERSION_ID < 80100) {
            $method->setAccessible( true );
        }

		ob_start();
		$method->invoke( $box );
		ob_get_clean();

		$this->assertSame( 1, $fired, 'The empty-cart notice owner must fire edd_cart_empty.' );

		FormLayer::clear_empty_cart();
	}

	/**
	 * A non-engaged, non-owner box on the front end renders nothing.
	 *
	 * @since 3.7.0
	 */
	public function test_print_content_duplicate_box_renders_nothing() {
		$box = $this->make_box_with_id( 'covBoxDuplicate' );

		FormLayer::reset_engaged();
		FormLayer::clear_empty_cart();

		$method = new \ReflectionMethod( $box, 'print_content' );
		if (PHP_VERSION_ID < 80100) {
            $method->setAccessible( true );
        }

		ob_start();
		$method->invoke( $box );
		$output = ob_get_clean();

		$this->assertSame( '', $output, 'A second/duplicate checkout box must render nothing.' );
	}

	/**
	 * Build a registered checkout-box element instance against the real Container.
	 *
	 * @return \EDD\Elementor\Elements\CheckoutBox
	 */
	private function make_box() {
		$manager = new class() {
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
		};

		( new CheckoutBoxSubscriber() )->register_element( $manager );

		return $manager->registered[0];
	}

	/**
	 * Build a full checkout-box element instance carrying the given id.
	 *
	 * The print_content branches key on get_id(), and parent::print_content()
	 * iterates get_children() (which reads the element's `elements` data). A full
	 * data instance (non-empty $data) records the id via Controls_Stack::init() and
	 * gives get_children() a real, empty children array — a bare type instance has
	 * no data at all, so get_children() would dereference null. The element class is
	 * loaded/registered by make_box() first (the subscriber requires its file).
	 *
	 * @param string $id The id get_id() should report.
	 * @return \EDD\Elementor\Elements\CheckoutBox
	 */
	private function make_box_with_id( string $id ) {
		$this->make_box();

		return new \EDD\Elementor\Elements\CheckoutBox(
			array(
				'id'       => $id,
				'elType'   => 'edd-checkout-box',
				'settings' => array(),
				'elements' => array(),
			)
		);
	}
}
