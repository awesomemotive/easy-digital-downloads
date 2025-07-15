<?php
/**
 * Handles dates for stats.
 *
 * @since 3.5.0
 * @package EDD\Stats\Traits
 */

namespace EDD\Stats\Traits;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

use EDD\Reports;

/**
 * Dates trait.
 *
 * @since 3.5.0
 */
trait Dates {

	/**
	 * Set up the date ranges available.
	 *
	 * @since 3.0
	 * @access private
	 */
	private function set_date_ranges() {
		foreach ( Reports\get_dates_filter_options() as $range => $label ) {
			$this->date_ranges[ $range ]          = Reports\parse_dates_for_range( $range );
			$this->relative_date_ranges[ $range ] = Reports\parse_relative_dates_for_range( $range );
		}
	}

	/**
	 * Generate date query SQL for relative time periods.
	 *
	 * @since 3.0
	 * @access protected
	 *
	 * @return string Date query SQL.
	 */
	private function generate_relative_date_query_sql() {

		// Bail if relative calculation not requested.
		if ( false === $this->query_vars['relative'] ) {
			return '';
		}

		// Generate date query SQL if dates have been set.
		if ( ! empty( $this->query_vars['relative_start'] ) || ! empty( $this->query_vars['relative_end'] ) ) {
			$date_query_sql = "AND {$this->query_vars['table']}.{$this->query_vars['date_query_column']} ";

			if ( ! empty( $this->query_vars['relative_start'] ) ) {
				$date_query_sql .= $this->get_db()->prepare( '>= %s', $this->query_vars['relative_start'] );
			}

			// Join dates with `AND` if start and end date set.
			if ( ! empty( $this->query_vars['relative_start'] ) && ! empty( $this->query_vars['relative_end'] ) ) {
				$date_query_sql .= ' AND ';
			}

			if ( ! empty( $this->query_vars['relative_end'] ) ) {
				$date_query_sql .= $this->get_db()->prepare( "{$this->query_vars['table']}.{$this->query_vars['date_query_column']} <= %s", $this->query_vars['relative_end'] );
			}

			return $date_query_sql;
		}
	}
}
