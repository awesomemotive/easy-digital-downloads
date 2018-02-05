<?php
/**
 * EDD Utilities Bootstrap
 *
 * @package     EDD
 * @subpackage  Utilities
 * @copyright   Copyright (c) 2018, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */

/**
 * Class that bootstraps various utilities leveraged in EDD core.
 *
 * @since 3.0
 *
 * @property-read int    $wp_offset   The calculated WordPress gmt_offset in seconds.
 * @property-read string $date_format The current WordPress date format.
 * @property-read string $time_format The current WordPress time format.
 */
class EDD_Utilities {

	/**
	 * Sets up instantiating core utilities.
	 *
	 * @since 3.0
	 */
	public function __construct() {
		$this->wp_offset   = get_option( 'gmt_offset', 0 ) * HOUR_IN_SECONDS;
		$this->date_format = get_option( 'date_format', 'M j, Y' );
		$this->time_format = get_option( 'time_format', 'g:i a' );

		$this->includes();
	}

	/**
	 * Loads needed files for core utilities.
	 *
	 * @since 3.0
	 */
	private function includes() {
		$utils_dir = EDD_PLUGIN_DIR . 'includes/utilities/';

		// Interfaces.
		require_once $utils_dir . 'interface-edd-exception.php';
		require_once $utils_dir . 'interface-static-registry.php';

		// Exceptions.
		require_once $utils_dir . 'class-edd-exception.php';
		require_once $utils_dir . 'exceptions/class-attribute-not-found.php';
		require_once $utils_dir . 'exceptions/class-invalid-argument.php';
		require_once $utils_dir . 'exceptions/class-invalid-parameter.php';

		// Date management.
		require_once $utils_dir . 'class-date.php';

		// Registry.
		require_once $utils_dir . 'class-registry.php';
	}

	/**
	 * Retrieves a given registry instance by name.
	 *
	 * @since 3.0
	 *
	 * @param string $name Registry name.
	 * @return \EDD\Utils\Registry|\WP_Error The registry instance if it exists, otherwise a WP_Error..
	 */
	public function get_registry( $name ) {

		switch( $name ) {
			case 'reports':

				if ( ! did_action( 'edd_reports_init' ) ) {

					_doing_it_wrong( __FUNCTION__, 'The Reports registry cannot be retrieved prior to the edd_reports_init hook.', 'EDD 3.0' );

				} elseif ( class_exists( '\EDD\Reports\Data\Reports_Registry' ) ) {

					$registry = \EDD\Reports\Data\Reports_Registry::instance();

				}
				break;

			case 'reports:endpoints':

				if ( ! did_action( 'edd_reports_init' ) ) {

					_doing_it_wrong( __FUNCTION__, 'The Endpoints registry cannot be retrieved prior to the edd_reports_init hook.', 'EDD 3.0' );

				} elseif ( class_exists( '\EDD\Reports\Data\Endpoint_Registry' ) ) {

					$registry = \EDD\Reports\Data\Endpoint_Registry::instance();

				}
				break;

			default:
				$registry = new \WP_Error( 'invalid_registry', "The '{$name}' registry does not exist." );
				break;
		}

		return $registry;
	}

	/**
	 * Retrieves a date format string based on a given short-hand format.
	 *
	 * @since 3.0
	 *
	 * @param string $format Shorthand date format string. Accepts 'date', 'time', 'mysql', or
	 *                       'datetime'. If none of the accepted values, the original value will
	 *                       simply be returned. Default is the value of the `$date_format` property,
	 *                       derived from the core 'date_format' option.
	 * @return string date_format()-compatible date format string.
	 */
	public function get_date_format( $format ) {

		if ( empty( $format ) ) {
			$format = 'date';
		}

		if ( ! in_array( $format, array( 'date', 'time', 'datetime', 'mysql' ) ) ) {
			return $format;
		}

		switch( $format ) {
			case 'time':
				$format = $this->time_format;
				break;

			case 'datetime':
				$format = $this->date_format . ' ' . $this->time_format;
				break;

			case 'mysql':
				$format = 'Y-m-d H:i:s';
				break;

			case 'date':
			default:
				$format = $this->date_format;
				break;
		}

		return $format;
	}

	/**
	 * Retrieves a date instance for the WP timezone (and offset) based on the given date string.
	 *
	 * @since 3.0
	 *
	 * @param string $date_string Optional. Date string. Default 'now'.
	 * @param string $timezone    Optional. Timezone to generate the Carbon instance for.
	 *                            Default is the timezone set in WordPress settings.
	 * @return \EDD\Utils\Date Date instance.
	 */
	public function date( $date_string = 'now' ) {

		$timezone = edd_get_timezone();

		/*
		 * Create the DateTime object with the "local" WordPress timezone.
		 *
		 * Note that supplying the timezone during DateTime instantiation doesn't actually
		 * convert the UNIX timestamp, it just lays the groundwork for deriving the offset.
		 */
		$date = new EDD\Utils\Date( $date_string, new DateTimezone( $timezone ) );

		return $date;
	}

	/**
	 * Refreshes the wp_offset property.
	 *
	 * Useful if the gmt_offset has been updated or changed after the class has already loaded.
	 *
	 * @since 3.0
	 */
	public function refresh_wp_offset() {
		$this->wp_offset = get_option( 'gmt_offset', 0 ) * HOUR_IN_SECONDS;
	}

}
