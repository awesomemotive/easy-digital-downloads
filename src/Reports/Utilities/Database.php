<?php
/**
 * Reports API - Database Utilities.
 *
 * @package     EDD\Reports\Utilities
 * @copyright   Copyright (c) 2026, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.6.5
 */

namespace EDD\Reports\Utilities;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

use EDD\Utils\Date;

/**
 * Database Utilities.
 *
 * @since 3.6.5
 */
class Database {

	/**
	 * Gets the SQL clauses.
	 * The result of this function should be run through $wpdb->prepare().
	 *
	 * @since 3.6.5
	 * @param string $period The period for the query.
	 * @param string $column The column to query.
	 * @return array
	 */
	public static function get_sql_clauses( string $period, string $column = 'date_created' ): array {

		// Get the date for the query.
		$converted_date = self::get_column_conversion( $column );

		switch ( $period ) {
			case 'hour':
				$date_format = '%%Y-%%m-%%d %%H:00:00';
				break;
			case 'day':
				$date_format = '%%Y-%%m-%%d';
				break;
			default:
				$date_format = '%%Y-%%m';
				break;
		}

		return array(
			'select'  => "DATE_FORMAT({$converted_date}, \"{$date_format}\") AS date",
			'where'   => '',
			'groupby' => 'date',
			'orderby' => 'date',
		);
	}

	/**
	 * Parses start and end dates for database queries.
	 *
	 * Accepts dates in various formats (strings, DateTimeInterface, EDD\Utils\Date)
	 * and returns UTC-converted MySQL datetime strings ready for WHERE clauses.
	 *
	 * @since 3.6.5
	 * @param string|\DateTimeInterface $start Start date in store timezone.
	 * @param string|\DateTimeInterface $end   End date in store timezone.
	 * @return array{start: string, end: string, period: string} UTC datetime strings and recommended period.
	 */
	public static function parse_date_range_for_query( $start, $end ): array {
		$tz_id = edd_get_timezone_id();

		// Convert start to EDD\Utils\Date in store timezone.
		if ( $start instanceof \EDD\Utils\Date ) {
			$start_date = $start->copy();
		} elseif ( $start instanceof \DateTimeInterface ) {
			$start_date = EDD()->utils->date( $start->format( 'Y-m-d H:i:s' ), $tz_id, false );
		} else {
			$start_date = EDD()->utils->date( $start, $tz_id, false );
		}

		// Convert end to EDD\Utils\Date in store timezone.
		if ( $end instanceof \EDD\Utils\Date ) {
			$end_date = $end->copy();
		} elseif ( $end instanceof \DateTimeInterface ) {
			$end_date = EDD()->utils->date( $end->format( 'Y-m-d H:i:s' ), $tz_id, false );
		} else {
			$end_date = EDD()->utils->date( $end, $tz_id, false );
		}

		// Ensure start of day / end of day boundaries.
		$start_date = $start_date->startOfDay();
		$end_date   = $end_date->endOfDay();

		// Determine the appropriate grouping period based on the range.
		$period = self::get_period_for_date_range( $start_date, $end_date );

		// Convert to UTC for database queries.
		$start_utc = edd_get_utc_equivalent_date( $start_date );
		$end_utc   = edd_get_utc_equivalent_date( $end_date );

		return array(
			'start'  => $start_utc->format( 'Y-m-d H:i:s' ),
			'end'    => $end_utc->format( 'Y-m-d H:i:s' ),
			'period' => $period,
		);
	}

	/**
	 * Determines the appropriate grouping period based on date range span.
	 *
	 * @since 3.6.5
	 * @param \DateTimeInterface $start Start date.
	 * @param \DateTimeInterface $end   End date.
	 * @return string 'hour', 'day', or 'month'.
	 */
	public static function get_period_for_date_range( \DateTimeInterface $start, \DateTimeInterface $end ): string {
		$difference = $end->getTimestamp() - $start->getTimestamp();

		// 2 days or less: hourly.
		if ( $difference <= ( DAY_IN_SECONDS * 2 ) ) {
			return 'hour';
		}

		// 3 months or less: daily.
		if ( $difference < ( YEAR_IN_SECONDS / 4 ) ) {
			return 'day';
		}

		// Longer ranges: monthly.
		return 'month';
	}

	/**
	 * Retrieves the column conversion for the given date column.
	 *
	 * @since 3.6.5
	 * @param string $column The column to convert.
	 * @return string
	 */
	public static function get_column_conversion( string $column = 'date_created' ): string {
		$date       = EDD()->utils->date( 'now', edd_get_timezone_id(), false );
		$gmt_offset = $date->getOffset();
		if ( empty( $gmt_offset ) ) {
			return $column;
		}

		// Output the offset in the proper format.
		$hours   = abs( floor( $gmt_offset / HOUR_IN_SECONDS ) );
		$minutes = abs( floor( ( $gmt_offset / MINUTE_IN_SECONDS ) % MINUTE_IN_SECONDS ) );
		$math    = ( $gmt_offset >= 0 ) ? '+' : '-';

		$formatted_offset = ! empty( $minutes ) ? "{$hours}:{$minutes}" : $hours . ':00';

		/**
		 * There is a limitation here that we cannot get past due to MySQL not having timezone information.
		 *
		 * When a requested date group spans the DST change. For instance, a 6 month graph will have slightly
		 * different results for each month than if you pulled each of those 6 months individually. This is because
		 * our 'grouping' can only convert the timezone based on the current offset and that can change if the
		 * range spans the DST break, which would have some dates be in a +/- 1 hour state.
		 *
		 * @see https://github.com/awesomemotive/easy-digital-downloads/pull/9449
		 */
		return "CONVERT_TZ({$column}, '+00:00', '{$math}{$formatted_offset}')";
	}
}
