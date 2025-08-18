<?php
/**
 * Tests for Reports Endpoints
 *
 * @group edd_reports
 * @group edd_endpoints
 *
 * @package   EDD\Tests\Reports
 * @copyright Copyright (c) 2025, Easy Digital Downloads, LLC
 * @license   https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since     3.5.1
 */

namespace EDD\Tests\Reports;

use EDD\Reports\Endpoints\Endpoint;
use EDD\Reports\Endpoints\Tiles\Tile;
use EDD\Reports\Endpoints\Charts\Graph;
use EDD\Reports\Endpoints\Pies\Pie;
use EDD\Reports\Endpoints\Traits\Colors;
use EDD\Tests\PHPUnit\EDD_UnitTestCase;

/**
 * Tests for Reports Endpoints.
 *
 * @since 3.5.1
 * @covers \EDD\Reports\Endpoints\Endpoint
 * @covers \EDD\Reports\Endpoints\Tiles\Tile
 * @covers \EDD\Reports\Endpoints\Charts\Graph
 * @covers \EDD\Reports\Endpoints\Pies\Pie
 * @covers \EDD\Reports\Endpoints\Traits\Colors
 */
class Endpoints extends EDD_UnitTestCase {

	/**
	 * Mock reports registry for testing.
	 *
	 * @since 3.5.1
	 * @var object
	 */
	protected $mock_reports;

	/**
	 * Set up each test.
	 *
	 * @since 3.5.1
	 */
	public function setUp(): void {
		parent::setUp();

		// Create a mock reports registry
		$this->mock_reports = $this->createMock( \EDD\Reports\Data\Report_Registry::class );
	}

	/**
	 * @covers \EDD\Reports\Endpoints\Endpoint::__construct
	 * @covers \EDD\Reports\Endpoints\Endpoint::initialize_properties
	 */
	public function test_endpoint_base_initialization() {
		$endpoint = $this->getMockForAbstractClass(
			Endpoint::class,
			array( $this->mock_reports )
		);

		$this->assertInstanceOf( Endpoint::class, $endpoint );

		// Test that properties are initialized correctly
		$reflection = new \ReflectionClass( $endpoint );
		$reports_prop = $reflection->getProperty( 'reports' );
		$reports_prop->setAccessible( true );
		$this->assertSame( $this->mock_reports, $reports_prop->getValue( $endpoint ) );

		// Test that dates, currency, and exclude_taxes properties are set
		$dates_prop = $reflection->getProperty( 'dates' );
		$dates_prop->setAccessible( true );
		$this->assertNotNull( $dates_prop->getValue( $endpoint ) );

		$currency_prop = $reflection->getProperty( 'currency' );
		$currency_prop->setAccessible( true );
		$this->assertNotNull( $currency_prop->getValue( $endpoint ) );

		$exclude_taxes_prop = $reflection->getProperty( 'exclude_taxes' );
		$exclude_taxes_prop->setAccessible( true );
		$this->assertIsBool( $exclude_taxes_prop->getValue( $endpoint ) );
	}

	/**
	 * @covers \EDD\Reports\Endpoints\Endpoint::get_download_data
	 */
	public function test_endpoint_get_download_data() {
		$endpoint = $this->getMockForAbstractClass(
			Endpoint::class,
			array( $this->mock_reports )
		);

		// Test with no filter
		$_GET = array();
		$result = $this->invokeMethod( $endpoint, 'get_download_data' );
		$this->assertFalse( $result );

		// Test with 'all' filter
		$_GET['products'] = 'all';
		$result = $this->invokeMethod( $endpoint, 'get_download_data' );
		$this->assertFalse( $result );
	}

	/**
	 * @covers \EDD\Reports\Endpoints\Endpoint::get_gateway
	 */
	public function test_endpoint_get_gateway() {
		$endpoint = $this->getMockForAbstractClass(
			Endpoint::class,
			array( $this->mock_reports )
		);

		// Test default gateway
		$result = $this->invokeMethod( $endpoint, 'get_gateway' );
		$this->assertIsString( $result );
	}

