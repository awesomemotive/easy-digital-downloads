<?php
/**
 * Polyfills for PHP functions which may not be available in older versions.
 *
 * These are loaded via the composer.json file.
 *
 * @since 3.2.0
 *
 * @package     EDD
 * @subpackage  Globals\Polyfills\PHP
 */

if ( ! function_exists( 'cal_days_in_month' ) ) {
	/**
	 * Fallback in case the calendar extension is not loaded in PHP
	 *
	 * Only supports Gregorian calendar
	 */
	function cal_days_in_month( $calendar, $month, $year ) {
		return date( 't', mktime( 0, 0, 0, $month, 1, $year ) ); // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
	}
}

if ( ! function_exists( 'getallheaders' ) ) {
	/**
	 * Retrieve all headers
	 *
	 * Ensure getallheaders function exists in the case we're using nginx
	 *
	 * @since  2.4
	 * @return array
	 */
	function getallheaders() {
		$headers = array();
		foreach ( $_SERVER as $name => $value ) {
			if ( substr( $name, 0, 5 ) === 'HTTP_' ) {
				$headers[ str_replace( ' ', '-', ucwords( strtolower( str_replace( '_', ' ', substr( $name, 5 ) ) ) ) ) ] = $value;
			}
		}
		return $headers;
	}
}
