<?php
/**
 * Tests for Gateway Pie Chart Endpoints
 *
 * @group edd_reports
 * @group edd_endpoints
 * @group edd_pie_charts
 * @group edd_gateways
 *
 * @package   EDD\Tests\Reports\Endpoints
 * @copyright Copyright (c) 2026, Easy Digital Downloads, LLC
 * @license   https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since     3.6.4
 */

namespace EDD\Tests\Reports\Endpoints;

use EDD\Reports\Endpoints\Pies\GatewaySales;
use EDD\Reports\Endpoints\Pies\GatewayEarnings;
use EDD\Reports\Endpoints\Pies\Pie;
use EDD\Tests\PHPUnit\EDD_UnitTestCase;

/**
 * Tests for Gateway Pie Chart Endpoints.
 *
 * @since 3.6.4
 * @covers \EDD\Reports\Endpoints\Pies\GatewaySales
 * @covers \EDD\Reports\Endpoints\Pies\GatewayEarnings
 * @covers \EDD\Reports\Endpoints\Pies\Traits\Gateway
 */
class GatewayPies extends EDD_UnitTestCase {

	/**
	 * Mock reports registry for testing.
	 *
	 * @since 3.6.4
	 * @var object
	 */
	protected $mock_reports;

	/**
	 * Set up each test.
	 *
	 * @since 3.6.4
	 */
	public function setUp(): void {
		parent::setUp();

		// Create a mock reports registry
		$this->mock_reports = $this->createMock( \EDD\Reports\Data\Report_Registry::class );
	}

	/**
	 * Tear down each test.
	 *
	 * @since 3.6.4
	 */
	public function tearDown(): void {
		parent::tearDown();
	}

	/**
	 * Skip test if PHPUnit version doesn't support onlyMethods().
	 *
	 * The onlyMethods() method was introduced in PHPUnit 8.0. This helper
	 * allows tests using modern mocking patterns to be skipped on older
	 * PHPUnit versions (e.g., PHPUnit 7.x used with PHP 7.4).
	 *
	 * @since 3.6.4
	 * @return void
	 */
	private function skip_if_no_only_methods(): void {
		if ( ! method_exists( \PHPUnit\Framework\MockObject\MockBuilder::class, 'onlyMethods' ) ) {
			$this->markTestSkipped( 'This test requires PHPUnit 8.0+ (onlyMethods() not available).' );
		}
	}

	/**
	 * @covers \EDD\Reports\Endpoints\Pies\GatewaySales::__construct
	 */
	public function test_gateway_sales_initialization() {
		$pie = new GatewaySales( $this->mock_reports );

		$this->assertInstanceOf( GatewaySales::class, $pie );
		$this->assertInstanceOf( Pie::class, $pie );

		// Test that key is set correctly
		$reflection = new \ReflectionClass( $pie );
		$key_prop = $reflection->getProperty( 'key' );
		$key_prop->setAccessible( true );
		$this->assertSame( 'sales', $key_prop->getValue( $pie ) );
	}

	/**
	 * @covers \EDD\Reports\Endpoints\Pies\GatewaySales::get_id
	 */
	public function test_gateway_sales_get_id() {
		$pie = new GatewaySales( $this->mock_reports );

		$id = $this->invokeMethod( $pie, 'get_id' );
		$this->assertSame( 'gateway_sales_breakdown', $id );
	}

	/**
	 * @covers \EDD\Reports\Endpoints\Pies\GatewaySales::get_label
	 */
	public function test_gateway_sales_get_label() {
		$pie = new GatewaySales( $this->mock_reports );

		$label = $this->invokeMethod( $pie, 'get_label' );
		$this->assertSame( 'Gateway Sales', $label );
	}

	/**
	 * @covers \EDD\Reports\Endpoints\Pies\GatewayEarnings::__construct
	 */
	public function test_gateway_earnings_initialization() {
		$pie = new GatewayEarnings( $this->mock_reports );

		$this->assertInstanceOf( GatewayEarnings::class, $pie );
		$this->assertInstanceOf( Pie::class, $pie );

		// Test that key is set correctly
		$reflection = new \ReflectionClass( $pie );
		$key_prop = $reflection->getProperty( 'key' );
		$key_prop->setAccessible( true );
		$this->assertSame( 'earnings', $key_prop->getValue( $pie ) );
	}

	/**
	 * @covers \EDD\Reports\Endpoints\Pies\GatewayEarnings::get_id
	 */
	public function test_gateway_earnings_get_id() {
		$pie = new GatewayEarnings( $this->mock_reports );

		$id = $this->invokeMethod( $pie, 'get_id' );
		$this->assertSame( 'gateway_earnings_breakdown', $id );
	}

