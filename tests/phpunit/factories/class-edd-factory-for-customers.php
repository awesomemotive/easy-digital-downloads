<?php
namespace EDD\Tests\Factory;

class Customer extends \WP_UnitTest_Factory_For_Thing {

	function __construct( $factory = null ) {
		parent::__construct( $factory );

		$this->default_generation_definitions = array(
			'name'  => new \WP_UnitTest_Generator_Sequence( 'User %s' ),
			'email' => new \WP_UnitTest_Generator_Sequence( 'user%d@edd.test' ),
		);
	}

	/**
	 * Stub out copy of parent method for IDE type hinting purposes.
	 *
	 * @param array $args
	 * @param null  $generation_definitions
	 *
	 * @return \EDD_Customer|false
	 */
	function create_and_get( $args = array(), $generation_definitions = null ) {
		return parent::create_and_get( $args, $generation_definitions );
	}

	function create_object( $args ) {
		return edd_add_customer( $args );
	}

	function update_object( $customer_id, $fields ) {
		return edd_update_customer( $customer_id, $fields );
	}

	public function delete( $customer_id ) {
		edd_delete_customer( $customer_id );
	}

	public function delete_many( $customers ) {
		foreach ( $customers as $customer ) {
			$this->delete( $customer );
		}
	}

	/**
	 * Stub out copy of parent method for IDE type hinting purposes.
	 *
	 * @param $customer_id Customer ID.
	 *
	 * @return \EDD_Customer|false
	 */
	function get_object_by_id( $customer_id ) {
		return edd_get_customer( $customer_id );
	}
}
