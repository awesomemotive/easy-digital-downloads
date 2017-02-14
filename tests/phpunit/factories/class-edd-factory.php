<?php
namespace EDD\Tests;

/**
 * A factory for making WordPress data with a cross-object type API.
 *
 * Tests should use this factory to generate test fixtures.
 */
class Factory extends \WP_UnitTest_Factory {

	/**
	 * @var \EDD\Tests\Factory\Customer
	 */
	public $customer;

	/**
	 * @var \EDD\Tests\Factory\Simple_Discount
	 */
	public $simple_discount;

	/**
	 * @var \EDD\Tests\Factory\Simple_Flat_Discount
	 */
	public $simple_flat_discount;

	/**
	 * @var \EDD\Tests\Factory\Simple_Download
	 */
	public $simple_download;

	/**
	 * @var \EDD\Tests\Factory\Variable_Download
	 */
	public $variable_download;

	/**
	 * @var \EDD\Tests\Factory\Simple_Payment
	 */
	public $simple_payment;

	function __construct() {
		parent::__construct();

		$this->customer             = new Factory\Customer( $this );

		$this->simple_discount      = new Factory\Simple_Discount( $this );
		$this->simple_flat_discount = new Factory\Simple_Flat_Discount( $this );

		$this->simple_download      = new Factory\Simple_Download( $this );
		$this->variable_download    = new Factory\Variable_Download( $this );

		$this->simple_payment       = new Factory\Simple_Payment( $this );
	}
}
