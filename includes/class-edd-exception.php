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

/**
 * Implements some EDD-specific logic for exception handling.
 *
 * @since 3.0
 *
 * @see \Exception
 */
class EDD_Exception extends \Exception {

	/**
	 * Logs an exception to the EDD debug log.
	 *
	 * @since 3.0
	 *
	 * @param string $extra Any extra information to include in the log entry
	 *                      alongside the exception message.
	 */
	public function log( $extra = '' ) {
		/**
		 * Filters whether to skip logging EDD exceptions to the debug log.
		 *
		 * @since 3.0
		 *
		 * @param bool $skip Whether to skip logging exceptions. Default false (proceed).
		 */
		if ( false === apply_filters( 'edd_skip_logging_exceptions', false ) ) {

			if ( $this->getCode() ) {

				edd_debug_log( sprintf( 'Exception Code: %1$s – Message: %2$s %3$s',
					$this->getCode(),
					$this->getMessage(),
					$extra
				) );

			} else {

				edd_debug_log( sprintf( 'Exception Message: %1$s %2$s',
					$this->getMessage(),
					$extra
				) );

			}

		}
	}

	/**
	 * Constructs an EDD_Exception instance.
	 *
	 * @since 3.0
	 *
	 * @param string         $message  Optional. Exception message. Default empty string.
	 * @param int            $code     Optional. Exception code. Default zero.
	 * @param Throwable|null $previous Optional. Previous exception used when chaining. Default null.
	 */
	public function __construct( $message = "", $code = 0, Throwable $previous = null ) {
		parent::__construct( $message, $code, $previous );
	}

}
