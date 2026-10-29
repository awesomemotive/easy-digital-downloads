<?php
/**
 * Tests for the EDD report, store, and log abilities.
 *
 * @package     EDD\Tests\Abilities
 * @copyright   Copyright (c) 2026, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.7.1
 */

namespace EDD\Tests\Abilities;

use EDD\Abilities\Logs\Emails;
use EDD\Abilities\Logs\FileDownloads;
use EDD\Abilities\Reports\Sales;
use EDD\Abilities\Reports\SalesSummary;
use EDD\Abilities\Store\HealthCheck;
use EDD\Abilities\Store\Settings;
use EDD\Abilities\Store\TaxRates;
use EDD\Tests\PHPUnit\EDD_UnitTestCase;

/**
 * Report, store, and log ability tests.
 *
 * @since 3.7.1
 */
class Reports extends EDD_UnitTestCase {

	/**
	 * Set the current user to the site administrator, who holds all shop capabilities.
	 */
	public function set_up() {
		parent::set_up();
		wp_set_current_user( 1 );
	}

	/**
	 * Recursively collects every string key from a nested array.
	 *
	 * @param array $data The array to scan.
	 * @return array
	 */
	private function collect_keys_recursively( array $data ): array {
		$keys = array();
		foreach ( $data as $key => $value ) {
			if ( is_string( $key ) ) {
				$keys[] = $key;
			}
			if ( is_array( $value ) ) {
				$keys = array_merge( $keys, $this->collect_keys_recursively( $value ) );
			}
		}

		return $keys;
	}

	public function test_sales_report_for_preset_period() {
		parent::edd()->order->create_many( 2 );

		$ability = new Sales();
		$result  = $ability->execute( array( 'period' => 'this_month' ) );

		$this->assertIsArray( $result );
		$this->assertSame( 'this_month', $result['period'] );
		$this->assertNull( $result['date_start'] );
		$this->assertNull( $result['date_end'] );
		$this->assertSame( edd_get_currency(), $result['currency'] );
		$this->assertGreaterThanOrEqual( 2, $result['order_count'] );
		$this->assertIsFloat( $result['earnings_gross'] );
		$this->assertIsFloat( $result['earnings_net'] );
		$this->assertGreaterThan( 0, $result['earnings_net'] );
		$this->assertIsFloat( $result['refund_amount'] );
		$this->assertIsInt( $result['refund_count'] );
		$this->assertEqualsWithDelta( $result['earnings_net'] / $result['order_count'], $result['average_order_value'], 0.001 );
	}

	public function test_sales_report_with_custom_date_range() {
		parent::edd()->order->create();

		$start = gmdate( 'Y-m-d', strtotime( '-1 day' ) );
		$end   = gmdate( 'Y-m-d', strtotime( '+1 day' ) );

		$ability = new Sales();
		$result  = $ability->execute(
			array(
				'date_start' => $start,
				'date_end'   => $end,
			)
		);

		$this->assertIsArray( $result );
		$this->assertNull( $result['period'] );
		$this->assertSame( $start, $result['date_start'] );
		$this->assertSame( $end, $result['date_end'] );
		$this->assertGreaterThanOrEqual( 1, $result['order_count'] );
	}

	public function test_sales_report_rejects_an_impossible_calendar_date() {
		parent::edd()->order->create();

		$ability = new Sales();

		// The control: a real date in the same shape reports numbers, so the
		// rejection below is the calendar check and not the report failing.
		$control = $ability->execute(
			array(
				'date_start' => gmdate( 'Y-m-d', strtotime( '-1 day' ) ),
			)
		);

		$this->assertIsArray( $control );
		$this->assertIsInt( $control['order_count'] );

		$result = $ability->execute( array( 'date_start' => '2026-02-31' ) );

		$this->assertWPError( $result );
		$this->assertSame( 'edd_ability_invalid_date', $result->get_error_code() );
		$this->assertStringContainsString( 'date_start', $result->get_error_message() );
	}

	public function test_download_log_rejects_an_impossible_calendar_date() {
		$result = ( new FileDownloads() )->execute( array( 'date_end' => '2026-02-31' ) );

		$this->assertWPError( $result );
		$this->assertSame( 'edd_ability_invalid_date', $result->get_error_code() );
		$this->assertStringContainsString( 'date_end', $result->get_error_message() );
	}

