<?php
namespace EDD\Notes;

/**
 * Note Meta DB Tests
 *
 * @covers EDD_DB_Note_Meta
 * @group edd_notes_db
 * @group database
 * @group edd_notes
 */
class Tests_Note_Meta extends \EDD_UnitTestCase {

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

	/**
	 * @covers \EDD_DB_Note_Meta::add_meta()
	 * @covers Note::add_meta()
	 */
	public function test_add_metadata_with_empty_key_value_should_return_false() {
		$this->assertFalse( self::$note->add_meta( '', '' ) );
	}

	public function test_add_metadata_with_empty_value_should_not_be_empty() {
		$this->assertNotEmpty( self::$note->add_meta( 'test_key', '' ) );
	}

	public function test_add_metadata_with_key_value_should_not_be_empty() {
		$this->assertNotEmpty( self::$note->add_meta( 'test_key', '1' ) );
	}

	/**
	 * @covers \EDD_DB_Note_Meta::update_meta()
	 * @covers Note::update_meta()
	 */
	public function test_update_metadata_with_empty_key_value_should_return_false() {
		$this->assertEmpty( self::$note->update_meta( '', '' ) );
	}

	/**
	 * @covers \EDD_DB_Note_Meta::update_meta()
	 * @covers Note::update_meta()
	 */
	public function test_update_metadata_with_empty_value_should_not_be_empty() {
		$this->assertNotEmpty( self::$note->update_meta( 'test_key_2', '' ) );
	}

	/**
	 * @covers \EDD_DB_Note_Meta::update_meta()
	 * @covers Note::update_meta()
	 */
	public function test_update_metadata_with_key_value_should_not_be_empty() {
		$this->assertNotEmpty( self::$note->update_meta( 'test_key_2', '1' ) );
	}

	/**
	 * @covers \EDD_DB_Note_Meta::get_meta()
	 * @covers Note::get_meta()
	 */
	public function test_get_metadata_with_no_key_should_be_empty() {
		$this->assertEmpty( self::$note->get_meta() );
	}

	/**
	 * @covers \EDD_DB_Note_Meta::get_meta()
	 * @covers Note::get_meta()
	 */
	public function test_get_metadata_with_invalid_key_should_be_empty() {
		$this->assertEmpty( self::$note->get_meta( 'key_that_does_not_exist', true ) );
		self::$note->update_meta( 'test_key_2', '1' );
		$this->assertEquals( '1', self::$note->get_meta( 'test_key_2', true ) );
		$this->assertInternalType( 'array', self::$note->get_meta( 'test_key_2', false ) );
	}

	/**
	 * @covers \EDD_DB_Note_Meta::get_meta()
	 * @covers Note::get_meta()
	 */
	public function test_get_metadata_after_update_should_return_1_and_be_of_type_array() {
		self::$note->update_meta( 'test_key_2', '1' );

		$this->assertEquals( '1', self::$note->get_meta( 'test_key_2', true ) );
		$this->assertInternalType( 'array', self::$note->get_meta( 'test_key_2', false ) );
	}

	/**
	 * @covers \EDD_DB_Note_Meta::delete_meta()
	 * @covers Note::delete_meta()
	 */
	public function test_delete_metadata_after_update() {
		self::$note->update_meta( 'test_key', '1' );

		$this->assertTrue( self::$note->delete_meta( 'test_key' ) );
		$this->assertFalse( self::$note->delete_meta( 'key_that_does_not_exist' ) );
	}
}