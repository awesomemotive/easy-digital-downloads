<?php
/**
 * Earnings by Taxonomy list table.
 *
 * @package     EDD
 * @subpackage  Reports/Data/File_Downloads
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */
namespace EDD\Reports\Data\Downloads;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

use EDD\Reports as Reports;
use EDD\Admin\List_Table;

/**
 * Earnings_By_Taxonomy_List_Table class.
 *
 * @since 3.0
 */
class Earnings_By_Taxonomy_List_Table extends List_Table {

	/**
	 * Query the database and fetch the top five most downloaded products.
	 *
	 * @since 3.0
	 *
	 * @return array Taxonomies.
	 */
	public function get_data() {
		global $wpdb;

		$dates      = Reports\get_filter_value( 'dates' );
		$taxes      = Reports\get_filter_value( 'taxes' );
		$date_range = Reports\parse_dates_for_range( $dates['range'] );

		// Generate date query SQL if dates have been set.
		$date_query_sql = '';

		if ( ! empty( $date_range['start'] ) || ! empty( $date_range['end'] ) ) {
			if ( ! empty( $date_range['start'] ) ) {
				$date_query_sql .= $wpdb->prepare( 'AND date_created >= %s', $date_range['start']->format( 'mysql' ) );
			}

			// Join dates with `AND` if start and end date set.
			if ( ! empty( $date_range['start'] ) && ! empty( $date_range['end'] ) ) {
				$date_query_sql .= ' AND ';
			}

			if ( ! empty( $date_range['end'] ) ) {
				$date_query_sql .= $wpdb->prepare( 'date_created <= %s', $date_range['end']->format( 'mysql' ) );
			}
		}

		$taxonomies = get_object_taxonomies( 'download', 'names' );
		$taxonomies = array_map( 'sanitize_text_field', $taxonomies );

		$placeholders = implode( ', ', array_fill( 0, count( $taxonomies ), '%s' ) );

		$taxonomy__in = $wpdb->prepare( "tt.taxonomy IN ({$placeholders})", $taxonomies );

		$sql = "SELECT t.*, tt.*, tr.object_id
				FROM {$wpdb->terms} AS t
				INNER JOIN {$wpdb->term_taxonomy} AS tt ON t.term_id = tt.term_id
				INNER JOIN {$wpdb->term_relationships} AS tr ON tr.term_taxonomy_id = tt.term_taxonomy_id
				WHERE {$taxonomy__in}";

		$results = $wpdb->get_results( $sql );

		// Build intermediate array to allow for better data processing.
		$taxonomies = array();
		foreach ( $results as $r ) {
			$taxonomies[ absint( $r->term_id ) ]['name']         = esc_html( $r->name );
			$taxonomies[ absint( $r->term_id ) ]['object_ids'][] = absint( $r->object_id );
			$taxonomies[ absint( $r->term_id ) ]['parent']       = absint( $r->parent );
		}

		$data       = array();
		$parent_ids = array();

		foreach ( $taxonomies as $k => $t ) {
			$c       = new \stdClass();
			$c->id   = $k;
			$c->name = $taxonomies[ $k ]['name'];

			$placeholders   = implode( ', ', array_fill( 0, count( $taxonomies[ $k ]['object_ids'] ), '%d' ) );
			$product_id__in = $wpdb->prepare( "product_id IN({$placeholders})", $taxonomies[ $k ]['object_ids'] );

			$column = Reports\get_taxes_excluded_filter() ? 'total - tax' : 'total';

			$sql = "SELECT SUM({$column}) as total, COUNT(id) AS sales
					FROM {$wpdb->edd_order_items}
					WHERE {$product_id__in} {$date_query_sql}";

			$result = $wpdb->get_row( $sql ); // WPCS: unprepared SQL ok.

			$earnings = null === $result && null === $result->total
				? 0.00
				: floatval( $result->total );

			$sales = null === $result && null === $result->sales
				? 0
				: absint( $result->sales );

			$c->sales    = $sales;
			$c->earnings = $earnings;
			$c->parent   = 0 === $t['parent']
				? null
				: $t['parent'];

			$average_sales    = 0;
			$average_earnings = 0.00;

			foreach ( $taxonomies[ $k ]['object_ids'] as $download ) {
				$average_sales    += edd_get_average_monthly_download_sales( $download );
				$average_earnings += edd_get_average_monthly_download_earnings( $download );
			}

			$c->average_sales    = $average_sales;
			$c->average_earnings = $average_earnings;

			$data[] = $c;
		}

		$sorted_data = array();

		foreach ( $data as $d ) {

			// Get parent level elements
			if ( null === $d->parent ) {
				$sorted_data[] = $d;

				$objects = array_values( wp_filter_object_list( $data, array( 'parent' => $d->id ) ) );

				foreach ( $objects as $o ) {
					$sorted_data[] = $o;
				}
			}
		}

		// Sort by total earnings
		usort( $sorted_data, function( $a, $b ) {
			return ( $a->earnings < $b->earnings ) ? -1 : 1;
		} );

		return $sorted_data;
	}

