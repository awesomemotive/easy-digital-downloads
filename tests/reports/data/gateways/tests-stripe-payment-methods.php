<?php
/**
 * Tests for Stripe Payment Methods Report Table.
 *
 * @package     EDD\Tests\Reports\Data\Gateways
 * @copyright   Copyright (c) 2026, Sandhills Development, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.6.4
 */

namespace EDD\Tests\Reports\Data\Gateways;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;
use EDD\Reports\Data\Gateways\StripePaymentMethods;
use EDD\Reports\Init as ReportsInit;
use EDD\Reports;

new ReportsInit();

/**
 * Tests for the StripePaymentMethods class.
 *
 * @group edd_reports
 * @group edd_reports_gateways
 * @group edd_stripe
 *
 * @coversDefaultClass \EDD\Reports\Data\Gateways\StripePaymentMethods
 */
class StripePaymentMethods_Tests extends EDD_UnitTestCase {

	/**
	 * Instance of the StripePaymentMethods class.
	 *
	 * @var StripePaymentMethods
	 */
	protected $table;

	/**
	 * Set up test fixtures.
	 */
	public function setUp(): void {
		parent::setUp();
		$this->table = new StripePaymentMethods();
	}

	/**
	 * Tear down test fixtures.
	 */
	public function tearDown(): void {
		unset( $this->table );
		unset( $_REQUEST['range'] );
		unset( $_REQUEST['filter_from'] );
		unset( $_REQUEST['filter_to'] );
		unset( $_GET['range'] );
		unset( $_GET['filter_from'] );
		unset( $_GET['filter_to'] );
		parent::tearDown();
	}

	/**
	 * @covers ::get_columns
	 */
	public function test_get_columns_returns_expected_columns() {
		$columns = $this->table->get_columns();

		$this->assertIsArray( $columns );
		$this->assertArrayHasKey( 'label', $columns );
		$this->assertArrayHasKey( 'complete_sales', $columns );
		$this->assertArrayHasKey( 'pending_sales', $columns );
		$this->assertArrayHasKey( 'refunded_sales', $columns );
		$this->assertArrayHasKey( 'total_sales', $columns );
	}

	/**
	 * @covers ::get_columns
	 */
	public function test_get_columns_has_five_columns() {
		$columns = $this->table->get_columns();

		$this->assertCount( 5, $columns );
	}

	/**
	 * @covers ::get_columns
	 */
	public function test_get_columns_label_is_payment_method() {
		$columns = $this->table->get_columns();

		$this->assertSame( __( 'Payment Method', 'easy-digital-downloads' ), $columns['label'] );
	}

	/**
	 * @covers ::get_primary_column_name
	 */
	public function test_get_primary_column_name_returns_label() {
		// Use reflection to access protected method.
		$reflection = new \ReflectionClass( $this->table );
		$method     = $reflection->getMethod( 'get_primary_column_name' );
		$method->setAccessible( true );

		$this->assertSame( 'label', $method->invoke( $this->table ) );
	}

	/**
	 * @covers ::column_default
	 */
	public function test_column_default_returns_column_value() {
		$item = array(
			'label'          => 'Card',
			'complete_sales' => '10',
			'pending_sales'  => '2',
			'refunded_sales' => '1',
			'total_sales'    => '13',
		);

		$this->assertSame( 'Card', $this->table->column_default( $item, 'label' ) );
		$this->assertSame( '10', $this->table->column_default( $item, 'complete_sales' ) );
		$this->assertSame( '2', $this->table->column_default( $item, 'pending_sales' ) );
		$this->assertSame( '1', $this->table->column_default( $item, 'refunded_sales' ) );
		$this->assertSame( '13', $this->table->column_default( $item, 'total_sales' ) );
	}

	/**
	 * @covers ::bulk_actions
	 */
	public function test_bulk_actions_returns_nothing() {
		$this->assertNull( $this->table->bulk_actions() );
	}

