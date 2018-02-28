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
		$note_id = EDD()->notes->insert( array(
			'object_id'   => '1234',
			'object_type' => 'payment',
			'content'     => 'Payment status changed',
		) );

		self::$note = new Note( $note_id );
	}

	/**
	 * @covers EDD_DB_Discount_Meta::add_meta()
	 * @covers EDD_Discount::add_meta()
	 */
	public function test_add_metadata() {
		$this->assertFalse( self::$note->add_meta( '', '' ) );
		$this->assertNotEmpty( self::$note->add_meta( 'test_key', '' ) );
		$this->assertNotEmpty( self::$note->add_meta( 'test_key', '1' ) );
	}

	/**
	 * @covers EDD_DB_Discount_Meta::update_meta()
	 * @covers EDD_Discount::update_meta()
	 */
	public function test_update_metadata() {
		$this->assertEmpty( self::$note->update_meta( '', '' ) );
		$this->assertNotEmpty( self::$note->update_meta( 'test_key_2', '' ) );
		$this->assertNotEmpty( self::$note->update_meta( 'test_key_2', '1' ) );
	}

	/**
	 * @covers EDD_DB_Discount_Meta::get_meta()
	 * @covers EDD_Discount::get_meta()
	 */
	public function test_get_metadata() {
		$this->assertEmpty( self::$note->get_meta() );
		$this->assertEmpty( self::$note->get_meta( 'key_that_does_not_exist', true ) );
		self::$note->update_meta( 'test_key_2', '1' );
		$this->assertEquals( '1', self::$note->get_meta( 'test_key_2', true ) );
		$this->assertInternalType( 'array', self::$note->get_meta( 'test_key_2', false ) );
	}

	/**
	 * @covers EDD_DB_Discount_Meta::delete_meta()
	 * @covers EDD_Discount::delete_meta()
	 */
	public function test_delete_metadata() {
		self::$note->update_meta( 'test_key', '1' );
		$this->assertTrue( self::$note->delete_meta( 'test_key' ) );
		$this->assertFalse( self::$note->delete_meta( 'key_that_does_not_exist' ) );
	}
}