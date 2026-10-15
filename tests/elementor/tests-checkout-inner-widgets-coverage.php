<?php
/**
 * Coverage tests for the four EDD checkout section widgets, against REAL Elementor.
 *
 * Runs under --extra elementor: the RealElementorWidgetFixture restores the
 * fully-initialized \Elementor\Plugin singleton and builds each section widget as a
 * real \Elementor\Widget_Base instance (full element data, real controls stack),
 * instead of the retired stub Widget_Base double. Each widget's identity, its real
 * registered controls, its front-end/editor render, and the plain-content
 * marker-write gate are exercised through the real widget/controls pipeline.
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
use EDD\Elementor\Widgets\CheckoutInner\Cart;
use EDD\Elementor\Widgets\CheckoutInner\PersonalInfo;
use EDD\Elementor\Widgets\CheckoutInner\PaymentInfo;
use EDD\Elementor\Widgets\CheckoutInner\DiscountForm;
use EDD\Tests\Elementor\Support\RealElementorWidgetFixture;

/**
 * Coverage tests for the four checkout section widgets.
 *
 * @covers \EDD\Elementor\Widgets\CheckoutInner\Cart
 * @covers \EDD\Elementor\Widgets\CheckoutInner\PersonalInfo
 * @covers \EDD\Elementor\Widgets\CheckoutInner\PaymentInfo
 * @covers \EDD\Elementor\Widgets\CheckoutInner\DiscountForm
 *
 * @group elementor
 *
 * @since 3.7.0
 */
class CheckoutInnerWidgetsCoverage extends EDD_UnitTestCase {

	use RealElementorWidgetFixture;

	/**
	 * A simple download so the section renderers have cart contents.
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
	 * Remove the shared download after the class runs.
	 */
	public static function tear_down_after_class() {
		EDD_Helper_Download::delete_download( self::$download->ID );
		parent::tear_down_after_class();
	}

	/**
	 * Restore the real Elementor singleton, populate the cart, and engage a box.
	 */
	public function setUp(): void {
		parent::setUp();

		$this->edd_boot_real_elementor();

		edd_empty_cart();
		edd_add_to_cart( self::$download->ID );

		FormLayer::reset_engaged();
		FormLayer::clear_rendered_sections();

		// These coverage tests exercise the in-box render contract, so engage a
		// checkout box: with the render-inert backstop a section widget
		// renders its markup only while a box is engaged. The negative
		// (outside-a-box) contract is asserted in its own test, which resets this.
		FormLayer::mark_engaged( 'coverageBox' );

		$this->edd_clear_edit_mode();
	}

	/**
	 * Clear cart, render-pass, and editor state between tests.
	 */
	public function tear_down() {
		// Restore the real singleton before EDD teardown deletes posts: its
		// delete_post hook dereferences Plugin::$instance->documents (Elementor's
		// kit manager), and the plain-content gate tests swap in a throwaway
		// documents-only Plugin.
		if ( ! empty( $GLOBALS['edd_elementor_real_plugin'] ) ) {
			\Elementor\Plugin::$instance = $GLOBALS['edd_elementor_real_plugin'];
		}

		edd_empty_cart();
		FormLayer::reset_engaged();
		FormLayer::clear_rendered_sections();

		unset( $_GET['edd_blocks_is_block_editor'] );

		parent::tear_down();
	}

	/**
	 * The Cart widget reports its identity, style deps, controls, and renders.
	 *
	 * @since 3.7.0
	 */
	public function test_cart_widget() {
		$widget = $this->edd_make_widget( Cart::class );

		$this->assertSame( 'edd-checkout-cart', $widget->get_name() );
		$this->assertSame( 'EDD Checkout Cart', $widget->get_title() );
		$this->assertSame( 'dashicons dashicons-cart', $widget->get_icon() );
		$this->assertContains( 'cart', $widget->get_keywords() );
		$this->assertSame( array( 'edd' ), $widget->get_categories() );
		$this->assertSame( array( 'edd-checkout-style', 'edd-checkout-cart-style' ), $widget->get_style_depends() );
		$this->assertFalse( $widget->has_widget_inner_wrapper() );

		$this->assertNotEmpty(
			$this->edd_widget_section_ids( $widget ),
			'Cart controls must register at least one section on the real controls stack.'
		);

		$output = $this->render( $widget );
		$this->assertIsString( $output, 'The cart widget must render without fataling on the front end.' );
	}

