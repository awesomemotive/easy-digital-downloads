<?php
namespace EDD\Tests\Factory;

class File_Download_Log extends \WP_UnitTest_Factory_For_Thing {

	function __construct( $factory = null ) {
		parent::__construct( $factory );

		$this->default_generation_definitions = array(
			'download_id' => new \WP_UnitTest_Generator_Sequence( '%f' ),
			'file_id'     => new \WP_UnitTest_Generator_Sequence( '%f' ),
			'payment_id'  => new \WP_UnitTest_Generator_Sequence( '%f' ),
			'price_id'    => '1',
			'email'       => new \WP_UnitTest_Generator_Sequence( 'admin_%s@edd.test' ),
			'ip'          => '10.1.1.1',
		);
	}

	/**
	 * Stub out copy of parent method for IDE type hinting purposes.
	 *
	 * @param array $args
	 * @param null  $generation_definitions
	 *
	 * @return \EDD\Logs\File_Download_Log|false
	 */
	function create_and_get( $args = array(), $generation_definitions = null ) {
		return parent::create_and_get( $args, $generation_definitions );
	}

	function create_object( $args ) {
		return EDD()->file_download_logs->insert( $args );
	}

	function update_object( $log_id, $fields ) {
		return EDD()->file_download_logs->update( $log_id, $fields );
	}

	public function delete( $log_id ) {
		EDD()->file_download_logs->delete( $log_id );
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
	 * @return \EDD\Logs\File_Download_Log|false
	 */
	function get_object_by_id( $log_id ) {
		return new \EDD\Logs\File_Download_Log( $log_id );
	}
}