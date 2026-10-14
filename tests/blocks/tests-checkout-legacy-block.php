<?php
/**
 * Tests for the legacy checkout block, saved without inner blocks.
 *
 * @package EDD\Tests\Blocks\Checkout
 */

namespace EDD\Tests\Blocks\Checkout;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;
use EDD\Tests\Helpers\EDD_Helper_Download;

/**
 * @group blocks
 */
class LegacyCheckoutBlock extends EDD_UnitTestCase {

	/**
	 * A download for the cart, since the block renders nothing for an empty cart.
	 *
	 * @var \WP_Post
	 */
	private static $download;

	/**
	 * The user who was current before the test logged out.
	 *
	 * @var int
	 */
	private $original_user_id;

	/**
	 * Option values before the test changed them, keyed by option name.
	 *
	 * @var array
	 */
	private $original_options = array();

	public static function wpSetUpBeforeClass() {
		self::$download = EDD_Helper_Download::create_simple_download();
	}

	public function set_up() {
		parent::set_up();

		$this->original_user_id = get_current_user_id();
		wp_set_current_user( 0 );

		foreach ( array( 'show_register_form', 'logged_in_only' ) as $option ) {
			$this->original_options[ $option ] = edd_get_option( $option, null );
		}

		// do_checkout_form_top() renders only once per request.
		unset( $GLOBALS['wp_actions']['edd_checkout_form_top'] );

		edd_empty_cart();
		edd_add_to_cart( self::$download->ID );
	}

	public function tear_down() {
		edd_empty_cart();
		unset( $GLOBALS['wp_actions']['edd_checkout_form_top'] );

		foreach ( $this->original_options as $option => $value ) {
			if ( is_null( $value ) ) {
				edd_delete_option( $option );
			} else {
				edd_update_option( $option, $value );
			}
		}

		wp_set_current_user( $this->original_user_id );

		parent::tear_down();
	}

	public function test_login_setting_shows_login_switcher_to_logged_out_customer() {
		edd_update_option( 'show_register_form', 'login' );

		$html = $this->render_legacy_checkout();

		$this->assertStringContainsString( 'data-attr="guest"', $html );
		$this->assertStringContainsString( 'data-attr="login"', $html );
	}

	public function test_none_setting_shows_guest_fields_without_switcher() {
		edd_update_option( 'show_register_form', 'none' );

		$html = $this->render_legacy_checkout();

		$this->assertStringContainsString( 'edd-blocks__user-details', $html );
		$this->assertStringNotContainsString( 'edd-account-forms', $html );
	}

	public function test_required_account_with_login_setting_renders_login_form() {
		edd_update_option( 'show_register_form', 'login' );
		edd_update_option( 'logged_in_only', 'required' );

		$html = $this->render_legacy_checkout();

		$this->assertStringContainsString( 'id="edd_login_fields"', $html );
		$this->assertStringNotContainsString( 'id="edd_register_fields"', $html );
	}

	/**
	 * Renders the checkout block as stored by `<!-- wp:edd/checkout /-->`.
	 *
	 * @return string The rendered checkout HTML.
	 */
	private function render_legacy_checkout() {
		return \EDD\Blocks\Checkout\checkout( array(), '' );
	}
}
