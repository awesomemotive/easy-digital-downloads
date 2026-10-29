<?php
/**
 * Render-callback HTML-assertion tests for the checkout inner blocks.
 *
 * Render-callback coverage for the checkout inner blocks (cart, personal-info,
 * payment-info, discount-form). Each test invokes
 * the block's render callback with a representative cart/state and asserts the
 * output contains the block's stable wrapper/markers and does not fatal.
 *
 * @package EDD\Tests\Blocks\Checkout
 */

namespace EDD\Tests\Blocks\Checkout;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;
use EDD\Tests\Helpers\EDD_Helper_Download;
use EDD\Tests\Helpers\EDD_Helper_Discount;
use EDD\Blocks\Checkout\Cart as CartBlock;
use EDD\Blocks\Checkout\PersonalInfo as PersonalInfoBlock;
use EDD\Blocks\Checkout\PaymentInfo as PaymentInfoBlock;
use EDD\Blocks\Checkout\DiscountForm as DiscountFormBlock;

/**
 * @group blocks
 */
class CheckoutRender extends EDD_UnitTestCase {

	/**
	 * A simple download added to the cart for render paths that require contents.
	 *
	 * @var \WP_Post
	 */
	private static $download;

	/**
	 * A percentage discount ID so the discount-form render path is active.
	 *
	 * @var int
	 */
	private static $discount_id;

	public static function setUpBeforeClass(): void {
		parent::setUpBeforeClass();

		self::$download    = EDD_Helper_Download::create_simple_download();
		self::$discount_id = EDD_Helper_Discount::create_simple_percent_discount();
	}

	public static function tearDownAfterClass(): void {
		EDD_Helper_Download::delete_download( self::$download->ID );
		EDD_Helper_Discount::delete_discount( self::$discount_id );

		parent::tearDownAfterClass();
	}

	/**
	 * Callbacks that restore what a test changed, run in reverse order by tearDown().
	 *
	 * @var callable[]
	 */
	private $restorers = array();

	public function setUp(): void {
		parent::setUp();

		// The dedupe flags are statics, so they persist across tests.
		\EDD\Blocks\Utility::reset_checkout_form_top();
		\EDD\Checkout\Address::reset_billing_top();

		edd_empty_cart();
		edd_add_to_cart( self::$download->ID );
	}

	public function tearDown(): void {
		edd_empty_cart();

		// Reset the editor-context simulation.
		unset( $_GET['edd_blocks_is_block_editor'] );

		// The dedupe flags are statics that no teardown restores, so clear them per test.
		\EDD\Blocks\Utility::reset_checkout_form_top();
		\EDD\Checkout\Address::reset_billing_top();

		// Put back whatever the billing address tests changed.
		foreach ( array_reverse( $this->restorers ) as $restore ) {
			$restore();
		}
		$this->restorers = array();

		parent::tearDown();
	}

	/**
	 * Cart block: populated cart renders the cart wrapper + form.
	 */
	public function test_cart_block_render_contains_wrapper_and_form() {
		$block = new CartBlock();
		$html  = $block->render();

		$this->assertIsString( $html );
		$this->assertStringContainsString( 'edd-blocks__cart', $html );
		$this->assertStringContainsString( 'edd_checkout_cart_form', $html );
	}

	/**
	 * Cart block: empty cart with no fees renders the empty-cart message, not the form.
	 */
	public function test_cart_block_render_empty_cart_shows_message() {
		edd_empty_cart();

		$block = new CartBlock();
		$html  = $block->render();

		$this->assertIsString( $html );
		$this->assertStringContainsString( 'edd-empty-cart', $html );
		$this->assertStringNotContainsString( 'edd_checkout_cart_form', $html );
	}

	/**
	 * Personal-info block: renders the user-details wrapper with the email field.
	 */
	public function test_personal_info_block_render_contains_user_details() {
		$block = new PersonalInfoBlock();
		$html  = $block->render();

		$this->assertIsString( $html );
		$this->assertStringContainsString( 'edd-blocks__user-details', $html );
		$this->assertStringContainsString( 'edd-email', $html );
	}