	/**
	 * The Personal Info widget reports its identity, controls, and renders.
	 *
	 * @since 3.7.0
	 */
	public function test_personal_info_widget() {
		$widget = $this->edd_make_widget( PersonalInfo::class );

		$this->assertSame( 'edd-checkout-personal-info', $widget->get_name() );
		$this->assertSame( 'EDD Checkout Personal Info', $widget->get_title() );
		$this->assertSame( 'dashicons dashicons-admin-users', $widget->get_icon() );
		$this->assertContains( 'email', $widget->get_keywords() );
		$this->assertSame( array( 'edd-checkout-style', 'edd-checkout-personal-info-style' ), $widget->get_style_depends() );

		$this->assertNotEmpty(
			$this->edd_widget_section_ids( $widget ),
			'Personal Info controls must register at least one section on the real controls stack.'
		);

		$output = $this->render( $widget );
		$this->assertIsString( $output, 'The personal-info widget must render without fataling on the front end.' );
	}

	/**
	 * A logged-in customer's personal-info widget does NOT emit the account block.
	 *
	 * The logged-in "Account Information" block now renders at the top of the
	 * purchase form from the CheckoutFormLayer subscriber (mirroring the block
	 * checkout topology), NOT nested inside the personal-info widget. The widget
	 * must therefore emit only the shared user-details markup and never the
	 * .edd-blocks__logged-in account block, so the composed checkout shows exactly
	 * one account block (from the subscriber) rather than a duplicate.
	 *
	 * @since 3.7.0
	 */
	public function test_personal_info_widget_omits_logged_in_account_block() {
		$user_id = self::factory()->user->create(
			array(
				'user_email' => 'logged-in-customer@example.test',
				'first_name' => 'Ada',
				'last_name'  => 'Lovelace',
			)
		);
		wp_set_current_user( $user_id );
		$this->reset_attribute_cache();

		$widget = $this->edd_make_widget( PersonalInfo::class );
		$output = $this->render( $widget );

		wp_set_current_user( 0 );
		$this->reset_attribute_cache();

		$this->assertStringContainsString(
			'edd-blocks__user-details',
			$output,
			'The personal-info widget must still emit the shared user-details markup for a logged-in customer.'
		);
		$this->assertStringNotContainsString(
			'edd-blocks__logged-in',
			$output,
			'The personal-info widget must NOT emit the account block; the form-layer subscriber renders it at the top of the form.'
		);
	}

	/**
	 * A guest's personal-info widget renders no account-information block.
	 *
	 * The logged-in account block is gated on the logged_in attribute, so a guest
	 * checkout is untouched and no duplicate personal-info markup is produced.
	 *
	 * @since 3.7.0
	 */
	public function test_personal_info_widget_guest_has_no_account_block() {
		wp_set_current_user( 0 );
		$this->reset_attribute_cache();

		$widget = $this->edd_make_widget( PersonalInfo::class );
		$output = $this->render( $widget );

		$this->assertStringNotContainsString(
			'edd-blocks__logged-in',
			$output,
			'A guest checkout must not render the logged-in account block.'
		);
	}

	/**
	 * The Payment Info widget reports identity, deps, controls, and renders.
	 *
	 * @since 3.7.0
	 */
	public function test_payment_info_widget() {
		$widget = $this->edd_make_widget( PaymentInfo::class );

		$this->assertSame( 'edd-checkout-payment-info', $widget->get_name() );
		$this->assertSame( 'EDD Checkout Payment Info', $widget->get_title() );
		$this->assertSame( 'dashicons dashicons-money-alt', $widget->get_icon() );
		$this->assertContains( 'gateway', $widget->get_keywords() );
		$this->assertSame( array( 'edd-checkout-style', 'edd-checkout-payment-info-style' ), $widget->get_style_depends() );

		// On the front end the gateway scripts are declared as dependencies.
		$this->assertSame( array( 'edd-checkout-global', 'edd-ajax' ), $widget->get_script_depends() );

		$this->assertNotEmpty(
			$this->edd_widget_section_ids( $widget ),
			'Payment Info controls must register at least one section on the real controls stack.'
		);

		$output = $this->render( $widget );
		$this->assertNotSame( '', $output, 'The payment-info widget must render markup on the front end.' );
	}

