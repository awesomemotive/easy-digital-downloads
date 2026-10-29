<?php
/**
 * AJAX tests for the admin note handlers.
 *
 * @package   EDD\Tests\Ajax
 * @copyright (c) 2026, Sandhills Development, LLC
 * @license https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 3.7.1
 */

namespace EDD\Tests\Ajax;

defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

use EDD\Tests\PHPUnit\Ajax_UnitTestCase;

/**
 * Note AJAX handler tests.
 *
 * @since 3.7.1
 *
 * @group edd_notes
 */
class Notes extends Ajax_UnitTestCase {

	/**
	 * Discount fixture.
	 *
	 * @var \EDD_Discount
	 */
	protected static $discount;

	/**
	 * Note fixture.
	 *
	 * @var \EDD\Notes\Note
	 */
	protected static $note;

	/**
	 * Set up fixtures once.
	 *
	 * @access public
	 */
	public static function wpSetUpBeforeClass() {
		wp_set_current_user( 1 );

		self::$discount = self::edd()->discount->create_and_get(
			array(
				'name'              => '20 Percent Off',
				'code'              => '20OFF',
				'status'            => 'active',
				'type'              => 'percent',
				'amount'            => '20',
				'use_count'         => 54,
				'max_uses'          => 10,
				'min_charge_amount' => 128,
				'product_condition' => 'all',
				'start_date'        => '2010-12-12 00:00:00',
				'end_date'          => '2050-12-31 23:59:59',
			)
		);

		self::$note = self::edd()->note->create_and_get(
			array(
				'object_id'   => self::$discount->id,
				'object_type' => 'discount',
				'content'     => 'Test note content.',
				'user_id'     => get_current_user_id(),
			)
		);
	}

	/**
	 * Registers the note AJAX handlers for each test.
	 *
	 * The handlers live in admin-only files that the test bootstrap does not load
	 * (include_admin() is gated on is_admin()). Requiring the files defines the functions;
	 * the explicit add_action() calls re-attach the hooks each test, since the WP test
	 * harness resets $wp_filter between tests and add_action is idempotent per callback.
	 *
	 * @return void
	 */
	public function setUp(): void {
		parent::setUp();

		require_once EDD_PLUGIN_DIR . 'includes/admin/notes/note-functions.php';
		require_once EDD_PLUGIN_DIR . 'includes/admin/notes/note-actions.php';

		add_action( 'wp_ajax_edd_add_note', 'edd_admin_ajax_add_note' );
		add_action( 'wp_ajax_edd_delete_note', 'edd_admin_ajax_delete_note' );
	}

	/**
	 * @covers ::edd_admin_ajax_add_note
	 */
	public function test_add_discount_note_with_no_args_should_die() {
		$this->_setRole( 'shop_manager' );
		// Without the cap this dies on the capability gate, not the missing arguments.
		$this->assertTrue( current_user_can( 'edit_shop_payments' ), 'Fixture: the shop manager must pass the capability gate.' );

		// The nonce is user-scoped, so issue it after the role switch.
		$_POST['nonce'] = wp_create_nonce( 'edd_note' );

		$this->expectException( \WPAjaxDieStopException::class );
		$this->expectExceptionMessage( '-1' );

		$this->_handleAjax( 'edd_add_note' );
	}

	/**
	 * @covers ::edd_admin_ajax_add_note
	 */
	public function test_add_discount_note_with_incorrect_role_should_die() {
		$this->_setRole( 'subscriber' );

		$_POST['nonce']       = wp_create_nonce( 'edd_note' );
		$_POST['object_id']   = self::$discount->id;
		$_POST['object_type'] = 'discount';
		$_POST['note']        = 'This is a test note.';

		$this->expectException( \WPAjaxDieStopException::class );
		$this->expectExceptionMessage( '-1' );

		$this->_handleAjax( 'edd_add_note' );
	}

	/**
	 * @covers ::edd_admin_ajax_add_note
	 */
	public function test_add_discount_note_with_no_note_should_die() {
		$this->_setRole( 'shop_manager' );

		$_POST['nonce']       = wp_create_nonce( 'edd_note' );
		$_POST['object_id']   = self::$discount->id;
		$_POST['object_type'] = 'discount';

		// Without the cap this dies on the capability gate, not the empty note.
		$this->assertTrue( current_user_can( 'edit_shop_payments' ), 'Fixture: the shop manager must pass the capability gate.' );

		$this->expectException( \WPAjaxDieStopException::class );
		$this->expectExceptionMessage( '-1' );

		$this->_handleAjax( 'edd_add_note' );
	}

