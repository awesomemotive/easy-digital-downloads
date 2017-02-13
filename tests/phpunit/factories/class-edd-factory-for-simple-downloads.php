<?php
namespace EDD\Tests\Factory;

class Simple_Download extends \WP_UnitTest_Factory_For_Post {

	function __construct( $factory = null ) {
		parent::__construct( $factory );

		$this->default_generation_definitions = array();
	}

	public function create_object( $args ) {
		$download = \EDD_Helper_Download::create_simple_download();

		return $download->ID;
	}
}
