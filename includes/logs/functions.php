<?php

/**
 * Helper method to insert a new log into the database.
 *
 * @since 1.3.3
 *
 * @see EDD\Logging::add()
 *
 * @param string $title   Log title.
 * @param string $message Log message.
 * @param int    $parent  Download ID.
 * @param null   $type    Log type.
 *
 * @return int ID of the new log.
 */
function edd_record_log( $title = '', $message = '', $parent = 0, $type = null ) {
	$edd_logs = EDD()->debug_log;

	return $edd_logs->add( $title, $message, $parent, $type );
}

/**
 * Logs a message to the debug log file.
 *
 * @since 2.8.7
 * @since 2.9.4 Added the 'force' option.
 *
 * @param string $message Log message.
 * @param bool   $force   Whether to force a log entry to be added. Default false.
 */
function edd_debug_log( $message = '', $force = false ) {
	$edd_logs = EDD()->debug_log;

	if ( edd_is_debug_mode() || $force ) {

		if ( function_exists( 'mb_convert_encoding' ) ) {

			$message = mb_convert_encoding( $message, 'UTF-8' );

		}

		$edd_logs->log_to_file( $message );
	}
}

/**
 * Logs an exception to the debug log file.
 *
 * @since 3.0
 *
 * @param \Exception $exception Exception object.
 */
function edd_debug_log_exception( $exception ) {

	$label = get_class( $exception );

	if ( $exception->getCode() ) {

		$message = sprintf(
			'%1$s: %2$s - %3$s',
			$label,
			$exception->getCode(),
			$exception->getMessage()
		);

	} else {

		$message = sprintf(
			'%1$s: %2$s',
			$label,
			$exception->getMessage()
		);

	}

	edd_debug_log( $message );
}
