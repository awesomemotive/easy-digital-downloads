<?php
/**
 * Tax Collected by Location list table.
 *
 * @package     EDD\Reports\Data\Taxes
 * @copyright   Copyright (c) 2018, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */

namespace EDD\Reports\Data\Taxes;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

use EDD\Admin\List_Table;
use EDD\Reports;

/**
 * Tax_Collected_by_Location class.
 *
 * @since 3.0
 */
class Tax_Collected_By_Location extends List_Table {

	/**
	 * Get things started
	 *
	 * @since 1.5
	 * @see WP_List_Table::__construct()
	 */
	public function __construct() {
		parent::__construct(
			array(
				'singular' => 'report-tax-collected-by-location',
				'plural'   => 'report-tax-collected-by-locations',
				'ajax'     => false,
			)
		);
	}

	/**
	 * Gets the name of the primary column.
	 *
	 * @since 2.5
	 * @access protected
	 *
	 * @return string Name of the primary column.
	 */
	protected function get_primary_column_name() {
		return 'label';
	}

	/**
	 * Render each column.
	 *
	 * @since 1.5
	 *
	 * @param array  $item Contains all the data of the downloads.
	 * @param string $column_name The name of the column.
	 *
	 * @return string Column Name
	 */
	public function column_default( $item, $column_name ) {
		return $item[ $column_name ];
	}

	/**
	 * Column names.
	 *
	 * @since 3.0
	 *
	 * @return array $columns Array of all the list table columns.
	 */
	public function get_columns() {
		return array(
			'country' => __( 'Country/Region', 'easy-digital-downloads' ),
			'gross'   => __( 'Gross', 'easy-digital-downloads' ),
			'tax'     => __( 'Tax', 'easy-digital-downloads' ),
			'net'     => __( 'Net', 'easy-digital-downloads' ),
		);
	}

	/**
	 * Query data for the list table.
	 *
	 * @since 3.0
	 *
	 * @return array $data All the data for the list table.
	 */
	public function get_data() {
		global $wpdb;

		$data             = array();
		$tax_rates        = edd_get_tax_rates( array( 'status' => 'active' ), OBJECT );
		$currency         = Reports\get_filter_value( 'currencies' );
		$convert_currency = empty( $currency ) || 'convert' === $currency;
		$format_currency  = $convert_currency ? edd_get_currency() : strtoupper( $currency );

		$tax_column   = $convert_currency ? 'tax / rate' : 'tax';
		$total_column = $convert_currency ? 'total / rate' : 'total';

		$currency_sql = '';
		if ( ! $convert_currency && array_key_exists( strtoupper( $currency ), edd_get_currencies() ) ) {
			$currency_sql = $wpdb->prepare( ' AND currency = %s ', strtoupper( $currency ) );
		}

		$date_query = $this->get_date_query();

		/*
		 * We need to first calculate the total tax collected for all orders so we can determine the amount of tax collected for the global rate
		 *
		 * The total determined here will be reduced by the amount collected for each specified tax rate/region.
		 */
		$all_orders = $wpdb->get_results(
			"
			SELECT SUM({$tax_column}) as tax, SUM({$total_column}) as total
			FROM {$wpdb->edd_orders}
			WHERE 1=1
			{$currency_sql} {$date_query}
			",
			ARRAY_A
		);

		foreach ( $tax_rates as $tax_rate ) {
			$key = $this->get_data_array_key( $tax_rate );

			if ( array_key_exists( $key, $data ) ) {
				continue; // We've already pulled numbers for this country / region.
			}

			if ( 'global' === $key ) {
				$results = $wpdb->get_results(
					$wpdb->prepare(
						"
						SELECT SUM($tax_column) as tax, SUM($total_column) as total
						FROM {$wpdb->edd_orders}
						AND {$wpdb->edd_orders}.tax_rate_id = %d
						{$date_query} {$currency_sql}
						",
						$tax_rate->id
					),
					ARRAY_A
				);
			} else {
				$region = ! empty( $tax_rate->state )
					? $wpdb->prepare( ' AND region = %s', esc_sql( $tax_rate->state ) )
					: '';

				$results = $wpdb->get_results(
					$wpdb->prepare(
						"
						SELECT SUM($tax_column) as tax, SUM($total_column) as total, country, region
						FROM {$wpdb->edd_orders}
						INNER JOIN {$wpdb->edd_order_addresses} ON {$wpdb->edd_order_addresses}.order_id = {$wpdb->edd_orders}.id
						WHERE {$wpdb->edd_order_addresses}.country = %s {$region} {$date_query} {$currency_sql}
						",
						esc_sql( $tax_rate->country )
					),
					ARRAY_A
				);
			}

			$all_orders[0]['tax']   -= $results[0]['tax'];
			$all_orders[0]['total'] -= $results[0]['total'];

			$data[ $key ] = array(
				'country' => $this->get_location_name( $tax_rate ),
				'gross'   => edd_currency_filter( edd_format_amount( floatval( $results[0]['total'] ) ), $format_currency ),
				'tax'     => edd_currency_filter( edd_format_amount( floatval( $results[0]['tax'] ) ), $format_currency ),
				'net'     => edd_currency_filter( edd_format_amount( floatval( $results[0]['total'] - $results[0]['tax'] ) ), $format_currency ),
			);
		}

		if ( ! array_key_exists( 'global', $data ) && $all_orders[0]['total'] > 0 && $all_orders[0]['tax'] > 0 ) {
			$data['global'] = array(
				'country' => __( 'Global Rate', 'easy-digital-downloads' ),
				'gross'   => edd_currency_filter( edd_format_amount( floatval( max( 0, $all_orders[0]['total'] ) ) ), $format_currency ),
				'tax'     => edd_currency_filter( edd_format_amount( floatval( max( 0, $all_orders[0]['tax'] ) ) ), $format_currency ),
				'net'     => edd_currency_filter( edd_format_amount( floatval( max( 0, $all_orders[0]['total'] - $all_orders[0]['tax'] ) ) ), $format_currency ),
			);
		}

		return $data;
	}

