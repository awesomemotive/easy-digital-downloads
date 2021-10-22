/* global eddNotifications */

document.addEventListener( 'DOMContentLoaded', function() {
	var notifications = document.querySelectorAll( '.edd-notification' );
	if ( ! notifications ) {
		return;
	}

	notifications.forEach( function( notification ) {
		var dismissButton = notification.querySelector( '.edd-notification--dismiss' );
		if ( ! dismissButton ) {
			return;
		}

		dismissButton.addEventListener( 'click', function( e ) {
			e.preventDefault();

			var notificationId = dismissButton.getAttribute( 'data-id' );
			if ( ! notificationId ) {
				return;
			}

			console.log( 'Dismissing', notificationId );

			dismissNotification( notificationId )
				.then( function() {
					notification.remove();
				} )
				.catch( function( error ) {
					console.log( 'Dismiss error', error );
				} );
		} );
	} );

	/**
	 * Dismisses a notification.
	 *
	 * @param {integer} id
	 * @returns {Promise<Response>}
	 */
	function dismissNotification( id ) {
		return fetch( eddNotifications.restBase + '/notifications/' + id, {
			method: 'DELETE',
			credentials: 'same-origin',
			headers: {
				'Content-Type': 'application/json',
				'X-WP-Nonce': eddNotifications.restNonce
			}
		} ).then( function( response ) {
			if ( ! response.ok ) {
				return Promise.reject( response );
			}

			return response.json();
		} );
	}
} );
