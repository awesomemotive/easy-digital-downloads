<?php

/**
 * Notes DB Tests
 *
 * @covers EDD_DB_Notes
 * @group edd_notes_db
 * @group database
 * @group edd_notes
 */
class Tests_Notes_DB extends EDD_UnitTestCase {

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
		for ( $i = 0; $i < 3; $i++ ) {
			$note_id = EDD()->notes->insert( array(
				'object_id'   => '1234' . $i,
				'object_type' => 'payment',
				'note'        => 'Payment status changed',
			) );

			self::$notes[] = new EDD_Note( $note_id );
		}
	}

	/**
	 * @covers EDD_DB_Notes::get_columns()
	 */
	public function test_get_columns() {
		$expected = array(
			'id'           => '%d',
			'object_id'    => '%s',
			'object_type'  => '%s',
			'note'         => '%s',
			'user_id'      => '%s',
			'date_created' => '%s',
		);

		$this->assertSame( $expected, EDD()->notes->get_columns() );
	}

	/**
	 * @covers EDD_DB_Notes::get_column_defaults()
	 */
	public function test_get_column_defaults() {
		$expected = array(
			'id'           => 0,
			'object_id'    => 0,
			'object_type'  => '',
			'note'         => '',
			'user_id'      => 0,
			'date_created' => date( 'Y-m-d H:i:s' ),
		);

		$this->assertSame( $expected, EDD()->notes->get_column_defaults() );
	}

	/**
	 * @covers EDD_DB_Notes::insert()
	 */
	public function test_insert_should_return_false_if_no_object_id_supplied() {
		$this->assertFalse( EDD()->notes->insert( array(
			'object_type' => 'payment',
			'note'        => 'Payment status changed',
		) ) );
	}

	/**
	 * @covers EDD_DB_Notes::insert()
	 */
	public function test_insert_should_return_false_if_no_object_type_supplied() {
		$this->assertFalse( EDD()->notes->insert( array(
			'object_id' => 6278,
			'note'      => 'Payment status changed',
		) ) );
	}

	/**
	 * @covers EDD_DB_Notes::insert()
	 */
	public function test_insert_with_valid_date() {
		$this->assertGreaterThan( 0, EDD()->notes->insert( array(
			'object_type' => 'payment',
			'object_id'   => 6278,
			'note'        => 'Payment status changed',
		) ) );
	}

	/**
	 * @covers EDD_DB_Notes::update()
	 */
	public function test_update_should_return_false_if_no_row_id_supplied() {
		$this->assertFalse( EDD()->notes->update( 0 ) );
	}

	/**
	 * @covers EDD_DB_Notes::update()
	 */
	public function test_update() {
		$this->assertTrue( EDD()->notes->update( self::$notes[0]->id, array(
			'note' => 'Note with updated body',
		) ) );

		$note = new EDD_Note( self::$notes[0]->id );

		$this->assertEquals( 'Note with updated body', $note->note );
	}

	/**
	 * @covers EDD_DB_Notes::delete()
	 */
	public function test_delete_should_return_false_if_no_row_id_supplied() {
		$this->assertFalse( EDD()->notes->delete( 0 ) );
	}

	/**
	 * @covers EDD_DB_Notes::delete()
	 */
	public function test_delete() {
		$this->assertTrue( EDD()->notes->delete( self::$notes[0]->id ) );

		$note = new EDD_Note( self::$notes[0]->id );

		$this->assertNull( $note->id );
	}
}
