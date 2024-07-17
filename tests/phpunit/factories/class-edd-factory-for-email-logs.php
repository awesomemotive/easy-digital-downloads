<?php
namespace EDD\Tests\Factory;

use \EDD\Database\Queries\LogEmail;

class EmailLog extends \WP_UnitTest_Factory_For_Thing {

	function __construct( $factory = null ) {
		parent::__construct( $factory );

		$this->default_generation_definitions = array(
			'object_id'   => new \WP_UnitTest_Generator_Sequence( '%f' ),
			'object_type' => 'order',
			'email_id'    => 'order_receipt',
			'subject'     => new \WP_UnitTest_Generator_Sequence( 'Email subject %s' ),
			'email'       => 'test@edd.local',
		);
	}

	/**
	 * Stub out copy of parent method for IDE type hinting purposes.
	 *
	 * @param array $args
	 * @param null  $generation_definitions
	 *
	 * @return \EDD\Logs\Log|false
	 */
	function create_and_get( $args = array(), $generation_definitions = null ) {
		return parent::create_and_get( $args, $generation_definitions );
	}

	function create_object( $args ) {
		$query = new LogEmail();

		return $query->add_item( $args );
	}

	function update_object( $log_id, $fields ) {
		$query = new LogEmail();

		return $query->update_item( $log_id, $fields );
	}

	public function delete( $log_id ) {
		$query = new LogEmail();

		$query->delete_item( $log_id );
	}

	public function delete_many( $logs ) {
		foreach ( $logs as $log ) {
			$this->delete( $log );
		}
	}

	/**
	 * Stub out copy of parent method for IDE type hinting purposes.
	 *
	 * @param $log_id Log ID.
	 *
	 * @return \EDD\Logs\Log|false
	 */
	function get_object_by_id( $log_id ) {
		$query = new LogEmail();

		return $query->get_item( $log_id );
	}
}
