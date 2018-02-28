<?php
namespace EDD\Notes;

/**
 * Notes DB Tests.
 *
 * @group edd_notes_db
 * @group database
 * @group edd_notes
 *
 * @coversDefaultClass \EDD_DB_Notes
 */
class Tests_Notes_DB extends \EDD_UnitTestCase {

	/**
	 * Notes fixture.
	 *
	 * @access protected
	 * @var array
	 */
	protected static $notes = array();

	/**
	 * Set up fixtures once.
	 */
	public static function wpSetUpBeforeClass() {
		self::$notes = parent::edd()->note->create_many( 5 );
	}

	/**
	 * @covers ::get_columns()
	 */
	public function test_get_columns() {
		$expected = array(
			'id'           => '%d',
			'object_id'    => '%s',
			'object_type'  => '%s',
			'content'      => '%s',
			'user_id'      => '%s',
			'date_created' => '%s',
		);

		$this->assertEqualSets( $expected, EDD()->notes->get_columns() );
	}

	/**
	 * @covers ::get_column_defaults()
	 */
	public function test_get_column_defaults() {
		$expected = array(
			'id'           => 0,
			'object_id'    => 0,
			'object_type'  => '',
			'content'      => '',
			'user_id'      => 0,
			'date_created' => date( 'Y-m-d H:i:s' ),
		);

		$this->assertEqualSets( $expected, EDD()->notes->get_column_defaults() );
	}

	/**
	 * @covers ::insert()
	 */
	public function test_insert_should_return_false_if_no_object_id_supplied() {
		$this->assertFalse( EDD()->notes->insert( array(
			'object_type' => 'payment',
			'content'     => 'Payment status changed',
		) ) );
	}

	/**
	 * @covers ::insert()
	 */
	public function test_insert_should_return_false_if_no_object_type_supplied() {
		$this->assertFalse( EDD()->notes->insert( array(
			'object_id' => 6278,
			'content'   => 'Payment status changed',
		) ) );
	}

	/**
	 * @covers ::insert()
	 */
	public function test_insert_with_valid_date() {
		$this->assertGreaterThan( 0, EDD()->notes->insert( array(
			'object_type' => 'payment',
			'object_id'   => 6278,
			'content'     => 'Payment status changed',
		) ) );
	}

	/**
	 * @covers ::update()
	 */
	public function test_update_should_return_false_if_no_row_id_supplied() {
		$this->assertFalse( EDD()->notes->update( 0 ) );
	}

	/**
	 * @covers ::update()
	 */
	public function test_update() {
		$this->assertTrue( EDD()->notes->update( self::$notes[0], array(
			'content' => 'Note with updated body',
		) ) );

		$note = new Note( self::$notes[0] );

		$this->assertEquals( 'Note with updated body', $note->content );
	}

	/**
	 * @covers ::delete()
	 */
	public function test_delete_should_return_false_if_no_row_id_supplied() {
		$this->assertFalse( EDD()->notes->delete( 0 ) );
	}

	/**
	 * @covers ::delete()
	 */
	public function test_delete() {
		$this->assertTrue( EDD()->notes->delete( self::$notes[0] ) );

		$note = new Note( self::$notes[0] );

		$this->assertNull( $note->id );
	}
}
