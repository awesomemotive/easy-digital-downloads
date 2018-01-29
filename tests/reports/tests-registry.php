<?php
namespace EDD\Reports;

if ( ! class_exists( '\EDD\Reports' ) ) {
	require_once( EDD_PLUGIN_DIR . 'includes/reports/class-init.php' );
}

/**
 * Tests for the Reports registry API.
 *
 * @group edd_registry
 * @group edd_reports
 */
class Registry_Tests extends \EDD_UnitTestCase {

	/**
	 * Reports fixture.
	 *
	 * @var \EDD\Reports
	 * @static
	 */
	protected static $reports;

	/**
	 * Reports registry fixture.
	 *
	 * @access protected
	 * @var    \EDD\Reports\Data\Reports_Registry
	 */
	protected $registry;

	/**
	 * Set up fixtures once.
	 */
	public static function wpSetUpBeforeClass() {
		self::$reports = new \EDD\Reports();
	}

	/**
	 * Set up fixtures once.
	 */
	public function setUp() {
		parent::setUp();

		$this->registry = new \EDD\Reports\Registry();
	}

	/**
	 * Runs after each test to reset the items array.
	 *
	 * @access public
	 */
	public function tearDown() {
		$this->registry->exchangeArray( array() );

		parent::tearDown();
	}

	/**
	 * @covers \EDD\Reports\Registry::$item_error_label
	 */
	public function test_item_error_label_should_be_report() {
		$this->assertSame( 'reports item', $this->registry::$item_error_label );
	}

	/**
	 * @covers \EDD\Reports\Registry::validate_attributes()
	 * @throws \EDD_Exception
	 */
	public function test_validate_attributes_should_throw_exception_if_attribute_is_empty_and_not_filtered() {
		$this->setExpectedException(
			'\EDD\Reports\Exceptions\Invalid_Parameter',
			"The 'foo' parameter for the 'some_item_id' item is missing or invalid in 'EDD\Reports\Registry::validate_attributes'."
		);

		$this->registry->validate_attributes( array( 'foo' => '' ), 'some_item_id' );
	}

	/**
	 * @covers \EDD\Reports\Registry::validate_attributes()
	 * @throws \EDD_Exception
	 */
	public function test_validate_attributes_should_not_throw_exception_if_attribute_is_empty_and_filtered() {
		$attributes = array(
			'foo' => 'bar',
			'baz' => ''
		);

		$filter = array( 'foo' );

		$this->setExpectedException(
			'\EDD\Reports\Exceptions\Invalid_Parameter',
			"The 'baz' parameter for the 'some_item_id' item is missing or invalid in 'EDD\Reports\Registry::validate_attributes'."
		);

		/*
		 * Tough to actually test for no exception, so we'll have to settle
		 * for testing that the first (filtered) attribute _doesn't_ throw one.
		 */
		$this->registry->validate_attributes( $attributes, 'some_item_id', $filter );
	}
}