	/**
	 * Retrieve the table columns.
	 *
	 * @since 3.0
	 *
	 * @return array $columns Array of all the list table columns
	 */
	public function get_columns() {
		return array(
			'name'             => __( 'Name',                     'easy-digital-downloads' ),
			'sales'            => __( 'Total Sales',              'easy-digital-downloads' ),
			'earnings'         => __( 'Total Earnings',           'easy-digital-downloads' ),
			'average_sales'    => __( 'Monthly Sales Average',    'easy-digital-downloads' ),
			'average_earnings' => __( 'Monthly Earnings Average', 'easy-digital-downloads' )
		);
	}

	/**
	 * Render the Name Column.
	 *
	 * @since 3.0
	 *
	 * @param \stdClass $taxonomy Taxonomy object.
	 * @return string Data shown in the Name column.
	 */
	public function column_name( $taxonomy ) {
		return 0 < $taxonomy->parent
			? '&#8212; ' . $taxonomy->name
			: $taxonomy->name;
	}

	/**
	 * Render the Sales Column.
	 *
	 * @since 3.0
	 *
	 * @param \stdClass $taxonomy Taxonomy object.
	 * @return string Data shown in the Sales column.
	 */
	public function column_sales( $taxonomy ) {
		return $taxonomy->sales;
	}

	/**
	 * Render the Earnings Column.
	 *
	 * @since 3.0
	 *
	 * @param \stdClass $taxonomy Taxonomy object.
	 * @return string Data shown in the Earnings column.
	 */
	public function column_earnings( $taxonomy ) {
		return edd_currency_filter( edd_format_amount( $taxonomy->earnings ) );
	}

	/**
	 * Render the Average Sales Column.
	 *
	 * @since 3.0
	 *
	 * @param \stdClass $taxonomy Taxonomy object.
	 * @return string Data shown in the Average Sales column.
	 */
	public function column_average_sales( $taxonomy ) {
		return edd_format_amount( $taxonomy->average_sales );
	}

	/**
	 * Render the Average Earnings Column.
	 *
	 * @since 3.0
	 *
	 * @param \stdClass $taxonomy Taxonomy object.
	 * @return string Data shown in the Average Earnings column.
	 */
	public function column_average_earnings( $taxonomy ) {
		return edd_currency_filter( edd_format_amount( $taxonomy->average_earnings ) );
	}

	/**
	 * Setup the final data for the table.
	 *
	 * @since 3.0
	 */
	public function prepare_items() {
		$columns  = $this->get_columns();
		$hidden   = array();
		$sortable = $this->get_sortable_columns();

		$this->_column_headers = array( $columns, $hidden, $sortable );
		$this->items           = $this->get_data();
	}

	/**
	 * Message to be displayed when there are no items
	 *
	 * @since 3.0
	 */
	public function no_items() {
		esc_html_e( 'No taxonomies found.', 'easy-digital-downloads' );
	}

	/**
	 * Gets the name of the primary column.
	 *
	 * @since 3.0
	 * @access protected
	 *
	 * @return string Name of the primary column.
	 */
	protected function get_primary_column_name() {
		return 'name';
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
