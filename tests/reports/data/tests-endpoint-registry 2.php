<?php
namespace EDD\Reports\Data;

require_once EDD_PLUGIN_DIR . 'includes/admin/reporting/reports.php';

if ( ! class_exists( 'EDD\\Reports\\Init' ) ) {
	require_once( EDD_PLUGIN_DIR . 'includes/reports/class-init.php' );
}

new \EDD\Reports\Init();

/**
 * Tests for the Endpoint registry API.
 *
 * @group edd_registry
 * @group edd_reports
 * @group edd_reports_endpoints
 *
 * @coversDefaultClass \EDD\Reports\Data\Endpoint_Registry
 */
class Endpoint_Registry_Tests extends \EDD_UnitTestCase {

	/**
	 * Endpoint registry fixture.
	 *
	 * @access protected
	 * @var    \EDD\Reports\Data\Endpoint_Registry
	 */
	protected $registry;

	/**
	 * Set up fixtures once.
	 */
	public function setUp() {
		parent::setUp();

		$this->registry = new \EDD\Reports\Data\Endpoint_Registry();
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
	 * @covers ::instance()
	 */
	public function test_static_registry_should_have_instance_method() {
		$this->assertTrue( method_exists( $this->registry, 'instance' ) );
	}

	/**
	 * @covers ::__call()
	 * @group edd_errors
	 */
	public function test_get_endpoint_with_invalid_endpoint_id_should_return_an_empty_array() {
		$this->setExpectedException( '\EDD_Exception', "The 'foo' reports endpoint does not exist." );

		$result = $this->registry->get_endpoint( 'foo' );

		$this->assertEqualSets( array(), $result );
	}

	/**
	 * @covers ::__call()
	 * @group edd_errors
	 */
	public function test_get_endpoint_with_invalid_endpoint_id_should_throw_an_exception() {
		$this->setExpectedException( '\EDD_Exception', "The 'foo' reports endpoint does not exist." );

		$this->registry->get_endpoint( 'foo' );
	}

	/**
	 * @covers ::__call()
	 * @throws \EDD_Exception
	 */
	public function test_get_endpoint_with_valid_endpoint_id_should_return_that_endpoint() {
		$expected = array(
			'id'       => 'foo',
			'label'    => 'Foo',
			'priority' => 10,
			'views'    => array(
				'tile' => array(
					'data_callback'    => '__return_false',
					'display_callback' => '\\EDD\\Reports\\default_display_tile',
					'display_args'     => array(
						'type'             => '' ,
						'context'          => 'primary',
						'comparison_label' => __( 'All time', 'easy-digital-downloads' ),
					)
				),
			)
		);

		// Add a test endpoint.
		$this->registry->register_endpoint( 'foo', $expected );

		$this->assertEqualSetsWithIndex( $expected, $this->registry->get_endpoint( 'foo' ) );
	}

	/**
	 * @covers ::__call()
	 * @throws \EDD_Exception
	 */
	public function test_unregister_endpoint_with_invalid_endpoint_id_should_affect_no_change() {
		// Add a test endpoint.
		$this->registry->register_endpoint( 'foo', array(
			'label'    => 'Foo',
			'priority' => 10,
			'views'    => array(
				'tile' => array(
					'data_callback' => '__return_empty_array'
				),
			),
		) );

		$this->registry->unregister_endpoint( 'bar' );

		$this->assertEqualSets( array( 'foo' ), array_keys( $this->registry->get_endpoints() ) );
	}

	/**
	 * @covers ::__call()
	 * @throws \EDD_Exception
	 */
	public function test_unregister_endpoint_with_valid_endpoint_id_should_unregister_that_endpoint() {
		// Add a test endpoint.
		$this->registry->register_endpoint( 'foo', array(
			'label' => 'Foo',
			'views' => array(
				'tile' => array(
					'data_callback' => '__return_empty_array'
				),
			)
		) );

		$this->registry->unregister_endpoint( 'foo' );

		$this->assertEqualSets( array(), $this->registry->get_endpoints() );
	}

	/**
	 * @covers ::__call()
	 * @throws \EDD_Exception
	 */
	public function test_get_endpoints_with_no_sort_should_return_endpoints_in_order_of_registration() {

		$this->add_test_endpoints();

		$endpoint_ids = array_keys( $this->registry->get_endpoints() );

		$this->assertEqualSets( array( 'foo', 'bar' ), $endpoint_ids );
	}

	/**
	 * @covers ::__call()
	 * @throws \EDD_Exception
	 */
	public function test_get_endpoints_with_invalid_sort_should_return_endpoints_in_order_of_registration() {

		$this->add_test_endpoints();

		$endpoint_ids = array_keys( $this->registry->get_endpoints( 'fake_sort' ) );

		$this->assertEqualSets( array( 'foo', 'bar' ), $endpoint_ids );
	}

	/**
	 * @covers ::__call()
	 * @throws \EDD_Exception
	 */
	public function test_get_endpoints_with_ID_sort_should_return_endpoints_in_alphabetical_order_by_ID() {

		$this->add_test_endpoints();

		$endpoint_ids = array_keys( $this->registry->get_endpoints( 'ID' ) );

		$this->assertEqualSets( array( 'bar', 'foo' ), $endpoint_ids );
	}

	/**
	 * @covers ::__call()
	 * @throws \EDD_Exception
	 */
	public function test_get_endpoints_with_priority_sort_should_return_endpoints_in_order_of_priority() {

		$this->add_test_endpoints();

		$endpoint_ids = array_keys( $this->registry->get_endpoints( 'priority' ) );

		$this->assertEqualSets( array( 'bar', 'foo' ), $endpoint_ids );
	}

	/**
	 * @covers ::register_endpoint()
	 * @expectedException \EDD_Exception
	 */
	public function test_register_endpoint_with_empty_attributes_should_return_false() {
		$this->assertFalse( $this->registry->register_endpoint( 'foo', array() ) );
	}

	/**
	 * @covers ::register_endpoint()
	 * @group edd_errors
	 * @throws \EDD_Exception
	 */
	public function test_register_endpoint_with_empty_label_should_throw_exception() {
		$this->setExpectedException(
			'\EDD\Reports\Exceptions\Invalid_Parameter',
			"The 'label' parameter for the 'foo' item is missing or invalid in 'EDD\Reports\Registry::validate_attributes'."
		);

		$this->registry->register_endpoint( 'foo', array(
			'label' => ''
		) );
	}

	/**
	 * @covers ::register_endpoint()
	 * @group edd_errors
	 * @throws \EDD_Exception
	 */
	public function test_register_endpoint_with_empty_views_should_throw_exception() {
		$this->setExpectedException(
			'\EDD\Reports\Exceptions\Invalid_Parameter',
			"The 'views' parameter for the 'foo' item is missing or invalid in 'EDD\Reports\Registry::validate_attributes'."
		);

		$added = $this->registry->register_endpoint( 'foo', array(
			'label' => 'Foo',
		) );
	}

	/**
	 * @covers ::register_endpoint()
	 * @throws \EDD_Exception
	 */
	public function test_register_endpoint_with_no_priority_should_set_priority_10() {
		$this->registry->register_endpoint( 'foo', array(
			'label' => 'Foo',
			'views' => array(
				'tile' => array(
					'data_callback' => '__return_empty_array'
				)
			),
		) );

		$report = $this->registry->get_endpoint( 'foo' );

		$this->assertSame( 10, $report['priority'] );
	}

	/**
	 * @covers ::register_endpoint()
	 * @throws \EDD_Exception
	 */
	public function test_register_endpoint_with_priority_should_set_that_priority() {
		$this->registry->register_endpoint( 'foo', array(
			'label'     => 'Foo',
			'priority'  => 15,
			'views'     => array(
				'tile' => array(
					'data_callback' => '__return_empty_array'
				)
			),
		) );

		$report = $this->registry->get_endpoint( 'foo' );

		$this->assertSame( 15, $report['priority'] );
	}

	/**
	 * @covers ::register_endpoint()
	 */
	public function test_register_endpoint_if_successful_should_return_true() {
		$result = $this->registry->register_endpoint( 'foo', array(
			'label' => 'Foo',
			'views' => array(
				'tile' => array(
					'data_callback' => '__return_empty_array',
				),
			),
		) );

		$this->assertTrue( $result );
	}

	/**
	 * @covers ::build_endpoint()
	 */
	public function test_build_endpoint_with_Endpoint_object_should_return_that_object_unchanged() {
		$endpoint = $this->mock_Endpoint( array(
			'view' => 'tile',
			'atts' => array()
		) );

		$this->assertInstanceOf( '\EDD\Reports\Data\Endpoint', $endpoint );
	}

	/**
	 * @covers ::build_endpoint()
	 * @group edd_errors
	 */
	public function test_build_endpoint_with_invalid_endpoint_id_should_return_WP_Error_code_invalid_endpoint() {
		/** @var \WP_Error $result */
		$result = $this->registry->build_endpoint( 'fake', '' );

		$this->assertSame( 'invalid_endpoint', $result->get_error_code() );
	}

	/**
	 * @covers ::build_endpoint()
	 * @throws \EDD_Exception
	 */
	public function test_build_endpoint_with_valid_endpoint_id_valid_type_should_return_an_Endpoint_object() {
		$this->registry->register_endpoint( 'foo', array(
			'label' => 'Foo',
			'views' => array(
				'tile' => array(
					'display_args'     => array( 'some_value' ),
					'display_callback' => '__return_false',
					'data_callback'    => '__return_false',
				),
			),
		) );

		$result = $this->registry->build_endpoint( 'foo', 'tile' );

		$this->assertInstanceOf( 'EDD\Reports\Data\Endpoint', $result );
	}

	/**
	 * @covers ::build_endpoint()
	 * @group edd_errors
	 * @throws \EDD_Exception
	 */
	public function test_build_endpoint_with_valid_endpoint_id_invalid_type_should_return_WP_Error_including_invalid_view_error_code() {
		$this->add_test_endpoints();

		$result = $this->registry->build_endpoint( 'foo', 'fake' );

		$this->assertContains( 'invalid_view', $result->get_error_codes() );
	}

	/**
	 * @covers ::validate_views()
	 * @throws \EDD_Exception
	 */
	public function test_validate_views_with_invalid_view_should_throw_exception() {
		$this->setExpectedException(
			'\EDD\Reports\Exceptions\Invalid_View',
			"The 'fake' view for the 'foo' item is missing or invalid in 'EDD\Reports\Data\Endpoint_Registry::validate_views'"
		);

		$this->registry->register_endpoint( 'foo', array(
			'label'    => 'Foo',
			'views'    => array(
				'fake' => array(
					'display_callback' => '__return_false',
					'data_callback'    => '__return_false',
				),
			)
		) );
	}


	/**
	 * Adds two test endpoints for use with get_endpoints() tests.
	 *
	 * @throws \EDD_Exception
	 */
	protected function add_test_endpoints() {
		$this->registry->register_endpoint( 'foo', array(
			'label'    => 'Foo',
			'priority' => 10,
			'views'    => array(
				'tile' => array(
					'data_callback' => '__return_empty_array'
				),
			)
		) );

		$this->registry->register_endpoint( 'bar', array(
			'label'    => 'Bar',
			'priority' => 5,
			'views'    => array(
				'tile' => array(
					'data_callback' => '__return_empty_array'
				),
			)
		) );
	}

	/**
	 * Mocks a copy of the Endpoint abstract class.
	 *
	 * @param array $args
	 * @return \EDD\Reports\Data\Endpoint Mocked Endpoint instance.
	 */
	protected function mock_Endpoint( $args ) {
		return $this->getMockForAbstractClass( '\EDD\Reports\Data\Endpoint', array( $args ) );
	}

}
