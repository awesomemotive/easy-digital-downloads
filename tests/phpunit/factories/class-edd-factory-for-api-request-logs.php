<?php
namespace EDD\Tests\Factory;

class API_Request_Log extends \WP_UnitTest_Factory_For_Thing {

	function __construct( $factory = null ) {
		parent::__construct( $factory );

		$this->default_generation_definitions = array(
			'user_id' => new \WP_UnitTest_Generator_Sequence( '%d' ),
			'api_key' => new \WP_UnitTest_Generator_Sequence( 'b8062c469b938352k3bg4656999ed%d' ),
			'token'   => new \WP_UnitTest_Generator_Sequence( 'b8062c469b938352k3bg4656999ed%d' ),
			'request' => new \WP_UnitTest_Generator_Sequence( 'edd-api=sales&key=b8062c469b938352k3bg4656999ed%1$d&token=b8062c469b938352k3bg4656999ed%1$d' ),
			'version' => 'v2',
			'ip'       => '10.1.1.1',
			'time'     => new \WP_UnitTest_Generator_Sequence( '0.0%d' ),
		);
	}

	/**
	 * Stub out copy of parent method for IDE type hinting purposes.
	 *
	 * @param array $args
	 * @param null  $generation_definitions
	 *
	 * @return \EDD\Logs\Api_Request_Log|false
	 */
	function create_and_get( $args = array(), $generation_definitions = null ) {
		return parent::create_and_get( $args, $generation_definitions );
	}

	function create_object( $args ) {
		return edd_add_api_request_log( $args );
	}

	function update_object( $log_id, $fields ) {
		return edd_update_api_request_log( $log_id, $fields );
	}

	public function delete( $log_id ) {
		edd_delete_api_request_log( $log_id );
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
	 * @return \EDD\Logs\Api_Request_Log|false
	 */
	function get_object_by_id( $log_id ) {
		return new \EDD\Logs\Api_Request_Log( $log_id );
	}
}