	/**
	 * Checkout block: a second render in the same request still outputs the
	 * personal-info fieldset, as when a page builder pre-renders the content.
	 */
	public function test_checkout_block_renders_personal_info_on_a_second_render() {
		$first_render  = $this->render_checkout_block_as_guest();
		$second_render = $this->render_checkout_block_as_guest();

		// Assert the fixture: the first render took the inner-blocks path and output the fieldset once.
		$this->assertStringNotContainsString( 'edd-blocks__user-details--logged-in', $first_render );
		$this->assertStringContainsString( 'edd-checkout__inner-blocks', $first_render );
		$this->assertSame( 1, substr_count( $first_render, 'edd-blocks__user-details' ) );

		$this->assertStringContainsString( 'edd-blocks__user-details', $second_render );
		$this->assertStringContainsString( 'edd-email', $second_render );
		$this->assertSame( 1, substr_count( $second_render, 'edd-blocks__user-details' ) );
	}

	/**
	 * Checkout block: when the form top hook already fired outside a block checkout
	 * in this request, the render still outputs the personal-info fieldset exactly once.
	 */
	public function test_checkout_block_renders_personal_info_once_when_form_top_already_fired_elsewhere() {
		// Fire the hook as the legacy shortcode or Elementor checkout would, outside any block.
		ob_start();
		do_action( 'edd_checkout_form_top' );
		ob_get_clean();
		$this->assertGreaterThan( 0, did_action( 'edd_checkout_form_top' ) );

		$html = $this->render_checkout_block_as_guest();

		$this->assertSame( 1, substr_count( $html, 'edd-blocks__user-details' ) );
		$this->assertSame( 1, substr_count( $html, 'id="edd_checkout_user_info"' ) );
	}

	/**
	 * Checkout block: with address fields required, a second render in the same
	 * request outputs the billing address fieldset exactly once.
	 */
	public function test_checkout_block_renders_billing_address_on_a_second_render() {
		$this->remember_option( 'checkout_address_fields' );
		edd_update_option(
			'checkout_address_fields',
			array(
				'address' => 1,
				'city'    => 1,
				'state'   => 1,
				'zip'     => 1,
				'country' => 1,
			)
		);

		$this->assert_billing_address_renders_once_per_render();
	}

	/**
	 * Checkout block: a taxed cart forces the address fields, and a second render
	 * in the same request still outputs the billing address fieldset exactly once.
	 */
	public function test_checkout_block_renders_taxed_cart_billing_address_on_a_second_render() {
		$this->remember_option( 'checkout_address_fields' );
		$this->remember_option( 'enable_taxes' );
		edd_update_option( 'enable_taxes', 1 );

		$this->assertTrue( edd_is_cart_taxed() );
		$this->assertEmpty( edd_get_option( 'checkout_address_fields' ) );

		$this->assert_billing_address_renders_once_per_render();
	}

	/**
	 * Checkout block: a Stripe payment method that forces the address fields still
	 * outputs the billing address fieldset exactly once on a second render.
	 */
	public function test_checkout_block_renders_stripe_forced_billing_address_on_a_second_render() {
		$mode          = edd_is_test_mode() ? 'test' : 'live';
		$configuration = 'edd_test_pmc';

		$this->remember_option( 'checkout_address_fields' );
		$this->remember_option( 'currency' );
		$this->remember_option( 'stripe_elements_mode' );
		$this->remember_transient( "edd_stripe_pmc_{$mode}" );
		$this->remember_transient( $configuration );
		$connect           = edd_stripe()->connect();
		$was_connected     = $connect->is_connected;
		$this->restorers[] = static function () use ( $connect, $was_connected ) {
			$connect->is_connected = $was_connected;
		};

		edd_update_option( 'currency', 'INR' );
		edd_update_option( 'stripe_elements_mode', 'payment-elements' );
		( new \EDD\Utils\Transient( "edd_stripe_pmc_{$mode}" ) )->set( array( 'edd20241002' => $configuration ) );
		( new \EDD\Utils\Transient( $configuration ) )->set( array( 'upi' => array( 'available' => true ) ) );
		$connect->is_connected = true;

		$this->assertTrue( \EDD\Gateways\Stripe\PaymentMethods::requires_billing_address() );
		$this->assertEmpty( edd_get_option( 'checkout_address_fields' ) );

		$this->assert_billing_address_renders_once_per_render();
	}

