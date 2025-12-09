/* global $, edd_scripts, ajaxurl */

/**
 * Sends an API request to admin-ajax.php
 *
 * @link https://github.com/WordPress/WordPress/blob/master/wp-includes/js/wp-util.js#L49
 *
 * @param {string} action AJAX action to send to admin-ajax.php
 * @param {Object} data Additional data to send to the action.
 * @return {Promise} jQuery Promise.
 */
export function apiRequest( action, data ) {
	const options = {
		type: 'POST',
		dataType: 'json',
		xhrFields: {
			withCredentials: true,
		},
		url: ( window.edd_scripts && window.edd_scripts.ajaxurl ) || window.ajaxurl,
		data: {
			action,
			...data,
		},
	};

	const deferred = $.Deferred( function( deferred ) {
		// Use with PHP's wp_send_json_success() and wp_send_json_error()
		deferred.jqXHR = $.ajax( options ).done( function( response ) {
			// Treat a response of 1 or 'success' as successful for backward compatibility with existing handlers.
			if ( response === '1' || response === 1 ) {
				response = { success: true };
			}

			if ( typeof response === 'object' && typeof response.success !== undefined ) {
				deferred[ response.success ? 'resolveWith' : 'rejectWith' ]( this, [ response.data ] );
			} else {
				deferred.rejectWith( this, [ response ] );
			}
		} ).fail( function() {
			deferred.rejectWith( this, arguments );
		} );
	} );

	const promise = deferred.promise();
	promise.abort = function() {
		deferred.jqXHR.abort();
		return this;
	};

	return promise;
}