	/**
	 * @covers \EDD\Reports\Endpoints\Pies\GatewayEarnings::get_label
	 */
	public function test_gateway_earnings_get_label() {
		$pie = new GatewayEarnings( $this->mock_reports );

		$label = $this->invokeMethod( $pie, 'get_label' );
		$this->assertSame( 'Gateway Earnings', $label );
	}

	/**
	 * @covers \EDD\Reports\Endpoints\Pies\GatewaySales::get_query_results
	 */
	public function test_gateway_sales_get_query_results_calls_stats() {
		$pie = new GatewaySales( $this->mock_reports );

		// Set up some test data
		$reflection = new \ReflectionClass( $pie );
		$dates_prop = $reflection->getProperty( 'dates' );
		$dates_prop->setAccessible( true );
		$dates_prop->setValue( $pie, array( 'range' => 'this_month' ) );

		$currency_prop = $reflection->getProperty( 'currency' );
		$currency_prop->setAccessible( true );
		$currency_prop->setValue( $pie, 'USD' );

		$results = $this->invokeMethod( $pie, 'get_query_results' );

		// Results should be an array (even if empty)
		$this->assertIsArray( $results );
	}

	/**
	 * @covers \EDD\Reports\Endpoints\Pies\GatewayEarnings::get_query_results
	 */
	public function test_gateway_earnings_get_query_results_calls_stats() {
		$pie = new GatewayEarnings( $this->mock_reports );

		// Set up some test data
		$reflection = new \ReflectionClass( $pie );
		$dates_prop = $reflection->getProperty( 'dates' );
		$dates_prop->setAccessible( true );
		$dates_prop->setValue( $pie, array( 'range' => 'this_month' ) );

		$currency_prop = $reflection->getProperty( 'currency' );
		$currency_prop->setAccessible( true );
		$currency_prop->setValue( $pie, 'USD' );

		$exclude_taxes_prop = $reflection->getProperty( 'exclude_taxes' );
		$exclude_taxes_prop->setAccessible( true );
		$exclude_taxes_prop->setValue( $pie, false );

		$results = $this->invokeMethod( $pie, 'get_query_results' );

		// Results should be an array (even if empty)
		$this->assertIsArray( $results );
	}

	/**
	 * @covers \EDD\Reports\Endpoints\Pies\Traits\Gateway::format_breakdown_label
	 */
	public function test_gateway_trait_format_breakdown_label() {
		$pie = new GatewaySales( $this->mock_reports );

		$label = $this->invokeMethod( $pie, 'format_breakdown_label', array( 'paypal_commerce' ) );
		$this->assertSame( 'PayPal', $label );

		$label = $this->invokeMethod( $pie, 'format_breakdown_label', array( 'manual' ) );
		$this->assertSame( 'Store Gateway', $label );

		$label = $this->invokeMethod( $pie, 'format_breakdown_label', array( 'stripe' ) );
		$this->assertSame( 'Stripe', $label );
	}

	/**
	 * @covers \EDD\Reports\Endpoints\Pies\Traits\Gateway::get_labels
	 */
	public function test_gateway_trait_get_labels() {
		$this->skip_if_no_only_methods();

		// Set up test query results
		$test_results = array(
			(object) array( 'gateway' => 'paypal_commerce', 'total' => 10 ),
			(object) array( 'gateway' => 'stripe', 'total' => 5 ),
		);

		// Create a mock that returns our test data
		$pie = $this->getMockBuilder( GatewaySales::class )
			->setConstructorArgs( array( $this->mock_reports ) )
			->onlyMethods( array( 'get_query_results' ) )
			->getMock();

		$pie->method( 'get_query_results' )
			->willReturn( $test_results );

		// Use public API - this processes results and gets labels
		$chart_options = $this->invokeMethod( $pie, 'get_chart_options' );

		$this->assertIsArray( $chart_options );
		$this->assertArrayHasKey( 'labels', $chart_options );
		$this->assertContains( 'PayPal', $chart_options['labels'] );
		$this->assertContains( 'Stripe', $chart_options['labels'] );
	}

