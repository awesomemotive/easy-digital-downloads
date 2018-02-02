<?php
namespace EDD\Tests;

/**
 * A factory for making WordPress data with a cross-object type API.
 *
 * Tests should use this factory to generate test fixtures.
 */
class Factory extends \WP_UnitTest_Factory {

	/**
	 * @var \EDD\Tests\Factory\Discount
	 */
	public $discount;

	public function __construct() {
		parent::__construct();

		$this->discount = Factory\Discount( $this );
	}
}