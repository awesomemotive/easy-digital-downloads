<?php
namespace EDD\Tests;

/**
 * A factory for making WordPress data with a cross-object type API.
 *
 * Tests should use this factory to generate test fixtures.
 */
class Factory extends \WP_UnitTest_Factory {

	/**
	 * @var \EDD\Tests\Factory\Simple_Download
	 */
	public $simple_download;

	/**
	 * @var \EDD\Tests\Factory\Variable_Download
	 */
	public $variable_download;


	function __construct() {
		parent::__construct();

		$this->simple_download   = new Factory\Simple_Download( $this );
		$this->variable_download = new Factory\Variable_Download( $this );
	}
}
