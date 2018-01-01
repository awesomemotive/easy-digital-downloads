<?php
/**
 * Exception object for EDD
 *
 * This class handles connecting exception handling to the debug log (as needed).
 *
 * @package     EDD
 * @subpackage  Classes/Utilities
 * @copyright   Copyright (c) 2018, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */
namespace EDD\Utils;

/**
 * Implements some EDD-specific logic for exception handling.
 *
 * @since 3.0
 *
 * @see \Exception
 */
class Exception extends \Exception {

	/**
	 * Logs an exception to the EDD debug log.
	 *
	 * @since 3.0
	 *
	 * @param string $extra Any extra information to include in the log entry
	 *                      alongside the exception message.
	 */
	public function log( $extra = '' ) {
		if ( $this->getCode() ) {

			$message = sprintf( 'Exception Code: %1$s – Message: %2$s',
				$this->getCode(),
				$this->getMessage()
			);

		} else {

			$message = sprintf( 'Exception Message: %1$s',
				$this->getMessage()
			);

		}

		if ( ! empty( $extra ) ) {
			$message = sprintf( "{$message} %s", $extra );
		}

		edd_debug_log( $message );
	}

}