	/**
	 * @covers \EDD\Reports\Endpoints\Pies\Traits\Gateway::process_results
	 */
	public function test_gateway_sales_process_results_filters_zero_values() {
		$pie = new GatewaySales( $this->mock_reports );

		// Test data with some zero values
		$test_results = array(
			(object) array( 'gateway' => 'paypal_commerce', 'total' => 10 ),
			(object) array( 'gateway' => 'manual', 'total' => 0 ),
			(object) array( 'gateway' => 'stripe', 'total' => 5 ),
		);

		$processed = $this->invokeMethod( $pie, 'process_results', array( $test_results ) );

		// Should only include gateways with values > 0
		$this->assertArrayHasKey( 'paypal_commerce', $processed );
		$this->assertArrayHasKey( 'stripe', $processed );
		$this->assertArrayNotHasKey( 'manual', $processed );

		// Check values
		$this->assertSame( 10, $processed['paypal_commerce'] );
		$this->assertSame( 5, $processed['stripe'] );
	}

	/**
	 * @covers \EDD\Reports\Endpoints\Pies\Traits\Gateway::process_results
	 */
	public function test_gateway_earnings_process_results_filters_zero_values() {
		$pie = new GatewayEarnings( $this->mock_reports );

		// Test data with some zero values
		$test_results = array(
			(object) array( 'gateway' => 'paypal_commerce', 'earnings' => 100.50 ),
			(object) array( 'gateway' => 'manual', 'earnings' => 0.00 ),
			(object) array( 'gateway' => 'stripe', 'earnings' => 50.25 ),
		);

		$processed = $this->invokeMethod( $pie, 'process_results', array( $test_results ) );

		// Should only include gateways with values > 0
		$this->assertArrayHasKey( 'paypal_commerce', $processed );
		$this->assertArrayHasKey( 'stripe', $processed );
		$this->assertArrayNotHasKey( 'manual', $processed );

		// Check values (should be floats for earnings)
		$this->assertSame( 100.50, $processed['paypal_commerce'] );
		$this->assertSame( 50.25, $processed['stripe'] );
	}

	/**
	 * @covers \EDD\Reports\Endpoints\Pies\Traits\Gateway::process_results
	 */
	public function test_gateway_sales_process_results_uses_total_key() {
		$pie = new GatewaySales( $this->mock_reports );

		$test_results = array(
			(object) array( 'gateway' => 'paypal_commerce', 'total' => 10, 'earnings' => 100 ),
		);

		$processed = $this->invokeMethod( $pie, 'process_results', array( $test_results ) );

		// Sales should use 'total' key, not 'earnings'
		$this->assertSame( 10, $processed['paypal_commerce'] );
	}

	/**
	 * @covers \EDD\Reports\Endpoints\Pies\Traits\Gateway::process_results
	 */
	public function test_gateway_earnings_process_results_uses_earnings_key() {
		$pie = new GatewayEarnings( $this->mock_reports );

		$test_results = array(
			(object) array( 'gateway' => 'paypal_commerce', 'total' => 10, 'earnings' => 100.50 ),
		);

		$processed = $this->invokeMethod( $pie, 'process_results', array( $test_results ) );

		// Earnings should use 'earnings' key, not 'total'
		$this->assertSame( 100.50, $processed['paypal_commerce'] );
	}

	/**
	 * @covers \EDD\Reports\Endpoints\Pies\Traits\Gateway::process_results
	 */
	public function test_gateway_sales_process_results_returns_integers() {
		$pie = new GatewaySales( $this->mock_reports );

		$test_results = array(
			(object) array( 'gateway' => 'paypal_commerce', 'total' => '10' ), // String value
		);

		$processed = $this->invokeMethod( $pie, 'process_results', array( $test_results ) );

		// Sales should be integers
		$this->assertIsInt( $processed['paypal_commerce'] );
		$this->assertSame( 10, $processed['paypal_commerce'] );
	}

	/**
	 * @covers \EDD\Reports\Endpoints\Pies\Traits\Gateway::process_results
	 */
	public function test_gateway_earnings_process_results_returns_floats() {
		$pie = new GatewayEarnings( $this->mock_reports );

		$test_results = array(
			(object) array( 'gateway' => 'paypal_commerce', 'earnings' => '100.50' ), // String value
		);

		$processed = $this->invokeMethod( $pie, 'process_results', array( $test_results ) );

		// Earnings should be floats
		$this->assertIsFloat( $processed['paypal_commerce'] );
		$this->assertSame( 100.50, $processed['paypal_commerce'] );
	}