	/**
	 * @covers ::edd_admin_ajax_add_note
	 */
	public function test_add_discount_note_should_return_true() {
		$this->_setRole( 'shop_manager' );

		$_POST['nonce']       = wp_create_nonce( 'edd_note' );
		$_POST['object_id']   = self::$discount->id;
		$_POST['object_type'] = 'discount';
		$_POST['note']        = 'This is a test note.';

		// The note HTML builds a delete URL via add_query_arg() with no base, which falls back to
		// $_SERVER['REQUEST_URI']; give it a value so it is not null (other tests may leave it unset).
		$request_uri            = isset( $_SERVER['REQUEST_URI'] ) ? $_SERVER['REQUEST_URI'] : null;
		$_SERVER['REQUEST_URI'] = '/wp-admin/admin-ajax.php';

		// WP_Ajax_Response::send() calls header() unguarded, which warns in CLI where output has
		// already been sent; mask E_WARNING around the dispatch so it is not converted to an exception.
		$error_reporting = error_reporting( error_reporting() & ~E_WARNING );
		try {
			$this->_handleAjax( 'edd_add_note' );
		} finally {
			error_reporting( $error_reporting );
			$_SERVER['REQUEST_URI'] = $request_uri;
		}

		// The response envelope carries the hardcoded edd_note_html literal whether or not a note
		// was written, so assert the stored row instead.
		$notes = edd_get_notes(
			array(
				'object_id'   => self::$discount->id,
				'object_type' => 'discount',
			)
		);

		$this->assertContains( 'This is a test note.', wp_list_pluck( $notes, 'content' ) );
	}

	/**
	 * @covers ::edd_admin_ajax_delete_note
	 */
	public function test_delete_discount_note_with_no_args_should_die() {
		// Switch to a capable user so the handler reaches the missing note_id check instead of the
		// capability gate, and issue the nonce afterward, since it is user-scoped.
		$this->_setRole( 'shop_manager' );
		$this->assertTrue( current_user_can( 'manage_shop_settings' ), 'Fixture: the shop manager must pass the capability gate.' );
		$_POST['nonce'] = wp_create_nonce( 'edd_note' );

		$this->expectException( \WPAjaxDieStopException::class );
		$this->expectExceptionMessage( '-1' );

		$this->_handleAjax( 'edd_delete_note' );
	}

	/**
	 * @covers ::edd_admin_ajax_delete_note
	 */
	public function test_delete_discount_note_with_incorrect_role_should_die() {
		$this->_setRole( 'subscriber' );

		$_POST['nonce']   = wp_create_nonce( 'edd_note' );
		$_POST['note_id'] = 1;

		$this->expectException( \WPAjaxDieStopException::class );
		$this->expectExceptionMessage( '-1' );

		$this->_handleAjax( 'edd_delete_note' );
	}

	/**
	 * @covers ::edd_admin_ajax_delete_note
	 */
	public function test_delete_discount_note_with_invalid_id_should_die() {
		$this->_setRole( 'shop_manager' );
		$this->assertTrue( current_user_can( 'manage_shop_settings' ), 'Fixture: the shop manager must pass the capability gate.' );

		$_POST['nonce']   = wp_create_nonce( 'edd_note' );
		$_POST['note_id'] = 99;

		$this->expectException( \WPAjaxDieStopException::class );
		$this->expectExceptionMessage( '0' );

		$this->_handleAjax( 'edd_delete_note' );
	}

	/**
	 * Deletes the shared note fixture, so it belongs last in the class.
	 *
	 * @covers ::edd_admin_ajax_delete_note
	 */
	public function test_delete_discount_note_should_return_true() {
		$this->_setRole( 'shop_manager' );

		$_POST['nonce']   = wp_create_nonce( 'edd_note' );
		$_POST['note_id'] = self::$note->id;

		$this->expectException( \WPAjaxDieStopException::class );
		$this->expectExceptionMessage( '1' );

		$this->_handleAjax( 'edd_delete_note' );
	}
}
