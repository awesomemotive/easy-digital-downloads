<?php
/**
 * Reports API - Date Ranges
 *
 * @package     EDD\Reports\Dates
 * @copyright   Copyright (c) 2026, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.7.3
 */

namespace EDD\Reports\Dates;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

/**
 * The report date range options.
 *
 * Autoloaded, so it can be used before the reports API has bootstrapped.
 *
 * @since 3.7.3
 */
class Range {

	/**
	 * Unfiltered key/label pairs, built on first use.
	 *
	 * @since 3.7.3
	 * @var array|null
	 */
	private static $options = null;

	/**
	 * Retrieves key/label pairs of date range options for use in a drop-down.
	 *
	 * Call no earlier than `init`. Labels are translated once and cached for the rest of the request.
	 * The edd_report_date_options filter runs on every call.
	 *
	 * @since 3.7.3
	 *
	 * @return array Key/label pairs of date range options.
	 */
	public static function options(): array {
		if ( is_null( self::$options ) ) {
			self::$options = array(
				'other'        => __( 'Custom', 'easy-digital-downloads' ),
				'today'        => __( 'Today', 'easy-digital-downloads' ),
				'yesterday'    => __( 'Yesterday', 'easy-digital-downloads' ),
				'this_week'    => __( 'This Week', 'easy-digital-downloads' ),
				'last_week'    => __( 'Last Week', 'easy-digital-downloads' ),
				'last_30_days' => __( 'Last 30 Days', 'easy-digital-downloads' ),
				'this_month'   => __( 'Month to Date', 'easy-digital-downloads' ),
				'last_month'   => __( 'Last Month', 'easy-digital-downloads' ),
				'this_quarter' => __( 'Quarter to Date', 'easy-digital-downloads' ),
				'last_quarter' => __( 'Last Quarter', 'easy-digital-downloads' ),
				'this_year'    => __( 'Year to Date', 'easy-digital-downloads' ),
				'last_year'    => __( 'Last Year', 'easy-digital-downloads' ),
			);
		}

		/**
		 * Filters the list of key/label pairs of date filter options.
		 *
		 * @since 1.3
		 *
		 * @param array $date_options Date filter options.
		 */
		return apply_filters( 'edd_report_date_options', self::$options );
	}
}
