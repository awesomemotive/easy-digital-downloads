<?php

/**
 * Purpose of this file is to include functions that are in trunk unit testing suite, but not yet in all versions of WordPress core.
 *
 * We'll need to periodly make sure these shims are in parity with the core functions.
 */

 /**
 * Function required to test the qTranslate compatibility function.
 *
 * @since 2.3
 */
if ( ! function_exists( 'qtrans_useCurrentLanguageIfNotFoundShowAvailable' ) ) {
	function qtrans_useCurrentLanguageIfNotFoundShowAvailable( $content ) {
		return $content;
	}
}

/**
 * Set up wp_die handler for EDD unit tests.
 *
 * This makes wp_die() and edd_die() calls throw appropriate exceptions
 * so they can be caught and tested in unit tests. Respects AJAX context
 * to throw WPAjaxDieContinueException or WPAjaxDieStopException as needed.
 *
 * @since 3.6.3
 * @param string       $message Optional. Error message. Default empty.
 * @param string       $title   Optional. Error title. Default empty.
 * @param array|string $args    Optional. Arguments to control behavior. Default empty array.
 * @throws \WPDieException|\WPAjaxDieContinueException|\WPAjaxDieStopException Depends on context.
 */
function _edd_test_die_handler( $message = '', $title = '', $args = array() ) {
	// Sanitize the message for the exception.
	$exception_message = '';
	if ( ! empty( $message ) ) {
		if ( is_scalar( $message ) ) {
			$exception_message = (string) $message;
		} elseif ( is_array( $message ) ) {
			$exception_message = implode( ', ', array_filter( $message, 'is_scalar' ) );
		} elseif ( is_object( $message ) && method_exists( $message, '__toString' ) ) {
			$exception_message = (string) $message;
		}
	}
	throw new \WPDieException( $exception_message );
}

add_filter(
	'wp_die_handler',
	function ( $function ) {
		return '_edd_test_die_handler';
	}
);
add_filter(
	'wp_die_json_handler',
	function ( $function ) {
		return '_edd_test_die_handler';
	}
);
