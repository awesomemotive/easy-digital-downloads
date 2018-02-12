<?php
/**
 * Date Functions
 *
 * @package    EDD
 * @subpackage Functions
 * @copyright  Copyright (c) 2018, Pippin Williamson
 * @license    http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since      3.0
 */

/**
 * Retrieves a localized, formatted date based on the WP timezone rather than UTC.
 *
 * @since 3.0
 *
 * @param int    $timestamp Timestamp. Can either be based on UTC or WP settings.
 * @param string $format    Optional. Accepts shorthand 'date', 'time', or 'datetime'
 *                          date formats, as well as any valid date_format() string.
 *                          Default 'date' represents the value of the 'date_format' option.
 * @return string The formatted date, translated if locale specifies it.
 */
function edd_date_i18n( $timestamp, $format = 'date' ) {

	$format = edd_get_date_format( $format );

	return date_i18n( $format, (int) $timestamp );
}

/**
 * Attempts to derive a timezone string from the WordPress settings.
 *
 * @since 3.0
 *
 * @return string WordPress timezone as derived from a combination of the timezone_string
 *                and gmt_offset options. If no valid timezone could be found, defaults to
 *                UTC.
 */
function edd_get_timezone() {

	// Passing a $default value doesn't work for the timezone_string option.
	$timezone = get_option( 'timezone_string' );

	/*
	 * If the timezone isn't set, or rather was set to a UTC offset, core saves the value
	 * to the gmt_offset option and leaves timezone_string empty – because that makes
	 * total sense, obviously. ¯\_(ツ)_/¯
	 *
	 * So, try to use the gmt_offset to derive a timezone.
	 */
	if ( empty( $timezone ) ) {
		// Try to grab the offset instead.
		$gmt_offset = get_option( 'gmt_offset', 0 );

		// Yes, core returns it as a string, so as not to confuse it with falsey.
		if ( '0' !== $gmt_offset ) {
			$timezone = timezone_name_from_abbr( '', (int) $gmt_offset * HOUR_IN_SECONDS, date( 'I' ) );
		}

		// If the offset was 0 or $timezone is still empty, just use 'UTC'.
		if ( '0' === $gmt_offset || empty( $timezone ) ) {
			$timezone = 'UTC';
		}
	}

	return $timezone;
}

/**
 * Retrieves a date format string based on a given short-hand format.
 *
 * @since 3.0
 *
 * @see \EDD_Utilities::get_date_format_string()
 *
 * @param string $format Shorthand date format string. Accepts 'date', 'time', 'mysql', or
 *                       'datetime'. If none of the accepted values, the original value will
 *                       simply be returned. Default is the value of the `$date_format` property,
 *                       derived from the core 'date_format' option.
 *
 * @return string date_format()-compatible date format string.
 */
function edd_get_date_format( $format ) {
	return EDD()->utils->get_date_format_string( $format );
}

/**
 * Retrieves the start and end date filters for use with the Reports API.
 *
 * @since 3.0
 *
 * @param string $values   Optional. What format to retrieve dates in the resulting array in.
 *                         Accepts 'strings' or 'objects'. Default 'strings'.
 * @param string $timezone Optional. Timezone to force for filter dates. Primarily used for
 *                         legacy testing purposes. Default empty.
 * @return array|\EDD\Utils\Date[] {
 *     Query date range for the current graph filter request.
 *
 *     @type string|\EDD\Utils\Date $start Start day and time (based on the beginning of the given day).
 *                                         If `$values` is 'objects', a Carbon object, otherwise a date
 *                                         time string.
 *     @type string|\EDD\Utils\Date $end   End day and time (based on the end of the given day). If `$values`
 *                                         is 'objects', a Carbon object, otherwise a date time string.
 * }
 */
