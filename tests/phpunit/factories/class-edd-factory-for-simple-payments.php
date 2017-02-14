<?php
namespace EDD\Tests\Factory;

class Simple_Payment extends \WP_UnitTest_Factory_For_Post {

	function __construct( $factory = null ) {
		parent::__construct( $factory );

		$this->default_generation_definitions = array();
	}

	public function create_object( $args ) {
		return \EDD_Helper_Payment::create_simple_payment();
	}

	function get_object_by_id( $payment_id ) {
		return edd_get_payment( $payment_id );
	}

}