	/**
	 * date_end covers the whole of that day in the store's timezone.
	 *
	 * Berlin stamps date_created in UTC, so an unconverted bound cuts the day
	 * short by the store's offset and drops that evening's activity.
	 */
	public function test_download_log_date_end_covers_the_whole_store_day() {
		// Phoenix holds UTC-7 all year, so 23:30 store time on the 10th is
		// 06:30 UTC on the 11th, past an unconverted end-of-day bound.
		update_option( 'timezone_string', 'America/Phoenix' );

		$log_id = parent::edd()->file_download_log->create( array( 'date_created' => '2026-09-11 06:30:00' ) );

		// The row has to sit at that UTC instant, or the bound is not what is tested.
		$this->assertSame( '2026-09-11 06:30:00', edd_get_file_download_log( $log_id )->date_created );

		$result = ( new FileDownloads() )->execute( array( 'date_end' => '2026-09-10' ) );

		$this->assertIsArray( $result );
		$this->assertCount( 1, $result['logs'] );
		$this->assertSame( (int) $log_id, $result['logs'][0]['id'] );
	}

	public function test_email_log_rejects_an_impossible_calendar_date() {
		$result = ( new Emails() )->execute( array( 'date_start' => '2026-13-01' ) );

		$this->assertWPError( $result );
		$this->assertSame( 'edd_ability_invalid_date', $result->get_error_code() );
	}

	public function test_sales_report_rounds_the_average_order_value() {
		parent::edd()->order->create( array( 'total' => 20.33, 'subtotal' => 20.33, 'tax' => 0, 'discount' => 0 ) );
		parent::edd()->order->create( array( 'total' => 20.34, 'subtotal' => 20.34, 'tax' => 0, 'discount' => 0 ) );

		$result = ( new Sales() )->execute( array( 'period' => 'this_month' ) );

		$this->assertIsArray( $result );
		$this->assertSame( 2, $result['order_count'] );

		// The two orders have to net 40.67, whose average of 20.335 is the value
		// the rounding resolves. An average already at two decimals proves nothing.
		$this->assertEqualsWithDelta( 40.67, $result['earnings_net'], 0.0001 );

		$this->assertSame( 20.34, $result['average_order_value'] );
	}

	public function test_sales_report_with_product_scope() {
		// Factory orders always carry an order item for product ID 1.
		parent::edd()->order->create();

		$ability = new Sales();
		$result  = $ability->execute(
			array(
				'period'     => 'this_month',
				'product_id' => 1,
			)
		);

		$this->assertIsArray( $result );
		$this->assertSame( 1, $result['product_id'] );
		$this->assertArrayHasKey( 'product_earnings', $result );
		$this->assertArrayHasKey( 'product_sales', $result );
		$this->assertIsFloat( $result['product_earnings'] );
		$this->assertIsInt( $result['product_sales'] );
		$this->assertGreaterThanOrEqual( 1, $result['product_sales'] );
	}

	public function test_sales_summary_rejects_an_input_it_does_not_accept() {
		$result = ( new SalesSummary() )->execute( array( 'junk' => 1 ) );

		$this->assertWPError( $result );
		$this->assertSame( 'edd_ability_invalid_input', $result->get_error_code() );
		$this->assertStringContainsString( 'junk', $result->get_error_message() );
	}

	public function test_sales_summary_returns_all_ranges() {
		parent::edd()->order->create();

		$ability = new SalesSummary();
		$result  = $ability->execute();

		$this->assertIsArray( $result );
		foreach ( array( 'this_month', 'last_month', 'today', 'total' ) as $range ) {
			$this->assertArrayHasKey( $range, $result );
			$this->assertIsFloat( $result[ $range ]['earnings'] );
			$this->assertIsInt( $result[ $range ]['sales'] );
		}

		$this->assertSame( array( 'this_month', 'last_month', 'today', 'total', 'currency' ), array_keys( $result ) );
		$this->assertGreaterThanOrEqual( 1, $result['total']['sales'] );
		$this->assertSame( edd_get_currency(), $result['currency'] );
	}