	/**
	 * In the Elementor editor the payment-info widget drops its front-end scripts.
	 *
	 * @since 3.7.0
	 */
	public function test_payment_info_script_depends_empty_in_editor() {
		$this->edd_force_edit_mode();

		$widget = $this->edd_make_widget( PaymentInfo::class );

		$this->assertSame( array(), $widget->get_script_depends(), 'The editor preview must not load front-end checkout scripts.' );
	}

	/**
	 * The Discount Form widget reports identity and renders.
	 *
	 * The widget is control-less: its presence in the checkout box is the
	 * visibility gate, so a present widget renders the discount form without a
	 * per-widget toggle.
	 *
	 * @since 3.7.0
	 */
	public function test_discount_form_widget_renders() {
		$widget = $this->edd_make_widget( DiscountForm::class );

		$this->assertSame( 'edd-checkout-discount-form', $widget->get_name() );
		$this->assertSame( 'EDD Checkout Discount Form', $widget->get_title() );
		$this->assertSame( 'dashicons dashicons-tag', $widget->get_icon() );
		$this->assertContains( 'coupon', $widget->get_keywords() );
		$this->assertSame( array( 'edd-checkout-style', 'edd-checkout-discount-form-style' ), $widget->get_style_depends() );

		// Rendering must not fatal; output depends on active discounts/cart total.
		$this->render( $widget );

		$this->assertTrue( true );
	}

	/**
	 * Each section widget's render_plain_content() emits the shared outer
	 * edd/checkout block marker plus its own inner marker.
	 *
	 * Elementor's plain-text save pipeline writes this output to post_content, and
	 * has_block()/Attributes::get() read it. With no current Elementor document
	 * resolvable in the suite, the outer attributes fall back to the documented
	 * defaults (layout empty, show_discount_form true, thumbnail_width 25).
	 *
	 * @since 3.7.0
	 *
	 * @covers \EDD\Elementor\Widgets\Base::render_plain_content
	 * @covers \EDD\Elementor\Widgets\Base::get_inner_block_name
	 * @covers \EDD\Elementor\Widgets\Base::get_checkout_block_attributes
	 */
	public function test_render_plain_content_emits_outer_and_inner_markers() {
		$widgets = array(
			'edd/checkout-cart'          => $this->edd_make_widget( Cart::class, 'stub-widget-id' ),
			'edd/checkout-personal-info' => $this->edd_make_widget( PersonalInfo::class, 'stub-widget-id' ),
			'edd/checkout-payment-info'  => $this->edd_make_widget( PaymentInfo::class, 'stub-widget-id' ),
			'edd/checkout-discount-form' => $this->edd_make_widget( DiscountForm::class, 'stub-widget-id' ),
		);

		// The marker-write gate resolves box-membership from the current document:
		// place the widget's own id inside an edd-checkout-box so the gate passes
		// and both markers are emitted (the in-box save path).
		$this->edd_install_current_document(
			array(
				array(
					'id'       => 'boxSave',
					'elType'   => 'edd-checkout-box',
					'settings' => array(),
					'elements' => array(
						array(
							'id'         => 'stub-widget-id',
							'elType'     => 'widget',
							'widgetType' => 'edd-checkout-cart',
							'settings'   => array(),
							'elements'   => array(),
						),
					),
				),
			)
		);

		foreach ( $widgets as $inner_marker => $widget ) {
			ob_start();
			$widget->render_plain_content();
			$output = (string) ob_get_clean();

			$this->assertStringContainsString(
				'<!-- wp:edd/checkout {"layout":"","show_discount_form":true,"thumbnail_width":25} /-->',
				$output,
				"{$widget->get_name()} must emit the shared outer edd/checkout marker with the default attributes."
			);
			$this->assertStringContainsString(
				'<!-- wp:' . $inner_marker . ' /-->',
				$output,
				"{$widget->get_name()} must emit its own {$inner_marker} inner marker."
			);
		}
	}

