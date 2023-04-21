<?php
namespace EDD\Tests\Notes;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;

/**
 * Note Meta DB Tests
 *
 * @covers EDD\Database\Queries\Notes
 * @group edd_notes_db
 * @group database
 * @group edd_notes
 */
class Tests_Note_Meta extends EDD_UnitTestCase {

	/**
	 * Note fixture.
	 *
	 * @access protected
	 * @var    Note
	 */
	protected static $note = null;

	/**
	 * Set up fixtures once.
	 */
	public static function wpSetUpBeforeClass() {
		self::$note = parent::edd()->note->create_and_get();
	}

	public function tearDown(): void {
		parent::tearDown();

		edd_get_component_interface( 'note', 'meta' )->truncate();
	}

	/**
	 * @covers \EDD\Database\Queries\Notes::add_meta()
	 * @covers Note::add_meta()
	 */
	public function test_add_metadata_with_empty_key_value_should_return_false() {
		$this->assertFalse( edd_add_note_meta( self::$note->id, '', '' ) );
	}

	public function test_add_metadata_with_empty_value_should_not_be_empty() {
		$this->assertNotEmpty( edd_add_note_meta( self::$note->id, 'test_key', '' ) );
	}

	public function test_add_metadata_with_key_value_should_not_be_empty() {
		$this->assertNotEmpty( edd_add_note_meta( self::$note->id, 'test_key', '1' ) );
	}

	/**
	 * @covers \EDD\Database\Queries\Notes::update_meta()
	 * @covers Note::update_meta()
	 */
	public function test_update_metadata_with_empty_key_value_should_return_false() {
		$this->assertEmpty( edd_update_note_meta( self::$note->id, '', '' ) );
	}

	/**
	 * @covers \EDD\Database\Queries\Notes::update_meta()
	 * @covers Note::update_meta()
	 */
	public function test_update_metadata_with_empty_value_should_not_be_empty() {
		$this->assertNotEmpty( edd_update_note_meta( self::$note->id, 'test_key_2', '' ) );
	}

	/**
	 * @covers \EDD\Database\Queries\Notes::update_meta()
	 * @covers Note::update_meta()
	 */
	public function test_update_metadata_with_key_value_should_not_be_empty() {
		$this->assertNotEmpty( edd_update_note_meta( self::$note->id, 'test_key_2', '1' ) );
	}

	/**
	 * @covers \EDD\Database\Queries\Notes::get_meta()
	 * @covers Note::get_meta()
	 */
	public function test_get_metadata_with_no_args_should_be_empty() {
		$this->assertEmpty( edd_get_note_meta( self::$note->id, '' ) );
	}

	/**
	 * @covers \EDD\Database\Queries\Notes::get_meta()
	 * @covers Note::get_meta()
	 */
	public function test_get_metadata_with_invalid_key_should_be_empty() {
		$this->assertEmpty( edd_get_note_meta( self::$note->id, 'key_that_does_not_exist', true ) );
		edd_update_note_meta( self::$note->id, 'test_key_2', '1' );
		$this->assertEquals( '1', edd_get_note_meta( self::$note->id, 'test_key_2', true ) );
		$this->assertIsArray( edd_get_note_meta( self::$note->id, 'test_key_2', false ) );
	}

	/**
	 * @covers \EDD\Database\Queries\Notes::get_meta()
	 * @covers Note::get_meta()
	 */
	public function test_get_metadata_after_update_should_return_1_and_be_of_type_array() {
		edd_update_note_meta( self::$note->id, 'test_key_2', '1' );

		$this->assertEquals( '1', edd_get_note_meta( self::$note->id, 'test_key_2', true ) );
		$this->assertIsArray( edd_get_note_meta( self::$note->id, 'test_key_2', false ) );
	}

	/**
	 * @covers \EDD\Database\Queries\Notes::delete_meta()
	 * @covers Note::delete_meta()
	 */
	public function test_delete_metadata_after_update() {
		edd_update_note_meta( self::$note->id, 'test_key', '1' );

		$this->assertTrue( edd_delete_note_meta( self::$note->id, 'test_key' ) );
		$this->assertFalse( edd_delete_note_meta( self::$note->id, 'key_that_does_not_exist' ) );
	}
}