	public function test_sales_summary_output_schema_requires_every_range_and_currency() {
		$ability = new SalesSummary();
		$method  = new \ReflectionMethod( $ability, 'get_output_schema' );
		$method->setAccessible( true );
		$schema = $method->invoke( $ability );

		$expected_keys = array( 'this_month', 'last_month', 'today', 'total', 'currency' );

		$this->assertSame( $expected_keys, $schema['required'] );
		$this->assertSame( $expected_keys, array_keys( $schema['properties'] ) );
		$this->assertSame( array( 'type' => 'string' ), $schema['properties']['currency'] );

		foreach ( array_slice( $expected_keys, 0, 4 ) as $range ) {
			$range_schema = $schema['properties'][ $range ];

			$this->assertSame( 'object', $range_schema['type'] );
			$this->assertSame( 'number', $range_schema['properties']['earnings']['type'] );
			$this->assertSame( 'integer', $range_schema['properties']['sales']['type'] );
			$this->assertSame( array( 'earnings', 'sales' ), $range_schema['required'] );
		}
	}

	public function test_settings_returns_curated_values_without_secrets() {
		$ability = new Settings();
		$result  = $ability->execute();

		$this->assertIsArray( $result );
		$this->assertSame( edd_get_currency(), $result['currency'] );
		$this->assertIsBool( $result['test_mode'] );
		$this->assertIsArray( $result['enabled_gateways'] );
		foreach ( $result['enabled_gateways'] as $gateway ) {
			$this->assertArrayHasKey( 'id', $gateway );
			$this->assertArrayHasKey( 'label', $gateway );
		}
		$this->assertIsBool( $result['taxes_enabled'] );
		$this->assertIsBool( $result['sequential_order_numbers'] );
		$this->assertSame( EDD_VERSION, $result['edd_version'] );
		$this->assertIsBool( $result['pro'] );

		// No key anywhere in the output may look like it holds a secret.
		foreach ( $this->collect_keys_recursively( $result ) as $key ) {
			foreach ( array( 'key', 'secret', 'token', 'password' ) as $needle ) {
				$this->assertStringNotContainsString( $needle, strtolower( $key ), "Settings output contains a suspicious key: {$key}." );
			}
		}
	}

	public function test_tax_rates_lists_seeded_rate() {
		$rate_id = edd_add_tax_rate(
			array(
				'country' => 'US',
				'amount'  => 8.25,
			)
		);
		$this->assertNotEmpty( $rate_id );

		$ability = new TaxRates();
		$result  = $ability->execute( array() );

		$this->assertIsArray( $result );
		$this->assertIsBool( $result['enabled'] );
		$this->assertNotEmpty( $result['rates'] );

		$rates_by_country = wp_list_pluck( $result['rates'], 'rate', 'country' );
		$this->assertArrayHasKey( 'US', $rates_by_country );
		$this->assertEqualsWithDelta( 8.25, $rates_by_country['US'], 0.001 );

		$rate = $result['rates'][0];
		foreach ( array( 'id', 'country', 'region', 'rate', 'status', 'scope' ) as $key ) {
			$this->assertArrayHasKey( $key, $rate );
		}

		// The country filter only returns matching rates.
		$filtered = $ability->execute( array( 'country' => 'US' ) );
		$this->assertNotEmpty( $filtered['rates'] );
		foreach ( $filtered['rates'] as $filtered_rate ) {
			$this->assertSame( 'US', $filtered_rate['country'] );
		}
	}

	public function test_health_check_snapshot() {
		parent::edd()->order->create( array( 'status' => 'complete' ) );

		$ability = new HealthCheck();
		$result  = $ability->execute();

		$this->assertIsArray( $result );
		$this->assertSame( EDD_VERSION, $result['edd_version'] );
		$this->assertIsString( $result['wp_version'] );
		$this->assertIsString( $result['php_version'] );
		$this->assertIsBool( $result['pro'] );
		$this->assertIsBool( $result['test_mode'] );
		$this->assertSame( edd_get_currency(), $result['currency'] );

		$this->assertIsArray( $result['order_counts'] );
		$this->assertArrayHasKey( 'total', $result['order_counts'] );
		$this->assertGreaterThanOrEqual( 1, $result['order_counts']['complete'] );

		$this->assertIsInt( $result['customer_count'] );
		$this->assertIsInt( $result['product_count'] );
		$this->assertIsArray( $result['gateways_enabled'] );
	}

