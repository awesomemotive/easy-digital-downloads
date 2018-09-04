<?php
/**
 * Tax Collected by Location list table.
 *
 * @package     EDD
 * @subpackage  Reports/Data/Customers
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */
namespace EDD\Reports\Data\Taxes;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

use EDD\Admin\List_Table;

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
				'plural'   => 'report-tax-collected-by-locationss',
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
	 * @param array  $item Contains all the data of the downloads
	 * @param string $column_name The name of the column
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
	 * @return array $columns Array of all the list table columns
	 */
	public function get_columns() {
		return array(
			'country'  => __( 'Country/Region', 'easy-digital-downloads' ),
			'tax_rate' => __( 'Tax Rate', 'easy-digital-downloads' ),
			'from'     => __( 'From', 'easy-digital-downloads' ),
			'to'       => __( 'To', 'easy-digital-downloads' ),
			'gross'    => __( 'Gross', 'easy-digital-downloads' ),
			'net'      => __( 'Net', 'easy-digital-downloads' ),
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

		$data = array();

		$tax_rates = edd_get_tax_rates( array(), OBJECT );

		foreach ( $tax_rates as $tax_rate ) {
			$location = edd_get_country_name( $tax_rate->name );

			if ( ! empty( $tax_rate->description ) ) {
				$location .= ' &mdash; ' . edd_get_state_name( $tax_rate->name, $tax_rate->description );
			}

			$from = empty( $tax_rate->start_date ) || '0000-00-00 00:00:00' === $tax_rate->start_date
				? '&mdash;'
				: edd_date_i18n( EDD()->utils->date( $tax_rate->start_date, null, true )->startOfDay()->timestamp );

			$to = empty( $tax_rate->end_date ) || '0000-00-00 00:00:00' === $tax_rate->end_date
				? '&mdash;'
				: edd_date_i18n( EDD()->utils->date( $tax_rate->end_date, null, true )->endOfDay()->timestamp );

			$region = ! empty( $tax_rate->description )
				? $wpdb->prepare( ' AND region = %s', esc_sql( $tax_rate->description ) )
				: '';

			// Date query.
			$date_query = '';

			if ( ! empty( $tax_rate->start_date ) && '0000-00-00 00:00:00' !== $tax_rate->start_date ) {
				$date_query .= $wpdb->prepare( "AND {$wpdb->edd_orders}.date_created >= %s", esc_sql( $tax_rate->start_date ) );
			}

			if ( ! empty( $tax_rate->end_date ) && '0000-00-00 00:00:00' !== $tax_rate->end_date ) {
				$date_query .= $wpdb->prepare( "AND {$wpdb->edd_orders}.date_created <= %s", esc_sql( $tax_rate->end_date ) );
			}

			$results = $wpdb->get_row(
				$wpdb->prepare(
					"
				SELECT tax, total, country, region
				FROM {$wpdb->edd_orders}
				INNER JOIN {$wpdb->edd_order_addresses} ON {$wpdb->edd_order_addresses}.order_id = {$wpdb->edd_orders}.id
				WHERE {$wpdb->edd_order_addresses}.country = %s {$region} {$date_query}
				GROUP BY country, region
			",
					esc_sql( $tax_rate->name )
				),
				ARRAY_A
			);

			$results = wp_parse_args(
				$results,
				array(
					'subtotal' => 0.00,
					'total'    => 0.00,
				)
			);

			$data[] = array(
				'country'  => $location,
				'tax_rate' => floatval( $tax_rate->amount ) . '%',
				'from'     => $from,
				'to'       => $to,
				'gross'    => edd_currency_filter( edd_format_amount( floatval( $results['total'] - $results['tax'] ) ) ),
				'net'      => edd_currency_filter( edd_format_amount( floatval( $results['total'] ) ) ),
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
	 * @param string $which
	 */
	protected function pagination( $which ) {
	}

	/**
	 * Hide table navigation.
	 *
	 * @since 3.0
	 *
	 * @param string $which
	 */
	protected function display_tablenav( $which ) {
	}
}
