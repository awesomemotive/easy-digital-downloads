<?php
/**
 * Class for date management
 *
 * @package     EDD
 * @subpackage  Classes/Date
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */
namespace EDD\Utils;

/**
 * Implements date formatting helpers for EDD.
 *
 * @since 3.0
 *
 * @see \EDD\Vendor\Carbon\Carbon
 * @see \DateTime
 */
final class Date extends \EDD\Vendor\Carbon\Carbon {

	/**
	 * Sets up the date.
	 *
	 * @since 3.0
	 * @throws \Exception
	 */
	public function __construct( $time = null, $timezone = null ) {
		if ( null === $timezone ) {
			$timezone = new \DateTimeZone( edd_get_timezone_id() );
		}

		parent::__construct( $time, $timezone );

		// Apply the WP offset based on the WP timezone that was set.
		$offset   = $this->getOffset();
		$interval = \DateInterval::createFromDateString( "{$offset} seconds" );
		$this->add( $interval );
	}

	/**
	 * Formats a given date string according to WP date and time formats and timezone.
	 *
	 * @since 3.0
	 *
	 * @param string|true $format Optional. How to format the date string.  Accepts 'date',
	 *                            'time', 'datetime', 'mysql', 'timestamp', 'wp_timestamp',
	 *                            'object', or any valid date_format() string. If true, 'datetime'
	 *                            will be used. Default 'datetime'.
	 * @return string|int|\DateTime Formatted date string, timestamp if `$type` is timestamp,
	 *                              or a DateTime object if `$type` is 'object'.
	 */
	#[\ReturnTypeWillChange]
	public function format( $format = 'datetime' ) {

		if ( empty( $format ) || true === $format ) {
			$format = 'datetime';
		}

		switch( $format ) {

			// jQuery UI Datepicker formats
			case 'date-attribute':
			case 'date-js':
			case 'date-mysql':
			case 'time-mysql':

			// WordPress Formats
			case 'date':
			case 'time':
			case 'datetime':
			case 'mysql':
				$formatted = parent::format( edd_get_date_format( $format ) );
				break;

			case 'object':
				$formatted = $this;
				break;

			case 'timestamp':
				$formatted = $this->getTimestamp();
				break;

			case 'wp_timestamp':
				/*
				 * Note: Even if the timezone has been changed, getTimestamp() will still
				 * return the original timestamp because DateTime doesn't directly allow
				 * conversion of the timestamp in terms of offset; it's immutable.
				 */
				$formatted = $this->getWPTimestamp();
				break;

			default:
				$formatted = parent::format( $format );
				break;
		}

		return $formatted;
	}

	/**
	 * Retrieves the date timestamp with the WordPress offset applied.
	 *
	 * @since 3.0
	 *
	 * @return int WordPress "local" timestamp.
	 */
	public function getWPTimestamp() {
		return $this->getTimestamp() + EDD()->utils->get_gmt_offset();
	}

	/**
	 * Converts a localized date string to UTC.
	 *
	 * @since 3.1.4
	 * @param $format string
	 * @return string
	 */
	public function get_utc_from_local( $format = 'Y-m-d H:i:s' ) {
		$utc_timezone = new \DateTimeZone( 'utc' );
		$this->setTimezone( $utc_timezone );

		return $this->format( $format );
	}
}
