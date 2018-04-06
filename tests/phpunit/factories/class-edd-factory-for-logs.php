<?php
namespace EDD\Tests\Factory;

class Log extends \WP_UnitTest_Factory_For_Thing {

	function __construct( $factory = null ) {
		parent::__construct( $factory );

		$this->default_generation_definitions = array(
			'object_id'   => new \WP_UnitTest_Generator_Sequence( '%f' ),
			'object_type' => 'download',
			'type'        => 'sale',
			'title'       => new \WP_UnitTest_Generator_Sequence( 'Log title %s' ),
			'content'     => new \WP_UnitTest_Generator_Sequence( 'Log message %s' ),
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
		return edd_add_log( $args );
	}

	function update_object( $log_id, $fields ) {
		return edd_update_log( $log_id, $fields );
	}

	public function delete( $log_id ) {
		edd_delete_log( $log_id );
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
		return edd_get_log( $log_id );
	}
}