	/**
	 * @covers \EDD\Reports\Endpoints\Endpoint::get_currency_sql
	 */
	public function test_endpoint_get_currency_sql() {
		$endpoint = $this->getMockForAbstractClass(
			Endpoint::class,
			array( $this->mock_reports )
		);

		// Test with no currency filter
		$reflection = new \ReflectionClass( $endpoint );
		$currency_prop = $reflection->getProperty( 'currency' );
		$currency_prop->setAccessible( true );
		$currency_prop->setValue( $endpoint, '' );

		$result = $this->invokeMethod( $endpoint, 'get_currency_sql' );
		$this->assertSame( '', $result );

		// Test with valid currency
		$currency_prop->setValue( $endpoint, 'USD' );
		$result = $this->invokeMethod( $endpoint, 'get_currency_sql' );
		$this->assertStringContainsString( 'currency = \'USD\'', $result );
	}

	/**
	 * @covers \EDD\Reports\Endpoints\Endpoint::get_discount_data
	 */
	public function test_endpoint_get_discount_data() {
		$endpoint = $this->getMockForAbstractClass(
			Endpoint::class,
			array( $this->mock_reports )
		);

		// Test with no discount
		$_GET = array();
		$result = $this->invokeMethod( $endpoint, 'get_discount_data' );
		$this->assertFalse( $result );

		// Test with 'all' discount
		$_GET['discounts'] = 'all';
		$result = $this->invokeMethod( $endpoint, 'get_discount_data' );
		$this->assertFalse( $result );
	}

	/**
	 * @covers \EDD\Reports\Endpoints\Tiles\Tile::__construct
	 * @covers \EDD\Reports\Endpoints\Tiles\Tile::get_display_args
	 */
	public function test_tile_initialization() {
		$tile = $this->getMockForAbstractClass(
			Tile::class,
			array( $this->mock_reports )
		);

		$this->assertInstanceOf( Tile::class, $tile );
		$this->assertInstanceOf( Endpoint::class, $tile );

		// Test display args
		$display_args = $this->invokeMethod( $tile, 'get_display_args' );
		$this->assertIsArray( $display_args );
		$this->assertArrayHasKey( 'comparison_label', $display_args );
	}

	/**
	 * @covers \EDD\Reports\Endpoints\Tiles\Tile::get_stats
	 */
	public function test_tile_get_stats() {
		$tile = $this->getMockForAbstractClass(
			Tile::class,
			array( $this->mock_reports )
		);

		$stats = $this->invokeMethod( $tile, 'get_stats' );
		$this->assertInstanceOf( \EDD\Stats::class, $stats );

		// Test with additional args
		$additional_args = array( 'revenue_type' => 'net' );
		$stats = $this->invokeMethod( $tile, 'get_stats', array( $additional_args ) );
		$this->assertInstanceOf( \EDD\Stats::class, $stats );
	}

	/**
	 * @covers \EDD\Reports\Endpoints\Tiles\Tile::format_output
	 */
	public function test_tile_format_output() {
		$tile = $this->getMockForAbstractClass(
			Tile::class,
			array( $this->mock_reports )
		);

		// Test with plain text
		$result = $this->invokeMethod( $tile, 'format_output', array( 'Test Value' ) );
		$this->assertSame( 'Test Value', $result );

		// Test with HTML (should return as-is)
		$html_value = '<span>$100</span>';
		$result = $this->invokeMethod( $tile, 'format_output', array( $html_value ) );
		$this->assertSame( $html_value, $result );

		// Test with currency symbol (should return as-is)
		$currency_value = '$100.00';
		$result = $this->invokeMethod( $tile, 'format_output', array( $currency_value ) );
		$this->assertSame( $currency_value, $result );

		// Test with dangerous content (should be escaped)
		$dangerous_value = '<script>alert("xss")</script>';
		$result = $this->invokeMethod( $tile, 'format_output', array( $dangerous_value ) );
		$this->assertStringNotContainsString( '<script>', $result );
	}

