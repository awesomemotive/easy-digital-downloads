<?php
namespace EDD\Tests\Factory;

class Note extends \WP_UnitTest_Factory_For_Thing {

	function __construct( $factory = null ) {
		parent::__construct( $factory );

		$this->default_generation_definitions = array(
			'object_id'   => new \WP_UnitTest_Generator_Sequence( '%d' ),
			'object_type' => 'payment',
			'content'     => new \WP_UnitTest_Generator_Sequence( 'Payment status changed for object with ID: %d' ),
			'user_id'     => new \WP_UnitTest_Generator_Sequence( '%d' ),
		);
	}

	/**
	 * Stub out copy of parent method for IDE type hinting purposes.
	 *
	 * @param array $args
	 * @param null  $generation_definitions
	 *
	 * @return \EDD\Notes\Note|false
	 */
	function create_and_get( $args = array(), $generation_definitions = null ) {
		return parent::create_and_get( $args, $generation_definitions );
	}

	function create_object( $args ) {
		return edd_add_note( $args );
	}

	function update_object( $note_id, $fields ) {
		return edd_update_note( $note_id, $fields );
	}

	public function delete( $note_id ) {
		edd_delete_note( $note_id );
	}

	public function delete_many( $notes ) {
		foreach ( $notes as $note ) {
			$this->delete( $note );
		}
	}

	/**
	 * Stub out copy of parent method for IDE type hinting purposes.
	 *
	 * @param $note_id Note ID.
	 *
	 * @return \EDD\Notes\Note|false
	 */
	function get_object_by_id( $note_id ) {
		return edd_get_note( $note_id );
	}
}