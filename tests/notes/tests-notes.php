<?php
namespace EDD\Notes;

use EDD\Tests\Ajax_UnitTestCase;

/**
 * Discount Ajax functions to be tested
 */
require_once EDD_PLUGIN_DIR . 'includes/admin/notes/note-actions.php';
require_once EDD_PLUGIN_DIR . 'includes/admin/notes/note-functions.php';

/**
 * Class Tests_Notes
 *
 * @group edd_notes
 * @group edd_ajax
 */
class Tests_Notes extends Ajax_UnitTestCase {

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
	 * Setup the parent
	 */
	public function setUp() {
		parent::setup();
	}

	/**
	 * Set up fixtures once.
	 *
	 * @access public
	 */
	public static function wpSetUpBeforeClass() {
		wp_set_current_user( 1 );

		self::$discount = self::edd()->discount->create_and_get( array(
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
			'end_date'          => '2050-12-31 23:59:59'
		) );

		self::$note = self::edd()->note->create_and_get( array(
			'object_id'   => self::$discount->id,
			'object_type' => 'discount',
			'content'     => 'Test note content.',
			'user_id'     => get_current_user_id()
		) );
	}

	/**
	 * @covers \edd_ajax_add_discount_note()
	 */
	public function test_add_discount_note_with_no_args_should_die() {
		$_POST['nonce'] = wp_create_nonce( 'edd_note' );
		$e = false;
		try {
			$this->_handleAjax( 'edd_add_note' );
		} catch ( \WPAjaxDieStopException $e ) { }

		$this->assertSame( '-1', $e->getMessage() );
	}

	/**
	 * @covers \edd_ajax_add_discount_note()
	 */
	public function test_add_discount_note_with_incorrect_role_should_die() {
		$this->_setRole( 'subscriber' );

		$_POST['nonce']       = wp_create_nonce( 'edd_note' );
		$_POST['object_id']   = self::$discount->id;
		$_POST['object_type'] = 'discount';
		$_POST['note']        = 'This is a test note.';

		try {
			$this->_handleAjax( 'edd_add_note' );
		} catch ( \WPAjaxDieStopException $e ) { }

		$this->assertSame( '-1', $e->getMessage() );
	}

	/**
	 * @covers \edd_ajax_add_discount_note()
	 */
	public function test_add_discount_note_with_no_note_should_die() {
		$this->_setRole( 'shop_manager' );

		$_POST['nonce']       = wp_create_nonce( 'edd_note' );
		$_POST['object_id']   = self::$discount->id;
		$_POST['object_type'] = 'discount';

		try {
			$this->_handleAjax( 'edd_add_note' );
		} catch ( \WPAjaxDieStopException $e ) { }

		$this->assertSame( '-1', $e->getMessage() );
	}

	/**
	 * @covers \edd_ajax_add_discount_note()
	 */
	public function test_add_discount_note_should_return_true() {
		$this->_setRole( 'shop_manager' );

		$_POST['nonce']       = wp_create_nonce( 'edd_note' );
		$_POST['object_id']   = self::$discount->id;
		$_POST['object_type'] = 'discount';
		$_POST['note']        = 'This is a test note.';

		try {
			$this->_handleAjax( 'edd_add_note' );
		} catch ( \WPAjaxDieContinueException $e ) {
			unset( $e );
		}

		$this->assertContains( 'edd_note_html', $this->_last_response );
	}

	/**
	 * @covers \edd_ajax_delete_discount_note()
	 */
	public function test_delete_discount_note_with_no_args_should_die() {
		$_POST['nonce'] = wp_create_nonce( 'edd_note' );

		try {
			$this->_handleAjax( 'edd_delete_note' );
		} catch ( \WPAjaxDieStopException $e ) { }

		$this->assertSame( '-1', $e->getMessage() );
	}

	/**
	 * @covers \edd_ajax_delete_discount_note()
	 */
	public function test_delete_discount_note_with_incorrect_role_should_die() {
		$this->_setRole( 'subscriber' );

		$_POST['nonce']   = wp_create_nonce( 'edd_note' );
		$_POST['note_id'] = 1;

		try {
			$this->_handleAjax( 'edd_delete_note' );
		} catch ( \WPAjaxDieStopException $e ) { }

		$this->assertSame( '-1', $e->getMessage() );
	}

	/**
	 * @covers \edd_ajax_delete_discount_note()
	 */
	public function test_delete_discount_note_with_invalid_id_should_die() {
		$this->_setRole( 'shop_manager' );

		$_POST['nonce']   = wp_create_nonce( 'edd_note' );
		$_POST['note_id'] = 99;

		try {
			$this->_handleAjax( 'edd_delete_note' );
		} catch ( \WPAjaxDieStopException $e ) { }

		$this->assertSame( '0', $e->getMessage() );
	}

	/**
	 * @covers \edd_ajax_delete_discount_note()
	 */
	public function test_delete_discount_note_should_return_true() {
		$this->_setRole( 'shop_manager' );

		$_POST['nonce']   = wp_create_nonce( 'edd_note' );
		$_POST['note_id'] = self::$note->id;

		try {
			$this->_handleAjax( 'edd_delete_note' );
		} catch ( \WPAjaxDieStopException $e ) {}

		$this->assertSame( '1', $e->getMessage() );
	}
}
