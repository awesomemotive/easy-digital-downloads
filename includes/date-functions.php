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

	// If timestamp is a string, attempt to turn it into a timestamp.
	if ( is_string( $timestamp ) ) {
		$timestamp = strtotime( $timestamp );
	}

	return date_i18n( $format, (int) $timestamp );
}

/**
 * Retrieve timezone ID
 *
 * @since 1.6
 * @return string $timezone The timezone ID
 */
function edd_get_timezone_id() {
	return EDD()->utils->get_time_zone( true );
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
function edd_get_date_format( $format = 'date' ) {
	return EDD()->utils->get_date_format_string( $format );
}
