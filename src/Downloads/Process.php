<?php
/**
 * Process file downloads.
 *
 * @package EDD
 * @since 3.3.3
 */

namespace EDD\Downloads;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Downloads process class
 *
 * @since 3.3.3
 */
class Process {

	/**
	 * Validate a download file.
	 *
	 * @since 3.3.3
	 *
	 * @param string $file File path.
	 * @return bool
	 */
	public static function validate( $file ) {
		if ( ! $file ) {
			return false;
		}

		return in_array( validate_file( $file ), self::get_allowed_validations( $file ), true );
	}

	/**
	 * Get allowed validations.
	 *
	 * @since 3.3.3
	 *
	 * @param string $file File path.
	 * @return array
	 */
	private static function get_allowed_validations( $file ) {
		$allowed_validations = array( 0 );

		if ( edd_is_dev_environment() ) {
			$allowed_validations[] = 2;
		}

		return apply_filters( 'edd_file_allowed_validations', $allowed_validations, $file );
	}
}
