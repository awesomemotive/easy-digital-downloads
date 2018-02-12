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