	/**
	 * @covers \EDD\Reports\Endpoints\Pies\Traits\Gateway::process_results
	 */
	public function test_gateway_process_results_groups_small_percentages() {
		$pie = new GatewaySales( $this->mock_reports );

		// Set min_percentage to 10% for testing
		$reflection = new \ReflectionClass( $pie );
		$min_percentage_prop = $reflection->getProperty( 'min_percentage' );
		$min_percentage_prop->setAccessible( true );
		$min_percentage_prop->setValue( $pie, 10.0 );

		// Test data: 100 total sales, one gateway has 3% (should be grouped into Other)
		$test_results = array(
			(object) array( 'gateway' => 'paypal_commerce', 'total' => 90 ), // 90%
			(object) array( 'gateway' => 'manual', 'total' => 7 ),  // 7%
			(object) array( 'gateway' => 'stripe', 'total' => 3 ),  // 3% - should be grouped
		);

		$processed = $this->invokeMethod( $pie, 'process_results', array( $test_results ) );

		// paypal should be present
		$this->assertArrayHasKey( 'paypal_commerce', $processed );
		$this->assertSame( 90, $processed['paypal_commerce'] );

		// manual should be grouped (7% is below 10%)
		$this->assertArrayNotHasKey( 'manual', $processed );

		// stripe should be grouped into "Other"
		$this->assertArrayNotHasKey( 'stripe', $processed );

		// "Other" should exist with combined value
		$this->assertArrayHasKey( 'Other', $processed );
		$this->assertSame( 10, $processed['Other'] ); // 7 + 3
	}

	/**
	 * @covers \EDD\Reports\Endpoints\Pies\Traits\Gateway::process_results
	 */
	public function test_gateway_process_results_groups_excess_pieces() {
		$pie = new GatewaySales( $this->mock_reports );

		// Set max_pieces to 3 for testing
		$reflection = new \ReflectionClass( $pie );
		$max_pieces_prop = $reflection->getProperty( 'max_pieces' );
		$max_pieces_prop->setAccessible( true );
		$max_pieces_prop->setValue( $pie, 3 );

		// Disable percentage grouping
		$min_percentage_prop = $reflection->getProperty( 'min_percentage' );
		$min_percentage_prop->setAccessible( true );
		$min_percentage_prop->setValue( $pie, 0.0 );

		// Test data with 5 gateways (exceeds max of 3)
		$test_results = array(
			(object) array( 'gateway' => 'gateway_1', 'total' => 50 ),
			(object) array( 'gateway' => 'gateway_2', 'total' => 30 ),
			(object) array( 'gateway' => 'gateway_3', 'total' => 10 ),
			(object) array( 'gateway' => 'gateway_4', 'total' => 5 ),
			(object) array( 'gateway' => 'gateway_5', 'total' => 5 ),
		);

		$processed = $this->invokeMethod( $pie, 'process_results', array( $test_results ) );

		// Should have exactly 3 pieces (top 2 + "Other")
		$this->assertCount( 3, $processed );

		// Top 2 should be present
		$this->assertArrayHasKey( 'gateway_1', $processed );
		$this->assertArrayHasKey( 'gateway_2', $processed );

		// "Other" should exist with combined smaller values
		$this->assertArrayHasKey( 'Other', $processed );
		$this->assertSame( 20, $processed['Other'] ); // 10 + 5 + 5
	}

	/**
	 * @covers \EDD\Reports\Endpoints\Pies\GatewaySales::get_data_for_callback
	 */
	public function test_gateway_sales_get_data_for_callback_format() {
		$pie = new GatewaySales( $this->mock_reports );

		$data = $pie->get_data_for_callback();

		$this->assertIsArray( $data );
		$this->assertArrayHasKey( 'sales', $data );
		$this->assertIsArray( $data['sales'] );
	}

	/**
	 * @covers \EDD\Reports\Endpoints\Pies\GatewayEarnings::get_data_for_callback
	 */
	public function test_gateway_earnings_get_data_for_callback_format() {
		$pie = new GatewayEarnings( $this->mock_reports );

		$data = $pie->get_data_for_callback();

		$this->assertIsArray( $data );
		$this->assertArrayHasKey( 'earnings', $data );
		$this->assertIsArray( $data['earnings'] );
	}

	/**
	 * Test that chart options include gateway-specific labels.
	 *
	 * @covers \EDD\Reports\Endpoints\Pies\Pie::get_chart_options
	 */
	public function test_gateway_chart_options_include_labels() {
		$this->skip_if_no_only_methods();

		// Set up test query results
		$test_results = array(
			(object) array( 'gateway' => 'paypal_commerce', 'total' => 10 ),
			(object) array( 'gateway' => 'stripe', 'total' => 5 ),
		);

		// Create a mock that returns our test data
		$pie = $this->getMockBuilder( GatewaySales::class )
			->setConstructorArgs( array( $this->mock_reports ) )
			->onlyMethods( array( 'get_query_results' ) )
			->getMock();

		$pie->method( 'get_query_results' )
			->willReturn( $test_results );

		// Use public API to get chart options
		$options = $this->invokeMethod( $pie, 'get_chart_options' );

		$this->assertIsArray( $options );
		$this->assertArrayHasKey( 'labels', $options );
		$this->assertContains( 'PayPal', $options['labels'] );
		$this->assertContains( 'Stripe', $options['labels'] );
	}

