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

/**
 * Get the format used by jQuery UI Datepickers.
 *
 * Use this if you need to use placeholder or format attributes in input fields.
 *
 * This is a bit different than `edd_get_date_format()` because these formats
 * are exposed to users as hints and also used by jQuery UI so the Datepicker
 * knows what format it returns into it's connected input value.
 *
 * Previous to this function existing, all values were hard-coded, causing some
 * inconsistencies across admin-area screens.
 *
 * @see https://github.com/easydigitaldownloads/easy-digital-downloads/commit/e9855762892b6eec578b0a402f7950f22bd19632
 *
 * @since 3.0
 *
 * @param string $context The context we are getting the format for. Accepts 'display' or 'js'.
 *                        Use 'js' for use with jQuery UI Datepicker. Use 'display' for HTML attributes.
 * @return string
 */
function edd_get_date_picker_format( $context = 'display' ) {

	// What is the context that we are getting the picker format for?
	switch ( $context ) {

		// jQuery UI Datepicker does its own thing
		case 'js' :
		case 'javascript' :
			$retval = EDD()->utils->get_date_format_string( 'date-js' );
			break;

		// Used to display in an attribute, placeholder, etc...
		case 'display' :
		default :
			$retval = EDD()->utils->get_date_format_string( 'date-attribute' );
			break;
	}

	/**
	 * Filter the date picker format, allowing for custom overrides
	 *
	 * @since 3.0
	 *
	 * @param string $retval  Date format for date picker
	 * @param string $context The context this format is for
	 */
	return apply_filters( 'edd_get_date_picker_format', $retval, $context );
}
