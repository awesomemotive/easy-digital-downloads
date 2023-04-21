<?php
namespace EDD\Tests\Reports;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;
use EDD\Reports\Init as ReportsInit;
new ReportsInit();

/**
 * Tests for the Report registry API.
 *
 * @group edd_registry
 * @group edd_reports
 *
 * @coversDefaultClass \EDD\Reports\Registry
 */
class Registry_Tests extends EDD_UnitTestCase {

	/**
	 * Report registry fixture.
	 *
	 * @access protected
	 * @var    \EDD\Reports\Data\Report_Registry
	 */
	protected $registry;

	/**
	 * Set up fixtures once.
	 */
	public function setup(): void {
		parent::setUp();

		$this->registry = new \EDD\Reports\Registry();
	}

	/**
	 * Runs after each test to reset the items array.
	 *
	 * @access public
	 */
	public function tearDown(): void {
		$this->registry->exchangeArray( array() );

		parent::tearDown();
	}

	/**
	 * @covers ::validate_attributes()
	 * @throws \EDD_Exception
	 */
	public function test_validate_attributes_should_throw_exception_if_attribute_is_empty_and_not_filtered() {
		$this->expectException( '\EDD\Reports\Exceptions\Invalid_Parameter' );
		$this->expectExceptionMessage( "The 'foo' parameter for the 'some_item_id' item is missing or invalid in 'EDD\Reports\Registry::validate_attributes'." );

		$this->registry->validate_attributes( array( 'foo' => '' ), 'some_item_id' );
	}

	/**
	 * @covers ::validate_attributes()
	 * @throws \EDD_Exception
	 */
	public function test_validate_attributes_should_not_throw_exception_if_attribute_is_empty_and_filtered() {
		$attributes = array(
			'foo' => 'bar',
			'baz' => ''
		);

		$filter = array( 'foo' );

		$this->expectException( '\EDD\Reports\Exceptions\Invalid_Parameter' );
		$this->expectExceptionMessage( "The 'baz' parameter for the 'some_item_id' item is missing or invalid in 'EDD\Reports\Registry::validate_attributes'." );

		/*
		 * Tough to actually test for no exception, so we'll have to settle
		 * for testing that the first (filtered) attribute _doesn't_ throw one.
		 */
		$this->registry->validate_attributes( $attributes, 'some_item_id', $filter );
	}
}