	/**
	 * Marker-write gate: a section widget saved OUTSIDE an
	 * edd-checkout-box emits NO markers — the negative test that IS the crash fix.
	 *
	 * The widget's own id resolves to a node at the document ROOT (not inside a
	 * box), so is_within_checkout_box() is false and render_plain_content() echoes
	 * nothing. With no wp:edd/checkout marker written, has_block() stays false and
	 * the loose widget is never treated as a live checkout (the loose-widget
	 * null-form-gateway crash). Emptiness is asserted explicitly — not merely "no error".
	 *
	 * @since 3.7.0
	 *
	 * @covers \EDD\Elementor\Widgets\Base::render_plain_content
	 * @covers \EDD\Elementor\Widgets\Base::is_within_checkout_box
	 */
	public function test_render_plain_content_outside_box_emits_no_marker() {
		$widgets = array(
			$this->edd_make_widget( Cart::class, 'stub-widget-id' ),
			$this->edd_make_widget( PersonalInfo::class, 'stub-widget-id' ),
			$this->edd_make_widget( PaymentInfo::class, 'stub-widget-id' ),
			$this->edd_make_widget( DiscountForm::class, 'stub-widget-id' ),
		);

		// The widget's id sits at the document root, with no edd-checkout-box ancestor.
		$this->edd_install_current_document(
			array(
				array(
					'id'         => 'stub-widget-id',
					'elType'     => 'widget',
					'widgetType' => 'edd-checkout-payment-info',
					'settings'   => array(),
					'elements'   => array(),
				),
			)
		);

		foreach ( $widgets as $widget ) {
			ob_start();
			$widget->render_plain_content();
			$output = (string) ob_get_clean();

			$this->assertSame(
				'',
				$output,
				"{$widget->get_name()} outside a checkout box must emit no plain-content output at all."
			);
			$this->assertStringNotContainsString(
				'wp:edd/checkout',
				$output,
				"{$widget->get_name()} outside a checkout box must write NO wp:edd/checkout marker."
			);
		}
	}

	/**
	 * Standing marker-write regression guard: the gate keys on BOX
	 * MEMBERSHIP, not on the widget's nesting depth or its position at the document
	 * root.
	 *
	 * Distinct from the two P1 landing tests it complements — the direct-child
	 * positive (test_render_plain_content_emits_outer_and_inner_markers) and the
	 * document-root negative (test_render_plain_content_outside_box_emits_no_marker).
	 * This locks the gate's real contract in for the long term against a future
	 * refactor that (a) stops propagating the box flag down to a nested section, or
	 * (b) starts treating any wrapping container as a box:
	 *
	 *  - a section nested one level deep inside a plain container that is ITSELF
	 *    inside an edd-checkout-box still writes both markers (the box flag
	 *    propagates through the whole subtree); and
	 *  - the SAME section nested inside a plain container that is NOT inside a box
	 *    writes nothing (a non-box wrapper never engages the gate).
	 *
	 * @since 3.7.0
	 *
	 * @covers \EDD\Elementor\Widgets\Base::render_plain_content
	 * @covers \EDD\Elementor\Widgets\Base::is_within_checkout_box
	 */
	public function test_render_plain_content_gate_is_box_membership_scoped_not_depth_scoped() {
		// (a) The widget sits one level deep, inside a plain container that is a child
		// of an edd-checkout-box — the box flag must propagate down to it.
		$this->edd_install_current_document(
			array(
				array(
					'id'       => 'boxDeep',
					'elType'   => 'edd-checkout-box',
					'settings' => array(),
					'elements' => array(
						array(
							'id'       => 'innerColumn',
							'elType'   => 'container',
							'settings' => array(),
							'elements' => array(
								array(
									'id'         => 'stub-widget-id',
									'elType'     => 'widget',
									'widgetType' => 'edd-checkout-cart',
									'settings'   => array(),
									'elements'   => array(),
								),
							),
						),
					),
				),
			)
		);

		ob_start();
		$this->edd_make_widget( Cart::class, 'stub-widget-id' )->render_plain_content();
		$in_box = (string) ob_get_clean();

		$this->assertStringContainsString(
			'<!-- wp:edd/checkout ',
			$in_box,
			'A section nested inside a container that is inside a box must still write the outer marker — box membership propagates through the subtree, it is not limited to direct children.'
		);
		$this->assertStringContainsString(
			'<!-- wp:edd/checkout-cart /-->',
			$in_box,
			'A section nested inside a container that is inside a box must still write its own inner marker.'
		);

		// (b) The SAME widget, same nesting depth, but the wrapping container is NOT
		// inside a box — the gate must stay closed.
		$this->edd_install_current_document(
			array(
				array(
					'id'       => 'looseColumn',
					'elType'   => 'container',
					'settings' => array(),
					'elements' => array(
						array(
							'id'         => 'stub-widget-id',
							'elType'     => 'widget',
							'widgetType' => 'edd-checkout-cart',
							'settings'   => array(),
							'elements'   => array(),
						),
					),
				),
			)
		);

		ob_start();
		$this->edd_make_widget( Cart::class, 'stub-widget-id' )->render_plain_content();
		$outside_box = (string) ob_get_clean();

		$this->assertSame(
			'',
			$outside_box,
			'A section nested inside a NON-box container must write no marker: the gate is scoped to box membership, not nesting depth.'
		);
	}