	/**
	 * Test that datasets are configured correctly for sales.
	 *
	 * @covers \EDD\Reports\Endpoints\Pies\Pie::get_datasets
	 */
	public function test_gateway_sales_datasets_configuration() {
		$pie = new GatewaySales( $this->mock_reports );

		$datasets = $this->invokeMethod( $pie, 'get_datasets' );

		$this->assertIsArray( $datasets );
		$this->assertArrayHasKey( 'sales', $datasets );
		$this->assertSame( 'number', $datasets['sales']['type'] );
	}

	/**
	 * Test that datasets are configured correctly for earnings.
	 *
	 * @covers \EDD\Reports\Endpoints\Pies\Pie::get_datasets
	 */
	public function test_gateway_earnings_datasets_configuration() {
		$pie = new GatewayEarnings( $this->mock_reports );

		$datasets = $this->invokeMethod( $pie, 'get_datasets' );

		$this->assertIsArray( $datasets );
		$this->assertArrayHasKey( 'earnings', $datasets );
		$this->assertSame( 'currency', $datasets['earnings']['type'] );
	}

	/**
	 * Test that empty results are handled gracefully.
	 *
	 * @covers \EDD\Reports\Endpoints\Pies\Traits\Gateway::process_results
	 */
	public function test_gateway_process_results_handles_empty_results() {
		$pie = new GatewaySales( $this->mock_reports );

		$processed = $this->invokeMethod( $pie, 'process_results', array( array() ) );

		$this->assertIsArray( $processed );
		$this->assertEmpty( $processed );
	}

	/**
	 * Test the other pieces breakdown functionality.
	 *
	 * @covers \EDD\Reports\Endpoints\Pies\Pie::get_other_pieces_breakdown
	 */
	public function test_gateway_other_pieces_breakdown() {
		$this->skip_if_no_only_methods();

		// Test data with 4 gateways
		$test_results = array(
			(object) array( 'gateway' => 'stripe', 'total' => 50 ),
			(object) array( 'gateway' => 'paypal_commerce', 'total' => 30 ),
			(object) array( 'gateway' => 'manual', 'total' => 10 ),
			(object) array( 'gateway' => 'amazon', 'total' => 10 ),
		);

		// Create a mock that returns our test data
		$pie = $this->getMockBuilder( GatewaySales::class )
			->setConstructorArgs( array( $this->mock_reports ) )
			->onlyMethods( array( 'get_query_results' ) )
			->getMock();

		$pie->method( 'get_query_results' )
			->willReturn( $test_results );

		// Set max_pieces to 2 to force grouping
		$reflection = new \ReflectionClass( $pie );
		$max_pieces_prop = $reflection->getProperty( 'max_pieces' );
		$max_pieces_prop->setAccessible( true );
		$max_pieces_prop->setValue( $pie, 2 );

		// Disable percentage grouping
		$min_percentage_prop = $reflection->getProperty( 'min_percentage' );
		$min_percentage_prop->setAccessible( true );
		$min_percentage_prop->setValue( $pie, 0.0 );

		// Use public API to get chart options which includes the breakdown
		$options = $this->invokeMethod( $pie, 'get_chart_options' );

		$this->assertIsArray( $options );
		$this->assertArrayHasKey( 'otherBreakdown', $options );

		$breakdown = $options['otherBreakdown'];
		$this->assertIsArray( $breakdown );
		$this->assertNotEmpty( $breakdown );

		// Smaller gateways should be in the breakdown with formatted labels
		$this->assertArrayHasKey( 'Store Gateway', $breakdown );
		$this->assertArrayHasKey( 'Amazon', $breakdown );
	}

	/**
	 * Helper method to invoke protected/private methods for testing.
	 *
	 * @param object $object The object to invoke the method on.
	 * @param string $method The method name.
	 * @param array  $args   Method arguments.
	 * @return mixed
	 */
	protected function invokeMethod( $object, $method, array $args = array() ) {
		$reflection = new \ReflectionClass( get_class( $object ) );
		$method = $reflection->getMethod( $method );
		$method->setAccessible( true );

		return $method->invokeArgs( $object, $args );
	}
}
