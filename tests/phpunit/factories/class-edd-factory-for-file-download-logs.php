<?php
namespace EDD\Tests\Factory;

class File_Download_Log extends \WP_UnitTest_Factory_For_Thing {

	function __construct( $factory = null ) {
		parent::__construct( $factory );

		$this->default_generation_definitions = array(
			'product_id'  => new \WP_UnitTest_Generator_Sequence( '%f' ),
			'file_id'     => new \WP_UnitTest_Generator_Sequence( '%f' ),
			'order_id'    => new \WP_UnitTest_Generator_Sequence( '%f' ),
			'price_id'    => '1',
			'customer_id' => new \WP_UnitTest_Generator_Sequence( '%f' ),
			'ip'          => '10.1.1.1',
			'user_agent'  => 'PHPUnit/Unix',
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
		return edd_add_file_download_log( $args );
	}

	function update_object( $log_id, $fields ) {
		return edd_update_file_download_log( $log_id, $fields );
	}

	public function delete( $log_id ) {
		edd_delete_file_download_log( $log_id );
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
		return edd_get_file_download_log( $log_id );
	}
}