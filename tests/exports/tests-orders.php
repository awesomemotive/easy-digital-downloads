<?php
/**
 * Tests for the Orders exporter.
 *
 * @group exports
 * @group edd_orders
 */
namespace EDD\Tests\Exports;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;
use EDD\Admin\Exports\Exporters\Orders;

class OrdersExport extends EDD_UnitTestCase {

	/**
	 * Exporter instance.
	 *
	 * @var Orders
	 */
	protected $exporter;

	/**
	 * ID of the fixture order that has a refund.
	 *
	 * @var int
	 * @static
	 */
	protected static $order_id;

	/**
	 * Set up fixtures once for the class.
	 */
	public static function wpSetUpBeforeClass() {
		$order          = parent::edd()->order->create_and_get();
		self::$order_id = $order->id;

		edd_refund_order( $order->id );
	}

	/**
	 * Set up the exporter for each test.
	 */
	public function setUp(): void {
		parent::setUp();
		$this->exporter = new Orders();
	}

	public function test_get_data_headers_include_refunded_columns() {
		$headers = $this->invoke_method( 'get_data_headers' );

		$this->assertArrayHasKey( 'refunded', $headers );
		$this->assertArrayHasKey( 'refunded_tax', $headers );
	}

	public function test_refunded_amount_populated_for_refunded_order() {
		$row = $this->find_export_row( self::$order_id );

		$this->assertNotNull( $row, 'Fixture order should appear in export data.' );
		$this->assertSame(
			html_entity_decode( edd_format_amount( 120.0 ) ),
			$row['refunded']
		);
	}

	public function test_refunded_tax_populated_for_refunded_order() {
		$row = $this->find_export_row( self::$order_id );

		$this->assertNotNull( $row, 'Fixture order should appear in export data.' );
		$this->assertSame(
			html_entity_decode( edd_format_amount( 25.0 ) ),
			$row['refunded_tax']
		);
	}

	public function test_refunded_amount_is_zero_for_unrefunded_order() {
		$order_id = parent::edd()->order->create_and_get()->id;

		$row = $this->find_export_row( $order_id );

		$this->assertNotNull( $row, 'Unrefunded order should appear in export data.' );
		$this->assertSame(
			html_entity_decode( edd_format_amount( 0.0 ) ),
			$row['refunded']
		);
	}

	/**
	 * Invoke a protected or private method via reflection.
	 *
	 * @param string $method_name
	 * @return mixed
	 */
	private function invoke_method( string $method_name ) {
		$reflection = new \ReflectionClass( $this->exporter );
		$method     = $reflection->getMethod( $method_name );
		if (PHP_VERSION_ID < 80100) {
            $method->setAccessible( true );
        }
		return $method->invoke( $this->exporter );
	}

	/**
	 * Find a row in the export data by order ID.
	 *
	 * @param int $order_id
	 * @return array|null
	 */
	private function find_export_row( int $order_id ): ?array {
		$data = $this->invoke_method( 'get_data' );
		foreach ( $data as $row ) {
			if ( (int) $row['id'] === $order_id ) {
				return $row;
			}
		}
		return null;
	}
}
