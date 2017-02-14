<?php
namespace EDD\Tests\Factory;

class Simple_Flat_Discount extends \WP_UnitTest_Factory_For_Post {

	function __construct( $factory = null ) {
		parent::__construct( $factory );

		$this->default_generation_definitions = array();
	}

	public function create_object( $args ) {
		return \EDD_Helper_Discount::create_simple_flat_discount();
	}
}