	/**
	 * Checkout block: an empty-cart render followed by a filled one in the same
	 * request still outputs the personal-info and billing address fieldsets once.
	 */
	public function test_checkout_block_renders_fields_once_after_an_empty_cart_render() {
		$this->remember_option( 'checkout_address_fields' );
		edd_update_option(
			'checkout_address_fields',
			array(
				'address' => 1,
				'city'    => 1,
				'state'   => 1,
				'zip'     => 1,
				'country' => 1,
			)
		);

		edd_empty_cart();
		$empty_render = $this->render_checkout_block_as_guest();
		$this->assertStringNotContainsString( 'edd-blocks__user-details', $empty_render );

		edd_add_to_cart( self::$download->ID );
		$filled_render = $this->render_checkout_block_as_guest();

		$this->assertSame( 1, substr_count( $filled_render, 'edd-blocks__user-details' ) );
		$this->assertSame( 1, substr_count( $filled_render, 'id="edd_cc_address"' ) );
	}

	/**
	 * Payment-info block: renders the payment-details wrapper.
	 */
	public function test_payment_info_block_render_contains_payment_details() {
		$block = new PaymentInfoBlock();
		$html  = $block->render();

		$this->assertIsString( $html );
		$this->assertStringContainsString( 'edd-blocks__payment-details', $html );
	}

	/**
	 * Discount-form block: with an active discount + cart total the form renders.
	 */
	public function test_discount_form_block_render_contains_discount_code() {
		$block = new DiscountFormBlock();
		$html  = $block->render();

		$this->assertIsString( $html );
		$this->assertStringContainsString( 'edd_discount_code', $html );
	}

	/**
	 * Records an EDD option's current value so tearDown() restores it, or deletes
	 * it again if it was absent.
	 *
	 * @param string $key The option key.
	 */
	private function remember_option( $key ) {
		$absent   = new \stdClass();
		$original = edd_get_option( $key, $absent );

		$this->restorers[] = static function () use ( $key, $original, $absent ) {
			if ( $original === $absent ) {
				edd_delete_option( $key );
			} else {
				edd_update_option( $key, $original );
			}
		};
	}

	/**
	 * Records a transient's current value so tearDown() restores it, or deletes
	 * it again if it was absent.
	 *
	 * @param string $name The transient option name.
	 */
	private function remember_transient( $name ) {
		$original = ( new \EDD\Utils\Transient( $name ) )->get( true );

		$this->restorers[] = static function () use ( $name, $original ) {
			$transient = new \EDD\Utils\Transient( $name );
			if ( false === $original || null === $original ) {
				$transient->delete();
			} else {
				$transient->set( $original );
			}
		};
	}

	/**
	 * Renders the checkout block twice and asserts each render outputs the billing
	 * address fieldset once. The first render is the fixture: it proves the fieldset
	 * renders at all before the second render's result means anything.
	 */
	private function assert_billing_address_renders_once_per_render() {
		$first_render  = $this->render_checkout_block_as_guest();
		$second_render = $this->render_checkout_block_as_guest();

		$this->assertSame( 1, substr_count( $first_render, 'id="edd_cc_address"' ) );
		$this->assertSame( 1, substr_count( $second_render, 'id="edd_cc_address"' ) );
	}

	/**
	 * Renders a checkout block with a personal-info inner block as a guest.
	 *
	 * A logged-in user adds a `--logged-in` modifier class to the user details wrapper.
	 *
	 * @return string
	 */
	private function render_checkout_block_as_guest() {
		$original_user_id = get_current_user_id();
		wp_set_current_user( 0 );

		$html = do_blocks( '<!-- wp:edd/checkout --><!-- wp:edd/checkout-personal-info /--><!-- /wp:edd/checkout -->' );

		wp_set_current_user( $original_user_id );

		return $html;
	}
}
