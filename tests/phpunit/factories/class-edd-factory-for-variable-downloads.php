<?php
namespace EDD\Tests\Factory;

class Variable_Download extends \WP_UnitTest_Factory_For_Post {

	function __construct( $factory = null ) {
		parent::__construct( $factory );

		$this->default_generation_definitions = array();
	}

	public function create_object( $args ) {
		$download = \EDD_Helper_Download::create_variable_download();

		return $download->ID;
	}
}