	/**
	 * All four checkout section widgets are hidden from the Add-widget panel.
	 *
	 * show_in_panel() must return false so a loose section cannot be dropped onto a
	 * page from the panel (the first layer of the loose-widget null-form-gateway
	 * crash defense). The box element itself stays panel-visible; that is pinned by
	 * CheckoutBoxElementCoverage::test_get_initial_config_forces_panel_flags.
	 *
	 * @since 3.7.0
	 */
	public function test_section_widgets_hidden_from_panel() {
		$widgets = array(
			$this->edd_make_widget( Cart::class ),
			$this->edd_make_widget( PersonalInfo::class ),
			$this->edd_make_widget( PaymentInfo::class ),
			$this->edd_make_widget( DiscountForm::class ),
		);

		foreach ( $widgets as $widget ) {
			$this->assertFalse(
				$widget->show_in_panel(),
				"{$widget->get_name()} must be hidden from the Add-widget panel (show_in_panel() === false)."
			);
		}
	}

	/**
	 * Render-inert backstop: a section widget rendered on the front end with NO
	 * checkout box engaged renders nothing.
	 *
	 * Resets the box-engagement state set up by setUp() so is_box_engaged() is
	 * false; with edit mode off (front end), every section widget's render() must
	 * short-circuit and emit an empty string — no gateway markup, no
	 * #edd_purchase_form_wrap. Covers the loose/standalone widget vectors the panel
	 * hide cannot close (copy/paste, import, pre-existing page, drag-out-of-box).
	 *
	 * @since 3.7.0
	 */
	public function test_render_inert_widget_outside_engaged_box_renders_nothing() {
		FormLayer::reset_engaged();
		FormLayer::clear_rendered_sections();

		$widgets = array(
			$this->edd_make_widget( Cart::class ),
			$this->edd_make_widget( PersonalInfo::class ),
			$this->edd_make_widget( PaymentInfo::class ),
			$this->edd_make_widget( DiscountForm::class ),
		);

		foreach ( $widgets as $widget ) {
			$this->assertSame(
				'',
				$this->render( $widget ),
				"{$widget->get_name()} rendered outside an engaged checkout box must render nothing (render-inert backstop)."
			);
		}
	}

	/**
	 * In the Elementor editor the payment-info widget forces the gateway selector to
	 * preview, then removes the edit-mode filters so nothing leaks to a real request.
	 *
	 * With no live cart the shared renderer hides the gateway selector
	 * (#edd_payment_mode_select) because edd_show_gateways() is false and
	 * edd_get_cart_total() is 0. render() adds the edd_show_gateways / edd_get_cart_total
	 * edit-mode filters so the selector previews, then removes them immediately after
	 * the delegated render. This asserts the selector rendered (filters active DURING
	 * render) and that the edd_show_gateways force is gone AFTER render (no leak).
	 *
	 * @since 3.7.0
	 *
	 * @covers \EDD\Elementor\Widgets\CheckoutInner\PaymentInfo::render
	 */
	public function test_payment_info_editor_forces_gateway_selector_and_cleans_up() {
		$this->edd_force_edit_mode();

		// Empty the cart so only the edit-mode force can surface the gateway selector.
		edd_empty_cart();
		FormLayer::clear_rendered_sections();

		remove_filter( 'edd_show_gateways', '__return_true' );

		$widget = $this->edd_make_widget( PaymentInfo::class );
		$output = $this->render( $widget );

		$this->assertStringContainsString(
			'edd_payment_mode_select',
			$output,
			'In the editor the payment-info widget must force the gateway selector to preview even with no live cart.'
		);
		$this->assertFalse(
			has_filter( 'edd_show_gateways', '__return_true' ),
			'The editor-only edd_show_gateways force must be removed after the delegated render (no leak to the front end).'
		);

		unset( $_GET['edd_blocks_is_block_editor'] );
	}

