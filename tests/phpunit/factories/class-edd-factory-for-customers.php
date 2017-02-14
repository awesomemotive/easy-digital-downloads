<?php
namespace EDD\Tests\Factory;

class Customer extends \WP_UnitTest_Factory_For_Thing {

	function __construct( $factory = null ) {
		parent::__construct( $factory );
	}

	function create_and_get( $args = array(), $generation_definitions = null ) {
		return parent::create_and_get( $args, $generation_definitions );
	}

	function create_object( $args ) {
		if ( empty( $args['email'] ) ) {
			$args['email'] = sprintf( 'edd_user_%s@test.dev', rand_str( 8 ) );
		}

		$customer = new \EDD_Customer( $args['email'] );

		return $customer->create( $args );
	}

	function update_object( $customer_id, $fields ) {
		$customer = new \EDD_Customer( $customer_id );
		return $customer->update( $fields );
	}

	function get_object_by_id( $customer_id ) {
		return new \EDD_Customer( $customer_id );
	}
}