	/**
	 * Test that get_meta_query returns correct query for empty method (card).
	 *
	 * @covers ::get_meta_query
	 */
	public function test_get_meta_query_for_empty_method_returns_or_query() {
		// Use reflection to access private method.
		$reflection = new \ReflectionClass( $this->table );
		$method     = $reflection->getMethod( 'get_meta_query' );
		$method->setAccessible( true );

		$result = $method->invoke( $this->table, '' );

		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'relation', $result );
		$this->assertSame( 'OR', $result['relation'] );
		$this->assertCount( 3, $result ); // relation + 2 conditions.
	}

	/**
	 * Test that get_meta_query returns correct query for specific method.
	 *
	 * @covers ::get_meta_query
	 */
	public function test_get_meta_query_for_specific_method_returns_simple_query() {
		// Use reflection to access private method.
		$reflection = new \ReflectionClass( $this->table );
		$method     = $reflection->getMethod( 'get_meta_query' );
		$method->setAccessible( true );

		$result = $method->invoke( $this->table, 'link' );

		$this->assertIsArray( $result );
		$this->assertCount( 1, $result );
		$this->assertSame( 'stripe_payment_method_type', $result[0]['key'] );
		$this->assertSame( 'link', $result[0]['value'] );
		$this->assertSame( '=', $result[0]['compare'] );
	}

	/**
	 * Test that get_meta_query returns correct query for us_bank_account method.
	 *
	 * @covers ::get_meta_query
	 */
	public function test_get_meta_query_for_us_bank_account_method() {
		// Use reflection to access private method.
		$reflection = new \ReflectionClass( $this->table );
		$method     = $reflection->getMethod( 'get_meta_query' );
		$method->setAccessible( true );

		$result = $method->invoke( $this->table, 'us_bank_account' );

		$this->assertSame( 'us_bank_account', $result[0]['value'] );
	}

	/**
	 * Test that query method uses parse_dates_for_range correctly.
	 * This verifies the fix for accessing $filter['range'] correctly.
	 *
	 * @covers ::query
	 */
	public function test_query_uses_parsed_dates_correctly() {
		// Set up the date filter.
		$_GET['range'] = 'this_month';

		// Use reflection to access private method.
		$reflection = new \ReflectionClass( $this->table );
		$method     = $reflection->getMethod( 'query' );
		$method->setAccessible( true );

		// This should not throw an error now that we use parse_dates_for_range().
		// Previously, accessing $filter['range']['start'] would fail because
		// $filter['range'] is a string like 'this_month', not an array.
		$result = $method->invoke( $this->table, '', array( 'status' => array( 'complete' ) ) );

		// Result should be an integer (count).
		$this->assertIsInt( $result );
	}

	/**
	 * Test that query method handles 'today' date range.
	 *
	 * @covers ::query
	 */
	public function test_query_with_today_range() {
		$_GET['range'] = 'today';

		$reflection = new \ReflectionClass( $this->table );
		$method     = $reflection->getMethod( 'query' );
		$method->setAccessible( true );

		$result = $method->invoke( $this->table, '', array( 'status' => array( 'complete' ) ) );

		$this->assertIsInt( $result );
	}

	/**
	 * Test that query method handles 'last_month' date range.
	 *
	 * @covers ::query
	 */
	public function test_query_with_last_month_range() {
		$_GET['range'] = 'last_month';

		$reflection = new \ReflectionClass( $this->table );
		$method     = $reflection->getMethod( 'query' );
		$method->setAccessible( true );

		$result = $method->invoke( $this->table, '', array( 'status' => array( 'complete' ) ) );

		$this->assertIsInt( $result );
	}

	/**
	 * Test that query method handles 'last_year' date range.
	 *
	 * @covers ::query
	 */
	public function test_query_with_last_year_range() {
		$_GET['range'] = 'last_year';

		$reflection = new \ReflectionClass( $this->table );
		$method     = $reflection->getMethod( 'query' );
		$method->setAccessible( true );

		$result = $method->invoke( $this->table, '', array( 'status' => array( 'complete' ) ) );

		$this->assertIsInt( $result );
	}

	/**
	 * Test instantiation of StripePaymentMethods class.
	 */
	public function test_class_instantiation() {
		$this->assertInstanceOf( StripePaymentMethods::class, $this->table );
	}
}