	/**
	 * Outside the editor the payment-info widget never registers the edit-mode
	 * gateway filters (no leak).
	 *
	 * setUp() engages a box so the front-end render is not inert; the render must
	 * still leave no edd_show_gateways force behind.
	 *
	 * @since 3.7.0
	 *
	 * @covers \EDD\Elementor\Widgets\CheckoutInner\PaymentInfo::render
	 */
	public function test_payment_info_front_end_registers_no_gateway_force() {
		remove_filter( 'edd_show_gateways', '__return_true' );

		$widget = $this->edd_make_widget( PaymentInfo::class );
		$this->render( $widget );

		$this->assertFalse(
			has_filter( 'edd_show_gateways', '__return_true' ),
			'On the front end the payment-info widget must not register the edit-mode gateway force filter.'
		);
	}

	/**
	 * In the editor with Preview as Guest on in the document, the shared editor-preview
	 * setup adds the edd_blocks_doing_guest_preview filter so the preview renders as a guest.
	 *
	 * The setting is read from the checkout's Personal Info widget in the page's Elementor
	 * data (EditorPreview::checkout_previews_as_guest via Page::get_widget_data), not from the
	 * rendering widget's own instance, so the document must carry it.
	 *
	 * @since 3.7.0
	 *
	 * @covers \EDD\Elementor\Widgets\CheckoutInner\Concerns\EditorPreview::maybe_setup_editor_preview
	 * @covers \EDD\Elementor\Widgets\CheckoutInner\Concerns\EditorPreview::checkout_previews_as_guest
	 */
	public function test_editor_guest_preview_adds_filter_from_document() {
		$this->edd_force_edit_mode();
		$this->install_checkout_preview_document( 'yes' );

		remove_filter( 'edd_blocks_doing_guest_preview', '__return_true' );

		$this->render( $this->edd_make_widget( PersonalInfo::class, 'piWidget' ) );

		$this->assertNotFalse(
			has_filter( 'edd_blocks_doing_guest_preview', '__return_true' ),
			'In the editor with Preview as Guest on, the shared preview setup must add the guest-preview filter.'
		);

		// Clean up so the guest-preview filter does not leak into later tests.
		remove_filter( 'edd_blocks_doing_guest_preview', '__return_true' );
		unset( $_GET['edd_blocks_is_block_editor'] );
	}

	/**
	 * Preview as Guest still applies when another section renders before Personal Info.
	 *
	 * Regression for the editor-preview guest bug: Attributes::get() memoizes the logged_in
	 * flag for the whole render pass, and in the editor's single-request render the Cart
	 * section calls it first. When the guest filter lived on PersonalInfo::render() it landed
	 * after Cart had already frozen logged_in as true, so the preview showed the logged-in
	 * view. The filter now applies in the shared setup every section runs first, so rendering
	 * Cart first must still add the filter AND leave logged_in false.
	 *
	 * @since 3.7.0
	 *
	 * @covers \EDD\Elementor\Widgets\CheckoutInner\Concerns\EditorPreview::maybe_setup_editor_preview
	 */
	public function test_editor_guest_preview_applies_when_cart_renders_first() {
		$this->edd_force_edit_mode();
		$this->install_checkout_preview_document( 'yes', true );

		remove_filter( 'edd_blocks_doing_guest_preview', '__return_true' );
		$this->reset_attribute_cache();

		// Render the Cart section first — the section that froze logged_in before the fix.
		$this->render( $this->edd_make_widget( Cart::class, 'cartWidget' ) );

		$this->assertNotFalse(
			has_filter( 'edd_blocks_doing_guest_preview', '__return_true' ),
			'Rendering Cart first must still add the guest-preview filter, before any Personal Info render.'
		);

		$attributes = \EDD\Blocks\Checkout\Attributes::get();
		$this->assertEmpty(
			$attributes['logged_in'],
			'With Preview as Guest on, logged_in must be false even though Cart rendered first.'
		);

		remove_filter( 'edd_blocks_doing_guest_preview', '__return_true' );
		unset( $_GET['edd_blocks_is_block_editor'] );
	}