	/**
	 * @covers \EDD\Reports\Endpoints\Tiles\Tile::get_gateway
	 */
	public function test_tile_get_gateway() {
		$tile = $this->getMockForAbstractClass(
			Tile::class,
			array( $this->mock_reports )
		);

		// Test with no gateway filter
		$_GET = array();
		$result = $this->invokeMethod( $tile, 'get_gateway' );
		$this->assertSame( '', $result );

		// Test with 'all' gateway
		$_GET['gateways'] = 'all';
		$result = $this->invokeMethod( $tile, 'get_gateway' );
		$this->assertSame( 'all', $result );

		// Test with specific gateway
		$_GET['gateways'] = 'stripe';
		$result = $this->invokeMethod( $tile, 'get_gateway' );
		$this->assertSame( 'stripe', $result );
	}

	/**
	 * @covers \EDD\Reports\Endpoints\Charts\Graph::__construct
	 */
	public function test_graph_initialization() {
		$graph = $this->getMockForAbstractClass(
			Graph::class,
			array( $this->mock_reports )
		);

		$this->assertInstanceOf( Graph::class, $graph );
		$this->assertInstanceOf( Endpoint::class, $graph );

		// Test that chart_type is set correctly
		$reflection = new \ReflectionClass( $graph );
		$chart_type_prop = $reflection->getProperty( 'chart_type' );
		$chart_type_prop->setAccessible( true );
		$this->assertSame( 'line', $chart_type_prop->getValue( $graph ) );
	}

	/**
	 * @covers \EDD\Reports\Endpoints\Pies\Pie::__construct
	 */
	public function test_pie_initialization() {
		$pie = $this->getMockForAbstractClass(
			Pie::class,
			array( $this->mock_reports )
		);

		$this->assertInstanceOf( Pie::class, $pie );
		$this->assertInstanceOf( Endpoint::class, $pie );

		// Test that chart_type is set correctly
		$reflection = new \ReflectionClass( $pie );
		$chart_type_prop = $reflection->getProperty( 'chart_type' );
		$chart_type_prop->setAccessible( true );
		$this->assertSame( 'pie', $chart_type_prop->getValue( $pie ) );

		// Test cutout percentage default
		$cutout_prop = $reflection->getProperty( 'cutout_percentage' );
		$cutout_prop->setAccessible( true );
		$this->assertSame( 0, $cutout_prop->getValue( $pie ) );
	}

	/**
	 * @covers \EDD\Reports\Endpoints\Traits\Colors
	 */
	public function test_colors_trait() {
		$class = new class() {
			use Colors;

			public function get_test_colors() {
				return $this->get_colors();
			}

			public function get_earnings_color() {
				return $this->earnings;
			}

			public function get_sales_color() {
				return $this->sales;
			}
		};

		// Test that predefined colors exist
		$this->assertStringContainsString( 'rgba', $class->get_earnings_color() );
		$this->assertStringContainsString( 'rgba', $class->get_sales_color() );

		// Test that get_colors returns an array
		$colors = $class->get_test_colors();
		$this->assertIsArray( $colors );
		$this->assertNotEmpty( $colors );

		// Test that colors are in RGB format
		foreach ( $colors as $color ) {
			$this->assertStringStartsWith( 'rgb(', $color );
			$this->assertStringEndsWith( ')', $color );
		}
	}

	/**
	 * Test concrete tile implementation registration.
	 *
	 * @covers \EDD\Reports\Endpoints\Tiles\Sales
	 */
	public function test_concrete_tile_registration() {
		// Mock the reports registry to expect register_endpoint call
		$this->mock_reports->expects( $this->once() )
			->method( 'register_endpoint' )
			->with(
				$this->equalTo( 'overview_sales' ),
				$this->arrayHasKey( 'label' )
			);

		// Create a real Sales tile to test concrete implementation
		$sales_tile = new \EDD\Reports\Endpoints\Tiles\Sales( $this->mock_reports );
		$this->assertInstanceOf( \EDD\Reports\Endpoints\Tiles\Sales::class, $sales_tile );
	}

	/**
	 * Test concrete chart implementation registration.
	 *
	 * @covers \EDD\Reports\Endpoints\Charts\Sales
	 */
	public function test_concrete_chart_registration() {
		// Mock the reports registry to expect register_endpoint call
		$this->mock_reports->expects( $this->once() )
			->method( 'register_endpoint' )
			->with(
				$this->equalTo( 'overview_sales_chart' ),
				$this->arrayHasKey( 'label' )
			);

		// Create a real Sales chart to test concrete implementation
		$sales_chart = new \EDD\Reports\Endpoints\Charts\Sales( $this->mock_reports );
		$this->assertInstanceOf( \EDD\Reports\Endpoints\Charts\Sales::class, $sales_chart );
	}