	/**
	 * Setup the final data for the table
	 *
	 * @since 1.5
	 * @uses EDD_Gateway_Reports_Table::get_columns()
	 * @uses EDD_Gateway_Reports_Table::get_sortable_columns()
	 * @uses EDD_Gateway_Reports_Table::reports_data()
	 * @return void
	 */
	public function prepare_items() {
		$columns               = $this->get_columns();
		$hidden                = array(); // No hidden columns
		$sortable              = $this->get_sortable_columns();
		$this->_column_headers = array( $columns, $hidden, $sortable );
		$this->items           = $this->get_data();
	}

	/**
	 * Return empty array to disable sorting.
	 *
	 * @since 3.0
	 *
	 * @return array
	 */
	public function get_sortable_columns() {
		return array();
	}

	/**
	 * Return empty array to remove bulk actions.
	 *
	 * @since 3.0
	 *
	 * @return array
	 */
	public function get_bulk_actions() {
		return array();
	}

	/**
	 * Hide pagination.
	 *
	 * @since 3.0
	 *
	 * @param string $which The position of the pagination.
	 */
	protected function pagination( $which ) {}

	/**
	 * Hide table navigation.
	 *
	 * @since 3.0
	 *
	 * @param string $which The position of the table navigation.
	 */
	protected function display_tablenav( $which ) {}

	/**
	 * Get the date query.
	 *
	 * @since 3.5.0
	 * @return string
	 */
	private function get_date_query() {
		global $wpdb;

		$date_filter = Reports\get_filter_value( 'dates' );
		$date_range  = Reports\parse_dates_for_range( $date_filter['range'] );

		// Date query.
		$date_query = '';

		if ( ! empty( $date_range['start'] ) && '0000-00-00 00:00:00' !== $date_range['start'] ) {
			$date_query .= $wpdb->prepare( " AND {$wpdb->edd_orders}.date_created >= %s", esc_sql( $date_range['start']->format( 'mysql' ) ) );
		}

		if ( ! empty( $date_range['end'] ) && '0000-00-00 00:00:00' !== $date_range['end'] ) {
			$date_query .= $wpdb->prepare( " AND {$wpdb->edd_orders}.date_created <= %s", esc_sql( $date_range['end']->format( 'mysql' ) ) );
		}

		return $date_query;
	}

	/**
	 * Get the data array key.
	 *
	 * @since 3.5.0
	 * @param object $tax_rate The tax rate object.
	 * @return string
	 */
	private function get_data_array_key( $tax_rate ) {
		if ( 'global' === $tax_rate->scope ) {
			return 'global';
		}

		return $tax_rate->state ? $tax_rate->country . '-' . $tax_rate->state : $tax_rate->country;
	}

	/**
	 * Get the location name.
	 *
	 * @since 3.5.0
	 * @param object $tax_rate The tax rate object.
	 * @return string
	 */
	private function get_location_name( $tax_rate ) {
		if ( 'global' === $tax_rate->scope ) {
			return __( 'Global Rate', 'easy-digital-downloads' );
		}

		$location = edd_get_country_name( $tax_rate->country );

		if ( ! empty( $tax_rate->state ) ) {
			$location .= ' &mdash; ' . edd_get_state_name( $tax_rate->country, $tax_rate->state );
		}

		return $location;
	}
}