	/**
	 * In the editor with Preview as Guest OFF on the Personal Info widget, no guest-preview filter is added.
	 *
	 * When the render IS the Personal Info section, checkout_previews_as_guest() reads the widget's
	 * LIVE editor setting from $this (honoring an unsaved toggle), the same way the monolith Checkout
	 * widget does. Against the real controls stack the `preview_as_guest` switcher DEFAULTS to `yes`
	 * (Widgets\Config\Checkout\General), so "off" is the toggle explicitly set to `''` on the widget.
	 * An absent setting would fall back to the default (on); an explicit `''` is off.
	 *
	 * @since 3.7.0
	 *
	 * @covers \EDD\Elementor\Widgets\CheckoutInner\Concerns\EditorPreview::checkout_previews_as_guest
	 */
	public function test_editor_guest_preview_absent_without_document_setting() {
		$this->edd_force_edit_mode();

		remove_filter( 'edd_blocks_doing_guest_preview', '__return_true' );

		$this->render(
			$this->edd_make_widget( PersonalInfo::class, 'piWidget', array( 'preview_as_guest' => '' ) )
		);

		$this->assertFalse(
			has_filter( 'edd_blocks_doing_guest_preview', '__return_true' ),
			'With Preview as Guest off on the Personal Info widget, the guest-preview filter must not be added.'
		);

		unset( $_GET['edd_blocks_is_block_editor'] );
	}

	/**
	 * On the front end the guest-preview filter is never added, even with Preview as Guest on.
	 *
	 * The shared setup early-returns outside edit mode, so the setting is editor-only.
	 *
	 * @since 3.7.0
	 *
	 * @covers \EDD\Elementor\Widgets\CheckoutInner\Concerns\EditorPreview::maybe_setup_editor_preview
	 */
	public function test_guest_preview_absent_on_front_end() {
		remove_filter( 'edd_blocks_doing_guest_preview', '__return_true' );

		$this->render(
			$this->edd_make_widget( PersonalInfo::class, 'piWidget', array( 'preview_as_guest' => 'yes' ) )
		);

		$this->assertFalse(
			has_filter( 'edd_blocks_doing_guest_preview', '__return_true' ),
			'On the front end the guest-preview filter must never be added, even with the setting on.'
		);
	}

	/**
	 * Attach an Elementor document to the editor-preview post whose checkout box carries a
	 * Personal Info section with the given Preview as Guest setting.
	 *
	 * The guest-preview decision reads the setting from the document via Page::get_widget_data,
	 * so it must live in the page's _elementor_data. Optionally places the Cart section before
	 * Personal Info to exercise the render-order regression.
	 *
	 * @since 3.7.0
	 *
	 * @param string $preview_as_guest The Personal Info preview_as_guest value ('yes' or '').
	 * @param bool   $cart_first       Whether to place the Cart section before Personal Info.
	 * @return void
	 */
	private function install_checkout_preview_document( string $preview_as_guest, bool $cart_first = false ): void {
		$personal_info = array(
			'id'         => 'piWidget',
			'elType'     => 'widget',
			'widgetType' => 'edd-checkout-personal-info',
			'settings'   => array( 'preview_as_guest' => $preview_as_guest ),
			'elements'   => array(),
		);
		$cart          = array(
			'id'         => 'cartWidget',
			'elType'     => 'widget',
			'widgetType' => 'edd-checkout-cart',
			'settings'   => array(),
			'elements'   => array(),
		);

		$children = $cart_first ? array( $cart, $personal_info ) : array( $personal_info, $cart );

		$tree = array(
			array(
				'id'       => 'boxWidget',
				'elType'   => 'edd-checkout-box',
				'settings' => array(),
				'elements' => $children,
			),
		);

		$post_id = (int) $this->edd_edit_mode_post;
		update_post_meta( $post_id, '_elementor_edit_mode', 'builder' );
		update_post_meta( $post_id, '_elementor_template_type', 'wp-page' );
		update_post_meta( $post_id, '_elementor_data', wp_slash( wp_json_encode( $tree ) ) );
	}

	/**
	 * Invoke a widget's protected render() and return the buffered output.
	 *
	 * @param object $widget The widget instance.
	 * @return string
	 */
	private function render( $widget ): string {
		$method = new \ReflectionMethod( $widget, 'render' );
		if (PHP_VERSION_ID < 80100) {
            $method->setAccessible( true );
        }

		ob_start();
		$method->invoke( $widget );

		return (string) ob_get_clean();
	}

	/**
	 * Reset the checkout Attributes request cache.
	 *
	 * Attributes::get() memoises its result for the request, so a change to the
	 * login state is only reflected on the next call once the cache is cleared.
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
}