function edd_get_filter_dates( $values = 'strings', $timezone = '' ) {
	$date       = EDD()->utils->date( 'now', $timezone );
	$date_range = edd_get_date_filter_range();

	/** @var \EDD\Utils\Date[] $dates */
	$dates = array();

	switch( $date_range ) {

		case 'this_month':
			$dates = array(
				'start' => $date->copy()->startOfMonth(),
				'end'   => $date->copy()->endOfMonth(),
			);
			break;

		case 'last_month':
			$dates = array(
				'start' => $date->copy()->subMonth( 1 )->startOfMonth(),
				'end'   => $date->copy()->subMonth( 1 )->endOfMonth(),
			);
			break;

		case 'today':
			$dates = array(
				'start' => $date->copy()->startOfDay(),
				'end'   => $date->copy()->endOfDay(),
			);
			break;

		case 'yesterday':
			$dates = array(
				'start' => $date->copy()->subDay( 1 )->startOfDay(),
				'end'   => $date->copy()->subDay( 1 )->endOfDay(),
			);
			break;

		case 'this_week':
			$dates = array(
				'start' => $date->copy()->startOfWeek(),
				'end'   => $date->copy()->endOfWeek(),
			);
			break;

		case 'last_week':
			$dates = array(
				'start' => $date->copy()->subWeek( 1 )->startOfWeek(),
				'end'   => $date->copy()->subWeek( 1 )->endOfWeek(),
			);
			break;

		case 'last_30_days':
			$dates = array(
				'start' => $date->copy()->subDay( 30 )->startOfDay(),
				'end'   => $date->copy()->endOfDay(),
			);
			break;

		case 'this_quarter':
			$dates = array(
				'start' => $date->copy()->startOfQuarter(),
				'end'   => $date->copy()->endOfQuarter(),
			);
			break;

		case 'last_quarter':
			$dates = array(
				'start' => $date->copy()->subQuarter( 1 )->startOfQuarter(),
				'end'   => $date->copy()->subQuarter( 1 )->endOfQuarter(),
			);
			break;

		case 'this_year':
			$dates = array(
				'start' => $date->copy()->startOfYear(),
				'end'   => $date->copy()->endOfYear(),
			);
			break;

		case 'last_year':
			$dates = array(
				'start' => $date->copy()->subYear( 1 )->startOfYear(),
				'end'   => $date->copy()->subYear( 1 )->endOfYear(),
			);
			break;

		case 'other':
		default:
			$filter_dates = edd_get_filter_date_values( true );

			$dates = array(
				'start' => EDD()->utils->date( $filter_dates['start'] )->startOfDay(),
				'end'   => EDD()->utils->date( $filter_dates['end'] )->endOfDay(),
			);
			break;
	}

	if ( 'strings' === $values ) {
		if ( ! empty( $dates['start'] ) ) {
			$dates['start'] = $dates['start']->toDateTimeString();
		}
		if ( ! empty( $dates['end'] ) ) {
			$dates['end'] = $dates['end']->toDateTimeString();
		}
	}

	/**
	 * Filters the start and end date filters for use with the Graphs API.
	 *
	 * @since 3.0
	 *
	 * @param array|\EDD\Utils\Date[] $dates {
	 *     Query date range for the current graph filter request.
	 *
	 *     @type string|\EDD\Utils\Date $start Start day and time (based on the beginning of the given day).
	 *                                         If `$values` is 'objects', a Carbon object, otherwise a date
	 *                                         time string.
	 *     @type string|\EDD\Utils\Date $end   End day and time (based on the end of the given day). If `$values`
	 *                                         is 'objects', a Carbon object, otherwise a date time string.
	 * }
	 */
	return apply_filters( 'edd_get_filter_dates', $dates );
}

/**
 * Retrieves values of the filter_from and filter_to request variables.
 *
 * @since 3.0
 *
 * @param bool $now Optional. Whether to default to 'now' when retrieving empty values. Default false.
 * @return array {
 *     Query date range for the current date filter request.
 *
 *     @type string $start Start day and time string based on the WP timezone.
 *     @type string $end   End day and time string based on the WP timezone.
 * }
 */
function edd_get_filter_date_values( $now = false ) {
	if ( true === $now ) {
		$default = 'now';
	} else {
		$default = '';
	}
	$values = array(
		'start' => empty( $_REQUEST['filter_from'] ) ? $default : $_REQUEST['filter_from'],
		'end'   => empty( $_REQUEST['filter_to'] )   ? $default : $_REQUEST['filter_to']
	);
	/**
	 * Filters the start and end filter date values for a Graph API request.
	 *
	 * @since 2.2
	 *
	 * @param array {
	 *     Query date range for the current date filter request.
	 *
	 *     @type string $start Start day and time string based on the WP timezone.
	 *     @type string $end   End day and time string based on the WP timezone.
	 * }
	 * @param string $default The fallback value if 'filter_from' and/or 'filter_to' `$_REQUEST`
	 *                        values are empty. If `$now` is true, will be 'now', otherwise empty.
	 */
	return apply_filters( 'edd_get_filter_date_values', $values, $default );
}

/**
 * Retrieves the date filter range.
 *
 * @since 3.0
 *
 * @return string Date filter range.
 */
function edd_get_date_filter_range() {
	if ( isset( $_REQUEST['range'] ) ) {
		$range = sanitize_key( $_REQUEST['range'] );
	} else {
		$range = 'last_30_days';
	}

	/**
	 * Filters the report dates default range.
	 *
	 * @since 1.3
	 *
	 * @param string $range Date range as derived from the 'range' request var.
	 *                      Default 'last_30_days'
	 */
	return apply_filters( 'edd_get_report_dates_default_range', $range );
}