	/**
	 * Test that get_data_for_callback handles exceptions gracefully.
	 *
	 * @covers \EDD\Reports\Endpoints\Tiles\Tile::get_data_for_callback
	 */
	public function test_tile_callback_exception_handling() {
		$tile = $this->getMockForAbstractClass(
			Tile::class,
			array( $this->mock_reports )
		);

		// Mock get_data to throw an exception
		$tile->method( 'get_data' )
			->willThrowException( new \Exception( 'Test exception' ) );

		// Should return empty string on exception
		$result = $tile->get_data_for_callback();
		$this->assertSame( '', $result );
	}

	/**
	 * Test endpoint with secondary context.
	 *
	 * @covers \EDD\Reports\Endpoints\Tiles\Tile::get_display_args
	 */
	public function test_tile_secondary_context() {
		$tile = $this->getMockForAbstractClass(
			Tile::class,
			array( $this->mock_reports )
		);

		// Set context to secondary
		$reflection = new \ReflectionClass( $tile );
		$context_prop = $reflection->getProperty( 'context' );
		$context_prop->setAccessible( true );
		$context_prop->setValue( $tile, 'secondary' );

		$display_args = $this->invokeMethod( $tile, 'get_display_args' );
		$this->assertArrayHasKey( 'context', $display_args );
		$this->assertSame( 'secondary', $display_args['context'] );
	}

	/**
	 * Test endpoint with primary context (default).
	 *
	 * @covers \EDD\Reports\Endpoints\Tiles\Tile::get_display_args
	 */
	public function test_tile_primary_context() {
		$tile = $this->getMockForAbstractClass(
			Tile::class,
			array( $this->mock_reports )
		);

		// Context should default to primary and not be included in display args
		$display_args = $this->invokeMethod( $tile, 'get_display_args' );
		$this->assertArrayNotHasKey( 'context', $display_args );
	}

	/**
	 * Test endpoint inheritance hierarchy.
	 */
	public function test_endpoint_inheritance() {
		// Test that Tile extends Endpoint
		$this->assertTrue( is_subclass_of( Tile::class, Endpoint::class ) );

		// Test that Graph extends Endpoint
		$this->assertTrue( is_subclass_of( Graph::class, Endpoint::class ) );

		// Test that Pie extends Endpoint
		$this->assertTrue( is_subclass_of( Pie::class, Endpoint::class ) );

		// Test concrete implementations
		$this->assertTrue( is_subclass_of( \EDD\Reports\Endpoints\Tiles\Sales::class, Tile::class ) );
		$this->assertTrue( is_subclass_of( \EDD\Reports\Endpoints\Charts\Sales::class, Graph::class ) );
	}

	/**
	 * Test that all abstract methods are properly defined.
	 */
	public function test_abstract_methods_defined() {
		$endpoint_reflection = new \ReflectionClass( Endpoint::class );
		$tile_reflection = new \ReflectionClass( Tile::class );
		$graph_reflection = new \ReflectionClass( Graph::class );
		$pie_reflection = new \ReflectionClass( Pie::class );

		// Test that Endpoint has the expected abstract methods
		$this->assertTrue( $endpoint_reflection->hasMethod( 'get_data_for_callback' ) );
		$this->assertTrue( $endpoint_reflection->hasMethod( 'get_id' ) );
		$this->assertTrue( $endpoint_reflection->hasMethod( 'register' ) );
		$this->assertTrue( $endpoint_reflection->hasMethod( 'get_data' ) );
		$this->assertTrue( $endpoint_reflection->hasMethod( 'get_label' ) );

		// Test that abstract classes remain abstract
		$this->assertTrue( $endpoint_reflection->isAbstract() );
		$this->assertTrue( $tile_reflection->isAbstract() );
		$this->assertTrue( $graph_reflection->isAbstract() );
		$this->assertTrue( $pie_reflection->isAbstract() );
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