	public function test_download_log_list_filters_and_paginates() {
		for ( $i = 0; $i < 3; $i++ ) {
			parent::edd()->file_download_log->create( array( 'product_id' => 111 ) );
		}
		parent::edd()->file_download_log->create( array( 'product_id' => 222 ) );

		$ability = new FileDownloads();

		$all = $ability->execute( array() );
		$this->assertArrayHasKey( 'logs', $all );
		$this->assertGreaterThanOrEqual( 4, $all['total'] );

		$filtered = $ability->execute( array( 'product_id' => 111 ) );
		$this->assertSame( 3, $filtered['total'] );
		foreach ( $filtered['logs'] as $log ) {
			$this->assertSame( 111, $log['product_id'] );
			$this->assertArrayHasKey( 'ip', $log );
			$this->assertArrayHasKey( 'date_created', $log );
		}

		$paged = $ability->execute(
			array(
				'product_id' => 111,
				'limit'      => 2,
				'offset'     => 0,
			)
		);
		$this->assertCount( 2, $paged['logs'] );
		$this->assertSame( 2, $paged['limit'] );
		$this->assertTrue( $paged['has_more'] );
	}

	public function test_email_log_list_and_recipient_filter() {
		parent::edd()->email_logs->create( array( 'email' => 'alice@edd.test' ) );
		parent::edd()->email_logs->create( array( 'email' => 'alice@edd.test' ) );
		parent::edd()->email_logs->create( array( 'email' => 'bob@edd.test' ) );

		$ability = new Emails();

		$all = $ability->execute( array() );
		$this->assertArrayHasKey( 'logs', $all );
		$this->assertGreaterThanOrEqual( 3, $all['total'] );

		$filtered = $ability->execute( array( 'recipient' => 'alice@edd.test' ) );
		$this->assertSame( 2, $filtered['total'] );
		foreach ( $filtered['logs'] as $log ) {
			$this->assertSame( 'alice@edd.test', $log['recipient'] );
			$this->assertSame( 'order_receipt', $log['email_id'] );
			$this->assertSame( 'order', $log['object_type'] );
			$this->assertArrayHasKey( 'subject', $log );
		}
	}

	public function test_report_capabilities_split() {
		$read_only = array( new Sales(), new SalesSummary() );
		$settings  = array( new Settings(), new TaxRates(), new HealthCheck(), new FileDownloads(), new Emails() );
		$user_id   = self::factory()->user->create( array( 'role' => 'subscriber' ) );

		// A plain subscriber is denied everywhere.
		wp_set_current_user( $user_id );
		foreach ( array_merge( $read_only, $settings ) as $ability ) {
			$this->assertFalse( $ability->check_permissions( array() ), get_class( $ability ) . ' must deny a subscriber.' );
		}

		// view_shop_reports unlocks the reports, but not the settings-gated abilities.
		wp_get_current_user()->add_cap( 'view_shop_reports' );

		foreach ( $read_only as $ability ) {
			$this->assertTrue( $ability->check_permissions( array() ), get_class( $ability ) . ' must allow view_shop_reports.' );
		}
		foreach ( $settings as $ability ) {
			$this->assertFalse( $ability->check_permissions( array() ), get_class( $ability ) . ' must require manage_shop_settings.' );
		}
	}

	public function test_sales_summary_leaves_report_view_filters_in_place() {
		$callback = '__return_empty_array';
		add_filter( 'edd_report_views', $callback );

		// A read-only ability must not strip callbacks off a public filter for
		// the rest of the request.
		$this->assertNotFalse( has_filter( 'edd_report_views', $callback ) );

		( new SalesSummary() )->execute();

		$still_registered = has_filter( 'edd_report_views', $callback );
		remove_filter( 'edd_report_views', $callback );

		$this->assertNotFalse( $still_registered );
	}

	public function test_settings_reports_the_url_customers_are_sent_to() {
		$success      = self::factory()->post->create( array( 'post_type' => 'page', 'post_status' => 'publish' ) );
		$confirmation = self::factory()->post->create( array( 'post_type' => 'page', 'post_status' => 'publish' ) );

		edd_update_option( 'success_page', $success );
		edd_update_option( 'confirmation_page', $confirmation );

		// edd_get_success_page_uri() prefers the confirmation page when no query
		// string is passed, so the raw option is not where checkout lands.
		$this->assertNotSame( get_permalink( $success ), edd_get_success_page_uri() );

		$result = ( new Settings() )->execute();

		$this->assertSame( edd_get_success_page_uri(), $result['success_page_url'] );
	}
}
