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
			'object_id'    => '',
			'object_type'  => '',
			'note'         => '',
			'user_id'      => '',
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
}
